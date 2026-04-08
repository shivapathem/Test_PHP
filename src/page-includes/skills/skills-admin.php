<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/skillsfunctions.php';
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  
$arrMyDepartments = GetSkillsDepartments($strLogin);  
echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
echo '</div>';
echo '<br>';


if (isset($arrMyDepartments)) {
  echo '<div id="accordion-skills">'; 
  // Loop through the available Groups
  foreach ($arrMyDepartments as $intDepartmentID => $strDepartmentName) {
    echo '<h3 id="'.$intDepartmentID.'">';
    echo $strDepartmentName;       
    echo '</h3>';
    echo '<div>';
    echo '  <div class="skillstabs-'.$intDepartmentID.'">';
    echo '    <ul>';
    echo '      <li><a href="#skillstabs-0-'.$intDepartmentID.'">Skills/Staff</a></li>';
    echo '      <li><a href="#skillstabs-1-'.$intDepartmentID.'">Duties/Skills</a></li>';
    echo '      <li><a href="#skillstabs-2-'.$intDepartmentID.'">Duties/Staff</a></li>';
    echo '      <li><a href="#skillstabs-3-'.$intDepartmentID.'">Staff/Skills/Duties</a></li>'; 
    echo '    </ul>';
    echo '    <div id="skillstabs-0-'.$intDepartmentID.'">';
    echo '    </div>';
    echo '    <div id="skillstabs-1-'.$intDepartmentID.'">';
    echo '    </div>';
    echo '    <div id="skillstabs-2-'.$intDepartmentID.'">';
    echo '    </div>';
    echo '    <div id="skillstabs-3-'.$intDepartmentID.'">';
    echo '    </div>';          
    echo '</div>';    
    echo '</div>';
  }  
  echo '</div>';
}
$intFirstKey = array_keys($arrMyDepartments)[0];
?>

<div id="dialog-programme-delete" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete this Programme?<br>It will remove all staff associated with it!</span></p>
</div>




<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $( "#accordion-skills" ).accordion({
      heightStyle: "content",
      collapsible: true,
      activate : function( event, ui ) {
        var departmentid = ($('.ui-accordion-header-active').attr('id'));
        var $tabs = $('.skillstabs-'+departmentid).tabs();
        var SelectedTab = $tabs.tabs('option', 'active'); 
        GetSkillsTabContent(departmentid, SelectedTab);
      }
    });
  });
  
<?php
  foreach ($arrMyDepartments as $intDepartmentID => $strDepartmentName) {
?>
  $(function() {
    $(".skillstabs-<?php echo $intDepartmentID?>").tabs({ 
      heightStyle: "content",
      activate : function( event, ui ) {
        var SelectedTab = $(".skillstabs-<?php echo $intDepartmentID?>").tabs( "option", "active" );
          GetSkillsTabContent(<?php echo $intDepartmentID?>, SelectedTab);
      }      
    });
  });
  
  
<?php
}
?>   
GetSkillsTabContent (<?php echo $intFirstKey?>, 0)

})
function GetSkillsTabContent (department, SelectedTab) {
  if (SelectedTab == 0) { 
    $.post("page-includes/skills/skills-progs-staff.php", {
      department: department
    },  
    function(data,status){
      $('#skillstabs-'+SelectedTab+'-'+department).html(data); 
    })
  }
  if (SelectedTab == 1) { 
    $.post("page-includes/skills/skills-duties-programmes.php", {
      department: department
    },  
    function(data,status){
      $('#skillstabs-'+SelectedTab+'-'+department).html(data); 
    })
  } 
  if (SelectedTab == 2) { 
    $.post("page-includes/skills/skills-duties-staff-cando.php", {
      department: department
    },  
    function(data,status){
      $('#skillstabs-'+SelectedTab+'-'+department).html(data); 
    })
  } 
  if (SelectedTab == 3) { 
    $.post("page-includes/skills/skills-staff-programmes-duties.php", {
      department: department
    },  
    function(data,status){
      $('#skillstabs-'+SelectedTab+'-'+department).html(data); 
    })
  }   
  
  
  
  
  
  
     
}
function ToggleTimeZone(department, SelectedTab) {
    $.post("page-includes/skills/skills-toggletimezone.php", {
    },  
    function(data,status){
      GetSkillsTabContent(department, SelectedTab); 
    })
  
  
  
}

</script>


