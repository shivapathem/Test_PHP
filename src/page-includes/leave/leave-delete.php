<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

$idRequestData = $_REQUEST['id'];
$allocationidRequestData = $_REQUEST['allocationid'];

if(is_array($idRequestData)) { // Group delete
    foreach($idRequestData as $key => $idData) {
        deleteLeave($idData,  $allocationidRequestData[$key]);
    }
} else { // Single delete
    deleteLeave($idRequestData,  $allocationidRequestData);
}

function deleteLeave($idP, $allocationidP) {
    $service = new AllocationService();
    $pdo = OpenDBLinkA7();
    $id = $idP;
    $allocationid = $allocationidP;
    $_SESSION['allocationid'] = $allocationid;
    $UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

    $UserFullName = isset($_SESSION['user']['FullName']) && !empty($_SESSION['user']['FullName']) ? $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
    $history = 'Leave deleted by '.$UserFullName.' On '.getDateTimeInEuropeTimezone(1, 0).' '.getDateTimeInEuropeTimezone(0, 1);
    $history = escapeSingleQuotes($history);
    $pdo = OpenDBLinkA7();
    //get the leaveapplication details
    $leavedetails = getLeaveApplicationDetails($id);

    //get leave taken data from particular leave application
    $arrAllocLeaveTypes = GetLeaveAllocateTypes();
    $dataRange=$arrLeaveTakenData =$removeApplicationData= array();
    $typesNames = array_column($arrAllocLeaveTypes, 'AllocName', 'ID');

        try {
                $strQuery = "exec [dbo].[usp_DeleteLeaveApplications] ?,?,?,?";
                $stmt = $pdo->prepare($strQuery);
                $stmt->bindParam(1, $id, PDO::PARAM_INT);
                $stmt->bindParam(2, $history, PDO::PARAM_STR);
                $stmt->bindParam(3, $UserID, PDO::PARAM_INT);
                $stmt->bindParam(4, $UserFullName, PDO::PARAM_STR);
                $stmt->execute();
            
        } catch(Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

    if ( count($leavedetails) > 0 ) {
        if ($leavedetails['ShortNotice'] == 1) {
            $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $emailfrom = $leavedetails['emailfrom'];
            $emailcopiesto = $leavedetails['emailcopiesto'];
            $strRequesterName = $leavedetails['FullName'];
            $strEmailTo = !empty($leavedetails['InternalEmail']) ? $leavedetails['InternalEmail'] : GetEmailFromLogin($leavedetails['Login']);
            $strLeaveDate = date("l, jS F Y", strtotime($leavedetails['dDate']));
            $strGroupDescription = $leavedetails['GroupDesc'];
            $strTypeDescription = $leavedetails['TypeDesc'];		
            $pdlStrtTime  = $leavedetails['LeaveStartTime'];
            $pdlEndTime = $leavedetails['LeaveEndTime'];

            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->SMTPDebug = 0;        

            $mail->Host = getenv('SMTP_HOST');
            $mail->Port = 25;

            $mail->setFrom($emailfrom, 'Allocate');
            $mail->addAddress($strEmailTo);

            $arrCCMail = explode(",", $emailcopiesto);
            for ($intCount = 0; $intCount < count($arrCCMail); $intCount++) {
                $mail->AddCC($arrCCMail[$intCount]);
            }
            $mail->AddCC($strEmailTo);

            $mail->Subject = getenv('EMAIL_SUFFIX') . $strRequesterName . ' -  Cancellation of Short Notice Leave';

            $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
            $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

            $html = '<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?: '').'</style>
            <body>
            <img alt="Banner" src="cid:pobanner" /><br><br>';

            $html .= '<h2>Cancellation of Short Notice Leave - for ' . $strRequesterName . '</h2>';

            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<td width="300px" class="datecell">Date</td>';        
            $html .= '<td width="300px" class="datecell">Leave Type</td>';
            $html .= '<td width="300px" class="datecell">Leave Group</td>';
            $html .= '<td width="300px" class="datecell">Time</td>';
            $html .= '<td width="300px" class="datecell">Duration</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td>';
            $html .= $strLeaveDate;
            $html .= '</td>';        
            $html .= '<td>';
            $html .= $strTypeDescription;
            $html .= '</td>';
            $html .= '<td>';
            $html .= $strGroupDescription;
            $html .= '</td>';
            if (($pdlStrtTime != '') && ($pdlEndTime != '')) { 
            $html .= '<td>';
            $html .= $service->convertSecondsIntoTime($pdlStrtTime,':','No').' - '.$service->convertSecondsIntoTime($pdlEndTime,':','No');
            $html .= '</td>';		
            $html .= '<td>';
            if ($pdlStrtTime > $pdlEndTime) {
                $calPdlEndDuration = (86400 + $pdlEndTime);
                $html.= $service->convertSecondsIntoTime($calPdlEndDuration-$pdlStrtTime);
            }else{
                $html .= $service->convertSecondsIntoTime($pdlEndTime-$pdlStrtTime);
            }
            $html .= '</td>';
            }
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td colspan="2">';
            $html .= 'Cancelled on ' . getDateTimeInEuropeTimezone(1, 0, "jS M Y") . ' at ' . getDateTimeInEuropeTimezone(0, 1);
            $html .= '</td>';
            $html .= '</tr>';
            $html.= '<tr><td>&nbsp;</td></tr>';
            $html.= '<tr>';
            $html.= '<td colspan=4 style="text-align:center">';
            $html.= '<a href="' . getenv('BASE_URL') . '">Click here to access Allocate</a>';
            $html.= '</td>';
            $html.= '</tr>';

            $html .= '</table>';

            $html .= '<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
            $html .= '</body></html>';
            $mail->msgHTML($html);

            if (!$mail->send()) {
                echo "Mailer Error: " . $mail->ErrorInfo;
                exit;
            } else {
                echo "Message sent!";
            }

        }
    }
}