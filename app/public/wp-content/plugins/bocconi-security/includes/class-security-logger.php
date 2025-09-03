<?php
if (!defined('ABSPATH')) {
    exit;
}

class BocconiSecurityLogger {
    private static $instance = null;
    private $log_file;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/security.log';
    }

    public function log_security_event(string $type, array $data = array(), string $level = 'info'): void {
        $entry = array(
            'datetime' => current_time('mysql'),
            'level'    => $level,
            'type'     => $type,
            'ip'       => $this->get_client_ip(),
            'user_id'  => get_current_user_id() ?: null,
            'data'     => $data,
        );

        // Log to database if table exists
        $this->log_to_database($entry);
        
        // Also log to file for backward compatibility
        $line = '[' . $entry['datetime'] . '] ' . strtoupper($entry['level']) . ' ' . $entry['type'] . ' IP:' . $entry['ip'] . ' DATA:' . wp_json_encode($entry['data']) . PHP_EOL;
        error_log($line, 3, $this->log_file);
    }
    
    private function log_to_database(array $entry): void {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bocconi_security_logs';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            $wpdb->insert(
                $table_name,
                array(
                    'datetime' => $entry['datetime'],
                    'level'    => $entry['level'],
                    'type'     => $entry['type'],
                    'ip'       => $entry['ip'],
                    'user_id'  => $entry['user_id'],
                    'data'     => wp_json_encode($entry['data'])
                ),
                array('%s', '%s', '%s', '%s', '%d', '%s')
            );
        }
    }
    
    public function get_recent_logs(int $limit = 50): array {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bocconi_security_logs';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return array();
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY datetime DESC LIMIT %d",
            $limit
        ), ARRAY_A);
        
        return $results ?: array();
    }
    
    public function get_threat_stats(int $days = 30): array {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bocconi_security_logs';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return array('total' => 0, 'by_type' => array(), 'by_level' => array());
        }
        
        $date_from = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE datetime >= %s",
            $date_from
        ));
        
        $by_type = $wpdb->get_results($wpdb->prepare(
            "SELECT type, COUNT(*) as count FROM $table_name WHERE datetime >= %s GROUP BY type ORDER BY count DESC",
            $date_from
        ), ARRAY_A);
        
        $by_level = $wpdb->get_results($wpdb->prepare(
            "SELECT level, COUNT(*) as count FROM $table_name WHERE datetime >= %s GROUP BY level ORDER BY count DESC",
            $date_from
        ), ARRAY_A);
        
        return array(
            'total' => (int) $total,
            'by_type' => $by_type ?: array(),
            'by_level' => $by_level ?: array()
        );
    }

    private function get_client_ip(): string {
        $ip_keys = array('HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_X_CLUSTER_CLIENT_IP','HTTP_FORWARDED_FOR','HTTP_FORWARDED','REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                foreach (explode(',', (string) $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
    }
}