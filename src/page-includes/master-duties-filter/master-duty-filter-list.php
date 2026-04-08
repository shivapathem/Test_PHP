<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../function-includes/masterduty_filter_functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';
    /*Set the permission of the user for this page*/
    //Check User Authentication
$pageid = 4;

// Call User Permission function.
$permissions = getUserRolePermissions($pageid);
if ($permissions->cancreate == 0) {
    $intStatus = 0;
    $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
    exit();
  }

    //get all active master duties
    $intAreaID = $_SESSION['user']['AreaID'];
    $intFilterId = $_REQUEST['FilterID'];
    $FilterValue = 'new';//$_REQUEST['FilterValue'];
    $intDutyTypeID = 1;
    $strSearchText = '';
    $intArchived = 1;
    if($FilterValue == 'new'){
        $rsDutiesJson = ListAllMasterDutiesForRota($intAreaID, $intDutyTypeID, $intArchived, $strSearchText, $strTeams);
        $rsDuties = json_decode($rsDutiesJson,true);
    }else{
        $rsSelectFilterDutiesFJson = GetAllMasterDutiesForFilter($intFilterId);
        $rsDuties = json_decode($rsSelectFilterDutiesFJson,true);
    }
?>

    <div class="filterFields">
                <p class="info-msg-del"><strong>Click on a record to Edit or Delete it.</strong></p>
                <form  id="filterform">
                <div class="fields">
                    <label class="alingment-test"  for="filtername">Filter Name</label>
                    <input type="text" id="filtername" name="filternames"  value="" disabled>
                 </div>
                 <div class="fields" id="cmmnts">
                    <label class="alingment-test" for="comments">Comments</label>
                    <textarea  name="comments" id="comments" cols="30" rows="5"  maxlength = "200" disabled></textarea>
                 </div>
                 </form>
    </div>

