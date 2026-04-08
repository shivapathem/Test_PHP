<?php

  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  ini_set("zlib.output_compression", 1);
  include_once '../../function-includes/init.php';
  include_once '../../function-includes/leavefunctions.php';
  include_once '../../function-includes/genericfunctions.php';
  include_once '../../function-includes/common/classCommonDBFunctions.php';
  include_once '../users/process/classUserSetup.php';
  include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
  $service = new AllocationService();

  $setupObj = new classUserSetup();
  $commonObj= new classCommonDBFunctions();
  $pdo = OpenDBLinkA7();
  $intSysAdmin =$isDivisionalAdmin= 0;
  $intLinksAdmin=0; //Addded By Soniya
  $showOptions =0;
  $userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  if($userID>0){
  $intSysAdmin= $commonObj->UserIsSysAdmin($userID) ?? 0;
  $isDivisionalAdmin = $commonObj->UserIsDivAdmin($userID);
  }
  $loggedUsedInfo = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
  $isSchedulingTeamAdmin = 0;
  if (isset($loggedUsedInfo['isSchedulingTeamAdmin'])){
    $isSchedulingTeamAdmin = $loggedUsedInfo['isSchedulingTeamAdmin'];
  }
  $tz_from = 'UTC';
  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

  $intOption =  $_REQUEST['option'] ?? '';
  $selecteduser = '';
  $intScheduledPersonID = 0;
  $arrUserLeaveSettingVar = json_decode($commonObj->userLeaveRequestByNetLogin($strUser, 0),true);
  $isLeaveManagerOnlyVar = 1;
  foreach ($arrUserLeaveSettingVar['LeaveRequests'] ?? [] as $intGroupIDVar => $arrGroupVar) {
    if ($arrGroupVar['Admin'] >= 1 && $arrGroupVar['Admin'] != 3) {
      $isLeaveManagerOnlyVar = 0;
    }
  }

  if(!in_array($intOption, [2, 4, 5]) && $isLeaveManagerOnlyVar == 1) {
    echo '<span>Access denied</span>';
    die;
  }
// The group is the one we need to get
// The Option is what we need to retreive

$arrLeave = GetLeaveUnapproved($strUser);

switch ($intOption) {
  case 0:
    $shortnoticeText='';
    // ##################################################################################  Do the ShortNotice
    $shortnoticeText.= '<table class="tablesmall compact stripe w-100" id="shortnotice">';
    $shortnoticeText.= '<thead>';
    $shortnoticeText.= '<tr>';
    $shortnoticeText.= '<th>';
    $shortnoticeText.= 'Group';
    $shortnoticeText.= '</th>';
    $shortnoticeText.= '<th>';
    $shortnoticeText.= 'Type';
    $shortnoticeText.= '</th>';
    $shortnoticeText.= '<th>';
    $shortnoticeText.= 'Date';
    $shortnoticeText.= '</th>';
    $shortnoticeText.= '<th>';
    $shortnoticeText.= 'Week';
    $shortnoticeText.= '</th>';
    $shortnoticeText.= '<th>';
    $shortnoticeText.= 'Person';
    $shortnoticeText.= '</th>';
    $shortnoticeText.= '<th>';
    $shortnoticeText.= 'Requested';
    $shortnoticeText.= '</th>';
	/* View part day leave using leave admin */
    $shortnoticeText.= '<th>';
	$shortnoticeText.= 'Part Day';
    $shortnoticeText.= '</th>';
	$shortnoticeText.= '<th>';
    $shortnoticeText.= 'Comments';
    $shortnoticeText.= '</th>';
    $shortnoticeText.= '</tr>';
    $shortnoticeText.= '</thead>';
    $shortnoticeText.= '<tbody>';
    if (isset($arrLeave['LeaveRequests']['ShortNotice'])) {
      foreach ($arrLeave['LeaveRequests']['ShortNotice'] as $strDate => $arrLeaveDates) {
        foreach ($arrLeaveDates as $intRequestID => $arrRequest) {
          if ($arrRequest['Unlikely'] == 0 && $arrRequest['IsAgreed'] != 1) {
            $getWeekandDayArr = GetAllocationWeekandDay($strDate);
            $intCurrWeek = $getWeekandDayArr['ixYearWeek'];
            $intGroupID = $arrRequest['GroupID'];
            $shortnoticeText.= '<tr class="handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')">';
            $shortnoticeText.= '<td>';
            $shortnoticeText.= $arrRequest['GroupDescription'];
            $shortnoticeText.= '</td>';

            $shortnoticeText.= '<td>';
            $shortnoticeText.= $arrRequest['TypeDescription'];
            $shortnoticeText.= '</td>';

            $shortnoticeText.= '<td>';
            $shortnoticeText.= '<span class="customdateSort">'.date("Y-m-d", strtotime($strDate)).'</span>';
            $shortnoticeText.= date("l, jS M Y", strtotime($strDate));
            $shortnoticeText.= '</td>';

            $shortnoticeText.= '<td>';
            $shortnoticeText.= spinweek($intCurrWeek);
            $shortnoticeText.= '</td>';

            $shortnoticeText.= '<td>';
            $shortnoticeText.= $arrRequest['FullName'];
            $shortnoticeText.= '</td>';

            $shortnoticeText.= '<td>';
            $shortnoticeText.= '<span class="customdateSort">'.date("Y-m-d", strtotime($arrRequest['Created'])).'</span>'.getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']) .' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
            $shortnoticeText.= '</td>';

			if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
			$arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');
			}
			else{$arrRequestPdl ='';}
			$shortnoticeText.= '<td>';
			$shortnoticeText.= $arrRequestPdl;
            $shortnoticeText.= '</td>';

            $shortnoticeText.= '<td>';
            if ($arrRequest['UserComments'] != '') {
              $shortnoticeText.= '<b>User Comments</b><br>';
              $shortnoticeText.= $arrRequest['UserComments'].'<br>';
            }
            if ($arrRequest['OfficeComments'] != '') {
              $shortnoticeText.= '<b>Office Comments</b><br>';
              $shortnoticeText.= $arrRequest['OfficeComments'].'<br>';
            }
            $shortnoticeText.= '</td>';
            $shortnoticeText.= '</tr>';
          }
        }
      }
    }
    $shortnoticeText.= '</tbody>';
    $shortnoticeText.= '</table>';
    echo $shortnoticeText;
          ?>
<script type="text/javascript">
$(document).ready(function () {
    var yadcfColumns = [
        { column_number: 0, filter_type: 'select', aria_label: 'Group' },
        { column_number: 1, filter_type: 'text', aria_label: 'Type' },
        { column_number: 3, filter_type: 'text', aria_label: 'Week' },
        { column_number: 4, filter_type: 'text', aria_label: 'Person' },
        { column_number: 7, filter_type: 'text', aria_label: 'Comments' }
    ];
    var table = $('#shortnotice').DataTable({
        paging: false,
        scrollY: parseInt($(window).height() - 250),
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        initComplete: function (settings, json) {
            $('#shortnotice').attr('aria-label', 'Short notice table');
            $('#shortnotice thead th').each(function (index) {
                let columnTitle = $(this).clone().children().remove().end().text().trim() || 'Column ' + (index + 1);
                $(this).attr('aria-label', columnTitle + ' column header');
            });
            if ($('#shortnotice').length) {
                ResizeShortNoticeGrid();
            }
        }
    });
    yadcf.init(table, yadcfColumns);
    setTimeout(function () {
        yadcfColumns.forEach(function (colConfig) {
            let colNum = colConfig.column_number;
            let ariaLabel = colConfig.aria_label;
            if (!ariaLabel) return;
            let filterDivId = '#yadcf-filter--shortnotice-' + colNum;
            let $filterElement = $(filterDivId).find('select, input').first();
            if ($filterElement.length) {
                $filterElement.attr('aria-label', ariaLabel);
            }
            $(filterDivId).attr('aria-label', ariaLabel);
        });
    }, 300);
});

function ResizeShortNoticeGrid () {
  if ($("#shortnotice").length) {
    var offset = parseInt($("#shortnotice").offset().top);
    var windowheight = $(window).height() - offset - 30;
    $('.dataTables_scrollBody:has(#shortnotice)').height(windowheight+'px');
    $('#shortnotice').dataTable().fnAdjustColumnSizing();
  }
}
$(window).resize(function() {
  if ($("#shortnotice").length) {
    ResizeShortNoticeGrid();
  }
})
</script>
    <?php


    // ##################################################################################  END Do the ShortNotice
    break;
   case 1:
      $leaveOKText ='';
      // ##################################################################################  IsOK Leave
      $leaveOKText.= '<table class="tablesmall compact stripe w-100" id="leaveOK">';
      $leaveOKText.='<thead>';
      $leaveOKText.= '<tr>';
      $leaveOKText.= '<th>';
      $leaveOKText.= 'Group';
      $leaveOKText.= '</th>';
      $leaveOKText.= '<th>';
      $leaveOKText.= 'Type';
      $leaveOKText.= '</th>';
      $leaveOKText.= '<th>';
      $leaveOKText.= 'Date';
      $leaveOKText.= '</th>';
      $leaveOKText.= '<th>';
      $leaveOKText.= 'Week';
      $leaveOKText.= '</th>';
      $leaveOKText.= '<th>';
      $leaveOKText.= 'Person';
      $leaveOKText.= '</th>';
      $leaveOKText.= '<th>';
      $leaveOKText.= 'Requested';
      $leaveOKText.= '</th>';
	  /* View part day leave using leave admin */
	  $leaveOKText.= '<th>';
      $leaveOKText.= 'Part Day';
      $leaveOKText.= '</th>';
      $leaveOKText.= '<th>';
      $leaveOKText.= 'Comments';
      $leaveOKText.= '</th>';
      $leaveOKText.= '</tr>';
      $leaveOKText.= '</thead>';
      $leaveOKText.= '<tbody>';
      if (isset($arrLeave['LeaveRequests']['General'])) {
        foreach ($arrLeave['LeaveRequests']['General'] as $strDate => $arrLeaveDates) {
          foreach ($arrLeaveDates as $intRequestID => $arrRequest) {
            if ($arrRequest['IsOK'] == 1 && $arrRequest['Unlikely'] ==0 && $arrRequest['IsAgreed'] != 1) {
              $getWeekandDayArr = GetAllocationWeekandDay($strDate);
              $intCurrWeek = $getWeekandDayArr['ixYearWeek'];
              $intGroupID = $arrRequest['GroupID'];
              $leaveOKText.= '<tr class="handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')">';
              $leaveOKText.= '<td>';
              $leaveOKText.= $arrRequest['GroupDescription'];
              $leaveOKText.= '</td>';

              $leaveOKText.= '<td>';
              $leaveOKText.= $arrRequest['TypeDescription'];
              $leaveOKText.= '</td>';

              $leaveOKText.= '<td>';
              $leaveOKText.= '<span class="customdateSort">'.date("Y-m-d", strtotime($strDate)).'</span>';
              $leaveOKText.= date("l, jS M Y", strtotime($strDate));
              $leaveOKText.= '</td>';

              $leaveOKText.= '<td>';
              $leaveOKText.= spinweek($intCurrWeek);
              $leaveOKText.= '</td>';

              $leaveOKText.= '<td>';
              $leaveOKText.= $arrRequest['FullName'];
              $leaveOKText.= '</td>';

              $leaveOKText.= '<td>';
              $leaveOKText.= '<span class="customdateSort">'.date("Y-m-d", strtotime($arrRequest['Created'])).'</span>'.getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']). ' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
              $leaveOKText.= '</td>';

			  if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
			  $arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');
			  }
			  else{$arrRequestPdl ='';}
			  $leaveOKText.= '<td nowrap>';
              $leaveOKText.= $arrRequestPdl;
              $leaveOKText.= '</td>';
              $leaveOKText.= '<td>';
              if ($arrRequest['UserComments'] != '') {
                $leaveOKText.= '<b>User Comments</b><br>';
                $leaveOKText.= $arrRequest['UserComments'].'<br>';
              }
              if ($arrRequest['OfficeComments'] != '') {
                $leaveOKText.= '<b>Office Comments</b><br>';
                $leaveOKText.= $arrRequest['OfficeComments'].'<br>';
              }
              $leaveOKText.= '</td>';

              $leaveOKText.= '</tr>';
            }
          }
        }
      }
      $leaveOKText.= '</tbody>';
      $leaveOKText.= '</table>';

     echo $leaveOKText;
?>
<script type="text/javascript">
$(document).ready(function(){
    var yadcfColumns = [
        { column_number: 0, filter_type: 'select', aria_label: 'Group' },
        { column_number: 1, filter_type: 'text', aria_label: 'Type' },
        { column_number: 3, filter_type: 'text', aria_label: 'Week' },
        { column_number: 4, filter_type: 'text', aria_label: 'Person' },
        { column_number: 7, filter_type: 'text', aria_label: 'Comments' }
    ];
    var table = $('#leaveOK').DataTable({
        paging: false,
        scrollY: parseInt($(window).height() - 250),
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        initComplete: function (settings, json) {
            $('#leaveOK').attr('aria-label', 'Guaranteed table');
            $('#leaveOK thead th').each(function (index) {
                let columnTitle = $(this).clone().children().remove().end().text().trim() || 'Column ' + (index + 1);
                $(this).attr('aria-label', columnTitle + ' column header');
            });
            if ($('#leaveOK').length) {
                ResizeLeaveOKGrid();
            }
        }
    });
    yadcf.init(table, yadcfColumns);
    setTimeout(function () {
        yadcfColumns.forEach(function (colConfig) {
            let colNum = colConfig.column_number;
            let ariaLabel = colConfig.aria_label;
            if (!ariaLabel) return;
            let filterDivId = '#yadcf-filter--leaveOK-' + colNum;
            let $filterElement = $(filterDivId).find('select, input').first();
            if ($filterElement.length) {
                $filterElement.attr('aria-label', ariaLabel);
            }
            $(filterDivId).attr('aria-label', ariaLabel);
        });
    }, 300);
});
function ResizeLeaveOKGrid () {
	if($("#leaveOK").length) {
	  var offset = parseInt($("#leaveOK").offset().top);
	  var windowheight = $(window).height() - offset - 30;
	  $('.dataTables_scrollBody:has(#leaveOK)').height(windowheight+'px');
	  $('#leaveOK').dataTable().fnAdjustColumnSizing();
	}
}
$(window).resize(function() {
  if($("#leaveOK").length) {
    ResizeLeaveOKGrid();
  }
})
</script>
<?php
      break;
    case 2:
      $leaveNotOK='';
      // ##################################################################################  Is NOT OK Leave
      $leaveNotOK.= '<table class="tablesmall compact stripe w-100" id="leaveNotOK">';
      $leaveNotOK.= '<thead>';
      $leaveNotOK.= '<tr>';
      $leaveNotOK.= '<th>';
      $leaveNotOK.= 'Group';
      $leaveNotOK.= '</th>';
      $leaveNotOK.= '<th>';
      $leaveNotOK.= 'Type';
      $leaveNotOK.= '</th>';
      $leaveNotOK.= '<th>';
      $leaveNotOK.= 'Date';
      $leaveNotOK.= '</th>';
      $leaveNotOK.= '<th>';
      $leaveNotOK.= 'Week';
      $leaveNotOK.= '</th>';
      $leaveNotOK.= '<th>';
      $leaveNotOK.= 'Person';
      $leaveNotOK.= '</th>';
      $leaveNotOK.= '<th>';
      $leaveNotOK.= 'Requested';
      $leaveNotOK.= '</th>';
      /* View part day leave using leave admin */
	  $leaveNotOK.= '<th>';
      $leaveNotOK.= 'Part Day';
      $leaveNotOK.= '</th>';
      $leaveNotOK.= '<th>';
      $leaveNotOK.= 'Comments';
      $leaveNotOK.= '</th>';
      $leaveNotOK.= '</tr>';
      $leaveNotOK.= '</thead>';
      $leaveNotOK.= '<tbody>';
      if (isset($arrLeave['LeaveRequests']['General'])) {
        foreach ($arrLeave['LeaveRequests']['General'] as $strDate => $arrLeaveDates) {
          foreach ($arrLeaveDates as $intRequestID => $arrRequest) {
            if ($arrRequest['IsOK'] == 0 && $arrRequest['Unlikely'] ==0 && $arrRequest['IsAgreed'] != 1) {
              $getWeekandDayArr = GetAllocationWeekandDay($strDate);
              $intCurrWeek = $getWeekandDayArr['ixYearWeek'];
              $intGroupID = $arrRequest['GroupID'];
              $leaveNotOK.= '<tr class="handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')">';
              $leaveNotOK.= '<td>';
              $leaveNotOK.= $arrRequest['GroupDescription'];
              $leaveNotOK.= '</td>';

              $leaveNotOK.= '<td>';
              $leaveNotOK.= $arrRequest['TypeDescription'];
              $leaveNotOK.= '</td>';

              $leaveNotOK.= '<td>';
              $leaveNotOK.= '<span class="customdateSort">'.date("Y-m-d", strtotime($strDate)).'</span>';
              $leaveNotOK.= date("l, jS M Y", strtotime($strDate));
              $leaveNotOK.= '</td>';

              $leaveNotOK.= '<td>';
              $leaveNotOK.= spinweek($intCurrWeek);
              $leaveNotOK.= '</td>';

              $leaveNotOK.= '<td>';
              $leaveNotOK.= $arrRequest['FullName'];
              $leaveNotOK.= '</td>';

			  $leaveNotOK.= '<td>';
              $leaveNotOK.= '<span class="customdateSort">'.date("Y-m-d", strtotime($arrRequest['Created'])).'</span>'.getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']) .' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
              $leaveNotOK.= '</td>';

			  if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
			  $arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');
			  }
			  else{$arrRequestPdl ='';}
			  $leaveNotOK.= '<td nowrap>';
              $leaveNotOK.= $arrRequestPdl;
              $leaveNotOK.= '</td>';

              $leaveNotOK.= '<td>';
              if ($arrRequest['UserComments'] != '') {
                $leaveNotOK.= '<b>User Comments</b><br>';
                $leaveNotOK.= $arrRequest['UserComments'].'<br>';
              }
              if ($arrRequest['OfficeComments'] != '') {
                $leaveNotOK.= '<b>Office Comments</b><br>';
                $leaveNotOK.= $arrRequest['OfficeComments'].'<br>';
              }
              $leaveNotOK.= '</td>';

              $leaveNotOK.= '</tr>';
            }
          }
        }
      }
      $leaveNotOK.= '</tbody>';
      $leaveNotOK.= '</table>';
      echo $leaveNotOK;
?>
<script type="text/javascript">
$(document).ready(function() {
    var yadcfColumns = [
        { column_number: 0, filter_type: 'select', aria_label: 'Group' },
        { column_number: 1, filter_type: 'text', aria_label: 'Type' },
        { column_number: 3, filter_type: 'text', aria_label: 'Week' },
        { column_number: 4, filter_type: 'text', aria_label: 'Person' },
        { column_number: 7, filter_type: 'text', aria_label: 'Comments' }
    ];
    var table = $('#leaveNotOK').DataTable({
        paging: false,
        scrollY: parseInt($(window).height() - 250),
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        initComplete: function (settings, json) {
            $('#leaveNotOK').attr('aria-label', 'Waiting List table');
            $('#leaveNotOK thead th').each(function (index) {
                let columnTitle = $(this).clone().children().remove().end().text().trim() || 'Column ' + (index + 1);
                $(this).attr('aria-label', columnTitle + ' column header');
            });
            if ($('#leaveNotOK').length) {
                ResizeLeaveNotOKGrid();
            }
        }
    });
    yadcf.init(table, yadcfColumns);
    setTimeout(function () {
        yadcfColumns.forEach(function (colConfig) {
            let colNum = colConfig.column_number;
            let ariaLabel = colConfig.aria_label;
            if (!ariaLabel) return;
            let filterDivId = '#yadcf-filter--leaveNotOK-' + colNum;
            let $filterElement = $(filterDivId).find('select, input').first();
            if ($filterElement.length) {
                $filterElement.attr('aria-label', ariaLabel);
            }
            $(filterDivId).attr('aria-label', ariaLabel);
        });
    }, 300);
})
function ResizeLeaveNotOKGrid () {
	if($("#leaveNotOK").length) {
	  var offset = parseInt($("#leaveNotOK").offset().top);
	  var windowheight = $(window).height() - offset - 30;
	  $('.dataTables_scrollBody:has(#leaveNotOK)').height(windowheight+'px');
	  $('#leaveNotOK').dataTable().fnAdjustColumnSizing();
	}
}
$(window).resize(function() {
  if($("#leaveNotOK").length) {
   ResizeLeaveNotOKGrid();
  }
})
</script>
<?php
      // ##################################################################################  END Is NOT OK Leave
      break;

    case 3:
      $leaveUnlikely='';
    // ##################################################################################  Is marked Unlikely
    // ##################################################################################  Do the ShortNotice
    $leaveUnlikely.= '<table class="tablesmall compact stripe w-100" id="leaveUnlikely">';
    $leaveUnlikely.= '<thead>';
    $leaveUnlikely.='<tr>';
    $leaveUnlikely.= '<th>';
    $leaveUnlikely.= 'Group';
    $leaveUnlikely.='</th>';
    $leaveUnlikely.= '<th>';
    $leaveUnlikely.= 'Type';
    $leaveUnlikely.= '</th>';
    $leaveUnlikely.= '<th>';
    $leaveUnlikely.= 'Date';
    $leaveUnlikely.= '</th>';
    $leaveUnlikely.= '<th>';
    $leaveUnlikely.= 'Week';
    $leaveUnlikely.= '</th>';
    $leaveUnlikely.= '<th>';
    $leaveUnlikely.= 'Person';
    $leaveUnlikely.= '</th>';
    $leaveUnlikely.= '<th>';
    $leaveUnlikely.= 'Requested';
    $leaveUnlikely.= '</th>';
	/* View part day leave using leave admin */
	$leaveUnlikely.= '<th>';
    $leaveUnlikely.= 'Part Day';
    $leaveUnlikely.= '</th>';
    $leaveUnlikely.= '<th>';
    $leaveUnlikely.= 'Comments';
    $leaveUnlikely.= '</th>';
    $leaveUnlikely.= '<th>';
    $leaveUnlikely.='Is OK';
    $leaveUnlikely.= '</th>';
    $leaveUnlikely.='</tr>';
    $leaveUnlikely.='</thead>';
    $leaveUnlikely.= '<tbody>';
    if (isset($arrLeave['LeaveRequests']['ShortNotice'])) {
      foreach ($arrLeave['LeaveRequests']['ShortNotice'] as $strDate => $arrLeaveDates) {
        foreach ($arrLeaveDates as $intRequestID => $arrRequest) {
          if ($arrRequest['Unlikely'] == 1 && $arrRequest['IsAgreed'] != 1) {
            $getWeekandDayArr = GetAllocationWeekandDay($strDate);
            $intCurrWeek = $getWeekandDayArr['ixYearWeek'];
            $intGroupID = $arrRequest['GroupID'];
            $leaveUnlikely.= '<tr class="handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')">';

            $leaveUnlikely.= '<td>';
            $leaveUnlikely.= $arrRequest['GroupDescription'];
            $leaveUnlikely.='</td>';

            $leaveUnlikely.= '<td>';
            $leaveUnlikely.= $arrRequest['TypeDescription'];
            $leaveUnlikely.= '</td>';

            $leaveUnlikely.= '<td>';
            $leaveUnlikely.= '<span class="customdateSort">'.date("Y-m-d", strtotime($strDate)).'</span>';
            $leaveUnlikely.= date("l, jS M Y", strtotime($strDate));
            $leaveUnlikely.= '</td>';

            $leaveUnlikely.= '<td>';
            $leaveUnlikely.= spinweek($intCurrWeek);
            $leaveUnlikely.= '</td>';

            $leaveUnlikely.= '<td>';
            $leaveUnlikely.= $arrRequest['FullName'];
            $leaveUnlikely.= '</td>';

            $leaveUnlikely.= '<td>';
            $leaveUnlikely.= '<span class="customdateSort">'.date("Y-m-d", strtotime($arrRequest['Created'])).'</span>'.getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']).' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
            $leaveUnlikely.= '</td>';

			if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
			$arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');
			}
			else{$arrRequestPdl ='';}
			$leaveUnlikely.= '<td>';
            $leaveUnlikely.= $arrRequestPdl;
            $leaveUnlikely.= '</td>';
            $leaveUnlikely.= '<td>';
            if ($arrRequest['UserComments'] != '') {
              $leaveUnlikely.= '<b>User Comments</b><br>';
              $leaveUnlikely.= $arrRequest['UserComments'].'<br>';
            }
            if ($arrRequest['OfficeComments'] != '') {
              $leaveUnlikely.= '<b>Office Comments</b><br>';
              $leaveUnlikely.= $arrRequest['OfficeComments'].'<br>';
            }
            $leaveUnlikely.= '</td>';

            $leaveUnlikely.= '<td class="LeaveShortNoticeApplied">';
            $leaveUnlikely.= '</td>';

            $leaveUnlikely.= '</tr>';
          }
        }
      }
    }
    // ##################################################################################  END Do the ShortNotice
      if (isset($arrLeave['LeaveRequests']['General'])) {
        foreach ($arrLeave['LeaveRequests']['General'] as $strDate => $arrLeaveDates) {
          foreach ($arrLeaveDates as $intRequestID => $arrRequest) {
            if ($arrRequest['Unlikely'] == 1) {
              $getWeekandDayArr = GetAllocationWeekandDay($strDate);
              $intCurrWeek = $getWeekandDayArr['ixYearWeek'];
              $intGroupID = $arrRequest['GroupID'];
              $leaveUnlikely.= '<tr class="handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')">';

              $leaveUnlikely.= '<td>';
              $leaveUnlikely.= $arrRequest['GroupDescription'];
              $leaveUnlikely.= '</td>';

              $leaveUnlikely.= '<td>';
              $leaveUnlikely.= $arrRequest['TypeDescription'];
              $leaveUnlikely.= '</td>';

              $leaveUnlikely.= '<td>';
              $leaveUnlikely.= '<span class="customdateSort">'.date("Y-m-d", strtotime($strDate)).'</span>';
              $leaveUnlikely.= date("l, jS M Y", strtotime($strDate));
              $leaveUnlikely.= '</td>';

              $leaveUnlikely.= '<td>';
              $leaveUnlikely.= spinweek($intCurrWeek);
              $leaveUnlikely.= '</td>';

              $leaveUnlikely.= '<td>';
              $leaveUnlikely.= $arrRequest['FullName'];
              $leaveUnlikely.= '</td>';

              $leaveUnlikely.= '<td>';
              $leaveUnlikely.= '<span class="customdateSort">'.date("Y-m-d", strtotime($arrRequest['Created'])).'</span>'.getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']).' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
              $leaveUnlikely.= '</td>';

			  if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
			  $arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');
			  }
			  else{$arrRequestPdl ='';}
			  $leaveUnlikely.= '<td>';
			  $leaveUnlikely.= $arrRequestPdl;
			  $leaveUnlikely.= '</td>';
              $leaveUnlikely.= '<td>';
              if ($arrRequest['UserComments'] != '') {
                $leaveUnlikely.= '<b>User Comments</b><br>';
                $leaveUnlikely.= $arrRequest['UserComments'].'<br>';
              }
              if ($arrRequest['OfficeComments'] != '') {
                $leaveUnlikely.= '<b>Office Comments</b><br>';
                $leaveUnlikely.= $arrRequest['OfficeComments'].'<br>';
              }
              $leaveUnlikely.= '</td>';

              $leaveUnlikely.= '<td align="center">';
              $leaveUnlikely.= '<span class="customdateSort">'.$arrRequest['IsOK'].'</span>';
              if ($arrRequest['IsOK'] == 1) {
                $leaveUnlikely.= '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';
              } else {
                $leaveUnlikely.= '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';
              }
              $leaveUnlikely.= '</td>';

              $leaveUnlikely.= '</tr>';
            }
          }
        }
      }
      $leaveUnlikely.= '</table>';
      echo $leaveUnlikely;
          ?>
 <script type="text/javascript">
$(document).ready(function(){
    var yadcfColumns = [
        { column_number: 0, filter_type: 'select', aria_label: 'Group' },
        { column_number: 1, filter_type: 'text', aria_label: 'Type' },
        { column_number: 3, filter_type: 'text', aria_label: 'Week' },
        { column_number: 4, filter_type: 'text', aria_label: 'Person' },
        { column_number: 7, filter_type: 'text', aria_label: 'Comments' }
    ];
    var table = $('#leaveUnlikely').DataTable({
        paging: false,
        scrollY: parseInt($(window).height() - 95),
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        initComplete: function (settings, json) {
            $('#leaveUnlikely').attr('aria-label', 'Unlikely List table');
            $('#leaveUnlikely thead th').each(function (index) {
                let columnTitle = $(this).clone().children().remove().end().text().trim() || 'Column ' + (index + 1);
                $(this).attr('aria-label', columnTitle + ' column header');
            });
            if ($('#leaveUnlikely').length) {
                ResizeLeaveUnlikelyGrid();
            }
        }
    });
    yadcf.init(table, yadcfColumns);
    setTimeout(function () {
        yadcfColumns.forEach(function (colConfig) {
            let colNum = colConfig.column_number;
            let ariaLabel = colConfig.aria_label;
            if (!ariaLabel) return;
            let filterDivId = '#yadcf-filter--leaveUnlikely-' + colNum;
            let $filterElement = $(filterDivId).find('select, input').first();
            if ($filterElement.length) {
                $filterElement.attr('aria-label', ariaLabel);
            }
            $(filterDivId).attr('aria-label', ariaLabel);
        });
    }, 300);
});
function ResizeLeaveUnlikelyGrid() {
  if ($("#leaveUnlikely").length) {
    var offset = parseInt($("#leaveUnlikely").offset().top);
    var windowheight = $(window).height() - offset - 30;
    $('.dataTables_scrollBody:has(#leaveUnlikely)').height(windowheight+'px');
    $(".dataTables_scrollBody").css({ "overflow-y": "hidden","max-height":"395px" });
    $('#leaveUnlikely').dataTable().fnAdjustColumnSizing();
  }
}
$(window).resize(function() {
  if ($("#leaveUnlikely").length) {
    ResizeLeaveUnlikelyGrid();
  }
})
</script>
<?php
break;
    // ##################################################################################  END Is NOT OK Leave
    /** Add Yearly Staff Leave */
    case 8:
    // ##################################################################################  Is StaffYearlyLeaves
    echo '<div id="StaffYearlyLeaves">';
    include_once 'yearlyLeaveFilter.php';
    /* Yearly Leave Start */
    $currentLeaveYear = $intLeaveYear = $selectedYear = GetCurrentLeaveYearGeneric();
    $keys=[];
    $values=[];
    $formdataArr=[];
    $checked_approved=null;
    $checked_pending=null;
    $checked_deleted=null;
    $checked_agreed=null;
    $canView =0;
    $result['DataInput'] = readYearlyFilter();

    if (isset($result['DataInput']) && !empty($result['DataInput'])) {
      parse_str($result['DataInput'], $formdataArr);
      $keys = array_keys($formdataArr);
      $values = array_values($formdataArr);
    }

    if (!empty($keys) && in_array('filter_approved',$keys)) {
      $keyno = array_search('filter_approved',$keys);
      $checked_approved= 'checked';
    } else {
      $checked_approved= '';
    }

    if (!empty($keys) && in_array('filter_pending',$keys)) {
      $keyno = array_search('filter_pending',$keys);
      $checked_pending= 'checked';
    } else {
      $checked_pending= '';
    }

    if (!empty($keys) && in_array('filter_deleted',$keys)) {
      $keyno = array_search('filter_deleted',$keys);
      $checked_deleted= 'checked';
    } else {
      $checked_deleted='';
    }

    if (!empty($keys) && in_array('filter_agreed',$keys)) {
      $keyno = array_search('filter_agreed',$keys);
      $checked_agreed= 'checked';
    } else {
      $checked_agreed='';
    }

    if (!empty($keys) && in_array('selectedyear',$keys)) {
      $keyno = array_search('selectedyear',$keys);
      if ($values[$keyno] != '') {
        $intLeaveYear = $values[$keyno];
        $selectedYear = $values[$keyno];
      }
    }

    if ($selectedYear >= $currentLeaveYear) {
      $canView =1;
    } else {
      if ($selectedYear < $currentLeaveYear) {
        $diff = ($currentLeaveYear-$selectedYear);
        $tillDateApprove = ($currentLeaveYear).'-06-30';
        if ($diff==1 && date('Y-m-d') <= $tillDateApprove)
        {
          $canView =1;
        }
      }
  }

  $dteStartDate = $intLeaveYear.'-04-01';
  $dteEndDate = ($intLeaveYear + 1).'-03-31';
  $LeaveAdminGroupsData = GetLeaveAdminGroups($strUser );
  $arrLeaveAdmingrps = [];
  $arrGroupAminType = [];
  if (!empty($LeaveAdminGroupsData)) {
	  foreach ($LeaveAdminGroupsData as $value) {
      array_push($arrLeaveAdmingrps, $value['LeaveGroupID']);
      $arrGroupAminType[$value['LeaveGroupID']] = $value['Admin'];
	  }
  }
    if (!empty($keys) && in_array('ChooseUser', $keys)) {
      $keyno = array_search('ChooseUser', $keys);
      $selecteduser = strtoupper($values[$keyno]);
      $intScheduledPersonID = getScheduledPersonIDByNetLoginID($selecteduser);
      if ($intScheduledPersonID > 0) {
        $arrYearlyLeave = GetYealyLeaveInformationByNetLoginId($intScheduledPersonID, $strUser, $dteStartDate, $dteEndDate, $checked_approved, $checked_pending, $checked_deleted, $checked_agreed);
      } else {
        $selecteduser = '';
        $intScheduledPersonID = 0;
        $arrYearlyLeave = GetYealyLeaveInformationByNetLoginId($intScheduledPersonID, $strUser, $dteStartDate, $dteEndDate, $checked_approved, $checked_pending, $checked_deleted, $checked_agreed);
      }
    } else {
      $selecteduser = '';
      $intScheduledPersonID = 0;
      $arrYearlyLeave = GetYealyLeaveInformationByNetLoginId($intScheduledPersonID, $strUser, $dteStartDate, $dteEndDate, $checked_approved, $checked_pending, $checked_deleted, $checked_agreed);
    }
   $wrtline='';
   /* Yearly Leave End */
   $wrtline.='<div id="resYearlyLeaves">';
   $wrtline.= '<table class="tablesmall compact stripe fullwidth w-100" id="StaffYearlyLeavesTable">';
   $wrtline.= '<thead>';
   $wrtline.= '<tr>';
   $wrtline.= '<th>';
   $wrtline.= '<input type="hidden" value="'.$currentLeaveYear.'" id="genericLeaveYear" name="genericLeaveYear" readonly/>';
   $wrtline.= '<input type="hidden" value="'.$canView.'" id="isManage" name="isManage" />';
   $wrtline.=  '<input type="hidden" value="'.$intSysAdmin.'" id="issystest" name="systest" />';
   $wrtline.='<input type="hidden" value="'.$isSchedulingTeamAdmin.'" id="isSchedulingTeamAdmin" name="isSchedulingTeamAdmin" readonly/>';
   $wrtline.=  '<input type="hidden" value="'.$isDivisionalAdmin.'" id="isdivisionalAdmin" name="isdivisionalAdmin" readonly/>Group';
   $wrtline.= '</th>';
   $wrtline.= '<th>';
   $wrtline.= 'Type';
   $wrtline.= '</th>';
   $wrtline.= '<th>';
   $wrtline.= 'Date';
   $wrtline.= '</th>';
   $wrtline.= '<th>';
   $wrtline.= 'Week';
   $wrtline.= '</th>';
   $wrtline.= '<th>';
   $wrtline.= 'Person';
   $wrtline.= '</th>';
   $wrtline.= '<th>';
   $wrtline.= 'Requested';
   $wrtline.= '</th>';
   /* View part day leave using leave admin */
   $wrtline.= '<th>';
   $wrtline.= 'Part Day';
   $wrtline.= '</th>';
   $wrtline.= '<th>';
   $wrtline.= 'Comments';
   $wrtline.= '</th>';
   $wrtline.= '<th>';
   $wrtline.= 'Is OK';
   $wrtline.= '</th>';
   $wrtline.= '</tr>';
   $wrtline.= '</thead>';
   $wrtline.= '<tbody>';
  if (isset($arrYearlyLeave['LeaveRequests']) && !empty($arrYearlyLeave['LeaveRequests'])) {
    foreach ($arrYearlyLeave['LeaveRequests'] as $strDate => $arrLeaveDates) {
      foreach ($arrLeaveDates as $intRequestID => $arrRequest) {
       if (!empty($arrRequest['GroupID'])) {
       $class='';
      if ($arrRequest['Approved']) {
          if ($arrRequest['CountLeave'] == 1) {
            $class=" LeaveApproved";
          } else {
            $class=" LeaveHashedOrange";
          }

      } else {
        if ($arrRequest['Deleted'] == 1) {
          $class=" LeaveDeleted";
        } else {
          if ($arrRequest['IsAgreed'] == 1) {
            $class=" LeaveAgreed";
          } else if ($arrRequest['ShortNotice'] == 1) {
            $class=" LeaveShortNoticeApplied";
          } else {
            if ($arrRequest['OverSummer'] == 1) {
              $class=" LeaveOverSummer";
            } else {
              if ($arrRequest['IsOK']  == 1) {
                $class=" LeaveOK";
              } else {
                $class=" LeaveNotOK";
              }
            }
              if ($arrRequest['Deleted']) {
                $class=" LeaveDeleted";
              }
          }
        }
      }
      $getWeekandDayArr = GetAllocationWeekandDay($strDate);
      $intCurrWeek = $getWeekandDayArr['ixYearWeek'];
      $intGroupID = $arrRequest['GroupID'];
	  
	  if($arrRequest['AllocationID']!=''){$allocationId = $arrRequest['AllocationID'];}else{$allocationId = 0;}
	  
      $wrtline.= '<tr id="'.$arrRequest['ID'].'" callpage="'.$intLinksAdmin.'" showoptions="'.$showOptions.'" week="'.$intCurrWeek.'" data-approved="' . $arrRequest['Approved'] . '" data-agreed="' . $arrRequest['IsAgreed'] . '" data-leave-group-id="'.$intGroupID.'" data-leave-allocation-id="'.$allocationId.'" data-admin-type="' . ($arrGroupAminType[$arrRequest['GroupID']] ?? 0) . '"';
         if ($intSysAdmin==1 || $isDivisionalAdmin==1 || $canView==1 || $isSchedulingTeamAdmin==1) {
           if ($arrRequest['Deleted'] != 1) {
            $wrtline.= 'onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')"';
            if (in_array($arrRequest['GroupID'],$arrLeaveAdmingrps)){
				$wrtline.='class="handcursor context-menu-yearly "';
			} else {
				$wrtline.='class="handcursor context-menu-yearly-onlyhistory "';
			}
           } else {
			   if (in_array($arrRequest['GroupID'],$arrLeaveAdmingrps))
			   {
					$wrtline.=  'class=" context-menu-d-yearly"';
			   }else
			   {
				   $wrtline.=  'class=" context-menu-yearly-onlyhistory"';
			   }
           }
         } else {
          $wrtline.= 'class=" context-menu-yearly-onlyhistory"';
         }
         $wrtline.= '>';

         $wrtline.= '<td class="'.$class.'">';
         $wrtline.= $arrRequest['GroupDescription'];
         $wrtline.= '</td>';

         $wrtline.= '<td class="'.$class.'">';
         $wrtline.= $arrRequest['TypeDescription'];
         $wrtline.= '</td>';

         $wrtline.= '<td class="'.$class.'">';
         $wrtline.= '<span class="customdateSort">'.date("Y-m-d", strtotime($strDate)).'</span>'.date("l, jS M Y", strtotime($strDate));
         $wrtline.= '</td>';

         $wrtline.= '<td class="'.$class.'">';
         $wrtline.= spinweek($intCurrWeek);
         $wrtline.= '</td>';

         $wrtline.= '<td class="'.$class.'">';
         $wrtline.= $arrRequest['FullName'];
         $wrtline.= '</td>';


         $wrtline.= '<td class="'.$class.'">';
         $wrtline.= '<span class="customdateSort">'.date("Y-m-d", strtotime($arrRequest['Created'])).'</span>'.getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']).' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
         $wrtline.= '</td>';

		 if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
		 $arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');
	     }
		 else{$arrRequestPdl ='';}
		 $wrtline.= '<td class="'.$class.'">';
         $wrtline.= $arrRequestPdl;
         $wrtline.= '</td>';
         $wrtline.= '<td class="'.$class.'">';
         if (!empty($arrRequest['UserComments'])) {
          $wrtline.= '<b>User Comments</b><br>';
		  if($arrRequest['LeaveStartTime'] !='' && $arrRequest['LeaveEndTime'] !=''){
			$wrtline.= 'PDL: ';
		  }
          $wrtline.= $arrRequest['UserComments'].'<br>';
         }
         if (!empty($arrRequest['OfficeComments'])) {
         $wrtline.= '<b>Office Comments</b><br>';
		 if($arrRequest['LeaveStartTime'] !='' && $arrRequest['LeaveEndTime'] !=''){
		 $wrtline.= 'PDL: ';
		 }
         $wrtline.= $arrRequest['OfficeComments'].'<br>';
         }
         $wrtline.= '</td>';

         $wrtline.= '<td align="center" class="'.$class.'">';
         $wrtline.= '<span class="customdateSort">'.$arrRequest['IsOK'].'</span>';
         if ($arrRequest['IsOK'] == 1) {
          $wrtline.= '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';
         } else {
          $wrtline.= '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';
         }
         $wrtline.= '</td>';

         $wrtline.= '</tr>';
       }
     }
   }
 } else {
  $totalrow = 9;
  $middlerecord = round(9/2);
  $wrtline.= '<tr>';
    for($i = 1; $i<=$totalrow;$i++){
      if($middlerecord == $i){
        $wrtline.= '<td align="center">No Record Found</td>';
      }else{
        $wrtline.= '<td></td>';
      }
    }
    $wrtline.= '</tr>';
}
 // ##################################################################################  END StaffYearlyLeaves
 $wrtline.= '</tbody></table></div><div>';
 echo $wrtline;
            ?>
<script type="text/javascript">
$(document).ready(function() {
  var SystemAdmin =$('#issystest').val();
  var manegeble =$('#isManage').val();
  var isdivisionalAdmin =$('#isdivisionalAdmin').val();
  var isSchedulingTeamAdmin=$('#isSchedulingTeamAdmin').val();
  if(SystemAdmin==1 || isdivisionalAdmin==1 || manegeble==1 || isSchedulingTeamAdmin==1) {
          $('#addNewLeave').css('display','block');
        } else {
          $('#addNewLeave').css('display','none');
        }
  $(".chosen-select").chosen({
            no_results_text: "Oops, nothing found!",
            width: "65%"
        });
    readUpdatedFilter(SystemAdmin);
    <?php
     if (isset($arrYearlyLeave['LeaveRequests']) && !empty($arrYearlyLeave['LeaveRequests'])) {
    ?>
    var yadcfColumns = [
        { column_number: 0, filter_type: 'select', aria_label: 'Group' },
        { column_number: 1, filter_type: 'text', aria_label: 'Type' },
        { column_number: 3, filter_type: 'select', aria_label: 'Week' },
        { column_number: 7, filter_type: 'text', aria_label: 'Comments' }
    ];
    var table = $('#StaffYearlyLeavesTable').DataTable({
        paging: false,
        scrollY: 1000,
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        initComplete: function (settings, json) {
            $('#StaffYearlyLeavesTable').attr('aria-label', 'Yearly Staff Leave table');
            $('#StaffYearlyLeavesTable thead th').each(function (index) {
                let columnTitle = $(this).clone().children().remove().end().text().trim() || 'Column ' + (index + 1);
                $(this).attr('aria-label', columnTitle + ' column header');
            });
            if ($('#StaffYearlyLeavesTable').length) {
                ResizeStaffYearlyLeaveGrid();
            }
        }
    });
    yadcf.init(table, yadcfColumns);
    setTimeout(function () {
        yadcfColumns.forEach(function (colConfig) {
            let colNum = colConfig.column_number;
            let ariaLabel = colConfig.aria_label;
            if (!ariaLabel) return;
            let filterDivId = '#yadcf-filter--StaffYearlyLeavesTable-' + colNum;
            let $filterElement = $(filterDivId).find('select, input').first();
            if ($filterElement.length) {
                $filterElement.attr('aria-label', ariaLabel);
            }
            $(filterDivId).attr('aria-label', ariaLabel);
        });
    }, 300);
    <?php
     }
     ?>


     });

  function ResizeStaffYearlyLeaveGrid () {
    if($("#StaffYearlyLeavesTable").length){
        var offset = parseInt($("#StaffYearlyLeavesTable").offset().top);
        var windowheight = $(window).height() - offset - 50;
        $('.dataTables_scrollBody:has(#StaffYearlyLeavesTable)').height(windowheight+'px');
        $('#StaffYearlyLeavesTable').dataTable().fnAdjustColumnSizing();

    }
  }
  $(window).resize(function() {
    if ($("#StaffYearlyLeavesTable").length) {
      ResizeStaffYearlyLeaveGrid();
    }
});
/** Add New Leave */
function addNew()
{
  var user = $('#ChooseUser').val();
  var selectedyear = $('#selectedyear').val();
  if(user!=undefined && user!=0 &&  user!='') {
  $.ajax({
          url:"page-includes/leave/new-leave-popup.php",
          type:'POST',
          data:"userNetlogin="+user+"&LeaveYear="+selectedyear,
          dataType:"text",
          success:function(response,status,http) {
                $.facebox(response);
              },
          error: function(http,status,error){
            customAlert("Error Found :" + error);
              }
      });
    } else {
      customAlert("Please select someone's name in the 'Filter by Staff' drop down in order to add a leave record");
    }
}

 function ShowYearlyLeave(LYear) {
    var selectedyear = $('#selectedyear').val();
    if(LYear == 'prevYear') {
      var newyear = parseInt(selectedyear)-1;
    }else {
      var newyear = parseInt(selectedyear)+1;
    }
    /** Check LeaveYEar */
    var currentLeaveYear =$('#genericLeaveYear').val();
    var currentdate = new Date();
    if (newyear >= currentLeaveYear) {
      $('#isManage').val(1);
    } else if(newyear < currentLeaveYear) {
        var diff = (currentLeaveYear-newyear);
        var tillDateApprove = new Date(parseInt(currentLeaveYear), 05, 30);
        if (diff==1 && currentdate.getTime() <= tillDateApprove.getTime()) {
          $('#isManage').val(1);
        } else {
          $('#isManage').val(0);
        }
    }
    /** Check LeaveYEar */
    var user = $('#ChooseUser').val();
    $('#selectedyear').val(newyear);
    var formdata = $('#filterYealyLeave').serialize();
    if ((user != undefined && user != 0 &&  user != '') && (selectedyear != undefined && selectedyear != 0 &&  selectedyear != '')) {
        var ismanageble =$('#isManage').val();
        var isSystemAmin =$('#issystest').val();
        var isdivisionalAdmin =$('#isdivisionalAdmin').val();
        var isSchedulingTeamAdmin=$('#isSchedulingTeamAdmin').val();
        if(isSystemAmin==1 || isdivisionalAdmin==1 || ismanageble==1 || isSchedulingTeamAdmin==1) {
          $('#addNewLeave').css('display','block');
          $('#closeNewLeave').css('display','none');
        } else {
          $('#addNewLeave').css('display','none');
          $('#closeNewLeave').css('display','block');
        }
        $('#prevYear').html( ' << '+(newyear-1));
        $('#nextYear').html((parseInt(newyear)+1)+' >>');

        $.ajax({
        url:"page-includes/leave/save-filter.php",
        type:'POST',
        data:formdata,
        success:function(response,status,http) {
          getresultbyfilter();
        },
        error: function(http,status,error) {
          customAlert("Error Found :" + error);
            }
      });

  } else {
      customAlert("Please select someone's name in the 'Filter by Staff' drop down in order to view leave records.");
    }
}

  function ShowStaffYearlyLeave(user) {
    var user = $('#ChooseUser').val();
    var selectedyear = $('#selectedyear').val();
    var formdata = $('#filterYealyLeave').serialize();
    var SystemAdmin =$('#issystest').val();
  if ((user != undefined && user != 0 &&  user != '') && (selectedyear != undefined && selectedyear != 0 &&  selectedyear != '')) {
    $.ajax({
        url:"page-includes/leave/save-filter.php",
        type:'POST',
        data:formdata,
        success:function(response,status,http) {
          readUpdatedFilter(SystemAdmin);
          getresultbyfilter();
          $('#ChooseUser').val(user);
        },
        error: function(http,status,error) {
          customAlert("Error Found :" + error);
            }
      });
  } else {
     var currentLeaveYear =$('#genericLeaveYear').val();
      $('#selectedyear').val(currentLeaveYear);
      $('#prevYear').html( ' << '+(currentLeaveYear-1));
      $('#nextYear').html((parseInt(currentLeaveYear)+1)+' >>');
      $.ajax({
        url:"page-includes/leave/save-filter.php",
        type:'POST',
        data:formdata,
        success:function(response,status,http) {
         getresultbyfilter();
        },
        error: function(http,status,error) {
          customAlert("Error Found :" + error);
            }
      });
  }
}

function ApplyAdvanceFilter() {
   var user = $('#ChooseUser').val();
   var selectedyear = $('#selectedyear').val();
   var formdata = $('#filterYealyLeave').serialize();
  if ((user != undefined && user != 0 &&  user != '') && (selectedyear != undefined && selectedyear != 0 &&  selectedyear != '')) {
    $.ajax({
        url:"page-includes/leave/save-filter.php",
        type:'POST',
        data:formdata,
        success:function(response,status,http) {
          getresultbyfilter();
        },
        error: function(http,status,error) {
          customAlert("Error Found :" + error);
            }
      });
 } else {
     customAlert("Please select someone's name in the 'Filter by Staff' drop down in order to apply filter.");
     $('#filter_deleted').prop("checked",false);
     $('#filter_approved').prop("checked",false);
     $('#filter_pending').prop("checked",false);
     $('#filter_agreed').prop("checked",false);
    }
}

function getresultbyfilter() {
   var user = $('#ChooseUser').val();
   var selectedyear = $('#selectedyear').val();
   $("#resYearlyLeaves").empty();
  if ((user != undefined && user != 0 &&  user != '') && (selectedyear != undefined && selectedyear != 0 &&  selectedyear != '')) {
    $.ajax({
        url:"page-includes/leave/staff-leave-yearly.php",
        success:function(response,status,http) {
          $('#resYearlyLeaves').html(response);
        },
        error: function(http,status,error) {
          customAlert("Error Found :" + error);
            }
      });
 } else {
     $('#filter_deleted').prop("checked",false);
     $('#filter_approved').prop("checked",false);
     $('#filter_pending').prop("checked",false);
     $('#filter_agreed').prop("checked",false);
     $('#resYearlyLeaves').html('<table class="fullwidth"><tbody><tr align="center"><td colspan="8">Please select someone\'s name in the Filter by Staff drop down in order to view leave record</td></tr></tbody></table>');
    }
}


function saveSelectedFilter() {
  var formdata = $('#filterYealyLeave').serialize();
    if (formdata!='') {
    $.ajax({
        url:"page-includes/leave/save-filter.php",
        type:'POST',
        data:formdata,
        success:function(response,status,http) {
          var SystemAdmin =$('#issystest').val();
          readUpdatedFilter(SystemAdmin);
        },
        error: function(http,status,error) {
          customAlert("Error Found :" + error);
            }
      });

    } else {
        customAlert("Opps!! Some problem in filter saving.");
    }
}

function readUpdatedFilter(SystemAdmin) {

    $.ajax({
        url:"page-includes/leave/getyearlyFilter.php",
        type:'POST',
        success:function(response,status,http) {
         var filrres = response.split('&');
          $.each(filrres,function(index,value) {
            var innervalue=value;
            var innerres = innervalue.split('=');
            if(innerres[0]=='ChooseUser')
              {
                $('#ChooseUser').val(innerres[1]);
              }
              if(innerres[0]=='selectedyear')
              {
                var YEarVal = innerres[1];
                $('#selectedyear').val(YEarVal);
                $('#prevYear').text( ' << '+(YEarVal-1));
                $('#nextYear').text((parseInt(YEarVal)+1) +' >> ');

                  var isSystemAmin = SystemAdmin;
                  var isdivisionalAdmin =$('#isdivisionalAdmin').val();
				  var isSchedulingTeamAdmin=$('#isSchedulingTeamAdmin').val();
                  var selectedyear = YEarVal;
                  var currentLeaveYear =$('#genericLeaveYear').val();
                  var newyear = parseInt(selectedyear)-1;
                  var currentdate = new Date();
                  if (selectedyear >= currentLeaveYear) {
                  var ismanegeble=1;
                  } else if(newyear < currentLeaveYear) {
                    var diff = (currentLeaveYear-newyear);
                    var tillDateApprove = new Date(parseInt(currentLeaveYear), 05, 30);
                    if (diff==1 && currentdate.getTime() <= tillDateApprove.getTime()) {
                       ismanegeble=1;
                    } else {
                        ismanegeble=0;
                    }
                  }

              if(isSystemAmin==1 || isdivisionalAdmin==1 || ismanegeble==1 || isSchedulingTeamAdmin==1) {
                $('#addNewLeave').css('display','block');
                $('#closeNewLeave').css('display','none');
              } else {
                $('#addNewLeave').css('display','none');
                $('#closeNewLeave').css('display','block');
              }

              }else{
                var d = new Date();
                if(d.getMonth() > 2)
                {
                  var cyear = d.getFullYear();
                }else{
                  var cyear = parseInt(d.getFullYear())-1;
                }
                $('#selectedyear').val(cyear);
                $('#prevYear').text( ' << '+(cyear-1));
                $('#nextYear').text(parseInt(cyear)+1 +' >> ');
                var ismanegeble =$('#isManage').val();
                var isSystemAmin = SystemAdmin;
                var isdivisionalAdmin =$('#isdivisionalAdmin').val();
				var selectedyear = cyear;
                  var currentLeaveYear =$('#genericLeaveYear').val();
                  var newyear = parseInt(selectedyear)-1;
                  var currentdate = new Date();
                  if (selectedyear >= currentLeaveYear) {
                  var ismanegeble=1;
                  } else if(newyear < currentLeaveYear) {
                    var diff = (currentLeaveYear-newyear);
                    var tillDateApprove = new Date(parseInt(currentLeaveYear), 05, 30);
                    if (diff==1 && currentdate.getTime() <= tillDateApprove.getTime()) {
                       ismanegeble=1;
                    } else {
                        ismanegeble=0;
                    }
                  }
                if(isSystemAmin==1 || isdivisionalAdmin==1 || ismanegeble==1 || isSchedulingTeamAdmin==1) {
                  $('#addNewLeave').css('display','block');
                  $('#closeNewLeave').css('display','none');
                } else {
                  $('#addNewLeave').css('display','none');
                  $('#closeNewLeave').css('display','block');
                }

              }
             $.each(innerres,function(i,v) {

              if(v=='filter_approved')
              {
                $('#filter_approved').prop("checked",true);
              }
              if(v=='filter_pending')
              {
                $('#filter_pending').prop("checked",true);
              }
              if(v=='filter_agreed')
              {
                $('#filter_agreed').prop("checked",true);
              }
              if(v=='filter_deleted')
              {
                $('#filter_deleted').prop("checked",true);
              }
            });
           });
        },
        error: function(http,status,error) {
          customAlert("Error Found :" + error);
            }
      });

}

      if (($.cookie("StaffYearlyLeavesTableScroll") !== null) && ($.cookie("StaffYearlyLeavesTableScroll") != '') && ($.cookie("StaffYearlyLeavesTableScroll") != undefined)) {
        $(".dataTables_scrollBody").scrollTop($.cookie("StaffYearlyLeavesTableScroll"));
      }

      $(".dataTables_scrollBody").on("scroll", function() {
        let StaffYearlyLeavesTable_Scroll = $(this).scrollTop();
        $.cookie("StaffYearlyLeavesTableScroll", StaffYearlyLeavesTable_Scroll );
      });

   </script>

<?php
  break;
  case 4:
    $leaveApproved='';
    // ##################################################################################  Is marked approved by approver
    // ##################################################################################  Do the ShortNotice
    $leaveApproved.= '<table class="tablesmall compact stripe w-100" id="leaveApproved">';
    $leaveApproved.= '<thead>';
    $leaveApproved.='<tr>';
    $leaveApproved.= '<th>';
    $leaveApproved.= 'Group';
    $leaveApproved.='</th>';
    $leaveApproved.= '<th>';
    $leaveApproved.= 'Type';
    $leaveApproved.= '</th>';
    $leaveApproved.= '<th>';
    $leaveApproved.= 'Date';
    $leaveApproved.= '</th>';
    $leaveApproved.= '<th>';
    $leaveApproved.= 'Week';
    $leaveApproved.= '</th>';
    $leaveApproved.= '<th>';
    $leaveApproved.= 'Person';
    $leaveApproved.= '</th>';
    $leaveApproved.= '<th>';
    $leaveApproved.= 'Requested';
    $leaveApproved.= '</th>';
    /* View part day leave using leave admin */
    $leaveApproved.= '<th>';
    $leaveApproved.= 'Part Day';
    $leaveApproved.= '</th>';
    $leaveApproved.= '<th>';
    $leaveApproved.= 'Comments';
    $leaveApproved.= '</th>';
    $leaveApproved.= '<th>';
    $leaveApproved.='Is OK';
    $leaveApproved.= '</th>';
    $leaveApproved.='</tr>';
    $leaveApproved.='</thead>';
    $leaveApproved.= '<tbody>';
    if (isset($arrLeave['LeaveRequests']['ShortNotice'])) {
      foreach ($arrLeave['LeaveRequests']['ShortNotice'] as $strDate => $arrLeaveDates) {
        foreach ($arrLeaveDates as $intRequestID => $arrRequest) {
          if ($arrRequest['IsAgreed'] == 1 && $arrRequest['Approved'] != 1) {
            $getWeekandDayArr = GetAllocationWeekandDay($strDate);
            $intCurrWeek = $getWeekandDayArr['ixYearWeek'];
            $intGroupID = $arrRequest['GroupID'];
            $leaveApproved.= '<tr class="handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')">';

            $leaveApproved.= '<td>';
            $leaveApproved.= $arrRequest['GroupDescription'];
            $leaveApproved.='</td>';

            $leaveApproved.= '<td>';
            $leaveApproved.= $arrRequest['TypeDescription'];
            $leaveApproved.= '</td>';

            $leaveApproved.= '<td>';
            $leaveApproved.= '<span class="customdateSort">'.date("Y-m-d", strtotime($strDate)).'</span>';
            $leaveApproved.= date("l, jS M Y", strtotime($strDate));
            $leaveApproved.= '</td>';

            $leaveApproved.= '<td>';
            $leaveApproved.= spinweek($intCurrWeek);
            $leaveApproved.= '</td>';

            $leaveApproved.= '<td>';
            $leaveApproved.= $arrRequest['FullName'];
            $leaveApproved.= '</td>';

            $leaveApproved.= '<td>';
            $leaveApproved.= '<span class="customdateSort">'.date("Y-m-d", strtotime($arrRequest['Created'])).'</span>'.getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']).' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
            $leaveApproved.= '</td>';

      if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
      $arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');
      }
      else{$arrRequestPdl ='';}
      $leaveApproved.= '<td>';
            $leaveApproved.= $arrRequestPdl;
            $leaveApproved.= '</td>';
            $leaveApproved.= '<td>';
            if ($arrRequest['UserComments'] != '') {
              $leaveApproved.= '<b>User Comments</b><br>';
              $leaveApproved.= $arrRequest['UserComments'].'<br>';
            }
            if ($arrRequest['OfficeComments'] != '') {
              $leaveApproved.= '<b>Office Comments</b><br>';
              $leaveApproved.= $arrRequest['OfficeComments'].'<br>';
            }
            $leaveApproved.= '</td>';

            $leaveApproved.= '<td class="LeaveShortNoticeApplied">';
            $leaveApproved.= '</td>';

            $leaveApproved.= '</tr>';
          }
        }
      }
    }
    // ##################################################################################  END Do the ShortNotice
      if (isset($arrLeave['LeaveRequests']['General'])) {
        foreach ($arrLeave['LeaveRequests']['General'] as $strDate => $arrLeaveDates) {
          foreach ($arrLeaveDates as $intRequestID => $arrRequest) {
            if ($arrRequest['IsAgreed'] == 1 && $arrRequest['Approved'] != 1) {
              $getWeekandDayArr = GetAllocationWeekandDay($strDate);
              $intCurrWeek = $getWeekandDayArr['ixYearWeek'];
              $intGroupID = $arrRequest['GroupID'];
              $leaveApproved.= '<tr class="handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')">';

              $leaveApproved.= '<td>';
              $leaveApproved.= $arrRequest['GroupDescription'];
              $leaveApproved.= '</td>';

              $leaveApproved.= '<td>';
              $leaveApproved.= $arrRequest['TypeDescription'];
              $leaveApproved.= '</td>';

              $leaveApproved.= '<td>';
              $leaveApproved.= '<span class="customdateSort">'.date("Y-m-d", strtotime($strDate)).'</span>';
              $leaveApproved.= date("l, jS M Y", strtotime($strDate));
              $leaveApproved.= '</td>';

              $leaveApproved.= '<td>';
              $leaveApproved.= spinweek($intCurrWeek);
              $leaveApproved.= '</td>';

              $leaveApproved.= '<td>';
              $leaveApproved.= $arrRequest['FullName'];
              $leaveApproved.= '</td>';

              $leaveApproved.= '<td>';
              $leaveApproved.= '<span class="customdateSort">'.date("Y-m-d", strtotime($arrRequest['Created'])).'</span>'.getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']).' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
              $leaveApproved.= '</td>';

        if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
        $arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');
        }
        else{$arrRequestPdl ='';}
        $leaveApproved.= '<td>';
        $leaveApproved.= $arrRequestPdl;
        $leaveApproved.= '</td>';
              $leaveApproved.= '<td>';
              if ($arrRequest['UserComments'] != '') {
                $leaveApproved.= '<b>User Comments</b><br>';
                $leaveApproved.= $arrRequest['UserComments'].'<br>';
              }
              if ($arrRequest['OfficeComments'] != '') {
                $leaveApproved.= '<b>Office Comments</b><br>';
                $leaveApproved.= $arrRequest['OfficeComments'].'<br>';
              }
              $leaveApproved.= '</td>';

              $leaveApproved.= '<td align="center">';
              $leaveApproved.= '<span class="customdateSort">'.$arrRequest['IsOK'].'</span>';
              if ($arrRequest['IsOK'] == 1) {
                $leaveApproved.= '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';
              } else {
                $leaveApproved.= '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';
              }
              $leaveApproved.= '</td>';

              $leaveApproved.= '</tr>';
            }
          }
        }
      }
      $leaveApproved.= '</table>';
      echo $leaveApproved;
          ?>
  <script type="text/javascript">
  $(document).ready(function(){
    var yadcfColumns = [
        { column_number: 0, filter_type: 'select', aria_label: 'Group' },
        { column_number: 1, filter_type: 'text', aria_label: 'Type' },
        { column_number: 3, filter_type: 'text', aria_label: 'Week' },
        { column_number: 4, filter_type: 'text', aria_label: 'Person' },
        { column_number: 7, filter_type: 'text', aria_label: 'Comments' }
    ];
    var table = $('#leaveApproved').DataTable({
        paging: false,
        scrollY: parseInt($(window).height() - 95),
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        initComplete: function (settings, json) {
            $('#leaveApproved').attr('aria-label', 'Agreed table');
            $('#leaveApproved thead th').each(function (index) {
                let columnTitle = $(this).clone().children().remove().end().text().trim() || 'Column ' + (index + 1);
                $(this).attr('aria-label', columnTitle + ' column header');
            });
            if ($('#leaveApproved').length) {
                ResizeleaveApprovedGrid();
            }
        }
    });
    yadcf.init(table, yadcfColumns);
    setTimeout(function () {
        yadcfColumns.forEach(function (colConfig) {
            let colNum = colConfig.column_number;
            let ariaLabel = colConfig.aria_label;
            if (!ariaLabel) return;
            let filterDivId = '#yadcf-filter--leaveApproved-' + colNum;
            let $filterElement = $(filterDivId).find('select, input').first();
            if ($filterElement.length) {
                $filterElement.attr('aria-label', ariaLabel);
            }
            $(filterDivId).attr('aria-label', ariaLabel);
        });
    }, 300);
  });
  function ResizeleaveApprovedGrid() {
  if ($("#leaveApproved").length) {
    var offset = parseInt($("#leaveApproved").offset().top);
    var windowheight = $(window).height() - offset - 30;
    $('.dataTables_scrollBody:has(#leaveApproved)').height(windowheight+'px');
    $(".dataTables_scrollBody").css({ "overflow-y": "hidden","max-height":"395px" });
    $('#leaveApproved').dataTable().fnAdjustColumnSizing();
  }
  }
  $(window).resize(function() {
  if ($("#leaveApproved").length) {
    ResizeleaveApprovedGrid();
  }
  })
  </script>
  <?php
  break;
}
?>

<style>
  .dataTables_scrollHeadInner{
    width:100% !important;
  }
</style>
