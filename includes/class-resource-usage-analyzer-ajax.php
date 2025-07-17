<?php
/**
 * The AJAX functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Resource_Usage_Analyzer
 * @subpackage Resource_Usage_Analyzer/includes
 */

class Resource_Usage_Analyzer_Ajax {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name
     * @param    string    $version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Handle scan request via AJAX.
     */
    public function handle_scan_request() {
        // Verify nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		if (!wp_verify_nonce($nonce, 'resource_usage_analyzer_nonce')) {
			wp_send_json_error(array('message' => __('Security check failed.', 'resource-usage-analyzer')));
		}

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'resource-usage-analyzer')));
        }

        try {
            // Perform scan
            $scanner = new Resource_Usage_Analyzer_Scanner();
            $scan_results = $scanner->scan_resources();
            
            // Store results in transient for later retrieval
            set_transient('resource_usage_analyzer_results', $scan_results, HOUR_IN_SECONDS);
            
            wp_send_json_success(array(
                'message' => __('Scan completed successfully.', 'resource-usage-analyzer'),
                'results' => $scan_results
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(
				      // translators: %s is replaced with the error message
                    __('An error occurred during scanning: %s', 'resource-usage-analyzer'),
                    esc_html($e->getMessage())
                )
            ));
        }
    }

    /**
     * Handle get report request via AJAX.
     */
    public function handle_get_report() {
        // Verify nonce
       $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		if (!wp_verify_nonce($nonce, 'resource_usage_analyzer_nonce')) {
		wp_send_json_error(array('message' => __('Security check failed.', 'resource-usage-analyzer')));
		}

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'resource-usage-analyzer')));
        }

        // Get scan results from transient
        $scan_results = get_transient('resource_usage_analyzer_results');
        
        if (!$scan_results) {
            wp_send_json_error(array('message' => __('No scan results found. Please run a scan first.', 'resource-usage-analyzer')));
        }

        try {
            // Generate report
            $reporter = new Resource_Usage_Analyzer_Reporter();
            $report = $reporter->generate_report($scan_results);
            
            // Generate HTML
            $html_report = $reporter->generate_html_report($report);
            $html_recommendations = $reporter->generate_recommendations_html($report['recommendations']);
            
            wp_send_json_success(array(
                'report' => $html_report,
                'recommendations' => $html_recommendations,
                'data' => $report
            ));
        } catch (Exception $e) {
			
            wp_send_json_error(array(
                'message' => sprintf(
				/* translators: %s is replaced with the error message */
                    __('An error occurred while generating the report: %s', 'resource-usage-analyzer'),
                    esc_html($e->getMessage())
                )
            ));
        }
    }
}