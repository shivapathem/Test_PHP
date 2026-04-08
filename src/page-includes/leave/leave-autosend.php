<?php
include_once __DIR__ . '/../../function-includes/init.php';
include_once __DIR__ . '/../../function-includes/genericfunctions.php';
include_once __DIR__ . '/../../function-includes/userfunctions.php';
include_once __DIR__ . '/../../function-includes/leavefunctions.php';
include_once __DIR__.'/../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService();

$leaveData = GetApprovedLeavsEmailToSendUser();

$groupedLeaves = array();
foreach ($leaveData as $leave) {
    $login = $leave['Login'];
    if (!isset($groupedLeaves[$login])) {
        $groupedLeaves[$login] = array();
    }
    $groupedLeaves[$login][] = $leave;
}

foreach ($groupedLeaves as $login => $leaves) {
    $leaveapplicationsIDS = array();
    $emailfrom = $leaves[0]['EmailFrom'];
    $emailcopiesto = $leaves[0]['emailcopiesto'];
    $groupid = $leaves[0]['GroupID'];
    $strFullName = $leaves[0]['FullName'];
    if (is_null($leaves[0]["UserEmail"])) {
        $arrUser = bbc_GetFromLDAPFull($login);
        $email = $arrUser["email"];
    } else {
        $email = $leaves[0]["UserEmail"];
    }

    $uniqueLeaveCategories = array();
    $accumulatedLeaveCategoryAmount = array();
    $lastRowByLeaveCategory = array();

    foreach ($leaves as $row) {
        $leaveapplicationsIDS[] = $row['ID'];

        if (!isset($uniqueLeaveCategories[$row['ID']])) {
            $uniqueLeaveCategories[$row['ID']] = array();
        }
        $uniqueLeaveCategories[$row['ID']][] = $row['LeaveCategory'];
        $uniqueLeaveCategories[$row['ID']] = array_unique($uniqueLeaveCategories[$row['ID']]);
        $leaveCategoryText = implode(', ', $uniqueLeaveCategories[$row['ID']]);

        if (!isset($accumulatedLeaveCategoryAmount[$row['ID']])) {
            $accumulatedLeaveCategoryAmount[$row['ID']] = 0;
        }
        $accumulatedLeaveCategoryAmount[$row['ID']] += $row['LeaveCategoryAmount'];

        if ($accumulatedLeaveCategoryAmount[$row['ID']] == 0) {
            $leaveCategoryText .= " (OFF Leave)";
        }

        $pdlStrtTime = $row['LeaveStartTime'];
        $pdlEndTime = $row['LeaveEndTime'];

        $lastRowByLeaveCategory[$row['ID']] = array(
            'Date' => date('l, jS F Y', strtotime($row['dDate'])),
            'GroupDescription' => $row['GroupDescription'],
            'TypeDescription' => $row['TypeDescription'],
            'LeaveCategory' => $leaveCategoryText,
            'Comments' => nl2br($row['Comments'] ?? ''),
            'OfficeComments' => nl2br($row['OfficeComments'] ?? ''),
            'Time' => ($pdlStrtTime != '' && $pdlEndTime != '') ? $service->convertSecondsIntoTime($pdlStrtTime, ':', 'No') . ' - ' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') : '',
            'Duration' => $accumulatedLeaveCategoryAmount[$row['ID']],
        );
    }

    $html = '<style>' . (@file_get_contents(getenv('EMAIL_CSS')) ?? '') . '</style>';
    $html .= '<body>';
    $html .= '<img alt="Banner" src="'.getenv('BASE_URL').'images/po_banner.png" /><br><br>';
    $html .= '<h2>This is an update to Leave you have requested<br>The dates below have been approved:</h2>';
    $html .= '<table style="width:100%;!important">';
    $html .= '<tr>';
    $html .= '<td width="200px" class="datecell">Date</td>';
    $html .= '<td width="200px" class="datecell">Leave Group</td>';
    $html .= '<td width="200px" class="datecell">Leave Type</td>';
    $html .= '<td width="200px" class="datecell">Leave Category</td>';
    $html .= '<td width="200px" class="datecell">Duration</td>';
    $html .= '<td width="200px" class="datecell">Comments</td>';
    $html .= '<td width="200px" class="datecell">Office Comments</td>';
    $html .= '<td width="200px" class="datecell">Time</td>';
    $html .= '</tr>';

    foreach ($lastRowByLeaveCategory as $row) {
        $html .= '<tr>';
        $html .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['Date'] . '</td>';
        $html .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['GroupDescription'] . '</td>';
        $html .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['TypeDescription'] . '</td>';
        $html .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['LeaveCategory'] . '</td>';
        $html .= '<td class="lightcell" style="padding-right:unset !important;">' . ($row['Duration'] == 0 ? "0" : $row['Duration']) . '</td>';
        $html .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['Comments'] . '</td>';
        $html .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['OfficeComments'] . '</td>';
        $html .= '<td class="lightcell" style="padding-right:unset !important;">' . $row['Time'] . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';
    $html .= '<br><br><br><br><p align="center"><img alt="BBC" src="'.getenv('BASE_URL').'images/BBC.png" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '</font></p>';
    $html .= '</body></html>';
	
	if (isset($emailcopiesto) && $emailcopiesto != '') {
        $ccEmail = $emailfrom . ',' . $emailcopiesto;
    } else {
        $ccEmail = $emailfrom;
    }
    sendDataPostmaster($html, $email, $strFullName,$ccEmail);
    $leaveemailsend = ModEmailsToSend();
}

/* This function will add data in table which is linked with postmaster to send an email.*/

function sendDataPostmaster($html, $userEmail,$strFullName,$ccEmail)
{
	$pdo = OpenDBLinkA7();
	$mailCC = $ccEmail;
	$mailBCC = getenv('EMAIL_BCC');
	$mailFrom = 'Allocate<noreply@bbc.co.uk>';
	$mailSubject = ' '.$strFullName . ' -  Approval of Leave Request';
	$mailMessage = $html;
    $emailSubjectPrefix = getenv('EMAIL_SUFFIX');
    $emailSubjectPrefix = str_replace(':',' ',$emailSubjectPrefix);
	$createdBy='Allocate7';
	$lastModBy='Allocate7 Email Send';
	$createdDate=date('Y-m-d H:i:s.v');
	$history="Allocate7 ready to send approval of leave request email  by ".$lastModBy." to ".$userEmail."  on ".$createdDate;
	try{
			$sql = "INSERT INTO SendAllocateEmails (AllocateInstanceID,AllocateInstanceName,DepartmentID,StaffID,AccPeriodID,AccountID,
			PayrollMonth,BBCWeek,TimesheetSentID,PeriodStarts,PeriodEnds,MailTo,MailCC,MailBCC, MailFrom,MailSubject,
			MailMessage, MailSendSuccess,MailSendError, MailSendRetries,CreatedBy,CreatedDate,LastModBy,LastModDate,History) values (5,'".$emailSubjectPrefix."',0,0,0,0,0,0,0,0,0,'".$userEmail."','".$mailCC."' ,'".$mailBCC."','".$mailFrom."', :mailSubject, :mailMessage,0,'',0,'".$createdBy."','".$createdDate."','".$lastModBy."','".$createdDate."',:history)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':mailMessage', $mailMessage, PDO::PARAM_STR);
			$stmt->bindParam(':mailSubject', $mailSubject);
			$stmt->bindParam(':history', $history);
			$stmt->execute();
			logger()->INFO("Autosend email for user ".$userEmail." added into postmaster.");
	}
	catch(PDOException $e){
		logger()->ERROR("Autosend email for user ".$userEmail." not added into postmaster due to ".$e->getMessage().".");
	}
}
echo 'Task completed.';
?>
