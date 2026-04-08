<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

//Alloacte Users UI
include_once '../../../../function-includes/testaccess.php';
include_once '../process/classAllocateUsers.php';
include_once '../../../../class-includes/userRolePermissions.php';
include_once '../../../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
if (($intSysAdmin == 1) || !empty($userDivisionsList) || ($arrUsersTeamdata["isSchedulingTeamAdmin"] == 1) || ($arrUsersTeamdata["isScheduler"] == 1)) {
//defien the variable
$pageid = 9;
// Call User Permission function.
$activeclass = '';
$permissions = getUserRolePermissions($pageid);
//set the permissions
if ($permissions->cancreate == 0) {
    $activeclass = 'activeclass';
}
?>
<script src="js/allocateusers.js?v=<?php echo time(); ?>"></script>
<div id="searchScheduleperson unset-flot" class="widthposition pos-height-relative">
      <div class="Searchcontainer addNew hight100percent pos-height-relative">
        <div class="parant_div_1 hight100percent pos-height-relative">
          <div class="child_div_1 leftChild unset-flot hight100percent pos-height-relative">
            <!--new tabbs-->
            <div id="adminuserstabs" class="pos-height-relative">
              <ul >
                <li><a href="#adminuserstabs-1">Allocate Users</a></li>
                <li><a href="#schPersonTab">Scheduled Staff</a></li>
                <li><a href="#nonSchPersonTab">Non Scheduled Staff</a></li>
                <?php if(($intSysAdmin == 1) || !empty($userDivisionsList)) { ?>
                  <li><a href="#adminuserstabs-4">Area Permissions</a></li>
                <?php } ?>
              </ul>
              <div id="adminuserstabs-1" class="tabcontent pdt-10-LRB-0">
                        <div class="Searchcontainer Pos-Searchcontainer">
                          <div class="tableheadersmall medtextboldcentre screen-fix-withscroll"><br><h1 style="text-align: center;">Allocate Users</h1>
                            <br>Displaying a list of all Allocate Users.<br>
                              <div class="parant_div_1">
                                <div class="child_div_1 no-border <?php echo $activeclass ?>" >
                                      <button class="btn-class addbtn allocate-addbtn" id="js_addallocateuser">
                                        <img src="images/button_add.png" alt="Allocate User">&nbsp;Allocate User
                                      </button>
                                </div>
                              </div>
                          </div>
                          <div class="allocateUsertableH"> 
                                  <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer screen-fix-withscroll width100Percent" id="alocateUsersLists">
                                    <thead>
                                    <tr class="headerSticky">
                                      <th>Full Name</th>
                                      <th>Network ID</th>
                                      <!--th>Forename</th>
                                      <th>Surname</th-->
                                      <th>Email address</th>
                                      <th>Employee Number</th>
                                      <th>Staff Number</th>
                                      <th>Current Home Team</th>
                                      <th>Info</th>
                                    </tr>
                                    </thead>
                                    <tbody class="context-menu-one">
                                    </tbody>
                                  </table>
                          </div>
                        </div>

              </div>
              
              <div id="schPersonTab" class="tabcontent ss" style="padding: 0px 0px; display: none;">
              </div> 
              <div id="nonSchPersonTab" class="tabcontent" style="padding: 0px 0px; display: block;">
              </div>
              <div id="adminuserstabs-4" class="tabcontent tabcontentPos">
              </div>
            </div>
          </div>
        </div>
    </div>
</div>
<?php } else {
  echo 'Access Denied'; die;
}
?>