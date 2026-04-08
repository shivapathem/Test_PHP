$(document).ready(function () {
    $("#bugtd").hide();
    //call the permissions
    getPagePermission(9);
    // Call the Allocate Users List
    getAllocateUsersLists();

    //define the tabing function
    $(function () {
        $("#adminuserstabs").tabs({
            activate: function (event, ui) {
                var active = $("#adminuserstabs").tabs("option", "active");
				$("#adminuserstabs").css("position", "");
                switch (active) {
                    case 1:
                        showSchedulingStaffTeamUI();
                      break;    
                    case 2:
                        showNonSchedulingStaffTeamUI();
                      break; 
                    case 3:
                        ShowDivisionalAdmin();
                    break; 
                }
            }
        });
        $("#adminuserstabs .ui-tabs-panel").removeAttr("aria-labelledby");
    });
    //click on search bar
    $("#js_addallocateuser").on('click', function (e) {
        e.stopPropagation();
        searchADUsers();
    });
    $(document).on('click','.js_userinfo', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        let netlogin = $(this).data('netlogin');
        let userid = $(this).data('userid');
        let staffid =  $(this).data('staffid');
        let first_tab = false;
        if($(this).data('first_tab'))
		{
			first_tab =  $(this).data('first_tab');
		}
        userInfoPopup(netlogin,userid,staffid,first_tab);
    });
   
    /*
    * @Description :Autocomplete network id
    * @access : Public
    * @global : N/A
    * @param  : N/A
    * @return : N/A
    */
    $(document).on('keyup', '#js_netLogin', function () {
        $(this).autocomplete({

            source: "page-includes/staff-details/process/getStaffDetailsAutocompleteList.php?termKey=NetLogin",
            minLength: 2,
            select: function (event, ui) {
                $('#js_netLogin').val(ui.item.value);
            }
        })
            .on('mouseup', function () {
                $(this).select();
            });
    });

    //create the allocate user
    $(document).on('click', "#js_adduserssubmit", function (e) {
        e.stopPropagation();
        createAllocateUsers();
    });
    
});

/*
* @Description : Calling of function for  allocate userlist data.Append to datatables
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getAllocateUsersLists() {
    $.fn.dataTable.ext.errMode = 'none';
    let table = $("#alocateUsersLists").DataTable({
        lengthChange: false,
        paging: true,
        iDisplayLength: 50,
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend:    'print',
                text:      '<img id="print" class="handcursor" name="print" border="0" src="images/print.png" style="top:10px; vertical-align:middle;" alt="Print" width="18px" height="17px">',
                titleAttr: 'Print'
            }
        ],
        "ajax": {
            type: 'POST',
            url: '/page-includes/admin/allocate-users/process/allocateUsers.php',
            "data": {
                action: 'getAllocateUsers'
            },
        },
        "columnDefs": [
            {"orderable": false, "targets": 6}
        ],
		"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull)
		{
		  $(nRow).attr("id", aData[1]);
		  return nRow;
		}
    });
    yadcf.init(table, [
        {
            column_number: 0,
            filter_type: 'text'
        },
        {
            column_number: 1,
            filter_type: 'text'
        },
        {
            column_number: 3,
            filter_type: 'text'
        },
        {
            column_number: 4,
            filter_type: 'text'
        },
        {
            column_number: 5,
            filter_type: 'text'
        }
    ]);
    $(".dataTables_paginate").css('visibility', 'visible');
    $(".dataTables_length").css("display", "block");
    $("#adminuserstabs").css("position", "absolute");
}

/* Table ajax relaod datatables */
function reloadDataTablesUserAjax() {
    $('#alocateUsersLists').DataTable().clear().destroy();
    getAllocateUsersLists();
}
/*
* @Description : Search AD User .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function searchADUsers() {
    $.post("/page-includes/admin/allocate-users/process/allocateUsers.php", {
            
            action: 'createuserspopup',
            formname:"newadduser",
            buttonid:"js_adduserssubmit",
            hiddenactionname:"js_checkuser",
            hiddenactionvalue:"check",
            teamid:'',
            title: 'Allocate'
        },
        function (data) {
            $.facebox(data);
        })

}
/*
* @Description : Create Allocate User .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function createAllocateUsers() {
    $('#newadduser').validate({
        debug: false,
        rules: {
            "netLogin": {
                required: true
            }
        },
        messages: {
            "netLogin": {
                required: "<br/> Please enter the netLogin ID."
            }
        },
        submitHandler: function (form) {
            $.ajax({
                type: 'POST',
                url: '/page-includes/admin/allocate-users/process/allocateUsers.php',
                dataType: "json",
                data: $('#newadduser').serialize(),
                success: function (data) {
                    if (data.status == 'success') {
                        $.facebox.close();
                        alert(data.view);
                        reloadDataTablesUserAjax();
                    } else {
                        if (data.status == 'usererror') {
                            $.facebox(data.view);
                        } else {
                            $(".messageerror").html(data.view);
                        }
                    }
                }
            });
        }
    })
}

function userInfoPopup(netlogin,userid,staffid,first_tab = false){

    $.ajax({
        type: 'POST',
        url: '/page-includes/admin/allocate-users/process/allocateUsers.php',
        dataType: "json",
        data: {action:'showuserinfo',netlogin:netlogin,userid:userid,staffid:staffid,first_tab:first_tab},
        success: function (data) {
            if (data.status == 'success') {
                $.facebox(data.view);
                
               
            } else {
                if (data.status == 'usererror') {
                    $.facebox(data.view);
                } else {
                    $(".messageerror").html(data.view);
                }
            }
        }
    });
}
