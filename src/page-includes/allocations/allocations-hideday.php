<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';

$intTeamID =  $_REQUEST['teamId'];
$strDate =  $_REQUEST['date'];
$intaction =  $_REQUEST['action'];
$strDate = $strDate.' 00:00:00';
$strHideHistory = '<br>Day changed to Hidden by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i");
$strShowHistory = '<br>Day changed to Visible by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i");
  
$pdo = OpenDBLinkA7();
	try {
            $query = "exec [dbo].[usp_GetAllocationsHideDay] ?,?,?,?,?";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(1, $intTeamID, PDO::PARAM_INT);
            $stmt->bindValue(2, $strDate, PDO::PARAM_STR);
            $stmt->bindValue(3, $intaction, PDO::PARAM_INT);
            $stmt->bindValue(4, $strHideHistory, PDO::PARAM_STR);
            $stmt->bindValue(5, $strShowHistory, PDO::PARAM_STR);
            $stmt->execute();           
        }catch(Exception $e){
			logger()->critical('DB Error', (array) $e);
       }
       return [];
?>