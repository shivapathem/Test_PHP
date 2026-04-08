<div class="teamPayFilterBlock">
	<div class="grid box1">
		<div class="recordFilter recordFilter1">
			<h2 class="heading">Record Filter</h2>
			<div class="recordFilterBox">
			<form name="incorrectRecordFilter" id="incorrectRecordFilter" onSubmit="return false;" >
				<?php
					$requestData['name'] = $requestData['name'] ?? '';
					$requestData['empNumber'] = $requestData['empNumber'] ?? 0;
					$requestData['estCode'] = $requestData['estCode'] ?? 0;
					$requestData['filterType'] = $requestData['filterType'] ?? 0;
				?>
				<input type="text" class="wid-120" placeholder="Name" name="name" id="name" value="<?php echo $requestData['name']; ?>">
				<input type="text" class="wid-120" placeholder="Emp Number" name="empNumber" id="empNumber" value="<?php echo $requestData['empNumber']; ?>">
				<input type="text" class="wid-120" placeholder="Est Code" name="estCode" id="estCode" value="<?php echo $requestData['estCode']; ?>">
				<select id="schedulingTeamId" class="wid-120" name="schedulingTeamId">
					<option value=''>Select Team</option>
					<?php
						foreach($data[1] as $schedulingTeamsArr)
						{
							$selectedTeams	=	'';
							$requestData['schedulingTeamId'] = $requestData['schedulingTeamId'] ?? 0;
							if($requestData['schedulingTeamId'] == $schedulingTeamsArr['schedulingTeamId'])
							{
								$selectedTeams	=	'selected="selected"';
							}
					?>
					<option value="<?php echo $schedulingTeamsArr['schedulingTeamId']; ?>" <?php echo $selectedTeams; ?>><?php echo $schedulingTeamsArr['schedulingTeamName']; ?></option>
					<?php
						}
					?>
				</select>
				<select id="records" class="wid-120" name="records">
					<option value="1" <?php if($requestData['records'] == "1"){echo 'selected="selected"';} ?>>All Records</option>
					<option value="2" <?php if($requestData['records'] == "2"){echo 'selected="selected"';} ?>>On Hold Records</option>
					<option value="3" <?php if($requestData['records'] == "3"){echo 'selected="selected"';} ?>>Not-On Hold Records</option>
				</select>
				<input type="hidden" id="conrollerName" name="conrollerName" value="getIncorrectRecord" >
				<input type="hidden" id="filterType" name="filterType" value="1" >
				<input type="hidden" id="recordOffset" name="recordOffset" value="<?php echo ($requestData['recordOffset'] > 0) ? $requestData['recordOffset'] : 0 ?>" >
			</form>
			</div>
		</div>
		<div class="recordFilter recordFilterBtn">
			<div class="recordFilterBox">
				<input name="filterRecords" id="filterRecords" type="button" value="Filter" onclick="$('#recordOffset').val(0); getIncorrectRecord(true);">
				<input name="excludeRecords" id="excludeRecords" type="button" value="Exclude" onclick=" document.getElementById('filterType').value = 0; $('#recordOffset').val(0); getIncorrectRecord(true);" <?php if($requestData['filterType'] == "0"){echo 'style="background-color:#ccc"';} ?>>
				<input name="clearRecords" id="clearRecords" type="button" value="Clear" onclick="clearIncorrectRecordsFilter();">
				<div style="display: inline-flex;float: right;left: 250%; position: absolute; width:300%">
					<div>Page No. <?php echo $requestData['recordOffset'] + 1; ?>&nbsp;&nbsp;</div>
					<input type="button" onClick="$('#recordOffset').val(parseInt($('#recordOffset').val()) - 1);getIncorrectRecord(true);" <?php if($requestData['recordOffset'] == 0){echo 'disabled';} ?> value="&lt;&lt;" > 
					<input type="button" onClick="$('#recordOffset').val(parseInt($('#recordOffset').val()) + 1);getIncorrectRecord(true);" <?php if(count($data[0]) < 100){echo 'disabled';} ?> value="&gt;&gt;" >
				</div>
			</div>
		</div>
	</div>
</div>
<div class="teamPayRecordSection" id="incorrectRecordContainer" onScroll="saveScrollPosForIncorrectRecord();" >
<form name="mainForm" id="mainForm" onSubmit="return false;" >
<table id="incorrectRecordEntries" class="oddevenclass tablesmall" style="width:100%">
	<thead>
		<tr>
			<th>
				<input type="checkbox" id="CheckAllRecord" class="checkBoxInput" value=""/>
			</th>
			<th>TP ID</th>
			<th>A7 ID</th>
			<th>Staff Number</th>
			<th>Full Name</th>
			<th>Scheduling Team</th>
            <th>A7_Network Login</th>
            <th>TP_Network Login</th>

			<th>Effective From</th>
			<th>A7_End Date</th>
			<th>TP_End Date</th>
			<th>A7_Job Title</th>
			<th>TP_Job Title</th>
			<th>A7_Grade</th>
			<th>TP_Grade</th>
			<th>A7_Emp Type</th>
			<th>TP_Emp Type</th>
			<th>A7_Work Pattern</th>
			<th>TP_Work Pattern</th>
			<th>A7_EFT</th>
			<th>TP_EFT</th>
			<th>A7_EDP Min (Exc)</th>
			<th>TP_EDP Min (Exc)</th>
			<th>A7_Contract Hours</th>
			<th>TP_Contract Hours</th>
			<th>A7_Cost Code</th>
			<th>TP_Cost Code</th>
			<th>A7_Acc Group</th>
			<th>TP_Acc Group</th>
			<th>A7_Manual EDP</th>
			<th>TP_Manual EDP</th>
			<th>A7_PaymentType</th>
			<th>TP_PaymentType</th>
			<th>Comments</th>
		</tr>
	</thead>
	<tbody>
	<div id="incorrectRecordPos"></div>
	<?php 
		$teampayEmpNumberPrev	=	'';
		foreach($data[0] as $dataVal)
		{
			
			if(!empty($teampayEmpNumberPrev) && $teampayEmpNumberPrev != $dataVal['TeampayEmpNumber'])
			{
				$rowSeperatorClass	=	'rowSeperator';
			}else
			{
				$rowSeperatorClass	=	'';
			}
			$rowDisabledCount	=	0;
			$disabledStr	=	'';
			$jobStyle		= '';
			$tdStyle		=	'style="background-color: bisque;"';
			if($dataVal['A7_JobTitle'] !=$dataVal['JobTitle'])
			{
				$jobStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$grdStyle		= '';
			if($dataVal['A7_Grade'] !=$dataVal['Grade'])
			{
				$grdStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$empStyle		= '';
			if($dataVal['A7_EmpGroupDescription'] != $dataVal['EmpGroup'])
			{
				$empStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$empSubGrpStyle		= '';
			if($dataVal['A7_EmpSubGroupDescription'] != $dataVal['EmpSubGroup'])
			{
				$empSubGrpStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$eftStyle		= '';
			if($dataVal['A7_EFT'] != $dataVal['EFT'])
			{
				$eftStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$edpMinStyle	= '';
			if($dataVal['A7_EDPMinimumExcBreaks'] != $dataVal['EDPMinimumExcBreaks'])
			{
				$edpMinStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$partTimeEdpStyle	= '';
			if($dataVal['A7_PartTimeEDP'] != $dataVal['PartTimeEDP'])
			{
				$partTimeEdpStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$costCodeStyle	= '';
			if($dataVal['A7_CostCode'] != $dataVal['CostCode'])
			{
				$costCodeStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$accGrpStyle	= '';
			if($dataVal['A7_AccGroup'] != $dataVal['AccGroup'])
			{
				$accGrpStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$manualEdpStyle	= '';
			if($dataVal['A7_ManualEDP'] != $dataVal['ManualEDP'])
			{
				$manualEdpStyle	= $tdStyle;
				$rowDisabledCount++;
			}
			$paymentTypeStyle = '';
			if($dataVal['A7_PaymentType'] != $dataVal['TP_PaymentType'])
			{
				
				$paymentTypeStyle = $tdStyle;
				$rowDisabledCount++;
			}
           $networkLoginStyle = '';
           if($dataVal['A7_NetLogin'] != $dataVal['TP_NetLogin'])
           {
                          
                          $networkLoginStyle = $tdStyle;
                          $rowDisabledCount++;
           }
			$endDateStyle = '';
			if($dataVal['A7_EndDate'] != $dataVal['EndDate'])
			{
				$endDateStyle	= 'background-color: bisque;';
				$rowDisabledCount++;
			}
			if($dataVal['comments'] == 'Approved' || $rowDisabledCount == 0)
			{
				$disabledStr	=	'disabled';
			}
			$onHoldClass		=	'';
			if($dataVal['comments'] != 'Approved' && strlen((string) $dataVal['comments']) > 0)
			{
				$onHoldClass	=	'onHoldClass';
			}
			$dataVal['A7_EFT'] = number_format($dataVal['A7_EFT'], 3);
			$dataVal['EFT'] = number_format($dataVal['EFT'], 3);
			$dataVal['UserID'] = $dataVal['UserID'] ?? 0;
	?>
		<tr class="<?php echo $rowSeperatorClass. " $onHoldClass"; ?>" >
			<td>
				<input class="checkbox" type="checkbox" name="chkId[]" value="<?php echo $dataVal['SCP_ID']; ?>" />
			</td>
			<td><?php echo $dataVal['TeampayStaffID']; ?></td>
			<td><?php echo $dataVal['UserID']; ?></td>
			<td><?php echo $dataVal['TeampayStaffNumber']; ?></td>
			<td><?php echo $dataVal['PreferredForename'] ? $dataVal['PreferredForename'] . ' ' . $dataVal['Surname'] : $dataVal['Forename'] . ' ' . $dataVal['Surname']; ?></td>
			<td><?php echo $dataVal['schedulingTeamName']; ?></td>
            <td <?php echo $networkLoginStyle; ?>><?php echo $dataVal['A7_NetLogin']; ?></td>
            <td <?php echo $networkLoginStyle; ?>><?php echo $dataVal['TP_NetLogin']; ?></td>
			<td style="white-space: nowrap;"><?php echo date('d-m-Y', strtotime($dataVal['StartDate'])); ?></td>
			<td style="white-space: nowrap; <?php echo $endDateStyle; ?>"><?php echo date('d-m-Y', strtotime($dataVal['A7_EndDate'])); ?></td>
			<td style="white-space: nowrap; <?php echo $endDateStyle; ?>"><?php echo date('d-m-Y', strtotime($dataVal['EndDate'])); ?></td>
			<td <?php echo $jobStyle; ?> ><?php echo $dataVal['A7_JobTitle']; ?></td>
			<td <?php echo $jobStyle; ?> ><?php echo $dataVal['JobTitle']; ?></td>
			<td <?php echo $grdStyle; ?>><?php echo $dataVal['A7_Grade']; ?></td>
			<td <?php echo $grdStyle; ?>><?php echo $dataVal['Grade']; ?></td>
			<td <?php echo $empStyle; ?>><?php echo $dataVal['A7_EmpGroupDescription']; ?></td>
			<td <?php echo $empStyle; ?>><?php echo $dataVal['EmpGroup']; ?></td>
			<td <?php echo $empSubGrpStyle; ?>><?php echo $dataVal['A7_EmpSubGroupDescription']; ?></td>
			<td <?php echo $empSubGrpStyle; ?>><?php echo $dataVal['EmpSubGroup']; ?></td>
			<td <?php echo $eftStyle; ?>><?php echo ($dataVal['A7_EFT'] == 1) ? 1 : $dataVal['A7_EFT']; ?></td>
			<td <?php echo $eftStyle; ?>><?php echo ($dataVal['EFT'] == 1) ? 1 : $dataVal['EFT']; ?></td>
			<td <?php echo $edpMinStyle; ?>><?php echo $dataVal['A7_EDPMinimumExcBreaks']; ?></td>
			<td <?php echo $edpMinStyle; ?>><?php echo $dataVal['EDPMinimumExcBreaks']; ?></td>
			<td <?php echo $partTimeEdpStyle; ?>><?php echo $dataVal['A7_PartTimeEDP']; ?></td>
			<td <?php echo $partTimeEdpStyle; ?>><?php echo $dataVal['PartTimeEDP']; ?></td>
			<td <?php echo $costCodeStyle; ?>><?php echo $dataVal['A7_CostCode']; ?></td>
			<td <?php echo $costCodeStyle; ?>><?php echo $dataVal['CostCode']; ?></td>
			<td <?php echo $accGrpStyle; ?>><?php echo $dataVal['A7_AccGroup']; ?></td>
			<td <?php echo $accGrpStyle; ?>><?php echo $dataVal['AccGroup']; ?></td>
			<td <?php echo $manualEdpStyle; ?>><?php echo $dataVal['A7_ManualEDP'] ? 'Yes' : 'No'; ?></td>
			<td <?php echo $manualEdpStyle; ?>><?php echo $dataVal['ManualEDP'] ? 'Yes' : 'No'; ?></td>
			<td <?php echo $paymentTypeStyle; ?>><?php echo $dataVal['A7_PaymentType']; ?></td>
			<td <?php echo $paymentTypeStyle; ?>><?php echo $dataVal['TP_PaymentType']; ?></td>
			<td id="commentTxt_<?php echo $dataVal['SCP_ID']; ?>" ><?php echo $dataVal['comments']; ?></td>
		</tr>
		<?php 
			$teampayEmpNumberPrev	=	$dataVal['TeampayEmpNumber'];
		}
		?>
	</tbody>
</table>
<input type="hidden" id="conrollerName" name="conrollerName" value="teamPayIntegrationSave" >
<input type="hidden" id="requestFrom" name="requestFrom" value="ICR" >
<input type="hidden" id="requestType" name="requestType" value="ONHOLD" >
<input type="hidden" id="formName" name="formName" value="incorrectRecords" >
</form>
</div>
