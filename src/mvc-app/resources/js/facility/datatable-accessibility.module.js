/**
 * Shared DataTable Accessibility Module
 *
 * Provides reusable accessibility fixes for any DataTable initialised
 * across the application.  Import this module wherever a DataTable is
 * created and call the relevant methods from initComplete / drawCallback.
 *
 * Usage:
 *   var DatatableAccessibility = require('./datatable-accessibility.module');
 *   // inside your constructor / init:
 *   this._a11y = new DatatableAccessibility();
 *   // inside initComplete / drawCallback:
 *   this._a11y.applyHeaderAccessibilityFixes(this.myTable);
 *   this._a11y.addAriaLabelsToFilterClearButtons('my-table-id', filterSetting);
 *   this._a11y.addAriaLabelledbyToYadcfTextInputs('my-table-id');
 */

/**
 * DatatableAccessibility
 *
 * Constructor – create one instance per module that owns a DataTable.
 */
var DatatableAccessibility = function () { };

function logTableName(methodName, tableId) {
    if (typeof console === 'undefined' || !console.log) return;
    var label = tableId || '(unknown)';
    console.log('DatatableAccessibility:', methodName, 'table:', label);
}

function escapeRegExp(text) {
    return String(text).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

DatatableAccessibility.prototype = {

    /**
     * Hide sort controls from assistive technology by default and reveal
     * them only when they receive keyboard focus (NVDA / VoiceOver fix).
     *
     * @param {DataTables.Api} dtInstance  An initialised DataTables API instance.
     */
    applyHeaderAccessibilityFixes: function (dtInstance) {
        if (!dtInstance) return;

        var tableId = '';
        try {
            var node = dtInstance.table && dtInstance.table().node && dtInstance.table().node();
            tableId = node && node.id ? node.id : '';
        } catch (e) { }
        logTableName('applyHeaderAccessibilityFixes', tableId);

        var container = dtInstance.table().container();
        var $container = $(container);

        var $sortControls = $container.find(
            'thead th .dt-column-order,' +
            'thead th button.dt-column-order,' +
            'thead th span[role="button"].dt-column-order,' +
            'thead th span[role="button"][aria-label*="sort"],' +
            'thead th span[role="button"][aria-label*="Activate to sort"]'
        );

        $sortControls.off('focusin.srfix focusout.srfix');
        $sortControls.attr('aria-hidden', 'true');

        $sortControls.each(function () {
            var $btn = $(this);
            // Keep native <button> elements alone; ensure other elements are tabbable.
            if (($btn.prop('tagName') || '').toLowerCase() !== 'button') {
                var tabindex = $btn.attr('tabindex');
                if (!tabindex || tabindex === '-1') { $btn.attr('tabindex', '0'); }
            }

            // Store the original aria-label for focus events
            var originalLabel = $btn.attr('aria-label');
            if (originalLabel && !$btn.attr('data-sr-label')) {
                $btn.attr('data-sr-label', originalLabel);
            }
        });

        $sortControls.on('focusin.srfix', function () {
            var $el = $(this);
            $el.removeAttr('aria-hidden');
            var label = $el.attr('data-sr-label');
            if (label) $el.attr('aria-label', label);
        });
        $sortControls.on('focusout.srfix', function () {
            var $el = $(this);
            $el.attr('aria-hidden', 'true');
            $el.removeAttr('aria-label');
        });
    },

    /**
     * Remove duplicate column names from sort button aria-labels for
     * columns that already expose their header text separately.
     *
     * @param {DataTables.Api} dtInstance  An initialised DataTables API instance.
     * @param {Array<number>} columns      Zero-based column indexes to normalise.
     */
    normalizeSortButtonLabels: function (dtInstance, columns) {
        if (!dtInstance || !columns || !columns.length) return;

        var colSet = {};
        columns.forEach(function (idx) { colSet[idx] = true; });

        var container = dtInstance.table().container();
        var $container = $(container);
        var $headerRow = $container.find('thead tr').first();
        if (!$headerRow.length) return;

        $headerRow.find('th').each(function (colIdx) {
            if (!colSet[colIdx]) return;

            var $th = $(this);
            var headerText = $th.clone()
                .find('input, select, button, .dt-column-order, span[role="button"], span[id$="-label"], .yadcf-filter-wrapper, .yadcf-filter, .yadcf-filter-reset-button')
                .remove()
                .end()
                .text()
                .replace(/\s+/g, ' ')
                .trim();

            if (!headerText) return;
            var half = Math.floor(headerText.length / 2);
            if (headerText.length % 2 === 0 && headerText.slice(0, half) === headerText.slice(half)) {
                headerText = headerText.slice(0, half).trim();
            }

            var $sortControls = $th.find(
                '.dt-column-order,' +
                'button.dt-column-order,' +
                'span[role="button"].dt-column-order,' +
                'span[role="button"][aria-label*="sort"],' +
                'span[role="button"][aria-label*="Activate to sort"]'
            );

            var $otherInteractive = $th.find('input, select, textarea, a, button')
                .not($sortControls)
                .not('.yadcf-filter-reset-button');
            var hasOtherControls = $otherInteractive.length > 0 || $th.find('.yadcf-filter, .yadcf-filter-wrapper').length > 0;

            $sortControls.each(function () {
                var $btn = $(this);
                var label = $btn.attr('aria-label') || $btn.attr('data-sr-label');
                if (!label) return;

                var normalized = label;
                if (hasOtherControls) {
                    var colonIndex = label.indexOf(':');
                    var actionText = label;
                    if (colonIndex !== -1) {
                        actionText = label.slice(colonIndex + 1).trim();
                    }
                    if (actionText) {
                        normalized = headerText + ': ' + actionText;
                    }
                } else {
                    normalized = label.replace(new RegExp('^' + escapeRegExp(headerText) + '\\s*:\\s*', 'i'), '');
                    if (normalized === label) {
                        normalized = label.replace(/^[^:]+:\s*/, '');
                    }
                }
                if (normalized && normalized !== label) {
                    $btn.attr('aria-label', normalized);
                    $btn.attr('data-sr-label', normalized);
                }
            });
        });
    },

    /**
     * Add descriptive aria-labels (and focus-only visibility) to yadcf
     * filter-reset ("×") buttons.
     *
     * @param {string} tableId       HTML id of the <table> element (without #).
     * @param {Array}  filterSetting The filterSetting array passed to yadcf.init().
     */
    addAriaLabelsToFilterClearButtons: function (tableId, filterSetting) {
        logTableName('addAriaLabelsToFilterClearButtons', tableId);
        var clearButtonLabels = {};
        filterSetting.forEach(function (filter) {
            if (filter.clear_button_label) {
                clearButtonLabels[filter.column_number] = filter.clear_button_label;
            }
        });

        setTimeout(function () {
            $('#' + tableId).find('.yadcf-filter-reset-button').each(function () {
                var $btn = $(this);
                var $th = $btn.closest('th');
                var $parentTr = $th.parent('tr');
                var thIndex = $parentTr.find('th').index($th);

                if (clearButtonLabels[thIndex]) {
                    $btn.attr('aria-label', clearButtonLabels[thIndex]);
                    $btn.attr('title', clearButtonLabels[thIndex]); // tooltip fallback

                    // Show the button only on keyboard focus.
                    $btn.off('focusin.srfix focusout.srfix');
                    $btn.attr('aria-hidden', 'true');
                    if (!$btn.attr('tabindex')) { $btn.attr('tabindex', '0'); }
                    $btn.on('focusin.srfix', function () { $(this).removeAttr('aria-hidden'); });
                    $btn.on('focusout.srfix', function () { $(this).attr('aria-hidden', 'true'); });
                }
            });
        }, 150);
    },

    /**
     * Associate each yadcf text filter <input class="yadcf-filter"> with its
     * column header text using aria-labelledby.
     *
     * @param {string} tableId  HTML id of the <table> element (without #).
     */
    addAriaLabelledbyToYadcfTextInputs: function (tableId) {
        logTableName('addAriaLabelledbyToYadcfTextInputs', tableId);
        setTimeout(function () {
            var $table = $('#' + tableId);
            var $thead = $table.find('thead');
            if (!$thead.length) return;

            var $labelRow = $thead.find('tr').first();
            if (!$labelRow.length) return;

            $thead.find('tr').each(function () {
                $(this).find('th').each(function (colIdx) {
                    var $th = $(this);
                    var $input = $th.find('input.yadcf-filter');
                    if (!$input.length) return;

                    var labelId = tableId + '-col-' + colIdx + '-label';

                    if (!document.getElementById(labelId)) {
                        var $labelTh = $labelRow.find('th').eq(colIdx);
                        if ($labelTh.length) {
                            var headerText = $labelTh.clone()
                                .find('input, select, button, .yadcf-filter-wrapper, .yadcf-filter, .yadcf-filter-reset-button')
                                .remove()
                                .end()
                                .text()
                                .replace(/\s+/g, ' ')
                                .trim();

                            if (!headerText) { headerText = 'Filter'; }

                            var $span = $('<span/>', {
                                id: labelId,
                                text: headerText,
                                'aria-hidden': 'true',
                                style: 'position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;'
                            });
                            $labelTh.prepend($span);
                        }
                    }

                    $('#' + labelId).attr('aria-hidden', 'true');
                    $input.attr('aria-labelledby', labelId);
                    $input.off('focusin.srlabel focusout.srlabel');
                    $input.on('focusin.srlabel', function () { $('#' + labelId).removeAttr('aria-hidden'); });
                    $input.on('focusout.srlabel', function () { $('#' + labelId).attr('aria-hidden', 'true'); });
                });
            });
        }, 200);
    },

    /**
     * Hide yadcf <select> filter values from screen readers unless focused.
     *
     * @param {string} tableId  HTML id of the <table> element (without #).
     */
    hideYadcfSelectValuesUntilFocus: function (tableId) {
        logTableName('hideYadcfSelectValuesUntilFocus', tableId);
        setTimeout(function () {
            var $table = $('#' + tableId);
            $table.find('select.yadcf-filter').each(function () {
                var $sel = $(this);
                $sel.off('focusin.srselect focusout.srselect');
                $sel.attr('aria-hidden', 'true');
                if (!$sel.attr('tabindex')) { $sel.attr('tabindex', '0'); }
                $sel.on('focusin.srselect', function () { $(this).removeAttr('aria-hidden'); });
                $sel.on('focusout.srselect', function () { $(this).attr('aria-hidden', 'true'); });
            });
        }, 200);
    }

};

module.exports = DatatableAccessibility;
