var hometeamname = '';
$(document).ready(function () {
    // set defualt current date while updating
    if($("#saveSchedulePerson").text().trim() == 'Update Person'){
        $("#hometeam").on("change", function () {
            if ($(this).val() !== "") {
                const today = new Date();
                $("#startdate").datepicker("setDate", today);
            }
       });
    }
    let prevaddteamarray = [];
    let $griddata = '';
    let schalertmsg = '';
    let useractiontype = null;
    $("#teamerrorerror").hide();
	$("#content").height('auto');
    $( function() {
        $( "#schedulePersonTab" ).tabs();
        $("#schedulePersonTab").tabs("disable", 1);
    } );
    $('#myBtnEditUpdate').addClass('buttonDisabled');
    //Modal code Start
    resetLeftTab();

    $('#addteamsubmit').on('click', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        let length = 0;
        useractiontype = $('#useractiontype').val();

        prevaddteamarray = jQuery.parseJSON($('#addteamhiddenarray').val());
        if (prevaddteamarray == "" || prevaddteamarray == null) {
            prevaddteamarray = [];
        } else {
            length = prevaddteamarray.length;
        }
        let addteammodal = document.getElementById("myModal");
        let addteamSPTeamID = $('#addteamSPTeamID').val();
        let ddlteamsid = $('#ddlTeams').val();
        let ddlteamsname = $('#ddlTeams option:selected').text();
        let addteamstartdate = $('#addteamstartdate').val();
        let addteamenddate = $('#addteamenddate').val();
        let addteamsortcode = $('#addteamsortcode').val();
        let addteamBackcolor = $('#addteamBackcolor').spectrum('get').toHexString();
        let addteamfontcolor = $('#addteamfontcolor').spectrum('get').toHexString();
        let addteamarray = [];
        let hometeamid = $("#hometeam").val();
        let isavailable = 0;
        let addteamdefaultbgcolour = 0;
        let event = new Date();
        let currentdateoriginalUKdate = event.toLocaleString('en-GB', { timeZone: 'Europe/London' });
        let currentdate = currentdateoriginalUKdate.replace(',','');
		currentdate = currentdate.replaceAll('/','-');
	    if ($("#addteamisavailable").prop("checked") == true) {
            isavailable = 1;
        }
        if ($("#addteamdefaultbgcolour").prop("checked") == true) {
            addteamdefaultbgcolour = 1;
        }
        schalertmsg = '';
        if (length > 49) {
            customAlert('You cannot add more than 50 additional teams.');
            return;
        }
        if (addteamenddate == '') {
            addteamenddate = '01-01-9999';
        }

        if (ddlteamsid == '-1') {
            schalertmsg = 'Team is a mandatory field for adding additional team. Please select one from the dropdown.\n';
        }
        if ((ddlteamsid != '-1') && (addteamstartdate == null || addteamstartdate == '')) {
            schalertmsg += 'For Additional team Start date is a mandatory field. Please select start date.\n';
        }

        if ((useractiontype=='edit') && (ddlteamsid == '-1') && (addteamstartdate != null || addteamstartdate != '')) {
          schalertmsg = 'You do not have permissions to Edit this team.';
        }

        let boundarystartdate = new Date('1995','12','02');
        let addteamstartdatesplit = addteamstartdate.split('-');
        let teamstartdatehome = $("#startdate").val();
        let teamenddatehome = $("#enddate").val();
        let addteamstartdatesplithome = teamstartdatehome.split('-');
        let addteamenddatesplithome = teamenddatehome.split('-');
        let addteamenddatesplit = addteamenddate.split('-');
        let startdatecompare = new Date(addteamstartdatesplit[2], addteamstartdatesplit[1], addteamstartdatesplit[0]);
        let startdatecomparehome = new Date(addteamstartdatesplithome[2], addteamstartdatesplithome[1], addteamstartdatesplithome[0]);
        let enddatecomparehome = new Date(addteamenddatesplithome[2], addteamenddatesplithome[1], addteamenddatesplithome[0]);
        let enddatecompare = new Date(addteamenddatesplit[2], addteamenddatesplit[1], addteamenddatesplit[0]);
        if (startdatecompare > enddatecompare) {
            schalertmsg += 'Start Date cannot be greater than End Date.\n';
        }
        if (startdatecompare < boundarystartdate) {
            schalertmsg += 'Start Date cannot be smaller than 02-12-1995.\n';
        }
        let addButtonHtmltext = $("#saveSchedulePerson").html().trim();
        if (startdatecompare < startdatecomparehome && addButtonHtmltext == 'Create Person') {
            schalertmsg += "Start Date of an Additional Team cannot be before the start date of the first Home Team. Please select a date on or after " + teamstartdatehome + "\n";
        }

        if ((ddlteamsid == hometeamid && (startdatecomparehome > startdatecompare && startdatecomparehome < enddatecompare))
            || (ddlteamsid == hometeamid && (enddatecomparehome > startdatecompare && enddatecomparehome < enddatecompare))
            || (ddlteamsid == hometeamid && (startdatecomparehome < startdatecompare && startdatecomparehome > enddatecompare))
            || (ddlteamsid == hometeamid && (enddatecomparehome < startdatecompare && enddatecomparehome > enddatecompare))
        ) {
            schalertmsg += 'Home team is (' + ddlteamsname + ') and Additional team is (' + ddlteamsname + '). Both cannot be same. Please select some other team.\n';
            customAlert('WARNING:' + '\n' + schalertmsg);
            schalertmsg='';
            prevaddteamarray='';
            return;
        }

        let schedulepersonid = $('#getpersonid').val();
        let adduseractiontype = $('#adduseractiontype').val();
        let hometeamhiddenVal = $('#hometeamhidden').val();
        let homestartdateHideNewTeam = $('#homestartdateHideNewTeam').val();

        $.ajax({
            url: "../page-includes/staff-details/process/createSchedulePerson.php",
            type: "POST",
            dataType: "json",
            async:false,
            data: {
                "task" : "validateAddTeams",
                "prevaddteamarray" : prevaddteamarray,
                "addteamSPTeamID" : addteamSPTeamID,
                "ddlteamsid" : ddlteamsid,
                "ddlteamsname": ddlteamsname,
                "schedulepersonid" : schedulepersonid,
                "addteamstartdate" : addteamstartdate,
                "addteamenddate" : addteamenddate,
                "hometeamStartDate" : $('#startdate').val(),
                "hometeamhiddenVal" : hometeamhiddenVal,
                "adduseractiontype" : adduseractiontype,
                "homestartdateHideNewTeam" : homestartdateHideNewTeam,
                "schDisplayName" : $("#dispFirstName").val()+' '+$("#dispLastName").val()
            },
            success: function (data) {
                if(data.status == 0)
                {
                    schalertmsg += data.strStatus;
                }
            }
        });

        if ($("#addteamsubmit").val() == 'Add' && length > 0) {
            prevaddteamarray.forEach((number, index, array) => {
                let teamidfromPrevarray = Object.values(array[index]);
                let teamenddatefromPrevarray = Object.values(array[index]);
                if (teamidfromPrevarray[0] == ddlteamsid && teamenddatefromPrevarray[0] == '01-01-9999') {
                    schalertmsg += 'Team already added to additional team. Please select other team to continue.\n ';
                }
            });
        }

        if (schalertmsg != '') {
            customAlert('WARNING:<br/>' + schalertmsg);
            schalertmsg='';
            prevaddteamarray='';
            return;
        }

        if ($("#addteamsubmit").val() == 'Update') {
            addteamarray = prevaddteamarray;
            prevaddteamarray.forEach((number, index, array) => {
                let teamidfromPrevarray = array[index];
                if (teamidfromPrevarray['addteamsid'] == ddlteamsid && teamidfromPrevarray['addteamstartdate'] == addteamstartdate) {
                    let splitnewenddatevalue = addteamenddate.split('-');
                    let mergenewenddatevalue = splitnewenddatevalue[2] + '' + splitnewenddatevalue[1] + '' + splitnewenddatevalue[0];
                    let isavailablearray = parseInt(teamidfromPrevarray['addteamisavailable']);
                    let spliteOldEndDate = teamidfromPrevarray['addteamenddate'].split('-');
                    let mergeOldEndDate = spliteOldEndDate[2] + '' + spliteOldEndDate[1] + '' + spliteOldEndDate[0];
                    if (mergeOldEndDate == mergenewenddatevalue && addteamsortcode == teamidfromPrevarray['addteamsortcode']
                        && addteamBackcolor == teamidfromPrevarray['addteamBackcolor'] && addteamfontcolor == teamidfromPrevarray['addteamfontcolor']
                        && isavailable == isavailablearray && addteamdefaultbgcolour == teamidfromPrevarray['addteamIsDefaultBGColour']
                    ) {
                        customAlert("There no changes made in this additional team data.");
                        return;
                    } else {
                        addteamarray[index] = {
							"addteamSPTeamID" : addteamSPTeamID,
                            'addteamsid': ddlteamsid,
                            'addteamstartdate': addteamstartdate,
                            'addteamenddate': addteamenddate,
                            'addteamsortcode': addteamsortcode,
                            'addteamBackcolor': addteamBackcolor,
                            'addteamfontcolor': addteamfontcolor,
                            'addteamisavailable': isavailable,
                            'addteamCreatedBy': teamidfromPrevarray['addteamCreatedBy'],
                            'addteamCreatedDate': teamidfromPrevarray['addteamCreatedDate'],
                            'addteamLastUpdatedBy': null,
                            'addteamLastUpdatedDate': currentdate,
                            'addteamupdate': 1,
                            'addteamIsDefaultBGColour': addteamdefaultbgcolour
                        };
                        var CurrentDate = new Date();
                        CurrentDate.setHours(0,0,0,0)
                        let classHidden = '';
                        var date = addteamenddate.substring(0, 2);
                        var month = addteamenddate.substring(3, 5);
                        var year = addteamenddate.substring(6, 10);
                        var dateToCompare = new Date(year, month - 1, date);
                        prevaddteamarray = addteamarray;
                        /*Start Additional team grid data edit*/
                        let IsAvailableEdit = (isavailable == 0 ? 'No' : 'Yes');
                        $('#endDate'+ddlteamsid).html(addteamenddate);
                        $('#isAvailable'+ddlteamsid).html(IsAvailableEdit);
                        if(CurrentDate > dateToCompare){
                            $('#ddlteamsidedit'+ddlteamsid).addClass('customdateSort');
                        }

                        if(addteamsortcode != '') {
                            $('#sortCode'+ddlteamsid).html($('<span></span>').text(addteamsortcode));
                            $('#sortCode'+ddlteamsid).css({
                                'background' : addteamBackcolor,
                                'color': addteamfontcolor
                            });
                        } else {
                            $('#sortCode'+ddlteamsid).html(addteamsortcode);
                            $('#sortCode'+ddlteamsid).removeAttr('style');
                        }

                        /*End Additional team grid data edit*/
                        $('#addteamhiddenarray').val(JSON.stringify(prevaddteamarray));
                        customAlert("Please click Update Person button on home team tab to save it permanently.");
                        resetAdditionalTeampopup();
                        addteammodal.style.display = "none";
                        return;
                    }
                }
            });
        } else {

            addteamarray = prevaddteamarray;
            addteamarray[length] = {
                "addteamSPTeamID" : addteamSPTeamID,
                'addteamsid': ddlteamsid,
                'addteamstartdate': addteamstartdate,
                'addteamenddate': addteamenddate,
                'addteamsortcode': addteamsortcode,
                'addteamBackcolor': addteamBackcolor,
                'addteamfontcolor': addteamfontcolor,
                'addteamisavailable': isavailable,
                'addteamCreatedBy': null,
                'addteamCreatedDate': currentdate,
                'addteamLastUpdatedBy': null,
                'addteamLastUpdatedDate': currentdate,
                'addteamupdate': 1,
                'addteamIsDefaultBGColour': addteamdefaultbgcolour
            };
            var CurrentDate = new Date();
            CurrentDate.setHours(0,0,0,0)
            let classHidden = '';
            var date = addteamenddate.substring(0, 2);
            var month = addteamenddate.substring(3, 5);
            var year = addteamenddate.substring(6, 10);
            var dateToCompare = new Date(year, month - 1, date);
           if(CurrentDate > dateToCompare){
                 classHidden = 'customdateSort'
           }
            prevaddteamarray = addteamarray;
            $('#addteamhiddenarray').val(JSON.stringify(prevaddteamarray));
            if(useractiontype == 'edit') {
                $griddata = document.getElementById('additionalteamgrid').innerHTML;
            }
            let IsAvailable = (isavailable == 0 ? 'No' : 'Yes');
            let sortCodeStyle = addteamsortcode != '' ? 'background:' + addteamBackcolor + ';color:' + addteamfontcolor + ';' : '';
            $griddata += '<input type="hidden" value="Add" name="adduseractiontype" id="adduseractiontype" />';
            $griddata += '<tr><td class="addTeamHeadStyle1" title="' + ddlteamsname + '">' + ddlteamsname.substring(0, 30) + '</td>' +
                '<td class="addTeamHeadStyle2">' + addteamstartdate + '</td>' +
                '<td class="addTeamHeadStyle3 editenddate_'+ddlteamsid+'" id="endDate'+ddlteamsid+'">' + addteamenddate + '</td>' +
                '<td class="addTeamHeadStyle6 editsortcode_'+ddlteamsid+'" id="sortCode'+ddlteamsid+'" style="' + sortCodeStyle + '">' + $('<span></span>').text(addteamsortcode).html() + '</td>' +
                '<td class="addTeamHeadStyle4 editisavailable_'+ddlteamsid+'" id="isAvailable'+ddlteamsid+'">' + IsAvailable + '</td>' +
                '<td class="addTeamHeadStyle5"><input id="ddlteamsidview" type="button" value="View" onclick="openModalPopup(' + ddlteamsid + ',\'' + addteamstartdate + '\',\'ddlteamsidview\')" id="' + ddlteamsid + '"/>' +
                '<input class="btnedit ddlteamsidedit_'+ddlteamsid+''+classHidden+'" id="ddlteamsidedit" type="button" value="Edit" onclick="openModalPopup(' + ddlteamsid + ',\'' + addteamstartdate + '\',\'ddlteamsidedit\')" id="' + ddlteamsid + '"/></td></tr>';

            $('#additionalteamgrid').html($griddata);
            resetAdditionalTeampopup();
            let btntext = '';
            if(useractiontype == 'edit') {
                btntext = 'Update Person';
            } else {
                btntext = 'Create Person';
            }
            customAlert('Please click '+btntext +' on home team tab to save it permanently.');
            addteammodal.style.display = "none";
            return;
        }
    });

    // Save Schedule person details
let isRequestInProgress = false;
    $('#saveSchedulePerson').on('click', function (e) {
        if (isRequestInProgress) return;
        isRequestInProgress = true;
		e.stopImmediatePropagation();
        e.preventDefault();
        let ControlName = '#dispFirstName';
        let FieldName = 'Display First Name';
        let flag = TextTypeValidation(ControlName, FieldName);
        if (flag == 0) {
            return;
        }
        let ControlName2 = '#dispLastName';
        let FieldName2 =  'Display Last Name';
        let flagLastName = TextTypeValidation(ControlName2, FieldName2);
        if (flagLastName == 0) {
            return;
        }
        let hometeamstartDate = null;
        let additionalteamarray = null;
        if ($('#addteamhiddenarray').val() == "" || $('#addteamhiddenarray').val() == null) {
            additionalteamarray = JSON.stringify(prevaddteamarray);
        } else {
            additionalteamarray = $('#addteamhiddenarray').val();
        }

        let SPTeamID = $("#SPTeamID").val();
        let displayFirstName = $("#dispFirstName").val();
        let displayLastName = $("#dispLastName").val();
        let homeTeam = $("#hometeam").val();
        if($("#hometeam").val() == null || $("#hometeam").val() == " "){
            homeTeam = $("#hometeamhidden").val();
        }
        if ($('#startdate').is(':disabled')) {
            $("#startdate").prop('disabled', false);
            hometeamstartDate = $("#startdate").val();
            $("#startdate").prop('disabled', true);
        } else {
            hometeamstartDate = $("#startdate").val();
        }
        let hometeamendDate = $("#enddate").val();
        let hometeamsortCode = $("#sortcode").val();
        let hometeambackcolour = $("#hometeambackcolour").spectrum('get').toHexString();
        let hometeamfontcolour = $("#hometeamfontcolour").spectrum('get').toHexString();
        let adminNotes = $("#adminnotes").val();
        let fwaNotes = $("#fwanotes").val();
        let intScheduledPersonID = $('#newpersonid').val();
        let controlnamedispfirstname = '#dispFirstName';
        let fieldnamedispfirstname = 'Display First Name';
        let controlnamedisplastname = '#dispLastName';
        let fieldnamedisplastname = 'Display Last Name';
        useractiontype = $('#useractiontype').val();
        let minlength = 2;
        let maxlength = 25;
        schalertmsg = '';
        let defaultBgColour = 0;
        if ($("#defaultbgcolour").prop('checked') == true) {
            defaultBgColour = 1;
        }
		let additionalleave = 0;
        if ($("#additionalleave").prop('checked') == true) {
            additionalleave = 1;
        }
        // Validations to create a schedule person with home team and additional team
        // if no end date is selected so it should be null
        if (hometeamendDate == '') {
            hometeamendDate = '01-01-9999';
        }
        if (prevaddteamarray.length != 0) {
                prevaddteamarray.forEach((number, index, array) => {
                    let teamidfromPrevarray = Object.values(array[index]);
                    if (teamidfromPrevarray[0] == homeTeam && useractiontype != 'edit') {
                        schalertmsg += 'Team is already added as additional team. Please select other team for home team.\n';
                    }
                });
        }

        // validation for Display first name non empty
        if (displayFirstName == null || displayFirstName == '') {
            schalertmsg = 'Display First name is a mandatory field for creating new record.\n';
        }
       // validation for Display last name non empty
        if (displayLastName == null || displayLastName == '') {
            schalertmsg += 'Display Last name is a mandatory field for creating new record.\n';
        }
        // validation for Home Team Id non empty
        if (homeTeam == '-1') {
            schalertmsg += 'Team is a mandatory field for home team. Please select one from the dropdown.\n';
        }
        // validation for Start Date non empty
        if (hometeamstartDate == null || hometeamstartDate == '') {
            schalertmsg += 'For home team Start date is a mandatory field. Please select start date.\n';
        }
        let boundarystartdate = new Date('1995','12','02');
        let hometeamstartdatesplit = hometeamstartDate.split('-');
        let hometeamenddatesplit = hometeamendDate.split('-');
        let startdatecomparehome = new Date(hometeamstartdatesplit[2], hometeamstartdatesplit[1], hometeamstartdatesplit[0]);
        let enddatecomparehome = new Date(hometeamenddatesplit[2], hometeamenddatesplit[1], hometeamenddatesplit[0]);
        if (startdatecomparehome > enddatecomparehome) {
            //schalertmsg += 'Start Date cannot be greater than End Date.\n';
        }
        if (startdatecomparehome < boundarystartdate) {
            schalertmsg += 'Start Date cannot be smaller than 02-12-1995.\n';
        }

        if (schalertmsg != '') {
            customAlert('*Warning*<br/>' + schalertmsg);
            return;
        }

        let validtext = ValidateText(controlnamedispfirstname, minlength, maxlength, fieldnamedispfirstname);
        if (validtext == 0) {
            return;
        }
        let validtextlastname = ValidateText(controlnamedisplastname, minlength, maxlength, fieldnamedisplastname);
        if (validtextlastname == 0) {
            return;
        }
		var selectedteamid= $('#selectedteamid').val();
	    createSchedulePerson(displayFirstName,displayLastName, homeTeam, hometeamstartDate, hometeamendDate, hometeamsortCode, hometeambackcolour, hometeamfontcolour, adminNotes, fwaNotes, useractiontype, intScheduledPersonID, additionalteamarray,defaultBgColour,additionalleave,selectedteamid, SPTeamID);

        setTimeout(() => {
            isRequestInProgress = false;
        }, 2000);
    });

});

function goBack(searchteamid, searchUserid) {
    ShowScheduledPerson(searchteamid, searchUserid);
}

function ConfirmDialog(message,searchteamid, searchUserid) {
    $('<div></div>').appendTo('body')
      .html('<div><h6>' + message + '?</h6></div>')
      .dialog({
        modal: true,
        title: 'Warning',
        zIndex: 10000,
        autoOpen: true,
        width: 'auto',
        resizable: false,
        buttons: {
          Yes: function() {
            $(this).dialog("close");
            ShowScheduledPerson(searchteamid, searchUserid);
          },
          No: function() {
            $(this).dialog("close");
          }
        },
        close: function(event, ui) {
          $(this).remove();
        }
      });
  };
/* Function to add Pagination ends here*/
function getSchedulingTeams(useractiontype, schedulepersonid) {
    $.ajax({
        type: 'POST',
        url: '../page-includes/staff-details/process/createSchedulePerson.php',
        dataType: "json",
        data: {
            task: 'getteamlist',
			schedulepersonid: schedulepersonid,
            struseractiontype: useractiontype
        },
        success: function (Result) {
            let $Teamlist = null;
            let $TeamlistAdd = null;
            var selectedteamid= $('#selectedteamid').val();
            $Teamlist += '<option value="-1">Select a Team</option>';
            $TeamlistAdd += '<option value="-1">Select a Team</option>';
            Result.forEach((number, index, array) => {

                let selectedteamidStr   =   '';
                if( selectedteamid !='' && selectedteamid == array[index]['TeamID'])
                {
                    selectedteamidStr  =   "selected='selected'";
					$('#hometeamhidden').val(array[index]['TeamID']);
                }
                let TeamNameAddHome = array[index]['TeamName'];
                if (TeamNameAddHome.toUpperCase() != 'ARCHIVE' && TeamNameAddHome.toUpperCase() != 'FREELANCER' && TeamNameAddHome.toUpperCase() != 'FREELANCERS' && TeamNameAddHome.toUpperCase() != 'OTHER BBC') {
                    $Teamlist += '<option ' + selectedteamidStr + ' value=' + array[index]['TeamID'] + '>' + array[index]['TeamName'] + '</option>';
                    $TeamlistAdd += '<option ' + selectedteamidStr + ' value=' + array[index]['TeamID'] + '>' + array[index]['TeamName'] + '</option>';
                } else {
                    $Teamlist += '<option ' + selectedteamidStr + ' value=' + array[index]['TeamID'] + '>' + array[index]['TeamName'] + '</option>';
                }

            });
            $('#ddlTeams').html($TeamlistAdd);
            $Teamlist = $Teamlist;
            $('#hometeam').html($Teamlist);
            return;
        },
        error: function (x, e) {
            if (x.status == 0) {
                customAlert('You are offline!!<br/> Please Check Your Network.');
            } else if (x.status == 404) {
                customAlert('Requested URL not found.');
            } else if (x.status == 500) {
                customAlert('Internal Server Error.');
            } else if (e == 'parsererror') {
                customAlert('Error.<br/>Parsing JSON Request failed.');
            } else if (e == 'timeout') {
                customAlert('Request Time out.');
            } else {
                customAlert('Unknown Error.<br/>' + x.responseText);
            }
        }
    });
}

function createSchedulePerson(displayFirstName,displayLastName, homeTeam, hometeamstartDate, hometeamendDate, hometeamsortCode,
                              hometeambackcolour, hometeamfontcolour, adminNotes, fwaNotes, useractiontype,
                              intScheduledPersonID, additionalteamarray, defaultBgColour,additionalleave,selectedteamid, SPTeamID) {

    $.ajax({
        type: 'POST',
        url: '../page-includes/staff-details/process/createSchedulePerson.php',
        dataType: "json",
        data: {
            'task': 'createscheduleperson',
            'displayfirstName': displayFirstName,
            'displaylastname' :displayLastName,
            'homeTeamid': homeTeam,
            'hometeamstartDate': hometeamstartDate,
            'hometeamendDate': hometeamendDate,
            'hometeamsortCode': hometeamsortCode,
            'hometeambackcolour': hometeambackcolour,
            'hometeamfontcolour': hometeamfontcolour,
            'adminNotes': adminNotes,
            'fwaNotes': fwaNotes,
            'useractiontype': useractiontype,
            'intScheduledPersonID': intScheduledPersonID,
            'addteamarray': additionalteamarray,
            'defaultBgColour': defaultBgColour,
			'additionalleave':additionalleave,
			'SPTeamID':SPTeamID
        },
        beforeSend: function(jqXHR, settings){
            $("#loading").fadeIn(200);
        },
        success: function (data) {
            if (data.sqlstatusstring == 'success') {
                //all good
                if (useractiontype == 'edit') {
                    customAlert("Scheduled person record updated successfully.");
                    $('#span_scheduled_person').html('Edit Scheduled Person Details - ' + $('#dispFirstName').val()+' '+ $('#dispLastName').val());
                } else {
                    customAlert("New Scheduled person saved successfully.");
                }
                $("#schedulePersonTab").tabs("enable", 1);
                $('#myBtnEditUpdate').removeClass('buttonDisabled').addClass('buttonEnabled');
                $('#newpersonid').val(data.sqlnewID);
                $("#startdate").prop('disabled', true);
                if ($('#useractiontype').val() == 'create') {

                    $('#myBtn').attr('disabled', true);
                    $('#saveSchedulePerson').css('display','none');
                    $(".btnedit").prop('disabled', true);


                } else {
                    $("#myBtn").prop('disabled', false);
                    $(".btnedit").prop('disabled', false);
                    $('#saveSchedulePerson').css('display','block');
                }
                    let userAction = 'edit';
				    RefreshScheduledPersonScreen(userAction,data.sqlnewID,selectedteamid);
                $('#addteamhiddenarray').val(additionalteamarray);
                // ****Need to enabled edit button in right hand window****
                hometeamname = $('#hometeam').children("option:selected").text();
                if (hometeamname.substring(0, 7) == 'Archive') {
                    $('#myBtn').attr('disabled', true);
                    $('#saveSchedulePerson').attr('disabled', true);
                    $('#additionalteamgrid :input'). attr('disabled', true);
                }else{
                    if ($('#useractiontype').val() != 'create') {
                        $('#myBtn').attr('disabled', false);
                        $('#saveSchedulePerson').attr('disabled', false);
                        $('#additionalteamgrid :input'). attr('disabled', false);
                    }
                }
                let newpersonid = $('#newpersonid').val();
                if(newpersonid.length == 0){
                    $(".schedule-person-team").addClass('buttonDisabled');
                } else {
                    $(".schedule-person-team").removeClass('buttonDisabled');
                }
                return;
            } else {
                customAlert(data.sqlstatusstring);
            }
        },
        error: function (x, e) {
            if (x.status == 0) {
                customAlert('You are offline!!<br/> Please Check Your Network.');
            } else if (x.status == 404) {
                customAlert('Requested URL not found.');
            } else if (x.status == 500) {
                customAlert('Internal Server Error.');
            } else if (e == 'parsererror') {
                customAlert('Error.<br/>Parsing JSON Request failed.');
            } else if (e == 'timeout') {
                customAlert('Request Time out.');
            } else {
                customAlert('Unknown Error.<br/>' + x.responseText);
            }
        }
    });
}

function resetLeftTab() {
    resetCreateScheduleform();
    resetAdditionalTeampopup();
    let useractiontype = $('#useractiontype').val();
    let schedulepersonid = $('#getpersonid').val();
    if (schedulepersonid != '') {
		getSchedulingTeams(useractiontype, schedulepersonid);
		setTimeout(function() {getSchedulepersondetails(schedulepersonid);}, 200);
    }else{
		getSchedulingTeams(useractiontype, schedulepersonid);
	}
}

function resetCreateScheduleform() {
    $("#dispLastName").val('');
    $("#dispFirstName").val('');
    $("#hometeam").val('-1');
    $("#startdate").val('');
    $("#enddate").val('');
    $("#sortcode").val('');
    $("#adminnotes").val('');
    $("#fwanotes").val('');
    $('#additionalteamgrid').html('');
    colorPickerDropdown('#hometeambackcolour', '#eeeeee');
    colorPickerDropdown('#hometeamfontcolour', '#000');
    $('#newpersonid').val('');
    $('#dispFirstName').on('keyup',function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        let ControlFirstName = '#dispFirstName';
        let FieldFirstName = 'Display First Name';
        let flag = TextTypeValidation(ControlFirstName, FieldFirstName);
        if (flag == 0) {
            return;
        }
    });
    $('#dispLastName').on('keyup',function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        let ControlLastName = '#dispLastName';
        let FieldLastName = 'Display Last Name';
        let flag = TextTypeValidation(ControlLastName, FieldLastName);
        if (flag == 0) {
            return;
        }
    });
    callDatePicker('#startdate');
    callDatePicker('#enddate');
    let prevhometeamidcreate = $('#hometeamhidden').val();
    $("#hometeam").on("change", function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
		prevhometeamidcreate = $('#hometeamhidden').val();
        hometeamname = $('#hometeam').children("option:selected").text();
        if (hometeamname.substring(0, 7) == 'Archive') {
            $('#myBtn').attr('disabled', true);
            $('#additionalteamgrid :input'). attr('disabled', true);
        }else{
            $('#myBtn').attr('disabled', false);
            $('#additionalteamgrid :input'). attr('disabled', false);
        }
        if (prevhometeamidcreate != $(this).val()) {
            $("#startdate").prop('disabled', false);
            $("#SPTeamID").val(0);
        } else {
             $("#startdate").val($("#homestartdateHide").val());
             $("#startdate").prop('disabled', true);
			 $("#SPTeamID").val(prevhometeamidcreate);
        }
    });
    $("#startdate").on("change", function () {
        $("#homestartdateHideNewTeam").val($("#startdate").val());
    });
}

function resetAdditionalTeampopup() {
    $('#ddlTeams').val('-1');
    $('#addteamstartdate').val('');
    $('#addteamenddate').val('');
    $('#addteamsortcode').val('');
    colorPickerDropdown('#addteamBackcolor', '#cccccc');
    colorPickerDropdown('#addteamfontcolor', '#000');
    $('#addteamisavailable').prop("checked", false);
    callDatePicker('#addteamstartdate');
    callDatePicker('#addteamenddate');
    $('#ddlTeams').prop("disabled", false);
    $('#addteamstartdate').prop("disabled", false);
    $('#addteamenddate').prop("disabled", false);
    $('#addteamsortcode').prop("disabled", false);
    $('#addteamBackcolor').prop("disabled", false);
    $('#addteamfontcolor').prop("disabled", false);
    $('#addteamisavailable').prop("disabled", false);
    $('#addteamdefaultbgcolour').prop("disabled", false);
    $('#addteamsubmit').prop("hidden", false);
}

function callDatePicker(dateControlName) {
    $(dateControlName).datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        yearRange: '1995:2050',
        inline: true,
        minDate: new Date('31-12-1995'),
        maxDate: new Date('01-01-2050')
    });
    $(dateControlName).datepicker('option', 'firstDay', 6);
}

function getSchedulepersondetails(schedulepersonid) {
    $.ajax({
        type: 'POST',
        url: '../page-includes/staff-details/process/createSchedulePerson.php',
        dataType: "json",
        data: {
            task: 'getSchedulepersondetails',
            intschedulepersonid: schedulepersonid
        },
        success: function (data) {
            let SPTeamID = (data[0].SPTeamID);
            let startdatehometeam = (data[0].StartDate);
            let enddatehometeam = (data[0].EndDate);
            if(data[0].DisplayName != "") {
                if($('#useractiontype').val() == 'view') {
                    $("#span_scheduled_person").html('View Scheduled Person Details - '+data[0].DisplayName);
                }
                else if($('#useractiontype').val() == 'edit') {
                    $("#span_scheduled_person").html('Edit Scheduled Person Details - '+data[0].DisplayName);
                }
                else {
                    $("#span_scheduled_person").html('Add New Scheduled Person Details');
                }
            }
            $("#dispFirstName").val(data[0].DisplayFirstName);
            $("#dispLastName").val(data[0].DisplayLastName);
            $('#hometeam').find('option[value="' + data[0].TeamID + '"]').attr('selected', 'selected');
            $('#hometeam').val(data[0].TeamID);
            $("#hometeamhidden").val(data[0].TeamID);

            $("#SPTeamID").val(SPTeamID);
            $("#startdate").val(startdatehometeam);
            $("#homestartdateHide").val(startdatehometeam);
            $("#startdate").prop('disabled', true);
            if (data[0].EndDate == '01-01-9999') {
                $("#enddate").val();
            } else {
                $("#enddate").val(enddatehometeam);
            }
            $("#homeenddatediv").css('display','none');
            $("#sortcode").val(data[0].SortCode);
            $("#adminnotes").val(data[0].AdminNotes);
            $("#fwanotes").val(data[0].FWANotes);
            $('#hometeamfontcolour').val(data[0].fontcolour);
            colorPickerDropdown('#hometeamfontcolour', data[0].fontcolour);
            $('#hometeambackcolour').val(data[0].BackgroundColour);
            colorPickerDropdown('#hometeambackcolour', data[0].BackgroundColour);
            if(data[0].IsDefaultBGColour == 1){
                $('#defaultbgcolour').prop('checked', true);
            }
			if(data[0].IsAdditionalLeave == 1){
                $('#additionalleave').prop('checked', true);
            }
            $('#newpersonid').val(data[0].ScheduledPersonID);
            $("#schedulePersonTab").tabs("enable", 1);
            let newpersonid = $('#newpersonid').val();
            if(newpersonid.length == 0 || newpersonid == "") {
                $(".schedule-person-team").addClass('buttonDisabled');
            } else {
                $(".schedule-person-team").removeClass('buttonDisabled');
            }
            let disableeditbutton = '';
            if ($('#useractiontype').val() == 'view') {
                disableeditbutton = 'hidden';
                $("#myBtn").prop('disabled', true);
            } else {
                $("#myBtn").prop('disabled', false);
                disableeditbutton = 'button';
            }
            if((data[0].StaffDetailsID == null || data[0].StaffDetailsID == 0) && data[0].NetLogin == null && $('#useractiontype').val() != 'view') {
                //****Need to uncomment it once code merge with naveeta's code****
                $('#myBtnEditUpdate').removeClass('buttonDisabled').addClass('buttonEnabled');
            } else {
                $('#myBtnEditUpdate').hide();
                $("#js_title").html(data[0].Title);
                $("#js_staffnumber").html(data[0].StaffNumber);
                $("#js_netlogin").html(data[0].NetLogin);
                $("#js_forename").html(data[0].Forename);
                $("#js_surname").html(data[0].Surname);
                $("#js_midname").html('');
                $("#js_prefname").html(data[0].PreferredForename);
                $("#js_designation").html(data[0].JobTitle);
                $(".contract_history ").removeClass('buttonDisabled');
                if(data[0].StaffNumber  == null) {
                    $(".contract_history ").addClass('buttonDisabled');
                }
            }
            //*************************************************
            let prevhometeamid = $('#hometeamhidden').val();
            $("#hometeam").on("change", function (e) {
                e.stopImmediatePropagation();
                e.preventDefault();
                hometeamname = $('#hometeam').children("option:selected").text();
                if (hometeamname.substring(0, 7) == 'Archive') {
                    $('#myBtn').attr('disabled', true);
                    $('#additionalteamgrid :input'). attr('disabled', true);
                }else{
                    $('#myBtn').attr('disabled', false);
                    $('#additionalteamgrid :input'). attr('disabled', false);
                }
                if (prevhometeamid != $(this).val()) {
                    $("#startdate").prop('disabled', false);
                } else {
                    $("#startdate").prop('disabled', true);
                }
            });

            let addteamarray = [];
            let $griddata = '';
            let prevaddteamarray = [];
            if(data.length > 1) {
                $griddata += '<thead><tr><th class="addTeamHeadStyle1">Team Name</th><th class="addTeamHeadStyle2">Start Date</th><th class="addTeamHeadStyle3">End Date</th><th class="addTeamHeadStyle6">Sort Code</th><th class="addTeamHeadStyle4">Is Available</th><th class="addTeamHeadStyle5">&emsp;Action</th></tr></thead>';
                data.forEach((number, index, array) => {
                    let IsAvail = array[index]['IsAvailable'] == 0 ? 'No' : 'Yes';
                    if (index > 0 && array[index]['IsHomeTeam'] == 0) {
						let ludate = new Date(array[index]['LastUpdatedDate']);
						let newluDate = ludate.toLocaleString('en-GB', { timeZone: 'Europe/London' });
						newluDate = newluDate.replace(',','');
						newluDate = newluDate.replaceAll('/','-');
                        addteamarray[index - 1] = {
                            addteamSPTeamID: array[index]['SPTeamID'],
                            addteamsid: array[index]['TeamID'],
                            addteamstartdate: array[index]['StartDate'],
                            addteamenddate: array[index]['EndDate'],
                            addteamsortcode: array[index]['SortCode'],
                            addteamBackcolor: array[index]['BackgroundColour'],
                            addteamfontcolor: array[index]['fontcolour'],
                            addteamisavailable: array[index]['IsAvailable'],
                            addteamCreatedBy: array[index]['CreatedBy'],
                            addteamCreatedDate: array[index]['CreatedDate'],
                            addteamLastUpdatedBy: array[index]['LastUpdatedBy'],
                            addteamLastUpdatedDate: newluDate,
                            addteamupdate: 0,
                            addteamIsDefaultBGColour: array[index]['IsDefaultBGColour']
                        };
						var CurrentDate = new Date();
                        CurrentDate.setHours(0,0,0,0)
                        let classHidden = 'notclass';
                        var date = array[index]['EndDate'].substring(0, 2);
                        var month = array[index]['EndDate'].substring(3, 5);
                        var year = array[index]['EndDate'].substring(6, 10);
                        var dateToCompare = new Date(year, month - 1, date);
                       if(CurrentDate > dateToCompare){
                             classHidden = 'customdateSort'
                       }
                       let sortCodeStyle = array[index]['SortCode'] != '' ? 'background:' + array[index]['BackgroundColour'] + ';color:' + array[index]['fontcolour'] + ';' : '';
                        $griddata += '<tr><td class="addTeamHeadStyle1" title="' + array[index]['TeamName'] + '">' + array[index]['TeamName'].substring(0, 30) + '</td>' +
                            '<td class="addTeamHeadStyle2">' + array[index]['StartDate'] + '</td>' +
                            '<td class="addTeamHeadStyle3" id="endDate'+array[index]['TeamID']+'">' + array[index]['EndDate'] + '</td>' +
                            '<td class="addTeamHeadStyle6" id="sortCode'+array[index]['TeamID']+'" style="' + sortCodeStyle + '">' + $('<span></span>').text(array[index]['SortCode']).html() + '</td>' +
                            '<td class="addTeamHeadStyle4" id="isAvailable'+array[index]['TeamID']+'">' + IsAvail + '</td>' +
                            '<td class="addTeamHeadStyle5"><input id="ddlteamsidview" type="button" value="View" onclick="openModalPopup(' + array[index]["TeamID"] +',\''+ array[index]["StartDate"] + '\',\'ddlteamsidview\')" teamid="' + array[index]['TeamID'] + '"/>' +
                            '<input class = "'+classHidden+'"  id="ddlteamsidedit" type="' + disableeditbutton + '" value="Edit" onclick="openModalPopup(' + array[index]["TeamID"] +',\''+ array[index]["StartDate"] + '\',\'ddlteamsidedit\')" teamid="' + array[index]['TeamID'] + '" /></td>' +
                            '</tr>';
                    }
                });
                $griddata += '<input type="hidden" value="Add" name="adduseractiontype" id="adduseractiontype" />';
                $('#additionalteamgrid').html($griddata);
                prevaddteamarray = addteamarray;
                $('#addteamhiddenarray').val(JSON.stringify(prevaddteamarray));
                hometeamname = $('#hometeam').children("option:selected").text();
                if (hometeamname.substring(0, 7) == 'Archive') {
                    $('#myBtn').attr('disabled', true);
                    $('#additionalteamgrid :input'). attr('disabled', true);
                }else{
                    $('#myBtn').attr('disabled', false);
                    $('#additionalteamgrid :input'). attr('disabled', false);
                }
            }
        },
        error: function (x, e) {
            if (x.status == 0) {
                customAlert('You are offline!!<br/> Please Check Your Network.');
            } else if (x.status == 404) {
                customAlert('Requested URL not found.');
            } else if (x.status == 500) {
                customAlert('Internal Server Error.');
            } else if (e == 'parsererror') {
                customAlert('Error.<br/>Parsing JSON Request failed.');
            } else if (e == 'timeout') {
                customAlert('Request Time out.');
            } else {
                customAlert('Unknown Error.<br/>' + x.responseText);
            }
        }
    });
}

function openModalPopup(schedulepersonteamid, schedulepersonteamstartdate, buttonidpopup) {
    $("#teamerrorerror").hide();
    // Modal loading logic start
	let struseractiontype = 'edit';
    if (buttonidpopup == 'myBtn'){
        $("#Addlabelteam").prop('hidden', false);
        $("#Addlabelstartdate").prop('hidden', false);
		struseractiontype = 'create';
    } else {
        if (buttonidpopup == 'ddlteamsidview') {
            $("#Addlabelteam").prop('hidden', true);
            $("#Addlabelstartdate").prop('hidden', true);
        }
        else {
            $("#Addlabelteam").prop('hidden', false);
            $("#Addlabelstartdate").prop('hidden', false);
        }
    }
	if((schedulepersonteamid == null) && (schedulepersonteamstartdate == null) && ($('#useractiontype').val() == 'edit'))
	{
		struseractiontype = 'createAddTeam';
	}
	$.ajax({
        url: "page-includes/staff-details/process/createSchedulePerson.php",
        type: "POST",
        dataType: "json",
        data: {
            'task': 'getteamlist',
            'schedulepersonid': $('#getpersonid').val(),
            'struseractiontype': struseractiontype
        },
        success: function (data) {
			let $TeamlistAdd = '<option value="-1">Select a Team</option>';
			data.forEach((number, index, array) => {
                let selectedteamidStr   =   '';
                if( schedulepersonteamid !='' && schedulepersonteamid == array[index]['TeamID'])
                {
                    selectedteamidStr  =   "selected='selected'";
                }
                let TeamNameAddHome = array[index]['TeamName'];
                if (TeamNameAddHome.toUpperCase() != 'ARCHIVE' && TeamNameAddHome.toUpperCase() != 'FREELANCER' && TeamNameAddHome.toUpperCase() != 'FREELANCERS' && TeamNameAddHome.toUpperCase() != 'OTHER BBC') {
                    $TeamlistAdd += '<option ' + selectedteamidStr + ' value=' + array[index]['TeamID'] + '>' + array[index]['TeamName'] + '</option>';
                }

            });
			$('#ddlTeams').html($TeamlistAdd);
        },
        error: function (x, e) {
            if (x.status == 0) {
                customAlert('You are offline!!<br/> Please Check Your Network.'+ x.responseText);
            } else if (x.status == 404) {
                customAlert('Requested URL not found.');
            } else if (x.status == 500) {
                customAlert('Internal Server Error.');
            } else if (e == 'parsererror') {
                customAlert('Error.<br/>Parsing JSON Request failed.');
            } else if (e == 'timeout') {
                customAlert('Request Time out.');
            } else {
                customAlert('Unknown Error.<br/>' + x.responseText);
            }
        }
    });
    if(schedulepersonteamid == null)
    {
        $('#adduseractiontype').val('Add');
    }else{
        $('#adduseractiontype').val('Edit');
    }
    // Get the modal
    let modal = document.getElementById("myModal");
    // Get the <span> element that closes the modal
    let span = document.getElementsByClassName("closebox")[0];
    // Function to open modal
    openPopupforAdditionalteam(schedulepersonteamid, buttonidpopup, modal, schedulepersonteamstartdate);
    // When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        modal.style.display = "none";
    }
    // Text validation for Display first name
    $('#dispFirstName').on('keypress', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        let ControlNameDispName = '#dispFirstName';
        let FieldNameDispName = 'Display First Name';
        TextTypeValidation(ControlNameDispName, FieldNameDispName);
    });

    // Text validation for Display last name
    $('#dispLastName').on('keypress', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        let ControlNameDispLastName = '#dispLastName';
        let FieldNameDispLastName = 'Display Last Name';
        TextTypeValidation(ControlNameDispLastName, FieldNameDispLastName);
    });
    $('#addteamcancel').on('click', function () {
        modal.style.display = "none";
    });
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
}

function openPopupforAdditionalteam(schedulepersonteamid, buttonidpopup, modal, schedulepersonteamstartdate) {

    let homeTeampopup = $("#hometeam").val();
    let homeTeamstartdate = $("#startdate").val();
    let message = '';
    if (homeTeampopup == '-1') {
        message += "You cannot add additional team before selecting home team.\n";
    }
    if(homeTeamstartdate == '' || homeTeamstartdate == null) {
        message += "You cannot add additional team before selecting home team start date.";
    }
    if(message != '') {
        customAlert("Warning:<br/>"+message);
        return;
    }
    modal.style.display = "block";
    if (schedulepersonteamid != null) {
        // Function to add data in additional team popup
        addAddpopupdata(schedulepersonteamid, buttonidpopup, schedulepersonteamstartdate);
    } else {
        resetAdditionalTeampopup();
        $('#addteamsubmit').val("Add");
        $('#addteamsubmit').prop("disabled", false);
        $('#addteamsubmit').prop("hidden", false);
        $('#addteamcancel').prop("hidden", false);
    }
}

function addAddpopupdata(schedulepersonteamid, buttonidpopup, schedulepersonteamstartdate) {

    let additionalteamenddate = '';
    useractiontype = $('#useractiontype').val();
    prevaddteamarray = jQuery.parseJSON($('#addteamhiddenarray').val());
    prevaddteamarray.forEach((number, index, array) => {
        let teamidfromPrevarray = Object.values(array[index]);
        if (teamidfromPrevarray[1] == schedulepersonteamid && teamidfromPrevarray[2] == schedulepersonteamstartdate) {
            if (teamidfromPrevarray[3] == '01-01-9999') {
                additionalteamenddate = '';
            } else {
                additionalteamenddate = teamidfromPrevarray[3];
            }
            $('#ddlTeams').find('option:selected').removeAttr('selected');
            $('#ddlTeams').find('option[value="' + teamidfromPrevarray[1] + '"]').attr('selected', 'selected');
            $('#addteamstartdate').val(teamidfromPrevarray[2]);
            $('#addteamenddate').val(additionalteamenddate);
            $('#addteamsortcode').val(teamidfromPrevarray[4]);
            $('#addteamSPTeamID').val(teamidfromPrevarray[0]);
            colorPickerDropdown('#addteamBackcolor', teamidfromPrevarray[5]);
            colorPickerDropdown('#addteamfontcolor', teamidfromPrevarray[6]);
            if (teamidfromPrevarray[7] == false) {
                $('#addteamisavailable').prop("checked", false);
            } else {
                $('#addteamisavailable').prop("checked", true);
            }
            if (teamidfromPrevarray[13] == 1) {
                $('#addteamdefaultbgcolour').prop("checked", true);
            } else {
                $('#addteamdefaultbgcolour').prop("checked", false);
            }
            if (buttonidpopup == 'ddlteamsidview') {
                $('#ddlTeams').prop("disabled", true);
                $('#addteamstartdate').prop("disabled", true);
                $('#addteamenddate').prop("disabled", true);
                $('#addteamsortcode').prop("disabled", true);
                $("#addteamBackcolor").spectrum('disable');
                $("#addteamfontcolor").spectrum('disable');
                $('#addteamBackcolor').prop("disabled", true);
                $('#addteamfontcolor').prop("disabled", true);
                $('#addteamisavailable').prop("disabled", true);
                $('#addteamsubmit').prop("hidden", true);
                $('#addteamcancel').prop("hidden", true);
                $('#addteamdefaultbgcolour').prop("disabled", true);
            } else {
                $('#ddlTeams').prop("disabled", true);
                $('#addteamstartdate').prop("disabled", true);
                $('#addteamenddate').prop("disabled", false);
                $('#addteamsortcode').prop("disabled", false);
                $("#addteamBackcolor").spectrum('enable');
                $("#addteamfontcolor").spectrum('enable');
                $('#addteamBackcolor').prop("disabled", false);
                $('#addteamfontcolor').prop("disabled", false);
                $('#addteamisavailable').prop("disabled", false);
                $('#addteamsubmit').prop("hidden", false);
                $('#addteamsubmit').prop("disabled", false);
                if($('#ddlTeams').val() != schedulepersonteamid){
                    $('#addteamsubmit').prop("disabled", true);
                    $("#teamerrorerror").show();
                }
                $('#addteamsubmit').val("Update");
                $('#addteamcancel').prop("hidden", false);
                $('#addteamdefaultbgcolour').prop("disabled", false);
            }
        }
    });
}

function openTab(evt, tabName) {
    if (tabName == 'stfdtls') {
        document.getElementById('stfdtls').style.display = "block";
        document.getElementById('cnthist').style.display = "none";
    } else if (tabName == 'scperson') {
        document.getElementById('schistory').style.display = "none";
        document.getElementById('scperson').style.display = "block";
    } else if (tabName == 'cnthist') {
        document.getElementById('stfdtls').style.display = "none";
        document.getElementById('cnthist').style.display = "block";
    } else {
        document.getElementById('schistory').style.display = "block";
        document.getElementById('scperson').style.display = "none";
    }
    evt.currentTarget.className += " active";
}

window.onload = function () {
    changePage(1);
};
