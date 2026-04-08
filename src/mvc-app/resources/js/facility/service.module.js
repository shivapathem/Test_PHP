/**
 * Service JavaScript
 *
 * @param {Object} p parameters
 */

var ServiceVar = function (p) {
    this.createUrl = p.createUrl;
    this.storeUrl = p.storeUrl;
    this.editUrl = p.editUrl;
    this.updateUrl = p.updateUrl;
    this.deleteFormUrl = p.deleteFormUrl;
    this.deleteUrl = p.deleteUrl;
    this.serviceTable = null;
    this.init();
};

ServiceVar.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        that._a11y = new DatatableAccessibility();
        that.serviceTable = new DataTable('#service-list-table', {
            "info": false,
            "dom": 'rtip',
            initComplete: function () {
                that._a11y.applyHeaderAccessibilityFixes(that.serviceTable);
            },
            order: [],
            columnDefs: [
                { 'orderable': false, 'targets': 0 }
            ],
            drawCallback: function (settings) {
                var api = this.api();

                that._a11y.applyHeaderAccessibilityFixes(that.serviceTable);
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
            { column_number: 1, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Service filters" }
        ];
        yadcf.init(that.serviceTable, filterSetting);
        that._a11y.addAriaLabelledbyToYadcfTextInputs('service-list-table');
        that._a11y.addAriaLabelsToFilterClearButtons('service-list-table', filterSetting);

        $('#service-list-table').off('draw.dt.a11y').on('draw.dt.a11y', function () {
            that._a11y.addAriaLabelledbyToYadcfTextInputs('service-list-table');
            that._a11y.addAriaLabelsToFilterClearButtons('service-list-table', filterSetting);
        });
        that._a11y.applyHeaderAccessibilityFixes(that.serviceTable);
        that.createForm();
        that.updateForm();
        that.deleteForm();
        that.destroyForm();
    },

    createForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#create-service-button").on("click", function () {
            $.ajax({
                url: that.createUrl,
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitForm();
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
        $('#service-list-table').on("click", ".edit-service-icon", function () {
            let serviceId = $(this).attr('data-service-id');
            $.ajax({
                url: that.editUrl.replace(":service", serviceId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitUpdateForm(serviceId);
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    submitForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-create-service-form').on("click", function () {
            $.ajax({
                url: that.storeUrl,
                method: "POST",
                data: $('#service-form').serialize(),
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').remove();
                    let data = JSON.parse(xhr.responseText);
                    let errors = data.errors;
                    Object.keys(errors).forEach((key) => {
                        errors[key].forEach((message) => {
                            $('#' + key).after($('<span class="validation-error-text"></span>').text(message));
                            $('#' + key).after($('</br>'));
                        });
                    });
                }
            });
        });
    },
    /*
    * Update service
    */
    submitUpdateForm: function (serviceId) {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-update-service-form').on("click", function () {
            $.ajax({
                url: that.updateUrl.replace(":service", serviceId),
                method: "POST",
                data: $('#service-form').serialize(),
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').remove();
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    Object.keys(errors).forEach((key) => {
                        errors[key].forEach((message) => {
                            $('#' + key).after($('<span class="validation-error-text"></span>').text(message));
                            $('#' + key).after($('</br>'));
                        });
                    });
                }
            });
        });
    },
    /*
    * Soft delete service
    */
    deleteForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#service-list-table').on("click", ".delete-service-icon", function () {
            let serviceId = $(this).attr('data-service-id');
            $.ajax({
                url: that.deleteFormUrl.replace(":service", serviceId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    $('#ignore-delete-service-form').hide();
                    that.destroyForm(serviceId);
                },
                error: function (xhr, status, error) {
                }
            });
        });
    },
    destroyForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#ignore-delete-service-form").on("click", function () {
            location.reload();
        });
        $("#cancel-delete-service-form").on("click", function () {
            location.reload();
        });
        $("#approve-delete-service-form").on("click", function () {
            let serviceId = $(this).attr('data-service-id');
            $.ajax({
                url: that.deleteUrl.replace(":service", serviceId),
                method: "POST",
                data: { "_method": "DELETE", "service": serviceId, '_token': $('#service-delete-form input[name=_token]').val() },
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').html('');
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    $('.validation-error-text').html(errors['service']);
                    $('#cancel-delete-service-form').hide();
                    $('#approve-delete-service-form').hide();
                    $('#ignore-delete-service-form').show();
                }
            });
        });
    }
}

module.exports = ServiceVar;