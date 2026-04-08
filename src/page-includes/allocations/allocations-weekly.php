<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
date_default_timezone_set('UTC');
require_once '../../../vendor/autoload.php';
include_once '../../function-includes/bootstrap.php';
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/skillsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
include_once '../../page-includes/allocations/weekly/service/AllocationViewService.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../../page-includes/allocations/weekly/filters/filters-script.php';
include_once __DIR__. '/../../function-includes/user-scheduling-team-list.php';

// ########################################## A few settings that affect page layout ##########################################
use Symfony\Component\HttpFoundation\Request;

use App\Models\User\RefRole;
use Traits\UserRoleTrait;
$userRoleTrait = new class {
    use UserRoleTrait;
};
$request = Request::createFromGlobals();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$service = new AllocationService();
$Vservice = new AllocationViewService();
$commonObj = new classCommonDBFunctions();
$isFreelencer =  $commonObj->GetIsFreelencerByNetlogin($strUser);
$_SESSION['user']["isFreelance"] = !empty($isFreelencer) ? 1 : 0;
$filterName = 'Filters';
$screenName = 'ViewWeekly';
$schedulingPersonId = GetScheduledPersonIdbyUserId($userId);
$arrUser = GetScheduledPersonTeamDetails($schedulingPersonId);
if (isset($_POST['teamId'])) {
    $intTeamID = $_POST['teamId'];
} else {
    $intTeamID = GetDefaultSchedulingTeamIdByLogin($schedulingPersonId);
    $_POST['teamId'] = $intTeamID;
    $request->request->set('teamId', $intTeamID);
}

$strUserLogin = $arrUser[$intTeamID]['Login'] ?? '';
if (strtolower($strUserLogin) == strtolower($strUser)) {
  $intThisIsMe = 1;
} else {
  $intThisIsMe = 0;
}

if (isset($_SESSION['screenwidth'])) {
    $intScreenWidth = $_SESSION['screenwidth'];
} else {
    $intScreenWidth = 1600;
}
$intDateHeight = 55;
$intStatusHeight= 10;
$intRowHeight = 45;
$intNamesWidth = 250;
$intDutyWidth = ($intScreenWidth - $intNamesWidth - 40) / 7;
$intTableWidth = $intNamesWidth + ($intDutyWidth * 7);
$intShowAll = 1;
// ########################################## END  ###########################################################################



//change io nto request
if (isset($_REQUEST['cando'])) {
    $intShowCanDo = $_REQUEST['cando'];
}
else {
    $intShowCanDo = 0;
}
// ######################################################## Work Out the week we are to view ###############################################################
//change into request
if (isset($_REQUEST['WeekNumber'])) {
    // Passed a week Number?
    $intWeekNumber = $_REQUEST['WeekNumber'];
}
else {
    if (isset($_SESSION["allocations"]["WeekNumber"])) {
        // Is the session set?
        $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
    } else {
        $bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
        $intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? 0;
    }
}

$selSchPersonId = $request->get('scheduledPersonId', '');

$filterQuery = '';
if($request->get('queryStr') != ''){
  $filterQuery = " AND ".$request->get('queryStr');
}
$filterQuery2 = $intTeamID;
if(is_array($request->get('additionalTeams'))){
  $filterQuery2 = $request->get('additionalTeams');
  $addteam=implode(",",$filterQuery2);
  $addteam=$addteam.",".$intTeamID;
  $filterQuery2 = $addteam;
}

$filterQuery3 = '';
if($request->get('queryStr3') != ''){
  $filterQuery3 = $request->get('queryStr3');
}
$filterOrderStr = '';
$intSortOrder = 0;
if($request->get('orderStr') != ''){
  $filterOrderStr = $request->get('orderStr');
  $intSortOrder = $request->get('orderStr');
}

$selFilterType = '';
if($request->get('selFilterType') != ''){
  $selFilterType = $request->get('selFilterType');
}
$selFilterId = '';
if($request->get('selFilterId') != ''){
  $selFilterId = $request->get('selFilterId');
}
$skillFilterDaily ='';
if ($request->get('skillFilterDaily') != '') {
  $skillFilterDaily = $request->get('skillFilterDaily');
}
$dutyFilterDaily ='';
if ($request->get('dutyFilterDaily') != '') {
  $dutyFilterDaily = $request->get('dutyFilterDaily');
}
echo '<input type="hidden" name="selFilterType" id="selFilterType" value="'.$selFilterType.'">';
echo '<input type="hidden" name="selFilterId" id="selFilterId" value="'.$selFilterId.'">';

$datefromweek = $commonObj->GetWeekStartDateByWeekNoFromTimeDim($intWeekNumber,'ByweeknoOnly',NULL);
$dateStartStr = explode(' ',$datefromweek['dDateTime']);
$dteStartDate = $dateStartStr[0];
$dteEndtDate = date('Y-m-d', strtotime($dteStartDate. ' + 6 days'));

$validDateNo =  strtotime('-7 year', strtotime(date('Y-m-d')));
$validDate= date('jS F Y',$validDateNo);

if ($intTeamID == 0) {
    echo '<br>';
    echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
    echo '<br><br>';
     echo 'You are attempting to view the Allocations for your Default Team.<br>Please assign a default team or select the required team from the allocations menu.';
    echo '<br></br><br>';
    echo '</div>';
}
else {

    $arrTeamDefaults = GetTeamDefaults($userId, $intTeamID);

    $isScheduler = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULER);
    $isManager = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::MANAGER);
    $isTeamAdmin = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_ADMIN);
    $hasShiftleaderRole = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SHIFT_LEADER);
    $isTeamLeader = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::TEAM_LEADER);
    $isSchedulingTeamViewer = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_VIEWER);
    $isScheduledPerson = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULED_PERSON);
    $isAdmin = $userRoleTrait->isSystemAdmin();
    //If system admin or area admin the person have STA permission
    if($isTeamAdmin == 0) {
        $isTeamAdmin = $userRoleTrait->checkEditWeeeklyAdminRole($intTeamID);
    }

    $isShiftleader = 1;
    $request->request->set('isShifttoCheck', 0);
    $request->request->set('cando', $intShowCanDo);

    $canViewAdditional = 0;
    if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isManager == 1)) {
        $isShiftleader = 0;
        $canViewAdditional = 1;
    }

    if ($isManager == 1 || $isScheduler == 1 || $isTeamAdmin == 1) {
        $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
    } else if(($hasShiftleaderRole == 1 && $intThisIsMe == 1) || ($isSchedulingTeamViewer == 1 && $intThisIsMe == 1) || ($intThisIsMe == 1)) {
        $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
    }
    else {
        $dteFirstDate = date("Y-m-d", strtotime("-$intViewYears years"));
    }

    $intIsFreelance = $_SESSION['user']["isFreelance"];
    // Restrict View for freelancers....
    $intFreelanceMaskDays = $arrTeamDefaults[$intTeamID]['FreelancerMaskingDays'] ;
    $intAllowInBuilding = $arrTeamDefaults[$intTeamID]['AllowInBuilding'];
    $intSignInDays = $arrTeamDefaults[$intTeamID]['SignInDays'];
    $intConfirmedDays = $arrTeamDefaults[$intTeamID]['ConfirmedDays'];
    $intMaskDays = $arrTeamDefaults[$intTeamID]['MaskAfter'];
    $intMaskType = $arrTeamDefaults[$intTeamID]['MaskType'];
    $strteamName = $arrTeamDefaults[$intTeamID]['Description'];
    $intColourWeek = $arrTeamDefaults[$intTeamID]['ColourWeek'];
    $myschdeullingPersonID = $schedulingPersonId;

    $intSkillsCount = CountJobSkillsInDepartment($intTeamID);
    $arrFilters = GetDutyFiltersByDepartment($intTeamID);

    if($isAdmin == 1 || $isScheduler == 1 || $isTeamAdmin == 1 || $hasShiftleaderRole == 1 ) {
        $intSignInAll = 1;
	}
    else {
        $intSignInAll = 0;
		//if(($isScheduler == 1) && ($isManager == 0))
		//		$intSignInAll = 1;
    }
	if($isAdmin == 1 || $isScheduler == 1 || $isTeamAdmin == 1  || $hasShiftleaderRole == 1 ) {
       $intovertime = 1;
    }
    else {
        $intovertime = 0;
		if(($isScheduler == 1) && ($isManager == 0) && ($hasShiftleaderRole == 0))
				$intovertime = 1;
    }
	if($isAdmin == 1 || $isScheduler == 1 || $isTeamAdmin == 1) {
       $redpound = 1;
	   $intCanViewComments = 1;
    }
    else {
        $redpound = 0;
		$intCanViewComments = 0;

    }
// Viewing Person Comments
    if ($isAdmin == 1 || $isManager == 1 || $isScheduler == 1 || $isTeamAdmin == 1) {
        //i think we dont have - id concept in A7 for team NAveeta
        if ($intTeamID < 0) {
            $strCanEditPerson = 'staff-context-menu ';
        }
        else {
            $strCanEditPerson = '';
        }
    }
    else {
        $strCanEditPerson = '';
    }
// ######################################################## End ###############################################################

// Are we going to import this week (Dep negative)
    /*This  Code will replaced with WS (types teams in A7)
    earlier it was NegativeDept with Schedule All concept*/
    $intUTCLastPublished = 0;
    if ($intTeamID < 0) {
        $intLastAllowed = addweeks(bbcweeknumber(date("Y-m-d")), $arrTeamDefaults[$intTeamID]['WeeksViewAllowed']);
        $intUTCLastPublished = GetLastPublish($intWeekNumber, $intTeamID);
        if ($intLastAllowed >= $intWeekNumber && $arrTeamDefaults[$intTeamID]['AutoImport'] == 1) {
            $UTCLastEdit  = GetLastEditUTC($intWeekNumber, $intTeamID);
            if ($UTCLastEdit > $intUTCLastPublished) {
                ImportScheduAll($intTeamID, $intWeekNumber);
                $intUTCLastPublished = time();
            }
        }
    }

    $arrHolidays = calculateBankHolidayst(date("Y", strtotime($dteStartDate)));
    $intPreviousWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate. ' - 7 days')));
    $intPreviousWeek = $intPreviousWeekArray['ixYearWeek'];
    $intNextWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate. ' + 7 days')));
    $intNextWeek = $intNextWeekArray['ixYearWeek'];
    $_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;
    $intTeamIDs = $intTeamID;

	$arrHiddenDays= array();
    $arrRequests = GetAppliedRequestsANdLocks($dteStartDate, $dteEndtDate);
    $arrHiddenDays = ReadHiddenDays($intTeamID, $dteStartDate, $dteEndtDate);
    $weekstatus = $Vservice->checkWeekPublishStatus($intTeamID,$intWeekNumber);
    if($weekstatus > 0) {
        $intIgnoreRota=1;//Check Week here
    }else{
        $intIgnoreRota=0;//Check Rota here
    }
    if($hasShiftleaderRole == 1 && $intShowCanDo == 1) {
        $request->request->set('isShifttoCheck', 1);
    }
    if(isset($_POST['isShifttoCheck'])) {
        $request->request->set('isShifttoCheck', (int) $_POST['isShifttoCheck']);
    }

    $isShifttoCheck = $request->get('isShifttoCheck');

    if ($dteStartDate > $dteFirstDate) {
       $arrAllocations = ReadMultiWeekAllocations($intWeekNumber, $intWeekNumber, $intTeamIDs, $intConfirmedDays, $intMaskDays, $intMaskType, $intCanViewComments, $intIgnoreRota, $intSortOrder, $myschdeullingPersonID,$intColourWeek,$filterQuery,$filterQuery2,$filterQuery3,$filterOrderStr, $selSchPersonId, $isShiftleader, $isShifttoCheck, $schedulingPersonId, $intShowCanDo, 1, $canViewAdditional);
    } else {
        $arrAllocations = array();
    }

    $arrEDP = readedp($dteStartDate, $dteEndtDate);

// When do we restrict the text from?
    $maskfrom = date("Y-m-d", strtotime("+".$intMaskDays." days"));
    if(($_REQUEST['screenName'] ?? '') == 'ShiftsToCheck'){
        echo '<h1 class="sr-only">Shifts To Check</h1>';
    }else{
        echo '<h1 class="sr-only">Weekly Allocation</h1>';
    }
    echo '<table class="tablegreysmallnoborder allocationWeekHeader" border="1" width="100%">';
//echo '<table border="1" width="'.$intTableWidth.'px">';
    echo '<tr height="35px">';
    if ($intShowCanDo == 0 && $isShifttoCheck == 0) {

        echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowAllocations('.$intTeamID.',"'.$intPreviousWeek.'")\';>&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</span></td>';
        echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowAllocations('.$intTeamID.',"'.$intNextWeek.'")\';>Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</span></td>';
    }
    else {
        echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowAllocationsNoCanDo('.$intTeamID.',"'.$intPreviousWeek.'", 1)\';>&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</span></td>';
        echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowAllocationsNoCanDo('.$intTeamID.',"'.$intNextWeek.'", 1)\';>Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</span></td>';
    }
    echo '<td width="120px" class="medtextbold" align="right">Choose Date</td>';
    echo '<td width="75px" class="medtextbold handcursor" align="left"><input type="hidden" id="datepicker"></td>';

    echo '<td width="200px" class="medtextbold" align="center" nowrap>';
    echo 'Allocations for Week '.spinweek($intWeekNumber).'</td>';
    echo '</td>';
    // ------Filters Start ----
    echo '<td colspan="5" valign="top">';
    echo '<td valign="top" width="450px" style="display:inline-flex;">';
    if($intShowCanDo == 0) {
    echo '<select name="teamId" id="teamId" onchange="allocationViewByTeam(this.value,'.$intWeekNumber.')" class="chosen-select">';
    echo '<option value="">Select The Team</option>';
    $teamOptions = getSchedulingTeamList($intTeamID, 'allocation-policy', 'view');
    echo $teamOptions;
    echo '</select>';
    }
    echo '<div class="filterPosition">';
    include(__DIR__ . '/../../components/filters/filters.php');
    echo '</div>';
    echo '</div>';
    echo '</td>';
    // ------Filters END ----
    if ($intCanViewComments == 1 && $intTeamID < 0) {
        echo '<td align="right" rowspan="2" class="medtextbold handcursor">';
        echo '<img src="images/publish.png" height="32" border="0" width="32" onclick="javascript:ImportDuties('.$intTeamID.', \''.$intWeekNumber.'\')";>';
        echo '&nbsp;&nbsp;';
        if ($arrTeamDefaults[$intTeamID]['AutoImport'] == 1 && $intTeamID < 0 && $intUTCLastPublished != 0) {
            if ($intLastAllowed < $intWeekNumber) {
                echo '<img src="images/button_delete.png" height="32" border="0" width="32" onclick="javascript:DeleteDuties('.$intTeamID.', \''.$intWeekNumber.'\')";>';
                echo '&nbsp;&nbsp;';
            }
        }
        echo '</td>';
    }

    echo '<td rowspan="5"></td>';

    echo '</tr>';

    echo '<tr>';
    echo '<td class="medtextbold handcursor" colspan="2">&nbsp;&nbsp;';
    if ($arrTeamDefaults[$intTeamID]['HasDutiesView'] == 1) {
        echo '&nbsp;&nbsp;<img title="Show Production View" class="handcursor" onclick=\'javascript:ShowAllocationsDuties("'.$intTeamID.'","'.$intWeekNumber.'")\'; border="0" src="../images/AllocationsWeeklyDuties.png" width="53px" height="28px">&nbsp;&nbsp;';
    }
    if ($intIsFreelance == 0) {
        echo '<img title="Multi Week View" src="images/AllocationsMultuWeekl.png" width="53px" height="28px" border="0" onclick="javascript:ShowAllocationsMulti('.$intTeamID.', \''.$intWeekNumber.'\')";>';
    }
    echo '<img id="print" class="weeklyPrintIcon" class="handcursor" name="print" border="0" src="images/print.png"  alt="Print" onclick="window.print();">';
    echo '</td>';

    echo '<td colspan="2" class="medtextbold handcursor">';

    if ($intUTCLastPublished != 0) {
        echo 'Last Publish: '.date("d/m/Y H:i", $intUTCLastPublished);
    }
    echo '</td>';

    echo '<td width="400px" align="center" class="medtextbold">';
    if ($isShifttoCheck == 1) {
        echo '<font color="#990000">Showing Shifts To Check for '.$strteamName.'</font>';
    }
    echo '</td>';
    echo '<td colspan="6"></td>';

    echo '</tr> ';
    echo '</table>';

    // End the header
    //have we any allocations to show?

 include_once('readWeeklyRotaAndAllocations.php');
?>
    <div id="dialog-no-filter" title="Information!" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You have a filter set!<br>Because the weekly view is restricted the filter has been removed for this week.<br>Do you want to keep the filter in place for viewing unrestricted weeks or clear it?</p>
    </div>

    <script language="JavaScript" type="text/javascript">
        <?php
        if (isset($_SESSION['showdailycomments'])) {
            echo "$('.comments').toggle();";
        }

        ?>
        function allocationViewByTeam(teamId,weekno){
          ShowAllocations(teamId,weekno);
        }

        $(function() {
			<?php if($validDateNo > strtotime($dteStartDate) && ($dteStartDate!='')){
				$bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
				$_SESSION["allocations"]["WeekNumber"] = $bbcweeknumberArray['ixYearWeek'] ?? 0;
				?>
				customAlert("Allocate cannot open a Week older than seven years. Please select another Week.");
				 ShowAllocations('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
			<?php } ?>
            $('#btnAdd').click(function() {
                $('.comments').toggle();
                $.post("page-includes/ajax-calls/allocations-toggle-comments.php", {
                })
            });
        });
        $(document).ready(function() {
            $(".chosen-select").chosen({
                no_results_text: "Oops, nothing found!",
                width: "160px"
            });
            $(function() {
                $( "#accordion" ).accordion({
                    heightStyle: "content",
                    collapsible: true,
                    active: false,
                    beforeActivate: function( event, ui ) {
                        $('*').qtip('hide');
                    }
                });
                $('.ui-accordion-content').css({
                    "padding":"2px",
                    "width":"500px"
                });
                $('.accordiontop').css({
                    "width":"250px"
                });
            });
            $(document).click(function (event) {
                if(!$(event.target).closest('#accordion').length) {//if you clicked outside of the accordion
                    $("#accordion").accordion({active: false});//collapse all the panels
                }
            });

            // If cookie is set, scroll to the position saved in the cookie.
            if ( $.cookie("vscroll") !== null ) {
                $("#weeklynames").scrollTop( $.cookie("W-vscroll") );
                $("#weeklyduties").scrollTop( $.cookie("W-vscroll") );
                $("#weeklyduties").scrollLeft( $.cookie("W-hscroll") );
            }
            // When scrolling happens....
            $("#weeklyduties").on("scroll", function() {
                // Set a cookie that holds the scroll position.
                $.cookie("W-vscroll", $("#weeklyduties").scrollTop() );
                $.cookie("W-hscroll", $("#weeklyduties").scrollLeft() );
                $('#weeklynames').scrollTop($(this).scrollTop());
                $('#weeklytop').scrollLeft($(this).scrollLeft());
            });
            $('[title]').qtip({
                position: {
                    at: 'bottom left',
                    my: 'right center',
                    effect: false,
                    viewport: $(window),
                    adjust: {
                        method: 'none shift'
                    }
                },
                style: { classes: 'qtip-rounded qtip-shadow qtip-light'}
            });

            $('.tipremotecomments').each(function() {
                $(this).qtip({
                    content: {
                        text: function(event, api) {
                            var reqData = {
                            'dutyDate' : api.elements.target.attr('DutyDate'),
                            'schPersonId' : api.elements.target.attr('SchedulingPersonID'),
                            'teamId' : api.elements.target.attr('teamid'),
                            'dutyId' : api.elements.target.attr('dutyId'),
                            'rolepermission' : 0
                        };
                            $.ajax({
                                type: "post",
                                data: reqData,
                                url: 'page-includes/allocations/allocations-duty-comments.php'
                            })
                                .then(function(content) {
                                        // Set the tooltip content upon successful retrieval
                                        api.set('content.text', content);
                                    },
                                    function(xhr, status, error) {
                                        // Upon failure... set the tooltip content to error
                                        api.set('content.text', status + ': ' + error);
                                    });
                            return 'Loading...'; // Set some initial text
                        }
                    },
                    position: {
                        viewport: $(window)
                    },
                    style: 'qtip-rounded qtip-shadow qtip-light',
                    events: {
                        move: function(event, api) {
                            customtipweekly('', '');
                        }
                    }
                });
            });

            $('.signedtip').each(function() {
                $(this).qtip({
                    content: {
                        text: function(event, api) {
                            $.ajax({
                                url: 'page-includes/allocations/allocations-signedin-info.php',
                                type: 'POST',
                                data: {
                                    allocationsSPID: api.elements.target.attr('allocationsSPID'),
									StartTime: api.elements.target.attr('StartTime'),
									EndTime: api.elements.target.attr('EndTime'),
									DutyName: api.elements.target.attr('DutyName')
                                }
                            })
                            .then(function(content) {
                                // Set the tooltip content upon successful retrieval
                                api.set('content.text', content);
                            },
                            function(xhr, status, error) {
                                // Upon failure... set the tooltip content to error
                                api.set('content.text', status + ': ' + error);
                            });
                            return 'Loading...'; // Set some initial text
                        }
                    },
                    position: {
                        viewport: $(window)
                    },
                    style: 'qtip-rounded qtip-shadow qtip-light'
                });
            });
            $('.tipremoteedp').each(function() {
                $(this).qtip({
                    content: {
                        text: function(event, api) {
                            $.ajax({
                                url: 'page-includes/allocations/allocations-edp-info.php',
                                type: 'POST',
                                data: {schedulingPersonId: api.elements.target.attr('SchedulingPersonID'),
                                    ddate: api.elements.target.attr('DutyDate'),
                                    teamId: api.elements.target.attr('teamid')
                                }
                            })
                                .then(function(content) {
                                        // Set the tooltip content upon successful retrieval
                                        api.set('content.text', content);
                                    },
                                    function(xhr, status, error) {
                                        // Upon failure... set the tooltip content to error
                                        api.set('content.text', status + ': ' + error);
                                    });
                            return 'Loading...'; // Set some initial text
                        }
                    },
                    position: {
                        viewport: $(window)
                    },
                    style: 'qtip-rounded qtip-shadow qtip-light'
                });
            });
            $('.tipremote').each(function() {
            $(this).qtip({
            content: {
            text: function(event, api) {
            $.ajax({
            url: 'page-includes/allocations/allocations-miscinfo.php',
            type: 'POST',
            data: {
              schPersonId: api.elements.target.attr('schPersonId'),
              dutydate: api.elements.target.attr('dutydate'),
              teamId: api.elements.target.attr('teamId')
            }
          })
          .then(function(content) {
            // Set the tooltip content upon successful retrieval
            api.set('content.text', content);
          },
          function(xhr, status, error) {
            // Upon failure... set the tooltip content to error
            api.set('content.text', status + ': ' + error);
          });
          return 'Loading...'; // Set some initial text
        }
      },
      position: {
      viewport: $(window)
      },
      style: 'qtip-rounded qtip-shadow qtip-light'
    });
  });
        });


        $.contextMenu({
            selector: '.staff-context-menu',
            //trigger: 'left',
            callback: function(key, options) {
            },
            items: {

                "edit": {
                    name: "Edit",
                    icon: "edit",
                    // superseeds "global" callback
                    callback: function(key, options) {
                        LogIn = options.$trigger.attr("LogIn");
                        TeamID = options.$trigger.attr("TeamID");
                        $.post("page-includes/admin/edituser.php", {
                                login: LogIn,
                                department: TeamID,
                                showweek: 1,
                            },
                            function(data,status){
                                $.facebox(data);
                            }
                        )
                    }
                },
            },
        })






        <?php
        if (isset($arrAllocations) && count($arrAllocations) > 0) {
        ?>
        $(document).ready(function() {
            setTimeout(function() {
                ResizeWeeklyGrids();
            }, 100); 
        });
        $('#weeklynames').on('scroll', function () {
            $('#weeklyduties').scrollTop($(this).scrollTop());
        });

        $(window).resize(function() {
            if ($("#weeklyduties").length) {
             ResizeWeeklyGrids();
            }
        })
        <?php
        }
        ?>
        $('#FormDutyName').validate({
            rules:{
                "filter":{
                    required:true,
                },
            },
            submitHandler: function(form) {
                $.ajax({type:'POST', url: 'page-includes/allocations/allocations-setfilter.php', data:$('#FormDutyName').serialize(), success: function(data) {
                        ShowAllocations('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
                    }});
            }
        })

        $('#FormSortCode').validate({
            rules:{
                "filter":{
                    required:true,
                },
            },
            submitHandler: function(form) {
                $.ajax({type:'POST', url: 'page-includes/allocations/allocations-setfilter.php', data:$('#FormSortCode').serialize(), success: function(data) {
                        ShowAllocations('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
                    }});
            }
        })


        $('#FormNameFilter').validate({
            rules:{
                "filter":{
                    required:true,
                },
            },
            submitHandler: function(form) {
                $.ajax({type:'POST', url: 'page-includes/allocations/allocations-setfilter.php', data:$('#FormNameFilter').serialize(), success: function(data) {
                        ShowAllocations('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
                    }});
            }
        })


        function ResizeWeeklyGrids() {
            const $window = $(window);
            const windowWidth = $window.width();
            const windowHeight = $window.height();
            const $weeklyNames = $("#weeklynames");
            const $weeklyDuties = $("#weeklyduties");
            const $weeklyTop = $("#weeklytop");
            const topOffset = $weeklyTop.offset();
            var contentOffset = $("#content").offset();

            if (!topOffset || !contentOffset) {
                return;
            }
            var offset = topOffset.top + contentOffset.top;
            const namesWidth = $weeklyNames.outerWidth();
            const availableWidth = windowWidth - namesWidth - 20; 
            const availableHeight = windowHeight - offset; 

            $weeklyDuties.width(availableWidth).height(availableHeight);
            $weeklyTop.width(availableWidth);
            $weeklyNames.height(availableHeight);

            const $dayHeaders = $weeklyTop.children("div.headerEle");
            const dayCount = $dayHeaders.length;
            const cellWidth = availableWidth / dayCount;
            $dayHeaders.each(function(i) {
                $(this).css({
                    width: cellWidth,
                    left: i * cellWidth
                });   
            });

            $('.auto-width-set').each(function(i) {
                $(this).css({
                    width: cellWidth,
                    left: i * cellWidth
                });
                  
            });

            $weeklyDuties.find("div[class*='dutyAllocation_']").each(function() {
                const $cell = $(this);
                const left = parseFloat($cell.css("left"));
                const top = parseFloat($cell.css("top"));
                const colIndex = Math.round(left / $cell.outerWidth()); 
                $cell.css({
                    width: cellWidth - 1.5, 
                    left: colIndex * cellWidth                  
                });
            });
        }

        <?php
        if ($intShowCanDo == 0) {
        ?>
        $(function() {
            $( "#datepicker" ).datepicker({
                showOn: "button",
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true,
                showOtherMonths: 'true',
                selectOtherMonths: 'true',
                firstDay: '6',
                gotoCurrent: 'true',
                changeMonth: true,
                changeYear: true,
                dateFormat: "yy-mm-dd",
                defaultDate: "<?php echo $dteStartDate?>" ,
                onSelect: function (dateText, inst) {
                    var currentdate = dateText;
                    $.post("page-includes/allocations/allocations-set-week-from-date.php", {
                            date: currentdate
                        },
                        function(data,status){{
                            ShowAllocations(<?php echo $intTeamID?>,data)
                        }});
                }
            });
        });
        <?php
        }
        else {

        ?>
        $(function() {
            $( "#datepicker" ).datepicker({
                showOn: "button",
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true,
                showOtherMonths: 'true',
                selectOtherMonths: 'true',
                firstDay: '6',
                gotoCurrent: 'true',
                changeMonth: true,
                changeYear: true,
                dateFormat: "yy-mm-dd",
                defaultDate: "<?php echo $dteStartDate?>" ,
                onSelect: function (dateText, inst) {
                    var currentdate = dateText;
                    $.post("page-includes/allocations/allocations-set-week-from-date.php", {
                            date: currentdate
                        },
                        function(data,status){{
                            ShowAllocationsNoCanDo(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
                        }});
                }
            });
        });
        <?php
        }
        ?>
        function WeeklySignInToDay(date, action, DutyName, StartTime, EndTime, allocationsDutyId, allocationsSpId, signinStatus) {
           $( "#dialog-sign-in" ).dialog(
                {
                    width:600,
                    open: function() {
                        $(this).siblings('.ui-dialog-buttonpane').find('button:eq(1)').focus();
                    },
                    buttons: {
                        "Sign-In / Un-Sign In": function() {
                            $('*').qtip('hide');
                            $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
                                    sdate: date,
                                    DutyName: DutyName,
                                    action: action,
                                    StartTime: StartTime,
                                    EndTime: EndTime,
                                    allocationsDutyId: allocationsDutyId,
                                    allocationsSpId: allocationsSpId,
                                    SigninStatus: signinStatus,
                                    screenType: '1'
                                },
                                function(data,status){
                                    {
                                        ShowAllocations(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
                                    }
                                });
                            $( this ).dialog( "close" );
                        },
                        "Mark As In Building": function() {
                            $('*').qtip('hide');
                            $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
                                    sdate: date,
                                    DutyName: DutyName,
                                    action: 2,
                                    StartTime: StartTime,
                                    EndTime: EndTime,
                                    allocationsDutyId: allocationsDutyId,
                                    allocationsSpId: allocationsSpId,
                                    screenType: '1'
                                },
                                function(data,status){
                                    {
                                        if (data == 0) {
                                            $("#dialog-noinbuilding").dialog({
                                                title: "Alert!!",
                                                resizable: false,
                                                height:160,
                                                width:600,
                                                modal: true,
                                                buttons: {
                                                    OK: function() {
                                                        $( this ).dialog( "close" );
                                                    }
                                                }
                                            });
                                        }
                                        else {
                                            ShowAllocations(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
                                        }

                                    }
                                });
                            $( this ).dialog( "close" );
                        },
                        "Cancel": function() {
                            $( this ).dialog( "close" );
                        }
                    }
                }
            );
        }

        function WeeklySignInDay(date, action, DutyName, StartTime, EndTime, allocationsDutyId, allocationsSpId, signinStatus) {
            $('*').qtip('hide');
            $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
                    sdate: date,
                    DutyName: DutyName,
                    action: action,
                    StartTime: StartTime,
                    EndTime: EndTime,
					allocationsDutyId: allocationsDutyId,
					allocationsSpId: allocationsSpId,
					SigninStatus: signinStatus,
                    screenType: '1'
                },
                function(data,status){
                    {
                        ShowAllocations(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
                    }
                });
        }

        $(function() {
            $( "#skill" ).autocomplete({
                source: "page-includes/allocations/allocations-returnstaffwithskills.php?department=<?php echo $intTeamID?>",
                minLength: 1,
                select: function( event, ui ) {
                    SetFilter('11', ui.item.id, <?php echo $intTeamID?>, 1)
                }
            })
                .on('mouseup', function() {
                    $(this).select();
                });;
        });
    </script>
    <?php
}
?>
<script type="text/javascript" src="js/allocations/weekly/weekly-jobs-view.js?v=<?php echo time(); ?>" data-key-name="highlightRowWeeklyPerson-"></script>
<script type="text/javascript">
$(function() {
    dateCommentOptionsSet(0, 'fa-2x', 'lightblue');
    initializeDateCommentViewIcon();
});
<?php if(empty($teamOptions)) { ?>
    customAlertByModel('Your session has been interrupted. Please reload the week.');
<?php }?>
function EDPEdit(ddate,StartDate,schedulingPersonId, teamId) {
$('*').qtip('hide');
$(function() {
  $( "#dialog-edp-offer" ).dialog(
    {
      width: 400,
      buttons: {
        "Add Comments": function() {
          $.post("page-includes/allocations/allocations-edp-comments.php", {
            ddate: ddate,
            StartDate :StartDate,
            schedulingPersonId: schedulingPersonId,
            teamId: teamId,
            action: 'update',
            page :'weekly',
            intWeekNumber:<?php echo $intWeekNumber?>
          },
          function(data,status){
            $.facebox(data);
          })
          $(this).dialog("close");
        },
        "Delete": function() {
          $.post("page-includes/allocations/allocations-deleteedp.php", {
            ddate: ddate,
            schedulingPersonId: schedulingPersonId,
            teamId: teamId,
            action: 'delete'
          },
          function(data,status){
            ShowAllocations(teamId,<?php echo $intWeekNumber?>);
          }
          )
          $( this ).dialog( "close" );
          },
          "Cancel": function() {
            $( this ).dialog( "close" );
          }
        }
    }
  );
});
}

function customtipweekly(type='',currObj, Dutyid){
	$('#loading').hide();
	if(type == 'showtooltip'){
	 let message = $('#'+Dutyid).attr('data-qtip-content');
      if(message != ''){
        let tipHtml = '';
        tipHtml +='<table class="tablesmalltidy tooltip-table" width="250px" cellpadding="0">';
        tipHtml += '<tr class="tooltip-table-background">';
        tipHtml += '<td colspan="2" style="height:23px; vertical-align:top; padding:3px; border: 1px solid #505050; border-collapse: collapse;">'+message+'</td>';
        tipHtml += '</tr>';
        tipHtml += '</table>';

        let nativeTopPos = $(currObj).offset().top;
        let nativeLeftPos = $(currObj).offset().left;

        let topPos = nativeTopPos-135;
        let leftPos = nativeLeftPos+10;

        $('#customTip').css('left',leftPos+'px');
        $('#customTip').css('top',topPos+'px');
        $('#customTip').html(tipHtml);
        if(!$('#job-list-tooltip').is(":visible") && !$('.qtip-default').is(":visible")) {
            $('#customTip').show();
        }
      }

	} else {
        $('#customTip').hide();
    }
}

/*
  Function used for filter data
*/
function applyViewWeeklyFilter() {
    if((localStorage.getItem('weeklyscreen_filter') == '') && (localStorage.getItem('weeklyscreen_filter') == null)){
        return false;
    }
    $('#loading').show();
    var StaffNameFilter = $('#StaffNameFilter').val();
    var StaffName = $('#StaffName').val();
    var SortCodeFilter = $('#SortCodeFilter').val();
    var SortCode = $('#SortCode').val();
    var CostCodeFilter = $('#CostCodeFilter').val();
    var CostCode = $('#CostCode').val();
    var SkillFilter = $('#SkillFilter').val();
    var Skill = $('#Skill').val();
    var DutyFilter = $('#DutyFilter').val();
    var Duty = $('#Duty').val();
    var DutyLabelFilter = $('#DutyLabelFilter').val();
    var DutyLabel = $('#DutyLabel').val();
    var startDate = '<?php echo $dteStartDate; ?>';
    var endDate = '<?php echo $dteEndtDate; ?>';
    var teamId = $('#teamId').val();
    var AdditionalTeamsFilter = $('#AdditionalTeamsFilter').val();
    var AdditionalTeams = $('#AdditionalTeams').val();

    var groupCondition = $('#andFilter').prop('checked') ? false : true;

    let filteredData = [];

    //Get Skills to use in filter
    var scheduledPersonAllSkills = [];
    if(Skill) {
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/filters/filters-get-scheduled-person-skills.php",
            data: {
                startDate: startDate,
                endDate: endDate,
                teamId: teamId
            },
            async: false,
            success: function (response) {
                scheduledPersonAllSkills = (response['status'] == true) ? response['data'] : [];
            }
        });
    }

    //Start filtering
    $('.person-data-cell').each(function() {
        let id = $(this).attr('data-id');
        let matchFound = true;
        let groupOr = false;

        // Staff Name Filter
        if (StaffName) {
            let searchData = $(this).attr('data-order');
            let names = StaffName.split(',').map(s => s.trim());
            let containsMatch = names.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = names.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = names.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = names.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            var staffNameLoopMatch = true;
            if (StaffNameFilter === "*" && !containsMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === "!" && !notContainsMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === ";" && !(exactSomeMatch)) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === "__AND__" && !exactMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && staffNameLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        // Sort Code Filter
        if (SortCode) {
            let searchData = $(this).attr('data-sort-code') ?? '';
            let codes = SortCode.split(',').map(s => s.trim());
            let containsMatch = codes.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = codes.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = codes.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = codes.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            let sortCodeLoopMatch = true;
            if (SortCodeFilter === "*" && !containsMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === "!" && !notContainsMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === ";" && !(exactSomeMatch)) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === "__AND__" && !exactMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && sortCodeLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Cost code
        if (CostCode) {
            let searchData = $(this).attr('data-cost-code') ?? '';
            let codes = CostCode.split(',').map(s => s.trim());
            let containsMatch = codes.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = codes.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = codes.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = codes.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            let costCodeLoopMatch = true;
            if (CostCodeFilter === "*" && !containsMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === "!" && !notContainsMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === ";" && !(exactSomeMatch)) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === "__AND__" && !exactMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && costCodeLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        // Duty name Filter
        if (Duty) {
            let dutyNames = [];
            $('.scheduledPerson_duties_' + id + ' .scheduledPersonDuty').each(function () {
                dutyNames.push($(this).attr('data-duty-name'));
            });

            let duties = Duty.split(',').map(s => s.trim());
            let containsMatch = duties.some(name => new RegExp(name, "i").test(dutyNames));
            let notContainsMatch = duties.every(name => !new RegExp(name, "i").test(dutyNames));
            let exactMatch = duties.every(name =>
                dutyNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = duties.some(name =>
                dutyNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let startsWithSomeMatch = duties.some(name =>
                 dutyNames.some(dn => dn.startsWith(name))
            );

            //filter type
            let dutyLoopMatch = true;
            if (DutyFilter === "*" && !containsMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === "!" && !notContainsMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === ";" && !(exactSomeMatch)) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === "__AND__" && !exactMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if(DutyFilter === "%" && !startsWithSomeMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && dutyLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Skill filter
        if(Skill) {
            let scheduledPersonSkillsLabels = [];

            let scheduledPersonId = id;
            let filteredSkills = typeof scheduledPersonAllSkills[scheduledPersonId] !== 'undefined' ? scheduledPersonAllSkills[scheduledPersonId] : [];
            filteredSkills.forEach(function(item, index) {
                scheduledPersonSkillsLabels.push(item);
            });

            let skills = Skill;
            let containsMatch = skills.some(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let notContainsMatch = skills.every(name =>
                !scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactMatch = skills.every(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = skills.some(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );

            //filter type
            let skillLoopMatch = true;
            if (SkillFilter === "*" && !containsMatch) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === "!" && !notContainsMatch) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === ";" && !(exactSomeMatch)) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === "__AND__" && !exactMatch) {
                skillLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && skillLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Duty Label filter
        if (DutyLabel) {

             $('.scheduledPerson_duties_' + id + ' .scheduledPersonDuty').each(function () {
                let dutyLabels = [];
                let filteredLabels = $(this).attr('data-duty-labels').split(',').map(s => s.trim());
                filteredLabels.forEach(function(item, index) {
                    dutyLabels.push(item);
                });

                let labels = DutyLabel;
                let containsMatch = labels.some(name => new RegExp(name, "i").test(dutyLabels));
                let notContainsMatch = labels.every(name => !new RegExp(name, "i").test(dutyLabels));
                let exactMatch = labels.every(name =>
                    dutyLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
                );
                let exactSomeMatch = labels.some(name =>
                    dutyLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
                );

                //filter type
                let dutyLabelLoopMatch = true;
                if (DutyLabelFilter === "*" && !containsMatch) {
                    dutyLabelLoopMatch = false;
                    matchFound = false;
                } else if (DutyLabelFilter === "!" && !notContainsMatch) {
                    dutyLabelLoopMatch = false;
                    matchFound = false;
                } else if (DutyLabelFilter === ";" && !(exactSomeMatch)) {
                    dutyLabelLoopMatch = false;
                    matchFound = false;
                } else if (DutyLabelFilter === "__AND__" && !exactMatch) {
                    dutyLabelLoopMatch = false;
                    matchFound = false;
                }

                if((groupCondition && dutyLabelLoopMatch)) { // for or condition
                    groupOr = true;
                }

                if(matchFound) {
                    return false;
                }
            });
        }

        if (matchFound || groupOr) {
            filteredData.push(id);
        }
    });

    //Show only filtered data and hide remaining
    $('.filter-hide-row').removeClass('filter-hide-row');
    var totalVisible = 0;
    $('.person-data-cell').each(function() {
        if(filteredData.includes($(this).attr('data-id')) == false) {
            $(this).addClass('filter-hide-row');
            $('.scheduledPerson_duties_' + $(this).attr('data-id')).addClass('filter-hide-row');
        } else {
            let top = <?php echo $intRowHeight ?> * totalVisible;
            $(this).css("top", top);
            $('.scheduledPerson_duties_' + $(this).attr('data-id') + ' .scheduledPersonDuty').css("top", top);
            totalVisible++;
        }
    });
    $('#loading').hide();
}
</script>