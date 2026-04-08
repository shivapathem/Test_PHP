<?php
session_start();
date_default_timezone_set('UTC');

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/skillsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];


  
$strEmail = trim(GetEmailFromLogin($strUser));
if(empty($strEmail))
{
	echo "Email Address Not Found.";die;
}
$intDepartmentID = $_REQUEST['department'];
if ($intDepartmentID == 0) {
  $strDepartmentName = 'All Departments';
}
else {
  $strDepartmentName = GetDepartmentNameFromID($intDepartmentID);
}

if (isset($_REQUEST['id'])) {
  // Passed a week Number?
  $intWeekNumber = $_REQUEST['id'];
}
else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];    
  }
  else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));  
  }
}
$dteStartDate = datefromweek($intWeekNumber);
$arrAllocations = ReadFreelanceAllocations($intDepartmentID, $intWeekNumber, array());
$strContent = '<style>'.file_get_contents(getenv('EMAIL_CSS')).'</style>
       <body>';

$strContent.= '<table class="tablesmalltidy" width="100%">';
$strContent.= '<tr height="55px">';
$strContent.= '<td colspan="2" class="datecell" nowrap>';
$strContent.= '<font size="6">Allocate</font>';
$strContent.= '</td>';
$strContent.= '<td colspan="6" class="datecell">';
$strContent.= '<font size="4">Freelance Staffing for Week '.spinweek($intWeekNumber).' ('.$strDepartmentName.')<br>';
$strContent.= $arrAllocations['TotalCount'].' Freelance shifts booked for this week.';
$strContent.= '</font></td>';
$strContent.= '</tr>';




$strContent.= '<tr>';
$strContent.= '<th>';
$strContent.= '</th>';
for ($i = 0; $i <=6; $i++) {
  $strCurrDate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
  $strContent.= '<th><br><b>';
  $strContent.= $invdowMap[$i] .'<br>'.spindate($strCurrDate);
  $strContent.= '</b><br><br></th>';
}  
$strContent.= '</tr>';
// Totals....
$strContent.= '<tr height="50px">';
$strContent.= '<th><b>';
$strContent.= 'Total Hours Worked '.$arrAllocations['Total'];
$strContent.= '</b></th>';

for ($i = 0; $i <=6; $i++) {

  $strContent.= '<th><b>';
  if (isset($arrAllocations['Count'][$intWeekNumber][$i]))  {
    $strContent.= 'Shift Count '.$arrAllocations['Count'][$intWeekNumber][$i];
  }
  $strContent.= '</b></th>';
}  
$strContent.= '</tr>';



foreach ($arrAllocations['Duties'] as $strStaffNumber => $arrPerson) {
  $strContent.= '<tr>';
  $strContent.= '<th><b>';  
  $strContent.= $arrPerson['Name'];
  $strContent.= '</b><br>'.$strStaffNumber.'</th>';   
  for ($i = 0; $i <=6; $i++) {
    $strCurrDate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
    $strContent.= '<td>';
    if (isset($arrPerson['Duties'][$intWeekNumber][$i])) {
       $strContent.= '<b>'.$arrPerson['Duties'][$intWeekNumber][$i]["DepartmentName"].'</b><br>'; 
       if ($arrPerson['Duties'][$intWeekNumber][$i]['PersonComments'] != '') {
         $strContent.= '** <b><i>'.trim($arrPerson['Duties'][$intWeekNumber][$i]['PersonComments']).'</i></b> **<br>';   
       }
       else {
         $strContent.= '** **<br>';
       }       
       $strContent.= $arrPerson['Duties'][$intWeekNumber][$i]['DutyName'];
       if (isset($arrPerson['Duties'][$intWeekNumber][$i]["StartTime"])) {
         $strContent.= '<br>';
         $strContent.= $arrPerson['Duties'][$intWeekNumber][$i]["StartTime"].'-'.$arrPerson['Duties'][$intWeekNumber][$i]["EndTime"];       
       }
       else {
         if (!$arrPerson['Duties'][$intWeekNumber][$i]["Duration"] == 0) {
           $strContent.= '<br>'.$arrPerson['Duties'][$intWeekNumber][$i]["Duration"].' Hours';
         }       
       }
    }
    $strContent.= '</td>';
  }  
  $strContent.= '</tr>'; 
}
$strContent.= '</table>';
$strContent.='<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';


$strContent.= '</body></html>';   

$mail = new PHPMailer\PHPMailer\PHPMailer();

$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->setFrom($strEmail, 'Allocate');
$mail->Host = getenv('SMTP_HOST');
$mail->Port = 25;
$mail->Subject = getenv('EMAIL_SUFFIX').'Freelance Usage for Week '.spinweek($intWeekNumber).' - '.$strDepartmentName;
$mail->addAddress($strEmail);
$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');


$mail->msgHTML($strContent);

if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo; exit;
} else {
    echo "Message sent!";
}




?>