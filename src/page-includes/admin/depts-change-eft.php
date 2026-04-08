<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$strStaffLogin = $_REQUEST['login'];  
$intTeamID = $_REQUEST['teamid']; 

if (isset($_REQUEST['source'])) {
  $intSource = $_REQUEST['source'];
}
else {
  $intSource  = 0;
}
if (isset($_REQUEST['year'])) {
  $intYear = $_REQUEST['year'];
}
else {
  $intYear  = 0;
}


if (isset($_REQUEST['submit'])) {
  $strDepName = GetTeamNameFromID($intTeamID);

  $intEFT = $_REQUEST['EFT']; 
  $db = OpenDatabase();
  $strQuery = "if exists (SELECT        1 AS Expr1
                          FROM          Staff_Web_Config_Departments_Link
                          WHERE         (Login = N'$strStaffLogin') AND (DepartmentID = $intTeamID))
               UPDATE                    Staff_Web_Config_Departments_Link
                          SET           EFT = N'$intEFT'
                          WHERE         (Login = N'$strStaffLogin') AND (DepartmentID = $intTeamID)

               ELSE       INSERT INTO Staff_Web_Config_Departments_Link
                          (EFT, Login, DepartmentID)
               VALUES     (N'$intEFT', N'$strStaffLogin', $intTeamID)";

sqlsrv_query($db, $strQuery); 
$strHistory = 'EFT changed to '.$intEFT.' for '.$strDepName;
$strHistory.= ' by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i").'<hr>';



// Update the history.........
$strQuery = "UPDATE     Staff_Web_Config
             SET        History = CONCAT('$strHistory', ISNULL(History,''))
             WHERE   (Login = N'$strStaffLogin')";

sqlsrv_query($db, $strQuery);  


}
else {
  $arrUserAndEFT = GetNameAndEFT ($strStaffLogin, $intTeamID);

  echo '<form id="editeft">';
  echo '<table class="redtable" width="600px">';  
  echo '<tr height="30px">';  
  echo '<th>Editing EFT for '.$arrUserAndEFT['FullName'].'</th>';
  echo '</tr>';
  echo '</table>';
  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>'; 

  echo '<table class="redtable" width="600px">';   
  echo '<tr>';  
  echo '<td>EFT</td>';  
  echo '<td>';
  echo '<input id="EFT" name="EFT" type="text" size="40" value="'.round(($arrUserAndEFT['EFT']), 3).'" autofocus />';
  echo '</td>';
  echo '</tr>';
  
  echo '<tr>';   
  echo '<td></td>'; 
  echo '<td>'; 
  echo '<input id="submit" name="submit" type="submit" value="Update"></input><input type="button" name="Cancel" value="Cancel" onclick="abort()">';  
  echo '</td>'; 
  echo '</tr>';   
  echo '</table>';
  echo '<input type="hidden" name="login" value="'.$strStaffLogin.'">';  
  echo '<input type="hidden" name="teamid" value="'.$intTeamID.'">';   
  
  
  echo '</form>'; 
  
?>       
 <script type="text/javascript">
$('document').ready(function(){
    $('#editeft').validate({
      errorLabelContainer: "#errorBox",
      rules:{
        "EFT":{
          required:true,
          min: 0,
          max: 1,
        }          
      },
      messages: {
        EFT: "Please Enter a value between Zero and One<br>",
      },      
      submitHandler: function(form) {
        $('input[type="submit"]').prop('disabled', true);
        $.ajax({type:'POST', url: 'page-includes/admin/depts-change-eft.php', data:$('#editeft').serialize(), success: function(data) {
          $.facebox.close(); 
          
<?php          
          
  if ($intSource == 0) {        
    echo 'ShowStaffThisDepartment('.$intTeamID.');';
  } 
  else {
    echo 'GetLeaveCreditTab('.$intTeamID.',0,'.$intYear.')';
  
  }         
?>          
          
          
            
        }});
      }
    })
  $( "#EFT" ).focus();    
  $( "#EFT" ).select();
});

function abort() {
  $.facebox.close(); 
        
} 
</script>   

<?php

}
?>
  