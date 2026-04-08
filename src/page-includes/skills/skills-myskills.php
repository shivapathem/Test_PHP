<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';
include_once '../../function-includes/genericfunctions.php';
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$intDepartmentID = 4;
$strStaffNumber = GetStaffNumberFromLogin($strLogin);

$arrStaffDepartments = GetStaffScheduledDepartments($strLogin);
//echo '<pre>';
//print_r($arrStaffDepartments);




echo '<div class="tableheadersmall" style="width: 100%">';
echo '<br><b>Below are your skills in the department(s) you are scheduled in.</b>
      <br><br>';
echo '</div>';
echo '<br>';
foreach ($arrStaffDepartments as $intDepartmentID => $strDepartmentName) {
  echo '<div>';
  echo '<div class="tableheadersmall medtextboldcentre"><br>'.$strDepartmentName.'<br><br></div>';
  echo '<div id="staffskills-'.$intDepartmentID.'">';
  echo '</div>';
  echo '</div>';
}
echo '<br>';


?>

<script type="text/javascript">
$(document).ready(function(){
<?php
  foreach ($arrStaffDepartments as $intDepartmentID => $strDepartmentName) {
    echo 'ShowStaffSkills('.$intDepartmentID.');'; 
  }  
?>

})

function ShowStaffSkills (department) {
  $.post("page-includes/skills/skills-staff-progs-cando-fillpage.php", {
    staffnumber: '<?php echo $strStaffNumber?>',
    department: department
  },
    function(data,status){
      $('#staffskills-'+department).html(data);
    }
  )
}
</script>


<?php


die;


$db = OpenDatabase();

$query = "SELECT        skills_programmes.programmename, skills_programmes.ID AS ProgID, Departments.FullName  as DepartmentFullName
          FROM          Staff 
          INNER JOIN    skills_programmes_staff_link ON Staff.ID = skills_programmes_staff_link.staff_id 
          INNER JOIN    skills_programmes ON skills_programmes_staff_link.programmes_id = skills_programmes.ID 
          INNER JOIN    Departments ON skills_programmes.DepartmentID = Departments.ID
          WHERE         (Staff.Login = N'$strLogin') AND (Staff.DepartmentID <> 255)
          ORDER BY      skills_programmes.programmename";
        
          
          
$myskills = sqlsrv_query($db, $query);

while ($row = sqlsrv_fetch_array($myskills)) {
  $arrmyskills[$row['ProgID']] = $row['programmename'].' ('.$row['DepartmentFullName'].')';
}


$query = "SELECT        skills_programmes.programmename, skills_programmes.ID AS ProgID, Departments.FullName
          FROM          Departments 
          INNER JOIN    skills_programmes ON Departments.ID = skills_programmes.DepartmentID 
          INNER JOIN    Staff ON skills_programmes.DepartmentID = Staff.DepartmentID
          WHERE         (Staff.Login = N'$strLogin') AND (Staff.DepartmentID <> 255)
          ORDER BY       skills_programmes.programmename";

$allskills = sqlsrv_query($db, $query);




echo '<div class="tableheadersmall" style="width: 100%">';
echo '<br><b>Below are 2 lists:</b><br>Programmes you can currently be scheduled to work on. This list includes programmes from any Department in which you have skills.<br>
      Programmes you currently can\'t be scheduled to work on. This list shows only programmes in your Scheduled Departments.
      <br><br>';
echo '</div>';
echo '<br>';

echo '<table class="tablesmall">';
echo '<tr>';
echo '<td class="tableheadersmall" width="350px">';
echo 'Programmes you can currently be scheduled to work on';
echo '</td>';
echo '<td class="tableheadersmall" width="250px">';
echo 'Programmes you currently can\'t be scheduled to work on';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top">';
if (isset($arrmyskills)) {
  echo '<table width="100%">';
  foreach ($arrmyskills as $key => $thisskill) {
    echo '<tr>';
    echo '<td>';
    echo $thisskill;
    echo '</td>';
    echo '</tr>';
  }
  echo '</table>';
}
echo '</td>';
echo '<td valign="top">';
echo '<table width="100%">';
while ($row = sqlsrv_fetch_array($allskills)) {
  if(!isset($arrmyskills[$row['id']]) && $row['base'] == $base) {
    echo '<tr>';
    echo '<td>';
    echo $row['programmename'];
    echo '</td>';
    echo '</tr>';
  }
}
echo '</table>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '<br><br><br><br><br>';

?>

<script type="text/javascript">
  $(document).ready(function(){
  $("table.tablesmall tr:odd").addClass("odd");
  $("table.tablesmall tr:even").addClass("even");

    $("#flip").click(function(){
      $("#panel").slideToggle("fast");
      document.getElementById('username').focus();
    });
  });

$(function() {
  function log( message ) {
    $( "<div>" ).text( message ).prependTo( "#log" );
    $( "#log" ).scrollTop( 0 );
  }
  $( "#username" ).autocomplete({
    source: "page-includes/ajax-calls/returnstaff.php",
    minLength: 2,
    select: function( event, ui ) {
      $.post("page-includes/ajax-calls/addusertobase.php", {
        id:  ui.item.id
      },
      function(data,status){
        UserEdit (data);
        //location.reload();
      });
    }
  });
});
</script>