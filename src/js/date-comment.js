/*
Date comment handling javascript
*/
var itsDailyViewPage = 0;
var dateCommentIconSize = 1;
var dateCommentIconColor = 'blue';
var dateDefaultComment = 'No comment';
function dateCommentOptionsSet(itsDailyViewPagePara = 0, iconSize = 'fa-lg', iconColor = 'blue') {
    itsDailyViewPage = itsDailyViewPagePara;
    dateCommentIconSize = iconSize;
    dateCommentIconColor = iconColor;
    $.contextMenu( 'destroy', '.date-comment' );    
}
function addModalElement() {
    $('#date-comment-tooltip-container').remove();
    $('#content').append($('<div id="date-comment-tooltip-container" style="position: fixed; z-index: 9999; display: none;"></div>'));
};
function initializeDateComment() {
    $.contextMenu({
        selector: '.date-comment', 
        callback: function(key, options) {
        },
        items: {
            "add_comment": {
                name: "Add Date Comment", 
                icon: "comment",
                visible: function(key, opt, e) {
                    let $obj = $(this).find('.date-comment-tooltip');
                    return $obj.length == 0 || $obj.attr('data-date-comment-hover') == dateDefaultComment ? true : false;
                },
                callback: function(key, options) {
                    let date = $(this).attr("data-date-comment-date");
                    let teamId = $(this).attr("data-date-comment-team-id");
                    addDateCommentModal(date, teamId);
                }
            },
            "edit": {
                name: 'Edit Date Comment',
                visible: function(key, opt, e) {
                    let $obj = $(this).find('.date-comment-tooltip');
                    return $obj.length != 0 && $obj.attr('data-date-comment-hover') != dateDefaultComment ? true : false;
                },
                icon: "edit",
                callback: function(key, opt, e) {
                    let date = $(this).attr("data-date-comment-date");
                    let teamId = $(this).attr("data-date-comment-team-id");
                    addDateCommentModal(date, teamId);
                }
            },
            "delete": {
                name: "Delete Date Comment",
                icon: "delete",
                visible: function(key, opt, e) {
                    let $obj = $(this).find('.date-comment-tooltip');
                    return $obj.length != 0 && $obj.attr('data-date-comment-hover') != dateDefaultComment ? true : false;
                },
                callback: function(key, opt, e) {
                    let date = $(this).attr("data-date-comment-date");
                    let teamId = $(this).attr("data-date-comment-team-id");
                    let commentId =  $(this).find('.date-comment-tooltip').attr("date-date-comment-id");
                    updateDateComment(commentId, date, teamId, '');
                }
            },
            "history": {
                name: "Date comment history",
                icon: "history",
                visible: function(key, opt, e) {
                    let $obj = $(this).find('.date-comment-tooltip');
                    return $obj.length != 0 && $obj.attr('data-date-comment-hover') != dateDefaultComment ? true : false;
                },
                callback: function(key, options) {
                    let date = $(this).attr("data-date-comment-date");
                    let teamId = $(this).attr("data-date-comment-team-id");
                    historyDateCommentModal(date, teamId);
                }
            }
        }
    });
    initializeDateCommentViewIcon();
}

function addDateCommentModal(date, teamId) {
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/modals/add-date-comment-modal.php",
        data: {'date' : date, 'teamId' : teamId},
        success: function (response) { 
            $.facebox(response);
        }
    });
}

function historyDateCommentModal(date, teamId) {
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/modals/get-date-comment-history-modal.php",
        data: {'date' : date, 'teamId' : teamId},
        success: function (response) { 
            $.facebox(response);
        }
    });
}

function updateDateComment(commentId, date, teamId, comment = $('#date-Comment-txt').val()) {
    if($.trim(comment) == '' && commentId == 0) {
        $('#facebox .close').click();
        return false;
    }
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/edits/add-date-comment.php",
        data: {'date' : date, 'teamId' : teamId, 'comment' : comment , 'id': (commentId == 0 ? '' : commentId)},
        success: function (response) { 
            $('#facebox .close').click();
            initializeDateCommentViewIcon();
        }
    });
}

function initializeDateCommentViewIcon() {
    let dates = [];
    let teamId = null;
    $('.date-comment').each(function(i, obj) {
        dates.push($(this).attr("data-date-comment-date"));
        teamId = $(this).attr("data-date-comment-team-id");
    });
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/get-date-comment.php",
        data: {'teamId' : teamId, 'dates' : dates},
        success: function (response) {            
            $('.date-commment-info-icon').find('.date-comment-tooltip').remove()
            response =  JSON.parse(response);            
            response.data.forEach(element => {
                addCommentViewIcon($('.date-comment[data-date-comment-date="' + element.CommentDate + '"]').find('.date-commment-info-icon'), element.CommentDate, element.Comment, element.ID);
            });          
            addModalElement();
            showDateCommentTooltip();
            $(".date-comment .date-commment-info-icon").click(function(e) {
                e.stopPropagation();
            });
        }
    });
}

function addCommentViewIcon($obj, date, comment, commentId = 0) {
    let backgroundColour = itsDailyViewPage == 1 || itsDailyViewPage == 2 ? InheritedBackgroundColor($obj) : '';
    let styles = '';
    if(itsDailyViewPage == 1) {
        styles = 'border-radius:100%;background:#4B72BF;box-shadow:0px 0px 0px 1px '+ backgroundColour +' inset;';
    } else if (itsDailyViewPage == 2) {
        styles = 'background:#4B72BF;box-shadow:0px 0px 0px 4px '+ backgroundColour +' inset;';
    }
    $obj.html(
        $('<i class="fa fa-info-circle ' + dateCommentIconSize + ' date-comment-tooltip" style="color:' + dateCommentIconColor + '; ' + styles + '" aria-hidden="true"></i>').attr({'data-date-hover' : date, 'data-date-comment-hover': comment, 'date-date-comment-id' : commentId})
    );    
}

function InheritedBackgroundColor($obj){ 
    let bg = '';   
    $($obj).parent().each(function() {
        var bc = $(this).css("background-color");     
        bg = bc.replace(/\s/g, ''); 
    });
    if(bg == "rgba(0,0,0,0)" || bg == '' || bg == 'transparent') {        
        return InheritedBackgroundColor($($obj).parent());
     } else {
        return bg;
    } 
 }

function showDateCommentTooltip() {
    $('.date-comment-tooltip').hover(function() {
        let date = new Date($(this).attr('data-date-hover'));
        let dateUI = ((date.getDate() > 9) ? date.getDate() : ('0' + date.getDate())) + '/' + ((date.getMonth() > 8) ? (date.getMonth() + 1) : ('0' + (date.getMonth() + 1))) +'/' + date.getFullYear();
        $('#date-comment-tooltip-container').html('<table style="width:300px ;border: 2px solid #ffffff;font-size: 10pt;background-color: #ebebeb; color:black;"><tbody><tr><td style="padding: 4px;word-break:break-all;"> ' +  $(this).attr('data-date-comment-hover') + ' </td></tr></tbody></table>');
        let nativeTopPos = $(this).offset().top;
        let nativeLeftPos = $(this).offset().left;
        let topPos = itsDailyViewPage == 0 ? nativeTopPos - 25 : nativeTopPos - 30;
        let leftPos = nativeLeftPos - $('#date-comment-tooltip-container').width();
        $('#date-comment-tooltip-container').css('left',leftPos);
        $('#date-comment-tooltip-container').css('top',topPos);
        $('#date-comment-tooltip-container').show();
    });
    $('.date-comment-tooltip').mouseleave(function() {
         $('#date-comment-tooltip-container').hide();
    });
}