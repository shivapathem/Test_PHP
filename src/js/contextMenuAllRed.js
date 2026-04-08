// Context Menu Code 1
$(function(){
    $.contextMenu({
        selector: '.leave-context-menu',
        callback: function(key, options) {
            $('*').qtip('hide');
        },
        items: {
            "delete": {
                name: "Delete",
                icon: "delete",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("leaveid");
                    var year =  options.$trigger.attr("year");
                    var user =  options.$trigger.attr("user");
					var allocationid =  options.$trigger.attr("leaveDutyAllocationID");

                    $( "#dialog-leave-delete" ).dialog(
                        {
                            buttons: {
                                "Yes": function() {
                                    $( this ).dialog( "close" );
                                    $.ajax({
                                        url:"page-includes/leave/leave-delete.php",
                                        type:'POST',
										data: { id: leaveid, allocationid: allocationid },
                                        dataType:"text",
                                        success:function(response,status,http) {
                                            ShowLeave(year, user);
                                        },
                                        error: function(http,status,error){
                                            alert("Error Found :" + error);
                                        }
                                    });

                                },
                                "No": function() {
                                    $( this ).dialog( "close" );
                                },
                            }
                        }
                    );
                },
                visible: function(key, options) {
                    if (options.$trigger.attr("isLeaveProved") == 0) {
                        return true;
                    }
                }
            },
            "sep1": "---------",
            "comments": {
                name: "Comments",
                icon: "comment",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("leaveid");
                    var year =  options.$trigger.attr("year");
                    var user =  options.$trigger.attr("user");
                    var isPDLAllow =  options.$trigger.attr("ispartdayleaveallow");
                    var dutyStartTime =  options.$trigger.attr("starttime");
                    var dutyEndTime =  options.$trigger.attr("endtime");
                    var dutyName =  options.$trigger.attr("dutyname");
					var allocationid =  options.$trigger.attr("leaveDutyAllocationID");

                    let dutyStartTimeHr = Math. floor(parseInt(options.$trigger.attr("starttime")) / 3600);
                    if(dutyStartTimeHr <= 9){
                        dutyStartTimeHr = '0'+dutyStartTimeHr;
                    }

                    let dutyStartTimeMin = Math. floor((parseInt(options.$trigger.attr("starttime")) - (dutyStartTimeHr * 3600)) / 60);
                    if(dutyStartTimeMin <= 9){
                        dutyStartTimeMin = '0'+dutyStartTimeMin;
                    }

                    let dutyEndTimeHr = Math. floor(parseInt(options.$trigger.attr("endtime")) / 3600);
                    if(dutyEndTimeHr <= 9){
                        dutyEndTimeHr = '0'+dutyEndTimeHr;
                    }

                    let dutyEndTimeMin = Math. floor((parseInt(options.$trigger.attr("endtime")) - (dutyEndTimeHr * 3600)) / 60);
                    if(dutyEndTimeMin <= 9){
                        dutyEndTimeMin = '0'+dutyEndTimeMin;
                    }

                    $.post("page-includes/leave/leave-comments.php", {
                        id: leaveid,
                        year: year,
                        user: user,
                        isPDLAllow: isPDLAllow,
                        dutyStartTimeSeconds: dutyStartTime,
                        dutyEndTimeSeconds: dutyEndTime,
                        dutyStartTimeHrMin : dutyStartTimeHr+':'+dutyStartTimeMin,
                        dutyEndTimeHrMin : dutyEndTimeHr+':'+dutyEndTimeMin,
                        dutyName : dutyName,
                        allocationid : allocationid
                    },
                    function(data,status){
                        $.facebox(data);
                    });
                },
                visible: function(key, options) {
                    if ((options.$trigger.attr('isPDLApplied') == 1) && (options.$trigger.attr("isLeaveProved") == 0) || ((options.$trigger.attr('isPDLApplied') == 0) && (options.$trigger.attr("isLeaveProved") == 0)) || (options.$trigger.attr('isPDLApplied') == 1) && (options.$trigger.attr("isLeaveProved") == 1)) {
                        return true;
                    }
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("leaveid");
                    $.post("page-includes/leave/leave-history.php", {
                            id: leaveid
                        },
                        function(data,status){
                            $.facebox(data);
                        });
                }
            }
        }
    });
});
$(function(){
    $.contextMenu({
        selector: '.pdl-leave-context-menu-approved',
        callback: function(key, options) {
            $('*').qtip('hide');
        },
        items: {
            "comments": {
                name: "Comments",
                icon: "comment",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("leaveid");
                    var year =  options.$trigger.attr("year");
                    var user =  options.$trigger.attr("user");
                    var isPDLAllow =  options.$trigger.attr("ispartdayleaveallow");
                    var dutyStartTime =  options.$trigger.attr("starttime");
                    var dutyEndTime =  options.$trigger.attr("endtime");
                    var dutyName =  options.$trigger.attr("dutyname");
					var allocationid =  options.$trigger.attr("leaveDutyAllocationID");


                    let dutyStartTimeHr = Math. floor(parseInt(options.$trigger.attr("starttime")) / 3600);
                    if(dutyStartTimeHr <= 9){
                        dutyStartTimeHr = '0'+dutyStartTimeHr;
                    }

                    let dutyStartTimeMin = Math. floor((parseInt(options.$trigger.attr("starttime")) - (dutyStartTimeHr * 3600)) / 60);
                    if(dutyStartTimeMin <= 9){
                        dutyStartTimeMin = '0'+dutyStartTimeMin;
                    }

                    let dutyEndTimeHr = Math. floor(parseInt(options.$trigger.attr("endtime")) / 3600);
                    if(dutyEndTimeHr <= 9){
                        dutyEndTimeHr = '0'+dutyEndTimeHr;
                    }

                    let dutyEndTimeMin = Math. floor((parseInt(options.$trigger.attr("endtime")) - (dutyEndTimeHr * 3600)) / 60);
                    if(dutyEndTimeMin <= 9){
                        dutyEndTimeMin = '0'+dutyEndTimeMin;
                    }

                    $.post("page-includes/leave/leave-comments.php", {
                            id: leaveid,
                            year: year,
                            user: user,
                            isPDLAllow: isPDLAllow,
                            dutyStartTimeSeconds: dutyStartTime,
                            dutyEndTimeSeconds: dutyEndTime,
                            dutyStartTimeHrMin : dutyStartTimeHr+':'+dutyStartTimeMin,
                            dutyEndTimeHrMin : dutyEndTimeHr+':'+dutyEndTimeMin,
                            dutyName : dutyName,
						    allocationid : allocationid

                        },
                        function(data,status){
                            $.facebox(data);
							if (isPDLAllow == 1){
							document.getElementById("starttimemasterduty").disabled = true;
							document.getElementById("endtimemasterduty").disabled = true;
							}
                        });
                }},
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("leaveid");
                    $.post("page-includes/leave/leave-history.php", {
                            id: leaveid
                        },
                        function(data,status){
                            $.facebox(data);
                        });
                }
            }
        }
    });
});
$(function(){
    $.contextMenu({
        selector: '.leave-context-menu-approved',
        callback: function(key, options) {
            $('*').qtip('hide');
        },
        items: {
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("leaveid");
                    $.post("page-includes/leave/leave-history.php", {
                            id: leaveid
                        },
                        function(data,status){
                            $.facebox(data);
                        });
                }
            }
        }
    });
});

$(function(){
    $.contextMenu({
        selector: '.request-context-menu',
        //trigger: 'left',
        callback: function(key, options) {
            id = options.$trigger.attr("id");
        },
        items: {
            "comments": {
                name: "Comments",
                icon: "comment",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-comments.php", {
                            id: requestid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
            "delete": {
                name: "Delete",
                icon: "delete",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    var CurrentDate =  options.$trigger.attr("CurrentDate");
                    $( "#dialog-request-delete" ).dialog(
                        {
                            width: 500,
                            buttons: {
                                "Yes": function() {
                                    $( this ).dialog( "close" );
                                    $.post("page-includes/requests/request-delete.php", {
                                            id: requestid
                                        },
                                        function(data,status){
                                            ShowRequests(CurrentDate);
                                        });
                                },
                                "No": function() {
                                    $( this ).dialog( "close" );
                                },
                            }
                        }
                    );
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-history.php", {
                            id: requestid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            }
        }
    });
});
$(function(){
    $.contextMenu({
        selector: '.request-context-menu-approved',
        //trigger: 'left',
        callback: function(key, options) {
            $('*').qtip('hide');
        },
        items: {
            "comments": {
                name: "Comments",
                icon: "comment",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-comments.php", {
                            id: requestid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-history.php", {
                            id: requestid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            }
        }
    });
});

$(function(){
    $.contextMenu({
        selector: '.weekly-requests-context-menu',
        //trigger: 'left',
        callback: function(key, options) {
            id = options.$trigger.attr("id");
        },
        items: {
            "approve": {
                name: "Approve",
                icon: "tick",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    var date =  options.$trigger.attr("date");
                    $.post("page-includes/requests/request-approve.php", {
                            id: requestid
                        },
                        function(data,status){
                            ShowWeeklyRequestsAdmin(date)
                        });
                }
            },
            "sep1": "---------",
            "unlikely": {
                name: "Unlikely",
                icon: "exclaim",
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    var date =  options.$trigger.attr("date");
                    $("#loading").show();
                    $.post("page-includes/requests/request-unlikely.php", {
                            id: requestid
                        },
                        function(data,status){
                            ShowWeeklyRequestsAdmin(date)
                        });

                }
            },
            "decline": {
                name: "Not Possible",
                icon: "decline",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    var date =  options.$trigger.attr("date");
                    $( "#dialog-request-decline" ).dialog(
                        {
                            width: 500,
                            buttons: {
                                "Yes": function() {
                                    $( this ).dialog( "close" );
                                    $.post("page-includes/requests/request-decline.php", {
                                            id: requestid
                                        },
                                        function(data,status){
                                            ShowWeeklyRequestsAdmin(date)
                                        });
                                },
                                "No": function() {
                                    $( this ).dialog( "close" );
                                },
                            }
                        }
                    );
                }
            },
            "delete": {
                name: "Delete",
                icon: "delete",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    var date =  options.$trigger.attr("date");
                    $( "#dialog-request-delete" ).dialog(
                        {
                            width: 500,
                            buttons: {
                                "Yes": function() {
                                    $( this ).dialog( "close" );
                                    $.post("page-includes/requests/request-delete.php", {
                                            id: requestid
                                        },
                                        function(data,status){
                                            ShowWeeklyRequestsAdmin(date)
                                        });
                                },
                                "No": function() {
                                    $( this ).dialog( "close" );
                                },
                            }
                        }
                    );
                }
            },
            "changetype": {
                name: "Change Type",
                icon: "change",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-change-type.php", {
                            id: requestid,
                            pageid: 1
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
            "new": {
                name: "New Request",
                icon: "add",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-admin-apply.php", {
                            id: requestid,
                            pageid: 1
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
            "sep2": "---------",
            "comments": {
                name: "Comments",
                icon: "comment",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-comments.php", {
                            id: requestid,
                            pageid: 1
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-history.php", {
                            id: requestid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            }
        }
    });
});

$(function(){
    $.contextMenu({
        selector: '.weekly-requests-context-menu-approved',
        //trigger: 'left',
        callback: function(key, options) {
            id = options.$trigger.attr("id");
        },
        items: {
            "approve": {
                name: "Unapprove",
                icon: "cross",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    var date =  options.$trigger.attr("date");
                    $.post("page-includes/requests/request-approve.php", {
                            id: requestid
                        },
                        function(data,status){
                            ShowWeeklyRequestsAdmin(date)
                        });
                }
            },
            "sep1": "---------",
            "changetype": {
                name: "Change Type",
                icon: "change",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-change-type.php", {
                            id: requestid,
                            pageid: 1
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
            "new": {
                name: "New Request",
                icon: "add",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-admin-apply.php", {
                            id: requestid,
                            pageid: 1
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
            "sep2": "---------",
            "comments": {
                name: "Comments",
                icon: "comment",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-comments.php", {
                            id: requestid,
                            pageid: 1
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },

            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var requestid =  options.$trigger.attr("requestid");
                    $.post("page-includes/requests/request-history.php", {
                            id: requestid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            }
        }
    });
});

$(function(){
    $.contextMenu({
        selector: '.weekly-leave-context-menu',
        //trigger: 'left',
        callback: function(key, options) {
            $('*').qtip('hide');
        },
        items: {
            "managetype": {
                name: "Manage",
                icon: "change",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid = options.$trigger.attr("id");
                    var week = options.$trigger.attr("week");
                    var callpage =  options.$trigger.attr("callpage");
                    $.post("page-includes/leave/leave-approve-popup.php", {
                            id: leaveid,
                            week: week,
                            callpage:callpage,
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                },
                visible: function(key, options){
                    var showoptions = options.$trigger.attr("showoptions");
                    var isLeaveManager = options.$trigger.attr("data-admin-type");
                    if(showoptions == 'yes' && isLeaveManager != 3){
                        return true;
                    }else{
                        return false;
                    }
                }

            },
            "agree": {
                name: "Agree",
                icon: "tick",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid = options.$trigger.attr("id");
                    var week = options.$trigger.attr("week");
                    var callpage =  options.$trigger.attr("callpage");
                    var group = options.$trigger.attr("group");
                    $.post("page-includes/leave/leave-approve-approver.php", {
                            id: leaveid,
                            week: week,
                            callpage:callpage,
                            agree: 1
                        },
                        function(data,status){                            
                            if (callpage == 2) {
                                AdminShowLeaveWeekly(week, group);
                            } else {
                                ShowLeaveWeeklyAdmin(week, group);
                            }
                        })
                },
                visible: function(key, options){          
                    var showoptions = options.$trigger.attr("showoptions");                    
                    let leaveAgreed = options.$trigger.hasClass('LeaveAgreed');           
                    return options.$trigger.attr("data-admin-type") == 3 && showoptions == 'yes' && !leaveAgreed ? true : false;
                }
            },
            "unagree": {
                name: "Unagree",
                icon: "delete",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid = options.$trigger.attr("id");
                    var week = options.$trigger.attr("week");
                    var callpage =  options.$trigger.attr("callpage");
                    var group = options.$trigger.attr("group");
                    $.post("page-includes/leave/leave-approve-approver.php", {
                            id: leaveid,
                            week: week,
                            callpage:callpage,
                            agree: 0
                        },
                        function(data,status){
                            if (callpage == 2) {
                                AdminShowLeaveWeekly(week, group);
                            } else {
                                ShowLeaveWeeklyAdmin(week, group);
                            }
                        })
                },
                visible: function(key, options){          
                    var showoptions = options.$trigger.attr("showoptions");   
                    let leaveAgreed = options.$trigger.hasClass('LeaveAgreed');       
                    return showoptions == 'yes' && leaveAgreed ? true : false;
                }
            },
            "sep1": "---------",
            "unlikely": {
                name: "Unlikely",
                icon: "exclaim",
                callback: function(key, options) {
                    var leaveid = options.$trigger.attr("id");
                    var week = options.$trigger.attr("week");
                    var group = options.$trigger.attr("group");
                    var callpage =  options.$trigger.attr("callpage");
                    var fullname = options.$trigger.attr("fullname");
                    $.post("page-includes/leave/leave-unlikely.php", {
                            id: leaveid
                        },
                        function(data,status){
                            if(status=='success'){
                                if(data==1){
                                    $('#username_'+leaveid).text("["+fullname+"]");
                                    if (callpage == 2) {
                                        AdminShowLeaveWeekly(week, group);
                                    } else {
                                        ShowLeaveWeeklyAdmin(week, group);
                                    }
                                }else{
                                    $('#username_'+leaveid).text(fullname);
                                }
                            }
                        });

                },
                visible: function(key, options){
                    var showoptions = options.$trigger.attr("showoptions");
                    var isLeaveManager = options.$trigger.attr("data-admin-type");
                    if(showoptions == 'yes' && isLeaveManager != 3){
                        return true;
                    }else{
                        return false;
                    }
                }
            },
            "comments": {
                name: "Comments",
                icon: "comment",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid = options.$trigger.attr("id");
                    var week = options.$trigger.attr("week");
                    var group = options.$trigger.attr("group");
                    var callpage =  options.$trigger.attr("callpage");
					var allocationid =  options.$trigger.attr("leaveDutyAllocationID");
                    $.post("page-includes/leave/leave-comments.php", {
                            id: leaveid,
                            week: week,
                            group: group,
                            callpage:callpage,
							allocationid:allocationid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                },
                visible: function(key, options){
                    var showoptions = options.$trigger.attr("showoptions");
                    var isLeaveManager = options.$trigger.attr("data-admin-type");
                    if(showoptions == 'yes' && isLeaveManager != 3){
                        return true;
                    }else{
                        return false;
                    }
                }
            },
            "delete": {
                name: "Delete",
                icon: "delete",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid = options.$trigger.attr("id");
                    var week = options.$trigger.attr("week");
                    var group = options.$trigger.attr("group");
                    var callpage =  options.$trigger.attr("callpage");
					var allocationid =  options.$trigger.attr("leaveDutyAllocationID");
					
                    $( "#dialog-leave-delete" ).dialog({
                        width:600,
                        buttons: {
                            "Yes": function() {
                                $( this ).dialog( "close" );
                                $.ajax({
                                    url:"page-includes/leave/leave-delete.php",
                                    type:'POST',
									data: { id: leaveid, allocationid: allocationid },
                                    dataType:"text",
                                    success:function(response,status,http) {
                                        if (callpage == 2) {
                                            AdminShowLeaveWeekly(week, group);
                                        }
                                        else {
                                            ShowLeaveWeeklyAdmin(week, group);
                                        }
                                    },
                                    error: function(http,status,error){
                                        alert("Error Found :" + error);
                                    }
                                });
                            },
                            "No": function() {
                                $( this ).dialog( "close" );
                            },
                        }
                    });

                },
                visible: function(key, options){
                    var showoptions = options.$trigger.attr("showoptions");
                    var isLeaveManager = options.$trigger.attr("data-admin-type");
                    if(showoptions == 'yes' && isLeaveManager != 3){
                        return true;
                    }else{
                        return false;
                    }
                }
            },
            "requests": {
                name: "Change Type",
                icon: "change",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid = options.$trigger.attr("id");
                    var week = options.$trigger.attr("week");
                    var callpage =  options.$trigger.attr("callpage");
                    $.post("page-includes/leave/leave-change-type.php", {
                            id: leaveid,
                            week: week,
                            callpage:callpage,
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                },
                visible: function(key, options){
                    var showoptions = options.$trigger.attr("showoptions");
                    var isLeaveManager = options.$trigger.attr("data-admin-type");
                    if(showoptions == 'yes' && isLeaveManager != 3){
                        return true;
                    }else{
                        return false;
                    }
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("id");
                    $.post("page-includes/leave/leave-history.php", {
                            id: leaveid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            }
        }
    });
});

$(function(){
    /**
     * Yearly Page menu 2
     */
    $.contextMenu({
        selector: '.context-menu-d-yearly',
        callback: function (key, options) {
        },
        items: {
            "undelete": {
                name: "Undelete",
                icon: "restore",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("id");
                    var week = options.$trigger.attr("week");
                    $.post("page-includes/leave/leave-restore.php", {
                            id: leaveid
                        },
                        function(data,status){
                            ShowYealyLeaves();
							data = JSON.parse(data);
							if(data.status ==0){
								customAlert(data.strstatus);
							}
							else{
                            openManagePopup(leaveid,week);
							}
                        })
                },
                visible: function(key, options){
                    var isLeaveManager = options.$trigger.attr("data-admin-type");
                    if(isLeaveManager != 3){
                        return true;
                    }else{
                        return false;
                    }
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("id");
                    $.post("page-includes/leave/leave-history.php", {
                            id: leaveid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            }
        }
    });

    //Menu 3

    $.contextMenu({
        selector: '.context-menu-yearly-onlyhistory',
        callback: function (key, options) {
        },
        items: {

            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var leaveid =  options.$trigger.attr("id");
                    $.post("page-includes/leave/leave-history.php", {
                            id: leaveid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            }
        }
    });
// Context Menu Code 2
    $.contextMenu({
    selector: '.context-menu-yearly',
    callback: function (key, options) {
    },
    items: {
        "managetype": {
            name: "Manage",
            icon: "change",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                $.post("page-includes/leave/leave-approve-popup.php", {
                        id: leaveid,
                        week: week,
                        callpage:'yearly',
                    },
                    function(data,status){
                        $.facebox(data);
                    })
            },
            visible: function(key, options){
                var isLeaveManager = options.$trigger.attr("data-admin-type");
                if(isLeaveManager != 3){
                    return true;
                }else{
                    return false;
                }
            }
        },
		"comments": {
            name: "Comments",
            icon: "comment",
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                var group = options.$trigger.attr("data-leave-group-id");
                var callpage =  8;
                var allocationid =  options.$trigger.attr("data-leave-allocation-id");
                $.post("page-includes/leave/leave-comments.php", {
                        id: leaveid,
                        week: week,
                        group: group,
                        callpage:callpage,
                        allocationid:allocationid
                    },
                    function(data,status){
                        $.facebox(data);
                    })
            },
            visible: function(key, options){
                return true;
            }
        },
        "agree": {
            name: "Agree",
            icon: "tick",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                $.post("page-includes/leave/leave-approve-approver.php", {
                        id: leaveid,
                        week: week,
                        agree: 1
                },
                function(data,status){                            
                    GetTabContent(8);
                })
            },
            visible: function(key, options){                          
                let leaveAgreed = options.$trigger.attr('data-agreed');  
                let leaveApproved = options.$trigger.attr('data-approved');        
                return options.$trigger.attr("data-admin-type") == 3 && leaveAgreed == 0 && leaveApproved == 0 ? true : false;
            }
        },
        "unagree": {
            name: "Unagree",
            icon: "delete",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                $.post("page-includes/leave/leave-approve-approver.php", {
                        id: leaveid,
                        week: week,
                        agree: 0
                },
                function(data,status){
                    GetTabContent(8);
                })
            },
            visible: function(key, options){           
                let leaveAgreed = options.$trigger.attr('data-agreed');  
                let leaveApproved = options.$trigger.attr('data-approved');         
                return options.$trigger.attr("data-admin-type") == 3 && leaveAgreed == 1 && leaveApproved == 0 ? true : false;
            }
        },
		"requests": {
			name: "Change Type",
			icon: "change",
			// superseeds "global" callback
			callback: function(key, options) {
				var leaveid = options.$trigger.attr("id");
				var week = options.$trigger.attr("week");
				var callpage =  8;
				$.post("page-includes/leave/leave-change-type.php", {
					id: leaveid,
					week: week,
					callpage:callpage,
				},
				function(data,status){
					$.facebox(data);
				})
			},
			visible: function(key, options){
                var isLeaveManager = options.$trigger.attr("data-admin-type");
                if(isLeaveManager != 3){
                    return true;
                }else{
                    return false;
                }
            }
		},
        "history": {
            name: "History",
            icon: "history",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid =  options.$trigger.attr("id");
                $.post("page-includes/leave/leave-history.php", {
                        id: leaveid
                    },
                    function(data,status){
                        $.facebox(data);
                    })
            }
        }
    }
}); //Yearly context menu end
$.contextMenu({
    selector: '.weekly-leave-context-menu-approved',
    //trigger: 'left',
    callback: function(key, options) {
        $('*').qtip('hide');
    },
    items: {
        "managetype": {
            name: "Manage",
            icon: "change",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                var callpage =  options.$trigger.attr("callpage");
                $.post("page-includes/leave/leave-approve-popup.php", {
                        id: leaveid,
                        week: week,
                        callpage:callpage,
                    },
                    function(data,status){
                        $.facebox(data);
                    })
            },
            visible: function(key, options){
                var showoptions = options.$trigger.attr("showoptions");
                if(showoptions == 'yes' && options.$trigger.attr("data-admin-type") != 3){
                    return true;
                }else{
                    return false;
                }
            }
        },

        "sep1": "---------",
        "unlikely": {
            name: "Unlikely",
            icon: "exclaim",
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                var group = options.$trigger.attr("group");
                var callpage =  options.$trigger.attr("callpage");
                var fullname = options.$trigger.attr("fullname");
                $.post("page-includes/leave/leave-unlikely.php", {
                        id: leaveid
                    },
                    function(data,status){
                        if(status=='success'){
                            if(data==1){
                                $('#username_'+leaveid).text("["+fullname+"]");
                                if (callpage == 2) {
                                    AdminShowLeaveWeekly(week, group);
                                } else {
                                    ShowLeaveWeeklyAdmin(week, group);
                                }
                            }else{
                                $('#username_'+leaveid).text(fullname);
                            }
                        }
                    });

            },
            visible: function(key, options){
                var showoptions = options.$trigger.attr("showoptions");
                var isLeaveManager = options.$trigger.attr("data-admin-type");
                if(showoptions == 'yes' && isLeaveManager != 3){
                    return true;
                }else{
                    return false;
                }
            }
        },
        "comments": {
            name: "Comments",
            icon: "comment",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                var group = options.$trigger.attr("group");
                var callpage =  options.$trigger.attr("callpage");
				var allocationid =  options.$trigger.attr("leaveDutyAllocationID");
                $.post("page-includes/leave/leave-comments.php", {
                        id: leaveid,
                        week: week,
                        group: group,
                        callpage:callpage,
						allocationid:allocationid
                    },
                    function(data,status){
                        $.facebox(data);
                    })
            },
            visible: function(key, options){
                var showoptions = options.$trigger.attr("showoptions");
                var isLeaveManager = options.$trigger.attr("data-admin-type");
                if(showoptions == 'yes' && isLeaveManager != 3){
                    return true;
                }else{
                    return false;
                }
            }
        },
        "delete": {
            name: "Delete",
            icon: "delete",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                var group = options.$trigger.attr("group");
                var callpage =  options.$trigger.attr("callpage");
				var allocationid =  options.$trigger.attr("leaveDutyAllocationID");

                $( "#dialog-leave-delete" ).dialog({
                    width:600,
                    buttons: {
                        "Yes": function() {
                            $( this ).dialog( "close" );
                            $.ajax({
                                url:"page-includes/leave/leave-delete.php",
                                type:'POST',
								data: { id: leaveid, allocationid: allocationid },
                                dataType:"text",
                                success:function(response,status,http) {
                                    if (callpage == 2) {
                                        AdminShowLeaveWeekly(week, group);
                                    } else {
                                        ShowLeaveWeeklyAdmin(week, group);
                                    }
                                },
                                error: function(http,status,error){
                                    alert("Error Found :" + error);
                                }
                            });
                        },
                        "No": function() {
                            $( this ).dialog("close");
                        },
                    }
                });

            },
            visible: function(key, options){
                var showoptions = options.$trigger.attr("showoptions");
                var isLeaveManager = options.$trigger.attr("data-admin-type");
                if(showoptions == 'yes' && isLeaveManager != 3){
                    return true;
                }else{
                    return false;
                }
            }
        },
        "requests": {
            name: "Change Type",
            icon: "change",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid = options.$trigger.attr("id");
                var week = options.$trigger.attr("week");
                var callpage =  options.$trigger.attr("callpage");
                $.post("page-includes/leave/leave-change-type.php", {
                        id: leaveid,
                        week: week,
                        callpage:callpage,
                    },
                    function(data,status){
                        $.facebox(data);
                    })
            },
            visible: function(key, options){
                var showoptions = options.$trigger.attr("showoptions");
                var isLeaveManager = options.$trigger.attr("data-admin-type");
                if(showoptions == 'yes' && isLeaveManager != 3){
                    return true;
                }else{
                    return false;
                }
            }
        },
        "history": {
            name: "History",
            icon: "history",
            // superseeds "global" callback
            callback: function(key, options) {
                var leaveid =  options.$trigger.attr("id");
                $.post("page-includes/leave/leave-history.php", {
                        id: leaveid
                    },
                    function(data,status){
                        $.facebox(data);
                    })
            }
        }
    }
});
});

$(function(){
    $.contextMenu({
        selector: '.locks-context-menu',
        //trigger: 'left',
        callback: function(key, options) {
            id = options.$trigger.attr("id");
        },
        items: {
            "delete": {
                name: "Delete",
                icon: "delete",
                // superseeds "global" callback
                callback: function(key, options) {
                    var lockid =  options.$trigger.attr("lockid");
                    $( "#dialog-lock-delete" ).dialog({
                        width:600,
                        buttons: {
                            "Yes": function() {
                                $( this ).dialog( "close" );
                                $.post("page-includes/requests/lock-delete.php", {
                                        id: lockid
                                    },
                                    function(data,status){
                                        ShowLocksAdmin();
                                    });
                            },
                            "No": function() {
                                $( this ).dialog( "close" );
                            },
                        }
                    });
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var lockid =  options.$trigger.attr("lockid");
                    $.post("page-includes/requests/lock-history.php", {
                            id: lockid
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            }
        }
    });
});


$(function(){
    $.contextMenu({
        selector: '.locks-context-menu-header',
        //trigger: 'left',
        callback: function(key, options) {
            id = options.$trigger.attr("id");
        },
        items: {
            "new": {
                name: "New Lock",
                icon: "add",
                // superseeds "global" callback
                callback: function(key, options) {
                    var date =  options.$trigger.attr("currdate");
                    var pageoption =  options.$trigger.attr("pageoption");
                    var group =  options.$trigger.attr("group");
                    $.post("page-includes/requests/lock-admin-apply.php", {
                            date: date,
                            pageoption: pageoption,
                            group: group
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
        }
    });
});

$(function(){
    $.contextMenu({
        selector: '.allocations-hideday-menu',

        callback: function(key, options) {
            $('*').qtip('hide');
        },
        items: {
            "starttime": {
                name: "Hide Allocations",
                icon: "cross",
                visible: function(key, options) {
                    if (options.$trigger.attr("data-edit-screen") == 1) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    var team =  options.$trigger.attr("team");
                    var date =  options.$trigger.attr("date");
                    var callerpage = options.$trigger.attr("pageid");
                    HideDay(date, team, callerpage, 1);
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var team =  options.$trigger.attr("team");
                    var date =  options.$trigger.attr("date");
                    $.post("page-includes/allocations/hidden-day-history.php", {
                            teamID: team,
                            date: date
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
        }
    });
});

$(function(){
    $.contextMenu({
        selector: '.allocations-showday-menu',

        callback: function(key, options) {
            $('*').qtip('hide');
        },
        items: {
            "starttime": {
                name: "Show Allocations",
                icon: "tick",
                visible: function(key, options) {
                    if (options.$trigger.attr("data-edit-screen") == 1) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    var team =  options.$trigger.attr("team");
                    var date =  options.$trigger.attr("date");
                    var callerpage = options.$trigger.attr("pageid");
                    HideDay(date, team, callerpage, 0);
                }
            },
            "history": {
                name: "History",
                icon: "history",
                // superseeds "global" callback
                callback: function(key, options) {
                    var team =  options.$trigger.attr("team");
                    var date =  options.$trigger.attr("date");
                    $.post("page-includes/allocations/hidden-day-history.php", {
                            teamID: team,
                            date: date
                        },
                        function(data,status){
                            $.facebox(data);
                        })
                }
            },
        }
    });
});
