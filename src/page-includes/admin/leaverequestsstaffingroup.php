<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/common/classCommonDBFunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intGroupID = $_REQUEST['id'];

$commonObj = new classCommonDBFunctions();
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$isDivisionalAdmin = $commonObj->UserIsDivAdmin($sessUserId);
$isSysAdmin = $_SESSION['user']['SysAdmin']==''? 0:$_SESSION['user']['SysAdmin'];
if ($isSysAdmin == 1 || $isDivisionalAdmin==1) {
  $intAdminLevel = 2;
}
else {
  $intAdminLevel = $commonObj->GetAdminLeaveRequestGroupsAdminFromLogin ($strUser, $intGroupID);
}
// ###################################################################### Get the departments this user can administer.....

$arrGroupsAndTeams = GetLeaveGroupAndTeamsFromID($intGroupID);
$arrUsersInGroup = GetStaffInLeaveGroup($intGroupID);
$arrUsersNotInGroup = json_decode(GetStaffNotInLeaveGroup($intGroupID),true);

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h1 style="text-align:center;">Staff - Leave and Requests Administration for \''.$arrGroupsAndTeams[$intGroupID]['Description'].'\''.'</h1>';
if ($intAdminLevel != 2) {
  echo '&nbsp;&circledR;';
}

echo '<br>
      Once a user is added to the Group you can assign permissions to them..<br>
      Double-Click in the list on the right to add staff. Double-Click on the left table to remove someone.<br><br>';
echo '</div>';
  echo '<table class="tablesmall compact stripe" id="leaveingroup">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Name';
  echo '</th>';
  echo '<th>';
  echo 'User Name';
  echo '</th>';
  echo '<th>';
  echo 'Home Scheduling Team';
  echo '</th>';
  echo '<th>';
  echo 'Grouped Requests %';
  echo '</th>';
  echo '<th>';
  echo 'Seperately<br>Counted Requests %';
  echo '</th>';
  echo '<th>';
  echo 'Summer Leave %';
  echo '</th>';
  echo '<th>';
  echo 'Notes';
  echo '</th>';
  echo '<th>';
  echo 'Admin';
  echo '</th>';  
  echo '<th>';
  echo 'History';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';

  echo '<tbody>';
  if (isset($arrUsersInGroup)) {
    foreach ($arrUsersInGroup as $strLogin => $arrUser) {
      if ($intAdminLevel >= 1) {
        echo '<tr ondblclick="javascript:RemoveUserFromLeaveGroup(\''.$strLogin.'\');" class="handcursor">';
      }
      else {
        echo '<tr>';           
      }
      echo '<td';
      if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
        echo ' style="color:red;"';
      }
      echo '>';
      echo $arrUser['Name'];
      echo '</td>';
      echo '<td';
      if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
        echo ' style="color:red;"';
      }
      echo '>';
      echo $strLogin;
      echo '</td>';
      echo '<td'; 
      if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
        echo ' style="color:red;"';
      }
      echo '>';          
      echo $arrUser['Team'];
      echo '</td>';  
      if ($intAdminLevel >= 1) {
        echo '<td onclick="javascript:EditLeaveEFT(\''.$strLogin.'\')"';
        if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
          echo ' style="color:red;"';
        }
        echo '>';
        echo $arrUser['EFT'].'%';
        echo '</td>';      

        echo '<td onclick="javascript:EditLeaveEFT(\''.$strLogin.'\')"';
        if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
          echo ' style="color:red;"';
        }
        echo '>';
        echo $arrUser['EFT1'].'%';
        echo '</td>'; 

        echo '<td class="handcursor" onclick="javascript:EditLeaveEFT(\''.$strLogin.'\')"';
        if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
          echo ' style="color:red;"';
        }
        echo '>';
        echo $arrUser['EFTSummer'].'%';
        echo '</td>'; 
        
        echo '<td align="center" onclick="javascript:EditLeaveEFT(\''.$strLogin.'\')">';
          if ($arrUser['EFTNotes'] != 0) {
            echo '<img Login="'.$strLogin.'" class="tipremoteEFTcomments" border="0" src="../images/info.png" width="12px" height="12px">'; 
          }
        echo '</td>';         
        
      } 
      else {
        echo '<td';
        if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
          echo ' style="color:red;"';
        }
        echo '>';
        echo $arrUser['EFT'].'%';
        echo '</td>';      

        echo '<td';
        if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
          echo ' style="color:red;"';
        }
        echo '>';
        echo $arrUser['EFT1'].'%';
        echo '</td>'; 

        echo '<td';
        if ($arrUser['Team'] == 'Archive' || $arrUser['Team'] == 'Freelancers' || $arrUser['Team'] == 'Other BBC'){
          echo ' style="color:red;"';
        }
        echo '>';
        echo $arrUser['EFTSummer'].'%';
        echo '</td>'; 
              
        echo '<td align="center" >';
        if ($arrUser['EFTNotes'] == 0) {
          //echo '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';
        }
        else {
          echo '<img Login="'.$strLogin.'" class="tipremoteEFTcomments" border="0" src="../images/info.png" width="12px" height="12px">'; 
        }
        echo '</td>'; 
      }           
       // Are they an admin
      echo '<td align="center">';
      echo '<span style="display:none">'.$arrUser["Admin"].'</span>';
      if ($intAdminLevel == 2) {
        switch ($arrUser["Admin"]) {
          case 3:
            echo '<img onclick="javascript:EditLeaveAtributes(\''.$strLogin.'\');" border="0" src="../images/purple_tick.png" width="12px" height="12px">';          
            break;
          case 2:
            echo '<img onclick="javascript:EditLeaveAtributes(\''.$strLogin.'\');" border="0" src="../images/green_tick.png" width="12px" height="12px">';          
            break;
          case 1:
            echo '<img onclick="javascript:EditLeaveAtributes(\''.$strLogin.'\');" border="0" src="../images/yellow_tick.png" width="12px" height="12px">';  
            break;        
          default:
            echo '<img onclick="javascript:EditLeaveAtributes(\''.$strLogin.'\');" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
            break;
        }
      }
      else {
        switch ($arrUser["Admin"]) {
          case 3:
            echo '<img border="0" src="../images/purple_tick.png" width="12px" height="12px">';          
            break;
          case 2:
            echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';          
            break;
          case 1:
            echo '<img border="0" src="../images/yellow_tick.png" width="12px" height="12px">';  
            break;        
          default:
            echo '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';    
            break;
        }      
      }
      echo '</td>';
      
      echo '<td align="center">';    
      echo '<img border="0" onclick="javascript:showHistory(\''.$arrUser['staffLeaveGroupId'].'\' ,\''.$strLogin.'\');" src="../images/info.png" width="12px" height="12px">';    
      echo '</td>';  
                        
      echo '</tr>';
    }
  }
echo '</tbody>';
echo '</table>';  


// The staff that can be added....
// Tabs here.....

echo'<div id="leavestaffavailabletabs">';
echo'<ul>';
echo'<li><a href="#leavestaffavailabletabs-1">Allocate Users</a></li>';  
echo'</ul>';

echo'<div id="leavestaffavailabletabs-1">';

  echo '<table class="tablesmall compact stripe" id="usergroupavailabletable">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Name';
  echo '</th>';
  echo '<th>';
  echo 'Home Scheduling Team';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
if (isset($arrUsersNotInGroup)) {
  foreach ($arrUsersNotInGroup as $strLogin => $arrUser) {
    if ($intAdminLevel >= 1) {   
      echo '<tr ondblclick="javascript:AddUserToLeaveGroup(\'' . $arrUser['NetLogin'] . '\',\''.$arrUser['ScheduledPersonID'].'\');" class="handcursor">';
    } else {
      echo '<tr>';
    }
    echo '<td valign="top"';
    if ($arrUser['teamname'] == 'Archive' || $arrUser['teamname'] == 'Freelancers' || $arrUser['teamname'] == 'Other BBC'){
      echo ' style="color:red;"';
    }
    echo '>';
    echo $arrUser['userDisplayName'];
    echo '</td>';
    echo '<td';
    if ($arrUser['teamname'] == 'Archive' || $arrUser['teamname'] == 'Freelancers' || $arrUser['teamname'] == 'Other BBC'){
      echo ' style="color:red;"';
    }
    echo '>';
    echo $arrUser['teamname'];
    echo '</td>';
    echo '</tr>';
  }
}
echo '</tbody>';
echo '</table>';
  

echo'</div>';





/*

*/
  
  
echo '</div>';

?>
<div id="dialog-change-status" title="Leave Administrator" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
<table>
  <tr>
    <td colspan="2">Change Leave Group Admin<br>
    There are 4 Levels to Choose</td>
  </tr>
  <tr>
    <td>Requester</td>
    <td>This user can request Leave in this group.</td>
  </tr>
  <tr>
    <td>Leave Manager</td>
    <td>This user can agree Leave in this group.</td>
  </tr>
  <tr>
    <td>Authoriser</td>
    <td>This user can authorise Leave in this group.<br>
    They also have certain administrative privileges </td>
  </tr>
  <tr>
    <td>Administrator</td>
    <td>This user can administer this group.</td>
  </tr>
</table>
</div>


<script type="text/javascript">

$(document).ready( function () {
  var tabCookieName = "leavestaffavailabletabs";
  $(function() {
    $( "#leavestaffavailabletabs" ).tabs({
      active : ($.cookie(tabCookieName) || "0"),
      activate : function( event, ui ) {
        var newIndex = ui.newTab.parent().children().index(ui.newTab);
        $.cookie(tabCookieName, newIndex, { expires: 1 });
        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
      }
    });
  }); 

  var table1 = $("#leaveingroup").DataTable({
    paging: false,
    destroy: true,
    scrollY: parseInt($(window).height() - 270),
    info:     false,
    stateSave: true,
    deferRender: true,
    order: [0],
    autoWidth: false,
  "columnDefs": [
    { "orderable": false, "targets": 8 },
  ]     
  });
  yadcf.init(table1, [
    {column_number: 0,
       filter_type: 'text'
    },
    {column_number: 1,
       filter_type: 'text'
    },
    {column_number: 2,
      filter_type: 'text'
    },
    ]);
    
    if ( $.cookie("leaveingroup") !== null ) {
      scrollPos = $.cookie("leaveingroup");
      $('#leaveingroup').closest('.dataTables_scrollBody').scrollTop(scrollPos);      
    };
    
  var table2 = $("#usergroupavailabletable").DataTable({
    paging: true,
    destroy: true,
    iDisplayLength: 50,
    scrollY: parseInt($(window).height() - 308),
    info:     false,
    stateSave: true,
    deferRender: true,
    order: [0],
    autoWidth: false,
  });
  yadcf.init(table2, [
    {column_number: 0,
       filter_type: 'text'
    },
    {column_number: 1,
      filter_type: 'text'
    },
    ]);
    if ( $.cookie("usergroupavailabletable") !== null ) {
      scrollPos = $.cookie("usergroupavailabletable");
      $('#usergroupavailabletable').closest('.dataTables_scrollBody').scrollTop(scrollPos);      
    };

    $(".dataTables_paginate").css('visibility', 'visible');
  $('#adminleavetabs').height($(window).height() - 70);
  
  $('.tipremoteEFTcomments').each(function() {
    $(this).qtip({
      content: {
        text: function(event, api) {
          $.ajax({
            url: 'page-includes/admin/leave-eft-comments.php?Login=' + api.elements.target.attr('Login') + '&GroupID=<?php echo $intGroupID?>'
          })
          .then(function(content) {
            // Set the tooltip content upon successful retrieval
            api.set('content.text', content);
          },
          function(xhr, status, error) {
            // Upon failure... set the tooltip content to error
            api.set('content.text', status + ': ' + error);
          });
          return 'Loading...'; // Set some initial text
        }
      },
      position: {
         viewport: $(window)
      },
      style: 'qtip-rounded qtip-shadow qtip-light'
    });
  }); 
  
})
$('#leaveingroup').closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $('#leaveingroup').closest('.dataTables_scrollBody').scrollTop();
  $.cookie("leaveingroup", currpos);
});
$('#usergroupavailabletable').closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $('#usergroupavailabletable').closest('.dataTables_scrollBody').scrollTop();
  $.cookie("usergroupavailabletable", currpos);
});


function EditLeaveAtributes(LogIn) {
    if (LogIn == '<?php echo $strUser?>' && (<?php echo $isSysAdmin ?> != 1 && <?php echo $isDivisionalAdmin ?> != 1)) {
          $( "#dialog-no-edit" ).dialog(
          {
          width: 600,
          buttons: {
          "Cancel": function()

          { $( this ).dialog( "close" ); }
          }
          });
    }
    else{
        $( "#dialog-change-status" ).dialog(
        {
          width: 600,
          height: 300,
          buttons: {
                "Requester": function() {
                $( this ).dialog( "close" );
                $.ajax({
                    url: "page-includes/admin/leavechangestaffstatus.php",
                    type: "POST",
                    data: {  login: LogIn, action: 0, groupid: <?php echo $intGroupID?>},
                    success: function (data,status) {
                      ShowLeaveStaff(<?php echo $intGroupID?>) 
                    }
                });
              
                },
                "Leave Manager": function() {
                  $( this ).dialog( "close" );
                  $.ajax({
                      url: "page-includes/admin/leavechangestaffstatus.php",
                      type: "POST",
                      data: {  login: LogIn, action: 3, groupid: <?php echo $intGroupID?>},
                      success: function (data,status) {
                        ShowLeaveStaff(<?php echo $intGroupID?>) 
                      }
                  });              
                },
                "Authoriser": function() {
                $( this ).dialog( "close" );
                $.ajax({
                    url: "page-includes/admin/leavechangestaffstatus.php",
                    type: "POST",
                    data: {login: LogIn, action: 1, groupid: <?php echo $intGroupID?>},
                    success: function (data,status) {
                      ShowLeaveStaff(<?php echo $intGroupID?>) 
                    }
                });
               
                },
                "Administrator": function() {
                $( this ).dialog( "close" );
                $.ajax({
                    url: "page-includes/admin/leavechangestaffstatus.php",
                    type: "POST",
                    data: {login: LogIn, action: 2, groupid: <?php echo $intGroupID?>},
                    success: function (data,status) {
                      ShowLeaveStaff(<?php echo $intGroupID?>) 
                    }
                });
                },
                "Cancel": function() { $( this ).dialog( "close" ); },
              }
        });
    };
}

function EditLeaveEFT(LogIn) {
  $.ajax({
        url: "page-includes/admin/leaveediteft.php",
        type: "POST",
        dataType: "HTML",
        data: {  login: LogIn,
                  groupid: <?php echo $intGroupID?>},
        success: function (data,status) {
          $.facebox(data); 
        }
    });
}
  
function AddUserToLeaveGroup (LogIn, ScheduledPersonID) {
    $.ajax({
            url: "page-includes/admin/leavegroupadduser.php",
            type: "POST",
            data: { 
                    login: LogIn, 
                    scheduledPersonID : ScheduledPersonID,
                    group: <?php echo $intGroupID?>
                  },
            success: function (data,status) {
              ShowLeaveStaff(<?php echo $intGroupID?>) 
            }
        });
 
}
function RemoveUserFromLeaveGroup (LogIn) {
  $.ajax({
            url: "page-includes/admin/leavegroupremoveuser.php",
            type: "POST",
            data: { login: LogIn,
                    group: <?php echo $intGroupID?>},
            success: function (data,status) {
              ShowLeaveStaff(<?php echo $intGroupID?>) 
            }
      });
  
}

function showHistory(schedulingteamid,strLogin) {
  $.ajax({
        url: "function-includes/common/common.php",
        type: "POST",
        dataType: "HTML",
        data: {   strLogin: strLogin,
            action: 'leavenrequesthistory',
            modulename: 'Staff&Percentages'},
        success: function (data,status) {
          $.facebox(data); 
        }
    });
}
</script>