<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/adminfunctions.php';
$db = OpenDatabase();

if (isset($_REQUEST['departmentid'])) {
  $intDepartment = $_REQUEST['departmentid'];
}
else {
  $intDepartment = 0;
}



$login = '';
if (isset($_REQUEST['checkuser'])) {
    $login = $_REQUEST['Login'];
    // Find the user in the staff Table
    
    $arrUserDepartments = GetUserDepartments($login);
    if (isset($arrUserDepartments)) {
      // In the Allocations staff table and in an active department
      $intAction = 1;     
    }
    else {
      $arrGuestUserDepartments = GetGuestUserDepartments($login);    
      if (isset($arrGuestUserDepartments)) {
        // In the Guest staff table and may be in an active department
        $intAction = 2;     
      }

    else {
      $arrUser = bbc_GetFromLDAPFull($login);

      if (isset($arrUser) && !empty($arrUser)) {
        // Found in AD
        $intAction = 3;
      }
      else {
        //not found
        $intAction = 0;
      }
    }
  }
}
else {
  $intAction = 0;
}


switch ($intAction) {
  case 1;
    // The user is in Allocate
    echo '<table class="redtable" width="600px">';
    echo '<tr>';
    echo '<th colspan="2" align="center"><br>New Guest User<br><br></th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="2">';
    echo '<br>The Network Login you have entered is aready used in Allocate<br><br>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';  
    echo '<td>';  
    echo 'The Name of the user is';
    echo '</td>';
    echo '<td>';
    echo $arrUserDepartments['UserName'];
    echo '</td>';  
    echo '</tr>';    
    echo '<tr>';  
    echo '<td>';  
    echo 'Their Department(s)';
    echo '</td>';
    echo '<td>';
    foreach ($arrUserDepartments['Departments'] as $intDepID => $strDepName) {
      echo $strDepName.'<br>';
    }
    echo '</td>';  
    echo '</tr>';     
    echo '</table>';  
  break;
  
  case 2;
    // The user is in Guest User table
    echo '<table class="redtable" width="600px">';
    echo '<tr>';
    echo '<th colspan="2" align="center"><br>New Guest User<br><br></th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="2">';
    echo '<br>The Network Login you have entered is aready a Guest User<br><br>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';  
    echo '<td>';  
    echo 'The Name of the user is';
    echo '</td>';
    echo '<td>';
    echo $arrGuestUserDepartments['UserName'];
    echo '</td>';  
    echo '</tr>';
    if (isset($arrGuestUserDepartments['Departments'])) {    
      echo '<tr>';  
      echo '<td>';  
      echo 'Their Department(s)';
      echo '</td>';
      echo '<td>';
      foreach ($arrGuestUserDepartments['Departments'] as $intDepID => $strDepName) {
        echo $strDepName.'<br>';
      }
      echo '</td>';  
      echo '</tr>';
    }
    else {    
      echo '<tr>';  
      echo '<td colspan="2">';  
      echo 'They do not have acces to view any Departments';
      echo '</td>';
      echo '</tr>';
    }      
    echo '</table>';  
  break; 
 
  
  
  case 3;
    $strEmail = $arrUser['email'];
    $Surname = $arrUser['lastname'];
    $Forename = $arrUser['firstname'];
    $query = "INSERT INTO Staff_Guests
                 (Surname, Forename, Login)
                  VALUES        ('$Surname', '$Forename', '$login')";          
             
   sqlsrv_query($db, $query);

echo '<div id="dialog-added" title="Added....">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>The user has been added.</p>
</div>
<script type="text/javascript">
  $( function() {
    $( "#dialog-added" ).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "OK": function() {        
           $.facebox.close();';
if ($intDepartment == 0) {
  echo 'ShowGuestUsers();';
}           
else {           
  echo 'ShowGuestsThisDepartment('.$intDepartment.');';
}           
    echo '$( this ).dialog( "close" );
        },
      }
    });
  } );
</script>';
   break;
   default;
     // Show the initial form
  echo '<form id="newguestuser">';
  echo '<table class="redtable" width="600px">';
  echo '<tr>';
  echo '<th colspan="2" align="center"><br>New Guest User<br><br></th>';
  echo '</tr>';
  echo '<tr>';
  if ($login != '') {
  echo '<tr>';
  echo '<td colspan="2" align="center"><br>The Login has not been found. Please try again.<br><br></td>';
  echo '</tr>';
  echo '<tr>';
  }

  echo '<td>Network Login</td>';
  echo '<td>';
  echo '<input type="text" style="width:250px" id="Login" name="Login" size="35" value="'.$login.'">';
  echo '</td>';
  echo '</tr>';
  echo '</tr>';
  echo '<td></td>';
  echo '<td><input name="submit" type="submit" value="Check User"></input></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="checkuser" value="check">';
  echo '<input type="hidden" name="departmentid" value="'.$intDepartment.'">';  
  echo '</form>';
?>
<script type="text/javascript">
$('document').ready(function(){
  $('#newguestuser').validate({
    rules:{
      "Login":{
        required:true,

      }
    },
    errorElement: "div",
      errorPlacement: function(error, element) {
      error.insertAfter(element);
    },
    submitHandler: function(form) {
      $.ajax({type:'POST', url: 'page-includes/admin/addnewguestuser.php', data:$('#newguestuser').serialize(), success: function(data) {
        $.facebox(data);
      }});
    }
  })
});
</script>
<?php   
   
   
   
   
}

