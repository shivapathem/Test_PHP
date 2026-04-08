<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
$strHtml='';
$fullname = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
$assignteamName = '';
$assigndutyOverTime = 0;
$assigndutydate = '';
$assignDuty = '';
$assignSchedulePersonName = '';
if (!empty($unassigned_allocationInfor)) {
    $unassignDuty = $unassigned_allocationInfor['DutyName'];
    $unassignSchedulePerson = $unassigned_allocationInfor['ScheduledPersonID'];
    $unassignSchedulePersonName = $unassigned_allocationInfor['FullName'];
    $unassignDutyweek = $unassigned_allocationInfor['WeekNumber'];
    $unassignDutyday = $unassigned_allocationInfor['iDay'];
    $unassignteamName = $unassigned_allocationInfor['schedulingTeamName'];
    $unassignteamID = $unassigned_allocationInfor['SchedulingTeamId'];
	$unassigndutydate = $unassigned_allocationInfor['DutyDate'];
	$unassigndutyIsSwap = isset($unassigned_allocationInfor['isSwap']) ? $unassigned_allocationInfor['isSwap'] : 0;
    $overTime = $unassigned_allocationInfor['MannualOThours'];
    $intOverTime = $allocService->convertSecondsIntoTime($overTime,'.','No');
    if (!empty($assigned_allocationInfor)) {
        $assignDuty = $assigned_allocationInfor['DutyName'];
        $assignSchedulePerson = $assigned_allocationInfor['ScheduledPersonID'];
        $assignSchedulePersonName = $assigned_allocationInfor['FullName'];
        $assignDutyweek = $assigned_allocationInfor['WeekNumber'];
        $assignDutyday = $assigned_allocationInfor['iDay'];
        $assignteamName = $assigned_allocationInfor['schedulingTeamName'];
        $assignteamID = $assigned_allocationInfor['SchedulingTeamId'];
		$assigndutydate = $assigned_allocationInfor['DutyDate'];
        $assigndutyOverTime = $assigned_allocationInfor['MannualOThours'];
        $intOverTime1 = $allocService->convertSecondsIntoTime($assigndutyOverTime,'.','No');
    }
    $strEmail = GetTeamEmail($unassignteamID);
    //email ready
        if (!is_null($strEmail)) {
               $strSubject = 'Duty Assigned '.$assignteamName.' for duty '.$unassignDuty;
        }
         if (isset($assignDuty) && $assignDuty=='U') {
            $strHtml.= ''.$assignteamName.' - '.date('jS M Y',strtotime($assigndutydate)).'<br>';
            $strHtml.= '<table>';
            $strHtml.= '<tr>';
            $strHtml.= '<td width="1200px">'.$unassignDuty.' which was unassigned was assigned to '.$assignSchedulePersonName.' by '.$fullname .' on '.date('jS M Y').' at '.date("H:i").'<hr></td>';
            $strHtml.= '</tr>';
            $strHtml.'</table>';
        }else if (isset($unassignDuty) && $unassignDuty!='') {
			$strSubject = 'Duty Unassigned '.$unassignteamName.' for duty '.$unassignDuty;
			$strHtml.= ''.$unassignteamName.' - '.date('jS M Y',strtotime($unassigndutydate)).'<br>';
            $strHtml.= '<table>';
            $strHtml.= '<tr>';
            $strHtml.= '<td width="1200px">'.$unassignDuty.' which was assigned to   '.$unassignSchedulePersonName.' was unassigned by '.$fullname .' on '.date('jS M Y').' at '.date("H:i"). ((($overTime == 0) && ($unassigndutyIsSwap == 0)) || (($assigndutyOverTime == 0) && ($unassigndutyIsSwap == 1)) ? '<hr>' : '') .'</td>';
            $strHtml.= '</tr>';
            if($overTime > 0){
                $strHtml.= '<tr>';
                $strHtml.= '<td width="1200px"><br>'.$intOverTime . ' hours of overtime were unmarked for '.$unassignSchedulePersonName.' on ' . date('jS M Y',strtotime($unassigndutydate)).'.<hr></td>';
                $strHtml.= '</tr>';
            }elseif(($assigndutyOverTime > 0) && ($unassigndutyIsSwap == 1)){
                $strHtml.= '<tr>';
                $strHtml.= '<td width="1200px"><br>'.$intOverTime1 . ' hours of overtime were unmarked for '.$assignSchedulePersonName.' on ' . date('jS M Y',strtotime($assigndutydate)).'.<hr></td>';
                $strHtml.= '</tr>';
            }
        } else {
            $strHtml.= ''.$assignteamName.' - '.date('jS M Y',strtotime($assigndutydate)).'<br>';
            $strHtml.= '<table>';
            $strHtml.= '<tr>';
            $strHtml.= '<td width="1200px">'.$unassignDuty.' which was unassigned was swapped with '.$assignDuty.' which was assigned to '.$assignSchedulePersonName.' by '.$fullname .' on '.date('jS M Y').' at '.date("H:i").'<hr></td>';
            $strHtml.= '</tr>';

        }
        $strHtml.='<tr align="right"><td align="right"><img src="cid:bbclogo" /><br><font size="1">BBC  '.romanNumerals(date("Y")).'<font></td></tr>';
        $strHtml.='</table>';
    $strHtml.='</body>';
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    if (!empty($strEmail)) {
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = getenv('SMTP_HOST');
        $mail->Port = 25;
        $mail->setFrom('noreply@bbc.co.uk', 'Allocate');
        $mailAdd = getenv('EMAIL_BCC');
        $arrMailAdd = explode(",", $mailAdd);
        for ($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
            $mail->AddBCC($arrMailAdd[$intCount ]);
        }
        $mail->Subject =getenv('EMAIL_SUFFIX').$strSubject;
            if (substr_count($strEmail, ';')) {
                $arrDepFrom = explode(";", $strEmail);
                foreach ($arrDepFrom as $strEmail) {
                    $mail->addAddress(trim($strEmail));
                }
            } else {
                $mail->addAddress(trim($strEmail));
            }
            $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
            $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
            $strHtml = '<style>'. (@file_get_contents(getenv('EMAIL_CSS')) ?: '') .'</style>
            <body><img alt="Banner" src="cid:pobanner" /><br><br>'.$strHtml;
            $mail->msgHTML($strHtml);
            if (!$mail->send()) {
                logger()->critical('Mailer Error', (array) $mail->ErrorInfo);
                exit;
            }
        } else {
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            logger()->critical('email not set in TEAM', (array) $mail->ErrorInfo);
        }
    } else {
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        logger()->critical('Email Not Found', (array) $mail->ErrorInfo);
    }
