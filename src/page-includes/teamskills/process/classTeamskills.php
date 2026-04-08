<?php
/*
* @Description : list all fuctions for skills .
* @access : Public

*/
include_once __DIR__.'../../../../function-includes/DBHelper.php';
include_once __DIR__.'../../../../function-includes/helpers.php';

class classTeamskills
{
    /* All Skill based on Team Id */
    function ListAllProgrammes($teamID)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GET_ListAllProgrammesByTeamId] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $teamID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $resultjson = json_encode($result);
            return $resultjson;
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    /*   Now get the people who can do this duty by Staff Number */
    function GetStaffProgsCanDo($strStaffNumber,$teamId=0)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GET_GetStaffProgsCanDoByStaffnumber] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $strStaffNumber, PDO::PARAM_STR);
            $stmt->bindParam(2, $teamId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $resultjson = json_encode($result);
            return $resultjson;
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Now get all the duties with can do/ can't do in by Team ID
    function ListAllDutiesWithProgrammes($bst, $teamID,$strStaffNumber)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GET_ListAllDutiesWithProgrammes] ?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $bst, PDO::PARAM_INT);
            $stmt->bindParam(2, $teamID, PDO::PARAM_INT);
			$stmt->bindParam(3, $strStaffNumber, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // get duty amd dutyname with to show can do /can't do
            foreach ($result as $row => $duties) {
                $arrduties[$duties['ID']]['duty'] = $duties['duty'];
                $arrduties[$duties['ID']]['dutyname'] = $duties['duty'] . ' ' . $duties['description'];
				$arrduties[$duties['ID']]['StaffDutyFlag'] = $duties['StaffDutyFlag'] ;
            }
            if (isset($arrduties)) {
                return $resultjson = json_encode($arrduties);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Get List of staff for specific TeamID
    function ListAllStaff($intTeamID)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GET_StaffDetailsByTeamID] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row => $data) {
                $arrstaff[$row]['staffnumber'] = $data['StaffNumber'];
                $arrstaff[$row]['StaffID'] = $data['StaffID'];
                $arrstaff[$row]['name'] = $data['DisplayName'];
                if(!empty($data['FullName'])){
                    $arrstaff[$row]['name'] = $data['FullName'];
                }
            }
            if (isset($arrstaff)) {
                return $resultjson = json_encode($arrstaff);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Link Scheduled Person with Program
    function AddPersonWithProgram($progid, $staffid)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "INSERT INTO skills_programmes_staff_link (programmes_id, UserID)
            VALUES ( ?, ?)";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $progid, PDO::PARAM_INT);
            $stmt->bindParam(2, $staffid, PDO::PARAM_STR);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Unlink Scheduled Person with Program
    function DeletePersonWithProgram($progid, $staffid)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "DELETE FROM skills_programmes_staff_link
            WHERE (programmes_id = ?)
            AND (UserID = ?)";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $progid, PDO::PARAM_INT);
            $stmt->bindParam(2, $staffid, PDO::PARAM_STR);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Add Skill Program with Program with Team
    function AddProgramWithTeam($prog, $intTeamID)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_LinkSkillProgramWithTeam] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $prog, PDO::PARAM_STR);
            $stmt->bindParam(2, $intTeamID, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Update Skill Program with Program with Team
    function UpdateProgramWithTeam($prog, $id)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_UpdateSkillProgramWithTeam] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $prog, PDO::PARAM_STR);
            $stmt->bindParam(2, $id, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Get Program Name by Id
    function GetProgramById($id)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GET_ProgrammeById] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultjson = json_encode($result);
            return $resultjson;
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Now get all the duties with can do/ can't do in by Prog ID
    function StaffWhoCanDoProg($progid)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "SELECT distinct ud.UD_DisplayName AS userDisplayName, ud.UD_UserID StaffID, ud.UD_StaffNumber StaffNumber
            FROM  UserDetails ud(nolock) --StaffDetails sd
            LEFT OUTER JOIN  skills_programmes_staff_link spsl ON ud.UD_UserID = spsl.UserID
            --LEFT OUTER JOIN ScheduledPeople sp on sp.StaffDetailsID = spsl.staff_id
            LEFT OUTER JOIN ScheduledPersonTeam_LINK spt on spt.ScheduledPersonID = ud.UD_UserID and spt.scheduledType = 1
            WHERE (spsl.programmes_id=?) ORDER BY userDisplayName";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $progid, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row => $data) {
                $arrtrained[$row]['staffnumber'] = $data['StaffNumber'];
                $arrtrained[$row]['StaffID'] = $data['StaffID'];
                $arrtrained[$row]['name'] = $data['userDisplayName'];
            }
            if (isset($arrtrained)) {
                return $resultjson = json_encode($arrtrained);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // Now get all the duties with can do/ can't do day wise by Team ID
    function ListAllDuties($bst, $teamID)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GET_ListAllDutiesWithProgrammesDaywise] ?,?";
            $stmt = $pdo->prepare($sql);
            //The parameters
            $stmt->bindParam(1, $bst, PDO::PARAM_INT);
            $stmt->bindParam(2, $teamID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // get duty amd dutyname with to show can do /can't do
             foreach ($result as $row => $duties) {
                $arrDuties[$duties['id']]['id'] = $duties['id'];
                $arrDuties[$duties['id']]['dutyname'] = $duties['dutydesc'];
                $arrDuties[$duties['id']]['GMT'] = $duties['GMT'];
                $arrDuties[$duties['id']]['BST'] = $duties['BST'];
                $arrDuties[$duties['id']]['days'][$duties['dotw']] = 1;
                $arrDuties[$duties['id']]['duration'] = $duties['DurationWith'];
            }
            if (isset($arrDuties)) {
                return $resultjson = json_encode($arrDuties);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //get List of All Programmes By DutyId.
    function ProgrammesAssigned($id)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GET_ListSkillProgramByDutyId] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // get duty amd dutyname with to show can do /can't do
            foreach ($result as $row => $duties) {
                $arrProgs[$row]['programmename'] = $duties['programmename'];
                $arrProgs[$row]['id'] = $duties['id'];
            }
            if (isset($arrProgs)) {
                return $resultjson = json_encode($arrProgs);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // update duties day.
    function UpdateToggleDay($intDutyID, $intDay)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_UpdateDutiesDay] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $intDay, PDO::PARAM_INT);
			$stmt->bindParam(2, $intDutyID, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //Add Skill duties.
    function AddSkillDuties($duty, $description, $gmt, $bst, $intDuration, $intTeamID)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_AddSkillDuties] ?,?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $duty, PDO::PARAM_STR);
            $stmt->bindParam(2, $description, PDO::PARAM_STR);
            $stmt->bindParam(3, $gmt, PDO::PARAM_INT);
            $stmt->bindParam(4, $bst, PDO::PARAM_INT);
            $stmt->bindParam(5, $intDuration, PDO::PARAM_INT);
            $stmt->bindParam(6, $intTeamID, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //Update Skill duties.
    function UpdateSkillDuties($id, $duty, $description, $intDuration)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_UpdateSkillDuties] ?,?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $duty, PDO::PARAM_STR);
            $stmt->bindParam(2, $description, PDO::PARAM_STR);
            $stmt->bindParam(3, $intDuration, PDO::PARAM_INT);
            $stmt->bindParam(4, $id, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //Get List of Skill duties By ID
    function ListSkillDutiesByID($id)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_ListSkillDutiesById] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultjson = json_encode($result);
            return $resultjson;
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //Delete Skill duties Program Link.
    function DeteteSkillDutiesProgramLink($dutyid, $progid)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_DelSkillDutiesProgrammesLink] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $dutyid, PDO::PARAM_INT);
            $stmt->bindParam(2, $progid, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //Add Skill duties Program Link.
    function AddSkillDutiesProgramLink($dutyid, $progid)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_AddSkillDutiesProgrammesLink] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $dutyid, PDO::PARAM_INT);
            $stmt->bindParam(2, $progid, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //Get Staff can do duty ByDuty Id
    function GetStaffCanDoDuty($intID)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GetStaffCanDoDutyByDutyId] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $intID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row => $skillduties) {
                $arrCanDoDuty[$row]['FullName'] = $skillduties['FullName'];
                $arrCanDoDuty[$row]['ScheduledPersonID'] = $skillduties['ScheduledPersonID'];
            }
            if (isset($arrCanDoDuty)) {
                return $resultjson = json_encode($arrCanDoDuty);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //Delete Skill Program.
    function DeleteSkillProgram($id)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_DeleteSkillProgram] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    //Delete Skill Program Staff Link.
    function DeleteSkillProgramStaffLink($id)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_DeleteSkillProgramStaffLink] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    // update duties Zone By ID.
    function UpdateToggleByID($intID, $intGMTBST)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_UpdateDutiesByID] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $intID, PDO::PARAM_INT);
            $stmt->bindParam(2, $intGMTBST, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

	//Delete Skill Duty.
    function DeleteSkillDuty($id)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_DeleteSkillDutyById] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

	//Delete Skill Duty Program Link.
	function DeleteSkillDutyProgramLink($id)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_DeleteSkillDutyProgramLinkById] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            return (bool)$stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

	//Get Team for shiftleader.
	function GetSkillsDepartmentsShiftLeader($strLogin)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
			$roleid=10;
			$sql = "exec [dbo].[usp_get_TeamsForShiftleaderSkill] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
			$stmt->bindParam(1, $strLogin, PDO::PARAM_STR);
			$stmt->bindParam(2, $roleid, PDO::PARAM_INT);
			$stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row => $value) {
                 $return_array[$value['schedulingTeamId']] = $value['schedulingTeamName'];
            }
            if (isset($return_array)) {
                return $resultjson = json_encode($return_array);
            } else {
                return false;
            }
        } catch (PDOException $e) {
			logger()->critical('db error', (array) $e);
        }
    }
}