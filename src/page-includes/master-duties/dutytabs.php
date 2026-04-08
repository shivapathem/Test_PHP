<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/helpers.php';
include_once '../../class-includes/userRolePermissions.php';

$intAreaID = $_SESSION['user']['AreaID'];
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intTeamID = $_REQUEST['teamId'] ?? $_COOKIE['masterdutyteams'] ?? 0;
$intArchivedID = $_REQUEST["tab"] ?? 0;
$intType = 0;

//We are looking at Master Duties (not Rotas)
$_SESSION['isrota'] = 0;
//Check User Authentication
$pageid = 1;
if($intArchivedID == 1){
  $pageid = 5;
}
// Call User Permission function.
if($intTeamID == 0 || $intTeamID== '')
{

	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $intTeamID);
}
if ($permissions->canview == 1) {
  $rsAreaTeams = GetUserAreaTeamsList ($intUserID);
  $TeamOptions = TeamListDropDown ($rsAreaTeams,$intTeamID);

  echo '<div id="masterdutiestabs" style="border:none; width:99.3%; margin-left:10px">';
    echo '<h2 class="sr-only" id="dutyTabHeading">Master Duties</h2>';
    echo '<div>';
    echo '<ul>';
      echo '<li id="MasterDutyTab" onclick=\'javascript:ListMasterDuties(0,0,0)\';><a href="#dutieslistdiv0" id="#dutieslistdiv0">Master Duties</a></li>';
      echo '<li id="MiscDutyTab" onclick=\'javascript:ListMasterDuties(1,0,0)\';><a href="#dutieslistdiv0" id="#dutieslistdiv1">Miscellaneous Duties</a></li>';
    echo '</ul>';
	echo '</div>';
  echo '<div>';
  echo '<table class="smalltable" style="width:300px;" role="presentation">';
  echo '<tr>';
  echo '<td width="1%">&nbsp;</td>';
  echo '<td width="42%">';
  echo '<div style="display:flex; align-items:center" id="addBtnContainer" style="display:none;">';
  echo "<img class='handcursor' id='add-duty' border='0' src='images/add.png' width='18px' height='17px' role='button' tabindex='0' aria-label='Create new duty' onclick='javascript:NewDuty();' onkeypress=\"if(event.key === 'Enter' || event.key === ' ') NewDuty();\">&nbsp;New Duty";
  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
  echo '<img id="print" class="handcursor" name="print" border="0" src="images/print.png" style="top:10px; vertical-align:middle;" alt="Print" width="18px" height="17px" onclick="window.print();" hidden>';
  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
  echo '<input id="HiddenDutyType" type="hidden" value="0">';
  echo '</div>';
  echo '</td>';
  echo '<td width="57%">';
  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
  echo '<input type="checkbox" id="showEndedDuties" onclick=\'javascript:ListMasterDuties(0,0,0)\' value="">';
  echo '<label id ="showEndedDutieslbl" for="showEndedDuties">Show Ended Duties</label><br>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo'</div>';

  echo '<div id="dutieslistdiv0">';
  echo'</div>';
  echo'</div>';
}
?>

<script type="text/javascript">
$(document).ready(function(){
    if (<?php echo empty($permissions->canview) ? 0 : $permissions->canview ?> == 0) {

    $( '#content' ).load( 'page-includes/no_access.php', function() { });
  }
  else {
    var $tabs = $('#masterdutiestabs').tabs();
    $("#masterdutiestabs .ui-tabs-panel").removeAttr("aria-labelledby");

    $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    $(function() {
        $("button").button()
    });

    $("#ddlArchive").css('font-size','6');

    var archived = <?php echo $intArchivedID; ?>;

    if (archived == 1) {
        $('#ddlArchived option[value="-1"]').attr("selected","selected");
        $("#MasterDutyTab").removeClass("ui-tabs-active ui-state-active");
        $("#MiscDutyTab").addClass("ui-tabs-active ui-state-active");
        $( "#masterdutiestabs" ).tabs({
		    active: 1
        });
    }
    else {
        $('select[name^="ddlArchived"] option:selected').attr("selected",null);
        $('select[name^="ddlArchived"] option[value="0"]').attr("selected","selected");
        $("#MiscDutyTab").removeClass("ui-tabs-active ui-state-active");
        $("#MasterDutyTab").addClass("ui-tabs-active ui-state-active");
        $( "#masterdutiestabs" ).tabs({
			active: 0
        });
    }
    ShowArchived(archived);
  }
});

function ListMasterDuties(listtype, id, showedit) {

  var strTeams = $('#ddlRotaTeams').val();
  var isShowEnded = $('#showEndedDuties').is(':checked') ? 1 : 0;
  var tabID;
  if ( $(".master-jobs-context-menu").length )
	{
		$('.master-jobs-context-menu').contextMenu('destroy');
	}
	if ( $(".listduty-context-menu-jobs").length )
	{
		$('.listduty-context-menu-jobs').contextMenu('destroy');
	}
	if ( $(".dutyjob-context-menu").length )
	{
		$('.dutyjob-context-menu').contextMenu('destroy');
	}
  if ( $(".Miscduty-context-menu-0").length )
  {
  	$('.Miscduty-context-menu-0').contextMenu('destroy');
  }
  switch (listtype) {
    case 0:
      tabID = 0;
        $("#MiscDutyTab").removeClass("ui-tabs-active ui-state-active");
        $("#MasterDutyTab").addClass("ui-tabs-active ui-state-active");
		$("#showEndedDuties").show();
		$("#showEndedDutieslbl").show();
        var $tabs = $('#masterdutiestabs').tabs();
        document.getElementById('add-duty').setAttribute('alt', 'Create new master duty');
        document.getElementById('dutyTabHeading').textContent = 'Master Duties';
      break;

    case 1:
      tabID = 1;
        $("#MiscDutyTab").addClass("ui-tabs-active ui-state-active");
        $("#MasterDutyTab").removeClass("ui-tabs-active ui-state-active");
		$("#showEndedDuties").hide();
		$("#showEndedDutieslbl").hide();
	    var $tabs = $('#masterdutiestabs').tabs();
      document.getElementById('add-duty').setAttribute('alt', 'Create new misc duty');
      document.getElementById('dutyTabHeading').textContent = 'Miscellaneous Duties';
      break;
  }
    let strInput = $.cookie("searchMasterDuty");
    var archived = <?php echo $intArchivedID ?>;
  switch (listtype) {
    case 0:
      $("#divShowJobs").show();
      $.post("page-includes/master-jobs/list-masterduties.php", {
        id: id,
        archived: 0,
        teamId: strTeams,
        strSearchText: strInput,
        isShowEnded: isShowEnded
      },
        function(data,status){
          $('#dutieslistdiv0').html(data);
		  $('#duties').scrollTop(Math.abs($.cookie("dutyListingPosTop")));
		  $('#duties').scrollLeft(Math.abs($.cookie("dutyListingPosLeft")));
          GoToRow(id);
        }
      );
      break;

      case 1:
      $("#divShowJobs").hide();
	  let listtype = $.cookie("searchdutyMisctype") != '' ? $.cookie("searchdutyMisctype") : $('#HiddenDutyType').val();
      $.post("page-includes/master-duties/ListMiscellaneousDuties.php", {
        id: id,
        archived: 0,
        teamId: strTeams,
        listtype: listtype,
		strsearch: $.cookie("searchmiscdutyname")
      },
        function(data,status){
          $('#dutieslistdiv0').html(data);
		  $('#duties').scrollTop(Math.abs($.cookie("miscDutyListingPosTop")));
		  $('#duties').scrollLeft(Math.abs($.cookie("miscDutyListingPosLeft")));
          GoToRow(id);
        }
      );
      break;
  }

  if (showedit == 1) {
    EditDuty(id, listtype);
  }

}
DoMDResize();
$( window ).resize(function() {
  DoMDResize();
});

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

function DoMDResize() {
  var windowheight = $(window).height() - 225;
  var windowwidth = $(window).width() - 40;
  $('.dataTables_scrollBody').height((windowheight - 40));
}

function NewDuty(DutyTypeMisc = 0) {
    var $tabs = $('#masterdutiestabs').tabs();
    var selected = $tabs.tabs('option', 'active');
    var dutytype;

    switch (selected) {
        case 0:
            dutytype = 0;
            CallPopUptoCreateEdit(dutytype, DutyTypeMisc);
            break;

        case 1:
            dutytype = 2;
            CallPopUptoCreateEdit(dutytype, DutyTypeMisc);
            break;

        case 2:
            dutytype = 1;
            break;

    }
}

function CallPopUptoCreateEdit(dutytype,DutyTypeMisc){
    if (dutytype == 0) {
        var DutyUrl = "page-includes/master-duties/edit-masterduty.php";
    }
    else {
        var DutyUrl = "page-includes/master-duties/EditMiscellaneousDuty.php";
    }
    var ddlRotaTeams=$('#ddlRotaTeams').val();

    $.post(DutyUrl, {
            id: 0,
            dutytype: DutyTypeMisc,
            isrota: 0,
            isEdit:1,
            ddlRotaTeams:ddlRotaTeams
        },
        function(data,status){
            $.facebox(data);
        });
}


function ShowJobs() {
    var $tabs = $('#masterdutiestabs').tabs();
    var selected = $tabs.tabs('option', 'active');
    if (selected == 1) {
      ListMasterJobs();
    }
}

function ChangeTeams() {
  var strTeams = $('#ddlDutyTeams').val();
  var CookieName = "masterdutyteams";
  $.cookie(CookieName, strTeams, { expires: 1 });
  var $tabs = $('#masterdutiestabs').tabs();
  var selected = $tabs.tabs('option', 'active');
  var dutytype = selected;

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

  }

  ListMasterDuties(dutytype, 0, 0);

}

function ShowArchived(showarchived) {
  if (showarchived == 0) {
    $("#ddlArchived").css('background-color','#A9F5A9');
  }
  else {
    $("#ddlArchived").css('background-color','#F6CECE');
  }

  var selected = <?php echo $intArchivedID ?>;
  var dutytype = selected;
  switch (selected) {
    case 0:
      dutytype = 0;
      break;

    case 1:
      dutytype = 1;
      break;

  }

  ListMasterDuties(dutytype, 0, 0);
}


function EditDuty(id, dutytype) {
   var tabCookieName = "masterdutiestabs";
   var $tabs = $('#masterdutiestabs').tabs();
  var selected = $tabs.tabs('option', 'active');
  $.cookie(tabCookieName, selected, { expires: 1 });

  var newIndex = 0;
   var tabCookieName1 = "editdutydatetabs";
   $.cookie(tabCookieName1, newIndex, { expires: 1 });

  var selected = <?php echo $intArchivedID ?>;
  var ddlRotaTeams=$('#ddlRotaTeams').val();

  $.post("page-includes/master-duties/edit-masterduty.php", {
    id: id,
    dutytype: dutytype,
    isrota: 0,
    isEdit : 1,
    ddlRotaTeams:ddlRotaTeams
  },
  function(data,status){
	  $.facebox(data);
  })
}


function CopyDuty(id, dutytype) {
  var tabCookieName = "masterdutiestabs";
  var $tabs = $('#masterdutiestabs').tabs();
  var selected = $tabs.tabs('option', 'active');
  $.cookie(tabCookieName, selected, { expires: 1 });

  var newIndex = 0;
  var tabCookieName1 = "editdutydatetabs";
  $.cookie(tabCookieName1, newIndex, { expires: 1 });

  $.post("page-includes/master-duties/copy-masterduty.php", {
    id: id,
    dutytype: dutytype
  },
  function(data,status){
	  $.facebox(data);
  });
}

function DutyHistory(id) {
  $.post("page-includes/master-duties/duty-history.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
};

function UpdateRow(dutyid) {
    $.ajax({
                url: "page-includes/master-duties/update-duty.php",
                type: "GET",
                data: {
                    'DutyID': dutyid
                },
                success: function(data) {
		  //all good
		  var divname = '#' + dutyid;
		  $(divname).html(data);
		},
		error:function(x,e) {
		    if (x.status==0) {
			customAlert('You are offline!!<br/> Please Check Your Network.');
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


function DutyDetails(id, dutytype) {
  var ddlRotaTeams=$('#ddlRotaTeams').val();

        $.post("page-includes/master-duties/edit-masterduty.php", {
            id: id,
            dutytype: dutytype,
            isrota: 1,
            isEdit : 0,
            ddlRotaTeams:ddlRotaTeams
        },
        function(data,status){
            $.facebox(data);
        });
    }

function ListDutiesByTeam() {
    var strTeams = $('#ddlRotaTeams').val();
    var CookieName = "masterdutyteams";
    $.cookie(CookieName, strTeams, {expires: 1});

    var $tabs = $('#masterdutiestabs').tabs();
    var selected = $tabs.tabs('option', 'active');
    ListMasterDuties(selected, 0, 0);

}
</script>
