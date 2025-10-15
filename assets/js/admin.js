/**
 * MICCSS Admin JavaScript
 * 
 * @package MICCSS
 * @author Thomas Kamau
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function () {
        MICCSS_Admin.init();
    });

    var MICCSS_Admin = {

        /**
         * Initialize admin functionality
         */
        init: function () {
            this.bindEvents();
            this.initTextareaFeatures();
            this.validateCriticalCSS();
            this.updateStats();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            // Enable/disable toggle
            $('#miccss_enabled').on('change', this.handleEnableToggle);

            // Critical CSS validation
            $('#miccss_critical_css').on('input', this.validateCriticalCSS);

            // Handle array inputs
            $('#miccss_exclude_handles, #miccss_defer_handles').on('blur', this.handleArrayInput);

            // Test CSS button
            $('.miccss-test-css').on('click', this.testCriticalCSS);

            // Clear CSS button
            $('.miccss-clear-css').on('click', this.clearCriticalCSS);

            // Import CSS button
            $('.miccss-import-css').on('click', this.importCSS);

            // Save settings
            $('#miccss-settings-form').on('submit', this.saveSettings);
        },

        /**
         * Handle enable/disable toggle
         */
        handleEnableToggle: function () {
            var isEnabled = $(this).is(':checked');
            var $form = $('.miccss-form-table');

            if (isEnabled) {
                $form.removeClass('miccss-disabled');
                $('.miccss-warning.disabled').hide();
            } else {
                $form.addClass('miccss-disabled');
                $('.miccss-warning.disabled').show();
            }
        },

        /**
         * Initialize textarea features
         */
        initTextareaFeatures: function () {
            var $textarea = $('#miccss_critical_css');

            if ($textarea.length) {
                // Add line numbers
                this.addLineNumbers($textarea);

                // Add syntax highlighting (basic)
                this.addSyntaxHighlighting($textarea);

                // Add resize handler
                $textarea.on('input', function () {
                    MICCSS_Admin.updateStats();
                });
            }
        },

        /**
         * Add line numbers to textarea
         */
        addLineNumbers: function ($textarea) {
            var $wrapper = $('<div class="miccss-textarea-wrapper"></div>');
            var $lineNumbers = $('<div class="miccss-line-numbers"></div>');

            $textarea.wrap($wrapper);
            $textarea.before($lineNumbers);

            this.updateLineNumbers($textarea, $lineNumbers);

            $textarea.on('scroll input', function () {
                MICCSS_Admin.updateLineNumbers($textarea, $lineNumbers);
            });
        },

        /**
         * Update line numbers
         */
        updateLineNumbers: function ($textarea, $lineNumbers) {
            var lines = $textarea.val().split('\n').length;
            var lineNumbersHtml = '';

            for (var i = 1; i <= lines; i++) {
                lineNumbersHtml += '<div>' + i + '</div>';
            }

            $lineNumbers.html(lineNumbersHtml);
            $lineNumbers.scrollTop($textarea.scrollTop());
        },

        /**
         * Basic syntax highlighting
         */
        addSyntaxHighlighting: function ($textarea) {
            // This is a simplified version - in production you might want to use a library like CodeMirror
            $textarea.addClass('miccss-syntax-highlighted');
        },

        /**
         * Validate critical CSS
         */
        validateCriticalCSS: function () {
            var $textarea = $('#miccss_critical_css');
            var css = $textarea.val();
            var $feedback = $('.miccss-css-feedback');

            if (!$feedback.length) {
                $feedback = $('<div class="miccss-css-feedback"></div>');
                $textarea.after($feedback);
            }

            if (css.trim() === '') {
                $feedback.html('<div class="miccss-warning">No critical CSS provided.</div>');
                return;
            }

            // Basic CSS validation
            var errors = MICCSS_Admin.validateCSSSyntax(css);
            var size = new Blob([css]).size;
            var sizeKB = (size / 1024).toFixed(2);

            var feedbackHtml = '<div class="miccss-css-stats">';
            feedbackHtml += '<span class="miccss-stat">Size: ' + sizeKB + ' KB</span>';
            feedbackHtml += '<span class="miccss-stat">Lines: ' + css.split('\n').length + '</span>';

            if (size > 14336) { // 14KB
                feedbackHtml += '<span class="miccss-warning">⚠️ CSS is larger than recommended 14KB</span>';
            } else {
                feedbackHtml += '<span class="miccss-success">✅ Good size</span>';
            }

            if (errors.length > 0) {
                feedbackHtml += '<div class="miccss-errors">';
                feedbackHtml += '<strong>Syntax Issues:</strong><ul>';
                errors.forEach(function (error) {
                    feedbackHtml += '<li>' + error + '</li>';
                });
                feedbackHtml += '</ul></div>';
            }

            feedbackHtml += '</div>';
            $feedback.html(feedbackHtml);
        },

        /**
         * Basic CSS syntax validation
         */
        validateCSSSyntax: function (css) {
            var errors = [];
            var openBraces = (css.match(/\{/g) || []).length;
            var closeBraces = (css.match(/\}/g) || []).length;

            if (openBraces !== closeBraces) {
                errors.push('Mismatched braces: ' + openBraces + ' opening, ' + closeBraces + ' closing');
            }

            // Check for common issues
            if (css.includes('undefined')) {
                errors.push('Contains "undefined" - check for JavaScript variables');
            }

            if (css.includes('null')) {
                errors.push('Contains "null" - check for JavaScript variables');
            }

            return errors;
        },

        /**
         * Handle array inputs (comma-separated values)
         */
        handleArrayInput: function () {
            var $input = $(this);
            var value = $input.val();

            if (value) {
                // Clean up the input
                var items = value.split(',').map(function (item) {
                    return item.trim();
                }).filter(function (item) {
                    return item !== '';
                });

                $input.val(items.join(', '));
            }
        },

        /**
         * Test critical CSS
         */
        testCriticalCSS: function (e) {
            e.preventDefault();

            var $button = $(this);
            var originalText = $button.text();
            var css = $('#miccss_critical_css').val();

            if (!css.trim()) {
                alert('Please enter some critical CSS first.');
                return;
            }

            $button.html('<span class="miccss-loading"></span>Testing...').prop('disabled', true);

            // Create a test preview
            MICCSS_Admin.createCSSPreview(css);

            setTimeout(function () {
                $button.text(originalText).prop('disabled', false);
            }, 2000);
        },

        /**
         * Create CSS preview
         */
        createCSSPreview: function (css) {
            var $preview = $('#miccss-css-preview');

            if (!$preview.length) {
                $preview = $('<div id="miccss-css-preview" class="miccss-css-preview"></div>');
                $('#miccss_critical_css').after($preview);
            }

            // Simple syntax highlighting
            var highlightedCSS = this.highlightCSS(css);
            $preview.html(highlightedCSS).show();
        },

        /**
         * Simple CSS syntax highlighting
         */
        highlightCSS: function (css) {
            return css
                .replace(/([.#][a-zA-Z0-9_-]+)/g, '<span class="css-selector">$1</span>')
                .replace(/([a-zA-Z-]+)(\s*):/g, '<span class="css-property">$1</span>$2:')
                .replace(/:(\s*)([^;]+);/g, ':<span class="css-value">$2</span>;')
                .replace(/(\/\*[^*]*\*\/)/g, '<span class="css-comment">$1</span>')
                .replace(/\n/g, '<br>');
        },

        /**
         * Clear critical CSS
         */
        clearCriticalCSS: function (e) {
            e.preventDefault();

            if (confirm('Are you sure you want to clear all critical CSS? This action cannot be undone.')) {
                $('#miccss_critical_css').val('').trigger('input');
                $('#miccss-css-preview').hide();
            }
        },

        /**
         * Import CSS from file
         */
        importCSS: function (e) {
            e.preventDefault();

            var $input = $('<input type="file" accept=".css" style="display: none;">');
            $('body').append($input);

            $input.on('change', function (e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#miccss_critical_css').val(e.target.result).trigger('input');
                    };
                    reader.readAsText(file);
                }
                $input.remove();
            });

            $input.click();
        },

        /**
         * Save settings with validation
         */
        saveSettings: function (e) {
            var css = $('#miccss_critical_css').val();
            var errors = MICCSS_Admin.validateCSSSyntax(css);

            if (errors.length > 0) {
                if (!confirm('There are CSS syntax issues. Do you want to save anyway?\n\n' + errors.join('\n'))) {
                    e.preventDefault();
                    return false;
                }
            }

            // Show saving indicator
            var $submit = $('#submit');
            var originalText = $submit.val();
            $submit.val('Saving...').prop('disabled', true);

            // Re-enable after form submission
            setTimeout(function () {
                $submit.val(originalText).prop('disabled', false);
            }, 3000);
        },

        /**
         * Update statistics
         */
        updateStats: function () {
            var css = $('#miccss_critical_css').val();
            var size = new Blob([css]).size;
            var lines = css.split('\n').length;
            var rules = (css.match(/\{[^}]*\}/g) || []).length;

            $('.miccss-stat-size .miccss-stat-number').text((size / 1024).toFixed(2) + ' KB');
            $('.miccss-stat-lines .miccss-stat-number').text(lines);
            $('.miccss-stat-rules .miccss-stat-number').text(rules);

            // Update size indicator
            var $sizeCard = $('.miccss-stat-size');
            $sizeCard.removeClass('miccss-warning miccss-success');

            if (size > 14336) { // 14KB
                $sizeCard.addClass('miccss-warning');
            } else {
                $sizeCard.addClass('miccss-success');
            }
        }
    };

    // Helper functions for WordPress integration
    window.MICCSS_Admin = MICCSS_Admin;

})(jQuery);