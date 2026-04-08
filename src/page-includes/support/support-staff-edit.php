<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/supportfunctions.php';

$strLogin = $_REQUEST['login'];
$intDepartmentID = $_REQUEST['departmentid'];
  // Get the list of Appraisers / mentors and managers
  $arrManagersAppraisorsMentors = GetManagersAppraisorsMentors();
  // Get the user values
  $arrManagerAppraisoMentor = GetStaffManagerAppraisoMentor ($strLogin, $intDepartmentID);
  
if (isset($_POST['update'])) {
  $strOrigManagerLogin   = $arrManagerAppraisoMentor['ManagerLogin'];
  $strOrigAppraiserLogin   = $arrManagerAppraisoMentor['AppraiserLogin'];
  $strOrigMentorLogin   = $arrManagerAppraisoMentor['MentorLogin'];    
  
  $strManagerLogin   = $_POST['manager'];
  $strAppraiserLogin = $_POST['appraiser'];
  $strMentorLogin    = $_POST['mentor'];
  $strHistory = '';
  // Make up the History....
  if ($strOrigManagerLogin != $strManagerLogin) {
    if ($strManagerLogin == '0') {
      $strHistory.= 'Manager Changed to No One.<br>';      
    }
    else {
      $strHistory.= 'Manager Changed to '.$arrManagersAppraisorsMentors['Managers'][$strManagerLogin].'.<br>';
    }  
  }
  
  if ($strOrigAppraiserLogin != $strAppraiserLogin) {
    if ($strAppraiserLogin == '0') {
      $strHistory.= 'Appraiser Changed to No One.<br>';      
    }
    else {
      $strHistory.= 'Appraiser Changed to '.$arrManagersAppraisorsMentors['Appraisers'][$strAppraiserLogin].'.<br>';
    }
  }  
  if ($strOrigMentorLogin != $strMentorLogin) {
    if ($strMentorLogin == '0') {
      $strHistory.= 'Mentor Changed to No One.<br>';      
    }
    else {
      $strHistory.= 'Mentor Changed to '.$arrManagersAppraisorsMentors['Mentors'][$strMentorLogin].'.<br>';  
    }
  } 
  if ($strHistory != '') {
    $db = OpenDatabase();
    $strHistory = "Record updated by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i").".<br>".$strHistory."<hr>";
    $strHistory = escapeSingleQuotes($strHistory);

    // Update the record
    $strQuery = "IF EXISTS   (SELECT        Login
                           FROM          Staff_Web_Config_Departments_Link
                           WHERE         (Login = '$strLogin') AND (DepartmentID = $intDepartmentID))
              UPDATE       Staff_Web_Config_Departments_Link
              SET          ManagerLogin = '$strManagerLogin', AppraiserLogin = '$strAppraiserLogin', MentorLogin = '$strMentorLogin'
              WHERE        (Login = N'$strLogin') AND (DepartmentID = $intDepartmentID)
              ELSE
              INSERT INTO Staff_Web_Config_Departments_Link(Login, DepartmentID, ManagerLogin, AppraiserLogin, MentorLogin)
              VALUES       (N'$strLogin', $intDepartmentID, '$strManagerLogin', '$strAppraiserLogin', '$strMentorLogin')"; 
    sqlsrv_query($db, $strQuery);

    // Update the history
    $strQuery = "IF EXISTS   (SELECT          ID
                          FROM            Staff_Web_Config
                          WHERE          (Login = N'$strLogin'))
              UPDATE      Staff_Web_Config
              SET         History = CONCAT('$strHistory', ISNULL(History,''))
              WHERE       (Login = N'$strLogin')
              ELSE
                          INSERT INTO Staff_Web_Config
                          (History, Login)
              VALUES      ('$strHistory', '$strLogin')"; 

    sqlsrv_query($db, $strQuery);
  }
}
else {

  $strFullName = GetFullNameFromLogin($strLogin);
  echo '<form name="supportstaffeditform" id="supportstaffeditform">';
  echo '<input type="hidden" name="login" value="'.$strLogin.'">';
  echo '<input type="hidden" name="departmentid" value="'.$intDepartmentID.'">';

  echo '<table width="600px" class="tablesmalltidy">';
  echo '<tr>';
  echo '<th colspan="2"><br>Editing information for '.$strFullName.'<br><br></th>';
  echo '</tr>';


  // Who's the Manager
  echo '<tr>';
  echo '<th width="150px">Line Manager</th>';
  echo '<td>';

  echo '<select style="width:250px" class="chosen-select" name="manager">';
  echo '<option value="0">-</option>';
  if (isset($arrManagersAppraisorsMentors['Managers'][$intDepartmentID])) {
    foreach ($arrManagersAppraisorsMentors['Managers'][$intDepartmentID] as $strManagerLogin => $strManagerName) {
      if ($strManagerLogin == $arrManagerAppraisoMentor['ManagerLogin']) {
        echo '<option selected value="'.$strManagerLogin.'">'.$strManagerName.'</option>';
      }
      else {
        echo '<option value="'.$strManagerLogin.'">'.$strManagerName.'</option>';
      }
    }
  }
  echo '</select>';
  //die;
  echo '</td>';
  echo '</tr>';

  //Who's the Appraiser
  echo '<tr>';
  echo '<td>Appraiser</td>';
  echo '<td>';

  echo '<select style="width:250px" class="chosen-select" name="appraiser">';
  echo '<option value="0">-</option>';
  if (isset($arrManagersAppraisorsMentors['Appraisers'][$intDepartmentID])) {
    foreach ($arrManagersAppraisorsMentors['Appraisers'][$intDepartmentID] as $strAppraiserLogin => $strAppraiserName) {
      if ($strAppraiserLogin == $arrManagerAppraisoMentor['AppraiserLogin']) {
        echo '<option selected value="'.$strAppraiserLogin.'">'.$strAppraiserName.'</option>';
      }
      else {
        echo '<option value="'.$strAppraiserLogin.'">'.$strAppraiserName.'</option>';
      }
    }
  }
  echo '</select>';
  echo '</td>';
  echo '</tr>';

  // Who's the Mentor
  echo '<tr>';
  echo '<td>Mentor</td>';
  echo '<td>';
  echo '<select style="width:250px" class="chosen-select" name="mentor">';
  echo '<option value="0">-</option>';
  if (isset($arrManagersAppraisorsMentors['Mentors'][$intDepartmentID])) {
    foreach ($arrManagersAppraisorsMentors['Mentors'][$intDepartmentID] as $strMentorLogin => $strMentorName) {
      if ($strMentorLogin == $arrManagerAppraisoMentor['MentorLogin']) {
        echo '<option selected value="'.$strMentorLogin.'">'.$strMentorName.'</option>';
      }
      else {
        echo '<option value="'.$strMentorLogin.'">'.$strMentorName.'</option>';
      }
    }
  }
  echo '</select>';

  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td></td>';
  echo '<td colspan="2"><input type="submit" value="Update" name="update"></td>';
  echo '</tr>';
  echo '</table>';
  echo '</form>';
?>

<script type="text/javascript">
$('document').ready(function(){
  $('#supportstaffeditform').validate({
    submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
    $.ajax({type:'POST', url: 'page-includes/support/support-staff-edit.php', data:$('#supportstaffeditform').serialize(), success: function(data) {
      $.facebox.close();
      GetContent(<?php echo $intDepartmentID?>)
    }});
  }
  })
    
  $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
  $(function() {
    $( "button" )
      .button()
  });
});
</script>

<?php
}
?>