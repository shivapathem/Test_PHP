<style type="text/css">
.ui-timepicker-wrapper{ z-index: 999999 !important;}
</style>
<?php

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../../function-includes/masterduty_functions.php';
require_once __DIR__ . '/../../../../function-includes/DB_Functions.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/AllocationRepository.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
$repository = new AllocationRepository();

$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = isset($_SESSION['user']['AreaID']) ? $_SESSION['user']['AreaID'] : 0;
$intTeamID = $request->get('teamId', 0);

$rsColours = GetDutyColourList($intTeamID);
$rsColoursArray = json_decode($rsColours,true);
$defaultColourId = 0;
if(!empty($rsColoursArray)){
    foreach($rsColoursArray as $mastColourKey => $mastColourVal){
        if($mastColourVal['IsDefaultColour'] == 1){
            $defaultColourId = $mastColourVal['MasterDutyColourID'];
            break;
        }
    }
}
$allocationsDutyId = $request->get("allocationsDutyId") ?? 0;
$allocationsSpId = $request->get("allocationsSpId") ?? 0;
$allocationsDate = $request->get("allocationsDate") ?? '';
$allocationsSchPer = $request->get("allocationsSchPer") ?? 0;
if($request->get("ID") == '0'){
    $intDutyColourID = 0;
    if($defaultColourId > 0){
        $intDutyColourID = $defaultColourId;
    }
} else {
    $row = $service->getAllocationByID($allocationsDutyId);
    $rowDutyBreakTime = isset($row['dutyBreakTime']) ? $row['dutyBreakTime'] : 0;
    $rowDutyProgramId = isset($row['dutyProgramId']) ? $row['dutyProgramId'] : 0;
    $rowDutyProgramId2 = isset($row['DutyProgramId2']) ? $row['DutyProgramId2'] : 0;
    $rowDutyProgramId3 = isset($row['DutyProgramId3']) ? $row['DutyProgramId3'] : 0;
    $rowDutyProgramId4 = isset($row['DutyProgramId4']) ? $row['DutyProgramId4'] : 0;
    $rowDutyProgramId5 = isset($row['DutyProgramId5']) ? $row['DutyProgramId5'] : 0;
    $rowDutyProgramId6 = isset($row['DutyProgramId6']) ? $row['DutyProgramId6'] : 0;
    $rowDutyColorId = isset($row['dutyColorId']) ? $row['dutyColorId'] : 0;
    $rowDutyName = isset($row['DutyName']) ? $row['DutyName'] : '';
    $rowSchedulingTeamId = isset($row['SchedulingTeamId']) ? $row['SchedulingTeamId'] : 0;
    $rowSchedulingPersonId = (isset($row['SchedulingPersonID']) && $row['SchedulingPersonID'] > 0) ? $row['SchedulingPersonID'] : $allocationsSchPer;
    $rowId = isset($row['ID']) ? $row['ID'] : 0;
    $rowIsPublished = isset($row['isPublished']) ? $row['isPublished'] : 0;
    $rowIsEdited = isset($row['isEdited']) ? $row['isEdited'] : 0;
    $rowStartTime = isset($row['StartTime']) ? $row['StartTime'] : 0;
    $rowEndTime = isset($row['EndTime']) ? $row['EndTime'] : 0;
    $rowDuration = isset($row['Duration']) ? $row['Duration'] : 0;
    $rowDutyDate = isset($row['DutyDate']) ? $row['DutyDate'] : '';
    $rowDutyComments = isset($row['Comments']) ? $row['Comments'] : '';
    $rowIsNeedCovering = isset($row['IsNeedCovering']) ? $row['IsNeedCovering'] : 1;
    $rowIsOverrideOver12 = isset($row['IsOverrideOver12']) ? $row['IsOverrideOver12'] : 1;
    if($rowDutyName == 'U'){
        $breakTime = ($rowDutyBreakTime != 0 ? $rowDutyBreakTime : 4500);
    } else {
        $breakTime = $rowDutyBreakTime;
    }
	$disableDurationStr = (($rowStartTime == 0) && ($rowEndTime == 0)) ? 'disabled' : '';
    $labelIds = array_filter([$rowDutyProgramId, $rowDutyProgramId2, $rowDutyProgramId3, $rowDutyProgramId4, $rowDutyProgramId5, $rowDutyProgramId6]);
    $intDutyColourID = $rowDutyColorId;
    if($defaultColourId > 0 && ($rowDutyColorId == '' || $rowDutyColorId == 0)){
        $intDutyColourID = $defaultColourId;
    }

    $intBreakTimeHour = floor($breakTime / 3600);
    $intBreakTimeMinute = floor(($breakTime / 60) % 60);

    $intAreaID = $_SESSION['user']['AreaID']??0;
    $intDutyTypeID = 0;
    $intArchived = 0;
    $strTeams = $rowSchedulingTeamId;
    $strsearch = '';

    $getMiscDutyList = ListAllMasterDuties($intAreaID, $intDutyTypeID, $intArchived, $strTeams, $strsearch);
    $getMiscDutyList = json_decode($getMiscDutyList,true);
    $disabledStartEndTime = 'No';
    if(!empty($getMiscDutyList)){
        foreach($getMiscDutyList as $miscK => $miscV){
            $miscDutyName = (isset($miscV['DutyName'])) ? $miscV['DutyName'] : '' ;
            if($miscDutyName == $rowDutyName){
                $disabledStartEndTime = 'Yes';
                break;
            } else {

            }
        }
    }

    if (substr(trim($rowDutyName), 0, 1)=="-"){
        $disabledStartEndTime = 'Yes';
    } else if(substr(trim($rowDutyName), 0, 2)=="--"){
        $disabledStartEndTime = 'Yes';
    }
}

$breakTimehourOptions = PopulateHoursDropDown($intBreakTimeHour);
$breakTimeminuteOptions = PopulateMinutesDropDown_limited($intBreakTimeMinute);

$rsAreaTeams = GetUserAreaTeamsList ($intUserID);
$TeamOptions = TeamListDropDown ($rsAreaTeams,$intTeamID);

$ColourOptions = PopulateDutyColoursDropDown($rsColours, $intDutyColourID);

$prog = getAllLabels();
$allProg = json_decode($prog);
$lablestr = '';
foreach ($allProg as $key => $value){
    if(in_array($value->ID, $labelIds)){
        $lablestr   .='<option value="'.$value->ID.'" selected>'.$value->Programme .'</option>';
    } else {
        $lablestr   .='<option value="'.$value->ID.'">'.$value->Programme .'</option>';
    }
}
?>
<div>
<div style="width: 1%; float:left; color: white;">.</div>
<div style="width: 99%; float:right; text-align: center; font-size: 11px; color: #FF0000; display: none;" id="errorDiv"></div>
</div>
<form id="editDutyForm" onsubmit="return false;">
<table id="dutynewedittabledetail" class="redtable smalltable bluetable" width="100%">
        <tbody>
            <tr>
                <td>Duty Name<span class="required">*</span></td>
                <td>
                    <?php if($request->get("ID") == '0'){?>
                        <input class="w-100" type="text" name="DutyName" id="DutyName" onkeyup="dutyNameValidation('#DutyName','Duty Name',event);" onfocusout=checkMiscellaneousDuty(<?php echo $request->get("teamId"); ?>) placeholder="U" maxlength="50">
                    <?php } else {?>
                        <input class="w-100" type="text" name="DutyName" id="DutyName" value="<?php echo $rowDutyName; ?>" onkeyup="dutyNameValidation('#DutyName','Duty Name',event);" onfocusout="checkMiscellaneousDuty(<?php echo $intTeamID; ?>)" maxlength="50">
                    <?php }?>
                </td>
                <td width="15%">Scheduling Team<span class="required">*</span></td>
                <td width="35%">
                     <select class="chosen-select" disabled="disabled" name="SchedulingTeamID" id="ddldutyteam" style="background-color:white; width: 100%;">
                        <?php echo $TeamOptions; ?>
                     </select>
                </td>
            </tr>

            <tr>
                <td width="15%">Start Time<span class="required" id="startTimeStar">*</span></td>
                <td width="35%">
                    <?php if($request->get("ID") == '0'){?>
                        <input id="starttimemasterduty" name="StartTime" type="text" class="time ui-timepicker-input" size="10" autocomplete="off" onchange="calculateDuration()" maxlength="5">
                    <?php } else {?>
                        <input id="starttimemasterduty" name="StartTime" type="text" class="time ui-timepicker-input" size="10" value="<?php echo $service->secondsIntoTime($rowStartTime) ?>" autocomplete="off" onchange="calculateDuration()" maxlength="5">
                    <?php }?>
                </td>
                <td width="15%">End Time<span class="required" id="endTimeStar">*</span></td>
                <td width="35%">
                    <?php if($request->get("ID") == '0'){?>
                        <input id="endtimemasterduty" name="EndTime" type="text" class="time ui-timepicker-input" size="10" autocomplete="off" onchange="calculateDuration()" maxlength="5">
                    <?php } else {?>
                        <input id="endtimemasterduty" name="EndTime" type="text" class="time ui-timepicker-input" size="10" value="<?php echo $service->secondsIntoTime($rowEndTime) ?>" autocomplete="off" onchange="calculateDuration()" maxlength="5">
                    <?php }?>
                </td>
            </tr>

            <tr>
                <td>Break Duration</td>
                <td>
                    <?php if($request->get("ID") == '0'){?>
                        <select class="chosen-select" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 40%;" onchange="updateCalculatedDuration(0)" disabled>
                            <?php echo $breakTimehourOptions; ?>
                        </select> :
                        <select class="chosen-select" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 40%;" onchange="updateCalculatedDuration(0)" disabled>
                            <?php echo $breakTimeminuteOptions; ?>
                        </select>
                    <?php } else {?>
                        <select class="chosen-select" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 40%;" onchange="updateCalculatedDuration(<?php echo $rowId; ?>);$('#endtimemasterduty').trigger('change');" <?php echo $disableDurationStr; ?>>
                            <?php echo $breakTimehourOptions; ?>
                        </select> :
                        <select class="chosen-select" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 40%;" onchange="updateCalculatedDuration(<?php echo $rowId; ?>);$('#endtimemasterduty').trigger('change');"  <?php echo $disableDurationStr; ?>>
                            <?php echo $breakTimeminuteOptions; ?>
                        </select>
                    <?php }?>
                </br>
                </td>
                <td>Duration<br><span class="font9">(Exc Break)</span></td>
                <td>
                    <?php if($request->get("ID") == '0'){?>
                        <input name="Duration" id="Duration" type="text" class="time ui-timepicker-input" size="10" autocomplete="off" onfocusout="setDurationValue()">
                    <?php } else {?>
                        <?php if($disabledStartEndTime == 'Yes'){?>
                            <input name="Duration" id="Duration" type="text" class="time ui-timepicker-input" value="<?php echo $service->secondsIntoTime($rowDuration); ?>" size="10" autocomplete="off"  onfocusout="setDurationValue()">
                        <?php } else { ?>
                            <input name="Duration" id="Duration" type="text" class="time ui-timepicker-input" value="<?php echo $service->secondsIntoTime($rowDuration-$rowDutyBreakTime); ?>" size="10" autocomplete="off"  onfocusout="setDurationValue()">
                        <?php }?>
                    <?php }?>
                </td>

            </tr>

            <tr>
                <td width="15%">Duty Colour<span class="required">*</span></td>
                <td width="35%" id="dutydiv">
                     <select class="chosen-select" name="dutyColorId" id="ddldutycolour" style="background-color:white; width: 100%;">
                        <?php echo $ColourOptions; ?>
                     </select>
                </td>
                <td width="15%">Duty Label</td>
                <td width="35%">
                    <select multiple data-placeholder="Select label" class="chosen-select" name="labelIds[]" id="labelIds" style="background-color:white; width: 100%;">
                        <?php echo $lablestr; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="15%">Doesn't Need Covering</td>
                <td>
                    <input id="isNeedCovering" name="isNeedCovering" type="checkbox" <?php echo isset($rowIsNeedCovering) && $rowIsNeedCovering == 0 ? 'checked' : '' ?>>
                </td>
                <td width="15%"> <span class="isOverrideOver12Td"> Disable Over 12 </span></td>
                <td>
                    <input class="isOverrideOver12Td" id="isOverrideOver12" name="isOverrideOver12" type="checkbox" <?php echo isset($rowIsOverrideOver12) && $rowIsOverrideOver12 == 0 ? 'checked' : '' ?>>
                </td>
            </tr>
            <?php if(isset($rowId)) {
                $request->request->set('id', $rowId);
                $comments = $repository->getAllocationComments($request);
                $dDcomments = isset($comments['DutyComments']) ? $comments['DutyComments'] : $rowDutyComments;
                $personComments = isset($comments['PersonComments']) ? $comments['PersonComments'] : '';

                if (strpos($dDcomments, '__COMMENT_SEPARETOR__') !== false) {
                    $commentArr = explode('__COMMENT_SEPARETOR__', $dDcomments);
                    $personComments = isset($commentArr[0]) ? trim($commentArr[0]) : '';
                    $dDcomments = isset($commentArr[1]) ? trim($commentArr[1]) : '';
                }
            ?>
            <tr>
                <td class="padding-5">Duty Comments</td>
                <td>
                    <textarea name="DutyComments" id="DutyCommentsEditDuty" cols="35" rows="5" maxlength="500" placeholder="Please enter Duty Comments" ><?php echo htmlspecialchars($dDcomments ?? ''); ?></textarea>
                    Characters Remaining : <span id="remainDutyCommentsEditDuty">500</span>
                </td>
                <td class="padding-5">Person Comments</td>
                <td>
                    <textarea <?php echo ($rowSchedulingPersonId == 0 ? 'disabled' : '')  ?> name="PersonComments" id="PersonCommentsEditDuty" cols="35" rows="5" maxlength="500" <?php if($rowSchedulingPersonId != 0)  { ?> placeholder="Please enter Person Comments" <?php } ?>><?php echo htmlspecialchars($personComments ?? ''); ?></textarea>
                    <?php if($rowSchedulingPersonId != 0)  { ?> Characters Remaining : <span id="remainPersonCommentsEditDuty">500</span> <?php  } ?>
                </td>
            </tr>
            <?php } ?>

            <?php if(($request->get('pdlStartTimeHrMin') != '') && ($request->get('pdlEndTimeHrMin') != '') && (($request->get('pdlStartTime') != 0) || ($request->get('pdlEndTime') != 0))){?>
                <tr>
                    <td width="100%" colspan="4" style="height: 35px; vertical-align: middle; color:#109146; font-weight: bold;">PDL: <?php echo $request->get('pdlStartTimeHrMin')?>-<?php echo $request->get('pdlEndTimeHrMin')?> | <?php echo $request->get('pdlDurationHrMin')?></td>
                </tr>
            <?php }?>

            <?php if($request->get("ID") == '0'){?>
                <input type="hidden" name="DutyDate" value="<?php echo (new Carbon($request->get("dutyDate")))->format('Y-m-d H:i:s'); ?>">
                <input type="hidden" name="SchedulingPersonID" value="<?php echo $request->get("schedulingPersonId"); ?>">
                <input type="hidden" name="SchedulingTeamID" value="<?php echo $request->get("teamId"); ?>">
                <input type="hidden" name="iday" value="<?php echo $request->get("iDay"); ?>">
                <input type="hidden" name="weekNumber" value="<?php echo $request->get("weekNum"); ?>">
                <input type="hidden" name="action" value="add">
            <?php } else {?>
                <input type="hidden" name="DutyDate" value="<?php echo (new Carbon($rowDutyDate))->format('Y-m-d H:i:s'); ?>">
                <input type="hidden" name="DutyID" id="DutyID" value="<?php echo $rowId; ?>">
                <input type="hidden" name="ID" id="ID" value="<?php echo $request->get("ID"); ?>">
                <input type="hidden" name="SchedulingPersonID" value="<?php echo $rowSchedulingPersonId; ?>">
                <input type="hidden" name="SchedulingTeamID" value="<?php echo $request->get("teamId"); ?>">
                <input type="hidden" name="isPublished" value="<?php echo $rowIsPublished; ?>">
                <input type="hidden" name="isEdited" value="<?php echo $rowIsEdited; ?>">
                <input type="hidden" name="currDurationVal" id="currDurationVal" value="<?php echo $service->secondsIntoTime($rowDuration); ?>">
                <input type="hidden" name="miscDuty" id="miscDuty">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="dutyTypehidden" id="dutyTypehidden" value="<?php if($disabledStartEndTime == 'Yes'){echo 0;}else{echo 1;}?>">
                <input type="hidden" name="ID" id="ID" value="<?php echo $request->get("ID"); ?>">
            <?php }?>

        </tbody>
    </table>

    <!-- Allow form submission with keyboard without duplicating the dialog button -->
    <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    <input type="hidden" id="startEndDisable" value="<?php echo $disabledStartEndTime?>">
    <input type="hidden" id="pdlStartTime" value="<?php echo $request->get("pdlStartTime"); ?>">
    <input type="hidden" id="pdlEndTime" value="<?php echo $request->get("pdlEndTime"); ?>">
    <input type="hidden" id="allocationsDutyId" name="allocationsDutyId" value="<?php echo $allocationsDutyId; ?>">
    <input type="hidden" id="allocationsSpId" name="allocationsSpId" value="<?php echo $allocationsSpId; ?>">
    <input type="hidden" id="allocationsDate" name="allocationsDate" value="<?php echo $allocationsDate; ?>">
    <input type="hidden" id="allocationsSchPer" name="allocationsSchPer" value="<?php echo $allocationsSchPer; ?>">
    <input type="hidden" value="<?php echo htmlspecialchars($dDcomments); ?>" id="oldDutyComments" name="oldDutyComments">
    <input type="hidden" value="<?php echo htmlspecialchars($personComments); ?>" id="oldPersonComments" name="oldPersonComments">
</form>
<script type="text/javascript">
var maxcharsComments = 500;
$( document ).ready(function() {
    $(function() {
        $('#starttimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });

        $('#endtimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });

        $('#Duration').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });

        $('#ddldutycolour').trigger("chosen:updated");
        setTimeout(function() {
            $("#labelIds").chosen("destroy");
            $("#labelIds").chosen({no_results_text: "Oops, nothing found!", max_selected_options: 6});
        }, 100);

        $('#DutyCommentsEditDuty').keyup(function () {
            commentsTextCount('remainDutyCommentsEditDuty', $(this));
        });
        $('#PersonCommentsEditDuty').keyup(function () {
            commentsTextCount('remainPersonCommentsEditDuty', $(this));
        });
        commentsTextCount('remainDutyCommentsEditDuty',  $('#DutyCommentsEditDuty'));
        commentsTextCount('remainPersonCommentsEditDuty', $('#PersonCommentsEditDuty'));
    });

    function commentsTextCount(textId, $textArea) {
        if($($textArea).length == 0) {
            return;
        }
        var tlength = $($textArea).val().length;
        $($textArea).val($($textArea).val().substring(0, maxcharsComments));
        var tlength = $($textArea).val().length;
        remaincomments = maxcharsComments - parseInt(tlength);
        $('#' + textId).text(remaincomments);
    }

    if($('#DutyName').val() == 'U'){
        $('.ui-dialog-buttonset').find('button:nth-child(1)').html('Create Duty');
		document.getElementById("breakTimeHour").disabled   = false;
        document.getElementById("breakTimeMinute").disabled = false;
    }

    var dutySelId = '<?php echo $intDutyColourID?>';
    $("#ddldutycolour").val(dutySelId).trigger("chosen:updated");

    $(function() {
        <?php if($request->get("ID") != '0'){?>
            var breakTimeHr = '<?php echo $intBreakTimeHour?>';
            var breakTimeMin = '<?php echo $intBreakTimeMinute?>';
            $("#breakTimeHour").val(breakTimeHr).trigger("chosen:updated");
            $("#breakTimeMinute").val(breakTimeMin).trigger("chosen:updated");
        <?php }?>
    });

    var startTimeEnabledDisabled = '<?php echo $disabledStartEndTime?>';
    if(startTimeEnabledDisabled == 'Yes'){
        $("#starttimemasterduty").attr("disabled","disabled").val('--:--');
        $("#endtimemasterduty").attr("disabled","disabled").val('--:--');
        $('#startTimeStar').hide();
        $('#endTimeStar').hide();
    } else {
        $("#Duration").attr("disabled","disabled");
    }

    $('#starttimemasterduty').on('changeTime', function() {
        initializeOverrideOver12()
    });

    $('#endtimemasterduty').on('changeTime', function() {
        initializeOverrideOver12()
    });
    initializeOverrideOver12();
});

function initializeOverrideOver12() {
    if('<?php echo $disabledStartEndTime?>' != 'Yes') {
        var start_time = $('#starttimemasterduty').val();
        var end_time = $('#endtimemasterduty').val();
        var diff = (new Date("1970-1-1 " + end_time) - new Date("1970-1-1 " + start_time) ) /1000/60;
        var hour = parseInt(diff/60);
        if(hour < 0 || hour.toString() === '-0') {
            diff = (new Date("1970-1-2 " + end_time) - new Date("1970-1-1 " + start_time) ) /1000/60;
            hour = parseInt(diff/60);
        }
        var min = diff%60;
        if(min < 10){
           min = "0" + min;
        }
        let duration = parseInt(hour + '' + min);
        duration > 1200 ? handleOverrideOver12('enable') : handleOverrideOver12('disable');
    } else {
        handleOverrideOver12('disable');
    }
}

function handleOverrideOver12(status) {
    status == 'disable' ? $('.isOverrideOver12Td').hide() : $('.isOverrideOver12Td').show();
}

function dutyNameValidation(ControlName, FieldName, event) {
    var ControlVal = $(ControlName).val();
    var RGEXToCheck = /^([a-zA-Z0-9+&!()|\[\]\- ,.=()*_:{}?]{0,500})$/;
    var key = window.event.keyCode;
    var Flag = RGEXToCheck.test(ControlVal);
    var FlagforDutyName=!/leave|LEAVE|Leave|sick|SICK|Sick/i.test(ControlVal);

    if ((FlagforDutyName == false) && (Flag != false)) {
            $(ControlName).val(ControlVal.slice(0, -1));
            $('#errorDiv').html("Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a different name.");
            $('#errorDiv').show();
    }
    if ((FlagforDutyName == false) && (Flag == false) && (key != 8)) {
            $(ControlName).val(ControlVal.slice(0, -1));
            $('#errorDiv').html("Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a different name.Only specified symbols (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) are permitted in " + FieldName + ".");
            $('#errorDiv').show();
    }
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val(ControlVal.slice(0, -1));
           $('#errorDiv').html("Only specified symbols (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) are permitted in " + FieldName + ".");
           $('#errorDiv').show();
        }
    } else {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $('.ui-dialog-buttonset').find('button:nth-child(1)').trigger('click');
        }
    }
}
</script>