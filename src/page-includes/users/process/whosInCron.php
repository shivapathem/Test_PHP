<?php
include_once __DIR__ .'/../../../function-includes/DBHelper.php';
include_once __DIR__ .'/../../../function-includes/init.php';
$html1 = '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Email</title>
</head>
<style>
    body {
        box-sizing: border-box;
        font-size: 10px;
        font-family: inherit;
        font-family: Verdana, Arial, sans-serif;
    }

    .container {
        max-width: 100%;
        padding: 10px;
        position: relative;
    }

    .footer{
        font-size: 11px;
    }

    .footer img {
        margin-top: 10px;
        margin-bottom: 2px;
        cursor: pointer;
        min-width: auto;
        min-height: auto;
        max-width: 100%;
        height: 16px;
    }

    .charging-dec {
        font-size: 11px;
        line-height: 1.5;
    }

	td{
		padding: 0 20 0 0;
	}
	.btmLine{
		border-bottom:2px solid ;
	}
	.tbl{
		width:100% ;
	}
</style>';

$pdo = OpenDBLinkA7();
$sql = "exec usp_Get_WhoISInDetails";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sql1 = "select distinct sl.TeamID, 
			sp.UD_UserID ScheduledPersonID, 
			sp.UD_InternalEmail  InternalEmail, 
			sp.UD_DisplayName DisplayName 
		from  UserDetails sp
		inner join ScheduledPersonTeam_LINK sl on sl.ScheduledPersonID = sp.UD_UserID 
		where isWhosIn = 1 
		and getdate() between StartDate and isNull(EndDate, '9999-01-01')";
$stmt1 = $pdo->prepare($sql1);
$stmt1->execute();
$result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
$finalUserDetails = array();
$personIdArr = array();

foreach($result1 as $key=>$resultarr1)
{	
	if(in_array($resultarr1['ScheduledPersonID'], $personIdArr))
	{
		$finalUserDetails[$resultarr1['ScheduledPersonID']]['TeamIdArr'][] = $resultarr1['TeamID'];
	}else
	{
		$personIdArr[] = $resultarr1['ScheduledPersonID'];
		$finalUserDetails[$resultarr1['ScheduledPersonID']] = $resultarr1;
		$finalUserDetails[$resultarr1['ScheduledPersonID']]['TeamIdArr'][] = $resultarr1['TeamID'];
	}
}
if (count($finalUserDetails)>0){
	foreach($finalUserDetails as $finalUserDetailsArr)
	{
		$printflag=0;
		$html = $html1 . '<body>
		<div class="container">
			<p class="charging-dec" style="text-align:center; font-size: 20px;text-decoration: underline;">
				<b>Who&apos;s In - '.date('l, jS F').'</b>
			</p>
				<table class="tbl">';
		$teamIdArr = array();
		$htmlSick = '';
		$htmlLeave = '';
		foreach($result as $resultarr)
		{
			if((in_array($resultarr['schedulingTeamId'], $finalUserDetailsArr['TeamIdArr'])))
			{			
				if(!in_array($resultarr['schedulingTeamId'], $teamIdArr))
				{
					$printflag=1;
					$teamIdArr[] = $resultarr['schedulingTeamId'];
					$html .= $htmlLeave;
					$html .= $htmlSick;
					$html .= '<tr><td colspan="7" style="color:white;">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
					<tr><td class="btmLine" style="font-size: 18px;" colspan="7"> '. $resultarr['schedulingTeamName'] .'</td></tr>';
					$htmlSick = '';
					$htmlLeave = '';
				}
				$startTimeText = (($resultarr['StartTime'] + $resultarr['EndTime']) > 0) ? gmdate("H:i", $resultarr['StartTime']): '';
				$EndTimeText = (($resultarr['StartTime'] + $resultarr['EndTime']) > 0) ? gmdate("H:i", $resultarr['EndTime']) : '';
				$resultarr['PreviousDayDuty'] = $resultarr['PreviousDayDuty'] ? ' Previous day '.$resultarr['PreviousDayDuty']: '';
				$resultarr['DutyName'] = (($resultarr['LeaveStartTime'] + $resultarr['LeaveEndTime']) > 0) ? $resultarr['DutyName'] . '<br/>(Part Day Leave ' . gmdate("H:i", $resultarr['LeaveStartTime']).' - '.gmdate("H:i", $resultarr['LeaveEndTime']) . ')' : $resultarr['DutyName'];
					$verticalAlignCss = (($resultarr['LeaveStartTime'] + $resultarr['LeaveEndTime']) > 0) ? 'vertical-align: top;' : '';
				if(strpos(strtolower($resultarr['DutyName']), 'sick') !== false)
				{
					$htmlSick .= '
						<tr style="vertical-align:top;">
							<td style="width:30%; '.$verticalAlignCss.'">'.$resultarr['DisplayFirstName'].' '.$resultarr['DisplayLastName'].'  </td>
							<td style="width:10%;white-space:nowrap; '.$verticalAlignCss.'">'.$startTimeText.'  </td>
							<td style="width:10%;white-space:nowrap; '.$verticalAlignCss.'">'.$EndTimeText.'  </td>
							<td style="width:35%; '.$verticalAlignCss.'">'.$resultarr['DutyName'].'  </td>
							<td style="font-style:italic;width:15%; '.$verticalAlignCss.'" >'.$resultarr['PreviousDayDuty'].'  </td>
						</tr>';
				}elseif((strpos(strtolower($resultarr['DutyName']), 'leave') !== false) && (empty($startTimeText)))
				{
					$htmlLeave .= '
						<tr style="vertical-align:top;">
							<td style="width:30%; '.$verticalAlignCss.'">'.$resultarr['DisplayFirstName'].' '.$resultarr['DisplayLastName'].'  </td>
							<td style="width:10%;white-space:nowrap; '.$verticalAlignCss.'">'.$startTimeText.'  </td>
							<td style="width:10%;white-space:nowrap; '.$verticalAlignCss.'">'.$EndTimeText.'  </td>
							<td style="width:35%; '.$verticalAlignCss.'">'.$resultarr['DutyName'].'  </td>
							<td style="font-style:italic;width:15%; '.$verticalAlignCss.'" >'.$resultarr['PreviousDayDuty'].'  </td>
						</tr>';
				}else {
					$html .= '
						<tr style="vertical-align:top;">
							<td style="width:30%; '.$verticalAlignCss.'">'.$resultarr['DisplayFirstName'].' '.$resultarr['DisplayLastName'].'  </td>
							<td style="width:10%;white-space:nowrap; '.$verticalAlignCss.'">'.$startTimeText.'  </td>
							<td style="width:10%;white-space:nowrap; '.$verticalAlignCss.'">'.$EndTimeText.'  </td>
							<td style="width:35%; '.$verticalAlignCss.'">'.$resultarr['DutyName'].'  </td>
							<td style="font-style:italic;width:15%; '.$verticalAlignCss.'" >'.$resultarr['PreviousDayDuty'].'  </td>
						</tr>';
			   }
			}
			
		}
		$html .= $htmlLeave;
		$html .= $htmlSick;
		$html .= '
				</table>
				<br/>
				<br/><br/><br/>
						<div class="footer" align="center">
							<img src="'.getenv('BASE_URL').'images/BBC.png" alt="BBC">
							<br>
							<span>
								<font size="1">BBC '.romanNumerals(date("Y")).'<font>
							</span>
						</div>
					</div>
				</body>
				</html>';
				if ($printflag==1){
					sendDataPostmaster($html, $finalUserDetailsArr['InternalEmail']);
				}
	}
}

/* This function will add data in table which is linked with postmaster to send an email.*/

function sendDataPostmaster($html, $userEmail)
{
	$pdo = OpenDBLinkA7();
	$mailCC = '';
	$mailBCC = getenv('EMAIL_BCC');
	$mailFrom = 'Allocate<noreply@bbc.co.uk>';
	$emailSubjectPrefix = getenv('EMAIL_SUFFIX');
    $emailSubjectPrefix = str_replace(':',' ',$emailSubjectPrefix);
	$mailSubject = " Who's In - ".date('l, jS F');
	$mailMessage = '<style>'.file_get_contents(getenv('EMAIL_CSS')).'</style>
	<body><img alt="Banner" src="'.getenv('BASE_URL').'images/po_banner.png" style="width:100%;" /><br><br>'.$html;
	$createdBy='Allocate7';
	$lastModBy='Allocate7 Email Send';
	$createdDate=date('Y-m-d H:i:s.v');
	$history="Allocate7 ready to send Who's In email by ".$lastModBy." to ".$userEmail."  on ".$createdDate;
	
	try{
			$sql = "INSERT INTO SendAllocateEmails (AllocateInstanceID,AllocateInstanceName,DepartmentID,StaffID,AccPeriodID,AccountID,
			PayrollMonth,BBCWeek,TimesheetSentID,PeriodStarts,PeriodEnds,MailTo,MailCC,MailBCC, MailFrom,MailSubject,
			MailMessage, MailSendSuccess,MailSendError, MailSendRetries,CreatedBy,CreatedDate,LastModBy,LastModDate,History) values (5,'".$emailSubjectPrefix."',0,0,0,0,0,0,0,0,0,'".$userEmail."','".$mailCC."' ,'".$mailBCC."','".$mailFrom."', :mailSubject, :mailMessage,0,'',0,'".$createdBy."','".$createdDate."','".$lastModBy."','".$createdDate."',:history)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':mailMessage', $mailMessage, PDO::PARAM_STR);
			$stmt->bindParam(':mailSubject', $mailSubject);
			$stmt->bindParam(':history', $history);
			$stmt->execute();
			logger()->INFO("Who's In email for user ".$userEmail." added into postmaster ");
	}
	catch(PDOException $e){
		logger()->ERROR("Who's In email for user ".$userEmail." not added into postmaster due to ".$e->getMessage().".");
	}
}
echo 'Task completed.';