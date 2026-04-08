<?php

/**
	* connect to factconfig database and returns the connection object
*/
function connect_to_factconfig(){
	$dbhost = getenv('FACTCONFIG_DB_HOST');
	$connectionInfo = get_factconfig_connection_params();

	$dbhandle = sqlsrv_connect($dbhost, $connectionInfo)
        or die("1 Couldn't connect to SQL Server on $dbhost");

    return ($dbhandle);
}


/**
	* Check the paramrs in env file and returns the connection params
	* @return array.
*/
function get_factconfig_connection_params(){
	//sanity check
	if(empty(getenv('FACTCONFIG_DB_HOST')) || empty(getenv('FACTCONFIG_DB_NAME')) ){
		$error = 'You have not supplied any values for FACTCONFIG_DB_HOST or FACTCONFIG_DB_NAME' .
				'A hostname and database name is mandatory' .
				"\n Please check you env file for correct configuration";
		Throw new Exception($error);exit;
	}

	// all well
	$params = [
        'APP' => "ALLOCATE7",
        "Database" => getenv('FACTCONFIG_DB_NAME'),
        "TrustServerCertificate" => getenv('FACTCONFIG_SQL_SERVER_TrustServerCertificate'),
        "Encrypt" => getenv('FACTCONFIG_SQL_SERVER_Encrypted_CONNECTION')
    ];

    // check auth mode for SQL server
    if(strtoupper(getenv('FACTCONFIG_SQL_SERVER_AUTH_MODE')) == 'NONE')
    {
    	if(empty(getenv('FACTCONFIG_DB_USER')) || empty(getenv('FACTCONFIG_DB_PWD')) ){
    		$error = 'You SQL Server auth mode is not "WINDOWS" and you have not supplied values for '.
    				'FACTCONFIG_DB_USER or FACTCONFIG_DB_PWD'.
    				"\n Please check you env file for correct configuration";
    		Throw new Exception($error);exit;
    	}else{
    		$params['UID'] = getenv('FACTCONFIG_DB_USER');
       	 	$params['PWD'] = getenv('FACTCONFIG_DB_PWD');
    	}

    }
    elseif(strtoupper(getenv('FACTCONFIG_SQL_SERVER_AUTH_MODE')) !== 'WINDOWS'){
    	Throw new Exception("Invalid value defined in env file for FACTCONFIG_SQL_SERVER_AUTH_MODE");
	}
	return $params;

}


/**
	*@param $deptId department Id for which the
*/
function find_ms_access_details_for_department($deptId){
	// execute the stored procedure [usp_GetAllocateDepartmentValues]
	$db = connect_to_factconfig();

	$sql = "exec [dbo].[usp_GetAllocateDepartmentValues] @intDepartmentID = ?";
	$procParams = [[$deptId, SQLSRV_PARAM_IN]];

	$stmt = sqlsrv_prepare($db, $sql, $procParams);

	if (!sqlsrv_execute($stmt)) {
	    error_log(print_r(sqlsrv_errors(), 1));
	    return [];
	}

	$departmentInfo = sqlsrv_fetch_array($stmt);	// the department ID is unique so the resultset must have only one row.
	// error_log(print_r($departmentInfo,1));
	sqlsrv_free_stmt($stmt);
	sqlsrv_close($db);
	return $departmentInfo;

}


/**
	*@param $filePath, path fo the MS access database file which we want to connect
	*@param $filePassword, password for the MS access file if its encrypted. can be null if no password
	*@return $db the connection objec to MS ACCESS. If no connection the boolean false
*/
function msaccess_connect($filePath, $filePassword){

	$root = $_SERVER['DOCUMENT_ROOT'];
	require_once($root . DIRECTORY_SEPARATOR . 'vendor/adodb/adodb-php/adodb.inc.php');

	$db = ADONewConnection('ado_access');
	//$db->debug = true;		// uncomment this line to degub issues here.
	$dns = 'PROVIDER=Microsoft.ACE.OLEDB.12.0;DATA SOURCE='. $filePath . ';';
	if(!empty($filePassword)){
		$dns .= 'Jet OLEDB:Database Password=' . $filePassword . ';';
	}
  //echo $dns;
	$db->Connect($dns);

	if($db->isConnected()){
		return $db;
	}
	else{
		$e = $db->errorMsg();
		error_log("\n " . print_r($e, 1));
		error_log("\n connection string:: " .$dns);
    print_r($e);
    
		Throw new Exception("Unable to connect to MS Access database." .
					" \n Please check error logs for more details");
		return false;
	}

}
