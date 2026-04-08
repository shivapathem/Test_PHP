<?php
require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../function-includes/bootstrap.php');
$dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();
include_once __DIR__.'/../class-includes/db.php';
$dotenv->required(['ALLOCATE_DB_HOST', 'ALLOCATE_DB_NAME', 'SQL_SERVER_TrustServerCertificate',
    'SQL_SERVER_Encrypted_CONNECTION', 'SQL_SERVER_AUTH_MODE']);

// Allow for SQL Server authentication
if (strtoupper(getenv('SQL_SERVER_AUTH_MODE')) == 'NONE') {
    $dotenv->required(['ALLOCATE_DB_USER', 'ALLOCATE_DB_PWD']);
    $sql_server = getenv('ALLOCATE_DB_HOST');
    $sql_username = getenv('ALLOCATE_DB_USER');
    $sql_password = getenv('ALLOCATE_DB_PWD');
    $sql_database = getenv('ALLOCATE_DB_NAME');
}
// If not using authentication Windows
else if (strtoupper(getenv('SQL_SERVER_AUTH_MODE')) != 'WINDOWS') {
    echo "Invalid value defined in env file for SQL_SERVER_AUTH_MODE";
}

function OpenDBLinkA7()
{
    if (strtoupper(getenv('SQL_SERVER_AUTH_MODE')) == 'NONE') {

        $sql_server = getenv('ALLOCATE_DB_HOST');
        $sql_username = getenv('ALLOCATE_DB_USER');
        $sql_password = getenv('ALLOCATE_DB_PWD');
        $sql_database = getenv('ALLOCATE_DB_NAME');
    }
    else if (strtoupper(getenv('SQL_SERVER_AUTH_MODE')) == 'WINDOWS') {
        $sql_server = getenv('ALLOCATE_DB_HOST');
        $sql_username = null;
        $sql_password = null;
        $sql_database = getenv('ALLOCATE_DB_NAME');
    }
    else {
        echo "Invalid value defined in env file for SQL_SERVER_AUTH_MODE";
    }
    try {
        //$pdo = new PDO("sqlsrv:server=$sql_server;Database=$sql_database", $sql_username, $sql_password, ['ReturnDatesAsStrings' => true]);
        //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo= DB::OpenDBLinkA7();
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        die("Database Connection Error");
    }
    return $pdo;
}

function OpenDBLinkTeamWork() {
    $sql_server = getenv('ALLOCATE_DB_HOST');
    $sql_username = getenv('ALLOCATE_DB_USER');
    $sql_password = getenv('ALLOCATE_DB_PWD');
    $sql_database = "Teamwork_Dev";
    try {
        $pdo = new PDO("sqlsrv:server=$sql_server;MultiSubnetFailover=".getenv('MULTI_SUBNET_FAILOVER').";Database=$sql_database",$sql_username,$sql_password,['ReturnDatesAsStrings'=>true]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        die("Database Connection Error");
    }
    return $pdo;
}