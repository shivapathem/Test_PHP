<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase();

$intDepartmentID = $_REQUEST['id'];

if (isset($_REQUEST['Update'])) {

  $strStartWeek = $_REQUEST['startweek'];
  if ($strStartWeek != '') {
    $arrWeek = explode ('/', $strStartWeek);
    $intStartWeek =  $arrWeek[1].$arrWeek[0];
  }
  else {
    $intStartWeek = 0;
  
  }
  
  $intDefaultHours = $_REQUEST['defaulthours'];

  if ($intStartWeek == 0) {
    $strQuery = "UPDATE       Departments
                 SET          ReportsStart = NULL, 
                              DefaultShiftLength = $intDefaultHours
                 WHERE        (ID = $intDepartmentID)";
  
  }
  else {
    $strQuery = "UPDATE       Departments
                 SET          ReportsStart = $intStartWeek, 
                              DefaultShiftLength = $intDefaultHours
                 WHERE        (ID = $intDepartmentID)";         
  }
  sqlsrv_query($db, $strQuery);

}
else { 

  $strQuery = "SELECT          FullName, ReportsStart, DefaultShiftLength
               FROM            Departments
               WHERE           (ID = $intDepartmentID)";

  $rsDepartment = sqlsrv_query($db, $strQuery);

  $row = sqlsrv_fetch_array($rsDepartment);
  if (is_null($row['ReportsStart'])) {
    $strStartWeek = '';
  }
  else {
    $strStartWeek = spinweek($row['ReportsStart']);
  }


  echo '<form id="depsadminform">';
  echo '<table class="tablesmalltidy" width="500px">';
  echo '<tr height="30px">';
  echo '<th colspan="2">';
  echo 'Edit Allocations Reports for '.$row['FullName'];
  echo '</th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td valign="top">Reports Start (ww/yyyy)</td>';
  echo '<td><input type="text" name="startweek" size="20" value="'.$strStartWeek.'"></td>';
  echo '</tr>';  

  echo '<tr>';
  echo '<td valign="top">Default Duty Hours</td>';
  echo '<td>';
  echo '<input type="text" name="defaulthours" size="20" value="'.$row['DefaultShiftLength'].'">';
  echo '</td>';
  echo '</tr>'; 
  
  echo '<tr>';
  echo '<td></td>';
  echo '<td><input type="submit" value="Update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';    
                
  echo '</table>';

  echo '<input type="hidden" name="id" value="'.$intDepartmentID.'">';  
  echo '</form>';  

             
?>
<div id="dialog-delete-break" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete this Break?.</p>
</div>
<script type="text/javascript">
  $('document').ready(function(){
    $('#depsadminform').validate({
      rules:{
         "defaulthours":{
          required:true,
        }},

        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/system-admin-reports-edit.php', data:$('#depsadminform').serialize(), success: function(data) {
            $.facebox.close();
            ShowReportsConfig();            
          }});
        }  
    })
  });
</script>            

<?php
}
?>