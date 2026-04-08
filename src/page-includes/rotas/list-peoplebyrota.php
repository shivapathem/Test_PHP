<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/userRolePermissions.php';
include_once 'setTeamCookieRotas.php';
$pageid = 24;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);

$intAreaID = $_SESSION['user']['AreaID'];
$intRotaID = $_REQUEST["id"];
$intWeeksInRota = 0;
$dtRotaStartDate = '';
$intType = 0;
$strEffDate = date("d/m/Y");
$intTeamID = 0;
$rsRotaDetailsJson = GetRotaDutiesDetails($intRotaID, $strEffDate);
$rsRotaDetails = json_decode($rsRotaDetailsJson,true);
if (!empty($rsRotaDetails)) {
    $intWeeksInRota = 1;
    $dtRotaStartDate = $rsRotaDetails['RotaStartDate'];
    $intTeamID = $rsRotaDetails['TeamID'];
    $intWeekInRota = $rsRotaDetails['CurrentWeekInRota'];
    $intBBCStartWeek = $rsRotaDetails['BBCStartWeek'];
}
if($intTeamID == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $intTeamID);
}
$className	=	'';
if ($permissions->canmodify == 1)
{
	$className	=	'rotapeopletabledroppable';
}
$rsPeopleJson = GetRotaPeople($intRotaID);
$rsPeople = json_decode($rsPeopleJson,true);
if (!empty($rsPeople)) {
    $arrPeople['name'] = $rsPeople['RotaName'];
    $arrPeople['weeksinrota'] = $rsPeople['WeeksInRota'];
    $arrPeople[$rsPeople['RotaWeek']][$rsPeople['ScheduledPersonID']]['ScheduledPersonID'] = $rsPeople['ScheduledPersonID'];
    $arrPeople[$rsPeople['RotaWeek']][$rsPeople['ScheduledPersonID']]['rotapeopleid'] = $rsPeople['RotaPeopleID'];
    $arrPeople[$rsPeople['RotaWeek']][$rsPeople['ScheduledPersonID']]['forename'] = $rsPeople['Forename'];
    $arrPeople[$rsPeople['RotaWeek']][$rsPeople['ScheduledPersonID']]['surname'] = $rsPeople['Surname'];
    $arrPeople[$rsPeople['RotaWeek']][$rsPeople['ScheduledPersonID']]['fullname'] = $rsPeople['DisplayName'];
    $arrPeople[$rsPeople['RotaWeek']][$rsPeople['ScheduledPersonID']]['startdate'] = $rsPeople['RotaPersonStartDate'];
    $arrPeople[$rsPeople['RotaWeek']][$rsPeople['ScheduledPersonID']]['enddate'] = $rsPeople['RotaPersonEndDate'];
    $arrPeople[$rsPeople['RotaWeek']][$rsPeople['ScheduledPersonID']]['eft'] = $rsPeople['EFT'];
}

echo '<table id="rotapeopledetails" class="stripe bluetable" style="width:100%;">';
echo '<thead>';
echo '<tr>';
echo '<th colspan="5">Showing People on this Rota Pattern';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th style="border-top:2px solid #000000;" width="12%">Line</th>';
echo '<th style="border-top:2px solid #000000;" width="30%">Name</th>';
echo '<th style="border-top:2px solid #000000;" width="28%">Start Date</th>';
echo '<th style="border-top:2px solid #000000;" width="28%">End Date</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($intWeeksInRota > 0) {
  for ($intWeek = 1; $intWeek <= $intWeeksInRota; $intWeek++) {
    if (isset($arrPeople[$intWeek])) {
	echo '<tr>';
	echo '<td>';
	echo $intWeek;
	echo '</td>';
	echo '<td colspan="4" style="padding:0px; color:#000000;">';
	echo '<table width="100%">';

	foreach ($arrPeople[$intWeek] as $arrRotaRecords) {
	      $intRotaPersonID = $arrRotaRecords['rotapeopleid'];
	      $intScheduledPersonID = $arrRotaRecords['ScheduledPersonID'];
	      $strPersonName = $arrRotaRecords['fullname'];
	      $strStartDate = $arrRotaRecords['startdate'];
	      $strEndDate = $arrRotaRecords['enddate'];
	      $strEFT = $arrRotaRecords['eft'];
            echo '<tr class="rotapeople-context-menu" id="RotaPerson" rotapersonid="'.$intScheduledPersonID.'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" lastmoddate="">';
            echo '<td class="'.$className.'" id="w'.strval($intWeek).'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" weekid="'.$intWeek.'" width="30%">';
            echo $strPersonName;
            echo '</td>';
            echo '<td class="'.$className.'" id="w'.strval($intWeek).'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" weekid="'.$intWeek.'" width="28%">';
            echo $strStartDate;
            echo '</td>';
            echo '<td class="'.$className.'" id="w'.strval($intWeek).'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" weekid="'.$intWeek.'" width="28%">';
            echo $strEndDate;
            echo '</td>';
            echo '</tr>';
	}
	echo '</table>';
	echo '</td>';
      }
      else {
            $intRotaPersonID = 0;
            $intScheduledPersonID = 0;
            $strPersonName = '';
            $strStartDate = '';
            $strEndDate = '';
          echo '<tr class="rotapeople-context-menu" rotapersonid="'.$intScheduledPersonID.'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" lastmoddate="">';
          echo '<td class="'.$className.'">';
          echo $intWeek;
          echo '</td>';
          echo '<td class="'.$className.'" id="w'.strval($intWeek).'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" weekid="'.$intWeek.'" >';
              echo $strPersonName;
          echo '</td>';
          echo '<td class="'.$className.'" id="w'.strval($intWeek).'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" weekid="'.$intWeek.'" >';
              echo $strStartDate;
          echo '</td>';
          echo '<td class="'.$className.'" id="w'.strval($intWeek).'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" weekid="'.$intWeek.'" >';
              echo $strEndDate;
          echo '</td>';
//          echo '<td class="rotapeopletabledroppable" id="w'.strval($intWeek).'" ScheduledPersonID="'.$intScheduledPersonID.'" rotaid="'.$intRotaID.'" weekid="'.$intWeek.'" >';
//              echo $strEFT;
//          echo '</td>';
          echo '</tr>';
      }
  }
}

echo '</tbody>';
echo '</table>';


?>


<script type="text/javascript"> 

$(document).ready(function(){ 

    $( "td.rotapeopletabledroppable" ).droppable({
      tolerance: "pointer",
        over: function(event, ui) {
	        $(this).addClass('highlighted');
        },
        out: function(event, ui) {
	        $(this).removeClass('highlighted');
        },
        drop: function( event, ui ) {
            var PrevPerson = $("#RotaPerson").attr("ScheduledPersonID");
            var NewPerson = ui.draggable.attr("ScheduledPersonID");
            $(this).removeClass('highlighted');
            var ScheduledPersonID = ui.draggable.attr("ScheduledPersonID");
            var staffname = ui.draggable.attr("staffname");
            var rotaid = $(this).attr("rotaid");
            var weekid = $(this).attr("weekid");
            var blUpdate = 0;
            var blnoreplace = 0;
            blUpdate = 1;
            if(PrevPerson == null || PrevPerson == "") {
                $.ajax({
                    type: 'POST',
                    url: "page-includes/rotas/dropperson2.php",
                    data: {
                        'RotaID': rotaid,
                        'WeekID': weekid,
                        'ScheduledPersonID': ScheduledPersonID,
                        "teamId": $('#ddlRotaTeams').val()
                    },
                    success: function (data) {
                        $.facebox(data);
                    }
                });
            }
            else if(PrevPerson == NewPerson){
                customAlert("This Person is already assigned to this Rota Pattern.");
                return;
            }
            else {
                if (PrevPerson != NewPerson) {
					customConfirm('This Rota Pattern is already allocate to other person. Do you wish to allocate it to new person?',function(){
                            $.ajax({
                            type: 'POST',
                            url: "page-includes/rotas/dropperson2.php",
                            data: {
								'RotaID': rotaid,
								'WeekID': weekid,
								'ScheduledPersonID': ScheduledPersonID,
                                "teamId": $('#ddlRotaTeams').val()
                            },
                            success: function (data) {
                                setTimeout(function() {$.facebox(data);}, 500);
                        },
                        });
						},
						function() {
							return false;
						}
					);
                }
            }
        }
    });

  $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    $(function() {
      $("button")
	.button()
    });

      $(function() {
        $( ".selector" ).selectmenu({
          change: function( event, ui ) {
            var value = $(this).val();
            ShowRotaDetails(value, 1);
          }
        });
      });
});


function DeleteRotaPerson(rotaid, rotapersonid) {
    if (rotapersonid > 0) {
        $.ajax({
            type: 'POST',
            url: "page-includes/rotas/DeletePerson.php",
            data: {
                "rotaid": rotaid,
                "rotapersonid": rotapersonid,
                "teamid":'<?php echo $intTeamID; ?>'
            },
            success: function (data) {
                if (data.status == 'success') {
                    ShowPeopleByRota(rotaid);
                    ShowRotaDetails(rotaid, 1, 1);
                }
                else{
                    customAlert(data.sqlstatusstring);
                }
            }
        });
    }
}

if ((<?php echo empty($permissions->candelete) ? 0:$permissions->candelete ?> == 1) && (<?php echo $intWeeksInRota; ?> == 1))
{
    $(function () {
        $.contextMenu({
            selector: '.rotapeople-context-menu',
            items: {
                "delete": {
                    name: "Remove Person",
                    icon: "delete",
                    // superseeds "global" callback
                    callback: function (key, options) {
                        var rotaid = options.$trigger.attr("rotaid");
                        var rotapersonid = options.$trigger.attr("rotapersonid");
                        DeleteRotaPerson(rotaid, rotapersonid);
                    }
                },
            }
        })
    });
}
</script>
