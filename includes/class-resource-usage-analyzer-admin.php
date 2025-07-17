<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Resource_Usage_Analyzer
 * @subpackage Resource_Usage_Analyzer/includes
 */

class Resource_Usage_Analyzer_Admin {

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
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'tools_page_resource-usage-analyzer') {
            wp_enqueue_style(
                $this->plugin_name,
                RESOURCE_USAGE_ANALYZER_PLUGIN_URL . 'assets/css/resource-usage-analyzer-admin.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'tools_page_resource-usage-analyzer') {
            wp_enqueue_script(
                $this->plugin_name,
                RESOURCE_USAGE_ANALYZER_PLUGIN_URL . 'assets/js/resource-usage-analyzer-admin.js',
                array('jquery'),
                $this->version,
                false
            );

            wp_localize_script(
                $this->plugin_name,
                'resource_usage_analyzer_ajax',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('resource_usage_analyzer_nonce'),
                    'strings' => array(
                        'scanning' => __('Scanning...', 'resource-usage-analyzer'),
                        'error' => __('An error occurred. Please try again.', 'resource-usage-analyzer'),
                        'complete' => __('Scan complete!', 'resource-usage-analyzer')
                    )
                )
            );
        }
    }

    /**
     * Register the administration menu for this plugin.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_management_page(
            __('Resource Usage Analyzer', 'resource-usage-analyzer'),
            __('Resource Usage Analyzer', 'resource-usage-analyzer'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page')
        );
    }

    /**
     * Render the admin page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="resource-usage-analyzer-container">
                <div class="rua-section">
                    <h2><?php esc_html_e('Scan Resources', 'resource-usage-analyzer'); ?></h2>
                    <p><?php esc_html_e('Analyze which plugins are contributing to your site\'s output (CSS, JS, HTML) and identify dormant plugins.', 'resource-usage-analyzer'); ?></p>
                    
                    <form id="rua-scan-form" method="post">
                        <?php wp_nonce_field('resource_usage_analyzer_scan', 'rua_nonce'); ?>
                        <button type="submit" class="button button-primary" id="rua-scan-button">
                            <?php esc_html_e('Start Analysis', 'resource-usage-analyzer'); ?>
                        </button>
                    </form>
                    
                    <div id="rua-progress" style="display: none;">
                        <div class="rua-progress-bar">
                            <div class="rua-progress-fill"></div>
                        </div>
                        <p class="rua-progress-text"></p>
                    </div>
                </div>
                
                <div id="rua-results" class="rua-section" style="display: none;">
                    <h2><?php esc_html_e('Analysis Results', 'resource-usage-analyzer'); ?></h2>
                    <div id="rua-results-content"></div>
                </div>
                
                <div id="rua-recommendations" class="rua-section" style="display: none;">
                    <h2><?php esc_html_e('Recommendations', 'resource-usage-analyzer'); ?></h2>
                    <div id="rua-recommendations-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
}