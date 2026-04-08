<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

include_once '../../../function-includes/init.php';
include_once '../../../function-includes/shiftleaderfunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
$intID = $_POST['id'];
$intLeaderTypeID = $_POST['typeid'];
$strDate = $_POST['currentDate'] ?? $_SESSION['allocattionsdate'] ?? '';
$rolepermission = isset($_POST['roleIDPermission'])? $_POST['roleIDPermission']:'';
$filterQuery1 = isset($_POST['filterQuery1'])? $_POST['filterQuery1']:'';
$filterQuery2 = isset($_POST['filterQuery2'])? $_POST['filterQuery2']:'';
$filterQuery3 = isset($_POST['filterQuery3'])? $_POST['filterQuery3']:'';
$filterOrderStr = isset($_POST['filterOrderStr'])?$_POST['filterOrderStr']:'';
$skillFilterDaily ='';
if (isset($_POST['skillFilterDaily']) && $_POST['skillFilterDaily'] != '') {
  $skillFilterDaily = $_POST['skillFilterDaily'];
}

$dutyFilterDaily ='';
if (isset($_POST['dutyFilterDaily']) && $_POST['dutyFilterDaily'] != '') {
  $dutyFilterDaily = $_POST['dutyFilterDaily'];
}

$jobFilterDaily ='';
if (isset($_POST['jobFilterDaily']) && $_POST['jobFilterDaily'] != '') {
  $jobFilterDaily = $_POST['jobFilterDaily'];
}

$jobNameAll ='';
if (isset($_POST['jobNameAll']) && $_POST['jobNameAll'] != '') {
  $jobNameAll = $_POST['jobNameAll'];
}

$jobLabelAll ='';
if (isset($_POST['jobLabelAll']) && $_POST['jobLabelAll'] != '') {
  $jobLabelAll = $_POST['jobLabelAll'];
}


$selectedDay= isset($_POST['selectedDay'])? $_POST['selectedDay']:0;
$strCurrentDate = date("Y-m-d", strtotime($_POST['currentDate'] ?? $_SESSION['allocattionsdate']));
$getWeekandDayArr = GetAllocationWeekandDay($strCurrentDate);
$intWeek = $getWeekandDayArr['ixYearWeek'];
$intDay = intval($getWeekandDayArr['ixDayInWeek']);
//Get the shiftleadetr options
$arrLeaderType = json_decode(GetLeaderTypeInfoByID($intLeaderTypeID), true);
$schedulingTeamId = $arrLeaderType['schedulingTeamId'];
$pdo = OpenDBLinkA7();
if (isset($_POST['ScheduledPersonID'])) {
  // Form being submitted
  $strScheduledPersonID = $_POST['ScheduledPersonID'];
  $arrperson = getusernameandsortcode($strScheduledPersonID, $schedulingTeamId);
  $starttime = $_POST['StartTime'];
  $endtime = $_POST['EndTime'];
  $telephone = $_POST['telephone'];
  if (isset($_POST['aftermidnight'])) {
    $aftermidnight = 1;
  } else {
    $aftermidnight = 0;
  }

  $dblstarttime = timetoseconds($starttime);
  $dblendtime = timetoseconds($endtime);
  
if(($dblstarttime < $dblendtime) && ($aftermidnight == 1)){
    $dblendtime = $dblendtime + 86400;
    $dblstarttime = $dblstarttime + 86400;
  }

  if($dblstarttime > $dblendtime){
    $dblendtime = $dblendtime + 86400;
  }

  if ($intID == 0) {
    $history = "New Shiftleader created by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i")."<br>";
    $history.= "The person was  ".$arrperson[0]." and the times were ".$starttime."-".$endtime."<hr>";
    $history = escapeSingleQuotes($history);
  } else {
	$history = "<hr>Shiftleader edited by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i")."<br>";
    $history.= "The person was  ".$arrperson[0]." and the times were ".$starttime."-".$endtime."<hr>";
    $history = escapeSingleQuotes($history);
  }  
	try {
		$pdo = OpenDBLinkA7();
		$sql = "exec usp_mod_shiftleaderDuty ?,?,?,?,?,?,?,?,?,?,?,?";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(1, $intID, PDO::PARAM_INT);
		$stmt->bindParam(2, $dblstarttime, PDO::PARAM_INT);
		$stmt->bindParam(3, $dblendtime, PDO::PARAM_INT);
		$stmt->bindParam(4, $intLeaderTypeID, PDO::PARAM_INT);
		$stmt->bindParam(5, $intWeek, PDO::PARAM_INT);
		$stmt->bindParam(6, $intDay, PDO::PARAM_INT);
		$stmt->bindParam(7, $telephone, PDO::PARAM_STR);
		$stmt->bindParam(8, $history, PDO::PARAM_STR);
		$stmt->bindParam(9, $strScheduledPersonID, PDO::PARAM_INT);
		$stmt->bindParam(10, $aftermidnight, PDO::PARAM_INT);
		$stmt->bindParam(11, $status, PDO::PARAM_INT);
		$stmt->bindParam(12, $strstatus, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		logger()->critical('DB Error', (array) $e);
	}
?>

      <script type="text/javascript">
      $(function(){
        $.facebox.close();
        ShowDailyAllocations ('<?php echo $schedulingTeamId ?>', '<?php echo $strDate ?>');
      })
      </script>
      <?php
}
else {
  // Get the allocations....
$arrAllocations=[];
  if($rolepermission!='') {
  if ($selectedDay > 1) {
    $daysadded = $selectedDay-1;
    $day7date  = date("Y-m-d", strtotime("+$daysadded day", (strtotime($strCurrentDate))));
  } else {
    $day7date = date("Y-m-d",strtotime($strCurrentDate));
  }
  $arrAllocations = ReadAllocationsDay($intWeek, $intDay,$schedulingTeamId,0, $rolepermission,$filterQuery1,$filterQuery2,$filterQuery3,$filterOrderStr,$strCurrentDate,$day7date,$skillFilterDaily,$dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll);
  $arrAllocations = json_decode($arrAllocations,true);
}
 
	$teamUserRole = GetUserTeamRoleLink($schedulingTeamId);
  $sql="Select Top (1) RoleID from [REF_Roles] where RoleName='Shift Leader' AND isActive=1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $res = $stmt->fetch(PDO::FETCH_ASSOC);
  $shiftleaderRoleID = $res['RoleID'];
	$spRoleMap = array();
	foreach($teamUserRole as $value) {
    if ($value['RoleID']==$shiftleaderRoleID) {
		  $spRoleMap[$value['ScheduledPersonID']]= $value['RoleID'];
    }
  }
  
  if ($intID != 0) {
    $query = "SELECT id, starttime, endtime, telephone,ScheduledPersonID,aftermidnight 
              FROM  shiftleaders (NOLOCK)
              WHERE (id = $intID)";
     $stmt = $pdo->prepare($query);
     $stmt->execute();
     $row = $stmt->fetch(PDO::FETCH_ASSOC);
     $strScheduledPersonID = $row['ScheduledPersonID'];
     $telephone = $row['telephone'];
     if ($row['aftermidnight'] == 1) {
       $aftermidnight = 1;
     } else {
       $aftermidnight = 0;
    }

     $starttime = gmdate("H:i", $row['starttime']);
     $endtime = gmdate("H:i", $row['endtime']);
     $intID = $row['id'];
  }
  else {
    $query = "SELECT telephone
              FROM  shiftleadertypes
              WHERE (id = $intLeaderTypeID)";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $telephone = $row['telephone'];
    $strScheduledPersonID = '';
    $starttime = '00:00';
    $endtime = '00:00';
    $intID = 0;
    $aftermidnight = 0;

  }

  echo '<div id="page">';
  echo '<form id="leaderform">';
  echo '<table class="redtable" width="700px">';
  echo '<tr>';
  echo '<th colspan="4" class="tableheadersmall bigtextboldcentre "><br>';
  if ($intID == 0) {
    echo 'You are creating a Shift Leader Duty for ';
  }
  else {
    echo 'You are editing a Shift Leader Duty for ';
  }
  echo '\''.$arrLeaderType['description'].'\'';
  echo '<br><br></th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td width="150px">Available staff</td>';
  echo '<td class="lightcell" colspan="3">';
  echo '<select class="chosen-select" name="ScheduledPersonID" id="ScheduledPersonID">';
  if (isset($arrAllocations['assigned'])) {
	foreach ($arrAllocations['assigned']  as $valAllocations){
		foreach ($valAllocations as $ScheduledPersonID => $arrAllocation) {
			if (isset($spRoleMap[$arrAllocation['ScheduledPersonID']]) && ($spRoleMap[$arrAllocation['ScheduledPersonID']]==10) && ($arrAllocation['duty']<>'U') && ($arrAllocation['duty']<>'Leave') &&( $arrAllocation['duty']<>'OFF Leave')) {
			  if (isset($arrAllocation['ScheduledPersonID']) && ($strScheduledPersonID == $arrAllocation['ScheduledPersonID'])) {
				echo '<option selected value="'.$arrAllocation['ScheduledPersonID'].'">'.$arrAllocation['fullname'].' ('.$arrAllocation['duty'].') </option>';
			  } else {
				echo '<option value="'.$arrAllocation['ScheduledPersonID'].'">'.$arrAllocation['fullname'].' ('.$arrAllocation['duty'].')</option>';
			  }
			}
		}
	}
  }	
  echo '</select>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>Telephone</td>';
  echo '<td class="lightcell" colspan="3">';
  echo '<input id="telephone" name="telephone" type="text" size="25" value="'.$telephone.'"/>';
  echo '</td>';
  echo '</tr>';


  echo '<tr>';
  echo '<td width="75px">Start</td>';
  echo '<td>';
  echo '<input id="StartTime" name="StartTime" type="text"  size="10" value="'.$starttime.'"/>';
  echo '</td>';
  echo '<td width="75px">End</td>';
  echo '<td>';
  echo '<input id="EndTime" name="EndTime" type="text"  size="10"  value="'.$endtime.'"/>';
   echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td colspan="3">Starts after midnight</td>';
  echo '<td>';
  echo '<input type="checkbox" name="aftermidnight" value="ON"';
  if ($aftermidnight == 1) {
    echo ' checked';
  }
  echo '>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '</td>';
  echo '<td align="center" class="lightcell smalltext" colspan="4"><input type="submit" value="Assign" name="update">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="typeid" value="'.$intLeaderTypeID.'">';
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo  '<input type="hidden" name="currentDate" value="' . $strDate . '">';
  echo '</form> ';
  echo '</div> ';
?>

<script type="text/javascript">


$(document).ready(function() {
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "450px"
  });

    $('#leaderform').validate({
      
      rules:{
        "StartTime":{
          required:true,
        },
        "EndTime":{
          required:true,
        }
      },
        messages:{
          "StartTime":{
            required:"<img id='exclamation' src='images/messagebox_warning.png' width='16' height='16' title='Please enter the start time.' />"
          },
          "EndTime":{
            required:"<img id='exclamation' src='images/messagebox_warning.png' width='16' height='16' title='Please enter the end time.' />"
          },
        },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/allocations/edits/shiftleader-new-edit.php', data:$('#leaderform').serialize(), success: function(data) {
            $('#page').html(data);
          }});
        }
  })
});

$(function() {
  $('#StartTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

$(function() {
  $('#EndTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

</script>

<?php
}

?>
