<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService();

$strUser = GetUserLogon();
date_default_timezone_set("Europe/London");
$id = $_REQUEST['id'];
$pdlStartTime = '';
$isPDLAllow = isset($_POST['isPDLAllow']) ? $_POST['isPDLAllow'] : 0;
$currentuserid = isset($_COOKIE['editWeeklyUserId']) ? $_COOKIE['editWeeklyUserId'] : $_SESSION['user']['UserID'];
$username = isset($_COOKIE['editWeeklyUserFullName']) ? $_COOKIE['editWeeklyUserFullName'] : $_SESSION['user']['FullName'];

$pdo = OpenDBLinkA7();

try {
    $strQuery = "exec [dbo].[usp_get_LeaveDetailsforComments] ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    logger()->critical('DB Error', (array) $e);
}
$pdlStartTimeHis = $service->convertSecondsIntoTime($row['LeaveStartTime'], ':', 'No');
$pdlEndTimeHis = $service->convertSecondsIntoTime($row['LeaveEndTime'], ':', 'No');
$UseroldComments = $row['Comments'];

if (isset($_POST['doupdate'])) {
    try {

        $strQuery = "exec [dbo].[usp_get_LeaveApplicationsDetails] ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logger()->critical('DB Error', (array) $e);
    }
    $strDate = date("Y-m-d", strtotime($row['dDate']));
    if (is_null($row["InternalEmail"])) {
        $arrUser = bbc_GetFromLDAPFull($row['Login']);
        $email = $arrUser["email"];
    } else {
        $email = $row["InternalEmail"];
    }
    $strFullName = $row["FullName"];
    $txtleavegroup = $row["GroupDesc"];
    $txtleavetype = $row["TypeDesc"];
    $emailfrom = $row["emailfrom"];
    $emailcopiesto = $row["emailcopiesto"];
    $ShortNotice = $row['ShortNotice'];
    if ($emailcopiesto != '') {
        $ccEmail = $emailfrom . ',' . $emailcopiesto;
    } else {
        $ccEmail = $emailfrom;
    }

    if (isset($_POST['officecomments'])) {
        $comments = $_POST['officecomments'];
        if ($comments == '') {
            $comments = null;
            $strQuery = "UPDATE LeaveApplications
                SET OfficeComments = null,LastModDate=getutcdate(),LastModBy=$currentuserid
                WHERE (ID = $id)";
        } else {
            $commentsesc = ms_escape_string($comments);
            $strQuery = "UPDATE LeaveApplications
                SET OfficeComments = N'$commentsesc',UserEmailSent = $ShortNotice, LastModDate=getutcdate(),LastModBy=$currentuserid
                WHERE (ID = $id)";
        }

        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
    }
    /////////////////////////////////////////////////////////////////////////////////////
    $commentflag = 0;
    if (isset($_POST['usercomments'])) {
        $comments = $_POST['usercomments'];
        $UseroldComments = $UseroldComments ?? '';
        $comments = $comments ?? '';
        if (strtolower($UseroldComments) != strtolower($comments)) {
            $commentflag = 1;
        }
        if ($comments == '') {
            $comments = null;
            $strQuery = "UPDATE LeaveApplications
                SET Comments = null,LastModDate=getutcdate(),LastModBy=$currentuserid
                WHERE (ID = $id)";
        } else {
            $comments = ms_escape_string($comments);
            $strQuery = "UPDATE LeaveApplications
                SET Comments = N'$comments',UserEmailSent = $ShortNotice,LastModDate=getutcdate(),LastModBy=$currentuserid
                WHERE (ID = $id)";
        }

        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
    }
    if ((isset($_POST['pdlAllow'])) && ($_POST['pdlAllow'] == 1)) {
        if ($commentflag == 1) {
            $attributeid = $id;
            $historytype = '15';
            $userid = $currentuserid;
            $message = 'Part day of leave user comments updated by ' . $username . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
            $status = 1;
            $historysubtype = 'NULL';
            PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);

            $attributeid = $_POST['allocationid'];
            $historytype = '8';
            $userid = $currentuserid;
            $message = 'Part day of leave user comments updated by ' . $username . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
            $status = 1;
            $historysubtype = 'PH';
            PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);
        }

        $baseDate = DateTime::createFromFormat('Y-m-d', $_POST['dDate']);
        $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $baseDate->format('Y-m-d') . ' 00:00:00');
        $endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $baseDate->format('Y-m-d') . ' 00:00:00');
        $startDateTime->modify("+{$_POST['pdlStartTimeInSec']} seconds");
        $endDateTime->modify("+{$_POST['pdlEndTimeInSec']} seconds");
        $LeaveStartDateTime = $startDateTime->format('Y-m-d H:i');
        $LeaveEndDateTime = $endDateTime->format('Y-m-d H:i');

        $leaveStartTime = isset($_POST['pdlStartTimeInSec']) ? (int) $_POST['pdlStartTimeInSec'] : null;
        $leaveEndTime = isset($_POST['pdlEndTimeInSec']) ? (int) $_POST['pdlEndTimeInSec'] : null;
        $strQuery = "UPDATE LeaveApplications SET LeaveStartTime = '" . $leaveStartTime . "', LeaveEndTime = '" . $leaveEndTime . "',UserEmailSent = $ShortNotice,LastModBy=$currentuserid, LastModDate=getutcdate(),LeaveStartDateTime='" . $LeaveStartDateTime . "', LeaveEndDateTime='" . $LeaveEndDateTime . "' WHERE (ID = $id)";
        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();

        $pdlEditStartTimeHis = $service->convertSecondsIntoTime($_POST['pdlStartTimeInSec'], ':', 'No');
        $pdlEditEndTimeHis = $service->convertSecondsIntoTime($_POST['pdlEndTimeInSec'], ':', 'No');

        if (($row['LeaveStartTime'] == '') &&  ($row['LeaveStartTime'] == '') && ($_POST['pdlStartTimeInSec'] != '') && ($_POST['pdlEndTimeInSec'] != '')) {
            $attributeid = $id;
            $historytype = '15';
            $userid = $currentuserid;
            $message = 'Part day of leave requested by ' . $username . ' from ' . $pdlEditStartTimeHis . ' to ' . $pdlEditEndTimeHis . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
            $status = 1;
            $historysubtype = 'NULL';
            PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);

            $attributeid = $_POST['allocationid'];
            $historytype = '8';
            $userid = $currentuserid;
            $message = 'Part day of leave requested by ' . $username . ' from ' . $pdlEditStartTimeHis . ' to ' . $pdlEditEndTimeHis . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
            $status = 1;
            $historysubtype = 'PH';
            PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);
        } else {
            if (($pdlEditStartTimeHis != $pdlStartTimeHis) && ($pdlEditEndTimeHis == $pdlEndTimeHis)) {
                $attributeid = $id;
                $historytype = '15';
                $userid = $currentuserid;
                $message = 'Part day of leave start time amended by ' . $username . ' from ' . $pdlStartTimeHis . ' to ' . $pdlEditStartTimeHis . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                $status = 1;
                $historysubtype = 'NULL';
                PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);

                $attributeid = $_POST['allocationid'];
                $historytype = '8';
                $userid = $currentuserid;
                $message = 'Part day of leave start time amended by ' . $username . ' from ' . $pdlStartTimeHis . ' to ' . $pdlEditStartTimeHis . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                $status = 1;
                $historysubtype = 'PH';
                PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);
            }
            if (($pdlEditEndTimeHis != $pdlEndTimeHis) && ($pdlEditStartTimeHis == $pdlStartTimeHis)) {
                $attributeid = $id;
                $historytype = '15';
                $userid = $currentuserid;
                $message = 'Part day of leave end time amended by ' . $username . ' from ' . $pdlEndTimeHis . ' to ' . $pdlEditEndTimeHis . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                $status = 1;
                $historysubtype = 'NULL';
                PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);

                $attributeid = $_POST['allocationid'];
                $historytype = '8';
                $userid = $currentuserid;
                $message = 'Part day of leave end time amended by ' . $username . ' from ' . $pdlEndTimeHis . ' to ' . $pdlEditEndTimeHis . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                $status = 1;
                $historysubtype = 'PH';
                PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);
            }
            if (($pdlEditStartTimeHis != $pdlStartTimeHis) && ($pdlEditEndTimeHis != $pdlEndTimeHis)) {
                $attributeid = $id;
                $historytype = '15';
                $userid = $currentuserid;
                $message = 'Part day of leave start time and end time amended by ' . $username . ' from ' . $pdlStartTimeHis . ' to ' . $pdlEditStartTimeHis . ' and ' . $pdlEndTimeHis . ' to ' . $pdlEditEndTimeHis . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                $status = 1;
                $historysubtype = 'NULL';
                PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);

                $attributeid = $_POST['allocationid'];
                $historytype = '8';
                $userid = $currentuserid;
                $message = 'Part day of leave start time and end time amended by ' . $username . ' from ' . $pdlStartTimeHis . ' to ' . $pdlEditStartTimeHis . ' and ' . $pdlEndTimeHis . ' to ' . $pdlEditEndTimeHis . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                $status = 1;
                $historysubtype = 'PH';
                PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);
            }
        }
    } else {
        $Keyexists = array_key_exists("usercomments", $_POST);
        $_POST['partdaystarttime'] = $_POST['partdaystarttime'] ?? '';
        $_POST['partdayendtime'] = $_POST['partdayendtime'] ?? '';
        if (($_POST['partdaystarttime'] == '') && ($_POST['partdayendtime'] == '') && ($Keyexists == 1)) {
            $strQuery = "UPDATE LeaveApplications SET LeaveStartTime = NULL, LeaveEndTime = NULL,UserEmailSent = $ShortNotice, Approved = 0, LastModBy=$currentuserid, LastModDate=getutcdate(), LeaveStartDateTime=NULL, LeaveEndDateTime=NULL WHERE (ID = $id)";
            $stmt = $pdo->prepare($strQuery);
            $stmt->execute();
            if (($row['LeaveStartTime'] != '') &&  ($row['LeaveStartTime'] != '') && ($LeaveStartTime == '') && ($leaveEndTime == '')) {
                $attributeid = $id;
                $historytype = '15';
                $userid = $currentuserid;
                $message = 'Part day of leave converted to full day leave by ' . $username . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                $status = 1;
                $historysubtype = 'NULL';
                PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);

                $attributeid = $_POST['allocationid'];
                $historytype = '8';
                $userid = $currentuserid;
                $message = 'Part day of leave converted to full day leave by ' . $username . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                $status = 1;
                $historysubtype = 'PH';
                PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);
            } else {
                if ($commentflag == 1) {
                    $attributeid = $id;
                    $historytype = '15';
                    $userid = $currentuserid;
                    $message = 'Full day leave user comments updated by ' . $username . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                    $status = 1;
                    $historysubtype = 'NULL';
                    PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);

                    $attributeid = $_POST['allocationid'];
                    $historytype = '8';
                    $userid = $currentuserid;
                    $message = 'Full day leave user comments updated by ' . $username . ' On ' . getDateTimeInEuropeTimezone(1, 0) . ' ' . getDateTimeInEuropeTimezone(0, 1);
                    $status = 1;
                    $historysubtype = 'PH';
                    PDLHistoryUpdate($attributeid, $historytype, $userid, $message, $status, $historysubtype);
                }
            }
        }
    }
    try {
        $strQuery = "exec [dbo].[usp_get_LeaveDetailsforComments] ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logger()->critical('DB Error', (array) $e);
    }
    $pdlStrtTime = $row['LeaveStartTime'];
    $pdlEndTime = $row['LeaveEndTime'];

    ////////////////////////////////////////////////////////////////////////////////////
    // Send the email......
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = 0;

    $mail->Host = getenv('SMTP_HOST');
    $mail->Port = 25;
    $mail->setFrom($emailfrom, 'Allocate');
    $mailAdd = getenv('EMAIL_BCC');
    $arrCCMail = explode(",", $ccEmail);
    for ($intCount = 0; $intCount < count($arrCCMail); $intCount++) {
        $mail->AddCC($arrCCMail[$intCount]);
    }
    $arrMailAdd = explode(",", $mailAdd);
    for ($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
        $mail->AddBCC($arrMailAdd[$intCount]);
    }
    $mail->addAddress($email);

    $mail->Subject = (getenv('EMAIL_SUFFIX') . $strFullName . ' - Update to Leave Requests');

    $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
    $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

    $html = '<style>' . (@file_get_contents(getenv('EMAIL_CSS')) ?: '') . '</style>
           <body>

	   <img alt="banner" src="cid:pobanner" />
	   <br><br>';

    $html .= '<h2>This is an update to Leave you have requested</h2>';

    $html .= '<table width="1000px">';
    $html .= '<tr>';
    $html .= '<td width="400px" class="datecell">Person</td>';
    $html .= '<td width="600px" class="lightcell">' . $strFullName . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="400px" class="datecell">Leave Group</td>';
    $html .= '<td width="600px" class="lightcell">' . $txtleavegroup . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="400px" class="datecell">Leave Type</td>';
    $html .= '<td width="600px" class="lightcell">' . $txtleavetype . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td width="400px" class="datecell">Date</td>';
    $html .= '<td width="600px" class="lightcell">' . date("l, jS F Y", strtotime($row['dDate'])) . '</td>';
    $html .= '</tr>';
    if (($pdlStrtTime != '') && ($pdlEndTime != '')) {
        $html .= '<tr>';
        $html .= '<td width="400px" class="datecell">Time</td>';
        $html .= '<td width="600px" class="lightcell">' . $service->convertSecondsIntoTime($pdlStrtTime, ':', 'No') . ' - ' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td width="400px" class="datecell">Duration</td>';
        if ($pdlStrtTime > $pdlEndTime) {
            $calPdlEndDuration = (86400 + $pdlEndTime);
            $html .= '<td width="600px" class="lightcell">' . $service->convertSecondsIntoTime($calPdlEndDuration - $pdlStrtTime) . '</td>';
        } else {
            $html .= '<td width="600px" class="lightcell">' . $service->convertSecondsIntoTime($pdlEndTime - $pdlStrtTime) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    $comments = $comments ?? '';
    $html .= 'Your request on ' . date("d M Y", strtotime($row['dDate'])) . ' has had comments added.<br>They are:<br><br><b>';
    $html .= htmlspecialchars($comments);
    $html .= '</b><br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
    $html .= '</body></html>';

    /**
     *  Ticket FAST - 1497
     *  Comment:- No Longer Required
	 *	Reopened :- RSBREAKFIX-97
     */

     $mail->msgHTML($html);
     if (!$mail->send()) {
         echo "Mailer Error: " . $mail->ErrorInfo;exit;
     } else {
         echo "Message sent!";exit;
     }

} else {

    if (isset($_REQUEST['week'])) {
        $intWeek = $_REQUEST['week'];
    } else {
        $intWeek = 0;
    }

    if (isset($_REQUEST['user'])) {
        $StrThisUser = $_REQUEST['user'];
    } else {
        $StrThisUser = $strUser;
    }

    // If we are being posted a group then it's from admin
    if (isset($_REQUEST['group'])) {
        $intGroupID = $_REQUEST['group'];
    } else {
        $intGroupID = 0;
    }
    if (isset($_REQUEST['callpage'])) {
        $intCallPage = $_REQUEST['callpage'];
    } else {
        $intCallPage = 0;
    }

    if (isset($_REQUEST['year'])) {
        $intYear = $_REQUEST['year'];
    } else {
        $intYear = 0;
    }

    try {
        $strQuery = "exec [dbo].[usp_get_LeaveDetailsforComments] ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logger()->critical('DB Error', (array) $e);
    }

    $strUserName = $row['FullName'];
    $strDate = date("Y-m-d", strtotime($row['dDate']));
    $dteDateCurrentText = date("l, jS F Y", strtotime($row['dDate']));
    $strThisLogin = $row['Login'];
    $strOfficeComments = $row['OfficeComments'];
    $strUserComments = $row['Comments'];
    $pdlStrtTime = $row['LeaveStartTime'];
    $pdlEdTime = $row['LeaveEndTime'];

    if (($pdlStrtTime != '') && ($pdlEdTime != '')) {
        $pdlStartTimeMin = floor(($row['LeaveStartTime'] % 3600) / 60);
        $pdlStartTimeHr = floor(($row['LeaveStartTime'] % 86400) / 3600);

        $pdlEndTimeMin = floor(($row['LeaveEndTime'] % 3600) / 60);
        $pdlEndTimeHr = floor(($row['LeaveEndTime'] % 86400) / 3600);

        $pdlStartTime = $pdlStartTimeHr . ':' . $pdlStartTimeMin;
        $pdlEndTime = $pdlEndTimeHr . ':' . $pdlEndTimeMin;
    }

    echo '<div class="tableheadersmall medtextboldcentre" style="padding-bottom:5px;"><br>Request comments for ' . $strUserName . '<br><span id="pdlErrorDiv" style="color:#FF0000;"></span><br></div>';
    echo '<form onsubmit="return submitForm();" id="form">';
    echo '<table class="tablesmall" width="600px">';
    echo '<tr>';
    echo '<th>Date</th>';
    echo '<td>
  <div class="pdl-div-block" style="margin-bottom: 10px;">
        <span style="margin-right: 20px;">' . $dteDateCurrentText . '</span>';
    if ($isPDLAllow == 1) {
        echo '<span class="pdl-div-block">Part Day <input type="checkbox" name="partdayleaveapply" id="partdayleaveapply" onclick="showhidedivPDL();"> </span> <span class="pdl-div-block" style="padding-left:10px;">' . $_POST['dutyName'] . ' | ' . $_POST['dutyStartTimeHrMin'] . ' - ' . $_POST['dutyEndTimeHrMin'] . '</span>';
    }

    echo '</div>';
    if ($isPDLAllow == 1) {
        echo '<div class="pdl-div-block" style="display:none;" id="PDLStartEndTimeDiv">
        <label style="margin-right: 20px;">Start Time <input id="starttimemasterduty" name="partdaystarttime" type="text" class="time ui-timepicker-input" size="6" autocomplete="off" maxlength="5" onchange="clearPDLErrorDiv();"></label>
        <label>End Time <input id="endtimemasterduty" name="partdayendtime" type="text" class="time ui-timepicker-input" size="6" autocomplete="off" maxlength="5" onchange="clearPDLErrorDiv();"> </label>
    </div>';
    }
    echo '</td>';

    echo '</tr>';
    echo '<tr>';
    echo '<th valign="top">Office Comments</th>';
    if (strtolower($strUser) != strtolower($strThisLogin)) {
        echo '<td><textarea rows="4" name="officecomments" cols="45" maxlength="500" class="searchbox">' . $strOfficeComments . '</textarea></td>';
    } else {
        echo '<td class="lightcell smalltext">' . $strOfficeComments . '</td>';
    }
    echo '</tr>';
    echo '<tr>';
    echo '<th valign="top">Comments</th> ';
    if (strtolower($strUser) == strtolower($strThisLogin)) {
        echo '<td><textarea rows="4" name="usercomments" cols="45" class="searchbox">' . $strUserComments . '</textarea>
		</td>';
    } else {
        echo '<td>' . $strUserComments . '</td>';
    }
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightcell smalltext">&nbsp;</td>';
    echo '<td class="lightcell smalltext"><input type="submit" id="Update" value="Update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
    echo '</tr>';
    echo '</table>';
    echo '<input type="hidden" name="id" value="' . $id . '">';
    echo '<input type="hidden" name="doupdate" value="0">';
    echo '<input type="hidden" name="pdlAllow" id="pdlAllow" value="0">';
    echo '<input type="hidden" name="pdlStartTimeInSec" id="pdlStartTimeInSec" value="0">';
    echo '<input type="hidden" name="pdlEndTimeInSec" id="pdlEndTimeInSec" value="0">';
    echo '<input type="hidden" name="dDate" id="pdlEndTimeInSec" value="' . $strDate . '">';
    echo '<input type="hidden" name="allocationid" id="allocationid" value="' . $_POST['allocationid'] . '">';

    echo '</form> ';
?>
    <style type="text/css">
        .ui-timepicker-wrapper {
            width: 5.1em;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
            $(function() {
                <?php if (($isPDLAllow == 1) && ($pdlStrtTime != '') && ($pdlEdTime != '') && (($pdlStrtTime != 0) || ($pdlEdTime != 0))) { ?>
                    $('#partdayleaveapply').trigger('click');
                    $('#starttimemasterduty').val('<?php echo $pdlStartTime; ?>');
                    $('#endtimemasterduty').val('<?php echo $pdlEndTime; ?>');
                <?php } ?>
                $('#pdlErrorDiv').html('');
                $('#starttimemasterduty').timepicker({
                    'step': 15,
                    'timeFormat': 'H:i'
                });
                $('#endtimemasterduty').timepicker({
                    'step': 15,
                    'timeFormat': 'H:i'
                });
            });
        });

        function submitForm() {
            let validationPass = true;
            if ($('#partdayleaveapply').prop('checked') == true) {
                let pdlStartTime = $('#starttimemasterduty').val();
                pdlStartTime = pdlStartTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
                let pdlEndTime = $('#endtimemasterduty').val();
                pdlEndTime = pdlEndTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
                if (pdlStartTime == 0) {
                    pdlStartTime = '00:00';
                }
                if (pdlEndTime == 0) {
                    pdlEndTime = '00:00';
                }

                let dutyStartTime = parseInt('<?php echo $_POST['dutyStartTimeSeconds'] ?? 0 ?>');
                let dutyEndTime = parseInt('<?php echo $_POST['dutyEndTimeSeconds'] ?? 0 ?>');
                if (dutyStartTime > dutyEndTime) {
                    dutyEndTime = parseInt(86400 + dutyEndTime);
                }

                let pdlStartTimeSec = 0;
                let pdlEndTimeSec = 0;
                let pdlEndTimeDbSave = 0;
                if (pdlStartTime != 0) {
                    let pdlStartTimeSplit = pdlStartTime.split(':');
                    pdlStartTimeSec = (+pdlStartTimeSplit[0]) * 60 * 60 + (+pdlStartTimeSplit[1]) * 60;
                }
                if (pdlEndTime != 0) {
                    let pdlEndTimeSplit = pdlEndTime.split(':');
                    pdlEndTimeSec = (+pdlEndTimeSplit[0]) * 60 * 60 + (+pdlEndTimeSplit[1]) * 60;
                    pdlEndTimeDbSave = pdlEndTimeSec;
                }

                if (pdlStartTimeSec > pdlEndTimeSec) {
                    pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
                }

                if (dutyEndTime > 86400) {
                    if ((pdlStartTimeSec < pdlEndTimeSec) && (pdlStartTimeSec < dutyStartTime)) {
                        pdlStartTimeSec = parseInt(86400 + pdlStartTimeSec);
                        pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
                    }
                }

                if (pdlStartTime == '' && pdlEndTime != '') {
                    $('#pdlErrorDiv').html('Please select PDL start time');
                    validationPass = false;
                }
                if (pdlStartTime != '' && pdlEndTime == '') {
                    $('#pdlErrorDiv').html('Please select PDL end time');
                    validationPass = false;
                }
                if (pdlStartTime == '' && pdlEndTime == '') {
                    $('#pdlErrorDiv').html('Please select PDL start and end time');
                    validationPass = false;
                }
                if (pdlStartTime != '' && pdlEndTime != '') {
                    if (pdlStartTimeSec == dutyStartTime && pdlEndTimeSec < dutyEndTime) {
                        let dutyEndTime15MinBefore = parseInt(dutyEndTime - 4500);
                        if (pdlEndTimeSec > dutyEndTime15MinBefore) {
                            $('#pdlErrorDiv').html('PDL end time should be 1.25 Hrs before duty end time');
                            validationPass = false;
                        }
                    } else if (pdlStartTimeSec > dutyStartTime && pdlEndTimeSec == dutyEndTime) {
                        let dutyStartTime15MinAfter = parseInt(dutyStartTime + 4500);
                        if (pdlStartTimeSec < dutyStartTime15MinAfter) {
                            $('#pdlErrorDiv').html('PDL start time should be 1.25 Hrs after duty start time');
                            validationPass = false;
                        }
                    } else if (pdlStartTimeSec == dutyStartTime && pdlEndTimeSec == dutyEndTime) {
                        $('#pdlErrorDiv').html('Duty start and end time cannot be the same as Part Day Leave start and end time. Please change the duty start or end time.');
                        validationPass = false;
                    } else if (pdlStartTimeSec < dutyStartTime && pdlEndTimeSec > dutyEndTime) {
                        $('#pdlErrorDiv').html('PDL should be allowed within duty duration');
                        validationPass = false;
                    } else if (pdlStartTimeSec < dutyStartTime && pdlEndTimeSec <= dutyEndTime) {
                        $('#pdlErrorDiv').html('PDL should be allowed within duty duration');
                        validationPass = false;
                    } else if (pdlStartTimeSec >= dutyStartTime && pdlEndTimeSec > dutyEndTime) {
                        $('#pdlErrorDiv').html('PDL should be allowed within duty duration');
                        validationPass = false;
                    } else if (pdlStartTimeSec > dutyStartTime && pdlEndTimeSec < dutyEndTime) {
                        let pdlDuration = parseInt(parseInt(pdlEndTimeSec) - parseInt(pdlStartTimeSec));
                        let dutyDuration = parseInt(parseInt(dutyEndTime) - parseInt(dutyStartTime));
                        if (pdlDuration == 0) {
                            $('#pdlErrorDiv').html('Part Day Leave start and end time can not be same');
                            validationPass = false;
                        }
                        if (pdlDuration > dutyDuration) {
                            $('#pdlErrorDiv').html('Part Day Leave timings does not lies between duty timings.');
                            validationPass = false;
                        }
                    }
                }
                if (validationPass == true) {
                    $('#pdlStartTimeInSec').val(pdlStartTimeSec);
                    if (pdlStartTimeSec == 86400) {
                        $('#pdlStartTimeInSec').val(0);
                    }
                    if (pdlStartTimeSec > 86400) {
                        let saveDbVal = parseInt(parseInt(pdlStartTimeSec) - 86400);
                        $('#pdlStartTimeInSec').val(saveDbVal);
                    }
                    $('#pdlEndTimeInSec').val(pdlEndTimeDbSave);
                }
            }
            if (validationPass == true) {
                $.ajax({
                    type: 'POST',
                    url: 'page-includes/leave/leave-comments.php',
                    data: $('#form').serialize(),
                    beforeSend: function() {
                        $('#loading').show();
                    },
                    success: function(data) {
                        if ((validationPass == true) && ($('#partdayleaveapply').prop('checked') == true)) {
                            customAlert('You have applied for a part day of leave. If it is not possible to find a corresponding part day of work to combine it with, then it may be necessary for you to take a full day of leave instead. You will be notified if this is the case.');
                            <?php
                            if ($intYear != 0) {
                                echo "ShowLeave('" . $intYear . "', '" . $StrThisUser . "')";
                            } else {
                                if ($intGroupID == 0) {
                                    echo "ShowWeeklyLeave('" . $intWeek . "')";
								 } else if($intCallPage == 8) {
									echo "GetTabContent('".$intCallPage."')";	
                                } else {
                                    if ($intCallPage == 2) {
                                        echo "AdminShowLeaveWeekly('" . $intWeek . "', " . $intGroupID . ")";
                                    } else {
                                        echo "ShowLeaveWeeklyAdmin('" . $intWeek . "', " . $intGroupID . ")";
                                    }
                                }
                            }
                            ?>
                        } else {
                            $.facebox.close();
                            <?php
                            if ($intYear != 0) {
                                echo "ShowLeave('" . $intYear . "', '" . $StrThisUser . "')";
                            } else {
                                if ($intGroupID == 0) {
                                    echo "ShowWeeklyLeave('" . $intWeek . "')";
								 } else if($intCallPage == 8) {
									echo "GetTabContent('".$intCallPage."')";	
                                } else {
                                    if ($intCallPage == 2) {
                                        echo "AdminShowLeaveWeekly('" . $intWeek . "', " . $intGroupID . ")";
                                    } else {
                                        echo "ShowLeaveWeeklyAdmin('" . $intWeek . "', " . $intGroupID . ")";
                                    }
                                }
                            }
                            ?>
                        }
                    }
                });
            }
            return false;
        }

        function showhidedivPDL() {
            $('#starttimemasterduty').val('');
            $('#endtimemasterduty').val('');
            if ($('#partdayleaveapply').prop('checked') == true) {
                $('#pdlAllow').val(1);
                $('#PDLStartEndTimeDiv').show();

                <?php if ($pdlStartTime && $pdlEndTime != '') { ?>
                    $('#starttimemasterduty').val('<?php echo $pdlStartTime; ?>');
                    $('#endtimemasterduty').val('<?php echo $pdlEndTime; ?>');
                    $('#starttimemasterduty').timepicker({
                        'timeFormat': 'H:i'
                    });
                    $('#endtimemasterduty').timepicker({
                        'timeFormat': 'H:i'
                    });
                <?php } ?>

            } else {
                $('#PDLStartEndTimeDiv').hide();
                $('#pdlAllow').val(0);
                $('#pdlStartTimeInSec').val(0);
                $('#pdlEndTimeInSec').val(0);
            }
        }

        function clearPDLErrorDiv() {
            $('#pdlErrorDiv').html('');
        }
    </script>
<?php } ?>