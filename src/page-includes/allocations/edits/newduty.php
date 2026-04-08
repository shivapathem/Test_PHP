<?php
use Symfony\Component\HttpFoundation\Request;
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/masterduty_functions.php';
require_once __DIR__ . '/../weekly/service/AllocationService.php';

$TeamId = empty($_REQUEST["teamId"]) ? 0 : $_REQUEST["teamId"];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$rsAreaTeams = GetUserAreaTeamsList($intUserID);
$TeamOptions = TeamListDropDown($rsAreaTeams, $TeamId);
$WeekNumber = $_REQUEST['WeekNumber'];
$iDay = $_REQUEST['iDay'];
$scheduledPersonId = $_REQUEST['scheduledPersonId'];
$prog = getAllLabels();
$allProg = json_decode($prog);
$labelId = 0;
$lablestr = '';
foreach ($allProg as $key => $value)
{
    if($value->ID == $labelId)
    {
        $lablestr   .='<option value="'.$value->ID.'" selected>'.$value->Programme .'</option>';
    }else
    {
        $lablestr   .='<option value="'.$value->ID.'">'.$value->Programme .'</option>';
    }
}
    $BreakTime = 4500;
    $Duration = 0;
    $intBreakTimeHour = intval((int)$BreakTime / 3600);
    $intBreakTimeMinute = intval(((int)$BreakTime % 3600) / 60);
    $breakTimehourOptions = PopulateHoursDropDown($intBreakTimeHour);
    $breakTimeminuteOptions = PopulateMinutesDropDown_limited($intBreakTimeMinute);

    $intDurationHour = intval((int)$Duration / 3600);
    $intDurationHour = strlen(trim($intDurationHour))== 1 ? "0".$intDurationHour : $intDurationHour;
    $intDurationMinute = intval(((int)$Duration % 3600) / 60);
    $intDurationMinute = strlen(trim($intDurationMinute))== 1 ? "0".$intDurationMinute : $intDurationMinute;
    $intDutyDuration = $intDurationHour . ':' . $intDurationMinute;
    $disabledStartEndTime = $disabledStartEndTime ?? '';

$request = Request::createFromGlobals();
$service = new AllocationService();
$personComments = '';
$dutyComments = '';
$data = $service->getAllocationComments($request);
if (isset($data['DutyComments']) && strpos($data['DutyComments'], '__COMMENT_SEPARETOR__') !== false) {
	$commentArr = explode('__COMMENT_SEPARETOR__', $data['DutyComments']);
	$personComments = isset($commentArr[0]) ? $commentArr[0] : '';
	$dutyComments = isset($commentArr[1]) ? $commentArr[1] : '';
}else
{
	$personComments = isset($data['PersonComments']) ? $data['PersonComments'] : '';
	$dutyComments = isset($data['DutyComments']) ? $data['DutyComments'] : '';
}

$dName = ($scheduledPersonId == 0) ? '' : 'U';
$modalTitle = ($scheduledPersonId == 0) ? 'New Duty' : 'Edit Duty';
?>
<script src="js/allocations/daily/newduty.js?v=<?php echo time(); ?>"></script>
<script src="js/allocations/daily/commonduty.js?v=<?php echo time(); ?>"></script>
<div class="popup">
    <div class="content">
        <?php echo '<div style="width: 745px">'; ?>
        <form id="newunallocatedduty" method="post">
            <table id="jobnewedittable" class="smalltable bluetable" width="100%">
                <thead>
				<tr id ="bugtd">
                <td colspan="5" class ="messageerror error" align="center" ></td>
                </tr>
                <tr>
                    <th colspan="5"><?php echo $modalTitle;?></th>
                </tr>
                </thead>
                <tbody>
				 <tr>
                    <td colspan="4" class="hiddenErrorDiv" id="errorDiv"></td>
                </tr>
                <tr>
                    <td class="lightblue">Duty Name<span class="required">*</span></td>
                    <td colspan="3"><input id="eDutyname" value="<?php echo $dName;?>" name="eDutyname" type="text" size="50" value="" onfocusout="checkMiscellaneousDuty(<?php echo $TeamId; ?>)"  required>
					</td>
                </tr>
                <tr>
                    <td>Start Time<span class="required">*</span></td>
                    <td><input onchange="setStartTime();" id="StartTime" name="StartTime" type="text" class=" ui-timepicker-input" size="10" value="0" autocomplete="off" onfocusout="calculateDuration()"></td>
                    <td>End Time<span class="required">*</span></td>
                    <td><input onchange="setEndTime();" id="EndTime" name="EndTime" type="text"
                        class="ui-timepicker-input" size="10" value="0" autocomplete="off" onfocusout="calculateDuration()">
                  </td>
                </tr>
                <tr>
                    <td>Break Duration</td>
                    <td>
                    <?php    echo '<select class="chosen-select" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 40%;" onchange="updateDailyCalculatedDuration()">';
                        echo ''.$breakTimehourOptions.'';
                        echo '   </select> : ';
                        echo '   <select class="chosen-select" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 40%;" onchange="updateDailyCalculatedDuration()">';
                        echo ''.$breakTimeminuteOptions.'';
                        echo '   </select></br>';   ?>
                     </td>

                    <td>Duty Colour</td>
                    <td><?php echo '   <select class="chosen-select bg-white chosen-selectMaxWidth" name="ddldutycolour" id="ddldutycolour" style="background-color: white;width:100%;"> <option value="0">Select Color</option>';
                    ?></td>
                </tr>
                <tr>
                    <td>Scheduling Team</td>
                    <td>
                        <?php echo '   <select class="chosen-select bg-white chosen-selectMaxWidth schedulingTeam185W" name="scheduling_team" id="selteam">';
                        echo '' . $TeamOptions . ''; ?>
                    </td>
                    <td>Duration</td>
                    <td>
                        <input id="dutyduration" name="dutyduration" type="text" value="<?php echo $intDutyDuration ?>" autocomplete="off" readonly="readonly" class="dailyscreenDisabledDuration" onfocusout="setDuration()">
                    </td>
                </tr>
                <tr>
                    <td>Select Label</td>
                    <td><?php echo '<select data-placeholder="Select label" class="chosen-select" multiple name="labelIds[]" id="labelIds" style="background-color:white;">
                    '. $lablestr .'</select>';?>
                    </td>
                    <td>Doesn't Need Covering</td>
                    <td>
                        <input id="isNeedCovering" name="isNeedCovering" type="checkbox" <?php echo isset($rowIsNeedCovering) && $rowIsNeedCovering == 0 ? 'checked' : '' ?>>
                    </td>
                </tr>

                <tr>
                    <td class="padding-5">Duty Comments</td>
                    <td>
                        <textarea name="DutyComments" id="DutyCommentsEditDuty" cols="35" rows="5" maxlength="500" placeholder="Please enter Duty Comments" ><?php echo $dutyComments; ?></textarea>
                        <br>Characters Remaining : <span id="remainDutyCommentsEditDuty">500</span>
                    </td>
                    <td class="padding-5">Person Comments</td>
                    <td>
                        <textarea <?php if($scheduledPersonId == 0) { ?> disabled <?php } ?> name="PersonComments" id="PersonCommentsEditDuty" placeholder="Please enter Duty Comments" cols="35" rows="5" maxlength="500"><?php echo $personComments; ?></textarea>
                        <?php if($scheduledPersonId != 0) { ?>
                        <br>Characters Remaining : <span id="remainPersonCommentsEditDuty">500</span>
                        <?php } ?>
                    </td>
                <tr>
                <tr class="isOverrideOver12Td">
                    <td width="15%"> <span class="isOverrideOver12Td"> Disable Over 12 </span></td>
                    <td colspan="3">
                        <input class="isOverrideOver12Td" id="isOverrideOver12" name="isOverrideOver12" type="checkbox">
                    </td>
                <tr>

                <input type="hidden" name="WeekNumber" id="WeekNumber" value="<?php echo $WeekNumber;?>">
                <input type="hidden" name="iDay" id="iDay" value="<?php echo $iDay;?>">
                <input type="hidden" name="unallocated" id="unallocated" value="1">
                <input type="hidden" name="scheduledPersonId" id="scheduledPersonId" value="<?php echo $scheduledPersonId; ?>">
                <input type="hidden" name="requested_date" value="<?php echo $_REQUEST['currdate'];?>" id="requested_date">
                <input type="hidden" name="role" value="<?php echo $_REQUEST['role'];?>" id="role">
                <input type="hidden" name="duty_parent_id" value="<?php echo $_REQUEST['dutyParentId'];?>" id="duty_parent_id">

                <tr>
                    <td> <td>
                    <td colspan="3"><input name="submit" type="submit" id="updateduty" value="Create Duty"></td>
                </tr>
                </tbody>
            </table>
        </form>
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

        setTimeout(function() {
            $("#labelIds").chosen("destroy");
            $("#labelIds").chosen({no_results_text: "Oops, nothing found!", max_selected_options: 6});
        }, 100);
    });

    $(function() {
            var breakTimeHr = '<?php echo $intBreakTimeHour?>';
            var breakTimeMin = '<?php echo $intBreakTimeMinute?>';
            $("#breakTimeHour").val(breakTimeHr).trigger("chosen:updated");
            $("#breakTimeMinute").val(breakTimeMin).trigger("chosen:updated");
    });

    var startTimeEnabledDisabled = '<?php echo $disabledStartEndTime ?? '' ?>';
    if(startTimeEnabledDisabled == 'Yes'){
        $("#StartTime").attr("disabled","disabled").val('--:--');
        $("#EndTime").attr("disabled","disabled").val('--:--');
        $('#startTimeStar').hide();
        $('#endTimeStar').hide();
    } else {
        calculateDuration();
    }
    $('#DutyCommentsEditDuty').keyup(function () {
        commentsTextCount('remainDutyCommentsEditDuty', $(this));
    });
    setTimeout(function() {
        commentsTextCount('remainDutyCommentsEditDuty',  $('#DutyCommentsEditDuty'));
    }, 100);

    $('#PersonCommentsEditDuty').keyup(function () {
        commentsTextCount('remainPersonCommentsEditDuty', $(this));
    });
    setTimeout(function() {
        commentsTextCount('remainPersonCommentsEditDuty',  $('#PersonCommentsEditDuty'));
    }, 100);

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

    let dataAllocPost = {
        'action': 'getallocationrowdetail',
        'id': 0
    }
    $.post("/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php", dataAllocPost).success(function(resp) {
			resp = $.parseJSON(resp);
			if (resp.status) {
			    let dataGet = {
                    'action': 'checkDutyName',
                    'teamId': teamId,
                    'dutyName': dutyName
                };
                $.post("/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php", dataGet).success(function(res) {
                    res = $.parseJSON(res);
                    if (res.status) {
					    let dutyColourId = parseInt(res.dutyColourId);
                        $('#Dutyname').val(res.miscDutyName);
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
                });
            }
		});
}
</script>
