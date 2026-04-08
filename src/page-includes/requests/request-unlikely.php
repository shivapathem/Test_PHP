<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/genericfunctions.php';

$id = $_REQUEST['id'] ?? 0;
$pdo = OpenDBLinkA7();
try {
  $strQuery ="exec [dbo].[usp_fetch_request_by_id] ?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $id, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
}

if (isset($row) && !empty($row))
{
  $intUnlikely = $row["Unlikely"];  
  $dteRequestDate =  date('Y-m-d',strtotime($row['dDate']));
  $strRequesterEmail = $row["RequesterEmail"]; 
  $strRequesterFullName = $row["FullName"];
  $strRequesterEmailfrom = $row['EmailFrom'];
  $strRequestDescription = $row["Description"];

  if ($intUnlikely == 0 || $intUnlikely == null ) {
    $intSetUnlikely = 1;
    $strHistory = '<hr>Marked as Unlikely by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
  } else {
    $intSetUnlikely = 0;
    $strHistory = '<hr>Marked Unlikely was removed by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
  }
  try {
    $strQuery = "UPDATE Requests SET Unlikely = $intSetUnlikely,History = CONCAT(ISNULL(History,''), '$strHistory') WHERE (ID = ?)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
  
  // Send the email......
  $mail = new PHPMailer\PHPMailer\PHPMailer();
  $mail->isSMTP();
  $mail->SMTPDebug = 0;
  $mail->Host = getenv('SMTP_HOST');
  $mail->Port = 25;
  $mail->setFrom($strRequesterEmailfrom, 'Allocate');
  $mailAdd = getenv('EMAIL_BCC');
  $arrMailAdd = explode(",", $mailAdd);
  for($intCount = 0; $intCount < count($arrMailAdd); $intCount++)
  {
      $mail->AddBCC($arrMailAdd[$intCount ]);
  }
  $mail->Subject = getenv('EMAIL_SUFFIX').$strRequesterFullName.' - Update to Shift Request';
  $mail->addAddress($strRequesterEmail);
  $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
  $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

  $html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?? '').'</style>
        <body>
        <img alt="banner" src="cid:pobanner" /><br><br>';
            
  $html.= '<h2>This is an update to a Shift Request you have submitted</h2>';
  if ($intUnlikely == 0) {
    $html.= 'Your request on '.date("d M Y", strtotime($dteRequestDate)).' for '.$strRequestDescription.' has been been marked as Unlikely.';
  } else {
    $html.= 'Your request on '.date("d M Y", strtotime($dteRequestDate)).' for '.$strRequestDescription.' which was marked as Unlikely has had this removed.';
  }
  $html.='<br><br><br><br><p align="center"><img src="cid:bbclogo" /><br><font size="1">BBC  '.romanNumerals(date("Y")).'<font></p>';
  $html.='</body></html>';              
  $mail->msgHTML($html);
  if (!$mail->send()) {
    logger()->critical('Mailer Error', (array) $mail->ErrorInfo);
  } 
}
?>