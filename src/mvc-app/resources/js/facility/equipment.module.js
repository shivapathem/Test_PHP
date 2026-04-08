/**
 * Equipment JavaScript
 *
 * @param {Object} p parameters
 */

var EquipmentVar = function (p) {
    this.createUrl = p.createUrl;
    this.storeUrl = p.storeUrl;
    this.editUrl = p.editUrl;
    this.updateUrl = p.updateUrl;
    this.deleteFormUrl = p.deleteFormUrl;
    this.deleteUrl = p.deleteUrl;
    this.equipmentTable = null;
    this.init();
};

EquipmentVar.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        that._a11y = new DatatableAccessibility();
        that.equipmentTable = new DataTable('#equipment-list-table', {
            "info": false,
            "dom": 'rtip',
            initComplete: function () {
                that._a11y.applyHeaderAccessibilityFixes(that.equipmentTable);
            },
            order: [],
            columnDefs: [
                { 'orderable': false, 'targets': 0 }
            ],
            drawCallback: function (settings) {
                var api = this.api();

                that._a11y.applyHeaderAccessibilityFixes(that.equipmentTable);
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
            { column_number: 1, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Equipment filters" },
            { column_number: 2, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Equipment Type filters" }
        ];
        yadcf.init(that.equipmentTable, filterSetting);
        that._a11y.addAriaLabelledbyToYadcfTextInputs('equipment-list-table');
        that._a11y.addAriaLabelsToFilterClearButtons('equipment-list-table', filterSetting);
        $('#equipment-list-table').off('draw.dt.a11y').on('draw.dt.a11y', function () {
            that._a11y.addAriaLabelledbyToYadcfTextInputs('equipment-list-table');
            that._a11y.addAriaLabelsToFilterClearButtons('equipment-list-table', filterSetting);
        });
        that._a11y.applyHeaderAccessibilityFixes(that.equipmentTable);
        that.createForm();
        that.updateForm();
        that.deleteForm();
        that.destroyForm();
    },

    createForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#create-equipment-button").on("click", function () {
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
        $("#equipment-list-table").on("click", ".edit-equipment-icon", function () {
            let equipmentId = $(this).attr('data-equipment-id');
            $.ajax({
                url: that.editUrl.replace(":equipment", equipmentId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitUpdateForm(equipmentId);
                },
                error: function (xhr, status, error) {

                }
            });
        });
    },
    submitForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-create-equipment-form').on("click", function () {
            $.ajax({
                url: that.storeUrl,
                method: "POST",
                data: $('#equipment-form').serialize(),
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
    submitUpdateForm: function (equipmentId) {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-update-equipment-form').on("click", function () {
            $.ajax({
                url: that.updateUrl.replace(":equipment", equipmentId),
                method: "POST",
                data: $('#equipment-form').serialize(),
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
        $("#equipment-list-table").on("click", ".delete-equipment-icon", function () {
            let equipmentId = $(this).attr('data-equipment-id');
            $.ajax({
                url: that.deleteFormUrl.replace(":equipment", equipmentId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    $('#ignore-delete-equipment-form').hide();
                    that.destroyForm(equipmentId);
                },
                error: function (xhr, status, error) {
                }
            });
        });
    },
    destroyForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#ignore-delete-equipment-form").on("click", function () {
            location.reload();
        });
        $("#cancel-delete-equipment-form").on("click", function () {
            location.reload();
        });
        $("#approve-delete-equipment-form").on("click", function () {
            let equipmentId = $(this).attr('data-equipment-id');
            $.ajax({
                url: that.deleteUrl.replace(":equipment", equipmentId),
                method: "POST",
                data: { "_method": "DELETE", "equipment": equipmentId, '_token': $('#equipment-delete-form input[name=_token]').val() },
                success: function (data) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    $('.validation-error-text').html('');
                    let data = JSON.parse(xhr.responseText);
                    errors = data.errors;
                    $('.validation-error-text').html(errors['equipment']);
                    $('#cancel-delete-equipment-form').hide();
                    $('#approve-delete-equipment-form').hide();
                    $('#ignore-delete-equipment-form').show();
                }
            });
        });
    }
}

module.exports = EquipmentVar;