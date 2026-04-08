$(document).ready(function () {
    $(".division-seclect").chosen({
        no_results_text: "Oops, nothing found!",
        width: "200px"
    });

    //defien the varaiblr
    let action = 'searchStaffTeam';
    let teamID = '';
    let setparam  = '';
    let userID = '';
    let schedulepersonId = '';
    let divisionid = 0;
    let actionString = '';
    let issysadmin = 0;
    var savedTeamID=0;
    var wtablename = $("#js_tablename").val();
    var green_path_class = 'staffcrossGreen';
    var red_path_class = 'staffcrossRed';
    let teamsname = '';
    let rolename = '';
   

    //call by default function
    //getPagePermission(11);
    
    if($.fn.dataTable.isDataTable("#"+$("#js_tablename").val()) == false){

        if(wtablename=='js_scheduledstaffteamlist') {
            savedTeamID =  getSavedTeamForSchduledStaff();  
            var SavedTeamName = getSavedTeamNameForSchduledStaff();
        } else if(wtablename == 'js_nonscheduledstaffteamlist') {
            savedTeamID =  getSavedSelectedTeamForNonSchduledStaff();
            var SavedTeamName = getSavedSelectedTeamNameForNonSchduled();
        }
        
        if(savedTeamID) {
            teamID = savedTeamID;
            if(wtablename == 'js_scheduledstaffteamlist') {
            $('#js_schedulled_teamdropdown').val(teamID);
            }else if(wtablename == 'js_nonscheduledstaffteamlist') {
                $('#js_teamdropdown').val(teamID);
                $(" body #js_addnonscheduledstaff").removeClass('notclickable');
                $("body #js_addnonscheduledstaff").removeClass('buttonDisabled').addClass('buttonEnabled');
            }
            $('.chosen-single span').text(SavedTeamName);     
        } 
        divisionid = $("#js_teamdropdown option:selected").data('divisionid');
        getTeamStaffLists(action,teamID,divisionid);
    } else {
      reloadDataTablesAjax(action,teamID);
    }
    
    //click on add new button create, edit, view
    $("#js_addnonscheduledstaff").on('click', function () {
        teamID =  $("#js_teamdropdown option:selected").val();
        searchALoocateUsers(teamID);    
    });

    $(document).on('click',".js_additionalpermissions" ,function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        $(this).unbind('click');
        action = 'additionalpermission';
        teamID = $(this).data('teamid');
        schedulepersonId = '';
        userID = $(this).data('userid');
        roleID = $(this).data('roleid');
        setparam = $(this).data('setparam');
        teamsname = $("#js_teamdropdown option:selected").text();
        $('#loading').show();
        setAdditionalPermission(action,teamID,userID,roleID,setparam,schedulepersonId,teamsname);
        if(setparam == 1) {
            $(this).removeClass(red_path_class).addClass(green_path_class);
            $(this).prev().text(1);
            $(this).data('setparam',0);
        } else {
            $(this).removeClass(green_path_class).addClass(red_path_class);
            $(this).prev().text(0);
            $(this).data('setparam',1);
        }
        setTimeout(function() {reloadDataTablesAjax('searchStaffTeam',teamID);}, 1000);
    });

    $(document).on('change',".js_mainrole" ,function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        $(this).unbind('click');
        action = 'rolepermission';
        teamID = $(this).data('teamid');
        schedulepersonId = $(this).data('schedulepersonid');
        userID = $(this).data('userid');
        setparam = $(this).data('setparam');
        roleID = $(this).val();
        teamsname = $("#js_teamdropdown option:selected").text();
        setAdditionalPermission(action,teamID,userID,roleID,setparam,schedulepersonId,teamsname);
        setTimeout(function() {reloadDataTablesAjax('searchStaffTeam',teamID);}, 1000);
    });

    $(document).on('change',".js_rota" ,function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        $(this).unbind('click');
        action = 'rotapermission';
        teamID = $(this).data('teamid');
       
        schedulepersonId = $(this).data('schedulepersonid');
        userID=$(this).data('userid');
        roleID = $(this).val();
        setparam = $(this).data('setparam');
        teamsname = $("#js_teamdropdown option:selected").text();
        setAdditionalPermission(action,teamID,userID,roleID,setparam,schedulepersonId,teamsname);	
        setTimeout(function() {reloadDataTablesAjax('searchStaffTeam',teamID);}, 1000);
    });
    
    //set default team
    $(document).on('click', ".js_setdefaultteam", function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        $(this).unbind('click');
        let scheduledteamid = $(this).data('teamid');
        
        let defaultValue = $(this).data('default');
        let schedulepersonid = $(this).data('schedulepersonid');
        setDefaultTeam(scheduledteamid, schedulepersonid, defaultValue);
        if(defaultValue == 1) {
            $(this).removeClass(red_path_class).addClass(green_path_class);
            $(this).prev().text(1);
            $(this).data('default',0);
        } else {
            $(this).removeClass(green_path_class).addClass(red_path_class);
            $(this).prev().text(0);
            $(this).data('default',1);
        }
        setTimeout(function() {reloadDataTablesAjax('searchStaffTeam',scheduledteamid);}, 1000);
    });
    
    //Enable disabled button of search start
    if ($("#js_teamdropdown option:selected").val() == '' ) {
        toogleSearchButton();
        
    }

    $(document).on('change','#js_teamdropdown,#js_schedulled_teamdropdown', function (event) { 
        actionString = '';
        divisionid = $("#js_teamdropdown option:selected").data('divisionid');
        issysadmin = $("#js_sysadmin ").val();
        getPagePermission(11,teamID);
        if(divisionid != 0 || issysadmin == 1 || $("#js_create").val()){
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
    });

    //serach the scheduled people
    $(document).on('click',"#search", function (event) {
        //define the varaibe
        event.stopImmediatePropagation();
        event.preventDefault();
        $(this).unbind('click');
        action = 'searchStaffTeam';
        teamID = $("#js_teamdropdown option:selected").val();//for non schedull
        searchStaffTeam(action,teamID);
    });

     //create the allocate user
     $(document).on('click', "#js_nonschedteamstaff", function (event) {
        createStaffTeamUsers();
    });

    //remove scheduled team
    $(document).on('click', ".js_removeStaff", function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        $(this).unbind('click');
        let scheduledteamid = $(this).data('teamid');
        let schedulepersonid = $(this).data('schedulepersonid');
        removeScheduledTeam(scheduledteamid, schedulepersonid);
    });

    //display user info tabe
    $(document).on('click','.js_userdatainfo', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        let netlogin = $(this).data('netlogin');
        let userid = $(this).data('userid');
        let staffid =  $(this).data('staffid');
        userInfoDataPopup(netlogin,userid,staffid);
    });

    //get history model
    $(document).on('click', "#js_nonscheduledhistory", function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        let scheduledteamid = $(this).data('teamid');
        userID = $(this).data('userid');
        showAdditionalTeamHistory(scheduledteamid,userID);
    });
});


function searchResult() {
    let action = 'searchStaffTeam';
    if($('#js_usertype').val()==0) {
        teamID = $("#js_teamdropdown option:selected").val();
        saveSelectedTeamForNonSchduledStaff(teamID,$("#js_teamdropdown option:selected").text());
    }
    if($('#js_usertype').val()==1) {
        teamID = $("#js_schedulled_teamdropdown option:selected").val();
        saveSelectedTeamForSchduledStaff(teamID,$("#js_schedulled_teamdropdown option:selected").text());
    }
   if(teamID != ''){
    searchStaffTeam(action,teamID);
   }
    
}

/*Function to call the searc/clear filter*/
function searchStaffTeam(action, teamID) {
    reloadDataTablesAjax(action,teamID);
}

//Enable disabled button of search 
function toogleSearchButton(action = '') {
    searchResult();
   
    if (action == 'Enable') {
        $(" body #js_addnonscheduledstaff").removeClass('notclickable');
        $("body #js_addnonscheduledstaff").removeClass('buttonDisabled').addClass('buttonEnabled');
    } else {
        $("#js_addnonscheduledstaff").addClass('notclickable');
        $("#js_addnonscheduledstaff").removeClass('buttonEnabled').addClass('buttonDisabled');
    }
}

//call CreateScheduledPerson page
function addNonScheduledTeamStaff(action,teamid) {
    
    $.post("page-includes/users/userstab/process/userStaffTeam.php", {
            action: 'addNonScheduledTeamStaff',
            action : 'assigned'
        },
        function (data, status) {
            $('#content').html(data);
        })
}
/*
* @Description : Calling of function for  Team user staff list data.Append to datatables
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getTeamStaffLists(action,teamID,divisionid, datatableInstance = null) {
    let usertype = $("#js_usertype").val();
    let tablename = $("#js_tablename").val();
   $.ajax({
        type: 'POST',
        dataType: "json",
        url: '/page-includes/users/userstab/process/userStaffTeam.php',
        "data": {
            action: action,
            selectedTeamID: teamID,
            usertype:usertype,
            divisionid:divisionid
        },
        success: function (data) {
            var table_body = '';
            if(datatableInstance != null) {
                datatableInstance.fnDestroy()
            }
            if(data.data) {
                $.each(data.data, function (index, obj) {
                    table_body += '<tr>';
                    $.each(obj, function (key, value) {
                        if(value != null) {
                            if (value.search("green_tick.png") >= 0 ) {
                                table_body += '<td data-order=1 >'+value+'</td>';
                            } else if (value.search("red_cross.png") >= 0) {
                                table_body += '<td data-order=0>'+value+'</td>';
                            } else if (value.search("data_order_") >= 0) {
                                if(value.search("data_order_0") >= 0) {
                                    table_body += '<td data-order="Show">'+value+'</td>';
                                } else if (value.search("data_order_1") >= 0) {
                                    table_body += '<td data-order="Hide">'+value+'</td>';
                                } else if (value.search("data_order_2") >= 0) {
                                    table_body += '<td data-order="Leave">'+value+'</td>';
                                } else if (value.search("data_order_3") >= 0) {
                                    table_body += '<td data-order="Scheduling Team Admin">'+value+'</td>';
                                } else if (value.search("data_order_4") >= 0) {
                                    table_body += '<td data-order="Senior Scheduler">'+value+'</td>';
                                } else if (value.search("data_order_5") >= 0) {
                                    table_body += '<td data-order="Scheduler">'+value+'</td>';
                                } else if (value.search("data_order_6") >= 0) {
                                    table_body += '<td data-order="Scheduling Team Viewer">'+value+'</td>';
                                }
                            } else {
                                table_body += '<td>'+value+'</td>';
                            }
                        } else {
                            table_body += '<td></td>';
                        }
                    });
                    table_body += '</tr>';
                });
                $("#"+tablename+" tbody").html(table_body);
            }

            $(".scheduledNonSchstaffteamlist").height($(window).height() - 270);
            $("#adminuserstabs").height($(window).height() - 100);
            let table = $("#"+tablename).DataTable({
                lengthChange: false,
                paging: true,
                "pageLength": 50,
                info: false,
                stateSave: true,
                deferRender: true,
                scrollY: parseInt($(".scheduledNonSchstaffteamlist").height() + 20),
                scrollX: true,
                buttons: [
                    {
                        extend:    'print',
                        text:      '<img id="print" class="handcursor" name="print" border="0" src="images/print.png" style="top:10px; vertical-align:middle;" alt="Print" width="18px" height="17px">',
                        titleAttr: 'Print'
                    }
                ],
                
                "fnDrawCallback": function (oSettings) {
                    $(".sorting, .sorting_asc").css('min-width', '128px');
                   
                },
            });
            $(".dataTables_paginate").css('visibility', 'visible');
            $(".dataTables_length").css("display", "none");
            //apply search bfilter
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
            
            //Set table scrollBody height dynamically
            $('.scheduledNonSchstaffteamlist .dataTables_scrollBody').css('height', parseInt($(".scheduledNonSchstaffteamlist").height() + 20));
            $(window).resize(function() {
                $('.scheduledNonSchstaffteamlist .dataTables_scrollBody').css('height', parseInt($(".scheduledNonSchstaffteamlist").height() + 20));
            });

            //Store scroll position in cookie  
            var scrollCookieNameY = tablename + '_table_scroll_position_y';
            var scrollCookieNameX = tablename + '_table_scroll_position_x';
            if (!!$.cookie(scrollCookieNameY)) {
                $('#'+ tablename + '_wrapper').find('.dataTables_scrollBody').scrollTop($.cookie(scrollCookieNameY));
                $('#'+ tablename + '_wrapper').find('.dataTables_scrollBody').scrollLeft($.cookie(scrollCookieNameX));
            }
            $('#'+ tablename + '_wrapper').find('.dataTables_scrollBody').on('scroll', function() { 
                $.cookie(scrollCookieNameY, $(this).scrollTop());
                $.cookie(scrollCookieNameX, $(this).scrollLeft());
            });
        }
    });
}



/* Table ajax relaod datatables */
function reloadDataTablesAjax(action,teamID) {
    let tablename = $("#js_tablename").val();
    let datatableInstance = $("#"+tablename).dataTable();
    let divisionid = $("#js_teamdropdown option:selected").data('divisionid');
    getTeamStaffLists(action,teamID,divisionid,datatableInstance);
}




function setAdditionalPermission(action,teamID,userID,roleID,setparam,schedulepersonId,teamsname){
    $.ajax({
        url: "/page-includes/users/userstab/process/userStaffTeam.php",
        type: "POST",
        dataType: "json",
        global: false,
        data: {
            action: action,
            teamID: teamID,
            userID: userID,
            roleID:roleID,
            setparam:setparam,
            schedulepersonId:schedulepersonId,
            teamsname : teamsname
        },
        success: function (data) {
            if (data.strstatus == 'success') {
                let actionvalue = 'searchStaffTeam'; 
            }
        }
    });
    
}

/*
* @Description : Set User Default Scheduling team .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function setDefaultTeam(teamID, schedulepersonid, defaultValue) {

    $.ajax({
        type: 'POST',
        url: '/page-includes/users/process/setUserSetup.php',
        dataType: "json",
        data: {
            action: 'setdefault',
            defaultValue: defaultValue,
            scheduledteamid: teamID,
            schedulepersonid: schedulepersonid
        },
        success: function (data) {
            if (data.strstatus == 'success') {
                let actionvalue = 'searchStaffTeam'; 
            } else {
                alert(data.strreturnstring);
            }
        }
    });

}

/*
* @Description : Search AD User .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function searchALoocateUsers(teamID) {
    $.post("/page-includes/admin/allocate-users/process/allocateUsers.php", {
            action: 'createuserspopup',
            formname:"newstaffteamuser",
            buttonid:"js_nonschedteamstaff",
            hiddenactionname:"js_checkalloacteuser",
            hiddenactionvalue:"checkalloacate",
            teamid:teamID,
            title : 'Non Scheduled Team'
        },
        function (data) {
            $.facebox(data);
        })
}


/*
    * @Description :Autocomplete network id
    * @access : Public
    * @global : N/A
    * @param  : N/A
    * @return : N/A
    */
$(document).on('keyup', '#js_netLogin', function () {
    $(this).autocomplete({

        source: "page-includes/users/userstab/process/userStaffTeam.php?action=searchNetLogin",
        minLength: 2,
        select: function (event, ui) {
            $('#js_netLogin').val(ui.item.value);
        }
    })
        .on('mouseup', function () {
            $(this).select();
        });
});

/*
* @Description : Create Allocate User .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function createStaffTeamUsers() {
    $('#newstaffteamuser').validate({
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
                url: '/page-includes/users/userstab/process/userStaffTeam.php',
                dataType: "json",
                data: $('#newstaffteamuser').serialize(),
                success: function (data) {
                    if (data.status == 'success') {
                        $.facebox.close();
                       
                        $("#js_nonschedteamstaff").unbind('click');
                        alert(data.view);
                        let actionvalue = 'searchStaffTeam';
                        teamID = $("#js_teamdropdown option:selected").val(); 
                     reloadDataTablesAjax(actionvalue,teamID);
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

function removeScheduledTeam(scheduledteamid,schedulepersonid){
    $.ajax({
        type: 'POST',
        url: '/page-includes/users/userstab/process/userStaffTeam.php',
        dataType: "json",
        data: {action: 'removestaff',
            scheduledteamid: scheduledteamid,
            schedulepersonid: schedulepersonid},
        success: function (data) {
            if (data.status == 'success') {
                let actionvalue = 'searchStaffTeam';
                teamID = $("#js_teamdropdown option:selected").val(); 
                reloadDataTablesAjax(actionvalue,teamID);
            } else {
                alert(data.view);
            }
        }
    })
}

/* get the history*/

function  showAdditionalTeamHistory(scheduledteamid,schedulepersonid){
    $.post("function-includes/common/common.php", {
            attributeid: scheduledteamid,
            attributeid2: schedulepersonid,
            action: 'additionalpermissionhistory',
            modulename: 'NonScheduledTeamStaff'
        },
        function (data, status) {
            $.facebox(data);
        })
}


// Focus Drop Down Effect
$('#adminuserstabs .fields label').click(function() {
    $('#js_teamdropdown_chosen').addClass('chosen-container-active');
});



//Added New 
