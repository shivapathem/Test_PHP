<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase();
$strTerm = $_REQUEST['personname'];
          
$query = "SELECT   RTRIM(LTRIM(Teampay_Aux.dbo.SchedStaff.EmployeeName)) AS FullName, ISNULL(Teampay_Aux.dbo.SchedStaff.BBCNetworkLogin, '') AS Login, 
                ISNULL(Teampay_Aux.dbo.SchedStaff.ExtStaffNumber, '') AS StaffNumber
FROM      Teampay_Aux.dbo.SchedStaff LEFT OUTER JOIN
                Staff ON Teampay_Aux.dbo.SchedStaff.ExtStaffNumber = Staff.StaffNumber
WHERE   (Teampay_Aux.dbo.SchedStaff.EmployeeName LIKE '%$strTerm%') AND (ISNULL(Staff.DepartmentID, 255) = 255)
ORDER BY FullName";
          
          
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>People in ScheduALL who are not yet in Allocate <br>Only people with both a Staff Number and a Network Login can be added to this site<br><br>';
echo '</div>';

//echo $query;
$rsUsers = sqlsrv_query($db, $query);
  echo '<table class="tablesmall compact stripe" id="schedstaff">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Staff Name';
  echo '</th>';
  echo '<th>';
  echo 'Staff Number';
  echo '</th>';
  echo '<th>';
  echo 'Network Login';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';

  echo '<tbody>';


while($row = sqlsrv_fetch_array($rsUsers)) {
  echo '<tr>';
  echo '<td>';
  echo iconv('UTF-8', 'UTF-8//IGNORE', $row['FullName']);
  echo '</td>';
  echo '<td>';
  echo $row['StaffNumber'];
  echo '</td>';
  echo '<td>';
  echo $row['Login'];
  echo '</td>';
  echo '</tr>';
};
echo '</tbody>';
echo '</table>';
?>


<script type="text/javascript">

$(document).ready( function () {
  var table1 = $("#schedstaff").DataTable({
    paging: false,
    destroy: true,
    info:     false,
    stateSave: true,
    deferRender: true,
   
  });
  yadcf.init(table1, [
    {column_number: 0,
       filter_type: 'text'
    },
    {column_number: 1,
       filter_type: 'text'
    },
    {column_number: 2,
      filter_type: 'text'
    },
    ]);
})

</script>    
    