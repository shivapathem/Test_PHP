<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
use Symfony\Component\HttpFoundation\Request;

include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/masterduty_functions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../page-includes/allocations/weekly/service/AllocationService.php';
include_once '../../../page-includes/allocations/weekly/service/AllocationRepository.php';

$service = new AllocationService();
$request = Request::createFromGlobals();
$repository = new AllocationRepository();
$TeamId = empty($_REQUEST["teamId"]) ? 0 : $_REQUEST["teamId"];
$DutyId = empty($_REQUEST["dutyid"]) ? 0 : $_REQUEST["dutyid"];
$role = empty($_REQUEST["role"]) ? 0 : $_REQUEST["role"];
$ParentId = empty($_REQUEST["parentid"]) ? 0 : $_REQUEST["parentid"];
$currDate = $_REQUEST["currdate"];
$miscduty = empty($_REQUEST["miscduty"]) ? '' : 'disabled';
$miscflag = empty($_REQUEST["miscduty"]) ? 0 : $_REQUEST["miscduty"];
$disabledStartEndTime = 'No';
$Duration = 0;
$labelIds = [];
$DutyDetails = [
    'DutyColourID' => 0,
    'StartTime' => 0,
    'EndTime' => 0,
    'DutyName' => 'U'
];
$durationreadonly = 'readonly';
$Breakdurationreadonly = '';
$dailyscreenDisabledDuration = 'dailyscreenDisabledDuration';
$miscduty ='';

if($DutyId==0 || $DutyId==''){
    $SchedulingPersonID = $_REQUEST["scheduledPersonId"];
    $BreakTime = 4500;
    $allocationsID = $ParentId;
    $StartDate = $currDate;
    $EndDate = $currDate;
    $DutyDate = $currDate;
} else {

    $DutyDetails = GetDutyDetails($DutyId);
    $allocationsID = $DutyDetails['AllocationsID'];
    $allocationsSPID = $DutyDetails['AllocationsSPID'];
    $SchedulingPersonID = $DutyDetails['SchedulingPersonID'] ?? 0;
    $rowDutyProgramId = $DutyDetails['DutyProgramID1'];
    $rowDutyProgramId2 = $DutyDetails['DutyProgramID2'];
    $rowDutyProgramId3 = $DutyDetails['DutyProgramID3'];
    $rowDutyProgramId4 = $DutyDetails['DutyProgramID4'];
    $rowDutyProgramId5 = $DutyDetails['DutyProgramID5'];
    $rowDutyProgramId6 = $DutyDetails['DutyProgramID6'];
    $labelIds = array_filter([$rowDutyProgramId, $rowDutyProgramId2, $rowDutyProgramId3, $rowDutyProgramId4, $rowDutyProgramId5, $rowDutyProgramId6]);
    $StartDate = $DutyDetails['StartDate'];
    $EndDate = $DutyDetails['EndDate'];
    $DutyDate = $DutyDetails['DutyDate'];
    $rowIsNeedCovering = $DutyDetails['IsNeedCovering'];
    $rowIsOverrideOver12 = $DutyDetails['IsOverrideOver12'] ?? 1;
    $Comments = $DutyDetails['Comments'];
	if (($DutyDetails['AD_DutyBreak'] == NULL) || ($DutyDetails['AD_DutyBreak'] == '')){
		$BreakTime = 4500;
	} else {
		$BreakTime = $DutyDetails['AD_DutyBreak'];
	}
    $Duration = $DutyDetails['Duration'];
	if (($DutyDetails['DutyName'] == 'U' || $miscflag==1)){
		$disabledStartEndTime = 'Yes';
	} else {
        $disabledStartEndTime = 'No';
    }

    if($miscflag==1){
        $durationreadonly = '';
        $Breakdurationreadonly = 'disabled';
        $dailyscreenDisabledDuration = '';
    }else{
        $durationreadonly = 'readonly';
        $Breakdurationreadonly = '';
        $dailyscreenDisabledDuration = 'dailyscreenDisabledDuration';
        $miscduty ='';
    }

}

$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$rsAreaTeams = GetUserAreaTeamsList($intUserID);
$TeamOptions = TeamListDropDown($rsAreaTeams, $TeamId);
$WeekNumber = $_REQUEST['WeekNumber'];
$iDay = $_REQUEST['iDay'];

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

        $intBreakTimeHour = intval((int)$BreakTime / 3600);
        $intBreakTimeMinute = intval(((int)$BreakTime % 3600) / 60);
        $breakTimehourOptions = PopulateHoursDropDown($intBreakTimeHour);
        $breakTimeminuteOptions = PopulateMinutesDropDown_limited($intBreakTimeMinute);

        $intDurationHour = intval((int)$Duration / 3600);
        $intDurationHour = strlen(trim($intDurationHour))== 1 ? "0".$intDurationHour : $intDurationHour;
        $intDurationMinute = intval(((int)$Duration % 3600) / 60);
        $intDurationMinute = strlen(trim($intDurationMinute))== 1 ? "0".$intDurationMinute : $intDurationMinute;
        $intDutyDuration = $intDurationHour . ':' . $intDurationMinute;

        $rsColours = GetDutyColourList($TeamId);
        $ColourOptions = PopulateDutyColoursDropDown($rsColours, $DutyDetails['DutyColourID'], $DutyId);

        $intstartHour = intval($DutyDetails['StartTime'] / 3600);
        $intstartHour = strlen(trim($intstartHour))== 1 ? "0".$intstartHour : $intstartHour;
        $intstartMinute = intval(($DutyDetails['StartTime'] % 3600) / 60);
        $intstartMinute = strlen(trim($intstartMinute))== 1 ? "0".$intstartMinute : $intstartMinute;
        $intstartTime   =   $intstartHour . ':' . $intstartMinute;
		if (!empty($_REQUEST["miscduty"])){
			$intstartTime   =   '--:--';
		}

        $intendHour = intval($DutyDetails['EndTime'] / 3600);
        $intendHour = strlen(trim($intendHour))== 1 ? "0".$intendHour : $intendHour;
        $intendMinute = intval(($DutyDetails['EndTime'] % 3600) / 60);
        $intendMinute = strlen(trim($intendMinute))== 1 ?  "0".$intendMinute : $intendMinute;
        $intendTime   =   $intendHour . ':' . $intendMinute;
		if (!empty($_REQUEST["miscduty"])){
			$intendTime   =   '--:--';
		}
?>
<script src="js/allocations/daily/editduty.js?v=<?php echo time(); ?>"></script>
<script src="js/allocations/daily/commonduty.js?v=<?php echo time(); ?>"></script>
<div class="popup">
    <div class="content">
        <div>
	    <form id="newunallocatedduty" method="post">
            <table id="jobnewedittable" class="smalltable bluetable" width="100%">
                <thead>
				<tr id ="bugtd">
                <td colspan="5" class ="messageerror error" align="center" ></td>
                </tr>
                <tr>
                    <th colspan="5">Edit Duty</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="4" class="hiddenErrorDiv" id="errorDivADaily"></td>
                </tr>
				 <tr>
                    <td colspan="4" class="hiddenErrorDiv" id="errorDiv"></td>
                </tr>
                <tr>
                    <td class="lightblue">Duty Name<span class="required">*</span></td>
                    <td colspan="3">
					<input id="eDutyname" name="eDutyname" type="text" size="50" value="<?php echo $DutyDetails['DutyName']?>" onfocusout="checkMiscellaneousDuty(<?php echo $TeamId; ?>)" required></td>
                </tr>
                <tr>
           <?php  echo '<td class="lightblue" width="15%">Start Time<span class="required"  id="startTimeStar">*</span></td>';
        echo '<td width="35%">';
        echo  '<input id="StartTime" name="StartTime" type="text" maxlength="5" class="ui-timepicker-input" size="10" value="'.$intstartTime.'" onChange="if(StartTime.value == 0){setTimeout(function(){StartTime.value ='."'00:00'".'; StartTime.blur(); }, 1000);}" autocomplete="off" onfocusout="calculateDuration()" '.$miscduty.'>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">End Time<span class="required" id="endTimeStar">*</span></td>';
        echo '<td width="35%">';
        echo  '<input id="EndTime" name="EndTime" type="text" maxlength="5" class="ui-timepicker-input" size="10" value="'.$intendTime.'" onChange="if(EndTime.value == 0){setTimeout(function(){EndTime.value ='."'00:00'".'; EndTime.blur(); }, 1000);}" autocomplete="off" onfocusout="calculateDuration()" '.$miscduty.'>';
        echo '</td>'; ?>
                </tr>
                <tr>
                    <td>Break Duration</td>
                    <td>
                 <?php  echo '   <select class="chosen-select" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 40%;" onchange="updateDailyCalculatedDuration('.$DutyId.')" '.$Breakdurationreadonly.'>';
        echo ''.$breakTimehourOptions.'';
        echo '   </select> : ';
        echo '   <select class="chosen-select" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 40%;" onchange="updateDailyCalculatedDuration('.$DutyId.')" '.$Breakdurationreadonly.'>';
        echo ''.$breakTimeminuteOptions.'';
        echo '   </select></br>'; ?>

                     </td>

                    <td>Duty Colour</td><td>
<?php
echo ' <select class="chosen-select" name="ddldutycolour" id="ddldutycolour" style="background-color:white; width: 100%;">';
        echo ''.$ColourOptions.'';
        echo '   </select>';
        echo ' <input type="hidden" name="dutyColourID" id="dutyColourID" value="'.$DutyDetails['DutyColourID'].'">'; ?>
                </td></tr>
                <tr>
                    <td>Scheduling Team</td>
                    <td>
                        <?php echo '   <select class="chosen-select bg-white chosen-selectMaxWidth" style="min-width:250px!important" name="scheduling_team" id="selteam">';
                        echo '' . $TeamOptions . ''; ?></td>

                    <td>Duration</td>
                    <td>
                        <input id="dutyduration" name="dutyduration" type="text" value="<?php echo $intDutyDuration ?>" autocomplete="off" <?php echo $durationreadonly?> class="<?php echo $dailyscreenDisabledDuration ?>" onfocusout="setDuration()">
                    </td>

                </tr>
                <tr>
                        <td>Select Label</td>
                        <td><?php echo '<select data-placeholder="Select label" class="chosen-select" multiple name="labelIds[]" id="labelIds" style="background-color:white;">
                        '. $lablestr .'
                    </select>';?></td>
                    <td>Doesn't Need Covering</td>
                    <td>
                        <input id="isNeedCovering" name="isNeedCovering" type="checkbox" <?php echo isset($rowIsNeedCovering) && $rowIsNeedCovering == 0 ? 'checked' : '' ?>>
                    </td>
                </tr>
                <?php if($DutyId != '0') {
                    $request->request->set('allocationsDutyId', $DutyId);
                    $request->request->set('id', $allocationsID);
                    $comments = $repository->getAllocationComments($request);
                ?>
                <tr>
                    <td class="padding-5">Duty Comments</td>
                    <td>
                        <textarea name="DutyComments" id="DutyCommentsEditDuty" cols="35" rows="5" maxlength="500" placeholder="Please enter Duty Comments" ><?php echo htmlspecialchars($Comments ?? ''); ?></textarea>
                        Characters Remaining : <span id="remainDutyCommentsEditDuty">500</span>
                    </td>
                    <td class="padding-5">Person Comments</td>
                    <td>
                        <textarea <?php echo $SchedulingPersonID == 0 ? 'disabled' : '' ?> name="PersonComments" id="PersonCommentsEditDuty" cols="35" rows="5" maxlength="500" <?php if($SchedulingPersonID != 0) { ?> placeholder="Please enter Person Comments" <?php } ?>><?php echo htmlspecialchars($comments['PersonComments'] ?? ''); ?></textarea>
                        <?php if($SchedulingPersonID != 0) { ?> Characters Remaining : <span id="remainPersonCommentsEditDuty">500</span> <?php } ?>
                    </td>
                </tr>
                <?php } ?>
                <tr class="isOverrideOver12Td" style="display: none;">
                    <td width="15%"> <span> Disable Over 12 </span></td>
                    <td colspan="3">
                        <input id="isOverrideOver12" name="isOverrideOver12" type="checkbox" <?php echo isset($rowIsOverrideOver12) && $rowIsOverrideOver12 == 0 ? 'checked' : '' ?>>
                    </td>
                </tr>
                <?php
                if((isset($_POST['pdlStartTime'])) && (isset($_POST['pdlEndTime'])) && (($_POST['pdlStartTime'] != 0) || ($_POST['pdlEndTime'] != 0))){
                    $dispPdlEndTime = $_POST['pdlEndTime'];
                    if($_POST['pdlStartTime'] > $_POST['pdlEndTime']){
                        $dispPdlEndTime = (86400 + $_POST['pdlEndTime']);
                    }
                ?>
                <tr>
                    <td colspan='4' style='height: 35px; vertical-align: middle; color:#109146; font-weight: bold;'>PDL: <?php echo $service->convertSecondsIntoTime($_POST['pdlStartTime'],':','No');?>-<?php echo $service->convertSecondsIntoTime($_POST['pdlEndTime'],':','No');?> | <?php echo $service->convertSecondsIntoTime(($dispPdlEndTime - $_POST['pdlStartTime']),'.','Yes');?></td>
                </tr>
                <?php }?>
                    <input type="hidden" name="allocationsID" id="allocationsID" value="<?php echo $allocationsID;?>">
                    <input type="hidden" name="WeekNumber" id="WeekNumber" value="<?php echo $WeekNumber;?>">
                    <input type="hidden" name="iDay" id="iDay" value="<?php echo $iDay;?>">
                    <input type="hidden" name="dutyId" id="dutyId" value="<?php echo $DutyId; ?>">
                    <input type="hidden" name="StartDate" id="StartDate" value="<?php echo $StartDate; ?>">
                    <input type="hidden" name="EndDate" id="EndDate" value="<?php echo $EndDate; ?>">
                    <input type="hidden" name="DutyDate" id="DutyDate" value="<?php echo $DutyDate; ?>">
                    <input type="hidden" name="role" id="role" value="<?php echo $role; ?>">
                    <input type="hidden" name="parentId" id="parentId" value="<?php echo $ParentId; ?>">
                    <input type="hidden" name="SchedulingPersonID" id="SchedulingPersonID" value="<?php echo $SchedulingPersonID; ?>">
                    <input type="hidden" name="currDate" id="currDate" value="<?php echo $currDate; ?>">
				    <input type="hidden" name="currDurationVal" id="currDurationVal">
					<input type="hidden" name="miscDuty" id="miscDuty">
                    <input type="hidden" id="pdlStartTime" value="<?php echo $_POST['pdlStartTime'] ?? ''; ?>">
                    <input type="hidden" id="pdlEndTime" value="<?php echo $_POST['pdlEndTime'] ?? ''; ?>">
                    <input type="hidden" value="<?php echo htmlspecialchars($Comments ?? ''); ?>" id="oldDutyComments" name="oldDutyComments">
                    <input type="hidden" value="<?php echo htmlspecialchars($comments['PersonComments'] ?? ''); ?>" id="oldPersonComments" name="oldPersonComments">
                    <input type="hidden" name="allocationsSPID" id="allocationsSPID" value="<?php echo $allocationsSPID;?>">
                    <tr>
                        <td colspan="4" style="text-align:center;">
                            <input name="submit" type="submit" id="updateduty"  value="Update Duty">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
</div>
<script type="text/javascript">
var maxcharsComments = 500;
$( document ).ready(function() {
    checkMiscellaneousDuty(<?php echo $TeamId; ?>);
    $(function() {
        $('#StartTime').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });

        $('#EndTime').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });

        $('#dutyduration').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });
    });

    $(function() {
            var breakTimeHr = '<?php echo $intBreakTimeHour?>';
            var breakTimeMin = '<?php echo $intBreakTimeMinute?>';
            $("#breakTimeHour").val(breakTimeHr).trigger("chosen:updated");
            $("#breakTimeMinute").val(breakTimeMin).trigger("chosen:updated");
    });

    var startTimeEnabledDisabled = '<?php echo $disabledStartEndTime?>';
    if(startTimeEnabledDisabled == 'Yes'){
        $("#StartTime").attr("disabled","disabled").val('--:--');
        $("#EndTime").attr("disabled","disabled").val('--:--');
        $('#startTimeStar').hide();
        $('#endTimeStar').hide();
    } else {
        calculateDuration();
    }

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


    $('#StartTime').on('changeTime', function() {
        initializeOverrideOver12();
    });

    $('#EndTime').on('changeTime', function() {
        initializeOverrideOver12();
    });
    initializeOverrideOver12();
});


function initializeOverrideOver12() {
    if('<?php echo $disabledStartEndTime?>' != 'Yes') {
        var start_time = $('#StartTime').val();
        var end_time = $('#EndTime').val();
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

function checkMiscellaneousDuty(teamId) {
    let dutyName = $('#eDutyname').val();
    let startTime = $('#StartTime').val();
    let splitStartTime = startTime.split(':');
    let endTime = $('#EndTime').val();
    let splitEndTime = endTime.split(':');
    let dutyRowId = <?php echo $DutyId;?>;

    let dataAllocPost = {
        'action': 'getallocationrowdetail',
        'id': dutyRowId
    }
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
        data: dataAllocPost,
        success: function (resp) {
            resp = $.parseJSON(resp);
            if (resp.status) {
                if (resp.data.DutyName != dutyName) {
                    let dataGet = {
                        'action': 'checkDutyName',
                        'teamId': teamId,
                        'dutyName': dutyName
                    };
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                        data: dataGet,
                        success: function (res) {
                            res = $.parseJSON(res);
                            if (res.status) {
                                let dutyColourId = parseInt(res.dutyColourId);
                                $('#eDutyname').val(res.miscDutyName);
                                $('#dutyduration').val(res.duration);
                                $('#dutyduration').prop("readonly", false);
                                $('#dutyduration').removeClass("dailyscreenDisabledDuration");
                                $('#miscDuty').val('Yes');
                                $('#currDurationVal').val(res.duration);
                                $("#StartTime").attr("disabled", "disabled").val('--:--');
                                $("#EndTime").attr("disabled", "disabled").val('--:--');
                                $("#ddldutycolour").val(dutyColourId).trigger("chosen:updated");
                                $("#breakTimeHour").val(res.dutyBreakTimeHr).trigger("chosen:updated");
                                $("#breakTimeMinute").val(res.dutyBreakTimeMin).trigger("chosen:updated");
                                $("#breakTimeHour").attr("disabled", "disabled");
                                $("#breakTimeMinute").attr("disabled", "disabled");
                                $("#breakTimeHour_chosen").addClass("chosen-disabled");
                                $("#breakTimeMinute_chosen").addClass("chosen-disabled");
                                $('#startTimeStar').hide();
                                $('#endTimeStar').hide();
                            } else {
                                if ((splitStartTime[0] == '00' || splitStartTime[0] == '--') && (splitStartTime[1] == '00' || splitStartTime[1] == '--')) {
                                $('#StartTime').val('00:00');
                                }
                                if ((splitEndTime[0] == '00' || splitEndTime[0] == '--') && (splitEndTime[1] == '00' || splitEndTime[1] == '--')) {
                                $('#EndTime').val('00:00');
                                }
                                $("#StartTime").prop("disabled", false);
                                $("#EndTime").prop("disabled", false);
                                $("#breakTimeHour").removeAttr("disabled");
                                $("#breakTimeMinute").removeAttr("disabled");
                                $("#breakTimeHour_chosen").removeClass("chosen-disabled");
                                $("#breakTimeMinute_chosen").removeClass("chosen-disabled");
                                $("#breakTimeHour").trigger("chosen:updated");
                                $("#breakTimeMinute").trigger("chosen:updated");
                                $('#dutyduration').prop("readonly", true);
                                $('#dutyduration').addClass("dailyscreenDisabledDuration");
                                $('#startTimeStar').show();
                                $('#endTimeStar').show();
                                calculateDuration();
                            }
                        }
                    });
                }
            }
        }
    });
}

</script>
