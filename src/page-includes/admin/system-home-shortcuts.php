<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'] ?? 0;
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
if ($intSysAdmin == 1 || !empty($userDivisionsList)) {

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>Menu \'Home\' Shortcuts<br><br>.<br>Menu Items on the Home menu<br><br>';
echo '<div class="DutyCellBottomLeft" onclick="javascript:EditMenuItem(0)";>';
echo '<table>';
echo '<tr>';
echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
echo '<td>';
echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '</div>';

$arrMenuItems = GetMenuItems(); 
echo '<br>';
  
  
  echo '<table class="tablesmall compact stripe" id="menustable">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Description';
  echo '</th>';
  echo '<th>';
  echo 'URL';
  echo '</th>';
  echo '<th>';
  echo 'Delete';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  
  echo '<tbody>';
  if (isset($arrMenuItems))  {
    foreach ($arrMenuItems as $intID => $arrMenuItem) {
      echo '<tr class="handcursor" ondblclick="javascript:EditMenuItem('.$intID.');">';
      echo '<td>';
      echo $arrMenuItem['Description'];
      echo '</td>';

      echo '<td>';
      echo $arrMenuItem['URL'];
      echo '</td>';

      echo '</td>';
      echo '<td align="center">';
      echo '<img border="0" src="../images/delete.png" width="12px" height="12px" onclick="javascript:DeleteMenuItem('.$intID.');">';    
      echo '</td>';  
      echo '</tr>';
    }
  }
echo '</tbody>';  
echo '</table>'; 
} else {
  echo 'Access Denied'; die;
}
?>

<div id="dialog-menu-delete" title="Question!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this URL?</span></p>
</div>



<script type="text/javascript">

$(document).ready( function () {
  var gueststable = $("#menustable").DataTable({
    paging: false,
    destroy: true,
    scrollY: 400,
    info:     false,
    stateSave: true,
    deferRender: true,
    order: [0],
    autoWidth: false,
  });

})     

function DeleteMenuItem(id) {
  $( "#dialog-menu-delete" ).dialog(
    {
      width:400,
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
            $.post("page-includes/admin/system-admin-menu-delete.php", {
              id: id
            },
            function(data,status){
              FillMenu();
              ShowHomeShortcuts();
            });
        },
        "No": function() {
          $( this ).dialog( "close" );
        },
      }
    }
  );
}
 
function EditMenuItem (id) {
  $.post("page-includes/admin/system-admin-menu-newedit.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
}




</script>