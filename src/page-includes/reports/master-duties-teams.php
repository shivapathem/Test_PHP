<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';


$intTeamID = $_REQUEST['departmentid'];

if (isset($_REQUEST['admin'])) {
  $intIsAdmin = $_REQUEST['admin'];
}
else {
  $intIsAdmin = 0;
}
if ($intIsAdmin == 1) {
  $intColTotal  = 5;
}
else {
  $intColTotal  = 4;
}


$strDepartmentName = GetDepartmentNameFromID($intTeamID);

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

// ######################################################## Work Out the week we are to view ###############################################################
if (isset($_REQUEST['WeekNumber'])) {
  // Passed a week Number?
  $intWeekNumber = $_REQUEST['WeekNumber'];
}
else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];    
  }
  else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));  
  }
}

$arrTeamsStaff = ReadStaffForReports($intTeamID, $intIsAdmin);

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>';
if ($intIsAdmin == 1) {
  echo 'You can hide people from the Reports here.';
}
echo '<br><font size="3">People in this view <span id="MasterDutiesTeamsCount"></span><br>EFT for this view <span id="MasterDutiesTeamsTotal">1213</span></font><br><br>';


echo '</div>';  


echo '<table class="tablesmall stripe" id="MasterDutiesTeams-'.$intTeamID.'">';   
echo '<thead>'; 
echo '<tr>';
if ($intIsAdmin == 1) {
  echo '<th style="text-align:center;">Visible</th>';
}    

echo '<th style="text-align:center;">Name</th>';
echo '<th style="text-align:center;">Current EFT</th>';
echo '</tr>';
echo '</thead>'; 

echo '<tbody>';
$sumOfEFT = 0;
if (isset($arrTeamsStaff)) {   
  foreach($arrTeamsStaff as $strStaffNumber => $arrPerson) {
    echo '<tr>';
    if ($intIsAdmin == 1) {  
      echo '<td align="center" class="handcursor"><span style="display:none">'.$arrPerson['HidePerson'].'</span>';
      if ($arrPerson['HidePerson'] == 0) {
        echo '<img onclick="javascript:ToggleTeamPerson('.$intTeamID.', \''.$arrPerson['Login'].'\', 1);" border="0" src="images/green_tick.png" width="12px" height="12px">';
      }
      else {
        echo '<img onclick="javascript:ToggleTeamPerson('.$intTeamID.', \''.$arrPerson['Login'].'\', 0);" border="0" src="images/red_cross.png" width="12px" height="12px">';    
      }  
       echo '</td>';      
    }     
     echo '<td style="text-align:center;">';
     echo $arrPerson['Name'];
     echo '</td>'; 
     echo '<td style="text-align:center;">';   
     echo $arrPerson['EFT'];
     echo '</td>';        
     echo '</tr>';
	$sumOfEFT = $sumOfEFT + $arrPerson['EFT'];
  }
}      
echo '</tbody>'; 
   
echo '</table>';    
      
?>

<script type="text/javascript">
 $(document).ready(function() {
  var table = $("#MasterDutiesTeams-<?php echo $intTeamID?>").DataTable({
    destroy: true,
    paging: false,
    scrollY: 1000,
    scrollCollapse: true,
    info:     false,
    stateSave: true,
    deferRender: true,
    //order: [[ 2, "desc" ]],

        
  "initComplete": function( settings, json ) {
    ResizeMasterDutiesTeamsTable();
    $('#loading').hide();
  }         
  });
<?php
if ($intIsAdmin == 1) {   
?>
  yadcf.init(table, [ 
	{column_number: 1,
      filter_type: 'text'
    },  
    /*{column_number: 2,
      filter_type: 'select'
    }, 
    {column_number: 3,
      filter_type: 'select'
    }, */
  ]);
<?php
} 
else { 
?>
  yadcf.init(table, [  
    {column_number: 0,
      filter_type: 'text'
    },
  ]);      

<?php
} 

?>
  
  if ( $.cookie("#MasterDutiesTeams-<?php echo $intTeamID?>") !== null ) {
    scrollPos = $.cookie("#MasterDutiesTeams-<?php echo $intTeamID?>");
    $("#MasterDutiesTeams-<?php echo $intTeamID?>").closest('.dataTables_scrollBody').scrollTop(scrollPos);      
  };  
})  

  
$("#MasterDutiesTeams-<?php echo $intTeamID?>").closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $("#MasterDutiesTeams-<?php echo $intTeamID?>").closest('.dataTables_scrollBody').scrollTop();
  $.cookie("#MasterDutiesTeams-<?php echo $intTeamID?>", currpos);
});


$(window).resize(function() {
  ResizeMasterDutiesTeamsTable();
})

function ResizeMasterDutiesTeamsTable () {
  var offset = ($("#MasterDutiesTeams-<?php echo $intTeamID?>").length > 0) ? ($("#MasterDutiesTeams-<?php echo $intTeamID?>").offset().top) : 0;
  var windowheight = $(window).height() - offset - 80;
  $('.dataTables_scrollBody:has(#MasterDutiesTeams-<?php echo $intTeamID?>)').height(windowheight+'px');
  $('#MasterDutiesTeams-<?php echo $intTeamID?>').dataTable().fnAdjustColumnSizing(); 
}

function ToggleTeamPerson(department, login, action) {
  $.post("page-includes/reports/master-duty-team-toggle.php", {
    departmentid: department,
    login: login,
    action: action
  },  
  function(data,status){
    ShowMDManageStaff();
  })
}
$('#MasterDutiesTeamsTotal').html("<?php echo $sumOfEFT; ?>");
</script>    