# MICCSS Installation & Setup Guide

## Quick Installation

### Method 1: Manual Installation (Recommended)

1. **Download the Plugin**

   ```bash
   git clone https://github.com/thomaskamau/miccss.git
   ```

2. **Upload to WordPress**

   - Rename the main plugin file from `miccss-improved.php` to `miccss.php`
   - Upload the entire `miccss` folder to `/wp-content/plugins/`
   - Or zip the folder and upload via WordPress admin

3. **Activate**

   - Go to WordPress Admin → Plugins
   - Find "MICCSS - Manual Inline Critical CSS"
   - Click "Activate"

4. **Configure**
   - Go to Settings → MICCSS
   - Add your critical CSS
   - Save settings

### Method 2: WordPress Admin Upload

1. Rename `miccss-improved.php` to `miccss.php`
2. Zip the entire plugin folder
3. Go to WordPress Admin → Plugins → Add New → Upload Plugin
4. Choose the zip file and install
5. Activate the plugin

## File Structure

```
miccss/
├── miccss.php                 # Main plugin file (use miccss-improved.php)
├── uninstall.php             # Uninstall cleanup
├── README.md                 # Documentation
├── CHANGELOG.md              # Version history
├── assets/
│   ├── css/
│   │   └── admin.css         # Admin interface styles
│   └── js/
│       └── admin.js          # Admin interface JavaScript
└── templates/
    └── admin-page.php        # Admin page template
```

## Configuration Steps

### 1. Generate Critical CSS

Use any of these tools to generate critical CSS:

- **Critical CSS Generator**: https://www.criticalcss.com/
- **Critical Path CSS Generator**: https://jonassebastianohlsson.com/criticalpathcssgenerator/
- **Penthouse**: https://github.com/pocketjoso/penthouse

#### Using Critical CSS Generator:

1. Visit https://www.criticalcss.com/
2. Enter your website URL
3. Wait for processing
4. Copy the generated CSS
5. Paste into MICCSS settings

### 2. Plugin Settings

Navigate to **Settings → MICCSS** and configure:

#### Basic Settings

- **Enable MICCSS**: Toggle plugin on/off
- **Critical CSS**: Paste your generated critical CSS
- **Minify Critical CSS**: Automatically compress CSS
- **Cache Critical CSS**: Cache processed CSS for performance

#### Advanced Settings

- **Exclude Handles**: Style handles to exclude from deferring

  - Default: `admin-bar, dashicons`
  - Add more: `custom-style, plugin-style`

- **Defer Handles**: Style handles to defer
  - Default: `main-style`
  - Add theme styles: `theme-style, custom-theme`

### 3. Testing Your Setup

1. **Save Settings** in MICCSS admin panel
2. **Clear Cache** (if using caching plugins)
3. **Test Frontend** - view your site
4. **Check Performance**:
   - Use PageSpeed Insights
   - Verify CSS is inlined in `<head>`
   - Confirm stylesheets are preloaded

## The Defer Method Implementation

The plugin uses this exact method as requested:

```php
function defer_non_critical_css() {
    wp_enqueue_style('main-style', get_stylesheet_uri(), [], null);
    add_filter('style_loader_tag', function($html, $handle) {
        if ('main-style' === $handle) {
            $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.rel='stylesheet'\"", $html);
        }
        return $html;
    }, 10, 2);
}
add_action('wp_enqueue_scripts', 'defer_non_critical_css', 20);
```

### What This Does:

1. Enqueues the main stylesheet
2. Converts `rel='stylesheet'` to `rel='preload' as='style'`
3. Adds `onload="this.rel='stylesheet'"` for conversion after load
4. Includes noscript fallback for JavaScript-disabled browsers

## Troubleshooting

### Common Issues

#### 1. CSS Not Loading

**Problem**: Styles aren't applying to the page
**Solutions**:

- Check if plugin is enabled
- Verify critical CSS is properly formatted
- Clear browser cache and caching plugins
- Check browser console for JavaScript errors

#### 2. Performance Not Improving

**Problem**: PageSpeed scores aren't improving
**Solutions**:

- Ensure critical CSS covers above-the-fold content
- Keep critical CSS under 14KB
- Test with different critical CSS generators
- Verify non-critical CSS is being deferred

#### 3. Admin Area Issues

**Problem**: WordPress admin looks broken
**Solutions**:

- Check exclude handles include `admin-bar, dashicons`
- Add other admin styles to exclude list
- Disable plugin temporarily to isolate issue

#### 4. JavaScript Errors

**Problem**: Console shows JavaScript errors
**Solutions**:

- Ensure proper noscript fallbacks are present
- Check for conflicting plugins
- Verify theme compatibility

### Debug Mode

Enable WordPress debug mode to troubleshoot:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## Performance Optimization

### Best Practices

1. **Critical CSS Size**

   - Keep under 14KB for optimal performance
   - Focus on above-the-fold content only
   - Remove unused CSS rules

2. **Testing Strategy**

   - Test on different devices (mobile, tablet, desktop)
   - Use multiple page types (homepage, posts, pages)
   - Verify with real user monitoring tools

3. **Monitoring**
   - Regular PageSpeed Insights checks
   - Monitor Core Web Vitals
   - Track First Contentful Paint (FCP)
   - Watch Largest Contentful Paint (LCP)

### Performance Metrics to Track

- **First Contentful Paint (FCP)**: Should improve significantly
- **Largest Contentful Paint (LCP)**: Main benefit of critical CSS
- **Cumulative Layout Shift (CLS)**: Should remain stable
- **Time to Interactive (TTI)**: May improve slightly

## Integration with Other Tools

### Caching Plugins

Compatible with:

- WP Rocket
- W3 Total Cache
- WP Super Cache
- LiteSpeed Cache

**Setup**: Enable HTML minification in your caching plugin for additional benefits.

### Page Builders

Works with:

- Elementor
- Gutenberg
- Beaver Builder
- Divi

**Note**: Generate critical CSS after finalizing page builder designs.

### CDN Integration

Compatible with:

- Cloudflare
- MaxCDN
- Amazon CloudFront

**Setup**: Ensure CSS files are properly cached on CDN.

## Advanced Usage

### Theme Integration

Add to your theme's `functions.php`:

```php
// Add custom critical CSS
function my_theme_critical_css() {
    if (function_exists('miccss_add_critical_css')) {
        $custom_css = '
            .header { background: #333; }
            .hero { min-height: 100vh; }
        ';
        miccss_add_critical_css($custom_css, 'my-theme-critical');
    }
}
add_action('wp_head', 'my_theme_critical_css', 2);

// Conditional critical CSS
function conditional_critical_css() {
    if (is_front_page() && function_exists('miccss_add_critical_css')) {
        $homepage_css = '/* Homepage specific CSS */';
        miccss_add_critical_css($homepage_css, 'homepage-critical');
    }
}
add_action('wp_head', 'conditional_critical_css', 2);
```

### Programmatic Control

```php
// Check if MICCSS is active
if (function_exists('miccss_is_enabled') && miccss_is_enabled()) {
    // Your code here
}

// Get current critical CSS
$critical_css = miccss_get_critical_css();

// Check plugin version
$version = miccss_get_version();
```

## Security Considerations

1. **Input Sanitization**: All CSS input is sanitized to prevent XSS
2. **Capability Checks**: Only administrators can modify settings
3. **Nonce Verification**: All form submissions are verified
4. **Direct Access Prevention**: All files check for WordPress context

## Multisite Support

The plugin supports WordPress Multisite networks:

1. **Network Activation**: Activate for entire network or individual sites
2. **Per-Site Settings**: Each site can have unique critical CSS
3. **Bulk Management**: Use network admin for bulk operations

## Uninstallation

### Clean Removal

1. Deactivate plugin from WordPress admin
2. Delete plugin files
3. Database cleanup is automatic via `uninstall.php`

### Manual Cleanup (if needed)

```sql
DELETE FROM wp_options WHERE option_name LIKE 'miccss_%';
DELETE FROM wp_options WHERE option_name LIKE '_transient_miccss_%';
```

## Support & Updates

- **GitHub Issues**: https://github.com/thomaskamau/miccss/issues
- **Documentation**: Check README.md for latest info
- **Updates**: Follow semantic versioning (x.y.z)

---

**Author**: Thomas Kamau  
**Version**: 1.0.0  
**Last Updated**: October 15, 2025
