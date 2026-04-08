<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  // ###################################################################### Get the departments this user can administer.....
$arrBreakTypes = GetBreaksTypes();

echo '<div class="tableheadersmall medtextboldcentre" style="width:99%; position:relative">';
echo '<br>Scheduling Team Breaks Table<br><br></div>';

echo '<table class="tablesmall" width="100%">';
echo '<tr>';
echo '<td valign="top" width="600px">';

echo '<div class="tableheadersmall medtextboldcentre" style="width:400px; position:relative">';
echo '<br>Breaks Types<br><br>';
echo '<div class="DutyCellBottomRight"><img border="0" src="images/add.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:NewEditBreakType(0)";></div></div>';
echo '<table width="400px" id="breakslist" class="tablesmalltidy">';
echo '<tr>';
echo '<th>';
echo '<b>Description</b>';
echo '</th>';
echo '<th>';
echo '<b>Default Scheduling Team</b>';
echo '</th>';
echo '</tr>';


foreach ($arrBreakTypes as $intBreakID => $arrBreakType) {
  echo '<tr class="handcursor" onclick="javascript:FillBreaksHolder('.$intBreakID.')"; ondblclick="javascript:NewEditBreakType('.$intBreakID.')";>';
  echo '<td>';
  echo $arrBreakType['Description'];
  echo '</td><td>';
  echo $arrBreakType['TeamName'];
  echo '</td></tr>';
}
echo '</tbody>';
echo '</table>';

echo '<td valign="top">';
echo '<div id="deptdetails">';
echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
echo '<br>Choose a duty from the list<br><br>';
echo '</div></div>';
echo '</td>';

echo '</tr>';
echo '</table>';




?>

<script type="text/javascript">

function FillBreaksHolder (typeid) {
  $.post("page-includes/admin/system-admin-breaks-by-type.php", {
    typeid: typeid
  },
    function(data,status){
      $('#deptdetails').html(data);
    }
  )
}

function NewEditBreak (id, typeid) {
  $.post("page-includes/admin/system-admin-break-edit.php", {
    id: id,
    typeid: typeid
  },
  function(data,status){
	  $.facebox(data);
  })
}

function NewEditBreakType (typeid) {
  $.post("page-includes/admin/system-admin-break-type-edit.php", {
    typeid: typeid
  },
  function(data,status){
	  $.facebox(data);
  })
}

</script>
