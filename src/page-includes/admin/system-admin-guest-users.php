<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase();


$query = "SELECT            Staff_Guests.ID, Staff_Guests.Surname, Staff_Guests.Forename, Staff_Guests.Login, Staff_Web_Config_Departments_Link.DepartmentID, 
                            Departments_1.FullName AS DepartmentFullName,  ISNULL(Staff.DepartmentID, 255) AS AllocateDepartmentID, Departments.FullName AS AllocateDepartmentFullName
          FROM              Departments AS Departments_1 
          INNER JOIN        Staff_Web_Config_Departments_Link ON Departments_1.ID = Staff_Web_Config_Departments_Link.DepartmentID 
          RIGHT OUTER JOIN  Departments 
          INNER JOIN        Staff ON Departments.ID = Staff.DepartmentID 
          RIGHT OUTER JOIN  Staff_Guests ON Staff.Login = Staff_Guests.Login ON Staff_Web_Config_Departments_Link.Login = Staff_Guests.Login
          ORDER BY          Staff_Guests.Surname, Staff_Guests.Forename";
//echo $query;
$users = sqlsrv_query($db, $query);
$arrStaff = [];
while ($row = sqlsrv_fetch_array($users)) {
  $arrStaff[$row['Login']]['Name'] = $row['Surname'].', '.$row['Forename'];
  if (!is_null($row['DepartmentID'])) {
    $arrStaff[$row['Login']]['Departments'][$row['DepartmentID']] = $row['DepartmentFullName'];
  }
  if (!is_null($row['AllocateDepartmentID'])) {
    $arrStaff[$row['Login']]['AllocateDepartments'][$row['AllocateDepartmentID']] = $row['AllocateDepartmentFullName'];
  }
  if ($row['AllocateDepartmentID'] != 255) {
    $arrStaff[$row['Login']]['HasAllocateDepartments'] = 1;
  }  
}


  echo '<div style="position: relative; width:100%" class="tableheadersmall medtextboldcentre">';
  echo '<br>Guest Users<br><br>';
  echo '<div class="DutyCellBottomLeft" onclick="javascript:NewGuestUser()";>';
  echo '<table>';
  echo '<tr>';
  echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
  echo '<td>';
  echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';  

  echo '</div>';
  echo '<br>';
 
  echo '<table class="tablesmall compact stripe" id="userstableguest">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Name';
  echo '</th>';
  echo '<th>';
  echo 'Logon';
  echo '</th>';
  echo '<th>';
  echo 'Departments Assigned as Guest';
  echo '</th>';
  echo '<th>';
  echo 'Allocate Scheduled Departments';
  echo '</th>';
  echo '<th>';
  echo 'Allocate User';
  echo '</th>';
  echo '<th>';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';


  echo '<tbody>';
  if(isset($arrStaff))
  {
  foreach ($arrStaff as $strLogin => $arrStaffDetails) {
    echo '<tr class="handcursor">';
    echo '<td>';
    echo $arrStaffDetails['Name'];
    echo '</td>';
    echo '<td>';
    echo $strLogin;
    echo '</td>';

    // The bases this person wants to adminisyet leave for
    echo '<td>';
    if (isset($arrStaffDetails['Departments'])) {
      foreach ($arrStaffDetails['Departments'] as $intDepartmentID => $strDepartmentName) {
        echo $strDepartmentName.'<br>';
      }
    }
    echo '</td>';
    // The bases this person wants to adminisyet leave for
    echo '<td>';
    if (isset($arrStaffDetails['AllocateDepartments'])) {
      foreach ($arrStaffDetails['AllocateDepartments'] as $intDepartmentID => $strDepartmentName) {
        echo $strDepartmentName.'<br>';
      }
    }
    echo '</td>';
    echo '<td align="center">';
    if (isset($arrStaffDetails['HasAllocateDepartments'])) {
      echo '<img  onclick="javascript:ShowUserInfo(\''.$strLogin.'\');" border="0" src="../images/info.png" width="12px" height="12px">'; 
    }
    echo '</td>';    
    echo '<td align="center">';
    if (isset($arrStaffDetails['HasAllocateDepartments'])) {
      echo '<img  class="handcursor" onclick="javascript:DeletetGuestUser(\''.$strLogin.'\', 0)"; border="0" src="../images/delete.png" width="12px" height="12px">'; 
    }
    else {
      echo '<img class="handcursor" onclick="javascript:DeletetGuestUser(\''.$strLogin.'\', 1)"; border="0" src="../images/delete.png" width="12px" height="12px">';         
    }
    
    echo '</td>';     
    
    echo '</tr>';
  }
  }
  echo '</tbody>'; 
  echo '</table>';

  echo '<br><br><br><br>'

?>
<div id="dialog-guest-delete" title="Question!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this User?</span></p>
</div>


<script type="text/javascript">
$(document).ready(function(){
        var guestTable = $("#userstableguest").DataTable({
          paging: false,
          scrollY: 400,
          info:     false,
          stateSave: true,
          deferRender: true,
          "initComplete": function( settings, json ) {
            DoResize();
          }
        });

      yadcf.init(guestTable, [
        {column_number: 0,
          filter_type: 'text'
        },
        {column_number: 1,
          filter_type: 'text'
        },        
      ]);
      if ( $.cookie("userstableguest") !== null ) {
        scrollPos = $.cookie("userstableguest");
        $(".dataTables_scrollBody").scrollTop(scrollPos);
      };
})
$('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $('#userstableguest').closest('.dataTables_scrollBody').scrollTop();
  $.cookie("userstableguest", currpos);
});

$(window).resize(function() {
  DoResize();
})


function DoResize() {
  var windowheight = $(window ).height() - 250;  
  $('.dataTables_scrollBody').height((windowheight));  
}


function NewGuestUser() {
  $.post("page-includes/admin/addnewguestuser.php", {
  },
  function(data,status){
	  $.facebox(data);
  })
}

function GuestUserGroups(id) {
  $.post("page-includes/ajax-calls/admin-guestgroups.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
}

function DeletetGuestUser(login, action) {
  $( "#dialog-guest-delete" ).dialog({
    width:400,
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/admin/system-admin-guest-user-delete.php", {
          login: login,
          action: action
        })
       ShowGuestUsers();  
      },
      "No": function() {
        $( this ).dialog( "close" );
      },
    }}
  );
}



</script>