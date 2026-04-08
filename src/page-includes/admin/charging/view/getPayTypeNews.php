
<div class="teamPayRecordSection" id="getPayTypeTGContainer" >
<form name="mainForm" id="mainForm" onSubmit="return false;" >
<table id="leaversRecordEntries" class="oddevenclass tablesmall" style="width:100%">
	<thead>
		<tr>
			<th>Department</th>
			<th>Staff ID</th>
			<th>Surname</th>
			<th>Forename</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Work Pattern</th>
			<th>Payment Type Name</th>
			<th>Is Leaver ?</th>
		</tr>
	</thead>
	<tbody>
	<div id="getPayTypeNewsPos"></div>
	<?php
		$endDateStyle = $endDateStyle ?? '';
		if(count($data[0]) > 0)
		{
			foreach($data[0] as $dataVal)
			{
	?>
			<tr >
				<td><?php echo $dataVal['Department']; ?></td>
				<td><?php echo $dataVal['StaffID']; ?></td>
				<td><?php echo $dataVal['Surname']; ?></td>
				<td><?php echo $dataVal['Forename']; ?></td>
				<td style="white-space: nowrap;"><?php echo date('d-m-Y', strtotime($dataVal['Startdate'])); ?></td>
				<td style="white-space: nowrap;"><?php echo date('d-m-Y', strtotime($dataVal['Endate'])); ?></td>
				<td style="white-space: nowrap; <?php echo $endDateStyle; ?>"><?php echo $dataVal['WorkPattern']; ?></td>
				<td ><?php echo $dataVal['PaymentTypeName']; ?></td>
				<td ><?php echo $dataVal['isLeaver']; ?></td>
			</tr>
		<?php
			}
		}else
		{
			echo '<tr><td></td><td></td><td></td><td></td><td style="text-align:right;">No Record Found.</td><td></td><td></td><td></td><td></td></tr>';
		}
		?>
	</tbody>
</table>
<input type="hidden" id="formName" name="formName" value="getPayTypeTG" >
</form>
</div>
