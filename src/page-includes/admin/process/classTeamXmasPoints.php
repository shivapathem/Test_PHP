<?php

include_once '../../../function-includes/DBHelper.php';
include_once '../../../class-includes/userRolePermissions.php';


/*
* @Description : Class for handling schedulling team database queries.
* @access : Public
* @global : Not Applicable
*/

class ClassTeamXmasPoints
{
    /*
    * @Description : Get XmasPoints By Scheduling Team.
    * @access : Public
    * @global : Not Applicable
    * @param  : $intUserId
    * @return : JSON Output
    */
function getXmasPointByTeam($teamID) {
try{ 
// Open the database
$pdo = OpenDBLinkA7();
// Set the statement to use
$sql = "exec [dbo].[usp_get_XmasPointByTeam] ?";
$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1, $teamID, PDO::PARAM_INT);
$stmt->execute();
$resultXmasPoint = $stmt->fetchAll(PDO::FETCH_ASSOC);
$arrPoints = array();
if (!empty($resultXmasPoint)) {
foreach($resultXmasPoint as $row){
    $arrPoints[$row['MonthDay']]['Basic'] = $row['BasicPoints'];
    $arrPoints[$row['MonthDay']]['Limit'] = $row['Limit'];
    if (!is_null($row['LinkID'])) {
        $arrPoints[$row['MonthDay']]['Times'][$row['LinkID']]['StartTime'] = $row['StartTime'];
        $arrPoints[$row['MonthDay']]['Times'][$row['LinkID']]['EndTime'] = $row['EndTime'];
        $arrPoints[$row['MonthDay']]['Times'][$row['LinkID']]['Points'] = $row['Points'];
    }
} 
}
return json_encode($arrPoints);
} catch (PDOException $e) {
    echo $e->getMessage();
}

}

/*
* @Description : Get XmasPoints of Day By Scheduling Team.
* @access : Public
* @global : Not Applicable
* @param  : $intUserId
* @return : JSON Output
*/

function modXmasPointDayByTeam($params) {
try{ 
// Open the database
$pdo = OpenDBLinkA7();
// Set the statement to use
$sql = "exec [dbo].[usp_mod_XmasPointDayByTeam] ?,?,?,?";
$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1, $params['teamid'], PDO::PARAM_INT);
$stmt->bindParam(2, $params['basicpoints'], PDO::PARAM_INT);
$stmt->bindParam(3, $params['limit'], PDO::PARAM_INT);
$stmt->bindParam(4, $params['day'], PDO::PARAM_INT);
$stmt->execute();
$resultXmasPointByDay = $stmt->fetch(PDO::FETCH_ASSOC);
return json_encode($resultXmasPointByDay);
} catch (PDOException $e) {
    echo $e->getMessage();
}
}

/*
* @Description : Modifiy XmasPoints of Sub(time) By Scheduling Team.
* @access : Public
* @global : Not Applicable
* @param  : $intUserId
* @return : JSON Output
*/


function modXmasPointTimeByTeam($params) {  
try{
// Open the database
$pdo = OpenDBLinkA7();
// Set the statement to use
$sql = "exec [dbo].[usp_mod_XmasPointTimeByTeam] ?,?,?,?,?,?";
$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1, $params['teamid'], PDO::PARAM_INT);
$stmt->bindParam(2, $params['subxmaspointid'], PDO::PARAM_INT);
$stmt->bindParam(3, $params['day'], PDO::PARAM_INT);
$stmt->bindParam(4, $params['starttime'], PDO::PARAM_INT);
$stmt->bindParam(5, $params['endtime'], PDO::PARAM_INT);
$stmt->bindParam(6, $params['points'], PDO::PARAM_INT);
$stmt->execute();
$resultXmasPointByDay = $stmt->fetch(PDO::FETCH_ASSOC);
return json_encode($resultXmasPointByDay);
} catch (PDOException $e) {
    echo $e->getMessage();
}
}
/*
* @Description : Delte XmasPoints of Sub(time) By Scheduling Team.
* @access : Public
* @global : Not Applicable
* @param  : $xmaspointID
* @return : JSON Output
*/
function delXmasPointTimeByTeam($xmaspointID) {
    try {    
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_del_XmasPointTimeByTeam] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $xmaspointID, PDO::PARAM_INT);
        $stmt->execute();
        
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    
}

/*
* @Description : Delte XmasPoints of Sub(time) By Scheduling Team.
* @access : Public
* @global : Not Applicable
* @param  : $xmaspointID
* @return : JSON Output
*/
function getXmasPointDayDetail($teamID,$day) {
try{
// Open the database
$pdo = OpenDBLinkA7();
// Set the statement to use
$sql = "exec [dbo].[usp_get_XmasPointDayDetail]?,?";
$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1, $teamID, PDO::PARAM_INT);
$stmt->bindParam(2, $day, PDO::PARAM_INT);

$stmt->execute();
$resultXmasPointByDay = $stmt->fetch(PDO::FETCH_ASSOC);
return json_encode($resultXmasPointByDay);
} catch (PDOException $e) {
    echo $e->getMessage();
}
    
}
/*
* @Description : Delte XmasPoints of Sub(time) By Scheduling Team.
* @access : Public
* @global : Not Applicable
* @param  : $xmaspointID
* @return : JSON Output
*/
function getXmasPointSubDetail($subID) {
try{
// Open the database
$pdo = OpenDBLinkA7();
// Set the statement to use
$sql = "exec [dbo].[usp_get_XmasPointSubDetail]?";
$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1, $subID, PDO::PARAM_INT);
$stmt->execute();
$resultXmasPointByDay = $stmt->fetch(PDO::FETCH_ASSOC);
return json_encode($resultXmasPointByDay);
} catch (PDOException $e) {
    echo $e->getMessage();
}
    
}
}
