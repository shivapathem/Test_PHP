<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

if (isset($_REQUEST['hideheader'])) {
  $intHideHeader = $_REQUEST['hideheader'];
}
else{
  $intHideHeader = 0;
}
$arrTeams = GetTeamAdmins();
echo '<h1 class="sr-only">Team Administrators</h1>';
echo '<div style="width:80%; margin:0 auto; position:relative;">';
if ($intHideHeader == 0) {
  echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%"><br><h2>Scheduling Team Administrators</h2>Should you require access to a Team you are not scheduled in please contact the Scheduling Team Administrator/s.<br><br></div><br>';
}
echo '<div class="team-administrators-row">';  
  
$i = 0; 
foreach ($arrTeams as $intTeamID => $arrTeam) {
   if ($i == 4) {   
     echo '<div class="team-administrators-row">';        
     $i = 0;
   }
    echo '<div class="team-administrators-col" valign="top"><div class="tablesmallgrey" >';
    echo '<div class="team-administrators-row" style="background: lightgray;">';
    echo '<h3 class="team-administrators-col" style="font-weight: 600; text-align: center;">';
    echo $arrTeam['Team'];
    echo '</h3>';
    echo '</div>';
    echo '<div class="team-administrators-row"><div class="team-administrators-col">'; 
    foreach ($arrTeam['Users'] as $strUserLogin => $strUserFullName) {
      echo $strUserFullName.'<br>';
    }
    echo '</div></div></div>';
 
    echo '</div>';
    if ($i == 3) {  
      echo '</div>';  
    }
    $i++;     
  }
echo '</div>';