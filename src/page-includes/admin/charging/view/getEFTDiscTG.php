
<div class="teamPayRecordSection" id="getPayTypeTGContainer" >
<form name="mainForm" id="mainForm" onSubmit="return false;" >
<table id="leaversRecordEntries" class="oddevenclass tablesmall" style="width:100%">
	<thead>
		<tr>
			<th>Staff ID</th>
			<th>Display Name</th>
			<th>Department</th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Part Time EDP</th>
			<th>EDP Min Exc., Breaks</th>
			<th>Week</th>
			<th>Contract EFT</th>
			<th>Config EFT</th>
			<th>Term Time Hours</th>
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
				<td><?php echo $dataVal['StaffID']; ?></td>
				<td><?php echo $dataVal['DisplayName']; ?></td>
				<td><?php echo $dataVal['Department']; ?></td>
				<td style="white-space: nowrap;"><?php echo date('d-m-Y', strtotime($dataVal['Startdate'])); ?></td>
				<td style="white-space: nowrap;"><?php echo date('d-m-Y', strtotime($dataVal['Endate'])); ?></td>
				<td style="white-space: nowrap; <?php echo $endDateStyle; ?>"><?php echo $dataVal['PartTimeEDP']; ?></td>
				<td ><?php echo $dataVal['EDPMinimumExcBreaks']; ?></td>
				<td ><?php echo $dataVal['Week']; ?></td>
				<td ><?php echo $dataVal['ContractEFT']; ?></td>
				<td ><?php echo $dataVal['ConfigEFT']; ?></td>
				<td ><?php echo $dataVal['TermTimeHours']; ?></td>
				<td ><?php echo $dataVal['IsLeaver']; ?></td>
			</tr>
		<?php
			}
		}else
		{
			echo '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td>No Record Found.</td><td></td><td></td><td></td><td></td><td></td></tr>';
		}
		?>
	</tbody>
</table>
<input type="hidden" id="formName" name="formName" value="getEFTDiscTG" >
</form>
</div>
