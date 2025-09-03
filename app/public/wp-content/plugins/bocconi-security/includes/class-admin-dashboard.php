<?php
/**
 * Bocconi Security Admin Dashboard
 * 
 * Gestisce l'interfaccia di amministrazione del plugin
 * 
 * @package BocconiSecurity
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class BocconiSecurityAdminDashboard {
    
    private $config;
    private $implementation;
    
    public function __construct() {
        $this->config = BocconiSecurityConfig::getInstance();
        $this->implementation = BocconiSecurityImplementation::getInstance();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_bocconi_security_scan', array($this, 'ajax_security_scan'));
        add_action('wp_ajax_bocconi_security_test', array($this, 'ajax_security_test'));
        add_action('wp_ajax_bocconi_security_report', array($this, 'ajax_security_report'));
        
        // Register new AJAX handlers for UI
        add_action('wp_ajax_bocconi_security_get_score', array($this, 'ajax_get_score'));
        add_action('wp_ajax_bocconi_security_get_recent_threats', array($this, 'ajax_get_recent_threats'));
        add_action('wp_ajax_bocconi_security_get_system_status', array($this, 'ajax_get_system_status'));
        add_action('wp_ajax_bocconi_security_get_stats', array($this, 'ajax_get_stats'));
        add_action('wp_ajax_bocconi_security_get_active_preset', array($this, 'ajax_get_active_preset'));
        add_action('wp_ajax_bocconi_security_apply_preset', array($this, 'ajax_apply_preset'));
        add_action('wp_ajax_bocconi_security_toggle_owasp_rule', array($this, 'ajax_toggle_owasp_rule'));
        add_action('wp_ajax_bocconi_security_get_threat_chart_data', array($this, 'ajax_get_threat_chart_data'));
        add_action('wp_ajax_bocconi_security_get_activity_chart_data', array($this, 'ajax_get_activity_chart_data'));
        add_action('wp_ajax_bocconi_security_run_scan', array($this, 'ajax_run_scan'));
        add_action('wp_ajax_bocconi_security_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_bocconi_security_export_config', array($this, 'ajax_export_config'));
        add_action('wp_ajax_bocconi_security_import_config', array($this, 'ajax_import_config'));
        add_action('wp_ajax_bocconi_security_test_email', array($this, 'ajax_test_email'));
        add_action('wp_ajax_bocconi_security_toggle_auto_update', array($this, 'ajax_toggle_auto_update'));
        
        // Backup and integrity AJAX actions
        add_action('wp_ajax_bocconi_security_create_backup', array($this, 'ajax_create_backup'));
        add_action('wp_ajax_bocconi_security_get_backup_settings', array($this, 'ajax_get_backup_settings'));
        add_action('wp_ajax_bocconi_security_update_backup_settings', array($this, 'ajax_update_backup_settings'));
        add_action('wp_ajax_bocconi_security_list_backups', array($this, 'ajax_list_backups'));
        add_action('wp_ajax_bocconi_security_delete_backup', array($this, 'ajax_delete_backup'));
        add_action('wp_ajax_bocconi_security_init_integrity_baseline', array($this, 'ajax_init_integrity_baseline'));
        add_action('wp_ajax_bocconi_security_check_integrity', array($this, 'ajax_check_integrity'));
        add_action('wp_ajax_bocconi_security_get_integrity_settings', array($this, 'ajax_get_integrity_settings'));
        add_action('wp_ajax_bocconi_security_update_integrity_settings', array($this, 'ajax_update_integrity_settings'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Bocconi Security',
            'Bocconi Security',
            'manage_options',
            'bocconi-security',
            array($this, 'admin_page'),
            'dashicons-shield-alt',
            30
        );
        
        add_submenu_page(
            'bocconi-security',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'bocconi-security',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'bocconi-security',
            'Configurazione',
            'Configurazione',
            'manage_options',
            'bocconi-security-config',
            array($this, 'config_page')
        );
        
        add_submenu_page(
            'bocconi-security',
            'Log di Sicurezza',
            'Log di Sicurezza',
            'manage_options',
            'bocconi-security-logs',
            array($this, 'logs_page')
        );
        
        add_submenu_page(
            'bocconi-security',
            'Report OWASP',
            'Report OWASP',
            'manage_options',
            'bocconi-security-report',
            array($this, 'report_page')
        );
    }
    
    public function register_settings() {
        register_setting('bocconi_security_options', 'bocconi_security_options', array(
            'sanitize_callback' => array($this, 'sanitize_options')
        ));
    }
    
    public function sanitize_options($options) {
        $sanitized = array();
        
        foreach ($options as $key => $value) {
            $sanitized[$key] = $this->config->validate_option($key, $value);
        }
        
        return $sanitized;
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'bocconi-security') === false) {
            return;
        }
        
        wp_enqueue_style('bocconi-security-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/admin-style.css', array(), '1.0.0');
        wp_enqueue_script('bocconi-security-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/admin-script.js', array('jquery'), '1.0.0', true);
        
        wp_localize_script('bocconi-security-admin', 'bocconiSecurityAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bocconi_security_nonce'),
            'strings' => array(
                'scanning' => 'Scansione in corso...',
                'scan_complete' => 'Scansione completata',
                'scan_error' => 'Errore durante la scansione'
            )
        ));
    }
    
    public function admin_page() {
        $security_level = $this->config->get_security_level();
        $recent_threats = $this->get_recent_threats();
        $system_status = $this->get_system_status();
        
        ?>
        <div class="wrap">
            <h1><span class="dashicons dashicons-shield-alt"></span> Bocconi Security Dashboard</h1>
            
            <div class="bocconi-security-dashboard">
                <!-- Security Level Card -->
                <div class="security-card">
                    <h2>Livello di Sicurezza</h2>
                    <div class="security-meter">
                        <div class="meter-bar">
                            <div class="meter-fill" style="width: <?php echo $security_level; ?>%;"></div>
                        </div>
                        <span class="meter-text"><?php echo $security_level; ?>%</span>
                    </div>
                    <p class="security-status <?php echo $this->get_security_status_class($security_level); ?>">
                        <?php echo $this->get_security_status_text($security_level); ?>
                    </p>
                </div>
                
                <!-- Quick Actions -->
                <div class="security-card">
                    <h2>Azioni Rapide</h2>
                    <div class="quick-actions">
                        <button class="button button-primary" id="security-scan">Scansione Sicurezza</button>
                        <button class="button" id="update-config">Aggiorna Configurazione</button>
                        <button class="button" id="export-logs">Esporta Log</button>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="security-card">
                    <h2>Stato del Sistema</h2>
                    <table class="widefat">
                        <tbody>
                            <?php foreach ($system_status as $check => $status): ?>
                            <tr>
                                <td><?php echo esc_html($check); ?></td>
                                <td>
                                    <span class="status-indicator <?php echo $status['status']; ?>">
                                        <?php echo $status['message']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Recent Threats -->
                <div class="security-card">
                    <h2>Minacce Recenti</h2>
                    <?php if (empty($recent_threats)): ?>
                        <p>Nessuna minaccia rilevata nelle ultime 24 ore.</p>
                    <?php else: ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>IP</th>
                                    <th>Data/Ora</th>
                                    <th>Azione</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_threats as $threat): ?>
                                <tr>
                                    <td><?php echo esc_html($threat['type']); ?></td>
                                    <td><?php echo esc_html($threat['ip']); ?></td>
                                    <td><?php echo esc_html($threat['datetime']); ?></td>
                                    <td><?php echo esc_html($threat['action']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
        .bocconi-security-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .security-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .security-card h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .security-meter {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .meter-bar {
            flex: 1;
            height: 20px;
            background: #f0f0f1;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .meter-fill {
            height: 100%;
            background: linear-gradient(90deg, #dc3232 0%, #ffb900 50%, #46b450 100%);
            transition: width 0.3s ease;
        }
        
        .meter-text {
            font-weight: bold;
            font-size: 16px;
        }
        
        .security-status {
            font-weight: bold;
            margin: 0;
        }
        
        .security-status.low { color: #dc3232; }
        .security-status.medium { color: #ffb900; }
        .security-status.high { color: #46b450; }
        
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .status-indicator {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-indicator.pass {
            background: #d4edda;
            color: #155724;
        }
        
        .status-indicator.fail {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-indicator.warning {
            background: #fff3cd;
            color: #856404;
        }
        </style>
        <?php
    }
    
    public function config_page() {
        if (isset($_POST['submit'])) {
            check_admin_referer('bocconi_security_config');
            
            $options = $_POST['bocconi_security'] ?? array();
            $this->config->update_options($options);
            
            echo '<div class="notice notice-success"><p>Configurazione salvata con successo!</p></div>';
        }
        
        $current_options = $this->config->get_all_options();
        
        ?>
        <div class="wrap">
            <h1>Configurazione Bocconi Security</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('bocconi_security_config'); ?>
                
                <div class="config-sections">
                    <!-- Architettura -->
                    <div class="config-section">
                        <h2>V1 - Sicurezza dell'Architettura</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Nascondi Versione WordPress</th>
                                <td>
                                    <input type="checkbox" name="bocconi_security[hide_wp_version]" value="1" <?php checked($current_options['hide_wp_version']); ?> />
                                    <p class="description">Rimuove la versione di WordPress dall'HTML</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Disabilita XML-RPC</th>
                                <td>
                                    <input type="checkbox" name="bocconi_security[disable_xmlrpc]" value="1" <?php checked($current_options['disable_xmlrpc']); ?> />
                                    <p class="description">Disabilita l'endpoint XML-RPC per prevenire attacchi</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Autenticazione -->
                    <div class="config-section">
                        <h2>V2 - Autenticazione</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Tentativi di Login Massimi</th>
                                <td>
                                    <input type="number" name="bocconi_security[login_attempts_limit]" value="<?php echo esc_attr($current_options['login_attempts_limit']); ?>" min="1" max="20" />
                                    <p class="description">Numero massimo di tentativi di login falliti prima del blocco</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Durata Blocco (secondi)</th>
                                <td>
                                    <input type="number" name="bocconi_security[login_lockout_duration]" value="<?php echo esc_attr($current_options['login_lockout_duration']); ?>" min="300" max="86400" />
                                    <p class="description">Durata del blocco IP in secondi (300-86400)</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Gestione Sessioni -->
                    <div class="config-section">
                        <h2>V3 - Gestione Sessioni</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Timeout Sessione (secondi)</th>
                                <td>
                                    <input type="number" name="bocconi_security[session_timeout]" value="<?php echo esc_attr($current_options['session_timeout']); ?>" min="300" max="7200" />
                                    <p class="description">Timeout automatico delle sessioni in secondi</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Cookie Sicuri</th>
                                <td>
                                    <input type="checkbox" name="bocconi_security[secure_cookies]" value="1" <?php checked($current_options['secure_cookies']); ?> />
                                    <p class="description">Imposta i cookie come sicuri e HttpOnly</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Comunicazioni -->
                    <div class="config-section">
                        <h2>V9 - Sicurezza delle Comunicazioni</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Forza HTTPS</th>
                                <td>
                                    <input type="checkbox" name="bocconi_security[force_https]" value="1" <?php checked($current_options['force_https']); ?> />
                                    <p class="description">Forza l'uso di HTTPS per tutto il sito</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Header di Sicurezza</th>
                                <td>
                                    <input type="checkbox" name="bocconi_security[security_headers]" value="1" <?php checked($current_options['security_headers']); ?> />
                                    <p class="description">Aggiunge header di sicurezza HTTP</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Monitoraggio -->
                    <div class="config-section">
                        <h2>Monitoraggio e Alert</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Email per Alert</th>
                                <td>
                                    <input type="email" name="bocconi_security[alert_email]" value="<?php echo esc_attr($current_options['alert_email']); ?>" class="regular-text" />
                                    <p class="description">Email per ricevere gli alert di sicurezza</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Abilita Logging</th>
                                <td>
                                    <input type="checkbox" name="bocconi_security[logging_enabled]" value="1" <?php checked($current_options['logging_enabled']); ?> />
                                    <p class="description">Abilita il logging degli eventi di sicurezza</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php submit_button('Salva Configurazione'); ?>
            </form>
        </div>
        
        <style>
        .config-sections {
            margin-top: 20px;
        }
        
        .config-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .config-section h2 {
            margin-top: 0;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        </style>
        <?php
    }
    
    public function logs_page() {
        $logs = $this->get_security_logs();
        
        ?>
        <div class="wrap">
            <h1>Log di Sicurezza</h1>
            
            <div class="log-filters">
                <form method="get">
                    <input type="hidden" name="page" value="bocconi-security-logs" />
                    <select name="log_level">
                        <option value="">Tutti i livelli</option>
                        <option value="error" <?php selected($_GET['log_level'] ?? '', 'error'); ?>>Errori</option>
                        <option value="warning" <?php selected($_GET['log_level'] ?? '', 'warning'); ?>>Avvisi</option>
                        <option value="info" <?php selected($_GET['log_level'] ?? '', 'info'); ?>>Info</option>
                    </select>
                    <input type="date" name="date_from" value="<?php echo esc_attr($_GET['date_from'] ?? ''); ?>" />
                    <input type="date" name="date_to" value="<?php echo esc_attr($_GET['date_to'] ?? ''); ?>" />
                    <input type="submit" class="button" value="Filtra" />
                </form>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Data/Ora</th>
                        <th>Livello</th>
                        <th>Evento</th>
                        <th>IP</th>
                        <th>Dettagli</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5">Nessun log trovato.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log['datetime']); ?></td>
                            <td><span class="log-level <?php echo esc_attr($log['level']); ?>"><?php echo esc_html(strtoupper($log['level'])); ?></span></td>
                            <td><?php echo esc_html($log['event']); ?></td>
                            <td><?php echo esc_html($log['ip']); ?></td>
                            <td><?php echo esc_html($log['details']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .log-filters {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .log-filters form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .log-level {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .log-level.error { background: #f8d7da; color: #721c24; }
        .log-level.warning { background: #fff3cd; color: #856404; }
        .log-level.info { background: #d1ecf1; color: #0c5460; }
        </style>
        <?php
    }
    
    public function report_page() {
        // Removed invalid include of non-existent OWASP checker file
        
        ?>
        <div class="wrap">
            <h1>Report OWASP ASVS</h1>
            
            <div class="report-actions">
                <button class="button button-primary" id="generate-report">Genera Nuovo Report</button>
                <button class="button" id="export-report">Esporta Report</button>
            </div>
            
            <div id="owasp-report-container">
                <!-- Il report verrà caricato qui via AJAX -->
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-report').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Generazione in corso...');
                
                $.post(ajaxurl, {
                    action: 'bocconi_security_report',
                    nonce: '<?php echo wp_create_nonce('bocconi_security_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        $('#owasp-report-container').html(response.data);
                    } else {
                        alert('Errore nella generazione del report');
                    }
                }).always(function() {
                    button.prop('disabled', false).text('Genera Nuovo Report');
                });
            });
            
            // Genera automaticamente il report al caricamento della pagina
            $('#generate-report').trigger('click');
        });
        </script>
        <?php
    }
    
    // AJAX Handlers
    public function ajax_security_scan() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        $scan_results = $this->implementation->run_security_scan();
        
        wp_send_json_success($scan_results);
    }
    
    public function ajax_security_test() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        $test_results = $this->implementation->run_security_tests();
        
        wp_send_json_success($test_results);
    }

    public function ajax_security_report() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }

        $score = method_exists($this->config, 'get_security_score') ? $this->config->get_security_score() : null;
        $validation = method_exists($this->config, 'validate_configuration') ? $this->config->validate_configuration() : true;
        $recommendations = method_exists($this->config, 'get_recommendations') ? $this->config->get_recommendations() : array();
        $owasp_status = $this->get_owasp_status();

        ob_start();
        ?>
        <div class="bocconi-owasp-report">
            <h2>Stato Sicurezza</h2>
            <?php if ($score): ?>
            <p>Punteggio: <strong><?php echo esc_html($score['score']); ?></strong> / <?php echo esc_html($score['max_score']); ?> (<?php echo esc_html($score['percentage']); ?>%)</p>
            <?php endif; ?>

            <h3>Validazione Configurazione</h3>
            <?php if ($validation === true): ?>
                <p class="notice notice-success"><strong>OK</strong>: Configurazione valida.</p>
            <?php else: ?>
                <ul class="notice notice-warning">
                    <?php foreach ($validation as $err): ?>
                        <li><?php echo esc_html($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h3>Stato Controlli OWASP ASVS (principali)</h3>
            <div class="owasp-status-grid">
                <?php foreach ($owasp_status as $category => $items): ?>
                    <div class="owasp-category">
                        <h4><?php echo esc_html($category); ?></h4>
                        <ul>
                            <?php foreach ($items as $item): ?>
                                <li class="status-<?php echo esc_attr($item['status']); ?>">
                                    <strong><?php echo esc_html($item['label']); ?>:</strong>
                                    <span><?php echo esc_html($item['message']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($recommendations)): ?>
                <h3>Raccomandazioni</h3>
                <ul class="recommendations">
                    <?php foreach ($recommendations as $rec): ?>
                        <li>
                            <strong><?php echo esc_html(strtoupper($rec['type'] ?? 'info')); ?>:</strong>
                            <?php echo esc_html($rec['message'] ?? ''); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
        $html = ob_get_clean();
        wp_send_json_success($html);
    }
    
    // Helper Methods
    private function get_security_status_class($level) {
        if ($level >= 80) return 'high';
        if ($level >= 60) return 'medium';
        return 'low';
    }
    
    private function get_security_status_text($level) {
        if ($level >= 80) return 'Sicurezza Elevata';
        if ($level >= 60) return 'Sicurezza Media';
        return 'Sicurezza Bassa - Azione Richiesta';
    }
    
    private function get_recent_threats() {
        $logger = BocconiSecurityLogger::getInstance();
        $logs = $logger->get_recent_logs(20);
        
        $threats = array();
        foreach ($logs as $log) {
            // Filter for security-related events in last 24 hours
            $log_time = strtotime($log['datetime']);
            if ($log_time >= strtotime('-24 hours') && 
                in_array($log['level'], array('warning', 'error', 'critical')) && 
                in_array($log['type'], array('BRUTE_FORCE', 'SQL_INJECTION', 'XSS_ATTEMPT', 'MALWARE_DETECTED', 'SUSPICIOUS_ACTIVITY', 'LOGIN_FAILURE'))) {
                $threats[] = array(
                    'datetime' => $log['datetime'],
                    'level' => $log['level'],
                    'type' => $log['type'],
                    'ip' => $log['ip'],
                    'data' => $log['data']
                );
            }
        }
        
        return array_slice($threats, 0, 10);
    }
    
    private function get_system_status() {
        $status = array();
        
        // Determine environment
        $env = function_exists('wp_get_environment_type') ? wp_get_environment_type() : (defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'production');
        $site_url = get_site_url();
        $is_local = in_array($env, array('development', 'local')) || preg_match('/(localhost|\.local|127\.0\.0\.1)/', $site_url);
        
        // WordPress Version
        $wp_version = get_bloginfo('version');
        $latest_version = $this->get_latest_wp_version();
        $status['wordpress_version'] = array(
            'label' => 'Versione WordPress',
            'status' => version_compare($wp_version, $latest_version, '>=') ? 'pass' : 'warning',
            'message' => "Installata: {$wp_version}" . (version_compare($wp_version, $latest_version, '<') ? " (aggiornamento disponibile: {$latest_version})" : '')
        );
        
        // HTTPS
        $https_ok = is_ssl();
        $force_https = method_exists($this->config, 'get_option') ? (bool) $this->config->get_option('force_https') : false;
        $status['https'] = array(
            'label' => 'HTTPS',
            'status' => ($https_ok || $force_https) ? 'pass' : ($is_local ? 'warning' : 'fail'),
            'message' => $https_ok ? 'Attivo' : ($is_local ? 'Non attivo (ambiente locale)' : 'Non attivo (richiesto in produzione)')
        );
        
        // HSTS
        $hsts_enabled = method_exists($this->config, 'get_option') ? (bool) $this->config->get_option('hsts_enabled') : false;
        $status['hsts'] = array(
            'label' => 'HSTS',
            'status' => $hsts_enabled ? 'pass' : ($https_ok ? 'warning' : 'fail'),
            'message' => $hsts_enabled ? 'Abilitato' : 'Disabilitato'
        );
        
        // Security Headers configuration
        $security_headers = method_exists($this->config, 'get_option') ? (bool) $this->config->get_option('security_headers') : false;
        $status['security_headers'] = array(
            'label' => 'Header di Sicurezza',
            'status' => $security_headers ? 'pass' : 'warning',
            'message' => $security_headers ? 'Configurati' : 'Non configurati'
        );
        
        // Debug Mode
        $status['debug_mode'] = array(
            'label' => 'Modalità Debug',
            'status' => (defined('WP_DEBUG') && WP_DEBUG) ? 'warning' : 'pass',
            'message' => (defined('WP_DEBUG') && WP_DEBUG) ? 'Attivo (disabilitare in produzione)' : 'Disattivato'
        );
        
        // File Permissions
        $wp_config_path = ABSPATH . 'wp-config.php';
        $wp_config_perms = file_exists($wp_config_path) ? substr(sprintf('%o', fileperms($wp_config_path)), -4) : '----';
        $status['wp_config_perms'] = array(
            'label' => 'Permessi wp-config.php',
            'status' => ($wp_config_perms === '0644' || $wp_config_perms === '0600') ? 'pass' : 'warning',
            'message' => $wp_config_perms
        );
        
        return $status;
    }
    
    private function get_latest_wp_version() {
        $version_check = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/');
        
        if (is_wp_error($version_check)) {
            return get_bloginfo('version');
        }
        
        $version_data = json_decode(wp_remote_retrieve_body($version_check), true);
        
        return $version_data['offers'][0]['version'] ?? get_bloginfo('version');
    }
    
    private function get_owasp_status() {
        $items = array();
        $is_ssl_active = is_ssl();
        $force_https = (bool) $this->config->get_option('force_https');
        
        // Determine environment (align with get_system_status)
        $env = function_exists('wp_get_environment_type') ? wp_get_environment_type() : (defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'production');
        $site_url = get_site_url();
        $is_local = in_array($env, array('development', 'local')) || preg_match('/(localhost|\.local|127\.0\.0\.1)/', $site_url);
        
        // V2 - Authentication
        $items['V2 - Autenticazione'] = array(
            array(
                'label' => '2FA',
                'status' => $this->config->get_option('two_factor_auth') ? 'pass' : 'warning',
                'message' => $this->config->get_option('two_factor_auth') ? 'Abilitata' : 'Non abilitata'
            ),
            array(
                'label' => 'Tentativi Login',
                'status' => ((int) $this->config->get_option('login_attempts_limit') <= 10) ? 'pass' : 'warning',
                'message' => 'Limite: ' . (int) $this->config->get_option('login_attempts_limit')
            ),
            array(
                'label' => 'Errori Login',
                'status' => $this->config->get_option('hide_login_errors') ? 'pass' : 'warning',
                'message' => $this->config->get_option('hide_login_errors') ? 'Nascosti' : 'Visibili'
            ),
        );
        
        // V3 - Gestione Sessione
        $items['V3 - Sessioni'] = array(
            array(
                'label' => 'Sessioni Sicure',
                'status' => $this->config->get_option('secure_sessions') ? 'pass' : 'warning',
                'message' => $this->config->get_option('secure_sessions') ? 'Abilitato' : 'Disabilitato'
            ),
            array(
                'label' => 'Timeout Sessione',
                'status' => ((int) $this->config->get_option('session_timeout') <= 7200) ? 'pass' : 'warning',
                'message' => 'Timeout: ' . (int) $this->config->get_option('session_timeout') . 's'
            ),
        );
        
        // V4 - Access Control
        $items['V4 - Controllo Accessi'] = array(
            array(
                'label' => 'Restrizione Admin',
                'status' => $this->config->get_option('restrict_admin_access') ? 'pass' : 'warning',
                'message' => $this->config->get_option('restrict_admin_access') ? 'Abilitata' : 'Non abilitata'
            ),
            array(
                'label' => 'REST API',
                'status' => $this->config->get_option('rest_api_security') ? 'pass' : 'warning',
                'message' => $this->config->get_option('rest_api_security') ? 'Protetta' : 'Non protetta'
            ),
        );
        
        // V1/V5 - Validazione e XSS
        $items['V1/V5 - Validazione & XSS'] = array(
            array(
                'label' => 'Validazione Input',
                'status' => $this->config->get_option('input_validation') ? 'pass' : 'fail',
                'message' => $this->config->get_option('input_validation') ? 'Abilitata' : 'Disabilitata'
            ),
            array(
                'label' => 'Protezione XSS',
                'status' => $this->config->get_option('xss_protection') ? 'pass' : 'warning',
                'message' => $this->config->get_option('xss_protection') ? 'Abilitata' : 'Disabilitata'
            ),
        );
        
        // V7 - Error Handling e Logging
        $items['V7 - Errori & Logging'] = array(
            array(
                'label' => 'WP_DEBUG',
                'status' => (defined('WP_DEBUG') && WP_DEBUG) ? 'warning' : 'pass',
                'message' => (defined('WP_DEBUG') && WP_DEBUG) ? 'Attivo' : 'Disattivato'
            ),
            array(
                'label' => 'Logging Sicurezza',
                'status' => $this->config->get_option('logging_enabled') ? 'pass' : 'warning',
                'message' => $this->config->get_option('logging_enabled') ? 'Abilitato' : 'Disabilitato'
            ),
        );
        
        // V9 - Comunicazione (TLS)
        $items['V9 - Comunicazione (TLS)'] = array(
            array(
                'label' => 'Forza HTTPS',
                'status' => ($force_https || $is_ssl_active) ? 'pass' : ($is_local ? 'warning' : 'fail'),
                'message' => ($force_https || $is_ssl_active) ? 'Abilitato/Attivo' : ($is_local ? 'Non abilitato (ambiente locale)' : 'Non abilitato (richiesto in produzione)')
            ),
            array(
                'label' => 'HSTS',
                'status' => $this->config->get_option('hsts_enabled') ? 'pass' : ($is_ssl_active ? 'warning' : ($is_local ? 'warning' : 'fail')),
                'message' => $this->config->get_option('hsts_enabled') ? 'Abilitato' : 'Disabilitato'
            ),
            array(
                'label' => 'Header Sicurezza',
                'status' => $this->config->get_option('security_headers') ? 'pass' : 'warning',
                'message' => $this->config->get_option('security_headers') ? 'Configurati' : 'Non configurati'
            ),
        );
        
        return $items;
    }
    
    private function get_security_logs($filters = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bocconi_security_logs';
        
        $where_clauses = array('1=1');
        $where_values = array();
        
        if (!empty($filters['log_level'])) {
            $where_clauses[] = 'level = %s';
            $where_values[] = $filters['log_level'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'DATE(datetime) >= %s';
            $where_values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'DATE(datetime) <= %s';
            $where_values[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_clauses);
        
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY datetime DESC LIMIT 100";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        $logs = $wpdb->get_results($query, ARRAY_A);
        
        return $logs ?: array();
    }
    
    // New AJAX handlers for UI integration
    
    public function ajax_get_score() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $score_data = $this->config->get_security_score();
        $level = 'low';
        
        if ($score_data['percentage'] >= 80) {
            $level = 'high';
        } elseif ($score_data['percentage'] >= 60) {
            $level = 'medium';
        }
        
        wp_send_json_success(array(
            'score' => $score_data['percentage'],
            'level' => $level,
            'details' => $score_data
        ));
    }
    
    public function ajax_get_recent_threats() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $threats = $this->parse_security_log(10);
        wp_send_json_success($threats);
    }
    
    public function ajax_get_system_status() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $status = $this->get_system_status();
        wp_send_json_success($status);
    }
    
    public function ajax_get_stats() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $stats = $this->get_security_stats();
        wp_send_json_success($stats);
    }
    
    public function ajax_get_active_preset() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $active_preset = get_option('bocconi_security_active_preset', 'basic');
        wp_send_json_success($active_preset);
    }
    
    public function ajax_apply_preset() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $preset = sanitize_text_field($_POST['preset']);
        
        if (!in_array($preset, array('basic', 'standard', 'advanced', 'maximum'))) {
            wp_send_json_error('Preset non valido');
        }
        
        $result = $this->config->apply_security_preset($preset);
        
        if ($result) {
            update_option('bocconi_security_active_preset', $preset);
            wp_send_json_success('Preset applicato con successo');
        } else {
            wp_send_json_error('Errore nell\'applicazione del preset');
        }
    }
    
    public function ajax_toggle_owasp_rule() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $rule_id = sanitize_text_field($_POST['rule_id']);
        $enabled = filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN);
        
        $options = $this->config->get_options();
        $options[$rule_id] = $enabled;
        
        if ($this->config->update_options($options)) {
            wp_send_json_success('Regola aggiornata');
        } else {
            wp_send_json_error('Errore nell\'aggiornamento della regola');
        }
    }
    
    public function ajax_get_threat_chart_data() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $chart_data = $this->get_threat_chart_data();
        wp_send_json_success($chart_data);
    }
    
    public function ajax_get_activity_chart_data() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $chart_data = $this->get_activity_chart_data();
        wp_send_json_success($chart_data);
    }
    
    public function ajax_run_scan() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $scan_type = sanitize_text_field($_POST['scan_type'] ?? 'full');
        
        // Reuse existing scan logic
        $this->ajax_security_scan();
    }
    
    public function ajax_clear_logs() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $log_file = WP_CONTENT_DIR . '/security.log';
        
        if (file_exists($log_file)) {
            if (file_put_contents($log_file, '') !== false) {
                wp_send_json_success('Log cancellati con successo');
            } else {
                wp_send_json_error('Errore nella cancellazione dei log');
            }
        } else {
            wp_send_json_success('Nessun log da cancellare');
        }
    }
    
    public function ajax_export_config() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $config_data = $this->config->export_config();
        $decoded_data = json_decode(base64_decode($config_data), true);
        
        wp_send_json_success($decoded_data);
    }
    
    public function ajax_import_config() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $config = $_POST['config'];
        $config_data = base64_encode($config);
        
        $result = $this->config->import_config($config_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success('Configurazione importata con successo');
        }
    }
    
    public function ajax_test_email() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $email = sanitize_email($_POST['email']);
        
        if (!is_email($email)) {
            wp_send_json_error('Indirizzo email non valido');
        }
        
        $subject = 'Test Email - Bocconi Security Plugin';
        $message = 'Questo è un messaggio di test dal plugin Bocconi Security. Se ricevi questa email, la configurazione email funziona correttamente.';
        
        if (wp_mail($email, $subject, $message)) {
            wp_send_json_success('Email di test inviata con successo');
        } else {
            wp_send_json_error('Errore nell\'invio dell\'email');
        }
    }
    
    public function ajax_toggle_auto_update() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $enabled = filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN);
        
        if (update_option('bocconi_security_auto_update', $enabled)) {
            wp_send_json_success('Impostazione aggiornata');
        } else {
            wp_send_json_error('Errore nell\'aggiornamento');
        }
    }
    
    // Helper methods for new AJAX handlers
    
    private function parse_security_log($limit = 10) {
        $log_file = WP_CONTENT_DIR . '/security.log';
        $threats = array();
        
        if (!file_exists($log_file)) {
            return $threats;
        }
        
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines); // Most recent first
        
        $count = 0;
        foreach ($lines as $line) {
            if ($count >= $limit) break;
            
            // Parse log line format: [datetime] LEVEL type IP:ip DATA:data
            if (preg_match('/\[(.*?)\]\s+(\w+)\s+(\w+)\s+IP:(\S+)\s+DATA:(.*)/', $line, $matches)) {
                $threats[] = array(
                    'time' => $matches[1],
                    'level' => strtolower($matches[2]),
                    'type' => $matches[3],
                    'ip' => $matches[4],
                    'description' => $this->get_threat_description($matches[3]),
                    'data' => json_decode($matches[5], true)
                );
                $count++;
            }
        }
        
        return $threats;
     }
      
      private function get_security_level($score) {
          if ($score >= 80) {
              return 'high';
          } elseif ($score >= 60) {
              return 'medium';
          } else {
              return 'low';
          }
      }
     
     private function get_threat_description($type) {
        $descriptions = array(
            'login_attempt' => 'Tentativo di login',
            'blocked_request' => 'Richiesta bloccata',
            'malware_detected' => 'Malware rilevato',
            'suspicious_activity' => 'Attività sospetta',
            'file_upload' => 'Upload file',
            'plugin_activation' => 'Attivazione plugin',
            'plugin_deactivation' => 'Disattivazione plugin'
        );
        
        return isset($descriptions[$type]) ? $descriptions[$type] : ucfirst(str_replace('_', ' ', $type));
    }
    
    private function get_security_stats() {
        $logger = BocconiSecurityLogger::getInstance();
        $threat_stats = $logger->get_threat_stats(30);
        
        $stats = array(
            'threats' => $threat_stats['total'],
            'blocked' => 0,
            'scans' => 0
        );
        
        // Count specific event types from database
        foreach ($threat_stats['by_type'] as $type_stat) {
            if (in_array($type_stat['type'], array('BLOCKED_REQUEST', 'FIREWALL_BLOCK'))) {
                $stats['blocked'] += (int) $type_stat['count'];
            }
            if (in_array($type_stat['type'], array('SECURITY_SCAN', 'MALWARE_SCAN'))) {
                $stats['scans'] += (int) $type_stat['count'];
            }
        }
        
        return $stats;
    }
    
    private function get_threat_chart_data() {
        $stats = $this->get_security_stats();
        
        return array(
            'labels' => array('Minacce', 'Bloccate', 'Scansioni'),
            'datasets' => array(
                array(
                    'data' => array($stats['threats'], $stats['blocked'], $stats['scans']),
                    'backgroundColor' => array('#ff6384', '#36a2eb', '#ffce56')
                )
            )
        );
    }
    
    private function get_activity_chart_data() {
        $log_file = WP_CONTENT_DIR . '/security.log';
        $activity = array();
        
        if (!file_exists($log_file)) {
            return array(
                'labels' => array(),
                'datasets' => array(
                    array(
                        'label' => 'Attività',
                        'data' => array(),
                        'borderColor' => '#36a2eb',
                        'fill' => false
                    )
                )
            );
        }
        
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Group by date
        foreach ($lines as $line) {
            if (preg_match('/\[(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                $date = $matches[1];
                if (!isset($activity[$date])) {
                    $activity[$date] = 0;
                }
                $activity[$date]++;
            }
        }
        
        // Get last 7 days
        $labels = array();
        $data = array();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d/m', strtotime($date));
            $data[] = isset($activity[$date]) ? $activity[$date] : 0;
        }
        
        return array(
            'labels' => $labels,
            'datasets' => array(
                array(
                    'label' => 'Eventi di sicurezza',
                    'data' => $data,
                    'borderColor' => '#36a2eb',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'fill' => true
                )
            )
        );
    }
    
    // Backup AJAX handlers
    public function ajax_create_backup() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $backup_manager = BocconiBackupManager::getInstance();
        $result = $backup_manager->create_manual_backup();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_get_backup_settings() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $backup_manager = BocconiBackupManager::getInstance();
        wp_send_json_success($backup_manager->get_backup_settings());
    }
    
    public function ajax_update_backup_settings() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $settings = array(
            'enabled' => sanitize_text_field($_POST['enabled']) === 'true',
            'interval' => sanitize_text_field($_POST['interval']),
            'retention_days' => intval($_POST['retention_days']),
            'include_database' => sanitize_text_field($_POST['include_database']) === 'true',
            'include_uploads' => sanitize_text_field($_POST['include_uploads']) === 'true'
        );
        
        $backup_manager = BocconiBackupManager::getInstance();
        $backup_manager->update_backup_settings($settings);
        
        wp_send_json_success($settings);
    }
    
    public function ajax_list_backups() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $backup_manager = BocconiBackupManager::getInstance();
        wp_send_json_success($backup_manager->list_backups());
    }
    
    public function ajax_delete_backup() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $filename = sanitize_file_name($_POST['filename']);
        $backup_manager = BocconiBackupManager::getInstance();
        $result = $backup_manager->delete_backup($filename);
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => 'Errore durante l\'eliminazione del backup'));
        }
    }
    
    // File Integrity AJAX handlers
    public function ajax_init_integrity_baseline() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $monitor = BocconiFileIntegrityMonitor::getInstance();
        $files_count = $monitor->initialize_baseline();
        
        wp_send_json_success(array(
            'message' => "Baseline inizializzato con {$files_count} file",
            'files_count' => $files_count
        ));
    }
    
    public function ajax_check_integrity() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $monitor = BocconiFileIntegrityMonitor::getInstance();
        $result = $monitor->get_last_check_results();
        
        wp_send_json_success($result);
    }
    
    public function ajax_get_integrity_settings() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $monitor = BocconiFileIntegrityMonitor::getInstance();
        wp_send_json_success($monitor->get_integrity_settings());
    }
    
    public function ajax_update_integrity_settings() {
        check_ajax_referer('bocconi_security_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $settings = array(
            'enabled' => sanitize_text_field($_POST['enabled']) === 'true',
            'interval' => sanitize_text_field($_POST['interval']),
            'notifications' => sanitize_text_field($_POST['notifications']) === 'true'
        );
        
        $monitor = BocconiFileIntegrityMonitor::getInstance();
        $monitor->update_integrity_settings($settings);
        
        wp_send_json_success($settings);
    }
}

// Initialize admin dashboard
if (is_admin()) {
    new BocconiSecurityAdminDashboard();
}