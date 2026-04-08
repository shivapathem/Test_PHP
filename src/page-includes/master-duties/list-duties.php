<?php
session_start();
include_once '../../function-includes/init.php';

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/helpers.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';

$strSessionAdditionalFields = 'JobsUserFields';
$windowheightoffset = 280;

$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$currid = $_REQUEST["id"] ?? 0;
$intListType = $_REQUEST["listtype"] ?? 0;

$intArchived = 0;
$intTeamID = $_REQUEST['teamId'] ?? 0;

//Check User Authentication
$pageid = 1;//Master duties form id

// Call User Permission function.


$intHourWidth = 80;
$intDutyHeight = 30;
$currenttop = 50;
$earlieststart = 0;
$lateststart = 24;
$intDutyID = $currid;
$intDutyTypeID = 1;
$strSearchText = '';

if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
}else{
    $rsAreaTeams = GetUserAreaTeamsList ($intUserID);
    $schedulingTeamIds = pluck(json_decode($rsAreaTeams), 'TeamID');
    $strTeams = implode(',', $schedulingTeamIds);
}
if($strTeams == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $strTeams);
}
if ($permissions->canmodify == 1) {
    $classname = 'dutydroppable';
} else {
    $classname = 'notdutydroppable';
}


$intAreaID = $_SESSION['user']['AreaID'];
$rsDutiesJson = ListAllMasterDutiesForRota($intAreaID, $intDutyTypeID, $intArchived, $strSearchText, $strTeams);
$rsDuties = json_decode($rsDutiesJson, true);
$earlieststart = 0;
$lateststart = 0;
if (empty($rsDuties)){
        echo "<p>There are no Master Duties for this team<p>";}
else {
    for ($row = 0; $row < count($rsDuties); $row++) {
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

        if (!is_null($rsDuties[$row]['JobID'])) {
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobTypeID'] = $rsDuties[$row]['JobTypeID'];
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['Job'] = $rsDuties[$row]['Job'];
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobStartTime'] = $rsDuties[$row]['JobStartTime'];
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobEndTime'] = $rsDuties[$row]['JobEndTime'];
            if (is_null($rsDuties[$row]['JobBackColour'])) {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobBackColour'] = 'dddddd';
            } else {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobBackColour'] = $rsDuties[$row]['JobBackColour'];
            }
            if (is_null($rsDuties[$row]['JobBackColour'])) {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobForeColour'] = '000000';
            } else {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobForeColour'] = $rsDuties[$row]['JobForeColour'];
            }
        } else {
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['dutyhasjobs'] = 0;
        }
    }
}

// The hours in the day....
echo '<div style="overflow: hidden; position: absolute; width:900px; height:30px; left:250px; top:' . $currenttop . 'px" id="top" class="times">';
for ($i = $earlieststart; $i <= ($lateststart + $earlieststart); $i++) {
    echo '<div style="width:' . $intHourWidth . 'px;  position:absolute; left:' . (($i - $earlieststart) * $intHourWidth) . 'px; top:0px">';
    echo '<div class="highlighted" style="position:absolute; left: 0px; top:0px; width:' . $intHourWidth . 'px; height:15px">' . (gmdate("H:00", $i * 3600)) . '</div>';
    echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
    echo '</div>';
}
echo '</div>';

$currenttop = $currenttop + 30;
// ################################################################ First a div with the Duty names

if (isset($arrDuties)) {
    $intCounter = 0;
    $prevDutyID = 0;
    $dayletter = array('S', 'S', 'M', 'T', 'W', 'T', 'F');
    echo '<div style="overflow: hidden; font-size:10px; position: absolute; width:250px; height:10px; left:0px; top:' . ($currenttop - 10) . 'px" id="daynames">';
    for ($intDOTW = 0; $intDOTW <= 6; $intDOTW++) {
        echo '<div style="overflow:hidden; position: absolute; width:9px; height:' . ($intDutyHeight - 1) . 'px; left:' . (169 + ($intDOTW * 10)) . 'px; top:0px">';
        echo $dayletter[$intDOTW];
        echo '</div>';
    }
    echo '</div>';
    echo '<div style="overflow: scroll; font-size:10px; position: absolute; width:250px; height:400px; left:0px; top:' . $currenttop . 'px" id="names">';

    foreach ($arrDuties as $intDutyID => $arrDuty) {
        if (($permissions->canview == 1) || ($permissions->canmodify == 1) || ($permissions->candelete == 1)) {
            if ($intArchived == 0) {
                echo '<div class="listduty-context-menu"  title = "' . $arrDuty['DutyName'] . '" id="' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="background-color: #99bfe6; position: absolute; width:230px; height:' . ($intDutyHeight - 1) . 'px; left:0px; top:' . ($intCounter * $intDutyHeight) . 'px;cursor: pointer" >';
            } else {
                echo '<div class="listdutyarchived-context-menu" id="' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="background-color: #99bfe6; position: absolute; width:230px; height:' . ($intDutyHeight - 1) . 'px; left:0px; top:' . ($intCounter * $intDutyHeight) . 'px" >';
            }
        } else {
            if ($intArchived == 0) {
                echo '<div class="listdutybasic-context-menu"   title = "' . $arrDuty['DutyName'] . '" id="' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="background-color: #99bfe6; position: absolute; width:230px; height:' . ($intDutyHeight - 1) . 'px; left:0px; top:' . ($intCounter * $intDutyHeight) . 'px" >';
            } else {
                echo '<div class="listdutyarchivedbasic-context-menu" title = "' . $arrDuty['DutyName'] . '" id="' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="background-color: #99bfe6; position: absolute; width:230px; height:' . ($intDutyHeight - 1) . 'px; left:0px; top:' . ($intCounter * $intDutyHeight) . 'px" >';
            }
        }
        echo '<b>' . trim(substr($arrDuty['DutyName'], 0, 30)) . '</b>';
        echo '<br>';
        echo '(' . round(($arrDuty['Duration'] / 3600), 2) . ' Hours)';

        $arrDoTW = ExplodeStringToArray($arrDuty['dotw']);
        for ($intDOTW = 0; $intDOTW <= 6; $intDOTW++) {
            if (isset($arrDoTW[$intDOTW])) {
                if ($arrDuty['count' . $intDOTW] == 0) {
                    echo '<div class="greenDutyBox" style="overflow:hidden; position:absolute; width:9px; height:' . ($intDutyHeight - 1) . 'px; left:' . (167 + ($intDOTW * 10)) . 'px; top:0px">';
                    echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:' . ($intDutyHeight - 1) . 'px;top:30%;" title="' . $arrDuty['d' . $intDOTW] . ' ' . ($arrDuty['d' . $intDOTW] > 1 ? 'Duties' : 'Duty') . ' available. ' . $arrDuty['count' . $intDOTW] . ' ' . ($arrDuty['count' . $intDOTW] > 1 ? 'Duties' : 'Duty') . ' used."></span>';
                } else {
                    echo '<div class="orange" style="overflow:hidden; position:absolute; width:9px; height:' . ($intDutyHeight - 1) . 'px; left:' . (167 + ($intDOTW * 10)) . 'px; top:0px">';
                    echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:' . ($intDutyHeight - 1) . 'px;top:30%;" title="' . $arrDuty['d' . $intDOTW] . ' ' . ($arrDuty['d' . $intDOTW] > 1 ? 'Duties' : 'Duty') . ' available. ' . $arrDuty['count' . $intDOTW] . ' ' . ($arrDuty['count' . $intDOTW] > 1 ? 'Duties' : 'Duty') . ' used."></span>';
                }
            } else {
                echo '<div class="grey" style="overflow:hidden; position:absolute; width:9px; height:' . ($intDutyHeight - 1) . 'px; left:' . (167 + ($intDOTW * 10)) . 'px; top:0px">';
                echo '<span style="overflow:hidden; color:white; width:100%; text-align:center; position:absolute; height:' . ($intDutyHeight - 1) . 'px;top:30%;" title="Duty not available on this day."></span>';
            }
            echo '</div>';
        }

        echo '</div>';
        $prevDutyID = $intDutyID;
        $intCounter++;
    }
    echo '</div>';
    // ################################################################ End the Duty Names

    // ################################################################ First a div with the Duties and Jobs

    $intCounter = 0;
    $prevDutyID = 0;
    echo '<div style="overflow:scroll; position: absolute; width:1000px; height:400px; left:250px; top:' . $currenttop . 'px" id="duties">';
    foreach ($arrDuties as $intDutyID => $arrDuty) {
        // a holder for everythnig
        if (($permissions->canview == 1) || ($permissions->canmodify == 1) || ($permissions->candelete == 1)) {
            if ($intArchived == 0) {
                echo '<div class="listduty-context-menu" id="dutydet' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="height:' . ($intDutyHeight) . 'px; position:absolute; top:' . ($intCounter * $intDutyHeight) . 'px;cursor: pointer">';
            } else {
                echo '<div class="listdutyarchived-context-menu" id="dutydet' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="height:' . ($intDutyHeight) . 'px; position:absolute; top:' . ($intCounter * $intDutyHeight) . 'px">';
            }
        } else {
            if ($intArchived == 0) {
                echo '<div class="listdutybasic-context-menu" id="dutydet' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="height:' . ($intDutyHeight) . 'px; position:absolute; top:' . ($intCounter * $intDutyHeight) . 'px">';
            } else {
                echo '<div class="listdutyarchivedbasic-context-menu" id="dutydet' . $intDutyID . '" dutyid="' . $intDutyID . '" prevdutyid="' . $prevDutyID . '" style="height:' . ($intDutyHeight) . 'px; position:absolute; top:' . ($intCounter * $intDutyHeight) . 'px">';
            }
        }


        // Draw in the Times
        drawtimecells("2015-01-01", $earlieststart, $lateststart, $intHourWidth, $intDutyHeight - 1);

        $intWidth = (($arrDuty['EndTime'] - $arrDuty['StartTime']) / 3600) * $intHourWidth;
        $left = ($arrDuty['StartTime'] / 3600) * $intHourWidth;
        echo '<div class="' . $classname . '" dutyid="' . $intDutyID . '" id="' . $intDutyID . '" lastmoddate="' . $arrDuty['lastmoddate'] . '" style="width: ' . $intWidth . 'px; height:' . ($intDutyHeight - 8) . 'px; position:absolute; left:' . $left . 'px; top:4px; background-color:#' . strval($arrDuty['backcolour']) . '; color:#' . strval($arrDuty['forecolour']) . ';">';
        if (isset($arrDuty['jobs'])) {
            foreach ($arrDuty['jobs'] as $intJobID => $arrJob) {
                $intJobWidth = (($arrJob['JobEndTime'] - $arrJob['JobStartTime']) / 3600) * $intHourWidth;
                $intJobLeft = (($arrJob['JobStartTime'] / 3600) * $intHourWidth) - $left;
                echo '<div class="dutyjob-context-menu" dutyid="' . $intDutyID . '" jobid="' . $intJobID . '" id="d' . strval($intDutyID) . 'j' . strval($intJobID) . '" lastmoddate="' . $arrDuty['lastmoddate'] . '" style="overflow:hidden; border: 1px solid #C0C0C0; width:' . $intJobWidth . 'px; height:' . ($intDutyHeight - 15) . 'px; position:absolute; left:' . $intJobLeft . 'px; top:3px; background-color:#' . $arrJob['JobBackColour'] . '; color:#' . $arrJob['JobForeColour'] . ';">';
                echo $arrJob['Job'];
                echo '</div>';
            }
        }
        echo '</div>';
        echo '</div>';
        $prevDutyID = $intDutyID;
        $intCounter++;

    }
    echo '</div>';

}


?>

<script type="text/javascript">

    //ListMasterJobs();
    DoResize();
    $(document).ready(function () {
        if (<?php echo empty($permissions->canview) ? 0 : $permissions->canview ?> == 0)
        {
            $('#content').load('page-includes/no_access.php', function () {
            });
        }
    else
        {
            $("#masterdutieslist").DataTable({
                paging: false,
                scrollY: 400,
                info: false,
                stateSave: true,
                "initComplete": function (settings, json) {
                    DoMDResize();
                }
            });
        }
    });

    $("div.dutydroppable").droppable({
        over: function (event, ui) {
            $(this).addClass('highlighted');
        },
        out: function (event, ui) {
            $(this).removeClass('highlighted');
        },
        drop: function (event, ui) {
            var attr = ui.draggable.attr("jobid");
            if (typeof attr !== typeof undefined && attr !== false) {
                //all good
                var dragjobid = attr;
            } else {
                var dragjobid = 0;
            }

            if (dragjobid > 0) {
                $(this).removeClass('highlighted');
                var dragjobid = ui.draggable.attr("jobid");
                var draglastmoddate = ui.draggable.attr("lastmoddate");
                var dropdutyid = $(this).attr("dutyid");
                var droplastmoddate = $(this).attr("lastmoddate");
                if (dropdutyid != 0) {
                    $.ajax({
                        url: "page-includes/master-duties/dropjob.php",
                        type: "POST",
                        dataType: "json",
                        data: {
                            'DutyID': dropdutyid,
                            'DutyLastModDate': droplastmoddate,
                            'JobID': dragjobid,
                            'JobLastModDate': draglastmoddate
                        },
                        success: function (data) {
                            if (data.status == 'success') {
                                //all good
                                var divname = '#' + dropdutyid;
                                $(divname).attr('lastmoddate', data.lastmoddate);
                                UpdateRow(dropdutyid);
                            } else {
                                customAlert(data.status);
                            }
                        },
                        error: function (x, e) {
                            if (x.status == 0) {
                                customAlert('You are offline!!<br/>Please Check Your Network.');
                            } else if (x.status == 404) {
                                customAlert('Requested URL not found.');
                            } else if (x.status == 500) {
                                customAlert('Internal Server Error.');
                            } else if (e == 'parsererror') {
                                customAlert('Error.<br/>Parsing JSON Request failed.');
                            } else if (e == 'timeout') {
                                customAlert('Request Time out.');
                            } else {
                                customAlert('Unknow Error.<br/>' + x.responseText);
                            }
                        }
                    });
                } else {
                    customAlert('Please ensure you drop the Job on a valid Duty');
                }
            }
        }
    });

    function UpdateRow(dutyid) {
        $.ajax({
            url: "page-includes/master-duties/update-duty.php",
            type: "GET",
            data: {
                'DutyID': dutyid
            },
            success: function (data) {
                //all good
                var divname = '#' + dutyid;
                $(divname).html(data);
            },
            error: function (x, e) {
                if (x.status == 0) {
                    customAlert('You are offline!!<br/>Please Check Your Network.');
                } else if (x.status == 404) {
                    customAlert('Requested URL not found.');
                } else if (x.status == 500) {
                    customAlert('Internal Server Error.');
                } else if (e == 'parsererror') {
                    customAlert('Error.<br/>Parsing JSON Request failed.');
                } else if (e == 'timeout') {
                    customAlert('Request Time out.');
                } else {
                    customAlert('Unknow Error.<br/>' + x.responseText);
                }
            }
        });
    }

    $('#duties').on('scroll', function () {
        $('#names').scrollTop($(this).scrollTop());
        $('#top').scrollLeft($(this).scrollLeft());
        $('#unallocatedduties').scrollLeft($(this).scrollLeft());
        $('#unallocatedjobs').scrollLeft($(this).scrollLeft());
        $(".leaders").scrollLeft($(this).scrollLeft());
    });

    $('#names').on('scroll', function () {
        $('#duties').scrollTop($(this).scrollTop());
    });

    $(window).resize(function () {
        DoResize;
    });

    function DoResize() {
        var widowwidth = $(window).width() - 285;
        var widowheight = $(window).height() - <?=$windowheightoffset?>;
        $("#duties").width(widowwidth).height(widowheight);
        $("#names").height(widowheight);
        $("#top").width(widowwidth);

    }

    function DeleteDuty(dutyid, prevdutyid, lastmoddate, action) {
        //check if can delete
        if (<?php echo empty($permissions->candelete) ? 0 : $permissions->candelete ?> == 0
    )
        {
            customAlert('You do not have delete privileges for this form.');
        }
    else
        {
			customConfirm('Are you sure you want to delete this duty?wwww',function(){
					//check for Duty in Rotas before deleting
					$.ajax({
						url: "page-includes/master-duties/delete-duty.php",
						type: "POST",
						dataType: "json",
						data: {
							'dutyid': dutyid,
							'action': action,
							'lastmoddate': lastmoddate
						},
						success: function (data) {
							if (data.status == 'success') {
								//all good
								ListMasterDuties(0, prevdutyid, 0);
							} else {
								customAlert(data.status);
							}
						},
						error: function (x, e) {
							if (x.status == 0) {
								customAlert('You are offline!!<br/>Please Check Your Network.');
							} else if (x.status == 404) {
								customAlert('Requested URL not found.');
							} else if (x.status == 500) {
								customAlert('Internal Server Error.');
							} else if (e == 'parsererror') {
								customAlert('Error.<br/>Parsing JSON Request failed.');
							} else if (e == 'timeout') {
								customAlert('Request Time out.');
							} else {
								customAlert('Unknow Error.<br/>' + x.responseText);
							}
						}
					});
				},
				function() {
					return false;
				}
			);
        }
    }

    $(function () {
        $.contextMenu({
            selector: '.listduty-context-menu',
            items: {

                <?php if ($permissions->canview == 1) { ?>
                "view": {
                    name: "View Duty Details",
                    icon: "comment",
                    // superseeds "global" callback
                    callback: function (key, options) {
                        var dutyid = options.$trigger.attr("dutyid");
                        DutyDetails(dutyid, 0);
                    }
                },
                <?php } if ($permissions->canmodify == 1) {?>
                "edit": {
                    name: "Edit Duty Details",
                    icon: "edit",
                    // superseeds "global" callback
                    callback: function (key, options) {
                        var dutyid = options.$trigger.attr("dutyid");
                        EditDuty(dutyid, 0);
                    }
                }, <?php } if ($permissions->candelete == 1) {?>
                "delete": {
                    name: "Delete Duty",
                    icon: "delete",
                    // superseeds "global" callback
                    callback: function (key, options) {
                        var dutyid = options.$trigger.attr("dutyid");
                        var prevdutyid = options.$trigger.attr("prevdutyid");
                        var lastmoddate = '';
                        DeleteDuty(dutyid, prevdutyid, lastmoddate, 1);
                    }
                },
                <?php } ?>
            }
        });
    });
</script>
