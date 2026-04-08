<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ .'/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../modals/classEditWeeklyAllocateDutyUI.php';
include_once __DIR__ .'/../../../../function-includes/masterduty_functions.php';
include_once __DIR__ .'/../../../../function-includes/AllocationsFunctionsEditing.php';
require_once __DIR__ . '/../service/TimeDimension.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';


$request = Request::createFromGlobals();

$service = new AllocationService();
$editWeeklyUI = new classEditWeeklyAllocateDutyUI();
$timeDimensionService = new TimeDimensionService();

if( $request->get('action') == 'selectweekpopup'){
    $rsDuty = GetDutyDetailsByID($request->get('dutyID'));
    $dutyDetail = json_decode($rsDuty, true);

    $createweekpopup = $editWeeklyUI->addToUnalloctedDutiesWeek($request,$dutyDetail['DutyName']);
    echo $createweekpopup;
}

if( $request->get('action') == 'selectweekpopupforcopy'){
    $rsDuty = GetDutyDetailsByID($request->get('dutyID'));
    $dutyDetail = json_decode($rsDuty, true);

    $createweekpopup = $editWeeklyUI->copyToAllDutiesWeek($request,$dutyDetail['DutyName']);
    echo $createweekpopup;
}

if( $request->get('action') == 'saveunallocatedduties'){
   $weekNumber = explode('/', $request->get('weeknumber'));
   if($weekNumber[1] != ''){
    $year = $weekNumber[1];
    $week = $weekNumber[0];
    $weekNumber =  $year.$week;
   } else {
    if($weekNumber[0] < 10){
      $year = date('Y');
      $week = '0'.$weekNumber[0];
      $weekNumber =  $year.$week;
    } else {
      $year = date('Y');
      $week = $weekNumber[0];
      $weekNumber =  $year.$week;
    }
   }

   $request->request->set('weeknumber',$weekNumber);

   $weekdata = $service->addUnallocteDutiesFromMasterDuties($request);
   if(($weekdata['spStatus']?? 1) == 0){
    $response_array = array('status'=> 'error','message'=> $weekdata['errorMessage']?? '');
   } else {
    $response_array = array('status'=> 'success','message'=> $weekdata['successMessage']?? '');
   }
   echo json_encode($response_array); exit();

}

if( $request->get('action') == 'saveCopyToAllDutiesForm'){
   $weekNumber = explode('/', $request->get('copytoaldutiesweeknumber'));
   if(array_key_exists(1, $weekNumber) && ($weekNumber[1] != '')){
    $year = $weekNumber[1];
    $week = $weekNumber[0];
    $weekNumber =  $year.$week;
   } else {
    if($weekNumber[0] < 10){
      $year = date('Y');
      $week = '0'.$weekNumber[0];
      $weekNumber =  $year.$week;
    } else {
      $year = date('Y');
      $week = $weekNumber[0];
      $weekNumber =  $year.$week;
    }
   }

   $request->request->set('copytoaldutiesweeknumber',$weekNumber);

   $weekdata = $service->copyAllDutiesToAllocation($request);
   copyDutyMailSend($weekdata, $request->get('copytoaldutiesweeknumber'));
   if(!empty($weekdata[0][0]['SPExecStatus'])){
    $response_array = array('status'=> 'error','message'=> $weekdata[0][0]['SPMessage']);
   } else {
    $response_array = array('status'=> 'success','message'=> $weekdata[0][0]['SPMessage']);
   }
   echo json_encode($response_array); exit();

}

if(isset($request->get('dataval')['action']) && $request->get('dataval')['action'] == 'showsicknesspopup'){
      //get allocations details
      $allocationDetails =  $service->getAllocationByID($request->get('dataval')['allocationsDutyId']);

      //get sickness record =
      $getUserSicknessRecord =  $service->getUserSicknessRecord($request->get('dataval')['schedulingPersonId'], $request->get('dataval')['dateonly']);

      $request->request->set('userSicknessRecord',$getUserSicknessRecord);
      //get consecutive record
      $getConsecutiveSickness = $service->getConsecutiveSicknessRecord( $request->get('dataval')['dateonly'],$request->get('dataval')['schedulingPersonId']);

      $request->request->set('getConsecutiveSickness',$getConsecutiveSickness);
      //check the condition if we have sick data on next/prev date
      $getNextPrevResult =  $service->getNextPrevSicknessRecord($request->get('dataval')['dateonly'],$request->get('dataval')['schedulingPersonId']);

      $getNextPrevResult['prevDayReasonId'] = isset($getNextPrevResult['prevDayReasonId']) ? $getNextPrevResult['prevDayReasonId'] : 0;
      $getNextPrevResult['nextPrevDayReasonId'] = isset($getNextPrevResult['nextPrevDayReasonId']) ? $getNextPrevResult['nextPrevDayReasonId'] : 0;
      $getNextPrevResult['nextDayReasonId'] = isset($getNextPrevResult['nextDayReasonId']) ? $getNextPrevResult['nextDayReasonId'] : 0;
      $getNextPrevResult['prevDayComment'] = isset($getNextPrevResult['prevDayComment']) ? $getNextPrevResult['prevDayComment'] : '';
      $getNextPrevResult['nextPrevDayComment'] = isset($getNextPrevResult['nextPrevDayComment']) ? $getNextPrevResult['nextPrevDayComment'] : '';
      $getNextPrevResult['nextDayComment'] = isset($getNextPrevResult['nextDayComment']) ? $getNextPrevResult['nextDayComment'] : '';
      $getNextPrevResult['prevDayMarkSickness'] = isset($getNextPrevResult['prevDayMarkSickness']) ? $getNextPrevResult['prevDayMarkSickness'] : 0;
      $getUserSicknessRecord[0]['MarkedSickness'] = isset($getUserSicknessRecord[0]['MarkedSickness']) ? $getUserSicknessRecord[0]['MarkedSickness'] : 0;
      $getNextPrevResult['beforePrevDay'] = isset($getNextPrevResult['beforePrevDay']) ? $getNextPrevResult['beforePrevDay'] : 0;

      $request->request->set('prevDayReasonId',$getNextPrevResult['prevDayReasonId']);
      $request->request->set('nextPrevDayReasonId',$getNextPrevResult['nextPrevDayReasonId']);
      $request->request->set('nextDayReasonId',$getNextPrevResult['nextDayReasonId']);
      $request->request->set('prevDayComment',$getNextPrevResult['prevDayComment']);
      $request->request->set('nextPrevDayComment',$getNextPrevResult['nextPrevDayComment']);
      $request->request->set('nextDayComment',$getNextPrevResult['nextDayComment']);
      $request->request->set('prevDayMarkedSickness',$getNextPrevResult['prevDayMarkSickness']);
      $request->request->set('currDayMarkedSickness',$getUserSicknessRecord[0]['MarkedSickness']);
      $request->request->set('beforePrevDayReasonId',$getNextPrevResult['beforePrevDay']);
      if(empty($getUserSicknessRecord ) || (isset($getUserSicknessRecord[0]['MarkedSickness']) && $getUserSicknessRecord[0]['MarkedSickness'] == 0)){
         $request->request->set('action','insert');
         if($getNextPrevResult['anotherSicknessRecord']!= ''){
            $request->request->set('userSicknessRecord',$getNextPrevResult['anotherSicknessRecord']);
         }
      }else{
         $request->request->set('action','update');
      }

   //get sickness reasons list
   $getSicknessReeasonList = json_decode($service->getSicknessList(),true);
   $request->request->set('sicknessReasonList',$getSicknessReeasonList);
   //get the pseron details
   $scheduledpersondetails = $service->getSchedulePersondetailsByID($request);

   $hours = '7.00';
   //team details
   $scheduledTeamdetails = json_decode($service->getScheduleTeamsdetailsByID($request),true);

   if($allocationDetails['DutyName']== 'U'){
      $hours = '0.00';
   }else {
       //get the hours
      switch($scheduledTeamdetails['defaultSicknessHoursAllocation']){
         case 1:
            $hours = '7.00';
            break;
         case 2:
            $hours = '7.00';
            break;
         case 3:
            //get the duty details
            $duration =$allocationDetails['Duration'] - $allocationDetails['dutyBreakTime'];
            $hours = $service->convertSecondsIntoTime($duration,'.','No');
            // $m = floor(($duration%3600)/60);
			   // $h = floor(($duration%86400)/3600);
			   // $hours = str_pad($h,2,'0',STR_PAD_LEFT).":".str_pad($m,2,'0',STR_PAD_RIGHT);
            break;
      }
   }
   $request->request->set('hours',$hours);
   $request->request->set('displayName',$scheduledpersondetails['DisplayName']);
   $request->request->set('staffnumber',$scheduledpersondetails['StaffNumber']);
   $createweekpopup = $editWeeklyUI->sicknessPopup($request);
   echo $createweekpopup;

}

if($request->get('js_actionvalue') && $request->get('js_actionvalue') == 'saveSicknessAction'){

   $date = $request->get('js_date');
   $scheduleddpersonid = $request->get('scheduledpersonId');
   //get allocation details
   $allocationDetails =  $service->getAllocationByID($request->get('allocationsDutyId'));

   //get consecutive data
   $getConsecutiveSickness = $service->getConsecutiveSicknessRecord($date,$scheduleddpersonid);
   $request->request->set('sickStartDate',$getConsecutiveSickness['sickstartdate']);
   $request->request->set('sickEndDate',$getConsecutiveSickness['sickenddate']);
   if($request->get('checkbox') == 1){

     if($getConsecutiveSickness['sickstartdate']<= $request->get('js_date') || $getConsecutiveSickness['sickenddate'] >= $request->get('js_date') ){
         $request->request->set('setcheckoption','on');
     }
     $request->request->set('setcheckoption','on');
   }else{

      $request->request->set('setcheckoption','off');
   }

   switch($request->get('synctype')){

      case 0:
         //no synch simple insert or update
         $request->request->set('sickStartDate',$getConsecutiveSickness['sickstartdate']);
         $request->request->set('sickEndDate',$getConsecutiveSickness['sickenddate']);
         break;
      case 1:
         //insert time all sync
         $request->request->set('sickStartDate',$getConsecutiveSickness['sickstartdate']);
         $request->request->set('sickEndDate',$getConsecutiveSickness['sickenddate']);
         break;
      case 2:
         if($request->get('js_date') < $request->get('sickEndDate')){
            $request->request->set('sickStartDate',$request->get('js_date'));
            $request->request->set('sickEndDate',$request->get('sickEndDate'));
         } else if($request->get('js_date') > $request->get('sickEndDate')){
            $request->request->set('sickEndDate',$request->get('js_date'));
            $request->request->set('sickStartDate',$getConsecutiveSickness['sickstartdate']);
         }
         break;
   }

   if (str_contains($request->get('hours'), '.')) {
      list($hours, $minutes) = explode('.', $request->get('hours'), 2);
   } else {
      $timeArr = explode(':', $request->get('hours'), 2);
	  $hours = $timeArr[0] ?? '00';
	  $minutes = array_key_exists(1, $timeArr) ? $timeArr[1] : '00';
   }
   $minutes = (int) $minutes;
   if($minutes == 25){
      $minutes = 15;
   } else if(($minutes == 50) || ($minutes == 5)){
      $minutes = 30;
   } else if($minutes == 75){
      $minutes = 45;
   } else {
      $minutes = 0;
   }
   $seconds = $minutes * 60 + $hours * 3600;
   $request->request->set('hours',$seconds);
   $sicknessHistory = '';
   $sicknessHrsNew = number_format((float) (($request->get('hours')) / 3600), 2, '.', '');
   $sicknessHrsOld = $request->get('sicknessHrsOld');
   if(is_numeric($sicknessHrsOld)){
      $sicknessHrsOld = number_format((float) (($request->get('sicknessHrsOld')) / 3600), 2, '.', '');
   }else{
      $sicknessHrsOld = 0;
   }
   $sicknessIdOld = $request->get('pcurrDayMarkedSickness');
    $reasonList = $request->get('reasonlist');
   $sicknessNameOld = $request->get('sicknessNameOld');
   if($request->get('sicknessIdOld') == '')
   {
	   $sicknessHistory = "Marked Sick $sicknessHrsNew hours with reason " . $request->get('sicknessNameNew');
   }elseif(($request->get('reasonlist') != $request->get('sicknessIdOld')) && ($request->get('hours') != $request->get('sicknessHrsOld')))
   {
	   $sicknessHistory = "Marked Sick from $sicknessHrsOld to $sicknessHrsNew hours and reason changed from $sicknessNameOld to " . $request->get('sicknessNameNew');
   }elseif($request->get('reasonlist') != $request->get('sicknessIdOld'))
   {
	   $sicknessHistory = "Marked Sick reason changed from $sicknessNameOld to " . $request->get('sicknessNameNew');
   }elseif($request->get('hours') != $request->get('sicknessHrsOld') && $sicknessHrsOld != 0)
   {
	   $sicknessHistory = "Marked Sick from $sicknessHrsOld to $sicknessHrsNew hours";
   }elseif($request->get('hours') != $request->get('sicknessHrsOld') && $sicknessHrsOld == 0)
   {
      $sicknessHistory = "Marked Sick $sicknessHrsNew hours";
   }elseif($request->get('sicknessIdOld'))
   {
	   $sicknessHistory = "Marked Sick $sicknessHrsNew hours with reason " . $sicknessNameOld;
   }

   $shouldSaveRecord = 0;
   if(($sicknessIdOld != $reasonList) || ($sicknessHrsOld != $sicknessHrsNew) || ($request->get('oDutyName') == 'U')){
     $shouldSaveRecord = 1;
   }

   $response_array =  json_decode($service->insertSicknessRecord($request),true);
   if($response_array['strstatus'] == 'success'){
      //insert into allocations and allocation edit table for record
      $isEdited = 0;
      $strNewDuty = $request->get('hours') == 0 ? 'U-Sick':'Sick';
      $markdutyColorId = 11;
      $isShiftleader = $_REQUEST['isShiftleader'] ?? 0;
	  $editype='MARKSICK';
      if(((strtolower($allocationDetails['DutyName']) == 'u-sick') || (strtolower($allocationDetails['DutyName']) == '-sick')) && ($request->get('hours') == 0)){
         if($request->get('reasonlist') != $request->get('sicknessIdOld')){
            $responseResult = json_decode(makeDutyAbsentSickLeave($request->get('js_allocationid'),$request->get('sicknessteamId'),$request->get('weeknumber'),$isShiftleader,$editype,$request->get('hours'),$request->get('wstartdate'),$request->get('wenddate'),$request->get('edate'),$request->get('mastMiscFilterId'),$allocationDetails['DutyName'], $sicknessHistory, $request->get('allocationsSpId'), $request->get('isChargingPresent'), $scheduleddpersonid, $request->get('dutyDate'), $request->get('allocationsDutyId')),true);
         }else{
            $responseResult = array("strstatus"=>1,"strsmsg"=>"Sickness record was not saved due to matching IDs.");
         }
      } else {
         if($shouldSaveRecord == 1){
            $responseResult = json_decode(makeDutyAbsentSickLeave($request->get('js_allocationid'),$request->get('sicknessteamId'),$request->get('weeknumber'),$isShiftleader,$editype,$request->get('hours'),$request->get('wstartdate'),$request->get('wenddate'),$request->get('edate'),$request->get('mastMiscFilterId'),$allocationDetails['DutyName'], $sicknessHistory, $request->get('allocationsSpId'), $request->get('isChargingPresent'), $scheduleddpersonid, $request->get('dutyDate'), $request->get('allocationsDutyId')),true);
         }else{
            $responseResult = array('strstatus' => 1, 'message' => 'Sickness record was not saved due to matching IDs.');
         }
      }
	  if($responseResult['strstatus'] == 0){
         $response_array = array('strstatus'=> 'success','strreturnstring'=> 'There some error while enter sickness record.');
      } else {
		  $response_array =$responseResult;
	  }
   }
   echo json_encode($response_array);exit();

}

if(!is_null($request->get('action')) && $request->get('action') == 'saveremoveweek'  ){
    $getDataByDateTimeDimension = $timeDimensionService->findByDateWithoutCarbon(date("Y-m-d"));
    $getImmediateNextWeekOfCurrentWeek = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($getDataByDateTimeDimension->ixYearWeek);

    $weekNumber = explode('/', $request->get('weeknumber'));
    $year = $weekNumber[1];
    $week = $weekNumber[0];
    $weekNumber =  $year.$week;
    $currWeek = $getDataByDateTimeDimension->ixYearWeek;
    $originalWeekYear = substr($getDataByDateTimeDimension->ixYearWeek,0,4);
    $originalWeekNum = substr($getDataByDateTimeDimension->ixYearWeek,4,2);

    $request->request->set('immedigateweek',$getImmediateNextWeekOfCurrentWeek->ixYearWeek);
    $request->request->set('removeweeknumber',$weekNumber);
    $request->request->set('weekNumber',$currWeek);
    $request->request->set('originalweek',$originalWeekNum.'/'.$originalWeekYear);
    $response_array =  $service->removeWeekAllocations($request);

    echo json_encode($response_array);exit();

}

function copyDutyMailSend($queryData) {
   $duties = $queryData[1] ?? [];
   $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
   $pdo = OpenDBLinkA7();
   $query = "SELECT UD_InternalEmail AS InternalEmail FROM UserDetails  WHERE UD_NetLogin = '" . $sessUserNetId . "'";
   $stmt = $pdo->prepare($query);
   $stmt->execute();
   $userMail = $stmt->fetch(PDO::FETCH_ASSOC);
   if(count($duties) == 0) {
      return;
   }
   $unaffectedDuty = [];
   foreach($duties as $duty) {
      $starttime = '--:--';
      $endtime = '--:--';
      if (!empty($duty['StartTime']) || !empty($duty['StartTime'])) {
         $intstartHour = intval($duty["StartTime"] / 3600);
         $intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
         $intstartMinute = intval(($duty["StartTime"] % 3600) / 60);
         $intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
         $starttime = $duty["StartTime"];
         $starttime = $intstartHour . ":" . $intstartMinute;
         if ($duty["EndTime"] > 86400) {
            $duty['EndTime'] = $duty['EndTime'] - 86400;
         }
         $intendHour = intval($duty['EndTime'] / 3600);
         $intendHour = strlen(trim($intendHour)) == 1 ? "0" . $intendHour : $intendHour;
         $intendMinute = intval(($duty['EndTime'] % 3600) / 60);
         $intendMinute = strlen(trim($intendMinute)) == 1 ? "0" . $intendMinute : $intendMinute;
         $endtime = $intendHour . ":" . $intendMinute;
      }
      $duty['formatted_start_time'] = $starttime;
      $duty['formatted_end_time'] = $endtime;
      $unaffectedDuty[] = $duty;
   }



   $mail = new PHPMailer\PHPMailer\PHPMailer();

   $mail->isSMTP();
   $mail->SMTPDebug = 0;
   $mail->Host = getenv('SMTP_HOST');
   $mail->Port = 25;

   $mail->setFrom('noreply@bbc.co.uk', 'Allocate');

   $mail->addAddress($userMail['InternalEmail'] ?? '');
   $mail->Subject = getenv('EMAIL_SUFFIX') . ' ' . $queryData[0][0]['SPMessage'];

   $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
   $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

   $html='<style>'.file_get_contents(getenv('EMAIL_CSS')).'</style>
      <body>
      <div style="background-color: #4B72BF"><img src="cid:pobanner" /></div><br>';
   $html.= '
   <div style="padding:12px">Hi,<br><br>';

      if(count($duties) > 0) {

         $html .= '<div><div style="display: grid; place-items: center;margin-top:8px;">
         <table class="tablesmalltidy text-style">
         <thead>
            <tr role="row">
                  <th colspan="6" class="headercell" style="text-align:center">Duties not impacted by copy duty</th>
            </tr>
            <tr role="row">
                  <th class="headercell" >Duty Name</th>
                  <th class="headercell" >Duty Date</th>
                  <th class="headercell" >Start Time</th>
                  <th class="headercell" >End Time</th>
                  <th class="headercell" >Scheduling Team</th>
            </tr>
         </thead>
         <tbody>';
         foreach($unaffectedDuty as $key => $duty) {

            $html.= '<tr role="row" class="odd">
                  <td>
                     ' . $duty['DutyName'] . '
                  </td>
                  <td>
                     ' . date('d/m/Y', strtotime($duty['DutyDate'])) . '
                  </td>
                  <td>
                     ' . $duty['formatted_start_time'] . '
                  </td>
                  <td>
                     ' . $duty['formatted_end_time'] . '
                  </td>
                  <td>
                     ' . $duty['schedulingTeamName'] . '
                  </td>
            </tr>';
         }
         $html.='</tbody></table></div></div>';
      }
   $html .= '<br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
   $html.='</div></body></html>';
   $mail->msgHTML($html);
   $mail->send();
}


