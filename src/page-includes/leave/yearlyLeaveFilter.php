<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/leavefunctions.php';
include_once '../users/process/classUserSetup.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../../function-includes/adminfunctions.php';
$commonObj= new classCommonDBFunctions();
$setupObj = new classUserSetup(); 
$adminUser = GetUserLogon();
$arrStaff  = GetStaffInMyAdminGroups($adminUser);
$intLeaveYear = GetCurrentLeaveYearGeneric(); 
/* Yearly Leave Start */
$result['DataInput']=readYearlyFilter();

$keys=[];
$values=[];
$formdataArr=[];

if (isset($result['DataInput']) && !empty($result['DataInput'])) {
  parse_str($result['DataInput'], $formdataArr);
  $keys = array_keys($formdataArr);
  $values = array_values($formdataArr);
}
if (!empty($keys) && in_array('selectedyear', $keys)) {
  $keyno = array_search('selectedyear', $keys);
  if ($values[$keyno] != '') {
    $intLeaveYear = $values[$keyno];
  } else {
    $intLeaveYear = GetCurrentLeaveYearGeneric();
  }
}
if (!empty($keys) && in_array('ChooseUser', $keys)) {
  $keyno = array_search('ChooseUser', $keys);
  $selecteduser = $values[$keyno];
}
?>

  <form name="filterYealyLeave" id="filterYealyLeave" method="post">
  <table class="tablesmallnoborder leaveAdminTable" width="100%"><tbody>
  <tr>
  <th class="verticalMiddle w171">
  <button type="button" class="btn-class addbtn" onclick="addNew();" id="addNewLeave">
  <img src="../../../images/button_add.png" class="add-btn" alt="Add New Leave"><span style="position:relative; top:-6px;">&nbsp;Add New<span></button>
  </th>
  <th style="width:300px;" class="verticalMiddle">
    <label>Filter By Staff: </label>
   <select class="chosen-select" size="1" name="ChooseUser" onchange="ShowStaffYearlyLeave(value)"; id="ChooseUser">
     <option value='0'>Select User</option>
     <?php
    if (isset($arrStaff) && !empty($arrStaff)) {
      foreach ($arrStaff as $strLogin => $arrStafDetails) {
        if ($selecteduser == $strLogin) {
          $selcted="selected";
        } else {
          $selcted=' ';
        }
        if ($arrStafDetails['Admin'] == 0) {
          echo '<option value="'.$strLogin.'" '.$selcted.'>'.$arrStafDetails['Name'].'</option>';
        }
      }
    }
    ?>
    </select>
   </th>
   <th class="verticalMiddle"> Filter : <input type="checkbox" class="handcursor" name="filter_approved" onclick="ApplyAdvanceFilter();" id="filter_approved">Approved &nbsp;&nbsp; <input type="checkbox" class="handcursor" name="filter_pending" onclick="ApplyAdvanceFilter();" id="filter_pending"/>Unapproved &nbsp;&nbsp; <input type="checkbox" class="handcursor" name="filter_agreed" onclick="ApplyAdvanceFilter();" id="filter_agreed"/>Agreed&nbsp;&nbsp; <input type="checkbox" class="handcursor" name="filter_deleted" onclick="ApplyAdvanceFilter();" id="filter_deleted"/>Deleted
   </th>
   <th class="medtextbold leaveNav handcursor" onclick="ShowYearlyLeave(this.id)" id="prevYear">&nbsp;
   </th>
	<th class="medtextbold leaveNav handcursor"> &nbsp;&nbsp; | &nbsp;&nbsp;</th>
  <th class="medtextbold leaveNav handcursor" onclick="ShowYearlyLeave(this.id)" id="nextYear">&nbsp;</th>
  <th>
  <input type="hidden" value="<?php echo $intLeaveYear; ?>" id="selectedyear" name="selectedyear" readonly>
  </th>
  </tr>
  </tbody>
  </table>
  </form>
   