<?php
include_once '../../../../function-includes/DBHelper.php';
include_once '../../../../function-includes/helpers.php';


class ClassAllocateUsers
{
    public $userID = '';
    public $moduleName = '';
    public $cureentaDate = '';
    public $isactive = '';
    public $netlogin = '';

    public function __construct()
    {
        $this->userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
        $this->moduleName = 'AllocateUsers';
        $this->cureentaDate = date("d-m-Y");
        $this->isactive = 1;
        $this->netlogin =  isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ? $_SESSION['user']['user'] :$_COOKIE['editWeeklyUserNetLogin'];
    }

    /*
    * @Description : Fetch all of the allocate users (i.e from users table).
	* @access : Public
	* @global : Not Applicable
	* @param  : $type
	* @return : JSON output
    */
    function getAllocateUsers($type)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_AllocateUsers] ?,?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,$this->netlogin, PDO::PARAM_STR);
            $stmt->bindParam(2, $type, PDO::PARAM_STR);
            $stmt->execute();
            $resultallocateusers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if( $type !='list'){
                return json_encode($resultallocateusers);
            }
            if(empty($resultallocateusers)){
                $resultallocatedata[] = array('', '','', 'No Record Found', '', '','');
            }
            else{
                foreach ($resultallocateusers as $value) {

                    if($value['DisplayName']) {
                        $fullname = $value['DisplayName'];
                        $forename = $value['Forename'];
                    } else {
                        $fullname = $value['Forename'].' '.$value['Surname'];
                        $forename = $value['Forename'];
                    }

                    $resultallocatedata[] = array($fullname, $value['NetLogin'],
                        $value['InternalEmail'], $value['EmpNumber'], $value['StaffNumber'], $value['schedulingTeamName'],
                    '<a class="js_userinfo"  href="javascript:void(0)" data-netlogin="'.$value['NetLogin'].'"  data-userid="'.$value['UserID'].'" data-staffid="'.$value['StaffID'].'" data-first_tab="true"><i class="fa fa-info-circle" id="circle-clr"></i></a>');
                }
            }
            return json_encode($resultallocatedata);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
     * @Description : insert/ allocate users record
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */
    function insertAllcoateUsers($netLogin, $UserEmployeeNumber, $UserSurname, $UserFirstName, $UserEmailAddress)
    {
        try {
            $status = $returnstring = '';
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_mod_allocateUserWithStaffEntry] ?,?,?,?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $netLogin, PDO::PARAM_STR);
            $stmt->bindParam(2, $this->userID, PDO::PARAM_INT);
            $stmt->bindParam(3, $status, PDO::PARAM_STR);
            $stmt->bindParam(4, $returnstring, PDO::PARAM_STR);
            $stmt->bindParam(5, $UserEmployeeNumber, PDO::PARAM_STR);
            $stmt->bindParam(6, $UserSurname, PDO::PARAM_STR);
            $stmt->bindParam(7, $UserFirstName, PDO::PARAM_STR);
            $stmt->bindParam(8, $UserEmailAddress, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode($result);

        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    /*
     * @Description : check in AD
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */
    function UserDetailsFromActiveDirectory($netLogin)
    {

        try {

            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_UserDetailsFromDomain] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $netLogin, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if(isset($result['UserEmployeeNumber']) && strlen($result['UserEmployeeNumber']) > 7) {
                $result['UserEmployeeNumber'] = ltrim($result['UserEmployeeNumber'], '0');
            }
            return json_encode($result);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    /*
     * @Description : check allocate users in users table
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */

    function checkAllocateUser($netLogin)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_GET_Checkusers] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $netLogin, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode($result);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
/*
     * @Description : show allocate user info
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */

    function showAllocateUserInfo($userID,$netLogin)
    {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_get_MySetUp] ?, ?, 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,  $userID, PDO::PARAM_INT);
            $stmt->bindParam(2,  $netLogin, PDO::PARAM_STR);
            $stmt->execute();
            $resultUserSetup = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $resultdata = $this->dataformat($resultUserSetup);
            return json_encode($resultdata);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
    * @Description : Response result to getUserSetupByIdNetlogin functiomn in normal array .
	* @access : Public
	* @global : Not Applicable
	* @param  : N/A
	* @return : Array output
    */

    function dataformat($resultUserSetup){

        $greenTickImg 		= '<img src="../../../images/green_tick.png" alt="Yes" class="tick">';
        $redTickImg 		= '<img src="../../../images/red_cross.png" alt="No" class="tick">';
        if (isset($resultUserSetup[0])) {
            $roleContainerArr	=	array_slice($resultUserSetup[0], 14);
        } else {
            $roleContainerArr = array();
        }

        unset($roleContainerArr['System Admin']);
		unset($roleContainerArr['Scheduled Person']);
        $data = [];
        foreach ($resultUserSetup as $value)
		{
            $schedulingTeamId = $value['schedulingTeamId'];

            $value['Area Admin'] = $value['Area Admin'] ?? 0;
            $value['scheduledType'] = $value['scheduledType'] ?? 0;
            $value['System Admin'] = $value['System Admin'] ?? 0;
            $value['Scheduling Team Admin'] = $value['Scheduling Team Admin'] ?? 0;
            $value['Senior Scheduler'] = $value['Senior Scheduler'] ?? 0;
            $value['Scheduler'] = $value['Scheduler'] ?? 0;
            $value['Scheduling Team Viewer'] = $value['Scheduling Team Viewer'] ?? 0;
            $value['Home Team'] = $value['Home Team'] ?? 0;

            if(($value['scheduledType'] == 1) || (($value['scheduledType'] == 0) && (($value['System Admin'] + $value['Area Admin'] + $value['Scheduling Team Admin'] + $value['Senior Scheduler'] + $value['Scheduler'] + $value['Scheduling Team Viewer']) > 0)))
			{
                $data[$schedulingTeamId]['ScheduledPersonID'] 	= $value['ScheduledPersonID'];
                $data[$schedulingTeamId]['schedulingTeamName'] 	= $value['schedulingTeamName'];
                $data[$schedulingTeamId]['SortCode'] 			= $value['SortCode'];
                $data[$schedulingTeamId]['isDefault'] 			= (($value['isDefault'] == 0 || $value['isDefault'] == null) ? $redTickImg : $greenTickImg);
				$data[$schedulingTeamId]['System Admin'] 		= ( $value['System Admin'] == 1 ) ? $greenTickImg : $redTickImg;
				$data[$schedulingTeamId]['Area Admin'] 	= ( $value['DivisionId']  > 0 ) ? $greenTickImg : $redTickImg;
				$data[$schedulingTeamId]['Scheduled Person'] 	= ( $value['scheduledType']  > 0 ) ? $greenTickImg : $redTickImg;
				foreach($roleContainerArr as $roleContainerArrKey=>$roleContainerArrVal)
				{

                    if($value['Home Team'] ==1){
						$value['Todayactivehometeam'] = isset($value['Todayactivehometeam']) ? $value['Todayactivehometeam'] : '';
                        $data[$schedulingTeamId][$roleContainerArrKey]= ($value[$roleContainerArrKey] > 0) ? '<img src="../../../images/green_tick.png" alt="Yes" title="'.$value['Todayactivehometeam'].'" class="tick">' : '<img src="../../../images/red_cross.png" alt="No" class="tick">';
                    }else{
                        $data[$schedulingTeamId][$roleContainerArrKey]= ($value[$roleContainerArrKey] > 0) ? $greenTickImg : $redTickImg;
                    }

				}
            }
        }
        return $data;
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

            $sql = "SELECT * FROM REF_Roles Where isActive = ?  order by isSequence,isAdditional,RoleID ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,$this->isactive, PDO::PARAM_INT);
            $stmt->execute();
            $resultRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($resultRoles) && count($resultRoles)) {
                $nextIndex = count($resultRoles) + 1;
                $resultRoles[] = array('RoleID' => $nextIndex, 'RoleName' => 'Home Team', 'RoleDescription' => 'Home','IsActive' => 1,'isAdditional' => 0);
            }

            return json_encode($resultRoles);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
