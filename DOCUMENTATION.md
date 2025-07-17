# Resource Usage Analyzer - Documentation

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Features](#features)
5. [Technical Details](#technical-details)
6. [API Reference](#api-reference)
7. [Hooks and Filters](#hooks-and-filters)
8. [Troubleshooting](#troubleshooting)
9. [Contributing](#contributing)

## Overview

Resource Usage Analyzer is a WordPress plugin designed to help site administrators understand and optimize their plugin usage. It provides detailed insights into which plugins are contributing resources (JavaScript, CSS, HTML) to your site and identifies dormant plugins that can be safely removed.

### Key Benefits

- **Performance Optimization**: Identify resource-heavy plugins
- **Site Cleanup**: Find and remove dormant plugins
- **Resource Tracking**: See exactly what each plugin contributes
- **Data-Driven Decisions**: Make informed choices about plugin usage

## Installation

### Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

### Installation Steps

1. Download the plugin ZIP file
2. Navigate to **Plugins > Add New** in your WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Upload the `resource-usage-analyzer` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress

## Usage

### Running a Scan

1. Navigate to **Tools > Resource Usage Analyzer**
2. Click the **Start Analysis** button
3. Wait for the scan to complete (usually takes 10-30 seconds)
4. Review the results and recommendations

### Understanding the Results

#### Summary Section
- **Total Plugins**: Number of active plugins
- **Contributing Plugins**: Plugins that add resources
- **Dormant Plugins**: Plugins with no detectable output
- **Total Scripts**: Combined JavaScript files
- **Total Styles**: Combined CSS files
- **Total Size**: Combined size of all resources

#### Plugin Resource Usage Table
- **Plugin**: Plugin directory name
- **Scripts**: Number of JavaScript files
- **Styles**: Number of CSS files
- **HTML Output**: Whether the plugin outputs HTML
- **Total Size**: Combined size of plugin resources

### Exporting Results

Click the **Export Results** button to download a JSON file containing:
- Scan timestamp
- Detailed resource breakdown
- Plugin information
- Recommendations

## Features

### Resource Detection

The plugin detects:
- **JavaScript Files**: Enqueued scripts (frontend and admin)
- **CSS Files**: Enqueued stylesheets
- **HTML Output**: Content from shortcodes, widgets, and hooks

### Dormant Plugin Detection

A plugin is considered dormant if it:
- Doesn't enqueue any scripts or styles
- Doesn't register any shortcodes
- Doesn't register any widgets
- Doesn't hook into content output filters

### Performance Analysis

The plugin analyzes:
- Resource file sizes
- Number of HTTP requests per plugin
- Context (frontend vs admin)

## Technical Details

### Architecture

```
resource-usage-analyzer/
├── includes/
│   ├── class-resource-usage-analyzer.php          # Core plugin class
│   ├── class-resource-usage-analyzer-loader.php   # Hook loader
│   ├── class-resource-usage-analyzer-i18n.php     # Internationalization
│   ├── class-resource-usage-analyzer-admin.php    # Admin interface
│   ├── class-resource-usage-analyzer-scanner.php  # Scanning engine
│   ├── class-resource-usage-analyzer-reporter.php # Report generation
│   ├── class-resource-usage-analyzer-ajax.php     # AJAX handlers
│   ├── class-resource-usage-analyzer-activator.php
│   └── class-resource-usage-analyzer-deactivator.php
├── assets/
│   ├── css/
│   │   └── resource-usage-analyzer-admin.css
│   └── js/
│       └── resource-usage-analyzer-admin.js
├── languages/
│   └── resource-usage-analyzer.pot
├── resource-usage-analyzer.php
├── readme.txt
└── DOCUMENTATION.md
```

### Database Schema

The plugin creates one table for storing scan logs:

```sql
CREATE TABLE wp_resource_usage_analyzer_logs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    scan_date datetime DEFAULT CURRENT_TIMESTAMP,
    scan_data longtext,
    PRIMARY KEY (id),
    KEY scan_date (scan_date)
);
```

### Options

The plugin stores these options:
- `resource_usage_analyzer_keep_logs`: Days to keep scan logs (default: 30)
- `resource_usage_analyzer_auto_scan`: Enable automatic scanning (default: false)
- `resource_usage_analyzer_scan_frequency`: Scan frequency (default: weekly)
- `resource_usage_analyzer_email_reports`: Enable email reports (default: false)
- `resource_usage_analyzer_email_address`: Email address for reports

## API Reference

### Classes

#### Resource_Usage_Analyzer_Scanner

Main scanning class that analyzes plugin resources.

```php
$scanner = new Resource_Usage_Analyzer_Scanner();
$results = $scanner->scan_resources();
```

#### Resource_Usage_Analyzer_Reporter

Generates reports from scan results.

```php
$reporter = new Resource_Usage_Analyzer_Reporter();
$report = $reporter->generate_report($scan_results);
```

### AJAX Actions

- `resource_usage_analyzer_scan`: Triggers a resource scan
- `resource_usage_analyzer_get_report`: Retrieves scan report

## Hooks and Filters

### Actions

```php
// Fired after a successful scan
do_action('resource_usage_analyzer_scan_complete', $scan_results);

// Fired before report generation
do_action('resource_usage_analyzer_before_report', $scan_results);
```

### Filters

```php
// Modify scan results
$scan_results = apply_filters('resource_usage_analyzer_scan_results', $scan_results);

// Modify recommendations
$recommendations = apply_filters('resource_usage_analyzer_recommendations', $recommendations, $scan_results);

// Modify export data
$export_data = apply_filters('resource_usage_analyzer_export_data', $export_data);
```

## Troubleshooting

### Common Issues

#### Scan Takes Too Long
- **Cause**: Large number of active plugins
- **Solution**: Increase PHP memory limit and execution time

#### Some Plugins Not Detected
- **Cause**: Plugins may load resources conditionally
- **Solution**: Run scan on different pages/contexts

#### Memory Errors
- **Cause**: Insufficient PHP memory
- **Solution**: Add to wp-config.php: `define('WP_MEMORY_LIMIT', '256M');`

### Debug Mode

Enable debug logging by adding to wp-config.php:
```php
define('RESOURCE_USAGE_ANALYZER_DEBUG', true);
```

### Coding Standards

- Follow WordPress Coding Standards
- Use proper sanitization and escaping
- Include PHPDoc comments
- Test with multiple PHP versions

### Reporting Issues

Please include:
- WordPress version
- PHP version
- Active plugins list
- Error messages (if any)
- Steps to reproduce

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Created by [CognitoWP](https://wpbay.com/store/cognitowp/)

### Third-party Libraries

- None currently used

## Changelog

See readme.txt for version history.