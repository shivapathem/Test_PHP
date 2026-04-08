<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
$pdo = OpenDBLinkA7();

$intID = $_REQUEST['id'];
$intAction = $_REQUEST['action'];

// Action = 1 move down which increments the number

if ($intAction == 1) {
	
	try {
		$strQuery = "SELECT SortOrder FROM LeaveAllocateTypes WHERE (id = $intID)";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$intSortOrder = $row['SortOrder'];  
		$intReplaceSortOrder = $intSortOrder + 1;
		
		$strQuery = "SELECT id FROM	LeaveAllocateTypes WHERE (SortOrder = $intReplaceSortOrder)";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$intReplaceID = $row['id'];
		
		$strQuery = "UPDATE LeaveAllocateTypes SET SortOrder = $intSortOrder WHERE (id = $intReplaceID)";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		
		$strQuery = "UPDATE LeaveAllocateTypes SET SortOrder = $intReplaceSortOrder WHERE (id = $intID)";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		
	} catch (PDOException $e) {
        logger()->critical('db error', (array)$e);
    }
      // Get the next one up.....
} else {
	
	try {
		
		$strQuery = "SELECT SortOrder FROM LeaveAllocateTypes WHERE (id = $intID)";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$intSortOrder = $row['SortOrder'];  
		$intReplaceSortOrder = $intSortOrder - 1;
		
		// Get the next one up.....
		$strQuery = "SELECT id FROM LeaveAllocateTypes WHERE (SortOrder = $intReplaceSortOrder)";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$intReplaceID = $row['id'];
		
		$strQuery = "UPDATE LeaveAllocateTypes SET SortOrder = $intSortOrder WHERE (id = $intReplaceID)";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		
		$strQuery = "UPDATE   LeaveAllocateTypes SET SortOrder = $intReplaceSortOrder  WHERE (id = $intID)";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		
	} catch (PDOException $e) {
        logger()->critical('db error', (array)$e);
    }
}