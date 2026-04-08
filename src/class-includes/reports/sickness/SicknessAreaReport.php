<?php
include_once '../../../function-includes/DBHelper.php';
include_once '../../../page-includes/admin/divisions/process/classDivisionalAdmin.php';

class SicknessAreaReport {

    protected $userID, $netLogin;

    public function __construct()
    {
        $this->userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
        $this->netLogin =  isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ? $_SESSION['user']['user'] :$_COOKIE['editWeeklyUserNetLogin'];
    }

    function getSicknessAreaReport(int $areaId, string $startDate, string $endDate) {
        //Check access
        $areaObj = new ClassDivisionalAdmin();
        $accessibleArea = $areaObj->getDivisionsListBasedOnAreaReportRole();
        $accessible = false;
        foreach($accessibleArea as $areaAccessible) {
            if($areaAccessible['DivisionID'] == $areaId) {
                $accessible = true;
            }
        }
        if(!$accessible) {
            echo 'Access denied'; die;
        }

        // Get report data
        return $this->spSicknessAreaReport($areaId, $startDate, $endDate);
    }

    function getSicknessAreaReportOccurencesBreach(int $areaId, string $startDate, string $endDate) {
        //Check access
        $areaObj = new ClassDivisionalAdmin();
        $accessibleArea = $areaObj->getDivisionsListBasedOnAreaReportRole();
        $accessible = false;
        foreach($accessibleArea as $areaAccessible) {
            if($areaAccessible['DivisionID'] == $areaId) {
                $accessible = true;
            }
        }
        if(!$accessible) {
            echo 'Access denied'; die;
        }

        // Get report data
        return $this->spSicknessOccurrencesbreachAreaReport($areaId, $startDate, $endDate);
    }

    function spSicknessAreaReport(int $areaId, string $startDate, string $endDate) {
        $pdo = OpenDBLinkA7();   
        $query = "exec [dbo].[usp_get_SicknessByArea] :areaId, :startDate, :endDate";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = [];
        $count = 1;
        do {
            $resultData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($resultData) > 0 ) {
                $data[] = $resultData;
            }
            $count++;         
        } while ($stmt->nextRowset());
        if(count($data) == 1) {
            $data[1] = $data[0];
            $data[0] = [];
        }
        if(count($data) == 3) {
            $data[3] = $data[2];
            $data[2] = [];
        }
        return $data;
    }

    function spSicknessOccurrencesbreachAreaReport(int $areaId, string $startDate, string $endDate) {
        $pdo = OpenDBLinkA7();   
        $query = "exec [dbo].[usp_Sickness_Occurences_Report_Area] :areaId, :startDate, :endDate";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}