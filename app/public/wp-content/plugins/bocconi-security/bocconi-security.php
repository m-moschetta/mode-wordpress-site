<?php
/**
 * Plugin Name: Bocconi Security
 * Plugin URI: https://bocconi.it
 * Description: Plugin di sicurezza personalizzato per Bocconi basato su OWASP ASVS
 * Version: 1.0.0
 * Author: Mario Moschetta
 * License: GPL v2 or later
 * Text Domain: bocconi-security
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BOCCONI_SECURITY_VERSION', '1.0.0');
define('BOCCONI_SECURITY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BOCCONI_SECURITY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Bootstrap plugin classes
require_once BOCCONI_SECURITY_PLUGIN_DIR . 'includes/class-security-config.php';
require_once BOCCONI_SECURITY_PLUGIN_DIR . 'includes/class-security-logger.php';
require_once BOCCONI_SECURITY_PLUGIN_DIR . 'includes/class-security-implementation.php';
require_once BOCCONI_SECURITY_PLUGIN_DIR . 'includes/class-admin-dashboard.php';
require_once BOCCONI_SECURITY_PLUGIN_DIR . 'includes/class-backup-manager.php';
require_once BOCCONI_SECURITY_PLUGIN_DIR . 'includes/class-file-integrity-monitor.php';

class BocconiSecurity {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Delegate runtime hooks to BocconiSecurityImplementation singleton
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize components
        BocconiBackupManager::getInstance();
        BocconiFileIntegrityMonitor::getInstance();
    }
    
    public function init() {
        // V1 - Architecture, Design and Threat Modeling
        $this->implement_architecture_security();
        
        // V2 - Authentication
        $this->implement_authentication_security();
        
        // V3 - Session Management
        $this->implement_session_security();
        
        // V4 - Access Control
        $this->implement_access_control();
        
        // V5 - Validation, Sanitization and Encoding
        $this->implement_validation_security();
        
        // V7 - Error Handling and Logging
        $this->implement_error_handling();
        
        // V9 - Communications
        $this->implement_communication_security();
        
        // V10 - Malicious Code
        $this->implement_malicious_code_protection();
        
        // V11 - Business Logic
        $this->implement_business_logic_security();
        
        // V12 - File and Resources
        $this->implement_file_security();
        
        // V13 - API and Web Service
        $this->implement_api_security();
        
        // V14 - Configuration
        $this->implement_configuration_security();
    }
    
    // V1 - Architecture, Design and Threat Modeling
    private function implement_architecture_security() {
        // Hide WordPress version
        remove_action('wp_head', 'wp_generator');
        add_filter('the_generator', '__return_empty_string');
        
        // Remove unnecessary meta tags
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        
        // Disable pingbacks
        add_filter('xmlrpc_methods', array($this, 'disable_xmlrpc_pingback'));
        
        // Security headers handled by Implementation class
    }
    
    // V2 - Authentication
    private function implement_authentication_security() {
        // Strong password enforcement
        add_action('user_profile_update_errors', array($this, 'validate_password_strength'), 0, 3);
    }
    
    // V3 - Session Management
    private function implement_session_security() {
        // Delegated to BocconiSecurityImplementation::implement_session_security
    }
    
    // V4 - Access Control
    private function implement_access_control() {
        // Respect DISALLOW_FILE_EDIT via wp-config.php; plugin does not enforce this at runtime.
        
        // Delegated to BocconiSecurityImplementation::implement_access_control
    }
    
    // V5 - Validation, Sanitization and Encoding
    private function implement_validation_security() {
        // Input validation
        add_action('init', array($this, 'validate_inputs'));
        
        // Output encoding
        add_filter('the_content', array($this, 'secure_output'));
        add_filter('comment_text', array($this, 'secure_output'));
    }
    
    // V7 - Error Handling and Logging
    private function implement_error_handling() {
        // Custom error logging is handled by Implementation class
        
        // Security event logging
        // add_action('init', array($this, 'setup_security_logging'));
    }
    
    // V9 - Communications
    private function implement_communication_security() {
        // Force HTTPS
        // add_action('template_redirect', array($this, 'force_https'));
        
        // Secure cookies
        // add_action('init', array($this, 'secure_cookies'));
    }
    
    // V10 - Malicious Code
    private function implement_malicious_code_protection() {
        // File upload security
        // Upload MIME restrictions handled by Implementation class
        // add_filter('wp_handle_upload_prefilter', array($this, 'scan_uploaded_files'));
        
        // Code injection prevention
        add_action('init', array($this, 'prevent_code_injection'));
    }
    
    // V11 - Business Logic
    private function implement_business_logic_security() {
        // Delegated to BocconiSecurityImplementation::implement_rate_limiting and related checks
    }
    
    // V12 - File and Resources
    private function implement_file_security() {
        // Delegated to BocconiSecurityImplementation::implement_file_security
    }
    
    // V13 - API and Web Service
    private function implement_api_security() {
        // Delegated to BocconiSecurityImplementation::implement_api_security
    }
    
    // V14 - Configuration
    private function implement_configuration_security() {
        // Secure configuration
        add_action('init', array($this, 'secure_configuration'));
        
        // Environment-specific settings
        add_action('init', array($this, 'environment_security'));
    }
    
    // Security check methods
    public function security_checks() {
        $this->check_file_permissions();
        $this->check_suspicious_activity();
        $this->monitor_admin_access();
    }
    
    // Implementation methods
    public function disable_xmlrpc_pingback($methods) {
        unset($methods['pingback.ping']);
        unset($methods['pingback.extensions.getPingbacks']);
        return $methods;
    }
    
    public function add_security_headers() {
        // Delegated to Implementation::add_security_headers
    }
    
    public function login_failed($username) {
        // Delegated to Implementation::handle_failed_login
    }
    
    public function check_login_attempts($user, $username, $password) {
        // Delegated to Implementation::check_login_attempts
        return $user;
    }
    
    public function validate_password_strength($errors, $update, $user) {
        $password = $_POST['pass1'] ?? '';
        
        if (!empty($password)) {
            if (strlen($password) < 12) {
                $errors->add('password_too_short', 'La password deve essere di almeno 12 caratteri.');
            }
            
            if (!preg_match('/[A-Z]/', $password)) {
                $errors->add('password_no_uppercase', 'La password deve contenere almeno una lettera maiuscola.');
            }
            
            if (!preg_match('/[a-z]/', $password)) {
                $errors->add('password_no_lowercase', 'La password deve contenere almeno una lettera minuscola.');
            }
            
            if (!preg_match('/[0-9]/', $password)) {
                $errors->add('password_no_number', 'La password deve contenere almeno un numero.');
            }
            
            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $errors->add('password_no_special', 'La password deve contenere almeno un carattere speciale.');
            }
        }
    }
    
    public function hide_login_errors($error) {
        // Delegated to Implementation::generic_login_error via filter when enabled
        return $error;
    }
    
    public function secure_session_config() {
        // Session hardening is handled by WordPress auth cookies and the Implementation class.
    }
    
    public function block_suspicious_requests() {
        // Delegate to Implementation
        BocconiSecurityImplementation::getInstance()->block_suspicious_requests();
    }
    
    public function force_https() {
        // Delegated to Implementation::force_https_redirect based on environment and option
    }
    
    public function restrict_upload_mimes($mimes) {
        // Delegated to Implementation::restrict_upload_mimes
        return $mimes;
    }
    
    public function scan_uploaded_files($file) {
        // Delegated to Implementation::validate_file_upload
        return $file;
    }
    
    public function secure_rest_api($result) {
        // Delegated to Implementation::restrict_api_access via implement_api_security
        return $result;
    }
    
    // Utility methods
    private function get_client_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    private function log_security_event($event, $details = '') {
        $log_entry = date('[Y-m-d H:i:s]') . ' SECURITY: ' . $event;
        if ($details) {
            $log_entry .= ' - ' . $details;
        }
        $log_entry .= ' - IP: ' . $this->get_client_ip() . PHP_EOL;
        
        error_log($log_entry, 3, WP_CONTENT_DIR . '/security.log');
    }
    
    // Placeholder methods for additional security features
    private function check_file_permissions() {
        // Implementation for file permission checks
    }
    
    private function check_suspicious_activity() {
        // Implementation for suspicious activity monitoring
    }
    
    private function monitor_admin_access() {
        // Implementation for admin access monitoring
    }
    
    /* log_successful_login removed: handled by Implementation::handle_successful_login */
    
    public function log_failed_login($username) {
        // Delegated to Implementation::handle_failed_login
    }
    
    // Additional placeholder methods
    public function set_session_timeout() {}
    public function check_session_timeout() {}
    public function admin_security_checks() {}
    public function validate_inputs() {}
    public function secure_output($content) { return $content; }
    public function setup_security_logging() {}
    public function secure_cookies() {}
    public function prevent_code_injection() {}
    public function implement_rate_limiting() {}
    public function validate_business_logic() {}
    public function secure_file_access() {}
    public function prevent_directory_traversal() {}
    public function api_rate_limiting() {}
    public function secure_configuration() {}
    public function environment_security() {}
    
    // Plugin activation/deactivation
    public function activate() {
        $this->create_database_tables();
        $this->log_security_event('PLUGIN_ACTIVATED', 'Bocconi Security Plugin activated');
    }
    
    private function create_database_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bocconi_security_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            datetime datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            level varchar(20) DEFAULT 'info' NOT NULL,
            type varchar(100) NOT NULL,
            ip varchar(45) NOT NULL,
            user_id bigint(20) UNSIGNED NULL,
            data longtext NULL,
            PRIMARY KEY (id),
            KEY level (level),
            KEY type (type),
            KEY datetime (datetime),
            KEY ip (ip)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function deactivate() {
        $this->log_security_event('PLUGIN_DEACTIVATED', 'Bocconi Security Plugin deactivated');
    }
}

// Initialize the plugin
BocconiSecurity::getInstance();

?>