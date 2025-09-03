<?php
/**
 * Security Configuration Class
 * 
 * Gestisce tutte le configurazioni di sicurezza del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class BocconiSecurityConfig {
    
    private static $instance = null;
    private $options;
    private $default_options;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->set_default_options();
        $this->load_options();
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }
    
    private function set_default_options() {
        $this->default_options = array(
            // V1 - Architecture Security
            'hide_wp_version' => true,
            'disable_xmlrpc' => true,
            'remove_meta_tags' => true,
            
            // V2 - Authentication
            'login_attempts_limit' => 5,
            'login_lockout_duration' => 1800, // 30 minutes
            'strong_passwords' => true,
            'two_factor_auth' => false,
            
            // V3 - Session Management
            'secure_sessions' => true,
            'session_timeout' => 3600, // 1 hour
            'force_logout_idle' => true,
            'session_regeneration' => true,
            
            // V4 - Access Control
            'disable_file_editing' => true,
            'block_suspicious_requests' => true,
            'restrict_admin_access' => false,
            'admin_ip_whitelist' => array(),
            
            // V5 - Input Validation
            'input_validation' => true,
            'xss_protection' => true,
            'sql_injection_protection' => true,
            'csrf_protection' => true,
            
            // V7 - Error Handling
            'hide_login_errors' => true,
            'custom_error_pages' => true,
            'error_logging' => true,
            
            // V9 - Communications
            'force_https' => true,
            'secure_cookies' => true,
            'security_headers' => true,
            'hsts_enabled' => true,
            
            // V10 - Malicious Code Protection
            'file_upload_restrictions' => true,
            'malware_scanning' => true,
            'code_injection_protection' => true,
            
            // V11 - Business Logic
            'rate_limiting' => true,
            'api_rate_limit' => 100, // per hour
            'form_rate_limit' => 10, // per 10 minutes
            
            // V12 - File Security
            'file_access_control' => true,
            'directory_traversal_protection' => true,
            'file_integrity_monitoring' => false,
            
            // V13 - API Security
            'rest_api_security' => true,
            'api_authentication_required' => true,
            'api_rate_limiting' => true,
            
            // V14 - Configuration
            'debug_mode_protection' => true,
            'environment_hardening' => true,
            'secure_file_permissions' => true,
            
            // General Settings
            'email_alerts' => true,
            'alert_email' => get_option('admin_email'),
            'logging_enabled' => true,
            'log_retention_days' => 30,
            
            // Advanced Settings
            'geo_blocking' => false,
            'blocked_countries' => array(),
            'honeypot_protection' => false,
            'bot_protection' => true,
            
            // Monitoring
            'real_time_monitoring' => true,
            'security_scanning' => false,
            'vulnerability_alerts' => true,
            
            // Backup & Recovery
            'automatic_backups' => false,
            'backup_frequency' => 'daily',
            'backup_retention' => 7
        );
    }
    
    private function load_options() {
        $saved_options = get_option('bocconi_security_options', array());
        $this->options = wp_parse_args($saved_options, $this->default_options);
    }
    
    public function get_default_options() {
        return $this->default_options;
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Bocconi Security Settings',
            'Bocconi Security',
            'manage_options',
            'bocconi-security',
            array($this, 'admin_page')
        );
    }
    
    public function init_settings() {
        register_setting('bocconi_security_group', 'bocconi_security_options', array($this, 'sanitize_options'));
        
        // Architecture Section
        add_settings_section(
            'architecture_section',
            'V1 - Architettura e Sicurezza del Design',
            array($this, 'architecture_section_callback'),
            'bocconi-security'
        );
        
        // Authentication Section
        add_settings_section(
            'authentication_section',
            'V2 - Autenticazione',
            array($this, 'authentication_section_callback'),
            'bocconi-security'
        );
        
        // Session Management Section
        add_settings_section(
            'session_section',
            'V3 - Gestione Sessioni',
            array($this, 'session_section_callback'),
            'bocconi-security'
        );
        
        // Access Control Section
        add_settings_section(
            'access_section',
            'V4 - Controllo Accessi',
            array($this, 'access_section_callback'),
            'bocconi-security'
        );
        
        // Validation Section
        add_settings_section(
            'validation_section',
            'V5 - Validazione e Codifica',
            array($this, 'validation_section_callback'),
            'bocconi-security'
        );
        
        // Error Handling Section
        add_settings_section(
            'error_section',
            'V7 - Gestione Errori e Logging',
            array($this, 'error_section_callback'),
            'bocconi-security'
        );
        
        // Communications Section
        add_settings_section(
            'communication_section',
            'V9 - Comunicazioni',
            array($this, 'communication_section_callback'),
            'bocconi-security'
        );
        
        // Malicious Code Section
        add_settings_section(
            'malicious_section',
            'V10 - Protezione Codice Malevolo',
            array($this, 'malicious_section_callback'),
            'bocconi-security'
        );
        
        // Business Logic Section
        add_settings_section(
            'business_section',
            'V11 - Logica di Business',
            array($this, 'business_section_callback'),
            'bocconi-security'
        );
        
        // File Security Section
        add_settings_section(
            'file_section',
            'V12 - Sicurezza File',
            array($this, 'file_section_callback'),
            'bocconi-security'
        );
        
        // API Security Section
        add_settings_section(
            'api_section',
            'V13 - Sicurezza API',
            array($this, 'api_section_callback'),
            'bocconi-security'
        );
        
        // Configuration Section
        add_settings_section(
            'config_section',
            'V14 - Configurazione',
            array($this, 'config_section_callback'),
            'bocconi-security'
        );
        
        $this->add_settings_fields();
    }
    
    private function add_settings_fields() {
        // Architecture fields
        add_settings_field('hide_wp_version', 'Nascondi versione WordPress', array($this, 'checkbox_field'), 'bocconi-security', 'architecture_section', array('field' => 'hide_wp_version'));
        add_settings_field('disable_xmlrpc', 'Disabilita XML-RPC', array($this, 'checkbox_field'), 'bocconi-security', 'architecture_section', array('field' => 'disable_xmlrpc'));
        add_settings_field('security_headers', 'Header di sicurezza', array($this, 'checkbox_field'), 'bocconi-security', 'architecture_section', array('field' => 'security_headers'));
        
        // Authentication fields
        add_settings_field('login_attempts_limit', 'Limite tentativi login', array($this, 'number_field'), 'bocconi-security', 'authentication_section', array('field' => 'login_attempts_limit', 'min' => 1, 'max' => 20));
        add_settings_field('login_lockout_duration', 'Durata blocco (secondi)', array($this, 'number_field'), 'bocconi-security', 'authentication_section', array('field' => 'login_lockout_duration', 'min' => 300, 'max' => 3600));
        add_settings_field('strong_passwords', 'Password forti obbligatorie', array($this, 'checkbox_field'), 'bocconi-security', 'authentication_section', array('field' => 'strong_passwords'));
        add_settings_field('hide_login_errors', 'Nascondi errori login', array($this, 'checkbox_field'), 'bocconi-security', 'authentication_section', array('field' => 'hide_login_errors'));
        
        // Session fields
        add_settings_field('secure_sessions', 'Sessioni sicure', array($this, 'checkbox_field'), 'bocconi-security', 'session_section', array('field' => 'secure_sessions'));
        add_settings_field('session_timeout', 'Timeout sessione (secondi)', array($this, 'number_field'), 'bocconi-security', 'session_section', array('field' => 'session_timeout', 'min' => 900, 'max' => 86400));
        
        // Access Control fields
        add_settings_field('disable_file_editing', 'Disabilita modifica file', array($this, 'checkbox_field'), 'bocconi-security', 'access_section', array('field' => 'disable_file_editing'));
        add_settings_field('block_suspicious_requests', 'Blocca richieste sospette', array($this, 'checkbox_field'), 'bocconi-security', 'access_section', array('field' => 'block_suspicious_requests'));
        
        // Validation fields
        add_settings_field('input_validation', 'Validazione input', array($this, 'checkbox_field'), 'bocconi-security', 'validation_section', array('field' => 'input_validation'));
        add_settings_field('xss_protection', 'Protezione XSS', array($this, 'checkbox_field'), 'bocconi-security', 'validation_section', array('field' => 'xss_protection'));
        
        // Error Handling fields
        add_settings_field('security_logging', 'Logging sicurezza', array($this, 'checkbox_field'), 'bocconi-security', 'error_section', array('field' => 'security_logging'));
        add_settings_field('email_alerts', 'Alert email', array($this, 'checkbox_field'), 'bocconi-security', 'error_section', array('field' => 'email_alerts'));
        add_settings_field('alert_email', 'Email per alert', array($this, 'email_field'), 'bocconi-security', 'error_section', array('field' => 'alert_email'));
        
        // Communications fields
        add_settings_field('force_https', 'Forza HTTPS', array($this, 'checkbox_field'), 'bocconi-security', 'communication_section', array('field' => 'force_https'));
        add_settings_field('hsts_enabled', 'Abilita HSTS', array($this, 'checkbox_field'), 'bocconi-security', 'communication_section', array('field' => 'hsts_enabled'));
        
        // Malicious Code fields
        add_settings_field('file_upload_restrictions', 'Restrizioni upload file', array($this, 'checkbox_field'), 'bocconi-security', 'malicious_section', array('field' => 'file_upload_restrictions'));
        add_settings_field('malware_scanning', 'Scansione malware', array($this, 'checkbox_field'), 'bocconi-security', 'malicious_section', array('field' => 'malware_scanning'));
        
        // Business Logic fields
        add_settings_field('rate_limiting', 'Rate limiting', array($this, 'checkbox_field'), 'bocconi-security', 'business_section', array('field' => 'rate_limiting'));
        add_settings_field('api_rate_limit', 'Limite API (richieste/ora)', array($this, 'number_field'), 'bocconi-security', 'business_section', array('field' => 'api_rate_limit', 'min' => 10, 'max' => 1000));
        
        // File Security fields
        add_settings_field('file_access_control', 'Controllo accesso file', array($this, 'checkbox_field'), 'bocconi-security', 'file_section', array('field' => 'file_access_control'));
        add_settings_field('directory_traversal_protection', 'Protezione directory traversal', array($this, 'checkbox_field'), 'bocconi-security', 'file_section', array('field' => 'directory_traversal_protection'));
        
        // API Security fields
        add_settings_field('rest_api_security', 'Sicurezza REST API', array($this, 'checkbox_field'), 'bocconi-security', 'api_section', array('field' => 'rest_api_security'));
        add_settings_field('api_authentication_required', 'Autenticazione API obbligatoria', array($this, 'checkbox_field'), 'bocconi-security', 'api_section', array('field' => 'api_authentication_required'));
        
        // Configuration fields
        add_settings_field('secure_configuration', 'Configurazione sicura', array($this, 'checkbox_field'), 'bocconi-security', 'config_section', array('field' => 'secure_configuration'));
        add_settings_field('debug_mode_protection', 'Protezione modalità debug', array($this, 'checkbox_field'), 'bocconi-security', 'config_section', array('field' => 'debug_mode_protection'));
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Bocconi Security Settings</h1>
            <p>Configurazione del sistema di sicurezza basato su OWASP ASVS 4.0</p>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('bocconi_security_group');
                do_settings_sections('bocconi-security');
                submit_button('Salva Impostazioni');
                ?>
            </form>
            
            <div class="security-status">
                <h2>Stato Sicurezza</h2>
                <?php $this->display_security_status(); ?>
            </div>
        </div>
        <?php
    }
    
    private function display_security_status() {
        $status = $this->get_security_status();
        echo '<div class="security-score">';
        echo '<h3>Punteggio Sicurezza: ' . $status['score'] . '/100</h3>';
        echo '<div class="progress-bar"><div class="progress" style="width: ' . $status['score'] . '%"></div></div>';
        echo '</div>';
        
        if (!empty($status['warnings'])) {
            echo '<div class="security-warnings">';
            echo '<h4>Avvisi di Sicurezza:</h4>';
            echo '<ul>';
            foreach ($status['warnings'] as $warning) {
                echo '<li>' . esc_html($warning) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    
    private function get_security_status() {
        $score = 0;
        $warnings = array();
        $total_checks = 20;
        
        // Check various security settings
        if ($this->get_option('hide_wp_version')) $score += 5;
        else $warnings[] = 'Versione WordPress visibile';
        
        if ($this->get_option('disable_xmlrpc')) $score += 5;
        else $warnings[] = 'XML-RPC abilitato';
        
        if ($this->get_option('strong_passwords')) $score += 10;
        else $warnings[] = 'Password forti non obbligatorie';
        
        if ($this->get_option('security_logging')) $score += 10;
        else $warnings[] = 'Logging di sicurezza disabilitato';
        
        if ($this->get_option('force_https')) $score += 15;
        else $warnings[] = 'HTTPS non forzato';
        
        if ($this->get_option('file_upload_restrictions')) $score += 10;
        else $warnings[] = 'Restrizioni upload file disabilitate';
        
        if ($this->get_option('rate_limiting')) $score += 10;
        else $warnings[] = 'Rate limiting disabilitato';
        
        if ($this->get_option('rest_api_security')) $score += 10;
        else $warnings[] = 'Sicurezza API REST disabilitata';
        
        if ($this->get_option('block_suspicious_requests')) $score += 15;
        else $warnings[] = 'Blocco richieste sospette disabilitato';
        
        if ($this->get_option('secure_sessions')) $score += 10;
        else $warnings[] = 'Sessioni non sicure';
        
        return array(
            'score' => min(100, $score),
            'warnings' => $warnings
        );
    }
    
    // Field rendering methods
    public function checkbox_field($args) {
        $field = $args['field'];
        $value = $this->get_option($field);
        echo '<input type="checkbox" name="bocconi_security_options[' . $field . ']" value="1" ' . checked(1, $value, false) . ' />';
    }
    
    public function number_field($args) {
        $field = $args['field'];
        $value = $this->get_option($field);
        $min = isset($args['min']) ? $args['min'] : 0;
        $max = isset($args['max']) ? $args['max'] : 999999;
        echo '<input type="number" name="bocconi_security_options[' . $field . ']" value="' . $value . '" min="' . $min . '" max="' . $max . '" />';
    }
    
    public function email_field($args) {
        $field = $args['field'];
        $value = $this->get_option($field);
        echo '<input type="email" name="bocconi_security_options[' . $field . ']" value="' . esc_attr($value) . '" class="regular-text" />';
    }
    
    // Section callbacks
    public function architecture_section_callback() {
        echo '<p>Impostazioni per l\'architettura e il design sicuro del sistema.</p>';
    }
    
    public function authentication_section_callback() {
        echo '<p>Configurazione dei controlli di autenticazione e gestione delle credenziali.</p>';
    }
    
    public function session_section_callback() {
        echo '<p>Gestione sicura delle sessioni utente.</p>';
    }
    
    public function access_section_callback() {
        echo '<p>Controlli di accesso e autorizzazione.</p>';
    }
    
    public function validation_section_callback() {
        echo '<p>Validazione, sanitizzazione e codifica dei dati.</p>';
    }
    
    public function error_section_callback() {
        echo '<p>Gestione degli errori e logging degli eventi di sicurezza.</p>';
    }
    
    public function communication_section_callback() {
        echo '<p>Sicurezza delle comunicazioni e trasporto dati.</p>';
    }
    
    public function malicious_section_callback() {
        echo '<p>Protezione contro codice malevolo e malware.</p>';
    }
    
    public function business_section_callback() {
        echo '<p>Controlli sulla logica di business e rate limiting.</p>';
    }
    
    public function file_section_callback() {
        echo '<p>Sicurezza dei file e controllo delle risorse.</p>';
    }
    
    public function api_section_callback() {
        echo '<p>Sicurezza delle API e dei servizi web.</p>';
    }
    
    public function config_section_callback() {
        echo '<p>Configurazione sicura del sistema.</p>';
    }
    
    public function sanitize_options($input) {
        $sanitized = array();
        
        foreach ($this->get_default_options() as $key => $default) {
            if (isset($input[$key])) {
                if (is_bool($default)) {
                    $sanitized[$key] = (bool) $input[$key];
                } elseif (is_int($default)) {
                    $sanitized[$key] = (int) $input[$key];
                } elseif (is_email($default)) {
                    $sanitized[$key] = sanitize_email($input[$key]);
                } else {
                    $sanitized[$key] = sanitize_text_field($input[$key]);
                }
            } else {
                $sanitized[$key] = $default;
            }
        }
        
        return $sanitized;
    }
    
    public function get_option($key, $default = null) {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        
        if ($default !== null) {
            return $default;
        }
        
        return isset($this->default_options[$key]) ? $this->default_options[$key] : null;
    }
    
    public function set_option($key, $value) {
        $this->options[$key] = $value;
        return $this->save_options();
    }
    
    public function get_all_options() {
        return $this->options;
    }
    
    public function update_options($new_options) {
        $this->options = wp_parse_args($new_options, $this->default_options);
        return $this->save_options();
    }
    
    public function reset_to_defaults() {
        $this->options = $this->default_options;
        return $this->save_options();
    }
    
    private function save_options() {
        return update_option('bocconi_security_options', $this->options);
    }
    
    public function update_option($key, $value) {
        $this->options[$key] = $value;
        update_option('bocconi_security_options', $this->options);
    }
    
    // Configuration validation methods
    public function validate_option($key, $value) {
        switch ($key) {
            case 'login_attempts_limit':
                return max(1, min(20, intval($value)));
                
            case 'login_lockout_duration':
                return max(300, min(86400, intval($value))); // 5 minutes to 24 hours
                
            case 'session_timeout':
                return max(300, min(86400, intval($value))); // 5 minutes to 24 hours
                
            case 'api_rate_limit':
                return max(10, min(1000, intval($value)));
                
            case 'form_rate_limit':
                return max(1, min(100, intval($value)));
                
            case 'log_retention_days':
                return max(1, min(365, intval($value)));
                
            case 'backup_retention':
                return max(1, min(30, intval($value)));
                
            case 'alert_email':
                return is_email($value) ? $value : get_option('admin_email');
                
            case 'admin_ip_whitelist':
                if (is_array($value)) {
                    return array_filter($value, function($ip) {
                        return filter_var($ip, FILTER_VALIDATE_IP);
                    });
                }
                return array();
                
            case 'blocked_countries':
                if (is_array($value)) {
                    return array_filter($value, function($country) {
                        return preg_match('/^[A-Z]{2}$/', $country);
                    });
                }
                return array();
                
            case 'backup_frequency':
                $allowed = array('hourly', 'daily', 'weekly', 'monthly');
                return in_array($value, $allowed) ? $value : 'daily';
                
            default:
                // For boolean options
                if (isset($this->default_options[$key]) && is_bool($this->default_options[$key])) {
                    return (bool) $value;
                }
                
                // For string options
                if (is_string($value)) {
                    return sanitize_text_field($value);
                }
                
                return $value;
        }
    }
    
    // Security level presets
    public function apply_security_preset($level) {
        switch ($level) {
            case 'basic':
                $preset = array(
                    'hide_wp_version' => true,
                    'disable_xmlrpc' => true,
                    'login_attempts_limit' => 10,
                    'secure_sessions' => true,
                    'input_validation' => true,
                    'security_headers' => true,
                    'file_upload_restrictions' => true
                );
                break;
                
            case 'standard':
                $preset = array(
                    'hide_wp_version' => true,
                    'disable_xmlrpc' => true,
                    'remove_meta_tags' => true,
                    'login_attempts_limit' => 5,
                    'login_lockout_duration' => 1800,
                    'secure_sessions' => true,
                    'session_timeout' => 3600,
                    'disable_file_editing' => true,
                    'block_suspicious_requests' => true,
                    'input_validation' => true,
                    'xss_protection' => true,
                    'hide_login_errors' => true,
                    'force_https' => true,
                    'security_headers' => true,
                    'hsts_enabled' => true,
                    'file_upload_restrictions' => true,
                    'malware_scanning' => true,
                    'rate_limiting' => true,
                    'rest_api_security' => true
                );
                break;
                
            case 'advanced':
                $preset = $this->default_options;
                $preset['two_factor_auth'] = true;
                $preset['restrict_admin_access'] = true;
                $preset['file_integrity_monitoring'] = true;
                $preset['geo_blocking'] = true;
                $preset['honeypot_protection'] = true;
                $preset['security_scanning'] = true;
                break;
                
            case 'maximum':
                $preset = $this->default_options;
                $preset['two_factor_auth'] = true;
                $preset['restrict_admin_access'] = true;
                $preset['file_integrity_monitoring'] = true;
                $preset['geo_blocking'] = true;
                $preset['honeypot_protection'] = true;
                $preset['security_scanning'] = true;
                $preset['automatic_backups'] = true;
                $preset['login_attempts_limit'] = 3;
                $preset['login_lockout_duration'] = 3600;
                $preset['session_timeout'] = 1800;
                $preset['api_rate_limit'] = 50;
                $preset['form_rate_limit'] = 5;
                break;
                
            default:
                return false;
        }
        
        return $this->update_options($preset);
    }
    
    // Get security level based on score
    public function get_security_level() {
        $score = $this->get_security_score();
        
        if ($score >= 80) {
            return 'high';
        } elseif ($score >= 60) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    // Get security score based on current configuration
    public function get_security_score() {
        $score = 0;
        $max_score = 0;
        
        $security_checks = array(
            'hide_wp_version' => 5,
            'disable_xmlrpc' => 5,
            'remove_meta_tags' => 3,
            'login_attempts_limit' => 10,
            'secure_sessions' => 8,
            'session_timeout' => 5,
            'disable_file_editing' => 10,
            'block_suspicious_requests' => 8,
            'input_validation' => 10,
            'xss_protection' => 8,
            'hide_login_errors' => 5,
            'force_https' => 10,
            'security_headers' => 8,
            'hsts_enabled' => 5,
            'file_upload_restrictions' => 8,
            'malware_scanning' => 7,
            'rate_limiting' => 6,
            'rest_api_security' => 7,
            'debug_mode_protection' => 5,
            'environment_hardening' => 5
        );
        
        foreach ($security_checks as $check => $points) {
            $max_score += $points;
            if ($this->get_option($check)) {
                $score += $points;
            }
        }
        
        // Bonus points for advanced features
        if ($this->get_option('two_factor_auth')) $score += 15;
        if ($this->get_option('restrict_admin_access')) $score += 10;
        if ($this->get_option('file_integrity_monitoring')) $score += 8;
        if ($this->get_option('geo_blocking')) $score += 5;
        if ($this->get_option('honeypot_protection')) $score += 5;
        if ($this->get_option('security_scanning')) $score += 10;
        
        $max_score += 53; // Total bonus points
        
        return array(
            'score' => $score,
            'max_score' => $max_score,
            'percentage' => round(($score / $max_score) * 100, 1)
        );
    }
    
    // Export configuration
    public function export_config() {
        $export_data = array(
            'version' => BOCCONI_SECURITY_VERSION,
            'timestamp' => current_time('timestamp'),
            'site_url' => get_site_url(),
            'options' => $this->options
        );
        
        return base64_encode(json_encode($export_data));
    }
    
    // Import configuration
    public function import_config($config_data) {
        try {
            $decoded_data = json_decode(base64_decode($config_data), true);
            
            if (!$decoded_data || !isset($decoded_data['options'])) {
                return new WP_Error('invalid_config', 'Configurazione non valida.');
            }
            
            // Validate imported options
            $validated_options = array();
            foreach ($decoded_data['options'] as $key => $value) {
                if (array_key_exists($key, $this->default_options)) {
                    $validated_options[$key] = $this->validate_option($key, $value);
                }
            }
            
            if ($this->update_options($validated_options)) {
                return true;
            } else {
                return new WP_Error('import_failed', 'Impossibile salvare la configurazione.');
            }
            
        } catch (Exception $e) {
            return new WP_Error('import_error', 'Errore durante l\'importazione: ' . $e->getMessage());
        }
    }
    
    // Check if configuration is valid
    public function validate_configuration() {
        $errors = array();
        
        // Check critical settings
        if (!$this->get_option('input_validation')) {
            $errors[] = 'La validazione degli input è disabilitata - rischio di sicurezza elevato.';
        }
        
        if (!$this->get_option('security_headers')) {
            $errors[] = 'Gli header di sicurezza sono disabilitati.';
        }
        
        // Determine environment: treat HTTPS absence as warning in local/dev
        $env = function_exists('wp_get_environment_type') ? wp_get_environment_type() : (defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'production');
        $site_url = get_site_url();
        $is_local = in_array($env, array('development', 'local')) || preg_match('/(localhost|\.local|127\.0\.0\.1)/', $site_url);
        
        if (!$this->get_option('force_https') && !is_ssl()) {
            if (!$is_local) {
                $errors[] = 'HTTPS non è forzato e il sito non utilizza SSL.';
            }
            // In ambiente locale non lo segnaliamo come errore critico nella validazione
        }
        
        if ($this->get_option('login_attempts_limit') > 10) {
            $errors[] = 'Il limite di tentativi di login è troppo alto.';
        }
        
        if ($this->get_option('session_timeout') > 7200) {
            $errors[] = 'Il timeout della sessione è troppo lungo.';
        }
        
        if (!$this->get_option('logging_enabled')) {
            $errors[] = 'Il logging di sicurezza è disabilitato.';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    // Get configuration recommendations
    public function get_recommendations() {
        $recommendations = array();
        
        if (!$this->get_option('two_factor_auth')) {
            $recommendations[] = array(
                'type' => 'warning',
                'message' => 'Considera l\'attivazione dell\'autenticazione a due fattori per maggiore sicurezza.',
                'action' => 'enable_2fa'
            );
        }
        
        if (!$this->get_option('automatic_backups')) {
            $recommendations[] = array(
                'type' => 'info',
                'message' => 'I backup automatici non sono attivi. Considera l\'attivazione per la sicurezza dei dati.',
                'action' => 'enable_backups'
            );
        }
        
        if (!$this->get_option('file_integrity_monitoring')) {
            $recommendations[] = array(
                'type' => 'info',
                'message' => 'Il monitoraggio dell\'integrità dei file può aiutare a rilevare modifiche non autorizzate.',
                'action' => 'enable_fim'
            );
        }
        
        if ($this->get_option('api_rate_limit') > 200) {
            $recommendations[] = array(
                'type' => 'warning',
                'message' => 'Il limite di rate per le API è molto alto. Considera di ridurlo.',
                'action' => 'reduce_api_limit'
            );
        }
        
        if (!$this->get_option('geo_blocking') && $this->get_option('restrict_admin_access')) {
            $recommendations[] = array(
                'type' => 'info',
                'message' => 'Il geo-blocking può fornire un ulteriore livello di protezione.',
                'action' => 'enable_geo_blocking'
            );
        }
        
        return $recommendations;
    }
    
    /**
     * Get current plugin options
     */
    public function get_options() {
        return $this->options;
    }
}

// Initialize the configuration
BocconiSecurityConfig::getInstance();

?>