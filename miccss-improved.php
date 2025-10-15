<?php
/**
 * MICCSS - Enhanced Version (Not Active - Use miccss.php instead)
 * 
 * This is an improved version of the plugin with additional features.
 * To use this version, delete miccss.php and rename this file to miccss.php
 * 
 * Enhanced Features:
 * - Singleton pattern
 * - Better error handling
 * - AJAX validation
 * - Caching system
 * - Minification
 * 
 * @package MICCSS
 * @author Thomas Kamau
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Define plugin constants
if (!defined('MICCSS_VERSION')) {
    define('MICCSS_VERSION', '1.0.0');
}
if (!defined('MICCSS_PLUGIN_DIR')) {
    define('MICCSS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('MICCSS_PLUGIN_URL')) {
    define('MICCSS_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('MICCSS_PLUGIN_FILE')) {
    define('MICCSS_PLUGIN_FILE', __FILE__);
}
if (!defined('MICCSS_PLUGIN_BASENAME')) {
    define('MICCSS_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

/**
 * Main MICCSS Plugin Class
 */
class MICCSS_Plugin
{

    /**
     * Single instance of the plugin
     * @var MICCSS_Plugin
     */
    private static $instance = null;

    /**
     * Plugin options
     * @var array
     */
    private $options = array();

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->load_options();
        $this->init_hooks();
    }

    /**
     * Get single instance of plugin
     * @return MICCSS_Plugin
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'defer_non_critical_css'), 20);
        add_action('wp_head', array($this, 'inline_critical_css'), 1);
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_miccss_validate_css', array($this, 'ajax_validate_css'));
        add_filter('plugin_action_links_' . MICCSS_PLUGIN_BASENAME, array($this, 'add_plugin_links'));

        // Plugin activation and deactivation hooks
        register_activation_hook(MICCSS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(MICCSS_PLUGIN_FILE, array($this, 'deactivate'));
    }

    /**
     * Load plugin options
     */
    private function load_options()
    {
        $this->options = array(
            'enabled' => get_option('miccss_enabled', true),
            'critical_css' => get_option('miccss_critical_css', ''),
            'exclude_handles' => get_option('miccss_exclude_handles', array('admin-bar', 'dashicons')),
            'defer_handles' => get_option('miccss_defer_handles', array('main-style')),
            'minify_css' => get_option('miccss_minify_css', false),
            'cache_css' => get_option('miccss_cache_css', true)
        );
    }

    /**
     * Initialize plugin
     */
    public function init()
    {
        // Load text domain for translations
        load_plugin_textdomain('miccss', false, dirname(MICCSS_PLUGIN_BASENAME) . '/languages');

        // Check if we need to update options format
        $this->maybe_update_options();
    }

    /**
     * Update options format if needed
     */
    private function maybe_update_options()
    {
        $version = get_option('miccss_version', '0.0.0');
        if (version_compare($version, MICCSS_VERSION, '<')) {
            $this->upgrade_options();
            update_option('miccss_version', MICCSS_VERSION);
        }
    }

    /**
     * Upgrade options for newer versions
     */
    private function upgrade_options()
    {
        // Convert string handles to arrays if needed
        $exclude_handles = get_option('miccss_exclude_handles', '');
        if (is_string($exclude_handles) && !empty($exclude_handles)) {
            $handles = array_map('trim', explode(',', $exclude_handles));
            update_option('miccss_exclude_handles', $handles);
        }

        $defer_handles = get_option('miccss_defer_handles', '');
        if (is_string($defer_handles) && !empty($defer_handles)) {
            $handles = array_map('trim', explode(',', $defer_handles));
            update_option('miccss_defer_handles', $handles);
        }
    }

    /**
     * Defer non-critical CSS using preload method
     */
    public function defer_non_critical_css()
    {
        if (!$this->options['enabled'] || is_admin()) {
            return;
        }

        // Apply the defer method as specified by the user
        add_filter('style_loader_tag', array($this, 'modify_style_loader_tag'), 10, 2);
    }

    /**
     * Modify style loader tag to implement defer method
     */
    public function modify_style_loader_tag($html, $handle)
    {
        // Skip if handle is in exclude list
        if (in_array($handle, $this->options['exclude_handles'])) {
            return $html;
        }

        // Apply preload method to main-style and other specified handles
        if ('main-style' === $handle || in_array($handle, $this->options['defer_handles'])) {
            // Implement the exact method provided by the user
            $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.rel='stylesheet'\"", $html);

            // Add media print to prevent render blocking
            if (strpos($html, 'media=') === false) {
                $html = str_replace('>', ' media="print">', $html);
            }

            // Add noscript fallback
            $noscript_html = str_replace(array("rel='preload' as='style'", 'onload="this.rel=\'stylesheet\'"', 'media="print"'), array("rel='stylesheet'", '', 'media="all"'), $html);
            $html .= '<noscript>' . $noscript_html . '</noscript>';
        }

        return $html;
    }

    /**
     * Inline critical CSS in head
     */
    public function inline_critical_css()
    {
        if (!$this->options['enabled'] || is_admin() || empty($this->options['critical_css'])) {
            return;
        }

        $critical_css = $this->options['critical_css'];

        // Minify CSS if option is enabled
        if ($this->options['minify_css']) {
            $critical_css = $this->minify_css($critical_css);
        }

        // Cache CSS if option is enabled
        if ($this->options['cache_css']) {
            $cache_key = 'miccss_critical_' . md5($critical_css);
            $cached_css = get_transient($cache_key);

            if (false === $cached_css) {
                $cached_css = $critical_css;
                set_transient($cache_key, $cached_css, HOUR_IN_SECONDS);
            }

            $critical_css = $cached_css;
        }

        echo "<!-- MICCSS Critical CSS Start -->\n";
        echo '<style id="miccss-critical-css" type="text/css">' . "\n";
        echo wp_strip_all_tags($critical_css);
        echo "\n</style>\n";
        echo "<!-- MICCSS Critical CSS End -->\n";
    }

    /**
     * Minify CSS
     */
    private function minify_css($css)
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove unnecessary whitespace
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
        $css = str_replace(array('; ', ' ;', ' {', '{ ', '} ', ' }', ': ', ' :'), array(';', ';', '{', '{', '}', '}', ':', ':'), $css);

        return trim($css);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_options_page(
            __('MICCSS Settings', 'miccss'),
            __('MICCSS', 'miccss'),
            'manage_options',
            'miccss-settings',
            array($this, 'admin_page')
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook)
    {
        if ('settings_page_miccss-settings' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'miccss-admin-css',
            MICCSS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            MICCSS_VERSION
        );

        wp_enqueue_script(
            'miccss-admin-js',
            MICCSS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MICCSS_VERSION,
            true
        );

        wp_localize_script('miccss-admin-js', 'miccss_ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('miccss_ajax_nonce'),
            'strings' => array(
                'saving' => __('Saving...', 'miccss'),
                'saved' => __('Saved!', 'miccss'),
                'error' => __('Error saving settings', 'miccss'),
                'confirm_clear' => __('Are you sure you want to clear all critical CSS? This action cannot be undone.', 'miccss')
            )
        ));
    }

    /**
     * AJAX handler for CSS validation
     */
    public function ajax_validate_css()
    {
        check_ajax_referer('miccss_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'miccss'));
        }

        $css = sanitize_textarea_field($_POST['css']);
        $validation = $this->validate_css($css);

        wp_send_json_success($validation);
    }

    /**
     * Validate CSS syntax
     */
    private function validate_css($css)
    {
        $errors = array();
        $warnings = array();
        $info = array();

        // Check for balanced braces
        $open_braces = substr_count($css, '{');
        $close_braces = substr_count($css, '}');

        if ($open_braces !== $close_braces) {
            $errors[] = sprintf(__('Mismatched braces: %d opening, %d closing', 'miccss'), $open_braces, $close_braces);
        }

        // Check CSS size
        $size_bytes = strlen($css);
        $size_kb = round($size_bytes / 1024, 2);

        if ($size_bytes > 14336) { // 14KB
            $warnings[] = sprintf(__('CSS size (%s KB) exceeds recommended 14KB limit', 'miccss'), $size_kb);
        }

        // Basic syntax checks
        if (strpos($css, 'undefined') !== false) {
            $errors[] = __('CSS contains "undefined" - check for JavaScript variables', 'miccss');
        }

        if (strpos($css, 'null') !== false) {
            $errors[] = __('CSS contains "null" - check for JavaScript variables', 'miccss');
        }

        // Count rules and selectors
        $rules = substr_count($css, '{');
        $lines = substr_count($css, "\n") + 1;

        $info['size_kb'] = $size_kb;
        $info['lines'] = $lines;
        $info['rules'] = $rules;

        return array(
            'errors' => $errors,
            'warnings' => $warnings,
            'info' => $info,
            'valid' => empty($errors)
        );
    }

    /**
     * Initialize admin settings
     */
    public function admin_init()
    {
        // Register settings with validation
        register_setting('miccss_settings', 'miccss_enabled', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));

        register_setting('miccss_settings', 'miccss_critical_css', array(
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => array($this, 'sanitize_critical_css')
        ));

        register_setting('miccss_settings', 'miccss_exclude_handles', array(
            'type' => 'array',
            'default' => array('admin-bar', 'dashicons'),
            'sanitize_callback' => array($this, 'sanitize_handle_array')
        ));

        register_setting('miccss_settings', 'miccss_defer_handles', array(
            'type' => 'array',
            'default' => array('main-style'),
            'sanitize_callback' => array($this, 'sanitize_handle_array')
        ));

        register_setting('miccss_settings', 'miccss_minify_css', array(
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));

        register_setting('miccss_settings', 'miccss_cache_css', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));
    }

    /**
     * Sanitize critical CSS
     */
    public function sanitize_critical_css($css)
    {
        // Remove script tags and other potentially harmful content
        $css = wp_strip_all_tags($css);

        // Remove any potential JavaScript
        $css = preg_replace('/javascript:/i', '', $css);
        $css = preg_replace('/expression\s*\(/i', '', $css);

        return $css;
    }

    /**
     * Sanitize handle arrays
     */
    public function sanitize_handle_array($handles)
    {
        if (is_string($handles)) {
            $handles = explode(',', $handles);
        }

        if (!is_array($handles)) {
            return array();
        }

        return array_map('sanitize_text_field', array_map('trim', $handles));
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_links($links)
    {
        $settings_link = '<a href="' . admin_url('options-general.php?page=miccss-settings') . '">' . __('Settings', 'miccss') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Admin page content
     */
    public function admin_page()
    {
        // Handle form submission
        if (isset($_POST['submit']) && check_admin_referer('miccss_settings', 'miccss_nonce')) {
            $this->save_settings();
        }

        // Reload options after save
        $this->load_options();

        include MICCSS_PLUGIN_DIR . 'templates/admin-page.php';
    }

    /**
     * Save settings
     */
    private function save_settings()
    {
        $settings = array(
            'miccss_enabled' => isset($_POST['miccss_enabled']),
            'miccss_critical_css' => $this->sanitize_critical_css($_POST['miccss_critical_css']),
            'miccss_exclude_handles' => $this->sanitize_handle_array($_POST['miccss_exclude_handles']),
            'miccss_defer_handles' => $this->sanitize_handle_array($_POST['miccss_defer_handles']),
            'miccss_minify_css' => isset($_POST['miccss_minify_css']),
            'miccss_cache_css' => isset($_POST['miccss_cache_css'])
        );

        foreach ($settings as $option => $value) {
            update_option($option, $value);
        }

        // Clear CSS cache when settings are updated
        $this->clear_css_cache();

        add_settings_error('miccss_settings', 'settings_updated', __('Settings saved successfully!', 'miccss'), 'updated');
    }

    /**
     * Clear CSS cache
     */
    private function clear_css_cache()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_miccss_critical_%'");
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Set default options
        add_option('miccss_enabled', true);
        add_option('miccss_critical_css', '');
        add_option('miccss_exclude_handles', array('admin-bar', 'dashicons'));
        add_option('miccss_defer_handles', array('main-style'));
        add_option('miccss_minify_css', false);
        add_option('miccss_cache_css', true);
        add_option('miccss_version', MICCSS_VERSION);

        // Create necessary database tables or perform other setup tasks
        $this->create_tables();

        // Schedule any recurring tasks
        if (!wp_next_scheduled('miccss_cleanup_cache')) {
            wp_schedule_event(time(), 'daily', 'miccss_cleanup_cache');
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Clear scheduled events
        wp_clear_scheduled_hook('miccss_cleanup_cache');

        // Clear cache
        $this->clear_css_cache();
    }

    /**
     * Create necessary database tables
     */
    private function create_tables()
    {
        // For future use - performance tracking, etc.
        // Currently not needed, but placeholder for potential features
    }
}

// Initialize the plugin
MICCSS_Plugin::get_instance();

/**
 * Helper functions for theme developers
 */

/**
 * Get critical CSS
 * @return string
 */
function miccss_get_critical_css()
{
    return get_option('miccss_critical_css', '');
}

/**
 * Check if MICCSS is enabled
 * @return bool
 */
function miccss_is_enabled()
{
    return (bool) get_option('miccss_enabled', true);
}

/**
 * Manually add critical CSS (for theme developers)
 * @param string $css Critical CSS to add
 * @param string $id Optional ID for the style tag
 */
function miccss_add_critical_css($css, $id = 'miccss-manual')
{
    if (!miccss_is_enabled() || empty($css)) {
        return;
    }

    $css = wp_strip_all_tags($css);
    echo '<style id="' . esc_attr($id) . '" type="text/css">' . "\n";
    echo '/* Manual Critical CSS */' . "\n";
    echo $css . "\n";
    echo '</style>' . "\n";
}

/**
 * Get plugin version
 * @return string
 */
function miccss_get_version()
{
    return MICCSS_VERSION;
}

/**
 * Check if current page should have critical CSS
 * @return bool
 */
function miccss_should_load_critical_css()
{
    if (!miccss_is_enabled()) {
        return false;
    }

    // Don't load on admin pages
    if (is_admin()) {
        return false;
    }

    // Don't load for AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return false;
    }

    // Don't load for REST API requests
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return false;
    }

    return true;
}