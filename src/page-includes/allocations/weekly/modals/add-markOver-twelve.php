<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../../../../function-includes/genericfunctions.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
//get the details of the Allocation details
$allocd = $service->getAllocationByID($request->get('allocationsDutyId'));
$overTwelvehrs = timetohours($service->secondsIntoTime($allocd['OverTwelveHrs']));
$isOverseas = $allocd['IsOverseasOverTwelve'];
$markOverTwelve = $allocd['OverTwelveHrs'] > 0 ? 1 : 0;
$plannedDuration = $allocd['PlannedDuration'];
$duration = $allocd['Duration'];
$dutyBreak = $allocd['dutyBreakTime'];
$sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$plannedDuration = $allocd['PlannedDuration'] > 0 ? $service->secondsIntoTime($allocd['PlannedDuration']) : 0;

$over12 = 43200;  //set 12:00 hrs time to seconds

if($duration > $over12) {
    $maxHours = $allocd['Duration'] - $over12;
} else {
    $maxHours = $duration;
}
$maxHours = $service->secondsIntoTime($maxHours);
$shMaxHours = timetohours($maxHours);

$calDuration = $duration == 0 || $duration == '' ? number_format((float)0, 2, '.', '') : number_format((float)($duration / 3600), 2, '.', '');
?>
<div class="shiftPopupBox">
    <div class="popupHeading">Authorise Over 12 Hours for Shift </div>
        <div class="popupContent">
            <form id="saveauthorise_form" method="post">

                <div class="fields">
                    <label for="shiftDuration">Shift Duration (inc Breaks)</label>
                    <input type="text" id="shiftDuration" name="shiftDuration" value="<?php echo $calDuration;?>" readonly />
                </div>
                <div class="fields">
                    <label for="plannedHours">Planned Hours (inc Breaks)</label>
                    <input type="text" id="plannedHours" name="plannedHours" value="<?php echo $plannedDuration;?>" readonly />
                </div>
                <div class="fields">
                    <label for="authoriseOver12">Authorise Over12</label>
                    <select id="authoriseOver12" name="authoriseOver12">
                        <option value="0" <?php echo $markOverTwelve == 0 ? "selected": "";?>>NOT Authorised Over 12</option>
                        <option value="1" <?php echo $markOverTwelve == 1 ? "selected": "";?>>YES Authorised Over 12</option>
                    </select>
                </div>
                <div class="fields">
                    <label for="overseasDeployment">Overseas Deployment</label>
                    <select id="overseasDeployment" name="overseasDeployment">
                        <option value="1" <?php echo $isOverseas == 1 ? "selected": "";?>>YES Overseas Deployment</option>
                        <option value="0" <?php echo $isOverseas == 0 ? "selected": "";?>>NOT Overseas Deployment</option>
                    </select>
                </div>
                <div class="fields">
                    <label for="authorised12Hours">Authorised Over 12 Hours</label>
                    <input type="text" id="authorised12Hours" name="authorised12Hours" value="<?php echo $overTwelvehrs;?>" />
                    <input type="hidden" id="allocationId" name="allocationId" value="<?php echo $request->get('allocationId');?>" />
					<input type="hidden" id="allocationsDutyId" name="allocationsDutyId" value="<?php echo $request->get('allocationsDutyId');?>" />
					<input type="hidden" id="allocationsSpId" name="allocationsSpId" value="<?php echo $request->get('allocationsSpId');?>" />
					<input type="hidden" id="dutyDate" name="dutyDate" value="<?php echo $request->get('dutyDate');?>" />
                    <input type="hidden" id="netLoginId" name="netLoginId" value="<?php echo $sessUserNetLogin;?>" />
                    <input type="hidden" id="maxHours" name="maxHours" value="<?php echo $shMaxHours;?>" />
                    <span>Max <?php echo $shMaxHours;?> Hours</span>
                </div>
                <div class="popupButton"><div id="over12Error"></div>
                    <button type="submit" class="charging-btn" id="js_saveauthorise">Submit</button>
                    <button type="button" id="js_cancelauthorise" class="charging-btn">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

