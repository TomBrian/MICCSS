<?php
/**
 * Plugin Name: MICCSS - Manual Inline Critical CSS
 * Plugin URI: https://github.com/thomaskamau/miccss
 * Description: A WordPress plugin for manually inlining Critical CSS to improve page load performance. Defers non-critical CSS using preload with fallback.
 * Version: 1.0.0
 * Author: Thomas Kamau
 * Author URI: https://thomaskamau.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: miccss
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 *
 * @package MICCSS
 * @author Thomas Kamau
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MICCSS_VERSION', '1.0.0');
define('MICCSS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MICCSS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MICCSS_PLUGIN_FILE', __FILE__);

/**
 * Main MICCSS Plugin Class
 */
class MICCSS_Plugin
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'defer_non_critical_css'), 20);
        add_action('wp_head', array($this, 'inline_critical_css'), 1);
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));

        // Plugin activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Initialize plugin
     */
    public function init()
    {
        // Load text domain for translations
        load_plugin_textdomain('miccss', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Defer non-critical CSS using preload method
     */
    public function defer_non_critical_css()
    {
        // Get plugin settings
        $enabled = get_option('miccss_enabled', true);
        $exclude_handles = get_option('miccss_exclude_handles', array());

        if (!$enabled) {
            return;
        }

        // Enqueue main stylesheet
        wp_enqueue_style('main-style', get_stylesheet_uri(), [], MICCSS_VERSION);

        // Add filter to modify style tags
        add_filter('style_loader_tag', function ($html, $handle) use ($exclude_handles) {
            // Skip if handle is in exclude list
            if (in_array($handle, $exclude_handles)) {
                return $html;
            }

            // Apply preload method to main-style and other non-excluded styles
            if ('main-style' === $handle || $this->should_defer_style($handle)) {
                $html = str_replace(
                    "rel='stylesheet'",
                    "rel='preload' as='style' onload=\"this.rel='stylesheet'\" media='print'",
                    $html
                );
                // Add noscript fallback
                $html .= '<noscript>' . str_replace("rel='preload' as='style' onload=\"this.rel='stylesheet'\" media='print'", "rel='stylesheet'", $html) . '</noscript>';
            }

            return $html;
        }, 10, 2);
    }

    /**
     * Check if style should be deferred
     */
    private function should_defer_style($handle)
    {
        $defer_handles = get_option('miccss_defer_handles', array('main-style'));
        return in_array($handle, $defer_handles);
    }

    /**
     * Inline critical CSS in head
     */
    public function inline_critical_css()
    {
        $enabled = get_option('miccss_enabled', true);
        $critical_css = get_option('miccss_critical_css', '');

        if (!$enabled || empty($critical_css)) {
            return;
        }

        echo "<style id='miccss-critical'>\n";
        echo "/* MICCSS - Critical CSS */\n";
        echo wp_strip_all_tags($critical_css);
        echo "\n</style>\n";
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
     * Initialize admin settings
     */
    public function admin_init()
    {
        // Register settings
        register_setting('miccss_settings', 'miccss_enabled');
        register_setting('miccss_settings', 'miccss_critical_css');
        register_setting('miccss_settings', 'miccss_exclude_handles');
        register_setting('miccss_settings', 'miccss_defer_handles');

        // Add settings sections
        add_settings_section(
            'miccss_main_section',
            __('Critical CSS Settings', 'miccss'),
            array($this, 'settings_section_callback'),
            'miccss_settings'
        );

        // Add settings fields
        add_settings_field(
            'miccss_enabled',
            __('Enable MICCSS', 'miccss'),
            array($this, 'enabled_field_callback'),
            'miccss_settings',
            'miccss_main_section'
        );

        add_settings_field(
            'miccss_critical_css',
            __('Critical CSS', 'miccss'),
            array($this, 'critical_css_field_callback'),
            'miccss_settings',
            'miccss_main_section'
        );

        add_settings_field(
            'miccss_exclude_handles',
            __('Exclude Handles', 'miccss'),
            array($this, 'exclude_handles_field_callback'),
            'miccss_settings',
            'miccss_main_section'
        );

        add_settings_field(
            'miccss_defer_handles',
            __('Defer Handles', 'miccss'),
            array($this, 'defer_handles_field_callback'),
            'miccss_settings',
            'miccss_main_section'
        );
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback()
    {
        echo '<p>' . __('Configure your Critical CSS settings below.', 'miccss') . '</p>';
    }

    /**
     * Enabled field callback
     */
    public function enabled_field_callback()
    {
        $enabled = get_option('miccss_enabled', true);
        echo '<input type="checkbox" id="miccss_enabled" name="miccss_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="miccss_enabled">' . __('Enable Critical CSS optimization', 'miccss') . '</label>';
    }

    /**
     * Critical CSS field callback
     */
    public function critical_css_field_callback()
    {
        $critical_css = get_option('miccss_critical_css', '');
        echo '<textarea id="miccss_critical_css" name="miccss_critical_css" rows="20" cols="80" class="large-text code">' . esc_textarea($critical_css) . '</textarea>';
        echo '<p class="description">' . __('Paste your critical CSS here. This will be inlined in the document head.', 'miccss') . '</p>';
    }

    /**
     * Exclude handles field callback
     */
    public function exclude_handles_field_callback()
    {
        $exclude_handles = get_option('miccss_exclude_handles', array());
        $exclude_handles_string = is_array($exclude_handles) ? implode(', ', $exclude_handles) : '';
        echo '<input type="text" id="miccss_exclude_handles" name="miccss_exclude_handles" value="' . esc_attr($exclude_handles_string) . '" class="regular-text" />';
        echo '<p class="description">' . __('Comma-separated list of style handles to exclude from deferring (e.g., admin-bar, dashicons)', 'miccss') . '</p>';
    }

    /**
     * Defer handles field callback
     */
    public function defer_handles_field_callback()
    {
        $defer_handles = get_option('miccss_defer_handles', array('main-style'));
        $defer_handles_string = is_array($defer_handles) ? implode(', ', $defer_handles) : '';
        echo '<input type="text" id="miccss_defer_handles" name="miccss_defer_handles" value="' . esc_attr($defer_handles_string) . '" class="regular-text" />';
        echo '<p class="description">' . __('Comma-separated list of style handles to defer (default: main-style)', 'miccss') . '</p>';
    }

    /**
     * Admin page content
     */
    public function admin_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('MICCSS - Manual Inline Critical CSS', 'miccss'); ?></h1>
            <p><?php _e('Improve your site\'s performance by inlining critical CSS and deferring non-critical stylesheets.', 'miccss'); ?>
            </p>

            <div class="miccss-info">
                <h2><?php _e('How it works:', 'miccss'); ?></h2>
                <ol>
                    <li><?php _e('Critical CSS is inlined in the document head for immediate rendering', 'miccss'); ?></li>
                    <li><?php _e('Non-critical CSS is loaded asynchronously using preload with fallback', 'miccss'); ?></li>
                    <li><?php _e('Noscript fallback ensures CSS loads even without JavaScript', 'miccss'); ?></li>
                </ol>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('miccss_settings');
                do_settings_sections('miccss_settings');
                submit_button();
                ?>
            </form>

            <div class="miccss-tools">
                <h2><?php _e('Tools & Resources', 'miccss'); ?></h2>
                <p><?php _e('To generate critical CSS, you can use these online tools:', 'miccss'); ?></p>
                <ul>
                    <li><a href="https://www.criticalcss.com/" target="_blank">Critical CSS Generator</a></li>
                    <li><a href="https://jonassebastianohlsson.com/criticalpathcssgenerator/" target="_blank">Critical Path CSS
                            Generator</a></li>
                    <li><a href="https://web.dev/extract-critical-css/" target="_blank">Extract Critical CSS - Web.dev</a></li>
                </ul>
            </div>
        </div>

        <style>
            .miccss-info {
                background: #f9f9f9;
                border-left: 4px solid #0073aa;
                padding: 15px;
                margin: 20px 0;
            }

            .miccss-tools {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
            }

            .miccss-tools ul {
                list-style-type: disc;
                margin-left: 20px;
            }
        </style>
        <?php
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
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Clean up if needed
    }
}

// Initialize the plugin
new MICCSS_Plugin();

/**
 * Helper functions
 */

/**
 * Get critical CSS
 */
function miccss_get_critical_css()
{
    return get_option('miccss_critical_css', '');
}

/**
 * Check if MICCSS is enabled
 */
function miccss_is_enabled()
{
    return get_option('miccss_enabled', true);
}

/**
 * Manually add critical CSS (for theme developers)
 */
function miccss_add_critical_css($css)
{
    if (miccss_is_enabled()) {
        echo "<style id='miccss-manual'>\n/* Manual Critical CSS */\n" . wp_strip_all_tags($css) . "\n</style>\n";
    }
}