<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/locksfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$strRequesterUser = null;
$sheduledPersonId = null;
$strDate = null;
$intRequestType = null;
$intRequestGroup = null;
$id = $_REQUEST['id'] ?? 0;
if (isset($_REQUEST['pageid'])) {
  $pageid = $_REQUEST['pageid'];
} else {
  $pageid = 0;
}
$pdo = OpenDBLinkA7();
$strQuery ="exec [dbo].[usp_fetch_request_by_id] :ID";
try {
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':ID', $id, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!empty($row)) {
    $strRequesterUser = $row['Login'];
    $sheduledPersonId = $row['ScheduledPersonID'];
    $strDate = date('Y-m-d',strtotime($row['dDate']));
    $intRequestType = $row['RequestType'];
    $intRequestGroup = $row['GroupID']; 
  }
} catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
}

if (isset($_REQUEST['submit'])) {
  // Do Update
  $intNewTypeID = $_REQUEST['NewTypeID'];
  $strOriginalDesc = GetRequestTypeDescriptionFromID($intRequestType);
  $strNewDesc = GetRequestTypeDescriptionFromID($intNewTypeID);
  $strHistory =  '<hr>The Request Type was changed from \''.$strOriginalDesc.'\' to \''.$strNewDesc.'\' by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
  $strHistory = escapeSingleQuotes($strHistory);
  $strQuery = "UPDATE Requests
               SET RequestType = $intNewTypeID, 
              History = CONCAT(ISNULL(History,''), '$strHistory')
              WHERE(ID = :ID)";
  try {
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':ID', $id, PDO::PARAM_INT);
    $stmt->execute();
  } catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
  }
} else {
  $intAdminLevel = GetAdminLeaveRequestGroupsAdminFromLogin($strUser, $intRequestGroup);
  $strFullName = GetFullNameFromLogin($strRequesterUser);
  $arrRequests = GetRequests($strRequesterUser,$sheduledPersonId,$strDate, $strDate);
  $formprint='';
  $formprint.= '<form id="changerequesttype">';
  $formprint.='<table class="smalltable" width="600px">';
  $formprint.='<tr>';
  $formprint.='<td colspan="2" class="tableheadersmall medtextboldcentre">';
  $formprint.='<br>Change Request Type for '.$strFullName.'<br><br>';
  if ($intAdminLevel == 2) {
    $formprint.='Items Shown in <span class="pinkeven">Pink</span> are in a different Group.<br>You may not have administrative rights for these groups.';
    $formprint.='<br><br>';
  }
  $formprint.= 'The order of the current requests in both types will be re-ordered which may mean that entries change their \'OK\' status';
  $formprint.='<br><br>';
  $formprint.='</td>';
  $formprint.='</tr>';

  $formprint.='<tr>';
  $formprint.= '<td class="tableheadersmall">Date</td>';
  $formprint.='<td class="lightcell smalltext">'.date("l, jS F Y", strtotime($strDate)).'</td>';
  $formprint.='</tr>';

  $formprint.= '<tr>';
  $formprint.='<td class="tableheadersmall" valign="top">Request Type</td>';
    $formprint.='<td class="lightcell smalltext">';
    $formprint.='<select size="1" name="NewTypeID">'; 
    if (!empty($arrRequests) && isset($arrRequests['Groups'])) {
    foreach ($arrRequests['Groups'] as $intGroupID => $arrGroupRequests) {
      if (!is_null($arrGroupRequests['Types'])) {
        foreach ($arrGroupRequests['Types'] as $intTypeID => $arrTypeRequests) {
          if ($intTypeID == $intRequestType) {
            $formprint.='<option selected value="'.$intTypeID.'">'.$arrTypeRequests['Description'].'</option>';
          } else {
            if ($intGroupID == $intRequestGroup) {
              $formprint.= '<option value="'.$intTypeID.'">'.$arrTypeRequests['Description'].'</option>';
            } else {
              if ($intAdminLevel == 2) {
                $formprint.= '<option class="pinkeven" value="'.$intTypeID.'">'.$arrTypeRequests['Description'].'</option>';        
              }
            }
          }      
        }
      }
    } 
  } 
 $formprint.='</select>';
 $formprint.='</td>';  
 $formprint.='</tr>'; 

 $formprint.='<tr>';
 $formprint.='<td></td>';
 $formprint.='<td><input id="submit" name="submit" type="submit" value="Change">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
 $formprint.='</tr>';

 $formprint.='</table>';
 $formprint.='<input type="hidden" name="id" value="'.$id.'">';
 $formprint.='<input type="hidden" name="pageid" value="'.$pageid.'">';
 $formprint.='</form>';  
 echo $formprint;
?>

<script type="text/javascript">
$('document').ready(function(){
    $('#changerequesttype').validate({
      submitHandler: function(form) {
          $.facebox.close();
        $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/requests/request-change-type.php', data:$('#changerequesttype').serialize(), success: function(data) {     
             <?php
   if ($pageid == 1) {
      echo "ShowWeeklyRequestsAdmin('".$strDate."');";
    }
    ?>         
        }});
      }
    })
  });
</script>  
<?php
}
?>