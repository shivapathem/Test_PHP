/*
   * @Description : Fetch all of the Schedule Person Team History.
   * @access : NA
   * @global :NA
   * @param  : NA
   * @return : NA
   */
$(document).ready(function (e) {
    let newpersonid = $('#newpersonid').val();
    if (newpersonid.length == 0) {
        $(".schedule-person-team").addClass('buttonDisabled');
    } else {
        $(".schedule-person-team").removeClass('buttonDisabled');
    }
    $(".schedule-person-team").on('click', function () {
        let personid = $("#newpersonid").val();
        let table = $('#scheduleTeamHistory').DataTable({
            "bPaginate":true,
            "iDisplayLength": 20,
            "destroy":true,
            "lengthChange": false,
            "order": [[ 2, 'desc' ]],
            "fnDrawCallback": function (oSettings) {
                if ($('#scheduleTeamHistory tr').length >= 11) {
                    $(".dataTables_paginate").css('visibility', 'visible');
                    $(".dataTables_length").css("display", "block");
                    $("#dataTables_paginate ").css('font-size', '8px');
                    $(".dataTables_info").css('display', 'block');
                } else {
                    $(".dataTables_info").css('display', 'none');
                }
            },

            "ajax": {
                url: '../page-includes/staff-details/view/scheduleTeamData.php',
                "data": {personid: personid},
                "complete": function(xhr, textStatus) {
                    if(xhr.status == "200")
                    {
                        var filterFlag=$('#schedulingTeamHistoryHomeTeamFilterFlag').val();
                        if(filterFlag != '')
                        {
                            $('#yadcf-filter--scheduleTeamHistory-1').val(filterFlag).trigger('change');
                        }
                    }
                }
            }
        });
        yadcf.init(table, [
            {
                column_number: 1,
                filter_type: 'select'
            }
        ]);
        $(".dataTables_length").css("display", "block");
    });

    $(document).on('change','.yadcf-filter',function(){
        var val = $('option:selected',this).val()
        $('#schedulingTeamHistoryHomeTeamFilterFlag').val(val);
    });

    $(".first-tab ul li:first").click(function (e) {
        $(this).addClass("ui-tabs-active ui-state-active ui-state-focus");
        $(this).attr("aria-expanded", true);
        $(this).attr("aria-selected", true);
        $(".schedule-person-team").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $(".schedule-person-team").attr("aria-expanded", false);
        $(".schedule-person-team").attr("aria-selected", false);

    });
    $(".schedule-person-team").click(function (e) {
        $(this).addClass("ui-tabs-active ui-state-active ui-state-focus");
        $(this).attr("aria-expanded", true);
        $(this).attr("aria-selected", true);
        $(".first-tab ul li:first").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $(".first-tab ul li:first").attr("aria-expanded", false);
        $(".first-tab ul li:first").attr("aria-selected", false);

    });

    $(".staff_detail").click(function (e) {
        $(this).addClass("ui-tabs-active ui-state-active ui-state-focus");
        $(this).attr("aria-expanded", true);
        $(this).attr("aria-selected", true);
        $(".contract_history").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $(".contract_history").attr("aria-expanded", false);
        $(".contract_history").attr("aria-selected", false);

    });
    $(".contract_history").click(function (e) {
        $(this).addClass("ui-tabs-active ui-state-active ui-state-focus");
        $(this).attr("aria-expanded", true);
        $(this).attr("aria-selected", true);
        $(".staff_detail").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $(".staff_detail").attr("aria-expanded", false);
        $(".staff_detail").attr("aria-selected", false);

    });
    $(document).on('click', '#schPersonDelete', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        let HomeTeamIdDel = $(this).attr("value");
        let personid = $(this).attr("PersonIdHomeTeam");
        // Validate Person is assigned to a rota
        customConfirmModal('Do you wish to delete this Home Team?',
            function() {
                ValidatePersonOnRota(HomeTeamIdDel,personid);
            },
            function() {
                return;
            }
        );
    });
});


function ValidatePersonOnRota(HomeTeamIdDel,personid) {
    $.ajax({
        url: "../page-includes/staff-details/process/createSchedulePerson.php",
        type: "POST",
        dataType: "json",
        async:false,
        data: {
            "task" : "ValidateSchPersonHomeTeamHaveRota",
            "HomeTeamId" : HomeTeamIdDel,
            "personid" : personid
        },
        success: function (data) {
            if(data.IntStatus == 0)
            {
                customConfirmModal(data.StrStatus,
                    function() {
                        deleteHomeTeamSchPerson(HomeTeamIdDel,personid,1);
                    },
                    function() {
                        return;
                    }
                );
            } else {
                // Delete Home Team for sch person
                deleteHomeTeamSchPerson(HomeTeamIdDel,personid);
            }
        }
    });
}

function deleteHomeTeamSchPerson(HomeTeamIdDel, personid, DeleteFromRota = 0) {
    $.ajax({
        url: "../page-includes/staff-details/process/createSchedulePerson.php",
        type: "POST",
        dataType: "json",
        async:false,
        data: {
            "task" : "deleteSchPersonHomeTeam",
            "HomeTeamId" : HomeTeamIdDel,
            "personid" : personid,
            "DeleteFromRota": DeleteFromRota
        },
        success: function (data) {
            if(data[0].intStatus == 0)
            {
                customAlert(data[0].strStatus);
            } else {
                $('#hometeam').val(data[0].TeamID);
                $("#hometeamhidden").val(data[0].TeamID);
                $("#startdate").val(data[0].StartDate);
                $("#homestartdateHide").val(data[0].StartDate);
                $("#startdate").prop('disabled', true);
                if (data.EndDate == '01-01-9999') {
                    $("#enddate").val();
                } else {
                    $("#enddate").val(data[0].EndDate);
                }
                $("#homeenddatediv").css('display','none');
                $("#sortcode").val(data[0].SortCode);
                $('#hometeamfontcolour').val(data[0].fontcolour);
                colorPickerDropdown('#hometeamfontcolour', data[0].fontcolour);
                $('#hometeambackcolour').val(data[0].BackgroundColour);
                colorPickerDropdown('#hometeambackcolour', data[0].BackgroundColour);
                $('.schedule-person-team').trigger('click');

                let addteamarray = [];
                let $griddata = '';
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
                                addteamupdate: 0
                            };
                            let sortCodeStyle = array[index]['SortCode'] != '' ? 'background:' + array[index]['BackgroundColour'] + ';color:' + array[index]['fontcolour'] + ';' : '';
                            $griddata += '<tr><td class="addTeamHeadStyle1" title="' + array[index]['TeamName'] + '">' + array[index]['TeamName'].substring(0, 30) + '</td>' +
                                '<td class="addTeamHeadStyle2">' + array[index]['StartDate'] + '</td>' +
                                '<td class="addTeamHeadStyle3">' + array[index]['EndDate'] + '</td>' +
                                '<td class="addTeamHeadStyle6" style="' + sortCodeStyle + '">' + $('<span></span>').text(array[index]['SortCode']).html() + '</td>' +
                                '<td class="addTeamHeadStyle4">' + IsAvail + '</td>' +
                                '<td class="addTeamHeadStyle5"><input id="ddlteamsidview" type="button" value="View" onclick="openModalPopup(' + array[index]["TeamID"] + ',\'' + array[index]["StartDate"] + '\',\'ddlteamsidview\')" teamid="' + array[index]['TeamID'] + '"/>' +
                                '<input id="ddlteamsidedit" type="button" value="Edit" onclick="openModalPopup(' + array[index]["TeamID"] + ',\'' + array[index]["StartDate"] + '\',\'ddlteamsidedit\')" teamid="' + array[index]['TeamID'] + '" /></td>' +
                                '</tr>';
                        }
                    });
                    $griddata += '<input type="hidden" value="Add" name="adduseractiontype" id="adduseractiontype" />';
                }
                $('#additionalteamgrid').html($griddata);
                $('#addteamhiddenarray').val(JSON.stringify(addteamarray));
            }
        }
    });
}
