$(document).ready(function () {
    chargeWbsCodePermissions();
    let codeType = $("#js_codetype").val();
    $.facebox.close();
    let tableId = 'receiverCodeEntries';
    let container =  'chargingContainer';
    let searchBox = "Search Charge Code";
        if($("#js_codetype").val() == 1){
           
            tableId = 'wbsCodeEntries';
            container =  'wbsContainer';
            searchBox = "Search WBS Code";
        }
    createDataTable(tableId,container,searchBox,codeType);
});

function createDataTable(tableId,container,searchBox,codeType = 0) {
    if (codeType == 0) {
        var table = $('#wbsCodeEntries').DataTable();
    } else {
        var table = $('#receiverCodeEntries').DataTable();
    }
    table.destroy();

    $("."+container).height($(window).height() - 120);
        var table = $("#"+tableId).DataTable( {
        lengthChange: false,
        paging: true,
        "pageLength": 100,
        info: true,
        scrollY: parseInt($("."+container).height() - 120),
        "fnDrawCallback": function (oSettings) {
            if ($('#'+tableId+' tr').length >= 20) {
                $(".dataTables_paginate").css('visibility', 'visible');
                $(".dataTables_length").css("display", "none");
                $("#dataTables_paginate ").css('font-size', '8px');
                $(".dataTables_info").css('display', 'block');
            }else{
                $(".dataTables_info").css('display', 'none');
                $(".dataTables_paginate").css('visibility', 'none');
            }
        },
        "columnDefs": [
            { "searchable": false, "targets": [1,2,3] }
        ],
        language: {
           search: "",
           searchPlaceholder: searchBox
        }	
   } );
}
//document ready closed
/* Inactive/Active code */
function startsupdate(activecodeid) {
    chargeWbsCodePermissions();
    setActiveCode(activecodeid);
}

/* set active inactive charge/wbs code*/
function setActiveCode(activecodeid) {
    let codeType = $("#js_codetype").val();
    $.ajax({
        url: "page-includes/admin/charging/index.php",
        type: "POST",
        dataType: "json",
        data: {
            conrollerName:'isActiveCode',
            chargeWbsCodeid: activecodeid
           
        },
        success: function (data) {
            if (data.Status == 1) {
                //update active code
                if (data.actionValue == 0) {
                    $(".js_isActiveCode_" + activecodeid).attr('src', './images/red_cross.png');
					$("#activehidestatus_"+activecodeid).html(0);
                } else {
                    $(".js_isActiveCode_" + activecodeid).attr('src', './images/green_tick.png');
					$("#activehidestatus_"+activecodeid).html(1);
                }
            } else {
                customAlert(data.responseMessage, 1000);
                if (data.actionValue == 1) {
                    $(".js_isActiveCode_" + activecodeid).attr('src', './images/red_cross.png');
					$("#activehidestatus_"+activecodeid).html(0);
                } else {
                    $(".js_isActiveCode_" + activecodeid).attr('src', './images/green_tick.png');
					$("#activehidestatus_"+activecodeid).html(1);
                }
                
            }
            if (codeType==0) {
                actionHandler('listChargeCode');
            }else {
                actionHandler('listWbsCode');
            }
            return;
        }
    });
}



/* check permissions*/
function chargeWbsCodePermissions(){
    if($("#js_codetype").val() == 1){
        getPagePermission(18);
       
    } else{
        getPagePermission(17);
    }
}
function highlightTableRow(thisVal)
{
	$('.unHighlightOrange').removeClass('highlightOrange');
	$(thisVal).addClass('highlightOrange');
}
