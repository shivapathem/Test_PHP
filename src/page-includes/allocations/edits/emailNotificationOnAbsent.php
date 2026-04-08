<?php
if ($Allocation_and_staff_info != false) {
    $ScheduledPersonID = $Allocation_and_staff_info['ScheduledPersonID'] ?? 0;
    $strFullName = $Allocation_and_staff_info['FullName'] ?? '';
    $strDutyName = $Allocation_and_staff_info['DutyName'] ?? '';
    $week = $Allocation_and_staff_info['WeekNumber'] ?? 0;
    $day = $Allocation_and_staff_info['iDay'] ?? null;
    $teamName = $teamName;
    $dutyDate = $Allocation_and_staff_info['DutyDate'] ?? '';
    $isShiftleader = 1;
    $overTime = $Allocation_and_staff_info['MannualOThours'] ?? 0;
    $intOverTime = $allocService->convertSecondsIntoTime($overTime,'.','No');
    $rowStartTime = isset($Allocation_and_staff_info['StartTime']) ? (int)$Allocation_and_staff_info['StartTime'] : 0;
    $rowEndTime = isset($Allocation_and_staff_info['EndTime']) ? (int)$Allocation_and_staff_info['EndTime'] : 0;
    $intstartHour = intval($rowStartTime / 3600);
    $intstartHour = strlen(trim($intstartHour))== 1 ? "0".$intstartHour : $intstartHour;
    $intstartMinute = intval(($rowStartTime % 3600) / 60);
    $intstartMinute = strlen(trim($intstartMinute))== 1 ? "0".$intstartMinute : $intstartMinute;
    $intstartTime   =   $intstartHour . ':' . $intstartMinute;
    $intendHour = intval($rowEndTime / 3600);
    $intendHour = strlen(trim($intendHour))== 1 ? "0".$intendHour : $intendHour;
    $intendMinute = intval(($rowEndTime % 3600) / 60);
    $intendMinute = strlen(trim($intendMinute))== 1 ?  "0".$intendMinute : $intendMinute;
    $intendTime   =   $intendHour . ':' . $intendMinute;
	$strTimes='';
	if ((!empty($rowStartTime)) && (!empty($rowEndTime))){
		$strTimes = " (".$intstartTime.'-'.$intendTime.")";
	}

    $datefromweek = $commonObj->GetWeekStartDateByWeekNoFromTimeDim($week,'ByweeknoAndIDayOnly',$day);

    $strDate = $datefromweek['dDateTime'] ?? '';
    $strThisDate = date("D, jS M Y", strtotime($strDate))  ?? '';
    $SchedulingTeamId = $Allocation_and_staff_info['SchedulingTeamId'] ?? 0;
    $strEmail = GetTeamEmail($SchedulingTeamId);
    $intEndWeek = addweeks($week, 1);
    //email ready
    $strHtml='';
    if (!is_null($strEmail)) {
        if($Allocation_and_staff_info['type'] == 'absent') {
            $strHtml .= '<p><b>'.$strFullName.'</b> was marked as \''.ucfirst($Allocation_and_staff_info['type']).' \' on '.date("jS M Y").' at '.date("H:i").' by '.$_SESSION['user']['FullName'].'. <br>';
            $strHtml.='The Duty was \''.$strDutyName.'\' on '.date("jS M Y", strtotime($strDate)). $strTimes;
            if($overTime > 0){
                $strHtml.= '<br><br>'.$intOverTime . ' hours of overtime were unmarked for '.$strFullName.' on ' . date('jS M Y',strtotime($dutyDate)).'.';
            }
            $strSubject = $strFullName.' - Duty marked as '.ucfirst($Allocation_and_staff_info['type']);
        } else {
            $strHtml .= '<p><b>'.$strFullName.'</b> was marked as \''.$strDutyName.'\' on '.date("jS M Y").' at '.date("H:i").' by '.$_SESSION['user']['FullName'].'. <br>';
            if($overTime > 0){
                $strHtml.= '<br>'.$intOverTime . ' hours of overtime were unmarked for '.$strFullName.' on ' . date('jS M Y',strtotime($dutyDate)).'. <br>';
            }
            $strSubject = 'Duty Assigned '.$teamName.' for duty '.$strDutyName;
        }
        $arrTeamDefaults = GetTeamDefaults($intUserId = 0, $SchedulingTeamId);
        $arrAllocations = ReadAllocationsIndividual($week, $intEndWeek, $ScheduledPersonID, $arrTeamDefaults, 0, $intLeaveCaller = 0, $isShiftleader=1,$email='send');

        if (isset($arrAllocations)) {
            $strHtml.= '<br><br>The Duties for the next period are:<br><br>';
            $strHtml.= '<table>';
            $strHtml.= '<tr>';
            $strHtml.= '<td width="350px" class="datecell">Date</td>';
            $strHtml.= '<td width="350px" class="datecell">Duty</td>';
            $strHtml.= '<td width="350px" class="datecell">Times/Duration</td>';
            $strHtml.= '</tr>';

            foreach ($arrAllocations['Weeks'] as $intAllocationWeek => $arrWeeksAllocations) {

               foreach ($arrWeeksAllocations as $intAllocationDay => $arrDaysAllocation) {

                    if ($intAllocationWeek == $week && $intAllocationDay >= $day || $intAllocationWeek != $week) {
                        $datefromweek =$commonObj->GetWeekStartDateByWeekNoFromTimeDim($intAllocationWeek,'ByweeknoAndIDayOnly',$intAllocationDay);
                        $strThisDate = date("D, jS M Y", strtotime($datefromweek['dDateTime']));
                        $strHtml.= '<tr>';
                        $strHtml.= '<td>';
                        $strHtml.= $strThisDate;
                        $strHtml.= '</td>';
                        $strHtml.= '<td>';
                        $strHtml.= $arrDaysAllocation['Duty'];
                        $strHtml.= '</td>';
                        $strHtml.= '<td>';
                        if (isset($arrDaysAllocation['StartTime'])) {
                            $strHtml.= $arrDaysAllocation['StartTime'].' - '.$arrDaysAllocation['EndTime'];
                        }
                        else {
                            if(isset($arrDaysAllocation['Duration'])){
                                $strHtml.= $arrDaysAllocation['Duration'].' Hours';
                              }else{
                                $strHtml.='0.00 Hours';
                              }
                        }
                        $strHtml.= '</td>';
                        $strHtml.= '</tr>';
                    }

                }
            } //Foreach Close

            $strHtml.='</table>';
            $strHtml.='</body>';
			$strHtml.='<br><br><br><p align="left"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
        } //If allocation Exists then Check
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = getenv('SMTP_HOST');
    $mail->Port = 25;
    $mail->setFrom('noreply@bbc.co.uk', 'Allocate');
    $mailAdd = getenv('EMAIL_BCC');
    $arrMailAdd = explode(",", $mailAdd);
    for($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
        $mail->AddBCC($arrMailAdd[$intCount ]);
    }
    $mail->Subject =getenv('EMAIL_SUFFIX').$strSubject;
        if (substr_count($strEmail,';')) {
            $arrDepFrom = explode(";", $strEmail);
            foreach ($arrDepFrom as $strEmail) {
                $mail->addAddress(trim($strEmail));
            }
        } else {
            $mail->addAddress(trim($strEmail));
        }
        $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
        $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
        $strHtml = '<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?: '').'</style>
        <body><img alt="Banner" src="cid:pobanner" /><br><br>'.$strHtml;
        $mail->msgHTML($strHtml);
        if (!$mail->send()) {
			 logger()->critical('Mailer Error', (array) $mail->ErrorInfo);
             exit;
        }
    } //if Email Id is not Blank
    else {
    }
}//If Allocation Data Exist;
else{
    logger()->critical('Email Not Found', (array) $mail->ErrorInfo);
}