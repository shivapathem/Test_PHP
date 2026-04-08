<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$intDefaultDepartment = GetDefaultTeamByLogin($strUser);

$arrDepartments = GetStaffOtionsByDepartment($strUser);
$intFirstDepartment = 0;
echo '<div id="ContactsTabsDepts">';
echo '<ul>';
 foreach ($arrDepartments as $intDepartmentID => $arrDepartment) {
   if ($arrDepartment['isShiftLeader'] == 1 || $arrDepartment['isScheduler'] == 1 || $arrDepartment['isManager'] == 1 || $arrDepartment['isAdmin'] == 1) {
     if ($intFirstDepartment == 0) {
       $intFirstDepartment = $intDepartmentID;
     }
   
     echo '<li><a href="#ContactsTabs'.$intDepartmentID.'">'.$arrDepartment['DepartmentName'].'</a></li>';   
   }
 }
echo '</ul>';
foreach ($arrDepartments as $intDepartmentID => $arrDepartment) {
  if ($arrDepartment['isShiftLeader'] == 1 || $arrDepartment['isScheduler'] == 1 || $arrDepartment['isManager'] == 1 || $arrDepartment['isAdmin'] == 1) { 
    echo '<div id="ContactsTabs'.$intDepartmentID.'">';
    echo '</div>'; 
  }
}
echo '</div>'; 


?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $("#ContactsTabsDepts").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#ContactsTabsDepts").tabs( "option", "active" ); 
        GetTabContent(SelectedTab);     
      }
    });
  });
  ShowDepContacts(<?php echo $intFirstDepartment?>);



function GetTabContent(SelectedTab) {
switch (SelectedTab) {
<?php
$intCount = 0;
foreach ($arrDepartments as $intDepartmentID => $arrDepartment) {
  if ($arrDepartment['isShiftLeader'] == 1 || $arrDepartment['isScheduler'] == 1 || $arrDepartment['isManager'] == 1 || $arrDepartment['isAdmin'] == 1) {
echo "case ".$intCount.":\n";
echo "ShowDepContacts(".$intDepartmentID.");\n";
echo "break;\n";
$intCount++;
  }
}
?>

}
}

function ShowDepContacts(DepartmentID) {
  $.post("page-includes/admin/department-contacts.php", {
    departmentid: DepartmentID
  },  
  function(data,status){
    $('#ContactsTabs'+DepartmentID).html(data); 
  })
}
  
});



</script>
