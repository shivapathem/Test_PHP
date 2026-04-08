<?php
function SaveXmasPoints ($SchedulingTeamId, $arrText) {
  try{
        // Open the database
        $pdo = OpenDBLinkA7();
        // Delete the existing
          $strQuery = "DELETE FROM     XmasPoints
          WHERE          (SchedulingTeamId = $SchedulingTeamId)";
          $stmt = $pdo->prepare($strQuery);
          $stmt->execute();

        $count = 1;
        if( !empty($arrText)){
            $strQueryinsert = "INSERT INTO XmasPoints(SchedulingTeamId, ScheduledPersonID, Points)
                          VALUES ";
            foreach ($arrText as $ScheduledPersonID => $intPoints) {
              if(count($arrText) != $count ){
                $strQueryinsert .= "($SchedulingTeamId, $ScheduledPersonID, $intPoints),";
              }else{
                $strQueryinsert .= "($SchedulingTeamId, $ScheduledPersonID, $intPoints)";
              }
              $count++;
            }
        $stmt2 = $pdo->prepare($strQueryinsert);
        $stmt2->execute();
      }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function GetXmasPointsByDepartment($intDepartmentID) {
  $db = OpenDatabase();
  // Delete the existing

  $strQuery = "SELECT          XmasPointsByDepartment.ID, XmasPointsByDepartment.MonthDay, XmasPointsByDepartment.BasicPoints, XmasPointsByDepartment.Limit,
                                XmasPointsByDepartmentLink.ID AS LinkID, XmasPointsByDepartmentLink.StartTime, XmasPointsByDepartmentLink.EndTime, XmasPointsByDepartmentLink.Points
               FROM            XmasPointsByDepartment
               LEFT OUTER JOIN XmasPointsByDepartmentLink ON XmasPointsByDepartment.ID = XmasPointsByDepartmentLink.XmasPointsByDepartmentID
               WHERE           (XmasPointsByDepartment.DepartmentID = $intDepartmentID)
               ORDER BY        XmasPointsByDepartmentLink.StartTime";

  $rsPoints = sqlsrv_query($db, $strQuery);

  while($row = sqlsrv_fetch_array($rsPoints)){
    $arrPoints[$row['MonthDay']]['Basic'] = $row['BasicPoints'];
    $arrPoints[$row['MonthDay']]['Limit'] = $row['Limit'];
    if (!is_null($row['LinkID'])) {
      $arrPoints[$row['MonthDay']]['Times'][$row['LinkID']]['StartTime'] = $row['StartTime'];
      $arrPoints[$row['MonthDay']]['Times'][$row['LinkID']]['EndTime'] = $row['EndTime'];
      $arrPoints[$row['MonthDay']]['Times'][$row['LinkID']]['Points'] = $row['Points'];
    }


  }
  if (isset($arrPoints)) {
    return ($arrPoints);
  }



}

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


/**
     * get scheduled team record of user
     *@param  $teamID
     * @return array
     */
    function getScheduleTeamsdetailsByID($teamID)
    {
        try{
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_ScheduleTeamsdetails] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $teamID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultjson = json_encode($result);
            return $resultjson;
        } catch (PDOException $e) {
          echo $e->getMessage();
      }

    }