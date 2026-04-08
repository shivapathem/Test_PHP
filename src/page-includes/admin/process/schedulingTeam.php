<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../view/schedulingTeamUI.php';
include_once 'classSchedulingTeam.php';
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/pageperms.php';
include_once '../../../class-includes/userRolePermissions.php';
include_once '../../staff-details/process/classScheduledPerson.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
if (($intSysAdmin == 1) || !empty($userDivisionsList) || ($arrUsersTeamdata["isSchedulingTeamAdmin"] == 1) || ($arrUsersTeamdata["isScheduler"] == 1)) {

$pageid = 8;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);

if (((isset($_POST['RequestFrom']) && $_POST['RequestFrom'] != null) ? $_POST['RequestFrom'] : '') == 'ChargeCode')
{
    $permissions->canview = 1;
    $permissions->cancreate = 1;
    $permissions->canmodify = 1;
}

if (($permissions->canview == 1) || ($permissions->cancreate == 1) || ($permissions->canmodify == 1)) {

    $task = $_POST['task']??'';
    $intuserid = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];

    $schedteamobj = new classSchedulingTeam();
    $userTeamRoles = $schedteamobj->checkForLinkAccess();
    $schedteamuiobj = new schedulingTeamUI($userTeamRoles);
    $scheduleobj = new classScheduledPerson;

    // Controller for Scheduling team UI.
    if ($task === 'schedulingTeamHtmlCall') {
        $schedteamuiobj->schedulingTeamHtmlCall();
        return;
    }

    // Controller for scheduling team details.
    if ($task === 'getSchedulingTeamDetails') {
        $schedulingteamid = 0;
        $schedteamobj->getSchedulingTeamDetails($intuserid,$schedulingteamid,'grid');
        return;
    }

    if ($task === 'deleteTeam') {
        $schedteamobj->deleteTeam($_POST['team_id']);
        return;
    }

    if ($permissions->cancreate == 1 || $permissions->canmodify == 1) {
        // Controller to create and update Scheduling team.
        if ($task === 'createupdateschedulingteam') {
            $taskcreateedit = $_POST['taskcreateedit'];
            $tabname = $_POST['tabname'];
        
            if ($tabname == 'identity') {
                $schedulingTeamName = $_POST['schedulingTeamName'];
                $schedulingTeamDescription = $_POST['schedulingTeamDescription'];
                $divisionId = (int)$_POST['divisionId'];
                $defaultChargeCode = $_POST['defaultChargeCode'];
                $isActive = (int)($_POST['isActive']);
                $defaultSicknessHoursAllocation = null;
                $defaultDutyDuration = null;
                $workTimeDirectiveOptOut = null;
                $checkOverSixDaysWorked = null;
                $checkOverFiveDaysWorked = null;
                $signIn = null;
                $signInDays = null;
                $allowInBuilding = null;
                $allowOvertimeRequests = null;
                $colourWeek = null;
                $locks = null;
                $locksStart = null;
                $locksEnd = null;
                $locksWeekataTime = null;
                $maskType = null;
                $maskAfter = null;
                $DailyViewMasking = null;
                $dailyViewMaskingDays = null;
                $freelancerMasking = null;
                $freelancerMaskingDays = null;
                $restrictedEditing = null;
                $numberofDaysAllowedEditing = null;
                $editingStart = null;
                $editingEnd = null;
                $WeekendOnly = null;
                $autoLockTodayTimer = null;
                $hasGridChecks = null;
                $showProductionView = null;
                $createDutyFromRota = null;
                $showJobsInWeeklyView = null;
                $autoImportWeeks = null;
                $NoofAutoAutoimportWeeks = null;
                $restrictCopyDuty = null;
                $defaultNumberweeksRotaPattern = null;
                $defaultRotaStartDate = null;
                $currentLeaveYear = null;
                $leaveSelectiveHide = null;
                $hasHandovers = null;
                $hasXmasPoints = null;
                $staffAvailabilityReportStartDate = null;
                $isShowEditYearly = null;
                $isRestrictDeleteDuty = null;
                $restrictApplyROTAPattern = null;
                $intnewteamid = $_POST['intnewschteamid'];
                $defaultChargeCodeDescription = $_POST['defaultChargeCodeDescription'];
                $defaultActiveCode = $_POST['defaultActiveCode'];
            } else if ($tabname == 'allocations') {
                $schedulingTeamName = null;
                $schedulingTeamDescription = null;
                $divisionId = null;
                $defaultChargeCode = null;
                $isActive = null;
                $defaultSicknessHoursAllocation = $_POST['defaultSicknessHoursAllocation'];
                $defaultDutyDuration = (int)$_POST['defaultDutyDuration'];
                $workTimeDirectiveOptOut = (int)$_POST['workTimeDirectiveOptOut'];
                $checkOverSixDaysWorked = (int)$_POST['checkOverSixDaysWorked'];
                $checkOverFiveDaysWorked = (int)$_POST['checkOverFiveDaysWorked'];
                $signIn = (int)$_POST['signIn'];
                $signInDays = (int)$_POST['signInDays'];
                $allowInBuilding = (int)$_POST['allowInBuilding'];
                $allowOvertimeRequests = (int)$_POST['allowOvertimeRequests'];
                $colourWeek = (int)$_POST['colourWeek'];
                $locks = (int)$_POST['locks'];
                $locksStart = (int)$_POST['locksStart'];
                $locksEnd = (int)$_POST['locksEnd'];
                $locksWeekataTime = (int)$_POST['locksWeekataTime'];
                $maskType = (int)$_POST['maskType'];
                $maskAfter = (int)$_POST['maskAfter'];
                $DailyViewMasking = (int)$_POST['DailyViewMasking'];
                $dailyViewMaskingDays = (int)$_POST['dailyViewMaskingDays'];
                $freelancerMasking = (int)$_POST['freelancerMasking'];
                $freelancerMaskingDays = (int)$_POST['freelancerMaskingDays'];
                $restrictedEditing = (int)$_POST['restrictedEditing'];
                $numberofDaysAllowedEditing = (int)$_POST['numberofDaysAllowedEditing'];
                $editingStartHour = $_POST['editingStartHour'];
                $editingStartMinute = $_POST['editingStartMinute'];
                $editingStart = ((int)$editingStartHour * 3600) + ((int)$editingStartMinute * 60);
                $editingEndHour = $_POST['editingEndHour'];
                $editingEndMinute = $_POST['editingEndMinute'];
                $editingEnd = ((int)$editingEndHour * 3600) + ((int)$editingEndMinute * 60);
                $WeekendOnly = (int)$_POST['WeekendOnly'];
                $autoLockTodayTimer = (int)$_POST['autoLockTodayTimer'];
                $hasGridChecks = (int)$_POST['hasGridChecks'];
                $showProductionView = (int)$_POST['showProductionView'];
                $createDutyFromRota = (int)$_POST['createDutyFromRota'];
                $showJobsInWeeklyView = (int)$_POST['showJobsInWeeklyView'];
                $isShowEditYearly = (int)$_POST['showEditYearly'];
                $isRestrictDeleteDuty = (int)$_POST['restrictDeleteDuty'];
                $restrictApplyROTAPattern = (int)$_POST['restrictApplyRota'];
                // $autoImportWeeks = (int)$_POST['autoImportWeeks'];
                // $NoofAutoAutoimportWeeks = $_POST['NoofAutoAutoimportWeeks'];
                $autoImportWeeks = null;
                $NoofAutoAutoimportWeeks = null;
                $restrictCopyDuty = (int)$_POST['restrictCopyDuty'];
                $defaultNumberweeksRotaPattern = null;
                $defaultRotaStartDate = null;
                $currentLeaveYear = null;
                $leaveSelectiveHide = null;
                $hasHandovers = null;
                $hasXmasPoints = null;
                $staffAvailabilityReportStartDate = null;
                $intnewteamid = $_POST['intnewschteamid'];
                $defaultChargeCodeDescription = null;
                $defaultActiveCode = null;
            } else {
                $schedulingTeamName = $_POST['schedulingTeamName'];
                $schedulingTeamDescription = $_POST['schedulingTeamDescription'];
                $divisionId = (int)$_POST['divisionId'];
                $defaultChargeCode = $_POST['defaultChargeCode'];
                $isActive = (int)($_POST['isActive']);
                $defaultSicknessHoursAllocation = $_POST['defaultSicknessHoursAllocation'];
                $defaultDutyDuration = (int)$_POST['defaultDutyDuration'];
                $workTimeDirectiveOptOut = (int)$_POST['workTimeDirectiveOptOut'];
                $checkOverSixDaysWorked = (int)$_POST['checkOverSixDaysWorked'];
                $checkOverFiveDaysWorked = (int)$_POST['checkOverFiveDaysWorked'];
                $signIn = (int)$_POST['signIn'];
                $signInDays = (int)$_POST['signInDays'];
                $allowInBuilding = (int)$_POST['allowInBuilding'];
                $allowOvertimeRequests = (int)$_POST['allowOvertimeRequests'];
                $colourWeek = (int)$_POST['colourWeek'];
                $locks = (int)$_POST['locks'];
                $locksStart = (int)$_POST['locksStart'];
                $locksEnd = (int)$_POST['locksEnd'];
                $locksWeekataTime = (int)$_POST['locksWeekataTime'];
                $maskType = (int)$_POST['maskType'];
                $maskAfter = (int)$_POST['maskAfter'];
                $DailyViewMasking = (int)$_POST['DailyViewMasking'];
                $dailyViewMaskingDays = (int)$_POST['dailyViewMaskingDays'];
                $freelancerMasking = (int)$_POST['freelancerMasking'];
                $freelancerMaskingDays = (int)$_POST['freelancerMaskingDays'];
                $restrictedEditing = (int)$_POST['restrictedEditing'];
                $numberofDaysAllowedEditing = (int)$_POST['numberofDaysAllowedEditing'];
                $editingStartHour = $_POST['editingStartHour'];
                $editingStartMinute = $_POST['editingStartMinute'];
                $editingStart = ((int)$editingStartHour * 3600) + ((int)$editingStartMinute * 60);
                $editingEndHour = $_POST['editingEndHour'];
                $editingEndMinute = $_POST['editingEndMinute'];
                $editingEnd = ((int)$editingEndHour * 3600) + ((int)$editingEndMinute * 60);
                $WeekendOnly = (int)$_POST['WeekendOnly'];
                $autoLockTodayTimer = (int)$_POST['autoLockTodayTimer'];
                $hasGridChecks = (int)$_POST['hasGridChecks'];
                $showProductionView = (int)$_POST['showProductionView'];
                $createDutyFromRota = (int)$_POST['createDutyFromRota'];
                $showJobsInWeeklyView = (int)$_POST['showJobsInWeeklyView'];
                $isShowEditYearly = (int)$_POST['showEditYearly'];
                $isRestrictDeleteDuty = (int)$_POST['restrictDeleteDuty'];
                $restrictApplyROTAPattern = (int)$_POST['restrictApplyRota'];
                // $autoImportWeeks = (int)$_POST['autoImportWeeks'];
                // $NoofAutoAutoimportWeeks = $_POST['NoofAutoAutoimportWeeks'];
                $autoImportWeeks = null;
                $NoofAutoAutoimportWeeks = null;
                $restrictCopyDuty = (int)$_POST['restrictCopyDuty'];
                $autoImportWeeks = (int)$_POST['autoImportWeeks'];
                $NoofAutoAutoimportWeeks = $_POST['NoofAutoAutoimportWeeks']??'';
                $defaultNumberweeksRotaPattern = (int)$_POST['defaultNumberweeksRotaPattern'];
                $defaultRotaStartDate = "30-12-1995";
                $currentLeaveYear = (int)date("Y");
                $leaveSelectiveHide = (int)$_POST['leaveSelectiveHide'];
                $hasHandovers = (int)$_POST['hasHandovers'];
                $hasXmasPoints = (int)$_POST['hasXmasPoints'];
                $staffAvailabilityReportStartDate = $_POST['staffAvailabilityReportStartDate'];
                $intnewteamid = (int)$_POST['intnewschteamid'];
                $schEmail = $_POST['schEmail'];
                $defaultChargeCodeDescription = $_POST['defaultChargeCodeDescription'];
                $defaultActiveCode =(int) $_POST['defaultActiveCode'];
            }
            
            $tab_array = array('identity','allocations','miscellaneous');
            $responsearray = array();
            foreach($tab_array as $tabname){
                $schteamresponse = $schedteamobj->createupdateschedulingteam($schedulingTeamName, $schedulingTeamDescription, $divisionId,
                    $defaultChargeCode, $isActive, $defaultSicknessHoursAllocation, $defaultDutyDuration,
                    $maskType, $maskAfter, $DailyViewMasking, $dailyViewMaskingDays, $freelancerMasking, $freelancerMaskingDays,
                    $restrictedEditing, $numberofDaysAllowedEditing, $WeekendOnly, $editingStart, $autoLockTodayTimer, $editingEnd,
                    $workTimeDirectiveOptOut, $checkOverSixDaysWorked, $checkOverFiveDaysWorked, $locks, $locksStart,
                    $locksWeekataTime, $locksEnd, $signIn, $signInDays, $allowInBuilding, $hasGridChecks, $autoImportWeeks,
                    $NoofAutoAutoimportWeeks, $showProductionView, $allowOvertimeRequests, $colourWeek, $defaultNumberweeksRotaPattern,
                    $defaultRotaStartDate, $currentLeaveYear, $leaveSelectiveHide, $hasHandovers, $hasXmasPoints,
                    $staffAvailabilityReportStartDate, $taskcreateedit, $tabname, $intuserid, $intnewteamid, $schEmail, $defaultChargeCodeDescription, $defaultActiveCode,$restrictCopyDuty,$showJobsInWeeklyView,$createDutyFromRota,
                    $isShowEditYearly, $isRestrictDeleteDuty, $restrictApplyROTAPattern
                );

                if($tabname == 'identity') {
                  $intnewteamid =   $schteamresponse["intnewidschteam"];
                }

                // Sync Scheduling Groups for this team
                $schedulingGroupsArray = isset($_POST['schedulingGroups']) ? array_map('intval', (array)$_POST['schedulingGroups']) : [];
                
                // Only attempt sync if team exists
                if ($intnewteamid > 0) {
                    $schedteamobj->syncTeamGroups($intnewteamid, $schedulingGroupsArray, $intuserid);
                }
                
                $responsearray = array('status' => 'success',
                'sqlstatus' => $schteamresponse["intStatus"],
                'sqlstatusstring' => $schteamresponse["strstatusschteam"],
                'intnewidschteam' => $schteamresponse["intnewidschteam"]);

                if(!$schteamresponse["intStatus"])
				{
					break;
				}
            }
            
            echo json_encode($responsearray);
        }
        // Controller for division data.
        if ($task == 'getalldivisionsbyuser') {
            return $schedteamobj->getalldivisionsbyuser($intuserid);
        }

        // Controller for division data by user permission all users.
        if ($task == 'getalldivisionsbyuser_AllUsers') {
            return $schedteamobj->getalldivisionsbyuserAllUsers($intuserid);
        }

        // Controller to all mask types.
        if ($task == 'getallmasktype') {
            return $schedteamobj->getallmasktype();
        }

        // Controller to fetch Scheduling Groups filtered by Area
        if ($task === 'getSchedulingGroupsByArea') {
            echo json_encode($schedteamobj->getSchedulingGroupsByArea($_POST['divisionId']));
            return;
        }

         // Controller to fetch teams mapped to selected Scheduling Groups
         if ($task === 'getTeamsForSchedulingGroups') {
            echo json_encode($schedteamobj->getTeamsForSchedulingGroups($_POST['groupIds']));
            return;
        }

        // Controller for scheduling team details for modify popup.
        if ($task === 'getSchedulingTeamDetailsForPopup') {
            $schedulingTeamId = $_POST['schedulingTeamId'];
            $schteamdatapopup = json_decode($schedteamobj->getSchedulingTeamDetails($intuserid,$schedulingTeamId,'popup'), true);
            if(isset($schteamdatapopup)) {
                echo json_encode(array("status" => 1, "data" => $schteamdatapopup[0]));
            } else {
                echo json_encode(array("status" => 0, "data" => "No Record Found"));
            }
        }

        // Controller for division data.
        if ($task == 'getmappedactivecodebyteam') {
           
            return $schedteamobj->getMappedActiveCodeByTeam($_REQUEST['chargeCode']);
        }
    }
    
} else {
    include_once '../../../page-includes/no_access.php';
    die;
}
    } else {
        echo 'Access Denied'; die;
    }
