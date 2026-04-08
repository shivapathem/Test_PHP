<?php
// set error_log

// echo "\n error_log:: ", ini_get('error_log');
$logPath = ini_get('error_log');
if(!empty($logPath)){
  $logPath = explode(DIRECTORY_SEPARATOR, $logPath);
  // echo "\n<br> filename:: ", $logPath[sizeof($logPath) -1],
  $logPath[sizeof($logPath) -1] = 'php_error.' . date('Y-m-d') . '.log';
  // echo "\n <br> new log file:: ", implode(DIRECTORY_SEPARATOR, $logPath);

  ini_set('error_log', implode(DIRECTORY_SEPARATOR, $logPath));

}

/* loading environment variables and configurations   */
require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../function-includes/bootstrap.php');
$dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();

$dotenv->required(['ALLOCATE_DB_HOST', 'ALLOCATE_DB_NAME', 'SQL_SERVER_TrustServerCertificate',
  'SQL_SERVER_Encrypted_CONNECTION', 'SQL_SERVER_AUTH_MODE']);

if(strtoupper(getenv('SQL_SERVER_AUTH_MODE')) === 'NONE')
{
    $dotenv->required(['ALLOCATE_DB_USER', 'ALLOCATE_DB_PWD']);
}elseif(strtoupper(getenv('SQL_SERVER_AUTH_MODE')) !== 'WINDOWS'){
    Throw new Exception("Invalid value defined in env file for SQL_SERVER_AUTH_MODE");
}


function OpenDatabase() {
    $myServer = getenv('ALLOCATE_DB_HOST');
    //connection to the database
	$connectionInfo = [
		'APP' => "ALLOCATE7",
		"Database" => getenv('ALLOCATE_DB_NAME'),
		"TrustServerCertificate" => getenv('SQL_SERVER_TrustServerCertificate'),
		"Encrypt" => getenv('SQL_SERVER_Encrypted_CONNECTION')
	];
	if(getenv('MULTI_SUBNET_FAILOVER') == 'True')
	{
		$connectionInfo["MultiSubnetFailover"] = getenv('MULTI_SUBNET_FAILOVER');
	}
    if(strtoupper(getenv('SQL_SERVER_AUTH_MODE')) == 'NONE')
    {
        $connectionInfo['UID'] = getenv('ALLOCATE_DB_USER');
        $connectionInfo['PWD'] = getenv('ALLOCATE_DB_PWD');
    }

    //connection to the database
    $dbhandle = sqlsrv_connect($myServer, $connectionInfo)
        or die("1 Couldn't connect to SQL Server on $myServer");
    return ($dbhandle);
}

function OpenDatabaseTeampay() {
    $myServer = getenv('ALLOCATE_DB_HOST');

      //connection to the database
    $connectionInfo = [
        'APP' => "ALLOCATE7",
        "Database" => getenv('TEAMPAY_DB_NAME'),
        "TrustServerCertificate" => getenv('SQL_SERVER_TrustServerCertificate'),
        "Encrypt" => getenv('SQL_SERVER_Encrypted_CONNECTION')

    ];

    if(strtoupper(getenv('SQL_SERVER_AUTH_MODE')) == 'NONE')
    {
        $connectionInfo['UID'] = getenv('ALLOCATE_DB_USER');
        $connectionInfo['PWD'] = getenv('ALLOCATE_DB_PWD');
    }

    //connection to the database
    $dbhandle = sqlsrv_connect($myServer, $connectionInfo)
        or die("1 Couldn't connect to SQL Server on $myServer");

    return ($dbhandle);

}
function OpenDBLink() {

    $sql_server = getenv('ALLOCATE_DB_HOST');
    $sql_username = getenv('ALLOCATE_DB_USER');
    $sql_password = getenv('ALLOCATE_DB_PWD');
    $sql_database = getenv('ALLOCATE_DB_NAME');
    try {
        $pdo = new PDO("sqlsrv:server=$sql_server;MultiSubnetFailover=".getenv('MULTI_SUBNET_FAILOVER').";Database=$sql_database",$sql_username,$sql_password,['ReturnDatesAsStrings'=>true]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        die("Database Connection Error");
    }
    return $pdo;
}

  $dowMap = array(
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
  );

  $invdowMap = array(
      0 => "Saturday",
      1 => "Sunday",
      2 => "Monday",
      3 => "Tuesday",
      4 => "Wednesday",
      5 => "Thursday",
      6 => "Friday"
  );

function doimport($sortcode) {

	 // What is the base?
	$doit = 0;
	// Only send for NBH people
	if (!(strpos($sortcode, "BH") === false)) {
		$doit = 1;
	}
	// Now exclude people with NR in the sort code
	if (!(strpos($sortcode, "NR") === false)) {
		$doit = 0;
	}
	return ($doit);
}
// ****************************************************************************************************************



// ****************************************************************************************************************'
function getDaysInWeek ($weekNumber, $year, $dayStart = 1) {
    // Count from '0104' because January 4th is always in week 1
    // (according to ISO 8601).
    $time = strtotime($year . '0104 +' . ($weekNumber - 1).' weeks');
    // Get the time of the first day of the week
    $dayTime = strtotime('-' . (date('w', $time) - $dayStart) . ' days', $time);
    // Get the times of days 0 -> 6
    $dayTimes = array ();
    for ($i = 0; $i < 7; ++$i) {
      $dayTimes[] = strtotime('+' . $i . ' days', $dayTime);
    }
    // Return timestamps for mon-sun.
    return $dayTimes;
}

function bbcweeknumber($date) {
  date_default_timezone_set('UTC');
  $unixdate = strtotime($date);
  $year = date("Y", strtotime($date));
  $yearstart =  getstartofyear($year);
  $weekcount = getweeksinyear($year);
  $weekssincestart =  floor(($unixdate - $yearstart) / (24 * 60 * 60 * 7));

  // If zero then use last week of previous year
  if ($weekssincestart == -1) {
    $weekcount = getweeksinyear($year - 1);
    $WeekNumber = ($year-1).sprintf("%02d",$weekcount);
  }
  else {
    if ($weekssincestart >= $weekcount) {
     $WeekNumber = ($year + 1)."01";

    }
    else {
     $WeekNumber = $year.sprintf("%02d",$weekssincestart + 1);
    }
  }

  date_default_timezone_set('Europe/London');

  return $WeekNumber;
}

function bbcweeknumberrota($date) {
    $Newdate = str_replace('/','-',$date);
    date_default_timezone_set('UTC');
    $unixdate = strtotime($Newdate);
    $year = date("Y", strtotime($Newdate));
    $yearstart =  getstartofyear($year);
    $weekcount = getweeksinyear($year);
    $weekssincestart =  floor(($unixdate - $yearstart) / (24 * 60 * 60 * 7));
    // If zero then use last week of previous year
    if ($weekssincestart == -1) {
        $weekcount = getweeksinyear($year - 1);
        $WeekNumber = ($year-1).sprintf("%02d",$weekcount);
    }
    else {
        if ($weekssincestart >= $weekcount) {
            $WeekNumber = ($year + 1)."01";

        }
        else {
            $WeekNumber = $year.sprintf("%02d",$weekssincestart + 1);
        }
    }
    return $WeekNumber;
}
// ****************************************************************************************************************'
 function getstartofyear ($year) {
	$dowMap = array(
	  "Sat"  => 0,
	  "Sun"  => 1,
	  "Mon"  => 2,
	  "Tue"  => 3,
	  "Wed"  => 4,
	  "Thu"  => 5,
	  "Fri"  => 6
	);
	date_default_timezone_set('UTC');
	$unixstartofyear = strtotime($year.'-01-01');
	// Weekday for the first day of the year
	$weekday = $dowMap[date('D',$unixstartofyear)];
	if ($weekday > 3) {
		while ($dowMap[date('D',$unixstartofyear)] != 0) {
			$unixstartofyear = strtotime("+1 day", $unixstartofyear);
		}
	}
	else {
		while ($dowMap[date('D',$unixstartofyear)] != 0) {
			$unixstartofyear = strtotime("-1 day", $unixstartofyear);
		}
	}
	return $unixstartofyear;
}
// ****************************************************************************************************************'
 function getweeksinyear($year) {

	$start = getstartofyear($year);
	$end = getstartofyear($year + 1);
	$weeks = ($end - $start) / (24 * 60 * 60 * 7);
	return $weeks;
}

// ****************************************************************************************************************'

function spinweek ($week) {
	$year = substr($week, 0, 4);
	$weekonly = substr($week, 4, 2);
	return ($weekonly."/".$year);

}


function FormatTimeDiff ($dteStart, $dteEnd) {
    $intStart = strtotime($dteStart);
    $intEnd = strtotime($dteEnd);
    $intDiff = $intEnd - $intStart;
    return (sprintf('%02d:%02d', ($intDiff / 3600), ($intDiff / 60 % 60)));
  }
  function FormatTimeDiffIncSeconds ($dteStart, $dteEnd) {
    $intStart = strtotime($dteStart);
    $intEnd = strtotime($dteEnd);
    $intDiff = $intEnd - $intStart;
    return (sprintf('%02d:%02d:%02d', ($intDiff / 3600), ($intDiff / 60 % 60), $intDiff % 60));
  }
  function SecondsToHoursDecimal ($dteTime) {
    return round(($dteTime / 3600) ,2);
  }


  /**
* This function is used to convert time into seconds from hours.
*
* @param $str_time This param contains the Time in hours
*
* @return $seconds Returns the seconds
*/
  function seconds_from_time($str_time) {
    $arrdur = explode(":", $str_time);
    $seconds = ($arrdur[0] * 3600);
    if (isset($arrdur[1])) {
       $seconds = $seconds + ($arrdur[1] * 60);
    }
    if (isset($arrdur[2])) {
       $seconds = $seconds + $arrdur[2];
    }
    return $seconds;
  }

// ****************************************************************************************************************'

function datefromweek ($week, $day = 0) {

	$dowMap = array (
					"Sat"  => 0,
					"Sun"  => 1,
					"Mon"  => 2,
					"Tue"  => 3,
					"Wed"  => 4,
					"Thu"  => 5,
					"Fri"  => 6
				  );


	$year = substr($week ?? '', 0, 4);
	$weekonly = substr($week ?? '', 4, 2);
	$unixstartofyear = strtotime($year.'-01-01');
	// Weekday for the first day of the year
	$weekday = $dowMap[date('D',$unixstartofyear)];
	if ($weekday > 3) {
		while ($dowMap[date('D',$unixstartofyear)] != 0) {
		  $unixstartofyear = strtotime("+1 day", $unixstartofyear);
		}
	}
	else {
		while ($dowMap[date('D',$unixstartofyear)] != 0) {
		  $unixstartofyear = strtotime("-1 day", $unixstartofyear);
		}
	}

	$startofweek = strtotime("+".(intval($weekonly) - 1)." weeks", $unixstartofyear);
	$realdate = date("Y-m-d", strtotime("+$day day", $startofweek));
	return ($realdate);
}

// ****************************************************************************************************************'
function DayFromDate ($dteDate) {
  $dowMap = array (
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
   );

  $intDay = $dowMap[date("D", strtotime($dteDate))];
  return($intDay);

}


function secondsToTime($seconds)
{
    // extract hours
    $hours = floor($seconds / (60 * 60));
    if ($hours >= 24 ) {
      $hours = $hours - 24;
    }
    // extract minutes
    $divisor_for_minutes = $seconds % (60 * 60);
    $minutes = floor($divisor_for_minutes / 60);
    $minutes = ceil($minutes / 15) * 15;
    if ($minutes==60) {
      $minutes = 0;
    }
    $hours = sprintf("%02s",$hours);
    $minutes = sprintf("%02s",$minutes);
    return $hours.':'.$minutes;
}

function getrotaweek($week, $rotastarts, $weeksinrota) {
	$dayscount = round((strtotime(datefromweek($week)) - strtotime(datefromweek($rotastarts))) / (60*60*24)) ;
	$weekscount = floor($dayscount / 7);
	$weekofrota = ($weekscount % $weeksinrota) + 1;
	if ($weekofrota <= 0) {
		$weekofrota = $weeksinrota + $weekofrota;
	}
	return ($weekofrota);
}

// ****************************************************************************************************************'

function romanNumerals($num)
{
    $n = intval($num);
    $res = '';

    /*** roman_numerals array  ***/
    $roman_numerals = array(
                'M'  => 1000,
                'CM' => 900,
                'D'  => 500,
                'CD' => 400,
                'C'  => 100,
                'XC' => 90,
                'L'  => 50,
                'XL' => 40,
                'X'  => 10,
                'IX' => 9,
                'V'  => 5,
                'IV' => 4,
                'I'  => 1);

    foreach ($roman_numerals as $roman => $number)
    {
        /*** divide to get  matches ***/
        $matches = intval($n / $number);

        /*** assign the roman char * $matches ***/
        $res .= str_repeat($roman, $matches);

        /*** substract from the number ***/
        $n = $n % $number;
    }

    /*** return the res ***/
    return $res;
    }

// ****************************************************************************************************************'

function bbc_GetFromLDAP($username='') {

// $G_AppConfig->ldapDomains['core.bbc.co.uk']->username='XXXXXXXxxxxxxxxxxxxxx';
// $G_AppConfig->ldapDomains['core.bbc.co.uk']->password='XXXXXXXxxxxxxxxxxxxxx';
// $G_AppConfig->ldapDomains['worldwide.bbc.co.uk']->username='XXXXXXXxxxxxxxxxxxxxx';
// $G_AppConfig->ldapDomains['worldwide.bbc.co.uk']->password='XXXXXXXxxxxxxxxxxxxxx';
// $G_AppConfig->ldapDomains['global.mon.bbc.co.uk']->username='XXXXXXXxxxxxxxxxxxxxx';
// $G_AppConfig->ldapDomains['global.mon.bbc.co.uk']->password='XXXXXXXxxxxxxxxxxxxxx';

	#
	# prepend $info values with @ to protect in the event of param not defined in AD
	#
	foreach ($G_AppConfig->ldapDomains as $ldap_server=>$ldap_auth) {
		unset($con);
		if ($con=ldap_connect('ldap://' . $ldap_server . ':' . 3268)) {
			ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
			if ($bind=@ldap_bind($con, $ldap_auth->username, $ldap_auth->password)) {
				if ($srch=@ldap_search($con, 'dc=bbc,dc=co,dc=uk', "sAMAccountName=$username")) {
					if ($info=ldap_get_entries($con, $srch)) {
						$ldap=@$info[0]['mail'][0];
						//$ldap[1]=@$info[0]['cn'][0];
						break;
					}
				}
			}
		}
	}
	if (isset($ldap)) return($ldap);
}
// ****************************************************************************************************************'


function ms_escape_string($data) {
        if ( !isset($data) or empty($data) ) return '';
        if ( is_numeric($data) ) return $data;

        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );
        foreach ( $non_displayables as $regex )
            $data = preg_replace( $regex, '', $data );
        $data = str_replace("'", "''", $data );
        return $data;
    }

function addweeks($startweek, $weekstoadd) {
	$daystoadd = ($weekstoadd * 7) + 1;
	$startdate = datefromweek($startweek);
	$enddate = date("Y-m-d", strtotime("+".$daystoadd." days", strtotime($startdate)));
	$endweek = bbcweeknumber($enddate);
	return ($endweek);
}

function authUser()
{
    $authUser = $_SERVER['AUTH_USER'];
    $authType = $_SERVER['AUTH_TYPE'];
    //$httpAuth = $_SERVER['HTTP_AUTHORIZATION'];
    //echo  $_SERVER['HTTP_AUTHORIZATION'];exit;
    //if(isset($authUser) && $authType == 'Negotiate' && strlen($httpAuth) > 25)
    if(isset($authUser) && ($authType == 'Negotiate' || strtolower($authType) == 'ntlm'))
    {
        $arrUser = explode("\\", $authUser);
        return $arrUser[1];
    }
    else
        return false;

}
/**
  * A function to obtain last insert ID after an insert statement
*/

function sql_last_insert_id($queryResult){
    sqlsrv_next_result($queryResult);
    sqlsrv_fetch($queryResult);
    return sqlsrv_get_field($queryResult, 0);

}
//formattime
function FormatTime ($dteTime) {
  return (sprintf('%02d:%02d', ($dteTime / 3600), ($dteTime / 60 % 60)));
}

/**
 * This function is used to convert time into seconds
 *
 * @param $str_time This param contains the time information in (H:i) format
 *
 * @return Integer Return seconds
 */
function secondsFromTime($str_time) {
  $arrdur = explode(":", $str_time);
  $seconds = ($arrdur[0] * 3600);
  if (isset($arrdur[1])) {
     $seconds = $seconds + ($arrdur[1] * 60);
  }
  if (isset($arrdur[2])) {
     $seconds = $seconds + $arrdur[2];
  }
  return $seconds;
}

?>