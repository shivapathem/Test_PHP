<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../service/Allocation.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../../../../function-includes/DB_Functions.php';
include_once __DIR__ . '/../../../../function-includes/masterduty_filter_functions.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';

$request = $request ?? Request::createFromGlobals();
$service = new AllocationService();
$timeDimService = new TimeDimensionService;

$request = $service->prepareRequestDates($request);
$days = $request->get('days');

if($request->get('fireQuery') == 'Yes'){
    if($request->get('weeks') > 1){
        $days = 7;
        $queryDays = (($request->get('weeks') * $days));
        $days = (($request->get('weeks') * $days)-1);
        $dateRangeDays = $queryDays;

        $endDate = date('Y-m-d', strtotime($request->get('startDate'). ' + '.$queryDays.' days'));
        $request->request->set('days',$days);
        $request->request->set('endDate',$endDate);
        $request->request->set('endWeekDate',$endDate);
    }
    if($request->get('date') != ''){
        $request->request->set('startDate',date('Y-m-d',strtotime($request->get('date'))));
        $endDate = date('Y-m-d', strtotime($request->get('startDate'). ' + '.($days+1).' days'));
        $request->request->set('endDate',$endDate);
    }
    $request->request->set('showDataType','UNALLOC');
    $unallocatedsdata  = $service->getEditWeeklyData($request);
    $decodeunallocateds = json_decode($unallocatedsdata,true);
    $unAllocateds = $decodeunallocateds['unalloc'];

    if(!empty($unAllocateds)){
        $unqDutyIdArr = [];
        foreach($unAllocateds as $unK => $unV){
            $unqDutyIdArr[$unV['DutyName']][$unV['DutyDate']][] = $unV['ID'];
        }

        foreach($unAllocateds as $unKe => $unVa){
            $unAllocateds[$unKe]['InstanceIds'] = implode(',',$unqDutyIdArr[$unVa['DutyName']][$unVa['DutyDate']]);
        }
    }

    $periodStart = new Carbon($request->get('startDate'));
    $periodEnd = clone $periodStart; $periodEnd->addDays($days);
    $period = CarbonPeriod::create($periodStart, $periodEnd);
    $period = $period->toArray();

    $satRowCount = 0;
    foreach($period as $dateVal){
        if($dateVal->format('l') == 'Saturday'){
            break;
        }
        $satRowCount++;
    }
} else {
    $unAllocateds = json_decode(stripslashes($request->get('editWeeklydutyData')));
    $unAllocateds = json_decode(json_encode($unAllocateds), true);
    
    $periodStart = new Carbon($request->get('startDate'));
    $periodEnd = clone $periodStart; $periodEnd->addDays($days);
    $period = CarbonPeriod::create($periodStart, $periodEnd);
    $period = $period->toArray();

    $dateRangeDays = 7;
}

$unalloatedData = [];
$unallocateddutynamearr = [];

foreach($unAllocateds as $k1 => $v1){
    $unallocateddutynamearr[$v1['DutyName']][$v1['DutyDate']]['DutyInstances'] = $v1['DutyInstances'];
    $unalloatedData[$v1['DutyName']][$v1['DutyDate']][] = $v1;
}

$id = $request->get('ID');
$dutyDate = $request->get('dutyDate');
$iDay = $request->get('iDay');
$schedulingPersonId = $request->get('schedulingPersonId');
$teamId = $request->get('teamId');
$weekNum = $request->get('weekNum');

$areaID = $_SESSION['user']['AreaID']??0;
$dutyType = 0;
$isArchived = 0;
$strsearch = '';
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$getMiscDutyList = ListAllMasterDuties($areaID, $dutyType, $isArchived, $teamId, $strsearch);
$getMiscDutyList = json_decode($getMiscDutyList,true);

$masterMiscDutiesFilterData =  GetMasterDutyFilter($request->get('teamId'),$sessUserId);
$mastMiscFilterData = json_decode($masterMiscDutiesFilterData,true);
?>
<style type="text/css">
.hide-tb-tr{
    display: none;
}
</style>
<input type="hidden" name="unAllocDutyData" id="unAllocDutyData" value='<?php echo json_encode($unallocateddutynamearr);?>'>
<div class="block-25 grid-item" onmouseover="removetip();">
    <div id="miscDutyBlock" onScroll="setContainerScrollPos()">
        <div id="miscDutyBlockPos" name="miscDutyBlockPos"></div>
        <div class="weeklyAllocationTable weekly-table scrollHorizontal sticky-header" id="miscDutyTopHeader" style="width:99.9%; <?php if(($request->get('weeks') > 1) || ($request->get('date') != '')){?> height:41px; <?php }?>">
            <div class="weekly-table-header">
			<?php
				if ($request->get('weeks', 1) > 1) {
				echo "<div class='miscHeader weekly-table-row' style='height:41px'>";
				} else {
				echo "<div class='miscHeader weekly-table-row' style='height:27px'>";
				}
				?>
                    <div class="left-pad-10 weekly-header-cell">
                        <?php if($request->get('showMiscDutyFilter') == 'showFilter'){?>
                            <input type="checkbox" checked="checked" name="showMastMiscDuty" id="showMastMiscDuty" onclick="checkMiscDutyFilterBox();">
                        <?php } else {?>
                            <input type="checkbox" name="showMastMiscDuty" id="showMastMiscDuty" onclick="checkMiscDutyFilterBox();">
                        <?php }?>
                    </div>
                    <h3 class="weekly-header-cell font-bold heading">Misc Duties</h3>
                    <div class="weekly-header-cell">
                        <input type="text" name="searchMiscDuty" id="searchMiscDuty" placeholder="Misc Duty" autocomplete="off">
                    </div>
                </div>
            </div>
		</div>
                <?php if(!empty($getMiscDutyList)){foreach($getMiscDutyList as $miscKey => $miscVal){?>
                <div class="border-colour td-bg-colour dutiesListTR hide-tb-tr" style="cursor: pointer;" id="miscDutyRow<?php echo $miscVal['MasterDutyID']?>">
                    <div class="border-colour td-bg-colour miscDutyTD">
                        <div id="dragdiv<?php echo $miscVal['MasterDutyID']?>" class="<?php ?>dragAlloc misc-drag-drop" draggable="true" data-source="misc" data-misc-dutyname="<?php echo $miscVal['DutyName']?>" data-misc-dutyduration="<?php echo $miscVal['Duration']?>" data-misc-dutycolour-id="<?php echo $miscVal['DutyColourID']?>" data-misc-id="<?php echo $miscVal['MasterDutyID']?>" data-break-time="<?php echo $miscVal['BreakTime']?>" date-range-days="<?php echo $dateRangeDays?>" date-start-date="<?php echo $request->get('startDate')?>" date-end-date="<?php echo date( 'Y-m-d', strtotime( $request->get('endDate') . ' -1 day '))?>">
                            <?php echo $miscVal['DutyName']?>
                        </div>
                    </div>
                </div>
                <?php }} else {?>
                <div id="miscDutyNoRow" class="dutiesListTR">
                    <div class="border-colour td-bg-colour miscDutyTD">Sorry, There are no Miscellaneous Duties</div>
                </div>
                <?php }?>      
    </div>
</div>
<div class="block-75 grid-item">
    <div id="weeklyUnAllocation-2"  class="scrollHorizontal <?php echo ($request->get('weeks', 1) > 1)? 'allocationMultiWeeks' : 'allocationSingleWeek' ?>" onscroll="divScrollTop();">
        <div id="unallocDutyBlockPos" name="unallocDutyBlockPos"></div>
        <div id="weeklyAllocationTable" class="weeklyAllocationTable weekly-table scrollHorizontal dropzone"  data-source="unallocated" data-charging-present = "<?php echo $data['TriangleColour'] == null ? 0 : 1; ?>" style="width:100%;">
            <div class="weekly-table-header sticky-header">

            <?php 
			$weekDate = clone $period[0];
            if ($request->get('date') == ''){?>
                <div class="weekly-table-row multiWeekHolder <?php if ($request->get('weeks', 1) == 1){?> hide-tb-tr <?php }?>">
                    <?php for($i = 1; $i <= $request->get('weeks', 1); $i++) : ?>
                        <div class="min-w120 weekly-header-cell text-center" style="border-left: 1px solid #fff;">
    					</div>
    					<div class="min-w120 weekly-header-cell text-center">
    					</div>
    					<div class="min-w120 weekly-header-cell text-center">
    					</div>
                        <div class="min-w120 weekly-header-cell text-center">
                            <?php 
                            $weekDate->addWeeks();
                            $weekDate->startOfWeek(Allocation::BBC_CARBON_DAYS[0]);
                            echo sprintf('Week %s/%s',  $weekDate->format('W'), $weekDate->format('Y'));
                            ?>
                        </div>
    					<div class="min-w120 weekly-header-cell text-center">
    					</div>
    					<div class="min-w120 weekly-header-cell text-center">
    					</div>
    					<div class="min-w120 weekly-header-cell text-center">
    					</div>
                    <?php endfor; ?>
                </div>
            <?php } else {
                $weekDate->addWeeks();
                $weekDate->startOfWeek(Allocation::BBC_CARBON_DAYS[0]);
                $dateWeekNumber = $weekDate->format('W');
                $dateWeekYear = $weekDate->format('Y');
                $nextWeek = $timeDimService->findImmediateNextWeekOfCurrentWeek($weekDate->format('Y').$weekDate->format('W'));
                $nextWeekNum = substr(strval($nextWeek->ixYearWeek),4,2);
                $nextWeekYear = substr(strval($nextWeek->ixYearWeek),0,4);
                switch($satRowCount){
                    case "0":
                        echo '<div class="weekly-table-row multiWeekHolder">
                            <div class="min-w120 weekly-header-cell text-center date-border"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center">'.sprintf('Week %s/%s',  $dateWeekNumber, $dateWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                        </div>';
                        break;
                    case "1":
                        echo '<div class="weekly-table-row multiWeekHolder">
                            <div class="min-w120 weekly-header-cell text-center date-border">'.sprintf('Week %s/%s',$dateWeekNumber,$dateWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell date-border"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell date-text-right">Week</div>
                            <div class="min-w120 weekly-header-cell date-text-left">'.sprintf('%s/%s',$nextWeekNum,$nextWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                        </div>';
                        break;
                    case "2":
                        echo '<div class="weekly-table-row multiWeekHolder">
                            <div class="min-w120 weekly-header-cell date-border date-text-right">Week</div>
                            <div class="min-w120 weekly-header-cell date-text-left">'.sprintf('%s/%s',$dateWeekNumber,$dateWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center date-border"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center">'.sprintf('Week %s/%s',$nextWeekNum,$nextWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                        </div>';
                        break;
                    case "3":
                        echo '<div class="weekly-table-row multiWeekHolder">
                            <div class="min-w120 weekly-header-cell text-center date-border"></div>
                            <div class="min-w120 weekly-header-cell text-center">'.sprintf('Week %s/%s',$dateWeekNumber,$dateWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center date-border"></div>
                            <div class="min-w120 weekly-header-cell date-text-right">Week</div>
                            <div class="min-w120 weekly-header-cell date-text-left">'.sprintf('%s/%s',$nextWeekNum,$nextWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                        </div>';
                        break;
                    case "4":
                        echo '<div class="weekly-table-row multiWeekHolder">
                            <div class="min-w120 weekly-header-cell text-center date-border"></div>
                            <div class="min-w120 weekly-header-cell date-text-right">Week</div>
                            <div class="min-w120 weekly-header-cell date-text-left">'.sprintf('%s/%s',$dateWeekNumber,$dateWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center date-border"></div>
                            <div class="min-w120 weekly-header-cell text-center">'.sprintf('Week %s/%s',$nextWeekNum,$nextWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                        </div>';
                        break;
                    case "5":
                        echo '<div class="weekly-table-row multiWeekHolder">
                            <div class="min-w120 weekly-header-cell text-center date-border"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center">'.sprintf('Week %s/%s',$dateWeekNumber,$dateWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell date-border date-text-right">Week</div>
                            <div class="min-w120 weekly-header-cell date-text-left">'.sprintf('%s/%s',$nextWeekNum,$nextWeekYear).'</div>
                        </div>';
                        break;
                    case "6":
                        echo '<div class="weekly-table-row multiWeekHolder">
                            <div class="min-w120 weekly-header-cell text-center date-border"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell date-text-right">Week</div>
                            <div class="min-w120 weekly-header-cell date-text-left">'.sprintf('%s/%s',$dateWeekNumber,$dateWeekYear).'</div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center"></div>
                            <div class="min-w120 weekly-header-cell text-center date-border">'.sprintf('Week %s/%s',$nextWeekNum,$nextWeekYear).'</div>
                        </div>';
                        break;       
                }
            ?>
            <?php }?>
            <div class="weekly-table-row <?php echo ($request->get('weeks', 1) > 1)? 'unallocatTable2Head' : 'unallocatTable1Head' ?>">
                <!-- generate header as date count we have -->
                <?php foreach ($period as $date):?>
                    <div class="min-w120 weekly-header-cell" data-sort="SORT_ASC">
                        <?php echo $date->format('l'); ?>
                        <br>
                        <?php echo $date->format('d/m/Y'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            </div>
            <div class="weekly-table-body unAllocateddTableBody dutydroppable dropeventscall-un" ondrop="dropAlloc(event,this)" data-scheduling-person="0" data-source="unallocated">

            <?php $rowCounter = 1;?>
            <?php foreach($unalloatedData as $dutyName => $unAllocated) : ?>
                <div class="weekly-table-row">
                    
                    <?php
                    $blankCellCounter = 0;
                    foreach ($period as $date): 
                        $date = $date->format('Y-m-d'); ?>

                        <?php if(isset($unAllocated[$date])) : ?>

                            <?php
                            $data = $unAllocated[$date][0];
                            $dutyCount = count($unAllocated[$date]);
                            ?>
                                <div id="rowUnqId_<?php echo $data['ID']; ?>" class="NotFixedon weekly-body-cell context-menu-unallocated ulrow_<?php echo $rowCounter;?> unalloc-cell" 
                                    title='<?php echo $data['DutyName']; ?> (<?php echo number_format((float)($data['Duration'] / 3600), 2, '.', '').' Hours' ?>)'
                                    onmouseover="removetip()"
                                    data-date="<?php echo $date; ?>" 
                                    data-source="unallocated" 
                                    data-id="<?php echo $data['ID']; ?>" 
                                    data-duty-count="<?php echo $dutyCount; ?>" 
                                    data-duty-name="<?php echo $data['DutyName']; ?>" 
                                    data-scheduling-person="<?php echo $data['SchedulingPersonID']??0 ?>" 
                                    data-unique-id="<?php echo $data['ID'] ?>" 
                                    data-scheduling-team-id="<?php echo $data['SchedulingTeamId']??0 ?>"
                                    data-row-start="<?php echo $data['StartTime']??0;?>"
                                    data-row-end="<?php echo $data['EndTime']??0;?>"
                                    data-duty-instances="<?php echo $data['DutyInstances'] ?>"
                                    data-row-counter="<?php echo $rowCounter ?>"
                                    data-cell-counter="<?php echo $blankCellCounter ?>" 
                                    data-duty-instance-ids="<?php echo $data['InstanceIds'] ?>" 
                                    data-next-instance-id="0" 
                                    data-remain-duty-instances="0" 
                                    date-range-days="<?php echo $dateRangeDays?>" 
                                    date-start-date="<?php echo $request->get('startDate')?>" 
                                    date-end-date="<?php echo date( 'Y-m-d', strtotime( $request->get('endDate') . ' -1 day '))?>"
                                    >
                                    <div id="colUnqId_<?php echo $data['ID'] ?>" class="unallocated dragAlloc unalloc-drag-drop textoverflow uldutycell_<?php echo $data['DutyDate']?>" 
                                    draggable="true" 
                                    data-date="<?php echo $date; ?>" 
                                    data-occupied=1 
                                    data-source="unallocated" 
                                    data-id="<?php echo $data['ID']; ?>" 
                                    data-duty-name="<?php echo $data['DutyName']; ?>"
                                    data-duty-count="<?php echo $dutyCount; ?>" 
                                    data-scheduling-person="<?php echo $data['SchedulingPersonID']??0 ?>" 
                                    data-unique-id="<?php echo $data['ID'] ?>" 
                                    data-scheduling-team-id="<?php echo $data['SchedulingTeamId']??0 ?>"
                                    data-row-start="<?php echo $data['StartTime']??0;?>"
                                    data-row-end="<?php echo $data['EndTime']??0;?>"
                                    data-unique-id="<?php echo $data['ID'] ?>"
                                    data-duty-instances="<?php echo $data['DutyInstances'] ?>"
                                    data-row-counter="<?php echo $rowCounter ?>"
                                    data-cell-counter="<?php echo $blankCellCounter ?>" 
                                    data-duty-instance-ids="<?php echo $data['InstanceIds'] ?>" 
                                    data-next-instance-id="0" 
                                    data-remain-duty-instances="0" 
                                    date-range-days="<?php echo $dateRangeDays?>" 
                                    date-start-date="<?php echo $request->get('startDate')?>" 
                                    date-end-date="<?php echo date( 'Y-m-d', strtotime( $request->get('endDate') . ' -1 day '))?>"
                                    >
                                        <span class="dutyTip"><b><?php echo substr($data['DutyName'], 0, 17); ?></b><br>
                                        <?php if (!is_null($data['DutyName'])) :?>
                                        <?php 
                                        if($data['StartTime'] != 0 || $data['EndTime'] != 0){
                                            echo gmdate("H:i", $data['StartTime']).' - '.gmdate("H:i", $data['EndTime']);
                                        }
                                        ?>
                                        <?php endif ?>
                                        </span>
                                    </div>
                                </div>
                        <?php else: ?>
                            <div id="rowUnqId_<?php echo $date.'_'.$rowCounter.'_'.$blankCellCounter; ?>" class="NotFixedon weekly-body-cell ulrow_<?php echo $rowCounter;?> unalloc-cell" data-order="U-">
                                <div id="colUnqId_<?php echo $date.'_'.$blankCellCounter; ?>" class="unallocated textoverflow unallocother-drag-drop uldutycell_<?php echo $date?>" data-occupied=0 data-source="unallocated" data-date="<?php echo $date; ?>" data-scheduling-person="0" data-row-counter="<?php echo $rowCounter ?>" data-cell-counter="<?php echo $blankCellCounter ?>" data-unique-id="<?php echo $date.'_'.$rowCounter.'_'.$blankCellCounter; ?>" date-range-days="<?php echo $dateRangeDays?>" date-start-date="<?php echo $request->get('startDate')?>" date-end-date="<?php echo date( 'Y-m-d', strtotime( $request->get('endDate') . ' -1 day '))?>">
                                    <span> &nbsp;<br></span> 
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php $blankCellCounter++;?>
                    <?php endforeach; ?>
                </div>
                <?php $rowCounter = ($rowCounter+200);?>
            <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>
<?php if($request->get('loadFileJs') == 'Yes' || $request->get('loadFileJs') == ''){?>
<?php echo '<script type="text/javascript" src="js/allocations/weekly/drag-drop.js"></script>';?>
<?php }?>