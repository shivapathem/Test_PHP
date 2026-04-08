<?php
date_default_timezone_set('UTC');
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
require_once '../../../page-includes/allocations/weekly/service/AllocationRepository.php';
$repository = new AllocationRepository();
$pdo = OpenDBLinkA7();
$allocationId = $_REQUEST['dutyid'];
$isAttention = $_REQUEST['isAttention'];
$isEdited = $_REQUEST['isEdited'];
$parentAllocId = $_REQUEST['parentId'];
$scheduledPersonId = $_REQUEST['scheduledPersonId'];
$date = $_REQUEST['date'];
$dutyDetails = GetAllocationsDetailsByAllocationDutyId($allocationId);
$isAttention = ($isAttention == 0) ? 1 : 0;

try {
    $data = [
        'editType' => 'MARKFORATTENTION',
        'isAttention' =>  $isAttention,
        'allocationsSchPer' => $scheduledPersonId,
        'ID' => $parentAllocId,
        'allocationsSpId' => $dutyDetails['AllocationsSPID'] ?? 0,
        'allocationsDutyId' => $dutyDetails['AllocationsDutyID'] ?? 0,
        'allocationsDate' => $date
    ];
    $repository->editAllocations($data);
    echo json_encode(['status' => 'success']);
} catch(PDOException $e) {
    logger()->critical('DB error', (array) $e);
}





