<?php
// Check if a session is not already started
if (session_status() === PHP_SESSION_NONE  ) {
    session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/init.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/helpers.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';
include_once 'setTeamCookie.php';

$windowheightoffset = 280;
$windowheightList= 428;

$currid = $_REQUEST["id"] ?? 0;
$intDutyTypeID = $_REQUEST["listtype"] ?? 0;
$intArchived = $_REQUEST["archived"] ?? 0;
$strsearch = $_REQUEST["strsearch"] ?? '';

//Check User Authentication
$pageid = 5;//Master duties form id
// Call User Permission function.
$strTeams = $_COOKIE["masterdutyteams"] ? $_COOKIE["masterdutyteams"]: '0';
if($strTeams == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $strTeams);
}
$RotaPage = $_REQUEST['pageId'] ?? 0;
if ($permissions->cancreate == 1 && $RotaPage == 3)
{
    $classname = 'draggable';
}
else
{
    $classname = 'notdraggable';
}

$intHourWidth = 80;
$intDutyHeight = 30;
$currenttop = 50;
$earlieststart = 0;
$lateststart = 24;
$earlieststart = 24;
$lateststart = 0;
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'];
if (isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])) {
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
} else {
    $strTeams = 0;
}

$rsDutiesJson = ListAllMasterDuties($intAreaID, $intDutyTypeID, $intArchived, $strTeams, $strsearch);
$rsDuties = json_decode($rsDutiesJson,true);
$misduty_context_menu = 'Miscdutybasic-context-menu';
if (($permissions->canview == 1) || ($permissions->canmodify == 1) || ($permissions->candelete == 1)) {
    $misduty_context_menu = 'Miscduty-context-menu-'.$RotaPage;
}

if ($intDutyTypeID == 0) {
    $intID = 0;
} else {
    $intID = $intDutyTypeID;
}

$MiscDutyTypes = GetMiscellaneousDutyTypes();
$MiscDutyTypeOptions = PopulateMiscDutyTypes($MiscDutyTypes, $intDutyTypeID, $intID);
$rsDuties[0]['MasterDutyID'] = $rsDuties[0]['MasterDutyID'] ?? 0;
if (isset($rsDuties) && $rsDuties[0]['MasterDutyID'] > 0) {
    $ArrCount = count($rsDuties);
    for($row = 0; $row < $ArrCount; $row++) {
        $intThisEnd = ceil($rsDuties[$row]['EndTime'] / 3600);
        if ($intThisEnd > $lateststart) {
            $lateststart = $intThisEnd;
        }
        $intThisStart = floor($rsDuties[$row]['StartTime'] / 3600);
        if ($intThisStart < $earlieststart) {
            $earlieststart = $intThisStart;
        }
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['DutyName'] = $rsDuties[$row]['DutyName'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['DutyTypeID'] = $rsDuties[$row]['DutyTypeID'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['DutyTypeName'] = $rsDuties[$row]['DutyTypeName'];
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
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['IsNightShift'] = $rsDuties[$row]['IsNightShift'];
        $arrDuties[$rsDuties[$row]['MasterDutyID']]['dutyhasjobs'] = 1;

        if (!is_null($rsDuties[$row]['JobID'])) {
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobTypeID'] = $rsDuties[$row]['JobTypeID'];
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['Job'] = $rsDuties[$row]['Job'];
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobStartTime'] = $rsDuties[$row]['JobStartTime'];
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobEndTime'] = $rsDuties[$row]['JobEndTime'];
            if (is_null($rsDuties[$row]['JobBackColour'])) {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobBackColour'] = '#dddddd';
            }
            else {
                $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobBackColour'] = $rsDuties[$row]['JobBackColour'];
            }
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['jobs'][$rsDuties[$row]['JobID']]['JobForeColour'] = $rsDuties[$row]['JobForeColour'];
        }
        else {
            $arrDuties[$rsDuties[$row]['MasterDutyID']]['dutyhasjobs'] = 0;
        }
    }
}
else{
    echo '<label for="searchmiscdutyname" class="sr-only">Search Miscellaneous Duty</label>';
    echo '<input id="searchmiscdutyname" name="searchmiscdutyname" type="text" aria-label="Search miscellaneous duty name" style="height:15px; width:120px; left:-2px; position: absolute; top:'.($currenttop + 13).'px" size="12" value="'.$strsearch.'" />';
	echo '<i class="fa fa-search buttonEnabled" id="searchMiscDuty" style="position: absolute; left:122px; top:'.($currenttop + 14).'px" role="button" aria-label="search miscellaneous duty" tabindex="0"></i>'; 
    echo '<select name="searchdutyMisctype" id="searchdutyMisctype" style=" position: absolute; left:21%; top:'.($currenttop + 11).'px" >';
    echo $MiscDutyTypeOptions;
    echo '</select>';
    echo '<div style="overflow: hidden; position: absolute; width:64.7%; height:20px; left:35%; top:'.$currenttop.'px" id="top" class="times">';
    echo '</div>';
    $currenttop = $currenttop + 30;
    echo '<div style="overflow: scroll; position: absolute; width:35%; left:0px; top:'.$currenttop.'px; font-size: 12px;" id="names">';
    echo "No Record Found";
    echo '</div>';
    $currenttop = $currenttop - 10;
    $intCounter = 0;
    echo '<div class="compact stripe bluetable" style="overflow:scroll; position: absolute; width:64.7%; left:35%; top:'.$currenttop.'px; font-size: 12px;" id="duties">';
    echo "No Record Found";
    echo '</div>';
}

// ################################################################ First a div with the Duty names
if (!empty($arrDuties)) {
    $intCounter = 0;
    echo '<label for="searchmiscdutyname" class="sr-only">Search Miscellaneous Duty</label>';
    echo '<input id="searchmiscdutyname" name="searchmiscdutyname" type="text" aria-label="Search miscellaneous duty name" style="height:15px; width:120px; left:-2px; position: absolute; top:'.($currenttop + 13).'px" size="12" value="'.$strsearch.'" />';
	echo '<i class="fa fa-search buttonEnabled" id="searchMiscDuty" style="position: absolute; left:122px; top:'.($currenttop + 14).'px" role="button" aria-label="search miscellaneous duty" tabindex="0"></i>'; 
    echo '<select name="searchdutyMisctype" id="searchdutyMisctype" style="position: absolute; left:21%; top:'.($currenttop + 11).'px">';
    echo $MiscDutyTypeOptions;
    echo '</select>';
    echo '<div style="overflow: hidden; position: absolute; width:64.7%; height:20px; left:35%; top:'.$currenttop.'px" id="top" class="times">';
    echo '</div>';
    $currenttop = $currenttop + 30;
    echo '<div style="overflow: scroll; position: absolute; width:35%; left:0px; top:'.$currenttop.'px" class="list-duties_misc" id="names">';
    foreach ($arrDuties as $intDutyID => $arrDuty) {
        $Duration = (int)$arrDuty['Duration'];
        $DutyTypeName = $arrDuty['DutyTypeName'];
        $HourDuration = number_format((float)($Duration / 3600), 2, '.', '');
        $MinutesPercentile = $Duration % 3600;
        $MinutesDuration = intval(($MinutesPercentile) / 60);
        $DutyName = $arrDuty['DutyName'];
        $isNightDuty = $arrDuty['IsNightShift'];
        echo '<div class="'.$misduty_context_menu.'" dutyid="'.$intDutyID.'" id="'.$intDutyID.'" style="font-size:10px;background-color: #99bfe6; position: absolute; width:100%; height:'.($intDutyHeight - 1).'px; left:0px; top:'.($intCounter * $intDutyHeight).'px" title="'.$DutyName.'">';
        echo '<b>'.trim(substr($DutyName,0,20)).'</b>';
        echo '<br>';
        if($isNightDuty == 1){
            echo '('.$HourDuration.' Hours, Night)';
        }else{
            echo '('.$HourDuration.' Hours)';
        }
        echo '<b style="float:right; width:120px; margin-right:15px;">'.$DutyTypeName.'</b>';
        echo '</div>';
        $intCounter++;
    }
    echo '</div>';
    // ################################################################ End the Duty Names

    // ################################################################ First a div with the Duties and Jobs
    $currenttop = $currenttop - 10;
    $intCounter = 0;
    echo '<div class="compact stripe list-duties_misc bluetable" style="overflow:scroll; position: absolute; width:64.7%;  left:35%; top:'.$currenttop.'px;"  id="duties" onScroll="setMiscDutyListingScrollPos()">';
	echo '<div id="miscDutyListingPos"  name="miscDutyListingPos"></div>';
    foreach ($arrDuties as $intDutyID => $arrDuty) {
        // A holder for everythnig
        echo '<div class="'.$misduty_context_menu.'" dutyid="'.$intDutyID.'" style="height:'.($intDutyHeight).'px; position:absolute; top:'.($intCounter * $intDutyHeight).'px; background-color:#'.strval($arrDuty['backcolour']).'; color:#'.strval($arrDuty['forecolour']).';">';
        // Draw in the Times
        $intWidth = 500;
        $left = 0;
        echo '<div class="'.$classname.'" dutyid="'.$intDutyID.'" dutyname="'.$arrDuty['DutyName'].'" dutytypeid="'.$arrDuty['DutyTypeID'].'" style="border: 1px solid #C0C0C0; text-align:center; width: '.$intWidth.'px; height:'.($intDutyHeight - 8).'px; position:absolute; left:'.$left.'px; top:4px; background-color:#'.strval($arrDuty['backcolour']).'; color:#'.strval($arrDuty['forecolour']).';" title="'.$arrDuty['DutyName'].'">';
        echo ''.trim(substr($arrDuty['DutyName'],0,20)).'';
        echo '</div>';
        echo '</div>';
        $intCounter++;
    }
    echo '</div>';
}
echo '<div id="drag_helper" style="width:50px;"></div>';
?>

<script type="text/javascript">

$(document).ready(function(){
    if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
        $( '#content' ).load( 'page-includes/no_access.php', function() { });
    }
    else {
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
        $("#masterdutieslist").DataTable({
            paging: false,
            scrollY: 400,
            info:     false,
            stateSave: true,
            "initComplete": function( settings, json ) {
                DoMDResize();
            }
        });
        $("#imgShowJobs").hide;
        $('#searchdutyMisctype').on("change", function() {
            var DutyTypeId = this.value;
            $('#HiddenDutyType').val(DutyTypeId);
			$.cookie("searchdutyMisctype", DutyTypeId);
            ListMiscellaneousDuties(DutyTypeId, 0, 0);
        });

        if ($('#searchmiscdutyname').length > 0) {
            var searchinput = $('#searchmiscdutyname');
            var intlength = $('#searchmiscdutyname').val().length * 2;
            searchinput[0].setSelectionRange(intlength, intlength);
        }
        $('#searchMiscDuty').on("click", function() {
            var strInput = $('#searchmiscdutyname').val();
			$.cookie("searchmiscdutyname", strInput);
            ListMiscellaneousDuties(0, 0, 0, $.cookie("searchmiscdutyname"));
        });
		$("#searchmiscdutyname").keyup(function(event) {
			if (event.keyCode === 13) {
				$("#searchMiscDuty").click();
			}
		});
    }
});

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

function DeleteDuty(dutyid, prevdutyid, lastmoddate, action) {
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
        success: function(data) {
            if (data.status == 'success') {
                //all good
                ListMiscellaneousDuties(0, prevdutyid, 0);
            }
            else {
                customAlert(data.status);
            }
        },
        error:function(x,e) {
            if (x.status==0) {
                customAlert('You are offline!!<br/>Please Check Your Network.');
            } else if(x.status==404) {
                customAlert('Requested URL not found.');
            } else if(x.status==500) {
                customAlert('Internal Server Error.');
            } else if(e=='parsererror') {
                customAlert('Error.<br/>Parsing JSON Request failed.');
            } else if(e=='timeout'){
                customAlert('Request Time out.');
            } else {
                customAlert('Unknow Error.<br/>'+x.responseText);
            }
        }
    });
}

//Refresh the miscellaneous duties list
function ListMiscellaneousDuties (listtype, id, showedit, strsearch = ''){
    let MiscDutyType = $('#HiddenDutyType').val()
	strsearch = strsearch != '' ? strsearch : $.cookie("searchmiscdutyname");
    $.ajax({
            type: 'POST',
            url: "page-includes/master-duties/ListMiscellaneousDuties.php",
            data: {
                id: id,
                archived: 0,
                listtype: listtype,
                strsearch: strsearch,
                pageId: <?php echo ($RotaPage == 3 ? $RotaPage : 0); ?>
        },
        success: function (data) {
            $('#dutieslistdiv0').html(data);
            $('#rotadutieslistdiv0').html(data);
			$('#duties').scrollTop(Math.abs($.cookie("miscDutyListingPosTop")));
			$('#duties').scrollLeft(Math.abs($.cookie("miscDutyListingPosLeft")));
            GoToRow(id);
        }
    });
}

function GoToRow(currid) {
    if(currid != 0) {
        var strid = "#" + currid;
        if($(strid).length && $('#names').length)
        {
            var pos = $(strid).offset().top - $('#names').offset().top;
            $('#names').animate({
                scrollTop: pos
            },'fast');
        }
    }
}

function EditMiscDuty(id, dutytype, isrota) {
    var tabCookieName = "masterdutiestabs";
    var $tabs = $('#masterdutiestabs').tabs();
    var selected = $tabs.tabs('option', 'active');
    $.cookie(tabCookieName, selected, { expires: 1 });

    var newIndex = 0;
    var tabCookieName1 = "editdutydatetabs";
    $.cookie(tabCookieName1, newIndex, { expires: 1 });
    var ddlRotaTeams = $('#ddlRotaTeams').val();
    $.ajax({
        type: 'POST',
        url: "page-includes/master-duties/EditMiscellaneousDuty.php",
        data: {
            "id": id,
            "dutytype": dutytype,
            "isrota": isrota,
            "isEdit":1,
            'ddlRotaTeams': ddlRotaTeams
        },
        success: function (data) {
            $.facebox(data);
        }
    });
}



function DoResize () {
    var widowwidth = $(window).width() - 285;
    var widowheight = $(window).height() - <?=$windowheightoffset?>;
    if (<?php echo $RotaPage == 3 ? $RotaPage : 0;?> == 3) {
        $("#duties").width(widowwidth).height(widowheight - 75);
        $("#names").height(widowheight - 75);
    }
    else {
        $("#duties").width(widowwidth).height(widowheight);
        $("#names").height(widowheight);
    }
    $("#top").width(widowwidth);
}

    <?php if ($RotaPage == 3) { ?>
        var widowheight_list = "155px";
    <?php } else { ?>
        var widowheight_list = $(window).height() - <?=$windowheightList?>;
    <?php } ?>
	$(".list-duties_misc").height(widowheight_list);

$(function () {
        $.contextMenu({
            selector: '.Miscduty-context-menu-'+"<?php echo $RotaPage; ?>",
            items: {
                <?php if ($permissions->canview == 1) { ?>
                "View": {
                    name: "View Misc Duty",
                    icon: "comment",
                    // superseeds "global" callback
                    callback: function(key, options) {
                        var dutyid = options.$trigger.attr("dutyid");
                        ViewMiscDuty(dutyid, 2, 0);
                    }
                },<?php } if ($permissions->cancreate == 1 && $RotaPage != 3) { ?>
                "AddMiscDuty": {
                    name: "Add Misc Duty",
                    icon: "add",
                    // superseeds "global" callback
                    callback: function(key, options) {
                        let DutyTypeMisc = $('#searchdutyMisctype').val();
                        NewDuty(DutyTypeMisc);
                    }
                },<?php } if ($permissions->canmodify == 1 && $RotaPage != 3) {?>
                "edit": {
                        name: "Edit Misc Duty",
                        icon: "edit",
                        // superseeds "global" callback
                        callback: function(key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            let DutyTypeId = $('#searchdutyMisctype').val();
                            EditMiscDuty(dutyid, DutyTypeId, 0);
                        }
                }, <?php } if ($permissions->candelete == 1 && $RotaPage != 3) {?>
                "delete": {
                name: "Delete Misc Duty",
                icon: "delete",
                // superseeds "global" callback
                callback: function(key, options) {
                    var dutyid =  options.$trigger.attr("dutyid");
                    var prevdutyid =  "";
                    var lastmoddate = "";
					customConfirm('Do you want to delete this miscellaneous duty? Press Yes to confirm.',function(){
							DeleteDuty(dutyid, prevdutyid, lastmoddate, 1);
						},
						function() {
							return false;
						}
					);
                }
            },<?php }if ($permissions->canview == 1) { ?>
                "MiscDutyHistory": {
                    name: "History",
                    icon: "history",
                    callback: function(key, options) {
                        var dutyid = options.$trigger.attr("dutyid");
                        History(dutyid);
                    }
                }
            <?php } ?>
            }
        });
    });

function ViewMiscDuty(id, dutytype, isrota) {
    var tabCookieName = "masterdutiestabs";
    var $tabs = $('#masterdutiestabs').tabs();
    var selected = $tabs.tabs('option', 'active');
    $.cookie(tabCookieName, selected, { expires: 1 });

    var newIndex = 0;
    var tabCookieName1 = "editdutydatetabs";
    $.cookie(tabCookieName1, newIndex, { expires: 1 });
    var ddlRotaTeams = $('#ddlRotaTeams').val();
    $.ajax({
        type: 'POST',
        url: "page-includes/master-duties/EditMiscellaneousDuty.php",
        data: {
            "id": id,
            "dutytype": dutytype,
            "isrota": isrota,
            "isEdit":0,
            'ddlRotaTeams': ddlRotaTeams
        },
        success: function (data) {
            $.facebox(data);
        }
    });
}
function setMiscDutyListingScrollPos()
{
	let top = $('#miscDutyListingPos').position().top;
	let left = $('#miscDutyListingPos').position().left;
	if(document.getElementById('ddlRotaName'))
	{
		$.cookie("miscDutyListingRotaPosTop", top);
		$.cookie("miscDutyListingRotaPosLeft", left);
	}else
	{
		$.cookie("miscDutyListingPosTop", top);
		$.cookie("miscDutyListingPosLeft", left);
	}
}
</script>
