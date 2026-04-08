/**
 * Allocate Users JavaScript
 *
 * @param {Object} p parameters
 */

var AllocateUser = function (p) {
    this.urls = p.urls;
    this.selectedAreaId = null;
    this.availableAreaRoles = [];
    this.additionalAreaRoles = [];
    this._areaPerms = { canModify: 0, canAllocate: 0, isSysAdmin: 0 };
    this._a11y = new window.DatatableAccessibility();
    this._select2Ui = new window.Select2Ui();
    this.init();
};

AllocateUser.prototype = {
    /**
     * Initialisation
     */
    init: function () {
        var that = this;

        // Initialize DataTable numeric type fix
        this.initDataTableNumericFix();

        $(document).off('areaUserAdded.allocateUser').on('areaUserAdded.allocateUser', function (e, areaId, payload) {
            if (payload && payload.users && payload.additionalRoles) {
                that.updateAreaPermissionsTable(
                    payload.users,
                    payload.additionalRoles,
                    payload.canModify,
                    payload.canAllocate,
                    payload.isSysAdmin
                );
            } else if (areaId) {
                that.handleAreaChange(areaId);
            }
        });
        $("#allocate-user-container-tab").tabs({
            activate: function (event, ui) {
                let $tab = ui.newTab;
                let tabType = $tab.attr('data-list-type');
                if (tabType === 'area_permission') {
                    that.getAreaPermissionsTabData();
                }
                if (tabType === "scheduled_staff" || tabType === "non_scheduled_staff") {
                    that.getStaffTabData(tabType);
                }
                if (tabType === 'permission_descriptions') {
                    that.getPermissionDescriptionsTabData();
                }
            }
        });
        that.getAllocateUserTabData();
        that.bindAreaHomeTeamUserChange();
    },
    /**
     * get add allocate user form
     */
    getAddAllocateUserForm: function () {
        const that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            $.ajax({
                url: that.urls.getAddAllocateUserFormUrl,
                method: 'GET',
                beforeSend: function () {
                    $('#loading-spinner').show();
                },
                success: function (response, textStatus, jqXHR) {
                    $('#facebox .popup').removeClass('allocate-user-error-popup');
                    $.facebox(response);
                    $('#facebox .popup').removeClass('allocate-user-error-popup');
                    resolve({ response, textStatus, jqXHR });
                },
                error: function (xhr, status, error) {
                    reject({ xhr, status, error });
                },
                complete: function () {
                    $('#loading-spinner').hide();
                    $('#allocate-users-loading').hide();
                }
            });
        });
    },
    /**
     * Get Allocate User Tab data
     */
    getAllocateUserTabData: function () {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.urls.getAllocateUserTabDataUrl,
            method: 'POST',
            data: {},
            beforeSend: function (jqXHR, settings) {
                $('#allocate_user').empty();
                $('#loading-spinner').show();
            },
            success: function (response) {
                $('#allocate_user').empty().append(response);
                that.initAllocateUserElements();
                $('#loading-spinner').hide();
            },
            error: function (xhr, status, error) {
                $('#loading-spinner').hide();
            },
            complete: function () {
            }
        });
    },
    /**
     * Init allocate user elements
     */
    initAllocateUserElements: function () {
        let that = this; // NOSONAR javascript:S7740
        that.initAllocateUserDataTables();
        $("#js_addallocateuser").on('click', function (e) {
            $('#allocate-users-loading').css('display', 'flex');
            that.getAddAllocateUserForm()
                .then(() => {
                    // Initialize autocomplete for netLogin field
                    $('#allocate-user-add-netlogin-frm').on('keyup', function () {
                        $(this).autocomplete({
                            source: that.urls.getStaffDetailsAutocompleteListUrl + '?termKey=NetLogin',
                            minLength: 2,
                            select: function (event, ui) {
                                $('#allocate-user-add-netlogin-frm').val(ui.item.value);
                            }
                        }).on('mouseup', function () {
                            $(this).select();
                        });
                    });
                    $('#submit-create-allocate-user-add-form').on('click', function (e) {
                        that.createAllocateUsers();
                    });
                })
                .catch((err) => {
                    console.error('Failed:', err);
                });

        });
    },
    /**
     * Create allocate user
     */
    createAllocateUsers: function () {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            type: 'POST',
            url: that.urls.createAllocateUserUrl,
            data: {
                'net_login': $('#allocate-user-add-netlogin-frm').val(),
                'user_exists': 1,
                'active_directory_check': 1
            },
            success: function (data) {
                let response = data;
                if (response.strstatus == 'success') {
                    $.facebox.close();
                    alert(response.strreturnstring);
                    that.getAllocateUserTabData();
                } else {
                    if (response.strstatus == 'usererror') {
                        $.facebox(response.strreturnstring);
                    } else {
                        $('#allocate-user-validation-errors').empty().html(response.strreturnstring);
                    }
                }
            },
            error: function (xhr, status, error) {
                let data = JSON.parse(xhr.responseText);
                let errors = data.errors;
                Object.keys(errors).forEach((key) => {
                    errors[key].forEach((message) => {
                        $div = $('<div class="allocate-user-error-content"></div>');
                        if (key === 'user_exists') {
                            that.openAllocateUserErrorPopup('New Allocate User', 'The Network Login you have entered is already a Allocate User.', [
                                { label: 'The Name of the user is', value: message }
                            ]);
                        } else {
                            const $message = $('<div style="font-weight: bold; border: 1px solid #666; padding: 4px;"></div>');
                            $message.append($('<span class="validation-error-text" style="font-size: 11px;"></span>').text(message));
                            $('#allocate-user-validation-errors').empty().append($message);
                        }
                    });
                });
            }
        });
    },
    /**
     * Render Allocate User style facebox error popup using div-based table rows
     */
    openAllocateUserErrorPopup: function (title, message, detailRows) {
        let $content = $('<div class="allocate-user-error-content"></div>');
        let $table = $('<div class="allocate-user-error-table" role="table" aria-label="Allocate user details"></div>');

        $table.append(
            $('<div class="allocate-user-error-row allocate-user-error-row--header" role="row"></div>')
                .append(
                    $('<div class="allocate-user-error-cell allocate-user-error-cell--header" role="cell"></div>')
                        .text(title)
                )
        );

        $table.append(
            $('<div class="allocate-user-error-row allocate-user-error-row--message" role="row"></div>')
                .append(
                    $('<div class="allocate-user-error-cell allocate-user-error-cell--message validation-error-text" role="cell"></div>')
                        .text(message)
                )
        );

        (detailRows || []).forEach(function (detailRow) {
            let $row = $('<div class="allocate-user-error-row" role="row"></div>');
            $row.append(
                $('<div class="allocate-user-error-cell allocate-user-error-cell--full" role="cell"></div>')
                    .append(
                        $('<div class="allocate-user-error-inline"></div>')
                            .append($('<span class="allocate-user-error-inline-label"></span>').text(detailRow.label))
                            .append($('<span class="allocate-user-error-inline-value"></span>').text(detailRow.value))
                    )
            );
            $table.append($row);
        });

        $.facebox($content.append($table));
        $('#facebox .popup').addClass('allocate-user-error-popup');
        $(document).one('close.facebox', function () {
            $('#facebox .popup').removeClass('allocate-user-error-popup');
        });
    },
    /**
     * Allocate user data table initialization
     */
    initAllocateUserDataTables: function () {
        let that = this; // NOSONAR javascript:S7740
        let filterSetting = [
            {
                column_number: 0,
                filter_type: 'text',
                clear_button_label: 'Clear Full Name filter'
            },
            {
                column_number: 1,
                filter_type: 'text',
                clear_button_label: 'Clear Network ID filter'
            },
            {
                column_number: 3,
                filter_type: 'text',
                clear_button_label: 'Clear Employee Number filter'
            },
            {
                column_number: 4,
                filter_type: 'text',
                clear_button_label: 'Clear Staff Number filter'
            },
            {
                column_number: 5,
                filter_type: 'text',
                clear_button_label: 'Clear Current Home Team filter'
            }
        ];
        let table = $("#alocateUsersLists").DataTable({
            lengthChange: false,
            paging: true,
            iDisplayLength: 50,
            info: false,
            stateSave: true,
            dom: 'rtip',
            pagingType: "simple_numbers",
            language: {
                loadingRecords: "&nbsp;",
                paginate: {
                    previous: "Previous",
                    next: "Next"
                }
            },
            ajax: {
                url: that.urls.getAllocateUsersDataUrl,
                type: 'GET',
                beforeSend: function (xhr) {
                    $('#allocate-users-loading').css('display', 'flex');
                },
                complete: function (xhr, status) {
                    $('#allocate-users-loading').hide();
                }
            },
            columns: [
                { data: 0, title: 'Full Name' },
                { data: 1, title: 'Network ID' },
                { data: 2, title: 'Email address' },
                { data: 3, title: 'Employee Number' },
                { data: 4, title: 'Staff Number' },
                { data: 5, title: 'Current Home Team' },
                {
                    data: 6, title: 'Info', orderable: false,
                    render: function (data, type, row) {
                        return `
                        <a class="js_userinfo" href="javascript:void(0)" aria-label="View user info" data-userid="${row[7]}" data-staffid="${row[4]}" data-first_tab="true">
                            <i class="fa fa-info-circle circle-clr"></i>
                        </a>
                    `;
                    }

                }
            ],
            "columnDefs": [
                { "orderable": false, "targets": 6 }
            ],
            "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $(nRow).attr("id", aData[7]);
                return nRow;
            },
            "initComplete": function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
                that._a11y.addAriaLabelsToFilterClearButtons('alocateUsersLists', filterSetting);
                that._a11y.addAriaLabelledbyToYadcfTextInputs('alocateUsersLists');
                that._a11y.hideYadcfSelectValuesUntilFocus('alocateUsersLists');
            },
            "drawCallback": function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
                $('.js_userinfo').off().on('click', function () {
                    $('#allocate-users-loading').css('display', 'flex');
                    that.openUserInfo($(this).attr('data-userid'), $('#allocate-users-loading'));
                });
            }
        });
        yadcf.init(table, filterSetting);
    },
    /**
     * Open user info modal
     */
    openUserInfo: function (userId, $loader) {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.urls.getUserInfoUrl.replace(':userId', userId),
            method: 'GET',
            beforeSend: function (jqXHR, settings) {
                $('#loading-spinner').show();
            },
            success: function (response) {
                $.facebox(response);
                $('#facebox .popup').addClass('user-info-popup');
                $(document).one('close.facebox', function () {
                    $('#facebox .popup').removeClass('user-info-popup');
                });
            },
            error: function (xhr, status, error) {
            },
            complete: function () {
                $('#loading-spinner').hide();
                if ($loader) $loader.hide();
            }
        });
    },
    /*
    * Get Permission Descriptions Tab data
    */
    getPermissionDescriptionsTabData: function () {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.urls.getPermissionDescriptionsTabDataUrl,
            method: 'POST',
            data: {},
            beforeSend: function (jqXHR, settings) {
                $('#permission_descriptions').empty();
                $('#loading-spinner').show();
            },
            success: function (response) {
                $('#permission_descriptions').empty().append(response);
                that.initPermissionDescriptionsDataTable();
                $('#loading-spinner').hide();
            },
            error: function (xhr, status, error) {
            },
            complete: function () {
                $('#loading-spinner').hide();
            }

        });
    },
    /**
     * Permission descriptions data table initialization
     */
    initPermissionDescriptionsDataTable: function () {
        var that = this;

        // Destroy existing DataTable if re-initialising
        if ($.fn.DataTable.isDataTable('#permissionDescriptionsLists')) {
            $('#permissionDescriptionsLists').DataTable().destroy();
        }

        var table = $('#permissionDescriptionsLists').DataTable({
            lengthChange: false,
            paging: false,
            info: false,
            searching: false,
            ordering: false,
            dom: 'rt',
            initComplete: function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
            },
            drawCallback: function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
            }
        });
    },
    /*
    * Get Area Permissions Tab data
    */
    getAreaPermissionsTabData: function () {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.urls.getAreaPermissionsTabDataUrl,
            method: 'POST',
            data: {},
            beforeSend: function (jqXHR, settings) {
                $('#area_permission').empty();
                $('#loading-spinner').show();
            },
            success: function (response) {
                $('#area_permission').empty().append(response);
                that.initAreaPermissionsDataTables();
                $('#loading-spinner').hide();

                // Initialize select2
                $('#area_chosen').select2({
                    width: '200px',
                    dropdownParent: $('#area_chosen').closest('.area-dropdown-wrap'),
                    placeholder: $('#area_chosen').data('placeholder') || 'Select Area'
                });
                that._select2Ui.applyUiFixes('#area_chosen', { width: '200px' });

                // Restore saved area selection from localStorage (same keys as legacy)
                let savedAreaId   = localStorage.getItem('divisionID');
                let savedAreaName = localStorage.getItem('divisionName');
                if (savedAreaId) {
                    $('#area_chosen').val(savedAreaId).trigger('change.select2');
                    if (savedAreaName) {
                        $('#area_chosen').find('option[value="' + savedAreaId + '"]').text(savedAreaName);
                    }
                    that.handleAreaChange(savedAreaId);
                }

                // Add change event listener
                $('#area_chosen').on('change', function (e, params) {
                    let areaId   = $(this).val();
                    let areaName = $(this).find('option:selected').text();
                    if (areaId) {
                        localStorage.setItem('divisionID', areaId);
                        localStorage.setItem('divisionName', areaName);
                        that.handleAreaChange(areaId);
                    } else {
                        localStorage.removeItem('divisionID');
                        localStorage.removeItem('divisionName');
                        that.handleAreaChange('');
                    }
                });

                // Add click handler for Allocate User button in Area Permissions tab
                $('#area_permission').on('click', '#js_addallocateuser_area_permission', function (e) {
                    e.stopPropagation();
                    that.callAreaPermissionPopUp();
                });
            },
            error: function (xhr, status, error) {
            },
            complete: function () {
            }
        });
    },
    /*
    * Area permissions data table initialization
    */
    initAreaPermissionsDataTables: function () {
        let that = this; // NOSONAR javascript:S7740
        let filterSetting = [
            {
                column_number: 0,
                filter_type: 'text',
                clear_button_label: 'Clear Full Name Filters'
            }
        ]
        let table = $("#areaPermissionsLists").DataTable({
            lengthChange: false,
            paging: true,
            iDisplayLength: 50,
            info: false,
            stateSave: true,
            dom: 'rtip',
            pagingType: "simple_numbers",
            language: {
                paginate: {
                    previous: "Previous",
                    next: "Next"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": 6 }
            ],
            "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $(nRow).attr("id", aData[1]);
                return nRow;
            },
            "initComplete": function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
                that._a11y.addAriaLabelsToFilterClearButtons('areaPermissionsLists', filterSetting);
                that._a11y.addAriaLabelledbyToYadcfTextInputs('areaPermissionsLists');
                that._a11y.hideYadcfSelectValuesUntilFocus('areaPermissionsLists');
            },
            "drawCallback": function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
                // History icon click handler
                $('.js_area_user_history').off().on('click', function (e) {
                    e.preventDefault();
                    let userRoleId = $(this).attr('data-userroleid');
                    that.showAreaUserHistory(userRoleId);
                });
            }
        });
        yadcf.init(table, filterSetting);
    },
    /*
    *Handle area select change event
    */
    handleAreaChange: function (areaId) {
        let that = this; // NOSONAR javascript:S7740
        that.selectedAreaId = areaId;
        if (!areaId || areaId === '') {
            if ($.fn.DataTable.isDataTable("#areaPermissionsLists")) {
                $("#areaPermissionsLists").DataTable().clear().draw();
            }
            // Reset Add button state when no area selected
            let $addBtn = $('#js_addallocateuser_area_permission');
            if ($addBtn.length) {
                $addBtn.addClass('notclickable buttonDisabled grayscale-icon').removeClass('buttonEnabled');
            }
            return;
        }

        let url = that.urls.getAreaUsersUrl.replace(':areaId', areaId);

        $.ajax({
            url: url,
            method: 'POST',
            data: { areaId: areaId },
            beforeSend: function (jqXHR, settings) {
                $('#area-permissions-loading').css('display', 'flex');
                $('#loading-spinner').show();
            },
            success: function (response) {
                that.availableAreaRoles = response.availableAreaRoles || [];
                that.additionalAreaRoles = response.additionalAreaRoles || [];
                that.updateAreaPermissionsTable(
                    response.users,
                    response.additionalRoles,
                    response.canModify,
                    response.canAllocate,
                    response.isSysAdmin
                );
                // Toggle Add button based on per-area canAllocate (legacy handleAddButton)
                let $addBtn = $('#js_addallocateuser_area_permission');
                if ($addBtn.length) {
                    if (response.canAllocate) {
                        $addBtn.removeClass('notclickable buttonDisabled grayscale-icon').addClass('buttonEnabled');
                    } else {
                        $addBtn.addClass('notclickable buttonDisabled grayscale-icon').removeClass('buttonEnabled');
                    }
                }
                $('#area-permissions-loading').hide();
                $('#loading-spinner').hide();
            },
            error: function (xhr, status, error) {
                $('#area-permissions-loading').hide();
                $('#loading-spinner').hide();
            }
        });
    },
    /*
    * Update area permissions table with user data
    */
    updateAreaPermissionsTable: function (users, additionalRoles, canModify, canAllocate, isSysAdmin) {
        let that = this; // NOSONAR javascript:S7740
        // Cache permissions when provided; reuse cached values for table-refresh calls
        if (canModify !== undefined) that._areaPerms.canModify  = canModify;
        if (canAllocate !== undefined) that._areaPerms.canAllocate = canAllocate;
        if (isSysAdmin !== undefined) that._areaPerms.isSysAdmin  = isSysAdmin;
        canModify  = that._areaPerms.canModify;
        isSysAdmin = that._areaPerms.isSysAdmin;

        let disableModify  = canModify  ? '' : 'notclickable buttonDisabled grayscale-icon';
        let disableRemove  = isSysAdmin ? '' : 'notclickable buttonDisabled grayscale-icon';

        // Destroy existing DataTable
        if ($.fn.DataTable.isDataTable("#areaPermissionsLists")) {
            $("#areaPermissionsLists").DataTable().destroy();
        }

        //Table rows
        let tbody = $("#areaPermissionsLists tbody");
        tbody.empty();

        if (users && users.length > 0) {
            users.forEach(function (user) {
                let fullName = user.DisplayName || (user.PreferredForename + ' ' + user.Surname);
                let hasFacilityAdmin = additionalRoles[user.UserID] && additionalRoles[user.UserID]['Facility Administrator'];
                let hasAreaReport = additionalRoles[user.UserID] && additionalRoles[user.UserID]['Area Reports'];

                // Only sysAdmin can change roles (non-sysAdmin sees plain text)
                let roleCell;
                if (isSysAdmin) {
                    let roleOptions = `<option value="${user.RoleID}" selected>${user.RoleName || ''}</option>`;
                    if (that.availableAreaRoles && Array.isArray(that.availableAreaRoles)) {
                        that.availableAreaRoles.forEach(role => {
                            if (role.RoleID != user.RoleID) {
                                roleOptions += `<option value="${role.RoleID}">${role.RoleName}</option>`;
                            }
                        });
                    }
                    // Disable dropdown if user is Area Admin ( $clickdisable)
                    let clickDisable = (user.RoleName === 'Area Admin') ? 'notclickable' : '';
                    roleCell = `<select class="area-admin-role ${clickDisable}" data-user-id="${user.UserID}" data-user-role-id="${user.UserRoleID}" data-old-role-id="${user.RoleID}">${roleOptions}</select>`;
                } else {
                    roleCell = user.RoleName || '';
                }

                // Get role IDs for Facility Administrator and Area Reports from additional roles
                let facilityAdminRoleId = 0;
                let areaReportRoleId = 0;
                if (that.additionalAreaRoles && Array.isArray(that.additionalAreaRoles)) {
                    that.additionalAreaRoles.forEach(role => {
                        let roleName = (role.RoleName || '').toLowerCase().trim();
                        if (roleName === 'facility administrator') {
                            facilityAdminRoleId = role.RoleID;
                        }
                        if (roleName === 'area reports') {
                            areaReportRoleId = role.RoleID;
                        }
                    });
                }

                // Facility Administrator toggle: disabled when the row user IS Area Admin,
                // OR when the logged-in user is not SysAdmin and not Area Admin of this specific area
                let facilityDisable = (user.RoleName === 'Area Admin' || (!isSysAdmin && !that._areaPerms.canAllocate))
                    ? 'notclickable area-readonly-icon' : '';

                let row = `
                    <tr>
                        <td>${fullName}</td>
                        <td>${user.StaffNumber || ''}</td>
                        <td>${user.NetLogin || ''}</td>
                        <td>${roleCell}</td>
                        <td>${hasFacilityAdmin
                        ? '<span class="imgtransparent">1</span><div class="tick areaAdditionalOption crossGreenAreaAdmin ' + facilityDisable + '" data-user-id="' + user.UserID + '" data-division-admin-id="' + user.UserRoleID + '" data-area-additional-role-id="' + facilityAdminRoleId + '" data-action="disable" data-permission="facility"><img src="/images/green_tick.png" alt="Yes" class="tick"></div>'
                        : '<span class="imgtransparent">0</span><div class="tick areaAdditionalOption crossRedAreaAdmin ' + facilityDisable + '" data-user-id="' + user.UserID + '" data-division-admin-id="' + user.UserRoleID + '" data-area-additional-role-id="' + facilityAdminRoleId + '" data-action="enable" data-permission="facility"><img src="/images/red_cross.png" alt="No" class="tick"></div>'}</td>
                        <td>${hasAreaReport
                        ? '<span class="imgtransparent">1</span><div class="tick areaAdditionalOption crossGreenAreaAdmin ' + (disableModify ? 'notclickable area-readonly-icon' : '') + '" data-user-id="' + user.UserID + '" data-division-admin-id="' + user.UserRoleID + '" data-area-additional-role-id="' + areaReportRoleId + '" data-action="disable" data-permission="report"><img src="/images/green_tick.png" alt="Yes" class="tick"></div>'
                        : '<span class="imgtransparent">0</span><div class="tick areaAdditionalOption crossRedAreaAdmin ' + (disableModify ? 'notclickable area-readonly-icon' : '') + '" data-user-id="' + user.UserID + '" data-division-admin-id="' + user.UserRoleID + '" data-area-additional-role-id="' + areaReportRoleId + '" data-action="enable" data-permission="report"><img src="/images/red_cross.png" alt="No" class="tick"></div>'}</td>
                        <td style="text-align: center;">
                            <a href="javascript:void(0)" class="js_area_user_history" data-userroleid="${user.UserRoleID}">
                                <i class="fa fa-hourglass-3"></i>
                            </a>
                        </td>
                        <td><a href="javascript:void(0)" class="remove-user ${disableRemove}" data-userid="${user.UserID}" data-areaid="${user.DivisionId}">Remove</a></td>
                    </tr>
                `;
                tbody.append(row);
            });
        }
        that.initAreaPermissionsDataTables();

        // Bind role dropdown change events
        $('#area_permission').off('change', '.area-admin-role').on('change', '.area-admin-role', function () {
            that.handleAreaAdminRoleChange($(this));
        });

        // Bind remove-user click events
        $('#area_permission').off('click', '.remove-user').on('click', '.remove-user', function (e) {
            e.preventDefault();
            if ($(this).hasClass('notclickable') || $(this).hasClass('buttonDisabled')) {
                return;
            }
            let userId = $(this).data('userid');
            let areaId = $(this).data('areaid');
            that.removeAreaUser(userId, areaId);
        });

        // Bind additional role toggle click events
        $('#area_permission').off('click', '.areaAdditionalOption').on('click', '.areaAdditionalOption', function (e) {
            e.preventDefault();
            that.handleAreaAdditionalRoleToggle($(this));
        });
    },

    /**
     * Handle area additional role toggle (Facility Administrator / Area Reports)
     */
    handleAreaAdditionalRoleToggle: function ($element) {
        let that = this; // NOSONAR javascript:S7740

        // Check if element has disabled class
        if ($element.hasClass('grayscale-icon') || $element.hasClass('buttonDisabled') || $element.hasClass('area-readonly-icon')) {
            return;
        }

        let userRoleId = $element.data('division-admin-id');
        let roleId = $element.data('area-additional-role-id');
        let userId = $element.data('user-id');
        let action = $element.data('action');
        let permission = $element.data('permission');

        if (!userRoleId || !roleId || !userId || !action) {
            console.error('Missing required data attributes for role toggle');
            return;
        }

        $.ajax({
            type: 'POST',
            url: that.urls.toggleAreaAdditionalRoleUrl,
            data: {
                'user_role_id': userRoleId,
                'role_id': roleId,
                'user_id': userId,
                'area_id': that.selectedAreaId,
                'action': action,
                'permission': permission,
                '_token': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                $('#area-permissions-loading').css('display', 'flex');
            },
            success: function (data) {
                if (data.status == 'success') {
                    if (data.users && data.additionalRoles) {
                        that.updateAreaPermissionsTable(data.users, data.additionalRoles);
                    } else {
                        that.handleAreaChange(that.selectedAreaId);
                    }
                } else {
                    alert(data.message || 'Error toggling role');
                    that.handleAreaChange(that.selectedAreaId);
                }
            },
            error: function (xhr, status, error) {
                alert('Error toggling role');
                that.handleAreaChange(that.selectedAreaId);
            },
            complete: function () {
                $('#area-permissions-loading').hide();
            },
        });
    },
    //Show area user history popup
    showAreaUserHistory: function (userRoleId) {
        let that = this; // NOSONAR javascript:S7740

        if (!that.selectedAreaId) {
            $.facebox('<div class="error-message"><p>Please select an area first.</p></div>');
            return;
        }

        let url = that.urls.getAreaUserHistoryUrl
            .replace(':areaId', that.selectedAreaId)
            .replace(':userRoleId', userRoleId);

        $.ajax({
            url: url,
            method: 'GET',
            beforeSend: function (jqXHR, settings) {
                $('#area-permissions-loading').css('display', 'flex');
            },
            success: function (response) {
                $.facebox(response);
            },
            error: function (xhr, status, error) {
                $.facebox('<div class="error-message"><p>Error loading history. Please try again.</p></div>');
            },
            complete: function () {
                $('#area-permissions-loading').hide();
            }
        });
    },
    /**
     * Get add area allocate user form
     */
    getAddAreaAllocateUserForm: function () {
        const that = this; // NOSONAR javascript:S7740
        return new Promise((resolve, reject) => {
            // Check if an area is selected
            if (!that.selectedAreaId || that.selectedAreaId === '') {
                $.facebox('<div class="error-message"><p style="padding: 20px; text-align: center;">Please select an area first before allocating users.</p></div>');
                reject({ error: 'No area selected' });
                return;
            }

            $.ajax({
                url: that.urls.getAddAreaAllocateUserFormUrl,
                method: 'GET',
                success: function (response, textStatus, jqXHR) {
                    $.facebox(response);
                    resolve({ response, textStatus, jqXHR });
                },
                error: function (xhr, status, error) {
                    reject({ xhr, status, error });
                }
            });
        });
    },
    /**
     * Call Area Permission PopUp - Following core PHP CallPopUp() pattern
     */
    callAreaPermissionPopUp: function () {
        let that = this; // NOSONAR javascript:S7740
        let areaId = $('#area_chosen').val();

        if (!areaId || areaId === '' || areaId === '0') {
            alert('Please select any Area from the Area List.');
            return;
        }

        that.selectedAreaId = areaId;

        // Make AJAX call to get the form using GET
        $.ajax({
            url: that.urls.getAddAreaAllocateUserFormUrl,
            method: 'GET',
            data: { area_id: areaId },
            dataType: 'html',
            beforeSend: function (jqXHR, settings) {
                $('#area-permissions-loading').css('display', 'flex');
            },
            success: function (data, status) {
                $.facebox(data);

                // Bind form elements after form is loaded in facebox
                setTimeout(function () {
                    $('#area-allocate-user-netlogin-frm').off().on('change', function () {
                        that.handleAreaUserSelection($(this).val());
                    });
                    $('#submit-area-allocate-user-form').off().on('click', function (e) {
                        e.preventDefault();
                        that.createAreaAllocateUser();
                    });

                    // Initialize select2 for area-hometeam-user select
                    $('#area-hometeam-user').select2({
                        width: '200px',
                        dropdownParent: $('#area-hometeam-user').closest('.form-input-area')
                    });
                    that._select2Ui.applyUiFixes('#area-hometeam-user', { width: '200px' });
                }, 100);
            },
            error: function (xhr, status, error) {
                console.error('Error loading area permission form:', error);
            },
            complete: function () {
                $('#area-permissions-loading').hide();
            }
        });
    },
    /**
     * Handle area user selection
     */
    handleAreaUserSelection: function (selection) {
        if (selection) {
            // The selection value contains both userId and netLogin separated by |
            // We can parse and process as needed
            var parts = selection.split('|');
            var userId = parts[0];
            var netLogin = parts[1];
        }
    },
    /**
     * Create area allocate user
     */
    createAreaAllocateUser: function () {
        let that = this; // NOSONAR javascript:S7740
        let userSelection = $('#area-allocate-user-netlogin-frm').val();
        let roleId = $('#area-allocate-user-role-frm').val();

        if (!userSelection || !roleId) {
            $('#area-allocate-user-validation-errors').empty().html(
                '<div style="background: #eee; text-align: center; border: 1px solid #666; margin:15px; padding: 22px; width: 90%; color: red;">Please select both user and role.</div>'
            );
            return;
        }

        var parts = userSelection.split('|');
        var userId = parts[0];

        $.ajax({
            type: 'POST',
            url: that.urls.createAreaAllocateUserUrl,
            data: {
                'user_id': userId,
                'area_id': that.selectedAreaId,
                'role_id': roleId,
                '_token': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.status == 'success') {
                    $.facebox.close();
                    alert(data.message || 'User allocated to area successfully');
                    // If server returned refreshed users, update table directly
                    if (data.users && data.additionalRoles) {
                        that.updateAreaPermissionsTable(data.users, data.additionalRoles);
                    } else {
                        that.handleAreaChange(that.selectedAreaId);
                    }
                } else {
                    $('#area-allocate-user-validation-errors').empty().html(
                        '<div style="background: #eee; text-align: center; border: 1px solid #666; margin:15px; padding: 22px; width: 90%; color: red;">' + (data.message || 'Error allocating user to area') + '</div>'
                    );
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = 'Error allocating user to area';
                try {
                    let data = JSON.parse(xhr.responseText);
                    if (data.message) {
                        errorMessage = data.message;
                    } else if (data.errors) {
                        Object.keys(data.errors).forEach((key) => {
                            if (Array.isArray(data.errors[key])) {
                                errorMessage = data.errors[key][0];
                            }
                        });
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || error;
                }
                $('#area-allocate-user-validation-errors').empty().html(
                    '<div style="background: #eee; text-align: center; border: 1px solid #666; margin:15px; padding: 22px; width: 90%; color: red;">' + errorMessage + '</div>'
                );
            }
        });
    },
    /**
     * Handle area admin role change
     */
    handleAreaAdminRoleChange: function ($select) {
        let that = this; // NOSONAR javascript:S7740
        let userId = $select.data('user-id');
        let roleId = $select.val();

        $.ajax({
            type: 'POST',
            url: that.urls.updateAreaUserRoleUrl,
            data: {
                'user_id': userId,
                'area_id': that.selectedAreaId,
                'role_id': roleId,
                '_token': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                $('#area-permissions-loading').css('display', 'flex');
            },
            success: function (data) {
                if (data.status == 'success') {
                    if (data.users && data.additionalRoles) {
                        that.updateAreaPermissionsTable(data.users, data.additionalRoles);
                    } else {
                        that.handleAreaChange(that.selectedAreaId);
                    }
                } else {
                    alert(data.message || 'Error updating user role');
                    that.handleAreaChange(that.selectedAreaId);
                }
            },
            error: function (xhr, status, error) {
                alert('Error updating user role');
                that.handleAreaChange(that.selectedAreaId);
            },
            complete: function () {
                $('#area-permissions-loading').hide();
            }
        });
    },
    /**
     * Remove area user
     */
    removeAreaUser: function (userId, areaId) {
        let that = this; // NOSONAR javascript:S7740

        if (!confirm('Are you sure you want to remove this user from the area?')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: that.urls.removeAreaUserUrl,
            data: {
                'user_id': userId,
                'area_id': areaId,
                '_token': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                $('#area-permissions-loading').css('display', 'flex');
            },
            success: function (data) {
                if (data.status == 'success') {
                    if (data.users && data.additionalRoles) {
                        that.updateAreaPermissionsTable(data.users, data.additionalRoles);
                    } else {
                        that.handleAreaChange(areaId);
                    }
                } else {
                    alert(data.message || 'Error removing user from area');
                }
            },
            error: function (xhr, status, error) {
                alert('Error removing user from area');
            },
            complete: function () {
                $('#area-permissions-loading').hide();
            }
        });
    },

    /**
     * Bind handler for #area-hometeam-user change (loads details and binds add button)
     */
    bindAreaHomeTeamUserChange: function () {
        let that = this; // NOSONAR javascript:S7740
        $(document).off('change', '#area-hometeam-user').on('change', '#area-hometeam-user', function () {
            var user_id = this.value;
            var area_id = $('#area_id').val();

            if (user_id == '0' || user_id == '') {
                $('#area-user-details').html('');
                return;
            }

            // Determine URL for fetching user details
            var getUserDetailsUrl = (that.urls && (that.urls.getUserDetailsUrl || that.urls.getAreaUserDetailsUrl)) || that.urls.getAddAreaAllocateUserFormUrl;

            $.ajax({
                type: 'GET',
                url: getUserDetailsUrl,
                data: {
                    user_id: user_id,
                    area_id: area_id
                },
                success: function (data) {
                    if (data && data.userDetails) {
                        // Generate HTML from JSON data using JavaScript
                        let html = that.generateUserDetailsRow(data);
                        $('#area-user-details').html(html);

                        // Bind add button click
                        $('.area-add-user-btn').off().on('click', function (e) {
                            e.preventDefault();
                            let userId = $(this).data('userid');
                            let roleSelect = $(this).closest('tr').find('.role-select');
                            let roleId = roleSelect.val();

                            if (!roleId || roleId === '0') {
                                alert('Please select a role');
                                return;
                            }

                            that.addAreaUser(userId, roleId);
                        });
                    } else if (data && data.html) {
                        $('#area-user-details').html(data.html);

                        // Bind add button click
                        $('.area-add-user-btn').off().on('click', function (e) {
                            e.preventDefault();
                            let userId = $(this).data('userid');
                            let roleSelect = $(this).closest('tr').find('.role-select');
                            let roleId = roleSelect.val();

                            if (!roleId || roleId === '0') {
                                alert('Please select a role');
                                return;
                            }

                            that.addAreaUser(userId, roleId);
                        });
                    } else {
                        $('#area-user-details').html('<tr><td colspan="8" style="text-align:center;color:red;">Error loading user details</td></tr>');
                    }
                },
                error: function (xhr, status, error) {
                    $('#area-user-details').html('<tr><td colspan="8" style="text-align:center;color:red;">Error: ' + error + '</td></tr>');
                }
            });
        });
    },

    /**
     * Generate HTML row for user details in area allocation form
     * @param {Object} data - JSON data containing userDetails, roles, existingMapping, userId
     * @returns {string} HTML string
     */
    generateUserDetailsRow: function (data) {
        let userDetails = data.userDetails || {};
        let roles = data.roles || [];
        let existingMapping = data.existingMapping;
        let userId = data.userId;

        let html = '<tr>';
        html += '<td>' + (userDetails.UD_NetLogin || '') + '</td>';
        html += '<td>' + (userDetails.UD_DisplayName || '') + '</td>';
        html += '<td>' + (userDetails.UD_DisplayFirstName || '') + '</td>';
        html += '<td>' + (userDetails.UD_DisplayLastName || '') + '</td>';
        html += '<td>' + (userDetails.UD_InternalEmail || '') + '</td>';
        html += '<td>' + (userDetails.UD_EmpNumber || '') + '</td>';
        html += '<td>';

        let disabledAttr = existingMapping ? 'disabled' : '';
        html += '<select class="role-select" id="role-' + userId + '" ' + disabledAttr + '>';
        html += '<option value="0">Select Role</option>';

        for (let i = 0; i < roles.length; i++) {
            let role = roles[i];
            let rId = role.RoleID || '';
            let rName = role.RoleName || '';
            // Pre-select the existing role if user is already added to the area
            let selectedAttr = (existingMapping && existingMapping.UR_RoleID == rId) ? 'selected' : '';
            html += '<option value="' + rId + '" ' + selectedAttr + '>' + rName + '</option>';
        }

        html += '</select>';
        html += '</td>';

        if (existingMapping) {
            html += '<td>Added</td>';
        } else {
            html += '<td><button type="button" class="add-button area-add-user-btn" data-userid="' + userId + '">Add</button></td>';
        }

        html += '</tr>';

        return html;
    },

    /**
     * Add area user (AJAX POST)
     */
    addAreaUser: function (userId, roleId) {
        let that = this; // NOSONAR javascript:S7740
        var allocationData = {
            user_id: userId,
            area_id: $('#area_id').val(),
            role_id: roleId,
            '_token': $('meta[name="csrf-token"]').attr('content')
        };

        var postUrl = (that.urls && that.urls.createAreaAllocateUserUrl) || '/admin/allocate-users/create-area-allocate-user';

        $.ajax({
            type: 'POST',
            url: postUrl,
            data: allocationData,
            success: function (data) {
                if (data && data.status == 'success') {
                    alert(data.message || 'User allocated successfully');
                    $.facebox.close();
                    // Trigger a document event to notify the page to refresh the area table
                    $(document).trigger('areaUserAdded', [allocationData.area_id, data]);
                } else {
                    alert((data && data.message) || 'Error allocating user');
                }
            },
            error: function (xhr, status, error) {
                let errorMsg = 'Error allocating user';
                try {
                    let data = JSON.parse(xhr.responseText);
                    if (data.message) errorMsg = data.message;
                } catch (e) { }
                alert(errorMsg);
            }
        });
    },
    /**
     * Show area user history popup
     */
    showAreaUserHistory: function (userRoleId) {
        let that = this; // NOSONAR javascript:S7740

        if (!that.selectedAreaId) {
            $.facebox('<div class="error-message"><p>Please select an area first.</p></div>');
            return;
        }

        let url = that.urls.getAreaUserHistoryUrl
            .replace(':areaId', that.selectedAreaId)
            .replace(':userRoleId', userRoleId);

        $.ajax({
            url: url,
            method: 'GET',
            beforeSend: function (jqXHR, settings) {
                $('#area-permissions-loading').css('display', 'flex');
            },
            success: function (response) {
                $.facebox(response);
            },
            error: function (xhr, status, error) {
                $.facebox('<div class="error-message"><p>Error loading history. Please try again.</p></div>');
            },
            complete: function () {
                $('#area-permissions-loading').hide();
            }
        });
    },

    /**
     * Load and render the staff tab content for scheduled and non-scheduled staff
     */
    getStaffTabData: function (tabType) {
        let that = this; // NOSONAR javascript:S7740
        let isScheduled = tabType === "scheduled_staff";
        let dropdownId = isScheduled ? "#js_schedulled_teamdropdown" : "#js_teamdropdown";
        let tableId = isScheduled ? "#js_scheduledstaffteamlist" : "#js_nonscheduledstaffteamlist";
        let userType = isScheduled ? 1 : 0;
        let loadingSpinnerId = isScheduled ? "#scheduled-staff-loading" : "#non-scheduled-staff-loading";

        $.ajax({
            url: that.urls.tabUrl,
            method: "POST",
            data: { tabType: tabType },
            beforeSend: function () {
                $("#" + tabType).empty();
                $("#loading-spinner").show();
            },
            success: function (response) {
                $("#" + tabType).empty().append(response);
                // Show inline loading spinner while initializing table
                $(loadingSpinnerId).show();

                // Initialize table (empty at this point)
                that.initStaffDataTable(tableId);

                $("#loading-spinner").hide();
                // Hide inline spinner after table initialization, data will load separately
                $(loadingSpinnerId).hide();

                $(dropdownId).select2({
                    width: '205px',
                    dropdownParent: $(dropdownId).closest('.fields')
                });
                that._select2Ui.applyUiFixes(dropdownId, { width: '205px' });

                $(dropdownId).on("change", function () {
                    let teamId = $(this).val();
                    let teamName = $(this).find("option:selected").text();
                    let divisionId = $(this).find("option:selected").data("divisionid");

                    // Save selected team to localStorage (matching old behavior)
                    if (isScheduled) {
                        localStorage.setItem('teamID', teamId);
                        localStorage.setItem('teamName', teamName);
                    } else {
                        localStorage.setItem('NSteamID', teamId);
                        localStorage.setItem('NSteamName', teamName);
                    }

                    that.handleStaffTeamChange(teamId, divisionId, userType, tableId);

                    // Enable/disable add button for non-scheduled staff
                    if (!isScheduled) {
                        if (teamId) {
                            $("#js_addnonscheduledstaff").removeClass('grayscale-icon buttonDisabled').addClass('buttonEnabled');
                        } else {
                            $("#js_addnonscheduledstaff").addClass('grayscale-icon buttonDisabled').removeClass('buttonEnabled');
                        }
                    }
                });

                // Restore previously selected team from localStorage (matching old behavior)
                let savedTeamId = isScheduled ? localStorage.getItem('teamID') : localStorage.getItem('NSteamID');
                let savedTeamName = isScheduled ? localStorage.getItem('teamName') : localStorage.getItem('NSteamName');
                if (savedTeamId) {
                    $(dropdownId).val(savedTeamId);
                    $(dropdownId).trigger('change.select2');

                    // Trigger change to load data
                    let divisionId = $(dropdownId).find("option:selected").data("divisionid");
                    that.handleStaffTeamChange(savedTeamId, divisionId, userType, tableId);

                    // Enable add button for non-scheduled if team is selected
                    if (!isScheduled && savedTeamId) {
                        $("#js_addnonscheduledstaff").removeClass('grayscale-icon buttonDisabled').addClass('buttonEnabled');
                    }
                }

                // Add non-scheduled staff button click - use direct binding after element exists
                if (!isScheduled) {
                    // Show/hide add button based on server-side flag (SysAdmin or Area Admin)
                    if ($('#js_show_add').val() != '1') {
                        $('#js_addnonscheduledstaff').hide();
                    }
                    $("#js_addnonscheduledstaff").off('click').on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if ($(this).hasClass('grayscale-icon')) {
                            return false;
                        }
                        let teamId = $(dropdownId).val();
                        if (teamId) {
                            that.openAddStaffPopup(teamId);
                        }
                    });
                }
            },
            error: function () { },
        });
    },

    /**
     * Open add non-scheduled staff popup
     */
    openAddStaffPopup: function (teamId) {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            url: that.urls.getAddNonScheduledStaffFormUrl,
            method: 'GET',
            data: { teamid: teamId },
            beforeSend: function () {
                $('#non-scheduled-staff-loading').css('display', 'flex');
            },
            success: function (data) {
                $('#facebox .popup').removeClass('allocate-user-error-popup');
                $.facebox(data);
                $('#facebox .popup').removeClass('allocate-user-error-popup');

                setTimeout(function () {
                    $('#js_netLogin').on('keyup', function () {
                        $(this).autocomplete({
                            source: that.urls.getStaffDetailsAutocompleteListUrl + '?termKey=NetLogin',
                            minLength: 2,
                            select: function (event, ui) {
                                $('#js_netLogin').val(ui.item.value);
                            }
                        }).on('mouseup', function () {
                            $(this).select();
                        });
                    });

                    $('#js_nonschedteamstaff').off('click').on('click', function (e) {
                        e.preventDefault();
                        that.addNonScheduledStaff(teamId);
                    });
                }, 100);
            },
            complete: function () {
                $('#non-scheduled-staff-loading').hide();
            }
        });
    },

    /**
     * Add non-scheduled staff to team
     */
    addNonScheduledStaff: function (teamId) {
        let that = this; // NOSONAR javascript:S7740
        let netLogin = $('#js_netLogin').val();

        if (!netLogin) {
            alert('Please enter a network login');
            return;
        }

        $.ajax({
            type: 'POST',
            url: that.urls.addNonScheduledStaffUrl,
            dataType: 'json',
            data: {
                netLogin: netLogin,
                teamid: teamId
            },
            beforeSend: function () {
                $('#non-scheduled-staff-loading').css('display', 'flex');
            },
            success: function (data) {
                if (data.status == 'success') {
                    $.facebox.close();
                    alert(data.view);
                    that.reloadStaffDataTables(teamId).always(function () {
                        $('#non-scheduled-staff-loading').hide();
                    });
                } else if (data.status == 'usererror') {
                    $('#non-scheduled-staff-loading').hide();
                    let detailRows = [];
                    if (data.userName) {
                        detailRows.push({ label: 'The Name of the user is', value: data.userName });
                    }
                    if (data.teamName) {
                        detailRows.push({ label: 'Team Name', value: data.teamName });
                    }
                    that.openAllocateUserErrorPopup('Add Non Scheduled Staff', data.message, detailRows);
                } else {
                    $('#non-scheduled-staff-loading').hide();
                    alert(data.view);
                }
            },
            error: function (xhr, status, error) {
                $('#non-scheduled-staff-loading').hide();
                alert('Error adding staff member');
            }
        });
    },

    /**
     * Initialize staff data table for scheduled and non-scheduled staff
     */
    initStaffDataTable: function (tableId) {
        let that = this; // NOSONAR javascript:S7740
        let tableIdClean = tableId.replace('#', '');
        let filterSetting = [
            { column_number: 0, filter_type: 'text', clear_button_label: 'Clear Full Name filter' },
            { column_number: 1, filter_type: 'text', clear_button_label: 'Clear Staff Number filter' },
            { column_number: 2, filter_type: 'text', clear_button_label: 'Clear Network ID filter' },
        ];
        $(".scheduledNonSchstaffteamlist").height($(window).height() - 270);
        $("#adminuserstabs").height($(window).height() - 100);

        let table = $(tableId).DataTable({
            lengthChange: false,
            paging: true,
            pageLength: 50,
            info: false,
            stateSave: true,
            dom: "rtip",
            pagingType: "simple_numbers",
            language: {
                paginate: {
                    previous: "Previous",
                    next: "Next"
                }
            },
            fnDrawCallback: function () {
                $(".sorting, .sorting_asc").css("min-width", "128px");
            },
            initComplete: function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
                that._a11y.normalizeSortButtonLabels(table, [0, 1, 2]);
                that._a11y.addAriaLabelsToFilterClearButtons(tableIdClean, filterSetting);
                that._a11y.addAriaLabelledbyToYadcfTextInputs(tableIdClean);
                that._a11y.hideYadcfSelectValuesUntilFocus(tableIdClean);
            },
            drawCallback: function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
                that._a11y.normalizeSortButtonLabels(table, [0, 1, 2]);
                $(".js_userdatainfo")
                    .off()
                    .on("click", function () {
                        that.openUserInfo($(this).attr("data-userid"));
                    });
            },
        });

        yadcf.init(table, filterSetting);

        return table;
    },

    /**
     * Handle staff team change event for scheduled and non-scheduled staff
     */
    handleStaffTeamChange: function (teamId, divisionId, userType, tableId) {
        let that = this; // NOSONAR javascript:S7740
        let isScheduled = userType === 1;
        let loadingSpinnerId = isScheduled ? "#scheduled-staff-loading" : "#non-scheduled-staff-loading";

        if (!teamId || teamId === "") {
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().clear().draw();
            }
            return;
        }

        $.ajax({
            url: that.urls.searchStaffTeamUrl,
            method: "POST",
            data: {
                selectedTeamID: teamId,
                usertype: userType,
                divisionid: divisionId,
            },
            beforeSend: function () {
                $(loadingSpinnerId).css('display', 'flex');
                $("#loading-spinner").show();
            },
            success: function (response) {
                that.updateStaffTable(response.data, tableId);
                $(loadingSpinnerId).hide();
                $("#loading-spinner").hide();
            },
            error: function () {
                $(loadingSpinnerId).hide();
                $("#loading-spinner").hide();
            },
        });
    },

    /**
     * Update staff table with data for scheduled and non-scheduled staff
     */
    updateStaffTable: function (data, tableId) {
        let that = this; // NOSONAR javascript:S7740

        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }

        let tbody = $(tableId + " tbody");
        tbody.empty();
        if (data.error) {
            that.initStaffDataTableWithData(tableId);
            return;
        }
        if (data.staff && data.staff.length > 0) {
            that.renderStaffRows(tbody, data);
        }

        that.initStaffDataTableWithData(tableId);
        that.initStaffClickHandlers();
        that.roleBasedAccess(tableId, data);
    },
    /**
     * Hide column and 
     * 
     * @param {*} tableId 
     * @param {*} data 
     */
    roleBasedAccess: function (tableId, data) {
        var columnIndex = -1;
        // Iterate over the header cells to find the index
        $('#js_nonscheduledstaffteamlist thead th').each(function (index) {
            if ($(this).text() == 'Action') {
                columnIndex = index;
                return false; // Break the loop once the column is found
            }
        });
        if (columnIndex !== -1) {
            $('#js_nonscheduledstaffteamlist').DataTable().column(columnIndex).visible(true);
        }
        //Update delete and close button access
        $('#js_addnonscheduledstaff').css("visibility", "visible");
        if (tableId == '#js_nonscheduledstaffteamlist') {
            data.permissions.canmodify == 0 ? $('#js_addnonscheduledstaff').css("visibility", "hidden") : '';
            if (data.permissions.candelete == 0) {
                if (columnIndex !== -1) {
                    $('#js_nonscheduledstaffteamlist').DataTable().column(columnIndex).visible(false);
                }
            }
        }
    },
    /**
     * Initialize staff data table with data for scheduled and non-scheduled staff
     */
    initStaffDataTableWithData: function (tableId) {
        let that = this; // NOSONAR javascript:S7740
        let tablename = tableId.replace('#', '');
        let filterSetting = [
            { column_number: 0, filter_type: 'text', clear_button_label: 'Clear Full Name filter' },
            { column_number: 1, filter_type: 'text', clear_button_label: 'Clear Staff Number filter' },
            { column_number: 2, filter_type: 'text', clear_button_label: 'Clear Network ID filter' },
        ];

        $('.scheduledNonSchstaffteamlist').height($(window).height() - 270);
        $('#adminuserstabs').height($(window).height() - 100);

        // Use same configuration as initStaffDataTable - without scrollX/scrollY
        let table = $(tableId).DataTable({
            lengthChange: false,
            paging: true,
            pageLength: 50,
            info: false,
            stateSave: true,
            dom: 'rtip',
            pagingType: "simple_numbers",
            language: {
                paginate: {
                    previous: "Previous",
                    next: "Next"
                }
            },
            fnDrawCallback: function () {
                $('.sorting, .sorting_asc').css('min-width', '128px');
            },
            initComplete: function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
                that._a11y.normalizeSortButtonLabels(table, [0, 1, 2]);
                that._a11y.addAriaLabelsToFilterClearButtons(tablename, filterSetting);
                that._a11y.addAriaLabelledbyToYadcfTextInputs(tablename);
                that._a11y.hideYadcfSelectValuesUntilFocus(tablename);
            },
            drawCallback: function () {
                that._a11y.applyHeaderAccessibilityFixes(table);
                that._a11y.normalizeSortButtonLabels(table, [0, 1, 2]);
            },
        });

        // Initialize yadcf with same config as initial load
        yadcf.init(table, filterSetting);

        // Check where filters were placed
        let filterRow = $(tableId + ' thead tr.yadcf-filter-wrapper');
    },

    /**
     * Render staff rows for scheduled and non-scheduled staff table
     */
    renderStaffRows: function (tbody, data) {
        let that = this; // NOSONAR javascript:S7740
        const { staff, mainRoles, additionalRoles, schedulingTeamId, permissions, hideSchedulingTeamOption, currentNetLogin, usertype } = data;
        const disabled = permissions.canmodify == 0 ? 'grayscale-icon' : '';
        const delsactiveclass = permissions.candelete == 0 ? 'activeclass' : '';

        $.each(staff, function (index, user) {
            let row = '<tr>';
            row += `<td>${user.userDisplayName}</td>`;
            row += `<td>${user.StaffNumber}</td>`;
            row += `<td>${user.NetLogin}</td>`;
            row += that.renderRoleDropdown(user, usertype, mainRoles, schedulingTeamId, disabled, hideSchedulingTeamOption, currentNetLogin);
            row += that.renderDefaultToggle(user, schedulingTeamId, disabled);
            row += `<td style="text-align: center;"><div class="tick staffcrossGreen ${disabled}"></div></td>`;
            row += that.renderAdditionalRoles(user, additionalRoles, schedulingTeamId, disabled, usertype);
            row += that.renderActions(user, usertype, schedulingTeamId, delsactiveclass, disabled);
            row += '</tr>';
            tbody.append(row);
        });
    },

    /**
     * Render role dropdown for scheduled and non-scheduled staff
     */
    renderRoleDropdown: function (user, usertype, mainRoles, schedulingTeamId, disabled, hideSchedulingTeamOption, currentNetLogin) {
        const selectedvalue = usertype == 0 ? (user.NonAdditionalRoleID ?? 0) : (user.rota ?? 0);
        const dropdownClass = usertype == 0 ? 'js_mainrole' : 'js_rota';
        const extraClass = usertype == 1 ? 'mWH60' : '';

        let html = `<td><div id="unsetRotadiv" data_order_${selectedvalue}>`;
        html += `<select class="rota-btn ${dropdownClass} ${extraClass} ${disabled}" name="hometeam" id="mt-0" data-setparam="1" data-teamid="${schedulingTeamId}" data-userid="${user.UserID}" data-schedulepersonid="${user.UserID}">`;

        if (usertype == 0) {
            $.each(mainRoles, function (i, role) {
                const selected = role.RoleID == selectedvalue ? 'selected' : '';
                const isDisabled = hideSchedulingTeamOption && role.RoleID == 3 && currentNetLogin !== (user.NetLogin || '').toLowerCase() ? 'disabled' : '';
                html += `<option value="${role.RoleID}" ${selected} ${isDisabled}>${role.RoleName}</option>`;
            });
        } else {
            const rotaOptions = { 0: 'Show', 1: 'Hide', 2: 'Leave' };
            $.each(rotaOptions, function (key, value) {
                const selected = key == selectedvalue ? 'selected' : '';
                html += `<option value="${key}" ${selected}>${value}</option>`;
            });
        }

        html += '</select></div></td>';
        return html;
    },

    /**
     * Render default toggle for non-scheduled staff
     */
    renderDefaultToggle: function (user, schedulingTeamId, disabled) {
        const isDefault = user.isDefault == 1;
        const cssClass = isDefault ? 'staffcrossGreen' : 'staffcrossRed';
        const dataDefault = isDefault ? '0' : '1';
        const orderValue = isDefault ? '1' : '0';

        return `<td style="text-align: center;"><span class="imgtransparent">${orderValue}</span><div class="tick js_setdefaultteam ${cssClass} ${disabled}" data-default="${dataDefault}" data-schedulepersonid="${user.UserID}" data-teamid="${schedulingTeamId}"></div></td>`;
    },

    /**
     * Render additional roles for scheduled and non-scheduled staff
     */
    renderAdditionalRoles: function (user, additionalRoles, schedulingTeamId, disabled, usertype) {
        let html = '';
        $.each(additionalRoles, function (i, role) {
            const roleValue = user[role.RoleName];
            const hasRole = !(roleValue == 0 || roleValue == null);
            let permissionStatus = user[role.RoleName + '_PermissionStatus'];

            let cssClass, setparam, orderValue, disabledClass, isConditional = false;

            // STV (non-scheduled) + Facility Booker: always conditional, disable if no Shift Leader
            if (usertype == 0 && role.RoleName === 'Facility Booker' && permissionStatus !== 'MANDATORY') {
                const hasShiftLeader = !(user['Shift Leader'] == 0 || user['Shift Leader'] == null);
                isConditional = true;
                if (!hasShiftLeader) {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
                }
            // Scheduled Person + Facility Booker: always conditional, disable if no Shift Leader AND no Team Leader
            } else if (usertype == 1 && role.RoleName === 'Facility Booker' && permissionStatus !== 'MANDATORY') {
                const hasShiftLeader = !(user['Shift Leader'] == 0 || user['Shift Leader'] == null);
                const hasTeamLeader  = !(user['Team Leader']  == 0 || user['Team Leader']  == null);
                isConditional = true;
                if (!hasShiftLeader && !hasTeamLeader) {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
                }
            // Scheduled Person + Edit All Allocations: MANDATORY (greyed green) if Team Leader, else NA (greyed red)
            } else if (usertype == 1 && role.RoleName === 'Edit All Allocations') {
                const hasTeamLeader = !(user['Team Leader'] == 0 || user['Team Leader'] == null);
                isConditional = true;
                if (hasTeamLeader) {
                    cssClass = 'staffcrossGreen'; setparam = '0'; orderValue = '1'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                }
            // Scheduled Person + Edit Master Duties: OPTIONAL if Team Leader, else NA (greyed red)
            } else if (usertype == 1 && role.RoleName === 'Edit Master Duties') {
                const hasTeamLeader = !(user['Team Leader'] == 0 || user['Team Leader'] == null);
                isConditional = true;
                if (!hasTeamLeader) {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
                }
            // Scheduled Person + Edit Rota Patterns: OPTIONAL if Team Leader, else NA (greyed red)
            } else if (usertype == 1 && role.RoleName === 'Edit Rota Patterns') {
                const hasTeamLeader = !(user['Team Leader'] == 0 || user['Team Leader'] == null);
                isConditional = true;
                if (!hasTeamLeader) {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
                }
            // Scheduled Person + Edit Leave Credits: OPTIONAL if Team Leader, else NA (greyed red)
            } else if (usertype == 1 && role.RoleName === 'Edit Leave Credits') {
                const hasTeamLeader = !(user['Team Leader'] == 0 || user['Team Leader'] == null);
                isConditional = true;
                if (!hasTeamLeader) {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
                }
            // Scheduled Person + Move Person Between Teams: OPTIONAL if Team Leader, else NA (greyed red)
            } else if (usertype == 1 && role.RoleName === 'Move Person Between Teams') {
                const hasTeamLeader = !(user['Team Leader'] == 0 || user['Team Leader'] == null);
                isConditional = true;
                if (!hasTeamLeader) {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
                }
            // Scheduled Person + Create New Freelancer: OPTIONAL if Team Leader, else NA (greyed red)
            } else if (usertype == 1 && role.RoleName === 'Create New Freelancer') {
                const hasTeamLeader = !(user['Team Leader'] == 0 || user['Team Leader'] == null);
                isConditional = true;
                if (!hasTeamLeader) {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
                }
            // Scheduled Person + Create New Staff: OPTIONAL if Team Leader, else NA (greyed red)
            } else if (usertype == 1 && role.RoleName === 'Create New Staff') {
                const hasTeamLeader = !(user['Team Leader'] == 0 || user['Team Leader'] == null);
                isConditional = true;
                if (!hasTeamLeader) {
                    cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
                } else {
                    cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
                }
            } else if (permissionStatus === 'MANDATORY') {
                cssClass = 'staffcrossGreen'; setparam = '0'; orderValue = '1'; disabledClass = 'grayscale-icon buttonDisabled';
            } else if (permissionStatus === 'NA') {
                cssClass = 'staffcrossRed'; setparam = '1'; orderValue = '0'; disabledClass = 'grayscale-icon buttonDisabled';
            } else if (permissionStatus === 'CONDITIONAL') {
                isConditional = true;
                cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
            } else {
                cssClass = hasRole ? 'staffcrossGreen' : 'staffcrossRed'; setparam = hasRole ? '0' : '1'; orderValue = hasRole ? '1' : '0'; disabledClass = disabled;
            }

            const icon = `<div class="tick js_additionalpermissions ${cssClass} ${disabledClass}" style="top: 0;" data-setparam="${setparam}" data-roleid="${role.RoleID}" data-userid="${user.UserID}" data-teamid="${schedulingTeamId}"></div>`;
            html += `<td style="text-align: center;"><span class="imgtransparent">${orderValue}</span>${isConditional ? `<span class="bracket-container">${icon}</span>` : icon}</td>`;
        });
        return html;
    },

    /**
     * Render actions column for scheduled and non-scheduled staff
     */
    renderActions: function (user, usertype, schedulingTeamId, delsactiveclass, disabled) {
        let html = '';

        if (usertype == 1) {
            const isHomeTeam = user.IsHomeTeam == 1;
            const cssClass = isHomeTeam ? 'staffcrossGreen' : 'staffcrossRed';
            const dataDefault = isHomeTeam ? '0' : '1';
            const orderValue = isHomeTeam ? '1' : '0';
            html += `<td style="text-align: center;"><span class="imgtransparent">${orderValue}</span><div class="tick ${cssClass} ${disabled}" data-default="${dataDefault}" data-schedulepersonid="${user.UserID}" data-teamid="${schedulingTeamId}" id="js_hometeam"></div></td>`;
        }

        html += `<td style="text-align: center;"><a href="javascript:void(0)" class="js_userdatainfo" data-staffid="${user.StaffID}" data-netlogin="${user.NetLogin}" data-userid="${user.UserID}"><i class="fa fa-info-circle" id="circle-clr"></i></a>&nbsp;&nbsp;&nbsp;`;
        html += `<a href="javascript:void(0)" id="js_nonscheduledhistory" data-userid="${user.UserID}" data-teamid="${schedulingTeamId}"><i class="fa fa-hourglass-3" id="circle-clr"></i></a></td>`;

        if (usertype == 0) {
            html += `<td style="text-align: center;"><a href="javascript:void(0)" class="anchor-colour js_removeStaff ${delsactiveclass}" data-teamid="${schedulingTeamId}" data-schedulepersonid="${user.UserID}">Remove</a></td>`;
        }

        return html;
    },

    /**
     * Initialize click handlers for scheduled and non-scheduled staff team elements
     */
    initStaffClickHandlers: function () {
        let that = this; // NOSONAR javascript:S7740
        var green_path_class = 'staffcrossGreen';
        var red_path_class = 'staffcrossRed';

        // Set default team click handler
        $(document).on('click', ".js_setdefaultteam", function (event) {
            // Check if element is disabled
            if ($(this).hasClass('grayscale-icon') || $(this).hasClass('buttonDisabled')) {
                return false;
            }

            event.stopImmediatePropagation();
            event.preventDefault();
            $(this).unbind('click');

            let scheduledteamid = $(this).data('teamid');
            let defaultValue = $(this).data('default');
            let schedulepersonid = $(this).data('schedulepersonid');

            that.setDefaultTeam(scheduledteamid, schedulepersonid, defaultValue);

            if (defaultValue == 1) {
                $(this).removeClass(red_path_class).addClass(green_path_class);
                $(this).prev().text(1);
                $(this).data('default', 0);
            } else {
                $(this).removeClass(green_path_class).addClass(red_path_class);
                $(this).prev().text(0);
                $(this).data('default', 1);
            }

            setTimeout(function () {
                that.reloadStaffDataTables(scheduledteamid);
            }, 1000);
        });

        // Main role change handler
        $(document).on('change', '.js_mainrole', function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            $(this).unbind('click');

            let action = 'rolepermission';
            let teamID = $(this).data('teamid');
            let schedulepersonId = $(this).data('schedulepersonid');
            let userID = $(this).data('userid');
            let setparam = $(this).data('setparam');
            let roleID = $(this).val();
            let teamsname = $("#js_teamdropdown option:selected").text();

            $('#non-scheduled-staff-loading').css('display', 'flex');
            that.setAdditionalPermission(action, teamID, userID, roleID, setparam, schedulepersonId, teamsname)
                .always(function () {
                    that.reloadStaffDataTables(teamID, 'js_nonscheduledstaffteamlist', '0').always(function () {
                        $('#non-scheduled-staff-loading').hide();
                    });
                });
        });

        // Rota change handler
        $(document).on('change', '.js_rota', function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            $(this).unbind('click');

            let action = 'rotapermission';
            let teamID = $(this).data('teamid');
            let schedulepersonId = $(this).data('schedulepersonid');
            let userID = $(this).data('userid');
            let roleID = parseInt($(this).val());
            let setparam = $(this).data('setparam');
            let teamsname = $("#js_schedulled_teamdropdown option:selected").text();

            $('#scheduled-staff-loading').css('display', 'flex');
            that.setAdditionalPermission(action, teamID, userID, roleID, setparam, schedulepersonId, teamsname)
                .always(function () {
                    that.reloadStaffDataTables(teamID, 'js_scheduledstaffteamlist', '1').always(function () {
                        $('#scheduled-staff-loading').hide();
                    });
                });
        });

        // Additional permissions click handler
        $(document).on('click', ".js_additionalpermissions", function (event) {
            // Check if element is disabled
            if ($(this).hasClass('grayscale-icon') || $(this).hasClass('buttonDisabled')) {
                return false;
            }

            event.stopImmediatePropagation();
            event.preventDefault();
            $(this).unbind('click');

            let action = 'additionalpermission';
            let teamID = $(this).data('teamid');
            let userID = $(this).data('userid');
            let roleID = $(this).data('roleid');
            let setparam = $(this).data('setparam');
            let isScheduled = $(this).closest('#scheduled_staffs').length > 0;
            let teamsname = isScheduled ? $("#js_schedulled_teamdropdown option:selected").text() : $("#js_teamdropdown option:selected").text();
            let $loader = isScheduled ? $('#scheduled-staff-loading') : $('#non-scheduled-staff-loading');
            let tablename = isScheduled ? 'js_scheduledstaffteamlist' : 'js_nonscheduledstaffteamlist';
            let usertype = isScheduled ? '1' : '0';

            $loader.css('display', 'flex');
            that.setAdditionalPermission(action, teamID, userID, roleID, setparam, null, teamsname)
                .always(function () {
                    that.reloadStaffDataTables(teamID, tablename, usertype).always(function () {
                        $loader.hide();
                    });
                });
        });

        // User info click handler
        $(document).on('click', '.js_userdatainfo', function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();
            let $loader = $(this).closest('#scheduled_staffs').length ? $('#scheduled-staff-loading') : $('#non-scheduled-staff-loading');
            $loader.css('display', 'flex');
            let userid = $(this).data('userid');
            that.openUserInfo(userid, $loader);
        });

        // History click handler
        $(document).on('click', '#js_nonscheduledhistory', function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            let $loader = $(this).closest('#scheduled_staffs').length ? $('#scheduled-staff-loading') : $('#non-scheduled-staff-loading');
            $loader.css('display', 'flex');
            let scheduledteamid = $(this).data('teamid');
            let userID = $(this).data('userid');
            that.showStaffHistory(scheduledteamid, userID, $loader);
        });

        // Remove staff click handler
        $(document).on('click', '.js_removeStaff', function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            $(this).unbind('click');
            let scheduledteamid = $(this).data('teamid');
            let schedulepersonid = $(this).data('schedulepersonid');
            $('#non-scheduled-staff-loading').css('display', 'flex');
            that.removeStaff(scheduledteamid, schedulepersonid);
        });
    },

    /**
     * Show staff history for scheduled and non-scheduled staff
     */
    showStaffHistory: function (scheduledteamid, schedulepersonid, $loader) {
        $.ajax({
            url: this.urls.getStaffHistoryUrl,
            type: 'GET',
            data: {
                teamId: scheduledteamid,
                userId: schedulepersonid
            },
            beforeSend: function () {
                $('#loading-spinner').show();
            },
            success: function (data) {
                $.facebox(data);
            },
            complete: function () {
                $('#loading-spinner').hide();
                if ($loader) $loader.hide();
            },
            error: function () {
                console.error('Unable to load staff history');
            }
        });
    },
    /**
     * Remove staff from non-scheduled team
     */
    removeStaff: function (scheduledteamid, schedulepersonid) {
        let that = this; // NOSONAR javascript:S7740
        $.ajax({
            type: 'POST',
            url: that.urls.removeStaffUrl,
            dataType: "json",
            data: {
                scheduledteamid: scheduledteamid,
                schedulepersonid: schedulepersonid
            },
            success: function (data) {
                if (data.status == 'success') {
                    that.reloadStaffDataTables(scheduledteamid).always(function () {
                        $('#non-scheduled-staff-loading').hide();
                    });
                } else {
                    $('#non-scheduled-staff-loading').hide();
                    alert(data.view);
                }
            },
            error: function () {
                $('#non-scheduled-staff-loading').hide();
            }
        });
    },

    /**
     * Set default team for non-scheduled staff via AJAX
     */
    setDefaultTeam: function (teamID, schedulepersonid, defaultValue) {
        let that = this; // NOSONAR javascript:S7740
        return $.ajax({
            type: 'POST',
            url: that.urls.setDefaultTeamUrl,
            dataType: "json",
            data: {
                defaultValue: defaultValue,
                scheduledteamid: teamID,
                schedulepersonid: schedulepersonid
            },
            beforeSend: function (jqXHR, settings) {
                $('#loading-spinner').show();
            },
            success: function (data) {
                if (data.strstatus != 'success') {
                    alert(data.strreturnstring);
                }
            },
            complete: function () {
                $('#loading-spinner').hide();
            }
        });
    },

    /**
     * Set additional permission for scheduled and non-scheduled staff via AJAX
     */
    setAdditionalPermission: function (action, teamID, userID, roleID, setparam, schedulepersonId, teamsname) {
        return $.ajax({
            url: this.urls.setUsersPermissionsUrl,
            type: "POST",
            dataType: "json",
            global: false,
            data: {
                action: action,
                teamID: teamID,
                userID: userID,
                roleID: roleID,
                setparam: setparam,
                schedulepersonId: schedulepersonId,
                teamsname: teamsname
            },
            beforeSend: function (jqXHR, settings) {
                $('#loading-spinner').show();
            },
            success: function (data) {
                if (data.strstatus == 'success') {
                    // Permission updated successfully
                }
            },
            complete: function () {
                $('#loading-spinner').hide();
            }
        });
    },

    /**
     * Reload staff data tables for scheduled and non-scheduled staff
     */
    reloadStaffDataTables: function (teamID, tablename, usertype) {
        let that = this; // NOSONAR javascript:S7740
        tablename = tablename || $("#js_tablename").val();
        usertype  = usertype  || $("#js_usertype").val();

        // Determine which dropdown to use based on table name
        let dropdownId = tablename === 'js_scheduledstaffteamlist' ? '#js_schedulled_teamdropdown' : '#js_teamdropdown';
        let divisionid = $(dropdownId + " option:selected").data('divisionid');

        return $.ajax({
            type: 'POST',
            dataType: "json",
            url: that.urls.searchStaffTeamUrl,
            data: {
                selectedTeamID: teamID,
                usertype: usertype,
                divisionid: divisionid
            },
            success: function (data) {
                let tableId = "#" + tablename;
                that.updateStaffTable(data.data, tableId);
                $('#loading').hide();
            }
        });
    },

    /**
     * Initialize DataTable numeric type fix
     */
    initDataTableNumericFix: function () {
        var that = this;

        function forceStringTypes(settings) {
            if (!settings || !settings.aoColumns) return;

            // Force all columns to string type if they have dt-type-numeric class
            for (var i = 0; i < settings.aoColumns.length; i++) {
                var col = settings.aoColumns[i];
                if (!col) continue;

                // Check if this column has dt-type-numeric class
                var $th = $(settings.nTable).find('thead th').eq(i);
                if ($th.hasClass('dt-type-numeric')) {
                    col.sType = 'string';
                    col._sManualType = 'string';
                }
            }
        }

        function fixHeaderClasses(settings) {
            if (!settings || !settings.nTable) return;

            // Remove dt-type-numeric and add dt-type-string to all columns that had it
            var $ths = $(settings.nTable).find('thead th');
            $ths.each(function (i) {
                var $th = $(this);
                if ($th.hasClass('dt-type-numeric')) {
                    $th.removeClass('dt-type-numeric').addClass('dt-type-string');
                }
            });
        }

        function fixBodyClasses(settings) {
            if (!settings || !settings.nTable) return;

            // Remove dt-type-numeric from tick/cross cells to allow centering
            $(settings.nTable)
                .find('tbody td.dt-type-numeric')
                .has('.tick, .bracket-container')
                .removeClass('dt-type-numeric')
                .addClass('dt-type-string');
        }

        // Bind DataTable events for universal numeric type fixing
        $(document).on('preInit.dt', function (e, settings) {
            forceStringTypes(settings);
        });

        $(document).on('init.dt', function (e, settings) {
            forceStringTypes(settings);
            fixHeaderClasses(settings);
            fixBodyClasses(settings);
        });

        $(document).on('draw.dt', function (e, settings) {
            fixHeaderClasses(settings);
            fixBodyClasses(settings);
        });
    }
}


module.exports = AllocateUser;
