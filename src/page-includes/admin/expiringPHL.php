
<?php
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';
$teamID =$_REQUEST['teamid'] ?? 0;
?>
<div class="PHLfacebox">
<div class="popupBlock-container">
    <div class="popupBlock-main-heading">
    <form name="expiryWeek" id="expiryWeek" method="post">
        <div class="popupBlock-heading-v">
            <h2>Expiring PHL In Next</h2>
            <select name="weekno" id="weekno">
            <option value="0">Select</option>
            <?php for($j=1;$j<17;$j++){ ?>
                <option value="<?php echo $j; ?>"><?php echo $j; ?></option>
            <?php  } ?>    
            </select>
            <span> Week(s)</span>
        </div>
        </form>
    </div>
    <div class="fullWidth">
     <fieldset>
        <legend class="popupBlock-small-text"><b>Expiring PHL Records</b></legend>
        <div id="PHLTableBlock">
        <table id="PHLExpiring" class="fullWidth phlTable">
        <thead>
            <tr>
                <th class="popupBlock-labs PHLName">Name</th>
                <th class="popupBlock-labs">Staff Number</th>
                <th class="popupBlock-labs">Leave Year</th>
                <th class="popupBlock-labs">PHL Date</th>
                <th class="popupBlock-labs">PHL Amount</th>
                <th class="popupBlock-labs">Expiry Date</th>
                <th class="popupBlock-labs">PHL Expiring</th>
                <th class="popupBlock-labs PHLComment">Comment</th>
            </tr>
        </thead>
        <tbody id="tresult">
            <tr><td colspan="8" align="center">Select week to see the records.</td></tr>           
        </tbody>	
<input type="hidden" name="PHLExpiredContainer" id="PHLExpiredContainer" value="0" readonly>
        </table>
        </div>
    </fieldset>
    <div class="popupBlock btns-block">
            <div class="popupBlockCol popupBlockCol2"></div>
            <div class="popupBlockCol popupBlockCol2"></div>     
            <div class="popupBlockCol popupBlockCol2"><button type="text" class="popupBlock-btn" onClick="exportExpiringPHL();" id="exportPHL" disabled="true">Export</button></div>       
            <div class="popupBlockCol popupBlockCol2"><button type="text" class="popupBlock-btn" onclick="cancel();">Finished</button></div>           
            <div class="popupBlockCol popupBlockCol2"></div>
            <div class="popupBlockCol popupBlockCol2"></div>       
    </div>   
    </div>
</div>
</div>
<script>
//highlight the selected table row
   $('#weekno').change(function() {
        var $this = $(this);
        var selKeyVal = $this.val();
        $('#PHLExpiredContainer').val(selKeyVal);
        $.post("page-includes/admin/fetchExpiringPHL.php", {selectedweek: selKeyVal,teamId:<?php echo $teamID;?>}, function(result) {
            if(result!='') {
                $('#exportPHL').attr('disabled',false);
            } else {
                $('#exportPHL').attr('disabled',true);
            }
            $('#tresult').html(result);        
        });
    });
  function exportExpiringPHL()
    {
        expiringPHLContainer=$('#PHLExpiredContainer').val();
        teamId=<?php echo $teamID;?>;
        if(teamId!=undefined && expiringPHLContainer > 0) {
        location='page-includes/admin/expiringPHL/expiringPHLExport.php?expiringPHLWeek='+expiringPHLContainer+'&TeamId='+teamId;
        } else{
            customAlert('Opps! Some problem found in team/ week selection.');
            return false;
        }
    }
</script>