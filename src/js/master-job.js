$(document).ready(function () {
    let jobiddrag = null;
    let jobsttimedrag = null;
    let jobendtimedrag = null;
    let teamiddrag = null;
    let canview = 0;
    let cancreate = 0;
    let canmodify = 0;
    let candelete = 0;

    $.ajax({
        url: "page-includes/master-jobs/userPermissionForJobs.php",
        type: "POST",
        dataType: "json",
        async: false,
        data: {
            id: 0
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

    $(".toggle").click(function (event) {
        var jobid = $(this).attr('jobid');
        var idx = $('.toggle').index(this);
        if (idx == 0) {
            $tr = (idx) ? $('.toggle').eq(idx - 1).closest('tr') : $(this).closest('tr').next('tr');
        } else {
            $tr = (idx) ? $('.toggle').eq(idx - 1).closest('tr') : $(this).closest('tr').prev('tr');
        }
        $td = $tr.find('td:nth-child(1)');

        if (typeof ($td.attr('jobid')) == "undefined") {
            var altid = 0;
        } else {
            var altid = $td.attr('jobid');
        }
        $.post("page-includes/master-jobs/toggle-inactive.php", {
                JobID: jobid
            },
            function (data, status) {
                ListJobs(altid, 1)
            });
    });

    //Context menu
    if (cancreate == 1 && canmodify == 1 && candelete == 1) {
        $(function () {
            $.contextMenu({
                selector: '.master-jobs-context-menu',
                items: {
                    "Add": {
                        name: "Add",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            newMasterJob();
                        }
                    },
                    "copy": {
                        name: "Copy",
                        icon: "copy",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            CopyJob(jobid);
                        }
                    },
                    "edit": {
                        name: "Edit",
                        icon: "edit",
                        // disabled: canmodify == 1 ? false : true,
                        display: false,
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            EditJob(jobid);
                        }
                    },
                    "delete": {
                        name: "Delete",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            var mduty = options.$trigger.attr("mduty");
                            var jobName = options.$trigger.attr("jobName");
                            DeleteJob(jobid, mduty, jobName);
                        }
                    }
                }
            });
        });
    } else if (cancreate == 1 && canmodify == 1 && candelete == 0) {
        $(function () {
            $.contextMenu({
                selector: '.master-jobs-context-menu',
                items: {
                    "Add": {
                        name: "Add",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            newMasterJob();
                        }
                    },
                    "copy": {
                        name: "Copy",
                        icon: "copy",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            CopyJob(jobid);
                        }
                    },
                    "edit": {
                        name: "Edit",
                        icon: "edit",
                        // disabled: canmodify == 1 ? false : true,
                        display: false,
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            EditJob(jobid);
                        }
                    }
                }
            });
        });
    }
    else if (cancreate == 1 && canmodify == 0 && candelete == 0) {
        $(function () {
            $.contextMenu({
                selector: '.master-jobs-context-menu',
                items: {
                    "Add": {
                        name: "Add",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            newMasterJob();
                        }
                    },
                    "copy": {
                        name: "Copy",
                        icon: "copy",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            CopyJob(jobid);
                        }
                    }
                }
            });
        });
    }
    else if (cancreate == 0 && canmodify == 1 && candelete == 0)
    {
        $(function () {
            $.contextMenu({
                selector: '.master-jobs-context-menu',
                items: {
                    "edit": {
                        name: "Edit",
                        icon: "edit",
                        disabled: canmodify == 1 ? false : true,
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            EditJob(jobid);
                        }
                    }
                }
            });
        });
    } else if (cancreate == 0 && canmodify == 0 && candelete == 1) {
        $(function () {
            $.contextMenu({
                selector: '.master-jobs-context-menu',
                items: {
                    "delete": {
                        name: "Delete",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            var mduty = options.$trigger.attr("mduty");
                            var jobName = options.$trigger.attr("jobName");
                            DeleteJob(jobid, mduty, jobName);
                        }
                    }
                }
            });
        });

    }

    $(function () {
        if (canview == 1 && cancreate == 1 && canmodify == 1 && candelete == 1) {
			if((document.cookie.indexOf('masterdutyteams') > -1) && ($.cookie('masterdutyteams') != ''))
			{
				$('#addBtnContainer').show();
				$('#addJobBtnContainer').show();
			}else
			{
				$('#addBtnContainer').hide();
				$('#addJobBtnContainer').hide();
			}
            $.contextMenu({
                selector: '.listduty-context-menu-jobs',
                items: {
                    "view": {
                        name: "View Duty Details",
                        icon: "comment",
                        disabled: canview == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            DutyDetails(dutyid, 0);
                        }
                    },
                    "NewDuty": {
                        name: "New Duty",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            NewDuty();
                        }
                    },

                    "edit": {
                        name: "Edit Duty Details",
                        icon: "edit",
                        disabled: canmodify == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            EditDuty(dutyid, 0);
                        }
                    },
                    "copy": {
                        name: "Copy Duty",
                        icon: "copy",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            CopyDuty(dutyid, 1);
                        }
                    },
                    "delete": {
                        name: "Delete Duty",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            var prevdutyid = options.$trigger.attr("prevdutyid");
                            var lastmoddate = '';
                            DeleteDuty(dutyid, prevdutyid, lastmoddate, 1);
                        }
                    },
                    "AddJobToDuty": {
                        name: "Add Job to Duty",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            var startTime = $(this).attr("dst");
                            var endTime = options.$trigger.attr("dend");
                            AddJobToDuty(dutyID, startTime, endTime);
                        }
                    },/*
                    "Addtounallocated": {
                        name: "Add to Unallocated Duties",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            var teamid = $(this).attr("teamid");
                           
                            AddToUnallocatedDuty(dutyID, teamid);
                        }
                    },*/
                    "CopyToAllDuties": {
                        name: "Copy to All Duties",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            var teamid = $(this).attr("teamid");
                           
                            CopyToAllDuties(dutyID, teamid);
                        }
                    },
                    "DutyHistory": {
                        name: "History",
                        icon: "history",
                        disabled: canview == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            History(dutyID);
                        }
                    }
                }
            });
        } else if (canview == 1 && cancreate == 1 && canmodify == 1 && candelete == 0) {
			if((document.cookie.indexOf('masterdutyteams') > -1) && ($.cookie('masterdutyteams') != ''))
			{
				$('#addBtnContainer').show();
				$('#addJobBtnContainer').show();
			}else
			{
				$('#addBtnContainer').hide();
				$('#addJobBtnContainer').hide();
			}
            $.contextMenu({
                selector: '.listduty-context-menu-jobs',
                items: {
                    "view": {
                        name: "View Duty Details",
                        icon: "comment",
                        disabled: canview == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            DutyDetails(dutyid, 0);
                        }
                    },
                    "NewDuty": {
                        name: "New Duty",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            NewDuty();
                        }
                    },

                    "edit": {
                        name: "Edit Duty Details",
                        icon: "edit",
                        disabled: canmodify == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            EditDuty(dutyid, 0);
                        }
                    },
                    "copy": {
                        name: "Copy Duty",
                        icon: "copy",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            CopyDuty(dutyid, 1);
                        }
                    },
                    "AddJobToDuty": {
                        name: "Add Job to Duty",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            var startTime = $(this).attr("dst");
                            var endTime = options.$trigger.attr("dend");
                            AddJobToDuty(dutyID, startTime, endTime);
                        }
                    },
                    "DutyHistory": {
                        name: "History",
                        icon: "history",
                        disabled: canview == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            History(dutyID);
                        }
                    }
                }
            });
        } else if (canview == 1 && cancreate == 1 && canmodify == 0 && candelete == 1) {
			if((document.cookie.indexOf('masterdutyteams') > -1) && ($.cookie('masterdutyteams') != ''))
			{
				$('#addBtnContainer').show();
				$('#addJobBtnContainer').show();
			}else
			{
				$('#addBtnContainer').hide();
				$('#addJobBtnContainer').hide();
			}
            $.contextMenu({
                selector: '.listduty-context-menu-jobs',
                items: {
                    "view": {
                        name: "View Duty Details",
                        icon: "comment",
                        disabled: canview == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            DutyDetails(dutyid, 0);
                        }
                    },
                    "NewDuty": {
                        name: "New Duty",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            NewDuty();
                        }
                    },
                    "copy": {
                        name: "Copy Duty",
                        icon: "copy",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            CopyDuty(dutyid, 1);
                        }
                    },
                    "delete": {
                        name: "Delete Duty",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            var prevdutyid = options.$trigger.attr("prevdutyid");
                            var lastmoddate = '';
                            DeleteDuty(dutyid, prevdutyid, lastmoddate, 1);
                        }
                    },
                    "AddJobToDuty": {
                        name: "Add Job to Duty",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            var startTime = $(this).attr("dst");
                            var endTime = options.$trigger.attr("dend");
                            AddJobToDuty(dutyID, startTime, endTime);
                        }
                    },
                    "DutyHistory": {
                        name: "History",
                        icon: "history",
                        disabled: canview == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            History(dutyID);
                        }
                    }
                }
            });
        } else if (canview == 1 && cancreate == 0 && canmodify == 1 && candelete == 1) {
			$('#addBtnContainer').hide();
			$('#addJobBtnContainer').hide();
            $.contextMenu({
                selector: '.listduty-context-menu-jobs',
                items: {
                    "view": {
                        name: "View Duty Details",
                        icon: "comment",
                        disabled: canview == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            DutyDetails(dutyid, 0);
                        }
                    },
                    "edit": {
                        name: "Edit Duty Details",
                        icon: "edit",
                        disabled: canmodify == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            EditDuty(dutyid, 0);
                        }
                    },
                    "delete": {
                        name: "Delete Duty",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            var prevdutyid = options.$trigger.attr("prevdutyid");
                            var lastmoddate = '';
                            DeleteDuty(dutyid, prevdutyid, lastmoddate, 1);
                        }
                    },
                    "DutyHistory": {
                        name: "History",
                        icon: "history",
                        disabled: canview == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            History(dutyID);
                        }
                    }
                }
            });
        } else if (canview == 1 && cancreate == 1 && canmodify == 0 && candelete == 0) {
			if((document.cookie.indexOf('masterdutyteams') > -1) && ($.cookie('masterdutyteams') != ''))
			{
				$('#addBtnContainer').show();
				$('#addJobBtnContainer').show();
			}else
			{
				$('#addBtnContainer').hide();
				$('#addJobBtnContainer').hide();
			}
            $.contextMenu({
                selector: '.listduty-context-menu-jobs',
                items: {
                    "view": {
                        name: "View Duty Details",
                        icon: "comment",
                        disabled: canview == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            DutyDetails(dutyid, 0);
                        }
                    },
                    "NewDuty": {
                        name: "New Duty",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            NewDuty();
                        }
                    },

                    "copy": {
                        name: "Copy Duty",
                        icon: "copy",
                        disabled: cancreate == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            CopyDuty(dutyid, 1);
                        }
                    },
                    "AddJobToDuty": {
                        name: "Add Job to Duty",
                        icon: "add",
                        disabled: cancreate == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            var startTime = $(this).attr("dst");
                            var endTime = options.$trigger.attr("dend");
                            AddJobToDuty(dutyID, startTime, endTime);
                        }
                    },
                    "DutyHistory": {
                        name: "History",
                        icon: "history",
                        disabled: canview == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            History(dutyID);
                        }
                    }
                }
            });
        } else if (canview == 1 && cancreate == 0 && canmodify == 1 && candelete == 0) {
			$('#addBtnContainer').hide();
			$('#addJobBtnContainer').hide();
            $.contextMenu({
                selector: '.listduty-context-menu-jobs',
                items: {
                    "view": {
                        name: "View Duty Details",
                        icon: "comment",
                        disabled: canview == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            DutyDetails(dutyid, 0);
                        }
                    },
                    "edit": {
                        name: "Edit Duty Details",
                        icon: "edit",
                        disabled: canmodify == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            EditDuty(dutyid, 0);
                        }
                    },
                    "delete": {
                        name: "Delete Duty",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            var prevdutyid = options.$trigger.attr("prevdutyid");
                            var lastmoddate = '';
                            DeleteDuty(dutyid, prevdutyid, lastmoddate, 1);
                        }
                    },
                    "DutyHistory": {
                        name: "History",
                        icon: "history",
                        disabled: canview == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            History(dutyID);
                        }
                    }
                }
            });
        } else if (canview == 1 && cancreate == 0 && canmodify == 0 && candelete == 1) {
			$('#addBtnContainer').hide();
			$('#addJobBtnContainer').hide();
            $.contextMenu({
                selector: '.listduty-context-menu-jobs',
                items: {
                    "view": {
                        name: "View Duty Details",
                        icon: "comment",
                        disabled: canview == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            DutyDetails(dutyid, 0);
                        }
                    },
                    "delete": {
                        name: "Delete Duty",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            var prevdutyid = options.$trigger.attr("prevdutyid");
                            var lastmoddate = '';
                            DeleteDuty(dutyid, prevdutyid, lastmoddate, 1);
                        }
                    },
                    "DutyHistory": {
                        name: "History",
                        icon: "history",
                        disabled: canview == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            History(dutyID);
                        }
                    }
                }
            });
        } else {
			$('#addBtnContainer').hide();
			$('#addJobBtnContainer').hide();
            $.contextMenu({
                selector: '.listduty-context-menu-jobs',
                items: {
                    "view": {
                        name: "View Duty Details",
                        icon: "comment",
                        disabled: canview == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var dutyid = options.$trigger.attr("dutyid");
                            DutyDetails(dutyid, 0);
                        }
                    },
                    "DutyHistory": {
                        name: "History",
                        icon: "history",
                        disabled: canview == 1 ? false : true,
                        callback: function (key, options) {
                            var dutyID = $(this).attr("dutyid");
                            History(dutyID);
                        }
                    }
                }
            });
        }

        if (candelete == 1 && canmodify == 1) {
            $.contextMenu({
                selector: '.dutyjob-context-menu',
                items: {
                    "deleteJobDuty": {
                        name: "Delete Job From Duty",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            var mduty = options.$trigger.attr("dutyid");
                            var dutyname = $(this).text();
                            deleteJobDuty(jobid, mduty, dutyname);
                        }
                    },
                    "edit": {
                        name: "Edit",
                        icon: "edit",
                        disabled: canmodify == 1 ? false : true,
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            var mduty = options.$trigger.attr("dutyid");
                            EditJob(jobid, mduty, 'DUTY');
                        }
                    }
                }
            });
        } else if (candelete == 1 && canmodify == 0) {
            $.contextMenu({
                selector: '.dutyjob-context-menu',
                items: {
                    "deleteJobDuty": {
                        name: "Delete Job From Duty",
                        icon: "delete",
                        disabled: candelete == 1 ? false : true,
                        // superseeds "global" callback
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            var mduty = options.$trigger.attr("dutyid");
                            var dutyname = $(this).text();
                            deleteJobDuty(jobid, mduty, dutyname);
                        }
                    }
                }
            });
        } else if (candelete == 0 && canmodify == 1) {
            $.contextMenu({
                selector: '.dutyjob-context-menu',
                items: {
                    "edit": {
                        name: "Edit",
                        icon: "edit",
                        disabled: canmodify == 1 ? false : true,
                        callback: function (key, options) {
                            var jobid = options.$trigger.attr("jobid");
                            EditJob(jobid);
                        }
                    }
                }
            });
        }
    });

    $(".duty-list").click(function (e) {
        if ($.fn.dataTable.isDataTable('#jobsassigned')) {
            $('#jobsassigned').DataTable().destroy();
        }
        $(".duty-list").removeClass("chooseDuty");
        $(this).addClass('chooseDuty');
        var dutyname = $(this).attr('title');
        var dutyStart = $(this).attr('dutyStart');
        var dutyend = $(this).attr('dutyEnd');
		var dutyEndArr = dutyend.split(':');
		if(dutyEndArr[0] > 23)
		{
			dutyend = dutyEndArr[0] - 24 + ':' + dutyEndArr[1];
		}
        $("#dutyStDisp").val(dutyStart);
        $("#dutyEndDisp").val(dutyend);
        $("#dutyName").val(dutyname);
        var id = $(this).attr("id");
        $.ajax({
            url: "page-includes/master-jobs/bind-jobs-in-table.php",
            type: "POST",
            dataType: "html",
            data: {
                'dutyid': id
            },
            success: function (data) {
                $("#jobsassigned tbody").html(data);
            }
        });
    });

    $('.dragmyjob').on('dragstart', function (event) {
        let jobid = $(this).attr('jobid');
        let jobsttime = $(this).attr('jobsttime');
        let jobendtime = $(this).attr('jobendtime');
        event.stopImmediatePropagation();
        jobiddrag = jobid;
        jobsttimedrag = jobsttime;
        jobendtimedrag = jobendtime;
        dragStart(event);
    });

    $('.dropeventscall').on('dragover', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
		let dutyid = event.target.getAttribute("dutyid");	
        $("#duty_" + dutyid).css('border', "1px solid red");
    });

	$('.dropeventscall').on('dragleave', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        let dutyid = event.target.getAttribute("dutyid");
        $("#duty_" + dutyid).css('border', 'none');
    });

    $('.dropeventscall').on('dragleave', function (event) {	
        event.stopImmediatePropagation();	
        event.preventDefault();	
        let dutyid = event.target.getAttribute("dutyid");	
        $("#duty_" + dutyid).css('border', 'none');	
    });
	$(document).on('click','#js_saveUnallocted', function () {
    
        AddUnAlloctedDuties();
    });
	$(document).on('click','#js_saveCopyForm', function () {
    
        saveCopyToAllDutiesForm();
    });
});

function EditJob(id, dutyid=0, requestFrom='JOB') {
    $.post("page-includes/master-jobs/edit-job.php", {
            id: id,
            dutyid: dutyid,
			requestFrom:requestFrom
        },
        function (data, status) {
            $.facebox(data);
        });
}

function CopyJob(id) {
    $.ajax({
        type: 'POST', url: 'page-includes/master-jobs/copy-master-job.php',
        data: {
            id: id
        },
        success: function (data) {
            NewData = JSON.parse(data);
            ListJobs(0, 0);
            $.facebox.close();
        }
    });
}

function DeleteJob(id, mduty, jobName) {
    var jobid = id;
    var mduty = mduty;
    var jobName = jobName;
	customConfirm('Are you sure you want to delete this job ' + jobName + '?',function(){
			if(mduty > 0){
				let htmlText = '<div style="width: 600px"><table id="customConfirm2" class="smalltable bluetable" width="100%" role="presentation"><thead><tr><th colspan="5">Please Confirm</th></tr></thead><tbody><tr><td style="padding:10px;">This job is being used in one or more Master Duties. Are you sure you want to delete this job?</td></tr><tr style="text-align:right;"><td><input class="yes2" name="yes2" id="yes2" type="submit" value="Yes" onclick="DeleteJobSubFunc('+jobid+');parent.$.facebox.close();" >&nbsp;&nbsp;&nbsp;&nbsp<input name="no2" id="no2" type="submit" value="No" onClick="parent.$.facebox.close();"></td></tr></tbody></table></div>';
				setTimeout(function() {$.facebox(htmlText);}, 1000);
			} else {
				DeleteJobSubFunc(jobid);
			}
		},
		function() {
			return false;
		}
	);
}
function DeleteJobSubFunc(jobid)
{
	$.post("page-includes/master-jobs/toggle-inactive.php", {
		jobid: jobid
	},
	function (data, status) {
		ListJobs(0, 0);
        ListMasterDuties(0, 0);
	});
	
}
function AddJobToDuty(id, startTime, endTime) {
    $.post("page-includes/master-jobs/add-job-from-duty.php", {
        dutyID: id,
        startTime: startTime,
        endTime: endTime
    },
    function (data, status) {
        $.facebox(data);
    });
}

function History(id) {
    $.post("page-includes/master-duties/dutyHistory.php", {
            dutyID: id
        },
        function (data, status) {
            $.facebox(data);
        });
}

function deleteJobDuty(jobID, masterDutyID, dutyName) {
    var jobid = jobID;
    var mduty = masterDutyID;
    var jobName = dutyName;
	customConfirm('Are you sure you want to delete this job from this master duty?',function() {
			$.ajax({
				type: 'POST',
				url: 'page-includes/master-jobs/delete-job-from-duty.php',
				async:true,
				data: {
					jobid: jobid,
					mduty: mduty
				},
				success: function (data) {
					let datanew = JSON.parse(data);
					if (datanew.status == 1) {
						ListJobs(0, 0);
						let jobIdContainer	= 'd' + mduty + 'j' + jobid;
						$('#'+jobIdContainer).css("display", "none");
					} else {
						customAlert(datanew.strstatus, 1000);
						return;
					}
				}
			});
		},
		function() {
			return false;
		}
	);
	return false;
}

function newMasterJob(id) {
    var ddlRotaTeams = $('#ddlRotaTeams').val();
    $.post("page-includes/master-jobs/add-newjob.php", {
        id: id,
        listtype: 1,
        ddlRotaTeams: ddlRotaTeams
    },
    function (data, status) {
        $.facebox(data);
    });
}

function GoToRow(currid) {
    if (currid != 0) {
        var strid = "#" + currid;
        $(".dataTables_scrollBody").scrollTop($(strid).offset().top - $(".dataTables_scrollBody").height());
        $(strid).addClass("selected");
    }
}

function dragStart(event) {
    jobiddrag = event.target.getAttribute("jobid");
    jobsttimedrag = event.target.getAttribute("jobsttime");
    jobendtimedrag = event.target.getAttribute("jobendtime");
    teamiddrag = event.target.getAttribute("teamid");
}

function drop(event) {
    let d = dragelements();
    let dutystart = event.target.getAttribute("dutystart");
    let duty_end = event.target.getAttribute("duty_end");
    let dutyid = event.target.getAttribute("dutyid");
    let jobid = jobiddrag;
    let teamid = teamiddrag;
    let jobsttime = jobsttimedrag;
    let jobendtime = jobendtimedrag;
    $("#duty_" + dutyid).css('border', "1px solid green");
    let dt;
    let assignteam1;
	//If Job is assigned Before the Duty Time
	if (parseInt(jobsttime) < parseInt(dutystart) && parseInt(duty_end) < 86400
		&& (((parseInt(jobsttime) + 86400) > parseInt(duty_end))
		|| ((parseInt(jobendtime) + 86400) < parseInt(dutystart)))) {
		customAlertByModel("Warning: the job you are trying to add or edit will produce a conflict with either another job or the duty start/end time. Please try again.");
	}
	//End before

	//After Duty Time
	else if (parseInt(jobendtime) > parseInt(duty_end)) {
		let flag = 'After';
		let msg = "Warning! the job you are trying to add is after the duty time . Please click on to continue to amend this job in duty.";
		$mes = '<table class="smalltable bluetable" width="100%"><tr><td colspan="2">Warning! The job you are trying to add is after the duty time. Please click ok to continue to amend this job in dutyTo add is before the duty time.</td></tr></td></tr></td></tr><tr><td ><input style="float:right;" type="button" name="ok" id="afterAdd" value="OK">&nbsp;&nbsp;<input  style="float:right;" type="button" name="cancel" id="cancelafter" value="Cancel"></td></tr></table>';
		$.facebox($mes);
		$("#afterAdd").click(function (e) {
			$.ajax({
				type: 'POST',
				url: 'page-includes/master-jobs/update-masteduties-masterjobs.php',
				data: {startTime: jobsttime, endTime: jobendtime, duty_id: dutyid, jobid: jobid, flag: flag},
				success: function (data) {
					data = JSON.parse(data);
					if (data.status == 1 || data.status == "1" ) {
						ListMasterDuties(0, 0);
						$.facebox.close();
					} else {
						customAlertByModel(data.strStatus);
					}
				}
			});

		});
		$("#cancelafter").click(function (e) {
			$.facebox.close();
		});
	}
	//End After Duty Time
	else {
		$.ajax({
			async:false,
			type: 'POST',
			dataType: "json",
			url: 'page-includes/master-jobs/job-within-duties.php',
			data: {
				startTime: jobsttime,
				endTime: jobendtime,
				duty_id: dutyid,
				jobid: jobid
			},
			success: function (data) {
				if (data.status == 1 || data.status == "1" ) {
					ListMasterDuties(0, 0);
                    $("#jobsassigned tbody").html('');
                    $("#dutyStDisp").val('');
                    $("#dutyEndDisp").val('');
                    $("#dutyName").val('');
					$.facebox.close();
				} else {
					customAlertByModel(data.strStatus);
				}
			}
		});
	}

    $("#duty_" + dutyid).css('border', "none");
}

function dragelements() {
    let jobid = $(this).attr('jobid');
    let data = [{'jobid': jobid}];
    return data;
}

function DeleteDuty(dutyid, prevdutyid, lastmoddate, action) {
	customConfirm('Are you sure you want to delete this duty?', function() {
		$.post("page-includes/master-duties/verifyDutyAssignedtoRota.php", {
				intDutyId: dutyid
			},
			function (data) {
				let ParsedData = JSON.parse(data);
				if (ParsedData.status == 0) {
					let htmlText = '<div style="width: 600px"><table id="customConfirm2" class="smalltable bluetable" width="100%"><thead><tr><th colspan="5">Please Confirm</th></tr></thead><tbody><tr><td style="padding:10px;">This will delete the duty from all Rota Patterns.</td></tr><tr style="text-align:right;"><td><input class="yes2" name="yes2" id="yes2" type="submit" value="Yes" onclick="deldutyconfirm('+"'"+dutyid+"'"+', '+"'"+prevdutyid+"'"+', '+"'"+lastmoddate+"'"+', '+"'"+action+"'"+');parent.$.facebox.close();" >&nbsp;&nbsp;&nbsp;&nbsp<input name="no2" id="no2" type="submit" value="No" onClick="parent.$.facebox.close();"></td></tr></tbody></table></div>';
					setTimeout(function() {$.facebox(htmlText);}, 3000);
				}
				else {
					deldutyconfirm(dutyid, prevdutyid, lastmoddate, action);
				}
			});
			}, function() {
				return;
			}
		);
}

/*
Desc: Add master duties to unallocated duties on allocations screen
*/

function AddToUnallocatedDuty(id, teamid) {
    $.post("/page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php", {
        dutyID: id,
        teamID: teamid,
        action : 'selectweekpopup'
    },
    function (data) {
        $.facebox(data);
    });
}

/*
Desc: Add master duties to unallocated/allocated duties on allocations screen
*/

function CopyToAllDuties(id, teamid) {
    $.post("/page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php", {
        dutyID: id,
        teamID: teamid,
        action : 'selectweekpopupforcopy'
    },
    function (data) {
        $.facebox(data);
    });
}

function deldutyconfirm(dutyid, prevdutyid, lastmoddate, action) {
    //check for Duty in Rotas before deleting
    $.ajax({
        url: "page-includes/master-duties/delete-duty.php",
        type: "POST",
        dataType: "json",
        data: {
            'dutyid': dutyid,
            'action': action,
            'lastmoddate': lastmoddate
        },
        success: function (data) {
            if (data.status == 'success') {
                //all good
                ListMasterDuties(0, prevdutyid, 0);
            } else {
                customAlert(data.status);
            }
        },
        error: function (x, e) {
            if (x.status == 0) {
                customAlert('You are offline!!\n Please Check Your Network.');
            } else if (x.status == 404) {
                customAlert('Requested URL not found.');
            } else if (x.status == 500) {
                customAlert('Internal Server Error.');
            } else if (e == 'parsererror') {
                customAlert('Error.\nParsing JSON Request failed.');
            } else if (e == 'timeout') {
                customAlert('Request Time out.');
            } else {
                customAlert('Unknow Error.\n' + x.responseText);
            }
        }
    });
}

/* add unallocted duties */
function AddUnAlloctedDuties(){
   
    $('#addunallocateddutyform').validate({
        debug: false,
        
        rules: {
            "weeknumber": {
                required: true,
            }
        },
        messages: {
           
            
        },
        submitHandler: function (form) {
            
            $.ajax({
                type: 'POST',
                url: '/page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php',
                dataType: "json",
                data: $('#addunallocateddutyform').serialize(),
                success: function (data) {
                    if(data.status === 'success'){
                        customAlert(data.message, 1000);
                    } else {
                        $(".messageerror").html(data.message);
                        
                    }
                }
            });
        }
    })
}

/* add unallocted duties */
function saveCopyToAllDutiesForm(){
   
    $('#copytoalldutiesform').validate({
        debug: false,
        
        rules: {
            "copytoaldutiesweeknumber": {
                required: true,
            }
        },
        messages: {
           
            
        },
        submitHandler: function (form) {
            
            $.ajax({
                type: 'POST',
                url: '/page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php',
                dataType: "json",
                data: $('#copytoalldutiesform').serialize(),
                success: function (data) {
                    if(data.status === 'success'){
                        customAlert(data.message, 1000);
                    } else {
                        $(".copytoalldutieserrormsg").html(data.message);
                        
                    }
                }
            });
        }
    })
}