<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Resource_Usage_Analyzer
 * @subpackage Resource_Usage_Analyzer/includes
 */

class Resource_Usage_Analyzer {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Resource_Usage_Analyzer_Loader    $loader
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('RESOURCE_USAGE_ANALYZER_VERSION')) {
            $this->version = RESOURCE_USAGE_ANALYZER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'resource-usage-analyzer';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer-loader.php';
        require_once RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer-i18n.php';
        require_once RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer-admin.php';
        require_once RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer-scanner.php';
        require_once RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer-reporter.php';
        require_once RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer-ajax.php';

        $this->loader = new Resource_Usage_Analyzer_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Resource_Usage_Analyzer_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Resource_Usage_Analyzer_Admin($this->get_plugin_name(), $this->get_version());
        $plugin_ajax = new Resource_Usage_Analyzer_Ajax($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');

        // AJAX hooks
        $this->loader->add_action('wp_ajax_resource_usage_analyzer_scan', $plugin_ajax, 'handle_scan_request');
        $this->loader->add_action('wp_ajax_resource_usage_analyzer_get_report', $plugin_ajax, 'handle_get_report');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks.
     *
     * @since     1.0.0
     * @return    Resource_Usage_Analyzer_Loader
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}