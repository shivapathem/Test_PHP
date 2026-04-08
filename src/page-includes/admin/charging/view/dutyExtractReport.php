<link href="../styles/charging/charging.css" rel="stylesheet">
<link href="../styles/charging/chargingReports.css" rel="stylesheet">
<title>Duty Extract Report</title>
<div id="content-duty-extract-report">
	<h1 class="headertextallpages  main-heading">Duty Extract Report</h1>
	<div id="dutyExtractReport" class="chargeRow">
		<div class="filterContainerFlex chargeCol10">
			<h2 class="heading">Selection criteria for weeks Extract</h2>
			<div class="chargeRow">
				<div class="chargeCol chargeCol5">
					<div class="fields displayFlex">
						<label for="schedulingTeam">Scheduling Teams</label>
						<select id="schedulingTeam" name="schedulingTeam" class="dutyExtractSelect" data-placeholder="Select Scheduling Teams" onChange="updateFilterOption(this.value);">
						<option value="-1" >All My Teams</option>
						<?php
							echo $data[2];
						?>
						</select>
					</div>
					<div class="fields">
						<?php 
							$iDayTxtArr	=	array('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'); 
							$requestData['weekFrom'] = $requestData['weekFrom'] ?? null;
							$requestData['weekTo'] = $requestData['weekTo'] ?? null;
							$requestData['ExtractDays'] = $requestData['ExtractDays'] ?? null;
							$requestData['includeLeave'] = $requestData['includeLeave'] ?? null;
							$requestData['reportType'] = $requestData['reportType'] ?? null;
							$requestData['extractGroup1'] = $requestData['extractGroup1'] ?? null;
							$requestData['extractGroup2'] = $requestData['extractGroup2'] ?? null;
							$requestData['staffNumber'] = $requestData['staffNumber'] ?? null;
							$requestData['staffNumberVal'] = $requestData['staffNumberVal'] ?? null;
							$requestData['extractJobProgramme'] = $requestData['extractJobProgramme'] ?? null;
							$requestData['extractJob'] = $requestData['extractJob'] ?? null;
							$requestData['extractJobProgramme'] = $requestData['extractJobProgramme'] ?? null;
							$requestData['extractContact'] = $requestData['extractContact'] ?? null;
							$requestData['extractJobProgrammeVal'] = $requestData['extractJobProgrammeVal'] ?? null;
							$requestData['extractContactVal'] = $requestData['extractContactVal'] ?? null;
							$requestData['extractLocation'] = $requestData['extractLocation'] ?? null;
							$requestData['extractLocationVal'] = $requestData['extractLocationVal'] ?? null;
							$requestData['extractDutyVal'] = $requestData['extractDutyVal'] ?? null;
							$requestData['extractProgramme'] = $requestData['extractProgramme'] ?? null;
							$requestData['extractFilterBy'] = $requestData['extractFilterBy'] ?? null;
							$requestData['extractFilterByCond'] = $requestData['extractFilterByCond'] ?? null;
							$requestData['extractFilterByCondVal'] = $requestData['extractFilterByCondVal'] ?? null;
							$requestData['extractHistoryData'] = $requestData['extractHistoryData'] ?? null;
							$requestData['extractDuty'] = $requestData['extractDuty'] ?? null;
							$requestData['extractJobVal'] = $requestData['extractJobVal'] ?? null;
							$requestData['extractProgrammeVal'] = $requestData['extractProgrammeVal'] ?? null;
							$requestData['extractFilter'] = $requestData['extractFilter'] ?? null;
						?>
						<label for="weeksFromTo"> Weeks : From</label>
						<input type="text" name="wFrom" id="wFrom" style="width:10%" value="<?php echo $requestData['weekFrom'] ? substr($requestData['weekFrom'],4,2) : date("W"); ?>" onKeyUP="weekFrom.value = YFrom.value + wFrom.value;" > /
						<input type="text" name="YFrom" id="YFrom" style="width:14%" value="<?php echo $requestData['weekFrom'] ? substr($requestData['weekFrom'],0,4) : date("Y"); ?>" onKeyUP="weekFrom.value = YFrom.value + wFrom.value;" >
						<input type="hidden" name="weekFrom" id="weekFrom" style="width:14%" value="<?php echo $requestData['weekFrom'] ? $requestData['weekFrom'] : date("YW"); ?>" >
						<span> To </span>
						<input type="text" name="wTo" id="wTo" style="width:10%" value="<?php echo $requestData['weekTo'] ? substr($requestData['weekTo'],4,2) : date("W"); ?>" onKeyUP="weekTo.value = YTo.value + wTo.value;" > /
						<input type="text" name="YTo" id="YTo" style="width:14%" value="<?php echo $requestData['weekTo'] ? substr($requestData['weekTo'],0,4) : date("Y"); ?>" onKeyUP="weekTo.value = YTo.value + wTo.value;" >
						<input type="hidden" name="weekTo" id="weekTo" style="width:14%" value="<?php echo $requestData['weekTo'] ? $requestData['weekTo'] : date("YW"); ?>" >
					</div>


					<div class="fields">
					    <label for="ExtractDays">Days</label>
					    <select id="ExtractDays" name="ExtractDays" multiple style="height: 50px;">
					        <option value="ALL" <?php if ($requestData['ExtractDays'] == 'ALL') echo 'selected'; ?>>All</option>
					        <?php
					        foreach ($iDayTxtArr as $iDayTxtArrKey => $iDayTxtArrVal) {
					            $selected = '';

					            if (isset($requestData['ExtractDays']) && is_array(explode(',',$requestData['ExtractDays']))) {
					                if (in_array($iDayTxtArrKey, explode(',',$requestData['ExtractDays']))) {
					                    $selected = 'selected';
					                }
					            } elseif (isset($requestData['ExtractDays']) && $requestData['ExtractDays'] == $iDayTxtArrKey) {
					                $selected = 'selected';
					            }

					            echo '<option value="' . $iDayTxtArrKey . '" ' . $selected . '>' . $iDayTxtArrVal . '</option>';
					        }
					        ?>
					    </select>
					</div>
					<div class="fields">
						<label for="extractFilter">Select Filter</label>
						<select id="extractFilter" name="extractFilter" >
							<option value="0">No Filter</option>
							<?php
								foreach($data[0] as $filterValArr)
								{
									if($requestData['extractFilter'] == $filterValArr['ID'])
									{
										echo '<option value="'.$filterValArr['ID'].'" selected>'.$filterValArr['Description'].'</option>';
									}else
									{
										echo '<option value="'.$filterValArr['ID'].'">'.$filterValArr['Description'].'</option>';
									}
								}
							?>
						</select>
						<!--span><button type="text" class="goBtn">Go</button></span-->
					</div>
					<div class="fields">
						<label for="includeLeave">Include Leave</label>
						<select id="includeLeave" name="includeLeave">
							<option value="0" <?php if($requestData['includeLeave'] == 0){ echo 'selected="selected"';} ?>>Include Leave</option>
							<option value="1" <?php if($requestData['includeLeave'] == 1){ echo 'selected="selected"';} ?>>Exclude Leave</option>
						</select>
					</div>
					<div class="fields">
						<label for="includeJobData">Include Job Data</label>
						<select id="includeJobData" name="includeJobData" onChange="validateJobsRelFields(this.value);" >
							<option value="0" <?php if(isset($requestData['includeJobData']) && $requestData['includeJobData'] == 0){ echo 'selected="selected"';} ?>>Include Job Data</option>
							<option value="1" <?php if(!isset($requestData['includeJobData']) || $requestData['includeJobData'] == 1){ echo 'selected="selected"';} ?>>Exclude Job Data</option>
						</select>
					</div>
					<div class="fields">
						<label for="reportType">Report Type</label>
						<select id="reportType" name="reportType" onChange="if(this.value == 0){ extractGroup1.disabled = true; extractGroup2.disabled = true; }else{ extractGroup1.disabled = false; }" >
							<option value="0" <?php if($requestData['reportType'] == 0){ echo 'selected="selected"';} ?>>Show All Record</option>
							<option value="1" <?php if($requestData['reportType'] == 1){ echo 'selected="selected"';} ?>>Group Record</option>
						</select>
					</div>
					<div class="fields">
						<label for="extractGroup1">Group By 1</label>
						<select id="extractGroup1" name="extractGroup1" onChange="$('.extGrp2').attr('disabled', false); document.getElementById('extGrp2-'+this.value).disabled = true; extractGroup2.disabled = false; if(this.value == extractGroup2.value){extractGroup2.disabled = true;}" <?php if(in_array($requestData['extractGroup1'], array(null , ''))){ echo 'disabled';} ?>>
							<option value="0" <?php if($requestData['extractGroup1'] == 0){ echo 'selected="selected"';} ?>>Nothing</option>
							<option value="1" <?php if($requestData['extractGroup1'] == 1){ echo 'selected="selected"';} ?>>Name</option>
							<option value="2" <?php if($requestData['extractGroup1'] == 2){ echo 'selected="selected"';} ?>>Staff Number</option>
							<option value="3" <?php if($requestData['extractGroup1'] == 3){ echo 'selected="selected"';} ?>>Sort code</option>
							<option value="4" <?php if($requestData['extractGroup1'] == 4){ echo 'selected="selected"';} ?>>Date</option>
							<option value="5" <?php if($requestData['extractGroup1'] == 5){ echo 'selected="selected"';} ?>>Week</option>
							<option value="6" <?php if($requestData['extractGroup1'] == 6){ echo 'selected="selected"';} ?>>Day</option>
							<option value="7" <?php if($requestData['extractGroup1'] == 7){ echo 'selected="selected"';} ?>>Duty Name</option>
						</select>
					</div>
					<div class="fields">
						<label for="extractGroup2">Group By 2</label>
						<select id="extractGroup2" name="extractGroup2" <?php if(in_array($requestData['extractGroup2'], array(null, ''))){ echo 'disabled';} ?>>
							<option class='extGrp2' id="extGrp2-0" value="0" <?php if($requestData['extractGroup2'] == 0){ echo 'selected="selected"';} ?>>Nothing</option>
							<option class='extGrp2' id="extGrp2-1" value="1" <?php if($requestData['extractGroup2'] == 1){ echo 'selected="selected"';} ?>>Name</option>
							<option class='extGrp2' id="extGrp2-2" value="2" <?php if($requestData['extractGroup2'] == 2){ echo 'selected="selected"';} ?>>Staff Number</option>
							<option class='extGrp2' id="extGrp2-3" value="3" <?php if($requestData['extractGroup2'] == 3){ echo 'selected="selected"';} ?>>Sort code</option>
							<option class='extGrp2' id="extGrp2-4" value="4" <?php if($requestData['extractGroup2'] == 4){ echo 'selected="selected"';} ?>>Date</option>
							<option class='extGrp2' id="extGrp2-5" value="5" <?php if($requestData['extractGroup2'] == 5){ echo 'selected="selected"';} ?>>Week</option>
							<option class='extGrp2' id="extGrp2-6" value="6" <?php if($requestData['extractGroup2'] == 6){ echo 'selected="selected"';} ?>>Day</option>
							<option class='extGrp2' id="extGrp2-7" value="7" <?php if($requestData['extractGroup2'] == 7){ echo 'selected="selected"';} ?>>Duty Name</option>
						</select>
					</div>	
				</div>

				<div class="chargeCol chargeCol7">
					<p class="extractSearchText">To Search for multiple items, use the 'In' option and separate with a comma ','</p>
					<div class="fields" class="displayFlex">
						<label for="staffNumber">Staff Number</label>
						<select id="staffNumber" name="staffNumber" class="wid85">
							<option value="0" <?php if($requestData['staffNumber'] == 0){ echo 'selected="selected"';} ?>>Wildcard</option>
							<option value="1" <?php if($requestData['staffNumber'] == 1){ echo 'selected="selected"';} ?>>Starts with</option>
							<option value="2" <?php if($requestData['staffNumber'] == 2){ echo 'selected="selected"';} ?>>Ends with</option>
							<option value="3" <?php if($requestData['staffNumber'] == 3){ echo 'selected="selected"';} ?>>Exact match</option>
							<option value="4" <?php if($requestData['staffNumber'] == 4){ echo 'selected="selected"';} ?>>Not equal to</option>
							<option value="5" <?php if($requestData['staffNumber'] == 5){ echo 'selected="selected"';} ?>>In</option>

						</select>

						<input type="text" id="staffNumberVal" name="staffNumberVal" class="searchMultiItem" value="<?php echo $requestData['staffNumberVal']; ?>" />

					</div>

					<div class="fields" class="displayFlex">
						<label for="extractJob">Job Name</label>
						<select id="extractJob" name="extractJob" class="wid85">
							<option value="0" <?php if($requestData['extractJob'] == 0){ echo 'selected="selected"';} ?>>Wildcard</option>
							<option value="1" <?php if($requestData['extractJob'] == 1){ echo 'selected="selected"';} ?>>Starts with</option>
							<option value="2" <?php if($requestData['extractJob'] == 2){ echo 'selected="selected"';} ?>>Ends with</option>
							<option value="3" <?php if($requestData['extractJob'] == 3){ echo 'selected="selected"';} ?>>Exact match</option>
							<option value="4" <?php if($requestData['extractJob'] == 4){ echo 'selected="selected"';} ?>>Not equal to</option>
							<option value="5" <?php if($requestData['extractJob'] == 5){ echo 'selected="selected"';} ?>>In</option>
						</select>

						<input type="text" id="extractJobVal" name="extractJobVal" class="searchMultiItem" value="<?php echo $requestData['extractJobVal']; ?>" />
					</div>

					<div class="fields" class="displayFlex">
						<label for="extractJobProgramme">Job Label</label>
						<select id="extractJobProgramme" name="extractJobProgramme" class="wid85">
							<option value="0" <?php if($requestData['extractJobProgramme'] == 0){ echo 'selected="selected"';} ?>>Wildcard</option>
							<option value="1" <?php if($requestData['extractJobProgramme'] == 1){ echo 'selected="selected"';} ?>>Starts with</option>
							<option value="2" <?php if($requestData['extractJobProgramme'] == 2){ echo 'selected="selected"';} ?>>Ends with</option>
							<option value="3" <?php if($requestData['extractJobProgramme'] == 3){ echo 'selected="selected"';} ?>>Exact match</option>
							<option value="4" <?php if($requestData['extractJobProgramme'] == 4){ echo 'selected="selected"';} ?>>Not equal to</option>
							<option value="5" <?php if($requestData['extractJobProgramme'] == 5){ echo 'selected="selected"';} ?>>In</option>
						</select>

						<input type="text" id="extractJobProgrammeVal" name="extractJobProgrammeVal" class="searchMultiItem" value="<?php echo $requestData['extractJobProgrammeVal']; ?>" />
					</div>


					<div class="fields" class="displayFlex">
						<label for="extractContact">Job Contact</label>
						<select id="extractContact" name="extractContact" class="wid85">
							<option value="0" <?php if($requestData['extractContact'] == 0){ echo 'selected="selected"';} ?>>Wildcard</option>
							<option value="1" <?php if($requestData['extractContact'] == 1){ echo 'selected="selected"';} ?>>Starts with</option>
							<option value="2" <?php if($requestData['extractContact'] == 2){ echo 'selected="selected"';} ?>>Ends with</option>
							<option value="3" <?php if($requestData['extractContact'] == 3){ echo 'selected="selected"';} ?>>Exact match</option>
							<option value="4" <?php if($requestData['extractContact'] == 4){ echo 'selected="selected"';} ?>>Not equal to</option>
							<option value="5" <?php if($requestData['extractContact'] == 5){ echo 'selected="selected"';} ?>>In</option>
						</select>

						<input type="text" id="extractContactVal" name="extractContactVal" class="searchMultiItem" value="<?php echo $requestData['extractContactVal']; ?>" />
					</div>
					
					<div class="fields" class="displayFlex">
						<label for="extractLocation">Job Location</label>
						<select id="extractLocation" name="extractLocation" class="wid85">
							<option value="0" <?php if($requestData['extractLocation'] == 0){ echo 'selected="selected"';} ?>>Wildcard</option>
							<option value="1" <?php if($requestData['extractLocation'] == 1){ echo 'selected="selected"';} ?>>Starts with</option>
							<option value="2" <?php if($requestData['extractLocation'] == 2){ echo 'selected="selected"';} ?>>Ends with</option>
							<option value="3" <?php if($requestData['extractLocation'] == 3){ echo 'selected="selected"';} ?>>Exact match</option>
							<option value="4" <?php if($requestData['extractLocation'] == 4){ echo 'selected="selected"';} ?>>Not equal to</option>
							<option value="5" <?php if($requestData['extractLocation'] == 5){ echo 'selected="selected"';} ?>>In</option>
						</select>

						<input type="text" id="extractLocationVal" name="extractLocationVal" class="searchMultiItem" value="<?php echo $requestData['extractLocationVal']; ?>" />
					</div>

					<div class="fields" class="displayFlex">
						<label for="extractDuty">Duty Name</label>
						<select id="extractDuty" name="extractDuty" class="wid85">
							<option value="0" <?php if($requestData['extractDuty'] == 0){ echo 'selected="selected"';} ?>>Wildcard</option>
							<option value="1" <?php if($requestData['extractDuty'] == 1){ echo 'selected="selected"';} ?>>Starts with</option>
							<option value="2" <?php if($requestData['extractDuty'] == 2){ echo 'selected="selected"';} ?>>Ends with</option>
							<option value="3" <?php if($requestData['extractDuty'] == 3){ echo 'selected="selected"';} ?>>Exact match</option>
							<option value="4" <?php if($requestData['extractDuty'] == 4){ echo 'selected="selected"';} ?>>Not equal to</option>
							<option value="5" <?php if($requestData['extractDuty'] == 5){ echo 'selected="selected"';} ?>>In</option>
						</select>

						<input type="text" id="extractDutyVal" name="extractDutyVal" class="searchMultiItem" value="<?php echo $requestData['extractDutyVal']; ?>" />
					</div>

					<div class="fields" class="displayFlex">
						<label for="extractProgramme">Duty Label</label>
						<select id="extractProgramme" name="extractProgramme" class="wid85">
							<option value="0" <?php if($requestData['extractProgramme'] == 0){ echo 'selected="selected"';} ?>>Wildcard</option>
							<option value="1" <?php if($requestData['extractProgramme'] == 1){ echo 'selected="selected"';} ?>>Starts with</option>
							<option value="2" <?php if($requestData['extractProgramme'] == 2){ echo 'selected="selected"';} ?>>Ends with</option>
							<option value="3" <?php if($requestData['extractProgramme'] == 3){ echo 'selected="selected"';} ?>>Exact match</option>
							<option value="4" <?php if($requestData['extractProgramme'] == 4){ echo 'selected="selected"';} ?>>Not equal to</option>
							<option value="5" <?php if($requestData['extractProgramme'] == 5){ echo 'selected="selected"';} ?>>In</option>
						</select>

						<input type="text" id="extractProgrammeVal" name="extractProgrammeVal" class="searchMultiItem" value="<?php echo $requestData['extractProgrammeVal']; ?>" />
					</div>

					<div class="fields" class="displayFlex">
						<label for="extractFilterBy"> Filter By</label>
						<select id="extractFilterBy" name="extractFilterBy" class="wid85" onChange="if(this.value == 0){ extractFilterByCondVal.value = '';}" >
							<option value="0" <?php if($requestData['extractFilterBy'] == 0){echo 'selected';} ?> >-No Filter-</option>
							<option value="1" <?php if($requestData['extractFilterBy'] == 1){echo 'selected';} ?> >Duration</option>

						</select>

							<select class="wid50" id="extractFilterByCond" name="extractFilterByCond" >
								<option value="0" <?php if($requestData['extractFilterByCond'] == 0){ echo 'selected="selected"';} ?>> < </option>
								<option value="1" <?php if($requestData['extractFilterByCond'] == 1){ echo 'selected="selected"';} ?>> <= </option>
								<option value="2" <?php if($requestData['extractFilterByCond'] == 2){ echo 'selected="selected"';} ?>> = </option>
								<option value="3" <?php if($requestData['extractFilterByCond'] == 3){ echo 'selected="selected"';} ?>> > </option>
								<option value="4" <?php if($requestData['extractFilterByCond'] == 4){ echo 'selected="selected"';} ?>> => </option>
								<option value="5" <?php if($requestData['extractFilterByCond'] == 4){ echo 'selected="selected"';} ?>> <> </option>
							</select>
						<input type="text" class="wid50" onKeyUP="if(extractFilterBy.value == 0){this.value = '';}" id="extractFilterByCondVal" name="extractFilterByCondVal" value="<?php echo $requestData['extractFilterByCondVal']; ?>" >
					</div>


					<div class="fields" class="displayFlex">
						<label for="extractHistoryData">Export History Data</label>
						<select id="extractHistoryData">
							<option value="0" <?php if($requestData['extractHistoryData'] == 0){ echo 'selected="selected"';} ?>>-No History in Export-</option>
							<option value="1" <?php if($requestData['extractHistoryData'] == 1){ echo 'selected="selected"';} ?>>-Include History in Export-</option>
						</select>
					</div>
				</div>

			</div>
		</div>

		<div class="chargeCol2 infoBox">

			<div class="chargeCol7 keyWidth">
				<div class="chargeCol chargeCol2">
					<button type="text" class="charging-btn" onClick="getFilteredData();" >Extract</button>

					<button type="text" class="charging-btn" onClick="createDocs();"><img src="./images/excel.svg"
							class="exportIcon">Create</button>

				</div>
			</div>
		</div>

		<div class="chargeCol chargeCol12">
			<div class="tables weeklyChangingTable">
				<table id="extractEntries" class="oddevenclass tablesmall reportTable" style="table-layout: fixed;width:100%">
					<thead>
						<tr>
							<th>Scheduling Team&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Name&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Staff Number&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Sort Code&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Date&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Week&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Day&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Duty Label&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Duty Name&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Start Time&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>End Time&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Duration&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Job Name&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Job Start&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Job End&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Job Duration&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Job Label&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Job Location&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Job Contact&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Duty Comments&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Person Comments&nbsp;&nbsp;&nbsp;&nbsp;</th>
							<th>Leave Type&nbsp;&nbsp;&nbsp;&nbsp;</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if(!empty($data[1])){
						foreach($data[1] as $reportListArr)
						{
							$groupId = $reportListArr['DutyDate'] . '-' . $reportListArr['WeekNumber'] . '-' . $reportListArr['iDay'] . '-' . $reportListArr['DutyName'] . '-' . $reportListArr['StaffNumber'];
							if (isset($reportListArr['DutyComments']) && strpos($reportListArr['DutyComments'], '__COMMENT_SEPARETOR__') !== false) {
								$commentArr = explode('__COMMENT_SEPARETOR__', $reportListArr['DutyComments']);
								$reportListArr['PersonComments'] = isset($commentArr[0]) ? $commentArr[0] : '';
								$reportListArr['DutyComments'] = isset($commentArr[1]) ? $commentArr[1] : '';
							}
							$listStartTime = !empty($reportListArr['StartTime']) ? $reportListArr['StartTime'] : 0;
							$listEndTime = !empty($reportListArr['EndTime']) ? $reportListArr['EndTime'] : 0;
					?>
						<tr data-group="<?php echo $groupId; ?>">
							<td style="word-wrap: break-word"><?php echo $reportListArr['HomeTeam']; ?></td>
							<td style="word-wrap: break-word"><?php echo $reportListArr['DisplayName']; ?></td>
							<td><?php echo $reportListArr['StaffNumber']; ?></td>
							<td style="word-wrap: break-word"><?php echo $reportListArr['sortcode']; ?></td>
							<td nowrap><?php echo date("d-m-Y",strtotime($reportListArr['DutyDate'])); ?></td>
							<td><?php echo $reportListArr['WeekNumber']; ?></td>
							<td><span style="color:transparent;"><?php echo $reportListArr['iDay']; ?></span><?php echo $iDayTxtArr[$reportListArr['iDay']]; ?></td>
							<td style="word-wrap: break-word"><?php echo implode(', ', array_filter([$reportListArr['Programme'], $reportListArr['DutyLabel2'], $reportListArr['DutyLabel3'], $reportListArr['DutyLabel4'], $reportListArr['DutyLabel5'], $reportListArr['DutyLabel6']])); ?></td>
							<td style="word-wrap: break-word"><?php echo $reportListArr['DutyName']; ?></td>
							<td><?php echo gmdate("H:i", $listStartTime); ?></td>
							<td><?php echo gmdate("H:i", $listEndTime); ?></td>
							<td><?php echo gmdate("H:i", ($reportListArr['Duration']-$reportListArr['dutyBreakTime'])); ?></td>
							<td style="word-wrap: break-word"><?php echo (!empty($reportListArr['jobdata']) && isset($reportListArr['jobdata'][0]['JobName'])) ? $reportListArr['jobdata'][0]['JobName'] : ''; ?></td>
							<td style="word-wrap: break-word"><?php echo (!empty($reportListArr['jobdata']) && isset($reportListArr['jobdata'][0]['JobStartTime'])) ? $reportListArr['jobdata'][0]['JobStartTime'] : ''; ?></td>
							<td style="word-wrap: break-word"><?php echo (!empty($reportListArr['jobdata']) && isset($reportListArr['jobdata'][0]['JobEndTime'])) ? $reportListArr['jobdata'][0]['JobEndTime'] : ''; ?></td>
							<td style="word-wrap: break-word"><?php echo (!empty($reportListArr['jobdata']) && isset($reportListArr['jobdata'][0]['JobDuration'])) ? $reportListArr['jobdata'][0]['JobDuration'] : ''; ?></td>
							<td style="word-wrap: break-word"><?php echo (!empty($reportListArr['jobdata']) && isset($reportListArr['jobdata'][0]['JobLabel'])) ? $reportListArr['jobdata'][0]['JobLabel'] : ''; ?></td>
							<td style="word-wrap: break-word"><?php echo (!empty($reportListArr['jobdata']) && isset($reportListArr['jobdata'][0]['JobLocation'])) ? $reportListArr['jobdata'][0]['JobLocation'] : ''; ?></td>
							<td style="word-wrap: break-word"><?php echo (!empty($reportListArr['jobdata']) && isset($reportListArr['jobdata'][0]['JobContact'])) ? $reportListArr['jobdata'][0]['JobContact'] : ''; ?></td>
							<td style="word-wrap: break-word"><?php echo $reportListArr['DutyComments']; ?></td>
							<td style="word-wrap: break-word"><?php echo $reportListArr['PersonComments']; ?></td>
							<td style="word-wrap: break-word">
								<?php 
								$isLeave = false;
								if (!empty($reportListArr['jobdata']) && is_array($reportListArr['jobdata']) && isset($reportListArr['jobdata'][0]['JobName']) && is_string($reportListArr['jobdata'][0]['JobName'])) {
									$isLeave = stripos($reportListArr['jobdata'][0]['JobName'], 'leave') !== false;
								}
								if (is_string($reportListArr['DutyName']) && stripos($reportListArr['DutyName'], 'leave') !== false) {
									$isLeave = true;
								}
								if ($isLeave) {
									echo htmlspecialchars($reportListArr['DisplayAllocName'] ?? '');
								}
								?>
							</td>
						</tr>

						<?php 
							if(count($reportListArr['jobdata']) > 1){
								for($i=1; $i<count($reportListArr['jobdata']); $i++){
						?>
									<tr data-group="<?php echo $groupId; ?>">
										<<td style="word-wrap: break-word"><?php echo $reportListArr['HomeTeam']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['DisplayName']; ?></td>
										<td><?php echo $reportListArr['StaffNumber']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['sortcode']; ?></td>
										<td nowrap><?php echo date("d-m-Y",strtotime($reportListArr['DutyDate'])); ?></td>
										<td><?php echo $reportListArr['WeekNumber']; ?></td>
										<td><span style="color:transparent;"><?php echo $reportListArr['iDay']; ?></span><?php echo $iDayTxtArr[$reportListArr['iDay']]; ?></td>
										<td style="word-wrap: break-word"><?php echo implode(', ', array_filter([$reportListArr['Programme'], $reportListArr['DutyLabel2'], $reportListArr['DutyLabel3'], $reportListArr['DutyLabel4'], $reportListArr['DutyLabel5'], $reportListArr['DutyLabel6']])); ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['DutyName']; ?></td>
										<td><?php echo gmdate("H:i", $reportListArr['StartTime']); ?></td>
										<td><?php echo gmdate("H:i", $reportListArr['EndTime']); ?></td>
										<td><?php echo gmdate("H:i", ($reportListArr['Duration']-$reportListArr['dutyBreakTime'])); ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['jobdata'][$i]['JobName']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['jobdata'][$i]['JobStartTime']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['jobdata'][$i]['JobEndTime']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['jobdata'][$i]['JobDuration']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['jobdata'][$i]['JobLabel']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['jobdata'][$i]['JobLocation']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['jobdata'][$i]['JobContact'] ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['DutyComments']; ?></td>
										<td style="word-wrap: break-word"><?php echo $reportListArr['PersonComments']; ?></td>
										<td>
											<?php 
											if ((stripos($reportListArr['jobdata'][$i]['JobName'], 'leave') !== false) || (stripos($reportListArr['DutyName'], 'leave') !== false)) {
												echo $reportListArr['DisplayAllocName'];
											}
											?>
										</td>
									</tr>
						<?php 
								}
							}
						?>
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
var table ;
$(document).ready(function () {
	let selectedIDs = "<?php echo isset($requestData['ExtractDays']) ? trim($requestData['ExtractDays']) : ''; ?>";
	if (selectedIDs !== '') {
	    let selectedArray = selectedIDs.split(',').map(id => id.trim());
	    $('#ExtractDays').val(selectedArray);
	} else {
	    $('#ExtractDays').val(['ALL']);
	}
	$('#ExtractDays option').css({ 'background-color': '#FFFFFF', 'color': '#000000' });
	$('#ExtractDays option:selected').css({ 'background-color': '#1967D2', 'color': '#FFFFFF' });
	$('#ExtractDays').on('change', function () {
	    let selected = $(this).val() || [];
	    if (selected.includes('ALL')) {
	        $(this).find('option').each(function () {
	            if ($(this).val() !== 'ALL') {
	                $(this).prop('selected', false);
	            }
	        });
	        $(this).val(['ALL']);
	    } else {
	        $(this).find('option[value="ALL"]').prop('selected', false);
	    }
	    $(this).find('option').css({ 'background-color': '#FFFFFF', 'color': '#000000' });
	    $(this).find('option:selected').css({ 'background-color': '#1967D2', 'color': '#FFFFFF' });
	});

	jQuery.fn.dataTable.ext.type.order['date-custom-pre'] = function (date) {
		const parts = date.split('-');
		return parts[2] + parts[1] + parts[0];
	};

	table = $('#extractEntries').DataTable({
		paging: false,
		lengthChange: false,
		bInfo: false,
		bFilter: false,
		scrollY: 170,
		order: [],
		columnDefs: [{
			type: 'date-custom',
			targets: 3,
			orderable: true
		}],
		drawCallback: function () {
			const $body = $('#extractEntries tbody');
			const rows = $body.find('tr');
			const groupedRows = {};

			const dt = $(this).DataTable();
			const order = dt.order();
			const sortIndex = order.length ? order[0][0] : 0;
			const sortDir = order.length ? order[0][1] : 'asc';

			rows.each(function () {
				const group = $(this).data('group') || '';
				if (!groupedRows[group]) groupedRows[group] = [];
				groupedRows[group].push(this);
			});

			const sortedGroups = Object.values(groupedRows).sort((a, b) => {
				const getVal = (group) => {
					const masterRow = group.find(row => $(row).find('td').eq(1).text().trim() !== '') || group[0];
					return $(masterRow).find('td').eq(sortIndex).text().trim();
				};

				let valA = getVal(a);
				let valB = getVal(b);

				if (sortIndex === 3) {
					valA = $.fn.dataTable.ext.type.order['date-custom-pre'](valA);
					valB = $.fn.dataTable.ext.type.order['date-custom-pre'](valB);
				}

				return sortDir === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
			});

			$body.empty();
			sortedGroups.forEach(group => {
				const masterIndex = group.findIndex(row => {
					const displayName = $(row).find('td').eq(1).text().trim();
					return displayName !== '';
				});

				if (masterIndex > 0) {
					const [masterRow] = group.splice(masterIndex, 1);
					group.unshift(masterRow);
				}

				group.forEach(row => $body.append(row));
			});
		}
	});
	validateJobsRelFields($('#includeJobData').val());
});
function validateJobsRelFields(eleVal)
{
	if(eleVal == 0)
	{
		document.getElementById('extractJob').disabled 			= false;
		document.getElementById('extractJobProgramme').disabled = false;
		document.getElementById('extractContact').disabled 		= false;
		document.getElementById('extractLocation').disabled 	= false;
		document.getElementById('extractJobVal').disabled 		= false;
		document.getElementById('extractJobProgrammeVal').disabled = false;
		document.getElementById('extractContactVal').disabled 	= false;
		document.getElementById('extractLocationVal').disabled 	= false;
	}else
	{
		document.getElementById('extractJob').disabled 			= true;
		document.getElementById('extractJobProgramme').disabled = true;
		document.getElementById('extractContact').disabled 		= true;
		document.getElementById('extractLocation').disabled 	= true;
		document.getElementById('extractJobVal').disabled 		= true;
		document.getElementById('extractJobProgrammeVal').disabled = true;
		document.getElementById('extractContactVal').disabled 	= true;
		document.getElementById('extractLocationVal').disabled 	= true;
	}
}
function getFilteredData()
{
	let schedulingTeam			= $('#schedulingTeam').val();
	let weekTo 					= $('#weekTo').val();
	let weekFrom 				= $('#weekFrom').val();
	let ExtractDays             = ($('#ExtractDays').val()) ? $('#ExtractDays').val().toString() : '';	
	let extractFilter 			= $('#extractFilter').val();
	let includeLeave 			= $('#includeLeave').val();
	let includeJobData 			= $('#includeJobData').val();
	let reportType 				= $('#reportType').val();
	let extractGroup1 			= ($('#extractGroup1').prop('disabled')) ? '' : $('#extractGroup1').val();
	let extractGroup2 			= ($('#extractGroup2').prop('disabled')) ? '' : $('#extractGroup2').val();
	let staffNumber				= $('#staffNumber').val();
	let staffNumberVal			= $('#staffNumberVal').val();
	let extractJob				= ($('#extractJob').prop('disabled')) ? '' : $('#extractJob').val();
	let extractJobVal			= ($('#extractJobVal').prop('disabled')) ? '' : $('#extractJobVal').val();
	let extractProgramme		= ($('#extractProgramme').prop('disabled')) ? '' : $('#extractProgramme').val();
	let extractProgrammeVal		= ($('#extractProgrammeVal').prop('disabled')) ? '' : $('#extractProgrammeVal').val();
	let extractContact			= ($('#extractContact').prop('disabled')) ? '' : $('#extractContact').val();
	let extractContactVal		= ($('#extractContactVal').prop('disabled')) ? '' : $('#extractContactVal').val();
	let extractLocation			= ($('#extractLocation').prop('disabled')) ? '' : $('#extractLocation').val();
	let extractLocationVal		= ($('#extractLocationVal').prop('disabled')) ? '' : $('#extractLocationVal').val();
	let extractDuty				= ($('#extractDuty').prop('disabled')) ? '' : $('#extractDuty').val();
	let extractDutyVal			= ($('#extractDutyVal').prop('disabled')) ? '' : $('#extractDutyVal').val();
	let extractFilterBy			= $('#extractFilterBy').val();
	let extractFilterByCond		= $('#extractFilterByCond').val();
	let extractFilterByCondVal	= $('#extractFilterByCondVal').val();
	let extractHistoryData		= $('#extractHistoryData').val();
	let extractJobProgramme		= ($('#extractJobProgramme').prop('disabled')) ? '' : $('#extractJobProgramme').val();
	let extractJobProgrammeVal		= ($('#extractJobProgrammeVal').prop('disabled')) ? '' : $('#extractJobProgrammeVal').val();
	$.ajax({
		  type: 'POST',
		  url: 'page-includes/admin/charging/index.php',
		  data: {
					schedulingTeam			: schedulingTeam,
					weekTo 					: weekTo,
					weekFrom 				: weekFrom,
					ExtractDays 			: ExtractDays,
					extractFilter 			: extractFilter,
					includeLeave 			: includeLeave,
					includeJobData 			: includeJobData,
					reportType 				: reportType,
					extractGroup1 			: extractGroup1,
					extractGroup2 			: extractGroup2,
					staffNumber				: staffNumber,
					staffNumberVal			: staffNumberVal,
					extractJob				: extractJob,
					extractJobVal			: extractJobVal,
					extractProgramme		: extractProgramme,
					extractProgrammeVal		: extractProgrammeVal,
					extractContact			: extractContact,
					extractContactVal		: extractContactVal,
					extractLocation			: extractLocation,
					extractLocationVal		: extractLocationVal,
					extractDuty				: extractDuty,
					extractDutyVal			: extractDutyVal,
					extractFilterBy			: extractFilterBy,
					extractFilterByCond		: extractFilterByCond,
					extractFilterByCondVal	: extractFilterByCondVal,
					extractHistoryData		: extractHistoryData,
					extractJobProgramme		: extractJobProgramme,
					extractJobProgrammeVal	: extractJobProgrammeVal,
					conrollerName			: 'dutyExtractReport'
			  },
		  success: function (returnHtml){
			if($($.parseHTML(returnHtml)).filter("#content-duty-extract-report").length)
			{
				$('#content-duty-extract-report').html(returnHtml);
				$('#schedulingTeam').val(schedulingTeam);
			}else
			{
				customAlert(returnHtml);
			}
		}
	});
}
function createDocs()
{
	let schedulingTeam			= $('#schedulingTeam').val();
	let weekTo 					= $('#weekTo').val();
	let weekFrom 				= $('#weekFrom').val();
	let ExtractDays             = ($('#ExtractDays').val()) ? $('#ExtractDays').val().toString() : '';
	let extractFilter 			= $('#extractFilter').val();
	let includeLeave 			= $('#includeLeave').val();
	let includeJobData 			= $('#includeJobData').val();
	let reportType 				= $('#reportType').val();
	let extractGroup1 			= ($('#extractGroup1').prop('disabled')) ? '' : $('#extractGroup1').val();
	let extractGroup2 			= ($('#extractGroup2').prop('disabled')) ? '' : $('#extractGroup2').val();
	let staffNumber				= $('#staffNumber').val();
	let staffNumberVal			= $('#staffNumberVal').val();
	let extractJob				= ($('#extractJob').prop('disabled')) ? '' : $('#extractJob').val();
	let extractJobVal			= ($('#extractJobVal').prop('disabled')) ? '' : $('#extractJobVal').val();
	let extractProgramme		= ($('#extractProgramme').prop('disabled')) ? '' : $('#extractProgramme').val();
	let extractProgrammeVal		= ($('#extractProgrammeVal').prop('disabled')) ? '' : $('#extractProgrammeVal').val();
	let extractContact			= ($('#extractContact').prop('disabled')) ? '' : $('#extractContact').val();
	let extractContactVal		= ($('#extractContactVal').prop('disabled')) ? '' : $('#extractContactVal').val();
	let extractLocation			= ($('#extractLocation').prop('disabled')) ? '' : $('#extractLocation').val();
	let extractLocationVal		= ($('#extractLocationVal').prop('disabled')) ? '' : $('#extractLocationVal').val();
	let extractDuty				= ($('#extractDuty').prop('disabled')) ? '' : $('#extractDuty').val();
	let extractDutyVal			= ($('#extractDutyVal').prop('disabled')) ? '' : $('#extractDutyVal').val();
	let extractFilterBy			= $('#extractFilterBy').val();
	let extractFilterByCond		= $('#extractFilterByCond').val();
	let extractFilterByCondVal	= $('#extractFilterByCondVal').val();
	let extractHistoryData		= $('#extractHistoryData').val();
	let extractJobProgramme		= ($('#extractJobProgramme').prop('disabled')) ? '' : $('#extractJobProgramme').val();
	let extractJobProgrammeVal	= ($('#extractJobProgrammeVal').prop('disabled')) ? '' : $('#extractJobProgrammeVal').val();
	location='page-includes/admin/charging/dutyExtractExport.php?weekTo='+weekTo+'&weekFrom='+weekFrom+'&ExtractDays='+ExtractDays+'&extractFilter='+extractFilter+'&includeLeave='+includeLeave+'&includeJobData='+includeJobData+'&includeJobData='+includeJobData+'&reportType='+reportType+'&extractGroup1='+extractGroup1+'&extractGroup2='+extractGroup2+'&staffNumber='+staffNumber+'&staffNumberVal='+staffNumberVal+'&extractJob='+extractJob+'&extractJobVal='+extractJobVal+'&orderBy='+btoa(table.order())+'&extractProgramme='+extractProgramme+'&extractProgrammeVal='+extractProgrammeVal+'&extractContact='+extractContact+'&extractContactVal='+extractContactVal+'&extractLocation='+extractLocation+'&extractLocationVal='+extractLocationVal+'&extractDuty='+extractDuty+'&extractDutyVal='+extractDutyVal+'&extractFilterBy='+extractFilterBy+'&extractFilterByCond='+extractFilterByCond+'&extractFilterByCondVal='+extractFilterByCondVal+'&extractHistoryData='+extractHistoryData+'&schedulingTeam='+schedulingTeam+'&extractJobProgramme='+extractJobProgramme+'&extractJobProgrammeVal='+extractJobProgrammeVal;
}

function updateFilterOption(schedulingTeam)
{
	$.ajax({
		  type: 'POST',
		  url: 'page-includes/admin/charging/index.php',
		  data: {
					schedulingTeam			: schedulingTeam,
					conrollerName			: 'dutyExtractFilterOption'
			  },
		  success: function (returnHtml){
			$('#extractFilter').html(returnHtml);
		}
	});
}
</script>