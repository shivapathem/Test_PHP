<?php
/* start for default value inShirt cut keys*/
include_once 'page-includes/users/process/classUserSetup.php';
include_once 'common/classCommonDBFunctions.php';
include_once 'leavefunctions.php';
include_once 'genericfunctions.php';
$commonDbobj = new classCommonDBFunctions();
$setupObj = new classUserSetup();
$arrUserSettings = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true); //Allocate 7 Menu
$bbcweeknumberArray =  $commonDbobj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
$currentYear   = date('Y');
$intWeekNumber = date('W');
if ($intWeekNumber < 9) {
  $intWeekNumber = '0'.$intWeekNumber;   
}
$yearweekno = $currentYear.$intWeekNumber;
$intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? $yearweekno;
$weeknum = $_SESSION['allocations']['WeekNumber'] ?? $intWeekNumber;
$loggedDate = $_SESSION['allocattionsdate'] ?? date('Y-m-d');
$netloginID = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : ($_COOKIE['editWeeklyUserNetLogin'] ?? '');
$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : ($_COOKIE['editWeeklyUserId'] ?? '');
$DefaultTeam =$arrUserSettings['DefaultTeam'] ?? 0;
$intScheduledPersonID = getScheduledPersonIDByNetLoginID($netloginID);
$intLeaveYear = GetCurrentLeaveYearGeneric();
$arrUser = GetScheduledPersonTeamDetails($intScheduledPersonID);
if(!empty($arrUser['homeTeamId'])) {
  $schedulingTeamId = $arrUser['homeTeamId'];
}
else {
  $schedulingTeamId = key($arrUser);
}
/* end for default value inShirt cut keys*/
?>
<script type="text/javascript" src="../js/mousetrap.js"></script>
<script type="text/javascript">
Mousetrap.bind("alt+w", function() { ShowAllocations('<?php echo $DefaultTeam; ?>','<?php echo $weeknum; ?>','','',1) });
Mousetrap.bind("alt+g", function() { ShowDailyAllocations('<?php echo $DefaultTeam; ?>', '<?php echo $loggedDate; ?>', 'home',1) });
<?php if (isset($arrUserSettings['DefaultTeam']) && ($arrUserSettings['DefaultTeam'] != 0)) { 
  if (($arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['Scheduler'] == 1) || ($arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['SchedulingTeamAdmin'] == 1) || ($arrUserSettings['Teams'][$arrUserSettings['DefaultTeam']]['TeamLeader'] == 1)){ ?>
      Mousetrap.bind("alt+u", function() { EditAllocations('<?php echo $DefaultTeam; ?>') });
 <?php }
} ?>
Mousetrap.bind("alt+q", function() { EditDailyAllocations('<?php echo $DefaultTeam; ?>') });
Mousetrap.bind("alt+r", function() { ShowRota('<?php echo $loggedDate; ?>', '<?php echo $intScheduledPersonID; ?>', '<?php echo $schedulingTeamId; ?>') });
Mousetrap.bind("alt+l", function() { ShowLeave('<?php echo $intLeaveYear; ?>', '<?php echo $netloginID; ?>') });
Mousetrap.bind("esc", function() { $.facebox.close(); }); 
</script>