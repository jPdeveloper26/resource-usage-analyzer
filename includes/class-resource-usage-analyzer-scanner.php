<?php
/**
 * The scanner functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Resource_Usage_Analyzer
 * @subpackage Resource_Usage_Analyzer/includes
 */

class Resource_Usage_Analyzer_Scanner {

    /**
     * Tracked resources during scan.
     *
     * @var array
     */
    private $resources = array(
        'scripts' => array(),
        'styles' => array(),
        'html_contributions' => array(),
        'dormant_plugins' => array()
    );

    /**
     * Active plugins list.
     *
     * @var array
     */
    private $active_plugins;

    /**
     * Initialize the scanner.
     */
    public function __construct() {
        $this->active_plugins = get_option('active_plugins', array());
    }

    /**
     * Perform a full resource scan.
     *
     * @return array
     */
    public function scan_resources() {
        // First, scan all plugin files for registered scripts and styles
        $this->scan_plugin_files();
        
        // Also analyze currently registered scripts and styles
        global $wp_scripts, $wp_styles;
        
        if ($wp_scripts && is_object($wp_scripts)) {
            $this->analyze_scripts($wp_scripts, 'registered');
        }
        
        if ($wp_styles && is_object($wp_styles)) {
            $this->analyze_styles($wp_styles, 'registered');
        }
        
        // Track HTML output contributions
        $this->analyze_plugin_contributions();
        
        // Identify dormant plugins
        $this->identify_dormant_plugins();
        
        return $this->get_scan_results();
    }
    
    /**
     * Scan plugin files for assets.
     */
    private function scan_plugin_files() {
        foreach ($this->active_plugins as $plugin) {
            $plugin_dir = dirname($plugin);
            $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_dir;
            
            // Scan for JS files
            $js_files = $this->find_files($plugin_path, array('js'));
            foreach ($js_files as $js_file) {
                $relative_path = str_replace(WP_PLUGIN_DIR . '/', '', $js_file);
                $url = plugins_url($relative_path);
                
                if (!isset($this->resources['scripts'][$plugin_dir])) {
                    $this->resources['scripts'][$plugin_dir] = array();
                }
                
                $this->resources['scripts'][$plugin_dir][] = array(
                    'handle' => basename($js_file, '.js'),
                    'src' => $url,
                    'context' => 'file-scan',
                    'size' => filesize($js_file)
                );
            }
            
            // Scan for CSS files
            $css_files = $this->find_files($plugin_path, array('css'));
            foreach ($css_files as $css_file) {
                $relative_path = str_replace(WP_PLUGIN_DIR . '/', '', $css_file);
                $url = plugins_url($relative_path);
                
                if (!isset($this->resources['styles'][$plugin_dir])) {
                    $this->resources['styles'][$plugin_dir] = array();
                }
                
                $this->resources['styles'][$plugin_dir][] = array(
                    'handle' => basename($css_file, '.css'),
                    'src' => $url,
                    'context' => 'file-scan',
                    'size' => filesize($css_file)
                );
            }
        }
    }
    
    /**
     * Find files with specific extensions.
     *
     * @param string $directory
     * @param array $extensions
     * @return array
     */
    private function find_files($directory, $extensions) {
        $files = array();
        
        if (!is_dir($directory)) {
            return $files;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
                if (in_array($ext, $extensions)) {
                    // Skip minified versions if original exists, and node_modules
                    $path = $file->getPathname();
                    if (strpos($path, 'node_modules') === false && 
                        strpos($path, 'vendor') === false &&
                        strpos($path, '.min.') === false) {
                        $files[] = $path;
                    }
                }
            }
        }
        
        return $files;
    }

    /**
     * Track frontend resources.
     */
    public function track_frontend_resources() {
        global $wp_scripts, $wp_styles;
        
        $this->analyze_scripts($wp_scripts, 'frontend');
        $this->analyze_styles($wp_styles, 'frontend');
    }

    /**
     * Track admin resources.
     */
    public function track_admin_resources() {
        global $wp_scripts, $wp_styles;
        
        $this->analyze_scripts($wp_scripts, 'admin');
        $this->analyze_styles($wp_styles, 'admin');
    }

    /**
     * Analyze registered scripts.
     *
     * @param WP_Scripts $wp_scripts
     * @param string $context
     */
    private function analyze_scripts($wp_scripts, $context) {
        if (!$wp_scripts || !is_object($wp_scripts) || empty($wp_scripts->registered)) {
            return;
        }

        foreach ($wp_scripts->registered as $handle => $script) {
            if (empty($script->src)) {
                continue;
            }
            
            $plugin = $this->identify_resource_plugin($script->src);
            if ($plugin) {
                if (!isset($this->resources['scripts'][$plugin])) {
                    $this->resources['scripts'][$plugin] = array();
                }
                
                $this->resources['scripts'][$plugin][] = array(
                    'handle' => $handle,
                    'src' => $script->src,
                    'context' => $context,
                    'size' => $this->get_file_size($script->src)
                );
            }
        }
    }

    /**
     * Analyze registered styles.
     *
     * @param WP_Styles $wp_styles
     * @param string $context
     */
    private function analyze_styles($wp_styles, $context) {
        if (!$wp_styles || !is_object($wp_styles) || empty($wp_styles->registered)) {
            return;
        }

        foreach ($wp_styles->registered as $handle => $style) {
            if (empty($style->src)) {
                continue;
            }
            
            $plugin = $this->identify_resource_plugin($style->src);
            if ($plugin) {
                if (!isset($this->resources['styles'][$plugin])) {
                    $this->resources['styles'][$plugin] = array();
                }
                
                $this->resources['styles'][$plugin][] = array(
                    'handle' => $handle,
                    'src' => $style->src,
                    'context' => $context,
                    'size' => $this->get_file_size($style->src)
                );
            }
        }
    }

    /**
     * Identify which plugin a resource belongs to.
     *
     * @param string $src
     * @return string|false
     */
    private function identify_resource_plugin($src) {
        if (empty($src)) {
            return false;
        }

        // Check if it's a plugin resource
        if (strpos($src, WP_PLUGIN_URL) !== false || strpos($src, 'wp-content/plugins') !== false) {
            $plugin_path = str_replace(WP_PLUGIN_URL . '/', '', $src);
            $plugin_path = preg_replace('/\?.*$/', '', $plugin_path); // Remove query strings
            $parts = explode('/', $plugin_path);
            
            if (!empty($parts[0])) {
                return $parts[0];
            }
        }
        
        return false;
    }

    /**
     * Analyze plugin HTML contributions.
     */
    private function analyze_plugin_contributions() {
        foreach ($this->active_plugins as $plugin) {
            $plugin_dir = dirname($plugin);
            
            // Check for common output patterns
            $has_shortcodes = $this->plugin_has_shortcodes($plugin);
            $has_widgets = $this->plugin_has_widgets($plugin);
            $has_hooks = $this->plugin_has_output_hooks($plugin);
            
            if ($has_shortcodes || $has_widgets || $has_hooks) {
                $this->resources['html_contributions'][$plugin_dir] = array(
                    'shortcodes' => $has_shortcodes,
                    'widgets' => $has_widgets,
                    'hooks' => $has_hooks
                );
            }
        }
    }

    /**
     * Identify dormant plugins.
     */
    private function identify_dormant_plugins() {
        $contributing_plugins = array_merge(
            array_keys($this->resources['scripts']),
            array_keys($this->resources['styles']),
            array_keys($this->resources['html_contributions'])
        );
        
        $contributing_plugins = array_unique($contributing_plugins);
        
        foreach ($this->active_plugins as $plugin) {
            $plugin_dir = dirname($plugin);
            
            if (!in_array($plugin_dir, $contributing_plugins)) {
                $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                $this->resources['dormant_plugins'][$plugin] = array(
                    'name' => $plugin_data['Name'],
                    'version' => $plugin_data['Version'],
                    'author' => $plugin_data['Author']
                );
            }
        }
    }

    /**
     * Check if plugin has registered shortcodes.
     *
     * @param string $plugin
     * @return bool
     */
    private function plugin_has_shortcodes($plugin) {
        global $shortcode_tags;
        
        if (empty($shortcode_tags)) {
            return false;
        }
        
        $plugin_dir = dirname($plugin);
        
        foreach ($shortcode_tags as $tag => $callback) {
            if (is_array($callback) && is_object($callback[0])) {
                $class = get_class($callback[0]);
                $reflection = new ReflectionClass($class);
                $filename = $reflection->getFileName();
                
                if ($filename && strpos($filename, $plugin_dir) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if plugin has registered widgets.
     *
     * @param string $plugin
     * @return bool
     */
    private function plugin_has_widgets($plugin) {
        global $wp_widget_factory;
        
        if (!isset($wp_widget_factory->widgets)) {
            return false;
        }
        
        $plugin_dir = dirname($plugin);
        
        foreach ($wp_widget_factory->widgets as $widget) {
            $reflection = new ReflectionClass($widget);
            $filename = $reflection->getFileName();
            
            if ($filename && strpos($filename, $plugin_dir) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if plugin has output hooks.
     *
     * @param string $plugin
     * @return bool
     */
    private function plugin_has_output_hooks($plugin) {
        global $wp_filter;
        
        $output_hooks = array(
            'wp_head', 'wp_footer', 'wp_body_open',
            'the_content', 'the_excerpt', 'the_title',
            'admin_head', 'admin_footer'
        );
        
        $plugin_dir = dirname($plugin);
        
        foreach ($output_hooks as $hook) {
            if (!isset($wp_filter[$hook])) {
                continue;
            }
            
            foreach ($wp_filter[$hook] as $priority => $functions) {
                foreach ($functions as $function) {
                    if (isset($function['function']) && is_array($function['function']) && is_object($function['function'][0])) {
                        $class = get_class($function['function'][0]);
                        $reflection = new ReflectionClass($class);
                        $filename = $reflection->getFileName();
                        
                        if ($filename && strpos($filename, $plugin_dir) !== false) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Get file size.
     *
     * @param string $url
     * @return int
     */
    private function get_file_size($url) {
        $file_path = str_replace(site_url(), ABSPATH, $url);
        $file_path = preg_replace('/\?.*$/', '', $file_path);
        
        if (file_exists($file_path)) {
            return filesize($file_path);
        }
        
        return 0;
    }

    /**
     * Get scan results.
     *
     * @return array
     */
    public function get_scan_results() {
        return $this->resources;
    }
}