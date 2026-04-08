<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$service = new AllocationService();

$dataAllocatedJobs = $service->getDutyAllocatedJobs($request);
?>

<table class="tablesmalltidy" width="100%">
	<tr height="30px">
		<th colspan="3"><b>Allocated Jobs<b></th>
	</tr>
	<tr>
		<td colspan="3"><hr></td>
	</tr>
	<tr height="30px">
		<td style="font-weight: bold; padding: 5px;">Job Name</td>
		<td style="font-weight: bold; text-align: center; padding: 5px;">Start</td>
		<td style="font-weight: bold; text-align: center; padding: 5px;">End</td>
	</tr>
	<?php if(!empty($dataAllocatedJobs)){
		foreach($dataAllocatedJobs as $JobKey => $JobValue){
	    ?>
	<tr>
		<td style="padding: 5px;"><?php echo $JobValue['JobName'];?></td>
		<td style="text-align: center; padding: 5px;"><?php echo gmdate('H:i', strtotime($JobValue['StartTime']));?></td>
		<td style="text-align: center; padding: 5px;"><?php echo gmdate('H:i', strtotime($JobValue['EndTime']));?></td>
	</tr>
	<?php }} else {?>
	<tr>
		<td colspan="3" style="text-align: center;">There are no Jobs in this Duty</td>
	</tr>
	<?php }?>
	<tr>
		<td align="center" colspan="3"><input type="button" value="Close" onclick="cancel()"></td>
	</tr>
</table>
