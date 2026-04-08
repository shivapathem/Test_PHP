<?php 
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/helpers.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../page-includes/staff-details/process/classSchedulTeamHistory.php';

$scheduledPersonTitle = $_REQUEST["scheduledPersonTitle"];
$arrUser = GetScheduledPersonTeamDetailsNew($_REQUEST['scheduledPersonId']);
$scteamobj = new classSchedulTeamHistory;
$scheduledPersonTeamHistory = $scteamobj->getScheduleTeamHistory($_REQUEST['scheduledPersonId'], 1);
echo '<h4 style="margin-top:10px;margin-bottom:10px;"><b>' . $scheduledPersonTitle . ' - Scheduling Team Details</b></h4>';
echo'<table class="tablesmalltidy" id="scheduling-team-history-table">';
echo '<thead>';
echo '<tr>';
echo '<th>Scheduling Team</th>';
echo '<th>Home Team Y/N</th>';
echo '<th>Start Date</th>';
echo '<th>End Date</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
foreach($scheduledPersonTeamHistory['data'] as $history) {
    echo '<tr>';
    echo '<td>' . $history[0] . '</td>';
    echo '<td>' . $history[1] . '</td>';
    echo '<td>' . $history[2] . '</td>';
    echo '<td>' . $history[3] . '</td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
?>

<script>
    $(function() {
        $('#scheduling-team-history-table').DataTable({
            "ordering": false,
            "bPaginate": false,
            "bLengthChange" : false, 
            "bInfo":false,  
            order: [[2, 'desc']]
        });
    })
</script>