<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$intDefaultDepartment = GetDefaultTeamByLogin($strUser);

  // ###################################################################### Get the departments this user can administer.....
$arrDepartments = GetAllTeams();

echo '<div class="tableheadersmall medtextboldcentre" style="width:99%; position:relative">';
echo '<br>Departments<br>You can match the Sort Codes for Staff and the items following the pipe charavter in Allocations with a description here.<br><br>';
echo '</div>';

echo '<table class="tablesmall" width="100%">';
echo '<tr>';
echo '<td valign="top" width="600px">';

echo '<div class="tableheadersmall medtextboldcentre" style="width:400px; position:relative">';
echo '<br>Departments<br><br>';
echo '<div class="DutyCellBottomRight"><img border="0" src="images/add.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:NewEditSortCodeType(0)";></div>';
echo '</div>';
echo '<table width="400px" id="SortCodeslist" class="tablesmalltidy">';
echo '<tr>';
echo '<th>';
echo 'Description';
echo '</th>';
echo '</tr>';


foreach ($arrDepartments as $intDeptID => $strDepartmentName) {
  echo '<tr class="handcursor" onclick="javascript:FillSortCodeHolder('.$intDeptID.')";>';
  echo '<td>';
  echo $strDepartmentName;
  echo '</td>';
  echo '</tr>';
}
echo '</tbody>';
echo '</table>';

echo '<td valign="top">';
echo '<div id="deptscdetails">';
echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
echo '<br>Choose a Department from the list<br><br>';
echo '</div>';
echo '</div>';
echo '</td>';

echo '</tr>';
echo '</table>';




?>

<script type="text/javascript">

function FillSortCodeHolder (id) {
  $.post("page-includes/admin/system-admin-sortcodes-by-type.php", {
    id: id
  },
    function(data,status){
      $('#deptscdetails').html(data);
    }
  )
}

function NewEditSortCode (id, departmentid) {
  $.post("page-includes/admin/system-admin-sortcode-edit.php", {
    id: id,
    departmentid: departmentid
  },
  function(data,status){
	  $.facebox(data);
  })
}


</script>
