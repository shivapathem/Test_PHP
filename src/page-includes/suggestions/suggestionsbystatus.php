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


$arrSuggestions = GetSuggestions($intTypeID, $intPageType);
if (isset($arrSuggestions)) {
  echo '<table class="redtable compact stripe handcursor" id="suggestions'.$intPageType.'" width="100%">';
  echo '<thead>';
  echo '<tr style="height:45px">';
  echo '<th>';
  echo 'Status';
  echo '</th>';
  echo '<th>';
  echo 'Created By';
  echo '</th>';
  echo '<th>';
  echo 'Created On';
  echo '</th>';
  echo '<th>';
  echo 'Subject<br>';
  echo '</th>';
  echo '<th>';
  echo 'Details<br>';
  echo '</th>';
  echo '<th>';
  echo 'Business Reason';
  echo '</th>';
  echo '<th>';
  echo 'Progress';
  echo '</th>';  
  echo '<th>';
  echo 'Remedy Ref';
  echo '</th>';
  echo '<th>';
  echo 'History';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  foreach ($arrSuggestions as $intID => $arrSuggestion) {
    if ($intCanEdit == 1) {
      echo '<tr ondblclick="javascript:EditSuggestion('.$intID.')";>';
    }
    else {
      echo '<tr>';    
    }
    
    $order = 0;
    switch ($arrSuggestion['Status']) {
      case 0;
        $img = "PurpleCircle.png";
        $order = 1;
        break;
      case 1;
        $img = "AmberCircle.png";
        $order = 2;
        break;
      case 2;
        $img = "GreenCircle.png";
        $order = 3;
        break;
      case 3;
        $img = "RedCircle.png";
        $order = 4;
        break;
    }
    echo '<td align="center" data-order="'.$order.'" >';
    if ($intCanEdit == 1) {
      echo '<img border="0" src="images/'.$img.'" width="12" height="12" onclick="javascript:ChangeStatus('.$intID.')";>';
    }
    else {
      echo '<img border="0" src="images/'.$img.'" width="12" height="12">';    
    }
    echo '</td>';
    echo '<td>';
    echo  $arrSuggestion['Creator'];
    echo '</td>';
    echo '<td>';
    echo '<span style="display:none">'.$arrSuggestion['uDateCreated'].'</span>';
    echo  $arrSuggestion['DateCreated'];
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
?>
<script language="JavaScript" type="text/javascript">
$('document').ready(function(){
      var table = $("#suggestions<?php echo $intPageType?>").DataTable({
        paging: false,
        destroy: true,
        scrollY: 400,
        info:     false,
        stateSave: true,
        deferRender: true,

          "columnDefs": [
            { "width": "50px", "targets": 0 },
            { "width": "100px", "targets": 1 },
            { "width": "125px", "targets": 2 },
            { "width": "200px", "targets": 3 },
            { "width": "100px", "targets": 7 },
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
          {column_number: 3,
            filter_type: 'text'
          },
          {column_number: 4,
            filter_type: 'text'
          },
          {column_number: 5,
            filter_type: 'text'
          },
          {column_number: 6,
            filter_type: 'text'
          },
        ]);
})        
$( window ).resize(function() {
  DoResize();
})

function DoResize() {
  var windowheight = $(window ).height() - 275;
  $('.dataTables_scrollBody').height((windowheight));
}

</script>