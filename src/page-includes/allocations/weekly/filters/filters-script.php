<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$argScreenName = $_POST['screenName'] ?? '';
$screenName = (isset($screenName) && $screenName != '') ? $screenName : $argScreenName;
if(($screenName != 'ViewDaily')){
?>
<script type="text/javascript" src="js/date-comment.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript" src="js/allocations/weekly/weekly.js?v=<?php echo time(); ?>"></script>
<?php }?>
<script type="text/javascript">
    function getFilterDetails(filterId, initialLoad = 0, screenName = '') {
        $('#filterNameErr').hide();
        $('#filterNameErr').html('');
		if(filterId != ''){
            $.ajax({
                type: "post",
                url: "/components/filters/filter-process.php",
                data: {
                    filterId: filterId,
                    action:'getdetails'
                },
                beforeSend: function (jqXHR, settings){
                    $('#loading').hide();
                },
                success: function (data,status) { 
				    var filterData = JSON.parse(data);
					var blnkVal = '';
					for (var key in filterData) {
						filterData[key] = (filterData[key] == '&')? '__AND__': filterData[key];
					}
                    if(filterData.Description == null){
                        $('#FilterName').val(blnkVal);
                    } else {
                        $('#FilterName').val(filterData.Description);
                    }

                    if(filterData.StaffName == null){
                        $('#StaffName').val(blnkVal);
                    } else {
                        $('#StaffName').val(filterData.StaffName);
                    }
                    $('#StaffNameFilter').val(filterData.StaffNameOptFilter);

                    if(filterData.SortCodeFilter == null){
                        $('#SortCode').val(blnkVal);
                    } else {
                        $('#SortCode').val(filterData.SortCodeFilter);
                    }
                    $('#SortCodeFilter').val(filterData.SortCodeOptFilter);

                    if(filterData.CostCode == null){
                        $('#CostCode').val(blnkVal);
                    } else {
                        $('#CostCode').val(filterData.CostCode);
                    }
                    $('#CostCodeFilter').val(filterData.CostCodeOptFilter);

                    if(filterData.DutyFilter == null){
                        $('#Duty').val(blnkVal);
                    } else {
                        $('#Duty').val(filterData.DutyFilter);
                    }
                    $('#DutyFilter').val(filterData.DutyOptFilter);
					
					if(filterData.DutyTime == null){
                        $('#DutyTime').val(blnkVal);
                    } else {
                        $('#DutyTime [value='+filterData.DutyTime+']').attr('selected', 'true');
						if (filterData.DutyTime=='-1'){
							$(".chosen-single").css('color', '#999');
						} else {
							$(".chosen-single").css('color', '#000');
						}	
				    }
                    $('#DutyTimeFilter').val(filterData.DutyTimeOptFilter);


                    if(filterData.JobName == null){
                        $('#JobName').val(blnkVal);
                    } else {
                        $('#JobName').val(filterData.JobName);
                    }
                    
                    if (filterData.JobNameOptFilter==''){
                        $('#JobNameFilter').val('*');
                    } else {
                        $('#JobNameFilter').val(filterData.JobNameOptFilter);
                    }   

                    if(filterData.SortOrder == null){
                        $('#SortOrder').val(blnkVal);
                    } else {
                       $('#SortOrder [value='+filterData.SortOrder+']').attr('selected', 'true');
                    }

                    var getFilterAdditionalTeams = filterData.AdditionalTeams;
                    if(getFilterAdditionalTeams != null){
                        var splitAdditionalTeams = getFilterAdditionalTeams.split(',');
                        var additionalTeamsId = [];
                        $(splitAdditionalTeams).each(function(indx,value){
                            additionalTeamsId.push(value);
                        });
                        $("#AdditionalTeams").val(additionalTeamsId).trigger("chosen:updated");
                       if (filterData.AdditionalTeamsOptFilter==''){
                            $('#AdditionalTeamsFilter').val('*');
                        } else {
                            $('#AdditionalTeamsFilter').val(filterData.AdditionalTeamsOptFilter);
                        }   
                    } else {
                        $("#AdditionalTeams").val(blnkVal);
                        $("#AdditionalTeamsFilter").val(blnkVal);
                    }
                
                    var getFilterSkills = filterData.SkillName;
                    if(getFilterSkills != null){
                        var splitSkills = getFilterSkills.split(',');
                        var SkillsId = [];
                        $(splitSkills).each(function(ind,val){
                            SkillsId.push(val);
                        });
                        $("#Skill").val(SkillsId).trigger("chosen:updated");
                        $('#SkillFilter').val(filterData.SkillNameOptFilter);
                    } else {
                        $("#Skill").val(blnkVal);
                        $('#SkillFilter').val(blnkVal);
                    }

                    var getJobLabels = filterData.JobLabel;
                    if(getJobLabels != null){
                        var splitJobLabels = getJobLabels.split(',');
                        var JobLabelsId = [];
                        $(splitJobLabels).each(function(ind,val){
                            JobLabelsId.push(val);
                        });
                        $("#JobLabel").val(JobLabelsId).trigger("chosen:updated");
                        if (filterData.JobLabelOptFilter==''){
                            $('#JobLabelFilter').val('*');
                        } else {
                            $('#JobLabelFilter').val(filterData.JobLabelOptFilter);
                        }           
                    } else {
                        $("#JobLabel").val(blnkVal);
                        $('#JobLabelFilter').val(blnkVal);
                    }
                
                    var getDutyLabels = filterData.DutyLabel;
                    if(getDutyLabels != null){
                        var splitDutyLabels = getDutyLabels.split(',');
                        var DutyLabelsId = [];
                        $(splitDutyLabels).each(function(ind,val){
                            DutyLabelsId.push(val);
                        });
                        $("#DutyLabel").val(DutyLabelsId).trigger("chosen:updated");
                        $('#DutyLabelFilter').val(filterData.DutyLabelOptFilter);
                    } else {
                        $("#DutyLabel").val(blnkVal);
                        $('#DutyLabelFilter').val(blnkVal);
                    }
                    
                    $('#filterId').val(filterData.ID);
                
                    if(filterData.AndMatch == 1){
                        $("#andFilter").prop("checked", true);
                        $("#orFilter").prop("checked", false);
                    } else {
                        $("#orFilter").prop("checked", true);
                        $("#andFilter").prop("checked", false);
                    }

                    if(filterData.JobNameAll == 1){
                        $("#JobNameALL").prop("checked", true);
                    } else {
                        $("#JobNameALL").prop("checked", false);
                    }

                    if(filterData.JobLabelAll == 1){
                        $("#JobLabelALL").prop("checked", true);
                    } else {
                        $("#JobLabelALL").prop("checked", false);
                    }
					
					var getDutyTime = filterData.DutyTime;
                    if(getDutyTime != 0){
                        $("#DutyTime").val(getDutyTime).trigger("chosen:updated");
                        $('#DutyTimeFilter').val('*');
                    } else {
                        $("#DutyTime").val(blnkVal);
                       $('#DutyTimeFilter').val('*');
                    }
                    if(initialLoad == 1 && screenName == 'ViewDaily') {
                        applyViewDailyFilter();
                    }
                    if(initialLoad == 1 && screenName == 'ViewWeekly') {
                        applyViewWeeklyFilter();
                    }
                    
                    if(initialLoad == 1 && screenName == 'MultiWeek') {
                        applyViewMultiWeeklyFilter();
                    }
                    
                    if(initialLoad == 1 && screenName == 'EditWeeklyRota') {
                        applyEditRotaWeeklyFilter();
                    }
                    
                }
            });
        } else {
            $("#StaffNameFilter").val("*");
            $("#StaffName").val("");
            $("#SortCodeFilter").val("*");
            $("#SortCode").val("");
            $("#CostCodeFilter").val("*");
            $("#CostCode").val("");
            $("#SkillFilter").val("*");
            $("#Skill").val("").trigger("chosen:updated");
            $("#DutyFilter").val("*");
            $("#Duty").val("");
            $("#JobNameFilter").val("*");
            $("#JobName").val("");
            $("#JobLabelFilter").val("*");
            $("#JobLabel").val("").trigger("chosen:updated");
    		$("#DutyLabelFilter").val("*");
            $('#DutyLabel').val("").trigger("chosen:updated");
            $("#SortOrder").val("");
            $("#AdditionalTeamsFilter").val("*");
            $("#AdditionalTeams").val("*");
            $('#AdditionalTeams').val("").trigger("chosen:updated");
            $("#FilterName").val("");
            $("#JobNameALL").prop("checked", false);
            $("#JobLabelALL").prop("checked", false);
        }
    }

    function savePublicFilter(screenName){
        var StaffNameFilter = $('#StaffNameFilter').val();
        var StaffName = $('#StaffName').val();
        var SortCodeFilter = $('#SortCodeFilter').val();
        var SortCode = $('#SortCode').val();
        var CostCodeFilter = $('#CostCodeFilter').val();
        var CostCode = $('#CostCode').val();
        var SkillFilter = $('#SkillFilter').val();
        var Skill = $('#Skill').val();
		if(screenName == 'ViewDaily'){
			var DutyTimeFilter = $('#DutyTimeFilter').val();
			var DutyTime = $('#DutyTime').val();
            
            if(DutyTime!= null && DutyTime.indexOf('NOW') !== -1){
                customAlert('Cannot add or edit public filter with option NOW in Dutytime.');
                return false;
            }
		} else {
			var DutyTimeFilter = '';
			var DutyTime = '';
		}	
		var DutyFilter = $('#DutyFilter').val();
        var Duty = $('#Duty').val();
        var JobNameFilter = $('#JobNameFilter').val();
        var JobName = $('#JobName').val();
        var JobLabelFilter = $('#JobLabelFilter').val();
        var JobLabel = $('#JobLabel').val();
        var DutyLabelFilter = $('#DutyLabelFilter').val();
        var DutyLabel = $('#DutyLabel').val();
        var SortOrder = $('#SortOrder').val();
        
        if((screenName != 'EditWeekly') || (screenName != 'EditWeeklyRota')){
            var AdditionalTeamsFilter = $('#AdditionalTeamsFilter').val();
            var AdditionalTeams = $('#AdditionalTeams').val();
        } else {
            var AdditionalTeamsFilter = '';
            var AdditionalTeams = '';
        }
        var FilterName = $('#FilterName').val();
        var filterId = $('#filterId').val();
        var teamId = $('#teamId').val();
        var andMatch = 0;
        if($('#andFilter').prop('checked') == true){
            andMatch = 1;
        }
        var isPublic = 1;

        if(Skill != null){
            Skill = Skill.join(",");
        } else {
            Skill = '';
        }

        if((screenName != 'EditWeekly') && (screenName != 'EditWeeklyRota')){
            if(AdditionalTeams != null){
                AdditionalTeams = AdditionalTeams.join(",");
            } else {
                AdditionalTeams = '';
            }
        }

        if(JobLabel != null){
            JobLabel = JobLabel.join(",");
        } else {
            JobLabel = '';
        }
        
        if(DutyLabel != null){
            DutyLabel = DutyLabel.join(",");
        } else {
            DutyLabel = '';
        }

        var submitData = true;
        $('#filterNameErr').hide();
        $('#filterNameErr').html('');
        if(FilterName == ''){
            $('#filterNameErr').html('Please Enter Filter Name');
            $('#filterNameErr').show();
            submitData = false;
        }

        var actionType = '';
        if(filterId == ''){
            actionType = 'new';
        } else {
            actionType = 'old';
        }

        var jobNameAll = 0;
        if($('#JobNameALL').prop('checked') == true){
            jobNameAll = 1;
        }

        var jobLabelAll = 0;
        if($('#JobLabelALL').prop('checked') == true){
            jobLabelAll = 1;
        }
        
        if(submitData){
            $.ajax({
                type: "post",
                url: "/components/filters/filter-process.php",
                data: {
                    'StaffNameFilter': StaffNameFilter,
                    'StaffName': StaffName,
                    'SortCodeFilter': SortCodeFilter,
                    'SortCode': SortCode,
                    'CostCodeFilter': CostCodeFilter,
                    'CostCode': CostCode,
                    'SkillFilter': SkillFilter,
                    'Skill': Skill,
                  	'DutyTimeFilter': DutyTimeFilter,
					'DutyTime': DutyTime,
					'DutyFilter': DutyFilter,
                    'Duty': Duty,
                    'JobNameFilter': JobNameFilter,
                    'JobName': JobName,
                    'JobLabelFilter': JobLabelFilter,
                    'JobLabel': JobLabel,
                    'DutyLabelFilter': DutyLabelFilter,
                    'DutyLabel': DutyLabel,
                    'SortOrder': SortOrder,
                    'AdditionalTeamsFilter': AdditionalTeamsFilter,
                    'AdditionalTeams': AdditionalTeams,
                    'FilterName': FilterName,
                    'ID': filterId,
                    'isPublic': isPublic,
                    'teamId': teamId,
                    'andMatch': andMatch,
                    'action':'savepublic',
                    'actionType':actionType,
                    'checkExists':'Yes',
                    'jobNameAll':jobNameAll,
                    'jobLabelAll':jobLabelAll
                },
                beforeSend: function (jqXHR, settings){
                    $('#loading').hide();
                },
                success: function (data,status) { 
                    var returnData = $.parseJSON(data);
                    if(returnData.status == true){
                      if(returnData.isExists == true){
                        customConfirm(returnData.confirmMessage,function(){
                                $.ajax({
                                    type: "post",
                                    url: "/components/filters/filter-process.php",
                                    data: {
                                        'StaffNameFilter': StaffNameFilter,
                                        'StaffName': StaffName,
                                        'SortCodeFilter': SortCodeFilter,
                                        'SortCode': SortCode,
                                        'CostCodeFilter': CostCodeFilter,
                                        'CostCode': CostCode,
                                        'SkillFilter': SkillFilter,
                                        'Skill': Skill,
                                        'DutyTimeFilter': DutyTimeFilter,
										'DutyTime': DutyTime,
										'DutyFilter': DutyFilter,
                                        'Duty': Duty,
                                        'JobNameFilter': JobNameFilter,
                                        'JobName': JobName,
                                        'JobLabelFilter': JobLabelFilter,
                                        'JobLabel': JobLabel,
                                        'DutyLabelFilter': DutyLabelFilter,
                                        'DutyLabel': DutyLabel,
                                        'SortOrder': SortOrder,
                                        'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                        'AdditionalTeams': AdditionalTeams,
                                        'FilterName': FilterName,
                                        'ID': filterId,
                                        'isPublic': isPublic,
                                        'teamId': teamId,
                                        'andMatch': andMatch,
                                        'action':'savepublic',
                                        'actionType':actionType,
                                        'checkExists':'No',
                                        'jobNameAll':jobNameAll,
                                        'jobLabelAll':jobLabelAll
                                    },
                                    beforeSend: function (jqXHR, settings){
                                        $('#loading').hide();
                                    },
                                    success: function (dataInn,statusInn) { 
                                        var returnDataInn = $.parseJSON(dataInn);
                                        if(actionType == 'old'){
                                            if(returnDataInn.status == true){
                                                applyViewFilter(screenName,returnDataInn.lastInsId);
                                            } else {
                                                customAlert(returnDataInn.errMsg);
                                            }
                                        }
                                        $('#filterType').val('');
                                        $('#publicFilterId').val('');
                                        $('#publicFilterRow').hide();
                                        $('#toggleBtnRow').hide();
                                        $( "span.allocation-filters" ).trigger( "click" );
                                    }
                                });
                            },
                            function() {
                            }
                        );
                      } else {
                        $.ajax({
                            type: "post",
                            url: "/components/filters/filter-process.php",
                            data: {
                                  'StaffNameFilter': StaffNameFilter,
                                  'StaffName': StaffName,
                                  'SortCodeFilter': SortCodeFilter,
                                  'SortCode': SortCode,
                                  'CostCodeFilter': CostCodeFilter,
                                  'CostCode': CostCode,
                                  'SkillFilter': SkillFilter,
                                  'Skill': Skill,
								  'DutyTimeFilter': DutyTimeFilter,
								  'DutyTime': DutyTime,
                                  'DutyFilter': DutyFilter,
                                  'Duty': Duty,
								  'JobNameFilter': JobNameFilter,
                                  'JobName': JobName,
                                  'JobLabelFilter': JobLabelFilter,
                                  'JobLabel': JobLabel,
                                  'DutyLabelFilter': DutyLabelFilter,
                                  'DutyLabel': DutyLabel,
                                  'SortOrder': SortOrder,
                                  'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                  'AdditionalTeams': AdditionalTeams,
                                  'FilterName': FilterName,
                                  'ID': filterId,
                                  'isPublic': isPublic,
                                  'teamId': teamId,
                                  'andMatch': andMatch,
                                  'action':'savepublic',
                                  'actionType':actionType,
                                  'checkExists':'No',
                                'jobNameAll':jobNameAll,
                                'jobLabelAll':jobLabelAll
                            },
                            beforeSend: function (jqXHR, settings){
                                $('#loading').hide();
                            },
                            success: function (dataInn,statusInn) { 
                                var returnDataInn = $.parseJSON(dataInn);
                                  if(returnDataInn.status == true){
                                    if(returnDataInn.updateDropdown == true){
                                        $('#publicFilterId').html(filterDropDownHtml(returnDataInn.filters));
                                    }
                                    $('#privateFilterId').val('');
                                    $('#privateFilterRow').hide();
                                    applyViewFilter(screenName,returnDataInn.lastInsId);
                                  } else {
                                     customAlert(returnDataInn.errMsg);
                                  }
                            }
                        });
                      }
                    } else {
                       customAlert(returnData.errMsg);
                    }
                }
            });
        }
    }

    function savePrivateFilter(screenName){
        var StaffNameFilter = $('#StaffNameFilter').val();
        var StaffName = $('#StaffName').val();
        var SortCodeFilter = $('#SortCodeFilter').val();
        var SortCode = $('#SortCode').val();
        var CostCodeFilter = $('#CostCodeFilter').val();
        var CostCode = $('#CostCode').val();
        var SkillFilter = $('#SkillFilter').val();
        var Skill = $('#Skill').val();
		if(screenName == 'ViewDaily'){
			var DutyTimeFilter = $('#DutyTimeFilter').val();
			var DutyTime = $('#DutyTime').val();
            if(DutyTime!= null && DutyTime.indexOf('NOW') !== -1){
                customAlert('Cannot add or edit private filter with option NOW in Dutytime.');
                return false;
            }
		} else {
			var DutyTimeFilter = '';
			var DutyTime = '';
		}	
		var DutyFilter = $('#DutyFilter').val();
        var Duty = $('#Duty').val();
        var JobNameFilter = $('#JobNameFilter').val();
        var JobName = $('#JobName').val();
        var JobLabelFilter = $('#JobLabelFilter').val();
        var JobLabel = $('#JobLabel').val();
        var DutyLabelFilter = $('#DutyLabelFilter').val();
        var DutyLabel = $('#DutyLabel').val();
        var SortOrder = $('#SortOrder').val();
        if((screenName != 'EditWeekly') && (screenName != 'EditWeeklyRota')) {
            var AdditionalTeamsFilter = $('#AdditionalTeamsFilter').val();
            var AdditionalTeams = $('#AdditionalTeams').val();
        } else {
            var AdditionalTeamsFilter = '';
            var AdditionalTeams = '';
        }
        var FilterName = $('#FilterName').val();
        var filterId = $('#filterId').val();
        var teamId = $('#teamId').val();
        var andMatch = 0;
        if($('#andFilter').prop('checked') == true){
            andMatch = 1;
        }
        var isPublic = 0;
        var selectedPublicFilter = $('#publicFilterId').val();

        if(Skill != null){
            Skill = Skill.join(",");
        } else {
            Skill = '';
        }

        if((screenName != 'EditWeekly') && (screenName != 'EditWeeklyRota')){
            if(AdditionalTeams != null){
                AdditionalTeams = AdditionalTeams.join(",");
            } else {
                AdditionalTeams = '';
            }
        }

        if(JobLabel != null){
            JobLabel = JobLabel.join(",");
        } else {
            JobLabel = '';
        }
        
         if(DutyLabel != null){
            DutyLabel = DutyLabel.join(",");
        } else {
            DutyLabel = '';
        }
        var submitData = true;
        $('#filterNameErr').hide();
        $('#filterNameErr').html('');
        if(FilterName == ''){
            $('#filterNameErr').html('Please Enter Filter Name');
            $('#filterNameErr').show();
            submitData = false;
        }

        var actionType = '';
        if(filterId == ''){
            actionType = 'new';
        } else {
            actionType = 'old';
        }

        var jobNameAll = 0;
        if($('#JobNameALL').prop('checked') == true){
            jobNameAll = 1;
        }

        var jobLabelAll = 0;
        if($('#JobLabelALL').prop('checked') == true){
            jobLabelAll = 1;
        }
        
        if(submitData){
            $.ajax({
                type: "post",
                url: "/components/filters/filter-process.php",
                data: {
                    'StaffNameFilter': StaffNameFilter,
                    'StaffName': StaffName,
                    'SortCodeFilter': SortCodeFilter,
                    'SortCode': SortCode,
                    'CostCodeFilter': CostCodeFilter,
                    'CostCode': CostCode,
                    'SkillFilter': SkillFilter,
                    'Skill': Skill,
                    'DutyFilter': DutyFilter,
                    'Duty': Duty,
					'DutyTimeFilter': DutyTimeFilter,
                    'DutyTime': DutyTime,
                    'JobNameFilter': JobNameFilter,
                    'JobName': JobName,
                    'JobLabelFilter': JobLabelFilter,
                    'JobLabel': JobLabel,
                    'DutyLabelFilter': DutyLabelFilter,
                    'DutyLabel': DutyLabel,
                    'SortOrder': SortOrder,
                    'AdditionalTeamsFilter': AdditionalTeamsFilter,
                    'AdditionalTeams': AdditionalTeams,
                    'FilterName': FilterName,
                    'ID': filterId,
                    'isPublic': isPublic,
                    'selectedPublicFilter': selectedPublicFilter,
                    'teamId': teamId,
                    'andMatch': andMatch,
                    'action':'saveprivate',
                    'actionType':actionType,
                    'checkExists':'Yes',
                    'jobNameAll':jobNameAll,
                    'jobLabelAll':jobLabelAll
                },
                beforeSend: function (jqXHR, settings){
                    $('#loading').hide();
                },
                success: function (data,status) { 
                    var returnData = $.parseJSON(data);
                    if(returnData.status == true){
                        if(returnData.isExists == true){
                          customConfirm(returnData.confirmMessage,function(){
                                $.ajax({
                                    type: "post",
                                    url: "/components/filters/filter-process.php",
                                    data: {
                                        'StaffNameFilter': StaffNameFilter,
                                        'StaffName': StaffName,
                                        'SortCodeFilter': SortCodeFilter,
                                        'SortCode': SortCode,
                                        'CostCodeFilter': CostCodeFilter,
                                        'CostCode': CostCode,
                                        'SkillFilter': SkillFilter,
                                        'Skill': Skill,
                                        'DutyFilter': DutyFilter,
                                        'Duty': Duty,
										'DutyTimeFilter': DutyTimeFilter,
                                        'DutyTime': DutyTime,
                                        'JobNameFilter': JobNameFilter,
                                        'JobName': JobName,
                                        'JobLabelFilter': JobLabelFilter,
                                        'JobLabel': JobLabel,
                                        'DutyLabelFilter': DutyLabelFilter,
                                        'DutyLabel': DutyLabel,
                                        'SortOrder': SortOrder,
                                        'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                        'AdditionalTeams': AdditionalTeams,
                                        'FilterName': FilterName,
                                        'ID': filterId,
                                        'isPublic': isPublic,
                                        'selectedPublicFilter': selectedPublicFilter,
                                        'teamId': teamId,
                                        'andMatch': andMatch,
                                        'action':'saveprivate',
                                        'actionType':actionType,
                                        'checkExists':'No',
                                        'jobNameAll':jobNameAll,
                                        'jobLabelAll':jobLabelAll
                                    },
                                    beforeSend: function (jqXHR, settings){
                                        $('#loading').hide();
                                    },
                                    success: function (dataInn,statusInn) { 
                                        var returnDataInn = $.parseJSON(dataInn);
                                        if(actionType == 'old'){
                                            if(returnDataInn.status == true){
                                                applyViewFilter(screenName,returnDataInn.lastInsId);
                                            } else {
                                                customAlert(returnDataInn.errMsg);
                                            }
                                        }
                                    }
                                });
                              },
                              function() {
                              }
                          );
                        } else {
                            $.ajax({
                                type: "post",
                                url: "/components/filters/filter-process.php",
                                data: {
                                    'StaffNameFilter': StaffNameFilter,
                                      'StaffName': StaffName,
                                      'SortCodeFilter': SortCodeFilter,
                                      'SortCode': SortCode,
                                      'CostCodeFilter': CostCodeFilter,
                                      'CostCode': CostCode,
                                      'SkillFilter': SkillFilter,
                                      'Skill': Skill,
                                      'DutyFilter': DutyFilter,
                                      'Duty': Duty,
									  'DutyTimeFilter': DutyTimeFilter,
                                      'DutyTime': DutyTime,
                                      'JobNameFilter': JobNameFilter,
                                      'JobName': JobName,
                                      'JobLabelFilter': JobLabelFilter,
                                      'JobLabel': JobLabel,
                                      'DutyLabelFilter': DutyLabelFilter,
                                      'DutyLabel': DutyLabel,
                                      'SortOrder': SortOrder,
                                      'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                      'AdditionalTeams': AdditionalTeams,
                                      'FilterName': FilterName,
                                      'ID': filterId,
                                      'isPublic': isPublic,
                                      'selectedPublicFilter': selectedPublicFilter,
                                      'teamId': teamId,
                                      'andMatch': andMatch,
                                      'action':'saveprivate',
                                      'actionType':actionType,
                                      'checkExists':'No',
                                    'jobNameAll':jobNameAll,
                                    'jobLabelAll':jobLabelAll
                                },
                                beforeSend: function (jqXHR, settings){
                                    $('#loading').hide();
                                },
                                success: function (dataInn,statusInn) { 
                                    var returnDataInn = $.parseJSON(dataInn);
                                      if(returnDataInn.status == true){
                                        if(returnDataInn.updateDropdown == true){
                                            $('#privateFilterId').html(filterDropDownHtml(returnDataInn.filters));
                                        }
                                        $('#publicFilterId').val('');
                                        $('#publicFilterRow').hide();
                                        applyViewFilter(screenName,returnDataInn.lastInsId);
                                      } else {
                                         customAlert(returnDataInn.errMsg);
                                      }
                                }
                            });
                        }
                    } else {
                        customAlert(returnData.errMsg);
                    }
                }
            });
        }
    }

    function deleteAutopagesFilterConfirmation(haveAccess){
        let filterType = $('#filterType').val();
        let filterId = $('#filterId').val();
        let teamId = $('#teamId').data('teamid');
        <?php 
        ob_start();
        $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : ($_COOKIE['editWeeklyUserId'] ?? '');
        ?>
        let userId = '<?php echo $sessUserId;?>';
        if((haveAccess == 1) || (haveAccess == 0 && filterType == 'private')){
            customConfirm('Are you sure want to delete filter?',function(){
                  deleteAutoPagesFilter(filterType, teamId, filterId, userId, 'deletefilter');
                },
                function() {
                }
            );
        } else {
            customAlert('You did not have access to delete the filter');
        }
    }

    function deleteAutoPagesFilter(filterType, teamId, filterId, userId, actionName){
        $.ajax({
            type: "post",
            url: "/components/filters/filter-process.php",
            data: {
                'filterType': filterType,
                'teamId': teamId,
                'filterId': filterId,
                'userId': userId,
                'action':actionName
            },
            beforeSend: function (jqXHR, settings){
                $('#loading').hide();
            },
            success: function (data,status) { 
                var returnData = $.parseJSON(data);
                if(returnData.status == true){
                    customAlert('Filter deleted successfully', 2000);
                    if(filterType == 'public'){
                        $('#publicFilterId').html(filterDropDownHtml(returnData.filters));
                    } else {
                        $('#privateFilterId').html(filterDropDownHtml(returnData.filters));
                    }
                    $('#filterType').val('');
                    $('#publicFilterId').val('');
                    $('#privateFilterId').val('');
                    $('#publicFilterRow').hide();
                    $('#privateFilterRow').hide();
                    $('#toggleBtnRow').hide();
                    $( "span.allocation-filters" ).trigger( "click" );
                    $('#clearFilterIcon').trigger('click');
                }
            }
        });
    }

    function applyViewFilter(screenName='', paramFilterId='', initialLoad='No', viewWeeklyWeekNum = '',requestrota=''){

	    var filterId = '';
        if(paramFilterId == '' && screenName != 'EditWeeklyNameFilter'){
            let filterType = $('#filterType').val();
            if(filterType == 'public'){
                filterId = $('#publicFilterId').val();
            }
            if(filterType == 'private'){
                filterId = $('#privateFilterId').val();
            }
        } else {
            filterId = paramFilterId;
        }
        if((filterId != '') && (filterId > 0) && screenName != 'EditWeeklyNameFilter'){
			var teamid=$('#teamId').val();
        
			if(screenName == 'EditWeekly' || screenName == 'EditWeeklyNameFilter') {
				var teamid=$('#teamId').data('teamid');
			} 
			if(screenName == 'EditWeeklyRota'){
				var teamid=$('#teamId').val();
			} 
            var postSetFilterData = {
                filterId: filterId,
                teamId: teamid,
                action:'setfilter',
                screenName: screenName
            }
	        $.ajax({
                type: "post",
                url: "/components/filters/filter-process.php",
                data: postSetFilterData,
                beforeSend: function (jqXHR, settings){
                    $('#loading').hide();
                },
                success: function (dt,st) { 
                    var dtResult = $.parseJSON(dt);
		            if(dtResult.status){
                        $.ajax({
                            type: "post",
                            url: "/components/filters/filter-process.php",
                            data: {
                                filterId: filterId,
                                action:'getdetails'
                            },
                            beforeSend: function (jqXHR, settings){
                                $('#loading').hide();
                            },
                            success: function (dataFilter,statusFilter) { 
                                var filterDetails = $.parseJSON(dataFilter);
								for (var key in filterDetails) {
									filterDetails[key] = (filterDetails[key] == '&')? '__AND__': filterDetails[key];
								}
                                var editWeeklyQuickFilter = '';
                                if(screenName == 'EditWeeklyNameFilter' || screenName == 'EditWeekly') {
                                    if($('#edit-weekly-name-quick-filter').length == 0) { // Loading page from rota view
                                        if(((localStorage.getItem('EditWeeklyNameFilter') != '') && (localStorage.getItem('EditWeeklyNameFilter') != null))) {
                                            var filterQuickSearch = {'teamId' : ''};
                                            try {
                                                filterQuickSearch = JSON.parse(localStorage.getItem('EditWeeklyNameFilter'));
                                            } catch(e) {
                                                filterQuickSearch = {'teamId' : ''};
                                            }
                                            
                                            if(filterQuickSearch.teamId == $('#teamId').val()) {
                                                editWeeklyQuickFilter = filterQuickSearch.filter;
                                            }
                                        }
                                    } else {
                                        editWeeklyQuickFilter = $('#edit-weekly-name-quick-filter').val();
                                    }
                                }
                                var StaffNameFilter = (editWeeklyQuickFilter != '') ? '*' : filterDetails.StaffNameOptFilter;
                                var StaffName = (editWeeklyQuickFilter != '') ? editWeeklyQuickFilter : filterDetails.StaffName;
							    var SortCodeFilter = filterDetails.SortCodeOptFilter;
                                var SortCode = filterDetails.SortCodeFilter;
                                var CostCodeFilter = filterDetails.CostCodeOptFilter;
                                var CostCode = filterDetails.CostCode;
                                var SkillFilter = filterDetails.SkillNameOptFilter;
                                var Skill = filterDetails.SkillName;
								var DutyFilter = filterDetails.DutyOptFilter;
                                var Duty = filterDetails.DutyFilter;
                                var JobNameFilter = filterDetails.JobNameOptFilter;
                                var JobName = filterDetails.JobName;
                                var JobLabelFilter = filterDetails.JobLabelOptFilter;
                                var JobLabel = filterDetails.JobLabel;
                                var DutyLabelFilter = filterDetails.DutyLabelOptFilter;
                                var DutyLabel = filterDetails.DutyLabel;
                                var SortOrder = filterDetails.SortOrder;
                                var AdditionalTeamsFilter = filterDetails.AdditionalTeamsOptFilter;
                                var AdditionalTeams = filterDetails.AdditionalTeams;
                                var teamId = $('#teamId').val();
                                if(teamId !==true){ teamId = filterDetails.SchedulingTeamId;}
                                var isShifttoCheck = $('#isShifttoCheck').val();
                                var andMatch = filterDetails.AndMatch;
                                var jobNameAll = filterDetails.JobNameAll;
                                var jobLabelAll = filterDetails.JobLabelAll;
                                if(screenName == 'ViewDaily'){
                                    var intWeek = $('#intWeek').val();
                                    var intDay = $('#intDay').val();
                                    var rolepermission = $('#rolepermission').val();
                                    var startDate = $('#startDate').val();
                                    var endDate =  $('#endDate').val();
                                    var strCurrentDate = $('#strCurrentDate').val();
									var DutyTime = filterDetails.DutyTime;
									var DutyTimeFilter = $('#DutyTimeFilter').val();
		                            var postData = {
                                        'StaffNameFilter': StaffNameFilter,
                                        'StaffName': StaffName,
                                        'SortCodeFilter': SortCodeFilter,
                                        'SortCode': SortCode,
                                        'CostCodeFilter': CostCodeFilter,
                                        'CostCode': CostCode,
                                        'SkillFilter': SkillFilter,
                                        'Skill': Skill,
                                        'DutyTime': DutyTime,
										'DutyTimeFilter': DutyTimeFilter,
										'DutyFilter': DutyFilter,
                                        'Duty': Duty,
                                        'JobNameFilter': JobNameFilter,
                                        'JobName': JobName,
                                        'JobLabelFilter': JobLabelFilter,
                                        'JobLabel': JobLabel,
                                        'DutyLabelFilter': DutyLabelFilter,
                                        'DutyLabel': DutyLabel,
                                        'SortOrder': SortOrder,
                                        'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                        'AdditionalTeams': AdditionalTeams,
                                        'andMatch': andMatch,
                                        'action':'createquery',
                                        'screenName': screenName,
                                        'intWeek': intWeek,
                                        'intDay': intDay,
                                        'intTeamID': teamId,
                                        'rolepermission': rolepermission,
                                        'startDate': startDate,
                                        'endDate': endDate,
                                        'strCurrentDate': strCurrentDate
                                    }
                                    $('*').qtip('hide');
                                    var skillFilterDaily='';
                                    var dutyFilterDaily='';
                                    var dutyTimeFilterDaily='';
                                    var jobFilterDaily='';
                                    if((SkillFilter == '&') && (Skill != null)){
                                        skillFilterDaily=Skill;
                                    } 
                                    
                                    if((DutyLabelFilter == '&') && (DutyLabel != null)){
                                        dutyFilterDaily=DutyLabel;
                                    }
                                    if((JobLabelFilter == '&') && (JobLabel != null)){
                                        jobFilterDaily=JobLabel;
                                    }
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/allocations-daily.php",
                                        data: {
                                            'teamId': teamId,
                                            'type': 'filterapplied',
                                            'selFilterType': filterDetails.isPublic ? 'public' : 'private',
                                            'selFilterId': filterDetails.ID,
                                            'date': $('#strCurrentDate').val(),
                                            'skillFilterDaily': skillFilterDaily,
                                            'dutyTimeFilterDaily': dutyTimeFilterDaily,
                                            'dutyFilterDaily': dutyFilterDaily,
                                            'jobFilterDaily': jobFilterDaily,
                                            'screenName': screenName,
                                            'jobNameAll':jobNameAll,
                                            'jobLabelAll':jobLabelAll
                                        },
                                        beforeSend: function (jqXHR, settings){
                                            $('#loading').hide();
                                        },
                                        success: function (data,status) { 
                                            $('#content').html(data);
                                            var filterNameForDisplay = filterDetails.Description;
                                            if(filterNameForDisplay!=null && filterNameForDisplay.length > 16){
                                                $('#filterName').html('<span class="qtip-hover" title="'+filterNameForDisplay+'"> '+filterNameForDisplay.substr(0,16)+'...</span>');
                                            } else {
                                                $('#filterName').html('<span class="qtip-hover" title="'+filterNameForDisplay+'"> '+filterNameForDisplay+'</span>');
                                            }
                                            $('#clearFilterIcon').show();
                                            getFilterDetails(filterDetails.ID, 1, 'ViewDaily');
                                        }
                                    });
                                    return true;;
                                }
                                if(screenName == 'ViewWeekly'){
                                    var intSWeekNumber = $('#intSWeekNumber').val();
                                    var intEWeekNumber = $('#intEWeekNumber').val();
                                    let intTeamIDs = $('#intTeamIDs').val();
                                    let intSortOrder = $('#intSortOrder').val();
                                    var postData = {
                                        'StaffNameFilter': StaffNameFilter,
                                        'StaffName': StaffName,
                                        'SortCodeFilter': SortCodeFilter,
                                        'SortCode': SortCode,
                                        'CostCodeFilter': CostCodeFilter,
                                        'CostCode': CostCode,
                                        'SkillFilter': SkillFilter,
                                        'Skill': Skill,
                                        'DutyFilter': DutyFilter,
                                        'Duty': Duty,
                                        'JobNameFilter': JobNameFilter,
                                        'JobName': JobName,
                                        'JobLabelFilter': JobLabelFilter,
                                        'JobLabel': JobLabel,
                                        'DutyLabelFilter': DutyLabelFilter,
                                        'DutyLabel': DutyLabel,
                                        'SortOrder': SortOrder,
                                        'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                        'AdditionalTeams': AdditionalTeams,
                                        'andMatch': andMatch,
                                        'action':'createquery',
                                        'screenName': screenName,
                                        'intSWeekNumber': intSWeekNumber,
                                        'intEWeekNumber': intEWeekNumber,
                                        'intTeamIDs': intTeamIDs,
                                        'intSortOrder': intSortOrder,
                                        'isShifttoCheck': isShifttoCheck
                                    }
                                    $('*').qtip('hide');
                                    var skillFilterDaily='';
                                    var dutyFilterDaily='';
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/allocations-weekly.php",
                                        data: {
                                            'teamId': teamId,
                                            'queryStr': '',
                                            'queryStr2': '',
                                            'queryStr3': '',
                                            'orderStr': SortOrder,
                                            'type': 'filterapplied',
                                            'selFilterType': filterDetails.isPublic ? 'public' : 'private',
                                            'selFilterId': filterDetails.ID,
                                            'isShifttoCheck': isShifttoCheck,
                                            'WeekNumber':intSWeekNumber,
                                            'skillFilterDaily': skillFilterDaily,
                                            'dutyFilterDaily': dutyFilterDaily,
                                            'additionalTeams': getAvailableAdditionalTeam(AdditionalTeams.split(','))
                                        },
                                        beforeSend: function (jqXHR, settings){
                                            $('#loading').hide();
                                        },
                                        success: function (data,status) { 
                                            $('#content').html(data);
                                            var filterNameForDisplay = filterDetails.Description;
                                            if(filterNameForDisplay!=null && filterNameForDisplay.length > 16){
                                                $('#filterName').html('<span class="qtip-hover" title="'+filterNameForDisplay+'"> '+filterNameForDisplay.substr(0,16)+'...</span>');
                                            } else {
                                                $('#filterName').html('<span class="qtip-hover" title="'+filterNameForDisplay+'"> '+filterNameForDisplay+'</span>');
                                            }
                                            $('#clearFilterIcon').show();
                                            getFilterDetails(filterDetails.ID, 1, 'ViewWeekly');
                                        }
                                    });
                                    return true;
                                }
                                if(screenName == 'MultiWeek'){
                                    var intSWeekNumber = $('#intSWeekNumber').val();
                                    var intEWeekNumber = $('#intEWeekNumber').val();
                                    let intTeamIDs = $('#intTeamIDs').val();
                                    var postData = {
                                        'StaffNameFilter': StaffNameFilter,
                                        'StaffName': StaffName,
                                        'SortCodeFilter': SortCodeFilter,
                                        'SortCode': SortCode,
                                        'CostCodeFilter': CostCodeFilter,
                                        'CostCode': CostCode,
                                        'SkillFilter': SkillFilter,
                                        'Skill': Skill,
                                        'DutyFilter': DutyFilter,
                                        'Duty': Duty,
                                        'JobNameFilter': JobNameFilter,
                                        'JobName': JobName,
                                        'JobLabelFilter': JobLabelFilter,
                                        'JobLabel': JobLabel,
                                        'DutyLabelFilter': DutyLabelFilter,
                                        'DutyLabel': DutyLabel,
                                        'SortOrder': SortOrder,
                                        'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                        'AdditionalTeams': AdditionalTeams,
                                        'andMatch': andMatch,
                                        'action':'createquery',
                                        'screenName': screenName,
                                        'intSWeekNumber': intSWeekNumber,
                                        'intEWeekNumber': intEWeekNumber,
                                        'intTeamIDs': intTeamIDs
                                    }
                                    $('*').qtip('hide');
                                    var skillFilterDaily='';
                                    var dutyFilterDaily='';
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/allocations-multi-week.php",
                                        data: {
                                            'teamId': teamId,
                                            'queryStr': '',
                                            'queryStr2': '',
                                            'queryStr3': '',
                                            'orderStr': SortOrder,
                                            'type': 'filterapplied',
                                            'selFilterType': filterDetails.isPublic ? 'public' : 'private',
                                            'selFilterId': filterDetails.ID,
                                            'week':intSWeekNumber,
                                            'skillFilterDaily': skillFilterDaily,
                                            'dutyFilterDaily': dutyFilterDaily,
                                            'additionalTeams': getAvailableAdditionalTeam(AdditionalTeams.split(','))
                                        },
                                        beforeSend: function (jqXHR, settings){
                                            $('#loading').hide();
                                        },
                                        success: function (data,status) { 
                                            $('#content').html(data);
                                            var filterNameForDisplay = filterDetails.Description;
                                            if(filterNameForDisplay!=null && filterNameForDisplay.length > 16){
                                                $('#filterName').html('<span class="qtip-hover" title="'+filterNameForDisplay+'"> '+filterNameForDisplay.substr(0,16)+'...</span>');
                                            } else {
                                                $('#filterName').html('<span class="qtip-hover" title="'+filterNameForDisplay+'"> '+filterNameForDisplay+'</span>');
                                            }
                                            $('#clearFilterIcon').show();
                                            getFilterDetails(filterDetails.ID, 1, 'MultiWeek');
                                        }
                                    });
                                    return true;
                                }
                                if(screenName == 'EditWeekly'){
                                    let startDate = $('#startDate').val();
                                    let endDate = $('#endDate').val();
                                    var postData = {
                                        'StaffNameFilter': StaffNameFilter,
                                        'StaffName': StaffName,
                                        'SortCodeFilter': SortCodeFilter,
                                        'SortCode': SortCode,
                                        'CostCodeFilter': CostCodeFilter,
                                        'CostCode': CostCode,
                                        'SkillFilter': SkillFilter,
                                        'Skill': Skill,
                                        'DutyFilter': DutyFilter,
                                        'Duty': Duty,
                                        'JobNameFilter': JobNameFilter,
                                        'JobName': JobName,
                                        'JobLabelFilter': JobLabelFilter,
                                        'JobLabel': JobLabel,
                                        'DutyLabelFilter': DutyLabelFilter,
                                        'DutyLabel': DutyLabel,
                                        'SortOrder': SortOrder,
                                        'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                        'AdditionalTeams': AdditionalTeams,
                                        'andMatch': andMatch,
                                        'action':'createquery',
                                        'screenName': screenName,
                                        'startDate': startDate,
                                        'endDate': endDate,
                                        'teamId': teamId,
                                        'quickFilter': editWeeklyQuickFilter
                                    }
                                    localStorage.setItem('editWeeklyFilter', JSON.stringify(postData));
                                    $('#topFilterName').val(filterDetails.Description);
                                    $('#selFilterType').val(filterDetails.isPublic ? 'public' : 'private');
                                    $('#selFilterId').val(filterDetails.ID);
                                    var filterNameForDisplay = filterDetails.Description;
                                    if(filterNameForDisplay!=null && filterNameForDisplay.length > 15){
                                        $('#filterName').html('<span title="'+filterNameForDisplay+'"> '+filterNameForDisplay.substr(0,15)+'...</span>');
                                    } else {
                                        $('#filterName').html('<span title="'+filterNameForDisplay+'"> '+filterNameForDisplay+'</span>');
                                    }
                                    $('#clearFilterIcon').show();
                                    $("#filterName").trigger("click");
                                    if($('#filterDropdown').css('display') == 'block'){
                                        $("#filterName").trigger("click");
                                    }
                                    if((SkillFilter == '&') && (Skill != null)){
                                        $('#filterAndSkill').val(Skill);
                                    } else {
                                        $('#filterAndSkill').val('');
                                    }
                                    if((DutyLabelFilter == '&') && (DutyLabel != null)){
                                        $('#filterAndDutyLabel').val(DutyLabel);
                                    } else {
                                        $('#filterAndDutyLabel').val('');
                                    }
                                    if(initialLoad == 'Yes'){
                                        reloadeditweeklygrid('','','','','ALL');
                                    } else {
                                        applyEditWeeklyFilter();
                                    }
                                    return true;
                                }
								if(screenName == 'EditWeeklyRota'){
                                    let startDate = $('#startDate').val();
                                    let endDate = $('#endDate').val();
                                    var postData = {
                                        'StaffNameFilter': StaffNameFilter,
                                        'StaffName': StaffName,
                                        'SortCodeFilter': SortCodeFilter,
                                        'SortCode': SortCode,
                                        'CostCodeFilter': CostCodeFilter,
                                        'CostCode': CostCode,
                                        'SkillFilter': SkillFilter,
                                        'Skill': Skill,
                                        'DutyFilter': DutyFilter,
                                        'Duty': Duty,
                                        'JobNameFilter': JobNameFilter,
                                        'JobName': JobName,
                                        'JobLabelFilter': JobLabelFilter,
                                        'JobLabel': JobLabel,
                                        'DutyLabelFilter': DutyLabelFilter,
                                        'DutyLabel': DutyLabel,
                                        'SortOrder': SortOrder,
                                        'AdditionalTeamsFilter': AdditionalTeamsFilter,
                                        'AdditionalTeams': AdditionalTeams,
                                        'andMatch': andMatch,
                                        'action':'createquery',
                                        'screenName': screenName,
                                        'startDate': startDate,
                                        'endDate': endDate,
                                        'teamId': teamId
                                    }
                                    $('*').qtip('hide');
                                    var skillFilterDaily='';
                                    var dutyFilterDaily='';                                   
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/allocations-editweekly-rota.php",
                                        data: {
                                            'teamId': teamId,
                                            'queryStr': '',
                                            'queryStr2': '',
                                            'orderStr': SortOrder,
                                            'type': 'filterapplied',
                                            'selFilterType': filterDetails.isPublic ? 'public' : 'private',
                                            'selFilterId': filterDetails.ID,
                                            'isShifttoCheck': isShifttoCheck,
                                            'WeekNumber':viewWeeklyWeekNum,
                                            'skillFilterDaily': skillFilterDaily,
                                            'dutyFilterDaily': dutyFilterDaily,
                                            'requestval':requestrota
                                        },
                                        beforeSend: function (jqXHR, settings){
                                            $('#loading').hide();
                                        },
                                        success: function (data,status) { 
                                            $('#content').html(data);
                                            var filterNameForDisplay = filterDetails.Description;
                                            if(filterNameForDisplay!=null && filterNameForDisplay.length > 16){
                                                $('#filterName').html('<span class="qtip-hover" title="'+filterNameForDisplay+'"> '+filterNameForDisplay.substr(0,16)+'...</span>');
                                            } else {
                                                $('#filterName').html('<span class="qtip-hover" title="'+filterNameForDisplay+'"> '+filterNameForDisplay+'</span>');
                                            }
                                            $('#clearFilterIcon').show();
                                            getFilterDetails(filterDetails.ID, 1, 'EditWeeklyRota');
                                        }
                                    });
                                    return true;
                                }
                            }
                        });
                    } else {
                        customAlert('Error in saving the filter');
                    }
                }
            });
        } else {
            var editWeeklyQuickFilter = '';
            if(screenName == 'EditWeeklyNameFilter' || screenName == 'EditWeekly') {
                if($('#edit-weekly-name-quick-filter').length == 0) { // Loading page from rota view
                    if(((localStorage.getItem('EditWeeklyNameFilter') != '') && (localStorage.getItem('EditWeeklyNameFilter') != null))) {
                        var filterQuickSearch = {'teamId' : ''};
                        try {
                            filterQuickSearch = JSON.parse(localStorage.getItem('EditWeeklyNameFilter'));
                        } catch(e) {
                            filterQuickSearch = {'teamId' : ''};
                        }
                        
                        if(filterQuickSearch.teamId == $('#teamId').val()) {
                            editWeeklyQuickFilter = filterQuickSearch.filter;
                        }
                    }
                } else {
                    editWeeklyQuickFilter = $('#edit-weekly-name-quick-filter').val();
                }
            }
            var StaffNameFilter = (screenName == 'EditWeeklyNameFilter') || (editWeeklyQuickFilter != '') ? '*' : $('#StaffNameFilter').val();
            var StaffName = (screenName == 'EditWeeklyNameFilter') || (editWeeklyQuickFilter != '') ? editWeeklyQuickFilter : $('#StaffName').val();
            var SortCodeFilter = $('#SortCodeFilter').val();
            var SortCode = $('#SortCode').val();
            var CostCodeFilter = $('#CostCodeFilter').val();
            var CostCode = $('#CostCode').val();
            var SkillFilter = $('#SkillFilter').val();
            var Skill = $('#Skill').val();
			var DutyTime = $('#DutyTime').val();
			var DutyFilter = $('#DutyFilter').val();
            var Duty = $('#Duty').val();
            var JobNameFilter = $('#JobNameFilter').val();
            var JobName = $('#JobName').val();
            var JobLabelFilter = $('#JobLabelFilter').val();
            var JobLabel = $('#JobLabel').val();
    		var DutyLabelFilter = $('#DutyLabelFilter').val();
            var DutyLabel = $('#DutyLabel').val();
            var SortOrder = $('#SortOrder').val();
            var AdditionalTeamsFilter = $('#AdditionalTeamsFilter').val();
            var AdditionalTeams = $('#AdditionalTeams').val();
            var teamId = $('#teamId').val();
            var isShifttoCheck = $('#isShifttoCheck').val();
            var andMatch = 0;
            if($('#andFilter').prop('checked') == true){
                andMatch = 1;
            }
            var jobNameAll = 0;
            if($('#JobNameALL').prop('checked') == true){
                jobNameAll = 1;
            }

            var jobLabelAll = 0;
            if($('#JobLabelALL').prop('checked') == true){
                jobLabelAll = 1;
            }
            if(screenName == 'ViewDaily') {
                var intWeek = $('#intWeek').val();
                var intDay = $('#intDay').val();
                var rolepermission = $('#rolepermission').val();
                var startDate = $('#startDate').val();
                var endDate =  $('#endDate').val();
                var strCurrentDate = viewWeeklyWeekNum ?? $('#strCurrentDate').val();
				var DutyTimeFilter = $('#DutyTimeFilter').val();
				var DutyTime = $('#DutyTime').val();
                var DutyTimeNow = 0;
                if(DutyTime && DutyTime.indexOf('NOW') !== -1){
				    DutyTimeNow = 1;
                    let currentDate = '<?php echo date('Y-m-d');?>';
		           if ((strCurrentDate != currentDate) && (strCurrentDate!='')){
                     //if ((strCurrentDate == currentDate) || (startDate == currentDate))  {
						customAlert('Cannot move on another day because NOW option is selected in Dutytime filter.');
                        return false;
                       
                    } else {
						  DutyTime = parseInt(DutyTime.replace('NOW--',''));
                       
                    }
                }
    		    var postData = {
                    'StaffNameFilter': StaffNameFilter,
                    'StaffName': StaffName,
                    'SortCodeFilter': SortCodeFilter,
                    'SortCode': SortCode,
                    'CostCodeFilter': CostCodeFilter,
                    'CostCode': CostCode,
                    'SkillFilter': SkillFilter,
                    'Skill': Skill,
                    'DutyFilter': DutyFilter,
                    'Duty': Duty,
					'DutyTimeFilter': DutyTimeFilter,
                    'DutyTime': DutyTime,
                    'JobNameFilter': JobNameFilter,
                    'JobName': JobName,
                    'JobLabelFilter': JobLabelFilter,
                    'JobLabel': JobLabel,
    				'DutyLabelFilter': DutyLabelFilter,
                    'DutyLabel': DutyLabel,
                    'SortOrder': SortOrder,
                    'AdditionalTeamsFilter': AdditionalTeamsFilter,
                    'AdditionalTeams': AdditionalTeams,
                    'andMatch': andMatch,
                    'action':'createquery',
                    'screenName': screenName,
                    'intWeek': intWeek,
                    'intDay': intDay,
                    'intTeamID': teamId,
                    'rolepermission': rolepermission,
                    'startDate': startDate,
                    'endDate': endDate,
                    'jobNameAll': jobNameAll,
                    'jobLabelAll': jobLabelAll
                }
                if(initialLoad != 'Yes') {
                    applySaveddailyFilter(postData);
                }

                if(initialLoad == 'Yes' || SortOrder) {
                    $('*').qtip('hide');
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/allocations-daily.php",
                        data: {
                            'teamId': teamId,
                            'queryStr1': '',
                            'queryStr2': '',
                            'queryStr3': '',
                            'orderStr': SortOrder,
                            'skillFilterDaily': '',
                            'dutyTimeFilterDaily': '',
                            'dutyTimeFilterNowDaily': '',
                            'dutyFilterDaily': '',
                            'jobFilterDaily': '',
                            'screenName': screenName,
                            'date':strCurrentDate,
                            'jobNameAll':'',
                            'jobLabelAll': ''
                        },
                        beforeSend: function (jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (data,status) { 
                            $('#content').html(data);
                            $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                            $('#clearFilterIcon').show();
                            applyViewDailyFilter();
                        }
                    });
                } else {           
                    $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                    $('#clearFilterIcon').show();
                    $('#filterDropdown').hide();
                    applyViewDailyFilter();
                }
                return true;
            }
            if(screenName == 'ViewWeekly'){
                let intSWeekNumber = $('#intSWeekNumber').val();
                let intEWeekNumber = $('#intEWeekNumber').val();
                let intTeamIDs = $('#intTeamIDs').val();
                let intSortOrder = $('#intSortOrder').val();

                var postData = {
                    'StaffNameFilter': StaffNameFilter,
                    'StaffName': StaffName,
                    'SortCodeFilter': SortCodeFilter,
                    'SortCode': SortCode,
                    'CostCodeFilter': CostCodeFilter,
                    'CostCode': CostCode,
                    'SkillFilter': SkillFilter,
                    'Skill': Skill,
                    'DutyFilter': DutyFilter,
                    'Duty': Duty,
                    'JobNameFilter': JobNameFilter,
                    'JobName': JobName,
                    'JobLabelFilter': JobLabelFilter,
                    'JobLabel': JobLabel,
    				'DutyLabelFilter': DutyLabelFilter,
                    'DutyLabel': DutyLabel,
                    'SortOrder': SortOrder,
                    'AdditionalTeamsFilter': AdditionalTeamsFilter,
                    'AdditionalTeams': AdditionalTeams,
                    'andMatch': andMatch,
                    'action':'createquery',
                    'screenName': screenName,
                    'intSWeekNumber': intSWeekNumber,
                    'intEWeekNumber': intEWeekNumber,
                    'intTeamIDs': intTeamIDs,
                    'intSortOrder': intSortOrder,
                    'isShifttoCheck': isShifttoCheck
                }
                applySavedweeklyFilter(postData);
                if(initialLoad == 'Yes' || SortOrder || AdditionalTeams != null) {                    
                    $('*').qtip('hide');
                    var skillFilterDaily='';
                    var dutyFilterDaily='';
                    var jobFilterDaily='';                    
                    let dataPayLoad = {
                        'teamId': teamId,
                        'queryStr': '',
                        'queryStr2': '',
                        'queryStr3': '',                      
                        'orderStr': SortOrder,
                        'isShifttoCheck': isShifttoCheck,
                        'skillFilterDaily': skillFilterDaily,
                        'dutyFilterDaily': dutyFilterDaily,
                        'WeekNumber': viewWeeklyWeekNum != '' ? viewWeeklyWeekNum : $('#intWeekNumber').val(),
                        'additionalTeams' : AdditionalTeams
                    };
                    if(viewWeeklyWeekNum != ''){
                        dataPayLoad.WeekNumber = viewWeeklyWeekNum;
                    }
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/allocations-weekly.php",
                        data: dataPayLoad,
                        beforeSend: function (jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (data,status) { 
                            $('#content').html(data);
                            $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                            $('#clearFilterIcon').show();
                            applyViewWeeklyFilter();
                        }
                    });
                } else {
                    $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                    $('#clearFilterIcon').show();
                    applyViewWeeklyFilter();
                    filterToggle();
                }                
                return true;
            }
            if(screenName == 'MultiWeek'){
                let intSWeekNumber = $('#intSWeekNumber').val();
                let intEWeekNumber = $('#intEWeekNumber').val();
                let intTeamIDs = $('#intTeamIDs').val();
                var postData = {
                    'StaffNameFilter': StaffNameFilter,
                    'StaffName': StaffName,
                    'SortCodeFilter': SortCodeFilter,
                    'SortCode': SortCode,
                    'CostCodeFilter': CostCodeFilter,
                    'CostCode': CostCode,
                    'SkillFilter': SkillFilter,
                    'Skill': Skill,
                    'DutyFilter': DutyFilter,
                    'Duty': Duty,
                    'JobNameFilter': JobNameFilter,
                    'JobName': JobName,
                    'JobLabelFilter': JobLabelFilter,
                    'JobLabel': JobLabel,
    				'DutyLabelFilter': DutyLabelFilter,
                    'DutyLabel': DutyLabel,
                    'SortOrder': SortOrder,
                    'AdditionalTeamsFilter': AdditionalTeamsFilter,
                    'AdditionalTeams': AdditionalTeams,
                    'andMatch': andMatch,
                    'action':'createquery',
                    'screenName': screenName,
                    'intSWeekNumber': intSWeekNumber,
                    'intEWeekNumber': intEWeekNumber,
                    'intTeamIDs': intTeamIDs
                }
                applySavedweeklyFilter(postData);
                if(SortOrder || AdditionalTeams != null) {
                    $('*').qtip('hide');
                    var skillFilterDaily='';
                    var dutyFilterDaily='';
                    var jobFilterDaily='';
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/allocations-multi-week.php",
                        data: {
                            'teamId': teamId,
                            'queryStr': '',
                            'queryStr2': '',
                            'queryStr3':'',
                            'orderStr': SortOrder,
                            'skillFilterDaily': '',
                            'dutyFilterDaily': '',
                            'WeekNumber': viewWeeklyWeekNum != '' ? viewWeeklyWeekNum : $('#intWeekNumber').val(),
                            'additionalTeams' : AdditionalTeams
                        },
                        beforeSend: function (jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (data,status) { 
                            $('#content').html(data);
                            $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                            $('#clearFilterIcon').show();
                            applyViewMultiWeeklyFilter()
                        }
                    });
                } else {
                    $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                    $('#clearFilterIcon').show();
                    applyViewMultiWeeklyFilter();
                    filterToggle();
                }
                return true;
            }
            if(screenName == 'EditWeekly'){
                let startDate = $('#startDate').val();
                let endDate = $('#endDate').val();

                var postData = {
                    'StaffNameFilter': StaffNameFilter,
                    'StaffName': StaffName,
                    'SortCodeFilter': SortCodeFilter,
                    'SortCode': SortCode,
                    'CostCodeFilter': CostCodeFilter,
                    'CostCode': CostCode,
                    'SkillFilter': SkillFilter,
                    'Skill': Skill,
                    'DutyFilter': DutyFilter,
                    'Duty': Duty,
                    'JobNameFilter': JobNameFilter,
                    'JobName': JobName,
                    'JobLabelFilter': JobLabelFilter,
                    'JobLabel': JobLabel,
    				'DutyLabelFilter': DutyLabelFilter,
                    'DutyLabel': DutyLabel,
                    'SortOrder': SortOrder,
                    'AdditionalTeamsFilter': AdditionalTeamsFilter,
                    'AdditionalTeams': AdditionalTeams,
                    'andMatch': andMatch,
                    'action':'createquery',
                    'screenName': screenName,
                    'startDate': startDate,
                    'endDate': endDate,
                    'teamId': teamId,
                    'quickFilter': editWeeklyQuickFilter
                }

                localStorage.setItem('editWeeklyFilter', JSON.stringify(postData));
                $('#topFilterName').val('Filter Applied');
                $('#topFilterName').val('Filter Applied');
                $('#selFilterType').val('');
                $('#selFilterId').val('');
                $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                $('#clearFilterIcon').show();
                $("#filterName").trigger("click");
                if($('#filterDropdown').css('display') == 'block'){
                    $("#filterName").trigger("click");
                }
                if((SkillFilter == '&') && (Skill != null)){
                    $('#filterAndSkill').val(Skill.join(','));
                } else {
                    $('#filterAndSkill').val('');
                }
                if((DutyLabelFilter == '&') && (DutyLabel != null)){
                    $('#filterAndDutyLabel').val(DutyLabel.join(','));
                } else {
                    $('#filterAndDutyLabel').val('');
                }
                if(initialLoad == 'Yes'){
                    reloadeditweeklygrid('','','','','ALL');
                } else {
                    applyEditWeeklyFilter();
                }
                return true;
            }

            if(screenName == 'EditWeeklyNameFilter'){
                let startDate = $('#startDate').val();
                let endDate = $('#endDate').val();

                var postData = {
                    'StaffNameFilter': StaffNameFilter,
                    'StaffName': StaffName,
                    'SortCodeFilter': SortCodeFilter,
                    'SortCode': SortCode,
                    'CostCodeFilter': CostCodeFilter,
                    'CostCode': CostCode,
                    'SkillFilter': SkillFilter,
                    'Skill': Skill,
                    'DutyFilter': DutyFilter,
                    'Duty': Duty,
                    'JobNameFilter': JobNameFilter,
                    'JobName': JobName,
                    'JobLabelFilter': JobLabelFilter,
                    'JobLabel': JobLabel,
    				'DutyLabelFilter': DutyLabelFilter,
                    'DutyLabel': DutyLabel,
                    'SortOrder': SortOrder,
                    'AdditionalTeamsFilter': AdditionalTeamsFilter,
                    'AdditionalTeams': AdditionalTeams,
                    'andMatch': andMatch,
                    'action':'createquery',
                    'screenName': screenName,
                    'startDate': startDate,
                    'endDate': endDate,
                    'teamId': teamId,
                    'quickFilter': editWeeklyQuickFilter
                }                
                if((SkillFilter == '&') && (Skill != null)){
                    $('#filterAndSkill').val(Skill.join(','));
                } else {
                    $('#filterAndSkill').val('');
                }
                if((DutyLabelFilter == '&') && (DutyLabel != null)){
                    $('#filterAndDutyLabel').val(DutyLabel.join(','));
                } else {
                    $('#filterAndDutyLabel').val('');
                }
                localStorage.setItem('EditWeeklyNameFilter', JSON.stringify({'teamId' : teamId, 'filter': editWeeklyQuickFilter}));
                applyEditWeeklyFilter();
                return true;
            }
			
			if(screenName == 'EditWeeklyRota'){
                let startDate = $('#startDate').val();
                let endDate = $('#endDate').val();

                var postData = {
                    'StaffNameFilter': StaffNameFilter,
                    'StaffName': StaffName,
                    'SortCodeFilter': SortCodeFilter,
                    'SortCode': SortCode,
                    'CostCodeFilter': CostCodeFilter,
                    'CostCode': CostCode,
                    'SkillFilter': SkillFilter,
                    'Skill': Skill,
                    'DutyFilter': DutyFilter,
                    'Duty': Duty,
                    'JobNameFilter': JobNameFilter,
                    'JobName': JobName,
                    'JobLabelFilter': JobLabelFilter,
                    'JobLabel': JobLabel,
    				'DutyLabelFilter': DutyLabelFilter,
                    'DutyLabel': DutyLabel,
                    'SortOrder': SortOrder,
                    'AdditionalTeamsFilter': AdditionalTeamsFilter,
                    'AdditionalTeams': AdditionalTeams,
                    'andMatch': andMatch,
                    'action':'createquery',
                    'screenName': screenName,
                    'startDate': startDate,
                    'endDate': endDate,
                    'teamId': teamId
                }
                if(initialLoad != 'Yes') {
                    applySavedEditweeklyRotaFilter(postData);
                }				
                if(initialLoad == 'Yes' || SortOrder) {
                    $('*').qtip('hide');
                    var skillFilterDaily='';
                    var dutyFilterDaily='';
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/allocations-editweekly-rota.php",
                        data: {
                            'teamId': teamId,
                            'queryStr': '',
                            'orderStr': SortOrder,
                            'selFilterType': '',
                            'skillFilterDaily': skillFilterDaily,
                            'dutyFilterDaily': dutyFilterDaily,
                            'WeekNumber': viewWeeklyWeekNum != '' ? viewWeeklyWeekNum : $('#intWeekNumber').val()
                        },
                        beforeSend: function (jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (data,status) { 
                            $('#content').html(data); 
                            $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                            $('#clearFilterIcon').show();
                            applyEditRotaWeeklyFilter();
                        }
                    });
                } else {
                    $('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
                    $('#clearFilterIcon').show();
                    applyEditRotaWeeklyFilter();
                    filterToggle();
                }
                return true;
            }
        }
    }

    function getAvailableAdditionalTeam(addtionalTeams) {
        let availableAdditionaTeams = [];
        $('#AdditionalTeams option').each(function(index, element) {
            if (addtionalTeams.includes($(element).val())) {
                availableAdditionaTeams.push($(element).val());
            }
        });
        return availableAdditionaTeams;
    }

    function filterDropDownHtml(filterData){
        var filterDDHtml = '';
        filterDDHtml +='<option value="">--Select Filter--</option>';
        $( filterData ).each(function( ind,val ) {
            filterDDHtml +='<option value="'+val.ID+'">'+val.FilterName+'</option>';
        });
        return filterDDHtml;
    }

    function applyGoFilter(screenName){
        if($('#publicFilterId').val() != '' || $('#privateFilterId').val() != ''){
            applyViewFilter(screenName);
        }
    }

    function clearViewFilter(screenName, teamId, isShifttoCheck = 0){
        var postData = {
            'action': 'clearfilter',
            'screenName': screenName,
            'teamId': teamId,
            'isShifttoCheck': isShifttoCheck
        }

        $.ajax({
            type: "post",
            url: "/components/filters/filter-process.php",
            data: postData,
            beforeSend: function (jqXHR, settings){
                $('#loading').hide();
            },
            success: function (dataClearFilter,statusClearFilter) { 
                var returnDataClearFilter = $.parseJSON(dataClearFilter);
                if(returnDataClearFilter.status){
                    if(screenName == 'ViewDaily'){
                        deltedSaveddailyFilter();
                        ShowDailyAllocations(teamId, $('#strCurrentDate').val());
                    }
                    if(screenName == 'ViewWeekly'){
                        deltedSavedweeklyFilter();
                        var SweekNum = $("#intSWeekNumber").val();
                        ShowAllocations(teamId, SweekNum, '', isShifttoCheck);
                    }
                    if(screenName == 'MultiWeek'){
                      deltedSavedweeklyFilter();
                      ShowAllocationsMulti(teamId);
                    }
                    if(screenName == 'EditWeekly'){
                        if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                            localStorage.removeItem('editWeeklyFilter');
                        }
                        $('#queryString').val('');
                        $('#queryOrder').val('');
                        $("#selFilterType").val('');
                        $("#selFilterId").val('');
                        $('#clearFilterIcon').hide();
                        $('#filterName').html('<span title="Filters"> Filters</span>');
                        $("#filterName").trigger("click");
                        if($('#filterDropdown').css('display') == 'block'){
                          $("#filterName").trigger("click");
                        }
                        clearFilterData();
                        $('.filter-hide-row').removeClass('filter-hide-row');
                        if($('#edit-weekly-name-quick-filter').val() != '') {
                            callNameFilter();
                        }
                        
                        let shiftCountGridShowHide = $('#showCountBlock').css('display');
                        if (shiftCountGridShowHide || shiftCountGridShowHide !== 'none') {
							if($('#showHideCount').prop('checked') == true)
							{
								reloadShiftCountingGrid();
							}
                        }
                    }
					if (screenName == 'EditWeeklyRota'){
					    deletedSavedEditweeklyRotaFilter();
						ShowRotaInEditWeeklyAllocations(teamId);
                    }
                }
            }
        });
    }
</script>
