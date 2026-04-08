<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService();

$strLogin = $_REQUEST['login'];
$utcDateTime = new DateTime('now', new DateTimeZone('UTC'));
$londonDateTime = clone $utcDateTime;
$londonDateTime->setTimezone(new DateTimeZone('Europe/London'));
$strDateNow = $londonDateTime->format('Y-m-d H:i:s');
$bodyInfo = '';
$leave = GetApprovedLeavsEmailToSendUser($strLogin);

$strUser = GetUserLogon();
$LeaveAdminGroupsData = GetLeaveAdminGroups($strUser);
$arrLeaveAdmingrps = [];
if (!empty($LeaveAdminGroupsData)) {
  foreach ($LeaveAdminGroupsData as $value) {
    array_push($arrLeaveAdmingrps, $value['LeaveGroupID']);
  }
}
$leaveapplicationsIDS = array();
$lastRowByLeaveCategory = array();
$uniqueLeaveCategories = array();
$accumulatedLeaveCategoryAmount = array();
// Now compose the email.....
foreach ($leave as $row) {
  $leaveapplicationsIDS[] = $row['ID'];
  $emailfrom = $row['EmailFrom'];
  $emailcopiesto = $row['emailcopiesto'];
  $groupid = $row['GroupID'];
  if (is_null($row["UserEmail"])) {
    $arrUser = bbc_GetFromLDAPFull($row['Login']);
    $email = $arrUser["email"];
  } else {
    $email = $row["UserEmail"];
  }
  $strFullName = $row['FullName'];
  $pdlStrtTime = $row['LeaveStartTime'];
  $pdlEndTime = $row['LeaveEndTime'];

  if (in_array($groupid, $arrLeaveAdmingrps)) {
    if (!isset($uniqueLeaveCategories[$row['ID']])) {
      $uniqueLeaveCategories[$row['ID']] = array();
    }
    $uniqueLeaveCategories[$row['ID']][] = $row['LeaveCategory'];
    $uniqueLeaveCategories[$row['ID']] = array_unique($uniqueLeaveCategories[$row['ID']]);
    $leaveCategoryText = implode(', ', $uniqueLeaveCategories[$row['ID']]);

    if (!isset($accumulatedLeaveCategoryAmount[$row['ID']])) {
      $accumulatedLeaveCategoryAmount[$row['ID']] = 0;
    }
    $accumulatedLeaveCategoryAmount[$row['ID']] += $row['LeaveCategoryAmount'];

    if ($accumulatedLeaveCategoryAmount[$row['ID']] == 0) {
      $leaveCategoryText = $leaveCategoryText . "(OFF Leave)";
    }

    $lastRowByLeaveCategory[$row['ID']] = array(
      'Date' => date('l, jS F Y', strtotime($row['dDate'])),
      'GroupDescription' => $row['GroupDescription'],
      'TypeDescription' => $row['TypeDescription'],
      'LeaveCategory' => $leaveCategoryText,
      'Comments' => nl2br($row['Comments'] ?? ''),
      'OfficeComments' => nl2br($row['OfficeComments'] ?? ''),
      'Time' => ($pdlStrtTime != '' && $pdlEndTime != '') ? $service->convertSecondsIntoTime($pdlStrtTime, ':', 'No') . ' - ' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') : '',
      'Duration' => $accumulatedLeaveCategoryAmount[$row['ID']],
    );
  }
}

foreach ($lastRowByLeaveCategory as $row) {
  $bodyInfo .= '<tr>';
  $bodyInfo .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['Date'] . '</td>';
  $bodyInfo .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['GroupDescription'] . '</td>';
  $bodyInfo .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['TypeDescription'] . '</td>';
  $bodyInfo .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['LeaveCategory'] . '</td>';
  $bodyInfo .= '<td class="lightcell" style="padding-right:unset !important;">' . ($row['Duration'] == 0 ? "0" : $row['Duration']) . '</td>';
  $bodyInfo .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['Comments'] . '</td>';
  $bodyInfo .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['OfficeComments'] . '</td>';
  $bodyInfo .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['Time'] . '</td>';
  $bodyInfo .= '</tr>';
}
$mail = new PHPMailer\PHPMailer\PHPMailer();

if (isset($emailcopiesto) && $emailcopiesto !== '') {
  $ccEmail = $emailfrom . ',' . $emailcopiesto;
} else {
  $ccEmail = $emailfrom;
}
$arrCCMail = $ccEmail !== null && $ccEmail !== '' ? explode(',', $ccEmail) : [];
$mail->addAddress($email);
for ($intCount = 0; $intCount < count($arrCCMail); $intCount++) {
  $mail->AddCC($arrCCMail[$intCount]);
}
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->setFrom($emailfrom, 'Allocate');
$mail->Host = getenv('SMTP_HOST');
$mail->Port = 25;
$mailAdd = getenv('EMAIL_BCC');
$arrMailAdd = explode(",", $mailAdd);
for ($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
  $mail->AddBCC($arrMailAdd[$intCount]);
}
$mail->Subject = getenv('EMAIL_SUFFIX') . $strFullName . ' -  Approval of Leave Request';
$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

$html = '<style>' . (@file_get_contents(getenv('EMAIL_CSS')) ?? '') . '</style>
       <body>
       <img alt="Banner" src="cid:pobanner" /><br><br>';

$html .= '<h2>This is an update to Leave you have requested<br>The dates below have been approved:</h2>';

$html .= '<table style="width:100%;!important">';
$html .= '<tr>';
$html .= '<td width="200px" class="datecell">Date</td>';
$html .= '<td width="200px" class="datecell">Leave Group</td>';
$html .= '<td width="200px" class="datecell">Leave Type</td>';
$html .= '<td width="200px" class="datecell">Leave Category</td>';
$html .= '<td width="200px" class="datecell">Duration</td>';
$html .= '<td width="200px" class="datecell">Comments</td>';
$html .= '<td width="200px" class="datecell">Office Comments</td>';
$html .= '<td width="200px" class="datecell">Time</td>';
$html .= '</tr>';
$html .= $bodyInfo;
$html .= '</table>';

$html .= '<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
$html .= '</body></html>';

$mail->msgHTML($html);
if (!$mail->send()){
  logger()->ERROR('Error to sent an EMail to '.$email);
}else {
  logger()->INFO('EMail sent to '.$email);
}
// Now update the records

$history = '<hr>eMail for Approved Leave sent to \'' . $email . '\' by ' . $_SESSION['user']['FullName'] . ' on ' . getDateTimeInEuropeTimezone(1, 0) . ' at ' . getDateTimeInEuropeTimezone(0, 1);
$history = escapeSingleQuotes($history);
$leaveemailsend = ModEmailsToSend($strLogin);
$leaveapplicationsIDS = array_unique($leaveapplicationsIDS);
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
foreach ($leaveapplicationsIDS as $row) {
  $historytype = 15;
  InsertHistory($historytype, $sessUserId, $history, $strDateNow, $row);
}
if (isset($leaveemailsend)) {
  logger()->INFO($leaveemailsend['strStatus']);
}

?>