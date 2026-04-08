<?php
session_start();
include_once '../../../function-includes/common/classCommonDBFunctions.php';
$commonDbObj = new classCommonDBFunctions();
//call the common class object
$strStaffNumber = $_SESSION['user']['StaffNumber'];
$intStaffId = $_SESSION['user']['StaffID'];
$scheduledType = 1;
$isMySkillFlag = $_POST['isMySkillFlag'] ? $_POST['isMySkillFlag'] : 0;

/* Get Default team of looged user */
$arrStaffTeams = json_decode($commonDbObj->UserTeamByScheduledTypeByStaffId($strStaffNumber, $scheduledType), true);
echo '<h1 class="sr-only">My Skills</h1>';
echo '<div class="tableheadersmall" style="width: 100%">';
echo '<br><b>Below are your skills in the team you are scheduled in.</b><br><br>';
echo '</div>';
echo '<br>';
if (!empty($arrStaffTeams)) {
    foreach ($arrStaffTeams as $dataTeamvalue) {

        echo '<div>';
        echo '<div class="tableheadersmall medtextboldcentre"><br><h2>' . $dataTeamvalue['schedulingTeamName'] . '</h2><br></div>';
        echo '<div id="staffskills-' . $dataTeamvalue['schedulingTeamId'] . '">';
        echo '</div>';
        echo '</div>';
    }
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        <?php
        foreach ($arrStaffTeams as $dataTeamvalue) {
            echo 'ShowStaffSkills(' . $dataTeamvalue['schedulingTeamId'] . ');';
        }
        ?>

    })

    function ShowStaffSkills(teamid) {
        $.post("page-includes/teamskills/view/skills-staff-progs-cando-fillpage.php", {
            staffnumber: '<?php echo $strStaffNumber ?>',
            teamid: teamid,
            isMySkillFlag: '<?php echo $isMySkillFlag; ?>'
        },
            function (data, status) {
                $('#staffskills-' + teamid).html(data);
            }
        )
    }
</script>