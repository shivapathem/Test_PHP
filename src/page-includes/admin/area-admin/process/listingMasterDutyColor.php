<?php
//Listing of Divisions
include_once '../../../../function-includes/testaccess.php';
include_once '../process/classColorMaster.php';
include_once '../../../../class-includes/userRolePermissions.php';

//call the class object
$colorMasterObj = new classColorMaster;
//defien the variable
$pageid = 16;
$masterDutyColorLists = json_decode($colorMasterObj->getColorsList(), true);
// Call User Permission function.
$disabled = '';
$permissions = getUserRolePermissions($pageid);
if ($permissions->canmodify == 1) {
    $disabled = 'notclickable';
}
?>
<?php
$addlistview = '';
foreach ($masterDutyColorLists as $masterDutyColor) {
    $addlistview .='<tr>';
                   		$addlistview .='<td id="colorname_'.$masterDutyColor['MasterDutyColourID'].'" title="'.trim($masterDutyColor['ColourName']).'">'.trim(substr($masterDutyColor['ColourName'], 0, 90)).'</td>';
                        $addlistview .='<td id="divisionname_'.$masterDutyColor['MasterDutyColourID'].'" title ="'.trim($masterDutyColor['DivisionName']).'">'.trim(substr($masterDutyColor['DivisionName'], 0, 30)).'</td>';
                        $addlistview .='<td id="notes_'.$masterDutyColor['MasterDutyColourID'].'" title="'.trim($masterDutyColor['ColourNotes']).'">'.trim(substr($masterDutyColor['ColourNotes'], 0, 90)).'</td>';
                        $addlistview .='<td id="bgcolor_'.$masterDutyColor['MasterDutyColourID'].'" title="'.trim($masterDutyColor['ColourBackground']).'">'.trim($masterDutyColor['ColourBackground']).'</td>';
                        $addlistview .='<td id="fontcolor_'.$masterDutyColor['MasterDutyColourID'].'" title="'.trim($masterDutyColor['ColourFont']).'">'.trim($masterDutyColor['ColourFont']).'</td>';
                        $addlistview .='<td>';
                        	if($masterDutyColor['IsDefaultColour'] == 1){
                        		$addlistview .='<input class="isactive_'.$masterDutyColor['MasterDutyColourID'].' '.$disabled.'"
                                   type="checkbox" checked="checked"  value= '. $masterDutyColor['IsDefaultColour'].' id="checkbox-btn" data-defaultcolour= "'.$masterDutyColor['MasterDutyColourID'].'-'.$masterDutyColor['DivisionID'].'">';
                        	} else {
                        		$addlistview .='<input class="isactive_'.$masterDutyColor['MasterDutyColourID'].' '.$disabled.'"
                                   type="checkbox"  value= '. $masterDutyColor['IsDefaultColour'].'

                                   id="checkbox-btn" data-defaultcolour= "'.$masterDutyColor['MasterDutyColourID'].'-'.$masterDutyColor['DivisionID'].'">';
                        	}
                        $addlistview .='</td>';
                        $addlistview .='<td>';
                            $addlistview .='<span>';
                                $addlistview .='<a title="Edit" id="editcolour" class="editaction  '.$activeclass.'" data-editcolourId= '.$masterDutyColor['MasterDutyColourID'].' href="javascript:void(0);" style="text-decoration:none;">';
                                    $addlistview .='<i class="fa fa-edit"></i>';
                                $addlistview .='</a>&nbsp;&nbsp;&nbsp;';
                                if($masterDutyColor['IsActive'] == 1){
                                    $addlistview .='<a title="Active" id="colorstatus"  data-colorId= '.$masterDutyColor['MasterDutyColourID'].'  href="javascript:void(0);">
                                        <img src="../../../../images/green_tick.png" class="tick" style="margin: -12px 0 0 20px;">';
                                    $addlistview .='</a>';
                                } else {
                                    $addlistview .='<a title="Inactive" id="colorstatus"  data-colorId= '.$masterDutyColor['MasterDutyColourID'].'  href="javascript:void(0);">
                                        <img src="../../../../images/red_cross.png" class="tick" style="margin: -12px 0 0 20px;">';
                                    $addlistview .='</a>';
                                }
                            $addlistview .='</span>';
                        $addlistview .='</td>';
                    $addlistview .='</tr>';
}
$response_array = array('status' => 'success', 'view' => $addlistview);
header('Content-type: application/json');
echo json_encode($response_array);