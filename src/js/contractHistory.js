/*
   * @Description : Fetch all of the Contract Person History.
   * @access : NA
   * @global :NA
   * @param  : satffid
   * @return : NA
   */
$(document).ready(function (e) {
    $(".contract_history").on('click', function () {
        $('#contract_history').DataTable().clear().destroy();
        let personid = $("#newpersonid").val();
        $('#contract_history').DataTable({
            "pageLength": 20,
            "lengthChange": false,
            "order": [[ 0, 'desc' ]],
            "fnDrawCallback": function (oSettings) {
                $(".dataTables_paginate").css('visibility', 'visible');
                $(".dataTables_length").css("display", "block");
                $("#dataTables_paginate ").css('font-size', '8px');
                $(".sorting").css('min-width', '95px');
            },
            "ajax": {
                url: '../page-includes/staff-details/view/contractHistoryData.php',
                "data": {staffid: personid}
            }
        });
        $(".dataTables_paginate").css('visibility', 'visible');
        $(".dataTables_length").css("display", "block");
    });
});

function  showContractHistory(thisVal){
	let msg = $(thisVal).data('msg');
    $.post("function-includes/common/common.php", {
            action: 'contractHistory',
            modulename: 'contractHistory',
			msg: msg
        },
        function (data, status) {
            $.facebox(data);
        })
}

