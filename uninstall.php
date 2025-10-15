<?php
/**
 * MICCSS Uninstall Script
 * 
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data from the database.
 * 
 * @package MICCSS
 * @author Thomas Kamau
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove all plugin options from the database
 */
function miccss_uninstall_cleanup()
{
    // Delete plugin options
    delete_option('miccss_enabled');
    delete_option('miccss_critical_css');
    delete_option('miccss_exclude_handles');
    delete_option('miccss_defer_handles');
    delete_option('miccss_version');
    delete_option('miccss_settings');

    // Delete any transients
    delete_transient('miccss_css_cache');
    delete_transient('miccss_performance_data');

    // For multisite installations
    if (is_multisite()) {
        global $wpdb;

        // Get all blog IDs
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);

            // Delete options for each site
            delete_option('miccss_enabled');
            delete_option('miccss_critical_css');
            delete_option('miccss_exclude_handles');
            delete_option('miccss_defer_handles');
            delete_option('miccss_version');
            delete_option('miccss_settings');

            // Delete transients for each site
            delete_transient('miccss_css_cache');
            delete_transient('miccss_performance_data');

            restore_current_blog();
        }
    }

    // Clear any cached data
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}

// Execute cleanup
miccss_uninstall_cleanup();

// Log uninstall event (optional)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('MICCSS Plugin: Uninstalled and cleaned up successfully');
}