<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/userGroupfunctions.php';

if (isset($_REQUEST['id'])) {
  $currentid = $_REQUEST['id'];
}
else {
  $currentid = 0;
}

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$rsGroups = GetGroupListByLogin($strUser);

  echo '<div id="prefsmyfavs">';
  echo '<div class="tableheadersmall medtextbold" style="width: 1000px">';
  echo '<table border="0">';
  echo '<tr>';
  echo '<td valign="top" width="500px"><br>You can choose to create groups of staff for a customised view.
        <br>These can be people you appraise, people who work similar shifts to you
        <br>or just people you socialise with.<br><br></td>';
  echo '<td>';
  echo '<table>';
  echo '<tr>';
  echo '<td width="33%">';

  if (!empty($rsGroups)) {
    echo 'Current Lists<br><br>';
    echo '<select class="chosen-select"  id="favsdrop" name="favsdrop" onchange="javascript:ShowFavourites(value)";>';
    foreach ($rsGroups as $row) {
      if ($currentid == 0) {
        $currentid = $row['id'];
      }
      if ($currentid == $row['id']) {
        echo '<option selected value="'.$row['id'].'">'.$row['description'].'</option>';
      }
      else {
        echo '<option value="'.$row['id'].'">'.$row['description'].'</option>';
      }
    }
    echo '</select>';
  }

  echo '</td>';
  echo '<td width="200px" align="center" Style="cursor: pointer" onclick="javascript:FavsAddnew()";>';
  echo '<table>';
  echo '<tr>';
  echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
  echo '<td>';
  echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  if ($currentid != 0) {
    echo '<td width="200px" align="center"  Style="cursor: pointer" onclick="javascript:FavsDelete()";>';
    echo '<table>';
    echo '<tr>';
    echo '<td align="right">&nbsp;&nbsp;Delete&nbsp;</td>';
    echo '<td>';
    echo '<img border="0" src="images/button_delete.png" width="30px" height="30px"></img>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</td>';
  }
  echo '</tr>';
  echo '</table>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';
  echo '<br>';
  echo '<div id="prefsmyfavsrestofpage">';

  echo '</div>';
  echo '</div>';
?>
<div id="dialog-fav-delete" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this Group?</span></p>
</div>

<script type="text/javascript">

ShowFavourites(<?php echo  $currentid?>);

$(document).ready(function(){
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "200px"
  });

})

function ShowFavourites(favid) {
  $.post("page-includes/users/user-groups-fillpage.php", {
    id: favid
  },
  function(data,status){
    $('#prefsmyfavsrestofpage').html(data);
   }
  )
}

function FavsAddnew () {
  $.post("page-includes/users/user-groups-add-new.php", {
  },
  function(data,status){
	  $.facebox(data);
  })
}

function FavsDelete() {
  $( "#dialog-fav-delete" ).dialog(
    {
      width:400,
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
            var id = ($('#favsdrop option:selected').val());
            $.post("page-includes/users/user-group-delete.php", {
              id: id
            },
            function(data,status){
              ShowUserGroups(0);
              FillMenu();
            });
        },
        "No": function() {
          $( this ).dialog( "close" );
        },
      }
    }
  );
}
</script>
