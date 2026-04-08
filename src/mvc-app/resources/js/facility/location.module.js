/**
 * Location JavaScript
 *
 * @param {Object} p parameters
 */

var LocationVar = function (p) {
    this.createUrl = p.createUrl;
    this.storeUrl = p.storeUrl;
    this.editUrl = p.editUrl;
    this.updateUrl = p.updateUrl;
    this.deleteFormUrl = p.deleteFormUrl;
    this.deleteUrl = p.deleteUrl;
    this.locationTable = null
    this.init();
};

LocationVar.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        that._a11y = new DatatableAccessibility();
        that.locationTable = new DataTable('#location-list-table', {
            "info": false,
            "dom": 'rtip',
            initComplete: function () {
                that._a11y.applyHeaderAccessibilityFixes(that.locationTable);
            },
            order: [],
            columnDefs: [
                { 'orderable': false, 'targets': 0 }
            ],
            drawCallback: function (settings) {
                var api = this.api();

                that._a11y.applyHeaderAccessibilityFixes(that.locationTable);
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
            { column_number: 1, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Location filters" }
        ];
        yadcf.init(that.locationTable, filterSetting);
        that._a11y.addAriaLabelledbyToYadcfTextInputs('location-list-table');
        that._a11y.addAriaLabelsToFilterClearButtons('location-list-table', filterSetting);
        $('#location-list-table').off('draw.dt.a11y').on('draw.dt.a11y', function () {
            that._a11y.addAriaLabelledbyToYadcfTextInputs('location-list-table');
            that._a11y.addAriaLabelsToFilterClearButtons('location-list-table', filterSetting);
        });
        that._a11y.applyHeaderAccessibilityFixes(that.locationTable);
        that.createForm();
        that.updateForm();
        that.deleteForm();
        that.destroyForm();
    },

    createForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#create-location-button").on("click", function () {
            $.ajax({
                url: that.createUrl,
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitCreateForm();
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    /*
    * Location update form
    */
    updateForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#location-list-table').on("click", ".edit-location-icon", function () {
            let locationId = $(this).attr('data-location-id');
            $.ajax({
                url: that.editUrl.replace(":location", locationId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitUpdateForm(locationId);
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    submitCreateForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-create-location-form').on("click", function () {
            $.ajax({
                url: that.storeUrl,
                method: "POST",
                data: $('#location-form').serialize(),
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').remove();
                    let data = JSON.parse(xhr.responseText);
                    let errors = data.errors;
                    Object.keys(errors).forEach((key) => {
                        errors[key].forEach((message) => {
                            $('#' + key).after($('<div class="validation-error-text"></div>').text(message));
                        });
                    });
                }
            });
        });
    },
    /*
    * Update location
    */
    submitUpdateForm: function (locationId) {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-update-location-form').on("click", function () {
            $.ajax({
                url: that.updateUrl.replace(":location", locationId),
                method: "POST",
                data: $('#location-form').serialize(),
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').remove();
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    Object.keys(errors).forEach((key) => {
                        errors[key].forEach((message) => {
                            $('#' + key).after($('<div class="validation-error-text"></div>').text(message));
                        });
                    });
                }
            });
        });
    },
    /*
    * Soft delete location
    */
    deleteForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#location-list-table').on("click", ".delete-location-icon", function () {
            let locationId = $(this).attr('data-location-id');
            $.ajax({
                url: that.deleteFormUrl.replace(":location", locationId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    $('#ignore-delete-location-form').hide();
                    that.destroyForm(locationId);
                },
                error: function (xhr, status, error) {
                }
            });
        });

    },
    destroyForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#ignore-delete-location-form").on("click", function () {
            location.reload();
        });
        $("#cancel-delete-location-form").on("click", function () {
            location.reload();
        });
        $("#approve-delete-location-form").on("click", function () {
            let locationId = $(this).attr('data-location-id');
            $.ajax({
                url: that.deleteUrl.replace(":location", locationId),
                method: "POST",
                data: { "_method": "DELETE", "location": locationId, '_token': $('#location-delete-form input[name=_token]').val() },
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').html('');
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    $('.validation-error-text').html(errors['location']);
                    $('#cancel-delete-location-form').hide();
                    $('#approve-delete-location-form').hide();
                    $('#ignore-delete-location-form').show();
                }
            });
        });
    }
}

module.exports = LocationVar;