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
$under11 = 39600;

$OverrideUnderElevenHrs = $allocd['OverrideUnderElevenHrs'] ?? 0;
$calculatedUnderElevenHrs = $allocd['CalculatedUnderElevenHrs'] ?? 0;

if($calculatedUnderElevenHrs != '' || !is_null($calculatedUnderElevenHrs)) {
    if($calculatedUnderElevenHrs < $under11) {
        $turnAroundHrs = $under11 - $calculatedUnderElevenHrs;
    } else {
        $turnAroundHrs = $calculatedUnderElevenHrs;
    }
} else {
    $turnAroundHrs = 0;
    $calculatedUnderElevenHrs = 0;
}

$UnderElevenComment = $allocd['UnderElevenComment'] ?? '';

$calDuration = $turnAroundHrs == 0 || $turnAroundHrs == '' ? number_format(0, 2, '.', '') : number_format(($turnAroundHrs / 3600), 2, '.', '');

$calculatedUnderElevenHrs = number_format((float)($calculatedUnderElevenHrs / 3600), 2, '.', '');

$calexplode = explode('.', $calculatedUnderElevenHrs);
if($calexplode[1] == '75') {
    $calexplode[1] = '00';
    $calexplode[0] += 1;
}
else if($calexplode[1] == '25') {
    $calexplode[1] = '50';
}
$calculatedUnderElevenHrs = $calexplode[0].'.'.$calexplode[1];
$sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
?>

<div class="shiftPopupBox">
    <div class="popupHeading">Override Under11 Turnaround </div>
    <form id="saveoverride11_form" method="post">
        <div class="popupContent">
            <div class="fields">
                <label for="turnDuration">Turnaround Duration (inc Breaks)</label>
                <input type="text" id="turnDuration" name="turnDuration" value="<?php echo $calDuration; ?>" readonly />
            </div>
            <div class="fields">
                <label for="underBreakHours">Rounded Under 11 Break Hours</label>
                <input type="text" id="underBreakHours" name="underBreakHours" value="<?php echo $calculatedUnderElevenHrs; ?>" readonly />
            </div>
            <div class="fields">
                <label for="actualUnder11hrs">Adjusted Under 11 Break Hours</label>
                <input type="text" id="actualUnder11hrs" name="actualUnder11hrs" value="<?php echo number_format((float)($OverrideUnderElevenHrs/3600), 2, '.',''); ?>" />
            </div>
            <div class="fields">
                <label for="under11Comments">Comments</label>
                <textarea name="under11Comments" id="under11Comments" class="textAreaBox" cols="25" rows="3" maxlength="500" placeholder="Please enter Comments"><?php echo $UnderElevenComment; ?></textarea>
                <input type="hidden" id="allocationId" name="allocationId" value="<?php echo $request->get('allocationId');?>" />
                <input type="hidden" id="allocationsDutyId" name="allocationsDutyId" value="<?php echo $request->get('allocationsDutyId');?>" />
                <input type="hidden" id="allocationsSpId" name="allocationsSpId" value="<?php echo $request->get('allocationsSpId');?>" />
                <input type="hidden" id="dutyDate" name="dutyDate" value="<?php echo $request->get('dutyDate');?>" />
                <input type="hidden" id="IsUnderElevenBreakOverride" name="IsUnderElevenBreakOverride" value="1" />
                <input type="hidden" id="RemoveOverride" name="RemoveOverride" value="0" />
                <input type="hidden" id="netLoginId" name="netLoginId" value="<?php echo $sessUserNetLogin;?>" />
            </div>
            <div class= "popupButton"><div id="under11Error"></div>
                <button type="submit" class="charging-btn" id="js_saveoverride">Submit</button>
                <button type="submit" class="charging-btn" id="js_removeoverride">Remove</button>
                <button type="button" class="charging-btn" id="js_canceloverride">Cancel</button>
            </div>
        </div>
    </form>
    </div>
</div>