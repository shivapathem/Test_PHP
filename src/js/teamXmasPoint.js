$(document).ready(function () {
    let teamid= '';
    let action= '';
    let extrapointid= '';
    //call the permissions
    getPagePermission(13);
    // Call the Xmas Point Users List
    
    $("#clearfilterbox").hide();

    //Enable disabled button of search start
    if ($("#js_teamdropdown option:selected").val() == '' ) {
        toogleSearchButton();
    }
    
    $(".teams-seclect").chosen({
        no_results_text: "Oops, nothing found!",
        width: "200px"
    });
    if(!!$.cookie("selectedextraxmas")) {
        $("#js_teamdropdown").val($.cookie("selectedextraxmas")).trigger("chosen:updated");
        toogleSearchButton('Enable');
        getXmasPointUsersLists();
    }else{
        getXmasPointUsersLists();
    }
    $(document).on('change','#js_teamdropdown', function (event) { 
        let actionString = '';
        let divisionid = $("#js_teamdropdown option:selected").data('divisionid');
        let teamID  = $("#js_teamdropdown option:selected").val();
        let issysadmin = $("#js_sysadmin").val();
        getPagePermission(13,teamID);
        if($("#js_create").val()){
            actionString = 'Enable';
       }
        event.stopImmediatePropagation();
        event.preventDefault();
        $(this).unbind('click');
        if ( $(this).val() == '') {
            toogleSearchButton();
        }else{
          
            toogleSearchButton(actionString);
        }
       
        searchExtraXmasPoint(action,teamID);
    });
    $(document).on('click','#js_addxmaspointuser', function (e) {
       
        e.stopImmediatePropagation();
        e.preventDefault();
        $(this).unbind('click');
        teamid = $("#js_teamdropdown option:selected").val();
        action ='createxmaspoint';
        extrapointid = 0;
        extraXmasPointPopup(teamid,extrapointid,action);
    });

    $(document).on('click','#js_editextrapoint', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        $(this).unbind('click');
        teamid = $(this).data('teamid');
        action ='updateextrapoint';
        extrapointid = $(this).data('extrapointid');
        extraXmasPointPopup(teamid,extrapointid,action);
    });
    $(document).on('click','#js_deleteExtraXmasPoint', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        $(this).unbind('click');
		extrapointid = $(this).data('extrapointid');
		customConfirm('Are you sure to delete this extra Christmas point entry?',function(){
				action ='delete';
				removeExtraXmasPoint(action,extrapointid,action);
			},
			function() {
				return false;
			}
		);
    });
    
    //create the extra xmas point for  user
    $(document).on('click', "#js_addxtrapointubmit", function (e) {
        e.stopPropagation();
        createUsersExtraXmasPoint();
    });
});

/*
* @Description : Calling of function for  allocate userlist data.Append to datatables
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getXmasPointUsersLists() {
    
    $.cookie("selectedextraxmas", $("#js_teamdropdown option:selected").val());
    $.fn.dataTable.ext.errMode = 'none';
    let table = $("#extraxmaspoinlists").DataTable({
        paging: false,
        scrollY: 400,
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        async:false,
        "ajax": {
            type: 'POST',
            url: '/page-includes/admin/process/teamXmasPoint.php',
            "data": {
                task: 'extraXmasPointListCall',
                teamid:$("#js_teamdropdown option:selected").val(),
                divisionid:$("#js_teamdropdown option:selected").data('divisionid')
            },
        },
        "columnDefs": [
            {"orderable": false, "targets": 3},
            {"orderable": false, "targets": 4}
        ]
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
            column_number: 2,
            filter_type: 'text'
        }
    ]);
    $(".dataTables_paginate").css('visibility', 'visible');
    $(".dataTables_length").css("display", "block");
}

/* Table ajax relaod datatables */
function reloadDataTablesUserAjax() {
    $('#extraxmaspoinlists').DataTable().clear().destroy();
    getXmasPointUsersLists();
}


/*resize the grid */
function ResizeGrid() {
    var offset = ($("#extraxmaspoinlists").offset().top);
    var windowheight = $(window).height() - offset;
    $('.dataTables_scrollBody').height((windowheight));
}


/*
* @Description : Create Allocate User .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function createUsersExtraXmasPoint() {
    $('#newaddxmaspoint').validate({
        debug: false,
        rules: {
            "year": {
                required: true,
                min: 2011,
                max: 2024,
            },
            "points": {
                required:true,
                min: -10,
                max: 30,
            },
            "userid":{
                required:true
            },
            "notes": {
                minlength: 3,
                maxlength: 500
            }

        },
        messages: {
            "userid": {
                required: "<br/> Please select the users."
            },
            "points": {
                required: "<br/> Enter the points."
                
            },
            "year": {
                required: "<br/> Enter the year."
                
            },
            "notes": {
                minlength: jQuery.validator.format("<br/> At least {0} characters are necessary."),
                maxlength: '<br/> Notes can not be more than {0} characters.'
            }

        },
        submitHandler: function (form) {
            $.ajax({
                type: 'POST',
                url: '/page-includes/admin/process/teamXmasPoint.php',
                dataType: "json",
                data: $('#newaddxmaspoint').serialize(),
                success: function (data) {
                    if (data.strstatus == 'success') {
                        $.facebox.close();
                        reloadDataTablesUserAjax();
                    } else {
                        alert(data.strreturnstring);
                    }
                }
            });
        }
    })
}

function extraXmasPointPopup(teamid,extrapointid,action){
    let divisionid = $("#js_teamdropdown option:selected").data('divisionid');
    $.ajax({
        type: 'POST',
        url: '/page-includes/admin/process/teamXmasPoint.php',
        dataType: "json",
        data: {task:action,teamid:teamid,extrapointid:extrapointid,divisionid:divisionid},
        success: function (data) {
            if (data.status == 'success') {
                $.facebox(data.view);
            } else {
                alert("no data");
            }
        }
    });
}

/*Remove extra xmas point*/
function removeExtraXmasPoint(action,extrapointid){
    $.ajax({
        type: 'POST',
        url: '/page-includes/admin/process/teamXmasPoint.php',
        dataType: "json",
        data: {task:action,extrapointid:extrapointid,teamid:''},
        success: function (data) {
            if (data.strstatus == 'success') {
                reloadDataTablesUserAjax();
            } else {
                alert(data.strreturnstring);
            }
        }
    });
}


//Enable disabled button of search 
function toogleSearchButton(action = '') {
  
    if (action == 'Enable') {
        $("#js_addxmaspointuser").removeClass('notclickable');
        $("#js_addxmaspointuser").removeClass('buttonDisabled').addClass('buttonEnabled');
    } else {
        $("#js_addxmaspointuser").addClass('notclickable');
        $("#js_addxmaspointuser").removeClass('buttonEnabled').addClass('buttonDisabled');
    }
}

/*Function to call the searc/clear filter*/
function searchExtraXmasPoint(teamID) {
    reloadDataTablesUserAjax(teamID);
}
