<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../process/classTeamskills.php';
include_once '../../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
if ($arrUsersTeamdata["isShiftLeader"] == 1) {
    $teamskillobj = new classTeamskills;
    $strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    echo '<h1 class="sr-only">Skills</h1>';
    $arrMyDepartments = json_decode($teamskillobj->GetSkillsDepartmentsShiftLeader($strLogin), true);
    echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
    echo '</div>';
    echo '<br>';

    if (isset($arrMyDepartments)) {
        echo '<div id="accordion-skills">';
        // Loop through the available Groups
        foreach ($arrMyDepartments as $intTeamD => $strTeamName) {
            echo '<h2 id="' . $intTeamD . '">';
            echo $strTeamName;
            echo '</h2>';
            echo '<div>';
            echo '  <div class="skillstabs-' . $intTeamD . '">';
            echo '    <ul>';
            echo '      <li><a href="#skillstabs-0-' . $intTeamD . '">Programmes/Staff</a></li>';
            echo '      <li><a href="#skillstabs-1-' . $intTeamD . '">Staff/Programmes/Duties</a></li>';
            echo '    </ul>';
            echo '    <div id="skillstabs-0-' . $intTeamD . '">';
            echo '    </div>';
            echo '    <div id="skillstabs-1-' . $intTeamD . '">';
            echo '    </div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    $intFirstKey = array_keys($arrMyDepartments)[0];
} else {
    echo 'Access Denied';die;
}
?>

<div id="dialog-programme-delete" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete this Programme?<br>It will remove all staff associated with it!</span></p>
</div>




<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $( "#accordion-skills" ).accordion({
      heightStyle: "content",
      collapsible: true,
      activate : function( event, ui ) {
        var teamid = ($('.ui-accordion-header-active').attr('id'));
        var $tabs = $('.skillstabs-'+teamid).tabs();
        var SelectedTab = $tabs.tabs('option', 'active');
        GetSkillsTabContent(teamid, SelectedTab);
      }
    });
  });

<?php
foreach ($arrMyDepartments as $intTeamD => $strTeamName) {
    ?>
  $(function() {
    $(".skillstabs-<?php echo $intTeamD ?>").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $(".skillstabs-<?php echo $intTeamD ?>").tabs( "option", "active" );
          GetSkillsTabContent(<?php echo $intTeamD ?>, SelectedTab);
      }
    });
  });


<?php
}
?>
GetSkillsTabContent (<?php echo $intFirstKey ?>, 0)

})
function GetSkillsTabContent (teamid, SelectedTab) {
  if (SelectedTab == 0) {
    $.post("page-includes/teamskills/view/skills-progs-staff.php", {
      teamid: teamid,
      readonly: 1,
	  isshiftleader:1
    },
    function(data,status){
      $('#skillstabs-'+SelectedTab+'-'+teamid).html(data);
    })
  }
  if (SelectedTab == 1) {
    $.post("page-includes/teamskills/view/skills-staff-programmes-duties.php", {
      teamid: teamid,
       readonly: 1,
	  isshiftleader:1
    },
    function(data,status){
      $('#skillstabs-'+SelectedTab+'-'+teamid).html(data);
    })
  }

}
function ToggleTimeZone(teamid, SelectedTab) {
    $.post("page-includes/teamskills/view/skills-toggletimezone.php", {
    },
    function(data,status){
      GetSkillsTabContent(teamid, SelectedTab);
    })
}
</script>