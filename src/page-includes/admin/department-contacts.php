<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intDepartmentID = $_REQUEST['departmentid'];
$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);

$arrStaff = GetContactInfo($intDepartmentID);



echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h2>Staff Contacts for \''.$strDepartmentName.'\'.</h2><br>';
echo '</div><br>';
echo '<table class="tablesmall compact stripe" id="depcontacts'.$intDepartmentID.'" min-width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th>';
echo 'Name';
echo '</th>'; 
echo '<th>';
echo 'Telephone';
echo '</th>';
echo '<th>';
echo 'eMail';
echo '</th>'; 
echo '</tr>'; 
echo '</thead>';

echo '<tbody>';
foreach ($arrStaff as $strLogon => $arrPerson) {
  echo '<tr>';  

  echo '<td>'; 
  echo $arrPerson['FullName'];
  echo '</td>';
  
  echo '<td>'; 
  echo nl2br($arrPerson['UserPhone']);
  echo '</td>';
  
  echo '<td>'; 
  echo nl2br($arrPerson['PersonalEmail']);
  echo '</td>';  
    
  echo '</tr>';

}
echo '</tbody>';
echo '</table>'; 

// ###################################################### END Put the groups in the div


?>
<script type="text/javascript">
$(document).ready( function () {
  var table = $("#depcontacts<?php echo $intDepartmentID?>").DataTable({
    paging: false,
    scrollY: 2000,
    info:     false,
    stateSave: true,
    deferRender: true,
    scrollCollapse: true,
  columnDefs: [
    { width: 500, targets: 1 },
    { width: 500, targets: 2 },
  ],     
    "initComplete": function( settings, json ) {
        Resizedepcontacts();
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]);
  

  if ( $.cookie("depcontacts<?php echo $intDepartmentID?>") !== null ) {
    scrollPos = $.cookie("depcontacts<?php echo $intDepartmentID?>");
    $('#depcontacts<?php echo $intDepartmentID?>').closest('.dataTables_scrollBody').scrollTop(scrollPos);      
  };
  
})


$(window).resize(function() {
  Resizedepcontacts();
})   

function Resizedepcontacts () {
  var offset = ($("#depcontacts<?php echo $intDepartmentID?>").offset().top);
  var windowheight = $(window).height() - offset - 50;
  $('.dataTables_scrollBody').height((windowheight));
  $('#depcontacts<?php echo $intDepartmentID?>').DataTable().columns.adjust().draw();
}  

$('#depcontacts<?php echo $intDepartmentID?>').closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $('#depcontacts<?php echo $intDepartmentID?>').closest('.dataTables_scrollBody').scrollTop();
  $.cookie("depcontacts<?php echo $intDepartmentID?>", currpos);
});  
     
</script>   