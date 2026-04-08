<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';
include_once '../users/process/classUserSetup.php';
$pdo = OpenDBLinkA7();
$intID = $_POST['id'];
$intTeamID = $_POST['teamid'];

$setupObj = new classUserSetup();
$arrStaffTeams=[];
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
if (isset($arrUsersTeamdata["Teams"])) {
  $arrStaffTeams = $arrUsersTeamdata["Teams"];
}

if (isset($_POST['Update'])) {
  $strDescription = $_POST['description'];
  $strFilter = $_POST['filter'];
  $intSchedulingTeamId = $_POST['SchedulingTeamId'];
 
  $strQuery = "exec [dbo].[usp_mod_Prodviewgroups] ?,?,?,?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $intID, PDO::PARAM_INT);
  $stmt->bindParam(2, $intSchedulingTeamId, PDO::PARAM_INT);
  $stmt->bindParam(3, $strDescription, PDO::PARAM_STR);
  $stmt->bindParam(4, $strFilter, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
else {
  if ($intID == 0) {
    $strDescription = '';
    $strFilter = '';
    $intSchedulingTeamId  = '';
  }
  else {

    $strQuery = "exec [dbo].[usp_get_Prodviewgroups] ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intID, PDO::PARAM_INT);
 
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $strDescription = $row['Description'];
    $strFilter  = $row['Filter'];
    $intSchedulingTeamId  = $row['SchedulingTeamId'];
  }

  echo '<link rel="stylesheet" href="../../styles/deptaddeditgroup.css">';
  echo '<form id="neweditgroup">';
  echo '<div class="deptgroupstable-layout">';
  
  // Header row
  echo '<div class="deptgroupstable-row deptgroupstable-row-full">';
  echo '<div class="deptgroupstable-cell deptgroupstable-header">';
  if ($intID == 0) {
    echo '<h3>New Group</h3>';
  }
  else {
    echo '<h3>Editing Group</h3>';
  }
  echo '</div>';
  echo '</div>';
  
  // Scheduling Team row
  echo '<div class="deptgroupstable-row">';
  echo '<div class="deptgroupstable-cell deptgroupstable-label"><label for="SchedulingTeamId">Scheduling Team<span class="required">*</span></label></div>';
  echo '<div class="deptgroupstable-cell deptgroupstable-value">';
  echo '<select name="SchedulingTeamId" id="SchedulingTeamId">';
  echo '<option value="">Select Scheduling Team</option>';
  if(!empty($arrStaffTeams)){
    foreach($arrStaffTeams as $teamIdKey => $teamIdVal){
      if($teamIdVal['SchedulingTeamAdmin'] == 1 ){
        if($intTeamID == $teamIdKey){
          echo '<option value="'.$teamIdKey.'" selected="selected">'.$teamIdVal['schedulingTeamName'].'</option>';
        } else {
          echo '<option value="'.$teamIdKey.'">'.$teamIdVal['schedulingTeamName'].'</option>';
        }
      }
    }
  }
  echo '</select>';
  echo '</div>';
  echo '</div>';
  
  // Description row
  echo '<div class="deptgroupstable-row">';
  echo '<div class="deptgroupstable-cell deptgroupstable-label"><label for="description">Description<span class="required">*</span></label></div>';
  echo '<div class="deptgroupstable-cell deptgroupstable-value">';
  echo '<input type="text" id="description" name="description" class="deptgroupstable-input" value="'.$strDescription.'">';
  echo '</div>';
  echo '</div>';
  
  // Duty Contains row
  echo '<div class="deptgroupstable-row">';
  echo '<div class="deptgroupstable-cell deptgroupstable-label"><label for="filter">Duty Contains<span class="required">*</span></label></div>';
  echo '<div class="deptgroupstable-cell deptgroupstable-value">';
  echo '<input type="text" id="filter" name="filter" class="deptgroupstable-input" value="'.$strFilter.'">';
  echo '</div>';
  echo '</div>';
  
  // Buttons row
  echo '<div class="deptgroupstable-row deptgroupstable-row-buttons">';
  echo '<div class="deptgroupstable-cell deptgroupstable-label"></div>';
  echo '<div class="deptgroupstable-cell deptgroupstable-value">';
  echo '<input type="submit" value="Update" name="Update" class="deptgroupstable-button">';
  echo '<input type="button" value="Cancel" onclick="cancel()" class="deptgroupstable-button">';
  echo '</div>';
  echo '</div>';
  
  echo '</div>'; // Close deptgroupstable-layout
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '<input type="hidden" name="teamid" value="'.$intTeamID.'">';
  echo '</form> ';
?>

<script type="text/javascript">
  $('document').ready(function(){
    $('#neweditgroup').validate({
      rules:{
        "description":{
          required:true,
        },
        "filter":{
          required:true,
        },
      },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/deptaddeditgroup.php', data:$('#neweditgroup').serialize(), success: function(data) {
            $.facebox.close();
            ShowProductionViewGroupFilters(<?php echo $intTeamID?>);         
          }});
        }   
    })
  });

</script>
<?php
 }
?>