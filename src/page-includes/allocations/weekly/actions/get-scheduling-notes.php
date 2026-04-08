<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ .'/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$data = $service->getSchedulingNotes($request);

echo'<table class="tablesmalltidy tooltip-table" width="400px">';
	echo '<tr height="50px">';
		echo '<th colspan="2">Scheduling notes</th>';
	echo '</tr>';
	if ($data['FWANotes'] != '')
	{
		echo '<tr>';
			echo '<td>';
				echo nl2br($data['FWANotes']);
			echo '</td>';
		echo '</tr>';
	}
	else
	{
		echo '<tr>';
			echo '<td>';
				echo 'No Record Found.';
			echo '</td>';
		echo '</tr>';
	}
echo '</table>';