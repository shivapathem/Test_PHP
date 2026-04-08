<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase();
$intID = $_REQUEST['id'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

if (isset($_REQUEST['Update'])) {
  $strAbbreviated = $_REQUEST['abbreviated'];
  $strDescription = $_REQUEST['description'];
  $intDepID = $_REQUEST['departmentid'];
  $intBaseCodeID = $_REQUEST['basecodeid'];
  
  if ($intBaseCodeID == 0) {
    $intBaseCodeID = 'null';
  
  }
  
  
  
  $strChargeCode = $_REQUEST['chargecode'];  
  $strManagerLogon = $_REQUEST['managerlogon'];   
  $strSchedulerLogon = $_REQUEST['schedulerlogon'];  
  $strSmartBookUserLogon = $_REQUEST['smartbookuserlogon']; 
  $strManager = $_REQUEST['manager'];   
  $strScheduler = $_REQUEST['scheduler'];  
  $strSmartBookUser = $_REQUEST['smartbookuser'];   
  $strRole = $_REQUEST['role'];  
  $intHourlyRate = $_REQUEST['hourlyrate'];  

  if ($intID == 0) {
    $strQuery = "INSERT INTO SortCodeMapping
                            (Abbreviated, Description, DepartmentID, BaseCodesID, ChargeCode, ManagerLogon, Manager, SchedulerLogon, Scheduler, SmartBookUserLogon, SmartBookUser, Role, HourlyRate)
                 VALUES     ( '$strAbbreviated', 
                              '$strDescription', 
                              $intDepID,
                              $intBaseCodeID,
                              '$strChargeCode',
                              '$strManagerLogon',
                              '$strManager',
                              '$strSchedulerLogon',
                              '$strScheduler',
                              '$strSmartBookUserLogon',
                              '$strSmartBookUser',                             
                              '$strRole',
                              $intHourlyRate
                              
                            )";
  }
  else {
    $strQuery = "UPDATE       SortCodeMapping
    
                 SET          Abbreviated = '$strAbbreviated',
                              Description = '$strDescription',
                              DepartmentID = $intDepID, 
                              BaseCodesID = $intBaseCodeID, 
                              ChargeCode = '$strChargeCode', 
                              ManagerLogon = '$strManagerLogon',
                              Manager = '$strManager', 
                              SchedulerLogon = '$strSchedulerLogon',
                              Scheduler = '$strScheduler',
                              SmartBookUser = '$strSmartBookUser',
                              SmartBookUserLogon = '$strSmartBookUserLogon',
                              Role = '$strRole',
                              HourlyRate = $intHourlyRate
                              
                              
                 WHERE        (ID = $intID)";

  }
  echo $strQuery;
  sqlsrv_query($db, $strQuery);
  die;
}

$arrAdminDepts = GetAdminDepts($strUser, 1);
$arrBaseCodes = GetBaseCodes();

  if ($intID == 0) {
    $strAbbreviated = '';
    $strDescription = ''; 
    $intDepartmentID = 0;
    $intBaseCodesID = 0;      
    $strChargeCode = '';
    $strRole = '';
    $intHourlyRate = '';
    $strManagerLogon = '';  
    $strManager = '';            
    $strSchedulerLogon = '';      
    $strScheduler = '';
    $strSmartBookUserLogon = '';
    $strSmartBookUser = '';   
  }
  else {    
    $strQuery = "SELECT    Abbreviated, Description, DepartmentID, BaseCodesID, ChargeCode, Role, HourlyRate, ManagerLogon, Manager, SchedulerLogon, Scheduler, 
                           SmartBookUserLogon, SmartBookUser
                 FROM      dbo.SortCodeMapping
                 WHERE     (ID = $intID)";  
    
      $rsSortCode = sqlsrv_query($db, $strQuery);
      $row = sqlsrv_fetch_array($rsSortCode);
      $strAbbreviated = $row['Abbreviated'];
      $strDescription = $row['Description']; 
      $intDepartmentID = $row['DepartmentID'];
      $intBaseCodesID = $row['BaseCodesID'];      
      $strChargeCode = $row['ChargeCode'];
      $strRole = $row['Role'];
      $intHourlyRate = $row['HourlyRate'];      
      $strManagerLogon = $row['ManagerLogon'];    
      $strManager = $row['Manager'];            
      $strSchedulerLogon = $row['SchedulerLogon'];
      $strScheduler = $row['Scheduler'];
      $strSmartBookUser = $row['SmartBookUser']; 
      $strSmartBookUserLogon = $row['SmartBookUserLogon'];        
  }
  //echo $strQuery;
  
  
  
  echo '<form id="neweditsortcodetypeform">';
  echo '<table class="tablesmalltidy" width="500px">';
  echo '<tr height="30px">';
  echo '<th colspan="2">';
  echo 'Edit Team / Duty Mapping';
  echo '</th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td valign="top">Abbreviation (Match Duty)</td>';
  echo '<td><input type="text" name="abbreviated" size="20" value="'.$strAbbreviated.'"></td>';
  echo '</tr>';  

  echo '<tr>';
  echo '<td valign="top">Team Description</td>';
  echo '<td><input type="text" name="description" size="20" value="'.$strDescription.'"></td>';
  echo '</tr>';  

  echo '<tr>';
  echo '<td valign="top">Associated Department</td>';
  echo '<td>';
  echo '<select class="chosen-select" size="1" name="departmentid">';
  echo '<option value="0">-</option>'; 
  foreach ($arrAdminDepts as $intDepID => $arrDep) {
    if ($intDepID == $intDepartmentID) {
      echo '<option selected value="'.$intDepID.'">'.$arrDep['Description'].'</option>';     
    }
    else {
      echo '<option value="'.$intDepID.'">'.$arrDep['Description'].'</option>'; 
    }
  }
  echo '</select>';   
  echo '</td>';
  echo '</tr>';  

  echo '<tr>';
  echo '<td valign="top">Associated Base Code</td>';
  echo '<td>';
  echo '<select class="chosen-select" size="1" name="basecodeid">';
  echo '<option value="0">-</option>'; 
  foreach ($arrBaseCodes as $intBaseID => $strBaseDesc) {
    if ($intBaseID == $intBaseCodesID) {
      echo '<option selected value="'.$intBaseID.'">'.$strBaseDesc.'</option>';     
    }
    else {
      echo '<option value="'.$intBaseID.'">'.$strBaseDesc.'</option>'; 
    }
  }
  echo '</select>';   
  echo '</td>';
  echo '</tr>'; 

  echo '<tr>';
  echo '<td valign="top">Role</td>';
  echo '<td><input type="text" name="role" size="20" value="'.$strRole.'"></td>';
  echo '</tr>'; 
  
  echo '<tr>';
  echo '<td valign="top">Charge Code</td>';
  echo '<td><input type="text" name="chargecode" size="20" value="'.$strChargeCode.'"></td>';
  echo '</tr>'; 

  echo '<tr>';
  echo '<td valign="top">Hourly Rate</td>';
  echo '<td>&pound;<input type="text" name="hourlyrate" size="20" value="'.$intHourlyRate.'"></td>';
  echo '</tr>'; 
  
  echo '<tr>';
  echo '<td valign="top">Manager</td>';
  echo '<td><input type="text" id="manager" name="manager" size="20" value="'.$strManager.'"onfocus="this.select();" onmouseup="return false;" /></td>';
  echo '</tr>'; 

  echo '<tr>';
  echo '<td valign="top">Scheduler</td>';
  echo '<td><input type="text" id="scheduler" name="scheduler" size="20" value="'.$strScheduler.'"></td>';
  echo '</tr>'; 
  
  echo '<tr>';
  echo '<td valign="top">Smart Book User</td>';
  echo '<td><input type="text" id="smartbookuser" name="smartbookuser" size="20" value="'.$strSmartBookUser.'"></td>';
  echo '</tr>'; 
    
  echo '<tr>';
  echo '<td><input type="submit" value="Update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  if ($intID != 0) {
    echo '<td align="right"><img border="0" src="images/delete.gif" width="18" height="17" Style="cursor: pointer" onclick="javascript:DeleteSortCode('.$intID.')"; ></td>';
  }
  else {
    echo '<td></td>';
  }

  echo '</tr>';    


  
  echo '</table>';
  
  echo '<input id="managerlogon" type="hidden" name="managerlogon" value="'.$strManagerLogon.'">';
  echo '<input id="schedulerlogon" type="hidden" name="schedulerlogon" value="'.$strSchedulerLogon.'">';  
  echo '<input id="smartbookuserlogon" type="hidden" name="smartbookuserlogon" value="'.$strSmartBookUserLogon.'">';   
  
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '</form>';  

             
?>
<div id="dialog-delete-sortcode" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete this Sort Code match?.</p>
</div>
<script type="text/javascript">
  $('document').ready(function(){
    $('#neweditsortcodetypeform').validate({
      rules:{
         "description":{
           required:true
         },
         "hourlyrate":{
           required:true,
           number: true
         }        
        
        },

        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/system-admin-sortcode-edit.php', data:$('#neweditsortcodetypeform').serialize(), success: function(data) {
            $.facebox.close();
            ShowSortcodeMapping();            
          }});
        }  
  })
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "200px"
  });  
  });

function DeleteSortCode (id) {
  $( "#dialog-delete-sortcode" ).dialog({
    width:500,
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/admin/system-admin-sortcode-delete.php", {
        id: id
      },
      function(data,status){
        $.facebox.close();       
        ShowSortcodeMapping();  
      });
      },
      "No": function() {
        $( this ).dialog( "close" );
      },
    }
  }); 
}

$(function() {
  $( "#manager" ).autocomplete({
    source: "page-includes/admin/calls/list-available-people.php",
    minLength: 1,
    select: function( event, ui ) {
      $('#managerlogon').val(ui.item.id);
    }
  });
});
$(function() {
  $( "#scheduler" ).autocomplete({
    source: "page-includes/admin/calls/list-available-people.php",
    minLength: 1,
    select: function( event, ui ) {
      $('#schedulerlogon').val(ui.item.id);
    }
  });
});
$(function() {
  $( "#smartbookuser" ).autocomplete({
    source: "page-includes/admin/calls/list-available-people.php",
    minLength: 1,
    select: function( event, ui ) {
      $('#smartbookuserlogon').val(ui.item.id);
    }
  });
});
</script>