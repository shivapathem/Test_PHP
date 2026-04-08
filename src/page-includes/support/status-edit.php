<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$arradmin = array('None','Shift Leader','Skills Admin','Leave Admin', 'Global Leave Admin', 'Global Admin');
$arrattach = array('No','Internal','External');

$strLogin = $_REQUEST['login'];
$intDepartmentID = $_REQUEST['department'];
$intTabOption = $_REQUEST['currenttab'];

$db = OpenDatabase();

if (isset($_POST['update'])) {
  // Depends what type of info is being updated.....
  
  switch ($intTabOption) {
    case 0:
      $onattachment = $_POST['onattachment'];
      $attachstartdate = $_POST['sAttachDate'];
      $attachenddate = $_POST['eAttachDate'];
      if (isset($_POST['attachmentnotes'])) {
        $attachmentnotes = escapeSingleQuotes($_POST['attachmentnotes']);
      }
      else {
        $attachmentnotes = '';
      }
      $strHistory = 'Record updated by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i").'.<br>';
      $strHistory.= 'On Attachment was set to '.$arrattach[$onattachment].'.<br>';
      $strHistory.= 'The Start Date was set to ';
      if ($attachstartdate == '') {
        $strHistory.= 'Nothing.<br>';
      }
      else {
        $strHistory.= $attachstartdate.'.<br>';
      }
      $strHistory.= 'The End Date was set to ';
      if ($attachenddate == '') {
        $strHistory.= 'Nothing.<br>';
      }
      else {
        $strHistory.= $attachenddate.'.<br>';
      }      
      $strHistory.='<hr>';

      $strHistory = escapeSingleQuotes($strHistory);
      
      // Do the update
      $strQuery = "IF EXISTS     (SELECT        Login
                   FROM          staff_status
                   WHERE         (Login = '$strLogin'))
                   UPDATE        staff_status
                   SET           IsOnAttach = $onattachment,"; 
                   if ($attachstartdate == '' ) {
                     $strQuery.= "AttachStartDate = NULL,";
                   }
                   else {
                     $strQuery.= " AttachStartDate = CONVERT(DATETIME, '$attachstartdate 00:00:00', 102),"; 
                   }
                   if ($attachenddate == '' ) {
                     $strQuery.= "AttachEndDate = NULL,";
                   }
                   else {
                     $strQuery.= " AttachEndDate = CONVERT(DATETIME, '$attachenddate 00:00:00', 102),"; 
                   }                   

                   $strQuery.= " AttachNotes = N'$attachmentnotes',
                   History = CONCAT('$strHistory', ISNULL(History,''))
                   WHERE        (Login = N'$strLogin')
                   ELSE                   
              
                   INSERT INTO staff_status (Login, IsOnAttach, AttachStartDate, AttachEndDate, AttachNotes, History)
                   VALUES        ('$strLogin', 
                                   $onattachment,"; 
                    if ($attachstartdate == '' ) {
                     $strQuery.= "NULL,";
                   }
                   else {
                     $strQuery.= " CONVERT(DATETIME, '$attachstartdate 00:00:00', 102),"; 
                   }
                   if ($attachstartdate == '' ) {
                     $strQuery.= " NULL,";
                   }
                   else {
                     $strQuery.= " CONVERT(DATETIME, '$attachenddate 00:00:00', 102),"; 
                   }                                   
                   $strQuery.= " '$attachmentnotes',
                   '$strHistory')";

      //echo $strQuery;
      sqlsrv_query($db, $strQuery);
    break;
    case 1:
          // FTC
      if (isset($_POST['ftc'])) {
        $ftc = 1;
        $strFTCHistory = "Yes";
      }
      else {
        $ftc = 0;
        $strFTCHistory = "No";
      }
      $ftcstartdate = $_POST['sftcDate'];
      $ftcenddate = $_POST['eftcDate'];
      if (isset($_POST['ftcnotes'])) {
        $ftcnotes = escapeSingleQuotes($_POST['ftcnotes']);
      }
      else {
        $ftcnotes = '';
      }
      
      $strHistory = 'Record updated by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i").'.<br>';
      $strHistory.= 'FTC was set to '.$strFTCHistory.'.<br>';
      $strHistory.= 'The Start Date was set to ';
      if ($ftcstartdate == '') {
        $strHistory.= 'Nothing.<br>';
      }
      else {
        $strHistory.= $ftcstartdate.'.<br>';
      }
      $strHistory.= 'The End Date was set to ';
      if ($ftcenddate == '') {
        $strHistory.= 'Nothing.<br>';
      }
      else {
        $strHistory.= $ftcenddate.'.<br>';
      }      
      $strHistory.='<hr>';
      $strHistory = escapeSingleQuotes($strHistory);      
      
      // Do the update
      $strQuery = "IF EXISTS     (SELECT        Login
                   FROM          staff_status
                   WHERE         (Login = '$strLogin'))
                   UPDATE        staff_status
                   SET           isFTC = $ftc,"; 
                   if ($ftcstartdate == '' ) {
                     $strQuery.= "FTCStartDate = NULL,";
                   }
                   else {
                     $strQuery.= " FTCStartDate = CONVERT(DATETIME, '$ftcstartdate 00:00:00', 102),"; 
                   }
                   if ($ftcenddate == '' ) {
                     $strQuery.= "FTCEndDate = NULL,";
                   }
                   else {
                     $strQuery.= " FTCEndDate = CONVERT(DATETIME, '$ftcenddate 00:00:00', 102),"; 
                   }                   

                   $strQuery.= " FTCNotes = N'$ftcnotes',
                   History = CONCAT('$strHistory', ISNULL(History,''))
                   WHERE        (Login = N'$strLogin')
                   ELSE                   
              
                   INSERT INTO staff_status (Login, isFTC, FTCStartDate, FTCEndDate, FTCNotes, History)
                   VALUES        ('$strLogin', 
                                   $ftc,"; 
                    if ($ftcstartdate == '' ) {
                     $strQuery.= "NULL,";
                   }
                   else {
                     $strQuery.= " CONVERT(DATETIME, '$ftcstartdate 00:00:00', 102),"; 
                   }
                   if ($ftcenddate == '' ) {
                     $strQuery.= " NULL,";
                   }
                   else {
                     $strQuery.= " CONVERT(DATETIME, '$ftcenddate 00:00:00', 102),"; 
                   }                                   
                   $strQuery.= " '$ftcnotes', 
                   '$strHistory')";

      //echo $strQuery;
      sqlsrv_query($db, $strQuery);        
      break;    
    case 2:
      // FWR
      if (isset($_POST['fwr'])) {
        $fwr = 1;
        $strFWRHistory = "Yes";
      }
      else {
        $fwr = 0;
        $strFWRHistory = "No";
      }
      if (isset($_POST['sfwrDate'])) {
        $fwrstartdate = $_POST['sfwrDate'];
      }
      else {
        $fwrstartdate = '';
      }
      if (isset($_POST['efwrDate'])) {
        $fwrenddate = $_POST['efwrDate'];
      }
      else {
        $fwrenddate = '';
      }

      if (isset($_POST['fwrnotes'])) {
        $fwrnotes = escapeSingleQuotes($_POST['fwrnotes']);
      }
      else {
        $fwrnotes = '';
     }
     
      $strHistory = 'Record updated by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i").'.<br>';
      $strHistory.= 'FWA was set to '.$strFWRHistory.'.<br>';
      $strHistory.= 'The Start Date was set to ';
      if ($fwrstartdate == '') {
        $strHistory.= 'Nothing.<br>';
      }
      else {
        $strHistory.= $fwrstartdate.'.<br>';
      }
      $strHistory.= 'The End Date was set to ';
      if ($fwrenddate == '') {
        $strHistory.= 'Nothing.<br>';
      }
      else {
        $strHistory.= $fwrenddate.'.<br>';
      }      
      $strHistory.='<hr>';
      $strHistory = escapeSingleQuotes($strHistory); 
     
     
     
      // Do the update
      $strQuery = "IF EXISTS     (SELECT        Login
                   FROM          staff_status
                   WHERE         (Login = '$strLogin'))
                   UPDATE        staff_status
                   SET           isFWR = $fwr,"; 
                   if ($fwrstartdate == '' ) {
                     $strQuery.= "FWRStartDate = NULL,";
                   }
                   else {
                     $strQuery.= " FWRStartDate = CONVERT(DATETIME, '$fwrstartdate 00:00:00', 102),"; 
                   }
                   if ($fwrenddate == '' ) {
                     $strQuery.= "FWREndDate = NULL,";
                   }
                   else {
                     $strQuery.= " FWREndDate = CONVERT(DATETIME, '$fwrenddate 00:00:00', 102),"; 
                   }                   

                   $strQuery.= " FWRNotes = N'$fwrnotes',
                   History = CONCAT('$strHistory', ISNULL(History,''))                   
                   WHERE        (Login = N'$strLogin')
                   ELSE                   
              
                   INSERT INTO staff_status (Login, isFWR, FWRStartDate, FWREndDate, FWRNotes, History)
                   VALUES        ('$strLogin', 
                                   $fwr,"; 
                    if ($fwrstartdate == '' ) {
                     $strQuery.= "NULL,";
                   }
                   else {
                     $strQuery.= " CONVERT(DATETIME, '$fwrstartdate 00:00:00', 102),"; 
                   }
                   if ($fwrenddate == '' ) {
                     $strQuery.= " NULL,";
                   }
                   else {
                     $strQuery.= " CONVERT(DATETIME, '$fwrenddate 00:00:00', 102),"; 
                   }                                   
                   $strQuery.= " '$fwrnotes', 
                   '$strHistory')";

      //echo $strQuery;
      sqlsrv_query($db, $strQuery);     
    break;
  }

}
else {

  $strQuery = "SELECT         Staff.Forename + N' ' + Staff.Surname AS FullName, ISNULL(staff_status.IsOnAttach, 0) AS IsOnAttach, staff_status.AttachStartDate, staff_status.AttachEndDate, 
                              ISNULL(staff_status.AttachNotes, N'') AS AttachNotes, ISNULL(staff_status.isFTC, 0) AS isFTC, staff_status.FTCStartDate, staff_status.FTCEndDate, 
                              ISNULL(staff_status.FTCNotes, N'') AS FTCNotes, ISNULL(staff_status.isFWR, 0) AS isFWR, 
                              staff_status.FWRStartDate, staff_status.FWREndDate, ISNULL(staff_status.FWRNotes, N'') AS FWRNotes
            FROM              staff_status 
            RIGHT OUTER JOIN  Staff ON staff_status.Login = Staff.Login
            WHERE             (Staff.Login = N'$strLogin') AND (Staff.DepartmentID = $intDepartmentID)";

  //echo $strQuery;
  $rsUser = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsUser);
  $fullname = $row['FullName'];

  // Attachment Info
  $isOnAttachment = $row['IsOnAttach'];

  if (is_null ($row['AttachStartDate'])) {
    $AttachStartDate = '';
    $AttachStartDatetext = '';
  }
  else {
    $AttachStartDate = $row['AttachStartDate']->format("Y-m-d");
    $AttachStartDatetext = $row['AttachStartDate']->format("j F, Y");
  }
  if (is_null ($row['AttachEndDate'])) {
    $AttachEndDate = '';
    $AttachEndDatetext = '';
  }
  else {
    $AttachEndDate =  $row['AttachEndDate']->format("Y-m-d");
    $AttachEndDatetext = $row['AttachEndDate']->format("j F, Y");
  }
  $AttachNotes = $row['AttachNotes'];

  // FTC Info
  $ftc = $row['isFTC'];
 
  if (is_null ($row['FTCStartDate'])) {
    $FTCStartDate = '';
    $FTCStartDatetext = '';
  }
  else {
    $FTCStartDate = $row['FTCStartDate']->format("Y-m-d");
    $FTCStartDatetext = $row['FTCStartDate']->format("j F, Y");
  }
  if (is_null ($row['FTCEndDate'])) {
    $FTCEndDate = '';
    $FTCEndDatetext = '';
  }
  else {
    $FTCEndDate = $row['FTCEndDate']->format("Y-m-d");
    $FTCEndDatetext = $row['FTCEndDate']->format("j F, Y");
  }

  $ftcinfo = $row['FTCNotes'];

  // FWR Info
  $fwr = $row['isFWR'];

  if (is_null ($row['FWRStartDate'])) {
    $FWRStartDate = '';
    $FWRStartDatetext = '';
  }
  else {
    $FWRStartDate = $row['FWRStartDate']->format("Y-m-d"); // date("Y-m-d", strtotime($row['FWRStartDate']));
    $FWRStartDatetext = $row['FWRStartDate']->format("j F, Y");  //date("j F, Y", strtotime($row['FWRStartDate']));
  }
  if (is_null ($row['FWREndDate'])) {
    $FWREndDate = '';
    $FWREndDatetext = '';
  }
  else {
    $FWREndDate = $row['FWREndDate']->format("Y-m-d"); //date("Y-m-d", strtotime($row['FWREndDate']));
    $FWREndDatetext = $row['FWREndDate']->format("j F, Y"); // date("j F, Y", strtotime($row['FWREndDate']));
  }
  $FWRNotes = $row['FWRNotes'];

  echo '<form name="supportstatusedit" id="supportstatusedit">';
  echo '<input type="hidden" name="login" value="'.$strLogin.'">';
  echo '<input type="hidden" name="department" value="'.$intDepartmentID.'">';
  echo '<input type="hidden" name="currenttab" value="'.$intTabOption.'">';    

//$intTabOption = $_REQUEST['currenttab'];
  
  echo '<table class="redtable" width="600px">';
  echo '<tr>';
  echo '<th colspan="4">';
  echo '<br>Editing information for '.$fullname.'<br><br>';
  echo '</th>';
  echo '</tr>';    
    
  if ($intTabOption == 0) {
    echo '<tr>';
    echo '<th width="150px" colspan="2">On Attachment</th>';
    echo '<td>';
    echo '<select size="1" name="onattachment" id="onattachment">';
    foreach ($arrattach as $key=>$value) {
      if ($key == $isOnAttachment) {
        echo '<option selected value="'.$key.'">'.$value.'</option>';
      }
      else {
        echo '<option value="'.$key.'">'.$value.'</option>';
      }
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th width="150px">Attach Start Date</th>';
    echo '<th style="text-align:right;"><input type="hidden" name="sAttachDate" id="datepicker-attachstart" value="'.$AttachStartDate.'"></th>';
    echo '<td><input type="text" id="sattachalternate" size="17" value="'.$AttachStartDatetext.'" readonly>';
    echo '&nbsp;<img onclick=\'javascript:ClearAttachStartDate()\'; border="0" src="images/delete.gif" width="17" height="17">';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th width="150px">Attach End Date</th>';
    echo '<th style="text-align:right;"><input type="hidden" name="eAttachDate" id="datepicker-attachend" value="'.$AttachEndDate.'"></th>';
    echo '<td><input type="text" id="eattachalternate" size="17" value="'.$AttachEndDatetext.'" readonly>';
    echo '&nbsp;<img onclick=\'javascript:ClearAttachEndDate()\'; border="0" src="images/delete.gif" width="17" height="17">';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th colspan="3"><br>Attachment Notes<br><br></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td class="lightcell smalltext" colspan="3">';
    echo '<textarea rows="8" name="attachmentnotes" cols="80">'.$AttachNotes.'</textarea>';
    echo '</td>';
    echo '</tr>';
  }
  if ($intTabOption == 1) {
    // Fixed Term Contract
    echo '<tr>';
    echo '<th colspan="2">Fixed Term Contract</th>';
    echo '<td><input type="checkbox" name="ftc" value="ON"';
    if ($ftc == 1) {
      echo ' checked';
    }
    echo '></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th width="150px">FTC Start Date</th>';
    echo '<th style="text-align:right;"><input type="hidden" name="sftcDate" id="datepicker-ftcstart" value="'.$FTCStartDate.'"></th>';
    echo '<td><input type="text" id="sftcalternate" size="17" value="'.$FTCStartDatetext.'" readonly>';
    echo '&nbsp;<img onclick=\'javascript:ClearFTCStartDate()\'; border="0" src="images/delete.gif" width="17" height="17">';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th width="150px">FTC End Date</td>';
    echo '<th  style="text-align:right;"><input type="hidden" name="eftcDate" id="datepicker-ftcend" value="'.$FTCEndDate.'"></td>';
    echo '<td><input type="text" id="eftcalternate" size="17" value="'.$FTCEndDatetext.'" readonly>';
    echo '&nbsp;<img onclick=\'javascript:ClearFTCEndDate()\'; border="0" src="images/delete.gif" width="17" height="17">';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th colspan="3"><br>FTC Notes<br><br></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="3">';
    echo '<textarea rows="8" name="ftcnotes" cols="80">'.$ftcinfo.'</textarea>';
    echo '</td>';
    echo '</tr>';
  // END Fixed Term Contract
  }

  if ($intTabOption == 2) {
    // Flexible Working Request
    echo '<tr>';
    echo '<th width="150px" colspan="2">Flexible Working Arrangement</th>';
    echo '<td><input type="checkbox" name="fwr" value="ON"';
    if ($fwr == 1) {
      echo ' checked';
    }
    echo '></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th width="150px">FWA Start Date</th>';
    echo '<th style="text-align:right;"><input type="hidden" name="sfwrDate" id="datepicker-start" value="'.$FWRStartDate.'"></th>';
    echo '<td><input type="text" id="salternate" size="17" value="'.$FWRStartDatetext.'" readonly>';
    echo '&nbsp;<img onclick=\'javascript:ClearStartDate()\'; border="0" src="images/delete.gif" width="17" height="17">';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th width="150px">FWA End Date</td>';
    echo '<th style="text-align:right;"><input type="hidden" name="efwrDate" id="datepicker-end" value="'.$FWREndDate.'"></td>';
    echo '<td><input type="text" id="ealternate" size="17" value="'.$FWREndDatetext.'" readonly>';
    echo '&nbsp;<img onclick=\'javascript:ClearEndDate()\'; border="0" src="images/delete.gif" width="17" height="17">';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th colspan="3"><br>FWA Notes<br><br></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="3">';
    echo '<textarea rows="8" name="fwrnotes" cols="80">'.$FWRNotes.'</textarea>';
    echo '</td>';
    echo '</tr>';
    // END Flexible Working Request
  }
  echo '<tr>';
  echo '<td colspan="3" align="center"><input type="submit" value="Update" name="update">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '</form>';
?>

<script type="text/javascript">
$('document').ready(function(){
  $('#supportstatusedit').validate({
    submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
    $.ajax({type:'POST', url: 'page-includes/support/status-edit.php', data:$('#supportstatusedit').serialize(), success: function(data) {
      $.facebox.close();
      GetSupportTabContent (<?php echo $intDepartmentID?>, <?php echo $intTabOption?>);
    }});
  }
  })
});

function ClearStartDate() {
  $('#datepicker-start').val("");
  $('#salternate').val("");
}
function ClearEndDate() {
  $('#datepicker-end').val("");
  $('#ealternate').val("");
}

function ClearAttachStartDate() {
  $('#datepicker-attachstart').val("");
  $('#sattachalternate').val("");
}
function ClearAttachEndDate() {
  $('#datepicker-attachend').val("");
  $('#eattachalternate').val("");
}

function ClearFTCStartDate() {
  $('#datepicker-ftcstart').val("");
  $('#sftcalternate').val("");
}
function ClearFTCEndDate() {
  $('#datepicker-ftcend').val("");
  $('#eftcalternate').val("");
}

  $(function() {
    $( "#datepicker-start" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#salternate",
      altFormat: "d MM, yy",
    });
  });
  $(function() {
    $( "#datepicker-end" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#ealternate",
      altFormat: "d MM, yy"
    });
  });
  $(function() {
    $( "#datepicker-attachstart" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#sattachalternate",
      altFormat: "d MM, yy"
    });
  });
  $(function() {
    $( "#datepicker-attachend" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#eattachalternate",
      altFormat: "d MM, yy"
    });
  });

  $(function() {
    $( "#datepicker-ftcstart" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#sftcalternate",
      altFormat: "d MM, yy"
    });
  });
  $(function() {
    $( "#datepicker-ftcend" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#eftcalternate",
      altFormat: "d MM, yy"
    });
  });


</script>


<?php
}
?>