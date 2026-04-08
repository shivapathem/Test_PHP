<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//Listing of Divisions
include_once '../../../../function-includes/testaccess.php';
include_once '../process/classDivisions.php';
include_once '../../../../class-includes/userRolePermissions.php';
//call the class object
$divisionobj = new ClassDivisions;
//defien the variable
$pageid = 7;
$divisionLists = json_decode($divisionobj->getDivisionsList(), true);
// Call User Permission function.
$disabled = '';
$permissions = getUserRolePermissions($pageid);
if ($permissions->canmodify == 0) {
    $disabled = 'notclickable';
}
$addlistview = '';
foreach ($divisionLists as $division) {
    $isactive = $division['isActive'] == 1 ? "checked" : "";
    $notes = $division['Notes'] ?? '';
    $effectedFrom = $division['EffectedFrom'] ?? '';
    $addlistview .= '<tr>';
    $addlistview .= '<td id="divisionname_' . $division['DivisionID'] . '">' . ($division['DivisionName'] ?? '') . '</td>';
    $addlistview .= '<td id="notes_' . $division['DivisionID'] . '">' . trim(substr($notes, 0, 20)) . '</td>';
    $addlistview .= '<td><span class="customdateSort">' . ($effectedFrom ? date("Y-m-d", strtotime($effectedFrom)) : '') . '</span>' . ($effectedFrom ? date("d-m-Y", strtotime($effectedFrom)) : '') . '</td>';
    $addlistview .= '<td> <input class="isactive' . $division['DivisionID'] . $disabled . '" type="checkbox" ' .  $isactive . ' value= "' . $division['isActive'] . '" id="checkbox-btn" data-activedivisionId = ' . $division['DivisionID'] . '> </td>';
    $addlistview .= '<td><span><a  id="editdivision" data-editdivisionId = ' . $division['DivisionID'] . '  href="javascript:void(0)" title="Edit"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a data-historydivisionid = ' . $division['DivisionID'] . ' id="historydivision" href="javascript:void(0);"><i class="fa fa-hourglass-3"></i></a></span></td>';
    $addlistview .= ' </tr>';
}
$response_array = array('status' => 'success', 'view' => $addlistview);
header('Content-type: application/json');
echo json_encode($response_array);
