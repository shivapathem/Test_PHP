<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
date_default_timezone_set('Europe/London');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/DB_Functions.php';
$jobid = $_REQUEST['id'];
$moduleName = 'AllocationJobs';
$pdo = OpenDBLinkA7();
  // Edited job

    $query = "SELECT AJ_JobName as JobName, AJ_JobStartTimeSec as StartTime, AJ_JobEndTimeSec as EndTime from AllocationsJobs
    WHERE  AJ_AllocateJobID = $jobid";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

  $historyResults = json_decode(getHistoryLists($moduleName,$jobid), true);

  echo '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 525px; z-index: 101;">';
  echo '<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">';
  echo '<span id="ui-id-5" class="ui-dialog-title">Job History for '.$result['JobName'].'</span>';
  echo '</div>';
  echo '<div style="width: auto; min-height: 0px; max-height: 500px;" class="ui-dialog-content ui-widget-content">';

  echo '<table class="redtable" width="100%">';
  echo '<tr height="30px">';
  echo '<th>';
  echo 'Times';
  echo '</th>';
  echo '<th>';
  echo secondsToTime($result['StartTime']);
  echo '-';
  echo secondsToTime($result['EndTime']);
  echo '</th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="2" class="historyholder">';

  foreach($historyResults as $key => $value){
    echo '<div>';
    echo $value['History'];
    echo '</div>';
  }
   echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="2" align="center">';
  echo '<input type="button" value="Done" onclick="cancel()">';
  echo '</td>';
  echo '</tr>';

  echo '</table>';
  echo '</div>';