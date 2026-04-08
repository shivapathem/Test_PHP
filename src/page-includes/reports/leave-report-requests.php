<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
$arrperiods = array(0 => '',3 => '3 Months', 6  => '6 Months', 12 => '1 Year', 24 => '2 Years' );

$teamID = $_POST['teamID'] ?? 0;
$strDepartmentName = GetTeamNameFromID($teamID);

$arrPeriods = array( 0 => '---',
                     999 => 'Current Year', 
                     -12 => '1 Year Back',
                     -9  => '9 Months Back',
                     -6  => '6 Months Back',
                     -3  => '3 Months Back',
                     -1 => '1 Month Back', 
                      3 => '3 Months Forward', 
                      6 => '6 Months Forward', 
                      9 => '9 Months Forward', 
                      12 => '1 Year Forward'                           
                   );



if (isset($_POST['period'])) {
  $intPeriod = $_POST['period'];
  if ($intPeriod == 999) {
    $intCurrentYear = date("Y", strtotime("-4 months"));
    $strStartDate = $intCurrentYear.'-04-01';
    $strEndDate = ($intCurrentYear + 1).'-03-31';;      
  }
  else {
    if ($intPeriod < 0) {
      // Backwards
      $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
      $strEndDate = date("Y-m-d");  
    }
    else {
      // Forwards
      $strStartDate = date("Y-m-d");
      $strEndDate = date("Y-m-d", strtotime($intPeriod." months"));    
    }
  }
}  
else {
  if (isset($_POST['sDate'])) {
    $intPeriod = 0; 
    $strStartDate = $_POST['sDate'];
    $strEndDate = $_POST['eDate'];
  }
  else {
    $intPeriod = -12;  
    $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
    $strEndDate = date("Y-m-d");
  }  
}
?>
<div style="height: 400px">

<form onsubmit="return submitForm();" id="leavereportform<?php echo $teamID?>">
  <table class="tablesmall" width="100%">
    <tr>
      <th colspan="7">
        <br>Leave Requests for <?php echo $strDepartmentName?><br><br>
      </th>
    </tr>
    <tr>
      <th width="100px" height="30px">Start Date</th>
      <th align="right" width="100px"><input type="hidden" name="sDate" id="leave-requests-datepicker-start<?php echo $teamID?>" value="<?php echo $strStartDate?>" required/></th>
      <td width="250px"><input type="text" id="slrralternate<?php echo $teamID?>" size="30" value="<?php echo date("l, j F, Y", strtotime($strStartDate))?>"></td>
      <th width="100px">End Date</th>
      <th align="right" width="100px"><input type="hidden" name="eDate" id="leave-requests-datepicker-end<?php echo $teamID?>" value="<?php echo $strEndDate?>" size="20" required/></th>
      <td width="250px"><input type="text" id="elrralternate<?php echo $teamID?>" size="30" value="<?php echo date("l, j F, Y", strtotime($strEndDate))?>"></td>
      <td><input id="submit" type="submit" value="Search" name="Update"></td>      
    </tr>  
    
    <tr>      
      <th colspan="2" height="30px">
      Quick Links
      </th>
      <td colspan="5">
      <?php
      echo '<select class="chosen-select" name="QL" onchange="javascript:ShowQuickLinkLeave(value)";>';
      
      
      foreach ($arrPeriods as $thisperiod => $text) {
        if ($thisperiod == $intPeriod) {
          echo '<option selected value="'.$thisperiod.'">'.$text.'</option>';
        }
        else {
          echo '<option value="'.$thisperiod.'">'.$text.'</option>';
        }
      }
      echo '</select>';
      ?>
      </td>
    </tr>
  </table>
  <input type="hidden" name="teamID" value="<?php echo $teamID?>">
</form>
<br>
<?php
  // The Results.....
$arrLeave = ReadLeaveByDepartment($teamID, $strStartDate, $strEndDate);

if (isset($arrLeave['Groups'])) {

  // Now get the amounts allowed for each returned Group
  foreach ($arrLeave['Groups'] as $intGroupID => $arrGroup) {
    $arrLeaveAllowed[$intGroupID] = GetLeaveAllowedTotals($intGroupID, $strStartDate, $strEndDate);
  }

  echo '<table width="100%"" class="tablesmalltidy">';
  echo '<thead>';
  echo '<tr>';
  echo '<th></th>';
  echo '<th colspan="4">Within Limit</th>';
  echo '<th>&nbsp;</th>';  
  echo '<th colspan="5">Over Limit</th>';  
  echo '<th></th>';
  echo '<th>Availability</th>';
  echo '<th>&nbsp;</th>';  
  echo '<th colspan="3">Number of Days Matching Guaranteed Availability</th>';   
  echo '</tr>';  
  echo '<tr>';
  echo '<th>Leave Type</th>';
  echo '<th>Requests</th>';
  echo '<th>Approved</th>';
  echo '<th>Percentage Approved</th>';
  echo '<th>Unpproved</th>';
  echo '<th>&nbsp;</th>';
  echo '<th>Requests</th>';
  echo '<th>Approved</th>';
  echo '<th>Percentage Approved</th>';
  echo '<th>Over Limit</th>';
  echo '<th>Percentage Not Approved</th>';
  echo '<th>&nbsp;</th>';
  echo '<th>Requests Available</th>';
  echo '<th>&nbsp;</th>';  
  echo '<th>Under-requested</th>';
  echo '<th>Matching</th>';
  echo '<th>Over-requested</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  if (isset($arrLeave['Groups'])) {
  foreach ($arrLeave['Groups'] as $intGroupID => $arrGroup) {
    foreach ($arrGroup['Types'] as $intTypeID => $strTypeDescription) {
    echo '<tr>';
    echo '<td>'; 
    echo $arrGroup['Description'].' ('.$strTypeDescription.')';  
    echo '</td>';
    echo '<td>'; 
    if (isset($arrLeave['Leave'][$intGroupID][$intTypeID]['isOK'])) {
      $intCountOK = $arrLeave['Leave'][$intGroupID][$intTypeID]['isOK'];
    }
    else {
      $intCountOK = 0;
    }       
    echo $intCountOK;
    echo '</td>';    
    
    echo '<td>';    
    if (isset($arrLeave['Leave'][$intGroupID][$intTypeID]['isOKApproved'])) {
      $intCountOKApproved = $arrLeave['Leave'][$intGroupID][$intTypeID]['isOKApproved'];
    }
    else {
      $intCountOKApproved = 0;
    }       
    echo $intCountOKApproved;    
    echo '</td>';      

    echo '<td>';    
    if ($intCountOKApproved == 0) {
      $intPercentOKApproved = 0;
    }
    else {
      $intPercentOKApproved = round(($intCountOKApproved / $intCountOK) * 100, 1);
    }
    echo $intPercentOKApproved.'%';
    echo '</td>';

    echo '<td>';    
    if (isset($arrLeave['Leave'][$intGroupID][$intTypeID]['isOKNotApproved'])) {
      $intCountOKNorApproved = $arrLeave['Leave'][$intGroupID][$intTypeID]['isOKNotApproved'];
    }
    else {
      $intCountOKNorApproved = 0;
    }       
    echo $intCountOKNorApproved;    
    echo '</td>';    

    echo '<th>';
    echo '</th>';
        
    //  Not OK Requets
    echo '<td>'; 
    if (isset($arrLeave['Leave'][$intGroupID][$intTypeID]['isNotOK'])) {
      $intCountNotOK = $arrLeave['Leave'][$intGroupID][$intTypeID]['isNotOK'];
    }
    else {
      $intCountNotOK = 0;
    }       
    echo $intCountNotOK;
    echo '</td>'; 

    //  Not OK Requets Approved
    echo '<td>'; 
    if (isset($arrLeave['Leave'][$intGroupID][$intTypeID]['isNotOKApproved'])) {
      $intCountNotOKApproved = $arrLeave['Leave'][$intGroupID][$intTypeID]['isNotOKApproved'];
    }
    else {
      $intCountNotOKApproved = 0;
    }       
    echo $intCountNotOKApproved;
    echo '</td>';     

    echo '<td>';    
    if ($intCountNotOKApproved == 0) {
      $intPercentNotOKApproved = 0;
    }
    else {
      $intPercentNotOKApproved = round(($intCountNotOKApproved / $intCountNotOK) * 100, 1);
    }
    echo $intPercentNotOKApproved.'%';
    echo '</td>';    

    echo '<td>';    
    if (isset($arrLeave['Leave'][$intGroupID][$intTypeID]['isNotOKNotApproved'])) {
      $intCountNotOKNorApproved = $arrLeave['Leave'][$intGroupID][$intTypeID]['isNotOKNotApproved'];
    }
    else {
      $intCountNotOKNorApproved = 0;
    }       
    echo $intCountNotOKNorApproved;    
    echo '</td>'; 

    echo '<td>';    
    if ($intCountNotOKNorApproved == 0) {
      $intPercentNotOKNotApproved = 0;
    }
    else {
      $intPercentNotOKNotApproved = round(($intCountNotOKNorApproved / $intCountNotOK) * 100, 1);
    }
    echo $intPercentNotOKNotApproved.'%';
    echo '</td>'; 

    echo '<th>'; 
    echo '&nbsp;';
    echo '</th>';
            
    echo '<td>'; 
    echo $arrLeaveAllowed[$intGroupID][$intTypeID]['Available'];
    echo '</td>'; 
    echo '<th>&nbsp;</th>';      
    echo '<td>'; 
    echo $arrLeaveAllowed[$intGroupID][$intTypeID]['UnderAvailable'];
    echo '</td>';

    echo '<td>'; 
    echo $arrLeaveAllowed[$intGroupID][$intTypeID]['MatchesAvailable'];
    echo '</td>';

    echo '<td>'; 
    echo $arrLeaveAllowed[$intGroupID][$intTypeID]['OverAvailable'];
    echo '</td>';        
        
    echo '</tr>';
  }
}
}
echo '</tbody>';
echo '</table>';
echo '</div>';

}
else {
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1000px">';
  echo '<br>Either there is no leave Credited for this year yet or any Leave Requests in.<br>So it\'s not possible to show statistics.<br><br>';
  echo '</div>';
}
?>
<script type="text/javascript">
$(document).ready(function() {
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "200px"
  });
});

function submitForm() {
  $.ajax({type:'POST', url: 'page-includes/reports/leave-report-requests.php', data:$('#leavereportform<?php echo $teamID?>').serialize(), success: function(data) {
    $('#LeaveReportTabs-4').html(data); 
  }});
  return false;
}

function ShowQuickLinkLeave(period) {
  $.ajax({
        type: 'POST',
        url: "page-includes/reports/leave-report-requests.php",
        data: {
            'period': period,
            'teamID': <?php echo $teamID?>
        },
        success: function (data) {
          $('#LeaveReportTabs-4').html(data);
        },
        error:function (data) {
            alert('some error found in leave-report call.');
        }
    });
}

  $(function() {
    $( "#leave-requests-datepicker-start<?php echo $teamID?>" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#slrralternate<?php echo $teamID?>",
      altFormat: "DD, d MM, yy"
    });
  });
  $(function() {
    $( "#leave-requests-datepicker-end<?php echo $teamID?>" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#elrralternate<?php echo $teamID?>",
      altFormat: "DD, d MM, yy"
    });
  });
  </script>