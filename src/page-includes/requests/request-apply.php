<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
$dteRequestDate = $_REQUEST['date'];
$intRequestType = $_REQUEST['type'];
$strUser = $_REQUEST['user'];
$intScheduledPersonID = getScheduledPersonIDByNetLoginID($strUser);
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
if (isset($_POST['usercomments'])) {
  $strCommentemails = $_POST['usercomments'];
  $strComments = ms_escape_string($strCommentemails);
} else {
  $strCommentemails = '';
  $strComments = '';
}

$pdo = OpenDBLinkA7();
  $dowMap = array ( 
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6                                                 
   );
  
$day = $dowMap[date("D", strtotime($dteRequestDate))]; 
    try {
        $sql = "SELECT RequestTypes.description, RequestTypes.day_0 AS allowed, RequestTypes.SendEmails AS SentEmail, RequestTypes.GroupID, 
              LeaveRequestGroups.Description AS GroupDescription
            FROM  RequestTypes (Nolock)
            INNER JOIN  LeaveRequestGroups (Nolock) ON RequestTypes.GroupID = LeaveRequestGroups.ID
            WHERE (RequestTypes.ID = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $intRequestType, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
              logger()->critical('DB Error', (array) $e);
    } 
if (!empty($row)) {
  $intRequestsAllowed = $row['allowed'];
  $strDescription = $row['description'];
  $intSendEmail = $row['SentEmail'];
  $intGroupID = $row['GroupID'];
  $strGroupDescription = $row['GroupDescription'];
  } else {
  $intRequestsAllowed = null;
  $strDescription = null;
  $intSendEmail = null;
  $intGroupID = null;
  $strGroupDescription = null;
  }
// Get any requests in.....
try {
  $sql = "SELECT  RequestType
  FROM  dbo.Requests (Nolock)
  WHERE (RequestType = ?)
  AND (dDate = CONVERT(DATETIME, ?, 102))
  AND (Deleted = 0)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1, $intRequestType, PDO::PARAM_STR);
  $stmt->bindParam(2, $dteRequestDate, PDO::PARAM_STR);
  $stmt->execute();
  $rsin = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $intRequestsIn = count($rsin);
} catch (PDOException $e) {
  logger()->critical('DB Error', (array) $e);
}
  $history = "Request for $strDescription applied on ". date("d/m/Y") ." at ".date("H:i").".<br>$intRequestsAllowed requests are allowed on this day.<br>There were $intRequestsIn already in.";
 
/**
 * RequestDatesClosed Fix start
 */
try {
  $intClosed=null;
  $strQuery = "SELECT count(ID) as total
  FROM RequestDatesClosed
  WHERE (GroupID = $intGroupID)
  AND (startdate <= CONVERT(DATETIME, '$dteRequestDate 00:00:00', 102))
  AND (enddate >= CONVERT(DATETIME, '$dteRequestDate 00:00:00', 102))";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $rsClosed = $stmt->fetch(PDO::FETCH_ASSOC);
  $intClosed=$rsClosed['total'];
} catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
 }
 if ($intClosed == 0) {
  if ($intRequestsIn >= $intRequestsAllowed) {
    $intIsOK = 0;
    $history = "Request for $strDescription applied for on ".date("d/m/Y")." at ".date("H:i").".<br>$intRequestsAllowed requests are allowed on this day.<br>There were $intRequestsIn already in.";
  } else {
    $intIsOK = 1;
  }
} else {
  $intIsOK = 0;
  $history = "Request for $strDescription applied for on ".date("d/m/Y")." at ".date("H:i").".<br>$intRequestsAllowed requests are allowed on this day.<br>There were $intRequestsIn already in."; $history.= "<br> However this day is closed for requests so it has been added to the queue.";
  }

 /**
 * RequestDatesClosed Fix End
 */
  $strQuery = "INSERT INTO
              dbo.Requests(
                dDate, 
                RequestType,
                isOK, 
                Login, 
                Created, 
                History,
                UserComments,
                ScheduledPersonID
              )
              VALUES (
                CONVERT(DATETIME, '$dteRequestDate 00:00:00', 102),
                $intRequestType,
                $intIsOK, 
                N'$strUser', 
                CONVERT(DATETIME, '".date("Y-m-d H:i:s")."', 102), 
                N'$history',
                '$strComments',
                $intScheduledPersonID)";
 try {               
      $stmt = $pdo->prepare($strQuery);
      $stmt->execute();    
 
      $query = "exec usp_UPDAllocationLockAndRequest @ScheduledPersonID = $intScheduledPersonID, @WeekNumber = NULL, @iDay = NULL, @DutyDate = '".$dteRequestDate."', @UserID = $UserID";
      $stmt = $pdo->prepare($query);
      $stmt->execute();
  } catch (PDOException $e) {
      logger()->critical('DB Error', (array) $e);
  }
if ($intSendEmail == 1) {
  $arrAllLeaveGroups = GetLeaveGroupAndTeamsFromID();
  $mail = new PHPMailer\PHPMailer\PHPMailer();
  $emailfrom = $arrAllLeaveGroups[$intGroupID]['email']; 
  $emailcopiesto = $arrAllLeaveGroups[$intGroupID]['emailcopiesto'];
  $strEmailTo = GetEmailFromLogin($strUser);
  $strUserFullName = GetFullNameFromLogin($strUser);
  if ($emailcopiesto != '') {
    $ccEmail = $emailcopiesto;
  } else {
    $ccEmail = '';
  }
 
  if(!empty($strEmailTo)) {
    $arrCCMail = explode(",", $ccEmail);
    for($intCount = 0; $intCount < count($arrCCMail); $intCount++) {
      $mail->AddCC($arrCCMail[$intCount]);
    }

    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->setFrom($emailfrom, 'Allocate');

    $mail->Host = getenv('SMTP_HOST');
    $mail->Port = 25;

    $mail->Subject = getenv('EMAIL_SUFFIX').$strUserFullName.' -  Application for a Request';
    
    $mail->addAddress($strEmailTo);
    $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
    $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

    $html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?? '').'</style>
          <body>
          <img alt="Banner" src="cid:pobanner" /><br><br>';

    $html.= '<h2>This is a Request application for '.$strUserFullName.'</h2>';

    $html.= '<table>';
    $html.= '<tr>';
    $html.= '<td style="width:280px" class="datecell">Date</td>';
    $html.= '<td style="width:280px" class="datecell">Request Type</td>';
    $html.= '<td style="width:280px" class="datecell">Group</td>';
    $html.= '</tr>';

    $html.= '<tr>';
    $html.= '<td><b>';
    $html.=  date("l, jS F Y", strtotime($dteRequestDate));
    $html.= '</b></td>';  
    $html.= '<td><b>';
    $html.=  $strDescription;
    $html.= '</b></td>';  
    $html.= '<td><b>';
    $html.=  $strGroupDescription;
    $html.= '</b></td>'; 
    $html.= '</tr>';

    $html.= '<tr>';
    $html.= '<td colspan="3">';
    $html.= '&nbsp;<br><br>';
    $html.= '</td>'; 
    $html.= '</tr>';
      
    if ($strCommentemails != '') {
      $html.= '<tr>';
      $html.= '<td colspan="3" class="datecell">Comments</td>';
      $html.= '</tr>';  
      $html.= '<tr>';
      $html.= '<td colspan="3"><b>';
      $html.=  $strCommentemails;
      $html.= '</b></td>';  
      $html.= '</tr>';
    }
    $html.= '<tr>';
    $html.= '<td colspan="3">';
    $html.= '<br><br>Applied for on '.date("jS M Y").' at '.date("H:i");
    $html.= '</td>'; 
    $html.= '</tr>';
    $html.= '</table>';

    $html.='<br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
    $html.='</body></html>';

    $mail->msgHTML($html);

    if (!$mail->send()) {
        logger()->critical('Mailer Error', (array) $mail->ErrorInfo);
    } 
  }
}
?>