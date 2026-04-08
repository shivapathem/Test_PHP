/**
 * Equipment JavaScript
 *
 * @param {Object} p parameters
 */

var ActionVar = function (p) {
    this.createUrl = p.createUrl;
    this.storeUrl = p.storeUrl;
    this.editUrl = p.editUrl;
    this.updateUrl = p.updateUrl;
    this.deleteFormUrl = p.deleteFormUrl;
    this.deleteUrl = p.deleteUrl;
    this.actionTable = null;
    this.init();
};

ActionVar.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        that._a11y = new DatatableAccessibility();
        that.actionTable = new DataTable('#action-list-table', {
            "info": false,
            "dom": 'rtip',
            initComplete: function () {
                that._a11y.applyHeaderAccessibilityFixes(that.actionTable);
            },
            language: {
                emptyTable: "No Actions Available."
            },
            order: [],
            columnDefs: [
                { 'orderable': false, 'targets': 0 }
            ],
            drawCallback: function (settings) {
                var api = this.api();

                that._a11y.applyHeaderAccessibilityFixes(that.actionTable);
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
            { column_number: 1, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Action Name filters" },
            { column_number: 2, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Description filters" }
        ];
        yadcf.init(that.actionTable, filterSetting);
        that._a11y.addAriaLabelledbyToYadcfTextInputs('action-list-table');
        that._a11y.addAriaLabelsToFilterClearButtons('action-list-table', filterSetting);
        $('#action-list-table').off('draw.dt.a11y').on('draw.dt.a11y', function () {
            that._a11y.addAriaLabelledbyToYadcfTextInputs('action-list-table');
            that._a11y.addAriaLabelsToFilterClearButtons('action-list-table', filterSetting);
        });
        that._a11y.applyHeaderAccessibilityFixes(that.actionTable);
        that.createForm();
        that.updateForm();
        that.deleteForm();
        that.destroyForm();
    },

    createForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#create-action-button").on("click", function () {
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
    * Equipment update form
    */
    updateForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#action-list-table').on("click", ".edit-action-icon", function () {
            let actionId = $(this).attr('data-action-id');
            $.ajax({
                url: that.editUrl.replace(":action", actionId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitUpdateForm(actionId);
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    submitForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-create-action-form').on("click", function () {
            $.ajax({
                url: that.storeUrl,
                method: "POST",
                data: $('#action-form').serialize(),
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
    * Update equipment
    */
    submitUpdateForm: function (actionId) {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-update-action-form').on("click", function () {
            $.ajax({
                url: that.updateUrl.replace(":action", actionId),
                method: "POST",
                data: $('#action-form').serialize(),
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
    * Soft delete equipment
    */
    deleteForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#action-list-table').on("click", ".delete-action-icon", function () {
            let actionId = $(this).attr('data-action-id');
            $.ajax({
                url: that.deleteFormUrl.replace(":action", actionId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    $('#ignore-delete-action-form').hide();
                    that.destroyForm(actionId);
                },
                error: function (xhr, status, error) {
                }
            });
        });
    },
    destroyForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#ignore-delete-action-form").on("click", function () {
            location.reload();
        });
        $("#cancel-delete-action-form").on("click", function () {
            location.reload();
        });
        $("#approve-delete-action-form").on("click", function () {
            let actionId = $(this).attr('data-action-id');
            $.ajax({
                url: that.deleteUrl.replace(":action", actionId),
                method: "POST",
                data: { "_method": "DELETE", "action": actionId, '_token': $('#action-delete-form input[name=_token]').val() },
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').html('');
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    $('.validation-error-text').html(errors['action']);
                    $('#cancel-delete-action-form').hide();
                    $('#approve-delete-action-form').hide();
                    $('#ignore-delete-action-form').show();
                }
            });
        });
    }
}

module.exports = ActionVar;