<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/DBHelper.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/class-includes/userRolePermissions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/page-includes/admin/divisions/process/classDivisionalAdmin.php';

/*
* @Description : Class for handling schedulling team database queries.
* @access : Public
* @global : Not Applicable
*/

class classSchedulingTeam
{
    /*
    * @Description : Get details of schedulling team according to user permission.
    * @access : Public
    * @global : Not Applicable
    * @param  : $intUserId
    * @return : JSON Output
    */
    function getSchedulingTeamDetails($intuserid, $schedulingteamid, $datafor)
    {

        $isSysAdmin = isset($_SESSION['user']['SysAdmin']) && ($_SESSION['user']['SysAdmin'] != '') ? $_SESSION['user']['SysAdmin'] : $this->getIsSysAdmin($intuserid);

        $isDivadmin = 0;
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_get_IsDivisonalAdminByUser] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $intuserid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($result)) {
            $isDivadmin = 1;
        }

        $pageid = 8;
        // Call User Permission function.
        // $permissions = getUserRolePermissions($pageid);
        $show_identity_tab = 1;
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_SearchSchedulingTeamDetails] ?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $intuserid, PDO::PARAM_INT);
        $stmt->bindParam(2, $schedulingteamid, PDO::PARAM_INT);
        $stmt->execute();
        $resultschedteam = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($datafor == 'grid') {
            $teamIds = array_map(fn($r) => (int)$r['schedulingTeamId'], $resultschedteam);
            $teamGroupsMap = $this->getGroupNamesForTeams($teamIds);

            if (empty($resultschedteam)) {
                $data[] = [
                    '',
                    '',
                    'No Record Found',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            } else {
                foreach ($resultschedteam as $value) {

                    $action = '';
                    $schedulingTeamId = $value["schedulingTeamId"];
                    $groupsCsv = $teamGroupsMap[$schedulingTeamId] ?? '';

                    $controlhideedit = '';
                    if ($value['isDivsiosn'] == 0 && $isSysAdmin != 1) {
                        $permissions = getUserRolePermissions($pageid, $schedulingTeamId);

                        if (($permissions->cancreate == 0)  && ($isDivadmin == 0)) {
                            $show_identity_tab = 0;
                        }

                        if ($permissions->canview == 1 && $permissions->canmodify == 1) {
                            $controlhideedit = '';
                        } else  if ($permissions->canview == 0 && $permissions->canmodify == 1) {
                            $controlhideedit = '';
                        } else {
                            $controlhideedit = 'activeclass';
                        }
                    }

                    $action = '<a id ="schteamviewbutton" class="viewaction js_historyschteam" href="javascript:void(0)" title="View History" value="' . $schedulingTeamId . '"><i class="fa fa-hourglass-3 iconstyleallocate7"></i></a>
                    &nbsp;<a id="schteameditbutton" href="javascript:void(0)" class="editaction js_editschteam ' . $controlhideedit . '"  title="Edit" value="' . $schedulingTeamId . '" data-editing="' . $show_identity_tab . '" ><i class="fa fa-edit iconstyleallocate7"></i></a>';

                    $editingStartHour = (int)((int)$value['editingStart'] / 3600);
                    $editingStartMinute = (int)(((int)$value['editingStart'] % 3600) / 60);
                    $editingEndHour = (int)((int)$value['editingEnd'] / 3600);
                    $editingEndMinute = (int)(((int)$value['editingEnd'] % 3600) / 60);
                    $class_name = "";
                    if (!$value['isActive']) {
                        $class_name = "hideContent ";
                    }

                    if ((int)(($value['defaultDutyDuration'] % 3600) / 60) == 15) {
                        $defaultDutyDurationMinute = "25";
                    } else if ((int)(($value['defaultDutyDuration'] % 3600) / 60) == 30) {
                        $defaultDutyDurationMinute = "50";
                    } else if ((int)(($value['defaultDutyDuration'] % 3600) / 60) == 45) {
                        $defaultDutyDurationMinute = "75";
                    } else {
                        $defaultDutyDurationMinute = "00";
                    }
                    $data[] = [
                        '<span class="' . $class_name . '" title="' . $value["schedulingTeamName"] . '">' . substr(implode(PHP_EOL, str_split((string) $value['schedulingTeamName'], 25)), 0, 25) . '</span>',
                        '<span title="' . $value["schedulingTeamDescription"] . '">' . substr(implode(PHP_EOL, str_split((string) $value['schedulingTeamDescription'], 43)), 0, 43) . '</span>',
                        $value['DivisionName'],
                        $groupsCsv,
                        $value['EstablishCode'],
                        ($value['isActive'] == 1 ? 'Yes' : 'No'),
                        ($value['defaultSicknessHoursAllocation'] == 1 ? '7 Hours' : ($value['defaultSicknessHoursAllocation'] == 2 ? 'Standard Day length from teampay config' : ($value['defaultSicknessHoursAllocation'] == 3 ? 'Shift Length (without mealbreaks)' : 'N/A'))),
                        (int)($value['defaultDutyDuration'] / 3600) . "." . $defaultDutyDurationMinute,
                        (($value['workTimeDirectiveOptOut'] == 0 || $value['workTimeDirectiveOptOut'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['checkOverSixDaysWorked'] == 0 || $value['checkOverSixDaysWorked'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['checkOverFiveDaysWorked'] == 0 || $value['checkOverFiveDaysWorked'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['signIn'] == 0 || $value['signIn'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['signIn'] == 0 || $value['signIn'] == null) ? 'N/A' : $value['signInDays']),
                        (($value['allowInBuilding'] == 0 || $value['allowInBuilding'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['allowOvertimeRequests'] == 0 || $value['allowOvertimeRequests'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['colourWeek'] == 0 || $value['colourWeek'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['locks'] == 0 || $value['locks'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['locks'] == 0 || $value['locks'] == null) ? 'N/A' : $value['locksStart']),
                        (($value['locks'] == 0 || $value['locks'] == null) ? 'N/A' : $value['locksEnd']),
                        (($value['locks'] == 0 || $value['locks'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : (($value['locksWeekataTime'] == 0 || $value['locksWeekataTime'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>')),
                        (($value['maskType'] == -1 || $value['maskType'] == null) ? 'N/A' : $value['maskTypeName']),
                        (($value['maskType'] == -1 || $value['maskType'] == null) ? 'N/A' : $value['maskAfter']),
                        (($value['DailyViewMasking'] == 0 || $value['DailyViewMasking'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['DailyViewMasking'] == 0 || $value['DailyViewMasking'] == null) ? 'N/A' : $value['dailyViewMaskingDays']),
                        (($value['freelancerMasking'] == 0 || $value['freelancerMasking'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['freelancerMasking'] == 0 || $value['freelancerMasking'] == null) ? 'N/A' : $value['freelancerMaskingDays']),
                        (($value['restrictedEditing'] == 0 || $value['restrictedEditing'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['restrictedEditing'] == 0 || $value['restrictedEditing'] == null) ? 'N/A' : $value['numberofDaysAllowedEditing']),
                        (($value['restrictedEditing'] == 0 || $value['restrictedEditing'] == null) ? 'N/A' : ($editingStartMinute == 0 ? (strlen($editingStartHour) == 1 ? '0' . $editingStartHour : $editingStartHour) . ':00' : (strlen($editingStartMinute) == 1 ? (strlen($editingStartHour) == 1 ? '0' . $editingStartHour : $editingStartHour) . ':0' . $editingStartMinute : (strlen($editingStartHour) == 1 ? '0' . $editingStartHour : $editingStartHour) . ':' . $editingStartMinute))),
                        (($value['restrictedEditing'] == 0 || $value['restrictedEditing'] == null) ? 'N/A' : ($editingEndMinute == 0 ? (strlen($editingEndHour) == 1 ? '0' . $editingEndHour : $editingEndHour) . ':00' : (strlen($editingEndMinute) == 1 ? (strlen($editingEndHour) == 1 ? '0' . $editingEndHour : $editingEndHour) . ':0' . $editingEndMinute : (strlen($editingEndHour) == 1 ? '0' . $editingEndHour : $editingEndHour) . ':' . $editingEndMinute))),
                        (($value['restrictedEditing'] == 0 || $value['restrictedEditing'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : (($value['WeekendOnly'] == 0 || $value['WeekendOnly'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>')),
                        (($value['restrictedEditing'] == 0 || $value['restrictedEditing'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : (($value['autoLockTodayTimer'] == 0 || $value['autoLockTodayTimer'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>')),
                        (($value['hasGridChecks'] == 0 || $value['hasGridChecks'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['showProductionView'] == 0 || $value['showProductionView'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['IsCreateDutyFromRota'] == 0 || $value['IsCreateDutyFromRota'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['ShowJobsInWeeklyView'] == 0 || $value['ShowJobsInWeeklyView'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['autoImportWeeks'] == 0 || $value['autoImportWeeks'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        (($value['autoImportWeeks'] == 0 || $value['autoImportWeeks'] == null) ? 'N/A' : $value['NoofAutoAutoimportWeeks']),
                        (($value['hasXmasPoints'] == 0 || $value['hasXmasPoints'] == null) ? '<span class="imgtransparent">0</span><div id="crossRed"></div>' : '<span class="imgtransparent">1</span><div id="tick-mark"></div>'),
                        $value['defaultNumberweeksRotaPattern'],
                        ($value['defaultRotaStartDate'] == '01-01-1900' ? '' : $value['defaultRotaStartDate']),
                        $value['currentLeaveYear'],
                        (($value['leaveSelectiveHide'] == 0 || $value['leaveSelectiveHide'] == null) ? 'No' : 'Yes'),
                        (($value['hasHandovers'] == 0 || $value['hasHandovers'] == null) ? 'No' : 'Yes'),
                        ($value['staffAvailabilityReportStartDate'] == '01-01-1900' ? '' : $value['staffAvailabilityReportStartDate']),
                        $action,
                        $value['Email']
                    ];
                }
            }
            $resultarray = ['data' => $data];

            echo json_encode($resultarray);
        } else {
            // datafor != 'grid', currently used as 'popup'
            // Enrich the first row with mapped groups for preselect
            if ($datafor === 'popup' && isset($resultschedteam[0])) {
                $row = $resultschedteam[0];

                // Normalize divisionId for frontend
                if (!isset($row['divisionId']) && isset($row['divisionid'])) {
                    $row['divisionId'] = $row['divisionid'];
                }

                // Fetch mapped group IDs for this team
                $pdo2 = OpenDBLinkA7();

                $sqlMap = "SELECT SchedulingGroupsID FROM SchedulingGroupsTeamsLinks WHERE SchedulingTeamID = ? AND EndDate > CAST(SYSDATETIME() AS DATE);";

                $stmt2 = $pdo2->prepare($sqlMap);
                $teamId = (int)$row['schedulingTeamId']; // adjust if your alias is different
                $stmt2->execute([$teamId]);

                // Return as strings front-end expects string IDs for .val()
                $mapped = array_map('strval', array_column($stmt2->fetchAll(PDO::FETCH_ASSOC), 'SchedulingGroupsID'));

                // Attach to the outgoing row
                $row['mappedSchedulingGroups'] = $mapped;

                // Return a single-row array just like before
                return json_encode([$row]);
            }

            return json_encode($resultschedteam);
        }
    }

    public function updateStatus($id, $status)
    {
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "update SchedulingTeams set isActive = '" . $status . "' where schedulingTeamId = '" . $id . "' ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    public function deleteTeam($id)
    {
        $pdo = OpenDBLinkA7();
        $sql = "delete from SchedulingTeams where schedulingTeamId = '" . $id . "' ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    /*
    * @Description : Create or update details of schedulling team according.
    * @access : Public
    * @global : Not Applicable
    * @param  : $schedulingTeamName, $schedulingTeamDescription, $divisionId, $defaultChargeCode,
                $isActive, $defaultSicknessHoursAllocation, $defaultDutyDuration,
                $maskType, $maskAfter, $DailyViewMasking, $dailyViewMaskingDays, $freelancerMasking,
                $freelancerMaskingDays, $restrictedEditing, $numberofDaysAllowedEditing, $WeekendOnly,
                $editingStart, $autoLockTodayTimer, $editingEnd, $workTimeDirectiveOptOut,
                $checkOverSixDaysWorked, $checkOverFiveDaysWorked, $locks, $locksStart,
                $locksWeekataTime, $locksEnd, $signIn, $signInDays, $allowInBuilding, $hasGridChecks,
                $autoImportWeeks, $NoofAutoAutoimportWeeks, $showProductionView, $allowOvertimeRequests,
                $colourWeek, $defaultNumberweeksRotaPattern, $defaultRotaStartDate, $currentLeaveYear,
                $leaveSelectiveHide, $hasHandovers, $hasXmasPoints, $staffAvailabilityReportStartDate,
                $task, $tab, $intuserid, $intnewteamid, $showJobsInWeeklyView, $createDutyFromRota, 
                $isShowEditYearly, $isRestrictDeleteDuty, $restrictApplyROTAPattern
    * @return : Array Output
    */
    function createupdateschedulingteam($schedulingTeamName = '', $schedulingTeamDescription = '', $divisionId = 0, $defaultChargeCode = '', $isActive = 0, $defaultSicknessHoursAllocation = '', $defaultDutyDuration = '', $maskType = 0, $maskAfter = '', $DailyViewMasking = '', $dailyViewMaskingDays = '', $freelancerMasking = '', $freelancerMaskingDays = '', $restrictedEditing = '', $numberofDaysAllowedEditing = '', $WeekendOnly = '', $editingStart = '', $autoLockTodayTimer = '', $editingEnd = '', $workTimeDirectiveOptOut = '', $checkOverSixDaysWorked = '', $checkOverFiveDaysWorked = '', $locks = '', $locksStart = '', $locksWeekataTime = '', $locksEnd = '', $signIn = '', $signInDays = '', $allowInBuilding = '', $hasGridChecks = '', $autoImportWeeks = '', $NoofAutoAutoimportWeeks = '', $showProductionView = '', $allowOvertimeRequests = '', $colourWeek = '', $defaultNumberweeksRotaPattern = '', $defaultRotaStartDate = '', $currentLeaveYear = '', $leaveSelectiveHide = '', $hasHandovers = '', $hasXmasPoints = '', $staffAvailabilityReportStartDate = '', $task = '', $tab = '', $intuserid = '', $intnewteamid = '', $schEmail = '', $defaultChargeCodeDescription = '', $defaultActiveCode = '', $restrictCopyDuty = '', $showJobsInWeeklyView = '', $createDutyFromRota = '', $isShowEditYearly = '', $isRestrictDeleteDuty = '', $restrictApplyROTAPattern = '')
    {

        $schedulingTeamName = trim((string) $schedulingTeamName);
        // Open the database
        $pdo = OpenDBLinkA7();
        $oldDivisionId = null;
        if (!empty($intnewteamid)) {
            //Get old area Id
            $strQuery = "SELECT divisionId FROM  schedulingTeams WHERE schedulingTeamId = ? AND isActive = 1";
            $stmt = $pdo->prepare($strQuery);
            $stmt->bindParam(1, $intnewteamid, PDO::PARAM_INT);
            $stmt->execute();
            $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

            $oldDivisionId = null;
            if (!empty($oldData)) {
                $oldDivisionId = $oldData['divisionId'];
            }
        }

        // Set the statement to use
        $sql = "exec [dbo].[usp_CreateUpdateSchedulingTeam] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,
                                                            ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $schedulingTeamName, PDO::PARAM_STR);
        $stmt->bindParam(2, $schedulingTeamDescription, PDO::PARAM_STR);
        $stmt->bindParam(3, $divisionId, PDO::PARAM_INT);
        $stmt->bindParam(4, $isActive, PDO::PARAM_INT);
        $stmt->bindParam(5, $defaultSicknessHoursAllocation, PDO::PARAM_INT);
        $stmt->bindParam(6, $defaultDutyDuration, PDO::PARAM_INT);
        $stmt->bindParam(7, $workTimeDirectiveOptOut, PDO::PARAM_INT);
        $stmt->bindParam(8, $checkOverSixDaysWorked, PDO::PARAM_INT);
        $stmt->bindParam(9, $checkOverFiveDaysWorked, PDO::PARAM_INT);
        $stmt->bindParam(10, $signIn, PDO::PARAM_INT);
        $stmt->bindParam(11, $signInDays, PDO::PARAM_INT);
        $stmt->bindParam(12, $allowInBuilding, PDO::PARAM_INT);
        $stmt->bindParam(13, $allowOvertimeRequests, PDO::PARAM_INT);
        $stmt->bindParam(14, $colourWeek, PDO::PARAM_INT);
        $stmt->bindParam(15, $locks, PDO::PARAM_INT);
        $stmt->bindParam(16, $locksStart, PDO::PARAM_INT);
        $stmt->bindParam(17, $locksEnd, PDO::PARAM_INT);
        $stmt->bindParam(18, $locksWeekataTime, PDO::PARAM_INT);
        $stmt->bindParam(19, $maskType, PDO::PARAM_STR);
        $stmt->bindParam(20, $maskAfter, PDO::PARAM_INT);
        $stmt->bindParam(21, $DailyViewMasking, PDO::PARAM_INT);
        $stmt->bindParam(22, $dailyViewMaskingDays, PDO::PARAM_INT);
        $stmt->bindParam(23, $freelancerMasking, PDO::PARAM_INT);
        $stmt->bindParam(24, $freelancerMaskingDays, PDO::PARAM_INT);
        $stmt->bindParam(25, $restrictedEditing, PDO::PARAM_INT);
        $stmt->bindParam(26, $numberofDaysAllowedEditing, PDO::PARAM_INT);
        $stmt->bindParam(27, $editingStart, PDO::PARAM_INT);
        $stmt->bindParam(28, $editingEnd, PDO::PARAM_INT);
        $stmt->bindParam(29, $WeekendOnly, PDO::PARAM_INT);
        $stmt->bindParam(30, $autoLockTodayTimer, PDO::PARAM_INT);
        $stmt->bindParam(31, $hasGridChecks, PDO::PARAM_INT);
        $stmt->bindParam(32, $showProductionView, PDO::PARAM_INT);
        $stmt->bindParam(33, $autoImportWeeks, PDO::PARAM_INT);
        $stmt->bindParam(34, $NoofAutoAutoimportWeeks, PDO::PARAM_STR);
        $stmt->bindParam(35, $hasXmasPoints, PDO::PARAM_INT);
        $stmt->bindParam(36, $defaultNumberweeksRotaPattern, PDO::PARAM_INT);
        $stmt->bindParam(37, $defaultRotaStartDate, PDO::PARAM_STR);
        $stmt->bindParam(38, $currentLeaveYear, PDO::PARAM_INT);
        $stmt->bindParam(39, $leaveSelectiveHide, PDO::PARAM_INT);
        $stmt->bindParam(40, $hasHandovers, PDO::PARAM_INT);
        $stmt->bindParam(41, $staffAvailabilityReportStartDate, PDO::PARAM_STR);
        $stmt->bindParam(42, $task, PDO::PARAM_STR);
        $stmt->bindParam(43, $tab, PDO::PARAM_STR);
        $stmt->bindParam(44, $intuserid, PDO::PARAM_INT);
        $stmt->bindParam(45, $intnewteamid, PDO::PARAM_INT);
        $stmt->bindParam(46, $schEmail, PDO::PARAM_STR);
        $stmt->bindParam(47, $defaultActiveCode, PDO::PARAM_INT);
        $stmt->bindParam(48, $restrictCopyDuty, PDO::PARAM_INT);
        $stmt->bindParam(49, $showJobsInWeeklyView, PDO::PARAM_INT);
        $stmt->bindParam(50, $createDutyFromRota, PDO::PARAM_INT);
        $stmt->bindParam(51, $isShowEditYearly, PDO::PARAM_INT);
        $stmt->bindParam(52, $isRestrictDeleteDuty, PDO::PARAM_INT);
        $stmt->bindParam(53, $restrictApplyROTAPattern, PDO::PARAM_INT);
        $stmt->execute();
        $resultschedteam = $stmt->fetch(PDO::FETCH_ASSOC);

        //Remove and add Area viewer access for scheduling team view access        
        if ($oldDivisionId != $divisionId && $resultschedteam['intStatus'] == 1 && isset($resultschedteam['intnewidschteam']) && $resultschedteam['intnewidschteam'] != 0) {
            $areaObject = new ClassDivisionalAdmin();
            if ($oldDivisionId != null) {
                $oldTeamAreaViewUser = $areaObject->getUsersWithAreaViewerRole($oldDivisionId);
                foreach ($oldTeamAreaViewUser as $avuser) {
                    $areaObject->updateSchedulingTeamViewerRole($avuser['UR_UserID'], $divisionId, 0, $resultschedteam['intnewidschteam']);
                }
            }
            $newTeamAreaViewUser = $areaObject->getUsersWithAreaViewerRole($divisionId);
            foreach ($newTeamAreaViewUser as $avuser) {
                $areaObject->updateSchedulingTeamViewerRole($avuser['UR_UserID'], $divisionId, $avuser['UR_RoleID'], $resultschedteam['intnewidschteam']);
            }
        }

        // add the establish code when tab is identity only
        if ($tab == 'identity') {
            // Set the statement to use
            $sql = "exec [dbo].[usp_mod_EstablishCode] ?,?,?,?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $defaultChargeCode, PDO::PARAM_STR);
            $stmt->bindParam(2, $defaultChargeCodeDescription, PDO::PARAM_STR);
            $stmt->bindParam(3, $intuserid, PDO::PARAM_INT);
            $stmt->bindParam(4, $resultschedteam['intnewidschteam'], PDO::PARAM_INT);
            $stmt->execute();
            $resultestablishcode = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $resultschedteam;
    }

    /*
    * @Description : Get all divisions data for dropdown. According to assigned role for users.
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : JSON Output
    */
    function getalldivisionsbyuser($intuserid)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_getdivisionbyuser] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $intuserid, PDO::PARAM_INT);
        $stmt->execute();
        $resultdivision = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($resultdivision) > 0) {
            echo json_encode(['status' => 1, 'data' => $resultdivision]);
        } else {
            echo json_encode(['status' => 0, 'data' => 'No Record Found.']);
        }
    }

    /*
    * @Description : Get all divisions data for dropdown. According to assigned role for users.
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : JSON Output
    */
    function getalldivisionsbyuserAllUsers($intuserid)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_getdivisionbyuserForAllUsers] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $intuserid, PDO::PARAM_INT);
        $stmt->execute();
        $resultdivision = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($resultdivision) > 0) {
            echo json_encode(['status' => 1, 'data' => $resultdivision]);
        } else {
            echo json_encode(['status' => 0, 'data' => 'No Record Found.']);
        }
    }

    /*
    * @Description : Get mask types data for dropdown.
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : JSON Output
    */
    function getallmasktype()
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_getallmasktype]";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->execute();
        $resultdivision = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($resultdivision) > 0) {
            echo json_encode(['status' => 1, 'data' => $resultdivision]);
        } else {
            echo json_encode(['status' => 0, 'data' => 'No Record Found for mask type.']);
        }
    }

    /*
    * @Description : Get all team Extra Xmas Points
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : array Output
    */
    function getExtraXmasPointByTeamID($extraXmasPointID, $schedulingTeamid, $type)
    {

        $pageid = 13;
        // Call User Permission function.
        $modiactiveclass = $delactiveclass = '';
        //set the permissions
        $permissions = getUserRolePermissions($pageid, $schedulingTeamid);
        if ($permissions->candelete == 0) {
            $delactiveclass = 'activeclass';
        }
        if ($permissions->canmodify == 0) {
            $modiactiveclass = 'activeclass';
        }


        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_SchedulingTeamExtraXmasPoint] ?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $extraXmasPointID, PDO::PARAM_INT);
        $stmt->bindParam(2, $schedulingTeamid, PDO::PARAM_INT);
        $stmt->execute();
        $resultschedteam = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($type === 'list') {
            if (!empty($resultschedteam)) {
                foreach ($resultschedteam as $pointdata) {
                    $data[] = [
                        $pointdata['Fullname'],
                        $pointdata['year'],
                        $pointdata['points'],
                        $pointdata['notes'],
                        '<a id="js_editextrapoint" href="javascript:void(0)" class="editaction js_editextrapoint ' . $modiactiveclass . '" title="Edit" data-teamid="' . $pointdata['SchedulingTeamID'] . '" data-extrapointid="' . $pointdata['extraChristmasID'] . '"><i class="fa fa-edit iconstyleallocate7"></i></a> &nbsp;
                    <a id ="js_deleteExtraXmasPoint" class="viewaction js_deleteExtraXmasPoint ' . $delactiveclass . '" href="javascript:void(0)" title="Delete Point "  data-extrapointid="' . $pointdata['extraChristmasID'] . '"><i class="fa fa-trash iconstyleallocate7"></i></a>
                '
                    ];
                }
            } else {
                $data[] = ['', '', 'No Record Found', '', ''];
            }
            return json_encode($data);
        } else {

            return json_encode($resultschedteam[0]);
        }
    }

    /*
    * @Description : Insert user Extra Xmas Points
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : JSON Output
    */
    function modUserExtraXmasPointByTeam($params)
    {
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_mod_SchedulingTeamUserExtraXmasPoint] ?,?,?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $params['userid'], PDO::PARAM_INT);
        $stmt->bindParam(2, $params['notes'], PDO::PARAM_STR);
        $stmt->bindParam(3, $params['points'], PDO::PARAM_INT);
        $stmt->bindParam(4, $params['year'], PDO::PARAM_INT);
        $stmt->bindParam(5, $params['teamid'], PDO::PARAM_INT);
        $stmt->bindParam(6, $params['extraxmaspoint'], PDO::PARAM_INT);
        $stmt->bindParam(7, $params['js_xtrapointubmit'], PDO::PARAM_STR);
        $stmt->execute();
        $resultschedteam = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($resultschedteam[0]);
    }

    /*
    * @Description : Remove user Extra Xmas Points
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : JSON Output
    */
    function deleUserExtraXmasPointByTeam($extraxmaspoint)
    {
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_del_SchedulingTeamUserExtraXmasPoint] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $extraxmaspoint, PDO::PARAM_INT);
        $stmt->execute();
        $resultschedteam = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($resultschedteam);
    }

    public function updateEmailByStaffID($params)
    {

        $pdo = OpenDBLinkA7();

        $staffId = $params['staffId'];
        $nonBBCEmail = $params['nonBBCEmail'];
        $nonBBCPhone = $params['nonBBCPhone'];

        // Set the statement to use
        $sql = "exec [dbo].[usp_UpdateNONBBCEmailByStaffID] ?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $staffId, PDO::PARAM_INT);
        $stmt->bindParam(2, $nonBBCEmail, PDO::PARAM_STR);
        $stmt->bindParam(3, $nonBBCPhone, PDO::PARAM_STR);
        return (bool)$stmt->execute();
    }

    public function getEmailByStaffID($staffid)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_get_NonBbcEmailByStaffID] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $staffid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) {
            return $result;
        }
    }

    public function checkForLinkAccess()
    {
        // Open the database
        $intuserid = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
        $hasAccess = false;
        $pdo = OpenDBLinkA7();
        $query = "exec [dbo].[usp_get_IsSchedulingTeamAdminByUser] ? ";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(1, $intuserid, PDO::PARAM_INT);
        $stmt->execute();
        $row =  $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($row)) {
            $hasAccess = true;
        }
        return $hasAccess;
    }

    /*
    * @Description : Get Active mapped list
    * @access : Public
    * @global : Not Applicable
    * @param  : establishCode
    * @return : JSON Output
    */

    public function getMappedActiveCodeByTeam($establishCode)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_ActiveChargeCodeMappingList] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $establishCode, PDO::PARAM_STR);
        $stmt->execute();
        $resultMappedCode = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultMappedCode) > 0) {
            echo json_encode(['status' => 1, 'data' => $resultMappedCode]);
        } else {
            echo json_encode(['status' => 0, 'data' => 'No Record Found.']);
        }
    }

    /*
    * @Description : Get all team Extra Xmas Points by year
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : JSON Output
    */

    function SchedulingTeamExtraXmasPointByYear($year, $schedulingTeamid, $arrStaff)
    {
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_SchedulingTeamExtraXmasPointByYear] ?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $year, PDO::PARAM_INT);
        $stmt->bindParam(2, $schedulingTeamid, PDO::PARAM_INT);
        $stmt->execute();
        $rsadditional = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rsadditional as $row) {
            $scheduledPersonID = $row['ScheduledPersonID'];
            if (isset($arrStaff[$scheduledPersonID])) {
                $arrStaff[$scheduledPersonID]['additional'][$year]['points'] = $row['points'];
                $arrStaff[$scheduledPersonID]['additional'][$year]['notes'] = htmlspecialchars((string) $row['notes']);
            }
        }
        return ($arrStaff);
    }

    /*
    * @Description : Syst admin .
	* @access : Public
	* @global : Not Applicable
	* @param  : N/A
	* @return : JSON output
    */
    function getIsSysAdmin($intuserid)
    {

        try {
            // Open the database
            $pdo = OpenDBLinkA7();

            $sql = "SELECT UR_RoleID as RoleId FROM UserRoles Where UR_UserID = ? and UR_RoleID = 1 and GETDATE() Between UR_StartDate and UR_EndDate";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $intuserid, PDO::PARAM_INT);
            $stmt->execute();
            $isSysAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($isSysAdmin)) {
                return $isSysAdmin['RoleId'];
            } else {
                return 0;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
    * @Description : Retrieves all active (non-deleted) Scheduling Groups that belong to a specific Division/Area.
    * @access      : Public
    * @global      : Not Applicable
    * @param       : int $divisionId - ID of the division/area to filter groups.
    * @return      : array - Status flag and list of scheduling groups.
    */
    public function getSchedulingGroupsByArea($divisionId)
    {
        $divisionId = (int)$divisionId;
        $pdo = OpenDBLinkA7();

        $sql = "SELECT SchedulingGroupsID, SchedulingGroupsName FROM SchedulingGroups WHERE DeletedAt IS NULL AND DivisionID = ? ORDER BY SchedulingGroupsName ASC;";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $divisionId, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'status' => 1,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    /*
    * @Description : Retrieves all scheduling teams linked to one or more
    *                Scheduling Groups. Only teams with valid (non-expired)
    *                group-to-team link records are included. Results are
    *                grouped by Scheduling Group.
    * @access      : Public
    * @global      : Not Applicable
    * @param       : array $groupIds - List of Scheduling Group IDs.
    * @return      : array - Grouped list of groups with their associated teams.
    */
    public function getTeamsForSchedulingGroups($groupIds)
    {
        if (!is_array($groupIds)) {
            return [];
        }

        $groupIds = array_values(array_filter(array_map('intval', $groupIds)));

        if (!$groupIds) {
            return [];
        }

        $pdo = OpenDBLinkA7();
        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));

        $sql = "
        SELECT
            g.SchedulingGroupsID     AS groupId,
            g.SchedulingGroupsName   AS groupName,
            t.SchedulingTeamID       AS teamId,
            t.SchedulingTeamName     AS schedulingTeamName
        FROM SchedulingGroups g
        LEFT JOIN SchedulingGroupsTeamsLinks l
               ON l.SchedulingGroupsID = g.SchedulingGroupsID
              AND l.EndDate > CAST(SYSDATETIME() AS DATE)
        LEFT JOIN schedulingTeams t
               ON t.SchedulingTeamID = l.SchedulingTeamID
        WHERE g.SchedulingGroupsID IN ($placeholders)
        ORDER BY g.SchedulingGroupsName, t.SchedulingTeamName;
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($groupIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group results
        $out = [];
        foreach ($groupIds as $gid) {
            $out[$gid] = [
                'groupId'   => $gid,
                'groupName' => '',
                'teams'     => []
            ];
        }

        foreach ($rows as $r) {
            $gid = $r['groupId'];
            $out[$gid]['groupName'] = $r['groupName'];
            if (!empty($r['teamId'])) {
                $out[$gid]['teams'][] = [
                    'teamId'              => $r['teamId'],
                    'schedulingTeamName'  => $r['schedulingTeamName'] ?? ''
                ];
            }
        }

        return array_values($out);
    }

    /*
    * @Description : Retrieves a comma separated list of Scheduling Group names for each Scheduling Team ID provided. Only active (non-expired) group-to-team links are considered.
    * @access      : Private
    * @global      : Not Applicable
    * @param       : array $teamIds - List of Scheduling Team IDs.
    * @return      : array - Map of teamId => CSV string of assigned group names.
    */
    private function getGroupNamesForTeams(array $teamIds): array
    {
        if (empty($teamIds)) return [];

        $pdo = OpenDBLinkA7();
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

        $sql = "
        SELECT
            l.SchedulingTeamID AS teamId,
            STRING_AGG(g.SchedulingGroupsName, ', ')
                WITHIN GROUP (ORDER BY g.SchedulingGroupsName) AS groupsCsv
        FROM SchedulingGroupsTeamsLinks l
        JOIN SchedulingGroups g
             ON g.SchedulingGroupsID = l.SchedulingGroupsID
        WHERE l.EndDate > CAST(SYSDATETIME() AS DATE)
          AND l.SchedulingTeamID IN ($placeholders)
        GROUP BY l.SchedulingTeamID;
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($teamIds);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];

        foreach ($rows as $r) {
            $map[(int)$r['teamId']] = (string)$r['groupsCsv'];
        }

        return $map;
    }

    /*
    * @Description : Creates a history entry when a scheduling group's assignment is added or removed for a team. Retrieves the user's display
    *                name, builds the formatted history string, and inserts it into the standard History table used by SP.
    * @access      : Private
    * @global      : Not Applicable
    * @param       : int    $teamId    - The Scheduling Team ID affected.
    * @param       : int    $userId    - The ID of the user performing the action.
    * @param       : string $groupName - Name of the Scheduling Group updated.
    * @param       : string $action    - Action performed (e.g., 'added', 'removed').
    * @return      : void
    */
    private function addGroupHistory($teamId, $userId, $groupName, $action)
    {
        $pdo = OpenDBLinkA7();

        // Get user display name (same as SP)
        $stmt = $pdo->prepare("SELECT UD_DisplayName FROM UserDetails WHERE UD_UserID = ?");
        $stmt->execute([$userId]);
        $name = $stmt->fetchColumn();

        if (trim($name) === '') {
            $stmt = $pdo->prepare("SELECT UD_NetLogin FROM UserDetails WHERE UD_UserID = ?");
            $stmt->execute([$userId]);
            $name = $stmt->fetchColumn();
        }

        // Build exact matching history entry
        $timestampDate = date('d-m-Y');
        $timestampTime = date('H:i');

        $history =
            "Record updated by $name on $timestampDate at $timestampTime.<br>" .
            "Scheduling Groups $action: \"$groupName\".<br>";

        // Insert into same History table used by SP
        $sql = "
        INSERT INTO History (HistoryType, UserID, History, datetime, AttributeID)
        VALUES (3, ?, ?, GETDATE(), ?)
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $history, $teamId]);
    }

    /*
    * @Description : Retrieves the name of a Scheduling Group by its ID.Returns a fallback label ("Group <ID>") if no record exists.
    * @access      : Private
    * @global      : Not Applicable
    * @param       : PDO $pdo        - Database connection instance.
    * @param       : int $groupId    - The Scheduling Group ID.
    * @return      : string          - The group name or a fallback string.
    */
    private function getGroupNameById(PDO $pdo, int $groupId): string
    {
        $stmt = $pdo->prepare("SELECT SchedulingGroupsName FROM SchedulingGroups WHERE SchedulingGroupsID = ?");
        $stmt->execute([$groupId]);
        return (string)($stmt->fetchColumn() ?: "Group $groupId");
    }

    /*
    * @Description : Synchronizes the scheduling groups assigned to a team by comparing selected groups with existing active mappings.
    *                Adds new links, ends removed links, and records history entries for all changes made.
    * @access      : Public
    * @global      : Not Applicable
    * @param       : int   $teamId         - The Scheduling Team ID to update.
    * @param       : array $selectedGroups - List of selected Scheduling Group IDs.
    * @param       : int   $userId         - User performing the update.
    * @return      : bool                  - True on successful sync.
    */
    public function syncTeamGroups($teamId, array $selectedGroups, $userId)
    {
        $pdo = OpenDBLinkA7();

        // Normalize input
        $selectedGroups = array_values(array_unique(array_filter(array_map('intval', $selectedGroups))));

        // Fetch existing active mappings WITH names
        $sql = "
        SELECT l.SchedulingGroupsID, g.SchedulingGroupsName
        FROM SchedulingGroupsTeamsLinks l
        JOIN SchedulingGroups g ON g.SchedulingGroupsID = l.SchedulingGroupsID
        WHERE l.SchedulingTeamID = ?
          AND l.EndDate > CAST(SYSDATETIME() AS DATE);
    ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$teamId]);

        $existingRows  = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $existing      = array_column($existingRows, 'SchedulingGroupsID');
        $existingNames = array_column($existingRows, 'SchedulingGroupsName', 'SchedulingGroupsID');

        $toAdd = array_diff($selectedGroups, $existing);
        $toEnd = array_diff($existing, $selectedGroups);

        $addedNames = [];
        $removedNames = [];

        // Insert new mappings (EndDate = 9999-01-01)
        $insertSql = "
        INSERT INTO SchedulingGroupsTeamsLinks
            (SchedulingGroupsID, SchedulingTeamID, StartDate, EndDate, CreatedBy, CreatedDate)
        VALUES (?, ?, SYSDATETIME(), '9999-01-01', ?, SYSDATETIME());
    ";
        $insertStmt = $pdo->prepare($insertSql);

        foreach ($toAdd as $gid) {
            $insertStmt->execute([$gid, $teamId, $userId]);
            $addedNames[] = $this->getGroupNameById($pdo, (int)$gid);
        }

        // End removed mappings (EndDate = now)
        $endSql = "
        UPDATE SchedulingGroupsTeamsLinks
        SET EndDate = SYSDATETIME(),
            UpdatedBy = ?,
            UpdatedDate = SYSDATETIME()
        WHERE SchedulingGroupsID = ?
          AND SchedulingTeamID = ?
          AND EndDate > CAST(SYSDATETIME() AS DATE);
    ";
        $endStmt = $pdo->prepare($endSql);

        foreach ($toEnd as $gid) {
            $endStmt->execute([$userId, $gid, $teamId]);
            $removedNames[] = $existingNames[$gid] ?? $this->getGroupNameById($pdo, (int)$gid);
        }

        //History record for adds
        if (!empty($addedNames)) {
            $this->addGroupHistory($teamId, $userId, implode(', ', $addedNames), "added");
        }

        //History record for removals
        if (!empty($removedNames)) {
            $this->addGroupHistory($teamId, $userId, implode(', ', $removedNames), "removed");
        }

        return true;
    }
}
