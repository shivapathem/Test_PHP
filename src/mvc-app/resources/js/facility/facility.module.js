
/**
 * Facility JavaScript
 *
 * @param {Object} p parameters
 */
var Facility = function (p) {
    this.createUrl = p.createUrl;
    this.storeUrl = p.storeUrl;
    this.editUrl = p.editUrl;
    this.facilityAdministratorUrl = p.facilityAdministratorUrl;
    this.facilityAdministratorStoreUrl = p.facilityAdministratorStoreUrl;
    this.updateUrl = p.updateUrl;
    this.facilityBookersUrl = p.facilityBookersUrl;
    this.facilityShowUrl = p.facilityShowUrl;
    this.deleteFormUrl = p.deleteFormUrl;
    this.deleteUrl = p.deleteUrl;
    this.archiveFormUrl = p.archiveFormUrl;
    this.archiveUrl = p.archiveUrl;
    this.facilityHistoryUrl = p.facilityHistoryUrl;
    this.futureBookingCountUrl = p.futureBookingCountUrl;
    this.init();
};

Facility.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#facility-tabs").tabs();
        that.createForm();
        that.updateForm();
        that.showFacilty();
        that.deleteForm();
        that.archiveForm();
        that.facilityAdministratorForm();
        that.viewFacilityHistory();
        that.removeAriaLabelby();
        that.filterExpandCollapsed();
    },
    /*
    * Create form
    */
    createForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#create-facility-button").on("click", function () {
            $.ajax({
                url: that.createUrl,
                method: "GET",
                beforeSend: function () {
                    that.clearModals();
                },
                success: function (data) {
                    $("#facility-form-dialog").html(data).dialog({
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        draggable: false,
                        title: "Create Facility",
                        open: function (event, ui) {
                            $(this).parent().css({
                                'top': '2rem',
                                'width': '92%',
                                'left': '4%',
                                'right': '4%'
                            });

                            // Convert ui-dialog to h1 for Accessibility
                            const $t = $(this).closest(".ui-dialog").find(".ui-dialog-title");
                            if (!$t.is("h1")) {
                                $t.replaceWith(
                                    $("<h1/>", { id: $t.attr("id"), class: $t.attr("class"), text: $t.text() })
                                        .css({ fontSize: "inherit", fontWeight: "inherit" })
                                );
                            }
                        }
                    }).dialog('open');
                    that.initFormElements();
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },

    removeAriaLabelby: function () {
        $('div[aria-labelledby="ui-id-1"]').removeAttr('aria-labelledby');
    },
    /**
     * Show faciltiy
     */
    showFacilty: function () {
        let that = this; // NOSONAR javascript:S7740
        $(".view-facility-icon").on("click", function () {
            $.ajax({
                url: that.facilityShowUrl.replace(':facility', $(this).attr('data-facility-id')),
                method: "GET",
                beforeSend: function () {
                    that.clearModals();
                },
                success: function (data) {
                    $("#facility-form-dialog").html(data).dialog({
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        draggable: false,
                        title: "View Facility",
                        open: function (event, ui) {
                            $(this).parent().css({
                                'top': '2rem',
                                'width': '92%',
                                'left': '4%',
                                'right': '4%'
                            });

                            // Convert ui-dialog to h1 for Accessibility
                            const $t = $(this).closest(".ui-dialog").find(".ui-dialog-title");
                            if (!$t.is("h1")) {
                                $t.replaceWith(
                                    $("<h1/>", { id: $t.attr("id"), class: $t.attr("class"), text: $t.text() })
                                        .css({ fontSize: "inherit", fontWeight: "inherit" })
                                );
                            }
                        }
                    }).dialog('open');
                    that.initFormElements();
                    that.initEditActions();
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    /*
    * Update form
    */
    updateForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('.edit-facility-icon').on('click', function () {
            let facilityId = $(this).attr('data-facility-id');
            $.ajax({
                url: that.editUrl.replace(':facility', facilityId),
                method: "GET",
                beforeSend: function () {
                    that.clearModals();
                },
                success: function (data) {
                    $("#facility-form-dialog").html(data).dialog({
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        draggable: false,
                        title: "Edit Facility",
                        open: function (event, ui) {
                            $(this).parent().css({
                                'top': '2rem',
                                'width': '92%',
                                'left': '4%',
                                'right': '4%'
                            });

                            // Convert ui-dialog to h1 for Accessibility
                            const $t = $(this).closest(".ui-dialog").find(".ui-dialog-title");
                            if (!$t.is("h1")) {
                                $t.replaceWith(
                                    $("<h1/>", { id: $t.attr("id"), class: $t.attr("class"), text: $t.text() })
                                        .css({ fontSize: "inherit", fontWeight: "inherit" })
                                );
                            }
                        }
                    }).dialog('open');
                    that.initFormElements();
                    that.initEditActions();
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    /*
    * Clear all jquery ui modal related to form
    */
    clearModals: function () {
        let removerFunction = function (modalId) {
            if ($('div[aria-describedby=' + modalId + ']').length) {
                $('#' + modalId).dialog('destroy').remove();
                $('div[aria-describedby=' + modalId + ']').remove();
            }
        };
        removerFunction('facility-form-dialog');
        removerFunction('location-external-uk-modal');
        removerFunction('location-external-international-modal');
        removerFunction('facility-link-modal');
        removerFunction('facility-technical-setup-modal');
        removerFunction('facility-type-modal');
        removerFunction('facility-availability-modal');
        removerFunction('facility-change-booking-type-modal');
        removerFunction('facility-mark-as-unavailable-modal');
        removerFunction('booking-type-confirm-modal');
        removerFunction('booking-Private-confirm-modal');
        removerFunction('facility-linked-facility-validation-modal');
        removerFunction('facility-administrator-form-dialog');
        if ($('#facility-form-dialog').length == 0) {
            $('#facility-tabs').after($('<div id="facility-form-dialog"></div>'));
        }
        if ($('#facility-administrator-form-dialog').length == 0) {
            $('#facility-tabs').after($('<div id="facility-administrator-form-dialog"></div>'));
        }
    },
    /*
    * Initialize facility form elements
    */
    initFormElements: function () {
        let that = this; // NOSONAR javascript:S7740
        let defaultSelectOptions = {
            width: '98%'
        };
        //External Uk location
        $("#location-external-uk-modal").dialog({
            title: 'Local Address',
            autoOpen: false,
            modal: true,
            width: 700,
            height: "auto"
        });
        //External international location
        $('#location-external-international-modal').dialog({
            title: 'International Address',
            autoOpen: false,
            modal: true,
            width: 500,
            height: "auto"
        });
        //Facility Link
        $('#facility-link-modal').dialog({
            title: 'Link Facility',
            autoOpen: false,
            modal: true,
            width: 600,
            height: "auto"
        });
        $('#facility_link_chose').chosen(defaultSelectOptions);
        that.initFacilityLink();

        //Facility Availability
        that.initFacilityAvailability();
        that.initUnavailabilityConfirmDialog();

        //Facility Type
        that.initFacilityType();

        //Facility Technical Setup
        $('#facility-technical-setup-modal').dialog({
            title: 'Technical Setup',
            autoOpen: false,
            modal: true,
            width: 600,
            height: "auto",
            close: function () {
                that.restoreTechnicalSetupValues();
            }
        });
        $('#technical_setup_open_frm').on('click', function () {
            that.storeTechnicalSetupValues();
            $('#facility-technical-setup-modal').dialog("open");
        });
        that.initTechnicalSetup();

        //active from trigger calendar
        $("#active_from_frm").datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: 0
        });

        if ($('#area_owner_frm').is("select")) {
            $('#area_owner_frm').chosen(defaultSelectOptions).change(function () {
                that.updateRestrictBookersList();
            });
        }

        $('#restrict_bookers_frm').chosen(defaultSelectOptions);
        if (!$('#area_owner_frm').is("select") && $('#area_owner_frm').val()) {
            that.updateRestrictBookersList();
        }
        $('#provider_type_frm').chosen(defaultSelectOptions).change(function () {
            that.locationFormInit('add');
        });
        $('#internal_location_frm').chosen(defaultSelectOptions)

        $('#facility_accessible_frm').chosen(defaultSelectOptions);


        $("#facility_accessible_frm").change(function () {
            that.accessibilityDisable();
        });

        that.accessibilityDisable();

        $('#facility_make_bookings_private_frm').chosen(defaultSelectOptions);

        //Default booking type
        $('#facility_default_booking_frm').chosen(defaultSelectOptions);
        $('#facility_default_booking_frm').on('change', function () {
            const bookingType = $(this).val();
            const isViewOnly = $('#facility-form-view-only').val() == 1;
            const isCreate = $('#submit-create-facility-form-button').length > 0;

            const allowBookingRadios = $('input[name="allow_booking_frm"]');
            const oneOffBookingRadios = $('input[name="on_off_booking_frm"]');

            // Reset first
            allowBookingRadios.prop('disabled', true);
            oneOffBookingRadios.prop('disabled', true);

            if (isViewOnly) {
                return;
            }

            if (isCreate) {
                // SELF BOOKED
                if (bookingType === 'self_booked') {
                    // Force Allow Booking = YES & lock it
                    $('#allow_booking_yes_frm').prop('checked', true);
                    allowBookingRadios.prop('disabled', true);
                    // Enable One-Off Booking radios
                    oneOffBookingRadios.prop('disabled', false);
                    // Force Reset One-Off Booking values
                    $('#on_off_booking_yes_frm').prop('checked', false);
                    $('#on_off_booking_no_frm').prop('checked', false);

                }
                // MANAGED
                if (bookingType === 'managed') {
                    // Force One-Off Booking = NO & lock it
                    $('#on_off_booking_no_frm').prop('checked', true);
                    oneOffBookingRadios.prop('disabled', true);
                    // Enable Allow Booking radios
                    allowBookingRadios.prop('disabled', false);
                    // Force Reset Allow Booking values
                    $('#allow_booking_yes_frm').prop('checked', false);
                    $('#allow_booking_no_frm').prop('checked', false);
                }
            }

            if (!isCreate) {
                if (bookingType === 'self_booked') {
                    oneOffBookingRadios.prop('disabled', false);
                    allowBookingRadios.prop('disabled', true);
                }

                if (bookingType === 'managed') {
                    allowBookingRadios.prop('disabled', false);
                    oneOffBookingRadios.prop('disabled', true);
                }
            }

            // Allow self booking range
            const disableSelfBookingRange = bookingType === 'managed';
            $('#allow_self_booking_from_frm').prop('disabled', disableSelfBookingRange);
            $('#allow_self_booking_to_frm').prop('disabled', disableSelfBookingRange);
            //Update Restrict bookers form field based on default booking type
            that.updateRestrictBookerField();
        });

        //Submit create form
        $('#submit-create-facility-form-button').on("click", function () {
            that.submitForm();
        });

        //Submit update form
        $('#submit-update-facility-form-button').on("click", function () {
            that.submitForm($(this).attr('data-facility-id'));
        });

        //Remove validation errors
        $('input').on('input', function () {
            $(this).parent().find('.validation-error-text').remove();
        });
        $('input').on('change', function () {
            $(this).parent().find('.validation-error-text').remove();
        });
        $('select').on('change', function () {
            $(this).parent().find('.validation-error-text').remove();
        });

        //Mark as Unavailable
        $('#facility-mark-as-unavailable-modal').dialog({
            title: 'Mark as Unavailable',
            autoOpen: false,
            modal: true,
            width: 500,
            height: "auto",
            close: function () {
                that.restoreMarkAsUnavailableValues();
            }
        });

        //Validation popup of linked facility
        $('#facility-linked-facility-validation-modal').dialog({
            title: 'Linked Facility Validation',
            autoOpen: false,
            modal: true,
            width: 500,
            height: "auto",
            open: function (event, ui) {
                $(this).parent().find(".ui-dialog-titlebar-close").hide();
            }
        });

        $('#mark_as_unavailable_open_frm').on('click', function () {
            that.storeMarkAsUnavailableValues();
            $('#facility-mark-as-unavailable-modal').dialog("open");
        });
        that.initMarkAsUnavailable();
        that.validationFormFields();
        // Disable booking fields on popup open
        that.disableBookingFieldsOnLoad();

        // Primary Facility toggle linked facility
        that.initPrimaryFacility();

        //Update Restrict bookers form field based on default booking type
        that.updateRestrictBookerField();
    },
    /**
     * Update Restrict bookers form field based on default booking type
     */
    updateRestrictBookerField: function () {
        let defaultBookingType = $('#facility_default_booking_frm').val();
        $('#restrict-bookers-select-container').hide();
        $('#restrict-bookers-text-container').hide();
        if (defaultBookingType == '' || defaultBookingType == 'managed') {
            $('#restrict-bookers-select-container').show();
        } else {
            $('#restrict-bookers-text-container').show();
            $('#restrict_bookers_frm').val([]);
            $('#restrict_bookers_frm').trigger('chosen:updated');
        }
    },
    /**
     * Disable accessibility notes based on accessibility type
     */
    accessibilityDisable: function () {
        $('#accessibility_notes_frm').prop('disabled', false);
    },

    /**
     * Validate before submit
     */
    validateSubmitForm: function () {
        let that = this; // NOSONAR javascript:S7740
        let validate = true;
        if (!that.validateFacilityAvailability()) {
            $('#facility-availability-modal').dialog("open");
            validate = false;
        }
        if (!that.validateMarkUnavailability()) {
            $('#facility-mark-as-unavailable-modal').dialog("open");
            validate = false;
        }
        if (!that.validateChangeBookingType()) {
            $('#facility-change-booking-type-modal').dialog("open");
            validate = false;
        }
        if (!that.validateFacilityType()) {
            validate = false;
        }
        return validate;
    },
    /**
     * Validate fields
     */
    validationFormFields: function () {
        let that = this; // NOSONAR javascript:S7740
        //Facility Capacity Validation
        $('#facility_capacity_frm').on('blur', function () {
            that.validateCapcityField();
        });
        //Self Booking From Validation
        $('#allow_self_booking_from_frm').on('blur', function () {
            that.validateSelfBookingFrom();
        });
        //Self Booking To Validation
        $('#allow_self_booking_to_frm').on('blur', function () {
            that.validateSelfBookingTo();
        });
    },
    /**
     * Validate capacity field
     */
    validateCapcityField: function () {
        let facility_capacity_frm_val = $('#facility_capacity_frm').val();
        $('#facility_capacity_frm').parent().find('.validation-error-text').remove();
        if (parseInt(facility_capacity_frm_val) < 0 || parseInt(facility_capacity_frm_val) > 9999) {
            $('#facility_capacity_frm').after($('<span class="validation-error-text"></span>').text("Facility Capacity value should be minimum of 0 and maximum of 9999"));
            return false;
        }
        return true;
    },
    /**
     * Validate self booking from
     */
    validateSelfBookingFrom: function () {
        let allow_self_booking_frm_val = $('#allow_self_booking_from_frm').val();
        $('#allow_self_booking_from_frm').parent().find('.validation-error-text').remove();
        if (parseInt(allow_self_booking_frm_val) < 0 || parseInt(allow_self_booking_frm_val) > 999) {
            $('#allow_self_booking_from_frm').after($('<span class="validation-error-text"></span>').text("Self Booking From value should be minimum of 0 and maximum of 999"));
            return false;
        }
        return true;
    },
    /**
     * Validate self booking to
     */
    validateSelfBookingTo: function () {
        let allow_self_booking_to_frm_val = $('#allow_self_booking_to_frm').val();
        $('#allow_self_booking_to_frm').parent().find('.validation-error-text').remove();
        if (parseInt(allow_self_booking_to_frm_val) < 0 || parseInt(allow_self_booking_to_frm_val) > 999) {
            $('#allow_self_booking_to_frm').after($('<span class="validation-error-text"></span>').text("Self Booking To value should be minimum of 0 and maximum of 999"));
            return false;
        }
        return true;
    },
    /*
    * Edit form elements
    */
    initEditActions: function () {
        let that = this; // NOSONAR javascript:S7740
        //Locations
        if ($("#location-external-uk").is(":visible")) {
            that.locationFormInit('edit');
            that.saveExternalLocationUk();
        }
        if ($("#location_external_international_open_frm").is(":visible")) {
            that.locationFormInit('editExtInt');
            that.saveExternalLocationInternational();
        }
        //Facility Availability
        that.populateFacilityAvailabilityString();
        //Facility Unavailability
        that.populateFacilityUnAvailabilityString();
        //Facility Type
        that.facilityTypeUpdateString();
        //Facility link
        that.populateFacilityLinkString();
        //Facility Equipments
        that.technicalSetupPopulateString();
        //Default booking type
        $('#facility_default_booking_frm').trigger("change");
        //Change booking type
        that.initChangeBookingType();
        //Mark as Unavailable
        that.initMarkAsUnavailable();
        //mark as decline or confirm on select self book
        that.initBookingTypeConfirmPopup(that)
        //show worning message
        that.initPrivateConfirmPopup()
        // Change booking type summary
        that.populateChangeBookingTypeString();
    },

    initPrivateConfirmPopup: function () {

        const $select = $('#facility_make_bookings_private_frm');
        let previousValue = $select.val();
        let pendingValue = null;

        $('#booking-Private-confirm-modal').dialog({
            title: 'Confirm Booking Privacy Policy',
            autoOpen: false,
            modal: true,
            width: 380,
            height: 'auto',
            closeOnEscape: true,
            close: function () {
                if (pendingValue !== null) {
                    $select.val(previousValue).trigger('chosen:updated');
                    pendingValue = null;
                }
            }
        });

        function updateModalText(value) {
            let text = '';
            if (value == 'yes') {
                text = '<strong>Yes:</strong> All future bookings will be made <strong>Private</strong>.';
            } else if (value == 'no') {
                text = '<strong>No:</strong> Existing private bookings will remain unchanged.';
            } else if (value === 'summary') {
                text = '<strong>Summary:</strong> All future bookings will be made <strong>Show Summary</strong> except Private bookings.';
            }
            $('#booking-Private-confirm-modal p').html(text);
        }

        $select.on('change', function () {
            const newValue = $(this).val();
            if (newValue !== previousValue && newValue != 'no') {
                pendingValue = newValue;
                updateModalText(pendingValue); // Update modal text
                $('#booking-Private-confirm-modal').dialog('open');
            }
        });

        $('#booking-type-confirm-no').on('click', function () {
            $select.val(pendingValue).trigger('chosen:updated');
            previousValue = pendingValue;
            pendingValue = null;
            $('#booking-Private-confirm-modal').dialog('close');
        });
    },

    initBookingTypeConfirmPopup: function (that) {

        let $select = $('#facility_default_booking_frm');
        let previousValue = $select.val();
        let pendingValue = null;

        $('#booking-type-confirm-modal').dialog({
            title: 'Mark Booking Status',
            autoOpen: false,
            modal: true,
            width: 350,
            height: 'auto',
            close: function () {
                if (pendingValue !== null) {
                    $select.val(previousValue).trigger('chosen:updated').trigger('change');
                    pendingValue = null;
                }

                $('input[name="booking_type_confirm"]').prop('checked', false);
            }
        });

        $select.on('change', function () {

            let newValue = $(this).val();
            let facilityId = $('#submit-update-facility-form-button').data('facility-id');
            let url = that.futureBookingCountUrl.replace(':facility', facilityId);
            if (newValue === 'self_booked') {
                let from = $('#allow_self_booking_from_frm').val();
                let to = $('#allow_self_booking_to_frm').val();
                $.ajax({
                    url: url,
                    method: "GET",
                    data: { from: from, to: to },
                    success: function (response) {
                        if (response.future_booking_count > 0) {
                            pendingValue = newValue;
                            $('#booking-type-confirm-modal').dialog('open');
                        } else {
                            previousValue = newValue;
                        }
                    },
                    error: function () {
                        $select.val(previousValue).trigger('chosen:updated');
                    }
                });

            } else {
                previousValue = newValue;
            }
        });

        $('#booking-type-confirm-btn').on('click', function () {

            let decision = $('input[name="booking_type_confirm"]:checked').val();
            if (!decision) {
                $('#booking-type-error-model').show();
                return;
            }
            $('#booking-type-error-model').hide();
            previousValue = pendingValue;
            if (decision === 'Confirmed') {
                $('#booking_status_declined_or_confirm').val('confirmed');
            }

            if (decision === 'Declined') {
                $('#booking_status_declined_or_confirm').val('declined')
            }

            pendingValue = null;
            $('#booking-type-confirm-modal').dialog('close');
        });
    },
    /*
    * Change booking type
    */
    initChangeBookingType: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#facility-change-booking-type-modal').dialog({
            title: 'Change Booking Type',
            autoOpen: false,
            modal: true,
            width: 500,
            height: "auto"
        });
        $('#change_booking_type_open_frm').on('click', function () {
            $('#facility-change-booking-type-modal').dialog("open");
        });
        $("#change_booking_type_start_date_frm").datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: 0,
            maxDate: '+5y',
        });
        $("#change_booking_type_end_date_frm").datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: 0,
            maxDate: '+5y',
        });
        $('#facility-change-booking-type-save-btn').on('click', function () {
            if (that.validateChangeBookingType()) {
                $('#facility-change-booking-type-modal').dialog("close");
                that.populateChangeBookingTypeString();
            }
        });
    },
    /**
     * Mark as unavailable form
     */
    initMarkAsUnavailable: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#mark_as_unavailable_start_date_frm").datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: 0,
            maxDate: '+5y',
        });
        $("#mark_as_unavailable_end_date_frm").datepicker({
            firstDay: 6,
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            minDate: 0,
            maxDate: '+5y',
        });

        // When modal opens, disable time inputs that have no date range selected
        $('#facility-mark-as-unavailable-modal').on('dialogopen', function () {
            that.updateMarkAsUnavailableTimeFields();
        });

        // When dates change, enable/disable time fields
        $("#mark_as_unavailable_start_date_frm, #mark_as_unavailable_end_date_frm").on('change', function () {
            that.updateMarkAsUnavailableTimeFields();
        });

        $('.facility-mark-as-unavailable-active-chk').on('change', function () {
            const startDate = $('#mark_as_unavailable_start_date_frm').val();
            const endDate = $('#mark_as_unavailable_end_date_frm').val();
            const hasDateRange = !!(startDate && endDate);
            const $chk = $(this);
            if (!hasDateRange) {
                if (!$('#facility-mark-as-unavailable-form').next('.validation-error-text').length) {
                    $('#facility-mark-as-unavailable-form').after(
                        '<span class="validation-error-text" id="show_warning_message_for_date_selection">Please select start date and end date to enable time fields.<br/></span>'
                    );
                }
                $chk.prop('checked', false);
                return;
            }
            $('#show_warning_message_for_date_selection').remove();
            that.updateMarkAsUnavailableTimeFields();
        });

        // Save button
        $('#facility-mark-as-unavailable-save-btn').on('click', function () {
            if (that.validateMarkUnavailability()) {
                const days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                days.forEach(function (day) {
                    const $from = $('#facility_mark_as_unavailable_' + day + '_from_frm');
                    const $to = $('#facility_mark_as_unavailable_' + day + '_to_frm');
                    if ($from.prop('disabled')) $from.prop('disabled', false);
                    if ($to.prop('disabled')) $to.prop('disabled', false);
                });
                that.markAsUnavailableSaved = true;
                that.populateFacilityUnAvailabilityString();
                $('#facility-mark-as-unavailable-modal').dialog("close");
            }
        });
    },

    updateMarkAsUnavailableTimeFields: function () {

        const days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        const startDate = $('#mark_as_unavailable_start_date_frm').val();
        const endDate = $('#mark_as_unavailable_end_date_frm').val();
        const hasDateRange = !!(startDate && endDate);
        const weekOrder = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        const that = this; // NOSONAR javascript:S7740


        $('#facility-mark-as-unavailable-form tbody tr').each(function (i) {
            const day = days[i];
            const idx = weekOrder.indexOf(day);
            const $from = $('#facility_mark_as_unavailable_' + day + '_from_frm');
            const $to = $('#facility_mark_as_unavailable_' + day + '_to_frm');
            const $active = $('#facility_mark_as_unavailable_' + day + '_active_frm');
            const globallyDisabled = that.unavailableWeekdays && that.unavailableWeekdays.indexOf(idx) !== -1;
            const activeChecked = $active.length ? $active.is(':checked') : true;
            const shouldDisable = !hasDateRange || globallyDisabled || !activeChecked;
            // Disable and prevent clicking
            $active.prop({ 'disabled': globallyDisabled }).css('pointer-events', globallyDisabled ? 'none' : 'auto').css('opacity', globallyDisabled ? '0.6' : '1');

            if (Array.isArray(that.unavailableWeekdays) && that.unavailableWeekdays?.includes(idx)) {
                // $('#facility_mark_as_unavailable_' + day + '_active_frm').prop('checked', true);
            }

            $from.prop('disabled', shouldDisable).css('pointer-events', shouldDisable ? 'none' : 'auto').css('opacity', shouldDisable ? '0.6' : '1');

            $to.prop('disabled', shouldDisable).css('pointer-events', shouldDisable ? 'none' : 'auto').css('opacity', shouldDisable ? '0.6' : '1');

            // mark the day cell (day name is second cell now)
            const $dayTd = $(this).find('td').eq(1);
            $dayTd.toggleClass('facility-unavailable', shouldDisable).attr('title', shouldDisable ? 'Unavailable' : null);

            try { $from.trigger('chosen:updated'); $to.trigger('chosen:updated'); } catch (e) { }
        });
    },

    captureDisabledMarkAsUnavailableFields: function () {
        const days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        const data = {};
        days.forEach(function (day) {
            const $from = $('#facility_mark_as_unavailable_' + day + '_from_frm');
            const $to = $('#facility_mark_as_unavailable_' + day + '_to_frm');
            if ($from.prop('disabled')) data['facility_mark_as_unavailable_' + day + '_from_frm'] = $from.val();
            if ($to.prop('disabled')) data['facility_mark_as_unavailable_' + day + '_to_frm'] = $to.val();
        });
        return data;
    },

    /*
    * Populates facility Unavailability string
    */
    populateFacilityUnAvailabilityString: function () {
        $('#facility-unavailability-detail-string-span').remove();
        const days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        const weekOrder = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        const $table = $('<table style="width:100%;border-collapse:collapse"></table>');
        const $dateRow = $('<tr></tr>').append(
            $('<td colspan="4" style="font-weight:600"></td>').text('Start Date : ' + $('#mark_as_unavailable_start_date_frm').val()),
            $('<td colspan="4" style="font-weight:600"></td>').text('End Date : ' + $('#mark_as_unavailable_end_date_frm').val())
        );

        const $header = $('<tr></tr>').append($('<td style="font-weight:600">Slot/Day</td>'));
        const starts = []; const ends = [];

        days.forEach(function (day) {
            $header.append($('<td style="font-weight:600"></td>').text(day.slice(0, 3)));
            const keyDay = day.toLowerCase();
            const $from = $('#facility_mark_as_unavailable_' + keyDay + '_from_frm');
            const $to = $('#facility_mark_as_unavailable_' + keyDay + '_to_frm');
            const $chk = $('#facility_mark_as_unavailable_' + keyDay + '_active_frm');
            const chackDayDisable = $('#facility_availability_' + keyDay + '_unavailability_frm').prop('checked');
            const dayIndex = weekOrder.indexOf(keyDay);

            let isUnavailable = false;

            if ($chk.length) {
                isUnavailable = !$chk.prop('checked');
            }

            else if (this.unavailableWeekdays && this.unavailableWeekdays.indexOf(dayIndex) !== -1) {
                isUnavailable = true;
            }
            if (isUnavailable) {
                if ($chk.prop('disabled') || chackDayDisable) {
                    starts.push('UA');
                    ends.push('UA');
                } else {
                    starts.push('A');
                    ends.push('A');
                }
            } else {
                starts.push($from.val() || '');
                ends.push($to.val() || '');
            }
        }, this);


        const $startRow = $('<tr></tr>').append($('<td style="font-weight:600">Start</td>'));
        starts.forEach(s => $startRow.append($('<td></td>').text(s)));
        const $endRow = $('<tr></tr>').append($('<td style="font-weight:600">End</td>'));
        ends.forEach(s => $endRow.append($('<td></td>').text(s)));

        $table.append($dateRow, $header, $startRow, $endRow);
        $('#mark_as_unavailable_open_frm').before($('<span id="facility-unavailability-detail-string-span" style="font-size:8px"></span>').append($('#mark_as_unavailable_start_date_frm').val() == '' ? 'No Settings<br>' : $table));

        // mark UA cells visually
        $('#facility-unavailability-detail-string-span table tr').each(function (r) {
            if (r === 2 || r === 3) $(this).find('td').each(function (c) { if (c === 0) return; if ($(this).text().trim() === 'UA') $(this).addClass('facility-unavailable').attr('title', 'Unavailable'); });
        });
    },
    validateMarkUnavailability: function () {
        let that = this; // NOSONAR javascript:S7740
        let days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        let valid = true;
        let error_msg = "";
        $('#facility-mark-as-unavailable-form').find('.validation-error-text').remove();
        let unavailableStartDateString = $('#mark_as_unavailable_start_date_frm').val();
        let unavailableEndDateString = $('#mark_as_unavailable_end_date_frm').val();

        if (unavailableStartDateString == '' && unavailableEndDateString == '') { // No start and end date
            return true;
        }

        let unavl_start_date = moment(unavailableStartDateString, "DD/MM/YYYY");
        let unavl_end_date = moment(unavailableEndDateString, "DD/MM/YYYY");

        if (unavailableStartDateString == '' || unavailableEndDateString == '') {
            valid = false;
            error_msg = error_msg + " Start/End Date Required .<br/>";
        }

        if (unavl_start_date.isAfter(unavl_end_date)) {
            valid = false;
            error_msg = error_msg + " Invalid Start/End Date Selection .<br/>";
        }

        // Validate only checked days
        days.forEach(function (day) {
            const $checkbox = $('#facility_mark_as_unavailable_' + day + '_active_frm');

            // Skip validation if not checked
            if (!$checkbox.is(':checked')) {
                return;
            }

            let from_unavl_time = moment($('#facility_mark_as_unavailable_' + day + '_from_frm').val(), "HH:mm");
            let to_unavl_time = moment($('#facility_mark_as_unavailable_' + day + '_to_frm').val(), "HH:mm");
            let from_avl_time = moment($('#facility_availability_' + day + '_from_frm').val(), "HH:mm");
            let to_avl_time = moment($('#facility_availability_' + day + '_to_frm').val(), "HH:mm");

            if (from_unavl_time.isAfter(to_unavl_time) && to_unavl_time.format("HH:mm") != '00:00') {
                valid = false;
                error_msg = error_msg + " End Time of Facility Unavailability cannot be earlier than the Start Time on " + that.capitalizeFirstLetter(day) + ". <br/>";
            }

            // Validate against Facility Availability - must be within availability window
            let fromUn = from_unavl_time.clone();
            let toUn = to_unavl_time.clone();
            let fromAv = from_avl_time.clone();
            let toAv = to_avl_time.clone();

            // Treat 00:00 as 24:00 (end of day)
            if (toUn.format('HH:mm') === '00:00') {
                toUn.add(1, 'day');
            }
            if (toAv.format('HH:mm') === '00:00') {
                toAv.add(1, 'day');
            }

            if (fromUn.isBefore(fromAv) || toUn.isAfter(toAv)) {
                valid = false;
                error_msg +=
                    " Unavailability times on " +
                    that.capitalizeFirstLetter(day) +
                    " must be within Facility Availability hours (" +
                    from_avl_time.format('HH:mm') +
                    " - " +
                    to_avl_time.format('HH:mm') +
                    "). <br/>";
            }

        });

        if (!valid) {
            $('#facility-mark-as-unavailable-form').append($('<span class="validation-error-text"></span>').html(error_msg));
        }
        return valid;
    },
    /**
     * Change booking type validation
     */
    validateChangeBookingType: function () {
        let that = this; // NOSONAR javascript:S7740
        let days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        let valid = true;
        let error_msg = "";
        $('#facility-change-booking-type-modal').find('.validation-error-text').remove();
        let changeBookingTypeStartDateString = $('#change_booking_type_start_date_frm').val();
        let changeBookingEndStartDateString = $('#change_booking_type_end_date_frm').val();

        if (changeBookingTypeStartDateString == '' && changeBookingEndStartDateString == '') { // No start and end date
            return true;
        }

        let startDateM = moment(changeBookingTypeStartDateString, "DD/MM/YYYY");

        let endDateM = moment(changeBookingEndStartDateString, "DD/MM/YYYY");

        if (changeBookingTypeStartDateString == '' || changeBookingEndStartDateString == '') {
            valid = false;
            error_msg = error_msg + " Start/End Date Required .<br/>";
        }

        if (startDateM > endDateM) {
            valid = false;
            error_msg = error_msg + " Invalid Start/End Date Selection .<br/>";
        }

        days.forEach(function (day) {
            let from_unavl_time = moment($('#facility_change_booking_type_' + day + '_from_frm').val(), "HH:mm");
            let to_unavl_time = moment($('#facility_change_booking_type_' + day + '_to_frm').val(), "HH:mm");

            if (from_unavl_time > to_unavl_time) {
                valid = false;
                error_msg = error_msg + " End Time of Change Booking Type cannot be earlier than the Start Time on " + that.capitalizeFirstLetter(day) + ". <br/>";
            }
        });

        if (!valid) {
            $('#facility-change-booking-type-form').append($('<span class="validation-error-text"></span>').html(error_msg));
        }
        return valid;
    },
    /*
    * Init facility type
    */
    initFacilityType: function () {
        var that = this; // NOSONAR javascript:S7740
        that.isSavingFacilityType = false;
        $('#facility-type-modal').dialog({
            title: 'Facility Type',
            autoOpen: false,
            modal: true,
            width: 500,
            height: "auto",
            close: function () {
                if (!that.isSavingFacilityType) {
                    that.restoreFacilityTypeValues();
                }
                that.isSavingFacilityType = false;
            }
        });

        $('#facility_type_open_frm').on('click', function () {
            that.storeFacilityTypeValues();
            $('#facility-type-modal').dialog("open");
            that.toggleFacilitySubType(false);
        });

        $('#facility-type-save-btn').on('click', function () {
            if (!that.validateFacilityType()) {
                return false;
            }
            that.isSavingFacilityType = true;
            that.facilityTypeUpdateString();
            $('#facility-type-modal').dialog("close");
        });

        $('input[name="facility_type_frm"]').on('change', function () {
            $('input[name="facility_sub_type_frm[]"]').prop('checked', false);
            $('input[name="facility_sub_type_primary_frm"]').prop('checked', false).prop('disabled', true);

            //Uncheck previous tabs
            that.toggleFacilitySubType(true);
        });

        $('input[name="facility_sub_type_frm[]"]').on('change', function () {
            let selectedFacilityType = $('[name="facility_type_frm"]:checked').val();
            $('#facility_sub_type_primary_frm_' + $(this).val()).prop('disabled', $(this).is(":checked") ? false : true);
            if ($(this).is(":disabled")) {
                $('#facility_sub_type_primary_frm_' + $(this).val()).prop('checked', false);
            }
            $('#facility-sub-type-set-' + selectedFacilityType + ' input[name="facility_sub_type_frm[]"]:checked').each(function (index, element) {
                if ($('#facility-sub-type-set-' + selectedFacilityType + ' input[name="facility_sub_type_frm[]"]:checked').length == 1) {
                    $('#facility_sub_type_primary_frm_' + $(element).val()).prop("checked", true);
                }
            });
        });
    },
    /**
     * Validate facility Type
     */
    validateFacilityType: function () {
        let valid = true;
        let validationMessage = [];
        $('#facility-type-detail-container').find('.validation-error-text').remove();
        $('#facility-type-modal').find('.validation-error-text').remove();
        let selectedFacilityType = $('[name="facility_type_frm"]:checked').val();
        if (!$('[name="facility_type_frm"]').is(':checked')) {
            valid = false;
            validationMessage.push('Facility Type');
        }
        if (!$('#facility-sub-type-set-' + selectedFacilityType + ' [name="facility_sub_type_frm[]"]').is(':checked')) {
            valid = false;
            validationMessage.push('Facility Sub Type');
        }
        if (!$('#facility-sub-type-primary-set-' + selectedFacilityType + ' [name="facility_sub_type_primary_frm"]').filter(":enabled").is(':checked')) {
            valid = false;
            validationMessage.push('Primary Sub Type');
        }
        if (!valid) {
            $('#facility-type-detail-container').append($('<div class="validation-error-text"></div>').text(
                validationMessage.join(', ') + ' is required.'
            ));
            $('#facility-type-form').after($('<div class="validation-error-text"></div>').text(
                validationMessage.join(', ') + ' is required.'
            ));
        }
        return valid;
    },
    /*
    * Update the facility type string
    */
    facilityTypeUpdateString: function () {
        $('#facility-type-detail-string').remove();
        if ($('input[name="facility_type_frm"]:checked').length == 0) {
            return false;
        }
        let $div = $('<div id="facility-type-detail-string" class="facility-type-detail-scroll" style="font-size: 9px;"></div>');
        $div.append('<b>Facility Type : </b>');
        $div.append($('<span></span>').text($('input[name="facility_type_frm"]:checked').next('label').text()));
        $div.append('<br><b>Facility Sub Types : </b>');
        let first = 1;
        $('input[name="facility_sub_type_frm[]"]:checked').each(function (index, element) {
            let primary = false;
            if ($('input[name="facility_sub_type_primary_frm"]:checked').val() == $(element).val()) {
                primary = true;
            }
            let $span = $('<p style="margin: 0px 0px 3px 0px !important;"></p>').text($(element).next('label').text());
            if (primary) {
                $span.append('&nbsp;<span class="custom-badge">Primary</span>');
            }
            $div.append($span);
            first = 0;
        });

        $('#facility_type_open_frm').before($div);
    },

    //Store facility type values before opening modal
    storeFacilityTypeValues: function () {
        let that = this; // NOSONAR javascript:S7740
        that.originalFacilityType = {
            facilityType: $('input[name="facility_type_frm"]:checked').val(),
            facilitySubTypes: [],
            primarySubType: $('input[name="facility_sub_type_primary_frm"]:checked').val()
        };

        $('input[name="facility_sub_type_frm[]"]:checked').each(function () {
            that.originalFacilityType.facilitySubTypes.push($(this).val());
        });
    },

    restoreFacilityTypeValues: function () {
        let that = this; // NOSONAR javascript:S7740

        if (!that.originalFacilityType) {
            return;
        }

        $('#facility-type-form')[0].reset();
        $('.facility-sub-type-set').hide();
        $('.facility-sub-type-primary-set').hide();

        $('input[name="facility_sub_type_frm[]"]').prop('checked', false);
        $('input[name="facility_sub_type_primary_frm"]').prop('checked', false).prop('disabled', true);

        if (that.originalFacilityType.facilityType) {
            $('input[name="facility_type_frm"][value="' + that.originalFacilityType.facilityType + '"]').prop('checked', true);
        }

        that.toggleFacilitySubType(false);

        that.originalFacilityType.facilitySubTypes.forEach(function (subTypeId) {
            $('input[name="facility_sub_type_frm[]"][value="' + subTypeId + '"]').prop('checked', true);
            $('#facility_sub_type_primary_frm_' + subTypeId).prop('disabled', false);
        });

        if (that.originalFacilityType.primarySubType) {
            $('input[name="facility_sub_type_primary_frm"][value="' + that.originalFacilityType.primarySubType + '"]').prop('checked', true);
        }
    },

    //Store facility availability values before opening modal
    storeFacilityAvailabilityValues: function () {
        let that = this; // NOSONAR javascript:S7740
        that.facilityAvailabilitySaved = false;
        that.originalFacilityAvailability = {};

        let days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        days.forEach(function (day) {
            that.originalFacilityAvailability[day] = {
                from: $('#facility_availability_' + day + '_from_frm').val(),
                to: $('#facility_availability_' + day + '_to_frm').val(),
                unavailable: $('#facility_availability_' + day + '_unavailability_frm').is(':checked')
            };
        });
    },

    //Restore facility availability values when modal is closed without saving
    restoreFacilityAvailabilityValues: function () {
        let that = this; // NOSONAR javascript:S7740

        if (that.facilityAvailabilitySaved || !that.originalFacilityAvailability) {
            return;
        }

        let days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        days.forEach(function (day) {
            let original = that.originalFacilityAvailability[day];
            if (original) {
                $('#facility_availability_' + day + '_from_frm').val(original.from).trigger('chosen:updated');
                $('#facility_availability_' + day + '_to_frm').val(original.to).trigger('chosen:updated');
                $('#facility_availability_' + day + '_unavailability_frm').prop('checked', original.unavailable);
            }
        });
    },

    //Store mark as unavailable values before opening modal
    storeMarkAsUnavailableValues: function () {
        let that = this; // NOSONAR javascript:S7740
        that.markAsUnavailableSaved = false;
        that.originalMarkAsUnavailable = {
            startDate: $('#mark_as_unavailable_start_date_frm').val(),
            endDate: $('#mark_as_unavailable_end_date_frm').val(),
            days: {}
        };

        let days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        days.forEach(function (day) {
            that.originalMarkAsUnavailable.days[day] = {
                from: $('#facility_mark_as_unavailable_' + day + '_from_frm').val(),
                to: $('#facility_mark_as_unavailable_' + day + '_to_frm').val(),
                active: $('#facility_mark_as_unavailable_' + day + '_active_frm').is(':checked')
            };
        });
    },

    //Restore mark as unavailable values when modal is closed without saving
    restoreMarkAsUnavailableValues: function () {
        let that = this; // NOSONAR javascript:S7740

        if (that.markAsUnavailableSaved || !that.originalMarkAsUnavailable) {
            return;
        }

        $('#mark_as_unavailable_start_date_frm').val(that.originalMarkAsUnavailable.startDate);
        $('#mark_as_unavailable_end_date_frm').val(that.originalMarkAsUnavailable.endDate);

        let days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        days.forEach(function (day) {
            let original = that.originalMarkAsUnavailable.days[day];
            if (original) {
                $('#facility_mark_as_unavailable_' + day + '_from_frm').val(original.from).trigger('chosen:updated');
                $('#facility_mark_as_unavailable_' + day + '_to_frm').val(original.to).trigger('chosen:updated');
                $('#facility_mark_as_unavailable_' + day + '_active_frm').prop('checked', original.active);
            }
        });
        that.updateMarkAsUnavailableTimeFields();
    },
    /*
    * Facility Availability
    */
    initFacilityAvailability: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#facility-availability-modal').dialog({
            title: 'Facility Availability',
            autoOpen: false,
            modal: true,
            width: 500,
            height: "auto",
            close: function () {
                that.restoreFacilityAvailabilityValues();
            }
        });
        $('#facility_availability_open_frm').on('click', function () {
            that.storeFacilityAvailabilityValues();
            $('#facility-availability-modal').dialog("open");
        });
        $('#facility-availability-save-btn').on('click', function () {
            if (that.validateFacilityAvailability()) {
                const allowed = that.ensureSelectedDatesAllowed();
                if (!allowed) {
                    return;
                }
                that.facilityAvailabilitySaved = true;
                that.populateFacilityAvailabilityString();
                $('#facility-availability-modal').dialog("close");
            }
        });

        $('.facility-avaliablility-chosen').each(function (index, element) {
            $(element).chosen({ width: '100%' });
        });
    },
    /**
     * Validate facility availablity
     */
    validateFacilityAvailability: function () {
        let that = this; // NOSONAR javascript:S7740
        let days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        let valid = true;
        let error_msg = "";
        $('#facility-availability-form').find('.validation-error-text').remove();

        days.forEach(function (day) {
            let from_avl_time = moment($('#facility_availability_' + day + '_from_frm').val(), "HH:mm");
            let to_avl_time = moment($('#facility_availability_' + day + '_to_frm').val(), "HH:mm");

            if (from_avl_time.isAfter(to_avl_time) && to_avl_time.format('HH:mm') != '00:00') {
                valid = false;
                error_msg = error_msg + " End Time of Facility Availability cannot be earlier than the Start Time on " + that.capitalizeFirstLetter(day) + ". <br/>";
            }
        });
        if (!valid) {
            $('#facility-availability-form').append($('<span class="validation-error-text"></span>').html(error_msg));
        }
        return valid;
    },
    /*
    * Capitalize first letter
    */
    capitalizeFirstLetter: function (str) {
        if (str.length === 0) {
            return ""; // Handle empty strings
        }
        return str.charAt(0).toUpperCase() + str.slice(1);
    },
    /*
    * Populates facility Availability string
    */
    populateFacilityAvailabilityString: function () {
        $('#facility-availability-detail-string-span').remove();
        let days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        let availabilityDetail = [];

        let $tablefacilityavl = $('<table style="width: 100%; border-collapse: collapse;"></table>'), $row = $('<tr></tr>'), $row1 = $('<tr></tr>'), $row2 = $('<tr></tr>'), detailString_from = [], detailString_to = [], $detailTdfromlist, $detailTdtolist, $detailTd_col, $detailTdfrom_col;

        let $detailTd = $('<td style="font-weight: 600;"></td>').append("Slot/Day");
        $row.append($detailTd);

        let disabledWeekdays = [];
        let serverAvailability = null;
        if ($('#facility-availability').length) {
            try {
                serverAvailability = JSON.parse($('#facility-availability').val());
            } catch (e) {
                serverAvailability = null;
            }
        }
        days.forEach(function (day) {
            let detailString = day.slice(0, 3);
            day = day.toLowerCase();
            $detailTd = $('<td style="font-weight: 600;"></td>').append(detailString);
            $row.append($detailTd);

            // If availability provided, use it to determine UA days
            const weekOrder = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            if (serverAvailability) {
                const key = 'FCA_FacilityTimeAvailability_' + that.capitalizeFirstLetter(day);
                if (serverAvailability[key] === 0 || serverAvailability[key] === '0') {
                    detailString_from.push('UA');
                    detailString_to.push('UA');
                    const dayIndex = weekOrder.indexOf(day);
                    if (dayIndex !== -1) disabledWeekdays.push(dayIndex);
                } else {
                    detailString_from.push($('#facility_availability_' + day + '_from_frm').val());
                    detailString_to.push($('#facility_availability_' + day + '_to_frm').val());
                }
            } else if ($('#facility_availability_' + day + '_unavailability_frm').is(':checked')) {
                detailString_from.push('UA');
                detailString_to.push('UA');
                const dayIndex = weekOrder.indexOf(day);
                if (dayIndex !== -1) {
                    disabledWeekdays.push(dayIndex);
                }
            } else {
                detailString_from.push($('#facility_availability_' + day + '_from_frm').val());
                detailString_to.push($('#facility_availability_' + day + '_to_frm').val());
            }

            detailString = detailString + ($('#facility_availability_' + day + '_unavailability_frm').is(':checked')
                ? ' : Unavailable'
                : (' : ' + $('#facility_availability_' + day + '_from_frm').val() + ' - ' + $('#facility_availability_' + day + '_to_frm').val()));
        });

        let $detailTdfrom = $('<td style="font-weight: 600;"></td>').append("Start");
        $row1.append($detailTdfrom);

        detailString_from.forEach(function (from_avl) {
            $detailTdfrom = $('<td></td>').append(from_avl);
            $row1.append($detailTdfrom);
        });

        let $detailTdto = $('<td style="font-weight: 600;"></td>').append("End");
        $row2.append($detailTdto);

        detailString_to.forEach(function (to_avl) {
            $detailTdto = $('<td></td>').append(to_avl);
            $row2.append($detailTdto);
        });

        $tablefacilityavl.append($row, $row1, $row2);

        $('#facility_availability_open_frm').before($('<span id="facility-availability-detail-string-span" style="font-size: 8px;"></span>').append($tablefacilityavl));

        // mark UA cells visually in the generated table
        $('#facility-availability-detail-string-span table tr').each(function (rowIndex, rowElem) {
            if (rowIndex === 1 || rowIndex === 2) {
                $(rowElem).find('td').each(function (colIndex, td) {
                    if (colIndex === 0) return;
                    if ($(td).text().trim() === 'UA') {
                        $(td).addClass('facility-unavailable').attr('title', 'Unavailable');
                    }
                });
            }
        });
        disabledWeekdays = Array.from(new Set(disabledWeekdays)).sort(function (a, b) { return a - b });

        this.unavailableWeekdays = disabledWeekdays;
        this.updateDatepickerUnavailableDays(disabledWeekdays);
        try {
            if ($('#facility-mark-as-unavailable-modal').is(':visible')) {
                this.updateMarkAsUnavailableTimeFields();
            }
        } catch (e) { }
    },

    updateDatepickerUnavailableDays: function (disabledDays) {
        $.datepicker.setDefaults({
            beforeShowDay: function (date) {
                if (!disabledDays || disabledDays.length === 0) return [true, '', ''];
                const dow = date.getDay();
                if (disabledDays.indexOf(dow) !== -1) {
                    return [false, 'ui-state-disabled facility-unavailable-date', 'Unavailable'];
                }
                return [true, '', ''];
            }
        });

        const pickers = ['#active_from_frm', '#change_booking_type_start_date_frm', '#change_booking_type_end_date_frm', '#mark_as_unavailable_start_date_frm', '#mark_as_unavailable_end_date_frm'];
        pickers.forEach(function (sel) {
            if ($(sel).hasClass('hasDatepicker')) {
                try { $(sel).datepicker('refresh'); } catch (e) { }
            }
        });
    },

    ensureSelectedDatesAllowed: function () {
        let that = this; // NOSONAR javascript:S7740
        const pickers = ['#active_from_frm', '#change_booking_type_start_date_frm', '#change_booking_type_end_date_frm', '#mark_as_unavailable_start_date_frm', '#mark_as_unavailable_end_date_frm'];
        let blocked = [];
        pickers.forEach(function (sel) {
            if ($(sel).length && $(sel).val()) {
                try {
                    const parts = $(sel).val().split('/');
                    if (parts.length === 3) {
                        const d = new Date(parseInt(parts[2], 10), parseInt(parts[1], 10) - 1, parseInt(parts[0], 10));

                        const bs = $.datepicker._defaults.beforeShowDay || $.datepicker._get(inst => { });
                        let allowed = true;
                        if ($.datepicker._defaults && typeof $.datepicker._defaults.beforeShowDay === 'function') {
                            allowed = $.datepicker._defaults.beforeShowDay(d)[0];
                        }
                        if (!allowed) {
                            blocked.push({ selector: sel, value: $(sel).val() });
                        }
                    }
                } catch (e) { }
            }
        });

        $('.facility-availability-validation-error').remove();

        return true;
    },
    /*
    * Facility Link
    */
    initFacilityLink: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#area_owner_frm').on('change', function () {
            //Based on area enable disable linked facility
            that.populateLinkedFacilityDropDown();
        });
        that.populateLinkedFacilityDropDown();

        $('#linked_facility_open_frm').on('click', function () {
            $('#facility-link-modal').dialog("open");
        });
        $('#facility-link-save-btn').on('click', function () {
            $('#facility-link-modal').dialog("close");
            that.populateFacilityLinkString();
        });
        $('#facility_link_chose').on('change', function () {
            $('#facility-link-details-table-body').empty();
            $("#facility_link_chose option:selected").each(function (index, element) {
                let $row = $('<tr></tr>');
                let $detailTd = $('<td></td>').append(
                    $('<span></span>').text($(element).text())
                );
                let $mandatory = $('<td style="text-align:center"></td>').append(
                    $('<input type="checkbox" name="facility_link_mandatory[]" />').attr('value', $(element).val())
                );
                $row.append($detailTd, $mandatory);
                $('#facility-link-details-table-body').append($row);
            });
        });
    },
    /**
     * Populate linked facility drop down based on area
     */
    populateLinkedFacilityDropDown: function () {
        let selectedAreaOwner = $('#area_owner_frm').val();
        $("#facility_link_chose option").each(function (index, element) {
            let isLinkedFacility = $(this).attr('data-facility-linked') == 1;
            let optionFacilityArea = $(this).attr('data-facility-area');
            if (!isLinkedFacility && optionFacilityArea != selectedAreaOwner) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
        $('#facility_link_chose').trigger('chosen:updated');
    },
    /*
    * Populate the facility link string
    */
    populateFacilityLinkString: function () {
        $('#linked-facility-view-string').remove();

        let mandatory = $("input[name='facility_link_mandatory[]']:checked").map(function () {
            return $(this).val();
        }).get();

        let mandatoryFacilities = [];
        let nonMandatoryFacilities = [];

        $('#facility_link_chose option:selected').each(function (index, element) {
            let facilityName = $(element).text();
            if (mandatory.includes($(element).val())) {
                mandatoryFacilities.push(facilityName);
            } else {
                nonMandatoryFacilities.push(facilityName);
            }
        });

        // Create table structure
        let $table = $('<table id="linked-facility-view-string" class="linked-facility-view-scroll" style="margin: unset; font-size:9px; border-collapse: collapse; width: 100%;"></table>');
        let $thead = $('<thead></thead>');
        let $headerRow = $('<tr style="border-bottom: 1px solid #ccc;"></tr>');
        $headerRow.append($('<th style="text-align: left; padding: 5px; font-weight: bold;">Type</th>'));
        $headerRow.append($('<th style="text-align: left; padding: 5px;"></th>'));
        $thead.append($headerRow);
        $table.append($thead);

        let $tbody = $('<tbody></tbody>');

        // Mandatory row
        if (mandatoryFacilities.length) {
            let $mandatoryRow = $('<tr style="border-bottom: 1px solid #eee;"></tr>');
            $mandatoryRow.append($('<td style="padding: 5px; font-weight: bold;">Mandatory</td>'));
            $mandatoryRow.append($('<td style="padding: 5px;">' + mandatoryFacilities.map(f => f.trim()).join(', ') + '</td>'));
            $tbody.append($mandatoryRow);
        }

        // Non-Mandatory row
        if (nonMandatoryFacilities.length) {
            let $nonMandatoryRow = $('<tr style="border-bottom: 1px solid #eee;"></tr>');
            $nonMandatoryRow.append($('<td style="padding: 5px; font-weight: bold;">Non-Mandatory</td>'));
            $nonMandatoryRow.append($('<td style="padding: 5px;">' + nonMandatoryFacilities.map(f => f.trim()).join(', ') + '</td>'));
            $tbody.append($nonMandatoryRow);
        }

        $table.append($tbody);

        $('#linked_facility_open_frm').before($table);
    },
    /*
    * Technical setup
    */
    initTechnicalSetup: function () {
        let that = this; // NOSONAR javascript:S7740
        that.technicalSetupSaved = false;
        $('#service_list_select').multiselect();
        $('#equipment_list_select').multiselect();
        $('#facility-technical-setup-selection-next-page-quantity').on('click', function () {
            $('#service-list-validation-error').hide();
            $('#equipment-list-validation-error').hide();
            let $selectedServices = $('#service_list_select_to option');
            let $equipmentServices = $('#equipment_list_select_to option');
            let validation = true;
            if ($selectedServices.length == 0) {
                $('#service-list-validation-error').show();
                validation = false;
            }
            if ($equipmentServices.length == 0) {
                $('#equipment-list-validation-error').show();
                validation = false;
            }
            if (!validation) {
                return false;
            }
            $('#facility-technical-setup-selections').hide();
            $('#facility-technical-setup-quantity').show();

            //Remove previous quantity selection
            $('.empty-row-tech-quantity').remove();
            $('.technical-quantity').each((index, element) => {
                let found = false;
                $equipmentServices.each(function (index, element2) {
                    if ($(element).attr('data-id') == $(element2).val()) {
                        found = true;
                    }
                });
                if (!found) {
                    $('#technical-quantity-row-' + $(element).attr('data-id')).remove();
                }
            });

            $equipmentServices.each(function (index, element) {
                let datatype = element.getAttribute("data-type");
                let $tr = $('#technical-quantity-row-' + $(element).val());
                if ($tr.length > 0) { // already exists
                    if (datatype == "SW") {
                        $tr.css("display", "none")
                    }
                    return;
                }
                $tr = $('<tr class="technical-quantity"></tr>').attr('id', 'technical-quantity-row-' + $(element).val()).attr('data-id', $(element).val())
                if (datatype == "SW") {
                    $tr.css("display", "none")
                }
                $tr.append($('<td></td>').text($(element).text()));
                $tr.append($('<td style="text-align:center"></td>').append(
                    $('<input type="number" style="width: 35px;" min="1" step="1" value="1">').attr({
                        'name': 'equipment_quantity_' + $(element).val(),
                        'onkeydown': $('#facility_capacity_frm').attr('onkeydown'),
                        'oninput': $('#facility_capacity_frm').attr('oninput')
                    }))
                );
                $tr.append($('<td></td>').append($('<input type="text" class="facility-form-input">').attr('name', 'equipment_note_' + $(element).val())));
                $('#table-technical-setup-quantity').append($tr);
            });

            if ($('.technical-quantity:visible').length == 0) {
                $('#table-technical-setup-quantity').append('<tr class="empty-row-tech-quantity"><td colspan="3" style="text-align: center">No Equipment Selected</td></tr>')
            }
        });
        $('#facility-technical-setup-quantity-back-page-selection').on('click', function () {
            $('#facility-technical-setup-selections').show();
            $('#facility-technical-setup-quantity').hide();
        });
        $('#facility-technical-setup-complete').on('click', function () {
            if (that.validateTechnicalSetup()) {
                that.technicalSetupSaved = true;
                that.technicalSetupPopulateString();
                $('#facility-technical-setup-modal').dialog("close");
            }
        });
    },
    /**
     * Validate Technical setup
     */
    validateTechnicalSetup: function () {
        let valid = true;
        $('.technical-quantity').find('.validation-error-text').remove();
        $('.technical-quantity:visible').each(function (index, element) {
            let $quantity = $('[name="equipment_quantity_' + $(element).attr('data-id') + '"]');
            if ($.trim($quantity.val()) == '') { // Validate quantity
                valid = false;
                $quantity.after($('<div class="validation-error-text">Quantity required</div>'));
            }
            if ($quantity.val() > 9999 || $quantity.val() < 1) { //Max quantity validation
                valid = false;
                $quantity.after($('<div class="validation-error-text">Quantity value should be minimum of 1 and maximum of 9999</div>'));
            }
            let $note = $('[name="equipment_note_' + $(element).attr('data-id') + '"]');
            let noteData = $.trim($note.val());
            if (noteData.length > 200) { // Validate note
                valid = false;
                $note.after($('<div class="validation-error-text">Note length should be maximum 200 characters</div>'));
            }
        });
        if (valid) {
            $('#technical-setup-detail-container').find('.validation-error-text').remove();
        }
        return valid;
    },
    /*
    * Technical setup populate string
    */
    technicalSetupPopulateString: function () {
        $('#technical-setup-populate-string').remove();
        let $div = $('<div id="technical-setup-populate-string" style="font-size: 9px;"></div>');
        let selectedServices = [];
        $('#service_list_select_to option').each(function (index, element) {
            selectedServices.push($(element).text())
        });
        let selectedEquipments = [];
        $('#equipment_list_select_to option').each(function (index, element) {
            let quantityName = 'input[name="equipment_quantity_' + $(element).val() + '"]';
            let quantity = $(quantityName).length && $(element).attr('data-type') == 'ET' ? (' (' + $(quantityName).val() + ')') : '';
            selectedEquipments.push($(element).text() + quantity);
        });
        let $div2 = $('<div><b>Services : </b></div>');
        $div.append(
            $div2.append(
                $('<span></span>').text(selectedServices.join(', '))
            )
        );
        let $div3 = $('<div><b>Equipments : </b></div>')
        $div.append(
            $div3.append(
                $('<span></span>').text(selectedEquipments.join(', '))
            )
        );
        $('#technical_setup_open_frm').before($div);
    },

    //Store technical setup values before opening modal
    storeTechnicalSetupValues: function () {
        let that = this; // NOSONAR javascript:S7740
        that.technicalSetupSaved = false;
        that.originalServices = {
            available: [],
            selected: []
        };
        $('#service_list_select option').each(function () {
            that.originalServices.available.push({
                id: $(this).val(),
                text: $(this).text()
            });
        });

        $('#service_list_select_to option').each(function () {
            that.originalServices.selected.push({
                id: $(this).val(),
                text: $(this).text()
            });
        });

        that.originalEquipment = {
            available: [],
            selected: []
        };

        $('#equipment_list_select option').each(function () {
            that.originalEquipment.available.push({
                id: $(this).val(),
                text: $(this).text(),
                type: $(this).data('type')
            });
        });

        $('#equipment_list_select_to option').each(function () {
            that.originalEquipment.selected.push({
                id: $(this).val(),
                text: $(this).text(),
                type: $(this).data('type')
            });
        });

        that.originalQuantityNotes = {};
        $('.technical-quantity').each(function () {
            let equipId = $(this).attr('data-id');
            that.originalQuantityNotes[equipId] = {
                quantity: $('input[name="equipment_quantity_' + equipId + '"]').val(),
                note: $('input[name="equipment_note_' + equipId + '"]').val()
            };
        });
    },

    // Restore technical setup values when modal is closed without saving
    restoreTechnicalSetupValues: function () {
        let that = this; // NOSONAR javascript:S7740
        if (that.technicalSetupSaved || !that.originalServices || !that.originalEquipment) {
            return;
        }
        $('#service_list_select').empty();
        $('#service_list_select_to').empty();

        that.originalServices.available.forEach(function (service) {
            $('#service_list_select').append(
                $('<option>', { value: service.id, text: service.text })
            );
        });

        that.originalServices.selected.forEach(function (service) {
            $('#service_list_select_to').append(
                $('<option>', { value: service.id, text: service.text })
            );
        });

        $('#equipment_list_select').empty();
        $('#equipment_list_select_to').empty();

        that.originalEquipment.available.forEach(function (equipment) {
            let optionTag = $('<option>', {
                value: equipment.id,
                text: equipment.text
            }).attr('data-type', equipment.type);
            $('#equipment_list_select').append(optionTag);
        });

        that.originalEquipment.selected.forEach(function (equipment) {
            let optionTag = $('<option>', {
                value: equipment.id,
                text: equipment.text
            }).attr('data-type', equipment.type);
            $('#equipment_list_select_to').append(optionTag);
        });
        if (that.originalQuantityNotes) {
            Object.keys(that.originalQuantityNotes).forEach(function (equipId) {
                let data = that.originalQuantityNotes[equipId];
                $('input[name="equipment_quantity_' + equipId + '"]').val(data.quantity);
                $('input[name="equipment_note_' + equipId + '"]').val(data.note);
            });
        }
        $('#facility-technical-setup-quantity').hide();
        $('#facility-technical-setup-selections').show();
    },
    /*
    * Toggle the facility sub type based on facility type
    */
    toggleFacilitySubType: function (clearSelections = true) {
        if (clearSelections) {
            $('input[name="facility_sub_type_frm[]"]').prop('checked', false);
            $('input[name="facility_sub_type_primary_frm"]').prop('checked', false).prop('disabled', true);
        }

        //Show facility sub types
        let selectfacilityType = $('input[name="facility_type_frm"]:checked').val();
        $('.facility-sub-type-set').hide();
        $('#facility-sub-type-set-' + selectfacilityType).show();
        $('.facility-sub-type-primary-set').hide();
        $('#facility-sub-type-primary-set-' + selectfacilityType).show();
    },
    /*
    * Reset the facility type popup when click on (X) Close button
    */
    resetFacilityPopup: function () {
        $('#facility-type-form')[0].reset();
        $('.facility-sub-type-set').hide();
        $('.facility-sub-type-primary-set').hide();
    },
    /*
    * Restrict bookers drop down
    */
    updateRestrictBookersList: function () {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.facilityBookersUrl.replace(":area", $('#area_owner_frm').val()),
            method: "GET",
            success: function (data) {
                let teams = data;
                let selected = $('#restrict_bookers_frm').val();
                $('#restrict_bookers_frm').empty();

                // Sort teams alphabetically by name
                let sortedTeams = Object.keys(teams).sort((a, b) => {
                    return teams[a].localeCompare(teams[b]);
                });

                sortedTeams.forEach(key => {
                    $('#restrict_bookers_frm').append(
                        $('<option></option>').attr('value', key).text(teams[key]).prop('selected', selected.includes(key))
                    );
                });
                $('#restrict_bookers_frm').trigger('chosen:updated');
            },
            error: function (xhr, status, error) {
                let data = JSON.parse(xhr.responseText);
                let errors = data.errors;
                Object.keys(errors).forEach((key) => {

                });
            }
        });
    },
    /*
    * Facility form location elements
    */
    locationFormInit: function (param = null) {
        let that = this; // NOSONAR javascript:S7740
        let provderType = $('#provider_type_frm').val();
        let providerName = $('#provider_name_frm').val();
        //Reset fields
        $('#location-internal').hide();
        $('#location-external-uk').hide();
        $('#location-external-international').hide();
        if ($('#facility-form-view-only').val() != 1) { // while view dont enable
            $('#provider_name_frm').prop('disabled', false);
            $('#provider_name_frm').nextAll('.validation-error-text').remove();
        }

        //Update location fields based on provider type
        switch (provderType) {
            case 'internal':
                $('#location-internal').show();
                $('#provider_name_frm').val('BBC');
                $('#provider_name_frm').prop('disabled', true);
                $('#provider_name_frm').nextAll('.validation-error-text').remove();
                $('#provider_type_frm').on('change', function () {
                    that.clearFieldError($('#provider_type_frm'));
                });
                break;
            case 'external_uk':
                if (param != 'edit') {
                    $('#provider_name_frm').val('');
                } else if (param == 'edit') {
                    $('#provider_name_frm').val(providerName);
                }
                $('#location-external-uk').show();
                $('#location_external_uk_open_frm').on('click', function () {
                    $('#location-external-uk-modal').dialog("open");
                });
                $('#facility-location-external-uk-save-btn').on('click', function () {
                    that.saveExternalLocationUk();
                });
                break;
            case 'external_international':
                if (param != 'editExtInt') {
                    $('#provider_name_frm').val('');
                } else if (param == 'editExtInt') {
                    $('#provider_name_frm').val(providerName);
                }
                $('#location-external-international').show();
                $('#location_external_international_open_frm').on('click', function () {
                    $('#location-external-international-modal').dialog("open");
                });
                $('#facility-location-external-international-save-btn').on('click', function () {
                    that.saveExternalLocationInternational();
                });
                break;
        }
    },
    /** 
    * Clear error for all validation
    */
    clearFieldError: function ($field) {
        $field.removeClass('error');
        const $next = $field.next('.validation-error-text');
        if ($next.length) {
            $next.remove();
        }
    },
    /*
    *
    */
    saveExternalLocationUk: function () {
        let building = $('#location_external_uk_building_number').val();
        let street = $('#location_external_uk_street').val();
        let city = $('#location_external_uk_city').val();
        let county = $('#location_external_uk_county').val();
        let postcode = $('#location_external_uk_post_code').val();
        let country = $('#location_external_uk_country').val();
        $("#external-uk-location-form").validate({
            rules: {
                'location_external_uk_building_number': {
                    required: true
                },
                'location_external_uk_street': {
                    required: true
                },
                'location_external_uk_city': {
                    required: true
                },
                'location_external_uk_county': {
                    required: true
                },
                'location_external_uk_city_post_code': {
                    required: true
                },
                'location_external_uk_country': {
                    required: true
                }
            }
        });
        if ($("#external-uk-location-form").valid()) {
            let address = [
                'Building Number/Name - ' + building,
                'Street - ' + street,
                'City - ' + city,
                'County - ' + county,
                'Postcode - ' + postcode,
                'Country - ' + country
            ];
            $('#facility_location_external_uk_span').remove();
            $('#location_external_uk_open_frm').before($('<span style="margin-right: 10px;" id="facility_location_external_uk_span"></span>').text(address.join(', ')));
            $('#location-external-uk-modal').dialog("close");
        }
    },

    /*
    * Validate International address
    */
    saveExternalLocationInternational: function () {
        let addressData = $('#location_external_international_address').val();
        $("#external-international-location-form").validate({
            rules: {
                'location_external_international_address': {
                    required: true
                }
            }
        });
        if ($("#external-international-location-form").valid()) {
            let address = [
                'Address - ' + addressData
            ];
            $('#facility_location_external_international_span').remove();
            $('#location_external_international_open_frm').before($('<span style="margin-right: 10px;" id="facility_location_external_international_span"></span>').text(address.join(', ')));
            $('#location-external-international-modal').dialog("close");
        }
    },
    /*
    * Submit create facility form
    */
    submitForm: function (facilityId = 0, linkedConfirm = 0) {
        var that = this; // NOSONAR javascript:S7740
        let facilityFormData = that.fomatFormData($('#create-facility-form').serializeArray());
        facilityFormData['provider_type_frm'] = $('#provider_type_frm').val();
        facilityFormData['booking_status_declined_or_confirm'] = $('#booking_status_declined_or_confirm').val();
        facilityFormData['provider_name_frm'] = $('#provider_name_frm').val();
        facilityFormData['internal_location_frm'] = $('#internal_location_frm').val();
        facilityFormData['location_notes_frm'] = $('#location_notes_frm').val();
        facilityFormData['allow_booking_frm'] = $('input[name="allow_booking_frm"]:checked').val();
        facilityFormData['on_off_booking_frm'] = $('input[name="on_off_booking_frm"]:checked').val();
        //Technical setup data
        let technicalFormData = {};
        technicalFormData['service_list_select_to'] = $('#service_list_select_to option').map(function () {
            return $(this).val();
        }).get();
        technicalFormData['equipment'] = {};
        $('#equipment_list_select_to option').each(function (index, element) {
            technicalFormData['equipment'][$(element).val()] = {};
            technicalFormData['equipment'][$(element).val()]['equipment_id'] = $(element).val();
            technicalFormData['equipment'][$(element).val()]['quantity'] = $('input[name="equipment_quantity_' + $(element).val() + '"]').val() ?? "";
            technicalFormData['equipment'][$(element).val()]['note'] = $('input[name="equipment_note_' + $(element).val() + '"]').val() ?? "";
            technicalFormData['equipment'][$(element).val()]['type'] = $(element).attr('data-type');
        });
        // Facility link form
        let facilityLinkFormData = that.fomatFormData($('#facility-link-form').serializeArray());
        facilityLinkFormData['facility_link_chose'] = $('#facility_link_chose').val();

        //Build form data
        let formUrl = facilityId != 0 ? that.updateUrl.replace(':facility', facilityId) : that.storeUrl;

        // Check if there are existing validation errors on the page
        let hasExistingErrors = $('.validation-error-text').length > 0;

        // On first attempt, skip validation and proceed to show spinner and get all errors from server
        // Only validate on subsequent attempts (if errors already exist)
        if (hasExistingErrors) {
            let isFormValid = that.validateSubmitForm();
            if (!isFormValid) {
                return; // Don't proceed if validation fails on subsequent attempts
            }
        }

        let formData = {
            '_token': $('#create-facility-form input[name=_token]').val(),
            'facilityFormData': facilityFormData,
            'facilityExternalUkLocationForm': that.fomatFormData($('#external-uk-location-form').serializeArray()),
            'facilityExternalUkInternationalForm': that.fomatFormData($('#external-international-location-form').serializeArray()),
            'facilityAvailability': that.fomatFormData($('#facility-availability-form').serializeArray()),
            'facilityTypeForm': that.fomatFormData($('#facility-type-form').serializeArray()),
            'facilityTechnicalSetupForm': technicalFormData,
            'facilityLinkFacilityForm': facilityLinkFormData,
            'facilityMarkAsUnavailable': that.fomatFormData($('#facility-mark-as-unavailable-form').serializeArray()),
            'facilityFormValidation': 1,
            'facility_booking_linked_decline_reason': linkedConfirm
        };
        if (facilityId != 0) {
            formData['_method'] = 'PUT';
            formData['facilityChangeBookingType'] = that.fomatFormData($('#facility-change-booking-type-form').serializeArray());
            formData['validate_linked_facility'] = 1;
        }
        that.spinnerFaceBox();
        $.ajax({
            url: formUrl,
            method: "POST",
            data: formData,
            success: function (data) {
                location.reload();
            },


            error: function (xhr, status, error) {
                // Close spinner before handling errors
                that.closeSpinnerFaceBox();

                // Debug log – leave in while testing
                // console.debug('Update facility AJAX error', xhr.status, xhr.responseText);

                let resp = {};
                try { resp = JSON.parse(xhr.responseText || '{}'); } catch (e) { }

                const conflict = that.parseUnavailabilityConflict(resp);
                if (conflict.isConflict) {
                    that.handleUnavailabilityConflict(resp, formUrl, formData);
                    return; // IMPORTANT: stop default validation rendering when conflict path runs
                }

                // Default: show validation errors
                $('.validation-error-text').remove();
                that.facilityFormValidationMessages((resp && resp.errors) || {});
                that.validateSubmitForm();
            }


        });
    },
    /**
     * Spinner box
     * 
     */
    spinnerFaceBox: function () {
        $.facebox($('<div style="margin-top: 10px;font-size:12px;text-align:center;"><div><div style="display: flex; justify-content: center;"><div class="loader-spinner"></div></div><span>Submitting Form, Please wait.</span></div></div>'));
        $('#facebox').find('.close').hide();
    },


    closeSpinnerFaceBox: function () {
        try {
            $('#facebox .close').trigger('click');
            $('#facebox, #facebox_overlay').close();
        } catch (e) { }
    },


    /*
    * Form submission validation message
    */
    facilityFormValidationMessages: function (errors) {
        let that = this; // NOSONAR javascript:S7740
        let locationExtUkValidation = true;
        let linkedFacilityValidation = {};
        let locationInternational = true;
        let facilityTypeValidation = [];
        let facilityChangeBookingValidation = [];
        let facilityMarkAsUnavailableValidation = [];
        let technicalSetup = [];
        let technicalSetupNote = [];
        let facilityLink = true;
        let validationCount = 0;
        Object.keys(errors).forEach((key) => {
            validationCount++;
            errors[key].forEach((message) => {
                let formatedKey = key.replace("facilityFormData.", "").replace("facilityExternalUkLocationForm.", "").replace("facilityExternalUkInternationalForm.", "").replace("facilityChangeBookingType.", "").replace("facilityMarkAsUnavailable.", "");
                formatedKey = (formatedKey == 'allow_booking_frm' ? 'allow_booking_no_frm_label' : formatedKey);
                formatedKey = (formatedKey == 'on_off_booking_frm' ? 'on_off_booking_no_frm_label' : formatedKey);
                let eleId = '#' + formatedKey;
                eleId = $(eleId + '_chosen').length > 0 ? eleId + '_chosen' : eleId; //Chosen select drop down
                if (key.includes('facilityExternalUkLocationForm')) {
                    locationExtUkValidation = false;
                }
                if (key.includes('facilityExternalUkInternationalForm')) {
                    locationInternational = false;
                }
                if (key.includes('facilityTypeForm')) {
                    facilityTypeValidation.push(message);
                }
                if (key.includes('facilityLinkFacilityForm')) {
                    facilityLink = false;
                }
                if (key.includes('facilityTechnicalSetupForm.equipment') && key.includes('note')) {
                    technicalSetupNote.push(message);
                } else if (key.includes('facilityTechnicalSetupForm')) {
                    technicalSetup.push(message);
                }
                if (key.includes('facilityChangeBookingType')) {
                    facilityChangeBookingValidation.push(message)
                }
                if (key.includes('facilityMarkAsUnavailable')) {
                    facilityMarkAsUnavailableValidation.push(message);
                }
                if (key.includes('validate_linked_facility')) {
                    linkedFacilityValidation = JSON.parse(message);
                }
                $(eleId).after($('<span class="validation-error-text"></span>').text(message));
                $(eleId).after($('<br class="validation-error-text">'));
            });
        });
        if (!locationExtUkValidation) {
            $('#location_external_uk_open_frm').after($('<span class="validation-error-text"></span>').text('Location details required.'));
            $('#location_external_uk_open_frm').after('<br class="validation-error-text">');
        }
        if (!locationInternational) {
            $('#location_external_international_open_frm').after($('<span class="validation-error-text"></span>').text('Location details required.'));
            $('#location_external_international_open_frm').after('<br class="validation-error-text">');
        }
        if (!facilityLink) {
            $('#linked_facility_open_frm').after($('<span class="validation-error-text"></span>').text('Linked Facility not completed.'));
            $('#linked_facility_open_frm').after('<br class="validation-error-text">');
        }
        if (facilityTypeValidation.length > 0) {
            $('#facility_type_open_frm').after($('<span class="validation-error-text"></span>').text(facilityTypeValidation.join(', ') + ' is required.'));
            $('#facility_type_open_frm').after('<br class="validation-error-text">');
        }
        if (technicalSetup.length > 0) {
            $('#technical_setup_open_frm').after($('<span class="validation-error-text"></span>').text('Technical Setup not completed.'));
            $('#technical_setup_open_frm').after('<br class="validation-error-text">');
        }
        if (facilityChangeBookingValidation.length > 0) {
            $('#change_booking_type_open_frm').after($('<span class="validation-error-text"></span>').text(facilityChangeBookingValidation.join(', ')));
            $('#change_booking_type_open_frm').after('<br class="validation-error-text">');
        }
        if (facilityMarkAsUnavailableValidation.length > 0) {
            $('#mark_as_unavailable_open_frm').after($('<span class="validation-error-text"></span>').text(facilityMarkAsUnavailableValidation.join(', ')));
            $('#mark_as_unavailable_open_frm').after('<br class="validation-error-text">');
        }
        if (Object.keys(linkedFacilityValidation).length > 0 && validationCount == 1) {
            that.linkFacilityValidationError(linkedFacilityValidation);
        }
    },
    /**
     * Facility linked facilities update validation handler
     * 
     * @param {*} data 
     * @returns 
     */
    linkFacilityValidationError: function (validationMessage) {
        let that = this; // NOSONAR javascript:S7740
        let formCanBeSubmitted = true;
        if (validationMessage['toBeRemoved'].length > 0) {
            $('#facility-linked-facility-validation-removed').empty().append(
                $('<div class="validation-error-text"></div>').text('Bookings Will be Deleted on the Following Linked Facility : ' + validationMessage['toBeRemoved'].join(', '))
            );
        }
        if (validationMessage['conflicts'].length > 0) {
            let conflicts = {};

            (validationMessage?.conflicts ?? []).forEach((data) => {
                const key = data.FC_FacilityName;
                const date = moment(data.FB_BookingStartDateTime).format('DD/MM/YYYY');
                (conflicts[key] ||= []).push(date);
            });

            let $div = $('<div><h4>Conflicts for Mandatory Linked Facility</h4></div>');
            Object.entries(conflicts).flatMap(([facility, dates]) => {
                $div.append(`<p>${facility} - ${dates.join(', ')}</p>`);
                return $div; // Return the modified $div from the callback
            });

            $('#facility-linked-facility-validation-conflicts').empty().append(
                $('<div class="validation-error-text"></div>').append($div)
            );
            formCanBeSubmitted = false;
        }
        $('#facility-linked-facility-validation-confirm-button').hide();
        $('#facility_booking_linked_decline_reason').val('');
        if (formCanBeSubmitted) {
            $('#facility-linked-facility-validation-confirm-button').show();
        }
        $('#facility-linked-facility-validation-confirm-button').off().on('click', function () {
            $('#facility-linked-facility-validation-modal').dialog("close");
            that.submitForm($('#submit-update-facility-form-button').attr('data-facility-id'), 1);
        });
        $('#facility-linked-facility-validation-cancel-button').off().on('click', function () {
            $('#facility-linked-facility-validation-modal').dialog("close");
        });
        $('#facility-linked-facility-validation-modal').dialog("open");
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
    /*
    * Soft delete  form
    */
    deleteForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('.delete-facility-icon').on('click', function () {
            let facilityId = $(this).attr('data-facility-id');
            $.ajax({
                url: that.deleteFormUrl.replace(':facility', facilityId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    $('#ignore-delete-facility-form').hide();
                    that.destroyForm(facilityId);
                },
                error: function (xhr, status, error) {
                }
            });
        });
    },
    destroyForm: function (facilityId) {
        let that = this; // NOSONAR javascript:S7740
        $("#ignore-delete-facility-form").on("click", function () {
            location.reload();
        });
        $("#cancel-delete-facility-form").on("click", function () {
            location.reload();
        });
        $("#approve-delete-facility-form").on("click", function () {
            $.ajax({
                url: that.deleteUrl.replace(":facility", facilityId),
                method: "POST",
                data: { "_method": "DELETE", "facility": facilityId, '_token': $('#facility-delete-form input[name=_token]').val() },
                success: function (data) {
                    if (data == false) {
                        $('.validation-error-text').html('There are future bookings for the facility you are trying to delete. Please delete/move the bookings to another facility before you delete the facility.');
                        $('#cancel-delete-facility-form').hide();
                        $('#approve-delete-facility-form').hide();
                        $('#ignore-delete-facility-form').show();
                    }
                    else {
                        location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').html('');
                    let data = JSON.parse(xhr.responseText);
                    let errors = data.errors;
                    $('.validation-error-text').html(errors['facility']);
                    $('#cancel-delete-facility-form').hide();
                    $('#approve-delete-facility-form').hide();
                    $('#ignore-delete-facility-form').show();
                }
            });
        });
    },
    /*
    * Archive Facility form
    */
    archiveForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#archive-facility-icon, #active-facility-icon').on('click', function () {
            let facilityId = $(this).attr('data-facility-id');
            let id = $(this).attr('id');
            $.ajax({
                url: that.archiveFormUrl.replace(':facility', facilityId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    if (id == "active-facility-icon") {
                        id = "active";
                        $('#archive-header-td-text').html('Activate Facility');
                        $('#archive-td-text').html('Are you sure you want to Activate this Facility?');
                    } else {
                        id = "archive";
                    }
                    $('#ignore-archive-facility-form').hide();
                    that.archive(facilityId, id);
                },
                error: function (xhr, status, error) {
                }
            });
        });
    },
    /**
     * Archive facility action
     * @param {*} facilityId
     */
    archive: function (facilityId, change_status) {
        let that = this; // NOSONAR javascript:S7740

        if (change_status == "archive") {
            let activeFromDateStr = $('#facility_active_from_date').val();
            let minArchiveDate = 0;

            if (activeFromDateStr) {
                let parts = activeFromDateStr.split('/');
                let activeFromDate = new Date(parts[2], parts[1] - 1, parts[0]);
                let today = new Date();
                today.setHours(0, 0, 0, 0);

                if (activeFromDate > today) {
                    minArchiveDate = activeFromDate;
                }
            }

            $("#archive_from").datepicker({
                firstDay: 6,
                changeMonth: true,
                changeYear: true,
                dateFormat: "dd/mm/yy",
                minDate: minArchiveDate
            });
        } else {
            $("#active_from").datepicker({
                firstDay: 6,
                changeMonth: true,
                changeYear: true,
                dateFormat: "dd/mm/yy",
                minDate: 0
            });
        }

        $("#ignore-archive-facility-form").on("click", function () {
            location.reload();
        });
        $("#cancel-archive-facility-form").on("click", function () {
            location.reload();
        });

        $("#approve-archive-facility-form").on("click", function () {
            $('#archive_from_confirm').hide();
            if (change_status == "archive") {
                $('#active_from_date').hide();
                $('#archive_from_date').show();
            } else {
                $('#active_from_date').show();
                $('#archive_from_date').hide();
            }
            $("#archive-facility-form, #active-facility-form").on("click", function () {
                if ($.trim($('#facility-archive-form input[name=archive_from]').val()) || ($.trim($('#facility-archive-form input[name=active_from]').val()))) {

                    let change_date;
                    if ($.trim($('#facility-archive-form input[name=archive_from]').val())) {
                        change_date = $('#facility-archive-form input[name=archive_from]').val();
                    } else {
                        change_date = $('#facility-archive-form input[name=active_from]').val();
                    }

                    $.ajax({
                        url: that.archiveUrl.replace(":facility", facilityId),
                        method: "GET",
                        data: { "facility": facilityId, '_token': $('#facility-archive-form input[name=_token]').val(), 'change_date': change_date, 'change_status': change_status },
                        success: function (data) {
                            if (data == false) {
                                if (change_status == "archive") {
                                    $('#archive_from').after($('<br/><span style="border-bottom: none; padding: 0px;" class="validation-error-text"></span>').text("The facility you are trying to archive has bookings beyond the 'Archive From' date. Please remove the bookings beyond 'Archive From' date to archive the facility."));
                                    $('#ignore-archive-facility').show();
                                    $('#archive-facility-form').hide();
                                    $("#ignore-archive-facility").on("click", function () {
                                        location.reload();
                                    });
                                }
                            }
                            else if (data == 'unavailable_conflict') {
                                if (change_status == "archive") {
                                    $('#archive_from').after($('<br/><span style="border-bottom: none; padding: 0px;" class="validation-error-text"></span>').text("The facility you are trying to archive is already marked as unavailable for the selected date."));
                                    $('#ignore-archive-facility').show();
                                    $('#archive-facility-form').hide();
                                    $("#ignore-archive-facility").on("click", function () {
                                        location.reload();
                                    });
                                }
                            }
                            else {
                                location.reload();
                            }
                        },
                        error: function (xhr, status, error) {
                            $('.validation-error-text').html('');
                            let data = JSON.parse(xhr.responseText);
                            errors = data.errors;
                            $('#cancel-archive-facility-form').hide();
                            $('#approve-archive-facility-form').hide();
                            $('#ignore-archive-facility-form').show();
                        }
                    });
                } else {
                    if (change_status == "archive") {
                        $('#archive_from').after($('<br/><span class="validation-error-text"></span>').text("The Archive From Date is required."));
                    } else {
                        $('#active_from').after($('<br/><span class="validation-error-text"></span>').text("The Archive From Date is required."));
                    }
                }
            });
        });
    },


    parseUnavailabilityConflict: function (resp) {
        let isConflict = false;

        try {
            if (resp && resp.message === 'FACILITY_UNAVAILABILITY_CONFLICT') {
                isConflict = true;
            } else if (resp && resp.errors) {
                Object.keys(resp.errors).forEach(function (key) {
                    (resp.errors[key] || []).forEach(function (msg) {
                        if (typeof msg === 'string' && msg.indexOf('FACILITY_UNAVAILABILITY_CONFLICT') === 0) {
                            isConflict = true;
                        }
                    });
                });
            }
        } catch (e) { }

        // ✅ define details so the return doesn't throw
        const details = resp && resp.conflictDetails ? resp.conflictDetails : null;

        return { isConflict, details };
    },



    showUnavailabilityConflictDialog: function (details, onProceed, onCancel) {
        // Ensure dialog exists/initialized
        this.initUnavailabilityConfirmDialog();

        // Minimal content (preserve existing copy)
        $('#facility-unavailability-confirm-content').html(
            '<p style="margin-bottom:10px;">Bookings found during the unavailability period.</p>' + '<p>They will be declined</p>' +
            '<p>Do you want to proceed anyway?</p>'
        );

        // (Re)bind buttons defensively
        $('#facility-unavailability-confirm-proceed').off('click').on('click', function () {
            const $proceed = $(this);
            const $cancel = $('#facility-unavailability-confirm-cancel');

            // Prevent double clicks
            $proceed.prop('disabled', true);
            $cancel.prop('disabled', true);

            if (typeof onProceed === 'function') {
                onProceed($proceed, $cancel);
            }
        });

        $('#facility-unavailability-confirm-cancel').off('click').on('click', function () {
            if (typeof onCancel === 'function') {
                onCancel($(this), $('#facility-unavailability-confirm-proceed'));
            }
        });

        // Open dialog
        $('#facility-unavailability-confirm-dialog').dialog('open');
    },


    handleUnavailabilityConflict: function (resp, formUrl, formData) {
        const that = this; // NOSONAR javascript:S7740
        const details = (resp && resp.conflictDetails) ? resp.conflictDetails : null;

        // Proceed: add confirm flag, keep spinner, retry POST
        const proceedFn = function ($proceedBtn, $cancelBtn) {
            $('#facility-unavailability-confirm-dialog').dialog('close');

            // Show spinner again to match previous UX
            that.spinnerFaceBox();

            // Let server override the conflict
            formData['confirm_mark_unavailable'] = 1;

            $.ajax({
                url: formUrl,
                method: "POST",
                data: formData,
                success: function () {
                    location.reload();
                },
                error: function (xhr2) {
                    // Re-enable buttons (in case dialog is reopened later)
                    if ($proceedBtn && $proceedBtn.length) $proceedBtn.prop('disabled', false);
                    if ($cancelBtn && $cancelBtn.length) $cancelBtn.prop('disabled', false);

                    // Close spinner facebox
                    try { $('#facebox').find('.close').trigger('click'); } catch (e) { }

                    // Normal validation rendering
                    let data2 = {};
                    try { data2 = JSON.parse(xhr2.responseText || '{}'); } catch (e) { }
                    $('.validation-error-text').remove();
                    that.facilityFormValidationMessages(data2.errors || {});
                }
            });
        };

        // Cancel: close dialog & spinner
        const cancelFn = function () {
            $('#facility-unavailability-confirm-dialog').dialog('close');
            that.closeSpinnerFaceBox();
        };

        that.showUnavailabilityConflictDialog(details, proceedFn, cancelFn);
    },

    /**
     * View Facility history
     */
    viewFacilityHistory: function () {
        let that = this; // NOSONAR javascript:S7740
        $('.history-facility-icon').on('click', function () {
            let facilityId = $(this).attr('data-facility-id');
            $.ajax({
                url: that.facilityHistoryUrl.replace(':facility', facilityId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                },
                error: function (xhr, status, error) {
                }
            });
        });
    },
    /**
     * Disable booking-related fields on popup open
     */
    disableBookingFieldsOnLoad: function () {
        if ($('#submit-create-facility-form-button').length === 0) {
            return;
        }

        $('input[name="allow_booking_frm"]').prop('disabled', true);
        $('input[name="on_off_booking_frm"]').prop('disabled', true);
        $('#allow_self_booking_from_frm').prop('disabled', true);
        $('#allow_self_booking_to_frm').prop('disabled', true);
    },
    /*
    * Populate Change Booking Type summary
    */
    populateChangeBookingTypeString: function () {
        $('#facility-change-booking-type-detail-string-span').remove();

        let startDate = $('#change_booking_type_start_date_frm').val();
        let endDate = $('#change_booking_type_end_date_frm').val();

        if (startDate === '' && endDate === '') {
            $('#change_booking_type_open_frm').before(
                $('<span id="facility-change-booking-type-detail-string-span" style="font-size:8px;">No Settings<br></span>')
            );
            return;
        }

        let days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        let $table = $('<table style="width:100%; border-collapse:collapse;"></table>');

        $table.append(
            $('<tr></tr>').append(
                $('<td colspan="4" style="font-weight:600;"></td>').text('Start Date : ' + startDate),
                $('<td colspan="4" style="font-weight:600;"></td>').text('End Date : ' + endDate)
            )
        );

        let $headerRow = $('<tr><td style="font-weight:600;">Slot/Day</td></tr>');
        let $fromRow = $('<tr><td style="font-weight:600;">Start</td></tr>');
        let $toRow = $('<tr><td style="font-weight:600;">End</td></tr>');

        days.forEach(function (day) {
            let key = day.toLowerCase();
            $headerRow.append($('<td style="font-weight:600;"></td>').text(day.slice(0, 3)));
            $fromRow.append($('<td></td>').text($('#facility_change_booking_type_' + key + '_from_frm').val()));
            $toRow.append($('<td></td>').text($('#facility_change_booking_type_' + key + '_to_frm').val()));
        });

        $table.append($headerRow, $fromRow, $toRow);

        $('#change_booking_type_open_frm').before(
            $('<span id="facility-change-booking-type-detail-string-span" style="font-size:8px;"></span>').append($table)
        );
    },

    /**
     * Initialize Primary Facility toggle for linked facility
     */
    initPrimaryFacility: function () {
        $('input[name="primary_facility_frm"]').on('change', function () {
            if ($(this).val() === 'yes') {
                $('#linked-facility-row').show();
            } else {
                $('#linked-facility-row').hide();
            }
        });
        // Trigger on load to set initial state
        $('input[name="primary_facility_frm"]:checked').trigger('change');
    },
    /**
     * 
     */
    initUnavailabilityConfirmDialog: function () {
        // Create the dialog container if missing (works without changing HTML template)
        if (!$('#facility-unavailability-confirm-dialog').length) {
            const $dlg = $(
                '<div id="facility-unavailability-confirm-dialog" style="display:none">' +
                '<div id="facility-unavailability-confirm-content" style="margin-bottom:1rem; font-size:12px;"></div>' +
                '<div style="text-align:right;">' +
                '<button id="facility-unavailability-confirm-proceed" class="btn btn-primary">Proceed</button> ' +
                '<button id="facility-unavailability-confirm-cancel" class="btn btn-secondary">Cancel</button>' +
                '</div>' +
                '</div>'
            );
            // Append near existing modal host
            $('#facility-tabs').after($dlg);
        }

        // Initialize jQuery UI dialog
        $('#facility-unavailability-confirm-dialog').dialog({
            title: 'Unavailability confirmation',
            autoOpen: false,
            modal: true,
            width: 420,
            height: 'auto',
            closeOnEscape: true,
            draggable: false,
            zIndex: 100000,
            open: function () {
                $(this).parent().find('.ui-dialog-titlebar-close').remove(); // Hide the X button
            }

        });

        // Button handlers will be rebound each time we open the dialog in submitForm()
    },
    /**
     * Facility Administrator form
     * 
     */
    facilityAdministratorForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $(".facility-administrator-icon").on("click", function () {
            $.ajax({
                url: that.facilityAdministratorUrl.replace(':facility', $(this).attr('data-facility-id')),
                method: "GET",
                beforeSend: function () {
                    that.clearModals();
                },
                success: function (data) {
                    $("#facility-administrator-form-dialog").html(data).dialog({
                        modal: true,
                        width: '60%',
                        height: 'auto',
                        title: "Add Facility Administrator",
                        open: function (event, ui) {
                            $(this).css('overflow', 'visible');
                            $(this).parent().css('overflow', 'visible');

                            // Convert ui-dialog to h1 for Accessibility
                            const $t = $(this).closest(".ui-dialog").find(".ui-dialog-title");
                            if (!$t.is("h1")) {
                                $t.replaceWith(
                                    $("<h1/>", { id: $t.attr("id"), class: $t.attr("class"), text: $t.text() })
                                        .css({ fontSize: "inherit", fontWeight: "inherit" })
                                );
                            }
                        }
                    }).dialog('open');
                    that.initFacilityAdministratorFormElements();
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    /**
     * Facility Administrator form elements
     */
    initFacilityAdministratorFormElements: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#facility_admins').chosen({
            width: '100%'
        });
        $('#facility-administrator-update-button').off().on('click', function () {
            that.submitFacilityAdministrator();
        });
    },
    /**
     * Facility Administrator submit form
     * 
     */
    submitFacilityAdministrator: function () {
        let that = this; // NOSONAR javascript:S7740
        let $select = $('#facility_admins');
        $('#facility-administrator-update-button').prop('disabled', true);
        $.ajax({
            url: that.facilityAdministratorStoreUrl.replace(':facility', $select.attr('data-facility-id')),
            method: "POST",
            data: {
                'facility_admins': $select.val()
            },
            beforeSend: function () {
            },
            success: function (data) {
                toastr.success('Facility Administrators Updated');
                $("#facility-administrator-form-dialog").dialog('close');
            },
            error: function (xhr, status, error) {

            }
        });
    },
    // Filter buttton collapsed and expanded for accessibility 
    filterExpandCollapsed:function(){
        const toggleBtn = document.getElementById("facility-filter-trigger-dropdown");
        const panel = document.getElementById("facility-filters-panel");
        const icon = document.getElementById("facility-filter-trigger-dropdown-icon");

        toggleBtn.addEventListener("click", () => {
        const expanded = toggleBtn.getAttribute("aria-expanded") === "true";
        toggleBtn.setAttribute("aria-expanded", !expanded);

        // Show / hide panel
        panel.hidden = expanded;
        });
    }

}

module.exports = Facility;