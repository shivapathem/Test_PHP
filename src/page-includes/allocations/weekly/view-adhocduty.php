<?php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;
require_once __DIR__ . '/service/AllocationService.php';

$service = new AllocationService();
$request = $request ?? $service->prepareRequestDates(Request::createFromGlobals());

$viewAdhocStWeekDate = $request->get('startDate');
$days = 6;
$allocateds = new Collection($service->getAdhocDuties($request));

//group by person and date
$allocateds = $allocateds->groupBy(['ScheduledPersonID','DutyDate']);

//generate period as the day count
$periodStart = new Carbon($request->get('startDate'));
$periodEnd = new Carbon($request->get('startDate'));
$periodEnd->addDays($days);
$period = CarbonPeriod::create($periodStart, $periodEnd);
$period = $period->toArray();

$reqParamsForDivReload = [
    'schedulingTeamId' => $request->get('teamId'),
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
    'endWeek' => $request->get('endWeek')
];
$reqParamsForDivReload = json_encode($reqParamsForDivReload);

$checkWeekAndRole = $service->checkWeekAndRole($request);
$dataEdit = 0;
if(($checkWeekAndRole['screenView'] == 0) && ($checkWeekAndRole['screenEdit'] == 1)){
    $dataEdit = 1;
}

require_once "header.php";
?>
    <script type="text/javascript" src="js/allocations/weekly/weekly.js?v=<?php echo time(); ?>"></script>
    <script type="text/javascript">

        $("#searchTeamId").val('<?php echo $request->get('teamId'); ?>');
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
        $("#unAllocDutyData").val('<?php echo $request->get('unAllocDutyData'); ?>');
		 $("#showWeeks").val('1');

    </script>

    <style>
        .ui-dialog.editAdhocDutyClass {
            z-index: 999999 !important;
        }
        .ui-dialog.editAdhocDutyClass .adhoc-timepicker {
            z-index: 999999 !important;
        }
        .ui-dialog .editAdhocDutyClass .ui-widget-overlay {
            z-index: 3 !important;
        }

    </style>
	<div id="dialog-form" title="Edit Adhoc Duty"></div>
	<div id="dialog-form-comment" ></div>
	<div id="dialog-form-history" title="Duty History"></div>
    <div id="stWeekDate" style="display:none;"><?php echo $request->get('startWeekDate')?></div>
    <div id="stDate" style="display:none;"><?php echo $request->get('startDate')?></div>

    <div id="weeklyAllocationTable" class="weeklyAllocationTable weekly-table scrollHorizontal dropzone" data-source="unallocated" data-charging-present="0" style="width:100%">
        <div class="weekly-table-header">
            <div class="weekly-table-row unallocatTable1Head">
                <div class="min-w120 weekly-header-cell">Name</div>
                <?php foreach ($period as $date) : ?>
                    <div class="min-w120 weekly-header-cell">
                        <?php echo $date->format('l'); ?>
                        <br>
                        <?php echo $date->format('d/m/Y'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="weekly-table-body">
            <?php foreach ($allocateds as $id => $allocations) {?>
                <?php
                    $data = $allocations->first()->first();
                    $userName = $data['DisplayName'];
                ?>
                <div class="weekly-table-row">
                    <div class="NotFixedon weekly-body-cell">
                        <div class="max-height-duty-cell"><?php echo $data['DisplayName']; ?></div>
                    </div>
                    <?php foreach ($period as $date) {
                    if($allocations->has($date->format('Y-m-d'))){
                        $data = $allocations->get($date->format('Y-m-d'))->first();

                        if($data['EndTime'] == 86400) {
                            $data['EndTime'] = 0;
                        }
                    ?>
                    <div class="NotFixedon weekly-body-cell context-menu-adhoc" data-order="<?php echo $data['DutyName'].$userName; ?>" id="cellID_<?php echo $date->format('Y-m-d'); ?>_<?php echo $data['ScheduledPersonID']; ?>">
                        <div class="item-cell allocated" data-id="<?php echo $data['MasterDutyID']; ?>"  data-teamId="<?php echo $request->get('teamId'); ?>" data-startDate="<?php echo $request->get('startDate'); ?>" data-endDate="<?php echo $request->get('endDate'); ?>" data-days="<?php echo $request->get('days'); ?>" data-colorId = "<?php echo $data['DutyColourID'];?>" data-date="<?php echo $date->format('Y-m-d'); ?>" data-edit-screen="<?php echo $dataEdit;?>">
                            <span class="dutyTip">
                                <b><?php echo substr($data['DutyName'],0,17); ?></b>
                                <br>
                                <?php if (!is_null($data['DutyName'])) {?>
                                    <?php echo sprintf('%02d:%02d', ($data['StartTime']/ 3600),($data['StartTime']/ 60 % 60)); ?> - <?php echo sprintf('%02d:%02d', ($data['EndTime']/ 3600),($data['EndTime']/ 60 % 60)); ?>
                                <?php } ?>
                            </span>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="NotFixedon weekly-body-cell allocatedDutyCell" data-order="U-<?php echo $userName; ?>" data-source="allocated" data-id="<?php echo $data['MasterDutyID']; ?>">
                        <div class="max-height-duty-cell" data-date="<?php echo $date->format('Y-m-d'); ?>" data-occupied=0 data-source="allocated" data-id="<?php echo $data['MasterDutyID']; ?>" data-scheduling-person="<?php echo $data['ScheduledPersonID']; ?>">
                            <span class="dutyTip" title="U">
                                U
                            </span>
                        </div>
                    </div>
                    <?php }} ?>
                </div>
            <?php } ?>
        </div>
    </div>