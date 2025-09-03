<?php
if (!defined('ABSPATH')) {
    exit;
}

class BocconiBackupManager {
    private static $instance = null;
    private $backup_dir;
    private $logger;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->backup_dir = WP_CONTENT_DIR . '/bocconi-backups';
        $this->logger = BocconiSecurityLogger::getInstance();
        
        // Ensure backup directory exists
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
            // Protect backup directory
            file_put_contents($this->backup_dir . '/.htaccess', "deny from all\n");
            file_put_contents($this->backup_dir . '/index.php', "<?php // Silence is golden");
        }
        
        // Schedule backup cron if not already scheduled
        if (!wp_next_scheduled('bocconi_security_backup')) {
            $interval = get_option('bocconi_backup_interval', 'daily');
            wp_schedule_event(time(), $interval, 'bocconi_security_backup');
        }
        
        add_action('bocconi_security_backup', array($this, 'run_scheduled_backup'));
    }
    
    public function run_scheduled_backup() {
        if (!get_option('bocconi_backup_enabled', false)) {
            return;
        }
        
        $this->logger->log_security_event('BACKUP_STARTED', array('type' => 'scheduled'));
        
        try {
            $backup_file = $this->create_backup();
            if ($backup_file) {
                $this->cleanup_old_backups();
                $this->logger->log_security_event('BACKUP_COMPLETED', array(
                    'file' => basename($backup_file),
                    'size' => filesize($backup_file)
                ));
            }
        } catch (Exception $e) {
            $this->logger->log_security_event('BACKUP_FAILED', array(
                'error' => $e->getMessage()
            ), 'error');
        }
    }
    
    public function create_backup() {
        $timestamp = date('Y-m-d_H-i-s');
        $backup_file = $this->backup_dir . "/backup_{$timestamp}.zip";
        
        // Create ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('Cannot create backup file');
        }
        
        // Add WordPress files (excluding some directories)
        $this->add_directory_to_zip($zip, ABSPATH, '', array(
            'wp-content/cache',
            'wp-content/uploads/cache',
            'wp-content/bocconi-backups',
            '.git',
            'node_modules'
        ));
        
        // Add database dump
        $db_dump = $this->create_database_dump();
        if ($db_dump) {
            $zip->addFromString('database.sql', $db_dump);
        }
        
        $zip->close();
        
        return $backup_file;
    }
    
    private function add_directory_to_zip($zip, $dir, $zip_dir = '', $exclude = array()) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $file_path = $file->getRealPath();
            $relative_path = substr($file_path, strlen(ABSPATH));
            
            // Skip excluded directories
            $skip = false;
            foreach ($exclude as $exclude_path) {
                if (strpos($relative_path, $exclude_path) === 0) {
                    $skip = true;
                    break;
                }
            }
            
            if ($skip) continue;
            
            if ($file->isDir()) {
                $zip->addEmptyDir($zip_dir . $relative_path);
            } else if ($file->isFile()) {
                $zip->addFile($file_path, $zip_dir . $relative_path);
            }
        }
    }
    
    private function create_database_dump() {
        global $wpdb;
        
        $dump = "-- WordPress Database Backup\n";
        $dump .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
        
        // Get all tables
        $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        
        foreach ($tables as $table) {
            $table_name = $table[0];
            
            // Get table structure
            $create_table = $wpdb->get_row("SHOW CREATE TABLE `{$table_name}`", ARRAY_N);
            $dump .= "\n-- Table structure for `{$table_name}`\n";
            $dump .= "DROP TABLE IF EXISTS `{$table_name}`;\n";
            $dump .= $create_table[1] . ";\n\n";
            
            // Get table data
            $rows = $wpdb->get_results("SELECT * FROM `{$table_name}`", ARRAY_A);
            if (!empty($rows)) {
                $dump .= "-- Data for table `{$table_name}`\n";
                foreach ($rows as $row) {
                    $values = array();
                    foreach ($row as $value) {
                        $values[] = is_null($value) ? 'NULL' : "'" . $wpdb->_escape($value) . "'";
                    }
                    $dump .= "INSERT INTO `{$table_name}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $dump .= "\n";
            }
        }
        
        return $dump;
    }
    
    private function cleanup_old_backups() {
        $max_backups = get_option('bocconi_backup_retention', 7);
        $files = glob($this->backup_dir . '/backup_*.zip');
        
        if (count($files) > $max_backups) {
            // Sort by modification time (oldest first)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remove oldest files
            $to_remove = array_slice($files, 0, count($files) - $max_backups);
            foreach ($to_remove as $file) {
                unlink($file);
            }
        }
    }
    
    public function get_backup_list() {
        $files = glob($this->backup_dir . '/backup_*.zip');
        $backups = array();
        
        foreach ($files as $file) {
            $backups[] = array(
                'name' => basename($file),
                'size' => filesize($file),
                'date' => filemtime($file),
                'path' => $file
            );
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        return $backups;
    }
    
    public function delete_backup($filename) {
        $file_path = $this->backup_dir . '/' . $filename;
        
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Security check - ensure filename is valid backup file
        if (!preg_match('/^backup-\d{4}-\d{2}-\d{2}-\d{6}\.zip$/', $filename)) {
            return false;
        }
        
        $result = unlink($file_path);
        
        if ($result) {
            $this->logger->log_security_event('BACKUP_DELETED', array(
                'filename' => $filename
            ));
        }
        
        return $result;
    }
    
    public function get_backup_settings() {
        return array(
            'enabled' => get_option('bocconi_backup_enabled', false),
            'interval' => get_option('bocconi_backup_interval', 'daily'),
            'retention_days' => get_option('bocconi_backup_retention', 7),
            'include_database' => get_option('bocconi_backup_include_db', true),
            'include_uploads' => get_option('bocconi_backup_include_uploads', true),
            'next_backup' => wp_next_scheduled('bocconi_security_backup')
        );
    }
    
    public function create_manual_backup() {
        $this->logger->log_security_event('MANUAL_BACKUP_STARTED', array());
        
        try {
            $backup_file = $this->create_backup();
            
            if ($backup_file) {
                $this->logger->log_security_event('MANUAL_BACKUP_COMPLETED', array(
                    'file' => basename($backup_file),
                    'size' => filesize($backup_file)
                ));
                
                return array(
                    'success' => true,
                    'message' => 'Backup creato con successo',
                    'file' => basename($backup_file),
                    'size' => $this->format_bytes(filesize($backup_file))
                );
            } else {
                throw new Exception('Errore durante la creazione del backup');
            }
        } catch (Exception $e) {
            $this->logger->log_security_event('MANUAL_BACKUP_FAILED', array(
                'error' => $e->getMessage()
            ), 'error');
            
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    public function list_backups() {
        $backups = array();
        
        if (!is_dir($this->backup_dir)) {
            return $backups;
        }
        
        $files = glob($this->backup_dir . '/backup-*.zip');
        
        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = array(
                'filename' => $filename,
                'size' => $this->format_bytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file)
            );
        }
        
        // Sort by date descending
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        return $backups;
    }
    
    private function format_bytes($size, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    public function update_backup_settings($settings) {
        update_option('bocconi_backup_enabled', $settings['enabled']);
        update_option('bocconi_backup_interval', $settings['interval']);
        update_option('bocconi_backup_retention', $settings['retention']);
        
        // Reschedule cron if interval changed
        wp_clear_scheduled_hook('bocconi_security_backup');
        if ($settings['enabled']) {
            wp_schedule_event(time(), $settings['interval'], 'bocconi_security_backup');
        }
        
        $this->logger->log_security_event('BACKUP_SETTINGS_UPDATED', $settings);
    }
}