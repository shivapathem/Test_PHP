<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';
include_once __DIR__ .'/../filters/filters-script.php';
include_once __DIR__ ."/../../../../function-includes/genericfunctions.php";
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$service = new AllocationService();
$request = $service->prepareRequestDates($request);

$date = new Carbon(date('Y-m-d'));
$timeDimensionService = new TimeDimensionService();
$timeDimension = $timeDimensionService->findByDate($date);
$weeknumber= str_pad(substr(strval($timeDimension->ixYearWeek),4,2), 2, '0', STR_PAD_LEFT)."/".str_pad(substr(strval($timeDimension->ixYearWeek),0,4), 4, '0', STR_PAD_LEFT);
$ixWeek = (int) substr(strval($timeDimension->ixYearWeek), 4, 2);
$queryWeekNumber = (int) ($timeDimension->ixYear . $ixWeek);

$selAutoPageFilterId = 0;
$shiftCountingFilterId = 0;
$request->request->set('checkweeknumber',$queryWeekNumber);
$request->request->set('schedulingTeamId',$request->get('teamId'));
$checkWeekExistsForTeam = $service->checkWeekExists($request);
$allocationWeekExists = 'No';
if((isset($checkWeekExistsForTeam)) && ($checkWeekExistsForTeam != '') && ($checkWeekExistsForTeam != 0)){
	$allocationWeekExists = 'Yes';
}
$getSetFilterId = $service->getCurrentSetEditWeeklyFilter($request);
if($getSetFilterId > 0){
    $selAutoPageFilterId = $getSetFilterId;
}
$getShiftCountFilterId = $service->getCurrentShiftCountingFilter($request);
if(isset($getShiftCountFilterId['ID']) && !empty($getShiftCountFilterId['ShiftCountingFilter'])){
	$shiftCountingFilterId = $getShiftCountFilterId['ShiftCountingFilter'];
}
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
?>
<form name="chooseWeekForm" id="chooseWeekForm" method="post" onsubmit="return false;">
	<input type="hidden" name="schedulingTeamId" id ="schedulingTeamId" value="<?php echo $request->get('teamId'); ?>">
	<input type="hidden" name="userId" value="<?php echo $sessUserId; ?>">
	<input type="hidden" name="selAutoPageFilterId"  id="selAutoPageFilterId" value="<?php echo $selAutoPageFilterId; ?>">
	<input type="hidden" name="allocationWeekExists" id="allocationWeekExists" value="<?php echo $allocationWeekExists; ?>">
	<input type="hidden" name="shiftCountingFilterId" id="shiftCountingFilterId" value="<?php echo $shiftCountingFilterId; ?>">
	<input type="hidden" name="errormessage" id="errormessage">
	<input type="hidden" name="transitionDateYear" id="transitionDateYear" value="<?php echo date('Y',strtotime(getenv('ACCESS_DATE')))?>">
	<input type="hidden" name="errTransitionDate" id="errTransitionDate" value="<?php echo date('jS F Y',strtotime(getenv('ACCESS_DATE')))?>">
	<div style="background-color: #dddddd;" autofocus>
		<h2 id="choose-week-heading" class="choose-week">Choose Week</h2>
	</div>
	<table class="tablesmalltidy" width="100%">
		<tr>
			<td colspan="2">Enter Week number to show.</td>
		</tr>

	    <tr>
			<td colspan="2"><hr></td>
		</tr>

	    <tr>
			<td>Week</td>
			<td>
				<input type="text" class="select-content focus-content" id="setWeekNumber" name="weekNumber" value="<?php echo $weeknumber; ?>" maxlength="7" onkeyup="submitChooseWeek(event);" autocomplete="off">
				<div id="errorDivWeek" style="color: #FF0000; display: none;"></div>
			</td>
		</tr>

		<tr>
			<td align="left"></td>
			<td align="right">
				<input type="button" value="OK" id="viewEditWeeklyGrid" style="background-color: #ededed;">
				<input type="button" value="Cancel" onclick="closeChooseWeek()">
			</td>
		</tr>
	</table>
</form>

<script>
	$(document).ready(function(){
		$(".focus-content").focus();
		$('#facebox').on('keydown', function(e) {
			if (e.key === 'Tab') {
				var focusableElements = $('#facebox :focusable');
				var firstElement = focusableElements[0];
				var lastElement = focusableElements[focusableElements.length - 1];
				if (e.shiftKey && document.activeElement === firstElement) {
					lastElement.focus();
					e.preventDefault();
				} else if (!e.shiftKey && document.activeElement === lastElement) {
					firstElement.focus();
					e.preventDefault();
				}
			}
    	});
		$('#facebox .close')
			.click($.facebox.close)
			.empty()
			.append('<img src="'
			+ $.facebox.settings.closeImage
			+ '" class="close_image" title="close">')

		$('#setWeekNumber').select();

		if($.cookie('unallocgridheight') != undefined){
            $("#unallocatedDuty").height($.cookie('unallocgridheight'));
            $("#allocatedDuty").height($.cookie('allocgridheight'));
            $('#miscDutyBlock, #weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
            $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 11);
        }
	});

	function closeChooseWeek(){
		$('body > *:not(#facebox)').removeAttr('aria-hidden');
    	$('#facebox').removeAttr('role aria-modal aria-labelledby');
		$('#facebox .close').click();
	}

	function submitChooseWeek(event){
		let enteredWeekNumber = $('#setWeekNumber').val();
		let substring = "/";
            if(!enteredWeekNumber.includes(substring)){
                if(parseInt(enteredWeekNumber) < 10){
                    enteredWeekNumber = '0'+parseInt(enteredWeekNumber);
                }
                enteredWeekNumber = new Date().getFullYear()+enteredWeekNumber;
            } else {
                let spltEnteredWeekNum = enteredWeekNumber.split('/');
                if((spltEnteredWeekNum[1] != undefined) && (spltEnteredWeekNum.length > 0)){
                    if(parseInt(spltEnteredWeekNum[0]) < 10){
                        spltEnteredWeekNum[0] = '0'+parseInt(spltEnteredWeekNum[0]);
                    }
                    enteredWeekNumber = spltEnteredWeekNum[1]+spltEnteredWeekNum[0];
                }
            }
		var postReq = {
            'actionname' : 'checkrotaweekexists',
            'WeekNumber' : enteredWeekNumber,
			'teamId' : $('#schedulingTeamId').val()
        };
		$.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/check-week.php",
            data: postReq,
            beforeSend: function(jqXHR, settings){
                $('#loading').hide();
            },
            success: function (response) {
                response = $.parseJSON(response);
			    if (response.isweekexist==""){
					$('#selAutoPageFilterId').val(0);
					$('#shiftCountingFilterId').val(0);
			    }
            }
        });
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			if($('#errorDiv:visible').length == 0){
				$('#viewEditWeeklyGrid').trigger('click');
			} else {
				editWeeklyPageLoad('Yes');
			}
		}
	}
</script>