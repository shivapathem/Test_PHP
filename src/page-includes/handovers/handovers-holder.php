<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/handoverfunctions.php';
include_once "../../function-includes/DBHelper.php";
include_once '../users/process/classUserSetup.php';
/* new data  team wise*/

$setupObj = new classUserSetup();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);

if (isset($_POST['date'])) {
  $strCurrentDate = $_POST['date'];
}
else {
  $strCurrentDate = date("Y-m-d");
}
$strYesterday = date("Y-m-d", strtotime("-1 day", (strtotime($strCurrentDate))));
$strTomorrow = date("Y-m-d", strtotime("+1 day", (strtotime($strCurrentDate))));

echo '<h1 class="sr-only">Handovers</h1>';
if (isset($arrUsersTeamdata)) {
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 100%">';
  echo '<table class="tablesmallnoborder">';
  echo '<tr height="50px">';
  echo '<td width="200px" class="handcursor" onclick=\'javascript:ShowHandovers("'.$strYesterday.'")\';>&lt;&lt;&nbsp;'.date("jS M Y", strtotime($strYesterday)).'</td>';
  echo '<td width="200px" class="handcursor" onclick=\'javascript:ShowHandovers("'.$strTomorrow.'")\';>'.date("jS M Y", strtotime($strTomorrow)).'&nbsp;&gt;&gt;</td>';
  
  echo '<td width="200px" align="right">Choose a Date&nbsp;&nbsp;</td>';
  echo '<td width="200px" align="left"><input type="hidden" id="handoverdatepicker"></td>';
  echo '<td width="800px" align="left">Handovers for '.date("l jS F Y", strtotime($strCurrentDate)).'</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';

  echo'<div id="handovertabs" style="width: 100%">';
  echo'<ul>';

  $teamIds = array();
  if(isset($arrUsersTeamdata['Teams'])) {
    foreach($arrUsersTeamdata['Teams'] as $intTeamID => $arrUsersTeam) {
      $teamIds[] = $intTeamID;
    }
  }
  $isHandOversEnables = json_decode($setupObj->checkTeamsHandoversValue($teamIds), true);
  
  foreach ($arrUsersTeamdata['Teams'] as $intTeamID => $arrUsersTeam) {
    if ($arrUsersTeam['ShiftLeader'] == 1 && isset($isHandOversEnables[$intTeamID])) {
        echo'<li><a href="#handoverstabs-'.$intTeamID.'">'.$arrUsersTeam['schedulingTeamName'].'</a></li>';
    }
  }
  echo'</ul>';
  foreach ($arrUsersTeamdata['Teams'] as $intTeamID => $arrUsersTeam) {
    if ($arrUsersTeam['ShiftLeader'] == 1 && isset($isHandOversEnables[$intTeamID])) {
      echo'<div teamid="'.$intTeamID.'" id="handoverstabs-'.$intTeamID.'">';
      echo '</div>';
    }
  }
  echo '</div>';
  
?>
<div id="dialog-handover-delete" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this Handover Entry?</span></p>
</div>


<script type="text/javascript">

$(document).ready(function(){
  $(function() {
    var tabCookieName = "handoverstabs";
    $( "#handovertabs" ).tabs({
      active : ($.cookie(tabCookieName) || "0"),
      activate : function( event, ui ) {
        var newIndex = ui.newTab.parent().children().index(ui.newTab);
        $.cookie(tabCookieName, newIndex, { expires: 1 });
        var schedulingTeamId = $("#handovertabs .ui-tabs-panel:visible").attr("teamid");
        ShowTeamHandovers(schedulingTeamId);
      } 
    });
  });  

});
<?php
   foreach ($arrUsersTeamdata['Teams'] as $intTeamID => $arrUsersTeam) {
    if ($arrUsersTeam['ShiftLeader'] == 1 || $arrUsersTeam['hasHandovers'] == 1 || $arrUsersTeam['Scheduler'] == 1 || $arrUsersTeam['Manager'] == 1 || $arrUsersTeam['SystemAdmin'] == 1) {
      echo "ShowTeamHandovers($intTeamID);"; 
    }
  }

?>
 


function ShowTeamHandovers(schedulingTeamId) {
  $.post("page-includes/handovers/handovers-show.php", {
    date: '<?php echo $strCurrentDate?>',
        schedulingTeamId: schedulingTeamId
  },  
  function(data,status){
    $('#handoverstabs-'+schedulingTeamId).html(data);
  })
}


$(function() {
  $( "#handoverdatepicker" ).datepicker({
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    dateFormat: "yy-mm-dd",
    defaultDate: "<?php echo $strCurrentDate?>" ,
    onSelect: function (dateText, inst) {
      var currentdate = dateText;
      ShowHandovers(currentdate)
    }
  });
});

function NewEditHandover(date, teamid, id)     {
  $.post("page-includes/handovers/handovers-new-edit.php", {
    id: id,
    date: date,
    teamid: teamid
  },
  function(data,status){
	  $.facebox(data);
  })
}

function ConfirmDelete(id, teamid) {
  $( "#dialog-handover-delete" ).dialog(
    {
      width:400,
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
            $.post("page-includes/handovers/handover-delete.php", {
              id: id
            },
            function(data,status){
              ShowTeamHandovers(teamid);
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

<?php
}
else {
  echo '<div class="tableheadersmall medtextboldcentre bigtextbold">';
  echo '<br>You do not have access to the Handovers.<br>Only Shiftleaders and Administrators can view Handovers<br><br>';
  echo '</div>';
}
?>