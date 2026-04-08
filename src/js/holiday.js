$(document).ready(function () {
    getPagePermission(10);
    $("#holidaylisting_info").css("display", "none");
    $('#holidaylisting_paginate').css("display", "none");
    $.fn.dataTable.ext.errMode = 'none';
    $('#holidaylisting').DataTable({
        'info':false,
        'lengthChange':false,
        "bPaginate": false,
        "columnDefs": [
            {"orderable": false, "targets":3},
            {"orderable": false, "targets": 4},
            {"orderable": false, "targets": 5}
        ] 
    });
   
    // Get the modalc
    let modal = document.getElementById("myModal");

    // When the user clicks the button, open the modal 
    // Get the button that opens the modal
    $("#addbtn").on('click', function () {
        resetForm();
        modal.style.display = "block";
    });

    $(document).on('click', ".editaction", function () {
        $("#js_actionbutton").val('edit');
        $("#js_holidayid").val($(this).data("publicholidayid"));
        selectHoliday($(this).data("publicholidayid"));
        modal.style.display = "block";
    });    

    $(".closebox,.cancel").on('click', function () {
        modal.style.display = "none";
        ShowPublicHolidays();
    });
    // When the user clicks anywhere outside of the modal, close it
   
    $("#js_saveHoliday").on('click', function () {
        let holidayid = $("#js_holidayid").val();
        saveHoliday(holidayid);
    });
});

$(document).on('click', ".holidaystatus", function (event) {
    let id = $(this).data('id');
    event.stopImmediatePropagation();
    event.preventDefault();
    setActiveHoliday(id);
});

$(document).one('click', ".btn-del", function (event) {
    event.stopImmediatePropagation();
    event.preventDefault();
    $(this).unbind('click');
    let id = $(this).attr('holidayid');
   deleteHoliday(id);
});

/* set active inactive Holidays*/
function setActiveHoliday(id) {
    $.ajax({
        url: "page-includes/admin/holidays/process/actionHolidays.php",
        type: "POST",
        dataType: "json",
        data: {
            id: id,
            action: 'active'
        },
        success: function (data) {
             alert(data.strStatus);
          $.post("page-includes/admin/holidays/view/holidayUI.php", {
                tab:0
            },
          function(data,status){
            $('#content').html(data);
          })
        }
    });
}

/* Delete Holidays*/
function deleteHoliday(id) {
        $.ajax({
            url: "page-includes/admin/holidays/process/actionHolidays.php",
            type: "POST",
            dataType: "json",
            data: {
                id: id,
                action: 'delete'
            },
            success: function (data) {
                 alert(data.strStatus);
                 ShowPublicHolidays();
                 return false ;
            }
        });
}

function getWeekNumber(inputdate) {
    let date = inputdate;
     $.ajax({
        url: 'page-includes/admin/holidays/process/actionHolidays.php',
        type: 'POST',
        data: {date: date, action: 'weeknumber'},
        success: function (data) {
            $("#showweek").text(data);
            $("#weekno").val(data);
            }
        });
    
}

function callDatePicker(dateControlName) {
    $(dateControlName).datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: "dd-mm-yy",
        yearRange: '1900:9999',
        inline: true,
        minDate: new Date('31-12-1900'),
        maxDate: new Date('01-01-9999')
    });
    $(dateControlName).datepicker('option', 'firstDay', 6);
}

function callDatePickerPublicHolidays(dateControlName) {
    $(dateControlName).datepicker({
        showButtonPanel: true,
        dateFormat: "dd-mm-yy",
        minDate: new Date('31-12-1900'),
        maxDate: new Date('01-01-9999')
    });
    $(dateControlName).datepicker('option', 'firstDay', 6);
    $(dateControlName).val($.datepicker.formatDate('dd-mm-yy', new Date()));
}

function reinitializeDatePickerPublicHolidays(dateControlName,calYear) {
    $('#pubholidaydate').hide();
    $(dateControlName).show();
    $(dateControlName).datepicker( "destroy" );
    $(dateControlName).val('01-01-'+calYear);
    $(dateControlName).datepicker({
        dateFormat: "dd-mm-yy",
        setDate: '01-01-'+calYear,
        minDate: '01-01-'+calYear,
        maxDate: '31-12-'+calYear
    });
    $(dateControlName).datepicker('option', 'firstDay', 6);
}

function setCalendarOption(calYear){
    reinitializeDatePickerPublicHolidays("#reinitializeCal",calYear);
}

callDatePicker("#holidaydate");
callDatePicker("#reinitializeCal");
callDatePickerPublicHolidays("#pubholidaydate");
callDatePickerPublicHolidays("#reinitializeCal");

$('input[name=holidaydate], #calenderyear').change(function(){
    let date = $('input[name=holidaydate]').datepicker({ dateFormat: 'yy-mm-dd' }).val();
    getWeekNumber(date);
});

/* save/update  Holidays*/
function saveHoliday(holidayid) {
    $.validator.addMethod("regexValid", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9+&!()|\[\]\- ' ]*$/i.test(value);
    });
    $.validator.addMethod("noSpace", function (value, element) {
        let item = value.trim();
        return item != "";
    }, "No space please and don't leave it empty");
    $('#holidayform').validate({
        debug: false,
        rules: {
            "description": {
                required: true,
                minlength: 3,
                noSpace: true,
                maxlength: 50
            },
            "calenderyear":{
                required: true,
            },
            "holidaydate":{
                required: true,
            }
        },
        messages: {
            "description": {
                required: "<br/> Please enter description.",
                minlength: jQuery.validator.format("<br/> At least {0} characters are necessary."),
                maxlength: '<br/> Description can not be more than {0} characters.',
                noSpace: "<br/> No space please and don't leave it empty."
            },
            "calenderyear": {
                required:"<br/>Please select calendar year"
            },
            "holidaydate":{
                required:"<br/>Please select holiday date"
            }
        },
        submitHandler: function (form) {
            $.ajax({
                type: 'POST',
                url: 'page-includes/admin/holidays/process/actionHolidays.php',
                dataType: "json",
                data: $('#holidayform').serialize(),
                success: function (data) {
                    if (data.strstatus == 'success') {
                        let holidaymodal = document.getElementById("myModal");
                        holidaymodal.style.display = "none";
                        alert(data.strreturnstring);
                        ShowPublicHolidays();
                        return false;
                    }
                }
            });
        }
    })
}

/* populate the modal form  for insert/update and  holiday*/
function selectHoliday(holidayid) {
    $.ajax({
        url: 'page-includes/admin/holidays/process/actionHolidays.php',
        dataType: "json",
        type: 'POST',
        data: {holidayid: holidayid, action: 'openmodal'},
        success: function (data) {
            if (data.status == 'success') {
                if (holidayid > 0) {
                    $('#loading').css('display','none');
                    $('#calenderyear').attr('selected', 'false');
                    $('#calenderyear').val(data.holidaydetails.CalenderYear).attr('selected', 'true');
                    $("#title").html(data.holidaydetails.title);
                    $("#js_saveHoliday").val(data.holidaydetails.button);
                    $("#showweek").text(data.holidaydetails.Week);
                    $("#description").val(data.holidaydetails.Description);
                    $('#calenderyear').prop('disabled',true);
                    $("#holidaydate").val(data.holidaydetails.HolidayDate);
                    $("#holidaydate").prop('disabled',true);
                    $("#reinitializeCal").val(data.holidaydetails.HolidayDate);
                    $("#reinitializeCal").prop('disabled',true);
                } else {
                    resetForm();
                }
            }
        }
    });
}

/* reset the form*/
function resetForm() {
    $('#calenderyear').attr('selected', 'false');
    $("#title").html("Public Holidays");
    $("#js_saveHoliday").val('Save');
    $("#showweek").text('');
    $("#description").val('');
    $("#holidaydate").val('');
    $("#reinitializeCal").val('');
}
