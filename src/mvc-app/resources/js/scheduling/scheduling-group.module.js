/**
 * Scheduling Group JavaScript
 *
 * @param {Object} p parameters
 */
var SchedulingVar = function (p) {
    this.createUrl = p.createUrl;
    this.storeUrl = p.storeUrl;
    this.editUrl = p.editUrl;
    this.updateUrl = p.updateUrl;
    this.deleteFormUrl = p.deleteFormUrl;
    this.deleteUrl = p.deleteUrl;
    this.schedulingGroupHistoryUrl = p.schedulingGroupHistoryUrl;
    this.table = null;
    this.filterSettings = [];
    this.init();
};

SchedulingVar.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this; // NOSONAR javascript:S7740
        this.filterSettings = [
            { column_number: 1, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Area Name filter" },
            { column_number: 2, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Scheduling Group Name filter" },
            { column_number: 3, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Scheduling Team filter" },
            { column_number: 4, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Allocations Menu filter" },
            { column_number: 5, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Notes filter" },
            { column_number: 6, filter_type: "text", filter_reset_button_text: "x", clear_button_label: "Clear Last Amended By filter" }
        ];
        this.table = $('#scheduling-list-table').DataTable({
            info: false,
            dom: 'rtip',
            order: [],
            columnDefs: [{ orderable: false, targets: 0 }],
            initComplete: function () {
                that.applyHeaderAccessibilityFixes();
                that.addAriaLabelsToFilterClearButtons(
                    'scheduling-list-table',
                    that.filterSettings
                );
            },
            drawCallback: function () {
                var api = this.api();
                that.applyHeaderAccessibilityFixes();
                that.addAriaLabelsToFilterClearButtons(
                    'scheduling-list-table',
                    that.filterSettings
                );
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
    
    yadcf.init(this.table, this.filterSettings);

    this.table.on('draw', function () {
        that.bindRowEvents();
    });
        this.bindRowEvents();
        this.createForm();
        // Add focus functionality
        this.setupFocusAndTab();
    },

    /**
     * Clean up header announcements for NVDA without changing the UI.
     */
    applyHeaderAccessibilityFixes: function () {
        if (!this.table) return;
        var container = this.table.table().container();
        var $container = $(container);
        var $table = $(this.table.table().node());
        var tableId = $table.attr('id') || 'datatable';

        // Expose the sort button only on keyboard focus
        var $sortControls = $container
            .find('thead th .dt-column-order, thead th button.dt-column-order, thead th span[role="button"].dt-column-order, thead th span[role="button"][aria-label*="sort"], thead th span[role="button"][aria-label*="Activate to sort"]');

        $sortControls.off('focusin.srfix focusout.srfix');
        $sortControls.attr('aria-hidden', 'true');
        $sortControls.each(function () {
            var $btn = $(this);
            // Keep native buttons alone; for other elements ensure they can be tabbed to.
            if (($btn.prop('tagName') || '').toLowerCase() !== 'button') {
                var tabindex = $btn.attr('tabindex');
                if (!tabindex || tabindex === '-1') { $btn.attr('tabindex', '0'); }
            }
        });
        $sortControls.on('focusin.srfix', function () { $(this).removeAttr('aria-hidden'); });
        $sortControls.on('focusout.srfix', function () { $(this).attr('aria-hidden', 'true'); });
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

    $('#' + tableId).find('button.yadcf-filter-reset-button').each(function () {
            let $btn = $(this);
            let $th = $btn.closest('th');
            let thIndex = $th.parent('tr').find('th').index($th);

            if (!clearButtonLabels[thIndex]) return;

            let label = clearButtonLabels[thIndex];

            $btn.attr({
                'aria-label': label,
                'title': label,
                'tabindex': '0',
                'aria-hidden': 'true'
            });

            $btn.off('.srfix');

            $btn.on('focusin.srfix', function () {
                $(this).removeAttr('aria-hidden');
            });

            $btn.on('focusout.srfix', function () {
                $(this).attr('aria-hidden', 'true');
            });
        });
    },

    setupFocusAndTab: function () {
    
        var focusableSelector = [
            'a[href]',
            'button:not([disabled])',
            'textarea:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            '[tabindex]:not([tabindex="-1"])'
        ].join(',');

        // When facebox popup opens
        $(document).on('afterReveal.facebox', function () {
            var $modal = $('#facebox');

            var $focusable = $modal
                .find(focusableSelector)
                .filter(':visible');

            if (!$focusable.length) return;

            var first = $focusable.first()[0];
            var last  = $focusable.last()[0];

            // Always force focus into popup
            first.focus();

            // Trap tab key
            $modal.on('keydown.focusTrap', function (e) {
                if (e.key !== 'Tab') return;

                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    // Tab
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            });
        });

        // Cleanup when popup closes
        $(document).on('close.facebox', function () {
            $('#facebox').off('keydown.focusTrap');
        });
    },

    bindRowEvents: function () {
        this.updateForm();
        this.deleteForm();
        this.schedulingGroupHistory();
        this.contextMenu();
    },
    createForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#create-scheduling-button").on("click", function () {
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
    /**
     * Create Scheduling Group
     */
    submitForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-create-scheduling-form').on("click", function () {
            $(this).prop('disabled', true);
            $.ajax({
                url: that.storeUrl,
                method: "POST",
                data: $('#scheduling-form').serialize(),
                headers: { 'Accept': 'application/json' },
                success: function (data) {
                    $.facebox('<p style="margin: 5px;padding-top: 8px;">'+data.message+'</p>');
                    setTimeout(() => {window.location.href = data.redirect;}, 1500);
                },
                error: function (xhr) {
                    $('#submit-create-scheduling-form').prop('disabled', false);
                    $('.validation-error-text').remove();
                    let errors = xhr.responseJSON.errors;
                    if (xhr.status === 422) {
                        $.each(errors, function (field, messages) {
                            let input = $('#' + field);
                            if (input.length) {
                                input.after($('<span class="validation-error-text"></span>').text(messages[0])).after('<br>');
                            }
                        });
                    } else {
                        let message = xhr.responseJSON?.message || 'Unexpected error occurred';
                        $.facebox(message);
                    }
                }
            });
        });
    },
    /*
    * Update Scheduling Group Form
    */
    updateForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#scheduling-list-table").off('click', '.edit-scheduling-group-icon').on("click", ".edit-scheduling-group-icon", function () {
            let groupId = $(this).attr('data-scheduling-group-id');
            $.ajax({
                url: that.editUrl.replace(":schedulingGroup", groupId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    that.submitUpdateForm(groupId);
                },
                   error: function (xhr, status, error) {
                }
            });
        });
    },
    submitUpdateForm: function (groupId) {
        let that = this; // NOSONAR javascript:S7740
        $('#submit-update-scheduling-form').on("click", function () {
            // Validate form fields
            let isValid = true;
            $('.validation-error-text').remove();

            // Check if group name is provided
            if (!$('#group_name').val().trim()) {
                $('#group_name').after($('<span class="validation-error-text"></span>').text('Scheduling Group Name is required.'));
                $('#group_name').after($('</br>'));
                isValid = false;
            }

            // Check if area is selected
            if (!$('#divisionid').val()) {
                $('#divisionid').after($('<span class="validation-error-text"></span>').text('Area is required.'));
                $('#divisionid').after($('</br>'));
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            // Build form data to ensure scheduling_team_ids is always included
            let formData = $('#scheduling-form').serializeArray();
            $.ajax({
                url: that.updateUrl.replace(":schedulingGroup", groupId),
                method: "POST",
                data: $.param(formData),
                success: function (data) {
                    $.facebox('<p style="margin: 5px;padding-top: 8px;">'+data.message+'</p>');
                    setTimeout(() => {window.location.href = data.redirect;}, 1500);
                },
                error: function (xhr) {
                    $('.validation-error-text').remove();
                    let errors = xhr.responseJSON.errors;
                    if (xhr.status === 422) {
                        $.each(errors, function (field, messages) {
                            let input = $('#' + field);
                            if (input.length) {
                                input.after($('<span class="validation-error-text"></span>').text(messages[0])).after('<br>');
                            }
                        });
                    } else {
                        let message = xhr.responseJSON?.message || 'Unexpected error occurred';
                        $.facebox(message);
                    }
                }
            });
        });
    },

    /*
    * Delete Scheduling Group
    */
    deleteForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#scheduling-list-table").off('click', '.delete-scheduling-group-icon').on("click", ".delete-scheduling-group-icon", function () {
            let groupId = $(this).attr('data-scheduling-group-id');
            $.facebox.close(); // Close any existing facebox to prevent blank popups
            $.ajax({
                url: that.deleteFormUrl.replace(":schedulingGroup", groupId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                    $('#ignore-delete-scheduling-group-form').hide();
                    that.destroyForm();
                },
                error: function (xhr, status, error) {
                    console.error("Failed to load delete form:", error);
                }
            });
        });
    },
    destroyForm: function () {
        let that = this; // NOSONAR javascript:S7740
        $("#ignore-delete-scheduling-group-form").off('click').on("click", function () {
            location.reload();
        });
        $("#cancel-delete-scheduling-group-form").off('click').on("click", function () {
            $.facebox.close();
        });
        $("#approve-delete-scheduling-group-form").off('click').on("click", function () {
            $(this).prop('disabled', true);
            $('.validation-error-text').html('In progress...');
            let sgroupId = $(this).attr('data-scheduling-group-id');
            $.ajax({
                url: that.deleteUrl.replace(":schedulingGroup", sgroupId),
                method: "POST",
                data: { "_method": "DELETE", "scheduling": sgroupId, '_token': $('#scheduling-group-delete-form input[name=_token]').val() },
                success: function (data) {
                    if (data.success) {
                        $.facebox(data.message);
                        setTimeout(() => { location.reload(); }, 800);
                    } else {
                        // If somehow server sent success=false, show error in modal
                        $('.validation-error-text').html(data.error || 'Unknown error');
                        $('#cancel-delete-scheduling-group-form, #approve-delete-scheduling-group-form').hide();
                        $('#ignore-delete-scheduling-group-form').show();
                    }
                },
                error: function (xhr) {
                    let data = xhr.responseJSON || {};
                    $('.validation-error-text').html('');
                    let errors = data.errors || [data.error];
                    $('.validation-error-text').html(errors.join('<br>'));
                    $('#cancel-delete-scheduling-group-form').hide();
                    $('#approve-delete-scheduling-group-form').hide();
                    $('#ignore-delete-scheduling-group-form').show();
                }
            });
        });
    },
    /**
     * Schedulig Group history
     */
    schedulingGroupHistory: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#scheduling-list-table').off('click', '.history-scheduling-group-icon').on('click', '.history-scheduling-group-icon', function () {
            let groupId = $(this).attr('data-scheduling-group-id');
            $.ajax({
                url: that.schedulingGroupHistoryUrl.replace(':schedulingGroup', groupId),
                method: "GET",
                success: function (data) {
                    $.facebox(data);
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        });
    },
    /**
     * Right-click context menu for Scheduling Group Name column
     */
    contextMenu: function () {
        let that = this; // NOSONAR javascript:S7740
        $('#scheduling-list-table').off('contextmenu', '.scheduling-group-name');
        $('#scheduling-list-table').on('contextmenu', '.scheduling-group-name', function (e) {
            e.preventDefault();
            let groupId = $(this).data('scheduling-group-id');
            $('.context-menu').remove();

            let menu = $('<ul class="context-menu-list context-menu-root"></ul>').css({
                top: e.pageY,
                left: e.pageX,
            });

            $('<li style="cursor: pointer;" class="context-menu-item context-menu-icon context-menu-icon-add"><span>Add New Group</span></li>')
                .on('click', function () {
                    $('#create-scheduling-button').trigger('click');
                    menu.remove();
                })
                .appendTo(menu);

            $('<li style="cursor: pointer;" class="context-menu-item context-menu-icon context-menu-icon-edit"><span>Edit Group Name</span></li>')
                .on('click', function () {
                    $('.edit-scheduling-group-icon[data-scheduling-group-id="' + groupId + '"]').trigger('click');
                    menu.remove();
                })
                .appendTo(menu);

            $('<li style="cursor: pointer;" class="context-menu-item context-menu-icon context-menu-icon-delete context-menu-visible"><span>Delete Group</span></li>')
                .on('click', function () {
                    $('.delete-scheduling-group-icon[data-scheduling-group-id="' + groupId + '"]').trigger('click');
                    menu.remove();
                })
                .appendTo(menu);

            $('body').append(menu);

            $(document).one('click', function () {
                menu.remove();
            });
        });
    }
}
module.exports = SchedulingVar;
