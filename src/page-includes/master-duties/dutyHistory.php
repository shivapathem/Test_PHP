<?php
session_start();
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';

$dutyID       = trim($_POST['dutyID'] ?? '');
$dutyType     = trim($_POST['dutyType'] ?? '');
$outputStr    = '';
$result       = array();
$historylist  = '';
if(!empty($dutyID))
{
  $pdo        = OpenDBLinkA7();
  $sql        = "exec [dbo].[usp_GET_DutyHistory] ?, ?";
  $stmt       = $pdo->prepare($sql);
  $stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
  $stmt->bindParam(2, $outputStr, PDO::PARAM_STR);
  $stmt->execute();
  $result     = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $resultStr  = $result[0]['History'] ?? '';
  $resultStr  = str_replace('Created', '\nCreated', $resultStr);
  $resultStr  = str_replace('Updated', '\nUpdated', $resultStr);
  $resultArr  = explode('\n', $resultStr ?? '');
  krsort($resultArr);
}else
{
  $historylist .= "<tr><td> There is no History for this user yet! </td></tr>";
}
foreach ($resultArr as $history) {
  if(empty(trim($history))) {
    continue;
  }
  $historylist .= "<tr><td style='white-space: pre-wrap;'> ".$history." </td></tr>";
}
echo <<<history
<div class="ScrollableTableRotaPeople" >
  <table class="tablesmalltidy " width="800px" role="presentation">
    <tr>
      <th id="textcenter">
        <br><b>History</b><br><br>
      </th>
    </tr>
    $historylist
    <tr>
      <th id="textcenter">
        
      </th>
    </tr>
    <tr>
      <td id="textcenter">
        <button onclick="$.facebox.close();"> close </button>
      </td>
    </tr>
  </table>
</div>
history;
?>
