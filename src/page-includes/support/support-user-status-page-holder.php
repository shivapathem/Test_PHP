<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  
$arrUserDepartments = GetSupportDepartmentsByLogin($strUser);  
//echo '<pre>';
//print_r($arrUserDepartments);
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>Staff Attachments, FTCs and FWAs.<br><br>';
echo '</div>';

if (!isset($arrUserDepartments)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Scheduling Groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
}
else {
  echo '<div id="accordion">'; 
  foreach ($arrUserDepartments as $intDepartmentID => $arrUserDepartment) {
    echo '<h3 id="'.$intDepartmentID.'">';
    echo $arrUserDepartment['DepartmentName']; 
    if($arrUserDepartment['isAdmin'] == 0) {
      echo '&nbsp;&circledR;';    
    }
    echo '</h3>';
    echo '<div id="statusaccordioncontents'.$intDepartmentID.'">';
    echo '</div>';     
  }  
  echo '</div>';
}

$intFirstKey = array_keys($arrUserDepartments)[0];

?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $( "#accordion" ).accordion({
      heightStyle: "content",
      collapsible: true,
      activate : function( event, ui ) {
        var department = ($('.ui-accordion-header-active').attr('id'));
        GetStatusContent(department);         
      }
    });
  });
  GetStatusContent (<?php echo $intFirstKey?>, 0)
  
});

function GetStatusContent (department) {
  if (typeof department != 'undefined') {
  $.post("page-includes/support/support-user-status.php", {
    department: department
  },  
  function(data,status){
      $('#statusaccordioncontents'+department+'').html(data); 
  })
  }
}

function GetSupportTabContent (departmentid, selectedtab) {
  if (selectedtab >= 0 && selectedtab <= 2) { 
    $.post("page-includes/support/support-user-movements.php", {
      departmentid: departmentid,
      selectedtab: selectedtab
    },
    function(data,status){
      $('#tabs-' + selectedtab + '-' + departmentid).html(data);
    }
    )
  }
  if (selectedtab >= 3 && selectedtab <= 4) { 
    $.post("page-includes/support/support-needing-attention.php", {
      departmentid: departmentid,
      selectedtab: selectedtab
    },
    function(data,status){
      $('#tabs-' + selectedtab + '-' + departmentid).html(data);
    }
    )
  }  
  
  
}


</script>


