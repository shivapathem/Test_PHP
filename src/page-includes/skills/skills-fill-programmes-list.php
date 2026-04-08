<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';

$intCurrentID = $_REQUEST["id"];
$teamID = $_REQUEST['department'];


if (isset($_REQUEST['readonly'])) {
  $intReadOnly = $_REQUEST['readonly'];
}
else {
  $intReadOnly = 0;
}

$db = OpenDatabase();

$programmes = ListAllProgrammes($teamID);
echo '<div class="tableheadersmall medtextboldcentre" style="width:99%; position:relative">';
echo '<br>Skills<br><br>';
if ($intReadOnly == 0) { 
  echo '<div class="DutyCellBottomRight"><img border="0" src="images/add.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:EditProgramme(0, '.$teamID.')";></div>';
}
echo '</div>';
echo '<table id="skillsstafftable-'.$teamID.'" class="tablesmall compact stripe" width="100%">';
echo '<thead>';
echo '<tr>';  
echo '<th>';

echo '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
if (isset($programmes)) {
  //echo '<td valign="top" colspan="2">';
  //echo '<div id="skillstafflist-'.$teamID.'" class="scrolldiv300">';
  //echo '<table width="100%" class="programmes tablesmall" id="programmes">';
  foreach ($programmes as $id => $progname) {
    if ($intReadOnly == 0) {     
      echo '<tr id="tr'.$id.'" onclick="javascript:FillProgsStaffPage('.$id.','.$teamID.')"; ondblclick="javascript:EditProgramme('.$id.','.$teamID.')";>';
    }
    else {
      echo '<tr id="tr'.$id.'" onclick="javascript:FillProgsStaffPage('.$id.','.$teamID.')";>';    
    }
    echo '<td colspan="2" class="handcursor">';
    echo $progname;
    echo '</td>';
    //if ($editable == 1) {
    //  echo '<td align="center"><img border="0" src="images/edit.gif" width="18" height="17" Style="cursor: pointer" onclick="javascript:EditProgramme('.$id.','.$teamID.')"; ></td>';
    //  echo '<td align="center"><img border="0" src="images/delete.gif" width="18" height="17" Style="cursor: pointer" onclick="javascript:DeleteProgramme('.$id.','.$teamID.')"; ></td>';
    //}
    echo '</tr>';
  }
  //echo '</table>';
  //echo '</div>';
}
//echo '</td>';
//echo '</tr>';
echo '</tbody>';
echo '</table>';






?>
<script type="text/javascript">
$(document).ready(function(){

    var table = $("#skillsstafftable-<?php echo $teamID?>").DataTable({
      paging: false,
      scrollY: 400, 
      info:     false,
      stateSave: true,
      "initComplete": function( settings, json ) {
      //DoResize();
      }
    });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]); 


  $('#skillsstafftable-<?php echo $teamID?> tbody').on( 'click', 'tr', function () {
    if ( $(this).hasClass('selected') ) {
      $(this).removeClass('selected');
    }
    else {
      table.$('tr.selected').removeClass('selected');
      $(this).addClass('selected');
    }
  });
})

 
</script>