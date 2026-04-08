<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intID = $_REQUEST['id'] ?? 0;
$strRequestType  = 0;
$strRequestGroup = 0;
if ($intID == 0) {
  $strDate = $_REQUEST['date'] ?? date('Y-m-d');
  $intRequestType = $_REQUEST['type'] ?? 0;
  $strRequesterLogin = $_REQUEST['user'] ?? '';
}
if (isset($_REQUEST['pageid'])) {
  $pageid = $_REQUEST['pageid'];
} else {
  $pageid = 0;
}

$intSendEmail = 0;
$pdo = OpenDBLinkA7();

if (isset($_REQUEST['submit'])) {
  if ($intID == 0) {
    // Add a new one....
    $strComments = $_POST['usercomments'];
  }
  else {
    if (isset($_POST['officecomments'])) {
      $strComments = $_POST['officecomments'];
      if ($strComments == '') {
        $strComments = Null;
        $strHistory = '<hr>The Office Comments were removed by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
        $strQuery = "UPDATE dbo.Requests
                  SET Comments = null,
                  History = CONCAT(ISNULL(History,''), '$strHistory')
                  WHERE (ID = :ID)";      
      } else {
        $strComments = ms_escape_string($strComments);
        $strHistory = '<hr>Office Comments were added by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
        $strHistory.= '<br>The Comments were <font color="#990000">'.$strComments.'</font>';
        $strQuery = "UPDATE dbo.Requests
                  SET Comments = N'$strComments',
                  History = CONCAT(ISNULL(History,''), '$strHistory')
                  WHERE (ID = :ID)";
        $intSendEmail = 1;          
      }
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(':ID', $intID, PDO::PARAM_INT);
      $stmt->execute();
    }

    if (isset($_POST['usercomments'])) {
      $strComments = $_POST['usercomments'];
      if ($strComments == '') {
        $strHistory = '<hr>User Comments were removed by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
        $strComments = Null;
        $strQuery = "UPDATE dbo.Requests
                     SET UserComments = null,
                     History = CONCAT(ISNULL(History,''), '$strHistory')
                     WHERE (ID = :ID)";
      }
      else {
        $strComments = ms_escape_string($strComments);
        $strHistory = '<hr>User Comments were added by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").'   at '.date("H:i");
        $strHistory.= '<br>The Comments were <font color="#990000">'.$strComments.'</font>';
        $strQuery = "UPDATE dbo.Requests
                     SET UserComments = N'$strComments',
                     History = CONCAT(ISNULL(History,''), '$strHistory')
                     WHERE (ID = :ID)";
        $intSendEmail = 1;          
    }
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(':ID', $intID, PDO::PARAM_INT);
      $stmt->execute(); 
    }
  }
  if ($intSendEmail == 1) {
    $strQuery = "exec [dbo].[usp_fetch_requestCommentinfo_byRequestID] :ID";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':ID', $intID, PDO::PARAM_INT);
    $stmt->execute(); 
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $emailfrom = $row['email'];
    $emailcopiesto = $row['emailcopiesto'];
    $strUserFullName = $row['FullName'];
    $strComments = $row['UserComments'] ?? '';
    $strOfficeComments = $row['Comments'] ?? ''; 
    $arrCCMail = explode(",", $emailcopiesto);  
    $dteRequestDate = date("l, jS F Y",strtotime($row['dDate']));
    $strGroupDescription = $row['GroupDescription'] ?? '';
    $strDescription = $row['description'] ?? '';
    $intAlwaysEmail = $row['SentEmail'];
    
    $strRequesterEmail = GetEmailFromLogin ($row['Login']);     
    if ($intAlwaysEmail == 1) { 
      $mail = new PHPMailer\PHPMailer\PHPMailer();
      for($intCount = 0; $intCount < count($arrCCMail); $intCount++) {
        $mail->AddCC($arrCCMail[$intCount]);
      }
      $mail->AddCC($strRequesterEmail);
      $mail->isSMTP();
      $mail->SMTPDebug = 0;
      $mail->setFrom($emailfrom, 'Allocate');
      $mail->Host = getenv('SMTP_HOST');
      $mail->Port = 25;

      $mail->Subject = getenv('EMAIL_SUFFIX').$strUserFullName.' - Request - Comments added';
  
      $mail->addAddress($emailfrom);
      $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
      $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

       $html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?? '').'</style>
             <body>
             <img alt="Banner" src="cid:pobanner" /><br><br>';

      $html.= '<h2>This is an addition of comments to a Request application for '.$strUserFullName.'</h2>';

      $html.= '<table>';
      $html.= '<tr>';
      $html.= '<td style="width:280px" class="datecell">Date</td>';
      $html.= '<td style="width:280px" class="datecell">Request Type</td>';
      $html.= '<td style="width:280px" class="datecell">Group</td>';
      $html.= '</tr>';

      $html.= '<tr>';
      $html.= '<td>';
      $html.=  date("l, jS F Y", strtotime($dteRequestDate));
      $html.= '</td>';  
      $html.= '<td>';
      $html.=  $strDescription;
      $html.= '</td>';  
      $html.= '<td>';
      $html.=  $strGroupDescription;
      $html.= '</td>'; 
      $html.= '</tr>';
      $html.= '<tr>';
      $html.= '<td colspan="3">';
      $html.= '<br>User Comments are: ';
      $html.= ''.nl2br($strComments).'';      
      $html.= '</td>'; 
      $html.= '</tr>';
      $html.= '<tr>';
      $html.= '<td colspan="3">';
      $html.= '<br>Office Comments are: ';
      $html.= ''.nl2br($strOfficeComments).'';      
      $html.= '</td>'; 
      $html.= '</tr>';      
      $html.= '<tr>';
      $html.= '<td colspan="3">';
      $html.= '<br>Comments added on '.date("jS M Y").' at '.date("H:i");
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
}
else {
  if ($intID == 0) {
    $strUserName = GetFullNameFromLogin($strRequesterLogin);
    $intRequestType = $_REQUEST['type'] ?? 0;
    $strQuery = "SELECT RequestTypes.description AS TypeDescription, LeaveRequestGroups.Description AS GroupDescription
    FROM LeaveRequestGroups (nolock)
    INNER JOIN RequestTypes (nolock) ON LeaveRequestGroups.ID = RequestTypes.GroupID
    WHERE (RequestTypes.ID = :intRequestType)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intRequestType', $intRequestType, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($row) && isset($row['TypeDescription'])) {
      $strRequestType = $row['TypeDescription'] ;
      $strRequestGroup = $row['GroupDescription'];
    }
    $strOfficeComments = '';
    $strUserComments = '';  
  }
  else {
    $strQuery = "exec [dbo].[usp_fetch_RequestComment] :intID";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intID', $intID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($row)) { //To bypass empty dataset condition
        $intRequestType = $row['RequestType'];
        $strUserName = $row['fullname'];
        $strDate = date('Y-m-d',strtotime($row['dDate']));
        $strRequesterLogin = $row['Login'];
        $strRequestType = $row['TypeDescription'] ?? '';
        $strRequestGroup = $row['GroupDescription'] ?? '';
        $strOfficeComments = $row['Comments'] ?? '';
        $strUserComments = $row['UserComments'] ?? '';
    } else { //To bypass empty dataset condition
        $intRequestType = "";
        $strUserName = "";
        $strDate = "";
        $strRequesterLogin = "";
        $strRequestType = "";
        $strRequestGroup = "";
        $strOfficeComments = "";
        $strUserComments = "";
    }
  }
$iniUserComments = strlen($strUserComments);
$remainingUserComments = (400 - $iniUserComments);
$iniOfficeComments = strlen($strOfficeComments);
$remainingOfficeComments = (400 - $iniOfficeComments);
  echo '<form id="requestcommentsform">';
  echo '<table class="redtable" width="600px">';

  echo '<tr>';
  echo '<td class="tableheadersmall" colspan="2">';
  echo '<br>Request comments for '.$strUserName.'<br><br>';
  echo '</td>';
  echo '</tr>';

  echo '<tr height="40px">';
  echo '<td>Request</td>';
  echo '<td>';
  echo  date("l, jS F Y", strtotime($strDate));
  echo '<br>';  
  echo 'Request type '.$strRequestType.' in '.$strRequestGroup;
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td height="30px">Office Comments</td>';
   if (strtoupper($strUser) != strtoupper($strRequesterLogin)) {
    echo '<td><textarea rows="2" name="officecomments" id="officecomments" cols="40" class="searchbox">'.$strOfficeComments.'</textarea><br>Characters Remaining : <span id="remainofficecomments">'.$remainingOfficeComments.'</span></td>';
  }
  else {
    echo '<td>'.$strOfficeComments.'</td>';
  }
  echo '</tr>';
  echo '<tr>';
  echo '<td>User Comments</td> ';
  if (strtoupper($strUser) == strtoupper($strRequesterLogin)) {
    echo '<td><textarea rows="2" name="usercomments" id="usercomments" cols="40" class="searchbox">'.$strUserComments.'</textarea><br>Characters Remaining : <span id="remainusercomments">'.$remainingUserComments.'</span></td>';
  }
  else {
    echo '<td>'.$strUserComments.'</td>';
  }
  echo '</tr>';
  echo '<tr>';
  echo '<td>&nbsp;</td>';
  
  echo '<td><input id="submit" name="submit" type="submit" value="Submit">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '<input type="hidden" name="date" value="'.$strDate.'">'; 
  echo '<input type="hidden" name="type" value="'.$intRequestType.'">';   
  echo '<input type="hidden" name="user" value="'.$strRequesterLogin.'">';    
  echo '</form> ';
?>
<script type="text/javascript">
$('document').ready(function(){
    $('#requestcommentsform').validate({
      submitHandler: function(form) {
        $('input[type="submit"]').prop('disabled', true);
        $.facebox.close();
        <?php
        if ($intID == 0) {?>
          document.getElementById('content').style.pointerEvents = 'none';
         <?php echo "$.ajax({type:'POST', url: 'page-includes/requests/request-apply.php', data:$('#requestcommentsform').serialize(), success: function(data) {";
          echo "ShowRequests('".$strDate."');";
          echo "}});";
        }
        else {
          echo "$.ajax({type:'POST', url: 'page-includes/requests/request-comments.php', data:$('#requestcommentsform').serialize(), success: function(data) {";
          if ($pageid == 1) {
            echo "ShowWeeklyRequestsAdmin('".$strDate."');";
          }
          echo "}});";
        }
        ?>
      
      }
    })
  
	var usermaxcommentschars = 400;
	$('#usercomments').keyup(function () {
		$(this).val($(this).val().substring(0, usermaxcommentschars));
		var tlengthuser = $(this).val().length;
		remainusercomments = usermaxcommentschars - parseInt(tlengthuser);
		$('#remainusercomments').text(remainusercomments);
	});
	var officemaxcommentschars = 400;
	$('#officecomments').keyup(function () {
		$(this).val($(this).val().substring(0, officemaxcommentschars));
		var tlengthoffice = $(this).val().length;
		remainofficecomments = officemaxcommentschars - parseInt(tlengthoffice);
		$('#remainofficecomments').text(remainofficecomments);
	});
});

</script>
<?php
}
?>