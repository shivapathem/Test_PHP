<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../function-includes/helpers.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/user-scheduling-team-list.php';
include_once 'setTeamCookieRotas.php';
$pageid = 24;
// Call User Permission function.

$intAreaID = $_SESSION['user']['AreaID'];
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intTeamID = $_POST['teamId'] ?? 0;

if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
}else{
    $strTeams = 0;
}
if($strTeams == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $strTeams);
}

$rsRotasJson = ListAllRotas($intAreaID, $strTeams);

$rsRotas = json_decode($rsRotasJson, true);
$intRotaID = $_POST["id"];
$intRotaLineID = $_POST["lineid"];
if (!(isset($intRotaLineID))) {
    $intRotaLineID = 0;
}
$intShowTemplates = $_POST["showtemplates"];
if (!(isset($intShowTemplates))) {
    $intShowTemplates = 0;
}
$intShowAll = $_POST["showall"];
if (!(isset($intShowAll))) {
    $intShowAll = 0;
}
$strEffDate = $_POST["effdate"];
if (!(isset($strEffDate))) {
    $strEffDate = date("d/m/Y");
}

$intShowBreaks = $_POST["showbreaks"];
if (!(isset($intShowBreaks))) {
    $intShowBreaks = 0;
}

$peopletabPage = $_POST["peopleTab"] ?? 0;

if (($permissions->canmodify == 1) || ($permissions->cancreate == 1)) {
    $classname = 'rotatabledroppable';
} else {
    $classname = 'notrotatabledroppable';
}

$intRotaTab = isset($_COOKIE['rotatabs']) ? $_COOKIE['rotatabs'] : 0;

$intWeeksInRota = 0;
$dtRotaStartDate = '';
$intType = 0;
$intTeamID = 0;

if ($intRotaTab > 0) {
    $intShowTemplates = 2;
    $intShowAll = 0;
}

//check if we show the form
if ($permissions->canview == 1) {
    $strEffDateArr  =   explode('/', $strEffDate);
    $dateNewFormate = $strEffDateArr['0'] . '-'. $strEffDateArr['1'] . '-' . $strEffDateArr['2'];
    $firstSatDate = date('d/m/Y', strtotime("first saturday of $dateNewFormate"));
    if(strtotime($dateNewFormate) < strtotime(str_replace('/','-',$firstSatDate)))
    {
        $dateNewFormate = date('d-m-Y', strtotime("-1 week $dateNewFormate"));
        $firstSatDate = date('d/m/Y', strtotime("first saturday of $dateNewFormate"));
    }
    
    $rsRotaDetailsJson = GetRotaDutiesDetails($intRotaID, $firstSatDate);
    
    $rsRotaDetails = json_decode($rsRotaDetailsJson, true);
    if (!empty($rsRotaDetails)) {
        $intWeeksInRota = $rsRotaDetails['WeeksInRota'];
        $dtRotaStartDate = $rsRotaDetails['RotaStartDate'];
        $intTeamID = $rsRotaDetails['TeamID'];
        $intWeekInRota = $rsRotaDetails['RotaWeek'] ? $rsRotaDetails['RotaWeek'] : $rsRotaDetails['CurrentWeekInRota'];
        $intBBCStartWeek = $rsRotaDetails['BBCStartWeek'] ? $rsRotaDetails['BBCStartWeek'] : $rsRotaDetails['RotaStartWeekDefault'];
    }
    
    $rsRotaJson = GetRotaDuties($intRotaID, $intShowTemplates, $intShowAll, $strEffDate);
    $rsRota = json_decode($rsRotaJson, true);
    $accountingData = [];
    if (!empty($rsRota)) {
        $RowCount = count($rsRota);
        for ($row = 0; $row < $RowCount; $row++) {
            $accountingData[$rsRota[$row]['RotaWeek']]['AccPeriod'] = $rsRota[$row]['AccPeriod'];
            $accountingData[$rsRota[$row]['RotaWeek']]['AccHrs'] = !empty($rsRota[$row]['AccHrs']) ? number_format((float)(intval($rsRota[$row]['AccHrs']) / 3600), 2, '.', '') : '';
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['dutyid'] = $rsRota[$row]['DutyID'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['rotadutyid'] = $rsRota[$row]['RotaDutyID'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['dutytypeid'] = $rsRota[$row]['DutyTypeID'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['dutyname'] = $rsRota[$row]['DutyName'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['startdate'] = $rsRota[$row]['RotaDutyStartDate'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['enddate'] = $rsRota[$row]['RotaDutyEndDate'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['backcolour'] = $rsRota[$row]['ColourBackground'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['fontcolour'] = $rsRota[$row]['ColourFont'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['duration'] = $rsRota[$row]['Duration'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['durationexc'] = $rsRota[$row]['DurationExc'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['starttime'] = $rsRota[$row]['StartTime'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['endtime'] = $rsRota[$row]['EndTime'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['isassigned'] = $rsRota[$row]['IsAssigned'];
            $arrRota[$rsRota[$row]['RotaWeek']][$rsRota[$row]['DayOfRota']][$rsRota[$row]['RotaDutyID']]['IsActive'] = $rsRota[$row]['IsActive'];
        }
    }

    $TeamOptions = getSchedulingTeamList($intTeamID, 'rota-pattern', 'view');

    $rsRotaCountsJson = GetRotaDutyCounts($intRotaID, $intShowBreaks, $strEffDate);
    $rsRotaCounts = json_decode($rsRotaCountsJson, true);
    
    $accPeriodData = [];
    $firstInstance = true;
    for ($intWeek = 1; $intWeek <= $intWeeksInRota; $intWeek++) {
        $accountingData[$intWeek] = $accountingData[$intWeek] ?? [];
        if(empty($accountingData[$intWeek])) {
            $firstInstance = true;
        } else {
            $accPeriodData2 = explode('/', $accountingData[$intWeek]['AccPeriod'] ?? '');
            if($accPeriodData2[0] != 1) {
                $firstInstance = true;
            }
        }        
        if(!empty($accountingData[$intWeek]) && $firstInstance) {        
            $accPeriodData2 = explode('/', $accountingData[$intWeek]['AccPeriod'] ?? '');
            if($accPeriodData2[0] != 1 && is_numeric($accPeriodData2[0])) {
                $weekNumber = $intWeek;
                for ($i = $accPeriodData2[0] - 1; $i >= 1; $i--) {
                    $weekNumber =  $weekNumber - 1;
                    $accountingData[$weekNumber] = [
                        'AccPeriod' => $i . '/' . $accPeriodData2[1] ,
                        'AccHrs' => $accountingData[$intWeek]['AccHrs']
                    ];
                }
            }
        }        
    }

    echo '<div id="rotadetailstopdiv" style="width:101%;">';
    echo '<table id="rotadetails" class="stripe bluetable" width="99%" style="margin-bottom:0px;">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="2">';
    echo '<select class="chosen-select" name="ddlRotaName" id="ddlRotaName" style="background-color:white; width:250px;" onchange="ShowRotaDetails(this.value, 1);">';
    if ($intRotaID == 0) {
        echo '<option value="0" selected>Select Rota Pattern</option>';
    } else {
        echo '<option value="0">Select Rota Pattern</option>';
    }
    if (!empty($rsRotas) && $rsRotas > 0) {
        $CountRow = count($rsRotas);
        foreach ($rsRotas as $rota) {
            $rotaName = isset($rota['RotaName']) ? $rota['RotaName'] : '';
            $selected = ($rota['rotaid'] == $intRotaID) ? ' selected' : '';
            echo "<option value=\"{$rota['rotaid']}\"{$selected}>{$rotaName}</option>";
        }
    }
    echo '</select>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    if ($intRotaTab == 0) {
        if ($permissions->cancreate == 1) {
            echo '<img id="imgNewRota" name="imgNewRota" tabindex="0" class="handcursor" border="0" src="images/add.png" style="top:10px; vertical-align:middle;" alt="New Rota" width="18px" height="18px" onclick=\'javascript:NewRota()\'; title="New Rota">';
        }
    }
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    if ($intRotaTab > 0) {
        //nothing
    } else if ($permissions->canmodify == 0) {
        //nothing
    } else if ($intRotaID == 0) {
        echo '<img id="imgEditRota" name="imgEditRota" border="0" src="images/edit_disabled.png" style="top:10px; vertical-align:middle;" alt="Edit Rota" width="18px" height="17px" title="Edit Rota">';
    } else {
        echo '<img id="imgEditRota" name="imgEditRota" class="handcursor" border="0" src="images/edit.png" style="top:10px; vertical-align:middle;" alt="Edit Rota" width="18px" height="17px" onclick=\'javascript:EditRota()\'; title="Edit Rota">';
    }
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    if ($intRotaTab > 0) {
        //nothing
    } else if ($permissions->canmodify == 0) {
        //nothing
    } else if ($intRotaID == 0) {
        echo '<img id="imgCopyRota" name="imgCopyRota" border="0" src="images/copy_disabled.png" style="top:10px; vertical-align:middle;" alt="Copy Rota" width="18px" height="17px" title="Copy Rota">';
    } else {
        echo '<img id="imgCopyRota" name="imgCopyRota" class="handcursor" border="0" src="images/copy.png" alt="Copy Rota" style="top:10px; vertical-align:middle;" width="18px" height="17px" title="Copy Rota" onclick=\'javascript:CopyRota()\';>';
    }
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    if ($intRotaTab > 0) {
        //nothing
    } else if ($permissions->candelete == 0) {
        //nothing
    } else if ($intRotaID == 0) {
        echo '<img id="imgDeleteRota" name="imgDeleteRota" border="0" src="images/delete_disabled.png" style="top:10px; vertical-align:middle;" alt="Delete Rota" width="18px" height="17px" title="Delete Rota">';
    } else {
        echo '<img id="imgDeleteRota" name="imgDeleteRota" class="handcursor" border="0" src="images/delete.png" alt="Delete Rota" style="top:10px; vertical-align:middle;" width="18px" height="17px" title="Delete Rota" onclick=\'javascript:DeleteRota()\';>';
    }

    if ($intRotaTab > 0) {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<select class="chosen-select" name="ddlShowRota" id="ddlShowRota" style="background-color:white; width:130px; height:20px;" onchange="ShowRota(this.value);">';
        echo '<option value="0">Hide Rota Pattern</option>';
        echo '<option value="1" selected>Show Rota Pattern</option>';
        echo '</select>';
    }
    echo '</th>';
    echo '<th colspan="2" style="text-align:center;">';

    echo '</th>';
    echo '<th colspan="4" style="text-align:center;">';
    echo '</th>';
    echo '<th colspan="2" style="padding:0px; ">';
    echo '<table width="100%" style="padding:0px; border-style:none;">';
    echo '<tr style="padding:0px; border-style:none;">';
    echo '<td style="padding:0px; border-style:none;" width="80%">';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<select class="chosen-select" name="ddlRotaBreaks" id="ddlRotaBreaks" style="background-color:white; width:100%; height:20px;" onchange=\'javascript:ShowRotaBreaks(' . $intRotaID . ',this.value)\';>';
    if ($intShowBreaks == 0) {
        echo '<option value="0" selected>Excluding Breaks</option>';
    } else {
        echo '<option value="0">Excluding Breaks</option>';
    }
    if ($intShowBreaks == 1) {
        echo '<option value="1" selected>Including Breaks</option>';
    } else {
        echo '<option value="1">Including Breaks</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</th>';
    echo '</tr>';
    echo '<tr>';

    if (!empty($rsRotaCounts)) {
        $countRow = count($rsRotaCounts);
        for ($row = 0; $row < $countRow; $row++) {
            echo '<th class="stripe bluetable">Start Date: ';
            if ($dtRotaStartDate == "") {
                echo "None";
            } else {
                echo $dtRotaStartDate;
            }
            echo '</th>';
            echo '<th class="stripe bluetable">Rota Weeks: ';
            echo $intWeeksInRota;
            echo '</th>';
            echo '<th class="stripe bluetable">Allocated Days: ';
            echo $rsRotaCounts[$row]['AllocatedDays'];
            echo '</th>';
            echo '<th class="stripe bluetable">Empty Days: ';
            echo $rsRotaCounts[$row]['UnAllocatedDays'];
            echo '</th>';
            echo '<th class="stripe bluetable">Master Duties: ';
            echo $rsRotaCounts[$row]['Masters'];
            echo '</th>';
            echo '<th class="stripe bluetable">';
            echo " ";
            echo '</th>';
            if ($intShowAll == 1) {
                echo '<th class="stripe bluetable">Hours: ';
                echo '</th>';
                echo '<th class="stripe bluetable">Average: ';
                echo '</th>';
                echo '<th class="stripe bluetable">EFT: ';
                echo '</th>';
            } else {
                echo '<th class="stripe bluetable">Hours: <span id="hoursContainer"></span>';
                echo '</th>';
                echo '<th class="stripe bluetable">Average: <span id="avgContainer"></span>';
                echo '</th>';
                echo '<th class="stripe bluetable">EFT: <span id="eftContainer"></span>';
                echo '</th>';
            }
            echo '<th class="stripe bluetable">';
            echo " ";
            echo '</th>';
        }
    } else {
        echo '<th colspan="10">&nbsp;</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '</table>';
    echo '</div>';

    echo '<div id="rotapeoplelistsdiv0" style="width:100%;" >';
    echo '<div id="rotapeoplelistsdiv1" style="overflow:auto; margin-top:-5px; float:left; width:54%;" >';
    echo '</div>';
    echo '<div id="rotapeoplelistsdiv2" style="float:right; width:44%;" >';
    echo '</div>';
    echo '</div>';

    echo '<div id="rotadutieslist0" style="overflow:scroll; margin-top:0px; position:relative; width:100%;"  onScroll="setRotaScrollPos()">';
	echo '<div id="rotaScrollPos"  name="rotaScrollPos"></div>';
    echo '<table id="rotadetails2" class="bluetable" style="width:100%; margin-bottom: 0px;position: relative; margin-top: 0px;">';
    echo '<thead>';
    echo '<tr>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" ' . ($peopletabPage == 1 ? 'width="2%" ' : 'width="10px" ') . '>Line</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" ' . ($peopletabPage == 1 ? 'width="2%" ' : 'width="20px" ') . '>Week</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" ' . ($peopletabPage == 1 ? 'width="2%" ' : 'width="30px" ') . '>Date</th>';
    if($peopletabPage == 1) {
        echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="2%">Accounting Period</th>';
        echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="6%">Planned Rota Pattern Hours</th>';
    }
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="80px">Saturday</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="80px">Sunday</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="80px">Monday</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="80px">Tuesday</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="80px">Wednesday</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="80px">Thursday</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="80px">Friday</th>';
    echo '<th style="border-top:2px solid #000000; position: -webkit-sticky; position: sticky; top: 0; z-index: 1;" width="30px">Hours</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    if ($intWeeksInRota > 0) {
        $highlightRowFlag   =   0;
		$totalDuration		=	0;
        $lastAccData = [];
        for ($intWeek = 1; $intWeek <= $intWeeksInRota; $intWeek++) {
            $bbcrotaweek = addweeks($rsRotaDetails['RotaStartWeek'], ($intWeek - 1));
            if ( $highlightRowFlag == 0 && ( strtotime(str_replace('/','-',$strEffDate)) - strtotime(datefromweek($bbcrotaweek, 0)) ) < "604800" ) {
                echo '<tr style="background-color:#93AAD8;" id="w' . strval($intWeek) . '">';
                $highlightRowFlag++;
            } else {
                echo '<tr id="w' . strval($intWeek) . '">';
            }
            echo '<td class="' . $classname . '">';
            echo $intWeek;
            echo '</td>';
            echo '<td class="' . $classname . '">';
            echo spinweek($bbcrotaweek);
            echo '</td>';            
            echo '<td class="' . $classname . '">';
            echo date("d/m/Y", strtotime(datefromweek($bbcrotaweek, 0)));
            echo '</td>';
            if($peopletabPage == 1) {
                if(isset($accountingData[$intWeek]['AccPeriod']) && !empty($accountingData[$intWeek]['AccPeriod'])) {
                    echo '<td>' . $accountingData[$intWeek]['AccPeriod'] . '</td>';
                    echo '<td>' . $accountingData[$intWeek]['AccHrs']  . '</td>';
                    $lastAccData = $accountingData[$intWeek];
                } else if(!empty($lastAccData)) {
                    $accPeriod = explode('/', $lastAccData['AccPeriod']);
                    if($accPeriod[0] == $accPeriod[1]) {
                        echo '<td></td>';
                        echo '<td></td>';
                        $lastAccData = [];
                    } else {
                        $lastAccData = [
                            'AccPeriod' => ($accPeriod[0] + 1) . '/' . $accPeriod[1],
                            'AccHrs' => $lastAccData['AccHrs']
                        ];
                        echo '<td>' . $lastAccData['AccPeriod'] . '</td>';
                        echo '<td>' . $lastAccData['AccHrs'] . '</td>';
                    }
                } else {
                    echo '<td></td>';
                    echo '<td></td>';
                }
            }
            $dblDuration = 0;
            for ($intDay = 0; $intDay <= 6; $intDay++) {
                $intDayOfRota = ($intWeek * 7) + $intDay;

                if (isset($arrRota[$intWeek][$intDay])) {
                    echo '<td class="' . $classname . '" id="w' . strval($intWeek) . 'd' . strval($intDay) . '" dayofrota="' . $intDay . '" rotaid="' . $intRotaID . '" weekid="' . $intWeek . '" style="padding:0px;" style="width: auto">';
                    echo '<table width="100%">';
					$iterationCount	= 0;
                    foreach ($arrRota[$intWeek][$intDay] as $arrRotaRecords) {
                        
                        if(!isset($dutydurationValSum)){
                            $dutydurationValSum =0;
                        }
                        if (isset($arrRotaRecords['dutyid'])) {
                            $intRotaDutyID = $arrRotaRecords['rotadutyid'];
                            $intDutyID = $arrRotaRecords['dutyid'];
                            $intDutyTypeID = $arrRotaRecords['dutytypeid'];
                            $strDutyName = $arrRotaRecords['dutyname'];
                            $strStartDate = $arrRotaRecords['startdate'];
                            $strEndDate = $arrRotaRecords['enddate'];
                            $intIsActive = $arrRotaRecords['IsActive'];
                            $strBackColour = $arrRotaRecords['backcolour'];
                            $strFontColour = $arrRotaRecords['fontcolour'];
                            if ($intShowBreaks == 1) {
                                $intDuration = $arrRotaRecords['duration'];
                            } else {
                                $intDuration = $arrRotaRecords['durationexc'];
                            }
                            $thisDurationhours = (int)($intDuration / 3600);
                            $Durationminutes = (int)(($intDuration % 3600) / 60);
							$intDurationInSecs	=	number_format((float)($intDuration / 3600), 2, '.', '');
                            $intAssigned = $arrRotaRecords['isassigned'];
                            $strStartTime = FormatTime($arrRotaRecords['starttime']);
							if($arrRotaRecords['endtime'] > 86400)
								$arrRotaRecords['endtime'] = $arrRotaRecords['endtime'] - 86400;
                            $strEndTime = FormatTime($arrRotaRecords['endtime']);
                        } else {
                            $intRotaDutyID = 0;
                            $intDutyID = 0;
                            $intDutyTypeID = 0;
                            $strDutyName = '';
                            $strBackColour = '';
                            $strFontColour = '';
                            $strStartDate = '';
                            $strEndDate = '';
                            $intDuration = 0;
                            $thisDurationhours = 0;
                            $strStartTime = '';
                            $strEndTime = '';
                        }
						if(strlen($strDutyName)<=20)
						{
							$strDutyNameDisplay	=	$strDutyName;
						}else
						{
							$strDutyNameDisplay	=	substr($strDutyName,0 ,20);
						}
                        if($iterationCount == 0)	
						{	
							$dblDuration = $dblDuration + $intDuration;	
						}
                        $widthDutyName = 'auto;';
                        $dutydurationVal = (float)($thisDurationhours . '.' . $Durationminutes);
                        $dutydurationValSum= $dutydurationValSum + $dutydurationVal;
                        $dutyduration = $dutydurationVal . ' Hours (' . $strStartTime . ' - ' . $strEndTime . ')';
                        $widthDutyDuration =  'auto;';
                        echo '<tr>';
                        echo '<td id="' . $intRotaDutyID . '" IsActive="'.$strBackColour.'" dayofrota="' . $intDay . '" dutyid="' . $intDutyID . '" dutytypeid="' . $intDutyTypeID . '" dayofrota="' . $intDay . '" rotaid="' . $intRotaID . '" weekid="' . $intWeek . '" style="background-color:#' . strval($strBackColour) . '; color:#' . strval($strFontColour) . ';" style="width: 30px">';

                        if ($intRotaTab > 0) {
                            echo '<div class="rotaduty-div" rotadutyid="' . $intRotaDutyID . '" dutyid="' . $intDutyID . '" rotaid="' . $intRotaID . '" lastmoddate="" dutytypeid="' . $intDutyTypeID . '">';
                        } else if ($permissions->canmodify == 1) {
                            echo '<div class="rotaselectedduty-context-menu" rotadutyid="' . $intRotaDutyID . '" dutyid="' . $intDutyID . '" rotaid="' . $intRotaID . '" weekid="' . $intWeek . '" lastmoddate="" dutytypeid="' . $intDutyTypeID . '">';
                        } else {
                            echo '<div class="rotaduty-div" rotadutyid="' . $intRotaDutyID . '" dutyid="' . $intDutyID . '" rotaid="' . $intRotaID . '" lastmoddate="" dutytypeid="' . $intDutyTypeID . '">';
                        }
                        if ($intShowAll == 1) {
                            if ($intDutyTypeID == 1) {
                                if (($strStartDate != '') && ($intDuration > 0)) {
                                    echo '<div title="'. $strDutyName .'" style="width:' . $widthDutyName . '">' . $strDutyNameDisplay . '</div><div style="width:' . $widthDutyDuration . '"> ' . $strStartTime . ' - ' . $strEndTime . '&nbsp;(' . $intDurationInSecs . ')</div>';
                                } else {
                                    echo $strDutyNameDisplay;
                                }
                            }
                            else {
                                if (($strStartDate != '') && ($intDuration > 0)) {
                                    echo '<div title="'. $strDutyName .'" style="width:' . $widthDutyName . '">' . $strDutyNameDisplay . '</div><div style="width:' . $widthDutyDuration . '"> ' . $intDurationInSecs . ' Hours</div>';
                                } else {
                                    echo $strDutyNameDisplay;
                                }
                            }
                        } else if (($strStartDate != '') && ($intDuration > 0)) {
                            if ($intDutyTypeID == 1) {
                                 echo '<div title="'. $strDutyName .'" style="width:' . $widthDutyName . '">' . $strDutyNameDisplay . '</div><div style="width:' . $widthDutyDuration . '">' . $strStartTime . ' - ' . $strEndTime . '&nbsp;(' . $intDurationInSecs . ')</div>';
                            }
                            else {
                                echo '<div title="'. $strDutyName .'" style="width:' . $widthDutyName . '">' . $strDutyNameDisplay . '</div><div style="width:' . $widthDutyDuration . '"> ' . $intDurationInSecs . ' Hours</div>';
                            }
                        } else {
                            echo $strDutyNameDisplay;
                            echo '<div style="width:' . $widthDutyDuration . '">' . $intDurationInSecs . ' Hours</div>';
                        }

                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';
						$iterationCount++;
                    }
                    echo '</table>';
                    echo '</td>';
                } else {
                    $intRotaDutyID = 0;
                    $intDutyID = 0;
                    $intDutyTypeID = 0;
                    $strDutyName = '';
                    $strBackColour = '';
                    $strFontColour = '';
                    $strStartDate = '';
                    $strEndDate = '';
                    $intDuration = 0;

                    echo '<td class="' . $classname . '" id="' . $intRotaDutyID . '" dayofrota="' . $intDay . '" dutyid="' . $intDutyID . '" dutytypeid="' . $intDutyTypeID . '" dayofrota="' . $intDay . '" rotaid="' . $intRotaID . '" weekid="' . $intWeek . '" style="background-color:#' . strval($strBackColour) . '; color:#' . strval($strFontColour) . ';" style="width: 80px">';
                    if ($intRotaTab > 0) {
                        echo '<div class="rotaduty-div" rotadutyid="' . $intRotaDutyID . '" dutyid="' . $intDutyID . '" rotaid="' . $intRotaID . '" lastmoddate="" dutytypeid="' . $intDutyTypeID . '">';
                    } else if ($permissions->canmodify == 1) {
                        echo '<div class="rotaduty-div" rotadutyid="' . $intRotaDutyID . '" dutyid="' . $intDutyID . '" rotaid="' . $intRotaID . '" weekid="' . $intWeek . '" lastmoddate="" dutytypeid="' . $intDutyTypeID . '">';
                    } else {
                        echo '<div class="rotaduty-div" rotadutyid="' . $intRotaDutyID . '" dutyid="' . $intDutyID . '" rotaid="' . $intRotaID . '" lastmoddate="" dutytypeid="' . $intDutyTypeID . '">';
                    }

                    echo '<div  rotadutyid="' . $intRotaDutyID . '" dutyid="' . $intDutyID . '" rotaid="' . $intRotaID . '" weekid="' . $intWeek . '" lastmoddate="" dutytypeid="' . $intDutyTypeID . '">' . $strDutyName . '</div>';
					echo '<div>&nbsp;</div><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>';
                    echo '</div>';
                    echo '</td>';
                }

            }
			$totalDuration	+=	$dblDuration;
            echo '<td class="' . $classname . '" style="width: 30">';
            if ($intShowAll == 1) {
                echo '&nbsp;';
            } else {
                echo number_format((float)($dblDuration / 3600), 2, '.', '');
            }
            echo '</td>';
            echo '</tr>';
        }
		$totalHours		=	number_format((float)($totalDuration/3600), 2, '.', '');
		$avgHours		=	number_format((float)(($totalDuration/$intWeeksInRota)/3600), 2, '.', '');
		$eftAvgHours		=	number_format((float)(($avgHours)/35), 3, '.', '');
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
?>


<script type="text/javascript">
    $(document).ready(function () {
        if (<?php echo empty($permissions->canview) ? 0 : $permissions->canview ?> == 0
    )
        {
            $('#content').load('page-includes/no_access.php', function () {
            });
        }
    else
        {
			$("#hoursContainer").html('<?php echo isset($totalHours) && $totalHours?$totalHours:0; ?>');
			$("#avgContainer").html('<?php echo  isset($avgHours) && $avgHours?$avgHours:0; ?>');
			$("#eftContainer").html('<?php echo  isset($eftAvgHours) && $eftAvgHours?$eftAvgHours:0; ?>');

			if(!($("#rotapeopletabs").height())){
				$("#rotadutieslist0").height($("#rotadutiesContainer").height() / 2 - 64);
			} else {
				if(!($("#rotapeoplelistdiv1").height())){
				  $("#rotapeoplelistdiv0").height($("#rotapeopletabs").height() - 42);
				  $("#rotadutieslist0").height($("#rotapeoplelistdiv0").height() / 2 - 22);
				} else {	
				  $("#rotapeoplelistdiv0").height(($("#rotapeopletabs").height() - $("#rotapeoplelistdiv1").height()) - 42);
				  $("#rotadutieslist0").height($("#rotapeoplelistdiv0").height() - 57);	
				}				
			}


            $("#effectivedate").datepicker({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                dateFormat: "dd/mm/yy"
            });

            var showall = $("#ddlViewAll option:selected").attr("value");
            if (showall > 0) {
                $('#effectivedate').hide();
            } else {
                $('#effectivedate').show();
            }

            var tabCookieName = "rotatabs";
            var tabID = ($.cookie(tabCookieName) || 0);
            var rotaid = <?=$intRotaID?>;
            var rotalineid = <?=$intRotaLineID?>;

            if (tabID == 0) {
                $("td.rotatabledroppable").droppable({
                    tolerance: "pointer",
                    over: function (event, ui) {
                        $(this).addClass('highlighted');
                    },
                    out: function (event, ui) {
                        $(this).removeClass('highlighted');
                    },
                    drop: function (event, ui) {
                        $(this).removeClass('highlighted');
                        var dutyid = ui.draggable.attr("dutyid");
                        var dutyname = ui.draggable.attr("dutyname");
                        var dutytypeid = ui.draggable.attr("dutytypeid");
                        var dutybackcolour = ui.draggable.css("background-color");
                        var dutyforecolour = ui.draggable.css("color");
                        var dayofrota = $(this).attr("dayofrota");
                        var dropdutyid = $(this).attr("dutyid");
                        var dropdutytypeid = $(this).attr("dutytypeid");
                        var dropisassigned = $(this).attr("isassigned");
                        var rotaid = $(this).attr("rotaid");
                        var weekid = $(this).attr("weekid");
                        var blUpdate = 0;
                        var blnoreplace = 0;
                        var isassigned;
                        var istemplate;

                        // Validation for rota duty start date and weeks
                        validationForDiffDateweekRota(dutytypeid, dayofrota, dutyid,rotaid,
						function(){
                            switch (dutytypeid) {
                                case "1":
                                    isassigned = 1;
                                    istemplate = 0;
                                    break;
                                case "2":
                                    isassigned = 0;
                                    istemplate = 1;
                                    break;
                                case "3":
                                    isassigned = 0;
                                    istemplate = 1;
                                    break;
                                case "4":
                                    isassigned = 1;
                                    istemplate = 0;
                                    break;
                                case "5":
                                    isassigned = 1;
                                    istemplate = 0;
                                    break;
                            }
                            blUpdate = 1;
                            $.ajax({
                                url: "page-includes/rotas/dropduty.php",
                                type: "POST",
                                dataType: "json",
                                async: false,
                                data: {
                                    'RotaID': rotaid,
                                    'WeekID': weekid,
                                    'DayOfRota': dayofrota,
                                    'DutyID': dutyid,
                                    'DutyTypeID': dutytypeid,
                                    'IsAssigned': isassigned,
                                    'IsTemplate': istemplate
                                },
                                success: function (data) {
                                    if (data.status == 'success') {
                                        //all good
                                        if (data.sqlstatus == 1) {
                                            ShowRotaDetails(rotaid, weekid);
                                        } else {
                                            customAlert(data.sqlstatusstring);
                                        }
                                    } else {
                                        customAlert(data.status);
                                    }
                                },
                                error: function (x, e) {
                                    if (x.status == 0) {
                                        customAlert('You are offline!!<br/> Please Check Your Network.');
                                    } else if (x.status == 404) {
                                        customAlert('Requested URL not found.');
                                    } else if (x.status == 500) {
                                        customAlert('Internal Server Error.');
                                    } else if (e == 'parsererror') {
                                        customAlert('Error.<br/>Parsing JSON Request failed.');
                                    } else if (e == 'timeout') {
                                        customAlert('Request Time out.');
                                    } else {
                                        customAlert('Unknown Error.<br/>' + x.responseText);
                                    }
                                }
                            });
						},
						function() {
							return false;
						});
                        
                    }
                });

                $('#rotapeoplelistsdiv0').hide();
                $('#rotadutieslist0').show();
                $('#rotadetails2').css("margin-top", "0px");

            } else {

                var $peopletabs = $('#rotapeopletabs').tabs();
                var peopleselected = $peopletabs.tabs('option', 'active');

                if (peopleselected == 0) {
                    $('#rotapeoplelistsdiv0').show();
                    $('#rotadetails2').css("margin-top", "0px");
                    ShowPeopleByRota(rotaid);
                    ShowRotaPeople(0);
                } else {
                    $('#rotapeoplelistsdiv0').hide();
                    $('#rotadutieslist0').show();
                    $('#rotadetails2').css("margin-top", "0px");
                }
            }

            $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
            $(function () {
                $("button").button()
            });


            $(function () {
                $(".selector").selectmenu({
                    change: function (event, ui) {
						event.stopImmediatePropagation();
                        var value = $(this).val();
                        ShowRotaDetails(value, 1);
                    }
                });
            });

        }
    });


    function ShowPeopleByRota(rotaid) {
        $.ajax({
            type: 'POST',
            url: "page-includes/rotas/list-peoplebyrota.php",
            data: {
                "id": rotaid,
                "teamId": <?php echo $intTeamID; ?>
            },
            success: function (data) {
                $('#rotapeoplelistsdiv1').html(data);
            },
        });
    }

    function ShowRotaPeople(StaffFlag = 0) {
        var teamid = $.cookie("masterdutyteams") == undefined ? 0 : parseInt($.cookie("masterdutyteams"));
        $.ajax({
            type: 'POST',
            url: "page-includes/rotas/list-teampeople.php",
            data: {
                "id": teamid,
                "StaffFlag": StaffFlag
            },
            success: function (data) {
                $('#rotapeoplelistsdiv2').html(data);
            },
        });
    }

    function NewRota() {
        let ddlRotaTeamsValue   =   $('#ddlRotaTeams').val();
        $.ajax({
            type: 'POST',
            url: "page-includes/rotas/edit-rota.php",
            data: {
                "id": 0,
                "task": 0,
                "ddlRotaTeamsValue":ddlRotaTeamsValue
            },
            success: function (data) {
                $.facebox(data);
            }
        });
    }

    function EditRota() {
        var id = $("#ddlRotaName option:selected").attr("value");
        $.ajax({
            type: 'POST',
            url: "page-includes/rotas/edit-rota.php",
            data: {
                "id": id,
                "ddlRotaTeamsValue": $('#ddlRotaTeams').val(),
                "task": 1
            },
            success: function (data) {
                $.facebox(data);
            }
        });
    }

    function CopyRota() {
        var id = $("#ddlRotaName option:selected").attr("value");
        $.ajax({
            type: 'POST',
            url: "page-includes/rotas/edit-rota.php",
            data: {
                "id": id,
                "ddlRotaTeamsValue": $('ddlRotaTeams').val(),
                "task": 2
            },
            success: function (data) {
                $.facebox(data);
            }
        });
    }

    function DeleteRota() {
        var rotaid = $("#ddlRotaName option:selected").attr("value");

		customConfirm('Are you sure want to delete this Rota Pattern?',function(){
				
            $.ajax({
                url: "page-includes/rotas/deleterota.php",
                type: "POST",
                dataType: "json",
                data: {
                    'RotaID': rotaid,
                    "teamId": $('#ddlRotaTeams').val()
                },
                success: function (data) {
                    if (data.status == 'success') {
                        //all good

                        if (data.sqlstatus != 1) {
                            customAlert(data.sqlstatusstring);
                        } else {
                            ShowRotaDetails(0, 1);

                        }
                    } else if (data.status == 'fail') {
                        //there was a problem

                        if (data.sqlstatus != 1) {
                            customAlert(data.sqlstatusstring);
                        } else {
                            customAlert('There was a problem deleting the Rota Pattern.');
                        }
                    } else {
                        customAlert(data.status);
                    }
                },
                error: function (x, e) {
                    if (x.status == 0) {
                        customAlert('You are offline!!\n Please Check Your Network.');
                    } else if (x.status == 404) {
                        customAlert('Requested URL not found.');
                    } else if (x.status == 500) {
                        customAlert('Internal Server Error.');
                    } else if (e == 'parsererror') {
                        customAlert('Error.\nParsing JSON Request failed.');
                    } else if (e == 'timeout') {
                        customAlert('Request Time out.');
                    } else {
                        customAlert('Unknown Error.\n' + x.responseText);
                    }
                }

            });
        
			},
			function() {
				return false;
			}
		);
    }

    function DeleteRotaDuty(rotadutyid, rotaid, dutyid, weekid, lastmoddate, dutytypeid) {
        var tab = $("#rotadutiestabs .ui-state-active").attr('data-listtype');
       
        $.ajax({
            url: "page-includes/rotas/removeduty.php",
            type: "POST",
            dataType: "json",
            data: {
                'RotaID': rotaid,
                'DutyID': dutyid,
                'RotaDutyID': rotadutyid,
                'LastModDate': lastmoddate,
                'teamId' :  $('#ddlRotaTeams').val()
            },
            success: function (data) {
                if (data.status == 'success') {
                    //all good
                    tab = parseInt(tab);
                    if (data.sqlstatus == 1) {
                        ShowRotaDetails(rotaid, weekid);
                        return;
                    } else {
                        customAlert(data.sqlstatusstring);
                        return;
                    }
                } else {
                    customAlert(data.status);
                }
            },
            error: function (x, e) {
                if (x.status == 0) {
                    customAlert('You are offline!!<br/> Please Check Your Network.');
                } else if (x.status == 404) {
                    customAlert('Requested URL not found.');
                } else if (x.status == 500) {
                    customAlert('Internal Server Error.');
                } else if (e == 'parsererror') {
                    customAlert('Error.<br/>Parsing JSON Request failed.');
                } else if (e == 'timeout') {
                    customAlert('Request Time out.');
                } else {
                    customAlert('Unknown Error.<br/>' + x.responseText);
                }
            }

        });
    }


    function ShowRota(blShowRota) {
        if (blShowRota == 0) {
            $('#rotadutieslist0').hide();
        } else {
            $('#rotadutieslist0').show();
        }
    }

    function ShowTemplateView(rotaid, showtemplates) {
        var CookieName = "rotashowtemplates";
        $.cookie(CookieName, showtemplates, {expires: 1});

        var tabCookieName = "rotadutiestabs";
        var tabID = ($.cookie(tabCookieName) || 0);
        if (showtemplates == 0) {
            if ((tabID == 1) || (tabID == 2)) {
                $("#rotadutiestabs").tabs({
                    active: 0,
                    activate: function (event, ui) {
                        var newIndex = ui.newTab.parent().children().index(ui.newTab);
                        $.cookie(tabCookieName, newIndex, {expires: 1});
                    }
                });
            }
            ShowRotaDetails(rotaid, 1);
        } else if (showtemplates == 1) {
            if ((tabID == 0) || (tabID == 3) || (tabID == 4)) {
                $("#rotadutiestabs").tabs({
                    active: 1,
                    activate: function (event, ui) {
                        var newIndex = ui.newTab.parent().children().index(ui.newTab);
                        $.cookie(tabCookieName, newIndex, {expires: 1});
                    }
                });
            }
            ShowRotaDetails(rotaid, 1);
        } else {
            ShowRotaDetails(rotaid, 1);
        }

    }

    function ShowAll(rotaid, showall) {
        var tabCookieName = "rotashowall";
        $.cookie(tabCookieName, showall, {expires: 1});

        if (showall > 0) {
            $('#effectivedate').hide();
        } else {
            $('#effectivedate').show();
        }
         ShowRotaDetails(rotaid, 1);
    }

    function ChangeDate(rotaid, selecteddate) {

        var tabCookieName = "rotaeffdate";
        $.cookie(tabCookieName, selecteddate, {expires: 1});

        var tabCookieName2 = "rotapeopleeffdate";
        $.cookie(tabCookieName2, selecteddate, {expires: 1});

        ShowRotaDetails(rotaid, 1);
    }

    function ShowRotaBreaks(rotaid, showbreaks) {
        //var showtemplates = $("#ddlViewTemplate option:selected").attr("value");
        var tabCookieName = "rotashowbreaks";
        $.cookie(tabCookieName, showbreaks, {expires: 1});

        ShowRotaDetails(rotaid, 1);
    }

    function EditDuty(rotadutyid, rotaid, dutyid) {
        if (rotadutyid > 0) {
            $.ajax({
                type: 'POST',
                url: "page-includes/rotas/edit-duty.php",
                data: {
                    "rotadutyid": rotadutyid,
                    "rotaid": rotaid,
                    "dutyid": dutyid
                },
                success: function (data) {
                    $.facebox(data);
                }
            });
        }
    }

    if (<?php echo empty($permissions->candelete) ? 0 : $permissions->candelete ?> == 1
    )
    {
        $(function () {
            $.contextMenu({
                selector: '.rotaselectedduty-context-menu',
                items: {
                    "delete": {
                        name: "Remove Duty from Rota",
                        icon: "delete",
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var rotadutyid = options.$trigger.attr("rotadutyid");
                            var dutytypeid = options.$trigger.attr("dutytypeid");
                            var dutyid = options.$trigger.attr("dutyid");
                            var rotaid = options.$trigger.attr("rotaid");
                            var lastmoddate = options.$trigger.attr("lastmoddate");
                            var weekid = options.$trigger.attr("weekid");

                            DeleteRotaDuty(rotadutyid, rotaid, dutyid, weekid, lastmoddate, dutytypeid);
                        }
                    },
                }
            })
        });
    }

    function updateChoosenSelect(element, teamId = 0) {
        var chosen = $(element);
        chosen.empty();

        $.post("page-includes/rotas/get-master-rotas.php", {
            teamId : teamId
        }, function(response) {
            var rotas = $.parseJSON(response);
            chosen.append($('<option value="0">Select an Option</option>'));
            $.each(rotas, function(key,rota){
                var newOption = $('<option value="'+rota.rotaid+'">'+rota.RotaName+'</option>');
                chosen.append(newOption);
            });
            chosen.trigger("chosen:updated");
        });

        $('#loading').slideUp();
    }

    function validationForDiffDateweekRota(dutytypeid, dayofrota, dutyid, rotaid, yesCB, noCB) {
		if(dutytypeid == 1)
		{
			$.ajax({
				url: "page-includes/rotas/Validate-Rotaduty.php",
				type: "POST",
				dataType: "json",
				async: false,
				data: {
					rotaid: rotaid,
					dutyid: dutyid,
                    dayofrota: dayofrota,
                    "teamId": $('#ddlRotaTeams').val()
				},
				success: function (data) {
					if (data.sqlstatus == 2){
					    customAlert('This Duty is not configured to be available on this day.');
                    }
					else if (data.sqlstatus == 0){
						customConfirm('Warning - "This duty is already being used for rota pattern '+data.strConflictRotaName+', are you sure to use the same?',function(){
								yesCB();
							},
							function() {
								noCB();
							}
						);
					}else{
						yesCB();
					}
				}
			});
		}else{
			yesCB();
		}
    }
function focusRotaLine(currid)
{
	if (currid != 0) {
		var strid = "#w" + currid;
		var rotabodytop = $('#rotadutieslist0').offset().top;
		var pos = $(strid).offset().top - rotabodytop - 50;
		$('#rotadutieslist0').animate({
				scrollTop: pos
			},
			'fast');
	}
}

function setRotaScrollPos()
{
	let top = $('#rotaScrollPos').position().top;
    $.cookie("rotaPosTop", top);
}
</script>
