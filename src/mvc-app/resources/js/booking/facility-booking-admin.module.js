/**
 * Facility booking JavaScript
 *
 * @param {Object} p parameters
 */
let FacilityBookingAdmin = function (p) {
    this.filterBooking = p.filterBooking;
    this.getAdminTabDataUrl = p.getAdminTabDataUrl;
    this.facilityBookingPageUrl = p.facilityBookingPageUrl;
    this.shortNoticeTable = null;
    this.cancelledTable = null;
    this.declinedTable = null;
    this.pendingTable = null;
    this.newBookingTable = null;
    this.init();
};

FacilityBookingAdmin.prototype = {

    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740

        $("#company-admin-booking-tab").tabs({
            activate: function (event, ui) {
                let $tab = ui.newTab;
                let tabType = $tab.attr('data-list-type');
                that.normalizeTabAriaState();
                that.getTabData(tabType);
            }
        });
        that.enforceTabAriaState();
        that.applyTabKeyboardFix();
        that.getTabData('short_notice');

        $('#company-admin-booking-tab').on('click', '.update_booking_admin', function () {
            let bookingType = $(this).attr('data-booking-type');
            let facilityId = $(this).attr('data-facility-id');
            let bookingId = $(this).attr('data-facility-booking-id');
            let tableId = $(this).closest('table').attr('id');
            if (tableId === 'admin-booking-cancelled-table') {
                if (bookingType === 'self_booked' || bookingType === 'managed') {
                    that.filterBooking.openFacilityBookingForm(facilityId, null, bookingId, null, 1);
                }
            } else if (bookingType === 'self_booked') {
                that.filterBooking.openFacilityBookingForm(facilityId, null, bookingId, null, 1);
            } else {
                that.filterBooking.openFacilityBookingForm(facilityId, null, bookingId);
            }
        });

    },
    /**
     * Keep tab announcements to selected/not selected (remove expanded/collapsed state)
     */
    normalizeTabAriaState: function (deferFollowUp = true) {
        const $tabs = $('#company-admin-booking-tab .facility-booking-tab > li[role="tab"]');
        if (!$tabs.length) return;

        $tabs.removeAttr('aria-expanded');
        $tabs.each(function () {
            const $tab = $(this);
            const isSelected = $tab.attr('aria-selected') === 'true';
            const tabText = $tab.find('a.ui-tabs-anchor').first().text().trim();

            // For non-selected tabs, inject explicit spoken state.
            if (!isSelected && tabText) {
                $tab.attr('aria-label', tabText + ' not selected');
            } else {
                $tab.removeAttr('aria-label');
            }
        });

        // Run again on next tick to override any delayed ARIA updates from jQuery UI.
        if (deferFollowUp) {
            const that = this; // NOSONAR javascript:S7740
            setTimeout(function () {
                that.normalizeTabAriaState(false);
            }, 0);
        }
    },
    /**
     * Patch jQuery UI tabs instance so aria-expanded is never left on tabs
     */
    enforceTabAriaState: function () {
        const $container = $('#company-admin-booking-tab');
        const tabsWidget = $container.data('ui-tabs');
        const that = this; // NOSONAR javascript:S7740

        if (tabsWidget && !tabsWidget._ariaExpandedPatchedForA11y) {
            tabsWidget._ariaExpandedPatchedForA11y = true;

            const originalToggle = tabsWidget._toggle;
            tabsWidget._toggle = function () {
                originalToggle.apply(this, arguments);
                that.normalizeTabAriaState();
            };

            const originalRefresh = tabsWidget._refresh;
            tabsWidget._refresh = function () {
                originalRefresh.apply(this, arguments);
                that.normalizeTabAriaState();
            };
        }

        that.normalizeTabAriaState();
    },
    /**
     * Keyboard model for tabs:
     * - Arrow/Home/End move focus between tabs only
     * - Enter/Space activate focused tab
     * - Tab always exits tab group to selected tab panel/table
     */
    applyTabKeyboardFix: function () {
        const that = this; // NOSONAR javascript:S7740
        const $container = $('#company-admin-booking-tab');
        const $list = $('#company-admin-booking-tab .facility-booking-tab');
        if (!$list.length) return;

        const tabSel = 'li[role="tab"]';
        const tabKeySel = 'li[role="tab"], a.ui-tabs-anchor';

        // Remove jQuery UI built-in keyboard handler so we can enforce manual activation.
        $list.off('keydown');
        $list.children('li').off('keydown');

        const getTabs = function () {
            return $list.children(tabSel);
        };

        const focusSelectedPanelTarget = function (retries = 6) {
            const $activeTab = getTabs().filter('.ui-tabs-active').first();
            if (!$activeTab.length) return;

            const $activeAnchor = $activeTab.find('a.ui-tabs-anchor').first();
            const panelSelector = $activeAnchor.attr('href');
            if (!panelSelector?.startsWith('#')) return;

            const $panel = $container.find(panelSelector);
            if (!$panel.length) return;

            const $table = $panel.find('table:visible, [role="table"]:visible').first();
            const $focusable = $panel.find(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            ).filter(':visible').first();
            const $target = $table.length ? $table : $focusable;

            if ($target.length) {
                if (!$target.is('a,button,input,select,textarea,[tabindex]')) {
                    $target.attr('tabindex', '-1');
                }
                $target[0].focus();
                return;
            }

            if (retries > 0) {
                setTimeout(function () {
                    focusSelectedPanelTarget(retries - 1);
                }, 100);
                return;
            }

            $panel.focus();
        };

        $list.off('keydown.adminTabCycle', tabKeySel);
        $list.on('keydown.adminTabCycle', tabKeySel, function (e) {
            const key = e.key;
            const code = e.which || e.keyCode;
            const isForward = key === 'ArrowRight' || key === 'Right' || code === 39;
            const isBackward = key === 'ArrowLeft' || key === 'Left' || code === 37;
            const isHome = key === 'Home' || code === 36;
            const isEnd = key === 'End' || code === 35;
            const isEnter = key === 'Enter' || code === 13;
            const isSpace = key === ' ' || key === 'Spacebar' || code === 32;
            const isTab = key === 'Tab' || code === 9;

            const $tabs = getTabs();
            const $currentTab = $(this).closest('li[role="tab"]');
            const idx = $tabs.index($currentTab);
            if (idx < 0) return;

            if (isForward || isBackward || isHome || isEnd) {
                let nextIdx = idx;
                if (isForward) nextIdx = (idx + 1) % $tabs.length;
                else if (isBackward) nextIdx = (idx - 1 + $tabs.length) % $tabs.length;
                else if (isHome) nextIdx = 0;
                else if (isEnd) nextIdx = $tabs.length - 1;

                e.preventDefault();
                $tabs.eq(nextIdx).focus();
                return;
            }

            if (isEnter || isSpace) {
                e.preventDefault();
                const $anchor = $currentTab.find('a.ui-tabs-anchor').first();
                if ($anchor.length) {
                    $anchor[0].click();
                }
                return;
            }

            if (isTab && !e.shiftKey) {
                e.preventDefault();
                focusSelectedPanelTarget();
            }
        });
    },
    /**
     * Get Tab data
     */
    getTabData: function (tabType) {
        let that = this; // NOSONAR javascript:S7740
        $('#current-tab-selected-facility-administrator').val(tabType);
        $.ajax({
            url: that.getAdminTabDataUrl,
            method: 'POST',
            data: { tabType: tabType },
            beforeSend: function (jqXHR, settings) {
                $('#' + tabType).empty();
                $('#loading-spinner').show();
            },
            success: function (response) {
                $('#' + tabType).empty().append(response);
                that.initDataTables();
                $('#loading-spinner').hide();
            },
            error: function (xhr, status, error) {
            },
            complete: function () {
            }
        });
    },
    /**
     * Add aria-labels to filter clear buttons
     */
    addAriaLabelsToFilterClearButtons: function (tableId, filterSetting) {
        let that = this; // NOSONAR javascript:S7740

        let clearButtonLabels = {};
        filterSetting.forEach(function (filter) {
            if (filter.clear_button_label) {
                clearButtonLabels[filter.column_number] = filter.clear_button_label;
            }
        });

        setTimeout(function () {
            $('#' + tableId).find('.yadcf-filter-reset-button').each(function () {
                let $btn = $(this);
                let $th = $btn.closest('th');
                let $parentTr = $th.parent('tr');
                let thIndex = $parentTr.find('th').index($th);

                let lbl = clearButtonLabels[thIndex] || $btn.attr('title') || 'Clear filter';
                $btn.attr('data-a11y-label', lbl);
                $btn.attr('title', lbl); // visual tooltip fallback

                $btn.removeAttr('aria-label');
                $btn.off('focusin.srfix focusout.srfix');
                $btn.attr('aria-hidden', 'true');
                if (!$btn.attr('tabindex')) { $btn.attr('tabindex', '0'); }

                // On keyboard focus: expose and set aria-label
                $btn.on('focusin.srfix', function () {
                    let label = $(this).attr('data-a11y-label') || 'Clear filter';
                    $(this).attr('aria-label', label);
                    $(this).removeAttr('aria-hidden');
                });

                // On blur: hide again and remove aria-label
                $btn.on('focusout.srfix', function () {
                    $(this).attr('aria-hidden', 'true');
                    $(this).removeAttr('aria-label');
                });
            });

        }, 150);
    },
    /**
     * Associate text filter inputs with their column header text
     */
    addAriaLabelledbyToYadcfTextInputs: function (tableId) {
        setTimeout(function () {
            let $table = $('#' + tableId);
            let $thead = $table.find('thead');
            if (!$thead.length) return;

            let $labelRow = $thead.find('tr').first();
            if (!$labelRow.length) return;

            $thead.find('tr').each(function () {
                $(this).find('th').each(function (colIdx) {
                    let $th = $(this);
                    let $input = $th.find('input.yadcf-filter');
                    if (!$input.length) return;

                    let labelId = tableId + '-col-' + colIdx + '-label';

                    if (!document.getElementById(labelId)) {
                        let $labelTh = $labelRow.find('th').eq(colIdx);
                        if ($labelTh.length) {
                            let headerText = $labelTh.clone()
                                .find('input, select, button, .yadcf-filter-wrapper, .yadcf-filter, .yadcf-filter-reset-button')
                                .remove()
                                .end()
                                .text()
                                .replaceAll(/\s+/g, ' ')
                                .trim();

                            if (!headerText) headerText = 'Filter';

                            let $span = $('<span/>', {
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
    applyYadcfA11yFixes: function (tableId, filterSetting) {
        let that = this; // NOSONAR javascript:S7740
        that.addAriaLabelledbyToYadcfTextInputs(tableId);
        that.addAriaLabelsToFilterClearButtons(tableId, filterSetting);

        // Re-apply on redraw (paging/sorting/filtering can recreate YADCF DOM)
        $('#' + tableId)
            .off('draw.dt.a11y')
            .on('draw.dt.a11y', function () {
                that.addAriaLabelledbyToYadcfTextInputs(tableId);
                that.addAriaLabelsToFilterClearButtons(tableId, filterSetting);
            });
    },
    /**
     * Init datatables
     */
    initDataTables: function () {
        let that = this; // NOSONAR javascript:S7740

        //Function to clear datatable
        let destroyDatatable = function (tableId) {
            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }
        };

        //Filter settings
        let filterSetting = [
            {
                column_number: 0,
                filter_type: 'select',
                'aria-label': 'Facility Name',
                clear_button_label: 'Clear Facility Name filters'
            },
            {
                column_number: 1,
                filter_type: 'text',
                clear_button_label: 'Clear Facility Provider filters'
            },
            {
                column_number: 2,
                filter_type: 'text',
                clear_button_label: 'Clear Location filters'
            },
            {
                column_number: 3,
                filter_type: 'select',
                'aria-label': 'Facility Sub Type',
                clear_button_label: 'Clear Facility Sub Type filters'
            },
            {
                column_number: 4,
                filter_type: 'select',
                'aria-label': 'Linked Facilities',
                clear_button_label: 'Clear Linked Facilities filters'
            },
            {
                column_number: 5,
                filter_type: 'text',
                clear_button_label: 'Clear Booking Title filters'
            },
            {
                column_number: 6,
                filter_type: 'text',
                clear_button_label: 'Clear Date filters'
            },
            {
                column_number: 7,
                filter_type: 'text',
                clear_button_label: 'Clear Time filters'
            },
            {
                column_number: 8,
                filter_type: 'text',
                clear_button_label: 'Clear Recur filters'
            },
            {
                column_number: 9,
                filter_type: 'text',
                clear_button_label: 'Clear Requestor Name filters'
            },
            {
                column_number: 10,
                filter_type: 'text',
                clear_button_label: 'Clear Company Name filters'
            },
            {
                column_number: 11,
                filter_type: 'text',
                clear_button_label: 'Clear Customer Name filters'
            },
            {
                column_number: 12,
                filter_type: 'text',
                clear_button_label: 'Clear Notes filters'
            },
            {
                column_number: 13,
                filter_type: 'text',
                clear_button_label: 'Clear Notes filters'
            },
        ];

        let dataTableSettings = {
            "info": false,
            "dom": 'rtip',
            initComplete: function () {
                that.applyHeaderAccessibilityFixes();
                that.applyHeaderForCancelAccessibilityFixes();
                that.applyHeaderForDeclinedAccessibilityFixes();
                that.applyHeaderForPendingAccessibilityFixes();
                that.applyHeaderForNewAccessibilityFixes();
            },
            columnDefs: [
                { visible: false, targets: 12 }
            ],
            drawCallback: function (settings) {
                let api = this.api();
                that.applyHeaderAccessibilityFixes();
                that.applyHeaderForCancelAccessibilityFixes();
                that.applyHeaderForDeclinedAccessibilityFixes();
                that.applyHeaderForPendingAccessibilityFixes();
                that.applyHeaderForNewAccessibilityFixes();
                let tableObj = this.api().table();
                (new CommonFunction).datatableAccessbilityText(tableObj);
                (new CommonFunction).datatableAccessbilityText(api);
                (new CommonFunction).datatableAccessbilityText(api);
                (new CommonFunction).datatableAccessbilityText(api);
                (new CommonFunction).datatableAccessbilityText(api);
                (new CommonFunction).datatableAccessbilityText(api);

                let total = api.rows().count();
                let filtered = api.rows({ search: 'applied' }).count();
                let message = "";
                if (filtered === 0) {
                    message = "No results found after filtering.";
                } else if (filtered === 1) {
                    message = "1 result found after filtering.";
                } else if (filtered < total) {
                    message = filtered + " results found after filtering.";
                } else {
                    message = "Showing all " + total + " results.";
                }

                let $status = $('#filter-status');
                $status.text('');

                setTimeout(function () {
                    $status.text(message);
                }, 50);
            }
        };

        destroyDatatable('admin-booking-shortnotice-table');
        that.shortNoticeTable = new DataTable('#admin-booking-shortnotice-table',
            dataTableSettings
        );

        //Filters
        if ($('#admin-booking-shortnotice-table').length > 0) {
            yadcf.init(that.shortNoticeTable,
                filterSetting
            );
            that.applyYadcfA11yFixes('admin-booking-shortnotice-table', filterSetting);
            $('#admin-booking-shortnotice-table th').eq(0).find('select').attr('aria-label', 'Facility Name');
            $('#admin-booking-shortnotice-table th').eq(3).find('select').attr('aria-label', 'Facility Sub Type');
            $('#admin-booking-shortnotice-table th').eq(4).find('select').attr('aria-label', 'Linked Facilities');
        }

        destroyDatatable('admin-booking-new-table');
        that.newBookingTable = new DataTable('#admin-booking-new-table',
            dataTableSettings
        );
        //Filters
        if ($('#admin-booking-new-table').length > 0) {
            yadcf.init(that.newBookingTable,
                filterSetting
            );
            that.applyHeaderForNewAccessibilityFixes();
            that.applyYadcfA11yFixes('admin-booking-new-table', filterSetting);
            $('#admin-booking-new-table th').eq(0).find('select').attr('aria-label', 'Facility Name');
            $('#admin-booking-new-table th').eq(3).find('select').attr('aria-label', 'Facility Sub Type');
            $('#admin-booking-new-table th').eq(4).find('select').attr('aria-label', 'Linked Facilities');
        }

        destroyDatatable('admin-booking-declined-table');
        that.declinedTable = new DataTable('#admin-booking-declined-table',
            dataTableSettings
        );
        //Filters
        if ($('#admin-booking-declined-table').length > 0) {
            yadcf.init(that.declinedTable,
                filterSetting
            );
            that.applyHeaderForDeclinedAccessibilityFixes();
            that.applyYadcfA11yFixes('admin-booking-declined-table', filterSetting);
            $('#admin-booking-declined-table th').eq(0).find('select').attr('aria-label', 'Facility Name');
            $('#admin-booking-declined-table th').eq(3).find('select').attr('aria-label', 'Facility Sub Type');
            $('#admin-booking-declined-table th').eq(4).find('select').attr('aria-label', 'Linked Facilities');
        }

        destroyDatatable('admin-booking-pending-table');
        that.pendingTable = new DataTable('#admin-booking-pending-table',
            dataTableSettings
        );
        //Filters
        if ($('#admin-booking-pending-table').length > 0) {
            yadcf.init(that.pendingTable,
                filterSetting
            );
            this.applyHeaderForPendingAccessibilityFixes();
            that.applyYadcfA11yFixes('admin-booking-pending-table', filterSetting);
            $('#admin-booking-pending-table th').eq(0).find('select').attr('aria-label', 'Facility Name');
            $('#admin-booking-pending-table th').eq(3).find('select').attr('aria-label', 'Facility Sub Type');
            $('#admin-booking-pending-table th').eq(4).find('select').attr('aria-label', 'Linked Facilities');
        }

        destroyDatatable('admin-booking-cancelled-table');
        that.cancelledTable = new DataTable('#admin-booking-cancelled-table',
            dataTableSettings
        );
        //Filters
        if ($('#admin-booking-cancelled-table').length > 0) {
            yadcf.init(that.cancelledTable,
                filterSetting
            );
            that.applyHeaderForCancelAccessibilityFixes();
            that.applyYadcfA11yFixes('admin-booking-cancelled-table', filterSetting);
            $('#admin-booking-cancelled-table th').eq(0).find('select').attr('aria-label', 'Facility Name');
            $('#admin-booking-cancelled-table th').eq(3).find('select').attr('aria-label', 'Facility Sub Type');
            $('#admin-booking-cancelled-table th').eq(4).find('select').attr('aria-label', 'Linked Facilities');
        }

        //Context menu
        that.initContextMenu();
    },
    /**
     * Clean up header announcements for NVDA to hide sort button
     */
    applyHeaderControlsAccessibilityFixes: function (dataTableInstance) {
        if (!dataTableInstance) return;
        let container = dataTableInstance.table().container();
        let $container = $(container);

        // Expose the DataTables sort control only on focus
        let $sortControls = $container
            .find('thead th .dt-column-order, thead th button.dt-column-order, thead th span[role="button"].dt-column-order, thead th span[role="button"][aria-label*="sort"], thead th span[role="button"][aria-label*="Activate to sort"], thead th span[role="presentation"].dt-column-order');

        $sortControls.off('focusin.srfix focusout.srfix');
        $sortControls.attr('aria-hidden', 'true');

        $sortControls.each(function () {
            let $btn = $(this);
            // Keep native buttons alone; for other elements ensure they can be tabbed to.
            if ((($btn.prop('tagName') || '')).toLowerCase() !== 'button') {
                let tabindex = $btn.attr('tabindex');
                if (!tabindex || tabindex === '-1') {
                    $btn.attr('tabindex', '0');
                }
            }
        });

        $sortControls.on('focusin.srfix', function () { $(this).removeAttr('aria-hidden'); });
        $sortControls.on('focusout.srfix', function () { $(this).attr('aria-hidden', 'true'); });

        // Expose the YADCF filters only on focus
        let $filters = $container
            .find('thead th select.yadcf-filter, thead th input.yadcf-filter, thead th textarea.yadcf-filter');

        $filters.off('focusin.srfix focusout.srfix');
        $filters.attr('aria-hidden', 'true');
        $filters.on('focusin.srfix', function () { $(this).removeAttr('aria-hidden'); });
        $filters.on('focusout.srfix', function () { $(this).attr('aria-hidden', 'true'); });

        // Expose the YADCF clear buttons only on focus
        let $clearBtns = $container
            .find('thead th .yadcf-filter-reset-button');

        $clearBtns.off('focusin.srfix focusout.srfix');
        $clearBtns.attr('aria-hidden', 'true');
        $clearBtns.removeAttr('aria-label');

        $clearBtns.each(function () {
            let $btn = $(this);
            if (!$btn.attr('tabindex')) { $btn.attr('tabindex', '0'); }
            if (!$btn.attr('data-a11y-label')) {
                let lbl = $btn.attr('title') || 'Clear filter';
                $btn.attr('data-a11y-label', lbl);
            }
        });

        $clearBtns.on('focusin.srfix', function () {
            let $btn = $(this);
            let lbl = $btn.attr('data-a11y-label') || $btn.attr('title') || 'Clear filter';
            $btn.attr('aria-label', lbl);
            $btn.removeAttr('aria-hidden');
        });

        $clearBtns.on('focusout.srfix', function () {
            $(this).attr('aria-hidden', 'true');
            $(this).removeAttr('aria-label');
        });
        let $labelSpans = $container.find('thead span[id$="-label"]');
        $labelSpans.attr('aria-hidden', 'true');

        $container.off('focusin.srfixhdr').on('focusin.srfixhdr', function (e) {
            if (!$(e.target).closest('thead').length) {
                $filters.attr('aria-hidden', 'true');
                $clearBtns.attr('aria-hidden', 'true');
                $clearBtns.removeAttr('aria-label');
                $labelSpans.attr('aria-hidden', 'true');
            }
        });

    },

    applyHeaderAccessibilityFixes: function () {
        this.applyHeaderControlsAccessibilityFixes(this.shortNoticeTable);

    },
    applyHeaderForCancelAccessibilityFixes: function () {
        this.applyHeaderControlsAccessibilityFixes(this.cancelledTable);

    },
    applyHeaderForDeclinedAccessibilityFixes: function () {
        this.applyHeaderControlsAccessibilityFixes(this.declinedTable);

    },
    applyHeaderForPendingAccessibilityFixes: function () {
        this.applyHeaderControlsAccessibilityFixes(this.pendingTable);

    },
    applyHeaderForNewAccessibilityFixes: function () {
        this.applyHeaderControlsAccessibilityFixes(this.newBookingTable);

    },
    /**
     * Context menu init
     */
    initContextMenu: function () {
        let that = this; // NOSONAR javascript:S7740
        $.contextMenu({
            selector: ".update_booking_admin",
            items: {
                'update_booking': {
                    name: "Update Booking",
                    icon: "edit",
                    visible: function (key, opt) {
                        let tableId = $(this).closest('table').attr('id');
                        let bookingType = opt.$trigger.attr('data-booking-type');
                        if (tableId === 'admin-booking-cancelled-table') {
                            return bookingType !== 'managed' && bookingType !== 'self_booked';
                        }
                        return bookingType !== 'self_booked';
                    },
                    callback: function (key, opt) {
                        let $bookingEle = opt.$trigger;
                        that.filterBooking.openFacilityBookingForm($($bookingEle).attr('data-facility-id'), null, $($bookingEle).attr('data-facility-booking-id'));
                    }
                },
                'view_booking': {
                    name: "View Booking",
                    icon: "fa-eye",
                    visible: function (key, opt) {
                        let bookingType = opt.$trigger.attr('data-booking-type');
                        return bookingType === 'self_booked' || bookingType === 'managed';
                    },
                    callback: function (key, opt) {
                        let $bookingEle = opt.$trigger;
                        that.filterBooking.openFacilityBookingForm($($bookingEle).attr('data-facility-id'), null, $($bookingEle).attr('data-facility-booking-id'), null, 1);
                    }
                },
                'view_facility_booking_screen': {
                    name: "Facility Bookings screen",
                    icon: "fa-map",
                    visible: function (key, opt) {
                        return true;
                    },
                    callback: function (key, opt) {
                        let $bookingEle = opt.$trigger;
                        that.redirectToFacilityBookingPage($bookingEle.attr('data-linked-facility-booking'), $bookingEle.attr('data-booking-date'));
                    }
                }
            }
        });
    },
    /**
     * Redirect to facility booking page with filters
     */
    redirectToFacilityBookingPage: function (facilityBookings, date) {
        let that = this; // NOSONAR javascript:S7740
        let tabType = $('#current-tab-selected-facility-administrator').val();

        localStorage.setItem('facility_booking_admin_filter_setting', JSON.stringify({
            'facility_bookings': facilityBookings,
            'date': date,
            'tab_type': tabType
        }));

        window.open(that.facilityBookingPageUrl, '_blank');
    }
}

module.exports = FacilityBookingAdmin;
