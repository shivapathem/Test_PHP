<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intDepartmentID = $_REQUEST['departmentid'];
$intTabOption = $_REQUEST['selectedtab'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrattach = array('No','Internal','External');
$arrDepartmentAccess = GetDepartmentAccessByLogin ($strUser, $intDepartmentID);

$today = date("Y-m-d");
$db = OpenDatabase();

$query = "SELECT        staff_status.id, staff_status.Login, Staff.Surname + N', ' + Staff.Forename AS FullName, Staff.StaffNumber, staff_status.ID, staff_status.FWRStartDate, staff_status.FWREndDate, 
                        staff_status.FWRNotes, staff_status.FWRStartNotes, staff_status.FWREndNotes, staff_status.FWRStartComplete, staff_status.FWREndComplete, 
                        staff_status.IsOnAttach, staff_status.AttachStartDate, staff_status.AttachEndDate, staff_status.AttachNotes, staff_status.AttachStartNotes, staff_status.AttachEndNotes,
                        staff_status.AttachStartComplete, staff_status.AttachEndComplete, staff_status.FTCStartDate, staff_status.FTCEndDate, staff_status.FTCNotes, 
                        staff_status.FTCStartNotes, staff_status.FTCEndNotes, staff_status.FTCStartComplete, staff_status.FTCEndComplete, Staff.DepartmentID
          FROM          staff_status 
          INNER JOIN    Staff ON staff_status.Login = Staff.Login
          WHERE        (Staff.DepartmentID = $intDepartmentID)";



  $rsPeople = sqlsrv_query($db, $query);
  $i = 0;
  while ($row = sqlsrv_fetch_array($rsPeople)) {
    // Attachment Start Date
   if (!is_null($row['AttachStartDate'])) {
     $arrPeople[$i]['ID'] = $row['id'];
     $arrPeople[$i]['Login'] = $row['Login'];
     $arrPeople[$i]['Name'] = $row['FullName'];
     $arrPeople[$i]['Desc'] = $arrattach[$row['IsOnAttach']].' Attachment';
     $arrPeople[$i]['Type'] = 1;
     $arrPeople[$i]['Date'] =  $row['AttachStartDate']->format("jS M Y");
     $arrPeople[$i]['uDate'] = strtotime($row['AttachStartDate']->format("Y-m-d"));
     $arrPeople[$i]['StartEnd'] = 0;
     $arrPeople[$i]['Notes'] = $row['AttachStartNotes'];
     $arrPeople[$i]['Complete'] = $row['AttachStartComplete'];
     $i++;
   }
   if (!is_null($row['AttachEndDate'])) {
     $arrPeople[$i]['ID'] = $row['id'];
     $arrPeople[$i]['Login'] = $row['Login'];
     $arrPeople[$i]['Name'] = $row['FullName'];
     $arrPeople[$i]['Desc'] = $arrattach[$row['IsOnAttach']].' Attachment';
     $arrPeople[$i]['Type'] = 2;
     $arrPeople[$i]['Date'] = $row['AttachEndDate']->format("jS M Y");
     $arrPeople[$i]['uDate'] = strtotime($row['AttachEndDate']->format("Y-m-d"));
     $arrPeople[$i]['StartEnd'] = 1;
     $arrPeople[$i]['Notes'] = $row['AttachEndNotes'];
     $arrPeople[$i]['Complete'] = $row['AttachEndComplete'];
     $i++;
   }

    // FWR Start Date
    if (!is_null($row['FWRStartDate'])) {
      $arrPeople[$i]['ID'] = $row['id'];
      $arrPeople[$i]['Login'] = $row['Login'];      
      $arrPeople[$i]['Name'] = $row['FullName'];
      $arrPeople[$i]['Desc'] = 'FWA';
      $arrPeople[$i]['Type'] = 3;
      $arrPeople[$i]['Date'] =  $row['FWRStartDate']->format("jS M Y");
      $arrPeople[$i]['uDate'] =  strtotime($row['FWRStartDate']->format("Y-m-d"));
      $arrPeople[$i]['StartEnd'] = 0;
      $arrPeople[$i]['Notes'] = $row['FWRStartNotes'];
      $arrPeople[$i]['Complete'] = $row['FWRStartComplete'];
      $i++;
   }
    // FWR End Date
   if (!is_null($row['FWREndDate'])) {
     $arrPeople[$i]['ID'] = $row['id'];
     $arrPeople[$i]['Login'] = $row['Login'];
     $arrPeople[$i]['Name'] = $row['FullName'];
     $arrPeople[$i]['Desc'] = 'FWA';
     $arrPeople[$i]['Type'] = 4;
     $arrPeople[$i]['Date'] =  $row['FWREndDate']->format("jS M Y");
     $arrPeople[$i]['uDate'] =  strtotime($row['FWREndDate']->format("Y-m-d"));
     $arrPeople[$i]['StartEnd'] = 1;
     $arrPeople[$i]['Notes'] = $row['FWREndNotes'];
     $arrPeople[$i]['Complete'] = $row['FWREndComplete'];
     $i++;
   }
    // FTC Start Date
   if (!is_null($row['FTCStartDate'])) {
     $arrPeople[$i]['ID'] = $row['id'];
     $arrPeople[$i]['Login'] = $row['Login'];     
     $arrPeople[$i]['Name'] = $row['FullName'];
     $arrPeople[$i]['Desc'] = 'FTC';
     $arrPeople[$i]['Type'] = 5;
     $arrPeople[$i]['Date'] = $row['FTCStartDate']->format("jS M Y");
     $arrPeople[$i]['uDate'] = strtotime($row['FTCStartDate']->format("Y-m-d"));
     $arrPeople[$i]['StartEnd'] = 0;
     $arrPeople[$i]['Notes'] = $row['FTCStartNotes'];
     $arrPeople[$i]['Complete'] = $row['FTCStartComplete'];
     $i++;
   }
    // FTC End Date
   if (!is_null($row['FTCEndDate'])) {
     $arrPeople[$i]['ID'] = $row['id'];
     $arrPeople[$i]['Login'] = $row['Login'];     
     $arrPeople[$i]['Name'] = $row['FullName'];
     $arrPeople[$i]['Desc'] = 'FTC';
     $arrPeople[$i]['Type'] = 6;
     $arrPeople[$i]['Date'] = $row['FTCEndDate']->format("jS M Y");
     $arrPeople[$i]['uDate'] = strtotime($row['FTCEndDate']->format("Y-m-d"));
     $arrPeople[$i]['StartEnd'] = 1;
     $arrPeople[$i]['Notes'] = $row['FTCEndNotes'];
     $arrPeople[$i]['Complete'] = $row['FTCEndComplete'];
     $i++;
   }
   
  }
  
  //echo '<pre>';
  //print_r($arrPeople);
if (!isset($arrPeople)) {
  echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
  echo '<br>There are no upcoming Staff Movements<br><br>';
  echo '</div>';
}
else {

  echo '<table class="tablesmall compact stripe" id="usersactivitytable-'.$intDepartmentID.'-'.$intTabOption.'" width="90%">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Start/End Date';
  echo '</th>';
  echo '<th>';
  echo 'Status';
  echo '</th>';
  echo '<th>';
  echo 'Name<br>';
  echo '</th>';
  echo '<th>';
  echo 'Activity';
  echo '</th>';

  echo '<th>';
  echo 'Complete';
  echo '</th>';
  echo '<th>';
  echo 'Notes';
  echo '</th>';
  echo '<th>';
  echo 'History';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  foreach ($arrPeople as $id => $arrperson) {
  if ($intTabOption == 3) {
    $intMatch = 0;
  }
  else {
    $intMatch = 2;  
  } 
  //echo '<pre>'; 
  //  print_r($arrperson);
    if ($arrperson['Complete'] == $intMatch) {
      echo '<tr>';
      echo '<td>';
      echo '<span style="display:none">'.$arrperson['uDate'].'</span>'.$arrperson['Date'];
      if ($arrperson['StartEnd'] == 0) {
        echo ' (Starts)';
      }
      else {
        echo ' (Ends)';
      }
      echo '</td>';
      echo '<td align="center">';

      if ($arrperson['uDate'] > strtotime("+70 Days")) {
        echo '<span style="display:none">0</span><img border="0" src="images/GreenCircle.png" width="16">';
      }
      elseif ($arrperson['uDate'] > strtotime("+35 Days")) {
        echo '<span style="display:none">1</span><img border="0" src="images/AmberCircle.png" width="16">';
      }
      else {
        echo '<span style="display:none">2</span><img border="0" src="images/RedCircle.png" width="16">';
      }
      echo '</td>';
      echo '<td>';
      echo $arrperson['Name'];
      echo '</td>';
      echo '<td>';
      echo $arrperson['Desc'];
      echo '</td>';
      echo '<td align="center" class="handcursor">';
      if ($arrDepartmentAccess['isAdmin'] == 1) {  
        // Can change Status
        switch ($arrperson['Complete']){
          case 0:
            echo '<span style="display:none">0</span><img border="0" src="images/red_cross.png" width="12" height="12" onclick=\'javascript:ToggleMovementComplete("'.$arrperson['ID'].'", "'.$arrperson['Type'].'", "2")\'>';
            break;
          case 1:
            echo '<span style="display:none">1</span><img border="0" src="images/yellow_tick.png" width="12" height="12" onclick=\'javascript:ToggleMovementComplete("'.$arrperson['ID'].'", "'.$arrperson['Type'].'", "2")\'>';
            break;
          case 2:
            echo '<span style="display:none">2</span><img border="0" src="images/tick.png" width="12" height="12" onclick=\'javascript:ToggleMovementComplete("'.$arrperson['ID'].'", "'.$arrperson['Type'].'", "0")\'>';
            break;
        }
      }
      else {
        switch ($arrperson['Complete']){
          case 0:
            echo '<span style="display:none">0</span><img border="0" src="images/red_cross.png" width="12" height="12">';
            break;
          case 1:
            echo '<span style="display:none">1</span><img border="0" src="images/yellow_tick.png" width="12" height="12">';
            break;
          case 2:
            echo '<span style="display:none">2</span><img border="0" src="images/tick.png" width="12" height="12">';
            break;
        }      
      }
      echo '</td>';
      if ($arrDepartmentAccess['isAdmin'] == 1) { 
        echo '<td class="handcursor" ondblclick=\'javascript:EditMovementNotes("'.$arrperson['ID'].'", "'.$arrperson['Type'].'")\'>';
      }
      else {
        echo '<td>';
      }
      echo $arrperson['Notes'];
      
      echo '</td>';
      echo '<td align="center">';  
      echo '<img border="0" onclick="javascript:ShowUserMovementsHistory(\''.$arrperson['Login'].'\');" src="../images/history.png" width="12px" height="12px">';
      echo '</td>';      
      echo '</tr>';
    }
  }
  echo '</tbody>';
  echo '</table>';



?>

<script type="text/javascript">

function EditMovementNotes(id, activitytype) {
  $.post("page-includes/support/support-movements-edit-notes.php", {
    id: id,
    activitytype: activitytype,
    department: <?php echo $intDepartmentID?>,
    taboption: <?php echo $intTabOption?>
  },
  function(data,status){
	  $.facebox(data);
  })
}

function ToggleMovementComplete(id, activitytype, action) {
  $.post("page-includes/support/support-movements-toggle-complete.php", {
    id: id,
    activitytype: activitytype,
    action: action
  },
  function(data,status){{
      GetSupportTabContent (<?php echo $intDepartmentID?>, <?php echo $intTabOption?>)
    }
  });
}


$(document).ready(function(){
    var table = $("#usersactivitytable-<?php echo $intDepartmentID?>-<?php echo $intTabOption?>").DataTable({
      paging:    false,
      scrollY:   450,
      info:      false,
      stateSave: true,
      "columnDefs": [
        { "width": "150px", "targets": 0 },
        { "width": "50px", "targets": 1 },
        { "width": "150px", "targets": 2 },
        { "width": "150px", "targets": 3 },
        { "width": "50px", "targets": 4 },
        { "width": "50px", "targets": 6 }
      ],
    });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
    {column_number: 2,
      filter_type: 'text'
    },
    {column_number: 3,
      filter_type: 'auto_complete'
    },
  ]);

});

</script>
<?php
}
?>