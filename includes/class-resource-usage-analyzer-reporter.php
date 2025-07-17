<?php
/**
 * The reporter functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Resource_Usage_Analyzer
 * @subpackage Resource_Usage_Analyzer/includes
 */

class Resource_Usage_Analyzer_Reporter {

    /**
     * Generate report from scan results.
     *
     * @param array $scan_results
     * @return array
     */
    public function generate_report($scan_results) {
        $report = array(
            'summary' => $this->generate_summary($scan_results),
            'details' => $this->generate_details($scan_results),
            'recommendations' => $this->generate_recommendations($scan_results),
            'export_data' => $this->prepare_export_data($scan_results)
        );
        
        return $report;
    }

    /**
     * Generate summary statistics.
     *
     * @param array $scan_results
     * @return array
     */
    private function generate_summary($scan_results) {
        $total_plugins = count(get_option('active_plugins', array()));
        $contributing_plugins = count(array_unique(array_merge(
            array_keys($scan_results['scripts']),
            array_keys($scan_results['styles']),
            array_keys($scan_results['html_contributions'])
        )));
        $dormant_plugins = count($scan_results['dormant_plugins']);
        
        $total_scripts = 0;
        $total_styles = 0;
        $total_size = 0;
        
        foreach ($scan_results['scripts'] as $plugin_scripts) {
            $total_scripts += count($plugin_scripts);
            foreach ($plugin_scripts as $script) {
                $total_size += $script['size'];
            }
        }
        
        foreach ($scan_results['styles'] as $plugin_styles) {
            $total_styles += count($plugin_styles);
            foreach ($plugin_styles as $style) {
                $total_size += $style['size'];
            }
        }
        
        return array(
            'total_plugins' => $total_plugins,
            'contributing_plugins' => $contributing_plugins,
            'dormant_plugins' => $dormant_plugins,
            'total_scripts' => $total_scripts,
            'total_styles' => $total_styles,
            'total_size' => $total_size,
            'formatted_size' => size_format($total_size)
        );
    }

    /**
     * Generate detailed plugin analysis.
     *
     * @param array $scan_results
     * @return array
     */
    private function generate_details($scan_results) {
        $details = array();
        
        // Combine all plugin data
        $all_plugins = array();
        
        // Add script contributions
        foreach ($scan_results['scripts'] as $plugin => $scripts) {
            if (!isset($all_plugins[$plugin])) {
                $all_plugins[$plugin] = array(
                    'scripts' => array(),
                    'styles' => array(),
                    'html' => false,
                    'total_size' => 0
                );
            }
            $all_plugins[$plugin]['scripts'] = $scripts;
            foreach ($scripts as $script) {
                $all_plugins[$plugin]['total_size'] += $script['size'];
            }
        }
        
        // Add style contributions
        foreach ($scan_results['styles'] as $plugin => $styles) {
            if (!isset($all_plugins[$plugin])) {
                $all_plugins[$plugin] = array(
                    'scripts' => array(),
                    'styles' => array(),
                    'html' => false,
                    'total_size' => 0
                );
            }
            $all_plugins[$plugin]['styles'] = $styles;
            foreach ($styles as $style) {
                $all_plugins[$plugin]['total_size'] += $style['size'];
            }
        }
        
        // Add HTML contributions
        foreach ($scan_results['html_contributions'] as $plugin => $contributions) {
            if (!isset($all_plugins[$plugin])) {
                $all_plugins[$plugin] = array(
                    'scripts' => array(),
                    'styles' => array(),
                    'html' => false,
                    'total_size' => 0
                );
            }
            $all_plugins[$plugin]['html'] = $contributions;
        }
        
        // Sort by total size
        uasort($all_plugins, function($a, $b) {
            return $b['total_size'] - $a['total_size'];
        });
        
        return $all_plugins;
    }

    /**
     * Generate recommendations based on scan results.
     *
     * @param array $scan_results
     * @return array
     */
    private function generate_recommendations($scan_results) {
        $recommendations = array();
        
        // Dormant plugins recommendation
        if (!empty($scan_results['dormant_plugins'])) {
            $dormant_plugin_names = array();
            foreach ($scan_results['dormant_plugins'] as $plugin_file => $plugin_data) {
                $dormant_plugin_names[] = $plugin_data['name'];
            }
            
            $recommendations[] = array(
                'type' => 'warning',
                'title' => __('Dormant Plugins Detected', 'resource-usage-analyzer'),
                'message' => sprintf(
				/* translators: %d is replaced with the number of plugins */
                    __('Found %d plugin(s) that appear to be dormant (not contributing any resources). Consider deactivating these plugins to improve performance.', 'resource-usage-analyzer'),
                    count($scan_results['dormant_plugins'])
                ),
                'plugins' => $dormant_plugin_names
            );
        }
        
        // Heavy resource usage recommendation
        $heavy_plugins = array();
        foreach ($scan_results['scripts'] as $plugin => $scripts) {
            $plugin_size = 0;
            foreach ($scripts as $script) {
                $plugin_size += $script['size'];
            }
            if ($plugin_size > 500000) { // 500KB
                $heavy_plugins[$plugin] = $plugin_size;
            }
        }
        
        foreach ($scan_results['styles'] as $plugin => $styles) {
            $plugin_size = 0;
            foreach ($styles as $style) {
                $plugin_size += $style['size'];
            }
            if (isset($heavy_plugins[$plugin])) {
                $heavy_plugins[$plugin] += $plugin_size;
            } elseif ($plugin_size > 500000) {
                $heavy_plugins[$plugin] = $plugin_size;
            }
        }
        
        if (!empty($heavy_plugins)) {
            arsort($heavy_plugins);
            $heavy_plugins_formatted = array();
            foreach ($heavy_plugins as $plugin => $size) {
                $heavy_plugins_formatted[] = sprintf('%s (%s)', $plugin, size_format($size));
            }
            
            $recommendations[] = array(
                'type' => 'info',
                'title' => __('Heavy Resource Usage', 'resource-usage-analyzer'),
                'message' => __('The following plugins are loading significant resources. Consider optimizing or finding lighter alternatives.', 'resource-usage-analyzer'),
                'plugins' => $heavy_plugins_formatted
            );
        }
        
        // Multiple script/style files recommendation
        foreach ($scan_results['scripts'] as $plugin => $scripts) {
            if (count($scripts) > 5) {
                $recommendations[] = array(
                    'type' => 'optimization',
					// translators: %s is replaced with the script number
                    'title' => sprintf(__('Multiple Scripts: %s', 'resource-usage-analyzer'), $plugin),
                    'message' => sprintf(
					        // translators: %d is replaced with the plugin script files
                        __('This plugin is loading %d separate script files. Consider combining them to reduce HTTP requests.', 'resource-usage-analyzer'),
                        count($scripts)
                    )
                );
            }
        }
        
        return $recommendations;
    }

    /**
     * Prepare data for export.
     *
     * @param array $scan_results
     * @return array
     */
    private function prepare_export_data($scan_results) {
        return array(
            'scan_date' => current_time('mysql'),
            'site_url' => get_site_url(),
            'wordpress_version' => get_bloginfo('version'),
            'results' => $scan_results
        );
    }

    /**
     * Generate HTML report.
     *
     * @param array $report
     * @return string
     */
    public function generate_html_report($report) {
        ob_start();
        ?>
        <div class="rua-report">
            <div class="rua-summary">
                <h3><?php esc_html_e('Summary', 'resource-usage-analyzer'); ?></h3>
                <div class="rua-stats">
                    <div class="rua-stat">
                        <span class="rua-stat-value"><?php echo esc_html($report['summary']['total_plugins']); ?></span>
                        <span class="rua-stat-label"><?php esc_html_e('Total Plugins', 'resource-usage-analyzer'); ?></span>
                    </div>
                    <div class="rua-stat">
                        <span class="rua-stat-value"><?php echo esc_html($report['summary']['contributing_plugins']); ?></span>
                        <span class="rua-stat-label"><?php esc_html_e('Contributing Plugins', 'resource-usage-analyzer'); ?></span>
                    </div>
                    <div class="rua-stat">
                        <span class="rua-stat-value"><?php echo esc_html($report['summary']['dormant_plugins']); ?></span>
                        <span class="rua-stat-label"><?php esc_html_e('Dormant Plugins', 'resource-usage-analyzer'); ?></span>
                    </div>
                    <div class="rua-stat">
                        <span class="rua-stat-value"><?php echo esc_html($report['summary']['total_scripts']); ?></span>
                        <span class="rua-stat-label"><?php esc_html_e('Total Scripts', 'resource-usage-analyzer'); ?></span>
                    </div>
                    <div class="rua-stat">
                        <span class="rua-stat-value"><?php echo esc_html($report['summary']['total_styles']); ?></span>
                        <span class="rua-stat-label"><?php esc_html_e('Total Styles', 'resource-usage-analyzer'); ?></span>
                    </div>
                    <div class="rua-stat">
                        <span class="rua-stat-value"><?php echo esc_html($report['summary']['formatted_size']); ?></span>
                        <span class="rua-stat-label"><?php esc_html_e('Total Size', 'resource-usage-analyzer'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="rua-details">
                <h3><?php esc_html_e('Plugin Resource Usage', 'resource-usage-analyzer'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Plugin', 'resource-usage-analyzer'); ?></th>
                            <th><?php esc_html_e('Scripts', 'resource-usage-analyzer'); ?></th>
                            <th><?php esc_html_e('Styles', 'resource-usage-analyzer'); ?></th>
                            <th><?php esc_html_e('HTML Output', 'resource-usage-analyzer'); ?></th>
                            <th><?php esc_html_e('Total Size', 'resource-usage-analyzer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['details'] as $plugin => $data) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($plugin); ?></strong></td>
                                <td><?php echo esc_html(count($data['scripts'])); ?></td>
                                <td><?php echo esc_html(count($data['styles'])); ?></td>
                                <td>
                                    <?php if ($data['html']) : ?>
                                        <span class="dashicons dashicons-yes"></span>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-no-alt"></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(size_format($data['total_size'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate recommendations HTML.
     *
     * @param array $recommendations
     * @return string
     */
    public function generate_recommendations_html($recommendations) {
        ob_start();
        ?>
        <div class="rua-recommendations">
            <?php foreach ($recommendations as $recommendation) : ?>
                <div class="notice notice-<?php echo esc_attr($recommendation['type']); ?> rua-recommendation">
                    <h4><?php echo esc_html($recommendation['title']); ?></h4>
                    <p><?php echo esc_html($recommendation['message']); ?></p>
                    <?php if (!empty($recommendation['plugins'])) : ?>
                        <ul>
                            <?php foreach ($recommendation['plugins'] as $plugin) : ?>
                                <li>
                                    <?php 
                                    if (is_array($plugin)) {
                                        echo esc_html($plugin['name'] ?? 'Unknown');
                                    } else {
                                        echo esc_html($plugin);
                                    }
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}