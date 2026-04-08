<?php 
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;
    use Illuminate\Support\Collection;
    use Symfony\Component\HttpFoundation\Request;
    
    require_once __DIR__ . '/../service/AllocationService.php';
    require_once __DIR__ . '/../../../../function-includes/allocationsfunctions.php';
    $service = new AllocationService();
    $request = $request ?? $service->prepareRequestDates(Request::createFromGlobals());
    
    $days = $request->get('days');

    if($request->get('fireQuery') == 'Yes'){
        if($request->get('weeks') > 1){
            $days = 7;
            $queryDays = (($request->get('weeks') * $days));
            $dateRangeDays = $queryDays;
            $days = (($request->get('weeks') * $days)-1);
    
            $endDate = date('Y-m-d', strtotime($request->get('startDate'). ' + '.$queryDays.' days'));
            $request->request->set('days',$days);
            $request->request->set('endDate',$endDate);
            $request->request->set('endWeekDate',$endDate);
        } else {
            $sTDate = strtotime($request->get('startDate'));
            $eDDate = strtotime($request->get('endDate'));
            $datediff = $eDDate - $sTDate;
            $dateRangeDays = round($datediff / (60 * 60 * 24));
        }
        if($request->get('date') != ''){
            $request->request->set('startDate',date('Y-m-d',strtotime($request->get('date'))));
            $endDate = date('Y-m-d', strtotime($request->get('startDate'). ' +7 days'));
            $request->request->set('endDate',$endDate);
        }
        $request->request->set('showDataType','ALLOC');
        $allocatedsdata  = $service->getEditWeeklyData($request);
        $decodeallocateds = json_decode($allocatedsdata,true);
        $allocateds = $decodeallocateds['alloc'];
    
        $periodStart = new Carbon($request->get('startDate'));
        $periodEnd = clone $periodStart; $periodEnd->addDays($days);
        $period = CarbonPeriod::create($periodStart, $periodEnd);
        $period = $period->toArray();
    } else {
        $allocateds = json_decode(stripslashes($request->get('editWeeklydutyData')));
        $allocateds = json_decode(json_encode($allocateds), true);
    
        $periodStart = new Carbon($request->get('startDate'));
        $periodEnd = clone $periodStart; $periodEnd->addDays($days);
        $period = CarbonPeriod::create($periodStart, $periodEnd);
        $period = $period->toArray();
        $dateRangeDays = 7;
    }
    $alloatedData = [];
		
	$sortval=SORT_ASC;
	
	if (isset($_REQUEST['action']) && ($_REQUEST['action']=='Sort')){
		
		if (isset($_REQUEST['sortval'])){
			if ($_REQUEST['sortval']=="SORT_ASC"){
				$sortval= SORT_ASC;
			}  else  {
				$sortval= SORT_DESC;
			}	
			$allocateds =json_decode($_REQUEST['allocatedData'],true);
			$sortcolumn =$_REQUEST['sortcolumn'];
			if (isset($_REQUEST['datadate']) && ($_REQUEST['datadate']!='')){
				$newary = array();
				foreach($allocateds as $k => $value)
				{
					if($value['DutyDate'] == $_REQUEST['datadate'])
					{
					  $newary[] = $allocateds[$k];		
					  unset($allocateds[$k]);
					}
				}
				array_multisort(array_map(function($element) {
				  return strtolower($element['DutyName']);
				}, $newary), $sortval, $newary);
				  
				$allocateds=array_merge_recursive($newary,$allocateds); 
			} else {
				array_multisort(array_map(function($element) {
				  return strtolower($element['DutyName']);
				}, $allocateds), $sortval, $allocateds);
				  
			}	
		 echo json_encode($allocateds);
		 exit();
		}
	}	
	
	if(!empty($allocateds)){  
	    foreach($allocateds as $k1 => $v1){
            if(isset($v1['SchedulingPersonID']) && $v1['SchedulingPersonID'] != 0 && $v1['SchedulingPersonID'] != ''){
                $alloatedData[$v1['SchedulingPersonID']][$v1['DutyDate']][] = $v1;
            }
        }
    }
	$_SESSION['previousStartWeek'] = $request->get('weekNumber');
    
    function secondsIntoLongHrsMin($ss){
        $init = $ss;
        $hours = floor($init / 3600);
        $minutes = floor(($init / 60) % 60);
        $hours = str_pad($hours,2,"0",STR_PAD_LEFT);
        $minutes = str_pad($minutes,2,"0",STR_PAD_RIGHT);
        return "$hours:$minutes";
    }
    
    $dowMap = array (
        "Sat"  => 0,
        "Sun"  => 1,
        "Mon"  => 2,
        "Tue"  => 3,
        "Wed"  => 4,
        "Thu"  => 5,
        "Fri"  => 6
    );
    
    $teamDefaults = GetTeamDefaults(0,$request->get('teamId')); 
    
    $teamDefaults = isset($teamDefaults[$request->get('teamId')]) ?  $teamDefaults[$request->get('teamId')] : [];
    
    $today = new Carbon();
    ?>
<style type="text/css">
    .hide-tb-tr{
    display: none;
    }
</style>
<script type="text/javascript">
    var request = <?php echo json_encode($request->request->all()); ?>;
</script>
<div class="block-25 grid-item">
    <div id="weeklyAllocation-1" class="scrollVertical" onscroll="divScrollL();" onmouseover="removetip();">
        <div id="teamPeoplesBlockPos" name="teamPeoplesBlockPos"></div>
        <div id="weeklyAllocation1" class="weeklyAllocationTable weekly-table" style="width:100%">
            <div class="weekly-table-header allocLeftTableHead">
                <div class="filter weekly-table-row">
                    <?php if($request->get('weeks') > 1){?>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="DisplayLastName">Name</div>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="SortCode">Code</div>
                    <?php } else {?>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="DisplayLastName">Name</div>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="SortCode">Code</div>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="ContractedHours">Hrs</div>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="ACC">ACC</div>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="pay">FSV</div>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="ManualEDP">EDP</div>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="AccDays">EFT</div>
                        <div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="DisplayLastName">Days</div>
                    <?php }?>
                </div>
            </div>
            <div class="weekly-table-body allocLeftTableData">
                <?php if(!empty($alloatedData)){?>
                <?php foreach ($alloatedData as $id => $allocations) { 
                    array_values($allocations);
					$data = array_shift($allocations)[0];
                    if($data['DisplayName']){
                        $userName = $data['DisplayName'];
                    }
                    $eft = $data['EFT'] == "1.000" ? round($data['EFT']) : "0.000";
                    $accdays = $data['AccDays'] != '' ? (int)$data['AccDays'] : "0";
                    $edp = $data['ManualEDP'] == 0 ? "C" : "M";
                    
                    $bgClass = $data['IsHomeTeam'] ? 'tdGrayBG' : 'tdGrayBGAdditionalPerson';
                    $cellStyle = '';
                    if(!empty($data['PersonBackgroundColour'])){
                        $cellStyle .= 'background-color:'.$data['PersonBackgroundColour'].' !important;';
                    }
                    if(!empty($data['PersonFontColour'])){
                        $cellStyle .= ' color:'.$data['PersonFontColour'].' !important;';
                    }
                    
                    if($userName != ''){?>
                        <div class="weekly-table-row schRowLeft<?php echo $data['SchedulingPersonID']; ?>">
                            <?php if($request->get('weeks') > 1){?>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell peronname_<?php echo $data['SchedulingPersonID']; ?>" title="<?php echo $userName; ?> <?php if(!empty($data['StaffNumber'])){?> (<?php echo $data['StaffNumber']; ?>) <?php }?>" data-order="<?php echo $userName; ?>" style="<?php echo $cellStyle; ?>">
                                <?php 
                                        $strLastNameArray =str_split($data['DisplayLastName'],8);
                                        $strFirstNameArray =str_split($data['DisplayFirstName'],8);
                                        echo $strLastNameArray[0];  
                                        if(count($strLastNameArray)>2){
                                        echo "<br/>".substr($strLastNameArray[1],0,5)."..,";
                                        }else if(count($strLastNameArray)==2){
                                        echo "<br/>".substr($strLastNameArray[1],0,8).",";
                                        }else{
                                            echo ",";
                                        }
                                    
                                    if(!empty($strFirstNameArray)){
                                        if(count($strFirstNameArray)>1){
                                            echo "<br/>".substr($strFirstNameArray[0],0,6)."...";
                                            }else{
                                            echo "<br/>".$strFirstNameArray[0];
                                            }   
                                    }
                                    ?>
                                </div>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell centerText" title="<?php echo $data['SortCode']; ?>" data-schedulingpersonid ="<?php echo $data['SchedulingPersonID']; ?>"> <?php echo substr($data['SortCode'], 0, 10); ?></div>
                            <?php } else {?>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell peronname_<?php echo $data['SchedulingPersonID']; ?>" title="<?php echo $userName; ?> <?php if(!empty($data['StaffNumber'])){?> (<?php echo $data['StaffNumber']; ?>) <?php }?>" data-order="<?php echo $userName; ?>" style="<?php echo $cellStyle; ?>">
                                <?php 
                                        $strLastNameArray =str_split($data['DisplayLastName'],8);
                                        $strFirstNameArray =str_split($data['DisplayFirstName'],8);
                                        echo $strLastNameArray[0];  
                                        if(count($strLastNameArray)>2){
                                        echo "<br/>".substr($strLastNameArray[1],0,5)."..,";
                                        }else if(count($strLastNameArray)==2){
                                        echo "<br/>".substr($strLastNameArray[1],0,8).",";
                                        }else{
                                            echo ",";
                                        }
                                    
                                    if(!empty($strFirstNameArray)){
                                        if(count($strFirstNameArray)>1){
                                            echo "<br/>".substr($strFirstNameArray[0],0,6)."...";
                                            }else{
                                            echo "<br/>".$strFirstNameArray[0];
                                            }   
                                    }
                                    ?>
                                </div>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell centerText js_schdsortcode_<?php echo $data['SchedulingPersonID'];?>" id="js_schdsortcode" title="<?php echo $data['SortCode']; ?>" data-schedulingpersonid ="<?php echo $data['SchedulingPersonID']; ?>"> <?php echo substr($data['SortCode'], 0, 10); ?></div>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell centerText" id="hrs_<?php echo $data['SchedulingPersonID']; ?>" title="Contracted Hours: <?php echo $data['ContractedHours'] ?? "00.00"; ?>"><?php echo number_format((float) (($data['WeekDuration']) / 3600), 2, '.', ''); ?>
                                </div>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell centerText" title="<?php echo $data['ACC']; ?> (<?php echo $data['AccPeriod']; ?>)"><?php echo $data['ACC']; ?></div>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell centerText"><?php echo (isset($data['pay']) ? $data['pay'] : ''); ?></div>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell centerText"> <?php echo $edp; ?></div>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell centerText"><?php echo $eft ?? ""; ?></div>
                                <div class="<?php echo $bgClass; ?> weekly-body-cell centerText"><?php echo $accdays ?? 0; ?></div>
                            <?php }?>
                        </div>
                    <?php }?>
                <?php }?>
            </div>
        </div>
        <?php } else {?>
    </div>
</div>
<div class="noRecordFound">No Record Found</div>
<?php }?>            
</div>
</div>
<div class="block-75 grid-item">
    <?php if(!empty($allocations)){?>
    <?php 
	array_values($allocations);
	$firstAllocation = array_shift($allocations) ? array_shift($allocations)[0] : null; ?>
    <?php } else {?>
    <?php $firstAllocation = null; ?>
    <?php }?>
    <div id="weeklyAllocation-2" class="scrollHorizontal scrollVertical allocationSingleWeek">
        <div id="weeklyAllocation2" data-published='<?php echo $firstAllocation['isPublished'] ?? "0"; ?>' class="weeklyAllocationTable weekly-table scrollHorizontal" style="width:100%;">
            <div class="weekly-table-header">
                <div class="weekly-table-row allocatedTableHead">
                    <?php foreach ($period as $date) : ?>
                    <div class="min-w120 weekly-header-cell" data-date="<?php echo $date->format('Y-m-d'); ?>">
                        <?php echo $date->format('l'); ?>
                        <br>
                        <?php echo $date->format('d/m/Y'); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="weekly-table-body allocatedtablebody">
                <?php $rowCnt = 0;?>
                <?php if(!empty($alloatedData)){?>
                <?php foreach ($alloatedData as $id => $allocations) :?>
                <?php 
                    $alData = array_values($allocations);
					$data = array_shift($alData)[0];
                    $userName = $data['DisplayName'];
                    $eft = $data['EFT'];
                    $edp = $data['ManualEDP'] == 0 ? "C" : "M";
                    $isOccupied = 1;
                    ?>
                <div id="mainUnqId_<?php echo $rowCnt;?>" class="weekly-table-row allocRow schRowRight<?php echo $data['SchedulingPersonID']; ?>" data-scheduling-person="<?php echo $data['SchedulingPersonID']; ?>">
                    <?php  
                        $countLock=0;  
                        foreach ($period as $date) :
                           if(isset($allocations[$date->format('Y-m-d')])): ?>
                        <?php  $data = $allocations[$date->format('Y-m-d')][0];
                        // For Images Starts
                        $imageNameSignInClsName = '';
                        switch($data['ImageNameSignin']){
                            case "1":
                                $imageNameSignInClsName = 'signin-red-cross';
                                break;
                            case "2":
                                $imageNameSignInClsName = 'signin-green-tick';
                                break;
                            case "3":
                                $imageNameSignInClsName = 'signin-blue-tick';
                                break;
                            case "4":
                                $imageNameSignInClsName = 'msg-box-warning';
                                break;
                        }
                        //For Images Ends
                        if ($date->format('Y-m-d') == date("Y-m-d") && $teamDefaults['AllowInBuilding'] == 1) {
                            $js = ' onclick=\'javascript:SignInToDay("'.$data['SchedulingPersonID'].'",'.$data['ActionNameForSignin'].', "'.$date->format('Y-m-d').'", '.$request->get('teamId').', '.$data['ID'].')\';';
                        } else {
                            $js = ' onclick=\'javascript:SignInDay("'.$data['SchedulingPersonID'].'",'.$data['ActionNameForSignin'].', "'.$date->format('Y-m-d').'", '.$request->get('teamId').','.$data['ID'].')\';';
                        }
                        
                        $allowsignin = 0;
                        if($data['StartTime'] != 0 || $data['EndTime'] != 0) {
                            if ($teamDefaults['SignInDays'] > 0 
                            && $date->isBetween((new Carbon())->subDay(), (new Carbon)->addDays($teamDefaults['SignInDays']), true) 
                            ) {
                                $allowsignin = 1;
                            }
                        }
                        
                        $cellbgcolour = '';
                        $cellBgColor = 'ebebeb';
                        $sTime = (int) $data['StartTime'];
                        $eTime = (int) $data['EndTime'];
                        if((($data['ColourBackground'] != '') && ($attentionClsName == '') && ($sTime == 0 && $eTime == 0)) || (($attentionClsName == '') && ($data['MarkedSickness'] == 1)) || (strtolower($data['DutyName']) == 'absent')){
                            $cellbgcolour = 'style="background-color:#'.$data['ColourBackground'].'; color:#'.$data['ColourFont'].';"';
                            $cellBgColor = $data['ColourBackground'];
                        }
                        
                        $contextClassName = '';
                        switch($data['contextMenuClsName']){
                            case "0":
                                $contextClassName = 'context-menu-additional';
                                break;
                            case "1":
                                $contextClassName = 'context-menu-leave';
                                break;
                            case "2":
                                $contextClassName = 'context-menu';
                        }
                        
                        $markWiadClass = '';
                        switch($data['WTDBreachClassName']){
                            case "1":
                                $markWiadClass = 'cross-red';
                                break;
                            case "2":
                                $markWiadClass = 'cross-blue';
                                break;
                            default:
                                $markWiadClass = '';
                        }

                        if($data['IsDutyFromOtherTeam'] == 1) {
                            $otherTeamClass = 'otherTeamClass';
                        } else {
                            $otherTeamClass = '';
                        }

                        $attentionClsName = '';
                        if($data['isAttentionClsName'] == 1){
                            $attentionClsName = 'attentionClass';
                        }
                        ?>
                    <div id="rowUnqId_<?php echo $data['ID']; ?>" <?php echo $cellbgcolour?> class="NotFixedon weekly-body-cell allocatedDutyCell 
                        <?php echo $contextClassName.' '.$markWiadClass; ?>    
                        <?php echo $attentionClsName; ?>"   
                        data-mannualedp="<?php echo $data['ManualEDP'] == 0 ? 1 : 0 ; ?>" 
                        data-duty-name="<?php echo $data['DutyName']; ?>" 
                        data-unique-id="<?php echo $data['ID'] ?>" 
                        data-id="<?php echo $data['ID'] ?>" 
                        date-range-days="<?php echo $dateRangeDays?>" 
                        date-start-date="<?php echo $request->get('startDate')?>" 
                        data-mark-wiad-option="<?php echo ($markWiadClass != '' && $markWiadClass == 'cross-red') ? true : false; ?>" 
                        date-end-date="<?php echo date( 'Y-m-d', strtotime( $request->get('endDate') . ' -1 day '))?>"
                        <?php if($data['EditDuty'] == 1){
                                if($data['DutyName'] == 'U'){
                                    $modalTitle = 'createduty';
                                } else {
                                    $modalTitle = 'editduty';
                                }
                        ?>
                        ondblclick="editAllocateDuty('<?php echo $modalTitle;?>','edit','<?php echo $data['ID']?>'
                        <?php }?>)">
                        <div id="colUnqId_<?php echo $data['ID']; ?>" class="item-cell dragClass_<?php echo $date->format('Y-m-d'); ?>_<?php echo $data['SchedulingPersonID']; ?> alloc 
                            <?php echo $data['TriangleColour'] == null ? '' : 'cornerIcon cornerIcon-'.$data['TriangleColour']?> <?php if ((($data['IsHomeTeam']) && ((trim($data['DutyName'])!="U") && ($data['DutyName']!=""))) || ((empty($data['IsHomeTeam'])) && ((trim($data['DutyName'])!="U") && ($data['DutyName']!="")) && ($data['MarkWiad'] || $data['MarkActual']))): ?>dragAlloc<?php endif; ?> dutydroppable <?php if($data['EditDuty'] == 1): ?>dropeventscall-un<?php endif; ?> alloc-drag-drop max-height-duty-cell <?php echo $data['MarkOverTwelve'] == -1 ? 'purpleTriangleIcon' : ''; ?>"
                            <?php if ($data['EditDuty'] == 1 && $data['DutyName'] != 'U'): ?>
                            draggable="true"
                            <?php endif; ?>
                            <?php if($data['EditDuty'] == 1): ?>
                            ondrop="dropAlloc(event,this);"
                            <?php endif; ?> 
                            data-iday ="<?php echo $data['iDay'];?>"  
                            data-dateonly ="<?php echo $date->format('Y-m-d'); ?>" 
                            data-date="<?php echo $data['DutyDate']; ?>" 
                            data-source="allocated" data-id="<?php echo $data['ID']; ?>" 
                            data-duty-name="<?php echo $data['DutyName']; ?>" 
                            data-scheduling-person="<?php echo $data['SchedulingPersonID']; ?>" 
                            data-is-home-team="<?php echo $data['IsHomeTeam']; ?>" 
                            data-attention="<?php echo $data['isAttention'] ?? 0 ; ?>" 
                            data-scheduling-team-id="<?php echo $data['SchedulingTeamId']; ?>" 
                            data-wtd="<?php echo $data['MarkWTD'];?>" 
                            data-UnAllocated="<?php echo $data['UnAllocated']?>" 
                            data-ispublished = "<?php echo $data['isPublished']; ?>"
                            data-edp="<?php echo $edp; ?>"
                            data-wiad="<?php echo $data['MarkWiad']; ?>"
                            data-actual="<?php echo $data['MarkActual']; ?>"
                            data-bgcolor="<?php echo $data['ColourBackground']; ?>"
                            data-font-color="<?php echo $data['ColourFont']; ?>" 
                            data-mark-wiad-option="<?php echo ($markWiadClass != '' && $markWiadClass == 'cross-red') ? true : false; ?>"
                            data-charging-present = "<?php echo $data['TriangleColour'] == null ? 0 : 1; ?>"
                            data-edit-duty-flag='<?php echo $data['EditDuty']?>'
                            data-row-id='<?php echo $rowCnt;?>'
                            data-row-start='<?php echo $data['StartTime']??0;?>'
                            data-row-end='<?php echo $data['EndTime']??0;?>'
                            data-unique-id="<?php echo $data['ID'] ?>" 
                            date-range-days="<?php echo $dateRangeDays?>" 
                            date-start-date="<?php echo $request->get('startDate')?>" 
                            date-end-date="<?php echo date( 'Y-m-d', strtotime( $request->get('endDate') . ' -1 day '))?>"
                            data-bg-color="<?php echo $cellBgColor;?>"
                            data-mark-twelve="<?php echo $data['MarkOverTwelve'];?>"
                            data-mark-eleven="<?php echo $data['IsUnderElevenBreak']; ?>"
                            data-show-wiad-actual="<?php echo $data['ShowWIAD'];?>" 
                            data-purple-font="<?php echo $data['isRequest'];?>" data-duty-duration="<?php echo $data['Duration']?>"
                            >
                            <?php if($markWiadClass != '' && $markWiadClass == 'cross-red'){
                                    $dataWiadOpt = true;
                                  }
                                  else {
                                    $dataWiadOpt = false; 
                                  }
                            ?>
                            <span id="dutytipID_<?php echo $date->format('Y-m-d'); ?>_<?php echo $data['SchedulingPersonID']; ?>" class="dutyTip <?php echo $data['isRequest'] == 0  || $data['isRequest'] == '' ? ' ' : 'requestClass' ; ?> <?php echo $data['MarkOverTwelve'] == -1 ? 'triangleIconSpan' : ''; ?> <?php echo $otherTeamClass; ?>" <?php if($markWiadClass != ''){?>onmouseover="showwtdtip('showtooltip',this,<?php echo $data['ID']?>,<?php echo $dataWiadOpt?>);" onmouseleave="showwtdtip('hidetooltip');"<?php }?> data-mark-wiad-option="" data-id="<?php echo $data['ID']?>" <?php if($markWiadClass == ''){?>title="<?php echo $data['DutyName'].' ('.number_format((float) (($data['Duration']-$data['dutyBreakTime']) / 3600), 2, '.', '').')'.' - '.$data['schedulingTeamName']; ?>" onmouseover="removetip();"<?php }?>>
                            <?php if (!is_null($data['DutyName']) && $data['DutyName'] != "U"):?>
                            <?php 
                            if ($request->get('weeks') > 1){
                                echo '<b>'.substr($data['DutyName'], 0, 15).'</b>';
                            } else {
                                echo '<b>'.substr($data['DutyName'], 0, 17).'</b>';
                            }
                            ?>
                            
                            <?php else: ?>
                            <?php echo "U" ?>
                            <?php endif; ?> 
                            <br> 
                            <?php if (!is_null($data['DutyName']) && strtolower($data['DutyName']) != 'sick') :?>
                            <span style="width: 100px; display:inline-block;">
                            <?php 
                                if($data['StartTime'] != 0 || $data['EndTime'] != 0){
                                    echo gmdate("H:i", $data['StartTime']).' - '.gmdate("H:i", $data['EndTime']);
                                }
                                ?>
                            <?php endif ?>
                            </span>
                            </span>
                            <?php 
                                if($data['DutyComments'] == 0 && $data['PersonComments'] != 0){
                                    $className='golden displayInlineBlock';
                                } else if($data['DutyComments'] != 0 && $data['PersonComments'] == 0){
                                    $className='blue displayInlineBlock';
                                } else if($data['DutyComments'] != 0 && $data['PersonComments'] != 0){
                                    $className='pink displayInlineBlock';
                                } else {
                                    $className='hide';
                                }  
                                ?>
                            
                            <div class="DutyIconPositionLeft">
                                <div class="IconPositionClass blueEuro displayInlineBlock">
                                    <?php if($data['ShowEDPIcon'] == 1){?>
                                    <div class="blue-pound-icon"></div>
                                    <?php }?>  
                                </div>
                                <?php 
                                if($data['MarkedOvertime'] == 1){ 
                                    $mclass='activemarkovertime'; 
                                } else {
                                    $mclass='inactivemarkovertime'; 
                                }
                                ?>
                                <div class="IconPositionClass redEuro displayInlineBlock <?php echo $mclass;?>" id="redEuro_<?php echo $data['ID']; ?>">
                                    <div class="red-pound-icon"></div>
                                </div>
                                <div class="markWA icons displayInlineBlock IconPositionClass">
                                    <?php if($data['MarkWiad']): ?>W<?php endif;?>   
                                    <?php if($data['MarkActual']): ?>A<?php endif;?>
                                </div>
                                <div class="displayInlineBlock IconPositionClass elevenIcon">
                                    <!-- Slot for eleven icon !-->
                                    <span class="padd1FontW600"><?php if($data['IsUnderElevenBreakOverride'] == 1):?>11<?php endif;?></span>
                                </div>
                                <div class="displayInlineBlock IconPositionClass iconL">
                                    <!-- Slot for L icon !-->
                                    <?php if($data['LeaveApproved'] == '1' && $data['LeaveDeleted'] == '0' && $data['CountLeave'] == '1'){?>
                                    <!-- orange background !-->
                                    <span class="leaveOrange"><b>L</b></span>
                                    <?php }elseif($data['LeaveApproved'] == '1' && $data['LeaveDeleted'] == '0' && $data['CountLeave'] == '0'){?>
                                    <!-- hashed orange background !-->
                                    <span class="leaveHashedOrange"><b>L</b></span>
                                    <?php }else{
                                        if($data['LeaveisOK'] == '1' && $data['LeaveDeleted'] == '0'){
                                        
                                        if($data['LeaveShortNotice'] == '1'){?>
                                    <span class="leavePowderBlue"><b>L</b></span>
                                    <?php  } else if($data['Leaveoversummer'] == '1'){?>
                                    <span class="leavePurple"><b>L</b></span>
                                    <?php  }else { ?>
                                    <span class="leaveYellow"><b>L</b></span>
                                    <?php }
                                        }else if($data['LeaveisOK'] == '0' && $data['LeaveDeleted'] == '0'){?>
                                    <span class="leaveGray"><b>L</b></span>
                                    <?php }
                                        }?>
                                </div>
                                <div class="displayInlineBlock IconPositionClass iconLock">
                                    <?php
                                    if (($data['ShowLock'] == 1) && ($countLock == 0)) {
                                    $countLock++;
                                    ?>
                                        <div class="lock-icon-image"></div>
                                    <?php }?>
                                </div>
                                <?php 
                                $RequestClass='';  
                                if ($data['ReqCount'] >= 1)  {
                                   $RequestClass=' multirequestvaialble';
                                }
                                ?>
                                 <div class="displayInlineBlock IconPositionClass iconR <?php echo $RequestClass; ?>" id="requestIcon_<?php echo $data['ID']; ?>">
                                                    <!-- Slot for R icon !-->
                                                <?php 
                                                $RequestImag = null;
                                                if ($data['ReqCount']>1)  {
                                                    $RequestImag= '<span class="requestPurple"><b>R</b></span>';
                                                }else {
                                                if ($data['LockIconColour']=='O' && $data['ReqCount']==1) 
                                                    {
                                                        $RequestImag= '<span class="requestOrange"><b>R</b></span>';
                                                    } 
                                                if($data['LockIconColour']=='B' && $data['ReqCount']==1)
                                                    {
                                                        $RequestImag= '<span class="requestPowderBlue"><b>R</b></span>';
                                                    } 
                                                 if ($data['LockIconColour']=='Y' && $data['ReqCount']==1) 
                                                    {
                                                        $RequestImag='<span class="requestYellow"><b>R</b></span>';
                                                    } 
                                                if ($data['LockIconColour']=='G' && $data['ReqCount']==1) 
                                                    {
                                                        $RequestImag= '<span class="requestGray"><b>R</b></span>';
                                                    }
                                                }
                                            echo $RequestImag;                        
                                            ?>
                                 </div>
                                <div class="displayInlineBlock IconPositionClass twelveIcon">
                                    <!-- Slot for over twelve icon !-->
                                    <span class="padd1FontW600"><?php if ($data['MarkOverTwelve'] == 1) : ?>12<?php endif; ?></span>
                                </div>
                            </div>
                            <div class="DutyIconPositionRight">
                                <?php if (($allowsignin) && ((trim($data['DutyName'])!="U") && ($data['DutyName']!=""))):?>
                                <div dutyid="<?php echo  $data['MasterDutyId']; ?>" date="<?php echo $date->format('Y-m-m'); ?>" team="<?php echo $request->get('teamId'); ?>" SchedulingPersonID="<?php echo $data['SchedulingPersonID']; ?>" dataid="<?php echo $data['ID']?>" class="handcursor displayInlineBlock" <?php echo $js; ?> onmouseover="showsignedtip('showtooltip',this,<?php echo $data['ID']?>);" onmouseleave="showsignedtip('hidetooltip');">
                                    <div class="<?php echo $imageNameSignInClsName;?>"></div>   
                                </div>
                                <?php endif; ?>
                                <div id="circle" class="<?php echo $className?> circleIcon" <?php if($data['DutyComments'] != 0 || $data['PersonComments'] != 0){?> onmouseover="showdutycomments('showtooltip',this,<?php echo $data['ID']?>);" onmouseleave="showdutycomments('hidetooltip');" <?php }?> datateamid="<?php echo $data['SchedulingTeamId']?>" dataschpersonid="<?php echo $data['SchedulingPersonID']?>" dataid="<?php echo $data['ID']?>" dutydate="<?php echo $data['DutyDate']?>" ></div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="NotFixedon weekly-body-cell context-menu" data-order="U-<?php echo $userName; ?>" data-source="allocated" data-id="<?php echo $data['ID']; ?>">
                        <div class="allocated alloc"
                            data-date="<?php echo $date->format('Y-m-d'); ?>" 
                            data-occupied=0 
                            data-source="allocated" 
                            data-id="0" 
                            data-scheduling-person="<?php echo $data['SchedulingPersonID']; ?>" 
                            data-scheduling-team-id="<?php echo $data['SchedulingTeamId']; ?>" 
                            data-iday ="<?php echo $dowMap[date('D',strtotime($date->format('Y-m-d')))];?>"
                            data-wiad="<?php echo $data['MarkWiad']; ?>"
                            data-actual="<?php echo $data['MarkActual']; ?>" 
                            date-range-days="<?php echo $dateRangeDays?>"
                            >
                            <span class="dutyTip" title='U'> U </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php $rowCnt++;?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php } else {?>
    </div>
</div>
<div class="noRecordFound">No Record Found</div>
<?php }?>
</div>
</div>
<?php
    echo "<script>setTimeout(function() {
      setBlockScrollPositions()
    }, 100);</script>";
    ?>
<script type="text/javascript">
    $(document).ready(function() {
        $(".chosen-selectWeekly").chosen({
            no_results_text: "No Filter",
            width: "130px",
            disable_search: true
        });
        $(".showWeekChoosen").chosen({
            no_results_text: "week",
            width: "86px",
            disable_search: true
        });
        showHideCountGrid();
    });
</script>
<?php if($request->get('loadFileJs') == 'Yes' || $request->get('loadFileJs') == ''){?>
<?php echo '<script type="text/javascript" src="js/allocations/weekly/drag-drop.js"></script>';?>
<?php }?>