<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
date_default_timezone_set('UTC');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../weekly/service/AllocationService.php';
$allocService = new AllocationService();
$pdo = OpenDBLinkA7();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$username = $_SESSION['user']['FullName'];
$allocationDutyId = $_POST['dutyid'];
$teamId = $_POST['teamId']?? 0;
$callerpagename = $_POST['pagename'] ?? 'daily';
$curdate =$strCurrentDate?? 0;
$isShiftleader = $_POST['isShiftleader'];
$arr = GetAllocationsDetailsByAllocationDutyId($allocationDutyId);
$week = $arr['WeekNumber'];

echo '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 400px; z-index: 101;" >
    <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
    <span id="ui-id-5" class="ui-dialog-title">Please Confirm</span>
    </div>
    <div style=" overflow:hidden; width: auto; min-height: 0px; max-height: none; height: 58px;" class="ui-dialog-content ui-widget-content">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
    Are you sure that you want to unassign this duty and associated jobs?</p>
    <input type="hidden" name="callerpagename" id="callerpagename" value="'.$callerpagename .'" readonly>
  </div>
  <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
    <div class="ui-dialog-buttonset">
      <input type="button" value="Unassign" onclick="confirm()">
      <input type="button" value="Cancel" onclick="cancel()">
    </div>
  </div>
</div>';
?>
<script>
function confirm() {
	 $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/edits/unassign-alloc-duty.php',
        data: {
            'dutyid':  <?php echo $allocationDutyId?>,
			'teamId': <?php echo $teamId?>,
			'action': 1,
			'isShiftleader': <?php echo $isShiftleader;?>
        },
        success: function (data,status) {
            $.facebox.close();
            if ($('#callerpagename').val() == 'production') {
              ShowAllocationsDuties(<?php echo $teamId?>, $('#strCurrentDate').val());
            } else {
              ShowDailyAllocations(<?php echo $teamId ?>, $('#strCurrentDate').val());
            }
	    },
        error:function (data) {
        }
    });
}

function cancel() {
  $.facebox.close();
}
</script>

<?php

if ($_REQUEST['action'] == 1) {
  try {
	$allocationInfor=[];
	$assigned_allocationInfor=[];
	$unassigned_allocationInfor=[];

	$allocationDutyDetails = GetAllocationsDetailsByAllocationDutyId($allocationDutyId);
  $unassigned_allocationInfor = $allocationDutyDetails;
	$query1 = "
    SELECT     chargingdutydate,
           UD_DisplayFirstName  displayfirstname,
           UD_DisplayLastName displaylastname,
           AD_DutyName dutyname,
           establishcode,
           establishcodedescription,
           isactual,
           activitycodename,
           AC.description,
           unitprice,
           chargewbscodename,
           establishcode,
           establishcodedescription,
           comments,
           contact,
           telephone,
           quantity
  FROM       chargingdutymapping_link cdml
  INNER JOIN establishcode EC ON         EC.establishcodeid = cdml.estabcodeid
  INNER JOIN activitycode AC ON         AC.activitycodeid = cdml.activitycodeid
  INNER JOIN chargewbscode CWC ON         CWC.chargewbscodeid = cdml.chargecodeid
  INNER JOIN AllocationsScheduledPersons AL ON         al.ASP_AllocationsSPID = cdml.allocationid
  INNER JOIN AllocationsDuties AD on AD.AD_AllocationsDutyID = ASP_AllocationsDutyID
  INNER JOIN UserDetails SP ON         SP.UD_UserID = cdml.personid
  WHERE      cdml.allocationid = ?";
	$stmt1 = $pdo->prepare($query1);
	$stmt1->bindValue(1, $allocationDutyDetails['AllocationsDutyID'], PDO::PARAM_INT);
	$stmt1->execute();
	$chargingResult = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    $sql = "exec usp_Edit_Allocations 'UNASSIGN', ?,@pAllocationsID=?, @Fromid=?, @pIsShiftleader=?";
	$stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $strUser, PDO::PARAM_STR);
	$stmt->bindValue(2, $allocationDutyDetails['AllocationID'], PDO::PARAM_INT);
	$stmt->bindValue(3, $allocationDutyDetails['AllocationsSPID'], PDO::PARAM_INT);
  $stmt->bindValue(4, $_REQUEST['isShiftleader'], PDO::PARAM_INT);
    $result = $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($_REQUEST['isShiftleader']==1){
    $allocationDutyDetails = GetAllocationsDetailsByAllocationDutyId($allocationDutyId);
    /* publish  allocation by shiftleadeer */
		$query = "exec [dbo].[usp_mod_PublishIndividulAllocations] ?,?,?";
		$stmt = $pdo->prepare($query);
		$stmt->bindValue(1, $allocationDutyDetails['AllocationID'], PDO::PARAM_INT);
		$stmt->bindValue(2, $allocationDutyId, PDO::PARAM_INT);
		$stmt->bindValue(3, $allocationDutyDetails['AllocationsSPID'] ?? 0, PDO::PARAM_INT);
		$stmt->execute();
	}

	  if((empty($result['errorMessage'])) && (count($chargingResult) > 0) && ($_REQUEST['isShiftleader']=="1"))
	  {
			$chargingDate = date('d/m/Y', strtotime($chargingResult[0]['ChargingDutyDate']));
			$query2 = "select Email, schedulingTeamName from schedulingTeams where schedulingTeamId = $teamId";
			$stmt2 = $pdo->prepare($query2);
			$stmt2->execute();
			$emailResult = $stmt2->fetch(PDO::FETCH_ASSOC);
			$chargingResult1 = $chargingResult[0];
			$mail = new PHPMailer\PHPMailer\PHPMailer();
			$mail->isSMTP();
			$mail->SMTPDebug = 0;
			$mail->Host = getenv('SMTP_HOST');
			$mail->Port = 25;
			$mail->setFrom('noreply@bbc.co.uk', 'Allocate');
			$mailAdd = $emailResult['Email'];
			$schedulingTeamName = $emailResult['schedulingTeamName'];
			$arrMailAdd = explode(";", $mailAdd);
			for($intCount = 0; $intCount < count($arrMailAdd); $intCount++)
			{
			  $mail->addAddress($arrMailAdd[$intCount ]);
			}
			$mail->Subject = "Charging on $chargingDate for $schedulingTeamName has been deleted because a Shiftleader swapped or unassigned a duty";
			$dutyName = $unassigned_allocationInfor['DutyName'];
			require_once 'ChargingEmail.php';
			$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
            $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
            $htmlStr = '<style>'.file_get_contents(getenv('EMAIL_CSS')).'</style>
            <body><img alt="Banner" src="cid:pobanner" /><br><br>'.$html;
			$mail->msgHTML($htmlStr);
			$mail->send();
	}
  if($_REQUEST['isShiftleader']=="1") {
    require_once 'emailNotificationOnAssign.php';
  }
    return json_encode($result);
  } catch(Exception $e) {
    logger()->critical('DB error', (array) $e);
  }
}
?>

