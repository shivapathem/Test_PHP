<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../users/process/classUserSetup.php';
include_once __DIR__ . '/../../../function-includes/laravel_init.php';
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$setupObj = new classUserSetup();
$strLogin = $_SESSION['user']["UserID"];

//Fetch the staffdeatils By ID and Netlogin
$arrMyTeams = auth()->user()->userAccessibleTeams()->get();
echo '<h1 class="sr-only">Skills Admin</h1>';
echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
echo '</div>';
echo '<br>';
if (!empty($arrMyTeams)) {
    $intTeamID = auth()->user()->defaultTeamId;
    $intFirstKey = $intTeamID;
    echo '<div id="accordion-skills">';
    foreach ($arrMyTeams as $teamsData) {        
        if ($userRoleTrait->checkSkillAdminRole($teamsData) == 1) {
            $teamsId = $teamsData->schedulingTeamId;
            // Loop through the available Groups
            echo '<h2 id="' . $teamsId . '">';
            echo $teamsData['schedulingTeamName'];
            echo '</h2>';
            echo '<div>';
            echo '  <div class="skillstabs-' . $teamsId . '">';
            echo '    <ul>';
            echo '      <li><a href="#skillstabs-0-' . $teamsId . '">Skills/Staff</a></li>';
            echo '      <li><a href="#skillstabs-1-' . $teamsId . '">Duties/Skills</a></li>';
            echo '      <li><a href="#skillstabs-2-' . $teamsId . '">Duties/Staff</a></li>';
            echo '      <li><a href="#skillstabs-3-' . $teamsId . '">Staff/Skills/Duties</a></li>';
            echo '    </ul>';
            echo '    <div id="skillstabs-0-' . $teamsId . '">';
            echo '    </div>';
            echo '    <div id="skillstabs-1-' . $teamsId . '">';
            echo '    </div>';
            echo '    <div id="skillstabs-2-' . $teamsId . '">';
            echo '    </div>';
            echo '    <div id="skillstabs-3-' . $teamsId . '">';
            echo '    </div>';
            echo '</div>';
            echo '</div>';
        }
    }
    echo '</div>';

}
?>

<div id="dialog-programme-delete" title="Information!" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete this
        Programme?<br>It will remove all staff associated with it!</span></p>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $(function () {
            $("#accordion-skills").accordion({
                heightStyle: "content",
                collapsible: true,
				active: 'none',
                activate: function (event, ui) {
                    var teamid = ($('.ui-accordion-header-active').attr('id'));
                    var $tabs = $('.skillstabs-' + teamid).tabs();
                    var SelectedTab = $tabs.tabs('option', 'active');
                    GetSkillsTabContent(teamid, SelectedTab);
                }
            });
        });
        <?php
foreach ($arrMyTeams as $teamsData) {
    $teamsId = $teamsData->schedulingTeamId;
    ?>
        $(function () {
            $(".skillstabs-<?php echo $teamsId ?>").tabs({
                heightStyle: "content",
                activate: function (event, ui) {
                    var SelectedTab = $(".skillstabs-<?php echo $teamsId ?>").tabs( "option", "active" );
                    GetSkillsTabContent(<?php echo $teamsId ?>, SelectedTab);
                }
            });
        });

        <?php
}
if (!empty($intFirstKey)) {
    ?>

        GetSkillsTabContent(<?php echo $intFirstKey ?>, 0)
			<?php }?>
    })

    function GetSkillsTabContent(teamid, SelectedTab) {

        if (SelectedTab == 0) {
            $.post("page-includes/teamskills/view/skills-progs-staff.php", {
                    teamid: teamid
                },
                function (data, status) {
                    $('#skillstabs-' + SelectedTab + '-' + teamid).html(data);
                })
        }

        if (SelectedTab == 1) {
            $.post("page-includes/teamskills/view/skills-duties-programmes.php", {
                    teamid: teamid
                },
                function (data, status) {
                    $('#skillstabs-' + SelectedTab + '-' + teamid).html(data);
                })
        }

        if (SelectedTab == 2) {
            $.post("page-includes/teamskills/view/skills-duties-staff-cando.php", {
                    teamid: teamid
                },
                function (data, status) {
                    $('#skillstabs-' + SelectedTab + '-' + teamid).html(data);
                })
        }
        if (SelectedTab == 3) {
            $.post("page-includes/teamskills/view/skills-staff-programmes-duties.php", {
                    teamid: teamid
                },
                function (data, status) {
                    $('#skillstabs-' + SelectedTab + '-' + teamid).html(data);
                })
        }

    }

    function ToggleTimeZone(teamid, SelectedTab) {
        $.post("page-includes/teamskills/view/skills-toggletimezone.php", {},
            function (data, status) {
                GetSkillsTabContent(teamid, SelectedTab);
            })
    }

</script>