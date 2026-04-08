<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/adminfunctions.php';
include_once '../users/process/classUserSetup.php';
$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
if (($intSysAdmin == 1) || !empty($userDivisionsList)) {

$rsAdminUsers = json_decode(getSystemAdminList(),true);

$rsAllocateUsers = json_decode(getNotSystemAdminList(), true);

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h2 aria-label="System Administration">System Administration</h2><br>Staff Scheduled in Allocate or Guest Users may be given System Admin rights.<br>Do Not remove yourself from this list or you will loose your access!.<br><br>';
echo '</div>';
echo '<br>';
echo '<table><tr><td>';
echo '<div style="width:600px; height:600px; position: relative; left:0px; top:0px;">';
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h3>Current System Administrators</h3><br>';
echo '</div>';
echo '<table class="tablesmall compact stripe" id="sysuserstable">';
echo '<thead>';
echo '<tr><th>Login</th><th>Name</th></tr>';
echo '</thead>';
echo '<tbody>';

  foreach($rsAdminUsers as $row){
    echo '<tr class="handcursor" ondblclick="javascript:AddUserSysAdmin(\''.$row['UserID'].'\',0);">';
    echo '<td>';
    echo $row['NetLogin'];
    echo '</td>';
    echo '<td>';
    echo $row['FullName'];
    echo '</td>';
    echo '</tr>';
  }
echo '</tbody>';  
echo '</table>'; 
echo '</td>';

echo '<td width="50px">';
echo '</td>';

echo '<td valign="top" width="600px">';

// Tabs here.....

echo'<div id="sysstaffavailable" style="width:100%">';
echo'<ul>';
echo'<li><a href="#sysstaffavailable-1">Allocate Users</a></li>'; 
echo'</ul>';

echo'<div id="sysstaffavailable-1">';
   
  echo '<table class="tablesmall compact stripe" id="allocateusersavailabletable">';
  echo '<thead>';
  echo '<tr><th>Login</th><th>Name</th></tr>';
  echo '</thead>';
  echo '<tbody>';
  foreach($rsAllocateUsers as $row){  
      echo '<tr ondblclick="javascript:AddUserSysAdmin(\''.$row['UserID'].'\',1);" class="handcursor">';
      echo '<td valign="top">';
      echo $row['NetLogin'];
      echo '</td>';
      echo '<td>';
      echo $row['FullName'];
      echo '</td>';
      echo '</tr>';
  }
echo '</tbody>';
echo '</table>';
  
echo '</div>';
echo '</td>';
echo'</td>';
echo'</table>';
} else {
  echo 'Access Denied'; die;
}
?>

<script type="text/javascript">

$(document).ready( function () {
  var gueststable = $("#sysuserstable").DataTable({
    paging: true,
    pageLength: 50,
    lengthChange : true,
    destroy: true,
    scrollY: 400,
    info:     false,
    order: [0],
    autoWidth: false,
  });
  yadcf.init(gueststable, [
    {column_number: 0,
       filter_type: 'text'
    },
    ]);
    if ( $.cookie("sysuserstable") !== null ) {
      scrollPos = $.cookie("sysuserstable");
      $(".dataTables_scrollBody").scrollTop(scrollPos);
    };

  var table2 = $("#allocateusersavailabletable").DataTable({
    paging: true,
    pageLength: 50,
    destroy: true,
    scrollY: 350,
    info:     false,
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

    $(".dataTables_paginate").css('visibility', 'visible');

    if ( $.cookie("allocateusersavailabletable") !== null ) {
      scrollPos = $.cookie("allocateusersavailabletable");
      $("#allocateusersavailabletable_scrollBody").scrollTop(scrollPos);
    };

    
  var tabCookieName = "sysstaffavailable";
  $(function() {
    $( "#sysstaffavailable" ).tabs({
      active : ($.cookie(tabCookieName) || "0"),
      
      activate : function( event, ui ) {
        var newIndex = ui.newTab.parent().children().index(ui.newTab);
        $.cookie(tabCookieName, newIndex, { expires: 1 });
        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
      }
    });
  });     
    
})     

function AddUserSysAdmin(userid, action) {
  $.post("page-includes/admin/sysadminaddremoveuesr.php", {
  userid: userid, 
  action: action
  },
  function(data,status){
    ShowSystemAdminUsers();
   }
  )
}
 





</script>