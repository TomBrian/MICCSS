# MICCSS - Manual Inline Critical CSS

A WordPress plugin for manually inlining Critical CSS to improve page load performance. Defers non-critical CSS using preload with fallback.

**Author:** Thomas Kamau  
**Version:** 1.0.0  
**Requires:** WordPress 5.0+, PHP 7.4+

## Features

- 🚀 **Critical CSS Inlining**: Manually inline critical CSS in the document head
- ⚡ **Universal Async CSS**: ALL non-critical CSS loads asynchronously automatically
- 🎯 **Smart Skip Handles**: Critical styles (admin-bar, dashicons) load synchronously
- 🔧 **Enhanced Admin Interface**: Easy-to-use configuration with live validation
- 📱 **Universal Compatibility**: Works with all themes, plugins, and devices
- 🔍 **SEO Optimized**: Dramatically improves Core Web Vitals and page speed scores
- 🛡️ **Bulletproof Fallbacks**: Comprehensive noscript support for all browsers
- 🧠 **Memory Safe**: Prevents JavaScript memory leaks with proper cleanup
- 🎛️ **Granular Control**: Fine-tune which styles load sync vs async

## Installation

1. Download the plugin files
2. Upload the `miccss` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > MICCSS to configure the plugin

## How It Works

The plugin implements an **enhanced two-step CSS optimization strategy**:

1. **Critical CSS**: Inlined in the `<head>` section for immediate rendering of above-the-fold content
2. **ALL Non-Critical CSS**: Automatically loaded asynchronously using advanced preload method with comprehensive fallbacks

### Enhanced Async CSS Loading Method

```php
/**
 * Make ALL non-critical styles load asynchronously using "preload + onload"
 * and provide a <noscript> fallback.
 */
add_filter('style_loader_tag', function ($html, $handle, $href, $media) {
    // Don't touch admin, login, or the critical (above-the-fold) handles.
    if (is_admin() || is_login()) {
        return $html;
    }

    // Skip critical handles that should load synchronously
    $skip_handles = ['critical-css-handle', 'dashicons', 'admin-bar'];

    if (in_array($handle, $skip_handles, true)) {
        return $html;
    }

    // Default media
    $media = $media ?: 'all';

    // Build async tag: preload first, then switch to stylesheet on load
    $async  = "<link rel='preload' as='style' href='" . esc_url($href) . "' media='{$media}' ";
    $async .= "onload=\"this.onload=null;this.rel='stylesheet'\">";
    // Fallback for users with JS disabled and for older browsers
    $async .= "<noscript><link rel='stylesheet' href='" . esc_url($href) . "' media='{$media}'></noscript>";

    return $async;
}, 10, 4);
```

### 🆕 Key Improvements:

- **Universal Coverage**: Affects ALL CSS files automatically (not just specific handles)
- **Smart Skipping**: Preserves critical styles (admin-bar, dashicons, custom critical handles)
- **Enhanced Fallback**: Robust noscript support for JavaScript-disabled browsers
- **Better Performance**: Uses 4-parameter filter for more precise control
- **Memory Safety**: Prevents JavaScript memory leaks with `this.onload=null`

## Configuration

### Admin Settings

Navigate to **Settings > MICCSS** in your WordPress admin to configure:

- **Enable MICCSS**: Toggle the plugin on/off
- **Critical CSS**: Paste your critical CSS code with live validation
- **Skip Handles (Critical Styles)**: Style handles that should load synchronously
- **Additional Skip Handles**: Optional extra handles to preserve immediate loading
- **Minify Critical CSS**: Automatically compress inline CSS
- **Cache Critical CSS**: Cache processed CSS for better performance

### 🆕 Enhanced Functionality:

- **Automatic Detection**: All CSS files are processed automatically
- **Universal Coverage**: No need to specify individual handles to defer
- **Smart Defaults**: Critical WordPress styles are preserved automatically

### Generating Critical CSS

To generate critical CSS for your site, use these recommended tools:

1. [Critical CSS Generator](https://www.criticalcss.com/)
2. [Critical Path CSS Generator](https://jonassebastianohlsson.com/criticalpathcssgenerator/)
3. [Web.dev Extract Critical CSS](https://web.dev/extract-critical-css/)

### Steps to Generate Critical CSS:

1. Visit one of the tools above
2. Enter your website URL
3. Copy the generated critical CSS
4. Paste it into the MICCSS settings page
5. Save and test your site

## Usage Examples

### Basic Usage

1. Install and activate the plugin
2. Generate critical CSS for your homepage
3. Paste the CSS in Settings > MICCSS
4. Enable the plugin and save

### Advanced Usage

#### Exclude Specific Stylesheets

```php
// In your theme's functions.php
add_filter('miccss_exclude_handles', function($handles) {
    $handles[] = 'custom-critical-style';
    return $handles;
});
```

#### Manually Add Critical CSS

```php
// In your theme files
if (function_exists('miccss_add_critical_css')) {
    miccss_add_critical_css('
        body { margin: 0; }
        .header { background: #333; }
    ');
}
```

#### Check if MICCSS is Active

```php
if (function_exists('miccss_is_enabled') && miccss_is_enabled()) {
    // MICCSS is active
    echo 'Critical CSS optimization is enabled';
}
```

## Best Practices

1. **Test Critical CSS**: Always test your critical CSS on different devices and page types
2. **Keep It Small**: Critical CSS should be under 14KB for optimal performance
3. **Above-the-Fold Only**: Include only styles for content visible without scrolling
4. **Regular Updates**: Update critical CSS when making design changes
5. **Monitor Performance**: Use tools like PageSpeed Insights to measure improvements

## Performance Benefits

- **Faster First Contentful Paint (FCP)**
- **Improved Largest Contentful Paint (LCP)**
- **Better Core Web Vitals scores**
- **Reduced render-blocking resources**
- **Enhanced user experience**

## Browser Support

- Chrome 50+
- Firefox 85+
- Safari 10+
- Edge 79+
- Internet Explorer 11 (with fallback)

## Troubleshooting

### Critical CSS Not Showing

1. Check if the plugin is enabled in Settings > MICCSS
2. Verify critical CSS is properly pasted (no syntax errors)
3. Clear any caching plugins
4. Check browser developer tools for errors

### Styles Not Loading

1. Verify exclude handles are correct
2. Check for JavaScript errors in console
3. Ensure noscript fallback is present
4. Test with caching disabled

### Performance Issues

1. Reduce critical CSS size (aim for under 14KB)
2. Minimize critical CSS code
3. Remove unused critical CSS rules
4. Test on different devices

## Hooks and Filters

### Actions

- `miccss_before_critical_css` - Fires before critical CSS output
- `miccss_after_critical_css` - Fires after critical CSS output

### Filters

- `miccss_critical_css` - Filter critical CSS content
- `miccss_exclude_handles` - Filter excluded style handles
- `miccss_defer_handles` - Filter deferred style handles

## Contributing

Contributions are welcome! Please feel free to submit issues and pull requests.

## Changelog

### 1.0.0

- Initial release
- Critical CSS inlining
- Non-critical CSS deferring
- Admin interface
- Noscript fallback support

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and questions, please create an issue on the plugin repository or contact Thomas Kamau.

---

**Made with ❤️ by Thomas Kamau**
