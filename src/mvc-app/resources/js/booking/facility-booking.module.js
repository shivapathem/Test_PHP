/**
 * Facility booking JavaScript
 *
 * @param {Object} p parameters
 */
let FacilityBooking = function (p) {
    this.getFacilityBookingsUrl = p.getFacilityBookingsUrl;
    this.createFacilityBookingRequestFormUrl = p.createFacilityBookingRequestFormUrl;
    this.createFacilityBookingRecordFormUrl = p.createFacilityBookingRecordFormUrl;
    this.storeFacilityBookingRequestUrl = p.storeFacilityBookingRequestUrl;
    this.editFacilityBookingFormUrl = p.editFacilityBookingFormUrl;
    this.editFacilityBookingUpdateUrl = p.editFacilityBookingUpdateUrl;
    this.cancelFacilityBookingUrl = p.cancelFacilityBookingUrl;
    this.cancelFacilityBookingPopUpUrl = p.cancelFacilityBookingPopUpUrl;
    this.cancelFacilityBookingDataUrl = p.cancelFacilityBookingDataUrl;
    this.reinstateFacilityBookingPopUpUrl = p.reinstateFacilityBookingPopUpUrl;
    this.reinstateFacilityBookingDataUrl = p.reinstateFacilityBookingDataUrl;
    this.copyFacilityBookingFormUrl = p.copyFacilityBookingFormUrl;
    this.moveFacilityBookingFormUrl = p.moveFacilityBookingFormUrl;
    this.userSetting = p.userSetting;
    this.storeFacilityBookingRecordUrl = p.storeFacilityBookingRecordUrl;
    this.moveFacilityBookingUrl = p.moveFacilityBookingUrl;
    this.showFacilityBookingUrl = p.showFacilityBookingUrl;
    this.createBookerNoteFormUrl = p.createBookerNoteFormUrl;
    this.editBookerNoteFormUrl = p.editBookerNoteFormUrl;
    this.storeBookerNoteUrl = p.storeBookerNoteUrl;
    this.historyFacilityBookingDataUrl = p.historyFacilityBookingDataUrl;
    this.updateBookerNoteUrl = p.updateBookerNoteUrl;
    this.historySchedulerNoteDataUrl = p.historySchedulerNoteDataUrl;
    this.deleteFacilityBookingUrl = p.deleteFacilityBookingUrl;
    this.schedulerNoteDeleteUrl = p.schedulerNoteDeleteUrl;
    this.recurrenceAvailableCountUrl = p.recurrenceAvailableCountUrl;
    this.deleteUrl = p.deleteUrl;
    this.copyBookingLocalStorageName = 'copy_booking_detail';
    this.moveBookingLocalStorageName = 'move_booking_detail';
    this.facilityBookingFilterInstance = null;
    this.filterBookingAdminInstance = null;
    this.token = p.token;
    this.administratorPage = p.administratorPage;
    this.oldFormData = {};
    this.clickPositionDateTime = null;
    this.constants = p.constants;
    this.init();
};

FacilityBooking.prototype = {
    /**
     * Context menus
     */
    init: function () {
        let that = this; // NOSONAR javascript:S7740

        that.initModals();

        that.initDoubleClick();

        that.initTimelineGridAccessibility();
    },
    initTimelineGridAccessibility: function () {
        let that = this; // NOSONAR javascript:S7740
        if (that.__fbTimelineGridA11yInit) {
            return;
        }
        that.__fbTimelineGridA11yInit = true;

        const scheduleRefresh = function () {
            clearTimeout(that.__fbTimelineGridA11yTimer);
            that.__fbTimelineGridA11yTimer = setTimeout(function () {
                that.refreshTimelineGridAccessibility();
            }, 0);
        };

        that.__scheduleTimelineGridA11yRefresh = scheduleRefresh;

        const timelineContainer = document.getElementById('weekly-view-time-line-container');
        if (timelineContainer && globalThis.MutationObserver) {
            that.__fbTimelineGridA11yObserver = new MutationObserver(function () {
                scheduleRefresh();
            });
            that.__fbTimelineGridA11yObserver.observe(timelineContainer, { childList: true, subtree: true });
        }

        $('#view-days').on('change.fbTimelineGridA11y', function () {
            scheduleRefresh();
        });
        $('#previous-day-btn, #next-day-btn').on('click.fbTimelineGridA11y', function () {
            scheduleRefresh();
        });
        that.__onDayNavArrowDownCapture = function (event) {
            const key = event.key || '';
            const code = event.keyCode || event.which || 0;
            const isArrowDown = key === 'ArrowDown' || code === 40;
            if (!isArrowDown) {
                return;
            }

            const target = event.target;
            const targetId = target?.id ?? '';
            const active = document.activeElement;
            const activeId = active?.id ?? '';

            const isDayNavTarget =
                targetId === 'previous-day-btn' || targetId === 'next-day-btn' || targetId === 'view-date-calender-icon' ||
                activeId === 'previous-day-btn' || activeId === 'next-day-btn' || activeId === 'view-date-calender-icon';

            if (!isDayNavTarget) {
                return;
            }

            const moved = that.focusFirstTimelineGridCell();
            if (moved) {
                event.preventDefault();
                event.stopPropagation();
            }
        };
        document.addEventListener('keydown', that.__onDayNavArrowDownCapture, true);

        that.__onTimelineGridCellFocus = function (event) {
            const target = event?.target;
            if (!target?.classList?.contains('fb-period-cell')) {
                return;
            }

            const row = target.closest('.facility-row');
            const facilityName = row?.dataset?.facilityName?.trim() || 'Unknown facility';

            setTimeout(function () {
                target.removeAttribute('aria-describedby');
                target.setAttribute('aria-label', that.buildTimelineGridCellLabel(target, facilityName));
            }, 0);
        };
        document.addEventListener('focusin', that.__onTimelineGridCellFocus, true);

        scheduleRefresh();
    },
    refreshTimelineGridAccessibility: function () {
        let that = this; // NOSONAR javascript:S7740
        const gridEl = document.getElementById('weekly-view-time-line-table');
        if (!gridEl) {
            return;
        }

        that.updateTimelineDateBanner(gridEl);
        that.alignDateLabelWithFacilityColumn(gridEl);

        gridEl.setAttribute('role', 'grid');
        gridEl.setAttribute('aria-readonly', 'true');
        gridEl.setAttribute('aria-label', 'Facility bookings table. Use arrow keys to move around the grid.');
        gridEl.removeAttribute('aria-labelledby');

        const rows = Array.from(gridEl.querySelectorAll('.facility-row'));
        gridEl.setAttribute('aria-rowcount', String(rows.length + 1));

        if (that.facilityBookingFilterInstance) {
            that.facilityBookingFilterInstance.__fbA11yUpdateCell = function () { };
        }

        rows.forEach(function (row) {
            row.setAttribute('role', 'row');
            const facilityName = row.dataset.facilityName?.trim() || 'Unknown facility';
            const rowHeader = row.querySelector('.booking-timeline-name-td');
            if (rowHeader) {
                rowHeader.setAttribute('role', 'presentation');
                rowHeader.setAttribute('aria-hidden', 'true');
            }

            row.querySelectorAll('.fb-day-overlay, .booking-detail-container, .booker-note-detail-container').forEach(function (overlayEl) {
                overlayEl.setAttribute('aria-hidden', 'true');
            });

            const cells = row.querySelectorAll('.fb-period-cell');

            cells.forEach(function (cell) {
                cell.setAttribute('role', 'gridcell');
                cell.removeAttribute('aria-describedby');
                cell.removeAttribute('title');
                cell.setAttribute('aria-label', that.buildTimelineGridCellLabel(cell, facilityName));
            });
        });

        gridEl.querySelectorAll('#weekly-view-time-line-table-head [role="columnheader"]').forEach(function (headerCell) {
            headerCell.setAttribute('role', 'presentation');
            headerCell.setAttribute('aria-hidden', 'true');
        });
        gridEl.querySelectorAll('.booking-period-cell').forEach(function (headerCell) {
            headerCell.setAttribute('role', 'presentation');
            headerCell.setAttribute('aria-hidden', 'true');
        });

        if (!gridEl.__fbGridArrowBridgeBound) {
            gridEl.__fbGridArrowBridgeBound = true;
            gridEl.addEventListener('keydown', function (event) {
                const key = event.key || '';
                if (key !== 'ArrowDown' && key !== 'ArrowUp' && key !== 'ArrowLeft' && key !== 'ArrowRight') {
                    return;
                }

                const target = event.target;
                const isGridCell = !!target?.classList?.contains('fb-period-cell');

                if (isGridCell) {
                    return;
                }

                const moved = that.focusFirstTimelineGridCell();
                if (moved) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);
        }

    },
    updateTimelineDateBanner: function (gridEl) {
        const dateRangeEl = document.getElementById('date-range-section');
        if (!dateRangeEl || !gridEl) {
            return;
        }
        const dateValues = Array.from(
            gridEl.querySelectorAll('.fb-period-cell[data-date]')
        ).map(el => el.dataset.date?.trim())
            .filter(Boolean);


        if (!dateValues.length) {
            return;
        }

        const uniqueDates = Array.from(new Set(dateValues)).sort((a, b) => a.localeCompare(b));

        const first = uniqueDates[0];
        const last = uniqueDates.at(-1);

        dateRangeEl.textContent = (first === last)
            ? this.formatDate(first)
            : (this.formatDate(first) + ' to ' + this.formatDate(last));
    },
    alignDateLabelWithFacilityColumn: function (gridEl) {
        const dateLabelSection = document.getElementById('date-label-section');
        if (!dateLabelSection || !gridEl) {
            return;
        }

        const nameW = Number.parseFloat(
            getComputedStyle(gridEl).getPropertyValue('--nameW')
        ) || 260;

        const widthPx = Math.max(120, Math.round(nameW)) + 'px';
        dateLabelSection.style.width = widthPx;
        dateLabelSection.style.minWidth = widthPx;
    },
    focusFirstTimelineGridCell: function () {
        const gridEl = document.getElementById('weekly-view-time-line-table');
        if (!gridEl) {
            return false;
        }

        const cells = Array.from(gridEl.querySelectorAll('.fb-period-cell'));
        if (!cells.length) {
            return false;
        }

        let firstVisibleCell = cells.find(function (cell) {
            return !!(cell.offsetWidth || cell.offsetHeight || cell.getClientRects().length);
        });
        if (!firstVisibleCell) {
            firstVisibleCell = cells[0];
        }

        cells.forEach(function (cell) {
            cell.setAttribute('tabindex', '-1');
        });
        firstVisibleCell.setAttribute('tabindex', '0');

        const row = firstVisibleCell.closest('.facility-row');

        const facilityName = row?.dataset?.facilityName?.trim() || 'Unknown facility';

        firstVisibleCell.removeAttribute('aria-describedby');
        firstVisibleCell.setAttribute('aria-label', this.buildTimelineGridCellLabel(firstVisibleCell, facilityName));
        firstVisibleCell.focus({ preventScroll: true });
        return true;
    },
    formatDate: function (value) {
        const m = moment(value, 'YYYY-MM-DD', true);
        return m.isValid() ? m.format('DD/MM/YYYY') : value;
    },
    buildTimelineGridCellLabel: function (cellEl, facilityName) {
        let that = this; // NOSONAR javascript:S7740
        const period = cellEl?.dataset?.period?.trim() || 'Unknown time';
        const bookings = that.getTimelineBookingsForGridCell(cellEl);
        const row = cellEl ? cellEl.closest('.facility-row') : null;
        const facilityLocation = row?.dataset?.facilityLocation?.trim() || '';
        const facilityText = facilityLocation
            ? ('Facility Name ' + facilityName + ' Location ' + facilityLocation)
            : ('Facility Name ' + facilityName);

        if (!bookings.length) {
            return period + '. ' + facilityText + '. No bookings.';
        }

        const bookingPrefix = bookings.length === 1 ? '1 booking.' : (bookings.length + ' bookings.');
        const bookingText = bookings.map(function (booking, index) {
            return 'Booking ' + (index + 1) + '. Booking Title ' + booking.title + ', Booking Duration ' + booking.startTime + ' to ' + booking.endTime + ', Status ' + booking.status + '.';
        }).join(' ');

        return period + '. ' + facilityText + '. ' + bookingPrefix + ' ' + bookingText;
    },
    getTimelineBookingsForGridCell: function (cellEl) {
        let that = this; // NOSONAR javascript:S7740
        const slot = that.getTimelineGridCellInterval(cellEl);
        if (!slot) {
            return [];
        }

        const dayWrap = cellEl.closest('.fb-day-wrap');
        if (!dayWrap) {
            return [];
        }

        const overlay = dayWrap.querySelector('.fb-day-overlay');
        if (!overlay) {
            return [];
        }

        const bookings = [];
        overlay.querySelectorAll('.booking-detail-container').forEach(function (bookingEl) {
            if (!bookingEl || bookingEl.offsetParent === null) {
                return;
            }

            const cellStart = that.parseTimelineDateTimeAllow24(bookingEl.dataset.bookingCellPositionStartDateTime);
            const cellEnd = that.parseTimelineDateTimeAllow24(bookingEl.dataset.bookingCellPositionEndTimeDateTime);
            if (!cellEnd?.isAfter(slot.start) || !cellStart?.isBefore(slot.end)) {
                return;
            }

            const fullStart = that.parseTimelineDateTimeAllow24(bookingEl.dataset.bookingStartTime);
            const fullEnd = that.parseTimelineDateTimeAllow24(bookingEl.dataset.bookingEndTime);
            const title = (bookingEl.dataset.facilityBookingTitle || bookingEl.getAttribute('title') || 'Booking').trim();
            const status = (bookingEl.dataset.facilityBookingStatus || 'unknown').trim();

            bookings.push({
                title: title,
                startSort: fullStart ? fullStart.valueOf() : cellStart.valueOf(),
                startTime: fullStart?.isValid() ? fullStart.format('HH:mm') : 'Unknown',
                endTime: fullEnd?.isValid() ? fullEnd.format('HH:mm') : 'Unknown',
                status: status
            });
        });

        bookings.sort(function (a, b) {
            return a.startSort - b.startSort;
        });

        return bookings;
    },
    getTimelineGridCellInterval: function (cellEl) {
        let that = this; // NOSONAR javascript:S7740
        const date = cellEl.dataset.date;
        const period = cellEl.dataset.period;
        if (!date || !period) {
            return null;
        }

        const start = that.parseTimelineDateTimeAllow24(date + ' ' + period);
        if (!start?.isValid()) {
            return null;
        }

        const minutes = Number.parseInt($('#view-days').val(), 10) === 1 ? 60 : 360;
        const end = moment(start).add(minutes, 'minutes');
        const dayEnd = moment(date + ' 00:00', 'YYYY-MM-DD HH:mm').add(1, 'day');

        return {
            start: start,
            end: end.isAfter(dayEnd) ? dayEnd : end
        };
    },
    parseTimelineDateTimeAllow24: function (dateTime) {
        if (!dateTime) {
            return null;
        }

        const str = String(dateTime).trim();
        const re = /^(\d{4}-\d{2}-\d{2})\s+24:00$/;
        const m = re.exec(str);
        if (m) {
            return moment(m[1] + ' 00:00', 'YYYY-MM-DD HH:mm').add(1, 'day');
        }

        const strict = moment(str, 'YYYY-MM-DD HH:mm', true);
        if (strict.isValid()) {
            return strict;
        }

        const fallback = moment(str);
        return fallback.isValid() ? fallback : null;
    },
    /**
     * Init Double click
     * 
     */
    initModals: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#facility-booking-cancellation').dialog({
            title: 'Cancel Facility Booking',
            autoOpen: false,
            modal: true,
            width: "auto",
            height: "auto",
            open: function () {
                that.resetCancelBookingDialog();
            }
        });

        $('#facility-booking-deletion').dialog({
            title: 'Delete Facility Booking',
            autoOpen: false,
            modal: true,
            width: "auto",
            height: "auto",
            closeOnEscape: false,
            open: function () {
                $(this).dialog('widget').find('.ui-dialog-titlebar-close').hide();
            }
        });

        //Delete scheduler note
        $('#delete-scheduler-note-modal').dialog({
            title: 'Delete Scheduler Note',
            autoOpen: false,
            modal: true,
            width: "auto",
            height: "120"
        });

        $('#delete-scheduler-note-cancel').on('click', function () {
            $('#delete-scheduler-note-modal').dialog('close');
        });

        //Reinstate
        $('#facility-booking-reinstate-modal').dialog({
            title: 'Reinstate Facility Booking',
            autoOpen: false,
            modal: true,
            width: "50%",
            height: "auto"
        });
    },
    /**
     * Init context menu
     */
    initContextMenu: function () {
        let that = this; // NOSONAR javascript:S7740
        //Right click options
        let bookingAvailableSettings = {
            'add_booking_request': {
                name: "Add Booking Request",
                icon: "add",
                visible: function (key, opt) {
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $td = opt.$trigger;
                    let access = true;
                    let accessRole = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                    if (accessRole && $tr.attr('data-facility-default-booking-type') == that.constants.managed_facility) {
                        access = false;
                    }

                    //Option not required for unavailable grid for requestors
                    if (($($td).hasClass('facility-unavailable-container')
                        && !accessRole)
                        || ($($td).hasClass('facility-unavailable-container') && $tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility)
                        || moment($($tr).attr('data-facility-active-date')).isAfter(moment($td.attr('data-date')), 'day')  //Active from
                    ) {
                        return false;
                    }

                    if ($tr.attr("data-facility-archive")) {
                        let archive_date = moment($tr.attr("data-facility-archive"));
                        let booking_date = moment($td.attr('data-date'));
                        if (booking_date >= archive_date) {
                            access = false;
                        }
                    }

                    if (!moment($td.attr('data-date')).isSameOrAfter(moment(), 'day')) {
                        access = false;
                    }
                    return access;
                },
                callback: function (key, opt) {
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $td = opt.$trigger;
                    if ($tr.attr('data-facility-default-booking-type') == that.constants.managed_facility && $tr.attr('data-facility-allow-booking-request') == 0) {
                        that.facilityCanNotBeBookedPopUp();
                        return true;
                    }
                    that.openFacilityBookingForm($($td).attr('data-facility-id'), $($td).attr('data-date'));
                }
            },
            'add_booking_record': {
                name: "Add Booking Record",
                icon: "fa-book",
                visible: function (key, opt) {
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $td = opt.$trigger;

                    let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                    if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility) {
                        access = false
                    }

                    if ($tr.attr("data-facility-archive")) {
                        let archive_date = moment($tr.attr("data-facility-archive"));
                        let booking_date = moment($td.attr('data-date'));
                        if (booking_date >= archive_date) {
                            access = false;
                        }
                    }

                    if (!moment($td.attr('data-date')).isSameOrAfter(moment(), 'day')) {
                        access = false;
                    }

                    //Active from
                    if (moment($($tr).attr('data-facility-active-date')).isAfter(moment($td.attr('data-date')), 'day')) {
                        access = false;
                    }

                    return access;
                },
                callback: function (key, opt) {
                    let $td = opt.$trigger;
                    that.openFacilityBookingForm($($td).attr('data-facility-id'), $($td).attr('data-date'), null, 1);
                }
            },
            'add_booker_note': {
                name: "Add Scheduler Note",
                icon: "fa-sticky-note",
                visible: function (key, opt) {
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $td = opt.$trigger;
                    let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                    //Active from
                    if (moment($($tr).attr('data-facility-active-date')).isAfter(moment($td.attr('data-date')), 'day')) {
                        access = false;
                    }
                    // Option Add Scheduler Note will not appear for past date
                    if (moment($td.attr('data-date')).isBefore(moment(), 'day')) {
                        return false;
                    }
                    return access;
                },
                callback: function (key, opt) {
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    that.openFacilityBookerNoteForm($tr.attr('data-facility-id'));
                }
            },
            'paste_booking': {
                name: "Paste Booking",
                icon: "paste",
                visible: function (key, opt) {
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $td = opt.$trigger;

                    let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));

                    let copyBooking = JSON.parse(localStorage.getItem(that.copyBookingLocalStorageName) ?? '{}');
                    let moveBooking = JSON.parse(localStorage.getItem(that.moveBookingLocalStorageName) ?? '{}');

                    if (that.userSetting.userId == copyBooking.created_by) {
                        access = true;
                    }

                    if (!moment($td.attr('data-date')).isSameOrAfter(moment(), 'day')) {
                        access = false;
                    }

                    if (!copyBooking.hasOwnProperty('facility_id') && !moveBooking.hasOwnProperty('facility_booking_id')) {
                        access = false;
                    }

                    if (copyBooking.hasOwnProperty('facility_id') && copyBooking.facility_id != $($td).attr('data-facility-id')) {
                        access = false;
                    }

                    if (moveBooking.hasOwnProperty('facility_id') && moveBooking.facility_id == $($td).attr('data-facility-id')) {
                        access = true;
                    }

                    if ($tr.attr("data-facility-archive")) {
                        let archiveDate = moment($tr.attr("data-facility-archive"));
                        let bookingDate = moment($td.attr('data-date'));
                        if (bookingDate.isSameOrAfter(archiveDate, 'day')) {
                            access = false;
                        }
                    }

                    //Active from
                    if (moment($($tr).attr('data-facility-active-date')).isAfter(moment($td.attr('data-date')), 'day')) {
                        access = false;
                    }

                    return access;
                },
                callback: function (key, opt) {
                    let $td = opt.$trigger;
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let createRecord = 0;
                    if ($tr.attr('data-facility-default-booking-type') == that.constants.managed_facility) {
                        createRecord = 1;
                    }
                    let copyBooking = JSON.parse(localStorage.getItem(that.copyBookingLocalStorageName) ?? '{}');
                    let moveBooking = JSON.parse(localStorage.getItem(that.moveBookingLocalStorageName) ?? '{}');

                    // Moving within the same facility show warning and do not paste
                    if (moveBooking.hasOwnProperty('facility_booking_id')) {
                        const sourceFacilityId = String(moveBooking.facility_id || '').trim();
                        const targetFacilityId = String($td.attr('data-facility-id') || '').trim();

                        if (sourceFacilityId && targetFacilityId && sourceFacilityId === targetFacilityId) {
                            $.facebox("<div><p style='padding: 11px; font-size: 12px;'> It is not possible to use ‘Move’ within the same facility. To alter the times or date of a Booking within the same facility, please select Edit Booking and alter the fields there.</p></div>");
                            localStorage.removeItem(that.moveBookingLocalStorageName);
                            return;
                        }
                    }

                    if (copyBooking.hasOwnProperty('facility_id')) {
                        that.openFacilityBookingForm(copyBooking.facility_id, null, copyBooking.facility_booking_id, createRecord, null, $td.attr('data-date'));
                    }
                    if (moveBooking.hasOwnProperty('facility_booking_id')) {
                        if (moveBooking.facility_booking_type != $tr.attr('data-facility-default-booking-type')) {
                            //When facility booking type is different
                            that.openWarningMoveFacilityBookingType(moveBooking.facility_booking_type);
                            return;
                        }
                        that.openFacilityBookingForm($td.attr('data-facility-id'), null, moveBooking.facility_booking_id, createRecord, null, null, $td.attr('data-date'));
                    }
                    localStorage.removeItem(that.copyBookingLocalStorageName);
                    localStorage.removeItem(that.moveBookingLocalStorageName);
                }
            }
        };
        that.bookingAvailableSettings = bookingAvailableSettings;
        let events = {
            show: function (options) {
                setTimeout(function () {
                    //When all li is hidden hide the context menu ul
                    if (options.$menu.find('li').filter(':visible').length == 0) {
                        options.$menu.hide();
                    }
                }, 0);
            }
        };

        $.contextMenu({
            selector: ".request-booking-available",
            build: function ($trigger, e) {
                // bookingAvailableSettings exists and has keys, but we must check per-trigger visibility
                const items = bookingAvailableSettings;
                if (!hasVisibleItems(items, $trigger, e)) {
                    return false;
                }
                return {
                    items: items,
                    events: events
                };
            }
        });

        $.contextMenu({
            selector: ".facility-unavailable-container",
            build: function ($trigger, e) {
                const items = bookingAvailableSettings;
                if (!that.hasVisibleItems(items, $trigger, e)) {
                    return false;
                }
                return {
                    items: items,
                    events: events
                };
            }
        });

        let bookingCellRightClickSettings = {
            'update_booking': {
                name: "Edit Booking",
                icon: "edit",
                visible: function (key, opt) {
                    return that.checkBookingEditAccess(opt);
                },
                callback: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    that.openFacilityBookingForm($($bookingEle).attr('data-facility-id'), null, $($bookingEle).attr('data-facility-booking-id'));
                }
            },
            'copy_booking': {
                name: "Copy Booking",
                icon: "copy",
                visible: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                    if ($bookingEle.attr('data-is-linked-booking') == 1) {
                        access = false;
                    }
                    if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility && $bookingEle.attr('data-booking-created-by') != that.userSetting.userId) {
                        access = false;
                    }
                    return access;
                },
                callback: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    localStorage.removeItem(that.moveBookingLocalStorageName);
                    localStorage.setItem(that.copyBookingLocalStorageName, JSON.stringify({
                        'facility_id': $($bookingEle).attr('data-facility-id'),
                        'facility_booking_id': $($bookingEle).attr('data-facility-booking-id'),
                        'created_by': $($bookingEle).attr('data-booking-created-by')
                    }));
                    $.facebox("<div><p style='padding: 11px; font-size: 12px;'>Please navigate to the Date and time into which you would like to paste this Booking, then right click and choose ‘Paste Booking’. It is not possible to copy from one Facility to another.</p></div>");
                }
            },
            'move_booking': {
                name: "Move Booking",
                icon: "fa-arrow-right",
                visible: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $td = opt.$trigger.parents('.booking-details-td').first();
                    let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                    if (!moment($td.attr('data-date')).isSameOrAfter(moment(), 'day')) {
                        access = false;
                    }
                    if ($bookingEle.attr('data-is-linked-booking') == 1) {
                        access = false;
                    }
                    if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility) {
                        access = false;
                    }
                    return access;
                },
                callback: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    localStorage.removeItem(that.copyBookingLocalStorageName);
                    localStorage.setItem(that.moveBookingLocalStorageName, JSON.stringify({
                        'facility_id': $($bookingEle).attr('data-facility-id'),
                        'facility_booking_id': $($bookingEle).attr('data-facility-booking-id'),
                        'facility_booking_type': $($tr).attr('data-facility-default-booking-type'),
                        'created_by': $($bookingEle).attr('data-booking-created-by')
                    }));
                    $.facebox("<div><p style='padding: 11px; font-size: 12px;'>Please navigate to the Facility and Date where you would like to paste this Booking then right click and choose \"Paste Booking\".</p></div>");
                }
            },
            'cancel_booking': {
                name: "Cancel Booking",
                icon: "fa-times",
                visible: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $td = opt.$trigger.parents('.booking-details-td').first();
                    let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                    if (opt.$trigger.attr('data-booking-created-by') == that.userSetting.userId) {
                        if ($bookingEle.attr('data-facility-booking-status') == 'new' || $tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility) {
                            access = true;
                        }
                    }
                    if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility && $bookingEle.attr('data-booking-created-by') != that.userSetting.userId) {
                        access = false;
                    }
                    if (!moment($td.attr('data-date')).isSameOrAfter(moment(), 'day')) {
                        access = false;
                    }
                    if ($bookingEle.attr('data-is-linked-booking') == 1) {
                        access = false;
                    }

                    return access;
                },
                callback: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    //Get recurrence count
                    $.get(
                        that.recurrenceAvailableCountUrl.replace(':facilityBookingId', $($bookingEle).attr('data-facility-booking-id')),
                        function (response) {
                            that.cancelBookingPopUp($($bookingEle).attr('data-facility-booking-id'), response['availableRecurrenceCount']);
                        }
                    );
                }
            },
            'view_booking_record': {
                name: "View Booking",
                icon: "fa-eye",
                visible: function (key, opt) {
                    return that.checkBookingViewAccess(opt);
                },
                callback: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    that.openFacilityBookingForm($($bookingEle).attr('data-facility-id'), null, $($bookingEle).attr('data-facility-booking-id'), 0, 1);
                }
            },
            'view_history': {
                name: "View History",
                icon: "fa-hourglass-end",
                visible: function (key, opt) {
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $bookingEle = opt.$trigger;
                    let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                    if ($bookingEle.attr('data-booking-created-by') == that.userSetting.userId) {
                        access = true;
                    }
                    if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility && $bookingEle.attr('data-booking-created-by') != that.userSetting.userId) {
                        access = false;
                    }
                    return access;
                },
                callback: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    that.viewFacilityBookingHistory($($bookingEle).attr('data-facility-booking-id'));
                }
            },
            'delete_booking': {
                name: "Delete Booking",
                icon: "fa-trash",
                visible: function (key, opt) {
                    let $tr = opt.$trigger.parents('.facility-row').first();
                    let $td = opt.$trigger.parents('.booking-details-td').first();
                    let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                    if (!moment($td.attr('data-date')).isSameOrAfter(moment(), 'day')) {
                        access = false;
                    }
                    if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility) {
                        access = false;
                    }
                    if (opt.$trigger.attr('data-is-linked-booking') == 1) {
                        access = false;
                    }
                    return access;
                },
                callback: function (key, opt) {
                    let $bookingEle = opt.$trigger;
                    that.deleteBookingPopUp($($bookingEle).attr('data-facility-booking-id'));
                }
            }
        };

        $.contextMenu({
            selector: ".booking-detail-cell-right-click-menu",
            build: function ($trigger, e) {
                const items = bookingCellRightClickSettings;
                if (!that.hasVisibleItems(items, $trigger, e)) {
                    return false;
                }
                return {
                    items: items,
                    events: events
                };
            }
        });

        $.contextMenu({
            selector: ".right-click-menu-admin-reinstate",
            items: {
                'reinstate_booking': {
                    name: "Reinstate Booking",
                    icon: "fa-refresh",
                    visible: function (key, opt) {
                        let $bookingEle = opt.$trigger;
                        let $parentBooking = $('.booking-detail-container[data-facility-booking-id="' + $bookingEle.attr('data-parent-booking-id') + '"]');
                        let $tr = opt.$trigger.parents('.facility-row').first();
                        let $td = opt.$trigger.parents('.booking-details-td').first();
                        let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                        if ($bookingEle.attr('data-booking-created-by') == that.userSetting.userId) {
                            access = true;
                        }
                        if (!moment($td.attr('data-date')).isSameOrAfter(moment(), 'day')) {
                            access = false;
                        }
                        if ($bookingEle.attr('data-is-linked-booking') == 1 && $parentBooking.attr('data-facility-booking-status') == 'cancelled') {
                            access = false;
                        }
                        return access;
                    },
                    callback: function (key, opt) {
                        let $bookingEle = opt.$trigger;
                        that.reinstateBookingPopUp($($bookingEle).attr('data-facility-booking-id'));
                    }
                },
                'view_booking_record': {
                    name: "View Booking",
                    icon: "fa-eye",
                    visible: function (key, opt) {
                        return that.checkBookingViewAccess(opt);
                    },
                    callback: function (key, opt) {
                        let $bookingEle = opt.$trigger;
                        that.openFacilityBookingForm($($bookingEle).attr('data-facility-id'), null, $($bookingEle).attr('data-facility-booking-id'), 0, 1);
                    }
                },
                'view_history': {
                    name: "View History",
                    icon: "fa-hourglass-end",
                    visible: function (key, opt) {
                        return true;
                    },
                    callback: function (key, opt) {
                        let $bookingEle = opt.$trigger;
                        that.viewFacilityBookingHistory($($bookingEle).attr('data-facility-booking-id'));
                    }
                }
            }
        });

        $.contextMenu({
            selector: ".booker-note-detail-container",
            items: {
                'update_booker_note': {
                    name: "Edit Scheduler Note",
                    icon: "edit",
                    visible: function (key, opt) {
                        let $tr = opt.$trigger.parents('.facility-row').first();
                        let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                        return access;
                    },
                    callback: function (key, opt) {
                        let $element = opt.$trigger;
                        that.openFacilityBookerNoteForm($($element).attr('data-facility-id'), $($element).attr('data-booker-note-id'));
                    }
                },
                'delete_booker_note': {
                    name: "Delete Scheduler Note",
                    icon: "fa-trash",
                    visible: function (key, opt) {
                        let $tr = opt.$trigger.parents('.facility-row').first();
                        let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                        return access;
                    },
                    callback: function (key, opt) {
                        let $element = opt.$trigger;
                        that.openSchedulerNoteDeletForm($($element).attr('data-booker-note-id'));
                    }
                },
                'view_history': {
                    name: "View History",
                    icon: "fa-hourglass-end",
                    visible: function (key, opt) {
                        let $tr = opt.$trigger.parents('.facility-row').first();
                        let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
                        return access;
                    },
                    callback: function (key, opt) {
                        let $element = opt.$trigger;
                        that.viewSchedulerNoteHistory($($element).attr('data-booker-note-id'));
                    }
                }
            }
        });

        //Get mouse right click position
        $("#weekly-view-time-line-table").on("contextmenu", function (e) {
            that.setClickPosition($(this), e);
        });
        $("#weekly-view-time-line-table").dblclick(function (e) {
            that.setClickPosition($(this), e);
        })
    },
    /**
     * Checks if any items in the context menu are visible
     * @param {*} opt 
     * @returns 
     */
    hasVisibleItems: function (items, $trigger, e) {
        if (!items) return false;
        let opt = { $trigger: $trigger, e: e };

        return Object.keys(items).some(function (key) {
            let item = items[key];
            if (!item) return false;
            if (item.type === 'separator' || item.separator) return false;

            if (typeof item.visible === 'function') {
                return item.visible(key, opt) !== false;
            }
            if (typeof item.visible === 'boolean') {
                return item.visible;
            }

            return !!(item.name || item.callback);
        });
    },
    /**
     * Check booking view access
     */
    checkBookingViewAccess: function (opt) {
        let that = this; // NOSONAR javascript:S7740
        let $tr = opt.$trigger.parents('.facility-row').first();
        let $bookingEle = opt.$trigger;
        let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
        if ($bookingEle.attr('data-booking-created-by') == that.userSetting.userId) {
            access = true;
        }
        if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility && $bookingEle.attr('data-booking-created-by') != that.userSetting.userId) {
            access = false;
        }
        let viewAccess = true;
        if (!access && ($bookingEle.attr('data-booking-privacy') == 'yes' || $bookingEle.attr('data-booking-privacy') == 'summary')) {
            viewAccess = false;
        }
        return viewAccess;
    },
    /**
    * Checks if the user can edit this booking
    */
    checkBookingEditAccess: function (opt) {
        let that = this; // NOSONAR javascript:S7740
        let $bookingEle = opt.$trigger;
        let $tr = opt.$trigger.parents('.facility-row').first();
        let $td = opt.$trigger.parents('.booking-details-td').first();
        if ($bookingEle.attr('data-facility-booking-status') == 'cancelled') {
            return false;
        }
        let accessBooker = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
        let access = accessBooker;
        if (opt.$trigger.attr('data-booking-created-by') == that.userSetting.userId) {
            access = true;
        }
        if (!moment($td.attr('data-date')).isSameOrAfter(moment(), 'day')) {
            access = false;
        }
        if (!accessBooker && $bookingEle.attr('data-facility-booking-status') !== 'new' && $tr.attr('data-facility-default-booking-type') == that.constants.managed_facility) {
            access = false;
        }
        if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility && $bookingEle.attr('data-booking-created-by') != that.userSetting.userId) {
            access = false;
        }
        return access;
    },
    /**
     * Set mouse position 
     */
    setClickPosition: function ($obj, event) {
        let that = this; // NOSONAR javascript:S7740
        const offset = $($obj).offset();
        const x = event.pageX - offset.left;
        let $closestTime = that.getStartTimeBasedRightClick(x);
        that.clickPositionDateTime = $closestTime.attr('data-date-time');
    },
    /**
     * Get closest time
     * 
     */
    getStartTimeBasedRightClick: function (xOffset) {
        let $closest = null;
        let minDistance = Infinity;
        $('.fifteen-interval-day').each(function () {
            const $el = $(this);
            const left = $el.offset().left;
            const distance = Math.abs(left - xOffset);

            if (distance < minDistance) {
                minDistance = distance;
                $closest = $el;
            }
        });
        return $closest == null ? null : $closest.next('div');
    },

    /**
     * Recurring booking warining
     */
    initRecurringBookingWarningPopup: function () {
        let facilityId = $('#facility-booking-recurrence-booking-available-count').val();
        if (facilityId > 0) {
            $('#facility-booking-recurring-warning-modal').dialog('open');
        }
    },

    /**
    * Double-click handler — same logic & conditions as right-click context menu
    */
    initDoubleClick: function () {
        let that = this; // NOSONAR javascript:S7740

        $('#weekly-view-time-line-container').on('dblclick', '.request-booking-available', function (e) {
            //For unavailable grid
            if ($(e.target).hasClass('facility-unavailable-container')) {
                $.facebox("This facility is not available at this time. Please select a different time.");
                return;
            }

            let $td = $(this);
            let $tr = $td.closest('tr');
            let opt = {
                $trigger: $td,
                trigger: $td,
                selected: $td[0],
                target: $td[0],
                originalEvent: e
            };

            let $bookingElement = $(e.target).closest('.booking-detail-container');
            if ($bookingElement.length > 0) {
                opt.$trigger = $bookingElement;
                opt.trigger = $bookingElement;
                opt.selected = $bookingElement[0];
                opt.target = $bookingElement[0];

                if (that.checkBookingEditAccess(opt) && $bookingElement.attr('data-facility-booking-status') != 'cancelled') {
                    let facilityId = $bookingElement.attr('data-facility-id');
                    let bookingId = $bookingElement.attr('data-facility-booking-id');
                    that.openFacilityBookingForm(facilityId, null, bookingId);
                } else {
                    let hasViewAccess = that.checkBookingViewAccess(opt);
                    if (!hasViewAccess) {
                        $.facebox("You are not authorized to view this booking.");
                        return;
                    }
                    let facilityId = $bookingElement.attr('data-facility-id');
                    let bookingId = $bookingElement.attr('data-facility-booking-id');
                    that.openFacilityBookingForm(facilityId, null, bookingId, 0, 1);
                }
                return;
            }

            let facilityAreaOwnerId = $tr.data('facility-area-owner-id');
            let facilityRestrictedTeamIds = ($tr.data('facility-restricted-team-ids') || '').toString().split(',').filter(Boolean);
            let access = that.checkFacilityAccess(facilityAreaOwnerId, $tr.attr('data-facility-id'), facilityRestrictedTeamIds);
            if (!access) {
                if ($(e.target).hasClass('booking-detail-container') && ($(e.target).is('.confirmed-booking.private-booking') || $(e.target).hasClass('declined-booking'))) {
                    $.facebox("You are not permitted to view the details of this booking.");
                    return;
                }
            }
            let settings = that.bookingAvailableSettings || {};
            let allowedActions = ['add_booking_request', 'add_booking_record'];
            let blockedActions = ['update_booking', 'delete_booking', 'cancel_booking', 'edit_booking'];
            let shouldOpen = false;

            // Step 1: Check if any blocked (edit/update) action is visible
            for (let i = 0; i < blockedActions.length; i++) {
                let key = blockedActions[i];
                let item = settings[key];
                if (!item || typeof item.visible !== 'function') continue;

                try {
                    if (item.visible(key, opt)) {
                        return; // stop immediately (existing booking slot)
                    }
                } catch (err) { }
            }

            // Step 2: Only allow request or record if visible
            for (let i = 0; i < allowedActions.length; i++) {
                let key = allowedActions[i];
                let item = settings[key];
                if (!item || typeof item.visible !== 'function') continue;

                let isVisible = false;
                try {
                    isVisible = item.visible(key, opt);
                } catch (err) { }

                if (isVisible && typeof item.callback === 'function') {
                    try {
                        item.callback(key, opt);
                    } catch (err) { }
                    shouldOpen = true;
                    break;
                }
            }

            if (!shouldOpen) {
                return;
            }
        });
    },

    /**
     * Move Booking warning when type are different
     */
    openWarningMoveFacilityBookingType: function (from) {
        let typeMessage = 'Self-Booked to Managed';
        if (from == that.constants.managed_facility) {
            typeMessage = 'Managed to Self-Booked';
        }
        $.facebox("<div><p style='padding: 11px; font-size: 12px;'>Booking can't be moved from " + typeMessage + ".</p></div>");
    },
    /**
     * Facilirt booker note form
     * @param {*} facilityId
     */
    openFacilityBookerNoteForm: function (facilityId, facilityBookerNoteId = null) {
        let that = this; // NOSONAR javascript:S7740
        let url = that.createBookerNoteFormUrl.replace(':facility', facilityId);
        if (facilityBookerNoteId != null) {
            url = that.editBookerNoteFormUrl.replace(':facilityBookerNote', facilityBookerNoteId);
        }
        $.ajax({
            url: url,
            data: {
                'click_position_datetime': that.clickPositionDateTime,
            },
            method: "GET",
            beforeSend: function () {
                that.clearModals();
            },
            success: function (data) {
                $("#facility-booker-note-form-dialog").html(data).dialog({
                    modal: true,
                    width: 750,
                    height: 'auto',
                    'title': facilityBookerNoteId == null ? 'Add Scheduler Note' : 'Edit Scheduler Note',
                    open: function (event, ui) {
                        $(this).parent().css({ 'top': '2rem' });
                    }
                }).dialog('open');
                that.initFacilityBookerNoteFormElements();
            },
            error: function (xhr, status, error) {

            }
        });
    },
    /**
     * Init facility booker note form element
     */
    initFacilityBookerNoteFormElements: function () {
        let that = this; // NOSONAR javascript:S7740
        let facilityActiveFromDate = moment($('#facility-active-from').val());
        $('#booker_note_date').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: facilityActiveFromDate.isAfter(moment()) ? facilityActiveFromDate.toDate() : facilityActiveFromDate.toDate(),
            maxDate: '+5y'
        });

        $('input[name="recurring_booker_note_enable"]').on('click', function () {
            $(this).val() != 'no'
                ? (
                    $('#facility-booker-note-recurring-details-table-body').show(),
                    $('#facility-booker-note-update-instances-detail').show(),
                    $('#booker_note_save_recurrence_type_only_one').prop('checked', false)
                )
                : (
                    $('#facility-booker-note-recurring-details-table-body').hide(),
                    $('#facility-booker-note-update-instances-detail').hide(),
                    $('#booker_note_save_recurrence_type_only_one').prop('checked', true)
                );

        });

        $('#booker-note-recurring-cancel-btn').on('click', function () {
            $('#facility-booker-note-form-dialog').dialog('close');
        });

        $('#booker_note_recurring_start_date').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: facilityActiveFromDate.isAfter(moment()) ? facilityActiveFromDate.toDate() : new Date(),
            maxDate: '+5y',
        });
        $('#booker_note_recurring_end_date').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: facilityActiveFromDate.isAfter(moment()) ? facilityActiveFromDate.toDate() : new Date(),
            maxDate: '+5y',
        });
        $('input[name="booker_note_recurring_recurrence_type"]').on('click', function () {
            $('#booker-note-daily-recurrence-setting-container, #booker-note-weekly-recurrence-setting-container').hide();
            $(this).val() == 'weekly' ? $('#booker-note-weekly-recurrence-setting-container').show() : $('#booker-note-daily-recurrence-setting-container').show();
        })
        //Submit form
        $('#booker-note-recurring-save-btn').on('click', function () {
            that.submitBookerNoteForm();
        });

        $('#booker_note_start_time, #booker_note_end_time').on('change', function () {
            $('#booker-note-recurring-start-time').text($('#booker_note_start_time').val());
            $('#booker-note-recurring-end-time').text($('#booker_note_end_time').val());
        });

        // Bind unified recurrence handlers (avoid duplicate bindings)
        that.bindSchedulerNoteRecurrenceHandlers();

        //Edit booker note
        if ($('input[name="booker_note_save_recurrence_type"]').length == 0) {
            return;
        }
        let RecurrenceStartDate = moment(Date($('#booker-note-update-recurrence-date-range').attr('data-recurrence-start-date')));
        $('#booker_note_save_recurrence_type_start_date_range, #booker_note_save_recurrence_type_end_date_range').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: RecurrenceStartDate.isAfter(moment()) ? RecurrenceStartDate.toDate() : 0,
            maxDate: new Date($('#booker-note-update-recurrence-date-range').attr('data-recurrence-end-date'))
        });

        $('input[name="booker_note_save_recurrence_type"]').on('click', function () {
            $('input[name="booker_note_save_recurrence_type"]:checked').val() == 'date_range' ? $('#booker-note-update-recurrence-date-range').show() : $('#booker-note-update-recurrence-date-range').hide();
        });
    },
    /**
     * Validate Scheduler Note recurrence
     */
    validateSchedulerNoteRecurrence: function () {
        const recurEnabled = $('input[name="recurring_booker_note_enable"]:checked').val() !== 'no';
        $('.validation-error-text.note-vs-recurstart, .validation-error-text.recur-end-lt-start').remove();
        if (!recurEnabled) return true;
        const noteStr = $('#booker_note_date').val();
        const rStartStr = $('#booker_note_recurring_start_date').val();
        const rEndStr = $('#booker_note_recurring_end_date').val();
        const mNote = noteStr ? moment(noteStr, 'DD/MM/YYYY', true) : null;
        const mStart = rStartStr ? moment(rStartStr, 'DD/MM/YYYY', true) : null;
        const mEnd = rEndStr ? moment(rEndStr, 'DD/MM/YYYY', true) : null;
        let ok = true;
        if (mNote && mStart && mNote.isValid() && mStart.isValid() && !mStart.isSame(mNote, 'day') && $('#booker-note-id-edit').length == 0) {
            $('#booker_note_recurring_start_date')
                .after($('<span class="validation-error-text note-vs-recurstart"></span>')
                    .text('Recurrence Start Date must match the Note Date.'))
                .after('<br class="validation-error-text note-vs-recurstart">');
            ok = false;
        }
        if (mStart && mEnd && mStart.isValid() && mEnd.isValid() && mEnd.isBefore(mStart, 'day')) {
            $('#booker_note_recurring_end_date')
                .after($('<span class="validation-error-text recur-end-lt-start"></span>')
                    .text('Invalid Recurrence: End Date cannot be earlier than Start Date.'))
                .after('<br class="validation-error-text recur-end-lt-start">');
            ok = false;
        }
        return ok;
    },
    /** 
     * Sync recurrence start/end dates with Note Date when recurrence is enabled. 
     */
    syncSchedulerNoteRecurrenceFromNote: function () {
        const recurEnabled = $('input[name="recurring_booker_note_enable"]:checked').val() !== 'no';
        if (!recurEnabled) return;

        const isEdit = $('#booker-note-id-edit').length > 0;
        const noteStr = $('#booker_note_date').val();
        if (!noteStr) return;

        const dStr = moment(noteStr, 'DD/MM/YYYY', true).format('DD/MM/YYYY');
        $('#booker_note_recurring_start_date').val(dStr);
        if (!isEdit) {
            $('#booker_note_recurring_end_date').val(dStr);
        }
    },
    /** 
     * Attach unified event handlers to sync and validate recurrence fields live
     */
    bindSchedulerNoteRecurrenceHandlers: function () {
        const that = this; // NOSONAR javascript:S7740

        $('#booker_note_date').off('.scheduler');
        $('#booker_note_recurring_start_date, #booker_note_recurring_end_date').off('.scheduler');
        $('input[name="recurring_booker_note_enable"]').off('.scheduler');

        $('#booker_note_date').on('change.scheduler input.scheduler', function () {
            that.syncSchedulerNoteRecurrenceFromNote();
            that.validateSchedulerNoteRecurrence();
        });

        $('#booker_note_recurring_start_date, #booker_note_recurring_end_date')
            .on('change.scheduler input.scheduler', function () {
                that.validateSchedulerNoteRecurrence();
            });

        $('input[name="recurring_booker_note_enable"]').on('click.scheduler', function () {
            that.syncSchedulerNoteRecurrenceFromNote();
            that.validateSchedulerNoteRecurrence();
        });
    },
    /**
     * Store booker note
     */
    submitBookerNoteForm: function () {
        let that = this; // NOSONAR javascript:S7740
        let facilityId = $('#booker-note-facility-id').val();
        let url = that.storeBookerNoteUrl.replace(':facility', facilityId);
        if ($('#booker-note-id-edit').length > 0) {
            url = that.updateBookerNoteUrl.replace(':facilityBookerNote', $('#booker-note-id-edit').val());
        }

        if (!that.validateSchedulerNoteRecurrence()) {
            return;
        }

        $.ajax({
            url: url,
            method: "POST",
            data: {
                'facilityBookerNoteData': that.fomatFormData($('#create-facility-booker-note-form').serializeArray()),
                '_token': $('#create-facility-booker-note-form input[name="_token"]').val(),
                'overlap_validate_booker_note': 1
            },
            beforeSend: function () {
            },
            success: function (data) {
                location.reload();
            },
            error: function (xhr, status, error) {
                $('.validation-error-text').remove();
                let data = JSON.parse(xhr.responseText);
                that.formValidationMessages(data.errors);
                let errors = data.errors;
                Object.keys(errors).forEach((key) => {
                    errors[key].forEach((message) => {
                        let formatedKey = key.replace("facilityBookerNoteData.", "");
                        eleId = '';
                        if (formatedKey == 'recurring_booker_note_enable') {
                            eleId = '[for="recurring_booker_note_no"]';
                        }
                        if (formatedKey == 'booker_note_recurring_recurrence_type') {
                            eleId = '[for="booker_note_recurring_weekly"]';
                        }
                        if (formatedKey == 'booker_note_save_recurrence_type') {
                            eleId = '[for="booker_note_save_recurrence_type_all_series"]';
                        }
                        if (formatedKey == 'recurring_booker_note_recurrence_daily_days') {
                            eleId = '#recurring-booker-note-recurrence-daily-days-container';
                        }
                        if (formatedKey == 'recurring_booker_note_recurrence_weekly_weeks' || formatedKey == "recurring_booker_note_weekly_days") {
                            eleId = '[for="recurring_booker_note_weekly_friday"]';
                        }
                        if (formatedKey == 'overlap_validate_booker_note') {
                            $('#overlap-validate-booker-note').empty().append(
                                $('<div class="validation-error-text"></div>').text('Conflicts on ' + message)
                            );
                        }
                        $(eleId).after($('<span class="validation-error-text"></span>').text(message));
                        $(eleId).after($('<br class="validation-error-text">'));
                    });
                });
            }
        });
    },
    /**
     * Delete Booking PopuP
     *
     */
    deleteBookingPopUp: function (bookingId) {
        let that = this; // NOSONAR javascript:S7740
        $('#facility-booking-deletion').dialog("open");
        $('#delete-booking-validation-error').empty();
        $('#approve-delete-booking').off().on('click', function () {
            $.ajax({
                url: that.deleteUrl.replace(":facilityBooking", bookingId),
                method: "GET",
                data: {
                    "_method": "DELETE", "facility_booking": bookingId
                },
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    let data = JSON.parse(xhr.responseText);
                    $('#delete-booking-validation-error').empty().append(
                        $('<span class="validation-error-text"></span>').text(data['errors']['facility_booking'][0])
                    );
                }
            });
        });
        $('#decline-delete-booking').off().on('click', function () {
            $('#facility-booking-deletion').dialog("close");
        })
    },


    /**
     * Cancel Booking Pop UP
     */
    cancelBookingPopUp: function (facilityBookingId, availableRecurrenceCount) {
        let that = this; // NOSONAR javascript:S7740
        // ✅ Single instance (or effectively non-recurring)
        // Still two-stage: open the confirm facebox first, then commit on Approve.
        if (!availableRecurrenceCount || Number(availableRecurrenceCount) <= 1) {
            that.cancelBookingOption(facilityBookingId, 'entire_booking');
            return;
        }

        // ----- Multi-instance series (show options) -----
        $('#cancel_some_note').hide();
        $('#facility-booking-cancellation').dialog('open');

        // Load variant UI when the radio changes
        $('#facility-booking-cancellation-form input[type="radio"]')
            .off('change.cancel')
            .on('change.cancel', function () {
                const selectedValue = $('input[name="cancel_booking"]:checked').val();
                if (!$('#cancel_some_note').length) {
                    $('#cancel_some_booking').next('label').after(
                        $('<div id="cancel_some_note" style="display:none; font-style:italic; font-size:12px; color:#666; margin-top:6px;"></div>').html(
                            'Choosing <i class="fa-solid fa-circle" aria-hidden="true" style="color:#1b6edb; "></i> will cancel Primary and Linked bookings.<br>' +
                            'Choosing <i class="fa fa-link" aria-hidden="true" style="color:#2196F3;"></i> will cancel only the Non-mandatory Linked bookings.'
                        )
                    );
                }

                if (selectedValue === 'some_booking') {
                    $('#cancel_some_note').show();
                } else {
                    $('#cancel_some_note').hide();
                }

                $('#error_text').empty();
                that.clearCancelVariants();

                // Only these need additional UI in the dialog
                if (selectedValue === 'some_booking' || selectedValue === 'recurrence_booking') {
                    // NOTE: CancelBooking signature is (bookingId, selectedValue)
                    that.CancelBooking(facilityBookingId, selectedValue);
                }
            });

        // Primary action
        $('#cancel-booking')
            .off('click.cancel')
            .on('click.cancel', function () {
                const selectedValue = $('input[name="cancel_booking"]:checked').val();
                $('#error_text').empty();

                // Require a choice
                if (!['entire_booking', 'some_booking', 'recurrence_booking', 'nonmandatory_booking'].includes(selectedValue)) {
                    $('#error_text').text('Please select an option before proceeding.');
                    return;
                }

                // Validate “Some” — use [] selector to match the array name
                if (selectedValue === 'some_booking') {
                    const len = $('input[name="some_booking_list[]"]:checked').length;
                    if (len === 0) {
                        $('#error_text').text('Please select at least one booking.');
                        return;
                    }
                }

                // Validate “Range”
                if (selectedValue === 'recurrence_booking') {
                    const from = $('#cancel_booking_from_date').val();
                    const to = $('#cancel_booking_to_date').val();
                    if (!from || !to) {
                        $('#error_text').text('Please select both From and To dates.');
                        return;
                    }
                }

                // ✅ Always proceed via the confirm facebox (Stage 2) then single POST (Stage 3)
                that.cancelBookingOption(facilityBookingId, selectedValue);
            });
    },


    /**
     * reinstate Booking PopuP
     *
     */
    reinstateBookingPopUp: function (facilityBookingId) {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.reinstateFacilityBookingPopUpUrl.replace(":facilityBooking", facilityBookingId),
            method: "POST",
            data: {
            },
            success: function (data) {
                $('#facility-booking-reinstate-container').empty().html(data);
                that.initReinstateElements();
                $('#facility-booking-reinstate-modal').dialog('open');
            }
        });
    },
    /**
     * Init reinstate popup elements
     */
    initReinstateElements: function () {
        let that = this; // NOSONAR javascript:S7740
        $('input[name="reinstate_booking"]').change(function () {
            $('#reinstate_some_recur').hide();
            $('#reinstate_range_recur').hide();
            if ($(this).val() == 'some_booking') {
                $('#reinstate_some_recur').show();
            } else if ($(this).val() == 'recurrence_booking') {
                $('#reinstate_range_recur').show();
            }
        });
        let recStart = moment($('#reinstate_booking_from_date').attr('data-recurrence-start-date'));
        let recEnd = moment($('#reinstate_booking_to_date').attr('data-recurrence-end-date'));
        $('#reinstate_booking_from_date').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: recStart.toDate(),
            maxDate: recEnd.toDate()
        });
        $('#reinstate_booking_to_date').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: recStart.toDate(),
            maxDate: recEnd.toDate()
        });
        $('#reinstate-booking').on('click', function () {
            let valid = true;
            $('#reinstate_error_text').empty();
            if ($('input[name="reinstate_booking"]:checked').length == 0) {
                $('#reinstate_error_text').append($('<div class="validation-error-text"></div>').text('Please make a selection.'));
                valid = false;
            }
            if ($('input[name="reinstate_booking"]:checked').val() == 'recurrence_booking') {
                if ($('#reinstate_booking_from_date').val() == '' || $('#reinstate_booking_to_date').val() == '') {
                    $('#reinstate_error_text').append($('<div class="validation-error-text"></div>').text('Range Start and End Date Required.'));
                    valid = false;
                }
            }
            if ($('input[name="reinstate_booking"]:checked').val() == 'some_booking' && $('input[name="some_booking_list[]"]:checked').length == 0) {
                $('#reinstate_error_text').append($('<div class="validation-error-text"></div>').text('Please Select Bookings.'));
                valid = false;
            }

            if (valid) {
                that.reinstateBooking();
            }
        });
    },
    /**    
     * Reinstate Booking Form
     * 
     */
    reinstateBooking: function () {
        let that = this; // NOSONAR javascript:S7740
        let url = that.reinstateFacilityBookingDataUrl.replace(':facilityBooking', $('#facility-booking-reinstate-form').attr('data-facility-id'));
        $.ajax({
            url: url,
            method: "POST",
            data: $('#facility-booking-reinstate-form').serialize(),
            beforeSend: function () {
                $('#facility-booking-reinstate-form').hide();
                $('#reinstate-submit-spinner').show();
            },
            success: function (response) {
                toastr.success('Reinstated Successfully.')
                location.reload();
            },
            error: function (xhr, status, error) {
                $('#facility-booking-reinstate-form').show();
                $('#reinstate-submit-spinner').hide();
                let dataResponse = JSON.parse(xhr.responseText);
                $('#reinstate_error_text').append($('<div class="validation-error-text"></div>').text('Conflicts on ' + JSON.parse(dataResponse.errors['reinstate_booking'][0]).join(', ')));
            }
        });
    },

    /**
     * Cancel Booking Option
     */
    cancelBookingOption: function (bookingId, selectedValue) {
        let that = this; // NOSONAR javascript:S7740

        // ---- Build payload from selection dialog (Stage 1) ----
        const basePayload = {
            _token: that.token,
            bookingId: bookingId,
            cancel_value: selectedValue
        };

        // Collect 'some' selections (parent instances)
        let someIds = [];
        if (selectedValue === 'some_booking') {
            $('input[name="some_booking_list[]"]:checked').each(function () {
                someIds.push($(this).val());
            });

            if (someIds.length === 0) {
                $('#error_text').html("Please select at least one booking.");
                return;
            }

            basePayload.some_booking_list = someIds; // <-- batched IDs
        }

        // Add range dates in BOTH UI and ISO, like before
        if (selectedValue === 'recurrence_booking') {
            const fromUi = $('#cancel_booking_from_date').val();
            const toUi = $('#cancel_booking_to_date').val();

            const toISO = (ui) => {
                const m = moment(ui, ['DD/MM/YYYY', 'DD/MM/YY'], true);
                return m.isValid() ? m.format('YYYY-MM-DD') : '';
            };

            if (!fromUi || !toUi) {
                $('#error_text').html("Select From and To before proceeding.");
                return;
            }

            basePayload.from_date = fromUi;
            basePayload.to_date = toUi;
            basePayload.from_date_iso = toISO(fromUi);
            basePayload.to_date_iso = toISO(toUi);
        }

        // ---- Ask server to render CONFIRMATION popup (Stage 2) ----
        $.ajax({
            url: that.cancelFacilityBookingPopUpUrl.replace(":facilityBooking", bookingId),
            method: "POST",
            data: basePayload,
            success: function (html) {
                // Close selection dialog (keeps selection state in memory/UI)
                $("#facility-booking-cancellation").dialog('close');
                that.clearModals();
                $.facebox(html);

                // Show a human-friendly summary in the confirmation, if “Some”
                // if (selectedValue === 'some_booking' && Array.isArray(someIds) && someIds.length > 0) {
                //     const $summary = $('<div class="cancel-confirm-summary" style="margin:8px 0; font-size:12px;"></div>');
                //     $summary.text(`You’re about to cancel ${someIds.length} parent instance(s). Mandatory linked children on those dates will also be cancelled.`);
                //     // Try to place below the confirm text if present
                //     $('.facebox-content, #facebox .content').first().append($summary);
                // }

                // Ensure spinner sits next to Approve button
                const ensureSpinner = () => {
                    if ($('#cancel-processing-spinner').length) return;
                    const $approve = $('#approve-cancel-booking');
                    const $spinner = $(
                        '<span id="cancel-processing-spinner" ' +
                        'style="margin-left:8px; display:none; font-size:12px; vertical-align:middle;">' +
                        '<i class="fa fa-spinner fa-spin" aria-hidden="true" style="margin-right:6px;"></i>' +
                        'Processing cancellation… please wait' +
                        '</span>'
                    );
                    if ($approve.length) {
                        $approve.after($spinner);
                    }
                };
                ensureSpinner();

                // Decline -> reload (as per existing behavior)
                $('#decline-cancel-booking').off('click').on('click', function () {
                    location.reload();
                });

                // Approve -> single POST (Stage 3 Commit)
                $('#approve-cancel-booking').off('click').on('click', function () {
                    const $approve = $('#approve-cancel-booking');
                    const $decline = $('#decline-cancel-booking');

                    $approve.prop('disabled', true).attr('aria-disabled', 'true');
                    $decline.prop('disabled', true).attr('aria-disabled', 'true');
                    $('#cancel-processing-spinner').show();

                    const url = that.cancelFacilityBookingDataUrl.replace(':facilityBooking', bookingId);

                    $.ajax({
                        url: url,
                        method: "POST",
                        data: basePayload, // <-- single batched payload
                        success: function () {
                            location.reload(); // single reload
                        },
                        error: function (xhr) {
                            // Show server errors back in the selection dialog area if possible
                            let data;
                            try { data = JSON.parse(xhr.responseText); } catch (e) { data = {}; }
                            const errors = data.errors || {};
                            // Close facebox and reopen selection dialog with errors
                            $(document).trigger('close.facebox');

                            // Surface errors in the selection dialog error region
                            $('#error_text').empty();
                            Object.entries(errors).forEach(([field, messages]) => {
                                messages.forEach((msg) => {
                                    $('#error_text')
                                        .append($('<span class="validation-error-text"></span>').text(msg))
                                        .append('<br class="validation-error-text">');
                                });
                            });
                            // Re-open selection dialog
                            $('#facility-booking-cancellation').dialog('open');

                            // Re-enable buttons in facebox if still visible
                            $approve.prop('disabled', false).attr('aria-disabled', 'false');
                            $decline.prop('disabled', false).attr('aria-disabled', 'false');
                            $('#cancel-processing-spinner').hide();
                        }
                    });
                });
            },
            error: function (xhr) {
                // Server-side validation for range dates, etc.
                let data;
                try { data = JSON.parse(xhr.responseText); } catch (e) { data = {}; }
                const errors = data.errors || {};

                $('#error_text').empty();
                $('.validation-error-text').remove();

                Object.entries(errors).forEach(([field, messages]) => {
                    messages.forEach((msg) => {
                        let $target;
                        if (field === 'from_date') $target = $('#cancel_booking_from_date');
                        else if (field === 'to_date') $target = $('#cancel_booking_to_date');
                        else $target = $('#error_text');

                        if ($target && $target.length) {
                            $target.after($('<span class="validation-error-text"></span>').text(msg));
                            $target.after('<br class="validation-error-text">');
                        } else {
                            $('#error_text').append($('<span class="validation-error-text"></span>').text(msg));
                            $('#error_text').append('<br class="validation-error-text">');
                        }
                    });
                });
            }
        });
    },


    /**
     * Cancel Booking
     * 
     */
    CancelBooking: function (bookingId, selectedValue) {
        let that = this; // NOSONAR javascript:S7740

        let url = that.cancelFacilityBookingUrl.replace(':facilityBooking', bookingId);
        $.ajax({
            url: url,
            method: "POST",
            dataType: 'json',
            data: {
                '_token': that.token,
                'bookingId': bookingId,
                'cancel_value': selectedValue
            },
            success: function (data) {
                if (selectedValue == "entire_booking" || selectedValue == "nonmandatory_booking") {
                    location.reload();
                } else if (selectedValue == "some_booking") {

                    // Ensure container exists & is visible
                    const $container = $('#cancel_some_recur');
                    $('#cancel_range_recur').hide();
                    if (!$container.length) {
                        console.error('Missing #cancel_some_recur container. Creating fallback.');
                        $('<div>', { id: 'cancel_some_recur' }).appendTo('body');
                    }
                    $('#cancel_some_recur').show().empty();

                    // Normalize payload: support either an array or {groups: [...]}
                    let groups = data;
                    if (typeof groups === 'string') {
                        try { groups = JSON.parse(groups); } catch (e) { groups = []; }
                    }
                    if (!Array.isArray(groups) && Array.isArray(data?.groups)) {
                        groups = data.groups;
                    }
                    if (!Array.isArray(groups)) {
                        console.warn('Unexpected payload for some_booking:', data);
                        groups = [];
                    }

                    // Log for quick debugging
                    console.log('Rendering some_booking groups:', groups.length);

                    groups.forEach(function (grp, idx) {
                        const dateStr = (grp && grp.date) ? grp.date : '';
                        const parent = (grp && grp.parent) ? grp.parent : {};
                        const nonMand = Array.isArray(grp?.non_mandatory_children) ? grp.non_mandatory_children : [];

                        // Skip groups with no parent id AND no children at all (nothing to render)
                        const hasParentId = !!parent.booking_id;
                        const hasChildren = nonMand.length > 0;
                        if (!hasParentId && !hasChildren) {
                            console.debug('Skipping empty group at index', idx, 'date:', dateStr);
                            return;
                        }

                        // Group wrapper
                        const $group = $('<div>', {
                            class: 'cancel-some-group',
                            'data-date': dateStr,
                            style: 'margin-bottom:10px;'
                        });

                        // Parent row (selectable if booking_id present)
                        const $parentRow = $('<div>', { class: 'parent-row', style: 'font-weight:600;' });
                        if (hasParentId) {
                            const parentId = `parent_${String(parent.booking_id).trim()}`;
                            const $parentChk = $('<input>', {
                                type: 'checkbox',
                                class: 'chk-parent',
                                id: parentId,
                                name: 'some_booking_list[]',  // <-- unified array name
                                value: parent.booking_id
                            });
                            let $iClassParent = $('<i class="fa fa-circle fa-xs" style="color:#1b6edb; margin :0 3px;"></i>');
                            const parentText = $('<span></span>').text(`${parent.booking_title || ''} (${parent.start_date || ''} - ${parent.end_date || ''})`);
                            const $parentLbl = $('<label>', { for: parentId }).append($iClassParent).append(parentText);
                            $parentRow.append($parentChk, $parentLbl);
                        } else {
                            // If parent is missing, show a header anyway (for the date)
                            $parentRow.text(dateStr ? `Bookings on ${dateStr}` : 'Bookings');
                        }

                        // Child list container
                        const $childList = $('<div>', {
                            class: 'child-list',
                            style: 'margin-left:18px; margin-top:6px;'
                        });

                        // Non-mandatory children (user-selectable)
                        nonMand.forEach(function (c) {
                            // Guard against missing IDs
                            if (!c.booking_id) { return; }

                            const childId = `child_${String(c.booking_id).trim()}`;
                            const $row = $('<div>', { class: 'child-row child-nonmandatory', style: 'margin:2px 0;' });
                            const $chk = $('<input>', {
                                type: 'checkbox',
                                class: 'chk-child chk-child-nonmandatory',
                                id: childId,
                                name: 'some_booking_list[]',             // <-- unified array name
                                value: c.booking_id
                            });
                            let $iClassChild = $('<i class="fa fa-link fa-xs" style="color:#2196F3;"></i>');
                            const $childText = $('<span></span>').text(` ${c.booking_title || ''} (${c.start_date || ''} - ${c.end_date || ''})`);
                            const $lbl = $('<label>', { for: childId }).append($iClassChild).append($childText);
                            $row.append($chk, $lbl);
                            $childList.append($row);
                        });

                        $group.append($parentRow, $childList);
                        $('#cancel_some_recur').append($group);
                    });

                    // Parent toggle → toggle all children in the same group (optional convenience)
                    $('#cancel_some_recur')
                        .off('change.parentCascade')
                        .on('change.parentCascade', '.chk-parent', function () {
                            const $grp = $(this).closest('.cancel-some-group');
                            const checked = $(this).is(':checked');
                            if (!checked) {
                                $grp.find('.chk-child').prop('checked', false).removeAttr('disabled');
                            } else {
                                $grp.find('.chk-child').prop('checked', checked).attr('disabled', true);
                            }
                        });

                    // Empty state if no groups
                    if (groups.length === 0) {
                        $('#cancel_some_recur').append(
                            $('<div>', { style: 'font-size:12px;color:#666;' })
                                .text('No future non-mandatory linked bookings for this series.')
                        );
                    }
                    return;


                }


                else if (selectedValue == "recurrence_booking") {
                    $('#cancel_range_recur').css("display", "block");
                    $('#cancel_some_recur').css("display", "none");

                    const parseIsoDate = (isoStr) => {
                        if (!isoStr) return null;
                        const [y, m, d] = isoStr.split('-').map(n => parseInt(n, 10));
                        return new Date(y, m - 1, d);
                    };

                    const minDateObj = parseIsoDate(data.minDate);
                    const maxDateObj = parseIsoDate(data.maxDate);

                    $('#cancel_booking_from_date').val('');
                    $('#cancel_booking_to_date').val('');

                    $('#cancel_booking_from_date').datepicker({
                        dateFormat: "dd/mm/yy",
                        minDate: minDateObj || 0,
                        maxDate: maxDateObj || 0
                    });

                    $('#cancel_booking_to_date').datepicker({
                        dateFormat: "dd/mm/yy",
                        minDate: minDateObj || 0,
                        maxDate: maxDateObj || 0
                    });


                }
            },
            error: function (xhr, status, error) {

            }
        });


    },
    /**
     * Check if the user have access
     *
     */
    checkFacilityAccess: function (facilityAreaOwnerId, facilityId, facilityRestrictedTeamIds) {
        let that = this; // NOSONAR javascript:S7740
        let haveAccess = false;
        facilityRestrictedTeamIds = facilityRestrictedTeamIds.filter(element => element != ""); // Remove empty values
        //Check if has facility administrator role
        let userAreaSettings = that.userSetting.userAreaSettings;
        Object.keys(userAreaSettings).forEach(key => {
            if (userAreaSettings[key]['DivisionID'] == facilityAreaOwnerId && userAreaSettings[key]['RoleName'] == "Facility Administrator") {
                haveAccess = true;
                return false;
            }
        });

        //Check if user have spoof facility admin roles
        let facilitySpoofAdminRole = that.userSetting.facilitySpoofAdminRole;
        Object.keys(facilitySpoofAdminRole).forEach(key => {
            if (facilitySpoofAdminRole[key]['FC_AreaOwnerID'] == facilityAreaOwnerId && facilitySpoofAdminRole[key]['FUR_FacilityID'] == facilityId) {
                haveAccess = true;
                return false;
            }
        });

        if (haveAccess) {
            return haveAccess;
        }

        //Function check admin access
        let accessCheckFunction = function (teamData) {
            return (teamData['scheduler'] > 0 || teamData['scheduling_team_admin'] > 0 || teamData['facility_booker'] > 0);
        };

        //Check has restricted teams access
        let userTeamSettings = that.userSetting.userTeamSettings;
        Object.keys(userTeamSettings).some(key => {
            let teamData = userTeamSettings[key];
            if (facilityRestrictedTeamIds.length > 0) {
                //When Restrict Bookers are chosen in facility catalogue
                if (facilityRestrictedTeamIds.includes(teamData['schedulingteamid']) && accessCheckFunction(teamData)) {
                    haveAccess = true;
                    return true;
                }
            } else {
                //When No Restrict Bookers are chosen in facility catalogue
                if (teamData['schedulingteamdivisionid'] == facilityAreaOwnerId && accessCheckFunction(teamData)) {
                    haveAccess = true;
                    return true;
                }
            }
        });
        return haveAccess;
    },
    /**
     * Open facility booking form
     *
     */
    openFacilityBookingForm: function (facilityId, dateSelected = null, bookingId = null, createBookingRecord = null, showFacilityBooking = null, copyBookingDate = null, moveBookingDate = null) {
        let that = this; // NOSONAR javascript:S7740
        let title = "New Booking Request";
        let url = that.createFacilityBookingRequestFormUrl.replace(':facility', facilityId);
        let data = { 'date_selected': dateSelected };
        if (bookingId != null) {
            url = that.editFacilityBookingFormUrl.replace(':facilityBooking', bookingId);
            title = 'Edit Booking';
        }
        if (createBookingRecord == 1) {
            url = that.createFacilityBookingRecordFormUrl.replace(':facility', facilityId);
            title = 'New Booking Record';
        }
        if (showFacilityBooking == 1) {
            url = that.showFacilityBookingUrl.replace(':facilityBooking', bookingId);
            title = 'View Booking';
        }
        if (copyBookingDate != null) {
            title = "New Booking Request";
            url = that.copyFacilityBookingFormUrl.replace(':facilityBooking', bookingId);
            if (createBookingRecord == 1) {
                title = 'New Booking Record';
            }
            data['copy_date'] = copyBookingDate;
            data['create_booking_record'] = createBookingRecord;
        }

        data['click_position_datetime'] = that.clickPositionDateTime;

        if (moveBookingDate != null) {
            title = "Move Booking";
            url = that.moveFacilityBookingFormUrl.replace(':facilityBooking', bookingId);
            data['move_date'] = moveBookingDate;
            data['create_booking_record'] = createBookingRecord;
            data['facility_id'] = facilityId;
        }

        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                method: "POST",
                data: data,
                beforeSend: function () {
                    that.clearModals();
                },
                success: function (response) {
                    $("#facility-booking-form-dialog").html(response).dialog({
                        modal: true,
                        width: 1050,
                        height: 'auto',
                        title: title,
                        open: function (event, ui) {
                            $(this).parent().css({ top: '2rem' });
                            // Auto-focus on booking title input
                            $('#facility_request_booking_title').focus().select();
                        }
                    }).on('dialogclose', function () {
                        // Close Action dialog if still open
                        if ($('#facility-booking-action-modal').length) {
                            $('#facility-booking-action-modal').dialog('close');
                        }
                        if ($("#facility-booking-facility-link-modal").length) {
                            $("#facility-booking-facility-link-modal").dialog(
                                "close"
                            );
                        }
                        if ($("#facility-booking-recurring-modal").length) {
                            $("#facility-booking-recurring-modal").dialog(
                                "close"
                            );
                        }
                    }).dialog('open');
                    that.initFormElements();
                    resolve(response);
                },
                error: function (xhr, status, error) {
                    reject({ xhr, status, error });
                }
            });
        });
    },
    /*
    * Clear all jquery ui modal related to form
    */
    clearModals: function () {
        let modalRemoverFunction = function (modalId) {
            if ($('div[aria-describedby=' + modalId + ']').length) {
                $('#' + modalId).dialog('destroy').remove();
                $('div[aria-describedby=' + modalId + ']').remove();
            }
        };
        modalRemoverFunction('facility-booking-form-dialog');
        modalRemoverFunction('facility-booking-facility-link-modal');
        modalRemoverFunction('facility-booking-recurring-modal');
        modalRemoverFunction('facility-booking-recurring-warning-modal');
        modalRemoverFunction('facility-booking-action-modal');
        modalRemoverFunction('facility-booking-popup-note-modal');
        modalRemoverFunction('facility-booker-note-form-dialog');
        modalRemoverFunction('facility-booking-unavailability-confirmation-modal');
        modalRemoverFunction('facility-booking-managed-facility-booking-conflict-modal');
        if ($('#facility-booking-form-dialog').length == 0) {
            $('#booking-weekly-view-container').after($('<div id="facility-booking-form-dialog"></div>'));
        }
        if ($('#facility-booker-note-form-dialog').length == 0) {
            $('#booking-weekly-view-container').after($('<div id="facility-booker-note-form-dialog"></div>'));
        }
    },
    /**
     * Initialize form elements
     */
    initFormElements: function () {
        let that = this; // NOSONAR javascript:S7740
        let defaultSelectOptions = {
            width: '100%'
        };
        ($("#facility-chosen").is("select")) ? $('#facility-chosen').select2(defaultSelectOptions) : '';
        this.syncSelect2AriaLabelledBy('#facility-chosen');
        $('#facility_booking_facility_sub_type_form').select2(defaultSelectOptions);
        this.syncSelect2AriaLabelledBy('#facility_booking_facility_sub_type_form');
        that.updateExternalCustomerDetails();

        let facilityActiveFromDate = moment($('#facility-active-from').val());
        $('#booking_date').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: facilityActiveFromDate.isAfter(moment()) ? facilityActiveFromDate.toDate() : new Date(),
            maxDate: '+5y',
            beforeShowDay: function (date) {
                return that.checkDateAvailable(date, that);
            }
        });
        $('#booking_date').on('change', function () {
            that.updateRecurringStartAndEndDate();
            that.updateStartTimeAndEndTime();
        });

        //While create/edit booking we will have the selected day date
        that.updateStartTimeAndEndTime(1);

        let previousFacilityValue = $('#facility-chosen').val();
        $('#facility-chosen').on('focus', function () {
            previousFacilityValue = $(this).val();
        });
        $('#facility-chosen').on('change', function () {
            try { $(this).select2('close'); } catch (e) { }
            that.updateFormBasedOnFacility(previousFacilityValue, $(this).val());
        });
        that.initLinkFacilties();
        that.initRecurring();
        that.initAction();
        that.initPopUpNote();
        that.initManagedBookingConflicts();
        that.updateRecurringStartAndEndDate();
        that.validationActionsOnBookingTimeChange();
        that.validateActionTime();

        //Remove validation errors
        $('input').on('input', function () {
            $(this).parent().find('.validation-error-text').remove();
        });
        $('input, select').on('change', function () {
            $(this).parent().find('.validation-error-text').remove();
        });

        $('#submit-cancel-facility-booking-form-button').on('click', function () {
            $("#facility-booking-form-dialog").dialog('close');
        });

        //Facility Linked Unavailability confirmation modal
        $("#facility-booking-unavailability-confirmation-modal").dialog({
            modal: true,
            autoOpen: false,
            width: 500,
            height: 'auto',
            title: 'Facility Unavailablity'
        });
        that.oldFormData = that.formData();
        if ($('#recurring_booking_yes').is(':checked')) {
            that.populateRecurringSettingTable();
        }
    },
    /**
     * Ensure select2 combobox keeps external label association from the source select.
     */
    syncSelect2AriaLabelledBy: function (selector) {
        const $select = $(selector);
        if (!$select.length) {
            return;
        }
        const labelId = $select.attr('aria-labelledby');
        if (!labelId) {
            return;
        }
        const $selection = $select.next('.select2').find('.select2-selection');
        if (!$selection.length) {
            return;
        }
        const existing = ($selection.attr('aria-labelledby') || '').split(/\s+/).filter(Boolean);

        // Keep the external label id first so SRs announce label before selected value.
        const ordered = [labelId].concat(existing.filter((id) => id !== labelId));
        const unique = [];
        ordered.forEach((id) => {
            if (id && unique.indexOf(id) === -1) {
                unique.push(id);
            }
        });

        $selection.attr('aria-labelledby', unique.join(' '));
    },

    /**
     * initialization of managed booking conflicts modal
     */
    initManagedBookingConflicts: function () {
        //Facility booking managed facility booking conflict modal
        $("#facility-booking-managed-facility-booking-conflict-modal").dialog({
            modal: true,
            autoOpen: false,
            width: 500,
            height: 'auto',
            title: 'Booking Conflicts'
        });

        $('#facility-booking-managed-facility-booking-conflict-yes-btn').on('click', function () {
            $('#managed_facility_booking_conflicts_input').val(1);
            $('#facility-booking-managed-facility-booking-conflict-modal').dialog('close');
            $('#facility-booking-popup-note-modal').dialog('open');
            $('#facility-booking-request-save-btn').trigger("click");
        });
        $('#facility-booking-managed-facility-booking-conflict-cancel-btn').on('click', function () {
            $('#managed_facility_booking_conflicts_input').val(0);
            $('#facility-booking-managed-facility-booking-conflict-modal').dialog('close');
        });
    },

    /**
     * Update recurrence link 
     * 
     */
    addEditRecurrenceLink: function () {

        const $container = $('#recurring-setting-detail-container');
        if (!$container.length) return;

        $('#recurrence-edit-link-wrapper, #recurrence-edit-link').remove();

        const $table = $('#recurring-setting-detail-table');
        if (!$table.length) {
            // If the table isn't present yet, do nothing
            return;
        }

        // Remove any previous link/wrapper
        $('#recurrence-edit-link-wrapper').remove();
        $('#recurrence-edit-link').remove();

        // Create the <a> element
        const $link = $('<a>', {
            id: 'recurrence-edit-link',
            href: '#',
            text: 'Edit Recurrence',
            css: { fontSize: '12px' }
        });

        // Right-aligned wrapper, added directly AFTER the table
        const $linkWrapper = $('<div>', {
            id: 'recurrence-edit-link-wrapper',
            css: { textAlign: 'right', marginTop: '6px' }
        }).append($link);

        // Insert BELOW the table
        $table.after($linkWrapper);

        // Click triggers YES radio so existing handler opens #facility-booking-recurring-modal
        $('#recurrence-edit-link').off('click').on('click', (e) => {
            e.preventDefault();
            const $yes = $('#recurring_booking_yes');
            if ($yes.length && !$yes.is(':disabled')) {
                $yes.prop('checked', true);
                $yes.trigger('click');
            }
        });
    },
    /**
     * Update Recurring Start And End Date
     *
     */
    updateRecurringStartAndEndDate: function () {
        let that = this; // NOSONAR javascript:S7740
        let bookingDate = moment($('#booking_date').val(), 'DD/MM/YYYY');
        if ($('#recurring_start_date').val() == $('#recurring_end_date').val()) {
            $('#recurring_start_date').val($('#booking_date').val());
            $('#recurring_end_date').val($('#booking_date').val());
        }
        if (that.isDatepicker($('#recurring_start_date'))) {
            if (moment().isAfter(bookingDate, 'day')) {
                return false;
            }
            if ($('#recurring_start_date').is('[data-existing-date]')) {
                let existingStartDate = moment($('#recurring_start_date').attr('data-existing-date'), 'YYYY-MM-DD')
                if (bookingDate.isSameOrAfter(existingStartDate)) {
                    return false;
                }
            }
            $("#recurring_start_date").datepicker("option", "minDate", bookingDate.toDate());
            $("#recurring_end_date").datepicker("option", "minDate", bookingDate.add(1, 'days').toDate());
        }
    },
    /**
     * Cheack if its date picker
     */

    isDatepicker: function ($el) {
        return $el.hasClass('hasDatepicker') || $el.data('datepicker') !== undefined;
    },
    /**
     * Update form based on facility selected
     *
     */
    updateFormBasedOnFacility: function (prevFacilityId, newFacilityId) {
        let that = this; // NOSONAR javascript:S7740
        let $tr = $('#tr-facility-' + newFacilityId);
        let access = that.checkFacilityAccess($tr.attr('data-facility-area-owner-id'), $tr.attr('data-facility-id'), $tr.attr('data-facility-restricted-team-ids').split(','));
        if (access == false && $tr.attr('data-facility-default-booking-type') == that.constants.managed_facility && $tr.attr('data-facility-allow-booking-request') == 0) {
            that.facilityCanNotBeBookedPopUp();
            $('#facility-chosen').val(prevFacilityId).trigger('change.select2');
            return true;
        }
        if ($tr.attr('data-facility-default-booking-type') == that.constants.self_booked_facility) {
            access = false
        }

        //Clone the old form data so we can use in new form
        $('#customer_type').find(":selected").attr("selected", true);
        $('#external_customer_company').find(":selected").attr("selected", true);
        let $prevFacilityBookingFormClone = $('#create-facility-booking-form').clone(true);
        let $prevFacilityBookingRecurringSettingFormClone = $('#facility-booking-recurring-form').clone(true);
        $('.action-start-time').find(":selected").attr("selected", true);
        $('.action-end-time').find(":selected").attr("selected", true);
        let $prevFacilityBookingActionSettingFormClone = $('#facility-booking-action-form').clone(true);
        let bookingDate = moment($('#booking_date').val(), 'DD/MM/YYYY');
        //Open new form with new facility
        if ($('#facility-booking-id-hidden').length == 0) { //Create form
            if (access) {
                that.openFacilityBookingForm(newFacilityId, bookingDate.format('YYYY-MM-DD'), null, 1).then(function (response) {
                    that.updatePreviousFormDate($prevFacilityBookingFormClone, $prevFacilityBookingRecurringSettingFormClone, $prevFacilityBookingActionSettingFormClone);
                });
            } else {
                that.openFacilityBookingForm(newFacilityId, bookingDate.format('YYYY-MM-DD')).then(function (response) {
                    that.updatePreviousFormDate($prevFacilityBookingFormClone, $prevFacilityBookingRecurringSettingFormClone, $prevFacilityBookingActionSettingFormClone);
                });
            }
        } else { // Update booking
            if ($('#booking_date').val() == '') {
                $('#facility-chosen').val(prevFacilityId).trigger('change.select2');
                toastr.warning('Booking Date Required');
                return;
            }
            let bookingDate = moment($('#booking_date').val(), 'DD/MM/YYYY');
            let createRecord = access ? 1 : 0;
            if ($('#facility-booking-current-facility-id-hidden').val() == newFacilityId) {
                //facility is same update booking
                that.openFacilityBookingForm(newFacilityId, null, $('#facility-booking-id-hidden').val()).then(function (response) {
                });
            } else {
                //If facility is changed consider it as move of booking

                //Only same facility booking type can be moved
                if ($('#facility-default-booking-type').val() != $tr.attr('data-facility-default-booking-type')) {
                    that.openWarningMoveFacilityBookingType($('#facility-default-booking-type').val());
                    $('#facility-chosen').val(prevFacilityId).trigger('change.select2');
                    return;
                }

                that.openFacilityBookingForm(newFacilityId, null, $('#facility-booking-id-hidden').val(), createRecord, null, null, bookingDate.format('YYYY-MM-DD')).then(function (response) {
                });
            }
        }
    },
    /**
     * Update previous form data
     */
    updatePreviousFormDate: function ($prevFacilityBookingFormClone, $prevFacilityBookingRecurringSettingFormClone, $prevFacilityBookingActionSettingFormClone) {
        //Update booking title value
        $('#facility_request_booking_title').val($prevFacilityBookingFormClone.find('#facility_request_booking_title').val());
        //Update booking data
        if ($prevFacilityBookingFormClone.find('#booking_date').val() != '') {
            $('#booking_date').val($prevFacilityBookingFormClone.find('#booking_date').val()).trigger('change');
        }
        //Update recurring settings
        if ($prevFacilityBookingFormClone.find('#recurring_booking_yes:enabled:checked').length > 0) {

        }
        //Update Customer details
        $('#customer_type').val($prevFacilityBookingFormClone.find('#customer_type').val()).trigger('change.select2').trigger('change');
        $('#external_customer_company').val($prevFacilityBookingFormClone.find('#external_customer_company').val()).trigger('change.select2');;
        $('#customer_contact_name').val($prevFacilityBookingFormClone.find('#customer_contact_name').val());
        $('#customer_contact_telephone').val($prevFacilityBookingFormClone.find('#customer_contact_telephone').val());
        $('#customer_contact_email').val($prevFacilityBookingFormClone.find('#customer_contact_email').val());
        $('#requestor_name').val($prevFacilityBookingFormClone.find('#requestor_name').val());
        $('#requestor_detail').val($prevFacilityBookingFormClone.find('#requestor_detail').val());

        //Update actions
        let actionsSelected = [];
        $prevFacilityBookingActionSettingFormClone.find('.table-edit-action-row').each(function (index, element) {
            let actionId = $(element).attr('data-id');
            actionsSelected.push(actionId);
            $('#facility_booking_action_chose').val(actionsSelected).trigger('change.select2').trigger('change');
            $('.action-start-time[data-id="' + actionId + '"]').val(
                $prevFacilityBookingActionSettingFormClone.find('.action-start-time[data-id="' + actionId + '"]').find(":selected").val()
            );
            $('.action-end-time[data-id="' + actionId + '"]').val(
                $prevFacilityBookingActionSettingFormClone.find('.action-end-time[data-id="' + actionId + '"]').find(":selected").val()
            );
        });
    },
    /**
     * Facility cannot be booked popup
     *
     */
    facilityCanNotBeBookedPopUp: function () {
        $.facebox("<div><p style='padding: 11px; font-size: 12px;'>Right now this Facility does not accept any Booking Request.</p></div>");
    },
    /**
     * Pop up notes on submit
     */
    initPopUpNote: function () {
        let that = this; // NOSONAR javascript:S7740
        //Pop up note modal
        $('#facility-booking-popup-note-modal').dialog({
            title: $('input[name="save_recurrence_type"]').length == 0 ? 'Submit Facility Booking Request' : 'Submit Facility Booking',
            autoOpen: false,
            modal: true,
            width: 690,
            height: "auto"
        });

        $('#submit-facility-booking-form-button, #submit-confirm-facility-booking-form-button, #submit-pending-facility-booking-form-button, #submit-decline-facility-booking-form-button').on('click', function () {
            let valid = true;
            if (!that.validationActions()) {
                valid = false;
            }

            if (!that.validateBookingDate()) {
                valid = false;
            }

            if (!that.validateRecurring()) {
                valid = false;
            }

            //While submitting again revalidate managed booking conflicts
            $('#managed_facility_booking_conflicts_input').val(0);

            $('#facility-booking-declined-message-container').hide();
            if ($(this).attr('data-status') != undefined) { // Update status based on button click
                $('#facility-booking-current-status-hidden').val($(this).attr('data-status'));
                if ($(this).attr('data-status') == 'declined') { // Show declined reason textarea box
                    $('#facility-booking-declined-message-container').show();
                }
            }

            if (!that.validateChangeInData() && $('#move-facility-booking').length == 0) {
                if (!that.validateChangeInData()) {
                    $.facebox("No Changes Made.");
                }
                valid = false;
            }

            if (!valid) {
                return false;
            }

            //Hide instance selector when there is only one instance of booking
            if (
                $('#recurring_booking_no').is(':checked')
                ||
                ($('#facility-booking-recurrence-booking-available-count').length == 1 && $('#facility-booking-recurrence-booking-available-count').val() == 1)) {
                $('#facility-booking-popup-modal-instances-container').hide();
                $('#save_recurrence_type_all_series').prop('checked', true);
            }
            //One - off booking edit
            if (
                $('#recurring_booking_no').is(':checked')
                ||
                ($('#facility-booking-recurrence-booking-total-bookings-hidden').length == 1 && $('#facility-booking-recurrence-booking-total-bookings-hidden').val() == 1)) {
                $('#save_recurrence_type_only_one').prop('checked', true);
            }
            $('#facility-booking-popup-note-modal').dialog('open');
        });
        $('#facility-booking-request-cancel-btn').on('click', function () {
            $('#facility-booking-popup-note-modal').dialog('close');
        });
        $('#facility-booking-request-save-btn').on('click', function () {
            if (!that.validatePopup()) {
                return false;
            }
            that.submitFacilityBooking();
        });

        //Edit booking options
        if ($('input[name="save_recurrence_type"]').length == 0) {
            return;
        }
        $('#save_recurrence_type_start_date_range, #save_recurrence_type_end_date_range').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: new Date($('#update-recurrence-date-range').attr('data-recurrence-start-date')),
            maxDate: new Date($('#update-recurrence-date-range').attr('data-recurrence-end-date')),
            beforeShowDay: function (date) {
                return that.checkDateAvailable(date, that);
            }
        });

        $('input[name="save_recurrence_type"]').on('click', function () {
            $('input[name="save_recurrence_type"]:checked').val() == 'date_range' ? $('#update-recurrence-date-range').show() : $('#update-recurrence-date-range').hide();
        });
    },
    /**
     * Validate 
     */
    validateChangeInData: function () {
        let that = this; // NOSONAR javascript:S7740
        if ($('#facility-booking-id-hidden').length == 0) {
            return true;
        }
        let changesMade = !that.checkObjectsAreSame(that.oldFormData, that.formData());
        return changesMade;
    },
    /**
     * Validate popup
     *
     */
    validatePopup: function () {
        let validation = true;
        $('#facility-booking-update-instances-detail-form .validation-error-text').remove();
        if ($('#facility-booking-update-instances-detail-form').length > 0) {
            if ($('input[name="save_recurrence_type"]:checked').length == 0) {
                $('label[for="save_recurrence_type_all_series"]').after($('<span class="validation-error-text"></span>').text('Instance Type is Required.'));
                $('label[for="save_recurrence_type_all_series"]').after($('<br class="validation-error-text">'));
                validation = false;
            }
            if ($('#facility-booking-current-status-hidden').val() == 'declined' && $('#declined_reason').val() == '') {
                $('#declined_reason').after($('<span class="validation-error-text"></span>').text('Declined Reason Required.'));
                $('#declined_reason').after($('<br class="validation-error-text">'));
                validation = false;
            }
            if ($('input[name="save_recurrence_type"]:checked').val() == 'date_range') {
                if ($('#save_recurrence_type_start_date_range').val() == '') {
                    $('#save_recurrence_type_start_date_range').after($('<span class="validation-error-text"></span>').text('Start Date Required.'));
                    $('#save_recurrence_type_start_date_range').after($('<br class="validation-error-text">'));
                    validation = false;
                }
                if ($('#save_recurrence_type_end_date_range').val() == '') {
                    $('#save_recurrence_type_end_date_range').after($('<span class="validation-error-text"></span>').text('End Date Required.'));
                    $('#save_recurrence_type_end_date_range').after($('<br class="validation-error-text">'));
                    validation = false;
                }
            }
        }
        return validation;
    },
    /**
     * Submit facility booking
     */
    submitFacilityBooking: function () {
        let that = this; // NOSONAR javascript:S7740
        let facilityBookingId = null, createFacilityBooking = null;
        if ($('#facility-booking-id-hidden').length > 0) {
            facilityBookingId = $('#facility-booking-id-hidden').val();
        } else if ($('#facility-create-booking-record').length > 0) {
            createFacilityBooking = 1;
        }

        let url = that.storeFacilityBookingRequestUrl.replace(':facility', $('#facility-id').val());
        if (facilityBookingId != null) {
            url = that.editFacilityBookingUpdateUrl.replace(':facilityBooking', facilityBookingId);
        }

        //Create booking record
        if (createFacilityBooking != null) {
            url = that.storeFacilityBookingRecordUrl.replace(':facility', $('#facility-id').val());
        }

        //Move booking
        if ($('#move-facility-booking').length > 0) {
            url = that.moveFacilityBookingUrl.replace(':facility', $('#facility-id').val()).replace(':facilityBooking', facilityBookingId);
        }
        $.ajax({
            url: url,
            method: "POST",
            data: that.formData(),
            beforeSend: function () {
                $('#facility-booking-popup-container').hide();
                $('#popup-submit-spinner').show();
                $('#facility-booking-request-save-btn').prop('disabled', true);
            },
            success: function (data) {
                if (that.facilityBookingFilterInstance != null) {
                    that.facilityBookingFilterInstance.refreshFacilityBookingTr(data)
                } else if (that.filterBookingAdminInstance != null) { //Used in facility administrator page
                    that.filterBookingAdminInstance.getTabData($('#current-tab-selected-facility-administrator').val());
                }
                $('#facility-booking-popup-note-modal').dialog('close');
                $("#facility-booking-form-dialog").dialog('close');
                (createFacilityBooking != null) ? $.facebox("Facility booking record added successfully") : $.facebox("Facility booking record updated successfully");
            },
            error: function (xhr, status, error) {
                setTimeout(function () {
                    var $firstError = $('.validation-error-text:visible').first();
                    if ($firstError.length) {
                        var $input = $firstError.closest('td, div').find('input, select, textarea').filter(':visible').first();
                        if ($input.length) {
                            $input.focus();
                        }
                    }
                }, 50);
                $('.validation-error-text').remove();
                $('#facility-booking-popup-container').show();
                $('#popup-submit-spinner').hide();
                $('.validation-error-text').remove();
                $('#facility-booking-request-save-btn').prop('disabled', false);
                let data = JSON.parse(xhr.responseText);
                that.formValidationMessages(data.errors);
            },
            complete: function () {
                $('#facility-booking-popup-container').show();
                $('#popup-submit-spinner').hide();
                $('#facility-booking-request-save-btn').prop('disabled', false);
            }
        });
    },
    /**
     * Generate for data
     */
    formData: function () {
        let that = this; // NOSONAR javascript:S7740
        let facilityBookingId = null, createFacilityBooking = null;
        if ($('#facility-booking-id-hidden').length > 0) {
            facilityBookingId = $('#facility-booking-id-hidden').val();
        } else if ($('#facility-create-booking-record').length > 0) {
            createFacilityBooking = 1;
        }
        //Facility link
        let facilityBookingFacilityLink = [];
        $('input[name="facility_booking_facility_link_mandatory[]"]:checked').each(function (index, element) {
            facilityBookingFacilityLink.push($(element).val());
        });
        //Actions
        let actions = {};
        if ($('#facility_booking_action_chose').length > 0) {
            $('#facility_booking_action_chose').val().forEach(function (value, index) {
                actions[value] = {};
                actions[value]['actionStartTime'] = $('.action-start-time[data-id="' + value + '"]').val();
                actions[value]['actionEndTime'] = $('.action-end-time[data-id="' + value + '"]').val();
                actions[value]['action_id'] = value;
            });
        }

        let formData = {
            'overlap_validate': 1,
            'facility_availability_validation': 1,
            'facilityBookingMainData': that.fomatFormData($('#create-facility-booking-form').serializeArray()),
            'facilityBookingFacilityLink': facilityBookingFacilityLink,
            'facilityBookingRecurring': that.fomatFormData($('#facility-booking-recurring-form').serializeArray()),
            'facilityBookingActions': actions,
            'confirmAddUnavailableLinked': $('#facility-booking-unavailability-confirmation-data').val(),
            'managed_facility_booking_conflicts': $('#managed_facility_booking_conflicts_input').val()
        };
        //Contact details
        formData['facilityBookingMainData']['customer_type'] = $('#customer_type').val();
        formData['facilityBookingMainData']['external_customer_company'] = $('#external_customer_company').val();
        formData['facilityBookingMainData']['customer_contact_name'] = $('#customer_contact_name').val();
        formData['facilityBookingMainData']['customer_contact_telephone'] = $('#customer_contact_telephone').val();
        formData['facilityBookingMainData']['customer_contact_email'] = $('#customer_contact_email').val();
        formData['facilityBookingMainData']['requestor_name'] = $('#requestor_name').val();
        formData['facilityBookingMainData']['requestor_detail'] = $('#requestor_detail').val();

        if ($('#private_booking').length) {
            formData['facilityBookingMainData']['private_booking'] = $('#private_booking').val();
        }
        if ($('#recurring_booking_no').prop('disabled')) {
            formData['facilityBookingMainData']['recurring_booking_enable'] = "no";
        }
        if ($('#recurring_booking_yes').prop('checked')) {
            formData['facilityBookingMainData']['recurring_booking_enable'] = "yes";
        }
        if (that.administratorPage == 1) {
            formData['admin_page'] = 1;
        }
        if (facilityBookingId != null) {
            formData['facilityUpdateInstanceDetail'] = that.fomatFormData($('#facility-booking-update-instances-detail-form').serializeArray());
            formData['recurrence_overlap_check'] = 1;
            //For linked facility booking
            if ($('#facility-booking-is-linked-booking-hidden').val() == 1) {
                formData['facilityUpdateInstanceDetail'] = {};
                formData['facilityUpdateInstanceDetail']['save_recurrence_type'] = 'current';
                formData['facilityBookingMainData']['booking_date'] = $('#booking_date').val();
                formData['facilityBookingMainData']['booking_start_time'] = $('#booking_start_time').val();
                formData['facilityBookingMainData']['booking_end_time'] = $('#booking_end_time').val();
                formData['facilityBookingMainData']['is_linked_booking'] = 1;
            }
        }
        formData['facilityBookingListSetting'] = that.facilityBookingFilterInstance != null ? that.facilityBookingFilterInstance.getFacilityBookingListSetting() : '';
        return formData;
    },
    /*
    * Form submission validation message
    */
    formValidationMessages(errors) {
        let that = this; // NOSONAR javascript:S7740
        let facilityLinksValidation = [];
        let recurringValidation = [];
        let actionValidation = [];
        let managedFacilityBookingConflict = {};
        let unavailabilityValidationExists = false;
        Object.keys(errors).forEach((key) => {
            errors[key].forEach((message) => {
                let formatedKey = key.replace("facilityBookingMainData.", "").replace("facilityBookingFacilityLink.", "").replace("facilityBookingRecurring.", "").replace("facilityBookingActions.", "").replace("facilityBookerNoteData.", "").replace("facilityUpdateInstanceDetail.", "");
                let eleId = '#' + formatedKey;
                eleId = $(eleId + '_chosen').length > 0 ? eleId + '_chosen' : eleId; //Chosen select drop down
                $(eleId).after($('<span class="validation-error-text"></span>').text(message));
                $(eleId).after($('<br class="validation-error-text">'));
                if (key.includes('facilityBookingRecurring') || key.includes('recurring_booking_enable')) {
                    recurringValidation.push(message);
                }
                if (key.includes('facilityBookingActions')) {
                    actionValidation.push(message);
                }
                if (key.includes('facilityBookingFacilityLink.')) {
                    facilityLinksValidation.push(message);
                }
                //Validate overlap
                if (formatedKey == 'overlap_validate') {
                    that.overlapConflictValidation(JSON.parse(message));
                }
                //Validate recurrence ovrlap
                if (formatedKey == 'recurrence_overlap_check') {
                    $('#recurrence-overlap-validation').empty().append(
                        $('<div class="validation-error-text"></div>').text(
                            message
                        )
                    );
                }

                if (formatedKey == 'facility_availability_validation') { //Availability validation
                    that.availabilityValidation(JSON.parse(message));
                    unavailabilityValidationExists = true;
                }

                if (formatedKey == 'managed_facility_booking_conflicts') { //Facility Booking managed Conflicts
                    managedFacilityBookingConflict = JSON.parse(message);
                }
            });
        });
        if (facilityLinksValidation.length > 0) {
            $('#facility_booking_facility_link_open_frm').after($('<span class="validation-error-text"></span>').text(facilityLinksValidation.join(', ')));
            $('#facility_booking_facility_link_open_frm').after('<br class="validation-error-text">');
        }
        if (recurringValidation.length > 0) {
            $('label[for="recurring_booking_no"]').after($('<span class="validation-error-text"></span>').text(recurringValidation.join(', ')));
            $('label[for="recurring_booking_no"]').after('<br class="validation-error-text">');
        }
        if (actionValidation.length > 0) {
            $('#edit-action-open-frm').after($('<span class="validation-error-text"></span>').text(actionValidation.join(', ')));
            $('#edit-action-open-frm').after('<br class="validation-error-text">');
        }
        if (Object.keys(managedFacilityBookingConflict).length > 0 && unavailabilityValidationExists == false) {
            that.managedFacilityBookingConflict(managedFacilityBookingConflict);
        }
        $('#facility-booking-popup-note-modal').dialog('close');
    },
    /**
     * Conflicts in managed booking popup
     *  
     */
    managedFacilityBookingConflict: function (validation) {
        let selectedFacility = $('#facility-booking-facility-name').length > 0 ? $('#facility-booking-facility-name').text() : $("#facility-chosen option:selected").text();
        let $div = $('<div class="validation-error-text"></div>');
        Object.values(validation).forEach((dataFacility) => {
            let facilityName = '';
            let conflictDates = [];
            dataFacility.forEach(function (data) {
                facilityName = data['facilityName'];
                conflictDates.push(data['conflict_date']);
            });
            let showErrorMsg = facilityName == selectedFacility ?
                ('There are conflicts in Facility ' + facilityName + ' on following Dates ' + conflictDates.join(', ')) :
                ('There are conflicts in Linked Facility  ' + facilityName + ' on following Dates ' + conflictDates.join(', '));
            $div.append($('<div class="validation-error-text"></div>').text(showErrorMsg));
        });
        $('#facility-booking-managed-facility-booking-conflict-container').empty().append($div);
        $('#facility-booking-managed-facility-booking-conflict-modal').dialog('open');
    },
    /**
     * Overlap validation
     */
    overlapConflictValidation: function (validation) {
        let selectedFacility = $('#facility-booking-facility-name').length > 0 ? $('#facility-booking-facility-name').text() : $("#facility-chosen option:selected").text();
        let $div = $('<div class="validation-error-text"></div>');
        Object.values(validation).forEach((dataFacility) => {
            let facilityName = '';
            let conflictDates = [];
            dataFacility.forEach(function (data) {
                facilityName = data['facilityName'];
                conflictDates.push(data['conflict_date']);
            });
            let showErrorMsg = facilityName == selectedFacility ?
                ('There are conflicts in Facility ' + facilityName + ' on following Dates ' + conflictDates.join(', ')) :
                ('There are conflicts in Linked Facility  ' + facilityName + ' on following Dates ' + conflictDates.join(', '));
            $div.append($('<div class="validation-error-text"></div>').text(showErrorMsg));
        });
        $('#overlap-validation').empty().append($div);
    },
    /**
     * Availability Validation
     * 
     * @param {*} availabilityValidation 
     */
    availabilityValidation: function (availabilityValidation) {
        let that = this; // NOSONAR javascript:S7740
        let bookerAccess = true;
        $('#facility-booking-unavailability-confirmation-data').val('0');
        $('#facility-availability-validation').empty();
        let currentFacilityId = $('#facility-chosen option:selected').length == 0 ? $('#facility-chosen').val() : $('#facility-chosen option:selected').val();
        let currentFacilityName = $('#facility-chosen option:selected').length == 0 ? $('#facility-booking-facility-name').text() : $('#facility-chosen option:selected').text();
        var facilitiesAvailable = [currentFacilityName];
        let facilities = [
            {
                'facilityName': currentFacilityName,
                'mandatory': 1,
                'facilityId': currentFacilityId
            }
        ];
        $('input[name="facility_booking_facility_link_mandatory[]"]:checked').each(function (index, element) {
            facilitiesAvailable.push($(element).attr('data-facility-name'));
            facilities.push(
                {
                    'facilityName': $(element).attr('data-facility-name'),
                    'mandatory': $(element).is(":disabled") ? 1 : 0,
                    'facilityId': $(element).val()
                }
            );
        });
        let mandatory = false;

        //Check mandatory unavailable facilities 
        let unavailableFacilities = [];
        let message = [];
        let messageArchived = [];
        let messageFacilityInactive = [];
        availabilityValidation.forEach(function (data) {
            let unavailability = [];
            let archived = [];
            let inactiveFacility = [];
            data['unavailability'].forEach(function (data2) {
                if (data2['archived'] == 1) {
                    archived.push(
                        moment(data2['from']).format('DD/MM/YYYY')
                    );
                } else if (data2['active'] == 0) {
                    inactiveFacility.push(
                        moment(data2['from']).format('DD/MM/YYYY')
                    );
                } else {
                    unavailability.push(
                        moment(data2['from']).format('DD/MM/YYYY HH:mm') + ' to ' + moment(data2['to']).format('DD/MM/YYYY HH:mm')
                    );
                }
            });
            if (unavailability.length > 0) {
                const facilityType = currentFacilityName === data.facilityName ? 'Primary Facility' : 'Linked Facility';
                message.push(
                    `${facilityType} "${data.facilityName}" is unavailable in the following timeline: ${unavailability.join(', ')}`
                );
            }

            if (archived.length > 0) {
                messageArchived.push(
                    'Facility ' + data['facilityName'] + ' is Archived. So Bookings cannot be placed on this Days ' + archived.join(', ') + '.'
                )
            }

            if (inactiveFacility.length > 0) {
                messageFacilityInactive.push(
                    'Facility ' + data['facilityName'] + ' is active from ' + moment(data['facilityActiveFrom']).format('DD/MM/YYYY') + '. So Bookings cannot be placed on this Days ' + inactiveFacility.join(', ') + '.'
                )
            }

            let accessRole = that.checkFacilityAccess($('#facility-area-owner-booking-frm').val(), $('#facility-chosen').val(), $('#facility-restricted-bookers-booking-frm').val().split(','));
            if ($('#facility-default-booking-type').val() == that.constants.self_booked_facility && $('#facility-booking-is-linked-booking-hidden').val() != 1) {
                bookerAccess = false; // Self booked facility all are requestors
            }
            if (!accessRole) {
                bookerAccess = false;
            }
            facilities.forEach(function (facilityData) {
                if (data['facilityId'] == facilityData['facilityId']) {
                    if (facilityData['mandatory'] == 1) {
                        mandatory = true;
                    }
                    if (unavailability.length > 0) {
                        unavailableFacilities.push(facilityData['facilityName']);
                    }
                    if (!bookerAccess) {
                        // Find the index of the value
                        let index = facilitiesAvailable.indexOf(facilityData['facilityName']);
                        // Check if the value was found
                        if (index != -1) {
                            // Remove the element at the found index
                            facilitiesAvailable.splice(index, 1);
                        }
                    }
                }
            });
        });

        //Create error message element
        let $messageEle = $('<div></div>');

        if (messageArchived.length > 0) {
            $messageEle.append($('<span class="validation-error-text" style="display: block"></span>').append(
                messageArchived.join(', ')
            ));
        }
        if (messageFacilityInactive.length > 0) {
            $messageEle.append($('<span class="validation-error-text" style="display: block"></span>').append(
                messageFacilityInactive.join(', ')
            ));
        }
        if (message.length > 0) {
            $messageEle.append($('<span class="validation-error-text" style="display: block"></span>').append(
                (mandatory && !bookerAccess) ? (message.join(', ') + ' Please amend the time.') : (message.join(', ') + '.')
            ));
        }

        $('#facility-availability-validation').html($messageEle.clone());

        //If mandatory dont allow to sumit form by requestors
        if (mandatory && !bookerAccess) {
            return false;
        }
        $('#facility-booking-unavailability-confirmation-details').html($messageEle.clone().css('font-size', '12px'));
        $("#facility-booking-unavailability-confirmation-modal").dialog('open');

        //If non mandatory linked facility allow submit of form
        //If booker allow create of  booking
        $('#facility-linked-unavailability-save-btn').off().on('click', function () {
            $('#facility-booking-unavailability-confirmation-data').val(1);
            $("#facility-booking-unavailability-confirmation-modal").dialog('close');
            $('#facility-booking-popup-note-modal').dialog('open');
            $('#facility-booking-request-save-btn').trigger("click");
        });

        $('#bookings-can-be-created-on').html(
            $('<span></span>').text(
                bookerAccess
                    ?
                    'Are you sure you want to make Bookings on Unavailable Facilities - ' + unavailableFacilities.join(', ')
                    :
                    'Bookings can be created/updated on Facilities - ' + facilitiesAvailable.join(',')
            )
        );

        $('#facility-linked-unavailability-cancel-btn').off().on('click', function () {
            $("#facility-booking-unavailability-confirmation-modal").dialog('close');
        });
    },
    /**
     * Update Start Time and End Time based on availability
     */
    updateStartTimeAndEndTime: function (initialLoad = 0) {
        let that = this; // NOSONAR javascript:S7740
        let mDate = moment($('#booking_date').val(), "DD/MM/YYYY");
        let day = mDate.format('dddd');
        let availableDate = JSON.parse($('#facility-availability').val());
        let unavailableDate = JSON.parse($('#facility-unavailability').val());
        let timeSlots = that.getTimeSlots(availableDate['FCA_FacilityTimeFrom_' + day], availableDate['FCA_FacilityTimeTo_' + day], 15);
        let accessRole = that.checkFacilityAccess($('#facility-area-owner-booking-frm').val(), $('#facility-chosen').val(), $('#facility-restricted-bookers-booking-frm').val().split(','));
        if ($('#facility-default-booking-type').val() == that.constants.self_booked_facility && $('#facility-booking-is-linked-booking-hidden').val() != 1) {
            accessRole = false; // Self booked facility all are requestors
        }
        //Based on mark as unavailable
        let unAvailableTimeSlot = [];
        if (unavailableDate != null && accessRole == false) { // Booking admin/bookers can create bookings on unavailable time 
            let unStartDate = moment(unavailableDate['FMU_FacilityMarkUnavailableStartDate']);
            let unEndDate = moment(unavailableDate['FMU_FacilityMarkUnavailableEndDate']);
            if (mDate.isBetween(unStartDate, unEndDate, 'day', '[]') && unavailableDate['FMU_FacilityMarkUnavailableIsChecked_' + day] == 1) {
                unAvailableTimeSlot = that.getTimeSlots(unavailableDate['FMU_FacilityMarkUnavailableTimeFrom_' + day], unavailableDate['FMU_FacilityMarkUnavailableTimeTo_' + day], 15);
            }
        }

        $('#booking_start_time option').each(function (index, element) {
            $(element).prop("disabled", false);
            if (accessRole == false) { // Booking admin/bookers can create bookings on unavailable time 
                if (!timeSlots.includes($(element).val()) || unAvailableTimeSlot.includes($(element).val())) {
                    $(element).prop("disabled", true);
                }
            }
        });

        $('#booking_end_time option').removeAttr('data-disabled-based-on-start-time');

        $('#booking_end_time option').each(function (index, element) {
            $(element).prop("disabled", false);
            if (timeSlots.includes('23:45') && !unAvailableTimeSlot.includes('23:45')) {
                return true; // Enable End date time for midnight duty
            }
            if (accessRole == false) { // Booking admin/bookers can create bookings on unavailable time 
                if (!timeSlots.includes($(element).val()) || unAvailableTimeSlot.includes($(element).val())) {
                    $(element).prop("disabled", true);
                }
            }
        });

        //On change events
        $('#booking_start_time, #booking_end_time').off('change');

        //Disable end time drop downs
        $('#booking_start_time').on('change', function () {
            let startTimeSelected = $(this).val();
            let disable = false;
            $('[data-disabled-based-on-start-time="1"]').prop("disabled", false);
            let disabledOptionIndex = [];
            $('#booking_end_time option').each(function (index, element) {
                if ($(element).is(':disabled') && $(element).val() != '') {
                    disabledOptionIndex.push(index);
                };
            });
            let firstDisabledIndex = typeof disabledOptionIndex[0] == 'undefined' ? 0 : disabledOptionIndex[0];
            let selectedIndex = $('#booking_start_time').prop('selectedIndex');
            $('#booking_end_time option').each(function (index, element) {
                if (firstDisabledIndex > index && firstDisabledIndex > selectedIndex && index < selectedIndex) {
                    disable = true;
                }
                if (($(element).is(':disabled') && $(element).val() != '')) {
                    disable = true;
                }
                if (disable && !$(element).is(':disabled')) {
                    $(element).prop("disabled", true).attr('data-disabled-based-on-start-time', 1);
                }
                if ($(element).val() == startTimeSelected) {
                    $(element).prop("disabled", true).attr('data-disabled-based-on-start-time', 1);
                    disable = false;
                }
            });
            $("#booking_end_time").trigger('change.select2');
        });

        $('#booking_start_time, #booking_end_time').on('change', function () {
            $('#recurring-start-time').text($('#booking_start_time').val());
            $('#recurring-end-time').text($('#booking_end_time').val());
            $('#facility-booking-unavailability-confirmation-data').val(0);
            if ($('#recurring-setting-detail-table').length > 0) {
                that.populateRecurringSettingTable();
            }
        });

        if (initialLoad == 0) { //In inital load for editing we will have preselected time
            $("#booking_start_time").find("option:selected").is(":disabled") ? $("#booking_start_time").prop("selectedIndex", 0) : '';
            $("#booking_end_time").find("option:selected").is(":disabled") ? $("#booking_end_time").prop("selectedIndex", 0) : '';
        }
        $("#booking_start_time").trigger('change.select2');
        $("#booking_end_time").trigger('change.select2');
        $('#booking_start_time').trigger("change");
    },
    /**
     * Validate booking date
     */
    validateBookingDate: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#booking-date-container').find('.validation-error-text').remove();
        if (($('#booking_date').val() == '')
            ||
            ($('#facility-booking-is-linked-booking-hidden').val() == 1)
            ||
            ($('#booking_date').val() == $('#facility-booking-edit-existing-date-hidden').val())
        ) {
            return true;
        }
        let bookingStartDate = moment($('#booking_date').val(), "DD/MM/YYYY").format('YYYY-MM-DD');
        let validDate = that.checkDateAvailable(bookingStartDate)[0];
        if (!validDate) {
            $('#booking_date').after($('<div class="validation-error-text">Selected booking date is not available.</div>'));
        }
        return validDate;
    },
    /**
    /**
     * Check date is available
     * @param {*} date
     * @param {*} that currentt facility booking instance
     */
    checkDateAvailable: function (date, thatP = null) {
        let that = thatP != null ? thatP : this;
        let mdate = moment(date);
        let day = mdate.format('dddd');
        let availableDate = JSON.parse($('#facility-availability').val());
        let unavailableDate = JSON.parse($('#facility-unavailability').val());
        let available = true;
        let accessRole = that.checkFacilityAccess($('#facility-area-owner-booking-frm').val(), $('#facility-chosen').val(), $('#facility-restricted-bookers-booking-frm').val().split(','));

        if ($('#facility-default-booking-type').val() == that.constants.self_booked_facility && $('#facility-booking-is-linked-booking-hidden').val() != 1) {
            accessRole = false; // Self booked facility all are requestors
        }

        //Based on availability
        //Bookers/admin can raise on unavailability day
        if (availableDate['FCA_FacilityTimeAvailability_' + day] == 0 && accessRole == false) {
            available = false;
        }

        //Based on mark as unavailable
        //Bookers/admin can raise on unavailability day
        if (unavailableDate != null && accessRole == false) {
            let unStartDate = moment(unavailableDate['FMU_FacilityMarkUnavailableStartDate']);
            let unEndDate = moment(unavailableDate['FMU_FacilityMarkUnavailableEndDate']);
            let unStartTime = moment(unavailableDate['FMU_FacilityMarkUnavailableTimeFrom_' + day], "HH:mm");
            let unEndTime = moment(unavailableDate['FMU_FacilityMarkUnavailableTimeTo_' + day], "HH:mm");
            if (mdate.isBetween(unStartDate, unEndDate, 'day', '[]') && unavailableDate['FMU_FacilityMarkUnavailableIsChecked_' + day] == 1
                && unStartTime.format("HH:mm") == '00:00' && unEndTime.format("HH:mm") == '00:00') {
                available = false;
            }
        }

        //self booked facility - Self booking availability
        if ($('#facility-default-booking-type').val() == that.constants.self_booked_facility && accessRole == false) {
            let fromDays = $('#facility-allow-self-booking-from').val();
            let toDays = $('#facility-allow-self-booking-to').val();
            let daysFromNow = moment().add(fromDays, 'days');
            let daysToNow = moment().add(toDays, 'days');
            if (!mdate.isBetween(daysFromNow, daysToNow, 'day', '[]')) {
                available = false;
            }
        }

        // Archive facility
        if ($.trim($('#facility-archive-from').val()) != '') {
            let archiveDateM = moment($('#facility-archive-from').val(), "YYYY-MM-DD");
            if (mdate.isSameOrAfter(archiveDateM)) {
                available = false;
            }
        }

        return [
            available
        ];
    },
    /**
     * Initialize Linked Facilities
     */
    initLinkFacilties: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#facility_booking_facility_link_open_frm').on('click', function () {
            $('#facility-booking-facility-link-modal').dialog('open');
        });
        //Facility Link
        $('#facility-booking-facility-link-modal').dialog({
            title: 'Edit Link Facility',
            autoOpen: false,
            modal: true,
            width: 600,
            height: "auto"
        });
        $('#facility-booking-facility-link-save-btn').on('click', function () {
            that.facilityLinkString();
            $('#facility-booking-unavailability-confirmation-data').val(0);
            $('#facility-booking-facility-link-modal').dialog('close');
        });
        that.facilityLinkString();
    },
    /**
     * Recurring form
     */
    initRecurring: function () {
        let that = this; // NOSONAR javascript:S7740
        let currentStartDateM = null;
        $('input[name="recurring_booking_enable"]').on('click', function () {
            if ($(this).val() != 'no' && that.bookingDateSelected()) {
                currentStartDateM = moment($('#booking_date').val(), "DD/MM/YYYY");
                $('#facility-booking-recurring-modal').dialog('open');
            } else {
                $('#recurring_booking_no').prop("checked", true);
                $('#recurring-setting-detail-container').empty();
            }
        });
        $('#facility-booking-recurring-modal').dialog({
            title: 'Recurring Booking',
            autoOpen: false,
            modal: true,
            width: 550,
            height: "auto"
        });

        let previousValue = $('input[name="recurring_booking_enable"]:checked').val();
        $('#facility-booking-recurring-warning-modal').dialog({
            title: 'Recurring Booking Warning',
            autoOpen: false,
            modal: true,
            width: 350,
            height: 'auto',
            minHeight: 69.9906,
            close: function () {
                if (previousValue === 'no') {
                    $('input[name="recurring_booking_enable"][value=yes]').prop('checked', true);
                    previousValue = 'yes';
                    if (that.validateRecurring()) {
                        that.populateRecurringSettingTable();
                    }
                }
            }
        });

        $('input[name="recurring_booking_enable"]').on('change', function () {
            let newValue = $(this).val();
            if (newValue !== previousValue && newValue === 'no') {
                that.initRecurringBookingWarningPopup();

            }
            previousValue = newValue;
        });

        $('#booking-recurring-confirm-warning').on('click', function () {
            previousValue = 'yes';
            $('#facility-booking-recurring-warning-modal').dialog('close');
        });

        $('#facility-booking-recurring-save-btn').on('click', function () {
            if (that.validateRecurring()) {
                that.populateRecurringSettingTable();
            }
        });
        let recuStartDate = moment($('#booking_date').val(), "DD/MM/YYYY");
        let existingRecurringStartdate = $('#recurring_start_date').attr('data-existing-date');
        if (existingRecurringStartdate != undefined) {
            recuStartDate = moment(existingRecurringStartdate, "YYYY-MM-DD");
        }

        //Recurrence start date
        if (!$('#recurring_start_date').prop('readonly')) {
            $('#recurring_start_date').datepicker({
                firstDay: 6,
                changeMonth: true,
                changeYear: true,
                dateFormat: "dd/mm/yy",
                minDate: recuStartDate.toDate(),
                maxDate: '+5y',
                beforeShowDay: function (date) {
                    return that.checkDateAvailable(date, that);
                }
            });
            $('#recurring_start_date').on('change', function () {
                if (currentStartDateM == null) {
                    return
                }
                let recurStartDateM = moment($(this).val(), "DD/MM/YYYY");
                if (existingRecurringStartdate == undefined && currentStartDateM.isSameOrBefore(recurStartDateM)) {
                    $('#booking_date').val(recurStartDateM.format("DD/MM/YYYY"));
                }
            });
        }

        //Recurrence end date
        $('#recurring_end_date').datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: recuStartDate.toDate(),
            maxDate: '+5y',
            beforeShowDay: function (date) {
                return that.checkDateAvailable(date, that);
            }
        });
        $('#booking_start_time').select2({
            width: '100%',
            dropdownParent: $('#booking_start_time').parent(),
            matcher: (new CommonFunction).select2CustomMatch
        });
        that.syncSelect2AriaLabelledBy('#booking_start_time');
        $('#booking_end_time').select2({
            width: '100%',
            dropdownParent: $('#booking_end_time').parent(),
            matcher: (new CommonFunction).select2CustomMatch
        });
        that.syncSelect2AriaLabelledBy('#booking_end_time');

        $('input[name="recurring_booking_recurrence_type"]').on('click', function () {
            $('#daily-recurrence-setting-container, #weekly-recurrence-setting-container').hide();
            $(this).val() == 'weekly' ? $('#weekly-recurrence-setting-container').show() : $('#daily-recurrence-setting-container').show();
        })
        $('#customer_type').select2({
            width: '100%',
            dropdownParent: $('#customer_type').parent()
        });
        that.syncSelect2AriaLabelledBy('#customer_type');
    },
    /**
     * Check booking date is selected
     * 
     */
    bookingDateSelected: function () {
        let selected = true;
        $('.check-date-selected').remove();
        if ($('#booking_date').val() == '') {
            selected = false;
            $('#recurring-booking-detail-container').append($('<div class="validation-error-text check-date-selected">Please Select Booking Date.</div>'));
        }
        return selected;
    },
    /**
     * Validate recurring
     */
    validateRecurring: function () {
        let that = this; // NOSONAR javascript:S7740

        $('#recurring-booking-detail-container').find('.validation-error-text').remove();
        $('#facility-booking-recurring-modal').find('.validation-error-text').remove();;
        if (!$('#recurring_booking_yes').is(':checked') || $('#recurring_booking_yes').is(':disabled')) {
            return true;
        }

        let recurStartDate = moment($('#recurring_start_date').val(), "DD/MM/YYYY");
        let recurEndDate = moment($('#recurring_end_date').val(), "DD/MM/YYYY");
        let bookingDate = moment($('#booking_date').val(), "DD/MM/YYYY");
        let existingRecurringStartdate = $('#recurring_start_date').is('[data-existing-date]') ? moment($('#recurring_start_date').attr('data-existing-date'), "YYYY-MM-DD") : null;
        let noError = true;

        //Validate start and end date
        if ($('#recurring_start_date').val() == '') {
            noError = false;
            $('#recurring_start_date').after($('<div class="validation-error-text">The Start Date is required.</div>'));
        } else if (!that.checkDateAvailable(recurStartDate.format('YYYY-MM-DD'))[0] && $('#recurring_start_date').attr('data-existing-date') != recurStartDate.format('YYYY-MM-DD')) {
            noError = false;
            $('#recurring_start_date').after($('<div class="validation-error-text">Selected Start Date is not available.</div>'));
        }

        if ($('#recurring_start_date').val() != '' && $('#facility-booking-id-hidden').length == 0 && bookingDate.isAfter(recurStartDate)) {
            noError = false;
            $('#recurring_start_date').after($('<div class="validation-error-text">Selected Start Date is less than Booking Date.</div>'));
        }

        if ($('#recurring_end_date').val() == '') {
            noError = false;
            $('#recurring_end_date').after($('<div class="validation-error-text">The End Date is required.</div>'));
        } else if (!that.checkDateAvailable(recurEndDate.format('YYYY-MM-DD'))[0] && $('#recurring_end_date').attr('data-existing-date') != recurEndDate.format('YYYY-MM-DD')) {
            noError = false;
            $('#recurring_end_date').after($('<div class="validation-error-text">Selected End Date is not available.</div>'));
        }

        if (recurStartDate > recurEndDate) {
            noError = false;
            $('#recurring_start_date').after($('<span class="validation-error-text">Invalid Start/End Date Selection for Recurrence.</span>'));
        }

        //Validate based on recurring type
        let recurrenceType = $('input[name="recurring_booking_recurrence_type"]:checked').val();
        if (recurrenceType == '' || typeof recurrenceType == 'undefined') {
            noError = false;
            $('[for="recurring_booking_weekly"]').after($('<div class="validation-error-text">The Recurrence Type field is required when Recurring Booking is yes.</div>'));
        } else if (recurrenceType == 'daily') { //daily recurring
            if ($('[name="recurring_booking_recurrence_daily_days"]').val() == '') {
                noError = false;
                $('#recurring-booking-recurrence-daily-days').after($('<span class="validation-error-text">The Recurrence Day\'s field is required when Recurrence Type is daily.</span>'));
            }
        } else if (recurrenceType == 'weekly') { //Weekly recurring
            if ($('[name="recurring_booking_recurrence_weekly_weeks"]').val() == '') {
                noError = false;
                $('#recurring-booking-recurrence-weekly-days').after($('<div class="validation-error-text">The Recurrence Week\'s field is required when Recurrence Type is weekly.</div>'));
            }
            if ($('input[name="recurring_booking_weekly_days[]"]:checked').length == 0) {
                noError = false;
                $('#recurring-booking-recurrence-weekly-days').after($('<div class="validation-error-text">The Week days field is required when Recurrence Type is weekly.</div>'));
            }
        }

        if (noError) {
            $('#facility-booking-recurring-modal').dialog('close');
        } else {
            $('#facility-booking-recurring-modal').dialog('open');
        }
        return noError;
    },
    /**
     * Recurring settings table
     */
    populateRecurringSettingTable: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#recurring-setting-detail-container').empty();
        if ($('#recurring_booking_no').is(':checked') || !that.validateRecurring()) {
            return;
        }
        $table = $('<table id="recurring-setting-detail-table" style="font-size: 10px;border-collapse: inherit;width: 100%; margin-top: 4px;"></table>');
        $tableBody = $('<tbody></tbody>');
        let $tr1 = $('<tr></tr>');
        $tr1.append($('<td></td>').text('Start Date : ' + $('#recurring_start_date').val()));
        $tr1.append($('<td></td>').text('End Date : ' + $('#recurring_end_date').val()));
        $tr1.append($('<td>Start Time : <span id="recurring-setting-detail-start-datetime"></span></td>'));
        $tr1.append($('<td>End Time : <span id="recurring-setting-detail-end-datetime"></span></td>'));
        $tableBody.append($tr1);
        let $tr2 = $('<tr></tr>');

        //Weekly selected days
        let selectedDays = [];
        $('input[name="recurring_booking_weekly_days[]"]:checked').each(function () {
            selectedDays.push(that.capitalizeFirstLetter($(this).val()));
        });

        $tr2.append($('<td colspan="4"></td>').text(
            $('#recurring_booking_daily').is(':checked') ?
                ('Recur every ' + $('#recurring_booking_recurrence_daily_days').val() + ' day(s)')
                :
                ('Recur every ' + $('#recurring_booking_recurrence_weekly_weeks').val() + ' weeks(s) on ' + selectedDays.join(', '))
        ));

        $tableBody.append($tr2);

        //Check for unavailable days
        let unavailableDates = that.getUnAvailableRecurringDates();
        if (unavailableDates.length > 0 && that.validateChangeInData()) { // No need to show in edit
            let $tr3 = $('<tr></tr>');
            $tr3.append($('<td colspan="4"></td>').append(
                $('<span style="color: #c96800"></span>').text('Note: The Facility will not be Booked on the Following Days due to Unavailability or because the Date does not fall on a Recurring Day - ' + unavailableDates.join(', '))
            ));
            $tableBody.append($tr3);
        }
        $header = $('<span></span>').text('Recurring Setting' + (
            $('#facility-booking-recurrence-id').length > 0 ? (' Series ID: ' + $('#facility-booking-recurrence-id').val()) : ''
        ));
        $('#recurring-setting-detail-container').append($header);
        $table.append($tableBody);
        $('#recurring-setting-detail-container').append($table);
        $('#recurring-setting-detail-start-datetime').text($('#booking_start_time').val());
        $('#recurring-setting-detail-end-datetime').text($('#booking_end_time').val());

        //"Update recurrence" link on edit forms
        if ($('#facility-booking-is-linked-booking-hidden').val() != 1) {
            this.addEditRecurrenceLink();
        }
    },
    /**
     * Generate unavailable recurring dates
     */
    getUnAvailableRecurringDates: function () {
        let that = this; // NOSONAR javascript:S7740
        let startDate = moment($('#recurring_start_date').val(), 'DD/MM/YYYY');
        let startDateM = moment(startDate.format('YYYY-MM-DD'));
        let recurrenceType = $('input[name="recurring_booking_recurrence_type"]:checked').val();

        //Weekly selected days
        let selectedDays = [];
        $('input[name="recurring_booking_weekly_days[]"]:checked').each(function () {
            selectedDays.push(that.capitalizeFirstLetter($(this).val()));
        });
        let dayInterval = $('#recurring_booking_recurrence_daily_days').val();
        let weekInterval = $('#recurring_booking_recurrence_weekly_weeks').val();
        const endDateM = moment($('#recurring_end_date').val(), 'DD/MM/YYYY');

        const recurrenceAvailableDates = [];
        while (startDateM.isSameOrBefore(endDateM)) {
            if (startDateM.isBefore(moment())) {
                startDateM.add(1, 'days');
                continue; //No need to check past days
            }
            const dayName = startDateM.format('dddd'); // e.g., 'Monday'
            const daysPassed = startDateM.diff(startDate, 'days');
            const weeksPassed = Math.floor(daysPassed / 7);

            if (recurrenceType === 'weekly') {
                if (!selectedDays.includes(dayName)) {
                    startDateM.add(1, 'days');
                    continue;
                }
                if (weekInterval > 1 && weeksPassed % weekInterval !== 0) {
                    startDateM.add(1, 'days');
                    continue;
                }
            } else if (recurrenceType === 'daily') {
                if (dayInterval > 1 && daysPassed % dayInterval !== 0) {
                    startDateM.add(1, 'days');
                    continue;
                }
            }

            recurrenceAvailableDates.push(startDateM.format('YYYY-MM-DD'));
            startDateM.add(1, 'days');
        }

        let unavailableDates = [];
        //Check start date is in recurring
        if (!recurrenceAvailableDates.includes(startDate.format('YYYY-MM-DD')) && !startDate.isBefore(moment())) {
            unavailableDates.push(startDate.format('DD/MM/YYYY'));
        }
        //Check end date is in recurring
        if (!recurrenceAvailableDates.includes(endDateM.format('YYYY-MM-DD')) && !endDateM.isBefore(moment())) {
            unavailableDates.push(endDateM.format('DD/MM/YYYY'));
        }
        //Check in recurrence available dates
        recurrenceAvailableDates.forEach(function (date) {
            if (!that.checkDateAvailable(date)[0]) {
                unavailableDates.push(moment(date).format('DD/MM/YYYY'));
            }
        });

        // Sort ascending
        unavailableDates.sort(function (a, b) {
            return moment(a, 'DD/MM/YYYY').diff(moment(b, 'DD/MM/YYYY'));
        });

        return unavailableDates;
    },
    /**
     * Capitalize first letter
     */
    capitalizeFirstLetter: function (str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    },
    /**
     * Init action form elements
     */
    initAction: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#facility-booking-action-modal').dialog({
            title: 'Edit Action',
            autoOpen: false,
            modal: true,
            width: 500,
            height: "auto"
        });
        $('#facility_booking_action_chose').select2({
            width: '100%',
            dropdownParent: $('#facility_booking_action_chose').parent()
        }).change(function () {
            that.editActionsData();
        });
        $('#edit-action-open-frm').on('click', function () {
            $('#action-edit-container').find('.validation-error-text').remove();
            if ($('#booking_start_time').val() == '' || $('#booking_end_time').val() == '') {
                $('#edit-action-open-frm').after($('<div class="validation-error-text"></div>').text('Please select Start and End Time.'));
                return false;
            }
            $('#facility-booking-action-modal').dialog('open');
            that.validationActions(closeDialog = false);
        });
        $('#facility-booking-action-save-btn').on('click', function () {
            if (!that.validationActions()) {
                return false;
            }
            $('#facility-booking-action-modal').dialog('close');
            that.populateActionsString();
        });
        that.populateActionsString();
    },
    /**
     * Validate actions
     */
    validationActions: function (closeDialog = true) {
        const $bookingStart = $('#booking_start_time');
        const $bookingEnd = $('#booking_end_time');

        // Clear previous errors
        let validationErrors = [];
        $('#actions-validation-ul').remove();

        const showErrors = function (errors) {
            const $ul = $('<ul id="actions-validation-ul" style="font-size: 12px;color: red;"></ul>');
            errors.forEach((msg) => $ul.append($('<li></li>').text(msg)));
            $('#facility-booking-action-details-table').after($ul);
            $('#facility-booking-action-modal').dialog('open');
        };

        // If there are no action rows, nothing to validate
        const rowCount = $("#facility-booking-action-details-table-body tr").length;
        if (rowCount === 0) return true;

        // --- Basic booking-level validation ---
        if ($bookingStart.val() === '' || $bookingEnd.val() === '') {
            validationErrors.push('Please set the Booking Start Time and End Time.');
            showErrors(validationErrors);
            return false;
        }

        // Strict parsing of booking times (HH:mm)
        const outerStart = moment($bookingStart.val(), "HH:mm", true);
        const outerEnd = moment($bookingEnd.val(), "HH:mm", true);

        if (!outerStart.isValid() || !outerEnd.isValid()) {
            validationErrors.push('Booking Start/End must be in HH:mm format.');
            showErrors(validationErrors);
            return false;
        }

        // Overnight booking only when end <= start
        const isOvernight = outerEnd.isSameOrBefore(outerStart);
        if (isOvernight) {
            outerEnd.add(1, 'day');
        }

        // Bind action times to the booking's anchor day
        const anchorDay = outerStart.clone().startOf('day');
        const bindToAnchorDay = (hhmmStr) => {
            const t = moment(hhmmStr, "HH:mm", true);
            if (!t.isValid()) return null;
            let dt = anchorDay.clone()
                .hour(t.hour())
                .minute(t.minute())
                .second(0)
                .millisecond(0);

            // If overnight bookings, shift it to next day
            if (isOvernight) {
                const bookingStartClock = moment(outerStart.format("HH:mm"), "HH:mm", true);
                const bookingEndClock = moment(outerEnd.format("HH:mm"), "HH:mm", true);
                const thisClock = moment(hhmmStr, "HH:mm", true);

                if (thisClock.isBefore(bookingStartClock) || thisClock.isSameOrBefore(bookingEndClock)) {
                    dt.add(1, 'day');
                }
            }
            return dt;
        };

        // Sort action rows
        const $rows = $('.table-edit-action-row');
        const $tbody = $rows.parent();

        const sortedRows = $rows.get().sort((a, b) => {
            const actionStartA = bindToAnchorDay($(a).find('.action-start-time').val());
            const actionStartB = bindToAnchorDay($(b).find('.action-start-time').val());
            return actionStartA.diff(actionStartB);
        });

        $tbody.empty().append(sortedRows);

        // Iterate over sorted rows
        const $sortedRows = $('.table-edit-action-row');
        $sortedRows.each(function (index1, element) {
            const $el = $(element);
            const actionName = $el.find('td:first').text().trim();
            const $actionStartTime = $el.find('.action-start-time');
            const $actionEndTime = $el.find('.action-end-time');

            //Required check
            if ($actionStartTime.val() === '' || $actionEndTime.val() === '') {
                validationErrors.push(`${actionName}: Start Time and End Time are required.`);
                return true; // continue to next row
            }

            // Parse raw clock times
            let rawStart = moment($actionStartTime.val(), "HH:mm", true);
            let rawEnd = moment($actionEndTime.val(), "HH:mm", true);

            if (!rawStart.isValid() || !rawEnd.isValid()) {
                validationErrors.push(`${actionName}: Invalid time format (use HH:mm).`);
                return true;
            }

            if (rawStart.isSame(rawEnd)) {
                validationErrors.push(`${actionName}: Start Time and End Time cannot be the same.`);
                return true;
            }

            // Bind to anchor day
            let innerStart = bindToAnchorDay($actionStartTime.val());
            let innerEnd = bindToAnchorDay($actionEndTime.val());
            if (!innerStart || !innerEnd) {
                validationErrors.push(`${actionName}: Invalid time format (use HH:mm).`);
                return true;
            }

            // Overnight action only when end <= start
            if (innerEnd.isSameOrBefore(innerStart)) {
                innerEnd.add(1, 'day');
            }

            // clock based validation
            const bookingStartClock = moment(outerStart.format("HH:mm"), "HH:mm", true);
            const bookingEndClock = moment(outerEnd.format("HH:mm"), "HH:mm", true);

            const actionStartClock = rawStart;
            const actionEndClock = rawEnd;

            let isStartValid, isEndValid;

            if (isOvernight) {
                // Overnight rule valid if >= start OR <= end
                isStartValid =
                    actionStartClock.isSameOrAfter(bookingStartClock) ||
                    actionStartClock.isSameOrBefore(bookingEndClock);

                isEndValid =
                    actionEndClock.isSameOrAfter(bookingStartClock) ||
                    actionEndClock.isSameOrBefore(bookingEndClock);
            } else {
                // Normal booking
                isStartValid =
                    actionStartClock.isSameOrAfter(bookingStartClock) &&
                    actionStartClock.isSameOrBefore(bookingEndClock);

                isEndValid =
                    actionEndClock.isSameOrAfter(bookingStartClock) &&
                    actionEndClock.isSameOrBefore(bookingEndClock);
            }

            if (!isStartValid && !isEndValid) {
                validationErrors.push(`${actionName}: Both Start and End times are outside the Booking window.`);
            } else if (!isStartValid) {
                validationErrors.push(`${actionName}: Start Time does not fall within the Booking window.`);
            } else if (!isEndValid) {
                validationErrors.push(`${actionName}: End Time does not fall within the Booking window.`);
            }

            // --- Overlap check with other actions ---
            $sortedRows.each(function (index2, element2) {
                if (index1 === index2) return true; // skip same row

                const $el2 = $(element2);
                const actionName2 = $el2.find('td:first').text().trim();

                //Use the same binding logic for comparison ranges
                let range2Start = bindToAnchorDay($el2.find('.action-start-time').val());
                let range2End = bindToAnchorDay($el2.find('.action-end-time').val());
                if (!range2Start || !range2End) return true;

                if (range2End.isSameOrBefore(range2Start)) {
                    range2End.add(1, 'day');
                }

                // No overlap when one ends at or before the other's start
                const noOverlap = innerEnd.isSameOrBefore(range2Start) || range2End.isSameOrBefore(innerStart);

                if (!noOverlap) {
                    validationErrors.push(`${actionName}: overlaps with ${actionName2}.`);
                }
            });
        });

        // Display errors (if any)
        if (validationErrors.length > 0) {
            showErrors(validationErrors);
            return false;
        }

        if (closeDialog) {
            $('#facility-booking-action-modal').dialog('close');
        }
        return true;
    },
    /**
     * Validate actions when Start / End time of booking changes
     */
    validationActionsOnBookingTimeChange() {
        $('#booking_start_time, #booking_end_time').on('input change', () => this.validationActions());
    },

    /**
     * Validate actions when Start / End time of action changes
     */
    validateActionTime: function () {
        $('#facility-booking-action-details-table-body')
            .on('change input', '.action-start-time, .action-end-time', () => this.validationActions(false));
    },

    /**
     * Populate Action string
     */
    populateActionsString: function () {
        $('#actions-detail').empty();

        let $ul = $('<ul style="list-style-type: none; padding: unset;"></ul>');
        let bookingActionDetail = [];

        // Booking start time
        let bookingStartStr = $('#booking_start_time').val();
        let bookingStart = moment(bookingStartStr, 'HH:mm');
        $('#facility_booking_action_chose :selected').each(function () {
            let details = {};

            details.start_time = $('.action-start-time[data-id="' + $(this).val() + '"]').val();
            details.end_time = $('.action-end-time[data-id="' + $(this).val() + '"]').val();
            details.action_name = $(this).text();
            bookingActionDetail.push(details);
        });

        bookingActionDetail.sort(function (a, b) {
            let aStart = moment(a.start_time, 'HH:mm');
            let bStart = moment(b.start_time, 'HH:mm');

            if (aStart.isBefore(bookingStart)) {
                aStart.add(1, 'day');
            }
            if (bStart.isBefore(bookingStart)) {
                bStart.add(1, 'day');
            }

            let diff = aStart.diff(bStart);
            return diff;
        });

        bookingActionDetail.forEach(function (element) {
            let $li = $('<li></li>').text(
                element.start_time + ' - ' + element.end_time + ' ' + element.action_name
            );
            $ul.append($li);
        });

        $('#actions-detail').append($ul);
    },
    /**
     * Update external/internal customer details
     */
    updateExternalCustomerDetails: function () {
        $('.external-required').hide();

        // --- Helpers: read & write visible fields ---
        const readVisible = () => ({
            name: $('#customer_contact_name').val() || '',
            tel: $('#customer_contact_telephone').val() || '',
            email: $('#customer_contact_email').val() || ''
        });

        const writeVisible = (contactDetail = {}) => {
            $('#customer_contact_name').val(contactDetail.name || '');
            $('#customer_contact_telephone').val(contactDetail.tel || '');
            $('#customer_contact_email').val(contactDetail.email || '');
        };

        // --- Helpers: read & write hidden mirrors for a type ---
        const readHidden = (type) => ({
            name: $(`#${type}_contact_name`).val() || '',
            tel: $(`#${type}_contact_telephone`).val() || '',
            email: $(`#${type}_contact_email`).val() || ''
        });

        const writeHidden = (type, contactInfo = {}) => {
            $(`#${type}_contact_name`).val(contactInfo.name || '');
            $(`#${type}_contact_telephone`).val(contactInfo.tel || '');
            $(`#${type}_contact_email`).val(contactInfo.email || '');
        };

        // --- Populate external from selected company option (same as yours, but also mirrors to hidden) ---
        const dataPopulateFunction = function () {
            const $externalCompanyName = $('#external_customer_company');
            if (!$externalCompanyName.val()) return;

            const externalCustomerJson = $externalCompanyName.find(':selected').attr('data-external-customer');
            if (!externalCustomerJson) return;

            let extData;
            try {
                extData = JSON.parse(externalCustomerJson);
            } catch (e) {
                return;
            }

            const contactDetails = {
                name: extData['EC_ContactName'] ?? '',
                tel: extData['EC_ContactNumber'] ?? '',
                email: extData['EC_ContactEmail'] ?? ''
            };

            // Populate visible + mirror to EXTERNAL hidden
            writeVisible(contactDetails);
            writeHidden('external', contactDetails);
        };

        // Initialize external company <select> with Chosen and hook change
        $('#external_customer_company').select2({
            width: '100%',
            dropdownParent: $('#external_customer_company').parent()
        }).change(function () {
            if ($('#customer_type').val() === 'external') {
                dataPopulateFunction();
            }
        });
        this.syncSelect2AriaLabelledBy('#external_customer_company');

        // Mirror user edits into the hidden set for the active type
        $('#customer_contact_name, #customer_contact_telephone, #customer_contact_email')
            .on('input change', function () {
                const activeType = $('#customer_type').val(); // 'internal' | 'external'
                writeHidden(activeType, readVisible());
            });

        // On load: seed the mirrors from current visible (so immediate toggle-back restores correctly)
        writeHidden($('#customer_type').val(), readVisible());

        // Handle type switching
        $('#customer_type').on('change', function () {
            const newType = $(this).val(); // 'internal' or 'external'

            // Always clear visible fields when switching type (original behaviour)
            writeVisible({ name: '', tel: '', email: '' });

            if (newType === 'internal') {
                // Hide external UI
                $('.external-customer-container').hide();
                // Hide asterisks for external fields
                $('.external-required').hide();

                // Restore from INTERNAL hidden mirrors (if empty, it stays blank)
                writeVisible(readHidden('internal'));
            } else {
                // Show external UI
                $('.external-customer-container').show();
                // Show asterisks for external fields
                $('.external-required').show();

                // If a company is selected, populate from it and mirror to EXTERNAL hidden,
                // otherwise restore the last EXTERNAL hidden values (if any)
                if ($('#external_customer_company').val()) {
                    dataPopulateFunction();
                } else {
                    writeVisible(readHidden('external'));
                }
            }
        });
    },


    /**
     * Reset the Cancel Booking dialog state
     */
    resetCancelBookingDialog: function () {

        $('#error_text').empty();

        $('#facility-booking-cancellation-form input[type="radio"]').off('change');
        $('#cancel-booking').off('click');

        $('input[name="cancel_booking"]').prop('checked', false);

        $('#cancel_range_recur').hide();
        $('#cancel_some_recur').hide().empty();

        const $from = $('#cancel_booking_from_date');
        const $to = $('#cancel_booking_to_date');


        if ($from.data('datepicker') || $from.hasClass('hasDatepicker')) {
            $from.datepicker('destroy');
        }
        if ($to.data('datepicker') || $to.hasClass('hasDatepicker')) {
            $to.datepicker('destroy');
        }

        $from.val('').removeClass('hasDatepicker');
        $to.val('').removeClass('hasDatepicker');
    },

    clearCancelVariants: function () {
        // Hide & empty the "some" list
        $('#cancel_some_recur').hide().empty();

        // Hide & clear the range-from/to and destroy any datepicker instances
        const $from = $('#cancel_booking_from_date');
        const $to = $('#cancel_booking_to_date');

        if ($from.data('datepicker') || $from.hasClass('hasDatepicker')) {
            try { $from.datepicker('destroy'); } catch (e) { }
        }
        if ($to.data('datepicker') || $to.hasClass('hasDatepicker')) {
            try { $to.datepicker('destroy'); } catch (e) { }
        }
        $from.val('').removeClass('hasDatepicker');
        $to.val('').removeClass('hasDatepicker');

        $('#cancel_range_recur').hide();
    },

    /**
     * Edit actions
     */
    editActionsData: function () {
        let selectedActions = $('#facility_booking_action_chose').val();

        // Normalize selectedActions to an array (handles first open / null / single select)
        if (!selectedActions) {
            selectedActions = [];
        } else if (!Array.isArray(selectedActions)) {
            selectedActions = [selectedActions];
        }

        let $startTimeOptions = $('#booking_start_time').html();
        let $endTimeOptions = $('#booking_end_time').html();
        let $startTimeValue = $('#booking_start_time').val();
        let $endTimeValue = $('#booking_end_time').val();

        let alreadyExisting = [];

        // REMOVE ACTION
        $('.table-edit-action-row').each(function (index, element) {
            const id = $(element).attr('data-id');
            if (selectedActions.indexOf(id) === -1) {
                $(element).remove();
            }
        });

        // Rebuild alreadyExisting AFTER removals
        $('.table-edit-action-row').each(function (index, element) {
            alreadyExisting.push($(element).attr('data-id'));
        });

        // Find the end time of the last existing row (if any)
        let lastExistingEndTime = null;
        if (alreadyExisting.length > 0) {
            const lastId = alreadyExisting[alreadyExisting.length - 1];
            // Safe selector escape for quotes
            const safeLastId = String(lastId).replace(/"/g, '\\"');
            const $lastRow = $('.table-edit-action-row[data-id="' + safeLastId + '"]');
            const endVal = $lastRow.find('.action-end-time').val();
            if (endVal) {
                lastExistingEndTime = endVal;
            }
        }

        // ADD ACTION
        $('#facility_booking_action_chose :selected').each(function (index, element) {
            const val = $(element).attr('value');

            // Skip if row already exists
            if (alreadyExisting.indexOf(val) !== -1) {
                return;
            }

            // Start time: last existing row's end time if available, else booking start time
            const startTimeForNew = lastExistingEndTime || $startTimeValue;

            let $tr = $('<tr class="table-edit-action-row"></tr>').attr('data-id', val);
            $tr.append(
                $('<td></td>').text($(element).text()),
                $('<td></td>').append(
                    $('<select class="action-start-time"></select>')
                        .append($startTimeOptions)
                        .val(startTimeForNew)
                        .attr('data-id', val)
                ),
                $('<td></td>').append(
                    $('<select class="action-end-time"></select>')
                        .append($endTimeOptions)
                        .val($endTimeValue)
                        .attr('data-id', val)
                )
            );

            $('#facility-booking-action-details-table-body').append($tr);

            alreadyExisting.push(val);
        });

        this.validationActions(closeDialog = false);
    },
    /**
     * Populate facility link string
     */
    facilityLinkString: function () {
        let linkedFacility = [];
        $('input[name="facility_booking_facility_link_mandatory[]"]:checked').each(function (index, element) {
            linkedFacility.push($(element).attr('data-facility-name'));
        });
        $('#facility-booking-facility-link-text').remove();
        let $span = $('<span id="facility-booking-facility-link-text"></span>').text(linkedFacility.join(', '));
        $('#facility_booking_facility_link_open_frm').before($span);
    },
    /**
     * Get time slot between two time
     */
    getTimeSlots: function (startTime, endTime, intervalMinutes) {

        const start = moment(startTime, 'HH:mm');
        let end;

        if (endTime.slice(0, 5) === "00:00") {
            // Treat "00:00" as "23:45" of the same day
            end = moment("23:45", 'HH:mm');
        } else {
            end = moment(endTime, 'HH:mm');
            if (end.isBefore(start)) {
                // If end is before start, assume it's the next day
                end.add(1, 'day');
            }
        }

        let times = [];
        let current = start.clone();

        while (current <= end) {
            times.push(current.format('HH:mm'));
            current.add(intervalMinutes, 'minutes');
        }

        return times;
    },
    /*
     * Format form data
    */
    fomatFormData(data) {
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
     * View Facility Booking history
     */
    viewFacilityBookingHistory: function (bookingId) {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.historyFacilityBookingDataUrl.replace(':facilityBooking', bookingId),
            method: "POST",
            data: {
                '_token': that.token
            },
            success: function (data) {
                $.facebox(data);
            },
            error: function (xhr, status, error) {
            }
        });
    },

    /**
     * Delete Scheduler note
     * 
     * @param {*} schedulerNoteId 
     */
    openSchedulerNoteDeletForm: function (schedulerNoteId) {
        let that = this; // NOSONAR javascript:S7740
        let url = that.schedulerNoteDeleteUrl.replace(':facilityBookerNote', schedulerNoteId);
        $('#facility-booker-note-delete-form').attr('action', url);
        $('#delete-scheduler-note-modal').dialog('open');
    },

    /**
     * View Scheduler Note history
     */
    viewSchedulerNoteHistory: function (schedulerNoteId) {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.historySchedulerNoteDataUrl.replace(':facilityBookerNote', schedulerNoteId),
            method: "POST",
            data: {
                '_token': that.token
            },
            success: function (data) {
                $.facebox(data);
            },
            error: function (xhr, status, error) {
            }
        });
    },
    /**
     * Code to check to objects are same
     */
    checkObjectsAreSame: function (a, b) {
        if (Object.is(a, b)) return true;

        // If either is not an object (or is null), they must be strictly equal to be same
        if (typeof a !== 'object' || a === null ||
            typeof b !== 'object' || b === null) {
            return false;
        }

        const seen = new WeakMap(); // cycle detection

        function eq(x, y) {
            if (Object.is(x, y)) return true;
            if (typeof x !== 'object' || x === null ||
                typeof y !== 'object' || y === null) {
                return false;
            }

            // Cycle detection
            const xSeen = seen.get(x);
            if (xSeen && xSeen.get(y)) return true;
            if (!xSeen) seen.set(x, new WeakMap());
            seen.get(x).set(y, true);

            const xIsArray = Array.isArray(x);
            const yIsArray = Array.isArray(y);
            if (xIsArray !== yIsArray) return false;

            if (xIsArray) {
                if (x.length !== y.length) return false;
                for (let i = 0; i < x.length; i++) {
                    if (!eq(x[i], y[i])) return false;
                }
                return true;
            }

            // Plain objects
            // (If you want to ignore prototype differences, remove this check)
            if (Object.getPrototypeOf(x) !== Object.getPrototypeOf(y)) {
                return false;
            }

            const xKeys = Object.keys(x);
            const yKeys = Object.keys(y);
            if (xKeys.length !== yKeys.length) return false;

            for (const k of xKeys) {
                if (!Object.prototype.hasOwnProperty.call(y, k)) return false;
                if (!eq(x[k], y[k])) return false;
            }
            return true;
        }

        return eq(a, b);
    }
}

module.exports = FacilityBooking;
