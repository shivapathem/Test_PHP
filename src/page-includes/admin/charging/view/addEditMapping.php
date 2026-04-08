<?php
	$disableStr	=	$requestData['mappingid'] ? 'disabled="disabled"' : "";
?>
<link href="../styles/charging/charging.css" rel="stylesheet">
<form name="mappingEntrieAddEditForm" id="mappingEntrieAddEditForm" onSubmit="return false;" >
	<div id="mappingEntrieAdd" style="display:block;">
		<div class="popupHeading"><?php echo ($requestData['mappingid']) ? 'Edit' : 'Add'; ?> Record</div>
		<div class="popupContent">
			<div class="fields">
				<label class="popupLabelAlignment" for="entryYear">Year</label>
				<select id="entryYear" id="addEditYear" name="addEditYear" <?php echo $disableStr; ?>>
					<?php 
					$selectedYearFrom			=	$requestData['year'] ? $requestData['year'] : date("Y");
					$yearOptionContainerFrom	=	'';
					for($yearCounter=1995; $yearCounter < 2051; $yearCounter++)
					{
						if($yearCounter == $selectedYearFrom)
						{
							$yearOptionContainerFrom.=	'<option value="'.$yearCounter.'" title="'.$yearCounter.'" selected="selected">'.$yearCounter.'</option>';
						}else
						{
							$yearOptionContainerFrom.=	'<option value="'.$yearCounter.'" title="'.$yearCounter.'">'.$yearCounter.'</option>';
						}
					}
					echo $yearOptionContainerFrom;
					?>
				</select>
			</div>
			<div class="fields">
				<label class="popupLabelAlignment" for="entryChargeCode">Charge Code</label>
				<select id="addEditChargeCode" name="addEditChargeCode" <?php echo $disableStr; ?> onChange="chargeCodeDesc.innerHTML = this.options[this.selectedIndex].getAttribute('desc').substr(0, 20);chargeCodeDesc.title = this.options[this.selectedIndex].getAttribute('desc');">
					<?php 
					$selectedStr	=	'';
					$chargeCodeDesc	=	$data[0][0]['EstablishCodeDescription'];
					foreach($data[0] as $chargeCodeVal)
					{
						if($requestData['establishcodeid'] == $chargeCodeVal['EstablishCodeId'])
						{
							$chargeCodeDesc= 	$chargeCodeVal['EstablishCodeDescription'];
						}
						$selectedStr	=	($requestData['establishcodeid'] == $chargeCodeVal['EstablishCodeId']) ? 'selected="selected"' : '';
						echo '<option value="'.$chargeCodeVal['EstablishCodeId'].'" '.$selectedStr.'  desc="'.$chargeCodeVal['EstablishCodeDescription'].'" >'.$chargeCodeVal['EstablishCode'].'</option>';
					}
					?>
				</select>
				<div id="chargeCodeDesc" title="<?php echo $chargeCodeDesc; ?>"><?php echo substr($chargeCodeDesc, 0, 20); ?></div>
			</div>
			<div class="fields">
				<label class="popupLabelAlignment" for="entryActivityType">Activity Code</label>
				<select id="addEditActivityCode" name="addEditActivityCode" <?php echo $disableStr; ?> onChange="activityCodeDesc.innerHTML = this.options[this.selectedIndex].getAttribute('desc').substr(0, 20);activityCodeDesc.title = this.options[this.selectedIndex].getAttribute('desc');">
					<?php 
					$selectedStr	=	'';
					$activityCodeDesc= 	$data[1][0]['Description'];
					foreach($data[1] as $chargeCodeVal)
					{
						if($requestData['activecodeid'] == $chargeCodeVal['ActivityCodeId'])
						{
							$activityCodeDesc= 	$chargeCodeVal['Description'];
						}
						$selectedStr	=	($requestData['activecodeid'] == $chargeCodeVal['ActivityCodeId']) ? 'selected="selected"' : '';
						echo '<option value="'.$chargeCodeVal['ActivityCodeId'].'" '.$selectedStr.' desc="'.$chargeCodeVal['Description'].'" >'.$chargeCodeVal['ActivityCodeName'].'</option>';
					}
					?>
				</select>
				<div id="activityCodeDesc" title="<?php echo $activityCodeDesc; ?>"><?php echo substr($activityCodeDesc, 0, 20); ?></div>
			</div>
			<div class="fields">
				<label class="popupLabelAlignment" for="entryPrice">Price</label>
				<input type="text" id="addEditPriceDisplay" name="addEditPriceDisplay" value="£<?php echo number_format($requestData['price'], 2, '.', ''); ?>" onChange="addEditPrice.value = this.value.replace('£', '')" />
				<input type="hidden" id="addEditPrice" name="addEditPrice" value="<?php echo number_format($requestData['price'], 2, '.', ''); ?>"/>
			</div>
			<div class="popupButton">
				<button type="text" class="charging-btn" onClick="submitAddEditForm()" ><?php echo ($requestData['mappingid']) ? 'Update' : 'Add'; ?></button>
				<button type="text" class="charging-btn" onClick="$.facebox.close();" >Cancel</button>
			</div>
		</div>
	</div>
	<input type="hidden" name="mappingId" id="mappingId" value="<?php echo $requestData['mappingid']; ?>" >
	<input type="hidden" name="conrollerName" id="conrollerName" value="addEditMappingSave" >
	<input type="hidden" name="actionType" id="actionType" value="<?php echo ($requestData['mappingid']) ? 'UPDATE' : 'INSERT'; ?>" >
</form>
<script>
function submitAddEditForm()
{
	let addEditPrice 	= 	document.getElementById('addEditPrice').value;
	let decimalRegx  	= 	/^(?:\d*\.\d{1,2}|\d+)$/;
	if (!decimalRegx.test(addEditPrice))
	{
		customAlertByModel("Entered price is invalid.");
		return false;
	}
	if (addEditPrice > 1000)
	{
		customAlertByModel("The maximum Price value is £1000.00");
		return false;
	}
	if (addEditPrice <= 0)
	{
		customAlertByModel("Price should be greated than £0.00");
		return false;
	}
	$.ajax({
		  type: 'POST',
		  url: 'page-includes/admin/charging/index.php',
		  data: $('#mappingEntrieAddEditForm').serialize(),
		  success: function (returnHtml) {
			  returnHtml= JSON.parse(returnHtml);
			if(returnHtml.Status == 1)
			{
				document.getElementById('selectRowId').value = returnHtml.LastId;
				$.facebox.close();
				applyFilter();
				return true;
			}else
			{
				customAlert(returnHtml.StatusCode);
				return false;
			}
		}
	});
}
</script>