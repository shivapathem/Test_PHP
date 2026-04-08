<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';
include_once __DIR__ .'/leave/service/LeaveService.php';

$intTeamID = $_REQUEST['teamid'] ?? 0;
$intOption = $_REQUEST['taboption'] ?? 0;
$activeScheduledPeoplePHLCheck = isset($_COOKIE["activescheduledpeoplePHL_".$intTeamID])  && ($_COOKIE["activescheduledpeoplePHL_".$intTeamID] ==1) ? 'checked' : '';
$activeScheduledPeoplePHLVal = isset($_COOKIE["activescheduledpeoplePHL_".$intTeamID])  && ($_COOKIE["activescheduledpeoplePHL_".$intTeamID] ==1) ? '1' : '0';
$arrLeaveCredits=[];
$intCategoaryID = $_REQUEST['catid'] ?? 0;
$leaveService = new LeaveService();
$arrAllocLeaveTypes = GetLeaveAllocateTypes();
if(isset( $_REQUEST['year'])) {
  $intLeaveYear = $_REQUEST['year'];
}
else {
  if (isset($_SESSION['allocations']["leave"]['curentleaveyear'])) {
    $intLeaveYear = $_SESSION['allocations']["leave"]['curentleaveyear'];
  } else {
    $intLeaveYear = date("Y", strtotime("-3 months"));
  }
}
$_SESSION['allocations']["leave"]['curentleaveyear'] = $intLeaveYear;

if(isset( $_REQUEST['HolID'])) {
  $intTimeDemensionID = $_REQUEST['HolID'];
} else {
  $intTimeDemensionID = 0;
}
if ($intTimeDemensionID == 0) {
  $strButtonDisabled = ' disabled';
} else {
  $strButtonDisabled = '';

}

if ($intTimeDemensionID != 0) {
  $getTimeDimensionDate = $leaveService->getTimeDimesionDataById($intTimeDemensionID);
  $timeDimensionHolidayDate = date('d-M-Y',strtotime($getTimeDimensionDate['dDateTime']));
} else {
  $timeDimensionHolidayDate = '';
}

$intActiveLeaveYear = GetCurrentLeaveYearGeneric();
if ($intActiveLeaveYear == $intLeaveYear) {
  $strButtonDisabledCheck = 'checkboxdiv';
} else {
  $strButtonDisabledCheck = 'activeclass';
}
$arrHolidays = GetHolidaysByYear($intLeaveYear);
$strDepartmentName = GetTeamNameFromID($intTeamID);
if (isset($arrAllocLeaveTypes[$intCategoaryID]['AllocName'])) {
$arrLeaveCredits = GetLeaveCreditsByHolidays($intTeamID, $intLeaveYear, $arrAllocLeaveTypes[$intCategoaryID]['AllocName'], $intTimeDemensionID,$intActiveLeaveYear);
}
  
echo '<table class="tablesmallnoborder" width="100%">';
  echo '<tr height="40px">';
  echo '<th width="150px" align="right" class="medtextbold leaveNav handcursor" onclick="javascript:GetLeaveCreditTab('.$intTeamID.','.$intOption.','.($intLeaveYear - 1).')">';
  echo '&lt;&lt; '.($intLeaveYear - 1);
  echo '</th>';   
  
  echo '<th width="50px" class="medtextbold leaveNav handcursor">';
  echo '|';
  echo '</th>';  
                                                                                  
  echo '<th width="150px" align="right" class="medtextbold leaveNav handcursor" onclick="javascript:GetLeaveCreditTab('.$intTeamID.','.$intOption.','.($intLeaveYear + 1).')">';
  echo ($intLeaveYear + 1).' &gt;&gt;';
  echo '</th>';

  echo '<th id="textcenter">';
  echo '<span><b>PHL Credit for '.$strDepartmentName.'</b></span><br>';
  echo '<span><b>Showing Year '.$intLeaveYear.'</b></span><br>';
  echo '<span><b>Current Leave Year '.$intActiveLeaveYear.'</b></span>';
  echo '</th>';
  echo '<th><button id="expiredPHL" class="btn leaveCreditBTN handcursor" onClick="ProcessExpiredPHL();">Process Expired PHLs</button><button id="expiringPHL" class="btn leaveCreditBTN handcursor" onClick="viewExpiringPHL();">View Expiring PHLs</button></th>';  
  echo '</tr>';
  echo '</table>';  
  
    echo '<form name="frmleavecredit-hol'.$intTeamID.'" id="frmleavecredit-hol'.$intTeamID.'">';  
    echo '<input type="hidden" name="year" value="'.$intLeaveYear.'">';
    echo '<input type="hidden" name="teamid" value="'.$intTeamID.'">';  
  
    echo '<h3 class="PHLheading m-14">';
    echo 'New Leave Credit';
    echo '</h3>';
    echo '<div class=" checkboxdivPHL '.$strButtonDisabledCheck.'"><input type="checkbox" id="activescheduledpeoplePHL_'.$intTeamID.'" name="activescheduledpeoplePHL_" value="'.$activeScheduledPeoplePHLVal.'"  '.$activeScheduledPeoplePHLCheck.'>
    <label for="activescheduledpeoplePHL_">Show only current scheduled people</label></div>';
    echo '<div>';   

    echo '<table class="redtable compact stripe" width="100%">';
    if (isset($arrHolidays)) {    
      echo '<tr>';
      echo '<th>Holiday</th>';
      echo '<td width="150px" class="textcenter">';
      echo '<select class="chosen-select" size="1" id= "hoildaySelectId"  name="Holiday" onchange="javascript:GetLeaveCreditTab('.$intTeamID.','.$intOption.','.$intLeaveYear.', value)";>';
      echo '<option value="0">Show All</option>';
      foreach ($arrHolidays as $intHolid => $arrHoliday) { 
        if ($intTimeDemensionID == $intHolid) {
          echo '<option selected value="'.$intHolid.'">'.$arrHoliday['Event'].'</option>';      
        }  
        else {
          echo '<option value="'.$intHolid.'">'.$arrHoliday['Event'].'</option>';
        }
      }
      echo '</select>';
      echo '</td>';
      echo '<th>Date</th>';
      echo '<td width="150px">';
      echo '<input name="HolidayDate" id="HolidayDate" value="'.$timeDimensionHolidayDate.'" type="text" disabled="disabled">';
      echo '</td>';
      echo '<th>Use Duty Durations</th>';
      echo '<td class="textcenter">';
      echo '<input name="UseDuty" id="UseDuty" value="ON" type="checkbox">';
      echo '</td>';
  
      echo '<td width="250px">';  
      echo '<div id="errorBox" class="lightcell"></div>'; 
      echo '</td>';   
      echo '</tr>';
      echo '<tr>';
      echo '<td></td>';
      echo '<td colspan="6" class="textcenter">';
      echo '<input'.$strButtonDisabled.' id= "addUpdatePHL" name="submit" type="submit" value="Add">';
      echo '</td>';
      echo '</tr>';
   } else {
      echo '<tr height="50px">';
      echo '<th>';
      echo 'There are no Holidays defined for this year.';
      echo '</th>';
      echo '</tr>';    
   }        
    echo '</table>';
    echo '</div>'; 
  echo '<table class="tablesmall compact stripe" id="LeaveCreditsTable-'.$intTeamID.'-'.$intOption.'">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';  
  echo '<input type="checkbox" name="checkAll" class="checkAll" id="checkAllBox">';
  echo '</th>';  
  echo '<th>';  
  echo 'Name';
  echo '</th>';
  echo '<th>';  
  echo 'Sort Code';
  echo '</th>';
  echo '<th>';  
  echo 'Default PHL Amount';
  echo '</th>';  
         
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>'; 
  if (!empty($arrLeaveCredits)) {
    foreach ($arrLeaveCredits as $scheduledPersonId => $arrCreditsPerson) {
      $strCheckboxDisabled = $arrCreditsPerson['TimeDemensionID']== 0  && $intTimeDemensionID != 0 ? 'buttonEnabled' : 'buttonDisabled';
      $title = $arrCreditsPerson['TimeDemensionID']== 0  && $intTimeDemensionID != 0 ? '' : $arrCreditsPerson['Name'].' have PHL credit';
      $allocateId = isset($arrCreditsPerson['AllocateID']) ? $arrCreditsPerson['AllocateID'] : 0;
      $dutyDuration = isset($arrCreditsPerson['Duration']) ? $arrCreditsPerson['Duration'] : 0;
      $intPHLLeaveAmount =  floor($arrCreditsPerson['PHLLeaveAmount']*4)/4;
      $intPHLLeaveAmountDis =  number_format(($arrCreditsPerson['PHLLeaveAmount']),2, '.', '');
      echo '<tr class="handcursor">';
      echo '<td>';
      echo '<input title="'.$title.'"  name="scheduledpersonBox" class="scheduledpersonCheckBox  '.$strCheckboxDisabled.'" id="'.$scheduledPersonId.'" value="'.$scheduledPersonId.','.$dutyDuration.','.$arrCreditsPerson['StaffNumber'].','.$allocateId.'" type="checkbox">';
      echo '</td>';    
      echo '<td  onclick="javascript:ShowAllocateLeaveCredit(\''.$arrCreditsPerson['Login'].'\',\''.$intLeaveYear.'\','.$intTeamID.')">';
      echo $arrCreditsPerson['Name'];
      echo '</td>';
      echo '<td>';
      echo $arrCreditsPerson['SortCode'];
      echo '</td>';  
      echo '<td>';
      echo '<span class="activehidestatus">'.$intPHLLeaveAmount.'</span><input type="text" class="PHLLeaveAmt " id="PHLLeaveAmt'.$scheduledPersonId.'" value="'.$intPHLLeaveAmountDis.'" onfocusout="updatePHLLeaveAmt(\''.$scheduledPersonId.'\')">';
      echo '</td>';     
      echo '</tr>';  
    }
  } else {
    echo '<tr>';
    
    echo '<td></td>';
    echo '<td></td>';
    
    echo '<td>No Record Found</td>';
    echo '<td></td>';
    
    echo '</tr>';
    echo '</tr>';
  } 
  echo '</tbody>';  
  echo '</table>';
echo '</form>';
?>
<script type="text/javascript">
$(document).ready( function () {
  <?php if(!isset( $_REQUEST['HolID'])) { ?>
    localStorage.removeItem("checkboxValues");
  <?php } ?>
    var checkboxValues = JSON.parse(localStorage.getItem('checkboxValues')) || {};
    $('.checkAll').click(function(){
      
      if (this.checked) {
        checkboxValues['checkAllBox'] = this.checked;
         $(".buttonEnabled").prop("checked", true);
         storeCheckbox();
      } else {
         $(".scheduledpersonCheckBox").prop("checked", false);
         localStorage.removeItem("checkboxValues");
      }	
   });
var $checkboxes = $("input[name='scheduledpersonBox']:checkbox");

$checkboxes.on("change", function(){
  storeCheckbox();
});
function storeCheckbox(){
  $('input[name="scheduledpersonBox"]:checked').each(function(){
    checkboxValues[this.id] = this.checked;
  });
  localStorage.setItem("checkboxValues", JSON.stringify(checkboxValues));

}
  $('#UseDuty').click(function(){
            if($(this).prop("checked") == true){
              $("#UseDuty").prop('checked', true);
            }
            else if($(this).prop("checked") == false){
              $("#UseDuty").prop('checked', false);
            }
  })
  var table = $("#LeaveCreditsTable-<?php echo $intTeamID?>-<?php echo $intOption?>").DataTable({
  paging: false,
  destroy: true,
  scrollY: parseInt($(window).height() - 450),
  info: false,
  stateSave: true,
  deferRender: true,
   'columnDefs': [
      {
          width: 300, targets: 3     
      
      }
   ],
   'initComplete': function(settings){
     

    $.each(checkboxValues, function(key) {
        if(!$("#"+key).hasClass('buttonDisabled')){
          $("#"+key).prop('checked',true);
        }       
      });
         
      },
  });

  yadcf.init(table, [
    {column_number: 1,
      filter_type: 'text'
    },
    {column_number: 2,
      filter_type: 'text'
    },
    {column_number: 3,
        filter_type: 'text'
    },
  ]);
  
  if ( $.cookie("vscrollphl_<?php echo $intTeamID?>") !== null ) {
	        $(".dataTables_scrollBody").scrollTop(Math.abs($.cookie("vscrollphl_<?php echo $intTeamID?>")));
      }
    
        $(".dataTables_scrollBody").on("scroll", function() {
          // Set a cookie that holds the scroll position.
		    $.cookie("vscrollphl_<?php echo $intTeamID?>", $(".dataTables_scrollBody").scrollTop() );
		});
  <?php if($intTimeDemensionID == 0){ ?>
    $(".checkAll").prop("disabled",true);
  $(".scheduledpersonCheckBox").prop("disabled",true);
  $("#UseDuty").prop("disabled",true);
  <?php } ?>
  
    $('#frmleavecredit-hol<?php echo $intTeamID?>').validate({
      errorLabelContainer: "#errorBox",
      rules:{
        "Amount":{
          required:true,
        }
      },
      messages: {
        Amount: "Please Enter an Amount<br>"
      },
      

      submitHandler: function(form) {
        // Iterate over all selected checkboxes
        $('input[type="submit"]').prop('disabled', true);
        
        $('input[name="scheduledpersonBox"]:checked').each(function(index, rowId){
         
          var rowVal = '';
          if($('#UseDuty').prop("checked") == true){
            var splitVal = $(this).val().split(',');
            if(splitVal[1] <= 0){
              var userPHLVal = $('#PHLLeaveAmt'+splitVal[0]).val();
              rowVal = splitVal[0]+','+userPHLVal+','+splitVal[2]+','+splitVal[3]+',WD';
            } else {
              rowVal = $(this).val() +',D';
            }
          } else {
            var splitVal = $(this).val().split(',');
            var userPHLVal = $('#PHLLeaveAmt'+splitVal[0]).val();
            rowVal = splitVal[0]+','+userPHLVal+','+splitVal[2]+','+splitVal[3]+',WD';
          }
          // Create a hidden element
          $(form).append($('<input>').attr('type', 'hidden').attr('name', 'scheduledPersonIds[]').val(rowVal));
        });
        $(form).append($('<input>').attr('type', 'hidden').attr('name', 'action').val('updatePHLonleaveAllocation'));
        $.ajax({type:'POST', url: 'page-includes/admin/leave/leave-process-PHL.php', data:$('#frmleavecredit-hol<?php echo $intTeamID?>').serialize(), success: function(data) {
          GetLeaveCreditTab(<?php echo $intTeamID?>, <?php echo $intOption?>, <?php echo $intLeaveYear?>)
        }});
      }
  })   
}) 
       
function PHLLeaveAmount (StaffNumber) {
  $.post("page-includes/admin/depts-change-eft.php", {
    StaffNumber: StaffNumber,
    teamid: <?php echo $intTeamID?>,
    source: 1,
    year: <?php echo $intLeaveYear?>
  },
  function(data,status){
    $.facebox(data);
   }
  )
}

function updatePHLLeaveAmt(ScheduledPersonID){
 if( $('#PHLLeaveAmt'+ScheduledPersonID).val() > 22){
  $('#PHLLeaveAmt'+ScheduledPersonID).addClass("leaveManageerror");
      $('#PHLLeaveAmt'+ScheduledPersonID).focus();
      customAlertByModel("PHL credit amount cannot be more than 22.<br>Please check the row(s) highlighted in Red.");
      $(".checkAll").prop("disabled",true);
      $("#UseDuty").prop("disabled",true);
      $("#addUpdatePHL").prop("disabled",true);
      return;
 }else{
        let PHLLeaveAmt = $('#PHLLeaveAmt'+ScheduledPersonID).val();
        let dataPost = {'action':'updatePHLLeaveAmount','ScheduledPersonID':ScheduledPersonID,'PHLLeaveAmount':PHLLeaveAmt};
        $.ajax({
          type:'POST', 
          url: 'page-includes/admin/leave/leave-process-PHL.php', 
          data:dataPost, 
          success: function(response) {
            let responseData = $.parseJSON(response);
            if(responseData.status !='error'){
              $('#PHLLeaveAmt'+ScheduledPersonID).focusout();
              $('#PHLLeaveAmt'+ScheduledPersonID).removeClass("leaveManageerror");
              if($('#hoildaySelectId').val() > 0 && !$('.PHLLeaveAmt').hasClass("leaveManageerror")){ 
                  $(".checkAll").prop("disabled",false);
                  $("#UseDuty").prop("disabled",false);
                  $("#addUpdatePHL").prop("disabled",false);
              } 
            }else{
              $('#PHLLeaveAmt'+ScheduledPersonID).addClass("leaveManageerror");
              $('#PHLLeaveAmt'+ScheduledPersonID).focus();
              customAlertByModel("PHL credit amount cannot be more than 22.<br>Please check the row(s) highlighted in Red.");
              $(".checkAll").prop("disabled",true);
              $("#UseDuty").prop("disabled",true);
              $("#addUpdatePHL").prop("disabled",true);
              return ;
            }
          }
        });
  } 
 
}
/** Expire PHL Code Start*/
function viewExpiringPHL() {
  $.post("page-includes/admin/expiringPHL.php", {
    year: <?php echo $intLeaveYear?>,
    teamid: <?php echo $intTeamID?>,
  },
  function(data,status){
    $.facebox(data);
   }
  )
}

function ProcessExpiredPHL() {
  $.post("page-includes/admin/expiredPHL.php", {
    year: <?php echo $intLeaveYear?>,
    teamid: <?php echo $intTeamID?>,
  },
  function(data,status){
    $.facebox(data);
   }
  )
}  

$("#activescheduledpeoplePHL_"+<?php echo $intTeamID?>).on('click',function(){
  if($(this).val() == 1){
        $(this).removeAttr('checked');
        $.cookie("activescheduledpeoplePHL_"+<?php echo $intTeamID?>, 0);
  }else{
    $(this).attr('checked', 'checked');
    $.cookie("activescheduledpeoplePHL_"+<?php echo $intTeamID?>, 1);
  }
  $(this).val($.cookie("activescheduledpeoplePHL_"+<?php echo $intTeamID?>));

  GetLeaveCreditTab(<?php echo $intTeamID?>, <?php echo $intOption?>, <?php echo $intLeaveYear?>)
     
});

/** Expire PHL Code END*/
</script>