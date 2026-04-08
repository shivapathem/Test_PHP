<link href="../styles/charging/charging.css" rel="stylesheet">
<div id="content-charge-to-finance">
	<h1 class="headertextallpages  main-heading">Send Charging to Finance </h1>
	<div id="sendCharging" class="chargeRow ">
		<div class="chargingFilter fullWidth">
			<div class="grid box1">
			<form name="filterByExistDate" id="filterByExistDate" onSubmit="return false;" >
				<div class="filterContainer fContainer1">
					<span class="heading">Existing Charges</span>
					<div class="fields" style="display:flex;">
						<label for="existingCharges">Dates</label>
						<select id="existingCharges" name="existingDate" onChange="if($('#existingCharges').val() != ''){ document.getElementById('showBtnExistingCharges').disabled = false; }else{document.getElementById('showBtnExistingCharges').disabled = true;}" >
							<option value="">Select Existing Charges</option>
							<?php
							foreach($data[1] as $dataVal1)
							{
								$requestData['existingDate'] = $requestData['existingDate'] ?? '';
							?>
								<option value="<?php echo date('d/m/Y', strtotime($dataVal1['SentToFinanceDate'])); ?>" <?php echo ($requestData['existingDate'] == date('d/m/Y', strtotime($dataVal1['SentToFinanceDate']))) ? 'selected="selected"' : ''; ?>><?php echo date('d/m/Y', strtotime($dataVal1['SentToFinanceDate'])); ?></option>
							<?php
							}
							?>
						</select>
						<?php
							$requestData['fromDate'] = $requestData['fromDate'] ?? '';
							$requestData['toDate'] = $requestData['toDate'] ?? '';
						?>
						<input type="hidden" id="fromDate1" name="fromDate1" value="<?php echo $requestData['fromDate'] ? $requestData['fromDate'] : date('d/m/Y', strtotime($data[0][0]['ChargingDutyDate'] ?? date('Y-m-d', strtotime('-1 year')))); ?>">
						<input type="hidden" id="toDate1" name="toDate1" value="<?php echo $requestData['toDate'] ? $requestData['toDate'] : date('d-m-Y'); ?>">
						<input type="hidden" id="conrollerName" name="conrollerName" value="sendChargingToFinance">
						<input type="hidden" id="area-id-hidden" name="area-id" value="<?php echo $requestData['area_id'][0] ?? ''; ?>">
						<button type="text" id="showBtnExistingCharges" class="charging-btn" onClick="formHandler('filterByExistDate');">Show</button>
						<button type="text" class="charging-btn" onClick="actionHandler('sendChargingToFinance');" >Clear</button>
					</div>
				</div>
			</form>
			</div>
			<form name="filterByDateRange" id="filterByDateRange" onSubmit="return false;" >
				<div class="grid box2">
					<div class="filterContainer fContainer2">
						<span class="heading">Filter</span>
						<div class="fields" style="display:flex;">
							<div class="chargingDateFilter">
								<span>Date</span>
								<input type="text" placeholder="dd/mm/yyyy" autocomplete="off" name="fromDate" id="fromDateCharging" value="<?php echo $requestData['fromDate'] ? $requestData['fromDate'] : date('d/m/Y', strtotime($data[0][0]['ChargingDutyDate'] ?? date('Y-m-d', strtotime('-1 year')))); ?>" />
								<span>to</span>
								<input type="text" placeholder="dd/mm/yyyy" autocomplete="off" name="toDate" id="toDateCharging" value="<?php echo $requestData['toDate'] ? $requestData['toDate'] : date('d/m/Y'); ?>" />
								<input type="hidden" id="conrollerName" name="conrollerName" value="sendChargingToFinance">
								<span>Area</span>
								<?php if(count($data[2]) == 1) {
									echo '<span> : ' . $data[2][0]['DivisionName'] . '</span>'; 
								 } else { ?>	
								<select id="area-select" name="area_id[]" onChange="" >
									<option value="">All</option>
									<?php foreach($data[2] as $divisionLists) { ?>
										<option value="<?php echo $divisionLists['DivisionID']; ?>" <?php echo (in_array($divisionLists['DivisionID'], $requestData['area_id'] ?? []) ? 'selected' : '') ?>><?php echo $divisionLists['DivisionName']; ?></option>
									<?php } ?>
								</select>
								<?php } ?>
								<button type="text" class="charging-btn" onClick="formHandler('filterByDateRange');" >Show</button>
							</div>
						</div>
					</div>
				</div>
			</form>
			<div class="grid box3">
				<span class="sentSap">
				<?php
					$filterMsg	=	'';
					if(!empty($requestData['existingDate']))
					{
						$filterMsg	=	'Showing Charges sent to Finance and Date Stamped '.$requestData['existingDate'];
					}elseif(!empty($requestData['fromDate']) && !empty($requestData['toDate']))
					{
						$filterMsg	=	'Showing charges not yet sent to Finance for duties between ' . $requestData['fromDate'] . ' and ' . $requestData['toDate'];
					}else
					{
						$filterMsg	=	'Showing all charges not yet sent to Finance';
					}
					echo $filterMsg;
				?>
				</span>
			</div>
		</div>
		<div class="chargeRow">
			<div class="chargeCol chargeCol12">

			</div>
			<div class="chargeCol chargeCol12">
				<div class="tables">
					<table id="sendChargingEntries" class="oddevenclass tablesmall" style="width:100%">
						<thead>
							<tr>
								<th>Charge Code</th>
								<th>Activity Code </th>
								<th>CC or WBS Number </th>
								<th>Valid </th>
								<th>Quantity </th>
								<th>Unit Price </th>
								<th>Total Price </th>
								<th>Duty Date </th>
								<th>Comments </th>
								<th>Person & Charge Code</th>
								<th>Staff Number </th>
								<th>Created </th>
								<th>Contact Name</th>
								<th>Contact Telephone </th>
							</tr>
						</thead>
						<tbody>
						<?php 
							$chargingContainerArr	=	array();
							foreach($data[0] as $dataVal)
							{
								$chargingContainerArr[]	=	$dataVal['ChargingId'];
						?>
							<tr>
								<td><?php echo $dataVal['EstablishCode']; ?></td>
								<td><?php echo $dataVal['ActivityCodeName']; ?></td>
								<td><?php echo $dataVal['ChargeWbsCodeName']; ?></td>
								<td><?php echo $dataVal['MappingId'] ? 'Yes' : 'No'; ?></td>
								<td><?php echo $dataVal['Quantity']; ?></td>
								<td>£<?php echo number_format((float)$dataVal['UnitPrice'], 2, '.', ''); ?></td>
								<td>£<?php echo number_format((float)($dataVal['Quantity'] * $dataVal['UnitPrice']), 2, '.', ''); ?></td>
								<td><?php echo date('d/m/Y', strtotime($dataVal['ChargingDutyDate'])); ?></td>
								<td><?php echo $dataVal['Comments']; ?></td>
								<td><?php echo $dataVal['StaffDisplayName'].' ('.$dataVal['EstablishCode'].')'; ?></td>
								<td><?php echo $dataVal['StaffNumber']; ?></td>
								<td><?php echo $dataVal['DisplayName'].' ('.date('d/m/Y', strtotime($dataVal['CreatedDate'])).')'; ?></td>
								<td><?php echo $dataVal['Contact']; ?></td>
								<td><?php echo $dataVal['Telephone']; ?></td>
							</tr>
							<?php
							}
							$chargingContainerArr	=	array_unique($chargingContainerArr);
							?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="chargeCol chargeCol12 sendChargingBtnBlock">
				<button type="text" id="markAsSent" class="charging-btn" onClick="sendChargingToFinanceSave();" title="Mark as Sent button will be enabled after Exporting the charges." disabled >Mark as Sent</button>
				<button type="text" class="charging-btn" onClick="createDocs('<?php echo (implode(',', $chargingContainerArr)); ?>');" >
					<img src="./images/excel.svg" class="exportIcon" alt="Create">Create
				</button>
				<input type="hidden" name="chargingContainer" id="chargingContainer" value="<?php echo (implode(',', $chargingContainerArr)); ?>" >
			</div>
		</div>
	</div>
	<div id="wbsEntrieAdd" style="display:none;">
		<div class="popupHeading">New WBS Code </div>
		<div class="popupContent">
			<div class="fields">
				<label for="costCenter">WBS Centre  </label>
				<input type="text" id="costCenter" value="B0074"/>
			</div>
			<div class="fields">
				<label for="costCenterDesc">Description </label>
				<input type="text" id="costCenterDesc" value="News Global"/>
			</div>
			<div class="popupButton">
				<button type="text" class="charging-btn">Add</button>
				<button type="text" class="charging-btn">Cancel</button>
			</div>
		</div>
	</div>
</div>
<script>
let currDateTime  = "<?php echo date('d/m/Y & h:m'); ?>";
var table ;
$(document).ready(function() {
     table = $('#sendChargingEntries').DataTable( {
        "bJQueryUI":true,
      "bSort":false,
      "bPaginate":true,
       "iDisplayLength": 100,
		"lengthChange": true,
		"pagingType": 'numbers',
		"bInfo" : false,
		"bFilter": true,
		"columnDefs": [
						{ "orderable": false, "targets": 3 },
						{ "orderable": false, "targets": 6 }
					  ]
    } );
$("#fromDateCharging").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd/mm/yy",
        yearRange: '1995:9999',
        inline: true
    }).change(function (selected) {
  
    });
$("#toDateCharging").datepicker({
        changeMonth: true,
        changeYear: true,
        //minDate: document.getElementById("fromDateCharging").value,
        dateFormat: "dd/mm/yy",
        yearRange: '1995:9999',
        inline: true
    }).change(function (selected) {
  
    });

    $("#toDateCharging").on("change", function(){
        let FromDate = $("#fromDateCharging").val().split('/');
        let ToDate = $("#toDateCharging").val().split('/');
        let fromdatecompare = new Date(FromDate[2], FromDate[1], FromDate[0]);
        let todatecomparehome = new Date(ToDate[2], ToDate[1], ToDate[0]);
        if (fromdatecompare > todatecomparehome) {
            customAlert("You cannot select To date smaller than From date.");
            $("#toDateCharging").val('');
            $("#toDateCharging").focus();
        }
    });
    $("#fromDateCharging").on("change", function(){
        let FromDate = $("#fromDateCharging").val().split('/');
        let ToDate = $("#toDateCharging").val().split('/');
        let fromdatecompare = new Date(FromDate[2], FromDate[1], FromDate[0]);
        let todatecomparehome = new Date(ToDate[2], ToDate[1], ToDate[0]);
        if (fromdatecompare > todatecomparehome) {
            customAlert("You cannot select From Date greater than To Date.");
            $("#fromDateCharging").val("");
            $("#fromDateCharging").focus();
        }
    });
} );
function chargingCallbackFunc(data)
{
	if($($.parseHTML(data)).filter("#content-charge-to-finance").length)
	{
		$('#content').html(data);
	}else
	{
		customAlert(data);
	}
}
function sendChargingToFinanceSave()
{
	let currentdate = new Date(); 
    let DayNew = currentdate.getDate();
    let MonthNew = (currentdate.getMonth() + 1) < 10 ? ('0'+(currentdate.getMonth() + 1)) : (currentdate.getMonth() + 1);
    let YearNew = currentdate.getFullYear();
	let chargingContainer = document.getElementById('chargingContainer').value;
	customConfirm('Have you created and saved excel spreadsheet? These entries will be marked with the Date/Time stamp of '+DayNew+'/'+MonthNew+'/'+YearNew+'. Do you wish to proceed?',
		function(){
		  $.ajax({
				  type: 'POST',
				  url: 'page-includes/admin/charging/index.php',
				  data: {conrollerName:'sendChargingToFinanceSave',chargingContainer:chargingContainer},
				  success: function (returnHtml){
					if(returnHtml == 'Successs.')
					{
						setTimeout(function() {formHandler('filterByDateRange');}, 1000);
					}else
					{
						setTimeout(function() {customAlert(returnHtml);}, 1000);
					}
				}
			});
		},
		function() {
		}
	);
}
if($('#existingCharges').val() == '')
{
	document.getElementById('showBtnExistingCharges').disabled = true;
}
function createDocs(chargingContainer)
{
    if($("#showBtnExistingCharges").is(":disabled")) {
        document.getElementById('markAsSent').disabled = false;
    }
	$.ajax({
		type: 'POST',
		url: 'page-includes/admin/charging/chargingExport.php',
	data:{'chargingContainer':chargingContainer, 'orderBy':btoa(table.order())},
		success: function() {
			location = 'page-includes/admin/charging/excel/Charging to Finance_Allocate.xlsx';
		}
	});
}
</script>
<style>
.dataTables_wrapper .dataTables_paginate{
	visibility:visible !important;
}
</style>