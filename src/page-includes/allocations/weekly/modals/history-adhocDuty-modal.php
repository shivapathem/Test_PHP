<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../service/AllocationService.php';
include_once '../../../../function-includes/DB_Functions.php';

$request = Request::createFromGlobals();
$historyResults = json_decode(getHistoryLists('AdhocDuty', $request->get("ID")), true);

?>
<table class="tablesmalltidy" width="100%">
	<tr height="30px">
		<th><b>History<b></th>
	</tr>
	<tr>
		<td><hr></td>
	</tr>
	<?php if(!empty($historyResults)){foreach($historyResults as $key => $value){?>
	<tr>
		<td><?php echo $value['History'];?></td>
	</tr>
	<?php }} else {?>
	<tr>
		<td>There is no History for this Duty yet!</td>
	</tr>
	<?php }?>
	<tr>
		<td align="center"><input type="button" value="Close" onclick="cancel()"></td>
	</tr>
</table>
