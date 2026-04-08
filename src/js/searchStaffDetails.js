//defien the varaiblr
var actionType = '';
var staffNumber = '';
var networkID = '';
var foreName = '';
var surName = '';
//hide button intially
$("#js_cancel").hide();
$("#js_saveStaff").hide();
$(".js_cancelStaff").hide();
$("#stafferror").hide();
$(".contract_history").addClass('buttonDisabled');
//Enable disabled button of search start
if ($("#js_staffNumber").val() == '' || $("#js_staffNetworkId").val() == '' || $("#js_staffForeName").val() == '' || $("#js_staffSurName").val() == '') {
    toogleSearchButton();
}
//staff number
$("#js_staffNumber").on('keyup', function () {
    autocompleteStaffNumber();
    toogleSearchButton(action = 'Enable');
    if ($("#js_staffNumber").val() == '' && $("#js_staffNetworkId").val() == '' && $("#js_staffForeName").val() == '' && $("#js_staffSurName").val() == '') {
        toogleSearchButton();
    }

});

//forename autocomplete call
$("#js_staffForeName").on('keyup', function () {
    autocompleteStaffForeName();
    toogleSearchButton(action = 'Enable');
    if ($("#js_staffNumber").val() == '' && $("#js_staffNetworkId").val() == '' && $("#js_staffSurName").val() == '' && $("#js_staffForeName").val() == '') {
        toogleSearchButton();
    }
});
//network id autocomplete call
$("#js_staffNetworkId").on('keyup', function () {
    autocompleteStaffNetworkId();
    toogleSearchButton(action = 'Enable');
    if ($("#js_staffNumber").val() == '' && $("#js_staffForeName").val() == '' && $("#js_staffSurName").val() == '' && $("#js_staffNetworkId").val() == '') {
        toogleSearchButton();
    }
});
//surname autocomplete call
$("#js_staffSurName").on('keyup', function () {
    autocompleteStaffSurName();
    toogleSearchButton(action = 'Enable');
    if ($("#js_staffNumber").val() == '' && $("#js_staffForeName").val() == '' && $("#js_staffNetworkId").val() == '' && $("#js_staffSurName").val() == '') {
        toogleSearchButton();
    }
});
var modalEDT = document.getElementById("myModalUpdate");

// Get the button that opens the modal
var btnEDT = document.getElementById("myBtnEditUpdate");

// Get the <span> element that closes the modal
var spanupd = document.getElementsByClassName("closeupd")[0];
//serach the scheduled people
$("#myBtnEditUpdate").on('click', function () {
    // When the user clicks the button, open the modal
    modalEDT.style.display = "block";
});
// When the user clicks on <span> (x), close the modal
spanupd.onclick = function () {
    modalEDT.style.display = "none";
    clearFilterModel();
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function (event) {
    if (event.target == modalEDT) {
        modalEDT.style.display = "none";
       clearFilterModel();
    }
}
$("#js_find").on('click', function (e) {
    e.stopPropagation();
    //define the varaibe
    actionType = 'search';
    staffNumber = $("#js_staffNumber").val();
    networkID = $("#js_staffNetworkId").val();
    foreName = $("#js_staffForeName").val();
    surName = $("#js_staffSurName").val();
    searchClearStaffConfigFilter(staffNumber, networkID, foreName, surName, actionType);
});
//clear the filter
$("#js_cancel").on('click',function () {
    //define the varaibe
    clearFilterModel();
});

//attach user
function staffattachonClickAttach(staffID, scheduledPersonID) {
    actionType = 'select';
    attachStaffDetails(staffID, actionType, 0, scheduledPersonID);
}

$("#js_saveStaff").on('click', function (event) {
    event.stopImmediatePropagation();
    event.preventDefault();
    $(this).unbind('click');
    //define the varaibe
    let staffID = $("#getstaffid").val();
    let oldscheduledPersonID = $("#oldscheduledPersonID").val();
    actionType = 'save';
    attachStaffDetails(staffID, actionType, 1, oldscheduledPersonID);
    $(".staffattach").bind('click');
});

$(".js_cancelStaff").on('click', function (event) {
    event.stopImmediatePropagation();
    event.preventDefault();
    //define the varaibe
    let staffID = '';
    actionType = 'cancel';
    attachStaffDetails(staffID, actionType);
    $(".staffattach").bind('click');
});

/*Function to call the searc/clear filter*/
function searchClearStaffConfigFilter(staffNumber, networkID, foreName, surName, actionType) {

    $.ajax({
        url: "page-includes/staff-details/process/searchStaffDeatailsConfig.php",
        type: "POST",
        dataType: "json",
        data: {
            'selectedForeName': foreName,
            'selectedNetLogin': networkID,
            'selectedSurName': surName,
            'selectedStaffNumber': staffNumber,
            'actionType': actionType
        },
        success: function (data) {

            if (data.status == 'success') {
                $("#staffDetailsConfiglist tbody").html(data.view);

                if (actionType == 'search') {
                    $("#js_cancel").show();
                } else {
                    toogleSearchButton();
                    $("#js_cancel").hide();
                    $("#js_staffNumber").val('');
                    $("#js_staffNetworkId").val('');
                    $("#js_staffForeName").val('');
                    $("#js_staffSurName").val('');
                }


            } else {
                alert("no data");
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

//Enable disabled button of search 
function toogleSearchButton(action = '') {

    if (action == 'Enable') {
        $("#js_find").removeClass('buttonDisabled').addClass('buttonEnabled');

    } else {
        $("#js_find").removeClass('buttonEnabled').addClass('buttonDisabled');
    }
}

function autocompleteStaffNumber() {

    $("#js_staffNumber").autocomplete({
        source: "page-includes/staff-details/process/getStaffDetailsAutocompleteList.php?termKey=StaffNumber",
        minLength: 2,
        select: function (event, ui) {
            $('#js_staffNumber').val(ui.item.value);
        }
    })
        .on('mouseup', function () {
            $(this).select();
        });
}

function autocompleteStaffForeName() {

    $("#js_staffForeName").autocomplete({

        source: "page-includes/staff-details/process/getStaffDetailsAutocompleteList.php?termKey=Forename",
        minLength: 2,
        select: function (event, ui) {

            $('#js_staffForeName').val(ui.item.value);
        }
    })
        .on('mouseup', function () {
            $(this).select();
        });
}

function autocompleteStaffNetworkId() {

    $("#js_staffNetworkId").autocomplete({

        source: "page-includes/staff-details/process/getStaffDetailsAutocompleteList.php?termKey=NetLogin",
        minLength: 2,
        select: function (event, ui) {
            $('#js_staffNetworkId').val(ui.item.value);
        }
    })
        .on('mouseup', function () {
            $(this).select();
        });
}

function autocompleteStaffSurName() {

    $("#js_staffSurName").autocomplete({

        source: "page-includes/staff-details/process/getStaffDetailsAutocompleteList.php?termKey=Surname",
        minLength: 2,
        select: function (event, ui) {
            $('#js_staffSurName').val(ui.item.value);
        }
    })
        .on('mouseup', function () {
            $(this).select();
        });
}


/*Function to call the attach/clear staff details */
function attachStaffDetails(varstaffID, actionType, showpop = 0, scheduledPersonID = 0) {
    var replace_name = false;
    $("#stafferror").hide();
    let old_schedPersonID = scheduledPersonID;
    let new_schedPersonID = '';
    if (actionType == 'save') {
        new_schedPersonID = $('#newpersonid').val();
    }
    
    if(showpop) {
        $("#dialog-confirm-scheduled-person").dialog({
            resizable: false,
            modal: true,
            title: "Please Confirm!",
            height: 250,
            width: 400,
            create: function (e, ui) {
                var pane = $(this).dialog("widget").find(".ui-dialog-buttonpane")
                $("<label class='shut-up'><input name='replace_name' id='replace_name' type='checkbox'/>Replace Display Name with actual Staff Name</label>").prependTo(pane)
            },
            buttons: {
                "Yes": function () {
                    replace_name = $('input[type=checkbox][id=replace_name]').prop("checked");
                    $(this).dialog('close');
                    
                    $.ajax({
                        url: "page-includes/staff-details/process/attachStaffDeatailsConfig.php",
                        type: "POST",
                        dataType: "json",
                        data: {
                            'selectedstaffID': varstaffID,
                            'actionType': actionType,
                            'schedPersonID': new_schedPersonID,
                            'oldschedPersonID' : old_schedPersonID
                        },
                        success: function (data) {
                
                            if (data.status == 'success') {
                                switch (actionType) {
                                    case 'select':
                                        document.getElementById("myModalUpdate").style.display = "none";
                                        $("#attachStaffSession tbody").html(data.view);
                                        $("#js_saveStaff").show();
                                        $(".js_cancelStaff").show();
                                        $("#myBtnEditUpdate").hide();
                                        $("#getstaffid").val(varstaffID);
                                        $("#oldscheduledPersonID").val(old_schedPersonID);
                                        if(replace_name == true) {
                                            var last_name = $('#js_surname').text();
                                            if ($('#js_prefname').text() == null || $('#js_prefname').text() == '') {
                                                var first_name = $('#js_forename').text();
                                            }
                                            else {
                                                var first_name = $('#js_prefname').text();
                                            }
                                            $("#dispFirstName").val(first_name);
                                            $("#dispLastName").val(last_name);
                                        }
                                        break;
                
                                    case 'save':
                                        $("#js_saveStaff").hide();
                                        $(".js_cancelStaff").hide();
                                        $("#myBtnEditUpdate").hide();
                                        //enable contratc history tab and add id of schperson id
                                        $(".contract_history").removeClass('buttonDisabled').addClass('buttonEnabled');
                                        if(replace_name == true) {
                                            var last_name = $('#js_surname').text();
                                            if ($('#js_prefname').text() == null || $('#js_prefname').text() == '') {
                                                var first_name = $('#js_forename').text();
                                            }
                                            else {
                                                var first_name = $('#js_prefname').text();
                                            }
                                            $("#dispFirstName").val(first_name);
                                            $("#dispLastName").val(last_name);
                                        }
                                        break;
                
                                    case 'cancel':
                                        $("#attachStaffSession tbody").html(data.view);
                                        $("#js_saveStaff").hide();
                                        $(".js_cancelStaff").hide();
                                        $("#myBtnEditUpdate").show();
                                        $("#stafferror").hide();
                                        break;
                
                                }
                                clearFilterModel();
                                $("#stafferror").removeClass('error');
                
                            } else {
                                $("#attachStaffSession tbody").html(data.view);
                                $("#js_saveStaff").hide();
                                $(".js_cancelStaff").show();
                                $("#stafferror").addClass('error');
                            }
                            if (data.message != '') {
                                $("#stafferror").text(data.message);
                                $("#stafferror").show();
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
                },
                "No": function () {
                    $(this).dialog('close');
                }
            }
        });
    } else {
        $.ajax({
            url: "page-includes/staff-details/process/attachStaffDeatailsConfig.php",
            type: "POST",
            dataType: "json",
            data: {
                'selectedstaffID': varstaffID,
                'actionType': actionType,
                'schedPersonID': new_schedPersonID,
                'oldschedPersonID' : old_schedPersonID
            },
            success: function (data) {
    
                if (data.status == 'success') {
                    switch (actionType) {
                        case 'select':
                            document.getElementById("myModalUpdate").style.display = "none";
                            $("#attachStaffSession tbody").html(data.view);
                            $("#js_saveStaff").show();
                            $(".js_cancelStaff").show();
                            $("#myBtnEditUpdate").hide();
                            $("#getstaffid").val(varstaffID);
                            $("#oldscheduledPersonID").val(old_schedPersonID);
                            break;
    
                        case 'save':
                            $("#js_saveStaff").hide();
                            $(".js_cancelStaff").hide();
                            $("#myBtnEditUpdate").hide();
                            //enable contratc history tab and add id of schperson id
                            $(".contract_history").removeClass('buttonDisabled').addClass('buttonEnabled');
                            break;
    
                        case 'cancel':
                            $("#attachStaffSession tbody").html(data.view);
                            $("#js_saveStaff").hide();
                            $(".js_cancelStaff").hide();
                            $("#myBtnEditUpdate").show();
                            $("#stafferror").hide();
                            break;
    
                    }
                    clearFilterModel();
                    $("#stafferror").removeClass('error');
    
                } else {
                    $("#attachStaffSession tbody").html(data.view);
                    $("#js_saveStaff").hide();
                    $(".js_cancelStaff").show();
                    $("#stafferror").addClass('error');
                }
                if (data.message != '') {
                    $("#stafferror").text(data.message);
                    $("#stafferror").show();
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
}

function changeDisplayName(new_name, schedPersonID) {

    $.ajax({
        url: "page-includes/staff-details/process/attachStaffDeatailsConfig.php",
        type: "POST",
        dataType: "json",
        data: {
            'changeName': 1,
            'newName': new_name,
            'schedPersonID': schedPersonID
        },
        success: function (data) {
            $('#dispName').val(new_name);
        }
    });
}

function clearFilterModel(){
    actionType = 'claerfilter';
    staffNumber = '';
    networkID = '';
    foreName = '';
    surName = '';
    searchClearStaffConfigFilter(staffNumber, networkID, foreName, surName, actionType);
}

//call RefreshScheduledPersonScreen page
function RefreshScheduledPersonScreen(userAction, schedulepersonid,selectedteamid) {
    let task = "schedulepersonhtmlcall";
    $.ajax({
        url: "page-includes/staff-details/process/createSchedulePerson.php",
        type: "POST",
        data: {
            task: task,
            useraction: userAction,
            schedulepersonid: schedulepersonid,
			selectedteamid:selectedteamid
        },
        success: function (data) {
            $('#content').html(data);
        }
    });
}

