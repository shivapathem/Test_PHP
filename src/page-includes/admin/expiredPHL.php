<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';
include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';
$commonObj = new classCommonDBFunctions();
$teamID = $_REQUEST['teamid'] ?? 0;
$PHLCredits = [];
$PHLDebits  = [];
$PHLExpired = [];
$PHLData=[];
$intWeekNumber = 0;
$PHLStartDate = getPHLStartDate(); ///Step 1
$getExpiredPHL = getAllExpirdPHL($PHLStartDate,$intWeekNumber,$teamID);//Step 3
//Check for Credit Sum
if (!empty($getExpiredPHL)) {
    foreach ($getExpiredPHL as $index => $ExpiredPHL) {
        $scheduledPerson = $ExpiredPHL['SchedulingPersonID'];
        $creditdateData = explode(" ",$ExpiredPHL['PHLDATE']);
        $creditdate = $creditdateData[0];
        $expireddateData = explode(" ",$ExpiredPHL['ExpDate']);
        $PHLData[$scheduledPerson][$creditdate]['Balance'] = $ExpiredPHL['diff']; 
        $PHLData[$scheduledPerson][$creditdate]['SchedulingPersonID']=$scheduledPerson;        
        $PHLData[$scheduledPerson][$creditdate]['CreditDate'] = $creditdate;
        $PHLData[$scheduledPerson][$creditdate]['PHLAmount'] = $ExpiredPHL['rolling_sum']; 
        $PHLData[$scheduledPerson][$creditdate]['PHLUsed'] = $ExpiredPHL['rolling_d_sum'];
        $PHLData[$scheduledPerson][$creditdate]['expiryDate'] = $expireddateData[0];
        $PHLData[$scheduledPerson][$creditdate]['StaffNumber'] = $ExpiredPHL['StaffNumber'];
        $PHLData[$scheduledPerson][$creditdate]['Name'] =  $ExpiredPHL['userDisplayName'];
        $PHLData[$scheduledPerson][$creditdate]['iYear'] = $ExpiredPHL['iYear'];
        $PHLData[$scheduledPerson][$creditdate]['comment']= $ExpiredPHL['Comments'];
        $PHLData[$scheduledPerson][$creditdate]['TimeDemensionID']= $ExpiredPHL['TimeDemensionID'];
        $PHLData[$scheduledPerson][$creditdate]['SchedulingTeamid']= $teamID; 
        $PHLData[$scheduledPerson][$creditdate]['ID']= $ExpiredPHL['ID'];   
    }
}

$_SESSION['ExpiredPHL'] = $PHLData;
?>
<div class="popupBlock-container">
        <div class="popupBlock-main-heading">
            <h2 class="popupBlock-heading">Expired PHL
            </h2>
        </div>
		<div class="fullWidth">
		
		 <fieldset>
            <legend class="popupBlock-small-text"><b>Expired PHL Records</b></legend>
			<div id="PHLTableBlock">
            <table id="PHLExpired" class="fullWidth phlTable">
			<thead>
                <tr>
                    <th class="popupBlock-labs PHLName">Name</th>
                    <th class="popupBlock-labs">Staff Number</th>
                    <th class="popupBlock-labs">Leave Year</th>
                    <th class="popupBlock-labs">PHL Date</th>
					<th class="popupBlock-labs">PHL Credit</th>
                    <th class="popupBlock-labs">PHL Debit</th>
                    <th class="popupBlock-labs">Expiry Date</th>
					<th class="popupBlock-labs">PHL Expired</th>
					<th class="popupBlock-labs PHLComment">Comment</th>
                </tr>
			</thead>
			<tbody>
    <?php
    $row=0;
    if (!empty($PHLData)) {
      foreach ($PHLData as $key => $PHLExpireRow) {
        foreach ($PHLExpireRow as $innerKey=> $innerRow) {
           if (isset($innerRow['Balance']) && $innerRow['Balance'] < 0 && strtotime($innerRow['expiryDate']) < strtotime(date('Y-m-d'))) {
            $row++;
               if($row==1) {
                   $RowID = $key.'-'.$innerKey.'-'.$innerRow['ID'];
               }
    ?>
                <tr id="<?php echo $key; ?>-<?php echo $innerKey; ?>-<?php echo $innerRow['ID']; ?>" <?php if($row==1) { ?> class="highlightOrange" <?php } ?>>
                <td><?php echo $innerRow['Name']; ?></td>
                <td><?php echo $innerRow['StaffNumber']; ?></td>
                <td><?php echo $innerRow['iYear']; ?></td>
                <td><?php echo date('d/M/Y',strtotime($innerRow['CreditDate'])); ?></td>
                <td><?php echo isset($innerRow['PHLAmount']) && is_numeric($innerRow['PHLAmount']) ? round($innerRow['PHLAmount'], 2) : '0.00'; ?></td>
                <td><?php echo isset($innerRow['PHLUsed']) && is_numeric($innerRow['PHLUsed']) ? round($innerRow['PHLUsed'], 2) : '0.00'; ?></td>
                <td><?php echo date('d/M/Y',strtotime($innerRow['expiryDate'])); ?></td>
                <td><?php echo isset($innerRow['Balance']) && is_numeric($innerRow['Balance']) ? round($innerRow['Balance'], 2) : '0.00'; ?></td>
                <td><?php echo $innerRow['comment']; ?></td>
            
                </tr>
				<?php 
               
            }
        }
    } 
} if($row == 0) {
    
?>
<tr><td colspan="9" style="text-align:center">No Record Found</td></tr>
<?php
}
?>
			</tbody>	

            </table>
			</div>
        </fieldset>
		<?php if ($row > 0) { ?>
        <form name="PHLExpiredForm" id="PHLExpiredForm" method="post">
		<fieldset id="tresult">
        <legend class="popupBlock-small-text"><strong>PHL Expiry Approvalcks</strong></legend>
            <input type="text" id="selectedrow" name="selectedRow" value="<?php echo $RowID; ?>" readonly>
            <div class="popupBlock fullWidth">

                <div class="popupBlockCol popupBlockCol12">
					<label class="popupBlock-lab">Expire</label>
                    <input type="checkbox" class="checkBoxInput" id="expireCheckbox" name="expireCheckbox" value="1">
                    <label for="expireCheckbox">(Tick to Approve the Expiry of PHL)</label>
                 </div>
                 <div class="popupBlockCol popupBlockCol6">
                    <label class="popupBlock-lab mr1">Expired By</label>
                    <input class="popupBlock-inp disabledField" type="text" value="<?php echo $_SESSION['user']['FullName'];?>" readonly>
                </div>

                <div class="popupBlockCol popupBlockCol6">
                    <label class="popupBlock-lab">Expired Date</label>
                    <input class="popupBlock-inp popupBlockDate disabledField" type="text" value="<?php echo date('d/M/Y'); ?>" readonly>
				</div>	
                
				<div class="popupBlockCol popupBlockCol3">
                    <label class="popupBlock-lab">PHL Value</label>
                    <input class="popupBlock-inp popupBlockDate" type="text" value="" id="credit_sum" name="credit_sum" readonly>
                </div>
				
				<div class="popupBlockCol popupBlockCol4">
                    <label class="popupBlock-lab">Expired Amount</label>
                    <input class="popupBlock-inp popupBlockDate" type="text" value=""  name="PHLLeaveAmount" id="PHLLeaveAmount" readonly>
                </div>
				
				<div class="popupBlockCol popupBlockCol3">
                    <label class="popupBlock-lab">Expired Year</label>
                    <input class="popupBlock-inp popupBlockDate" type="text" value="" name="year" id="year" readonly>
                    <input class="popupBlock-inp popupBlockDate" type="hidden" value="" name="Holiday" id="Holiday" readonly>
                </div>
               
				
				<div class="popupBlockCol popupBlockCol12" style="display: flex;">
					 <label class="popupBlock-lab">Comments</label> 
					 <textarea style="width:79%;" rows = "3"  >
					 </textarea>
				</div>        

            </div>
        </fieldset>
        
		
		<div class="popupBlock btns-block">
				<div class="popupBlockCol popupBlockCol2"><input type="submit" class="popupBlock-btn" value="Expire PHL" name="submit" id="sbt"></div>     
        </form> 	
                <div class="popupBlockCol popupBlockCol2"><button type="button" class="popupBlock-btn" onclick="cancel();">Cancel</button></div>
				<div class="popupBlockCol popupBlockCol2"></div>
                <div class="popupBlockCol popupBlockCol2"><button type="button" class="popupBlock-btn" onclick="expiredAllPHL();">Expire All</button></div>
				<div class="popupBlockCol popupBlockCol2"><button type="button" class="popupBlock-btn" onclick="exportExpiredPHL();">Export</button></div>           
                <div class="popupBlockCol popupBlockCol2">                 
					<input type="text" class="popupBlock-btn text-center" onclick="cancel();" readonly value="Finished" />
                </div>
        </div>
		<div id="PHLExpiredFormerrorBox"></div> 
        <?php } ?>
		</div>
       
    </div>
    <script>
        $(document).ready(function() {
            var ArryID = $('#selectedrow').val();
             $.post("page-includes/admin/fetchselectedExpiredPHL.php", {IndexID: ArryID}, function(result){
               $('#tresult').html(result);
            });

        });
        $('#PHLExpired tbody tr').on('click','td',function () {
            $('tr').removeClass('highlightOrange');
            $(this).parent().addClass('highlightOrange');
            var ArryID =$(this).parent().attr('id'); 
            $('#selectedrow').val(ArryID);
            $.post("page-includes/admin/fetchselectedExpiredPHL.php", {IndexID: ArryID}, function(result){
               $('#tresult').html(result);
            });
		});	

     $('#PHLExpiredForm').validate({
      errorLabelContainer: "#PHLExpiredFormerrorBox",
      rules:{ 
        "PHLLeaveAmount":{
          required:true,
        },
        "credit_sum":{
          required:true,
        },
        "year":{
          required:true,
        },
        "expireCheckbox":{
          required:true,
        }
        
      },
      messages: {
        PHLLeaveAmount: "PHL Amount is Mendatory<br>",
        credit_sum: "Please select a row for Expiry Amount,PHL Amount, Expiry Year.<br>",
        year: "Expiry Year is Mendatory<br>",
        expireCheckbox:"Tick to approve the Expiry of PHL<br>"
    }
        ,
      
      submitHandler: function(form) {
        $('#sbt').attr('disabled',true);
        var expireCheckbox = $('#expireCheckbox').prop('checked');
        if(expireCheckbox == false) {
            customAlert('Click on Approve checkbox is mendatory for expire PHL process.');
           $('#expireCheckbox').focus();
           return false;
       }

        $(form).append($('<input>').attr('type', 'hidden').attr('name', 'action').val('updatePHLonleaveAllocation'));
        $.ajax({type:'POST', url: 'page-includes/admin/leave/PHL-expire-process.php', data:$('#PHLExpiredForm').serialize(), success: function(data) {
            data =JSON.parse(data);
            customAlert(data.response);
        }
        });
      }
  }) 

  function exportExpiredPHL()
    {
       var teamId=<?php echo $teamID;?>;
        if(teamId!=undefined) {
        location='page-includes/admin/expiringPHL/expiredPHLExport.php?TeamId='+teamId;
        } else{
            customAlert('Opps! Some problem found in team selection.');
            return false;
        }
    }

    function expiredAllPHL()
    {
       var teamId=<?php echo $teamID;?>;
        $.post("page-includes/admin/leave/All-PHL-expire-process.php", {teamId: teamId}, function(result){ 
            data =JSON.parse(result);
            customAlert(data.response);
        })
              
    } 
    </script>
