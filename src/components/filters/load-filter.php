<?php 
include_once 'page-includes/allocations/weekly/filters/filters-script.php';
?>
<script type="text/javascript">
function ShowWeeklyAllocation(teamId,WeekNumber,scheduledPersonId,isShifttoCheck)
{
  var myKeyVals = {'WeekNumber' : WeekNumber, 'teamId' : teamId,'scheduledPersonId': scheduledPersonId,'isShifttoCheck':isShifttoCheck};
    $.ajax({
			  type: 'POST',
			  url: 'page-includes/allocations/allocations-weekly.php',
        data: myKeyVals,
			  success: function (returnValue) {
				$('#content').html(returnValue);
			},
      error:function (returnValue) {
          alert('some error found in menu script.');
			}
		});
}

function ShowDailyAllocationWithAppliedFilter(teamId,date,callerpage,applyfilter)
{
  var myKeyVals = {'date' : date, 'teamId' : teamId,'callerpage': callerpage, 'screenName':'ViewDaily'};
    $.ajax({
			  type: 'POST',
			  url: 'page-includes/allocations/allocations-daily.php',
        data: myKeyVals,
			  success: function (returnValue) {
				$('#content').html(returnValue);
			},
      error:function (returnValue) {
          alert('some error found in menu script.');
			}
		});
}

function ShowAllocationsMultiWeekAllocatios(teamId, week) {
    var myKeyVals = { 'week' : week, 'teamId' : teamId};
    $.ajax({
			  type: 'POST',
			  url: 'page-includes/allocations/allocations-multi-week.php',
        data: myKeyVals,
			  success: function (returnValue) {
				$('#content').html(returnValue);
				if((localStorage.getItem('weeklyscreen_filter') != '') && (localStorage.getItem('weeklyscreen_filter') != null)){
					$('#filterName').html('<span class="qtip-hover" title="Filter Applied"> Filter Applied</span>');
					$('#clearFilterIcon').show();
					applyViewMultiWeeklyFilter();
				} 
			},
      error:function (returnValue) {
          alert('some error found in menu script.');
			}
		});
  }
  
function ShowRotaInEditWeeklyAllocations(argteamId,argweeknumber,finalargweeknumber,argstartDate,argendDate,argendWeek,finalargendweeknumber,request,selFilterId,selFilterType,mastMiscId, rotaData){
	var myKeyVals = {'WeekNumber' : finalargweeknumber, 'teamId' : argteamId,'argweeknumber' :argweeknumber ,'argstartDate' : argstartDate,'argendDate' : argendDate,'argendWeek' : finalargendweeknumber,'finalargendweeknumber' : argendWeek, 'requestval':request,'selFilterId':selFilterId,'selFilterType':selFilterType,'mastMiscFilterId':mastMiscId, 'rotaData':rotaData};
	$.ajax({
		type: 'POST',
		url: 'page-includes/allocations/allocations-editweekly-rota.php',
		data: myKeyVals,
		success: function (returnValue) {
			$('#content').html(returnValue);
		},
		error:function (returnValue) {
			alert('some error found in menu script.');
		}
	});
}
</script>
