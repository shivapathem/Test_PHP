<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/masterduty_filter_functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';
    /*Set the permission of the user for this page*/
    //Check User Authentication
$pageid = 4;

$intTeamID = $_REQUEST['schedulingTeamId'] ?? $_COOKIE['masterdutyteams'] ?? 0;

// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $intTeamID);


//get all active master duties
$filterID = isset($_REQUEST['dataFilterID'])? $_REQUEST['dataFilterID']:0;
$intDutyTypeID = 1;

if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
}else{
    $strTeams = 0;
}
$masterDutyIds= '';
if($_REQUEST['selectedDutyIds'] != ''){
$masterDutyIds =  json_decode(stripslashes($_REQUEST['selectedDutyIds']));
}


//get all the duties avaialble for filter
$rsAvailableDutiesJson  = GetAvaialbleDutiesToFilter($filterID,$intDutyTypeID, $strTeams);
$rsAvailableDutiesJson = json_decode($rsAvailableDutiesJson,true);

//get the assigned duties to the filter
$rsAssignedDutiesJson  = GetAssignedDutiesToFilter($filterID,$intDutyTypeID);
$rsAssignedDutiesJson = json_decode($rsAssignedDutiesJson,true);

$addview ='';
//  $addview .='<legend>Duties Selection for filter: '.$_REQUEST['filterName'].'</legend>
 $addview .='<h2 style="position: absolute; margin-top: -18px; background: #fff;">Duties Selection for filter: '.$_REQUEST['filterName'].'</h2>
 <div class="filtSection" style="margin-top: 10px;">
    <div class="filtLeft" id="leftbox">
        <h3 id="available-master-duties" style="position: absolute; margin: -4px 15px; background: #fff;">Available Master Duties</h3>
            <fieldset aria-labelledby="available-master-duties" style="padding: 15px;margin-bottom: 15px; margin-top: 5px;">
                            <div class="outerDiv">
                                <div id="innerDiv">
                                  <select id="multiSelect1" multiple="multiple" class="js_masterduties">';
                                         foreach($rsAvailableDutiesJson  as $dutyData) {
                                            if($dutyData['DutyTypeID'] == 1){
                                            $addview.=' <option value='.  $dutyData['MasterDutyID'] .'>'.  $dutyData['DutyName'] .'</option>';
                                          }
                                        }

                                $addview.=   ' </select>
                                </div>
                            </div>
                        </fieldset>
                        <h3 id="available-misc-duties" style="position: absolute; margin: -10px 15px; background: #fff;">Available Misc Duties</h3>
                        <fieldset aria-labelledby="available-misc-duties" style="padding: 15px;">
                            <div class="outerDiv">
                                <div id="innerDiv">
                                    <select id="multiSelect2" multiple="multiple" id="lstBox1">';
                                    foreach($rsAvailableDutiesJson  as $dutyMiscData) {

                                        if($dutyMiscData['DutyTypeID'] != 1){

                                            $addview.=' <option value='.$dutyMiscData['MasterDutyID'] .'>'.  $dutyMiscData['DutyName'] .'</option>';
                                        }
                                    }

                            $addview.='</select>
                                 </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="filtLeft">
                    <div class="filterLeft" id="middlebox">
                        <div class="Buttons">';
                        if ($permissions->canmodify == 1 || $permissions->cancreate == 1 ) {
                        $addview.='<div class="fields">
                            <button id="btnAllRight" aria-label="Move all master duties to filter">
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>
                        <div class="fields">
                            <button id="btnRight" aria-label="Move selected master duties to filter">
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>';
                        }

                        $addview.='</div>
                        <div class="Buttons">';
                        if ($permissions->canmodify == 1 || $permissions->cancreate == 1 ) {
                        $addview.='<div class="fields">
                            <button id="btnLeft" aria-label="Move selected master or miscellaneous duties back to available duties">
                                <i class="fal fa-angle-left"></i>
                            </button>
                        </div>
                        <div class="fields">
                            <button id="btnAllLeft" aria-label="Move all master or miscellaneous duties back to available duties">
                                <i class="fal fa-angle-left"></i>
                                <i class="fal fa-angle-left"></i>
                                <i class="fal fa-angle-left"></i>
                            </button>
                        </div>';
                        }
                        $addview.='</div>
                        <div class="Buttons movedownBtns">';
                        if ($permissions->canmodify == 1 || $permissions->cancreate == 1 ) {
                        $addview.=' <div class="fields">
                            <button id="miscdutiesMoveall" aria-label="Move all miscellaneous duties to filter">
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>
                        <div class="fields">
                            <button id="miscduties" aria-label="Move selected miscellaneous duties to filter">
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>';
                        }
                        $addview.=' </div>
                    </div>
                    </div>
                    <div class="filtLeft">
                    <div class="filterLeft" id="rightbox">
                    <h3 id="current-masters-&-misc-duties-in-filter" style="position: absolute; margin: -10px 15px; background: #fff;">Current Masters & Misc Duties in Filter</h3>
                        <fieldset aria-labelledby="current-masters-&-misc-duties-in-filter" style="padding: 15px;">
                            <div class="outerDiv" id="outerDivright">
                                <div id="innerDiv">
                                  <select id="multiSelect3" multiple="multiple">';
                                    foreach($rsAssignedDutiesJson  as $dutyAssignedData) {
                                         $selected =  is_array($masterDutyIds) && in_array($dutyAssignedData['MasterDutyID'],$masterDutyIds)? 'selected': '';

                                        $addview.=' <option '.$selected.'  value='. $dutyAssignedData['MasterDutyID'].'>'.$dutyAssignedData['DutyName'] .'</option>';
                                    }
                    $addview.=' </select>
                                </div>
                              </div>
                        </fieldset>
                    </div>
                    </div>
                </div>';

    $response_array = array('status'=>'success','view' =>$addview );

    header('Content-type: application/json');
    echo json_encode($response_array);


?>
