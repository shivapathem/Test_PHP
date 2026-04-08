<?php
date_default_timezone_set('UTC');
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;

$id = $_REQUEST['id'];

if ($id == 0) {
    echo '&nbsp;';
} else {

    $assignedprogs = json_decode($teamskillobj->ProgrammesAssigned($id), true);
    $arrCanDoDuty = json_decode($teamskillobj->GetStaffCanDoDuty($id), true);

    echo '<div class="tableheadersmall medtextboldcentre" style="height:40px; width:805px; position:relative">';
    echo '<br>Skills and Staff...';
    echo '</div>';


    echo '<table class="tablesmall">';
    echo '<tr height="40px">';
    echo '<th width="400px">';
    echo 'Skills';
    echo '</th>';
    echo '<th width="400px">';
    echo 'Staff Who Can Do This Duty';
    echo '</th>';
    echo '</tr>';
    echo '<tr>';

    echo '<td valign="top">';
    echo '<div id="sdassignedprogsdiv" class="scrolldiv400">';
    $progsarray = array();
    if (isset($assignedprogs)) {
        echo '<table class="tablesmall stripe" width="100%">';
        foreach ($assignedprogs as $details) {
            $progid = $details['id'];
            $progsarray[] = $progid;
            echo '<tr>';
            echo '<td>';
            echo $details['programmename'];
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    echo '</div>';
    echo '</td>';

    echo '<td valign="top">';
    echo '<div id="sdassignedstaffdiv" class="scrolldiv400">';
    echo '<table class="tablesmall stripe" width="100%">';
    if (isset($arrCanDoDuty)) {
        foreach ($arrCanDoDuty as $strFullName) {
            echo '<tr>';
            echo '<td>';
            echo $strFullName['FullName'];
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
    echo '</td>';
    echo '</div>';
    echo '</tr>';
    echo '</table>';
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("table.stripe tr:odd").addClass("odd");
        $("table.stripe tr:even").addClass("even");

        if ($.cookie("sdassignedprogsdiv") !== null) {
            $("#sdassignedprogsdiv").scrollTop($.cookie("sdassignedprogsdiv"));
        }
        $("#sdassignedprogsdiv").on("scroll", function () {
            $.cookie("sdassignedprogsdiv", $("#sdassignedprogsdiv").scrollTop());
        });

        if ($.cookie("sdassignedstaffdiv") !== null) {
            $("#sdassignedstaffdiv").scrollTop($.cookie("sdassignedstaffdiv"));
        }
        $("#sdassignedstaffdiv").on("scroll", function () {
            $.cookie("sdassignedstaffdiv", $("#sdassignedstaffdiv").scrollTop());
        });

    })
</script>