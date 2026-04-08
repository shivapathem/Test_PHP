<?php
session_start();
include_once __DIR__ . '/function-includes/init.php';
include_once __DIR__ . '/function-includes/genericfunctions.php';
include_once __DIR__ . '/function-includes/DBHelper.php';
include_once __DIR__ . '/function-includes/DB_Functions.php';
include_once __DIR__ . '/page-includes/users/process/classUserSetup.php';
include_once __DIR__ . '/function-includes/common/classCommonDBFunctions.php';
include_once __DIR__ . '/function-includes/adminfunctions.php';

$commonObjt = new classCommonDBFunctions();
$bbcweeknumberArray =  $commonObjt->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
$intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? 0;

$user = '';
if(empty($_SESSION)){
  $_SESSION['user'] = [];
  $_SESSION['user']['user'] = '';
  $_SESSION['http_auth'] = '';
  $_SESSION['orginalLogin'] = '';
}

if((!isset($_SESSION['http_auth'])) || (empty($_SESSION['http_auth']))) {
  if(isset($_SERVER['HTTP_AUTHORIZATION'])  && strlen($_SERVER['HTTP_AUTHORIZATION']) > 25){
    $user = !empty(authUser()) ? authUser():'';
    $_SESSION['http_auth'] = $_SERVER['HTTP_AUTHORIZATION'];
    $_SESSION['orginalLogin']=$_SESSION['user']['user'] = $user;
    if((!empty($user)) && (isset($user))){
      $updateLoginDateTime = $commonObjt->updateLoginDateTimeofUser($user);
    }
  } else {
    header("HTTP/1.1 401 Unauthorized");
  }
}
$_SESSION['user']['user'] =  isset($_SESSION['user']['user']) ? $_SESSION['user']['user'] : '';
if(!isset($_SESSION['orginalLoginSystemAdmin']) && !empty($commonObjt->GetUserDetailsFromDomaiDirectory($_SESSION['user']['user']))){
  $rsStaff = $commonObjt->GetStaffDetailsUserDetailsByLogin($_SESSION['user']['user']);
  $intSysAdmin = !empty($rsStaff) ? $commonObjt->UserIsSysAdmin($rsStaff["UserID"]) : 0;
  $_SESSION['orginalLoginSystemAdmin'] = $intSysAdmin == '' ? 0 :$intSysAdmin;
}

$user = !empty(authUser()) ? authUser():'';

if(!empty($_REQUEST["user"]) && isset($_REQUEST["user"])){
    $actualUser = (!empty($user) && isset($user)) ? $user : '';
    $spoofUser = (!empty($_REQUEST["user"]) && isset($_REQUEST["user"])) ? $_REQUEST["user"] : '';
    $spoofSecurityEmail  = getenv('IMPERSONATE_SECURITY_EMAIL');
    if(!empty($spoofSecurityEmail)){
        $newLogLine = "$actualUser is trying to access account of $spoofUser. An email sent to $spoofSecurityEmail";
    } else {
        $newLogLine = "$actualUser is trying to access account of $spoofUser.";
    }
    logger('Impersonate_User/impersonate_user')->CRITICAL($newLogLine);
    if(!empty($spoofSecurityEmail)){

        spoofUserSendEmailSecurity($actualUser, $spoofUser, $spoofSecurityEmail);
        
        echo '<script> 
              alert("You are not allowed to see this page");
              window.location.href = window.location.pathname;
              </script>';
    }
}

$_SESSION['user']['user'] = $user;
include_once __DIR__ . '/function-includes/testaccess.php';
include_once __DIR__ . '/function-includes/pageurls.php';
//MK time for sufix to JS/CSS files.
$mktRandom =  time();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
<meta http-equiv='cache-control' content='no-store, no-cache, must-revalidate'>
<meta http-equiv='cache-control' content='post-check=0, pre-check=0'>
<meta http-equiv='expires' content='Sat, 26 Jul 1997 05:00:00 GMT'>
<meta http-equiv='pragma' content='no-cache'>
<link rel="stylesheet" type="text/css" href="styles/default_TW.css?v=<?php echo $mktRandom; ?>">
<link rel="stylesheet" type="text/css" href="styles/accessibility.css?v=<?php echo $mktRandom; ?>">
<link rel="stylesheet" type="text/css" href="styles/default.css?v=<?php echo $mktRandom; ?>">
<link href="styles/sm-core-css.css" rel="stylesheet" type="text/css" />
<link href="styles/sm-clean.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" media="all" type="text/css" href="styles/menu.css?v=<?php echo $mktRandom; ?>" />
<link rel="stylesheet" type="text/css" href="styles/teamwork/common.css?v=<?php echo $mktRandom; ?>">
<link rel="stylesheet" href="styles/print.css?v=<?php echo $mktRandom; ?>" media="print" />
<link href="styles/jquery.qtip-min.css?v=<?php echo $mktRandom; ?>" rel="stylesheet" type="text/css" />
<link href="styles/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="styles/colpick-min.css" rel="stylesheet" type="text/css" />
<link href="styles/jquery-ui.theme-min.css" rel="stylesheet" type="text/css" />
<link href="styles/jquery.timepicker-min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="styles/allocations/weekly/contextMenu.css?v=<?php echo $mktRandom; ?>">
<link href="styles/jquery.selectBox-min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="styles/spectrum.css">
<link rel="stylesheet" type="text/css" href="styles/jquery.fileupload-min.css">
<link rel="stylesheet" type="text/css" href="styles/timeTo.css">
<link rel="stylesheet" type="text/css" href="styles/jquery-te-1.4.0-min.css">
<link rel="stylesheet" type="text/css" href="styles/chosen-min.css" />
<link rel="stylesheet" type="text/css" href="styles/facebox-min.css" />
<link rel="stylesheet" type="text/css" href="styles/jquery.ui.autocomplete.css" />
<script type="text/javascript" src="js/jquery-3.5.1.min.js?v=<?php echo $mktRandom; ?>"></script>
<script src ="js/jquery-migrate-3.0.0.min.js?v=<?php echo $mktRandom; ?>"></script>
<script src="js/jquery-1.12.4.min.js?v=<?php echo $mktRandom; ?>"></script>
<script src="js/jquery-migrate-1.4.1.min.js?v=<?php echo $mktRandom; ?>"></script>
<link rel="stylesheet" type="text/css" href="styles/jquery.dataTables.css?v=<?php echo $mktRandom; ?>" />
<link rel="stylesheet" type="text/css" href="styles/jquery.dataTables.yadcf-min.css?v=<?php echo $mktRandom; ?>" />
<link rel="stylesheet" type="text/css" href="styles/font-awesome.min.css?v=<?php echo $mktRandom; ?>" />
<link rel="stylesheet" type="text/css" href="styles/schedulepeople.css?v=<?php echo $mktRandom; ?>" />
<link rel="stylesheet" type="text/css" href="styles/master-job-style.css?v=<?php echo $mktRandom; ?>" />
<link rel="stylesheet" type="text/css" href="styles/admin.css?v=<?php echo $mktRandom; ?>" />
<link rel="stylesheet" type="text/css" href="styles/zebra_dialog.css?v=<?php echo $mktRandom; ?>" />
<link rel="stylesheet" type="text/css" href="styles/teamwork/allocations.css?v=<?php echo $mktRandom; ?>">
<link rel="stylesheet" type="text/css" href="styles/sicknessPopup.css?v=<?php echo $mktRandom; ?>" />
<link href="styles/expiringPHL.css?v=<?php echo $mktRandom; ?>" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="styles/allocations/weekly/weekly.css?v=<?php echo $mktRandom; ?>">
<link href="styles/allocations/weekly/workingTime.css?v=<?php echo $mktRandom; ?>" rel="stylesheet">
<!--  New CSS for Datatables
<link rel="stylesheet" type="text/css" href="styles/jquery.dataTables.min.css"/>
<link rel="stylesheet" type="text/css" href="styles/buttons.dataTables.min.css"/>
  New CSS for Datatables -->
<script type="text/javascript" src="js/Validations.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/handleThePermissions_min.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/jquery-ui.min.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/jquery.ui.position.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/jquery.qtip.min.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/allocations/weekly/contextMenu.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/jquery.form-min.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/jquery.validate-min.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/jquery.mtz.monthpicker-min.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter-min.js"></script>
<script type="text/javascript" src="js/fixed_table_rc_min.js"></script>
<script type="text/javascript" src="js/colpick-min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.js"></script>
<script type="text/javascript" src="js/dataTables.fixedHeader_min.js"></script>
<script type="text/javascript" src="js/dataTables.fixedColumns_min.js"></script>
<script type="text/javascript" src="js/jquery.timepicker-min.js"></script>
<script type="text/javascript" src="js/jquery.selectBox.js"></script>
<script type="text/javascript" src="js/dataTables.scroller_min.js"></script>
<script type="text/javascript" src="js/spectrum.js"></script>
<script type="text/javascript" src="js/jquery.additional-methods_min.js"></script>
<script type="text/javascript" src="js/jquery.cookie-min.js"></script>
<script type="text/javascript" src="js/jquery-fileupload-min.js"></script>
<script type="text/javascript" src="js/jquery.printPage-min.js"></script>
<script type="text/javascript" src="js/jquery.jeditable-min.js"></script>
<script type="text/javascript" src="js/jquery.jeditable.datepicker-min.js"></script>
<script type="text/javascript" src="js/jquery.floatThead-min.js"></script>
<script type="text/javascript" src="js/jquery.timeTo-min.js"></script>
<script type="text/javascript" src="js/jquery.json.min.js"></script>
<script type="text/javascript" src="js/jquery-te-1.4.0.min.js"></script>
<script type="text/javascript" src="js/chosen.jquery-min.js"></script>
<script type="text/javascript" src="js/facebox-min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.yadcf_min.js"></script>
<script type="text/javascript" src="js/jQuery.print.min.js"></script>
<script type="text/javascript" src="js/jquery.number.min.js"></script>
<script type="text/javascript" src="js/common.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript" src="js/zebra_dialog_min.js"></script>
<script type="text/javascript" src="js/dataTables.checkboxes.min.js"></script>
<script type="text/javascript" src="js/jquery.smartmenus.min.js?v=<?php echo time(); ?>"></script>
<script src="js/localStorage.js"></script>
<!-- <script type="text/javascript" src="js/allocations/weekly/weekly.js"></script> -->
<!--  New Js for Datatables
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="js/jszip.min.js"></script>
<script type="text/javascript" src="js/pdfmake.min.js"></script>
<script type="text/javascript" src="js/vfs_fonts.js"></script>
<script type="text/javascript" src="js/buttons.html5.min.js"></script>
<script type="text/javascript" src="js/buttons.print.min.js"></script>
  New Js for Datatables -->
<script type="text/javascript" src="js/allocations/weekly/editweekly-pageload.js?v=<?php echo $mktRandom; ?>"></script>
<title>Allocate</title>
</head>
<body>
<!--
=======================================
BANNER
=======================================
-->
<?php
include_once __DIR__ . '/function-includes/shortcut-key-config.php';
include_once __DIR__ . '/components/filters/load-filter.php';
$uatbackcolor = '';
if (getenv('SERVER_ENV')=='UAT'){
    $uatbackcolor = 'style="background-color:#'.getenv('NAVBAR_BG').'!important"';
}

?>
<div id="loading">
<img border="0" src="images/loading.gif" width="145px" height="100px">
</div>

<div class="noprint" id="header">
   <div id="banner" <?php echo $uatbackcolor ;?>>
    <img src="images/BBC-Allocate-white.png" class="site-logo" alt="BBC Allocate">
  </div>
  <div id="menu">
  </div>
</div>
<div id="CustomDivForModal"></div>
<div id="content">
    <?php
        $menuRedirect = isset($_GET['menuRedirect']) ? $_GET['menuRedirect'] : '';
        if (empty($menuRedirect)) {
            $intIsDownShowHome = GetSiteIsDown();
            if ($intIsDownShowHome == 0) {
                echo CallShowHomePHP();
            }
        }
    ?>
</div>
<div id="customMLRTip" class="tooltip-div"></div>
<div id="printcontent" class="printonly">
</div>

<!-- Element to pop up -->
  <div id="popup">
    <div class="button b-close"><ul>X</ul></div>
    <div id="popcontent"></div>
  </div>


<div id="dialog-leave-delete" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this Leave Request?</p>
</div>
<div id="dialog-leave-delete-group" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this block of Leave Request?</p>
</div>
<div id="dialog-week-delete" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to remove this week?<br>This will only remove the allocations from this view.</p>
</div>



<div id="dialog-jobnofit" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>The job you are trying to add will conflict with existing job. Please try again.</p>
</div>
<div id="dialog-extendduty" title="Information!" style="display:none;">
<p>The Job start time or end time is outside of the Duty start and end time. Click OK to continue and amend the Duty Start or End Time to that of the Job.</p>
</div>
<div id="dialog-dutytoosmall" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You cannot resize a duty to times less than the Jobs within it.<br>Please remove the Jobs first!.</p>
</div>
<div id="dialog-dutyoverlap" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You cannot have shifts with Overlapping hours!.</p>
</div>
<div id="dialog-job-unassign" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are unassigning a job which belongs to a different Scheduling Group.<br>Click Yes to move this job to the Group you are viewing.<br>Click No to keep it in its current Group (it will disppear from view if you do this).</p>
</div>
<div id="dialog-sign-in" title="Sign In / Un-Sign In, Mark in the Building" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Please choose an option.</p>
</div>
<div id="dialog-noinbuilding" title="Please Confirm!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can only indicate that you are in the building 1 hour before the shift starts!</p>
</div>
<div id="dialog-confirm" title="Please Confirm!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>If you restore this duty it will make it editable, but its original jobs will not be reinstated.<br>Do you want to continue?</p>
</div>
<div id="dialog-confirm-scheduled-person" title="Please Confirm!" style="display:none;">
<p style="height:30px"><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to attach this staff with this scheduled person?</p>
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>After selecting Yes please return to the Scheduled Person tab and select Update Person to complete this process.</p>
</div>
<div id="dialog-confirm-delete" title="Please Confirm!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This person is not scheduled to work on this day<br>if you delete the duty it will become unallocated <br>Do you want to continue?</p>
</div>
<div id="no-space-dialog" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Jobs can only be moved where there is space. Either edit this job to fit, or edit the jobs around it to make room</p>
</div>
<div id="dialog-confirm-copy-job" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can copy this job.<br>It will appear on the unassigned Jobs line.<br>Do you wish to continue?</p>
</div>
<div id="dialog-confirm-delete-job" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to Delete this job ?</p>
</div>
<div id="dialog-no-delete" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Jobs published from Allocate cannot be deleted!</p>
</div>
<div id="dialog-no-history" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Jobs published from Allocate don't have any history associated with them!</p>
</div>
<div id="dialog-lock-day" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Change the editable status of this day<br>You can Lock the day to prevent editing.<br>You can Unlock the day - this will allow users with Shiftleader status and above to edit the day.<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Or you can set the day to be editable on a timer.</p>
</div>
<div id="dialog-lock-day-confirm" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>If you unlock this day you will create a copy of all the Duties and Jobs.<br>This will detach the days Allocations from Allocate.<br>Are you sure you want to do this?</p>
</div>
<div id="dialog-grid-checks" title="Grid Checks" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can mark this day as having been checked.<br>Or it can be marked as being checked but with problems that need following up.<br>If you choose the second option an email will be sent to the office with your comments.</p>
</div>
<div id="dialog-duty-delete" title="Grid Checks" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Duties not yet edited cannot be deleted!!</p>
</div>
<div id="dialog-request-delete" title="Delete Request" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to Delete this Request?</p>
</div>
<div id="dialog-request-decline" title="Request Not Possible" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to mark this Request as Not Possible?<br>The Request will be marked as Deleted and an eMail will be sent to the requester informing them it's Not Possible.</p>
</div>
<div id="dialog-delete-shiftleader" title="Delete Shiftleader" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to remove this Shiftleader?</p>
</div>
<div id="dialog-lock-apply" title="Apply for A Lock" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You may request that this day is locked.<br>Once requested it cannot be changed without reference to your Allocations team.<br> Do you wish to lock this day?</p>
</div>
<div id="dialog-request-apply" title="Apply for A Request" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to add any comments to this request?<br>You can also add comments after the request has been created.</p>
</div>
<div id="dialog-lock-conditional-apply" title="Apply for A Lock" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 100px 0;"></span>You may request that this day is Locked.<br>You have one or more Requests in this week which are currently unapproved.<br>If you choose to Lock this day any unapproved Requests will be marked as deleted.<br>Once your Lock is requested it cannot be changed without reference to your Allocations team.<br><br>Do you wish to lock this day?</p>
</div>
<div id="dialog-lock-delete" title="Delete A Lock" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>Are you sure you want to Delete this Lock?<br>Assuming this date is withing the Locks request period it will allow this person to apply again.</p>
</div>
<div id="dialog-SA-Remove" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to remove this person from this Department?<br></p>
</div>
<div id="dialog-email-list" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can create a list based on this view of the eMail addresses for working Staff.<br>Or you can send an email of the Duties to the people in this view.<br>Please choose an option.</p>
</div>
<?php
  $intIsDown = GetSiteIsDown();
  $strMessage = GetMessage(1);
  if ($strMessage != '') {
    echo '<div id="message-box">';
    echo $strMessage;
    echo '</div>';
  }
?>
</body>
</html>

<script type="text/javascript">
<?php

if ($intIsDown == 0 || $intSysAdmin == 1) {
  echo "FillMenu();\n";
}
?>
$(document).ready(function(){

  $(document).bind("contextmenu",function(e){
     //e.preventDefault();
  });

  $(document).bind('loading.facebox', function() {
    $(document).unbind('keydown.facebox');
    $('#facebox_overlay').unbind('click');
  });

  $('#message-box').slideDown('slow').delay(3500).slideUp('slow');
  SetWidth ();
});

function SetWidth () {
  var width = screen.width;
    $.ajax({
        type: 'POST',
        data: {
            'width': width,
        },
        url: 'getscreenwidth.php',
        success: function (data) {
        }
    });
}

$(function() {
	window.setInterval("KeepSessionAlive()", 300000); //Changed value to 300000 from 1200000 due to session issue
	window.onload = function() {
		inactivityTime();
	}

	var inactivityTime = function () {

		var refreshtime;
		window.onload = resetTimer;
		// DOM Events
		document.onmousemove = resetTimer;
		document.onkeydown = resetTimer;

		function resetTimer() {

			clearTimeout(refreshtime);
			refreshtime = setTimeout(RefreshPage, 5400000)
			// 1000 milliseconds = 1 second
		}
	};
});

function ShowIsDown () {
    $.ajax({
        type: 'POST',
        url: 'page-includes/sitedown.php',
        success: function (data) {
            $('#content').html(data);
        }
    });
}

function TeamPhotos() {
  $( '#content' ).load( 'page-includes/photos-by-base.php', function() { });
}

function ShowStudioList(id) {
      $.ajax({
        type: 'POST',
          data: {
            'id': id
          },
        url: 'studios/studiolist.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in studio list call.');
        }
    });
}

function ShowPayments () {
      $.ajax({
        type: 'POST',
        url: 'page-includes/payments/payments.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in payments call.');
        }
    });
}

function ShowPayrollClose (year) {
      $.ajax({
        type: 'POST',
          data: {
              'year':year
          },
        url: 'page-includes/admin/payrollclose.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in payroll close call.');
        }
    });
}

function ShowMyAppraisees  () {
      $.ajax({
        type: 'POST',
        url: 'page-includes/users/users-myappraisees.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in myappraisees call.');
        }
    });
}

function ShowAppraisalGroups  () {
      $.ajax({
        type: 'POST',
        url: 'page-includes/support/support-appraisal-groups-header.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in appraisal groups call.');
        }
    });
}

function ShowWebSuggestions  (typeid) {
      $.ajax({
        type: 'POST',
        data: {
            'typeid': typeid,
        },
        url: 'page-includes/suggestions/index.php',
        success: function (data) {
        },
        error:function (data) {
            alert('some error found in index call.');
        }
    });
}

function ShowSchedulingAreas  () {
      $.ajax({
        type: 'POST',
        url: 'page-includes/suggestions/schedulingareas.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in scheduling areas call.');
        }
    });
}

function ShowStudioBlockBookings(id) {
      $.ajax({
        type: 'POST',
        url: 'studios/studioblocks.php',
        data: {
            'id': id
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in studio blocks call.');
        }
    });
}

function SetMultiBase(group) {
      $.ajax({
        type: 'POST',
          data: {
              'group': group
          },
        url: 'page-includes/ajax-calls/setmultibase.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in setmulti base call.');
        }
    });
}

function ShowAttachments(department, date) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/staff-attachments.php',
        data: {
            'department': department,
            'date': date
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in staff attachments call.');
        }
    });
}

function ShowAllocations(teamId, WeekNumber, scheduledPersonId = '', isShifttoCheck  = 0, checklocal = 0) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/load_filter.php',
        data: {
            'teamId': teamId,
            'WeekNumber': WeekNumber,
            'screenName':'ViewWeekly'
        },
        success: function (filterID) {
            $('#teamId').val(teamId);
            if (filterID == 0) {
              if (checklocal==0) {
                if((localStorage.getItem('weeklyscreen_filter') != '') && (localStorage.getItem('weeklyscreen_filter') != null) && (localStorage.getItem('weeklyscreen_filter') != 'undefined')) {
                    applyViewFilter("ViewWeekly", filterID, 'Yes', WeekNumber);
                } else {
					if((localStorage.getItem('weeklyscreen_filter') != '') && (localStorage.getItem('weeklyscreen_filter') != null)) {
                            localStorage.removeItem('weeklyscreen_filter');
                    }
                  ShowWeeklyAllocation(teamId,WeekNumber,scheduledPersonId,isShifttoCheck);
                }
              } else {
				  if((localStorage.getItem('weeklyscreen_filter') != '') && (localStorage.getItem('weeklyscreen_filter') != null)) {
                            localStorage.removeItem('weeklyscreen_filter');
                    }
                ShowWeeklyAllocation(teamId,WeekNumber,scheduledPersonId,isShifttoCheck);
              }
            } else {
                $('#intSWeekNumber').val(WeekNumber);
                $('#intEWeekNumber').val(WeekNumber);
                applyViewFilter(screenName="ViewWeekly",filterID);
            }
        },
        error:function (data) {
            console.log('some error found in load filter call.');
        }
    });
}

function ShowAllocationsRotaInEditWeekly(teamId, WeekNumber,finalargweeknumber,argstartDate,argendDate,argendWeek,finalargendweeknumber,request,selFilterId=0,selFilterType='',mastMiscId=0, rotaData = []) {
	$.ajax({
        type: 'POST',
        url: 'page-includes/allocations/load_filter.php',
        data: {
            'teamId': teamId,
            'WeekNumber': WeekNumber,
            'screenName':'EditWeeklyRota'
        },
        success: function (filterID) {
            $('#teamId').val(teamId);
            if (filterID == 0) {
				if((localStorage.getItem('EditweeklyRotascreen_filter') != '') && (localStorage.getItem('EditweeklyRotascreen_filter') != null) && (localStorage.getItem('EditweeklyRotascreen_filter') != 'undefined')) {
                    applyViewFilter("EditWeeklyRota", filterID, 'Yes', finalargweeknumber,request);
                } else {
                    if((localStorage.getItem('EditweeklyRotascreen_filter') != '') && (localStorage.getItem('EditweeklyRotascreen_filter') != null)) {
                            localStorage.removeItem('EditweeklyRotascreen_filter');
                    }
                 ShowRotaInEditWeeklyAllocations(teamId, WeekNumber,finalargweeknumber,argstartDate,argendDate,argendWeek,finalargendweeknumber,request,filterID,'',mastMiscId, rotaData);
               }
            } else {
                applyViewFilter(screenName="EditWeeklyRota",filterID,'',finalargweeknumber,request);
			}
        },
        error:function (data) {
            console.log('some error found in load filter call.');
        }
    });
}
function ShowStaffing(department, WeekNumber) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-staffing.php',
          data: {
              'department': department,
              'WeekNumber': WeekNumber
          },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations staffing call.');
        }
    });
}

function EditAllocations(team, WeekNumber) {
    var data = {teamId: team};
    $.ajax({
        type: 'POST',
        url: '/page-includes/allocations/weekly/modals/choose-week.php',
        data: data,
        success: function (response) {
            $('body > *:not(#facebox)').attr('aria-hidden', 'true');
            $('#facebox').attr({
                'role': 'dialog',
                'aria-modal': 'true',
                'aria-labelledby': 'choose-week-heading'
            });
            $.facebox(response);
        },
        error:function (response) {
            alert('some error found in ping call.');
        }
    });
}

function EditDailyAllocations(team) {
  var data = {teamId: team};
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/choose-date.php',
        data: data,
        success: function (response) {
            $.facebox(response);
        },
        error:function (response) {
            alert('some error found in ping call.');
        }
    });
}

$(document).on('click', "#viewEditWeeklyGrid", function(event){
  event.stopImmediatePropagation();
  event.preventDefault();
  $('#loading').show();
  editWeeklyPageLoad('Yes',{});
});

$(document).on('click', "#viewEditDailyGrid", function(event){
  event.stopImmediatePropagation();
  event.preventDefault();
  $('#loading').show();
  dailyallocationPageLoad('Yes',{});
});

function ShowWeeklyMismatch(department, WeekNumber) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-weekly-emails.php',
          data: {
              'department': department,
              'WeekNumber': WeekNumber
          },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations weekly emails call.');
        }
    });
}

function ShowAllocationsNoCanDo(team, WeekNumber) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-weekly.php',
        data: {
            'WeekNumber': WeekNumber,
            'team': team,
            'cando': 1,
            'screenName':'ShiftsToCheck'
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations weekly call.');
        }
    });
}

function ShowHandovers(date) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/handovers/handovers-holder.php',
        data: {
            'date': date
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in handovers holder call.');
        }
    });
}

function ShowUserHistory(LogIn) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/userhistory.php',
        data: {
            'login': LogIn
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in user history call.');
        }
    });
}


function ShowFindPeople() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/findpeople.php',
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in find people call.');
        }
    });
}

function ShowFindScheduallPeople() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/findshedpeople.php',
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in find shedule people call.');
        }
    });
}

function ShowUserMovementsHistory(LogIn) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/support/support-history.php',
        data: {
            'login': LogIn
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in support history call.');
        }
    });
}

function ShowUserInfo(LogIn) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/userinfo.php',
        data: {
            'login': LogIn
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in user info call.');
        }
    });
}

function ShowOvertimeVolunteers(teamId, ddate) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/overtime-volunteers.php',
        data: {
            'teamId': teamId,
            'ddate': ddate
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in overtime volunteers call.');
        }
    });
}

/**
* This function is used to get the email list on daily allocations.
* @param teamId This param contains the TeamID information
*@param date This param contains the date information
*/
function CreateEmailList(teamId, date,roleIDPermission,startendshiftflag,filterQuery1,filterQuery2,filterQuery3,filterOrderStr,selectedDay,jobNameAll,jobLabelAll) {
      if(jobNameAll == ''){
        jobNameAll = 0;
      }
      if(jobLabelAll == ''){
        jobLabelAll = 0;
      }
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/email-list.php',
        data: {
            'teamId': teamId,
            'date': date,
            'roleIDPermission' : roleIDPermission,
            'startendshiftflag' : startendshiftflag,
            'filterQuery1' : filterQuery1,
            'filterQuery2' : filterQuery2,
            'filterQuery3' : filterQuery3,
            'filterOrderStr' : filterOrderStr,
            'selectedDay' : selectedDay,
            'jobNameAll' : jobNameAll,
            'jobLabelAll' : jobLabelAll
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in email list call.');
        }
    });
}

function CreateEmailOptions(teamId, date,roleIDPermission,startendshiftflag,filterQuery1,filterQuery2,filterQuery3,filterOrderStr,selectedDay,jobNameAll,jobLabelAll) {
  if(jobNameAll == ''){
    jobNameAll = 0;
  }
  if(jobLabelAll == ''){
    jobLabelAll = 0;
  }
  $(function() {
    $("#dialog-email-list").dialog(
      {
      width: 800,
      buttons: {
        "Create List": function() {
            $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/email-list.php',
                data: {
                    'teamId': teamId,
                    'date': date,
                    'roleIDPermission' : roleIDPermission,
                    'startendshiftflag' : startendshiftflag,
                    'filterQuery1' : filterQuery1.replaceAll('-', "'"),
                    'filterQuery2' : filterQuery2.replaceAll('-', "'"),
                    'filterQuery3' : filterQuery3,
                    'filterOrderStr' : filterOrderStr,
                    'selectedDay' : selectedDay,
                    'jobNameAll' : jobNameAll,
                    'jobLabelAll' : jobLabelAll
                },
                success: function (data) {
                    $.facebox(data);
                },
                error:function (data) {
                    alert('some error found in email list.');
                }
            });
              $(this).dialog("close");
            },
            "Send eMails": function() {

            $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/email-send-status.php',
                success: function (data) {
                    $.facebox(data);
                },
                error:function (data) {
                    alert('some error found in email send status.');
                }
            });
            $("#loading").fadeOut(300);

            //Get filters scheduler list
            let filteredScheduledPersonList = [];
            $(".person-data-cell:visible").each(function(index, element) {
                filteredScheduledPersonList.push($(element).attr('data-id'));
            });
            $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/email-send-duties.php',
                data: {
                    'teamId': teamId,
                    'date': date,
                    'roleIDPermission' : roleIDPermission,
                    'startendshiftflag' : startendshiftflag,
                    'filterQuery1' : filterQuery1.replaceAll('-', "'"),
                    'filterQuery2' : filterQuery2.replaceAll('-', "'"),
                    'filterQuery3' : filterQuery3,
                    'filterOrderStr' : filterOrderStr,
                    'selectedDay' : selectedDay,
                    'jobNameAll' : jobNameAll,
                    'jobLabelAll' : jobLabelAll,
                    'filteredScheduledPersonList': filteredScheduledPersonList
                },
                success: function (data) {
                    $.facebox(data);
                },
                error:function (data) {
                    alert('some error found in email send duties.');
                }
            });

          $(this).dialog("close");
        },
        "Cancel": function() {
          $(this).dialog("close");
        }
      }
    });
  });
}

function SetSortOrder (order, department) {
     $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/setsortorder.php',
        data: {
            'order': order,
        },
        success: function (data) {
            ShowDailyAllocations(department, $('#strCurrentDate').val());
        },
        error:function (data) {
            alert('some error found in set sort order call.');
        }
    });
}

function SetFilter(filtertype, filterid, TeamID, callerpage) {
  $('*').qtip('hide');
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-setfilter.php',
        data: {
            'teamid': TeamID,
            'filtertype': filtertype,
            'filterid': filterid,
            'callerpage': callerpage
        },
        success: function (data) {
            ShowAllocationsDuties(TeamID);
        },
        error:function (data) {
            alert('some error found in allocations set filter call.');
        }
    });
}

function SetDaysToView(daynumber, TeamID) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-set-daystoview.php',
        data: {
            'daynumber': daynumber,
            'teamId': TeamID
        },
        success: function (data) {
            ShowAllocationsDuties(TeamID);
        },
        error:function (data) {
            alert('some error found in allocations set daystoview call.');
        }
    });
}

function SetDaysStart(daynumber, TeamID) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-set-daystart.php',
        data: {
            'daynumber': daynumber,
            'teamId': TeamID
        },
        success: function (data) {
            ShowAllocationsDuties(TeamID);
        },
        error:function (data) {
            alert('some error found in allocations set daystart call.');
        }
    });
}


function ShowDailyAllocations(teamId, date, callerpage='ViewDaily',checklocal=0) {
   $('*').qtip('hide');
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/load_filter.php',
        data: {
            'teamId': teamId,
            'screenName' :callerpage
        },
        success: function (filterID) {
            $('#teamId').val(teamId);
            if (filterID==0) {
                if (checklocal==0) {
                    if((localStorage.getItem('dailyscreen_filter') != '') && (localStorage.getItem('dailyscreen_filter') != null) && (localStorage.getItem('dailyscreen_filter') != 'undefined')) {
                        $('#strCurrentDate').val(date);
                        $('#teamId').length === 0 ? $('<input type="hidden" id="teamId" value="' + teamId + '" />').appendTo('#content') : '';
                        applyViewFilter(screenName="ViewDaily",filterID, 'Yes',date);
                    } else {
                        if((localStorage.getItem('dailyscreen_filter') != '') && (localStorage.getItem('dailyscreen_filter') != null)) {
                            localStorage.removeItem('dailyscreen_filter');
                         }
                        ShowDailyAllocationWithAppliedFilter(teamId,date,callerpage);
                    }
                } else {
                    if((localStorage.getItem('dailyscreen_filter') != '') && (localStorage.getItem('dailyscreen_filter') != null)) {
                            localStorage.removeItem('dailyscreen_filter');
                         }
                    ShowDailyAllocationWithAppliedFilter(teamId,date,callerpage);
                }
            } else {
                $('#strCurrentDate').val(date);
				localStorage.removeItem('dailysessiondate');
				localStorage.setItem('dailysessiondate',date);
                applyViewFilter(screenName="ViewDaily",filterID);
            }
        },
        error:function (filterID) {
            console.log('some error found in load filter call.');
        }
    });
}

function ShowDailyDeletions(teamId, date) {
  $('*').qtip('hide');
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-daily-deleted.php',
        data: {
            'teamId': teamId,
            'date': date
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in allocations daily deleted call.');
        }
    });
}

function ShowDailyAllocationsCompound(group, urldate) {
  if(typeof(urldate)==='undefined') urldate = '';
  $( '#content' ).load( 'page-includes/allocations/allocations-daily.php?showall='+ group + '&date=' + urldate, function() { });
}

function PrintDailyAllocations(teamId, date) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-daily.php',
        data: {
            'teamId': teamId,
            'date': date,
            'print': 1
        },
        success: function (data) {
            $('#printcontent').html(data);
            $("#printcontent").print({
                addGlobalStyles : true,
                stylesheet : null,
                rejectWindow : true,
                noPrintSelector : ".no-print",
                iframe : true,
                append : null,
                prepend : null
            });
        },
        error:function (data) {
            alert('some error found in allocations daily call.');
        }
    });
}

function PrintWeeklyAllocations(base, week) {
  var win = window.open('printing/allocations-weekly.php?base='+base+'&week='+week, '_blank');
  win.focus();
}

function ShowStudioUsage(teamId, date) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-studios-daily.php',
        data: {
            'teamId': teamId,
            'date': date
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations studios daily call.');
        }
    });
}

function ShowDailyAllocationsCompare(teamId,date) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-daily-compare.php',
        data: {
            'teamId': teamId,
            'date': date
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations daily compare call.');
        }
    });
}

function ShowGridChecks(teamId, days, date) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-gridchecks-holder.php',
        data: {
            'team': teamId,
            'days': days,
            'date': date
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations grid checks holder call.');
        }
    });
}

function ShowAllocationsMulti(teamId, week) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/load_filter.php',
        data: {
            'teamId': teamId,
            'screenName' :'MultiWeek'
        },
        success: function (filterID) {
            $('#teamId').val(teamId);
            if (filterID == 0) {
                ShowAllocationsMultiWeekAllocatios(teamId, week);
            } else {
                $('#intSWeekNumber').val(week);
                applyViewFilter(screenName="MultiWeek",filterID);
            }
        },
        error:function (data) {
            console.log('some error found in load filter call.');
        }
    });
}

function ShowAllocationsDuties(teamId, week) {
    $('*').qtip('hide');
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-weekly-duties.php',
        data: {
            'WeekNumber': week,
            'teamId': teamId
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations weekly duties call.');
        }
    });
}

function ShowRota(date, schedulingPersonId, teamId) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-monthly.php',
        data: {
            'date': date,
            'schedulingPersonId': schedulingPersonId,
            'teamId': teamId
        },
        success: function (data) {
            $('*').qtip('hide');
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations monthly call.');
        }
    });
}

function ShowYearAllocations(date, schedulingPersonId, teamId) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-year.php',
        data: {
            'date': date,
            'schedulingPersonId': schedulingPersonId,
            'teamId': teamId
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations year call.');
        }
    });
}

function ShowDailyRota(date, schedulingPersonId, teamId) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-individual-daily.php',
        data: {
            'date': date,
            'schedulingPersonId': schedulingPersonId,
            'teamId': teamId
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations individual daily call.');
        }
    });
}

function ShowDocuments() {
  $( '#content' ).load( 'page-includes/documents/documents.php', function() { });
}

function ShowLeave(year, user) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-yearly.php',
        data: {
            'year': year,
            'user': user
        },
        success: function (data) {
            document.getElementById('content').style.pointerEvents = 'auto';
            $('#loader').hide();
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave yearly call.');
        }
    });
}

function ShowLeaveWeekly(week, user) {
    $('*').qtip('hide');
    $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-weekly.php',
        data: {
            'week': week,
            'user': user
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave weekly call.');
        }
    });
}
function ShowLeaveWeeklyByDate(date, user) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-weekly.php',
        data: {
            'date': date,
            'user': user
        },
        success: function (data) {
            $('*').qtip('hide');
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave weekly call.');
        }
    });
}

function AdminShowLeaveWeekly(week) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-weekly.php',
        data: {
            'week': week,
            'admin': 2
        },
        success: function (data) {
            // $('*').qtip('hide');
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave weekly call.');
        }
    });
}

function AdminShowLeaveWeeklyByDate(date, user) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-weekly.php',
        data: {
            'date': date,
            'admin': 2
        },
        success: function (data) {
            $('*').qtip('hide');
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave weekly call.');
        }
    });
}

function ShowAllocateLeave(year) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-allocate.php',
        data: {
            'year': year
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave allocate call.');
        }
    });
}

function FillMenu() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/menu.php',
        success: function (data) {
            $('#menu').html(data);
        },
        error:function (data) {
            alert('some error found in menu script.');
        }
    });
}

function ShowWeeklyLeave(ddate) {
  if(typeof(ddate)==='undefined') ddate = '';
  $( '#content' ).load( 'page-includes/leave-weekly.php?date='+ddate, function() { });
}

function ShowMyOptions() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/users/user-options.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in user options call.');
        }
    });
}

function ShowMySupport() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/support/my-support.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in my support call.');
        }
    });
}

function ShowSystemAdmin() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/system-admin-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in system admin page holder call.');
        }
    });
}

/*
* @Description : Controller for schedulling team UI.
* @access : Public
* @global : Not Applicable
* @param  : N/A
* @return : Append HTML output to main page.
*/
function showSchedulingTeamUI() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/process/schedulingTeam.php',
        data: {
            'task': 'schedulingTeamHtmlCall'
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in scheduling Team call.');
        }
    });
}

function ShowDepartmentAdmins() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/department-admins.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in admins call.');
        }
    });
}

function ShowDepartmentContacts() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/contacts-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in contacts holder call.');
        }
    });
}

function ShowTeamContacts() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/team-contacts-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in team contacts call.');
        }
    });
}


function ShowUserContacts() {
  $.post("page-includes/users/user-contacts.php", {
  },
  function(data,status){
    $('#content').html(data);
   }
  )
}

function ShowMySetUpOptions() {
  $.post("page-includes/users/view/userSetupUI.php", {

  },
  function(data,status){
    $('#content').html(data);
   }
  )
}

function ShowAllocateHelp(id) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/allocate-help.php',
        data: {
            'id': id
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocate help call.');
        }
    });
}

function ShowDepartmentAdminsInHome() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/department-admins.php',
        data: {
            'hideheader': 1
        },
        success: function (data) {
            $('#depadminsinhome').html(data);
        },
        error:function (data) {
            alert('some error found in department admins call.');
        }
    });
}

function ShowConfigLeaveCredits() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/leave-credits-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave credits page holder call.');
        }
    });
}

function ShowConfigLeave() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/leaverequestspageholder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave requests call.');
        }
    });
}

function ShowXmasPoints(teamid, sortorder) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/xmas-points.php',
        data: {
            'teamid': teamid,
            'sortorder': sortorder
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in xmas points call.');
        }
    });
}

function ShowUserStatus() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/support/support-user-status-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in support user status page call.');
        }
    });
}

function ShowUserSupport() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/support/support-staff-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in support staff page call.');
        }
    });
}

function ShowStaffMovements() {
       $.ajax({
        type: 'POST',
        url: 'page-includes/admin-upcoming-movements.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in admin upcoming movements call.');
        }
    });
}


function ShowLeaveAdmin() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-admin.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave admin call.');
        }
    });
}

function ShowLeaveReports(refpage) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/reports/leave-reports-page-holder.php',
        data: {
            'refpage': refpage
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in leave reports call.');
        }
    });
}

function ShowSicknessReports() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/reports/sickness-reports-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in sickness reports call.');
        }
    });
}

function ShowSicknessReportsGlobal() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/reports/sickness-reports-global.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in sickness reports call.');
        }
    });
}

function ShowRequests(date) {

      $.ajax({
        type: 'POST',
        url: 'page-includes/requests/requests-monthly.php',
        data: {
            'date':date
        },
        success: function (data) {
            document.getElementById('content').style.pointerEvents = 'auto';
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in requests monthly call.');
        }
    });
}

function ShowWeeklyRequests(date) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/requests/requests-weekly.php',
        data: {
            'date':date
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in requests weekly call.');
        }
    });
}

function AdminShowWeeklyRequests() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/requests/requests-weekly.php',
        data: {
          'admin':1
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in requests weekly call.');
        }
    });
}

/**
 * Description : This function call on weekly screen by Admin
 * @param : Date string
 * @param : Group int
 * @param : admin int
 */
function ShowRequestWeeklyAdminByDate(Date, Group) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/requests/requests-weekly.php',
        data: {
            'date': Date,
            'group': Group,
            'admin': 1
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in requests weekly call.');
        }
    });
}

function ShowRequestsAdmin() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/requests/requests-admin.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in requests admin call.');
        }
    });
}

function ShowLocksAdmin(Date) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/requests/locks-admin.php',
        data: {
            'date': Date
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in locks admin call.');
        }
    });
}

function ShowSkillsAdmin() {
     $.ajax({
        type: 'POST',
        url: 'page-includes/teamskills/view/skills-admin.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in skills admin call.');
        }
    });
}

function ShowMySkills() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/skills/skills-myskills.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in my skills call.');
        }
    });
}

function ShowAllTeamSkills() {
        $.ajax({
        type: 'POST',
        url: 'page-includes/teamskills/view/skills-allteamskills.php',
        data: {
            'isMySkillFlag': 1
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in all team skills call.');
        }
    });
}

function ShowSkillsShiftleaders() {
       $.ajax({
        type: 'POST',
        url: 'page-includes/teamskills/view/skills-shiftleaders-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in skill shift leaders call.');
        }
    });
}

function ShowSkillsPhotos() {
  $( '#content' ).load( 'page-includes/skills-photos.php', function() { });
}


function ShowSkillsStaffDuties(base) {
  $( '#content' ).load( 'page-includes/skills-progs-staff-readonly.php?base='+base, function() { });
}

function ShowStaffUtilisation(base) {
  $( '#content' ).load( 'page-includes/admin-report-staff-utilisation.php?base=' + base, function() { });
}

function ShowSkillsReports() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/skills-reports-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in skills reports call.');
        }
    });
}

function ShowAllocationsReports() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/allocations-reports-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations reports call.');
        }
    });
}

function ShowAllocationsCapacity(WeekNumber) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/allocations-reports-capacity.php',
        data: {
            'WeekNumber': WeekNumber
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations reports capacity call.');
        }
    });
}

function ShowAllocationsUsage() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/allocations-usage-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in ping call.');
        }
    });
}

function ShowAllocationsUsageSched(weeknumber) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/allocations-usage-team.php',
        data: {
            'teamId': 0,
            'WeekNumber': weeknumber,
            'refpage': 1
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations usage department call.');
        }
    });
}

function ShowAllocationsUsageArea() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/reports/allocations-usage-area.php',
        data: {
            'teamId': 0,
            'refpage': 1,
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations usage department call.');
        }
    });
}

function ShowAllocationsUsageSummary(pageid) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/reports/allocations-usage-summary-page-holder.php',
        data: {
            'pageid': pageid
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations usage summary call.');
        }
    });
}

function ShowRotasReports() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/allocations-reports-rotas-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations reports rotas call.');
        }
    });
}

/* Allocate red */
function ShowMasterDutiesWeekly() {
        $.ajax({
        type: 'POST',
        url: 'page-includes/reports/masterduties-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in master duties page call.');
        }
    });
}

function ShowMDTeams(refpage) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/masterduties-teams-page-holder.php',
        data: {
            'refpage': refpage
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in master duties teams call.');
        }
    });
}

function ShowMasterDutiesSummary() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/masterduties-summary-page-holder.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in master duties summary call.');
        }
    });
}

function ShowAllocateHelper(teamId) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocate-helper/allocate-helper-page-holder.php',
        data: {
            'SchedulingteamId': teamId
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocate helper call.');
        }
    });
}

function showSchedulingStaffTeamUI() {
    $('#nonSchPersonTab').html("");
    $.ajax({
        type: 'POST',
        url: 'page-includes/users/userstab/process/userStaffTeam.php',
        data: {
            'tab':1,
            'action': 'getSearchTeamStaff',
            'usertype': 1
        },
        success: function (data) {
            $('#schPersonTab').html(data);
        },
        error:function (data) {
            alert('some error found in user Staff Team call.');
        }
    });
}

function showNonSchedulingStaffTeamUI() {
  $('#schPersonTab').html("");
      $.ajax({
        type: 'POST',
        url: 'page-includes/users/userstab/process/userStaffTeam.php',
        data: {
            'tab':2,
            'action': 'getSearchTeamStaff',
            'usertype': 0
        },
        success: function (data) {
            $('#nonSchPersonTab').html(data);
        },
        error:function (data) {
            alert('some error found in user Staff Team call.');
        }
    });
}

function showSchedulingTeamExtraXmasPointUI() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/process/teamXmasPoint.php',
        data: {
            'task': 'extraXmasPointHtmlListCall'
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in team Xmas Point call.');
        }
    });
}

function ShowDivisionalAdmin() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/divisions/view/divisionalAdminUI.php',
        data: {
            tab:4
        },
        success: function (data) {
            $('#adminuserstabs-4').html(data);
        },
        error:function (data) {
            alert('some error found in area Admin UI call.');
        }
    });
}

function ShowAllocationsKey() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-key.php',
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in allocations key call.');
        }
    });
}

function ShowLeaveHelp() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/ajax-calls/leavehelp.php',
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in leave help call.');
        }
    });
}

function ShowLeaveWeeklyHelp() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-help-weekly.php',
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in leave help weekly call.');
        }
    });
}

function ShowSicknessByAreaReports() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/allocations-sickness-by-area-report.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in ping call.');
        }
    });
}

function EditComments(date, base) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/ajax-calls/allocations-editdailycomments.php',
        data: {
            'date': date,
            'base': base
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in allocations edit daily comments call.');
        }
    });
}

function CreateIcal(date, staffnumber, teamId) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-create-ical.php',
        data: {
            'date': date,
            'staffnumber': staffnumber,
            'teamId': teamId
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in allocations create ical call.');
        }
    });
}

function DeleteUser(id) {
    customConfirm('Are you sure you want to delete this job from this master duty?',function(){
            $.ajax({
                type: 'POST',
                url: 'page-includes/ajax-calls/removeuserfrombase.php',
                data: {
                    'id': id
                },
                success: function (data) {
                    ShowUsers();
                },
                error:function (data) {
                    alert('some error found in remove user from base call.');
                }
            });

        },
        function() {
        }
	);
}

function DeleteGuestUser(id) {
  customConfirm('Do you want to delete this User?',function(){
        $.ajax({
            type: 'POST',
            url: 'page-includes/ajax-calls/removeguestuser.php',
            data: {
                'id': id
            },
            success: function (data) {
                ShowGuestUsers();
            },
            error:function (data) {
                alert('some error found in remove guest user call.');
            }
        });
	},
	function() {
	});
}

function ShowVersion () {
      $.ajax({
        type: 'POST',
        url: 'page-includes/version.php',
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in version call.');
        }
    });
}

function DeleteShiftLeader(id) {
  customConfirm('Do you want to delete this Entry?<br/>',function(){
		$.ajax({
            type: 'POST',
            url: 'page-includes/edits/deleteshiftleader.php',
            data: {
                'id': id
            },
            success: function (data) {
                sda ();
            },
            error:function (data) {
                alert('some error found in delete shift leader call.');
            }
        });
		},
		function() {
	});
}

function DeleteEdits(){
      $.ajax({
        type: 'POST',
        url: 'deleteedits.php',
        success: function (data) {
        },
        error:function (data) {
            alert('some error found in delete edits call.');
        }
    });
}

function UserStatusEdit(login, department, currenttab) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/support/status-edit.php',
        data: {
            'login': login,
            'department': department,
            'currenttab': currenttab
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in status edit call.');
        }
    });
}
function UserAdminStaffSupportEdit(login, departmentid) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/support/support-staff-edit.php',
        data: {
            'login': login,
            'departmentid': departmentid
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in support staff edit call.');
        }
    });
}

function UserLeaveGroups(id) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/ajax-calls/admin-leaverequestgroups.php',
        data: {
            'id': id
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in leave request groups call.');
        }
    });
}


function UserAddDepts(id) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/ajax-calls/admin-useradddepts.php',
        data: {
            'id': id
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in admin user add call.');
        }
    });
}


function EditContacts(filetype) {
  $( '#content' ).load( 'page-includes/editcontacts.php?filetype='+filetype, function() { });
}

function LeaveApply(user, date, leavetype, year) {
  // $('*').qtip('hide');
  document.getElementById('content').style.pointerEvents = 'none';
  $("#loader").show();
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-apply.php',
        data: {
            'user': user,
            'date': date,
            'leavetype': leavetype
        },
        success: function (data) {
			 data = JSON.parse(data);
            if((data.strstatus!='' || data.strstatus!=undefined) && (data.strstatus == 0)){
                customAlertByModel('Leave cannot be applied for this user, since the user is neither scheduled nor non-scheduled in any team at the moment. Please assign a team to the user, in order to apply leave');
            }
            ShowLeave (year);
        },
        error:function (data) {
            alert('some error found in leave apply call.');
        }
    });
}

function LeaveApplyAdmin(user, date, leavetype, year) {
  $('*').qtip('hide');
  document.getElementById('content').style.pointerEvents = 'none';
  $("#loader").show();
      $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-apply.php',
        data: {
            'user': user,
            'date': date,
            'leavetype': leavetype
        },
        success: function (data) {
			 data = JSON.parse(data);
              if (data.strstatus == 0) {
                customAlertByModel('Leave cannot be applied for this user, since the user is neither scheduled nor non-scheduled in any team at the moment. Please assign a team to the user, in order to apply leave');
              }
            ShowUserLeave (user, year);
        },
        error:function (data) {
            alert('some error found in leave apply call.');
        }
    });
}

function ImportDuties (DepartmentID, WeekNumber) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/importscheduall.php',
        data: {
            'DepartmentID': DepartmentID,
            'WeekNumber': WeekNumber
        },
        success: function (data) {
            ShowAllocations(DepartmentID, WeekNumber)
        },
        error:function (data) {
            alert('some error found in import scheduall call.');
        }
    });
}
function DeleteDuties (DepartmentID, WeekNumber) {
  $( "#dialog-week-delete" ).dialog(
    {
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
            $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/deletescheduall.php',
                data: {
                    'DepartmentID': DepartmentID,
                    'WeekNumber': WeekNumber
                },
                success: function (data) {
                    ShowAllocations(DepartmentID, WeekNumber)
                },
                error:function (data) {
                    alert('some error found in delete scheduall call.');
                }
            });
        },
        "No": function() {
          $( this ).dialog( "close" );
        },
      }
    }
  );
}

/* Description : Set the master duties filter*/
function ShowMasterDutiesFilter() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/master-duties-filter/master_duties_filter_design.php',
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in master duties filter design call.');
        }
    });
}

$(document).ajaxStart(function() {
  $("#loading").fadeIn(200);
}).ajaxStop(function() {
    $("#loading").fadeOut(300);
  }).ajaxSuccess(function() {
});

$('[title]').qtip({
  style: { classes: 'qtip-rounded qtip-shadow qtip-light'}
});
$('.qtip').click(function(event) {
    api.toggle(false);
})


$(function(){
  $.contextMenu({
    selector: '.sort-order-menu',
    trigger: 'left',
    callback: function(key, options) {
      $('*').qtip('hide');
    },
    items: {
    "starttime": {
        name: "Start Times",
        icon: "sorttime",
        // superseeds "global" callback
        callback: function(key, options) {
          var department =  options.$trigger.attr("department");
          SetSortOrder (0, department);
        }
     },
    "name": {
        name: "Duty Name",
        icon: "sortduty",
        // superseeds "global" callback
        callback: function(key, options) {
          var department =  options.$trigger.attr("department");
          SetSortOrder (1, department);
        }
     },
    "sortcode": {
        name: "Sort Code",
        icon: "sortsortcode",
        // superseeds "global" callback
        callback: function(key, options) {
          var department =  options.$trigger.attr("department");
          SetSortOrder (2, department);
        }
     },
    }
  });
});

function ShowScheduledPerson(searchteamid, searchuserid) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/staff-details/view/searchScheduledPersonUI.php',
        data: {
            'tab':0,
            'searchteamid':searchteamid,
            'searchuserid':searchuserid
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in search Scheduled Person UI call.');
        }
    });
}

function ShowDivisions() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/divisions/view/divisionUI.php',
        data: {
            'tab':0
        },
        success: function (data) {
            $('#systemoptiontabs-5').html(data);
        },
        error:function (data) {
            alert('some error found in division call.');
        }
    });
}

function ConfigureMasterDutyColors() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/area-admin/view/colormasterUI.php',
        data: {
            'tab':0
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in colour master call.');
        }
    });
}

function ShowAllocateUsers() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/allocate-users/view/allocateUsersUI.php',
        data: {
            'tab':0
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in Allocate Users call.');
        }
    });
}

function ShowPublicHolidays() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/holidays/view/holidayUI.php',
        data: {
            'tab':0
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in holiday call.');
        }
    });
}
function showTeamXmasPoint() {
      $.ajax({
        type: 'POST',
        url: 'page-includes/admin/process/teamXmasPoint.php',
        data: {
            'task': 'teamXmaspointHtmlCall'
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in Team XmasPoint call.');
        }
    });
}

//Start for charging
function actionHandler(conrollerName){
	$.ajax({
        type: 'POST',
        url: 'page-includes/admin/charging/index.php',
        data: {
            'conrollerName':conrollerName,
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in charging callback.');
        }
    });
}

function formHandler(formId, conrollerName){
	let frm_element = document.getElementById(formId).elements;
	let loopCount	=	frm_element.length;
	let mandatory	=	'';
	let maxlen		=	'';
	let minlen		=	'';
	let fieldname	=	'';
	let value		=	'';
	let len			=	'';
	let msg			=	'';
	for(let i= 0; i < loopCount; i++)
	{
		mandatory	=	frm_element[i].getAttribute("mandatory");
		maxlen		=	frm_element[i].getAttribute("maxlen");
		minlen		=	frm_element[i].getAttribute("minlen");
		fieldname	=	frm_element[i].getAttribute("fieldname");
		if(frm_element[i].value) {
            value = frm_element[i].value.trim();
            len = value.length;
        }
		else {
		    value = "";
        }
		if( (mandatory == 'yes') && (value == '') )
		{
			msg		=	msg + ' Please enter ' + fieldname + '. <br/>';
		}
		if( (mandatory == 'yes') && (value == -1) )
		{
			msg		=	msg + ' Please select ' + fieldname + '. <br/>';
		}
		if( (maxlen != null) && (len > maxlen) )
		{
			msg		+=	msg + fieldname + ' should not be greater then ' + maxlen + ' chars. <br/>';
		}
		if( (minlen != null) && (len < minlen) )
		{
			msg		+=	msg + fieldname + ' should not be smaller then ' + minlen + ' chars. <br/>';
		}
	}
	if( msg == '')
	{
		$.ajax({
			  type: 'POST',
			  url: 'page-includes/admin/charging/index.php',
			  data: $('#'+formId).serialize(),
			  success: function (returnValue) {
				  chargingCallbackFunc(returnValue);
			}
		});
	} else
	{
        customAlertByModel(msg);
	}
	return false;
}
//Start for charging
</script>
<script src="js/contextMenuAllRed.js?v=<?php echo $mktRandom; ?>"></script>
<script type="text/javascript">
    //Yearly context menu end
  /**
   * Yearly Page menu
   */

function openManagePopup(leaveid,week,callerpage='yearly')
{
    if (leaveid!=undefined && week!=undefined && leaveid!='' && week!='') {
        $.ajax({
            type: 'POST',
            url: 'page-includes/leave/leave-approve-popup.php',
            data: {
                'id': leaveid,
                'week': week,
                'callpage':callerpage,
            },
            success: function (data) {
                $.facebox(data);
            },
            error:function (data) {
                alert('some error found in leave approve popup call.');
            }
        });
    }
}

function HideDay(date, teamId, callerpage, action) {
    $('*').qtip('hide');
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-hideday.php',
        data: {
            'date': date,
            'teamId': teamId,
            'action': action
        },
        success: function (data) {
            switch (parseInt(callerpage, 10)) {
                case 0:
                    ShowDailyAllocations(teamId);
                    break;
                case 1:
                    ShowAllocations(teamId);
                    break;
                case 2:
                    ShowAllocationsMulti(teamId);
                    break;
                case 3:
                    ShowAllocationsDuties(teamId);
                    break;
                case 4:
					if(showWeeks.value > 1)
					{
						getMultiWeekData(showWeeks.value);
					}else
					{
						$("#editWeeklyAllocations").submit();
					}

                    break;
            }
        },
        error:function (data) {
            alert('some error found in allocations hideday call.');
        }
    });
}

function ShowMasterDutiesDepartment(DepartmentID, weeknumber) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/reports/master-duties-team.php',
        data: {
            'departmentid': DepartmentID,
            'WeekNumber': weeknumber
        },
        success: function (data) {
            $('#MDReportTabs-0-'+DepartmentID).html(data);
        },
        error:function (data) {
            alert('some error found in master duties team call.');
        }
    });
}

function ShowRotas() {
    $( '#content' ).load( 'page-includes/rotas/rotas.php', function() { });
}

function ShowMasterJobs() {
    $( '#content' ).load( 'page-includes/master-jobs/jobs.php', function() { });
}

function ShowMasterDutiesDepartmentHolder(DepartmentID) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/master-duties-team-holder.php',
        data: {
            'departmentid': DepartmentID
        },
        success: function (data) {
            $('#MasterDutiesReportTabs-0').html(data);
        },
        error:function (data) {
            alert('some error found in master duties holder call.');
        }
    });
}

function ShowMasterDutiesDepartmentSummary(DepartmentID) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/reports/master-duties-summary.php',
        data: {
            'departmentid': DepartmentID
        },
        success: function (data) {
            $('#MasterDutiesReportTabsSumm').html(data);
        },
        error:function (data) {
            alert('some error found in master duties summary call.');
        }
    });
}

function showadhocduty() {
    $( '#content' ).load( 'page-includes/allocations/add-adhoc-duty.php', function() { });
}

function ShowLeaveByAreaReport(leaveYear = null) {
    $('#loading').show();
    let currentYear = '<?php echo (date('m') < 4) ? date('Y') - 1 : date('Y'); ?>';
    if ((typeof $.cookie('leave_area_report_select') != 'undefined') && (leaveYear == null)) {
        leaveYear = $.cookie('leave_area_report_select');
    } else if(leaveYear == null) {
        leaveYear = currentYear;
    }
    $.cookie("leave_area_report_select", leaveYear);
    $.post("page-includes/reports/leave-by-area-report.php", {
        'leaveYear': leaveYear
    },
    function(data,status){
        $('#content').html(data);
        $('#loading').hide();
    });
}

function KeepSessionAlive() {
    $.ajax({
        type: 'POST',
        url: 'ping.php',
        success: function (data) {
        },
        error:function (data) {
            //Error handling code need to add.
        }
    });
}

function RefreshPage() {
   window.location.href = 'index.php';
}


function cancel() {
    $.facebox.close();
}

$(document).ready(function() {
    $('a[rel*=facebox]').facebox();
    $(document).bind('reveal.facebox', function() {
        $('.popup input:first').focus();
    });
})
async function customAlertByModel(msg)
{
    $("#CustomDivForModal").css("display", "block");
	let messageContainer = '<p>'+msg+'</p>';
	let dialog = $(messageContainer).dialog({
				buttons: {
					"OK": function (){
						dialog.dialog('close');
                        $("#CustomDivForModal").css("display", "none");
                        $(".ui-dialog-content").dialog("close");
					}
				}
			});
    $('.ui-dialog-titlebar-close').addClass("ModalCustomClass");
    $(".ModalCustomClass").on("click", function(){
        $("#CustomDivForModal").css("display", "none");
    });
}

async function customAlert(msg, delayInMilliSec=0)
{
	let messageContainer = '<div style="width: 600px"><table id="customAlert" class="smalltable bluetable" width="100%"><thead><tr><th colspan="5">Message</th></tr></thead><tbody><tr><td style="padding:10px;">'+msg+'</td></tr><tr style="text-align:right;"><td><input type="submit" value="OK" onclick="closeCustomAlert(1)"></td></tr></tbody></table></div><div class="clear"></div>';
	if(delayInMilliSec > 0)
	{
		setTimeout(function() {$.facebox(messageContainer);}, delayInMilliSec);
	}else
	{
		$.facebox(messageContainer);
	}

}

async function customConfirmModal(msg, yesCallback, noCallback, delayInMilliSec=0, screenName='')
{
    let messageContainer = '<p>'+msg+'</p>';
    if(screenName == ''){
      let dialogConfirm = $(messageContainer).dialog({
            buttons: [
              {
                  text: 'Yes',
                  click: function(){
                      yesCallback();
                      dialogConfirm.dialog('close');
                  }
              },
              {
                  text: 'No',
                  click: function(){
                      noCallback();
                      dialogConfirm.dialog('close');
                  }
              }
          ]
        });
    } else {
      if((screenName == 'EditWeeklyPublish') || (screenName == 'EditWeeklyCopyDuty')){
        let dialogConfirm = $(messageContainer).dialog({
            buttons: [
              {
                  tabIndex: -1,
                  text: 'OK',
                  click: function(){
                      yesCallback();
                      dialogConfirm.dialog('close');
                  }
              },
              {
                  text: 'Cancel',
                  click: function(){
                      noCallback();
                      dialogConfirm.dialog('close');
                  }
              }
          ],open: function () {
              $(this).parent().find('button:nth-child(1)').focus();
          }
        });
      } else {
        let dialogConfirm = $(messageContainer).dialog({
            buttons: [
              {
                  text: 'Yes',
                  click: function(){
                      yesCallback();
                      dialogConfirm.dialog('close');
                  }
              },
              {
                  tabIndex: -1,
                  text: 'No',
                  click: function(){
                      noCallback();
                      dialogConfirm.dialog('close');
                  }
              }
          ]
        });
      }
    }

}

function closeCustomAlert()
{
	parent.$.facebox.close();
}
function customConfirm(msg, yesCallback, noCallback, delayInMilliSec=0)
{
	let messageContainer = '<div style="width: 600px"><table id="customConfirm" role="presentation" class="smalltable bluetable" width="100%"><thead><tr><th colspan="5">Please Confirm</th></tr></thead><tbody><tr><td style="padding:10px;">'+msg+'</td></tr><tr style="text-align:right;"><td><input name="yes" id="yes" type="submit" value="Yes" >&nbsp;&nbsp;&nbsp;&nbsp<input name="no" id="no" type="submit" value="No"></td></tr></tbody></table></div><div class="clear"></div>';
	if(delayInMilliSec > 0)
	{
		setTimeout(function() {$.facebox(messageContainer);}, delayInMilliSec);
	}else
	{
		$.facebox(messageContainer);
	}
	$('#yes').click(function() {
		$.facebox.close();
        yesCallback();
    });
    $('#no').click(function() {
		$.facebox.close();
        noCallback();
    });
}

function ShowProductionViewGroupFilters (id) {

  if(id > 0 ){
    $.cookie("selectedeprod", id);
  }
  if(!!$.cookie("selectedeprod")){
    id = $.cookie("selectedeprod");
  }

    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/deptsprodgroups.php',
        data: {
            'id': id
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in depts prod groups call.');
        }
    });
}

function AddEditGroup(id, teamid) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/deptaddeditgroup.php',
        data: {
            'id': id,
            'teamid': teamid
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            alert('some error found in dept add edit group call.');
        }
    });
}

function getTeamProductionFiltersList(id){
  ShowProductionViewGroupFilters(id);
}

function DeleteGroup(id, teamid) {
  $( "#dialog-group-delete" ).dialog(
    {
      width:400,
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
            $.ajax({
                type: 'POST',
                url: 'page-includes/admin/depts-delete-group.php',
                data: {
                    'id': id
                },
                success: function (data) {
                    ShowProductionViewGroupFilters (teamid);
                },
                error:function (data) {
                    alert('some error found in delete group call.');
                }
            });
        },
        "No": function() {
          $( this ).dialog( "close" );
        },
      }
    }
  );
}

function clearProductionViewFilter(teamId){
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/clear-production-view-filter.php',
        data: {
            'teamid': teamId
        },
        success: function (data) {
            var returnData = $.parseJSON(data);
            if(returnData.status){
                ShowAllocationsDuties(teamId);
            }
        },
        error:function (data) {
            alert('some error found in clear production view filter call.');
        }
    });
}

function MoveGroupPosition (id, action, teamid) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/movegroupposition.php',
        data: {
            'id': id,
            'action': action
        },
        success: function (data) {
            ShowProductionViewGroupFilters (teamid);
        },
        error:function (data) {
            alert('some error found in move group position call.');
        }
    });
}

/*Compare Edit Functions Start*/
function RestoreDuty(dutyid, teamId, rdate, weeknumber,isShiftleader) {
  $(function() {
    $( "#dialog-restore-duty" ).dialog(
      {
      width: 800,
      buttons: {
        "Yes - Reshow this dialog": function() {
            $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/allocations-restoreduty.php',
                data: {
                    'dutyid': dutyid,
					'teamId': teamId,
					'weeknumber': weeknumber,
					'isShiftleader': isShiftleader
                },
                success: function (data) {
                    ShowDailyDeletions (teamId, rdate);
                },
                error:function (data) {
                    alert('some error found in allocations restore duty call.');
                }
            });
          $( this ).dialog( "close" );
        },
        "Yes then show DailyAllocations": function() {
          $( this ).dialog( "close" );
            $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/allocations-restoreduty.php',
                data: {
                    'dutyid': dutyid,
					'teamId': teamId,
					'weeknumber': weeknumber,
					'isShiftleader': isShiftleader
                },
                success: function (data) {
                    $.facebox.close();
                    ShowDailyAllocations(teamId);
                },
                error:function (data) {
                    alert('some error found in allocations restore duty call.');
                }
            });
        },
        "Cancel": function() {
          $( this ).dialog( "close" );
        }

      }
    });
  });
}


function RestoreJob(jobid, teamId, rdate) {
  $(function() {
    $( "#dialog-restore-duty" ).dialog(
      {
      width: 800,
      buttons: {
        "Yes - Reshow this dialog": function() {
            $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/allocations-restorejob.php',
                data: {
                    'jobid': jobid,
                },
                success: function (data) {
                    ShowDailyDeletions (teamId, rdate);
                },
                error:function (data) {
                    alert('some error found in allocations restore job call.');
                }
            });
          $( this ).dialog( "close" );
        },
        "Yes then show DailyAllocations": function() {
          $( this ).dialog( "close" );
            $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/allocations-restorejob.php',
                data: {
                    'jobid': jobid,
                },
                success: function (data) {
                    $.facebox.close();
                    ShowDailyAllocations(teamId);
                },
                error:function (data) {
                    alert('some error found in allocations restore job call.');
                }
            });
        },
        "Cancel": function() {
          $( this ).dialog( "close" );
        }

      }
    });
  });
}
/*Compare Edit Function End */

function ShowWeeklyRequestsAdmin(date) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/requests/requests-weekly.php',
        data: {
            'admin':1,
            'date':date
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in requests weekly call.');
        }
    });
}

/** Function For Yearly Page */
function GetTabContent(SelectedTab) {
  if ((SelectedTab >= 0 && SelectedTab <= 3) || (SelectedTab == 7)) {
    $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-admin-needingapproval.php',
        data: {
            'option': SelectedTab
        },
        success: function (data) {
            $('#leaveadmintabs-'+SelectedTab).html(data);
        },
        error:function (data) {
            alert('some error found in leave admin needing approval call.');
        }
    });
  }
  else {
    if (SelectedTab == 5) {
      GetSendEmails ();
    }
      if (SelectedTab == 6) {
        ShowSpoofUser ();
      }
  }
}
function ShowYealyLeaves() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-yearly-holder.php',
        success: function (data) {
            GetTabContent(8);
        },
        error:function (data) {
            alert('some error found in leave yearly holder call.');
        }
    });
}

function dailyallocationPageLoad(chooseDateFormData='Yes', formData={}){
  var form_array = {};
  if(chooseDateFormData == 'Yes'){
    var unindexed_array = $('#chooseDateForm').serializeArray();
    $.map(unindexed_array, function(n, i){
      form_array[n['name']] = n['value'];
    });
    form_array.teamId = form_array.schedulingTeamId;
  } else {
    form_array.teamId = formData.schedulingTeamId;
    form_array.dailyallocationdate = formData.dailyallocationdate;
  }
  ShowDailyAllocations(form_array.teamId, form_array.dailyallocationdate);
  $.facebox.close();
}

function ordinal(number) {
  number = Number(number)
  if(!number || (Math.round(number) !== number)) {
    return number
  }
  var signal = (number < 20) ? number : Number(('' + number).slice(-1))
  switch(signal) {
    case 1:
      return number + 'st'
    case 2:
      return number + 'nd'
    case 3:
      return number + 'rd'
    default:
      return number + 'th'
  }
}
function specialFormat(date) {
  // add two weeks
  var months = [
    'January'
    , 'February'
    , 'March'
    , 'April'
    , 'May'
    , 'June'
    , 'July'
    , 'August'
    , 'September'
    , 'October'
    , 'November'
    , 'December'
  ]
  var formatted = ordinal(date.getDate())
  formatted += ' ' + months[date.getMonth()]
  return formatted + ' ' + date.getFullYear()
}

function hidecustomLeaveToolTip(){
    if($('#leaveTooltipDiv').length > 0){
      $('#leaveTooltipDiv').hide();
    }
}

function ShowSchedulingTeamYearAllocations(teamId, year = '', schedulingPersonId = 0, fromDate = '', toDate = '') {
    if(schedulingPersonId == 0 && $.cookie("edit_yearly_allocations_scheduled_person_id" + teamId) != '' && $.cookie("edit_yearly_allocations_scheduled_person_id" + teamId) != undefined)
    {
        schedulingPersonId = $.cookie("edit_yearly_allocations_scheduled_person_id" + teamId)
    }

    if(fromDate == '' && $.cookie("edit_yearly_allocations_start_date" + teamId) != '' && $.cookie("edit_yearly_allocations_start_date" + teamId) != undefined)
    {
        fromDate = $.cookie("edit_yearly_allocations_start_date" + teamId)
    }

    if(toDate == '' && $.cookie("edit_yearly_allocations_end_date" + teamId) != '' && $.cookie("edit_yearly_allocations_end_date" + teamId) != undefined)
    {
        toDate = $.cookie("edit_yearly_allocations_end_date" + teamId)
    }

    if(year == '' && $.cookie("edit_yearly_allocations_year" + teamId) != '' && $.cookie("edit_yearly_allocations_year" + teamId) != undefined)
    {
        year = $.cookie("edit_yearly_allocations_year" + teamId)
    }
    $.ajax({
        type: 'POST',
        url: '/page-includes/allocations/allocations-edit-year-scheduling-team.php',
        data: {
            'teamId': teamId,
            'schedulingPersonId': schedulingPersonId,
            'fromDate': fromDate,
            'toDate': toDate,
            'date': year
        },
        success: function (data) {
            $('#content').html(data);
        },
        error:function (data) {
            alert('some error found in allocations year call.');
        }
    });
}
<?php
function CallShowHomePHP (): string {
    $commonDbobj = new classCommonDBFunctions();
    $setupObj = new classUserSetup();
    $bbcweeknumberArray =  $commonDbobj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
    $intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? 0;
    $arrLeaveRequestSettings = [];
    $arrUserSettings = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true); //Allocate 7 Menu
    $arrLeaveRequestSettings = json_decode($commonDbobj->userLeaveRequestByNetLogin($_SESSION['user']['user'], 0),true);
    $name	=	ucwords((string) $_SESSION['user']['FullName']);
    $HTMLALLHome = "";
    if (!isset($arrUserSettings['Teams']) || empty($arrUserSettings['Teams'])) {
        $HTMLALLHome = '<h1 class="sr-only">Home</h1><br><br><div style="width:80%; margin:0 auto; position:relative;">
          <div class="tableheadersmall bigtextboldcentre" style="width:100%">
           <br><br>
           Welcome '.$name.' to the Allocate Website.<br>
           We have detected your Network User ID as '.$_SESSION['user']['user'].'.<br>
           Access to view Allocations is restricted to registered users.<br>
           Should you require access please email one of the Administrators for the Team you would like access to.
           <br><br><br>
           </div></div><br>';
           $arrTeams = GetTeamAdmins();
           $HTMLALLHome .= '<div style="width:80%; margin:0 auto; position:relative;">';
           $HTMLALLHome .='<table class="tablesmall" width="100%"><tr>';

            $i = 0;
            foreach ($arrTeams as $arrTeam) {
            if ($i == 4) {
            $HTMLALLHome .= '<tr><td class="tablesmallgrey" colspan="4" height="5px"></td></tr><tr>';
            $i = 0;
            }
            $HTMLALLHome .= '<td valign="top"><table class="tablesmallgrey" width="100%"><tr><th>';
            $HTMLALLHome .= $arrTeam['Team'];
            $HTMLALLHome .= '</th></tr><tr><td>';
            foreach ($arrTeam['Users'] as $strUserFullName) {
            $HTMLALLHome .= $strUserFullName.'<br>';
            }
            $HTMLALLHome .= '</td></tr></table>';

            $HTMLALLHome .= '</td>';
            if ($i == 3) {
            $HTMLALLHome .= '</tr>';
            }
            $i++;
            }
            $HTMLALLHome .= '</table>';
    }
    else {
        $HTMLALLHome .= '<h1 class="sr-only">Home</h1><div class="tableheadersmall medtextboldcentre" style="width: 800px">
       <br></br>
       Welcome '.$name.' to the Allocate Website.
       <br></br>';

        if (isset($arrUserSettings['DefaultTeam']) && $arrUserSettings['DefaultTeam'] !=0) {

            $HTMLALLHome .= 'Your Default Team is '.$arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['schedulingTeamName'];
        }
        else {
            // get the teams the person is scheduled in
            if (isset($arrUserSettings['Teams'])) {
                foreach ($arrUserSettings['Teams'] as $intDepID => $arrDepartment) {
                    if ($arrDepartment['ScheduledPerson'] == 1) {
                        $arrScheduledTeams[$intDepID] = $arrDepartment['schedulingTeamName'];
                    }
                }
            }
            if (isset($arrScheduledTeams)) {
                $HTMLALLHome .= 'You are Scheduled in the following Teams<br><i>'. implode('<br>' ,$arrScheduledTeams).'</i><br> But you don\'t have a Default Team set.<br>You can set your default choice under Admin->My Options';
            }
        }
        $HTMLALLHome .= '<br></br>
      </div><br>';
        $intCanRequestLeave = 0;
        $intCanRequestRequests = 0;
        if (isset($arrLeaveRequestSettings["LeaveRequests"]) && !empty($arrLeaveRequestSettings["LeaveRequests"])) {
            $intCanRequestRequests = 1;
            $intCanRequestLeave = 1;
        }

        if ($arrUserSettings['isScheduledPerson'] == 1 || $arrUserSettings['DefaultTeam'] != 0 || $intCanRequestLeave == 1 || $intCanRequestRequests == 1 || $arrUserSettings['HasHandovers'] == 1) {

            $HTMLALLHome .= '<div class="accessbility-table-wrapper">';
            $HTMLALLHome .= '<h2 class="tableheadersmall bigtextbold" >Quick Links</h2>';
            $HTMLALLHome .= '<table border="1" class="tablesmalltidy" role="presentation">';
            $HTMLALLHome .= '<tr>';
             if ((($arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['SchedulingTeamAdmin'] ?? 0) ==1) || (($arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['Scheduler']?? 0)==1) || (($arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['TeamLeader']??0) ==1)) {
			    //IsScheduled
                $HTMLALLHome .= '<td width="150px" align="center"><a href="#" class="handcursor default-link" onclick=\'javascript:EditAllocations('.$arrUserSettings['DefaultTeam'].',"")\';>';
                $HTMLALLHome .= '<img border="0" src="images/AllocationsEditWeekly.png" width="106px" height="57px" alt=""><br>Edit Weekly Allocations</td>';
            }
            if ($arrUserSettings['isScheduledPerson'] ==1) {
                //IsScheduled
                $HTMLALLHome .= '<td width="150px" align="center"><a href="#" class="handcursor default-link" onclick=\'javascript:ShowRota()\';>';
                $HTMLALLHome .= '<img border="0" src="images/allocations.png" width="106px" height="57px" alt=""><br>Monthly Allocations</td>';
            }
			$intWeekNumber ??= 0;
            if ( isset($arrUserSettings['DefaultTeam']) && ($arrUserSettings['DefaultTeam'] != 0)) {
                $HTMLALLHome .= '<td width="150px" align="center"><a href="#" class="handcursor default-link" onclick=\'javascript:ShowAllocations('.$arrUserSettings['DefaultTeam'].','.$intWeekNumber.',"","",1)\';>';
                $HTMLALLHome .= '<img border="0" src="images/AllocationsWeekly.png" width="106px" height="57px" alt=""><br>Weekly Allocations';

                if ($arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['HasDutiesView']== 1) {
                    $HTMLALLHome .= '<td width="150px" align="center"><a href="#" class="handcursor default-link" onclick=\'javascript:ShowAllocationsDuties('.$arrUserSettings['DefaultTeam'].')\';>';
                    $HTMLALLHome .= '<img border="0" src="images/AllocationsWeeklyDuties.png" width="106px" height="57px" alt=""><br>Production View';
                }
                $ddate = '""';
                $dpage = '""';
                $ckLocalstorage=1;
                $HTMLALLHome .= '<td width="150px" align="center"><a href="#" class="handcursor default-link" onclick=\'javascript:ShowDailyAllocations('.$arrUserSettings['DefaultTeam'].','.$ddate.','.$dpage.','.$ckLocalstorage.')\';>';
                $HTMLALLHome .= '<div class="dailyquicklink">';
                $HTMLALLHome .= '<img border="0" src="images/AllocationsDaily.png" width="106px" height="57px" alt=""><br>Daily Allocations';
                $HTMLALLHome .= '<div class="smalltextboldcentre dailymonth">';
                $HTMLALLHome .= date ("M", time());
                $HTMLALLHome .= '</div>';
                $HTMLALLHome .= '<div class="bigtextboldcentre dailydate">';
                $HTMLALLHome .= date("d", time());
                $HTMLALLHome .= '</div>';
                $HTMLALLHome .= '</div></a></td>';
            }
            // Only show leave if the user is NOT an admin in one

            if ($intCanRequestLeave == 1) {
                $HTMLALLHome .= '<td width="150px" align="center"><a href="#" class="handcursor default-link" onclick=\'javascript:ShowLeave("'.date("Y").'")\';>';
                $HTMLALLHome .= '<img border="0" src="images/holiday.png" width="106px" height="57px" alt=""><br>My Leave</td>';
            }
            if ($intCanRequestRequests == 1) {
                $HTMLALLHome .= '<td width="150px" align="center"><a href="#" class="handcursor default-link" onclick=\'javascript:ShowRequests()\';> ';
                $HTMLALLHome .= '<img border="0" src="images/requests.png" width="106px" height="57px" alt=""><br>My Requests</td>';
            }

            if ($arrUserSettings['HasHandovers'] == 1) {
                $HTMLALLHome .= '<td width="150px" align="center"><a href="#" class="handcursor default-link" onclick=\'javascript:ShowHandovers()\';> ';
                $HTMLALLHome .= '<img border="0" src="images/handovers.png" width="106px" height="57px" alt=""><br>Handovers</td>';
            }

            $HTMLALLHome .= '</tr>';
            $HTMLALLHome .= '</table>';
            $HTMLALLHome .= '</div>';
        }
        $HTMLALLHome .= '<br><br>';

        $HTMLALLHome .= '<h2 class="tableheadersmall bigtextbold"  style="width: 400px;">Keyboard Shortcuts</h2>';
        $HTMLALLHome .= '<table border="0" class="tablesmall" role="presentation">';
        $HTMLALLHome .= '<tr class="even">';
        $HTMLALLHome .= '<td width="100px">&lt;alt&gt; l</td>';
        $HTMLALLHome .= '<td width="300px">My Leave Requests</td>';
        $HTMLALLHome .= '</tr>';
        $HTMLALLHome .= '<tr class="even">';
        $HTMLALLHome .= '<td width="100px">&lt;alt&gt; r</td>';
        $HTMLALLHome .= '<td width="300px">My Monthly Allocations</td>';
        $HTMLALLHome .= '</tr>';
        $HTMLALLHome .= '<tr class="even">';
        $HTMLALLHome .= '<td width="100px">&lt;alt&gt; q</td>';
        $HTMLALLHome .= '<td width="300px">Daily Allocations with Date Selection</td>';
        $HTMLALLHome .= '</tr>';
        $HTMLALLHome .= '<tr class="even">';
        $HTMLALLHome .= '<td width="100px">&lt;alt&gt; g</td>';
        $HTMLALLHome .= '<td width="300px">Daily Allocations</td>';
        $HTMLALLHome .= '</tr>';
        if (isset($arrUserSettings['DefaultTeam']) && $arrUserSettings['DefaultTeam'] != 0 && ($arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['Scheduler'] == 1 || $arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['SchedulingTeamAdmin'] == 1 || $arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['TeamLeader'] == 1)) {
            $HTMLALLHome .= '<tr class="even">';
            $HTMLALLHome .= '<td width="100px">&lt;alt&gt; u</td>';
            $HTMLALLHome .= '<td width="300px">Edit Weekly Allocations with Week Selection</td>';
            $HTMLALLHome .= '</tr>';
        }
        $HTMLALLHome .= '<tr class="even">';
        $HTMLALLHome .= '<td width="100px">&lt;alt&gt; w</td>';
        $HTMLALLHome .= '<td width="300px">Weekly Allocations</td>';
        $HTMLALLHome .= '</tr>';
        $HTMLALLHome .= '</table>';
    }
    return $HTMLALLHome;
}


if (isset($_REQUEST['page']) && $_SESSION['allocations']['staffnumber'] != 0) {
  if ($intIsDown == 0) {
    $showpage = $pageurls[$_REQUEST['page']];
    echo $showpage.'()';
  }
  else {
    echo "ShowIsDown()\n";
  }
  echo '</script>';
}
else {
  if ($intIsDown != 0) {
    echo "ShowIsDown()\n";
  }
  echo "</script>";
}
?>