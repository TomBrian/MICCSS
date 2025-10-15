# MICCSS Plugin Files - DUPLICATION FIX

## ⚠️ PLUGIN DUPLICATION ISSUE SOLVED

**Problem**: Having both `miccss.php` and `miccss-improved.php` with plugin headers causes WordPress to show duplicate plugins.

**Solution**: Choose ONE of these options:

### Option 1: Use Original Version (Simple)

- Keep `miccss.php` as is
- The `miccss-improved.php` header has been disabled (no longer shows as duplicate)

### Option 2: Use Enhanced Version (Recommended)

1. **Delete** `miccss.php`
2. **Rename** `miccss-improved.php` to `miccss.php`
3. **Restore the plugin header** in the renamed file (see instructions below)

## Installation Instructions

### For Enhanced Version (Recommended):

1. **Delete the original file**:

   - Delete `miccss.php`

2. **Rename enhanced file**:

   - Rename `miccss-improved.php` to `miccss.php`

3. **Restore plugin header** (replace the comment block at the top):

   ```php
   <?php
   /**
    * Plugin Name: MICCSS - Manual Inline Critical CSS
    * Plugin URI: https://github.com/TomBrian/miccss
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
    * Network: true
    *
    * @package MICCSS
    * @author Thomas Kamau
    * @since 1.0.0
    */
   ```

4. **Upload to WordPress**:

5. **File Structure for Installation**:

   ```
   /wp-content/plugins/miccss/
   ├── miccss.php              # Renamed from miccss-improved.php
   ├── uninstall.php
   ├── README.md
   ├── CHANGELOG.md
   ├── INSTALL.md
   ├── assets/
   │   ├── css/
   │   │   └── admin.css
   │   └── js/
   │       └── admin.js
   └── templates/
       └── admin-page.php
   ```

6. **Activate**: Go to WordPress Admin → Plugins → Activate "MICCSS"

## File Descriptions

- **miccss-improved.php**: Enhanced main plugin file (rename to miccss.php)
- **miccss.php**: Original plugin file (can be replaced)
- **templates/admin-page.php**: Admin interface template
- **assets/css/admin.css**: Admin styling
- **assets/js/admin.js**: Admin JavaScript functionality
- **uninstall.php**: Clean database removal on uninstall
- **README.md**: Complete documentation
- **INSTALL.md**: Detailed installation guide
- **CHANGELOG.md**: Version history

## Key Features in Improved Version

- Singleton pattern for better memory management
- Enhanced error handling and validation
- AJAX CSS validation
- Improved security with proper sanitization
- Caching system for better performance
- Minification option for critical CSS
- Better WordPress coding standards compliance
- Comprehensive admin interface
- Plugin action links
- Multisite support

---

**Author**: Thomas Kamau  
**Plugin**: MICCSS - Manual Inline Critical CSS  
**Version**: 1.0.0
