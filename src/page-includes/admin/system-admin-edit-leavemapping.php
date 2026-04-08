<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
 }
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
$pdo = OpenDBLinkA7();

$intID = $_REQUEST['id'];

if (isset($_REQUEST['Update'])) {
  $strDescription = $_REQUEST['Description'];
  $intScheID  = $_REQUEST['ScheID'];
  if (isset($_REQUEST['isCreditable'])) {
    $intisCreditable  = 1;
  }
  else {
    $intisCreditable  = 0;  
  }

  if (isset($_REQUEST['ShowZero'])) {
    $intShowZero  = 1;
  }
  else {
    $intShowZero  = 0;  
  }
  
  if (isset($_REQUEST['IncludeInReports'])) {
    $intIncludeInReports  = 1;
  }
  else {
    $intIncludeInReports  = 0;  
  }
  
  if (isset($_REQUEST['CalcInReports'])) {
    $intCalcInReports  = 1;
  }
  else {
    $intCalcInReports  = 0;  
  }  

  if (isset($_REQUEST['HasCredits'])) {
    $intHasCredits  = 1;
  }
  else {
    $intHasCredits  = 0;  
  }
  
  if (isset($_REQUEST['SelectiveHide'])) {
    $intSelectiveHide  = 1;
  }
  else {
    $intSelectiveHide  = 0;  
  }

  try {
		$strQuery = "UPDATE      LeaveAllocateTypes
               SET         Description = N'$strDescription', 
                           ScheID = $intScheID, 
                           isCreditable = $intisCreditable, 
                           ShowZero = $intShowZero, 
                           IncludeInReports = $intIncludeInReports, 
                           CalcInReports = $intCalcInReports, 
                           HasCredits = $intHasCredits, 
                           SelectiveHide = $intSelectiveHide
               WHERE   (id = $intID)";
  
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
	} catch (PDOException $e) {
        logger()->critical('db error', (array)$e);
    }

}
else {
	try {
	  $strQuery = "SELECT   id, Description, DisplayAllocName, ScheID, isCreditable, ShowZero, IncludeInReports, CalcInReports, HasCredits, SelectiveHide
				   FROM      LeaveAllocateTypes
				   WHERE   (id = $intID)";

	  $stmt = $pdo->prepare($strQuery);
	  $stmt->execute();
	  $row = $stmt->fetch(PDO::FETCH_ASSOC);
	  $strDescription = $row['Description'];
	  $strAllocName  = $row['DisplayAllocName'];
	  $intScheID  = $row['ScheID'];
	  $intisCreditable  = $row['isCreditable'];
	  $intShowZero  = $row['ShowZero'];
	  $intIncludeInReports  = $row['IncludeInReports'];
	  $intCalcInReports  = $row['CalcInReports'];
	  $intHasCredits  = $row['HasCredits'];
	  $intSelectiveHide  = $row['SelectiveHide'];  
	} catch (PDOException $e) {
        logger()->critical('db error', (array)$e);
    }
  echo '<form id="neweditmapping">';
  echo '<table class="tablesmalltidy" width="600px">';
  echo '<tr>';   
  echo '<td colspan="2" class="tableheadersmall smalltextbold"><br>Editing Leave Mapping<br><br></td>';  

  echo '</tr>';  
  
  echo '<tr>';
  echo '<th width="250px">Description</th>';
  echo '<td><input type="text" name="Description" id="Description" size="30" value="'.$strDescription.'"></td>';
  echo '</tr>';
  
  echo '<tr>';
  
  echo '<th>Allocate Field Name</th>';  
  echo '<td><input type="text" name="AllocName" id="AllocName" size="30" value="'.$strAllocName.'" readonly ></td>';  
  echo '</tr>';

  echo '<th>ScheduAll Activity Type ID</th>';  
  echo '<td><input type="text" name="ScheID" id="ScheID" size="30" value="'.$intScheID.'"></td>';  
  echo '</tr>';

  echo '<th>Is Creditable (In Allocate)</th>';  
  echo '<td>';
  echo '<input name="isCreditable" id="isCreditable" value="ON" type="checkbox"';
  if ($intisCreditable == 1) {
      echo ' checked';
  }
    echo '>';  
  echo '</td>';  
  echo '</tr>';
  
  echo '<tr>';    
  echo '<th>Show Zero Values</th>';  
  echo '<td>';
  echo '<input name="ShowZero" id="ShowZero" value="ON" type="checkbox"';
  if ($intShowZero == 1) {
      echo ' checked';
  }
    echo '>';  
  echo '</td>';   
  echo '</tr>';
  
  echo '<tr>';  
  echo '<th>Include in Reports</th>';  
  echo '<td>';
  echo '<input name="IncludeInReports" id="IncludeInReports" value="ON" type="checkbox"';
  if ($intIncludeInReports == 1) {
      echo ' checked';
  }
    echo '>';  
  echo '</td>'; 
  echo '</tr>';

  echo '<tr>';  
  echo '<th>Calculate in Reports</th>';  
  echo '<td>';
  echo '<input name="CalcInReports" id="CalcInReports" value="ON" type="checkbox"';
  if ($intCalcInReports == 1) {
      echo ' checked';
  }
    echo '>';  
  echo '</td>'; 
  echo '</tr>';
  
  echo '<tr>';    
  echo '<th>Has Credits</th>';  
  echo '<td>';
  echo '<input name="HasCredits" id="HasCredits" value="ON" type="checkbox"';
  if ($intHasCredits == 1) {
      echo ' checked';
  }
    echo '>';  
  echo '</td>';  
  echo '</tr>';
  
  echo '<tr>';  
  echo '<th>Selective Hide (By Scheduling Team)</th>';  
  echo '<td>';
  echo '<input name="SelectiveHide" id="SelectiveHide" value="ON" type="checkbox"';
  if ($intSelectiveHide == 1) {
      echo ' checked';
  }
    echo '>';  
  echo '</td>';  
  echo '</tr>';
     
  echo '<tr>';
  echo '<td>&nbsp;</td>';
  echo '<td><input type="submit" value="Update" name="Update">';
  echo '&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '</form> ';
?>

<script type="text/javascript">
  $('document').ready(function(){
    $('#neweditmapping').validate({
      rules:{
        "Description":{
          required:true,
        },
        "AllocName":{
          required:true,
        },
         "ScheID":{
          required:true,
          min: 0
        }        
      },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/system-admin-edit-leavemapping.php', data:$('#neweditmapping').serialize(), success: function(data) {
            $.facebox.close();
            ShowLeaveMapping();         
          }});
        }   
    })
  });

</script>
<?php
 }
?>