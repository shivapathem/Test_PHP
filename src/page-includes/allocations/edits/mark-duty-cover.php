<?php
include_once '../../../function-includes/allocationsfunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../weekly/service/AllocationService.php';
$allocService = new AllocationService();
$oldAllocationId = $_POST['oldAllocationId'] ?? 0;
$scheduledPerson = $_POST['scheduledPerson'] ?? 0;
$swapAllocationID = $_POST['swapAllocationId'] ?? 0;
$intTeamID = $_POST['intTeamID'] ?? 0;
$allocationsIDs = $oldAllocationId .", ".$swapAllocationID;
$allocationInfor=[];
$assigned_allocationInfor=[];
$unassigned_allocationInfor=[];
$unassigned = GetAllocationsDetailsByAllocationDutyId($oldAllocationId);
if (!empty($unassigned)) {
    $unassigned['isSwap'] = 1;
    $unassigned_allocationInfor = $unassigned;
}
$assigned = GetAllocationsDetailsByAllocationDutyId($swapAllocationID);
if (!empty($assigned)) {
    $assigned_allocationInfor = $assigned;
}
echo markAllocationCover($oldAllocationId, $scheduledPerson, $swapAllocationID, $intTeamID);
require_once 'emailNotificationOnAssign.php';