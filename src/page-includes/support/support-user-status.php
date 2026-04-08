<?php
session_start();

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intDepartmentID = $_REQUEST['department'];

// 3 Tabs to hold things.....
    echo '<div>';
    echo '  <div class="tabs-'.$intDepartmentID.'">';
    echo '    <ul>';
    echo '      <li><a href="#tabs-0-'.$intDepartmentID.'">Attachments</a></li>';
    echo '      <li><a href="#tabs-1-'.$intDepartmentID.'">FTCs</a></li>';
    echo '      <li><a href="#tabs-2-'.$intDepartmentID.'">FWAs</a></li>';    
    echo '      <li><a href="#tabs-3-'.$intDepartmentID.'">Needing Attention</a></li>';
    echo '      <li><a href="#tabs-4-'.$intDepartmentID.'">Completed</a></li>';
    echo '    </ul>';
    echo '    <div id="tabs-0-'.$intDepartmentID.'">';
    echo '    </div>';
    echo '    <div id="tabs-1-'.$intDepartmentID.'">';
    echo '    </div>';
    echo '    <div id="tabs-2-'.$intDepartmentID.'">';   
    echo '    </div>';
    echo '    <div id="tabs-3-'.$intDepartmentID.'">';   
    echo '    </div>';
    echo '    <div id="tabs-4-'.$intDepartmentID.'">';   
    echo '    </div>';    
    echo '    <div id="tabs-5-'.$intDepartmentID.'">';   
    echo '    </div>';



    echo '</div>'; 
?>
<script type="text/javascript">

$(document).ready(function(){
  $(function() {
    $(".tabs-<?php echo $intDepartmentID?>").tabs({ 
      activate : function( event, ui ) {
        var SelectedTab = $(".tabs-<?php echo $intDepartmentID?>").tabs( "option", "active" );
          GetSupportTabContent(<?php echo $intDepartmentID?>, SelectedTab);
      }      
    });
  });
  GetSupportTabContent (<?php echo $intDepartmentID?>, 0)
})





</script>

