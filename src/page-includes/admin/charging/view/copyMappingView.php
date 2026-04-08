<link rel="stylesheet" href="../styles/jquery-ui.css">
<div style="width: 600px">
	<form name="copyMappingSaveForm" id="copyMappingSaveForm" onSubmit="return false;" >
	<table class="redtable smalltable bluetable width100Percent">
		<thead>
			<tr>
				<th class="noBorder" colspan="3">Mapping Details</th>
				<th class="noBorder" colspan="1">
					<span class="spanFaceBoxClose" onClick="$.facebox.close();">×</span>
				</th>
			</tr>
		</thead>
	</table>
	<table class="redtable smalltable bluetable width100Percent">
		<tbody>
			<tr>
				<td class="lightblue">Mapping Year</td>
				<td><input type="text" value="<?php echo $requestData['copyYear'] ?>" disabled="disabled" ></td>
			</tr>
		</tbody>
	</table>
	<div class="ui-tabs ui-corner-all ui-widget ui-widget-content">
		<ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
		<?php
		$displaySkippedTab	=	'none';
		if(count($data) > 0 && empty($requestData['skippedRowHtml']))
		{
		?>
			<li id="tab_a" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active" onClick="$('#tab_a').addClass('ui-tabs-active ui-state-active');$('#tab_d').removeClass('ui-tabs-active ui-state-active'); skippedRcordsTab.style.display='none'; copyRcordsTab.style.display='block';" >
				<a href="javascript:void(0)" class="ui-tabs-anchor">Copy Records</a>
			</li>
		<?php
		}elseif(count($data) <= 0 && !empty($requestData['skippedRowHtml']))
		{
			$displaySkippedTab	=	'block';
		?>
			<li id="tab_d" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active" onClick="$('#tab_d').addClass('ui-tabs-active ui-state-active');$('#tab_a').removeClass('ui-tabs-active ui-state-active'); skippedRcordsTab.style.display='block'; copyRcordsTab.style.display='none';" >
				<a href="javascript:void(0)" class="ui-tabs-anchor">Skipped Records</a>
			</li>
		<?php
		}elseif(count($data) > 0 && !empty($requestData['skippedRowHtml']))
		{
		?>
			<li id="tab_a" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active" onClick="$('#tab_a').addClass('ui-tabs-active ui-state-active');$('#tab_d').removeClass('ui-tabs-active ui-state-active'); skippedRcordsTab.style.display='none'; copyRcordsTab.style.display='block';" >
				<a href="javascript:void(0)" class="ui-tabs-anchor">Copy Records</a>
			</li>
			<li id="tab_d" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab" onClick="$('#tab_d').addClass('ui-tabs-active ui-state-active');$('#tab_a').removeClass('ui-tabs-active ui-state-active'); skippedRcordsTab.style.display='block'; copyRcordsTab.style.display='none';" >
				<a href="javascript:void(0)" class="ui-tabs-anchor">Skipped Records</a>
			</li>
		<?php
		}
		?>
		</ul>
		<?php
		if(count($data) > 0)
		{
		?>
		<div id="copyRcordsTab" aria-labelledby="dutyrotas" role="tabpanel" class="ui-tabs-panel ui-corner-bottom ui-widget-content" aria-hidden="false" style="display: block;">
			<div style="font-size:11px;">
				<strong>Please enter the new price for the copied records Or just Click OK to keep current price.</strong>
			</div>
			<div class="chargeCol chargeCol12">
				<div class="tables mappingEntriesContainer">
					<table id="mappingEntries" class="oddevenclass tablesmall" style="width:100%">
						<thead>
							<tr>
								<th>Charge Code</th>
								<th>Charge Code Desc</th>
								<th>Activity Code</th>
								<th>Activity Code Desc</th>
								<th>Price</th>
							</tr>
						</thead>
						<tbody>
						<?php
							if(count($data) > 0)
							{
								foreach($data as $dataVal)
								{
									$idContainer[]	=	$dataVal['MappingId'];
						?>
								<tr >
									<td><?php echo $dataVal['EstablishCode']; ?></td>
									<td title="<?php echo $dataVal['EstablishCodeDescription']; ?>"><?php echo substr($dataVal['EstablishCodeDescription'], 0, 20); ?></td>
									<td><?php echo $dataVal['ActivityCodeName']; ?></td>
									<td title="<?php echo $dataVal['Description']; ?>"><?php echo substr($dataVal['Description'], 0, 30); ?></td>
									<td style="text-align: center;">
										<input style="width:50%; text-align: center;" name="price[<?php echo $dataVal['MappingId']; ?>][]" value="£<?php echo number_format($dataVal['Price'], 2, '.', ''); ?>" id="price_<?php echo $dataVal['MappingId']; ?>" estCode="<?php echo $dataVal['EstablishCode']; ?>" >
									</td>
								</tr>
						<?php
								}
							}else
							{
								echo '<tr><td colspan="5">No record Found</td></tr>';
							}
						?>
						<input type="hidden" name="idContainer" id="idContainer" value="<?php echo implode(',', $idContainer) ?>"> 
						</tbody>
					</table>
				</div>
			</div>
			<button type="text" class="charging-btn" onClick="copyMappingSave();" >OK</button>
			<button type="text" class="charging-btn" onClick="$.facebox.close();" >Cancel</button>
		</div>
		<?php
		}
		?>
		<?php 
		if(!empty($requestData['skippedRowHtml']))
		{
		?>
		<div id="skippedRcordsTab" aria-labelledby="dutyrotas" role="tabpanel" class="ui-tabs-panel ui-corner-bottom ui-widget-content" aria-hidden="false" style="display: <?php echo $displaySkippedTab; ?>;">
			<div style="font-size:11px;">
				<strong>The following records already exists in the <?php echo $requestData['copyYear'] ?> financial year and will be skipped.</strong>
			</div>
			<div class="chargeCol chargeCol12">
				<div class="tables mappingEntriesContainer">
					<table id="mappingEntries" class="oddevenclass tablesmall" style="width:100%">
						<thead>
							<tr>
								<th>Charge Code</th>
								<th>Charge Code Desc</th>
								<th>Activity Code</th>
								<th>Activity Code Desc</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								echo $requestData['skippedRowHtml'];
							?>
						</tbody>
					</table>
				</div>
			</div>
			<button type="text" class="charging-btn" onClick="$.facebox.close();" >Cancel</button>
		</div>
		<?php
		}
		?>
	</div>
	<input type="hidden" name="conrollerName" id="conrollerName" value="copyMappingSave">
	<input type="hidden" name="copyYear" id="copyYear" value="<?php echo $requestData['copyYear'] ?>">
	</form>
</div>
<script>
function copyMappingSave()
{
	let idContainer		=	document.getElementById('idContainer').value;
	let idContainerArr	=	idContainer.split(',');
	let priceVal		=	0;
	let tempId			=	'';
	let decimalRegx  	= 	/^(?:\d*\.\d{1,2}|\d+)$/;
	let validateMsg		=	'';
	for(let i=0; i<idContainerArr.length; i++)
	{
		tempId		=	'price_'+idContainerArr[i];
		priceVal	=	document.getElementById(tempId).value;
		priceVal	=	priceVal.replace('£', '');
		document.getElementById(tempId).style.borderColor	=	'';
		if (!decimalRegx.test(priceVal))
		{
			document.getElementById(tempId).style.borderColor	=	'#ff0000';
			validateMsg	+=	"Entered price for charge code "+$("#"+tempId).attr("estCode")+" is invalid.<br/>";
		}
		if (priceVal > 1000)
		{
			document.getElementById(tempId).style.borderColor	=	'#ff0000';
			validateMsg	+=	"Entered Price for charge code "+$("#"+tempId).attr("estCode")+" should be less than or equal to £1000.00.<br/>";
		}
		if (priceVal <= 0)
		{
			document.getElementById(tempId).style.borderColor	=	'#ff0000';
			validateMsg	+=	"Entered Price for charge code "+$("#"+tempId).attr("estCode")+" should be greated than or equal to £0.00.<br/>";
		}
	}
	if(validateMsg != '')
	{
		customAlertByModel(validateMsg);
		return false;
	}
	$.ajax({
		  type: 'POST',
		  url: 'page-includes/admin/charging/index.php',
		  data: $('#copyMappingSaveForm').serialize(),
		  success: function (returnHtml) {
			$.facebox.close();
			applyFilter();
			return true;
		}
	});
}
</script>
