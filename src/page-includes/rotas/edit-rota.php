<?php
/*
* objective: Insert, update and delete any rota
* Created By: Vipin Kushwaha
* Created Date: 05-04-2021
* Description: Contains all logic for Create, Update and delete rotas.
* */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$pageid = 14;
// Call User Permission function.
$intAreaID = $_SESSION['user']['AreaID'];
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intID = $_REQUEST["id"];
$intTask = $_REQUEST["task"];
$ddlRotaTeamsValue = isset($_REQUEST["ddlRotaTeamsValue"])? $_REQUEST["ddlRotaTeamsValue"] : 0;
$intType = 0;
$intShowForm = 1;
$intTeamID = 0;
$TeamOptions = null;
$WeeksOptions = null;
$Date1 = null;
$rsRota = GetRotaDetailsByID ($intType, $intAreaID, $intID);
$row = json_decode($rsRota,true);
$intTeamID = $ddlRotaTeamsValue != 0 ? $ddlRotaTeamsValue : $row['TeamID'];
$permissions = getUserRolePermissions($pageid, $intTeamID);
//check if we show the form
if (($permissions->canmodify == 1) || ($permissions->cancreate == 1)) {
  if ($intTask == 2) {   
    
    $Date1 = str_replace('/','-',$row['RotaStartDate']);
    if ($Date1 == '') {
      $Date1 = date('d-m-Y', strtotime('today'));
    }
    
    $WeeksOptions = PopulateNumberDropDown (intval($row['WeeksInRota']), 1, 52);
   
    $rsUserTeams = GetUserAreaTeamsListByFormId ($intUserID, 0, $pageid);
    $TeamOptions = PopulateTeamsDropDown ($rsUserTeams, $intTeamID, 0);
    echo '<div style="width: 600px">';
    echo '<form id="neweditrota">';
    echo '<table id="rotanewedittable" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="5">Copy Rota Pattern</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td class="lightblue">New Rota Pattern Name</td>';
    echo '<td colspan="3">';
    echo '<input id="RotaName" name="RotaName" type="text" maxlength="50" minlength="1" size="50" value="" />';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightblue" hidden>Scheduling Team</td>';
    echo '<td hidden>';
    echo '<select class="chosen-select" name="ddlRotaTeam" id="ddlRotaTeam" style="background-color:white; width:140px; height:20px;" >';
    echo ''.$TeamOptions.'';
    echo '</select>';
    echo '</td>';
    echo '<td class="lightblue">New Rota Starts from Week</td>';
    echo '<td>';
    echo '<input name="newRotaStartsWeek" id="newRotaStartsWeek" maxlength="2" type="text"></input>';
    echo '</td>';

    echo '</tr>';
    echo '<tr>';
    echo '<td>Notes</td>';
    echo '<td colspan="3">';
    echo '<textarea rows="4" name="RotaNotes" cols="50" maxlength="500"></textarea>';
    echo '</td>';
    echo '</tr>';
    echo '<tr hidden>';
    echo '<td class="lightblue" width="15%">Start Date</td>';
    echo '<td width="35%">';
    echo '<input id="startdate" name="startdate" type="text" size="15" value="'.$Date1.'" />';
    echo '</td>';
    echo '<td class="lightblue" width="15%">Weeks In Rota Pattern</td>';
    echo '<td width="35%">';
    echo '   <select class="chosen-select" name="rotaweeks" id="rotaweeks" style="background-color: white; width: 100%;" >';
    echo ''.$WeeksOptions.'';
    echo '   </select>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td></td>';
    echo '<td colspan="3"><input name="submit" type="submit" value="Create Rota Pattern"></input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="submit" id="Cancel" type="button" value="Cancel"></input></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '<input type="hidden" name="id" value="'.$intID.'">';
    echo '<input type="hidden" name="task" value="'.$intTask.'">';
    echo '<input type="hidden" name="oldRota" value="">';
    echo '<input type="hidden" name="oldNotes" value="">';
    echo '<input type="hidden" name="oldStart" value="">';
    echo '<input type="hidden" name="oldWeeks" value="">';
    echo '<input type="hidden" name="LastModDate" value="">';
    echo '<input type="hidden" name="listtype" value="'.$intType.'">';
  }
  else if ($intTask == 1) {
    $rsRota = GetRotaDetailsByID ($intType, $intAreaID, $intID);
    $row = json_decode($rsRota,true);
    $oldRota = $row['RealRotaName'];
    $oldNotes = $row['RotaNotes'];
    $oldWeeks = $row['WeeksInRota'];
    $strLastMod = $row['LastModDate'];
    $Date1 = str_replace('/','-',$row['RotaStartDate']);
    if ($Date1 == '') {
      $Date1 = date("d-m-Y", strtotime("today"));
    }
    $oldStart = $Date1;
    $WeeksOptions = PopulateNumberDropDown (intval($row['WeeksInRota']), 1, 52);
    $intTeamID = $ddlRotaTeamsValue != 0 ? $ddlRotaTeamsValue : $row['TeamID'];
    $rsUserTeams = GetUserAreaTeamsListByFormId ($intUserID, 0, $pageid);

    $TeamOptions = PopulateTeamsDropDown ($rsUserTeams, $ddlRotaTeamsValue, 0);

    echo '<div style="width: 600px">';
    echo '<form id="neweditrota">';
    echo '<table id="rotanewedittable" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="5">Edit Rota Pattern</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td width="20%" class="lightblue">Rota Pattern Name<span class="required">*</span></td>';
    echo '<td colspan="3">';
    echo '<input id="RotaName" name="RotaName" type="text" maxlength="50" minlength="1" size="50" value="'.$row['RealRotaName'].'" />';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightblue">Scheduling Team<span class="required">*</span></td>';
    echo '<td colspan="3">';
    echo '<select class="chosen-select" name="ddlRotaTeam" id="ddlRotaTeam" style="background-color:white; width:140px; height:20px;" disabled>';
    echo ''.$TeamOptions.'';
    echo '</select>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>Notes</td>';
    echo '<td colspan="3">';
    echo '<textarea rows="4" name="RotaNotes" cols="50" maxlength="500">'.$oldNotes.'</textarea>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightblue" width="15%">Weeks In Rota Pattern<span class="required">*</span></td>';
    echo '<td width="35%">';
    echo '   <select class="chosen-select" name="rotaweeks" style="background-color: white; width: 15%;">';
    echo ''.$WeeksOptions.'';
    echo '   </select>';
    echo '<input id="startdate" name="startdate" type="hidden" size="15" value="'.$Date1.'" disabled />';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td></td>';
    echo '<td colspan="3"><input name="submit" type="submit" value="Update Rota Pattern"></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '<input type="hidden" name="id" value="'.$intID.'">';
    echo '<input type="hidden" name="task" value="'.$intTask.'">';
    echo '<input type="hidden" name="oldRota" value="'.$oldRota.'">';
    echo '<input type="hidden" name="oldNotes" value="'.$oldNotes.'">';
    echo '<input type="hidden" name="oldStart" value="'.$oldStart.'">';
    echo '<input type="hidden" name="oldWeeks" value="'.$oldWeeks.'">';
    echo '<input type="hidden" name="LastModDate" value="'.$strLastMod.'">';
    echo '<input type="hidden" name="listtype" value="'.$intType.'">';
  }
  else {
      if (date("N",strtotime('today')) == 6) {
          $Date1 = date("d-m-Y",strtotime('today'));
      }
      else {
          $Date1 = date("d-m-Y",strtotime('next saturday'));
      }
    $rsUserTeams = GetUserAreaTeamsListByFormId ($intUserID, 0, $pageid);
    $parsedValueUserTeam = json_decode($rsUserTeams,true);
      $countrow = count($parsedValueUserTeam);
      for($i = 0; $i < $countrow; $i++){
          if($parsedValueUserTeam[$i]['TeamID'] == $ddlRotaTeamsValue) {
              $DefaultRotaValue = $parsedValueUserTeam[$i]['defaultNumberweeksRotaPattern'] != 0 ? $parsedValueUserTeam[$i]['defaultNumberweeksRotaPattern'] : 4;
              break;
          } else {
              $DefaultRotaValue = 4;
          }
      }
    $TeamOptions = PopulateTeamsDropDown ($rsUserTeams, $ddlRotaTeamsValue, 0);
    $WeeksOptions = PopulateNumberDropDown ($DefaultRotaValue, 1, 52);

    echo '<div style="width: 600px">';
    echo '<form id="neweditrota">';
    echo '<table id="rotanewedittable" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="5">New Rota Pattern</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td width="20%" class="lightblue">Rota Pattern Name<span class="required">*</span></td>';
    echo '<td colspan="3">';
    echo '<input id="RotaName" name="RotaName" type="text" maxlength="50" minlength="1" size="50" value="" />';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightblue">Scheduling Team<span class="required">*</span></td>';
    echo '<td colspan="3">';
    if ($ddlRotaTeamsValue > 0)
    {
        $hideTeamsDropdown = "disabled";
    } else {
        $hideTeamsDropdown = "";
    }
      echo '<select class="chosen-select" name="ddlRotaTeam" id="ddlRotaTeam" style="background-color:white; width:140px; height:20px;" '.$hideTeamsDropdown.' onChange="setDefaultRotaWeeks(this.options[this.selectedIndex].getAttribute(\'defaultrotaweek\'))">';
      echo ''.$TeamOptions.'';
      echo '</select>';
    echo '<input id="ddlRotaTeamhide" name="ddlRotaTeamhide" type="hidden" value="'.$ddlRotaTeamsValue.'"/>';
   
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>Notes</td>';
    echo '<td colspan="3">';
    echo '<textarea rows="4" name="RotaNotes" cols="50" maxlength="500"></textarea>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightblue" width="15%">Weeks In Rota Pattern<span class="required">*</span></td>';
    echo '<td width="35%">';
    echo '   <select class="chosen-select" id="rotaweeks" name="rotaweeks" style="background-color: white; width: 15%;">';
    echo ''.$WeeksOptions.'';
    echo '   </select>';
	echo '<input id="startdate" name="startdate" type="hidden" size="15" value="30-12-1995" disabled/>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td></td>';
    echo '<td colspan="3"><input name="submit" type="submit" value="Create Rota Pattern"></input></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '<input type="hidden" name="id" value="0">';
    echo '<input type="hidden" name="task" value="'.$intTask.'">';
    echo '<input type="hidden" name="oldRota" value="">';
    echo '<input type="hidden" name="oldNotes" value="">';
    echo '<input type="hidden" name="oldStart" value="">';
    echo '<input type="hidden" name="oldWeeks" value="">';
    echo '<input type="hidden" name="LastModDate" value="">';
    echo '<input type="hidden" name="listtype" value="'.$intType.'">';
  }
    echo '</form>';  
    echo '</div>';
}

?>

<script type="text/javascript">
    $(document).ready(function(){
        $("#facebox").removeClass('customFaceboxClose');
        $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
        $('#RotaName').on('keyup',function() {
            var ControlName = "#RotaName";
            var FieldName = "Rota Pattern Name";
            TextTypeValidation(ControlName,FieldName);
        });
        $('#newRotaStartsWeek').on('keyup',function() {
            var ControlName = "#newRotaStartsWeek";
            var FieldName = "New Rota Starts From Week";
            integerTypeValidation(ControlName, FieldName);
        });
        if ((<?php echo empty($permissions->canmodify)? 0:$permissions->canmodify ?> == 0) && (<?php echo empty($permissions->cancreate)? 0:$permissions->cancreate ?> == 0)) {
            $( '#content' ).load( 'page-includes/no_access.php', function() { });
        }
    else {
			$('#Cancel').click(function(){
                if($('#Cancel').val() == 'Cancel'){
                    $.facebox.close();
                }
            });
            $('#neweditrota').validate({
                // debug: true,
                rules:{
                    "RotaName":{
                        required:true,
                    },
                    "newRotaStartsWeek":{
                        required:true
                    },
                    ddlRotaTeam:{
                        required: {
                            depends: function(element) {
                                return $("#ddlRotaTeam").val() == "";
                            }
                        }
                    },
                },
                messages:{
                    "RotaName":{
                        required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter a Rota Name.' />"
                    },
                    "ddlRotaTeam":{
                        required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please select a Team for the Rota.' />"
                    },
                    "newRotaStartsWeek":{
                        required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please select New Rota Starts From Week.' />"
                    }
                },
                submitHandler: function(form) {
                    //Date validation for saturday
                    var DateNew = $('#startdate').val();
                    var SplitDate = DateNew.split("-").reverse().join("-");
                    var DayOfWeek = getDayOfWeek(SplitDate);
                    if(DayOfWeek != "Saturday"){
                        customAlertByModel("Please ensure that your start date is a Saturday.<br/>This allows the Rota pattern to apply for the whole week.");
                        return;
                    }
                    // Validation for Rota name should have a value not spaces
                    var ControlId = "#RotaName";
                    var MinLength = 3;
                    var MaxLength = 40;
                    var FieldName = "Rota Pattern";
                    var Valid = ValidateText(ControlId,MinLength,MaxLength,FieldName);
                    if(Valid == 0){
                        return;
                    }
                    $("#startdate").prop("disabled", false);
                    $("#ddlRotaTeam").attr("disabled", false);
                    $.ajax({type:'POST',
                        url: 'page-includes/rotas/edit-rota-save.php',
                        dataType: "json",
                        data: $('#neweditrota').serialize(),
                        success: function(data) {
                            $("#startdate").prop("disabled", true);
                            if (data.status == 'success') {
                                //all good
                                if (data.sqlstatusstring == 'success') {
                                    //all good
                                    var tabNo = typeof $("#tabNo").val() !== 'undefined' ? $("#tabNo").val() : 0 ;
                                    tabNo = parseInt(tabNo);
                                    ShowRotaDetails(data.sqlnewID, 1);
                                    $.facebox.close();
                                }
                                else {
                                    customAlertByModel(data.sqlstatusstring);
                                }
                            }
                            else {
                                customAlertByModel(data.status);
                            }
                            $("#ddlRotaTeam").attr("disabled", true);
                        },
                        error:function(x,e) {
                            if (x.status==0) {
                                customAlert('You are offline!!<br/> Please Check Your Network.');
                            } else if(x.status==404) {
                                customAlert('Requested URL not found.');
                            } else if(x.status==500) {
                                customAlert('Internal Server Error.');
                            } else if(e=='parsererror') {
                                customAlert('Error.<br/>Parsing JSON Request failed.');
                            } else if(e=='timeout'){
                                customAlert('Request Time out.');
                            } else {
                                customAlert('Unknown Error.r<br/>'+x.responseText);
                            }
                        }
                    });
                }
            });
        }
        //Function to find a day of the week.
        function getDayOfWeek(date) {
             const dayOfWeek = new Date(date.replace(/-/g, '\/')).getDay();
            return isNaN(dayOfWeek) ? null :
                ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][dayOfWeek];
        }

    });

    function setDefaultRotaWeeks(defaultRotaWeek) {
        $('#rotaweeks').val(defaultRotaWeek).trigger('chosen:updated');
    }
</script>
