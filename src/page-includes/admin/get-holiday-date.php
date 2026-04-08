<?php
 session_start();
include_once '../../function-includes/init.php';

include_once __DIR__ .'/leave/service/LeaveService.php';

$intTimeDemensionID = $_REQUEST['intTimeDemensionID'];

$leaveService = new LeaveService();

if ($intTimeDemensionID != 0) {
  $getTimeDimensionDate = $leaveService->getTimeDimesionDataById($intTimeDemensionID);
  $timeDimensionHolidayDate = date('d-M-Y',strtotime($getTimeDimensionDate['dDateTime']));

} else {
  $timeDimensionHolidayDate = '';
}
echo $timeDimensionHolidayDate;exit();