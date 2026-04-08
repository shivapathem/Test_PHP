<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$service = new AllocationService();

$type = $request->get('type');
$filterName = $request->get('filterName');
$userId = $request->get('userId');
$SchedulingTeamId = $request->get('SchedulingTeamId');
$isPublic = $request->get('isPublic');
$isActive = $request->get('isActive');
$detailsShow = $request->get('detailsShow');
$showTotalCount = $request->get('showTotalCount');
$colA = $request->get('colA');
$colB = $request->get('colB');
$colC = $request->get('colC');
$colD = $request->get('colD');
$colE = $request->get('colE');
$colF = $request->get('colF');
$colG = $request->get('colG');
$colH = $request->get('colH');
$colI = $request->get('colI');
$colJ = $request->get('colJ');
$colK = $request->get('colK');
$colL = $request->get('colL');
$colM = $request->get('colM');
$colN = $request->get('colN');
$colO = $request->get('colO');
$colP = $request->get('colP');
$colQ = $request->get('colQ');
$colR = $request->get('colR');
$colS = $request->get('colS');
$colT = $request->get('colT');
$colU = $request->get('colU');
$colV = $request->get('colV');
$colW = $request->get('colW');
$colX = $request->get('colX');
$colY = $request->get('colY');
$colZ = $request->get('colZ');
?>

<table class="tablesmalltidy" width="100%">
	<tr>
		<td>You are about to update the criteria for an existing Filter. Are you sure you wish to continue ?</td>
	</tr>
	<tr>
		<td align="center"><input type="button" value="Update" onclick="saveDutyShiftCountingFilter(<?php echo $userId?>,<?php echo $SchedulingTeamId?>,'<?php echo $type?>','<?php echo $filterName?>',<?php echo $isPublic?>,<?php echo $isActive;?>,<?php echo $detailsShow?>,<?php echo $showTotalCount?>,<?php echo $colA?>,<?php echo $colB?>,<?php echo $colC?>,<?php echo $colD?>,<?php echo $colE?>,<?php echo $colF?>,<?php echo $colG?>,<?php echo $colH?>,<?php echo $colI?>,<?php echo $colJ?>,<?php echo $colK?>,<?php echo $colL?>,<?php echo $colM?>,<?php echo $colN?>,<?php echo $colO?>,<?php echo $colP?>,<?php echo $colQ?>,<?php echo $colR?>,<?php echo $colS?>,<?php echo $colT?>,<?php echo $colU?>,<?php echo $colV?>,<?php echo $colW?>,<?php echo $colX?>,<?php echo $colY?>,<?php echo $colZ?>)">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>
	</tr>
</table>
