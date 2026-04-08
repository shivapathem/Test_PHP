var dataRequest = {
    "source": {
        "ID": 0,
        "dataSource": '',
        "DutyName": '',
        "Duration": 0,
        "dutyColorId": 0,
        "DutyDate": null,
        "SchedulingPersonID": 0,
        "MasterDutyId": 0,
        "dutyBreakTime": 0,
    },
    "target": {
        "ID": 0,
        "DutyDate": '',
        "iDay": '',
        "SchedulingPersonID": 0,
        "SchedulingTeamId": 0,
        "DutyDate": '',
        "dataSource": '',
        "WeekNumber": $('#getWeekNumber').val(),
    }
};


var dragSource;
var colCssId, colSourceUniqueId, colTargetUniqueId = 0;
var dataOrder, allocId;
var sourceCellIns, targetCellIns;

$(document).ready(function() {	
    dragInitialization();
});

function dragInitialization() {
    $('.dragAlloc').on('dragstart', function (event) {
        dragSource = $(this);
        event.stopImmediatePropagation();
        startDrag(dragSource);
    });

    $('.dropeventscall').on('dragover', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
    });
}

function startDrag(dragSource){
    
    if($('#weeklyUnAllocation-2').hasScrollBar()){
        $('#weeklyUnAllocation-2').css('padding-right', '10px').css('overflow-y','clip');
    }

    if($('#weeklyAllocation-2').hasScrollBar()){
        $('#weeklyAllocation-2').css('padding-right', '10px').css('overflow-y','clip');
    }

    colSourceUniqueId = dragSource.attr('data-unique-id');
    dataRequest.source.ID = dragSource.attr('data-allocations-duty-id');
    dataRequest.source.dataSource = dragSource.attr('data-source');
    dataRequest.source.DutyName = dragSource.attr('data-misc-dutyname');
    dataRequest.source.Duration = dragSource.attr('data-misc-dutyduration');
    dataRequest.source.dutyColorId = dragSource.attr('data-misc-dutycolour-id');
    dataRequest.source.DutyDate = dragSource.attr('data-date');
    dataRequest.source.SchedulingPersonID = dragSource.attr('data-scheduling-person');
    dataRequest.source.MasterDutyId = dragSource.attr('data-misc-id');
    dataRequest.source.dutyBreakTime = dragSource.attr('data-break-time');
    dataRequest.source.chargingpresent = dragSource.attr('data-charging-present');
    dataRequest.source.starttime = dragSource.attr('data-row-start');
    dataRequest.source.endtime = dragSource.attr('data-row-end');
    sourceCellIns = $("#colUnqId_" + colSourceUniqueId);

}

function dropAlloc(event,dropAttr){
    event.preventDefault();
    colTargetUniqueId = dropAttr.getAttribute("data-unique-id");
    dataRequest.target.ID = dropAttr.getAttribute("data-allocations-duty-id");
    dataRequest.target.iDay = dropAttr.getAttribute("data-iday");
    dataRequest.target.SchedulingPersonID = dropAttr.getAttribute("data-scheduling-person");
    dataRequest.target.SchedulingTeamId = dropAttr.getAttribute("data-scheduling-team-id");
    dataRequest.target.DutyDate = dropAttr.getAttribute("data-date");
    dataRequest.target.dataSource = dropAttr.getAttribute("data-source");
    dataRequest.target.DutyName = dropAttr.getAttribute("data-duty-name");
    dataRequest.target.chargingpresent = dropAttr.getAttribute("data-charging-present");
    targetCellIns = $("#colUnqId_" + colTargetUniqueId);

    $("#rowUnqId_" + colTargetUniqueId).css('background-color', '#ddd');

    if((dataRequest.source.dataSource == 'unallocated') && (dataRequest.target.dataSource == 'allocated')){
        var pd = overlapDuty();
        if(!pd) {
            return false;
        }
    }
	if ((dataRequest.source.dataSource != 'misc') && (dataRequest.target.dataSource != 'unallocated')) {
        if (dropAttr.getAttribute("data-date") !== dragSource.attr('data-date') && (dropAttr.getAttribute("data-date") != null)) {
            if((dropAttr.getAttribute('data-row-start') == 0) && (dropAttr.getAttribute('data-row-end') == 0)){
                $("#rowUnqId_"+dropAttr.getAttribute('data-id')).css('background-color','#'+dropAttr.getAttribute('data-bg-color'));
            } else {
                updateTagetBackground(colTargetUniqueId);
            }
        	customAlert('You can not drop it to another day!');
            return false;
        }
    }
    if((dataRequest.source.dataSource == 'misc') && (dataRequest.target.dataSource == 'unallocated')){
    	updateTagetBackground(colTargetUniqueId);
    	return false;
    }
    if((dataRequest.source.dataSource == 'misc') && (dataRequest.target.dataSource == 'misc')){
    	updateTagetBackground(colTargetUniqueId);
    	return false;
    }
    if((dataRequest.source.dataSource == 'unallocated') && (dataRequest.target.dataSource == 'unallocated')){
    	updateTagetBackground(colTargetUniqueId);
    	return false;
    }
    if((dataRequest.source.dataSource == 'unallocated') && (dataRequest.target.dataSource == 'allocated') && ((dataRequest.target.DutyName == 'U-Sick') || (dataRequest.target.DutyName == 'Sick') || (dataRequest.target.DutyName == '-Sick'))){
    	customAlert('Cannot assign a Duty to Absent/Sick/Leave Days');
    	updateTagetBackground(colTargetUniqueId);
    	return false;
    }
    if((dataRequest.source.dataSource == 'unallocated') && (dataRequest.target.dataSource == 'allocated') && (dataRequest.target.DutyName == 'Leave' || dataRequest.target.DutyName == 'OFF Leave')){
    	customAlert('Cannot assign a Duty to Absent/Sick/Leave Days');
    	updateTagetBackground(colTargetUniqueId);
    	return false;
    }
    if((dataRequest.source.dataSource == 'unallocated') && (dataRequest.target.dataSource == 'allocated') && (dataRequest.target.DutyName == 'Absent')){
    	customAlert('Cannot assign a Duty to Absent/Sick/Leave Days');
    	updateTagetBackground(colTargetUniqueId);
    	return false;
    }
    if(($('#redEuro_'+dataRequest.target.ID).hasClass('activemarkovertime') || $('#redEuro_'+dataRequest.source.ID).hasClass('activemarkovertime'))){
        customAlert('You cannot unallocate or swap a duty that is marked for Overtime. Please delete the Overtime before modifying the duty');
        updateTagetBackground(colTargetUniqueId);
        return false;
    }
    if(((dataRequest.source.dataSource == 'allocated') && (dataRequest.target.dataSource == 'allocated')) && (dataRequest.source.SchedulingPersonID == dataRequest.target.SchedulingPersonID)){
        if((dropAttr.getAttribute('data-row-start') == 0) && (dropAttr.getAttribute('data-row-end') == 0)){
            $("#rowUnqId_"+colTargetUniqueId).css('background-color','#'+dropAttr.getAttribute('data-bg-color'));
        }
        return false;
    }
    if(((dataRequest.source.dataSource == 'allocated') && (dataRequest.target.dataSource == 'allocated')) && (dataRequest.source.SchedulingPersonID != dataRequest.target.SchedulingPersonID)) {
        var pd = overlapDuty();
        if(!pd) {
            return false;
        }

    }

    if((dataRequest.source.SchedulingPersonID != 0) && (dataRequest.target.SchedulingPersonID != 0) && (dataRequest.source.dataSource != 'misc')){
        if(dataRequest.source.chargingpresent == 1 || dataRequest.source.chargingpresent == '1') {
            customAlert("You cannot Drag a Duty that has a Charging record associated with it!");
            changeBackColourAllocateGrid();
            return false;
        } else {
            if(dataRequest.target.chargingpresent == 1 || dataRequest.target.chargingpresent == '1') {
                customAlert("You cannot Drop a Duty that has a Charging record associated with it!");
                changeBackColourAllocateGrid();
                return false;
            } else {
                setAllocatedDutyOnFront(dragSource,dropAttr);
                refreshAllocatedSectionTr(dragSource.attr('data-scheduling-person'), dragSource.attr('data-row-id'), dataRequest.target.DutyDate, dropAttr.getAttribute('data-duty-name'), dropAttr.getAttribute('data-row-start'), dropAttr.getAttribute('data-row-end'), dragSource.attr('data-unique-id'), '', 'SWAP', dataRequest.source.ID, dataRequest.target.ID, 0, 'Yes', 'No', dragSource, dropAttr);
            }
        }
    }
    else if((dataRequest.source.SchedulingPersonID == 0) && (dataRequest.source.dataSource != 'misc') && (dataRequest.target.SchedulingPersonID != 0)){
        if(dataRequest.target.chargingpresent == 1 || dataRequest.target.chargingpresent == '1') {
            customAlert("You cannot Drop a Duty that has a Charging record associated with it!");
            changeBackColourAllocateGrid();
            return false;
        } else {
            let updUnallocGridDataUI = false;
            if(dragSource.attr('data-duty-name') != dropAttr.getAttribute("data-duty-name")){
                updUnallocGridDataUI = true;
            } else {
                if((dragSource.attr('data-row-start') == dropAttr.getAttribute("data-row-start")) && (dragSource.attr('data-row-end') != dropAttr.getAttribute("data-row-end"))){
                    updUnallocGridDataUI = true;
                } else if((dragSource.attr('data-row-start') != dropAttr.getAttribute("data-row-start")) && (dragSource.attr('data-row-end') == dropAttr.getAttribute("data-row-end"))){
                    updUnallocGridDataUI = true;
                } else if((dragSource.attr('data-row-start') != dropAttr.getAttribute("data-row-start")) && (dragSource.attr('data-row-end') != dropAttr.getAttribute("data-row-end"))){
                    updUnallocGridDataUI = true;
                } else if((dragSource.attr('data-row-start') == dropAttr.getAttribute("data-row-start")) && (dragSource.attr('data-row-end') == dropAttr.getAttribute("data-row-end")) && (dragSource.attr('data-duty-duration') != dropAttr.getAttribute("data-duty-duration"))){
                    updUnallocGridDataUI = true;
                }
            }
            if(updUnallocGridDataUI == true){
                if($('#unAllocSortType').val() == undefined || $('#unAllocSortType').val() == ''){
                    setAllocatedDutyOnFront(dragSource,dropAttr);
                }
                refreshAllocatedSectionTr(dropAttr.getAttribute('data-scheduling-person'), dropAttr.getAttribute('data-row-id'), dataRequest.target.DutyDate, dragSource.attr('data-duty-name'), dragSource.attr('data-row-start'), dragSource.attr('data-row-end'), dropAttr.getAttribute('data-unique-id'), '', 'UNALLOCTOALLOC', dataRequest.source.ID, dataRequest.target.ID, 0, 'Yes', 'No', dragSource, dropAttr);
            } else {
                if($('#dragDropId1').html() != '' ||  $('#dragDropId2').html() != ''){
                     $('#rowUnqId_'+$('#dragDropId1').html()).css('border','');
                     $('#rowUnqId_'+$('#dragDropId2').html()).css('border','');
                     $('#dragDropId1').html('');
                     $('#dragDropId2').html('');
                }
                $('#dragDropId1').html(dragSource.attr('data-id'));
                $('#dragDropId2').html(dropAttr.getAttribute('data-id'));
                $('#rowUnqId_'+dragSource.attr('data-id')).css('border-bottom','2px solid #bbb');
                $('#rowUnqId_'+dropAttr.getAttribute('data-id')).css('border-bottom','2px solid #bbb');
                changeBackColourAllocateGrid();
                return false;
            }
        }
    }
    else if((dataRequest.source.SchedulingPersonID != 0) && (dataRequest.target.SchedulingPersonID == 0) && (dataRequest.source.dataSource != 'misc')){
        if(dataRequest.source.chargingpresent == 1 || dataRequest.source.chargingpresent == '1') {
            customAlert("You cannot Drag a Duty that has a Charging record associated with it!");
            changeBackColourAllocateGrid();
            return false;
        } else {
            setAllocatedDutyOnFront(dragSource,dropAttr);
            refreshAllocatedSectionTr(dragSource.attr('data-scheduling-person'), dragSource.attr('data-row-id'), dataRequest.source.DutyDate, dragSource.attr('data-duty-name'), 0, 0, dragSource.attr('data-unique-id'), '', 'ALLOCTOUNALLOC', dataRequest.source.ID, 0, 0, 'Yes', 'Yes', dragSource);
        }
    }
    else if(dataRequest.source.dataSource == 'misc'){
        if(dataRequest.target.chargingpresent == 1 || dataRequest.target.chargingpresent == '1') {
            customAlert("You cannot Drop a Duty that has a Charging record associated with it!");
            changeBackColourAllocateGrid();
            return false;
        } else {
            var loadUnallocDiv = 'No';
            if (dropAttr.getAttribute('data-duty-name') != 'U') {
                loadUnallocDiv = 'Yes';
            }
            setAllocatedDutyOnFront(dragSource,dropAttr);
            refreshAllocatedSectionTr(dropAttr.getAttribute('data-scheduling-person'), dropAttr.getAttribute('data-row-id'), dataRequest.target.DutyDate, dragSource.attr('data-misc-dutyname'), dragSource.attr('data-row-start'), dragSource.attr('data-row-end'), dropAttr.getAttribute('data-unique-id'), 'misc', 'MISC', 0, dropAttr.getAttribute('data-id'), dataRequest.source.MasterDutyId, 'Yes', loadUnallocDiv, dragSource, dropAttr);
        }
    }
    
    changeBackColourAllocateGrid();
    return true;
}

function changeBackColourAllocateGrid() {
    $("#rowUnqId_" + colSourceUniqueId).css('background-color', '#ebebeb');
    $("#rowUnqId_" + colTargetUniqueId).css('background-color', '#ebebeb');
}

function setAllocatedDutyOnFront(dragSource,dropAttr) {
    if(dragSource.attr('data-source') == 'unallocated'){
        setAllocDuty(dropAttr,dragSource);
    }
    if(dragSource.attr('data-source') == 'allocated' && dropAttr.getAttribute('data-source') == 'allocated'){
        setSwapAllocDuty(dropAttr,dragSource);
    }
}
function updateTagetBackground(targetIdNum){
    $('#rowUnqId_'+targetIdNum).css('background-color','#ebebeb');
}

function overlapDateFormat(dateObj) {
    var d = new Date(dateObj);
    var day = d.getDate();
    var month = d.getMonth() + 1;
    var year = d.getFullYear();
    if (day < 10) {
        day = "0" + day;
    }
    if (month < 10) {
        month = "0" + month;
    }
    var date = day + "/" + month + "/" + year;
    return date;
}

function convertSecstoTime(ti) {
    let st = Number(ti);
    var sth = parseInt(Math.floor(st / 3600));
    var stm = parseInt(Math.floor(st % 3600 / 60));
    if (sth < 10){
        sth = "0"+sth;
    }
    if (stm < 10){
        stm = "0"+stm;
    }
    return sth+':'+stm;
}

function overlapDuty() {
   
    let olDragSrcDutyStartTime = dataRequest.source.starttime;
    let olDragSrcDutyEndTime = dataRequest.source.endtime;

    let prevOverlapdutyAllocId = $("#rowUnqId_"+dataRequest.target.ID).prev().attr('data-id');
    let nextOverlapdutyAllocId = $("#rowUnqId_"+dataRequest.target.ID).next().attr('data-id');

    let olPrevDutyStartTime = $("#colUnqId_"+prevOverlapdutyAllocId).attr('data-row-start');
    let olPrevDutyEndTime = $("#colUnqId_"+prevOverlapdutyAllocId).attr('data-row-end');
    let olPrevDutyDate = $("#colUnqId_"+prevOverlapdutyAllocId).attr('data-date');
    let olNextDutyStartTime = $("#colUnqId_"+nextOverlapdutyAllocId).attr('data-row-start');
    let olNextDutyEndTime = $("#colUnqId_"+nextOverlapdutyAllocId).attr('data-row-end');
    let olNextDutyName = $("#colUnqId_"+nextOverlapdutyAllocId).attr('data-duty-name');
    let oltarScheduledPerson = $(".peronname_"+dataRequest.target.SchedulingPersonID).attr('data-order');

    let olNextDutyDate = new Date(dataRequest.source.DutyDate);
    olNextDutyDate.setDate(olNextDutyDate.getDate() + 1);

    let olDragSrcDutyDate = dataRequest.source.DutyDate;
    
    let prevErrMsg = 'You cannot have shifts with overlapping hours. '+oltarScheduledPerson+' finishes a shift on '+overlapDateFormat(olDragSrcDutyDate)+' at '
    +convertSecstoTime(olPrevDutyEndTime) + ' and starts the next shift on '+overlapDateFormat(olDragSrcDutyDate)+' at ' 
    +convertSecstoTime(olDragSrcDutyStartTime) + '. You must change the hours so that these shifts do not overlap. ';

    let nextErrMsg = 'You cannot have shifts with overlapping hours. '+oltarScheduledPerson+' finishes a shift on '+overlapDateFormat(olNextDutyDate)+' at '
    +convertSecstoTime(olDragSrcDutyEndTime) + ' and starts the next shift on '+overlapDateFormat(olNextDutyDate)+' at ' 
    +convertSecstoTime(olNextDutyStartTime) + '. You must change the hours so that these shifts do not overlap. ';

    if((parseInt(olDragSrcDutyStartTime) == 0) && (parseInt(olDragSrcDutyEndTime) == 0)){
        return true;
    } else {
        if(parseInt(olPrevDutyStartTime) > parseInt(olPrevDutyEndTime)) {
            if(parseInt(olDragSrcDutyStartTime)  < parseInt(olPrevDutyEndTime))
            {
                customAlert(prevErrMsg);
                changeBackColourAllocateGrid();
                return false;
            }
        }
    }
    let excludeDutyArr = ["u", "u-sick", "leave", "off leave", "absent"];
    if((parseInt(olDragSrcDutyStartTime) == 0) && (parseInt(olDragSrcDutyEndTime) == 0)){
        return true;
    } else {
        if(parseInt(olDragSrcDutyEndTime) < parseInt(olDragSrcDutyStartTime)) 
        {
            olDragSrcDutyEndTime = parseInt(olDragSrcDutyEndTime) + 86400;
            if (olNextDutyName) {
                if ($.inArray(olNextDutyName.toLowerCase(), excludeDutyArr) == -1) {
                    olNextDutyStartTime = parseInt(olNextDutyStartTime) + 86400;
                    if ((parseInt(olDragSrcDutyEndTime) > parseInt(olNextDutyStartTime)) && (parseInt(olNextDutyStartTime) != 0 && parseInt(olNextDutyEndTime) != 0)) {
                        customAlert(nextErrMsg);
                        changeBackColourAllocateGrid();
                        return false;
                    }
                }
            }
        }
    }
    return true;
}

function dropSickLeaveAbsent(event,dropAttr){
    customAlert('Cannot assign a Duty to Absent/Sick/Leave Days');
    return false;
}

function dropPDL(event,dropAttr){
    customAlert('You cannot swap a duty containing a Part Day of Leave.');
    return false;
}

$("html").on("dragover", function(event) {
    event.preventDefault();
    event.stopPropagation();
});

$("html").on("drop", function(event) {
    if($('#weeklyUnAllocation-2').hasScrollBar()){
        $('#weeklyUnAllocation-2').css('padding-right', '0').css('overflow-y','scroll');
    }

    if($('#weeklyAllocation-2').hasScrollBar()){
        $('#weeklyAllocation-2').css('padding-right', '0').css('overflow-y','scroll');
    }
});

$("html").on("mousemove", function(event) {
    if(($('#weeklyUnAllocation-2').hasScrollBar()) && ($('#weeklyUnAllocation-2').css("overflow-y") == 'hidden' || $('#weeklyUnAllocation-2').css("overflow-y") == 'clip')){
        $('#weeklyUnAllocation-2').css('padding-right', '0').css('overflow-y','scroll');
    }

    if(($('#weeklyAllocation-2').hasScrollBar()) && ($('#weeklyAllocation-2').css("overflow-y") == 'hidden' || $('#weeklyAllocation-2').css("overflow-y") == 'clip')){
        $('#weeklyAllocation-2').css('padding-right', '0').css('overflow-y','scroll');
    }
});