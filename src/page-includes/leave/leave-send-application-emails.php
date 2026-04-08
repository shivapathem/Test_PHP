<?php
session_start();
set_time_limit(0);
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';

$strLogin = $_REQUEST['login'];
$dteToday = date("Y-m-d");
$strFullName = GetFullNameFromLogin($strLogin);

$pdo = OpenDBLinkA7();
try {
	$strQuery = "exec [dbo].[usp_get_LeaveSendEmails] ?";
	$stmt = $pdo->prepare($strQuery);
	$stmt->bindParam(1, $strLogin, PDO::PARAM_STR);
	$stmt->execute();
	$leave = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$bodyInfo = '';
	$strUserEmail = GetEmailFromLogin($strLogin);

	$arrLeave['UserEmail'] = $strUserEmail;
	$arrLeave['UserName'] = $strFullName;
	foreach ($leave as $row) {
		$arrLeave['Requests'][$row['GroupID']]['GroupDescription'] = $row['GroupDescription'];
		$arrLeave['Requests'][$row['GroupID']]['SendTo'] = $row['email'];
		if ($row['emailcopiesto'] != '') {
			$arrCCMail = explode(",", $row['emailcopiesto']);
			foreach ($arrCCMail as $intArrayItem => $strCCEmail) {
				$arrLeave['Requests'][$row['GroupID']]['SendToCC'][$intArrayItem] = $strCCEmail;
			}
		}
		$strDate = date('Y-m-d', strtotime($row['dDate']));
		$intDeleted = $row['Deleted'];
		$arrLeave['Requests'][$row['GroupID']][$intDeleted]['Dates'][$strDate]['Type'] = $row['TypeDscription'];
		$arrLeave['Requests'][$row['GroupID']][$intDeleted]['Dates'][$strDate]['ShortNotice'] = $row['ShortNotice'];
		$arrLeave['Requests'][$row['GroupID']][$intDeleted]['Dates'][$strDate]['isOK'] = $row['isOK'];
		$arrLeave['Requests'][$row['GroupID']][$intDeleted]['Dates'][$strDate]['Comments'] = $row['Comments'];
	}
	// The requests that have been entered
	$bodyInfo .= '<table style="width:1000px;">';
	$bodyInfo .= '<tr>';
	$bodyInfo .= '<th colspan="4" class="darkred">';
	$bodyInfo .= '<img alt="Banner" src="cid:pobanner" />';
	$bodyInfo .= '</th>';
	$bodyInfo .= '</tr>';
	foreach ($arrLeave['Requests'] as $intGroupID => $arrGroupRequests) {

		$bodyInfo .= '<tr>';
		$bodyInfo .= '<th colspan=4>';
		$bodyInfo .= $strFullName . ' has requested or deleted leave on the following dates';
		$bodyInfo .= '</th>';
		$bodyInfo .= '</tr>';
		$bodyInfo .= '<tr>';
		$bodyInfo .= '<td colspan=4 style="text-align:center">';
		$bodyInfo .= 'The Leave Group for these requests is &lsquo;' . $arrGroupRequests['GroupDescription'] . '&rsquo;';
		$bodyInfo .= '</td>';
		$bodyInfo .= '</tr>';

		// The requests
		if (isset($arrGroupRequests['0'])) {

			$bodyInfo .= '<tr>';
			$bodyInfo .= '<td colspan="4">';
			$bodyInfo .= '&nbsp;';
			$bodyInfo .= '</td>';
			$bodyInfo .= '</tr>';

			$bodyInfo .= '<tr>';
			$bodyInfo .= '<th colspan=4>';
			$bodyInfo .= 'Requests for the following dates require your approval';
			$bodyInfo .= '</th>';
			$bodyInfo .= '</tr>';
			$bodyInfo .= '<tr>';
			$bodyInfo .= '<th style="width:250px;">Date</th>';
			$bodyInfo .= '<th style="width:120px;">Leave Type</th>';
			$bodyInfo .= '<th style="width:120px;">Status</th>';
			$bodyInfo .= '<th>Comments</th>';
			$bodyInfo .= '</tr>';

			foreach ($arrGroupRequests['0']['Dates'] as $strDate => $arrRequest) {
				$bodyInfo .= '<tr>';
				$bodyInfo .= '<td>' . date('l, jS F Y', strtotime($strDate)) . '</td>';
				$bodyInfo .= '<td>' . $arrRequest['Type'] . '</td>';
				$bodyInfo .= '<td>';
				if ($arrRequest['isOK'] == 1) {
					$bodyInfo .= 'Is OK';
				} else {
					$bodyInfo .= 'In Waiting List';
				}
				$bodyInfo .= '</td>';
				$bodyInfo .= '<td>';
				$bodyInfo .= $arrRequest['Comments'];
				$bodyInfo .= '</td>';
				$bodyInfo .= '</tr>';
			}
		}

		// the deletions
		if (isset($arrGroupRequests['1'])) {


			$bodyInfo .= '<tr>';
			$bodyInfo .= '<td colspan="4">';
			$bodyInfo .= '&nbsp;';
			$bodyInfo .= '</td>';
			$bodyInfo .= '</tr>';

			$bodyInfo .= '<tr>';
			$bodyInfo .= '<th colspan=4>';
			$bodyInfo .= 'Deleted requests which have been previously been sent to you for approval';
			$bodyInfo .= '</th>';
			$bodyInfo .= '</tr>';
			$bodyInfo .= '<tr>';
			$bodyInfo .= '<th style="width:250px;">Date</th>';
			$bodyInfo .= '<th style="width:120px;">Leave Type</th>';
			$bodyInfo .= '<th style="width:120px;">Status</th>';
			$bodyInfo .= '<th>Comments</th>';
			$bodyInfo .= '</tr>';

			foreach ($arrGroupRequests['1']['Dates'] as $strDate => $arrRequest) {
				$bodyInfo .= '<tr>';
				$bodyInfo .= '<td>' . date('l, jS F Y', strtotime($strDate)) . '</td>';
				$bodyInfo .= '<td>' . $arrRequest['Type'] . '</td>';
				$bodyInfo .= '<td>';
				if ($arrRequest['isOK'] == 1) {
					$bodyInfo .= 'Is OK';
				} else {
					$bodyInfo .= 'In Waiting List';
				}
				$bodyInfo .= '</td>';
				$bodyInfo .= '<td>';
				$bodyInfo .= $arrRequest['Comments'];
				$bodyInfo .= '</td>';
				$bodyInfo .= '</tr>';
			}
		}
		$bodyInfo .= '<tr>';
		$bodyInfo .= '<td colspan="4">';
		$bodyInfo .= '&nbsp;';
		$bodyInfo .= '</td>';
		$bodyInfo .= '</tr>';
		$bodyInfo .= '<tr>';
		$bodyInfo .= '<td colspan="4">';
		$bodyInfo .= '&nbsp;';
		$bodyInfo .= '</td>';
		$bodyInfo .= '</tr>';
		$bodyInfo .= '<tr>';
		$bodyInfo .= '<td colspan="4">';
		$bodyInfo .= '&nbsp;';
		$bodyInfo .= '</td>';
		$bodyInfo .= '</tr>';

		$mail = new PHPMailer\PHPMailer\PHPMailer();
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->setFrom($arrGroupRequests['SendTo'], 'Allocate');
		$mail->Host = getenv('SMTP_HOST');
		$mail->Port = 25;

		$mail->Subject = getenv('EMAIL_SUFFIX') . $strFullName . ' -  Leave Requests needing approval';

		$mail->addAddress($arrGroupRequests['SendTo']);
		if (isset($arrGroupRequests['SendToCC'])) {
			foreach ($arrGroupRequests['SendToCC'] as $strCCTo) {
				$mail->AddCC($strCCTo);

			}
		}
		$mail->AddCC($strUserEmail);
		$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
		$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

		$html = '<style>' . (@file_get_contents(getenv('EMAIL_CSS')) ?? '') . '</style>
         <body>';

		$html .= $bodyInfo;
		$html .= '<tr>';
		$html .= '<td colspan=4 style="text-align:center">';
		$html .= '<a href="' . getenv('BASE_URL') . '">Click here to access Allocate</a>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
		$html .= '</body></html>';

		$mail->msgHTML($html);

		if (!$mail->send()) {
			//echo "Mailer Error: " . $mail->ErrorInfo; exit;
		} else {
			// echo "Message sent!";

			$query = "UPDATE       LeaveApplications
				SET          UserEmailSent = 1
				FROM         LeaveApplications 
				INNER JOIN   leave_types ON LeaveApplications.LeaveTypesID = leave_types.ID 
				INNER JOIN   LeaveRequestGroups ON leave_types.GroupID = LeaveRequestGroups.ID
				WHERE        (LeaveApplications.UserEmailSent = 0) 
				  AND        (LeaveApplications.Approved = 0) 
				  AND        (LeaveApplications.Login = '$strLogin') 
				  AND        (LeaveApplications.Deleted = 0) 
				  AND        (LeaveRequestGroups.ID = $intGroupID) 
				OR           (LeaveApplications.UserEmailSent = 2) 
				  AND        (LeaveApplications.Approved = 0) 
				  AND        (LeaveApplications.Login = N'$strLogin') 
				  AND        (LeaveRequestGroups.ID = $intGroupID)";

			$stmt = $pdo->prepare($query);
			$stmt->execute();

		}
	}
} catch (PDOException $e) {
	logger()->critical('db error', (array) $e);
	echo $e->getMessage();
}