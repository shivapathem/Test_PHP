<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../process/classUserSetup.php';
include_once __DIR__.'/../../../function-includes/common/classCommonDBFunctions.php';

$setupObj = new classUserSetup();
$commonDbobj = new classCommonDBFunctions();
$roleLists = json_decode($setupObj->getAllRoles(), true);
$sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userStaffDetails = json_decode($commonDbobj->getStaffDetailsByID($_SESSION['user']['user']), true);
$arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLogin($sessUserNetLogin, 0),true);
?>
<script src="js/usersetup.js?v=<?php echo time(); ?>"></script>
<div id="searchScheduleperson">
    <h1 class="sr-only">My Setup</h1>
    <div class="Searchcontainer p-0">
        <div class="fieldsection">
            <div class="parant_div_1" id="parant_div_1">
                <p class="text-justify main-heading p-11">Information for <?php echo $userStaffDetails['userDisplayName'] ?>
                    (<?php echo $sessUserNetLogin; ?>)<br>
                    You can set the default scheduling team by clicking on the red cross.<br>
                </p>
                <div class="input-acessbox">
                    <span><input autocomplete="off" class="notclickable   access-input" type="text" id="StaffNumber" readonly name="StaffNumber" value="<?php echo $userStaffDetails['StaffNumber']; ?>"></span>
                    <span><input autocomplete="off" class="notclickable access-input" type="text" id="JobTitle" readonly name="JobTitle" value="<?php echo $userStaffDetails['JobTitle']; ?>"></span>
                </div>
            </div>
        </div>

        <section class="pb-50">
            <div class="tables access-table">
                <div class="scrollable h-318">
                    <h2 class="sr-only">Scheduling Teams</h2>
                    <table class="oddevenclass tablesmall stripe  dataTable no-footer" id="userSetUpLists" style="width: 100%;" role="grid" aria-describedby="joblisting_info">
                        <thead>
                            <th>Scheduling Teams</th>
                            <th>Default&emsp;</th>
                            <th style="white-space: nowrap;">Who's In</th>
                            <?php foreach ($roleLists as $role) { if($role['RoleName'] != 'Smartbook user' && $role['RoleName'] != 'Timesheet Authoriser') { ?>
                            <th><?php echo $role['RoleName']; ?> </th>
                        <?php } } ?>
                        </thead>
                        <tbody class="context-menu-one"></tbody>
                    </table>
                </div>
            </div>
        </section>
                                
        <section>
            <div class="tables access-table sec-acces-table">
                <div class="scrollable">
                    <h2 class="sr-only">Leave & Request Groups</h2>
                    <table class="oddevenclass tablesmall stripe  dataTable no-footer" id="joblisting"
                           style="width: 100%;" role="grid" aria-describedby="joblisting_info">
                        <thead>
                        <th style="width: 225px">Leave & Request Group(s)</th>
                        <th>Administrator</th>
                        </thead>
                        <tbody class="context-menu-one">
                        <?php 
                        if(!empty($arrUserSettings['LeaveRequests'])){
                            foreach ($arrUserSettings['LeaveRequests'] as $intGroupID => $arrLeaveRequest)  { ?>
                        <tr>
                            <td><?php echo $arrLeaveRequest['Description']; ?></td>
                            <?php
                                switch ($arrLeaveRequest['Admin']) {
                                    case 3:
                                    $adminimg= '<img title="You may Leave manage the configuration of this Group" border="0" src="../images/purple_tick.png" width="12px" height="12px">';          
                                    break;
                                    case 2:
                                    $adminimg= '<img title="You may administer the configuration of this Group" border="0" src="../images/green_tick.png" width="12px" height="12px">';    
                                    break;
                                    case 1:
                                    $adminimg= '<img title="You may approve Leave in this Group" border="0" src="../images/yellow_tick.png" width="12px" height="12px">';    
                                    break;
                                    default:
                                    $adminimg= '<img title="You may request Leave in this Group" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
                                    break;
                                }
                            ?>
                            <td class="left-text"><?php echo $adminimg; ?></td>
                        </tr>
                            <?php } 
                        }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
