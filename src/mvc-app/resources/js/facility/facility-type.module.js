/**
 * Facility sub type JavaScript
 *
 * @param {Object} p parameters
 */

var FacilityType = function (p) {
    this.createUrl = p.createUrl;
    this.storeUrl = p.storeUrl;
    this.editUrl = p.editUrl;
    this.updateUrl = p.updateUrl;
    this.deleteFormUrl = p.deleteFormUrl;
    this.deleteUrl = p.deleteUrl;
    this.facilityTable = null;
    this.init();
};

FacilityType.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        that._a11y = new DatatableAccessibility();
        that.facilityTable = new DataTable('#facility-type-list-table', {
            "info": false,
            "dom": 'rtip',
            initComplete: function () {
                that._a11y.applyHeaderAccessibilityFixes(that.facilityTable);
            },
            order: [],
            columnDefs: [
                { 'orderable': false, 'targets': 0 }
            ],
            drawCallback: function (settings) {
                var api = this.api();

                that._a11y.applyHeaderAccessibilityFixes(that.facilityTable);
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
            }
        });

        let filterSetting = [
            { column_number: 1, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Facility Type filters" },
            { column_number: 2, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Facility Sub Type filters" }
        ];
        yadcf.init(that.facilityTable, filterSetting);
        that._a11y.addAriaLabelledbyToYadcfTextInputs('facility-type-list-table');
        that._a11y.addAriaLabelsToFilterClearButtons('facility-type-list-table', filterSetting);
        $('#facility-type-list-table').off('draw.dt.a11y').on('draw.dt.a11y', function () {
            that._a11y.addAriaLabelledbyToYadcfTextInputs('facility-type-list-table');
            that._a11y.addAriaLabelsToFilterClearButtons('facility-type-list-table', filterSetting);
        });
        that._a11y.applyHeaderAccessibilityFixes(that.facilityTable);
        that.createForm();
        that.updateForm();
        that.deleteForm();
        that.destroyForm();
    },

    createForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#create-facility-type-button").on("click", function () {
            $.ajax({
                url: that.createUrl,
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitForm();
                    $('.facility-sub-type').select2({ tags: true });
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    /*
    * Facility update form
    */
    updateForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#facility-type-list-table').on("click", ".edit-facilitytype-icon", function () {
            let facilitytypeId = $(this).attr('data-facilitytype-id');
            $.ajax({
                url: that.editUrl.replace(":facility-type", facilitytypeId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitUpdateForm(facilitytypeId);
                    $('.facility-sub-type').select2({ tags: true });
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    submitForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-create-facility-type-form').on("click", function () {
            $.ajax({
                url: that.storeUrl,
                method: "POST",
                data: $('#create-facility-type-form').serialize(),
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').remove();
                    let data = JSON.parse(xhr.responseText);
                    let errors = data.errors;
                    Object.keys(errors).forEach((key) => {
                        errors[key].forEach((message) => {
                            let eleId = '#' + key;
                            if (key == 'facility_sub_types') {
                                eleId = '.select2-container';
                            }
                            $(eleId).after($('<span class="validation-error-text"></span>').text(message));
                            $(eleId).after($('</br>'));
                        });
                    });
                }
            });
        });
    },
    /*
    * Update Faciltiy Type
    */
    submitUpdateForm: function (facilitytypeId) {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-update-facility-type-form').on("click", function () {
            $.ajax({
                url: that.updateUrl.replace(":facility-type", facilitytypeId),
                method: "POST",
                data: $('#create-facility-type-form').serialize(),
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').remove();
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    Object.keys(errors).forEach((key) => {
                        errors[key].forEach((message) => {
                            let eleId = '#' + key;
                            if (key == 'facility_sub_types') {
                                eleId = '.select2-container';
                            }
                            $(eleId).after($('<span class="validation-error-text"></span>').text(message));
                            $(eleId).after($('</br>'));
                        });
                    });
                }
            });
        });
    },
    /*
    * Soft delete faciltiy type
    */
    deleteForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#facility-type-list-table').on("click", ".delete-facilitytype-icon", function () {
            let facilityTypeID = $(this).attr('data-facilitytype-id');
            $.ajax({
                url: that.deleteFormUrl.replace(":facility-type", facilityTypeID),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    $('#ignore-delete-facilitytype-form').hide();
                    that.destroyForm(facilityTypeID);
                },
                error: function (xhr, status, error) {
                }
            });
        });

    },
    destroyForm: function (facilityTypeID) {
        let that = this; // NOSONAR javascript:S7740
        $("#ignore-delete-facilitytype-form").on("click", function () {
            location.reload();
        });
        $("#cancel-delete-facilitytype-form").on("click", function () {
            location.reload();
        });
        $("#approve-delete-facilitytype-form").on("click", function () {
            $.ajax({
                url: that.deleteUrl.replace(":facility-type", facilityTypeID),
                method: "POST",
                data: { "_method": "DELETE", "facility_type": facilityTypeID, '_token': $('#faciltiytype-delete-form input[name=_token]').val() },
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').html('');
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    $('.validation-error-text').html(errors['facility_type']);
                    $('#cancel-delete-facilitytype-form').hide();
                    $('#approve-delete-facilitytype-form').hide();
                    $('#ignore-delete-facilitytype-form').show();
                }
            });
        });
    }
}

module.exports = FacilityType;