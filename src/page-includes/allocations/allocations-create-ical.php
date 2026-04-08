<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
require_once("../../function-includes/zapcallib.php");
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = GetUserIdbyNetlogin($strUser);

if (isset($_REQUEST["schedulingPersonId"])) {

  $schedulingPersonId = $_REQUEST["schedulingPersonId"];
} else {

  $schedulingPersonId = GetScheduledPersonIdbyUserId($UserID);
}

$arrUser = GetScheduledPersonTeamDetails($schedulingPersonId);

if (isset($_REQUEST["teamId"])) {
  $schedulingTeamId = $_REQUEST["teamId"];
}
else {
  if(!empty($arrUser['defaultSchedulingTeamId']))
    $schedulingTeamId   = $arrUser['defaultSchedulingTeamId'];
  else
    $schedulingTeamId = key($arrUser);
}

$arrTeamDefaults = GetTeamDefaults(0,$schedulingTeamId);
// This persons login
$strUserLogin = $arrUser[$schedulingTeamId]['Login'];
$strFullName = $arrUser[$schedulingTeamId]['FullName'];
$arrStaffOptions = GetStaffOtionsByTeam ($strUser);

$isShiftleader = 1;
if ((isset($arrStaffOptions[$schedulingTeamId]['isScheduler']) && $arrStaffOptions[$schedulingTeamId]['isScheduler'] == 1) || (isset($arrStaffOptions[$schedulingTeamId]['isTeamAdmin']) && $arrStaffOptions[$schedulingTeamId]['isTeamAdmin'] == 1) || (isset($arrStaffOptions[$schedulingTeamId]['isManager']) && $arrStaffOptions[$schedulingTeamId]['isManager'] == 1)) {
  $isShiftleader = 0;
}

$pdo = OpenDBLinkA7();
$sql = "SELECT  ud.UD_ExternalEmail as PersonalEmail,ud.UD_InternalEmail as BBCEmail,ud.UD_DisplayName as FullName
FROM ScheduledPersonTeam_LINK (NOLOCK) spt
INNER JOIN UserDetails (NOLOCK) ud ON spt.ScheduledPersonID = ud.UD_UserID  
AND isnull(spt.StartDate,'9999-01-01') <= CONVERT(date,getdate(),102)
WHERE ud.UD_UserID = $schedulingPersonId
AND spt.TeamID = $schedulingTeamId AND spt.IsHomeTeam = 1 AND spt.scheduledType = 1";

$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1, $schedulingTeamId, PDO::PARAM_INT);
$stmt->bindParam(2, $schedulingPersonId, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$strFullName = $row['FullName'];
$strEmail = $row['PersonalEmail'];
$BBCEmail = $row['BBCEmail'];

if (($strEmail == '')&&($BBCEmail == '')) {
  echo '<table width="600px" class="tablesmalltidy">';
  echo '<tr height="70px">';
  echo '<th>';
  echo 'We do not have an email address on file.<br>It has not been possible send you your Allocations<br>Please contact your Allocate administrator to correct this.';
  echo '</th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td align="center"><input type="button" value="OK" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
} else {

	// Override the default?
	if (isset($_REQUEST['date'])) {
	  $dateisvalid = validateDate($_REQUEST['date']);
	  if ($dateisvalid == true) {
		$strStartDate = $_REQUEST['date'];
	  }
	}
	
	$currentweek = bbcweeknumber(date("Y-m-d"));
	$strStartDate = date('Y-m-01', strtotime($strStartDate));
	$nextmonth = date('Y-m-d', strtotime($strStartDate. ' +1 month'));
	$lasttmonth = date('Y-m-d', strtotime($strStartDate. '-1 month'));
	$strEndDate = date('Y-m-d', strtotime($nextmonth. '+4 days'));
	$tdate = $strStartDate;

	while (strtotime($tdate) <= strtotime($strEndDate)) {
	  $arrweeks[] = bbcweeknumber($tdate);
	  $tdate = date ("Y-m-d", strtotime("+1 week", strtotime($tdate)));
	}

	$arrAllocations = ReadAllocationsAndJobsIndividual($arrweeks[0], $arrweeks[count($arrweeks) - 1], $schedulingPersonId, $arrTeamDefaults,$strUser,$isShiftleader);
	// create the ical object
	$icalobj = new ZCiCal();
	if (isset($arrAllocations)){	
		foreach ($arrweeks as $currweek) {
		  for ($i=0; $i <= 6; $i++) {
			$currdate = datefromweek($currweek, $i);
			$nextday = date("Ymd", strtotime("+ 1 day", strtotime($currdate)));
			foreach ($arrAllocations as $intDepID => $arrDepAllocations) { 
			  if (isset($arrDepAllocations['Weeks'][$currweek][$i]["HiddenDays"]) && !empty($arrDepAllocations['Weeks'][$currweek][$i]["HiddenDays"]))  {
				continue;
			  }   
			  if (isset($arrDepAllocations['Weeks'][$currweek][$i]["StartTime"])) {
				$event_start = $currdate.' '.$arrDepAllocations['Weeks'][$currweek][$i]["StartTime"];
				$unixstarttime = strtotime($arrDepAllocations['Weeks'][$currweek][$i]["StartTime"]);
				$unixendtime = strtotime($arrDepAllocations['Weeks'][$currweek][$i]["EndTime"]);
				
				if ($unixendtime < $unixstarttime) {
				  $event_end = date("Y-m-d", strtotime("+1 day", strtotime($currdate)));
				}
				else {
				  $event_end = $currdate;
				}
				$event_end = $event_end.' '.$arrDepAllocations['Weeks'][$currweek][$i]["EndTime"];
			  }
			  else {
				$event_start = $currdate.' 00:00:00';
				$event_end = $currdate.' 00:00:00';
			  }

			  // create the event within the ical object
			  $eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);
			  if (isset($arrDepAllocations['Weeks'][$currweek][$i]["Duty"])) {
				$title = strip_tags($arrDepAllocations['Weeks'][$currweek][$i]["Duty"]);
			  }
			  else {
				$title = "Unknown";
			  }
			   $title.= ' ('.$arrTeamDefaults[$intDepID]['Description'].')'; 
			  // add title
			  $eventobj->addNode(new ZCiCalDataNode("SUMMARY:" . $title));
			  // add start date
			  $eventobj->addNode(new ZCiCalDataNode("DTSTART:" . ZCiCal::fromSqlDateTime($event_start)));
			  // add end date
			  $eventobj->addNode(new ZCiCalDataNode("DTEND:" . ZCiCal::fromSqlDateTime($event_end)));
			  $strJobs = $title.'<br><br>';
			  $eventobj->addNode(new ZCiCalDataNode("DESCRIPTION:".$strJobs));
			}
		  }
		}
	
	// UID is a required item in VEVENT, create unique string for this event
	// Adding your domain to the end is a good way of creating uniqueness
	$uid = $strUser;
	$eventobj->addNode(new ZCiCalDataNode("UID:" . $uid));

	// DTSTAMP is a required item in VEVENT
	$eventobj->addNode(new ZCiCalDataNode("DTSTAMP:" . ZCiCal::fromSqlDateTime()));

	// Add description
	$eventobj->addNode(new ZCiCalDataNode("Description:" . ZCiCal::formatContent(
	"Allocations")));
	}
	if ($strEmail<>""){
		$toEmail=$strEmail;
	} else {
		$toEmail=$BBCEmail;
	}	
	// write iCalendar feed to stdout
	$output = $icalobj->export();
	echo '<table width="600px" class="tablesmalltidy">';
	echo '<tr height="70px">';
	echo '<th>';
	echo 'Your request for a calendar file has been completed.<br>The file has been sent by email to<br>'.$toEmail;
	echo '</th>';
	echo '</tr>';
	echo '<tr>';
	echo '<td align="center"><input type="button" value="OK" onclick="cancel()"></td>';
	echo '</tr>';
	echo '</table>';

	//$filename = "/var/www/allocations/ical/Rota for $strFullName.ics";
	$filename = getenv('ICS_DIR')."/Rota for $strFullName.ics";
	
	if (!file_exists($filename)) {
		touch($filename);
	}

	if (file_exists($filename)) {
		file_put_contents($filename, $output);
	}	

	// Now send the email
	$mail = new PHPMailer\PHPMailer\PHPMailer();

	$mail->isSMTP();
	$mail->SMTPDebug = 0;
	$mail->Host = getenv('SMTP_HOST');
	$mail->Port = 25;

	$mail->setFrom('noreply@bbc.co.uk', 'Allocate');

	$mailAdd = getenv('EMAIL_BCC');
	$arrMailAdd = explode(",", $mailAdd);
	$mail->addAddress($toEmail);
	$mail->Subject = getenv('EMAIL_SUFFIX').$strFullName.' - Your Rota for '.date("F", strtotime($strStartDate)).' '.date("Y", strtotime($strStartDate));
	
	$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
	$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
	$mail->addAttachment($filename);

		$html='<style>'.file_get_contents(getenv('EMAIL_CSS')).'</style>
			   <body>
			   <img src="cid:pobanner" /><br><br>';

		$html.= '<h2>Your Rota '.$strFullName.'</h2>
				 <p>You have requested you rota<br>
				 This is attached as an iCal file.</p>
				 <p>You can import this file into Outlook or your mailbox on your iPhone.</p>';
		$html.='<br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
		$html.='</body></html>';

		 $mail->msgHTML($html);
		 $mail->send();
}
?>