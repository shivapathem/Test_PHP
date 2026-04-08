	<div class="teamPayFilterBlock">
		<div class="grid box1">
			<div class="recordFilter recordFilter1">
				<h2 class="heading">Record Filter</h2>
				<div class="recordFilterBox">
				<form name="missingRecordFilter" id="missingRecordFilter" onSubmit="return false;" >
					<?php
						$requestData['name'] = $requestData['name'] ?? '';
						$requestData['empNumber'] = $requestData['empNumber'] ?? 0;
						$requestData['estCode'] = $requestData['estCode'] ?? 0;
					?>
					<input type="text" class="wid-120" placeholder="Name" name="name" id="name" value="<?php echo $requestData['name']; ?>">
					<input type="text" class="wid-120" placeholder="Emp Number" name="empNumber" id="empNumber" value="<?php echo $requestData['empNumber']; ?>">
					<input type="text" class="wid-120" placeholder="Est Code" name="estCode" id="estCode" value="<?php echo $requestData['estCode']; ?>">
					<!--select id="schedulingTeamId" class="wid-120" name="schedulingTeamId">
						<option value=''>Select Team</option>
						<?php
							/*foreach($data[1] as $schedulingTeamsArr)
							{
								$selectedTeams	=	'';
								if($requestData['schedulingTeamId'] == $schedulingTeamsArr['schedulingTeamId'])
								{
									$selectedTeams	=	'selected="selected"';
								}
							*/
						?>
						<option value="<?php //echo $schedulingTeamsArr['schedulingTeamId']; ?>" <?php //echo $selectedTeams; ?>><?php //echo $schedulingTeamsArr['schedulingTeamName']; ?></option>
						<?php
							//}
						?>
					</select-->
					<select id="records" class="wid-120" name="records">
						<option value="1" <?php if($requestData['records'] == "1"){echo 'selected="selected"';} ?>>All Records</option>
						<option value="2" <?php if($requestData['records'] == "2"){echo 'selected="selected"';} ?>>On Hold Records</option>
						<option value="3" <?php if($requestData['records'] == "3"){echo 'selected="selected"';} ?>>Not-On Hold Records</option>
					</select>
					<input type="hidden" id="conrollerName" name="conrollerName" value="getMissingRecord" >
					<input type="hidden" id="filterType" name="filterType" value="1" >
					<input type="hidden" id="recordOffset" name="recordOffset" value="<?php echo ($requestData['recordOffset'] > 0) ? $requestData['recordOffset'] : 0 ?>" >
				</form>
				</div>
			</div>
			<div class="recordFilter recordFilterBtn">
				<div class="recordFilterBox">
					<?php
						$requestData['filterType'] = $requestData['filterType'] ?? '';
					?>
					<input name="filterRecords" id="filterRecords" type="button" value="Filter" onclick="$('#recordOffset').val(0); getMissingRecord(true);">
					<input name="excludeRecords" id="excludeRecords" type="button" value="Exclude" onclick=" document.getElementById('filterType').value = 0; $('#recordOffset').val(0); getMissingRecord(true);" <?php if($requestData['filterType'] == "0"){echo 'style="background-color:#ccc"';} ?>>
					<input name="clearRecords" id="clearRecords" type="button" value="Clear" onclick="clearMissingRecordsFilter()">
				</div>
				<div style="display: inline-flex;float: right;left: 280%; position: absolute; width:300%">
					<div>Page No. <?php echo $requestData['recordOffset'] + 1; ?> &nbsp;&nbsp; </div>
					<input type="button" onClick="$('#recordOffset').val(parseInt($('#recordOffset').val()) - 1);getMissingRecord(true);" <?php if($requestData['recordOffset'] == 0){echo 'disabled';} ?> value="&lt;&lt;"> 
					<input type="button" onClick="$('#recordOffset').val(parseInt($('#recordOffset').val()) + 1);getMissingRecord(true);" <?php if(count($data[0]) < 100){echo 'disabled';} ?> value="&gt;&gt;">
				</div>
			</div>
		</div>
	</div>
<div class="teamPayRecordSection" id="missingRecordContainer" onScroll="saveScrollPosForMissingRecord();" >
<form name="mainForm" id="mainForm" onSubmit="return false;" >	
	<table id="missingRecordEntries" class="oddevenclass tablesmall" style="width:100%">
		<thead>
			<tr>
				<th>
					<input type="checkbox" id="CheckAllRecord" class="checkBoxInput" value=""/>
				</th>
				<th>TP ID</th>
				<th>A7 ID</th>
				<th>Staff Number</th>
				<th>Full Name</th>
				<th>Effective From</th>
				<th>End Date</th>
				<th>Job Title</th>
				<th>Grade</th>
				<th>Emp Type</th>
				<th>Work Pattern</th>
                <th>Payment Type</th>
				<th>EFT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
				<th>EDP Min (Exc)</th>
				<th>Contract Hours</th>
				<th>Cost Code</th>
				<th>Acc Group</th>
				<th>Manual EDP</th>
				<th>Comments</th>
			</tr>
		</thead>
		<tbody>
		<div id="missingRecordPos"></div>
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
				$disabledStr	=	'';
				if( ($dataVal['comments'] == 'Approved') || ($dataVal['isNewData'] != 1) )
				{
					$disabledStr	=	'disabled';
				}
				$rowStyle = '';
				if($dataVal['isNewData'] == 1)
				{
					$rowStyle 		=	' style="background-color:#ADD8E6"';
				}
				if( ($dataVal['isNewData'] == 1) && ($dataVal['UserID'] == 0) )
				{
					$rowStyle 		=	' style="background-color:#f5d0d0"';
				}
				if( ($dataVal['isNewData'] == 1) && ($dataVal['UserID'] == 0) && strlen((string)$dataVal['comments']) > 0 )
				{
					$rowStyle 		=	' style="background:repeating-linear-gradient(45deg, #ffffb3 10%, #fdcee4 15%);"';
				}
				$dataVal['EFT'] = number_format($dataVal['EFT'], 3);
				$onHoldClass		=	'';
				if($dataVal['comments'] != 'Approved' && strlen((string)$dataVal['comments']) > 0)
				{
					$onHoldClass	=	'onHoldClass';
				}
		?>
			<tr class="<?php echo $rowSeperatorClass. " $onHoldClass"; ?>" <?php echo $rowStyle; ?>>
				<td>
					<input class="checkbox" type="checkbox" name="chkId[]" value="<?php echo $dataVal['SCP_ID']; ?>" <?php echo $disabledStr; ?> />
				</td>
				<td><?php echo $dataVal['TeampayStaffID']; ?></td>
				<td><?php echo $dataVal['UserID']; ?></td>
				<td><?php echo $dataVal['TeampayStaffNumber']; ?></td>
				<td><?php echo $dataVal['PreferredForename'] ? $dataVal['PreferredForename'] . ' ' . $dataVal['Surname'] : $dataVal['Forename'] . ' ' . $dataVal['Surname']; ?></td>
				<td style="white-space: nowrap;"><?php echo date('d-m-Y', strtotime($dataVal['StartDate'])); ?></td>
				<td style="white-space: nowrap;"><?php echo date('d-m-Y', strtotime($dataVal['EndDate'])); ?></td>
				<td><?php echo $dataVal['JobTitle']; ?></td>
				<td><?php echo $dataVal['Grade']; ?></td>
				<td><?php echo $dataVal['EmpGroup']; ?></td>
				<td><?php echo $dataVal['EmpSubGroup']; ?></td>
                <td><?php echo $dataVal['PaymentTypeName']; ?></td>
				<td><?php echo ($dataVal['EFT'] == 1) ? 1 : $dataVal['EFT']; ?></td>
				<td><?php echo $dataVal['EDPMinimumExcBreaks']; ?></td>
				<td><?php echo $dataVal['PartTimeEDP']; ?></td>
				<td><?php echo $dataVal['CostCode']; ?></td>
				<td><?php echo $dataVal['AccGroup']; ?></td>
				<td><?php echo $dataVal['ManualEDP'] ? 'Yes' : 'No'; ?></td>
				<td id="commentTxt_<?php echo $dataVal['SCP_ID']; ?>" ><?php echo $dataVal['comments']; ?></td>
			</tr>
			<?php 
				$teampayEmpNumberPrev	=	$dataVal['TeampayEmpNumber'];
			}
			?>
		</tbody>
	</table>
	<input type="hidden" id="conrollerName" name="conrollerName" value="teamPayIntegrationSave" >
<input type="hidden" id="requestFrom" name="requestFrom" value="MR" >
<input type="hidden" id="requestType" name="requestType" value="ONHOLD" >
	<input type="hidden" id="formName" name="formName" value="missingRecords" >
</form>
</div>
