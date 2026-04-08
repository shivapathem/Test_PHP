<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../../../function-includes/testaccess.php';
include_once '../../../../class-includes/userRolePermissions.php';

//Allocate Users Staff handled Scheduled and Non Scheduled Team Staff UI
class  classAllocateUsersStaffUI {
 /*
    * @Description : Non Scheduled Staff design
	* @access : Public
	* @global : Not Applicable
	* @param  : $searchUser,$searchTeam,$actionType=''
	* @return : Return the assigned duties filter resulrt
    */
function getNonScheduledStaffTeamHTML($teamlists,$additionalrolelist,$usertype){
  
  $activeClass= '';
  $columname = 'Role';
  $tablename = 'js_nonscheduledstaffteamlist';
  
  $pageid = 11;
    // Call User Permission function.
    $permissions = getUserRolePermissions($pageid);
    if ($permissions->cancreate == 0 ) {
        $activeClass = 'activeclass';
    }
    
 $nonscheduled= '';
 $nonscheduled .= '<script src="js/userStaffTab.js"></script>
 
 <input type="hidden"  name = "js_usertype" value="'.$usertype .'" id="js_usertype">
 <input type="hidden"  name = "js_tablename" value="'.$tablename .'" id="js_tablename">
 <input type="hidden"  name = "js_create" value="" id="js_create">
 <input type="hidden"  name = "js_sysadmin" value="'.$_SESSION['user']['SysAdmin']  .'" id="js_sysadmin">                 
                       <div class="fieldsection">
                              <div class="parant_div_1" id="parant_div_1">
                               <div class="child_div_1 no-border additonaltemtd">';
                               
                                $nonscheduled .='<button class=" notclickable nonscheduledStaffbtn  buttonDisabled btn-class addbtn '. $activeClass.'" id="js_addnonscheduledstaff">
							                            <img src="../images/button_add.png" alt="">&nbsp;Non Scheduled Staff
						                          </button>';
                              
                                      $nonscheduled .='<form class="stfform">            
                                 <div class="fields">
                                  <label for="hometeam" >Scheduling Team:</label>
                                  <select name="hometeam" class="division-seclect" id="js_teamdropdown">
                                   <option value="">Select the Team</option>';
                                   foreach($teamlists as $teamdata){
                                      $nonscheduled .= '<option data-divisionid = '.$teamdata['DivisionId'].' value="'.$teamdata['TeamID'].'">'.$teamdata['TeamName'].'</option>';
                                   }
                                   
                                 $nonscheduled.=' </select>
                                 </div>
                                </form>

                               </div>
                             
                        </div>
                      </div>

                     <div class="scheduledNonSchstaffteamlist border-0">
                        <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer " id="'.$tablename.'"
                        style="width: 100%;position: relative;" role="grid" aria-describedby="joblisting_info">
                              <thead>
                              <tr>
                               <th class="cellWmmrequested">Full Name</th>
                               <th class="cellWmmrequested">Staff Number</th>
                               <th class="cellWmmrequested">Network ID</th>
                               <th class="schedulingTeam185W">'.$columname.'</th>
                               <th class="mWH60">Default</th>';
                               foreach($additionalrolelist as $roledata){
								   if ($roledata['RoleName']!="Team Leader"){
									$nonscheduled .= '<th class="mWH90">'.$roledata['RoleName'].'</th>';
								   }
                               }
                              
                               $nonscheduled.='<th class="mWH90">Info/History</th><th class="mWH60">Action</th>';
                               
                               $nonscheduled.='</tr></thead>
                              <tbody class="context-menu-one">
                              </tbody>
                        </table>
                     </div>
                   ';
     return $nonscheduled;
} 

function getScheduledStaffTeamHTML($teamlists,$additionalrolelist,$usertype){
  
  $activeClass= '';
  $tablename = 'js_scheduledstaffteamlist';
  $columname = 'Rota';
   
  
  $pageid = 11;
    // Call User Permission function.
    $permissions = getUserRolePermissions($pageid);
    if ($permissions->cancreate == 0 ) {
        $activeClass = 'activeclass';
    }
    
 $nonscheduled= '';
 $nonscheduled .= '<script src="js/userStaffTab.js"></script>
 
 <input type="hidden"  name = "js_usertype" value="'.$usertype .'" id="js_usertype">
 <input type="hidden"  name = "js_tablename" value="'.$tablename .'" id="js_tablename">
 <input type="hidden"  name = "js_create" value="" id="js_create">
 <input type="hidden"  name = "js_sysadmin" value="'.$_SESSION['user']['SysAdmin']  .'" id="js_sysadmin">                 
                       <div class="fieldsection">
                              <div class="parant_div_1" id="parant_div_1">
                               <div class="child_div_1 no-border additonaltemtd">';
                               
                                      $nonscheduled .='<form class="stfform">            
                                 <div class="fields">
                                  <label for="hometeam" >Scheduling Team:</label>
                                  <select name="hometeam" class="division-seclect" id="js_schedulled_teamdropdown">
                                   <option value="">Select The Team </option>';
                                   foreach($teamlists as $teamdata){
                                      $nonscheduled .= '<option data-divisionid = '.$teamdata['DivisionId'].' value="'.$teamdata['TeamID'].'">'.$teamdata['TeamName'].'</option>';
                                   }
                                   
                                 $nonscheduled.=' </select>
                                 </div>
                                </form>

                               </div>
                             
                        </div>
                      </div>

                     <div class="scheduledNonSchstaffteamlist border-0 height-98">
                        <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="'.$tablename.'"
                        style="width: 100%;" role="grid" aria-describedby="joblisting_info">
                              <thead>
                              <tr><th class="cellWmmrequested">Full Name</th>
                                   <th class="cellWmmrequested">Staff Number</th>
                                   <th class="cellWmmrequested">Network ID</th>
                                   <th class="mWH60">'.$columname.'</th>
                                   <th class="mWH60">Default</th>';

                                   foreach ($additionalrolelist as $roledata) {
                                       $nonscheduled .= '<th class="mWH90">' . $roledata['RoleName'] . '</th>';
                                   }
                                   $nonscheduled .= '<th class="mWH60">Home&nbsp;</th><th class="mWH90">Info/History</th>';
                                  
                                   
                               $nonscheduled.='</tr>
                              </thead>
                              <tbody class="context-menu-one">
                              </tbody>
                        </table>
                     </div>
                   ';
     return $nonscheduled;
} 
}