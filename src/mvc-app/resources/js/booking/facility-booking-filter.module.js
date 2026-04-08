/**
 * Facility booking filter JavaScript
 *
 * @param {Object} p parameters
 */
var FacilityBookingFilter = function (p) {
    this.getFacilityBookingsUrl = p.getFacilityBookingsUrl;
    this.facilityBookingInstance = p.facilityBookingInstance;
    this.token = p.token;
    this.facilityBookingSettingStorageName = 'facility_booking_setting_local_storage';
    this.facilityBookingFilterLocalStorageName = 'facility_booking_filter_setting_local_storage';
    this.facilityBookingAdminFilterSettingStorageName = 'facility_booking_admin_filter_setting';
    this.userSetting = p.userSetting;
    this.filterTypeDataUrl = p.filterTypeDataUrl
    this.filterSubTypeDataUrl = p.filterSubTypeDataUrl;
    this.filterServiceDataUrl = p.filterServiceDataUrl;
    this.filterEquipmentDataUrl = p.filterEquipmentDataUrl;
    this.filterSaveUrl = p.filterSaveUrl;
    this.listSavedFilterUrl = p.listSavedFilterUrl;
    this.deleteSavedFilterUrl = p.deleteSavedFilterUrl;
    this.actionStaticSettings = {
        'prep': {
            'backgroundColour': 'fafa2a'
        },
        'set-up': {
            'backgroundColour': '78dbff'
        },
        'recording': {
            'backgroundColour': '77f25e'
        },
        'transmission': {
            'backgroundColour': 'ff4747'
        },
        'clear-down': {
            'backgroundColour': 'cfd0d1'
        },
        'training': {
            'backgroundColour': 'ff7ae9'
        },
        'maintenance/engineering': {
            'backgroundColour': 'a3a3a3'
        },
        'maintenance': {
            'backgroundColour': 'a3a3a3'
        },
        'engineering': {
            'backgroundColour': 'a3a3a3'
        }
    };
    this._activeBookingsXhr = null;
    this._bookingsRequestSeq = 0;


    this.__pendingScrollSnapshots = {};
    this.__lastUserScrollSnapshot = null;


    this.init();
};

FacilityBookingFilter.prototype = {
    /**
     * Accessibility: ensure Chosen widgets announce their associated <label>.
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

        var $searchInput = $container.find('.chosen-search input').first();
        if ($searchInput.length) {
            $searchInput.attr({
                'aria-labelledby': labelId,
                'role': 'searchbox',
                'aria-autocomplete': 'list'
            });
            $searchInput.removeAttr('aria-label');
        }

        $select.off('.chosenA11y_' + selectId);
        $select.on('chosen:showing_dropdown.chosenA11y_' + selectId, function () {
            $control.attr('aria-expanded', 'true');
        });
        $select.on('chosen:hiding_dropdown.chosenA11y_' + selectId, function () {
            $control.attr('aria-expanded', 'false');
        });
    },
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        that.toggleFilterVisibilty();
        that.initFilterKeyboardSupport();
        that.initFacilityFilterElements();
        that.timelineSettings();
        that.buildWeeklyViewTimeLine();
        that.updateTimelineMinHeight();
        that.enableAutoHorizontalRefit();
    },
    /**
     * Time line settings
     */
    timelineSettings: function () {
        let that = this; // NOSONAR javascript:S7740

        //Load settings
        that.setSettings();

        //Calender
        $('#view-date-calender-icon').click(function () {
            $("#view-date-calender").focus();
        });
        $('#view-date-calender').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            maxDate: '+5y',
            dateFormat: 'yy-mm-dd',
            beforeShow: function (input, inst) {
                inst.dpDiv.addClass('z-index-14');
            },
            onClose: function (dateText, inst) {
                inst.dpDiv.removeClass('z-index-14');
            }
        });
        $('#view-date-calender, #view-days').on('change', function () {
            if ($('.clearFilterIcon').is(':visible')) {
                that.storeFilterSettings();
            }
            that.reloadTimeline();
        });
        that.initNavigationButton();

        $('#include-cancelled-booking, input[name="show_only_booking_status[]"]').on('click', function () {
            $('#facility-booking-filter-apply-btn')
                .prop('disabled', true)
                .addClass('disabled')
                .css('pointer-events', 'none');
            if ($('#include-cancelled-booking').is(':checked') ||
                $('input[name="show_only_booking_status[]"]:checked').length > 0) {
                $('.clearFilterIcon').show();
            } else {
                $('.clearFilterIcon').hide();
            }
            that.reloadTimeline();
            $('#facility-booking-filter-apply-btn')
                .prop('disabled', false)
                .removeClass('disabled')
                .css('pointer-events', 'auto');
        });
    },
    /**
     * Reload timeline
     */
    reloadTimeline: function () {
        let that = this; // NOSONAR javascript:S7740
        try {
            const timelineEl = document.getElementById('weekly-view-time-line-container');
            const bookingWrapEl = document.getElementById('booking-weekly-view-container');

            const makeSnapshot = (el, targetName) => {
                const maxTop = Math.max(0, (el.scrollHeight || 0) - (el.clientHeight || 0));
                const maxLeft = Math.max(0, (el.scrollWidth || 0) - (el.clientWidth || 0));
                const topPx = el.scrollTop || 0;
                const leftPx = el.scrollLeft || 0;
                return {
                    target: targetName,
                    ts: Date.now(),
                    topRatio: maxTop > 0 ? (topPx / maxTop) : 0,
                    leftRatio: maxLeft > 0 ? (leftPx / maxLeft) : 0,
                    topPx,
                    leftPx
                };
            };

            const reqIdHint = (that._bookingsRequestSeq || 0) + 1;
            let snapshot = null;

            if (that.__suppressScrollStore && that.__lastUserScrollSnapshot) {
                snapshot = Object.assign({}, that.__lastUserScrollSnapshot);
            } else {
                // Normal capture from actual scroller
                if (timelineEl && ((timelineEl.scrollHeight > timelineEl.clientHeight) || (timelineEl.scrollWidth > timelineEl.clientWidth))) {
                    snapshot = makeSnapshot(timelineEl, 'weekly-view-time-line-container');
                } else if (bookingWrapEl && ((bookingWrapEl.scrollHeight > bookingWrapEl.clientHeight) || (bookingWrapEl.scrollWidth > bookingWrapEl.clientWidth))) {
                    snapshot = makeSnapshot(bookingWrapEl, 'booking-weekly-view-container');
                } else {
                    // fallback
                    snapshot = {
                        target: 'weekly-view-time-line-container',
                        ts: Date.now(),
                        topRatio: 0,
                        leftRatio: 0,
                        topPx: 0,
                        leftPx: 0
                    };
                }
            }

            snapshot.reqId = reqIdHint;
            that.__lastUserScrollSnapshot = Object.assign({}, snapshot);
            that.__pendingScrollSnapshots[reqIdHint] = snapshot;
            localStorage.setItem('fb_scroll_pending_restore_' + reqIdHint, JSON.stringify(snapshot));
            that.__pendingScrollSnapshot = snapshot;
            localStorage.setItem('fb_scroll_pending_restore', JSON.stringify(snapshot));
            that.__suppressScrollStore = true;
        } catch (e) { /* ignore */ }

        that.buildWeeklyViewTimeLine();
        that.storeSettingsLocalStorage();
        that.initNavigationButton();
    },
    /**
     * Init navigation button based on settings
     */
    initNavigationButton: function () {
        // handle enter event
        document.getElementById("previous-day-btn")
        .addEventListener("click", function () {});

        document.getElementById("next-day-btn")
        .addEventListener("click", function () {});
        let that = this; // NOSONAR javascript:S7740
        //Update button text
        $('#previous-text').text($('#view-days').val() == 1 ? 'Previous Day' : 'Previous Week');
        $('#next-text').text($('#view-days').val() == 1 ? 'Next Day' : 'Next Week');

        //Next prev navigation
        $('#previous-text').parent().off();
        let days = that.getWeekDates();
        $('#previous-text').parent().on('click', function () {
            if ($('#view-days').val() == 1) {
                let previousDay = moment(days[0]).add(-1, 'days');
                $('#view-date-calender').val(previousDay.format('YYYY-MM-DD')).trigger("change");
            } else {
                let previousWeekDay = moment(days.at(0)).add(-1, 'days');
                $('#view-date-calender').val(previousWeekDay.format('YYYY-MM-DD')).trigger("change");
            }
        });
        $('#next-text').parent().off();
        $('#next-text').parent().on('click', function () {
            if ($('#view-days').val() == 1) {
                let tomorrow = moment(days[0]).add(1, 'days');
                $('#view-date-calender').val(tomorrow.format('YYYY-MM-DD')).trigger("change");
            } else {
                let nextWeekDay = moment(days.at(-1)).add(1, 'days');
                $('#view-date-calender').val(nextWeekDay.format('YYYY-MM-DD')).trigger("change");
            }
        });
    },
    /**
     * Store settings
     */
    storeSettingsLocalStorage: function () {
        let that = this; // NOSONAR javascript:S7740
        localStorage.setItem(that.facilityBookingSettingStorageName, JSON.stringify(
            {
                'selected_date': $('#view-date-calender').val(),
                'view_days': $('#view-days').val(),
                'show_canceled': $('#include-cancelled-booking').is(':checked'),
                'show_only_booking_status_new': $('#show-only-new-booking').is(':checked'),
                'show_only_booking_status_pending': $('#show-only-pending-booking').is(':checked'),
                'show_only_booking_status_confirmed': $('#show-only-confirmed-booking').is(':checked'),
                'show_only_booking_status_declined': $('#show-only-declined-booking').is(':checked')
            }
        )); //Store for page reload
    },
    /**
     * Get settings from local storage
     */
    setSettings: function () {
        let that = this; // NOSONAR javascript:S7740
        let settings = JSON.parse(localStorage.getItem(that.facilityBookingSettingStorageName));
        let facilityBookingAdminSettings = JSON.parse(localStorage.getItem(that.facilityBookingAdminFilterSettingStorageName));
        if (settings == null && facilityBookingAdminSettings == null) {
            return;
        }
        if (facilityBookingAdminSettings != null) { // Page loaded from facility booking administrator page
            settings = [];
            settings['selected_date'] = facilityBookingAdminSettings['date'];
            settings['view_days'] = 1;
            if (facilityBookingAdminSettings['tab_type'] === 'cancelled') {
                settings['show_canceled'] = true;
            }
            localStorage.removeItem(that.facilityBookingSettingStorageName);
        }
        $('#view-date-calender').val(settings['selected_date'] ?? '');
        $('#view-days').val(settings['view_days'] ?? '');
        $('#include-cancelled-booking').prop('checked', settings['show_canceled'] ?? false);
        $('#show-only-new-booking').prop('checked', settings['show_only_booking_status_new'] ?? false);
        $('#show-only-pending-booking').prop('checked', settings['show_only_booking_status_pending'] ?? false);
        $('#show-only-confirmed-booking').prop('checked', settings['show_only_booking_status_confirmed'] ?? false);
        $('#show-only-declined-booking').prop('checked', settings['show_only_booking_status_declined'] ?? false);
    },
    /**
     * Disable the "full rebuild on resize/zoom" wiring created in timelineSettings().
     * This prevents reloadTimeline() -> buildWeeklyViewTimeLine() from firing on resize.
     */
    disableResizeTriggeredRebuild: function () {
        const that = this; // NOSONAR javascript:S7740

        try { $(window).off('resize.fbFullRerender'); } catch (e) { /* ignore */ }

        try { clearTimeout(that.__fbResizeRerenderT); } catch (e) { /* ignore */ }

        try {
            if (window.visualViewport && that.__fbVVResizeHandler) {
                window.visualViewport.removeEventListener('resize', that.__fbVVResizeHandler);
            }
        } catch (e) { /* ignore */ }

        try {
            if (that.__fbResizeObserver) {
                that.__fbResizeObserver.disconnect();
                that.__fbResizeObserver = null;
            }
        } catch (e) { /* ignore */ }
    },
    /**
     * One-call helper: disables rebuild-on-resize and ensures horizontal refit is enabled.
     * Safe to call multiple times.
     */
    activateWidthOnlyResizeBehavior: function () {
        const that = this; // NOSONAR javascript:S7740
        that.disableResizeTriggeredRebuild();
        if (typeof that.enableAutoHorizontalRefit === 'function') {
            that.enableAutoHorizontalRefit();
        }
    },
    /**
     * Build Weekly View Timeline
     *
     * @returns
     */
    buildWeeklyViewTimeLine: function () {
        let that = this; // NOSONAR javascript:S7740
        const viewDays = parseInt($('#view-days').val(), 10);
        const isDaily = viewDays === 1;
        const isWeekly = viewDays > 1;
        const weekDates = that.getWeekDates();

        const DAILY_MARKER_SRC = "/images/hourpointer.png";

        // Scale factor for smaller image
        const POINTER_SCALE = 1;

        const hourW = 75; // fallback only
        const weeklyScale = 0.125; // fallback only
        const fallbackNameW = 260;

        // SR-only date range
        try {
            const srRange = document.getElementById('weekly-view-time-line-date-range');
            const dateRange = document.getElementById('date-range-section');
            if (srRange && weekDates && weekDates.length) {
                const first = weekDates[0];
                const last = weekDates.at(-1);
                srRange.textContent = (first === last)
                    ? ('Selected date: ' + that.convertToDDMMYYYY(first))
                    : ('Selected range: ' + that.convertToDDMMYYYY(first) + ' to ' + that.convertToDDMMYYYY(last));
                dateRange.textContent = (first === last)
                    ? (that.convertToDDMMYYYY(first))
                    : (that.convertToDDMMYYYY(first) + ' to ' + that.convertToDDMMYYYY(last));
            }
        } catch (e) { /* ignore */ }

        function getCurrentNameW() {
            const el = document.getElementById('weekly-view-time-line-table');
            if (el) {
                const v = parseFloat(getComputedStyle(el).getPropertyValue('--nameW'));
                if (isFinite(v) && v > 0) return v;
            }
            return fallbackNameW;
        }

        function computeSizes() {
            const containerEl = document.getElementById('weekly-view-time-line-container');
            const availableW = containerEl ? containerEl.clientWidth : 0;
            const nameW = getCurrentNameW();
            const dayCount = Math.max(1, weekDates.length);
            const timelineAreaW = Math.max(0, availableW - nameW);

            let timelineW, effectiveHourW;
            if (timelineAreaW > 0) {
                timelineW = timelineAreaW / dayCount;
                effectiveHourW = timelineW / 24;
            } else {
                effectiveHourW = isWeekly ? (hourW * weeklyScale) : hourW;
                timelineW = effectiveHourW * 24;
            }

            effectiveHourW = Math.max(1, effectiveHourW);
            timelineW = Math.max(24, timelineW);

            const tickW = effectiveHourW / 4;
            const hoursPerPeriod = isDaily ? 1 : 6;
            const periodsPerDay = isDaily ? 24 : 4;
            const periodW = effectiveHourW * hoursPerPeriod;
            const tableW = nameW + (timelineW * dayCount);

            return { effectiveHourW, tickW, hoursPerPeriod, periodsPerDay, periodW, timelineW, tableW, nameW, dayCount };
        }

        function syncBlockWidths($grid, sizes) {
            if (!$grid || !$grid.length) return;
            const w = sizes.tableW + 'px';
            $grid.css('width', w);
            $grid.find('#weekly-view-time-line-table-head').css('width', w);
            $grid.find('#weekly-view-time-line-table-body').css('width', w);
            $grid.find('#weekly-view-time-line-table-head [role="row"]').each(function () { this.style.width = w; });
            $grid.find('#weekly-view-time-line-table-body [role="row"]').each(function () { this.style.width = w; });
        }

        function applySizes($grid, sizes) {
            if (!$grid || !$grid.length) return;
            const el = $grid[0];
            el.style.setProperty('--hourW', sizes.effectiveHourW + 'px');
            el.style.setProperty('--tickW', sizes.tickW + 'px');
            el.style.setProperty('--periodW', sizes.periodW + 'px');
            el.style.setProperty('--timelineW', sizes.timelineW + 'px');
            el.style.setProperty('--nameW', sizes.nameW + 'px');
            el.style.setProperty('--tableW', sizes.tableW + 'px');
            syncBlockWidths($grid, sizes);
        }

        // Keep the lines layer aligned with timeline width/name column
        function syncLinesLayer($linesLayer, sizes) {
            if (!$linesLayer || !$linesLayer.length) return;
            $linesLayer.css({
                left: sizes.nameW + 'px',
                width: (sizes.tableW - sizes.nameW) + 'px'
            });
        }

        const sizes = computeSizes();
        const periodLabels = that.splitDayIntoIntervals(sizes.periodsPerDay);
        const colCount = 1 + (sizes.dayCount * sizes.periodsPerDay);

        // First header row (Date)
        const dateHeaderText = isDaily
            ? that.convertToDDMMYYYY(weekDates[0])
            : (that.convertToDDMMYYYY(weekDates[0]) + ' to ' + that.convertToDDMMYYYY(weekDates.at(-1)));

        // Root ARIA grid
        const $grid = $('<div id="weekly-view-time-line-table" role="grid"></div>').attr({
            'aria-labelledby': 'weekly-view-time-line-grid-label weekly-view-time-line-date-range',
            'aria-colcount': colCount,
            'aria-readonly': 'true'
        });

        $grid.toggleClass('daily-view', isDaily);
        $grid.toggleClass('weekly-view', isWeekly);
        $('#weekly-view-time-line-container').toggleClass('daily-view', isDaily);
        $('#weekly-view-time-line-container').toggleClass('weekly-view', isWeekly);

        applySizes($grid, sizes);

        const $head = $('<div id="weekly-view-time-line-table-head" role="rowgroup"></div>');
        const $body = $('<div id="weekly-view-time-line-table-body" role="rowgroup"></div>');


        // Date header row
        const $dateHeaderRow = $('<div role="row" class="fb-grid-date-header-row"></div>');

        // Left label: fixed to name column width
        $dateHeaderRow.append(
            $('<div role="columnheader" class="weekly-timeline-header weekly-timeline-cell weekly-time-line-name fb-date-header-label"></div>')
                .append($('<span class="thead-corner-label fb-date-label-text"></span>').text('Date'))
                .css({ flex: '0 0 var(--nameW)' })
        );

        // Right cell: keeps header background/borders for the timeline area.
        $dateHeaderRow.append(
            $('<div role="columnheader" class="weekly-timeline-header weekly-timeline-cell fb-date-header-value"></div>')
                .append($('<span class="sr-only"></span>').text(dateHeaderText))
                .css({ flex: '1 1 auto' })
        );

        // Center overlay
        $dateHeaderRow.append(
            $('<div class="fb-date-header-center" aria-hidden="true" role="presentation"></div>')
                .append($('<span class="fb-date-header-center-text"></span>').text(dateHeaderText))
        );

        // $head.append($dateHeaderRow);


        // Lines layer (major + minor)
        const $linesLayer = $('<div class="timeline-lines-layer" aria-hidden="true"></div>');
        syncLinesLayer($linesLayer, sizes);

        // Header row with time labels
        const $timeHeaderRow = $('<div role="row" class="fb-grid-time-header-row"></div>');
        $timeHeaderRow.append(
            $('<div role="columnheader" class="weekly-timeline-header weekly-timeline-cell weekly-time-line-name"></div>')
                .append($('<span class="thead-corner-label"></span>').text('Facility Name'))
                .css({ flex: '0 0 var(--nameW)' })
        );

        weekDates.forEach(dateStr => {
            const $dayHeaderCell = $('<div class="weekly-timeline-header weekly-timeline-cell" role="presentation" style="padding:unset"></div>')
                .css({ flex: '0 0 var(--timelineW)' });
            const $periodDiv = $('<div class="period-div" role="presentation"></div>')
                .css({ display: 'flex', position: 'relative', width: 'var(--timelineW)', overflow: 'visible' });

            periodLabels.forEach(label => {
                $periodDiv.append(
                    $('<div class="booking-period-cell" role="columnheader"></div>')
                        .attr({ 'data-date': dateStr, 'data-period': label, 'aria-label': (isDaily ? label : (that.convertToDDMMYYYY(dateStr) + ' ' + label)) })
                        .append($('<span style="padding-right:4px"></span>').text(label))
                        .css({ flex: '0 0 var(--periodW)' })
                );

                // Major (hour) line
                $linesLayer.append(
                    $('<div class="vertical-line-one" style="position:absolute"></div>')
                        .attr({ 'data-date': dateStr, 'data-period': label })
                );
            });

            if (isDaily) {
                for (let h = 0; h <= 24; h++) {
                    const hh = String(h).padStart(2, '0');
                    const timeStr = (h === 24) ? '24:00' : `${hh}:00`;

                    $periodDiv.append(
                        $('<img alt="" class="daily-hour-pointer" />')
                            .attr('src', DAILY_MARKER_SRC)
                            .attr('data-date', dateStr)
                            .attr('data-time', timeStr)
                            .css({
                                position: 'absolute',
                                left: '0px',
                                top: '100%',
                                marginTop: '2px',
                                transform: `translateX(-50%) scale(${POINTER_SCALE})`,
                                transformOrigin: '50% 100%',
                                zIndex: 10000,
                                pointerEvents: 'none'
                            })
                    );
                }
            }

            $dayHeaderCell.append($periodDiv);
            $timeHeaderRow.append($dayHeaderCell);
        });

        $head.append($timeHeaderRow);

        // Hidden 15-min ticks row (anchors) + minor dotted lines
        const dayInterval = that.splitDayIntoIntervals(96);
        const $tickRow = $('<div class="weekly-day-grid-row" role="row" aria-hidden="true"></div>');
        $tickRow.append($('<div class="weekly-time-line-name" role="presentation"></div>').css({ flex: '0 0 var(--nameW)' }));

        weekDates.forEach(dateStr => {
            const $dayTicksCell = $('<div role="presentation"></div>').css({ flex: '0 0 var(--timelineW)' });
            const $ticksDiv = $('<div class="period-div" role="presentation"></div>')
                .css({ display: 'flex', position: 'relative', width: 'var(--timelineW)', overflow: 'visible' });

            let counter = 1;
            dayInterval.forEach(timeStr => {
                $ticksDiv.append(
                    $('<div class="fifteen-interval-day" role="presentation"></div>')
                        .attr('data-date-time', dateStr + ' ' + timeStr)
                        .css({ flex: '0 0 var(--tickW)', minWidth: 'var(--tickW)' })
                );

                // Minor lines at 15/30/45 boundaries (daily view only)
                counter++;
                if (isDaily && counter !== 5) {
                    $linesLayer.append(
                        $('<div class="vertical-line-two" style="position:absolute"></div>')
                            .attr('data-date-time', dateStr + ' ' + timeStr)
                    );
                } else if (counter === 5) {
                    counter = 1;
                }
            });

            // 24:00 anchor
            $ticksDiv.append(
                $('<div class="fifteen-interval-day end-of-day-tick" role="presentation"></div>')
                    .attr('data-date-time', dateStr + ' 24:00')
                    .css({ flex: '0 0 0', minWidth: '0', width: '0' })
            );

            $dayTicksCell.append($ticksDiv);
            $tickRow.append($dayTicksCell);
        });

        $head.append($tickRow);

        $grid.append($head);
        $grid.append($body);

        // Load bookings
        that.loadFacilityAndBookings();

        const $container = $('#weekly-view-time-line-container');
        $container.empty();
        $container.append($grid);
        $container.append($linesLayer);

        that.initAriaGridKeyboard();

        that.updateNameColumnWidthAndRefitTimeline();
        that.scheduleSetElementCss();
    },

    /**
     * Enable responsive horizontal refit WITHOUT rebuilding / reloading data.
     * Call once after the timeline is built.
     */
    enableAutoHorizontalRefit: function () {
        const that = this; // NOSONAR javascript:S7740
        if (that.__fbAutoRefitEnabled) return;
        that.__fbAutoRefitEnabled = true;

        const schedule = function () {
            clearTimeout(that.__fbAutoRefitT);
            that.__fbAutoRefitT = setTimeout(function () {
                that.refitTimelineWidthOnly();
            }, 60);
        };

        that.__fbAutoRefitSchedule = schedule;

        // Window resize
        $(window).off('resize.fbAutoRefit').on('resize.fbAutoRefit', schedule);

        try {
            if (window.visualViewport) {
                that.__fbVVAutoRefitHandler = function () { schedule(); };
                window.visualViewport.addEventListener('resize', that.__fbVVAutoRefitHandler, { passive: true });
            }
        } catch (e) { /* ignore */ }

        try {
            if (!that.__fbAutoRefitObserver && window.ResizeObserver) {
                that.__fbAutoRefitObserver = new ResizeObserver(function () { schedule(); });
                const c = document.getElementById('weekly-view-time-line-container');
                if (c) that.__fbAutoRefitObserver.observe(c);
            }
        } catch (e) { /* ignore */ }

        // Run once immediately
        schedule();
    },
    /**
     * Recompute ONLY horizontal widths (no rebuild, no AJAX):
     * - Updates CSS vars: --nameW, --timelineW, --hourW, --tickW, --periodW, --tableW
     * - Updates .period-div widths
     * - Updates lines-layer width/left
     * - Then repositions overlays/lines/labels via scheduleSetElementCss()
     */
    refitTimelineWidthOnly: function () {
        const that = this; // NOSONAR javascript:S7740

        const tableEl = document.getElementById('weekly-view-time-line-table');
        const containerEl = document.getElementById('weekly-view-time-line-container');
        if (!tableEl || !containerEl) return;

        const $table = $('#weekly-view-time-line-table');
        const isDaily = $table.hasClass('daily-view');
        const days = that.getWeekDates ? that.getWeekDates() : [];
        const dayCount = Math.max(1, days.length || 1);

        const availableW = containerEl.clientWidth || 0;
        if (availableW <= 0) return;

        const dpr = window.devicePixelRatio || 1;
        const sig = Math.round(availableW) + '@' + dpr + 'd' + dayCount + (isDaily ? 'D' : 'W');
        if (that.__fbLastWidthSig === sig) return;
        that.__fbLastWidthSig = sig;

        let maxNameW = 0;
        $('.booking-timeline-name-td').each(function () {
            maxNameW = Math.max(maxNameW, this.scrollWidth || 0);
        });

        maxNameW = Math.ceil(maxNameW + 12);
        maxNameW = Math.min(300, maxNameW);

        if (!maxNameW || maxNameW < 40) {
            const current = parseFloat(getComputedStyle(tableEl).getPropertyValue('--nameW')) || 260;
            maxNameW = Math.min(300, current);
        }

        maxNameW = Math.min(maxNameW, Math.max(80, availableW - 24)); // leave at least some timeline area

        const timelineAreaW = Math.max(0, availableW - maxNameW);
        const timelineW = (dayCount > 0) ? (timelineAreaW / dayCount) : timelineAreaW;

        const hourW = timelineW / 24;
        const tickW = hourW / 4;
        const hoursPerPeriod = isDaily ? 1 : 6;
        const periodW = hourW * hoursPerPeriod;

        const tableW = availableW;

        tableEl.style.setProperty('--nameW', maxNameW + 'px');
        tableEl.style.setProperty('--timelineW', timelineW + 'px');
        tableEl.style.setProperty('--hourW', hourW + 'px');
        tableEl.style.setProperty('--tickW', tickW + 'px');
        tableEl.style.setProperty('--periodW', periodW + 'px');
        tableEl.style.setProperty('--tableW', tableW + 'px');

        const w = tableW + 'px';
        $table.css('width', w);
        $table.find('#weekly-view-time-line-table-head').css('width', w);
        $table.find('#weekly-view-time-line-table-body').css('width', w);
        $table.find('#weekly-view-time-line-table-head [role="row"]').css('width', w);
        $table.find('#weekly-view-time-line-table-body [role="row"]').css('width', w);

        $('#weekly-view-time-line-table thead .period-div').each(function () {
            this.style.width = timelineW + 'px';
        });

        const $linesLayer = $('#weekly-view-time-line-container').children('.timeline-lines-layer');
        if ($linesLayer.length) {
            $linesLayer.css({
                left: maxNameW + 'px',
                width: Math.max(0, tableW - maxNameW) + 'px'
            });
        }

        that.scheduleSetElementCss();
    },
    /**
     * Set vertical lines
     */
    verticalLineSet: function () {
        var that = this; // NOSONAR javascript:S7740
        that.setMaxHeight();

        const $table = $('#weekly-view-time-line-table');
        const tableEl = $table[0];
        const isDaily = $table.hasClass('daily-view');

        const fullTableH = tableEl
            ? Math.max(tableEl.scrollHeight || 0, tableEl.offsetHeight || 0)
            : 0;

        const $linesLayer = $('#weekly-view-time-line-container').children('.timeline-lines-layer');
        if ($linesLayer.length && fullTableH > 0) {
            $linesLayer.css({
                height: (fullTableH + 2) + 'px',
                top: 0
            });
        }

        function rect(el) { return el ? el.getBoundingClientRect() : null; }
        function px(styleVal) { var n = parseFloat(styleVal); return isFinite(n) ? n : 0; }

        function contentBoxLeftEdge(parentEl) {
            if (!parentEl) return { contentLeft: 0 };
            const cs = getComputedStyle(parentEl);
            const borderL = px(cs.borderLeftWidth);
            const padL = px(cs.paddingLeft);
            const pr = rect(parentEl);
            return { contentLeft: pr ? (pr.left + borderL + padL) : 0 };
        }

        function heightToFullTable(parentEl) {
            if (!parentEl || fullTableH <= 0) return null;
            const topOffset = parentEl.offsetTop || 0;
            return Math.max(0, (fullTableH - topOffset) + 2); // +2px safety
        }

        function leftRelativeToParentEdge($cell, parentEl, edge) {
            if (!$cell || !$cell.length || !parentEl) return null;
            var cellRect = rect($cell[0]);
            var parentRect = rect(parentEl);
            if (!cellRect || !parentRect) return null;
            if (edge === 'left') return (cellRect.left - parentRect.left);
            return (cellRect.right - parentRect.left);
        }

        function findTick(dateStr, timeStr) {
            return $(
                '#weekly-view-time-line-table-head .weekly-day-grid-row ' +
                '.fifteen-interval-day[data-date-time="' + dateStr + ' ' + timeStr + '"]'
            ).first();
        }

        // MAJOR GRID LINES
        $('.vertical-line-one').each(function (_, line) {
            var $line = $(line);
            var date = $line.attr('data-date');
            var period = $line.attr('data-period');

            var $tick = findTick(date, period);
            if (!$tick.length) return;

            var tickRect = rect($tick[0]);
            if (!tickRect) return;

            var parentEl = $line.parent()[0]; // this is .timeline-lines-layer
            if (!parentEl) return;

            var c = contentBoxLeftEdge(parentEl);
            var left = Math.round(tickRect.left - c.contentLeft);
            if (left < 0) left = 0;

            var h = heightToFullTable(parentEl);
            if (h == null) return;

            $line.css({
                left: left + 'px',
                right: 'auto',
                top: 0,
                height: h
            });
        });

        // MINOR GRID LINES (daily only)
        if (isDaily) {
            $('.vertical-line-two').each(function (_, line) {
                var $line = $(line);
                var dt = $line.attr('data-date-time');
                var $tickCell = $('.fifteen-interval-day[data-date-time="' + dt + '"]').first();
                if (!$tickCell.length) return;

                var parentEl = $tickCell.parent()[0];
                var left = leftRelativeToParentEdge($tickCell, parentEl, 'right');
                if (left == null) return;

                var h = heightToFullTable($linesLayer[0] || parentEl);
                if (h == null) return;

                $line.css({
                    left: Math.round(left) + 'px',
                    top: 0,
                    height: h
                });
            });
        }

        // Position hour pointers using the same tick anchors
        if (isDaily) {
            const $pointers = $('#weekly-view-time-line-table .daily-hour-pointer');
            if ($pointers.length) {
                $pointers.each(function () {
                    const img = this;
                    const $img = $(img);

                    const dateStr = $img.attr('data-date');
                    const timeStr = $img.attr('data-time');
                    if (!dateStr || !timeStr) return;

                    // Find the exact tick that the vertical lines are anchored to
                    const $tick = findTick(dateStr, timeStr);
                    if (!$tick.length) return;

                    const tickRect = rect($tick[0]);
                    if (!tickRect) return;

                    const parentEl = img.parentElement;
                    if (!parentEl) return;

                    const c = contentBoxLeftEdge(parentEl);
                    let left = Math.round(tickRect.left - c.contentLeft);
                    if (left < 0) left = 0;

                    $img.css({ left: left + 'px' });
                });
            }
        }
    },
    /**
     * Load facilities and bookings
     *
     * @prarm facilitId If facility id passed only that specific facilty rows will refresh
     */
    loadFacilityAndBookings: function (facilityId = null) {
        let that = this; // NOSONAR javascript:S7740
        let data = that.getFacilityBookingListSetting();
        if (facilityId != null) {
            data['facilityId'] = facilityId;
        }

        // Abort any previous in-flight request
        if (that._activeBookingsXhr && that._activeBookingsXhr.readyState !== 4) {
            try { that._activeBookingsXhr.abort(); } catch (e) { }
        }

        // Only the latest request is allowed to render
        const reqId = ++that._bookingsRequestSeq;

        that._activeBookingsXhr = $.ajax({
            url: that.getFacilityBookingsUrl,
            method: "GET",
            data: data,
            success: function (resp) {
                // Ignore stale responses
                if (reqId !== that._bookingsRequestSeq) return;

                facilityId == null ? that.buildFacilityBooking(resp) : that.refreshFacilityBookingTr(resp);
                facilityId == null ? that.loadPreviousFilter() : '';
                that.setScrollPosition();
            },
            error: function (xhr, status, error) {
                // Ignore aborts when user clicks quickly
                if (status === 'abort') return;
            }
        });
    },
    /**
     * Refresh the facility detail tr row
     */
    refreshFacilityBookingTr: function (data) {
        let that = this; // NOSONAR javascript:S7740
        that.buildFacilityBooking(data, true);
        if ($('.clearFilterIcon').is(":visible")) {
            that.applyFacilityBookingFilter();
        }
        that.setScrollPosition();
    },
    /**
     * Get facility list settings
     */
    getFacilityBookingListSetting: function () {
        let that = this; // NOSONAR javascript:S7740
        let weekDates = that.getWeekDates();
        return {
            'start_date': weekDates[0],
            'end_date': weekDates.at(-1),
            'show_canceled': $('#include-cancelled-booking').is(':checked') ? 1 : 0,
            'show_only': $('input[name="show_only_booking_status[]"]:checked').map(function () {
                return $(this).val();
            }).get()
        };
    },
    /**
     * Build html for facility and bookings
     * @param data facility data
     * @param onlyFacilityRefresh only refresh the particular facility rows
     */

    buildFacilityBooking: function (data, onlyFacilityRefresh = false) {
        let that = this; // NOSONAR javascript:S7740
        let weekDates = that.getWeekDates();
        const viewDays = parseInt($('#view-days').val(), 10);
        const isDaily = viewDays === 1;
        const periodsPerDay = isDaily ? 24 : 4;
        const periodLabels = that.splitDayIntoIntervals(periodsPerDay);

        const $grid = $('#weekly-view-time-line-table');
        let $body = $('#weekly-view-time-line-table-body');
        if (!$body.length) {
            $body = $('<div id="weekly-view-time-line-table-body" role="rowgroup"></div>');
            $grid.append($body);
        }

        if (!onlyFacilityRefresh) {
            $body.empty();
        }

        const displayedByDay = new Set();
        let existingRows = $body.children('.facility-row').length;
        let startRowIndex = existingRows + 1;

        data.forEach((element, idx) => {
            let $row = onlyFacilityRefresh
                ? $('#tr-facility-' + element['facility_id'])
                : $('<div class="facility-row" role="row"></div>');

            $row.empty();

            $row.attr({
                'id': 'tr-facility-' + element['facility_id'],
                'data-facility-id': element['facility_id'],
                'data-facility-name': element['facility_name'],
                'data-facility-active-date': element['facility_active_form'],
                'data-facility-provider-name': element['facility_provider_name'],
                'data-facility-area-owner-id': element['facility_area_owner_id'],
                'data-facility-recurrence-id': element['FB_FacilityBookingRecurrenceID'],
                'data-facility-type': element['facility_type'],
                'data-facility-sub-type': element['facility_sub_types'].join(','),
                'data-facility-location': element['facility_current_location'],
                'data-facility-services': element['facility_services'].join(','),
                'data-facility-equipments': element['facility_equipments'].join(','),
                'data-facility-restricted-team-ids': element['facility_restricted_bookers_team'].join(','),
                'data-facility-default-booking-type': element['facility_default_booking_type'],
                'data-facility-accessible': element['facility_accessible'],
                'data-facility-archive': element['facility_archived'],
                'data-facility-allow-booking-request': element['facility_allow_booking_request'],
                'data-facility-note': element['facility_note'],
                'aria-rowindex': (startRowIndex + idx)
            });

            // Facility name cell
            let $nameCell = $('<div class="weekly-timeline-cell weekly-time-line-name booking-timeline-name-td" role="rowheader"></div>')
                .css({ flex: '0 0 var(--nameW)' })
                .append(
                    $('<div></div>').append(
                        document.createTextNode(element['facility_name'] + ' '),
                        $('<span class="info-icon" title="' + (element['facility_note'] ?? 'NA') + '"></span>')
                            .append('<i class="fa fa-info-circle" style="color: #5A1778;"></i>')
                    ),
                    $('<div class="facility-booking-timeline-location" style="color: #000000;"></div>')
                        .attr('title', element['facility_current_location'])
                        .append(
                            element['facility_current_location'].length > 20
                                ? (element['facility_current_location'].substring(0, 20) + '...')
                                : element['facility_current_location']
                        )
                );

            $row.append($nameCell);

            let access = that.facilityBookingInstance.checkFacilityAccess(
                element['facility_area_owner_id'],
                element['facility_id'],
                element['facility_restricted_bookers_team'].join(',').split(',')
            );

            // Day wrappers
            let colCounter = 2;
            weekDates.forEach(dayStr => {
                const $dayWrap = $('<div class="weekly-timeline-cell request-booking-available booking-details-td fb-day-wrap" role="presentation"></div>')
                    .attr({
                        'data-facility-id': element['facility_id'],
                        'data-date': dayStr,
                        'id': 'td-conatiner-' + element['facility_id'] + '-' + dayStr
                    })
                    .css({ flex: '0 0 var(--timelineW)', position: 'relative' });

                const $periodRow = $('<div class="period-div fb-period-row" role="presentation"></div>')
                    .css({ display: 'flex', width: 'var(--timelineW)', position: 'relative', zIndex: 1 });

                periodLabels.forEach(label => {
                    const $cell = $('<div class="fb-period-cell" role="gridcell" tabindex="-1"></div>')
                        .attr({
                            'data-fb-row': (startRowIndex + idx),
                            'data-fb-col': colCounter,
                            'aria-colindex': colCounter,
                            'data-date': dayStr,
                            'data-period': label,
                            'data-facility-id': element['facility_id'],
                            'data-facility-name': element['facility_name'],
                        })
                        .css({ flex: '0 0 var(--periodW)', minWidth: 'var(--periodW)' });

                    $cell.on('mousedown', function () {
                        try { if (that.__fbA11yUpdateCell) that.__fbA11yUpdateCell(this); } catch (e) { /* ignore */ }
                        try { that._setGridCellTabStop(this); } catch (e) { /* ignore */ }
                    });

                    $periodRow.append($cell);
                    colCounter++;
                });

                const $overlay = $('<div class="fb-day-overlay"></div>')
                    .css({ position: 'absolute', left: 0, top: 0, right: 0, bottom: 0, zIndex: 2 });

                $dayWrap.append($periodRow);
                $dayWrap.append($overlay);

                const dayStart = moment(dayStr).startOf('day');
                const dayEnd = moment(dayStart).add(1, 'day');

                // BOOKINGS: render per-day segment (overnight/multi-day)
                element['facility_bookings'].forEach(fb => {
                    const bookingId = fb['FB_FacilityBookingID'];
                    const start = moment(fb['FB_BookingStartDateTime']);
                    const end = moment(fb['FB_BookingEndDateTime']);

                    if (end.isSameOrBefore(dayStart) || start.isSameOrAfter(dayEnd)) return;

                    const segStart = moment.max(start, dayStart);
                    const segEnd = moment.min(end, dayEnd);

                    const key = bookingId + '|' + dayStr;
                    if (displayedByDay.has(key)) return;
                    displayedByDay.add(key);

                    const segStartStr = segStart.format('YYYY-MM-DD HH:mm');

                    const segEndStr = segEnd.isSame(dayEnd)
                        ? (dayStart.format('YYYY-MM-DD') + ' 24:00')
                        : segEnd.format('YYYY-MM-DD HH:mm');

                    let bookingAccess = access;
                    if (that.facilityBookingInstance.userSetting.userId == fb['FB_CreatedBy']) {
                        bookingAccess = true;
                    }
                    //In self booked booking all are requestors
                    if (element['facility_default_booking_type'] == that.facilityBookingInstance.constants.self_booked_facility
                        && that.facilityBookingInstance.userSetting.userId != fb['FB_CreatedBy']) {
                        bookingAccess = false;
                    }

                    $overlay.append(that.generateBookingDetailHtml(fb, bookingAccess, segStartStr, segEndStr)); 
                });

                // UNAVAILABILITY
                element['facility_unavailable'].forEach(facilityUnavailable => {
                    let fromM = moment(facilityUnavailable['from']);
                    let toM = moment(facilityUnavailable['to']);

                    if (toM.isSameOrBefore(dayStart) || fromM.isSameOrAfter(dayEnd)) return;

                    let segStart = moment.max(fromM, dayStart);
                    let segEnd = moment.min(toM, dayEnd);

                    let segStartStr = segStart.format('YYYY-MM-DD HH:mm');
                    let segEndStr = segEnd.isSame(dayEnd)
                        ? (dayStart.format('YYYY-MM-DD') + ' 24:00')
                        : segEnd.format('YYYY-MM-DD HH:mm');

                    $overlay.append(that.generateFacilityUnavailableHtml(
                        element['facility_id'], dayStr, facilityUnavailable, segStartStr, segEndStr
                    ));
                });

                // Booker notes
                element['facility_booker_notes'].forEach(facilityBookerNote => {
                    let bookingDate = moment(facilityBookerNote['FBN_StartDateTime']);
                    if (moment(dayStr).isSame(bookingDate, 'day')) {
                        $overlay.append(that.generateBookerNoteDetailHtml(facilityBookerNote));
                    }
                });

                $row.append($dayWrap);
            });

            if (!onlyFacilityRefresh) {
                $body.append($row);
            }
        });

        that.resetGridTabStops();
        that.updateNameColumnWidthAndRefitTimeline();
        that.scheduleSetElementCss();
        that.facilityBookingInstance.initContextMenu();
    },
    /**
     * Auto-size the NAME column to the widest facility name (max 300px),
     * then re-fit the timeline widths so there is still no horizontal scroll.
     */
    initAriaGridKeyboard: function () {
        const that = this; // NOSONAR javascript:S7740
        if (that.__fbAriaGridInit) return;
        that.__fbAriaGridInit = true;

        const gridEl = document.getElementById('weekly-view-time-line-table');
        if (!gridEl) return;

        that._setGridCellTabStop = function (cellEl) {
            try {
                const all = gridEl.querySelectorAll('.fb-period-cell');
                all.forEach(c => c.setAttribute('tabindex', '-1'));
                if (cellEl) {
                    cellEl.setAttribute('tabindex', '0');
                    cellEl.focus({ preventScroll: true });
                }
            } catch (e) { /* ignore */ }
        };

        function isVisible(el) {
            return !!(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));
        }

        function findCell(r, c) {
            return gridEl.querySelector('.fb-period-cell[data-fb-row="' + r + '"][data-fb-col="' + c + '"]');
        }

        gridEl.addEventListener('keydown', function (e) {
            const t = e.target;
            if (!t || !t.classList || !t.classList.contains('fb-period-cell')) return;

            const row = parseInt(t.getAttribute('data-fb-row') || '0', 10);
            const col = parseInt(t.getAttribute('data-fb-col') || '0', 10);
            if (!row || !col) return;

            const maxCol = parseInt(gridEl.getAttribute('aria-colcount') || '1', 10);
            let nextRow = row;
            let nextCol = col;

            switch (e.key) {
                case 'ArrowRight': nextCol = col + 1; break;
                case 'ArrowLeft': nextCol = col - 1; break;
                case 'ArrowDown': nextRow = row + 1; break;
                case 'ArrowUp': nextRow = row - 1; break;
                case 'Home': nextCol = 2; break;
                case 'End': nextCol = maxCol; break;
                default: return;
            }

            e.preventDefault();
            nextCol = Math.max(2, Math.min(maxCol, nextCol));

            let tries = 0;
            while (tries < 500) {
                const cand = findCell(nextRow, nextCol);
                if (cand && isVisible(cand)) {
                    try { if (that.__fbA11yUpdateCell) that.__fbA11yUpdateCell(cand); } catch (e) { /* ignore */ }
                    that._setGridCellTabStop(cand);
                    break;
                }
                if (e.key === 'ArrowDown') nextRow++;
                else if (e.key === 'ArrowUp') nextRow--;
                else break;
                tries++;
            }
        }, true);

        // Announce bookings under the focused/hovered grid cell
        try {
            if (!that.___fbBookingA11yBound) {
                that.___fbBookingA11yBound = true;
                function parseAllow24(dtStr) {
                    if (!dtStr) return null;
                    dtStr = String(dtStr);
                    const m = dtStr.match(/^(\d{4}-\d{2}-\d{2})\s+24:00$/);
                    if (m) {
                        return moment(m[1] + ' 00:00', 'YYYY-MM-DD HH:mm').add(1, 'day');
                    }
                    const mm = moment(dtStr, 'YYYY-MM-DD HH:mm', true);
                    return mm.isValid() ? mm : moment(dtStr);
                }
                function minutesPerPeriod() {
                    const v = parseInt($('#view-days').val(), 10);
                    return v === 1 ? 60 : 360;
                }
                function getCellInterval(cell) {
                    const day = cell.getAttribute('data-date') || (cell.closest('.fb-day-wrap') && cell.closest('.fb-day-wrap').getAttribute('data-date'));
                    const period = cell.getAttribute('data-period');
                    if (!day || !period) return null;
                    const start = parseAllow24(day + ' ' + period);
                    if (!start || !start.isValid || !start.isValid()) return null;
                    const end = moment(start).add(minutesPerPeriod(), 'minutes');
                    const dayEnd = moment(day + ' 00:00', 'YYYY-MM-DD HH:mm').add(1, 'day');
                    return { day, start, end: end.isAfter(dayEnd) ? dayEnd : end };
                }
                function collectBookingDescIds(cell, interval) {
                    const wrap = cell.closest('.fb-day-wrap');
                    if (!wrap) return [];
                    const overlay = wrap.querySelector('.fb-day-overlay');
                    if (!overlay) return [];
                    const ids = [];
                    overlay.querySelectorAll('.booking-detail-container').forEach(function (b) {
                        if (b.offsetParent === null) return;
                        const bs = parseAllow24(b.getAttribute('data-booking-cell-position-start-date-time'));
                        const be = parseAllow24(b.getAttribute('data-booking-cell-position-end-date-time'));
                        if (!bs || !be || !bs.isValid() || !be.isValid()) return;
                        if (be.isAfter(interval.start) && bs.isBefore(interval.end)) {
                            let id = b.getAttribute('data-sr-desc-id');
                            if (!id) {
                                const sr = b.querySelector('.fb-booking-sr-desc[id]');
                                if (sr) id = sr.id;
                            }
                            if (id) ids.push(id);
                        }
                    });
                    return ids;
                }
                function updateCell(cell) {
                    const interval = getCellInterval(cell);
                    if (!interval) return;
                    const ids = collectBookingDescIds(cell, interval);
                    if (ids.length) cell.setAttribute('aria-describedby', ids.join(' '));
                    else cell.removeAttribute('aria-describedby');
                }
                // Make updater accessible so we can set aria-describedby before moving focus
                that.__fbA11yUpdateCell = updateCell;

                gridEl.addEventListener('focusin', function (e) {
                    const t = e.target;
                    if (t && t.classList && t.classList.contains('fb-period-cell')) updateCell(t);
                }, true);
                gridEl.addEventListener('mouseover', function (e) {
                    const t = e.target;
                    if (t && t.classList && t.classList.contains('fb-period-cell')) updateCell(t);
                }, true);
            }
        } catch (e) { /* ignore */ }

        that.resetGridTabStops();
    },
    resetGridTabStops: function () {
        try {
            const gridEl = document.getElementById('weekly-view-time-line-table');
            if (!gridEl) return;
            const cells = Array.from(gridEl.querySelectorAll('.fb-period-cell'));
            cells.forEach(c => c.setAttribute('tabindex', '-1'));
            const first = cells.find(c => !!(c.offsetWidth || c.offsetHeight || c.getClientRects().length));
            if (first) first.setAttribute('tabindex', '0');
        } catch (e) { /* ignore */ }
    },
    updateNameColumnWidthAndRefitTimeline: function () {
        const that = this; // NOSONAR javascript:S7740

        const tableEl = document.getElementById('weekly-view-time-line-table');
        const containerEl = document.getElementById('weekly-view-time-line-container');
        if (!tableEl || !containerEl) return;

        const $table = $('#weekly-view-time-line-table');
        const isDaily = $table.hasClass('daily-view');
        const days = that.getWeekDates();
        const dayCount = Math.max(1, days.length);

        // Measure widest rendered name cell
        let maxNameW = 0;
        $('.booking-timeline-name-td').each(function () {
            maxNameW = Math.max(maxNameW, this.scrollWidth || 0);
        });

        maxNameW = Math.ceil(maxNameW + 12);
        maxNameW = Math.min(300, maxNameW);

        if (!maxNameW || maxNameW < 40) {
            const current = parseFloat(getComputedStyle(tableEl).getPropertyValue('--nameW')) || 260;
            maxNameW = Math.min(300, current);
        }

        const currentNameW = parseFloat(getComputedStyle(tableEl).getPropertyValue('--nameW')) || 260;
        if (Math.abs(currentNameW - maxNameW) < 1) return;

        const availableW = containerEl.clientWidth;
        const timelineAreaW = Math.max(0, availableW - maxNameW);
        let timelineW = timelineAreaW > 0 ? (timelineAreaW / dayCount) : 0;

        if (!timelineW || timelineW < 24) {
            timelineW = parseFloat(getComputedStyle(tableEl).getPropertyValue('--timelineW')) || (75 * 24);
        }

        const hourW = timelineW / 24;
        const tickW = hourW / 4;
        const hoursPerPeriod = isDaily ? 1 : 6;
        const periodW = hourW * hoursPerPeriod;
        const tableW = maxNameW + (timelineW * dayCount);

        tableEl.style.setProperty('--nameW', maxNameW + 'px');
        tableEl.style.setProperty('--timelineW', timelineW + 'px');
        tableEl.style.setProperty('--hourW', hourW + 'px');
        tableEl.style.setProperty('--tickW', tickW + 'px');
        tableEl.style.setProperty('--periodW', periodW + 'px');
        tableEl.style.setProperty('--tableW', tableW + 'px');

        $('#weekly-view-time-line-table thead .period-div').each(function () {
            this.style.width = timelineW + 'px';
        });

        const w = tableW + 'px';
        $table.css('width', w);
        $table.find('#weekly-view-time-line-table-head').css('width', w);
        $table.find('#weekly-view-time-line-table-body').css('width', w);
        $table.find('#weekly-view-time-line-table-head [role="row"]').css('width', w);
        $table.find('#weekly-view-time-line-table-body [role="row"]').css('width', w);

        const $linesLayer = $('#weekly-view-time-line-container').children('.timeline-lines-layer');
        if ($linesLayer.length) {
            $linesLayer.css({
                left: maxNameW + 'px',
                width: (tableW - maxNameW) + 'px'
            });
        }

        that.scheduleSetElementCss();
    },
    /**
     * Set element CSS
     */
    setElementCss: function () {
        let that = this; // NOSONAR javascript:S7740

        let weekDates = that.getWeekDates();
        that.cellPosition(weekDates.at(-1));

        that.updateOverlaps();
        that.verticalLineSet();
        that.syncBookingLabels();
    },
    /**
     * Defer setElementCss until after the browser has painted the new DOM.
     * Fixes "first render wrong until zoom" because zoom triggers a reflow/resize.
     */
    scheduleSetElementCss: function () {
        const that = this; // NOSONAR javascript:S7740

        if (that.__setCssScheduled) return;
        that.__setCssScheduled = true;

        function afterBookingsActuallyDisplayed() {
            if (that.__bookingsPaintHookScheduled) return;
            that.__bookingsPaintHookScheduled = true;

            requestAnimationFrame(() => {
                that.__bookingsPaintHookScheduled = false;

                const container = document.getElementById('weekly-view-time-line-container');
                const table = document.getElementById('weekly-view-time-line-table');
                const tbody = document.getElementById('weekly-view-time-line-table-body');
                if (!container || !table || !tbody) return;

                const visible = !!(container.offsetParent) && container.getClientRects().length > 0;
                if (!visible) return;

                const hasFacilityRows = tbody.querySelectorAll('tr.facility-row').length > 0;
                const hasNoMatchRow = !!document.getElementById('no-matching-records-row');
                if (!hasFacilityRows && !hasNoMatchRow) return;

                // Gate by bookings request sequence so it fires once per reload
                const seq = that._bookingsRequestSeq || 0;
                if (that.__lastDisplayedSeq === seq) return;
                that.__lastDisplayedSeq = seq;

                setTimeout(() => {
                    try {
                        const timelineEl = document.getElementById('weekly-view-time-line-container');
                        const bookingWrapEl = document.getElementById('booking-weekly-view-container');

                        let pending = (that.__pendingScrollSnapshots && that.__pendingScrollSnapshots[seq]) || null;

                        if (!pending) {
                            const raw = localStorage.getItem('fb_scroll_pending_restore_' + seq);
                            pending = raw ? JSON.parse(raw) : null;
                        }

                        // fallback to old single-key snapshot
                        if (!pending) {
                            const raw = localStorage.getItem('fb_scroll_pending_restore');
                            pending = raw ? JSON.parse(raw) : null;
                        }

                        function clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }

                        function applyScroll(el, snap) {
                            if (!el || !snap) return false;

                            const canY = el.scrollHeight > el.clientHeight;
                            const canX = el.scrollWidth > el.clientWidth;

                            const maxTop = Math.max(0, (el.scrollHeight || 0) - (el.clientHeight || 0));
                            const maxLeft = Math.max(0, (el.scrollWidth || 0) - (el.clientWidth || 0));

                            const top = Number.isFinite(snap.topRatio) ? (snap.topRatio * maxTop)
                                : (Number.isFinite(snap.topPx) ? snap.topPx : 0);

                            const left = Number.isFinite(snap.leftRatio) ? (snap.leftRatio * maxLeft)
                                : (Number.isFinite(snap.leftPx) ? snap.leftPx : 0);

                            if (canY) el.scrollTop = clamp(top, 0, maxTop);
                            if (canX) el.scrollLeft = clamp(left, 0, maxLeft);

                            requestAnimationFrame(() => {
                                const maxTop2 = Math.max(0, (el.scrollHeight || 0) - (el.clientHeight || 0));
                                const maxLeft2 = Math.max(0, (el.scrollWidth || 0) - (el.clientWidth || 0));

                                const top2 = Number.isFinite(snap.topRatio) ? (snap.topRatio * maxTop2)
                                    : (Number.isFinite(snap.topPx) ? snap.topPx : 0);

                                const left2 = Number.isFinite(snap.leftRatio) ? (snap.leftRatio * maxLeft2)
                                    : (Number.isFinite(snap.leftPx) ? snap.leftPx : 0);

                                if (canY) el.scrollTop = clamp(top2, 0, maxTop2);
                                if (canX) el.scrollLeft = clamp(left2, 0, maxLeft2);
                            });

                            return canY || canX;
                        }

                        let restored = false;

                        if (pending) {
                            if (pending.target === 'weekly-view-time-line-container') {
                                restored = applyScroll(timelineEl, pending);
                            } else if (pending.target === 'booking-weekly-view-container') {
                                restored = applyScroll(bookingWrapEl, pending);
                            } else {
                                restored = applyScroll(timelineEl, pending);
                            }
                        }

                        // fallback
                        if (!restored && timelineEl) {
                            const raw = localStorage.getItem('weekly_view_time_line_container_scroll_v2');
                            const obj = raw ? JSON.parse(raw) : null;
                            if (obj) restored = applyScroll(timelineEl, obj);
                        }

                    } catch (e) { /* ignore */ }
                    finally {
                        that.__suppressScrollStore = false;
                        try {
                            if (that.__pendingScrollSnapshots) delete that.__pendingScrollSnapshots[seq];
                        } catch (e) { }

                        try { localStorage.removeItem('fb_scroll_pending_restore_' + seq); } catch (e) { }
                        try { localStorage.removeItem('fb_scroll_pending_restore'); } catch (e) { }
                    }
                }, 100);
            });
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                that.__setCssScheduled = false;
                that.setElementCss();
                afterBookingsActuallyDisplayed();
            });
        });

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => {
                requestAnimationFrame(() => {
                    that.setElementCss();
                    afterBookingsActuallyDisplayed();
                });
            });
        }

        setTimeout(() => {
            that.setElementCss();
            afterBookingsActuallyDisplayed();
        }, 10);
    },
    /**
     * Update booking and action positions
     */
    cellPosition: function cellPosition(lastDate) {
        var that = this; // NOSONAR javascript:S7740
        const OFF = {
            booking: { leftAdjust: 0, widthAdjust: 0 },
            unavailable: { leftAdjust: -2 + 2, widthAdjust: 0 },
            note: { leftAdjust: -2 + 2, widthAdjust: 0 },
            action: { leftAdjust: -2, widthAdjust: 0 }
        };
        const OVERLAY_NUDGE_PX = 1;
        function rect(el) { return el ? el.getBoundingClientRect() : null; }
        function findTick(dateTimeStr) {
            return $('.fifteen-interval-day[data-date-time="' + dateTimeStr + '"]').first();
        }
        function px(v) { var n = parseFloat(v); return isFinite(n) ? n : 0; }
        function contentBoxLeftEdge(parentEl) {
            if (!parentEl) return { contentLeft: 0, padL: 0, padR: 0 };
            var cs = window.getComputedStyle(parentEl);
            var borderL = px(cs.borderLeftWidth);
            var padL = px(cs.paddingLeft);
            var padR = px(cs.paddingRight);
            var pr = rect(parentEl);
            var contentLeft = pr ? (pr.left + borderL + padL) : 0;
            return { contentLeft, padL, padR };
        }
        function placeAbs($el, parentEl, startRect, endRect, opts) {
            if (!$el || !$el.length || !parentEl || !startRect || !endRect) return;
            opts = opts || {};
            var leftAdjust = opts.leftAdjust || 0;
            var widthAdjust = opts.widthAdjust || 0;
            var c = contentBoxLeftEdge(parentEl);
            var scrollLeft = parentEl.scrollLeft || 0;
            var startX = (startRect.left - c.contentLeft) + scrollLeft + leftAdjust;
            var endX = (endRect.left - c.contentLeft) + scrollLeft + leftAdjust;
            if (!isFinite(startX) || !isFinite(endX)) return;
            startX = Math.max(0, startX);
            endX = Math.max(0, endX);
            endX = endX + widthAdjust;
            var startPx = Math.round(startX);
            var endPx = Math.round(endX);
            var contentW = Math.max(0, (parentEl.clientWidth || 0) - c.padL - c.padR);
            if (contentW > 0) {
                startPx = Math.min(startPx, contentW);
                endPx = Math.min(endPx, contentW);
            }
            startPx += OVERLAY_NUDGE_PX;
            endPx += OVERLAY_NUDGE_PX + 1;
            if (contentW > 0) {
                startPx = Math.min(Math.max(0, startPx), contentW);
                endPx = Math.min(Math.max(0, endPx), contentW);
            } else {
                startPx = Math.max(0, startPx);
                endPx = Math.max(0, endPx);
            }
            var widthPx = Math.max(0, endPx - startPx);
            $el.css({
                left: startPx + 'px',
                width: widthPx + 'px',
                'margin-left': 0
            });
            if (opts.extraCss) $el.css(opts.extraCss);
        }
        function resolveCeilEndTickForDay(startStr, endStr) {
            const startDay = (startStr || '').split(' ')[0];
            if (!startDay) return findTick(lastDate + ' 23:45');
            if ((endStr || '').endsWith(' 24:00')) {
                const $t = findTick(endStr);
                if ($t.length) return $t;
                const $cap = findTick(startDay + ' 24:00');
                return $cap.length ? $cap : findTick(startDay + ' 23:45');
            }
            let $endTick = findTick(endStr);
            if ($endTick.length) return $endTick;
            let m = moment(endStr, 'YYYY-MM-DD HH:mm', true);
            if (!m.isValid()) {
                let $cap = findTick(startDay + ' 24:00');
                return $cap.length ? $cap : findTick(startDay + ' 23:45');
            }
            const minutes = m.minutes();
            const rem = minutes % 15;
            if (rem !== 0) m.add(15 - rem, 'minutes');
            if (m.format('HH:mm') === '00:00' && m.format('YYYY-MM-DD') !== startDay) {
                let $cap = findTick(startDay + ' 24:00');
                return $cap.length ? $cap : findTick(startDay + ' 23:45');
            }
            const snappedStr = m.format('YYYY-MM-DD HH:mm');
            $endTick = findTick(snappedStr);
            if ($endTick.length) return $endTick;
            let $cap = findTick(startDay + ' 24:00');
            return $cap.length ? $cap : findTick(startDay + ' 23:45');
        }

        // 1) BOOKINGS
        $('.booking-detail-container').each(function (_, element) {
            var $booking = $(element);
            var startAttr = $booking.attr('data-booking-cell-position-start-date-time');
            var endAttr = $booking.attr('data-booking-cell-position-end-date-time');
            if (!startAttr || !endAttr) return;

            var $startTick = findTick(startAttr);
            if (!$startTick.length) { $booking.hide(); return; }

            var $endTick = findTick(endAttr);
            if (!$endTick.length) $endTick = resolveCeilEndTickForDay(startAttr, endAttr);
            if (!$endTick.length) { $booking.hide(); return; }

            var startRect = rect($startTick[0]);
            var endRect = rect($endTick[0]);
            if (!startRect || !endRect) return;

            var $td = $booking.closest('.fb-day-overlay');
            if (!$td.length) return;

            placeAbs($booking, $td[0], startRect, endRect, OFF.booking);
            $booking.show();

            const bookingId = String($booking.attr('data-facility-booking-id') || '');
            if (!bookingId) return;

            let $bar = $booking.find('> .booking-actions-bar[data-booking-id="' + bookingId + '"]');
            if (!$bar.length) $bar = $td.find('> .booking-actions-bar[data-booking-id="' + bookingId + '"]');

            if ($bar.length) {
                if ($bar.parent()[0] !== $td[0]) {
                    $bar = $bar.detach();
                    $td.append($bar);
                }

                const leftPx = parseFloat($booking.css('left')) || 0;
                const topPx = parseFloat($booking.css('top')) || 0;
                const widthPx = parseFloat($booking.css('width')) || 0;

                const bookingH = parseFloat($booking.css('height')) || 25;
                const bookingMarginTop = parseFloat($booking.css('margin-top')) || 0;
                const barH = 10;

                $bar.css({
                    position: 'absolute',
                    left: leftPx + 'px',
                    top: (topPx + bookingMarginTop + bookingH - barH) + 'px',
                    width: widthPx + 'px',
                    height: barH + 'px',
                    overflow: 'hidden'
                }).show();

            }
        });

        // 2) ACTION position
        $('.booking-actions-bar .action-detail-container').each(function (_, element) {
            var $action = $(element);
            var startStr = $action.attr('data-action-start-date');
            var endStr = $action.attr('data-action-end-date');
            if (!startStr || !endStr) return;

            var $startTick = findTick(startStr);
            var $endTick = findTick(endStr);

            // Clamp to day boundaries if ticks are missing
            if (!$startTick.length) {
                var endM = moment(endStr);
                $startTick = findTick(endM.format('YYYY-MM-DD') + ' 00:00');
            }
            if (!$endTick.length) {
                $endTick = resolveCeilEndTickForDay(startStr, endStr);
            }
            if (!$endTick.length) {
                var startM = moment(startStr);
                $endTick = findTick(startM.format('YYYY-MM-DD') + ' 23:45');
            }

            if (!$startTick.length || !$endTick.length) { $action.hide(); return; }

            var startRect = rect($startTick[0]);
            var endRect = rect($endTick[0]);
            if (!startRect || !endRect) return;

            var parentEl = $action.closest('.booking-actions-bar')[0];
            if (!parentEl) return;

            placeAbs($action, parentEl, startRect, endRect, {
                leftAdjust: OFF.action.leftAdjust,
                widthAdjust: OFF.action.widthAdjust,
                extraCss: { background: that.getActionColour(($action.attr('data-action-name') || $action.attr('title') || '')) }
            });

            $action.show();
        });

        // 3) BOOKER NOTES (relative to TD)
        $('.booker-note-detail-container').each(function (_, element) {
            var $note = $(element);
            var startStr = $note.attr('data-booker-note-start-date-time');
            var endStr = $note.attr('data-booker-note-end-date-time');
            if (!startStr || !endStr) return;
            var $startTick = findTick(startStr);
            var $endTick = findTick(endStr);
            if (!$startTick.length) { $note.hide(); return; }
            if (!$endTick.length) {
                $endTick = resolveCeilEndTickForDay(startStr, endStr);
            }
            if (!$endTick.length) {
                var startM = moment(startStr);
                $endTick = findTick(startM.format('YYYY-MM-DD') + ' 23:45');
            }
            if (!$endTick.length) { $note.hide(); return; }
            var startRect = rect($startTick[0]);
            var endRect = rect($endTick[0]);
            if (!startRect || !endRect) return;
            var $td = $note.closest('.fb-day-overlay');
            if (!$td.length) return;
            placeAbs($note, $td[0], startRect, endRect, OFF.note);
            $note.show();
        });

        // 4) FACILITY UNAVAILABILITY (relative to TD)
        $('.facility-unavailable-container').each(function (_, element) {
            var $un = $(element);
            var startStr = $un.attr('data-facility-unavailable-cell-start-date-time') ||
                $un.attr('data-facility-unavailable-start-date-time');
            var endStr = $un.attr('data-facility-unavailable-cell-end-date-time') ||
                $un.attr('data-facility-unavailable-end-date-time');
            if (!startStr || !endStr) return;
            var $startTick = findTick(startStr);
            if (!$startTick.length) { $un.hide(); return; }
            var $endTick = findTick(endStr);
            if (!$endTick.length) {
                $endTick = resolveCeilEndTickForDay(startStr, endStr);
            }
            if (!$endTick.length) {
                var startM = moment(startStr);
                $endTick = findTick(startM.format('YYYY-MM-DD') + ' 23:45');
            }
            if (!$endTick.length) { $un.hide(); return; }
            var startRect = rect($startTick[0]);
            var endRect = rect($endTick[0]);
            if (!startRect || !endRect) return;
            var $td = $un.closest('.fb-day-overlay');
            if (!$td.length) return;
            placeAbs($un, $td[0], startRect, endRect, OFF.unavailable);
            $un.show();
        });
    },
    /**
     * Update Overlaps with same time
     */
    updateOverlaps: function () {
        let that = this; // NOSONAR javascript:S7740

        const BASE_TOP = 21;
        const ROW_GAP = 40;

        // Reset booking tops first
        $('.booking-detail-container').css({ top: BASE_TOP });

        // Remove old overlays
        $('.overlap-div').remove();

        function num(v) {
            const n = parseFloat(v);
            return isFinite(n) ? n : 0;
        }
        function rect(el) {
            return el ? el.getBoundingClientRect() : null;
        }

        function mergeIntervals(intervals) {
            if (!intervals || !intervals.length) return [];
            intervals.sort((a, b) => a[0] - b[0]);
            const merged = [intervals[0].slice()];
            for (let i = 1; i < intervals.length; i++) {
                const [l, r] = intervals[i];
                const last = merged[merged.length - 1];
                if (l <= last[1] + 1) {
                    last[1] = Math.max(last[1], r);
                } else {
                    merged.push([l, r]);
                }
            }
            return merged;
        }

        // 1) Stack bookings within each TD
        $('.fb-day-overlay').each(function (_, td) {
            const $td = $(td);
            const $boxes = $td.find('.booking-detail-container:visible');
            if (!$boxes.length) return;

            // reset
            $boxes.css({ top: BASE_TOP });

            // collect by X-range
            const boxes = $boxes.get().map(el => {
                const $el = $(el);
                const left = num($el.css('left'));
                const width = num($el.css('width'));
                return { el, $el, left, right: left + width };
            }).sort((a, b) => a.left - b.left);

            // greedy row assignment
            const rowRightEdge = [];
            boxes.forEach(b => {
                let row = 0;
                while (row < rowRightEdge.length && b.left < rowRightEdge[row]) row++;
                if (row === rowRightEdge.length) rowRightEdge.push(0);
                rowRightEdge[row] = Math.max(rowRightEdge[row], b.right);

                b.$el.css({ top: BASE_TOP + (row * ROW_GAP) });
                b.$el.attr('data-overlap-row', row);
            });

            // ensure TD height fits stacked rows
            const rows = Math.max(1, rowRightEdge.length);
            const neededH = BASE_TOP + (rows * ROW_GAP);
            const $wrap = $td.closest('.fb-day-wrap');
            if ($wrap.length) {
                $wrap.css({ height: Math.max($wrap.height(), neededH) });
            }
        });

        // 2) Draw overlap overlays ONLY for the overlapped segments.
        $('.fb-day-overlay').each(function (_, td) {
            const $td = $(td);
            const tdRect = rect(td);
            if (!tdRect) return;

            const $boxes = $td.find('.booking-detail-container:visible');
            const els = $boxes.get();
            if (els.length < 2) return;

            // Build rect info in TD-local coordinates using true rendered rects
            const info = els.map(el => {
                const $el = $(el);
                const r = rect(el);
                if (!r) return null;

                const id = String($el.attr('data-facility-booking-id') || '');
                const status = String($el.attr('data-facility-booking-status') || '');

                return {
                    el,
                    $el,
                    id,
                    status,
                    left: r.left - tdRect.left,
                    right: r.right - tdRect.left,
                    top: r.top - tdRect.top,
                    height: r.height,
                    width: r.width
                };
            }).filter(Boolean);

            for (let i = 0; i < info.length; i++) {
                const a = info[i];
                if (!a.id) continue;

                if (a.status === 'cancelled' || a.status === 'declined') continue;

                const intervals = [];
                const relatedIds = new Set();

                for (let j = 0; j < info.length; j++) {
                    if (i === j) continue;
                    const b = info[j];

                    if (b.status === 'cancelled' || b.status === 'declined') continue;

                    const overlapLeft = Math.max(a.left, b.left);
                    const overlapRight = Math.min(a.right, b.right);

                    if (overlapRight > overlapLeft) {
                        intervals.push([overlapLeft, overlapRight]);
                        if (b.id) relatedIds.add(b.id);
                    }
                }

                const merged = mergeIntervals(intervals);
                if (!merged.length) continue; // no overlaps => no overlays

                // Create an overlay DIV for each overlapped segment only
                merged.forEach(([l, r]) => {
                    const w = r - l;
                    if (w <= 0) return;

                    const $overlap = $('<div class="overlap-div"></div>').attr({
                        'data-facility-booking-id': a.id,
                        'data-facility-booking-status': a.status,
                        'data-facility-booking-related-ids': Array.from(relatedIds).join(','),
                        // Optional: debugging markers
                        'data-overlap-left': Math.round(l),
                        'data-overlap-right': Math.round(r)
                    }).css({
                        position: 'absolute',
                        left: Math.round(l) + 'px',
                        top: Math.round(a.top) + 'px',
                        width: Math.round(w) + 'px',
                        height: Math.round(a.height) + 'px'
                    });

                    $td.append($overlap);
                });
            }
        });

        that.syncBookingLabels();
    },
    /**
     * Max height of table body
     */
    setMaxHeight: function () {
        const viewDays = parseInt($('#view-days').val(), 10);
        const isWideMode = viewDays >= 1;

        const $wrap = $('#weekly-view-time-line-container');
        const $body = $('#weekly-view-time-line-table-body');

        if (isWideMode) {
            $wrap.css({ height: '', 'max-height': '' });

            const maxH = $(window).height() - $wrap.offset().top - 15;
            $wrap.css({
                'max-height': maxH + 'px',
                'overflow-y': 'auto',
                'overflow-x': 'hidden'
            });

            $body.css({
                height: '',
                'max-height': '',
                'overflow-y': 'visible',
                'overflow-x': 'visible'
            });
        } else {
            $body.css("height", "");
            let height = $(window).height() - $body.offset().top - 15;
            $body.height($body.height() < height ? $body.height() : height);
        }
    },
    /**
     * Generate booking detail HTML
     */
    generateBookingDetailHtml: function (bookingDetail, access, segmentStartStr = null, segmentEndStr = null) {
        let that = this; // NOSONAR javascript:S7740
        let viewDays = $('#view-days').val();
        let bookingStartDateTime = moment(bookingDetail['FB_BookingStartDateTime']);
        let bookingEndDateTime = moment(bookingDetail['FB_BookingEndDateTime']);

        let bookingCellPositionStart = bookingStartDateTime.format('YYYY-MM-DD HH:mm');
        let bookingCellPositionEnd = bookingEndDateTime.format('YYYY-MM-DD HH:mm');

        if (segmentStartStr && segmentEndStr) {
            bookingCellPositionStart = segmentStartStr;
            bookingCellPositionEnd = segmentEndStr;
        }

        let canSeeTitle = (access || bookingDetail['FB_Private'] == 'no' || bookingDetail['FB_Private'] == 'summary');
        let title = bookingDetail['FB_BookingTitle'];
        if (access || bookingDetail['FB_Private'] == 'no') {
            title = title + ' ' +
                bookingStartDateTime.format('HH:mm') + ' - ' +
                bookingEndDateTime.format('HH:mm');
        }
        let showTitle = (canSeeTitle ? title : 'Busy');

        let $div = $('<div class="booking-detail-container"></div>').attr({
            'data-facility-id': bookingDetail['FB_FacilityID'],
            'data-facility-booking-id': bookingDetail['FB_FacilityBookingID'],
            'data-facility-booking-status': bookingDetail['FB_BookingStatus'],
            'data-booking-start-date-time': bookingStartDateTime.format('YYYY-MM-DD HH:mm'),
            'data-booking-end-date-time': bookingEndDateTime.format('YYYY-MM-DD HH:mm'),
            'data-booking-cell-position-start-date-time': bookingCellPositionStart,
            'data-booking-cell-position-end-date-time': bookingCellPositionEnd,
            'data-booking-created-by': bookingDetail['FB_CreatedBy'],
            'data-booking-privacy': bookingDetail['FB_Private'],
            'data-is-linked-booking': bookingDetail['linked_to_facility_booking'] == null ? 0 : 1,
            'data-parent-booking-id': bookingDetail['linked_to_facility_booking'] == null ? '' : bookingDetail['linked_to_facility_booking']['FB_FacilityBookingID'],
            'title': showTitle,
            'aria-label': 'Booking - ' + showTitle,
            'data-facility-booking-title': (canSeeTitle ? bookingDetail['FB_BookingTitle'] : 'Busy')
        });

        $div.addClass(bookingDetail['FB_BookingStatus'] + '-booking');
        // Accessibility
        try {
            const mkId = function (s) { return String(s || '').replace(/[^0-9A-Za-z]+/g, ''); };
            const srId = 'fb-booking-sr-' + bookingDetail['FB_FacilityBookingID'] + '-' + mkId(bookingCellPositionStart) + '-' + mkId(bookingCellPositionEnd);
            $div.attr('data-sr-desc-id', srId);
            const segDate = (bookingCellPositionStart || '').split(' ')[0];
            const segStartT = (bookingCellPositionStart || '').split(' ')[1] || '';
            const segEndT = (bookingCellPositionEnd || '').split(' ')[1] || '';
            const srDateText = segDate ? that.convertToDDMMYYYY(segDate) : '';
            const srStatus = bookingDetail['FB_BookingStatus'] || '';
            const srTitle = (showTitle || '').trim();
            const hasTimeRangeInTitle = /\d{2}:\d{2}\s*[\-–]\s*\d{2}:\d{2}/.test(srTitle);
            const timePart = (!hasTimeRangeInTitle && segStartT && segEndT) ? (', ' + segStartT + ' to ' + segEndT) : '';
            const srText = ('Booking: ' + (srTitle || 'Booking') + (srDateText ? ('. ' + srDateText) : '') + timePart + (srStatus ? ('. Status: ' + srStatus) : '')).trim();
            if ($div.find('#' + srId).length === 0) {
                $div.append($('<span class="sr-only fb-booking-sr-desc"></span>').attr('id', srId).text(srText));
            }
        } catch (e) { /* ignore */ }

        $div.append(
            $('<span class="booking-text-label"></span>')
                .attr('data-facility-booking-id', bookingDetail['FB_FacilityBookingID'])
                .text(showTitle)
        );
        if (bookingDetail['FB_Private'] == 'yes') {
            $div.addClass('private-booking');
        }
        if (access && bookingDetail['FB_BookingStatus'] == "cancelled") {
            $div.addClass('right-click-menu-admin-reinstate');
        } else {
            $div.addClass('booking-detail-cell-right-click-menu');
        }

        // ACTIONS: render for daily view only, inside a wrapper div
        let canSeeActions = (access || bookingDetail['FB_Private'] == 'no');
        if (viewDays == 1 && canSeeActions && Array.isArray(bookingDetail['actions'])) {
            const bookingId = bookingDetail['FB_FacilityBookingID'];

            const $bar = $('<div class="booking-actions-bar"></div>').attr({
                'data-booking-id': bookingId
            });

            const selectedDayStr = $('#view-date-calender').val();
            const selectedDay = selectedDayStr ? moment(selectedDayStr, 'YYYY-MM-DD') : moment(bookingStartDateTime.format('YYYY-MM-DD'), 'YYYY-MM-DD');
            const dayStart = moment(selectedDay).startOf('day');
            const dayEnd = moment(dayStart).add(1, 'day');

            let bookingStartTime = moment(bookingStartDateTime.format('HH:mm'), "HH:mm");
            let bookingEndTime = moment(bookingEndDateTime.format('HH:mm'), "HH:mm");
            const overnightBooking = bookingEndTime.isBefore(bookingStartTime);

            bookingDetail['actions'].forEach(actionObj => {
                if (!actionObj || !actionObj['pivot']) return;

                const actionName = actionObj['action_name'] || '';
                const pivot = actionObj['pivot'];
                const startT = pivot['FBA_ActionStartTime'];
                const endT = pivot['FBA_ActionEndTime'];
                if (!startT || !endT) return;

                let actionStartDateTime = moment(bookingStartDateTime.format('YYYY-MM-DD') + ' ' + startT, 'YYYY-MM-DD HH:mm');
                let actionEndDateTime = moment(bookingStartDateTime.format('YYYY-MM-DD') + ' ' + endT, 'YYYY-MM-DD HH:mm');

                // if booking is overnight, actions may spill into next day depending on times
                if (overnightBooking) {
                    const aStartTime = moment(startT, 'HH:mm');
                    const aEndTime = moment(endT, 'HH:mm');
                    if (aStartTime.isBefore(bookingStartTime)) actionStartDateTime.add(1, 'day');
                    if (aEndTime.isBefore(bookingStartTime)) actionEndDateTime.add(1, 'day');
                }

                if (actionEndDateTime.isSameOrBefore(dayStart) || actionStartDateTime.isSameOrAfter(dayEnd)) {
                    return;
                }

                const segStart = moment.max(actionStartDateTime, dayStart);
                const segEnd = moment.min(actionEndDateTime, dayEnd);

                const segStartStr = segStart.format('YYYY-MM-DD HH:mm');
                const segEndStr = segEnd.isSame(dayEnd)
                    ? (dayStart.format('YYYY-MM-DD') + ' 24:00')
                    : segEnd.format('YYYY-MM-DD HH:mm');

                const actionKey = [bookingId, actionName, segStartStr, segEndStr].join('|');
                const actionTitle = `${actionName} ${segStart.format('HH:mm')} - ${segEnd.format('HH:mm')}`;
                const actionColour = that.getActionColour(actionName);

                const $act = $('<div class="action-detail-container"></div>').attr({
                    'data-action-key': actionKey,
                    'data-booking-id': bookingId,
                    'data-action-name': actionName,
                    'data-action-start-date': segStartStr,
                    'data-action-end-date': segEndStr,
                    'title': actionTitle
                });

                // keep label inside action box
                $act.append(
                    $('<span class="action-text-label"></span>')
                        .attr({ 'data-action-key': actionKey, 'title': actionTitle })
                        .text(actionTitle)
                        .css({ 'background-color': actionColour })
                );

                $bar.append($act);
            });

            if ($bar.children().length) {
                $div.append($bar);
            }
        }

        return $div;
    },
    /**
     * Keep booking title labels above everything, correctly positioned, and never mismatched.
     * Uses the booking bar's own CSS (margin-left/top/width) so it's stable on abrupt resizes.
     */
    syncBookingLabels: function () {
        // Accessibility: the floating visual labels
        try { $('.booking-text-label').attr('aria-hidden', 'true'); } catch (e) { /* ignore */ }

        function rectRelativeTo($el, $td) {
            const el = $el[0], td = $td[0];
            if (!el || !td) return { left: 0, top: 0, width: 0, height: 0 };
            const r1 = el.getBoundingClientRect();
            const r2 = td.getBoundingClientRect();
            return {
                left: (r1.left - r2.left) + td.scrollLeft,
                top: (r1.top - r2.top) + td.scrollTop,
                width: r1.width,
                height: r1.height
            };
        }

        // BOOKING label cleanup
        const existingIds = new Set();
        $('.booking-detail-container').each(function () {
            const id = $(this).attr('data-facility-booking-id');
            if (id) existingIds.add(String(id));
        });

        $('.fb-day-overlay > .booking-text-label').each(function () {
            const id = $(this).attr('data-facility-booking-id');
            if (!id || !existingIds.has(String(id))) {
                $(this).remove();
            }
        });

        $('.fb-day-overlay').each(function (_, td) {
            const $td = $(td);

            // BOOKING OVERLAY LABELS
            $td.find('.booking-detail-container').each(function () {
                const $booking = $(this);
                const bookingId = String($booking.attr('data-facility-booking-id') || '');
                if (!bookingId) return;

                let $bLabel = $booking.find('> .booking-text-label[data-facility-booking-id="' + bookingId + '"]');
                if (!$bLabel.length) $bLabel = $td.find('> .booking-text-label[data-facility-booking-id="' + bookingId + '"]');

                if ($bLabel.length && $bLabel.parent()[0] === $booking[0]) {
                    $bLabel = $bLabel.detach();
                    $td.append($bLabel);
                }

                if (!$bLabel.length) {
                    $bLabel = $('<span class="booking-text-label"></span>')
                        .attr('data-facility-booking-id', bookingId)
                        .text(($booking.attr('title') || '').trim());
                    $td.append($bLabel);
                }

                if (!$booking.is(':visible') || $booking.outerWidth() < 2) {
                    $bLabel.hide();
                    return;
                }
                $bLabel.show();

                const b = rectRelativeTo($booking, $td);

                const titleLineHeight = parseFloat($bLabel.css('line-height')) || 12;

                // position booking title at row 1
                $bLabel.css({
                    left: b.left + 'px',
                    top: b.top + 'px',
                    width: b.width + 'px',
                    height: titleLineHeight + 'px',
                    'z-index': 4
                });

                $booking.data('__bookingRect', b);
                $booking.data('__titleH', titleLineHeight);
            });
        });
    },
    /**
     * Get actions color
     *
     */
    getActionColour: function (actionName) {
        let that = this; // NOSONAR javascript:S7740
        if (!actionName) return '';

        // Normalise: lower-case, trim, collapse spaces
        let actionNameLower = String(actionName).toLowerCase().trim().replace(/\s+/g, ' ');
        // If a time range is appended (e.g. "read news 04:00 - 04:30"), strip it
        actionNameLower = actionNameLower
            .replace(/\s\d{2}:\d{2}\s*-\s*\d{2}:\d{2}.*/g, '')
            .trim();

        let backgroundColour = '';
        if (actionNameLower && (actionNameLower in that.actionStaticSettings)) {
            backgroundColour = "#" + that.actionStaticSettings[actionNameLower].backgroundColour;
        }
        return backgroundColour;
    },
    /**
     * Generate booker notes html
     */
    generateBookerNoteDetailHtml: function (faciltyBokerNoteDetail) {
        let startDateTime = moment(faciltyBokerNoteDetail['FBN_StartDateTime']);
        let endDateTime = moment(faciltyBokerNoteDetail['FBN_EndDateTime']);
        let $div = $('<div class="booker-note-detail-container"></div>').attr({
            'data-facility-id': faciltyBokerNoteDetail['FBN_FacilityID'],
            'data-booker-note-id': faciltyBokerNoteDetail['FBN_FacilityBookerNoteID'],
            'data-booker-note-start-date-time': startDateTime.format('YYYY-MM-DD HH:mm'),
            'data-booker-note-end-date-time': endDateTime.format('YYYY-MM-DD HH:mm'),
            'data-booker-note-created-by': faciltyBokerNoteDetail['FBN_CreatedBy'],
            'title': faciltyBokerNoteDetail['FBN_Note']
        }).text(faciltyBokerNoteDetail['FBN_Note']);
        return $div;
    },
    /**
     * Generate Unavailable html
     */
    generateFacilityUnavailableHtml: function (facilityId, date, facilityUnavailable, segmentStartStr = null, segmentEndStr = null) {
        // Use segmented bounds if provided
        let startStr = segmentStartStr
            ? segmentStartStr
            : moment(facilityUnavailable['from']).format('YYYY-MM-DD HH:mm');

        let endStr = segmentEndStr
            ? segmentEndStr
            : moment(facilityUnavailable['to']).format('YYYY-MM-DD HH:mm');

        let cellStartStr = startStr;
        let cellEndStr = endStr;

        // Safety: if end tick doesn't exist, clamp to 23:45 of the same day.
        try {
            if ($('.fifteen-interval-day[data-date-time="' + cellEndStr + '"]').length === 0) {
                const day = (cellStartStr || '').split(' ')[0];
                if (day && $('.fifteen-interval-day[data-date-time="' + day + ' 23:45' + '"]').length > 0) {
                    cellEndStr = day + ' 23:45';
                }
            }
        } catch (e) { /* ignore */ }

        let title = facilityUnavailable.archived ? 'Archived' : 'Unavailable';

        let $div = $('<div class="facility-unavailable-container"></div>').attr({
            'data-facility-unavailable-start-date-time': startStr,
            'data-facility-unavailable-end-date-time': endStr,

            'data-facility-unavailable-cell-start-date-time': cellStartStr,
            'data-facility-unavailable-cell-end-date-time': cellEndStr,

            'data-facility-id': facilityId,
            'data-date': date,
            'title': title
        });

        return $div;
    },
    /**
     * Get week days saturday to friday
     *
     * @returns
     */
    getWeekDates: function () {
        const today = $.trim($('#view-date-calender').val()) == '' ? new Date() : new Date($('#view-date-calender').val());
        let showDays = parseInt($('#view-days').val());
        if (showDays == 1) {
            return [today.toISOString().split('T')[0]];
        }
        const day = today.getDay(); // 0 = Sunday, 6 = Saturday
        const saturdayOffset = (day === 6) ? 0 : (day + 1);
        const saturday = new Date(today);
        saturday.setDate(today.getDate() - saturdayOffset);
        const weekDates = [];
        for (let i = 0; i < showDays; i++) {
            const date = new Date(saturday);
            date.setDate(saturday.getDate() + i);
            weekDates.push(date.toISOString().split('T')[0]); // Format: YYYY-MM-DD
        }
        return weekDates;
    },
    /**
     * Convert yyyy-mm-dd to dd/mm/yyyy
     */
    convertToDDMMYYYY: function (dateStr) {
        const parts = dateStr.split("-");
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    },
    /**
     * Splits 24 hours to mins
     */
    splitDayIntoIntervals: function (parts) {
        const totalMinutes = 24 * 60;               // 1440
        const step = totalMinutes / parts;          // minutes per part
        const labels = [];
        for (let i = 0; i < parts; i++) {
            const mins = Math.floor(i * step);
            const hh = String(Math.floor(mins / 60)).padStart(2, '0');
            const mm = String(mins % 60).padStart(2, '0');
            labels.push(`${hh}:${mm}`);
        }
        return labels;
    },
    /**
     * Checks if two elements are overlapping
     * @param {*} $a
     * @param {*} $b
     * @returns
     */
    isOverlapping: function ($a, $b) {
        const a = $a[0].getBoundingClientRect();
        const b = $b[0].getBoundingClientRect();

        return !(
            a.right < b.left ||
            a.left > b.right ||
            a.bottom < b.top ||
            a.top > b.bottom
        );
    },
    /*
    * Toggle filters visibilty
    */
    toggleFilterVisibilty: function () {
        var that = this; // NOSONAR javascript:S7740

        var $trigger = $("#facility-booking-filter-trigger-dropdown");
        var $content = $("#facility-booking-filter-content");
        var $icon = $("#facility-booking-filter-trigger-dropdown-icon");

        if (!$content.data("orig-parent")) {
            $content.data("orig-parent", $content.parent());
            $content.data("orig-style", $content.attr("style") || "");
        }

        function positionDropdown() {
            if (!$content.is(":visible")) return;

            var rect = $trigger[0].getBoundingClientRect();

            $content.css({
                position: "fixed",
                top: (rect.bottom + 6) + "px",
                left: rect.left + "px",
                zIndex: 20000,
                // optional: keep it within viewport
                maxHeight: "calc(100vh - " + (rect.bottom + 12) + "px)",
                overflow: "auto"
            });
        }

        function openDropdown() {
            $("body").append($content);

            $content.show();
            $icon.removeClass("fa-caret-right").addClass("fa-caret-down");

            positionDropdown();

            $(window).on("scroll.fbFilter resize.fbFilter", positionDropdown);

            $(document).on("mousedown.fbFilter", function (e) {
                if ($(e.target).closest("#facility-booking-filter-content, #facility-booking-filter-trigger-dropdown").length === 0) {
                    closeDropdown();
                }
            });
        }

        function closeDropdown() {
            $content.hide();

            $content.data("orig-parent").append($content);

            $content.attr("style", $content.data("orig-style") || "display:none;");

            $icon.removeClass("fa-caret-down").addClass("fa-caret-right");

            $(window).off("scroll.fbFilter resize.fbFilter");
            $(document).off("mousedown.fbFilter");
        }

        $trigger.off("click.fbFilter").on("click.fbFilter", function (e) {
            e.preventDefault();
            e.stopPropagation();

            if ($content.is(":visible")) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        $('#facility-booking-filter-details-drop-down').click(function () {
            $("#facility-booking-filter-details").toggle();
            $("#facility-booking-filter-details-drop-down-icon").toggleClass("fa-angle-right fa-angle-down");
        });

        $('.clearFilterIcon').click(function () {
            that.clearFilter();
        });
    },
    /**
     * Accessibility: Keyboard support for the booking filter dropdown.
     */
    initFilterKeyboardSupport: function () {
        var $trigger = $('#facility-booking-filter-trigger-dropdown');
        var $content = $('#facility-booking-filter-content');
        var $icon = $('#facility-booking-filter-trigger-dropdown-icon');
        var $detailsBtn = $('#facility-booking-filter-details-drop-down');
        var $details = $('#facility-booking-filter-details');
        var $detailsIcon = $('#facility-booking-filter-details-drop-down-icon');
        var $clearBtn = $('#facility-booking-filter-cancel-btn');

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

        function firstFocusable(containerEl) {
            if (!containerEl) return null;
            return containerEl.querySelector(
                'input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), ' +
                'button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
            );
        }

        function prevDayElement() {
            var prevText = document.getElementById('previous-text');
            return prevText ? prevText.closest('button, [href], [tabindex], [role="button"]') : null;
        }

        // Safe re-init
        try {
            $trigger.off('.fbFilterA11y');
            $clearBtn.off('.fbFilterA11y');
            $content.off('.fbFilterA11y');
            $(document).off('keydown.fbFilterA11y');
        } catch (e) { /* ignore */ }

        // Keep ARIA in sync after open/close.
        $trigger.on('click.fbFilterA11y', function () { window.setTimeout(syncMainAria, 0); });
        $detailsBtn.on('click.fbFilterA11y', function () { window.setTimeout(syncDetailsAria, 0); });

        // Expanded: Tab from Filters enters the filter.
        $trigger.on('keydown.fbFilterA11y', function (e) {
            if (e.key !== 'Tab' || e.shiftKey) return;
            if (!syncMainAria()) return;
            var first = firstFocusable($content[0]);
            if (first) {
                e.preventDefault();
                first.focus();
            }
        });

        // Expanded: Shift+Tab from first focusable returns to Filters.
        $content.on('keydown.fbFilterA11y', function (e) {
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

            // ESC collapses filter if focus is inside.
            if (e.key === 'Escape') {
                if (!syncMainAria()) return;
                if ($content[0] && $content[0].contains(document.activeElement)) {
                    e.preventDefault();
                    $trigger.trigger('click');
                    window.setTimeout(function () { syncMainAria(); $trigger.focus(); }, 0);
                }
            }
        });

        // Expanded: Tab from Clear -> Previous Day.
        $clearBtn.on('keydown.fbFilterA11y', function (e) {
            if (e.key !== 'Tab' || e.shiftKey) return;
            if (!syncMainAria()) return;
            var prev = prevDayElement();
            if (!prev) return;
            e.preventDefault();
            prev.focus();
        });

        // Expanded: Shift+Tab from Previous Day -> Clear.
        $(document).on('keydown.fbFilterA11y', function (e) {
            if (e.key !== 'Tab' || !e.shiftKey) return;
            if (!syncMainAria()) return;
            if (!$clearBtn.length) return;
            var prev = prevDayElement();
            if (!prev) return;
            if (e.target === prev || (prev && prev.contains(e.target)) || (e.target && e.target.id === 'previous-text')) {
                e.preventDefault();
                $clearBtn.focus();
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
        $("#facility_booking_filter_privacy_type option:first").attr('selected', 'selected');
        $("#facility_booking_filter_saved_filter_list option:first").attr('selected', 'selected');
        that.clearFilterFields();
        $('.facility-row').show();
        if ($("#weekly-view-time-line-table-body").length) {
            that.setElementCss();
        }

        //Clear checkbox filters
        if ($('#include-cancelled-booking').is(':checked') || $('input[name="show_only_booking_status[]"]:checked').length > 0) {
            $('#include-cancelled-booking, input[name="show_only_booking_status[]"]').prop('checked', false);
            that.reloadTimeline();
        }
        localStorage.removeItem(that.facilityBookingFilterLocalStorageName);
    },
    /*
    * Facility filter elements
    */
    initFacilityFilterElements: function () {
        var that = this; // NOSONAR javascript:S7740
        $('#facility-booking-filter-provider-type-filter-data').chosen({
            width: '100%'
        });
        that.applyChosenA11y('facility-booking-filter-provider-type-filter-data');
        $('#facility-booking-filter-default-booking-type-filter-data').chosen({
            width: '100%'
        });
        that.applyChosenA11y('facility-booking-filter-default-booking-type-filter-data');
        $('#facility-booking-filter-accessible-type-filter-data').chosen({
            width: '100%'
        });
        that.applyChosenA11y('facility-booking-filter-accessible-type-filter-data');
        $('#filter_facility_booking_area_data').chosen({
            width: '100%'
        });
        that.applyChosenA11y('filter_facility_booking_area_data');
        $('#facility-booking-filter-facility-booking-type-filter-data').chosen({
            width: '100%'
        }).change(function () {
            that.setFilterSubTypeSelect();
            that.applyChosenA11y('facility-booking-filter-facility-booking-type-filter-data');
        });
        $('#facility-booking-filter-facility-booking-sub-type-filter-data').chosen({
            width: '100%'
        })
        $('#facility-booking-filter-facility-booking-service-filter-data').chosen({
            width: '100%'
        })
        $('#facility-booking-filter-facility-booking-equipment-filter-data').chosen({
            width: '100%'
        })
        that.setFilterTypeSelect();
        that.applyChosenA11y('facility-booking-filter-facility-booking-equipment-filter-data');
        that.applyChosenA11y('facility-booking-filter-facility-booking-service-filter-data');
        that.applyChosenA11y('facility-booking-filter-facility-booking-sub-type-filter-data');
        that.setFilterSerivceSelect();
        that.setFilterEquipmentSelect();

        //Apply filter
        $('#facility-booking-filter-apply-btn').on('click', function () {
            that.applyFacilityBookingFilter();
        });

        //Save filter
        $('#facility-booking-filter-private-save-btn').on('click', function () {
            that.saveFacilityFilter('private');
        });
        $('#facility-booking-filter-public-save-btn').on('click', function () {
            that.saveFacilityFilter('public');
        });

        //Delete filter
        $('#facility-booking-filter-delete-btn').on('click', function () {
            that.deleteFilter($('#facility_booking_filter_saved_filter_list').val());
        });

        //Cancel filter
        $('#facility-booking-filter-cancel-btn').on('click', function () {
            that.clearFilter();
            $("#facility-booking-filter-trigger-dropdown").trigger("click");
        });

        //Load saved filter
        $('#facility_booking_filter_privacy_type').on('change', function () {
            that.clearFilterFields();
            if ($('#facility_booking_filter_privacy_type').val() != '') {
                that.loadSavedFilter();
            } else {
                $("#facility_booking_filter_saved_filter_list").empty();
                $("#facility_booking_filter_saved_filter_list").append($('<option></option>').val('').html('Select Filter'));
                $("#facility_booking_filter_saved_filter_list option:first").attr('selected', 'selected');
            }
        });

        //Filter populate saved filter
        $('#facility-booking-filter-go-btn').on('click', function () {
            let notCloseOnUncheckedCheckbox = true;
            that.applyFacilityBookingFilter(notCloseOnUncheckedCheckbox);
        });

        $('#facility_booking_filter_saved_filter_list').on('change', async function () {
            let $selectFilter = $('#facility_booking_filter_saved_filter_list option:selected');
            if (typeof $selectFilter.attr('data-filter-json') == 'undefined' || $("#facility_booking_filter_privacy_type").val() == '') {
                that.clearFilterFields();
                return false;
            }
            let filterJson = $selectFilter.attr('data-filter-json');
            await that.applyExistingFilter(JSON.parse(filterJson), true);
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
                    $('#facility-booking-filter-facility-booking-type-filter-data').empty();
                },
                success: function (response) {
                    response.forEach(element => {
                        let $option = $('<option></option>').attr('value', element['FT_FacilityType']).attr('data-id', element['FT_FacilityTypeID']).text(element['FT_FacilityType']);
                        if (selected.includes(element['FT_FacilityType'])) {
                            $option.attr('selected', true);
                        }
                        $('#facility-booking-filter-facility-booking-type-filter-data').append(
                            $option
                        );
                    });
                    $('#facility-booking-filter-facility-booking-type-filter-data').trigger("chosen:updated");
                    that.applyChosenA11y('facility-booking-filter-facility-booking-type-filter-data');
                    that.setFilterSubTypeSelect(filterSubTypesSelected).then(() => resolve()).catch(reject);
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
     * Facility Sub Type
     */
    setFilterSubTypeSelect: function (selected = []) {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            // preserve current selections (if user had selected sub-types)
            let $subTypeSelect = $('#facility-booking-filter-facility-booking-sub-type-filter-data');
            let previousSelected = $subTypeSelect.val() ? $subTypeSelect.val().slice() : [];
            $subTypeSelect.empty();
            let facilityTypeIds = [];
            $('#facility-booking-filter-facility-booking-type-filter-data option:selected').each(function (index, element) {
                facilityTypeIds.push($(element).attr('data-id'));
            });
            // If no facility types selected, clear sub-type values and update chosen
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
                    $('#facility-booking-filter-facility-booking-sub-type-filter-data').empty();
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
                    that.applyChosenA11y('facility-booking-filter-facility-booking-sub-type-filter-data');
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
     * Equipment drop down
     */
    setFilterEquipmentSelect: function (selected = []) {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            $.ajax({
                url: that.filterEquipmentDataUrl,
                method: 'GET',
                beforeSend: function () {
                    $('#facility-booking-filter-facility-booking-equipment-filter-data').empty();
                },
                success: function (response) {
                    response.forEach(element => {
                        let $option = $('<option></option>').attr('value', element['EQ_Equipment']).text(element['EQ_Equipment']);
                        if (selected.includes(element['EQ_Equipment'])) {
                            $option.attr('selected', true);
                        }
                        $('#facility-booking-filter-facility-booking-equipment-filter-data').append(
                            $option
                        );
                    });
                    $('#facility-booking-filter-facility-booking-equipment-filter-data').trigger("chosen:updated");
                    that.applyChosenA11y('facility-booking-filter-facility-booking-equipment-filter-data');
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
     * Service drop down
     */
    setFilterSerivceSelect: function (selected = []) {
        let that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            $.ajax({
                url: that.filterServiceDataUrl,
                method: 'GET',
                beforeSend: function () {
                    $('#facility-booking-filter-facility-booking-service-filter-data').empty();
                },
                success: function (response) {
                    response.forEach(element => {
                        let $optionAttr = $('<option></option>').attr('value', element['SR_Service']).text(element['SR_Service']);
                        if (selected.includes(element['SR_Service'])) {
                            $optionAttr.attr('selected', true);
                        }
                        $('#facility-booking-filter-facility-booking-service-filter-data').append(
                            $optionAttr
                        );
                    });
                    $('#facility-booking-filter-facility-booking-service-filter-data').trigger("chosen:updated");
                    that.applyChosenA11y('facility-booking-filter-facility-booking-service-filter-data');
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
     * Apply facility filter
    */
    applyFacilityBookingFilter: function (notCloseOnUncheckedCheckbox = false) {
        let that = this; // NOSONAR javascript:S7740

        let orCondition = $('#filter-option-or').is(":checked");

        let showFacilityIds = [];
        let notFoundFacilityIds = [];
        let conditionExist = false;

        $('.facility-row').show();

        //Filter facility name
        if ($('#filter_facility_booking_facility_name_data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionText($(element).attr('data-facility-name'), $('#filter_facility_booking_facility_name_condition').val(), $('#filter_facility_booking_facility_name_data').val());

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'))
                }
            });
        }

        //Filter provider name
        if ($('#filter_facility_booking_provider_name_data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionText($(element).attr('data-facility-provider-name'), $('#filter_facility_booking_provider_name_condition').val(), $('#filter_facility_booking_provider_name_data').val());

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Filter Booking Title
        if ($('#filter_facility_booking_booking_title_data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let bookingTitles = [];
                $(element).find('.booking-detail-container').each(function (index2, element2) {
                    bookingTitles.push($(element2).attr('data-facility-booking-title'));
                });
                let match = that.filterDataByConditionText(bookingTitles, $('#filter_facility_booking_booking_title_condition').val(), $('#filter_facility_booking_booking_title_data').val());

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Filter by area
        if ($('#filter_facility_booking_area_data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionSelect($(element).attr('data-facility-area-owner-id'), $('#filter_facility_booking_area_condition').val(), $('#filter_facility_booking_area_data').val().join(','));

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Filter default booking type
        if ($('#facility-booking-filter-default-booking-type-filter-data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionSelect($(element).attr('data-facility-default-booking-type'), $('#facility-booking-filter-default-booking-type-filter-condition').val(), $('#facility-booking-filter-default-booking-type-filter-data').val().join(','));

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        // Accessible Type
        if ($('#facility-booking-filter-accessible-type-filter-data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionSelect($(element).attr('data-facility-accessible'), $('#facility-booking-filter-accessible-type-filter-condition').val(), $('#facility-booking-filter-accessible-type-filter-data').val().join(','));

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Facility Type
        if ($('#facility-booking-filter-facility-booking-type-filter-data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionSelect($(element).attr('data-facility-type'), $('#facility-booking-filter-facility-booking-type-filter-condition').val(), $('#facility-booking-filter-facility-booking-type-filter-data').val().join(','));

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Facility Sub type
        if ($('#facility-booking-filter-facility-booking-sub-type-filter-data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionSelect($(element).attr('data-facility-sub-type'), $('#facility-booking-filter-facility-booking-sub-type-filter-condition').val(), $('#facility-booking-filter-facility-booking-sub-type-filter-data').val().join(','));

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Filter location
        if ($('#filter_facility_booking_location_data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionText($(element).attr('data-facility-location'), $('#filter_facility_booking_location_condition').val(), $('#filter_facility_booking_location_data').val());

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Filter service
        if ($('#facility-booking-filter-facility-booking-service-filter-data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionSelect($(element).attr('data-facility-services'), $('#facility-booking-filter-facility-booking-service-filter-condition').val(), $('#facility-booking-filter-facility-booking-service-filter-data').val().join(','));

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Filter Equipment
        if ($('#facility-booking-filter-facility-booking-equipment-filter-data').val() != '') {
            conditionExist = true;
            $('.facility-row').each(function (index, element) {
                let match = that.filterDataByConditionSelect($(element).attr('data-facility-equipments'), $('#facility-booking-filter-facility-booking-equipment-filter-condition').val(), $('#facility-booking-filter-facility-booking-equipment-filter-data').val().join(','));

                if (!orCondition && !match) { // And condition
                    notFoundFacilityIds.push($(element).attr('data-facility-id'));
                }

                if (match) {
                    showFacilityIds.push($(element).attr('data-facility-id'));
                }
            });
        }

        //Hide facility rows based on filter
        $('.facility-row').each(function (index, element) {
            if (!conditionExist) {
                $(element).show();
                return true;
            }
            if (!showFacilityIds.includes($(element).attr('data-facility-id')) || notFoundFacilityIds.includes($(element).attr('data-facility-id'))) {
                $(element).hide();
            }
        });

        let visibleRows = $('.facility-row:visible').length;
        if (visibleRows === 0 && conditionExist) {
            if (!$('#no-matching-records-row').length) {
                let $noMatchRow = $('<tr id="no-matching-records-row"><td colspan="16" class="dt-empty">No matching records found</td></tr>');
                $('#weekly-view-time-line-table-body').append($noMatchRow);
            }
        } else {
            $('#no-matching-records-row').remove();
        }

        if (conditionExist) {
            $(".clearFilterIcon").show();
        }
        else if ($('input[name="show_only_booking_status[]"]:checked').length == 0) {
            $(".clearFilterIcon").hide();
        }
        if ($('#facility-booking-filter-content').is(":visible") && notCloseOnUncheckedCheckbox == false) {
            $("#facility-booking-filter-trigger-dropdown").trigger("click");
        }
        //Reset position
        that.setElementCss();
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
        let searchDataArray = searchData.split(',').map(s => s.trim().toLowerCase());
        let rowValues = Array.isArray(rowData) ? rowData.map(r => r.toLowerCase()) : [rowData.toLowerCase()];

        let containsMatch = searchDataArray.some(searchD =>
            rowValues.some(rowVal => new RegExp(searchD, "i").test(rowVal))
        );
        let notContainsMatch = searchDataArray.every(searchD =>
            rowValues.every(rowVal => !new RegExp(searchD, "i").test(rowVal))
        );
        let exactMatch = searchDataArray.every(searchD =>
            rowValues.includes(searchD)
        );
        let exactSomeMatch = searchDataArray.some(searchD =>
            rowValues.includes(searchD)
        );

        let matchFound = true;
        //filter type
        if (condition === "*" && !containsMatch) {
            matchFound = false;
        } else if (condition === "!" && !notContainsMatch) {
            matchFound = false;
        } else if (condition === ";" && !exactSomeMatch) {
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
        let filterData = that.fomatFormData($("#facility-booking-filter-form").serializeArray());
        filterData['facility_booking_filter_privacy_type'] = privacyType;
        delete filterData['_token'];
        $('#facility_booking_filter_privacy_type').val(privacyType);
        $.ajax({
            url: that.filterSaveUrl,
            beforeSend: function () {
                $('.validation-error-text').remove();
            },
            data: {
                'filterData': filterData,
                'filterName': $('input[name="facility_booking_filter_new_name"]').val(),
                'filterId': $('#facility_booking_filter_saved_filter_list option:selected').val(),
                'filterPrivacyType': privacyType,
                'filterType': 'facilityBookingView',
                '_token': $('#facility-booking-filter-form input[name="_token"]').val()
            },
            method: 'POST',
            success: function (response) {
                toastr.success(response['message']);
                that.loadSavedFilter(response['filterId']);
            },
            error: function (xhr, status, error) {
                $('.validation-error-text').remove();
                let data = JSON.parse(xhr.responseText);
                errors = data.errors;
                Object.keys(errors).forEach((key) => {
                    errors[key].forEach((message) => {
                        if (key == 'filterName') {
                            $('input[name="facility_booking_filter_new_name"]').after('<br class="validation-error-text">', $('<span class="validation-error-text"></span>').text(message));
                        }
                    });
                });
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
                that.storeSettingsLocalStorage();
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
                    'filterPrivacyType': $('#facility_booking_filter_privacy_type').val() != '' ? $('#facility_booking_filter_privacy_type').val() : 'private',
                    'filterType': 'facilityBookingView'
                },
                method: 'GET',
                success: async function (response) {
                    $('#facility_booking_filter_saved_filter_list').empty();
                    $('#facility_booking_filter_saved_filter_list').append($('<option></option>').val('').html('Select Filter'));
                    Object.keys(response).forEach((key) => {
                        let $option = $('<option></option>').attr({
                            'value': response[key]['FLR_FilterID'],
                            'data-filter-json': response[key]['FLR_FilterData_JSON']
                        }).text(response[key]['FLR_FilterName']);
                        if (selected == response[key]['FLR_FilterID']) {
                            $option.attr('selected', true);
                        }
                        $('#facility_booking_filter_saved_filter_list').append(
                            $option
                        );
                    });
                    if (selected != '') { //Load saved filter
                        let $selectFilter = $("#facility_booking_filter_saved_filter_list option:selected");
                        if (typeof $selectFilter.attr("data-filter-json") != "undefined") {
                            let filterJson = $selectFilter.attr("data-filter-json");
                            await that.applyExistingFilter(JSON.parse(filterJson), true);
                            that.storeFilterSettings();
                            $('#facility-booking-filter-go-btn').trigger("click");
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

            if (['facility_booking_filter_saved_filter_list', 'facility_booking_filter_privacy_type'].includes(key)) {
                continue;
            }

            if ($($ele).attr('type') == 'radio') {
                $('input[name="' + key + '"][value="' + filterData[key] + '"]').prop('checked', true);
            } else if ($($ele).is('input')) {
                $ele.val(filterData[key]);
            } else if ($($ele).is('select')) {
                switch (key) {
                    case 'filter_facility_booking_service_data':
                        await that.setFilterSerivceSelect(filterData[key]);
                        break;
                    case 'filter_facility_booking_type_data':
                        filterTypes = filterData[key] != null ? filterData[key] : [];
                        break;
                    case 'filter_facility_booking_sub_type_data':
                        filterSubTypes = filterData[key] != null ? filterData[key] : [];
                        break;
                    case 'filter_facility_booking_equipment_data':
                        await that.setFilterEquipmentSelect(filterData[key]);
                        break;
                    case 'filter_facility_booking_accessible_type_data':
                        $($ele).val(filterData[key]).trigger("chosen:updated");
                        break;
                    default:
                        if ($($ele).hasClass('chosen-select') || $($ele).data('chosen')) {
                            $($ele).val(filterData[key]).trigger("chosen:updated");
                        } else {
                            $($ele).val(filterData[key]);
                        }
                        break;
                }
            }
        };
        if (filterTypes.length > 0) {
            await that.setFilterTypeSelect(filterTypes, filterSubTypes);
        }
        if (!remainVisible) {
            let notCloseOnUncheckedCheckbox = true;
            that.applyFacilityBookingFilter(notCloseOnUncheckedCheckbox);
        }
    },
    /**
     * On page refresh load previous filters
     */
    loadPreviousFilter: async function () {
        let that = this; // NOSONAR javascript:S7740

        //Check page loaded from booking admin page
        let facilityBookingAdminFilter = JSON.parse(localStorage.getItem(that.facilityBookingAdminFilterSettingStorageName));
        if (facilityBookingAdminFilter != null) {
            $('#filter_facility_booking_booking_title_condition').val(';');
            $('#filter_facility_booking_booking_title_data').val(facilityBookingAdminFilter['facility_bookings']);
            localStorage.removeItem(that.facilityBookingAdminFilterSettingStorageName);
            localStorage.removeItem(that.facilityBookingFilterLocalStorageName); // After loading all setting delete the storage
            that.applyFacilityBookingFilter();
            return;
        }
        if ($('#include-cancelled-booking').is(':checked') || $('input[name="show_only_booking_status[]"]:checked').length > 0) {
            $('.clearFilterIcon').show();
        }
        let filterData = JSON.parse(localStorage.getItem(that.facilityBookingFilterLocalStorageName));
        if (filterData == null) {
            return;
        }
        if (filterData['saved_filter_id'].length > 0) {
            $('#facility_booking_filter_privacy_type').val(filterData['filter_privacy_type']);
            await that.loadSavedFilter(filterData['saved_filter_id']);
        } else {
            await that.applyExistingFilter(filterData['filter_data']);
        }
    },
    /**
     * Set timeline container min-height to "bottom of viewport"
     * so layout doesn't collapse while AJAX/rebuild runs.
     */
    updateTimelineMinHeight: function () {
        try {
            const el = document.getElementById('weekly-view-time-line-container');
            if (!el) return;

            const rect = el.getBoundingClientRect();
            const gutter = 15;
            const available = Math.floor(window.innerHeight - rect.top - gutter);

            const minH = Math.max(200, available);

            el.style.setProperty('--fb-timeline-minh', `${minH}px`);
        } catch (e) {
            /* ignore */
        }
    },
    /**
     * Set scroll position
     */
    setScrollPosition: function () {
        const that = this; // NOSONAR javascript:S7740

        const $timeline = $('#weekly-view-time-line-container');
        const $wrap = $('#booking-weekly-view-container');

        const keyTimeline = 'weekly_view_time_line_container_scroll_v2';
        const keyWrap = 'booking_weekly_view_container_scroll_v2';
        const keyWindow = 'window_scroll_v2';

        if (that.__scrollHandlerBound) return;
        that.__scrollHandlerBound = true;

        function storeKey(key, obj) {
            try { localStorage.setItem(key, JSON.stringify(obj)); } catch (e) { }
        }

        function computeRatios(el, targetName) {
            const maxTop = Math.max(0, (el.scrollHeight || 0) - (el.clientHeight || 0));
            const maxLeft = Math.max(0, (el.scrollWidth || 0) - (el.clientWidth || 0));
            const topPx = el.scrollTop || 0;
            const leftPx = el.scrollLeft || 0;
            return {
                ts: Date.now(),
                target: targetName,
                topRatio: maxTop > 0 ? (topPx / maxTop) : 0,
                leftRatio: maxLeft > 0 ? (leftPx / maxLeft) : 0,
                topPx,
                leftPx
            };
        }

        if ($timeline.length) {
            $timeline.on('scroll.fbScroll', function () {
                if (that.__suppressScrollStore) return;
                const el = $timeline[0];
                const snap = computeRatios(el, 'weekly-view-time-line-container');
                storeKey(keyTimeline, snap);

                that.__lastUserScrollSnapshot = Object.assign({}, snap);
            });
        }

        if ($wrap.length) {
            $wrap.on('scroll.fbScroll', function () {
                if (that.__suppressScrollStore) return;
                const el = $wrap[0];
                const snap = computeRatios(el, 'booking-weekly-view-container');
                storeKey(keyWrap, snap);

                // ✅ remember last known good user scroll
                that.__lastUserScrollSnapshot = Object.assign({}, snap);
            });
        }

        $(window).on('scroll.fbScroll', function () {
            if (that.__suppressScrollStore) return;
            storeKey(keyWindow, {
                ts: Date.now(),
                target: 'window',
                topRatio: 0,
                leftRatio: 0,
                topPx: window.scrollY || 0,
                leftPx: window.scrollX || 0
            });
        });
    },
    /**
     * Store browser settings
     */
    storeFilterSettings: function () {
        let that = this; // NOSONAR javascript:S7740
        let data = {};
        data['filter_privacy_type'] = $('#facility_booking_filter_privacy_type').val();
        data['saved_filter_id'] = $('#facility_booking_filter_saved_filter_list').val();
        data['filter_data'] = that.fomatFormData($("#facility-booking-filter-form").serializeArray());
        localStorage.setItem(that.facilityBookingFilterLocalStorageName, JSON.stringify(data));
    },
    /**
     * Clear the fields
     */
    clearFilterFields: function () {
        $('#filter_facility_booking_facility_name_data').val('');
        $('#filter_facility_booking_provider_name_data').val('');
        $('#filter_facility_booking_booking_title_data').val('');
        $('#filter_facility_booking_area_data').val('').trigger("chosen:updated");
        $('#facility-booking-filter-default-booking-type-filter-data').val('').trigger("chosen:updated");
        $('#facility-booking-filter-accessible-type-filter-data').val('').trigger("chosen:updated");
        $('#facility-booking-filter-facility-booking-type-filter-data').val('').trigger("chosen:updated");
        $('#facility-booking-filter-facility-booking-sub-type-filter-data').val('').trigger("chosen:updated");
        $('#filter_facility_booking_location_data').val('');
        $('#facility-booking-filter-facility-booking-service-filter-data').val('').trigger("chosen:updated");
        $('#facility-booking-filter-facility-booking-equipment-filter-data').val('').trigger("chosen:updated");
        $('#facility_booking_filter_new_name').val('').trigger("chosen:updated");
    }
}

module.exports = FacilityBookingFilter;
