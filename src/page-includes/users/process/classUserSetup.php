<?php

include_once __DIR__.'/../../../function-includes/DBHelper.php';
class classUserSetup
{
    public $userID = '';
    public $staffID = '';
    public $netLogin = '';
    public $isactive = 0;

    public function __construct()
    {
        // We will delete comment line below later when session related issue is fully verified
        //$this->userID = $_SESSION['user']["UserID"] == '' ? 0 : $_SESSION['user']["UserID"];
        $this->userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] :($_COOKIE['editWeeklyUserId'] ?? '');
        $this->staffID = !isset($_SESSION['user']['StaffID']) || $_SESSION['user']["StaffID"] == '' ? 0 : $_SESSION['user']["StaffID"];
        $this->netLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] :($_COOKIE['editWeeklyUserNetLogin'] ?? '');
        $this->isactive = 1;
    }


    /*
   * @Description : Fetch the staffdeatils By ID.
   * @access : Public
   * @global : Not Applicable
   * @param  : $intuserid
   * @return : JSON output
   */
    function getUserSetupByIdNetlogin($type='usersetup')
    {
        $pdo = OpenDBLinkA7();
        //Fetch the Roles
        $refRolesResult = json_decode($this->getAllRoles(), true);
        $isSysAdmin = $this->getSysAdmin();
        $sql = "exec [dbo].[usp_get_MySetUp] ?, ?, 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
        $stmt->bindParam(2, $this->netLogin, PDO::PARAM_STR);
        $stmt->execute();
        $resultUserSetup = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$resultdata['isSystemAdmin'] = $isSysAdmin;//overright current var b'coz sp is not returning proper flag
        if($type=='usersetup'){
            if (empty($resultUserSetup)) {
                $resultdata= '';
            } else {
                $resultdata = $this->dataFormatDatatable($resultUserSetup, $isSysAdmin);
            }
        }else{
            if (empty($resultUserSetup)) {
                $resultdata[] =array();
            } else {
                $resultdata = $this->dataFormatForMenu($resultUserSetup, $refRolesResult);
            }
        }

        return $resultjson = json_encode($resultdata);
    }

    /*
   * @Description : Fetch facility administrator roles details.
   */
    function facilityAdministratorArea() {
        $pdo = OpenDBLinkA7();
        $sql = "select [Divisions].*, [REF_Roles].[RoleName] from [divisions] (NOLOCK)
        inner join [UserRoles] on [divisions].[DivisionID] = [UserRoles].[UR_DivisionId] 
        inner join [REF_Roles] on [REF_Roles].[RoleID] = [UserRoles].[UR_RoleID] 
        where REF_Roles.RoleName = 'Facility Administrator' AND  [UserRoles].[UR_UserID] = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
   * @Description : Fetch the Ref_ROLES .
   * @access : Public
   * @global : Not Applicable
   * @param  : N/A
   * @return : JSON output
   */
    function getAllRoles()
    {

        try {
            // Open the database
            $pdo = OpenDBLinkA7();

            $sql = "SELECT * FROM REF_Roles Where isActive = ?  order by isAdditional,isSequence,RoleID ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $this->isactive, PDO::PARAM_INT);
            $stmt->execute();
            $resultRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultRolesJson = json_encode($resultRoles);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
    * @Description : Syst admin .
	* @access : Public
	* @global : Not Applicable
	* @param  : N/A
	* @return : JSON output
    */
    function getSysAdmin()
    {

        try {
            // Open the database
            $pdo = OpenDBLinkA7();

            $sql = "select isnull(
			(select RoleId from UserRoles ur 
			inner join REF_Roles rr on rr.RoleID = ur.UR_RoleID and rr.RoleName='System Admin'
			Where UR_UserId = ? AND getdate() BETWEEN UR_StartDate AND UR_EndDate)
			,0) as RoleId";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
            $stmt->execute();
            $isSysAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($isSysAdmin)) {
                return $isSysAdmin['RoleId'];
            } else {
                return 0;
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /*
   * @Description : Set defulat team .
   * @access : Public
   * @global : Not Applicable
   * @param  : N/A
   * @return : JSON output
   */
    function setDefaultTeam($personId, $default, $teamid)
    {
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_mod_setDefaultTeamRecord] ?,?,?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $personId, PDO::PARAM_INT);
        $stmt->bindParam(2, $default, PDO::PARAM_INT);
        $stmt->bindParam(3, $teamid, PDO::PARAM_INT);
        $stmt->bindParam(4, $this->userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;
    }

    /*
   * @Description : Response result to getUserSetupByIdNetlogin functiomn in normal array .
   * @access : Public
   * @global : Not Applicable
   * @param  : N/A
   * @return : Array output
   */

    function dataFormatForMenu($resultUserSetup,$refRolesResult)
    {


        foreach ($refRolesResult as $roleValue) {
            $newRoleNameSet = "is".str_replace(' ', '', $roleValue['RoleName']);
                $data[$newRoleNameSet] = 0;
        }
        $data['DivisionalAdmin'] =$data['HomeTeam'] = 0;
        $data['DefaultTeam'] =$data['HasXmasPoints'] = $data['HasHandovers']= $data['isAdmin'] = $data['HasTeamAdmin'] = $data['isScheduledPerson'] =0;

        $teamdata = array();
        foreach ($resultUserSetup as $value) {
            if($value['Home Team'] == 1){
                $data['HomeTeam'] = 1;
            }
            $schedulingTeamId = $value['schedulingTeamId'];
            if (!isset($teamdata[$schedulingTeamId])) {
                $value['showJobsInWeeklyView'] = isset($value['showJobsInWeeklyView']) ? $value['showJobsInWeeklyView'] : 0;
                $value['IsCreateDutyFromRota'] = isset($value['IsCreateDutyFromRota']) ? $value['IsCreateDutyFromRota'] : 0;

                $teamdata[$schedulingTeamId]['schedulingTeamName'] = $value['schedulingTeamName'] ?? '';
                $teamdata[$schedulingTeamId]['hasXmasPoints'] = $value['hasXmasPoints'];
                $teamdata[$schedulingTeamId]['hasHandovers'] = $value['hasHandovers'];
                $teamdata[$schedulingTeamId]['HasDutiesView'] = $value['showProductionView'];
                $teamdata[$schedulingTeamId]['HasJobsInWeeklyView'] = $value['showJobsInWeeklyView'];
                $teamdata[$schedulingTeamId]['IsCreateDutyFromRota'] = $value['IsCreateDutyFromRota'];
                $teamdata[$schedulingTeamId]['ShowEditYearly'] = $value['IsShowEditYearly'];
                $teamdata[$schedulingTeamId]['isHomeTeam'] = $value['Home Team'] == 1 ? 1 : 0;
                if($value['hasXmasPoints'] == 1){
                    $data['HasXmasPoints'] =1;
                }
                if($value['hasHandovers'] == 1){
                    $data['HasHandovers'] =1;
                }
               if( ($data['DefaultTeam'] == 0) && ($value['isDefault'] == 1)){
                $data['DefaultTeam'] =$schedulingTeamId;
               }
                $teamdata[$schedulingTeamId]['isDefault'] = (($value['isDefault'] == 0 || $value['isDefault'] == null)) ? 0:1;
                foreach ($refRolesResult as $roleValue) {
                    $newRoleName = str_replace(' ', '', $roleValue['RoleName']);

                    if($newRoleName == 'SchedulingTeamAdmin' && $value[$roleValue['RoleName']] != 0){
                        $data['HasTeamAdmin'] = 1;
                        $data['isAdmin'] = 1;
                    }
                    if( isset($value[$roleValue['RoleName']]) &&  $value[$roleValue['RoleName']] != 0 ){
                        $newRoleNameBasic = "is".str_replace(' ', '', $roleValue['RoleName']);
                        $data[$newRoleNameBasic] = 1;
                    }
                    if ($roleValue['RoleID']  == 2 ) {
                        $teamdata[$schedulingTeamId][$newRoleName] = (($value['DivisionId'] == 0 || $value['DivisionId'] == null) ? '<img src="../../../images/red_cross.png" class="tick">' : '<img src="../../../images/green_tick.png" class="tick">');
                        if($value['DivisionId'] != 0){
                            $data['DivisionalAdmin'] = 1;
                           }
                    }
                    else{
                        $teamdata[$schedulingTeamId][$newRoleName] = (($value[$roleValue['RoleName']] == 0 || $value[$roleValue['RoleName']] == null) ? 0:1);
                    }

                    if($data['isScheduledPerson'] == 0){
                        if($value['scheduledType'] == 1){
                            $data['isScheduledPerson'] = 1;
                        }
                    }
                }
            }

        }
        $data['Teams']  = $teamdata;
        return $data;
    }

    /*
   * @Description : Response result to getUserSetupByIdNetlogin functiomn in json for datatble array .
   * @access : Public
   * @global : Not Applicable
   * @param  : N/A
   * @return : Array output
   */
	function dataFormatDatatable($resultUserSetup, $isSysAdmin)
	{
		$greenTickImg 		= '<img src="../../../images/green_tick.png" class="tick">';
		$redTickImg 		= '<img src="../../../images/red_cross.png" class="tick">';
		$roleContainerArr	=	array_slice($resultUserSetup[0], 16);
		unset($roleContainerArr['Smartbook user']);
		unset($roleContainerArr['Timesheet Authoriser']);
        $roleContainerArr['Area Admin'] = $resultUserSetup[0]['DivisionId'];
		foreach ($resultUserSetup as $value) {
			$schedulingTeamId = $value['schedulingTeamId'];
			$data[$schedulingTeamId]['schedulingTeamName'] 	= $value['schedulingTeamName'];
			$data[$schedulingTeamId]['isDefault'] 			= (($value['isDefault'] == 0 || $value['isDefault'] == null) ? '<img src="../../../images/red_cross.png" class="tick js_setdefault" data-default= "1"  data-schedulepersonid="' . $value['ScheduledPersonID'] . '" data-teamid ="' . $schedulingTeamId . '"id="js_defaultimg">' : '<img src="../../../images/green_tick.png" class="tick js_setdefault" data-default= "0" data-schedulepersonid="' . $value['ScheduledPersonID'] . '" data-teamid ="' . $schedulingTeamId . '"id="js_defaultimg">');
			$data[$schedulingTeamId]['isWhosIn'] 			= (($value['isWhosIn'] == 0 || $value['isWhosIn'] == null || $value['isWhosIn'] == '') ? '<img src="../../../images/red_cross.png" class="tick" id="js_iswhosinimg' . $schedulingTeamId . '" onClick="setWhosInFlag(' . $schedulingTeamId . ', ' . $value['ScheduledPersonID'] . ', 1)">' : '<img src="../../../images/green_tick.png" class="tick" id="js_iswhosinimg' . $schedulingTeamId . '" onClick="setWhosInFlag(' . $schedulingTeamId . ', ' . $value['ScheduledPersonID'] . ', 0)">');
			$data[$schedulingTeamId]['System Admin'] 		= ( $isSysAdmin == 1 ) ? $greenTickImg : $redTickImg;
			foreach ($roleContainerArr as $roleContainerArrKey => $roleContainerArrVal) {
                if (in_array($roleContainerArrKey, array('System Admin'))) {
                    continue;
                }
                if ($roleContainerArrKey == 'Scheduled Person') {
                    $data[$schedulingTeamId]['Scheduled Person'] = ($value['scheduledType'] > 0) ? $greenTickImg : $redTickImg;
                } elseif ($roleContainerArrKey == 'Area Admin') {
                    $data[$schedulingTeamId]['Area Admin'] = (!empty($value['DivisionId'])) ? $greenTickImg : $redTickImg;
                } else {
                    $data[$schedulingTeamId][$roleContainerArrKey] = (!empty($value[$roleContainerArrKey])) ? $greenTickImg : $redTickImg;
                }
            }
		}
		return $data;
	}

	function GetDefaultTeam($type='') {
        if(!isset($type)){
            $type = '';
        }
		// Open the database
		$pdo = OpenDBLinkA7();
		// Set the statement to use
		$sql = "exec [dbo].[usp_Get_StaffScheduledTeams] ?,?";

		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(1,  $this->userID , PDO::PARAM_INT);
        $stmt->bindParam(2,  $type , PDO::PARAM_STR);
		// The parameters
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$resultjson = json_encode($result);
		return $resultjson;
	}

    /*
    * @Description :get User Home team
	* @access : Public
	* @global : Not Applicable
	* @return :array result
    */
    function GetUserHometeam() {
        $userData = json_decode($this->getUserSetupByIdNetlogin($type = 'menu'), true);
        $homeTeam = [];
        foreach($userData['Teams'] as $key => $userTeam) {
           if($userTeam['isHomeTeam'] == 1){
                $homeTeam = $userTeam;
           }
        }
        return $homeTeam;
    }

    /*
    * @Description :Check if user is other BBC user
	* @access : Public
	* @global : Not Applicable
	* @return :boolean result
    */
    function isOtherBBCUser() {
        $homeTeam = $this->GetUserHometeam();
        $isOtherBBCUser = isset($homeTeam['schedulingTeamName']) && $homeTeam['schedulingTeamName'] == 'Other BBC' ? 1 : 0;
        return $isOtherBBCUser;
    }

	function getUserDivisions(){
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_DivisionsByUserType] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }

    function isAreaReportUser(){
        // Open the database
        $pdo = OpenDBLinkA7();
        $strQuery = "SELECT	DivisionID , DivisionName 
        FROM Divisions(NOLOCK) DIV 
        WHERE EXISTS ( 
            SELECT 1 
            FROM UserRoles (NOLOCK) Ur 
            JOIN REF_Roles rr on rr.RoleID = Ur.UR_RoleID 
            WHERE Ur.UR_DivisionId = DIV.DivisionID 
            AND ur.UR_UserID = ? 
            AND rr.RoleName = 'Area Reports'
        )"; 
        $stmt = $pdo->prepare($strQuery);
        // The parameters
        $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return count($result) > 0 ? 1 : 0;
    }

    function checkTeamsHandoversValue($teamIds) {
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "select schedulingTeamId,hasHandovers from schedulingTeams where schedulingTeamId IN (".implode(',', $teamIds).")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $returnResult = array();

        foreach($result as $value) {
            if($value['hasHandovers'])
            $returnResult[$value['schedulingTeamId']] = $value['hasHandovers'];
        }

        return json_encode($returnResult);
    }

    function GetDefaultTeamSkill() {
        try {

            // Open the database
            $pdo = OpenDBLinkA7();

            $sql = "SELECT schedulingTeams.schedulingTeamId,schedulingTeams.schedulingTeamName
            FROM ScheduledPeople (NOLOCK)
            INNER JOIN ScheduledPersonTeam_LINK (NOLOCK) on ScheduledPeople.ScheduledPersonID=ScheduledPersonTeam_LINK.ScheduledPersonID
            INNER JOIN schedulingTeams (NOLOCK) ON ScheduledPersonTeam_LINK.TeamID=schedulingTeams.schedulingTeamId
            WHERE ScheduledPeople.UserId=:userid and ScheduledPersonTeam_LINK.IsHomeTeam=1 AND ScheduledPersonTeam_LINK.scheduledType=1  AND isnull(EndDate,'9999-01-01') >= getdate() and StartDate<=getdate()";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
            $stmt->execute();
            $resultRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultRolesJson = json_encode($resultRoles);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }

  /*
   * @Description : Set who's in flag team .
   * @access : Public
   * @global : Not Applicable
   * @param  : N/A
   * @return : JSON output
   */
    function setWhosInTeam($personId, $teamid)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_mod_setWhosInTeamRecord] ?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $personId, PDO::PARAM_INT);
        $stmt->bindParam(2, $teamid, PDO::PARAM_INT);
        $stmt->bindParam(3, $this->userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;
    }

    function createOrUpdateUserColor($intID, $description, $textColour, $divisionID) {
        if($textColour == ''){
            $textColour = null;
        }
        // Open the database
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_create_update_allocationsTextColours] ?, ?, ?, ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $intID, PDO::PARAM_INT);
        $stmt->bindParam(2, $description, PDO::PARAM_STR);
        $stmt->bindParam(3, $textColour, PDO::PARAM_STR);
        $stmt->bindParam(4, $divisionID, PDO::PARAM_INT);
        $stmt->execute();
    }


    /*
   * @Description : fetch login user is facility admin or not .
   * @access : Public
   * @global : Not Applicable
   * @param  : N/A
   * @return : boolen
   */
    function isFacilityAdmintUser(){
        // Open the database
        $pdo = OpenDBLinkA7();
        $strQuery = "SELECT DivisionID , DivisionName
        FROM Divisions DIV 
        WHERE EXISTS (
            SELECT 1 
            FROM DivisonalAdmin DA (NOLOCK)
            JOIN REF_Roles rr (NOLOCK) on rr.RoleID = DA.RoleId
            WHERE DA.DivisionID = DIV.DivisionID 
            AND DA.UserID = ?
            AND rr.RoleName = 'Facility Administrator' 
        )";
        $stmt = $pdo->prepare($strQuery);
        // The parameters
        $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return count($result) > 0 ? 1 : 0;
    }
}
