<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../function-includes/bootstrap.php';
require_once __DIR__ . '/service/Allocation.php';
require_once __DIR__ . '/service/AllocationService.php';
include_once __DIR__ . '/../../../function-includes/masterduty_filter_functions.php';
require_once __DIR__ . '/service/TimeDimensionService.php';
require_once __DIR__ . '/../../../function-includes/allocationsfunctions.php';
include_once __DIR__ . '/../../../function-includes/user-scheduling-team-list.php';

use App\Models\User\RefRole;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};


$service = new AllocationService();
$request = $service->prepareRequestDates(Request::createFromGlobals());
?>
<style type="text/css">
html, body {
    height: 100%;
    margin: 0;
  }

body {
    overflow: hidden;
}

#content {
    overflow: hidden;
}
</style>
<script type="text/javascript">
var request = <?php echo json_encode($request->request->all()); ?>;
request.weeks = <?php echo $request->get('weeks', 1); ?>;
request.initializeDragDrop = 'Yes';
</script>
<script type="text/javascript" src="js/allocations/weekly/drag-drop.js?v=<?php echo time(); ?>"></script>

<?php
$date = new Carbon();
$timeDimensionService = new TimeDimensionService();
$timeDimension = $timeDimensionService->findByDate($date);
if($timeDimension){
    $ixWeek =  str_pad( (int) $timeDimension->ixWeekInYear, 2, '0', STR_PAD_LEFT);
    $currentWeek = $ixWeek."/".(int) $timeDimension->ixYear;
}else{
    $date->startOfWeek(Allocation::BBC_CARBON_DAYS[0]);
    $date->addWeek();
    $currentWeek = $date->format("W/Y");
}

$weekNumber = $request->get('weekNumber');
if(!$request->get('teamId')) {
    $request->request->set('teamId', $request->get('searchTeamId'));
}
if(isset($viewAdhocStWeekDate)){
    $request->request->set('startWeekDate',$request->get('startWeekDate'));
}
$startWeekDate = $request->get('startWeekDate');
if ($startWeekDate) {
    $selectedWeek = new Carbon($startWeekDate);
    $selectedWeek->startOfWeek();
    $selectedWeek->addWeek();
} else {
    $selectedWeek = '';
}

if ($selectedWeek instanceof Carbon) {
    $previousWeek = (clone $selectedWeek)->subWeek();
    $nextWeek = (clone $selectedWeek)->addWeek();
} else {
    $previousWeek = '';
    $nextWeek = '';
}

$qWeekNum = explode('/', (string)$request->get('weekNumber'));
$qWeekNum[0] = $qWeekNum[0] ?? 0;
$qWeekNum[1] = $qWeekNum[1] ?? 0;

$hNextWeekNum = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($qWeekNum[1].$qWeekNum[0]) ?? 0;

$nButtonWYear = substr(strval(is_object($hNextWeekNum) ? ($hNextWeekNum->ixYearWeek ?? 0) : $hNextWeekNum), 0, 4);
$nButtonWNum = substr(strval(is_object($hNextWeekNum) ? ($hNextWeekNum->ixYearWeek ?? 0) : $hNextWeekNum), 4, 2);
$hPrevWeekNum = $timeDimensionService->findImmediatePrevWeekOfCurrentWeek($qWeekNum[1].$qWeekNum[0]) ?? 0;
$pButtonWYear = substr(strval(is_object($hPrevWeekNum) ? ($hPrevWeekNum->ixYearWeek ?? 0) : $hPrevWeekNum), 0, 4);
$pButtonWNum = substr(strval(is_object($hPrevWeekNum) ? ($hPrevWeekNum->ixYearWeek ?? 0) : $hPrevWeekNum), 4, 2);

$sessEWeeklyUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$masterMiscDutiesFilterData =  GetMasterDutyFilter($request->get('teamId'),$sessEWeeklyUserId);
$mastMiscFilterData = json_decode($masterMiscDutiesFilterData,true);

$getDutyShiftCountingFilters = $service->getDutyShiftCountingFilters($request,0,1);
if(!$request->get('isAdhocAction')) {
    $screenName = 'EditWeekly';
} else {
    $screenName = 'ViewAdhoc';
}
$teamOptions = getSchedulingTeamList($request->get('teamId'), 'allocation-policy', 'viewEditWeekly');
$arrStaffOptions = $service->getRolePermissionEditWeekly($request->get('teamId'));
$teamDefaults = GetTeamDefaults(0,$request->get('teamId'));
$teamDefaults = $teamDefaults[$request->get('teamId')] ?? [];
$teamLeaderRole= $request->get('teamId') ? $userRoleTrait->checkSchedulingTeamRoleExists($request->get('teamId'), RefRole::TEAM_LEADER) : '';
?>
<script>
    <?php if($request->get('dutyCountFilterCols') != '' && $request->get('shiftCountingCheckBox') == 'show'){?>
        $("#showHideCount").prop( "checked", true );
        $("#dutyCountFilterCols").val('<?php echo $request->get('dutyCountFilterCols')?>');
        $("#selShiftCountingFilterId").val('<?php echo $request->get('selShiftCountingFilterId')?>');
    <?php }?>
    if($('#setCookieWeekNum').val() != ''){
        $.cookie('cookieWeekNumber','<?php echo $weekNumber; ?>');
    }
</script>


<div id="dialog-form" title="Add new person" style="overflow:visible;"></div>
<div id="dialog-form-duty" title="Create Duty"></div>
<div id="dialog-form-adhoc-duty" title="Edit Adhoc Duty" style="overflow:visible;"></div>
<div id="dialog-form-copy-duty" title="Copy Duty" style="overflow:visible;"></div>
    <form id="editWeeklyAllocations">
    <input type="hidden" name="userId" id="userId" value="<?php echo $request->get('userId'); ?>">
    <input type="hidden" name="searchTeamId" id="searchTeamId" value="<?php echo $request->get('teamId'); ?>">
    <input type="hidden" name="shiftCountingCheckBox" id="shiftCountingCheckBox">
    <input type="hidden" name="dutyCountFilterCols" id="dutyCountFilterCols">
    <input type="hidden" name="selShiftCountingFilterId" id="selShiftCountingFilterId">
    <input type="hidden" name="queryString" id="queryString">
    <input type="hidden" name="queryOrder" id="queryOrder">
    <input type="hidden" name="topFilterName" id="topFilterName">
    <input type="hidden" name="topFilterApplied" id="topFilterApplied">
    <input type="hidden" name="selFilterType" id="selFilterType">
    <input type="hidden" name="selFilterId" id="selFilterId">
    <input type="hidden" name="setCookieWeekNum" id="setCookieWeekNum">
    <input type="hidden" name="unallocOrdering" id="unallocOrdering">
    <input type="hidden" name="newAvailablePerson" id="newAvailablePerson">
    <input type="hidden" name="ajaxLoadParams" id="ajaxLoadParams" value='<?php echo $reqParamsForDivReload?>'>
    <input type="hidden" name="searchMiscDutyFilter" id="searchMiscDutyFilter" value="<?php echo $request->get('searchMiscDutyFilter','')?>">
    <input type="hidden" name="showMiscDutyFilter" id="showMiscDutyFilter" value="<?php echo $request->get('showMiscDutyFilter','')?>">
    <input type="hidden" name="getWeekNumber" id="getWeekNumber" value="<?php echo $request->get('startWeek')?>">
    <input type="hidden" name="unAllocDutyData" id="unAllocDutyData">
    <input type="hidden" name="isAdhocAction" id="isAdhocAction" value="<?php echo $request->get('isAdhocAction')?>">
    <input type="hidden" name="filterAndSkill" id="filterAndSkill">
    <input type="hidden" name="filterAndDutyLabel" id="filterAndDutyLabel">
	<input type="hidden" name="staRole" id="staRole" value="<?php echo $arrStaffOptions['isTeamAdmin'];?>">
    <input type="hidden" name="isRestrictCopyDuty" id="isRestrictCopyDuty" value="<?php echo array_key_exists('IsRestrictCopyDuty', $teamDefaults) ? $teamDefaults['IsRestrictCopyDuty'] : '';?>">
    <?php if($request->get('topFilterName') == '') :?>
        <?php $filterName = 'Filters';?>
    <?php else :?>
        <?php $filterName = $request->get('topFilterName');?>
    <?php endif;?>
    <div class="weeklyFilterBlock">
        <h1 class="sr-only">Edit Weekly</h1>
        <div class="grid box1">
            <div class="filterContainer fContainer1">
                <h2 style="background: #dddddd;" class="heading">Week</h2>
                <div class="fieldBox weekBox">
                    <input name="wprev" id="wprev" data-week="<?php echo $pButtonWNum ; ?>/<?php echo $pButtonWYear; ?>" type="button" class="wprev weekAction" value="<<">
                    <input name="wcurrent" id="wcurrent" data-week="<?php echo $currentWeek; ?>" type="button" class="wcurrent weekAction" value="Current" >
                    <input name="wnext" id="wnext" data-week="<?php echo $nButtonWNum; ?>/<?php echo $nButtonWYear; ?>" type="button" class="wnext weekAction" value=">>" >
                    <span class="choosecalender chooseWeek">
					    <input type="text" placeholder="Enter Week" autocomplete="off" name="weekNumber" id="weekNumber" value="<?php echo $weekNumber; ?>" />
					</span>
					<span class="choosecalender chooseDate"><label for="Choose a Week">Date</label>
                        <input type="text" placeholder="dd-mm-yyyy" autocomplete="off" name="date" id="date" onkeyup="removeDateManually(event)"  class='date-picker' value="<?php echo $request->get('date', null); ?>"/>
					</span>
					<div class="showWeeksBlock">
                        <select name="weeks" id="showWeeks" class="showWeekChoosen" onchange="getMultiWeekData(this.value);">
                            <?php for($i=1; $i <=12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == $request->get('weeks') ? 'selected' : ""; ?>>
                                <?php echo $i;?> <?php echo $i > 1 ? "Weeks" : "Week"; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filterContainer publishWeek" id="publishWeekIconDiv">
                <span id="allocationPublish" class="handcursor" title="Publish">P</span>
            </div>
			<?php if (isset($teamDefaults['HasDutiesView']) && $teamDefaults['HasDutiesView'] == 1) {;?>
			<div class="filterContainer publishWeek" >
				<img title="Show Production View" class="handcursor" onclick='javascript:openProductionView()' border="0" src="../images/AllocationsWeeklyDuties.png" width="40px" height="28px">
			</div>
			<?php }?>
        </div>
        <div class="grid box2">
            <div class="filterContainer fContainer2">
                <h2 style="background: #dddddd;" class="heading">Team</h2>
                <div class="fieldBox">
		            <select name="teamId" id="adhocFilter" onchange="teamUpdate(this.value,<?php echo $request->get('userId'); ?>);" class="chosen-selectWeekly">
                        <?php if(!empty($teamOptions)) { 
                            echo "<option value=''>Select The Team</option>";
                            echo $teamOptions;
                        } else {?>
                        <option value="0">No Team Assigned</option>
                        <?php }?>
                    </select>
                </div>
            </div>
            <div class="filterContainer fContainer2">
                <h2 style="background: #dddddd;" class="heading">Filters</h2>
                <div class="fieldBox chooseFilterBox" id="allocations-filters">
					<?php include(__DIR__ . '/../../../components/filters/filters.php');?>
                </div>
            </div>
            <div class="filterContainer fContainer2">
                <h2 style="background: #dddddd;" class="heading">Master & Misc. Filters</h2>
                <div class="fieldBox">
                    <select name="mastMiscFilterId" id="mastMiscFilterId" class="chosen-selectWeekly">
                        <option selected value="">No Filter</option>
                        <?php if(!empty($mastMiscFilterData)) { foreach($mastMiscFilterData as $k => $v) {
                            if($v['IsActive'] == '1'){?>
                                <option value="<?php echo $v['MasterDutyFilterID']?>" <?php if($request->get('mastMiscFilterId') == $v['MasterDutyFilterID']) {?> selected="selected" <?php }?>><?php echo $v['FilterName']?></option>
                                <?php }?>
                            <?php }?>
                        <?php }?>
                    </select>
                </div>
            </div>
        </div>
        <div class="grid box3">
            <div class="filterContainer fContainer3">
                <h2 style="background: #dddddd;" class="heading">Drag</h2>
				<div class="fieldBox">
                    <input id="disableDrag" name="disableDrag"  type="checkbox" onclick="disableDragDrop()" style="vertical-align: middle;">
                    <label for="disableDrag">Off</label>
                </div>
            </div>
            <div class="filterContainer fContainer2 weekFilter">
                <h2 style="background: #dddddd;" class="heading">Shift Counting</h2>
                <div class="fieldBox">
                    <input id="showHideCount" name="showHideCount"  type="checkbox" style="vertical-align: middle;" onclick="showHideCountGrid();">
                    <label for="showHideCount">On</label>
                </div>
                <div class="fieldBox">
                    <select name="dutyShiftCountingFilterId" id="dutyShiftCountingFilterId" class="chosen-selectWeekly" onchange="applyDutyShiftCountingFilter(<?php echo $request->get('userId')?>,<?php echo $request->get('teamId')?>,this.value)">
                        <option value="NA">No Filter/Edit Filter</option>
                        <?php if(!empty($getDutyShiftCountingFilters)) { foreach($getDutyShiftCountingFilters as $ky => $vl) { if($request->get('selShiftCountingFilterId') == $vl['ID']){?>
                                <option value="<?php echo $vl['ID']?>" selected><?php echo $vl['FilterName']?></option>
                        <?php } else {?>
                                <option value="<?php echo $vl['ID']?>"><?php echo $vl['FilterName']?></option>
                        <?php }?>
                            <?php }?>
                        <?php }?>
                    </select>
                </div>
                <div class="fieldBox">
                    <input name="dityCountBtn" id="dityCountBtn"  type="button" value="Go" onclick="showDutyShiftCountingFilter(<?php echo $request->get('userId')?>,<?php echo $request->get('teamId')?>);">
                </div>
            </div>
            <div class="filterContainer contextMenuInfoIcon">
                <div class="info-list-icon" title="View Key" id="contextViewInfoIcons"></div>
            </div>
			<?php if ($teamLeaderRole==0) { ?>
            <div class="filterContainer removeWeekIcon">
                <div class="remove-week-icon" title="Delete Week" id="removeweek"></div>
            </div>
			 <?php }?>
			 <input type="submit" value="Submit" name="Submit" class="hide">
        </div>
    </div>
</form>
<script type="text/javascript">
    <?php if(empty($teamOptions)) { ?>
        showSessionBlankMsg();
    <?php }?>
</script>