<?php
include_once 'DBHelper.php';

function GetSkillsDepartments ($strLogin) {
  $strQuery = "SELECT      Staff_Web_Config_Departments_Link.DepartmentID, Staff_Web_Config_Departments_Link.isDefault, Departments.FullName
               FROM        Staff_Web_Config_Departments_Link
               INNER JOIN  Departments ON Staff_Web_Config_Departments_Link.DepartmentID = Departments.ID
               WHERE       (Staff_Web_Config_Departments_Link.Login = N'$strLogin') AND (Staff_Web_Config_Departments_Link.isAdmin = 1)
               OR          (Staff_Web_Config_Departments_Link.Login = N'$strLogin') AND (Staff_Web_Config_Departments_Link.isSkillsAdmin = 1)
               OR          (Staff_Web_Config_Departments_Link.Login = N'$strLogin') AND (Staff_Web_Config_Departments_Link.isScheduler = 1)
               ORDER BY    Staff_Web_Config_Departments_Link.isDefault DESC, Departments.FullName";
  $db = OpenDatabase();
  $rsUsers = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsUsers)) {
    $arrDepartments[$row["DepartmentID"]]  = $row["FullName"];
  }
  if (isset($arrDepartments)) {
    return ($arrDepartments);
  }
}

function GetSkillsDepartmentsShiftLeader ($strLogin) {

  $pdo = OpenDBLinkA7();
  $strQuery = "SELECT st.schedulingTeamId, st.schedulingTeamName
                from  Users U
                    INNER JOIN UserTeamRole_LINK UTL on UTL.UserID = U.UserID
                    INNER JOIN schedulingTeams as st ON st.schedulingTeamId = UTL.TeamID
                where U.NetLogin = ? AND UTL.RoleID = '14'
                and UTL.StartDate <= convert(datetime,convert(varchar(10),getdate(),110),110) and isnull(UTL.EndDate,'9999-12-01') >= convert(datetime,convert(varchar(10),getdate(),110),110)";

  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $strLogin, PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $return_array = [];

  foreach($result as $value) {
    $return_array[$value['schedulingTeamId']] = $value['schedulingTeamName'];
  }

  return $return_array;
}

function StaffWhoCanDoProg ($progid) {

  $strQuery = "SELECT             Staff.Surname + N', ' + Staff.Forename AS FullName, Staff.id, Staff.StaffNumber
               FROM               Staff
               LEFT OUTER JOIN    skills_programmes_staff_link ON Staff.ID = skills_programmes_staff_link.staff_id
               WHERE              (skills_programmes_staff_link.programmes_id = $progid)
               ORDER BY           FullName";

  //echo $strQuery;
  $db = OpenDatabase();
  $trained = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($trained)) {
    $arrtrained[$row['id']]['staffnumber'] = $row['StaffNumber'];
    $arrtrained[$row['id']]['name'] = $row['FullName'];
  }
  //echo '<pre>';
  //print_r($arrtrained);
  if (isset($arrtrained)) {
    return $arrtrained;
  }
}

function StaffNumbersWhoCanDoProg ($progid) {

  $strQuery = "SELECT           Staff.StaffNumber
            FROM                Staff
            LEFT OUTER JOIN     skills_programmes_staff_link ON Staff.ID = skills_programmes_staff_link.staff_id
            WHERE               (skills_programmes_staff_link.programmes_id = $progid)";

  //echo $strQuery;
  $db = OpenDatabase();
  $rsCanDo = sqlsrv_query($db, $strQuery);
  while ($row = sqlsrv_fetch_array($rsCanDo)) {
    $arrCanDo[$row['StaffNumber']] = $row['StaffNumber'];
  }
  if (isset($arrCanDo)) {
    return $arrCanDo;
  }
}

function ListAllProgrammes ($teamId) {

  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT        ID, programmename
               FROM          skills_programmes
               WHERE         (TeamID = ? )
               ORDER BY      programmename";

  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $return_array = [];

  foreach($result as $value) {
    $return_array[$value['ID']] = $value['programmename'];
  }

  return $return_array;
}

function ListAllStaff ($teamID) {

  $pdo = OpenDBLinkA7();

$strQuery = "exec [dbo].[usp_get_AllPeoplebyTeamIdWithStaffNumber] ?";


 $stmt = $pdo->prepare($strQuery);
 $stmt->bindParam(1, $teamID, PDO::PARAM_INT);
 $stmt->execute();
 $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

 $return_array = [];

 foreach($result as $value) {
   $return_array[$value['ScheduledPersonID']]['staffnumber'] = $value['StaffNumber'];
   $return_array[$value['ScheduledPersonID']]['name'] = $value['Fullname'];
 }

 return $return_array;
}

function listallduties ($intDepartmentID) {
//session_start();
  $bst = $_SESSION['bst'];


  $strQuery = "SELECT skills_duties.duty + N' (' + skills_duties.description + N')' AS dutydesc, skills_duties.id, skills_duties_days.dotw,
               skills_duties.BST, skills_duties.GMT, skills_duties.GMT, skills_duties.DurationWith
               FROM skills_duties
               LEFT OUTER JOIN skills_duties_days ON skills_duties.id = skills_duties_days.duty_id";
               if ($bst == 1) {
                 $strQuery.= " WHERE  (skills_duties.BST = 1)";
               }
               else {
                $strQuery.= " WHERE  (skills_duties.GMT = 1)";
               }
               $strQuery.= " AND DepartmentID = $intDepartmentID
               ORDER BY dutydesc";

   //echo "\n <br> q: ", $strQuery;
  $db = OpenDatabase();
  $rsAllDuties = sqlsrv_query($db, $strQuery);

  while ($row = sqlsrv_fetch_array($rsAllDuties)) {
    $arrDuties[$row['id']]['dutyname'] = $row['dutydesc'];
    $arrDuties[$row['id']]['GMT'] = $row['GMT'];
    $arrDuties[$row['id']]['BST'] = $row['BST'];
    $arrDuties[$row['id']]['days'][$row['dotw']] = 1;
    $arrDuties[$row['id']]['duration'] = $row['DurationWith'];
  }
  if (isset($arrDuties)) {
    return $arrDuties;
  }
}

function ProgrammesAssigned ($id) {
  $db = OpenDatabase();
  $strQuery = "SELECT skills_programmes.id, skills_programmes.programmename
            FROM skills_duties
            INNER JOIN skills_duties_programmes_link ON skills_duties.id = skills_duties_programmes_link.duties_id
            INNER JOIN skills_programmes ON skills_duties_programmes_link.programmes_id = skills_programmes.id
            WHERE  (skills_duties.id = $id)
            ORDER BY skills_programmes.programmename";

  $rsProgs = sqlsrv_query($db, $strQuery);

  while ($row = sqlsrv_fetch_array($rsProgs)) {
    $arrProgs[$row['id']] = $row['programmename'];
  }
  if (isset($arrProgs)) {
    return $arrProgs;
  }
}

function GetStaffCanDoDuty($intID) {
  $db = OpenDatabase();
  // Now get the people who can do this duty
  $strQuery = "SELECT          Staff.Surname+ ',' + Staff.Forename as FullName, Staff.StaffNumber
            FROM            skills_programmes_staff_link
            INNER JOIN      skills_duties
            INNER JOIN      skills_duties_programmes_link ON skills_duties.ID = skills_duties_programmes_link.duties_id ON  skills_programmes_staff_link.programmes_id = skills_duties_programmes_link.programmes_id
            INNER JOIN      Staff ON skills_programmes_staff_link.staff_id = Staff.ID
            WHERE           (skills_duties.ID = $intID)
            GROUP BY        Staff.Surname, Staff.Forename, Staff.StaffNumber
            HAVING          (COUNT(*) =
                             (SELECT        COUNT(skills_programmes.programmename) AS countfield
                               FROM         skills_duties_programmes_link AS skills_duties_programmes_link_1
                               INNER JOIN   skills_programmes ON skills_duties_programmes_link_1.programmes_id = skills_programmes.ID
                               INNER JOIN   skills_duties AS skills_duties_1 ON skills_duties_programmes_link_1.duties_id = skills_duties_1.ID
                               WHERE        (skills_duties_1.ID = $intID)))
            ORDER BY        Staff.Surname, Staff.Forename";

 //  echo "\n <br>35 q: ", $strQuery;
  $rsStaffCanDo = sqlsrv_query($db, $strQuery);
  while($row = sqlsrv_fetch_array($rsStaffCanDo)){
    $arrCanDoDuty[$row['StaffNumber']] = $row['FullName'];
  }
  if (isset($arrCanDoDuty)) {
    return ($arrCanDoDuty);
  }
}

function GetStaffProgsCanDo($strStaffNumber) {

  $strQuery = "SELECT        skills_programmes.programmename, skills_programmes.id
               FROM          skills_programmes_staff_link
               INNER JOIN    skills_programmes ON skills_programmes_staff_link.programmes_id = skills_programmes.ID
               INNER JOIN    Staff ON skills_programmes_staff_link.staff_id = Staff.ID
               WHERE         (Staff.StaffNumber = N'$strStaffNumber')
               ORDER BY      skills_programmes.programmename";

  $db = OpenDatabase();
  $progs = sqlsrv_query($db, $strQuery);

  while ($row = sqlsrv_fetch_array($progs)) {
    $arrprogs[$row['id']] = $row['programmename'];
  }
  if (isset($arrprogs)) {
    return $arrprogs;
  }
}

function listalldutieswithprogrammes ($bst = 1, $intDepartmentID = 0) {
  $db = OpenDatabase();

  $strQuery = "SELECT             skills_duties.duty, skills_duties.description, skills_duties.id, skills_duties.DepartmentID, skills_programmes.ID AS programmes_id
               FROM               skills_programmes
               INNER JOIN         skills_duties_programmes_link ON skills_programmes.ID = skills_duties_programmes_link.programmes_id
               RIGHT OUTER JOIN   skills_duties ON skills_duties_programmes_link.duties_id = skills_duties.ID";
               if ($bst == 1) {
  $strQuery.= "  WHERE              (skills_duties.BST = 1)";
               }
               else {
  $strQuery.= "  WHERE              (skills_duties.GMT = 1)";
               }


  $strQuery.= " AND                (skills_duties.DepartmentID = $intDepartmentID)
                ORDER BY           skills_duties.duty";

   //echo "\n <br> q: ", $strQuery;
  $duties = sqlsrv_query($db, $strQuery);
  //echo $strQuery;
  while ($row = sqlsrv_fetch_array($duties)) {
    $arrduties[$row['id']]['duty'] = $row['duty'];
    $arrduties[$row['id']]['dutyname'] = $row['duty'].' '.$row['description'];
    $arrduties[$row['id']]['progs'][$row['programmes_id']] = 1;
  }
  if (isset($arrduties)) {
    return $arrduties;
  }
}

function GetAllDutiesAndPeople($intDepartmentID) {

  $db = OpenDatabase();
  // Get all the skills and associated programmes
  $strQuery = "Select
  skills_duties.duty,
  skills_duties.ID,
  skills_duties.BST,
  skills_duties.GMT,
  skills_duties_days.dotw,
  skills_programmes.programmename,
  skills_programmes.ID As ProgID
From
  skills_duties Inner Join
  skills_duties_days
    On skills_duties.ID = skills_duties_days.duty_id Inner Join
  skills_duties_programmes_link
    On skills_duties.ID = skills_duties_programmes_link.duties_id Inner Join
  skills_programmes
    On skills_duties_programmes_link.programmes_id = skills_programmes.ID
Where
  skills_duties.DepartmentID = $intDepartmentID
Group By
  skills_duties.duty, skills_duties.ID, skills_duties.BST, skills_duties.GMT,
  skills_duties_days.dotw, skills_programmes.programmename, skills_programmes.ID
Order By
  skills_duties.duty";


  $duties = sqlsrv_query($db, $strQuery);

  while ($row = sqlsrv_fetch_array($duties)) {
   if ($row['BST'] == 1) {
     $arrDuties[$row['duty']][1][$row['dotw']]['progs'][$row['ProgID']]= $row['programmename'];
   }
   if ($row['GMT'] == 1) {
     $arrDuties[$row['duty']][0][$row['dotw']]['progs'][$row['ProgID']]= $row['programmename'];
   }
  }
  // Get the staff and programmes

  $strQuery = "SELECT        Staff.StaffNumber, skills_programmes.ID AS ProgID
               FROM          Staff INNER JOIN
                         skills_programmes_staff_link ON Staff.ID = skills_programmes_staff_link.staff_id INNER JOIN
                         skills_programmes ON skills_programmes_staff_link.programmes_id = skills_programmes.ID
WHERE        (Staff.DepartmentID = $intDepartmentID)
ORDER BY Staff.StaffNumber";

  $rsStaff = sqlsrv_query($db, $strQuery);

  while ($row = sqlsrv_fetch_array($rsStaff)) {
    $arrStaff[$row['StaffNumber']]['progs'][$row['ProgID']]= 1;
  }
  // Now add the people....

  //print_r($arrStaff);

  foreach ($arrDuties as $DutyDesc => $arrGmtBst) {
    foreach ($arrGmtBst as $GmtBst => $arrDoTW) {
      foreach ($arrDoTW as $DoTw => $arrProgs) {
        // Now loop through the staff and see if they can do all the oprogs....
        foreach ($arrStaff as $sn => $arrStaffProgs){
          $canDo = 1;
          foreach ($arrProgs['progs'] as $progID => $ProgName) {
            if (!isset($arrStaffProgs['progs'][$progID])) {
              $canDo = 0;
            }
          }
          if ($canDo == 1) {

            $arrDuties[$DutyDesc][$GmtBst][$DoTw]['staff'][$sn] = 1;
          }
        }
      }
    }
  }
  return ($arrDuties);
}

function CountJobSkillsInDepartment ($TeamID) {

  $strQuery = "SELECT      COUNT(ID) AS CountSkills
               FROM        skills_programmes
               WHERE      (TeamID = $TeamID)";

  $db = OpenDatabase();
  $rsProgrammes = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsProgrammes);
  return ($row['CountSkills']);
}

?>