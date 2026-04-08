<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$studioid = $_REQUEST['studioid'];
if (isset($_REQUEST['id'])) {
  $id = $_REQUEST['id'];
}
else {
  $id = 0;
}


$db = OpenDatabase();
$query = "SELECT studios_block_bookings.id, studios_block_bookings.studioid, studios_block_bookings.startdate, studios_block_bookings.enddate, studios_block_bookings.dotw,
          studios_block_bookings.programmeid, studios_block_bookings.sm, studios_block_bookings.starttime, studios_block_bookings.endtime, dbo.Programmes.Programme
          FROM  studios_block_bookings
          LEFT OUTER JOIN dbo.Programmes ON studios_block_bookings.programmeid = dbo.Programmes.ID
          AND studios_block_bookings.AllocateInstanceID = Programmes.AllocateInstanceID
          WHERE (studios_block_bookings.studioid = $studioid)
          AND studios_block_bookings.AllocateInstanceID = " . getCurrentInstanceId() ."
          ORDER BY studios_block_bookings.startdate";

$studios = sqlsrv_query($db, $query);

echo '<table id="blockbookingslist" class="tablesmall studios blockbookingslist" width="800px">';
echo '<thead>';
echo '<tr>';
echo '<th class="tableheadersmall">Start Date</th>';
echo '<th class="tableheadersmall">End Date</th>';
echo '<th class="tableheadersmall">Start Time</th>';
echo '<th class="tableheadersmall">End Time</th>';
echo '<th class="tableheadersmall">Programme</th>';
echo '<th class="tableheadersmall" width="25px">S</th>';
echo '<th class="tableheadersmall" width="25px">S</th>';
echo '<th class="tableheadersmall" width="25px">M</th>';
echo '<th class="tableheadersmall" width="25px">T</th>';
echo '<th class="tableheadersmall" width="25px">W</th>';
echo '<th class="tableheadersmall" width="25px">T</th>';
echo '<th class="tableheadersmall" width="25px">F</th>';
echo '<th class="tableheadersmall">SM Required</th>';
echo '<th class="tableheadersmall">';
echo '<img border="0" src="images/menu/new.png" width="18" height="17" onclick=\'javascript:EditBlock(0)\';>';
echo '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while($row = sqlsrv_fetch_array($studios)){
  unset ($arrdays);
  if (!is_null($row['dotw'])) {
    $arrtemp = explode(',', $row['dotw']);
    foreach ($arrtemp as $day)  {
      $arrdays[$day] = 1;
    }
  }

  $currid = $row['id'];
  echo '<tr id="tr'.$currid.'" onclick=\'javascript:EditBlock('.$currid.')\';>';
  echo '<td class="handcursor">';
  echo date("Y-m-d", strtotime($row['startdate']));
  echo '</td>';

  echo '<td class="handcursor">';
  echo date("Y-m-d", strtotime($row['enddate']));
  echo '</td>';

  echo '<td class="handcursor">';
  echo date("H:i", strtotime($row['starttime']));
  echo '</td>';

  echo '<td class="handcursor">';
  echo date("H:i", strtotime($row['endtime']));
  echo '</td>';

  echo '<td class="handcursor">';
  echo $row['Programme'];
  echo '</td>';

  for ($i = 0; $i <=6; $i++) {
    echo '<td class="handcursor" align="center">';
    if (isset($arrdays[$i])) {
      echo '<img border="0" src="images/green_tick.png" width="12" height="12">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="12" height="12">';
    }
    echo '</td>';
  }

  echo '<td class="handcursor" align="center" colspan="2">';
  if ($row['sm'] == 0) {
    echo '<img border="0" src="images/red_cross.png" width="12" height="12">';
  }
  else {
    echo '<img border="0" src="images/green_tick.png" width="12" height="12">';
  }
  echo '</td>';

  echo '</tr>';
}
echo '<tbody>';
echo '</table>';

?>

<script type="text/javascript">

$(document).ready(function(){
  $("table.blockbookingslist tr:odd").addClass("odd");
  $("table.blockbookingslist tr:even").addClass("even");

  $('#tr<?php echo $id?>').removeClass('odd');
  $('#tr<?php echo $id?>').removeClass('even');
  $('#tr<?php echo $id?>').addClass('highlighted');
})
$('#blockbookingslist tr').click(function(e) {
    $('table.blockbookingslist tr').removeClass('highlighted');
    $("table.blockbookingslist tr:odd").addClass("odd");
    $("table.blockbookingslist tr:even").addClass("even");

    $(this).addClass('highlighted');
    $(this).removeClass('odd');
    $(this).removeClass('even');
});

$('#blockbookingslist').floatThead()

function EditBlock(id) {
  $.post("studios/calls/studio-block-edit.php", {
    id: id,
    studioid: <?php echo $studioid?>
  },
  function(data,status){
	  $.facebox(data);
  })
}

</script>