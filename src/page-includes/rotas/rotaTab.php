<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/user-scheduling-team-list.php';
include_once 'setTeamCookieRotas.php';

$intAreaID = $_SESSION['user']['AreaID'];
$intTeamID = $_POST['teamId'] ?? $_COOKIE['masterdutyteams'] ?? 0;
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
$rsUserTeams = null;
$TeamOptions = null;

$pageid = 24;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);
$TeamOptions = getSchedulingTeamList($intTeamID, 'rota-pattern', 'view');

if ($permissions->canview == 1) {
    echo '<div class="blueboxmedtextheader" style="width:100%;" >';
    echo '<div style="width:40%; float:left;">';
    echo '<span class="blueboxmedtextheader-title">Rota Patterns</span>';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<img id="print" class="handcursor" name="print" border="0" src="images/print.png" style="top:10px; vertical-align:middle;" alt="Print" width="18px" height="17px" onclick="window.print();" hidden>';
    echo '</div>';
    echo '<div style="width:60%; float:left;">';
    echo '<label for="ddlRotaTeams">Teams: </label>';
    echo '<select class="chosen-select" name="ddlRotaTeams" id="ddlRotaTeams" style="background-color:blue; width:80%; height:20px;" onchange=\'javascript:ChangeRotaTeams()\';>';
    echo "<option value=''>Select The Team</option>";
    echo  $TeamOptions;
    echo '</select>';
    echo '</div>';
    echo '</div>';

    echo '<div id="rotadutiesContainer" style="width:100%">';
    echo '<div id="rotadutiestabs" style="border:none; width:100%;">';
    echo '<h2 id="rotaTabHeading" class="sr-only">Master Duties</h2>';
    echo '<ul style="border:none; width:100%;" role="tablist">';
    echo '<li id="tab_a" data-listtype="0" role="tab" aria-selected="true" onclick=\'javascript:ListRotaDuties(0,0);\'><a href="#rotadutieslistdiv0">Master Duties</a></li>';
    echo '<li id="tab_b" data-listtype="5" role="tab" aria-selected="false" onclick=\'javascript:ListRotaDuties(5,0);\'><a href="#rotadutieslistdiv0">Miscellaneous Duties</a></li>';
    echo '<input id="HiddenDutyType" type="hidden" value="0">';
    echo '</ul>';
    echo '<div id="rotadutieslistdiv0" style="width:100%;">';
    echo '</div>';
    echo '</div>';

    echo '<div id="rotasdetailsdiv" style="width:100%;">';
    echo '</div>';
    echo '</div>';
}

?>

<script type="text/javascript">

    $(document).ready(function () {
	$("#rotadutiesContainer").height($(window).height() - 134);
	$("#rotadutiestabs").height(227);
	$("#rotasdetailsdiv").height(($("#rotadutiesContainer").height() - $("#rotadutiestabs").height()) - 9);
        if (<?php echo empty($permissions->canview) ? 0 : $permissions->canview ?> == 0
    )
        {
            $('#content').load('page-includes/no_access.php', function () {
            });
        }
    else
        {
            $('#rotadutiestabs > ul > li').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                }
            });
            var tabCookieName = "rotadutiestabs";
            var h2Headings = ['Master Duties', 'Miscellaneous Duties'];
            $("#rotadutiestabs").tabs({
                active: ($.cookie(tabCookieName) || 0),
                activate: function (event, ui) {
                    var newIndex = ui.newTab.parent().children().index(ui.newTab);
                    $.cookie(tabCookieName, newIndex, {expires: 1});
                    updateRotaTabHeading(h2Headings[newIndex] || h2Headings[0]);
                }
            });
			if($.cookie('selectRotaId') > 0)
			{
				ShowRotaDetails($.cookie('selectRotaId'), 1);
			}else
			{
				ShowRotaDetails(0, 1);
			}

            var $tabs = $('#rotadutiestabs').tabs();
            var selected = $tabs.tabs('option', 'active');
            var dutytype = selected;

            // Update h2 heading based on initially selected tab
            updateRotaTabHeading(h2Headings[selected] || h2Headings[0]);

            switch (selected) {
                case 0:
                    dutytype = 0;
                    break;
                case 1:
                    dutytype = 2;
                    break;
                case 2:
                    dutytype = 1;
                    break;
                case 3:
                    dutytype = 4;
                    break;
                case 4:
                    dutytype = 3;
                    break;
                case 5:
                    dutytype = 5;
                    break;
            }
        }
    });

    function ListRotaDuties(listtype, id, searchtext = '') {
        var tabID = 0;
        searchtext = typeof searchtext !== 'undefined' ? searchtext : '';
		searchtext = searchtext != '' ? searchtext : $.cookie("searchMasterDuty");
		if (listtype == -1) {
            tabID = (($.cookie('rotadutiestabs') == undefined || $.cookie('rotadutiestabs') == 'undefined') ? 0 : parseInt($.cookie('rotadutiestabs')));
            listtype = (($.cookie('rotadutiestabs') == undefined || $.cookie('rotadutiestabs') == 'undefined') ? 0 : parseInt($.cookie('rotadutiestabs')));
        } else {
            tabID = (listtype == -1 ? 0 : parseInt(listtype));
            listtype = (listtype == -1 ? 0 : parseInt(listtype));
        }
        if (tabID == 0) {
            $("#tab_a").addClass('ui-tabs-active ui-state-active');
            $("#tab_b").removeClass('ui-tabs-active ui-state-active');
            updateRotaTabHeading('Master Duties');
        } else {
            $("#tab_a").removeClass('ui-tabs-active ui-state-active');
            $("#tab_b").addClass('ui-tabs-active ui-state-active');
            updateRotaTabHeading('Miscellaneous Duties');
        }
        var tabCookieName = "rotadutiestabs";
        $.cookie(tabCookieName, tabID, {expires: 1});
        switch (listtype) {
            case 0:
                $.ajax({
                    type: 'POST',
                    url: 'page-includes/rotas/list-master-duties.php',
                    data: {
                        "id": id,
                        "searchtext": searchtext,
                        "teamId": $('#ddlRotaTeams').val()
                    },
                    success: function (data) {
                        $('#rotadutieslistdiv0').html(data);
						$('#duties').scrollTop(Math.abs($.cookie("dutyListingRotaPosTop")));
						$('#duties').scrollLeft(Math.abs($.cookie("dutyListingRotaPosLeft")));
                    }
                });
                break;

            case 5:
                let listtype = $.cookie("searchdutyMisctype") != '' ? $.cookie("searchdutyMisctype") : $('#HiddenDutyType').val();
                $.ajax({
                    type: 'POST',
                    url: "page-includes/master-duties/ListMiscellaneousDuties.php",
                    data: {
                        "id": id,
                        "archived": 0,
                        "teamId": $('#ddlRotaTeams').val(),
                        "listtype": listtype,
                        "pageId": 3,
                        "strsearch": $.cookie("searchmiscdutyname")
                    },
                    success: function (data) {
                        $('#rotadutieslistdiv0').html(data);
                        $('#duties').scrollTop(Math.abs($.cookie("miscDutyListingRotaPosTop")));
                        $('#duties').scrollLeft(Math.abs($.cookie("miscDutyListingRotaPosLeft")));
                    }
                });
                break;
        }
    }

    function ChangeRotaTeams() {
        $("#Loading6style").show();
        var strTeams = $('#ddlRotaTeams').val();
        var CookieName = "masterdutyteams";
        $.cookie(CookieName, strTeams, {expires: 1});
		$.cookie('searchMasterDuty', '', {expires: 1});
		$.cookie('searchmiscdutyname', '', {expires: 1});
		$.cookie('searchdutyMisctype', '', {expires: 1});
		if($.cookie('selectRotaId') > 0)
		{
			$.cookie('selectRotaId', '', {expires: 1});
		}
		window.localStorage.removeItem('DataTables_joblisting_/');
        var $tabs = $('#rotadutiestabs').tabs();
        var selected = $tabs.tabs('option', 'active');
        var dutytype = selected;

        switch (selected) {
            case 0:
                dutytype = 0;
                break;
            case 1:
                dutytype = 5;
                break;
            case 2:
                dutytype = 1;
                break;
        }
        //update ddlRotaName chosen select
        updateChoosenSelect('#ddlRotaName', strTeams);
        setTimeout(function() {ShowRotaDetails(0, 1);}, 300);
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


    function ShowRotaDetails(id, lineid) {
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!
        var yyyy = today.getFullYear();
		$.cookie('selectRotaId', id);
        if (dd < 10) {
            dd = '0' + dd
        }

        if (mm < 10) {
            mm = '0' + mm
        }

        today = dd + '/' + mm + '/' + yyyy;

        var tabCookieName = "rotashowtemplates";
        var showtemplates = ($.cookie(tabCookieName) || 0);

        var tabCookieName2 = "rotashowall";
        var showall = ($.cookie(tabCookieName2) || 0);

        var tabCookieName3 = "rotaeffdate";
        var effdate = ($.cookie(tabCookieName3) || today);

        var tabCookieName4 = "rotashowbreaks";
        var showbreaks = ($.cookie(tabCookieName4) || 0);

        if (showtemplates == 0) {
            $("#tab_a").removeClass('disabledtab');
            $("#tab_b").removeClass('disabledtab');
            $("#tab_c").addClass('disabledtab');
            $("#tab_d").removeClass('disabledtab');
            $("#tab_e").removeClass('disabledtab');
        } else if (showtemplates == 1) {
            $("#tab_a").addClass('disabledtab');
            $("#tab_b").removeClass('disabledtab');
            $("#tab_c").removeClass('disabledtab');
            $("#tab_d").addClass('disabledtab');
            $("#tab_e").addClass('disabledtab');
        } else {
            $("#tab_a").removeClass('disabledtab');
            $("#tab_b").removeClass('disabledtab');
            $("#tab_c").removeClass('disabledtab');
            $("#tab_d").removeClass('disabledtab');
            $("#tab_e").removeClass('disabledtab');
        }

        $.ajax({
            type: 'POST',
            url: "page-includes/rotas/rotadetails.php",
            data: {
                "id": id,
                "lineid": lineid,
                "showtemplates": showtemplates,
                "showall": showall,
                "effdate": effdate,
                "showbreaks": showbreaks
            },
            success: function (data) {
                $('#rotasdetailsdiv').html(data);
                //Set Cookie here
                ListRotaDuties('-1',0, '');
                $('#rotadutieslist0').scrollTop(Math.abs($.cookie("rotaPosTop")));
                $("#Loading6style").hide();
                $('#loading').hide();
            },
        });
    }

    function DutyHistory(id) {
        $.ajax({
            type: 'POST',
            url: "page-includes/master-duties/duty-history.php",
            data: {
                "id": id
            },
            success: function (data) {
                $.facebox(data);
            },
        });
    }

    function DutyDetails(id, dutytype) {
        $.ajax({
            type: 'POST',
            url: "page-includes/master-duties/edit-masterduty.php",
            data: {
                "id": id,
                "dutytype": dutytype,
                "isrota": 1,
                "isEdit": 0
            },
            success: function (data) {
                $.facebox(data);
            },
        });
    }

    function updateRotaTabHeading(tabName) {
        $('#rotaTabHeading').text(tabName);
        // Update aria-selected for tabs
        if (tabName === 'Master Duties') {
            $('#tab_a').attr('aria-selected', 'true');
            $('#tab_b').attr('aria-selected', 'false');
        } else {
            $('#tab_a').attr('aria-selected', 'false');
            $('#tab_b').attr('aria-selected', 'true');
        }
    }

</script>
