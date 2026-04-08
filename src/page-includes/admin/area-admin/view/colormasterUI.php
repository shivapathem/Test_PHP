<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//Scheduled Person 
include_once '../../../../function-includes/testaccess.php';
include_once '../process/classColorMaster.php';
include_once '../../../../class-includes/userRolePermissions.php';

//call the class object
$colorMasterObj = new classColorMaster;
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
//defien the variable
$pageid = 16;
$masterDutyColorLists = json_decode($colorMasterObj->getColorsList(),true);
$userDivisionsList = $colorMasterObj->getUserDivisions();

if (($intSysAdmin) || !empty($userDivisionsList)) {
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
<script src="js/areaAdmin.js?v=<?php echo time(); ?>"></script>
<div id="searchScheduleperson">
    <div class="Searchcontainer p-0">
        <div class="fieldsection p-5">
            <div class="parant_div_1 " id="parant_div_1">
                <h1 class="headertextallpages main-heading pa-20">Master Duty Colours</h1>
                <div class="child_div_1 no-border " id="adddivision">
                    <div class="box-btn">
                        <button class="btn-class addbtn division-add-btn   <?php echo $activeclass ?>">
                            <img src="images/button_add.png" alt="">&nbsp;Add New
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <section>
            <div class="tables table-box">
                <div class="scrollable h-100">
                    <table class=" oddevenclass tablesmall stripe  dataTable no-footer w-100" id="divisionlisting"
                           role="grid" aria-describedby="divisionlisting_info">
                        <thead>
                            <th>Colour Name</th>
                            <th>Area Name</th>
                            <th>Notes</th>
                            <th>Background Colour</th>
                            <th>Font Colour</th>
                            <th>Default</th>
                            <th>Actions</th>
                        </thead>
                        <tbody class="context-menu-one" id="">
                        <?php if (!empty($masterDutyColorLists)) {
                            foreach ($masterDutyColorLists as $masterDutyColor) { ?>
                                <tr>
                                    <td id="colorname_<?php echo $masterDutyColor['MasterDutyColourID']; ?>" title="<?php echo trim($masterDutyColor['ColourName']) ?>"><?php echo trim(substr($masterDutyColor['ColourName'], 0, 90)) ?></td>
                                    <td id="divisionname_<?php echo  $masterDutyColor['MasterDutyColourID']; ?>" title ="<?php echo trim($masterDutyColor['DivisionName']) ?>"><?php echo  trim(substr($masterDutyColor['DivisionName'], 0, 30)) ?></td>
                                    <td id="notes_<?php echo $masterDutyColor['MasterDutyColourID']; ?>" title="<?php echo trim($masterDutyColor['ColourNotes']) ?>"><?php echo trim(substr($masterDutyColor['ColourNotes'], 0, 90)) ?></td>
                                    <td id="bgcolor_<?php echo $masterDutyColor['MasterDutyColourID']; ?>" title="<?php echo trim($masterDutyColor['ColourBackground']) ?>"><?php echo trim($masterDutyColor['ColourBackground']) ?></td>
                                    <td id="fontcolor_<?php echo $masterDutyColor['MasterDutyColourID']; ?>" title="<?php echo trim($masterDutyColor['ColourFont']) ?>"><?php echo trim($masterDutyColor['ColourFont']) ?></td>
                                    <td data-order="<?php if($masterDutyColor['IsDefaultColour'] == 1){ echo '1'; } else { echo '0'; }?>">
                                        <input class="isactive_<?php echo $masterDutyColor['MasterDutyColourID'];?>  <?php echo $disabled; ?> <?php echo 'division-'.$masterDutyColor['DivisionID']; ?> <?php echo 'color-'.$masterDutyColor['MasterDutyColourID']; ?> colorstatus"
                                               type="checkbox"  value= <?php echo  $masterDutyColor['IsDefaultColour'] ?> <?php echo $isactive =  $masterDutyColor['IsDefaultColour'] == 1 ? 'checked' : '' ?>
                                               id="checkbox-btn" data-defaultcolour= "<?php echo $masterDutyColor['MasterDutyColourID'].'-'.$masterDutyColor['DivisionID']; ?>">
                                    </td>
                                    <td data-order="<?php if($masterDutyColor['IsActive'] == 1){ echo '1'; } else { echo '0'; }?>">
                                        <span>
                                            <a title="Edit" id="editcolour" class="editaction  <?php echo $activeclass; ?>" data-editcolourId= <?php echo $masterDutyColor['MasterDutyColourID']; ?> href="javascript:void(0);" style="text-decoration:none;">
                                                <i class="fa fa-edit"></i>
                                            </a>&nbsp;&nbsp;&nbsp;
                                            <span id="statusSpan-<?php echo $masterDutyColor['MasterDutyColourID']?>">
                                            <?php if($masterDutyColor['IsActive'] == 1){?>
                                                <a title="Active" href="javascript:void(0);" onclick="setColourStatus(<?php echo $masterDutyColor['MasterDutyColourID']?>,<?php echo $masterDutyColor['IsActive']; ?>);">
                                                    <img src="../../../../images/green_tick.png" class="tick" style="margin: -12px 0 0 20px;" alt="Active">
                                                </a>
                                            <?php } else {?>
                                                <a title="Inactive" href="javascript:void(0);">
                                                    <img src="../../../../images/red_cross.png" class="tick" style="margin: -12px 0 0 20px;" onclick="setColourStatus(<?php echo $masterDutyColor['MasterDutyColourID']?>,<?php echo $masterDutyColor['IsActive']; ?>);" alt="Inactive">
                                                </a>
                                            <?php }?>
                                            
                                            </span>
                                        </span>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td class="filter_data_unavailabl"></td>
                                <td class="filter_data_unavailabl"></td>
                                <td class="filter_data_unavailabl"></td>
                                <td class="filter_data_unavailabl">No records Found.</td>
                                <td class="filter_data_unavailabl"></td>
                                <td class="filter_data_unavailabl"></td>
                                <td class="filter_data_unavailabl"></td>
                            </tr>
                        <?php }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div id="myModal" class="modal" style="display: none;">
            <div class="modal-content w-50"><span class="closebox">×</span>
                <div>
                    <?php
                        $colourId = $colourId ?? '';
                    ?>
                    <form id="neweditmastercolour" method="post" onSubmit="return false;" >
                        <table id="rotanewedittable" class="smalltable bluetable">
                            <thead>
                            <tr>
                                <th colspan="5" id="title"></th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Select Area<span
                                                class="required">*</span> </label></td>
                                <td width="35%">
                                    <select name="DivisionID" id="DivisionID" class="chosen-select">
                                        <option value="">Select Area</option>
                                        <?php foreach($userDivisionsList as $divisions){?>
                                            <option value="<?php echo $divisions['DivisionID']?>"><?php echo $divisions['DivisionName']?></option>
                                        <?php }?>
                                    </select>
                                    <span class="messageerror" id="divsisonerror"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Colour Name <span
                                                class="required">*</span></label></td>
                                <td width="35%">
                                    <input id="ColourName" name="ColourName" type="text" size="40" value=""><br/>
                                    <span class="messageerror" id="colournameerror"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Notes  <span
                                                class="required">*</span></label></td>
                                <td width="35%">
                                    <textarea id="ColourNotes" name="ColourNotes" cols="45" rows="10" type="text"
                                              size="500"></textarea><br/>
                                    <span class="messageerror" id="colournoteserror"></span>
                                </td>
                            </tr>

                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Background Colour</label></td>
                                <td width="35%">
                                    <input id="jobbackcolor" name="ColourBackground" type="text" size="10" value="">
                                </td>
                            </tr>
                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Font Colour</label></td>
                                <td width="35%">
                                    <input id="jobfontcolor" name="ColourFont" type="text" size="10" value="">
                                </td>
                            </tr>
                            <tr>
                                <td width="15%"></td>
                                <td width="35%">
                                    <input name="js_saveColour" id="js_saveColour" type="submit" value="Save">
                                    <input name="js_colourid" id="js_colourid" type="hidden"
                                           value="<?php echo $colourId; ?>">
                                    <input name="js_actionbutton" id="js_actionbutton" type="hidden" value="">
                                    <input name="js_isDefault" id="js_isDefault" type="hidden" value="">
                                    <input name="js_isActive" id="js_isActive" type="hidden" value="">
                                 </td>
                            </tr>

                            </tbody>
                        </table>
                    </form>
                </div>
                
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){ 
    colorPickerDropdown('#jobbackcolor', '#ffffff');
    colorPickerDropdown('#jobfontcolor', '#000000');   
    $('#jobbackcolor').val('#ffffff');
    $('#jobfontcolor').val('#000000');  
});
</script>
<?php
} else {
    echo "Access Denied"; die;
}
?>
