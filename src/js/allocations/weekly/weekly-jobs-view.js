$(function() {
    $(".view-job-details-icon > .fa-circle").on("mouseover", function (e) {
        jobsToolTip('showtooltip', $(this).parent());
    });
    $(".view-job-details-icon > .fa-circle").on("mouseleave", function (e) {
        jobsToolTip('hidetooltip', '');
    });
});


function jobsToolTip(type='',currObj){
	$('#loading').hide();
    $('#job-list-tooltip').remove();
    $('body').append($('<div class="tooltip-div" style="background:white;padding: 10px;width: 370px;"></div>').attr('id', 'job-list-tooltip'));
	if(type == 'showtooltip'){
        let tipHtml = '';
        let jobDetails = JSON.parse($(currObj).attr('data-job-detail'));
        jobDetails.sort((a, b) => {
            return a.jobStartTime.toString().localeCompare(b.jobStartTime);
        });
        tipHtml += '<table class="tablesmalltidy" width="100%"><tr height="30px"><th colspan="3"><b>Allocated Jobs<b></th></tr><tr><td colspan="3"><hr></td></tr><tr height="30px">		<td style="font-weight: bold; padding: 5px;">Job Name</td><td style="font-weight: bold; text-align: center; padding: 5px;">Start</td><td style="font-weight: bold; text-align: center; padding: 5px;">End</td></tr>';
        $.each(jobDetails, function (key, val) {
            tipHtml += '<tr>';
            tipHtml += '<td style="padding: 5px;">'+ val['jobName'] +'</td>';
            tipHtml += '<td style="padding: 5px;text-align: center;">'+ val['jobStartTime'] +'</td>';
            tipHtml += '<td style="padding: 5px;text-align: center;">'+ val['jobEndTime'] +'</td>';
            tipHtml += '</tr>';
        });

        tipHtml += '</table>';

        let nativeTopPos = $(currObj).offset().top;
        let nativeLeftPos = $(currObj).find('.fa-circle').offset().left;

        let topPos = nativeTopPos-159;
        let leftPos = nativeLeftPos-380;
        $('#job-list-tooltip').css('left',leftPos+'px');
        $('#job-list-tooltip').css('top',topPos+'px');
        $('#job-list-tooltip').html(tipHtml);
        $('#job-list-tooltip').show();        
        if($('#job-list-tooltip').offset().left < 0) {
            $('#job-list-tooltip').css('left', ($('#job-list-tooltip').offset().left + 60 ) +'px');
        }
        if ( typeof customtipweekly == 'function' ) { 
            customtipweekly('',currObj);
        }
	} else {
       $('#job-list-tooltip').hide();
    }
}

var SCRIPT_KEY_NAME = (function () {
    const DEFAULT_KEY = '';
    const scripts = document.getElementsByTagName('script');
    for (let i = scripts.length - 1; i >= 0; i--) {
        const key = scripts[i].getAttribute('data-key-name');
        if (key) return key;
    }
    return DEFAULT_KEY;
})();

$(document).ready(function () {
    let attempts = 0;
    let maxAttempts = 20;

    let restoreInterval = setInterval(function () {
        attempts++;

        if (restoreHighlightedRows() || attempts >= maxAttempts) {
            clearInterval(restoreInterval);
        }
    }, 500);
});

var PERSON_COLUMNS = [
    'personname_',
    'dutyAllocation_'
];
function applyHighlight(personId) {
    PERSON_COLUMNS.forEach(cls => {
        $('.' + cls + personId)
            .css('border-top', '1.5px solid #000000')
            .css('border-bottom', '1.5px solid #000000');
    });
}

function removeHighlight(personId) {
    PERSON_COLUMNS.forEach(cls => {
        $('.' + cls + personId)
            .css('border-top', 'none')
            .css('border-bottom', '0.5px solid #ddd');
    });
}

function getHighlightedPersons() {
    let teamId = $('#teamId').val();
    if (teamId > 0) {
        let data = $.cookie(SCRIPT_KEY_NAME + teamId);
        return data ? data.split(',').map(Number) : [];
    }
    return [];
}

function setHighlightedPersons(list) {
    let teamId = $('#teamId').val();
    if (teamId > 0) {
        $.cookie(
            SCRIPT_KEY_NAME + teamId,
            list.join(',')
        );
    }
}

function removeHighlightRowPersonCookie() {
    let teamId = $('#teamId').val();
    if (teamId > 0) {
        $.removeCookie(SCRIPT_KEY_NAME + teamId);
    }
}

function restoreHighlightedRows() {
    let selected = getHighlightedPersons();
    if (!selected.length) return false;

    let foundAny = false;
    selected.forEach(id => {
        if ($('.personname_' + id).length) {
            applyHighlight(id);
            foundAny = true;
        }
    });

    return foundAny;
}

function highlightRowPerson(schPerson = 0) {
    let persons = [];

    if (typeof schPerson === 'number' && schPerson > 0) {
        persons = [schPerson];
    } else if (typeof schPerson === 'string') {
        persons = schPerson
            .split(',')
            .map(id => parseInt(id, 10))
            .filter(Number.isFinite);
    }

    if (!persons.length) return;

    let selected = getHighlightedPersons();

    persons.forEach(personId => {
        if (selected.includes(personId)) {
            selected = selected.filter(id => id !== personId);
            removeHighlight(personId);
        } else {
            selected.push(personId);
            applyHighlight(personId);
        }
    });

    if (selected.length === 0) {
        removeHighlightRowPersonCookie();
    } else {
        selected = [...new Set(selected)];
        setHighlightedPersons(selected);
    }
}
