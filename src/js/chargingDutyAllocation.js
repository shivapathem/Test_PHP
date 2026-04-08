$(document).ready(function () {
    resetall();
    let actionType = $("#ActionType").val();
    if (actionType == "Insert") {
        if ($("#maintainCharging").prop("checked") == true) {
            $("#saveChargeWbsCode").attr("disabled", false);
        } else {
            $("#saveChargeWbsCode").attr("disabled", true);
        }
        $("#deleteChargeWbsCode").attr("disabled", true);
        $("#footerCharging").html("New Charging Record");
    } else {
        $("#saveChargeWbsCode").attr("disabled", false);
        $("#deleteChargeWbsCode").attr("disabled", false);
        $("#footerCharging").html("Edit Charging Record");
    }
    $("#chargeWbsQuantity").on("change", function(){
        integerTypeValidationNoDecimalNegativeAllowed("#chargeWbsQuantity", "Quantity");
        validateQuantity();
    });
    $("#chargeWbsQuantity").on("keyup", function() {
        $("#formDataChanged").val(1);
    });
    $("#chargeCodemain").on("change", function() {
        $("#formDataChanged").val(1);
    });
    $("#Comment").on("keyup", function() {
        $("#formDataChanged").val(1);
    });
    $("#chargeWbsContact").on("keyup", function() {
        $("#formDataChanged").val(1);
    });
    $("#chargeWbsTelephone").on("keyup", function() {
        $("#formDataChanged").val(1);
    });
    $("#activityCode").on("change", function() {
        $("#formDataChanged").val(1);
    });
    $("input[name=dutyCharging][value=Actual]").on("click", function (){
        let chargeWbsQuantityActual = $("#chargeWbsQuantity").val();
        let activityUnitPriceActual = $("#UnitPrice").val();
        if (chargeWbsQuantityActual != "") {
            if (activityUnitPriceActual == "" || activityUnitPriceActual == null) {
                customAlertByModel("Please select activity code from dropdown.");
            }
            if (chargeWbsQuantityActual > 50) {
                $("#chargeWbsQuantity").val("");
                $("#chargeWbsQuantity").focus();
                customAlertByModel("Maximum limit for Quantity is 50.");
            }

            let chargeWbsTotalPriceActual = chargeWbsQuantityActual * parseFloat(activityUnitPriceActual.substring(1, (activityUnitPriceActual.length)));
            chargeWbsTotalPriceActual = Math.round(chargeWbsTotalPriceActual * 100) / 100;
            if (chargeWbsTotalPriceActual > 1000) {
                customConfirmModal("You have created a charging record with a total value greater than £1000.00. Are you sure you wish to continue?",function(){
                        $("#chargeWbsTotalPrice").val("£" + chargeWbsTotalPriceActual);
                        $("#totalPriceValidation").val(1);
                    },
                    function() {
                        $("#chargeWbsQuantity").focus();
                        $("#chargeWbsQuantity").val("");
                        $("#chargeWbsTotalPrice").val("");
                    });
            }
            else {
                chargeWbsTotalPriceActual = isNaN(chargeWbsTotalPriceActual) ? 0 : chargeWbsTotalPriceActual;
                $("#chargeWbsTotalPrice").val("£" + chargeWbsTotalPriceActual);
                $("#totalPriceValidation").val(1);
            }
        }
    });
});

function resetall() {
    $("#activityCode").on("change", function(){
        let activityDesc = $('option:selected', this).attr('ActivityDesc');
        let activityPrice = $('option:selected', this).attr('ActivityPrice');
        if ($("#activityCode").val() == '-1') {
            $("#activityCodeDesc").val("");
            $("#activityUnitPrice").val("");
            $("#UnitPrice").val("");
        } else {
            $("#activityCodeDesc").val(activityDesc);
            $("#activityUnitPrice").val(activityPrice);
            $("#UnitPrice").val('£' + activityPrice);
        }
    });
    $("#WbsCode").hide();

    $("#chargeCodemain").on("change", function(){
        let chargeCodeDesc = $('option:selected', this).attr('chargeCodeDesc');
        $("#chargeWbsDesc").val(chargeCodeDesc);
        $("#saveChargeWbsCode").attr("disabled", false);
        $(".btn-focus").removeClass('btn-focus');
        $("#saveChargeWbsCode").addClass('btn-focus');
    });

}

function switchChargeWbsCodeRadio (chargeWbsCodeRadio) {
    if(chargeWbsCodeRadio == 'chargeCodeRadio') {
        $("#chargeWbsCodelabel").html("Charge Code<span class=\"required\">*</span>");
        $("#chargeWbsCodelabel").removeClass("RightMarginWBS");
        $("#chargeWbsDesc").val("");
        $("#chargeCodemain").show();
        $("#WbsCode").hide();
        $("#chargeCodemain").prop("disabled", false);
        $("#WbsCode").prop("disabled", true);
        $("#chargeCodemain").val("-1");
        $('#chargeWbsDesc').attr('fieldname','Charge Code');
        $("#chargeCodemain").on("change", function(){
            let chargeCodeDesc = $('option:selected', this).attr('chargeCodeDesc');
            $("#chargeWbsDesc").val(chargeCodeDesc);
            $("#saveChargeWbsCode").attr("disabled", false);
        });
    } else {
        $("#chargeWbsCodelabel").html("WBS Code<span class=\"required\">*</span>");
        $("#chargeWbsCodelabel").addClass("RightMarginWBS");
        $("#chargeWbsDesc").val("");
        $("#WbsCode").val("-1");
        $("#WbsCode").show();
        $("#chargeCodemain").hide();
        $("#WbsCode").prop("disabled", false);
        $("#chargeCodemain").prop("disabled", true);
        $('#chargeWbsDesc').attr('fieldname','WBS Code');
        $("#WbsCode").on("change", function(){
            let wbsCodeDesc = $('option:selected', this).attr('wbsCodeDesc');
            $("#chargeWbsDesc").val(wbsCodeDesc);
            $("#saveChargeWbsCode").attr("disabled", false);
        });
    }
}

function createNewRecordDutyChargeMapping() {
    if ($("#maintainCharging").prop("checked") != true) {
        $("#ActionType").val("Insert");
        $("#activityCode").val($("#hiddenDefaultActivityCode").val());
		if (!$("#activityCode").val())
		{
			$('select[name^="activityCode"] option:selected').attr("selected",null);
			$("#activityCode")[0].selectedIndex=1;
		}
        let activityDesc = $('option:selected', "#activityCode").attr('ActivityDesc');
        let activityPrice = $('option:selected', "#activityCode").attr('ActivityPrice');
        if ($("#activityCode").val() == '-1') {
            $("#activityCodeDesc").val("");
            $("#activityUnitPrice").val("");
            $("#UnitPrice").val("");
        } else {
            $("#activityCodeDesc").val(activityDesc);
            $("#activityUnitPrice").val(activityPrice);
            $("#UnitPrice").val('£' + activityPrice);
        }

        $("#chargeCodemain").val("-1");
        $("#WbsCode").val("-1");
        $("#chargeWbsDesc").val("");
        $("#Comment").val("");
        $("#chargeWbsContact").val("");
        $("#chargeWbsTelephone").val("");
        $("#chargeWbsQuantity").val("");
        $("#chargeWbsTotalPrice").val("");
        $("#footerCharging").html("New Charging Record");
        $("#dutyCharging").prop('checked',false);
        $("input[name=dutyCharging][value=Provisional]").prop('checked', true);
        $("#chargeWbsCodelabel").html("Charge Code<span class=\"required\">*</span>");
        $("#chargeWbsCodelabel").removeClass("RightMarginWBS");
        $("#chargeCodemain").show();
        $("#WbsCode").hide();
        $("#chargeCodemain").prop("disabled", false);
        $("#WbsCode").prop("disabled", true);
        $("#saveChargeWbsCode").attr("disabled", true);
        $("#deleteChargeWbsCode").attr("disabled", true);
        $("input[name=chargeWbsCodeRadio][value=chargeCodeRadio]").prop('checked', true);
        $("input[name=chargeWbsCodeRadio][value=wbsCodeRadio]").prop('checked', false);
        $('td').removeClass('highlightRed');
    } else {
        $("#ActionType").val("Insert");
        $("#activityCode").val($("#hiddenDefaultActivityCode").val());
        if (!$("#activityCode").val())
        {
            $('select[name^="activityCode"] option:selected').attr("selected",null);
            $("#activityCode")[0].selectedIndex=1;
        }
        let activityDesc = $('option:selected', "#activityCode").attr('ActivityDesc');
        let activityPrice = $('option:selected', "#activityCode").attr('ActivityPrice');
        if ($("#activityCode").val() == '-1') {
            $("#activityCodeDesc").val("");
            $("#activityUnitPrice").val("");
            $("#UnitPrice").val("");
        } else {
            $("#activityCodeDesc").val(activityDesc);
            $("#activityUnitPrice").val(activityPrice);
            $("#UnitPrice").val('£' + activityPrice);
        }
        $("#footerCharging").html("New Charging Record");
        $("#saveChargeWbsCode").attr("disabled", false);
        $("#deleteChargeWbsCode").attr("disabled", false);
    }
    $("input[name=chargeWbsCodeRadio][value=chargeCodeRadio]").attr("disabled",false);
    $("input[name=chargeWbsCodeRadio][value=wbsCodeRadio]").attr("disabled",false);
    $("#chargeCodemain").prop("disabled",false);
    $("#WbsCode").attr("disabled",false);
    $("#Comment").attr("disabled",false);
    $("#chargeWbsContact").attr("disabled",false);
    $("#chargeWbsTelephone").attr("disabled",false);
    $("#chargeWbsQuantity").attr("disabled",false);
    $("#saveChargeWbsCode").attr("disabled",false);
    $(".btn-focus").removeClass('btn-focus');
    $("#saveChargeWbsCode").addClass('btn-focus');

}

function closeFaceboxMain() {
    if ($("#formDataChanged").val() == 1) {
        customConfirmModal("You have changed this record would you like to Save the Changes?",
            function(){
                let errMsg = "";
                let validRes = $("#totalPriceValidation").val();
                if ($("#chargeCodemain").val() == '-1' && $("input[type='radio'][id='chargeWbsCodeRadio']:checked").val() == "chargeCodeRadio") {
                    errMsg += "Please enter Charge Code.<br>";
                }
                if ($("#WbsCode").val() == '-1' && $("input[type='radio'][id='chargeWbsCodeRadio']:checked").val() == "wbsCodeRadio") {
                    errMsg += "Please enter Wbs Code.<br>";
                }
                if ($("#activityCode").val() == '-1') {
                    errMsg += "Please select Activity Code.<br>";
                }
                if ($("#chargeWbsContact").val().trim() == '') {
                    errMsg += "Please enter Contact.<br>";
                }
                if ($("#chargeWbsTelephone").val().trim() == '') {
                    errMsg += "Please enter Telephone.<br>";
                }
                if ($("#chargeWbsQuantity").val().trim() == '') {
                    errMsg += "Please enter Quantity.<br>";
                }
                if ($("#chargeWbsQuantity").val().trim() != '' && ((validRes == 1) || (validRes == "1"))) {
                    errMsg += "Please validate the quantity. Then try again.";
                }

                if (errMsg != "") {
                    customAlertByModel(errMsg);
                    return;
                } else {
                    saveChargingDetails();
                    setTimeout(function () {
                        $.facebox.close();
                    }, 500);
                }
            },
            function() {
                $.facebox.close();
            });
    } else {
        $.facebox.close();
    }
}

function highlightChargingRow(chargingId,allChargingRecords,abc = 0)
{
    let PrevMaintainCharging = 0;
    let activityPrice = 0;
    $("#SelectedCharging").val(chargingId);
	let masterDutyId = $("#masterDutyId").val() ? $("#masterDutyId").val() : 0;
    if (masterDutyId == allChargingRecords.MasterDutyId) {
        $("#ActionType").val("Edit");
        $("#footerCharging").html("Edit Charging Record");
        if (allChargingRecords.SentToFinance == "Yes") {
            $("#deleteChargeWbsCode").attr("disabled", true);
        } else {
            $("#deleteChargeWbsCode").attr("disabled", false);
        }
    } else {
        $("#deleteChargeWbsCode").attr("disabled", true);
        $("#footerCharging").html("New Charging Record");
        $("#ActionType").val("Insert");
    }
    $("#chargingId").val(allChargingRecords.ChargingId);
    $('td').removeClass('highlightRed');
    $('#chargingTableColumn-1-'+chargingId).addClass('highlightRed');
    $('#chargingTableColumn-2-'+chargingId).addClass('highlightRed');
    // Check Maintain Code Start
    if ($("#maintainCharging").prop("checked") == true) {
        maintainChargeCode();
        PrevMaintainCharging = 1
    }
    // Check Maintain Code End
    // Block Activity Code Start
    if($('#EventCallMouseDownTable').val() == 'TableClick') {
        if(allChargingRecords) {
            $('#activityCode option').each(function(){
                if (this.value == allChargingRecords.ActivityCodeId)
                {
                    $('#activityCode').val(parseInt(allChargingRecords.ActivityCodeId));
                    setTimeout(function () {
                        let activityDesc = $('option:selected', '#activityCode').attr('ActivityDesc');
                        activityPrice = $('option:selected', '#activityCode').attr('ActivityPrice');
                        $("#activityCodeDesc").val(activityDesc);
                        $("#activityUnitPrice").val(activityPrice);
                        $("#UnitPrice").val('£' + activityPrice);
                    }, 20);
                }
            });
        }
    }
    $("#dutyCharging").prop('checked',false);
    $("input[name=dutyCharging][value=" + allChargingRecords.Status + "]").prop('checked', true);
    // Block Activity Code End

    // Block Start Charge Code
    let codeType = allChargingRecords.CodeType == 1 ? 'wbsCodeRadio' : 'chargeCodeRadio';
    $("input[name=chargeWbsCodeRadio][value=" + codeType + "]").prop('checked', true);
    if(codeType == 'chargeCodeRadio') {
        $("#chargeWbsCodelabel").html("Charge Code<span class=\"required\">*</span>");
        $("#chargeWbsCodelabel").removeClass("RightMarginWBS");
        $("#chargeWbsDesc").val("");
        $("#chargeCodemain").show();
        $("#WbsCode").hide();
        if (allChargingRecords.SentToFinance == "Yes") {
            $("#chargeCodemain").prop("disabled", true);
            $("#WbsCode").prop("disabled", true);
        } else {
            $("#chargeCodemain").prop("disabled", false);
            $("#WbsCode").prop("disabled", true);
        }
        $('#chargeWbsDesc').attr('fieldname','Charge Code');
        $("#chargeCodemain").val(parseInt(allChargingRecords.ChargeCodeId));

        setTimeout(function() {
            let chargeCodeDesc = $('option:selected', '#chargeCodemain').attr('chargeCodeDesc');
            $("#chargeWbsDesc").val(chargeCodeDesc);
        }, 20);
    } else {
        $("#chargeWbsCodelabel").html("WBS Code<span class=\"required\">*</span>");
        $("#chargeWbsCodelabel").addClass("RightMarginWBS");
        $("#chargeWbsDesc").val("");
        $("#WbsCode").show();
        $("#chargeCodemain").hide();
        $("#WbsCode").prop("disabled", false);
        $("#chargeCodemain").prop("disabled", true);
        $("#WbsCode").val(parseInt(allChargingRecords.ChargeCodeId));
        $('#chargeWbsDesc').attr('fieldname','WBS Code');
        setTimeout(function() {
            let wbsCodeDesc = $('option:selected', '#WbsCode').attr('wbsCodeDesc');
            $("#chargeWbsDesc").val(wbsCodeDesc);
        }, 20);
    }
    $("#Comment").val(allChargingRecords.Comments);
    $("#chargeWbsContact").val(allChargingRecords.Contact);
    $("#chargeWbsTelephone").val(allChargingRecords.Telephone);
    $("#chargeWbsQuantity").val(allChargingRecords.Quantity);
    if (allChargingRecords.MaintainCharging == 1 || PrevMaintainCharging == 1) {
        $("#maintainCharging").prop('checked', true);
        $("#saveChargeWbsCode").attr("disabled", false);
    } else {
        $("#maintainCharging").prop('checked', false);
        if ($("#ActionType").val() == 'Insert') {
            $("#saveChargeWbsCode").attr("disabled", true);
        }
    }
    if (allChargingRecords.SentToFinance == "Yes") {
        $("input[name=chargeWbsCodeRadio][value=chargeCodeRadio]").attr("disabled",true);
        $("input[name=chargeWbsCodeRadio][value=wbsCodeRadio]").attr("disabled",true);
        $("#chargeCodemain").prop("disabled",true);
        $("#WbsCode").prop("disabled",true);
        $("#Comment").attr("disabled",true);
        $("#chargeWbsContact").attr("disabled",true);
        $("#chargeWbsTelephone").attr("disabled",true);
        $("#chargeWbsQuantity").attr("disabled",true);
        $("#saveChargeWbsCode").attr("disabled",true);
    } else {
        $("input[name=chargeWbsCodeRadio][value=chargeCodeRadio]").attr("disabled",false);
        $("input[name=chargeWbsCodeRadio][value=wbsCodeRadio]").attr("disabled",false);
        $("#chargeCodemain").prop("disabled",false);
        $("#WbsCode").prop("disabled",false);
        $("#Comment").attr("disabled",false);
        $("#chargeWbsContact").attr("disabled",false);
        $("#chargeWbsTelephone").attr("disabled",false);
        $("#chargeWbsQuantity").attr("disabled",false);
        $("#saveChargeWbsCode").attr("disabled",false);
    }
    if ($("#maintainCharging").prop("checked") == true) {
        setTimeout(function() {
            let UnitPriceNew = $('#activityUnitPrice').val();
            let totalPriceChargeWBS = (allChargingRecords.Quantity * UnitPriceNew);
            $("#chargeWbsTotalPrice").val("£" + Math.round(totalPriceChargeWBS * 100) / 100);
        }, 50);
    } else {
        $("#chargeWbsTotalPrice").val("£" + Math.round(allChargingRecords.TotalPrice * 100) / 100);
    }

    if (allChargingRecords.SentToFinance == "Yes") {
        $(".btn-focus").removeClass('btn-focus');
        $("#newChargeWbsCode").addClass('btn-focus');
    } else {
        $(".btn-focus").removeClass('btn-focus');
        $("#saveChargeWbsCode").addClass('btn-focus');
    }

    // MaintainCharging
    // Block End Charge Code
}

function clickDeleteButton() {
    customConfirmModal("Are you sure to Delete the charge from this duty ?",function(){
            $("#ActionType").val("Delete");
            $("#activityCode").val($("#hiddenDefaultActivityCode").val());
            if (!$("#activityCode").val())
            {
                $('select[name^="activityCode"] option:selected').attr("selected",null);
                $("#activityCode")[0].selectedIndex=1;
            }
            let activityDesc = $('option:selected', "#activityCode").attr('ActivityDesc');
            let activityPrice = $('option:selected', "#activityCode").attr('ActivityPrice');
            if ($("#activityCode").val() == '-1') {
                $("#activityCodeDesc").val("");
                $("#activityUnitPrice").val("");
                $("#UnitPrice").val("");
            } else {
                $("#activityCodeDesc").val(activityDesc);
                $("#activityUnitPrice").val(activityPrice);
                $("#UnitPrice").val('£' + activityPrice);
            }
        formHandler('chargingDutyMappingForm');
    },
    function() {
        return;
    });

}

/* Function to maintain charge code */
function maintainChargeCode() {
    let maintainCharging = $("#maintainCharging").prop("checked") == true ? 1: 0;
    if ($("#maintainCharging").prop("checked") == true) {
        $("#chargeWbsTotalPrice").val("£" + Math.round((parseInt($("#UnitPrice").val().substring(1, ($("#UnitPrice").val().length))) * $("#chargeWbsQuantity").val()) * 100) / 100);
    } else {
        let selectedCharging = $("#SelectedCharging").val();
        if($("#chargingTableColumn-5-"+selectedCharging).length)
        {
            let oldUnitPrice = parseInt($("#chargingTableColumn-5-"+selectedCharging).html().substring(1, ($("#chargingTableColumn-5-"+selectedCharging).html().length)));
            $("#chargeWbsTotalPrice").val("£" + Math.round((oldUnitPrice * $("#chargeWbsQuantity").val()) * 100) / 100);
        }
    }
    let SchedulingTeamId = $(".SchedulingTeamIdMaintain").val();
    $.ajax({
        url: "../page-includes/admin/charging/index.php",
        type: "POST",
        dataType: "json",
        async:false,
        data: {
            "conrollerName" : "maintainCharging",
            "chargingId" : $("#chargingId").val(),
            "maintainCharging" : maintainCharging,
            "SchedulingTeamId" : SchedulingTeamId
        },
        success: function (data) {
            return;
        }
    });
}

function validateQuantity() {
    let activityUnitPrice = $("#activityUnitPrice").val();
    if ($('#chargeWbsQuantity').val() > 50) {
        $("#chargeWbsQuantity").val("");
        $("#chargeWbsQuantity").focus();
        customAlertByModel("Maximum limit for Quantity is 50.");
    }
    if (activityUnitPrice == "" || activityUnitPrice == null) {
        customAlertByModel("Please select activity code from dropdown.");
        $("#chargeWbsQuantity").val("");
        $("#chargeWbsQuantity").focus();
    }
    else {
        let chargeWbsTotalPrice = $("#chargeWbsQuantity").val() * parseFloat(activityUnitPrice);
		chargeWbsTotalPrice = Math.round(chargeWbsTotalPrice * 100) / 100;
        if (chargeWbsTotalPrice > 1000) {
            customConfirmModal("You have created a charging record with a total value greater than £1000.00. Are you sure you wish to continue?",function(){
                    $("#chargeWbsTotalPrice").val("£" + chargeWbsTotalPrice);
                    $("#totalPriceValidation").val(1);
                },
                function() {
                    $("#chargeWbsQuantity").focus();
                    $("#chargeWbsQuantity").val("");
                    $("#chargeWbsTotalPrice").val("");
                });
        }
        else {
			chargeWbsTotalPrice = isNaN(chargeWbsTotalPrice) ? 0 : chargeWbsTotalPrice;
			$("#chargeWbsTotalPrice").val("£" + chargeWbsTotalPrice);
            $("#totalPriceValidation").val(1);
        }
    }
}

function saveChargingDetails() {
    let validRes = $("#totalPriceValidation").val();
    if ((validRes == 1) || (validRes == "1")) {
        formHandler('chargingDutyMappingForm');
        let NewAllocId = $("#allocationId").val();
        $("#"+NewAllocId).attr("data-charging-present",1);
        $('div[data-id="' + NewAllocId + '"]').attr("data-charging-present",1);
    }
    else if ($("#footerCharging").html() == "Edit Charging Record") {
        formHandler('chargingDutyMappingForm');
    }
    else {
        customAlertByModel("Please validate the quantity. Then try again.");
        $("#totalPriceValidation").val(1);
    }
    return;
}
