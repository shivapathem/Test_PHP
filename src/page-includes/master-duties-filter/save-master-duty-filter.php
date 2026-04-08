<?php
session_start();
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../function-includes/masterduty_filter_functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/genericfunctions.php';
    /*Set the permission of the user for this page*/
    //Check User Authentication
$pageid = 4;
$schedulingTeamId = $_REQUEST['schedulingTeam'] ?? 0;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $schedulingTeamId);

if ($permissions->cancreate == 0) {
  $intStatus = 0;
  $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
  header('Content-type: application/json');
  echo json_encode($response_array); exit();
}

    //get all request param master duties
    
    $filterID =  trim($_REQUEST['dataFilterID']);
    $strFilterName =  $_REQUEST['dataFilterName'];
    $strComment =  trim($_REQUEST['dataComments']);    

    $userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    $addNewRow = '';
    list($filterID,$intStatus, $strStatus)  =  InsUpdMasterDutyFilter($filterID,$strFilterName,$strComment, $userID,$schedulingTeamId);

    $strSchedulingTeamName = '';
    if($schedulingTeamId > 0){
      $strSchedulingTeamName = GetTeamNameFromID($schedulingTeamId);
    }
    

    if($intStatus == false){
        $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
      }
      else{
        $addNewRow = "<tr class='highlightOrange' id='filterID_".$filterID."' data-filterid=".$filterID.">
        <td id='filtername_".$filterID."' class='tr-td-class'>".   $strFilterName."</td>
        <td id='comments_".$filterID."' class='tr-td-class'>".  $strComment."</td>
        <td id='team_".$filterID."' class='tr-td-class'  data-team-id='".$schedulingTeamId."'>".  $strSchedulingTeamName."</td>
        <td id = 'ispublic_".$filterID."' class='tbl-cent-aligin'><input type='checkbox' name='isPublic' class='ispublic_".$filterID."' id='isPublicCheck_".$filterID."'  disabled></td>
        <td id = 'isactive_ ".$filterID."' class='tbl-cent-aligin'><input type='checkbox' name='isActive' class='isactive_".$filterID."' id='isActiveCheck_".$filterID."'  disabled></td>
        </tr>";
        $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus,'newrow'=>$addNewRow,'filterID'=>$filterID); 
      }
    
    /*else {
      $intStatus = 0;
      $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
    }*/
    header('Content-type: application/json');
  echo json_encode($response_array);
?>
