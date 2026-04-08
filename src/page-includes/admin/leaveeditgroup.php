<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once 'divisions/process/classDivisionalAdmin.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
$pdo = OpenDBLinkA7();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$systemAdmin = isset($_SESSION['user']['SysAdmin']) ? $_SESSION['user']['SysAdmin'] :'0';
$intLeaveGroupID = $_REQUEST['id'];
$commonObj = new classCommonDBFunctions();
$divisionobj = new ClassDivisionalAdmin;
$isDivisionalAdmin = $commonObj->UserIsDivAdmin($sessUserId);
$divisionListsIds = $divisionobj->getDivisionsListIdByNetUserRole($strUser,$isDivisionalAdmin,$systemAdmin);
$divisionLists = json_decode($divisionobj->getDivisionsList(), true);
if (isset($_REQUEST['submit'])) {

  $description = $_POST['Description'];
  $email = escapeSingleQuotes($_POST['email']);
  $strEmailCopies = escapeSingleQuotes($_POST['emailcopies']);
  $hoursperleaveday = $_POST['hoursperleaveday'];
  $extraleaveclicks =  $_POST['extraleaveclicks'];
  $intRequestsAllowedYearly = $_POST['RequestsAllowedYearly'];
  $divisionID = $_POST['area'];
  if (isset($_POST['summerleaveoverlimit'])) {
    $summerleaveoverlimit = 1;
  }
  else {
    $summerleaveoverlimit = 0;
  }
  if (isset($_POST['ShowLeaveOverLimit'])) {
    $intShowLeaveOverLimit = 1;
  }
  else {
    $intShowLeaveOverLimit = 0;
  }  
  if (isset($_POST['partdayleaverequests'])) {
    $partdayleaverequests = 1;
  }
  else {
    $partdayleaverequests = 0;
  }
  if (isset($_POST['AllowEmails'])) {
    $intAllowEmails = 1;
  }
  else {
    $intAllowEmails = 0;
  } 
  $arrRequestsAllowed = $_POST['RequestsAllowed'];
  $strRequestsAllowed = implode(',', $arrRequestsAllowed);
  if ($intLeaveGroupID == 0) {
   $query = "INSERT INTO LeaveRequestGroups
                         (Description,
                          email,
                          emailcopiesto, 
                          HoursPerLeaveDay, 
                          ExtraLeaveClicks, 
                          SummerLeaveOverLimit,
                          ShowLeaveOverLimit, 
                          RequestsAllowedMonthly,
                          RequestsAllowedYearly,
						  IsPartDayLeaveAllowed,
						  DivisionID,
						  AllowEmails)
             VALUES      (N'$description',
                          N'$email',
                          N'$strEmailCopies', 
                          $hoursperleaveday, 
                          $extraleaveclicks, 
                          $summerleaveoverlimit,
                          $intShowLeaveOverLimit, 
                          N'$strRequestsAllowed',
                          $intRequestsAllowedYearly,
						  $partdayleaverequests,
						  $divisionID,
						  $intAllowEmails)";

  }
  else {
    $query = "UPDATE  LeaveRequestGroups
              SET     Description = '$description',
                      email = '$email',
                      emailcopiesto = '$strEmailCopies', 
                      HoursPerLeaveDay = $hoursperleaveday, 
                      ExtraLeaveClicks = $extraleaveclicks,
                      ShowLeaveOverLimit = $intShowLeaveOverLimit, 
                      SummerLeaveOverLimit = $summerleaveoverlimit,  
                      RequestsAllowedMonthly = '$strRequestsAllowed',
                      RequestsAllowedYearly = $intRequestsAllowedYearly,
					  IsPartDayLeaveAllowed = $partdayleaverequests,
					  DivisionID = $divisionID,
					  AllowEmails = $intAllowEmails
              WHERE   (id = $intLeaveGroupID)";
  }
  
  $stmt = $pdo->prepare($query);
  $stmt->execute();
}
else {
  if ($intLeaveGroupID != 0)  {
    $query = "SELECT    Description, HoursPerLeaveDay, ExtraLeaveClicks, SummerLeaveOverLimit, 
                        ShowLeaveOverLimit, RequestsAllowedMonthly, RequestsAllowedYearly, email, emailcopiesto, IsPartDayLeaveAllowed, DivisionID,AllowEmails
              FROM      LeaveRequestGroups
              WHERE     (id = $intLeaveGroupID)";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $arrRequestsAllowed = explode(",", $row['RequestsAllowedMonthly']);    
    $intRequestsAllowedYearly = $row['RequestsAllowedYearly'];    
    $strDesc = $row['Description'];
    $strEmail = $row['email'];
    $strEmailCopies = $row['emailcopiesto']; 
    $intHoursPerLeaveDay = $row['HoursPerLeaveDay'];
    $intSummerLeaveOverLimit = $row['SummerLeaveOverLimit'];
    $intPartDayLeaveRequests = $row['IsPartDayLeaveAllowed'];
    $intShowLeaveOverLimit = $row['ShowLeaveOverLimit'];
    $intExtraLeaveClicks = $row['ExtraLeaveClicks'];
	$currentDivisionID = $row['DivisionID'];
	$intAllowEmails = $row['AllowEmails'];
  }
  else{
    $strDesc = '';
    $strEmail = '';
    $strEmailCopies = '';
    $intHoursPerLeaveDay = '';
    $intSummerLeaveOverLimit = 0;
	$intPartDayLeaveRequests = 0;
    $intShowLeaveOverLimit = 0;
    $intExtraLeaveClicks = '';
    $arrRequestsAllowed[0] = 0;
    $intRequestsAllowedYearly = -1;
    $currentDivisionID= '';
	$intAllowEmails= '';
  }

  echo '<form action="page-includes/admin/leaveeditgroup.php" method="POST" id="leaveeditgroupform">';
  echo '<input type="hidden" name="id" value="'.$intLeaveGroupID.'">';
  if($isDivisionalAdmin == 0 && $systemAdmin == 0){
    echo '<input type="hidden" name="area" value="'.$currentDivisionID.'">';
  }
  echo '<table class="redtable" width="100%">';

  echo '<tr>';
  echo '<td colspan="2" class="tableheadersmall smalltextbold"><br>Editing Leave and Request Group<br><br></td>';
  echo '</tr>';
  echo '<tr>';
  echo '</table>';

  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>'; 

  echo '<table class="redtable" width="100%">';
  
  echo '<tr>';
  echo '<td class="lightcell smalltext" valign="top"><label for="area">Area</label></td>';
  echo '<td class="lightcell smalltext">';
  echo '<select name="area" class="smalltext" id="area" style="height:20px; width:298px"';
  if ($isDivisionalAdmin == 0 && $systemAdmin == 0) {
    echo ' disabled';
  }
  echo '>'; 
  $filteredDivisionLists = array_filter($divisionLists, function ($division) use ($divisionListsIds) {
    return in_array($division['DivisionID'], array_column($divisionListsIds, 'DivisionID'));
  });
  echo '<option value="">Select Area</option>';
  if (empty($filteredDivisionLists)) {
    foreach ($divisionLists as $division) {
      if ($division['DivisionID'] == $currentDivisionID) {
        $selected = ' selected="selected"';
        echo '<option value="'.$division['DivisionID'].'"'.$selected.'>'.$division['DivisionName'].'</option>';
        break;
      }
    }
  } else {
    foreach ($filteredDivisionLists as $division) {
      $selected = ($division['DivisionID'] == $currentDivisionID) ? ' selected="selected"' : '';
      echo '<option value="'.$division['DivisionID'].'"'.$selected.'>'.$division['DivisionName'].'</option>';
    }
  }
  echo '</select>';
  echo '</td>';
  echo ' </tr>';

  echo '<tr>';
  echo '<td class="lightcell smalltext" valign="top">Description</td>';
  echo '<td class="lightcell smalltext">';
  echo '<input class="smalltext" name="Description" id="Description" size="50" value="'.$strDesc.'" type="text">';
  echo '</td>';
  echo ' </tr>';

  echo '<tr>';
  echo '<td class="lightcell smalltext" valign="top">Send eMail From</td>';
  echo '<td class="lightcell smalltext">';
  echo '<input class="smalltext" name="email" id="email" size="50" value="'.$strEmail.'" type="text">';
  echo '</td>';
  echo ' </tr>';

  echo '<tr>';
  echo '<td class="lightcell smalltext" valign="top">Send eMail Copies To<br>(Separate multiple entries with commas)</td>';
  echo '<td class="lightcell smalltext">';
  echo '<input class="smalltext" name="emailcopies" id="emailcopies" size="50" value="'.$strEmailCopies.'" type="text">';
  echo '</td>';
  echo '</tr>';  
  echo '</table>';

  echo '<table class="tablesmall" width="100%">';    
  echo '<tr>';
  echo '<td colspan="6" class="tableheadersmall smalltextbold">Leave</td>';
  echo '</tr>';
  echo '<tr>';
  echo ' <td width="15%" class="lightcell smalltext" valign="top">';
  echo 'Collate Leave<br>Requests<br>';
  echo '<input name="AllowEmails" id="AllowEmails" value="ON" type="checkbox"';
  if ($intAllowEmails == 1) {
      echo ' checked';
  }
    echo '>';  
  echo ' </td>'; 
  echo ' <td width="20%" class="lightcell smalltext" valign="top">';
  echo 'Allow Leave requests over limit<br>';
  echo '<input name="ShowLeaveOverLimit" id="ShowLeaveOverLimit" value="ON" type="checkbox"';
  if ($intShowLeaveOverLimit == 1) {
      echo ' checked';
  }
    echo '>';  
  echo ' </td>';
  
  echo ' <td width="20%" class="lightcell smalltext" valign="top">';
  echo 'Allow Summer Leave requests over limit<br>';
  echo '<input name="summerleaveoverlimit" id="summerleaveoverlimit" value="ON" type="checkbox"';
  if ($intSummerLeaveOverLimit == 1) {
      echo ' checked';
  }
    echo '>';
  echo ' </td>';
  
  echo ' <td width="20%" class="lightcell smalltext" valign="top">';
  echo 'Allow Part Days of Leave Requests<br>';
  echo '<input name="partdayleaverequests" id="partdayleaverequests" value="ON" type="checkbox"';
  if ($intPartDayLeaveRequests == 1) {
      echo ' checked';
  }
    echo '>';
  echo ' </td>';
   
  echo ' <td width="15%" class="lightcell smalltext" valign="top">';
  echo 'Hours per Leave day<br>';
  echo ' <input class="smalltext" name="hoursperleaveday" id="hoursperleaveday" size="5" value="'.$intHoursPerLeaveDay.'" type="text">';
  echo ' </td>';
  
  echo ' <td width="10%" class="lightcell smalltext" valign="top">';
  echo 'Extra Leave Clicks<br>';
  echo ' <input class="smalltext" name="extraleaveclicks" id="extraleaveclicks" size="5" value="'.$intExtraLeaveClicks.'" type="text">';
  echo ' </td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="6" class="tableheadersmall smalltextbold">Request Amounts</td>';
  echo '</tr>'; 
  echo '<tr>';
  echo '<td  class="lightcell smalltext">Request Yearly Limit<br>(set to -1 to disable)</td>';
  echo '<td colspan="5" class="lightcell smalltext">';
  echo ' <input class="smalltext" name="RequestsAllowedYearly" id="RequestsAllowedYearly" size="5" value="'.$intRequestsAllowedYearly.'" type="text">';
  echo ' </td>';  
  echo '</tr>'; 

  echo '<tr>';
  echo '<td colspan="6" class="lightcell smalltext">';
  
  echo '<table width="100%">';
  echo '<tr>';
  echo '<td>Current Month<br>';
  if (is_numeric($arrRequestsAllowed[0])) {
    $intRequestsAllowed  = $arrRequestsAllowed[0];
  }
  else {
    $intRequestsAllowed = 0;
  }
  echo '<input class="smalltext" name="RequestsAllowed[0]" id="RequestsAllowed" size="4" value="'.$intRequestsAllowed.'" type="text" required>';
  echo '</td>';

  for ($iMonth = 1; $iMonth <= 12; $iMonth++) {
    if (isset($arrRequestsAllowed[$iMonth])) {
      $intRequestsAllowed  = $arrRequestsAllowed[$iMonth];
    }
    else {
      $intRequestsAllowed = 0;
    }

    echo '<td>+'.$iMonth.' Months<br>';
    echo '<input class="smalltext" name="RequestsAllowed['.$iMonth.']" id="RequestsAllowed" size="4" value="'.$intRequestsAllowed.'" type="text" required>';
    echo '</td>';
  }
  echo '</tr>';
  echo '</table>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="lightcell"></td>';
  echo '<td class="lightcell lightcellalign" colspan="6"><input value="Update" name="submit" type="submit">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  
  echo '</table>';
  echo '</form>';


?>

<script language="JavaScript" type="text/javascript">
$('#leaveeditgroupform').validate({
  errorLabelContainer: "#errorBox",
  rules: {
    area: {
      required: true
    },
    Description: {
      required: true,
      maxlength: 50
    },
    email: {
      required: true,
      email: true
    },   
    extraleaveclicks: {
      required: true,
      number: true
    },
    RequestsAllowedYearly: {
      required: true,
      number: true
    },    
    hoursperleaveday: {
      required: true,
      number: true
    },
 
  },
    messages: {
      area: "Please select an Area<br>",
      "Description": {
                    required: "Please Enter a Description<br>",
                    maxlength:'Description cannot be more than {50} characters<br>',
                },
      email: "Please Enter an eMail Address<br>",
      extraleaveclicks: "Please Enter the number of Extra Leave Clicks<br>",
      hoursperleaveday: "Please Enter the default number of Hours<br>",
      RequestsAllowedYearly: "Please Enter Yearly amount of requests allowed.<br>"
    },
    highlight: function(element, errorClass, validClass) {
      if (element.type === 'select-one') {
        $(element).closest('.form-group').addClass('has-error');
      } else {
        $(element).addClass(errorClass).removeClass(validClass);
      }
    },
    unhighlight: function(element, errorClass, validClass) {
      if (element.type === 'select-one') {
        $(element).closest('.form-group').removeClass('has-error');
      } else {
        $(element).removeClass(errorClass).addClass(validClass);
      }
    },
    errorPlacement: function(error, element) {
      if (element.is("select")) {
        error.insertAfter(element.closest('.form-group'));
      } else {
        error.insertAfter(element);
      }
    },
    submitHandler: function(form){
      $(form).ajaxSubmit({
        success: function(data) {
          $.facebox.close();
          ShowConfigLeave(<?php echo $intLeaveGroupID?>);
        }
      });

    }
});
</script>

<?php
}
?>
