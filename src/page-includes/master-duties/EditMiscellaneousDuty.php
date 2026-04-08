<?php
session_start();
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';

$intID = $_REQUEST["id"];
$intDutyType = 0;
$intIsRota = $_REQUEST["isrota"];
$ddlRotaTeams = empty($_REQUEST["ddlRotaTeams"]) ? 0 :$_REQUEST["ddlRotaTeams"];
if (!(isset($intIsRota))) {
    $intIsRota = 1;
    $_SESSION['isrota'] = 1;
}
$DutyTypeMisc = $_REQUEST['dutytype'];
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'];
$intType = 0;
$intShowForm = 1;
$isEdit = $_REQUEST['isEdit'];
$breakTime = "1";
//Check User Authentication
$_SESSION['isrota'] = 0;
$pageid = 5;//Master duties form id

// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $ddlRotaTeams);
if ($intIsRota == 1){
    $permissions->canmodify = 0;
}

//check if we show the form
if ($permissions->canview == 1) {
    $intDutyColourID = 0;
    $starttime = $endtime = $duration  = 0;
    $intTeamID = $ddlRotaTeams;
    $dutyName = '';
    $isNightDutyChecked = '';
    $intDutyType = 2;
    $row = array();
	$labelIds    = [];
    $buttonvalue = 'Create Duty';
    if ($intID != "0") {
        $rsDuty = GetDutyDetailsByID($intID);
        $row = json_decode($rsDuty, true);
        $dutyName = $row['DutyName'] ;
        $breakTime = (int)$row['BreakTime'] ;
        $duration = $row['Duration'];
        $intDutyType = $row['DutyTypeID'];
        $intTeamID = $row['TeamID'];
        $intDutyColourID = $row['DutyColourID'];
		$labelIds = array_filter([$row['DutyProgramId1'], $row['DutyProgramId2'], $row['DutyProgramId3'], $row['DutyProgramId4'], $row['DutyProgramId5'], $row['DutyProgramId6']]);
		$isNightDutyChecked = $row['IsNightShift'] ? 'checked' : '';
        $buttonvalue = "Update Duty";
    }
    $rsColours = GetDutyColourList($intTeamID);
    $ColourOptions = PopulateDutyColoursDropDown($rsColours, $intDutyColourID, $intID);
    if ($duration >= 3600){
        $intdurationHour = intval((int)$duration / 3600);
    }
    else{
        $intdurationHour = 0;
    }
    $intdurationMinute = intval((((int)$duration) % 3600) / 60);
    if ($intID > 0){
        $breakTimeHour = intval((int)$breakTime / 3600);
        $breakTimeMinute = intval((((int)$breakTime) % 3600) / 60);
    }
    else{
        $breakTimeHour = 0;
        $breakTimeMinute = 0;
    }


    $durationhourOptions = PopulateHoursDropDown($intdurationHour);
    $durationminuteOptions = PopulateMinutesDropDown($intdurationMinute);
    $breakTimeHourOptions = PopulateHoursDropDown($breakTimeHour);
    $breakTimeMinuteOptions = PopulateMinutesDropDown_limited($breakTimeMinute);
    $rsAreaTeams = GetUserAreaTeamsList ($intUserID);
    $TeamOptions = TeamListDropDown ($rsAreaTeams,$intTeamID);
    $MiscDutyTypes = GetMiscellaneousDutyTypes();
    $MiscDutyTypeOptions = PopulateMiscDutyTypes($MiscDutyTypes,$intDutyType, $intID);
	$prog = getAllLabels();
	$allProg = json_decode($prog);
    $lablestr = '';
	foreach ($allProg as $key => $value)
	{
		if(in_array($value->ID, $labelIds))
		{
			$lablestr   .='<option value="'.$value->ID.'" selected>'.$value->Programme .'</option>';
		}else
		{
			$lablestr   .='<option value="'.$value->ID.'">'.$value->Programme .'</option>';
		}
	}
    if($permissions->canview == 1 && $isEdit == 0){
        echo '<div style="width: 600px">';
        echo '<form id="neweditduty">';
        echo '<table id="dutynewedittable" class="redtable smalltable bluetable" width="100%" role="presentation">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="noBorder" colspan="3">Duty Details</th>';
        echo '<th class="noBorder" colspan="1"><span class="spanFaceBoxClose">×</span></th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
    }
    if ((($permissions->canmodify == 1) || ($permissions->cancreate == 1))  && $isEdit == 1) {
        echo '<div style="width: 600px">';
        echo '<form id="neweditduty">';
        echo '<table id="dutynewedittable" class="redtable smalltable bluetable" width="100%" role="presentation">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="noBorder" colspan="3">'.$title = $intID > 0 ? "Edit Miscellaneous Duty" : "New Miscellaneous Duty".'</th>';
        echo '<th class="noBorder"><span class="spanFaceBoxClose">×</span></th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '<tr>';
        echo '<td class="lightblue">Miscellaneous Duty Name<span class="required">*</span></td>';
        echo '<td colspan="3">';
        echo '<input id="dutyname" name="dutyname" type="text" size="55" maxlength="50" value="'.$dutyName.'">';
        echo '<input id="olddutyname" name="olddutyname" type="text" size="55" maxlength="50" value="'.$dutyName.'" hidden>';
        echo '<span style="float:right; padding: 2px;"><input type="checkbox" id="nightCheckbox" name="nightCheckbox"  style="vertical-align: middle;" value="" '.$isNightDutyChecked.'>';
        echo '<lable for="nightCheckbox">Night</lable></span>';
        echo "</td>";
        echo '</tr>';
        echo '<tr>';
		echo '<td class="lightblue" width="15%">Break Duration</td>';
        echo '<td colspan="1" width="35%">';
        echo '   <select class="chosen-select" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 20%;" disabled>';
        echo ''.$breakTimeHourOptions.'';
        echo '   </select> : ';
        echo '   <select class="chosen-select" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 20%;" disabled>';
        echo ''.$breakTimeMinuteOptions.'';
        echo '   </select></br>';
        echo "</td>";
		echo '<td width="15%" class="lightblue">Label </td>';
		echo '<td width="35%" colspan="4">';
		echo '<select data-placeholder="Select label" class="chosen-select" multiple name="labelIds[]" id="labelIds" style="background-color:white; width: 100%;">
                        '. $lablestr .'
                    </select>';
		echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Miscellaneous Duty Type<span class="required">*</span></td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="MiscDutyType" id="MiscDutyType" style="background-color:white; width: 100%;">';
        echo ''.$MiscDutyTypeOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Duration<span class="required">*</span></td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="DurationHour" id="DurationHour" style="background-color: white; width: 40%;">';
        echo ''.$durationhourOptions.'';
        echo '   </select> : ';
        echo '   <select class="chosen-select" name="DurationMinute" id="DurationMinute" style="background-color: white; width: 40%;">';
        echo ''.$durationminuteOptions.'';
        echo '   </select></br>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Duty Colour</td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="ddldutycolour" id="ddldutycolour" style="background-color:white; width: 100%;">';
        echo ''.$ColourOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Scheduling Team<span class="required">*</span></td>';
        echo '<td width="35%"> ';
        echo '   <select class="chosen-select" name="ddldutyteam" id="ddldutyteam" style="background-color:white; width: 100%;">';
        echo ''.$TeamOptions.'';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td></td>';
        echo '<td colspan="10"><input name="submitbtn" id="submitbtn" type="button" value="'.$buttonvalue.'"></td>';
        echo '</tr>';
        echo '<input type="hidden" name="dutyid" id ="dutyid" value="'.$intID.'">';
        if(!empty($row))
        {
            echo '<input type="hidden" name="olddutyname" value="'. $row['DutyName'].'">';
        }
        echo '<input type="hidden" name="MiscDutyType" id="MiscDutyType" value="'.$intDutyType.'">';
    }
    else {
        echo '<tr>';
        echo '<td class="lightblue">Misclleneous Duty Name<span class="required">*</span></td>';
        echo '<td colspan="3">';
        echo '<input id="dutyname" name="dutyname" type="text" size="55" Maxlength="50" value="'.$dutyName.'" disabled>';
        echo '<input id="olddutyname" name="olddutyname" type="text" size="55" Maxlength="50" value="'.$dutyName.'" disabled hidden>';
        echo '<span style="float:right; padding: 2px;"><input type="checkbox" id="nightCheckbox" name="nightCheckbox" style="vertical-align: middle;" value="" '.$isNightDutyChecked.' disabled>';
        echo '<lable for="nightCheckbox">Night</lable></span>';        
        echo "</td>";
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Break Duration</td>';
        echo '<td colspan="1" width="35%">';
        echo '   <select class="chosen-select" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 20%;" disabled>';
        echo ''.$breakTimeHourOptions.'';
        echo '   </select> : ';
        echo '   <select class="chosen-select" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 20%;" disabled>';
        echo ''.$breakTimeMinuteOptions.'';
        echo '   </select></br>';
        echo "</td>";
		echo '<td width="15%" class="lightblue">Label </td>';
		echo '<td width="35%" colspan="4">';
		echo '<select data-placeholder="Select label" class="chosen-select" multiple name="labelIds[]" id="labelIds[]" style="background-color:white; width: 100%;" disabled>
                        '. $lablestr .'
                    </select>';
		echo '</td>';
		echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Miscellaneous Duty Type<span class="required">*</span></td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="MiscDutyType" id="MiscDutyType" style="background-color:white; width: 100%;" disabled>';
        echo ''.$MiscDutyTypeOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Duration<span class="required">*</span></td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="DurationHour" id="DurationHour" style="background-color: white; width: 40%;" disabled>';
        echo ''.$durationhourOptions.'';
        echo '   </select> : ';
        echo '   <select class="chosen-select" name="DurationMinute" id="DurationMinute" style="background-color: white; width: 40%;" disabled>';
        echo ''.$durationminuteOptions.'';
        echo '   </select></br>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Duty Colour</td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="ddldutycolour" id="ddldutycolour" style="background-color:white; width: 100%;" disabled>';
        echo ''.$ColourOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Scheduling Team<span class="required">*</span></td>';
        echo '<td width="35%"> ';
        echo '   <select class="chosen-select" name="ddldutyteam" id="ddldutyteam" style="background-color:white; width: 100%;" disabled>';
        echo ''.$TeamOptions.'';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</form>';
    echo '</div>';
}
?>

<script type="text/javascript">
    $(document).ready(function(){
        setTimeout(function() {
            $("#labelIds").chosen("destroy");
            $("#labelIds").chosen({no_results_text: "Oops, nothing found!", max_selected_options: 6});
        }, 100);
        var selectedTeamId = $('#ddlRotaTeams').val();
        if(selectedTeamId){
            $("#ddldutyteam").val(selectedTeamId);
            $("#ddldutyteam").prop('disabled', true);
        }

        $("#facebox").addClass('customFaceboxClose');
        if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
            customAlert('You do not have modify privileges for this form.');
        }
        else {
            $('#dutyname').on('keyup',function() {
                const ControlName = "#dutyname";
                const FieldName = "Miscellaneous Duty Name";
                TextTypeValidation(ControlName, FieldName);
            });
            $('#submitbtn').click(function(){
                var dutyname = $("#dutyname").val();
                var olddutyname = $("#olddutyname").val();
                var dutyid = $("#dutyid").val();
                var dutytypeid = $("#MiscDutyType").val();
                var dutyteam = $("#ddldutyteam").val();
                var DurationHour = $("#DurationHour").val();
                var DurationMinute = $("#DurationMinute").val();
                var breakTimeHour = $("#breakTimeHour").val();
                var breakTimeMinute = $("#breakTimeMinute").val();
                var dutycolour = $("#ddldutycolour").val();
				var labelIds = $("#labelIds").val();
                var strHistory = '';
	            let warningMsg = '';
                if(dutyname == ''){
                    warningMsg += 'Please enter the Miscellaneous duty name.<br/>';
                }
                if (dutyteam == 0 || dutyteam == "0") {
                    warningMsg += 'Please select team from Scheduling Teams dropdown.<br/>';
                }
                if(dutytypeid == '' || dutytypeid == 0 || dutytypeid == '0'){
                    warningMsg += 'Please select Miscellaneous duty type.<br/>';
                }
                DurationHour = DurationHour.length == 1 ? "0" + DurationHour : DurationHour;
                breakTimeHour = breakTimeHour.length == 1 ? "0" + breakTimeHour : breakTimeHour;
                DurationMinute = DurationMinute.length == 1 ? "0" + DurationMinute : DurationMinute;
                breakTimeMinute = breakTimeMinute.length == 1 ? "0" + breakTimeMinute : breakTimeMinute;
                if (warningMsg != '') {
                    customAlertByModel('Warning:<br/>' + warningMsg);
                    return;
                }

                // Validation for Duty name should have a value not spaces
                var ControlName = "#dutyname";
                var Fieldname= 'Duty Name';
                var MinLength = 1;
                var MaxLength = 50;
                var Valid = ValidateText(ControlName,MinLength,MaxLength,Fieldname);
                if(Valid == 0){
                    return;
                }
                if (dutyid > 0 && olddutyname != dutyname) {
                    var strHistory = '-- Duty Name changed from [' + olddutyname + '] to [' + dutyname + ']';
                }
                var thisdutytype = <?=$intDutyType?>;
                var isNightDuty = $("#nightCheckbox").is(":checked") ? 1 : 0;
                $.ajax({
                    url: "page-includes/master-duties/SaveMiscellaneousDuty.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        'DutyID': dutyid,
                        'DutyName':dutyname,
                        'DutyTypeID': dutytypeid,
                        'History': strHistory,
                        'dutyteam': dutyteam,
                        'durationhour': DurationHour,
                        'durationminute': DurationMinute,
                        'breakTimeHour': breakTimeHour,
                        'breakTimeMinute': breakTimeMinute,
                       'dutycolour': dutycolour,
                        'labelIds': labelIds,
                        'isNightDuty' : isNightDuty
                    },
                    success: function(data) {
                        if (data.sqlstatus == 1) {
                            ListMiscellaneousDuties(dutytypeid, dutyid, 1);
                            $.facebox.close();
                        }
                        else {
                            customAlertByModel(data.sqlstatusstring);
                        }
                    },
                    error:function(x,e) {
                        if (x.status==0) {
                            customAlert('You are offline!!<br/>Please Check Your Network.');
                        } else if(x.status==404) {
                            customAlert('Requested URL not found.');
                        } else if(x.status==500) {
                            customAlert('Internal Server Error.');
                        } else if(e=='parsererror') {
                            customAlert('Error.<br/>Parsing JSON Request failed.');
                        } else if(e=='timeout'){
                            customAlert('Request Time out.');
                        } else {
                            customAlert('Unknown Error.<br/>'+x.responseText);
                        }
                    }
                });
            });
        }
    });

    $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    $(function() {
        $( "button" )
            .button()
    });

    // Update the Week dropdown on the changes of Year value
    $("#yearavaialblefrom").chosen().change(

        function() {
            var yearAvaialbleFrom = $("#yearavaialblefrom").val();
            var selectId = '#weekavaialblefrom';
            updatedWeekAvailable(yearAvaialbleFrom,selectId);
        });

    // Update the Week dropdown on the changes of Year value
    $("#yearavaialbleto").chosen().change(
        function() {
            var yearAvaialbleTo = $("#yearavaialbleto").val();
            var selectId = '#weekavaialbleto';
            updatedWeekAvailable(yearAvaialbleTo,selectId);
        });

    // Update the Week dropdown on the changes of Year value
    function updatedWeekAvailable(year,selectId){
        $.post("page-includes/master-duties/getweekdropdown.php", {
                year: year
            },
            function(data){
                // update thw week options
                $(selectId).empty();
                $(selectId).append(data.Weeks);
                $(selectId).trigger("chosen:updated");
            }
        );
    }

    //Refresh the miscellaneous duties list
    function ListMiscellaneousDuties (listtype, id, showedit){
        let MiscDutyType = $.cookie("searchdutyMisctype") != '' ? $.cookie("searchdutyMisctype") : $('#HiddenDutyType').val()
        var CookieName = "masterdutiesshowarchived";
        var archived = ($.cookie(CookieName) || 0);
        $.post("page-includes/master-duties/ListMiscellaneousDuties.php", {
                id: id,
                archived: 0,
                listtype: MiscDutyType,
				strsearch: $.cookie("searchmiscdutyname")
            },
            function(data,status){
                $('#dutieslistdiv0').html(data);
				$('#duties').scrollTop(Math.abs($.cookie("miscDutyListingPosTop")));
				$('#duties').scrollLeft(Math.abs($.cookie("miscDutyListingPosLeft")));
                GoToRow(id);
            }
        );
    }

    $('.spanFaceBoxClose').on('click', function(){
        $.facebox.close();
    });


</script>