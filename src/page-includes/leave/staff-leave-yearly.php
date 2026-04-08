<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/userfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../users/process/classUserSetup.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService(); 
$commonObj = new classCommonDBFunctions();
$setupObj  = new classUserSetup(); 

$intSysAdmin = 0;
$isDivisionalAdmin = 0;
$intScheduledPersonID=0;
$intLinksAdmin="yearly";
$showOptions="";
$intCurrWeek="";
$arrYearlyLeave = [];
$keys=[];
$values=[];
$formdataArr=[];
$selecteduser = '';

$intLeaveYear = $selectedYear= $currentLeaveYear= GetCurrentLeaveYearGeneric(); 

$checked_approved=null;
$checked_pending=null;
$checked_deleted=null;
$checked_agreed=null; 
$canView =0;
$userId = isset($_COOKIE['editWeeklyUserId']) ? $_COOKIE['editWeeklyUserId'] : $_SESSION['user']['UserID'];
$intSysAdmin= $commonObj->UserIsSysAdmin($userId) ?? 0;

$strUser = isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ? $_SESSION['user']['user'] :$_COOKIE['editWeeklyUserNetLogin'];

  $loggedUsedInfo = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
  $LeaveAdminGroupsData = GetLeaveAdminGroups($strUser );
  $arrLeaveAdmingrps = [];
  $arrGroupAminType = [];
  if (!empty($LeaveAdminGroupsData)) {
	  foreach ($LeaveAdminGroupsData as $value) {
      array_push($arrLeaveAdmingrps, $value['LeaveGroupID']);		
      $arrGroupAminType[$value['LeaveGroupID']] = $value['Admin'];  
	  }
   }

  if (isset($loggedUsedInfo['DivisionalAdmin'])){
    $isDivisionalAdmin = $loggedUsedInfo['DivisionalAdmin'];
  } else {
    $isDivisionalAdmin = 0;
  }
  
  $isSchedulingTeamAdmin = 0;
  if (isset($loggedUsedInfo['isSchedulingTeamAdmin'])){
    $isSchedulingTeamAdmin = $loggedUsedInfo['isSchedulingTeamAdmin'];
  } 
  
   /* Yearly Leave Start */
    $result['DataInput']=readYearlyFilter();
    if (isset($result['DataInput']) && !empty($result['DataInput'])) {
      $formdataArr = explode("&",$result['DataInput']); 
      if (sizeof($formdataArr)) {
        foreach($formdataArr as $row) {
          list($keys[],$values[]) = explode("=",$row);
        }
      } 
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

    if (!empty($keys) && in_array('filter_agreed',$keys)) {
      $keyno = array_search('filter_agreed',$keys);
      $checked_agreed= 'checked';
    } else {
      $checked_agreed='';
    }
  
    if (!empty($keys) && in_array('filter_deleted',$keys)) {
      $keyno = array_search('filter_deleted',$keys);
      $checked_deleted= 'checked'; 
    } else {
      $checked_deleted='';
    }
  
    if (!empty($keys) && in_array('selectedyear',$keys)) {
      $keyno = array_search('selectedyear',$keys);
      if ($values[$keyno] != '') {
        $intLeaveYear = $values[$keyno];
        $selectedYear=$values[$keyno];
      } else {
        $intLeaveYear = GetCurrentLeaveYearGeneric();
      }  
    }

    if ($selectedYear >= $currentLeaveYear) {
      $canView =1;
    } else {
      if($selectedYear < $currentLeaveYear) {
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
    if (!empty($keys) && in_array('ChooseUser',$keys)) {
    $keyno= array_search('ChooseUser',$keys);
    $selecteduser= $values[$keyno];
    
    $intScheduledPersonID = getScheduledPersonIDByNetLoginID($selecteduser);

      if ($intScheduledPersonID > 0) {
        $arrYearlyLeave = GetYealyLeaveInformationByNetLoginId($intScheduledPersonID,$strUser,$dteStartDate,$dteEndDate,$checked_approved,$checked_pending,$checked_deleted,$checked_agreed);  
      } else { 
        $selecteduser = '';
        $intScheduledPersonID = 0;
        $arrYearlyLeave = GetYealyLeaveInformationByNetLoginId($intScheduledPersonID,$strUser,$dteStartDate,$dteEndDate,$checked_approved,$checked_pending,$checked_deleted,$checked_agreed); 
      }
    } else {
      $selecteduser = '';
      $intScheduledPersonID = 0;
      $arrYearlyLeave = GetYealyLeaveInformationByNetLoginId($intScheduledPersonID,$strUser,$dteStartDate,$dteEndDate,$checked_approved,$checked_pending,$checked_deleted,$checked_agreed);
    } 

  /** Leave Yearly End */
  $res='';
  $res.= '<table class="tablesmall compact stripe fullwidth w-100" id="StaffYearlyLeavesTable">'; 
  $res.= '<thead>';
  $res.='<tr>';
  $res.='<th>';
  $res.= '<input type="hidden" value="'.$currentLeaveYear.'" id="genericLeaveYear" name="genericLeaveYear" readonly/>';
  $res.='<input type="hidden" value="'.$canView.'" id="isManage" name="isManage" readonly/>';
  $res.='<input type="hidden" value="'.$isSchedulingTeamAdmin.'" id="isSchedulingTeamAdmin" name="isSchedulingTeamAdmin" readonly/>';
  $res.='<input type="hidden" value="'.$intSysAdmin.'" id="issystest" name="systest"/>';
  $res.='<input type="hidden" value="'.$isDivisionalAdmin.'" id="isdivisionalAdmin" name="isdivisionalAdmin" readonly/>Group';
  $res.='</th>'; 
  $res.= '<th>';
  $res.='Type';
  $res.= '</th>';             
  $res.='<th>';
  $res.= 'Date';
  $res.= '</th>';   
  $res.= '<th>';
  $res.='Week';
  $res.='</th>';
  $res.= '<th>';
  $res.= 'Person';
  $res.= '</th>';   
  $res.='<th>';
  $res.='Requested';
  $res.= '</th>';
  /* View part day leave using leave admin */
  $res.= '<th>';
  $res.= 'Part Day';
  $res.= '</th>';  
  $res.= '<th>';
  $res.= 'Comments';
  $res.='</th>';           
  $res.= '<th>';
  $res.='Is OK';
  $res.='</th>';
  $res.= '</tr>'; 
  $res.='</thead>';
  $res.= '<tbody>';
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
    }  else {
      if ($arrRequest['IsAgreed'] == 1) {
        $class=" LeaveAgreed";
      } else if ($arrRequest['ShortNotice'] == 1) {
        $class=" LeaveShortNoticeApplied";
      } else {          
        if ($arrRequest['OverSummer'] == 1) {
          $class=" LeaveOverSummer";
        } else {
          if ($arrRequest['IsOK']== 1) {
            $class=" LeaveOK";
          } else {
            $class=" LeaveNotOK";
          }
        }
      }
    }
  }
    $intCurrWeek = bbcweeknumber($strDate);
    $intGroupID = $arrRequest['GroupID'];
  
    $res.= '<tr id="'.$arrRequest['ID'].'" callpage="'.$intLinksAdmin.'" showoptions="'.$showOptions.'" week="'.$intCurrWeek.'" data-approved="' . $arrRequest['Approved'] . '" data-agreed="' . $arrRequest['IsAgreed'] . '" data-admin-type="' . ($arrGroupAminType[$arrRequest['GroupID']] ?? 0) . '"'; 
     if ($intSysAdmin==1 || $isDivisionalAdmin==1 || $canView==1 || $isSchedulingTeamAdmin==1) {
      if ($arrRequest['Deleted'] != 1) {
        $res.= 'onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intCurrWeek.'\',\''.$intGroupID.'\')"';
        if (in_array($arrRequest['GroupID'],$arrLeaveAdmingrps)){
			$res.='class="handcursor context-menu-yearly "';
		} else {
			$res.='class="handcursor context-menu-yearly-onlyhistory "';
		}	
      } else{
		if (in_array($arrRequest['GroupID'],$arrLeaveAdmingrps))
		{
			$res.=  'class=" context-menu-d-yearly"';
		}else
		{
		   $res.=  'class=" context-menu-yearly-onlyhistory"';
		}; 
      }
    } else{
      $res.= 'class=" context-menu-yearly-onlyhistory"';
    }
    $res.= '>';
    $res.= '<td  nowrap class="'.$class.'">';
    $res.= $arrRequest['GroupDescription'];
    $res.= '</td>';
      
    $res.= '<td  nowrap class="'.$class.'">';
    $res.= $arrRequest['TypeDescription'];
    $res.= '</td>';            

    $res.= '<td  nowrap class="'.$class.'">';
    $res.='<span class="customdateSort">';
    $res.= date("Y-m-d", strtotime($strDate));
    $res.='</span>';
    $res.= date("l, jS M Y", strtotime($strDate));
    $res.= '</td>';     

    $res.= '<td  nowrap class="'.$class.'">';
    $res.= spinweek($intCurrWeek);
    $res.= '</td>';

    $res.= '<td nowrap class="'.$class.'">';
    $res.= $arrRequest['FullName'];
    $res.= '</td>';
    $res.= '<td  nowrap class="'.$class.'">';
    $res.='<span class="customdateSort">';
    $res.= date("Y-m-d", strtotime($arrRequest['Created']));
    $res.='</span>';
    $res.= getDateTimeInEuropeTimezone(1, 0, "jS F Y", $arrRequest['Created']).' '.getDateTimeInEuropeTimezone(0, 1, "", $arrRequest['Created']);
    $res.= '</td>';
	
	if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){

			$arrRequestPdl = $service->convertSecondsIntoTime($arrRequest['LeaveStartTime'],':','No').' - '.$service->convertSecondsIntoTime($arrRequest['LeaveEndTime'],':','No');

	     }
	else{$arrRequestPdl ='';}
	$res.= '<td nowrap class="'.$class.'">';
	$res.= $arrRequestPdl;
	$res.= '</td>';

	$res.= '<td class="'.$class.'">';
      if ($arrRequest['UserComments'] != '') {
        $res.= '<b>User Comments</b><br>';
		if($arrRequest['LeaveStartTime'] !='' && $arrRequest['LeaveEndTime'] !=''){
			$res.= 'PDL: '; 
		  }
        $res.= $arrRequest['UserComments'].'<br>';
      }
      if ($arrRequest['OfficeComments'] != '') {
        $res.= '<b>Office Comments</b><br>';
		if($arrRequest['LeaveStartTime'] !='' && $arrRequest['LeaveEndTime'] !=''){
		 $res.= 'PDL: '; 
		 }
        $res.= $arrRequest['OfficeComments'].'<br>'; 
      }
      $res.= '</td>'; 	

      $res.='<td class="'.$class.'">';
      $res.='<span class="customdateSort">';
      $res.= $arrRequest['IsOK'];
      $res.='</span>';
      if ($arrRequest['IsOK'] == 1) {
        $res.= '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';  
      }
      else {
        $res.='<img border="0" src="../images/red_cross.png" width="12px" height="12px">';  
      }
      $res.= '</td>';                                                            
      $res.= '</tr>';
    }   
  }
 }   
} else {
  $totalrow = 9;
  $middlerecord = round(9/2);
  $res.= '<tr>';
    for($i = 1; $i<=$totalrow;$i++){
      if($middlerecord == $i){
        $res.= '<td align="center">No Record Found</td>';
      }else{
        $res.= '<td></td>';
      }
    }
    $res.= '</tr>';
}

$res.='</tbody></table>';
echo $res;

?>

<script type="text/javascript">
$(document).ready(function() {
   var newyear = $('#selectedyear').val();
    var currentLeaveYear =$('#genericLeaveYear').val();
    /** Check LeaveYEar */
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
        var offset = ($("#StaffYearlyLeavesTable").offset().top);
        var windowheight = $(window).height() - offset - 50;
        $('.dataTables_scrollBody:has(#StaffYearlyLeavesTable)').height(windowheight+'px');
        $('#StaffYearlyLeavesTable').dataTable().fnAdjustColumnSizing();
    }
  }
  $(window).resize(function() {
    ResizeStaffYearlyLeaveGrid();
});
</script>
