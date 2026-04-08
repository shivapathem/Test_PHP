<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/testaccess.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/shiftleaderfunctions.php';
$pageid = 12;
$activeclass = $disabled  = 'noclass';
$permissions = getUserRolePermissions($pageid);
$user_id = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$role_id = $permissions->userhighrole;


if ($permissions->canmodify == 0) {
    $activeclass = 'activeclass';
    $disabled = 'notclickable';
}
if ($permissions->cancreate == 0) {
    $activeclass = 'activeclass';
}

$rsShiftLeaderTypes =  json_decode(getAllShiftLeaders($user_id, $role_id),true);

foreach($rsShiftLeaderTypes as $row){
  $arrShiftLeaderTypes[$row['ID']]['Description'] = $row['description'];
  $arrShiftLeaderTypes[$row['ID']]['Active'] = $row['Active'];
  $arrShiftLeaderTypes[$row['ID']]['schedulingTeamName'] = $row['schedulingTeamName'];
  $arrShiftLeaderTypes[$row['ID']]['Telephone'] = $row['telephone'];
  $arrShiftLeaderTypes[$row['ID']]['BackColour'] = $row['BackColour'];
  $arrShiftLeaderTypes[$row['ID']]['DepartmentID'] = $row['schedulingTeamId'];
  $arrShiftLeaderTypes[$row['ID']]['AppliesTo'][$row['shedulingTeamAppliesToID']] = $row['schedulingTeamAppliesToName'];
}



echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h2 aria-label="Shift Leaders">Shift Leaders</h2><br>.<br>Shift Leader Functions can be applied to an individual Scheduling Team.<br>In addition they can be shown on other Scheduling Team Daily View<br><br>';
echo '<div class="DutyCellBottomLeft'.$activeclass.' " onclick="javascript:EditShiftLeader(0)";>';
echo '<table>';
echo '<tr>';
echo '<td class='.$activeclass.' align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
echo '<td><img border="0" src="images/button_add.png" width="30px" height="30px"></img></td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '</div>';
echo '<table class="tablesmall compact stripe" id="shiftleadertable">';
echo '<thead>';
echo '<tr>';
echo '<th>Description</th><th>Belongs To</th>';
echo '<th>Colour</th>';
echo '<th>Default Contact Number</th>';
echo '<th>Other Scheduling Team which can see these ShiftLeaders</th>';
echo '<th>Delete/Restore</th>';
echo '</tr>';
echo '</thead>';
  
  echo '<tbody>';
foreach ($arrShiftLeaderTypes as $intID => $arrShiftLeaderType) {
    echo '<tr class="handcursor" ondblclick="javascript:EditShiftLeader('.$intID.');">';
    echo '<td>';
    echo $arrShiftLeaderType['Description'];
    echo '</td>';

    echo '<td>';
    echo $arrShiftLeaderType['schedulingTeamName'];
    echo '</td>';
    echo '<td bgcolor="'.$arrShiftLeaderType['BackColour'].'">';
    echo '</td>';

    echo '<td>';
    echo $arrShiftLeaderType['Telephone'];
    echo '</td>';
    echo '<td>';
    foreach ($arrShiftLeaderType['AppliesTo'] as $intShedulingTeamAppliesToID => $strShedulingTeamAppliesToID) {
      echo $strShedulingTeamAppliesToID.'<br>';
    }
    echo '</td>';
    echo '<td align="center">';
    if ($arrShiftLeaderType['Active'] == 1) {
      echo '<img border="0" src="../images/delete.png" width="12px" height="12px" onclick="javascript:DeleteShiftLeader('.$intID.');">';    
    } 
    else {
      echo '<img border="0" src="../images/menu/restore.png" width="12px" height="12px" onclick="javascript:RestoreShiftLeader('.$intID.');">';  
    }
    
    
    
    echo '</td>';  
    echo '</tr>';
  }
echo '</tbody>';  
echo '</table>'; 

?>

<div id="dialog-shiftleader-delete" title="Question!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to de-activate this Shift Leader Option?</span></p>
</div>
<div id="dialog-shiftleader-restore" title="Question!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to re-activeate this Shift Leader Option?</span></p>
</div>


<script type="text/javascript">

$(document).ready( function () {
  var gueststable = $("#shiftleadertable").DataTable({
      "pageLength": 10,
      "bPaginate": false, //hide pagination
      "bFilter": false,   //hide Search bar
      "bInfo": true,     // hide showing entries
      "lengthChange": false,
      "fnDrawCallback": function (oSettings) {
          if ($('#shiftleadertable tr').length >= 20) {
              $(".dataTables_paginate").css('visibility', 'visible');
              $(".dataTables_length").css("display", "none");
              $("#dataTables_paginate ").css('font-size', '8px');
              $(".dataTables_info").css('display', 'block');
          } else {
              $(".dataTables_info").css('display', 'none');
          }
      }
    });

})     

function DeleteShiftLeader(id) {
  $( "#dialog-shiftleader-delete" ).dialog(
    {
      width:400,
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
            $.post("page-includes/admin/system-admin-shiftleaders-toggle.php", {
              id: id
            },
            function(data,status){
              ShowShiftLeaders();
            });
        },
        "No": function() {
          $( this ).dialog( "close" );
        },
      }
    }
  );
}

function RestoreShiftLeader(id) {
  $( "#dialog-shiftleader-restore" ).dialog(
    {
      width:400,
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
            $.post("page-includes/admin/system-admin-shiftleaders-toggle.php", {
              id: id
            },
            function(data,status){
              ShowShiftLeaders();
            });
        },
        "No": function() {
          $( this ).dialog( "close" );
        },
      }
    }
  );
}
 
function EditShiftLeader (id) {
  $.post("page-includes/admin/system-admin-shiftleaders-newedit.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
}




</script>