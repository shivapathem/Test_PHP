<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$strUserName = $_SESSION['user']['FullName'];

$db = OpenDatabase();

$query = "SELECT         Staff_Web_Config_Departments_Link.Login as ApraiseeLogin, Staff.Forename + N' ' + Staff.Surname + N' (' + Departments.FullName + N')' AS ApraiseeName
          FROM           Staff_Web_Config_Departments_Link 
          INNER JOIN     Staff ON Staff_Web_Config_Departments_Link.Login = Staff.Login AND Staff_Web_Config_Departments_Link.DepartmentID = Staff.DepartmentID 
          INNER JOIN     Departments ON Staff_Web_Config_Departments_Link.DepartmentID = Departments.ID
          WHERE          (Staff_Web_Config_Departments_Link.AppraiserLogin = N'$strUser')
          GROUP BY       Staff_Web_Config_Departments_Link.Login, Staff.Surname, Staff.Forename, Staff.Forename + N' ' + Staff.Surname + N' (' + Departments.FullName + N')'
          ORDER BY       Staff.Surname, Staff.Forename";

$users = sqlsrv_query($db, $query);


echo '<div style="width:80%; margin:0 auto; position:relative;">';
echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
echo '<br>My Appraisees<br><br>';
echo '</div><br>';

echo '<table class="tablesmall" width="100%">';
echo '<tr>';  
  
$i = 0; 
if ($i == 4) {   
  echo '<tr>';
  echo '<td class="tablesmallgrey" colspan="4" height="5px">';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';        
  $i = 0;
}


  while($row = sqlsrv_fetch_array($users)){
    echo '<td align="center" valign="top">';
    echo '<table class="tablesmallgrey" width="100%">';
    echo '<tr>';      
    echo '<th>';    
    echo $row['ApraiseeName'];
    echo '</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td align="center">';    

    $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$row['ApraiseeLogin'].'.*');
    //$pics = glob(sql_regcase('/var/www/allocations/images/staffpics/'.$row['ApraiseeLogin'].'.*'));
    if (count($pics) > 0) {
      $picname = explode(DIRECTORY_SEPARATOR, $pics[0]);
      $picname = $picname[count($picname) -1];
      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'" width="100px">';
    }
    else {
      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif" width="100px">';
    }
    echo '</td>';
    echo '</tr>';
    echo '</table>';            
    
    
    
    
    echo '</td>';
    if ($i == 3) {  
      echo '</tr>';  
    }
    $i++;     
  }

echo '</tr>';
echo '</table>';
echo '</div>';
//print_r($arrAppraisers);