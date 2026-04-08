<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/suggestionsfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$intPageType = $_REQUEST['type'];

$intTypeID = $_REQUEST['suggestiontype'];
//$arrAdminDepartments = GetAdminDepartmentsByLogin ($strUser);
$intSysAdmin = GetIsSysAdmin ($strUser);

if ($intSysAdmin == 1) {
  $intCanEdit = 1;
}
else {
  $intCanEdit = 0;
}
/*
“Subject”                  ->            “Area - Dept – ID”
“Details”                  ->            “Managers and Schedulers”
“Business Reason”          ->            “Size” 
“Progress”                 ->            “Scheduling System”
“Remedy Ref”               ->            “Payments System”

Please hide:

“Created by”
“Created on”

*/

$arrSuggestions = GetSuggestions($intTypeID, $intPageType);
if (isset($arrSuggestions)) {
  echo '<table class="redtable compact stripe" id="schedareas'.$intPageType.'" width="100%">';
  echo '<thead>';
  echo '<tr style="height:45px">';
  echo '<th>';
  echo 'Status';
  echo '</th>';
  echo '<th>';
  echo 'Area - Dept - ID';
  echo '</th>';
  echo '<th>';
  echo 'Managers and Schedulers';
  echo '</th>';
  echo '<th>';
  echo 'Size';
  echo '</th>';
  echo '<th>';
  echo 'Timesheet Authorisers';
  echo '</th>';  
  echo '<th>';
  echo 'Systems';
  echo '</th>';
  echo '<th>';
  //echo 'History';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  foreach ($arrSuggestions as $intID => $arrSuggestion) {
    if ($intCanEdit == 1 || $strUser == $arrSuggestion['CreatorLog1n']) {
      echo '<tr class="handcursor" ondblclick="javascript:EditSchedArea('.$intID.')";>';
    }
    else {
      echo '<tr>';    
    }
    echo '<td align="center">';
    switch ($arrSuggestion['Status']) {
      case 0;
        $img = "PurpleCircle.png";
        break;
      case 1;
        $img = "AmberCircle.png";
        break;
      case 2;
        $img = "GreenCircle.png";
        break;
      case 3;
        $img = "RedCircle.png";
        break;
    }
    if ($intCanEdit == 1) {
      echo '<img border="0" src="images/'.$img.'" width="12" height="12" onclick="javascript:ChangeStatus('.$intID.')";>';
    }
    else {
      echo '<img border="0" src="images/'.$img.'" width="12" height="12">';    
    }
    echo '</td>';    
    echo '<td>';
    echo  $arrSuggestion['Subject'];
    echo '</td>';
    echo '<td>';
    echo  nl2br($arrSuggestion['Details']);
    echo '</td>';
    echo '<td>';
    echo  nl2br($arrSuggestion['BusinessJustification']);
    echo '</td>';
    echo '<td>';
    echo  nl2br($arrSuggestion['Progress']);
    echo '</td>';
    echo '<td>';
    echo  $arrSuggestion['RemedyRef'];
    echo '</td>';
    echo '<td align="center">';
    echo '<img border="0" onclick="javascript:ShowSuggestionHistory('.$intID.');" src="../images/history.png" width="12px" height="12px">';
    echo '</td>';


    echo '</tr>';
  }
  echo '</tbody>';
  echo '</table>';
}
else {
  echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
  echo '<br>There is nothing matching this view at the moment.<br><br>';
  echo '</div>';
}
if (isset($arrSuggestions)) {
?>
<script language="JavaScript" type="text/javascript">
      var table = $("#schedareas<?php echo $intPageType?>").DataTable({
        paging: false,
        destroy: true,
        scrollY: 400,
        info:     false,
        stateSave: true,
        deferRender: true,

          "columnDefs": [
            { "width": "50px", "targets": 0 },
            { "width": "250px", "targets": 1 },
            { "width": "200px", "targets": 2 },
            { "width": "125px", "targets": 3 },
            { "width": "250px", "targets": 4 },
          ],

        "initComplete": function( settings, json ) {
          DoResize();
        }
      });

      // initialize filter
      yadcf.init(table, [
          {column_number: 1,
            filter_type: 'text'
          },
          {column_number: 2,
            filter_type: 'text'
          },
          {column_number: 3,
            filter_type: 'text'
          },
          {column_number: 5,
            filter_type: 'text'
          },
        ]);
        
$( window ).resize(function() {
  DoResize();
})

function DoResize() {
  var windowheight = $(window ).height() - 275;
  $('.dataTables_scrollBody').height((windowheight));
}
function EditSchedArea(id, typeid) {
  $.post("page-includes/suggestions/editsschedulingarea.php", {
    id: id,
    typeid: typeid
  },
  function(data,status){
	  $.facebox(data);
  })
}

</script>
<?php
}
?>