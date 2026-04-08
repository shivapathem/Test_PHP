<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once __DIR__.'/../../function-includes/init.php';
include_once __DIR__.'/../../page-includes/teamskills/process/classTeamskills.php';
include_once __DIR__.'/../../page-includes/allocations/weekly/service/AllocationService.php';
require_once __DIR__.'/../../function-includes/masterduty_functions.php';
include_once __DIR__. '/../../function-includes/user-scheduling-team-list.php';

$teamskillobj = new classTeamskills;
$allocationServiceObj = new AllocationService();
$teamId = $request->get('teamId');
$isShifttoCheck = $request->get('isShifttoCheck');
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$userId = $request->get('userId') ? $request->get('userId') : $sessUserId;

$teamSkills = json_decode($teamskillobj->ListAllProgrammes($teamId), true);
$publicFilters = $allocationServiceObj->getPublicFilters($request);
$privateFilters = $allocationServiceObj->getPrivateFilters($request);
$userTeamRoles = $allocationServiceObj->getUserTeamRole($request);
$prog = getAllLabels();
$allProg = json_decode($prog);
?>
<link href="../../styles/charging/charging.css?v=<?php echo time(); ?>" rel="stylesheet">
<link href="../../styles/allocations/filters/epic-3.css" rel="stylesheet">
<?php if(($screenName != 'EditWeekly') && ($screenName != 'EditWeeklyRota') && ($screenName != 'ViewDaily')){?>
    <link href="../../styles/jquery.qtip.css" rel="stylesheet" type="text/css" />
<?php }?>
<?php if(($screenName != 'EditWeekly') && ($screenName != 'EditWeeklyRota') && ($screenName != 'ViewDaily')){?>
    <script type="text/javascript" src="../../js/jquery.qtip.min.js"></script>
<?php }?>

<?php if($screenName == 'ViewDaily') :?>
    <input type="hidden" id="intWeek" value="<?php echo $intWeek ?>">
    <input type="hidden" id="intDay" value="<?php echo $intDay ?>">
    <input type="hidden" id="startDate" value="<?php echo $strCurrentDate; ?>">
    <input type="hidden" id="endDate" value="<?php echo date('Y-m-d',strtotime($strCurrentDate. ' + '.($selectedDay-1).' days')); ?>">
    <input type="hidden" id="rolepermission" value="<?php echo $rolepermission ?>">
<?php endif;?>

<?php if($screenName == 'ViewWeekly') :?>
    <input type="hidden" id="intSWeekNumber" value="<?php echo $intWeekNumber ?>">
    <input type="hidden" id="intEWeekNumber" value="<?php echo $intWeekNumber ?>">
    <input type="hidden" id="intTeamIDs" value="<?php echo $intTeamIDs ?>">
    <input type="hidden" id="intSortOrder" value="<?php //echo $intSortOrder ?>">
<?php endif;?>

<?php if($screenName == 'MultiWeek') :?>
    <input type="hidden" id="intSWeekNumber" value="<?php echo $intWeekNumber ?>">
    <input type="hidden" id="intEWeekNumber" value="<?php echo $intNextWeek ?>">
    <input type="hidden" id="intTeamIDs" value="<?php echo $intTeamIDs ?>">
<?php endif;?>

<?php if(($screenName == 'EditWeekly') || ($screenName == 'EditWeeklyRota')):?>
    <input type="hidden" id="startDate" value="<?php echo $request->get('startDate') ?>">
    <input type="hidden" id="endDate" value="<?php echo $request->get('endDate') ?>">
<?php endif;?>

<div class="filter-container">
    <div class="filter-dropdown filterboxposition">
        <?php 
        $teamOptions = getSchedulingTeamList($teamId, 'allocation-policy', 'view');
        if($screenName == 'ViewDaily') :?>
            <span class="clearFilterIcon qtip-hover cross-red-icon" title="Clear Filter" id="clearFilterIcon" style="display: none;" onclick="clearViewFilter('<?php echo $screenName?>',<?php echo $teamId?>);">
                <div class="cross-red-icon"></div>
            </span>
        <?php endif;?>
        <?php if($screenName == 'ViewWeekly') :?>
            <span class="clearFilterIcon qtip-hover cross-red-icon" title="Clear Filter" id="clearFilterIcon" style="display: none;" onclick="clearViewFilter('<?php echo $screenName?>',<?php echo $teamId?>, <?php echo $isShifttoCheck?>);">
                <div class="cross-red-icon"></div>
            </span>
        <?php endif;?>
        <?php if($screenName == 'MultiWeek') :?>
            <span class="clearFilterIcon qtip-hover cross-red-icon" title="Clear Filter" id="clearFilterIcon" style="display: none;" onclick="clearViewFilter('<?php echo $screenName?>',<?php echo $teamId?>);">
                <div class="cross-red-icon"></div>
            </span>
        <?php endif;?>
        <?php if(($screenName == 'EditWeekly') || ($screenName == 'EditWeeklyRota') || ($screenName == 'ViewAdhoc')) :?>
            <span class="clearFilterIcon" title="Clear Filter" id="clearFilterIcon" style="display: none;" onclick="clearViewFilter('<?php echo $screenName?>',<?php echo $teamId?>);">
                <div class="cross-red-icon"></div>
            </span>
        <?php 
        $teamOptions = getSchedulingTeamList($teamId, 'allocation-policy', 'viewEditWeekly');
        endif;?>
        <span onclick="filterToggle()" class="filter-dropbtn fa fa-caret-right allocation-filters filterinputbox" id="filterName">
            <?php if(strlen($filterName) > 12) :?>
                <span title="<?php echo $filterName?>"> <?php echo substr($filterName, 0, 12).'...';?></span>
            <?php else:?>
                <span title="<?php echo $filterName?>"> <?php echo $filterName;?></span>
            <?php endif;?>
        </span>
        <div id="filterDropdown" class="filter-dropdown-content absoluteDropdown w-470">
            <table class="filter-table" id="allocationFilters">
                <tr>
                    <td>Filter Type</td>
                    <td colspan="2">
                        <select class="w-98" id="filterType" onchange="showFilterType(this.value)" style="height:22px;">
                            <option value="">--Select Filter Type--</option>
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                        </select>
                    </td>
                </tr>
                <tr id="publicFilterRow" style="display:none;">
                    <td>Preset Filters (Public) </td>
                    <td colspan="2">
                        <input type="button" id="" value="Go" onclick="applyGoFilter('<?php echo $screenName;?>')">
                        <select name="filters[publicFilterId]" id="publicFilterId" class="w-84" onchange="getFilterDetails(this.value)" style="height:22px;">
                            <option value="">--Select Filter--</option>
                            <?php if(isset($publicFilters) && !empty($publicFilters) && count($publicFilters) > 0){
                                foreach($publicFilters as $pubKey => $pubVal){?>
                                    <option value="<?php echo $pubVal['ID']?>"><?php echo $pubVal['FilterName']?></option>
                                <?php }?>
                            <?php }?>
                        </select>
                    </td>
                </tr>

                <tr id="privateFilterRow" style="display:none;">
                    <td>Preset Filters (Private) </td>
                    <td colspan="2">
                        <input type="button" value="Go" onclick="applyGoFilter('<?php echo $screenName;?>')">
                        <select name="filters[privateFilterId]" id="privateFilterId" class="w-84" onchange="getFilterDetails(this.value)" style="height:22px;">
                            <option value="">--Select Filter--</option>
                            <?php if(isset($privateFilters) && !empty($privateFilters) && count($privateFilters) > 0){
                                foreach($privateFilters as $prvKey => $prvVal){?>
                                    <option value="<?php echo $prvVal['ID']?>"><?php echo $prvVal['FilterName']?></option>
                                <?php }?>
                            <?php }?>
                        </select>
                    </td>
                </tr>

                <tr id="toggleBtnRow" style="display:none;">
                    <td colspan="3">
                        <span class="fa fa-chevron-right fa-lg allocation-filters" id="showFilterDetails"></span>
                    </td>
                </tr>

                <tr class="toggleFilter filterRows" id="Row1">
                    <td>
                        <span class="filter-label-txt">Filter People by Staff Name</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[StaffNameFilter]" id="StaffNameFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                        </select>
                    </td>
                    <td>
                        <input class="w-98" type="text" name="filters[StaffName]" id="StaffName" placeholder="Filter by Staff Name" maxlength="500">
                    </td>
                </tr>
                <tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Filter People by Sort Code</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[SortCodeFilter]" id="SortCodeFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                        </select>
                    </td>
                    <td>
                        <input class="w-98" type="text" name="filters[SortCode]" id="SortCode" placeholder="Filter by Sort Code" maxlength="200">
                    </td>
                </tr>

                <tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Filter People by Cost Code</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[CostCodeFilter]" id="CostCodeFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                        </select>
                    </td>
                    <td>
                        <input class="w-98" type="text" name="filters[CostCode]" id="CostCode" placeholder="Filter by Cost Code" maxlength="200">
                    </td>
                </tr>

                <tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Filter People by Skill</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[SkillFilter]" id="SkillFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                        </select>
                    </td>
                    <td>
                        <select name="filters[Skill]" id="Skill" class="w-98 chooseFilterOption SkillSelection" data-placeholder="Select Skills" multiple>
                            <?php if(isset($teamSkills) && !empty($teamSkills) && count($teamSkills) > 0){
                                    foreach($teamSkills as $tmSkillKey => $tmSkillVal){?>
                                        <option value="<?php echo $tmSkillVal['ID']?>"><?php echo $tmSkillVal['programmename']?></option>
                                    <?php }?>
                                <?php }?>
                        </select>
                    </td>
                </tr>

                <tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Filter Duties (Shifts) by Name</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[DutyFilter]" id="DutyFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                            <option value="%">STARTS WITH</option>
                        </select>
                    </td>
                    <td>
                        <input class="w-98" type="text" name="filters[Duty]" id="Duty" placeholder="Filter by Duty Name" maxlength="200">
                    </td>
                </tr>
				<tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Filter Duties (Shifts) by Label</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[DutyLabelFilter]" id="DutyLabelFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                        </select>
                    </td>
                    <td>
                        <select name="filters[DutyLabel]" id="DutyLabel" class="w-98 chooseFilterOption DutyLabelOption" data-placeholder="Select Duty Labels" multiple>
                            <?php if(!empty($allProg) && isset($allProg)){
                                foreach($allProg as $dtyKey => $dtyVal){?>
                                    <option value="<?php echo $dtyVal->ID?>"><?php echo $dtyVal->Programme?></option>
                                <?php } ?>
                            <?php }?>
                        </select>
                    </td>
                </tr>
				 <?php if(($screenName == 'ViewDaily')) :?>
				<tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Filter Duties (Shifts) by Time</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title=""></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[DutyTimeFilter]" id="DutyTimeFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                        </select>
                    </td>
                    <td>
                        <select name="filters[DutyTime]" id="DutyTime" class="w-98  chooseFilterOption" data-placeholder="Select Time">
                            <option value='-1'>Select Time</option>
                            <?php if($strCurrentDate == date('Y-m-d')) {?>
                                <?php if($dutyTimeFilterNow == 1){?>
                                    <option value="<?php echo 'NOW--'.seconds_from_time(date("H:i"));?>" selected="selected">Now</option>
                                <?php } else {?>
                                    <option value="<?php echo 'NOW--'.seconds_from_time(date("H:i"));?>">Now</option>
                                <?php }?>
                            <?php
                            }
                            $range = range(strtotime("00:00"), strtotime("23:45"), 900);
                            foreach ($range as $time) {
                            echo '<option value=' .seconds_from_time( date("H:i", $time)) . ' data-sel>' . date("H:i", $time) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
				<?php endif;?>

                <?php if(($screenName == 'ViewDaily')) :?>
                <tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Filter Jobs by Name</span>
                        <span class="info-icon-row" style="float: left; margin: -1% 0px 0px 10%;">
                             <input type="checkbox" id="JobNameALL" value="1">
                         </span><span class="filter-label-txt">All</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[JobNameFilter]" id="JobNameFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                            <option value="%">STARTS WITH</option>
                        </select>
                    </td>
                    <td>
                        <input class="w-98" type="text" name="filters[JobName]" id="JobName" placeholder="Filter by Job Name" maxlength="200">
                    </td>
                </tr>

                <tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Filter Jobs by Label</span>
                        <span class="info-icon-row" style="float: left; margin: -1% 0px 0px 11.5%;">
                             <input type="checkbox" id="JobLabelALL" value="1">
                         </span><span class="filter-label-txt">All</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[JobLabelFilter]" id="JobLabelFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                        </select>
                    </td>
                    <td>
                        <select name="filters[JobLabel]" id="JobLabel" class="w-98 chooseFilterOption jobLabelOption" data-placeholder="Select Job Labels" multiple>
                            <?php if(!empty($allProg) && isset($allProg)){
                                foreach($allProg as $jbLKey => $jbLVal){?>
                                    <option value="<?php echo $jbLVal->ID?>"><?php echo $jbLVal->Programme?></option>
                                <?php } ?>
                            <?php }?>
                        </select>
                    </td>
                </tr>
		<?php endif;?>

                <tr class="toggleFilter filterRows">
                    <td>Sort Order</td>
                    <td colspan="2">
                        <select name="filters[SortOrder]" id="SortOrder" class="w-98">
                            <option value="">--Select Sort Order--</option>
                            <option value="1">Staff Name</option>
                            <option value="2">Sort Code</option>
                            <?php if(($screenName != 'EditWeekly') && ($screenName != 'EditWeeklyRota') && ($screenName != 'ViewWeekly') && ($screenName != 'MultiWeek')){?>
                            <option value="3">Duty Name</option>
                            <option value="4">Start Time of Duty</option>
                            <?php }?>
                        </select>
                    </td>
                </tr>

                <?php if(($screenName == 'ViewWeekly') || (($screenName == 'ViewDaily') && ($rolepermission != 1)) || ($screenName == 'MultiWeek')) :?>
                <tr class="toggleFilter filterRows">
                    <td>
                        <span class="filter-label-txt">Additional Teams</span>
                        <span class="info-icon-row">
                            <div class="filter-info-icon" title="Separate multiple words using , without space"></div>
                        </span>
                    </td>
                    <td>
                        <select name="filters[AdditionalTeamsFilter]" id="AdditionalTeamsFilter" class="codeFilterSelect">
                            <option value="*">CONTAINS</option>
                            <option value="__AND__">AND</option>
                            <option value=";">OR</option>
                            <option value="!">NOT CONTAINS</option>
                        </select>
                    </td>
                    <td>
                        <select name="filters[AdditionalTeams]" id="AdditionalTeams" class="w-98 chooseFilterOption additionalTeamOption" data-placeholder="Select Additional Teams" multiple>
                            <option value=''>Select The Team</option>
                            <?php if(!empty($teamOptions)){
                                echo $teamOptions;
                            }?>
                        </select>
                    </td>
                </tr>
			    <?php endif; ?>

                <tr class="toggleFilter filterRows">
                    <td>New Filter Name </td>
                    <td colspan="2">
                    <input type="text" name="filters[FilterName]" id="FilterName" style="width: 98.7%;" maxlength="200" autocomplete="off">
                    <br>
                    <span style="font-size: 11px; color:#FF0000; display: none;" id="filterNameErr"></span>
                    </td>
                </tr>

                <tr class="toggleFilter filterRows">
                    <td colspan="12">
                        <table with="100%" style="margin: 0 auto;">
                            <tr>
                                <td>
                                    <table with="100%">
                                        <tr>
                                            <td class="widAuto"><input type="radio" id="andFilter" name="filters[conditionFilter]"  value="AND"></td>
                                            <td>AND</td>
                                            <td><input type="radio" id="orFilter" name="filters[conditionFilter]" checked="checked" value="OR"></td>
                                            <td>OR</td>
                                        </tr>
                                    </table>
                                </td>
                                <?php if(isset($userTeamRoles['hasAllAccess'])){?>
                                    <td><input type="button" id="savePublicBtn" value="Save Public" onclick="savePublicFilter('<?php echo $screenName?>');"></td>
                                <?php }?>
                                <td><input type="button" value="Save Filter" id="savePrivateBtn" onclick="savePrivateFilter('<?php echo $screenName?>');"></td>
                                <td>
                                    <?php
                                    $haveDeleteAccess = 0;
                                    if(isset($userTeamRoles['hasAllAccess'])){
                                        $haveDeleteAccess = 1;
                                    }
                                    ?>
                                    <input type="button" value="Delete" id="deleteBtn" onclick="deleteAutopagesFilterConfirmation(<?php echo $haveDeleteAccess?>);">
                                </td>
                                <td><input type="button" id="applyBtn" value="Apply" style="cursor:pointer;" onclick="applyViewFilter('<?php echo $screenName?>');"></td>
                                <td><input type="button" id="cancelBtn" value="Cancel" style="cursor:pointer;" onclick="cancelFilter('<?php echo $screenName?>');"></td>
                                <input type="hidden" data-userid="<?php echo $userId;?>" id="userId">
								<input type="hidden" data-teamid="<?php echo $teamId;?>" id="teamId" value="<?php echo $teamId;?>">
								<input type="hidden" data-shift-check="<?php echo $isShifttoCheck;?>" id="isShifttoCheck" value="<?php echo $isShifttoCheck;?>">
                                <input type="hidden" id="filterId">
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        /**Temp Filter Loading Start*/

    <?php if($screenName=='ViewWeekly' || $screenName=='MultiWeek') { ?>
        var SavedweeklyFilter = $.parseJSON(getSavedweeklyFilter());
        if (SavedweeklyFilter!==null && SavedweeklyFilter.screenName!="undefined" && (SavedweeklyFilter.screenName=='ViewWeekly' || SavedweeklyFilter.screenName=='MultiWeek')) {
            $('#StaffNameFilter').val(SavedweeklyFilter.StaffNameFilter);
            $('#StaffName').val(SavedweeklyFilter.StaffName);
            $('#SortCodeFilter').val(SavedweeklyFilter.SortCodeFilter);
            $('#SortCode').val(SavedweeklyFilter.SortCode);
            $('#CostCodeFilter').val(SavedweeklyFilter.CostCodeFilter);
            $('#CostCode').val(SavedweeklyFilter.CostCode);
            $('#SkillFilter').val(SavedweeklyFilter.SkillFilter);
            $('#Skill').val(SavedweeklyFilter.Skill);
            $('#DutyFilter').val(SavedweeklyFilter.DutyFilter);
            $('#Duty').val(SavedweeklyFilter.Duty);
            $('#DutyLabelFilter').val(SavedweeklyFilter.DutyLabelFilter);
            $('#DutyLabel').val(SavedweeklyFilter.DutyLabel);
            $('#SortOrder').val(SavedweeklyFilter.SortOrder);
            $('#AdditionalTeamsFilter').val(SavedweeklyFilter.AdditionalTeamsFilter);
            $('#AdditionalTeams').val(SavedweeklyFilter.AdditionalTeams);
            $('#isShifttoCheck').val(SavedweeklyFilter.isShifttoCheck);
            if (SavedweeklyFilter.andMatch == 1) {
                 $("#andFilter").prop("checked", true)
            }else{
                $("#orFilter").prop("checked", true)
            }
        }
        <?php } ?>

        <?php if($screenName=='ViewDaily') { ?>
        var SaveddailyFilter = $.parseJSON(getSaveddailyFilter());
        if (SaveddailyFilter!==null && SaveddailyFilter.screenName!="undefined" && (SaveddailyFilter.screenName=='ViewDaily')) {

            $('#StaffNameFilter').val(SaveddailyFilter.StaffNameFilter);
            $('#StaffName').val(SaveddailyFilter.StaffName);
            $('#SortCodeFilter').val(SaveddailyFilter.SortCodeFilter);
            $('#SortCode').val(SaveddailyFilter.SortCode);
            $('#CostCodeFilter').val(SaveddailyFilter.CostCodeFilter);
            $('#CostCode').val(SaveddailyFilter.CostCode);
            $('#SkillFilter').val(SaveddailyFilter.SkillFilter);
            $('#Skill').val(SaveddailyFilter.Skill);
			$('#DutyTimeFilter').val(SaveddailyFilter.DutyTimeFilter);
            $('#DutyTime').val(SaveddailyFilter.DutyTime);
            $('#DutyFilter').val(SaveddailyFilter.DutyFilter);
            $('#Duty').val(SaveddailyFilter.Duty);
            $('#DutyLabelFilter').val(SaveddailyFilter.DutyLabelFilter);
            $('#DutyLabel').val(SaveddailyFilter.DutyLabel);
            $('#SortOrder').val(SaveddailyFilter.SortOrder);
            $('#AdditionalTeamsFilter').val(SaveddailyFilter.AdditionalTeamsFilter);
            $('#AdditionalTeams').val(SaveddailyFilter.AdditionalTeams);
            $('#JobNameFilter').val(SaveddailyFilter.JobNameFilter);
            $('#JobName').val(SaveddailyFilter.JobName);
            $('#JobLabelFilter').val(SaveddailyFilter.JobLabelFilter);
            $('#JobLabel').val(SaveddailyFilter.JobLabel);
            if (SaveddailyFilter.andMatch == 1) {
               $("#andFilter").prop("checked", true);
            }else{
               $("#orFilter").prop("checked", true);
            }
            if (SaveddailyFilter.jobNameAll == 1) {
               $("#JobNameALL").prop("checked", true)
            }else{
               $("#JobNameALL").prop("checked", false);
            }
            if (SaveddailyFilter.jobLabelAll == 1) {
               $("#JobLabelALL").prop("checked", true);
            }else{
               $("#JobLabelALL").prop("checked", false);
            }

        }
	    <?php if($dutyTimeFilterNow == 1){?>
            let nowTime = '<?php echo 'NOW--'.seconds_from_time(date("H:i"));?>';
            $('#DutyTime').val(nowTime).trigger("chosen:updated");
        <?php }?>
        <?php } ?>

        <?php if($screenName=='EditWeekly')  { ?>
        if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
            var tempFilterEditWeekly = JSON.parse(localStorage.getItem('editWeeklyFilter'));
            $('#StaffNameFilter').val(tempFilterEditWeekly.StaffNameFilter);
            $('#StaffName').val(tempFilterEditWeekly.StaffName);
            $('#SortCodeFilter').val(tempFilterEditWeekly.SortCodeFilter);
            $('#SortCode').val(tempFilterEditWeekly.SortCode);
            $('#CostCodeFilter').val(tempFilterEditWeekly.CostCodeFilter);
            $('#CostCode').val(tempFilterEditWeekly.CostCode);
            $('#SkillFilter').val(tempFilterEditWeekly.SkillFilter);
            $('#Skill').val(tempFilterEditWeekly.Skill);
            $('#DutyFilter').val(tempFilterEditWeekly.DutyFilter);
            $('#Duty').val(tempFilterEditWeekly.Duty);
            $('#DutyLabelFilter').val(tempFilterEditWeekly.DutyLabelFilter);
            $('#DutyLabel').val(tempFilterEditWeekly.DutyLabel);
            $('#SortOrder').val(tempFilterEditWeekly.SortOrder);
            $('#AdditionalTeamsFilter').val(tempFilterEditWeekly.AdditionalTeamsFilter);
            $('#AdditionalTeams').val(tempFilterEditWeekly.AdditionalTeams);
            $('#JobNameFilter').val(tempFilterEditWeekly.JobNameFilter);
            $('#JobName').val(tempFilterEditWeekly.JobName);
            $('#JobLabelFilter').val(tempFilterEditWeekly.JobLabelFilter);
            $('#JobLabel').val(tempFilterEditWeekly.JobLabel);
            if (tempFilterEditWeekly.andMatch == 1) {
               $("#andFilter").prop("checked", true)
            }else{
               $("#orFilter").prop("checked", true)
            }
        }
        <?php } ?>

		<?php if($screenName == 'EditWeeklyRota') { ?>
		 var SavedEditweeklyRotaFilter = $.parseJSON(getSavedEditweeklyRotaFilter());
        if (SavedEditweeklyRotaFilter!==null && SavedEditweeklyRotaFilter.screenName!="undefined" && (SavedEditweeklyRotaFilter.screenName=='EditWeeklyRota')) {
            $('#StaffNameFilter').val(SavedEditweeklyRotaFilter.StaffNameFilter);
            $('#StaffName').val(SavedEditweeklyRotaFilter.StaffName);
            $('#SortCodeFilter').val(SavedEditweeklyRotaFilter.SortCodeFilter);
            $('#SortCode').val(SavedEditweeklyRotaFilter.SortCode);
            $('#CostCodeFilter').val(SavedEditweeklyRotaFilter.CostCodeFilter);
            $('#CostCode').val(SavedEditweeklyRotaFilter.CostCode);
            $('#SkillFilter').val(SavedEditweeklyRotaFilter.SkillFilter);
            $('#Skill').val(SavedEditweeklyRotaFilter.Skill);
            $('#DutyFilter').val(SavedEditweeklyRotaFilter.DutyFilter);
            $('#Duty').val(SavedEditweeklyRotaFilter.Duty);
            $('#DutyLabelFilter').val(SavedEditweeklyRotaFilter.DutyLabelFilter);
            $('#DutyLabel').val(SavedEditweeklyRotaFilter.DutyLabel);
            $('#SortOrder').val(SavedEditweeklyRotaFilter.SortOrder);
            $('#AdditionalTeamsFilter').val(SavedEditweeklyRotaFilter.AdditionalTeamsFilter);
            $('#AdditionalTeams').val(SavedEditweeklyRotaFilter.AdditionalTeams);
            $('#JobNameFilter').val(SavedEditweeklyRotaFilter.JobNameFilter);
            $('#JobName').val(SavedEditweeklyRotaFilter.JobName);
            $('#JobLabelFilter').val(SavedEditweeklyRotaFilter.JobLabelFilter);
            $('#JobLabel').val(SavedEditweeklyRotaFilter.JobLabel);
            if (SavedEditweeklyRotaFilter.andMatch == 1) {
               $("#andFilter").prop("checked", true)
            }else{
               $("#orFilter").prop("checked", true)
            }
        }
        <?php } ?>
        /**Temp Filter Loading End*/
        $(".chooseFilterOption").chosen({
            no_results_text: "Oops, nothing found!",
            width: "98%"
        });

        $("span.allocation-filters").click(function () {
            $(".toggleFilter").toggle();
            $(this).toggleClass("fa-chevron-down");
        });
		<?php if($screenName=='ViewDaily') { ?>
			if ($('#DutyTime').val()=='-1'){
				$(".chosen-single").css('color', '#999');
			} else {
				$(".chosen-single").css('color', '#000');
			}
			$(".chosen-drop").css('color', '#000');
			$('.chooseFilterOption').change(function () {
				  var val = $(this).val();
				  if (val=='-1'){
					  $(".chosen-single").css('color', '#999');
				  } else {
					  $(".chosen-single").css('color', '#000');
				  }
			});
		 <?php }?>

    });
    <?php if(($screenName != 'EditWeekly') || ($screenName != 'EditWeeklyRota')){?>
    $('.qtip-hover').qtip({
        position: {
            my: 'top center',
            viewport: $(window)
        },
        style: 'qtip-rounded qtip-shadow qtip-light'
    });
    <?php }?>
    function filterToggle() {
        var selFilterType = $("#selFilterType").val();
        $("#filterDropdown").toggle();
        if($('#showFilterDetails').hasClass('fa-chevron-down')) {
            $('#showFilterDetails').removeClass("fa-chevron-down");
        }
        $("#filterType").show();
	    if(selFilterType == ''){
            $("#filterType").val("public");
            $("#publicFilterRow").show();
            $("#publicFilterId").val('');
            $("#privateFilterRow").hide();
            $("#privateFilterId").val('');
	    } else {
            var selFilterId = $("#selFilterId").val();
            $("#filterType").val(selFilterType);
            if(selFilterType == 'public'){
                $("#publicFilterRow").show();
                $("#privateFilterRow").hide();
                $("#publicFilterId").val(selFilterId);
            } else {
                $("#privateFilterRow").show();
                $("#publicFilterRow").hide();
                $("#privateFilterId").val(selFilterId);
            }
            getFilterDetails(selFilterId);
        }
        $("#toggleBtnRow").show();
    }
    function showFilterType(filterType){
        clearFilterData();
        if(filterType == "public"){
            $("#publicFilterRow").show();
            $("#toggleBtnRow").show();
            $("#privateFilterRow").hide();
            $("#publicFilterId").val('');
        } else if(filterType == "private"){
            $("#publicFilterRow").hide();
            $("#toggleBtnRow").show();
            $("#privateFilterRow").show();
            $("#privateFilterId").val('');
        } else {
            $("#publicFilterRow").hide();
            $("#publicFilterId").val('');
            $("#privateFilterRow").hide();
            $("#privateFilterId").val('');
        }
    }

    function clearFilterData(){
        $('#filterNameErr').hide();
        $('#filterNameErr').html('');
        $("#StaffNameFilter").val("*");
        $("#StaffName").val("");
        $("#SortCodeFilter").val("*");
        $("#SortCode").val("");
        $("#CostCodeFilter").val("*");
        $("#CostCode").val("");
        $("#SkillFilter").val("*");
        $("#Skill").val("").trigger("chosen:updated");
		$("#DutyTimeFilter").val("*");
        $("#DutyTime").val("").trigger("chosen:updated");
        $("#DutyFilter").val("*");
        $("#Duty").val("");
        $("#JobNameFilter").val("*");
        $("#JobName").val("");
        $("#JobLabelFilter").val("*");
        $("#JobLabel").val("");
		$("#DutyLabelFilter").val("*");
        $("#DutyLabel").val("").trigger("chosen:updated");
        $("#SortOrder").val("");
        $("#AdditionalTeamsFilter").val("*");
        $("#AdditionalTeams").val("");
        $("#FilterName").val("");
        $("#savePublicBtn").data("filterId","");
    }

    function cancelFilter(){
        $('#filterDropdown').hide();
        // $("span.allocation-filters").removeClass('arrow-down');
        $("span.allocation-filters").removeClass("fa-chevron-down");
    }
</script>






