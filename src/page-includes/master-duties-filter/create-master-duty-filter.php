<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
 }

include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_filter_functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/user-scheduling-team-list.php';
//get all active master duties
//Fetch the all master duties filter data
$schedulingTeamId = $_POST['schedulingTeamId'] == null ? '0' : $_POST['schedulingTeamId'];
$intUserID = $_POST['intUserID'] == null ? 0 : $_POST['intUserID'];
$fetchMasterDutiesFilterData =  GetMasterDutyFilter($schedulingTeamId,$intUserID);
//$schedulingTeamId = empty($_POST["schedulingTeamId"]) ? 0 :$_POST["schedulingTeamId"];
//$fetchMasterDutiesFilterData =  GetMasterDutyFilter();
$rowFilterData = json_decode($fetchMasterDutiesFilterData,true);
$intTeamID = $rowFilterData[0]['TeamID'] ?? 0;
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$TeamOptions = getSchedulingTeamList($schedulingTeamId, 'master-duty-job', 'view');
?>
    <div class="filterFields" style="margin-bottom: 25px;">
                <p class="info-msg-del"><strong>Click on a record to Edit or Delete it.</strong></p>
                <form  id="filterform" class="editMasterDuty">
                <div class="fields">
                    <label class="alingment-test" for="filtername">Filter Name</label>
                    <input type="text" id="filtername" name="filternames"  value="<?php echo isset($rowFilterData[0]['FilterName']) ? $rowFilterData[0]['FilterName']: '' ?>" disabled>
                 </div>
                 <div class="fields" id="cmmnts">
                    <label class="alingment-test" for="comments">Comments</label>
                    <textarea  name="comments" id="comments" cols="48" rows="8"  maxlength = "230" disabled><?php echo isset($rowFilterData[0]['Comments']) ? $rowFilterData[0]['Comments']: ''?></textarea>
                 </div>
                 </form>
    </div>