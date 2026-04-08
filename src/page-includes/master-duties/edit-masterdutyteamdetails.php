<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';

$intDutyID = $_REQUEST["dutyid"];
$intDutyType = $_REQUEST["dutytypeid"];
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'];

$intIsRota = $_SESSION['isrota'];
if (!(isset($intIsRota))) {
  $intIsRota = 1;
  $_SESSION['isrota'] = 1;
}
$intType = 0;
$intCount = 0;

//Check User Authentication
if ($intIsRota == 1) {
    $pageid = 6;
    $perms = $_SESSION['areaperms'];
    for($ArraySeq = 0; $ArraySeq < count($perms); $ArraySeq++){
        if($perms[$ArraySeq]["formid"] == $pageid){
            $canview = $perms[$ArraySeq]["isview"];
            $canmodify = 0;
            $canviewextended = $perms[$ArraySeq]["isviewextended"];
            $canmodifyextended = 0;
            $candelete = 0;
        }
    }
}
else {
    $pageid = 4;
    $perms = $_SESSION['areaperms'];
    for($ArraySeq = 0; $ArraySeq < count($perms); $ArraySeq++){
        if($perms[$ArraySeq]["formid"] == $pageid){
            $canview = $perms[$ArraySeq]["isview"];
            $canmodify = $perms[$ArraySeq]["ismodify"];;
            $canviewextended = $perms[$ArraySeq]["isviewextended"];
            $canmodifyextended = $perms[$ArraySeq]["ismodifyextended"];;
            $candelete = $perms[$ArraySeq]["isdelete"];;
        }
    }
}

//check if we show the form
if ($canview == 1) {
  // Open the database
  $rsAreaTeams = GetUserAreaTeamsList ($intUserID);
  $arrDutyTeams = AreaTeamsListToArray($rsAreaTeams);

  $rsCurrDutyTeamsJson = GetDutyTeamsByDutyID($intDutyID);
  $rsCurrDutyTeams = json_decode($rsCurrDutyTeamsJson,true);
  for ($row = 0; $row < count($rsCurrDutyTeams); $row++) {
    $arrCurrDutyTeams[$row['id']] = 1;
    $intCount = $intCount + 1;
  }

  echo '<form id="neweditdutyteams">';
  echo '<table id="dutyteams" class="smalltable handcursor" width="100%">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>Available</th>';
  echo '<th>Assigned</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  echo'<tr>';
  echo'<td>';
  // A able with the bases the user is NOT in
  echo '<table width="100%" id="teamsnotinduty" class="teamsnotinduty">';
  foreach ($arrDutyTeams as $intTeamID => $arrDutyTeam) {
    if(!isset($arrCurrDutyTeams[$intTeamID])) {
      echo'<tr>';
      if ($canmodify == 1) {
	echo '<td onclick="javascript:AddToTeam('.$intDutyID.','.$intDutyType.','.$intTeamID.','.$intCount.')";>';
	echo $arrDutyTeam['teamname'];
      }
      else {
	echo '<td>';
	echo $arrDutyTeam['teamname'];
      }
      echo'</td>';
      echo'</tr>';
    }       
  }
  echo'</table>';
  echo'</td>';
  echo'<td>';
  // A able with the teams the user is in
  echo '<table width="100%" id="teamsinduty" class="teamsinduty">';

  foreach ($arrDutyTeams as $intTeamID => $arrDutyTeam) {
    if(isset($arrCurrDutyTeams[$intTeamID])) {
  
      echo'<tr>';
      if ($canmodify == 1) {
	echo '<td onclick="javascript:RemoveFromTeam('.$intDutyID.','.$intTeamID.')";>';
	echo $arrDutyTeam['teamname'];
      }
      else {
	echo '<td>';
	echo $arrDutyTeam['teamname'];
      }
      echo'</td>';
      echo'</tr>';
    }       
  }
  echo '</table>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</form>';  
}
?>


<script type="text/javascript">
$(document).ready(function(){
  if (<?php echo empty($canview) ? 0:$canview ?> == 0) {
    customAlert('You do not have view privileges for this form.');
  }
  else {
    $("table.teamsnotinduty tr:odd").addClass("odd");
    $("table.teamsnotinduty tr:even").addClass("even");
    
    $("table.teamsinduty tr:odd").addClass("odd");
    $("table.teamsinduty tr:even").addClass("even");
  }
});

function AddToTeam (DutyID, DutyTypeID, TeamID, Counter) {
  var blSave = 0;
  
  if (<?php echo empty($canmodify)? 0:$canmodify ?> == 1) {
      //check no Teams already added for Master Duty
      if (DutyTypeID == 0) {
	if (Counter >= 1) {
	  customAlert('You can only add a Master Duty to a single Team');
	}
	else {
	  blSave = 1;
	}
      }
      else {
	blSave = 1;
      }
  }
  if (blSave == 1) {
      SaveDutyTeam(DutyID, TeamID, 1);
  }

}

function RemoveFromTeam (DutyID, TeamID) {
  if (<?php echo empty($canmodify)? 0:$canmodify ?> == 1) {
    SaveDutyTeam(DutyID, TeamID, 0);
  }
}


function SaveDutyTeam (DutyID, TeamID, action) {
  if (<?php echo empty($canmodify)? 0:$canmodify ?> == 1) {
      $.ajax({ 
	url: "page-includes/master-duties/save-dutyteam.php",
	type: "POST",
	dataType: "json", 
	data: {
	  'dutyid': DutyID,
	  'teamid': TeamID,
	  'action': action
	},
	success: function(data) {
	  if (data.status == 'success') {
	    if (data.sqlstatusstring == 'success') {
		//all good
	    }
	    else {
		customAlert(data.sqlstatusstring);
	    }
	  }
	  else {
	    customAlert(data.sqlstatusstring);
	  }
	  FillMasterDutyTeamBody(DutyID, <?=$intDutyType?>);
	},
	error:function(x,e) {
	  if (x.status==0) {
	      customAlert('You are offline!!<br/>Please Check Your Network.');
	  } else if(x.status==404) {
	      customAlert('Requested URL not found.');
	  } else if(x.status==500) {
	      customAlert('Internal Server Error.');
	  } else if(e=='parsererror') {
	      customAlert('Error.<br/>Parsing JSON Request failed.');
	  } else if(e=='timeout'){
	      customAlert('Request Time out.');
	  } else {
	      customAlert('Unknown Error.<br/>'+x.responseText);
	  }
	}  
    });
  }
}

</script>