<?php
if (!defined('ABSPATH')) {
    exit;
}

class BocconiFileIntegrityMonitor {
    private static $instance = null;
    private $logger;
    private $hash_file;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->logger = BocconiSecurityLogger::getInstance();
        $this->hash_file = WP_CONTENT_DIR . '/bocconi-file-hashes.json';
        
        // Schedule integrity check if not already scheduled
        if (!wp_next_scheduled('bocconi_security_integrity_check')) {
            $interval = get_option('bocconi_integrity_interval', 'daily');
            wp_schedule_event(time(), $interval, 'bocconi_security_integrity_check');
        }
        
        add_action('bocconi_security_integrity_check', array($this, 'run_scheduled_check'));
    }
    
    public function run_scheduled_check() {
        if (!get_option('bocconi_integrity_enabled', false)) {
            return;
        }
        
        $this->logger->log_security_event('INTEGRITY_CHECK_STARTED', array('type' => 'scheduled'));
        
        try {
            $changes = $this->check_file_integrity();
            
            if (!empty($changes)) {
                $this->logger->log_security_event('INTEGRITY_VIOLATIONS_DETECTED', array(
                    'changes' => count($changes),
                    'files' => array_slice($changes, 0, 10) // Log first 10 changes
                ), 'warning');
                
                // Send notification if enabled
                if (get_option('bocconi_integrity_notifications', false)) {
                    $this->send_integrity_notification($changes);
                }
            } else {
                $this->logger->log_security_event('INTEGRITY_CHECK_PASSED', array());
            }
        } catch (Exception $e) {
            $this->logger->log_security_event('INTEGRITY_CHECK_FAILED', array(
                'error' => $e->getMessage()
            ), 'error');
        }
    }
    
    public function initialize_baseline() {
        $this->logger->log_security_event('INTEGRITY_BASELINE_INIT', array());
        
        $files_to_monitor = $this->get_files_to_monitor();
        $hashes = array();
        
        foreach ($files_to_monitor as $file) {
            if (file_exists($file) && is_readable($file)) {
                $hashes[$file] = array(
                    'hash' => hash_file('sha256', $file),
                    'size' => filesize($file),
                    'modified' => filemtime($file)
                );
            }
        }
        
        file_put_contents($this->hash_file, wp_json_encode($hashes, JSON_PRETTY_PRINT));
        
        $this->logger->log_security_event('INTEGRITY_BASELINE_CREATED', array(
            'files_count' => count($hashes)
        ));
        
        return count($hashes);
    }
    
    public function check_file_integrity() {
        if (!file_exists($this->hash_file)) {
            throw new Exception('Baseline non inizializzato. Eseguire prima l\'inizializzazione.');
        }
        
        $baseline = json_decode(file_get_contents($this->hash_file), true);
        $changes = array();
        
        foreach ($baseline as $file_path => $baseline_data) {
            $change = $this->check_single_file($file_path, $baseline_data);
            if ($change) {
                $changes[] = $change;
            }
        }
        
        // Check for new files in monitored directories
        $new_files = $this->detect_new_files($baseline);
        $changes = array_merge($changes, $new_files);
        
        return $changes;
    }
    
    private function check_single_file($file_path, $baseline_data) {
        if (!file_exists($file_path)) {
            return array(
                'file' => $file_path,
                'type' => 'deleted',
                'message' => 'File eliminato'
            );
        }
        
        $current_hash = hash_file('sha256', $file_path);
        $current_size = filesize($file_path);
        $current_modified = filemtime($file_path);
        
        if ($current_hash !== $baseline_data['hash']) {
            return array(
                'file' => $file_path,
                'type' => 'modified',
                'message' => 'Contenuto modificato',
                'old_hash' => $baseline_data['hash'],
                'new_hash' => $current_hash,
                'old_size' => $baseline_data['size'],
                'new_size' => $current_size
            );
        }
        
        if ($current_modified > $baseline_data['modified']) {
            return array(
                'file' => $file_path,
                'type' => 'timestamp_changed',
                'message' => 'Timestamp modificato',
                'old_modified' => $baseline_data['modified'],
                'new_modified' => $current_modified
            );
        }
        
        return null;
    }
    
    private function detect_new_files($baseline) {
        $new_files = array();
        $monitored_dirs = $this->get_monitored_directories();
        
        foreach ($monitored_dirs as $dir) {
            if (!is_dir($dir)) continue;
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $file_path = $file->getRealPath();
                    
                    // Skip if file was in baseline
                    if (isset($baseline[$file_path])) continue;
                    
                    // Skip temporary and cache files
                    if ($this->should_skip_file($file_path)) continue;
                    
                    $new_files[] = array(
                        'file' => $file_path,
                        'type' => 'new_file',
                        'message' => 'Nuovo file rilevato',
                        'size' => $file->getSize(),
                        'created' => $file->getMTime()
                    );
                }
            }
        }
        
        return $new_files;
    }
    
    private function get_files_to_monitor() {
        $files = array();
        $dirs = $this->get_monitored_directories();
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && !$this->should_skip_file($file->getRealPath())) {
                    $files[] = $file->getRealPath();
                }
            }
        }
        
        return $files;
    }
    
    private function get_monitored_directories() {
        $default_dirs = array(
            ABSPATH . 'wp-admin',
            ABSPATH . 'wp-includes',
            get_template_directory(),
            WP_CONTENT_DIR . '/plugins'
        );
        
        // Add child theme if exists
        if (get_stylesheet_directory() !== get_template_directory()) {
            $default_dirs[] = get_stylesheet_directory();
        }
        
        return apply_filters('bocconi_integrity_monitored_dirs', $default_dirs);
    }
    
    private function should_skip_file($file_path) {
        $skip_patterns = array(
            '/cache/',
            '/tmp/',
            '/temp/',
            '.log',
            '.tmp',
            '.cache',
            'bocconi-file-hashes.json',
            'bocconi-backups/',
            '.git/',
            'node_modules/'
        );
        
        foreach ($skip_patterns as $pattern) {
            if (strpos($file_path, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function send_integrity_notification($changes) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = "[{$site_name}] Violazioni Integrità File Rilevate";
        
        $message = "Sono state rilevate modifiche non autorizzate ai file:\n\n";
        
        foreach (array_slice($changes, 0, 20) as $change) {
            $message .= "- {$change['file']}: {$change['message']}\n";
        }
        
        if (count($changes) > 20) {
            $message .= "\n... e altre " . (count($changes) - 20) . " modifiche.\n";
        }
        
        $message .= "\nVerifica immediatamente la sicurezza del sito.";
        
        wp_mail($admin_email, $subject, $message);
    }
    
    public function get_integrity_settings() {
        return array(
            'enabled' => get_option('bocconi_integrity_enabled', false),
            'interval' => get_option('bocconi_integrity_interval', 'daily'),
            'notifications' => get_option('bocconi_integrity_notifications', false),
            'baseline_exists' => file_exists($this->hash_file),
            'next_check' => wp_next_scheduled('bocconi_security_integrity_check')
        );
    }
    
    public function update_integrity_settings($settings) {
        update_option('bocconi_integrity_enabled', $settings['enabled']);
        update_option('bocconi_integrity_interval', $settings['interval']);
        update_option('bocconi_integrity_notifications', $settings['notifications']);
        
        // Reschedule cron if interval changed
        wp_clear_scheduled_hook('bocconi_security_integrity_check');
        if ($settings['enabled']) {
            wp_schedule_event(time(), $settings['interval'], 'bocconi_security_integrity_check');
        }
        
        $this->logger->log_security_event('INTEGRITY_SETTINGS_UPDATED', $settings);
    }
    
    public function get_last_check_results() {
        try {
            $changes = $this->check_file_integrity();
            return array(
                'status' => empty($changes) ? 'clean' : 'violations',
                'changes' => $changes,
                'check_time' => time()
            );
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'error' => $e->getMessage(),
                'check_time' => time()
            );
        }
    }
}