<?php
include_once "DBHelper.php";

function GetUserSettings($strLogin, $intSaveSession = 1) {
  $db = OpenDatabase();
  // Get all The Departments
  $arrUser['isGuest'] = -1; 
  $arrUser['ProdDayCount'] = 7;  
  $arrUser['ProdStartDay'] = 0;      
  $arrUser['DefaultTeamID'] = 0;
  $arrUser['ShowReports'] = $arrUser['isAppraiser'] = $arrUser['isSkillsAdmin'] = $arrUser['isShiftLeader'] = $arrUser['isScheduler'] = $arrUser['isMentor'] = $arrUser['isManager'] = $arrUser['isAdmin'] = $arrUser['HasDepartmentAdmin'] = $arrUser['DefaultDepartment'] = $arrUser['HasLeave'] = $arrUser['HasLeaveAdmin'] = $arrUser['HasRequests'] = $arrUser['HasRequestsAdmin'] = $arrUser['IsScheduled'] = $arrUser['HasHandovers'] = $arrUser['HasXmasPoints'] =  $arrUser['IsScheduAll'] = $arrUser['ProdStartDay'] = $arrUser['DefaultTeamID'] = 0;
  $strQuery = "SELECT ID, FullName, HasHandovers, HasXmasPoints, eMail, isnull(HasDutiesView, -1) as HasDutiesView FROM Departments ORDER BY FullName";
  $rsDepartments = sqlsrv_query( $db, $strQuery);  
  while ($row = sqlsrv_fetch_array($rsDepartments)) {                                          
    $arrDepartments[$row['ID']]['DepartmentName'] = $row['FullName'];
    $arrDepartments[$row['ID']]['HasHandovers'] = $row['HasHandovers'];
    $arrDepartments[$row['ID']]['HasXmasPoints'] = $row['HasXmasPoints'];
    $arrDepartments[$row['ID']]['HasDutiesView'] = $row['HasDutiesView'];    
    $arrDepartments[$row['ID']]['eMail'] = $row['eMail'];         
    $arrDepartments[$row['ID']]['IsScheduled'] = 0;
    $arrDepartments[$row['ID']]['HasAccess'] = 0;
  }  
  // Get this users Scheduled Departments
  $strQuery = "SELECT Forename, Surname, StaffNumber, DepartmentID, Login FROM Staff WHERE (Login = N'$strLogin') AND DepartmentID <> 255"; 
  $rsUser = sqlsrv_query( $db, $strQuery);  
  while ($row = sqlsrv_fetch_array($rsUser)) {  
    $arrUser['Surname'] = $row['Surname'];
    $arrUser['Forename'] = $row['Forename'];
    $arrUser['isGuest'] = 0;
    $arrUser['isAllocate'] = 1; 
    $arrUser['StaffNumbers'][$row['DepartmentID']] = $row['StaffNumber']; 
    $arrDepartments[$row['DepartmentID']]['StaffNumber'] = $row['StaffNumber']; 
    $arrUser['IsScheduled'] = 1;
    if ($row['DepartmentID'] < 0) {
      $arrUser['IsScheduAll'] = 1;
    }
    $arrDepartments[$row['DepartmentID']]['IsScheduled'] = 1;
    $arrDepartments[$row['DepartmentID']]['HasAccess'] = 1;
 }
  if (!isset($arrUser['Surname'])) {    
      // Get them as a Guest
      $strQuery = "SELECT Surname, Forename, Login FROM Staff_Guests WHERE (Login = N'$strLogin')";
      $rsUser = sqlsrv_query($db, $strQuery);  
      while ($row = sqlsrv_fetch_array($rsUser)) {  
        $arrUser['Surname'] = $row['Surname'];
        $arrUser['Forename'] = $row['Forename'];
        $arrUser['isAllocate'] = 0;
        $arrUser['isGuest'] = 1;   
      }
    }  
  if(isset($arrUser)) {
    // Get the departments we have access to....
     $strQuery = "SELECT DepartmentID, isAdmin, isDefault, ShowReports, isAppraiser, isSkillsAdmin, isShiftLeader, isScheduler, isMentor, isManager, HideRota
                  FROM Staff_Web_Config_Departments_Link WHERE (Login = '$strLogin')";
    $rsUser = sqlsrv_query($db, $strQuery);  
    while ($row = sqlsrv_fetch_array($rsUser)) {  
      $arrDepartments[$row['DepartmentID']]['HasAccess'] = 1;
      if (isset($arrDepartments[$row['DepartmentID']]['HasXmasPoints']) && $arrDepartments[$row['DepartmentID']]['HasXmasPoints'] == 1) {
        $arrUser['HasXmasPoints'] = 1;
      } 
      if ($row['isAdmin'] == 1) {
        $arrUser['HasDepartmentAdmin'] = $row['isAdmin'];
        $arrDepartments[$row['DepartmentID']]['Admin'] = 1;
        $arrUser['isSkillsAdmin'] = 1;
        $arrDepartments[$row['DepartmentID']]['SkillsAdmin'] = 1;          
      }else {
        $arrDepartments[$row['DepartmentID']]['Admin'] = 0;            
      }      
      if ($row['isDefault'] == 1) {
        $arrUser['DefaultDepartment'] = $row['DepartmentID'];
        $arrDepartments[$row['DepartmentID']]['Default'] = 1;
      }
      if ($row['ShowReports'] == 1) {
        $arrUser['ShowReports'] = 1;
        $arrDepartments[$row['DepartmentID']]['ShowReports'] = 1;      
      }
      if ($row['isAppraiser'] == 1) {
        $arrUser['isAppraiser'] = 1;
        $arrDepartments[$row['DepartmentID']]['Appraiser'] = 1;      
      } 
      if ($row['isSkillsAdmin'] == 1) {
        $arrUser['isSkillsAdmin'] = 1;
        $arrDepartments[$row['DepartmentID']]['SkillsAdmin'] = 1;       
      }       
      if ($row['isShiftLeader'] == 1) {
        $arrUser['isShiftLeader'] = 1;
        $arrDepartments[$row['DepartmentID']]['isShiftLeader'] = 1;
        if ($arrDepartments[$row['DepartmentID']]['HasHandovers']) {
          $arrUser['HasHandovers'] = 1;
        }
      }        
      if ($row['isScheduler'] == 1) {
        $arrUser['isScheduler'] = 1;
        $arrDepartments[$row['DepartmentID']]['DepartmentScheduler'] = 1;
        $arrUser['isSkillsAdmin'] = 1;
        $arrDepartments[$row['DepartmentID']]['SkillsAdmin'] = 1;                        
      }       
      if ($row['isMentor'] == 1) {
        $arrUser['isMentor'] = 1;      
        $arrDepartments[$row['DepartmentID']]['Mentor'] = 1;
      }         
      if ($row['isManager'] == 1) {
        $arrUser['isManager'] = 1;      
        $arrDepartments[$row['DepartmentID']]['Manager'] = 1;
      }         
    }    
  }  
  foreach ($arrDepartments as $intDepartmentID => $arrDepartment) {
    if($arrDepartment['HasAccess'] == 0) {
      unset($arrDepartments[$intDepartmentID]);    
    }
  }
  if (count($arrDepartments) > 0) {
    $arrUser['Departments'] = $arrDepartments;
  }
  // Now get Leave 
  $strQuery = "SELECT ISNULL(Staff_Web_Config_LeaveGroups_Link.Admin, 0) AS LeaveAdmin, LeaveRequestGroups.Description, LeaveRequestGroups.ID,  leave_types.ID AS LeaveTypeID, leave_types.description AS LeaveTypeDescription, RequestTypes.ID AS RequestTypeID, RequestTypes.description AS RequestType FROM Staff_Web_Config_LeaveGroups_Link INNER JOIN LeaveRequestGroups ON Staff_Web_Config_LeaveGroups_Link.LeaveGroupID = LeaveRequestGroups.ID LEFT OUTER JOIN leave_types ON LeaveRequestGroups.ID = leave_types.GroupID  LEFT OUTER JOIN RequestTypes ON LeaveRequestGroups.ID = RequestTypes.GroupID WHERE (Staff_Web_Config_LeaveGroups_Link.Login = N'$strLogin') ORDER BY LeaveRequestGroups.Description";
    $rsLeave = sqlsrv_query($db, $strQuery);  
    while ($row = sqlsrv_fetch_array($rsLeave)) { 
      $arrUser['LeaveRequests'][$row['ID']]['Description'] = $row['Description'];
      $arrUser['LeaveRequests'][$row['ID']]['Admin'] = $row['LeaveAdmin'];       
      if(!is_null($row['LeaveTypeID'])) {
        $arrUser['LeaveRequests'][$row['ID']]['LeaveTypes'][$row['LeaveTypeID']] =  $row['LeaveTypeDescription']; 
      }
      if ($row['LeaveAdmin'] == 0 && !is_null($row['LeaveTypeID'])) {
        $arrUser['HasLeave'] = 1;      
      }
      if ($row['LeaveAdmin'] >= 1) {
        $arrUser['HasLeaveAdmin'] = 1;      
      }
      if (!is_null($row['RequestTypeID'])) {
        if ($row['LeaveAdmin'] == 0) {
          $arrUser['HasRequests'] = 1;      
        }
        if ($row['LeaveAdmin'] >= 1) {
          $arrUser['HasRequestsAdmin'] = 1;      
        }      
      }
   }
  if ($arrUser['isGuest'] == 0) {  
    $strQuery = "SELECT COUNT(skills_programmes_staff_link.programmes_id) AS CountSkills FROM skills_programmes_staff_link INNER JOIN StaffDetails ON skills_programmes_staff_link.staff_id = StaffDetails.StaffID WHERE (StaffDetails.NetLogin = N'$strLogin')";
    $rsSkillCount = sqlsrv_query($db, $strQuery);
    $row = sqlsrv_fetch_array($rsSkillCount);
    $arrUser["SkillsCount"] = $row["CountSkills"];
  }else {
    $arrUser["SkillsCount"] = 0;  
  }
 // Finally if the user is in one department with no default.... set it
 if (isset($arrUser['Departments'])) {
   if ($arrUser['DefaultDepartment'] == 0 && count($arrUser['Departments']) == 1) {
   $intFirstKey = array_keys($arrUser['Departments'])[0];
   }
 }
  if (isset($arrUser['Surname'])) {
    if ($intSaveSession == 1) {
      $strQuery = "SELECT Login, SysAdmin FROM Staff_Web_Config WHERE (Login = N'$strLogin')";
      $rsSysAdmin = sqlsrv_query($db, $strQuery);
      $row = sqlsrv_fetch_array($rsSysAdmin);
      $_SESSION['user']['SysAdmin'] = $row['SysAdmin'];
      $_SESSION['user']['FullName'] = $arrUser['Forename'].' '.$arrUser['Surname'];
      $_SESSION['user']['DefaultDepartment'] = $arrUser["DefaultDepartment"];
      $_SESSION['user']['ScheduAll'] = $arrUser["IsScheduAll"];    
    }
 }
 $pdo = OpenDBLinkA7();
 if(isset($_SESSION['user']['UserID'])) {
    $sql = "exec [dbo].[usp_GET_UserDefaultTeam] ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $_SESSION['user']['UserID'], PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(isset($row['TeamID'])) {
      $arrUser['DefaultTeamID'] = $row['TeamID'];
    }
	$staffDetail_q = "exec [dbo].[usp_get_StaffDetailByUserId] ?";
    $staffDetail_r = $pdo->prepare($staffDetail_q);
    $staffDetail_r->bindParam(1, $_SESSION['user']['UserID'], PDO::PARAM_INT);
    $staffDetail_r->execute();
    $staffDetail_f = $staffDetail_r->fetch(PDO::FETCH_ASSOC);
    $arrUser['PreferredForename'] = $staffDetail_f['PreferredForename'];
    $arrUser['DisplayName'] = $staffDetail_f['DisplayName'];
	if ($intSaveSession == 1)
	{
		$_SESSION['user']['PreferredForename'] = $staffDetail_f['PreferredForename'];
		$_SESSION['user']['DisplayName'] = $staffDetail_f['DisplayName'];
	}
 }
  return ($arrUser);
}

function GetUserFavourites ($strLogin) {
  $db = OpenDatabase();

$strQuery = "SELECT        user_favourites.description, user_favourites.ID, Staff.DepartmentID
             FROM          user_favourites 
             INNER JOIN    user_favourites_staff_link ON user_favourites.ID = user_favourites_staff_link.userfavouriteid 
             INNER JOIN    Staff ON user_favourites_staff_link.StaffNumber = Staff.StaffNumber
             WHERE         (user_favourites.Login = '$strLogin')
             GROUP BY      user_favourites.description, user_favourites.ID, Staff.DepartmentID
             HAVING        (Staff.DepartmentID <> 255)
             ORDER BY      user_favourites.description";
        
  $rsFavs = sqlsrv_query($db, $strQuery);             
  while ($row = sqlsrv_fetch_array($rsFavs)) {              
    $arrFavs[$row["ID"]]['Description'] = $row["description"];
    $arrFavs[$row["ID"]]['DepartmentID'] = $row["DepartmentID"];
  }
  if (isset($arrFavs)) {
    return($arrFavs);
  } 
}   

function GetUserFavouritesInGroup ($intID) {
  $db = OpenDatabase();
  $strQuery = "SELECT         user_favourites_staff_link.staffnumber, Staff.DepartmentID, Staff.Surname + N', ' + Staff.Forename AS FullName
               FROM           user_favourites_staff_link 
               INNER JOIN     Staff ON user_favourites_staff_link.staffnumber = Staff.StaffNumber
               WHERE          (user_favourites_staff_link.userfavouriteid = $intID)
               GROUP BY       user_favourites_staff_link.staffnumber, Staff.DepartmentID, Staff.Surname + N', ' + Staff.Forename
               HAVING         (Staff.DepartmentID <> 255)
               ORDER BY       FullName";
          
  $rsFavs = sqlsrv_query($db, $strQuery);             
  while ($row = sqlsrv_fetch_array($rsFavs)) {              
    $arrFavs['Users'][$row["staffnumber"]] = $row["FullName"];
    $arrFavs['Department'] = $row["DepartmentID"];
  }
  if (isset($arrFavs)) {
    return($arrFavs);
  } 
}

function GetUserFavouritesNotInGroup ($intDepartmentID, $strLogin) {
  $db = OpenDatabase();
  if ($intDepartmentID == 0) {
  
    $strQuery = "SELECT        Staff.DepartmentID, Staff.Surname + N', ' + Staff.Forename AS FullName, Staff.StaffNumber
                 FROM          Staff 
                 INNER JOIN    Staff_Web_Config_Departments_Link ON Staff.DepartmentID = Staff_Web_Config_Departments_Link.DepartmentID
                 GROUP BY      Staff.DepartmentID, Staff.Surname + N', ' + Staff.Forename, Staff.StaffNumber, Staff_Web_Config_Departments_Link.DepartmentID, 
                               Staff_Web_Config_Departments_Link.Login
                 HAVING        (Staff_Web_Config_Departments_Link.Login = N'$strLogin')
                 ORDER BY       FullName";
  }
  else {
  
    $strQuery = "SELECT        DepartmentID, Surname + N', ' + Forename AS FullName, StaffNumber
                 FROM          Staff
                 GROUP BY      DepartmentID, Surname + N', ' + Forename, StaffNumber
                 HAVING        (DepartmentID = $intDepartmentID)
                 ORDER BY      FullName";
  }           
  $rsFavs = sqlsrv_query($db, $strQuery);             
  while ($row = sqlsrv_fetch_array($rsFavs)) {              
    $arrFavs[$row["StaffNumber"]] = $row["FullName"];
  }
  if (isset($arrFavs)) {
    return($arrFavs);
  } 
}  