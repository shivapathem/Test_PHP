<link href="../styles/charging/charging.css?v=<?php echo time(); ?>" rel="stylesheet">
<script src="js/chargingDutyAllocation.js?v=<?php echo time(); ?>"></script>
<div class="charging-container" id="charging-container-Popup">
    <div class="charging-main-heading">
        <h1 class="charging-heading">Charging</h1>
    </div>
    <form id="chargingDutyMappingForm" name="chargingDutyMappingForm" onsubmit="return false; ">
    <div class="w-83">
        <!-- 1 field.......................................... -->
        <fieldset>
            <legend class="week-small-text">Name & Duty Details</legend>
            <div class="chargeRow fullWidth">
                <div class="chargeCol chargeCol4 firstNameField">
                    <label class="charg-lab">First Name </label>
                    <input class="charg-inp" text="type"
                           value="<?php echo $data['allocationDetails']['DisplayFirstName']; ?>" readonly>
                </div>
                <div class="chargeCol chargeCol6 lastNameField">
                    <label class="charg-lab mr32">Last Name </label>
                    <input class="charg-inp" text="type"
                           value="<?php echo $data['allocationDetails']['DisplayLastName']; ?>" readonly>
                </div>

                <div class="chargeCol chargeCol4 dateField">
                    <label class="charg-lab">Date</label>
                    <input id="ChargingDate" name="ChargingDate" value="<?php echo date("d/m/Y", strtotime($data['allocationDetails']['StartDate'])); ?>" class="charg-inp" type="hidden" >
                    <input class="charg-inp" text="type" readonly
                           value="<?php echo date("d/m/Y", strtotime($data['allocationDetails']['StartDate'])); ?>">
                </div>
                <div class="chargeCol chargeCol9 dutyField">
                    <label class="charg-lab mr30">Duty</label>
                    <input class="charg-inp" text="type" id="dutyName" name="dutyName" value="<?php echo $data['allocationDetails']['DutyName']; ?>"
                            readonly>
                    <input type="hidden" id="allocationId" name="allocationId" value="<?php echo $data['allocationDetails']['AllocationsSPID'] . '_' . $data['allocationDetails']['StartDate']; ?>" >
                    <input type="hidden" id="masterDutyId" name="masterDutyId" value="<?php echo $data['allocationDetails']['MasterDutyId']; ?>">
                    <input type="hidden" id="staffDetailsId" name="staffDetailsId" value="<?php echo $data['allocationDetails']['StaffDetailsID']; ?>">
                    <input type="hidden" id="scheduledPersonId" name="scheduledPersonId" value="<?php echo $data['allocationDetails']['ScheduledPersonID']; ?>">
                </div>


                <div class="chargeCol chargeCol5 chargeCodeField">
                    <label class="charg-lab">Charge Code<img
                                src="./images/info.png" alt="helpicon" title="This is where the money is going into"
                                class="chargingInfo-image" style="margin-left:1px"></legend></label>
                    <input class="charg-inp" text="type" id="chargeCodeschteam" name="chargeCodeschteam"
                           value="<?php echo $data['allocationDetails']['EstablishCode']; ?>" disabled>
                    <input class="charg-inp" text="type" id="chargeCodeschTeamId" name="chargeCodeschTeamId"
                           value="<?php echo $data['allocationDetails']['EstablishCodeId']; ?>" type="hidden">
                </div>
                <div class="chargeCol chargeCol7 chargeCodeDesc">
                    <input class="charg-inp" text="type"
                           value="<?php echo $data['allocationDetails']['EstablishCodeDescription']; ?>" disabled>
                </div>
            </div>
        </fieldset>
        <!-- 2 field.......................................... -->
        <fieldset>
            <legend class="week-small-text">Activity Details</legend>
            <div class="chargeRow fullWidth">
                <div class="chargeCol chargeCol5">
                    <label class="charg-lab">Activity Code<span class="required">*</span></label>
                    <select class="sec chargingSelectPadding" id="activityCode" name="activityCode" mandatory="yes" fieldname="Activity Code">
                        <option value="-1" selected>Select the Activity code</option>
                        <?php
                        $selected = '-1';
                        $price = $activityCodeDesc = '';
						$hiddenDefaultActivityCode = '';
                        foreach ($data['activecodemapped'] as $codeemapped) {
                            if ((int)$codeemapped['IsDefault'] == 1){
                                $activityCodeDesc = $codeemapped['Description'];
                                $selected = 'selected';
                                $price = $codeemapped['Price'] != '' ? $codeemapped['Price'] : 0;
                                $hiddenDefaultActivityCode = $codeemapped['ActivityCodeId'];
                            } else {
                                $selected = '-1';
                            }
                            ?>
                            <option ActivityPrice="<?php echo $codeemapped['Price'] ?>"
                                    ActivityDesc="<?php echo $codeemapped['Description'] ?>"
                                    value="<?php echo $codeemapped['ActivityCodeId'] ?>" <?php echo $selected; ?>><?php echo $codeemapped['ActivityCodeName'] ?></option>
                        <?php } ?>
                    </select>
                    <input type="hidden" id="hiddenDefaultActivityCode" name="hiddenDefaultActivityCode" value="<?php echo $hiddenDefaultActivityCode ?>">
                </div>
                <div class="chargeCol chargeCol7 activityCodeDesc">
                    <input text="type" class="charg-inp" id="activityCodeDesc" name="activityCodeDesc"
                           value="<?php echo $activityCodeDesc ?>" disabled>
                </div>
                <div class="chargeCol chargeCol5 unitPriceField">
                    <label class="charg-lab">Unit Price</label>
                    <input class="charg-inp" text="type" id="UnitPrice" name="UnitPrice" value="£<?php echo $price; ?>" disabled>
                    <input class="charg-inp" text="type" id="activityUnitPrice" name="activityUnitPrice"
                           value="<?php echo $price; ?>" type="hidden">
                </div>

                <div class="chargeCol chargeCol6">
                    <input type="radio" class="radioButtonStyle" id="dutyCharging" name="dutyCharging" value="Hold">
                    <label for="Hold">Hold</label>
                    <input type="radio" class="radioButtonStyle" id="dutyCharging" name="dutyCharging" checked
                           value="Provisional">
                    <label for="provisional">Provisional</label>
                    <input type="radio" class="radioButtonStyle" id="dutyCharging" name="dutyCharging" value="Actual">
                    <label for="actual">Actual</label>
                </div>
            </div>
        </fieldset>

        <!-- 3 field.......................................... -->
        <fieldset>
            <legend class="week-small-text" title="This is where the money is coming from">Charge From <img
                        src="./images/info.png" alt="helpicon" title="This is where the money is coming from"
                        class="chargingInfo-image"></legend>
            <div class="chargeRow fullWidth">
                <div class="chargeCol chargeCol3">
                    <input type="radio" onclick="switchChargeWbsCodeRadio('chargeCodeRadio')" id="chargeWbsCodeRadio" name="chargeWbsCodeRadio"
                           value="chargeCodeRadio" class="radioButtonStyle" checked>
                    <label for="costCenter">Charge Code</label>
                </div>
                <div class="chargeCol chargeCol3">
                    <input type="radio" onclick="switchChargeWbsCodeRadio('wbsCodeRadio')" id="chargeWbsCodeRadio" name="chargeWbsCodeRadio"
                           value="wbsCodeRadio" class="radioButtonStyle">
                    <label for="WBSElement">WBS Element</label>
                </div>
                <div class="chargeCol chargeCol6">
                    <input type="checkbox" id="maintainCharging" name="maintainCharging" class="checkBoxInput handcursor" onclick="maintainChargeCode()">
                    <label for="maintainCharging">Maintain Charge From Details</label>
                </div>
            </div>
            <div class="chargeRow fullWidth">
                <div id="costCenterValue" class="chargeCol chargeCol5 displayChargeBlock">
                    <label class="charg-lab" id="chargeWbsCodelabel" name="chargeWbsCodelabel">Charge Code<span class="required">*</span></label>
                    <select class="sec chargingSelectPadding" id="chargeCodemain" name="chargeCodeMain" style="margin-left:-5px;">
                        <option value="-1">Select Charge code</option>
                        <?php foreach ($data['chargeCodeList'] as $chargeCodeList) { ?>
                            <option chargeCodeDesc="<?php echo $chargeCodeList['Description'] ?>"
                                    value="<?php echo $chargeCodeList['ChargeWbsCodeId'] ?>"><?php echo $chargeCodeList['ChargeWbsCodeName'] ?></option>
                        <?php } ?>
                    </select>
                    <select class="sec chargeCol chargeCol5 chargingSelectPadding" id="WbsCode" name="WbsCode" disabled>
                        <option value="-1">Select WBS Element</option>
                        <?php foreach ($data['WbsCodeList'] as $wbsCode) { ?>
                            <option wbsCodeDesc="<?php echo $wbsCode['Description'] ?>"
                                    value="<?php echo $wbsCode['ChargeWbsCodeId'] ?>"><?php echo $wbsCode['ChargeWbsCodeName'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="chargeCol chargeCol7 chargeWbsDescField">
                    <input class="charg-inp" type="text" id="chargeWbsDesc" name="chargeWbsDesc" disabled mandatory="yes" fieldname="Charge Code">
                </div>
                <div class="chargeCol chargeCol12 chargeComment" style="display: flex;">
                    <label class="charg-lab">Comments</label>
                    <textarea style="width:74%;" id="Comment" name="Comment" rows="3"></textarea>
                </div>
                <div class="chargeCol chargeCol6 chargeContactField">
                    <label class="charg-lab">Contact<span class="required">*</span></label>
                    <input class="charg-inp" type="text" id="chargeWbsContact" name="chargeWbsContact" mandatory="yes" fieldname="Contact">
                </div>
                <div class="chargeCol chargeCol6 chargeContactField chargeTelephoneField">
                    <label class="charg-lab">Telephone<span class="required">*</span></label>
                    <input class="charg-inp" type="text" id="chargeWbsTelephone" name="chargeWbsTelephone" mandatory="yes" fieldname="Telephone">
                </div>
                <div class="chargeCol chargeCol6 chargePriceQty">
                    <label class="charg-lab">Quantity<span class="required">*</span></label>
                    <input class="charg-inp" maxlen="50" type="text" id="chargeWbsQuantity" name="chargeWbsQuantity" mandatory="yes" fieldname="Quantity">
                </div>
                <div class="chargeCol chargeCol6 chargePriceQty">
                    <label class="charg-lab">Total Price</label>
                    <input class="charg-inp" maxlen="1000" type="text" id="chargeWbsTotalPrice" name="chargeWbsTotalPrice" disabled>
                    <input type="hidden" id="totalPriceValidation" name="totalPriceValidation" value="0" />
                </div>
            </div>
        </fieldset>
    </div>
    <!-- buttons........................... -->
    <div class="chargingBtn btns-block">
        <div class="chargeCol chargeCol12">
            <button type="text" id="newChargeWbsCode" name="newChargeWbsCode" class="charging-btn btn-focus" onclick="createNewRecordDutyChargeMapping()">New</button>
        </div>
        <div class="chargeCol chargeCol12">
            <button type="text" id="saveChargeWbsCode" name="saveChargeWbsCode" class="charging-btn" onclick="saveChargingDetails('chargingDutyMappingForm')">Save</button>
        </div>
        <div class="chargeCol chargeCol12">
            <button type="text" id="deleteChargeWbsCode" name="deleteChargeWbsCode" class="charging-btn" onclick="clickDeleteButton()">Delete</button>
        </div>
        <?php
        if (sizeof($data['chargingDutyWeeklyAllocation']) == 0) {
            $ActionType = 'Insert';
        } else if ((sizeof($data['chargingDutyWeeklyAllocation']) == 1) && ($data['chargingDutyWeeklyAllocation'][0]['MasterDutyId'] != $data['allocationDetails']['MasterDutyId'])){
            $ActionType = 'Insert';
        } else {
            $ActionType = 'Edit';
        }
        ?>
        <div class="chargeCol chargeCol12">
            <button type="text" id="finishedChargeWbsCode" name="finishedChargeWbsCode" class="charging-btn" onclick="closeFaceboxMain()">Finished</button>
            <input type="hidden" id="conrollerName" name="conrollerName" value="insUpdateDelChargingDutyMapping" />
            <input type="hidden" id="ActionType" name="ActionType" value="<?php echo $ActionType; ?>" />
            <input type="hidden" id="chargingId" name="chargingId" value="0" />
            <input type="hidden" id="formDataChanged" name="formDataChanged" value="0" />
            <input type="hidden" id="teamId" name="teamId" value="<?php echo $data['teamId']?>" class="SchedulingTeamIdMaintain" />
            <input type="hidden" id="allocationsDutyIdCharge" name="allocationsDutyId" value="<?php echo $data['allocationsDutyId'];  ?>" />
            <input type="hidden" id="allocationsSpIdCharge" name="allocationsSpId" value="<?php echo $data['allocationsSpId'];  ?>" />
        </div>
    </div>
    </form>
    <!-- 4 field.......................................... -->
    <fieldset>
        <legend class="week-small-text">Existing Charges</legend>
            <table id="chargingDutyMappingRecordTable" onclick="EventCallMouseDownTable.value = 'TableClick'" click class="fullWidth">
                <thead>
                <tr class="sicknes-tr">
                    <th class="charg-labs">Charge Code</th>
                    <th class="charg-labs">Activity Code</th>
                    <th class="charg-labs">Charge From</th>
                    <th class="charg-labs">Quantity</th>
                    <th class="charg-labs">Unit Price</th>
                    <th class="charg-labs">Total Price</th>
                    <th class="charg-labs">Status</th>
                    <th class="charg-labs">Sent to Finance</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $colourCodeEditWeekly = '';
				$SendToFinanceNoCounter = 0;
				$SendToFinanceYesCounter = 0;
				$SendToFinanceTotalCounter = 0;
				$holdCounter = 0;
                if (sizeof($data['chargingDutyWeeklyAllocation']) == 0) {?>
                    <tr class="">
                        <td class="sub-text TextAlignChargingNoRecord" colspan="8">No Record Found</td>
                    </tr>
                <?php
                } else {
                    $mappingIdContainerArr	=	array();
                    $DefaultChargingId = $data['chargingDutyWeeklyAllocation'][0]['ChargingId'];
                    foreach($data['chargingDutyWeeklyAllocation'] as $getChargingDutyWeeklyAllocation)
                    {

                        $mappingIdContainerArr[$getChargingDutyWeeklyAllocation['ChargingId']]	=	$getChargingDutyWeeklyAllocation;
                        if ($getChargingDutyWeeklyAllocation['MaintainCharging'] == 1) {
                            $DefaultChargingId = $getChargingDutyWeeklyAllocation['ChargingId'];
                        }
                        if ($getChargingDutyWeeklyAllocation['AllocationId'] != $data['allocationDetails']['AllocationsSPID']) {
                            $RowStatus = "hidden";
                        } else  {
                            if ($getChargingDutyWeeklyAllocation['IsActual'] == 0) {
                                $SendToFinanceNoCounter++;
                            } else {
                                $SendToFinanceYesCounter++;
                            }
                            $SendToFinanceTotalCounter++;
                            $RowStatus = "";
							if($getChargingDutyWeeklyAllocation['IsActual'] == 2)
							{
								$holdCounter++;
							}
                        }

                        ?>
                        <tr <?php echo $RowStatus ?> class="<?php echo $getChargingDutyWeeklyAllocation['SentToFinance'] == 'Yes' ? 'sapId-clr' : 'noSapId-clr' ?> handcursor"
                            id="<?php echo $getChargingDutyWeeklyAllocation['ChargingId']; ?>"
                            onMouseDown='EventCallMouseDownTable.value = "TableClick"; highlightChargingRow(<?php echo $getChargingDutyWeeklyAllocation['ChargingId']; ?>,
                                        <?php echo json_encode($mappingIdContainerArr[$getChargingDutyWeeklyAllocation['ChargingId']])?>); ' >

                            <td id="chargingTableColumn-1-<?php echo $getChargingDutyWeeklyAllocation['ChargingId']; ?>" class="sub-text"><?php echo $getChargingDutyWeeklyAllocation['EstablishCode']; ?></td>
                            <td id="chargingTableColumn-2-<?php echo $getChargingDutyWeeklyAllocation['ChargingId']; ?>" class="sub-text"><?php echo $getChargingDutyWeeklyAllocation['ActivityCodeName']; ?></td>
                            <td class="sub-text"><?php echo $getChargingDutyWeeklyAllocation['ChargeWbsCodeName']; ?></td>
                            <td class="sub-text"><?php echo $getChargingDutyWeeklyAllocation['Quantity']; ?></td>
                            <td id="chargingTableColumn-5-<?php echo $RowStatus == 'hidden' ? 0 : $getChargingDutyWeeklyAllocation['ChargingId']; ?>" class="sub-text">£<?php echo number_format((float)$getChargingDutyWeeklyAllocation['UnitPrice'], 2, '.', ''); ?></td>
                            <td class="sub-text">£<?php echo number_format((float)$getChargingDutyWeeklyAllocation['TotalPrice'], 2, '.', ''); ?></td>
                            <td class="sub-text"><?php echo $getChargingDutyWeeklyAllocation['Status']; ?></td>
                            <td class="sub-text"><?php echo $getChargingDutyWeeklyAllocation['SentToFinance']; ?></td>
                        </tr>
                        <?php
                    }
					$colourCodeEditWeekly = 'transparent';
                    if ($SendToFinanceNoCounter == $SendToFinanceTotalCounter && $SendToFinanceTotalCounter != 0) {
                        $colourCodeEditWeekly = 'Red';
                    } else if ($SendToFinanceYesCounter == $SendToFinanceTotalCounter && $SendToFinanceTotalCounter != 0) {
                        $colourCodeEditWeekly = 'Green';
                    } else if ($SendToFinanceTotalCounter == 0) {
                        $colourCodeEditWeekly = '';
                    }
                    else {
                        $colourCodeEditWeekly = 'Blue';
                    }
					if(($holdCounter > 0) && ($holdCounter == $SendToFinanceTotalCounter))
					{
						$colourCodeEditWeekly = 'Yellow';
					}
                }
                ?>
                </tbody>
            </table>
        <input type="hidden" id="SelectedCharging" value="0">
        <input type="hidden" id="EventCallMouseDownTable" value="Onload">
        <input id="chargingDutyMappingRecordTable_hide" type="hidden" value="<?php echo $SendToFinanceTotalCounter.',No: '.$SendToFinanceNoCounter.', Yes:'.$SendToFinanceYesCounter;?>">
        <form id="chargingDutyMappingRecord" name="chargingDutyMappingRecord">
            <input type="hidden" id="conrollerName" name="conrollerName" value="getDutyChargeMapping">
        </form>

        <div class="chargeRow fullWidth" style="margin: 7px 5px 0px 5px;">
            <div class="chargeCol chargeCol4">
                <span>Selected Record </span>
                <span class="colors-box box-1"></span>
            </div>

            <div class="chargeCol chargeCol4">
                <span>Sent to Finance </span>
                <span class="colors-box box-2"></span>
            </div>

            <div class="chargeCol chargeCol4">
                <span>Not Sent to Finance </span>
                <span class="colors-box box-3"></span>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <p id="footerCharging" class="noMargin">New Charging Record</p>
    </fieldset>
</div>
<script>
    $(".division-seclect").chosen({
        no_results_text: "Oops, nothing found!",
        width: "200px"
    });

    function chargingCallbackFunc(data) {
        if ($($.parseHTML(data)).filter("#charging-container-Popup").length) {
            $.facebox(data);
            $(".btn-focus").removeClass('btn-focus');
            $("#finishedChargeWbsCode").addClass('btn-focus');
        } else {
            customAlertByModel(data);
        }
    }
    <?php
        if (sizeof($data['chargingDutyWeeklyAllocation']) > 0) {
            echo '$("#'.$DefaultChargingId.'").mousedown();';
        }
    ?>
    var NewAllocId = $("#allocationId").val();
    var scheduledPersonId = $("#scheduledPersonId").val();
    $("#colUnqId_"+NewAllocId).removeClass("cornerIcon-Blue");
    $("#colUnqId_"+NewAllocId).removeClass("cornerIcon");
    $("#colUnqId_"+NewAllocId).removeClass("cornerIcon-Green");
    $("#colUnqId_"+NewAllocId).removeClass("cornerIcon-Red");
    $("#colUnqId_"+NewAllocId).removeClass("cornerIcon-Yellow");
	$("#schPersonId_"+scheduledPersonId).css('color', "<?php echo $colourCodeEditWeekly ? $colourCodeEditWeekly : 'transparent';?>");
    <?php if(in_array($colourCodeEditWeekly, array("", "0", "transparent"))) { ?>
        $("#colUnqId_"+NewAllocId).attr("data-charging-present",0);
        $("#"+NewAllocId).attr("data-charging-present",0);
		if(!$("#schPersonId_"+scheduledPersonId).length)
		{
			updateDataInIndexedDbForCharging(NewAllocId, "");
		}
    <?php } else { ?>
        $("#colUnqId_"+NewAllocId).addClass("cornerIcon-<?php echo $colourCodeEditWeekly;?>");
        $("#colUnqId_"+NewAllocId).addClass("cornerIcon");
        $("#colUnqId_"+NewAllocId).attr("data-charging-present", 1);
        $("#"+NewAllocId).attr("data-charging-present", 1);
		if(!$("#schPersonId_"+scheduledPersonId).length)
		{
			updateDataInIndexedDbForCharging(NewAllocId, "cornerIcon cornerIcon-<?php echo $colourCodeEditWeekly;?>");
		}
    <?php } ?>
</script>
