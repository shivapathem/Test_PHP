<link rel="stylesheet" type="text/css" href="../styles/default.css">
<link href="../styles/charging/charging.css" rel="stylesheet">
<link href="../styles/charging/chargingReports.css" rel="stylesheet">
<title>Staff Extract Report</title>
	<div id="content-staff-extract">
        <h1 class="headertextallpages main-heading">Staff Extract Report</h1>
        <div id="staffExtractReport" class="chargeRow">
            <div class="filterContainerFlex chargeCol10">
                <span class="heading">Filter</span>
                <div class="chargeRow">
                    <div class="chargeCol chargeCol5">
                        <div class="fields">
                            <label class="labelTxtAlign" for="reportType">Report Type</label>
                            <select id="reportType" class="weeklyChargingFrom" onChange="if(this.value == 'ALL'){$('#grpContainer1').hide();$('#grpContainer2').hide();$('#groupBy1').val(0);$('#groupBy2').val(0);}else{$('#grpContainer1').show();$('#grpContainer2').show();}" >
                                <option value="ALL" <?php if($requestData['reportType'] == "ALL"){echo 'selected';} ?>>Show All Records</option>
                                <option value="GRP" <?php if($requestData['reportType'] == "GRP"){echo 'selected';} ?>>Group Records</option>
                            </select>
						</div>

						<div class="fields">
                            <label class="labelTxtAlign" for="effectINForm">Effective On</label>
                            <input id="effectINForm" class="weeklyChargingFrom" value="<?php if(!empty($requestData['effectINForm'])){echo $requestData['effectINForm'];}else{echo date('d/m/Y');} ?>" readonly />
						</div>
						<div class="fields">
                            <label class="labelTxtAlign" for="eft">EFT</label>
                            <select id="eft" class="weeklyChargingFrom">
								<option value="-1" >All Available EFT</option>
                                <option value="1" <?php if($requestData['eft'] == "1"){echo 'selected';} ?>>Full time worker</option>
                                <option value="0" <?php if($requestData['eft'] == "0"){echo 'selected';} ?>>Other</option>
                            </select>
						</div>
						<div class="fields">
                            <label class="labelTxtAlign" for="edp">EDP</label>
                            <select id="edp" class="weeklyChargingFrom">
								<option value="-1" >All Available EDP</option>
                                <option value="0" <?php if($requestData['edp'] == "0"){echo 'selected';} ?>>Calculated</option>
                                <option value="1" <?php if($requestData['edp'] == "1"){echo 'selected';} ?>>Manual</option>
                            </select>
						</div>
						<div class="fields">
                            <label class="labelTxtAlign" for="fsv">FSV</label>
                            <select id="fsv" class="weeklyChargingFrom">
							<?php
								if(is_iterable($data[3])){
									foreach($data[3] as $fsvList)
									{
										$fsvSelectedStr = '';
										if($fsvList['PaymentTypeShortCode'] == $requestData['fsv'])
										{
											$fsvSelectedStr = 'selected';
										}
										if($fsvList['PaymentTypeShortCode'] == '')
										{
											echo '<option value="" >All Available FSV</option>';
										}else
										{
											echo '<option value="'.$fsvList['PaymentTypeShortCode'].'" '.$fsvSelectedStr.'>'.$fsvList['PaymentTypeShortCode'].'</option>';
										}
									}
								}
							?>
                            </select>
						</div>
                        <div class="fields displayFlex" >
                            <label class="labelTxtAlign" for="mappingFrom">Staff Number(s) Split with a comma','</label>
                            <textarea name="staffNumber" id="staffNumber" class="splitCol" cols="47"><?php echo $requestData['staffNumber']; ?></textarea>
                        </div>
                    </div>
                    <div class="chargeCol chargeCol5">
                        <div class="fields displayFlex">
                            <label class="labelTxtAlign" for="schedulingTeam">Scheduling Teams</label>
                            <select id="schedulingTeam" name="schedulingTeam" class="staffExtractSelect" data-placeholder="Select Scheduling Teams" multiple>
							<option value="-1" <?php if($requestData['schedulingTeam'] == "-1"){echo 'selected';} ?>>All Available Teams</option>
                                <?php
								$schedulingTeamTempArr = [];
								if(is_iterable($data[1])){
									$teamDefaultId = auth()->user()->defaultTeamId;
									foreach($data[1] as $teamDetailsArr)
									{
										if(($teamDefaultId == $teamDetailsArr['id']) && (!$requestData['schedulingTeam']))
										{
											$teamSelectStr = 'Selected';
											$schedulingTeamTempArr[] = $teamDetailsArr['name'];
										}elseif(in_array($teamDetailsArr['id'], explode('|', $requestData['schedulingTeam'])))
										{
											$teamSelectStr = 'Selected';
											$schedulingTeamTempArr[] = $teamDetailsArr['name'];
										}else
										{
											$teamSelectStr = '';
										}
										echo '<option value="'.$teamDetailsArr['id'].'" '.$teamSelectStr.'>'.$teamDetailsArr['name'].'</option>';
									}
								}
								?>
                            </select>
							<input type="hidden" name="schedulingTeamNameArr" id="schedulingTeamNameArr" value="<?php echo implode(',',$schedulingTeamTempArr); ?>" >
                        </div>

                        <div class="fields displayFlex">
                            <label class="labelTxtAlign" for="estabCodes">Charge Codes</label>
                            <select id="estabCodes" class="staffExtractSelect" data-placeholder="Select Charge Codes" multiple>
							<option value="" <?php if($requestData['estabCodes'] == ""){echo 'selected';} ?>>All Charge Codes</option>
                                <?php
								if(is_iterable($data[2])){
									foreach($data[2] as $estabDetailsArr)
									{
										if(in_array($estabDetailsArr['CostCode'], explode('|', $requestData['estabCodes'])))
										{
											$estabSelectStr = 'Selected';
										}else
										{
											$estabSelectStr = '';
										} 										
										echo '<option value="'.$estabDetailsArr['CostCode'].'" '.$estabSelectStr.'>'.$estabDetailsArr['CostCode'].'</option>';
									}
								}
								?>
                            </select>
                        </div>
						<div id="grpContainer1" class="fields displayFlex" <?php if(($requestData['reportType'] == "ALL") || ($requestData['reportType'] == "")){echo 'style="display:none"';} ?>>
                            <label class="labelTxtAlign" for="groupBy1">Group By 1</label>
                            <select id="groupBy1" class="" data-placeholder="Select Fields" onchange="updateGrp2(this.value);" >
                                <option value="0">Nothing</option>
                                <option value="1" <?php if($requestData['groupBy1'] == 1){echo 'Selected';} ?>>Scheduling Team</option>
                                <option value="2" <?php if($requestData['groupBy1'] == 2){echo 'Selected';} ?>>Sort Code</option>
                                <option value="3" <?php if($requestData['groupBy1'] == 3){echo 'Selected';} ?>>Charge Code</option>
                            </select>
                        </div>
						<div id="grpContainer2" class="fields displayFlex" <?php if(($requestData['reportType'] == "ALL") || ($requestData['reportType'] == "")){echo 'style="display:none"';} ?>>
                            <label class="labelTxtAlign" for="groupBy2">Group By 2</label>
                            <select id="groupBy2" class="" data-placeholder="Select Fields" onChange="if($('#groupBy1').val() == 0){$('#groupBy2').val(0);};" >
                                <option id="groupBy2_val0" value="0">Nothing</option>
                                <option id="groupBy2_val1" value="1" <?php if($requestData['groupBy2'] == 1){echo 'Selected';} ?>>Scheduling Team</option>
                                <option id="groupBy2_val2" value="2" <?php if($requestData['groupBy2'] == 2){echo 'Selected';} ?>>Sort Code</option>
                                <option id="groupBy2_val3" value="3" <?php if($requestData['groupBy2'] == 3){echo 'Selected';} ?>>Charge Code</option>
                            </select>
                        </div>
                    </div>


                    <div class="chargeCol chargeCol2">
                        <button type="text" class="charging-btn" onClick="applyFilter();" >Refresh</button>


                        <button type="text" class="charging-btn" onClick="createDocs();" ><img src="./images/excel.svg"
                                class="exportIcon">Export</button>
                       
                    </div>
                </div>
            </div>



            <div class="chargeCol2 infoBox">
                <p>You may click on the column headings to sort by that column. The spreadsheet will be produced with
                    the selected order.</p>
				<p>Use <b>Shift</b> & <b>Ctrl</b> keys to multiselect the teams and charge codes.</p>
            </div>

            <div class="chargeCol chargeCol12">
                <div class="tables">
                    <table id="staffExtractEntries" class="oddevenclass tablesmall reportTable" style="width:100%;">
                        <thead>
                            <tr class="freezeHeader" >
                                <th>Scheduling Team</th>
                                <th>Name</th>
                                <th>Staff Number</th>
								<th>Charge Code </th>
                                <th>Sort Code </th>
                                <th>FSV</th>
                                <th>EDP</th>
                                <th>EFT</th>
                            </tr>
                        </thead>
                        <tbody>
						<?php
						$staffReportVal = array();
						if(count((array)$data[0]) > 0)
						{
							if(is_iterable($data[0])){
							foreach($data[0] as $staffReportVal)
							{
								$manualEDP 	= 	'';
								if($staffReportVal['ManualEDP'] === "0")
								{
									$manualEDP 	= 	'C';
								}elseif($staffReportVal['ManualEDP'] === "1")
								{
									$manualEDP 	= 	'M';
								}
							
							
						
						?>
                            <tr>
                                <td><?php echo $staffReportVal['schedulingTeamName']; ?></td>
                                <td><?php echo $staffReportVal['DisplayName']; ?></td>
                                <td><?php echo $staffReportVal['StaffNumber']; ?></td>
                                <td><?php echo $staffReportVal['CostCode']; ?></td>
                                <td><?php echo $staffReportVal['SortCode']; ?></td>
								<td><?php echo $staffReportVal['PaymentTypeShortCode']; ?></td>
								<td><?php echo $manualEDP; ?></td>
								<td><?php echo $staffReportVal['EFT']; ?></td>
                            </tr>
						<?php
							}
						}
						}
						else
						{
						?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td align="right" >No record </td>
                                <td>found</td>
                                <td></td>
								<td></td>
								<td></td>
								<td></td>
                            </tr>
						<?php
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

	$(".staffExtractSelect").css('height','80px');
	
	table = $('#staffExtractEntries').DataTable({
		"paging": false,
		"lengthChange": false,
		"bInfo": false,
		"bFilter": false,
		"scrollY": 230,
		"columnDefs": [{
			"targets": 'no-sort',
			"orderable": false,
		}]

	});
	$("#effectINForm").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd/mm/yy",
        yearRange: '1995:9999',
        inline: true,
		firstDay: 6
    }).change(function (selected) {
  
    });
});
function chargingCallbackFunc(data)
{
	if($($.parseHTML(data)).filter("#content-staff-extract").length)
	{
		$('#content-staff-extract').html(data);
	}else
	{
		customAlert(data);
	}
}
function applyFilter()
{
	let reportType 		= $('#reportType').val();
	let effectINForm 	= $('#effectINForm').val();
	let eft		 		= $('#eft').val();
	let edp			 	= $('#edp').val();
	let fsv		 		= $('#fsv').val();
	let schedulingTeam	= $('#schedulingTeam').val();
	let estabCodes		= ($('#estabCodes').val()) ? $('#estabCodes').val().toString() : '';
	let staffNumber		= $('#staffNumber').val();
	let groupBy1		= $('#groupBy1').val();
	let groupBy2		= $('#groupBy2').val();
	estabCodes			= estabCodes.replaceAll(",", "|");
	if(!schedulingTeam)
	{
		customAlert('Please select scheduling team.');
		return false;
	}
	$.ajax({
		  type: 'POST',
		  url: 'page-includes/admin/charging/index.php',
		  data: {
				  conrollerName	:	'staffExtractReport',
				  effectINForm	:	effectINForm,
				  eft			:	eft,
				  edp			:	edp,
				  fsv			:	fsv,
				  schedulingTeam:	schedulingTeam,
				  estabCodes	:	estabCodes,
				  staffNumber	:	staffNumber,
				  reportType	:	reportType,
				  groupBy1		:	groupBy1,
				  groupBy2		:	groupBy2,
				  flormFlag		:	'submit'
			  },
		  success: function (returnHtml){
			if($($.parseHTML(returnHtml)).filter("#content-staff-extract").length)
			{
				$('#content-staff-extract').html(returnHtml);
			}else
			{
				customAlert(returnHtml);
			}
		}
	});
}
function createDocs()
{
	let reportType 		= $('#reportType').val();
	let effectINForm 	= $('#effectINForm').val();
	let eft		 		= $('#eft').val();
	let edp			 	= $('#edp').val();
	let fsv		 		= $('#fsv').val();
	let schedulingTeam	= ($('#schedulingTeam').val()) ? $('#schedulingTeam').val().toString() : '';
	let schedulingTeamNameArr	= $('#schedulingTeamNameArr').val();
	let estabCodes		= ($('#estabCodes').val()) ? $('#estabCodes').val().toString() : '';
	let staffNumber		= $('#staffNumber').val();
	let groupBy1		= $('#groupBy1').val();
	let groupBy2		= $('#groupBy2').val();
	schedulingTeam		= schedulingTeam.replaceAll(",", "|");
	staffNumber			= staffNumber.replaceAll(",", "|");
	estabCodes			= estabCodes.replaceAll(",", "|");
	if(!schedulingTeam)
	{
		customAlert('Please select scheduling team.');
		return false;
	}
	location='page-includes/admin/charging/staffExtractExport.php?reportType='+reportType+'&effectINForm='+effectINForm+'&eft='+eft+'&edp='+edp+'&fsv='+fsv+'&schedulingTeam='+schedulingTeam+'&estabCodes='+estabCodes+'&staffNumber='+staffNumber+'&schedulingTeamNameArr='+schedulingTeamNameArr+'&groupBy1='+groupBy1+'&groupBy2='+groupBy2+'&orderBy='+btoa(table.order());
}
function updateGrp2(currVal)
{
	$('#groupBy2').val(0);
	$('#groupBy2_val1').attr('disabled', false);
	$('#groupBy2_val2').attr('disabled', false);
	$('#groupBy2_val3').attr('disabled', false);
	switch(currVal)
	{
		case "1":	$('#groupBy2_val1').attr('disabled', true);
				break;
		case "2":	$('#groupBy2_val2').attr('disabled', true);
				break;
		case "3":	$('#groupBy2_val3').attr('disabled', true);
				break;
		default:	
				$('#groupBy2_val1').attr('disabled', false);
				$('#groupBy2_val2').attr('disabled', false);
				$('#groupBy2_val3').attr('disabled', false);
				break;
	}
}
<?php if(($requestData['reportType'] == "GRP") && ($requestData['groupBy2'] <= "0")){echo "updateGrp2($('#groupBy1').val());";} ?>
</script>
<style>
	.labelTxtAlign { 
		text-align: left !important;
	}
</style>