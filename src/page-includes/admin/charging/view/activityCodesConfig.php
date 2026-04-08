<?php
$pageid = 19;
$permissions = getUserRolePermissions($pageid);
if($permissions->canview != 1)
{
	require_once('../../no_access.php');die();
}
$requestData['searchSelect'] = $requestData['searchSelect'] ?? '';
$requestData['lastId'] = $requestData['lastId'] ?? 0;
?>
<link href="../styles/charging/charging.css" rel="stylesheet">
<div id="content-charging">
<h1 class="headertextallpages  main-heading">Activity Codes </h1>
<div class="middleBlock" style="padding: 20px 0px;">
<div id="activityCodes" class="chargeRow">

<form name="activityCodesConfigForm" id="activityCodesConfigForm" onSubmit="return false" >
<div class="chargeRow">
	<div class="chargeCol chargeCol6 brdRight">
		<div class="fields" >
			<input type="text" name="searchSelect" placeholder="Search Activity Code" id="searchSelect" style="margin-left:15px;" value="<?php echo $requestData['searchSelect']; ?>" >
				<select id="activityCodeList" size="20" onClick="actCode.value = this.options[this.selectedIndex].getAttribute('activityCode'); actDesc.value = this.options[this.selectedIndex].getAttribute('desc'); actCodeOld.value = this.options[this.selectedIndex].getAttribute('activityCode');  actDescOld.value = this.options[this.selectedIndex].getAttribute('desc'); activityCodeId.value = this.value;  actStatus.value = this.options[this.selectedIndex].getAttribute('actStatus'); actId.value = this.value; toggleAction(this.value, this.options[this.selectedIndex].getAttribute('actStatus')); if(this.options[this.selectedIndex].getAttribute('actStatus') == 0){ displayTextInAct.style.display ='block'; }else{ displayTextInAct.style.display = 'none';};">
					 <?php 
					foreach($data as $dataVal)
					{
						$activityDisplayName	=	$dataVal['ActivityCodeName'] . ' - ' . $dataVal['Description'];
						$activityDisplayName	=	($dataVal['IsActive']) ? $activityDisplayName : '--[' . $activityDisplayName . ']--';
					?>
					 <option value="<?php echo $dataVal['ActivityCodeId']; ?>" activityCode="<?php echo $dataVal['ActivityCodeName']; ?>" desc="<?php echo $dataVal['Description']; ?>" actStatus="<?php echo $dataVal['IsActive']; ?>" <?php if($requestData['lastId'] == $dataVal['ActivityCodeId']){ echo 'selected="selected"'; } ?> title="<?php echo $activityDisplayName; ?>" ><?php echo $activityDisplayName; ?></option>
					<?php 
					} ?>
				</select>
		</div>
	</div>
	<div class="chargeCol chargeCol6 CodeBtnRight">
		<div class="fields">
			<label for="actCode" class="activityCodeLevel" >Code<span class="required">*</span></label>
			<input type="text" id="actCode" name="actCode" maxlength="10" maxlen="10" mandatory="yes" fieldname="Acticity Code" disabled />
			<input type="hidden" id="actCodeOld" name="actCodeOld" />
		</div>	
		<div class="fields">
			<label for="actDesc" class="activityCodeLevel" >Description<span class="required">*</span> </label>
			<input type="text" id="actDesc" name="actDesc" maxlength="50" maxlen="50" mandatory="yes" fieldname="Description" disabled />
			<input type="hidden" id="actDescOld" name="actDescOld" />
			<input type="hidden" id="actStatus" name="actStatus" />
			<input type="hidden" id="actId" name="actId" />
			<input type="hidden" id="activityCodeId" name="activityCodeId" />
		</div>
		<div id="displayTextInAct">
			This Activity Code has previously been Inactivated. Click on Activate to use it again.
		</div>
		</div>
<input type="hidden" id="submitType" name="submitType" >
<input type="hidden" id="conrollerName" name="conrollerName" value="activityCodesConfigSave" >
<div class="chargeCol chargeCol12">
<div class= "actBtns" id="mainActionBtn">
<?php if($permissions->cancreate == 1) {?>
<button type="text" class="charging-btn" id="new" >New</button>
<?php }
        if($permissions->canmodify == 1) {?>
<button type="text" class="charging-btn" id="modify" >Modify</button>
<button type="text" class="charging-btn" id="activate" onClick="actionOnStatus();" >Activate</button>
<?php }
        if($permissions->canview == 1) {?>
<button type="text" class="charging-btn exportBtn" id="export"  onClick="location='page-includes/admin/charging/activityCodeExport.php'" ><img src="../images/excel.svg" alt="Export Excel" class="exportIcon"">Export</button>
<?php } ?>
</div>
<div class= "actBtns" id="addEditActivityCodeBtn" style="display:none" >
<button type="text" class="charging-btn" id="save" onClick="formHandler('activityCodesConfigForm');" >Save</button>
<button type="text" class="charging-btn" id="cancel" >Cancel</button>
</div>
</div>
</div>
</form>
</div>
</div>
</div>

<script>

var activityListJson	=	<?php echo json_encode($data); ?>;
<?php if(isset($requestData['lastId'])){ ?>
	if(document.getElementById('activityCodeList').value != '')
	{
		searchSelect();
	}
	$("#activityCodeList option[value='<?php echo $requestData['lastId']; ?>']").attr("selected","selected");
	//$("#activityCodeList").trigger("click");
	document.getElementById('modify').disabled 			= 	false;
	document.getElementById('activate').disabled 		= 	false;
<?php } ?>
$(document).ready(function() {
	$("#activityCodeList").click(function(){
		document.getElementById('submitType').value 	= 	1;
		document.getElementById('modify').disabled 		= 	false;
		document.getElementById('activate').disabled 	= 	false;
	});
	$("#new").click(function(){
		document.getElementById('activityCodesConfigForm').reset();
		document.getElementById('submitType').value 						= 	0;
		document.getElementById('mainActionBtn').style.display 				= 	'none';
		document.getElementById('addEditActivityCodeBtn').style.display 	= 	'block';
		document.getElementById("actCode").disabled							=	false;
		document.getElementById("actDesc").disabled							=	false;
		document.getElementById("activityCodeList").disabled 				=	true;
		document.getElementById("actCode").focus();
	});
	$("#modify").click(function(){
		document.getElementById('submitType').value 						= 	1;
		document.getElementById('mainActionBtn').style.display 				= 	'none';
		document.getElementById('addEditActivityCodeBtn').style.display 	= 	'block';
		document.getElementById("actDesc").disabled 						=	false;
		document.getElementById("activityCodeList").disabled 				=	true;
		document.getElementById("actDesc").focus();
	});
	$("#cancel").click(function(){
		document.getElementById('submitType').value 						= 	0;
		document.getElementById('mainActionBtn').style.display 				= 	'block';
		document.getElementById('addEditActivityCodeBtn').style.display 	= 	'none';
		document.getElementById("actCode").value							=	$('#actCodeOld').val();
		document.getElementById("actDesc").value							=	$('#actDescOld').val();
		document.getElementById("activityCodeList").value					=	$('#activityCodeId').val();
		document.getElementById("activityCodeList").disabled 				=	false;
		document.getElementById("actCode").disabled							=	true;
		document.getElementById("actDesc").disabled							=	true;
	});
	$('#searchSelect').keyup(function () {
		searchSelect();
	});
	if(document.getElementById("activityCodeList").value == '')
	{
		document.getElementById('activityCodeList').getElementsByTagName('option')[0].selected = 'selected';
		$("#activityCodeList").trigger("click");
	}else
	{
		$("#activityCodeList").trigger("click");
	}
});
function searchSelect()
{
	let rxp = new RegExp($('#searchSelect').val(), 'i');
	$('#activityCodeList').empty();
	let activityCodeList = $('#activityCodeList');
	activityListJson.forEach(function(data, index){
		let activityCodeNameStr	=	'';
		activityCodeNameStr		=	(data['IsActive'] == 1) ? data['ActivityCodeName'] + ' - ' + data['Description'] : '--[' + data['ActivityCodeName'] + ' - ' + data['Description'] + ']--';
		if (rxp.test(data['ActivityCodeName']) || rxp.test(data['Description']))
		{
			if( data['ActivityCodeId'] == $('#activityCodeId').val() )
			{
				activityCodeList.append($('<option/>').attr('value', data['ActivityCodeId']).text(activityCodeNameStr).attr('activityCode', data['ActivityCodeName']).attr('desc', data['Description']).attr('actStatus', data['IsActive']).attr('selected', 'selected'));
			}else
			{
				activityCodeList.append($('<option/>').attr('value', data['ActivityCodeId']).text(activityCodeNameStr).attr('activityCode', data['ActivityCodeName']).attr('desc', data['Description']).attr('actStatus', data['IsActive']));
			}
			
		}else
		{
			activityCodeList.append($('<option/>').attr('value', data['ActivityCodeId']).text(activityCodeNameStr).attr('activityCode', data['ActivityCodeName']).attr('desc', data['Description']).attr('actStatus', data['IsActive']).addClass("hidden"));
		}
	});
}
function toggleAction(selectedOptionId, statusVal)
{
	let statusText	=	'';
	if(statusVal == 1)
	{
		statusText	=	'Inactivate';
	}else
	{
		statusText	=	'Activate';
	}
	document.getElementById('activate').innerHTML	=	statusText;
}
function actionOnStatus()
{
	statusText 	=	document.getElementById('activate').innerHTML;
	let msg		=	'';
	if(statusText == 'Inactivate')
	{
		msg		=	'You are about to Inactivate an Activity code that could affect payments. Please click on Yes to continue.';
	}else
	{
		msg		=	'You are about to Activate an Activity code that could affect payments. Please click on Yes to continue.';
	}
	customConfirm(msg,function(){
			document.getElementById("submitType").value=2; 
			formHandler('activityCodesConfigForm');
		},
		function() {
		}
	);
}
function chargingCallbackFunc(data)
{
	if($($.parseHTML(data)).filter("#content-charging").length)
	{
		$('#content').html(data);
	}else
	{
		customAlert(data);
	}
}
</script>