<?php

/**
 * Class DbConnection
 * This class use for Database Connect by Form ID
 */
include_once __DIR__.'/../function-includes/helpers.php';
class DB {

	protected static $instance;

	protected function __construct() {}

	public static function OpenDBLinkA7() {

        require_once(__DIR__ . '/../../vendor/autoload.php');
        require_once(__DIR__ . '/../function-includes/bootstrap.php');
        $dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
        $dotenv->load();
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

        if (strtoupper(getenv('SQL_SERVER_AUTH_MODE')) == 'NONE') {
            $sql_server = getenv('ALLOCATE_DB_HOST');
            $sql_username = getenv('ALLOCATE_DB_USER');
            $sql_password = getenv('ALLOCATE_DB_PWD');
            $sql_database = getenv('ALLOCATE_DB_NAME');
        } else if (strtoupper(getenv('SQL_SERVER_AUTH_MODE')) == 'WINDOWS') {
            $sql_server = getenv('ALLOCATE_DB_HOST');
            $sql_username = null;
            $sql_password = null;
            $sql_database = getenv('ALLOCATE_DB_NAME');
        } else {
            echo "Invalid value defined in env file for SQL_SERVER_AUTH_MODE";
        }


		if(empty(self::$instance)) {
			try {
				self::$instance = new PDO("sqlsrv:server=$sql_server;MultiSubnetFailover=".getenv('MULTI_SUBNET_FAILOVER').";Database=$sql_database;APP=ALLOCATE7;" , $sql_username, $sql_password, ['ReturnDatesAsStrings' => true]);
				self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
				self::$instance->query('SET NAMES utf8');
				self::$instance->query('SET CHARACTER SET utf8');
                //echo " - CREATED - ";

			} catch(PDOException $error) {
				echo $error->getMessage();

			}

		}
        //echo " #NOT CREATED#";

		return self::$instance;
	}

	public static function setCharsetEncoding() {
		if (self::$instance == null) {
			self::connect();
		}

		self::$instance->exec(
			"SET NAMES 'utf8';
			SET character_set_connection=utf8;
			SET character_set_client=utf8;
			SET character_set_results=utf8");
	}
}

