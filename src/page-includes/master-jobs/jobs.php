<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css">
<link rel="stylesheet" href="styles/master-job-style.css">
<link rel="stylesheet" href="styles/master-job-default.css">
<style type="text/css">

    .left-duty-panel {
        float: left;
        font-size: 10px;
    }

    .time-slot div.showtime {
        position: relative;
        left: 15.2%;
    }

    .right-duty-panel {
        width: 100%; /*width:1330px;*/
        display: -webkit-box;
    }

    #firsttbl #myInput {
        margin: 0 0 8px 0;
        float: right;
    }

    table.dataTable.stripe tbody tr:nth-child(2n + 1) {
        background: #eee;
    }

    div.tables table tbody tr:hover {
        background: none;
        color: #000;
    }

    .chooseDuty {
        background-color: #990000 !important;
        color: #fff;
    }

    .startend {
        font-family: Verdana, Arial, sans-serif;
        font-size: 10.66667px;
    }


</style>
<?php

use App\Models\Scheduling\SchedulingTeam;
use Illuminate\Support\Facades\Gate;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/user-scheduling-team-list.php';
include_once 'setTeamCookieJob.php';
// Call User Permission function.
$pageid = 2;
$intTeamID = $_REQUEST['teamId'] ?? $_COOKIE['masterdutyteams'] ?? 0;
$intUserID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
}else{
    $strTeams = 0;
}

if($intTeamID == 0 || $intTeamID == '')
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $intTeamID);
}

$TeamOptions = getSchedulingTeamList($intTeamID, 'master-duty-job', 'view');

echo '<div class="blueboxmedtextheader" style="width:100%;" >';
echo '<div style="width:40%; float:left;">';
echo '<h1 style="font-size: inherit; font-weight: inherit; margin: 0; display: inline;">Master Duties, Master Jobs and Misc Duties</h1>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</div>';
echo '<div style="width:60%; float:left;">';
echo 'Teams: ';
echo '<select class="chosen-select" name="ddlRotaTeams" id="ddlRotaTeams" style="background-color:blue; width:80%; height:20px;" onchange=\'javascript:ChangeSchedulingTeam()\';>';
echo "<option value=''>Select The Team</option>";
echo  $TeamOptions;
echo '</select>';
echo '</div>';
echo '</div>';


echo '<div id="masterjobstabs" style="border:none;">';
echo '<h2 class="sr-only">Master Jobs</h2>';
echo '<ul>';
echo '<li onclick=\'javascript:ListJobs(0, 0)\';><a href="#joblistdiv">Master Jobs</a></li>';
echo '</ul>';
echo '<div style="width: 400px; height:25px;">';
echo '<table class="smalltable" width="100%" role="presentation">';
echo '<tr>';
echo '<td width="5%">&nbsp;</td>';
echo '<td width="95%" id="addJobBtnContainer" style="display:none;">';
echo '<img class="handcursor" border="0" src="images/add.png" width="18px" height="17px" role="button" tabindex="0" aria-label="Create new master job" onclick=\'javascript:newMasterJob()\'; onkeypress=\'if(event.key === "Enter" || event.key === " "
) newMasterJob();\'> New Job'; 
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '</div>';
echo '<section class="existingDept">';
echo '<div  id= "jobtablediv">';
echo '</div>';
echo '<div class="right-duty-panel time-slot" id= "dutylistdiv">';
echo '</div>';
echo '</section>';

?>

<script type="text/javascript">
    $(document).ready(function () {
        if (<?php echo empty($permissions->canview) ? 0 : $permissions->canview ?> == 0)
        {
            $('#content').load('page-includes/no_access.php', function () {});
			return false;
        }
        var tabCookieName = "masterjobstabs";
        $("#masterjobstabs").tabs({
            active: ($.cookie(tabCookieName) || 0),
            activate: function (event, ui) {
                var newIndex = ui.newTab.parent().children().index(ui.newTab);
                $.cookie(tabCookieName, newIndex, {expires: 1});
            }
        });
        var $tabs = $('#masterjobstabs').tabs();
        var selected = $tabs.tabs('option', 'active');
        ListJobs(0, selected);
        ListMasterDuties(0, selected);

        $(".chosen-select").chosen();
    })

    function DoResize() {
        var windowheight = $(window).height() - 225;
        var windowwidth = $(window).width() - 40;
        $("#joblistdiv").width(windowwidth).height(windowheight);
        $('.dataTables_scrollBody').height((windowheight - 60));
    }

    function ListJobs(id, listtype) {
        $.ajax({
			type: "post",
			url: "page-includes/master-jobs/list-jobs.php",
			data: {id: 0, listtype: listtype, teamId: $('#ddlRotaTeams').val()},
			success: function (data) {
				$('#jobtablediv').html(data);
				$('#firsttbl').scrollTop(Math.abs($.cookie("jobsdetailsPosTop")) + 101);
                 GoToRow(id);
			}
		});
    }

    function setStartTime() {
        var x = $("#StartTime").val();
        setTimeout(function () {
            if (x.length == 0) {
                $("#StartTime").val('00:00');
            }
        }, 1000);
    }

    function setEndTime() {
        var x = $("#EndTime").val();
        setTimeout(function () {
            if (x.length == 0) {
                $("#EndTime").val('00:00');
            }
        }, 1000);
    }

    function ListMasterDuties(id, listtype) {
        setTimeout(function() {
			$.ajax({
				type: "post",
				url: "page-includes/master-duties/dutytabs.php",
				data: {id: 0, listtype: listtype, teamId: $('#ddlRotaTeams').val()},
				success: function (data) {
					$('#dutylistdiv').html(data);
					GoToRow(id);
				}
			});
		}, 300);
    }

    function highlightJobs(jobId)
    {
        if ((jobId !== undefined) && (jobId !='') && ($('#'+jobId).length > 0)) {
                $('#firsttbl').scrollTop(Math.abs($('#'+jobId).position().top) - 101);
        }
        $('tr').removeClass("highlightOrange");
        $('#'+jobId).addClass("highlightOrange");
    }

    function ChangeSchedulingTeam() {
        var strTeams = $('#ddlRotaTeams').val();
        var CookieName = "masterdutyteams";
        $.cookie(CookieName, strTeams, {expires: 1});
		$.cookie('searchMasterDuty', '', {expires: 1});
		$.cookie('searchmiscdutyname', '', {expires: 1});
		$.cookie('searchdutyMisctype', '', {expires: 1});
		if($.cookie('selectRotaId') > 0)
		{
			$.cookie('selectRotaId', '', {expires: 1});
		}
		window.localStorage.removeItem('DataTables_joblisting_/');
        var $tabs = $('#masterdutiestabs').tabs();
        var selected = $tabs.tabs('option', 'active');
		if ( $(".master-jobs-context-menu").length )
		{
			$('.master-jobs-context-menu').contextMenu('destroy');
		}
		if ( $(".listduty-context-menu-jobs").length )
		{
			$('.listduty-context-menu-jobs').contextMenu('destroy');
		}
		if ( $(".dutyjob-context-menu").length )
		{
			$('.dutyjob-context-menu').contextMenu('destroy');
		}
		if ( $(".Miscduty-context-menu-0").length )
		{
			$('.Miscduty-context-menu-0').contextMenu('destroy');
		}
        ListJobs(0, 0);
        ListMasterDuties(selected, selected);
    }
</script>
