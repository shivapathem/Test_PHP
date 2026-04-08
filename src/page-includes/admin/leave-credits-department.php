<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
   $intDepartmentID = $_REQUEST['departmentid'];

echo '<div id="LeaveCreditTabs'.$intDepartmentID.'">';
echo '<ul>';
echo '<li><a href="#LeaveTabsDepts'.$intDepartmentID.'-0">Credits</a></li>';  
echo '<li><a href="#LeaveTabsDepts'.$intDepartmentID.'-1">Balances</a></li>'; 
echo '<li><a href="#LeaveTabsDepts'.$intDepartmentID.'-2">Individual Report</a></li>'; 
echo '</ul>';
echo '<div id="LeaveTabsDepts'.$intDepartmentID.'-0">';
echo '</div>';
echo '<div id="LeaveTabsDepts'.$intDepartmentID.'-1">';
echo '</div>';
echo '<div id="LeaveTabsDepts'.$intDepartmentID.'-2">';
echo '</div>';
echo '</div>'; 

   

?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $("#LeaveCreditTabs<?php echo $intDepartmentID?>").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#LeaveCreditTabs<?php echo $intDepartmentID?>").tabs( "option", "active" );
         //if ($.trim($("#LeaveCreditTabs<?php echo $intDepartmentID?>").html()).length == 0) { 
           GetLeaveCreditTab(<?php echo $intDepartmentID?>, SelectedTab);  
         //}
      }
    });
  });
  
  GetLeaveCreditTab(<?php echo $intDepartmentID?>, 0);
  
  
});


function GetLeaveCreditTab(DepartmentID, SelectedTab, CurrentYear) {
  switch (SelectedTab) {
  
  case 0:
    $.post("page-includes/admin/leave-credits-content.php", {
      departmentid: DepartmentID,
      taboption: SelectedTab,
      year: CurrentYear
    },  
    function(data,status){
      $('#LeaveTabsDepts<?php echo $intDepartmentID?>-'+SelectedTab).html(data); 
    })
    break;  
  case 1:
    $.post("page-includes/admin/leave-balances-content.php", {
      departmentid: DepartmentID,
      taboption: SelectedTab,
      year: CurrentYear
    },  
    function(data,status){
      $('#LeaveTabsDepts<?php echo $intDepartmentID?>-'+SelectedTab).html(data); 
    })
    break;  
  }


} 


function ShowAllocateLeaveCredit(logon, leaveyear, departmentid) {
  $.post("page-includes/admin/leave-allocate-credit.php", {
    user: logon,
    year: leaveyear,
    departmentid: departmentid
  },
  function(data,status){{
      $('#LeaveTabsDepts'+departmentid+'-2').html(data);
      $("#LeaveCreditTabs<?php echo $intDepartmentID?>").tabs( "option", "active", 2 ); 
    }}
  );
}


</script>

