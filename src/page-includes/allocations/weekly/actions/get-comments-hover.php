<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
$request = Request::createFromGlobals();
$service = new AllocationService();
$data = $service->getAllocationComments($request);
if (!$data) {
	$data = array('DutyName' => '', 'DutyComments' => '', 'PersonComments' => '');
}
if (isset($data['DutyComments']) && strpos($data['DutyComments'], '__COMMENT_SEPARETOR__') !== false) {
	$commentArr = explode('__COMMENT_SEPARETOR__', $data['DutyComments']);
	$data['PersonComments'] = isset($commentArr[0]) ? $commentArr[0] : '';
	$data['DutyComments'] = isset($commentArr[1]) ? $commentArr[1] : '';
}
echo '<table class="tablesmalltidy tooltip-table" width="400px">';
echo '<tr height="50px">';
echo '<th colspan="2">Comments for Duty: ' . $data['DutyName'] . '</th>';
echo '</tr>';
if ($data['DutyComments'] != '') {
	echo '<tr>';
	echo '<td valign="top">Duty<br>Comments</td>';
	echo '<td>';
	echo nl2br($data['DutyComments']);
	echo '</td>';
	echo '</tr>';
}
if ($data['PersonComments'] != '') {
	echo '<tr class="tooltip-table-background">';
	echo '<td valign="top">Person<br>Comments</td>';
	echo '<td>';
	echo nl2br($data['PersonComments']);
	echo '</td>';
	echo '</tr>';
}
echo '</table>';