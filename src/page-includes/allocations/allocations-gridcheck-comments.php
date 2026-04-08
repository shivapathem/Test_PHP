<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start(); 
}
date_default_timezone_set('Europe/London');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intTeamID = $_REQUEST['teamId']; 
$date = $_REQUEST['date'];
$period = $_REQUEST['period'];

if (isset($_REQUEST['action'])) {
  $action = $_REQUEST['action'];
}
else {
  $action = '';
}

$pdo = OpenDBLinkA7();
$dateTime = $date.' '.'00:00:00';

if (!isset($_REQUEST['submit'])) {
	
  echo '<div id="page">';
  // Before the form is submitted
 try{ 	
    $sql = "SELECT comments
              FROM  GridChecks
              WHERE (period = ?)
              AND (dDate = CONVERT(DATETIME, ?, 102))
              AND (SchedulingTeamId = ?)";

	$stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $period, PDO::PARAM_INT);
    $stmt->bindParam(2, $dateTime, PDO::PARAM_STR);
	$stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchall(PDO::FETCH_ASSOC); 
}catch (PDOException $e) {
      echo $e->getMessage();
	  $result =  array();
 }	
  $comments = '';
  foreach ($result as $row) {	
	  $comments = $row['comments'];
	}
	
  // The form
  echo '<form id="checkscomments">';
  echo '<table class="redtable" width="500px">';
  echo '<tr>';
  if ($action != '') {
    echo '<th colspan="2"><br>You are marking '.$date.'
          as being checked but with problems.<br>
          Please enter why in the comments section<br><br></th>';
  }
  else {
    echo '<th colspan="2"><br>Grid Check Comments for '.date("jS M Y", strtotime($date)).'<br><br></th>';
  }

  echo '</tr>';
  echo '<tr>';
  echo '<th width="150px">Comments</th>';
  echo '<td>';
  echo '<textarea rows="4" name="comments" cols="40">'.$comments.'</textarea>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '<td></td>';
  echo '<td><input name="submit" type="submit" value="Update"></input>&nbsp;&nbsp;</input>&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';

  echo '<input type="hidden" name="date" value="'.$date.'">';
  echo '<input type="hidden" name="period" value="'.$period.'">';
  echo '<input type="hidden" name="teamId" value="'.$intTeamID.'">';
  echo '<input type="hidden" name="action" value="'.$action.'">';
  echo '</form>';

?>

<script type="text/javascript">
$('document').ready(function(){
    $('#checkscomments').validate({
      rules: {
        comments: {
          required: true,
        },
      },
      errorPlacement: function(){
            return false;
        },
        submitHandler: function(form) {
          $.ajax({type:'POST', url: 'page-includes/allocations/allocations-gridcheck-comments.php', data:$('#checkscomments').serialize(), success: function(data) {
            $.facebox.close();
            ShowDailyAllocations (<?php echo $intTeamID?>);
          }});
        }
    })
  });
</script>
<?php
  echo '</div>';
}
else {	
  $comments = escapeSingleQuotes($_REQUEST['comments']);
   $fullname = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
  $history = "Marked as Checked and with problems by ".$fullname." on ".date("d/m/Y")." at ".date("H:i")."<br>";

  $history = escapeSingleQuotes($history);
  try{
  $query = "if exists   (SELECT        ID
                          FROM          GridChecks
                          WHERE        (period = ?) 
                          AND (dDate = ?) 
                          AND (SchedulingTeamId = ?))
             UPDATE       GridChecks
                            SET                
                              comments = ?,  
                              status=1,  
                              history = CONCAT(ISNULL(history,''), ?)
                            WHERE        
                              (period = ?) 
                            AND 
                              (dDate = ?) 
                            AND 
                              (SchedulingTeamId = ?)
             else         
             INSERT INTO GridChecks
                           (comments, history, dDate, status, SchedulingTeamId, period)
                         VALUES        (
                           ?,?,?,1,?,?)";
  
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(1, $period, PDO::PARAM_INT);
  $stmt->bindParam(2, $dateTime, PDO::PARAM_STR);
  $stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
  $stmt->bindParam(4, $comments, PDO::PARAM_STR);
  $stmt->bindParam(5, $history, PDO::PARAM_STR);
  $stmt->bindParam(6, $period, PDO::PARAM_INT);
  $stmt->bindParam(7, $dateTime, PDO::PARAM_STR);
  $stmt->bindParam(8, $intTeamID, PDO::PARAM_INT);
  $stmt->bindParam(9, $comments, PDO::PARAM_STR);
  $stmt->bindParam(10, $history, PDO::PARAM_STR);
  $stmt->bindParam(11, $dateTime, PDO::PARAM_STR);
  $stmt->bindParam(12, $intTeamID, PDO::PARAM_INT);
  $stmt->bindParam(13, $period, PDO::PARAM_INT);
  $stmt->execute();
  }catch (PDOException $e) {
      echo $e->getMessage();
  }
		

// Send Email
$strDepartmentEmail = GetTeamEmail ($intTeamID);

if ($strDepartmentEmail != '') {
  $mail = new PHPMailer\PHPMailer\PHPMailer();
  
  $mail->isSMTP();
  $mail->SMTPDebug = 0;
  
  $mail->Host = getenv('SMTP_HOST');
  $mail->Port = 25;
  $mail->setFrom('noreply@bbc.co.uk', 'Allocate');
  $mailAdd = $strDepartmentEmail;
  $arrMailAdd = explode(";", $mailAdd);
  for($intCount = 0; $intCount < count($arrMailAdd); $intCount++)
  {
      $mail->addAddress($arrMailAdd[$intCount ]);
  }
  
  $fullname = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
  $mail->Subject = getenv('EMAIL_SUFFIX').$fullname.' - Grid Checks for '.date("l, jS F Y", strtotime($date));
  $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
  $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
  
  $html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?: '').'</style>
  
       <body>
       <img alt="banner" src="cid:pobanner" /><br><br>';
  
    $html.= '<h2>Grid Check Comments - '.$period.' Days</h2>';
  
    $html.= '<table width="1000px">';
    $html.= '<tr>';
    $html.= '<td width="400px" class="datecell">Grid Date</td>';
    $html.= '<td width="600px" class="lightcell"><b>'.date("l, jS F Y", strtotime($date)).'</b></td>';
    $html.= '</tr>';
    $html.= '<tr>';
    $html.= '<td width="400px" class="datecell">Comments</td>';
    $html.= '<td width="600px" class="lightcell"><b>'.nl2br($comments).'</b></td>';
    $html.= '</tr>';
    $html.= '<tr>';
    $html.= '<td width="400px" class="datecell">Added By</td>';
    $html.= '<td width="600px" class="lightcell">'.$fullname.'</td>';
    $html.= '</tr>';
      $html.= '<tr>';
    $html.= '<td width="400px" class="datecell">Added On</td>';
    $html.= '<td width="600px" class="lightcell">'.date("jS F Y").' at '.date("H:i").'</td>';
    $html.= '</tr>';
    $html.= '</table>';
  
    $html.='<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
    $html.='</body></html>';
  
    $mail->msgHTML($html);
    if (!$mail->send()) {
      echo "Mailer Error: " . $mail->ErrorInfo;exit;
    } else {
      echo "Message sent!";
    }
  }
}