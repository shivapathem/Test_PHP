<link href="../styles/charging/teamPay.css" rel="stylesheet">
<div id="content-teamPay">
	<div class="headertextallpages main-heading">
        <h1 style="text-align: center;">Team Pay</h1>
		<span id="refBtnContainer" >
			<button type="text" id="refBtn" onclick="syncRefTables();" class="teamPay-btn" style="width: 10%; position: absolute; top:0%; right: 0%; border: black solid 2px;">Sync Ref Tables</button>
		</span>
    </div>
	<div id="TeamPayBlock">
		<div id="teamPayTab">
			<ul>
				<li>
					<a href="#tabs-1" onClick="clearFilter(1);getMissingRecord();" >Missing Records</a>
				</li>
				<li>
					<a href="#tabs-2" onClick="clearFilter(2);getIncorrectRecord();" >Incorrect Records</a>
				</li>
				<li>
					<a href="#tabs-3" onClick="clearFilter(3);getLeaversRecord();" >Leavers</a>
				</li>
				<li>
					<a href="#tabs-4" onClick="clearFilter(4);getEFTDiscNews();" >EFT Discrepancies (News)</a>
				</li>
				<li>
					<a href="#tabs-5" onClick="clearFilter(5);getPayTypeNews();" >Payment Type Discrepancies (News)</a>
				</li>
				<li>
					<a href="#tabs-6" onClick="clearFilter(6);getEFTDiscTG();" >EFT Discrepancies (TG)</a>
				</li>
				<li>
					<a href="#tabs-7" onClick="clearFilter(7);getPayTypeTG();" >Payment Type Discrepancies (TG)</a>
				</li>
			</ul>
			<div id="tabs-1" >
			</div>
			<div id="tabs-2" >
			</div>
			<div id="tabs-3" >
			</div>
			<div id="tabs-4" >
			</div>
			<div id="tabs-5" >
			</div>
			<div id="tabs-6" >
			</div>
			<div id="tabs-7" >
			</div>
		</div>
		<div class="teamPayActionBTN" id="teamPayActionBTN">
			<button type="text" id="approveBtn" onclick="approvedData();" class="teamPay-btn" disabled>Approve</button>
			<button type="text" id="onHoldBtn" onclick="onHoldData();" class="onHoldRecord teamPay-btn" disabled>On Hold</button>
		</div>
	</div>
</div>
</form>
<script>
$( "#teamPayTab" ).tabs();

$('#teamPayTab [role="tabpanel"]').removeAttr('aria-labelledby');
getMissingRecord();
function approvedData()
{
	let formName		=	document.getElementById("formName").value;
	document.getElementById("requestType").value = 'APPROVED';
	customConfirm('You are about to move selected records into A7. Please click on Yes to continue.',
		function(){
		  $.ajax({
				  type: 'POST',
				  url: 'page-includes/admin/charging/index.php',
				  data: $('#mainForm').serialize()+ "&onHoldComment=Approved",
				  success: function (returnHtml){
					closeCustomAlert();
					if(formName == 'missingRecords')
					{
						getMissingRecord(true);
					}else if(formName == 'leaversRecords')
					{
						getLeaversRecord(true);
					}else if(formName == 'incorrectRecords')
					{
						getIncorrectRecord(true);
					}
				}
			});
		},
		function() {
		}
	);
}
function onHoldData()
{
	let chkIdArr = $("input[name='chkId[]']:checked").map(function () {return this.value;}).get();
	let commentStr = '';
	if(chkIdArr.length == 1)
	{
		commentStr = $("#commentTxt_"+chkIdArr[0]).text();
	}else if(chkIdArr.length > 1)
	{
		commentStr = 'Multiple Comments';
	}
	document.getElementById("requestType").value = 'ONHOLD';	
	$.facebox('<div id="onHoldInfo"><div class="popupHeading">On Hold</div><div class="popupContent"><div class="fields"><textarea name="onHoldComment" id="onHoldComment" cols="40" rows="4" maxlength="500" placeholder="Comments" >'+commentStr+'</textarea></div><div class="popupButton"><button type="text" class="add-btn" onClick="missingRecordFormSubmit();" >Ok</button><button type="text" class="add-btn" onClick="closeCustomAlert();" >Cancel</button></div></div></div>');	
}
function chargingCallbackFunc(data)
{
	if($($.parseHTML(data)).filter("#content-teamPay").length)
	{
		$('#content').html(data);
	}else
	{
		customAlert(data);
	}
}
function missingRecordFormSubmit()
{
	let onHoldComment	=	document.getElementById("onHoldComment").value;
	let formName		=	document.getElementById("formName").value;
	if(onHoldComment.trim() == '')
	{
		customAlertByModel('Please comment is mandatory field.');
		return false;
	}
	$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: $('#mainForm').serialize()+ "&onHoldComment=" + onHoldComment,
			success: function (returnHtml){
				closeCustomAlert();
				if(formName == 'missingRecords')
				{
					getMissingRecord(true);
				}else if(formName == 'leaversRecords')
				{
					getLeaversRecord(true);
				}else if(formName == 'incorrectRecords')
				{
					getIncorrectRecord(true);
				}
		}
	});
}
function getIncorrectRecord(reqData = false)
{
	document.getElementById('approveBtn').disabled	=	true;
	document.getElementById('onHoldBtn').disabled	=	true;
	$('#teamPayActionBTN').show();
	let recordOffsetVal = $('#recordOffset').val() ? $('#recordOffset').val() : 0;
	let name = $('#name').val() ? $('#name').val() : '';
	let empNumber = $('#empNumber').val() ? $('#empNumber').val() : '';
	let estCode = $('#estCode').val() ? $('#estCode').val() : '';
	let schedulingTeamId = $('#schedulingTeamId').val() ? $('#schedulingTeamId').val() : '';
	let records = $('#records').val() ? $('#records').val() : 1;
	if(reqData)
	{
		reqData	=	$('#incorrectRecordFilter').serialize();
		$.cookie('nameIncorrectTab', name);
		$.cookie('empNumberIncorrectTab', empNumber);
		$.cookie('estCodeIncorrectTab', estCode);
		$.cookie('schedulingTeamIdIncorrectTab', schedulingTeamId);
		$.cookie('recordsIncorrectTab', records);
	}else
	{
		name	=	name ? name : $.cookie('nameIncorrectTab');
		empNumber	=	empNumber ? empNumber : $.cookie('empNumberIncorrectTab');
		estCode	=	estCode ? estCode : $.cookie('estCodeIncorrectTab');
		schedulingTeamId	=	schedulingTeamId ? schedulingTeamId : $.cookie('schedulingTeamIdIncorrectTab');
		records	=	$.cookie('recordsIncorrectTab') ? $.cookie('recordsIncorrectTab') : records;
		reqData	=	{'conrollerName':'getIncorrectRecord', 'recordOffset':0, 'name':name, 'empNumber':empNumber, 'estCode':estCode, 'schedulingTeamId':schedulingTeamId, 'records':records};
	}
	clearAllTabPanels();
	$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: reqData,
			success: function (returnHtml){
				document.getElementById('tabs-2').innerHTML = returnHtml;
				$(".teamPayRecordSection").height($(window).height() - 266);
				checkBoxJs();
				$('#name').val($.cookie("nameIncorrectTab"));
				$('#empNumber').val($.cookie("empNumberIncorrectTab"));
				$('#estCode').val($.cookie("estCodeIncorrectTab"));
				$('#schedulingTeamId').val($.cookie("schedulingTeamIdIncorrectTab"));
				let recordsIncorrectTab = $.cookie("recordsIncorrectTab") ? $.cookie("recordsIncorrectTab") : 1;
				$('#records').val(recordsIncorrectTab);
				$('#incorrectRecordContainer').scrollTop(Math.abs($.cookie("incorrectRecordPosTop")) + 100);
		}
	});
}
function getMissingRecord(reqData = false)
{
	document.getElementById('approveBtn').disabled	=	true;
	document.getElementById('onHoldBtn').disabled	=	true;
	$('#teamPayActionBTN').show();
	let recordOffsetVal = $('#recordOffset').val() ? $('#recordOffset').val() : 0;
	recordOffsetVal = parseInt(recordOffsetVal);
	let name = $('#name').val() ? $('#name').val() : '';
	let empNumber = $('#empNumber').val() ? $('#empNumber').val() : '';
	let estCode = $('#estCode').val() ? $('#estCode').val() : '';
	let records = $('#records').val() ? $('#records').val() : 1;
	if(reqData)
	{
		reqData	=	$('#missingRecordFilter').serialize();
		$.cookie('nameMissingTab', name);
		$.cookie('empNumberMissingTab', empNumber);
		$.cookie('estCodeMissingTab', estCode);
		$.cookie('recordsMissingTab', records);
	}else
	{
		name	=	name ? name : $.cookie('nameMissingTab');
		empNumber	=	empNumber ? empNumber : $.cookie('empNumberMissingTab');
		estCode	=	estCode ? estCode : $.cookie('estCodeMissingTab');
		records	=	$.cookie('recordsMissingTab') ? $.cookie('recordsMissingTab') : records;
		reqData	=	{'conrollerName':'getMissingRecord', 'recordOffset':0, 'name':name, 'empNumber':empNumber, 'estCode':estCode, 'records':records};
	}
	clearAllTabPanels();
	$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: reqData,
			success: function (returnHtml){
				document.getElementById('tabs-1').innerHTML = returnHtml;
				$(".teamPayRecordSection").height($(window).height() - 266);
				checkBoxJs();
				$('#name').val($.cookie("nameMissingTab"));
				$('#empNumber').val($.cookie("empNumberMissingTab"));
				$('#estCode').val($.cookie("estCodeMissingTab"));
				let recordsMissingTab = $.cookie("recordsMissingTab") ? $.cookie("recordsMissingTab") : 1;
				$('#records').val(recordsMissingTab);
				$('#missingRecordContainer').scrollTop(Math.abs($.cookie("missingRecordPosTop")) + 100);
		}
	});
}

function checkBoxJs()
{
	$('.checkbox').click(function() {
		let inputElements = [].slice.call(document.querySelectorAll('.checkbox'));
		let checkedValue = inputElements.filter(chk => chk.checked).length;
		if(checkedValue > 0)
		{
			document.getElementById('approveBtn').disabled	=	false;
			document.getElementById('onHoldBtn').disabled	=	false;
		}else
		{
			document.getElementById('approveBtn').disabled	=	true;
			document.getElementById('onHoldBtn').disabled	=	true;
		}
	});
	$("#CheckAllRecord").click(function () { // Check all id
	   var rows, checked;
	   if($('#missingRecordEntries').length)
	   {
			rows = $('#missingRecordEntries').find('tbody tr');
	   }else
	   {
		   rows = $('#incorrectRecordEntries').find('tbody tr');
	   }
		checked = $(this).prop('checked');
		$.each(rows, function() {
			if($($(this).find('td').eq(0)).find('input').attr("disabled") != 'disabled')
			{
				var checkbox = $($(this).find('td').eq(0)).find('input').prop('checked', checked); // check box column "0"
			}
		});
		let inputElements = [].slice.call(document.querySelectorAll('.checkbox'));
		let checkedValue = inputElements.filter(chk => chk.checked).length;
		if(checkedValue > 0)
		{
			document.getElementById('approveBtn').disabled	=	false;
			document.getElementById('onHoldBtn').disabled	=	false;
		}else
		{
			document.getElementById('approveBtn').disabled	=	true;
			document.getElementById('onHoldBtn').disabled	=	true;
		}
	});
}
function syncRefTables()
{
	customConfirm('You are about to sync all reference tables from allocate link to A7. Please click on Yes to continue.',
		function(){
		  $.ajax({
				  type: 'POST',
				  url: 'page-includes/admin/charging/index.php',
				  data: {'conrollerName':'syncRefTables'},
				  success: function (result){
					if(result == 'success')
					{
						closeCustomAlert();
						actionHandler("teamPayIntegration");
						customAlert('All refrence tables has been synced in A7.', 1000);
					}
				}
			});
		},
		function() {
		}
	);
}
function clearFilter(tabNo = 1)
{
	$('#name').val('');
	$('#empNumber').val('');
	$('#estCode').val('');
	$('#records').val(1);
	if(tabNo > 1)
	{
		$('#schedulingTeamId').val('');
	}
}

function getLeaversRecord(reqData = false)
{
	document.getElementById('approveBtn').disabled	=	true;
	document.getElementById('onHoldBtn').disabled	=	true;
	$('#teamPayActionBTN').show();
	let recordOffsetVal = $('#recordOffset').val() ? $('#recordOffset').val() : 0;
	let name = $('#name').val() ? $('#name').val() : '';
	let empNumber = $('#empNumber').val() ? $('#empNumber').val() : '';
	let estCode = $('#estCode').val() ? $('#estCode').val() : '';
	let schedulingTeamId = $('#schedulingTeamId').val() ? $('#schedulingTeamId').val() : '';
	let records = $('#records').val() ? $('#records').val() : 1;
	if(reqData)
	{
		reqData	=	$('#leaversRecordFilter').serialize();
		$.cookie('nameLeaversTab', name);
		$.cookie('empNumberLeaversTab', empNumber);
		$.cookie('estCodeLeaversTab', estCode);
		$.cookie('schedulingTeamIdLeaversTab', schedulingTeamId);
		$.cookie('recordsLeaversTab', records);
	}else
	{
		name	=	name ? name : $.cookie('nameLeaversTab');
		empNumber	=	empNumber ? empNumber : $.cookie('empNumberLeaversTab');
		estCode	=	estCode ? estCode : $.cookie('estCodeLeaversTab');
		schedulingTeamId	=	schedulingTeamId ? schedulingTeamId : $.cookie('schedulingTeamIdLeaversTab');
		records	=	$.cookie('recordsMissingTab') ? $.cookie('recordsMissingTab') : records;
		reqData	=	{'conrollerName':'getLeaversRecord', 'recordOffset':0, 'name':name, 'empNumber':empNumber, 'estCode':estCode, 'schedulingTeamId':schedulingTeamId, 'records':records};
	}
	clearAllTabPanels();
	$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: reqData,
			success: function (returnHtml){
				document.getElementById('tabs-3').innerHTML = returnHtml;
				$(".teamPayRecordSection").height($(window).height() - 266);
				checkBoxJs();
				$('#name').val($.cookie("nameLeaversTab"));
				$('#empNumber').val($.cookie("empNumberLeaversTab"));
				$('#estCode').val($.cookie("estCodeLeaversTab"));
				$('#schedulingTeamId').val($.cookie("schedulingTeamIdLeaversTab"));
				let recordsLeaversTab = $.cookie("recordsLeaversTab") ? $.cookie("recordsLeaversTab") : 1;
				$('#records').val(recordsLeaversTab);
				$('#leaversRecordContainer').scrollTop(Math.abs($.cookie("leaversRecordPosTop")) + 100);
		}
	});
}

function clearMissingRecordsFilter()
{
	$.cookie('nameMissingTab', '');
	$.cookie('empNumberMissingTab', '');
	$.cookie('estCodeMissingTab', '');
	$.cookie('recordsMissingTab', '');
	document.getElementById('filterType').value = 1;
	actionHandler('teamPayIntegration');
}
function clearIncorrectRecordsFilter()
{
	$.cookie('nameIncorrectTab', '');
	$.cookie('empNumberIncorrectTab', '');
	$.cookie('estCodeIncorrectTab', '');
	$.cookie('schedulingTeamIdIncorrectTab', '');
	$.cookie('recordsIncorrectTab', '');
	document.getElementById('filterType').value = 1;
	clearFilter(2);
	getIncorrectRecord();
}

function clearLeaversRecordsFilter()
{
	$.cookie('nameLeaversTab', '');
	$.cookie('empNumberLeaversTab', '');
	$.cookie('estCodeLeaversTab', '');
	$.cookie('schedulingTeamIdLeaversTab', '');
	$.cookie('recordsLeaversTab', '');
	document.getElementById('filterType').value = 1;
	clearFilter(3);
	getLeaversRecord();
}

function saveScrollPosForMissingRecord()
{
	let top = $('#missingRecordPos').position().top;
	$.cookie("missingRecordPosTop", top);
}

function saveScrollPosForIncorrectRecord()
{
	let top = $('#incorrectRecordPos').position().top;
	$.cookie("incorrectRecordPosTop", top);
}

function saveScrollPosForLeaversRecord()
{
	let top = $('#leaversRecordPos').position().top;
	$.cookie("leaversRecordPosTop", top);
}

function getEFTDiscNews(reqData = false)
{
	$('#teamPayActionBTN').hide();
	reqData	=	{'conrollerName':'getEFTDiscNews', 'recordOffset':0};
	clearAllTabPanels();
	$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: reqData,
			success: function (returnHtml){
				document.getElementById('tabs-4').innerHTML = returnHtml;
				$(".teamPayRecordSection").height($(window).height() - 266);
				checkBoxJs();
		}
	});
}

function getPayTypeNews(reqData = false)
{
	$('#teamPayActionBTN').hide();
	reqData	=	{'conrollerName':'getPayTypeNews', 'recordOffset':0};
	clearAllTabPanels();
	$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: reqData,
			success: function (returnHtml){
				document.getElementById('tabs-5').innerHTML = returnHtml;
		}
	});
}
function getEFTDiscTG(reqData = false)
{
	$('#teamPayActionBTN').hide();
	reqData	=	{'conrollerName':'getEFTDiscTG', 'recordOffset':0};
	clearAllTabPanels();
	$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: reqData,
			success: function (returnHtml){
				document.getElementById('tabs-6').innerHTML = returnHtml;
		}
	});
}
function getPayTypeTG(reqData = false)
{
	$('#teamPayActionBTN').hide();
	reqData	=	{'conrollerName':'getPayTypeTG', 'recordOffset':0};
	clearAllTabPanels();
	$.ajax({
			type: 'POST',
			url: 'page-includes/admin/charging/index.php',
			data: reqData,
			success: function (returnHtml){
				document.getElementById('tabs-7').innerHTML = returnHtml;
		}
	});
}

function clearAllTabPanels()
{
	for(let i = 1; i <= 7; i++)
	{
		document.getElementById('tabs-' + i).innerHTML = '';
	}
}

</script>