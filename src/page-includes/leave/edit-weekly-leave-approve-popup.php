<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Europe/London');

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/AllocationsFunctionsEditing.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/common/classCommonDBFunctions.php';
include_once '../users/process/classUserSetup.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

$commonObj = new classCommonDBFunctions();
$allocServiceObj = new AllocationService();
$strUser = GetUserLogon();
$pdo = OpenDBLinkA7();
$currentuserid = ($_COOKIE['editWeeklyUserId']) ?? $_SESSION['user']['UserID'];
$UserFullName  = ($_COOKIE['editWeeklyUserFullName']) ?? $_SESSION['user']['FullName'];
$isDivisionalAdmin = $commonObj->UserIsDivAdmin($currentuserid);
//$isSysAdmin = isset($_SESSION['user']['SysAdmin']) ||($_SESSION['user']['SysAdmin']=='')? 0 : 1;
$isSysAdmin = $commonObj->UserIsSysAdmin($currentuserid) ?? 0;
$setupObj = new classUserSetup();
$loggedUsedInfo = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
$isSchedulingTeamAdmin = 0;
if (isset($loggedUsedInfo['isSchedulingTeamAdmin'])) {
    $isSchedulingTeamAdmin = $loggedUsedInfo['isSchedulingTeamAdmin'];
}
$arrAllocLeaveTypes = GetLeaveAllocateTypes();

$arrIsLeaveCounted = GetIsLeaveCounted();
$ExceptionalId = GetIdExceptionalAllocateType();
$loggedUserNetLoginID = ($_COOKIE['editWeeklyUserNetLogin']) ?? $_SESSION['user']['user'];
$LeaveAdminGroupsData = GetLeaveAdminGroups($loggedUserNetLoginID);

$arrLeaveAdmingrps = [];
if (!empty($LeaveAdminGroupsData)) {
    foreach ($LeaveAdminGroupsData as $value) {
        array_push($arrLeaveAdmingrps, $value['LeaveGroupID']);
    }
}

$arrAmountsEntered = array();
$arrAmountsToEnter = array();
$arrLeaveCountedflag = [];
if (!empty($arrIsLeaveCounted)) {
    foreach ($arrIsLeaveCounted as $value) {
        array_push($arrLeaveCountedflag, $value['id']);
    }
}
$callingPagename = $_REQUEST['callpage'] ?? 'weekly';
if (isset($_POST['submit'])) {
    $arrApplicationId = [];
    $arrAmounts = $_POST['Amounts'];
    $arrTotalApplicationIds = [];

    $pdlLeaveDataArr = [];
    $pdlLeavesIds = [];
    $pdlLeaveAmts = [];
    if (!empty($_POST['PDLTime'])) {
        $inc = 0;
        foreach ($_POST['PDLTime'] as $k => $v) {
            if (($v[0] != '') && ($v[1] != '') && ($v[0] != 0 || $v[1] != 0)) {
                $pdlLeavesIds[] = (string) $k;
                $pETime = $v[1];
                if ($v[0] > $v[1]) {
                    $pETime = (86400 + $pETime);
                }
                $pdlLeaveAmts[$k]['pdlLeaveDur'] = ($pETime - $v[0]);
                $pdlLeaveDataArr[$inc]['LeaveID'] = (string) $k;
                $pdlLeaveDataArr[$inc]['LeaveStartTime'] = $v[0];
                $pdlLeaveDataArr[$inc]['LeaveEndTime'] = $v[1];
                $inc++;
            }
        }
    }

    $a = array();
    $b = array();
    $i = 0;
    $arrExceptionalTypes = isset($_POST['ExceptionalTypes']) ? $_POST['ExceptionalTypes'] : '';
    $popUpLeavesIds = [];
    $popUpLeaveAmts = [];
    foreach ($arrAmounts as $intRequestID => $intAmount) {
        $arrIndRequest = explode('-', $intRequestID);
        array_push($arrTotalApplicationIds, $arrIndRequest[0]);
        if (is_numeric($intAmount)) {
            $arrAmountsEntered[$arrIndRequest[0]][$arrIndRequest[1]] = $intAmount;
            $arrAmountsToEnter[$arrIndRequest[0]] = isset($arrAmountsToEnter[$arrIndRequest[0]]) ? ($arrAmountsToEnter[$arrIndRequest[0]] + $intAmount) : $intAmount;
            $popUpLeavesIds[] = (string) $arrIndRequest[0];
            $popUpLeaveAmts[$arrIndRequest[0]][] = (float) $intAmount;
            $a['AppId'] = $arrIndRequest[0];
            $a['TypeId'] = $arrIndRequest[1];
            $a['Amount'] = $intAmount;
            $b[$i] = $a;
        }
        $i++;
    }

    if (!empty($pdlLeavesIds) && !empty($popUpLeavesIds)) {
        $pdlLeaveIdErr = array_diff($pdlLeavesIds, $popUpLeavesIds);
        if (!empty($pdlLeaveIdErr)) {
            foreach ($pdlLeaveIdErr as $hndPdl) {
                if (!empty($pdlLeaveDataArr)) {
                    foreach ($pdlLeaveDataArr as $key => $val) {
                        $hndPdl = (string) $hndPdl;
                        if ($val['LeaveID'] == $hndPdl) {
                            unset($pdlLeaveDataArr[$key]);
                        }
                    }
                }
            }
        }

        $pdlLeaveIdComp = array_intersect($pdlLeavesIds, $popUpLeavesIds);
        if (!empty($pdlLeaveIdComp)) {
            $isError = [];
            foreach ($pdlLeaveIdComp as $ke => $vl) {
                $pdlDurationHrsForLeave = (float) $allocServiceObj->convertSecondsIntoTime($pdlLeaveAmts[$vl]['pdlLeaveDur'], '.', 'No');
                $indLeaveAmt = (float) array_sum($popUpLeaveAmts[$vl]);
                if (($pdlDurationHrsForLeave > $indLeaveAmt) || ($pdlDurationHrsForLeave < $indLeaveAmt)) {
                    $isError[] = 'YES';
                }
            }

            if (!empty($isError) && in_array('YES', $isError)) {
                $x['status'] = false;
                $x['errorType'] = 'HC'; //Hours Compare PDL
                $x['compLeaveIds'] = $pdlLeaveIdComp;
                $x['popUpLeaveAmts'] = $popUpLeaveAmts;
                echo json_encode($x);
                exit;
            }
        }
    }
    $pdlLeaveDataArr = array_values($pdlLeaveDataArr);

    $arrLeaveAmounts = array();
    $arrLeaveAmounts = array_values($b);
    $arrblankAmounts = [];
    $arrUnaprove = [];
    $arrTotalApplicationIds = array_unique($arrTotalApplicationIds);
    $arrblankAmounts = array_keys($arrAmountsToEnter);
    $arrUnaprove = array_diff($arrTotalApplicationIds, $arrblankAmounts);
    $arrApprove = array_diff($arrTotalApplicationIds, $arrUnaprove);
    $jsonAmounts = json_encode($arrLeaveAmounts);
    $ApproveApplicationIds = implode(",", $arrApprove);

    $jsonPDLLeaves = (!empty($pdlLeaveDataArr)) ? json_encode($pdlLeaveDataArr) : '[]';

    if (!empty($arrUnaprove)) {
        foreach ($arrUnaprove as $LeaveApplicationID) {
            $username = ($_COOKIE['editWeeklyUserFullName']) ?? ($_SESSION['user']['FullName']);
            $history = 'Leave unapproved by ' . $username . ' On ' . date("d/m/Y") . ' ' . date("H:i");
            $LeaveStartTime = 'No';
            $LeaveEndTime = 'No';
            $unapproveLeave = UnapproveLeave($LeaveApplicationID, $history, $currentuserid, $username, $LeaveStartTime, $LeaveEndTime);
        }
    }

    $result = InsertLeaveAmount($ApproveApplicationIds, $jsonAmounts, $jsonPDLLeaves);
    if (!empty($arrExceptionalTypes)) {
        $strQuery4Cond = '';
        foreach ($arrAllocLeaveTypes as $arrAllocLeaveTypesVal) {
            if (strtolower($arrAllocLeaveTypesVal['AllocName']) == 'exceptional') {
                $strQuery4Cond = ' AND LeaveTypeID = ' . $arrAllocLeaveTypesVal['ID'];
            }
        }
        foreach ($arrExceptionalTypes as $intApplicationID => $intReason) {
            $arrIdApplication = explode('-', $intApplicationID);
            $applicationId = $arrIdApplication[0];
            $oldReasonId = $arrIdApplication[2];
            $strQuery4 = "UPDATE  ref_LeaveApplications_Amounts
                 SET        ReasonID=$intReason
                 WHERE   (ApplicationID = $applicationId) $strQuery4Cond";

            $stmt = $pdo->prepare($strQuery4);
            $stmt->execute();

            if (($strQuery4Cond != '') && ($oldReasonId != $intReason)) {
                $strHistory = '';

                $prevID = (int) $oldReasonId;
                $currID = (int) $intReason;

                $reasonIDs = array_unique([$currID, $prevID]);
                $placeholders = implode(',', array_fill(0, count($reasonIDs), '?'));

                $sqlReasonName = "SELECT ID, Name FROM LeaveExceptionalTypes WHERE ID IN ($placeholders)";
                $stmt2 = $pdo->prepare($sqlReasonName);
                foreach ($reasonIDs as $i => $id) {
                    $stmt2->bindValue($i + 1, $id, PDO::PARAM_INT);
                }
                $stmt2->execute();

                $reasonData = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                $reasons = [];
                foreach ($reasonData as $row) {
                    $reasons[$row['ID']] = $row['Name'];
                }

                if ($prevID === 0) {
                    $strHistory .= 'Leave reason changed to ' . ($reasons[$currID] ?? 'Unknown');
                } else {
                    $from = $reasons[$prevID] ?? 'Unknown';
                    $to = $reasons[$currID] ?? 'Unknown';
                    $strHistory .= "Leave reason changed from $from to $to";
                }

                $strHistory .= " by " . $UserFullName . " on "
                            . getDateTimeInEuropeTimezone(1, 0) . " "
                            . getDateTimeInEuropeTimezone(0, 1) . "<hr>";

                $sqlInstHistory = "INSERT INTO History (HistoryType, UserID, History, AttributeID)
                                     SELECT ht.id, ?, ?, ?
                                       FROM HistoryTypes ht
                                      WHERE ht.HistoryType = 'LeaveApplication'";
                $stmt3 = $pdo->prepare($sqlInstHistory);
                $stmt3->execute([$currentuserid, $strHistory, $applicationId]);

                $sqlAllocation = "SELECT A.ID FROM LeaveApplications LA
                                  LEFT JOIN Allocations A ON A.SchedulingPersonID = LA.SchedulingPersonID
                                  AND CAST(A.DutyDate AS DATE) = CAST(LA.dDate AS DATE)
                                  WHERE LA.ID = ?";
                $stmtAlloc = $pdo->prepare($sqlAllocation);
                $stmtAlloc->bindValue(1, $applicationId, PDO::PARAM_INT);
                $stmtAlloc->execute();
                $getAllocId = $stmtAlloc->fetch(PDO::FETCH_ASSOC);

                if (isset($getAllocId['ID'])) {
                    $allocId = $getAllocId['ID'];
                    $historySubType = 'PH';
                    $sqlInstHistoryEW = "
                        INSERT INTO History (HistoryType, UserID, History, AttributeID, HistorySubType)
                        SELECT ht.id, ?, ?, ?, ?
                        FROM HistoryTypes ht
                        WHERE ht.HistoryType = 'AllocationDuty'
                    ";
                    $stmtHistory = $pdo->prepare($sqlInstHistoryEW);
                    $stmtHistory->execute([$currentuserid, $strHistory, $allocId, $historySubType]);
                }
            }
        }
    }

} else {
    $intID = $_REQUEST['id'];

    $leaveApplicationDetail = getLeaveApplicationDetails($intID);
    $strUserName = $leaveApplicationDetail['FullName'];
    $singleday = date('Y-m-d', strtotime('-7 day', strtotime($leaveApplicationDetail['dDate'])));

    $strLoopDate = '';
    $rsRequests = GetAllLeaveTypesDetailsForApprove($leaveApplicationDetail['Login'], $singleday);
    $c = 0;
    $popuprefreshid = 0;
    foreach ($rsRequests as $row) {
        if($c == 0){
            $popuprefreshid = $row['ID'];
            $c++;
        }
        $applicationAmountsRes = array();
        //fetch application amounts
        $applicationAmountsRes = GetApplicationAmounts($row['ID']);
        $strLogin = $row['Login'];
        $userID = $row['UserID'];
        $staffNumber = $row['StaffNumber'];
        $strScheduledPeopleId = $row['ScheduledPersonID'];
        if ($row['ID'] == $intID) {
            $intLeaveYear = $intLeaveYear = date("Y", strtotime($row['dDate'] . "-3 months"));
            $intGroupID = $row['GroupID'];
            $strPassedDate = date("Y-m-d", strtotime($row['dDate']));
        }
        if (!isset($strStartDate)) {
            $strStartDate = $arrRequests[$row['ID']]['Date'] = date("Y-m-d", strtotime($row['dDate']));
        }
        $strEndDate = $arrRequests[$row['ID']]['Date'] = date("Y-m-d", strtotime($row['dDate']));
        $arrRequests[$row['ID']]['Date'] = date("D, jS M Y", strtotime($row['dDate']));
        $arrRequests[$row['ID']]['ShortDate'] = date("Y-m-d", strtotime($row['dDate']));
        $arrRequests[$row['ID']]['Created'] = date("l, jS M Y", strtotime($row['Created']));
        $arrRequests[$row['ID']]['LeaveTypesID'] = $row['LeaveTypesID'];
        $arrRequests[$row['ID']]['TypeDesc'] = $row['TypeDesc'];
        $arrRequests[$row['ID']]['GroupID'] = $row['GroupID'];
        $arrRequests[$row['ID']]['GroupDesc'] = $row['GroupDesc'];
        $arrRequests[$row['ID']]['ShortNotice'] = $row['ShortNotice'];
        $arrRequests[$row['ID']]['isOK'] = $row['isOK'];
        $arrRequests[$row['ID']]['Approved'] = $row['Approved'];
        $arrRequests[$row['ID']]['Unlikely'] = $row['unlikely'];
        $arrRequests[$row['ID']]['oversummer'] = $row['oversummer'];
        $arrRequests[$row['ID']]['CountLeave'] = $row['CountLeave'];
        $arrRequests[$row['ID']]['IsAgreed'] = $row['IsAgreed'];
        if (!empty($applicationAmountsRes)) {
            $arrRequests[$row['ID']]['ReasonID'] = 0;
            foreach ($applicationAmountsRes as $appAmt) {
                if (($appAmt['ReasonID'] != '')) {
                    $arrRequests[$row['ID']]['ReasonID'] = $appAmt['ReasonID'];
                }
                $arrRequests[$row['ID']]['Amounts'][$appAmt['LeaveTypeID']] = round($appAmt['Amount'], 2);
            }
        }
        $arrDates[] = date("Y-m-d", strtotime($row['dDate']));
    }
    $arrRanges = getDateRangeSplitted($arrDates);

    // Put the ranges into start and end arrays
    foreach ($arrRanges as $arrRange) {
        $groupName = $arrRange[0] . '|' . $arrRange[1];
        $arrStarts[$arrRange[0]] = $groupName;
        $arrEnds[$arrRange[1]] = $groupName;
    }
    $strStaffNumber = $staffNumber;

    $tdate = $strStartDate;
    $arrStartweeks = GetAllocationWeekandDay($strStartDate);
    $arrEndweeks = GetAllocationWeekandDay($strEndDate);

    $strStaffNumber = $staffNumber;

    $arrAllocations = ReadAllocationsAndRotaLeave($arrStartweeks['ixYearWeek'], $arrEndweeks['ixYearWeek'], $strScheduledPeopleId);
    // Get the credits and total them
    $arrLeaveCredits = GetLeaveCredits($strStaffNumber, $strScheduledPeopleId, $intLeaveYear);
    if (isset($arrLeaveCredits['Leave'])) {
        foreach ($arrLeaveCredits['Leave'] as $intLeaveID => $arrLeaveCredit) {
            foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
                if ($arrAllocLeaveType['HasCredits'] == 1) {
                    if (isset($arrLeaveCredit[$arrAllocLeaveType['AllocName']])) {
                        if (isset($arrTotalCredits[$LTID])) {
                            $arrTotalCredits[$LTID] = $arrTotalCredits[$LTID] + $arrLeaveCredit[$arrAllocLeaveType['AllocName']];
                        } else {
                            $arrTotalCredits[$LTID] = $arrLeaveCredit[$arrAllocLeaveType['AllocName']];
                        }
                    }
                }
            }
        }
    }
    $arrLeaveTaken = GetLeaveTakenByUser($strScheduledPeopleId, $intLeaveYear);
    $homeTeamDuty = GetUserHometeamDutyDurtaion($strScheduledPeopleId);
    $defaultDutyDuration = '';
    if (!empty($homeTeamDuty)) {
        $defaultDutyDurationSeconds = $homeTeamDuty['defaultDutyDuration'];
        $DefaultDutyDurationMinute = (int) (($defaultDutyDurationSeconds % 3600) / 60);
        if ($DefaultDutyDurationMinute == 15) {
            $DefaultDutyDurationMinute = "25";
        } else if ($DefaultDutyDurationMinute == 30) {
            $DefaultDutyDurationMinute = "50";
        } else if ($DefaultDutyDurationMinute == 45) {
            $DefaultDutyDurationMinute = "75";
        }
        $defaultDutyDuration = (int) ($defaultDutyDurationSeconds / 3600) . "." . $DefaultDutyDurationMinute;
    }
    echo '<link rel="stylesheet" type="text/css" href="styles/leave-approve-popup.css">';
    echo '<div class="leaveManageContainer">';
    echo '<div class="tableheadersmall fullwidth medtextboldcentre"><br>Manage Leave for ' . $strUserName . '<br><br></div>';
    echo '<form id="approveleave" method="post">';
    echo '<input type="hidden" name="callerPage" id="callerPage" value="' . $callingPagename . '">';
    echo '<table class="tablesmallborder wordsBreak" id="LeaveApproveTable" width="100%">';
    echo '<tr>';
    echo '<th class="leaveTopTHWidth"></th>';
    foreach ($arrAllocLeaveTypes as $intTypeID => $arrAllocLeaveType) {
        echo '<th class="leaveTHWidth">';
        echo $arrAllocLeaveType['Description'];
        echo '</th>';
    }
    echo '<th class="leaveTReason"></th>';
    echo '</tr>';

    echo '<tr height="30px">';
    echo '<th >Leave Credited</th>';
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
        echo '<td>';
        if (isset($arrTotalCredits[$LTID])) {
            echo $arrTotalCredits[$LTID] > 0 ? number_format(($arrTotalCredits[$LTID]), 2, '.', '') : '';

        }
        echo '</td>';
    }
    echo '<td></td>';
    echo '</tr>';
    echo '<tr height="30px">';
    echo '<th >Leave Taken</th>';
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
        echo '<td>';

        if (isset($arrLeaveTaken[$arrAllocLeaveType['AllocName']])) {
            echo number_format(($arrLeaveTaken[$arrAllocLeaveType['AllocName']]), 2, '.', '');
        }
        echo '</td>';
    }
    echo '<td></td>';
    echo '</tr>';
    echo '<tr height="30px">';
    echo '<th  >';
    echo 'Balance';
    echo '</th>';

    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
        echo '<td  class="VeryVeryLightGrey"><b>';
        if (isset($arrAllocLeaveType['HasCredits']) && ($arrAllocLeaveType['HasCredits'] == 1)) {

            if (isset($arrLeaveTaken[$arrAllocLeaveType['AllocName']])) {
                $leavecreditval = isset($arrTotalCredits[$LTID]) ? number_format(($arrTotalCredits[$LTID]), 2, '.', '') : 0;
                echo $leavecreditval - $arrLeaveTaken[$arrAllocLeaveType['AllocName']];

            } else {
                if (isset($arrTotalCredits[$LTID]) && ($arrTotalCredits[$LTID] > 0)) {
                    echo number_format(($arrTotalCredits[$LTID]), 2, '.', '');
                }
            }
        }
        echo '</b></td>';
    }
    echo '<td  class="VeryVeryLightGrey"></td>';
    echo '</tr>';

    echo '<tr height="30px">
      <th colspan="12">Default Duty Duration = ';
    echo $defaultDutyDuration;
    echo '</th>
  </tr>';
    echo '</thead>';
    echo '</table>';

    // inserting my new table here
    echo '<table class="tablesmallborder wordsBreak" id="LeaveApproveTables" width="100%">';
    echo '<thead>';

    echo '<tr>';
    echo '<th width="1%"></th>';
    echo '<th class="date-Width">Date</th>';
    echo '<th class="group-type-Width">Group/Type</th>';
    echo '<th class="duty-type-Width">Duties</th>';
    foreach ($arrAllocLeaveTypes as $intTypeID => $arrAllocLeaveType) {
        echo '<th class="leaveTHWidth">';
        echo $arrAllocLeaveType['Description'];
        echo '</th>';
    }
    echo '<th class="leaveTReason">Reason</th>';
    echo '</tr>';

    echo '</thead>';
    echo '<tbody>';

    $intRowCount = 1;
    $groupSetter = '';
    foreach ($arrRequests as $indLeaveID => $arrRequest) {

        $arrExceptionalTypes = GetLeavExceptionalTypes($arrRequest['ShortDate']);
        if ($arrRequest['Approved'] == 1) {

            if ($arrRequest['CountLeave'] == 1) {
                $strClass = 'LeaveApproved';
            }
            if ($arrRequest['CountLeave'] == 0) {
                $strClass = 'LeaveHashedOrange';
            }

        } else {
            if ($arrRequest['IsAgreed'] == 1) {
                $strClass =" LeaveAgreed";
            } else if ($arrRequest['ShortNotice'] == 1) {
                $strClass = 'LeaveShortNoticeApplied';
            } else if ($arrRequest['oversummer'] == 1) {
                $strClass = 'LeavePurple';
            } else {
                if ($arrRequest['isOK'] == 1) {
                    $strClass = 'LeaveOK';
                } else {
                    $strClass = 'LeaveNotOK';
                }
            }
        }
        $intWeek = bbcweeknumber($arrRequest['ShortDate']);
        $intDay = $dowMap[date("D", strtotime($arrRequest['ShortDate']))];

        $readonly = '';
        $text = '';
        $sickClass = '';
        if (isset($arrAllocations['Weeks'][$intWeek][$intDay])) {
            if (($arrAllocations['Weeks'][$intWeek][$intDay]['MarkedSickness'] == 1) || ($arrAllocations['Weeks'][$intWeek][$intDay]['ChargingId'] == 1) || ($arrAllocations['Weeks'][$intWeek][$intDay]['MarkedOvertime'] == 1) || ($arrAllocations['Weeks'][$intWeek][$intDay]['Duty'] == 'U-Sick') || ($arrAllocations['Weeks'][$intWeek][$intDay]['Duty'] == 'Sick') || ($arrAllocations['Weeks'][$intWeek][$intDay]['Duty'] == '-Sick')) {
                $readonly = "readonly";
                $text = "charging/sickness/overtime";
                $sickClass = 'SickChargingOvertime';
            }
        }
        $currentDate = date("Y-m-d");
        $currentYear = date("Y");
        if ($currentDate <= $currentYear . "-06-30") {

            $newYear = $currentYear - 1;
        } else {
            $newYear = $currentYear;
        }

        if (($isDivisionalAdmin == 1 || $isSysAdmin == 1 || $isSchedulingTeamAdmin == 1) && $sickClass == '') {
            $readonly = "";
        } else {
            if ($arrRequest['ShortDate'] < $newYear . "-04-01") {
                $readonly = "readonly";
            }
        }

        if(isset($arrStarts[$arrRequest['ShortDate']])) {
            $groupSetter = 'group-' . $arrStarts[$arrRequest['ShortDate']];
        }
        if(isset($arrEnds[$arrRequest['ShortDate']])) {
            $groupSetter = 'group-' . $arrEnds[$arrRequest['ShortDate']];
        }

        if ($intRowCount % 2 == 0) {
            echo '<tr class="VeryVeryLightGrey  approverow" data-group="' . $groupSetter . '">';
        } else {
            echo '<tr class="VeryLightGrey approverow" data-group="' . $groupSetter . '">';
        }

        echo '<td class="' . $strClass . '">';
        if (isset($arrStarts[$arrRequest['ShortDate']])) {
            echo '<img border="0" class="handcursor" src="images/hourpointer-up.png" width="10px" height="10px" onclick="javascript:AdminApplication(' . $indLeaveID . ', 0);">';

        } else {
            echo '<img border="0" src="images/pixel.png" width="10px" height="10px">';
        }
        echo '<br>';
        if (isset($arrEnds[$arrRequest['ShortDate']])) {
            echo '<img border="0" class="handcursor" src="images/hourpointer.png" width="10px" height="10px" onclick="javascript:AdminApplication(' . $indLeaveID . ', 1);">';

        } else {
            echo '<img border="0" src="images/pixel.png" width="10px" height="10px">';
        }

        echo '</td>';
        echo '<td>';
        if ($arrRequest['Unlikely'] == 0) {
            echo $arrRequest['Date'];
        } else {
            echo '[' . $arrRequest['Date'] . ']';
        }
        echo '</td>';
        echo '<td class="wordBreak">';
        echo $arrRequest['GroupDesc'];
        $disabledGrp = '';
        $pdlAppliedRow = '';
        if (in_array($arrRequest['GroupID'], $arrLeaveAdmingrps) && isset($arrAllocations['Weeks'][$intWeek][$intDay]['IsPartDayLeaveApplied']) && ($arrAllocations['Weeks'][$intWeek][$intDay]['IsPartDayLeaveApplied'] != 1)) {
            $disabledGrp = '';
        }
        echo '<br>';
        echo ' (' . $arrRequest['TypeDesc'] . ')';
        echo '</td>';
        $rightClickPartDayLeave = '';
        $isPartDayAllowed = $arrAllocations['Weeks'][$intWeek][$intDay]['IsPartDayAllowed'];
        $isLeaveApproved = $arrAllocations['Weeks'][$intWeek][$intDay]['IsLeaveApproved'];
        $isPartDayLeaveApplied = $arrAllocations['Weeks'][$intWeek][$intDay]['IsPartDayLeaveApplied'];
        $dutyDuration = $arrAllocations['Weeks'][$intWeek][$intDay]['Duration'];
        $dutyName = $arrAllocations['Weeks'][$intWeek][$intDay]['Duty'];
        $dutyStartTime = $arrAllocations['Weeks'][$intWeek][$intDay]['DutyStartTime'];
        $dutyEndTime = $arrAllocations['Weeks'][$intWeek][$intDay]['DutyEndTime'];
        $pdlStartTime = $arrAllocations['Weeks'][$intWeek][$intDay]['LeaveStartTime'];
        $pdlEndTime = $arrAllocations['Weeks'][$intWeek][$intDay]['LeaveEndTime'];
        $leaveId = $arrAllocations['Weeks'][$intWeek][$intDay]['LeaveID'] ?? $indLeaveID;
        $dutyDurationExcBreakSec = $arrAllocations['Weeks'][$intWeek][$intDay]['DutyDurationExcBreak'];
        $dutyId = $arrAllocations['Weeks'][$intWeek][$intDay]['DutyID'];
        $calPdlEndTime = $pdlEndTime;
        if($pdlStartTime > $pdlEndTime){
            $calPdlEndTime = (int) (86400+$pdlEndTime);
        }
        $pdlDurationApproved = ($calPdlEndTime - $pdlStartTime);
        if (isset($arrAllocations['Weeks'][$intWeek][$intDay])) {
            if (($isPartDayAllowed == 1) && (empty($pdlStartTime)) && (empty($pdlEndTime))) {
                $rightClickPartDayLeave = 'pdl-leave-apply';
            }
            if ($isPartDayAllowed == 2) {
                $rightClickPartDayLeave = 'pdl-leave-approved';
            }
        }
        $emptyDutyClass='';
        if(($arrAllocations['Weeks'][$intWeek][$intDay]=='') || ($dutyName == 'U') || ($dutyName == 'Sick') || ($dutyName == 'Leave') || ($dutyName == 'OFF Leave') || (($dutyStartTime == 0 && $dutyEndTime == 0))){
            $emptyDutyClass =  'emptyDutyCell';
        }
        echo '<td class="wordBreak ' . $sickClass . ' ' . $rightClickPartDayLeave . ' '.$emptyDutyClass.'" title="' . $text . '" data-week="' . $intWeek . '" data-leaveId="' . $leaveId . '" data-duty-starttime="' . $dutyStartTime . '" data-duty-endtime="' . $dutyEndTime . '" data-duty-pdlstarttime="' . $pdlStartTime . '" data-duty-pdlendtime="' . $pdlEndTime . '" data-duty-name="' . $dutyName . '" data-duty-duration="' . $dutyDurationExcBreakSec . '" data-duty-id="' . $dutyId . '" id="leaveTd' . $leaveId . '" data-popup-open-id="' . $_REQUEST['id'] . '" data-popup-open-week="' . $_REQUEST['week'] . '" data-scheduling-person="' . $_REQUEST['dataSchedulingPerson'] . '" data-row-id="' . $_REQUEST['dataRowId'] . '" data-duty-name="' . $_REQUEST['dataDutyName'] . '" data-row-start="' . $_REQUEST['dataRowStart'] . '" data-row-end="' . $_REQUEST['dataRowEnd'] . '" data-unique-id="' . $_REQUEST['dataUniqueId'] . '" data-duty-date="' . $_REQUEST['dataDDate'] . '">';
        if (isset($arrAllocations['Weeks'][$intWeek][$intDay])) {

            echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
            echo '<tr>';
            echo '<td style="text-align:left; vertical-align:middle; border:0;">' . $dutyName . '</td>';
            echo '</tr>';
            echo '<tr>';
            if (($isPartDayLeaveApplied == 1) && (($dutyStartTime != 0 && $dutyEndTime == 0) || ($dutyStartTime == 0 && $dutyEndTime != 0) || ($dutyStartTime != 0 && $dutyEndTime != 0)) && isset($dutyDuration)) {
                echo '<td style="text-align:left; vertical-align:middle; border:0;">';
                echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
                echo '<tr>';
                echo '<td width="100%" style="text-align:left; vertical-align:middle; border:0;">' . displayTime($allocServiceObj->seconds2hours($dutyStartTime)) . '-' . displayTime($allocServiceObj->seconds2hours($dutyEndTime)) . '&nbsp;&nbsp;&nbsp;' . $dutyDuration . ' hrs</td>';
                echo '</tr>';
                echo '</table>';
                echo '</td>';
            } else {
                if((strtoupper($dutyName) != 'U') && (strtoupper($dutyName) != 'SICK') && (strtoupper($dutyName) != 'LEAVE') && (strtoupper($dutyName) != 'OFF LEAVE') && (strtoupper($dutyName) != ($dutyStartTime == 0 && $dutyEndTime == 0)))
                {
                    echo '<td style="text-align:left; vertical-align:middle; border:0;">' . displayTime($allocServiceObj->seconds2hours($dutyStartTime)) . '-' . displayTime($allocServiceObj->seconds2hours($dutyEndTime)) . '&nbsp;&nbsp;&nbsp;' . $dutyDuration . ' hrs</td>';
                }else{
                    echo '<td style="text-align:left; vertical-align:middle; border:0;">' . $dutyDuration . ' hrs</td>';
                }
            }
            echo '</tr>';
            if (($pdlStartTime != '') && ($pdlEndTime != '') && (($pdlStartTime != 0) || ($pdlEndTime != 0))) {
                echo '<tr>';
                echo '<td style="text-align:left; vertical-align:middle; border:0;">';
                if ($isLeaveApproved == 1) {
                    echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
                    echo '<tr>';
                    echo '<td style="text-align:left; vertical-align:middle; border:0; color:#109146; font-weight:bold; font-size:7pt;" id="pdlTimeTd' . $leaveId . '" data-pdlstarttime="' . $pdlStartTime . '" data-pdlendtime="' . $pdlEndTime . '" data-duty-date="' . $arrRequest['Date'] . '">L: ' . displayTime($allocServiceObj->seconds2hours($pdlStartTime)) . '-' . displayTime($allocServiceObj->seconds2hours($pdlEndTime)) . '&nbsp;&nbsp;&nbsp;' . displayTime(number_format((float) ($pdlDurationApproved / 3600), 2, '.', ''), '.') . ' hrs' . '</td>';

                    echo '<td width="20%" style="text-align:right; vertical-align:middle; border:0;" id="pdlIcon' . $leaveId . '"></td>';
                    echo '</tr>';
                    echo '</table>';

                } else {
                    echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
                    echo '<tr>';
                    echo '<td style="text-align:left; vertical-align:middle; border:0; font-size:7pt; font-weight:bold; color:#FF0000;" id="pdlTimeTd' . $leaveId . '" data-pdlstarttime="' . $pdlStartTime . '" data-pdlendtime="' . $pdlEndTime . '" data-duty-date="' . $arrRequest['Date'] . '">L: ' . displayTime($allocServiceObj->seconds2hours($pdlStartTime)) . '-' . displayTime($allocServiceObj->seconds2hours($pdlEndTime)) . '&nbsp;&nbsp;&nbsp;' . displayTime(number_format((float) ($pdlDurationApproved / 3600), 2, '.', ''), '.') . ' hrs' . '</td>';

                    $rightClickPartDayLeaveTd = '';
                    if ($isPartDayAllowed == 1) {
                        $rightClickPartDayLeaveTd = 'pdl-leave-unapproved';
                    }
                    echo '<td width="20%" style="text-align:right; vertical-align:middle; border:0;" id="pdlIcon' . $leaveId . '">';
                    echo '<span class="' . $rightClickPartDayLeaveTd . '" style="display: flex; flex-direction: row; align-items: center; float: right;" data-week="' . $intWeek . '" data-leaveId="' . $leaveId . '" data-duty-starttime="' . $dutyStartTime . '" data-duty-endtime="' . $dutyEndTime . '" data-duty-pdlstarttime="' . $pdlStartTime . '" data-duty-pdlendtime="' . $pdlEndTime . '" data-duty-name="' . $dutyName . '" data-duty-duration="' . $dutyDurationExcBreakSec . '" data-duty-id="' . $dutyId . '" data-popup-open-id="' . $_REQUEST['id'] . '" data-popup-open-week="' . $_REQUEST['week'] . '" data-scheduling-person="' . $_REQUEST['dataSchedulingPerson'] . '" data-row-id="' . $_REQUEST['dataRowId'] . '" data-duty-name="' . $_REQUEST['dataDutyName'] . '" data-row-start="' . $_REQUEST['dataRowStart'] . '" data-row-end="' . $_REQUEST['dataRowEnd'] . '" data-unique-id="' . $_REQUEST['dataUniqueId'] . '" data-duty-date="' . $_REQUEST['dataDDate'] . '">';
                    echo '<span style="height: 10px; margin-right: 3px;"><img src="../../images/menu/page_white_edit.png" width="10px"></span>';
                    echo '<span style="color:#474747;">PDL</span>';
                    echo '</span>';
                    echo '</td>';

                    echo '</tr>';
                    echo '</table>';
                }
                echo '</td>';
                echo '</tr>';
            } else {
                echo '<tr>';
                echo '<td style="text-align:left; vertical-align:middle; border:0; color:#109146; font-weight:bold; font-size:7pt;" id="pdlTimeTd' . $leaveId . '"></td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        echo '</td>';
        foreach ($arrAllocLeaveTypes as $intTypeID => $arrAllocLeaveType) {
            echo '<td>';
            if (isset($arrRequest['Amounts'][$intTypeID])) {
                $intLeaveAmount = $arrRequest['Amounts'][$intTypeID];
            } else {
                $intLeaveAmount = '';
            }

            if ($intTypeID == $ExceptionalId) {
                $class = 'reasonValid';
            } else {
                $class = '';
            }
            if (!empty($arrRequest['Amounts'][$ExceptionalId])) {
                $disabled = '';
            } else {
                $disabled = 'disabled';
            }
            if (($arrAllocations['Weeks'][$intWeek][$intDay]['IsPartDayLeaveApplied'] == 1) && ($arrAllocations['Weeks'][$intWeek][$intDay]['IsLeaveApproved'] == 0 || $arrAllocations['Weeks'][$intWeek][$intDay]['IsLeaveApproved'] == 1)) {
                $pdlAppliedRow = 'pdl-applied-' . $indLeaveID;
            }

            echo '<input name="Amounts[' . $indLeaveID . '-' . $intTypeID . ']" id="' . $indLeaveID . '-' . $intTypeID . '" type="text" size="3" class="' . $class . ' numberInput ' . $pdlAppliedRow . '" ' . $readonly . '  ' . $disabledGrp . ' value="' . $intLeaveAmount . '" step="any"  oninput="if(this.value < 0){ this.value=Math.abs(this.value);}" inputmode="number" pattern="^\d*(\.\d{0,2})?$" title="Input must be a number" data-leave-id="' . $indLeaveID . '" data-duty-pdlstarttime="' . $pdlStartTime . '" data-duty-pdlendtime="' . $pdlEndTime . '"/>';
            echo '<input name="strStaffNumber" type="hidden" name="PDLStartTime-' . $indLeaveID . '"="' . $strStaffNumber . '"/>';
            echo '<input name="strScheduledPeopleId" type="hidden" value="' . $strScheduledPeopleId . '"/>';
            echo '</td>';
        }
        // The reason.....
        echo '<td>';
        $arrReasonId = $arrRequest['ReasonID'] ?? '';
        echo '<input id="reasonTypeId" type="hidden" value="' . $intTypeID . '"/>';
        echo '<select size="1" id="Reason' . $indLeaveID . '-' . $intTypeID . '" name="ExceptionalTypes[' . $indLeaveID . '-' . $intTypeID . '-' . $arrReasonId . ']" class="reason" style="width: 100%;"  ' . $disabled . '  data-attr="' . $indLeaveID . '-' . $intTypeID . '">';
        echo '<option value="0">-</option>';
        foreach ($arrExceptionalTypes as $dataExceptionalType) {

            if (isset($arrReasonId) && $arrReasonId == $dataExceptionalType['ID']) {
                echo '<option value="' . $dataExceptionalType['ID'] . '" selected>' . $dataExceptionalType['Name'] . '</option>';
            } else {
                echo '<option value="' . $dataExceptionalType['ID'] . '">' . $dataExceptionalType['Name'] . '</option>';
            }
        }
        echo '</select>';
        echo '</td>';
        echo '<td style="display:none;" class="totalrow"></td>';
        if ($isLeaveApproved == 1) {
            echo '<input name="PDLTime[' . $indLeaveID . '][0]" id="PDLStartTime-' . $indLeaveID . '" type="hidden" value="' . $pdlStartTime . '"/>';
            echo '<input name="PDLTime[' . $indLeaveID . '][1]" id="PDLEndTime-' . $indLeaveID . '" type="hidden" value="' . $pdlEndTime . '"/>';
            echo '<input name="PDLTime[' . $indLeaveID . '][2]" id="PDLDate-' . $indLeaveID . '" type="hidden" value="' . $arrRequest['Date'] . '"/>';
        } else {
            echo '<input name="PDLTime[' . $indLeaveID . '][0]" id="PDLStartTime-' . $indLeaveID . '" type="hidden"/>';
            echo '<input name="PDLTime[' . $indLeaveID . '][1]" id="PDLEndTime-' . $indLeaveID . '" type="hidden"/>';
            echo '<input name="PDLTime[' . $indLeaveID . '][2]" id="PDLDate-' . $indLeaveID . '" type="hidden" value="' . $arrRequest['Date'] . '"/>';
        }

        echo '</tr>';
        if (isset($arrEnds[$arrRequest['ShortDate']])) {
            echo '<tr height="5px">';
            echo '<td colspan="' . (5 + count($arrAllocLeaveTypes)) . '" class="DarkGrey">';

            echo '</td>';
            echo '</tr>';
        }
        $intRowCount++;
    }
    echo '</tbody>';
    echo '</table>';

    echo '<div class="leaveManageAction">';
    echo '<input id="submit" name="submit" type="submit" value="Submit">&nbsp;&nbsp;</input><input id="cancelBtn" type="button" value="Cancel" onclick="cancel()">';
    echo '</div>';

    echo '</form>';
    echo '</div>';

    echo '<div id="pdl-form" title="Part Day Leave" style="overflow:visible;"></div>';
    ?>

<script type="text/javascript">
var dialogForm = $("#pdl-form");
var pdlTempData = [];
$(function(){
    $.contextMenu({
        selector: '.pdl-leave-apply',
        callback: function(key, options) {

        },
        items: {
            "partDayUnapproved1": {
                name: "Convert to PDL",
                icon: "edit",
                callback: function(key, options) {
                    let argData = {
                        'leaveId' : options.$trigger.attr('data-leaveId'),
                        'dutyName' : options.$trigger.attr('data-duty-name'),
                        'dutyDuration' : options.$trigger.attr('data-duty-duration'),
                        'dutyStartTime' : options.$trigger.attr('data-duty-starttime'),
                        'dutyEndTime' : options.$trigger.attr('data-duty-endtime'),
                        'pdlStartTime' : options.$trigger.attr('data-duty-pdlstarttime'),
                        'pdlEndTime' : options.$trigger.attr('data-duty-pdlendtime'),
                        'dutyId' : options.$trigger.attr('data-duty-id')
                    };
                    pdlApprovePopup(argData);
                }
            },
            "delete_leave": {
                name: "Delete this Leave",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    return true;
                },
                callback: function(key, options) {
                    singleLeaveDelete(options);
                }
            },
            "delete_leave_block": {
                name: "Delete this block of Leave",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    var dataGroup = options.$trigger.parent().attr('data-group');
                    return $('#LeaveApproveTables [data-group="' + dataGroup + '"]').length >  1;
                },
                callback: function(key, options) {
                    groupLeaveDelete(options)
                }
            }
        }
    });

    $.contextMenu({
        selector: '.pdl-leave-unapproved',
        callback: function(key, options) {

        },
        items: {
            "partDayUnapproved2": {
                name: "View/Edit PDL",
                icon: "edit",
                callback: function(key, options) {
                    let argData = {
                        'leaveId' : options.$trigger.attr('data-leaveId'),
                        'dutyName' : options.$trigger.attr('data-duty-name'),
                        'dutyDuration' : options.$trigger.attr('data-duty-duration'),
                        'dutyStartTime' : options.$trigger.attr('data-duty-starttime'),
                        'dutyEndTime' : options.$trigger.attr('data-duty-endtime'),
                        'pdlStartTime' : options.$trigger.attr('data-duty-pdlstarttime'),
                        'pdlEndTime' : options.$trigger.attr('data-duty-pdlendtime'),
                        'dutyId' : options.$trigger.attr('data-duty-id')
                    };
                    pdlApprovePopup(argData);
                }
            },
            "convertToFullDayPDL1": {
                name: "Convert to a Full Day Leave",
                icon: "edit",
                callback: function(key, options) {
                    let argData = {
                        'leaveId' : options.$trigger.attr('data-leaveId'),
                        'allocationid' : options.$trigger.attr('data-duty-id'),
                        'unapprovedPDL' : 1
                    };
                    let popupLeaveId = parseInt(options.$trigger.attr('data-popup-open-id'));
                    let weekNum = parseInt(options.$trigger.attr('data-popup-open-week'));

                    let dataSchedulingPerson = options.$trigger.attr('data-scheduling-person');
                    let dataRowId = options.$trigger.attr('data-row-id');
                    let dataDutyName = options.$trigger.attr('data-duty-name');
                    let dataRowStart = options.$trigger.attr('data-row-start');
                    let dataRowEnd = options.$trigger.attr('data-row-end');
                    let dataUniqueId = options.$trigger.attr('data-unique-id');
                    let dataDutyDate = options.$trigger.attr('data-duty-date');

                    $.ajax({
                        type: "post",
                        url: "/page-includes/leave/convert-full-day-leave.php",
                        data: argData,
                        success: function (response) {
                            response = $.parseJSON(response);
                            if(response.status == true){
                                setTimeout(function() {
                                    openManagePopupEditWeekly(popupLeaveId,weekNum,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDutyDate);
                                    // refreshAllocatedSectionTr(dataSchedulingPerson,dataRowId,dataDutyDate,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,'','CONTEXTMENUEDITDUTY',0,0,0,'Yes');
                                    reloadeditweeklygrid('','','','','ALL');
                                }, 1000);
                            }

                        }
                    });
                }
            },
            "delete_leave": {
                name: "Delete this Leave",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    return true;
                },
                callback: function(key, options) {
                    singleLeaveDelete(options);
                }
            },
            "delete_leave_block": {
                name: "Delete this block of Leave",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    leaveId = options.$trigger.attr('data-leaveid');
                    var dataGroup = $('#leaveTd' + leaveId).parent().attr('data-group');
                    return $('#LeaveApproveTables [data-group="' + dataGroup + '"]').length >  1;
                },
                callback: function(key, options) {
                    leaveId = options.$trigger.attr('data-leaveid');
                    options.$trigger = $('#leaveTd' + leaveId);
                    groupLeaveDelete(options);
                }
            }
        }
    });

    $.contextMenu({
        selector: '.pdl-leave-approved',
        callback: function(key, options) {

        },
        items: {
            "editPDL": {
                name: "Edit PDL",
                icon: "edit",
                callback: function(key, options) {
                    let argData = {
                        'leaveId' : options.$trigger.attr('data-leaveId'),
                        'dutyName' : options.$trigger.attr('data-duty-name'),
                        'dutyDuration' : options.$trigger.attr('data-duty-duration'),
                        'dutyStartTime' : (options.$trigger.attr('data-duty-starttime') != '') ? options.$trigger.attr('data-duty-starttime') : 0,
                        'dutyEndTime' : (options.$trigger.attr('data-duty-endtime') != '') ? options.$trigger.attr('data-duty-endtime') : 0,
                        'pdlStartTime' : options.$trigger.attr('data-duty-pdlstarttime'),
                        'pdlEndTime' : options.$trigger.attr('data-duty-pdlendtime'),
                        'dutyId' : options.$trigger.attr('data-duty-id')
                    };
                    pdlApprovePopup(argData);
                }
            },
            "convertToFullDayPDL2": {
                name: "Convert to a Full Day Leave",
                icon: "edit",
                callback: function(key, options) {
					           let argData = {
                        'leaveId' : options.$trigger.attr('data-leaveId'),
						'allocationid' : options.$trigger.attr('data-duty-id'),
                        'dutyName' : options.$trigger.attr('data-duty-name'),
                        'dutyDuration' : options.$trigger.attr('data-duty-duration'),
                        'dutyStartTime' : (options.$trigger.attr('data-duty-starttime') != '') ? options.$trigger.attr('data-duty-starttime') : 0,
                        'dutyEndTime' : (options.$trigger.attr('data-duty-endtime') != '') ? options.$trigger.attr('data-duty-endtime') : 0,
                        'pdlStartTime' : options.$trigger.attr('data-duty-pdlstarttime'),
                        'pdlEndTime' : options.$trigger.attr('data-duty-pdlendtime'),
                        'dutyId' : options.$trigger.attr('data-duty-id'),
                        'unapprovedPDL' : 0
                    };
                    let popupLeaveId = parseInt(options.$trigger.attr('data-popup-open-id'));
                    let weekNum = parseInt(options.$trigger.attr('data-popup-open-week'));

                    let dataSchedulingPerson = options.$trigger.attr('data-scheduling-person');
                    let dataRowId = options.$trigger.attr('data-row-id');
                    let dataDutyName = options.$trigger.attr('data-duty-name');
                    let dataRowStart = options.$trigger.attr('data-row-start');
                    let dataRowEnd = options.$trigger.attr('data-row-end');
                    let dataUniqueId = options.$trigger.attr('data-unique-id');
                    let dataDutyDate = options.$trigger.attr('data-duty-date');
                    $.ajax({
                        type: "post",
                        url: "/page-includes/leave/convert-full-day-leave.php",
                        data: argData,
                        success: function (response) {
                            response = $.parseJSON(response);
                            if(response.status == true){
                                setTimeout(function() {
                                    openManagePopupEditWeekly(popupLeaveId,weekNum,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDutyDate);
                                    // refreshAllocatedSectionTr(dataSchedulingPerson,dataRowId,dataDutyDate,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,'','CONTEXTMENUEDITDUTY',0,0,0,'Yes');
                                    reloadeditweeklygrid('','','','','ALL');
                                }, 1000);
                            }

                        }
                    });
                }
            },
            "delete_leave": {
                name: "Delete this Leave",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    return true;
                },
                callback: function(key, options) {
                    singleLeaveDelete(options);
                }
            },
            "delete_leave_block": {
                name: "Delete this block of Leave",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    var dataGroup = options.$trigger.parent().attr('data-group');
                    return $('#LeaveApproveTables [data-group="' + dataGroup + '"]').length >  1;
                },
                callback: function(key, options) {
                    groupLeaveDelete(options)
                }
            }
        }
    });

    $.contextMenu({
        selector: '.emptyDutyCell',
        callback: function(key, options) {

        },
        items: {
            "delete_leave": {
                name: "Delete this Leave",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    return options.$trigger.parent().find('.pdl-leave-unapproved').length == 0;
                },
                callback: function(key, options) {
                    singleLeaveDelete(options);
                }
            },
            "delete_leave_block": {
                name: "Delete this block of Leave",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    var dataGroup = options.$trigger.parent().attr('data-group');
                    return $('#LeaveApproveTables [data-group="' + dataGroup + '"]').length >  1 && options.$trigger.parent().find('.pdl-leave-unapproved').length == 0;
                },
                callback: function(key, options) {
                    groupLeaveDelete(options)
                }
            }
        }
    });
});


function singleLeaveDelete(options) {
    var choosenLeaveid = options.$trigger.attr("data-leaveid");
    var allocationid =  options.$trigger.attr("data-duty-id");
    $( "#dialog-leave-delete" ).dialog({
        width:600,
        buttons: {
            "Yes": function() {
                $( this ).dialog( "close" );
                $.ajax({
                    url:"page-includes/leave/leave-delete.php",
                    type:'POST',
                    data: { 'id': choosenLeaveid, 'allocationid': allocationid },
                    dataType:"text",
                    success:function(response,status,http) {
                        let week = null, leaveId = null;
                        $('#LeaveApproveTables tr').each(function() {
                            $(this).find('td[data-leaveid]').each(function(index){
                               week = $(this).attr('data-week');
                               leaveId = $(this).attr('data-leaveid');
                            });
                            if(choosenLeaveid != leaveId && leaveId != null) {
                                return false;
                            }
                        });
                       updatePopUp(leaveId, week);
                    },
                    error: function(http,status,error){
                        alert("Error Found :" + error);
                    }
                });
            },
            "No": function() {
                $( this ).dialog( "close" );
            },
        }
    });
}

function groupLeaveDelete(options) {
    let dataLeaveIds = [];
    let allocationIds = []
    var dataGroup = options.$trigger.parent().attr('data-group');
    $('#LeaveApproveTables [data-group="' + dataGroup + '"]').each(function() {
        $(this).find('td[data-leaveid]').each(function(index){
            dataLeaveIds.push($(this).attr("data-leaveid"));
            allocationIds.push($(this).attr("data-duty-id"));
        });
    });
    $( "#dialog-leave-delete-group" ).dialog({
        width:600,
        buttons: {
            "Yes": function() {
                $( this ).dialog( "close" );
                $.ajax({
                    url:"page-includes/leave/leave-delete.php",
                    type:'POST',
                    data: {'id' : dataLeaveIds, 'allocationid': allocationIds},
                    dataType:"text",
                    success:function(response,status,http) {
                        let week = null, leaveId = null;
                        $('#LeaveApproveTables tr').each(function() {
                            $(this).find('td[data-leaveid]').each(function(index){
                               week = $(this).attr('data-week');
                               leaveId = $(this).attr('data-leaveid');
                            });
                            if(!dataLeaveIds.includes(leaveId) && leaveId != null) {
                                return false;
                            }
                        });
                       updatePopUp(leaveId, week);
                    },
                    error: function(http,status,error){
                        alert("Error Found :" + error);
                    }
                });
            },
            "No": function() {
                $( this ).dialog( "close" );
            },
        }
    });
}

function updatePopUp(leaveid, week) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/leave/edit-weekly-leave-approve-popup.php',
        data: {
            'id': leaveid,
            'week': week,
            'callpage': '<?php echo $callingPagename; ?>',
        },
        success: function (data) {
            let $newLeavePopUp =  $('<div></div>').append(data);
            $('.numberInput').each(function(i, obj) {
                $newLeavePopUp.find("#" + $(this).attr('id')).val($(this).val());
            });

            $('.reason').each(function(i, obj) {
                if(!$(this).prop('disabled')) {
                    $newLeavePopUp.find('#' + $(this).attr('id') + 'option[value="' + $(this).val() + '"]').attr("selected", "selected");
                }
            });

            reloadeditweeklygrid('','','','','ALL');
            if($newLeavePopUp.find('.numberInput').length == 0) {
                var intervalDiscard = setInterval(function () {
                    if($("#loadingWeekly").is(":hidden")) {
                        clearInterval(intervalDiscard);
                        jQuery(document).trigger('close.facebox');
                    }
                }, 1000);
            } else {
                $.facebox($newLeavePopUp);
            }
        },
        error:function (data) {
            alert('some error found in leave approve popup call.');
        }
    });
}

function pdlApprovePopup(argData){
    dialogForm.html('');
    pdlDialog = dialogForm.dialog({
        autoOpen: false,
        height: 250,
        width: 500,
        modal: true,
        buttons: {
            "OK": function() {
                $('#errDiv').hide();
                let validationPass = true;
                let pdlStartTime = $('#starttimemasterduty').val();
                pdlStartTime = pdlStartTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
                let pdlEndTime = $('#endtimemasterduty').val();
                pdlEndTime = pdlEndTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
                if(pdlStartTime == 0){
                    pdlStartTime = '00:00';
                }
                if(pdlEndTime == 0){
                    pdlEndTime = '00:00';
                }

                let pdlStartTimeSec = 0;
                let pdlEndTimeSec = 0;
                let pdlEndTimeDbSave = 0;
                if(pdlStartTime != 0){
                    pdlStartTimeSec = convertTimeIntoSeconds(pdlStartTime);
                }
                if(pdlEndTime != 0){
                    pdlEndTimeSec = convertTimeIntoSeconds(pdlEndTime);
                    pdlEndTimeDbSave = pdlEndTimeSec;
                }

                if(pdlStartTimeSec > pdlEndTimeSec){
                    pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
                }

                if(pdlStartTime == '' && pdlEndTime != ''){
                    $('#errDiv').html('Please select PDL start time');
                    $('#errDiv').show();
                }
                if(pdlStartTime != '' && pdlEndTime == ''){
                    $('#errDiv').html('Please select PDL end time');
                    $('#errDiv').show();
                }
                if(pdlStartTime == '' && pdlEndTime == ''){
                    $('#errDiv').html('Please select PDL start and end time');
                    $('#errDiv').show();
                }

                if((pdlStartTimeSec != argData.pdlStartTime) || (pdlEndTimeSec != argData.pdlEndTime)){
                    var dutyStartTime = parseInt(argData.dutyStartTime);
                    var dutyEndTime = parseInt(argData.dutyEndTime);

                    if(dutyStartTime > dutyEndTime){
                        dutyEndTime = parseInt(86400 + dutyEndTime);
                    }
                }
                if(pdlStartTime != '' && pdlEndTime != ''){
                    if(dutyEndTime > 86400){
                        if((pdlStartTimeSec < pdlEndTimeSec) && (pdlStartTimeSec < dutyStartTime)){
                            pdlStartTimeSec = parseInt(86400 + pdlStartTimeSec);
                            pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
                        }
                    }
                    if(pdlStartTimeSec == dutyStartTime && pdlEndTimeSec < dutyEndTime){
                        let dutyEndTime15MinBefore = parseInt(dutyEndTime - 4500);
                        if(pdlEndTimeSec > dutyEndTime15MinBefore){
                            $('#errDiv').html('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                            validationPass = false;
                        }
                    } else if(pdlStartTimeSec > dutyStartTime && pdlEndTimeSec == dutyEndTime){
                        let dutyStartTime15MinAfter = parseInt(dutyStartTime + 4500);
                        if(pdlStartTimeSec < dutyStartTime15MinAfter){
                            $('#errDiv').html('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                            validationPass = false;
                        }
                    } else if(pdlStartTimeSec == dutyStartTime && pdlEndTimeSec == dutyEndTime){
                        $('#errDiv').html('Duty start and end time cannot be the same as Part Day Leave start and end time. Please change the duty start or end time.');
                        validationPass = false;
                    } else if(pdlStartTimeSec < dutyStartTime && pdlEndTimeSec > dutyEndTime){
                        $('#errDiv').html('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                        validationPass = false;
                    } else if(pdlStartTimeSec < dutyStartTime && pdlEndTimeSec <= dutyEndTime){
                        $('#errDiv').html('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                        validationPass = false;
                    } else if(pdlStartTimeSec >= dutyStartTime && pdlEndTimeSec > dutyEndTime){
                        $('#errDiv').html('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                        validationPass = false;
                    } else if(pdlStartTimeSec > dutyStartTime && pdlEndTimeSec < dutyEndTime){
                        let pdlDuration = parseInt(parseInt(pdlEndTimeSec) - parseInt(pdlStartTimeSec));
                        let dutyDuration = parseInt(parseInt(dutyEndTime) - parseInt(dutyStartTime));
                        if(pdlDuration == 0){
                            $('#errDiv').html('Part Day Leave start and end time can not be same');
                            validationPass = false;
                        }
                        if(pdlDuration > dutyDuration){
                            $('#errDiv').html('Part Day Leave timings does not lies between duty timings.');
                            validationPass = false;
                        }
                    }
                    if(validationPass == false){
                        $('#validationPassVal').val(0);
                        $('#errDiv').show();
                    } else {
                        $('#validationPassVal').val(1);
                    }
                }
                if($('#validationPassVal').val() == 1){
                    calculatePDLDuration('.');
                    let pdlStartTime = $('#starttimemasterduty').val();
                    pdlStartTime = pdlStartTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
                    let pdlEndTime = $('#endtimemasterduty').val();
                    pdlEndTime = pdlEndTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
                    if(pdlStartTime == 0){
                        pdlStartTime = '00:00';
                    }
                    if(pdlEndTime == 0){
                        pdlEndTime = '00:00';
                    }

                    if(pdlStartTime != 0){
                        pdlStartTimeSec = convertTimeIntoSeconds(pdlStartTime);
                    }
                    if(pdlEndTime != 0){
                        pdlEndTimeSec = convertTimeIntoSeconds(pdlEndTime);
                        pdlEndTimeDbSave = pdlEndTimeSec;
                    }
                    if(pdlStartTimeSec > pdlEndTimeSec){
                        pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
                    }

                    pdlDurationSec = parseInt(pdlEndTimeSec - pdlStartTimeSec);
                    let displayPdlDetail = 'L: '+pdlStartTime+'-'+pdlEndTime+'&nbsp;&nbsp;&nbsp;'+convertSecondsIntoTime(pdlDurationSec,'.');
                    var addNewObj = 1;
                    if(pdlTempData.length > 0){
                        for (const [key, value] of Object.entries(pdlTempData)) {
                            if(value.leaveId == $('#leaveId').val()){
                                pdlTempData[key]['pdlStartTime'] = pdlStartTimeSec;
                                pdlTempData[key]['pdlEndTime'] = pdlEndTimeDbSave;
                                pdlTempData[key]['pdlDuration'] = pdlDurationSec;
                                addNewObj = 0;
                                break;
                            }
                        }
                    }
                    if(addNewObj == 1){
                        let pdlTempObj = {};
                        Object.assign(pdlTempObj, {
                            "leaveId": $('#leaveId').val(),
                            "pdlStartTime": pdlStartTimeSec,
                            "pdlEndTime": pdlEndTimeDbSave,
                            "pdlDuration": pdlDurationSec
                        });
                        pdlTempData.push(pdlTempObj);
                    }

                    $('#PDLStartTime-'+$('#leaveId').val()).val(pdlStartTimeSec);
                    $('#PDLEndTime-'+$('#leaveId').val()).val(pdlEndTimeDbSave);
                    $('.pdl-applied-'+$('#leaveId').val()).removeAttr('disabled');
                    $('#pdlTimeTd'+$('#leaveId').val()).html(displayPdlDetail);
                    $('#leaveTd'+$('#leaveId').val()).attr('data-duty-pdlstarttime',pdlStartTimeSec);
                    $('#leaveTd'+$('#leaveId').val()).attr('data-duty-pdlendtime',pdlEndTimeDbSave);
                    $('.pdl-applied-'+$('#leaveId').val()).attr('data-duty-pdlstarttime',pdlStartTimeSec);
                    $('.pdl-applied-'+$('#leaveId').val()).attr('data-duty-pdlendtime',pdlEndTimeDbSave);
                    var pdlDutyDate = $('#PDLDate-'+$('#leaveId').val()).val();
                    if($('#adminApplyPDL').val() == 0){
                        $.ajax({
                            type: "post",
                            url: "/page-includes/leave/pdl-actions.php",
                            data: {
                              'action': 'verifypdl',
                              'leaveId' : $('#leaveId').val(),
                              'pdlStartTime' : pdlStartTimeSec,
                              'pdlEndTime' : pdlEndTimeDbSave,
                              'dDate': pdlDutyDate
                            },
                            success: function (response) {
                                response = $.parseJSON(response);
                                if(response.status == true){
                                    pdlDialog.html('');
                                    pdlDialog.dialog("close");
                                    pdlDialog.dialog('destroy');
                                    $('.ui-timepicker-wrapper').css('display','none');
                                    setTimeout(function() {
                                        reloadeditweeklygrid('','','','','ALL');
                                    }, 1000);
                                } else {
                                    $('#errDiv').html('Part Day Leave not Verified.');
                                    $('#errDiv').show();
                                }
                            }
                        });
                    } else {
                        $.ajax({
                            type: "post",
                            url: "/page-includes/leave/pdl-actions.php",
                            data: {
                                'action': 'autoapprovepdleditweekly',
                                'leaveId' : $('#leaveId').val(),
                                'pdlStartTime' : pdlStartTimeSec,
                                'pdlEndTime' : pdlEndTimeDbSave,
                                'openPopupId' : '<?php echo $_REQUEST['id']?>',
                                'openPopupWeek' : '<?php echo $_REQUEST['week']?>',
                                'dataSchedulingPerson' : '<?php echo $_REQUEST['dataSchedulingPerson']?>',
                                'dataRowId' : '<?php echo $_REQUEST['dataRowId']?>',
                                'dataDutyName' : '<?php echo $_REQUEST['dataDutyName']?>',
                                'dataRowStart' : '<?php echo $_REQUEST['dataRowStart']?>',
                                'dataRowEnd' : '<?php echo $_REQUEST['dataRowEnd']?>',
                                'dataUniqueId' : '<?php echo $_REQUEST['dataUniqueId']?>',
                                'dataDutyDate' : '<?php echo $_REQUEST['dataDDate']?>',
                                'dDate': pdlDutyDate
                            },
                            success: function (response) {
                                response = $.parseJSON(response);
                                if(response.status == true){
                                    pdlDialog.html('');
                                    pdlDialog.dialog("close");
                                    pdlDialog.dialog('destroy');
                                    $('.ui-timepicker-wrapper').css('display','none');
                                    let popupLeaveId = parseInt(response.openPopupId);
                                    let weekNum = parseInt(response.openPopupWeek);

                                    let dataSchedulingPerson = response.dataSchedulingPerson;
                                    let dataRowId = response.dataRowId;
                                    let dataDutyName = response.dataDutyName;
                                    let dataRowStart = response.dataRowStart;
                                    let dataRowEnd = response.dataRowEnd;
                                    let dataUniqueId = response.dataUniqueId;
                                    let dataDutyDate = response.dataDutyDate;
                                    setTimeout(function() {
                                        openManagePopupEditWeekly(popupLeaveId,weekNum,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDutyDate);
                                        // refreshAllocatedSectionTr(dataSchedulingPerson,dataRowId,dataDutyDate,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,'','CONTEXTMENUEDITDUTY',0,0,0,'Yes');
                                        reloadeditweeklygrid('','','','','ALL');
                                    }, 1000);
                                } else {
                                    $('#errDiv').html('Part Day Leave not saved.');
                                    $('#errDiv').show();
                                }
                            }
                        });
                    }
                }
            },
            Cancel: function() {
                pdlDialog.html('');
                pdlDialog.dialog("close");
                pdlDialog.dialog('destroy');
                $('.ui-timepicker-wrapper').css('display','none');
            }
        }
    });
    $('.ui-dialog-title').html("Part Day Leave");

    argData.pdlTempData = pdlTempData;

    $.ajax({
        type: "post",
        url: "/page-includes/leave/approve-part-day-leave.php",
        data: argData,
        success: function (response) {
            pdlDialog.html(response).dialog({
                modal: true,
                open: function(){
                    $('.ui-timepicker-wrapper').css('display','none');
                }
            }).dialog('open');
        }
    });
}

function calculatePDLDuration(seperator=':'){
    $('#errDiv').hide();
    let pdlStartTime = $('#starttimemasterduty').val();
    pdlStartTime = pdlStartTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
    let pdlEndTime = $('#endtimemasterduty').val();
    pdlEndTime = pdlEndTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
    if(pdlStartTime == 0){
        pdlStartTime = '00:00';
    }
    if(pdlEndTime == 0){
        pdlEndTime = '00:00';
    }

    if(pdlStartTime != '' && pdlEndTime != ''){
        let pdlStartTimeSec = 0;
        let pdlEndTimeSec = 0;
        let pdlDurationSec = 0;

        if(pdlStartTime != 0){
            pdlStartTimeSec = convertTimeIntoSeconds(pdlStartTime);
        }
        if(pdlEndTime != 0){
            pdlEndTimeSec = convertTimeIntoSeconds(pdlEndTime);
        }

        if(pdlStartTimeSec > pdlEndTimeSec){
            pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
        }
        pdlDurationSec = parseInt(pdlEndTimeSec - pdlStartTimeSec);
        $('#pdlDurationTd').html(convertSecondsIntoTime(pdlDurationSec,'.'));

        let dutyJobsData = $('#dutyJobs').val();
        if(dutyJobsData != ''){
            dutyJobsData = $.parseJSON(dutyJobsData);

            for (const [key, value] of Object.entries(dutyJobsData)) {
                let validPDLStartTime = true;
                let validPDLEndTime = true;
                let jobStartTimeInSec = convertTimeIntoSeconds(value.startTime);
                let jobEndTimeInSec = convertTimeIntoSeconds(value.endTime);

                if(jobStartTimeInSec > jobEndTimeInSec){
                    jobEndTimeInSec = parseInt(86400 + jobEndTimeInSec);
                }

                if((jobStartTimeInSec < pdlStartTimeSec) && (jobEndTimeInSec > pdlStartTimeSec)){
                    validPDLStartTime = false;
                }
                if((jobStartTimeInSec < pdlEndTimeSec) && (jobEndTimeInSec > pdlEndTimeSec)){
                    validPDLEndTime = false;
                }

                if((validPDLStartTime == false) || (validPDLEndTime == false)){
                    $('#errDiv').html('There are jobs coinciding with this leave request. If you continue to authorise this request, a job called  "Leave" will appear on the scheduled person\'s duty, and the jobs that coincide will appear in the Unallocated Jobs section of the Daily View.');
                    $('#errDiv').show();
                    $('#validationPassVal').val(1);
                    break;
                } else {
                    $('#validationPassVal').val(1);
                }
            }
        } else {
            $('#validationPassVal').val(1);
        }
    }
}

function convertTimeIntoSeconds(timeDuration){
    let [hours, minutes, seconds] = timeDuration.split(':');
    if(minutes == undefined){
        minutes = 0;
    }
    return Number(hours) * 60 * 60 + Number(minutes) * 60;
}

function convertSecondsIntoTime(secondsDuration, seperator = ':',displayHours='Yes'){
    let h = Math.floor(secondsDuration / 3600);
    let m = Math.floor(secondsDuration % 3600 / 60);

    let hDisplay = h;
    let mDisplay = m.toString();
    if(seperator == ':'){
        hDisplay = 0;
        if(h <= 9){
            hDisplay = '0'+h;
        } else {
            if(h > 24){
                if((h - 24) <= 9){
                    hDisplay = '0'+h;
                } else {
                    hDisplay = h;
                }
            } else {
                if(h == 24){
                    hDisplay = '00';
                } else {
                    hDisplay = h;
                }
            }
        }
        mDisplay = (m <= 9) ? '0'+m.toString() : m.toString();
    }

    if(seperator == '.'){
        switch(mDisplay){
            case "15":
                mDisplay = '25';
                break;
            case "30":
                mDisplay = '5';
                break;
            case "45":
                mDisplay = '75';
                break;
            default:
                mDisplay = '00';
        }
    }
    if(displayHours == 'Yes'){
        return hDisplay+seperator+mDisplay+' hrs';
    } else {
        return hDisplay+seperator+mDisplay;
    }
}

function customAlertByModelLeavePopup(msg){
    $("#CustomDivForModal").css("display", "block");
    let messageContainer = '<p>'+msg+'</p>';
    let dialog = $(messageContainer).dialog({
            width: 600,
            buttons: {
                "OK": function (){
                    dialog.dialog('close');
                    $("#CustomDivForModal").css("display", "none");
                    $(".ui-dialog-content").dialog("close");
                }
            }
        });
    $('.ui-dialog-titlebar-close').addClass("ModalCustomClass");
    $(".ModalCustomClass").on("click", function(){
        $("#CustomDivForModal").css("display", "none");
    });
}

function checkReasonValue(){
  $('select').removeClass("leaveManageerror");
  var val = [];
    $('.reasonValid').each(function(i){
        var currentId = $(this).attr('id');
        var currentval = $('#'+currentId).val();
        val[i] = currentval;
        if(val[i]>0){
            var result = currentId.split('-');
            var leaveid = result[0];
            var reasonTypeId = $('#reasonTypeId').val();
            var selectedreason = $('#Reason'+leaveid+'-'+reasonTypeId).val();

            if(selectedreason==0){
                $('#Reason'+leaveid+'-'+reasonTypeId).addClass("leaveManageerror");
                $('#Reason'+leaveid+'-'+reasonTypeId).focus();
            }

            $('#Reason'+leaveid+'-'+reasonTypeId).on('change', function() {
                var reasonval = this.value;
                if(reasonval>0){
                    $('#Reason'+leaveid+'-'+reasonTypeId).removeClass("leaveManageerror");
                }
            });
            return 1;
        } else {
            return 2;
        }
    });
    return 2;
  }

$(document).ready( function () {
    function calculateRowSum(leaveId='',pdlStTime=0,pdlEdTime=0){
        leaveId = parseInt(leaveId);
        pdlStTime = (pdlStTime != '') ? parseInt(pdlStTime) : 0;
        pdlEdTime = (pdlEdTime != '') ? parseInt(pdlEdTime) : 0;
        $('table tr:has(td):not(:last)').each(function(){
            var sum = 0;
            $(this).find('td').each(function(){
                sum += parseFloat($(this).find('.numberInput').val()) || 0;
            });
            $(this).find('td.totalrow').html(sum);
            let checkPDLHrs = false;
            let leavePDLDuration = parseFloat('0.00');
            if(leaveId != ''){
                if(pdlTempData.length > 0){
                    for (const [key, value] of Object.entries(pdlTempData)) {
                        if(value.leaveId == leaveId){
                            leavePDLDuration = parseFloat(convertSecondsIntoTime(value.pdlDuration,'.','No'));
                            checkPDLHrs = true;
                            break;
                        }
                    }
                } else {
                    if((pdlStTime != 0) || (pdlEdTime != 0)){
                        if(pdlStTime > pdlEdTime){
                            pdlEdTime = parseInt(86400 + pdlEdTime);
                        }

                        let pdlTDuration = (pdlEdTime - pdlStTime);
                        leavePDLDuration = parseFloat(convertSecondsIntoTime(pdlTDuration,'.','No'));
                        checkPDLHrs = true;
                    }
                }
            }
            if((sum > 22) && (checkPDLHrs == false)){
                customAlertByModel("Total Hrs for each day cannot be more than 22.<br>Please check the row(s) highlighted in Red.");
                $('#submit').prop('disabled',true);
                $(this).find('td input').addClass("Managetotalerror");
            } else {
                if(checkPDLHrs == true){
                    let tdLoopLeaveId = $(this).find('td input').attr('data-leave-id');
                    if(tdLoopLeaveId != undefined && tdLoopLeaveId == leaveId){
                        if(parseFloat(leavePDLDuration) < parseFloat(sum)){
                            customAlertByModel("Total Hrs for each day cannot be more than "+leavePDLDuration+".<br>Please check the row(s) highlighted in Red.");
                            $('#submit').prop('disabled',true);
                            $(this).find('td input').addClass("Managetotalerror");
                        } else {
                            if((parseFloat(leavePDLDuration) > parseFloat(sum)) && (parseFloat(sum) > 0)){
                              $('#submit').prop('disabled',true);
                              $(this).find('td input').addClass("Managetotalerror");
                            } else {
                                $('#PDLStartTime-'+tdLoopLeaveId).val($(this).find('td input').attr('data-duty-pdlstarttime'));
                                $('#PDLEndTime-'+tdLoopLeaveId).val($(this).find('td input').attr('data-duty-pdlendtime'));
                                $(this).find('td input').removeClass("Managetotalerror");
                            }
                        }
                    }
                } else {
                    $(this).find('td input').removeClass("Managetotalerror");
                }
            }
        });
    }

    $('.numberInput').keyup(function () {
        $('#submit').removeAttr('disabled');
        calculateRowSum($(this).attr('data-leave-id'),$(this).attr('data-duty-pdlstarttime'),$(this).attr('data-duty-pdlendtime'));
    })

    $('.reasonValid').keyup(function () {
        var currentId = $(this).attr('id');
        var result = currentId.split('-');
        var leaveid = result[0];
        var reasonTypeId = $('#reasonTypeId').val();
        var exceptionalVal = $('#'+currentId).val();
        if ( exceptionalVal>0 ) {
            $('#Reason'+leaveid+'-'+reasonTypeId).removeAttr('disabled');
        }
        if ( exceptionalVal=='' && exceptionalVal==0) {
            $('#Reason'+leaveid+'-'+reasonTypeId).prop('disabled',true);
        }
    });

    $('#submit').click(function(e) {
        var  res = checkReasonValue();
        var checkclass =  $('select').hasClass("leaveManageerror");

        if(checkclass){
            customAlertByModel("A reason is mandatory for Event Leave. Please select a reason from the dropdown.");
            e.preventDefault();
        }
    });

var callerPage = $('#callerPage').val();

$('#approveleave').validate({
    submitHandler: function(form) {
        $('#submit').prop('disabled',true);
        $.ajax({
            type:'POST',
            url: 'page-includes/leave/edit-weekly-leave-approve-popup.php',
            data:$('#approveleave').serialize(),
            success: function(data) {
                if((data == '') || (data == undefined)){
                    let dataSchedulingPerson = '<?php echo $_REQUEST['dataSchedulingPerson']?>'
                    let dataRowId = '<?php echo $_REQUEST['dataRowId']?>'
                    let dataDutyName = '<?php echo $_REQUEST['dataDutyName']?>'
                    let dataRowStart = '<?php echo $_REQUEST['dataRowStart']?>'
                    let dataRowEnd = '<?php echo $_REQUEST['dataRowEnd']?>'
                    let dataUniqueId = '<?php echo $_REQUEST['dataUniqueId']?>'
                    let dataDutyDate = '<?php echo $_REQUEST['dataDDate']?>'

                    // refreshAllocatedSectionTr(dataSchedulingPerson,dataRowId,dataDutyDate,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,'','CONTEXTMENUEDITDUTY',0,0,0,'Yes');
                    $.facebox.close();
                    reloadeditweeklygrid('','','','','ALL');
                } else {
                    let retData = $.parseJSON(data);
                    if(retData.status == false){
                        if(retData.errorType == 'HC'){
                        let reindexedArray = [];
                        for (var i in retData.compLeaveIds) {
                            reindexedArray.push(retData.compLeaveIds[i]);
                        }

                        if(reindexedArray.length > 0){
                            let errorMessage = 'You have amended some PDL amounts. However, the duration of the PDL on the date/s listed below does not match the duration of the leave you have approved. Please change either the duration of the PDL or the amount of leave you have approved.<br>';
                            let errorCounter = 0;
                            reindexedArray.forEach((number, index, rows) => {
                                let pdlUpdStartTime = $('#PDLStartTime-'+rows[index]).val();
                                let pdlUpdEndTime = $('#PDLEndTime-'+rows[index]).val();
                                let pdlUpdDur = parseFloat(convertSecondsIntoTime(parseInt(parseInt(pdlUpdEndTime) - parseInt(pdlUpdStartTime)),'.','No'));
                                let pdlDutyDate = $('#pdlTimeTd'+rows[index]).attr('data-duty-date');

                                let popUpLeaveAmtForleave = retData.popUpLeaveAmts[rows[index]];
                                let particularLeaveAmtSum = parseFloat(0);
                                if(popUpLeaveAmtForleave.length > 1){
                                    popUpLeaveAmtForleave.forEach((number1, index1, rows1) => {
                                        particularLeaveAmtSum = parseFloat(parseFloat(particularLeaveAmtSum) + parseFloat(rows1[index1]));
                                    });
                                } else {
                                    particularLeaveAmtSum = parseFloat(parseFloat(particularLeaveAmtSum) + parseFloat(popUpLeaveAmtForleave[0]));
                                }

                                if((particularLeaveAmtSum < pdlUpdDur) || (particularLeaveAmtSum > pdlUpdDur)){
                                    errorMessage +=pdlDutyDate+'<br>';
                                    errorCounter++;
                                }
                            });
                            customAlertByModelLeavePopup(errorMessage);
                            $('#facebox .close').off('click');
                            $('#submit').prop('disabled',false);
                            $('#cancelBtn').prop('disabled',true);
                        }
                    }
                }
            }
        }
        });
    }
    })
})
$(".leaveManageContainer").width($(window).width() - 40);
$('#LeaveApproveTables').css('max-height', $(window).height() - 360);
function AdminApplication (applicationid, action) {
  var callerPage = $('#callerPage').val();
  $.ajax({
    url: 'page-includes/leave/edit-weekly-leave-application-admin.php',
    type: 'POST',
    data: { id: applicationid,action: action,callerPage:callerPage,week:<?php echo $_REQUEST['week']; ?> },
    success: function (data) {
        $.facebox(data);
    }
  });
}

if (($.cookie("LeaveApproveTablesScroll") !== null) && ($.cookie("LeaveApproveTablesScroll") != '') && ($.cookie("LeaveApproveTablesScroll") != undefined)) {
      $("#LeaveApproveTables").scrollTop($.cookie("LeaveApproveTablesScroll"));
}

$("#LeaveApproveTables").on("scroll", function() {
    $.cookie("LeaveApproveTablesScroll", $("#LeaveApproveTables").scrollTop() );
});
</script>
<?php
}

?>