<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$intID = $_REQUEST["dutyid"];
$intDutyType = $_REQUEST["dutytypeid"];
$ddlRotaTeams = $_REQUEST["ddlRotaTeams"];

$prog = getAllLabels();
$allProg = json_decode($prog);
if (!(isset($intIsRota))) {
    $intIsRota = 1;
    $_SESSION['isrota'] = 1;
}
$intDBDutyType = ($intDutyType + 1);
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'];
$intType = 0;
$intShowForm = 1;
$isEdit = $_REQUEST['isEdit'];


//Check User Authentication
if ($intIsRota == 1) {
    $_SESSION['isrota'] = 1;
    $pageid = 3; // Rota form id
    // Call User Permission function.
    $permissions = getUserRolePermissions($pageid, $ddlRotaTeams);
}
else {
    $_SESSION['isrota'] = 0;
    $pageid = 1; //Master Duties form id.
    // Call User Permission function.
    $permissions = getUserRolePermissions($pageid, $ddlRotaTeams);
}

//check if we show the form
if ($permissions->canview == 1) {
    $monday =  $tuesday =  $wednesday = $thursday = $friday = $saturday = $sunday = $intDutyColourID =0;
    $starttime = $endtime = $duration  = $intTeamID=0;
    $StartYear = date("Y");
    $StartWeek= date("W");
    $EndYear=  '2035';
    $EndWeek =  '52';
    $dutyName =  '';
    $buttonvalue = 'Create Duty';
    $labelIds    = [];
    $isNeedCovering = 1;
    $isOverrideOver12 = 1;
    
    if ($intID > 0) {
        $rsDuty = GetDutyDetailsByID($intID);
        $row = json_decode($rsDuty, true);
        $dutyName = $row['DutyName'] ;
        $starttime = $row['StartTime'];
        $endtime = $row['EndTime'];
        $duration = $row['Duration'];
        $monday = $row['Monday'];
        $tuesday = $row['Tuesday'];
        $wednesday = $row['Wednesday'];
        $thursday = $row['Thursday'];
        $friday = $row['Friday'];
        $saturday = $row['Saturday'];
        $sunday = $row['Sunday'];
        $intTeamID = $row['TeamID'];
        $StartYear = substr($row['StartWeek'], 0, 4);
        $EndYear = substr($row['EndWeek'], 0, 4);
        $StartWeek = substr($row['StartWeek'], 4,2);
        $EndWeek = substr($row['EndWeek'], 4, 2);
        $breakTime = $row['BreakTime'];
        $labelIds = array_filter([$row['DutyProgramId1'], $row['DutyProgramId2'], $row['DutyProgramId3'], $row['DutyProgramId4'], $row['DutyProgramId5'], $row['DutyProgramId6']]);

        $intBreakTimeHour = intval((int)$breakTime / 3600);
        $intBreakTimeMinute = intval(((int)$breakTime % 3600) / 60);
        $breakTimehourOptions = PopulateHoursDropDown($intBreakTimeHour);
        $breakTimeminuteOptions = PopulateMinutesDropDown_limited($intBreakTimeMinute);


        $intDutyColourID = $row['DutyColourID'];
        $strForeColour = $row['ForeColour'];
        $strBackColour = $row['BackColour'];
        $isNeedCovering = $row['IsNeedCovering'] ?? 1;
        $isOverrideOver12 = $row['IsOverrideOver12'] ?? 1;
        $buttonvalue = "Update Duty";
    }else{
        $breakTimehourOptions = PopulateHoursDropDown(1);
        $breakTimeminuteOptions = PopulateMinutesDropDown_limited(15);
    }
    $rsColours = GetDutyColourList($intTeamID);
    $ColourOptions = PopulateDutyColoursDropDown($rsColours, $intDutyColourID, $intID);
    
    $intstartHour = intval($starttime / 3600);
    $intstartHour = strlen(trim($intstartHour))== 1 ? "0".$intstartHour : $intstartHour;
    $intstartMinute = intval(($starttime % 3600) / 60);
    $intstartMinute = strlen(trim($intstartMinute))== 1 ? "0".$intstartMinute : $intstartMinute;
    $intstartTime   =   $intstartHour . ':' . $intstartMinute;

    $intendHour = intval($endtime / 3600);
    $intendHour = strlen(trim($intendHour))== 1 ? "0".$intendHour : $intendHour;
    $intendMinute = intval(($endtime % 3600) / 60);
    $intendMinute = strlen(trim($intendMinute))== 1 ?  "0".$intendMinute : $intendMinute;
    $intendTime   =   $intendHour . ':' . $intendMinute;

    $intdurationHour = intval($duration / 3600);
    $intdurationMinute = intval(($duration % 3600) / 60);
    
    $durationhourOptions = PopulateHoursDropDown($intdurationHour);
    $durationminuteOptions = PopulateMinutesDropDown($intdurationMinute);

    $MondayOptions = PopulateNumberDropDown(intval($monday), 0, 30);
    $TuesdayOptions = PopulateNumberDropDown(intval($tuesday), 0, 30);
    $WednesdayOptions = PopulateNumberDropDown(intval($wednesday), 0, 30);
    $ThursdayOptions = PopulateNumberDropDown(intval($thursday), 0, 30);
    $FridayOptions = PopulateNumberDropDown(intval($friday), 0, 30);
    $SaturdayOptions = PopulateNumberDropDown(intval($saturday), 0, 30);
    $SundayOptions = PopulateNumberDropDown(intval($sunday), 0, 30);

    $YearFromDB = PopulateYearsTimeDimension();
    $YearAvaialbleFrom = GenerateYearOptions($YearFromDB, $StartYear);
    $YearAvaialbleTo = GenerateYearOptions($YearFromDB, $EndYear);

    $WeekAvaialbleFrom = PopulateWeekNumberDropDown($StartWeek,$StartYear,$intID);
    $WeekAvaialbleTo = PopulateWeekNumberDropDown($EndWeek,$EndYear,$intID);
    
    $rsAreaTeams = GetUserAreaTeamsList ($intUserID);
    $TeamOptions = TeamListDropDown ($rsAreaTeams,$intTeamID);
    
    $lablestr  = '';
    foreach ($allProg as $key => $value)
    {
        if(in_array($value->ID, $labelIds))
        {
            $lablestr   .='<option value="'.$value->ID.'" selected>'.$value->Programme .'</option>';
        }else
        {
            $lablestr   .='<option value="'.$value->ID.'">'.$value->Programme .'</option>';
        }
    }
    
    echo '<div style="width: 560px">';
    echo '<table id="dutynewedittabledetail" class="redtable smalltable bluetable" width="100%" role="presentation">';
    echo '<tbody>';
    echo '<tr><td colspan="4" class="hiddenErrorDiv" id="errorDivFP"></td></tr>';
    if ((($permissions->canmodify == 1) || ($permissions->cancreate == 1)) && $isEdit == 1) {
        echo '<tr>';
        echo '<td class="lightblue">Break Duration</td>';
        echo '<td>';
        echo '   <select class="chosen-select" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 40%;">';
        echo ''.$breakTimehourOptions.'';
        echo '   </select> : ';
        echo '   <select class="chosen-select" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 40%;">';
        echo ''.$breakTimeminuteOptions.'';
        echo '   </select></br>';
        echo "</td>";
        echo '<td class="lightblue">Label</td>
                <td>
                    <select data-placeholder="Select label" class="chosen-select" multiple name="labelIds[]" id="labelIds" style="background-color:white; width: 100%;">
                        '. $lablestr .'
                    </select>
                </td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="lightblue" width="15%">Start Time<span class="required">*</span></td>';
        echo '<td width="35%">';
        echo  '<input id="starttimemasterduty" name="starttimemasterduty" type="text" maxlength="5" class="ui-timepicker-input" size="10" value="'.$intstartTime.'" onChange="dutyTimeSanitization(starttimemasterduty.value, '."'starttimemasterduty'".')" autocomplete="off">';
        echo '</td>';
        echo '<td class="lightblue" width="15%">End Time<span class="required">*</span></td>';
        echo '<td width="35%">';
        echo  '<input id="endtimemasterduty" name="endtimemasterduty" type="text" maxlength="5" class="ui-timepicker-input" size="10" value="'.$intendTime.'" onChange="dutyTimeSanitization(endtimemasterduty.value, '."'endtimemasterduty'".')" autocomplete="off">';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="lightblue" width="15%">Scheduling Team<span class="required">*</span></td>';
        echo '<td width="35%"> ';
        echo '   <select class="chosen-select" name="ddldutyteam" id="ddldutyteam" style="background-color:white; width: 100%;">';
        echo ''.$TeamOptions.'';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Duty Colour</td>';
        echo '<td width="35%" id="dutydiv">';
        echo '   <select class="chosen-select" name="ddldutycolour" id="ddldutycolour" style="background-color:white; width: 100%;">';
        echo ''.$ColourOptions.'';
        echo '   </select>';
        echo ' <input type="hidden" name="dutyColourID" id="dutyColourID" value="'.$intDutyColourID.'">';
        echo '</td>';

        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Duty Available From<span class="required">*</span> </td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="yearavaialblefrom" id="yearavaialblefrom" style="background-color: white; width: 40%;">';
        echo ''.$YearAvaialbleFrom.'';
        echo '   </select> / ';
        echo '   <select class="chosen-select" name="weekavaialblefrom" id="weekavaialblefrom" style="background-color: white; width: 40%;">';
        echo ''.$WeekAvaialbleFrom.'';
        echo '   </select></br>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Duty Available To<span class="required">*</span></td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="yearavaialbleto" id="yearavaialbleto" style="background-color: white; width: 40%;">';
        echo ''.$YearAvaialbleTo.'';
        echo '   </select> / ';
        echo '   <select class="chosen-select" name="weekavaialbleto" id="weekavaialbleto" style="background-color: white; width: 40%;">';
        echo ''.$WeekAvaialbleTo.'';
        echo '   </select></br>';
        echo '</td>';
        echo '</tr>';
        echo '</td>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Allocations Per Day</td>';
        echo '<td width="35%">';
        echo '<table class="smalltable bluetable" style="border:none;" width="100%" role="presentation">';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Saturday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numsaturday" id="numsaturday" style="background-color: white; width: 100%;">';
        echo ''.$SaturdayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Sunday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numsunday" id="numsunday" style="background-color: white; width: 100%;">';
        echo ''.$SundayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Monday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="nummonday" id="nummonday" style="background-color: white; width: 100%;">';
        echo ''.$MondayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Tuesday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numtuesday" id="numtuesday" style="background-color: white; width: 100%;">';
        echo ''.$TuesdayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">&nbsp;</td>';
        echo '<td width="35%">';
        echo '<table class="smalltable bluetable" style="border:none;" width="100%" role="presentation">';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Wednesday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numwednesday" id="numwednesday" style="background-color: white; width: 100%;">';
        echo ''.$WednesdayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Thursday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numthursday" id="numthursday" style="background-color: white; width: 100%;">';
        echo ''.$ThursdayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Friday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numfriday" id="numfriday" style="background-color: white; width: 100%;">';
        echo ''.$FridayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</td>';
        echo '</tr>';        
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Doesn\'t Need Covering</td>';
        echo '<td colspan="">';
        echo '<input id="isNeedCovering" name="isNeedCovering" type="checkbox" ' . ($isNeedCovering == 0 ? 'checked' : '') . '>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Disable Over 12</td>';
        echo '<td colspan="">';
        echo '<input id="isOverrideOver12" name="isOverrideOver12" type="checkbox" ' . ($isOverrideOver12 == 0 ? 'checked' : '') . '>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td></td>';
        echo '<td colspan="10"><input name="submit" type="submit" value="'.$buttonvalue.'"></td>';
        echo '</tr>';

        echo '<input type="hidden" name="dutyid" id ="dutyid" value="'.$intID.'">';
        echo '<input type="hidden" name="olddutyname" value="'.$dutyName.'">';
        echo '<input type="hidden" name="dutytype" id="dutytype" value="'.$intDutyType.'">';

    }
    else {

        echo '<tr>';
        echo '<td class="lightblue">Break Duration</td>';
        echo '<td>';
        echo '   <select class="chosen-select" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 40%;" disabled>';
        echo ''.$breakTimehourOptions.'';
        echo '   </select> : ';
        echo '   <select class="chosen-select" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 40%;" disabled>';
        echo ''.$breakTimeminuteOptions.'';
        echo '   </select></br>';
        echo "</td>";
        echo '<td class="lightblue">Label</td>
                <td>
                    <select multiple data-placeholder="Select label" class="chosen-select" name="labelIds[]" id="labelIds" style="background-color:white; width: 100%;" disabled>
                        '. $lablestr .'
                    </select>
                </td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Start Time</td>';
        echo '<td width="35%">';
        echo  '<input id="starttimemasterduty" name="starttimemasterduty" type="text" maxlength="5" class="ui-timepicker-input" size="10" value="'.$intstartTime.'" onChange="dutyTimeSanitization(starttimemasterduty.value, '."'starttimemasterduty'".')" autocomplete="off" disabled>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">End Time</td>';
        echo '<td width="35%">';
        echo  '<input id="endtimemasterduty" name="endtimemasterduty" type="text" maxlength="5" class="ui-timepicker-input" size="10" value="'.$intendTime.'" onChange="dutyTimeSanitization(endtimemasterduty.value, '."'endtimemasterduty'".')" autocomplete="off" disabled>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="lightblue" width="15%">Scheduling Team</td>';
        echo '<td width="35%"> ';
        echo '   <select class="chosen-select" name="ddldutyteam" id="ddldutyteam" style="background-color:white; width: 100%;">';
        echo ''.$TeamOptions.'';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Duty Colour</td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="ddldutycolour" id="ddldutycolour" style="background-color:white; width: 100%;" disabled>';
        echo ''.$ColourOptions.'';
        echo '   </select>';
        echo ' <input type="hidden" name="dutyColourID" id="dutyColourID" value="'.$intDutyColourID.'">';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Duty Available From </td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="yearavaialblefrom" id="yearavaialblefrom" style="background-color: white; width: 40%;" disabled>';
        echo ''.$YearAvaialbleFrom.'';
        echo '   </select> / ';
        echo '   <select class="chosen-select" name="weekavaialblefrom" id="weekavaialblefrom" style="background-color: white; width: 40%;" disabled>';
        echo ''.$WeekAvaialbleFrom.'';
        echo '   </select></br>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Duty Available To</td>';
        echo '<td width="35%">';
        echo '   <select class="chosen-select" name="yearavaialbleto" id="yearavaialbleto" style="background-color: white; width: 40%;" disabled>';
        echo ''.$YearAvaialbleTo.'';
        echo '   </select> / ';
        echo '   <select class="chosen-select" name="weekavaialbleto" id="weekavaialbleto" style="background-color: white; width: 40%;" disabled>';
        echo ''.$WeekAvaialbleTo.'';
        echo '   </select></br>';
        echo '</td>';
        echo '</tr>';
        echo '</td>';
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Allocations Per Day</td>';
        echo '<td width="35%">';
        echo '<table class="smalltable bluetable" style="border:none;" width="100%" role="presentation">';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Saturday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numsaturday" id="numsaturday" style="background-color: white; width: 100%;" disabled>';
        echo ''.$SaturdayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Sunday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numsunday" id="numsunday" style="background-color: white; width: 100%;" disabled>';
        echo ''.$SundayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Monday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="nummonday" id="nummonday" style="background-color: white; width: 100%;" disabled>';
        echo ''.$MondayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Tuesday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numtuesday" id="numtuesday" style="background-color: white; width: 100%;" disabled>';
        echo ''.$TuesdayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">&nbsp;</td>';
        echo '<td width="35%">';
        echo '<table class="smalltable bluetable" style="border:none;" width="100%" role="presentation">';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Wednesday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numwednesday" id="numwednesday" style="background-color: white; width: 100%;" disabled>';
        echo ''.$WednesdayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Thursday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numthursday" id="numthursday" style="background-color: white; width: 100%;" disabled>';
        echo ''.$ThursdayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="lightblue" width="60%">Friday: </td>';
        echo '<td class="lightblue" width="40%">';
        echo '   <select class="chosen-select" name="numfriday" id="numfriday" style="background-color: white; width: 100%;" disabled>';
        echo ''.$FridayOptions.'';
        echo '   </select>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</td>';
        echo '</tr>';        
        echo '<tr>';
        echo '<td class="lightblue" width="15%">Doesn\'t Need Covering</td>';
        echo '<td colspan="">';
        echo '<input id="IsNeedCovering" name="IsNeedCovering" type="checkbox" ' . ($isNeedCovering == 0 ? 'checked' : '') . ' disabled>';
        echo '</td>';
        echo '<td class="lightblue" width="15%">Disable Over 12</td>';
        echo '<td colspan="">';
        echo '<input id="isOverrideOver12" name="isOverrideOver12" type="checkbox" ' . ($isOverrideOver12 == 0 ? 'checked' : '') . ' disabled>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

?>

<script type="text/javascript">

    $(document).ready(function(){
        $("#facebox").addClass('customFaceboxClose');
        $("#ddldutyteam").trigger('change');
        //set default team if any team is selected
        var selectedTeamId = $('#ddlRotaTeams').val();
        if(selectedTeamId){
            $("#ddldutyteam").val(selectedTeamId);
            getmastercolors(selectedTeamId);
            $("#ddldutyteam").prop('disabled', true);
        }

        $("#ddldutyteam").on('change', function(){
            var teamId = $("#ddldutyteam").val();
            getmastercolors(teamId);
        });

        function getmastercolors(teamId) {
            var dutyColourID=$("#dutyColourID").val();
            var dutyId=<?php echo $intID; ?>;
            $.ajax({
                url: 'page-includes/master-duties/get-masterdutycolors.php',
                dataType: "json",
                type: 'POST',
                data: {teamId: teamId, dutyColourID : dutyColourID, dutyId: dutyId},
                success: function (data) {

                    if (data.status == 'success') {
                        $('select.chosen-select options:contains("Swatch 1")');
                        $('#ddldutycolour').html(data.ColourOptions);
                        $('select.chosen-select').trigger("chosen:updated");
                    }
                }
            });
      
        }

        $('#dutyname').on('keyup', function(){
            let ControlName = "#dutyname";
            let Fieldname= 'Duty Name';
            let TextValid = TextTypeValidation(ControlName,Fieldname);
            if(TextValid == 0){
                return;
            }
        });

        if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
            customAlert('You do not have modify privileges for this form.');
        }
    else {
            $('#neweditduty').validate({
                submitHandler: function(form) {
                    var dutyname = form.elements["dutyname"].value;
                    var dutyid = form.elements["dutyid"].value;
                    var dutytypeid = form.elements["dutytype"].value;
                    var dutyteam = form.elements["ddldutyteam"].value;
                    var olddutyname = form.elements["olddutyname"].value;
                    var starttimemasterduty = form.elements["starttimemasterduty"].value;
                    var endtimemasterduty = form.elements["endtimemasterduty"].value;
                    if(starttimemasterduty == '' || starttimemasterduty == '0')
                    {
                        starttimemasterduty =   "00:00";
                    }
                    if(endtimemasterduty == '' || endtimemasterduty == '0')
                    {
                        endtimemasterduty =   "00:00";
                    }
                    var startTimeArr  =   starttimemasterduty.split(':');
                    var starthour =   startTimeArr[0];
                    var startminute =   startTimeArr[1];
                    var endTimeArr  =   endtimemasterduty.split(':');
                    var endhour =   endTimeArr[0];
                    var endminute =   endTimeArr[1];
                    var yearavaialblefrom = form.elements["yearavaialblefrom"].value;
                    var weekavaialblefrom = form.elements["weekavaialblefrom"].value;
                    var yearavaialbleto = form.elements["yearavaialbleto"].value;
                    var weekavaialbleto = form.elements["weekavaialbleto"].value;
                    var numsat = form.elements["numsaturday"].value;
                    var numsun = form.elements["numsunday"].value;
                    var nummon = form.elements["nummonday"].value;
                    var numtue = form.elements["numtuesday"].value;
                    var numwed = form.elements["numwednesday"].value;
                    var numthu = form.elements["numthursday"].value;
                    var numfri = form.elements["numfriday"].value;
                    var breakTimeHour = form.elements["breakTimeHour"].value;
                    var breakTimeMinute = form.elements["breakTimeMinute"].value;
                    var labelIds = $(form.elements["labelIds"]).val();
                    var dutycolour = form.elements["ddldutycolour"].value;

                    var strHistory = '';
                    if (dutyid > 0 && olddutyname != dutyname) {

                        var strHistory = '-- Duty Name changed from [' + olddutyname + '] to [' + dutyname + ']';

                    }
                    //validation start
                    //check the validation name
                    if(dutyname == ''){
                        customAlertByModel('Please enter the duty name');
                        return;
                    }

                    /* Duty  Start time and End Time validation*/
                    starthour = parseInt(starthour);
                    endhour = parseInt(endhour);
                    startminute = parseInt(startminute);
                    endminute = parseInt(endminute);
                    /* check the scheduling team dropdown*/

                    if( !$('#ddldutyteam').val() ) {
                        customAlertByModel("Select the scheduling team");
                        return
                    }
                    // Validation for Duty name should have a value not spaces
                    var ControlName = "#dutyname";
                    var Fieldname= 'Duty Name';
                    var MinLength = 1;
                    var MaxLength = 50;

                    var Valid = ValidateText(ControlName,MinLength,MaxLength,Fieldname);
                    if(Valid == 0){
                        return;
                    }
                    /** Check the Year Validation */
                    var startYear = $("#yearavaialblefrom").val();
                    var endYear = $("#yearavaialbleto").val();
                    var startWeek = $("#weekavaialblefrom").val();
                    var endWeek = $("#weekavaialbleto").val();
                    var YearValidation=  StartYearEndYear(startYear,endYear,startWeek,endWeek);
                    if(YearValidation == 1){
                        return;
                    }
                    /** End */

                    var thisdutytype = <?php echo $intDutyType == '' ? 1 : $intDutyType; ?>;
                    $.ajax({
                        url: "page-includes/master-duties/save-dutydetails.php",
                        type: "POST",
                        dataType: "json",
                        data: {
                            'DutyID': dutyid,
                            'DutyName':dutyname,
                            'DutyTypeID': dutytypeid,
                            'History': strHistory,
                            'dutyteam': dutyteam,
                            'starthour': starthour,
                            'startminute': startminute,
                            'endhour': endhour,
                            'endminute': endminute,
                            'numsaturday': numsat,
                            'numsunday': numsun,
                            'nummonday': nummon,
                            'numtuesday': numtue,
                            'numwednesday': numwed,
                            'numthursday': numthu,
                            'numfriday': numfri,
                            'dutycolour': dutycolour,
                            'yearavaialblefrom':yearavaialblefrom,
                            'yearavaialbleto': yearavaialbleto,
                            'weekavaialbleto':weekavaialbleto,
                            'weekavaialblefrom':weekavaialblefrom,
                            'breakTimeHour': breakTimeHour,
                            'breakTimeMinute': breakTimeMinute,
                            'labelIds' : labelIds
                        },
                        success: function(data) {
                            if (data.status == 'success') {
                                ListMasterDuties(dutytypeid, dutyid, 1);
                                $.facebox.close();
                            }
                            else {
                                customAlertByModel(data.sqlstatusstring);
                            }
                        },
                        error:function(x,e) {
                            if (x.status==0) {
                                customAlert('You are offline!!<br/>Please Check Your Network.');
                            } else if(x.status==404) {
                                customAlert('Requested URL not found.');
                            } else if(x.status==500) {
                                customAlert('Internal Server Error.');
                            } else if(e=='parsererror') {
                                customAlert('Error.<br/>Parsing JSON Request failed.');
                            } else if(e=='timeout'){
                                customAlert('Request Time out.');
                            } else {
                                customAlert('Unknown Error.<br/>'+x.responseText);
                            }
                        }

                    });
                }
            });
        }
    });
    
    $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    $(function() {
        $( "button" )
            .button();
        setTimeout(function() {
            $("#labelIds").chosen("destroy");
            $("#labelIds").chosen({no_results_text: "Oops, nothing found!", max_selected_options: 6});
        }, 100);
    });


    // Update the Week dropdown on the changes of Year value
    $("#yearavaialblefrom").chosen().change(
        function() {
            var yearAvaialbleFrom = $("#yearavaialblefrom").val();
            var selectId = '#weekavaialblefrom';

            updatedWeekAvailable(yearAvaialbleFrom,selectId);
        });

    // Update the Week dropdown on the changes of Year value
    $("#yearavaialbleto").chosen().change(
        function() {
            var yearAvaialbleTo = $("#yearavaialbleto").val();
            var selectId = '#weekavaialbleto';
            updatedWeekAvailable(yearAvaialbleTo,selectId);
        });

    // Update the Week dropdown on the changes of Year value
    function updatedWeekAvailable(year,selectId){
        let dutyid =  $("#dutyid").val();
        $.post("page-includes/master-duties/getweekdropdown.php", {
                year: year,
                dutyid:dutyid
            },
            function(data){
                // update thw week options
                $(selectId).empty();
                $(selectId).append(data.Weeks);
                $(selectId).trigger("chosen:updated");
            }
        );
    }

    //Refresh the master duties list
    function ListMasterDuties (listtype, id, showedit){
        var isShowEnded = $('#showEndedDuties').is(':checked') ? 1 : 0;
        var CookieName = "masterdutiesshowarchived";
        var strTeams = $('#ddlRotaTeams').val();
        let strInput = $.cookie("searchMasterDuty");
        switch (listtype) {
            case 0:
                tabID = 0;
                $("#MiscDutyTab").removeClass("ui-tabs-active ui-state-active");
                $("#MasterDutyTab").addClass("ui-tabs-active ui-state-active");
				$("#showEndedDuties").show();
				$("#showEndedDutieslbl").show();
                var $tabs = $('#masterdutiestabs').tabs();
                break;

            case 1:
                tabID = 1;
                $("#MiscDutyTab").addClass("ui-tabs-active ui-state-active");
                $("#MasterDutyTab").removeClass("ui-tabs-active ui-state-active");
                var $tabs = $('#masterdutiestabs').tabs();
				$("#showEndedDuties").hide();
				$("#showEndedDutieslbl").hide();
                break;
        }

        if(listtype == 0){
            $("#divShowJobs").show();
            $.post("page-includes/master-jobs/list-masterduties.php", {
                    id: id,
                    archived: 0,
                    teamId: strTeams,
                    strSearchText: strInput,
                    isShowEnded: isShowEnded
                },
                function(data,status){
                    $('#dutieslistdiv0').html(data);
					$('#duties').scrollTop(Math.abs($.cookie("dutyListingPosTop")));
					$('#duties').scrollLeft(Math.abs($.cookie("dutyListingPosLeft")));
                }
            );

        }
        if(listtype == 1){
            $("#divShowJobs").hide();
            $.post("page-includes/master-duties/ListMiscellaneousDuties.php", {
                    id: id,
                    archived: 0
                },
                function(data,status){
                    $('#dutieslistdiv0').html(data);
					$('#duties').scrollTop(Math.abs($.cookie("miscDutyListingPosTop")));
					$('#duties').scrollLeft(Math.abs($.cookie("miscDutyListingPosLeft")));
                    GoToRow(id);
                }
            );
        }
    }
    function checkStartTime(starthour, startminute)
    {
        if(starthour == 0 && startminute == 0)
        {
            customAlertByModel("Please select start time.");
            $('#endhour').val('0').trigger('chosen:updated');
            $('#endminute').val('0').trigger('chosen:updated');
            return false;
        }
    }
    $(function() {
        $('#starttimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });
    });

    $(function() {
        $('#endtimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });
    });

    $('.spanFaceBoxClose').on('click', function(){
        $.facebox.close();
    });

function dutyTimeSanitization(timeVal, elementId)
{
	let elementResorce	=	document.getElementById(elementId);
	if(timeVal.length == 4)
	{
		timeVal = timeVal + '0';
	}
	if(timeVal.length == 3)
	{
		timeVal = timeVal + '00';
	}
	if(timeVal.length == 2)
	{
		timeVal = timeVal + ':00';
	}
	if(timeVal.length == 1)
	{
		timeVal = timeVal + '0:00';
	}
	$('#'+elementId).val(timeVal);
    let TimeMinute = timeVal.length > 3 ? timeVal.substring(3, 5) : timeVal;
    if ((parseInt(TimeMinute) % 15) != 0) {
        $('#errorDivFP').html("Only 15 Minute intervals are allowed for the Start/End time.");
        $('#errorDivFP').show();
        $("#"+elementId).val(timeVal.substring(0,3)+'00');
        $("#"+elementId).focus();
        return;
    } else {
        $('#errorDivFP').hide();
    }

	if(timeVal == '0')
	{
		setTimeout(function(){
			elementResorce.value = '00:00'; 
			elementResorce.blur();
			}, 1000);
		return true;
	}
	let timeValArr	=	timeVal.split(':');
	if(timeValArr[0] < 24 && timeValArr[1] < 60)
	{
		
	}else
	{
		elementResorce.value	=	'00:00';
	}
}
</script>