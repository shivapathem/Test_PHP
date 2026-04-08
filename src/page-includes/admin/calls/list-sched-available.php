<?php
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';

$db = OpenDatabase();
$strTerm = $_REQUEST['term'];
          
$query = "SELECT          RTRIM(LTRIM(Teampay_Aux.dbo.SchedStaff.EmployeeName)) AS FullName, ISNULL(Teampay_Aux.dbo.SchedStaff.BBCNetworkLogin, '') AS Login
          FROM            Teampay_Aux.dbo.SchedStaff LEFT OUTER JOIN
                          Staff ON Teampay_Aux.dbo.SchedStaff.ExtStaffNumber = Staff.StaffNumber
          WHERE          (ISNULL(Teampay_Aux.dbo.SchedStaff.ExtStaffNumber, '') <> '') AND (ISNULL(Teampay_Aux.dbo.SchedStaff.BBCNetworkLogin, '') <> '') AND 
                         (Teampay_Aux.dbo.SchedStaff.EmployeeName LIKE '%$strTerm%') AND (ISNULL(Staff.DepartmentID, 255) = 255)
          ORDER BY       FullName";



$rsUsers = sqlsrv_query($db, $query);


$rs = array();
$i = 0;

while($row = sqlsrv_fetch_array($rsUsers)) {
    $rs[$i]['value'] = iconv('UTF-8', 'UTF-8//IGNORE', $row['FullName']);
    $rs[$i]['id'] = $row['Login'];
    $i++;
};
//print_r($rs);
$encoded = json_encode($rs); 



if ($encoded!='[false]') {
  echo $encoded;

}
