# Changelog

All notable changes to the MICCSS plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-10-15

### Added

- Initial release of MICCSS (Manual Inline Critical CSS) plugin
- Critical CSS inlining functionality in document head
- Non-critical CSS deferring using preload method with onload fallback
- WordPress admin interface for configuration
- Plugin settings page with the following options:
  - Enable/disable plugin functionality
  - Critical CSS textarea for manual input
  - Exclude handles configuration
  - Defer handles configuration
- Noscript fallback support for users without JavaScript
- Input validation for CSS syntax
- Performance statistics display (CSS size, lines, rules count)
- Array input handling for comma-separated values
- Multisite support
- Uninstall cleanup functionality
- CSS syntax highlighting in admin interface
- Line numbers for critical CSS textarea
- Real-time CSS validation and feedback
- Import CSS from file functionality
- Test CSS preview feature
- Responsive admin interface design
- Tools and resources section with external links
- Performance optimization following WordPress best practices
- Translation ready (text domain: 'miccss')
- GPL v2 license compatibility
- WordPress 5.0+ compatibility
- PHP 7.4+ requirement

### Security

- Proper input sanitization and validation
- Nonce verification for form submissions
- Capability checks for admin access
- XSS prevention measures
- Direct file access prevention

### Performance

- Optimized CSS delivery method
- Minimal impact on site performance
- Efficient admin interface with minimal JavaScript
- Cached CSS processing where applicable

### Developer Features

- Hook system for extensibility
- Helper functions for theme developers
- Clean, documented code following WordPress standards
- Modular architecture for maintainability

## [Unreleased]

### Planned Features

- Automatic critical CSS generation
- Multiple device critical CSS support
- Advanced CSS minification
- Performance monitoring dashboard
- Integration with popular page builders
- Bulk import/export settings
- CSS preprocessing support
- Advanced caching mechanisms
- REST API endpoints
- WP-CLI commands
- Gutenberg block for CSS management

### Known Issues

- None reported

---

**Author:** Thomas Kamau  
**Plugin URI:** https://github.com/thomaskamau/miccss  
**License:** GPL v2 or later
