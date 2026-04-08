<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/userGroupfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

  $row = GetWeeklyFilterOption($strUser);
  
  
  $intWeeklyFilterOption = $row['WeeklyFilterOption'];
  if ($intWeeklyFilterOption == 0) {
    $strTopClass = 'imageborder';
    $strBottomClass = 'imagewhiteborder';
  }
  else {
    $strTopClass = 'imagewhiteborder';
    $strBottomClass = 'imageborder';  
  
  }

  echo '<table class="tablesmall">';
  echo '<tr>';
  echo '<th><br>When you apply a Duty Names filter you can show all the Duties for a person where a match has been found.
        <br>Or you can choose only to show matching duties.
        <br>Please choose the view below which best suits you.<br>Your Current Choice is highlighted in green.<br><br></th>';
  echo '</tr>';
  echo '<tr>';
  echo '<th>';
  echo '<br>Keep All Duties<br><br>';
  echo '</th>';
  echo '</tr>';  
  echo '<tr>';
  echo '<td>';
  echo '<img class="handcursor '.$strTopClass.'" src="../images/WeeklyMatch_0.png" width="1308px" height="202px" onclick=\'javascript:ToggleOption(0)\';>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<th>';
  echo '<br>Remove Unmatched Duties<br><br>';
  echo '</th>';
  echo '</tr>';   
  echo '<tr>';
  echo '<td>';
  echo '<img class="handcursor '.$strBottomClass.'" src="../images/WeeklyMatch_1.png" width="1308px" height="202px" onclick=\'javascript:ToggleOption(1)\';>';
  echo '</td>';
  echo '</tr>';
  echo '</table>'; 
  
?>

<script type="text/javascript">
function ToggleOption  (option) {
  $.post("page-includes/users/user-options-weekly-toggle.php", {
    option: option
  },
  function(data,status){
    ShowWeeklyOptions();
   }
  )
} 

</script>  
  
  