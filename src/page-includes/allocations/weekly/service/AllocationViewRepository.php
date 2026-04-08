<?php
/*
 * Created on Mon Nov 9 2021
 * Name: Class AllocationViewRepository
 * Description: A repository layer for View Allocations
 *
 * Copyright (c) 2021 BBC
 */

require_once __DIR__ . "/../../../../function-includes/DBHelper.php";
require_once __DIR__ . "/../../../../function-includes/helpers.php";
include_once __DIR__.'/../../../../function-includes/common/classCommonDBFunctions.php';
class  AllocationViewRepository 
{
    public $pdo = null;

    /*
      @var \PDO
    protected $pdo;
    */
    public function __construct()
    {
        $this->pdo = OpenDBLinkA7();
    }
   
    /**
     * Description : Export Rota For View Page
     * @param $TeamID int,$current_User_id logged UserId
     * return status int Format
     */
        
    public function makeExport(int $TeamID,int $current_User_id) :int
    {
    try {
        $status = 1;
        $query = "exec [dbo].[usp_ExportRota] ?,?,?";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(1, $TeamID, PDO::PARAM_INT);
        $stmt->bindValue(2, $current_User_id, PDO::PARAM_INT);
        $stmt->bindValue(3, $status, PDO::PARAM_INT);
        $stmt->execute();
        $row =  $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['intstatus'];
    } catch(Exception $e){
        logger()->critical('DB Error', (array) $e);
    }
    return 0;
    } 
    /**
     * Description : readRotaService Complete read rota Service
     * @param int $TeamID,int $CurrentWeekNo,int $intEndWeek,string $filterStrSchPerson,string $filterStr2
     * return array if Sucess OR false
     */

    public function readRotaService($TeamID,$CurrentWeekNo,$intEndWeek,$intMaskAfterUnixDate, $intNoMask,$intMaskType,$intColourWeek,$filterStrSchPerson,$filterStr2)
    {
        //Real All Exported Rota from the Team
        $rotasInfo =$this->readExportedRotaByTeam($TeamID);
        //Real All Schedulled People
        $scheduledPeople=[];
        $scheduledPeople =$this->readScheduledPersonByTeam($TeamID,$CurrentWeekNo,$intEndWeek,$filterStrSchPerson,$filterStr2,$selSchPersonId='');
        //Read RotaWeek From RoataWeek By RotaId,$weekNumber
        for($i=$CurrentWeekNo;$i<=$intEndWeek;$i++)
        {
            foreach($rotasInfo as $Row) {
                $RotaWeekInfo =$this->readRotaWeekFromRotaSetting($Row['RotaID'],$Row['WeeksInRota'],$i);
                $rotares= $this->fetchRotaDuty($RotaWeekInfo['RotaWeek'],$RotaWeekInfo['ixYearWeek'],$Row['RotaID'],$TeamID);
                if(!empty($rotares)) {
                    $resRotas[]=$rotares;
                }
            }
        }
       
        if(!empty($resRotas)) {
            $crota =count($resRotas);
            for($j=0;$j<$crota;$j++) {
              foreach($resRotas[$j] as $row ) {
                $schedulingPersonID = $row["SchedulingPersonID"];
                $arrRota[$schedulingPersonID]["RotaID"] = $row["RotaID"]??0;
                $arrRota[$schedulingPersonID]["RotaWeek"] = $row["RotaWeek"] ?? "0";
                $arrRota[$schedulingPersonID]["StaffNumber"] = $row["StaffNumber"];
                $arrRota[$schedulingPersonID]["FullName"] = $row["DisplayName"];
                $arrRota[$schedulingPersonID]["SortCode"] = $row["SortCode"];
                $arrRota[$schedulingPersonID]["Login"] = $row["NetLogin"];
                $arrRota[$schedulingPersonID]["SchedulingTeamId"] = $row["SchedulingTeamId"];
                $arrRota[$schedulingPersonID]["StaffTextColour"] = $row['StaffTextColour'];    
                $arrRota[$schedulingPersonID]["RotaStarts"] = $row["StartWeek"];//Start For Rota People
                $arrRota[$schedulingPersonID]["WeeksInRota"] = $row['WeeksInRota'];
                $arrRota[$schedulingPersonID][$row["RotaWeek"]][$row["DOTW"]][$row["IsTemplate"]]['Duty'] = $row["DutyName"];
                $arrRota[$schedulingPersonID][$row["RotaWeek"]][$row["DOTW"]][$row["IsTemplate"]]['Duration'] = $row["Duration"];
                $arrRota[$schedulingPersonID][$row["RotaWeek"]][$row["DOTW"]][$row["IsTemplate"]]['StartTime'] =$row["StartTime"];
                if($row["EndTime"]>86400) {
                    $row['EndTime']=$row['EndTime']-86400;
                  }
                $arrRota[$schedulingPersonID][$row["RotaWeek"]][$row["DOTW"]][$row["IsTemplate"]]['EndTime'] =$row["EndTime"];
                $arrRota[$schedulingPersonID]["ThisSched"] = 1; 
                $colorid =$row["DutyColorID"];
                $arrRota[$schedulingPersonID][$row["RotaWeek"]][$row["DOTW"]][$row["IsTemplate"]]['DutyColorId'] =$colorid;
                if ($intColourWeek == 1 && $colorid!=0) {
                    try {
                        $ColorSQl = "exec [dbo].[usp_getMasterColorValueByColorId] ?";
                        $stmt = $this->pdo->prepare($ColorSQl);
                        $stmt->bindParam(1, $colorid, PDO::PARAM_INT);
                        $stmt->execute();
                        $colorRes = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        logger()->critical('DB Error', (array)$e);
                    }
                    if (!empty($colorRes) && count($colorRes) && !empty($colorRes['ColourBackground']) && !empty($colorRes['ColourFont'])) {
                        $Dutybackcolour = '#' . $colorRes['ColourBackground'];
                        $DutyTextColour = '#' . $colorRes['ColourFont'];
                    } else {
                        $Dutybackcolour = '#EFEFEF';
                        $DutyTextColour = '#330066';
                     }
                    
                     } else {
                    $Dutybackcolour = '#EFEFEF';
                    $DutyTextColour = '#330066';
                    }
                    $arrRota[$schedulingPersonID][$row["RotaWeek"]][$row["DOTW"]][$row["IsTemplate"]]['backcolour'] = $Dutybackcolour;
                    $arrRota[$schedulingPersonID][$row["RotaWeek"]][$row["DOTW"]][$row["IsTemplate"]]['allocateTextColour'] = $DutyTextColour;
               }
            }
         }
        $arrAllocation = $this->MakeRotaAllocations($CurrentWeekNo,$intEndWeek,$intMaskAfterUnixDate, $intNoMask,$intMaskType,$scheduledPeople,$arrRota);
        if(isset($arrAllocation) && !empty($arrAllocation)) {
            return $arrAllocation;
        }else {
            return false;
        }
    }
    /**
     * Description :MakeRotaAllocations
     * @param int $CurrentWeekNo,int $intEndWeek,array $scheduledPeople, $arrRota
     * return array
     */

    private function MakeRotaAllocations(int $CurrentWeekNo,int $intEndWeek,$intMaskAfterUnixDate, $intNoMask,$intMaskType,array $scheduledPeople,$arrRota) 
    {
        $arrAllocations=[];
        $commonObj =new classCommonDBFunctions();
        foreach ($scheduledPeople as $row) {
        if(!empty($row['DisplayName'])) {
            $scheduledPersonId = $row['ScheduledPersonID'];
            $arrAllocations[$scheduledPersonId]["RotaID"] = $arrRota[$scheduledPersonId]["RotaID"]?? "0";
            $arrAllocations[$scheduledPersonId]["RotaWeek"] = $arrRota[$scheduledPersonId]["RotaWeek"]?? "0";
            $arrAllocations[$scheduledPersonId]["StaffNumber"] = $arrRota[$scheduledPersonId]["StaffNumber"]??$row['StaffNumber'];
            $arrAllocations[$scheduledPersonId]["SchedulingPersonID"] = $scheduledPersonId;
            $arrAllocations[$scheduledPersonId]["FullName"] = $arrRota[$scheduledPersonId]["FullName"]??$row['DisplayName'];
            $arrAllocations[$scheduledPersonId]["SortCode"] = $arrRota[$scheduledPersonId]["SortCode"]??$row['SortCode'];
            $arrAllocations[$scheduledPersonId]["Login"] = $arrRota[$scheduledPersonId]["Login"]??$row['Login'];
            $arrAllocations[$scheduledPersonId]["TeamID"] = $arrRota[$scheduledPersonId]["SchedulingTeamId"]??$row['TeamID'];
            $arrAllocations[$scheduledPersonId]["StaffTextColour"]=@$arrRota[$scheduledPersonId]["StaffTextColour"];
            $arrAllocations[$scheduledPersonId]["ThisSched"] = 1;
            $intWeekOfRota=@$arrRota[$scheduledPersonId]["RotaWeek"];
    // Now read this into the dates.....  
    $intCurWeek = $CurrentWeekNo;
    while ($intCurWeek <= $intEndWeek) {
       for ($i = 0; $i <= 6; $i++) {
           $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["edited"] = 0;
           $strDutyname = $arrRota[$scheduledPersonId][$intWeekOfRota][$i] ?? 'U';
           $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["Duty"] = $strDutyname;
           $arrAllocations[$scheduledPersonId][$intCurWeek][$i]['HasLock'] = 0;
           if (isset($arrRota[$scheduledPersonId][$intWeekOfRota][$i])) {
                $intIsWorking=1;
            } else {
                $intIsWorking=0;
            }
           $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["isworking"] = $intIsWorking;
           $datefromweek = $commonObj->GetWeekStartDateByWeekNoFromTimeDim($intCurWeek,'ByweeknoAndIDayOnly',$i);
           $thisdate = $datefromweek['dDateTime'];
           if (strtotime((string) $thisdate) > $intMaskAfterUnixDate && $intNoMask == 0) {
                $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["Duty"] = $this->MaskDuty($strDutyname, $intMaskType);
                if ($intMaskType == 1) {
                    $CellClass = $CellClass = "NotFixedOFF";
                }
                else {
                    if ($intIsWorking == 0 ) {
                        $CellClass = "NotFixedOFF";
                    }
                    else {
                        $CellClass = "NotFixedON";
                    }
                }
            } else {
            if ($strDutyname == 'U') {              
                $CellClass = "NotFixedOFF";
            } else {
                $CellClass = "NotFixedON"; 
            }
          }

        $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["CellClass"] = $CellClass;
        $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["TextColour"] = '#000000';
        $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["Dutycomments"] =  0;
        $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["personcomments"] =  0;
        }
        $intCurWeek = addweeks($intCurWeek, 1);
      }//while Close
  }
}
    return $arrAllocations;
    }

    /**
     * Description : fetchRotaDuty gives Duties Under Rota By Schedule People
     * @param int $RotaWeek,int $RotaID,int $TeamID
     * return array if success Or false
     */

   private function fetchRotaDuty($RotaWeek,$WeekNo,$RotaID,$TeamID)
   {
    $commonObj =new classCommonDBFunctions();
    $datefromweek =$commonObj->GetWeekStartDateByWeekNoFromTimeDim($WeekNo,'ByweeknoOnly',NULL);
    $WeekStartDate = $datefromweek['dDateTime'];
    try {
        $query ="exec [dbo].[usp_read_Rota_Duties] ?,?,?,?";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(1, $TeamID, PDO::PARAM_INT);
        $stmt->bindValue(2, $RotaID, PDO::PARAM_INT);
        $stmt->bindValue(3, $RotaWeek, PDO::PARAM_INT);
        $stmt->bindValue(4, $WeekStartDate, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
                logger()->critical('fetchRotaDuty DB Error', (array) $e);
            }
        return false;
   }
   
     /**
     * Description : readScheduledPersonByTeam gives ScheduledPersonByTeam List
     * @param int $TeamID,int $CurrentWeekNo,$intEndWeek,$filterStrSchPerson,$filterStr2
     * return array if success Or false
     */
  
    public function readScheduledPersonByTeam($TeamID,$CurrentWeekNo,$intEndWeek,$filterStrSchPerson,$filterStr2, $selSchPersonId)
    {
        $commonObj = new classCommonDBFunctions();
        $datefromweek =$commonObj->GetWeekStartDateByWeekNoFromTimeDim($CurrentWeekNo,'ByweeknoOnly',NULL);
        $dteStartDate = $datefromweek['dDateTime'];
        try {
            $filterStr2 = str_replace('spl.TeamId','a.SchedulingTeamId',$filterStr2);
            $query = "exec [dbo].[usp_readScheduledPersonByTeam] '".$TeamID."','".$dteStartDate."','".str_replace("'","''",$filterStrSchPerson)."','".str_replace("'","''",$filterStr2)."', '".$selSchPersonId."'";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
                logger()->critical('DB Error', (array) $e);
            }
        return false;
    }

    /**
     * Description : readExportedRotaByTeam gives exported Rota 
     * @param int $TeamID
     * return array if success Or false
     */
    private function readExportedRotaByTeam($TeamID)
    {
        try
        {
            $query = "SELECT * FROM Exported_rota WHERE SchedulingTeamId=?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $TeamID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e){
                logger()->critical('readExportedRotaByTeam DB Error', (array) $e);
            }  
        return false;
    }

    /**
     * Description :checkkWeekPublishStatus
     * @param $intTeamID,$intWeekNumber
     * return [] if sucess or false
     */

    private function readRotaWeekFromRotaSetting($RotaId,$WeeksinRota,$CurrentWeekNo)
    {
        try
        {
            $query = "SELECT ?, T11.ixYearWeek, dbo.GetWeekofRota(?, '199601', T11.ixYearWeek) as RotaWeek FROM dbo.TimeDimension T11
            WHERE 
            T11.dDateTime >= CONVERT(DATETIME, '30-12-1995' + ' 00:00:00', 103) and
                (T11.dDateTime BETWEEN DATEADD(MM, - 36, GETDATE()) AND DATEADD(MM, 120, GETDATE()))
            AND ixDayInWeek = 0 AND ixYearWeek=?
            ORDER by ixYearWeek desc";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, $RotaId, PDO::PARAM_INT);
            $stmt->bindValue(2, $WeeksinRota, PDO::PARAM_INT);
            $stmt->bindValue(3, $CurrentWeekNo, PDO::PARAM_INT);
            $stmt->execute();
            $Result= $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty($Result)){
            return $Result;
        } else {
            return false; 
         }
        } catch(Exception $e){
                logger()->critical('readRotaWeekFromRotaSetting DB Error', (array) $e);
            }  
        return false;
    }
    /**
     * Description :checkkWeekPublishStatus
     * @param $intTeamID,$intWeekNumber
     * return int
     */
   function checkkWeekPublishStatus($intTeamID,$intWeekNumber):int
   {
        $query = "exec [dbo].[usp_checkkWeekPublishStatus] :startWeek,:teamId";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':startWeek', $intWeekNumber, PDO::PARAM_INT);
            $stmt->bindValue(':teamId', $intTeamID, PDO::PARAM_INT);
            $stmt->execute();
            $row =  $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'] ?? 0;
            } catch(Exception $e) {
            logger()->critical('DB Error', (array) $e);
         }
       return 0;
   }

   /**
    *   This function is used to return the Duty Name based on the masking type.   
    * 
    * @param $strDutyname This param contains the Duty Name information
    * @param $intMaskType This param contains the Mask Type information
    * 0 - Hide All Letters
    * 1 - Show First Letter Only
    * 2 - Show All Letter
    * @return string Returns the Duty Name as per the masking type
    */

    function MaskDuty($strDutynameData, $intMaskType)
        {
            if (is_array($strDutynameData)) {
                if (sizeof($strDutynameData)>1) {
                    foreach ($strDutynameData as $key=>$DutyData) {
                        if ($key==0) {
                         $strDutyname =$DutyData['Duty'];
                       }
                    }
                }else {
                    foreach ($strDutynameData as $DutyData) {
                        $strDutyname= $DutyData['Duty'];
                       }
                }
            }else {
                $strDutyname =$strDutynameData;   
            }
            switch ($intMaskType) {
                case 0 :
                $returnDuty = '';
                break;
                case 1:
                if (is_null($strDutyname)) {
                    $returnDuty = "Z";
                } else {
                    if (is_numeric(substr($strDutyname, 2, 1)) || substr($strDutyname, 2, 1) == " ") {
                    $returnDuty = substr($strDutyname, 0, 1);
                    } else {
                        $returnDuty = $strDutyname;
                    }
                }
                break;
                case 2:
                $returnDuty = $strDutyname;
                break;
            }
        return $returnDuty;

        }

/**
    * Description : CountUnExported Rota By Team ID
    * @param $TeamID int
    * return int
*/
    public function CountUnExportedRota(int $TeamID) :int
    {
    try {
        $query = "SELECT count(*) as tot FROM MasterRotas WHERE TeamID=? AND IsActive=1 AND IsExported=0";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(1, $TeamID, PDO::PARAM_INT);
        $stmt->execute();
        $row =  $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['tot'];
    } catch(Exception $e){
        logger()->critical('DB Error', (array) $e);
    }
    return 0;
    } 


}