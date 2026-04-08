<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$intTeamID = $_POST['teamId'];
echo '<form name="chooseDateForm" id="chooseDateForm" method="post" onsubmit="return false;">';
echo '<table class="tablegreysmallnoborder grayBG" width="100%">';
echo '<tr height="35px">';
echo '<td class="medtextbold"><span class="choosecalender chooseDate"><label for="Choose a Week">Date</label></td>';
echo '<td class="medtextbold handcursor">';
echo '<input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="dailyallocationdate"
  name="dailyallocationdate" required="required" class="widthpopupcontrols" onkeyup="submitChooseWeek(event);">';
echo '</td>';
echo '</tr>';
echo '<tr height="35px">';
echo '<td align="left"></td>';
echo '<td align="right">';
echo '<input type="hidden" name="schedulingTeamId" value='.$intTeamID.'>';
echo '<input type="button" value="OK" id="viewEditDailyGrid" style="background-color: #ededed;">';
echo '<input type="button" value="Cancel" onclick="closeChooseDate()">';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</form>';
?>
<script language="JavaScript" type="text/javascript">
$(document).ready( function () {

let currdate = new Date();
let day = currdate.getDate();
let month = currdate.getMonth() + 1;
let year = currdate.getFullYear() + 7;
let dateControlName = '#dailyallocationdate';
let mindate = month+ '-' + day  + '-' + (year - 14);
let yearrange = (year - 14) + ':' + year;
let maxdate = day + '-' + month + '-' + year;
$('#dailyallocationdate').val('');
callDatePicker(dateControlName, mindate, maxdate, yearrange);
});

function callDatePicker(dateControlName,mindate,maxdate,yearrange) {
    $(dateControlName).datepicker({
    changeMonth: true,
    changeYear: true,
    showButtonPanel: false,//no need to display this btn, removed on 21-07-2021
    dateFormat: "dd-mm-yy",
    firstDay : 6,
    yearRange: yearrange,
    inline: true,
    minDate: new Date(mindate),
    maxDate: maxdate,//new date fn is returning invalid date that's we removed it
    onSelect: function (dateText, inst) {
        $(dateControlName).focus();
        }
    });  
}

function submitChooseWeek(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        $("#dailyallocationdate").datepicker("hide");
        $('#viewEditDailyGrid').trigger('click');
    }
}

function closeChooseDate(){
    $('#facebox .close').click();
}
</script>