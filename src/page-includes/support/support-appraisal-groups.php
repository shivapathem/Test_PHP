<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intDepartemtID = $_REQUEST['depid'];

//$strDepartmentName = GetDepartmentNameFromID($intDepartemtID);


$intCellWidth = 250;
$intAppraiserHeight = 22;
$intAppraiseesHeight = 175;
$intTop = 55;
$db = OpenDatabase();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];



$query = "SELECT        Staff.Forename + N' ' + Staff.Surname AS ApraiseeName, Staff.Login, Staff_Web_Config_Departments_Link.AppraiserLogin, CASE WHEN Staff_1.Surname IS NULL 
                         THEN Staff_Guests.Forename + N' ' + Staff_Guests.Surname ELSE Staff_1.Forename + N' ' + Staff_1.Surname END AS AppraiserName
FROM            Staff INNER JOIN
                         Staff_Web_Config_Departments_Link ON Staff.DepartmentID = Staff_Web_Config_Departments_Link.DepartmentID AND 
                         Staff.Login = Staff_Web_Config_Departments_Link.Login LEFT OUTER JOIN
                         Staff_Guests ON Staff_Web_Config_Departments_Link.AppraiserLogin = Staff_Guests.Login LEFT OUTER JOIN
                         Staff AS Staff_1 ON Staff_Web_Config_Departments_Link.AppraiserLogin = Staff_1.Login
WHERE        (Staff.DepartmentID = $intDepartemtID) AND (Staff_Web_Config_Departments_Link.AppraiserLogin <> N'0')
ORDER BY Staff_Web_Config_Departments_Link.AppraiserLogin";

$users = sqlsrv_query($db, $query); 

while($row = sqlsrv_fetch_array($users)){
  $arrAppraisers[$row['AppraiserLogin']]['Appraiser'] = $row['AppraiserName'];  
  $arrAppraisers[$row['AppraiserLogin']]['Apraisees'][$row['Login']] = $row['ApraiseeName'];  
}

//echo '<div style="width:80%; margin:0 auto; position:relative;">';
//echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
//echo '<br>Appraisal Groups for '.$strDepartmentName.'<br><br>';
//echo '</div><br>';
  
$i = 0; 
if (isset($arrAppraisers)) { 
  echo '<table class="tablesmallgrey" width="100%">';
  echo '<tr>';
  foreach ($arrAppraisers as $appraiserLogin => $arrAppraiser) {
    if ($i == 4) {   
      echo '<tr>';      
      $i = 0;
    }
    echo '<td valign="top">';
    echo '<table class="tablesmall" width="100%">';
    echo '<tr>';
    echo '<th height="40px"><b>';

    echo $arrAppraiser['Appraiser'];
    echo '</b></th>';
     foreach ($arrAppraiser['Apraisees'] as $astrAppraiseeLogin => $strAppraisee) { 
      echo '<tr>';   
      echo '<td>'; 
      echo $strAppraisee.'<br>';
      echo '</td>';   
      echo '</tr>';
    } 
    echo '</table>';  
    echo '</td>';
    if ($i == 3) {  
      echo '</tr>';  
    }
    $i++;  
  }
  echo '</table>';
}
else {
echo '<div style="width:80%; margin:0 auto; position:relative;">';
echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
echo '<br>There are no Appraisers defined<br><br>';
echo '</div><br>';


}