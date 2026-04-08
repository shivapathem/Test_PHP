<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intSuggestionType = $_REQUEST['typeid'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrAdminDepartments = GetAdminDepartmentsByLogin ($strUser);
$intSysAdmin = GetIsSysAdmin ($strUser);

if (count($arrAdminDepartments) >= 0 || $intSysAdmin == 1) {
  $intCanEdit = 1;
}
else {
  $intCanEdit = 0;
}

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
switch ($intSuggestionType) {
  case 0:
    echo '<br>Grey Allocate Items.<br><br>';  
    break;
  case 1:  
    echo '<br>Allocate Website Items.<br><br>';
    break;
  case 2:
    echo '<br>Scheduling Areas.<br><br>';
    break;    
}


if ($intCanEdit == 1) {
  if ($intSuggestionType == 2) {
    echo '<div class="DutyCellBottomLeft" onclick="javascript:EditSchedArea(0, '.$intSuggestionType.')";>';  
  }
  else {
    echo '<div class="DutyCellBottomLeft" onclick="javascript:EditSuggestion(0, '.$intSuggestionType.')";>';  
  }
  echo '<table>';
  echo '<tr>';
  echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
  echo '<td>';
  echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';
}
echo '</div>';
if ($intSuggestionType == 2) {
  echo'<div id="suggestonstabs" style="width:100%">';
  echo'<ul>';
  echo'<li><a data-rel="0" href="page-includes/suggestions/schedulingareasbystatus.php?type=0&suggestiontype='.$intSuggestionType.'">Active</a></li>';
  echo'<li><a data-rel="2" href="page-includes/suggestions/schedulingareasbystatus.php?type=2&suggestiontype='.$intSuggestionType.'">Completed</a></li>';
  echo'<li><a data-rel="3" href="page-includes/suggestions/schedulingareasbystatus.php?type=3&suggestiontype='.$intSuggestionType.'">Deleted</a></li>';
  echo'</ul>';
  echo'</div>';
}
else {
  echo'<div id="suggestonstabs" style="width:100%">';
  echo'<ul>';
  echo'<li><a data-rel="0" href="page-includes/suggestions/suggestionsbystatus.php?type=0&suggestiontype='.$intSuggestionType.'">Active Suggestions</a></li>';
  echo'<li><a data-rel="2" href="page-includes/suggestions/suggestionsbystatus.php?type=2&suggestiontype='.$intSuggestionType.'">Completed Suggestions</a></li>';
  echo'<li><a data-rel="3" href="page-includes/suggestions/suggestionsbystatus.php?type=3&suggestiontype='.$intSuggestionType.'">Declined Suggestions</a></li>';
  echo'</ul>';
  echo'</div>';
}

?>
<script type="text/javascript">
$(function() {
  var table =[];
  $( "#suggestonstabs" ).tabs({
    load: function(event, ui){
      var current_index = $("#suggestonstabs").tabs("instance");
      current_index = $(current_index.active[0]).find('a').attr('data-rel');
    }
  });
});


function EditSuggestion(id, typeid) {
  $.post("page-includes/suggestions/editsuggestion.php", {
    id: id,
    typeid: typeid
  },
  function(data,status){
	  $.facebox(data);
  })
}

function ShowSuggestionHistory(id) {
  $.post("page-includes/suggestions/history.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
}

function ChangeStatus(id) {
  $.post("page-includes/suggestions/changestatus.php", {
    id: id,
  },
  function(data,status){
	  $.facebox(data);
  })
}

function showsuggestions() {
  var current_index = $("#suggestonstabs").tabs("option","active");
  $("#suggestonstabs").tabs('load',current_index);
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
function ShowSchedulingAreas() {
  var current_index = $("#suggestonstabs").tabs("option","active");
  $("#suggestonstabs").tabs('load',current_index);
}



</script>