$(document).ready(function () {

    getPagePermission(16);
    $("#divsisonerror").hide();
    $("#divisionlisting_info").css("display", "none");
    $('#divisionlisting_paginate').css("display", "none");

    let table = $('#divisionlisting').DataTable({
        bPaginate:false,
        info:false,
        lengthChange:false,
        stateSave: true,
        deferRender: true,
        dom: 'Bfrtip',
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
            {"orderable": false, "targets": 3},
            {"orderable": false, "targets": 4}
        ]
    });
    yadcf.init(table, [
        {
            column_number: 0,
            filter_type: 'text'
        },
        {
            column_number: 1,
            filter_type: 'text'
        }
    ]);

    // Get the modalc
    let modal = document.getElementById("myModal");

    // When the user clicks the button, open the modal 
    // Get the button that opens the modal
    $("#adddivision").on('click', function () {
        $('#js_actionbutton').val('create');
        resetForm();
        selectMasterColor(0);
        modal.style.display = "block";

    });

    $(document).on('click', "#editcolour", function () {
        selectColour($(this).data("editcolourid"));
        modal.style.display = "block";
    });

    // When the user clicks on <span> (x), close the modal

    $(".closebox").on('click', function () {
        resetColourForm();
        modal.style.display = "none";
        //resetForm();
    });
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {

        if (event.target == modal) {
            resetColourForm();
            modal.style.display = "block";
        }
    }
    $("#js_saveColour").on('click', function () {
        let colouridval = $("#js_colourid").val();
        selectMasterColor(colouridval);
    });
    

    $("#DivisionID").on('keyup', function () {
        $("#divsisonerror").hide();
    });
});
//document ready close

/* get permission to check the view  permission on landing page*/


$(document).on('click', ".colorstatus", function (event) {
    event.stopImmediatePropagation();
    event.preventDefault();
	let defaultDivisionColour = $(this).data('defaultcolour');
	var colourId = defaultDivisionColour.split("-")[0];
	var divisionId = defaultDivisionColour.split("-")[1];
	var attrVal = 0;
	var isDefault = 0;
	if ($(this).prop("checked") == true) {
		isDefault = 1;
	}

	$(".division-"+divisionId).each(function(idx, elem) {
		$(this).prop("checked",false);
	});

	if(isDefault) {
		$(".color-"+colourId).prop("checked",true);
	}
    if ($(this).prop("checked") == false) {
        attrVal = 0;		
		customConfirm('Are you sure to remove this colour from default colour',function(){
				
				setDefaultColour(isDefault, colourId, divisionId);
			},
			function() {
				$(this).prop("checked",true);
				
			}
		);
    }else{
        attrVal = 1;
		customConfirm('Are you sure to make this colour as default colour',function(){
				
				setDefaultColour(isDefault, colourId, divisionId);
			},
			function() {
				$(this).prop("checked",false);
			}
		);
    }
});

function setColourStatus(colorId,colourStatus){
    var setStatus = 0;
    if(colourStatus == 0){
        setStatus = 1;
    }
    $.ajax({
        url: "page-includes/admin/area-admin/process/createMasterColors.php",
        type: "POST",
        dataType: "json",
        data: {
            colourid: colorId,
            status: setStatus,
            action: 'updatestatus'
        },
        success: function (data) {
            if (data.strstatus == 'success') {
                
                var repDiv = '';
                if(setStatus == 0){
                    repDiv +='<a title="Inactive" href="javascript:void(0);">';
                        repDiv +='<img src="../../../../images/red_cross.png" class="tick" style="margin: -12px 0 0 20px;" onclick="setColourStatus('+colorId+',0);">';
                    repDiv +='</a>';
                } else {
                    repDiv +='<a title="Inactive" href="javascript:void(0);">';
                        repDiv +='<img src="../../../../images/green_tick.png" class="tick" style="margin: -12px 0 0 20px;" onclick="setColourStatus('+colorId+',1);">';
                    repDiv +='</a>';
                }
                $('#statusSpan-'+colorId).html(repDiv);
                
                return;
            } else {
                alert(data.strreturnstring);
                return;
            }
        }
    });
}

/* set default colour for the division*/
function setDefaultColour(isDefault, colourId, divisionId) {
    $.ajax({
        url: "page-includes/admin/area-admin/process/createMasterColors.php",
        type: "POST",
        dataType: "json",
        data: {
            colourid: colourId,
            divisionid: divisionId,
            action: 'isdefault',
            actionValue: isDefault
        },
        success: function (data) {
            if (data.strstatus == 'success') {
                customAlert(data.strreturnstring, 1000);
                $.post("page-includes/admin/area-admin/view/colormasterUI.php", {
                    tab:0
                  },  
                  function(data,status){
                    $('#content').html(data); 
                  });
                
            } else {
                customAlert(data.strreturnstring, 1000);
                if (actionValue == 1) {
                    $(".isactive_" + colourId).prop("checked", false);
                } else {
                    $(".isactive_" + colourId).prop("checked", true);
                }
               
            }
        }
    });
}

/* save/update  divisions*/
function selectMasterColor(coloridval) {
     $.validator.addMethod("regexValid", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9+&!()|\[\]\- ' ]*$/i.test(value);
    });
    $.validator.addMethod("noSpace", function (value, element) {
        let item = value.trim();
        return item != "";
    }, "No space please and don't leave it empty");
    $('#neweditmastercolour').validate({
        debug: false,
        rules: {
            "DivisionID": {
                required: {
                    depends: function(element){
                        if('' == $('#DivisionID').val()){
                            //Set predefined value to blank.
                            $('#DivisionID').val('');
                        }
                        return true;
                    }
                }
            },
            "ColourName": {
                required: true
            },
            "ColourNotes": {
                required: true,
                minlength: 3,
                maxlength: 500
            }

        },
        messages: {
            "DivisionID":{
                required: "<br/> Please select Area"
            },
            "ColourName": {
                required: "<br/> Please enter a Colour Name."
            },
            "ColourNotes": {
                required: "<br/> Please enter a Colour Notes.",
                minlength: jQuery.validator.format("<br/> At least {0} characters are necessary."),
                maxlength: '<br/> Notes can not be more than {0} characters.'
            }
        },
        submitHandler: function (form) {
            $.ajax({
                type: 'POST',
                url: 'page-includes/admin/area-admin/process/createMasterColors.php',
                dataType: "json",
                data: $('#neweditmastercolour').serialize(),
                success: function (data) {
                    if (data.strstatus == 'success') {
                        let colournmodal = document.getElementById("myModal");
                        colournmodal.style.display = "none";
                        alert(data.strreturnstring);
                        $.post("page-includes/admin/area-admin/view/colormasterUI.php", {
                            tab:0
                          },  
                          function(data,status){
                            $('#content').html(data); 
                          });
                        if ($("#js_actionbutton").val() == 'create') {
                            refreshColourList();
                        } else {
                            var selectedText = $("#DivisionID option:selected").html();
                            $("#divisionname_" + form.elements["js_colourid"].value).html(selectedText);
                            $("#colorname_" + form.elements["js_colourid"].value).html(form.elements["ColourName"].value);
                            $("#notes_" + form.elements["js_colourid"].value).html(form.elements["ColourNotes"].value);
                            $('#bgcolor_' + form.elements["js_colourid"].value).val(form.elements["jobbackcolor"].value);
                            $('#fontcolor_' + form.elements["js_colourid"].value).val(form.elements["jobfontcolor"].value);
                        }
                    } else {
                        if (data.strstatus == 'uniquename') {
                            $("#divsisonerror").show();
                            $("#ColourName").addClass('error');
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
function selectColour(colourid) {
    $.ajax({

        url: 'page-includes/admin/area-admin/process/createMasterColors.php',
        dataType: "json",
        type: 'POST',
        data: {colourId: colourid, action: 'openmodal'},
        success: function (data) {
            if (data.status == 'success') {

                $("#title").html(data.colourdetail.title);
                $("#js_saveColour").val(data.colourdetail.button);
                $("#js_colourid").val(colourid);
                $("#js_actionbutton").val(data.colourdetail.actionbutton)
                if (colourid > 0) {
                    $("#DivisionID").val(data.colourdetail.DivisionID);
                    $("#ColourName").val(data.colourdetail.ColourName);
                    $("#ColourNotes").val(data.colourdetail.ColourNotes);
                    $("#js_isDefault").val(data.colourdetail.IsDefaultColour);
                    $("#js_isActive").val(data.colourdetail.IsActive);
                    colorPickerDropdown('#jobbackcolor', '#'+data.colourdetail.ColourBackground);
                    colorPickerDropdown('#jobfontcolor', '#'+data.colourdetail.ColourFont); 
                    $('#jobbackcolor').val('#'+data.colourdetail.ColourBackground);
                    $('#jobfontcolor').val('#'+data.colourdetail.ColourFont);
                } else {
                    resetForm();
                }
            }
        }
    });
}
/* refresh the division list*/
function refreshColourList() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/admin/area-admin/view/colormasterUI.php',
        dataType: "json",
        data: {action: 'listing'},
        success: function (data) {
            if (data.status == 'success') {
                $("#divisionlisting tbody").html(data.view);
            }

        }
    });
}

/* reset the form*/
function resetForm() {
    $("#DivisionID").val('');
    $("#ColourName").val('');
     $("#ColourNotes").val('');
     colorPickerDropdown('#jobbackcolor', '#ffffff');
    colorPickerDropdown('#jobfontcolor', '#000000');   
    $('#jobbackcolor').val('#ffffff');
    $('#jobfontcolor').val('#000000'); 
}

/* reset the form validation*/
function resetColourForm() {
    $("#divsisonerror").hide();
    $("#colournameerror").hide();
    $("#colournoteserror").hide();
    let $colourform = $('#neweditmastercolour');
    $colourform.validate().resetForm();
    $colourform.find('.error').removeClass('error');
}
