<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$commonObj = new classCommonDBFunctions();
$arrUserLeaveSetting = json_decode($commonObj->userLeaveRequestByNetLogin($strUser, 0),true);
$isLeaveManagerOnly = 1;
foreach ($arrUserLeaveSetting['LeaveRequests'] as $intGroupID => $arrGroup) {
  if ($arrGroup['Admin'] >= 1) {
    $arrGroupsCanRequest[$intGroupID] = $arrGroup;
    if($arrGroup['Admin'] != 3) {
      $isLeaveManagerOnly = 0;
    }
  }
}
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h1 style="text-align: center;">Leave Administration</h1><br>Click on a row to Highlight it and then choose an option from the Tabs.<br><br>';
echo '</div>';

if (!isset($arrGroupsCanRequest)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Leave groups defined!<br>Please contact your Scheduling Team administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}
else {
    echo '  <div class="leaveadmintabs">';
    echo '    <ul>';
    echo '      <li><a href="#leaveadmintabs-0" ' . ($isLeaveManagerOnly == 1 ? 'style="display:none"' : '') . '>Short Notice</a></li>';
    echo '      <li><a href="#leaveadmintabs-1" ' . ($isLeaveManagerOnly == 1 ? 'style="display:none"' : '') . '>Guaranteed</a></li>';
    echo '      <li><a href="#leaveadmintabs-2">Waiting List</a></li>';
    echo '      <li><a href="#leaveadmintabs-3" ' . ($isLeaveManagerOnly == 1 ? 'style="display:none"' : '') . '>Unlikely List</a></li>';
    echo '      <li><a href="#leaveadmintabs-4">Agreed</a></li>'; 
    echo '      <li><a href="#leaveadmintabs-5">Weekly Leave</a></li>';
    echo '      <li><a href="#leaveadmintabs-6" ' . ($isLeaveManagerOnly == 1 ? 'style="display:none"' : '') . '>Send Emails</a></li>';
    echo '      <li><a href="#leaveadmintabs-7" ' . ($isLeaveManagerOnly == 1 ? 'style="display:none"' : '') . '>Spoof User</a></li>';
    echo '      <li><a href="#leaveadmintabs-8" ' . ($isLeaveManagerOnly == 1 ? 'style="display:none"' : '') . '>Yearly Staff Leave</a></li>';     
    echo '    </ul>';
    echo '    <div id="leaveadmintabs-0">';
    echo '    </div>';
    echo '    <div id="leaveadmintabs-1">';
    echo '    </div>';
    echo '    <div id="leaveadmintabs-2">';
    echo '    </div>';
    echo '    <div id="leaveadmintabs-3">';
    echo '    </div>';     
    echo '    <div id="leaveadmintabs-4">';
    echo '    </div>';        
    echo '    <div id="leaveadmintabs-5">';
    echo '    </div>'; 
    echo '    <div id="leaveadmintabs-6">';
    echo '    </div>';     
    echo '    <div id="leaveadmintabs-7">';
    echo '    </div>'; 
    echo '    <div id="leaveadmintabs-8">';
    echo '    </div>';  
    echo '  </div>';
}
$intFirstKey = array_keys($arrGroupsCanRequest)[0];
?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $(".leaveadmintabs").tabs({
      <?php if($isLeaveManagerOnly == 1) { ?>
        active: 2,
      <?php } ?>
      heightStyle: 'content', 
      disabled: [5],
      activate : function( event, ui ) {
        var SelectedTab = $(".leaveadmintabs").tabs( "option", "active" );
          GetTabContent(SelectedTab);
      }      
    });
  });
  <?php if($isLeaveManagerOnly == 0) { ?>
    GetTabContent(0);
  <?php } else { ?>
    GetTabContent(2);
  <?php } ?>
});

function GetSendEmails(Login = null){ 
  $.post("page-includes/leave/leave-emails-to-send.php", {
    openAccordionLogin:Login
  },  
  function(data,status){
    $('#leaveadmintabs-6').html(data);
  })
}

function ShowSpoofUser(){
  $.post("page-includes/leave/leave-spoof-holder.php", {
  },  
  function(data,status){
    $('#leaveadmintabs-7').html(data);
  })
}

function GetTabContent (SelectedTab, Login = null) {
  if ((SelectedTab >= 0 && SelectedTab <= 4) || (SelectedTab == 8)) { 
    $.post("page-includes/leave/leave-admin-needingapproval.php", {
      option: SelectedTab
    },  
    function(data,status){
      $('#leaveadmintabs-'+SelectedTab).html(data); 
    })
  }
  else {
    if (SelectedTab == 6) {
      GetSendEmails (Login);
    }
      if (SelectedTab == 7) {
        ShowSpoofUser ();         
      } 
   
  }  
}

  function ShowLeaveWeeklyAdmin (Week, Group, showActive='Y') {
    $.cookie('leaveWeeklyTabWeekNum',Week);
    $.cookie('leaveWeeklyTabGroupId',Group);
    if(showActive=='Y'){
      $( ".leaveadmintabs" ).tabs( "enable", 5 );
    }
    $.ajax({
          type: 'POST',
          url:"page-includes/leave/leave-weekly.php",
          data: {
          week: Week,
          group: Group,
          admin: 1,
          tabCall: 1
        },
        success:function(data,status){
          $('#leaveadmintabs-5').html(data);
        }
    });
    if(showActive=='Y'){
      $( ".leaveadmintabs" ).tabs( "option", "active", 5 );
    }
  } 
  
  function ShowLeaveWeeklyAdminByDate (Date, Group) {
    $( ".leaveadmintabs" ).tabs( "enable", 5 );
    $.ajax({ type: 'POST',
          url:"page-includes/leave/leave-weekly.php",
          data: {
      date: Date,
      group: Group,
      admin: 1
    },
    success:function(data,status){
      $('#leaveadmintabs-5').html(data);
     }
    });       
    $( ".leaveadmintabs" ).tabs( "option", "active", 5 );
  }  

  function refreshLeaveWeeklyTabData(){
    let weekNum = $.cookie('leaveWeeklyTabWeekNum');
    let groupId = $.cookie('leaveWeeklyTabGroupId');
    ShowLeaveWeeklyAdmin(weekNum,groupId,'N');
  }


</script>


