/**
 * Facility filter JavaScript
 *
 * @param {Object} p parameters
 */
var FacilityFilter = function (p) {
    this.filterAreaDataUrl = p.filterAreaDataUrl
    this.filterTypeDataUrl = p.filterTypeDataUrl
    this.filterSubTypeDataUrl = p.filterSubTypeDataUrl;
    this.filterServiceDataUrl = p.filterServiceDataUrl;
    this.filterEquipmentDataUrl = p.filterEquipmentDataUrl;
    this.filterSaveUrl = p.filterSaveUrl;
    this.listSavedFilterUrl = p.listSavedFilterUrl;
    this.deleteSavedFilterUrl = p.deleteSavedFilterUrl;
    this.facilityTable = null;
    this.facilityFilterLocalStorageName = 'facility_filter_local_storage';
    this.facilityFilterArchiveShowAllStorageName = 'facility_filter_archive_show_all_local_storage';
    this.init();
};

FacilityFilter.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        that.setContentToggle(true);
        that.facilityTable = new DataTable('#facility-list-table', {
            "info": false,
            "dom": 'rtip',
            scrollX: true,
            initComplete: function () {
                that.hideDTScrollHeadCloneFromSR();
                that.hideSortButtonsFromSR();
                that.applyHeaderAccessibilityFixes();
            },            
            columnDefs: [
                { 'visible': false, 'targets': 16 },
                { 'orderable': false, 'targets': 0 }
            ],
            "order": [[1, "asc"]],
            headerCallback: function (thead) {
                var $ths = $(thead).find('th');
                $ths.attr('tabindex', -1);
                var $firstTh = $ths.eq(0);
                $firstTh.attr('tabindex', 0);
                $firstTh.off('keydown.facHead').on('keydown.facHead', function (e) {
                    if (e.which === 9 && !e.shiftKey) {
                        e.preventDefault();
                        $('#facility-list-table tbody tr:first td:first div:first i:first').focus();
                    }
                });
            },
            drawCallback: function () {
                that.setContentToggle(false)
                that.hideDTScrollHeadCloneFromSR();
                that.hideSortButtonsFromSR();
                that.applyHeaderAccessibilityFixes();
                var $table = $('#facility-list-table');
                var selector = 'tbody tr td:first-child div:first-child i';
                var $icons = $table.find(selector);
                $icons.attr('tabindex', 0);
                $table.off('keydown.facIcon').on('keydown.facIcon', selector, function (e) {
                    if (e.which === 9 && !e.shiftKey) {
                        var $all = $table.find(selector);
                        var idx = $all.index(this);
                        if (idx > -1 && idx < $all.length - 1) {
                            e.preventDefault();
                            $all.eq(idx + 1).focus();
                        }
                    } else if (e.which === 13) {
                        var $btn = $(this).closest('a,button');
                        if ($btn.length) {
                            $btn.trigger('click');
                        } else {
                            $(this).trigger('click');
                        }
                    }
                });
                $icons.last().off('keydown.lastFix').on('keydown.lastFix', function (e) {
                    if (e.which === 9 && !e.shiftKey) {
                        $(this).blur();
                    }
                });
                (new CommonFunction).datatableAccessbilityText(that.facilityTable);            
            }
        });
        that.applyHeaderAccessibilityFixes();
        that.toggleFilterVisibilty();
        that.initFilterKeyboardSupport();
        that.initFacilityFilterElements();
        that.loadPreviousFilter();
        that.initToolTips();
    },

    /**
     * Clean up header announcements for NVDA without changing the UI.
     */
    applyHeaderAccessibilityFixes: function () {
        if (!this.facilityTable) return;
        var container = this.facilityTable.table().container();
        var $container = $(container);
        // Hide DataTables sort controls from screen readers and remove from Tab order.
        $container
            .find('thead th .dt-column-order, thead th span[role="button"].dt-column-order, thead th span[role="button"][aria-label*="sort"], thead th span[role="button"][aria-label*="Activate to sort"]')
            .attr('aria-hidden', 'true')
            .attr('tabindex', '-1')
            .attr('role', 'presentation');
    },
    /**
    * When scrollX is enabled, DataTables creates a separate header-only table
    * inside the scroll head container. NVDA reads it as a second table.
    * Hide that cloned header table from screen readers while keeping UI unchanged.
    */
    hideDTScrollHeadCloneFromSR: function () {
    if (!this.facilityTable) return;

    var container = this.facilityTable.table().container();
    var $container = $(container);

    var $scrollHeadTables = $container.find(
        '.dataTables_scrollHead table, .dt-scroll-head table'
    );

    if ($scrollHeadTables.length) {
        $scrollHeadTables
        .attr('aria-hidden', 'true')
        .attr('role', 'presentation');

        // Ensure nothing inside the clone is tabbable/focusable
        $scrollHeadTables
        .find('th, td, a, button, input, select, textarea, [tabindex]')
        .attr('tabindex', '-1');
    }

    $container.find('.fixedHeader-floating, .fixedHeader-locked')
        .attr('aria-hidden', 'true')
        .attr('role', 'presentation');
    },

    /**
     * Hide DataTables sort buttons from screen readers
     */
    hideSortButtonsFromSR: function () {
        if (!this.facilityTable) return;

        var container = this.facilityTable.table().container();
        var $container = $(container);

        // Hide span elements with role="button"
        $container.find('th span[role="button"], th .dt-column-order')
            .attr('aria-hidden', 'true')
            .attr('tabindex', '-1');
    },

    /**
     * Accessibility fix for Chosen <select> widgets.
     * @param {string} selectId The id of the original <select>
     */
    applyChosenA11y: function (selectId) {
        var $select = $('#' + selectId);
        if (!$select.length) return;

        var $label = $('label[for="' + selectId + '"]');
        if (!$label.length) return;

        var labelId = $label.attr('id');
        if (!labelId) {
            labelId = 'lbl-' + selectId;
            $label.attr('id', labelId);
        }

        var $container = $select.next('.chosen-container');
        if (!$container.length) return;

        var isMultiple = !!$select.prop('multiple');
        var $control = isMultiple ? $container.find('ul.chosen-choices').first() : $container.find('a.chosen-single').first();
        if (!$control.length) return;

        $control.attr({
            'aria-labelledby': labelId,
            'role': 'combobox',
            'aria-haspopup': 'listbox',
            'aria-expanded': $container.hasClass('chosen-with-drop') ? 'true' : 'false'
        });

        // Multi-select: SR focus typically lands on the search field input
        if (isMultiple) {
            var $multiInput = $container.find('li.search-field input').first();
            if ($multiInput.length) {
                $multiInput.attr({
                    'aria-labelledby': labelId,
                    'role': 'searchbox',
                    'aria-autocomplete': 'list'
                });
                $multiInput.removeAttr('aria-label');
            }
        }

        // Label dropdown search input too (single/multi)
        var $searchInput = $container.find('.chosen-search input').first();
        if ($searchInput.length) {
            $searchInput.attr({
                'aria-labelledby': labelId,
                'role': 'searchbox',
                'aria-autocomplete': 'list'
            });
            $searchInput.removeAttr('aria-label');
        }

        // Keep aria-expanded in sync
        $select.off('.chosenA11y_' + selectId);
        $select.on('chosen:showing_dropdown.chosenA11y_' + selectId, function () {
            $control.attr('aria-expanded', 'true');
        });
        $select.on('chosen:hiding_dropdown.chosenA11y_' + selectId, function () {
            $control.attr('aria-expanded', 'false');
        });
    },
    /**
     * Init tool tips
     */
    initToolTips: function () {
        //Linked Facility Tooltip
        $(document).tooltip({
            items: ".primary-facility-td",
            content: function () {
                let linkedFacilities = JSON.parse($('<textarea/>').html($(this).attr('data-linked-facilities')).text());
                if (linkedFacilities.length == 0) { // no linked facilities
                    return '<span class="linked-facility-tooltip-table">No Linked Facilities</span>';
                }
                let mandatoryFacilities = [];
                let nonMandatoryFacilities = [];
                linkedFacilities.forEach(function (data) {
                    if (data['pivot']['FCLK_Mandatory'] == 1) {
                        mandatoryFacilities.push(data['FC_FacilityName']);
                    } else {
                        nonMandatoryFacilities.push(data['FC_FacilityName']);
                    }
                });
                let $table = $('<table class="linked-facility-tooltip-table"><thead><tr><th>Type</th><th></th></tr></thead></table>');
                let $tableBody = $('<tbody></tbody>');
                if (mandatoryFacilities.length) {
                    $tableBody.append(
                        $('<tr><td>Mandatory</td></tr>').append($('<td></td>').text(mandatoryFacilities.join(', ')))
                    )
                }
                if (nonMandatoryFacilities.length) {
                    $tableBody.append(
                        $('<tr><td>Non-Mandatory</td></tr>').append($('<td></td>').text(nonMandatoryFacilities.join(', ')))
                    )
                }
                return $table.append($tableBody);
            }
        });
    }, 
    /*
    * Toggle filters visibilty
    */
    toggleFilterVisibilty: function () {
        var that = this; // NOSONAR javascript:S7740

        $("#facility-filter-trigger-dropdown").off("click.filterToggle");
        $("#facility-filter-details-drop-down").off("click.detailsToggle");
        $(".clearFilterIcon").off("click.clearFilter");
        $(document).off("click.filterOutside");

        $("#facility-filter-trigger-dropdown").on("click.filterToggle", function (e) {
            e.stopPropagation();
            $("#facility-filter-content").toggle();
            $("#facility-filter-trigger-dropdown-icon").toggleClass("fa-caret-right fa-caret-down");
        });

        $("#facility-filter-details-drop-down").on("click.detailsToggle", function (e) {
            e.stopPropagation();

            const $trigger = $(this);
            const $content = $("#facility-filter-details");
            const $icon = $("#facility-filter-details-drop-down-icon");

            const isExpanded = $trigger.attr("aria-expanded") === "true";
            const newExpanded = !isExpanded;

            if (newExpanded) {
                $content.show();
            } else {
                $content.hide();
            }

            $content.prop("hidden", !newExpanded);
            $trigger.attr("aria-expanded", String(newExpanded));

            if (newExpanded) {
                $icon.removeClass("fa-angle-right").addClass("fa-angle-down");
            } else {
                $icon.removeClass("fa-angle-down").addClass("fa-angle-right");
            }
        });

        $(".clearFilterIcon").on("click.clearFilter", function () {
            that.clearFilter();
        });

        $(document).on("click.filterOutside", function (e) {
            var $filterWrapper = $(".filter-wrapper");
            if (!$filterWrapper.is(e.target) && $filterWrapper.has(e.target).length === 0) {
                if ($("#facility-filter-content").is(":visible")) {
                    $("#facility-filter-content").hide();
                    $("#facility-filter-trigger-dropdown-icon")
                        .removeClass("fa-caret-down")
                        .addClass("fa-caret-right");
                }
            }
        });
    },
    /**
     * Accessibility: Keyboard support for the Facility Catalogue filter dropdown.
     */
    initFilterKeyboardSupport: function () {
        var $trigger = $('#facility-filter-trigger-dropdown');
        var $content = $('#facility-filter-content');
        var $icon = $('#facility-filter-trigger-dropdown-icon');
        var $detailsBtn = $('#facility-filter-details-drop-down');
        var $details = $('#facility-filter-details');
        var $detailsIcon = $('#facility-filter-details-drop-down-icon');
        var $clearBtn = $('#facility-filter-cancel-btn');
        var $includeArchived = $('#include-archived-facilities');

        function firstFocusable(containerEl) {
            if (!containerEl) return null;
            return containerEl.querySelector(
                'input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), ' +
                'button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
            );
        }

        function syncMainAria() {
            var open = $content.length ? $content.is(':visible') : false;
            if ($trigger.length) $trigger.attr('aria-expanded', open ? 'true' : 'false');
            if ($content.length) $content.attr('aria-hidden', open ? 'false' : 'true');
            if ($icon.length) {
                $icon.toggleClass('fa-caret-right', !open);
                $icon.toggleClass('fa-caret-down', open);
            }
            return open;
        }

        function syncDetailsAria() {
            var open = $details.length ? $details.is(':visible') : false;
            if ($detailsBtn.length) $detailsBtn.attr('aria-expanded', open ? 'true' : 'false');
            if ($details.length) $details.attr('aria-hidden', open ? 'false' : 'true');
            if ($detailsIcon.length) {
                $detailsIcon.toggleClass('fa-angle-right', !open);
                $detailsIcon.toggleClass('fa-angle-down', open);
            }
            return open;
        }

        // Safe re-init
        try {
            $trigger.off('.facFilterA11y');
            $clearBtn.off('.facFilterA11y');
            $content.off('.facFilterA11y');
            $(document).off('keydown.facFilterA11y');
        } catch (e) { /* ignore */ }

        // Keep ARIA in sync after open/close.
        $trigger.on('click.facFilterA11y', function () { window.setTimeout(syncMainAria, 0); });
        $detailsBtn.on('click.facFilterA11y', function () { window.setTimeout(syncDetailsAria, 0); });

        // Make Enter/Space activate the trigger (since it's a DIV role=button).
        $trigger.on('keydown.facFilterA11y', function (e) {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                $trigger.trigger('click');
                return;
            }

            // Expanded: Tab from Filters enters the filter.
            if (e.key === 'Tab' && !e.shiftKey) {
                if (!syncMainAria()) return;
                var first = firstFocusable($content[0]);
                if (first) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });

        // Expanded: Shift+Tab from first focusable returns to Filters.
        $content.on('keydown.facFilterA11y', function (e) {
            if (e.key === 'Tab' && e.shiftKey) {
                if (!syncMainAria()) return;
                var first = firstFocusable($content[0]);
                if (first && e.target === first) {
                    e.preventDefault();
                    $trigger.focus();
                    return;
                }
            }

            // NVDA-off parity: Enter toggles checkboxes.
            if (e.key === 'Enter') {
                var t = e.target;
                if (t && t.matches && t.matches('input[type="checkbox"]')) {
                    e.preventDefault();
                    t.click();
                    return;
                }
            }

            // ESC collapses filter when focus is inside.
            if (e.key === 'Escape') {
                if (!syncMainAria()) return;
                if ($content[0] && $content[0].contains(document.activeElement)) {
                    e.preventDefault();
                    $trigger.trigger('click');
                    window.setTimeout(function () { syncMainAria(); $trigger.focus(); }, 0);
                }
            }
        });

        // Expanded: Tab from Clear -> Include Archived.
        $clearBtn.on('keydown.facFilterA11y', function (e) {
            if (e.key !== 'Tab' || e.shiftKey) return;
            if (!syncMainAria()) return;
            if (!$includeArchived.length) return;
            e.preventDefault();
            $includeArchived.focus();
        });

        // Expanded: Shift+Tab from Include Archived -> Clear.
        $(document).on('keydown.facFilterA11y', function (e) {
            if (e.key !== 'Tab' || !e.shiftKey) return;
            if (!syncMainAria()) return;
            if (!$clearBtn.length || !$includeArchived.length) return;
            if (e.target === $includeArchived[0] || $includeArchived[0].contains(e.target)) {
                e.preventDefault();
                $clearBtn.focus();
            }
        });

        // Also allow Enter to toggle Include Archived / Show All checkboxes (NVDA-off parity)
        $(document).on('keydown.facFilterA11y', function (e) {
            if (e.key !== 'Enter') return;
            var t = e.target;
            if (t && t.matches && t.matches('input[type="checkbox"]')) {
                // Restrict to the Facility filter area checkboxes only
                if ($(t).is('#include-archived-facilities, #show-all-facilities')) {
                    e.preventDefault();
                    t.click();
                }
            }
        });

        syncMainAria();
        syncDetailsAria();
    },
    
    /**
     * Clear filter
     *
     */
    clearFilter: function () {
        let that = this; // NOSONAR javascript:S7740
        $('.clearFilterIcon').hide();
        $('.validation-error-text').remove();
        $("#facility_filter_privacy_type option:first").attr('selected', 'selected');
        $("#facility_filter_saved_filter_list option:first").attr('selected', 'selected');
        that.clearFilterFields();
        that.facilityTable.columns().search('').draw();
        localStorage.removeItem(that.facilityFilterLocalStorageName);
        that.checkboxFilters();
    },
    /*
    * Facility filter elements
    */
    initFacilityFilterElements: function () {
        var that = this; // NOSONAR javascript:S7740
        $('#facility-filter-facility-area-filter-data').chosen({
            width: '100%'
        })
        $('#facility-filter-provider-type-filter-data').chosen({
            width: '100%'
        });
        $('#facility-filter-default-booking-type-filter-data').chosen({
            width: '100%'
        });
        $('#facility-filter-facility-type-filter-data').chosen({
            width: '100%'
        }).change(function () {
            that.setFilterSubTypeSelect();
        });
        $('#facility-filter-facility-sub-type-filter-data').chosen({
            width: '100%'
        })
        $('#facility-filter-facility-service-filter-data').chosen({
            width: '100%'
        })
        $('#facility-filter-facility-equipment-filter-data').chosen({
            width: '100%'
        })
        $('#filter_facility_status_data').chosen({
            width: '100%'
        });
        $('#filter_facility_accessible_data').chosen({
            width: '100%'
        });
        that.setFilterAreaSelect();

        // Accessibility: apply ARIA labelling for Chosen selects
        [
            'facility-filter-facility-area-filter-data',
            'facility-filter-provider-type-filter-data',
            'facility-filter-default-booking-type-filter-data',
            'facility-filter-facility-type-filter-data',
            'facility-filter-facility-sub-type-filter-data',
            'facility-filter-facility-service-filter-data',
            'facility-filter-facility-equipment-filter-data',
            'filter_facility_status_data',
            'filter_facility_accessible_data'
        ].forEach(function (id) {
            that.applyChosenA11y(id);
        });


        that.setFilterTypeSelect();
        that.setFilterSerivceSelect();
        that.setFilterEquipmentSelect();

        //Apply filter
        $('#facility-filter-apply-btn').on('click', function () {
            that.applyFacilityFilter()
        });

        //Save filter
        $('#facility-filter-private-save-btn').on('click', function () {
            that.saveFacilityFilter('private');
        });
        $('#facility-filter-public-save-btn').on('click', function () {
            that.saveFacilityFilter('public');
        });

        //Delete filter
        $('#facility-filter-delete-btn').on('click', function () {
            that.deleteFilter($('#facility_filter_saved_filter_list').val());
        });

        //Cancel filter
        $('#facility-filter-cancel-btn').on('click', function () {
            that.clearFilter();
            $("#facility-filter-trigger-dropdown").trigger("click");
        });

        //Load saved filter
        $('#facility_filter_privacy_type').on('change', function () {
            that.clearFilterFields();
            if ($('#facility_filter_privacy_type').val() != '') {
                that.loadSavedFilter();
            }
            else {
                $("#facility_filter_saved_filter_list").empty();
                $("#facility_filter_saved_filter_list").append($('<option></option>').val('').html('Select Filter'));
                $("#facility_filter_saved_filter_list option:first").attr('selected', 'selected');
            }
        });

        //Filter populate saved filter
        $('#facility-filter-go-btn').on('click', function () {
            that.applyFacilityFilter();
        });

        $("#facility_filter_saved_filter_list").on("change", async function () {
            let $selectFilter = $("#facility_filter_saved_filter_list option:selected");
            if (typeof $selectFilter.attr("data-filter-json") == "undefined" || $("#facility_filter_privacy_type").val() == '') {
                that.clearFilterFields();
                return false;
            }
            let filterJson = $selectFilter.attr("data-filter-json");
            await that.applyExistingFilter(JSON.parse(filterJson), true);
        });

        //Archived and show all
        that.checkboxFilters();
    },
    /**
     * Archived and show all
     */
    checkboxFilters: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#include-archived-facilities').on('click', function () {
            that.filterArchivedShowAll();
        });
        $('#show-all-facilities').on('click', function () {
            that.filterArchivedShowAll();
        });
        let checkBoxFilter = JSON.parse(localStorage.getItem(that.facilityFilterArchiveShowAllStorageName));
        if (checkBoxFilter != null) {
            if (checkBoxFilter.archived) {
                $('#include-archived-facilities').prop('checked', true);
            }
            if (checkBoxFilter.showAll) {
                $('#show-all-facilities').prop('checked', true);
            }
        }
        that.filterArchivedShowAll();
    },
    /**
     * Filter archived
     */
    filterArchivedShowAll: function () {
        this.setContentToggle(true);
        let that = this; // NOSONAR javascript:S7740
        //Show archived
        let archivedRows = [];
        let counter = 1;
        let hasStatusFilter = $('#filter_facility_status_data').val() != '';
        let includeArchivedChecked = $('#include-archived-facilities').is(':checked');

        if (!hasStatusFilter) {
            that.facilityTable.column(2).search(function (value) {
                match = true;
                if (value == 'Archived') {
                    match = includeArchivedChecked ? true : false;
                    archivedRows.push(counter);
                }
                counter++;
                return match;
            });
        }

        //Show All - always apply regardless of status filter
        let counter2 = 1;
        let statusFilterValue = $('#filter_facility_status_data').val();
        let isArchivedStatusFilter = statusFilterValue && statusFilterValue.includes('Archived');
        that.facilityTable.column(16).search(function (value) {
            match = true;
            if (value == 'no' && !$('#show-all-facilities').is(':checked')) {
                match = false;
                //Include Archived checkbox overrides admin/booker check - show ALL archived
                let isArchivedRow = archivedRows.includes(counter2) || isArchivedStatusFilter;
                if (isArchivedRow && includeArchivedChecked) {
                    match = true;
                }
            }
            counter2++;
            return match;
        });

        localStorage.setItem(that.facilityFilterArchiveShowAllStorageName, JSON.stringify(
            {
                'archived': $('#include-archived-facilities').is(':checked'),
                'showAll': $('#show-all-facilities').is(':checked')
            }
        )); //Store for page reload
        that.facilityTable.draw();
    },
    /*
   * Facility area drop down
   */
    setFilterAreaSelect: function (selected = []) {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            $.ajax({
                url: that.filterAreaDataUrl,
                method: 'GET',
                beforeSend: function () {
                    $('#facility-filter-facility-area-filter-data').empty();
                },
                success: function (response) {
                    response.forEach(element => {
                        let $option = $('<option></option>').attr('value', element['DivisionName']).attr('data-id', element['DivisionID']).text(element['DivisionName']);
                        if (selected.includes(element['DivisionName'])) {
                            $option.attr('selected', true);
                        }
                        $('#facility-filter-facility-area-filter-data').append(
                            $option
                        );
                    });
                    $('#facility-filter-facility-area-filter-data').trigger("chosen:updated");
                    that.applyChosenA11y('facility-filter-facility-area-filter-data');
                    resolve();
                },
                error: function (xhr, status, error) {
                    reject(error);
                }
            });
        });
    },
    /*
    * Facility type drop down
    */
    setFilterTypeSelect: function (selected = [], filterSubTypesSelected = []) {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            $.ajax({
                url: that.filterTypeDataUrl,
                method: 'GET',
                beforeSend: function () {
                    $('#facility-filter-facility-type-filter-data').empty();
                },
                success: function (response) {
                    response.forEach(element => {
                        let $option = $('<option></option>').attr('value', element['FT_FacilityType']).attr('data-id', element['FT_FacilityTypeID']).text(element['FT_FacilityType']);
                        if (selected.includes(element['FT_FacilityType'])) {
                            $option.attr('selected', true);
                        }
                        $('#facility-filter-facility-type-filter-data').append(
                            $option
                        );
                    });
                    $('#facility-filter-facility-type-filter-data').trigger("chosen:updated");
                    that.applyChosenA11y('facility-filter-facility-type-filter-data');
                    that.setFilterSubTypeSelect(filterSubTypesSelected).then(() => resolve()).catch(reject);
                },
                error: function (xhr, status, error) {
                    reject(error);
                }
            });
        });
    },
    /**
     * Facility Sub Type
     */
    setFilterSubTypeSelect: function (selected = []) {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            let $subTypeSelect = $('#facility-filter-facility-sub-type-filter-data');
            let previousSelected = $subTypeSelect.val() ? $subTypeSelect.val().slice() : [];
            $subTypeSelect.empty();
            let facilityTypeIds = [];
            $('#facility-filter-facility-type-filter-data option:selected').each(function (index, element) {
                facilityTypeIds.push($(element).attr('data-id'));
            });
            if (facilityTypeIds.length === 0) {
                $subTypeSelect.val([]).empty().trigger("chosen:updated");
                return resolve();
            }
            $.ajax({
                url: that.filterSubTypeDataUrl,
                data: {
                    'facilityTypeIds': facilityTypeIds
                },
                method: 'GET',
                beforeSend: function () {
                    $subTypeSelect.empty();
                },
                success: function (response) {
                    let newOptions = [];
                    Object.entries(response).forEach(([key, value]) => {
                        let $optGroup = $('<optgroup></optgroup>').attr('label', key);
                        Object.entries(value).forEach(([key1, value1]) => {
                            let $option = $('<option></option>').attr('value', value1).text(value1);
                            newOptions.push(value1);
                            if (selected.length > 0) {
                                if (selected.includes(value1)) {
                                    $option.attr('selected', true);
                                }
                            } else {
                                if (previousSelected.includes(value1)) {
                                    $option.attr('selected', true);
                                }
                            }
                            $optGroup.append($option);
                        });
                        $subTypeSelect.append($optGroup);
                    });
                    if (selected.length === 0 && previousSelected.length > 0) {
                        let validSelected = previousSelected.filter(v => newOptions.includes(v));
                        $subTypeSelect.val(validSelected);
                    }
                    $subTypeSelect.trigger("chosen:updated");
                that.applyChosenA11y('facility-filter-facility-sub-type-filter-data');
                    resolve();
                },
                error: function (xhr, status, error) {
                    reject(error);
                }
            });
        });
    },
    /**
     * Equipment drop down
     */
    setFilterEquipmentSelect: function (selected = []) {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            $.ajax({
                url: that.filterEquipmentDataUrl,
                method: 'GET',
                beforeSend: function () {
                    $('#facility-filter-facility-equipment-filter-data').empty();
                },
                success: function (response) {
                    response.forEach(element => {
                        let $option = $('<option></option>').attr('value', element['EQ_Equipment']).text(element['EQ_Equipment']);
                        if (selected.includes(element['EQ_Equipment'])) {
                            $option.attr('selected', true);
                        }
                        $('#facility-filter-facility-equipment-filter-data').append(
                            $option
                        );
                    });
                    $('#facility-filter-facility-equipment-filter-data').trigger("chosen:updated");
                    that.applyChosenA11y('facility-filter-facility-equipment-filter-data');
                    resolve();
                },
                error: function (xhr, status, error) {
                    reject(error);
                }
            });
        });
    },
    /**
     * Service drop down
     */
    setFilterSerivceSelect: function (selected = []) {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            $.ajax({
                url: that.filterServiceDataUrl,
                method: 'GET',
                beforeSend: function () {
                    $('#facility-filter-facility-service-filter-data').empty();
                },
                success: function (response) {
                    response.forEach(element => {
                        let $optionAttr = $('<option></option>').attr('value', element['SR_Service']).text(element['SR_Service']);
                        if (selected.includes(element['SR_Service'])) {
                            $optionAttr.attr('selected', true);
                        }
                        $('#facility-filter-facility-service-filter-data').append(
                            $optionAttr
                        );
                    });
                    $('#facility-filter-facility-service-filter-data').trigger("chosen:updated");
                    that.applyChosenA11y('facility-filter-facility-service-filter-data');
                    resolve();
                },
                error: function (xhr, status, error) {
                    reject(error);
                }
            });
        });
    },
    /**
     * Apply facility filter
    */
    applyFacilityFilter: function () {
        this.setContentToggle(true);
        let that = this; // NOSONAR javascript:S7740
        that.facilityTable.search('').columns().search('');

        let orCondition = $('#filter-option-or').is(":checked");
        let hasActiveFilters = false;

        // Check if any filters are active
        let activeFilters = {
            area: $('#facility-filter-facility-area-filter-data').val() != '',
            name: $('#filter_facility_name_data').val() != '',
            status: $('#filter_facility_status_data').val() != '',
            providerType: $('#facility-filter-provider-type-filter-data').val() != '',
            providerName: $('#filter_facility_provider_name_data').val() != '',
            type: $('#facility-filter-facility-type-filter-data').val() != '',
            subtype: $('#facility-filter-facility-sub-type-filter-data').val() != '',
            location: $('#filter_facility_location_data').val() != '',
            bookingType: $('#facility-filter-default-booking-type-filter-data').val() != '',
            capacity: $('#filter_facility_capacity_data').val() != '',
            accessible: $('#filter_facility_accessible_data').val() != '',
            service: $('#facility-filter-facility-service-filter-data').val() != '',
            equipment: $('#facility-filter-facility-equipment-filter-data').val() != ''
        };

        hasActiveFilters = Object.values(activeFilters).some(filter => filter);

        if (hasActiveFilters && orCondition) {
            // Single search function for OR condition
            that.facilityTable.search(function (settings, data, dataIndex) {
                let matches = [];

                // Handle type and subtype as a group when both are active
                if (activeFilters.type && activeFilters.subtype) {
                    let typeMatch = that.filterDataByConditionSelect(data[7], $('#facility-filter-facility-type-filter-condition').val(), $('#facility-filter-facility-type-filter-data').val().join(','));
                    let subtypeMatch = that.filterDataByConditionSelect(data[8], $('#facility-filter-facility-sub-type-filter-condition').val(), $('#facility-filter-facility-sub-type-filter-data').val().join(','));
                    // Both type and subtype must match for this group
                    matches.push(typeMatch && subtypeMatch);
                } else {
                    // Apply type or subtype filter independently when only one is selected
                    if (activeFilters.type) {
                        let match = that.filterDataByConditionSelect(data[7], $('#facility-filter-facility-type-filter-condition').val(), $('#facility-filter-facility-type-filter-data').val().join(','));
                        matches.push(match);
                    }
                    if (activeFilters.subtype) {
                        let match = that.filterDataByConditionSelect(data[8], $('#facility-filter-facility-sub-type-filter-condition').val(), $('#facility-filter-facility-sub-type-filter-data').val().join(','));
                        matches.push(match);
                    }
                }

                // Check other filters
                if (activeFilters.area) {
                    let match = that.filterDataByConditionSelect(data[3], $('#facility-filter-facility-area-filter-condition').val(), $('#facility-filter-facility-area-filter-data').val().join(','));
                    matches.push(match);
                }
                if (activeFilters.name) {
                    let match = that.filterDataByConditionText(data[1], $('#filter_facility_name_condition').val(), $('#filter_facility_name_data').val());
                    matches.push(match);
                }
                if (activeFilters.status) {
                    let match = that.filterDataByConditionSelect(data[2], $('#filter_facility_status_condition').val(), $('#filter_facility_status_data').val().join(','));
                    matches.push(match);
                }
                if (activeFilters.providerType) {
                    let match = that.filterDataByConditionSelect(data[5], $('#facility-filter-provider-type-filter-condition').val(), $('#facility-filter-provider-type-filter-data').val().join(','));
                    matches.push(match);
                }
                if (activeFilters.providerName) {
                    let match = that.filterDataByConditionText(data[6], $('#filter_facility_provider_name_condition').val(), $('#filter_facility_provider_name_data').val());
                    matches.push(match);
                }
                if (activeFilters.location) {
                    let match = that.filterDataByConditionText(data[10], $('#filter_facility_location_condition').val(), $('#filter_facility_location_data').val());
                    matches.push(match);
                }
                if (activeFilters.bookingType) {
                    let match = that.filterDataByConditionSelect(data[11], $('#facility-filter-default-booking-type-filter-condition').val(), $('#facility-filter-default-booking-type-filter-data').val().join(','));
                    matches.push(match);
                }
                if (activeFilters.capacity) {
                    let match = that.filterDataByConditionText(data[12], $('#filter_facility_capacity_condition').val(), $('#filter_facility_capacity_data').val());
                    matches.push(match);
                }
                if (activeFilters.accessible) {
                    let match = that.filterDataByConditionSelect(data[13], $('#filter_facility_accessible_condition').val(), $('#filter_facility_accessible_data').val().join(','));
                    matches.push(match);
                }
                if (activeFilters.service) {
                    let match = that.filterDataByConditionSelect(data[14], $('#facility-filter-facility-service-filter-condition').val(), $('#facility-filter-facility-service-filter-data').val().join(','));
                    matches.push(match);
                }
                if (activeFilters.equipment) {
                    let match = that.filterDataByConditionSelect(data[15], $('#facility-filter-facility-equipment-filter-condition').val(), $('#facility-filter-facility-equipment-filter-data').val().join(','));
                    matches.push(match);
                }

                // Return true if ANY filter matches for OR Condition
                return matches.some(match => match);
            });
        } else if (hasActiveFilters) {
            // Individual column searches for AND Condition
            if (activeFilters.area) {
                that.facilityTable.column(3).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#facility-filter-facility-area-filter-condition').val(), $('#facility-filter-facility-area-filter-data').val().join(','));
                });
            }
            if (activeFilters.name) {
                that.facilityTable.column(1).search(function (value) {
                    return that.filterDataByConditionText(value, $('#filter_facility_name_condition').val(), $('#filter_facility_name_data').val());
                });
            }
            if (activeFilters.status) {
                that.facilityTable.column(2).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#filter_facility_status_condition').val(), $('#filter_facility_status_data').val().join(','));
                });
            }
            if (activeFilters.providerType) {
                that.facilityTable.column(5).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#facility-filter-provider-type-filter-condition').val(), $('#facility-filter-provider-type-filter-data').val().join(','));
                });
            }
            if (activeFilters.providerName) {
                that.facilityTable.column(6).search(function (value) {
                    return that.filterDataByConditionText(value, $('#filter_facility_provider_name_condition').val(), $('#filter_facility_provider_name_data').val());
                });
            }
            if (activeFilters.type) {
                that.facilityTable.column(7).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#facility-filter-facility-type-filter-condition').val(), $('#facility-filter-facility-type-filter-data').val().join(','));
                });
            }
            if (activeFilters.subtype) {
                that.facilityTable.column(8).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#facility-filter-facility-sub-type-filter-condition').val(), $('#facility-filter-facility-sub-type-filter-data').val().join(','));
                });
            }
            if (activeFilters.location) {
                that.facilityTable.column(10).search(function (value) {
                    return that.filterDataByConditionText(value, $('#filter_facility_location_condition').val(), $('#filter_facility_location_data').val());
                });
            }
            if (activeFilters.bookingType) {
                that.facilityTable.column(11).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#facility-filter-default-booking-type-filter-condition').val(), $('#facility-filter-default-booking-type-filter-data').val().join(','));
                });
            }
            if (activeFilters.capacity) {
                that.facilityTable.column(12).search(function (value) {
                    return that.filterDataByConditionText(value, $('#filter_facility_capacity_condition').val(), $('#filter_facility_capacity_data').val());
                });
            }
            if (activeFilters.accessible) {
                that.facilityTable.column(13).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#filter_facility_accessible_condition').val(), $('#filter_facility_accessible_data').val().join(','));
                });
            }
            if (activeFilters.service) {
                that.facilityTable.column(14).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#facility-filter-facility-service-filter-condition').val(), $('#facility-filter-facility-service-filter-data').val().join(','));
                });
            }
            if (activeFilters.equipment) {
                that.facilityTable.column(15).search(function (value) {
                    return that.filterDataByConditionSelect(value, $('#facility-filter-facility-equipment-filter-condition').val(), $('#facility-filter-facility-equipment-filter-data').val().join(','));
                });
            }
        }

        that.filterArchivedShowAll();

        if (hasActiveFilters) {
            $('.clearFilterIcon').show();
        } else {
            $('.clearFilterIcon').hide();
        }
        if ($('#facility-filter-content').is(":visible")) {
            $("#facility-filter-trigger-dropdown").trigger("click");
        }
        //Store filter settings in browser
        that.storeFilterSettings();
    },
    /**
     * Filter data text
     * @param {*} rowData
     * @param {*} condition
     * @param {*} searchData
     * @returns
     */
    filterDataByConditionText: function (rowData, condition, searchData) {
        let searchDataArray = searchData.split(',').map(s => s.trim());
        const numericOperators = ["<", ">", "<=", ">=", "!=", "="];
        if (numericOperators.includes(condition)) {
            let rowNum = parseFloat(rowData);
            let searchNum = parseFloat(searchData);
            switch (condition) {
                case "<":
                    return rowNum < searchNum;
                case ">":
                    return rowNum > searchNum;
                case "<=":
                    return rowNum <= searchNum;
                case ">=":
                    return rowNum >= searchNum;
                case "=":
                    return rowNum === searchNum;
                case "!=":
                    return rowNum !== searchNum;
            }
        }
        let containsMatch = searchDataArray.some(searchD => new RegExp(searchD, "i").test(rowData));
        let notContainsMatch = searchDataArray.every(searchD => !new RegExp(searchD, "i").test(rowData));
        let exactMatch = searchDataArray.every(searchD =>
            rowData.toLowerCase() == searchD.toLowerCase()
        );
        let exactSomeMatch = searchDataArray.some(searchD =>
            rowData.toLowerCase() == searchD.toLowerCase()
        );

        let matchFound = true;
        //filter type
        if (condition === "*" && !containsMatch) {
            matchFound = false;
        } else if (condition === "!" && !notContainsMatch) {
            matchFound = false;
        } else if (condition === ";" && !(exactSomeMatch)) {
            matchFound = false;
        } else if (condition === "__AND__" && !exactMatch) {
            matchFound = false;
        }
        return matchFound;
    },
    /**
    * Filter data select options
    * @param {*} rowData
    * @param {*} condition
    * @param {*} searchData
    * @returns
    */
    filterDataByConditionSelect: function (rowData, condition, searchData) {
        let searchDataArray = searchData.split(',').map(s => s.trim().toLowerCase());
        let rowDataArray = rowData.split(',').map(s => s.trim().toLowerCase());
        let exactMatch = searchDataArray.every(searchD =>
            rowDataArray.includes(searchD)
        );
        let exactSomeMatch = searchDataArray.some(searchD =>
            rowDataArray.includes(searchD)
        );
        let matchFound = true;
        if (condition === "*" && !exactSomeMatch) {
            matchFound = false;
        } else if (condition === "!" && exactSomeMatch) {
            matchFound = false;
        } else if (condition === ";" && !exactSomeMatch) {
            matchFound = false;
        } else if (condition === "__AND__" && !exactMatch) {
            matchFound = false;
        }
        return matchFound;
    },
    /**
     * Save filter
     */
    saveFacilityFilter: function (privacyType) {
        let that = this; // NOSONAR javascript:S7740
        let filterData = that.fomatFormData($("#facility-filter-form").serializeArray());
        filterData['facility_filter_privacy_type'] = privacyType;
        delete filterData['_token'];
        $.ajax({
            url: that.filterSaveUrl,
            beforeSend: function () {
                $('.validation-error-text').remove();
                $('#facility-filter-private-save-btn, #facility-filter-public-save-btn').prop('disabled', true);
            },
            data: {
                'filterData': filterData,
                'filterName': $('input[name="facility_filter_new_name"]').val(),
                'filterPrivacyType': privacyType,
                'filterId': $('#facility_filter_saved_filter_list option:selected').val(),
                'filterType': 'facility',
                '_token': $('#facility-filter-form input[name="_token"]').val()
            },
            method: 'POST',
            success: function (response) {
                toastr.success(response['message']);
                that.loadSavedFilter(response['filterId']);
                $('#facility-filter-private-save-btn, #facility-filter-public-save-btn').prop('disabled', false);
            },
            error: function (xhr, status, error) {
                $('.validation-error-text').remove();
                let data = JSON.parse(xhr.responseText);
                errors = data.errors;
                Object.keys(errors).forEach((key) => {
                    errors[key].forEach((message) => {
                        if (key == 'filterName') {
                            $('input[name="facility_filter_new_name"]').after('<br class="validation-error-text">', $('<span class="validation-error-text"></span>').text(message));
                        }
                    });
                });
                $('#facility-filter-private-save-btn, #facility-filter-public-save-btn').prop('disabled', false);
            },
            complete: function () {
            }
        });
    },
    /**
     * Delete filter
     */
    deleteFilter: function (selectedFilter) {
        let that = this; // NOSONAR javascript:S7740
        if (selectedFilter == '') {
            toastr.warning('Select a saved filter');
        }
        $.ajax({
            url: that.deleteSavedFilterUrl.replace(':filter', selectedFilter),
            beforeSend: function () {
            },
            method: 'GET',
            success: function (response) {
                toastr.success(response[0]);
                that.clearFilter();
                location.reload();
            },
            error: function (xhr, status, error) {
            },
            complete: function () {
            }
        });
    },
    /*
    * Format form data
    */
    fomatFormData: function (data) {
        let formatedData = {};
        data.forEach(function (currentValue, index) {
            if (currentValue['name'].includes('[]')) { //For array of data
                let elementName = currentValue['name'].replace("[]", "");
                if (formatedData[elementName] == undefined) {
                    formatedData[elementName] = [currentValue['value']];
                } else {
                    formatedData[elementName].push(currentValue['value']);
                }
            } else {
                formatedData[currentValue['name']] = currentValue['value'];
            }
        });
        return formatedData
    },
    /**
     * Load list of saved filter
     */
    loadSavedFilter: async function (selected = '') {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            $.ajax({
                url: that.listSavedFilterUrl,
                beforeSend: function () {
                },
                data: {
                    'filterPrivacyType': $('#facility_filter_privacy_type').val(),
                    'filterType': 'facility'
                },
                method: 'GET',
                success: async function (response) {
                    $('#facility_filter_saved_filter_list').empty();
                    $('#facility_filter_saved_filter_list').append($('<option></option>').val('').html('Select Filter'));
                    Object.keys(response).forEach((key) => {
                        let $option = $('<option></option>').attr({
                            'value': response[key]['FLR_FilterID'],
                            'data-filter-json': response[key]['FLR_FilterData_JSON']
                        }).text(response[key]['FLR_FilterName']);
                        if (selected == response[key]['FLR_FilterID']) {
                            $option.attr('selected', true);
                        }
                        $('#facility_filter_saved_filter_list').append(
                            $option
                        );
                    });
                    if (selected != '') { //Load saved filter
                        let $selectFilter = $("#facility_filter_saved_filter_list option:selected");
                        if (typeof $selectFilter.attr("data-filter-json") != "undefined") {
                            let filterJson = $selectFilter.attr("data-filter-json");
                            await that.applyExistingFilter(JSON.parse(filterJson), true);
                            that.storeFilterSettings();
                            $('#facility-filter-go-btn').trigger("click");
                        }
                    }
                    resolve();
                },
                error: function (xhr, status, error) {
                    reject(error);
                },
                complete: function () {
                }
            });
        });
    },
    /**
     * Populate the filter
     */
    applyExistingFilter: async function (filterData, remainVisible = false) {
        let that = this; // NOSONAR javascript:S7740
        var filterTypes = [];
        var filterSubTypes = [];
        that.clearFilterFields();
        for (let key of Object.keys(filterData)) {
            let $ele = $('[name="' + key + '"]').length > 0 ? $('[name="' + key + '"]') : $('[name="' + key + '[]"]');

            if (['facility_filter_saved_filter_list', 'facility_filter_privacy_type'].includes(key)) {
                continue;
            }

            if ($($ele).attr('type') == 'radio') {
                $('input[name="' + key + '"][value="' + filterData[key] + '"]').prop('checked', true);
            } else if ($($ele).is('input')) {
                $ele.val(filterData[key]);
            } else if ($($ele).is('select')) {
                switch (key) {
                    case 'filter_facility_service_data':
                        await that.setFilterSerivceSelect(filterData[key]);
                        break;
                    case 'filter_facility_area_data':
                        await that.setFilterAreaSelect(filterData[key]);
                        break;
                    case 'filter_facility_type_data':
                        filterTypes = filterData[key] != null ? filterData[key] : [];
                        break;
                    case 'filter_facility_sub_type_data':
                        filterSubTypes = filterData[key] != null ? filterData[key] : [];
                        break;
                    case 'filter_facility_equipment_data':
                        await that.setFilterEquipmentSelect(filterData[key]);
                        break;
                    default:
                        $($ele).val(filterData[key]).trigger("chosen:updated");
                        if ($($ele).attr('id')) { that.applyChosenA11y($($ele).attr('id')); }
                        break;
                }
            }
        }
        if (filterTypes.length > 0) {
            await that.setFilterTypeSelect(filterTypes, filterSubTypes);
        }
        if (!remainVisible) {
            that.applyFacilityFilter();
        }
    },
    /**
     * On page refresh load previous filters
     */
    loadPreviousFilter: async function () {
        let that = this; // NOSONAR javascript:S7740
        let filterData = JSON.parse(localStorage.getItem(that.facilityFilterLocalStorageName));
        if (filterData == null) {
            that.setContentToggle(false);
            return;
        }
        that.setContentToggle(true);
        if (filterData["saved_filter_id"].length > 0) {
            $("#facility_filter_privacy_type").val(
                filterData["filter_privacy_type"]
            );
            if ($("#facility_filter_privacy_type").val() != "") {
                await that.loadSavedFilter(filterData["saved_filter_id"]);
            } else {
                that.setContentToggle(false);
            }
        } else {
            await that.applyExistingFilter(filterData["filter_data"]);
        }
    },
    /**
     * Store browser settings
     */
    storeFilterSettings: function () {
        let that = this; // NOSONAR javascript:S7740
        let data = {};
        data['filter_privacy_type'] = $('#facility_filter_privacy_type').val();
        data['saved_filter_id'] = $('#facility_filter_saved_filter_list').val();
        data['filter_data'] = that.fomatFormData($("#facility-filter-form").serializeArray());
        localStorage.setItem(that.facilityFilterLocalStorageName, JSON.stringify(data));
    },
    /**
     * Clear the fields
     */
    clearFilterFields: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#filter_facility_name_data').val('');
        $('#facility-filter-provider-type-filter-data').val('').trigger("chosen:updated");
        $('#filter_facility_provider_name_data').val('');
        $('#filter_facility_status_data').val('').trigger("chosen:updated");
        $('#facility-filter-default-booking-type-filter-data').val('').trigger("chosen:updated");
        $('#facility-filter-facility-type-filter-data').val('').trigger("chosen:updated");
        $('#facility-filter-facility-sub-type-filter-data').val('').trigger("chosen:updated");
        $('#filter_facility_location_data').val('');
        $('#filter_facility_capacity_data').val('');
        $('#facility-filter-facility-service-filter-data').val('').trigger("chosen:updated");
        $('#facility-filter-facility-equipment-filter-data').val('').trigger("chosen:updated");
        $('#facility-filter-facility-area-filter-data').val('').trigger("chosen:updated");
        $('#filter_facility_accessible_data').val('').trigger("chosen:updated");
        $('#facility_filter_new_name').val('').trigger("chosen:updated");

        // Accessibility: apply ARIA labelling for Chosen selects (reset)
        [
            'facility-filter-facility-area-filter-data',
            'facility-filter-provider-type-filter-data',
            'facility-filter-default-booking-type-filter-data',
            'facility-filter-facility-type-filter-data',
            'facility-filter-facility-sub-type-filter-data',
            'facility-filter-facility-service-filter-data',
            'facility-filter-facility-equipment-filter-data',
            'filter_facility_status_data',
            'filter_facility_accessible_data'
        ].forEach(function (id) {
            that.applyChosenA11y(id);
        });

    },
    /**
     * hide loader and show content
     */
    setContentToggle: function (isLoading) {
        var that = this; // NOSONAR javascript:S7740
        if (isLoading) {
            $('#facility-loader').show();
            $('#facility-tabs-facility-catalogue').hide();
        } else {
            $('#facility-loader').hide();
            $('#facility-tabs-facility-catalogue').show();
            setTimeout(function () {
                that.facilityTable.columns.adjust();
                // Find all reset buttons and add aria-labels
                $('#facility-list-table').find('button.yadcf-filter-reset-button').each(function () {
                    let $btn = $(this);
                    let $th = $btn.closest('th');
                    let $parentTr = $th.parent('tr');
                    let thIndex = $parentTr.find('th').index($th);
                    if (clearButtonLabels[thIndex]) {
                        $btn.attr('aria-label', clearButtonLabels[thIndex]);
                        $btn.attr('title', clearButtonLabels[thIndex]); // fallback
                        // Expose the clear button only on keyboard focus
                        $btn.off('focusin.srfix focusout.srfix');
                        $btn.attr('aria-hidden', 'true');
                        if (!$btn.attr('tabindex')) { $btn.attr('tabindex', '0'); }
                        $btn.on('focusin.srfix', function () { $(this).removeAttr('aria-hidden'); });
                        $btn.on('focusout.srfix', function () { $(this).attr('aria-hidden', 'true'); });
                    }
                });
            }, 50);
        }
    }
}

module.exports = FacilityFilter;