<?php
include_once 'DBHelper.php';
include_once 'DB_Functions.php';
/*
    * @Description : Fetch LeaderType Info ByID l.
  * @access : Public
  * @global : Not Applicable
  * @param  : $id
  * @return : JSON output
    */
    function GetLeaderTypeInfoByID($intID) {   
      $pdo = OpenDBLinkA7();
      $sql = "SELECT        shiftleadertypes.description, shiftleadertypes.SchedulingTeamId as schedulingTeamId, shiftleadertypes.telephone, shiftleadertypes.BackColour, schedulingTeams.schedulingTeamName 
               FROM          shiftleadertypes 
               INNER JOIN    schedulingTeams ON shiftleadertypes.SchedulingTeamId = schedulingTeams.schedulingTeamId
               WHERE        (shiftleadertypes.ID = ?)";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(1, $intID, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      $resultjson = json_encode($result);
      return $resultjson;
}

/**
* This function is used to get the types of ShiftLeaders from the DB as per the scheduling teams ID.
*
* @param $intTeamID This param contains the TeamID information.
*
* @return array Returns the ShiftLeaders information.
*/
function GetLeaderTypes($intTeamID) {
   $pdo = OpenDBLinkA7();
   try{
		$query = "SELECT description, SchedulingTeamId, telephone, BackColour, ID
			FROM shiftleadertypes WHERE (SchedulingTeamId = ?) and (Active = 1)
			ORDER BY description";
	    $stmt = $pdo->prepare($query);
	    $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
	    $stmt->execute();
	    $rsLeaderTypes = $stmt->fetchall(PDO::FETCH_ASSOC);
   }catch(PDOException $e){
        logger()->critical('db error', (array) $e);
		$rsLeaderTypes = [];
  }
  foreach($rsLeaderTypes as $row){
		$arrLeaderTypes[$row['ID']]['Description'] = $row['description'];
		$arrLeaderTypes[$row['ID']]['BackColour'] = $row['BackColour'];
		$arrLeaderTypes[$row['ID']]['SchedulingTeamId'] = $row['SchedulingTeamId'];
  }
  try{
	 $query = "SELECT shiftleadertypes.SchedulingTeamId, shiftleadertypes.description, shiftleadertypes.BackColour, shiftleadertypes.ID
			FROM shiftleadertypes_appliesto 
			INNER JOIN  shiftleadertypes ON shiftleadertypes_appliesto.TypeID = shiftleadertypes.ID
			WHERE (shiftleadertypes_appliesto.SchedulingTeamId = ?)";  
		$stmt = $pdo->prepare($query);
		$stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
		$stmt->execute();
		$rsLeaderTypes = $stmt->fetchall(PDO::FETCH_ASSOC);
   }catch(PDOException $e){
        logger()->critical('db error', (array) $e);
		$rsLeaderTypes = [];
   }

  foreach($rsLeaderTypes as $row ){
		$arrLeaderTypes[$row['ID']]['Description'] = $row['description'];
		$arrLeaderTypes[$row['ID']]['BackColour'] = $row['BackColour'];
		$arrLeaderTypes[$row['ID']]['SchedulingTeamId'] = $row['SchedulingTeamId'];
  }	   
  if (isset($arrLeaderTypes)) {
	return ($arrLeaderTypes);
  }

}

/**
* This function is used to get the ShiftLeaders from the DB based on week and day.
*
* @param $week This param contains the week number.
*
* @param $day This param contains the day.
*
* @return array Returns the ShiftLeaders information array.
*/
function GetLeaders ($week, $day) {
  $pdo = OpenDBLinkA7();
  global  $dowMap;
  try{
	$query = "exec [dbo].[usp_GET_ShiftLeaderByDayWeek] ?,?";
	$DOT =intval($day);
	$stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $week, PDO::PARAM_INT);
    $stmt->bindParam(2, $DOT, PDO::PARAM_INT);
    $stmt->execute();
    $leaders = $stmt->fetchall(PDO::FETCH_ASSOC);
  }catch(PDOException $e){
      logger()->critical('db error', (array) $e);
	  $leaders=[];
  }
  foreach($leaders as $row ){
    $leadertype = $row['leadertype'];
    $arrleaders[$leadertype][$row['id']]['id'] = $row['id'];
    $arrleaders[$leadertype][$row['id']]['starttime'] = $row['starttime'];
    $arrleaders[$leadertype][$row['id']]['endtime'] = $row['endtime'];
    $arrleaders[$leadertype][$row['id']]['name'] = $row['FullName'];
    $arrleaders[$leadertype][$row['id']]['telephone'] = $row['telephone'];
    $arrleaders[$leadertype][$row['id']]['overallstarttime'] = $row['starttime'];
    $arrleaders[$leadertype][$row['id']]['overallendtime'] = $row['endtime'];
	$arrleaders[$leadertype][$row['id']]['aftermidnight'] = $row['aftermidnight'];
  }
  if (isset($arrleaders)) {
      $now = 0;
      foreach ($arrleaders as $leadertype => $item) {
       // $item contains the start end times etc.....
      unset($arrtimes);
      $i = 0;
      foreach ($item as $id => $entry) {
        $arrtimes[$i]['starttime'] = $entry['starttime'];
        $arrtimes[$i]['endtime'] = $entry['endtime'];
        $arrtimes[$i]['id'] = $id;
        $i++;
      }
      for ($i=0; $i < count($arrtimes) - 1 ; $i++) {
        $firstendtime = $arrtimes[$i]['endtime'];
        $secondsatarttime = $arrtimes[$i + 1]['starttime'];
        if ($firstendtime > $secondsatarttime)  {
          $arrleaders[$leadertype][$arrtimes[$i]['id']]['endtime'] = $secondsatarttime;
          $arrleaders[$leadertype][$arrtimes[$i + 1]['id']]['starttime'] = $firstendtime;
          // And add a new entry for the gap....
          $arrleaders[$leadertype][$now]['endtime'] = $firstendtime;
          $arrleaders[$leadertype][$now]['starttime'] = $secondsatarttime;
          $now = $now + 1;        
        }
      }
    }	
    return ($arrleaders);
  }
}


/**
* This function is used to get the Draw Leaders from the DB.
*
* @param $typeid This param contains the Type ID.
*
* @param $arrleaders This param contains the Leaders.
*
* @param $earlieststart This param contains Start Time.
*
* @param $hourwidth This param contains Hour Width.
*
* @param $arrleadertypes This param contains Leader Type.
*
* @param $intTeamID This param contains Team ID.
*
* @return Returns the Leaders array.
*/

function drawleaders ($typeid, $arrleaders, $earlieststart, $hourwidth, $arrleadertypes, $intTeamID,$rolepermission,$filterQuery1,$filterQuery2,$filterQuery3,$filterOrderStr,$editpermission,$isshiftleader,$skillFilterDaily,$dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll) {
  $txtleaders = '';
  foreach ($arrleaders as $leader) {
    if (isset($leader['id'])) {
      $id = $leader['id'];
    }
    else {
      $id = -1;
    }

    if (isset($leader['name'])) {
      $name = $leader['name'];
    }
    else {
      $name = '';
    }

    if (isset($leader['telephone'])) {
      $telephone = $leader['telephone'];
    }
    else {
      $telephone = '';
    }
    $starttime = $leader["starttime"];
    $endtime = $leader["endtime"];
    $line = 0;
    if (isset($arrlines)) {
      $counter = 0;

      foreach ($arrlines as $line => $arrsub) {
        $confilct = 0;
        foreach ($arrsub as $times) {
          if ($times['start'] < $endtime && $times['end'] > $starttime) {
            $confilct = 1;
          }
        }
        if ($confilct == 0) {
          break;
        }
        $counter++;
      }
      $arrlines[$counter][$id]['start'] = $starttime;
      $arrlines[$counter][$id]['end'] = $endtime;
    }
    else {
      $arrlines[0][$id]['start'] = $starttime;
      $arrlines[0][$id]['end'] = $endtime;
      $counter = 0;
    }
	$midnightval=0;
    $left = (((($midnightval+$starttime) / 3600) - $earlieststart + 1) * $hourwidth) - $hourwidth;
    $right = (((($midnightval+$endtime) / 3600) - $earlieststart + 1) * $hourwidth) - $hourwidth;
    $width = $right - $left;
    if ($arrleadertypes[$typeid]['SchedulingTeamId'] == $intTeamID) {
	  if ($id == -1) {
        $txtleaders.= '<div qtip-content="Overlap...." align="center" class="tipjob boxed handcursor transparenthashed" style="background-color:'.$arrleadertypes[$typeid]['BackColour'].'; overflow:hidden; width: '.$width.'px; height: 15px; position:absolute; left:'.$left.'px; top:'.($counter * 20).'px;">';
      }
      else {
		  if ((($rolepermission!=0) && ($editpermission==1)) || ($isshiftleader==1 )) {
			if ($isshiftleader==1){
				$rolepermission=1;
			}	  
			$txtleaders.= '<div qtip-content="'.$name.'<br>'.gmdate("H:i", $leader["overallstarttime"]).'-'.gmdate("H:i", $leader["overallendtime"]).'" align="center" id="'.$id.'" onclick=\'javascript:NewEditShiftLeader('.$typeid.', '.$id.','.$rolepermission.',"'.str_replace("'","-",$filterQuery1).'","'.str_replace("'","-",$filterQuery2).'","'.$filterQuery3.'","'.$filterOrderStr.'","'.$skillFilterDaily.'","'.$dutyFilterDaily.'","'.$jobFilterDaily.'","'.$jobNameAll.'","'.$jobLabelAll.'")\' class="tipjob boxed handcursor" style="background-color:'.$arrleadertypes[$typeid]['BackColour'].'; overflow:hidden; width: '.$width.'px; height: 15px; position:absolute; left:'.$left.'px; top:'.($counter * 20).'px;">';
		  } else {
			  $txtleaders.= '<div qtip-content="'.$name.'<br>'.gmdate("H:i", $leader["overallstarttime"]).'-'.gmdate("H:i", $leader["overallendtime"]).'" align="center" id="'.$id.'" class="tipjob boxed handcursor" style="background-color:'.$arrleadertypes[$typeid]['BackColour'].'; overflow:hidden; width: '.$width.'px; height: 15px; position:absolute; left:'.$left.'px; top:'.($counter * 20).'px;">';
		  }
       }
    }
    else {
      if ($id == -1) {
        $txtleaders.= '<div align="center" qtip-content="Overlap...." class="tipjob boxed handcursor transparenthashed" style="background-color:'.$arrleadertypes[$typeid]['BackColour'].'; overflow:hidden; width: '.$width.'px; height: 15px; position:absolute; left:'.$left.'px; top:'.($counter * 20).'px;">';
      }
      else {
        $txtleaders.= '<div qtip-content="'.$name.'<br>'.gmdate("H:i", $leader["overallstarttime"]).'-'.gmdate("H:i", $leader["overallendtime"]).'" align="center" class="tipjob boxed handcursor" style="background-color:'.$arrleadertypes[$typeid]['BackColour'].'; overflow:hidden; width: '.$width.'px; height: 15px; position:absolute; left:'.$left.'px; top:'.($counter * 20).'px;">';
      }
    }
    $txtleaders.= $name;
    if ($telephone != '') {
      $txtleaders.= ' ('.$telephone.')';
    }
    $txtleaders.= '</div>';
  }
  $arret['rows'] = count($arrlines);
  $arret['leaders'] = $txtleaders;
  return ($arret);
}

/**
* This function is used to get the Draw Leaders from the DB.
*
* @param $typeid This param contains the Type ID.
*
* @param $arrleadertypes This param contains Leader Type.
*
* @param $arrleaders This param contains the Leaders.
*
* @param $type This param contains the Type.
*
* @param $earlieststart This param contains Start Time.
*
* @param $lateststart This param contains Latest Start Time.
*
* @param $hourwidth This param contains Hour Width.
*
* @param $intTeamID This param contains Team ID.
*
* @param $currenttop This param contains current Top value.
*
* @param $date This param contains Date.
*
* @param $intPrint This param contains zero as default value.
*
* @return Returns the Leaders array.
*/
function doleaders($typeid = 0, $arrleadertypes = '', $arrleaders = '', $type = '', $earlieststart = '', $lateststart = '', $hourwidth = '', $intTeamID = 0, $currenttop = '', $date = '', $intPrint = 0, $role = 0, $filterQuery1 = '', $filterQuery2 = '', $filterQuery3 = '', $filterOrderStr = '', $editpermission = '', $isshiftleader = 0, $skillFilterDaily = '', $dutyFilterDaily = '', $jobFilterDaily = '', $jobNameAll = '', $jobLabelAll = '') {
  if (isset($arrleaders[$typeid])) {
    $arrdrawleaders = drawleaders($typeid, $arrleaders[$typeid], $earlieststart, $hourwidth, $arrleadertypes, $intTeamID,$role,$filterQuery1,$filterQuery2,$filterQuery3,$filterOrderStr,$editpermission,$isshiftleader,$skillFilterDaily,$dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll);
    $leaderheight = $arrdrawleaders['rows'] * 20;
    $leadertext = $arrdrawleaders['leaders'];
  } else {
    $leaderheight = 20;
    $leadertext = '';
  }    
  
  if (($type['SchedulingTeamId'] == $intTeamID) && (($intPrint == 0) && ($role!=0) && ($editpermission==1) || ($isshiftleader==1 ))) {
	if ($isshiftleader==1){
		$role=1;
  }else{
    $role=0;
  }	  
  
    $strContextMenu = 'sl-context-menu handcursor';
   $strAddNew ='<div style="overflow: hidden; position: absolute; width:25px; height:25px; left:86%; top:0px" class="handcursor" onclick=\'javascript:NewEditShiftLeader('.$typeid.', 0,'.$role.',"'.str_replace("'","-",$filterQuery1).'","'.str_replace("'","-",$filterQuery2).'","'.$filterQuery3.'","'.$filterOrderStr.'","'.$skillFilterDaily.'","'.$dutyFilterDaily.'","'.$jobFilterDaily.'","'.$jobNameAll.'","'.$jobLabelAll.'")\'><img border="0" src="images/menu/new.png" width="18" height="17"></div>';
  } else {
    $strContextMenu = ''; 
    $strAddNew = ''; 
  }

  echo '<div SchedulingTeamId="'.$intTeamID.'" typeid="'.$typeid.'" style="overflow: hidden; position: relative; width:14%; height:'.$leaderheight.'px; left:0px; top:0px" id="fixed"  class="names '.$strContextMenu.'">';
  echo $type['Description'];
  echo $strAddNew;
  echo '</div>';
  // The Shift Leaders
  if ($intPrint == 0) {
    echo '<div id="'.$typeid.'" class="leaderdroppable leaders" style="overflow-y: scroll; overflow-x: hidden; position: absolute; width:86%; height:'.($leaderheight).'px; left:14%; top:'.$currenttop.'px">';
  } else {
    echo '<div id="'.$typeid.'" class="leaderdroppable leaders" style="overflow-y: scroll; overflow-x: hidden;position: absolute; width:100%;  height:'.($leaderheight).'px; left:14%; top:'.$currenttop.'px">';    
  }
  drawtimecells ($date, $earlieststart, $lateststart, $hourwidth, $leaderheight, 0,1);
  echo $leadertext;
  echo '</div>';
  return($leaderheight);
} 

function addUpdateShiftleader($params=[]){
  try {
            $status = $returnstring = '';
			$params['telephone'] = isset($params['telephone']) ? (string) $params['telephone'] : '';
            $pdo = OpenDBLinkA7();
            $sql = "exec [dbo].[usp_mod_shiftleaders] ?,?,?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $params['id'], PDO::PARAM_INT);
            $stmt->bindParam(2, $params['backcolor'], PDO::PARAM_STR);
            $stmt->bindParam(3, $params['telephone'], PDO::PARAM_STR);
            $stmt->bindParam(4, $params['description'], PDO::PARAM_STR);
            $stmt->bindParam(5, $params['schedulingteamid'], PDO::PARAM_INT);
            $stmt->bindParam(6, $status, PDO::PARAM_STR);
            $stmt->bindParam(7, $returnstring, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultjson = json_encode($result);
            return $resultjson;

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

/*
    * @Description : Fetch shift leader by id.
  * @access : Public
  * @global : Not Applicable
  * @param  : $id
  * @return : JSON output
    */
function getShiftLeaderById($id = 0){
        $pdo = OpenDBLinkA7();
        $sql = "SELECT S.description, S.DepartmentID, S.SchedulingTeamId, S.Telephone, S.BackColour, ST.schedulingTeamName 
				FROM          shiftleadertypes  S
				INNER JOIN    schedulingTeams ST ON S.SchedulingTeamId = ST.schedulingTeamId
				WHERE        (S.ID = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;
}

  /*
    * @Description : Shift leader record status change, that is using in delete.
  * @access : Public
  * @global : Not Applicable
  * @param  : $id
  * @return : JSON output
    */
function getShiftActiveInactive($id = 0){
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $status = $returnstring = '';
        $sql = "exec [dbo].[usp_Del_ShiftLeader] ?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->bindParam(2, $status, PDO::PARAM_STR);
        $stmt->bindParam(3, $returnstring, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;
}



  /*
    * @Description : Shift leader record status change, that is using in delete.
  * @access : Public
  * @global : Not Applicable
  * @param  : $id
  * @return : JSON output
    */
    function getAllShiftLeaders($userId = 0, $roleId = 0){
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $sql = "exec [dbo].[usp_GET_AllShiftLeaders] ?, ?";

      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(1, $userId, PDO::PARAM_INT);
	  $stmt->bindParam(2, $roleId, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultjson = json_encode($result);
      return $resultjson; 
}