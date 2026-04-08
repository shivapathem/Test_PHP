<?php
include_once '../../../function-includes/DBHelper.php';

class classScheduledPerson
{

    public $userID;

    public function __construct()
    {

        $this->userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
    }


    /*
    * @Description : Get  Scheduled person details by serach param
	* @access : Public
	* @global : Not Applicable
	* @param  : $searchUser,$searchTeam,$actionType=''
	* @return : Return the assigned duties filter resulrt
    */
    function getSearchScheduledPerson($searchUser = '', $searchTeam = '', $actionType = '', $excludeNoTeam = '')
    {
        if($searchTeam == '' && $searchUser == ''){
            $searchTeam = 0;
        }
        $pdo = OpenDBLinkA7();

        // Set the statement to use
        $sql = "exec [dbo].[usp_get_ScheduledPeopleDetails] ?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $searchUser, PDO::PARAM_STR);
        $stmt->bindParam(2, $searchTeam, PDO::PARAM_STR);
        $stmt->bindParam(3, $actionType, PDO::PARAM_STR);
        $stmt->bindParam(4, $this->userID, PDO::PARAM_INT);
        $stmt->bindParam(5, $excludeNoTeam, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }


    /*
        * @Description : Get  Scheduled person team dropdown
       * @access : Public
        * @global : Not Applicable
        * @param  :
        * @return : Return the result which seearch by param
        */
    function getUserTeamList($roleId)
    {

        $pdo = OpenDBLinkA7();
		$maxAllowedRoleId = 6;	//only schedular[role id:6] and above can access team list
        // Set the statement to use
        $sql = "exec [dbo].[usp_get_GetUserTeamList] ?, ?, ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
        $stmt->bindParam(2, $roleId, PDO::PARAM_INT);
        $stmt->bindParam(3, $maxAllowedRoleId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }


    /*
        * @Description : Get  Scheduled person user
        * @access : Public
        * @global : Not Applicable
        * @param  : $searchTeam,$searchUser
        * @return : Return the result which seearch by param
        */
    function getScheduledPeopleUserList($searchTeam, $searchUser,$pageid, $excludeNoTeam)
    {

        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_get_GetScheduledPeopleUserList] ?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
        $stmt->bindParam(2, $searchTeam, PDO::PARAM_STR);
        $stmt->bindParam(3, $searchUser, PDO::PARAM_STR);
        $stmt->bindParam(4, $pageid, PDO::PARAM_INT);
        $stmt->bindParam(5, $excludeNoTeam, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }
}





