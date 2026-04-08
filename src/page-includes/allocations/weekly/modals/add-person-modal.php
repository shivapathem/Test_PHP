<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$schedulingPeople = $service->getSchedulingPeople($request, 0);
?>

<p class="validateTips">Select person to add.</p>
<form id="selectPerson">
	<select name="schedulingPersonId" onchange="enableDisableAddBtn(this.value);">
		<option value="">Select Additional Person</option>
	    <?php foreach($schedulingPeople as $person): ?>
	        <option value="<?php echo $person['ScheduledPersonID']; ?>"><?php echo $person['FullName']; ?></option>
	    <?php endforeach; ?>
	</select>
	<input type="hidden" name="weekNumber" value="<?php echo $request->get('getWeekNumber'); ?>">
	<input type="hidden" name="teamId" value="<?php echo $request->get('teamId'); ?>">
	<input type="hidden" name="dataId" value="<?php echo $request->get('dataId'); ?>">
	<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
</form>
<script type="text/javascript">
	function enableDisableAddBtn(schPersonId){
		if(schPersonId != ''){
			$("#button-add-person").removeAttr('disabled').removeClass('ui-button-disabled ui-state-disabled');
		} else {
			$("#button-add-person").prop('disabled','disabled').addClass('ui-button-disabled ui-state-disabled');
		}
	}
</script>
