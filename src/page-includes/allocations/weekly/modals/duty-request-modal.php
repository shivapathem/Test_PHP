<?php
use Symfony\Component\HttpFoundation\Request;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
include_once __DIR__ . '/../../../../function-includes/genericfunctions.php';
include_once __DIR__ . '/../../../../function-includes/requestfunctions.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";
require_once __DIR__ . '/../service/Allocation.php';
$request = Request::createFromGlobals();
$service = new AllocationService();
$dutyName = $request->get('dutyName');
$scheduledpersonID = $request->get('schedulingPersonId');
$strEndDate =$strStartDate = $request->get('dateonly');
$strUser = GetNetloginByscheduledPerson($scheduledpersonID);
$intGroup = 0;
$arrRequests = GetAllDailyRequestsByScheduledPerson($strUser, $strStartDate);
if (isset($arrRequests)) {
    echo '<div class="popupBlock-container">
    <div class="popupBlock-main-heading">
        <h1 class="popupBlock-heading">View Request Details
        </h1>
    </div>
    <div class="fullWidth"><fieldset>
       <legend class="popupBlock-small-text"></legend>
       <div id="PHLTableBlock"><table id="PHLExpiring" class="fullWidth phlTable">';
    echo '<thead><tr>
         <th class="popupBlock-labs">Date</th>
         <th class="popupBlock-labs">Leave & Request Group</th>
         <th class="popupBlock-labs">Request Type</th>
         <th class="popupBlock-labs">Approved</th>
         <th class="popupBlock-labs">Is OK</th>
         <th class="popupBlock-labs">User Comments</th>
         <th class="popupBlock-labs">Admin Comments</th>
     </tr></thead><tbody>';
    // Loop through the available Groups
    foreach ($arrRequests as $arrGroup) {
       ?>
     <tr>
         <td><?php $ddate=explode(" ",$arrGroup['dDate']); echo date("d/m/Y", strtotime($ddate[0]));?></td>
         <td><?php echo $arrGroup['Description']; ?></td>
         <td><?php echo $arrGroup['requesttype']; ?></td>
         <td align="center"><?php if($arrGroup['Approved']==1){echo 'Y';}else{echo 'N';}?></td>
         <td align="center"><?php if($arrGroup['isOK']==1){echo 'Y';}else{echo 'N';}?></td>
         <td><?php if(!empty($arrGroup['UserComments'])){ echo $arrGroup['UserComments'];} else{echo "No Comment";} ?></td>
         <td><?php if(!empty($arrGroup['Comments'])){ echo $arrGroup['Comments'];} else{echo "No Comment";} ?></td>
     </tr>
     <?php
      }
      echo '</tbody></table></div>';
    } else {
      echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
      echo '<br>No Record Found.<br><br>';
      echo '</div>';

    }
?>