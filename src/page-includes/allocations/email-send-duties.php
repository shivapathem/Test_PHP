<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intTeamID = $_REQUEST['teamId'] ?? '';
$roleIDPermission = $_REQUEST['roleIDPermission'] ?? '';
$startendshiftflag = $_REQUEST['startendshiftflag'] ?? '';
$filterQuery1 = $_REQUEST['filterQuery1'] ?? '';
$filterQuery2 = $_REQUEST['filterQuery2'] ?? '';
$filterQuery3 = $_REQUEST['filterQuery3'] ?? '';
$filterOrderStr = $_REQUEST['filterOrderStr'] ?? '';
$intSortOrder = isset($_REQUEST['sortOrder']) ? $_REQUEST['sortOrder'] : 0;
$filteredScheduledPersonList = $_REQUEST['filteredScheduledPersonList'] ?? [];
$skillFilterDaily ='';
if (isset($_REQUEST['skillFilterDaily']) && $_REQUEST['skillFilterDaily'] != '') {
  $skillFilterDaily = $_REQUEST['skillFilterDaily'];
}

$dutyFilterDaily ='';
if (isset($_REQUEST['dutyFilterDaily']) && $_REQUEST['dutyFilterDaily'] != '') {
  $dutyFilterDaily = $_REQUEST['dutyFilterDaily'];
}

$jobFilterDaily ='';
if (isset($_REQUEST['jobFilterDaily']) && $_REQUEST['jobFilterDaily'] != '') {
  $jobFilterDaily = $_REQUEST['jobFilterDaily'];
}

$jobNameAll ='';
if (isset($_REQUEST['jobNameAll']) &&  $_REQUEST['jobNameAll'] != '') {
  $jobNameAll = $_REQUEST['jobNameAll'];
}

$jobLabelAll ='';
if (isset($_REQUEST['jobLabelAll']) &&  $_REQUEST['jobLabelAll'] != '') {
  $jobLabelAll = $_REQUEST['jobLabelAll'];
}

$selectedDay = $_REQUEST['selectedDay'] ?? 1;
$arrStaffOptions = GetCurrentFilterData ($strUser,$intTeamID);
$arrFilters = GetDutyFiltersByDepartment($intTeamID);

$strCurrentDate = date("Y-m-d", strtotime($_REQUEST['date']));
if ($selectedDay > 1) {
    $daysadded = $selectedDay-1;
    $day7date  = date("Y-m-d", strtotime("+$daysadded day", (strtotime($strCurrentDate))));
  } else {
    $day7date = date("Y-m-d",strtotime($strCurrentDate));
  }
$intWeek = bbcweeknumber($strCurrentDate);
$intDay = getdayofweek($strCurrentDate);
$arrAllocations = ReadAllocationsDay($intWeek, $intDay,$intTeamID, $intSortOrder, $roleIDPermission,$filterQuery1,$filterQuery2,$filterQuery3,$filterOrderStr,$strCurrentDate,$day7date,$skillFilterDaily,$dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll);
$arrAllocations = json_decode($arrAllocations,true);
for ($j=1;	$j<=$selectedDay ;$j++){
	foreach($arrAllocations['assigned'][$j] ?? [] as $intAllocationID => $arrAllocation) {
		if($startendshiftflag==1){
			if($arrAllocation['miscduty']==1){
				unset($arrAllocations['assigned'][$j][$intAllocationID]);
			}
	  	}

		if(!in_array($arrAllocation['ScheduledPersonID'], $filteredScheduledPersonList)) { //filter
			unset($arrAllocations['assigned'][$j][$intAllocationID]);
		}
  }
}
$emailmsg='<table class="tablesmalltidy" width="600px">';
$emailmsg.='<tr>';
$emailmsg.='<th colspan="2"><br>eMails sent to:<br><br></th>';
$emailmsg.='</tr>';

$strEmailFrom = GetEmailFromLogin($strUser);
$arrduties = array("U", "U-Sick", "Leave", "Sick","OFF Leave","Absent");
$arrduties = array_map( 'strtolower', $arrduties );
for ($j=1;	$j<=$selectedDay ;$j++){
	foreach($arrAllocations['assigned'][$j] ?? [] as $intAllocationID => $arrAllocation) {
	  if (!in_array(strtolower(trim($arrAllocation['duty'])), $arrduties)) {
			if (empty($arrAllocation['StaffNONBBCEmail'])) {
				$strTo = ($arrAllocation['StaffBBCEmail'] != '') ? $arrAllocation['StaffBBCEmail'] : '';
				$strCC = '';
				} else {
				$strTo = ($arrAllocation['StaffNONBBCEmail'] != '') ? $arrAllocation['StaffNONBBCEmail'] : '';
				$strCC = ($arrAllocation['StaffBBCEmail'] != '') ? $arrAllocation['StaffBBCEmail'] : '';
				}

	  if (!empty($strTo)){
		  $strBodyInfo = '';
		  $strBodyInfo.='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?: '').'</style>
				 <body>
				 <img alt="Banner" src="cid:pobanner" /><br><br>';
		  $strBodyInfo.= '<table style="width:900px">';
		  $strBodyInfo.= '<tr>';
		  $strBodyInfo.= '<td class="lightcell" style="font-size:18px">';
		  $strBodyInfo.= '<br>'.$arrAllocation['fullname'].'<br>Information for your shift on '.date("l, jS F Y", strtotime($strCurrentDate)).'<br><br>';
		  $strBodyInfo.= '</td>';
		  $strBodyInfo.= '</tr>';
		  $strBodyInfo.= '<tr>';
		  $strBodyInfo.= '<td style="font-size:18px">';
		  $strBodyInfo.= '<br><b>'.$arrAllocation['duty'].'</b><br>';
		  $strBodyInfo.= 'Times '.gmdate("H:i",$arrAllocation['starttime']).'-'.gmdate("H:i",$arrAllocation['endtime']).'<br><br>';
		  $strBodyInfo.= '</td>';
		  $strBodyInfo.= '</tr>';
		  $strBodyInfo.= '</table>';

		  $mail = new PHPMailer\PHPMailer\PHPMailer();
		  $mail->isSMTP();
		  $mail->SMTPDebug = 0;
		  $mail->Host = getenv('SMTP_HOST');
		  $mail->Port = 25;
		  $mail->addAddress($strTo);
		  $mail->setFrom($strEmailFrom, 'Allocate');
		  $mailAdd = getenv('EMAIL_BCC');
		  $arrMailAdd = explode(",", $mailAdd);
		  for($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
			$mail->AddBCC($arrMailAdd[$intCount ]);
		  }

		   $mail->Subject =getenv('EMAIL_SUFFIX').$arrAllocation['fullname'].' - Information for your shift on '.date("l, jS F Y", strtotime($strCurrentDate));
		  $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
		  $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
		  $strBodyInfo.='<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
		  $strBodyInfo.='</body></html>';

		  $mail->msgHTML($strBodyInfo);

		  if (!$mail->send()) {
			//echo "Mailer Error: " . $mail->ErrorInfo; exit;
		  } else {
			$emailmsg.='<tr>';
			$emailmsg.='<td colspan="2">';
			$emailmsg.=$arrAllocation['fullname'];
			$emailmsg.='</td>';
			$emailmsg.='</tr>';
		  }
		}
	}
	}
}
$emailmsg.= '<tr>';
$emailmsg.= '<td>';
$emailmsg.= '</td>';
$emailmsg.= '<td>';
$emailmsg.= '<input type="button" value="Close" onclick="cancel()">';
$emailmsg.='</td>';
$emailmsg.= '</tr>';
$emailmsg.= '</table>';
echo $emailmsg;
die();
