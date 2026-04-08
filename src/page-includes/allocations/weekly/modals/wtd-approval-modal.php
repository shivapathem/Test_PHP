<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/WTDService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$WTDservice = new WTDService();

$breachesList = $WTDservice->getBreachesList($request);
$schedulingPersonName = $WTDservice->getSchedulingPersonName($request);
$breachId = 0;

$sBreachID = 0;
$sBreachSchedulingTeamId = 0;
$sBreachSchedulingPersonID = 0;
$sBreachBreachType = 0;
$sBreachStartDate = '';
$sBreachEndDate = '';
$sBreachBreachedBy = '';
$sBreachBreachedDate = '';
$sBreachIsApproved = 0;
$sBreachApprovedBy = '';
$sBreachApprovedDate = '';
$sBreachComments = '';
$sBreachHistory = '';
$sBreachRule = '';

if(count($breachesList) == 1){
    $sBreachID = $breachesList[0]['ID'] ?? 0;
    $sBreachSchedulingTeamId = $breachesList[0]['SchedulingTeamId'] ?? 0;
    $sBreachSchedulingPersonID = $breachesList[0]['SchedulingPersonID'] ?? 0;
    $sBreachBreachType = $breachesList[0]['BreachType'] ?? 0;
    $sBreachStartDate = $breachesList[0]['StartDate'] ?? '';
    $sBreachEndDate = $breachesList[0]['EndDate'] ?? '';
    $sBreachBreachedBy = $breachesList[0]['BreachedBy'] ?? '';
    $sBreachBreachedDate = $breachesList[0]['BreachedDate'] ?? '';
    $sBreachIsApproved = $breachesList[0]['IsApproved'] ?? 0;
    $sBreachApprovedBy = $breachesList[0]['ApprovedBy'] ?? '';
    $sBreachApprovedDate = $breachesList[0]['ApprovedDate'] ?? '';
    $sBreachComments = $breachesList[0]['Comments'] ?? '';
    $sBreachHistory = $breachesList[0]['History'] ?? '';
    $sBreachRule = $breachesList[0]['Rule'] ?? '';
}
?>
<input type="hidden" id="breachListCount" value="<?php echo count($breachesList); ?>">
<div class="WorkingTime-container">
    <div class="WorkingTime-main-heading">
        <h1 class="WorkingTime-heading">Working Time Directive Approval</h1>
    </div>
    <form name="workingTimeDirective" id="workingTimeDirective" onsubmit="return false;">
		<div class="fullWidth">

		    <fieldset>
                <legend class="week-small-text"><b>WTD breaches for <?php echo $schedulingPersonName['DisplayName']?></b></legend>
                <div id="WTDBreachesContainer">
                    <table id="WTDBreaches" class="fullWidth">
                        <tr>
                            <th class="wt-labs">Type</th>
                            <th class="wt-labs">From</th>
                            <th class="wt-labs">To</th>
                            <th class="wt-labs width140">Comments</th>
                        </tr>
                        <?php if(!empty($breachesList)){?>
                            <?php foreach($breachesList as $breachData){?>
                                <tr class="breachrows <?php if($breachData['IsApproved'] == 1){?>WTDApproved<?php } else {?>WTDNotApproved<?php }?>" ondblclick="getBreachDetails(<?php echo $breachData['ID'];?>,<?php echo $request->get('teamId');?>);" id="rowNum<?php echo $breachData['ID'];?>">
                                    <td><?php echo $breachData['Rule'];?></td>
                                    <td><?php echo date('d/m/Y',strtotime((string) $breachData['StartDate']));?></td>
                                    <td><?php echo date('d/m/Y',strtotime((string) $breachData['EndDate']));?></td>
                                    <td id="brchCmts<?php echo $breachData['ID'];?>">
                                        <?php
                                        if(strlen(trim((string) $breachData['Comments'])) > 20){
                                            echo substr(trim((string) $breachData['Comments']), 0, 20).'...';
                                        } else {
                                            echo trim((string) $breachData['Comments']);
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php }?>
                        <?php } else {?>
                            <tr>
                                <td colspan="4" style="text-align:center;">No Records</td>
                            </tr>
                        <?php }?>
                    </table>
                </div>
                <div class="WorkingTime fullWidth">
                    <div class="WorkingTimeCol WorkingTimeCol7">
                        <p class="BreachNote"> Double Click on a Breach to Edit or Delete it.</p>
                    </div>
                    <div class="WorkingTimeCol WorkingTimeCol5">
                        <div class="inlineBlock">
                            <span>Approved</span>
                            <span class="colors-box WTDApproved"></span>
                        </div>
                        <div class="inlineBlock">
                            <span>Not Approved</span>
                            <span class="colors-box WTDNotApproved"></span>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class="week-small-text"><b>Details</b></legend>
                <div class="WorkingTime fullWidth">
                    <div class="WorkingTimeCol WorkingTimeCol12">
                        <label class="wt-lab">Breach Type </label>
                        <input class="wt-inp width83 disabledField" id="breachType" type="text" placeholder="Select Breach" readonly value="<?php echo $sBreachRule; ?>">
                    </div>
    				<div class="WorkingTimeCol WorkingTimeCol4">
                        <label class="wt-lab">From </label>
                        <input class="wt-inp WTDate disabledField" id="breachFrom" type="text" placeholder="dd/mm/yyyy" readonly value="<?php echo $sBreachStartDate; ?>">
                    </div>
    				<div class="WorkingTimeCol WorkingTimeCol4">
                        <label class="wt-lab alignRight">To</label>
                        <input class="wt-inp WTDate disabledField" id="breachTo" type="text" readonly placeholder="dd/mm/yyyy" value="<?php echo $sBreachEndDate; ?>">
                    </div>
                    <div class="WorkingTimeCol WorkingTimeCol8">
                        <label class="wt-lab">Breached By</label>
                        <input class="wt-inp width65 disabledField" type="text" id="breachBy" placeholder="Select Breach" readonly value="<?php echo $sBreachBreachedBy; ?>">
                    </div>
                    <div class="WorkingTimeCol WorkingTimeCol4">
                        <label class="wt-lab width87">Breached Date</label>
                        <input class="wt-inp WTDate disabledField" type="text" id="breachDate" placeholder="dd/mm/yyyy" readonly value="<?php echo $sBreachBreachedDate; ?>">
    				</div>
                </div>
            </fieldset>

            <fieldset>
                <legend class="week-small-text"><b>Approval</b></legend>
                <div class="WorkingTime fullWidth">

                    <div class="WorkingTimeCol WorkingTimeCol12">
                        <input type="checkbox" id="WTDApproval" class="checkBoxInput" onclick="approveWTDBreach(<?php echo count($breachesList); ?>,<?php echo $breachId; ?>,<?php echo $request->get('teamId')?>)">
                        <label for="WTDApproval">(Tick to Approve the WTD Breach)</label>
                     </div>
                     <div class="WorkingTimeCol WorkingTimeCol8">
                        <label class="wt-lab">Approved By</label>
                        <input class="wt-inp width65 disabledField" id="approvedBy" type="text" placeholder="Approved By" readonly>
                    </div>
                    <div class="WorkingTimeCol WorkingTimeCol4">
                        <label class="wt-lab width87">Approved Date</label>
                        <input class="wt-inp WTDate disabledField" id="approvedDate" type="text" placeholder="dd/mm/yyyy" readonly>
    				</div>
    				<div class="WorkingTimeCol WorkingTimeCol12" style="display: flex;">
    					 <label class="wt-lab">Comments</label>
    					 <textarea class="disabledField" cols="64" rows="3" id="breachComments" placeholder="Breach comments"><?php echo $sBreachComments; ?></textarea>
    				</div>
                    <div class="WorkingTimeCol WorkingTimeCol12" style="display: flex;">
                         <label class="wt-lab">&nbsp;</label>
                         <span class="wtdCountSpan">Counts :</span> <span id="remainBreachComments">500</span> <span id="errMsg" style="display:none; padding-left: 10px;"></span>
                    </div>
    			    <div class="WorkingTimeCol WorkingTimeCol12" style="display: flex;">
    					 <label class="wt-lab">History</label> 
    					 <textarea cols="64" rows="3" id="breachHistory"  placeholder="Breach history" ><?php echo htmlspecialchars(preg_replace('/<br\s*\/?>/i', "\n", preg_replace('/<br\s*\/?>\s*/i', '<br>', $sBreachHistory))); ?></textarea>
    				</div>
                </div>
            </fieldset>

    		<fieldset>
        		<p class="noMargin"> Editing existing WTD record</p>
        		</fieldset>

        		<div class="WorkingTime btns-block">
                    <div class="WorkingTimeCol WorkingTimeCol2">&nbsp;</div>
                    <div class="WorkingTimeCol WorkingTimeCol2">&nbsp;</div>
                    <div class="WorkingTimeCol WorkingTimeCol2">
                        <button type="text" class="wt-btn" id="updateBtn" onclick="updateBreachDetails(<?php echo $request->get('teamId');?>)">Update</button>
                    </div>
                    <div class="WorkingTimeCol WorkingTimeCol2">
                        <button type="text" class="wt-btn" id="canceledBtn" onclick="cancelBreachUpdation(<?php echo $request->get('scheduledPersonId');?>,'<?php echo $request->get('dutyDate');?>',<?php echo $request->get('teamId');?>)">Cancel</button>
                    </div>
                    <div class="WorkingTimeCol WorkingTimeCol2">
                        <button type="text" class="wt-btn" id="finishedBtn" onclick="closeWTDPopup('<?php echo $request->get('cellId');?>')">Finished</button>
                    </div>
        			<div class="WorkingTimeCol WorkingTimeCol2"></div>
                </div>
            </fieldset>
		</div>
    </form>
</div>
<div id="refreshPageEditWeekly" style="display: none;"></div>
<div id="isBreachApproved" style="display: none;"></div>

<script>
    $('#WTDBreaches tbody tr').on('click','td',function () {
        $('tr').removeClass('highlightOrange');
        $(this).parent().addClass('highlightOrange');
	});

    var breachmaxchars = 500;
    $('#breachComments').keyup(function () {
        var tlengthbcomment = $(this).val().length;
        $(this).val($(this).val().substring(0, breachmaxchars));
        var tlengthbcomment = $(this).val().length;
        if(tlengthbcomment < 5){
            $('#errMsg').html('Please add a meaningful Comment in the Comments field.<br>(i.e - more than 5 characters)');
            $('#errMsg').css('color','#FF0000');
            $('#errMsg').show();
        } else {
            $('#errMsg').html('');
            $('#errMsg').hide();
        }
        remainbreachcomments = breachmaxchars - parseInt(tlengthbcomment);
        $('#remainBreachComments').text(remainbreachcomments);
    });

    $(document).ready(function() {
        var breachListCount = parseInt('<?php echo count($breachesList); ?>');
        var breachListFirstId = parseInt('<?php echo $sBreachID;?>');
        var pTeamId = parseInt('<?php echo $request->get('teamId');?>');
        if(breachListCount == 1){
            getBreachDetails(breachListFirstId, pTeamId);
            $("#rowNum"+breachListFirstId).addClass('highlightOrange');
            $("#WTDApproval").prop('checked', true);
            $("#WTDApproval").val('');
            approveWTDBreach(breachListCount,breachListFirstId,pTeamId);
        } else {
            $("#rowNum"+breachListFirstId).removeClass('highlightOrange');
        }
        if($('#WTDApproval').prop('checked') == false){
            $('#updateBtn').attr('disabled','disabled');
            $('#canceledBtn').attr('disabled','disabled');
        }
    });
</script>