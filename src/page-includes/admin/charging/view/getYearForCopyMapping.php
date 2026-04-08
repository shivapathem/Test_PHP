<form name="copyMappingForm" id="copyMappingForm" onSubmit="return false;" >
	<div id="mappingEntrieAdd" style="display:block;">
		<div class="popupHeading">Copy Record</div>
		<div class="popupContent">
			<div class="fields">
				<label for="copyYear">Copy records to Financial year</label>
				<select id="copyYear" name="copyYear" onChange="$.cookie('copyYearHidden', copyYear.value);" >
					<?php 
					if(empty($requestData['copyYearHidden']))
					{
						$copyYearHidden			=	(date("m") < 4) ? date("Y") : date("Y") + 1;
					}else
					{
						$copyYearHidden			=	$requestData['copyYearHidden'];
					}
					$selectedYearFrom			=	$requestData['selectedYear'] ? $requestData['selectedYear'] : date("Y");
					$yearOptionContainerFrom	=	'';
					for($yearCounter=(date("Y") - 10); $yearCounter < 2051; $yearCounter++)
					{
						if($yearCounter == $selectedYearFrom)
						{
							$yearOptionContainerFrom.=	'<option value="'.$yearCounter.'" title="'.$yearCounter.'" disabled="disabled">'.$yearCounter.'</option>';
						}elseif($yearCounter == $copyYearHidden)
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
			<div class="popupButton">
				<input type="hidden" id="checkedMappingIdArr" name="checkedMappingIdArr" value="<?php echo implode(',', $requestData['checkedMappingIdArr']); ?>">
				<input type="hidden" id="conrollerName" name="conrollerName" value="copyMapping">
				<button type="text" class="charging-btn" onClick="submitCopyForm();" >OK</button>
				<button type="text" class="charging-btn" onClick="$.facebox.close();" >Cancel</button>
			</div>
		</div>
	</div>
</form>
<script>
function submitCopyForm()
{
	$.ajax({
		  type: 'POST',
		  url: 'page-includes/admin/charging/index.php',
		  data: $('#copyMappingForm').serialize(),
		  success: function (returnHtml) {
			  setTimeout(function() {$.facebox(returnHtml);}, 1000);
		}
	});
}
</script>