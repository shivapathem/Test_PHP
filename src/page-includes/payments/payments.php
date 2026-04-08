<?php
session_start();
include_once '../../function-includes/init.php';

$myStaffNumber = $_SESSION['allocations']['staffnumber'];
$query  = "SELECT PayRoll
           FROM dbo.Accounting
           WHERE (StaffNumber = N'$myStaffNumber')
           AND AllocateInstanceID = " . getCurrentInstanceId() ."
           GROUP BY PayRoll
           ORDER BY PayRoll DESC";

$db = OpenDatabase();

$periods = sqlsrv_query($db, $query);

echo '<div class="tableheadersmall medtextboldcentre" style="width: 100%">';
echo '<table class="tablesmallnoborder">';
echo '<tr>';
echo '<td><br>Payments<br><br></td>';
echo '<td width="200px" align="right">Choose a Payment period&nbsp;&nbsp;</td>';
echo '<td width="200px" align="left">';

echo '<select name="periods" id="periods">';
  while($row = sqlsrv_fetch_array($periods)){

     echo '<option value="'.$row['PayRoll'].'">'.$row['PayRoll'].'</option>';
  }

echo '</select>';

echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';

echo'<div id="paymentsbody">';
echo '</div>';

?>
<script type="text/javascript">
  $(function() {

    $( "#periods" )
      .selectmenu()
      .selectmenu( "menuWidget" )
        .addClass( "overflow" );

  });
  </script>