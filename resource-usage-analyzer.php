<?php
/**
 * Plugin Name: Resource Usage Analyzer
 * Plugin URI: https://wpbay.com/store/cognitowp/
 * Description: Analyzes which plugins are contributing to output (JS/CSS/HTML) and identifies dormant plugins.
 * Version: 1.1.0
 * Author: CognitoWP
 * Author URI: https://wpbay.com/store/cognitowp/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: resource-usage-analyzer
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if ( ! function_exists( 'cwppua_wpbay_sdk' ) ) {
    function cwppua_wpbay_sdk() {
        require_once dirname( __FILE__ ) . '/wpbay-sdk/WPBay_Loader.php';
        $sdk_instance = false;
        global $wpbay_sdk_latest_loader;
        $sdk_loader_class = $wpbay_sdk_latest_loader;
        $sdk_params = array(
            'api_key'                 => 'OIAKDA-LTRHGZK4VP5ZXK3DECZI2OJACI',
            'wpbay_product_id'        => '', 
            'product_file'            => __FILE__,
            'activation_redirect'     => '',
            'is_free'                 => true,
            'is_upgradable'           => false,
            'uploaded_to_wp_org'      => false,
            'disable_feedback'        => false,
            'disable_support_page'    => false,
            'disable_contact_form'    => false,
            'disable_upgrade_form'    => true,
            'disable_analytics'       => false,
            'rating_notice'           => '1 week',
            'debug_mode'              => 'false',
            'no_activation_required'  => false,
            'menu_data'               => array(
                'menu_slug' => ''
            ),
        );
        if ( class_exists( $sdk_loader_class ) ) {
            $sdk_instance = $sdk_loader_class::load_sdk( $sdk_params );
        }
        return $sdk_instance;
    }
    cwppua_wpbay_sdk();
    do_action( 'cwppua_wpbay_sdk_loaded' );
}

// Define plugin constants
define('RESOURCE_USAGE_ANALYZER_VERSION', '1.0.0');
define('RESOURCE_USAGE_ANALYZER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESOURCE_USAGE_ANALYZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RESOURCE_USAGE_ANALYZER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function resource_usage_analyzer_activate() {
    require_once RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer-activator.php';
    Resource_Usage_Analyzer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function resource_usage_analyzer_deactivate() {
    require_once RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer-deactivator.php';
    Resource_Usage_Analyzer_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'resource_usage_analyzer_activate');
register_deactivation_hook(__FILE__, 'resource_usage_analyzer_deactivate');

/**
 * The core plugin class.
 */
require RESOURCE_USAGE_ANALYZER_PLUGIN_DIR . 'includes/class-resource-usage-analyzer.php';

/**
 * Begins execution of the plugin.
 */
function run_resource_usage_analyzer() {
    $plugin = new Resource_Usage_Analyzer();
    $plugin->run();
}
run_resource_usage_analyzer();