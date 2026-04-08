<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/function-includes/DBHelper.php';

function GetStaffInDepartment ($intDeptID) {

  $db = OpenDatabase();

  $strQuery = "SELECT        Staff.Surname, Staff.Forename, Staff.StaffNumber, Staff.DepartmentID, Staff.Login, Staff.SortCode,
                             ISNULL(Staff_Web_Config_Departments_Link.isAdmin, 0) AS isAdmin,
                             ISNULL(Staff_Web_Config_Departments_Link.isDefault, 0) AS isDefault,
                             ISNULL(Staff_Web_Config_Departments_Link.TextColour, '#000000') AS TextColour,
                             ISNULL(Staff_Web_Config_Departments_Link.ShowReports, 0) AS ShowReports,
                             ISNULL(Staff_Web_Config_Departments_Link.isAppraiser, 0) AS isAppraiser,
                             ISNULL(Staff_Web_Config_Departments_Link.isSkillsAdmin, 0) AS isSkillsAdmin,
                             ISNULL(Staff_Web_Config_Departments_Link.isShiftLeader, 0) AS isShiftLeader,
                             ISNULL(Staff_Web_Config_Departments_Link.isMentor, 0) AS isMentor,
                             ISNULL(Staff_Web_Config_Departments_Link.isManager, 0) AS isManager,
                             ISNULL(Staff_Web_Config_Departments_Link.isScheduler, 0) AS isScheduler,
                             ISNULL(Staff_Web_Config_Departments_Link.HidePhoto, 0) AS HidePhoto,
                             ISNULL(Staff_Web_Config_Departments_Link.HideAllocations, 0) AS HideAllocations,
                             ISNULL(Staff_Web_Config_Departments_Link.HideRota, 1) AS HideRota,
                             ISNULL(Staff_Web_Config_Departments_Link.ContractTypeID, 0) AS ContractTypeID,
                             ISNULL(Staff_Web_Config_Departments_Link.EFT, 1) AS EFT,
                             ISNULL(Staff_Web_Config_Departments_Link.SortCodeMappingID, 0) AS SortCodeMappingID,
                             ISNULL(Staff.BreaksType, 0) AS BreaksType
               From          Staff
               Left Join     Staff_Web_Config_Departments_Link On Staff.Login = Staff_Web_Config_Departments_Link.Login
               And           Staff.DepartmentID = Staff_Web_Config_Departments_Link.DepartmentID
               Where
                             Staff.DepartmentID = $intDeptID And
                             Staff.Login <> ''";

  //echo $strQuery;
  $rsUser = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsUser)) {
    $strLogin = $row["Login"];
    $arrUser[$strLogin]["Surname"] = $row["Surname"];
    $arrUser[$strLogin]["Forename"] = $row["Forename"];
    $arrUser[$strLogin]["StaffNumber"] = $row["StaffNumber"];
    $arrUser[$strLogin]["SortCode"] = $row["SortCode"];
    $arrUser[$strLogin]["TextColour"] = $row["TextColour"];
    $arrUser[$strLogin]["Admin"] = $row["isAdmin"];
    $arrUser[$strLogin]["Default"] = $row["isDefault"];
    $arrUser[$strLogin]["ShowReports"] = $row["ShowReports"];
    $arrUser[$strLogin]["isSkillsAdmin"] = $row["isSkillsAdmin"];
    $arrUser[$strLogin]["isShiftLeader"] = $row["isShiftLeader"];
    $arrUser[$strLogin]["isScheduler"] = $row["isScheduler"];
    $arrUser[$strLogin]["HidePhoto"] = $row["HidePhoto"];
    $arrUser[$strLogin]["isManager"] = $row["isManager"];
    $arrUser[$strLogin]["isAppraiser"] = $row["isAppraiser"];
    $arrUser[$strLogin]["isMentor"] = $row["isMentor"];
    $arrUser[$strLogin]["HideAllocations"] = $row["HideAllocations"];
    $arrUser[$strLogin]["HideRota"] = $row["HideRota"];
    $arrUser[$strLogin]["ContractTypeID"] = $row["ContractTypeID"];
    $arrUser[$strLogin]["EFT"] = $row["EFT"];
    $arrUser[$strLogin]["BreaksType"] = $row["BreaksType"];
    $arrUser[$strLogin]["SortCodeMappingID"] = $row["SortCodeMappingID"];


  }
  if (isset($arrUser)) {
    return($arrUser);
  }
}

function GetStaffNotInDepartment ($intDeptID) {
  $db = OpenDatabase();

  $strQuery = "SELECT      Staff.Login, Staff.Surname + ', ' + Staff.Forename AS Fullname, Staff.StaffNumber, ISNULL(Staff_Web_Config_Departments_Link.isAdmin, 0) AS isAdmin,
                           ISNULL(Staff_Web_Config_Departments_Link.isDefault, 0) AS isDefault, ISNULL(Staff_Web_Config_Departments_Link.ShowReports, 0) AS ShowReports,
                           ISNULL(Staff_Web_Config_Departments_Link.isAppraiser, 0) AS isAppraiser, ISNULL(Staff_Web_Config_Departments_Link.isMentor, 0) AS isMentor,
                           ISNULL(Staff_Web_Config_Departments_Link.isManager, 0) AS isManager, ISNULL(Staff_Web_Config_Departments_Link.isSkillsAdmin, 0) AS isSkillsAdmin,
                           ISNULL(Staff_Web_Config_Departments_Link.isShiftLeader, 0) AS isShiftLeader, ISNULL(Staff_Web_Config_Departments_Link.isScheduler, 0) AS isScheduler,
                           ISNULL(Staff_Web_Config_Departments_Link.HideAllocations, 0) AS HideAllocations, ISNULL(Staff_Web_Config_Departments_Link.HidePhoto, 0) AS HidePhoto
               FROM        Staff
               INNER JOIN  Staff_Web_Config_Departments_Link ON Staff.Login = Staff_Web_Config_Departments_Link.Login
               WHERE       (Staff_Web_Config_Departments_Link.DepartmentID = $intDeptID) AND (Staff.DepartmentID <> $intDeptID) AND (Staff.DepartmentID <> 255) AND (Staff.Login <> N'')
               ORDER BY    Staff.Login";
  //echo $strQuery;
  //die;
  $rsStaff = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsStaff)) {
    $strLogin = strtolower($row['Login']);
    $arrStaff['InDept'][$strLogin]['Name'] = $row['Fullname'];
    $arrStaff['InDept'][$strLogin]["StaffNumber"] = $row["StaffNumber"];
    $arrStaff['InDept'][$strLogin]["ShowReports"] = $row["ShowReports"];
    $arrStaff['InDept'][$strLogin]["Admin"] = $row["isAdmin"];
    $arrStaff['InDept'][$strLogin]["isAppraiser"] = $row["isAppraiser"];
    $arrStaff['InDept'][$strLogin]["isSkillsAdmin"] = $row["isSkillsAdmin"];
    $arrStaff['InDept'][$strLogin]["isShiftLeader"] = $row["isShiftLeader"];
    $arrStaff['InDept'][$strLogin]["isScheduler"] = $row["isScheduler"];
    $arrStaff['InDept'][$strLogin]["Default"] = $row["isDefault"];
    $arrStaff['InDept'][$strLogin]["isManager"] = $row["isManager"];
    $arrStaff['InDept'][$strLogin]["isMentor"] = $row["isMentor"];
  }
  // Do the people not in this depsrtment

  $strQuery = "SELECT        Staff.Surname + N', ' + Staff.Forename AS FullName, Staff.DepartmentID, Departments.FullName AS DepartmentName, LOWER(Staff.Login) AS Login
               FROM          Departments
               INNER JOIN    Staff ON Departments.ID = Staff.DepartmentID
               WHERE         (Staff.DepartmentID <> 255)
               AND           (NOT (LOWER(Staff.Login) IN
                               (SELECT        Staff_1.Login
                               FROM           Staff AS Staff_1
                               LEFT OUTER JOIN Staff_Web_Config_Departments_Link AS Staff_Web_Config_Departments_Link_IN
                               ON  Staff_1.Login = Staff_Web_Config_Departments_Link_IN.Login
                               WHERE   (Staff_1.DepartmentID = $intDeptID) AND (Staff_1.Login <> '')
                               OR (Staff_1.Login <> N'') AND (Staff_Web_Config_Departments_Link_IN.DepartmentID = $intDeptID)))) AND (Staff.Login <> N'')
               ORDER BY FullName";

  $rsNotStaff = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsNotStaff)) {
    $strLogin = $row['Login'];
      $arrStaff['NotInDept'][$strLogin]['Name'] = $row['FullName'];
      if (isset($arrStaff['NotInDept'][$strLogin]['DepartmentName'])) {
        $arrStaff['NotInDept'][$strLogin]['DepartmentName'] = $arrStaff['NotInDept'][$strLogin]['DepartmentName'].'<br>'.$row['DepartmentName'];
      }
      else {
        $arrStaff['NotInDept'][$strLogin]['DepartmentName'] = $row['DepartmentName'];
      }

  }
  if (isset($arrStaff)) {
    return ($arrStaff);
  }
}

function GetGuestsInDepartment ($intDeptID) {

  $db = OpenDatabase();

  $strQuery = "Select
  Staff_Guests.Surname,
  Staff_Guests.Forename,
  Staff_Guests.Login,
  Staff_Web_Config_Departments_Link.DepartmentID,
  IsNull(Staff_Web_Config_Departments_Link.isAdmin, 0) As isAdmin,
  IsNull(Staff_Web_Config_Departments_Link.isDefault, 0) As isDefault,
  IsNull(Staff_Web_Config_Departments_Link.ShowReports, 0) As ShowReports,
  IsNull(Staff_Web_Config_Departments_Link.isAppraiser, 0) As isAppraiser,
  IsNull(Staff_Web_Config_Departments_Link.isSkillsAdmin, 0) As isSkillsAdmin,
  IsNull(Staff_Web_Config_Departments_Link.isShiftLeader, 0) As isShiftLeader,
  IsNull(Staff_Web_Config_Departments_Link.isScheduler, 0) As isScheduler,
  IsNull(Staff_Web_Config_Departments_Link.HidePhoto, 0) As HidePhoto,
  ISNULL(Staff_Web_Config_Departments_Link.isAppraiser, 0) AS isAppraiser,
  ISNULL(Staff_Web_Config_Departments_Link.isMentor, 0) AS isMentor,
  ISNULL(Staff_Web_Config_Departments_Link.isManager, 0) AS isManager,
  IsNull(Staff_Web_Config_Departments_Link.HideAllocations, 0) As HideAllocations
From
  Staff_Guests Inner Join
  Staff_Web_Config_Departments_Link
    On Staff_Guests.Login = Staff_Web_Config_Departments_Link.Login
Where
  Staff_Web_Config_Departments_Link.DepartmentID = $intDeptID
Group By
  Staff_Guests.Surname, Staff_Guests.Forename, Staff_Guests.Login,
  Staff_Web_Config_Departments_Link.DepartmentID,
  IsNull(Staff_Web_Config_Departments_Link.isAdmin, 0),
  IsNull(Staff_Web_Config_Departments_Link.isDefault, 0),
  IsNull(Staff_Web_Config_Departments_Link.ShowReports, 0),
  IsNull(Staff_Web_Config_Departments_Link.isAppraiser, 0),
  IsNull(Staff_Web_Config_Departments_Link.isSkillsAdmin, 0),
  IsNull(Staff_Web_Config_Departments_Link.isShiftLeader, 0),
  IsNull(Staff_Web_Config_Departments_Link.isScheduler, 0),
  IsNull(Staff_Web_Config_Departments_Link.HidePhoto, 0),
  IsNull(Staff_Web_Config_Departments_Link.HideAllocations, 0),
  ISNULL(Staff_Web_Config_Departments_Link.isAppraiser, 0),
  ISNULL(Staff_Web_Config_Departments_Link.isMentor, 0),
  ISNULL(Staff_Web_Config_Departments_Link.isManager, 0)";
  //echo $strQuery;
  $rsUser = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsUser)) {
    $strLogin = $row["Login"];
    $arrUser[$strLogin]["Surname"] = $row["Surname"];
    $arrUser[$strLogin]["Forename"] = $row["Forename"];
    $arrUser[$strLogin]["Admin"] = $row["isAdmin"];
    $arrUser[$strLogin]["Default"] = $row["isDefault"];
    $arrUser[$strLogin]["ShowReports"] = $row["ShowReports"];
    $arrUser[$strLogin]["isAppraiser"] = $row["isAppraiser"];
    $arrUser[$strLogin]["isSkillsAdmin"] = $row["isSkillsAdmin"];
    $arrUser[$strLogin]["isShiftLeader"] = $row["isShiftLeader"];
    $arrUser[$strLogin]["isScheduler"] = $row["isScheduler"];
    $arrUser[$strLogin]["HidePhoto"] = $row["HidePhoto"];
    $arrUser[$strLogin]["HideAllocations"] = $row["HideAllocations"];
    $arrUser[$strLogin]["isManager"] = $row["isManager"];
    $arrUser[$strLogin]["isAppraiser"] = $row["isAppraiser"];
    $arrUser[$strLogin]["isMentor"] = $row["isMentor"];
  }
  if (isset($arrUser)) {
    return($arrUser);
  }
}

function GetGuestsAvailable ($intDeptID) {
  $db = OpenDatabase();

  $strQuery = "SELECT                 Staff_Guests.Surname + ', ' + Staff_Guests.Forename AS Fullname, Staff_Guests.Login, ISNULL(Staff.DepartmentID, 0) AS AllocateDepartment
               FROM                   Staff_Guests
               LEFT OUTER JOIN        Staff ON Staff_Guests.Login = Staff.Login
               LEFT OUTER JOIN        Staff_Web_Config_Departments_Link ON Staff_Guests.Login = Staff_Web_Config_Departments_Link.Login
               WHERE                 (Staff_Guests.Login NOT IN
                                       (SELECT          Login
                                        FROM            Staff_Web_Config_Departments_Link AS Staff_Web_Config_Departments_Link_1
                                        WHERE           (DepartmentID = $intDeptID)))
               ORDER BY               Staff_Guests.Login";
  //echo $strQuery;
  $rsStaff = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsStaff)) {
    $arrGuests[$row["Login"]]['Name'] = $row["Fullname"];

    if ($row["AllocateDepartment"] == 0 || $row["AllocateDepartment"] == 255) {
      $arrGuests[$row["Login"]]['isInAllocate'] = 0;
    }
    else {
      $arrGuests[$row["Login"]]['isInAllocate'] = 1;

    }


    $arrGuests[$row["Login"]]['AllocateDepartment'] = $row["AllocateDepartment"];



  }
  if (isset($arrGuests)) {
    return($arrGuests);
  }
}

function GetAdminDepts ($strLogin, $intSysAdmin = 0) {


  $db = OpenDatabase();
  if ($intSysAdmin == 1) {
   $strQuery = "SELECT        FullName, MaskAfter, MaskType, ConfirmedDays, DailyEditStart, DailyEditEnd, DailyEditPeriod, DailyAutoWeekend, SignInDays, AllowInBuilding, ColourWeek,
                              AllowApplyOvertime, ID AS DepartmentID, LocksEnd, HideDailyView, LocksRollWeek, HasHandovers, HasXmasPoints, HasGridChecks, eMail, WeeksView,
                              isnull(AutoImport, 0) as AutoImport, isnull(AutoLockToday, 0) as AutoLockToday, HasDutiesView, FLMaskAfter, LeaveHasMealDate
                FROM          Departments
                WHERE         retiredFlg = 0
                ORDER BY      Departments.FullName";
  }
  else {
    $strQuery = "SELECT        Staff_Web_Config_Departments_Link.DepartmentID, Departments.FullName, Departments.MaskAfter, Departments.MaskType, Departments.ConfirmedDays, Departments.DailyEditStart,
                               Departments.DailyEditEnd, Departments.DailyEditPeriod, Departments.DailyAutoWeekend, Departments.SignInDays, Departments.AllowInBuilding,
                               Departments.ColourWeek, Departments.AllowApplyOvertime, LocksEnd, HideDailyView, LocksRollWeek, HasHandovers, HasXmasPoints, HasGridChecks, eMail, WeeksView,
                               isnull(AutoImport, 0) as AutoImport, isnull(AutoLockToday, 0) as AutoLockToday, HasDutiesView, FLMaskAfter, LeaveHasMealDate
                 From          Staff_Web_Config_Departments_Link
                 Inner Join    Departments On Staff_Web_Config_Departments_Link.DepartmentID = Departments.ID
                 Where         Staff_Web_Config_Departments_Link.Login = '$strLogin'
                 AND           (Staff_Web_Config_Departments_Link.isAdmin = 1)
                 ORDER BY      Departments.FullName";

  }
//  echo $strQuery;
  $rsDepts = sqlsrv_query($db, $strQuery);
  // Bodge Locks per week is used for the day the daily view becomes hidden

  while ($row = sqlsrv_fetch_array($rsDepts)) {
    $arrDepts[$row["DepartmentID"]]["Description"] = $row["FullName"];
    $arrDepts[$row["DepartmentID"]]["MaskAfter"] = $row["MaskAfter"];
    $arrDepts[$row["DepartmentID"]]["MaskType"] = $row["MaskType"];
    $arrDepts[$row["DepartmentID"]]["ConfirmedDays"] = $row["ConfirmedDays"];
    $arrDepts[$row["DepartmentID"]]["DailyEditPeriod"] = $row["DailyEditPeriod"];
    $arrDepts[$row["DepartmentID"]]["DailyEditStart"] = $row["DailyEditStart"];
    $arrDepts[$row["DepartmentID"]]["DailyEditEnd"] = $row["DailyEditEnd"];
    $arrDepts[$row["DepartmentID"]]["DailyAutoWeekend"] = $row["DailyAutoWeekend"];
    $arrDepts[$row["DepartmentID"]]["SignInDays"] = $row["SignInDays"];
    $arrDepts[$row["DepartmentID"]]["AllowInBuilding"] = $row["AllowInBuilding"];
    $arrDepts[$row["DepartmentID"]]["ColourWeek"] = $row["ColourWeek"];
    $arrDepts[$row["DepartmentID"]]["LocksEnd"] = $row["LocksEnd"];
    $arrDepts[$row["DepartmentID"]]["HideDailyView"] = $row["HideDailyView"];
    $arrDepts[$row["DepartmentID"]]["LocksRollWeek"] = $row["LocksRollWeek"];
    $arrDepts[$row["DepartmentID"]]["AllowApplyOvertime"] = $row["AllowApplyOvertime"];
    $arrDepts[$row["DepartmentID"]]["HasHandovers"] = $row["HasHandovers"];
    $arrDepts[$row["DepartmentID"]]["HasGridChecks"] = $row["HasGridChecks"];
    $arrDepts[$row["DepartmentID"]]["HasXmasPoints"] = $row["HasXmasPoints"];
    $arrDepts[$row["DepartmentID"]]["eMail"] = $row["eMail"];
    $arrDepts[$row["DepartmentID"]]["WeeksView"] = $row["WeeksView"];
    $arrDepts[$row["DepartmentID"]]["AutoImport"] = $row["AutoImport"];
    $arrDepts[$row["DepartmentID"]]["HasDutiesView"] = $row["HasDutiesView"];
    $arrDepts[$row["DepartmentID"]]["FLMaskAfter"] = $row["FLMaskAfter"];
    $arrDepts[$row["DepartmentID"]]["AutoLockToday"] = $row["AutoLockToday"];
    $arrDepts[$row["DepartmentID"]]["LeaveHasMealDate"] = $row['LeaveHasMealDate']->format('Y-m-d');
  }
  if (isset($arrDepts)) {
    return ($arrDepts);

  }
}

function GetUserDepartments ($strLogin) {
  $db = OpenDatabase();

  $strQuery =     "SELECT       Staff.Forename + N' ' + Staff.Surname AS FullName,  Staff.DepartmentID, Departments.FullName as DepartmentName
                   FROM         Staff
                   INNER JOIN   Departments ON Staff.DepartmentID = Departments.ID
                   WHERE       (Staff.Login = N'$strLogin') AND (Staff.DepartmentID <> 255)
                   ORDER BY Departments.FullName";


  $rsDepts = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsDepts)) {

    $arrDepts["UserName"] = $row["FullName"];
    $arrDepts["Departments"][$row["DepartmentID"]] = $row["DepartmentName"];
  }
  if (isset($arrDepts)) {
    return ($arrDepts);

  }
}

function GetGuestUserDepartments ($strLogin) {
  $db = OpenDatabase();

  $strQuery = "Select         Staff_Guests.Forename + ' ' + Staff_Guests.Surname As FullName, Departments.ID as DepartmentID, Departments.FullName As DepartmentName
              From            Staff_Guests
              Left Join       Staff_Web_Config_Departments_Link On Staff_Guests.Login = Staff_Web_Config_Departments_Link.Login
              Left Join       Departments On Staff_Web_Config_Departments_Link.DepartmentID = Departments.ID
              Where           Staff_Guests.Login = '$strLogin'
              Order By        DepartmentName";


  $rsDepts = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsDepts)) {

    $arrDepts["UserName"] = $row["FullName"];
    if (!is_null($row["DepartmentName"])) {
      $arrDepts["Departments"][$row["DepartmentID"]] = $row["DepartmentName"];
    }
  }
  if (isset($arrDepts)) {
    return ($arrDepts);

  }
}

function GetStaffInLeaveGroup($intGroupID) {
  try {
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_StaffInLeaveGroup] ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intGroupID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($result as $row) {
        $strLogin = strtolower($row["login"]);
        $arrUser[$strLogin]["staffLeaveGroupId"] = $row["id"];
        $arrUser[$strLogin]["Name"] = $row["userDisplayName"];
        $arrUser[$strLogin]["Admin"] = $row["Admin"];
        $arrUser[$strLogin]["EFT"] = $row["eft"];
        $arrUser[$strLogin]["EFT1"] = $row["eft1"];
        $arrUser[$strLogin]["EFTSummer"] = $row["eftsummer"];
        $arrUser[$strLogin]["EFTNotes"] = $row["EFTNotes"];
        $arrUser[$strLogin]["Team"] = $row["teamname"];
      }
      if (isset($arrUser)) {
        return($arrUser);
      }
} catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
}
}

function GetStaffNotInLeaveGroup($intGroupID) {
  try {
      // Open the database
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $sql = "exec [dbo].[usp_get_AllocateUsersForLeaveModule] ?";
      $stmt = $pdo->prepare($sql);
      // The parameters
      $stmt->bindParam(1, $intGroupID, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return json_encode($result);

    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }
}

function GetStaffInMyAdminGroups($strUser) {
  $pdo = OpenDBLinkA7();
  try {
    $strQuery = "SELECT CASE WHEN sp.ScheduledPersonID IS NULL THEN  CASE WHEN (ISNULL(sd.UD_DisplayFirstName ,'') = '')
        THEN CASE WHEN (isnull(sd.UD_DisplayLastName,'') = '') THEN  LTRIM(sd.UD_DisplayName) end end
				ELSE (LTRIM(sd.UD_DisplayLastName) + ', ' + LTRIM(sd.UD_DisplayFirstName)) END
				AS FullName,
        Staff_Web_Config_LeaveGroups_Link_1.Login,Staff_Web_Config_LeaveGroups_Link_1.[Admin]
        FROM  Staff_Web_Config_LeaveGroups_Link (nolock)
        INNER JOIN Staff_Web_Config_LeaveGroups_Link (nolock) AS Staff_Web_Config_LeaveGroups_Link_1 ON Staff_Web_Config_LeaveGroups_Link.LeaveGroupID = Staff_Web_Config_LeaveGroups_Link_1.LeaveGroupID
        INNER  JOIN userdetails (nolock) sd on sd.UD_NetLogin = Staff_Web_Config_LeaveGroups_Link_1.[Login]
        inner join ScheduledPersonTeam_LINK SP with(nolock)  on SP.ScheduledPersonID=Sd.UD_UserID
        WHERE  (Staff_Web_Config_LeaveGroups_Link.Login = ? AND Staff_Web_Config_LeaveGroups_Link.IsActive=1 )
        AND  (ISNULL(Staff_Web_Config_LeaveGroups_Link_1.Admin, 0) = 0 AND Staff_Web_Config_LeaveGroups_Link_1.IsActive=1)
        AND Staff_Web_Config_LeaveGroups_Link.Admin >=1
    order by 1";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }

  if (!empty($result)) {
    foreach ( $result as $row ) {
        $strLogin = strtolower($row["Login"]);
        $arrUser[$strLogin]["Name"] = $row["FullName"];
        $arrUser[$strLogin]["Admin"] = $row["Admin"];
    }
  }

  if (isset($arrUser)) {
    return($arrUser);
  }
}

function GetTeamAdmins () {

  $pdo = OpenDBLinkA7();

  $sql = "Select DISTINCT UD_NetLogin NetLogin,UR_UserID UserID,UR_SchedulingTeamID TeamID,
	isnull(UD_PreferredFirstName, UD_DisplayFirstName) +' '+ UD_DisplayLastName AS FullName, schedulingTeamName AS TeamFullName, UD_DisplayLastName LastName
	from UserRoles
	INNER JOIN UserDetails on UD_UserID = UR_UserID
	LEFT JOIN StaffDetails on StaffDetails.StaffID = UD_TeampayStaffID
	INNER JOIN  schedulingTeams ON schedulingTeamId = UR_SchedulingTeamID
	where UR_RoleID = 3 and  schedulingTeams.isActive = 1 and getdate() between UR_StartDate and UR_EndDate
	and UR_UserID not in (select ur_UserId from UserRoles where UR_RoleID = 1
	UNION
    select ur_UserId
    from  UserRoles
    inner join	Divisions D on d.DivisionID = UR_DivisionId
    inner join REF_Roles rr on rr.RoleID = UR_RoleID
    where D.isActive = 1
      and rr.RoleName = 'Area Admin'
  )
	ORDER BY TeamFullName, UD_DisplayLastName";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();

  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $arrUser = array();

  foreach($result as $result_new) {
    if($result_new['FullName']) {
        $arrUser[$result_new['TeamID']]['Team'] = $result_new['TeamFullName'];

        $strLogin = strtolower($result_new['NetLogin']);

        $arrUser[$result_new['TeamID']]['Users'][$strLogin] = $result_new['FullName'];
    }

  }

  return $arrUser;
}

function UpdateArchiveLogin () {
  // Safety function to set all logins to NULL whwre the depratment is 255 - Archive
  $strQuery = "UPDATE        Staff
               SET           Login = NULL
               WHERE         (DepartmentID = 255)";

  $db = OpenDatabase();
  sqlsrv_query($db, $strQuery);
}

/*
*
* @Description : Fetch all of the system admin users staff details.
* @access : Public
* @global : Not Applicable
* @param  : no param
* @return : JSON output OR exception message will echo
*/
function getSystemAdminList(){
  try {
      $status = $returnstring = '';
      $pdo = OpenDBLinkA7();
      $sql = "select ud.UD_DisplayLastName + ', ' + ud.UD_DisplayFirstName AS FullName,
              ud.UD_UserID UserID,
              ud.UD_DisplayFirstName DisplayFirstName,
              ud.UD_EmpNumber EmpNumber,
              ud.UD_NetLogin NetLogin
            FROM UserRoles ur  WITH (NOLOCK)
            INNER JOIN UserDetails ud  WITH (NOLOCK) on ur.UR_UserID = ud.UD_UserID
            INNER JOIN REF_Roles RR ON RR.RoleID = ur.UR_RoleID
            WHERE RR.RoleName = 'System Admin'
              AND CAST(GETDATE() AS date) BETWEEN ur.UR_StartDate AND ur.UR_EndDate
            ORDER BY ud.UD_NetLogin asc
          ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultjson = json_encode($result);
      return $resultjson;

  } catch (PDOException $e) {
      echo $e->getMessage();
  }
}

function getAllSystemAdmin(){
  try {
      // Open the database
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $sql = "SELECT STUFF((SELECT distinct ';' + U.UD_InternalEmail
              FROM UserDetails U  WITH (NOLOCK)
              inner join UserRoles UR  WITH (NOLOCK) on U.UD_UserID =UR.UR_UserID
              INNER JOIN REF_Roles RR ON UR.UR_RoleID = RR.RoleID
              WHERE RR.RoleName='System Admin'
              FOR XML PATH(''), TYPE ).value('.', 'NVARCHAR(MAX)'),1,1,'')";
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $resultjson = json_encode($result);
      return $resultjson;
  } catch (PDOException $e) {
      echo $e->getMessage();
  }
}

function NonScheduledpersondetailsbyID($teamId,$scheduledPersonId){
  try {
      // Open the database
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $sql = "exec [dbo].[usp_get_NonScheduledpersondetailsbyID] ?,?";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
      $stmt->bindParam(2, $scheduledPersonId, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $resultjson = json_encode($result);
      return $resultjson;

  } catch (PDOException $e) {
      echo $e->getMessage();
  }
}

function getRoleNameById($roleId)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            $sql = "SELECT RoleName FROM REF_Roles Where RoleID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,$roleId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode($result['RoleName']);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
/*
*
* @Description : Fetch all of the system admin users staff details.
* @access : Public
* @global : Not Applicable
* @param  : no param
* @return : JSON output OR exception message will echo
*/
function getNotSystemAdminList(){
  try {
      $status = $returnstring = '';
      // Open the database
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $sql = "SELECT UD_NetLogin         NetLogin,
              UD_UserID           UserID,
              UD_DisplayLastName + ', ' + UD_DisplayFirstName AS FullName,
              UD_DisplayFirstName AS Forename,
              UD_DisplayLastName  AS Surname,
              UD_EmpNumber        AS StaffNumber,
              UD_StaffNumber      StaffID,
              UD_InternalEmail    AS InternalEmail
              FROM   UserDetails WITH (nolock)
              WHERE  NOT EXISTS ( SELECT 1
                                    FROM UserRoles UR WITH (nolock)
                          INNER JOIN REF_Roles RR ON RR.RoleID = ur.UR_RoleID
                                  WHERE RR.RoleName = 'System Admin'
                                    AND CAST(GETDATE() AS date) BETWEEN ur.UR_StartDate AND ur.UR_EndDate
                          AND UR_UserID = UD_UserID
                      )
                AND UD_NetLogin IS NOT NULL
              ORDER  BY FullName ASC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultjson = json_encode($result);
      return $resultjson;

  } catch (PDOException $e) {
      echo $e->getMessage();
  }
}

/*
*
* @Description : Fetch all of the system admin users staff details.
* @access : Public
* @global : Not Applicable
* @param  : no param
* @return : JSON output OR exception message will echo
*/
function modSystemAdminList($userid,$action,$currentuserid,$currentuser){
  try {
      $status = $returnstring = '';
      // Open the database
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $sql = "exec [dbo].[usp_mod_usersystemadmin] ?,?,?,?";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(1, $userid, PDO::PARAM_INT);
      $stmt->bindParam(2, $action, PDO::PARAM_INT);
      $stmt->bindParam(3, $currentuserid, PDO::PARAM_INT);
      $stmt->bindParam(4, $currentuser, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $resultjson = json_encode($result);
      return $resultjson;

  } catch (PDOException $e) {
      echo $e->getMessage();
  }
}

  /*
  * @Description : Assigned staff to leave group
	* @access : Public
	* @global : Not Applicable
	* @param  : $intGroupID,$strStaffLogin,$userFullName,$modulename,$currentuserid
	* @return : boolean
  */
function AddStaffInLeaveGroup($intGroupID,$strStaffLogin,$staffsheduledPersonId,$userFullName,$modulename,$currentuserid) {
  try {
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_RED_InsAddStaffToLeaveGroup] ?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intGroupID, PDO::PARAM_INT);
    $stmt->bindParam(2, $strStaffLogin, PDO::PARAM_STR);
    $stmt->bindParam(3, $staffsheduledPersonId, PDO::PARAM_INT);
    $stmt->bindParam(4, $userFullName, PDO::PARAM_STR);
    $stmt->bindParam(5, $currentuserid, PDO::PARAM_INT);
    $stmt->bindParam(6, $modulename, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result['ReturnValue'];
} catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
}
}

 /*
  * @Description : Get Staff leave group details
	* @access : Public
	* @global : Not Applicable
	* @param  : $intGroupID,$strStaffLogin
	* @return : array|void
  */

function GetStaffLeaveGroupDetail($strStaffLogin, $intGroupID) {
    try {
      // Open the database
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $strQuery ="SELECT  swcl.EFT, swcl.EFT1, swcl.EFTSummer, swcl.EFTNotes, lrg.Description, ud.UD_DisplayName  AS FullName FROM Staff_Web_Config_LeaveGroups_Link swcl (NOLOCK) INNER JOIN LeaveRequestGroups lrg (NOLOCK) ON swcl.LeaveGroupID = lrg.ID INNER JOIN UserDetails(nolock) ud on ud.UD_UserID=swcl.ScheduledPersonID WHERE (swcl.Login =?) AND (swcl.LeaveGroupID =?) AND (swcl.IsActive=1)";
      $stmt = $pdo->prepare($strQuery);

      // The parameters
      $stmt->bindParam(1, $strStaffLogin, PDO::PARAM_STR);
      $stmt->bindParam(2, $intGroupID, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
  }
}

/*
  * @Description : update staff leave Eft
	* @access : Public
	* @global : Not Applicable
	* @param  : $intGroupID,$intEFT,$intEFT1,$intEFTSummer,$strStaffLogin,$strEFTNotes,$strHistory,$modulename,$currentuserid
	* @return : boolean
  */
function UpdateStaffLeaveEFT($intGroupID,$intEFT,$intEFT1,$intEFTSummer,$strStaffLogin,$strEFTNotes,$strHistory,$modulename,$currentuserid) {
    try {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_RED_UpdateStaffLeaveEFT] ?,?,?,?,?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $intGroupID, PDO::PARAM_INT);
        $stmt->bindParam(2, $intEFT, PDO::PARAM_INT);
        $stmt->bindParam(3, $intEFT1, PDO::PARAM_INT);
        $stmt->bindParam(4, $intEFTSummer, PDO::PARAM_INT);
        $stmt->bindParam(5, $strStaffLogin, PDO::PARAM_STR);
        $stmt->bindParam(6, $strEFTNotes, PDO::PARAM_STR);
        $stmt->bindParam(7, $strHistory, PDO::PARAM_STR);
        $stmt->bindParam(8, $modulename, PDO::PARAM_STR);
        $stmt->bindParam(9, $currentuserid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result['ReturnValue'];
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }
}

/*
  * @Description : update staff leave group admin
	* @access : Public
	* @global : Not Applicable
	* @param  :$intGroup,$intAction,$fullName,$strLogin,$modulename,$currentuserid
	* @return : boolean
  */
function UpdateStaffLeaveGroupAdmin($intGroup,$intAction,$fullName,$strLogin,$modulename,$currentuserid) {

  try {
      // Open the database
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $sql = "exec [dbo].[usp_RED_UpdateStaffLeaveGroupAdmin] ?,?,?,?,?,?";
      $stmt = $pdo->prepare($sql);
      // The parameters
      $stmt->bindParam(1, $intGroup, PDO::PARAM_INT);
      $stmt->bindParam(2, $intAction, PDO::PARAM_INT);
      $stmt->bindParam(3, $strLogin, PDO::PARAM_STR);
      $stmt->bindParam(4, $fullName, PDO::PARAM_STR);
      $stmt->bindParam(5, $currentuserid, PDO::PARAM_INT);
      $stmt->bindParam(6, $modulename, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['ReturnValue'];
  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
  }
}