<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//Scheduled Person 
include_once '../../../../function-includes/testaccess.php';
include_once '../process/classDivisions.php';
include_once '../../../../class-includes/userRolePermissions.php';
include_once '../../../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
if (($intSysAdmin) || !empty($userDivisionsList) || ($arrUsersTeamdata['isSchedulingTeamAdmin'])) {

//call the class object
$divisionobj = new ClassDivisions;
//defien the variable
$pageid = 7;
$divisionLists = json_decode($divisionobj->getDivisionsList(), true);
// Call User Permission function.
$activeclass = $disabled  = 'noclass';
$permissions = getUserRolePermissions($pageid);

if ($permissions->canmodify == 0) {
    $activeclass = 'activeclass';
    $disabled = 'notclickable';
}
if ($permissions->cancreate == 0) {
    $activeclass = 'activeclass';
}
?>
<script src="js/divisions.js?v=<?php echo time(); ?>"></script>
<div id="searchScheduleperson">
    <div class="Searchcontainer p-0">
        <div class="fieldsection p-5">
            <div class="parant_div_1 headertextallpages  main-heading" id="parant_div_1">
                    <div class="HeaderTitle"><h2 aria-label="Area">Area</h2></div>
                    <div class="<?php echo $activeclass; ?>">Click on "Add New" to add a new Area</div>
                    <div class="<?php echo $activeclass; ?>">Click on "Edit" icon to edit a Area setup in action column</div>
                    <div>Click on "History" icon to view all history of a Area in action column</div>
                    <button id="adddivision" class="btn-class addbtn division-add-btn   <?php echo $activeclass ?>">
                            <img src="images/button_add.png" alt="">&nbsp;Add New
                        </button>
             </div>
        </div>
        <section>
            <div class="tables ">
                <div class="scrollable h-100">
                    <table class=" oddevenclass tablesmall stripe  dataTable no-footer w-100" id="divisionlisting"
                           role="grid" aria-describedby="divisionlisting_info">
                        <thead>
                        <th>Area Name</th>
                        <th>Notes</th>
                        <th>Effective From</th>
                        <th>Active</th>
                        <th>Actions</th>
                        </thead>
                        <tbody class="context-menu-one" id="">
                        <?php if (!empty($divisionLists)) {
                            foreach ($divisionLists as $division) { 
                                $division['Notes'] = $division['Notes'] ?? '';
                                $division['EffectedFrom'] = $division['EffectedFrom'] ?? '';
                                ?>
                                <tr>
                                    <td id="divisionname_<?php echo $division['DivisionID']; ?>" title ="<?php echo trim($division['DivisionName']) ?>"><?php echo  trim(substr($division['DivisionName'], 0, 30)) ?></td>
                                    <td id="notes_<?php echo $division['DivisionID']; ?>" title="<?php echo trim($division['Notes']) ?>"><?php echo trim(substr($division['Notes'], 0, 90)) ?></td>
                                    <td><span class="customdateSort"><?php echo date("Y-m-d", strtotime($division['EffectedFrom'])); ?></span><?php echo date("d-m-Y", strtotime($division['EffectedFrom'])); ?></td>
                                    <td>
                                        <input class="isactive_<?php echo $division['DivisionID'];?>  <?php echo $disabled; ?>"
                                               type="checkbox"  value= <?php echo $division['isActive'] ?> <?php echo $isactive = $division['isActive'] == 1 ? 'checked' : '' ?>
                                               id="checkbox-btn"
                                               data-activedivisionId= <?php echo $division['DivisionID']; ?>>
                                    </td>
                                    <td><span><a title="Edit" id="editdivision" class="editaction  <?php echo $activeclass; ?>"
                                                    data-editdivisionId= <?php echo $division['DivisionID']; ?> href="javascript:void(0);">
                                                    <em class="fa fa-edit"></em></a>&nbsp;&nbsp;&nbsp;
                                                    <a title="History" id="historydivision"
                                                       data-historydivisionId= <?php echo $division['DivisionID']; ?>  href="javascript:void(0);"><em
                                                                class="fa fa-hourglass-3"></em></a></span></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="filter_data_unavailabl">No records to display.</td>
                            </tr>
                        <?php }
                            } else {
                                echo 'Access Denied'; die;
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div id="myModal" class="modal" style="display: none;">
            <!-- Modal content -->
            <div class="modal-content w-50"><span class="closebox">×</span>
                <!--Model content-->
                <div>

                    <form id="neweditdivison" method="post">
                        <table id="rotanewedittable" class="smalltable bluetable" width="100%">
                            <thead>
                            <tr>
                                <th colspan="5" id="title"></th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Area Name<span
                                                id="Addlabelstartdate" class="required">*</span> </label></td>
                                <td width="35%">
                                    <input id="divisionname" name="divisionname" type="text" size="40" maxlength="50" value=""
                                           data-divisionnameid="9"><br/>
                                    <span class="messageerror" id="divsisonerror">there is error</span>
                                </td>
                            </tr>

                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Notes </label></td>
                                <td width="35%">
                                    <textarea id="notes" name="notes" cols="45" rows="10" type="text"
                                              size="500" maxlength="500" ></textarea>
                                </td>
                            </tr>

                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Effective From </label></td>
                                <td width="35%">
                                    <span id="effectivedate"><?php echo date("d-m-Y"); ?></span>
                                </td>
                            </tr>

                            <tr>
                                <td>Active</td>
                                <td>
                                    <div class="rowsbtns">
                                        <div class="fields-color">
                                            <input id="isactive" name="isactive" style="width:50px" type="checkbox">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td width="15%"></td>
                                <td width="35%">
                                    <input name="js_saveDivision" id="js_saveDivision" type="submit" value="save">
                                    <input name="js_divisionid" id="js_divisionid" type="hidden"
                                           value=<?php echo isset($divisionId) ? $divisionId : 0; ?>>
                                    <input name="js_actionbutton" id="js_actionbutton" type="hidden" value="">
                                 </td>
                            </tr>

                            </tbody>
                        </table>
                    </form>
                </div>
                <!--Model content closes-->
            </div>
        </div>

    </div>
</div>
