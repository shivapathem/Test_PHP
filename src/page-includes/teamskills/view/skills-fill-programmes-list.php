<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;

$intCurrentID = $_REQUEST["id"];
$intTeamID = $_REQUEST['teamid'];


if (isset($_REQUEST['readonly'])) {
    $intReadOnly = $_REQUEST['readonly'];
} else {
    $intReadOnly = 0;
}

$programmes = json_decode($teamskillobj->ListAllProgrammes($intTeamID), true);

echo '<div class="tableheadersmall medtextboldcentre" style="width:99%; position:relative">';
echo '<br>Skills<br><br>';
if ($intReadOnly == 0) {
    echo '<div class="DutyCellBottomRight"><img border="0" src="images/add.png" tabindex="0" width="16" height="16" Style="cursor: pointer" onclick="javascript:EditProgramme(0, ' . $intTeamID . ')";></div>';
}
echo '</div>';
echo '<div class="duityskillScroll">';
echo '<table id="skillsstafftable-' . $intTeamID . '" class="tablesmall compact stripe" width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th>';

echo '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
if (isset($programmes)) {
    foreach ($programmes as $progname) {
        $id = $progname['ID'];
        if ($intReadOnly == 0) {
            echo '<tr id="tr' . $id . '" onclick="javascript:FillProgsStaffPage(' . $id . ',' . $intTeamID . ')"; ondblclick="javascript:EditProgramme(' . $id . ',' . $intTeamID . ')";>';
        } else {
            echo '<tr id="tr' . $id . '" onclick="javascript:FillProgsStaffPage(' . $id . ',' . $intTeamID . ')";>';
        }
        echo '<td colspan="2" class="handcursor">';
        echo $progname['programmename'];
        echo '</td>';
        echo '</tr>';
    }
}
echo '</tbody>';
echo '</table>';
echo '</div>';
?>
<script type="text/javascript">
    $(document).ready(function () {

        var table = $("#skillsstafftable-<?php echo $intTeamID?>").DataTable({
            paging: false,
            scrollY: 400,
            info: false,
            stateSave: true,
            "initComplete": function (settings, json) {
                //DoResize();
            }
        });
        yadcf.init(table, [
            {
                column_number: 0,
                filter_type: 'text'
            },
        ]);
        $('.yadcf-filter-reset-button').attr('aria-label', 'Clear Filter');

        $('#skillsstafftable-<?php echo $intTeamID?> tbody').on('click', 'tr', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });
    })

</script>