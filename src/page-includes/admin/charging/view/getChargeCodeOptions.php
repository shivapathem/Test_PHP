<?php
	$chargeCodeReqStr	=	$requestData['chargeCodeReqStr'];
	$chargeCodeReqArr	=	explode(',', $chargeCodeReqStr);
	$chargeCodeReqArr	=	array_filter($chargeCodeReqArr);
	$chargeOptionContainer	=	'';
	foreach($data as $dataVal)
	{
		if(!empty($dataVal['EstablishCode']))
		{
			$selectedStr	=	'';
			if( (in_array($dataVal['EstablishCode'], $chargeCodeReqArr) ) && (count($chargeCodeReqArr) != count($data)) )
			{
				$selectedStr	= 'selected="selected"';
			}
			$chargeOptionContainer .= '<option value="'.$dataVal['EstablishCode'].'" title="'.$dataVal['EstablishCode'].'" '.$selectedStr.' >'.$dataVal['EstablishCode'].'</option>';
		}
	}
?>
<option value="0" title="All Charge Codes" onClick="chargeCodeContainer.value = $('#chargeCode option').map(function() {if($(this).val() != ''){return $(this).val();}}).get().join(',');" <?php if(count($chargeCodeReqArr) == count($data) || (count($data) == 0 || count($chargeCodeReqArr) == 0) ){echo 'selected="selected"';} ?> >All Charge Codes</option>
<?php echo $chargeOptionContainer; ?>