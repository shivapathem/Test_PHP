
<?php
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';
include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';
$commonObj = new classCommonDBFunctions();
$PHLCredits=[];
$PHLDebits=[];
$PHLExpiring=[];
$intWeekNumber = 1;
$teamID = $_REQUEST['teamId'];
if (isset($_REQUEST['selectedweek'])) {
   $intWeekNumber = $_REQUEST['selectedweek'];
}
$PHLStartDate = getPHLStartDate();
$getExpiringPHL = getAllExpirdPHL($PHLStartDate,$intWeekNumber,$teamID);//Step 3
if (!empty($getExpiringPHL)) {
    foreach ($getExpiringPHL as $index => $ExpiringPHL) {
        $scheduledPerson = $ExpiringPHL['SchedulingPersonID'];
		$ExpiringPHL['SchedulingTeamid'] = $ExpiringPHL['SchedulingTeamid'] ?? '';
        $creditdateData = explode(" ",$ExpiringPHL['PHLDATE']);
        $creditdate = $creditdateData[0];
        $expireddateData = explode(" ",$ExpiringPHL['ExpDate']);
        $PHLData[$scheduledPerson][$creditdate]['Balance'] = $ExpiringPHL['diff']; 
        $PHLData[$scheduledPerson][$creditdate]['SchedulingPersonID']=$scheduledPerson;        
        $PHLData[$scheduledPerson][$creditdate]['CreditDate'] = $creditdate;
        $PHLData[$scheduledPerson][$creditdate]['PHLAmount'] = $ExpiringPHL['rolling_sum']; 
        $PHLData[$scheduledPerson][$creditdate]['PHLUsed'] = $ExpiringPHL['rolling_d_sum'];
        $PHLData[$scheduledPerson][$creditdate]['expiryDate'] = $expireddateData[0];
        $PHLData[$scheduledPerson][$creditdate]['StaffNumber'] = $ExpiringPHL['StaffNumber'];
        $PHLData[$scheduledPerson][$creditdate]['Name'] =  $ExpiringPHL['userDisplayName'];
        $PHLData[$scheduledPerson][$creditdate]['iYear'] = $ExpiringPHL['iYear'];
        $PHLData[$scheduledPerson][$creditdate]['comment']= $ExpiringPHL['Comments'];
        $PHLData[$scheduledPerson][$creditdate]['TimeDemensionID']= $ExpiringPHL['TimeDemensionID'];
        $PHLData[$scheduledPerson][$creditdate]['SchedulingTeamid']= $ExpiringPHL['SchedulingTeamid']; 
        $PHLData[$scheduledPerson][$creditdate]['ID']= $ExpiringPHL['ID'];   
    }
}
$row=0;
if (!empty($PHLData)) {
    foreach ($PHLData as $key => $PHLExpireRow) {
      foreach ($PHLExpireRow as $innerKey=> $innerRow) {
            $datediff = strtotime($innerRow['expiryDate']) - strtotime(date('Y-m-d'));
            if(isset($innerRow['Balance']) && $innerRow['Balance'] < 0 && $datediff > 0 ) {
                $row++;         
    ?>
<tr id="tr<?php echo $key;?>">
    <td><?php echo $innerRow['Name']; ?></td>
    <td><?php echo $innerRow['StaffNumber']; ?></td>
    <td><?php echo $innerRow['iYear']; ?></td>
    <td><?php echo date('d/M/Y',strtotime($innerRow['CreditDate'])); ?></td>
    <td><?php echo round($innerRow['PHLAmount'],2); ?></td>
    <td><?php echo date('d/M/Y',strtotime($innerRow['expiryDate'])); ?></td>
    <td><?php echo round($innerRow['Balance'],2); ?></td>
    <td><?php echo $innerRow['comment']; ?></td>
 
</tr>
<?php 
            }
        }
    } 
} 
if($row == 0) {
?>
<tr style="text-align:center"><td colspan="8">No Record Found</td></tr>
<?php
}
?>
<script>
        $('#PHLExpiring tbody tr').on('click','td',function () {
            $('tr').removeClass('highlightOrange');
            $(this).parent().addClass('highlightOrange'); 
		});	
</script>