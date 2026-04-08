<?php
include_once '../process/classContractHistory.php';
$contract = new classContractHistory;
$staffid = $_GET['staffid'];
$result  = $contract->getContractHistory($staffid);
