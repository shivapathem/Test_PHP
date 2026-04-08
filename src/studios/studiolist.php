<?php
 session_start();
include_once '../function-includes/init.php';
include_once '../function-includes/genericfunctions.php';
if (isset($_REQUEST['id'])) {
  $currid = $_REQUEST['id'];
}
else {
  $currid = 0;
}
$db = OpenDatabase();
$query = "SELECT  id, studio, sortorder, sm, bookable
          FROM  studios
          WHERE AllocateInstanceID = " . getCurrentInstanceId() . "
          ORDER BY studio";

$studios = sqlsrv_query($db, $query);

echo '<div class="tableheadersmall medtextbold" style="width:100%">';
echo '<br>This is a list of current studios<br>
      Click on a row to edit the information<br>
      Click on the \'New\' Icon to add a new studio<br><br>';
echo '</div><br>';
echo '<table id="studios" class="tablesmall studios" width="400px">';
echo '<thead>';
echo '<tr>';
echo '<th class="tableheadersmall">Studio</th>';
echo '<th class="tableheadersmall">SM Operated</th>';
echo '<th class="tableheadersmall">Bookable</th>';
echo '<th class="tableheadersmall">';
echo '<img border="0" src="images/menu/new.png" width="18" height="17" onclick=\'javascript:EditStudioInfo(0)\';>';
echo '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
while($row = sqlsrv_fetch_array($studios)){
  $id = $row['id'];
  echo '<tr id="tr'.$id.'" onclick=\'javascript:EditStudioInfo('.$id.')\';>';
  echo '<td class="handcursor">';
  echo $row['studio'];
  echo '</td>';

  echo '<td class="handcursor" align="center">';
  if ($row['sm'] == 0) {
    echo '<img border="0" src="images/red_cross.png" width="12" height="12">';
  }
  else {
    echo '<img border="0" src="images/green_tick.png" width="12" height="12">';
  }
  echo '</td>';

  echo '<td class="handcursor" align="center" colspan="2">';
  if ($row['bookable'] == 0) {
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
  $("table.studios tr:odd").addClass("odd");
  $("table.studios tr:even").addClass("even");
  $('#tr<?php echo $currid?>').removeClass('odd');
  $('#tr<?php echo $currid?>').removeClass('even');
  $('#tr<?php echo $currid?>').addClass('highlighted');
})
$('#studios tr').click(function(e) {
    $('table.studios tr').removeClass('highlighted');
    $("table.studios tr:odd").addClass("odd");
    $("table.studios tr:even").addClass("even");

    $(this).addClass('highlighted');
    $(this).removeClass('odd');
    $(this).removeClass('even');
});
$('#studios').floatThead()


function EditStudioInfo(id) {
  $.post("studios/calls/studio-info-edit.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
}

</script>