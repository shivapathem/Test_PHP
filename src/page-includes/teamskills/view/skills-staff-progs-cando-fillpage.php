<?php
session_start();
include_once '../process/classTeamskills.php';
//call the common class object
$teamskillobj = new classTeamskills;

if (isset($_SESSION['bst'])) {
    $bst = $_SESSION['bst'];
} else {
    $bst = date("I");
    $_SESSION['bst'] = $bst;
}

$intTeamID = $_REQUEST['teamid'];
$strStaffNumber = $_REQUEST['staffnumber'];
$isSysAdmin = $_SESSION['user']['SysAdmin'];
$isMySkillFlag = $_REQUEST['isMySkillFlag'] ?? 0;

if ($strStaffNumber == 0) {
    echo '&nbsp;';
} else {
    // Get all the skills  based on teamID
    $arrAllProgs = json_decode($teamskillobj->ListAllProgrammes($intTeamID), true);

    // Now get the people who can do this duty by Staff Number
    $arrprogscando = json_decode($teamskillobj->GetStaffProgsCanDo($strStaffNumber, $intTeamID), true);

    // Now get all the duties with can do/ can't do in by Team ID
    $arrallduties = json_decode($teamskillobj->ListAllDutiesWithProgrammes($bst, $intTeamID, $strStaffNumber), true);

    $hasAssignedDuties = false;
    if (isset($arrallduties)) {
        foreach ($arrallduties as $dutyid => $thisduty) {
            if ($thisduty['StaffDutyFlag'] == 1) {
                $hasAssignedDuties = true;
                break;
            }
        }
    }

    if ($isMySkillFlag == 1) {
        echo '<table class="tablesmall">';
        echo '<tr height="30px">';
        echo '<th width="250px">';
        echo 'Skills Assigned';
        echo '</th>';
        if ($hasAssignedDuties) {
            echo '<th width="250px">';
            echo 'Duties Assigned';
            echo '</th>';
        }
        echo '</tr>';
        echo '<tr>';
        echo '<td valign="top">';
        $progsarray = array();
        if (isset($arrprogscando)) {
            if (count($arrprogscando) > 20) {
                $classNameProgCanDo = 'scrolldiv200';
            } else {
                $classNameProgCanDo = 'scrolldiv200 auto-height';
            }
            echo '<div class="' . $classNameProgCanDo . '">';
            echo '<table width="100%" class="stripe">';
            foreach ($arrprogscando as $thisprog) {
                $progid = $thisprog['id'];
                $progsarray[] = $progid;

                echo '<tr>';
                echo '<td>';
                echo $thisprog['programmename'];
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        } else {
            echo 'No Skills Defined';
        }
        echo '</td>';
        if ($hasAssignedDuties) {
            echo '<td valign="top">';
            if (isset($arrallduties)) {
                if (count($arrallduties) > 20) {
                    $classNameAllDuties = 'scrolldiv250';
                } else {
                    $classNameAllDuties = 'scrolldiv250 auto-height';
                }
                // The duties they can do
                echo '<div class="' . $classNameAllDuties . '">';
                echo '<table width="100%" class="stripe">';
                foreach ($arrallduties as $dutyid => $thisduty) {
                    if ($thisduty['StaffDutyFlag'] == 1) {
                        echo '<tr>';
                        echo '<td>';
                        echo $thisduty['dutyname'];
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                echo '</table>';
                echo '</div>';
            } else {
                echo 'No duties defined';
            }
            echo '</td>';
        }
        echo '</tr>';
        echo '</table>';
    } else {
        echo '<table class="tablesmall">';
        echo '<tr height="30px">';
        echo '<th width="250px">';
        echo 'Skills Assigned';
        echo '</th>';
        echo '<th width="250px">';
        echo 'Skills Not Assigned';
        echo '</th>';
        echo '<th width="250px">';
        echo 'Duties Assigned';
        echo '</th>';
        echo '<th width="250px">';
        echo 'Duties Not Assigned';
        echo '</th>';
        echo '</tr>';
        echo '<tr>';

        echo '<td valign="top">';
        $progsarray = array();
        if (isset($arrprogscando)) {
            if (count($arrprogscando) > 20) {
                $classNameProgCanDo = 'scrolldiv200';
            } else {
                $classNameProgCanDo = 'scrolldiv200 auto-height';
            }
            echo '<div class="' . $classNameProgCanDo . '">';
            echo '<table width="100%" class="stripe">';
            foreach ($arrprogscando as $thisprog) {
                $progid = $thisprog['id'];
                $progsarray[] = $progid;

                echo '<tr>';
                echo '<td>';
                echo $thisprog['programmename'];
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        } else {
            echo 'No Skills Defined';
        }
        echo '</td>';

        echo '<td valign="top">';
        if (isset($arrAllProgs)) {
            if (count($arrAllProgs) > 20) {
                $classNameAllProgs = 'scrolldiv250';
            } else {
                $classNameAllProgs = 'scrolldiv250 auto-height';
            }
            echo '<div class="' . $classNameAllProgs . '">';
            echo '<table width="100%" class="stripe">';
            foreach ($arrAllProgs as $thisprog) {
                $progid = $thisprog['ID'];

                if (!in_array($progid, $progsarray)) {

                    echo '<tr>';
                    echo '<td>';
                    echo $thisprog['programmename'];
                    echo '</td>';
                    echo '</tr>';
                }
            }
            echo '</div>';
            echo '</table>';
        } else {
            echo 'No Skills Defined';
        }
        echo '</td>';

        echo '<td valign="top">';
        if (isset($arrallduties)) {
            if (count($arrallduties) > 20) {
                $classNameAllDuties = 'scrolldiv250';
            } else {
                $classNameAllDuties = 'scrolldiv250 auto-height';
            }
            // The duties they can do
            echo '<div class="' . $classNameAllProgs . '">';
            echo '<table width="100%" class="stripe">';
            foreach ($arrallduties as $dutyid => $thisduty) {
                if ($thisduty['StaffDutyFlag'] == 1) {
                    echo '<tr>';
                    echo '<td>';
                    echo $thisduty['dutyname'];
                    echo '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
            echo '</div>';
        } else {
            echo 'No duties defined';
        }
        echo '</td>';
        echo '<td valign="top">';
        if (isset($arrallduties)) {
            if (count($arrallduties) > 20) {
                $classNameAllDuties = 'scrolldiv250';
            } else {
                $classNameAllDuties = 'scrolldiv250 auto-height';
            }
            // The duties they can't do
            echo '<div class="' . $classNameAllProgs . '">';
            echo '<table width="100%" class="stripe">';
            foreach ($arrallduties as $dutyid => $thisduty) {
                if ($thisduty['StaffDutyFlag'] == 0) {
                    echo '<tr>';
                    echo '<td>';
                    echo $thisduty['dutyname'];
                    echo '</td>';
                    echo '</tr>';
                    // Remove it from the array
                    unset($arrallduties[$dutyid]);
                }
            }
            echo '</table>';
            echo '</div>';
        } else {
            echo 'No duties defined';
        }
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    }
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("table.stripe tr:odd").addClass("odd");
        $("table.stripe tr:even").addClass("even");
    })

    function AddProg(progid, dutyid) {
        $.post("page-includes/ajax-calls/skills-add-prog-to-duty.php", {
            dutyid: dutyid,
            progid: progid
        },
            function (data, status) {
                FillDutiesPage(dutyid)
            }
        )
    }

    function RemoveProg(progid, dutyid) {
        $.post("page-includes/ajax-calls/skills-remove-prog-from-duty.php", {
            dutyid: dutyid,
            progid: progid
        },
            function (data, status) {
                FillDutiesPage(dutyid)
            }
        )
    }
</script>