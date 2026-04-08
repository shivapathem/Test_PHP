<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Master Duty Filter Page,basic design implement here

include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/masterduty_filter_functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/user-scheduling-team-list.php';
include_once 'setTeamCookieDutiesFilter.php';

$intTeamID = $_REQUEST['schedulingTeamId'] ?? $_COOKIE['masterdutyteams'] ?? 0;
$intUserID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];;
/*Set the permission of the user for this page*/
//Check User Authentication
$pageid = 4;

if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ? $_COOKIE["masterdutyteams"] : 0;
}else{
    $strTeams = 0;
}

// Call User Permission function.
if($strTeams == 0)
{ 
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $strTeams);
} 
//Fetch the all master duties filter data
$fetchMasterDutiesFilterData =  GetMasterDutyFilter($strTeams,$intUserID);
$rowFilterData = json_decode($fetchMasterDutiesFilterData,true);
?>
<link rel="stylesheet" href="styles/masterdutyfilter.css">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css">
<?php

$TeamOptions = getSchedulingTeamList($strTeams, 'master-duty-job', 'view');
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

echo '<div class="blueboxmedtextheader" style="width:100%;" >';
echo '<div style="width:40%; float:left;">';
echo '<h1 class="blueboxmedtextheader-title">Master Duties Filters</h1>';
echo '</div>';
echo '<div style="width:60%; float:left;">';
echo 'Teams: ';
echo '<select class="chosen-select" name="schedulingTeam" id="schedulingTeamId" style="background-color:blue; width:80%; height:20px;" onchange=\'javascript:ListDutyFiltersByTeam()\';>';
echo "<option value=''>Select The Team</option>";
echo '' . $TeamOptions . '';
echo '</select>';
echo '</div>';
echo '</div>';
?>
    <?php if ($permissions->canview == 1) {?>
        <section class="filter">
            <div class="container">
                <section class="formDepartment">
                
                    <fieldset class="reset-this redo-fieldset" role="presentation">
                        <legend class="reset-this redo-legend"><h2 class="legend-heading">Existing Duties Filter</h2></legend>
                        <div class="tblContainer">
                            <section class="existingDept">
                                <div class="tables-FILT">
                                    <div class="scrollable" id="TBL1">
                                        
                                        <table class="js_masterFilterList fullwidth" id ="js_masterFilterList">
                                        <tbody>
                                                <tr>
                                                <th class="fname">Filter Name</th>
                                                <th class="Comments">Comments</th>
                                                <th class="Team">Scheduling Team</th>
                                                <th class="tbl-cent-alignin">Public</th>
                                                <th class="tbl-cent-alignin">Active</th>
                                                </tr>
                                            
                                            
                                                <?php 
                                                    $count  =  0;
                                                    //looping the filter data
                                                    if(!empty($rowFilterData)) {
                                                    foreach($rowFilterData as $key => $filter){
                                                        $IsPublic = $IsActiveCheck = $class = " "; 
                                                        
                                                        if($filter['IsPublic'] == 1){
                                                            $IsPublic = 'checked="checked"'; 
                                                        }
                                                        if($filter['IsActive'] == 1 ){
                                                            $IsActiveCheck = 'checked="checked"'; 
                                                        }
                                                        if($key == $count){
                                                            $class = 'highlightOrange';
                                                        }
                                                        
                                                ?>

                                                <tr class="<?php echo $class; ?>" id="filterID_<?php echo $filter['MasterDutyFilterID'] ?>" data-filterID = <?php echo  $filter['MasterDutyFilterID']?>>
                                                <td id="filtername_<?php echo $filter['MasterDutyFilterID'] ?>" class="tr-td-class"><?php echo $filter['FilterName']?></td>
                                                <td id ="comments_<?php echo $filter['MasterDutyFilterID'] ?>" class="tr-td-class"><?php echo $filter['Comments']?></td>
                                                <td id ="team_<?php echo $filter['MasterDutyFilterID'] ?>" class="tr-td-class" data-team-id="<?php echo $filter['TeamID'] ?>"><?php echo $filter['schedulingTeamName']?></td>
                                                <td id = "ispublic_<?php echo $filter['MasterDutyFilterID'] ?>" class="tr-td-class tbl-cent-aligin"><input type="checkbox" name="isPublic" class = "ispublic_<?php echo $filter['MasterDutyFilterID'] ?>" id="isPublicCheck_<?php echo $filter['MasterDutyFilterID'] ?>" disabled <?php   echo $IsPublic ?> ></td>
                                                <td id = "isactive_<?php echo $filter['MasterDutyFilterID'] ?>" class="tr-td-class tbl-cent-aligin"><input type="checkbox" name="isActive" class = "isactive_<?php echo $filter['MasterDutyFilterID'] ?>"  id="isActiveCheck_<?php echo $filter['MasterDutyFilterID'] ?>" disabled <?php echo $IsActiveCheck ?>></td>
                                                </tr>
                                            <?php } } else { ?>
                                                <tr id="noRecordMsgContainer" ><td colspan=5>No result found on this Team</td></tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <div class="center-section centre-align BtnHeight">
                            <?php 
                                $publicButtontext= 'Set To Public Filter';
                                $activeButtontext= 'Activate Filter';
								$rowFilterData[0]['IsPublic'] = isset($rowFilterData[0]['IsPublic']) ? $rowFilterData[0]['IsPublic'] : 0;
                                if( $rowFilterData[0]['IsPublic'] > 0 ){
                                    $publicButtontext= 'Set To Private Filter';
                                }
                                if(isset($rowFilterData[0]['UserID']) && $rowFilterData[0]['IsActive'] == 1 ){
                                    $activeButtontext= 'De-Activate Filter';
                                }
                                if ($permissions->cancreate == 1) {
                            ?>
                            
                                <div class="newbutton" >
                                    <button class="btnWidth" id = "js_newfilter">New</button>
                                </div>
                                <?php }
                                     if ($permissions->canmodify == 1) { ?>
                                <div class="newbutton">
                                    <button class="btnWidth" id="js_modifyfilter">Modify</button>
                                </div>
                                <?php }
                                     if ($permissions->candelete == 1) { ?>
                                <div class="newbutton">
                                    <button class="btnWidth" id="js_deletefilter">Delete</button>
                                </div>
                                <?php }
                                     if ($permissions->canmodify == 1) { ?>
                                <div class="newbutton">
                                  
                                    <button class="btnWidth" id="js_activefilter" data-actionKey = 'isActive'><?php echo $activeButtontext; ?></button>
                                </div>
                                <div class="newbutton">
                                    <?php if($permissions->canmakepublic == 1) { ?>
                                    <button class="btnWidth " id="js_privatefilter" data-actionKey = 'isPublic' data-userid ="<?php echo $sessUserId;?>" ><?php echo $publicButtontext; ?></button>
                                <?php } ?>
                                </div>
                                <?php } ?>
                                <div class="js_newbutton">
                                    <button class="btnWidth js_savefilter" data-actionType = 'newSave' id="js_savefilter">Save</button>
                                    <button class="btnWidth js_confirmfilter" data-actionType = 'newSave' id="js_confirmfilter">Confirm</button>
                                    <button class="btnWidth js_updatefilter" data-actionType = 'update' id="js_updatefilter">Save</button>

                                </div>
 
                                
                                <div class="js_newbutton">
                                    <button class="btnWidth" id="js_cancelfilter">Cancel</button>
                                </div>      
                                                            

                                </div>
								</section>
                            </div>
                        </fieldset>

                </section>
            </div>
        </section>
    <?php } ?>                                        
        <section class="info-section" id = "newFilterCreate"></section>
    
        <section class="duties-selection">
            <fieldset id="AssignedDutiesList" role="presentation">
            <legend><h2 class="legend-heading">Duties Selection for filter: <span id="masterDutiesTitle"> All Londan BST </span></h2></legend>
            </fieldset>
        </section>

        <script src="js/masterDutiesFilter.js?v=<?php echo time(); ?>"></script>
        <script>
            function ListDutyFiltersByTeam() {
                var strTeams = $('#schedulingTeamId').val();
                var CookieName = "masterdutyteams";
                $.cookie(CookieName, strTeams, {expires: 1});
				if($.cookie('selectRotaId') > 0)
				{
					$.cookie('selectRotaId', '', {expires: 1});
				}
                var strTeams = $('#schedulingTeamId').val();
                $.post("page-includes/master-duties-filter/master_duties_filter_design.php", {
                        schedulingTeamId : strTeams
                    },
                    function(data,status){
                        $('#content').html(data);
                    });
            }
        $(document).ready(function(){
            if (<?php echo empty($permissions->canview) ? 0 : $permissions->canview ?> == 0) {

                $( '#content' ).load( 'page-includes/no_access.php', function() { });
            }
            var tabCookieName = "container-fluid";
            $.cookie(tabCookieName,{ expires: 1 });

            $('.chosen-select').chosen();

           // Call the  master duty functionalty
            function CreateMasterDutyFilter(){
                let schedulingTeamId = $('#schedulingTeamId').val();
              $.post("page-includes/master-duties-filter/create-master-duty-filter.php",{
                      schedulingTeamId: schedulingTeamId,
                      intUserID: <?php echo ($intUserID == 0 ? 0 : $intUserID) ?>
                  },
                  function(data,status){

                    $('#newFilterCreate').html(data);
                }
              );
            }
    
            CreateMasterDutyFilter();
        
        });
        </script>