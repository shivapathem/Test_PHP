

$(document).ready(function(){
    /*function strDes(a, b) {
        if (a.value>b.value) return 1;
        else if (a.value<b.value) return -1;
        else return 0;
      }
     */

     $(".js_newbutton").hide();
     $("#js_confirmfilter").hide();
     $("#js_updatefilter").hide();
   
    
      //Insert The Avaialble Duties
      function InsDelAvailableDuties(masterDutyIds,isMiscDuty,selectedOpts,actionType){
       
        var  dataFilterID = $(".highlightOrange").data('filterid');
            $.ajax({ 
                url: "page-includes/master-duties-filter/add-duties-available.php",
                type: "POST",
                dataType: "json", 
                data: {
                     'dataFilterID':dataFilterID,
                     'masterDutyIds':JSON.stringify(masterDutyIds),
                     'isMiscDuty': isMiscDuty,
                     'actionType':actionType,
                     'teamId': $("#schedulingTeamId").val()
                },
          success: function(data) {
            if (data.status == 'success') {
                 //update the list
                $('#multiSelect3').append($(selectedOpts).clone());
                $(selectedOpts).remove();
                populateTheMasterDuties(JSON.stringify(masterDutyIds));
                return;
            }
             else {
                 customAlert("no data");
             }
         }
      });
      }


     $(document).on('click','#btnRight',function (e) {
            var selectedOpts = $('#multiSelect1 option:selected');
            var masterDutyIds = [];
            var actionType ='insert';
            $("#multiSelect1 option:selected").each(function(i) {
                masterDutyIds[i] = this.value;
            });
            
            let  isMiscDuty = 0;
           // var selectedOpts = $('#lstBox1').val();
            if (selectedOpts.length == 0) {
                customAlert("Please Select atleast one Master Duty to move.");
                e.stopImmediatePropagation();
				e.preventDefault();
            }
            
            //call the insert function

            InsDelAvailableDuties(masterDutyIds,isMiscDuty,selectedOpts,actionType);
          
            /* -- Uncomment for optional sorting -- 
            var box2Options = $('#multiSelect3 option');
            var box2OptionsSorted;
            box2OptionsSorted = box2Options.toArray().sort(strDes);
            $('#multiSelect3').empty();
            box2OptionsSorted.forEach(function(opt){
              $('#multiSelect3').append(opt);
            })*/
            
			e.stopImmediatePropagation();
            e.preventDefault();
        });
        
        $(document).on('click','#btnAllRight',function (e) {
            var selectedOpts = $('#multiSelect1 option');
            var masterDutyIds = [];
            var actionType = 'insert';
            var isMiscDuty = 0;
            if (selectedOpts.length == 0) {
                customAlert("Please Select atleast one item to move.");
				e.stopImmediatePropagation();
                e.preventDefault();
            }
            $("#multiSelect1 > option").each(function(i) {
                masterDutyIds[i] = this.value;
                
            });
            InsDelAvailableDuties(masterDutyIds,isMiscDuty,selectedOpts,actionType);
			e.stopImmediatePropagation();
            e.preventDefault();
        });

        //delete duties 
        $(document).on('click','#btnLeft',function (e) {
            if( $('#multiSelect3').has('option').length <= 0 )
            {
                customAlert("There is no item to remove.");
				e.stopImmediatePropagation();
                e.preventDefault();
            }
            else{
                var selectedOpts = $('#multiSelect3 option:selected');
                if (selectedOpts.length == 0) {
                    customAlert("Please Select atleast one item to move.");
					e.stopImmediatePropagation();
                    e.preventDefault();
                }
            }

            var masterDutyIds = [];
            var actionType ='delete';
            var isMiscDuty = '';
            $("#multiSelect3 option:selected").each(function(i) {
                masterDutyIds[i] = this.value;
            });
            InsDelAvailableDuties(masterDutyIds,isMiscDuty,selectedOpts,actionType);
			e.stopImmediatePropagation();
            e.preventDefault();
        });
    
        $(document).on('click','#btnAllLeft',function (e) {
            if ($('#multiSelect3').has('option').length <= 0 )
            {
                customAlert("There is no item to remove.");
				e.stopImmediatePropagation();
                e.preventDefault();
            }
            else{
                var selectedOpts = $('#multiSelect3 option');
                if (selectedOpts.length == 0) {
                    customAlert("Please Select atleast one item to move.");
					e.stopImmediatePropagation();
                    e.preventDefault();
                }
            }
            var masterDutyIds = [];
            var actionType ='delete';
            var isMiscDuty = '';

            $("#multiSelect3 > option").each(function(i) {
                masterDutyIds[i] = this.value;
                
            });
            InsDelAvailableDuties(masterDutyIds,isMiscDuty,selectedOpts,actionType);
			e.stopImmediatePropagation();
            e.preventDefault();
        });

        $(document).on('click','#miscdutiesMoveall',function (e) {
            var selectedOpts = $('#multiSelect2 option');
            if (selectedOpts.length == 0) {
                customAlert("Please Select atleast one item to move.");
				e.stopImmediatePropagation();
                e.preventDefault();
            }
    
            var masterDutyIds = [];
            var actionType ='insert';
            var isMiscDuty = 1;

            $("#multiSelect2 > option").each(function(i) {
                masterDutyIds[i] = this.value;
                
            });
            InsDelAvailableDuties(masterDutyIds,isMiscDuty,selectedOpts,actionType);
			e.stopImmediatePropagation();
            e.preventDefault();
        });

        $(document).on('click','#miscduties',function (e) {
            var selectedOpts = $('#multiSelect2 option:selected');
            if (selectedOpts.length == 0) {
                customAlert("Please Select atleast one Miscellaneous Duty  to move.");
				e.stopImmediatePropagation();
                e.preventDefault();
            }
    
            var masterDutyIds = [];
            var actionType ='insert';
            var isMiscDuty = 1;

            $("#multiSelect2 option:selected").each(function(i) {
                masterDutyIds[i] = this.value;
            });
            
            InsDelAvailableDuties(masterDutyIds,isMiscDuty,selectedOpts,actionType);
			e.stopImmediatePropagation();
            e.preventDefault();
        });

        
       

//Toggele functionality to handle the actions buttion
        function buttonToogle(action,actionType){
           
            if(action == 0){
                $("#filtername").prop("disabled", false);
                $("#comments").prop("disabled", false);
                $("#schedulingTeam").prop("disabled", true);
                //update the button
                $(".newbutton").hide();
                $(".js_newbutton").show();
                switch(actionType){
                    case 'save':
                        $("#js_savefilter").show();
                        $("#js_updatefilter").hide();
                        $("#js_confirmfilter").hide();
                        break;

                    case 'update':
                        $("#js_savefilter").hide();
                        $("#js_updatefilter").show();
                        $("#js_confirmfilter").hide();
                        break;
                    
                    case 'delete':
                        $("#js_savefilter").hide();
                        $("#js_updatefilter").hide();
                        $("#js_confirmfilter").show();
                        break;

                }
                
                //$(".js_savefilter").attr('data-actionType', dataProp);
                
            }else{
                
                $("#filtername").prop("disabled", true);
                $("#comments").prop("disabled", true);
                $("#schedulingTeam").prop("disabled", true);
                //update the button
                $(".newbutton").show();
                $(".js_newbutton").hide();
                $(".js_savefilter").html("Save");
                $("#js_savefilter").show();
                $("#js_confirmfilter").hide();
                $("#js_updatefilter").hide();
                //$(".js_savefilter").attr('data-actionType',dataProp);
            }
        }

        //delete filter 
        $(document).on('click','#js_confirmfilter',function(){
             DeleteFilter();

        });
//New Filter Create action
        $("#js_newfilter").click(function() {
            $('tr').removeClass('highlightOrange');
            $('#filtername').val('');
            $('#comments').val('');
            $("#schedulingTeam").prop("disabled", true);
            let action = 0;
            let actionType = 'save';
            populateTheMasterDuties();  
            buttonToogle(action,actionType);
        });


        //Modify Filter Create action
        $("#js_modifyfilter").click(function() {
            
            populateTheHighLightFormValue();
            let action = 0;
            let actionType = 'update';
            buttonToogle(action,actionType);
        });

        //Delete Filter Create action
        $("#js_deletefilter").click(function() {
           var  dataFilterID = $(".highlightOrange").data('filterid');
            let action = 0;
            let isDelete = 1;
            let actionType = 'delete';
            buttonToogle(action,actionType);
        });

        //Cancel action  

        $("#js_cancelfilter").click(function() {
            $('#js_masterFilterList tr.highlightOrange').removeClass('highlightOrange');
            $('#js_masterFilterList tbody tr').eq(1).addClass('highlightOrange');
            populateTheHighLightFormValue();
            let action = 1;
            let actionType = 'cancel';
            buttonToogle(action,actionType);

        });




        //Save filter
        $("#js_savefilter").on('click',function() {
                //call the inert update masterduty fiter
                var dataFilterID = 0;
                var actionTypeValue = 'save';
                instUpdMasterDutyFilter(dataFilterID,actionTypeValue);
        });

        //Update fikter

        $("#js_updatefilter").on('click',function() {

            var dataFilterID = $(".highlightOrange").data('filterid');
            var actionTypeValue  = 'update';
            instUpdMasterDutyFilter(dataFilterID,actionTypeValue);
        });

        //Save/Update Filter
        function instUpdMasterDutyFilter(dataFilterID,actionTypeValue) {
            
            /** Validation start */
            var filterName = $("#filtername").val();
            var comments = $("#comments").val();
            var schedulingTeam = $("#schedulingTeamId").val();

            if(filterName== ''){
                    customAlert("Please enter the filter name");
                    return;
            }

            if(schedulingTeam < 1){
                customAlert("Please select a team.");
                return;
            }

            if(comments== ''){
                customAlert("Please enter the comments ");
                return;
            }

             // Validation for Filter name name should have a value not spaces
                 var ControlId = "#filtername";
                var MinLength = 3;
                var MaxLength = 50;
                var Fieldname= 'FilterName';
                var Valid = ValidateText(ControlId,MinLength,MaxLength,Fieldname);
                if(Valid == 0){
                    return;
                }

            // Validation for Comment check special chracter
            var ControlName = "#comments";
            var MinLength = 3;
            var MaxLength = 230;
            var Fieldname2= 'Comments';
            var TextValid = TextTypeValidation(ControlName,Fieldname2);
            if(TextValid == 0){
                  return;
            }
            
            // Validation for Comment name should have a value not spaces
               
            var Valid = ValidateText(ControlName,MinLength,MaxLength,Fieldname2);
               
                if(Valid == 0){
                    return ;
                }
             /** Validation End */   
            
                    $.ajax({ 
                           url: "page-includes/master-duties-filter/save-master-duty-filter.php",
                           type: "POST",
                           dataType: "json", 
                           data: {
                                'dataFilterID':dataFilterID,
                               'dataComments': comments,
                               'dataFilterName':filterName,
                               'schedulingTeam': schedulingTeam
                             },
                     success: function(data) {
                       if (data.status == 'success') {
                            //update the master duties filter listig page
                            if(actionTypeValue != 'update'){
                                $('tr').removeClass('highlightOrange'); 
                                if($('#noRecordMsgContainer').length)
                                {
                                    $('#noRecordMsgContainer').remove();
                                }
                                $('#js_masterFilterList > tbody:last-child').append(data.newrow);
                                $("#js_activefilter").html('Activate Filter');
                                $("#js_privatefilter").html('Set To Public Filter');
                            }else{
                                
                                $("#filtername_"+dataFilterID).html(filterName);
                                $("#comments_"+dataFilterID).html(comments);
                                var selectedTeam = $('#schedulingTeamId').find(":selected").text();
                                $("#team_"+dataFilterID).html(selectedTeam);
                            }

                                populateTheMasterDuties();
                                $("#selectedFilter").html(filterName);
                                let action = 1;
                                let actionType = 'cancel';
                                buttonToogle(action,actionType);
                       }
                        else {
                            customAlert(data.sqlstatusstring);
                            populateTheHighLightFormValue();
                            populateTheMasterDuties();
                            
                            let action = 1;
                            let actionType = 'cancel';
                            buttonToogle(action,actionType);
                        }
                    }
            });
               
        }
        

        // populate the highlight row value into the form
        function populateTheHighLightFormValue(){
                $('.highlightOrange').each(function() {
                    var $flterName = $('.highlightOrange').find('td').eq(0).text();
                    var $comments = $('.highlightOrange').find('td').eq(1).text();
                    var $schedulingTeamId = $('.highlightOrange').find('td').eq(2).attr('data-team-id');
                    $("#filtername").val($flterName);
                    $("#comments").val($comments);
                    $("#schedulingTeam").val($schedulingTeamId);
                    var  dataFilterID = $(".highlightOrange").data('filterid');
                    if($("#isActiveCheck_"+dataFilterID+":checkbox:checked").length > 0){
                        let actionValue = 0;
                        
                        $("#js_activefilter").html('De-Activate Filter');
                    }else{
                        $("#js_activefilter").html('Activate Filter');
                    }
                    if($("#isPublicCheck_"+dataFilterID+":checkbox:checked").length > 0){
                        let actionValue = 0;
                       
                        $("#js_privatefilter").html('Set To Private Filter');
                    }else{
                        $("#js_privatefilter").html('Set To Public Filter');
                    }
                });
        }


        //selecte the  table row 
        $(document).on('click','#js_masterFilterList  tbody tr td',function () {
            $('tr').removeClass('highlightOrange');
            $(this).parent().addClass('highlightOrange'); 
            populateTheHighLightFormValue();
            populateTheMasterDuties();
            var  dataFilterID = $(".highlightOrange").data('filterid');
            if($("#isActiveCheck_"+dataFilterID+":checkbox:checked").length > 0){
                let actionValue = 0;
                
                $("#js_activefilter").html('De-Activate Filter');
            }else{
                $("#js_activefilter").html('Activate Filter');
            }
            if($("#isPublicCheck_"+dataFilterID+":checkbox:checked").length > 0){
                let actionValue = 0;
               
                $("#js_privatefilter").html('Set To Private Filter');
            }else{
                $("#js_privatefilter").html('Set To Public Filter');
            }
        });

        //Delete Function
        function DeleteFilter(){
            var  dataFilterID = $(".highlightOrange").data('filterid');
            customConfirm("Do you want to delete this Master Duty Filter?",function() {
                    $.ajax({ 
                           url: "page-includes/master-duties-filter/delete-master-duty-filter.php",
                           type: "POST",
                           dataType: "json", 
                           data: {
                                'dataFilterID':dataFilterID,
                                'teamId': $("#schedulingTeamId").val()
                             },
                            success: function(data) {
                                if (data.status == 'success') {
                                    //remove the master duties filter listig page
                                    $("table#js_masterFilterList tr#filterID_"+dataFilterID).remove();
                                    $('#js_masterFilterList tr:last').addClass('highlightOrange');
                                    populateTheHighLightFormValue();
                                    populateTheMasterDuties();
                                    let action = 1;
                                    let actionType = 'cancel';
                                    buttonToogle(action,actionType);
                                    return;
                                }
                                else {
                                    customAlert(data.sqlstatusstring);
                                    let action = 1;
                                    let actionType = 'cancel';
                                    buttonToogle(action,actionType);
                                }
                                
                            }
                 });
				},
				function() {
					populateTheHighLightFormValue();
				}
			);
        }

        //active de-active filter
        $("#js_activefilter").on('click',function(){
            //get the value
            var actionKey ='isActive';
            var actionValue = 1;
            var buttonValue = 'De-Activate Filter';
            var  dataFilterID = $(".highlightOrange").data('filterid');
            if($("#isActiveCheck_"+dataFilterID+":checkbox:checked").length > 0){
                actionValue = 0;
                buttonValue = 'Activate Filter';
            }
            ActionToggle(actionKey,actionValue);
            $("#js_activefilter").html(buttonValue);
        });

        //active user-id filter
        $("#js_privatefilter").on('click',function(){
          
            var actionKey ='isPublic';
            var actionValue = '';
            var buttonValue = 'Set To Private Filter';
            var  dataFilterID = $(".highlightOrange").data('filterid');
            if($("#isPublicCheck_"+dataFilterID+":checkbox:checked").length > 0){
                 actionValue = $(this).data('userid');
                 buttonValue = 'Set To Public Filter';
            }
          
           ActionToggle(actionKey,actionValue);
           $("#js_privatefilter").html(buttonValue);

        });
        //function action toogle
         function ActionToggle(actionKey,actionValue){
            var  dataFilterID = $(".highlightOrange").data('filterid');
            
            $.ajax({ 
                url: "page-includes/master-duties-filter/action-master-duty-filter.php",
                type: "POST",
                dataType: "json", 
                data: {
                     'dataFilterID':dataFilterID,
                     'actionKey':actionKey,
                     'actionValue':actionValue,
                     'teamId': $('#schedulingTeamId').val()
                  },
          success: function(data) {
            if (data.status == 'success') {
                 //update the master duties filter listig page
                 //update check the box
                    if(actionKey == 'isPublic'){
                        if(actionValue == ''){
                            $(".ispublic_"+dataFilterID).prop("checked", true);
                        }else{
                            $(".ispublic_"+dataFilterID).prop("checked", false);
                        }
                            
                    }
                    if(actionKey == 'isActive'){
                        
                        if(actionValue == 1){
                            $(".isactive_"+dataFilterID).prop("checked", true);
                        }else{
                            $(".isactive_"+dataFilterID).prop("checked", false);
                        }
                    }
                   
                    let action = 1;
                    let actionType = 'cancel';
                    
                    buttonToogle(action,actionType);
                     return;
            }
             else {
                 customAlert(data.sqlstatusstring);
                 let action = 1;
                let actionType = 'cancel';
                buttonToogle(action,actionType);
                return;
             }
         }
      });
    }
  
    

    //function action toogle
    function populateTheMasterDuties(masterdutyids=''){
        var  dataFilterID = $(".highlightOrange").data('filterid');
        var filterName = $('.highlightOrange').find('td').eq(0).text();;
        var schedulingTeam = $("#schedulingTeamId").val();
        $.ajax({ 
            url: "page-includes/master-duties-filter/select-duties-available.php",
            type: "POST",
            dataType: "json", 
            data: {
                 'dataFilterID':dataFilterID,
                 'filterName':filterName,
                 'schedulingTeamId':schedulingTeam,
                 'selectedDutyIds':masterdutyids
            },
      success: function(data) {
        if (data.status == 'success') {
             
            $("#AssignedDutiesList").html(data.view);
        }
         else {
             customAlert("no data");
         }
     }
  });
}
populateTheHighLightFormValue();
populateTheMasterDuties();
        
});