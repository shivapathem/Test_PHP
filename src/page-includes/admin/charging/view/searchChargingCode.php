<?php

global $errorContainer;

?>
<link href="../styles/charging/charging.css" rel="stylesheet">
<div id="content-chargecode">
<h2 class="headertextallpages  main-heading">Charge Codes </h2>
	<div class="middleBlock">
	<div id="receiverCode" class="chargeRow">
	<div class="chargeRow">
	<form id="searchReceiverCodeForm" name="searchReceiverCodeForm" onsubmit="return formHandler('searchReceiverCodeForm');" >
		<div class="chargeCol chargeCol12 codeSearchBlock">
		<input type="text" id="searchReceiverCode", name="searchReceiverCode" placeholder="Search Charge Code" mandatory="yes" value="<?php echo $requestData['searchReceiverCode']; ?>" />
		<input type="hidden" id="conrollerName" name="conrollerName" value="searchChargingCode" />
		<input type="submit" class="charging-btn findCode" value="Find">
	</form>
	</div>
	<div class="chargeCol chargeCol12">
	<div class="tables">
	<table id="receiverCodeEntries" class="oddevenclass tablesmall" style="width:100%">
	<thead>
	<tr>
	<th>Charge Code</th>
	<th>Description </th>
	<th>Active </th>
	</tr>
	</thead>
	<tbody>
	<?php 
	foreach($data as $dataVal)
	{
	?>
	<tr class="receiverCode-context-menu">
	<td><?php echo $dataVal['ChargeWbsCodeName']; ?></td>
	<td><?php echo $dataVal['Description']; ?></td>
	<td><img src="./images/<?php echo $dataVal['IsActive'] ? 'green_tick.png': 'red_cross.png'; ?>" class="activeTick" alt="Tick"></td>
	</tr>
	<?php 
	} ?>
	</tbody>
	</table>
	</div>
	</div>
	</div>
	</div>
	</div>
	<div id="receiverEntrieAdd" style="display:none;">
<!-- Please update the popup label for Add Charge Code !-->
<div class="popupHeading">Modify Charge Code</div>
<div class="popupContent">

<div class="fields">
		<label for="costCenter">Charge Code </label>
		<input type="text" id="costCenter" value="B0074" readonly disabled />
</div>

<div class="fields">
		<label for="costCenterDesc">Description </label>
		<input type="text" id="costCenterDesc" value="News Global" />
</div>
<div class= "popupButton">
<button type="text" class="charging-btn">Update</button>
<button type="text" class="charging-btn">Cancel</button>
</div>
</div>
</div> 
</div>
<script>
$(document).ready(function() {
	$('#receiverCodeEntries').DataTable().destroy();
    var table = $('#receiverCodeEntries').DataTable( {
        "paging":         false,
		"lengthChange": false,
		"bInfo" : false,
		"bFilter": false,
        "scrollCollapse": true	
    } );
	   
	   //Context menu configuration
	    $(function () {
            $.contextMenu({
                selector: '.receiverCode-context-menu',
                items: {
                    "Add": {
                        name: "Add Charge Code",
                        icon: "add",
                        callback: function (key, options) {
                             //$.facebox($("#receiverEntrieAdd").html());
                        }
                    },
                    "Edit": {
                        name: "Modify Charge Code",
                        icon: "edit",
                        callback: function (key, options) {
                            //$.facebox($("#receiverEntrieAdd").html());
                        }
                    }
                }
            });
        });
} );
function chargingCallbackFunc(returnValue)
{
	$('#content').html(returnValue);
}
</script>
</body>


