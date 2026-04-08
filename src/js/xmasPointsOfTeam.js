$(document).ready(function () {
    let teamid= '';
    let action= '';
    let day = '';
    let subxmaspointid= '';
    //call the permissions
    getPagePermission(14);
    
    $(".teams-seclect").chosen({
        no_results_text: "Oops, nothing found!",
        width: "200px"
    });
    //Enable disabled button of search start
    if ($("#js_teamdropdownxmas option:selected").val() == '' ) {
        toogleSearchButton();
    }
    if(!! $.cookie("selectedxmas")) {
        $("#js_teamdropdownxmas").val($.cookie("selectedxmas")).trigger("chosen:updated");
        action= 'teamXmaspointLists';
        searchExtraXmasPoint(action,$.cookie("selectedxmas"));
    }

    $(document).on('change','#js_teamdropdownxmas', function (event) { 
        let actionString = 'Enable';
        let divisionid = $("#js_teamdropdownxmas option:selected").data('divisionid');
        let issysadmin = $("#js_sysadmin ").val();
        event.stopImmediatePropagation();
        event.preventDefault();
        $(this).unbind('click');
        if ( $(this).val() == '') {
            toogleSearchButton();
        }else{
            toogleSearchButton(actionString);
        }
        teamID  = $("#js_teamdropdownxmas option:selected").val();
        action= 'teamXmaspointLists';
        searchExtraXmasPoint(action,teamID);
    });

    $('#js_teamdropdownxmas').on('change', function(event) {
        //define the varaibe
        event.stopImmediatePropagation();
        event.preventDefault();
        teamID  = this.value;
        action= 'teamXmaspointLists';
        searchExtraXmasPoint(action,teamID);
    });

    $(document).on('click','#js_editxmaspointsday', function (e) {
       
        e.stopImmediatePropagation();
        e.preventDefault();
        $(this).unbind('click');
        teamid = $(this).data();
        day = $(this).data('xmasday');
        teamid = $("#js_teamdropdownxmas option:selected").val();
        action ='teamxmaspointpopup';
        teamXmasPointPopup(teamid,day,action);
    });

    $(document).on('click','#js_editxmaspointxub', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        $(this).unbind('click');
        teamid = $(this).data('teamid');
        action ='xmaspointsubpopup';
        day = $(this).data('xmasday');
        subxmaspointid = $(this).data('subxmaspointid');
        teamXmasPointSubPopup(teamid,day,action,subxmaspointid);
    });
    $(document).on('click','#js_deletexmaspointsub', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        $(this).unbind('click');
        action ='deletesubxmas';
        subxmaspointid = $(this).data('subxmaspointid');
		customConfirm('Are you sure to delete this Christmas point entry?',function(){
				removeXmasPointSub(action,subxmaspointid);
			},
			function() {
				return false;
			}
		);
    });
    
    //create the extra xmas point for  user
    $(document).on('click', "#js_addxtrapointubmit", function (e) {
        e.stopPropagation();
        createTeamXmasPoint();
    });

    $(document).on('click', "#js_addxmassubpoint", function (e) {
        e.stopPropagation();
        createTeamXmasSubPoint();
    });

    $('#starttime').timepicker({
        'step': 15,
        'timeFormat': 'H:i'
        });

    
    $('#endtime').timepicker({
        'step': 15,
        'timeFormat': 'H:i'
        });
});



/*
* @Description : Create Team Xmas POINT .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function createTeamXmasPoint() {
    $('#xmasform').validate({
        rules:{
            "basicpoints":{
                required:true,
                min: -10,
                max: 30,
            },
            "limit":{
            }
        },
        
        submitHandler: function (form) {
            $.ajax({
                type: 'POST',
                url: '/page-includes/admin/process/teamXmasPoint.php',
                dataType: "json",
                data: $('#xmasform').serialize(),
                success: function (data) {
                   
                    if (data.strstatus == 'success') {
                        $.facebox.close();
                        action= 'teamXmaspointLists';
                        teamID  = $("#js_teamdropdownxmas option:selected").val();
                        searchExtraXmasPoint(action,teamID);
                        
                    } else {
                        alert(data.strreturnstring);
                    }
                }
            });
        }
    })
}


/*
* @Description : Create Allocate User .
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function createTeamXmasSubPoint() {
    $('#xmasformsub').validate({
        rules:{
            "points":{
              required:true,
              max: 30,
            },
            "limit":{
            }
        },
        
        submitHandler: function (form) {
            $.ajax({
                type: 'POST',
                url: '/page-includes/admin/process/teamXmasPoint.php',
                dataType: "json",
                data: $('#xmasformsub').serialize(),
                success: function (data) {
                    if (data.strstatus == 'success') {
                        $.facebox.close();
                        action= 'teamXmaspointLists';
                        teamID  = $("#js_teamdropdownxmas option:selected").val();
                        searchExtraXmasPoint(action,teamID);
                        
                    } else {
                        alert(data.strreturnstring);
                    }
                }
            });
        }
    })
}

function teamXmasPointPopup(teamid,day,action){
   
    $.ajax({
        type: 'POST',
        url: '/page-includes/admin/process/teamXmasPoint.php',
        dataType: "json",
        data: {task:action,teamid:teamid,day:day},
        success: function (data) {
            if (data.status == 'success') {
                $.facebox(data.view);
            } else {
                alert("no data");
            }
        }
    });
}

function teamXmasPointSubPopup(teamid,day,action,subxmaspointid){
   
    $.ajax({
        type: 'POST',
        url: '/page-includes/admin/process/teamXmasPoint.php',
        dataType: "json",
        data: {task:action,teamid:teamid,subxmaspointid:subxmaspointid,day:day},
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
function removeXmasPointSub(action,subxmaspointid){
    $.ajax({
        type: 'POST',
        url: '/page-includes/admin/process/teamXmasPoint.php',
        dataType: "json",
        data: {task:action,subxmaspointid:subxmaspointid},
        success: function (data) {
            if (data.strstatus == 'success') {
                teamID  = $("#js_teamdropdownxmas option:selected").val();
                action= 'teamXmaspointLists';
                searchExtraXmasPoint(action,teamID);
            } else {
                alert(data.strreturnstring);
            }
        }
    });
}


//Enable disabled button of search 
function toogleSearchButton(action = '') {
  
    if (action == 'Enable') {
        $("body #js_editxmaspointsday").removeClass('notclickable');
        $("body #js_editxmaspointxub").removeClass('activeclass');
        $("body #js_deletexmaspointsub").removeClass('activeclass');
    } else {
        $("body #js_editxmaspointsday").addClass('abc');
        $("body #js_editxmaspointxub").addClass('abc');
        $("body #js_deletexmaspointsub").addClass('wew');
    }
}

/*Function to call the searc/clear filter*/
function searchExtraXmasPoint(action,teamID) {
    $.cookie('selectedxmas',teamID);
    $.ajax({
        type: 'POST',
        url: '/page-includes/admin/process/teamXmasPoint.php',
        dataType: "json",
        data: {task:action,teamid:teamID},
        success: function (data) {
            if (data.status == 'success') {
                $("#xmaspoints tbody").html(data.view);
            } else {
                $.post("page-includes/admin/christmasPointErrorLogger.php", {
                        status:data.status,
                        teamId:teamID
                    },
                    function(resp){
                        return true;
                    }
                );
            }
        }
    });
}
