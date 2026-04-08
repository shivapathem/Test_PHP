<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes//init.php';
include_once '../../function-includes/genericfunctions.php';

$id = $_REQUEST['id'] ?? 0;
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$pdo = OpenDBLinkA7();
try {
  $strQuery = $sql = "exec [dbo].[usp_fetch_request_by_id] ?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $id, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
}

if (!empty($row)) {
$intNotPossible = $row['NotPossible'];  
$intScheduledPersonID = $row["ScheduledPersonID"]; 
$dteRequestDate =  date('Y-m-d',strtotime($row['dDate']));
$strRequesterEmail = $row["RequesterEmail"]; 
$strRequesterFullName = $row['FullName'];
$strRequesterEmailfrom = $row['EmailFrom'];
$strRequesterEmailCC = $row['EmailCC'];
$strRequestDescription = $row['Description'];

if ($intNotPossible == 0) {
  $intSetNotPossible = 1;
  $strHistory = '<hr>Marked as Not Possible by '.$_SESSION['user']['FullName'].'<br> on '.date("d/m/Y").' at '.date("H:i");
} else {
  $intSetNotPossible = 0;
  $strHistory = '<hr>Marked Not Possible was removed by '.$_SESSION['user']['FullName'].'<br> on '.date("d/m/Y").' at '.date("H:i");
}
try {
  $strQuery = "UPDATE Requests SET NotPossible = $intSetNotPossible, Deleted = 1,History = CONCAT(ISNULL(History,''), '$strHistory') WHERE (ID = ?)";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $id, PDO::PARAM_INT);
  $stmt->execute();

  $query = "exec usp_UPDAllocationLockAndRequest @ScheduledPersonID = $intScheduledPersonID, @WeekNumber = NULL, @iDay = NULL, @DutyDate = '".$dteRequestDate."', @UserID = $UserID";
  $stmt = $pdo->prepare($query);
  $stmt->execute();
}catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
}

// Send the email......
$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = getenv('SMTP_HOST');
$mail->Port = 25;
$mail->setFrom($strRequesterEmailfrom, 'Allocate');
$arrMailAdd = explode(";", $strRequesterEmailCC);
for ($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
  $mail->AddCC($arrMailAdd[$intCount]);
}

$mail->Subject = getenv('EMAIL_SUFFIX').$strRequesterFullName.' - Update to Shift Request';
$mail->addAddress($strRequesterEmail);
$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

$html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?? '').'</style>
       <body>
       <img alt="banner" src="cid:pobanner" /><br><br>';
          
$html.= '<h2>This is an update to a Shift Request you have submitted</h2>';
if ($intNotPossible == 0) {
  $html.= 'Your request on '.date("d M Y", strtotime($dteRequestDate)).' for '.$strRequestDescription.' has been been marked as Not Possible.';
}
else {
  $html.= 'Your request on '.date("d M Y", strtotime($dteRequestDate)).' for '.$strRequestDescription.' which was marked as Not Possible has had this removed.';
}
$html.='<br><br><br><br><p align="center"><img src="cid:bbclogo" /><br><font size="1">BBC  '.romanNumerals(date("Y")).'<font></p>';
$html.='</body></html>';
$mail->msgHTML($html);

if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;exit;
}
} 
?>