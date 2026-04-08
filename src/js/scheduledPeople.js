$(document).ready(function () {
    $(".division-seclect").chosen({
        no_results_text: "Oops, nothing found!",
        width: "200px"
    });

    //defien the varaiblr
    let actionType = '';
    let teamID = '';
    let userID = '';
    let schedulepersonid = null;
    //call by default function
    getPagePermission(6);
    autocompleteDisplayName();

    //hide clear button intially
    $("#clearfilterbox").css("visibility", "hidden");
    
    //click on add new button create, edit, view
    $("#js_addnewbutton").on('click', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        actionType = 'create';
        schedulepersonid = null;
        CreateScheduledPerson(actionType, schedulepersonid);
    });
    //Enable disabled button of search start
    if ($("#teamdropdown option:selected").val() == '' || $("#dispalyName").val() == '') {
        toogleSearchButton();
    }
    $("#dispalyName").on('keyup', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        toogleSearchButton(action = 'Enable');
        if ($("#dispalyName").val() == '' && $("#teamdropdown option:selected").val() == '') {
            toogleSearchButton();
        }
        setTimeout(function() {
            $('.ui-menu.ui-autocomplete').css('overflow-y','auto').css('overflow-x','hidden').css('max-height','50%').css('min-height','auto').css('width','13.5%');
        }, 700);
    });

    //serach the scheduled people
    $("#search").on('click', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        //define the varaibe
        actionType = 'search';
        teamID = $("#teamdropdown option:selected").val();
        userID = $("#dispalyName").val();
       
        searchClearFilter(actionType, teamID, userID);

    });
    $("#teamdropdown").on('change', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        teamText = $("#teamdropdown option:selected").text();
		if(teamText.toLowerCase() == 'archive')
		{
			$("#excludeNoTeam").attr("disabled", true);
			$("#excludeNoTeamVal").val(0);
		}
		else
		{
			$("#excludeNoTeam").removeAttr("disabled");
			if($('#excludeNoTeam')[0].checked)
			{
				$("#excludeNoTeamVal").val(1);
			}else
			{
				$("#excludeNoTeamVal").val(0);
			}
		}
        teamID = $("#teamdropdown option:selected").val();
        userID = $("#dispalyName").val();
        autocompleteDisplayName(teamID);
        let actionString = 'Enable';
        
        if ($("#dispalyName").val() == '' && $("#teamdropdown option:selected").val() == '') {
            toogleSearchButton();
            actionType = 'claerfilter';
            teamID = '';
            userID = '';
        }else{
            toogleSearchButton(actionString);
            actionType = 'search';
        }
        searchClearFilter(actionType, teamID, userID);
    });
    //clear the filter
    $(document).on('click', '#clearfilter', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        //define the varaibe
        actionType = 'claerfilter';
        teamID = '';
        userID = '';
        searchClearFilter(actionType, teamID, userID);
    });

});

/* get permission */
function getPermission() {
    $.ajax({
        url: "page-includes/staff-details/process/handlethepermission.php",
        type: "POST",
        dataType: "json",
        data: {
            'pageId': 6,
        },
        success: function (data) {

            if (data.status == 'success') {
                if (data.permissions.canview == 0) {
                    $('#content').load('page-includes/no_access.php', function () {
                    });
                }
            }
        }
    });
}

/*function autocomplete */
function autocompleteDisplayName(teamID = '') {
    $("#dispalyName").autocomplete({

        //source: "page-includes/staff-details/process/getScheduledpersonUserList.php?excludeNoTeam=" + $('#excludeNoTeamVal').val() + "&teamid=" + teamID,
        source: function(request, response) {
					$.getJSON("page-includes/staff-details/process/getScheduledpersonUserList.php", { excludeNoTeam: $('#excludeNoTeamVal').val(), teamid: teamID, term: $('#dispalyName').val() }, 
							  response);
				  },
        minLength: 2,
        select: function (event, ui) {
            $('#dispalyName').val(ui.item.value);
            let searchTeamID = $("#teamdropdown option:selected").val();
            let searchUserName = ui.item.value;
            var actionType = 'search';
            if(searchTeamID != '' && searchUserName != ''){
                searchClearFilter(actionType, searchTeamID, searchUserName);
            }else{
                $("#search").trigger('click');
            }
            
        }
    })
        .on('mouseup', function () {
            $(this).select();
        });
}

/*Function to call the searc/clear filter*/
function searchClearFilter(actionType, teamID, userID) {

    $.ajax({
        url: "page-includes/staff-details/process/searchScheduledPerson.php",
        type: "POST",
        dataType: "json",
        data: {
            'selectedTeamID': teamID,
            'selectedUserName': userID,
            'actionType': actionType,
			'excludeNoTeam': $('#excludeNoTeamVal').val()
        },
        success: function (data) {

            if (data.status == 'success') {

                $("#scheduledpeoplelist tbody").html(data.view);
                if (actionType == 'search') {
                    $("#clearfilterbox").css("visibility", "visible");
                } else {
                    toogleSearchButton();
                    $("#clearfilterbox").css("visibility", "hidden");
                    autocompleteDisplayName();
                   $('#teamdropdown').val('').trigger('chosen:updated');
                    $("#dispalyName").val('');
                }

            } else {
                customAlert("no data");
            }
			if($("#teamdropdown option:selected").text().toLowerCase() == 'archive')
			{
				$("#excludeNoTeam").attr("disabled", true);
				$("#excludeNoTeamVal").val(0);
			}
			else
			{
				$("#excludeNoTeam").removeAttr("disabled");
				if($('#excludeNoTeam')[0].checked)
				{
					$("#excludeNoTeamVal").val(1);
				}else
				{
					$("#excludeNoTeamVal").val(0);
				}
			}
        },
        error: function (x, e) {
            if (x.status == 0) {
                customAlert('You are offline!!<br/> Please Check Your Network.'+ x.responseText);
            } else if (x.status == 404) {
                customAlert('Requested URL not found.');
            } else if (x.status == 500) {
                customAlert('Internal Server Error.');
            } else if (e == 'parsererror') {
                customAlert('Error.<br/>Parsing JSON Request failed.');
            } else if (e == 'timeout') {
                customAlert('Request Time out.');
            } else {
                customAlert('Unknown Error.<br/>' + x.responseText);
            }
        }
    });
}

//Enable disabled button of search 
function toogleSearchButton(action = '') {
    if (action == 'Enable') {
        $("#search").removeClass('buttonDisabled').addClass('buttonEnabled');

    } else {
        $("#search").removeClass('buttonEnabled').addClass('buttonDisabled');
    }
}

//call CreateScheduledPerson page
function CreateScheduledPerson(actionType, schedulepersonid, teamId = '') {
    let task = "schedulepersonhtmlcall";
    var selectedteamid= teamId ? teamId : $( "#teamdropdown" ).val();
    var searchUserId = $('#dispalyName').val();
    $.ajax({
        url: "page-includes/staff-details/process/createSchedulePerson.php",
        type: "POST",
        data: {
            task: task,
            useraction: actionType,
            schedulepersonid: schedulepersonid,
            selectedteamid:selectedteamid,
            selecteduserid:searchUserId
        },
        success: function (data) {
            $("#content").html(data);
        }
    });
}

function highlightTableRow(thisVal)
{
    $(thisVal).addClass('highlightOrange');
}
function unHighlightTableRow(thisVal)
{
    $(thisVal).removeClass('highlightOrange');
}
