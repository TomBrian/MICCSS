# MICCSS Plugin Files

## Main Plugin File

**Use this file as the main plugin file:**

- `miccss-improved.php` → Rename to `miccss.php` for installation

## Installation Instructions

1. **Rename the main file**:

   - Rename `miccss-improved.php` to `miccss.php`

2. **Upload to WordPress**:

   - Upload entire folder to `/wp-content/plugins/miccss/`
   - Or zip the folder and upload via WordPress admin

3. **File Structure for Installation**:

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

4. **Activate**: Go to WordPress Admin → Plugins → Activate "MICCSS"

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
