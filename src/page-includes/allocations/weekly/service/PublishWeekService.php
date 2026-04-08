<?php
/*
 * Created on Tue Nov 09 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Class PublishWeekService
 * Description: Publishes or unpublishes week
 *
 * Copyright (c) 2021 BBC
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../../../function-includes/DBHelper.php";
require_once __DIR__ . "/../../../../function-includes/helpers.php";

use Carbon\Carbon;
use Illuminate\Contracts\Queue\Queue;
use Symfony\Component\HttpFoundation\Request;

class PublishWeekService
{

     /**
     * @var \PDO
     */
    protected $pdo;

     /**
     * @var Request
     */
    protected $request;

    protected $error;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->pdo = OpenDBLinkA7();

    }


    /**
     * gets a publish record of the week for scheduling team
     *
     * @return array|bool
     */
    public function getPubslishRecord()
    {
        $request = $this->request;

        $query = "SELECT AL_Status Status, AL_UpdatedDate UpdatedDate, ud.UD_DisplayName DisplayName
		FROM Allocations as a
        inner join UserDetails ud (NOLOCK) on ud.UD_UserID = a.AL_UpdatedBy
        Where AL_WeekNumber = :startWeek
        And AL_SchedulingTeamID = :teamId
        ";

        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startWeek', $request->get('startWeek'), PDO::PARAM_STR);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return  $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }
    /**
     * updates allocation records as published or not
     *
     * @return void
     */
    public function update()
    {
        $request = $this->request;
        $sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

        try {
			$adhocDuties = $request->get('adhocDuties') ? 1 : 0;
            $isIncrementalPublish = 0;//$request->get('isIncrementalPublish') ? 0 : 1;
            $query = "exec [dbo].[usp_mod_PublishAllocations] @weekNumber = ?, @teamId = ?, @isPublished = ?, @futureAdhocDuty = ?, @IsIncrementalPublish = ?, @pNetLogin = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $request->get('weekNumber'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(3, $request->get('send'), PDO::PARAM_INT);
            $stmt->bindValue(4, $adhocDuties, PDO::PARAM_INT);
            $stmt->bindValue(5, $isIncrementalPublish, PDO::PARAM_INT);
            $stmt->bindValue(6, $sessUserNetLogin, PDO::PARAM_STR);
            $stmt->execute();
            if($request->get('send') == 1){
                //insert record
                if($this->getPubslishRecord() === false){
                    $isPublished=1;
                }
            }
            return true;
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
            $this->error = $e->getMessage();
        }

        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * gets a publish record of the week for scheduling team
     *
     * @return array|bool
     */
    public function getPubslishRecordUserName()
    {
        $request = $this->request;

        $query = "SELECT UD.UD_DisplayName AS DisplayName,
                        AL_UpdatedBy,
                        AL_UpdatedDate UpdatedDate
                    FROM Allocations AL
                    INNER JOIN UserDetails UD ON UD.UD_UserID = AL.AL_UpdatedBy
                    WHERE AL_WeekNumber  = :startWeek
                    AND AL_SchedulingTeamID = :teamId
                    AND AL_Status = 1";

        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startWeek', $request->get('startWeek'), PDO::PARAM_STR);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return  $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    /**
     * gets a publish record of the week for scheduling team for daily screen
     *
     * @return array|bool
     */
    public function getPubslishRecordDaily()
    {
        $request = $this->request;

        $query = "SELECT AL_Status FROM allocations
        Where AL_WeekNumber = :startWeek  And AL_SchedulingTeamID = :teamId";

        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startWeek', $request->get('startWeek'), PDO::PARAM_STR);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            $result =  $stmt->fetch(PDO::FETCH_ASSOC);
            if(!empty($result) && isset($result['AL_Status'])){
              return $result['AL_Status'];
            } else {
              return 0;
            }
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }
 }
