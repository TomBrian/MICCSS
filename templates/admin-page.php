<?php
/**
 * MICCSS Admin Page Template
 * 
 * @package MICCSS
 * @author Thomas Kamau
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

$options = array(
    'enabled' => get_option('miccss_enabled', true),
    'critical_css' => get_option('miccss_critical_css', ''),
    'exclude_handles' => get_option('miccss_exclude_handles', array('admin-bar', 'dashicons')),
    'defer_handles' => get_option('miccss_defer_handles', array('main-style')),
    'minify_css' => get_option('miccss_minify_css', false),
    'cache_css' => get_option('miccss_cache_css', true)
);

// Convert arrays to comma-separated strings for display
$exclude_handles_string = is_array($options['exclude_handles']) ? implode(', ', $options['exclude_handles']) : '';
$defer_handles_string = is_array($options['defer_handles']) ? implode(', ', $options['defer_handles']) : '';

// Get CSS statistics
$css_stats = array();
if (!empty($options['critical_css'])) {
    $size_bytes = strlen($options['critical_css']);
    $css_stats = array(
        'size_kb' => round($size_bytes / 1024, 2),
        'lines' => substr_count($options['critical_css'], "\n") + 1,
        'rules' => substr_count($options['critical_css'], '{'),
        'size_status' => $size_bytes > 14336 ? 'warning' : 'good'
    );
}
?>

<div class="wrap miccss-wrap">
    <div class="miccss-header">
        <h1><?php _e('MICCSS - Manual Inline Critical CSS', 'miccss'); ?></h1>
        <p><?php _e('Improve your site\'s performance by inlining critical CSS and deferring non-critical stylesheets using the proven preload method.', 'miccss'); ?></p>
    </div>

    <?php settings_errors('miccss_settings'); ?>

    <!-- Performance Stats -->
    <?php if (!empty($css_stats)): ?>
    <div class="miccss-stats">
        <div class="miccss-stat-card miccss-stat-size <?php echo $css_stats['size_status'] === 'warning' ? 'miccss-warning' : 'miccss-success'; ?>">
            <span class="miccss-stat-number"><?php echo $css_stats['size_kb']; ?> KB</span>
            <span class="miccss-stat-label"><?php _e('CSS Size', 'miccss'); ?></span>
        </div>
        <div class="miccss-stat-card miccss-stat-lines">
            <span class="miccss-stat-number"><?php echo $css_stats['lines']; ?></span>
            <span class="miccss-stat-label"><?php _e('Lines', 'miccss'); ?></span>
        </div>
        <div class="miccss-stat-card miccss-stat-rules">
            <span class="miccss-stat-number"><?php echo $css_stats['rules']; ?></span>
            <span class="miccss-stat-label"><?php _e('CSS Rules', 'miccss'); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- How it Works Info -->
    <div class="miccss-info">
        <h2><?php _e('How the Defer Method Works:', 'miccss'); ?></h2>
        <ol>
            <li><?php _e('Critical CSS is inlined in the document head for immediate rendering of above-the-fold content', 'miccss'); ?></li>
            <li><?php _e('Non-critical stylesheets are converted from rel="stylesheet" to rel="preload" as="style"', 'miccss'); ?></li>
            <li><?php _e('JavaScript onload event converts preload back to stylesheet after loading', 'miccss'); ?></li>
            <li><?php _e('Noscript fallback ensures CSS loads even without JavaScript support', 'miccss'); ?></li>
        </ol>
        <p><strong><?php _e('The exact method used:', 'miccss'); ?></strong></p>
        <div class="miccss-css-preview">
            <code>
function defer_non_critical_css() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;wp_enqueue_style('main-style', get_stylesheet_uri(), [], null);<br>
&nbsp;&nbsp;&nbsp;&nbsp;add_filter('style_loader_tag', function($html, $handle) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ('main-style' === $handle) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.rel='stylesheet'\"", $html);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return $html;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}, 10, 2);<br>
}<br>
add_action('wp_enqueue_scripts', 'defer_non_critical_css', 20);
            </code>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form method="post" action="" id="miccss-settings-form">
        <?php wp_nonce_field('miccss_settings', 'miccss_nonce'); ?>
        
        <table class="form-table miccss-form-table">
            <tbody>
                <!-- Enable/Disable Plugin -->
                <tr>
                    <th scope="row">
                        <label for="miccss_enabled"><?php _e('Enable MICCSS', 'miccss'); ?></label>
                    </th>
                    <td>
                        <div class="miccss-toggle">
                            <input type="checkbox" id="miccss_enabled" name="miccss_enabled" value="1" <?php checked(1, $options['enabled']); ?> />
                            <span class="miccss-toggle-slider"></span>
                        </div>
                        <label for="miccss_enabled"><?php _e('Enable Critical CSS optimization', 'miccss'); ?></label>
                        <p class="description"><?php _e('Toggle the entire plugin functionality on or off.', 'miccss'); ?></p>
                    </td>
                </tr>

                <!-- Critical CSS -->
                <tr>
                    <th scope="row">
                        <label for="miccss_critical_css"><?php _e('Critical CSS', 'miccss'); ?></label>
                    </th>
                    <td>
                        <div class="miccss-textarea-container">
                            <textarea id="miccss_critical_css" name="miccss_critical_css" class="miccss-textarea" rows="20" placeholder="<?php _e('Paste your critical CSS here...', 'miccss'); ?>"><?php echo esc_textarea($options['critical_css']); ?></textarea>
                            <div class="miccss-textarea-actions">
                                <button type="button" class="miccss-button miccss-button-secondary miccss-test-css"><?php _e('Test CSS', 'miccss'); ?></button>
                                <button type="button" class="miccss-button miccss-button-secondary miccss-import-css"><?php _e('Import from File', 'miccss'); ?></button>
                                <button type="button" class="miccss-button miccss-button-secondary miccss-clear-css"><?php _e('Clear', 'miccss'); ?></button>
                            </div>
                        </div>
                        <p class="description">
                            <?php _e('Paste your critical CSS here. This will be inlined in the document head for immediate rendering. Aim for under 14KB for optimal performance.', 'miccss'); ?>
                        </p>
                        <div class="miccss-css-feedback"></div>
                    </td>
                </tr>

                <!-- Exclude Handles -->
                <tr>
                    <th scope="row">
                        <label for="miccss_exclude_handles"><?php _e('Exclude Handles', 'miccss'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="miccss_exclude_handles" name="miccss_exclude_handles" value="<?php echo esc_attr($exclude_handles_string); ?>" class="regular-text" />
                        <p class="description">
                            <?php _e('Comma-separated list of style handles to exclude from deferring. Default excludes admin-bar and dashicons for proper admin functionality.', 'miccss'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Defer Handles -->
                <tr>
                    <th scope="row">
                        <label for="miccss_defer_handles"><?php _e('Defer Handles', 'miccss'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="miccss_defer_handles" name="miccss_defer_handles" value="<?php echo esc_attr($defer_handles_string); ?>" class="regular-text" />
                        <p class="description">
                            <?php _e('Comma-separated list of style handles to defer using the preload method. By default, main-style (your theme\'s main stylesheet) is deferred.', 'miccss'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Minify CSS -->
                <tr>
                    <th scope="row">
                        <label for="miccss_minify_css"><?php _e('Minify Critical CSS', 'miccss'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="miccss_minify_css" name="miccss_minify_css" value="1" <?php checked(1, $options['minify_css']); ?> />
                        <label for="miccss_minify_css"><?php _e('Automatically minify critical CSS output', 'miccss'); ?></label>
                        <p class="description"><?php _e('Removes comments and unnecessary whitespace to reduce CSS size.', 'miccss'); ?></p>
                    </td>
                </tr>

                <!-- Cache CSS -->
                <tr>
                    <th scope="row">
                        <label for="miccss_cache_css"><?php _e('Cache Critical CSS', 'miccss'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="miccss_cache_css" name="miccss_cache_css" value="1" <?php checked(1, $options['cache_css']); ?> />
                        <label for="miccss_cache_css"><?php _e('Cache processed critical CSS for better performance', 'miccss'); ?></label>
                        <p class="description"><?php _e('Caches the critical CSS to reduce processing time on each page load.', 'miccss'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(__('Save Settings', 'miccss'), 'primary', 'submit', true, array('id' => 'miccss-save-button')); ?>
    </form>

    <!-- Tools & Resources -->
    <div class="miccss-tools">
        <h2><?php _e('Tools & Resources', 'miccss'); ?></h2>
        <p><?php _e('Use these tools to generate critical CSS for your website:', 'miccss'); ?></p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
            <div>
                <h3><?php _e('Online Generators', 'miccss'); ?></h3>
                <ul>
                    <li><a href="https://www.criticalcss.com/" target="_blank"><?php _e('Critical CSS Generator', 'miccss'); ?></a></li>
                    <li><a href="https://jonassebastianohlsson.com/criticalpathcssgenerator/" target="_blank"><?php _e('Critical Path CSS Generator', 'miccss'); ?></a></li>
                    <li><a href="https://web.dev/extract-critical-css/" target="_blank"><?php _e('Extract Critical CSS - Web.dev', 'miccss'); ?></a></li>
                </ul>
            </div>
            
            <div>
                <h3><?php _e('Performance Testing', 'miccss'); ?></h3>
                <ul>
                    <li><a href="https://pagespeed.web.dev/" target="_blank"><?php _e('PageSpeed Insights', 'miccss'); ?></a></li>
                    <li><a href="https://gtmetrix.com/" target="_blank"><?php _e('GTmetrix', 'miccss'); ?></a></li>
                    <li><a href="https://webpagetest.org/" target="_blank"><?php _e('WebPageTest', 'miccss'); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="miccss-success">
            <h3><?php _e('Quick Start Guide', 'miccss'); ?></h3>
            <ol>
                <li><?php _e('Visit one of the critical CSS generators above', 'miccss'); ?></li>
                <li><?php _e('Enter your website URL and generate critical CSS', 'miccss'); ?></li>
                <li><?php _e('Copy the generated CSS and paste it in the Critical CSS field above', 'miccss'); ?></li>
                <li><?php _e('Save settings and test your site\'s performance', 'miccss'); ?></li>
                <li><?php _e('Use PageSpeed Insights to verify the improvements', 'miccss'); ?></li>
            </ol>
        </div>
    </div>

    <!-- Plugin Info -->
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666;">
        <p>
            <?php printf(__('MICCSS v%s - Made with ❤️ by %s', 'miccss'), MICCSS_VERSION, '<strong>Thomas Kamau</strong>'); ?>
            | <a href="https://github.com/thomaskamau/miccss" target="_blank"><?php _e('GitHub', 'miccss'); ?></a>
            | <a href="https://thomaskamau.dev" target="_blank"><?php _e('Author Website', 'miccss'); ?></a>
        </p>
    </div>
</div>

<style>
.miccss-disabled {
    opacity: 0.5;
    pointer-events: none;
}

.miccss-textarea-container {
    position: relative;
}

.miccss-textarea-actions {
    margin-top: 10px;
}

.miccss-textarea-actions .miccss-button {
    margin-right: 10px;
}

.miccss-css-feedback {
    margin-top: 15px;
    padding: 10px;
    border-radius: 3px;
    background: #f9f9f9;
    border-left: 4px solid #0073aa;
}

.miccss-css-feedback .miccss-warning {
    color: #d63638;
}

.miccss-css-feedback .miccss-success {
    color: #00a32a;
}

.miccss-css-stats {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.miccss-css-stats .miccss-stat {
    padding: 5px 10px;
    background: #fff;
    border-radius: 3px;
    border: 1px solid #ddd;
    font-size: 12px;
}

#miccss-css-preview {
    margin-top: 15px;
    padding: 15px;
    background: #f4f4f4;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}
</style>