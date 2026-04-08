<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;

$id = $_REQUEST["id"];
$intTeamID = $_REQUEST['teamid'];

if (isset($_POST['Update'])) {
    $duty = trim($_REQUEST["duty"]);
    $description = trim($_REQUEST["description"]);
    $bst = $_SESSION['bst'];
    $intDuration = trim($_REQUEST["duration"]);

    if ($bst == 0) {
        $gmt = 1;
    } else {
        $gmt = 0;
    }

    if ($id == 0) {
        $teamskillobj->AddSkillDuties($duty, $description, $gmt, $bst, $intDuration, $intTeamID);
    } else {
        $teamskillobj->UpdateSkillDuties($id, $duty, $description, $intDuration);
    }
} else {
    if ($id == 0) {
        $duty = '';
        $description = '';
        $intDuration = 0;
        $ButtonLabel = 'Add';
        $PopupTitle = 'Add Duty';
    } else {
        $skilldutiesdata = json_decode($teamskillobj->ListSkillDutiesByID($id), true);
        $duty = $skilldutiesdata['duty'];
        $description = $skilldutiesdata['description'];
        $intDuration = $skilldutiesdata['DurationWith'];
        $ButtonLabel = 'Update';
        $PopupTitle = 'Edit Duty';
    }
    echo '<form id="neweditdutyform">';
    echo '<table class="tablesmalltidy" width="100%">';
    echo '<tr height="30px">';
    echo '<th colspan="2">';
    echo $PopupTitle;
    echo '</th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td valign="top">Duty Name</td>';
    echo '<td ><input type="text" id="duty" name="duty" size="30" value="' . $duty . '"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td  valign="top">Duty Description</td>';
    echo '<td><input type="text" id="description" name="description" size="30" value="' . $description . '"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td  valign="top">Duration (Inc Meals)</td>';
    echo '<td><input type="text" id="duration" name="duration" size="30" value="' . $intDuration . '"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td align="center"><input type="submit" value="'.$ButtonLabel.'" name="Update"></td>';

    if ($id != 0) {
        echo '<td align="right"><img border="0" src="images/delete.gif" width="18" height="17" Style="cursor: pointer" onclick="javascript:DeleteDuty(' . $id . ')"; ></td>';
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
            $('#neweditdutyform').validate({
                rules: {
                    "duty": {
                        required: true,
                    },
                    "description": {
                        required: true,
                    },
                    "duration": {
                        required: true,
                        min: 0,
                        max: 36
                    }
                },
                submitHandler: function (form) {
                    $('input[type="submit"]').prop('disabled', true);
                    $.ajax({
                        type: 'POST',
                        url: 'page-includes/teamskills/view/skills-new-edit-duty.php',
                        data: $('#neweditdutyform').serialize(),
                        success: function (data) {
                            $.facebox.close();
                            FillDutiesList(<?php echo $id?>,<?php echo $intTeamID?>);
                            FillDutiesPage(<?php echo $id?>,<?php echo $intTeamID?>);

                        }
                    });
                }
            })
        });
    </script>
    <?php
}
?>