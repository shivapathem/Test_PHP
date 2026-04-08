<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/shiftleaderfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';

$intID = $_POST['id'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userID= isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$teams = getUserAllTeamsLists();
$allteam =json_decode($teams,true);
 
$strDescription 		= '';
$strTelephone 			= '';
$strBackColour 			= '';
$intSchedulingTeamId 	= 0;
 
if (isset($_POST['submit']))  {

  $strDescription = $_POST['description'] ?? 'No Description';
  $strTelephone = $_POST['telephone'] ?? '';
  $strBackColour = $_POST['BackColour'] ?? '';
    $intSchedulingTeamId = $_POST['schedulingTeamId'] ?? 0;
    if ($intID == 0) {
    $data =array('id' => 0,
                'backcolor'=> $strBackColour,
                'telephone' => $strTelephone,
                'description' => $strDescription,
                'schedulingteamid' => $intSchedulingTeamId);
    addUpdateShiftleader($data);
  }
  else {
    $data =array('id' => $intID,
                'backcolor'=> $strBackColour,
                'telephone' => $strTelephone,
                'description' => $strDescription,
                'schedulingteamid' => $intSchedulingTeamId); 
    addUpdateShiftleader($data);
  }
  
 
}
else { 
 
if ($intID != 0) {
  
  $leader = getShiftLeaderById($intID);
  $shiftleaderdata = json_decode($leader,true);
  $arrLeaderType = GetLeaderTypeInfoByID ($intID);
  $strDescription = $shiftleaderdata['description'] ?? '';
  $strSchedulingTeamName = $shiftleaderdata['schedulingTeamName'] ?? '' ;
  $strTelephone = $shiftleaderdata['Telephone'] ?? '';
  $strBackColour = $shiftleaderdata['BackColour'] ?? '';  
}

  echo '<form id="editshiftleader">';
  echo '<table class="redtable" width="600px">';
  echo '<tr height="40px">';
  if ($intID == 0) {
    echo '<th colspan="2">New Shiftleader</th>';
  }
  else {
    echo '<th colspan="2">Edit Shiftleader '.$strDescription.' belonging to '.$strSchedulingTeamName.'</th>';
  }
  echo '</tr>';    
  
  if ($intID == 0) {
    echo '<tr>';
    echo '<td>Belongs To</td>';
    echo '<td>';
    echo '<select class="chosen-select" name="schedulingTeamId">';
    foreach ($allteam as $intDepID => $team) {
      echo '<option value="'.$team['TeamID'].'">'.$team['TeamName'].'</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';  
  }
  
  echo '<tr>';
  echo '<td>Description</td>';
  echo '<td><input id="description" name="description" type="text" size="40" value="'.$strDescription.'" /></td>';
  echo '</tr>';  
  
  echo '<tr>';
  echo '<td>Default Contact</td>';
  echo '<td><input id="telephone" name="telephone" type="text" size="40" value="'.$strTelephone.'" /></td>';
  echo '</tr>';  
  
  echo '<tr>';
  echo '<td>Back Colour</td>';
  echo '<td><input type="text" name="BackColour" id="BackColour"  value="'.$strBackColour.'" /></td>';
  echo '</tr>';
  
  echo '<tr height="40px">';
  echo '<td colspan="2">';
  echo '<div class="table-scrollBar" id="shiftleaderdepartments">';
  
  echo '</div>';  
  echo '</td>';
  echo '</tr>';   
  
  echo '<tr>';
  echo '<td colspan="2" align="center"><input name="submit" type="submit" value="Submit">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="Cancel()"></input></td>';
  echo '</tr>';   
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '</form>';      
   

?>
<script type="text/javascript">
$('document').ready(function(){
    $('#editshiftleader').validate({
      rules:{
        "description":{
          required:true,
          //date: true
        },
      },
      errorElement: "div",
      errorPlacement: function(error, element) {
      error.insertAfter(element);
      },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/system-admin-shiftleaders-newedit.php', data:$('#editshiftleader').serialize(), success: function(data) {
            $.facebox.close();
            ShowShiftLeaders();
          }});
        }
  })
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "350px"
  });
  $("#BackColour").spectrum({
    showPaletteOnly: true,
    togglePaletteOnly: false,
    hideAfterPaletteSelect:true,
    //color: 'red',
    preferredFormat: "hex",
    palette: [
        ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
        ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
        ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
        ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
        ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
        ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
        ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
        ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
    ]
  });
LoadDepartments(<?php echo $intID?>);
})

function LoadDepartments (id) {
  $.post("page-includes/admin/system-admin-shiftleaders-newedit-departments.php", {
    id: id
  },
  function(data,status){
    $('#shiftleaderdepartments').html(data);
    }
  )
}
function Cancel () {
    $.facebox.close();
    ShowShiftLeaders();
}
</script>

<?php
}
die;
  echo '<table class="tablesmall compact stripe" id="shiftleadertable">';

  echo '<tr>';
  echo '<th>';
  echo 'Description';
  echo '</th>';
  echo '<th>';
  echo 'Belongs To';
  echo '</th>';
  echo '<th>';
  echo 'Default Contact Number';
  echo '</th>';
  echo '<th>';
  echo 'Other Departments which can see these ShiftLeaders';
  echo '</th>';
  echo '<th>';
  echo 'Delete';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  
  echo '<tbody>';
foreach ($arrShiftLeaderTypes as $intID => $arrShiftLeaderType) {
    echo '<tr class="handcursor" ondblclick="javascript:EditShiftLeader('.$intID.');">';
    echo '<td>';
    echo $arrShiftLeaderType['Description'];
    echo '</td>';
    echo '<td>';
    echo $arrShiftLeaderType['DepartmentName'];
    echo '</td>';
    echo '<td>';
    echo $arrShiftLeaderType['Telephone'];
    echo '</td>';
    echo '<td>';
    foreach ($arrShiftLeaderType['AppliesTo'] as $intDepartmetAppliesToID => $strDepartmetAppliesTo) {
      echo $strDepartmetAppliesTo.'<br>';
    }
    echo '</td>';
    echo '<td align="center">';
    echo '<img border="0" src="../images/delete.png" width="12px" height="12px" onclick="javascript:DeleteShiftLeader('.$intID.');">';
    echo '</td>';  
    echo '</tr>';
  }
echo '</tbody>';  
echo '</table>'; 

?>

<div id="dialog-shiftleader-delete" title="Question!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this Shift Leader Option?</span></p>
</div>



<script type="text/javascript">

$(document).ready( function () {
  var gueststable = $("#shiftleadertable").DataTable({
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

function DeleteShiftLeader(id) {
  $( "#dialog-shiftleader-delete" ).dialog(
    {
      width:400,
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
            $.post("page-includes/admin/shift-leader-delete.php", {
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
  $.post("page-includes/admin/shiftleaders-edit.php", {
    login: LogIn
  },
  function(data,status){
	  $.facebox(data);
  })
}




</script>