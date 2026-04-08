<?php
include_once __DIR__ . '/../../../function-includes/DBHelper.php';
include_once __DIR__ . '/../../../class-includes/userRolePermissions.php';
include_once __DIR__ . '/../../../function-includes/genericfunctions.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class classSchedulTeamHistory
{
    /*
    * @Description : Fetch all schedule person team history
	* @access : Public
	* @global : Not Applicable
	* @param  : $personid
	* @return : JSON output
    */
    function getScheduleTeamHistory($personid, $returnArray = 0)
    {

        $result = getScheduledPersonTeamHistory($personid);
        $HomeTeamCounter = 0;
        $DeletePermission = 0;
        foreach($result as $HomeTeamRecord){
            if ($HomeTeamRecord['HomeTeam'] == "Y") {
                $HomeTeamCounter++;
                $StartDateHomeTeam = date_create($HomeTeamRecord['Startdate']);
                $EndDateHomeTeam = date_create($HomeTeamRecord['Enddate']);
                $TodayDate = date_create(date("Y-m-d"));
                if (($StartDateHomeTeam < $TodayDate) && ($EndDateHomeTeam > $TodayDate)) {
                    $HomeTeamId = $HomeTeamRecord['TeamID'];
                    $pageid = 6;
                    $permissions = getUserRoleByTeam($pageid, $HomeTeamId);
                    if ($permissions->candelete == 1) {
                        $DeletePermission = 1;
                    }
                }
            }
        }
        foreach($result as $value){
            $HomeTeamId = $value['TeamID'];
            $pageid = 6;
            $permissions = getUserRoleByTeam($pageid, $HomeTeamId);
			$createdBy = GetPersonNamebyUserId($value['CreatedBy']);
			$createdBy = isset($createdBy['DisplayName']) ? ucwords($createdBy['DisplayName']) : '';
            if((date_create($value['Enddate']) > date_create('9998-12-31')) && ($value['HomeTeam'] == "Y") && (($permissions->candelete == 1) || ($DeletePermission == 1))
                && ($HomeTeamCounter > 1)) {
                $data[] = array($value['ScheduleTeam'],
                    $value['HomeTeam'],
                    '<span class="customdateSort">'.date('Ymd',strtotime($value['Startdate'])).'</span>'.$value['Startdate'],
                    '<span class="customdateSort">'.date('Ymd',strtotime($value['Enddate'])).'</span>'.$value['Enddate'],
                    $value['SortCode'],
                    '<span class="customdateSort">'.date('Ymd',strtotime($value['CreatedDate'])).'</span>'.date('d-m-Y H:i',strtotime($value['CreatedDate'])),
                    $createdBy,
                    '<span class="customdateSort">'. (isset($value['LastUpdatedDate']) ? date('Ymd',strtotime($value['LastUpdatedDate'])) : '') .'</span>'. (isset($value['LastUpdatedDate']) ? date('d-m-Y H:i',strtotime($value['LastUpdatedDate'])) : ''),
                    $value['LastupdatedBy'],
                    $value['IsAvailable'],
                    '<a id ="schPersonDelete" class="viewaction js_historyschteam" href="javascript:void(0)" title="Delete" value="'.$HomeTeamId.'" PersonIdHomeTeam="'.$personid.'"><i class="fa fa-trash anchor-colour iconstyleallocate7"></i></a>');
            } else {
                $data[] = array($value['ScheduleTeam'],
                    $value['HomeTeam'],
                    '<span class="customdateSort">'.date('Ymd',strtotime($value['Startdate'])).'</span>'.$value['Startdate'],
                    '<span class="customdateSort">'.date('Ymd',strtotime($value['Enddate'])).'</span>'.$value['Enddate'],
                    $value['SortCode'],
                    '<span class="customdateSort">'.date('Ymd',strtotime($value['CreatedDate'])).'</span>'.date('d-m-Y H:i',strtotime($value['CreatedDate'])),
                    $createdBy,
                    '<span class="customdateSort">'.(isset($value['LastUpdatedDate']) ? date('Ymd',strtotime($value['LastUpdatedDate'])) : '').'</span>'.(isset($value['LastUpdatedDate']) ? date('d-m-Y H:i',strtotime($value['LastUpdatedDate'])) : ''),
                    $value['LastupdatedBy'],
                    $value['IsAvailable'],
                    '');
            }
        }
        $history = array('data' => $data);
        if($returnArray == 0) {
            echo json_encode($history);
        } else {
            return $history;
        }
    }

    function deleteSchPersonHomeTeam($HomeTeamId, $Schpersonid, $DeleteFromRota) {
		$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] :($_COOKIE['editWeeklyUserId'] ?? '');
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_Mod_DeleteScheduledPersonHomeTeam] ?,?,?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $Schpersonid, PDO::PARAM_INT);
        $stmt->bindParam(2, $HomeTeamId, PDO::PARAM_INT);
        $stmt->bindParam(3, $DeleteFromRota, PDO::PARAM_INT);
        $stmt->bindParam(4, $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }
    function validateSchPersonHomeTeamHaveRota($HomeTeamId, $Schpersonid) {
		
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_get_validateSchPersonHomeTeamHaveRota] ?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $Schpersonid, PDO::PARAM_INT);
        $stmt->bindParam(2, $HomeTeamId, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }
}
