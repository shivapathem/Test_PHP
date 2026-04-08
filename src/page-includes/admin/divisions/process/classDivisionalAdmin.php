<?php
include_once __DIR__ . '/../../../users/userstab/process/classTeamStaff.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/DBHelper.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/genericfunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/DB_Functions.php';
@session_start();

class ClassDivisionalAdmin
{
    public $userID = '';
    public $userName = '';
    public $moduleName = '';
    public $systemAdmin = '';
    public $cureentaDate = '';

    public function __construct()
    {
        $this->userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
        $this->userName = $_SESSION['user']["FullName"];
        $this->moduleName = 'DivisionalAdmin';
        $this->systemAdmin = isset($_SESSION['user']['SysAdmin']) ? $_SESSION['user']['SysAdmin'] :'0';
        $this->cureentaDate = date("d-m-Y");
    }

    /*
    * @Description : Fetch all of the accessible scheduling team for a user.
	* @access : Public
	* @global : Not Applicable
	* @param  : $intuserid
	* @return : JSON output
    */
    function getDivisionsList($divisionid = 0)
    {
        $pdo = OpenDBLinkA7();
		$whereCond = (!empty($divisionid) && $divisionid > 0) ? 'Where DivisionID = ?' : '';
        $sql = "Select DivisionID,DivisionName,Notes,isActive,EffectedFrom from Divisions $whereCond order by DivisionName";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $divisionid, PDO::PARAM_INT);
        $stmt->execute();
        if ($divisionid == 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return json_encode($result);
    }

    function getLoginList($netLogin = '')
    {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_get_usersByNetlogin] ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $netLogin, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (is_array($result) && count($result) > 0) {
                return json_encode($result);
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function getUserDetail($user_id)
    {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_get_StaffDetailByUserId] ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $user_id, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($result) && count($result) > 0) {
                return json_encode($result);
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function getDivisonalMapList($division_id) {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "SELECT
                    UD_UserID as UserID,
                    ur.UR_UserRoleID as UserRoleID,
                    ur.UR_RoleID as RoleID,
                    ur.UR_DivisionId as DivisionId,
                    UD_DisplayFirstName as Forname,
                    UD_DisplayFirstName as PreferredForename,
                    UD_NetLogin as NetLogin,UD_DisplayLastName as Surname,'' Title,UD_StaffNumber as StaffNumber,
                    UD_DisplayName as DisplayName,
                    UD_InternalEmail as InternalEmail,
                    UD_ExternalEmail as ExternalEmail
                    FROM UserDetails inner join UserRoles ur(nolock) on ur.UR_UserID=UD_UserID
                    inner JOIN REF_Roles RR ON ur.UR_RoleID = RR.RoleID where ur.UR_DivisionId = ?
                    and rr.IsActive=1 and ( rr.RoleName = 'Area Admin'
                    or rr.RoleName = 'Area Viewer')";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $division_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (is_array($result) && count($result) > 0) {
                $resultjson = json_encode($result);
                return $resultjson;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function getDivisionalAdditionalRoleMapList($division_id) {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "SELECT UD_UserID as UserID, ur.UR_RoleID as RoleID, RR.RoleName as RoleName,
             ur.UR_DivisionId as DivisionId, UD_DisplayFirstName as Forname, UD_DisplayFirstName as PreferredForename,
             UD_NetLogin as NetLogin,UD_DisplayLastName as Surname,'' Title,UD_StaffNumber as StaffNumber,
             UD_DisplayName as DisplayName
             FROM UserDetails inner join UserRoles ur(nolock) on ur.UR_UserID=UD_UserID
             inner JOIN REF_Roles RR ON ur.UR_RoleID = RR.RoleID
             where ur.UR_DivisionId = ? and rr.IsActive = 1
             and  rr.RoleName IN ('Area Reports','Facility Administrator') ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $division_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (is_array($result) && count($result) > 0) {
                $resultjson = json_encode($result);
                return $resultjson;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function mapAddionalUser($user_id, $division_id, $role_id, $role_name) {
        try {

            $divisionalAdminId = 0;
            $pdo = OpenDBLinkA7();
            $endDate = '9999-01-01 00:00:00.000';
            $sql = "Insert into UserRoles (UR_UserID, UR_DivisionId, UR_RoleID, UR_StartDate, UR_EndDate,UR_CreatedBy, UR_CreatedDate) VALUES ('".$user_id."', '".$division_id."','" . $role_id . "',CAST(GETDATE() AS date),'".$endDate."', '".$this->userID."', GETDATE())";
            $stmt = $pdo->prepare($sql);
            if($stmt->execute()){
                $strQueryLastId = "SELECT TOP 1 UR_UserRoleID FROM UserRoles (NOLOCK) ORDER BY UR_UserRoleID DESC";
                $stmtLastId = $pdo->prepare($strQueryLastId);
                $stmtLastId->execute();
                $resLastId = $stmtLastId->fetch(PDO::FETCH_ASSOC);
                $divisionalAdminId = $resLastId['UR_UserRoleID'];
                $this->updateHistory($divisionalAdminId);
            }

            $roledetailData = $this->roleDetail($role_id);

            switch($roledetailData['Rolename']){
                 case 'area_viewer':
                     $this->updateSchedulingTeamViewerRole($user_id, $division_id, $role_id);
                    break;
                case 'area_admin':
                    //get facility admin role detail
                     $additonalRoleId = $this->roleDetail('Facility Administrator');
                    $this->changeAdditionalRole($divisionalAdminId, $additonalRoleId['RoleID'], $user_id, $division_id, 'enable', 0);
                    break;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
    * @Description : Remove Diviosnal Admin Mapping
	* @access : Public
	* @global : Not Applicable
	* @param  : $divisionId
    * @param  : $userId
	* @return : none
    */
    function unmapAddionalUser($divisionId, $userId) {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "SELECT * FROM UserRoles UR JOIN REF_Roles rr on rr.RoleID = UR.UR_RoleID WHERE UR.UR_DivisionId = ? AND UR.UR_UserID = ? AND rr.RoleName = 'Area Viewer'";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $divisionId, PDO::PARAM_INT);
            $stmt->bindParam(2, $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "DELETE from UserRoles WHERE UR_DivisionId = ? AND UR_UserID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $divisionId, PDO::PARAM_INT);
            $stmt->bindParam(2, $userId, PDO::PARAM_INT);
            $stmt->execute();

            if(!empty($result)) {
                $this->updateSchedulingTeamViewerRole($userId, $divisionId, 0);
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    /*
    * @Description : check the divisionname is exist or not
	* @access : Public
	* @global : Not Applicable
	* @param  : $divisionid,$divisionname
	* @return : array
    */
    function checkUserMapping($user_id, $division_id) {

        try {
            $pdo = OpenDBLinkA7();
            $sql = "SELECT TOP 1 * FROM UserRoles where UR_UserID = ? and UR_DivisionId = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $division_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
			return !empty($result) ? $result : [];
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
     * @Description : update division map history
     * @access : Public
     * @global : Not Applicable
     * @param  : $divisionalMapId
     * @return : null
     */
    function updateHistory($att_id) {

        $histry_text = 'Updated by '.$this->userName.' on '.$this->cureentaDate;

        $this->moduleName = 'DivisionalAdmin';

        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            $sql = "SELECT id FROM HistoryTypes where HistoryType = ? ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $this->moduleName, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($result) && count($result) > 0 && isset($result['id'])) {
                $sql = "INSERT INTO History (HistoryType, UserID, History, datetime, AttributeID) VALUES ('".$result['id']."', '".$this->userID."', '".$histry_text."', GETDATE(), '".$att_id."')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    function getDivisionsListIdByNetUserRole($strUser, $isDivisionalAdmin, $systemAdmin, $getName = false) {
        $pdo = OpenDBLinkA7();
        $nameQuery = $getName ? ', DivisionName' : '';
        if ($systemAdmin == '1') {
            // Query for system admin
            $strQuery = "SELECT DivisionID " . $nameQuery . "
                         FROM Divisions";
        } elseif ($isDivisionalAdmin == '1') {
            // Query for divisional admin
            $strQuery = "SELECT DivisionID  " . $nameQuery . "
                        FROM Divisions DIV
                        WHERE EXISTS ( SELECT 1
                            FROM UserRoles DA
                            INNER JOIN REF_Roles rr on rr.RoleID = DA.UR_RoleID
                            INNER JOIN UserDetails UD on UD.UD_UserID = DA.UR_UserID
                            WHERE DA.UR_DivisionId = DIV.DivisionID
                            AND UD_NetLogin = ?
                            AND rr.RoleName = 'Area Admin')";
        } else {
            // Query for other users (not admin)
            $strQuery = "SELECT DivisionID " . $nameQuery . "
                         FROM Divisions";
        }

        $stmt = $pdo->prepare($strQuery);

        if ($systemAdmin != '1') {
            $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getDivisionsListBasedOnAreaReportRole() {
        $pdo = OpenDBLinkA7();
        if ($this->systemAdmin  == '1') {
            // Query for system admin
            $strQuery = "SELECT DivisionID, DivisionName
                         FROM Divisions ORDER BY DivisionName";
        } else {
            // Query for divisional admin
            $strQuery = "SELECT DivisionID , DivisionName
                         FROM Divisions DIV
                         WHERE EXISTS (
                             SELECT 1
                             FROM DivisonalAdmin DA
                             JOIN REF_Roles rr on rr.RoleID = DA.RoleId
                             WHERE DA.DivisionID = DIV.DivisionID
                             AND DA.UserID = ?
                             AND rr.RoleName = 'Area Reports'
                         ) ORDER BY DIV.DivisionName";
        }

        $stmt = $pdo->prepare($strQuery);

        if ($this->systemAdmin != '1') {
            $stmt->bindParam(1, $this->userID, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getUsersWithAreaViewerRole($divisionId) {
        $pdo = OpenDBLinkA7();

        // Query for divisional admin
        $strQuery = "SELECT UR.* FROM UserRoles UR JOIN REF_Roles rr on rr.RoleID = UR.UR_RoleID AND UR.UR_DivisionID = ? AND rr.RoleName = 'Area Viewer' ";

		$stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $divisionId, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function changeAreaAdminRole($divisionAdminId, $roleId, $userId, $divisionId, $oldroleId) {
        try {

            $pdo = OpenDBLinkA7();
            $loggedInUser = $this->userID;
            $sql = "UPDATE UserRoles SET UR_RoleID=:roleId, UR_UpdatedBy=:updatedBy, UR_UpdatedDate=GETDATE() WHERE UR_DivisionId=:divisionId AND UR_UserID =:userId AND UR_RoleID=:oldRoleId";
            $stmt = $pdo->prepare(query: $sql);
            $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
            $stmt->bindParam(':divisionId', $divisionId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':oldRoleId', $oldroleId, PDO::PARAM_INT);
            $stmt->bindParam(':updatedBy', $loggedInUser, PDO::PARAM_INT);
            $stmt->execute();
            $this->updateHistory($divisionAdminId);
            $roledetailData = $this->roleDetail($roleId);

            $additonalRoleId = $this->roleDetail('Facility Administrator');
             switch($roledetailData['Rolename']){
                case 'area_viewer':
                     $this->updateSchedulingTeamViewerRole($userId, $divisionId, $roleId);
                    break;
                case 'area_admin':
                    //first remove old record
                    $this->changeAdditionalRole($divisionAdminId, $additonalRoleId['RoleID'], $userId, $divisionId, 'disable', 0);
                    //add new record
                    $this->changeAdditionalRole($divisionAdminId, $additonalRoleId['RoleID'], $userId, $divisionId, 'enable', 0);
                    break;
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function updateSchedulingTeamViewerRole($userId, $divisionId, $roleId, $teamId = null) {
        $pdo = OpenDBLinkA7();
        $loggedInUser = $this->userID;
        $query = 'select [schedulingTeamId], [divisionId] from schedulingTeams where divisionid= :divisionId';
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':divisionId', $divisionId, PDO::PARAM_INT);
        $stmt->execute();
        $teamsBelongingToDivision = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);
        $userDetails = json_decode($this->getUserDetail($userId), true);
        $userTeamDetails = GetStaffOtionsByTeam(!empty($userDetails['NetLogin']) ? $userDetails['NetLogin'] : (!empty($userDetails['UD_NetLogin']) ? $userDetails['UD_NetLogin'] : null));
        $roleDatas = json_decode(getRoleLists('all'), true);
        $scheduledPersonID = GetScheduledPersonIdbyUserId($userId);
        $areaViewerRole = null;
        foreach($roleDatas as $roleData) {
            if($roleData['RoleName'] === 'Area Viewer') {
                $areaViewerRole = $roleData['RoleID'];
            }
        }
        if($roleId == $areaViewerRole) {
            foreach($teamsBelongingToDivision as $teamDivision) {
                $canAddRole = true;
                if(isset($userTeamDetails[$teamDivision['schedulingTeamId']])
                &&
                ($userTeamDetails[$teamDivision['schedulingTeamId']]['isTeamAdmin'] == 1
                ||
                $userTeamDetails[$teamDivision['schedulingTeamId']]['isSchedulingTeamViewer'] == 1
                ||
                $userTeamDetails[$teamDivision['schedulingTeamId']]['isScheduler'] == 1
                ||
                $userTeamDetails[$teamDivision['schedulingTeamId']]['isScheduledPerson'] == 1
                )
                ) {
                    $canAddRole = false;
                }

                if($teamId !== null && $teamDivision['schedulingTeamId'] !== $teamId ) {
                    continue;
                }

                if($canAddRole) {
                    $teamStaff = new classTeamStaff();
                    $teamStaff->setUsersPermissions($teamDivision['schedulingTeamId'], $userId, 6, 1, 'rolepermission', $userDetails['StaffID']);
                    $teamLinks = $this->getExistingTeams($userId, $teamDivision['schedulingTeamId']);
                    if(!empty($teamLinks['teamLinks'])) {
                        $strQuery = "
                        set dateformat ymd
                        UPDATE ScheduledPersonTeam_LINK SET EndDate = '9999-01-01 00:00:00.000' WHERE TeamID=:teamId AND ScheduledPersonID = :scheduledPersonID AND (EndDate > GETDATE() OR EndDate IS NULL)";
                        $stmt = $pdo->prepare($strQuery);
                        $stmt->bindParam(':teamId', $teamLinks['teamLinks']['TeamID'], PDO::PARAM_INT);
                        $stmt->bindParam(':scheduledPersonID', $teamLinks['teamLinks']['ScheduledPersonID'], PDO::PARAM_INT);
						$stmt->execute();
                    } else {
                        $strQuery = "
                        set dateformat ymd
                        INSERT INTO ScheduledPersonTeam_LINK ([ScheduledPersonID]
                        ,[TeamID]
                        ,[IsHomeTeam]
                        ,[SortCode]
                        ,[CreatedBy]
                        ,[CreatedDate]
                        ,[StartDate]
                        ,[EndDate]
                        ,[LastUpdatedBy]
                        ,[LastUpdatedDate]
                        ,[BackgroundColour]
                        ,[fontcolour]
                        ,[IsActive]
                        ,[IsAvailable]
                        ,[isDefault]
                        ,[scheduledType]
                        ,[rota]
                        ,[IsDefaultBGColour]
                        ,[isWhosIn])
                        VALUES (
                        :scheduledPersonID
                        ,:teamId
                        ,0
                        ,null
                        ,:createdBy
                        ,GETDATE()
                        ,:startDate
                        ,:endDate
                        ,:lastUpdatedBy
                        ,GETDATE()
                        ,null
                        ,null
                        ,1
                        ,0
                        ,0
                        ,0
                        ,null
                        ,0
                        ,0
                        )";
                        $stmt = $pdo->prepare($strQuery);
                        $startDate = date("Y-m-d") . ' 00:00:00.000';
                        $endDate = '9999-01-01 00:00:00.000';
                        $stmt->bindParam(':teamId', $teamDivision['schedulingTeamId'], PDO::PARAM_INT);
                        $stmt->bindParam(':scheduledPersonID', $scheduledPersonID, PDO::PARAM_INT);
                        $stmt->bindParam(':createdBy', $loggedInUser, PDO::PARAM_INT);
                        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
                        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
                        $stmt->bindParam(':lastUpdatedBy', $loggedInUser, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }
            }
        }  else {
            foreach($teamsBelongingToDivision as $teamDivision) {
                $canRemoveRole = false;
                if(isset($userTeamDetails[$teamDivision['schedulingTeamId']])
                &&
                $userTeamDetails[$teamDivision['schedulingTeamId']]['isSchedulingTeamViewer'] == 1
                ) {
                    $canRemoveRole = true;
                }
                if($teamId !== null && $teamDivision['schedulingTeamId'] !== $teamId ) {
                    continue;
                }
                if($canRemoveRole) {
                    $endDate = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date("Y-m-d") ) ) )) . ' 00:00:00.000';
                    $strQuery = "
                    set dateformat ymd
                    UPDATE ScheduledPersonTeam_LINK SET EndDate = :endDate, IsActive = 0, LastUpdatedBy = :lastUpdatedBy, LastUpdatedDate = GETDATE() WHERE TeamID=:teamId AND ScheduledPersonID = :scheduledPersonID AND (EndDate > GETDATE() OR EndDate IS NULL)";
                    $stmt = $pdo->prepare($strQuery);
                    $stmt->bindParam(':teamId', $teamDivision['schedulingTeamId'], PDO::PARAM_INT);
                    $stmt->bindParam(':scheduledPersonID', $scheduledPersonID, PDO::PARAM_INT);
                    $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
                    $stmt->bindParam(':lastUpdatedBy', $loggedInUser, PDO::PARAM_INT);
                    $stmt->execute();

                    $strQuery = "
                    set dateformat ymd
                    UPDATE UserRoles SET UR_EndDate = :endDate, UR_UpdatedBy = :lastUpdatedBy, UR_UpdatedDate = GETDATE() WHERE UR_SchedulingTeamID=:teamId AND UR_UserID = :userId AND UR_RoleID = 6 AND (UR_EndDate > GETDATE() OR UR_EndDate IS NULL)";
                    $stmt = $pdo->prepare($strQuery);
                    $stmt->bindParam(':teamId', $teamDivision['schedulingTeamId'], PDO::PARAM_INT);
                    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                    $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
                    $stmt->bindParam(':lastUpdatedBy', $loggedInUser, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }
    }

    function getExistingTeams($userId, $teamId) {
        $pdo = OpenDBLinkA7();
        $strQuery = "select UD_UserID from UserDetails (nolock) WHERE (UD_UserID = :userId)";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $scheduledPersonDetails = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

        $query = 'SELECT * FROM ScheduledPersonTeam_LINK WHERE TeamID=:teamId AND ScheduledPersonID = :scheduledPersonID AND (EndDate > GETDATE() OR EndDate IS NULL)';
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':teamId', $teamId, PDO::PARAM_INT);
        $stmt->bindParam(':scheduledPersonID', $scheduledPersonDetails['UD_UserID'], PDO::PARAM_INT);
        $stmt->execute();
        return ['teamLinks' => $stmt->fetchAll(PDO::FETCH_ASSOC)[0] ?? [], 'scheduledPersonDetails' => $scheduledPersonDetails];
    }

    function changeAdditionalRole($divisionAdminId, $roleId, $userId, $divisionId, $action, $addHistory = 1) {
        try {
            $loggedInUser = $this->userID;
            $pdo = OpenDBLinkA7();
            if($action == 'enable') {
                $endDate = '9999-01-01 00:00:00.000';
                $sql = "
                set dateformat ymd
                INSERT INTO UserRoles (UR_UserID, UR_DivisionId, UR_RoleID, UR_StartDate, UR_EndDate, UR_CreatedBy, UR_CreatedDate) VALUES (:userId, :divisionId, :roleId, CAST(GETDATE() AS date), '".$endDate."', :createdBy, GETDATE())";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':divisionId', $divisionId, PDO::PARAM_INT);
                $stmt->bindParam(':createdBy', $loggedInUser, PDO::PARAM_INT);
            } else {
                $sql = "DELETE from UserRoles WHERE UR_UserID = :userId AND UR_RoleID = :roleId  AND UR_DivisionId = :divisionId ";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
                $stmt->bindParam(':divisionId', $divisionId, PDO::PARAM_INT);
            }
            $stmt->execute();
            if($addHistory == 1) {
                $this->updateHistory($divisionAdminId);
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    /*
   * @Description : get role id and name with id or name .
   * @access : Public
   * @global : Not Applicable
   * @param  : N/A
   * @return : array
   */
    function roleDetail($roleparam){
        $pdo = OpenDBLinkA7();
        $stringquery = is_numeric($roleparam)? "RoleId ='".$roleparam."'" : "RoleName ='".$roleparam."'";
        $strQuery= " SELECT RoleID,LOWER(REPLACE(Rolename,' ','_')) as Rolename FROM REF_Roles (NOLOCK) where $stringquery";
        $stmtLastId = $pdo->prepare($strQuery);
        $stmtLastId->execute();
        $additonalRoleId = $stmtLastId->fetch(PDO::FETCH_ASSOC);
        return $additonalRoleId;
    }
}
