<?php
/*
 * Created on Thu Oct 28 2021
 *
 * Author: Ashish Tripathi
 * Name: Class LeaveRepository
 * Description:  A repository layer for leave functions, used for database abstraction.
 *
 * Copyright (c) 2022 BBC
 */

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../../../../function-includes/DBHelper.php";
require_once __DIR__ . "/../../../../function-includes/helpers.php";

if(is_null($_SESSION)){
    session_start();
}

class  LeaveRepository 
{

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct()
    {
        $this->pdo = OpenDBLinkA7();
    }

    /**
     *  This function is used to set PHL Leave Amount for Scheduled Person
     *
     * @param $request
     *
     * @return boolean
     */
    public function updatePHLLeaveAmountSchPerson(Request $request){
        try {
            $scheduledpersonid = $request->get('ScheduledPersonID');
            $query = "UPDATE UserDetails SET UD_PHLLeaveAmount = '".$request->get('PHLLeaveAmount')."' WHERE UD_UserID = :ScheduledPersonID";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':ScheduledPersonID', $scheduledpersonid, PDO::PARAM_INT);
            if($stmt->execute()){
                return true;
            } else {
                return false;
            }
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to set PHL Leave in Leave Allocations
     *
     * @param $request
     *
     * @return boolean
     */
    public function addPHLLeave(Request $request){
        try {
            $getHolidayEventName = $this->getTimeDimesionDataById($request->get('Holiday'));
            $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
           if ($request->get('pagecall')=='ExpiredPHL') {
            $comment =$request->get('comment');
           } else {
            $comment = $getHolidayEventName['sEvent'];
           }
            $query = "exec ? = [dbo].[usp_add_PHLLeaveAllocation] ?,?,?,?,?,?,?,?,?,?,?";
            $spresult = 0;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $spresult, PDO::PARAM_INT|PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
            $stmt->bindValue(2, $request->get('StaffNumber'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('year'), PDO::PARAM_INT);
            $stmt->bindValue(4, $request->get('PHLLeaveAmount'), PDO::PARAM_STR);
            $stmt->bindValue(5, $comment, PDO::PARAM_STR);
            $stmt->bindValue(6, $request->get('teamid'), PDO::PARAM_INT);
            $stmt->bindValue(7, $request->get('Holiday'), PDO::PARAM_INT);
            $stmt->bindValue(8, $request->get('ScheduledPersonId'), PDO::PARAM_INT);
            $stmt->bindValue(9, $sessUserId, PDO::PARAM_INT);
            $stmt->bindValue(10, 14, PDO::PARAM_INT);
            $stmt->bindValue(11, $_SESSION['user']['FullName'], PDO::PARAM_STR);
            $stmt->bindValue(12, 0, PDO::PARAM_INT);
            $stmt->execute();
            return $spresult;
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to get Holiday name from Timedimension table
     *
     * @param $id This param contains the ID information
     *
     * @return array
     */
    public function getTimeDimesionDataById($id){
        try {
            $query = "SELECT dDateTime,sEvent FROM TimeDimension WHERE ID=:id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }
}