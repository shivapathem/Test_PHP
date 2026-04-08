<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../users/process/classUserSetup.php';
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$setupObj = new classUserSetup();
$isSkillAdmin = $userRoleTrait->checkSkillAdminRole($_POST['teamid']);
if (isset($_POST['isshiftleader'])) {
	$intShiftleader = $_POST['isshiftleader'];
} else {
    $intShiftleader = 0;
}
if ($isSkillAdmin == 1 || $intShiftleader == 1) {
    $intTeamID = $_POST['teamid'];
    if (isset($_POST['readonly'])) {
        $intReadOnly = $_POST['readonly'];
    } else {
        $intReadOnly = 0;
    }
    echo '<div class="tableheadersmall" style="width: 100%">';
    echo '<br>Skills and Staff.<br>You can define Skills and assign Staff to them here.<br>Skills can be Programmes, The ability to work in a Studio or Resource or another type of Skill such as Director, Camera Operator or Sound.<br><br>';
    echo '</div>';
    echo '<br>';

    echo '<table class="tablesmall" width="100%">';
    echo '<tr>';

    echo '<td valign="top" width="300px">';
    echo '<div id="programmelist-' . $intTeamID . '">';
    echo '</div>';
    echo '</td>';

    echo '<td valign="top">';
    echo '<div id="staff-' . $intTeamID . '">';
    echo '<div class="tableheadersmall medtextboldcentre" style="width:50%">';
    echo '<br>Choose a Skill from the list<br><br>';
    echo '</div>';


    echo '</div>';
    echo '</td>';

    echo '</tr>';
    echo '</table>';
} else {
    echo 'Access Denied'; die;
}
?>
<script type="text/javascript">
    $(document).ready(function () {
        FillProgrammesList(0, <?php echo $intTeamID?>);
    })

    function FillProgrammesList(id, teamid) {
        $.post("page-includes/teamskills/view/skills-fill-programmes-list.php", {
                id: id,
                teamid: teamid,
                readonly: <?php echo $intReadOnly?>
            },
            function (data, status) {
                $('#programmelist-<?php echo $intTeamID?>').html(data);
            }
        )
    }

    function FillProgsStaffPage(id, teamid) {
        $.post("page-includes/teamskills/view/skills-progs-staff-fillpage.php", {
                id: id,
                teamid: teamid,
                readonly: <?php echo $intReadOnly?>
            },
            function (data, status) {
                $('#staff-<?php echo $intTeamID?>').html(data);
            }
        )
    }

    function EditProgramme(id, teamid) {
        $.post("page-includes/teamskills/view/skills-new-edit-programme.php", {
                id: id,
                teamid: teamid
            },
            function (data, status) {
                $.facebox(data);
            })
    }


    function DeleteProgramme(id) {
        $("#dialog-programme-delete").dialog({
            width: 500,
            buttons: {
                "Yes": function () {
                    $(this).dialog("close");
                    $.post("page-includes/teamskills/view/skills-delete-programme.php", {
                            id: id
                        },
                        function (data, status) {
                            $.facebox.close();
                            FillProgrammesList(0, <?php echo $intTeamID?>);
                            FillProgsStaffPage(0, <?php echo $intTeamID?>);
                        });
                },
                "No": function () {
                    $(this).dialog("close");
                },
            }
        });

    }

    $('#programmes td').click(function (e) {
        $('#programmes td').removeClass('highlighted');
        $(this).addClass('highlighted');

    });

</script>