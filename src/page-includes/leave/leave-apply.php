<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
date_default_timezone_set('Europe/London');

if (isset($_POST['user']) && !empty($_POST['user'])) {
  $strUser = $_POST['user'];
} else {
  $strUser = isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
}

$LeaveApplicantUserID = GetUserIdbyNetlogin($strUser);
if (isset($_POST['leavetype']) && !empty($_POST['leavetype'])) {
  $intLeaveTypeID = $_POST['leavetype'];
}

if (isset($_POST['date']) && !empty($_POST['date'])) {
  $strLeaveDate = $_POST['date'];
}

if (isset($_POST['UserID']) && !empty($_POST['UserID'])) {
  $UserID = $_POST['UserID'];
} else {
  $UserID = $LeaveApplicantUserID;
}

if (isset($_POST['FullName']) && !empty($_POST['FullName'])) {
  $strRequesterName = $_POST['FullName'];
}else{
  $strRequesterName = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
}

$arrAllLeaveGroups = GetLeaveGroupAndTeamsFromID();

$arrTypeGroup = GetLeaveTypeAndGroupFromID($intLeaveTypeID);
$intGroupID = $arrTypeGroup['GroupID'];

$schedulingPersonId = GetScheduledPersonIdbyUserId($UserID);
$arrLeave = GetLeaveForUser($strUser, $strLeaveDate, $strLeaveDate,$schedulingPersonId);
$intLeaveStarts = strtotime("+".$arrAllLeaveGroups[$intGroupID]['Types'][$intLeaveTypeID]['LeaveStarts']." days");
$intAvailable = $arrLeave['Available'][$intLeaveTypeID][$strLeaveDate];
$intOverSummer = 0;

if (isset($arrLeave['Applications'][$intLeaveTypeID][$strLeaveDate])) {
  $intRequestsIn = $arrLeave['Applications'][$intLeaveTypeID][$strLeaveDate];
} else {
  $intRequestsIn = 0;
}

if ($intAvailable > $intRequestsIn) {
  $intIsOK = 1;
} else {
  $intIsOK = 0;
}

// Is it Short Notice??????
if (strtotime($strLeaveDate) <= $intLeaveStarts) {
  $intShortNotice = 1;
} else {
  $intShortNotice = 0;
}  

if (isset($arrLeave['Summer'][$intLeaveTypeID]['Dates'][$strLeaveDate])) {
  $intDoSummer = 1;
  // The number of summer requests allowed....
  $intSummerRequestsAllowed = $arrLeave['SummerClicksAllowed'];
  $intSummerCount = $arrLeave['UserLeave']['SummerLeaveClicks'];

  if ($intSummerCount >= $intSummerRequestsAllowed) {
    $intOverSummer = 1;  
   }
  else {
    $intOverSummer = 0;
  }
}
else {
  $intDoSummer = 0;
  $intOverSummer = 0;
} 
$strHistory = '<hr>Leave Type \''.$arrTypeGroup['TypeDescription'].'\' in Leave Group \''.$arrTypeGroup['GroupDescription'].'\' applied on '.getDateTimeInEuropeTimezone(1, 0).' at '.getDateTimeInEuropeTimezone(0, 1). '
               <br>By '.$strRequesterName.'. <br>There were '.$intAvailable.' Spaces available and '.$intRequestsIn.' Requests already in. ';
              
if ($intDoSummer == 1) {
  $strHistory.= ' <br>This is a Summer Leave Request. You have '.$intSummerCount.' Requests Already and '.$intSummerRequestsAllowed.' are allowed. ';               
}

if ($intShortNotice == 1) {
  $strHistory.= ' <br>This is a Short Notice Request. '; 
}
            
$strHistory = escapesinglequotes($strHistory);

if ($intAvailable < $intRequestsIn) {
  $intLeaveOK = 1;
}
else {
  $intLeaveOK = 0;
}

$TeamId = checkScheduledOrNonScheduled($schedulingPersonId,$intGroupID);
if($TeamId == ''){
  $strstatus = 0;
  $finalArray = array("strstatus"=>$strstatus);
  echo json_encode($finalArray);
  die();
}else{
  $strstatus = 1;
}

if ($strstatus == 1) {
$pdo = OpenDBLinkA7(); 
    try {
      $status = 1;
      $strQuery = "exec [dbo].[usp_mod_LeaveApplications] ?,?,?,?,?,?,?,?,?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $strLeaveDate, PDO::PARAM_STR);
      $stmt->bindParam(2, $strUser, PDO::PARAM_STR);
      $stmt->bindParam(3, $intLeaveTypeID, PDO::PARAM_INT);
      $stmt->bindParam(4, $intShortNotice, PDO::PARAM_INT);
      $stmt->bindParam(5, $intOverSummer, PDO::PARAM_INT);
      $stmt->bindParam(6, $intIsOK, PDO::PARAM_INT);
      $stmt->bindParam(7, $strHistory, PDO::PARAM_STR);
      $stmt->bindParam(8, $UserID, PDO::PARAM_INT);
      $stmt->bindParam(9, $status, PDO::PARAM_INT);
      $stmt->bindParam(10, $schedulingPersonId, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
    }

if ($intShortNotice == 1) {

  $mail = new PHPMailer\PHPMailer\PHPMailer();
  $emailfrom = $arrAllLeaveGroups[$intGroupID]['email'];
  $emailcopiesto = $arrAllLeaveGroups[$intGroupID]['emailcopiesto'];

  $strEmailTo = GetEmailFromLogin($strUser);
  $strUserFullName = GetFullNameFromLogin($strUser);

  $mail->isSMTP();
  $mail->SMTPDebug = 0;

  $mail->Host = getenv('SMTP_HOST');
  $mail->Port = 25;

  $mail->setFrom($emailfrom, 'Allocate');
  $mail->addAddress($strEmailTo);

  $arrCCMail = explode(",", $emailcopiesto);
  for($intCount = 0; $intCount < count($arrCCMail); $intCount++) {
    $mail->AddCC($arrCCMail[$intCount]);
  }
  $mail->AddCC($emailfrom);

  $mail->Subject = getenv('EMAIL_SUFFIX').$strUserFullName.' -  Application for Short Notice Leave';
  
  $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
  $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

  $html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?: '').'</style>
         <body>
         <img alt="Banner" src="cid:pobanner" /> <br><br>';

  $html.= '<h2>This is a Short Notice Leave Request for '.$strUserFullName.'</h2>';

  $html.= '<table>';
  $html.= '<tr>';
  $html.= '<td width="300px" class="datecell">Date</td>';
  $html.= '<td width="300px" class="datecell">Leave Type</td>';
  $html.= '<td width="300px" class="datecell">Leave Group</td>';
  $html.= '</tr>';

  $html.= '<tr>';
  $html.= '<td>';
  $html.=  date("l, jS F Y", strtotime($strLeaveDate));
  $html.= '</td>';  
  $html.= '<td>';
  $html.=  $arrTypeGroup['TypeDescription'];
  $html.= '</td>';  
  $html.= '<td>';
  $html.=  $arrTypeGroup['GroupDescription'];
  $html.= '</td>'; 
  $html.= '</tr>';
  $html.= '<tr>';
  $html.= '<td colspan="2">';
  $html.= 'Applied for on '.date("jS M Y").' at '.date("H:i");
  $html.= '</td>'; 
  $html.= '</tr>';
  $html.= '<tr><td>&nbsp;</td></tr>';
  $html.= '<tr>';
  $html.= '<td colspan=4 style="text-align:center">';
  $html.= '<a href="' . getenv('BASE_URL') . '">Click here to access Allocate</a>';
  $html.= '</td>';
  $html.= '</tr>';  
  $html.= '</table>';

  $html.='<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
  $html.='</body></html>';

  $mail->msgHTML($html);
  $mail->send();
}

$finalArray = array("strstatus"=>$strstatus);
echo json_encode($finalArray);
die();
}