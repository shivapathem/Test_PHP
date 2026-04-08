<?php
/*
 * Created on Tue Nov 23 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Class TimeDimensionService 
 * Description: A Service to handle BBC TimeDimension Flow
 *
 * Copyright (c) 2021 BBC
 */


use Carbon\CarbonInterface;

include_once __DIR__ . "/TimeDimension.php";
require_once __DIR__ . "/../../../../function-includes/init.php";


class TimeDimensionService 
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
     * Finds the TimeDimension by date
     *
     * @param CarbonInterface $carbon
     * @return TimeDimension|null
     */
    public function findByDate(CarbonInterface $carbon) : ?TimeDimension
    {
        
        $query = "SELECT ixWeekInYear,ixYear,ixYearWeek FROM TimeDimension
        Where dDateTime = :date
        ";

        try {
            $stmt = $this->pdo->prepare($query);
         
            $stmt->bindValue(':date', $carbon->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();
 
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);

            if($row){
                return new TimeDimension($row);
            }

            return null;

        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }


    /**
     * Finds the TimeDimension by weeknumber and iday
     *
     * @param integer $weekNumber
     * @param integer $ixWeekDay
     * @return TimeDimension|null
     */
    public function findByWeekNumber($weekNumber, $ixWeekDay = 0) : ?TimeDimension
    {
        
        $query = "SELECT * FROM TimeDimension
        Where ixYearWeek = :weekNumber
        and ixDayInWeek = :ixWeekDay
        order by ID desc
        ";

        try {
            $stmt = $this->pdo->prepare($query);
         
            $stmt->bindValue(':weekNumber', $weekNumber, PDO::PARAM_INT);
            $stmt->bindValue(':ixWeekDay', $ixWeekDay, PDO::PARAM_INT);
            $stmt->execute();
 
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);

            if($row){
                return new TimeDimension($row);
            }

            return null;

        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Finds the TimeDimension by date
     *
     * @param integer $weekNumber
     * @param integer $ixWeekDay
     * @return TimeDimension|null
     */
    public function findByDateWithoutCarbon($dateTime)
    {
        $query = "SELECT ixDayInWeek,ixYearWeek FROM TimeDimension WHERE dDateTime = CONVERT(DATETIME,:dateVal,102)";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':dateVal', $dateTime, PDO::PARAM_STR);
            $stmt->execute();
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);
            if($row){
                return new TimeDimension($row);
            }
            return null;
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Finds the TimeDimension by date
     *
     * @param integer $weekNumber
     * @param integer $ixWeekDay
     * @return TimeDimension|null
     */
    public function findImmediateNextWeekOfCurrentWeek($currentWeekNumber)
    {
        $query = "SELECT TOP 1 dDateTime FROM TimeDimension WHERE ixYearWeek = :currentWeekNumber ORDER BY dDateTime DESC";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':currentWeekNumber', $currentWeekNumber, PDO::PARAM_STR);
            $stmt->execute();
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);
            if($row){
                $nextDayDate = date('Y-m-d', strtotime('+1 day', strtotime((string) $row['dDateTime'])));
                $nextDayWeek = $this->findByDateWithoutCarbon($nextDayDate);
                if($nextDayWeek){
                    return $nextDayWeek;
                }
            }
            return null;
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Finds the TimeDimension start date for the weeknumber
     *
     * @param integer $weekNumber
     *
     * @return TimeDimension|null
     */
    public function getWeekStartDate($weekNumber)
    {
        $explodeWeekNumber = explode('/',$weekNumber);
        $condWeekNumber = $explodeWeekNumber[1].$explodeWeekNumber[0];
        $query = "SELECT TOP 1 dDateTime FROM TimeDimension WHERE ixYearWeek = :weekNum ORDER BY ID ASC";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':weekNum', $condWeekNumber, PDO::PARAM_INT);
            $stmt->execute();
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);
            if($row){
                return new TimeDimension($row);
            }
            return null;
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Finds the TimeDimension end date for the weeknumber
     *
     * @param integer $weekNumber
     *
     * @return TimeDimension|null
     */
    public function getWeekEndDate($weekNumber)
    {
        $explodeWeekNumber = explode('/',$weekNumber);
        $condWeekNumber = $explodeWeekNumber[1].$explodeWeekNumber[0];
        $query = "SELECT TOP 1 dDateTime FROM TimeDimension WHERE ixYearWeek = :weekNum ORDER BY ID DESC";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':weekNum', $condWeekNumber, PDO::PARAM_INT);
            $stmt->execute();
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);
            if($row){
                return new TimeDimension($row);
            }
            return null;
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Finds the TimeDimension by date
     *
     * @param integer $weekNumber
     * @param integer $ixWeekDay
     * @return TimeDimension|null
     */
    public function findImmediatePrevWeekOfCurrentWeek($currentWeekNumber)
    {
        $query = "SELECT TOP 1 dDateTime FROM TimeDimension WHERE ixYearWeek = :currentWeekNumber ORDER BY dDateTime ASC";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':currentWeekNumber', $currentWeekNumber, PDO::PARAM_STR);
            $stmt->execute();
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);
            if($row){
                $nextDayDate = date('Y-m-d', strtotime('-1 day', strtotime((string) $row['dDateTime'])));
                $nextDayWeek = $this->findByDateWithoutCarbon($nextDayDate);
                if($nextDayWeek){
                    return $nextDayWeek;
                }
            }
            return null;
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * check for valid week number
     *
     * @param integer $weekNumber
     *
     * @return TimeDimension|null
     */
    public function checkValidWeekOfYear($weekNumber)
    {
        try {
            $query = "SELECT TOP 1 ixWeekInYear FROM TimeDimension WHERE ixYearWeek = :weekNum";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':weekNum', $weekNumber, PDO::PARAM_INT);
            $stmt->execute();
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);
            if($row){
                return new TimeDimension($row);
            }
            return null;
        }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
        }
        return null;
    }
}