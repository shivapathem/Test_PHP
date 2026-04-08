<?php

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once '../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../weekly/service/AllocationRepository.php';
$request = Request::createFromGlobals();
$date = $request->get('date');
$teamId = $request->get('teamId');
$repository = new AllocationRepository();
$existingData = $repository->getDateComment($request->get('teamId'), [$request->get('date')]);
$existingData = $existingData[0] ?? [];
?>

<table id="date-comment-history-modal" class="redtable smalltable bluetable" width="100%" style="width: 750px">
	<thead><tr role="row"><th style="text-align:center;">History</th></tr></thead>
	<tbody>
		<?php if(empty($existingData['History'])) { ?>
			<tr role="row" class="odd">
				<td style="width:703px; white-space: pre-wrap;">No history<br><br></td>
			</tr>
			<?php } else {
			foreach(array_reverse(json_decode($existingData['History'], true) ?? []) as $key => $history )	{
			?>
				<tr role="row" class="<?php echo ($key%2) == 0 ? 'odd' :'even' ?>">
					<td style="width:703px; white-space: pre-wrap;word-break:break-all;"><?php echo $history; ?><br></td>
				</tr>
			<?php } } ?>
	</tbody>
</table>