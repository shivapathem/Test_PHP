<?php
date_default_timezone_set('UTC');
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;

$id = $_REQUEST['id'];
$intTeamID = $_REQUEST['teamid'];
if ($id == 0) {
    echo '&nbsp;';

} else {

    $assignedprogs = json_decode($teamskillobj->ProgrammesAssigned($id), true);

    $allprogs = json_decode($teamskillobj->ListAllProgrammes($intTeamID), true);
    echo '<div class="tableheadersmall medtextboldcentre" style="width:810px; position:relative">';
    echo '<br>Skills Availability...<br><br>';
    echo '</div>';

    echo '<table class="tablesmall">';
    echo '<tr height="40px">';
    echo '<th width="400px">';
    echo 'Skills Assigned';
    echo '</th>';
    echo '<th width="400px">';
    echo 'Available Skills';
    echo '</th>';
    echo '</tr>';
    echo '<tr>';

    echo '<td valign="top">';
    $progsarray = array();
    if (isset($assignedprogs)) {
        echo '<div id="assignedprogsdiv" class="scrolldiv400">';
        echo '<table width="100%" class="stripe">';

        foreach ($assignedprogs as $details) {
            $progid = $details['id'];
            $progsarray[] = $progid;
            echo '<tr>';
            echo '<td class="handcursor" onclick="javascript:RemoveProgFromDuty(' . $progid . ',' . $id . ')";>';
            echo $details['programmename'];
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }
    echo '</td>';

    echo '<td valign="top">';
    if (isset($allprogs)) {
        echo '<div id="unassignedprogsdiv" class="scrolldiv400">';
        echo '<table width="100%" class="stripe">';

        foreach ($allprogs as $details) {
            $progid = $details['ID'];
            //if (!isset($assignedprogs[$progid])) {
            if (!in_array($progid, $progsarray)) {
                echo '<tr>';
                echo '<td class="handcursor" onclick="javascript:handleClick(' . $progid . ',' . $id . ')";>';
                echo $details['programmename'];
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        echo '</div>';
    }

    echo '</td>'; 

    echo '</tr>';
    echo '</table>';

}

?>
<script type="text/javascript">

    var isAvailableSkillsClicked = false;
    function handleClick(progid, dutyid){
        if(!isAvailableSkillsClicked){
            isAvailableSkillsClicked = true;
            AddProgToDuty(progid, dutyid);
        }
    }

    $(document).ready(function () {
        $("table.stripe tr:odd").addClass("odd");
        $("table.stripe tr:even").addClass("even");

        if ($.cookie("assignedprogsdiv") !== null) {
            $("#assignedprogsdiv").scrollTop($.cookie("assignedprogsdiv"));
        }
        $("#assignedprogsdiv").on("scroll", function () {
            $.cookie("assignedprogsdiv", $("#assignedprogsdiv").scrollTop());
        });

        if ($.cookie("unassignedprogsdiv") !== null) {
            $("#unassignedprogsdiv").scrollTop($.cookie("unassignedprogsdiv"));
        }
        $("#unassignedprogsdiv").on("scroll", function () {
            $.cookie("unassignedprogsdiv", $("#unassignedprogsdiv").scrollTop());
        });
    })
</script>