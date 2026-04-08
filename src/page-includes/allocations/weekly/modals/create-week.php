<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use App\Models\User\RefRole;
use Traits\UserRoleTrait;
$userRoleTrait = new class {
    use UserRoleTrait;
};

$request = Request::createFromGlobals();

$service = new AllocationService();
$timeDimensionService = new TimeDimensionService();
$request = $service->prepareRequestDates($request);
$date = new Carbon($request->get('startWeekDate'));
$adHocDuties = count($service->getAdhocDuties($request));
$pweeknum = substr($request->get('weekNumber'), 0, 2);

$getSetFilterId = $service->getCurrentSetEditWeeklyFilter($request);
$selAutoPageFilterId = 0;
if($getSetFilterId > 0){
    $selAutoPageFilterId = $getSetFilterId;
}
$getShiftCountFilterId = $service->getCurrentShiftCountingFilter($request);
$selShiftCountFilterId = 0;
if(isset($getShiftCountFilterId['ID']) && !empty($getShiftCountFilterId['ShiftCountingFilter'])){
    $selShiftCountFilterId = $getShiftCountFilterId['ShiftCountingFilter'];
}    
$teamLeaderRole = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::TEAM_LEADER);
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
?>
<form name="createWeekForm" id="createWeekForm">

<table class="tablesmalltidy" width="100%">
	<tr height="30px">
		<th colspan="2"><b>Create Week<b></th>
	</tr>

	<tr>
		<td colspan="2">Week <?php echo $pweeknum; ?> has not been created yet.</td>
	</tr>

    <tr>
		<td colspan="2"><hr></td>
	</tr>


    <input type="hidden" name="schedulingTeamId" value="<?php echo $request->get('teamId'); ?>">
    <input type="hidden" name="userId" value="<?php echo $sessUserId; ?>">
    <input type="hidden" name="selAutoPageFilterId" value="<?php echo $selAutoPageFilterId; ?>">
    <input type="hidden" name="shiftCountingFilterId" id="shiftCountingFilterId" value="<?php echo $selShiftCountFilterId; ?>">
	<tr>
		<td align="left"></td>
		<td align="right">
		 <?php if ($teamLeaderRole==0) : ?>
			<input type="button" value="Create Week" id="createWeek" style="background-color: #ededed;">
            <?php if ($adHocDuties) : ?>
			<input type="button" value="View Ad Hoc Duties" id="viewAdhocDuties">
            <?php endif; ?>
		 <?php endif; ?>
			<input type="button" value="Cancel" id="closeCreateWeekPopup">

		</td>
	</tr>
</table>
</form>
<script type="text/javascript">
$(document).ready(function() {
	$('#date').val('');

	$("#userId").val('<?php echo $request->get('userId'); ?>');
    $("#shiftCountingCheckBox").val('<?php echo $request->get('shiftCountingCheckBox'); ?>');
    $("#dutyCountFilterCols").val('<?php echo $request->get('dutyCountFilterCols'); ?>');
    $("#selShiftCountingFilterId").val('<?php echo $request->get('selShiftCountingFilterId'); ?>');
    $("#queryString").val("<?php echo $request->get('queryString'); ?>");
    $("#queryOrder").val('<?php echo $request->get('queryOrder'); ?>');
    $("#topFilterName").val('<?php echo $request->get('topFilterName'); ?>');
    $("#topFilterApplied").val('<?php echo $request->get('topFilterApplied'); ?>');
    $("#selFilterType").val('<?php echo $request->get('selFilterType'); ?>');
    $("#selFilterId").val('<?php echo $request->get('selFilterId'); ?>');
    $("#setCookieWeekNum").val('<?php echo $request->get('setCookieWeekNum'); ?>');
    $("#unallocOrdering").val('<?php echo $request->get('unallocOrdering'); ?>');
    $("#newAvailablePerson").val('<?php echo $request->get('newAvailablePerson'); ?>');
    $("#searchMiscDutyFilter").val('<?php echo $request->get('searchMiscDutyFilter'); ?>');
    $("#showMiscDutyFilter").val('<?php echo $request->get('showMiscDutyFilter'); ?>');
    $("#getWeekNumber").val('<?php echo $request->get('getWeekNumber'); ?>');
});

$(document).keypress(function(event){
	var tag = event.target.type;
	var keycode = (event.keyCode ? event.keyCode : event.which);
	if((keycode == '13') && (tag!='text')){
		$('#createWeek').trigger('click');
	}
});


$(document).ready(function(){
	$(".focus-content").focus();
	$('#facebox .close')
		.click($.facebox.close)
		.empty()
		.append('<img src="'
		+ $.facebox.settings.closeImage
		+ '" class="close_image" title="close" id="closeIconCreateWeek">')
});

$( "#closeIconCreateWeek" ).click(function() {
  	$('#closeCreateWeekPopup').trigger('click');
});

setTimeout(function() {
  	$('#filterName').html('<span title="Filters"> Filters</span>');
	$('#clearFilterIcon').hide();
}, 1000);
</script>
