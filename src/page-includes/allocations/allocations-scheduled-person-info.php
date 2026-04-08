<?php 
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/genericfunctions.php';

$scheduledPersonTitle = $_REQUEST["scheduledPersonTitle"];
$arrUser = GetScheduledPersonTeamDetailsNew($_REQUEST['scheduledPersonId']);
$homeTeamStart = $arrUser[$arrUser['homeTeamId']]['StartDate'] ?? '';
echo'<table class="tablesmalltidy" width="240px">';
echo '<tr height="20px">';
echo '<th colspan="2">&nbsp ' . $scheduledPersonTitle . '</th>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top" width="45%">&nbsp Network ID</td>';
echo '<td>';
echo empty($arrUser[$arrUser['homeTeamId']]['Login']) ? '-' : $arrUser[$arrUser['homeTeamId']]['Login'] ;
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top" width="45%">&nbsp Home team</td>';
echo '<td>';
echo $arrUser[$arrUser['homeTeamId']]['TeamName'].($homeTeamStart !== '' ? ' (Start Date: ' . $homeTeamStart . ')' : '') ;
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top" width="45%">&nbsp Additional Team</td>';
echo '<td> ';
$additionalTeams = [];
foreach((array)$arrUser as $teamDetails) {
    if(isset($teamDetails['IsHomeTeam']) && $teamDetails['IsHomeTeam'] == 0) {
        $tStart = $teamDetails['StartDate'] ?? '';
        $additionalTeams[] = $teamDetails['TeamName'].($tStart !== '' ? ' (Start Date: ' . $tStart . ')' : '');
    }
}
echo empty($additionalTeams) ? '-' : implode(', ', $additionalTeams);
echo '</td>';
echo '</tr>';
echo '</table>';