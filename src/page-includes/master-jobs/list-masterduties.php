<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/master-jobs-functions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/helpers.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/init.php';
include_once '../../class-includes/pageperms.php';
include_once 'setTeamCookieJob.php';

$strSessionAdditionalFields = 'JobsUserFields';
$windowheightoffset = 280;
$windowheightList= 428;

$currid = $_REQUEST["id"] ??  0;
$intListType = $_REQUEST["listtype"] ?? 0;
$intArchived = $_REQUEST["archived"] ?? 0;

$pageid = 2;
$strSearchText = $_REQUEST['strSearchText'] ?? '';
//Check User Authentication
$intHourWidth = 80;
$intDutyHeight = 30;
$currenttop = 55;
$earlieststart = 0;
$lateststart = 24;
$intDutyID = $currid;
$intDutyTypeID = 1;

if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
}else{
    $strTeams = 0;
}

if($strTeams == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $strTeams);
}

if ($permissions->cancreate == 1) {
    $classname = 'dutydroppable';
} else {
    $classname = 'notdutydroppable';
}
$isShowEnded = isset($_REQUEST['isShowEnded']) ? (int)$_REQUEST['isShowEnded'] : 0;
$intAreaID = $_SESSION['user']['AreaID'];
$rsDutiesJson = getMasterDutiesList($intAreaID, $intDutyTypeID, $intArchived, $strSearchText, $strTeams, $isShowEnded);

$rsDuties = json_decode($rsDutiesJson, true);

$earlieststart = 0;
$lateststart = 0;
$rsDuties[0]['MasterDutyID'] = isset($rsDuties[0]['MasterDutyID']) ? $rsDuties[0]['MasterDutyID'] : 0;
if (isset($rsDuties) && $rsDuties[0]['MasterDutyID'] > 0) {
    $CountRow = count($rsDuties);
    for ($row = 0; $row < $CountRow; $row++) {
        $intThisEnd = ceil($rsDuties[$row]['EndTime'] / 3600);
        if ($intThisEnd > $lateststart) {
            $lateststart = $intThisEnd;
        }
        $intThisStart = floor($rsDuties[$row]['StartTime'] / 3600);
        if ($intThisStart < $earlieststart) {
            $earlieststart = $intThisStart;
        }
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['DutyName'] = $rsDuties[$row]['DutyName'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['TeamID'] = $rsDuties[$row]['TeamID'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['StartTime'] = $rsDuties[$row]['StartTime'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['EndTime'] = $rsDuties[$row]['EndTime'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['Duration'] = $rsDuties[$row]['Duration'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['WindowDuration'] = $rsDuties[$row]['WindowDuration'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['StartDate'] = $rsDuties[$row]['StartDate'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['EndDate'] = $rsDuties[$row]['EndDate'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['backcolour'] = $rsDuties[$row]['ColourBackground'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['forecolour'] = $rsDuties[$row]['ColourFont'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['dotw'] = $rsDuties[$row]['dotw'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['lastmoddate'] = $rsDuties[$row]['LastModDate'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['dutyhasjobs'] = 1;
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d0'] = $rsDuties[$row]['Sat'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d1'] = $rsDuties[$row]['Sun'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d2'] = $rsDuties[$row]['Mon'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d3'] = $rsDuties[$row]['Tue'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d4'] = $rsDuties[$row]['Wed'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d5'] = $rsDuties[$row]['Thu'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d6'] = $rsDuties[$row]['Fri'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count0'] = $rsDuties[$row]['CountSat'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count1'] = $rsDuties[$row]['CountSun'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count2'] = $rsDuties[$row]['CountMon'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count3'] = $rsDuties[$row]['CountTue'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count4'] = $rsDuties[$row]['CountWed'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count5'] = $rsDuties[$row]['CountThu'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count6'] = $rsDuties[$row]['CountFri'];

        if (!is_null($rsDuties[$row]['JobID'])) {

            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobTypeID'] = $rsDuties[$row]['JobTypeID'];
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['Job'] = $rsDuties[$row]['Job'];
            if ((int)$rsDuties[$row]['JobStartTime'] < 86400 && (int)$rsDuties[$row]['JobEndTime'] < 86400
                && (int)$rsDuties[$row]['EndTime'] > 86400
                && (((int)$rsDuties[$row]['JobStartTime'] + 86400) >=  (int)$rsDuties[$row]['StartTime'])
                && (((int)$rsDuties[$row]['JobEndTime'] + 86400) >=  (int)$rsDuties[$row]['StartTime'])
                && (((int)$rsDuties[$row]['JobStartTime'] + 86400) <=  (int)$rsDuties[$row]['EndTime'])
                && (((int)$rsDuties[$row]['JobEndTime'] + 86400) <=  (int)$rsDuties[$row]['EndTime'])) {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobStartTime'] = ($rsDuties[$row]['JobStartTime'] + 86400);
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobEndTime'] = ($rsDuties[$row]['JobEndTime'] + 86400);
            } else {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobStartTime'] = $rsDuties[$row]['JobStartTime'];
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobEndTime'] = $rsDuties[$row]['JobEndTime'];
            }
            if (is_null($rsDuties[$row]['JobBackColour'])) {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobBackColour'] = 'dddddd';
            } else {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobBackColour'] = $rsDuties[$row]['JobBackColour'];
            }
            if (is_null($rsDuties[$row]['JobBackColour'])) {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobForeColour'] = '000000';
            } else {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobForeColour'] = $rsDuties[$row]['JobForeColour'];
            }
        } else {
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['dutyhasjobs'] = 0;
        }
    }
}

// The hours in the day....
echo '<div style="overflow: hidden; position: absolute; width:76%; height:30px; left:300px; top:' . $currenttop . 'px" id="top" class="times">';
$CountValue = ($lateststart + $earlieststart);
for ($i = $earlieststart; $i <= ($CountValue < 25 ? 24:48); $i++) {
    echo '<div style="width:' . $intHourWidth . 'px;  position:absolute; left:' . (($i - $earlieststart) * $intHourWidth) . 'px; top:0px">';
    echo '<div class="highlighted" style="position:absolute; left: 0px; top:0px; width:' . $intHourWidth . 'px; height:15px">' . (gmdate("H:00", $i * 3600)) . '</div>';
    echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
    echo '</div>';
}
echo '</div>';

$currenttop = $currenttop + 30;
// ################################################################ First a div with the Duty names
$pageArr = array();
if (isset($arrDuties)) {
    $intCounter = 0;
    $prevDutyID = 0;
    $dayletter = array('S', 'S', 'M', 'T', 'W', 'T', 'F');
    echo '<div style="overflow: hidden; font-size:10px; position: absolute; width:79.8%; height:22px; left:0px; top:' . ($currenttop - 20) . 'px" id="daynames">';
    echo '<label for="searchdutyname" class="sr-only">Search Master Duty</label>';
    echo '<input id="searchdutyname" name="searchdutyname" type="text" aria-label="Search Master Duty" style="height:15px; top:0px; bottom:10px; width:120px" size="20" value="' . $strSearchText . '" />';
	echo '&nbsp;<i class="fa fa-search buttonEnabled" id="searchMasterDuty" role="button" aria-label="search master duty" tabindex="0"></i>'; 

    for ($intDOTW = 0; $intDOTW <= 6; $intDOTW++) {
        echo '<div style="overflow:hidden; position: absolute; width:9px; height:' . ($intDutyHeight - 1) . 'px; left:' . (210 + ($intDOTW * 10)) . 'px; top:0px">';
        echo $dayletter[$intDOTW];
        echo '</div>';
    }
    echo '</div>';
    echo '<div style="overflow: scroll; font-size:10px; position: absolute; width:300px; left:0px; top:' . $currenttop . 'px" class="list-duties_misc" id="names">';
    $totalRowCount = 0;
    foreach ($arrDuties as $intDutyID => $arrDuty) {
		$className	=	"elementCount_".$totalRowCount;
        $end_time = FormatTime($arrDuty['EndTime']);
        $start_time = FormatTime($arrDuty['StartTime']);
        if ($intArchived == 0) {
            echo '<div class="listduty-context-menu-jobs duty-list '.$className.'"  title = "' . $arrDuty['DutyName'] . '" id="' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="background-color: #99bfe6; position: absolute; width:230px; height:' . ($intDutyHeight - 1) . 'px; left:0px; top:' . ($intCounter * $intDutyHeight) . 'px;cursor: pointer"  dutyStart="' . $start_time . '" dutyEnd="' . $end_time . '" dutyName="'.trim(substr($arrDuty['DutyName'], 0, 20)).'"  teamid="'.$arrDuty['TeamID'].'">';
        } else {
            echo '<div class="listdutyarchived-context-menu '.$className.'" id="' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="background-color: #99bfe6; position: absolute; width:280px; height:' . ($intDutyHeight - 1) . 'px; left:0px; top:' . ($intCounter * $intDutyHeight) . 'px" >';
        }
        echo '<b>' . trim(substr($arrDuty['DutyName'], 0, 31)) . '</b>';
        echo '<br>';
        echo '('.number_format((float)($arrDuty['Duration'] / 3600), 2, '.', '').' Hours)';

        $arrDoTW = ExplodeStringToArray($arrDuty['dotw']);
        for ($intDOTW = 0; $intDOTW <= 6; $intDOTW++) {
            if (isset($arrDoTW[$intDOTW])) {
                if ($arrDuty['count' . $intDOTW] == 0) {
                    echo '<div class="greenDutyBox" style="overflow:hidden; position:absolute; width:10px; height:' . ($intDutyHeight - 1) . 'px; left:' . (207 + ($intDOTW * 10)) . 'px; top:0px; border:1px solid green;">';
                    echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:' . ($intDutyHeight - 1) . 'px;top:30%;" title="'.$arrDuty['d'.$intDOTW].' '.($arrDuty['d'.$intDOTW] > 1 ? 'Duties' : 'Duty').' required on this day.
The number of times this Duty has been used in any Rota pattern on this day is ['.$arrDuty['count'.$intDOTW].'] "></span>';
                } else {
                    echo '<div class="orange" style="overflow:hidden; position:absolute; width:10px; height:' . ($intDutyHeight - 1) . 'px; left:' . (207 + ($intDOTW * 10)) . 'px; top:0px; border:1px solid orange;">';
                    echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:' . ($intDutyHeight - 1) . 'px;top:30%;" title="'.$arrDuty['d'.$intDOTW].' '.($arrDuty['d'.$intDOTW] > 1 ? 'Duties' : 'Duty').' required on this day.
The number of times this Duty has been used in any Rota pattern on this day is ['.$arrDuty['count'.$intDOTW].'] "></span>';
                }
            } else {
                echo '<div class="grey" style="overflow:hidden; position:absolute; width:10px; height:' . ($intDutyHeight - 1) . 'px; left:' . (207 + ($intDOTW * 10)) . 'px; top:0px; border:1px solid grey;">';
                echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:' . ($intDutyHeight - 1) . 'px;top:30%;" title="Duty not available on this day."></span>';
            }
            echo '</div>';
        }

        echo '</div>';
        $prevDutyID = $intDutyID;
        $intCounter++;
		$totalRowCount++;
    }
    echo '</div></div>';
    // ################################################################ End the Duty Names

    // ################################################################ First a div with the Duties and Jobs
    $intCounter = 0;
    $prevDutyID = 0;
    echo '<div style="overflow:scroll; position: absolute; width:76%;  left:300px; top:' . $currenttop . 'px" id="duties" class="list-duties_misc" onScroll="setDutyListingScrollPos()">';
	echo '<div id="dutyListingPos"  name="dutyListingPos"></div>';
    foreach ($arrDuties as $intDutyID => $arrDuty) {
		$className	=	"elementCount1_".$totalRowCount;
        $end_time = FormatTime($arrDuty['EndTime']);
        $start_time = FormatTime($arrDuty['StartTime']);
        // a holder for everythnig
        if ($permissions->canmodify == 1) {
            if ($intArchived == 0) {
                echo '<div class="listduty-context-menu-jobs '.$className.'" id="dutydet' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '"  style="height:' . ($intDutyHeight) . 'px; position:absolute; top:' . ($intCounter * $intDutyHeight) . 'px;cursor: pointer" dutyStart="' . $start_time . '" dutyEnd="' . $end_time . '" teamid="'.$arrDuty['TeamID'].'">';
            } else {
                echo '<div class="listdutyarchived-context-menu '.$className.'" id="dutydet' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="height:' . ($intDutyHeight) . 'px; position:absolute; top:' . ($intCounter * $intDutyHeight) . 'px">';
            }
        } else {
            if ($intArchived == 0) {
                echo '<div class="listduty-context-menu-jobs '.$className.'" id="dutydet' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="height:' . ($intDutyHeight) . 'px; position:absolute; top:' . ($intCounter * $intDutyHeight) . 'px" teamid="'.$arrDuty['TeamID'].'">';
            } else {
                echo '<div class="listdutyarchivedbasic-context-menu '.$className.'" id="dutydet' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="height:' . ($intDutyHeight) . 'px; position:absolute; top:' . ($intCounter * $intDutyHeight) . 'px">';
            }
        }

        // Draw in the Times
        drawtimecellsMasterJobs("2015-01-01", $earlieststart, $lateststart, $intHourWidth, $intDutyHeight - 1, $intDutyID, $arrDuty['StartTime'], $arrDuty['EndTime'], $arrDuty['TeamID']);

        $intWidth = (($arrDuty['EndTime'] - $arrDuty['StartTime']) / 3600) * $intHourWidth;
        $left = ($arrDuty['StartTime'] / 3600) * $intHourWidth;
        echo '<div class="' . $classname . ' dropeventscall" ondrop="drop(event)"  dutyid="' . $intDutyID . '" id="' . 'duty_' . $intDutyID . '" lastmoddate="' . $arrDuty['lastmoddate'] . '" style="width: ' . $intWidth . 'px; height:' . ($intDutyHeight - 8) . 'px; position:absolute; left:' . $left . 'px; top:4px; background-color:#' . strval($arrDuty['backcolour']) . '; color:#' . strval($arrDuty['forecolour']) . ';">';
        if (isset($arrDuty['jobs'])) {
            foreach ($arrDuty['jobs'] as $intJobID => $arrJob) {
                $intJobWidth = (($arrJob['JobEndTime'] - $arrJob['JobStartTime']) / 3600) * $intHourWidth;
                $intJobLeft = (($arrJob['JobStartTime'] / 3600) * $intHourWidth) - $left;

                echo '<div  title= "' . $arrJob['Job'] . '"class="dutyjob-context-menu" "dutyStart="' . $arrDuty['StartTime'] . '" duty_end="' . $arrDuty['EndTime'] . '"  dutyid="' . $intDutyID . '" jobid="' . $intJobID . '" id="d' . strval($intDutyID) . 'j' . strval($intJobID) . '" lastmoddate="' . $arrDuty['lastmoddate'] . '" style="overflow:hidden; border: 1px solid #C0C0C0; width:' . $intJobWidth . 'px; height:' . ($intDutyHeight - 12) . 'px; position:absolute; left:' . $intJobLeft . 'px; padding:2px; top:2px; background-color:' . $arrJob['JobBackColour'] . '; color:' . $arrJob['JobForeColour'] . ';" onClick="highlightJobs(' . "'$intJobID'". ')">';
                echo $arrJob['Job'];
                echo '</div>';
            }
        }
        echo '</div>';
        echo '</div>';
        $prevDutyID = $intDutyID;
        $intCounter++;
		$totalRowCount++;
    }
    echo '</div>';
}
else {
    $intCounter = 0;
    $prevDutyID = 0;
    $dayletter = array('S', 'S', 'M', 'T', 'W', 'T', 'F');
    echo '<div style="overflow: hidden; font-size:10px; position: absolute; width:250px; height:22px; left:0px; top:' . ($currenttop - 20) . 'px" id="daynames">';
    echo '<label for="searchdutyname" class="sr-only">Search Master Duty</label>';
    echo '<input id="searchdutyname" name="searchdutyname" type="text" aria-label="Search Master Duty" style="height:15px; top:0px; bottom:10px; width:120px;" size="20" value="' . $strSearchText . '" />';
	echo '&nbsp;<i class="fa fa-search buttonEnabled" id="searchMasterDuty" role="button" aria-label="search master duty" tabindex="0"></i>'; 
    for ($intDOTW = 0; $intDOTW <= 6; $intDOTW++) {
        echo '<div style="overflow:hidden; position: absolute; width:9px; height:' . ($intDutyHeight - 1) . 'px; left:' . (210 + ($intDOTW * 10)) . 'px; top:0px">';
        echo $dayletter[$intDOTW];
        echo '</div>';
    }
    echo '</div>';
    echo '<div style="margin-top:1%; margin-left:430px; font-size:12px; width:250px; height:22px; left:0px;">No Record Found</div>';
}
$pageArr[] = COUNT($pageArr) + 1;

?>
<script type="text/javascript" src="../../js/master-job.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript">
    var pageArr = '<?php echo json_encode($pageArr); ?>';
    $(document).ready(function () {
        if (<?php echo empty($permissions->canview) ? 0 : $permissions->canview ?> == 0)
        {
            $('#content').load('page-includes/no_access.php', function () {});
        }
    else
        {
            $("#masterdutieslist").DataTable({
                paging: false,
                scrollY: 400,
                info: false,
                stateSave: true,
                "initComplete": function (settings, json) {
                    DoMDResize();
                }
            });
        }
        if ($('#searchdutyname').length > 0) {
            var searchinput = $('#searchdutyname');
            var intlength = $('#searchdutyname').val().length * 2;
            searchinput[0].setSelectionRange(intlength, intlength);
        }
        $('#searchMasterDuty').on("click", function() {
            var strInput = $('#searchdutyname').val().trim();
			$.cookie("searchMasterDuty", strInput);
            ListMasterDutiesJobs(0, 0, 0, $.cookie("searchMasterDuty"));
        });
		$("#searchdutyname").keyup(function(event) {
			if (event.keyCode === 13) {
				$("#searchMasterDuty").click();
			}
		});

    });

    $('#duties').on('scroll', function () {
        $('#names').scrollTop($(this).scrollTop());
        $('#top').scrollLeft($(this).scrollLeft());
        $('#unallocatedduties').scrollLeft($(this).scrollLeft());
        $('#unallocatedjobs').scrollLeft($(this).scrollLeft());
        $(".leaders").scrollLeft($(this).scrollLeft());
    });

    $('#names').on('scroll', function () {
        $('#duties').scrollTop($(this).scrollTop());
    });

    function DoResize() {
        var widowwidth = $(window).width() - 285;
        var widowheight = $(window).height() - <?=$windowheightoffset?>;
        $("#duties").width(widowwidth).height(widowheight);
        $("#names").height(widowheight);
        $("#top").width(widowwidth);
    }

	var widowheight_list = $(window).height() - <?=$windowheightList?>;
	$(".list-duties_misc").height(widowheight_list);

    function ListMasterDutiesJobs(listtype, id, showedit, strInput) {
        var strTeams = $('#ddlRotaTeams').val();
        $.post("page-includes/master-jobs/list-masterduties.php", {
                id: id,
                archived: 0,
                teamId: strTeams,
                strSearchText: strInput
            },
            function(data,status){
                $('#dutieslistdiv0').html(data);
				$('#duties').scrollTop(Math.abs($.cookie("dutyListingPosTop")));
				$('#duties').scrollLeft(Math.abs($.cookie("dutyListingPosLeft")));
                GoToRow(id);
            }
        );
    }
function setDutyListingScrollPos()
{
	let top = $('#dutyListingPos').position().top;
	let left = $('#dutyListingPos').position().left;
	$.cookie("dutyListingPosTop", top);
	$.cookie("dutyListingPosLeft", left);
}
</script>
