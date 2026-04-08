<?php
/*
 * Created on Thu Oct 28 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Class AllocationRepository
 * Description:  A repository layer for allocations, used for database abstraction.
 *
 * Copyright (c) 2021 BBC
 */
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;
use Traits\UserRoleTrait;

require_once __DIR__ . "/../../../../function-includes/DBHelper.php";
require_once __DIR__ . "/../../../../function-includes/helpers.php";
require_once __DIR__ . "/../../../../function-includes/genericfunctions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AllocationRepository
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
     * get pdo instance
     *
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * calculate seconds from time of Over 12 and Under 11
     *
     * @param Request $request
     * @return void
     */
    public function timetoseconds(string $duration, string $delimiter): int
    {
        $parts = explode($delimiter, $duration);

        if (count($parts) < 2) {
            // Handle invalid input gracefully, e.g., return 0 or throw an exception
            return 0;
        }

        [$hours, $minutes] = $parts;

        // Map fractional minutes to actual minutes
        $minutes = match ((int)$minutes) {
            25 => 15,
            50, 5 => 30,
            75 => 45,
            default => 0,
        };
        $seconds = ((int)$hours * 3600) + ($minutes * 60);
        return $seconds;
    }

    /**
     * Removes the allocations rows between days desired weeks
     *
     * @param Request $request
     * @return void
     */
    public function removeAllocations(Request $request)
    {
        $query = "DELETE FROM dbo.Allocations
        WHERE WeekNumber >= :startWeek
        And WeekNumber <= :endWeek
        And SchedulingTeamId = :teamId
        ";

        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startWeek', $request->get('startWeek'), PDO::PARAM_STR);
            $stmt->bindValue(':endWeek', $request->get('endWeek'), PDO::PARAM_STR);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    public function checkIfWeekExists(Request $request): array
    {
            $endDate = date('Y-m-d', strtotime('-1 day', strtotime($request->get('endDate'))));
			$gEditWeeklyDataSessUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];//set rowcount 100;
			$query = "exec [dbo].[usp_get_EditWeekly] '" . $request->get('startDate') . "','" . $endDate . "'," . $request->get('teamId') . ",'" . $gEditWeeklyDataSessUser . "'";
			$returnData['isWeekCreated'] = 0;
        try {
            $stmt = $this->pdo->prepare($query);
			$stmt->execute();
			$isWeekCreatedArr = $stmt->fetchAll(PDO::FETCH_NUM);
			if($isWeekCreatedArr[0][0] == 1)
			{
				$stmt->nextRowset();
				$returnDataArr = $stmt->fetchAll(PDO::FETCH_NUM);
				$uId = 1000001;
				foreach($returnDataArr as $returnDataArrVal)
				{
					//$returnDataArrVal[] = $uId;
					if($returnDataArrVal[68] == 'UL')
					{
						$returnDataArrVal[88] = '';//$returnDataArrVal[88] != 1 ? 'doesntNeedCoveringIcon' : '';
						$returnData['unalloc'][] = [$returnDataArrVal[1], $returnDataArrVal[2], $returnDataArrVal[3], $returnDataArrVal[4], $returnDataArrVal[5], $returnDataArrVal[6], $returnDataArrVal[13], $returnDataArrVal[14], 0, $returnDataArrVal[16], $returnDataArrVal[25], $returnDataArrVal[26], $returnDataArrVal[72], $returnDataArrVal[68], $returnDataArrVal[75], $returnDataArrVal[92], $returnDataArrVal[93], $returnDataArrVal[94], $returnDataArrVal[88], "InstanceIds"=>$returnDataArrVal[75]];
					}elseif($returnDataArrVal[68] == 'AL')
					{
						$returnData['alloc'][] = $returnDataArrVal;
					}
					$uId++;
				}
			}else
			{
				$stmt->nextRowset();
				$returnDataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$returnData['rota'][] = $returnDataArr;
			}
			$returnData['isWeekCreated'] = $isWeekCreatedArr[0][0];
            if($request->get('mastMiscFilterId') != ''){
                $filterId = $request->get('mastMiscFilterId') ?? 0;
                $returnData['unalloc'] = $this->getFilteredUnallocatedDuties($filterId, $returnData['unalloc']);
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return ['isWeekCreated' => 0];
    }

    public function getWeekData(Request $request): array
    {
		$endDate = date('Y-m-d', strtotime('-1 day', strtotime($request->get('endDate'))));
		$gEditWeeklyDataSessUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
        $query = "exec [dbo].[usp_get_EditWeekly] '" . $request->get('startDate') . "','" . $endDate . "'," . $request->get('teamId') . ",'" . $gEditWeeklyDataSessUser . "'";
		$returnData['isWeekCreated'] = 0;
        try {
            $stmt = $this->pdo->prepare($query);
			$stmt->execute();
			$isWeekCreatedArr = $stmt->fetchAll(PDO::FETCH_NUM);
			if(array_key_exists(0, $isWeekCreatedArr) && array_key_exists(0, $isWeekCreatedArr[0]) && $isWeekCreatedArr[0][0] == 1)
			{
				$stmt->nextRowset();
				$returnDataArr = $stmt->fetchAll(PDO::FETCH_NUM);
				$uId = 1000001;
				foreach ($returnDataArr as $returnDataArrVal) {

					if (isset($returnDataArrVal[68]) && $returnDataArrVal[68] === 'UL') {

						$returnData['unalloc'][] = [
							$returnDataArrVal[1]  ?? null,
							$returnDataArrVal[2]  ?? null,
							$returnDataArrVal[3]  ?? null,
							$returnDataArrVal[4]  ?? null,
							$returnDataArrVal[5]  ?? null,
							$returnDataArrVal[6]  ?? null,
							$returnDataArrVal[13] ?? null,
							$returnDataArrVal[14] ?? null,
							0,
							$returnDataArrVal[16] ?? null,
							$returnDataArrVal[25] ?? null,
							$returnDataArrVal[26] ?? null,
							$returnDataArrVal[72] ?? null,
							$returnDataArrVal[68],
							$returnDataArrVal[75] ?? null,
							$returnDataArrVal[92] ?? null,
							$returnDataArrVal[93] ?? null,
							$returnDataArrVal[94] ?? null,
							'',//$returnDataArrVal[88] != 1 ? 'doesntNeedCoveringIcon' : '',
							"InstanceIds" => $returnDataArrVal[75] ?? null
						];

					} elseif (isset($returnDataArrVal[68]) && $returnDataArrVal[68] === 'AL') {

						$returnData['alloc'][] = $returnDataArrVal;
					}

					$uId++;
				}

				setcookie("uId", $uId);
			}else
			{
				$stmt->nextRowset();
				$returnDataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$returnData['rota'][] = $returnDataArr;
			}
			$returnData['isWeekCreated'] = $isWeekCreatedArr[0][0] ?? 0;
            if($request->get('mastMiscFilterId') != ''){
                $filterId = $request->get('mastMiscFilterId') ?? 0;
                $returnData['unalloc'] = $this->getFilteredUnallocatedDuties($filterId, $returnData['unalloc'] ?? []);
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
		return $returnData;
    }

    /**
     * gets unallocated duties between two dates
     * from allocations table
     *
     * @param Request $request
     * @return array
     */
    public function getUnAllocatedDuties(Request $request): array
    {
        try {
            $mastMiscFilterId = 0;
            if ($request->get('mastMiscFilterId') != '') {
                $mastMiscFilterId = $request->get('mastMiscFilterId');
            }

            $query = "exec [dbo].[usp_get_ReadUnassignAllocationsEditWeekly] '" . $request->get('startDate') . "','" . $request->get('endDate') . "','" . $request->get('teamId') . "','" . $mastMiscFilterId . "','" . $request->get('unallocOrdering') . "'";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return [];

    }

    /**
     * Get allocated duties from db
     *
     * @param Request $request
     * @return array
     */
    public function getAllocatedDuties(Request $request): array
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $query = "exec [dbo].[usp_Edit_AndGetAllocations] @startDate=?,@EndDate=?,@TeamID=?,@SchedulingPersonID=?,@EditType=?,@pNetLogin=?,@FromID=?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $request->get('startDate'), PDO::PARAM_STR);
            $stmt->bindValue(2, $request->get('endDate'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(4, $request->get('scheduledPersonId'), PDO::PARAM_STR);
            $stmt->bindValue(5, 'UNASSIGN', PDO::PARAM_STR);
            $stmt->bindValue(6, $sessUserNetId, PDO::PARAM_STR);
            $stmt->bindValue(7, $request->get('dataSourceId'), PDO::PARAM_INT);
            $stmt->execute();
            $respdata = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!isset($respdata['spStatus']) && !empty($respdata)) {
                $returnData['spStatus'] = true;
                $returnData['spData'] = $respdata;
            } else {
                $returnData['spStatus'] = false;
                $returnData['errorMessage'] = $respdata[0]['errorMessage'];
            }
            return $returnData;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * gets master duties
     *
     * @return array
     */
    public function getMasterDuties(Request $request): array
    {
        //TODO Get master duties that are not assigned to anyone
        $query = "
        DECLARE @startWeek varchar(100) = :startWeek
        DECLARE @teamId int = :team
        SELECT * FROM dbo.MasterDuties as md
        WHERE (md.TeamID = @teamId)
        AND md.EndWeek >= @startWeek
        ";
        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startWeek', $request->get('startWeek'), PDO::PARAM_INT);
            $stmt->bindValue(':team', $request->get('teamId', 99), PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchall(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return [];
    }

    /**
     * get jobs belongs to master duty
     *
     * @param int $masterDutyId
     * @return array
     */
    public function getMasterDutyJobs($masterDutyId)
    {
        $query = "exec [dbo].[usp_GET_MasterJobsByMasterDutyID] ?";

        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(1, $masterDutyId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchall(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return [];
    }

    public function getTeamRotas(Request $request)
    {
        try {
            //get cycled rotas
            $rotasQuery = "select * from MasterRotas where TeamID = :team";
            $stmt = $this->pdo->prepare($rotasQuery);
            $stmt->bindValue(':team', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchall(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return [];
    }

    public function getCycledRotaDuties(Request $request, $schedulingPersonId = null)
    {

        //get duties in rotas
        $query = "
        DECLARE @startWeek varchar(100) = :startWeek
        DECLARE @team varchar(100) = :team

        select rd.RotaWeek, rp.StartWeek, mr.WeeksInRota, TD1.ixYearWeek,
            md.MasterDutyID, md.DutyName,stl.ScheduledPersonID as SchedulingPersonID, rd.RotaWeek, rd.DOTW,
            md.DutyColourID, md.BackColour, md.ForeColour, md.StartTime, md.EndTime, stl.TeamID as SchedulingTeamId,
            stl.IsHomeTeam
        from RotaPeople as rp
        inner join ScheduledPersonTeam_LINK as stl on rp.ScheduledPersonID = stl.ScheduledPersonID
        and (stl.IsHomeTeam = 1 or (stl.IsHomeTeam = 0 and stl.IsActive = 1) )
        inner join MasterRotas as mr on mr.RotaID = rp.RotaID
        inner join RotaDuties as rd (NOLOCK) ON rd.RotaID = mr.RotaID
        inner join MasterDuties as md on md.MasterDutyID = rd.MasterDutyID
        inner join
            ( SELECT td.ixYearWeek,
            CASE when ROW_NUMBER() over(partition by SchedulingPersonID order by td.ixYearWeek) % td.WeeksInRota = 0
            THEN td.WeeksInRota
            ELSE ROW_NUMBER() over(partition by SchedulingPersonID order by td.ixYearWeek) % td.WeeksInRota
            END weeksinrota,
            SchedulingPersonID
                FROM ( SELECT DISTINCT ixYearWeek,er.WeeksInRota,er.SchedulingPersonID
                    FROM TimeDimension td, ( SELECT DISTINCT mr.RotaStartWeek AssignmentStartWeek,
                    mr.WeeksInRota WeeksInRota,
                    rp.ScheduledPersonID as SchedulingPersonID
                    FROM RotaPeople rp
                    inner join MasterRotas as mr on mr.RotaID = rp.RotaID
                    and mr.TeamID = @team
                    and rp.IsActive=1 ) er
                    WHERE td.ixYearWeek BETWEEN er.AssignmentStartWeek AND @startWeek
                    ) TD
            ) TD1
            ON rd.rotaweek=td1.weeksinrota and rp.ScheduledPersonID =td1.SchedulingPersonID,
        (select min(dDateTime) as start_week, max(dDateTime) as end_Date
            from TimeDimension
            where ixYearWeek=202204
        ) as TD2

        where td1.ixYearWeek = @startWeek
        and stl.TeamID = @team
        and rd.StartDate <= td2.end_Date and rd.EndDate >= td2.start_week
        and rp.StartDate <= td2.end_Date and rp.EndDate >= td2.start_week
        and stl.scheduledType = 1
        and rp.IsActive=1
        and rd.IsActive=1
        ";

        if ($schedulingPersonId) {
            $query .= " and stl.ScheduledPersonID = :personId";
        }

        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startWeek', $request->get('startWeek'), PDO::PARAM_INT);
            $stmt->bindValue(':team', $request->get('teamId'), PDO::PARAM_INT);

            if ($schedulingPersonId) {
                $stmt->bindValue(':personId', $schedulingPersonId, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchall(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return [];
    }

    /**
     * inserts data to allocations table
     *
     * @param Collection $collection
     * @return bool
     */
    public function createWeek(Collection $collection): bool
    {
        //todo try batch insert with chunk

        try {
            $this->pdo->beginTransaction();
            $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
            foreach ($collection as $allocation) {
                if (!$allocation instanceof Allocation) {
                    throw new Exception('Collection must contain Allocation object');
                }

                if ($jobs = $allocation->getJobs()) {
                    unset($allocation->AllocationJobs);
                }

                $insert_keys = '(' . $this->placeholders(':', array_keys($allocation->toArray())) . ')';
                $insert_values = $allocation->toArray();

                $datafields = array_keys($collection->first()->toArray());

                $sql = "INSERT INTO dbo.Allocations (" . implode(",", $datafields) . ") VALUES " . $insert_keys;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($insert_values);

                $strQueryLastId = "SELECT TOP 1 ID FROM Allocations (NOLOCK) ORDER BY ID DESC";
                $stmtLastId = $pdo->prepare($strQueryLastId);
                $stmtLastId->execute();
                $resLastId = $stmtLastId->fetch(PDO::FETCH_ASSOC);
                $lastId = $resLastId['ID'];

                //insert jobs
                if ($jobs) {
                    foreach ($jobs as $job) {
                        if (!$job instanceof AllocationJob) {
                            throw new Exception('Allocation Jobs must contain AllocationJob object');
                        }
                        $job->AllocationID = $lastId;
                        $insert_keys = '(' . $this->placeholders(':', array_keys($job->toArray())) . ')';
                        $sql = "INSERT INTO dbo.Allocations_jobs (" . implode(",", array_keys($job->toArray())) . ") VALUES " . $insert_keys;
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute($job->toArray());
                    }
                }

                //add history log
                $logText = sprintf(
                    "Created %s on %s By %s. Duty: %s",
                    date('h:i:s A'),
                    date('d/m/Y'),
                    $_SESSION['user']['DisplayName'],
                    $insert_values['DutyName']
                );

                $request = new Request();
                $request->request->set('attributeId', $lastId);
                $request->request->set('historyType', 8);
                $request->request->set('userId', $sessUserId);
                $request->request->set('message', $logText);
                $this->addAllocationHistory($request);
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            logger()->critical('DB ERROR', (array) $e);
            return false;
        }

        return true;
    }

    private function placeholders($text, $data = [], $separator = ",")
    {
        $result = [];
        foreach ($data as $d) {
            $result[] = $text . $d;
        }

        return implode($separator, $result);
    }
    
    public function getAllRoles($isActive = 0)
    {
        try {
            $query = "SELECT * FROM REF_Roles Where isActive = ?  order by isAdditional,RoleID ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $isActive, PDO::PARAM_INT);
            $stmt->execute();
            $resultRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($resultRoles);
        } catch (Exception $e) {
            logger()->critical('DB ERROR', (array) $e);
            return false;
        }
    }

    public function setPublishStatus(Request $request, $isPublished = 1): bool
    {
        $query = "UPDATE dbo.Allocations
        SET isPublished = :isPublished
        WHERE WeekNumber >= :startWeek
        And WeekNumber <= :endWeek
        ";

        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startWeek', $request->get('startWeek'), PDO::PARAM_STR);
            $stmt->bindValue(':endWeek', $request->get('endWeek'), PDO::PARAM_STR);
            $stmt->bindValue(':isPublished', $isPublished, PDO::PARAM_BOOL);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'];
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    public function getPublicFilters(Request $request)
    {
        try {
            $query = "SELECT ID,Description FROM AutoPagesFilters WHERE SchedulingTeamId=:teamId AND isPublic=1 ORDER BY Description";
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $row;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    /**
     * gets the allocations by week range
     *
     * @param string $schedulingTeamId
     * @param string $startWeek
     * @param string $endWeek
     * @return array
     */
    public function getAllocationsByWeekRange($schedulingTeamId, $startWeek, $endWeek)
    {
        try {
            $query = "SELECT ID, FORMAT (a.DutyDate, 'yyyy-MM-dd') as DutyDate, Duration, WeekNumber
            FROM Allocations as a (NOLOCK)
            WHERE SchedulingTeamId=:teamId
             AND  (a.WeekNumber >= :startWeek and a.WeekNumber <= :endWeek)
             and SchedulingPersonID is not null
            ";
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startWeek', $startWeek, PDO::PARAM_INT);
            $stmt->bindValue(':endWeek', $endWeek, PDO::PARAM_INT);
            $stmt->bindValue(':teamId', $schedulingTeamId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return [];
    }

    public function getPrivateFilters(Request $request, $userId = 0)
    {
        try {
            $query = "SELECT ID,Description FROM AutoPagesFilters WHERE SchedulingTeamId=:teamId AND isPublic=0 AND UserID=:userId ORDER BY Description";
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $row;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    public function getUserTeamRole(Request $request, $userId = 0)
    {
        try {
            $query = "SELECT UR_RoleID AS RoleID FROM UserRoles
            WHERE UR_UserID = :userId AND UR_SchedulingTeamID = :teamId
            AND UR_StartDate <= convert(datetime,convert(varchar(10),getdate(),110),110)
            AND isnull(UR_EndDate,'9999-12-01') >= convert(datetime,convert(varchar(10),getdate(),110),110)";
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $row;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

	public function editAllocations($data)
	{
		try
		{
			$netLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			if(in_array($data['editType'], array('MARKFORATTENTION', 'MARKPURPLE')))
			{
				$colFlag = '';
				$colFlagVal = '';
				if($data['editType'] == 'MARKFORATTENTION')
				{
					$colFlag = '@pMarkForAttention';
					$colFlagVal = $data['isAttention'];
				}
				if($data['editType'] == 'MARKPURPLE')
				{
					$colFlag = '@pMarkPurple';
					$colFlagVal = $data['isRequest'];
				}
				$query = "exec usp_Edit_Allocations @EditType = '".$data['editType']."', @pSchedulingPersonID = ".$data['allocationsSchPer'].", @pIsShiftleader = 0, @pNetLogin = '$netLogin', @pAllocationsID = ".$data['ID'].", @pAllocationsSPID = ".$data['allocationsSpId'].", @pAllocationsDutyID = ".$data['allocationsDutyId'].", @pDutyDate = '".$data['allocationsDate']."', $colFlag = $colFlagVal";
				$stmt = $this->pdo->prepare($query);
				$stmt->execute();
				$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return $row;
			}
		} catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
	}

    public function updateAllocation($data = [], $where = []): bool
    {
        return $this->update('dbo.Allocations', $data, $where);
    }

    private function update($table, $parameters = [], $conditions = []): bool
    {
        if (isset($parameters['ID'])) {
            unset($parameters['ID']);
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', array_map(
                fn($key) => "{$key} = :s_{$key}",
                array_keys($parameters)
            )),
            implode(' AND ', array_map(
                function ($key) {
                    if ($key == 'ID') {
                        //for better performance
                        return "cast ({$key} as nvarchar(4000)) IN(:w_{$key})";
                    } else {
                        return "{$key} = :w_{$key}";
                    }
                },
                array_keys($conditions)
            ))
        );

        $parameters = array_combine(
            array_map(fn($key) => ":s_{$key}", array_keys($parameters)),
            $parameters
        ) + array_combine(
            array_map(fn($key) => ":w_{$key}", array_keys($conditions)),
            $conditions
        );

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
            return true;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
            return false;
        }
    }

    /**
     * Return scduling people with additional teams
     *
     * @param Request $request
     * @param integer $isAdditionalTeamAvailable
     * @return array
     */
    public function getSchedulingPeople(Request $request, $isAdditionalTeamAvailable = 1)
    {
        try {
            $expWeekNum = explode('/', $request->get('weekNumber'));
            $reqWeekNum = $expWeekNum[1] . $expWeekNum[0];
            $reqTeamId = $request->get('teamId');

            $query = "SELECT DISTINCT spl.ScheduledPersonID,
						   UD_DisplayName as    FullName
					  FROM ScheduledPersonTeam_LINK AS spl
					INNER JOIN UserDetails ud on ud.UD_UserID = spl.ScheduledPersonID
					INNER JOIN ( SELECT MIN(td.dDateTime) AS StartDate, MAX(td.dDateTime) AS EndDate
								   FROM TimeDimension as td
								  WHERE td.ixYearWeek =  ? ) AS td1 ON 1 = 1
					WHERE spl.TeamID = ?
					   AND spl.scheduledType = 1
					   AND td1.EndDate >= ISNULL(spl.StartDate, td1.EndDate)
					   AND td1.StartDate <= ISNULL(spl.EndDate, td1.StartDate)
					   AND spl.IsHomeTeam IN(0,2) AND spl.IsAvailable in(0, 1)
					   AND NOT EXISTS ( SELECT 1
										  FROM Allocations AL
										 INNER JOIN AllocationsAddPersons AA ON AL.AL_AllocationsID = AA.AAP_AllocationsID
										 WHERE AA.AAP_SchedulingPersonID = spl.ScheduledPersonID
										   AND AA.AAP_Status = 1
										   AND AL.AL_WeekNumber = ?
										   AND AL.AL_SchedulingTeamID = ?
										 )
					ORDER BY FullName ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $reqWeekNum, PDO::PARAM_INT);
            $stmt->bindValue(2, $reqTeamId, PDO::PARAM_INT);
            $stmt->bindValue(3, $reqWeekNum, PDO::PARAM_INT);
            $stmt->bindValue(4, $reqTeamId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return [];
    }

    /**
     * Get allocation record by id
     *
     * @param [type] $id
     * @return array|null
     */
    public function getAllocationByID($allocationsDutyId)
    {
        try {
			if($allocationsDutyId == 'null' || $allocationsDutyId == 0)
			{
				$result = array( "AllocationsDutyID"=>0, "ID"=>0, "DutyName"=>'U', "Duration"=>0, "iDay"=>0, "StartTime"=>0, "EndTime"=>0, "dutyBreakTime"=>0, "DutyDate"=>'', "MasterDutyID"=>0, "DutyType"=>0, "DutyStatus"=>0, "dutyColorId"=>0, "Comments"=>'', "isAttention"=>0, "isRequest"=>0, "dutyProgramId"=>0, "DutyProgramId2"=>0, "DutyProgramId3"=>0, "DutyProgramId4"=>0, "DutyProgramId5"=>0, "DutyProgramId6"=>0, "PlannedDuration"=>0, "PlannedDutyBreakTime"=>0, "IsNeedCovering"=>'', "IsOverrideOver12"=> 1, "IsDutyEdited"=>0, "SchedulingPersonID"=>0, "CalculatedUnderElevenHrs"=> '', "UnderElevenComment"=>'', "OverrideUnderElevenHrs"=>'', "OverTwelveHrs"=>'', "IsOverseasOverTwelve"=>'', "MarkOverTwelve"=>'', "SchedulingTeamId"=>'');
				return $result;
			}else
			{
				$query = "SELECT ASP.ASP_AllocationsSPID, AD_AllocationsDutyID AllocationsDutyID, AD_AllocationsID ID, AD_DutyName DutyName,	AD_Duration Duration,	AD_iDay iDay, AD_StartTimeSec StartTime,	AD_EndTimeSec EndTime,	AD_DutyBreakTime dutyBreakTime,	AD_DutyDate DutyDate,	AD_MasterDutyID MasterDutyID,	AD_DutyType DutyType,	AD_DutyStatus DutyStatus,	AD_DutyColourID dutyColorId, AD_Comments Comments,	AD_isAttention isAttention,	AD_isRequest isRequest,	AD_DutyProgramID1 dutyProgramId, AD_DutyProgramID2 DutyProgramId2, AD_DutyProgramID3	 DutyProgramId3, AD_DutyProgramID4 DutyProgramId4, AD_DutyProgramID5 DutyProgramId5, AD_DutyProgramID6 DutyProgramId6 ,	AD_PlannedDuration PlannedDuration,	AD_PlannedDutyBreakTime PlannedDutyBreakTime, AD_IsNeedCovering IsNeedCovering,	AD_IsOverrideOver12 IsOverrideOver12, AD_IsDutyEdited IsDutyEdited,	AD_CreatedBy CreatedBy,	AD_CreatedDate CreatedDate,	AD_UpdatedBy UpdatedBy,	AD_UpdatedDate UpdatedDate,	AD_IsEditedDutyAttention IsEditedDutyAttention, ASP_CalculatedUnderElevenHrs CalculatedUnderElevenHrs, ASP_OverrideUnderElevenHrs OverrideUnderElevenHrs, ASP_UnderElevenComments UnderElevenComment, ASP_OverTwelveHrs OverTwelveHrs, ASP_IsOverseasOverTwelve IsOverseasOverTwelve, ASP_OverTwelveStatus MarkOverTwelve, AL.AL_SchedulingTeamID SchedulingTeamId
				 FROM Allocations AL 
                 INNER JOIN AllocationsDuties  ON AL_AllocationsID = AD_AllocationsID
                 LEFT JOIN AllocationsScheduledPersons ASP ON ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
                where AD_AllocationsDutyID = ?"
				;
				$stmt = $this->pdo->prepare($query);

				$stmt->bindValue(1, $allocationsDutyId, PDO::PARAM_INT);
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return $result;
			}
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Get achedule person details record by id
     *
     * @param [type] $id
     * @return array|null
     */
    public function getSchPersonByID($allocationsDutyId, $allocationsSpId)
    {
        try {
			$query = "SELECT ASP_AllocationsID AllocationsID, ASP_AllocationsDutyID AllocationsDutyID, ASP_SchedulingPersonID, ASP_iDay iDay, ASP_SortCode SortCode, ASP_LeaveStatus LeaveStatus, ASP_DutyDate DutyDate, ASP_WIADStatus WIADStatus, ASP_MarkedOverTime MarkedOverTime, ASP_OverTimeHours OverTimeHours, ASP_DutyTeamID DutyTeamID,
ASP_SigninStartTime SigninStartTime, ASP_SigninEndTime SigninEndTime, ASP_SigninStatus SigninStatus, ASP_SigninINBuilding SigninINBuilding, ASP_EDPStatus EDPStatus2, ASP_Comments Comments, ASP_LeaveStartTimeSec LeaveStartTime,
ASP_LeaveEndTimeSec LeaveEndTime, ASP_UnderElevenBreakStatus UnderElevenBreakStatus, ASP_CalculatedUnderElevenHrs CalculatedUnderElevenHrs,ASP_OverrideUnderElevenHrs OverrideUnderElevenHrs, ASP_CreatedBy CreatedBy, ASP_CreatedDate CreatedDate, ASP_UpdatedBy UpdatedBy, ASP_UpdatedDate UpdatedDate, ASP_RequestsStatus RequestsStatus, ASP_LockRequestsStatus LockRequestsStatus, ASP_ChargingStatus ChargingStatus, ASP_RequestsCount RequestsCount, ASP_LeaveType LeaveType, ASP_UnderElevenComments UnderElevenComments, ASP_OverTwelveStatus OverTwelveStatus, ASP_OverTwelveHrs OverTwelveHrs, ASP_IsOverseasOverTwelve IsOverseasOverTwelve, ASP_LeaveDuration LeaveDuration
			FROM AllocationsScheduledPersons where ASP_AllocationsSPID = ? AND ASP_AllocationsDutyID = ?"
			;
			$stmt = $this->pdo->prepare($query);

			$stmt->bindValue(1, $allocationsSpId, PDO::PARAM_INT);
			$stmt->bindValue(2, $allocationsDutyId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Get allocation records for user and date
     *
     * @param int $schedulingPersonId
     * @param string  $dutyDate
     * @return array|null
     */
    public function getAllocationByUserAndDate($schedulingPersonId, $dutyStartDate, $dutyEndDate = null)
    {
        if (is_null($dutyEndDate)) {
            $dutyEndDate = $dutyStartDate;
        }

        try {
            $query = "SELECT *
            FROM Allocations as a (NOLOCK)
            Where a.SchedulingPersonID = :id
            and (a.DutyDate >= Convert(datetime, :startDate, 101) and a.DutyDate <= Convert(datetime, :endDate, 101))"
            ;

            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id', $schedulingPersonId, PDO::PARAM_INT);
            $stmt->bindValue(':startDate', $dutyStartDate, PDO::PARAM_STR);
            $stmt->bindValue(':endDate', $dutyEndDate, PDO::PARAM_STR);
            $stmt->execute();

            return $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Add Duty and Person comments for the Allocation
     *
     * @param Request $request
     * @param integer $isAdditionalTeamAvailable
     * @return bool
     */
    public function addCommentsToAllocations(Request $request)
    {
        date_default_timezone_set('Europe/London');
        try {
            $allocationId = ($request->get('id') != '') ? $request->get('id') : 0;
            $dutyComments = ($request->get('DutyComments') != '') ? $request->get('DutyComments') : null;
            $personComments = ($request->get('PersonComments') != '') ? $request->get('PersonComments') : null;
            $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
            $isShiftLeader = ($request->get('isShiftLeader') != '') ? (int) $request->get('isShiftLeader') : 0;
			$allocationsDutyId = $request->get('allocationsDutyId') ? trim($request->get('allocationsDutyId')) : 0;
			$allocationsSpId = $request->get('allocationsSpId') ? trim($request->get('allocationsSpId')) : 0;
			$dutyDate = $request->get('allocationsDate') ?? $request->get('dutyDate');
			$pCommentType = $request->get('pCommentType') ? $request->get('pCommentType') : 0;
            $oldDutyComments = $request->get('oldDutyComments') ? $request->get('oldDutyComments') : '';
            $oldPersonComments = $request->get('oldPersonComments') ? $request->get('oldPersonComments') : '';
			$allocationsSchPer = $request->get('allocationsSchPer');
			$netLogin = $_SESSION['user']['user'];
			$commentCond = '';
			switch($pCommentType)
			{
				case 0: $commentCond = ", @pPersonComments = ?, @pDutyComments = ?";
						break;
				case 1: $commentCond = ", @pDutyComments = ?";
						break;
				case 2: $commentCond = ", @pPersonComments = ?";
						break;
			}
			$query = "exec usp_Edit_Allocations @EditType ='ADDCOMMENTS', @pSchedulingPersonID = ?, @pIsShiftleader = ?, @pNetLogin = ?, @pAllocationsID = ?, @pAllocationsSPID = ?, @pAllocationsDutyID = ?, @pDutyDate = ?, @pCommentType = ? $commentCond";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindValue(1, (int)$allocationsSchPer, PDO::PARAM_INT);
			$stmt->bindValue(2, (int)$isShiftLeader, PDO::PARAM_INT);
			$stmt->bindValue(3, $netLogin, PDO::PARAM_STR);
			$stmt->bindValue(4, (int)$allocationId, PDO::PARAM_INT);
			$stmt->bindValue(5, (int)$allocationsSpId, PDO::PARAM_INT);
			$stmt->bindValue(6, (int)$allocationsDutyId, PDO::PARAM_INT);
			$stmt->bindValue(7, $dutyDate, PDO::PARAM_STR);
			$stmt->bindValue(8,(int)$pCommentType, PDO::PARAM_INT);
			switch($pCommentType)
			{
				case 0: $stmt->bindValue(9, $personComments, PDO::PARAM_STR);
						$stmt->bindValue(10, $dutyComments, PDO::PARAM_STR);
						break;
				case 1: $stmt->bindValue(9, $dutyComments, PDO::PARAM_STR);
						break;
				case 2: $stmt->bindValue(9, $personComments, PDO::PARAM_STR);
						break;
			}    			
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return ($result["SPExecStatus"] == "0") ? true : false;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /*
    * Set allocation comment
    */
    function setComment($allocationId, $dutyDate, $scheduledPersonId, $role, $comment = '', $commentType = 2, $dutyId = 0) {
        $request = Request::createFromGlobals();
        if($scheduledPersonId > 0 && !empty($scheduledPersonId)) {
            //Add comments
            $pdo = OpenDBLinkA7();
            $sqlQuery = "select ASP_AllocationsSPID, ASP_AllocationsDutyID
            from AllocationsScheduledPersons WHERE ASP_SchedulingPersonID = :schedulingPersonID AND ASP_AllocationsID = :allocationID AND ASP_DutyDate = :dutyDate ";
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':schedulingPersonID', $scheduledPersonId, PDO::PARAM_INT);
            $stmt->bindValue(':allocationID', $allocationId, PDO::PARAM_INT);
            $stmt->bindValue(':dutyDate', $dutyDate, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $request->request->set('id', $allocationId);
            $request->request->set('isShiftLeader', $role);
            $request->request->set('allocationsDutyId', $result['ASP_AllocationsDutyID'] ?? 0);
            $request->request->set('allocationsSpId', $result['ASP_AllocationsSPID'] ?? 0);
            $request->request->set('allocationsSchPer', $scheduledPersonId);
            $request->request->set('dutyDate', $dutyDate);
            $request->request->set('pCommentType', $commentType);
        } else if($commentType = 1 && $dutyId > 0) {
            //Add duty comments
            $request->request->set('id', $allocationId);
            $request->request->set('isShiftLeader', $role);
            $request->request->set('allocationsDutyId', $dutyId);
            $request->request->set('allocationsSpId', '');
            $request->request->set('allocationsSchPer', '');
            $request->request->set('dutyDate', $dutyDate);
            $request->request->set('pCommentType', $commentType);
        }
		$this->addCommentsToAllocations($request);
    }

    /**
     * Get Allocation Duty and Person Comments
     *
     * @param Request $request
     * @param integer $isAdditionalTeamAvailable
     * @return array
     */
    public function getAllocationComments(Request $request)
    {
        try {
            $query = "SELECT 
                        CASE WHEN AD_DutyName IS NULL THEN 'U'
                             WHEN AD_DutyType IN (8,11)
                             THEN 
                                CASE WHEN ASP_LeaveType = 1 THEN 'Leave'
                                     WHEN ASP_LeaveType = 2 THEN 'OFF Leave'
                                     WHEN ASP_LeaveType = 3 THEN 'Sick'
                                     WHEN ASP_LeaveType = 4 THEN 'U-Sick'
                                     WHEN ASP_LeaveType = 5 THEN '-Sick'
                                     WHEN ASP_LeaveType = 7 THEN 'Absent'
                               END
                              ELSE AD_DutyName
                       END  AS DutyName, 
                        ISNULL(AD_Comments,'') AS DutyComments, 
                        CASE WHEN AD_AllocationsID = ASP_AllocationsID AND AAP_AllocationsID IS NULL
                                THEN ISNULL(ASP_Comments,'') 
                            WHEN AAP_AllocationsID IS NOT NULL
                                THEN ISNULL(AAP_Comments,'') END AS PersonComments, 
                        AD_IsDutyEdited isEdited 
                        FROM AllocationsDuties 
                        LEFT JOIN AllocationsScheduledPersons on ASP_AllocationsDutyID = AD_AllocationsDutyID 
                        AND ASP_AllocationsSPID = ?
                        LEFT JOIN AllocationsAddPersons on ASP_AllocationsSPID = AAP_AllocationsSPID AND AAP_AllocationsID = ?
                        WHERE AD_AllocationsDutyID = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $request->get('allocationsSpId'), PDO::PARAM_INT);
			$stmt->bindValue(2, $request->get('id'), PDO::PARAM_INT);
            $stmt->bindValue(3, $request->get('allocationsDutyId'), PDO::PARAM_INT);
            //$stmt->bindValue(4, $request->get('id'), PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get Allocation Details
     *
     * @param Request $request
     * @param integer $schedulingpersonid,weeknumber,iday,teamid
     * @return array result or error in logfile
     */
    public function getAllocationDetailsByStaff(Request $request)
    {
        try {
            $query = "exec [dbo].[usp_GET_getAllocationsDetailsByStaffNumber] ?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $request->get('rolepermission'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('allocationId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get Master & Misc Duties Name by FilterID
     *
     * @param Request $request
     * @return array
     */
    public function getMasterMiscFilterDuties(Request $request)
    {
        try {
            $query = "SELECT DISTINCT md.DutyName FROM MasterDutiesFilterLinks mdfl
                      JOIN MasterDuties md ON md.MasterDutyID = mdfl.MasterDutyID
                      WHERE mdfl.FilterID=:filterID";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':filterID', $request->get('mastMiscFilterId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get Duty Allocated Jobs
     *
     * @param Request $request
     * @return array
     */
    public function getDutyAllocatedJobs(Request $request)
    {
        try {
            $query = "SELECT AJ_JobName JobName, AJ_JobStartTimeLocal StartTime, AJ_JobEndTimeLocal EndTime FROM Allocationsjobs (NOLOCK) WHERE AJ_AllocationsDutyID=:AllocationsDutyID AND AJ_JobStatus = 1 AND AJ_JobName!='Leave'  order by AJ_JobStartTimeLocal";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':AllocationsDutyID', $request->get('AllocationsDutyID'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Delete from allocatons table based on conditions
     *
     * @param where conditions
     * @return bool
     */
    public function deleteAllocation($where = []): bool
    {
        return $this->delete('dbo.Allocations', $where);
    }

    private function delete($table, $conditions = []): bool
    {
        $sql = sprintf(

            'DELETE FROM  %s WHERE %s',
            $table,

            implode(' AND ', array_map(
                fn($key) => "{$key} = :w_{$key}",
                array_keys($conditions)
            ))
        );

        $parameters = array_combine(
            array_map(fn($key) => ":w_{$key}", array_keys($conditions)),
            $conditions
        );

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
            return true;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
            return false;
        }
    }

    /**
     * Get Allocation Details
     *
     * @param Request $request
     *
     * @return string result or error in logfile
     */
    public function addAllocationHistory(Request $request)
    {
        try {
            $query = "exec ? = [dbo].[usp_mod_AllocationHistory] ?,?,?,?,?, ?";
            $historySubType = $request->get('historySubType') ?: 'CH';
            $spresult = 0;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $spresult, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
            $stmt->bindValue(2, $request->get('attributeId'), PDO::PARAM_INT);
            $stmt->bindValue(3, $request->get('historyType'), PDO::PARAM_INT);
            $stmt->bindValue(4, $request->get('userId'), PDO::PARAM_INT);
            $stmt->bindValue(5, $request->get('message'), PDO::PARAM_STR);
            $stmt->bindValue(6, 0, PDO::PARAM_INT);
            $stmt->bindValue(7, $historySubType, PDO::PARAM_STR);
            $stmt->execute();
            return $spresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get Master Duties DetailsBy ID
     *
     * @param Request $request
     * @return string result or error in logfilr
     */
    public function getMasterDutiesDetailsByID(Request $request)
    {
        try {

            $intAreaID = 0;
            $intDutyType = 0;
            $intDutyID = $request->get('dutyID');

            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_MasterDutyDetails] ?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $intAreaID, PDO::PARAM_INT);
            $stmt->bindParam(2, $intDutyType, PDO::PARAM_INT);
            $stmt->bindParam(3, $intDutyID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $jsonresult = json_encode($result);

            return $jsonresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get Master Duties DetailsBy ID
     *
     * @param Request $request
     * @return string
     */
    public function getAllocationsWeekByTeamID(Request $request, $endWeek)
    {
        try {

            $startWeek = $request->get('startWeek');
            $teamid = $request->get('teamId');

            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_AllocationsWeekByTeamID] ?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $startWeek, PDO::PARAM_STR);
            $stmt->bindParam(2, $endWeek, PDO::PARAM_STR);
            $stmt->bindParam(3, $teamid, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $jsonresult = json_encode($result);
            return $jsonresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to get Allocated Duty Data From Edit Table based on the AllocationID
     *
     * @param $allocationId This param contains the AllocationID Information
     * @return array
     */
    public function getAllocatedDutiesEdit($allocationId = 0)
    {
        try {
            $query = "SELECT TOP 1 ae.*, ae.AllocationID as AllocID,
                (CASE
                    WHEN mdc.ColourBackground IS NULL THEN '' ELSE mdc.ColourBackground
                END) AS ColourBackground,
                (CASE
                    WHEN mdc.ColourFont IS NULL THEN '' ELSE mdc.ColourFont
                END) AS ColourFont
                FROM  dbo.Allocations_edit ae
                LEFT JOIN REF_MasterDutyColours mdc on mdc.MasterDutyColourID = ae.dutyColorId
                WHERE ae.AllocationID = :allocationId";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':allocationId', $allocationId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return '';
    }

    /**
     * This function is used to get Duty Shiftcounting Filters
     *
     * @param Request $request
     * @param $filterId This param contains the fefilterId information for 0 -> Fetch all.
     * @param $isActive This param contains the isActive information.
     * @return string result or error in logfile
     */
    public function getDutyShiftCountingFilters(Request $request, $filterId = 0, $isActive = 1)
    {
        try {
            $query = "exec [dbo].[usp_GET_DutyShiftCountingFilters] ?,?,?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $request->get('userId'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(3, $isActive, PDO::PARAM_INT);
            if ($filterId == 0) {
                $stmt->bindValue(4, $filterId, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt->bindValue(4, $filterId, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to get Scheduling Team Details based on Scheduling Team ID.
     *
     * @param Request $request
     *
     * @return string result or error in logfile
     */
    public function getSchedulingTeamDetails(Request $request)
    {
        try {
            $query = "exec [dbo].[usp_SearchSchedulingTeamDetails] ?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $request->get('userId'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to check duplicate shiftcounting filter name for the particular person in team
     *
     * @param Request $request
     * @return array
     */
    public function checkDuplicateDutyShiftcountingFilter(Request $request)
    {
        try {
            $query = "SELECT ID AS isExist,isActive FROM DutyShiftcountingFilters WHERE FilterName=:FilterName AND UserID=:UserID AND SchedulingTeamId=:teamId";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':FilterName', $request->get('filterName'), PDO::PARAM_STR);
            $stmt->bindValue(':UserID', $request->get('userId'), PDO::PARAM_INT);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return 0;
    }

    /**
     * This function is used to Add Duty Shiftcounting Filters
     *
     * @param Request $request
     *
     * @return integer result or error in logfile
     */
    public function saveDutyShiftCountingFilter(Request $request)
    {
        try {
            $query = "exec ? = [dbo].[usp_SET_DutyShiftCountingFilter] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
            $spresult = 0;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $spresult, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
            $stmt->bindValue(2, $request->get('filterName'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('userId'), PDO::PARAM_INT);
            $stmt->bindValue(4, $request->get('SchedulingTeamId'), PDO::PARAM_INT);
            $stmt->bindValue(5, $request->get('isPublic'), PDO::PARAM_INT);
            $stmt->bindValue(6, $request->get('isActive'), PDO::PARAM_INT);
            $stmt->bindValue(7, $request->get('detailsShow'), PDO::PARAM_INT);
            $stmt->bindValue(8, $request->get('showTotalCount'), PDO::PARAM_INT);
            $stmt->bindValue(9, $request->get('colA'), PDO::PARAM_INT);
            $stmt->bindValue(10, $request->get('colB'), PDO::PARAM_INT);
            $stmt->bindValue(11, $request->get('colC'), PDO::PARAM_INT);
            $stmt->bindValue(12, $request->get('colD'), PDO::PARAM_INT);
            $stmt->bindValue(13, $request->get('colE'), PDO::PARAM_INT);
            $stmt->bindValue(14, $request->get('colF'), PDO::PARAM_INT);
            $stmt->bindValue(15, $request->get('colG'), PDO::PARAM_INT);
            $stmt->bindValue(16, $request->get('colH'), PDO::PARAM_INT);
            $stmt->bindValue(17, $request->get('colI'), PDO::PARAM_INT);
            $stmt->bindValue(18, $request->get('colJ'), PDO::PARAM_INT);
            $stmt->bindValue(19, $request->get('colK'), PDO::PARAM_INT);
            $stmt->bindValue(20, $request->get('colL'), PDO::PARAM_INT);
            $stmt->bindValue(21, $request->get('colM'), PDO::PARAM_INT);
            $stmt->bindValue(22, $request->get('colN'), PDO::PARAM_INT);
            $stmt->bindValue(23, $request->get('colO'), PDO::PARAM_INT);
            $stmt->bindValue(24, $request->get('colP'), PDO::PARAM_INT);
            $stmt->bindValue(25, $request->get('colQ'), PDO::PARAM_INT);
            $stmt->bindValue(26, $request->get('colR'), PDO::PARAM_INT);
            $stmt->bindValue(27, $request->get('colS'), PDO::PARAM_INT);
            $stmt->bindValue(28, $request->get('colT'), PDO::PARAM_INT);
            $stmt->bindValue(29, $request->get('colU'), PDO::PARAM_INT);
            $stmt->bindValue(30, $request->get('colV'), PDO::PARAM_INT);
            $stmt->bindValue(31, $request->get('colW'), PDO::PARAM_INT);
            $stmt->bindValue(32, $request->get('colX'), PDO::PARAM_INT);
            $stmt->bindValue(33, $request->get('colY'), PDO::PARAM_INT);
            $stmt->bindValue(34, $request->get('colZ'), PDO::PARAM_INT);
            $stmt->bindValue(35, $request->get('type'), PDO::PARAM_STR);
            $stmt->execute();
            return $spresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return 0;
    }

    /**
     * Get Adhoc Duty
     *
     * @param  Request $request
     * @return array
     */
    public function getAdhocDuties($request)
    {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_GetAdhocDutyByTeam] ?, ?, ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('startDate'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('endDate'), PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = [];
            $counter = 0;
            //Create separate instance for each day
            foreach($result as $data) {
                $period = new CarbonPeriod($data['StartDate'], $data['EndDate']);
                foreach($period as $date) {
                    $response[$counter] = $data;
                    $response[$counter]['DutyDate'] = $date->format('Y-m-d');
                    $counter++;
                }
            }
            return $response;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * get active pre defined scikness list
     *
     * @param none
     * @return array
     */
    public function getSicknessList()
    {
        try {
            $sql = "exec [dbo].[usp_GET_SicknessReasons]";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $resultUserSetup = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($resultUserSetup);
        } catch (Exception $e) {
            logger()->critical('DB ERROR', (array) $e);
            return false;
        }
        return [];
    }

    /**
     * Check Duty name on Allocations
     *
     * @param $masterduties
     * @return array
     */
    public function getDutyForUnallocatedCheck($masterDuties)
    {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_get_CheckDutyNameOnUnallocatedDuty] ?,?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $masterDuties['DutyName'], PDO::PARAM_STR);
            $stmt->bindValue(2, $masterDuties['DutyID'], PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {

            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Calaculate the Allocation per day of unallocted duties
     *
     * @param Request $request
     * @return array
     */
    public function getUnallocatedAllocationPerDay(Request $request)
    {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_get_AllocationPerDayUnalloacted] ?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $request->get('dayvalue'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('teamId'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('startWeek'), PDO::PARAM_STR);
            $stmt->bindValue(4, $request->get('dutyID'), PDO::PARAM_STR);
            $stmt->bindValue(5, $request->get('idayno'), PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Delete the Unallocted Duty
     *
     * @param Request $request
     * @return array
     */
    public function DeleteUnalloctedDutyFromAllocations(Request $request)
    {

        try {
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_del_DeleteUnalloctedDutyFromAllocations] ?,?,?,?";
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(1, $request->get('weekNumber'), PDO::PARAM_STR);
            $stmt->bindValue(2, $request->get('dutyID'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('idayno'), PDO::PARAM_INT);
            $stmt->bindValue(4, $request->get('deletedayvalue'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {

            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }
    /* This function is used to Delete Duty Shiftcounting Filters
     *
     * @param Request $request
     *
     * @return boolean result or error in logfile
     */
    public function deleteDutyCountFilter(Request $request)
    {
        try {
            $query = "exec [dbo].[usp_DEL_DutyShiftcountingFilter] ?,?,?,?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $request->get('userId'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(3, $request->get('filterId'), PDO::PARAM_INT);
            $stmt->bindValue(4, $request->get('staRole'), PDO::PARAM_INT);
            $stmt->bindValue(5, 1, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to Activate Duty Shiftcounting Filters
     *
     * @param Request $request
     * @return boolean
     */
    public function activateDutyCountFilter(Request $request)
    {
        try {
            $query = "UPDATE DutyShiftcountingFilters SET isActive=1 WHERE ID=:filterId AND UserID=:UserID AND SchedulingTeamId=:teamId";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':filterId', $request->get('filterId'), PDO::PARAM_INT);
            $stmt->bindValue(':UserID', $request->get('userId'), PDO::PARAM_INT);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * Delete Adhoc Duty by Id
     *
     * @param Request $request
     *
     * @return bool
     */
    public function deleteAdhocDuty(Request $request)
    {
        try {
            $netLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $adhocID = $request->get('id');
            $teamid = $request->get('teamid');
            $date = $request->get('date');
            $adHocDutyDetails = $this->getAdhocDutyByID($adhocID);
            $startDate = Carbon::parse($adHocDutyDetails['StartDate']);
            $endDate = Carbon::parse($adHocDutyDetails['EndDate']);
            $dutyDate = Carbon::parse($date);
            /*
            * If Adhoc master duty is between a date range create new records with corrent start and end date, Since we create a single record for a date range
            * Such that the record for that day will be only deleted
            */
            $intDay = '';
			if($adHocDutyDetails['StartDate'] != $adHocDutyDetails['EndDate'])
			{
				$diff = $startDate->diffInDays($endDate);
                $query = "update MasterDuties set Saturday = 0, Sunday = 0, Monday = 0, Tuesday = 0, Wednesday = 0, Thursday = 0, Friday = 0 where MasterDutyID = ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(1, $adhocID, PDO::PARAM_INT);
                $stmt->execute();
				
				if($diff == 1)
				{
					if($dutyDate == $startDate)
					{
                        $intDay .= mb_strtolower($endDate->format('l')).' =1';
						$query = "update MasterDuties set StartDate = ?, $intDay where MasterDutyID = ?";
						$stmt = $this->pdo->prepare($query);
						$stmt->bindValue(1, $endDate->format('Y-m-d'), PDO::PARAM_STR);
						$stmt->bindValue(2, $adhocID, PDO::PARAM_INT);
						$stmt->bindValue(2, $adhocID, PDO::PARAM_INT);
						$stmt->execute();
					}else
					{
                        $intDay .= mb_strtolower($startDate->format('l')).' =1';
						$query = "update MasterDuties set EndDate = ?, $intDay  where MasterDutyID = ?";
						$stmt = $this->pdo->prepare($query);
						$stmt->bindValue(1, $startDate->format('Y-m-d'), PDO::PARAM_STR);
						$stmt->bindValue(2, $adhocID, PDO::PARAM_INT);
						$stmt->execute();
					}
				}else
				{
					if($dutyDate == $startDate)
					{
						$newStartDate = (clone $dutyDate)->addDays(1);
                        $period = new CarbonPeriod($newStartDate, $endDate);
                        foreach($period->toArray() as $date) {
                            $intDay .= mb_strtolower($date->format('l')) . ' =1,';
                        }
						$intDay = rtrim($intDay,',');
						$occ = substr_count($intDay, 'saturday');
						if($occ > 1)
						{
							$pos = strrpos($intDay, 'saturday');
							$intDay = substr($intDay, $pos);
						}

                        $query = "update MasterDuties set StartDate = ?, $intDay  where MasterDutyID = ?";
						$stmt = $this->pdo->prepare($query);
						$stmt->bindValue(1, $newStartDate->format('Y-m-d'), PDO::PARAM_STR);
						$stmt->bindValue(2, $adhocID, PDO::PARAM_INT);
						$stmt->execute();
					}elseif($dutyDate == $endDate)
					{
						$newEndDate = (clone $dutyDate)->subDays(1);
                        $period = new CarbonPeriod($startDate, $newEndDate);
                        foreach($period->toArray() as $date) {
                            $intDay .= mb_strtolower($date->format('l')) . ' =1,';
                        }
						$intDay = rtrim($intDay,',');
						$occ = substr_count($intDay, 'saturday');
						if($occ > 1)
						{
							$pos = strrpos($intDay, 'saturday');
							$intDay = substr($intDay, $pos);
						}

						$query = "update MasterDuties set EndDate = ?, $intDay  where MasterDutyID = $adhocID";
						$stmt = $this->pdo->prepare($query);
						$stmt->bindValue(1, $newEndDate->format('Y-m-d'), PDO::PARAM_STR);
						$stmt->bindValue(2, $adhocID, PDO::PARAM_INT);
						$stmt->execute();
					}else
					{
						$newStartDate = (clone $dutyDate)->subDays(1);
                        $period = new CarbonPeriod($startDate, $newStartDate);
                        foreach($period->toArray() as $date) {
                            $intDay .= mb_strtolower($date->format('l')) . ' =1,';
                        }
						$intDay = rtrim($intDay,',');
						$occ = substr_count($intDay, 'saturday');
						if($occ > 1)
						{
							echo $pos = strrpos($intDay, 'saturday');
							echo $intDay = substr($intDay, $pos);
						}
                        $query = "update MasterDuties set EndDate = ?, $intDay  where MasterDutyID = ?";
						$stmt = $this->pdo->prepare($query);
						$stmt->bindValue(1, $newStartDate->format('Y-m-d'), PDO::PARAM_STR);
						$stmt->bindValue(2, $adhocID, PDO::PARAM_INT);
						$stmt->execute();
						$this->createAdHoc($adHocDutyDetails, $request, (clone $dutyDate)->addDays(1), $endDate);
					}
				}
			}else
			{
				$sql = "Update MasterDuties set IsActive = 0 where MasterDutyID = ?";
				$stmt = $this->pdo->prepare($sql);
				$stmt->bindValue(1, $adhocID, PDO::PARAM_INT);
				$stmt->execute();
			}
            return true;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }
    /**
     * get consecutive sickness record of user
     *@param Request $request
     * @return array
     */
    public function getConsecutiveSicknessRecord($sickDate, $schedulingPersonId)
    {

        try {
            $sql = "exec [dbo].[usp_GET_ConsecutiveSicknessRecord] ?,?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $sickDate, PDO::PARAM_STR);
            $stmt->bindValue(2, $schedulingPersonId, PDO::PARAM_INT);
            $stmt->execute();
            $resultData = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultData;
        } catch (Exception $e) {
            logger()->critical('DB ERROR', (array) $e);
            return false;
        }
        return [];
    }

    /**
     * Get scheduled persons record of user
     *
     * @param Request $request
     * @return array
     */
    public function getSchedulePersondetailsByID(Request $request)
    {
        $personId = $request->get('schedulingPersonId', $request->get('dataval')['schedulingPersonId']);

        try {
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_Schedulepersonteamsdetails] ?";
            $stmt = $this->pdo->prepare($sql);
            // The parameters
            $stmt->bindValue(1, $personId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            logger()->critical('DB ERROR', (array) $e);
        }

        return [];
    }

    /**
     * get scheduled team record of user
     *@param Request $request
     * @return array
     */
    public function getScheduleTeamsdetailsByID(Request $request)
    {
        try {
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_ScheduleTeamsdetails] ?";
            $stmt = $this->pdo->prepare($sql);
            // The parameters
            $stmt->bindValue(1, $request->get('dataval')['teamId'], PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultjson = json_encode($result);
            return $resultjson;
        } catch (Exception $e) {
            logger()->critical('DB ERROR', (array) $e);
            return false;
        }
        return [];
    }

    /**
     * Get allocation records for user and date
     *
     * @param int $schedulingPersonId
     * @param string  $dutyDate
     * @return array|null
     */
    public function getUserSicknessRecord($schedulingPersonId, $dutyDate)
    {
        try {
            $query = "SELECT asi.ReasonsId  SicknessReasonsId,
                asi.Totalhrs  as Hours,
                asi.Comments as   Comment,
                asi.AllocationsSPID as  SicknessAllocationsiD,
                asi.isChecked,
                1 MarkedSickness,
                ID as SicknessID
            FROM LeaveApplications as asi (nolock)
            JOIN AllocationsScheduledPersons al on al.ASP_SchedulingPersonID = asi.SchedulingPersonID
                and al.ASP_DutyDate = asi.dDate
            Where asi.SchedulingPersonID = :id
            and asi.dDate = CAST( :date AS DATE)
            and asi.LeaveTypeID IN (3,4,5)
            AND asi.Approved = 1
            AND asi.Deleted = 0
            ";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $schedulingPersonId, PDO::PARAM_INT);
            $stmt->bindValue(':date', $dutyDate, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return null;
    }

    /**
     * Insert sickness record
     *
     * @param $request
     * @return array|null
     */
    public function insertSicknessRecord(Request $request)
    {

        try {
            $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
            $sql = "exec [dbo].[usp_mod_AllocationSicknessRecord] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
            $stmt = $this->pdo->prepare($sql);
            // The parameters
            $stmt->bindValue(1, $request->get('js_sicknessid'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('js_action'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('scheduledpersonId'), PDO::PARAM_INT);
            $stmt->bindValue(4, $request->get('staffnumber'), PDO::PARAM_STR);
            $stmt->bindValue(5, $request->get('hours'), PDO::PARAM_INT);
            $stmt->bindValue(6, $request->get('reasonlist'), PDO::PARAM_INT);
            $stmt->bindValue(7, $request->get('js_date'), PDO::PARAM_STR);
            $stmt->bindValue(8, $sessUserId, PDO::PARAM_INT);
            $stmt->bindValue(9, $request->get('checkbox'), PDO::PARAM_INT);
            $stmt->bindValue(10, $request->get('sickStartDate'), PDO::PARAM_STR);
            $stmt->bindValue(11, $request->get('sickEndDate'), PDO::PARAM_STR);
            $stmt->bindValue(12, $request->get('setcheckoption'), PDO::PARAM_STR);
            $stmt->bindValue(13, $request->get('comments'), PDO::PARAM_STR);
            $stmt->bindValue(14, $request->get('js_allocationid'), PDO::PARAM_INT);
            $stmt->bindValue(15, $request->get('synctype'), PDO::PARAM_INT);
            $stmt->bindValue(16, ($request->get('allocationsSpId') == 'null' || empty($request->get('allocationsSpId')) ? 0 : $request->get('allocationsSpId')), PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultjson = json_encode($result);
            return $resultjson;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return null;
    }
    /**
     * This function is used to Make Duty Shiftcounting Filter Public
     *
     * @param Request $request
     * @return boolean
     */
    public function setPublicDutyCountFilter(Request $request)
    {
        try {
            $returnData['status'] = false;
            if (($request->get('orgIsPublic') == 0) && ($request->get('isPublic') == 1)) {
                $getFilterDetailQuery = "SELECT FilterName FROM DutyShiftcountingFilters WHERE ID=:filterId";
                $stmtFilterDetailQuery = $this->pdo->prepare($getFilterDetailQuery);
                $stmtFilterDetailQuery->bindValue(':filterId', $request->get('filterId'), PDO::PARAM_INT);
                $stmtFilterDetailQuery->execute();
                $resultFilterDetailQuery = $stmtFilterDetailQuery->fetch(PDO::FETCH_ASSOC);
                if (!empty($resultFilterDetailQuery)) {
                    $chkQuery = "SELECT COUNT(FilterName) AS filterCount FROM DutyShiftcountingFilters WHERE FilterName=:filterName AND SchedulingTeamId=:teamId AND isPublic=1";
                    $stmtQuery = $this->pdo->prepare($chkQuery);
                    $stmtQuery->bindValue(':filterName', $resultFilterDetailQuery['FilterName'], PDO::PARAM_STR);
                    $stmtQuery->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
                    $stmtQuery->execute();
                    $resultQuery = $stmtQuery->fetch(PDO::FETCH_ASSOC);
                    if (!empty($resultQuery) && $resultQuery['filterCount'] > 0) {
                        $returnData['message'] = 'Filter with this name already exists';
                    } else {
                        $query = "UPDATE DutyShiftcountingFilters SET isPublic=:isPublic WHERE ID=:filterId AND UserID=:UserID AND SchedulingTeamId=:teamId";
                        $stmt = $this->pdo->prepare($query);
                        $stmt->bindValue(':filterId', $request->get('filterId'), PDO::PARAM_INT);
                        $stmt->bindValue(':UserID', $request->get('userId'), PDO::PARAM_INT);
                        $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
                        $stmt->bindValue(':isPublic', $request->get('isPublic'), PDO::PARAM_INT);
                        if ($stmt->execute()) {
                            $returnData['status'] = true;
                            $returnData['message'] = '';
                        }
                    }
                } else {
                    $query = "UPDATE DutyShiftcountingFilters SET isPublic=:isPublic WHERE ID=:filterId AND UserID=:UserID AND SchedulingTeamId=:teamId";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':filterId', $request->get('filterId'), PDO::PARAM_INT);
                    $stmt->bindValue(':UserID', $request->get('userId'), PDO::PARAM_INT);
                    $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
                    $stmt->bindValue(':isPublic', $request->get('isPublic'), PDO::PARAM_INT);
                    if ($stmt->execute()) {
                        $returnData['status'] = true;
                        $returnData['message'] = '';
                    }
                }
            } else {
                $query = "UPDATE DutyShiftcountingFilters SET isPublic=:isPublic WHERE ID=:filterId AND UserID=:UserID AND SchedulingTeamId=:teamId";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':filterId', $request->get('filterId'), PDO::PARAM_INT);
                $stmt->bindValue(':UserID', $request->get('userId'), PDO::PARAM_INT);
                $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
                $stmt->bindValue(':isPublic', $request->get('isPublic'), PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $returnData['status'] = true;
                    $returnData['message'] = '';
                }
            }

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return $returnData;
    }

    /**
     * This function is used to insert miscellaneous duty data in Allocations table
     *
     * @param $masterDutyId This param contains the master duty id information
     * @param $targetId This param contains the target id information
     *
     * @return boolean
     */
    public function setMiscDutyInAllocation(Request $request)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$scheduledPersonId = $request->get('scheduledPersonId');
            $dataTargetId = $request->get('dataTargetId');
            $dataMasterDutyId = $request->get('dataMasterDutyId');
            $dutyDate = $request->get('dutyDate');
            $dropAllocationsSpId = $request->get('dropAllocationsSpId');

            $query = "exec usp_Edit_Allocations @EditType = 'ASSIGNMISCDUTY', @pNetLogin = '$sessUserNetId', @pAllocationsID = $dataTargetId, @FromID = $dropAllocationsSpId, @pMasterDutyID = $dataMasterDutyId, @pSchedulingPersonID = $scheduledPersonId, @pDutyDate = '".$dutyDate."'";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $respdata = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!isset($respdata[0]['spStatus']) && !empty($respdata)) {
                $returnData['spStatus'] = true;
                $returnData['spData'] = $respdata;
            }
            if (isset($respdata[0]['SPExecStatus']) && ($respdata[0]['SPExecStatus'] > 0)) {
                $returnData['spStatus'] = false;
                $returnData['errorMessage'] = $respdata[0]['SPMessage'];
            }else
			{
				$returnData['spStatus'] = true;
                $returnData['errorMessage'] = 'Success';
			}
            return json_encode($returnData, true);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * Get AdhocDuty record by id
     *
     * @param [type] $id
     * @return array|null
     */
    public function getAdhocDutyByID($id)
    {
        try {
            $query = "SELECT *
            FROM MasterDuties as a
            Where a.MasterDutyID = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return null;
    }

    /**
     * Update Adhoc Duty by Id
     *
     * @param Request $request
     *
     * @return string
     */
    public function updateAdhocDuty(Request $request)
    {
        try {
            $adhocID = $request->get('AdhocID');
            $Comment = ($request->get('Comment') != '') ? $request->get('Comment') : null;
            $DutyComment = ($request->get('DutyComment') != '') ? $request->get('DutyComment') : null;
            if((!empty($Comment) && !empty($DutyComment)) || (!empty($Comment)) || (!empty($DutyComment))){
                $finalComment = $Comment . '__COMMENT_SEPARETOR__' . $DutyComment;
            }else{
                $finalComment = '';
            }

            $startTimeArray = explode(":", $request->get('StartTime'));
            $intStartTime = $startTimeArray[0] * 3600 + $startTimeArray[1] * 60;

            $endTimeArray = explode(":", $request->get('EndTime'));
            $intEndTime = $endTimeArray[0] * 3600 + $endTimeArray[1] * 60;

            $breakTimeHour = $request->get('breakTimeHour');
            $breakTimeMinute = $request->get('breakTimeMinute');
            $strBreakTime = (($breakTimeHour * 3600) + ($breakTimeMinute * 60));
            $isNeedCovering = isset($_REQUEST['isNeedCovering']) && $_REQUEST['isNeedCovering'] == 'on' ? 0 : 1;
            if (($intStartTime == 0) && ($intEndTime == 0)) {
                $intEndTime = 86400;
            }

            if ($intStartTime < $intEndTime) {
                $duration = $intEndTime - $intStartTime;
            } else {
                $duration = (86400 - $intStartTime) + $intEndTime;
            }

            if ($strBreakTime > $duration) {
                $resultJson = "BreakTime-Error";
            } else {
                $adHocDutyDetails = $this->getAdhocDutyByID($adhocID);
                $startDate = Carbon::parse($adHocDutyDetails['StartDate']);
                $endDate = Carbon::parse($adHocDutyDetails['EndDate']);
                $dutyDate = Carbon::parse($request->get('dutyDate'));

                /*
                * If Adhoc master duty is between a date range create new records with corrent start and end date, Since we create a single record for a date range
                * Such that the already existing records acts as new updated record
                */
				if($adHocDutyDetails['StartDate'] != $adHocDutyDetails['EndDate'])
				{
					if($startDate->isBefore($dutyDate)) {
						$this->createAdHoc($adHocDutyDetails, $request, $startDate, (clone $dutyDate)->subDays(1));
					}
					if($endDate->isAfter($dutyDate)) {
						$this->createAdHoc($adHocDutyDetails, $request, (clone $dutyDate)->addDays(1), $endDate);
					}
				}
                $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
                $days = [mb_strtolower($dutyDate->format('l')) => 1];
                $startDateWeek = GetAllocationWeekandDay($dutyDate->format('Y-m-d'))['ixYearWeek'];
	            $endDateWeek = GetAllocationWeekandDay($dutyDate->format('Y-m-d'))['ixYearWeek'];
                //Update
                $query = "UPDATE dbo.MasterDuties SET
            				DutyComment = :Comment,
            				StartTime = :StartTime,
            				EndTime = :EndTime,
            				DutyName = :DutyName,
            				DutyColourID = :DutyColourID,
                            BreakTime = :BreakTime,
                            Duration = :Duration,
                            LastModBy = :currentuserID,
                            LastModDate = GETUTCDATE(),
                            IsNeedCovering = :isNeedCovering,
                            StartDate = :StartDate,
                            EndDate = :EndDate,
                            Saturday = :Saturday,
                            Sunday = :Sunday,
                            Monday = :Monday,
                            Tuesday = :Tuesday,
                            Wednesday = :Wednesday,
                            Thursday = :Thursday,
                            Friday = :Friday,
                            StartWeek = :startWeek,
                            EndWeek = :endWeek
			            WHERE MasterDutyID = :ID";

                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':ID', $adhocID, PDO::PARAM_INT);
                $stmt->bindValue(':Comment', $finalComment, PDO::PARAM_STR);
                $stmt->bindValue(':StartTime', $intStartTime, PDO::PARAM_INT);
                $stmt->bindValue(':EndTime', $intEndTime, PDO::PARAM_INT);
                $stmt->bindValue(':DutyName', $request->get('DutyName'), PDO::PARAM_STR);
                $stmt->bindValue(':DutyColourID', $request->get('ahdutycolour'), PDO::PARAM_INT);
                $stmt->bindValue(':BreakTime', $strBreakTime, PDO::PARAM_INT);
                $stmt->bindValue(':Duration', $duration, PDO::PARAM_INT);
                $stmt->bindValue(':currentuserID', $sessUserId, PDO::PARAM_INT);
                $stmt->bindValue(':isNeedCovering', $isNeedCovering, PDO::PARAM_INT);
                $stmt->bindValue(':StartDate', $dutyDate->format('Y-m-d'), PDO::PARAM_STR);
                $stmt->bindValue(':EndDate', $dutyDate->format('Y-m-d'), PDO::PARAM_STR);
                $stmt->bindValue(':Saturday', isset($days['saturday']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':Sunday', isset($days['sunday']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':Monday', isset($days['monday']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':Tuesday', isset($days['tuesday']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':Wednesday', isset($days['wednesday']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':Thursday', isset($days['thursday']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':Friday', isset($days['friday']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':startWeek', $startDateWeek, PDO::PARAM_INT);
                $stmt->bindValue(':endWeek', $endDateWeek, PDO::PARAM_INT);
                $stmt->execute();

                $resultJson = 'success';
            }

            return $resultJson;

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
    }

    /*
    * Function used to create new adhoc duty records
    * @param array   $adHocData Adhoc duty data.
    * @param Request $request request data
    * @param Carbon  $startDate adhoc duty start date
    * @param Carbon  $endDate adhoc duty end date
    */
    private function createAdHoc(array $adHocData, Request $request, Carbon $startDate, Carbon $endDate) {
        try{
            $startDateWeek = GetAllocationWeekandDay($startDate->format('Y-m-d'))['ixYearWeek'];
	        $endDateWeek = GetAllocationWeekandDay($endDate->format('Y-m-d'))['ixYearWeek'];
            $period = new CarbonPeriod($startDate, $endDate);
            $days = [];
            foreach($period->toArray() as $date) {
                $days[mb_strtolower($date->format('l'))] = 1;
            }
            $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
            $this->pdo->beginTransaction();
            $sql = "INSERT INTO MasterDuties
                (
                ScheduledPersonID,
                StartDate,
                EndDate,
                StartTime,
                EndTime,
                DutyName,
                TeamID,
                DutyComment,
                DutyColourID,
                BreakTime,
                Duration,
                CreatedDate,
                CreatedBy,
                LastModBy,
                LastModDate,
                IsNeedCovering,
                DutyTypeID,
                StartWeek,
                EndWeek,
                Saturday,
                Sunday,
                Monday,
                Tuesday,
                Wednesday,
                Thursday,
                Friday,
                History
                )
                values ( :ScheduledPersonID, :StartDate , :EndDate , :StartTime , :EndTime , :Dutyname , :SchedulingTeamID , :Comment, :dutyColorId, :strBreakTime, :duration, GETUTCDATE(), :current_User, :LastModBy, GETUTCDATE(), :isNeedCovering, 6, :startWeek, :endWeek,
                :Saturday,
                :Sunday,
                :Monday,
                :Tuesday,
                :Wednesday,
                :Thursday,
                :Friday,
                :History
                )";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':ScheduledPersonID', $adHocData['ScheduledPersonID'], PDO::PARAM_INT);
            $stmt->bindValue(':StartDate', $startDate->format('Y-m-d'), PDO::PARAM_STR);
            $stmt->bindValue(':EndDate', $endDate->format('Y-m-d'), PDO::PARAM_STR);
            $stmt->bindValue(':StartTime', $adHocData['StartTime'], PDO::PARAM_STR);
            $stmt->bindValue(':EndTime', $adHocData['EndTime'], PDO::PARAM_STR);
            $stmt->bindValue(':Dutyname', $adHocData['DutyName'], PDO::PARAM_STR);
            $stmt->bindValue(':SchedulingTeamID', $adHocData['TeamID'], PDO::PARAM_INT);
            $stmt->bindValue(':dutyColorId', $adHocData['DutyColourID'], PDO::PARAM_INT);
            $stmt->bindValue(':strBreakTime', $adHocData['BreakTime'], PDO::PARAM_STR);
            $stmt->bindValue(':duration', $adHocData['Duration'], PDO::PARAM_STR);
            $stmt->bindValue(':Comment', $adHocData['DutyComment'], PDO::PARAM_STR);
            $stmt->bindValue(':current_User', $adHocData['CreatedBy'], PDO::PARAM_STR);
            $stmt->bindValue(':LastModBy', $adHocData['CreatedBy'], PDO::PARAM_STR);
            $stmt->bindValue(':isNeedCovering', $adHocData['IsNeedCovering'], PDO::PARAM_INT);
            $stmt->bindValue(':startWeek', $startDateWeek, PDO::PARAM_INT);
            $stmt->bindValue(':endWeek', $endDateWeek, PDO::PARAM_INT);
            $stmt->bindValue(':Saturday', isset($days['saturday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Sunday', isset($days['sunday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Monday', isset($days['monday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Tuesday', isset($days['tuesday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Wednesday', isset($days['wednesday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Thursday', isset($days['thursday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Friday', isset($days['friday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':History', $adHocData['History'], PDO::PARAM_STR);
            $stmt->execute();

            $lastAhdutyId = $this->pdo->lastInsertId();
            $request->request->set('attributeId', $lastAhdutyId);
            $request->request->set('historyType', 11);
            $request->request->set('userId', $sessUserId);
            $request->request->set('message', $adHocData['History']);
            $this->addAllocationHistory($request);

            $this->pdo->commit();
        }
        catch(PDOException $e){
            $this->pdo->rollBack();
            $e->getMessage();
        }
    }

    /**
     * Get Publish Master Rotas of team
     *
     * @param  Request $request
     * @return array
     */
    public function getPublishMasterRotasByTeam($schedulingTeamid)
    {
        try {
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_get_PublishMasterRotasDetails] ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $schedulingTeamid, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Update Adhoc Duty Comments by Id
     *
     * @param Request $request
     *
     * @return bool
     */
    public function updateAdhocDutyComments(Request $request)
    {
        try {
            $adhocID = $request->get('AdhocID');
            $Comment = ($request->get('Comment') != '') ? $request->get('Comment') : null;
            $DutyComment = ($request->get('DutyComment') != '') ? $request->get('DutyComment') : null;
			if((!empty($Comment) && !empty($DutyComment)) || (!empty($Comment)) || (!empty($DutyComment))){
                $finalComment = $Comment . '__COMMENT_SEPARETOR__' . $DutyComment;
            }else{
                $finalComment = '';
            }
            $adHocDutyDetails = $this->getAdhocDutyByID($adhocID);
            $startDate = Carbon::parse($adHocDutyDetails['StartDate']);
            $endDate = Carbon::parse($adHocDutyDetails['EndDate']);
            $dutyDate = Carbon::parse($request->get('dutyDate'));
            /*
            * If Adhoc master duty is between a date range create new records with corrent start and end date, Since we create a single record for a date range
            * Such that the already existing records acts as new updated record
            */
            if($adHocDutyDetails['StartDate'] != $adHocDutyDetails['EndDate'])
            {
                if($startDate->isBefore($dutyDate)) {
                    $this->createAdHoc($adHocDutyDetails, $request, $startDate, (clone $dutyDate)->subDays(1));
                }
                if($endDate->isAfter($dutyDate)) {
                    $this->createAdHoc($adHocDutyDetails, $request, (clone $dutyDate)->addDays(1), $endDate);
                }
            }
            $days = [mb_strtolower($dutyDate->format('l')) => 1];
            $startDateWeek = GetAllocationWeekandDay($dutyDate->format('Y-m-d'))['ixYearWeek'];
            $endDateWeek = GetAllocationWeekandDay($dutyDate->format('Y-m-d'))['ixYearWeek'];
            $query = "UPDATE  dbo.MasterDuties set
				DutyComment = :Comment,
                StartDate = :StartDate,
                EndDate = :EndDate,
                Saturday = :Saturday,
                Sunday = :Sunday,
                Monday = :Monday,
                Tuesday = :Tuesday,
                Wednesday = :Wednesday,
                Thursday = :Thursday,
                Friday = :Friday,
                StartWeek = :startWeek,
                EndWeek = :endWeek
				WHERE MasterDutyID = :ID";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':ID', $adhocID, PDO::PARAM_INT);
            $stmt->bindValue(':Comment', $finalComment, PDO::PARAM_STR);
            $stmt->bindValue(':StartDate', $dutyDate->format('Y-m-d'), PDO::PARAM_STR);
            $stmt->bindValue(':EndDate', $dutyDate->format('Y-m-d'), PDO::PARAM_STR);
            $stmt->bindValue(':Saturday', isset($days['saturday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Sunday', isset($days['sunday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Monday', isset($days['monday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Tuesday', isset($days['tuesday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Wednesday', isset($days['wednesday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Thursday', isset($days['thursday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':Friday', isset($days['friday']) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':startWeek', $startDateWeek, PDO::PARAM_INT);
            $stmt->bindValue(':endWeek', $endDateWeek, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * Update over twelve by Id
     *
     * @param Request $request
     *
     * @return string
     */
    public function updateOverTwelveAllocation(Request $request)
    {
        try {
			if(strpos($request->get('authorised12Hours'), '.', 0))
			{
				$authorised12Hours = $this->timetoseconds($request->get('authorised12Hours'), '.');
			}else
			{
				$authorised12Hours = ((int)$request->get('authorised12Hours') * 3600);
			}

            $isOverseasDeployment = $request->get('overseasDeployment') ?? 0;
            $markOverTwelve = $request->get('authoriseOver12');
            $plannedHours = $request->get('plannedHours');
            $allocationId = $request->get('allocationId');
            $allocationsSpId = $request->get('allocationsSpId');
            $netLoginId = $request->get('netLoginId');
            if ($markOverTwelve == 0) {
                $authorised12Hours = 0;
            }
            $query = "exec [dbo].[usp_UpdateOverTwelve] @AllocationsSPID = $allocationsSpId, @pNetLogin = '" . $netLoginId . "', @pMarkOverTwelve = $markOverTwelve, @pOverTwelveHrs = $authorised12Hours, @pIsOverseasOverTwelve = $isOverseasDeployment";
            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
    }

    /**
     * Over Under Eleven by Allocation Id
     *
     * @param Request $request
     *
     * @return string
     */
    public function updateUnderElevenAllocation(Request $request)
    {
        try {
			if(strpos($request->get('actualUnder11hrs'),'.') !== false)
			{
				$actualUnder11hrs = $this->timetoseconds($request->get('actualUnder11hrs'), '.');
			}else
			{
				$actualUnder11hrs = $request->get('actualUnder11hrs') * 3600;
			}
            $IsUnderElevenBreakOverride = $request->get('IsUnderElevenBreakOverride');
            $RemoveOverride = $request->get('RemoveOverride');
            $under11Comments = $request->get('under11Comments');
            $allocationId = $request->get('allocationId');
            $allocationsSpId = $request->get('allocationsSpId');
            $allocationsDutyId = $request->get('allocationsDutyId');
            $netLoginId = $request->get('netLoginId');

            $query = "exec [dbo].[usp_UpdateUnderEleven] @AllocationsSPID = $allocationsSpId, @pNetLogin = '" . $netLoginId . "', @pIsUnderElevenBreakOverride = $IsUnderElevenBreakOverride, @pOverrideUnderElevenHrs = $actualUnder11hrs, @pUnderElevenComment = '" . $under11Comments . "', @pRemoveOverride = $RemoveOverride";
            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
    }

    /**
     *  Remove created week of allocations
     *
     * @param $request
     * @return array
     */
    public function removeWeekAllocations(Request $request)
    {
        $query = "exec [dbo].[usp_del_RemoveAllocationWeek] ?,?,?";

        try {
            $stmt = $this->pdo->prepare($query);
            $netLoginId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $stmt->bindValue(1, $request->get('scheduledteamid'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('removeweeknumber'), PDO::PARAM_INT);
            $stmt->bindValue(3, $netLoginId, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to check user is system admin or not
     *
     * @param $userId This param contains the userID information
     * @return boolean
     */
    public function checkSystemAdmin($userId = 0)
    {
        try {
            $query = "SELECT RoleID FROM UserSystemRole_Link where UserID  = :userId";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($row)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    /**
     *  This function is used to get current set filter for the daily screen
     *
     * @param Request $request
     * @return Integer
     */
    public function getCurrentSetEditWeeklyFilter(Request $request)
    {
        try {
            $strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $teamID = $request->get('teamId');
            $query = "SELECT EditWeeklyFilter FROM User_Web_Config WHERE Login=:strLogin AND SchedulingTeamId=:teamID";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':teamID', $teamID, PDO::PARAM_INT);
            $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (isset($row['EditWeeklyFilter']) && ($row['EditWeeklyFilter'] != '' || $row['EditWeeklyFilter'] != 0)) {
                return $row['EditWeeklyFilter'];
            } else {
                return 0;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    /**
     *  This function is used to get last created week for the team
     *
     * @param $request
     * @return Integer
     */
    public function getLastCreatedWeek(Request $request)
    {
        try {
            $query = "exec ? = [dbo].[usp_get_LastCreatedWeekOfTeam] ?";
            $spresult = 0;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $spresult, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
            $stmt->bindValue(2, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return $spresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is check if duty is associated with charging or not
     *
     * @param $allocationid
     * @return Integer
     */

    public function getDutyCharging($allocationid)
    {
        try {

            $query = "SELECT 1 as 'chargingstatus' FROM ChargingDutyMapping_Link WHERE AllocationId=:allocationid";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':allocationid', $allocationid, PDO::PARAM_INT);

            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    /**
     *  This function is used to insert data in Allocations Edit Table
     *
     * @param Request $request
     * @param $data This param contains the data which was going to update
     *
     * @return boolean
     */
    public function insertAllocationsEdit(Request $request, $data)
    {
        try {
            $allocEditId = $this->copyAllocToAllocEdit($request->get('ID'));
            if ($allocEditId > 0) {
                $updateAllocationsEdit = $this->updateAllocationsEdit($request, $data, $request->get('ID'));
                if ($updateAllocationsEdit) {
                    $intIsEdited = 1;
                    return $this->setIsEdited($request->get('ID'), $intIsEdited);
                }
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to update data in Allocations Edit Table
     *
     * @param Request $request
     * @param $data This param contains the data which was going to update
     * @param $allocationId This param contains the allocation ID information
     *
     * @return boolean
     */
    public function updateAllocationsEdit(Request $request, $data, $allocationId)
    {
        try {
            $updateId = 0;
            if (!empty($data)) {
                if ($allocationId > 0) {
                    $rowData = $this->getLatestAllocationsEdit($allocationId);
                    if (!empty($rowData) && $rowData['ID'] > 0) {
                        $updateId = $rowData['ID'];
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
                $query = "";
                $query .= "UPDATE Allocations_edit SET ";
                foreach ($data as $fieldName => $fieldValue) {
                    $query .= $fieldName;
                    $query .= "=";
                    if ($fieldName == 'DutyName') {
                        $query .= "'";
                    }
                    $query .= $fieldValue;
                    if ($fieldName == 'DutyName') {
                        $query .= "'";
                    }
                    $query .= ",";
                }
                $query .= "DutyDate=CONVERT(DATETIME,'" . $request->get('DutyDate') . "',102),isPublished=0 WHERE ID = " . $updateId;
                $stmt = $this->pdo->prepare($query);
                if ($stmt->execute()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to set isEdited column value in Allocations table
     *
     * @param $allocationid This param contains the AllocationID column information
     * @param $isEditedVal This param contains the isEdited column value information
     * @param $dutyDate This param contains the Duty Date information
     * @param $schedulingPersonId This param contains the scheduling person ID information
     *
     * @return boolean
     */
    public function setIsEdited($allocationid = 0, $isEditedVal = 0, $dutyDate = '', $schedulingPersonId = 0, $schedulingTeamId = 0)
    {
        try {
            if (!empty($allocationid)) {
                $query = "UPDATE Allocations SET isEdited=:intIsIedited WHERE ID=:intAllocationId";
            } else {
                $query = "UPDATE Allocations SET isEdited=:intIsIedited,UnAllocated=0 WHERE DutyDate=CONVERT(DATETIME,:dutyDate,102) AND SchedulingPersonID=:SchedulingPersonID AND SchedulingTeamId=:SchedulingTeamId";
            }
            $stmt = $this->pdo->prepare($query);
            if (!empty($allocationid)) {
                $stmt->bindParam(':intAllocationId', $allocationid, PDO::PARAM_INT);
            } else {
                $stmt->bindParam(':dutyDate', $dutyDate, PDO::PARAM_STR);
                $stmt->bindParam(':SchedulingPersonID', $schedulingPersonId, PDO::PARAM_INT);
                $stmt->bindParam(':SchedulingTeamId', $schedulingTeamId, PDO::PARAM_INT);
            }
            $stmt->bindParam(':intIsIedited', $isEditedVal, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to get last insert id from Allocations edit table for the particular AllocationID
     *
     * @param $allocationId This param contains the AllocationID column information
     *
     * @return Integer
     */
    public function getLatestAllocationsEdit($allocationId)
    {
        try {
            $strQuery = "SELECT TOP 1 * FROM Allocations_edit WHERE AllocationID=:intAllocationId ORDER BY ID DESC";
            $stmtStrQuery = $this->pdo->prepare($strQuery);
            $stmtStrQuery->bindParam(':intAllocationId', $allocationId, PDO::PARAM_INT);
            $stmtStrQuery->execute();
            return $stmtStrQuery->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return 0;
    }

    /**
     *  This function is used to copy Allocations data to Allocations Edit
     *
     * @param $allocId This param contains the Allocation table ID Information
     *
     * @return Integer
     */
    public function copyAllocToAllocEdit($allocId)
    {
        try {
            $query = "exec ? = [dbo].[usp_copy_AllocationToAllocationEdit] ?";
            $spresult = 0;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $spresult, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
            $stmt->bindValue(2, $allocId, PDO::PARAM_INT);
            $stmt->execute();
            return $spresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return 0;
    }

    /**
     *  This function is used to store swap duty details in Allocations Edit
     *
     * @param $sourceDutyData This param contains the source duty data Information
     * @param $data This param contains the target duty data Information
     * @param $allocId This param contains the Allocations table ID Information
     *
     * @return boolean
     */
    public function swapDuty($data, $allocId)
    {
        try {
            $duration = (int) $data->Duration;
            $startTime = (int) $data->StartTime;
            if (empty($data->StartTime)) {
                $startTime = 0;
            }
            $endTime = (int) $data->EndTime;
            if (empty($data->EndTime)) {
                $endTime = 0;
            }
            $isRequest = (int) $data->isRequest;
            if (empty($data->isRequest)) {
                $isRequest = 0;
            }
            $schedulingPersonId = (int) $data->SchedulingPersonID;
            if (empty($data->SchedulingPersonID)) {
                $schedulingPersonId = 0;
            }

            $query = "exec ? = [dbo].[usp_mod_SwapDutyDataAllocationEdit] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
            $spresult = 0;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $spresult, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
            $stmt->bindValue(2, $allocId, PDO::PARAM_INT);
            $stmt->bindValue(3, $data->DutyName, PDO::PARAM_STR);
            $stmt->bindValue(4, $duration, PDO::PARAM_INT);
            $stmt->bindValue(5, $data->iDay, PDO::PARAM_INT);
            $stmt->bindValue(6, $startTime, PDO::PARAM_INT);
            $stmt->bindValue(7, $endTime, PDO::PARAM_INT);
            $stmt->bindValue(8, $data->DutyComments, PDO::PARAM_STR);
            $stmt->bindValue(9, $data->AdhocDuty, PDO::PARAM_INT);
            $stmt->bindValue(10, $data->MarkedOvertime, PDO::PARAM_INT);
            $stmt->bindValue(11, $data->MarkedSickness, PDO::PARAM_INT);
            $stmt->bindValue(12, $schedulingPersonId, PDO::PARAM_INT);
            $stmt->bindValue(13, $data->DutyDate, PDO::PARAM_STR);
            $stmt->bindValue(14, $data->StartDate, PDO::PARAM_STR);
            $stmt->bindValue(15, $data->EndDate, PDO::PARAM_STR);
            $stmt->bindValue(16, $data->MasterDutyId, PDO::PARAM_INT);
            $stmt->bindValue(17, $data->dutyColorId, PDO::PARAM_INT);
            $stmt->bindValue(18, $data->dutyProgramId, PDO::PARAM_INT);
            $stmt->bindValue(19, $data->dutyBreakTime, PDO::PARAM_INT);
            $stmt->bindValue(20, $data->isAttention, PDO::PARAM_INT);
            $stmt->bindValue(21, $isRequest, PDO::PARAM_INT);
            $stmt->execute();
            return $spresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return 0;
    }

    /**
     *  This function is used to store swap duty details in Allocations Edit
     *
     * @param Request $request
     *
     * @return string
     */
    public function checkWeekExists(Request $request)
    {
        try {
            $pWeekNumber = $request->get('checkweeknumber') ?? 0;
            $pTeamId = $request->get('schedulingTeamId') ?? 0;

            $query = "select AL_Status from Allocations where AL_WeekNumber = ? and AL_SchedulingTeamID = ? and AL_Status in(0, 1)";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $pWeekNumber, PDO::PARAM_INT);
            $stmt->bindValue(2, $pTeamId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($result) && isset($result['AL_Status'])) {
				return substr($pWeekNumber,4,2).'/'.substr($pWeekNumber,0,4);
            }
            return '';
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return 0;
    }

    /**
     *  This function is used to Drag Drop Unallocated duty to allocated
     *
     * @param $sourceId This param contains the source ID Information
     * @param $targetId This param contains the target ID Information
     *
     * @return array
     */
    public function unAllocatedToAllocated(Request $request)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$scheduledPersonId = $request->get('scheduledPersonId');
            $dataSourceId = $request->get('dataSourceId');
			if(strpos($dataSourceId, '_') > 0)
			{
				$dataSourceIdArr = explode('_', $dataSourceId);
				$dataSourceId = $dataSourceIdArr[0];
			}
            $dragAllocationsId = $request->get('dragAllocationsId');
            $dropAllocationsSpId = ($request->get('dropAllocationsSpId') != '') ? (int)$request->get('dropAllocationsSpId') : '';

            $query = "exec usp_Edit_Allocations @EditType ='ASSIGN', @pNetLogin = $sessUserNetId,@pAllocationsID = $dragAllocationsId, @Fromid = ".$dataSourceId.", @ToId = '".$dropAllocationsSpId."', @pSchedulingpersonid = $scheduledPersonId";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $returnData['SPError'] = $stmt->errorInfo();
            $respdata = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!isset($respdata[0]['spStatus']) && !empty($respdata)) {
                $returnData['spStatus'] = true;
                $returnData['spData'] = $respdata;
            }
            if (isset($respdata[0]['SPExecStatus']) && ($respdata[0]['SPExecStatus'] > 0)) {
                $returnData['spStatus'] = false;
                $returnData['errorMessage'] = $respdata[0]['SPMessage'];
            }else
			{
				$returnData['spStatus'] = true;
                $returnData['errorMessage'] = 'Success';
			}
            return json_encode($returnData, true);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to create week for the Allocations
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function createWeekAllocations(Request $request)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $query = "exec [dbo].[usp_Create_Allocations] ?,?,?";
            $stmt = $this->pdo->prepare($query);            
            $stmt->bindValue(1, $request->get('startWeek'), PDO::PARAM_INT);
            $stmt->bindValue(2, $request->get('searchTeamId'), PDO::PARAM_INT);
            $stmt->bindValue(3, $sessUserNetId, PDO::PARAM_STR);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to find previous created week from the passing allocation week
     *
     * @param Request $request
     *
     * @return Integer
     */
    public function findPrevCreatedWeek(Request $request)
    {
        try {
            if ($request->get('enteredWeekNum') != '') {
                $explodeWeekNumber = explode('/', $request->get('enteredWeekNum'));
                $passingWeekNumber = $explodeWeekNumber[1] . $explodeWeekNumber[0];
            } else {
                return 0;
            }
            $query = "SELECT TOP 1 WeekNumber FROM Allocations WHERE WeekNumber < :weekNumber AND SchedulingTeamId = :teamId ORDER BY ID DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':weekNumber', $passingWeekNumber, PDO::PARAM_INT);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to swap duty within allocation on edit weekly screen
     *
     * @param $sourceId This param contains the source ID Information
     * @param $targetId This param contains the target ID Information
     *
     * @return Array
     */
    public function swapAllocatedDuty(Request $request)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$scheduledPersonIdArr = explode(',', $request->get('scheduledPersonId'));
			$scheduledPersonId = isset($scheduledPersonIdArr[1]) ? $scheduledPersonIdArr[1] : $request->get('scheduledPersonId');
            $dataSourceId = $request->get('dataSourceId');
			$dragAllocationsId = $request->get('dragAllocationsId');
            $query = "exec usp_Edit_Allocations @EditType = 'SWAP',@pNetLogin = '$sessUserNetId',@pAllocationsID = $dragAllocationsId, @Fromid = ".$request->get('dragAllocationsSpId').", @ToId = ".$request->get('dropAllocationsSpId').",  @pSchedulingpersonid = $scheduledPersonId";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $respdata = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $returnData['SPError'] = $stmt->errorInfo();
            if (!isset($respdata[0]['spStatus']) && !empty($respdata)) {
                $returnData['spStatus'] = true;
                $returnData['spData'] = $respdata;
            }
            if (isset($respdata[0]['SPExecStatus']) && ($respdata[0]['SPExecStatus'] > 0)) {
                $returnData['spStatus'] = false;
                $returnData['errorMessage'] = $respdata[0]['SPMessage'];
            }else
			{
				$returnData['spStatus'] = true;
                $returnData['errorMessage'] = 'Success';
			}
            return json_encode($returnData, true);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to swap duty within allocation on edit weekly screen
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function createAllocationNewPerson(Request $request)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $query = "exec ? = [dbo].[usp_Create_Allocations_New_Person] ?,?,?,?";
            $spresult = 0;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $spresult, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
            $stmt->bindValue(2, $request->get('startDate'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('endDate'), PDO::PARAM_STR);
            $stmt->bindValue(4, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(5, $sessUserNetId, PDO::PARAM_STR);
            $stmt->execute();
            return $spresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to Drag Drop Allocated duty to Unallocated
     *
     * @param $sourceId This param contains the source ID Information
     *
     * @return array
     */
    public function allocatedToUnallocated(Request $request)
    {
        try {
            $aLtoULstartDate = $request->get('startDate');
            $aLtoULendDate = $request->get('endDate');
            $aLtoULteamId = $request->get('teamId');
            $aLtoULscheduledPersonId = $request->get('scheduledPersonId');
            $aLtoULSessionUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $aLtoULdataSourceId = $request->get('dataSourceId');
            $dragAllocationsId = $request->get('dragAllocationsId');

            $query = "exec usp_Edit_Allocations @EditType ='UNASSIGN', @pNetLogin = '".$aLtoULSessionUser."', @pAllocationsID=".$dragAllocationsId.", @Fromid = ".$request->get('dragAllocationsSpId');
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $aLtoULSessionUser, PDO::PARAM_STR);
            $stmt->bindValue(2, $aLtoULdataSourceId, PDO::PARAM_INT);
            $stmt->bindValue(3, $request->get('dragAllocationsSpId'), PDO::PARAM_INT);
            $stmt->execute();
            $respdata = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $returnData['SPError'] = $stmt->errorInfo();
            if (!isset($respdata[0]['spStatus']) && !empty($respdata)) {
                $returnData['spStatus'] = true;
                $returnData['spData'] = $respdata;
            }
            if (isset($respdata[0]['SPExecStatus']) && ($respdata[0]['SPExecStatus'] > 0)) {
                $returnData['spStatus'] = false;
                $returnData['errorMessage'] = $respdata[0]['SPMessage'];
            }else
			{
				$returnData['spStatus'] = true;
                $returnData['errorMessage'] = 'Success';
			}
            return json_encode($returnData, true);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to Mark WIAD and Unmark WIAD for the Allocation
     *
     * @param $paramData This param contains the params data Information
     *
     * @return array
     */
    public function markWiadAllocation($paramData)
    {
        try {
            $mWiadSessUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$mActualSessUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$spActionParam = (int)$paramData['spActionParam'];
			$allocationsSpId = (int)$paramData['allocationsSpId'];
			$allocationsId = (int)$paramData['ID'];
			$schedulingPersonId = (int)$paramData['schedulingPersonId'];
			$dutyDate = $paramData['dutyDate'];
            $query = "exec [dbo].[usp_Edit_Allocations] @EditType = 'MARKWIAD', @pNetLogin = ?, @pMarkWIAD = ?, @FromID = ?, @pAllocationsID = ?, @pSchedulingPersonID = ?, @pDutyDate = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $mActualSessUser, PDO::PARAM_STR);
            $stmt->bindValue(2, $spActionParam, PDO::PARAM_INT);
			if(($allocationsSpId == 'null') || empty($allocationsSpId))
			{
				$stmt->bindValue(3, $allocationsSpId, PDO::NULL_EMPTY_STRING);
			}else
			{
				$stmt->bindValue(3, $allocationsSpId, PDO::PARAM_INT);
			}
            $stmt->bindValue(4, $allocationsId, PDO::PARAM_INT);
            $stmt->bindValue(5, $schedulingPersonId, PDO::PARAM_INT);
            $stmt->bindValue(6, $dutyDate, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to Mark Actual and Unmark Actual for the Allocation
     *
     * @param $paramData This param contains the params data Information
     *
     * @return array
     */
    public function markActualAllocation($paramData)
    {
        try {
            $mActualSessUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$spActionParam = (int)$paramData['spActionParam'];
			$allocationsSpId = (int)$paramData['allocationsSpId'];
			$allocationsId = (int)$paramData['ID'];
			$schedulingPersonId = (int)$paramData['schedulingPersonId'];
			$dutyDate = $paramData['dutyDate'];
            $query = "exec [dbo].[usp_Edit_Allocations] @EditType = 'MARKACTUAL', @pNetLogin = ?, @pMarkActual = ?, @FromID = ?, @pAllocationsID = ?, @pSchedulingPersonID = ?, @pDutyDate = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $mActualSessUser, PDO::PARAM_STR);
            $stmt->bindValue(2, $spActionParam, PDO::PARAM_INT);
			if(($allocationsSpId == 'null') || empty($allocationsSpId))
			{
				$stmt->bindValue(3, $allocationsSpId, PDO::NULL_EMPTY_STRING);
			}else
			{
				$stmt->bindValue(3, $allocationsSpId, PDO::PARAM_INT);
			}
            $stmt->bindValue(4, $allocationsId, PDO::PARAM_INT);
            $stmt->bindValue(5, $schedulingPersonId, PDO::PARAM_INT);
            $stmt->bindValue(6, $dutyDate, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to Remove Additional Person from the week
     *
     * @param Request $request
     *
     * @return array
     */
    public function removeFromWeek(Request $request)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$cellId = (int)$request->get('cellId');
			$schedulingPersonId = (int)$request->get('schedulingPersonId');
            $query = "exec usp_Edit_Allocations @EditType = 'REMOVEFROMWEEK', @pNetLogin = ?,@pAllocationsID = ?, @pSchedulingPersonID = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $sessUserNetId, PDO::PARAM_STR);
            $stmt->bindValue(2, $cellId, PDO::PARAM_INT);
            $stmt->bindValue(3, $schedulingPersonId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to add additional person in the allocations week
     *
     * @param Request $request
     *
     * @return array
     */
    public function addPersonToAllocations(Request $request)
    {
        try {
            $explodeWeekNumber = explode('/', $request->get('weekNumber'));
            $weekNumber = $explodeWeekNumber[1] . $explodeWeekNumber[0];
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

            $query = "exec [dbo].[usp_Edit_Allocations] @EditType = ?, @pNetLogin = ?, @pWeekNumber = ?, @pTeamID = ?, @pSchedulingPersonID = ?, @pAllocationsID = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, 'ADDPERSON', PDO::PARAM_STR);
            $stmt->bindValue(2, $sessUserNetId, PDO::PARAM_STR);
            $stmt->bindValue(3, $weekNumber, PDO::PARAM_INT);
            $stmt->bindValue(4, $request->get('teamId'), PDO::PARAM_INT);
            $stmt->bindValue(5, $request->get('schedulingPersonId'), PDO::PARAM_INT);
            $stmt->bindValue(6, $request->get('dataId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to get breaches list for the scheduled person based on duty date
     *
     * @param Request $request
     *
     * @return array
     */
    public function getBreachesList(Request $request)
    {
        try {
            $scheduledPersonId = $request->get('scheduledPersonId');
            $dutyDate = $request->get('dutyDate');
            $teamId = $request->get('teamId');

            $query = "exec [dbo].[usp_WTDBreach] @pSchedulingPersonID=?,@pDutyDate=?,@pTeamID=?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $scheduledPersonId, PDO::PARAM_INT);
            $stmt->bindParam(2, $dutyDate, PDO::PARAM_STR);
            $stmt->bindParam(3, $teamId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to get breach details by ID
     *
     * @param Request $request
     *
     * @return array
     */
    public function getBreachDetailById(Request $request)
    {
        try {
            $breachId = $request->get('breachId');

            $query = "exec [dbo].[usp_WTDBreach] @pBreakId=?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $breachId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to get Scheduling Person Name
     *
     * @param Request $request
     *
     * @return array
     */
    public function getSchedulingPersonName(Request $request)
    {
        try {
            $query = "SELECT UD_DisplayName AS DisplayName FROM UserDetails WHERE UD_UserID =" . $request->get('scheduledPersonId');
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to update breach details
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function updateBreachDetails(Request $request)
    {
        try {
            $editedId = $request->get('editedId');
            $historyLog = $request->get('historyLog');
            $breachComments = $request->get('breachComments');
            $ApprovedBy = $request->get('ApprovedBy');
            $approveDate = $request->get('approveDate');

            $query = "exec [dbo].[usp_Set_UpdateBreachDetails] @pBreachId=?,@pHistoryLog=?,@pBreachComments=?,@pApprovedBy=?,@pApproveDate=?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $editedId, PDO::PARAM_INT);
            $stmt->bindParam(2, $historyLog, PDO::PARAM_STR);
            $stmt->bindParam(3, $breachComments, PDO::PARAM_STR);
            $stmt->bindParam(4, $ApprovedBy, PDO::PARAM_STR);
            $stmt->bindParam(5, $approveDate, PDO::PARAM_STR);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    public function verifyWTDBreachDetails(Request $request)
    {
        try {
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');
            $teamId = $request->get('teamId');
            $NetLogin = $request->get('NetLogin');

            $query = "declare @retrun_value int;
						exec @retrun_value = usp_CreateWTDBreach ?, ?, ?, ?
						select @retrun_value SPExecStatus, '' SPMessage";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $startDate, PDO::PARAM_STR);
            $stmt->bindParam(2, $endDate, PDO::PARAM_STR);
            $stmt->bindParam(3, $teamId, PDO::PARAM_INT);
            $stmt->bindParam(4, $NetLogin, PDO::PARAM_STR);
            $stmt->execute();
            $respdata = $stmt->fetch(PDO::FETCH_ASSOC);
            return $respdata;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    public function deleteWTDBreachDetails(Request $request)
    {
        try {
			$teamId = $request->get('teamId') ? $request->get('teamId') : 0;
			$schedulingPersonId = $request->get('schedulingPersonId') ? $request->get('schedulingPersonId') : 0;
			$dutyDate = $request->get('dutyDate') ? $request->get('dutyDate') : '';
            $query = "exec [dbo].[usp_updateWorktimeDirective] ?,?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
            $stmt->bindParam(2, $schedulingPersonId, PDO::PARAM_INT);
            $stmt->bindParam(3, $dutyDate, PDO::PARAM_STR);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }
    /**
     *  This function is used to used to check the WTD Breach Rules
     *
     * @param $targetCellId
     *
     * @return boolean
     */
    public function checkWTDBreach($targetCellId)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $query = "exec ? = [dbo].[usp_UpdateWTD] ?,?";
            $spresult = 0;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $spresult, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
            $stmt->bindValue(2, $targetCellId, PDO::PARAM_INT);
            $stmt->bindValue(3, $sessUserNetId, PDO::PARAM_STR);
            $stmt->execute();
            return $spresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     *  This function is used to add unallocated duties from master duties
     *
     * @param Request $request
     *
     * @return array
     */
    public function addUnallocteDutiesFromMasterDuties(Request $request)
    {
        try {

            $weekNumber = $request->get('weeknumber');
            $dutyID = $request->get('dutyID');
            $teamId = $request->get('teamId');
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

            $query = "exec [dbo].[usp_AssignToUnallocateFromMasterDuty] ?,?,?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $weekNumber, PDO::PARAM_INT);
            $stmt->bindParam(2, $dutyID, PDO::PARAM_INT);
            $stmt->bindParam(3, $teamId, PDO::PARAM_INT);
            $stmt->bindParam(4, $sessUserNetId, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to add unallocated duties from master duties
     *
     * @param Request $request
     *
     * @return array
     */
    public function copyAllDutiesToAllocation(Request $request)
    {
        try {

            $weekNumber = $request->get('copytoaldutiesweeknumber');
            $dutyID = $request->get('dutyID');
            $teamId = $request->get('teamId');
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $query = "exec [dbo].[usp_CopyToALLDuties] $weekNumber,$dutyID,$teamId,$sessUserNetId";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $weekNumber, PDO::PARAM_INT);
            $stmt->bindParam(2, $dutyID, PDO::PARAM_INT);
            $stmt->bindParam(3, $teamId, PDO::PARAM_INT);
            $stmt->bindParam(4, $sessUserNetId, PDO::PARAM_STR);
            $stmt->execute();
            $queryData = [];
            do {
                $resultData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(count($resultData) > 0 ) {
                    $queryData[] = $resultData;
                }
            } while ($stmt->nextRowset());
            return $queryData;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to used to edit duty in allocations table
     *
     * @param $paramData This param contains the params data Information
     *
     * @return boolean
     */
    public function editDutyAllocation($paramData)
    {
        try {
            $colorId = $paramData['DutyColorId'] ?: 0;
            $labelId1 = $paramData['DutyLabelId'] ?: 0;
            $labelId2 = $paramData['DutyLabelId2'] ?: 0;
            $labelId3 = $paramData['DutyLabelId3'] ?: 0;
            $labelId4 = $paramData['DutyLabelId4'] ?: 0;
            $labelId5 = $paramData['DutyLabelId5'] ?: 0;
            $labelId6 = $paramData['DutyLabelId6'] ?: 0;
            $isNeedCovering = $paramData['isNeedCovering'] == 0 ? 0 : 1;
            $isOverrideOver12 = $paramData['isOverrideOver12'] == 0 ? 0 : 1;
            $allocationsDutyId = $paramData['allocationsDutyId'];
            $allocationsSpId = $paramData['allocationsSpId'];
            $allocationsDate = $paramData['allocationsDate'];
            $allocationsSchPer = $paramData['allocationsSchPer'];
            $allocationsDutyComments = trim($paramData['DutyComments']);
            $allocationsPersonComments = trim($paramData['PersonComments']);
			$allocationsPersonComments = empty($allocationsPersonComments) ? 'null' : "'".$allocationsPersonComments."'";
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$allocationsDutyId = ($allocationsDutyId == 'null') ? 0 : $allocationsDutyId;
			$allocationsSpId = ($allocationsSpId == 'null') ? 0 : $allocationsSpId;
            $query = "exec [dbo].[usp_EditDuty]
							@AllocationsID = ".$paramData['ID'].",
							@DutyName = '".$paramData['DutyName']."',
							@StartTime	= ".$paramData['StartTime'].",
							@EndTime = ".$paramData['EndTime'].",
							@BreakTime = ".$paramData['BreakTime'].",
							@Duration	=	".$paramData['Duration'].",
							@DutyColour	= $colorId,
							@DutyLable = $labelId1,
							@DutyLable2	= $labelId2,
							@DutyLable3 = $labelId3,
							@DutyLable4	= $labelId4,
							@DutyLable5	= $labelId5,
							@DutyLable6	= $labelId6,
							@pNetLogin	= '".$sessUserNetId."',
							@IsShiftleader = null,
							@IsNeedCovering	= $isNeedCovering,
							@IsOverrideOver12	= $isOverrideOver12,
							@AllocationsDutyID	=	$allocationsDutyId,
							@AllocationsSPID	= $allocationsSpId,
							@DutyDate		=		'".$allocationsDate."',
							@pSchedulingPersonID	=	$allocationsSchPer,
							@pDutyComments = ?";
            $stmt = $this->pdo->prepare($query);
			if(empty($allocationsDutyComments))
			{
				$stmt->bindValue(1, null, PDO::PARAM_NULL);
			}else{
				$stmt->bindParam(1, $allocationsDutyComments, PDO::PARAM_STR);
			}
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get allocated and unallocated duties from db
     *
     * @param Request $request
     * @return string
     */
    public function getEditWeeklyData(Request $request)
    {
        try {
            $gEditWeeklyDataSessUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $returnData = [];
			$endDate 	=	$request->get('endDate');
			$sDate 		=	new DateTime($request->get('startDate'));
			$eDater 	=	new DateTime($endDate);
			$dateDiff 	=	$eDater->diff($sDate)->format("%a");
			if($dateDiff % 7 == 0)
			{
				$endDate = date('Y-m-d', strtotime($request->get('endDate') . ' - 1 days'));
			}
            switch ($request->get('showDataType')) {
                case 'UNALLOC':
                    $query = "exec [dbo].[usp_get_EditWeekly] '" . $request->get('startDate') . "','" . $endDate . "'," . $request->get('teamId') . ",'" . $gEditWeeklyDataSessUser . "'";//, @pShowOnlyUnAllocatedDuty = 1
                    break;
                case 'ALLOC':
                    $query = "exec [dbo].[usp_get_EditWeekly] '" . $request->get('startDate') . "','" . $endDate . "'," . $request->get('teamId') . ",'" . $gEditWeeklyDataSessUser . "', @pShowOnlyUnAllocatedDuty = 0";
                    break;
                case 'ALL':
                    $query = "exec [dbo].[usp_get_EditWeekly] '" . $request->get('startDate') . "','" . $endDate . "'," . $request->get('teamId') . ",'" . $gEditWeeklyDataSessUser . "'";
                    break;
            }
			$stmt = $this->pdo->prepare($query);
			$stmt->execute();
			$stmt->nextRowset();
			$returnDataArr = $stmt->fetchAll(PDO::FETCH_NUM);
			foreach($returnDataArr as $returnDataArrVal)
			{
				if($returnDataArrVal[68] == 'AL')
				{
					$returnData['alloc'][] = $returnDataArrVal;
				}else
				{
					$returnData['unalloc'][] = [$returnDataArrVal[1], $returnDataArrVal[2], $returnDataArrVal[3], $returnDataArrVal[4], $returnDataArrVal[5], $returnDataArrVal[6], $returnDataArrVal[13], $returnDataArrVal[15], 0, $returnDataArrVal[16], $returnDataArrVal[25], $returnDataArrVal[26], $returnDataArrVal[72], $returnDataArrVal[68], $returnDataArrVal[75], $returnDataArrVal[92], $returnDataArrVal[93], 0, "InstanceIds"=>$returnDataArrVal[75]];
				}
			}
            if($request->get('mastMiscFilterId') != ''){
                $filterId = $request->get('mastMiscFilterId') ?? 0;
                $returnData['unalloc'] = $this->getFilteredUnallocatedDuties($filterId, $returnData['unalloc']);
            }
            return json_encode($returnData, true);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get allocated cell duties from db
     *
     * @param Request $request
     * @return string
     */
    public function getEditWeeklyDataCell(Request $request)
    {
		$scheduledPersonId = $request->get('scheduledPersonId') ? $request->get('scheduledPersonId') : 'null';
		if(strpos($scheduledPersonId, ","))
		{
			$scheduledPersonId = "'" . $scheduledPersonId . "'";
		}
		$endDate = date('Y-m-d', strtotime('-1 day', strtotime($request->get('endDate'))));
		$gEditWeeklyDataSessUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

        $query = "exec usp_get_EditWeekly '" . $request->get('startDate') . "','" . $endDate . "'," . $request->get('teamId') . ",'" . $gEditWeeklyDataSessUser . "', " . $scheduledPersonId . ", @pShowOnlyUnAllocatedDuty = 0";

        $returnData = [];
        try {
            $stmt = $this->pdo->prepare($query);
			$stmt->execute();
			$returnDataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if(isset($returnDataArr[0]['IsShowEditWeekly']))
			{
				$stmt->nextRowset();
				$returnDataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			$uId = 1000001;
			foreach($returnDataArr as $returnDataArrVal)
			{
				if($returnDataArrVal['DisplayGrid'] == 'AL')
				{
					//$returnDataArrVal[] = $uId;
                    $returnDataArrVal['uId'] = $uId;
					$returnData['alloc'][] = $returnDataArrVal;
					$uId++;
				}
			}
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return $returnData;
    }
    /**
     *  This function is used to update scheduled person sort code by weeknumber and team
     *  on allocation table duties from master duties
     *
     * @param Request $request
     *
     * @return array
     */
    public function modAllocationScheduledPersonSortCode(Request $request)
    {
        try {
            $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
            $psortcode = ($request->get('sortcode') != '') ? trim($request->get('sortcode')) : '';
            $pschedulingPersonId = ($request->get('schedulingPersonId') != '') ? $request->get('schedulingPersonId') : '';
            $allocateId = ($request->get('allocateId') != '') ? trim($request->get('allocateId')) : '';
            $pschedulingteamId = ($request->get('schedulingteamId') != '') ? trim($request->get('schedulingteamId')) : '';

            $query = "exec [dbo].[usp_mod_ScheduledPeopleSortCodeByWeekNumberTeam] @sortcode = ?, @scheduledpersonid = ?, @allocateId = ?, @teamid = ?, @current_User_Id = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $psortcode, PDO::PARAM_STR);
            $stmt->bindParam(2, $pschedulingPersonId, PDO::PARAM_INT);
            $stmt->bindParam(3, $allocateId, PDO::PARAM_INT);
            $stmt->bindParam(4, $pschedulingteamId, PDO::PARAM_INT);
            $stmt->bindParam(5, $sessUserId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     *  This function is used to delete Unassigned Allocations Jobs for the day
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function deleteAllocationJobs(Request $request): bool
    {
        try {
            $pdo = OpenDBLinkA7();
			$current_User = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $allocationsDutyId = $request->get('allocationsDutyId');
            $sql = "exec usp_Edit_Allocations @EditType = 'DELETEDUTY', @pNetLogin = ?, @Fromid = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $current_User, PDO::PARAM_STR);
            $stmt->bindParam(2, $allocationsDutyId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    public function getWTDTypes(Request $request)
    {
        try {
            $id = $request->get('id');
            $wiadOption = ($request->get('wiadOption') == true) ? 1 : 0;
            $query = "exec [dbo].[usp_get_WTDBreachTypes] ?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->bindParam(2, $wiadOption, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * Get Unique Scheduled Persons List for the Multiweek
     *
     * @param Request $request
     * @return array
     */
    public function getMultiweekSPList(Request $request): array
    {
        try {
            $queryCond = "";
            if ($request->get('queryString') != '') {
                $queryCond .= " AND (" . str_replace("_REPSQLCOND_", "''", $request->get('queryString')) . ")";
            }

            $orderCond = " ORDER BY DisplayLastName";
            if ($request->get('queryOrder') != '') {
                $orderCond = str_replace(", DutyName", "", " ORDER BY " . $request->get('queryOrder'));
            }

            $query = "exec [dbo].[usp_get_ReadAllocationsEditWeekly_SPList] @startDate='" . $request->get('reqStartDate') . "', @EndDate='" . $request->get('reqEndDate') . "', @pteamId=" . $request->get('teamId') . ",@filterCond = '" . str_replace("'", "''", $queryCond) . "',@filterOrderCond ='" . $orderCond . "'";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to get Duties for Shift Counting Filters
     *
     * @param Request $request
     * @return array
     */
    public function getShiftCountingFilterData(Request $request): array
    {
        try {
            $queryCond = "";
            if ($request->get('queryString') != '') {
                $queryCond .= " AND (" . str_replace("_REPSQLCOND_", "''", $request->get('queryString')) . ")";
            }

            $mastMiscId = 0;
            if ($request->get('mastMiscFilterId') != '') {
                $mastMiscId = $request->get('mastMiscFilterId');
            }
            $query = "exec [dbo].[usp_get_ReadAllocationsEditWeekly_DutyList] @startDate='" . $request->get('startDate') . "', @EndDate='" . $request->get('endDate') . "', @pteamId=" . $request->get('teamId') . ",@filterCond = '" . str_replace("'", "''", $queryCond) . "',@filterOrderCond ='',@DutyFilterID=" . $mastMiscId;
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to update mark overtime to other teams for the scheduled person added as additional person
     *
     * @param $allocId This param contains the allocation id information
     * @return boolean
     */
    public function updateMarkOvertime($argsArr)
    {
        try {
            $sessUserId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			$MarkedOvertime = $argsArr['MarkedOvertime'];
			$MannualOThours = $argsArr['MannualOThours'];
			$allocationsSpId = $argsArr['allocationsSpId'];
            $query = "exec usp_Edit_Allocations @EditType = 'MARKOVERTIME',@pnetlogin = ?, @pMarkOverTime = ?,  @pOverTimeHrs = ?,  @pAllocationsSPID = ?" ;
            $stmt = $this->pdo->prepare($query);
			$stmt->bindParam(1, $sessUserId, PDO::PARAM_STR);
			$stmt->bindParam(2, $MarkedOvertime, PDO::PARAM_INT);
			$stmt->bindParam(3, $MannualOThours, PDO::PARAM_INT);
			$stmt->bindParam(4, $allocationsSpId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to save filter ID for remembering for team
     *
     * @param $teamId This param contains the team id information
     * @param $filterId This param contains the filter id information
     *
     * @return boolean
     */
    public function saveShiftCountingFilter($teamId, $filterId)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
            $query1 = "SELECT ID,ShiftCountingFilter FROM User_Web_Config WHERE Login = :strLogin AND SchedulingTeamId = :teamId";
            $stmt1 = $this->pdo->prepare($query1);
            $stmt1->bindValue(':strLogin', $sessUserNetId, PDO::PARAM_STR);
            $stmt1->bindValue(':teamId', $teamId, PDO::PARAM_INT);
            $stmt1->execute();
            $row = $stmt1->fetch(PDO::FETCH_ASSOC);
            if (isset($row['ID']) && !empty($row)) {
                $query2 = "UPDATE User_Web_Config SET ShiftCountingFilter = :filterId WHERE ID = :rowId";
                $stmt2 = $this->pdo->prepare($query2);
                $stmt2->bindValue(':filterId', $filterId, PDO::PARAM_INT);
                $stmt2->bindValue(':rowId', $row['ID'], PDO::PARAM_INT);
                if ($stmt2->execute()) {
                    return true;
                }
            } else {
                $query = "INSERT INTO User_Web_Config(Login, SchedulingTeamId, ShiftCountingFilter, CreatedBy, CreatedDate) VALUES (:strLogin, :teamId, :filterId, :userId, CONVERT(DATETIME, GETDATE(), 101))";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindParam(':strLogin', $sessUserNetId, PDO::PARAM_STR);
                $stmt->bindValue(':teamId', $teamId, PDO::PARAM_INT);
                $stmt->bindValue(':filterId', $filterId, PDO::PARAM_INT);
                $stmt->bindParam(':userId', $sessUserId, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    return true;
                }
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * This function is used to get current set shift counting filter ID for team
     *
     * @param Request $request
     *
     * @return array
     */
    public function getCurrentShiftCountingFilter(Request $request)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $query = "SELECT ID,ShiftCountingFilter FROM User_Web_Config WHERE Login = :strLogin AND SchedulingTeamId = :teamId";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':strLogin', $sessUserNetId, PDO::PARAM_STR);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * This function is used to clear current set shift counting filter ID for team
     *
     * @param Request $request
     *
     * @return array
     */
    public function clearDutyShiftCountingFilter(Request $request)
    {
        try {
            $sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $query = "UPDATE User_Web_Config SET ShiftCountingFilter = NULL WHERE Login = :strLogin AND SchedulingTeamId = :teamId";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':strLogin', $sessUserNetId, PDO::PARAM_STR);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            if ($stmt->execute()) {
                return true;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * This function is used to check week is published or not
     *
     * @param Request $request
     *
     * @return array
     */
    public function checkWeekPublish(Request $request)
    {
        try {
            $explodeWeekNumber = explode('/', $request->get('weekNumber'));
            $qWeekNum = $explodeWeekNumber[1] . $explodeWeekNumber[0];

            $query = "SELECT AL_Status IsPublished FROM Allocations WHERE AL_SchedulingTeamID = :teamId AND AL_WeekNumber = :weeknumber AND AL_Status = 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':weeknumber', $qWeekNum, PDO::PARAM_INT);
            $stmt->bindValue(':teamId', $request->get('teamId'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * Gets the role of user based on teamID
     *
     * @param $teamId This param contains Team ID information
     *
     * @return array
     */
    public function getRolePermissionEditWeekly($teamId = 0)
    {
        try {
            $userRoleTrait = new class {
                use UserRoleTrait;
            };
            $returnData['isTeamAdmin'] = $teamId != 0 && $userRoleTrait->checkEditWeeeklyAdminRole($teamId) == 1 ? 1 : 0;
            return $returnData;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Gets the holiday list based on start and end date
     *
     * @param Request $request
     *
     * @return array
     */
    public function getHolidayList(Request $request)
    {
        try {

            $returnData = [];
            $query = "exec [dbo].[usp_get_BankHolidaysList] '" . $request->get('startDate') . "','" . $request->get('endDate') . "'";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $returnData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $returnData;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get Allocation Details For Leave
     *
     * @param Request $request
     *
     * @return array
     */
    public function getAllocationDetailsForLeave(Request $request)
    {
        try {
            $query = "exec [dbo].[usp_get_AllocationsDetailsForLeave] @netlogin=?,@FromtDate=?,@ToDate=?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $request->get('userNetLogin'), PDO::PARAM_STR);
            $stmt->bindValue(2, $request->get('pdlStartDate'), PDO::PARAM_STR);
            $stmt->bindValue(3, $request->get('pdlEndDate'), PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($data)) {
                if (($data[0]['IsLeaveApproved'] == 1) && ((($data[0]['leaveStartTime'] != '') && ($data[0]['leaveStartTime'] != 0)) || (($data[0]['leaveEndTime'] != '') && ($data[0]['leaveEndTime'] != 0)))) {
                    $data[0]['DutyType'] = 1;
                } else if ((strtoupper((string) $data[0]['DutyName']) == 'U-SICK') || (strtoupper((string) $data[0]['DutyName']) == 'SICK') || (strtoupper((string) $data[0]['DutyName']) == '-SICK')) {
                    $data[0]['DutyType'] = 2;
                } else if ((strtoupper((string) $data[0]['DutyName']) == 'LEAVE') || (strtoupper((string) $data[0]['DutyName']) == 'OFF LEAVE')) {
                    $data[0]['DutyType'] = 3;
                } else if (strtoupper((string) $data[0]['DutyName']) == 'ABSENT') {
                    $data[0]['DutyType'] = 4;
                } else if ((strtoupper((string) $data[0]['DutyName']) != 'U') && ((($data[0]['StartTime'] == '') || ($data[0]['StartTime'] == 0)) && (($data[0]['EndTime'] == '') || ($data[0]['EndTime'] == 0)))) {
                    $data[0]['DutyType'] = 5;
                } else if (strtoupper((string) $data[0]['DutyName']) == 'U') {
                    $data[0]['DutyType'] = 6;
                } else if (($data[0]['IsLeaveApproved'] == 0) && ((($data[0]['leaveStartTime'] != '') && ($data[0]['leaveStartTime'] != 0)) || (($data[0]['leaveEndTime'] != '') && ($data[0]['leaveEndTime'] != 0)))) {
                    $data[0]['DutyType'] = 7;
                } else {
                    $data[0]['DutyType'] = 0;
                }
                return $data;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get FWA/Scheduling Notes
     *
     * @param Request $request
     * @param integer $schPersonId
     * @return array
     */
    public function getSchedulingNotes(Request $request)
    {
        try {
            $query = "select UD_FWANotes FWANotes from UserDetails join ScheduledPersonTeam_LINK sptl on sptl.ScheduledPersonID = UD_UserID where UD_UserID = :schPersonId and sptl.IsHomeTeam = 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':schPersonId', $request->get('schPersonId'), PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Verify Part Day Leave
     *
     * @param Request $request
     *
     * @return array
     */
    public function verifyPartDayLeave(Request $request)
    {
        $query = "UPDATE LeaveApplications SET LeaveStartTime = :startTime, LeaveEndTime = :endTime, LastModDate=getutcdate(), LeaveStartDateTime= :LeaveStartDateTime, LeaveEndDateTime= :LeaveEndDateTime  WHERE ID = :leaveId";
        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':startTime', $request->get('pdlStartTime'), PDO::PARAM_STR);
            $stmt->bindValue(':endTime', $request->get('pdlEndTime'), PDO::PARAM_STR);
            $stmt->bindValue(':leaveId', $request->get('leaveId'), PDO::PARAM_BOOL);
            $stmt->bindValue(':LeaveStartDateTime', $request->get('LeaveStartDateTime'), PDO::PARAM_STR);
            $stmt->bindValue(':LeaveEndDateTime', $request->get('LeaveEndDateTime'), PDO::PARAM_STR);
            if ($stmt->execute()) {
                return true;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return false;
    }

    /**
     * Auto Apply and Approve PDL
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function autoApplyApprovePDL(Request $request)
    {
        try {
            $currentUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
            $NetLoginId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $UserFullName  = isset($_SESSION['user']['FullName']) && !empty($_SESSION['user']['FullName']) ?  $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
            $leaveID = $request->get('leaveId');
            $jsonAmounts = $request->get('jsonAmounts');
            $jsonPDLLeaves = $request->get('jsonPDLLeaves');

            $query = "exec [dbo].[usp_CreateUpdate_LeaveManagePopup] ?,?,?,?,?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $leaveID, PDO::PARAM_STR);
            $stmt->bindParam(2, $jsonAmounts, PDO::PARAM_STR);
            $stmt->bindParam(3, $currentUserID, PDO::PARAM_INT);
            $stmt->bindParam(4, $NetLoginId, PDO::PARAM_STR);
            $stmt->bindParam(5, $UserFullName, PDO::PARAM_STR);
            $stmt->bindParam(6, $jsonPDLLeaves, PDO::PARAM_STR);
            if($stmt->execute()){
                return true;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * Get scheduled persons list including allocated additional persons
     *
     * @param Request $request
     * @return array
     */
    public function getTeamScheduledPersons(Request $request)
    {

        $query = "SELECT SchedulingPersonID,
                    FullName
                FROM (
                SELECT ud.UD_UserID AS SchedulingPersonID, ud.UD_DisplayName AS FullName,
                        CASE WHEN spl.IsHomeTeam = 0 AND spl.IsAvailable = 0 AND AAP.AAP_AllocationsAPID IS NULL
                        THEN 0 ELSE 1 END AS AddSPExclFilter
                FROM Allocations AS AL (nolock)
                INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
                INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL_WeekNumber
                INNER JOIN UserDetails AS UD (nolock) ON UD_UserID = spl.ScheduledPersonID
                LEFT JOIN AllocationsAddPersons AAP on AL.AL_AllocationsID = AAP.AAP_AllocationsID
                                                AND spl.ScheduledPersonID = AAP.AAP_SchedulingPersonID
                                                AND td.ixDayInWeek = AAP.AAP_iDay
                WHERE AL.AL_SchedulingTeamID =  :teamID
                and spl.scheduledType = 1
				and td.dDateTime between spl.StartDate and spl.EndDate
				AND td.dDateTime between :fromDate and :toDate
                AND NOT EXISTS ( SELECT 1
                                    FROM AllocationsDelPersons ADP
                                    WHERE ADP_AllocationsID = AL.AL_AllocationsID
                                    AND spl.ScheduledPersonID = ADP.ADP_SchedulingPersonID
                                    )
                ) FD WHERE AddSPExclFilter = 1
				GROUP BY SchedulingPersonID, FullName order by FullName ASC";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':teamID', $request->get('schedulingTeamId'), PDO::PARAM_INT);
            $stmt->bindValue(':fromDate', date('Y-m-d',strtotime($request->get('fromDate'))), PDO::PARAM_STR);
            $stmt->bindValue(':toDate', date('Y-m-d',strtotime($request->get('toDate'))), PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return [];
    }

    /**
     * This function is used to copy duty from one peron to another
     *
     * @param Request $request
     * @return array
     */
    public function copyDuty(Request $request)
    {
        try {
            $NetLoginId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

            $copyDutyID = $request->get('copyDutyId');
            $fromDate = date('Y-m-d',strtotime($request->get('fromDate')));
            $toDate = date('Y-m-d',strtotime($request->get('toDate')));
            $teamID = $request->get('schedulingTeamId');
            $schduledPersonID = $request->get('schedulingPersonId');

            $query = "exec [dbo].[usp_CopyDuty] @CopyDutyID=?,@FromDate=?,@ToDate=?,@TeamID=?,@SchduledPersonID=?,@pNetLogin=?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $copyDutyID, PDO::PARAM_INT);
            $stmt->bindParam(2, $fromDate, PDO::PARAM_STR);
            $stmt->bindParam(3, $toDate, PDO::PARAM_INT);
            $stmt->bindParam(4, $teamID, PDO::PARAM_INT);
            $stmt->bindParam(5, $schduledPersonID, PDO::PARAM_INT);
            $stmt->bindParam(6, $NetLoginId, PDO::PARAM_STR);
            $stmt->execute();
            $respdata = $stmt->fetch(PDO::FETCH_ASSOC);
            return $respdata;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Get Week Number by Date
     *
     * @param $date This param contains date
     * @return array
     */
    public function getWeekNumberByDate($date = '')
    {
        try {
            $query = "select ixYearWeek from TimeDimension where dDateTime = :date";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!empty($result)){
                return $result;
            }
        } catch (Exception $e) {
            logger()->critical('DB ERROR', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to check duty exists on dates
     *
     * @param Request $request
     * @return integer
     */
    public function checkDutyExists(Request $request)
    {
        $query = "SELECT COUNT(al.AL_AllocationsID) as dutyCount 
				FROM Allocations al
				INNER JOIN TimeDimension TD on TD.ixYearWeek = al.AL_WeekNumber
				INNER JOIN AllocationsScheduledPersons ap on AL_AllocationsID = ap.ASP_AllocationsID
														 AND TD.ixDayInWeek = ap.ASP_iDay
				INNER JOIN AllocationsDuties AD ON AD_AllocationsDutyID = ASP_AllocationsDutyID
				WHERE ap.ASP_SchedulingPersonID = :scheduledPersonId 
				  AND TD.dDateTime BETWEEN :fromDate AND :toDate
				  AND AD.AD_DutyType < 7";
        try {
            $stmt = $this->pdo->prepare($query);
            //$stmt->bindValue(':teamID', $request->get('schedulingTeamId'), PDO::PARAM_INT);
            $stmt->bindValue(':fromDate', date('Y-m-d',strtotime($request->get('fromDate'))), PDO::PARAM_STR);
            $stmt->bindValue(':toDate', date('Y-m-d',strtotime($request->get('toDate'))), PDO::PARAM_STR);
            $stmt->bindValue(':scheduledPersonId', $request->get('schedulingPersonId'), PDO::PARAM_INT);
            $stmt->execute();
            $rowData = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rowData['dutyCount'];
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }

        return 0;
    }

    /**
     * This function is used to get max end date based on team
     *
     * @param Request $request
     * @return array
     */
    public function getCopyDutyEndDate(Request $request)
    {
        $query = "SELECT DATEADD(DAY,-1,min(dDateTime)) as Dutydate
                    FROM  TimeDimension TD
                    INNER JOIN
                    (
                        SELECT top 1 ixYearWeek
                        FROM
                            (
                                SELECT DISTINCT td.ixYearWeek , ap.AL_WeekNumber
                                FROM TimeDimension TD
                                LEFT JOIN Allocations AP ON ap.AL_WeekNumber = td.ixYearWeek AND ap.AL_SchedulingTeamID = :teamID
                                WHERE td.dDateTime >= :dutyDate
                            ) FD
                        WHERE fd.AL_WeekNumber is null
                        ORDER BY fd.ixYearWeek
                    ) TD1 ON TD1.ixYearWeek = TD.ixYearWeek";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':teamID', $request->get('schedulingTeamId'), PDO::PARAM_INT);
            $stmt->bindValue(':dutyDate', $request->get('dutyDate'), PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to get schedulers and sr.schedulers list for the duty belongs to
     *
     * @param Request $request
     * @return array
     */
    public function getSchedulersList(Request $request)
    {
        try {
            $dutyID = $request->get('dutyId');
            $allocationsSpId = $request->get('allocationsSpId');

            $query = "exec [dbo].[usp_GetSchedulerList] ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $allocationsSpId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to get schedule person name
     *
     * @param $schedulePersonId This param contains the scheduled person ID
     * @return array
     */
    public function getDefaultSchedulePerson($schedulePersonId)
    {
        $query = " select UD_DisplayName AS FullName, UD_UserID as ScheduledPersonID from UserDetails where UD_UserID = :schedulePersonId";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':schedulePersonId', $schedulePersonId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Inserts date comment to DateComment table
     *
     * @param Request $request
     * @return bool
     */
    public function addEditDateComment(Request $request): bool
    {
        try {
            $commentId = $request->get('id');
            $date = $request->get('date');
            $teamId = $request->get('teamId');
            $comment =  $request->get('comment');
            $userId = $_SESSION['user']['UserID'];
            $existingData = $this->getDateComment($teamId, [$date])[0] ?? [];
            $dateHistory = new DateTime('now', new DateTimeZone('Europe/London'));
            $history = json_decode($existingData['History'] ?? '[]', true);
            if(!empty($comment) || !empty($history)) { // build history string
                $history[] = sprintf('Date Comment %s by %s On %s %s',
                    empty($comment) ? 'deleted' : (empty($history) ? 'added' : 'changed'),
                    $_SESSION['user']['FullName'],
                    $dateHistory->format('d/m/Y H:i'),
                    !empty($history) && !empty($comment) ? ' From ' . $existingData['Comment'] . ' To ' . $comment : ' '
                );
            }
            $history = json_encode($history);
            $query = "exec [dbo].[usp_mod_DateComment] ?,?,?,?,?,?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(1, $commentId, PDO::PARAM_INT);
            $stmt->bindParam(2, $date, PDO::PARAM_STR);
            $stmt->bindParam(3, $teamId, PDO::PARAM_INT);
            $stmt->bindParam(4, $comment, PDO::PARAM_STR);
            $stmt->bindParam(5, $history, PDO::PARAM_STR);
            $stmt->bindParam(6, $userId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }

    /**
     * Get date comment
     *
     * @param int $teamId
     * @param array $dates
     * @return array
     */
    public function getDateComment(int $teamId, array $dates): array
    {
        try {
            $query = "SELECT
            [ID],
            [CommentDate],
            [TeamId],
            [Comment],
            [History]
            FROM [DateComment] (NOLOCK)
            WHERE [TeamId] = :teamId
            AND [CommentDate] IN (" . "'" . implode("','", $dates) . "'" . ")
            ORDER BY [CommentDate] ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':teamId', $teamId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Create duty from rota pattern
     *
     * @param int $allocationId
     * @param array $teamId
     * @return array
     */
    public function createDutyFromRota(int $allocationId, int $teamId): array
    {
        try {
            //Team details
            $sql = "exec [dbo].[usp_get_ScheduleTeamsdetails] ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $teamId, PDO::PARAM_INT);
            $stmt->execute();
            $teamData = $stmt->fetch(PDO::FETCH_ASSOC);
            $isCreateDutyFromRota = $teamData['IsCreateDutyFromRota'] == 1 ? 1 : 0;
			$allocationsSPID = $_POST['allocationsSPID'];
			$schedulingPersonId = $_POST['schedulingPersonId'];
			$dutyDate = $_POST['dutyDate'];

            //Create duty from rota pattern
            $netLoginId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
            $query = "exec [dbo].[usp_Create_Allocations_From_Rota] @AllocationsSPID = $allocationsSPID, @ScheduledPersonID = $schedulingPersonId, @DutyDate = '$dutyDate', @IsCreateDutyFromRota = $isCreateDutyFromRota, @NetLogin = '$netLoginId'";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * Create duty from rota pattern
     *
     * @param int $allocationId
     * @param array $teamId
     * @return array
     */
    public function getAssignedDutiesToFilter($filterId = 0, $dutyTypeId = 0)
    {
        try {
            $sql = "exec [dbo].[usp_GET_AssignedMasterDutiesFilterLinks] ?,?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $filterId, PDO::PARAM_INT);
            $stmt->bindParam(2, $dutyTypeId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $jsonresult = json_encode($result);
            return $jsonresult;
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return [];
    }

    /**
     * This function is used to filter unallocated duties as per applied master and misc filter
     *
     * @param Request $request
     * @return array
     */
    public function getFilteredUnallocatedDuties($filterId = 0, $unallocatedDuties = [])
    {
        $finalFilteredUnallocDuties = [];
        $getMastMiscFilterDuiesJson  = $this->getAssignedDutiesToFilter($filterId, 1);
        $mastMiscFilterDuties = json_decode($getMastMiscFilterDuiesJson,true);
        if(!empty($unallocatedDuties) && !empty($mastMiscFilterDuties)){
            $filterDutyNames = array_column($mastMiscFilterDuties, 'DutyName');
            $filteredUnallocDuties = array_filter($unallocatedDuties, function($item) use ($filterDutyNames) {
                return in_array($item[0], $filterDutyNames, true);
            });
            $finalFilteredUnallocDuties = array_values($filteredUnallocDuties);
        }
        return $finalFilteredUnallocDuties;
    }
}
