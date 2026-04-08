<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intSysAdmin = GetIsSysAdmin($strUser);

$intDefaultDepartment = GetDefaultTeamByLogin($strUser);


  // ###################################################################### Get the departments this user can administer.....
$arrAdminDepts = GetAdminDepts($strUser, $intSysAdmin);

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>Department Administration<br><br>Click on a row to Highlight it and then choose an option from the Tabs.<br>Double-Click to edit the  Department\'s default settings.<br><br>';
echo '</div>';

echo '<table class="tablesmall compact stripe" id="departmentslist" min-width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th>';
echo 'Description';
echo '</th>'; 
echo '<th title="The address that Grid Checks emails are sent to, if applicable">';
echo 'Email';
echo '</th>';
echo '<th>';
echo 'Auto Import Weeks';
echo '</th>';
echo '<th title="The number of days to allow sign-in in advance<br>Set to -1 to disable sign-in">';
echo 'Sign-In Days';
echo '</th>';
echo '<th title="Allows staff to indicate that they are in the building from one hour before the start of their shift<br>Requires sign-in to be enabled">';
echo 'Allow In Building';
echo '</th>';
echo '<th title="Allows staff to indicate that they are available for overtime from their Monthly View<br>People with Shiftleader permissions and above can indicate this for someone else<br>Allears on the Daily, Weekly and Monthly views">';
echo 'Allow Overtime Requests';
echo '</th>';
echo '<th title="Uses the Daily View colours to colour the Weekly, Multi-Week and Monthly views">';
echo 'Colour Week';
echo '</th>';
echo '<th title="Controls the start of hashing on the Weekly, Multi-Week and Monthly views<br>Also indicates the start of Locks<br>Set to 999 to disable">';
echo 'Locks Start';
echo '</th>';
echo '<th title="Controls the furthest extent of Locks into the future">';
echo 'Locks End';
echo '</th>';
echo '<th title="Causes the Locks to open one week at a time<br>The Locks-End figure is taken as the minimum number of days">';
echo 'Locks Roll Weeks';
echo '</th>';
//echo '<th>';
//echo 'Locks Per Week';
//echo '</th>';
echo '<th title="Shows only the First Letter of an Allocation where the 3<sup>rd</sup> letter in the Allocation is numeric or a space">';
echo 'Mask After<br>(Show First Letter)';
echo '</th>';
echo '<th title="Non Schedulers disable daily view">';
echo 'Daily View hidden after';
echo '</th>';
echo '<th title="Set the number of days allowed for editing on the Daily View on the website<br>Setting this value to -1 switches off editing">';
echo 'Daily Edit Period';
echo '</th>';
echo '<th title="If editing on the Daily View is allowed<br>this controls the opening time">';
echo 'Daily Edit Start';
echo '</th>'; 
echo '<th title="If editing on the Daily View is allowed<br>this controls the closing time">';
echo 'Daily Edit End';
echo '</th>'; 
//echo '<th>';
//echo 'Daily Auto Weekend';
//echo '</th>';
echo '<th title="Switches on the ability on the Daily View for Shiftleaders to indicate they have completed Grid Checks">';
echo 'Show Grid Checks';
echo '</th>';
echo '<th title="Switches on handovers for Shiftleaders and above">';
echo 'Show Handovers';
echo '</th>';
echo '<th title="Switches on Christmas Points calculations">';
echo 'Christmas Points';
echo '</th>';

echo '<th>';
echo 'History';
echo '</th>';

echo '</tr>'; 
echo '</thead>';
echo '<tbody>';
foreach ($arrAdminDepts as $intDepID => $arrDept) {
  echo '<tr class="handcursor" id='.$intDepID.' ondblclick=\'javascript:EditDepartment('.$intDepID.')\'>';

  echo '<td>'; 
  echo $arrDept["Description"];
  if ($intDepID < 0) {
    echo '<br>[ScheduAll] '.$intDepID;
  }
  echo '</td>';

  echo '<td>'; 
  echo $arrDept["eMail"];
  echo '</td>';

  echo '<td>';
  if ($intDepID < 0 && $arrDept["AutoImport"] == 1) {
    echo $arrDept["WeeksView"];  
  }
  else {
    echo 'N/A';
  }

  echo '</td>';


  
  echo '<td>'; 
  echo $arrDept["SignInDays"];
  echo '</td>';

  echo '<td align="center">';
  if ($arrDept["AllowInBuilding"] == 1) {
    echo '<img border="0" src="images/green_tick.png" width="12px" height="12px">';
  }
  else {
    echo '<img border="0" src="images/red_cross.png" width="12px" height="12px">';    
  }   
  echo '</td>';

  echo '<td align="center">';
  if ($arrDept["AllowApplyOvertime"] == 1) {
    echo '<img border="0" src="images/green_tick.png" width="12px" height="12px">';
  }
  else {
    echo '<img border="0" src="images/red_cross.png" width="12px" height="12px">';    
  }   
  echo '</td>';
  
  echo '<td align="center">';
  if ($arrDept["ColourWeek"] == 1) {
    echo '<img border="0" src="images/green_tick.png" width="12px" height="12px">';
  }
  else {
    echo '<img border="0" src="images/red_cross.png" width="12px" height="12px">';    
  }   
  echo '</td>';

  echo '<td>'; 
  if ($arrDept["ConfirmedDays"] != -1) {
    echo $arrDept["ConfirmedDays"];
  }
  echo '</td>';

  echo '<td>'; 
  if ($arrDept["ConfirmedDays"] != -1) {
    echo $arrDept["LocksEnd"];
  }
  echo '</td>';
  
  echo '<td align="center">';
  if ($arrDept["ConfirmedDays"] != -1) {
    if ($arrDept["LocksRollWeek"] == 1) {
      echo '<img border="0" src="images/green_tick.png" width="12px" height="12px">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="12px" height="12px">';    
    } 
  }  
  echo '</td>';
  
  //echo '<td>'; 
  //echo $arrDept["LocksPerWeek"];
  //echo '</td>';
              
  echo '<td>'; 
  echo $arrDept["MaskAfter"];
  echo '</td>';

  echo '<td>'; 
  echo $arrDept["HideDailyView"];
  echo '</td>';
    
  echo '<td>'; 
  echo $arrDept["DailyEditPeriod"];
  echo '</td>';

  echo '<td>'; 
  echo $arrDept["DailyEditStart"];
  echo '</td>';

  echo '<td>'; 
  echo $arrDept["DailyEditEnd"];
  echo '</td>';  

  //echo '<td align="center">';
  //if ($arrDept["DailyAutoWeekend"] == 1) {
  //  echo '<img border="0" src="images/green_tick.png" width="12px" height="12px">';
  //}
  //else {
  //  echo '<img border="0" src="images/red_cross.png" width="12px" height="12px">';    
  //}   
  //echo '</td>';

  echo '<td align="center">';
  if ($arrDept["HasGridChecks"] == 1) {
    echo '<img border="0" src="images/green_tick.png" width="12px" height="12px">';
  }
  else {
    echo '<img border="0" src="images/red_cross.png" width="12px" height="12px">';    
  }   
  echo '</td>';
  
  echo '<td align="center">';
  if ($arrDept["HasHandovers"] == 1) {
    echo '<img border="0" src="images/green_tick.png" width="12px" height="12px">';
  }
  else {
    echo '<img border="0" src="images/red_cross.png" width="12px" height="12px">';    
  }   
  echo '</td>';
  
  echo '<td align="center">';
  if ($arrDept["HasXmasPoints"] == 1) {
    echo '<img border="0" src="images/green_tick.png" width="12px" height="12px">';
  }
  else {
    echo '<img border="0" src="images/red_cross.png" width="12px" height="12px">';    
  }   
  echo '</td>';  

  echo '<td align="center">';    
  echo '<img border="0" onclick="javascript:ShowDeptHistory('.$intDepID.');" src="../images/history.png" width="12px" height="12px">';    
  echo '</td>';  

  echo '</tr>';
} 

echo '</tbody>';
echo '</table>'; 
//echo '</div>';
// ###################################################### END Put the groups in the div


?>
<script type="text/javascript">
$(document).ready(function(){

  var table = $("#departmentslist").DataTable({
    paging: false,
    scrollY: 400,
    info:     false,
    stateSave: true,
    deferRender: true,
    scrollCollapse: true,
    "initComplete": function( settings, json ) {
        ResizeDeptsGrid();
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]);
  table.$('#<?php echo $intDefaultDepartment?>').addClass('selected');    
  $('#departmentslist tbody').on( 'click', 'tr', function () {
    if ( $(this).hasClass('selected') ) {
      $(this).removeClass('selected');
    }
    else {
      table.$('tr.selected').removeClass('selected');
      $(this).addClass('selected');
    }
  }); 
ResizeDeptsGrid(); 

  $('[title]').qtip({
      position: {
        my: 'top center',
        at: 'bottom center',
        viewport: $(window)
    },
    style: 'qtip-rounded qtip-shadow qtip-light'
  });



   
});


function EditDepartment(DepID) {
  $.post("page-includes/admin/deptsedit.php", {
    DepID: DepID
  },
  function(data,status){
	  $.facebox(data);
  })
}

function ShowDeptHistory(DepID) {
  $.post("page-includes/admin/depthistory.php", {
    DepID: DepID
  },
  function(data,status){
	  $.facebox(data);
  })

}
$(window).resize(function() {
  ResizeDeptsGrid();
})

function ResizeDeptsGrid () {
  var offset = ($("#departmentslist").offset().top);
  var windowheight = $(window).height() - offset;
  $('.dataTables_scrollBody').height((windowheight));
}  
        
</script>  