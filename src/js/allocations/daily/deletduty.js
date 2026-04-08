  function confirm() {
     $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/edits/deleteduty-confirm.php',
      data: $('#cfrm').serialize(),
      success: function (data) {
		  ShowDailyAllocations($("#teamid").val(), $('#strCurrentDate').val());
        $.facebox.close();
      }
    });
  }
  
  function cancel() {
    $.facebox.close();
  }