<?php
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';

$db = OpenDatabase();
$strTerm = $_REQUEST['term'];

$query = "SELECT              Staff.Forename + N' ' + Staff.Surname AS FullName, ISNULL(Staff.Login, N'') AS Login, Staff_Guests.Forename + N' ' + Staff_Guests.Surname AS GuestFullName, 
                              Staff_Guests.Login AS GuestLogin
          FROM                Staff 
          FULL OUTER JOIN     Staff_Guests ON Staff.Login = Staff_Guests.Login
          GROUP BY            Staff.Forename + N' ' + Staff.Surname, ISNULL(Staff.Login, N''), Staff_Guests.Forename + N' ' + Staff_Guests.Surname, Staff_Guests.Login
          HAVING              (ISNULL(Staff.Login, N'') <> N'') AND (Staff.Forename + N' ' + Staff.Surname LIKE N'%$strTerm%') 
            OR                (Staff_Guests.Forename + N' ' + Staff_Guests.Surname LIKE N'%$strTerm%')
          ORDER BY            FullName";

$rsUsers = sqlsrv_query($db, $query);


$rs = array();
$i = 0;

while($row = sqlsrv_fetch_array($rsUsers)) {
  if ($row['Login'] == '') {
    $rs[$i]['value'] = iconv('UTF-8', 'UTF-8//IGNORE', $row['GuestFullName']);
    $rs[$i]['id'] = $row['GuestLogin'];  
  }
  else {
    $rs[$i]['value'] = iconv('UTF-8', 'UTF-8//IGNORE', $row['FullName']);
    $rs[$i]['id'] = $row['Login'];
  }



    $i++;
};
//print_r($rs);
$encoded = json_encode($rs); 



if ($encoded!='[false]') {
  echo $encoded;

}
