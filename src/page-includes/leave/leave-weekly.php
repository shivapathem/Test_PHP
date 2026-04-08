<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
date_default_timezone_set('UTC');
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../users/process/classUserSetup.php';

$commonDbobj = new classCommonDBFunctions();
// Application Module Log Starts
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$sessUserNetLogin = $_SESSION['user']['user'];
$isDivisionalAdmin = $commonDbobj->UserIsDivAdmin($sessUserId);
$isSysAdmin = $_SESSION['user']['SysAdmin']==''? 0:$_SESSION['user']['SysAdmin'];
$setupObj = new classUserSetup();
$loggedUsedInfo = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$isSchedulingTeamAdmin = 0;
if (isset($loggedUsedInfo['isSchedulingTeamAdmin'])){
   $isSchedulingTeamAdmin = $loggedUsedInfo['isSchedulingTeamAdmin'];
}
if (isset($_REQUEST['user'])) {
  $strUser = $_REQUEST['user'];
} else {
  $strUser = GetUserLogon();
}
if (isset($_REQUEST['week'])) {
  $intWeekNumber = $_REQUEST['week'];
} else {
  $requestDate =  isset($_REQUEST['date'])? $_REQUEST['date'] : date("Y-m-d");
  $bbcweeknumberArray = $commonDbobj->GetWeekNoAndIDayByDateFromTimeDim($requestDate);
  $intWeekNumber =  $bbcweeknumberArray['ixYearWeek'];
}

if (isset($_REQUEST['admin'])) {
  $intAdmin = $_REQUEST['admin'];
  $intLinksAdmin = $intAdmin;
  if ($intAdmin == 2) {
     $intLinksAdmin = $intAdmin;
     $intAdmin = 1;
  }
}
else {  
  $intLinksAdmin = 0;
  $intAdmin = 0;
}
if ($intAdmin == 0) {
  $intTRHeight = 40;
  $strMenuPopup = '';
}
else {
  $intTRHeight = 30;
  $strMenuPopup = 'weekly-leave-context-menu';
}

if (isset($_REQUEST['group'])) {
  $intLeaveGroup = $_REQUEST['group'];
}
else {  
  $intLeaveGroup = 0;
}

$strStartDate =  date('Y-m-d',strtotime($commonDbobj->GetWeekStartDateByWeekNoFromTimeDim($intWeekNumber,'ByDayWeek',0)['dDateTime']));
$strEndDate = date('Y-m-d',strtotime($commonDbobj->GetWeekStartDateByWeekNoFromTimeDim($intWeekNumber,'ByDayWeek',6)['dDateTime']));

$xmasstart = (date("Y", strtotime($strStartDate)))."-12-23";
$xmasend =  (date("Y", strtotime("+1 year", strtotime($strStartDate))))."-01-01";

if (strtotime($strEndDate) <= strtotime($xmasstart)  && strtotime($strStartDate) >= strtotime($xmasend)) {
  $doxmas = 0;
} else {
  $doxmas = 1;
}

$CurrentNextPrevWeek = $commonDbobj->GetCurrentNextPRevWeekNoByWeekNoFromTimeDim($intWeekNumber);

$intPrevWeek =$CurrentNextPrevWeek['PrevWeek'];
$intNextWeek = $CurrentNextPrevWeek['NextWeek'];
$prevweeksplit = str_split($CurrentNextPrevWeek['PrevWeek'], 4);
$nextweeksplit = str_split($CurrentNextPrevWeek['NextWeek'], 4);
$spinNextWeek = $nextweeksplit[1].'/'.$nextweeksplit[0];
$spinPrevWeek = $prevweeksplit[1].'/'.$prevweeksplit[0];
$currentweeksplit = str_split($CurrentNextPrevWeek['CurrentWeek'], 4);
$spinCurrentWeek = $currentweeksplit[1].'/'.$currentweeksplit[0];
$arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLogin($strUser, 0),true);

if ($intLeaveGroup != 0) {
  if(isset($arrUserSettings['LeaveRequests'][$intLeaveGroup])){
    $arrGroupsCanRequest[$intLeaveGroup] = $arrUserSettings['LeaveRequests'][$intLeaveGroup];
  }
}
else {
  foreach ($arrUserSettings['LeaveRequests'] as $intGroupID => $arrGroup) {
    if ($intAdmin == 0) {
      if ($arrGroup['Admin'] == 0) {
        $arrGroupsCanRequest[$intGroupID] = $arrGroup;
      }    
    }
    else {
      if ($arrGroup['Admin'] >= 1) {
        $arrGroupsCanRequest[$intGroupID] = $arrGroup;
      }
    }   
	
  }
}
$htmlweekly = '';
if (!isset($arrGroupsCanRequest)) {
  // No request groups defined
  $htmlweekly = '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  $htmlweekly .= '<br>You do not have any Leave groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  $htmlweekly .= '</div>';
} else {
  $arrLeave = ReadLeaveWeekly($strUser, $strStartDate, $strEndDate, $intAdmin,$intWeekNumber);
    if (!empty($arrLeave['ScheduledPersonIDs']) && isset($arrLeave['ScheduledPersonIDs'])) {
      $scheduledpersonIDS = array_unique($arrLeave['ScheduledPersonIDs']);
    } else {
      $scheduledpersonIDS = []; 
    }
  if (isset($scheduledpersonIDS) && $doxmas == 1) {
    $arrXmasPoints = GetXmasPoints($scheduledpersonIDS);
  }
  
  // ########################################################################### The Header
  if($intAdmin == 1){
    echo '<h1 class="sr-only">Leave By Week</h1>';
  }else{
    echo '<h1 class="sr-only">My Leave By Week</h1>';
  }
  $htmlweekly .= '<table class="tablegreysmallnoborder" width="100%">';
  $htmlweekly .= '<tr height="'.$intTRHeight.'px">';
  switch ($intLinksAdmin) {
    case 0:
    $htmlweekly .= '<td width="150px" class="medtextbold handcursor" onclick="javascript:ShowLeaveWeekly(\''.$intPrevWeek.'\',\''.$strUser.'\')">';
    $htmlweekly .= '&lt;&lt;&nbsp;'.$spinPrevWeek;
    $htmlweekly .= '</td>';
    $htmlweekly .= '<td width="150px" class="medtextbold handcursor" onclick="javascript:ShowLeaveWeekly(\''.$intNextWeek.'\',\''.$strUser.'\')">';
    $htmlweekly .= $spinNextWeek.'&nbsp;&gt;&gt;';
    $htmlweekly .= '</td>';
    $htmlweekly .= '<td width="100px" align="right" class="medtextbold" >Choose Week&nbsp;&nbsp;</td>';
    $htmlweekly .= '<td width="100px"><input type="hidden" id="datepicker"></td>';
      $strCalendarAction = 'ShowLeaveWeeklyByDate';
    break;
    
    case 1:
    $htmlweekly .= '<td width="150px" class="medtextbold handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intPrevWeek.'\',\''.$intLeaveGroup.'\')">';
    $htmlweekly .= '&lt;&lt;&nbsp;'.$spinPrevWeek;
    $htmlweekly .= '</td>';
    $htmlweekly .= '<td width="150px" class="medtextbold handcursor" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$intNextWeek.'\',\''.$intLeaveGroup.'\')">';
    $htmlweekly .= $spinNextWeek.'&nbsp;&gt;&gt;';
    $htmlweekly .= '</td>';
    $htmlweekly .= '<td width="100px" align="right" class="medtextbold" >Choose Week&nbsp;&nbsp;</td>';
    $htmlweekly .= '<td width="100px"><input type="hidden" id="datepicker"></td>';
      $strCalendarAction = 'ShowLeaveWeeklyAdminByDate';
    break;
    
    case 2:
    $htmlweekly .= '<td width="150px" class="medtextbold handcursor" onclick="javascript:AdminShowLeaveWeekly(\''.$intPrevWeek.'\',\''.$intLeaveGroup.'\')">';
    $htmlweekly .= '&lt;&lt;&nbsp;'.$spinPrevWeek;
    $htmlweekly .= '</td>';
    $htmlweekly .= '<td width="150px" class="medtextbold handcursor" onclick="javascript:AdminShowLeaveWeekly(\''.$intNextWeek.'\',\''.$intLeaveGroup.'\')">';
    $htmlweekly .= $spinNextWeek.'&nbsp;&gt;&gt;';
    $htmlweekly .= '</td>';
    $htmlweekly .= '<td width="100px" align="right" class="medtextbold" >Choose Week&nbsp;&nbsp;</td>';
    $htmlweekly .= '<td width="100px"><input type="hidden" id="datepicker"></td>';
      $strCalendarAction = 'AdminShowLeaveWeeklyByDate';  
    break;
                
  }
  $htmlweekly .= '<td align="center" class="medtextbold">Leave for Week '.$spinCurrentWeek;
  if ($intAdmin == 1) {
    $htmlweekly .= ' (Administration)';
  }
  $htmlweekly .= '</td>';
  if ($intAdmin == 0) {
    $htmlweekly .= '<td width="100px" class="handcursor  medtextbold" onclick="javascript:ShowLeaveWeeklyHelp()" align="center">';
    $htmlweekly .= '<img src="images/help.png" height="16" border="0" width="16"></td>';
 }
 $htmlweekly .= '</tr>';
 $htmlweekly .= '</table>';
 $htmlweekly .= '<br>';
 $rowcount = 1;
 $totalRowcount = sizeof($arrGroupsCanRequest);
  // ########################################################################### END The Header
  $htmlweekly .=  '<table class="tablesmalltidy fullwidth tablesmalllightcellwordBreak">';
  // ########################################################################### Loop through the groups
  foreach ($arrGroupsCanRequest as $intGroupID => $arrGroup) {
    $htmlweekly .=  '<tr height='.$intTRHeight.'px>';
    $htmlweekly .=  '<td colspan="8" class="tableheadersmall medtextboldcentre" onmouseover="hidecustomLeaveToolTip();">';
    $htmlweekly .=  $arrGroup['Description'];
    $htmlweekly .=  '</td>';
    $htmlweekly .=  '</tr>';
    // ########################################################################### Days Across the top
    $htmlweekly .=  '<tr>';
    $htmlweekly .=  '<th>';
    $htmlweekly .=  '</th>';
    for ($i=0; $i <= 6; $i++) {
      $currdate = date("Y-m-d", strtotime("+ $i days", strtotime($strStartDate)));
      $currdatelong = date("jS M Y", strtotime("+ $i days", strtotime($strStartDate)));
      $dayname = date("l", strtotime("+ $i days", strtotime($strStartDate)));
      $htmlweekly .=  '<th class="medtextboldcentre medtextboldcentreWidth200" onmouseover="hidecustomLeaveToolTip();">'.$currdatelong.'<br>'.$dayname;
      $htmlweekly .=  '</th>';
    }
    $htmlweekly .=  '</tr>';
    // ########################################################################### End Days across the top    

    // ########################################################################### Now loop through the tyoes in the group   
    if (isset($arrGroup['LeaveTypes'])) {
      foreach ($arrGroup['LeaveTypes'] as $intTypeID => $strTypeDescription) {
        $colNum = 1;
        $htmlweekly .=  '<tr height="'.$intTRHeight.'px">';
        $htmlweekly .=  '<th class="medtextboldcentre">';
        $htmlweekly .=  $strTypeDescription;  
        $htmlweekly .=  '</th>';
      
        for ($i=0; $i <= 6; $i++) {
          $currdate = date("Y-m-d", strtotime("+ $i days", strtotime($strStartDate)));
          if(isset($arrLeave['Available'][$intTypeID][$currdate])){
            $intAvailable = $arrLeave['Available'][$intTypeID][$currdate];
          }else{
            $intAvailable = '';
          }
          $showOptions = getLeaveYearOfCell($currdate,$isDivisionalAdmin,$isSysAdmin,$isSchedulingTeamAdmin);
          $htmlweekly .=  '<td class="lightcell tablesmalllightcellwordBreak" valign="top">';
          $htmlweekly .=  '<div class="LightGrey medtextright" onmouseover="hidecustomLeaveToolTip();">';
          $htmlweekly .=  '('.$intAvailable.')&nbsp;&nbsp;';
          $htmlweekly .=  '</div>';
          if (isset($arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$currdate])) {
            // Do it twice for Approved first then all the other
            for ($intApprovedCount = 1; $intApprovedCount >= 0 ; $intApprovedCount--) {
              $intICount = 1;
              $countLeaveZero = 0;
              foreach ($arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$currdate] as $intLeaveID => $arrRequest) {
                $intIsOK = $arrRequest['IsOK'];
                if($arrRequest['CountLeave']==0){
                  $countLeaveZero++;
                }
                if ($arrRequest['IsOK'] == 0 && ($arrRequest['Approved'] == 0 || $arrRequest['Approved'] == 2)) {

                  if ($intAvailable >= ($intICount - $countLeaveZero)) {
                    MakeLeaveOK ($intLeaveID);
                    $intIsOK = 1;                 
                  }
                }

                $leaveCommentIconFlag = 0;
                if((strtolower($sessUserNetLogin) == strtolower($arrRequest['Login'])) || ($arrUserSettings['HasLeaveAdmin'] == 1)){
                  $leaveCommentIconFlag = 1;
                }

                if ($arrRequest['Approved'] == $intApprovedCount) {
                $strLogon = $arrRequest['Login'];
                $pdlAsterisk = '';
        if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){ $pdlAsterisk = '*'; }
        if($arrRequest['AllocationID']!=''){$AllocationID = $arrRequest['AllocationID'];}else{$AllocationID = 0;}
        
                if ($arrRequest['Approved'] == 1) {
                    if ($intAdmin == 1) {
                        $pLeaveClsName = '';
                        if ($arrRequest['CountLeave'] == 1) {
                            $pLeaveClsName = 'LeaveApproved';
                        } else {
                            $pLeaveClsName = 'LeaveHashedOrange';
                        }

                        $fullName = $arrRequest['FullName'];
                        if(!empty($pdlAsterisk)){
                          $fullName = $arrRequest['FullName'].' '.$pdlAsterisk;
                        }
            
                        $htmlweekly .=  '<div data-approval="' . $arrRequest['Approved'] . '" callpage="'.$intLinksAdmin.'" data-admin-type="' . $arrGroup['Admin'] . '" group="'.$intGroupID.'" id='.$intLeaveID.' leaveDutyAllocationID='.$AllocationID.' week="'.$intWeekNumber.'"  showoptions="'.$showOptions.'" fullname="'. $fullName.'" class="'.$pLeaveClsName.' borderbottomwhite handcursor weekly-leave-context-menu-approved" onmouseover="showleavetip('."'showtooltip'".',this,'.$colNum.');" onmouseleave="showleavetip('."'hidetooltip'".');">';
                    }
                    else {
                        $pLeaveClsName = '';
                        if ($arrRequest['CountLeave'] == 1) {
                            $pLeaveClsName = 'LeaveOK';
                        } else {
                            $pLeaveClsName = 'LeaveHashedYellow';
                        }
                        $htmlweekly .=  '<div data-approval="' . $arrRequest['Approved'] . '" callpage="'.$intLinksAdmin.'" data-admin-type="' . $arrGroup['Admin'] . '" group="'.$intGroupID.'" id='.$intLeaveID.' leaveDutyAllocationID='.$AllocationID.' week="'.$intWeekNumber.'" showoptions="'.$showOptions.'" fullname="'. $arrRequest['FullName'].'" class="'.$pLeaveClsName.' borderbottomwhite handcursor '.$strMenuPopup.'" onmouseover="showleavetip('."'showtooltip'".',this,'.$colNum.');" onmouseleave="showleavetip('."'hidetooltip'".');">';
                    }
                } else {
                    $pLeaveClsName = '';
                    if($arrRequest['IsAgreed'] == 1 && $arrGroup['Admin'] != 0) {
                        $pLeaveClsName = 'LeaveAgreed';
                    } elseif ($arrRequest['ShortNotice'] == 1) {
                        $pLeaveClsName = 'LeaveShortNoticeApplied';
                    } else {       
                        if ($arrRequest['OverSummer'] == 1) {
                            $pLeaveClsName = 'LeaveOverSummer';
                        } else {
                            if ($intIsOK == 1) {
                                $pLeaveClsName = 'LeaveOK';
                            } else {
                                $pLeaveClsName = 'LeaveNotOK';
                            }
                        }       
                    } 
                    $fullName = $arrRequest['FullName'];
                    if(!empty($pdlAsterisk)){
                      $fullName = $arrRequest['FullName'].' '.$pdlAsterisk;
                    }                   
                    $htmlweekly .=  '<div data-approval="' . $arrRequest['Approved'] . '" callpage="'.$intLinksAdmin.'" data-admin-type="' . $arrGroup['Admin'] . '" group="'.$intGroupID.'" id='.$intLeaveID.' leaveDutyAllocationID='.$AllocationID.' week="'.$intWeekNumber.'" showoptions="'.$showOptions.'" fullname="'. $fullName.'" class="'.$pLeaveClsName.'  borderbottomwhite handcursor '.$strMenuPopup.'" onmouseover="showleavetip('."'showtooltip'".',this,'.$colNum.','.$totalRowcount.','.$rowcount.');" onmouseleave="showleavetip('."'hidetooltip'".');">';
                }
                                // On the Admin page put the Xmas Points First
                if ($intAdmin == 1) {
                  if (strtotime($currdate) >= strtotime($xmasstart) && strtotime($currdate) <= strtotime($xmasend)) {
                    if (isset($arrXmasPoints[$arrRequest['Login']])) {
                      $htmlweekly .=  '<font color="990000"><b>'.$arrXmasPoints[$arrRequest['Login']].'</b></font>&nbsp;';
                    }
                  }
                }
                else {    
                  if (strtotime($currdate) >= strtotime($xmasstart) && strtotime($currdate) <= strtotime($xmasend)) {
                    if (isset($arrXmasPoints[$arrRequest['Login']])) {
                      $htmlweekly .=  '<div class="TopRight">';
                      $htmlweekly .=  '<font color="990000"><b>'.$arrXmasPoints[$arrRequest['Login']].'</b></font>';
                      $htmlweekly .=  '</div>';                  
                    }
                  }               
                }  
        if(($arrRequest['LeaveStartTime'] !='') && ($arrRequest['LeaveEndTime'] !='') && (($arrRequest['LeaveStartTime'] !=0) || ($arrRequest['LeaveEndTime'] !=0))){
          if($arrRequest['Unlikely'] == 1){
            $htmlweekly .=  '<span id="username_'.$intLeaveID.'">['.$arrRequest['FullName'].'] * </span><br>';
          }
          else{
            $htmlweekly .=  '<span id="username_'.$intLeaveID.'">'.$arrRequest['FullName'].' *'.'</span><br>';  
          }
        }
        else{
          if($arrRequest['Unlikely'] == 1){
            $htmlweekly .=  '<span id="username_'.$intLeaveID.'">['.$arrRequest['FullName'].'] </span><br>';
          }
          else{
            $htmlweekly .=  '<span id="username_'.$intLeaveID.'">'.$arrRequest['FullName'].'</span><br>';  
          }          
        }
                // ################################### Leave entered into Allocate?
                // Add the allocation and close the div
                if (isset($arrLeave['Allocations'][$strLogon][$i])) {
                  $htmlweekly .=  '<font color="#666666">'.$arrLeave['Allocations'][$strLogon][$i].'</font>';
                }
                else {
                  $htmlweekly .=  '<br>';       
                }
                if ($arrRequest['HasComments'] == 1 && $leaveCommentIconFlag == 1) {
                  $htmlweekly .=  '<div id="'.$intLeaveID.'" class="DutyCellBottomRight handcursor">';
                  $htmlweekly .=  '<img width="10" height="10" border="0" src="images/info.png"></img>';
                  $htmlweekly .=  "</div>";   
                } 
                $htmlweekly .=  '</div>'; 
                }
                $intICount++; 
              }
          
            }
          }
          $htmlweekly .=  '</td>';
          $colNum++;
        }      
        $htmlweekly .=  '</tr>';
      }
    }
    $rowcount++;
  } 
  $htmlweekly .=  '</table>';
  $htmlweekly .=  '<div class="tooltip-div" id="leaveTooltipDiv"></div>';
  $htmlweekly .=  '<span id="LStDiv"></span>';
  echo $htmlweekly;
?>
<script type="text/javascript">
<?php
if ($intAdmin == 0) {
?>
$(function() {
  $( "#datepicker" ).datepicker({
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    changeMonth: true,
    changeYear: true,
    dateFormat: "yy-mm-dd",
    defaultDate: "<?php echo  $strStartDate?>" ,
    onSelect: function (dateText, inst) {
      var currentdate = dateText;
      <?php echo $strCalendarAction?>(currentdate,<?php echo "'".$strUser."'"; ?>)
    } 
  });
}); 
<?php
}
else {
?>
$(function() {
  $( "#datepicker" ).datepicker({
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    changeMonth: true,
    changeYear: true,
    dateFormat: "yy-mm-dd",
    defaultDate: "<?php echo  $strStartDate?>" ,
    onSelect: function (dateText, inst) {
      var currentdate = dateText;
      <?php echo $strCalendarAction?>(currentdate, <?php echo $intLeaveGroup ?>)
    } 
  });
}); 
<?php
}
?>

var leaveIdContainer=0;
function showleavetip(type='',currObj,colNum=0,totalGrpRow=0,GropLineNo=0){
    var checkForTabCall = parseInt('<?php echo (isset($_POST['tabCall']) && !empty($_POST['tabCall'])) ? $_POST['tabCall'] : 0;?>');
    if((type == 'showtooltip') && (colNum > 0)){
        let leaveId = $(currObj).attr('id');
        if(leaveId != leaveIdContainer){
            var reqData = {
                'id': leaveId
            };
            leaveIdContainer = leaveId;
            $.ajax({
                type: "post",
                url: "/page-includes/leave/leave-info-tip.php",
                data: reqData,
                beforeSend: function(jqXHR, settings){
                  $('#loading').hide();
                },
                success: function(result){
                    var nativeTopPos = $(currObj).offset().top;
                    var nativeLeftPos = $(currObj).offset().left;
                    var topPos = nativeTopPos-50;
                    var leftPos = nativeLeftPos+165;
                    var cscrollTop = $('#content').scrollTop();
                    var TooltipDivposition = $('#LStDiv').position();
                    
                    if(checkForTabCall == 1){
                        topPos = nativeTopPos-80;
                        if((cscrollTop > 0) && (totalGrpRow == GropLineNo)) {
                          topPos = nativeTopPos+(cscrollTop-210);
                        }
                        if((cscrollTop == 0) && (totalGrpRow == GropLineNo) && (nativeTopPos >= 829)) {
                          topPos = nativeTopPos-320;
                        }
                        if((cscrollTop > 0) && (totalGrpRow == GropLineNo) &&  (nativeTopPos < 829)) {
                          topPos = nativeTopPos+(cscrollTop-230);
                        }
                        if((cscrollTop == 0) && (totalGrpRow == GropLineNo) &&  (nativeTopPos < 829)) {
                          topPos = nativeTopPos+(cscrollTop-230);
                        }
                        if((cscrollTop >= 100) && (totalGrpRow != GropLineNo)) {
                          topPos = nativeTopPos+(cscrollTop-20);
                        }
                        leftPos = nativeLeftPos+150;
                    }
                    if(totalGrpRow == GropLineNo && GropLineNo > 0 && checkForTabCall == 0){
                        topPos = nativeTopPos-150;
                    }
                    if((cscrollTop > 0) && (totalGrpRow == GropLineNo) && (checkForTabCall == 0)) {
                      topPos = nativeTopPos+(cscrollTop-150);
                    }
                    if((cscrollTop == 0) && (totalGrpRow == GropLineNo) && (nativeTopPos >= 829) && (checkForTabCall == 0)) {
                      topPos = nativeTopPos-320;
                    }
                    if((cscrollTop > 0) && (totalGrpRow == GropLineNo) &&  (nativeTopPos < 829) && (checkForTabCall == 0)) {
                      topPos = nativeTopPos+(cscrollTop-170);
                    }
                    if((cscrollTop >= 100) && (totalGrpRow != GropLineNo) && (checkForTabCall == 0)) {
                      topPos = nativeTopPos+(cscrollTop-20);
                    }
                    if(colNum > 2){
                        leftPos = nativeLeftPos-400;
                        if(checkForTabCall == 1){
                          leftPos = nativeLeftPos-415;
                        }
                    }
                    
                    $('#leaveTooltipDiv').css('left',leftPos+'px');
                    $('#leaveTooltipDiv').css('top',topPos+'px');
                    $('#leaveTooltipDiv').html(result);
                    $('#leaveTooltipDiv').show();
                }
            });
        } else {
            $('#leaveTooltipDiv').show();
        }
    } else {
        $('#leaveTooltipDiv').hide();
    }
}

</script>

<?php
}
?>