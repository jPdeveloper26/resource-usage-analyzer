<?php
/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    Resource_Usage_Analyzer
 * @subpackage Resource_Usage_Analyzer/includes
 */

class Resource_Usage_Analyzer_Activator {

    /**
     * Plugin activation routine.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create database tables if needed
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Clear any existing transients
        delete_transient('resource_usage_analyzer_results');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables.
     *
     * @since    1.0.0
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'resource_usage_analyzer_logs';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            scan_date datetime DEFAULT CURRENT_TIMESTAMP,
            scan_data longtext,
            PRIMARY KEY (id),
            KEY scan_date (scan_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Set default plugin options.
     *
     * @since    1.0.0
     */
    private static function set_default_options() {
        $default_options = array(
            'keep_logs' => 30, // Days to keep scan logs
            'auto_scan' => false,
            'scan_frequency' => 'weekly',
            'email_reports' => false,
            'email_address' => get_option('admin_email')
        );
        
        foreach ($default_options as $option_name => $default_value) {
            if (get_option('resource_usage_analyzer_' . $option_name) === false) {
                add_option('resource_usage_analyzer_' . $option_name, $default_value);
            }
        }
    }
}