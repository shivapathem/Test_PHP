<?php
/*  Mark OVERTIME Form In Popup*/

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../../../../function-includes/genericfunctions.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$service = new AllocationService();

//set the week number
$fetchWeek = GetAllocationWeekandDay($request->get('dateonly'));
$fetchWeek['ixYearWeek'] = (array_key_exists('ixYearWeek', $fetchWeek) && isset($fetchWeek['ixYearWeek'])) ? $fetchWeek['ixYearWeek'] : 0;
$request->request->set('weekNumber', $fetchWeek['ixYearWeek']);

//get the details of the Allocation details
$allocationdetails = $service->getAllocationByID($request->get('allocationsDutyId'));
$schPersonetails = $service->getSchPersonByID($request->get('allocationsDutyId'), $request->get('allocationsSpId'));
//cal. the duration, breaktime and mannual ot hrs
$duration = $allocationdetails['Duration'] == 0 || $allocationdetails['Duration'] == '' ? number_format((float)0, 2, '.', ''): number_format((float)($allocationdetails['Duration']/ 3600), 2, '.', '');  
$mannualothr = $schPersonetails['OverTimeHours'] == 0 || $schPersonetails['OverTimeHours'] == '' ? number_format((float)0, 2, '.', '') : number_format((float)($schPersonetails['OverTimeHours'] / 3600), 2, '.', ''); 
$dutyBreakTime = $allocationdetails['dutyBreakTime'] == 0 || $allocationdetails['dutyBreakTime'] == '' ? number_format((float)0, 2, '.', ''): number_format((float)($allocationdetails['dutyBreakTime'] / 3600), 2, '.', '');
$availhrscal = ($allocationdetails['Duration']-$allocationdetails['dutyBreakTime']);
$availhrs =$availhrscal ==0 || $availhrscal==''? number_format((float)0, 2, '.', ''): number_format((float)($availhrscal/ 3600), 2, '.', '');
?>

<form id="markovertimeform" method="post" >
  <table id="markovertimetable" class="smalltable bluetable" width="100%">
    <thead><tr><th colspan="5"><?php echo $mannualothr > 0 ? "Edit Mark Overtime" : "Mark Overtime" ?> </th></tr></thead>
    <tbody>
      <tr><td class="lightblue">Duty Duration (including breaks)</td><td><input id="dutyduration" readonly name="dutyduration" type="text" class="width-55" value="<?php echo $duration; ?>"></td></tr>
      <tr>
      <tr><td class="lightblue">Avail Hours (excluding breaks)</td><td><input id="availhrs" readonly name="availhrs" type="text" class="width-55" value="<?php echo $availhrs; ?>"></td></tr></tr>
      <tr>
      <td class="lightblue">Manual OT Hours (excluding breaks)</td><td><input id="mannualothrs"  name="mannualothrs" type="text" class="width-55" value="<?php echo $mannualothr; ?>" class="ui-timepicker-input" autocomplete="off" >

	  </td>
      </tr>
	 </tbody>
  </table>
  <div id="overTimeError"></div>
            <input type="hidden"  name="schedulingPersonId" value="<?php echo $request->get('schedulingPersonId');?>">
            <input type="hidden" name="dutyDate" value="<?php echo $request->get('dutyDate');?>">
            <input type="hidden" name="schedulingteamId" value="<?php echo $request->get('teamId');?>">
            <input type="hidden" name="dateonly" value="<?php echo $request->get('dateonly');?>">
		        <input type="hidden" name="allocationId" value="<?php echo $request->get('allocationId');?>">
		        <input type="hidden" name="allocationsDutyId" value="<?php echo $request->get('allocationsDutyId');?>">
		        <input type="hidden" name="allocationsSpId" value="<?php echo $request->get('allocationsSpId');?>">
            <input type="hidden" name="prevOTHrs" id="prevOTHrs" value="<?php echo $mannualothr; ?>">
            <input type="hidden" name="action" value="overtime">
            <span><input name="js_saveOvertTime" id="js_saveOvertTime" type="submit" value="Save"></span>
</form>