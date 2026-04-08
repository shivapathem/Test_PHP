<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
date_default_timezone_set('Europe/London');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
require_once __DIR__ . '/../weekly/service/AllocationService.php';

use Symfony\Component\HttpFoundation\Request;

$pdo = OpenDBLinkA7();
$request = Request::createFromGlobals();
$service = new AllocationService(); 

$jobId = $request->get('jobId');
$isEdited = $request->get('isEdited');
$parentJobId = $request->get('parentId');

if($isEdited != 1) {
  $updatedJobId = $jobId;
} else {
  $updatedJobId = $parentJobId;
}

if (!isset($_REQUEST['submit'])) {
  // Before the form is submitted
  // Get the job
  $query = "SELECT JobName, Comments, schedulingTeamId
            FROM  Allocations_jobs
            WHERE (ID = :updatedJobId)";

  $stmt = $pdo->prepare($query);
  $stmt->bindValue(':updatedJobId', $updatedJobId, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $jobname = $row['JobName'];
  $comments = $row['Comments'];
  $teamId = $row['schedulingTeamId'];
 
  // The form
  echo '<form id="jobcomments">';
  echo '<table class="tablesmalltidy dutyCommentTable" width="100%">';
  echo '<tr height="30px">';
  echo '<th><b>Job Comments for '.$jobname.'</b></th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>';
  echo '<textarea rows="4" name="comments" cols="60" maxlength="500">'.$comments.'</textarea>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td align="center"><input name="submit" type="submit" value="Update"></input>&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="jobId" value="'.$updatedJobId.'">';
  echo '<input type="hidden" name="isEdited" value="'.$isEdited.'">';
  echo '<input type="hidden" name="parentId" value="'.$parentJobId.'">';
  echo '</form>';
?>
<script type="text/javascript">
$('document').ready(function(){
  $('#jobcomments').validate({
    submitHandler: function(form) {
      $.ajax({
        type:'POST', 
        url: 'page-includes/allocations/edits/jobcomments.php', 
        data:$('#jobcomments').serialize(), 
        success: function(data) {
          $.facebox.close();
          ShowDailyAllocations (<?php echo $teamId?>, $('#strCurrentDate').val());
        }
      });
    }
  })
});

function cancel() {
  $.facebox.close();
}

</script>
<?php
}
else {
  try {
    $comments = escapeSingleQuotes($request->get('comments'));
    $jobhistory= "Comments added by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i")."<hr>";
    $jobhistory = escapeSingleQuotes($jobhistory);
    $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

    $upQuery = "UPDATE Allocations_jobs
                SET
                Comments = :comments
                WHERE ID = :updatedJobId";

    $stmt = $pdo->prepare($upQuery);
    $stmt->bindValue(':updatedJobId', $updatedJobId, PDO::PARAM_INT);
    $stmt->bindValue(':comments', $comments, PDO::PARAM_STR);
    
    if($stmt->execute()) {
      $request->request->set('attributeId',$updatedJobId);
      $request->request->set('historyType',9);
      $request->request->set('userId',$sessUserId);
      $request->request->set('message',$jobhistory);
      $statusLog = $service->addAllocationHistory($request);
    } 
  } catch(Exception $e){
    logger()->critical('DB Error', (array) $e);
  }
}