<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set("zlib.output_compression", 1);
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../../../function-includes/DB_Functions.php';
require_once __DIR__ . '/service/AllocationService.php';
require_once __DIR__ . '/service/CreateWeekService.php';
require_once __DIR__ . '/service/TimeDimensionService.php';
include_once __DIR__ . "/../../../function-includes/genericfunctions.php";
include_once __DIR__ . '/filters/filters-script.php';

$request = Request::createFromGlobals();

$service = new AllocationService();
$createWeekService = new CreateWeekService();
$req = json_encode($_REQUEST);


if ($request->get('previous', false)) {
    $request->request->set('weekNumber', $_SESSION['previousStartWeek']);
}

$request = $service->prepareRequestDates($request);
$reqParamsForDivReload = [
    'schedulingTeamId' => $request->get('schedulingTeamId'),
    'userId' => $request->get('userId'),
    'weekNumber' => $request->get('weekNumber'),
    'teamId' => $request->get('teamId'),
    'newAvailablePerson' => $request->get('newAvailablePerson'),
    'days' => $request->get('days'),
    'startDate' => $request->get('startDate'),
    'endDate' => $request->get('endDate'),
    'startWeekDate' => $request->get('startWeekDate'),
    'endWeekDate' => $request->get('endWeekDate'),
    'startWeek' => $request->get('startWeek'),
    'endWeek' => $request->get('endWeek'),
    'filterAndSkill' => $request->get('filterAndSkill'),
    'filterAndDutyLabel' => $request->get('filterAndDutyLabel'),
];
$reqParamsForDivReload = json_encode($reqParamsForDivReload);

require_once 'header.php';

//find the leave year.
$leaveYear = new Carbon();
$leaveYear->subYear();
$leaveYear->month(4);
$leaveYear->startOfMonth();

$hasWeekData = true;
$currentDate = $request->get('startWeekDate') ? Carbon::createFromFormat('Y-m-d', $request->get('startWeekDate')) : '';
[$week, $year] = explode('/', (string) $request->get('weekNumber'));

$timeDimensionService = new TimeDimensionService();
$quWeekNum = $year . $week;
$validWeek = $timeDimensionService->checkValidWeekOfYear($quWeekNum);
$pvTeamId = ($request->get('teamId') != '') ? $request->get('teamId') : 0;
$arrStaffOptions = $service->getRolePermissionEditWeekly($pvTeamId);

if (($arrStaffOptions['isTeamAdmin'] == 1)) {
    $backdate7yr = strtotime('-2 year', strtotime(new Carbon(date('Y-m-d'))));
    $edbackdate7yr =  date('Y-m-d',strtotime('2 year', strtotime(new Carbon(date('Y-m-d')))));
} else {
    $backdate7yr = strtotime('-3 month', strtotime(new Carbon(date('Y-m-d'))));
    $edbackdate7yr =  date('Y-m-d',strtotime('-3 month', strtotime(new Carbon(date('Y-m-d')))));
}
$backdate7yralternate = strtotime(getenv('ACCESS_DATE'));
if ($backdate7yralternate > $backdate7yr) {
    $validDateNo = $backdate7yralternate;
} else {
    $validDateNo = $backdate7yr;
}
$validDate = date('jS F Y', $validDateNo);

$backDateWeekNum = '';
if($edbackdate7yr != ''){
    $getBackDateWeek = $timeDimensionService->findByDateWithoutCarbon($edbackdate7yr);
    $backDateWeekNum = isset($getBackDateWeek->ixYearWeek) ? (int) $getBackDateWeek->ixYearWeek : '';
}

$weekStartDateWeekNum = '';
if($request->get('startDate') != ''){
    $getWeekStartDateWeek = $timeDimensionService->findByDateWithoutCarbon($request->get('startDate'));
    $weekStartDateWeekNum = isset($getWeekStartDateWeek->ixYearWeek) ? (int) $getWeekStartDateWeek->ixYearWeek : '';
}
$checkIfWeekExists = $createWeekService->checkIfWeekExists($request);
$isWeekCreated = $checkIfWeekExists['isWeekCreated'];


?>
<?php if($validDateNo > strtotime((string) $request->get('startDate')) && ($request->get('startDate') != '')){?>
    <?php if($backdate7yralternate > strtotime((string) $request->get('startDate'))){?>
        <script type="text/javascript">
            customAlert("Allocate cannot open dates before <?php echo date('jS F Y',strtotime($backdate7yralternate)); ?> in the Edit Weekly Allocations view.");
        </script>
    <?php $hasWeekData = false;} else {?>
        <script type="text/javascript">
            let backDateWeekNum = parseInt('<?php echo $backDateWeekNum?>');
            let weekStartDateWeekNum = parseInt('<?php echo $weekStartDateWeekNum?>');
            let isMenuCall = parseInt('<?php echo $request->get('isMenuCall')?>');
            if(((isMenuCall == undefined) || (isMenuCall == '')) && (weekStartDateWeekNum < backDateWeekNum)){
                customConfirm('You do not have permissions to edit this week, but you can view it.',function(){
                        teamUpdate($('#searchTeamId').val(),$('#userId').val());
                    },
                    function() {
                    }
                );
                $('#yes').val('OK');
                $('#no').hide();
            } else {
                $.facebox.close();
            }
        </script>
    <?php }?>
<?php } else if ($week > (int) ($validWeek->ixWeekInYear ?? 0)) { ?>
    <script type="text/javascript">
        let ixWeekInYear = parseInt('<?php $validWeek->ixWeekInYear ?? 0;?>');
        if(ixWeekInYear > 0){
            customConfirmModal(
                "Please enter valid week number",
                function(){
                    var request = {};
                    request.previous = true;
                    request.teamId = <?php echo $request->get('teamId'); ?>;
                    EditAllocations(request.teamId);
                },
                function(){},
                0,
                'EditWeekly'
            );
        }
    </script>
<?php $hasWeekData = false;} else if($isWeekCreated == 0) {?>
    <script type="text/javascript">
		$('#loading').show();
     	 var request = <?php echo json_encode($request->request->all()) ?>;
		 let argteamId = parseInt('<?php echo $request->get('teamId'); ?>');
		 let argweeknumber = '<?php echo $request->get('weekNumber');?>';
		 let onlyyear = argweeknumber.split('/')[1];
		 let onlyweek = argweeknumber.split('/')[0];
		 let finalargweeknumber = parseInt(onlyyear+onlyweek);
		 let argstartDate = '<?php echo $request->get('startDate');?>';
		 let argendDate = '<?php echo $request->get('endDate');?>';
		 let argendWeek = '<?php echo $request->get('endWeek');?>';
		 let onlyendweek= argendWeek.substring(4,6);
		 let onlyendyear= argendWeek.substring(0,4);
		 let finalargendweeknumber=onlyendweek+"/"+onlyendyear;
		 request.ajaxLoadParams='<?php echo $reqParamsForDivReload?>';
		 let rotaData = '<?php echo str_replace(chr(39), "&apos;", json_encode($checkIfWeekExists["rota"]));?>';
		 ShowAllocationsRotaInEditWeekly(argteamId,argweeknumber,finalargweeknumber,argstartDate,argendDate,argendWeek,finalargendweeknumber,request, 0, '', 0, rotaData);
		 $.facebox.close();
		 $('#loading').hide();
    </script>
<?php $hasWeekData = false;
} else {?>
    <script type="text/javascript">
        $.facebox.close();
    </script>
<?php }?>

<?php
if ($hasWeekData){
    $request->request->set('showDataType', 'ALL');
    if (($request->get('cancelmultiweek') != '') && ($request->get('cancelmultiweek') == 'yes')) {
        $endDate = date('Y-m-d', strtotime($request->get('startDate') . ' + 7 days'));
        $request->request->set('endDate', $endDate);
    }

    if($request->get('noSpCall') == '' && $isWeekCreated > 0){
        $decodealldata = array('unalloc'=>($checkIfWeekExists['unalloc'] ?? []), 'alloc'=>($checkIfWeekExists['alloc'] ?? []));
        $unAllocatedDutyData = (isset($decodealldata['unalloc']) && !empty($decodealldata['unalloc'])) ? $decodealldata['unalloc'] : [];
        $allocatedDutyData = (isset($decodealldata['alloc']) && !empty($decodealldata['alloc'])) ? $decodealldata['alloc'] : [];
    }

    if (!empty($unAllocatedDutyData)) {
        usort($unAllocatedDutyData, function($x, $y) {
            return strnatcmp( strtolower($x[0] . $x[4] . $x[5]) , strtolower($y[0] . $y[4] . $y[5]));
        });


        $unqDutyIdArr = [];
        foreach ($unAllocatedDutyData as $unK => $unV) {
            $unqDutyIdArr[$unV[0] . $unV[4] . $unV[5]][$unV[9]][] = $unV[15];
        }

        foreach ($unAllocatedDutyData as $unKe => $unVa) {
            sort($unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
            $unAllocatedDutyData[$unKe]['InstanceIds'] = implode(',', $unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
            $unAllocatedDutyData[$unKe][14] = count($unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
        }

        $unAllocatedDutyData = json_encode($unAllocatedDutyData);
    } else {
        $unAllocatedDutyData = '""';
    }

    if (!empty($allocatedDutyData)) {
        $allocatedDutyData = json_encode($allocatedDutyData);
    } else {
        $allocatedDutyData = '""';
    }
    $areaID = $_SESSION['user']['AreaID'] ?? 0;
    $dutyType = 0;
    $isArchived = 0;
    $strsearch = '';
    $getMiscDutyList = ListAllMasterDuties($areaID, $dutyType, $isArchived, $request->get('teamId'), $strsearch);

    $dateWeekNumber = $week;
    $dateWeekYear = $year;
    $nextWeek = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($year . $week);
    $nextWeekNum = substr(strval($nextWeek->ixYearWeek), 4, 2);
    $nextWeekYear = substr(strval($nextWeek->ixYearWeek), 0, 4);

    $dWeekStartDate = $timeDimensionService->getWeekStartDate($dateWeekNumber . '/' . $dateWeekYear)->dDateTime;
    $dWeekEndDate = $timeDimensionService->getWeekEndDate($nextWeekNum . '/' . $nextWeekYear)->dDateTime;

    $datePickerDataArray = [
        'dateWeekNumber' => $dateWeekNumber,
        'dateWeekYear' => $dateWeekYear,
        'nextWeekNum' => $nextWeekNum,
        'nextWeekYear' => $nextWeekYear,
        'startDate' => $dWeekStartDate,
        'endDate' => $dWeekEndDate,
    ];

    $datePickerParam = json_encode($datePickerDataArray);
    $getDayIndicatorData = getDayIndicatorData($request->get('teamId'), $request->get('startDate'), $request->get('endDate'));

    $checkWeekAndRole = $service->checkWeekAndRole($request);
    $dataEdit = 0;
    if(($checkWeekAndRole['screenView'] == 0) && ($checkWeekAndRole['screenEdit'] == 1)){
        $dataEdit = 1;
    }
    ?>
    <link href="../styles/charging/charging.css" rel="stylesheet">
    <div id="loadingWeekly">
        <img border="0" src="images/loading.gif" width="145px" height="100px">
    </div>
    <input type="hidden" name="getDayIndicatorData" id="getDayIndicatorData" value='<?php echo base64_encode(json_encode($getDayIndicatorData)); ?>'>
    <input type="hidden" name="multiweekcount" id="multiweekcount">
    <input type="hidden" name="weekStartDate" id="weekStartDate">
    <input type="hidden" name="unallocBackColor" id="unallocBackColor">
    <input type="hidden" name="allocSortDate" id="allocSortDate" value="<?php if (empty($_COOKIE['allocSortDate'])) {echo 'NAME';} else {echo $_COOKIE['allocSortDate'];}?>">
    <input type="hidden" name="allocSortType" id="allocSortType" value="<?php if (empty($_COOKIE['allocSortType'])) {echo 0;} else {echo $_COOKIE['allocSortType'];}?>">
    <input type="hidden" name="unAllocSortDate" id="unAllocSortDate" value="0">
    <input type="hidden" name="unAllocSortType" id="unAllocSortType">
    <input type="hidden" name="unAllocSortDt" id="unAllocSortDt">
    <input type="hidden" name="allocSortDt" id="allocSortDt">
    <input type="hidden" name="miscDutyData" id="miscDutyData">
    <input type="hidden" name="dataPickerData" id="dataPickerData">
    <input type="hidden" name="weekNumHeader" id="weekNumHeader">
    <input type="hidden" name="setTop" id="setTop">
    <input type="hidden" name="editScreen" id="editScreen" value="<?php echo $dataEdit?>">
    <?php if($request->get('noSpCall') == ''){?>
    <div id="allocations-container" data-is-week-published="<?php echo $service->isWeekPublished($request) ? '1' : '0'; ?>" style="overflow: hidden; bottom:0px;">
        <div id="unallocatedDuty" class="grid-container resiz allocationSections resizeBox1">
            <?php if ($hasWeekData):?>
                <?php echo '<script>reloadeditweeklygrid(' . $unAllocatedDutyData . ',' . $allocatedDutyData . ',' . $getMiscDutyList . ',' . $datePickerParam . ',"");</script>'; ?>
            <?php endif;?>
        </div>
        <div id="allocatedDuty" class="grid-container resiz allocationSections resizeBox2"></div>
        <div id="showCountBlock" class="grid-container resiz allocationSections resizeBox3" style="display: none;"></div>
    </div>
    <?php } else {?>
        <div id="allocations-container" data-is-week-published="<?php echo $service->isWeekPublished($request) ? '1' : '0'; ?>" style="overflow: hidden; bottom:0px;">
            <div id="unallocatedDuty" class="grid-container resiz allocationSections resizeBox1" style="display: none;"></div>
            <div id="allocatedDuty" class="grid-container resiz allocationSections resizeBox2" style="display: none;"></div>
            <div id="showCountBlock" class="grid-container resiz allocationSections resizeBox3" style="display: none;"></div>
        </div>
    <?php }?>
    <div id="finalPgCl" style="display: none;"></div>
    <div id="dragDropId1" style="display: none;"></div>
    <div id="dragDropId2" style="display: none;"></div>
    <div id="dutyExists" style="display: none;"></div>
    <div id="unallocdutycount" style="display: none;"></div>
    <div id="allocdutycount" style="display: none;"></div>
    <div class="tooltip-div" id="tooltipDiv" aria-atomic="true"></div>
    <div id="sickAttr" style="display: none;"></div>
    <div id="sourceDataCell" style="display: none;"></div>
    <div id="targetDataCell" style="display: none;"></div>
    <div id="personRowHighlight" style="display: none;"></div>
    <div id="schedulingNotesContainer" style="position: absolute; z-index: 9999;"></div>
<?php }?>
