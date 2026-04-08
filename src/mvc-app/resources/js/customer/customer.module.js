/**
 * customer JavaScript
 *
 * @param {Object} p parameters
 */
var customerVar = function (p) {
    this.createUrl = p.createUrl;
    this.storeUrl = p.storeUrl;
    this.editUrl = p.editUrl;
    this.updateUrl = p.updateUrl;
    this.deleteFormUrl = p.deleteFormUrl;
    this.deleteUrl = p.deleteUrl;
    this.init();
};

customerVar.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        $("#company-addres-link-modal").tabs();
        let customerTable = new DataTable('#customer-list-table', {
            "info": false,
            "dom": 'rtip',
            order: [],
            columnDefs: [
                { 'orderable': false, 'targets': 0 }
            ],
        });
        that.createForm();
        that.updateForm();
        that.deleteForm();
        that.destroyForm();
    },
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        $("#company-addres-link-modal").tabs();
        let customerTable = new DataTable('#customer-list-table', {
            info: false,
            dom: "rtip",
            order: [],
            columnDefs: [{ targets: 0, orderable: false, searchable: false }],
            drawCallback: function (settings) {
                var api = this.api();

                (new CommonFunction).datatableAccessbilityText(api);

                var total = api.rows().count();
                var filtered = api.rows({ search: 'applied' }).count();
                var message = "";
                if (filtered === 0) {
                    message = "No results found after filtering.";
                } else if (filtered === 1) {
                    message = "1 result found after filtering.";
                } else if (filtered < total) {
                    message = filtered + " results found after filtering.";
                } else {
                    message = "Showing all " + total + " results.";
                }

                var $status = $('#filter-status');
                $status.text('');

                setTimeout(function () {
                    $status.text(message);
                }, 50);
                that.applyHeaderControlsAccessibilityFixes(api);
            }
        });

        let filterSetting = [
            { column_number: 1, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Customer ID filters" },
            { column_number: 2, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Contact Name filters" },
            {
                column_number: 3,
                filter_type: "select",
                filter_reset_button_text: "x",
                clear_button_label: "Clear Position filters"
            },
            { column_number: 4, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Phone Number filters" },
            { column_number: 5, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Email Address filters" },
            { column_number: 6, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Company Name filters" },
            { column_number: 7, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Company Address filters" }
        ];
        yadcf.init(customerTable, filterSetting);
        that.addAriaLabelledbyToYadcfTextInputs('customer-list-table');
        that.addAriaLabelsToFilterClearButtons('customer-list-table', filterSetting);
        that.applyHeaderControlsAccessibilityFixes(customerTable);
        customerTable.off('draw.dt.a11y').on('draw.dt.a11y', function () {
            that.addAriaLabelledbyToYadcfTextInputs('customer-list-table');
            that.addAriaLabelsToFilterClearButtons('customer-list-table', filterSetting);
            that.applyHeaderControlsAccessibilityFixes(customerTable);
        });

        // Add accessibility attributes to the select filter for column 3 (Position)
        $('#customer-list-table th').eq(3).find('select').attr('aria-label', 'Select Position').attr('role', 'combobox');

        that.createForm();
        that.updateForm();
        that.deleteForm();
        that.destroyForm();
    },

    /**
     * Add aria-labels to filter clear buttons
     */
    addAriaLabelsToFilterClearButtons: function (tableId, filterSetting) {
        let clearButtonLabels = {};
        filterSetting.forEach(function (filter) {
            if (filter.clear_button_label) {
                clearButtonLabels[filter.column_number] = filter.clear_button_label;
            }
        });

        setTimeout(function () {
            // Find all reset buttons and add aria-labels
            $('#' + tableId).find('.yadcf-filter-reset-button').each(function () {
                let $btn = $(this);
                let $th = $btn.closest('th');
                let $parentTr = $th.parent('tr');
                let thIndex = $parentTr.find('th').index($th);
                if (clearButtonLabels[thIndex]) {
                    $btn.attr('aria-label', clearButtonLabels[thIndex]);
                    $btn.attr('title', clearButtonLabels[thIndex]); // fallback
                }
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
                                .replace(/\s+/g, ' ')
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
                    $input.on('focusin.srlabel', function () {
                        $('#' + labelId).removeAttr('aria-hidden');
                    });
                    $input.on('focusout.srlabel', function () {
                        $('#' + labelId).attr('aria-hidden', 'true');
                    });
                });
            });
        }, 200);
    },
    /**
     * Hide header controls (sort buttons, filters, clear buttons, label spans) from being announced while
     * navigating the table body. Only expose them when they receive keyboard focus.
     */
    applyHeaderControlsAccessibilityFixes: function (dataTableInstance) {
        if (!dataTableInstance) return;
        var container = dataTableInstance.table().container();
        var $container = $(container);
        var $table = $(dataTableInstance.table().node());
        var tableId = $table.attr('id') || 'datatable';

        // Sort controls
        var $sortControls = $container
            .find('thead th .dt-column-order, thead th button.dt-column-order, thead th span[role="button"].dt-column-order, thead th span[role="button"][aria-label*="sort"], thead th span[role="button"][aria-label*="Activate to sort"], thead th span[role="presentation"].dt-column-order');
        $sortControls.off('focusin.srfix focusout.srfix');
        $sortControls.attr('aria-hidden', 'true');
        $sortControls.on('focusin.srfix', function () { $(this).removeAttr('aria-hidden'); });
        $sortControls.on('focusout.srfix', function () { $(this).attr('aria-hidden', 'true'); });

        // YADCF filters
        var $filters = $container
            .find('thead th select.yadcf-filter, thead th input.yadcf-filter, thead th textarea.yadcf-filter');
        $filters.off('focusin.srfix focusout.srfix');
        $filters.attr('aria-hidden', 'true');
        $filters.on('focusin.srfix', function () { $(this).removeAttr('aria-hidden'); });
        $filters.on('focusout.srfix', function () { $(this).attr('aria-hidden', 'true'); });

        // Clear buttons
        var $clearBtns = $container
            .find('thead th .yadcf-filter-reset-button');
        $clearBtns.off('focusin.srfix focusout.srfix');
        $clearBtns.attr('aria-hidden', 'true');
        $clearBtns.each(function () {
            var $btn = $(this);
            if (!$btn.attr('tabindex')) { $btn.attr('tabindex', '0'); }
        });
        $clearBtns.on('focusin.srfix', function () { $(this).removeAttr('aria-hidden'); });
        $clearBtns.on('focusout.srfix', function () { $(this).attr('aria-hidden', 'true'); });

        // Label spans created for aria-labelledby
        var $labelSpans = $container.find('thead span[id$="-label"]');
        $labelSpans.attr('aria-hidden', 'true');

        // If focus moves outside the header, force-hide header controls again
        $container.off('focusin.srfixhdr').on('focusin.srfixhdr', function (e) {
            if (!$(e.target).closest('thead').length) {
                $filters.attr('aria-hidden', 'true');
                $clearBtns.attr('aria-hidden', 'true');
                $labelSpans.attr('aria-hidden', 'true');
                $sortControls.attr('aria-hidden', 'true');
            }
        });
    },
    createForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#create-customer-button").on("click", function () {
            $.ajax({
                url: that.createUrl,
                method: "GET",
                beforeSend: function () {
                    that.clearModals();
                },
                success: function (data) {
                    $("#customer-form-dialog").html(data).dialog({
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        title: "",
                        role: 'dialog',
                        open: function (event, ui) {
                            $(this).parent().css({
                                'top': '2rem',
                                'width': '50%',
                                'left': '25%',
                                'right': '25%'
                            });

                            var dialogTitle = $('<h1 style="margin: 0px; font-size: 17px; margin-top: -35px; position: fixed;">', { id: 'dialog-title' }).text("Add External Customer");
                            $("#customer-form-dialog").prepend(dialogTitle);

                            $(this).attr('aria-labelledby', 'dialog-title');
                        }
                    }).dialog('open');
                    that.initFormElements();
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    /*
    * Customer update form
    */
    updateForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#customer-list-table").on("click", ".edit-customer-icon", function () {
            let customerId = $(this).attr('data-customer-id');
            $.ajax({
                url: that.editUrl.replace(":customer", customerId),
                method: "GET",
                beforeSend: function () {
                    that.clearModals();
                },
                success: function (data) {
                    $("#customer-form-dialog").html(data).dialog({
                        modal: true,
                        width: 'auto',
                        height: 'auto',
                        title: "",
                        role: "dialog",
                        open: function (event, ui) {
                            $(this).parent().css({
                                'top': '2rem',
                                'width': '50%',
                                'left': '25%',
                                'right': '25%'
                            });
                            var dialogTitle = $('<h1 style="margin: 0px; font-size: 17px; margin-top: -35px; position: fixed;">', { id: 'dialog-title' }).text("Edit External Customer");
                            $("#customer-form-dialog").prepend(dialogTitle);

                            $(this).attr('aria-labelledby', 'dialog-title');
                        }
                    }).dialog('open');
                    that.initFormElements();
                    that.populateCompanyAddressLinkString();
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    clearModals: function () {
        let removerFunction = function (modalId) {
            if ($('div[aria-describedby=' + modalId + ']').length) {
                $('#' + modalId).dialog('destroy').remove();
                $('div[aria-describedby=' + modalId + ']').remove();
            }
        };
        //Clear modals
        removerFunction('customer-form-dialog');
        removerFunction('company-addres-link-modal');
        if ($('#customer-form-dialog').length == 0) {
            $('#customer-list-table').after($('<div id="customer-form-dialog"></div>'));
        }
    },
    /**
     * Submit form
     * @param {*} customerId
     */
    submitForm: function (customerId = null) {
        let that = this; // NOSONAR javascript:S7740
        let customerFormData = that.fomatFormData($('#create-customer-form').serializeArray());
        let CustomerAddressLocal = that.fomatFormData($('#customer-adddress-form-local').serializeArray());
        CustomerAddressLocal['ec_buildingnumber'] = $('#ec_buildingnumber').val();
        CustomerAddressLocal['ec_street'] = $('#ec_street').val();
        CustomerAddressLocal['ec_city'] = $('#ec_city').val();
        CustomerAddressLocal['ec_postcode'] = $('#ec_postcode').val();
        CustomerAddressLocal['ec_county'] = $('#ec_county').val();
        CustomerAddressLocal['ec_country'] = $('#ec_country').val();


        let CustomerAddressInternational = that.fomatFormData(
            $('#customer-adddress-form-international').serializeArray());
        CustomerAddressLocal['ec_internationaladdress'] = $('#ec_internationaladdress').val();
        let formData = {
            '_token': $('#create-customer-form input[name=_token]').val(),
            'customerFormData': customerFormData,
            'CustomerAddressLocalForm': CustomerAddressLocal,
            'CustomerAddressInternationalForm': CustomerAddressInternational
        };
        let url = that.storeUrl;
        if (customerId != null) {
            url = that.updateUrl.replace(":customer", customerId);
            formData['_method'] = 'PUT';
        }
        $.ajax({
            url: url,
            method: "POST",
            data: formData,
            success: function (data) {
                location.reload();
            },
            error: function (xhr, status, error) {
                $('.validation-error-text').remove();
                let data = JSON.parse(xhr.responseText);
                that.custmerFormValidationMessages(data.errors);
            }
        });
    },
    /*
    * Form submission validation message
    */
    custmerFormValidationMessages(errors) {
        let locationExtUkValidation; // Declare the variable explicitly with let
        let locationInternational; // Declare the variable explicitly with let
        Object.keys(errors).forEach((key) => {
            errors[key].forEach((message) => {
                let formatedKey = key.replace("customerFormData.", "").replace("CustomerAddressLocalForm.", "").replace("CustomerAddressInternationalForm.", "");
                let eleId = '#' + formatedKey;
                if (key.includes('CustomerAddressLocalForm')) {
                    locationExtUkValidation = false;
                }
                if (key.includes('CustomerAddressInternationalForm')) {
                    locationInternational = false;
                }
                $(eleId).after($('<span class="validation-error-text"></span>').text(message));
                $(eleId).after($('</br class="validation-error-text">'));
            });
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
    /*
    /*
    * Initialize customer form elements
    */
    initFormElements: function () {

        let that = this; // NOSONAR javascript:S7740
        $('#company-addres-link-modal').dialog({
            title: 'Company Address',
            autoOpen: false,
            modal: true,
            width: "auto",
            height: "auto",
            open: function (event, ui) {
                $(this).parent().css({
                    'top': '2rem',
                    'width': '50%',
                    'left': '25%',
                    'right': '25%'
                });
            }
        });
        $("#company-addres-link-modal").tabs();

        //Select correct tab based on current values
        that.setInitialAddressTab();

        //Bind to input changes (namespaced to avoid duplicates if dialog reopens)
        $("#ec_internationaladdress, #ec_buildingnumber, #ec_street, #ec_city, #ec_postcode, #ec_country, #ec_county")
            .off("input.enforceOne change.enforceOne")
            .on("input.enforceOne change.enforceOne", function () {
                that.flipTabsByContent();
            });

        //Set initial enable/disable state when the dialog is shown
        that.flipTabsByContent();

        that.initCompanyAddressLink();
        $('#submit-customer-form-button').on('click', function () {
            $('.validation-error-text').remove();
            let valid = true;
            if (!that.validateAddress()) {
                valid = false;
            }
            if (!that.validateFormElement()) {
                valid = false;
            }
            if (!valid) {
                return false;
            }
            if ($('#external-customer-id').length > 0) {
                that.submitForm($('#external-customer-id').val());
            } else {
                that.submitForm();
            }
        });
        //Remove validation errors
        $('input').on('input', function () {
            $(this).parent().find('.validation-error-text').remove();
        });
        $('input, select').on('change', function () {
            $(this).parent().find('.validation-error-text').remove();
        });

        //Validate international text box
        let maxLength = 250;
        $('#ec_internationaladdress_char_count').text((maxLength - $('#ec_internationaladdress').val().length) + ' characters remaining');
        $('#ec_internationaladdress').on('input', function () {
            var currentLength = $(this).val().length;
            var remaining = maxLength - currentLength;
            $('#ec_internationaladdress_char_count').text(remaining + ' characters remaining');
        });
        let trimFunction = function () {
            let cleaned = $('#ec_internationaladdress').val().replace(/\s+/g, ' ').trim();
            $('#ec_internationaladdress').val(cleaned);
        }
        $('#ec_internationaladdress').on('blur change paste', function () {
            trimFunction();
        });
    },
    initCompanyAddressLink: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#company_address_open_frm').on('click', function () {
            $(this).parent().css({
                'top': '2rem',
                'width': '70%',
                'left': '15%',
                'right': '15%'
            });
            $('#company-addres-link-modal').dialog("open");
        });
        $('.submit-address-form-button').on('click', function () {
            that.validateAddress();
        });
    },
    /**
     * Show error for all validations
     */
    showFieldError: function ($field, message) {
        const $next = $field.next('.validation-error-text');
        if ($next.length) {
            $next.text(message);
        } else {
            $field.after($('<div class="validation-error-text"></div>').text(message));
        }
        $field.addClass('error');
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
    /**
     * Validate form element
     *
     */
    validateFormElement: function () {
        let valid = true;

        // Remove previous errors
        $('#ec_contactname, #ec_telephonenumber, #ec_email, #ec_companyname').next('.validation-error-text').remove();

        if ($('#ec_contactname').val().length === 0) {
            valid = false;
            this.showFieldError($('#ec_contactname'), 'The Contact Name is required.');
        }

        if ($('#ec_telephonenumber').val().length === 0) {
            valid = false;
            this.showFieldError($('#ec_telephonenumber'), 'The Telephone Number is required.');
        }

        if ($('#ec_email').val().length === 0) {
            valid = false;
            this.showFieldError($('#ec_email'), 'The Email Address is required.');
        }

        if ($('#ec_companyname').val().length === 0) {
            valid = false;
            this.showFieldError($('#ec_companyname'), 'The Company Name is required.');
        }

        return valid;
    },
    /**
     * Validate Address
     */
    validateAddress: function () {
        let that = this; // NOSONAR javascript:S7740
        let valid = true;

        $('#company-address-container').find('.validation-error-text').remove();

        const $building = $('#ec_buildingnumber');
        const $street = $('#ec_street');
        const $city = $('#ec_city');
        const $postcode = $('#ec_postcode');
        const $country = $('#ec_country');
        const $international = $('#ec_internationaladdress');

        const hasInternational = $.trim($international.val()).length > 0;
        const hasBuilding = ($building.val()).length > 0;
        const hasStreet = ($street.val()).length > 0;
        const hasCity = ($city.val()).length > 0;
        const hasPostcode = ($postcode.val()).length > 0;
        const hasCountry = ($country.val()).length > 0;

        if (!hasInternational) {
            if (!hasBuilding) {
                valid = false;
                this.showFieldError($building, 'The Building Number field is required.');
            } else {
                this.clearFieldError($building);
            }

            if (!hasStreet) {
                valid = false;
                this.showFieldError($street, 'The Street field is required.');
            } else {
                this.clearFieldError($street);
            }

            if (!hasCity) {
                valid = false;
                this.showFieldError($city, 'The City field is required.');
            } else {
                this.clearFieldError($city);
            }

            if (!hasPostcode) {
                valid = false;
                this.showFieldError($postcode, 'The Post Code field is required.');
            } else {
                this.clearFieldError($postcode);
            }

            if (!hasCountry) {
                valid = false;
                this.showFieldError($country, 'The Country field is required.');
            } else {
                this.clearFieldError($country);
            }
        }

        if (hasBuilding && hasStreet && hasCity && hasCountry) {
            that.populateCompanyAddressLinkString();
        } else {
            if (!hasInternational) {
                this.showFieldError($international, 'The International Address field is required.');
            } else {
                this.clearFieldError($international);
                that.populateCompanyAddressLinkString();
            }
        }

        // Show a single form-level error message to avoid stacking
        const $formAnchor = $('#company_address_open_frm');
        $formAnchor.next('.validation-error-text').remove();
        if (!valid) {
            $formAnchor.after(
                $('<div class="validation-error-text"></div>')
                    .text('The Company Address is Incomplete.')
            );
        }

        this.flipTabsByContent();

        return valid;
    },
    /*
    * Populate the company address link string
    */
    populateCompanyAddressLinkString: function () {

        $('.error-for-address-validation').html('');
        $('#company-addres-link-modal').dialog("close");

        $('#linked-address-view-string').remove();
        let $paragraph = $('<p id="linked-address-view-string" class="linked-facility-view-scroll" style="margin: unset; font-size:9px"></p>');


        let ec_internationaladdress = $('#ec_internationaladdress').val();

        let ec_buildingnumber = $('#ec_buildingnumber').val();
        let ec_city = $('#ec_city').val();
        let ec_street = $('#ec_street').val();
        let ec_county = $('#ec_county').val();
        let ec_postcode = $('#ec_postcode').val();
        let ec_country = $('#ec_country').val();

        let address = [];

        if (!$.trim($("#ec_internationaladdress").val())) {
            if ($.trim($("#ec_buildingnumber").val())) {
                address.push(" Building Number - " + ec_buildingnumber + ",");
            }
            if ($.trim($("#ec_city").val())) {
                address.push(" City - " + ec_city + ",");
            }
            if ($.trim($("#ec_street").val())) {
                address.push(" Street - " + ec_street + ",");
            }
            if ($.trim($("#ec_county").val())) {
                address.push(" County - " + ec_county + ",");
            }
            if ($.trim($("#ec_postcode").val())) {
                address.push(" Post Code - " + ec_postcode + ",");
            }
            if ($.trim($("#ec_country").val())) {
                address.push(" Country - " + ec_country + ".");
            }
        } else {
            address.push(" International Address - " + ec_internationaladdress);
        }

        $paragraph.text(address.join(''));

        $('#company_address_open_frm').before($paragraph);
        this.flipTabsByContent();
    },
    /*
    * Soft delete customer
    */
    deleteForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#customer-list-table").on("click", ".delete-customer-icon", function () {
            let customerId = $(this).attr('data-customer-id');
            $.ajax({
                url: that.deleteFormUrl.replace(":customer", customerId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    $('#ignore-delete-customer-form').hide();
                    that.destroyForm(customerId);
                },
                error: function (xhr, status, error) {
                }
            });
        });
    },
    destroyForm: function (customerId) {
        let that = this; // NOSONAR javascript:S7740
        $("#ignore-delete-customer-form").on("click", function () {
            location.reload();
        });
        $("#cancel-delete-customer-form").on("click", function () {
            location.reload();
        });
        $("#approve-delete-customer-form").on("click", function () {
            $.ajax({
                url: that.deleteUrl.replace(":customer", customerId),
                method: "POST",
                data: { "_method": "DELETE", "customer": customerId, '_token': $('#customer-delete-form input[name=_token]').val() },
                success: function (data) {
                    if (data == false) {
                        $('.validation-error-text').html('');
                        $('.validation-error-text').html('Booking Exist for the Selected Customer. So, Customer cannot be Deleted');
                        $('#cancel-delete-customer-form').hide();
                        $('#approve-delete-customer-form').hide();
                        $('#ignore-delete-customer-form').show();
                    }
                    else {
                        location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').html('');
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    $('.validation-error-text').html(errors['customer']);
                    $('#cancel-delete-customer-form').hide();
                    $('#approve-delete-customer-form').hide();
                    $('#ignore-delete-customer-form').show();
                }
            });
        });
    },
    /*
    * Checks for local address
    */
    localHas: function () {
        return !!($.trim($("#ec_buildingnumber").val()) || $.trim($("#ec_street").val()) || $.trim($("#ec_city").val()) || $.trim($("#ec_postcode").val()) ||
            $.trim($("#ec_country").val()) || $.trim($("#ec_county").val()));
    },
    /*
    * Checks for international address
    */
    internationalHas: function () {
        return $.trim($("#ec_internationaladdress").val()).length > 0;
    },
    /*
    * Enables/disables local or international address
    */
    flipTabsByContent: function () {
        let that = this; // NOSONAR javascript:S7740
        let $tabs = $("#company-addres-link-modal");

        if (!$tabs.data("ui-tabs")) {
            $tabs.tabs();
        }

        let local = that.localHas()
        let international = that.internationalHas();
        const maxLen = 250;

        if (international && !local) {
            // International is active → disable + CLEAR Local
            $("#ec_buildingnumber, #ec_street, #ec_city, #ec_postcode, #ec_country, #ec_county").val("")                   // <-- clear to empty string

            $("#local-address").find(":input, button").prop("disabled", true);
            $("#international-address").find(":input, button").prop("disabled", false);

            // Update char counter directly (if present)
            const len = $('#ec_internationaladdress').val().length;
            $('#ec_internationaladdress_char_count').text((maxLen - len) + ' characters remaining');

        } else if (local && !international) {
            // Local active - CLEAR International first, then disable International, enable Local
            $("#ec_internationaladdress").val("");

            $("#international-address").find(":input, button").prop("disabled", true);
            $("#local-address").find(":input, button").prop("disabled", false);

            // Reset char counter
            $('#ec_internationaladdress_char_count').text('250 characters remaining');

        } else {
            // None or both (transient while typing) → enable both; do NOT clear here
            $("#local-address, #international-address").find(":input, button").prop("disabled", false);

            // Keep counter in sync
            const len = $('#ec_internationaladdress').val().length;
            $('#ec_internationaladdress_char_count').text((maxLen - len) + ' characters remaining');
        }

        // Optional block of switching
        $tabs.off("tabsbeforeactivate.enforceOne")
            .on("tabsbeforeactivate.enforceOne", function (e, ui) {
                let targetHref = ui.newTab.find(".ui-tabs-anchor").attr("href");
                if (that.localHas() && targetHref === "#international-address") {
                    e.preventDefault();
                }
                if (that.internationalHas() && targetHref === "#local-address") {
                    e.preventDefault();
                }
            });
    },

    setInitialAddressTab: function () {
        let that = this; // NOSONAR javascript:S7740
        let $tabs = $("#company-addres-link-modal"); // IMPORTANT: single 'd' matches your HTML
        if (!$tabs.length) return;

        // Ensure tabs are initialized
        if (!$tabs.data("ui-tabs")) {
            $tabs.tabs();
        }

        let internationalAddress = $.trim($("#ec_internationaladdress").val()).length > 0;
        let localAddress = that.localHas();

        // Find indices by href (safer than relying on auto-generated ui-id-* values)
        var anchors = $tabs.find(".ui-tabs-nav .ui-tabs-anchor");

        let localIndex = -1;
        let intlIndex = -1;

        anchors.each(function (i) {
            let href = $(this).attr("href");
            if (href === "#local-address") {
                localIndex = i;
            }
            if (href === "#international-address") {
                intlIndex = i;
            }
        });

        // Decide which tab should be active
        let activeIndex;
        if (internationalAddress) {
            activeIndex = (intlIndex >= 0 ? intlIndex : 0);
        } else if (localAddress) {
            activeIndex = (localIndex >= 0 ? localIndex : 0);
        } else {
            // Default (choose Local)
            activeIndex = (localIndex >= 0 ? localIndex : 0);
        }

        // Activate it
        $tabs.tabs("option", "active", activeIndex);

        // Optional: once set, also reflect enable/disable state
        if (typeof this.flipTabsByContent === "function") {
            this.flipTabsByContent();
        }
    }
}

module.exports = customerVar;