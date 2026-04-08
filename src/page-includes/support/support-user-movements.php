<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intDepartmentID = $_REQUEST['departmentid'];
$intTabOption = $_REQUEST['selectedtab'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrAttach = array('No','Internal','External');
$arrDepartmentAccess = GetDepartmentAccessByLogin ($strUser, $intDepartmentID);

$db = OpenDatabase();

$strQuery = "SELECT         ISNULL(Staff.Login, N'') AS Login, Staff.Surname + ', ' + Staff.Forename AS FullName, Staff.PersonnelNumber AS StaffNumber, 
                            ISNULL(staff_status.FWRNotes, N'') AS FWRNotes, staff_status.FWRStartDate, staff_status.FWREndDate, ISNULL(staff_status.isFWR, 0) AS isFWR, 
                            ISNULL(staff_status.IsOnAttach, 0) AS IsOnAttach, staff_status.AttachStartDate, staff_status.AttachEndDate, ISNULL(staff_status.AttachNotes, N'') AS AttachNotes, 
                            ISNULL(staff_status.isFTC, 0) AS isFTC, staff_status.FTCStartDate, staff_status.FTCEndDate, ISNULL(staff_status.FTCNotes, N'') AS FTCNotes
          FROM              Staff 
          LEFT OUTER JOIN   staff_status ON Staff.Login = staff_status.Login
          WHERE             (Staff.DepartmentID = $intDepartmentID) AND (ISNULL(Staff.Login, N'') <> N'')
          ORDER BY          Staff.Surname, Staff.Forename";

  $rsUsers = sqlsrv_query($db, $strQuery);
  echo '<table class="tablesmall compact stripe" id="supporttable-'.$intDepartmentID.'-'.$intTabOption.'" width="100%">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Name<br>';
  echo '</th>';
  echo '<th>';
  echo 'Staff Number';
  echo '</th>';
  if ($intTabOption == 0) {
    echo '<th>';
    echo 'On Attach';
    echo '</th>';
    echo '<th>';
    echo 'Attach Start Date';
    echo '</th>';
    echo '<th>';
    echo 'Attach End Date';
    echo '</th>';
    echo '<th>';
    echo 'Attachment Notes';
    echo '</th>';
  }
  if ($intTabOption == 1) { 
    echo '<th>';
    echo 'FTC';
    echo '</th>';
    echo '<th>';
    echo 'FTC Start Date';
    echo '</th>';
    echo '<th>';
    echo 'FTC End Date';
    echo '</th>';
    echo '<th>';
    echo 'FTC Notes';
    echo '</th>';
  }
  if ($intTabOption == 2) { 
    echo '<th>';
    echo 'FWA';
    echo '</th>';
    echo '<th>';
    echo 'FWA Start Date';
    echo '</th>';
    echo '<th>';
    echo 'FWA End Date';
    echo '</th>';
    echo '<th>';
    echo 'FWA Notes';
    echo '</th>';

  }
  echo '<th>';
  echo 'History';
  echo '</th>';
  
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  while ($row = sqlsrv_fetch_array($rsUsers)) {
    //if ($arrDepartmentAccess['isScheduler'] == 1 || $arrDepartmentAccess['isAdmin'] == 1) {
    if ($arrDepartmentAccess['isAdmin'] == 1) {        
      echo '<tr class="handcursor" ondblclick="javascript:UserStatusEdit(\''.$row['Login'].'\','.$intDepartmentID.','.$intTabOption.');">';
    }
    else {
      echo '<tr>';     
    }
    
    echo '<td valign="top" nowrap>';
    echo $row['FullName'];
    echo '</td>';
    echo '<td valign="top">';
    echo $row['StaffNumber'];
    echo '</td>';
    if ($intTabOption == 0) {    
      echo '<td align="center">';
      if(!is_null($row['IsOnAttach'])) {
        echo $arrAttach[$row['IsOnAttach']];
      }
      echo '</td>';

      echo '<td>';
      if (!is_null($row['AttachStartDate'])) {
        echo '<span style="display:none">'.date("Ymd", strtotime($row['AttachStartDate']->format('Y-m-d'))).'</span>'.date("d/m/Y", strtotime($row['AttachStartDate']->format('Y-m-d')));
      }
      echo '</td>';
    
      echo '<td valign="top" nowrap>';
      if (!is_null($row['AttachEndDate'])) {
        echo '<span style="display:none">'.date("Ymd", strtotime($row['AttachEndDate']->format('Y-m-d'))).'</span>'.date("d/m/Y", strtotime($row['AttachEndDate']->format('Y-m-d')));
      }
      echo '</td>';

      echo '<td>';
      echo nl2br($row['AttachNotes']);
      echo '</td>';
    }
    if ($intTabOption == 1) {   
      echo '<td align="center">';
      if ($row['isFTC'] == 1){
        echo '<span style="display:none">0</span><img border="0" src="images/tick.png" width="16" height="16">';
      }
      else {
        echo '<span style="display:none">1</span><img border="0" src="images/red_cross.png" width="12" height="12">';
      }
      echo '</td>';

      echo '<td>';
      if (!is_null($row['FTCStartDate'])) {
        echo '<span style="display:none">'.date("Ymd", strtotime($row['FTCStartDate']->format('Y-m-d'))).'</span>'.date("d/m/Y", strtotime($row['FTCStartDate']->format('Y-m-d')));
      }
      echo '</td>';
      echo '<td valign="top" nowrap>';
      if (!is_null($row['FTCEndDate'])) {
        echo '<span style="display:none">'.date("Ymd", strtotime($row['FTCEndDate']->format('Y-m-d'))).'</span>'.date("d/m/Y", strtotime($row['FTCEndDate']->format('Y-m-d')));
      }
      echo '</td>';
      echo '<td>';
      echo nl2br($row['FTCNotes']);
      echo '</td>';
    }
    
    if ($intTabOption == 2) {   
      echo '<td align="center">';
      if ($row['isFWR'] == 1){
        echo '<span style="display:none">0</span><img border="0" src="images/tick.png" width="16" height="16">';
      }
      else {
        echo '<span style="display:none">1</span><img border="0" src="images/red_cross.png" width="12" height="12">';
      }
      echo '</td>';

      echo '<td>';
      if (!is_null($row['FWRStartDate'])) {
        echo '<span style="display:none">'.date("Ymd", strtotime($row['FWRStartDate']->format('Y-m-d'))).'</span>'.date("d/m/Y", strtotime($row['FWRStartDate']->format('Y-m-d')));
      }
      echo '</td>';
      echo '<td>';
      if (!is_null($row['FWREndDate'])) {
        echo '<span style="display:none">'.date("Ymd", strtotime($row['FWREndDate']->format('Y-m-d'))).'</span>'.date("d/m/Y", strtotime($row['FWREndDate']->format('Y-m-d')));
      }
      echo '</td>';

      echo '<td>';
      echo nl2br($row['FWRNotes']);
      echo '</td>';
    }
    echo '<td align="center">';  
    echo '<img border="0" onclick="javascript:ShowUserMovementsHistory(\''.$row['Login'].'\');" src="../images/history.png" width="12px" height="12px">';
    echo '</td>';
    echo '</tr>';
   
  }
     
        
        
  echo '</tbody>';
  echo '</table>';

?>

<script type="text/javascript">
$(document).ready(function(){
    var table = $("#supporttable-<?php echo $intDepartmentID?>-<?php echo $intTabOption?>").DataTable({
      paging:    false,
      scrollY:   450,
      info:      false,
      stateSave: true,
      "columnDefs": [
        { "width": "150px", "targets": 0 },
        { "width": "150px", "targets": 1 },
        { "width": "75px", "targets": 2 },
        { "width": "100px", "targets": 3 },
        { "width": "100px", "targets": 4 },
        { "width": "50px", "targets": 6 }
      ],      
    });

  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
    {column_number: 1,
      filter_type: 'auto_complete'
    },
  ]);
  if ( $.cookie("supporttable-<?php echo $intDepartmentID?>-<?php echo $intTabOption?>") !== null ) {
    scrollPos = $.cookie("supporttable-<?php echo $intDepartmentID?>-<?php echo $intTabOption?>");
    $('#supporttable-<?php echo $intDepartmentID?>-<?php echo $intTabOption?>').closest('.dataTables_scrollBody').scrollTop(scrollPos);
  };   
});

$('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $('#supporttable-<?php echo $intDepartmentID?>-<?php echo $intTabOption?>').closest('.dataTables_scrollBody').scrollTop();
  $.cookie("supporttable-<?php echo $intDepartmentID?>-<?php echo $intTabOption?>", currpos);
});



</script>