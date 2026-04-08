$(document).ready(function () {
    getPagePermission(7);
    $("#divsisonerror").hide();
    $("#divisionlisting_info").css("display", "none");
    $('#divisionlisting_paginate').css("display", "none");

    createDataTable();

    // Get the modalc
    let modal = document.getElementById("myModal");

    // When the user clicks the button, open the modal 
    // Get the button that opens the modal
    $("#adddivision").on('click', function () {
        selectDivison(0);
        modal.style.display = "block";

    });

    $(document).on('click', "#editdivision", function () {
        selectDivison($(this).data("editdivisionid"));
        modal.style.display = "block";
    });

    //get history model
    $(document).on('click', "#historydivision", function () {
        showHistory($(this).data("historydivisionid"));
    });

    $(document).on('click', "#historydivisionalAdmin", function () {
        showHistory_other($(this).data("historydivisionid"));
    });
    // When the user clicks on <span> (x), close the modal

    $(".closebox").on('click', function () {
        resetDivisonForm();
        modal.style.display = "none";
        //resetForm();
    });
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {

        if (event.target == modal) {
            resetDivisonForm();
            modal.style.display = "block";
        }
    }
    $("#js_saveDivision").on('click', function () {
        let divisionidval = $("#js_divisionid").val();
        saveDivision(divisionidval);
    });
    

    $("#divisionname").on('keyup', function () {
        $("#divsisonerror").hide();
    });

});

function createDataTable() {
    let table = $('#divisionlisting').DataTable({
        'bPaginate':false,
        'info':false,
        'lengthChange':false,
        "fnDrawCallback": function (oSettings) {
            if ($('#divisionlisting tr').length >= 11) {
                $("#divisionlisting_paginate").css('visibility', 'visible');
                $("#divisionlisting_info").css('font-size', '12px');
                $("#divisionlisting_paginate ").css('font-size', '12px');
            }
        },
		dom: 'Bfrtip',
		buttons: [
            {
                extend:    'print',
                text:      '<img id="print" class="handcursor" name="print" border="0" src="images/print.png" style="top:10px; vertical-align:middle;" alt="Print" width="18px" height="17px">',
                titleAttr: 'Print'
            }
        ],
        "columnDefs": [
            {"orderable": false, "targets": 1},
            {"orderable": false, "targets": 4}
        ]
    });
    yadcf.init(table, [
        {
            column_number: 0,
            filter_type: 'text'
        }
    ]);
}
//document ready close

/* get permission to check the view  permission on landing page*/


$(document).on('click', "#checkbox-btn", function () {
    let actionValue = 0;
    let activedivisionid = $(this).data('activedivisionid');
    if ($(this).prop("checked") == true) {
        actionValue = 1;
    }
    setActiveDivision(actionValue, activedivisionid);
});

/* set active inactive divisions*/
function setActiveDivision(actionValue, activedivisionid) {
    $.ajax({
        url: "page-includes/admin/divisions/process/createDivisions.php",
        type: "POST",
        dataType: "json",
        data: {
            divisionid: activedivisionid,
            action: 'isactive',
            actionValue: actionValue
        },
        success: function (data) {
            if (data.strstatus == 'success') {
                alert(data.strreturnstring)
                //update check the box
                if (actionValue == 0) {
                    $(".isactive_" + activedivisionid).prop("checked", false);
                } else {
                    $(".isactive_" + activedivisionid).prop("checked", true);
                }
                return;
            } else {
                alert(data.strreturnstring);
                if (actionValue == 1) {
                    $(".isactive_" + activedivisionid).prop("checked", false);
                } else {
                    $(".isactive_" + activedivisionid).prop("checked", true);
                }
                return;
            }
        }
    });
}

/* save/update  divisions*/
function saveDivision(divisionidval) {
    
    $.validator.addMethod("regexValid", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9+&!()|\[\]\- ' ]*$/i.test(value);
    });
    $.validator.addMethod("noSpace", function (value, element) {
        let item = value.trim();
        return item != "";
    }, "No space please and don't leave it empty");
    $('#neweditdivison').validate({
        debug: false,
        rules: {
            "divisionname": {
                required: true,
                minlength: 3,
                noSpace: true,
                maxlength: 50
            },
            "notes": {
                minlength: 3,
                maxlength: 500
            }

        },
        messages: {
            "divisionname": {
                required: "<br/> Please enter a Area Name.",
                minlength: jQuery.validator.format("<br/> At least {0} characters are necessary."),
                maxlength: '<br/> Area name can not be more than {0} characters.',
                noSpace: "<br/> No space please and don't leave it empty."
            },
            "notes": {
                minlength: jQuery.validator.format("<br/> At least {0} characters are necessary."),
                maxlength: '<br/> Notes can not be more than {0} characters.'
            }
        },
        submitHandler: function (form) {
            $.ajax({
                type: 'POST',
                url: 'page-includes/admin/divisions/process/createDivisions.php',
                dataType: "json",
                data: $('#neweditdivison').serialize(),
                success: function (data) {
                    if (data.strstatus == 'success') {
                        let divisionmodal = document.getElementById("myModal");
                        divisionmodal.style.display = "none";
                        alert(data.strreturnstring);
                        if ($("#js_actionbutton").val() == 'create') {
                            refreshDivisionList();
                        } else {
                            $("#divisionname_" + form.elements["js_divisionid"].value).html(form.elements["divisionname"].value);
                            $("#notes_" + form.elements["js_divisionid"].value).html(form.elements["notes"].value);
                            
                            if (data.isactiveval == 1) {
                                $(".isactive_"+form.elements["js_divisionid"].value).prop("checked", true);
                            } else {
                                $(".isactive_"+form.elements["js_divisionid"].value).prop("checked", false);
                            }
                        }
                    } else {
                        if (data.strstatus == 'uniquename') {
                            $("#divsisonerror").show();
                            $("#divisionname").addClass('error');
                            $("#divsisonerror").addClass('error');
                            $("#divsisonerror").text(data.strreturnstring);
                        } else {
                            alert(data.strreturnstring);
                        }
                    }
                }
            });
        }
    })
}

/* populate the modal form  for insert/update and  divisions*/
function selectDivison(divisionid) {
    $.ajax({

        url: 'page-includes/admin/divisions/process/createDivisions.php',
        dataType: "json",
        type: 'POST',
        data: {divisionId: divisionid, action: 'openmodal'},
        success: function (data) {
            if (data.status == 'success') {

                $("#title").html(data.divisiondetail.title);
                $("#js_saveDivision").val(data.divisiondetail.button);
                $("#js_divisionid").val(divisionid);
                $("#js_actionbutton").val(data.divisiondetail.actionbutton)
                if (divisionid > 0) {
                    $("#divisionname").val(data.divisiondetail.DivisionName);
                    $("#divisionname").data('divisionnameid', divisionid);
                    $("#notes").val(data.divisiondetail.Notes);
                    
                    if (data.divisiondetail.isActive == 1) {
                        $("#isactive").prop("checked", true);
                        $("#isactive").val(data.divisiondetail.isActive);
                    } else {
                        $("#isactive").prop("checked", false);
                        $("#isactive").val(data.divisiondetail.isActive);
                    }
                    $("#effectivedate").text(data.divisiondetail.EffectedFrom);
                } else {
                    resetForm();
                }
            }
        }
    });
}
/* refresh the division list*/
function refreshDivisionList() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/divisions/process/listingDivision.php',
        dataType: "json",
        data: {action: 'listing'},
        success: function (data) {
            if (data.status == 'success') {
                $('#divisionlisting').dataTable().fnClearTable();
                $('#divisionlisting').dataTable().fnDestroy();
                $("#divisionlisting tbody").html(data.view);
                createDataTable();
            }

        }
    });
}

/* reset the form*/
function resetForm() {
    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    let yyyy = today.getFullYear();
    today = dd + '-' + mm + '-' + yyyy;
    $("#divisionname").val('');
    $("#notes").val('');
    $("#isactive").prop("checked", true);
    $("#effectivedate").text(today);
}

/* get the history*/

function showHistory(divisionid) {
    $.post("function-includes/common/common.php", {
            divisionid: divisionid,
            action: 'history',
            modulename: 'Divisions'
        },
        function (data, status) {
            $.facebox(data);
        })
}

/* get the history of Divisional Admin*/
function showHistory_other(divisionid) {
    $.post("function-includes/common/common.php", {
            divisionid: divisionid,
            action: 'history',
            modulename: 'DivisionalAdmin'
        },
        function (data, status) {
            $.facebox(data);
        })
}

/* reset the form validation*/
function resetDivisonForm() {
    $("#divsisonerror").hide();
    let $divisionsform = $('#neweditdivison');
    $divisionsform.validate().resetForm();
    $divisionsform.find('.error').removeClass('error');
}
