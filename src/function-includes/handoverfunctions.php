<?php

include_once "../../function-includes/DBHelper.php";
include_once '../../function-includes/genericfunctions.php';

/*
* @Description : Get All Handover by login user of Selected Date.
* @access : Public
* @global : Not Applicable
* @param  : $strDate and $intschedulingTeamId
* @return : Array Output
*/
function GetHandoversByLoginByDate($intschedulingTeamId, $strDate) {
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_Get_HandoversByLoginByDate] ?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intschedulingTeamId, PDO::PARAM_INT);
    $stmt->bindParam(2, $strDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

/*
* @Description : INSERT Handover
* @access : Public
* @global : Not Applicable
* @param  : $strFullName,$messagedate,$now,$content,$intTeamID
* @return : will return response array
*/
function insertHandover($strFullName,$messagedate,$now,$content,$intTeamID)
{

$response_array = array();
try {
    // Open the database
$pdo = OpenDBLinkA7();

$sql = "INSERT INTO Handovers (fullname, messagedate, posttime, messagebody, SchedulingTeamId) VALUES (?,?,?,?,?)";
$stmt= $pdo->prepare($sql);
$insertResult = $stmt->execute([$strFullName, $messagedate,$now,$content,$intTeamID]);
if($insertResult =='0'){
    $response_array ['status'] = "Failed";
    $response_array ['message'] = $stmt->errorCode();
}
else{
    $response_array ['status'] = "Success";
    $response_array ['message'] = "Handovers has been inserted successfully.";
}
} catch (PDOException $e) {
$response_array ['status'] = "Error";
$response_array ['message'] =$e->getMessage();

}
return json_encode($response_array);
}

/*
* @Description : Update Handover
* @access : Public
* @global : Not Applicable
* @param  : $strFullName,$now,$content,$handoverid
* @return : will return response array
*/
function updateHandover($content, $now, $strFullName, $handoverid)
{
    $response_array = array();

try {

    // Open the database
    $pdo = OpenDBLinkA7();

    $sql = "UPDATE Handovers SET messagebody=?, messageupdated=?, updatedby=? WHERE id=?";
    $affectedRows  = $pdo->prepare($sql)->execute([$content, $now, $strFullName, $handoverid]);
    if($affectedRows =='0'){
        $response_array ['status'] = "Failed";
        $response_array ['message'] = "Handovers has not been updated.";
    }
    else{
        $response_array ['status'] = "Success";
        $response_array ['message'] = "Handovers has been updated successfully.";
    }

} catch (PDOException $e) {
    $response_array ['status'] = "Error";
    $response_array ['message'] =$e->getMessage();

}
return json_encode($response_array);
}

/*
* @Description : get Handover message
* @access : Public
* @global : Not Applicable
* @param  : $handoverid
* @return : string
*/

function getHandoverMessage($handoverid)
{
try {
    // Open the database
    $pdo = OpenDBLinkA7();

    $sql = "SELECT messagebody FROM Handovers Where id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $handoverid, PDO::PARAM_INT);
    $stmt->execute();
    $resultData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($resultData)) {
        return $resultData['messagebody'];
    } else {
        return '';
    }

} catch (PDOException $e) {
    echo $e->getMessage();
}


}

/*
* @Description : in active/delete Handover
* @access : Public
* @global : Not Applicable
* @param  : $handoverid,$now, $strFullName
* @return : will return response array
*/
function inRemovedHandover($now,$strFullName,$handoverid)
{
$deleted = 1;
$response_array = array();
try {
        // Open the database
        $pdo = OpenDBLinkA7();
        $sql = "UPDATE Handovers SET deleted=?, messageupdated=?, updatedby=? WHERE id=?";
        $affectedRows  = $pdo->prepare($sql)->execute([$deleted, $now, $strFullName, $handoverid]);
        if($affectedRows =='0'){
            $response_array ['status'] = "Failed";
            $response_array ['message'] = "Handovers has not been removed.";
        }
        else{
            $response_array ['status'] = "Success";
            $response_array ['message'] = "Handovers has been removed successfully.";
        }

    } catch (PDOException $e) {
        $response_array ['status'] = "Error";
        $response_array ['message'] =$e->getMessage();
    }
return json_encode($response_array);
}
