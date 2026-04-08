<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrUserDepartments = GetAdminDepartmentsByLogin($strUser);  
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>Staff Support Administration.<br><br>';
echo '</div>';

if (empty($arrUserDepartments)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Departments defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
}
else {
  $intFirstKey = array_keys($arrUserDepartments)[0];

  echo '<div id="accordion">'; 
  foreach ($arrUserDepartments as $intDepartmentID => $strDescription) {
    echo '<h3 id="'.$intDepartmentID.'">';
    echo $strDescription;       
    echo '</h3>';
    echo '<div id="accordioncontents'.$intDepartmentID.'">';
    echo '</div>';     
  }  
  echo '</div>';

?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $( "#accordion" ).accordion({
      heightStyle: "content",
      collapsible: true,
      activate : function( event, ui ) {
        var department = ($('.ui-accordion-header-active').attr('id'));
        GetContent(department);       
      }
    }
  );
  });
  GetContent (<?php echo $intFirstKey?>, 0)  
});




function GetContent (department) {
  if (typeof department != 'undefined') {
    $.post("page-includes/support/support-staff.php", {
      department: department
    },  
    function(data,status){
      $('#accordioncontents'+department+'').html(data); 
    })
  } 
}
</script>

<?php
}
