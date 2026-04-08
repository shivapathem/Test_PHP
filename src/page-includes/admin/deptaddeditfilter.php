<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../users/process/classUserSetup.php';

$db = OpenDatabase();
$setupObj = new classUserSetup();
$arrStaffTeams = json_decode($setupObj->GetDefaultTeam('ALL'), true);

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$intID = $_REQUEST['id'];
$intFilterType = $_REQUEST['filtertype'];
$intDepID = $_REQUEST['deptid'];

if (isset($_REQUEST['Update'])) {
  $strDescription = $_REQUEST['description'];
  $strDutiesFilter = $_REQUEST['DutiesFilter'];
  if (isset($_REQUEST['JobsFilter'])) {
    $strJobsFilter = $_REQUEST['JobsFilter'];
  }
  else {
    $strJobsFilter = '';
  }
  if (isset($_REQUEST['JobStarts'])) {
    $strJobsStart = $_REQUEST['JobStarts'];
  }
  else {
    $strJobsStart = '';
  }
  if (isset($_REQUEST['JobContains'])) {
    $strJobsContain = $_REQUEST['JobContains'];
  }
  else {
    $strJobsContain = '';
  }  
    
  if (isset($_REQUEST['SortCodesFilter'])) {
    $strSortCodesFilter = $_REQUEST['SortCodesFilter'];
  }
  else {
    $strSortCodesFilter = '';
  }   
  $intFilterType = $_REQUEST['filtertype'];
  
  if (isset($_REQUEST['SortOrder'])) {
    $intSortOrder = $_REQUEST['SortOrder'];
  }
  else {
    $intSortOrder = 0;
  }
  if (isset($_REQUEST['ShowSortCodes'])) {
    $intShowSortCodes = 1;
  }
  else {
    $intShowSortCodes = 0;
  }
  if (isset($_REQUEST['AndMatch'])) {
    $intMatch = 1;
  }
  else {
    $intMatch = 0;
  }
  if (isset($_REQUEST['SchedulingTeamId'])) {
    $intTeamId = $_REQUEST['SchedulingTeamId'];
  }
  else {
    $intTeamId = 0;
  }
  if (isset($_REQUEST['isPublic'])) {
    $isPublic = $_REQUEST['isPublic'];
  }
  else {
    $isPublic = 0;
  }
  if (isset($_REQUEST['UserID'])) {
    $userId = $_REQUEST['UserID'];
  }
  else {
    $userId = 0;
  }
  
  if ($intID == 0) {
    $strQuery = "INSERT INTO AutoPagesFilters
                             (Description, DutyFilter, Filter, SortCodeFilter, SortCode, SortOrder, DepartmentID, FilterType, JobStarts, JobContains, AndMatch, SchedulingTeamId, isPublic,UserID)
              VALUES 
                             ('$strDescription', 
                              '$strDutiesFilter', 
                              '$strJobsFilter', 
                              '$strSortCodesFilter', 
                              $intShowSortCodes, 
                              $intSortOrder, 
                              $intTeamId, 
                              $intFilterType,
                              '$strJobsStart',
                              '$strJobsContain',
                              $intMatch,
                              $intTeamId,
                              $isPublic,
                              $userId)";
  }
  else {
      $strQuery = "UPDATE     AutoPagesFilters
                   SET        Description = '$strDescription', 
                              DutyFilter = '$strDutiesFilter', 
                              Filter = '$strJobsFilter', 
                              SortCodeFilter = '$strSortCodesFilter',
                              JobStarts =    '$strJobsStart',
                              JobContains = '$strJobsContain',
                              SortCode = $intShowSortCodes,
                              AndMatch = $intMatch,
                              SortOrder = $intSortOrder,
                              SchedulingTeamId = $intTeamId,
                              isPublic = $isPublic,
                              UserID = $userId 
                    WHERE     (id = $intID)";
  
  
  }
  //echo $strQuery;
  sqlsrv_query($db, $strQuery);
}
else {
  $arrAdminDepts = GetAdminDepts($strUser, 1);
  $arrBaseCodes = GetBaseCodes();
  if ($intID == 0) {
    $strDescription = '';
    $strDuties = '';
    $strSortCodes = '';
    $intShowSortCodes = 0;
    $intSortOrder = '';
    $strJobs = '';
    $strJobsStarts  = '';    
    $strJobsContain  = '';
    $intMatch = 0;   
  }
  else {

    $strQuery = "SELECT      Description, DutyFilter, SortCodeFilter, Filter, JobStarts, JobContains, SortCode, SortOrder,  FilterType, isnull(AndMatch, 0) as AndMatch, 
                             ISNULL(BaseCodes, '') AS BaseCodes, ISNULL(ExtraDepartments, '') AS ExtraDepartments, DepartmentID,SchedulingTeamId,isPublic,UserID
                 FROM        AutoPagesFilters
                 WHERE       (id = $intID)";

    $rsFilter = sqlsrv_query($db, $strQuery);
    $row = sqlsrv_fetch_array($rsFilter);
    $strDescription = $row['Description'];
    $strDuties  = $row['DutyFilter'];
    $strJobs  = $row['Filter']; 
    $strJobsStarts  = $row['JobStarts'];    
    $strJobsContain  = $row['JobContains'];    
    $strSortCodes = $row['SortCodeFilter'];
    $intShowSortCodes  = $row['SortCode'];
    $intSortOrder  = $row['SortOrder'];
    $intMatch  = $row['AndMatch'];
    $teamId  = $row['SchedulingTeamId'];
    $isPublic  = $row['isPublic'];
    $userID  = $row['UserID'];
    if ($row['BaseCodes'] != '') {
      $arrTheseBaseCodes = explode(',', $row['BaseCodes']);
      
      $arrTheseBaseCodes = array_flip($arrTheseBaseCodes); 
      
    }
    if ($row['ExtraDepartments'] != '') {    
      $arrExtraDepartments = explode(',', $row['ExtraDepartments']);  
      $arrExtraDepartments = array_flip($arrExtraDepartments); 
    }
  }


  echo '<form id="neweditfilter">';
  echo '<table width="700px" class="tablesmalltidy">';

  echo '<tr>';   
  if ($intID == 0) {
    echo '<td colspan="4" class="tableheadersmall smalltextbold"><br>New Filter<br><br></td>';
  }
  else {
      echo '<td colspan="4" class="tableheadersmall smalltextbold"><br>Editing Filter<br><br></td>';  
  }
  echo '</tr>';  
  
  echo '<tr>';
  echo '<th width="250px">Select Scheduling Team</th>';
  echo '<td>';
  if ($intID == 0) {
    echo '<select size="1" name="SchedulingTeamId" onchange="setTeamId(this.value);">';
    echo '<option value="">Select Scheduling Team</option>';
    if(!empty($arrStaffTeams) && count($arrStaffTeams) > 0){
      foreach($arrStaffTeams as $teamIdKey => $teamIdVal){
        echo '<option value="'.$teamIdVal['schedulingTeamId'].'">'.$teamIdVal['schedulingTeamName'].'</option>';
      }
    }
    echo '</select>';
  } else {
    echo '<select size="1" name="SchedulingTeamId">';
    echo '<option value="">Select Scheduling Team</option>';
    if(!empty($arrStaffTeams) && count($arrStaffTeams) > 0){
      foreach($arrStaffTeams as $teamIdKey => $teamIdVal){
        if($teamIdVal['schedulingTeamId'] == $teamId){
          echo '<option value="'.$teamIdVal['schedulingTeamId'].'" selected="selected">'.$teamIdVal['schedulingTeamName'].'</option>';
        } else {
          echo '<option value="'.$teamIdVal['schedulingTeamId'].'">'.$teamIdVal['schedulingTeamName'].'</option>';
        }
      }
    }
    echo '</select>';
  }
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<th width="250px">Select Filter Type</th>';
  echo '<td>';
  if ($intID == 0) {
    echo '<select size="1" name="isPublic">';
    echo '<option value="1">Public</option>';
    echo '<option value="0">Private</option>';
    echo '</select>';
  } else {
    echo '<select size="1" name="isPublic">';
    if($isPublic == 1){
      echo '<option value="1" selected="selected">Public</option>';
      echo '<option value="0">Private</option>';
    } else {
      echo '<option value="1">Public</option>';
      echo '<option value="0" selected="selected">Private</option>';
    }
    echo '</select>';
  }
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<th width="250px">Description</th>';
  echo '<td><textarea name="description" cols="50" rows="2">'.$strDescription.'</textarea></td>';
  echo '</tr>';
  
  echo '<tr>';
  
  echo '<th>Duty Name Starts or Duty Name Contains (prefix with *)<br>Seperate with semi-colon.</th>';  
  echo '<td><textarea name="DutiesFilter" cols="50" rows="2">'.$strDuties.'</textarea></td>';
  echo '</tr>';
  if ($intFilterType == 0 && $intID != 0) {
  // Start of Base Codes
  echo '<th valign="top">Base Codes</th>';  
  echo '<td>';
  echo '<table width="100%">';
  echo '<tr>';   
  echo '<th width="50%">';  
  echo 'Assigned';
  echo '</th>';
  echo '<th width="50%">';  
  echo 'Available';
  echo '</th>';  
  echo '</tr>';
    
  echo '<tr>';   
  echo '<td>';
  echo '<div class="scrolldiv250x120">';
  if (isset($arrTheseBaseCodes))  {
    foreach ($arrTheseBaseCodes as $intThisID => $intBCID) {
      echo '<span class="handcursor" onclick="javascript:RemoveBC('.$intThisID.')">'.$arrBaseCodes[$intThisID].'</span><br>';    
    }     
  }
  echo '</div>';  
  echo '</td>';
  echo '<td>';
  echo '<div class="scrolldiv250x120">';      
  if (isset($arrBaseCodes))  {
    foreach ($arrBaseCodes as $intBCid => $strBCDesc) {
      if (!isset($arrTheseBaseCodes[$intBCid])) {
        echo '<span class="handcursor" onclick="javascript:AddBC('.$intBCid.')">'.$strBCDesc.'</span><br>';    
      }
    }  
  }
  echo '</div>';  
  echo '</td>';  
  echo '</tr>';  
  echo '</table>';
  echo '</td>';
  echo '</tr>';

  // End of Base Codes

  // Start of Departments
  echo '<th valign="top">Additional Departments</th>';  
  echo '<td>';
  echo '<table width="100%">';
  echo '<tr>';   
  echo '<th width="50%">';  
  echo 'Assigned';
  echo '</th>';
  echo '<th width="50%">';  
  echo 'Available';
  echo '</th>';  
  echo '</tr>';
    
  echo '<tr>';   
  echo '<td>';
  echo '<div class="scrolldiv250x120">';
  if (isset($arrExtraDepartments))  {
    foreach ($arrExtraDepartments as $intThisID => $intDepartment) {
      echo '<span class="handcursor" onclick="javascript:RemoveDept('.$intThisID.')">'.$arrAdminDepts[$intThisID]['Description'].'</span><br>';    
    }     
  }
  echo '</div>';  
  echo '</td>';
  echo '<td>';
  echo '<div class="scrolldiv250x120">';

        
  if (isset($arrAdminDepts))  {
    foreach ($arrAdminDepts as $intDepid => $arrAdminDept) {
      if (!isset($arrExtraDepartments[$intDepid])) {
        echo '<span class="handcursor" onclick="javascript:AddDept('.$intDepid.')">'.$arrAdminDept['Description'].'</span><br>';    
      }
    }  
  }
  echo '</div>';  
  echo '</td>';  
  echo '</tr>';  
  echo '</table>';
  echo '</td>';
  echo '</tr>';

  // End of Departments
}

 
  echo '<tr>';
  echo '<th>Sort Codes Contain (Use ; to separate)</th>'; 
  echo '<td><input type="text" name="SortCodesFilter" size="50" value="'.$strSortCodes.'"></td>';
  echo '</tr>';    
  
  if ($intFilterType == 1) {
    echo '<tr>';
    echo '<th>Job Labels Filter</th>';
    echo '<td><textarea name="JobsFilter" cols="50" rows="2">'.$strJobs.'</textarea></td>';
    echo '</tr>'; 

    echo '<tr>';
    echo '<th>Job Name Starts</th>';
    echo '<td><textarea name="JobStarts" cols="50" rows="2">'.$strJobsStarts.'</textarea></td>';
    echo '</tr>'; 

    echo '<tr>';
    echo '<th>Job Names Contain</th>';
    echo '<td><textarea name="JobContains" cols="50" rows="2">'.$strJobsContain.'</textarea></td>';
    echo '</tr>'; 
  }    

  if ($intFilterType == 0) {    
    echo '<tr>';
    echo '<th valign="top">';
    echo 'Show Sort Codes';
    echo '</th>';  
    echo '<td>';    
    echo '<input name="ShowSortCodes" id="ShowSortCodes" value="ON" type="checkbox"';
    if ($intShowSortCodes == 1) {
      echo ' checked';
    }
    echo '>';  
    echo ' </td>';
    echo '</tr>';
  }

  echo '<tr>';
  echo '<th valign="top">';
  echo 'Match All (Checked)<br>Match Any (Unchecked)';
  echo '</th>';  
  echo '<td>';    
  echo '<input name="AndMatch" id="AndMatch" value="ON" type="checkbox"';
  if ($intMatch == 1) {
    echo ' checked';
  }
  echo '>';  
  echo ' </td>';
  echo '</tr>';   

  if ($intFilterType == 1) {   
    echo '<tr>';
    echo '<th valign="top">';
    echo 'Sort Order';
    echo '</th>';
    echo ' <td>';  
    echo '<select size="1" name="SortOrder">'; 
    echo '<option value="0"';
    if ($intSortOrder == 0) {
      echo ' selected';
    }
    echo '>Duty Start Time</option>';  
    echo '<option value="1"';
    if ($intSortOrder == 1) {
      echo ' selected';
    }  
    echo '>Duty Name</option>';
    echo '<option value="2"';
    if ($intSortOrder == 2) {
      echo ' selected';
    }  
    echo '>Sort Code Then Name</option>';    
    echo '</select>';
    echo '</td>';
    echo '</tr>';
  }
  else {
    echo '<tr>';
    echo '<th valign="top">';
    echo 'Sort Order';
    echo '</th>';
    echo ' <td>';  
    echo '<select size="1" name="SortOrder">'; 
    echo '<option value="0"';
    if ($intSortOrder == 0) {
      echo ' selected';
    }
    echo '>Names</option>';  
    echo '<option value="1"';
    if ($intSortOrder == 1) {
      echo ' selected';
    }  
    echo '>Sort Code</option>';
   echo '</select>';
    echo '</td>';
    echo '</tr>';  
  }
  
  
 
  echo '<tr>';
  echo '<td>&nbsp;</td>';
  echo '<td><input type="submit" value="Update" name="Update">';
  echo '&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="filtertype" value="'.$intFilterType.'">';
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '<input type="hidden" name="UserID" value="'.$userId.'">';
  echo '</form> ';
?>

<script type="text/javascript">
  $('document').ready(function(){
    $('#neweditfilter').validate({
      rules:{
        "description":{
          required:true,
        },
        "SchedulingTeamId":{
          required:true,
        },
      },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/deptaddeditfilter.php', data:$('#neweditfilter').serialize(), success: function(data) {
            $.facebox.close();
<?php            
if ($intFilterType == 0) {           
  echo 'ShowFilters('.$intDepID.');';
}
else {
  echo 'ShowFilters('.$intDepID.');';
}            
            
            
?>            
          }});
        }   
    })
  });

function RemoveBC(bcid) {
  $.post("page-includes/admin/dep-filter-addremove-bc.php", {
     bcid: bcid,
     filterid: <?php echo $intID?>,
     action: 1
  },
  function(data,status){
    ShowSimpleFilters(<?php echo $intDepID?>)
    AddEditFilter(<?php echo $intID?>, <?php echo $intFilterType?>, <?php echo $intDepID?>)
  }
  )
}

function AddBC(bcid) {
  $.post("page-includes/admin/dep-filter-addremove-bc.php", {
     bcid: bcid,
     filterid: <?php echo $intID?>,
     action: 0
  },
  function(data,status){
    ShowSimpleFilters(<?php echo $intDepID?>)
    AddEditFilter(<?php echo $intID?>, <?php echo $intFilterType?>, <?php echo $intDepID?>)
  }
  )
}

function AddDept(deptid) {
  $.post("page-includes/admin/dep-filter-addremove-dept.php", {
     deptid: deptid,
     filterid: <?php echo $intID?>,
     action: 0
  },
  function(data,status){
    ShowSimpleFilters(<?php echo $intDepID?>)
    AddEditFilter(<?php echo $intID?>, <?php echo $intFilterType?>, <?php echo $intDepID?>)
  }
  )
}
function RemoveDept(deptid) {
  $.post("page-includes/admin/dep-filter-addremove-dept.php", {
     deptid: deptid,
     filterid: <?php echo $intID?>,
     action: 1
  },
  function(data,status){
    ShowSimpleFilters(<?php echo $intDepID?>)
    AddEditFilter(<?php echo $intID?>, <?php echo $intFilterType?>, <?php echo $intDepID?>)
  }
  )
}
</script>
<?php
 }
?>