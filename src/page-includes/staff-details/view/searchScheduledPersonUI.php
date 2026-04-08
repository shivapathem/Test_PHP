<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//Scheduled Person
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/testaccess.php';
include_once '../process/classScheduledPerson.php';
include_once '../../../class-includes/userRolePermissions.php';

$teamid = isset($_REQUEST['searchteamid']) ? $_REQUEST['searchteamid'] : 0;
$userid = isset($_REQUEST['searchuserid']) ? $_REQUEST['searchuserid'] : '';

//call the class object
$scheduleobj = new classScheduledPerson;
$pageid = 6;
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);
//fetch the user team from userteamrole_link table
$userTeamList = json_decode(GetUserAreaTeamsListByFormId ($sessUserId, 0, $pageid),true);
?>
<script src="js/scheduledPeople.js"></script>
<div id="searchScheduleperson" class="search-container">
    <h1 class="sr-only">Scheduled People</h1>
    <div class="parant_div_1 headertextallpages  main-heading">
        <h2>Search Scheduled Person</h2>
        <?php if ($permissions->cancreate == 1) { ?>
            <button class="btn-class addbtn m-30" id="js_addnewbutton">
                <img src="images/button_add.png" alt="Add Button">&nbsp;Add New
            </button>
        <?php } ?>
    </div>
    <div class="Searchcontainer">
        <div class="fieldsection">
            <div class="parant_div_1" id="parant_div_1">
                <div class='child_div_1 no-border'>
                    <form class="stfform">
                        <div class="fields" id="homescheduledteam">
                            <label for="hometeam"> Home Scheduling Team</label>
                            <select name="hometeam" id="teamdropdown" class="division-seclect">
                                <option value="">Select a Team</option>
                                <?php if(!isset($teamid) && $teamid == ''){?>
                                <?php foreach ($userTeamList as $key => $team) {
                                        $TeamIdOption = $team['TeamID'];
                                    ?>
                                    <option value="<?php echo $TeamIdOption; ?>"><?php echo $team['TeamName']; ?></option>
                                <?php } ?>
                                <?php } else {?>
                                <?php foreach ($userTeamList as $key => $team) {
                                        $TeamIdOption = $team['TeamID'];
                                    ?>
                                    <option value="<?php echo $TeamIdOption; ?>" <?php if($teamid == $TeamIdOption){?> selected="selected" <?php }?>><?php echo $team['TeamName']; ?></option>
                                <?php } ?>
                                <?php }?>
                            </select>
			            </div>
                    </form>
                </div>
                <div class='child_div_2'>
                    <form class="stfform Srcfld" style="display:inline-block;margin-right: 40px;" onSubmit="searchClearFilter('search', teamdropdown.value, dispalyName.value);$('.ui-autocomplete').hide();return false;" >
                        <div class="fields">
                            <label id="pt-37" for="dispName">Display Name</label>
                            <?php if(!isset($userid) && $userid == ''){?>
                            <input autocomplete="off" class="ui-autocomplete-input" type="text" placeholder="Enter Text"
                                   id="dispalyName" name="dispName" value=''>
                            <em class="fa fa-search" id="search"></em>
                            <?php } else {?>
                            <input autocomplete="off" class="ui-autocomplete-input" type="text" placeholder="Enter Text"
                                   id="dispalyName" name="dispName" value='<?php echo $userid?>'>
                            <em class="fa fa-search" id="search"></em>
                            <?php }?>
                        </div>
                    </form>
                    <div class="scheduled-people-filter-container">
                        <div class="fields" style="margin-top: 0px;margin-right: 7.2rem">
                            <label id="pt-38" for="excludeNoTeam" style="margin-top: 0px;">Exclude Archive</label>
                            <input type="checkbox" id="excludeNoTeam" name="excludeNoTeam" value="asd" style="height:10px;margin-top: 0px;" onclick="if(excludeNoTeamVal.value == 0){excludeNoTeamVal.value = 1;}else{excludeNoTeamVal.value = 0;} " checked >
                            <input type="hidden" id="excludeNoTeamVal" name="excludeNoTeamVal" value="1" >
                        </div>
                        <div class="clearFilter" >
                            <form class="stfform Srcfld">
                                <div class="fields" style="margin-top: 0px;">
                                    <div class="filter" id="clearfilterbox">
                                        <p class="primary-para"><span id="clearfilter" style="position:absolute"><img src="images/red_cross.png" alt="Red Cross" width="14px"></span>&nbsp;&nbsp;&nbsp;&nbsp;Clear filter</p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tables">
        <div class="scrollable" id="tableheightsearch">
            <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="scheduledpeoplelist"
                   style="width: 100%;" role="grid" aria-describedby="joblisting_info">
                <thead>
                <th>Display Name</th>
                <th>Forename</th>
                <th>Surname</th>
                <th>Network ID</th>
                <th>BBC Email Address</th>
                <th>Home Scheduling Team</th>
                <th>Actions</th>
                </thead>
                <tbody class="context-menu-one">
                <tr>

                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-record">Please Apply filter to display records.</td>
                    <td></td>
                    <td></td>
                    <td></td>

                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if((isset($teamid) &&  !empty($teamid)) || (isset($userid) && !empty($userid))){ if($teamid == 0 || $teamid == '0') { $teamid = '';}?>
<script type="text/javascript">
searchClearFilter('search','<?php echo $teamid;?>', '<?php echo $userid;?>');
</script>
<?php }?>