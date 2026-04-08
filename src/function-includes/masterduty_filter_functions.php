<?php

/* Description : Insert  Master Duty Filter 
    Created By: Naveeta Sharma
    Created On : 13-april-2021
*/
function InsUpdMasterDutyFilter($filterID,$strFilterName,$strComment,$userID, $schedulingTeam = 0) {
    

    //set the output parameter

    $intStatus = 1;
    $strStatus = '';
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_MasterDutyFilter] ?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $filterID, PDO::PARAM_INT);
    $stmt->bindParam(2, $strFilterName, PDO::PARAM_STR);
    $stmt->bindParam(3, $userID, PDO::PARAM_INT);
    $stmt->bindParam(4, $strComment, PDO::PARAM_STR);
    $stmt->bindParam(5, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(6, $strStatus, PDO::PARAM_STR);
    $stmt->bindParam(7, $schedulingTeam, PDO::PARAM_INT);
    $stmt->execute();
    if ($filterID == 0 ){
        $strQueryLastId = "SELECT TOP 1 MasterDutyFilterID FROM MasterDutiesFilter (NOLOCK) ORDER BY MasterDutyFilterID DESC";
        $stmtLastId = $pdo->prepare($strQueryLastId);
        $stmtLastId->execute();
        $resLastId = $stmtLastId->fetch(PDO::FETCH_ASSOC);
        $filterID = $resLastId['MasterDutyFilterID'];
    }
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(!empty($result)){
        $intStatus = false;
        $strStatus = "A Master duty filter with this name already exists";
        return [$filterID,$intStatus, $strStatus];
  
      }
      return [$filterID,$intStatus, $strStatus];
    
    
}

/* Description : Get  Master Duty Filter 
    Created By: Naveeta Sharma
    Created On : 13-april-2021
*/
function GetMasterDutyFilter($teamID = '0', $intUserID = 0) {
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_MasterDutiesFilter] ?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $filterId = 0;
    $stmt->bindParam(1, $filterId, PDO::PARAM_INT);
    $stmt->bindParam(2, $teamID, PDO::PARAM_STR);
    $stmt->bindParam(3, $intUserID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(isset($result[0]['status']) && $result[0]['status'] == 0)
    {
        $result = [];
    }
    $jsonresult = json_encode($result);
    return $jsonresult;
}

/* Description : Get  Assigned Duty To Filter From Links table
    Created By: Naveeta Sharma
    Created On : 14-april-2021
*/
function GetAssignedDutiesToFilter($filterID,$dutyTypeID){

    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_AssignedMasterDutiesFilterLinks] ?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $filterID, PDO::PARAM_INT);
    $stmt->bindParam(2, $dutyTypeID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
    $jsonresult = json_encode($result);
    return $jsonresult;
    
}

/* Description : Delete  Master Duty Filter From Links table
    Created By: Naveeta Sharma
    Created On : 14-april-2021
*/

function DelMasterDutyFilter($filterID){
    
    $intStatus = 1;
    $strStatus = '';
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_del_MasterDutiesfilterlinks] ? ";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $filterID, PDO::PARAM_INT);
   
    $stmt->execute();
    
    return [$filterID,$intStatus, $strStatus];
}

/* Description : Active/de-active/public/private  Master Duty Filter From Links table
    Created By: Naveeta Sharma
    Created On : 14-april-2021
*/

function ActionMasterDutyFilter($filterID,$actionKey,$actionValue,$userId){

    $intStatus = 1;
    $strStatus = '';
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_MasterDutyFilterAction] ?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $filterID, PDO::PARAM_INT);
    $stmt->bindParam(2, $actionKey, PDO::PARAM_STR);
    $stmt->bindParam(3, $actionValue, PDO::PARAM_INT);
    $stmt->bindParam(4, $userId, PDO::PARAM_INT);
    $stmt->bindParam(5, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(6, $strStatus, PDO::PARAM_STR);
    $stmt->execute();
    
    return [$filterID,$intStatus, $strStatus];
}

/* Description : Get  Avaialble  Duty To Filter
    Created By: Naveeta Sharma
    Created On : 14-april-2021
*/
function GetAvaialbleDutiesToFilter($filterID,$dutyTypeID, $teamID = '0'){
    //print_r($filterID);exit();
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_AvaialbleMasterDutiesFilterLinks] ?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $filterID, PDO::PARAM_INT);
    $stmt->bindParam(2, $dutyTypeID, PDO::PARAM_INT);
    $stmt->bindParam(3, $teamID, PDO::PARAM_STR);

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(isset($result[0]['status']) && $result[0]['status'] == 0)
    {
        $result = [];
    }
    $jsonresult = json_encode($result);
    return $jsonresult;
    
}



/* Description :Insert Master/Misc Duty Fillter links
    Created By: Naveeta Sharma
    Created On : 19-april-2021
*/
function InsertAvailableDutiesToFilter($filterID,$masterDutyIDs,$isMiscDuty,$actionType){

    $pdo = OpenDBLinkA7();
    $intStatus = 1;
    $strStatus = '';
    // Set the statement to use
    foreach($masterDutyIDs as $key=> $masterDutyData){
        $sql = "exec [dbo].[usp_ins_MasterDutyFilterLinks] ?,?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $filterID, PDO::PARAM_INT);
        $stmt->bindParam(2, $masterDutyData, PDO::PARAM_INT);
        $stmt->bindParam(3, $actionType, PDO::PARAM_INT);
        $stmt->bindParam(4, $isMiscDuty, PDO::PARAM_INT);
        $stmt->bindParam(5, $intStatus, PDO::PARAM_INT);
        $stmt->bindParam(6, $strStatus, PDO::PARAM_STR);
        $stmt->execute();
    }
   
   
    return [$intStatus, $strStatus];
    
}

?>