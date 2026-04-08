<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$arrReportDepartments = GetReportsDepartmentsByLogin($strUser);

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h2>Leave Reports</h2><br><br>';
echo '</div>';

if (!isset($arrReportDepartments)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Leave groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}

  echo '<div id="accordion">'; 
  foreach ($arrReportDepartments as $intDepartmentID => $strDepartment) {
    echo '<h3 id="'.$intDepartmentID.'">';
    echo $strDepartment;       
    echo '</h3>';
    echo '<div>';
    echo '  <div class="leaveReporttabs-'.$intDepartmentID.'">';
    echo '    <ul>';
    echo '      <li><a href="#leaveReporttabs-0-'.$intDepartmentID.'">Leave To Date</a></li>';
    echo '      <li><a href="#leaveReporttabs-1-'.$intDepartmentID.'">Percentage Leave Utilisation</a></li>';
    echo '      <li><a href="#leaveReporttabs-2-'.$intDepartmentID.'">Leave Requests</a></li>';
    echo '      <li><a href="#leaveReporttabs-3-'.$intDepartmentID.'">Leave Requests (By Leave Year)</a></li>';
    echo '      <li><a href="#leaveReporttabs-4-'.$intDepartmentID.'">Allocate Leave Balance</a></li>';
    echo '    </ul>';
    echo '    <div id="leaveReporttabs-0-'.$intDepartmentID.'">';
    echo '    </div>';
    echo '    <div id="leaveReporttabs-1-'.$intDepartmentID.'">';
    echo '    </div>';
    echo '    <div id="leaveReporttabs-2-'.$intDepartmentID.'">';
    echo '    </div>';
    echo '    <div id="leaveReporttabs-3-'.$intDepartmentID.'">';
    echo '    </div>';        
    echo '    <div id="leaveReporttabs-4-'.$intDepartmentID.'">';
    echo '    </div>';    
    echo '  </div>';
    echo '</div>';     
  }  
  echo '</div>';


$intFirstKey = array_keys($arrReportDepartments)[0];

?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $( "#accordion" ).accordion({
      heightStyle: "content",
      collapsible: true,
      activate : function( event, ui ) {
        var groupid = ($('.ui-accordion-header-active').attr('id'));
        var $tabs = $('.leaveReporttabs-'+groupid).tabs();
        var SelectedTab = $tabs.tabs('option', 'active'); 
        GetTabContent(groupid, SelectedTab);         
      }
    }
  );
});

<?php
  foreach ($arrReportDepartments as $intDepartmentID => $strDepartment) {
?>
  $(function() {
    $(".leaveReporttabs-<?php echo $intDepartmentID?>").tabs({ 
      disabled: [4],
      activate : function( event, ui ) {
        var SelectedTab = $(".leaveReporttabs-<?php echo $intDepartmentID?>").tabs( "option", "active" );
          GetTabContent(<?php echo $intDepartmentID?>, SelectedTab);
      }      
    });
  });
  
  
<?php
}
?>  
GetTabContent (<?php echo $intFirstKey?>, 0)
  
});


function GetTabContent (group, SelectedTab) {
alert(SelectedTab);
  switch (SelectedTab) {
  
  
  }
}






</script>


