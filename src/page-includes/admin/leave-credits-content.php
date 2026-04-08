<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../users/process/classUserSetup.php';
$setupObj = new classUserSetup();

$intTeamID = $_REQUEST['teamid'] ?? 0;
$intOption = $_REQUEST['taboption'] ?? 0;
$additionalflag = $_REQUEST['additionalflag'] ?? 0;
$typeval = $_REQUEST['typeval'] ?? 0;
$arrAllocLeaveTypes = GetLeaveAllocateTypes();
$activeScheduledPeopleCheck =  '';
$activeScheduledPeopleVal = '0';
$loggedUsedInfo = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);

if ($loggedUsedInfo['Teams'][$intTeamID]['SchedulingTeamAdmin']!=1){
	foreach ($arrAllocLeaveTypes as $key => $element) {
		if ($element['AllocName']=='Additional') {
			unset($arrAllocLeaveTypes[$key]);
		}
	}	
}

if(isset($_COOKIE["activescheduledpeopled_".$intTeamID])  && ($_COOKIE["activescheduledpeopled_".$intTeamID] ==1) ){
  $activeScheduledPeopleVal =  '1';
  $activeScheduledPeopleCheck ='checked' ;
}

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

$intActiveLeaveYear = GetCurrentLeaveYearGeneric();
$strTeamName = GetTeamNameFromID($intTeamID);
$activeScheduledPeople = (isset($_COOKIE["activescheduledpeopled_".$intTeamID]) && ($intActiveLeaveYear == $intLeaveYear)) ? $_COOKIE["activescheduledpeopled_".$intTeamID] : 0;
$arrLeaveCredits = GetLeaveCreditsSummary($intTeamID, $intLeaveYear, $arrAllocLeaveTypes,$activeScheduledPeople,$additionalflag);
if ($intActiveLeaveYear == $intLeaveYear) {
  $strButtonDisabled = 'checkboxdiv';
} else {
  $strButtonDisabled = 'activeclass';
}

$intLeaveYearStart = $intLeaveYear.'-04-01';
$intLeaveYearEnd = ($intLeaveYear + 1).'-03-31';

if (isset($_REQUEST['updateCheckedIdsVal'])) {
    if (is_string($_REQUEST['updateCheckedIdsVal'])) {
        $updateCheckedIdsVal = explode(',', $_REQUEST['updateCheckedIdsVal']);
    } elseif (is_array($_REQUEST['updateCheckedIdsVal'])) {
        $updateCheckedIdsVal = $_REQUEST['updateCheckedIdsVal'];
    }
} else {
    $updateCheckedIdsVal = [];
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

  echo '<th id="textcenter" class= "leavecreditheaderW">';
  echo '<span><b>Leave Credits for '.$strTeamName.'</b></span><br>';
  echo '<span><b>Showing Year '.$intLeaveYear.'</b></span><br>';
  echo '<span><b>Current Leave Year '.$intActiveLeaveYear.'</b></span>';
  echo '</th>';
  echo '<th><div class="checkboxdivcredit '.$strButtonDisabled.'"><input type="checkbox" id="activescheduledpeopled_'.$intTeamID.'" name="activescheduledpeopled" value="'.$activeScheduledPeopleVal.'"  '.$activeScheduledPeopleCheck.'>
        <label "for="activescheduledpeopled">Show only current scheduled people</label></div></th>';
  echo '<th><button  name="carryover" class="'.$strButtonDisabled.' btn  handcursor leaveCreditBTN carroverleave">CarryOver</button>';
  echo '</th>';
  echo '</tr>';
  echo '</table>';  
  
    echo '<form name="frmleavecredit'.$intTeamID.'" id="frmleavecredit'.$intTeamID.'">';  
    echo '<input type="hidden" name="year" value="'.$intLeaveYear.'">';
    echo '<input type="hidden" name="teamid" value="'.$intTeamID.'">';  
  
    echo '<div id="accordion-cred'.$intTeamID.'">';
    echo '<h3 class="PHLheading m-14">';
    echo 'New Leave Credit';
    echo '</h3>';
    echo '<div>';   

    echo '<table class="redtable newLeaveBlock compact stripe" width="100%">';
	
    echo '<tr>';
    echo '<th>Leave Type</th>';
    echo '<td>';  
    echo '<select size="1" name="LeaveType" id ="creditLeaveType"  class="creditLeaveType" onChange="GetFilteredLeaveCreditsSummary(this.value);">';
    foreach ($arrAllocLeaveTypes as $intLTid => $arrAllocLeaveType) { 
	  if ($typeval==$intLTid){$selected="selected";} else {$selected='';}
      if ($arrAllocLeaveType['isCreditable'] == 1) {
        echo '<option value="'.$intLTid.'" '.$selected.'>'.$arrAllocLeaveType['Description'].'</option>';
      }
    }  
    echo '</select>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';    
    echo '<th>Amount</th>';
    echo '</td>';     
    echo '<td>';
    echo '<input type="text" id="Amount" name="Amount" size="18">';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th>Credit Date</th>';
    echo '<td><input type="text" id="salternate"  class="leaveCreditDate salternate" size="18" value="'.date("l, j F, Y", strtotime($intLeaveYearStart)).'" readonly="true"> 
	<input type="hidden" name="sDate" class="datepicker-start" value="'.$intLeaveYearStart.'" required/></td>';
    echo '</tr>';
	
    echo '<tr>';
    echo '<th>Comments</th>';
    echo '</td>';    
    echo '<td>';    
    echo '<textarea rows="4" name="Comments" cols="50"></textarea>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td></td>';
    echo '<td colspan="3">';
    echo '<input name="submit" type="submit" value="Submit">';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
  
  echo '<table class="tablesmall compact leaveCreditTable stripe" id="LeaveCreditsTable-'.$intTeamID.'-'.$intOption.'">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';  
  echo '</th>';  
  echo '<th>';  
  echo 'Name';
  echo '</th>';
  echo '<th>';  
  echo 'Sort Code';
  echo '</th>';
  echo '<th>';  
  echo 'Staff Number';
  echo '</th>';  
  echo '<th>';  
  echo 'EFT';
  echo '</th>'; 
  foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    echo '<th>';
    echo $arrAllocLeaveType['Description'];
    echo '</th>';
  }       
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';  
  if (!empty($arrLeaveCredits)) {
  foreach ($arrLeaveCredits as $schdefdulledPerson => $arrCreditsPerson) {
	echo '<tr class="handcursor">';
  if (isset($arrCreditsPerson['ScheduledPersonID'])) {
    $isChecked = in_array($arrCreditsPerson['ScheduledPersonID'], $updateCheckedIdsVal) ? 'checked' : '';
    echo '<td id="'.$arrCreditsPerson['ScheduledPersonID'].'">';
    echo '<input type="checkbox" name="staffnumbers[]" value="'.$arrCreditsPerson['StaffNumber'] . "_" . $schdefdulledPerson.'" class="ScheduledPersonCheckbox dt-checkboxes"  '.$isChecked.'>';
    echo '</td>';
    echo '<td  onclick="javascript:ShowAllocateLeaveCredit(\''.$arrCreditsPerson['Login'].'\',\''.$intLeaveYear.'\','.$intTeamID.')">';
    echo $arrCreditsPerson['Name'];
    echo '</td>';
    echo '<td>';
    echo $arrCreditsPerson['SortCode'];
    echo '</td>';
    echo '<td>';
    echo $arrCreditsPerson['StaffNumber'];
    echo '</td>';
    echo '<td class="handcursor">';

    echo $EFTval= $arrCreditsPerson['EFT'] ==0 ? '' : $arrCreditsPerson['EFT'];
    echo '</td>';
  } else {
    echo '<td colspsan="5">&nbsp;</td>';
  }
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      echo '<td>';
      if (isset($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']])) {
        $intCredit =number_format(($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']]),2, '.', '');
      } else {
        $intCredit = 0;
      }
      echo $intCredit;
    }
     echo '</tr>';
  }
} else {
    $totalrow = count($arrAllocLeaveTypes)+5;
    $middlerecord = round((count($arrAllocLeaveTypes)+5)/2);
    echo '<tr>';
      for ($i = 1; $i<=$totalrow;$i++) {
        if ($middlerecord == $i) {
          echo '<td>No Record</td>';
        } else {
          echo '<td></td>';
        }
      }
    echo '</tr>';
  }
  echo '</tbody>';
  echo '</table>';

echo '</form>';
?>

<script type="text/javascript">
$(document).ready( function () {
	
  var table = $("#LeaveCreditsTable-<?php echo $intTeamID?>-<?php echo $intOption?>").DataTable({
  paging: false,
  destroy: true,
  scrollY: parseInt($(window).height() - 400),
  info: false,
  stateSave: true,
  deferRender: true,
   'columnDefs': [
      {
        'orderable': false, 
        'targets': 0,
        'checkboxes': true,
        render: function (data, type, row, meta) {
          return data;
        }
      }
   ],
  "initComplete": function( settings, json ) {
    //ResizeUserDeptTable();
    $('#loading').hide();
  } 
  });
  yadcf.init(table, [
    {column_number: 1,
      filter_type: 'text'
    },
    {column_number: 2,
      filter_type: 'text'
    },
    {column_number: 4,
        filter_type: 'multi_select', 
        select_type: 'chosen',
		filter_default_label: " "
    },
  ]);
  
  if ( $.cookie("vscroll_<?php echo $intTeamID?>") !== null ) {
	  $(".dataTables_scrollBody").scrollTop(Math.abs($.cookie("vscroll_<?php echo $intTeamID?>")));
  }
    
  $(".dataTables_scrollBody").on("scroll", function() {
     // Set a cookie that holds the scroll position.
	 $.cookie("vscroll_<?php echo $intTeamID?>", $(".dataTables_scrollBody").scrollTop() );
	});
  
    $('#frmleavecredit<?php echo $intTeamID?>').validate({
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
		var rows_selected = table.column(0).checkboxes.selected();
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/leave-credits-new.php', data:$('#frmleavecredit<?php echo $intTeamID?>').serialize(), success: function(data) {
            GetLeaveCreditTab(<?php echo $intTeamID?>, <?php echo $intOption?>, <?php echo $intLeaveYear?>,0,<?php echo $additionalflag;?>,<?php echo $typeval;?>)
          }});
        }
  })  
  
  $(function() {
	let addval=<?php echo $typeval;?>;
	if (addval>0){
		$( "#accordion-cred<?php echo $intTeamID?>" ).accordion({
				'active': 0,
				'collapsible': 1,
		});
	 } else {
		  $( "#accordion-cred<?php echo $intTeamID?>" ).accordion({
				'active': 1,
				'collapsible': 1,
		});
	 } 
	 $("#accordion-cred<?php echo $intTeamID?> .ui-accordion-header").click(function() {
		 var isActive = $("#accordion-cred<?php echo $intTeamID?>").accordion( "option", "active");
		 if(isActive=="false") {
			$( "#accordion-cred<?php echo $intTeamID?>" ).accordion({
					'active': true,
					'collapsible': true,
			});
		}
	});
  });  
  
  $(function() {
    $( ".datepicker-start" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: ".salternate",
      altFormat: "DD, d MM, yy",
      minDate: "<?php echo $intLeaveYearStart?>",
      maxDate: "<?php echo $intLeaveYearEnd?>"
    });
  });   
}) 

$(".carroverleave").on('click',function(){
  $confirmboxmessage ='This will carry over any non-zero leave balances for this Scheduling Team from <br/> the previous leave year into this year. Do you want to continue?';
      customConfirmModal($confirmboxmessage,function(){
        $.ajax({type:'POST', url: 'page-includes/admin/carry-over-leave.php',
           data:{teamid: <?php echo $intTeamID?>,year: <?php echo $intLeaveYear?>}
           , success: function(data) {
            GetLeaveCreditTab(<?php echo $intTeamID?>, <?php echo $intOption?>, <?php echo $intLeaveYear?>)
          }});
      },
      function() {
          $.facebox.close();
          return false;
      }
    )
}); 
 
$("#activescheduledpeopled_"+<?php echo $intTeamID?>).on('click',function(){
  if($("#activescheduledpeopled_"+<?php echo $intTeamID?>).val() == 1){
        $("#activescheduledpeopled_"+<?php echo $intTeamID?>).removeAttr('checked');
        $.cookie("activescheduledpeopled_"+<?php echo $intTeamID?>, 0);
  }else{
    $("#activescheduledpeopled_"+<?php echo $intTeamID?>).attr('checked', 'checked');
    $.cookie("activescheduledpeopled_"+<?php echo $intTeamID?>, 1);
  }
  $("#activescheduledpeopled_"+<?php echo $intTeamID?>).val($.cookie("activescheduledpeopled_"+<?php echo $intTeamID?>));

  GetLeaveCreditTab(<?php echo $intTeamID?>, <?php echo $intOption?>, <?php echo $intLeaveYear?>)
     
});

function GetFilteredLeaveCreditsSummary(typeval) {
	let str=creditLeaveType.options[creditLeaveType.selectedIndex].text;
	str = str.toLowerCase();
	let substr ="additional";
	let result =str.indexOf(substr); 
	let intOption= <?php echo $intOption?>;
	let intTeamID=<?php echo $intTeamID?>;
	let year =<?php echo $intLeaveYear?>;
	let additionalflag=0;
  let updateCheckedIdsVal = updateCheckedIds();
	if (result==0){
		additionalflag=1;
	} 
	GetLeaveCreditTab(intTeamID, intOption, year,0,additionalflag,typeval,updateCheckedIdsVal)
} 

function updateCheckedIds() {
    var checkedIds = [];
    $('#LeaveCreditsTable-<?php echo $intTeamID ?>-<?php echo $intOption ?> tbody input[type="checkbox"]:checked').each(function() {
        var rowId = $(this).closest('tr').find('td').attr('id');
        if (rowId) {
            checkedIds.push(rowId);
        }
    });
    return checkedIds;
}
</script>