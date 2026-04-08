<?php

use Carbon\Carbon;

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

include_once '../../../function-includes/init.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';

$intJobID = $_POST['id'];
$isEdited = $_POST['isEdited'] ?? 0;
$intDutyID = $_POST['dutyid'] ?? 0;
$pdo = OpenDBLinkA7();
if (!isset($_POST['submit'])) {
  echo '<div id="page">';
  $query = "SELECT AJ_JobName as JobName, AJ_JobStartTimeSec as StartTime, AJ_JobEndTimeSec as EndTime FROM AllocationsJobs WHERE AJ_AllocateJobID = :jobId";
  $stmt = $pdo->prepare($query);
  $stmt->bindValue(':jobId', $intJobID, PDO::PARAM_INT);
  $stmt->execute();
  $row =  $stmt->fetch(PDO::FETCH_ASSOC);
  $editjobname = $row['JobName'];
  $start = secondsToTime($row['StartTime']);
  $end =  secondsToTime($row['EndTime']);
  if ($row['StartTime'] > $row['EndTime']) {
    $diff = (($row['EndTime'] + 86400) - $row['StartTime']);
  } else {
    $diff = $row['EndTime'] - $row['StartTime'];
  }
  date_default_timezone_set('Europe/London');
  if ($row['StartTime'] >= 1) {
    $aftermidninght = 1;
  } else {
    $aftermidninght = 0;
  }
  $splitstart = date("H:i", strtotime('1970-01-01 ' . $start) + 900);
  $splitend = date("H:i", strtotime('1970-01-01 ' . $end) - 900);
  date_default_timezone_set('Europe/London');

  echo '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 525px; z-index: 101;">';
  echo '<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">';
  echo '<span id="ui-id-5" class="ui-dialog-title">Split Job</span>';
  echo '</div>';
  echo '<div style="width: auto; min-height: 0px; max-height: none;" class="ui-dialog-content ui-widget-content">';
  echo '<div id="errorBox" class="lightcell">';
  echo '</div>';
  echo '<form id="splitjob">';
  echo '<table class="redtable" width="100%">';

  echo '<tr>';
  echo '<th colspan="2">';
  echo '<br>You can split the existing job.<br>It will appear as 2 jobs against the duty which you can then reassign<br><br>';
  echo 'The current times for this job are ' . $start . '-' . $end . '<br><br>';
  echo '</th>';
  echo '</tr>';
  echo '<tr>';
  echo '<tr>';
  echo '<td>Time</td>';
  echo '<td>';
  echo '<input id="SplitTime" onblur="jobsTimeSanitization(this.value,' . "'SplitTime'" . ');" name="SplitTime" type="text" class="time" size="20" value="' . $splitstart . '"/>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td></td>';
  echo '<td><input name="submit" type="submit" value="Split"></input>&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="' . $intJobID . '">';
  echo '<input type="hidden" name="aftermidninght" value="' . $aftermidninght . '">';
  echo '</form>';
  echo '</div>';
?>
  <script type="text/javascript">
    $(function() {
      let timepickerflag = <?php echo $diff ?>;
      if (timepickerflag > 900) {
        $('#SplitTime').timepicker({
          'minTime': '<?php echo $splitstart ?>',
          'maxTime': '<?php echo $splitend ?>',
          'step': 15,
          'timeFormat': 'H:i'
        });
      }
    });

    function jobsTimeSanitization(timeVal, elementId) {
      let elementResorce = document.getElementById(elementId);
      if (timeVal == '0') {
        $('#errorBox').html("");
        setTimeout(function() {
          elementResorce.value = '00:00';
          elementResorce.blur();
          $('#errorBox').html("");
        }, 500);
        return true;
      }
    }

    $('document').ready(function() {
      $('#splitjob').validate({
        errorLabelContainer: "#errorBox",
        rules: {
          "SplitTime": {
            required: true,
          }
        },
        messages: {
          SplitTime: "Please Enter the time you would like to split this Job"
        },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/splitjob.php',
            data: $('#splitjob').serialize(),
            success: function(data) {
              $('#page').html(data);
              $('.content').css("display", 'none');
              $('.close').css("display", 'none');
            }
          });
        }
      })

    });

    function cancel() {
      $.facebox.close();
    }
  </script>
  <?php
} else {
  $time = time();
  $strSplitTime = $_REQUEST['SplitTime'];
  $splittime = secondsFromTime($strSplitTime);
  date_default_timezone_set('Europe/London');
  $intEditedJobID = $intJobID;
  $intNewJobID = time();
  $jobhistory = 'Job times split at ' . $strSplitTime . ' by ' . $_SESSION['user']['FullName'] . ' on ' . date("d/m/Y") . ' at ' . date("H:i") . '<hr>';
  $jobhistory = escapeSingleQuotes($jobhistory);

  $strQuery = "SELECT AJ_JobName as JobName,
  AL.AL_SchedulingTeamID as schedulingTeamId,
  AllocationsJobs.AJ_JobStartTimeSec as jobstarttime,
  AllocationsJobs.AJ_JobEndTimeSec as jobendtime,
  AllocationsJobs.AJ_JobStartTimeLocal as jobstarttimelocal,
  AllocationsJobs.AJ_JobEndTimeLocal as jobendtimelocal
  FROM  AllocationsJobs (NOLOCK)
  INNER JOIN AllocationsDuties AD (NOLOCK) ON AD.AD_AllocationsDutyID = AllocationsJobs.AJ_AllocationsDutyID
  INNER JOIN Allocations AL (NOLOCK) ON AL.AL_AllocationsID = AD.AD_AllocationsID
  WHERE AllocationsJobs.AJ_AllocateJobID = :editedJobID";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindValue(':editedJobID', $intEditedJobID, PDO::PARAM_INT);
  $stmt->execute();
  $row =  $stmt->fetch(PDO::FETCH_ASSOC);

  $strJobName = $row['JobName'];
  $intSchedulingTeamID = $row['schedulingTeamId'];
  $jobstarttime = $row['jobstarttime'];
  $jobendtime = $row['jobendtime'];
  
  $newSplittedTime = Carbon::parse($row['jobstarttimelocal'], 'Europe/London')->startOfDay();
  $newSplittedTime = $newSplittedTime->addSeconds($splittime);
  $newSplittedTimeUTC = $newSplittedTime->copy()->setTimezone('UTC');

  $newSplittedTime_copy = Carbon::parse($row['jobstarttimelocal'], 'Europe/London')->startOfDay();
  $newSplittedTime_copy = $newSplittedTime_copy->addSeconds($splittime);
  $newSplittedTimeUTC_copy = $newSplittedTime->copy()->setTimezone('UTC');

  $dutyhistory = 'Job (' . $strJobName . ') was split at ' . $strSplitTime . ' by ' . $_SESSION['user']['FullName'] . ' on ' . date("d/m/Y") . ' at ' . date("H:i") . '<hr>';
  $dutyhistory = escapeSingleQuotes($dutyhistory);

  $strQuery = "INSERT INTO AllocationsJobs
  (AJ_AllocationsDutyID,AJ_MasterJobID,AJ_ProgrammeID,AJ_Contact,AJ_Location,AJ_JobName,AJ_JobStartTimeSec,AJ_JobEndTimeSec,AJ_JobStartTimeUTC,AJ_JobEndTimeUTC,AJ_JobStartTimeLocal,AJ_JobEndTimeLocal,AJ_JobBGColour,AJ_JobFontColour,AJ_Comments,AJ_JobStatus,AJ_JobInfo,AJ_CreatedBy,AJ_CreatedDate,AJ_UpdatedBy,AJ_UpdatedDate,AJ_IsEditedJobAttention)
  SELECT AJ_AllocationsDutyID,AJ_MasterJobID,AJ_ProgrammeID,AJ_Contact,AJ_Location,AJ_JobName,AJ_JobStartTimeSec,AJ_JobEndTimeSec,AJ_JobStartTimeUTC,AJ_JobEndTimeUTC,AJ_JobStartTimeLocal,AJ_JobEndTimeLocal,AJ_JobBGColour,AJ_JobFontColour,AJ_Comments,AJ_JobStatus,AJ_JobInfo,AJ_CreatedBy,AJ_CreatedDate,AJ_UpdatedBy,AJ_UpdatedDate,AJ_IsEditedJobAttention
  FROM         AllocationsJobs
  WHERE        (AJ_AllocateJobID = :editedJobID)";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindValue(':editedJobID', $intEditedJobID, PDO::PARAM_INT);
  $stmt->execute();

  $strQueryLastId = "SELECT TOP 1 AJ_AllocateJobID AS ID FROM AllocationsJobs (NOLOCK) ORDER BY ID DESC";
  $stmtLastId = $pdo->prepare($strQueryLastId);
  $stmtLastId->execute();
  $resLastId = $stmtLastId->fetch(PDO::FETCH_ASSOC);

  $intNewID = $resLastId['ID'] ?? 0;
  $updateEndTime = 0;
  $strQuery = "UPDATE AllocationsJobs SET AJ_JobEndTimeSec = :splittime, AJ_JobEndTimeLocal = :localDateTime, AJ_JobEndTimeUTC = :utcDateTime WHERE AJ_AllocateJobID = :intEditedJobID";
  $stmt = $pdo->prepare($strQuery);
  $endsplittime = $splittime;

  if ($splittime == 0) {
    $endsplittime = 0;
  }
  $nextDaySplittedTime_end = $newSplittedTime->format('Y-m-d H:i:s');
  $nextDaySplittedTimeUTC_end = $newSplittedTimeUTC->format('Y-m-d H:i:s');
  if (($jobstarttime > $splittime) || ($splittime == 0)) {
    
    // Add 1 day to both $newSplittedTime and $newSplittedTimeUTC
    $newSplittedTime->modify('+1 day');
    $newSplittedTimeUTC->modify('+1 day');

    // Format the modified date/time
    $nextDaySplittedTime_end = $newSplittedTime->format('Y-m-d H:i:s');
    $nextDaySplittedTimeUTC_end = $newSplittedTimeUTC->format('Y-m-d H:i:s');
  }
  $stmt->bindValue(':splittime', $endsplittime, PDO::PARAM_INT);
  $stmt->bindValue(':intEditedJobID', $intEditedJobID, PDO::PARAM_INT);
  $stmt->bindValue(':localDateTime', $nextDaySplittedTime_end, PDO::PARAM_STR);
  $stmt->bindValue(':utcDateTime', $nextDaySplittedTimeUTC_end, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $updateEndTime = 1;
  }

  $nextDaySplittedTime_start = $newSplittedTime_copy->format('Y-m-d H:i:s');
  $nextDaySplittedTimeUTC_start = $newSplittedTimeUTC_copy->format('Y-m-d H:i:s');
  if ($splittime == 0 || $jobstarttime > $splittime) {

    $newSplittedTime_copy->modify('+1 day');
    $newSplittedTimeUTC_copy->modify('+1 day');

    $nextDaySplittedTime_start = $newSplittedTime_copy->format('Y-m-d H:i:s');
    $nextDaySplittedTimeUTC_start = $newSplittedTimeUTC_copy->format('Y-m-d H:i:s');
  }
  $updateStartTime = 0;
  $strQuery = "UPDATE AllocationsJobs SET AJ_JobStartTimeSec = :splittime, AJ_JobStartTimeLocal = :localDateTime, AJ_JobStartTimeUTC = :utcDateTime WHERE AJ_AllocateJobID = :intNewID";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindValue(':splittime', $splittime, PDO::PARAM_INT);
  $stmt->bindValue(':intNewID', $intNewID, PDO::PARAM_INT);
  $stmt->bindValue(':localDateTime', $nextDaySplittedTime_start, PDO::PARAM_STR);
  $stmt->bindValue(':utcDateTime', $nextDaySplittedTimeUTC_start, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $updateStartTime = 1;
  }

  if ($updateStartTime == 1 && $updateEndTime == 1) {
  ?>
    <script type="text/javascript">
      let dataPost = {
        'attributeId': <?php echo $intEditedJobID; ?>,
        'historyType': 9,
        'userId': <?php echo isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];; ?>,
        'message': '<?php echo $jobhistory; ?>'
      };
      $.post("/page-includes/allocations/edits/allocation-history.php", dataPost).success(function(res) {
        res = $.parseJSON(res);
        if (res.intstatus) {
          let dataPostSecond = {
            'attributeId': <?php echo $intNewID; ?>,
            'historyType': 9,
            'userId': <?php echo isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];; ?>,
            'message': '<?php echo $jobhistory; ?>'
          };
          $.post("/page-includes/allocations/edits/allocation-history.php", dataPostSecond).success(function(res) {});
        }
      });
    </script>
  <?php
  }
  ?>
  <script type="text/javascript">
    $(function() {
      $('.content').css("display", 'table');
      $.facebox.close();
      ShowDailyAllocations(<?php echo $intSchedulingTeamID ?>, $('#strCurrentDate').val());
    })
  </script>
<?php
}