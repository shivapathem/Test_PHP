<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';

$intDutyID = $_REQUEST["dutyid"];
$intDutyType = $_REQUEST["dutytypeid"];
$intStaffID = $_SESSION['user']['StaffID'];
$intAreaID = $_SESSION['user']['AreaID'];
$intIsRota = $_SESSION['isrota'];
if (!(isset($intIsRota))) {
    $intIsRota = 1;
    $_SESSION['isrota'] = 1;
}
$intType = 0;

//Check User Authentication
if ($intIsRota == 1) {
    $_SESSION['isrota'] = 1;
    $pageid = 3; // Rota form id
    // Call User Permission function.
    $permissions = getUserRolePermissions($pageid);
}
else {
    $_SESSION['isrota'] = 0;
    $pageid = 1; //Master Duties form id.
    // Call User Permission function.
    $permissions = getUserRolePermissions($pageid);
}

//check if we show the form
if ($permissions->canview == 1) {
    echo '<div style="width: 100%;" class="tableFixHead">';
    echo '<form id="neweditdutyrotas">';
    echo '<table id="dutyrotasnewedittable" class="smalltable bluetable" width="100%" role="presentation">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Rota Name</th>';
    echo '<th>Weeks In Rota</th>';
    echo '<th>Week Line</th>';
    echo '<th>Day</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    if ($intDutyID > 0) {
        $rsDutyRotasJson = GetRotasByDutyID($intDutyID);
        $rsDutyRotas = json_decode($rsDutyRotasJson, true);
        $rowCount = count($rsDutyRotas);
        if ($rowCount > 0) {
            for ($row = 0; $row < $rowCount; $row++) {
                echo '<tr>';
                echo '<td>';
                echo $rsDutyRotas[$row]['RotaName'];
                echo '</td>';
                echo '<td>';
                echo $rsDutyRotas[$row]['WeeksInRota'];
                echo '</td>';
                echo '<td>';
                echo $rsDutyRotas[$row]['RotaWeek'];
                echo '</td>';
                echo '<td>';
                echo $rsDutyRotas[$row]['DOTW'];
                echo '</td>';
                echo '</tr>';
            }
        }
    } else {
        echo '<tr>';
        echo '<td colspan="5">';
        echo 'No Record found';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</form>';
    echo '</div>';
}

?>
<script type="text/javascript">
    $(document).ready(function () {
        if (<?php echo empty($permissions->canview) ? 0 : $permissions->canview ?> == 0) {
            customAlert('You do not have view privileges for this form.');
        }
        let table = $('#dutyrotasnewedittable').DataTable({
            "bPaginate": false, //hide pagination
            "bInfo": false,     // hide showing entries
            "columnDefs": [
                {"orderable": true, "targets": 0},
                {"orderable": true, "targets": 1},
                {"orderable": true, "targets": 2},
                {"orderable": true, "targets": 3}
            ]
        });
    })
</script>
 