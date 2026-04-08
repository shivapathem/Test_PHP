<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';

$intJobID = $_REQUEST["jobid"];
$intAreaID = $_SESSION['user']['AreaID'];

$intLeftOffset = 100;
$intHourWidth = 80;


$rsJobJson = GetMasterJobByID ($intJobID, $intAreaID);
$rsJob = json_decode($rsJobJson,true);
if(!empty($rsJob)) {
    $rowcount = count($rsJob);
}
else{
    $rowcount = 1;
}
for ($row = 0; $row < $rowcount; $row++) {
    $arrJob['Job'] = $rsJob[$row]['Job'];
    $arrJob['StartTime'] = $rsJob[$row]['StartTime'];
    $arrJob['EndTime'] = $rsJob[$row]['EndTime'];
    if (is_null($rsJob[$row]['BackColour'])) {
        $arrJob['BackColour'] = '#aaaaaa';
    } else {
        $arrJob['BackColour'] = $rsJob[$row]['BackColour'];
    }
    $arrJob['ForeColour'] = $rsJob[$row]['ForeColour'];
}

$intStart = floor($arrJob['StartTime'] / 3600);
$intEnd = ceil($arrJob['EndTime'] / 3600);
$left = $intLeftOffset + (($arrJob['StartTime'] / 3600) - $intStart) * $intHourWidth;
$intWidth = (($arrJob['EndTime'] - $arrJob['StartTime']) / 3600) * $intHourWidth;

  echo '<div style="position: relative; height:40px;  margin:0 auto;">';
  
  // The hours in the day....
  echo '<div style="position: absolute; height:30px; left:'.$intLeftOffset.'px; top:0px" id="top" class="times">';
  for ($i=$intStart; $i <= ($intEnd); $i++){
    echo '<div style="width:'.$intHourWidth.'px;  position:absolute; left:'.(($i - $intStart) * $intHourWidth).'px; top:0px">'; 
    echo '<div class="highlighted" style="position: absolute; left: 0px; top:0px; width:'.$intHourWidth.'px; height:15px">'.(gmdate("H:00",  $i * 3600)).'</div>';
    echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
    echo '</div>';
  }
  echo '</div>';  
    // Draw in the Times
  echo '<div style="position: absolute; width:900px; height:30px; left:'.$intLeftOffset.'px; top:15px">';
  drawtimecells("2015-01-01", 0, $intEnd - $intStart + 1, $intHourWidth, 25);
  echo '</div>';  
  echo '<div class="master-subjobs-context-menu" jobid="'.$intJobID.'" style="overflow:hidden; width: '.$intWidth.'px; height:20px; position:absolute; left:'.$left.'px; top:20px; background-color:'.$arrJob['BackColour'].'">';
  echo $arrJob['Job'];
  echo '</div>';
  echo '</div>';
  
  


