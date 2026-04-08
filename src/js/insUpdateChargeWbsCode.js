$(document).ready(function () {
    let codeType = $("#js_codetype").val();
    let canview = 0;
    let cancreate = 0;
    let canmodify = 0;
    let candelete = 0;
    if(codeType == 1){
        tableId = 'wbsCodeEntries';
        container =  'wbsContainer';
        searchBox = "Search WBS Code";
        getUserPemissionforJS(codeType);
        $("#js_addnewbutton").on("click", function(){
            let actionType = 'add';
            getalldivisionsbyuser(0);
            openFaceboxPopup(codeType,actionType);
        });
        if (!$('#ChargeWBSTableNoDataFound').length) {
            callContextMenuWbsCode(codeType);
        }
    } else {
        getUserPemissionforJS(codeType);
        $("#js_addnewbutton").on("click", function(){
            let actionType = 'add';
            getalldivisionsbyuser(0);
            openFaceboxPopup(codeType,actionType);
        });
        if (!$('#ChargeWBSTableNoDataFound').length) {
            callContextMenuChargeCode(codeType);
        }
    }
});

function callContextMenuChargeCode (codeType) {
    $('.receiverCode-context-menu').contextMenu('destroy');
    //Context menu configuration
    if (cancreate == 1) {
        $(function () {
            $.contextMenu({
                selector: '.receiverCode-context-menu',
                items: {
                    "Add": {
                        name: "Add Charge Code",
                        icon: "add",
                        callback: function (key, options) {
                            let actionType = 'add';
                            openFaceboxPopup(codeType, actionType);
                            getalldivisionsbyuser(0);
                            return;
                        }
                    },
                    "Edit": {
                        name: "Modify Charge Code",
                        icon: "edit",
                        callback: function (key, options) {
                            $("#editWbsCode").val("");
                            $("#editWbsCodeDesc").val("");
                            $("#ChargeWbsCodeId").val("");
                            let DivisionId = options.$trigger.attr("DivisionId");
                            let ChargeWbsCodeName = options.$trigger.attr("ChargeWbsCodeName");
                            let Description = options.$trigger.attr("Description");
                            let ChargeWbsCodeId = options.$trigger.attr("ChargeWbsCodeId");
                            let actionType = 'edit';
                            openFaceboxPopup(codeType, actionType);
                            $("#editChargeCode").val(ChargeWbsCodeName);
                            $("#editChargeCodeDesc").val(Description);
                            $("#ChargeWbsCodeId").val(ChargeWbsCodeId);
                            getalldivisionsbyuser(DivisionId);
                            return;
                        }
                    }
                }
            });
        });
    } else {
        //Context menu configuration
        $(function () {
            $.contextMenu({
                selector: '.receiverCode-context-menu',
                items: {
                    "Edit": {
                        name: "Modify Charge Code",
                        icon: "edit",
                        callback: function (key, options) {
                            $("#editWbsCode").val("");
                            $("#editWbsCodeDesc").val("");
                            $("#ChargeWbsCodeId").val("");
                            let DivisionId = options.$trigger.attr("DivisionId");
                            let ChargeWbsCodeName = options.$trigger.attr("ChargeWbsCodeName");
                            let Description = options.$trigger.attr("Description");
                            let ChargeWbsCodeId = options.$trigger.attr("ChargeWbsCodeId");
                            let actionType = 'edit';
                            openFaceboxPopup(codeType, actionType);
                            $("#editChargeCode").val(ChargeWbsCodeName);
                            $("#editChargeCodeDesc").val(Description);
                            $("#ChargeWbsCodeId").val(ChargeWbsCodeId);
                            getalldivisionsbyuser(DivisionId);
                            return;
                        }
                    }
                }
            });
        });
    }
}

function callContextMenuWbsCode (codeType) {
    $('.receiverCode-context-menu').contextMenu('destroy');
    //Context menu configuration
    if (cancreate == 1) {
        $(function () {
            $.contextMenu({
                selector: '.receiverCode-context-menu',
                items: {
                    "Add": {
                        name: "Add WBS Code",
                        icon: "add",
                        callback: function (key, options) {
                            let actionType = 'add';
                            openFaceboxPopup(codeType, actionType);
                            getalldivisionsbyuser(0);
                            return;
                        }
                    },
                    "Edit": {
                        name: "Modify WBS Code",
                        icon: "edit",
                        callback: function (key, options) {
                            $("#editWbsCode").val("");
                            $("#editWbsCodeDesc").val("");
                            $("#ChargeWbsCodeId").val("");
                            let actionType = 'edit';
                            let DivisionId = options.$trigger.attr("DivisionId");
                            let ChargeWbsCodeName = options.$trigger.attr("ChargeWbsCodeName");
                            let Description = options.$trigger.attr("Description");
                            let ChargeWbsCodeId = options.$trigger.attr("ChargeWbsCodeId");
                            openFaceboxPopup(codeType, actionType);
                            $("#editWbsCode").val(ChargeWbsCodeName);
                            $("#addEditWbsCodeName").val(ChargeWbsCodeName);
                            $("#editWbsCodeDesc").val(Description);
                            $("#ChargeWbsCodeId").val(ChargeWbsCodeId);
                            getalldivisionsbyuser(DivisionId);
                            return;
                        }
                    }
                }
            });
        });
    } else {
        $(function () {
            $.contextMenu({
                selector: '.receiverCode-context-menu',
                items: {
                    "Edit": {
                        name: "Modify WBS Code",
                        icon: "edit",
                        callback: function (key, options) {
                            $("#editWbsCode").val("");
                            $("#editWbsCodeDesc").val("");
                            $("#ChargeWbsCodeId").val("");
                            let actionType = 'edit';
                            let DivisionId = options.$trigger.attr("DivisionId");
                            let ChargeWbsCodeName = options.$trigger.attr("ChargeWbsCodeName");
                            let Description = options.$trigger.attr("Description");
                            let ChargeWbsCodeId = options.$trigger.attr("ChargeWbsCodeId");
                            openFaceboxPopup(codeType, actionType);
                            $("#editWbsCode").val(ChargeWbsCodeName);
                            $("#addEditWbsCodeName").val(ChargeWbsCodeName);
                            $("#editWbsCodeDesc").val(Description);
                            $("#ChargeWbsCodeId").val(ChargeWbsCodeId);
                            getalldivisionsbyuser(DivisionId);
                            return;
                        }
                    }
                }
            });
        });
    }
}

function openFaceboxPopup(codeType,actionType) {
    $.ajax({
        url: "../page-includes/admin/charging/popupController.php",
        type: "POST",
        dataType: "html",
        async: false,
        data: {
            popupName : 'popupChargeWbsCode',
            codeType : codeType,
            actionType : actionType
        },
        success: function (data) {
            $.facebox(data);
            return;
        }
    });
}

function CancelChargeCode() {
    $.facebox.close();
}

/*
* @Description : Function to get all division by user.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getalldivisionsbyuser(DivisionId) {
    $.ajax({
        type: 'POST',
        url: '../page-includes/admin/process/schedulingTeam.php',
        dataType: "json",
        data: {
            task: 'getalldivisionsbyuser_AllUsers',
            RequestFrom: 'ChargeCode'
        },
        success: function (Result) {
            if (Result.status == 1) {
                let $divisionlist = null;
                $('#DivisionId').html('');
                $divisionlist += '<option value="-1">Select Area</option>';
                Result.data.forEach((number, index, array) => {
                    if (Result["data"].length == 1) {
                        $divisionlist += '<option value=' + array[index]['DivisionID'] + ' selected>' + array[index]['DivisionName'] + '</option>';
                    } else {
                        if (array[index]['DivisionID'] == DivisionId) {
                            $divisionlist += '<option value=' + array[index]['DivisionID'] + ' selected >' + array[index]['DivisionName'] + '</option>';
                        } else {
                            $divisionlist += '<option value=' + array[index]['DivisionID'] + ' >' + array[index]['DivisionName'] + '</option>';
                        }
                    }
                });
                $('#DivisionId').html($divisionlist);
                return;
            }
        },
        error: function (x, e) {
            if (x.status == 0) {
                alert('You are offline!!\n Please Check Your Network.');
            } else if (x.status == 404) {
                alert('Requested URL not found.');
            } else if (x.status == 500) {
                alert('Internal Server Error.');
            } else if (e == 'parsererror') {
                alert('Error.\nParsing JSON Request failed.');
            } else if (e == 'timeout') {
                alert('Request Time out.');
            } else {
                alert('Unknown Error.\n' + x.responseText);
            }
        }
    });
}

function getUserPemissionforJS(codeType) {
    $.ajax({
        url: "/page-includes/admin/charging/userRolePermissionCharging.php",
        type: "POST",
        dataType: "json",
        async: false,
        data: {
            page: codeType == 0 ? 17 : 18
        },
        success: function (data) {
            if (data) {
                canview = data.canview;
                cancreate = data.cancreate;
                canmodify = data.canmodify;
                candelete = data.candelete;
            }
        }
    });

}