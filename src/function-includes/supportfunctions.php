<?php

// ############################################################################### New Code

function GetStaffSupport ($intDepartmentID) {
  $db = OpenDatabase();
  $arrManagersAppraisorsMentors = GetManagersAppraisorsMentors();
 
  $strQuery = "SELECT          Staff.Surname, Staff.Forename, Staff.StaffNumber, Staff.Designation, Staff.DepartmentID, ISNULL(Staff.Login, N'') AS StaffLogin, 
                               Staff_Web_Config_Departments_Link.Managerlogin, Staff_Web_Config_Departments_Link.AppraiserLogin, Staff_Web_Config_Departments_Link.MentorLogin,
                               ISNULL(Staff_Web_Config_Departments_Link.isAppraiser, 0) AS isAppraiser, ISNULL(Staff_Web_Config_Departments_Link.isMentor, 0) AS isMentor, 
                               ISNULL(Staff_Web_Config_Departments_Link.isManager, 0) AS isManager
               FROM            Staff 
               LEFT OUTER JOIN Staff_Web_Config_Departments_Link ON Staff.Login = Staff_Web_Config_Departments_Link.Login 
               AND             Staff.DepartmentID = Staff_Web_Config_Departments_Link.DepartmentID
               WHERE           (Staff.DepartmentID = $intDepartmentID) AND (ISNULL(Staff.Login, N'') <> N'')
               ORDER BY        Staff.Forename, Staff.Surname";
  //echo $strQuery;
  //die;
  $rsStaff = sqlsrv_query($db, $strQuery);

  while($row = sqlsrv_fetch_array($rsStaff)){

    $arrStaff[$row['StaffLogin']]['FullName']  = $row['Surname'].', '.$row['Forename'];
    $arrStaff[$row['StaffLogin']]['StaffNumber']  = $row['StaffNumber'];
    $arrStaff[$row['StaffLogin']]['Designation']  = $row['Designation'];
    if (isset($arrManagersAppraisorsMentors['Managers'][$intDepartmentID][$row['Managerlogin']])) {
      $arrStaff[$row['StaffLogin']]['ManagerFullName']  = $arrManagersAppraisorsMentors['Managers'][$intDepartmentID][$row['Managerlogin']]; 
    }
    else {
      $arrStaff[$row['StaffLogin']]['ManagerFullName']  = '';
    }
    if (isset($arrManagersAppraisorsMentors['Appraisers'][$intDepartmentID][$row['AppraiserLogin']])) {
      $arrStaff[$row['StaffLogin']]['AppraiserFullName']  = $arrManagersAppraisorsMentors['Appraisers'][$intDepartmentID][$row['AppraiserLogin']]; 
    }
    else {
      $arrStaff[$row['StaffLogin']]['AppraiserFullName']  = '';
    }
    if (isset($arrManagersAppraisorsMentors['Mentors'][$intDepartmentID][$row['MentorLogin']])) {
      $arrStaff[$row['StaffLogin']]['MentorFullName']  = $arrManagersAppraisorsMentors['Mentors'][$intDepartmentID][$row['MentorLogin']]; 
    }
    else {
      $arrStaff[$row['StaffLogin']]['MentorFullName']  = '';
    }   
    $arrStaff[$row['StaffLogin']]['isManager']  = $row['isManager'];   
    $arrStaff[$row['StaffLogin']]['isAppraiser']  = $row['isAppraiser'];   
    $arrStaff[$row['StaffLogin']]['isMentor']  = $row['isMentor'];               
        
     
  }
  if (isset($arrStaff)) {
    return $arrStaff;
  }
}
function GetManagersAppraisorsMentors ($intDepartmentID = 0) {
  $db = OpenDatabase();

  $strQuery = "SELECT        Staff_Web_Config_Departments_Link.Login, Staff_Web_Config_Departments_Link.isAppraiser, Staff_Web_Config_Departments_Link.isMentor, 
                         Staff_Web_Config_Departments_Link.isManager, Departments.FullName AS DepartmentName, Departments.ID AS DepartmentID, CASE WHEN Staff.Surname IS NULL 
                         THEN Staff_Guests.Forename + N' ' + Staff_Guests.Surname ELSE Staff.Forename + N' ' + Staff.Surname END AS FullName
              FROM            Staff_Web_Config_Departments_Link INNER JOIN
                         Departments ON Staff_Web_Config_Departments_Link.DepartmentID = Departments.ID LEFT OUTER JOIN
                         Staff_Guests ON Staff_Web_Config_Departments_Link.Login = Staff_Guests.Login LEFT OUTER JOIN
                         Staff ON Staff_Web_Config_Departments_Link.Login = Staff.Login
GROUP BY Staff_Web_Config_Departments_Link.Login, Departments.FullName, Departments.ID, CASE WHEN Staff.Surname IS NULL 
                         THEN Staff_Guests.Forename + N' ' + Staff_Guests.Surname ELSE Staff.Forename + N' ' + Staff.Surname END, Staff_Web_Config_Departments_Link.isAppraiser, 
                         Staff_Web_Config_Departments_Link.isMentor, Staff_Web_Config_Departments_Link.isManager
HAVING        (Staff_Web_Config_Departments_Link.isManager = 1) AND (NOT (CASE WHEN Staff.Surname IS NULL 
                         THEN Staff_Guests.Forename + N' ' + Staff_Guests.Surname ELSE Staff.Forename + N' ' + Staff.Surname END IS NULL)) OR
                         (Staff_Web_Config_Departments_Link.isMentor = 1) AND (NOT (CASE WHEN Staff.Surname IS NULL 
                         THEN Staff_Guests.Forename + N' ' + Staff_Guests.Surname ELSE Staff.Forename + N' ' + Staff.Surname END IS NULL)) OR
                         (Staff_Web_Config_Departments_Link.isAppraiser = 1) AND (NOT (CASE WHEN Staff.Surname IS NULL 
                         THEN Staff_Guests.Forename + N' ' + Staff_Guests.Surname ELSE Staff.Forename + N' ' + Staff.Surname END IS NULL))";
  //echo $strQuery;
  $rsStaff = sqlsrv_query($db, $strQuery);

  while($row = sqlsrv_fetch_array($rsStaff)){
    if ($row['isAppraiser'] == 1) {
      $arrStaff['Appraisers'][$row['DepartmentID']][$row['Login']] = $row['FullName'];
      $arrStaff['Departments'][$row['DepartmentID']] = $row['DepartmentName'];      
      
    }
    if ($row['isMentor'] == 1) {
      $arrStaff['Mentors'][$row['DepartmentID']][$row['Login']] = $row['FullName'];
      $arrStaff['Departments'][$row['DepartmentID']] = $row['DepartmentName'];  
    }  
    if ($row['isManager'] == 1) {
      $arrStaff['Managers'][$row['DepartmentID']][$row['Login']] = $row['FullName'];
      $arrStaff['Departments'][$row['DepartmentID']] = $row['DepartmentName'];        
    }    
  }
  if (isset($arrStaff)) {
    return ($arrStaff);
  }
}
function GetStaffManagerAppraisoMentor ($strLogin, $intDepartmentID) {
  $db = OpenDatabase();

  $strQuery = "SELECT        ISNULL(Managerlogin, N'') AS Managerlogin, ISNULL(AppraiserLogin, N'') AS AppraiserLogin, ISNULL(MentorLogin, N'') AS MentorLogin
               FROM          Staff_Web_Config_Departments_Link
               WHERE         (DepartmentID = $intDepartmentID) AND (Login = N'$strLogin')";

  $rsStaff = sqlsrv_query($db, $strQuery);

  $row = sqlsrv_fetch_array($rsStaff);

  $arrStaff['ManagerLogin'] = $row['Managerlogin'];
  $arrStaff['AppraiserLogin'] = $row['AppraiserLogin'];
  $arrStaff['MentorLogin'] = $row['MentorLogin'];

  if (isset($arrStaff)) {
    return ($arrStaff);
  }
}
?>