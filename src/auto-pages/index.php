<?php
session_start();
include_once '../function-includes/init.php';
include_once '../function-includes/genericfunctions.php';
if(!isset($_SESSION['http_auth'])) {
  if(isset($_SERVER['HTTP_AUTHORIZATION'])  && strlen($_SERVER['HTTP_AUTHORIZATION']) > 25){
    $user = authUser();
    $_SESSION['http_auth'] = $_SERVER['HTTP_AUTHORIZATION'];
  }
  else {
    header("HTTP/1.1 401 Unauthorized");
    exit();
  }
}
if (isset($_REQUEST["user"])) {
  $strUser = $_REQUEST["user"];
}
else {
  $strUser = authUser();
}

$_SESSION['user']['user'] = $strUser;
  if (isset($_REQUEST['filter'])) {
    $intPassedFilterID = $_REQUEST['filter'];
  }
  else {
    $intPassedFilterID = 0;
  } 
  if (isset($_REQUEST['team'])) {
    $intPassedTeamID = $_REQUEST['team'];
  }
  else {
    $intPassedTeamID = 0;
  }  

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=8" />
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='expires' content='0'>
<meta http-equiv='pragma' content='no-cache'>
<link rel="stylesheet" media="all" type="text/css" href="../styles/menu.css" />
<link rel="stylesheet" type="text/css" href="../styles/default.css">
<link href="../styles/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="../styles/jquery-ui.theme.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../styles/timeTo.css">
<script type="text/javascript" src="../js/jquery-3.5.1.min.js"></script>
<script src ="../js/jquery-migrate-3.0.0.min.js"></script>
<!--<script src="http://code.jquery.com/jquery-1.12.4.js"></script>-->
<!--<script src="http://code.jquery.com/jquery-3.0.0.js"></script>-->
<!--<script src ="https://code.jquery.com/jquery-migrate-3.3.2.min.js"></script>-->
<!--<script type="text/javascript" src="../js/jquery-1.7.2.min.js"></script>-->
<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/jquery.timeTo.js"></script>
<title>Allocations</title>
<style type="text/css">
.filter-hide-row {
    display: none;
}
</style>
</head>
<body>
<?php
if (!isset($_SESSION['width'])) {
?>

<script language="JavaScript">
$(document).ready(function(){
  var scwidth=screen.width;
  var scheight=screen.height;
  $.post("setscreenres.php", {
    width: scwidth,
    height: scheight
  },
  function(data,status){
    location.reload();
   }
  )
})
</script>
<?php  
  
} 
else {
  if ($intPassedFilterID == 0) {
    echo '<div style="width:60%; margin:0 auto; position:relative;">';
    echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
    echo '<br>Welcome to the Staff Allocations Auto-Page views.<br>Below is a list of Scheduling Teams and Views You have access to.<br><br>';
    echo '</div><br>';
    $arrFilters = GetTeamsFiltersByLogin($strUser);

    echo '<table class="tablesmalltidy" width="100%">';
    if (isset($arrFilters)) {
      foreach ($arrFilters as $intTeamID => $arrFilter) {
        echo '<tr>'; 
        echo '<th height="30px" colspan="2">';  
        echo $arrFilter['TeamName']; 
        echo '</th>';
        echo '</tr>';
        foreach ($arrFilter['Filters'] as $intFilterID => $strFilterDesc) {
          echo '<tr>';
          echo '<td width="200px">'; 
          echo '</td>';   
          echo '<td>';  
          echo '<a href="index.php?filter='.$intFilterID.'&team='.$intTeamID.'">'.$strFilterDesc.'</a>'; 
          echo '</td>';
          echo '</tr>';    
        }
      }
    }
    echo '<table>';
    echo '</div>';
    echo '</div>';
  }
  else {
    $dutyheight = 35;
    $rowheight = 40;
    $screenwidth =  $_SESSION['width'];
    $screenheight =  $_SESSION['height'];
    $date = date("Y-m-d");
    echo '<div id="loading">
    <img border="0" src="../images/loading.gif" width="75px" height="75px">
    </div>';
    echo '<div style="background-color:transparent; z-index:9999; position: absolute; overflow:hidden; width:300px; height:30px; left:'.($screenwidth - 300).'px; top:0px" id="timer">';
    echo '</div>';
    echo '<div style="position: absolute; overflow:scroll; width:'.$screenwidth.'px; height:'.$screenheight.'px; left:0px; top:0px" id="content" class="content">';
    echo '</div>';

?>

<script language="JavaScript" type="text/javascript">
jQuery('body').css('overflow','hidden'); 
ShowAllocations(<?php echo $intPassedFilterID?>,<?php echo $intPassedTeamID?>);

function ShowAllocations(filter, teamId) {
    // The counter
    $.post("include/timer.php", {
        filter: filter,
        teamId: teamId
    },
    function(data,status){
        $('#timer').html(data);
    })
    $("#loading").show();
    $.post("include/get-allocations-data.php", {
        teamId: teamId
    },
    function(resp){
        var resp = $.parseJSON(resp);
        var postSetFilterData = {
            filterId: filter,
            teamId: teamId,
            action:'setfilter',
            screenName: 'ViewDaily'
        }
        $.post("../components/filters/filter-process.php", {
            filterId: filter,
            action:'getdetails'
        },
        function(dataFilter,statusFilter){
            var filterDetails = $.parseJSON(dataFilter);

            var postData = {
                'filter': filter,
                'teamId': teamId,
                'StaffNameFilter': filterDetails.StaffNameOptFilter,
                'StaffName': filterDetails.StaffName,
                'SortCodeFilter': filterDetails.SortCodeOptFilter,
                'SortCode': filterDetails.SortCodeFilter,
                'CostCodeFilter': filterDetails.CostCodeOptFilter,
                'CostCode': filterDetails.CostCode,
                'SkillFilter': filterDetails.SkillNameOptFilter,
                'Skill': filterDetails.SkillName,
                'DutyFilter': filterDetails.DutyOptFilter,
                'Duty': filterDetails.DutyFilter,
                'DutyLabelFilter': filterDetails.DutyLabelOptFilter,
                'DutyLabel': filterDetails.DutyLabel,
                'JobNameFilter': filterDetails.JobNameOptFilter,
                'JobName': filterDetails.JobName,
                'JobLabelFilter': filterDetails.JobLabelOptFilter,
                'JobLabel': filterDetails.JobLabel,
                'SortOrder': filterDetails.SortOrder,
                'AdditionalTeamsFilter': filterDetails.AdditionalTeamsOptFilter,
                'AdditionalTeams': filterDetails.AdditionalTeams,
                'andMatch': filterDetails.AndMatch,
                'action':'createquery',
                'screenName': 'ViewDaily',
                'intWeek': resp.intWeek,
                'intDay': resp.intDay,
                'intTeamID': teamId,
                'rolepermission': resp.rolepermission,
                'DutyTime': filterDetails.DutyTime
            }

            $.post("include/allocations.php", postData, function(allocateData,statusAllocateData){
                $("#loading").hide();
                $('#content').html(allocateData);
            });
        });
    })
};
</script>

<?php
  }
}
 



