=== Resource Usage Analyzer ===
Contributors: CognitoWP
Donate link: https://wpbay.com/store/cognitowp/
Tags: performance, optimization, resources, analysis, plugins
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Analyze which plugins are contributing to your site's output (CSS, JS, HTML) and identify dormant plugins to optimize performance.

== Description ==

Resource Usage Analyzer is a powerful tool that helps you understand how each plugin on your WordPress site contributes to your site's resources. It identifies which plugins are adding JavaScript, CSS, and HTML to your pages, and highlights dormant plugins that aren't contributing any output.

= Key Features =

* **Comprehensive Resource Analysis**: Scan all active plugins to see their JavaScript and CSS contributions
* **Dormant Plugin Detection**: Identify plugins that are active but not contributing any resources
* **Size Analysis**: See the total size of resources loaded by each plugin
* **HTML Output Detection**: Detect plugins that contribute HTML through shortcodes, widgets, or hooks
* **Performance Recommendations**: Get actionable recommendations to optimize your site
* **Export Functionality**: Export scan results for further analysis
* **User-Friendly Interface**: Clean, intuitive admin interface with visual statistics

= Benefits =

* **Improve Site Performance**: Remove or replace resource-heavy plugins
* **Reduce Page Load Time**: Identify and eliminate unnecessary scripts and styles
* **Clean Up Your Site**: Find and deactivate dormant plugins
* **Make Informed Decisions**: See exactly what each plugin contributes before deactivating

= How It Works =

1. Navigate to Tools > Resource Usage Analyzer
2. Click "Start Analysis" to scan your plugins
3. Review the detailed report showing each plugin's resource usage
4. Follow the recommendations to optimize your site
5. Export results for documentation or further analysis

== Installation ==

1. Upload the `resource-usage-analyzer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Tools > Resource Usage Analyzer to start analyzing

= Minimum Requirements =

* WordPress 5.0 or greater
* PHP version 7.2 or greater
* MySQL version 5.6 or greater

== Frequently Asked Questions ==

= Is this plugin safe to use on a production site? =

Yes, the plugin only analyzes resources and doesn't modify any files or database entries. It's completely safe to use on production sites.

= How accurate is the dormant plugin detection? =

The plugin checks for JavaScript, CSS, and HTML output contributions. Some plugins may work in the background without contributing visible resources, so always verify before deactivating.

= Can I schedule automatic scans? =

Not in the current version, but this feature is planned for a future release.

= Does this plugin slow down my site? =

No, the analysis only runs when you manually trigger it from the admin panel. It doesn't affect your site's frontend performance.

= Can I export the analysis results? =

Yes, you can export the results as a JSON file for further analysis or documentation.

== Screenshots ==

1. Main analysis dashboard showing the scan button and progress
2. Summary statistics after completing a scan
3. Detailed plugin resource usage table
4. Recommendations for optimizing your site
5. Export functionality for saving results

== Changelog ==

= 1.0.0 =
* Initial release
* Core scanning functionality
* Dormant plugin detection
* Resource size analysis
* HTML output detection
* Export functionality
* Recommendations engine

== Upgrade Notice ==

= 1.0.0 =
Initial release of Resource Usage Analyzer.

== Privacy Policy ==

This plugin does not collect, store, or transmit any personal data. All analysis is performed locally on your server, and results are stored temporarily in your WordPress database.
