<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/init.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/helpers.php';
include_once '../../class-includes/userRolePermissions.php';
include_once 'setTeamCookieRotas.php';
$strSessionAdditionalFields = 'JobsUserFields';
$windowheightoffset = 280;

$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$currid = $_REQUEST["id"] ?? 0;
$intListType = $_REQUEST["listtype"] ?? 0;
$strSearchText = $_REQUEST["searchtext"] ?? '';
$intArchived = $_REQUEST["archived"] ?? 0;
$teamId = !empty($_REQUEST['teamId']) ? $_REQUEST['teamId'] : 0;
$intHourWidth = 80;
$intDutyHeight = 30;
$currenttop = 35;
$earlieststart = 0;
$lateststart = 24;
$intDutyTypeID = 1;

$pageid = 24;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $teamId);

if (($permissions->canmodify == 1) || ($permissions->cancreate == 1)) {
    $classname = 'draggable';
}
else {
    $classname = 'notdraggable';
}

if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
}else{
    $strTeams = 0;
}

$intAreaID = $_SESSION['user']['AreaID'];
$rsDutiesJson = ListAllMasterDutiesForRota($intAreaID, $intDutyTypeID, 0, $strSearchText, $strTeams);
$rsDuties = json_decode($rsDutiesJson,true);

$rsDuties[0]['MasterDutyID'] = isset($rsDuties[0]['MasterDutyID']) ? $rsDuties[0]['MasterDutyID'] : 0;

if (isset($rsDuties) && $rsDuties[0]['MasterDutyID'] > 0) {
    $countArr = count($rsDuties);
    for($row = 0; $row < $countArr; $row++) {
        $intThisEnd = ceil($rsDuties[$row]['EndTime'] / 3600);
        if ($intThisEnd > $lateststart) {
            $lateststart = $intThisEnd;
        }
        $intThisStart = floor($rsDuties[$row]['StartTime'] / 3600);
        if ($intThisStart < $earlieststart) {
            $earlieststart = $intThisStart;
        }
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['DutyName'] = $rsDuties[$row]['DutyName'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['StartTime'] = $rsDuties[$row]['StartTime'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['EndTime'] = $rsDuties[$row]['EndTime'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['Duration'] = $rsDuties[$row]['Duration'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['WindowDuration'] = $rsDuties[$row]['WindowDuration'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['StartDate'] = $rsDuties[$row]['StartDate'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['EndDate'] = $rsDuties[$row]['EndDate'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['backcolour'] = $rsDuties[$row]['ColourBackground'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['forecolour'] = $rsDuties[$row]['ColourFont'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['dotw'] = $rsDuties[$row]['dotw'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['lastmoddate'] = $rsDuties[$row]['LastModDate'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['dutyhasjobs'] = 1;
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d0'] = $rsDuties[$row]['Sat'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d1'] = $rsDuties[$row]['Sun'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d2'] = $rsDuties[$row]['Mon'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d3'] = $rsDuties[$row]['Tue'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d4'] = $rsDuties[$row]['Wed'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d5'] = $rsDuties[$row]['Thu'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['d6'] = $rsDuties[$row]['Fri'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count0'] = $rsDuties[$row]['CountSat'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count1'] = $rsDuties[$row]['CountSun'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count2'] = $rsDuties[$row]['CountMon'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count3'] = $rsDuties[$row]['CountTue'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count4'] = $rsDuties[$row]['CountWed'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count5'] = $rsDuties[$row]['CountThu'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['count6'] = $rsDuties[$row]['CountFri'];
		/*
        if (!is_null($rsDuties[$row]['JobID'])) {
			$rsDuties[$row]['id'] = isset($rsDuties[$row]['id']) ? $rsDuties[$row]['id'] : '';
            $arrDuties[$rsDuties[$row]['id']]['jobs'][$rsDuties[$row]['JobID']]['JobTypeID'] = $rsDuties[$row]['JobTypeID'];
            $arrDuties[$rsDuties[$row]['id']]['jobs'][$rsDuties[$row]['JobID']]['Job'] = $rsDuties[$row]['Job'];
            $arrDuties[$rsDuties[$row]['id']]['jobs'][$rsDuties[$row]['JobID']]['JobStartTime'] = $rsDuties[$row]['JobStartTime'];
            $arrDuties[$rsDuties[$row]['id']]['jobs'][$rsDuties[$row]['JobID']]['JobEndTime'] = $rsDuties[$row]['JobEndTime'];
            if (is_null($rsDuties[$row]['JobBackColour'])) {
                $arrDuties[$rsDuties[$row]['id']]['jobs'][$rsDuties[$row]['JobID']]['JobBackColour'] = 'dddddd';
            }
            else {
                $arrDuties[$rsDuties[$row]['id']]['jobs'][$rsDuties[$row]['JobID']]['JobBackColour'] = $rsDuties[$row]['JobBackColour'];
            }
            if (is_null($rsDuties[$row]['JobForeColour'])) {
                $arrDuties[$rsDuties[$row]['id']]['jobs'][$rsDuties[$row]['JobID']]['JobForeColour'] = '000000';
            }
            else {
                $arrDuties[$rsDuties[$row]['id']]['jobs'][$rsDuties[$row]['JobID']]['JobForeColour'] = $rsDuties[$row]['JobForeColour'];
            }
        }*/
    }
    if (isset($arrDuties)) {

        // The hours in the day....
        echo '<div style="overflow: hidden; position: absolute; width:940px; height:30px; left:300px; top:'.$currenttop.'px" id="top" class="times">';
        for ($i=$earlieststart; $i <= ($lateststart + $earlieststart); $i++){
            echo '<div style="width:'.$intHourWidth.'px;  position:absolute; left:'.(($i - $earlieststart) * $intHourWidth).'px; top:0px">';
            echo '<div class="highlighted" style="position: absolute; left: 0px; top:0px; width:'.$intHourWidth.'px; height:15px">'.(gmdate("H:00",  $i * 3600)).'</div>';
            echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
            echo '</div>';
        }
        echo '</div>';


        $currenttop = $currenttop + 30;
        // ################################################################ First a div with the Duty names


        $intCounter = 0;
        $dayletter = array('S','S','M','T','W','T','F');
        echo '<div id="daynames" style="overflow: hidden; position: absolute; width:300px; height:22px; left:0px; top:'.($currenttop-22).'px" >';
        echo '<input id="searchdutyname" name="searchdutyname" type="text" style="height:15px; top:0px; width:120px" size="20" value="'.$strSearchText.'" />';
        echo '&nbsp;<i class="fa fa-search buttonEnabled" id="searchMasterDuty"></i>';
        for ($intDOTW = 0; $intDOTW <= 6; $intDOTW++) {
            if ($intDOTW == 4) {
                echo '<div style="overflow:hidden; position: absolute; width:9px; height:10px; font-size:9px; left:'.(210 + ($intDOTW * 10)).'px; top:2px;">';
            }
            else {
                echo '<div style="overflow:hidden; position: absolute; width:9px; height:10px; font-size:9px; left:'.(210 + ($intDOTW * 10)).'px; top:2px;">';
            }
            echo $dayletter[$intDOTW];
            echo '</div>';
        }
        echo '</div>';
        echo '<div style="overflow: scroll; position: absolute; width:300px; height:170px; left:0px; right:0px; top:'.$currenttop.'px" id="names">';
        foreach ($arrDuties as $intDutyID => $arrDuty) {
			$arrDuty['DutyName'] = isset($arrDuty['DutyName']) ? $arrDuty['DutyName'] : '';
            echo '<div class="rotaduty-context-menu" title = "'.$arrDuty['DutyName'].'" dutyid="'.$intDutyID.'" id="'.$intDutyID.'" style="background-color: #99bfe6; word-break: break-all; font-size: 10px; position: absolute; width:280px; height:'.($intDutyHeight - 1).'px; left:0px; top:'.($intCounter * $intDutyHeight).'px" >';
            echo  '<b>'.trim(substr($arrDuty['DutyName'],0,31)).'</b>';
            echo '<br>';
			$arrDuty['Duration'] = isset($arrDuty['Duration']) ? $arrDuty['Duration'] : 0;
            echo '('.number_format((float)($arrDuty['Duration'] / 3600), 2, '.', '').' Hours)';
			$arrDuty['dotw'] = isset($arrDuty['dotw']) ? $arrDuty['dotw'] : '';
            $arrDoTW = ExplodeStringToArray($arrDuty['dotw']);
            for ($intDOTW = 0; $intDOTW <= 6; $intDOTW++) {
                if (isset($arrDoTW[$intDOTW])) {
                    if ($arrDuty['count'.$intDOTW] == 0) {
                        echo '<div class="greenDutyBox" style="overflow:hidden; position:absolute; width:10px; height:'.($intDutyHeight - 1).'px; left:'.(210 + ($intDOTW * 10)).'px; top:0px; border:1px solid green;">';
                        echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:'.($intDutyHeight - 1).'px;top:30%;" title="'.$arrDuty['d'.$intDOTW].' '.($arrDuty['d'.$intDOTW] > 1 ? 'Duties' : 'Duty').' required on this day.
The number of times this Duty has been used in any Rota pattern on this day is ['.$arrDuty['count'.$intDOTW].'] "></span>';
                    }
                    else {
                        echo '<div class="orange" style="overflow:hidden; position:absolute; width:10px; height:'.($intDutyHeight - 1).'px; left:'.(210 + ($intDOTW * 10)).'px; top:0px; border:1px solid orange;">';
                        echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:'.($intDutyHeight - 1).'px;top:30%;" title="'.$arrDuty['d'.$intDOTW].' '.($arrDuty['d'.$intDOTW] > 1 ? 'Duties' : 'Duty').' required on this day. 
The number of times this Duty has been used in any Rota pattern on this day is ['.$arrDuty['count'.$intDOTW].'] "></span>';
                    }
                }
                else {
                    echo '<div class="grey" style="overflow:hidden; position:absolute; width:10px; height:'.($intDutyHeight - 1).'px; left:'.(210 + ($intDOTW * 10)).'px; top:0px; border:1px solid grey;">';
                    echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:'.($intDutyHeight - 1).'px;top:30%;" title="Duty not available on this day."></span>';
                }
                echo '</div>';
            }


            echo '</div>';
            $intCounter++;
        }
        echo '</div>';
        // ################################################################ End the Duty Names

        // ################################################################ First a div with the Duties and Jobs

        $currenttop = $currenttop - 10;
        $intCounter = 0;
        echo '<div class="compact stripe bluetable" style="overflow:scroll; position: absolute; width:940px; height:170px; left:300px; top:'.$currenttop.'px" id="duties" onScroll="setDutyListingRotaScrollPos()">';
        echo '<div id="dutyListingRotaPos"  name="dutyListingRotaPos"></div>';
        foreach ($arrDuties as $intDutyID => $arrDuty) {
            // a holder for everythnig
			$arrDuty['DutyName'] = isset($arrDuty['DutyName']) ? $arrDuty['DutyName'] : '';
			$arrDuty['backcolour'] = isset($arrDuty['backcolour']) ? $arrDuty['backcolour'] : '';
            echo '<div class="rotaduty-context-menu" title = "'.$arrDuty['DutyName'].'" dutyid="'.$intDutyID.'" style="height:'.($intDutyHeight).'px; position:absolute; top:'.($intCounter * $intDutyHeight).'px; background-color:#'.strval($arrDuty['backcolour']).'">';
            // Draw in the Times
            drawtimecells("2015-01-01", $earlieststart, $lateststart, $intHourWidth, $intDutyHeight-1);
			$arrDuty['StartTime'] = isset($arrDuty['StartTime']) ? $arrDuty['StartTime'] : 0;
			$arrDuty['EndTime'] = isset($arrDuty['EndTime']) ? $arrDuty['EndTime'] : 0;
			$arrDuty['forecolour'] = isset($arrDuty['forecolour']) ? $arrDuty['forecolour'] : '';
            $intWidth = (($arrDuty['EndTime'] - $arrDuty['StartTime']) / 3600) * $intHourWidth;
            $left = ($arrDuty['StartTime'] / 3600) * $intHourWidth;
            echo '<div class="'.$classname.'" dutyid="'.$intDutyID.'" dutyname="'.$arrDuty['DutyName'].'" dutytypeid="1" style="border: 1px solid #C0C0C0; text-align:center; width: '.$intWidth.'px; height:'.($intDutyHeight - 8).'px; position:absolute; overflow:hidden; white-space:nowrap; left:'.$left.'px; top:4px; background-color:#'.strval($arrDuty['backcolour']).'"; color:#'.strval($arrDuty['forecolour']).';">';
            if (!(isset($arrDuty['jobs']))) {
                echo trim($arrDuty['DutyName']);
            }
            if (isset($arrDuty['jobs'])) {
                foreach ($arrDuty['jobs'] as $intJobID => $arrJob) {
                    $intJobWidth = (($arrJob['JobEndTime'] - $arrJob['JobStartTime'])  / 3600) * $intHourWidth;
                    $intJobLeft = ($arrJob['JobStartTime']  / 3600) * $intHourWidth -$left;
                    echo '<div class="boxed" style="overflow:hidden; width:'.$intJobWidth.'px; height:'.($intDutyHeight - 15).'px; position:absolute; left:'.$intJobLeft.'px; top:3px; background-color:#'.$arrJob['JobBackColour'].'; color:#'.strval($arrJob['JobForeColour']).';">';
                    echo $arrJob['Job'];
                    echo '</div>';
                }
            }
            echo '</div>';
            echo '</div>';
            $intCounter++;

        }
        echo '</div>';

    }
    echo '<input type="hidden" name="tabNo" id="tabNo" value="0"';
    echo '<div id="drag_helper" style="width:50px;"></div>';
}
else {
    echo '<div style="overflow: hidden; position: absolute; width:900px; height:30px; left:300px; top:'.$currenttop.'px" id="top" class="times">';
    for ($i=$earlieststart; $i <= ($lateststart + $earlieststart); $i++){
        echo '<div style="width:'.$intHourWidth.'px;  position:absolute; left:'.(($i - $earlieststart) * $intHourWidth).'px; top:0px">';
        echo '<div class="highlighted" style="position: absolute; left: 0px; top:0px; width:'.$intHourWidth.'px; height:15px">'.(gmdate("H:00",  $i * 3600)).'</div>';
        echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
        echo '</div>';
    }
    echo '</div>';
    $intCounter = 0;
    $prevDutyID = 0;
    $dayletter = array('S','S','M','T','W','T','F');
    echo '<div id="daynames" style="overflow: hidden; position: absolute; width:300px; height:22px; left:0px; top:'.($currenttop + 5).'px" >';
    echo '<input id="searchdutyname" name="searchdutyname" type="text" style="height:15px; top:0px; width:120px" size="20" value="'.$strSearchText.'" />';
    echo '&nbsp;<i class="fa fa-search buttonEnabled" id="searchMasterDuty"></i>';
    for ($intDOTW = 0; $intDOTW <= 6; $intDOTW++) {
        if ($intDOTW == 4) {
            echo '<div style="overflow:hidden; position: absolute; width:9px; height:10px; font-size:9px; left:'.(210 + ($intDOTW * 10)).'px; top:2px;">';
        }
        else {
            echo '<div style="overflow:hidden; position: absolute; width:9px; height:10px; font-size:9px; left:'.(210 + ($intDOTW * 10)).'px; top:2px;">';
        }
        echo $dayletter[$intDOTW];
        echo '</div>';
    }
    echo '</div>
    <div style="overflow: scroll; position: absolute; width:300px; height:170px; left:0px; right:0px; top:'.($currenttop + 30).'px" id="names">No Record Found</div>';
    echo '<div class="compact stripe bluetable" style="overflow:scroll; position: absolute; font-size: 12px; width:940px; height:170px; left:300px; top:'.($currenttop + 20).'px" id="duties">No Record Found</div>';
}

?>

<script type="text/javascript">
    adjusttabsheight();
    $(document).ready(function(){
        if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
            $( '#content' ).load( 'page-includes/no_access.php', function() { });
        }
    else {
            $("#rotadutieslist").DataTable({
                paging: false,
                scrollY: 400,
                info:     false,
                stateSave: true,
                "initComplete": function( settings, json ) {
                    DoMDResize();
                }
            });

            if ($('#searchdutyname').length > 0) {
                var searchinput = $('#searchdutyname');
                var intlength = $('#searchdutyname').val().length * 2;
                searchinput[0].setSelectionRange(intlength, intlength);
            }
            $('#searchMasterDuty').on("click", function() {
                var strInput = $('#searchdutyname').val().trim();
                $.cookie("searchMasterDuty", strInput);
                ListRotaDuties(-1, 0, $.cookie("searchMasterDuty"));
            });
            $("#searchdutyname").keyup(function(event) {
                if (event.keyCode === 13) {
                    $("#searchMasterDuty").click();
                }
            });
        }
    });



    $(function() {
        $(".draggable" ).draggable({
            cursor: "move",
            appendTo: '#drag_helper',
            revert: "invalid",
            containment: "document",
            helper: "clone",
            zIndex: 100,
            cursorAt: {left:40, top:25},
            onStartDrag:function(){
                $(this).draggable('options').cursor = 'not-allowed';
                $(this).draggable('proxy').css('z-index',10);
            },
            onStopDrag:function(){
                $(this).draggable('options').cursor='move';
            }
        });
    });



    $('#duties').on('scroll', function () {
        $('#names').scrollTop($(this).scrollTop());
        $('#top').scrollLeft($(this).scrollLeft());
        $(".leaders").scrollLeft($(this).scrollLeft());
    });

    $('#names').on('scroll', function () {
        $('#duties').scrollTop($(this).scrollTop());
    });

    $(function(){
        $.contextMenu({
            selector: '.rotaduty-context-menu',
            items: {
                "details": {
                    name: "Duty Details",
                    icon: "comment",
                    // superseeds "global" callback
                    callback: function(key, options) {
                        var dutyid =  options.$trigger.attr("dutyid");
                        DutyDetails(dutyid, 0);
                    }
                },
                "history": {
                    name: "History",
                    icon: "history",
                    // superseeds "global" callback
                    callback: function(key, options) {
                        var dutyid =  options.$trigger.attr("dutyid");
                        History(dutyid);
                    }
                },
            }});
    });
    function History(id) {
        $.post("page-includes/master-duties/dutyHistory.php", {
                dutyID: id
            },
            function (data, status) {
                $.facebox(data);
            });
    }
    function setDutyListingRotaScrollPos()
    {
        let top = $('#dutyListingRotaPos').position().top;
        let left = $('#dutyListingRotaPos').position().left;
        $.cookie("dutyListingRotaPosTop", top);
        $.cookie("dutyListingRotaPosLeft", left);
    }
    function adjusttabsheight() {
        $("#duties").width($(window).width() - 335);
        $("#top").width($(window).width() - 335);
    }
</script>
