<?php
/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    Resource_Usage_Analyzer
 * @subpackage Resource_Usage_Analyzer/includes
 */

class Resource_Usage_Analyzer_Deactivator {

    /**
     * Plugin deactivation routine.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('resource_usage_analyzer_scheduled_scan');
        
        // Clear transients
        delete_transient('resource_usage_analyzer_results');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}