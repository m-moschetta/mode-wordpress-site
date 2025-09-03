<?php
/**
 * Security Implementation Class
 * 
 * Implementa tutte le funzionalità di sicurezza basate su OWASP ASVS
 */

if (!defined('ABSPATH')) {
    exit;
}

class BocconiSecurityImplementation {
    
    private static $instance = null;
    private $config;
    private $logger;
    private $failed_attempts = array();
    private $blocked_ips = array();
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->config = BocconiSecurityConfig::getInstance();
        $this->logger = BocconiSecurityLogger::getInstance();
        $this->init_security_measures();
    }
    
    private function init_security_measures() {
        // General Security
        add_action('wp_loaded', array($this, 'check_security_threats'));
        // V1 - Architecture Security
        add_action('init', array($this, 'implement_architecture_security'));
        
        // V2 - Authentication
        add_action('wp_login_failed', array($this, 'handle_failed_login'));
        add_filter('authenticate', array($this, 'check_login_attempts'), 30, 3);
        add_action('wp_login', array($this, 'handle_successful_login'), 10, 2);
        
        // V3 - Session Management
        add_action('init', array($this, 'implement_session_security'));
        add_action('wp_logout', array($this, 'secure_logout'));
        
        // V4 - Access Control
        add_action('init', array($this, 'implement_access_control'));
        
        // V5 - Input Validation
        add_action('init', array($this, 'implement_input_validation'));
        
        // V7 - Error Handling
        add_action('init', array($this, 'implement_error_handling'));
        
        // V9 - Communications
        add_action('init', array($this, 'implement_communication_security'));
        
        // V10 - Malicious Code Protection
        add_filter('wp_handle_upload_prefilter', array($this, 'validate_file_upload'));
        add_filter('upload_mimes', array($this, 'restrict_upload_mimes'));
        add_action('init', array($this, 'implement_malware_protection'));
        
        // V11 - Business Logic
        add_action('init', array($this, 'implement_rate_limiting'));
        
        // V12 - File Security
        add_action('init', array($this, 'implement_file_security'));
        
        // V13 - API Security
        add_action('rest_api_init', array($this, 'implement_api_security'));
        
        // V14 - Configuration
        add_action('init', array($this, 'implement_configuration_security'));
        
        // Security Headers
        add_action('send_headers', array($this, 'add_security_headers'));
    }
    
    // V1 - Architecture Security Implementation
    public function implement_architecture_security() {
        if ($this->config->get_option('hide_wp_version')) {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', '__return_empty_string');
        }
        
        if ($this->config->get_option('disable_xmlrpc')) {
            add_filter('xmlrpc_enabled', '__return_false');
            add_filter('wp_headers', array($this, 'remove_xmlrpc_pingback_header'));
        }
        
        if ($this->config->get_option('remove_meta_tags')) {
            remove_action('wp_head', 'wp_shortlink_wp_head');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'rsd_link');
        }
    }
    
    public function remove_xmlrpc_pingback_header($headers) {
        unset($headers['X-Pingback']);
        return $headers;
    }
    
    // V2 - Authentication Implementation
    public function handle_failed_login($username) {
        $ip = $this->get_client_ip();
        $attempts_key = 'login_attempts_' . md5($ip);
        $lockout_key = 'login_lockout_' . md5($ip);
        
        // Check if already locked out
        if (get_transient($lockout_key)) {
            return;
        }
        
        $attempts = get_transient($attempts_key);
        $attempts = $attempts ? $attempts + 1 : 1;
        
        set_transient($attempts_key, $attempts, 3600); // 1 hour
        
        $this->logger->log_security_event('failed_login', array(
            'username' => $username,
            'ip' => $ip,
            'attempts' => $attempts
        ));
        
        if ($attempts >= $this->config->get_option('login_attempts_limit')) {
            $lockout_duration = $this->config->get_option('login_lockout_duration');
            set_transient($lockout_key, true, $lockout_duration);
            
            $this->logger->log_security_event('login_lockout', array(
                'ip' => $ip,
                'duration' => $lockout_duration
            ));
            
            if ($this->config->get_option('email_alerts')) {
                $this->send_security_alert('Login Lockout', "IP {$ip} è stato bloccato per troppi tentativi di login falliti.");
            }
        }
    }
    
    public function check_login_attempts($user, $username, $password) {
        if (empty($username) || empty($password)) {
            return $user;
        }
        
        $ip = $this->get_client_ip();
        $lockout_key = 'login_lockout_' . md5($ip);
        
        if (get_transient($lockout_key)) {
            return new WP_Error('login_locked', 'Account temporaneamente bloccato per troppi tentativi falliti.');
        }
        
        return $user;
    }
    
    public function handle_successful_login($user_login, $user) {
        $ip = $this->get_client_ip();
        $attempts_key = 'login_attempts_' . md5($ip);
        $lockout_key = 'login_lockout_' . md5($ip);
        
        // Clear failed attempts on successful login
        delete_transient($attempts_key);
        delete_transient($lockout_key);
        
        $this->logger->log_security_event('successful_login', array(
            'username' => $user_login,
            'ip' => $ip,
            'user_id' => $user->ID
        ));
    }
    
    // V3 - Session Management Implementation
    public function implement_session_security() {
        if ($this->config->get_option('secure_sessions')) {
            // Force secure session cookies
            add_filter('secure_auth_cookie', '__return_true');
            add_filter('secure_logged_in_cookie', '__return_true');
            
            // Set session timeout
            add_filter('auth_cookie_expiration', array($this, 'set_session_timeout'));
        }
        
        if ($this->config->get_option('force_logout_idle')) {
            add_action('wp_login', array($this, 'set_user_last_activity'));
            add_action('init', array($this, 'check_user_activity'));
        }
    }
    
    public function set_session_timeout($expiration) {
        return $this->config->get_option('session_timeout', 3600);
    }
    
    public function set_user_last_activity($user_login) {
        $user = get_user_by('login', $user_login);
        if ($user) {
            update_user_meta($user->ID, 'last_activity', time());
        }
    }
    
    public function check_user_activity() {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $last_activity = get_user_meta($user_id, 'last_activity', true);
            $timeout = $this->config->get_option('session_timeout', 3600);
            
            if ($last_activity && (time() - $last_activity) > $timeout) {
                wp_logout();
                wp_redirect(wp_login_url() . '?timeout=1');
                exit;
            }
            
            // Update last activity
            update_user_meta($user_id, 'last_activity', time());
        }
    }
    
    public function secure_logout() {
        // Clear all user sessions
        $user_id = get_current_user_id();
        if ($user_id) {
            $sessions = WP_Session_Tokens::get_instance($user_id);
            $sessions->destroy_all();
        }
    }
    
    // V4 - Access Control Implementation
    public function implement_access_control() {
        if ($this->config->get_option('disable_file_editing')) {
            // Respect setting by advising via admin notice; do not define constant at runtime
            if (is_admin() && current_user_can('manage_options')) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-warning"><p>Per disabilitare l\'editor dei file in modo sicuro, definire DISALLOW_FILE_EDIT in wp-config.php.</p></div>';
                });
            }
        }
        
        if ($this->config->get_option('block_suspicious_requests')) {
            add_action('init', array($this, 'block_suspicious_requests'));
        }
        
        if ($this->config->get_option('restrict_admin_access')) {
            add_action('admin_init', array($this, 'restrict_admin_access'));
        }
    }
    
    public function block_suspicious_requests() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Block common attack patterns
        $suspicious_patterns = array(
            '/wp-config\.php/',
            '/\.\.\//',
            '/eval\s*\(/',
            '/base64_decode/',
            '/UNION.*SELECT/',
            '/script.*alert/',
            '/<script/',
            '/javascript:/',
            '/vbscript:/',
            '/onload\s*=/',
            '/onerror\s*=/'
        );
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $request_uri) || preg_match($pattern, $user_agent)) {
                $this->logger->log_security_event('suspicious_request_blocked', array(
                    'ip' => $this->get_client_ip(),
                    'request_uri' => $request_uri,
                    'user_agent' => $user_agent,
                    'pattern' => $pattern
                ));
                
                wp_die('Richiesta bloccata per motivi di sicurezza.', 'Accesso Negato', array('response' => 403));
            }
        }
    }
    
    public function restrict_admin_access() {
        $whitelist = $this->config->get_option('admin_ip_whitelist', array());
        if (!empty($whitelist)) {
            $client_ip = $this->get_client_ip();
            if (!in_array($client_ip, $whitelist)) {
                $this->logger->log_security_event('admin_access_denied', array(
                    'ip' => $client_ip
                ));
                wp_die('Accesso amministratore non autorizzato.', 'Accesso Negato', array('response' => 403));
            }
        }
    }
    
    // V5 - Input Validation Implementation
    public function implement_input_validation() {
        if ($this->config->get_option('input_validation')) {
            add_action('init', array($this, 'validate_all_inputs'));
        }
        
        if ($this->config->get_option('xss_protection')) {
            add_filter('pre_comment_content', array($this, 'sanitize_comment_content'));
            add_filter('content_save_pre', array($this, 'sanitize_post_content'));
        }
    }
    
    public function validate_all_inputs() {
        // Validate GET parameters
        foreach ($_GET as $key => $value) {
            $_GET[$key] = $this->sanitize_input($value);
        }
        
        // Validate POST parameters
        foreach ($_POST as $key => $value) {
            if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'wp_rest')) {
                $_POST[$key] = $this->sanitize_input($value);
            }
        }
    }
    
    private function sanitize_input($input) {
        if (is_array($input)) {
            return array_map(array($this, 'sanitize_input'), $input);
        }
        
        // Remove dangerous characters and patterns
        $input = strip_tags($input);
        $input = preg_replace('/[<>"\']/', '', $input);
        $input = preg_replace('/javascript:/i', '', $input);
        $input = preg_replace('/vbscript:/i', '', $input);
        $input = preg_replace('/on\w+\s*=/i', '', $input);
        
        return sanitize_text_field($input);
    }
    
    public function sanitize_comment_content($content) {
        return wp_kses($content, array(
            'p' => array(),
            'br' => array(),
            'strong' => array(),
            'em' => array(),
            'a' => array('href' => array())
        ));
    }
    
    public function sanitize_post_content($content) {
        // Allow more tags for post content but still sanitize
        return wp_kses_post($content);
    }
    
    // V7 - Error Handling Implementation
    public function implement_error_handling() {
        if ($this->config->get_option('hide_login_errors')) {
            add_filter('login_errors', array($this, 'generic_login_error'));
        }
        
        // Hide PHP errors in production
        if (!WP_DEBUG) {
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        }
    }
    
    public function generic_login_error() {
        return 'Credenziali non valide.';
    }
    
    // V9 - Communication Security Implementation
    public function implement_communication_security() {
        if ($this->config->get_option('force_https')) {
            add_action('template_redirect', array($this, 'force_https_redirect'));
        }
        
        if ($this->config->get_option('secure_cookies')) {
            add_filter('secure_auth_cookie', '__return_true');
            add_filter('secure_logged_in_cookie', '__return_true');
        }
    }
    
    public function force_https_redirect() {
        $env = function_exists('wp_get_environment_type') ? wp_get_environment_type() : (defined('WP_ENV') ? constant('WP_ENV') : 'production');
        if ($env !== 'production') {
            return; // Do not enforce HTTPS outside production
        }
        if (defined('WP_CLI') && constant('WP_CLI')) {
            return; // Skip in CLI
        }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return; // Skip AJAX
        }
        if (!is_ssl() && !is_admin()) {
            wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
            exit;
        }
    }
    
    public function add_security_headers() {
        if ($this->config->get_option('security_headers')) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            // Removed deprecated X-XSS-Protection header
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
            
            // Add a conservative CSP in Report-Only to avoid breakage; site owners can harden later
            $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' https:; frame-ancestors 'self'";
            header('Content-Security-Policy-Report-Only: ' . $csp);
            
            // HSTS only in production and when SSL is active and option enabled
            $env = function_exists('wp_get_environment_type') ? wp_get_environment_type() : (defined('WP_ENV') ? constant('WP_ENV') : 'production');
            if ($this->config->get_option('hsts_enabled') && is_ssl() && $env === 'production') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            }
        }
    }
    
    // V10 - Malicious Code Protection Implementation
    public function validate_file_upload($file) {
        if (!$this->config->get_option('file_upload_restrictions')) {
            return $file;
        }
        
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt');
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $file['error'] = 'Tipo di file non consentito per motivi di sicurezza.';
            return $file;
        }
        
        // Check file content for malicious code
        if ($this->contains_malicious_code($file['tmp_name'])) {
            $file['error'] = 'File bloccato: contiene codice potenzialmente pericoloso.';
            
            $this->logger->log_security_event('malicious_file_upload_blocked', array(
                'filename' => $file['name'],
                'ip' => $this->get_client_ip()
            ));
        }
        
        return $file;
    }

    /**
     * Restrict allowed upload mime types to a conservative whitelist and drop dangerous types.
     * This runs even if other plugins alter $mimes, but is gated by the file_upload_restrictions option.
     *
     * @param array $mimes
     * @return array
     */
    public function restrict_upload_mimes($mimes) {
        if (!$this->config->get_option('file_upload_restrictions')) {
            return $mimes;
        }
        
        // Remove known dangerous extensions if present
        $dangerous = array('exe', 'php', 'php3', 'php4', 'php5', 'phtml', 'js', 'swf', 'sh', 'bat', 'cmd', 'com', 'msi', 'msp', 'scr', 'jar', 'pl', 'py', 'cgi', 'phar');
        foreach ($dangerous as $ext) {
            unset($mimes[$ext]);
        }
        
        // Conservative whitelist: keep only common safe types that are already allowed
        $whitelist = array(
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'zip' => 'application/zip'
        );
        $filtered = array();
        foreach ($whitelist as $ext => $type) {
            if (isset($mimes[$ext])) {
                $filtered[$ext] = $mimes[$ext]; // preserve any site-specific mime mapping
            }
        }
        
        return !empty($filtered) ? $filtered : $mimes;
    }
    
    private function contains_malicious_code($file_path) {
        $content = file_get_contents($file_path, false, null, 0, 8192); // Read first 8KB
        
        $malicious_patterns = array(
            '/<\?php/',
            '/eval\s*\(/',
            '/base64_decode/',
            '/shell_exec/',
            '/system\s*\(/',
            '/exec\s*\(/',
            '/passthru\s*\(/',
            '/file_get_contents\s*\(/',
            '/fopen\s*\(/',
            '/fwrite\s*\(/',
            '/curl_exec\s*\(/'
        );
        
        foreach ($malicious_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function implement_malware_protection() {
        if ($this->config->get_option('malware_scanning')) {
            add_action('wp_loaded', array($this, 'scan_for_malware'));
        }
    }
    
    public function scan_for_malware() {
        // Basic malware scanning - check for suspicious files
        $suspicious_files = array(
            ABSPATH . 'wp-config-backup.php',
            ABSPATH . 'wp-config-sample.php.bak',
            ABSPATH . '.htaccess.bak'
        );
        
        foreach ($suspicious_files as $file) {
            if (file_exists($file)) {
                $this->logger->log_security_event('suspicious_file_detected', array(
                    'file' => $file
                ));
            }
        }
    }
    
    // V11 - Business Logic Implementation
    public function implement_rate_limiting() {
        if ($this->config->get_option('rate_limiting')) {
            add_action('init', array($this, 'check_rate_limits'));
        }
    }
    
    public function check_rate_limits() {
        $ip = $this->get_client_ip();
        $current_time = time();
        $window = 3600; // 1 hour
        
        // API rate limiting
        if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            $api_key = 'api_requests_' . md5($ip);
            $api_requests = get_transient($api_key) ?: 0;
            $api_limit = $this->config->get_option('api_rate_limit', 100);
            
            if ($api_requests >= $api_limit) {
                $this->logger->log_security_event('api_rate_limit_exceeded', array(
                    'ip' => $ip,
                    'requests' => $api_requests
                ));
                
                wp_die('Rate limit superato. Riprova più tardi.', 'Too Many Requests', array('response' => 429));
            }
            
            set_transient($api_key, $api_requests + 1, $window);
        }
        
        // Form submission rate limiting
        if ($_POST && !is_admin()) {
            $form_key = 'form_submissions_' . md5($ip);
            $form_submissions = get_transient($form_key) ?: 0;
            $form_limit = $this->config->get_option('form_rate_limit', 10);
            
            if ($form_submissions >= $form_limit) {
                $this->logger->log_security_event('form_rate_limit_exceeded', array(
                    'ip' => $ip,
                    'submissions' => $form_submissions
                ));
                
                wp_die('Troppi invii di form. Riprova più tardi.', 'Too Many Requests', array('response' => 429));
            }
            
            set_transient($form_key, $form_submissions + 1, 600); // 10 minutes
        }
    }
    
    // V12 - File Security Implementation
    public function implement_file_security() {
        if ($this->config->get_option('file_access_control')) {
            add_action('init', array($this, 'protect_sensitive_files'));
        }
        
        if ($this->config->get_option('directory_traversal_protection')) {
            add_action('init', array($this, 'prevent_directory_traversal'));
        }
    }
    
    public function protect_sensitive_files() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        $protected_files = array(
            '/wp-config.php',
            '/.htaccess',
            '/wp-config-sample.php',
            '/readme.html',
            '/license.txt'
        );
        
        foreach ($protected_files as $file) {
            if (strpos($request_uri, $file) !== false) {
                $this->logger->log_security_event('protected_file_access_attempt', array(
                    'file' => $file,
                    'ip' => $this->get_client_ip()
                ));
                
                wp_die('Accesso al file non autorizzato.', 'Forbidden', array('response' => 403));
            }
        }
    }
    
    public function prevent_directory_traversal() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        if (preg_match('/\.\.[\/\\]/', $request_uri)) {
            $this->logger->log_security_event('directory_traversal_attempt', array(
                'request_uri' => $request_uri,
                'ip' => $this->get_client_ip()
            ));
            
            wp_die('Tentativo di directory traversal bloccato.', 'Forbidden', array('response' => 403));
        }
    }
    
    // V13 - API Security Implementation
    public function implement_api_security() {
        if ($this->config->get_option('rest_api_security')) {
            add_filter('rest_authentication_errors', array($this, 'restrict_api_access'));
        }
        
        if ($this->config->get_option('api_authentication_required')) {
            add_filter('rest_pre_dispatch', array($this, 'require_api_authentication'), 10, 3);
        }
    }
    
    public function restrict_api_access($access) {
        // Be conservative: allow public GET for common content, require auth for write operations
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        // If it's a GET request, allow and let core permission callbacks handle sensitive data
        if ($method === 'GET') {
            return $access;
        }
        
        // For non-GET methods, require authentication on common content routes
        $protected_prefixes = array(
            '/wp-json/wp/v2/posts',
            '/wp-json/wp/v2/pages',
            '/wp-json/wp/v2/media',
        );
        
        foreach ($protected_prefixes as $prefix) {
            if (strpos($request_uri, $prefix) !== false && !is_user_logged_in()) {
                return new WP_Error('rest_forbidden', 'Autenticazione richiesta per questa operazione.', array('status' => 401));
            }
        }
        
        // Extra hardening for users endpoints (even though core already restricts these)
        if (strpos($request_uri, '/wp-json/wp/v2/users') !== false && !is_user_logged_in()) {
            return new WP_Error('rest_forbidden', 'Autenticazione richiesta per l\'endpoint utenti.', array('status' => 401));
        }
        
        return $access;
    }
    
    public function require_api_authentication($result, $server, $request) {
        if (!is_user_logged_in() && $request->get_method() !== 'GET') {
            return new WP_Error('rest_forbidden', 'Autenticazione richiesta.', array('status' => 401));
        }
        
        return $result;
    }
    
    // V14 - Configuration Security Implementation
    public function implement_configuration_security() {
        if ($this->config->get_option('debug_mode_protection')) {
            add_action('init', array($this, 'protect_debug_mode'));
        }
        
        if ($this->config->get_option('environment_hardening')) {
            add_action('init', array($this, 'harden_environment'));
        }
    }
    
    public function protect_debug_mode() {
        if (WP_DEBUG && !current_user_can('administrator')) {
            // Hide debug info from non-administrators
            ini_set('display_errors', 0);
        }
    }
    
    public function harden_environment() {
        // Remove WordPress version from various places
        remove_action('wp_head', 'wp_generator');
        
        // Disable pingbacks
        add_filter('xmlrpc_methods', function($methods) {
            unset($methods['pingback.ping']);
            return $methods;
        });
        
        // Remove unnecessary HTTP headers
        add_filter('wp_headers', function($headers) {
            unset($headers['X-Pingback']);
            return $headers;
        });
    }
    
    // Additional Security Methods
    public function check_security_threats() {
        $this->monitor_suspicious_activity();
        $this->check_file_integrity();
        $this->validate_request_patterns();
    }
    
    private function monitor_suspicious_activity() {
        $ip = $this->get_client_ip();
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Monitor for bot activity
        $bot_patterns = array('bot', 'crawler', 'spider', 'scraper');
        foreach ($bot_patterns as $pattern) {
            if (stripos($user_agent, $pattern) !== false) {
                $this->logger->log_security_event('bot_detected', array(
                    'ip' => $ip,
                    'user_agent' => $user_agent,
                    'pattern' => $pattern
                ));
                break;
            }
        }
    }
    
    private function check_file_integrity() {
        $critical_files = array(
            ABSPATH . 'wp-config.php',
            ABSPATH . '.htaccess',
            ABSPATH . 'index.php'
        );
        
        foreach ($critical_files as $file) {
            if (file_exists($file)) {
                $current_hash = md5_file($file);
                $stored_hash = get_option('bocconi_file_hash_' . md5($file));
                
                if ($stored_hash && $stored_hash !== $current_hash) {
                    $this->logger->log_security_event('file_integrity_violation', array(
                        'file' => basename($file),
                        'expected_hash' => $stored_hash,
                        'actual_hash' => $current_hash
                    ));
                    
                    if ($this->config->get_option('email_alerts')) {
                        $this->send_security_alert('File Integrity Alert', "Il file {$file} è stato modificato.");
                    }
                }
                
                update_option('bocconi_file_hash_' . md5($file), $current_hash);
            }
        }
    }
    
    private function validate_request_patterns() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $query_string = $_SERVER['QUERY_STRING'] ?? '';
        
        // Check for SQL injection patterns
        $sql_patterns = array(
            '/union\s+select/i',
            '/select\s+.*\s+from/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/drop\s+table/i',
            '/update\s+.*\s+set/i'
        );
        
        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $request_uri) || preg_match($pattern, $query_string)) {
                $this->logger->log_security_event('sql_injection_attempt', array(
                    'ip' => $this->get_client_ip(),
                    'request_uri' => $request_uri,
                    'query_string' => $query_string,
                    'pattern' => $pattern
                ));
                
                wp_die('Richiesta bloccata per motivi di sicurezza.', 'SQL Injection Detected', array('response' => 403));
            }
        }
    }
    
    // Metodi richiamati via AJAX dalla dashboard admin
    public function run_security_scan() {
        $issues = array();
        $checked = 0;
        
        // Controllo file sospetti
        $suspicious_files = array(
            ABSPATH . 'wp-config-backup.php',
            ABSPATH . 'wp-config-sample.php.bak',
            ABSPATH . '.htaccess.bak'
        );
        foreach ($suspicious_files as $file) {
            $checked++;
            if (file_exists($file)) {
                $issues[] = array(
                    'type' => 'suspicious_file',
                    'file' => $file
                );
            }
        }
        
        // HTTPS richiesto ma non attivo
        $checked++;
        if ($this->config->get_option('force_https') && !is_ssl()) {
            $issues[] = array(
                'type' => 'https',
                'message' => 'HTTPS non attivo ma richiesto dalla configurazione.'
            );
        }
        
        // WP_DEBUG attivo
        $checked++;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $issues[] = array(
                'type' => 'debug',
                'message' => 'WP_DEBUG è attivo (sconsigliato in produzione).'
            );
        }
        
        // Permessi wp-config.php
        $checked++;
        $wp_config = ABSPATH . 'wp-config.php';
        if (file_exists($wp_config)) {
            $perms = substr(sprintf('%o', fileperms($wp_config)), -4);
            if (!in_array($perms, array('0644', '0600'), true)) {
                $issues[] = array(
                    'type' => 'permissions',
                    'file' => 'wp-config.php',
                    'perms' => $perms
                );
            }
        }
        
        // Punteggio sicurezza dalla configurazione
        $score = null;
        if (method_exists($this->config, 'get_security_score')) {
            $score = $this->config->get_security_score();
        }
        
        return array(
            'timestamp' => current_time('mysql'),
            'checked' => $checked,
            'issues' => $issues,
            'score' => $score,
        );
    }
    
    public function run_security_tests() {
        $results = array(
            'logger_writable' => null,
            'wp_config_perms' => null,
            'https_enforced' => null,
            'session_timeout' => null,
        );
        
        // Verifica scrivibilità directory contenuti (per logger)
        $log_dir = WP_CONTENT_DIR;
        $results['logger_writable'] = is_writable($log_dir);
        
        // Permessi wp-config.php
        $wp_config = ABSPATH . 'wp-config.php';
        if (file_exists($wp_config)) {
            $perms = substr(sprintf('%o', fileperms($wp_config)), -4);
            $results['wp_config_perms'] = array('perms' => $perms, 'ok' => in_array($perms, array('0644', '0600'), true));
        } else {
            $results['wp_config_perms'] = array('perms' => null, 'ok' => false);
        }
        
        // HTTPS forzato
        $force_https = (bool) $this->config->get_option('force_https');
        $results['https_enforced'] = array('enabled' => $force_https, 'active' => is_ssl());
        
        // Timeout di sessione
        $timeout = (int) $this->config->get_option('session_timeout', 3600);
        $results['session_timeout'] = array('value' => $timeout, 'ok' => ($timeout >= 300 && $timeout <= 86400));
        
        // Riepilogo
        $passed = 0; $total = 0;
        foreach ($results as $key => $val) {
            $total++;
            $ok = false;
            if ($key === 'logger_writable') {
                $ok = (bool) $val;
            } elseif ($key === 'https_enforced') {
                $ok = (!$val['enabled']) || ($val['enabled'] && $val['active']);
            } elseif ($key === 'session_timeout') {
                $ok = (bool) $val['ok'];
            } elseif ($key === 'wp_config_perms') {
                $ok = (bool) $val['ok'];
            }
            if ($ok) { $passed++; }
        }
        
        return array(
            'timestamp' => current_time('mysql'),
            'passed' => $passed,
            'total' => $total,
            'results' => $results,
        );
    }
    
    // Utility Methods
    private function get_client_ip() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
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
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    private function send_security_alert($subject, $message) {
        $to = $this->config->get_option('alert_email');
        if ($to) {
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($to, '[Bocconi Security] ' . $subject, $message, $headers);
        }
    }
}

// Initialize the security implementation
BocconiSecurityImplementation::getInstance();

?>