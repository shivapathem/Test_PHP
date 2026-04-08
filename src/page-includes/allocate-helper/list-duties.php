<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$commonObj = new classCommonDBFunctions();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
if ($arrUsersTeamdata["isSkillsAdmin"] == 1) {
  $intTeamID = $_POST['teamId'];
  $strDate = $_POST['date'];
  $bbcweeknumberArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim($strDate);
  $weekday = intval($bbcweeknumberArray['ixDayInWeek']);
  $bst = getbst($strDate);
  $result = [];
  echo '<table class="tablesmall" id="duties" width="100%">';
  // Set the statement to use
  $pdo = OpenDBLinkA7();
  $sql = "exec [dbo].[usp_get_TeamsSkillDuty] ?,?,?";
  $stmt = $pdo->prepare($sql);
  // The parameters
  $stmt->bindParam(1, $weekday, PDO::PARAM_INT);
  $stmt->bindParam(2, $intTeamID, PDO::PARAM_INT);
  $stmt->bindParam(3, $bst, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($result)) {
    foreach ($result as $row) {
      echo '<tr>';
      echo '<td class="handcursor" onclick="javascript:FillHelper(' . $row['id'] . ',\'' . $strDate . '\',' . $intTeamID . ')";>';
      echo $row['duty'] . ' ' . $row['description'];
      echo '</td>';
      echo '</tr>';
    }
  } else {
    echo '<tr><td>Not Record Found.</td></tr>';
  }
  echo '</table>';
} else {
  echo 'Access Denied';
  die;
}
?>

<script type="text/javascript">
  $(document).ready(function() {
    $("table.tablesmall tr:odd").addClass("odd");
    $("table.tablesmall tr:even").addClass("even");
  })

  $('#duties td').click(function(e) {
    $('#duties td').removeClass('highlighted');
    $(this).addClass('highlighted');
  });
</script>