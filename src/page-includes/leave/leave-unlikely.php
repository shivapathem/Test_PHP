<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService();
date_default_timezone_set('Europe/London');
$id = $_REQUEST['id'];
//get leave application details

$leaveapplicationdetails = getLeaveApplicationDetails($id); 

$unlikely = $leaveapplicationdetails["unlikely"];
$date = date('Y-m-d', strtotime($leaveapplicationdetails['dDate']));

if (is_null($leaveapplicationdetails["emailto"])) {
  $arrUser = bbc_GetFromLDAPFull($leaveapplicationdetails['Login']);
  $strEmailTo = $arrUser["email"];
}
else {
  $strEmailTo = $leaveapplicationdetails["emailto"];    
}

$strFullName = $leaveapplicationdetails["FullName"];
$emailfrom = $leaveapplicationdetails["emailfrom"];
$emailcopiesto = $leaveapplicationdetails["emailcopiesto"];
$strLeaveGroupDesc =  $leaveapplicationdetails["GroupDesc"];
$strLeaveTypeDesc =  $leaveapplicationdetails["TypeDesc"];
$pdlStrtTime = $leaveapplicationdetails['LeaveStartTime'];
$pdlEndTime = $leaveapplicationdetails['LeaveEndTime'];
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

if ($emailcopiesto != '') {
  $ccEmail = $emailfrom.','.$emailcopiesto;
}
else {
  $ccEmail = $emailfrom;
}

if ($unlikely == 0) {
  $setunlikely = 1;
  $history = '<hr>Marked as unlikely by '.$_SESSION['user']['FullName'].' on '.getDateTimeInEuropeTimezone(1,0).' at '.getDateTimeInEuropeTimezone(0,1);
}
else {
  $setunlikely = 0;
  $history = '<hr>Removed unlikely by '.$_SESSION['user']['FullName'].' on '.getDateTimeInEuropeTimezone(1,0).' at '.getDateTimeInEuropeTimezone(0,1);
}
//set unlikely col value

$leave =  ModUnlikelyLeaveApplication($id,$setunlikely,$history,$sessUserId);

$mail = new PHPMailer\PHPMailer\PHPMailer();

$mail->isSMTP();
$mail->SMTPDebug = 0;

$mail->Host = getenv('SMTP_HOST');

$mail->Port = 25;
$mail->setFrom($emailfrom, 'Allocate');
$mailAdd = getenv('EMAIL_BCC');
$arrCCMail = explode(",", $ccEmail);
$mail->addAddress($strEmailTo);
for($intCount = 0; $intCount < count($arrCCMail); $intCount++) {
    $mail->AddCC($arrCCMail[$intCount ]);
}

$arrMailAdd = explode(",", $mailAdd);
for($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
  $mail->AddBCC($arrMailAdd[$intCount ]);
}
$mail->Subject = getenv('EMAIL_SUFFIX').$strFullName.' - Update to Leave Requests';

$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

// Send the email......
$html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?? '').'</style>
       <body>
       <img src="cid:pobanner" /><br><br>';

$html.= '<h2>This is an update to Leave you have requested</h2>';

$html.= '<table width="1000px">';
$html.= '<tr>';
$html.= '<td width="400px" class="datecell">Person</td>';
$html.= '<td width="600px" class="lightcell">'.$strFullName.'</td>';
$html.= '</tr>';

$html.= '<tr>';
$html.= '<td width="400px" class="datecell">Leave Group</td>';
$html.= '<td width="600px" class="lightcell">'.$strLeaveGroupDesc.'</td>';
$html.= '</tr>';

$html.= '<tr>';
$html.= '<td width="400px" class="datecell">Leave Type</td>';
$html.= '<td width="600px" class="lightcell">'.$strLeaveTypeDesc.'</td>';
$html.= '</tr>';
$html.= '<tr>';
$html.= '<td width="400px" class="datecell">Date</td>';
$html.= '<td width="600px" class="lightcell">'.date("l, jS F Y", strtotime($date)).'</td>';
$html.= '</tr>';

if (($pdlStrtTime != '') && ($pdlEndTime != '')) {
$html.= '<tr>';
$html.= '<td width="400px" class="datecell">Time</td>';
$html.= '<td width="600px" class="lightcell">' . $service->convertSecondsIntoTime($pdlStrtTime,':','No').' - '.$service->convertSecondsIntoTime($pdlEndTime,':','No') . '</td>';
$html.= '</tr>';
$html.= '<tr>';
$html.= '<td width="400px" class="datecell">Duration</td>';
  if ($pdlStrtTime > $pdlEndTime) {
    $calPdlEndDuration = (86400 + $pdlEndTime);
    $html.= '<td width="600px" class="lightcell">'.$service->convertSecondsIntoTime($calPdlEndDuration-$pdlStrtTime).'</td>';
  }else{
    $html.= '<td width="600px" class="lightcell">' . $service->convertSecondsIntoTime($pdlEndTime-$pdlStrtTime) . '</td>';
  }
$html.= '</tr>';
}

$html.= '<tr>';
$html.= '<td colspan="2" class="lightcell"><br>';
if ($unlikely == 0) {
  $html.= 'Your request on '.date("d M Y", strtotime($date)).' has been looked at and it\'s been marked as unlikely.';
}
else {
  $html.= 'Your request on '.date("d M Y", strtotime($date)).' was marked as unlikely.<br>We\'ve looked at this again and the unlikely mark has been removed.';
}
$html.= '<br><br></td>';
$html.= '</tr>';
$html.= '</table>';

$html.='<br><br><br><br><p align="center"><img src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
$html.='</body></html>';

$mail->msgHTML($html);


if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;exit;
} else {
    echo json_encode($setunlikely);
}