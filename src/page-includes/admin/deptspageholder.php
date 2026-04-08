<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
UpdateArchiveLogin();
$defaultTeamId = GetDefaultSchedulingTeamIdByLogin($sessUserId);

echo'<div id="admindeptstabs"  style="min-width:1700px">';
echo'<ul>';
echo'<li><a href="#admindeptstabs-1">Departments</a></li>';  
echo'<li><a href="#admindeptstabs-2">Scheduled Staff</a></li>';
echo'<li><a href="#admindeptstabs-3">Additional Staff</a></li>';
echo'<li><a href="#admindeptstabs-4">Guest Users</a></li>';
echo'<li><a href="#admindeptstabs-5">Filters, Autoscreens & Groups</a></li>';
echo'<li><a href="#admindeptstabs-6">Underlying Rota Pattern Hours</a></li>';
echo'<li><a href="#admindeptstabs-7">Christmas Points</a></li>';
echo'<li><a href="#admindeptstabs-8">Extra Christmas Points</a></li>';
echo'</ul>';

echo'<div id="admindeptstabs-1"></div>';
echo'<div id="admindeptstabs-2">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
echo '<br>Please highlight a Department and try again!<br><br>';
echo '</div></div>'; 
echo'<div id="admindeptstabs-3">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
echo '<br>Please highlight a Department and try again!<br><br>';
echo '</div></div>'; 
echo'<div id="admindeptstabs-4">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
echo '<br>Please highlight a Department and try again!<br><br>';
echo '</div></div>';
echo'<div id="admindeptstabs-5">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
echo '<br>Please highlight a Department and try again!<br><br>';
echo '</div></div>';
echo'<div id="admindeptstabs-6">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
echo '<br>Please highlight a Department and try again!<br><br>';
echo '</div></div>';
echo'<div id="admindeptstabs-7">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
echo '<br>Please highlight a Department and try again!<br><br>';
echo '</div></div>';
echo'<div id="admindeptstabs-8">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
echo '<br>Please highlight a Department and try again!<br><br>';
echo '</div></div></div>';

?>
<div id="dialog-no-edit" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You cannot change your own information!</p>
</div>
<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $( "#admindeptstabs" ).tabs({ 
      activate : function( event, ui ) {
        var active = $( "#admindeptstabs" ).tabs( "option", "active" );
        
        switch (active) {
        case 1:
          $("#departmentslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowStaffThisDepartment(id);
          });   
          break;
          
        case 2:
          $("#departmentslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowAdditionalStaffThisDepartment(id);
          });   
          break;          
        case 3:
          $("#departmentslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowGuestsThisDepartment(id);
          });   
          break;
        case 4:
          $("#departmentslist").dataTable().$("tr.selected").each(function(){
            var id = '<?php echo $defaultTeamId;?>';
            ShowFilterHolder(id);
          });   
          break;          
        case 5:
          $("#departmentslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowPatternHours(id);
          });   
          break; 
        case 6:
          $("#departmentslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowXmasPointsAdmin(id);
          });   
          break; 
        case 7:
          $("#departmentslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowXmasPointsExtra(id);
          });   
          break;
        }

      }
    });
  });

ShowDepartments();
          
});
function ShowDepartments () {
  $.post("page-includes/admin/deptslist.php", {
  },
  function(data,status){
    $('#admindeptstabs-1').html(data);
   }
  )
}

function ShowStaffThisDepartment (id) {
  $.post("page-includes/admin/deptsscheduledstaff.php", {
  id: id
  },
  function(data,status){
    $('#admindeptstabs-2').html(data);
   }
  )
}

function ShowAdditionalStaffThisDepartment (id) {
  $.post("page-includes/admin/deptsadditionalstaff.php", {
  id: id
  },
  function(data,status){
    $('#admindeptstabs-3').html(data);
   }
  )
}

function ShowGuestsThisDepartment (id) {
  $.post("page-includes/admin/deptsguestusers.php", {
  id: id
  },
  function(data,status){
    $('#admindeptstabs-4').html(data);
   }
  )
}

function ShowFilterHolder (id) {
  $.post("page-includes/admin/deptsfilterholder.php", {
  id: id
  },
  function(data,status){
    $('#admindeptstabs-5').html(data);
   }
  )
}

function ShowPatternHours (id) {
  $.post("page-includes/admin/depts-pattern-hours.php", {
    departmentid: id
  },
  function(data,status){
    $('#admindeptstabs-6').html(data);
   }
  )
}
function ShowXmasPointsAdmin (id) {
  $.post("page-includes/admin/xmas-points-setup.php", {
    departmentid: id
  },
  function(data,status){
    $('#admindeptstabs-7').html(data);
   }
  )
}

function ShowXmasPointsExtra (id) {
  $.post("page-includes/admin/depts-xmas-points.php", {
    departmentid: id
  },
  function(data,status){
    $('#admindeptstabs-8').html(data);
   }
  )
}


$( window ).resize(function() {
    DoResizeGrid();
})
function DoResizeGrid() {
  var windowheight = $(window ).height() - 250; 
  $('.dataTables_scrollBody').height((windowheight));
}







</script>