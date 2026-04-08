<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../function-includes/DBHelper.php';
include_once '../function-includes/helpers.php';
include_once '../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intSysAdmin = GetIsSysAdmin($strUser);
$pdo = OpenDBLinkA7();
  try {
    $strQuery = "SELECT StartTime, EndTime FROM DownTime";
      $stmt = $pdo->prepare($strQuery);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
  
   $dteStartTime = date("H:i", strtotime($row['StartTime']));
   $dteEndTime = date("H:i", strtotime($row['EndTime']));
   $dteStart = date("jS F Y", strtotime($row['StartTime']));
   $dteEnd = date("jS F Y", strtotime($row['EndTime']));
    echo '<div style="width:80%; margin:0 auto; position:relative;">';
    echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">
           <br></br>
           Welcome to the Allocate Website.<br>
           Unfortunately between '.$dteStartTime.' on '.$dteStart.' and '.$dteEndTime.' on '.$dteEnd.' this site is unavalable for maintainance.
           <br></br></br>
           </div><br>';
if ($intSysAdmin == 1) {
    echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">
           <br></br>
           As a System Administrator you can remove this restriction.<br><br>
           <input type="button" id="Cancel" value="Remove Restriction" onclick="RemoveRestriction()">
           <br><br></div>';
}
echo '</div>';
?>
<script type="text/javascript">
function RemoveRestriction() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/system-down-time.php',
        data: {
            'clear': 1,
        },
        success: function (data) {
          window.setTimeout('location.reload()', 100);
        }
    });
}
</script>
