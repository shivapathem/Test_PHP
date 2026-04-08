<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$expiredPHLs  = $_SESSION['ExpiredPHL'];
$CreditYear = $Creditmonth = $Creditday = $sheduledPerson = $CreditDate =null; 
$selecteddata=[];
if (isset($_REQUEST['IndexID'])) {
list($sheduledPerson,$CreditYear,$Creditmonth,$Creditday,$ID) = explode("-",$_REQUEST['IndexID']);
}
$CreditDate = $CreditYear.'-'.$Creditmonth.'-'.$Creditday;
if ($sheduledPerson !=null && $CreditDate !=null) {
    $selecteddata = $expiredPHLs[$sheduledPerson][$CreditDate];
}
$phprowdata='';
if (!empty($selecteddata)) {
$phprowdata.='<legend class="popupBlock-small-text"><b>PHL Expiry Approval</b></legend>
<div class="popupBlock fullWidth">

    <div class="popupBlockCol popupBlockCol12">
        <label class="popupBlock-lab">Expire</label>
        <input type="checkbox" class="checkBoxInput" id="expireCheckbox" name="expireCheckbox" value="1" checked>
        <label for="expireCheckbox">(Tick to Approve the Expiry of PHL)</label>
     </div>
     <div class="popupBlockCol popupBlockCol6">
        <label class="popupBlock-lab mr1">Expired By</label>
        <input class="popupBlock-inp disabledField" type="text" value="'.$_SESSION['user']['FullName'].'" readonly>
    </div>

    <div class="popupBlockCol popupBlockCol6">
        <label class="popupBlock-lab">Expired Date</label>
        <input class="popupBlock-inp popupBlockDate disabledField" type="text" value="'.date('d/M/Y',strtotime($selecteddata['CreditDate'])).'" readonly>
        <input class="popupBlock-inp popupBlockDate disabledField" type="hidden" value="'.$selecteddata['CreditDate'].'" readonly name="creditDate" id="creditDate">
    </div>	
    
    <div class="popupBlockCol popupBlockCol3">
        <label class="popupBlock-lab">PHL Value</label>
        <input class="popupBlock-inp popupBlockDate" type="text" id="credit_sum" name="credit_sum" value="'.$selecteddata['PHLAmount'].'" readonly>
    </div>
    
    <div class="popupBlockCol popupBlockCol4">
        <label class="popupBlock-lab">Expired Amount</label>
        <input class="popupBlock-inp popupBlockDate" type="text" name="ExpiredAmount" id="ExpiredAmount" value="'.$selecteddata['Balance'].'" readonly>
        <input class="popupBlock-inp popupBlockDate" type="hidden" value="'.$selecteddata['TimeDemensionID'].'" name="Holiday" id="Holiday" readonly>
        <input class="popupBlock-inp popupBlockDate" type="hidden" value="'.$selecteddata['SchedulingPersonID'].'" name="scheduledPersonId" id="scheduledPersonId" readonly>
        <input class="popupBlock-inp popupBlockDate" type="hidden" value="'.$selecteddata['StaffNumber'].'" name="staffnumber" id="staffnumber" readonly>
        <input class="popupBlock-inp popupBlockDate" type="hidden" value="'.$selecteddata['SchedulingTeamid'].'" name="SchedulingTeamid" id="SchedulingTeamid" readonly>
    </div>
    
    <div class="popupBlockCol popupBlockCol3">
        <label class="popupBlock-lab">Expired Year</label>
        <input class="popupBlock-inp popupBlockDate" type="text" id="year" name="year" value="'.$selecteddata['iYear'].'" readonly>
    </div>
    
    <div class="popupBlockCol popupBlockCol12" style="display: flex;">
         <label class="popupBlock-lab">Comments</label> 
         <textarea style="width:79%;" rows = "3" name="comment" id="comment">Unused PHL Leave of '.$selecteddata ['Balance'] * (-1).' hours for '.date('d/M/Y',strtotime($selecteddata['CreditDate'])).' ['.$selecteddata['comment'].'] has been expired by '.$_SESSION['user']['FullName'].'</textarea>
    </div>        

</div>';
}
echo $phprowdata;
?>