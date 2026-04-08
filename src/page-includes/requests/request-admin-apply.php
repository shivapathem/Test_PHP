<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/requestfunctions.php';
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$pdo = OpenDBLinkA7();
$strDate  = '';
$strLogin = '';
$strFullName = '';
$formprint='';
if (isset($_POST['submit'])) {
    $strDate = $_POST['requestdate'];
    $strLogin = $_POST['user'];
    $intScheduledPersonID = getScheduledPersonIDByNetLoginID($strLogin);
    $intRequestType = $_POST['requesttype'];
    if (isset($_POST['asapproved'])) {
      $intAsApproved = 1;
    } else {
      $intAsApproved = 0;
    }
    $strOfficeComments = $_POST['officecomments'];
    $formprint.='<div id="requestpage">';
    $intHasAffected = 0;
    $intSameRequest = 0;
    $strQuery = "SELECT Requests.ID, Requests.dDate, Requests.RequestType, Requests.Login, RequestTypes.description, 
    RequestTypes.UniqueCount, RequestTypes.Starts, RequestTypes.Ends, RequestTypes.AffectLocks, RequestTypes.AffectsOthers AS AffectsOthers
    FROM  Requests (NOLOCK)
    INNER JOIN RequestTypes (NOLOCK) ON Requests.RequestType = RequestTypes.ID
    WHERE (Requests.dDate = ?) 
    AND (Requests.Login = ?) 
    AND (Requests.Deleted = 0)";
    try {
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $strDate, PDO::PARAM_INT);
      $stmt->bindParam(2, $strLogin, PDO::PARAM_STR);
      $stmt->execute();
      $rsRequestsIn = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }

    if (!empty($rsRequestsIn) && isset($rsRequestsIn)) {
      foreach ($rsRequestsIn as $row) {
        //There can be more than one.....
        $arrRequestsIn[$row['ID']]['RequestType'] = $row['RequestType'];
        $arrRequestsIn[$row['ID']]['Description'] = $row['description'];
        $arrRequestsIn[$row['ID']]['AffectsOthers'] = $row['AffectsOthers'];
        if ($row['AffectsOthers'] == 1) {
          $intHasAffected = 1;
        }
        if ($row['RequestType'] == $intRequestType) {
          $intSameRequest = 1;
        }
      } 
    }
  // Get this Request type....
    $strQuery = "SELECT RequestTypes.startdate, RequestTypes.enddate, RequestTypes.description AS TypeDescription, RequestTypes.UniqueCount, RequestTypes.Starts, 
    RequestTypes.Ends, RequestTypes.AffectLocks, RequestTypes.ID, RequestTypes.AffectsOthers,
    LeaveRequestGroups.Description AS GroupDescription
    FROM RequestTypes (NOLOCK)
    INNER JOIN  LeaveRequestGroups (NOLOCK) ON RequestTypes.GroupID = LeaveRequestGroups.ID
    WHERE (RequestTypes.ID = ?)";
    try {
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $intRequestType, PDO::PARAM_INT);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }
    if (!empty($row)) {
      $intRequestAffects = $row['AffectsOthers'];
      $strTypeDescription = $row['TypeDescription'];
      $strGroupDescription = $row['GroupDescription'];
    }
    if ($intSameRequest == 1 || ($intHasAffected == 1 && $intRequestAffects == 1)) {
      $formprint.= '<table class="tablesmallgrey" width="600px">';
      $formprint.= '<tr height="60px">';    
      $formprint.= '<th>';       
      $formprint.= 'This New Request cannot be added<br>';
      $formprint.= '</th>';
      $formprint.= '</tr>';
      $formprint.= '<tr height="60px">';    
      $formprint.= '<td>';       
      if ($intHasAffected == 1 && $intRequestAffects == 1) {
        $formprint.= 'There is already a request submitted on this day<br>You should use the \'Change Type\' option to modify this Request';
      }
      if ($intSameRequest == 1) {
        $formprint.= 'You are attempting to add the same Request Type twice!';
      }
      $formprint.= '</td>';
      $formprint.= '</tr>';
      $formprint.= '<tr>';    
      $formprint.= '<td>'; 
      $formprint.= '<input type="button" value="Cancel" onclick="cancel()">';
      $formprint.= '</td>';
      $formprint.= '</tr>';    
      $formprint.= '</table>';  
      echo  $formprint;    
    } else {      
    $strHistory = 'A New Admin Request \''.$strTypeDescription.'\' in \''.$strGroupDescription.'\' ';
    $strHistory.= ' was added by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
    if ($intAsApproved == 1) {
      $strHistory.= '<br>The Request was created as Approved';
    }
    $strHistory = escapeSingleQuotes($strHistory);
    if ($strOfficeComments == '') {
      $strQuery = "INSERT INTO
                   dbo.Requests(
                   dDate, 
                   RequestType,
                   isOK,
                   Approved, 
                   Login, 
                   Created, 
                   History,
                   ScheduledPersonID
                   )
                   VALUES (?,?,?,?,?,?,?,?)";    
    } else {
      $strQuery = "INSERT INTO
                   dbo.Requests(
                   dDate, 
                   RequestType,
                   isOK,
                   Approved, 
                   Login, 
                   Created, 
                   History,
                   ScheduledPersonID,
                   Comments
                   )
                   VALUES (?,?,?,?,?,?,?,?,?)";
      }
 
  try {
      $curDate=date("Y-m-d H:i:s");
      $isOK = 1;
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $strDate, PDO::PARAM_STR);
      $stmt->bindParam(2, $intRequestType, PDO::PARAM_STR);
      $stmt->bindParam(3, $isOK, PDO::PARAM_INT);
      $stmt->bindParam(4, $intAsApproved, PDO::PARAM_INT);
      $stmt->bindParam(5, $strLogin, PDO::PARAM_STR);
      $stmt->bindParam(6, $curDate, PDO::PARAM_STR);
      $stmt->bindParam(7, $strHistory, PDO::PARAM_STR);
      $stmt->bindParam(8, $intScheduledPersonID, PDO::PARAM_INT);
    if (!empty($strOfficeComments)) {
      $stmt->bindParam(9, $strOfficeComments, PDO::PARAM_STR);
    }   
    $stmt->execute();

    $query = "exec usp_UPDAllocationLockAndRequest @ScheduledPersonID = $intScheduledPersonID, @WeekNumber = NULL, @iDay = NULL, @DutyDate = '".$strDate."', @UserID = $UserID";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
?>
<script type="text/javascript">
  $(function(){
    $.facebox.close();
    ShowRequestWeeklyAdminByDate ('<?php echo $strDate?>');
  })
</script>
<?php 
  } 
} else {
  $formprint='';
  $intID = $_POST['id'] ?? 0;
  $strQuery ="exec [dbo].[usp_fetch_request_by_id] :intID";
  try {
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intID', $intID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
  if (!empty($row)) {
    $strLogin =  $row['Login'];
    $strDate = date("Y-m-d",strtotime($row['dDate'])); 
    $strFullName = $row['FullName'];
  }

  $intWeekNumber = bbcweeknumber($strDate);
  $dteStartDate = date("Y-m-d", strtotime("-1 Day", strtotime(datefromweek($intWeekNumber))));
  $dteEndDate = date("Y-m-d", strtotime("+1 Day", strtotime(datefromweek($intWeekNumber, 7))));  
  // Get the requsets for this user alreadt in
  $strQuery = "SELECT Requests.dDate, Requests.RequestType, RequestTypes.description AS TypeDescription, LeaveRequestGroups.Description AS GroupDescription, 
  RequestTypes.UniqueCount
  FROM   Requests (NOLOCK)
  INNER JOIN  RequestTypes (NOLOCK) ON Requests.RequestType = RequestTypes.ID 
  INNER JOIN  LeaveRequestGroups (NOLOCK) ON RequestTypes.GroupID = LeaveRequestGroups.ID
  WHERE (Requests.Login = N'$strLogin') 
  AND  (Requests.dDate >= CONVERT(DATETIME, '$dteStartDate 00:00:00', 102)) 
  AND  (Requests.dDate <= CONVERT(DATETIME, '$dteEndDate 00:00:00', 102)) 
  AND  (Requests.Deleted = 0) ORDER BY Requests.dDate";
  try {
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $rsRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
}
  if (!empty($rsRequests) && isset($rsRequests)) {
    foreach ($rsRequests as $row) {
      $arrRequestsIn[date("Y-m-d",strtotime($row['dDate']))]['TypeDescription'] = $row['TypeDescription'];
      $arrRequestsIn[date("Y-m-d",strtotime($row['dDate']))]['GroupDescription'] = $row['GroupDescription'];
    }
  }
  $arrRequestTypes = GetRequestTypesFromUser($strLogin, $dteStartDate, $dteEndDate);
  $formprint.= '<div id="requestpage">';
  $formprint.= '<form id="adminrequest">';
  $formprint.= '<table class="tablesmallgrey" width="600px">';
  $formprint.= '<tr height="60px">';    
  $formprint.= '<th colspan="3">';       
  $formprint.= 'You are adding a New Request for '.$strFullName.'<br>';
  $formprint.= 'You may add a request for another day in this week'; 
  $formprint.= '</th>';
  $formprint.= '</tr>';  
  $formprint.= '<tr>'; 
  $formprint.= '<td valign="top">'; 
  $formprint.= 'Current Requests';      
  $formprint.= '</td>';
  $formprint.= '<td>'; 
    if (isset($arrRequestsIn) && !empty($arrRequestsIn)) {
      foreach ($arrRequestsIn as $strRequestDate => $arrRequestIn) {
        $formprint.= date("jS F Y", strtotime($strRequestDate)).'<br>';
      }
    }
    $formprint.= '</td>';  
    $formprint.='<td>';
    if (isset($arrRequestsIn) && !empty($arrRequestsIn)) { 
      foreach ($arrRequestsIn as $strRequestDate => $arrRequestIn) {
        $formprint.= $arrRequestIn['TypeDescription'];
        $formprint.= ' ('.$arrRequestIn['GroupDescription'].')<br>';      
      }
    }
    $formprint.='</td>';  
    $formprint.='</tr>'; 
    
    $formprint.='<tr>'; 
    $formprint.='<td valign="top">'; 
    $formprint.= 'Date';      
    $formprint.='</td>';
    $formprint.= '<td colspan="2">';   
    $formprint.='<select class="chosen-select" name="requestdate">';
    $dteLoopDate  = $dteStartDate;
    while (strtotime($dteLoopDate) < strtotime($dteEndDate)) {
      $formprint.='<option value="'.$dteLoopDate.'">'.date("jS F Y", strtotime($dteLoopDate)).'</option>';
  	  $dteLoopDate = date ("Y-m-d", strtotime("+1 day", strtotime($dteLoopDate)));
    }
    $formprint.='</select>';
    $formprint.='</td>';  
    $formprint.='</tr>';
    
    $formprint.= '<tr>'; 
    $formprint.= '<td valign="top">'; 
    $formprint.= 'Request Type';     
    $formprint.= '</td>';
      $formprint.= '<td colspan="2">';  
      $formprint.='<select class="chosen-select" name="requesttype">';
      if (isset($arrRequestTypes) && !empty($arrRequestTypes) && is_array($arrRequestTypes)) {
        foreach ($arrRequestTypes as $intGroupID => $arrRequestGroup) {
          $strRequestGroupDescription = $arrRequestGroup['Description'];
          foreach ($arrRequestGroup['Types'] as $intTypeID => $arrRequestType) {
            $formprint.= '<option value="'.$intTypeID.'">'.$arrRequestType['Description'].' ('.$strRequestGroupDescription.')'.'</option>';
          }
        }
      }
    $formprint.= '</select>';
    $formprint.='</td>';  
    $formprint.= '</tr>'; 
    $formprint.= '<tr>'; 
    $formprint.= '<td valign="top">'; 
    $formprint.='Create as Approved';      
    $formprint.='</td>';
    $formprint.='<td colspan="2">';  
    $formprint.='<input checked type="checkbox" name="asapproved" value="ON">';
    $formprint.= '</td>';
    $formprint.= '</tr>';     
    $formprint.= '<tr>'; 
    $formprint.='<td valign="top">'; 
    $formprint.= 'Comments';      
    $formprint.='</td>';
    $formprint.= '<td colspan="2">';  
    $formprint.='<textarea rows="2" name="officecomments" cols="40" class="searchbox"></textarea>';
    $formprint.='</td>';
    $formprint.='</tr>';           
    $formprint.='<tr>';  
    $formprint.='<td>'; 
    $formprint.='</td>'; 
    $formprint.= '<td colspan="2">';    
    $formprint.='<input name="submit" type="submit" value="Add"></input><input type="button" value="Cancel" onclick="cancel()">';
    $formprint.='</td>';  
    $formprint.= '</tr>';   
    $formprint.= '</table>';    
    $formprint.='<input type="hidden" name="user" value="'.$strLogin.'">';
    $formprint.= '</form>';    
    $formprint.='</div>';
    echo $formprint;
?>
<script language="JavaScript" type="text/javascript">
$('document').ready(function(){
    $('#adminrequest').validate({
        submitHandler: function(form) {
          $.ajax({type:'POST', url: 'page-includes/requests/request-admin-apply.php', data:$('#adminrequest').serialize(), success: function(data) {
            $('#requestpage').html(data);
          }});
        }
    })   
});
function cancel() {
    $.facebox.close();
}  
</script>
<?php
}
?>