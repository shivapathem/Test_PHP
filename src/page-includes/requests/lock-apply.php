<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
$intWeekNumber = $_REQUEST['week'];
$intDay = $_REQUEST['day'];
$strUser = $_REQUEST['user'];
$intScheduledPersonID = getScheduledPersonIDByNetLoginID($strUser);
$strCurrentDate = datefromweek($intWeekNumber, $intDay);
$strStartOfWeek = datefromweek($intWeekNumber);
$strEndOfWeek = date("Y-m-d", strtotime("+6 Days", strtotime($strStartOfWeek)));
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$pdo = OpenDBLinkA7();
$arrUserTeamSettings = getAssignTeamEmailByNetLoginID($intScheduledPersonID);
// Have they got a lock?
$strQuery = "SELECT COUNT(ID) AS CountLocks FROM LockRequests (nolock)
             WHERE (WeekNumber = :intWeekNumber) AND (ScheduledPersonID = :intScheduledPersonID)";
try {
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':intWeekNumber', $intWeekNumber, PDO::PARAM_INT);
  $stmt->bindParam(':intScheduledPersonID', $intScheduledPersonID, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
 }
if (!empty($row) && isset($row['CountLocks']) && $row['CountLocks'] != 0) { 
  die;
}
// Mark all the requests as deleted
$strRequestHistory = "This unauthorised request was marked as deleted because a Lock was requested";
$strQuery = "UPDATE Requests
             SET Deleted = 1, History = CONCAT(ISNULL(History,''), :strRequestHistory)
             FROM Requests 
             INNER JOIN RequestTypes ON Requests.RequestType = RequestTypes.ID
             WHERE (Requests.ScheduledPersonID = :intScheduledPersonID) 
             AND  (Requests.dDate >= CONVERT(DATETIME, :strStartOfWeek, 102)) 
             AND (Requests.dDate <= CONVERT(DATETIME, :strEndOfWeek, 102)) 
             AND (RequestTypes.AffectLocks = 1)";
try {
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':strRequestHistory', $strRequestHistory, PDO::PARAM_STR);
  $stmt->bindParam(':intScheduledPersonID', $intScheduledPersonID, PDO::PARAM_INT);
  $stmt->bindParam(':strStartOfWeek', $strStartOfWeek, PDO::PARAM_STR);
  $stmt->bindParam(':strEndOfWeek', $strEndOfWeek, PDO::PARAM_STR);
  $stmt->execute();
} catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
 }
 
$strQuery = "exec [dbo].[usp_getScheduledPersonAllocationByweekiday] :intWeekNumber,:intDay,:intScheduledPersonID";
  try {
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intWeekNumber', $intWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(':intDay', $intDay, PDO::PARAM_INT);
    $stmt->bindParam(':intScheduledPersonID', $intScheduledPersonID, PDO::PARAM_INT);
    $stmt->execute();
    $rsAllocation = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
   $strAllocInfo = '';
   if (empty($rsAllocation)) {
     $strAllocInfo = 'Unknown Duty';
     $strHeaderTimes = '';     
     $intDutyStart = null;
     $intDutyEnd = null;
     $intDutyDuration = null;
     $strDutyName=null;
   }
   else {  
     foreach ($rsAllocation as $row) {
       $strDutyName = $row['DutyName'];
       $strDepartmentName = $row['TeamName'];
       $intDutyDuration = $row['Duration']; 
       $strAllocInfo.= '<b>'.$strDepartmentName.'</b><br>';
       $strAllocInfo.= $strDutyName.'<br>';
       if (!is_null($row['StartTime']) && ($row['StartTime'] != 0)) {
         $strHeaderTimes = ' ('.gmdate("H:i", $row['StartTime']).'-'.gmdate("H:i", $row['EndTime']).')';
         $strAllocInfo.= gmdate("H:i", $row['StartTime']).'-'.gmdate("H:i", $row['EndTime']).'<br>';
         $intDutyStart = $row['StartTime'];
         $intDutyEnd = $row['EndTime'];
       } else {
         if (!is_null($row['Duration'])) {
           $intDutyStart = null;
           $intDutyEnd = null;
           $strAllocInfo.= round(gmdate("H",$row["Duration"]), 2, PHP_ROUND_HALF_UP).' Hours<br>'; 
           $strHeaderTimes = '';
         }      
       }
     }
   }                  
   $strHistory = "Lock created on ".date("d/m/Y")." at ".date("H:i")." by ".$_SESSION['user']['FullName'].".<br><br>";
   $strHistory.= '<br>The Allocation for this day:<br>'.$strAllocInfo.'<br>';
   $strHistory = escapeSingleQuotes($strHistory);
   $strNow = date("Y-m-d H:i:s");
  
   $strQuery = "INSERT INTO 
   LockRequests(Login, WeekNumber, iDay, RequestedOn, AllocationsInformation, History,DutyName,StartTime,EndTime,Duration,ScheduledPersonID)
VALUES (:strUser,:intWeekNumber,:intDay,:strNow,:strAllocInfo,:strHistory,:strDutyName,:intDutyStart,:intDutyEnd,:intDutyDuration,:intScheduledPersonID)";
try {
$stmt = $pdo->prepare($strQuery);
$stmt->bindParam(':strUser', $strUser, PDO::PARAM_STR);
$stmt->bindParam(':intWeekNumber', $intWeekNumber, PDO::PARAM_INT);
$stmt->bindParam(':intDay', $intDay, PDO::PARAM_STR);
$stmt->bindParam(':strNow', $strNow, PDO::PARAM_STR);
$stmt->bindParam(':strAllocInfo', $strAllocInfo, PDO::PARAM_STR);
$stmt->bindParam(':strHistory', $strHistory, PDO::PARAM_STR);
$stmt->bindParam(':strDutyName', $strDutyName, PDO::PARAM_STR);
$stmt->bindParam(':intDutyStart', $intDutyStart, PDO::PARAM_INT);
$stmt->bindParam(':intDutyEnd', $intDutyEnd, PDO::PARAM_INT);
$stmt->bindParam(':intDutyDuration', $intDutyDuration, PDO::PARAM_INT);
$stmt->bindParam(':intScheduledPersonID', $intScheduledPersonID, PDO::PARAM_INT);
$stmt->execute();
$query = "exec usp_UPDAllocationLockAndRequest @ScheduledPersonID = $intScheduledPersonID, @WeekNumber = $intWeekNumber, @iDay = $intDay, @DutyDate = NULL, @UserID = $UserID";
$stmt = $pdo->prepare($query);
$stmt->execute();
} catch (PDOException $e) {
logger()->critical('DB Error', (array) $e);
} 
// #### Now the email
 // Get the email addresses....
 foreach ($arrUserTeamSettings as $arrTeam) {
  if ($arrTeam['scheduledType'] == 1 && !is_null($arrTeam['Email'])) {
    $arrEmailFrom[$arrTeam['schedulingTeamId']] = $arrTeam['Email'];
  }
}

if (isset($arrEmailFrom)) {
$mail = new PHPMailer\PHPMailer\PHPMailer();
$strUserFullName = GetFullNameFromLogin($strUser);
$strEmailTo = GetEmailFromLogin($strUser);
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->setFrom($strEmailTo, $strUserFullName);

$mail->Host = getenv('SMTP_HOST');
$mail->Port = 25;

$mail->Subject = getenv('EMAIL_SUFFIX').$strUserFullName.' -  Lock';
foreach ($arrEmailFrom as $emailfrom) {
   $arrDepFrom = explode(";", $emailfrom);
   foreach ($arrDepFrom as $strPartDepEmail) {
     $mail->addAddress($strPartDepEmail);
  }
}

$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

$html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?? '').'</style>
       <body>
       <img alt="Banner" src="cid:pobanner" /><br><br>';

$html.= '<h2>This is a Lock for '.$strUserFullName.'</h2>';
$html.= '<h3>On '.date("l, jS F Y", strtotime($strCurrentDate)).$strHeaderTimes.'</h3>';

$html.=$strHistory;

$html.='<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
$html.='</body></html>';

$mail->msgHTML($html);

if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo; exit;
} else {
  echo "Message sent!";
} 
} 
