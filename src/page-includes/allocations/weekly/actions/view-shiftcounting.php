<?php 
use Carbon\Carbon;
use Faker\Factory;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '../../service/CreateWeekService.php';
$service = new AllocationService();
$createWeekService = new CreateWeekService();

$request = $request ?? $service->prepareRequestDates(Request::createFromGlobals());

$days = $request->get('days');

if($request->get('weekCount') > 1){
    $days = 7;
    $queryDays = (($request->get('weekCount') * $days));
    $days = (($request->get('weekCount') * $days)-1);
    $dateRangeDays = $queryDays;

    $endDate = date('Y-m-d', strtotime($request->get('startDate'). ' + '.$queryDays.' days'));
    $request->request->set('days',$days);
    $request->request->set('endDate',$endDate);
    $request->request->set('endWeekDate',$endDate);

    $periodStart = new Carbon($request->get('startDate'));
    $periodEnd = clone $periodStart; 
    $periodEnd->addDays((int)$days);
    $period = CarbonPeriod::create($periodStart, $periodEnd);
    $period = $period->toArray();
} else {
    $periodStart = new Carbon($request->get('startDate'));
    $periodEnd = clone $periodStart; 
    $periodEnd->addDays((int)$days);
    $period = CarbonPeriod::create($periodStart, $periodEnd);
    $period = $period->toArray();
}

$shiftCountingData = [];
if($request->get('dutyCountFilterCols') != ''){
    $dutyCountFilterData = $request->get('dutyCountFilterCols');
    $dutyCountFilterData = rtrim($dutyCountFilterData, ",");
    $expDutyCountFilterData = explode(',',$dutyCountFilterData);
    $shiftCountingData['detailsShow'] = 'N';
    $shiftCountingData['totalShow'] = 'N';
    $shiftCountingData['cols'] = [];
    if(in_array('d',$expDutyCountFilterData)){
        $shiftCountingData['detailsShow'] = 'Y';
    }
    if(in_array('t',$expDutyCountFilterData)){
        $shiftCountingData['totalShow'] = 'Y';
    }
    foreach($expDutyCountFilterData as $filterAlphabets){
        if($filterAlphabets == 'd'){
            continue;
        }
        if($filterAlphabets == 't'){
            continue;
        }
        $alphabets[] = $filterAlphabets;
    }

    foreach ($period as $date){
        foreach($alphabets as $val){
            $shiftCountingData['cols'][$val][$date->format('Y-m-d')]['problem'] = 0;
            $shiftCountingData['cols'][$val][$date->format('Y-m-d')]['solution'] = 0;
        }
    }

    $editWeeklyData = $createWeekService->checkIfWeekExists($request);
    if((!empty($editWeeklyData)) && (isset($editWeeklyData['isWeekCreated'])) && ($editWeeklyData['isWeekCreated'] == 1)){
        $decodealldata = array('unalloc'=>$editWeeklyData['unalloc'], 'alloc'=>$editWeeklyData['alloc']);
        $unAllocatedDutyData = (isset($decodealldata['unalloc']) && !empty($decodealldata['unalloc'])) ? $decodealldata['unalloc'] : [];
        $allocatedDutyData = (isset($decodealldata['alloc']) && !empty($decodealldata['alloc'])) ? (empty($_SESSION['filterdata']) ? $decodealldata['alloc'] : $_SESSION['filterdata']) : [];
    }

    $unalloatedData = [];
    if(!empty($unAllocatedDutyData)){
        foreach($unAllocatedDutyData as $k1 => $v1){
            $unalloatedData[$v1[0]][$v1[9]][] = $v1;
        }
        foreach($unalloatedData as $dutyName => $unAllocated){
            foreach ($period as $date){
                if(isset($unAllocated[$date->format('Y-m-d')])){
                    $data = $unAllocated[$date->format('Y-m-d')][0];
                    if(isset($alphabets) && !empty($alphabets)){
                        $secondAlphabet = strtoupper(substr($data[0] ?? '', 1, 1));
                        if(ctype_digit($secondAlphabet)){
                            foreach($alphabets as $val){
                                $firstAlphabet = strtoupper(substr($data[0] ?? '', 0, 1));
                                if($firstAlphabet == $val){
                                    if($data['InstanceIds'] == 1){
                                        $shiftCountingData['cols'][$val][date('Y-m-d',strtotime($date->format('Y-m-d')))]['problem']++;
                                    } else {
                                        $shiftCountingData['cols'][$val][date('Y-m-d',strtotime($date->format('Y-m-d')))]['problem'] = ($shiftCountingData['cols'][$val][date('Y-m-d',strtotime($date->format('Y-m-d')))]['problem'] + $data['InstanceIds']);
                                    }
                                }
                            }
                        }
                    } else {
                        $shiftCountingData = [];
                    }
                }
            }
        }
    }

    $alloatedData = [];
    if(!empty($allocatedDutyData)){
        foreach($allocatedDutyData as $k1 => $v1){
            if(trim(strtoupper($v1[1])) != 'U'){
                $alloatedData[$v1[15]][$v1[16]][] = $v1;
            }
        }

        foreach ($alloatedData as $id => $allocations){
            $data = count($allocations) ? array_values($allocations)[0][0] : [];
            foreach ($period as $date){
                if(isset($allocations[$date->format('Y-m-d')])){
                    $data = $allocations[$date->format('Y-m-d')][0];
                    if(!empty($shiftCountingData)){
                        foreach($alphabets as $val){
                            $firstAlphabet = strtoupper(substr($data[1], 0,2));
                            $fAlphabet = strtoupper(substr($data[1] ?? '', 0, 1));
                            if(((strlen($data[1]) == 1) && ($fAlphabet == $val)) || ($firstAlphabet == $val.' ') || ($firstAlphabet == $val.'-')){
                                $shiftCountingData['cols'][$val][$date->format('Y-m-d')]['solution']++;
                            }
                        }
                    } else {
                        if(isset($alphabets) && !empty($alphabets)){
                            foreach($alphabets as $val){
                                $firstAlphabet = strtoupper(substr($data[1], 0,2));
                                $fAlphabet = strtoupper(substr($data[1] ?? '', 0, 1));
                                if(((strlen($data[1]) == 1) && ($fAlphabet == $val)) || ($firstAlphabet == $val.' ') || ($firstAlphabet == $val.'-')){
                                    $shiftCountingData['cols'][$val][$date->format('Y-m-d')]['solution']++;
                                }
                            }
                        } else {
                            $shiftCountingData = [];
                        }
                    }
                }
            }
        }
    }
}
$getSchedulingTeamDetails = $service->getSchedulingTeamDetails($request);
?>

<div class="block-25 grid-item">
    <div id="showCountLeft" class="showCountContainer scrollCountVertical">
	<div id="showCountsLeft" class="display weeklyAllocationTable weekly-table" style="width:100%">
            <div class="weekly-table-header">
                <div class="weekly-table-row" style="height:27px">
                    <div class="weekly-header-cell"><?php echo $getSchedulingTeamDetails['schedulingTeamName'];?></div>
                    <div class="weekly-header-cell"> Duty</div>
                </div>
            </div>
			<div class="weekly-table-body">
			<?php if(empty($shiftCountingData)){?>
                <div class="weekly-table-row"><div class="weekly-body-cell">No Data Available</div></div>
            <?php } else {?>
                <?php 
                $detailRowClass = '';
                $totalRowClass = '';
                $total = []; 
                $totalSumLeftFinal = 0;
                foreach($shiftCountingData['cols'] as $shiftCountDataColKey => $shiftCountDataColVal){
					$totalSumLeft = 0;
                    if($shiftCountingData['detailsShow'] != 'Y'){
                        $detailRowClass = 'style="display:none;"';
                    }
                    if($shiftCountingData['totalShow'] != 'Y'){
                        $totalRowClass = 'style="display:none;"';
                    }

                    foreach ($shiftCountDataColVal as $dataKey => $dataVal){
                        $shiftCountNum = ($dataVal['solution']-$dataVal['problem']);
                        $total[$dataKey][] = $shiftCountNum;
                    }

                    if($shiftCountingData['totalShow'] == 'Y'){
                        foreach ($period as $date){
                            $date = $date->format('Y-m-d');
                            $columnSum = array_sum($total[$date]);
                            $totalSumLeft = ($totalSumLeft + $columnSum);
                        }
                    }
                ?>
                    <div class="weekly-table-row" <?php echo $detailRowClass;?>>
                        <div class="weekly-body-cell">&nbsp;</div>
                        <div class="weekly-body-cell"><?php echo $shiftCountDataColKey?></div>
                    </div>
                <?php $totalSumLeftFinal = $totalSumLeft;}?>
                <div class="weekly-table-row" <?php echo $totalRowClass;?>>
                    <div class="weekly-body-cell">&nbsp;</div>
                    <div class="weekly-body-cell" style="background-color:#56db63 !important;">Total (<?php echo $totalSumLeftFinal;?>)</div>
                </div>
            <?php }?>
		</div>
	</div>
	</div>
</div>
<div class="block-75 grid-item">
    <div id="showCountRight" class="showCountContainer scrollHorizontal scrollCountVertical">
        <div id="showCounts" class="display weeklyAllocationTable weekly-table dataTable no-footer" style="width:100%">
            <div class="weekly-table-header">
                <div class="weekly-table-row">
                    <?php foreach ($period as $date):?>
                    <div class="min-w120 weekly-header-cell">
                        <?php echo $date->format('l'); ?>
                        <br>
                        <?php echo $date->format('d/m/Y'); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
             <div class="weekly-table-body">
                <?php if(empty($shiftCountingData)){?>
                    <div class="weekly-table-row"><div class="weekly-body-cell">No Data Available</div></div>
                <?php } else {?>
                    <?php $total = []; foreach($shiftCountingData['cols'] as $shiftCountDataColKey => $shiftCountDataColVal){?>
                    <?php 
                    if($shiftCountingData['detailsShow'] != 'Y'){
                        $detailRowClass = 'style="display:none;"';
                    }
                    if($shiftCountingData['totalShow'] != 'Y'){
                        $totalRowClass = 'style="display:none;"';
                    }
                    ?>
                    <div class="weekly-table-row" <?php echo $detailRowClass?>>
                        <?php 
                        foreach ($shiftCountDataColVal as $dataKey => $dataVal){
                            $shiftCountNum = ($dataVal['solution']-$dataVal['problem']);
                            $weekday = DateTime::createFromFormat('Y-m-d', $dataKey);
                            $total[$dataKey][] = $shiftCountNum;
                            $satSun = 'No';
                            if((ucfirst($weekday->format('l')) == 'Saturday') || (ucfirst($weekday->format('l')) == 'Sunday')){
                                $bgColour = '#FFFFA7';
                                $satSun = 'Yes';
                            }
                            if($shiftCountNum < 0){
                                $bgColour = '#ed5f5f';
                            }
                            if($shiftCountNum == 0 && $satSun == 'No'){
                                $bgColour = '#a7b5a9';
                            }
                            if($shiftCountNum > 0){
                                $bgColour = '#5fe4ed';
                            }
                        ?>
                            <div class="showCountCell weekly-body-cell" style="background-color: <?php echo $bgColour;?>;" id="cellCountSum_<?php echo $weekday->format('Y-m-d').'_'.$shiftCountDataColKey; ?>"><?php echo $shiftCountNum;?></div> 
                        <?php }?>
                    </div>
                    <?php }?>
                    <div class="weekly-table-row" <?php echo $totalRowClass?>>
                        <?php 
                        foreach ($period as $date){
                            $date = $date->format('Y-m-d');
                            $columnSum = array_sum($total[$date]);
                            if($columnSum < 0){
                                $totalBgColour = '#ed5f5f';
                            }
                            if($columnSum == 0){
                                $totalBgColour = '#56db63';
                            }
                            if($columnSum > 0){
                                $totalBgColour = '#5fe4ed';
                            } 
                        ?>
                        <div class="showCountCell weekly-body-cell" style="background-color: <?php echo $totalBgColour;?>;" id="cellCountSum_<?php echo $date; ?>"><?php echo $columnSum;?></div>
                        <?php }?>
                    </div>
                <?php }?>
            </div>
        </div>
	</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
		$('.scrollCountVertical').scroll(function() {
			$('.scrollCountVertical').scrollTop($(this).scrollTop());
		});
	});
</script>