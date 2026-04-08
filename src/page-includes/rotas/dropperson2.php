<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$pageid = 24;
$teamId = $_REQUEST['teamId'];
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $teamId);

    $intRotaID = $_REQUEST["RotaID"];
    $intWeekID = $_REQUEST['WeekID'];
    $ScheduledPersonID = $_REQUEST['ScheduledPersonID'];
    $Date1 = '';
    $Date2 = '';
    $intType = 1;
    $intAreaID = $_SESSION["user"]["AreaID"];

    if ($Date1 == '') {
        $RotaDetailsJSON = GetRotaDetailsByID ($intType, $intAreaID, $intRotaID);
        $RotaDetails = json_decode($RotaDetailsJSON,true);
        $Date1 = date('d-m-Y', strtotime(str_replace('/','-',$RotaDetails["RotaStartDate"])));
    }
    if ($Date2 == '') {
        $AddDays = (7 * $RotaDetails["WeeksInRota"]) - 1;
        $Date2 = '01-01-9999';//date('d-m-Y', strtotime($Date1. ' + '.$AddDays.' days'));
    }
    if (($permissions->canview == 1) && ($permissions->cancreate == 1)) {
        echo '<div style="width: 600px">';
        echo '<form id="neweditrotaperson">';
        echo '<table id="rotapersonnewedittable" class="smalltable bluetable" width="100%">';
        echo '<thead>';
        echo '<tr>';
        echo '<th colspan="4">What date range would you like for this Person in the Rota...</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">People Rota From</td>';
        echo '<td width="35%">';
        echo '<input id="startdate" name="startdate" type="text" size="15" value="" autocomplete="off" />';
        echo '</td>';
        echo '<td class="lightblue" width="15%">People Rota Until</td>';
        echo '<td width="35%">';
        echo '<input id="enddate" name="enddate" type="text" size="15" value="" autocomplete="off"/>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td></td>';
        echo '<td colspan="4"><input name="submit" id="submit" type="button" value="Submit"></input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="cancel" id="cancel" type="button" value="Close"></input></td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo '<input type="hidden" name="RotaID" id="RotaID" value="' . $intRotaID . '">';
        echo '<input type="hidden" name="RotaStartDateHide" id="RotaStartDateHide" value="' . $RotaDetails["RotaStartDate"] . '">';
        echo '<input type="hidden" name="WeekID" id="WeekID" value="' . $intWeekID . '">';
        echo '<input type="hidden" name="ScheduledPersonID" id="ScheduledPersonID" value="' . $ScheduledPersonID . '">';
        echo '</form>';
        echo '</div>';
    }
?>

<script type="text/javascript">
$(document).ready(function(){
    if (<?php echo empty($permissions->cancreate)? 0:$permissions->cancreate ?> == 0) {
        $( '#content' ).load( 'page-includes/no_access.php', function() { });
    }
    else
    {

        $('#cancel').click(function () {
            if ($('#cancel').val() == 'Close') {
                CallDateCalender();
                $.facebox.close();
            }
        });

        $('#submit').click(function () {
            var rotaid = $("#RotaID").val();
            var weekid = $("#WeekID").val();
            var ScheduledPersonID = $("#ScheduledPersonID").val();
            var startdate = $("#startdate").val();
            var enddate = $("#enddate").val();

            if (startdate == "" || startdate == null) {
                customAlertByModel("Start date cannot be null or empty.");
                return;
            }

            if (enddate == "" || enddate == null) {
                enddate = '01-01-9999';
            }
            // Start Date validation for saturday
            var DateNew = $('#startdate').val();
            var SplitDate = DateNew.split("-").reverse().join("-");
            var DayOfWeek = getDayOfWeek(SplitDate);
            if(DayOfWeek != "Saturday"){
                customAlertByModel("Please ensure that your start date is a Saturday.<br/>This allows the Rota pattern to apply for the whole week for this person.");
                return;
            }

            $.ajax({
                url: "page-includes/rotas/dropperson.php",
                type: "POST",
                dataType: "json",
                data: {
                    'RotaPeopleID': 0,
                    'RotaID': rotaid,
                    'WeekID': weekid,
                    'ScheduledPersonID': ScheduledPersonID,
                    'StartDate': startdate,
                    'EndDate': enddate,
                    'teamId': <?php echo $teamId ?>
                },
                success: function (data) {
                    if (data.sqlstatusstring == 'success') {
                        // for successfully deleted record
                        CallDateCalender();
                        ShowPeopleByRota(rotaid);
                        ShowRotaDetails(rotaid, 1, 1);
                        $.facebox.close();
                    } else {
                        customAlertByModel(data.sqlstatusstring);
                    }
                },
                error: function (x, e) {
                    if (x.status == 0) {
                        customAlert('You are offline!!<br/> Please Check Your Network.');
                    } else if (x.status == 404) {
                        customAlert('Requested URL not found.');
                    } else if (x.status == 500) {
                        customAlert('Internal Server Error.');
                    } else if (e == 'parsererror') {
                        customAlert('Error.<br/>Parsing JSON Request failed.');
                    } else if (e == 'timeout') {
                        customAlert('Request Time out.');
                    } else {
                        customAlert('Unknown Error.<br/>' + x.responseText);
                    }
                }
            });
        });
    }
});

CallDateCalender();
function CallDateCalender() {
    let rotaStartDateMinCal = $("#RotaStartDateHide").val();
    let rotastartdatesplit = rotaStartDateMinCal.split('/');
    let startdateMinDate = rotastartdatesplit[0]+'-'+rotastartdatesplit[1]+'-'+rotastartdatesplit[2];
    $("#startdate").datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: false,//no need to display this btn, removed on 21-07-2021
        dateFormat: "dd-mm-yy",
        yearRange: '1995:2050',
        inline: true,
        minDate: startdateMinDate,//startdateMinDate,
        maxDate: '01-01-2050'//new date fn is returning invalid date that's we removed it.
    });
    $('#startdate').datepicker('option', 'firstDay', 6);

    $("#enddate").datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: false,//no need to display this btn, removed on 21-07-2021
        dateFormat: "dd-mm-yy",
        yearRange: '1995:2050',
        inline: true,
        minDate: startdateMinDate,//startdateMinDate,
        maxDate: '01-01-2050'//new date fn is returning invalid date that's we removed it.
    });
    $('#enddate').datepicker('option', 'firstDay', 6)
}
$(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
$(function() {
    $( "button" )
      .button()
});
</script>
