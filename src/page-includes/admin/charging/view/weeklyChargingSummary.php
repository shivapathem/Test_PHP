<link href="../styles/charging/charging.css" rel="stylesheet">
<link href="../styles/charging/chargingReports.css" rel="stylesheet">
<title>Weekly Charging Summary</title>
<div id="content-weekly-charging-summary">
	<h1 class="headertextallpages  main-heading">Weekly Charging Summary</h1>
	<?php
	$requestData['reportType'] = $requestData['reportType'] ?? '';
	$requestData['chargeStatus'] = $requestData['chargeStatus'] ?? '';
	$requestData['sapDate'] = $requestData['sapDate'] ?? '';
	$requestData['actualStatus'] = $requestData['actualStatus'] ?? '';
	$requestData['weeksStart'] = $requestData['weeksStart'] ?? '';
	$requestData['toYear'] = $requestData['toYear'] ?? '';
	$requestData['weeksRange'] = $requestData['weeksRange'] ?? '';
	$requestData['weekFinish'] = $requestData['weekFinish'] ?? '';
	$requestData['fromYear'] = $requestData['fromYear'] ?? '';
	$requestData['estabCodes'] = $requestData['estabCodes'] ?? '';
	$requestData['receiverCode'] = $requestData['receiverCode'] ?? '';
	$requestData['wbsCodes'] = $requestData['wbsCodes'] ?? '';
	$requestData['groupBy2'] = $requestData['groupBy2'] ?? '';
	$requestData['staffNumber'] = $requestData['staffNumber'] ?? '';
	$estCode = $estCode ?? 0;
	$wbsCode = $wbsCode ?? 0;
	?>
	<form id="summaryFilter" name="summaryFilter" onSubmit="return false;">
		<div id="weeklyCharging" class="chargeRow">
			<div class="filterContainerFlex chargeCol10">
				<h2 class="heading">Charges Filter</h2>
				<div class="chargeRow">
					<div class="chargeCol chargeCol3">
						<div class="fields">
							<label class="labelTxtAlign" for="reportType">Report Type</label>
							<select id="reportType" name="reportType" class="weeklyChargingFrom" onChange="if(this.value == 'GRP'){ $('#grpBy1Container').css('display', 'flex'); $('#grpBy2Container').css('display', 'flex'); }else{ $('#grpBy1Container').hide(); $('#grpBy2Container').hide(); }">
								<option value="ALL" <?php if ($requestData['reportType'] == 'ALL') {
														echo 'selected';
													} ?>>Show All Records</option>
								<option value="GRP" <?php if ($requestData['reportType'] == 'GRP') {
														echo 'selected';
													} ?>>Group Records</option>
							</select>
						</div>

						<div class="fields">
							<label class="labelTxtAlign" for="chargeStatus">Charge Status</label>
							<select id="chargeStatus" name="chargeStatus" class="weeklyChargingFrom" onChange="if(this.value=='STSAP'){sapDate.disabled = false;}else{sapDate.value=''; sapDate.disabled = true;}">
								<option value="">All Status</option>
								<option value="STSAP" <?php if ($requestData['chargeStatus'] == 'STSAP') {
															echo 'selected';
														} ?>>Sent to Finance</option>
								<option value="NSTSAP" <?php if ($requestData['chargeStatus'] == 'NSTSAP') {
															echo 'selected';
														} ?>>Not sent to Finance</option>
							</select>
						</div>

						<div class="fields">
							<label class="labelTxtAlign" for="sapDate">Sent to Finance date</label>
							<select id="sapDate" name="sapDate" <?php if ($requestData['chargeStatus'] != 'STSAP') {
																	echo 'disabled';
																} ?>>
								<option value="">-- ALL Sent to Finance Date --</option>
								<?php
								foreach ($data[2]['data'] as $sendToSAPDate) {
									$selectedStr	=	'';
									if ($sendToSAPDate['SentToFinanceDate'] == $requestData['sapDate']) {
										$selectedStr	=	'selected';
									}
									echo '<option value="' . $sendToSAPDate['SentToFinanceDate'] . '" ' . $selectedStr . '>' . $sendToSAPDate['SentToFinanceDate'] . '</option>';
								}
								?>
							</select>
						</div>

						<div class="fields">
							<label class="labelTxtAlign" for="actualStatus">Actual Status</label>
							<select id="actualStatus" name="actualStatus" class="weeklyChargingFrom">
								<option value="">All</option>
								<option value="Y" <?php if ($requestData['actualStatus'] == 'Y') {
														echo 'selected';
													} ?>>Actual</option>
								<option value="N" <?php if ($requestData['actualStatus'] == 'N') {
														echo 'selected';
													} ?>>Provisional</option>
								<option value="H" <?php if ($requestData['actualStatus'] == 'H') {
														echo 'selected';
													} ?>>Hold</option>
							</select>
						</div>

						<div class="fields">
							<label class="labelTxtAlign" for="weeksRange">Weeks Range</label>
							<select id="weeksRange" name="weeksRange" class="weeklyChargingFrom" onChange="if(this.value=='SW'){weekFinish.disabled = true; fromYear.disabled = true; weeksStartLable.innerHTML = 'Week'; }else{weekFinish.disabled = false; fromYear.disabled = false; weeksStartLable.innerHTML = 'Weeks Start' }">
								<option value="SW" <?php if ($requestData['weeksRange'] == 'SW') {
														echo 'selected';
													} ?>>Single Week</option>
								<option value="RoW" <?php if ($requestData['weeksRange'] == 'RoW') {
														echo 'selected';
													} ?>>Range Of Weeks</option>
							</select>
						</div>
						<div class="fields displayFlex">
							<label class="labelTxtAlign" for="mappingFrom">Staff Number(s) Split with a comma','</label>
							<textarea class="splitCol" id="staffNumber" name="staffNumber" cols="50"><?php echo $requestData['staffNumber']; ?></textarea>
						</div>

					</div>

					<div class="chargeCol chargeCol4">


						<div class="fields chargeRow">
							<div class="weeksBlock chargeCol12">
								<div>
									<label class="labelTxtAlign" id="weeksStartLable" for="weeksStart"><?php if ($requestData['weeksRange'] == 'RoW') {
																											echo 'Weeks Start';
																										} else {
																											echo 'Week';
																										} ?></label>
									<select id="weeksStart" name="weeksStart" class="weekStatus">
										<?php
										for ($weekItr = 1; $weekItr < 53; $weekItr++) {
											$selectedStr	=	'';
											if ($weekItr == $requestData['weeksStart']) {
												$selectedStr	=	'selected';
											} elseif (($requestData['weeksStart'] <= 0) && (date('W') == $weekItr)) {
												$selectedStr	=	'selected';
											}
											$weekItrVal = ($weekItr < 9) ? "0" . $weekItr : $weekItr;
											echo '<option value="' . $weekItrVal . '" ' . $selectedStr . '>' . $weekItrVal . '</option>';
										}
										?>
									</select>
									<span class="slas">/</span>


									<select name="toYear" id="toYear" class="weekStatus-year">
										<?php
										for ($yearItr = 1995; $yearItr <= 2030; $yearItr++) {
											$selectedStr	=	'';
											if ($yearItr == $requestData['toYear']) {
												$selectedStr	=	'selected';
											} elseif (($requestData['toYear'] <= 0) && (date('Y') == $yearItr)) {
												$selectedStr	=	'selected';
											}
											echo '<option value="' . $yearItr . '" ' . $selectedStr . '>' . $yearItr . '</option>';
										}
										?>
									</select>
								</div>
								<div>
									<label class="labelTxtAlign" for="mappingFrom">Weeks Finish</label>
									<select id="weekFinish" name="weekFinish" class="weekStatus" <?php if ($requestData['weeksRange'] != 'RoW') {
																										echo 'disabled';
																									} ?>>
										<?php
										for ($weekItr = 1; $weekItr < 53; $weekItr++) {
											$selectedStr	=	'';
											if ($weekItr == $requestData['weekFinish']) {
												$selectedStr	=	'selected';
											} elseif (($requestData['weekFinish'] <= 0) && (date('W') == $weekItr)) {
												$selectedStr	=	'selected';
											}
											$weekItrVal = ($weekItr < 9) ? "0" . $weekItr : $weekItr;
											echo '<option value="' . $weekItrVal . '" ' . $selectedStr . '>' . $weekItrVal . '</option>';
										}
										?>
									</select>
									<span class="slas">/</span>

									<select name="fromYear" id="fromYear" class="weekStatus-year" <?php if ($requestData['weeksRange'] != 'RoW') {
																										echo 'disabled';
																									} ?>>
										<?php
										for ($yearItr = 1995; $yearItr <= 2030; $yearItr++) {
											$selectedStr	=	'';
											if ($yearItr == $requestData['fromYear']) {
												$selectedStr	=	'selected';
											} elseif (($requestData['fromYear'] <= 0) && (date('Y') == $yearItr)) {
												$selectedStr	=	'selected';
											}
											echo '<option value="' . $yearItr . '" ' . $selectedStr . '>' . $yearItr . '</option>';
										}
										?>
									</select>
								</div>
							</div>
						</div>

						<div class="fields displayFlex">
							<label class="labelTxtAlign" for="schedulingTeam">Scheduling Teams</label>
							<select id="schedulingTeam" name="schedulingTeam" class="weeklyChargingSelect1" data-placeholder="Select Scheduling Teams" multiple>
								<option value="-1" <?php if ($requestData['schedulingTeam'] == -1) {
														echo 'selected';
													} ?>>All My Teams</option>
								<?php
								$schedulingTeamArr = explode(',', $requestData['schedulingTeam']);
								$estabCodesArr = explode(',', $requestData['estabCodes']);
								$unqArrChk = [];
								foreach ($data[0] as $teamDetails) {
									$selectedStrTeam	=	'';
									if (in_array($teamDetails['id'], $schedulingTeamArr)) {
										$selectedStrTeam	=	'selected';
									}
									echo '<option value="' . $teamDetails['id'] . '" ' . $selectedStrTeam . '>' . $teamDetails['name'] . '</option>';
									
									if (!empty($teamDetails['EstablishCode']) && !in_array($teamDetails['EstablishCode'], $unqArrChk)) {
										$unqArrChk[]  = $teamDetails['EstablishCode'];
										$selectedStrEst	=	'';
										if (in_array($teamDetails['EstablishCode'], $estabCodesArr)) {
											$selectedStrEst	=	'selected';
										}
										$estCode .= '<option value="' . $teamDetails['EstablishCode'] . '" ' . $selectedStrEst . '>' . $teamDetails['EstablishCode'] . '</option>';
									}
								}
								?>
							</select>
						</div>
						<div class="fields displayFlex" id="grpBy1Container">
							<label class="labelTxtAlign" for="groupBy1"> Group By 1</label>
							<select id="groupBy1" name="groupBy1" data-placeholder="Select Team">
								<option value="Team">Scheduling Teams</option>
							</select>
						</div>
					</div>

					<div class="chargeCol chargeCol3">

						<div class="fields displayFlex">
							<label class="labelTxtAlign" for="estabCodes">&nbsp;Charge Codes</label>
							<select id="estabCodes" name="estabCodes" class="weeklyChargingSelect" data-placeholder="Select Charge Codes" multiple>
								<option value="" <?php if (empty($requestData['estabCodes'])) {
														echo 'selected';
													} ?>>All Charge Codes</option>
								<?php
								echo $estCode;
								?>
							</select>
						</div>

						<div class="fields displayFlex">
							<label class="labelTxtAlign" for="chargefrom">&nbsp;Charge From : </label>
						</div>

						<div class="fields displayFlex">
							<label class="labelTxtAlign" for="receiverCode" style="white-space: nowrap;">&nbsp;Charge Codes</label>
							<select id="receiverCode" name="receiverCode" class="weeklyChargingSelect" data-placeholder="Charge Codes" multiple>
								<option value="" <?php if (empty($requestData['receiverCode'])) {
														echo 'selected';
													} ?>>All Charge Codes</option>
								<?php
								$receiverCodeArr = explode(',', $requestData['receiverCode']);
								$wbsCodesArr = explode(',', $requestData['wbsCodes']);
								foreach ($data[1]['data'] as $wbsChargeCode) {
									if ($wbsChargeCode['CodeType'] == 0) {
										$selectedStrChr	=	'';
										if (in_array($wbsChargeCode['ChargeWbsCodeId'], $receiverCodeArr)) {
											$selectedStrChr	=	'selected';
										}
										echo '<option value="' . $wbsChargeCode['ChargeWbsCodeId'] . '" ' . $selectedStrChr . '>' . $wbsChargeCode['ChargeWbsCodeName'] . '</option>';
									} else {
										$selectedStrWbs	=	'';
										if (in_array($wbsChargeCode['ChargeWbsCodeId'], $wbsCodesArr)) {
											$selectedStrWbs	=	'selected';
										}
										$wbsCode .= '<option value="' . $wbsChargeCode['ChargeWbsCodeId'] . '" ' . $selectedStrWbs . '>' . $wbsChargeCode['ChargeWbsCodeName'] . '</option>';
									}
								}
								?>
							</select>
						</div>
						<div class="fields displayFlex">
							<label class="labelTxtAlign" for="wbsCodes">&nbsp;WBS Codes</label>
							<select id="wbsCodes" name="wbsCodes" class="weeklyChargingSelect" data-placeholder="WBS Codes" multiple>
								<option value="" <?php if (empty($requestData['wbsCodes'])) {
														echo 'selected';
													} ?>>All WBS Codes</option>
								<?php echo $wbsCode; ?>
							</select>
						</div>

						<div class="fields displayFlex" id="grpBy2Container">
							<label class="labelTxtAlign" for="groupBy2"> Group By 2</label>
							<select id="groupBy2" name="groupBy2">
								<option value="0">Nothing</option>
								<option value="Name" <?php if ($requestData['groupBy2'] == 'Name') {
															echo 'selected';
														} ?>>Name</option>
								<option value="Estab" <?php if ($requestData['groupBy2'] == 'Estab') {
															echo 'selected';
														} ?>>Charge Code</option>
								<option value="Activity" <?php if ($requestData['groupBy2'] == 'Activity') {
																echo 'selected';
															} ?>>Activity Type</option>
							</select>
						</div>
					</div>
					<div class="chargeCol chargeCol2 chargeBtnBlock">
						<button type="text" class="charging-btn" onClick="applyFilter();">Refresh</button>
						<button type="text" class="charging-btn" onClick="createDocs();" <?php if ($requestData['disableExport']) {
																								echo 'disabled';
																							} ?>><img src="./images/excel.svg"
								class="exportIcon">Export</button>
					</div>
				</div>
			</div>
			<input type="hidden" id="conrollerName" name="conrollerName" value="weeklyChargingSummary">
	</form>
	<div class="chargeCol2 infoBox">
		<div class="filterContainerFlex">
			<h3 class="heading">Key</h3>
			<div class="keyBlock">
				<div class="keytext">Not Sent to Finance</div>
				<div class="keyBox1"></div>
			</div>

			<div class="keyBlock">
				<div class="keytext" style="text-align : left;">Sent to Finance</div>
				<div class="keyBox2"></div>
			</div>
		</div>
		<p>You may click on the column headings to sort by that column. The spreadsheet will be produced with
			the selected order.</p>
	</div>

	<div class="chargeCol chargeCol12">
		<div class="tables weeklyChangingTable">
			<table id="weeklyChargingEntries" class="oddevenclass tablesmall reportTable" style="width:100%">
				<thead>
					<tr><!-- We used &nbsp; in th to increase the width to avaoid css overriding -->
						<th>Scheduling Team&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Charge Code&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Activity&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Name&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Staff Number&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Week&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Day&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Date&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Duty Name&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Comments&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Charge From&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Actual&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Actualised Date&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Qty&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Unit Price&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Total Price&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Created By&nbsp;&nbsp;&nbsp;&nbsp;</th>
						<th>Sent to Finance Date&nbsp;&nbsp;&nbsp;&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($data[3])) {
						foreach ($data[3]['data'] as $summaryReportVal) {
							$rowClassName = ($summaryReportVal['IsSentToFinance'] ?? 0) == 1 ? 'keyBoxYellow' : 'keyBoxBlue';
					?>
							<tr class="<?php echo $rowClassName; ?>">
								<td><?php echo $summaryReportVal['schedulingTeamName'] ?? ''; ?></td>
								<td><?php echo $summaryReportVal['EstablishCode'] ?? ''; ?></td>
								<td><?php echo $summaryReportVal['ActivityCodeName'] ?? ''; ?></td>
								<td><?php echo $summaryReportVal['DisplayName'] ?? ''; ?></td>
								<td><?php //echo $summaryReportVal['StaffNumber'] ?? ''; ?></td>
								<td><?php echo $summaryReportVal['ixWeekInYear'] ?? ''; ?></td>
								<td><?php echo $summaryReportVal['sDayName'] ?? ''; ?></td>
								<td><?php echo ($summaryReportVal['ChargingDutyDate'] ?? null) ? date('d/m/Y', strtotime($summaryReportVal['ChargingDutyDate'])) : 'NA'; ?></td>
								<td><?php echo $summaryReportVal['DutyName'] ?? ''; ?></td>
								<td><?php echo $summaryReportVal['Comments'] ?? ''; ?></td>
								<td><?php echo $summaryReportVal['ChargeCode'] ?? ''; ?></td>
								<td><?php $isActual = $summaryReportVal['IsActual'] ?? null;
									if ($isActual == 2) {
										echo 'Hold';
									} elseif ($isActual == 1) {
										echo 'Actual';
									} elseif ($isActual == 0) {
										echo 'Provisional';
									} else {
										echo '';
									} ?></td>
								<td><?php $modifiedDate = $summaryReportVal['ModifiedDate'] ?? null;
									$createdDate = $summaryReportVal['CreatedDate'] ?? null;
									if ($modifiedDate) {
										echo date('d/m/Y', strtotime($modifiedDate));
									} elseif ($createdDate) {
										echo date('d/m/Y', strtotime($createdDate));
									} else {
										echo '';
									} ?></td>
								<td><?php echo (float)$summaryReportVal['Quantity']; ?></td>
								<td><?php echo isset($summaryReportVal['UnitPrice']) ? number_format($summaryReportVal['UnitPrice'], 2, '.', '') : 'NA'; ?></td>
								<td><?php echo number_format($summaryReportVal['TotalPrice'], 2, '.', ''); ?></td>
								<td><?php echo $summaryReportVal['CreatedBy'] ?? ''; ?></td>
								<td><?php echo isset($summaryReportVal['SentToFinanceDate']) ? date('d/m/Y h:i:s', strtotime($summaryReportVal['SentToFinanceDate'])) : ''; ?></td>
							</tr>
					<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
</div>
<script>
	<?php
	if ($requestData['reportType'] == 'GRP') {
		echo "$('#grpBy1Container').css('display', 'flex'); $('#grpBy2Container').css('display', 'flex');";
	} else {
		echo "$('#grpBy1Container').hide(); $('#grpBy2Container').hide();";
	}
	?>
	var table;
	$(document).ready(function() {

		$(".weeklyChargingSelect").css('height', '50px');
		$(".weeklyChargingSelect1").css('height', '135px');
		jQuery.fn.dataTable.ext.type.order['date-custom-pre'] = function(date) {
			const parts = date.split('/');
			return (parts[2] + parts[1] + parts[0]);
		};
		table = $('#weeklyChargingEntries').DataTable({
			"paging": false,
			"lengthChange": false,
			"bInfo": false,
			"bFilter": false,
			"scrollY": 200,
			"columnDefs": [{
				"type": 'date-custom',
				"targets": 7,
				"orderable": true,
			}]

		});

	});

	function applyFilter() {
		let reportType = $('#reportType').val();
		let chargeStatus = $('#chargeStatus').val();
		let sapDate = $('#sapDate').val();
		let actualStatus = $('#actualStatus').val();
		let weeksRange = $('#weeksRange').val();
		let weeksStart = $('#weeksStart').val();
		let toYear = $('#toYear').val();
		let weekFinish = $('#weekFinish').val();
		let fromYear = $('#fromYear').val();
		let staffNumber = $('#staffNumber').val();
		let groupBy1 = $('#groupBy1').val();
		let groupBy2 = $('#groupBy2').val();
		let schedulingTeam = ($('#schedulingTeam').val()) ? $('#schedulingTeam').val().toString() : '';
		let estabCodes = ($('#estabCodes').val()) ? $('#estabCodes').val().toString() : '';
		let receiverCode = ($('#receiverCode').val()) ? $('#receiverCode').val().toString() : '';
		let wbsCodes = ($('#wbsCodes').val()) ? $('#wbsCodes').val().toString() : '';
		$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: {
				conrollerName: 'weeklyChargingSummary',
				reportType: reportType,
				chargeStatus: chargeStatus,
				sapDate: sapDate,
				actualStatus: actualStatus,
				weeksRange: weeksRange,
				weeksStart: weeksStart,
				toYear: toYear,
				weekFinish: weekFinish,
				fromYear: fromYear,
				schedulingTeam: schedulingTeam,
				estabCodes: estabCodes,
				receiverCode: receiverCode,
				wbsCodes: wbsCodes,
				staffNumber: staffNumber,
				groupBy1: groupBy1,
				groupBy2: groupBy2
			},
			success: function(returnHtml) {
				if ($($.parseHTML(returnHtml)).filter("#content-weekly-charging-summary").length) {
					$('#content-weekly-charging-summary').html(returnHtml);
				} else {
					customAlert(returnHtml);
				}
			}
		});
	}

	function createDocs() {
		let reportType = $('#reportType').val();
		let chargeStatus = $('#chargeStatus').val();
		let sapDate = $('#sapDate').val();
		let actualStatus = $('#actualStatus').val();
		let weeksRange = $('#weeksRange').val();
		let weeksStart = $('#weeksStart').val();
		let toYear = $('#toYear').val();
		let weekFinish = $('#weekFinish').val();
		let fromYear = $('#fromYear').val();
		let staffNumber = $('#staffNumber').val();
		let groupBy1 = $('#groupBy1').val();
		let groupBy2 = $('#groupBy2').val();
		let schedulingTeam = ($('#schedulingTeam').val()) ? $('#schedulingTeam').val().toString() : '';
		let estabCodes = ($('#estabCodes').val()) ? $('#estabCodes').val().toString() : '';
		let receiverCode = ($('#receiverCode').val()) ? $('#receiverCode').val().toString() : '';
		let wbsCodes = ($('#wbsCodes').val()) ? $('#wbsCodes').val().toString() : '';
		location = 'page-includes/admin/charging/weeklyChargingSummaryExport.php?reportType=' + reportType + '&chargeStatus=' + chargeStatus + '&sapDate=' + sapDate + '&actualStatus=' + actualStatus + '&weeksRange=' + weeksRange + '&weeksStart=' + weeksStart + '&toYear=' + toYear + '&weekFinish=' + weekFinish + '&fromYear=' + fromYear + '&staffNumber=' + staffNumber + '&schedulingTeam=' + schedulingTeam + '&estabCodes=' + estabCodes + '&receiverCode=' + receiverCode + '&wbsCodes=' + wbsCodes + '&groupBy1=' + groupBy1 + '&groupBy2=' + groupBy2 + '&orderBy=' + btoa(table.order());
	}

	function chargingCallbackFunc(data) {
		if ($($.parseHTML(data)).filter("#content-weekly-charging-summary").length) {
			$('#content-weekly-charging-summary').html(data);
		} else {
			customAlert(data);
		}
	}
</script>
<style>
	.labelTxtAlign {
		text-align: left !important;
	}
</style>