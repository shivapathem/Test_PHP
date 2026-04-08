<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$commonObj = new classCommonDBFunctions();
$setupObj = new classUserSetup(); 
$intSysAdmin =  $commonObj->UserIsSysAdmin($userID);
$loggedUsedInfo = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
if (($intSysAdmin == 1) || !empty($userDivisionsList) || $loggedUsedInfo['isSchedulingTeamAdmin'] == 1) {

    if(isset($loggedUsedInfo['DivisionalAdmin'])){
        $isDivisionalAdmin = $loggedUsedInfo['DivisionalAdmin'];
    }else{
        $isDivisionalAdmin = 0;
    }

    if($intSysAdmin != 1){
        $hidetabshiftleader = "hidden";
    }
    else {
        $hidetabshiftleader = "";
    }

    if($intSysAdmin == 1 || $isDivisionalAdmin==1){
        $hidetableavemapping = "";
    }
    else {
        $hidetableavemapping = "hidden";
    }

    $hideAreaTab = $loggedUsedInfo['isSchedulingTeamAdmin'] == 1 || $intSysAdmin == 1 || $isDivisionalAdmin==1 ? '' : 'hidden';
        echo '<h1 class="sr-only">System</h1>';
        echo'<div id="systemoptiontabs" style="width:100%">';
        echo'<ul>';
        echo'<li><a href="#systemoptiontabs-1">Shiftleaders</a></li>';
        echo'<li '. ($intSysAdmin == 1 ? '' : 'hidden') .'><a href="#systemoptiontabs-2">Leave Mapping</a></li>';
        echo'<li '.$hidetableavemapping.'><a href="#systemoptiontabs-3">Text Colours</a></li>';
        echo'<li '.$hidetableavemapping.'><a href="#systemoptiontabs-4">Reports Config</a></li>';
        echo'<li '.$hideAreaTab.'><a href="#systemoptiontabs-5">Area</a></li>';
        echo'<li '.$hidetabshiftleader.'><a href="#systemoptiontabs-6">System Admin Users</a></li>';
        echo'<li '.$hidetabshiftleader.'><a href="#systemoptiontabs-7">Welcome Message</a></li>';
        echo'<li '.$hidetabshiftleader.'><a href="#systemoptiontabs-8">Down Time</a></li>';
        echo'</ul>';
        echo'<div id="systemoptiontabs-1"></div>';
        echo'<div id="systemoptiontabs-2"></div>';
        echo'<div id="systemoptiontabs-3"></div>';
        echo'<div id="systemoptiontabs-4"></div>';
        echo'<div id="systemoptiontabs-5"></div>';
        echo'<div id="systemoptiontabs-6"></div>';
        echo'<div id="systemoptiontabs-7"></div>';
        echo'<div id="systemoptiontabs-8"></div>';
        echo '</div>';
} else {
  echo 'Access Denied'; die;
}
?>
<script type="text/javascript">
let intSysAdmin = <?php echo ($intSysAdmin == '' ? 0 : $intSysAdmin); ?>;
$(document).ready(function(){
	ShowShiftLeaders();
  $(function() {
    $( "#systemoptiontabs" ).tabs({
    heightStyle: "content",
        create: function( event, ui ) {
          $('#systemoptiontabs .ui-tabs-panel').removeAttr('aria-labelledby');
        },
        activate : function( event, ui ) {
          var active = $( "#systemoptiontabs" ).tabs( "option", "active" );

        switch (active) {
          case 0:
          ShowShiftLeaders();
          break;    
          case 1:
          ShowLeaveMapping();
          break; 
          case 2:
          ShowSystemAdminColours();
          break; 
          case 3:
          ShowReportsConfig(); 
          break; 
          case 4:
          ShowDivisions();
          break;	
          case 5:
          ShowSystemAdminUsers();
          break;
          case 6:
          ShowWelcomeMessage();
          break;                             
          case 7:
          ShowDownTime();
          break;
          case 8:
          ShowDebug();
          break;        
        }
      }
      
    });
  });
});

function ShowSystemAdminUsers() {
  $.post("page-includes/admin/system-admin.php", {
  },
  function(data,status){
    $('#systemoptiontabs-6').html(data);
   }
  )
}

function ShowShiftLeaders() {
  $.post("page-includes/admin/system-admin-shift-leaders.php", {
  },
  function(data,status){
    $('#systemoptiontabs-1').html(data);
   }
  )
}

function ShowSystemAdminColours() {
  $.post("page-includes/admin/system-admin-staff-colours.php", {
  },
  function(data,status){
    $('#systemoptiontabs-3').html(data);
   }
  )
}

function ShowBreaksTable() {
  $.post("page-includes/admin/system-admin-breaks-holder.php", {
  },
  function(data,status){
    $('#systemoptiontabs-4').html(data);
   }
  )
}
function ShowSortcodeMapping() {
  $.post("page-includes/admin/system-sortcode-mapping.php", {
  },
  function(data,status){
    $('#systemoptiontabs-5').html(data);
   }
  )
}
function ShowLeaveMapping() {
  $.post("page-includes/admin/system-admin-leave-mapping.php", {
  },
  function(data,status){
    $('#systemoptiontabs-2').html(data);
   }
  )
}

function ShowReportsConfig() {
  $.post("page-includes/admin/system-admin-reports-config.php", {
  },
  function(data,status){
    $('#systemoptiontabs-4').html(data);
   }
  )
}


function ShowGuestUsers() {
  $.post("page-includes/admin/system-admin-guest-users.php", {
  },
  function(data,status){
    $('#systemoptiontabs-7').html(data);
   }
  )
}
function ShowHomeShortcuts() {
  $.post("page-includes/admin/system-home-shortcuts.php", {
  },
  function(data,status){
    $('#systemoptiontabs-5').html(data);
   }
  )
}

function ShowWelcomeMessage() {
  $.post("page-includes/admin/system-welcome-message.php", {
  },
  function(data,status){
    $('#systemoptiontabs-7').html(data);
   }
  )
}
function ShowDownTime() {

  $.ajax({
        type: 'POST',
        url: 'page-includes/admin/system-down-time.php',
        success: function (data) {
            $('#systemoptiontabs-8').html(data);
        },
        error:function (data) {
            alert('some error found in system down time call.');
        }
    });
}
function ShowDebug() {
  $.post("page-includes/admin/system-debug.php", {
  },
  function(data,status){
    $('#systemoptiontabs-9').html(data);
   }
  )
}

function MoveMappingPosition (id, action) {
  $.post("page-includes/admin/system-admin-move-leavemapping-position.php", {
    id: id,
    action: action
  },
  function(data,status){
    ShowLeaveMapping ();
  })
}

function EditLeaveMapping (id) {
  $.post("page-includes/admin/system-admin-edit-leavemapping.php", {
    id: id
  },
  function(data,status){
    $.facebox(data);
  })
}
</script>
