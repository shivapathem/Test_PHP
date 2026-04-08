<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;

$id = $_REQUEST["id"];
$intTeamID = $_REQUEST['teamid'];

if (isset($_POST['Update'])) {
    $prog = trim($_REQUEST["prog"]);
    if ($id == 0) {
        $teamskillobj->AddProgramWithTeam($prog, $intTeamID);

    } else {
        $teamskillobj->UpdateProgramWithTeam($prog, $id);
    }
} else {
    if ($id == 0) {
        $prog = '';
        $ButtomLabel = "Add";
    } else {
        $progrow = json_decode($teamskillobj->GetProgramById($id), true);
        $prog = $progrow['programmename'];
        $ButtomLabel = "Update";
    }

    echo '<form id="prognameeditform">';
    echo '<table class="tablesmalltidy" width="100%">';
    echo '<tr>';
    echo '<th colspan="2"><br>';
    if ($id == 0) {
        echo 'New Skill';
    } else {
        echo 'Edit Skill';
    }
    echo '<br><br></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td valign="top">Skill Description</td>';
    echo '<td><input type="text" id="prog" name="prog" size="30" value="' . $prog . '"></td>';
    echo '</tr>';

    echo '<td><input type="submit" value="'.$ButtomLabel.'" name="Update"></td>';

    if ($id != 0) {
        echo '<td align="right"><img border="0" src="images/delete.gif" width="18" height="17" Style="cursor: pointer" onclick="javascript:DeleteProgramme(' . $id . ',' . $intDepartmentID . ')"; ></td>';
    } else {
        echo '<td></td>';
    }

    echo '</tr>';
    echo '</table>';
    echo '<input type="hidden" name="id" value="' . $id . '">';
    echo '<input type="hidden" name="teamid" value="' . $intTeamID . '">';
    echo '</form> ';
    ?>

    <script type="text/javascript">
        $('document').ready(function () {
            $('#prognameeditform').validate({
                rules: {
                    "prog": {
                        required: true,
                    }
                },
                submitHandler: function (form) {
                    $('input[type="submit"]').prop('disabled', true);
                    $.ajax({
                        type: 'POST',
                        url: 'page-includes/teamskills/view/skills-new-edit-programme.php',
                        data: $('#prognameeditform').serialize(),
                        success: function (data) {
                            $.facebox.close();
                            FillProgrammesList(<?php echo $id?>, <?php echo $intTeamID?>);
                        }
                    });
                }
            })
        });
    </script>
    <?php
}
?>