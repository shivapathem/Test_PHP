<?php

// include db-adapter
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root . DIRECTORY_SEPARATOR . 'function-includes/init.php');
require_once($root . DIRECTORY_SEPARATOR . 'function-includes/ms-access/db-adapter.php');

/**
	*@param $weekNumber, string
	*@param $weekDay, int
	*@param $deptId, int department Id
*/     




function find_allocation_info($weekNumber, $weekDay, $deptId){

	$deptDetails = find_ms_access_details_for_department($deptId);
  $filePath = $deptDetails['AccessDataFilePath'] . trim($deptDetails['DatabaseName']) . '.accdb';
	$filePassword = $deptDetails['AllocateAccessPassword'];
  //$filePath = "\\\\fgbw1aprot1001.national.core.bbc.co.uk\Databases\Radio\\".trim($deptDetails['DatabaseName']) . '.accdb';
    //\\NGBWCFS1008.national.core.bbc.co.uk\AccessDB_Live\Radio\
    
    
	// now call ms access to get actual details.
	$dbHandler = msaccess_connect($filePath, $filePassword);

	// execute query and get the result set.
	$sql = 'SELECT Allocations.[Duty Name] as dutyname, Weeks.[Sort Code] as sortcode, Weeks.[Name] as username, Allocations.[Staff Number] as staffnumber FROM Allocations' ;

	$sql .= " INNER JOIN Weeks ON (Allocations.[Week] = Weeks.[Week]) AND (Allocations.[Staff Number] = Weeks.[Staff Number])";
	$sql .= ' WHERE (Allocations.[Week]=?)';
	$sql .= ' AND (Allocations.[Day]=?)';
	$sql .= ' ORDER BY Weeks.[Name];';

	$bindParams = [
		$dbHandler->addQ($weekNumber),
		(int)$dbHandler->addQ($weekDay)
	];

	$dbHandler->setFetchMode(ADODB_FETCH_ASSOC);
	$rs = $dbHandler->Execute($sql, $bindParams);
	$returnvalues = [];

	while(!$rs->EOF){
		$returnvalues[] = $rs->fields;
		// print_r($rs->fields);
		// echo "\n <br> ---------------------------------------------";
		$rs->MoveNext();

	}
	$dbHandler->close();
	// print_r($returnvalues);
	return $returnvalues;
}


/**
	*@param $weekNumber, string
	*@param $dutyName, String
	*@param $deptId, int department Id
*/
function find_duty_count($weekNumber, $dutyName, $deptId){
	$dutyLength = strlen($dutyName);

	$deptDetails = find_ms_access_details_for_department($deptId);
	$filePath = $deptDetails['AccessDataFilePath'] . trim($deptDetails['DatabaseName']) . '.accdb';
	$filePassword = $deptDetails['AllocateAccessPassword'];

	// now call ms access to get actual details.
	$dbHandler = msaccess_connect($filePath, $filePassword);

	$sql ="SELECT Allocations.[Staff Number] AS staffnumber, Count(Allocations.[Duty Name]) AS dutycount";
	$sql .= " FROM Allocations";
	$sql .= " WHERE Allocations.Week>=?";
	$sql .= " AND Left([Duty Name], ? )= ?";
	$sql .= " GROUP BY Allocations.[Staff Number]";
	$sql .= " HAVING (Not (Allocations.[Staff Number]) Is Null);";

	$bindParams = [
		$dbHandler->addQ($weekNumber),
		$dbHandler->addQ($dutyLength),
		$dbHandler->addQ($dutyName)
	];

	$dbHandler->setFetchMode(ADODB_FETCH_ASSOC);
	$rs = $dbHandler->Execute($sql, $bindParams);
	$returnvalues = [];

	while(!$rs->EOF){
		$returnvalues[] = $rs->fields;
		// print_r($rs->fields);
		// echo "\n <br> ---------------------------------------------";
		$rs->MoveNext();

	}
	$dbHandler->close();
	// print_r($returnvalues);
	return $returnvalues;

}

// find_allocation_info('201818', 5, 3);
// echo "\n =====================================";
// find_duty_count('201745', 'ATV Late News', 3);