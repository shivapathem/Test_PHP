$(document).ready(function () {
    //call the set up table list
    getSetUpLists();
    //create the allocate user
    $(document).on('click', "#js_defaultimg", function (e) {
        e.stopPropagation();
        let scheduledteamid = $(this).data('teamid');
        let defaultValue = $(this).data('default');
        let schedulepersonid = $(this).data('schedulepersonid');
        setDefaultTeam(scheduledteamid, schedulepersonid, defaultValue);
    });
});

/*
* @Description : Calling of function for  allocate userlist data.Append to datatables
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getSetUpLists() {
    $.ajax({
        type: 'POST',
        url: '/page-includes/users/process/setUserSetup.php',
        dataType: "json",
        data: { action: 'getUserSetup' },
        success: function (data) {
            var table_body = '';
            if (data.data) {
                // Log the first row to check column count and keys
                $.each(data.data, function (index, obj) {
                    table_body += '<tr>';
                    // Always add Scheduling Teams, Default, Who's In
                    table_body += '<td>' + (obj['schedulingTeamName'] || '') + '</td>';
                    
                    // ---------- Default (with data-order) ----------
                    var defaultHtml = obj['isDefault'] || '';
                    var defaultOrder = 0;
                    if (defaultHtml && defaultHtml.indexOf('green_tick.png') !== -1) {
                        defaultOrder = 1;
                    } else if (defaultHtml && defaultHtml.indexOf('red_cross.png') !== -1) {
                        defaultOrder = 0;
                    }
                    table_body += '<td data-order="' + defaultOrder + '">' + defaultHtml + '</td>';

                    // ---------- Who's In (with data-order) ----------
                    var whosInHtml = obj['isWhosIn'] || '';
                    var whosInOrder = 0;
                    if (whosInHtml && whosInHtml.indexOf('green_tick.png') !== -1) {
                        whosInOrder = 1;
                    } else if (whosInHtml && whosInHtml.indexOf('red_cross.png') !== -1) {
                        whosInOrder = 0;
                    }
                    table_body += '<td data-order="' + whosInOrder + '">' + whosInHtml + '</td>';

                    // Add all role columns in the correct order
                    // (Match the order in your $roleLists)
                    var roles = [
                        'System Admin', 'Area Admin', 'Scheduling Team Admin',
                        'Scheduler', 'Scheduling Team Viewer', 'Scheduled Person',
                        'Team Leader', 'Skills Admin', 'Skills Authoriser',
                        'Shift Leader', 'Manager', 'Basic Reports',
                        'Advanced Reports', 'Area Viewer', 'Area Reports',
                        'Facility Administrator', 'Facility Booker', 'Edit All Allocations',
                        'Edit Master Duties', 'Edit Rota Patterns', 'Edit Leave Credits',
                        'Move Person Between Teams', 'Create New Freelancer', 'Create New Staff',
                        'Give Team Permissions', 'Edit Team Settings', 'Create New Team',
                        'Create New Group', 'Edit Master Duty Colours', 'Create New Area',
                        'Edit System Settings'
                    ];
                    $.each(roles, function(i, role) {
                        if (obj.hasOwnProperty(role)) {
                            if (obj[role] != null) {
                                if (obj[role].search("green_tick.png") >= 0) {
                                    table_body += '<td data-order=1>' + obj[role] + '</td>';
                                } else if (obj[role].search("red_cross.png") >= 0) {
                                    table_body += '<td data-order=0>' + obj[role] + '</td>';
                                } else {
                                    table_body += '<td>' + obj[role] + '</td>';
                                }
                            } else {
                                table_body += '<td></td>';
                            }
                        } else {
                            table_body += '<td></td>'; // Add empty cell if role not present
                        }
                    });

                    table_body += '</tr>';
                });

                $("#userSetUpLists tbody").html(table_body);

                // Destroy old DataTable if it exists
                if ($.fn.DataTable.isDataTable('#userSetUpLists')) {
                    $('#userSetUpLists').DataTable().destroy();
                }

                // Initialize DataTable
                let table = $("#userSetUpLists").DataTable({
                    paging: false,
                    info: false,
                    destroy: true,
                    stateSave: true,
                    columnDefs: [
                        { width: 245, targets: 0 },
                        { orderable: true, targets: '_all' }
                    ]
                });

                var columnIndexToHide = $('#userSetUpLists thead th').filter(function () {
                        return $(this).text().trim() === 'Facility Administrator';
                    }).index();
                //Hide facility administrator role
                !window.facilityBookingEnabled ? table.column(columnIndexToHide).visible(false) : table.column(columnIndexToHide).visible(true);

                // Reinitialize yadcf if needed
                if (typeof yadcf !== 'undefined') {
                    yadcf.init(table, [
                        { column_number: 0, filter_type: 'text' }
                    ]);
                }

                $(".dataTables_paginate").css('visibility', 'visible');
                $(".dataTables_length").css("display", "block");
            }
        }
    });
}

/* Table ajax relaod datatables */
function reloadDataTablesAjax() {
    $('#userSetUpLists').DataTable().clear().destroy();
    getSetUpLists();
}

/*resize the grid */
function ResizeGrid() {
    var offset = ($("#userSetUpLists").offset().top);
    var windowheight = $(window).height() - offset;
    $('.dataTables_scrollBody').height((windowheight));
}


/*
* @Description : Set User Default Scheduling team .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function setDefaultTeam(scheduledteamid, schedulepersonid, defaultValue) {
    $.ajax({
        type: 'POST',
        url: '/page-includes/users/process/setUserSetup.php',
        dataType: "json",
        data: {
            action: 'setdefault',
            defaultValue: defaultValue,
            scheduledteamid: scheduledteamid,
            schedulepersonid: schedulepersonid
        },
        success: function (data) {
            if (data.strstatus == 'success') {
                $('#userSetUpLists').dataTable().fnDestroy();
                var red_path = '../../../images/red_cross.png';
                var green_path = '../../../images/green_tick.png';
                $(".js_setdefault").each(function(idx, elem) {
                    var temp_teamId = $(elem).data('teamid');
                    $(elem).attr('src', red_path);
                    $(elem).parent().attr('data-order', 0);
                    if(temp_teamId == scheduledteamid) {
                        $(elem).attr('src', green_path);
                        $(elem).parent().attr('data-order', 1);
                    } else {
                        $(elem).attr('src', red_path);
                    }
                });

                let table = $("#userSetUpLists").DataTable({
                    paging: false,
                    info: false,
                    bDestroy : true,
                    stateSave: true,
                    bStateSave: true,
                    columnDefs: [
					{ width: 245, targets: 0 }
                    ]
                });
                yadcf.init(table, [
                    {
                        column_number: 0,
                        filter_type: 'text'
                    }
                ]);
                $(".dataTables_paginate").css('visibility', 'visible');
                $(".dataTables_length").css("display", "block");

                $("#yadcf-filter--userSetUpLists-0-reset").on("click", function (e) {
                    var state = table.state.loaded();
                    state.search.search = "";
                    state.columns[0].search.search = "";
                    localStorage.clear();
                    localStorage.setItem('DataTables_userSetUpLists_/', JSON.stringify(state));
                });
                $("#yadcf-filter--userSetUpLists-1-reset").on("click", function (e) {
                    var state = table.state.loaded();
                    state.search.search = "";
                    state.columns[1].search.search = "";
                    localStorage.clear();
                    localStorage.setItem('DataTables_userSetUpLists_/', JSON.stringify(state));
                });
            } else {
                alert(data.strreturnstring);
            }
        }
    });

}

function setWhosInFlag(scheduledteamid, schedulepersonid, whosInFlag) {
    $.ajax({
        type: 'POST',
        url: '/page-includes/users/process/setUserSetup.php',
        dataType: "json",
        data: {
            action: 'setwhosin',
            scheduledteamid: scheduledteamid,
            schedulepersonid: schedulepersonid,
			whosInFlag:whosInFlag
        },
        success: function (data) {
            if (data.strstatus == 'success') {
				whosInFlagNew = whosInFlag ? 0 : 1;
				document.getElementById("js_iswhosinimg"+data.schedulingteamId).src = data.view;
				document.getElementById("js_iswhosinimg"+data.schedulingteamId).setAttribute('onclick','setWhosInFlag(' + scheduledteamid + ', ' + schedulepersonid + ', ' + whosInFlagNew + ')');
            } else {
                alert(data.strreturnstring);
            }
        }
    });

}