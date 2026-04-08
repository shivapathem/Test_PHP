/**
 * Shared Select2 UI Module
 *
 * Provides reusable UI fixes for any Select2 instance across the application.
 * Import this module wherever Select2 is initialised and call applyUiFixes().
 *
 * Usage:
 *   var Select2Ui = require('./select2-ui.module');
 *   this._select2Ui = new Select2Ui();
 *   $('#my-select').select2({ width: '200px' });
 *   this._select2Ui.applyUiFixes('#my-select', { width: '200px' });
 */

var Select2Ui = function (options) {
    this.options = options || {};
    this._ensureStyles();
};

Select2Ui.prototype = {
    /**
     * Inject shared CSS once.
     */
    _ensureStyles: function () {
        if (document.getElementById('a7-select2-ui-styles')) return;

        var style = document.createElement('style');
        style.id = 'a7-select2-ui-styles';
        style.type = 'text/css';
        style.textContent = [
            '.a7-select2.select2-container { margin: 15px 0 0 0 !important; z-index: 90 !important; }',
            '.a7-select2 .select2-selection--single {',
            '  height: var(--a7-select2-height, 25px) !important;',
            '  border: var(--a7-select2-border-width, 2px) solid var(--a7-select2-border-color, #c0c0c0) !important;',
            '  border-radius: 4px !important;',
            '  padding: 0 !important;',
            '  margin: 0 !important;',
            '  font-family: var(--a7-select2-font-family, inherit) !important;',
            '  font-size: var(--a7-select2-font-size, 12px) !important;',
            '}',
            '.a7-select2 .select2-selection--single .select2-selection__rendered {',
            '  line-height: calc(var(--a7-select2-height, 25px) - 4px) !important;',
            '  padding-left: var(--a7-select2-padding-x, 13px) !important;',
            '  padding-right: var(--a7-select2-padding-right, 22px) !important;',
            '  color: #000;',
            '  margin: 0 !important;',
            '}',
            '.a7-select2 .select2-selection--single .select2-selection__arrow {',
            '  height: calc(var(--a7-select2-height, 25px) - 2px) !important;',
            '  margin: 0 !important;',
            '}',
            '.a7-select2-open .select2-selection--single {',
            '  border-bottom-left-radius: 0 !important;',
            '  border-bottom-right-radius: 0 !important;',
            '}',
            '.a7-select2-dropdown.select2-dropdown {',
            '  margin-top: 0 !important;',
            '  border-top-left-radius: 0 !important;',
            '  border-top-right-radius: 0 !important;',
            '  font-family: var(--a7-select2-font-family, inherit) !important;',
            '  font-size: var(--a7-select2-font-size, 12px) !important;',
            '  z-index: 90 !important;',
            '}',
            '.a7-select2-dropdown.select2-dropdown.select2-dropdown--below {',
            '  margin-top: calc(-1 * var(--a7-select2-border-width, 2px)) !important;',
            '  border-top: 0 !important;',
            '}',
            '.a7-select2-dropdown.select2-dropdown.select2-dropdown--above {',
            '  margin-bottom: calc(-1 * var(--a7-select2-border-width, 2px)) !important;',
            '  border-bottom: 0 !important;',
            '}',
            '.a7-select2-dropdown .select2-results__option {',
            '  padding: 4px 8px !important;',
            '}'
        ].join('\n');

        document.head.appendChild(style);
    },

    /**
     * Apply UI fixes to one or more Select2 elements.
     *
     * @param {string|HTMLElement|jQuery} selector
     * @param {Object} options
     */
    applyUiFixes: function (selector, options) {
        var opts = $.extend({
            width: null,
            height: '25px',
            fontFamily: 'inherit',
            fontSize: '12px',
            paddingX: '13px',
            paddingRight: '22px',
            borderColor: '#c0c0c0',
            borderWidth: '2px'
        }, this.options || {}, options || {});

        var $selects = $(selector || []);
        if (!$selects.length) return;

        $selects.each(function () {
            var $select = $(this);
            if (!$select.data('select2')) return;
            if ($select.closest('.dataTables_wrapper, table.dataTable').length) {
                return;
            }

            var $container = $select.next('.select2');
            if (!$container.length) return;

            $container
                .addClass('a7-select2')
                .css('--a7-select2-height', opts.height)
                .css('--a7-select2-font-family', opts.fontFamily)
                .css('--a7-select2-font-size', opts.fontSize)
                .css('--a7-select2-padding-x', opts.paddingX)
                .css('--a7-select2-padding-right', opts.paddingRight)
                .css('--a7-select2-border-color', opts.borderColor)
                .css('--a7-select2-border-width', opts.borderWidth);

            if (opts.width) {
                $container.css('width', opts.width);
            }

            $select.off('select2:open.a7ui select2:close.a7ui');
            $select.on('select2:open.a7ui', function () {
                var $dropdown = $('.select2-dropdown').last();
                $dropdown.addClass('a7-select2-dropdown');
                $container.addClass('a7-select2-open');
                $dropdown
                    .css('--a7-select2-font-family', opts.fontFamily)
                    .css('--a7-select2-font-size', opts.fontSize)
                    .css('--a7-select2-border-width', opts.borderWidth);
                
                // Remove dropdown gap by adjusting top position
                setTimeout(function() {
                    $dropdown.css('top', -16 + 'px');
                }, 0);
            });
            $select.on('select2:close.a7ui', function () {
                $container.removeClass('a7-select2-open');
            });
        });
    }
};

module.exports = Select2Ui;
