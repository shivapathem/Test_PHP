<?php
session_start();
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/user-scheduling-team-list.php';
include_once 'setTeamCookieRotas.php';
$pageid = 24;
// Call User Permission function.
$intTeamID = $_POST['teamId'] ?? $_COOKIE['masterdutyteams'] ?? 0;
$permissions = getUserRolePermissions($pageid, $intTeamID);
$intUserID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
$case = $_POST['tabCase'] ?? null;
$TeamOptions = getSchedulingTeamList($intTeamID, 'rota-pattern', 'view');

    echo '<div class="blueboxmedtextheader" style="width:100%;" >';
    echo '<div style="width:35%; float:left;">';
    echo '<span class="blueboxmedtextheader-title">People In Rota Patterns</span>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<img id="print" class="handcursor" name="print" border="0" src="images/print.png" style="top:10px; vertical-align:middle;" alt="Print" width="18px" height="17px" onclick="window.print();" hidden>';
    echo '</div>';
    echo '<div style="width:50%; float:left;">';
    echo '<label for="ddlRotaTeams">Teams: </label>';
    echo '<select class="chosen-select topTeamList" name="ddlRotaTeams" id="ddlRotaTeams" style="background-color:blue; width:80%; height:20px;" onchange=\'javascript:ChangeRotaTeams()\';>';
    echo "<option value=''>Select The Team</option>";
    echo  $TeamOptions;
    echo '</select>';
    echo '</div><div style="width:15%; float:left;
                text-align: center;
                cursor: default;
                box-sizing: border-box;
                background-color: -internal-light-dark(rgb(239, 239, 239), rgb(59, 59, 59));
                color: -internal-light-dark(black, white);
                padding: 1px 3px;" id="exportBtnWrapper">';
    if($permissions->canmakepublic==1) {
    echo '<span id="unExportRota" style="background-color:Black;color:white;border:none;
    border-radius:50%;test-align:center;font-size:16px;padding-right:4px;padding-left:4px;"></span> <Input type="button" name="export" id="export" value="Export Rota Patterns" onclick="exportRota()">';
    }
    echo '</div></div>';

  echo '<div id="rotapeopletabs" style="border:none; width:100%;">';
  echo '<ul style="border:none; width:100%; display:none;">';
    echo '<li onclick=\'javascript:ListRotaPeople(0,0)\';><a href="#rotapeoplelistdiv0">People By Rota Pattern</a></li>';
    echo '<li onclick=\'javascript:ListRotaPeople(1,0)\';><a href="#rotapeoplelistdiv0">Rota Pattern By Person</a></li>';
  echo '</ul>';
  
  echo '<div id="rotapeoplelistdiv1" style="width:100%;">';
  echo '</div>';

  echo '<div id="rotapeoplelistdiv0" style="width:100%;padding:0px;">';
  echo '</div>';
  
  echo '</div>';

?>

<script type="text/javascript">

$(document).ready(function(){
  $("#rotapeopletabs").height($(window).height() - 134);
  if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
    $( '#content' ).load( 'page-includes/no_access.php', function() { });
  }
  let teamID =$("#ddlRotaTeams").val();
  if(teamID) {
    getUnexportedNoOfRota(teamID);
  }
  <?php if ($case == 2): ?>
        $('#exportBtnWrapper').empty();
  <?php endif; ?>
});


function ListRotaPeople(listtype, id, RotaDate = null) {
  var tabCookieName = "rotapeopletabs";
  $.cookie(tabCookieName, listtype, { expires: 0 }); 
  
  if (listtype == 1) {
      $.ajax({
          type: 'POST',
          url: 'page-includes/rotas/peopledetails.php',
          data: {
              "id": id,
              "RotaDate": RotaDate,
              "teamId": $("#ddlRotaTeams").val()
          },
          success: function (data) {
              $('#rotapeoplelistdiv1').html(data);
              var tabCookieName = 'rotatabs';
              $.cookie(tabCookieName, 0, { expires: 0 });
          },
      });
  }
  else {
    $('#rotapeoplelistdiv1').empty();
  }
  
	if($.cookie('selectRotaId') > 0)
	{
		ShowRotaDetails($.cookie('selectRotaId'), 1);
	}else
	{
		ShowRotaDetails(0, 1);
	}
}


function ShowRotaDetails(id, lineid, rotaTableLoadOnly = 0) {
  var today = new Date();
  var dd = today.getDate();
  var mm = today.getMonth()+1; //January is 0!
  var yyyy = today.getFullYear();
  $.cookie('selectRotaId', id);
  if(dd<10) {
      dd='0'+dd
  } 

  if(mm<10) {
      mm='0'+mm
  } 

  today = dd+'/'+mm+'/'+yyyy;
  
  var tabCookieName = "rotapeopleeffdate";
  var effdate = ($.cookie(tabCookieName) || today);

    $.ajax({
        type: 'POST',
        url: 'page-includes/rotas/rotadetails.php',
        data: {
            "id": id,
            "lineid": lineid,
            "showtemplates": 2,
            "showall": 0,
            "effdate": effdate,
            "showbreaks": ($.cookie('rotashowbreaks') || 0),
            "peopleTab": 1
        },
        success: function (data) {
            rotaTableLoadOnly == 0 ? $('#rotapeoplelistdiv0').html(data) : $('#rotadetails2').replaceWith($('<div></div>').html(data).find('#rotadetails2'));   
			$("#Loading6style").hide();
            $('#loading').hide();
        },
    });
}

function DutyHistory(id) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/master-duties/duty-history.php',
        data: {
            "id": id
        },
        success: function (data) {
            $.facebox(data);
        },
    });
}

function exportRota() {
  var TeamID=$('#ddlRotaTeams').val(); 
 if(TeamID<=0 && !isNaN(TeamID))
 {
  $.facebox('Please Select the Schedulling Team.');
  return false;
  $('#ddlRotaTeams').focus();
 }
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/weekly/actions/export-rota.php',
        data: {
            "TeamID": TeamID
        },
        success: function (data) {
            data = JSON.parse(data);
            $.facebox(data.msg);
            getUnexportedNoOfRota(TeamID);
        },
    });
}

function getUnexportedNoOfRota() {
  $('#unExportRota').css('display','none'); 
  var TeamID=$('#ddlRotaTeams').val(); 
 if(TeamID<=0 && !isNaN(TeamID)) {
  $.facebox('Please Select the Schedulling Team.');
  return false;
  $('#ddlRotaTeams').focus();
 }
    $.ajax({
        type: 'POST',
        url: "page-includes/allocations/weekly/actions/count-unexport-rota.php",
        data: {
            "TeamID": TeamID
        },
        success: function (data) {
            data = JSON.parse(data);
            if(data.status==1 && data.count>0){
                $('#unExportRota').css('display','none');
                $('#unExportRota').html(data.count);
            } else{
                $('#unExportRota').css('display','none');
            }
        }
    });
}
</script>
