<?php
date_default_timezone_set('UTC');
session_start();
include_once __DIR__.'../../../../function-includes/common/classCommonDBFunctions.php';
//call the class
$commonDbobj = new classCommonDBFunctions();
$intTeamID = $_REQUEST['teamid'];
$scheduledType =  1;

$allstaff = json_decode($commonDbobj->GetListStaffByType($intTeamID,$scheduledType), true);
    echo '<table id="spdstafflist-' . $intTeamID . '" class="tablesmall compact stripe">';
    echo '<thead>';
    echo '<tr height="30px">';
    echo '<th width="300px">';
    echo 'Staff';
    echo '</th>';
    echo '</tr>';
    echo '</thesd>';
    echo '<tbody>';

    if(isset($allstaff)) {
        foreach ($allstaff as $staffid => $details) {
            echo '<tr>';
            echo '<td class="handcursor" onclick="javascript:ShowProgsCanDo(\'' . $details['staffnumber'] . '\')";>';
            echo $details['name'];
            echo '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';

?>
<script type="text/javascript">
    $(document).ready(function () {

        var table = $("#spdstafflist-<?php echo $intTeamID?>").DataTable({
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

        $('#spdstafflist-<?php echo $intTeamID?> tbody').on('click', 'tr', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });

    });
</script>