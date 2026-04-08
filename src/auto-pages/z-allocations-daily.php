<?php
session_start();
include_once '../function-includes/init.php';
include_once '../function-includes/genericfunctions.php';
include_once '../function-includes/allocationsfunctionsday.php';
include_once '../function-includes/allocationsfunctions.php';
include_once '../function-includes/requestfunctions.php';
include_once '../function-includes/leavefunctions.php';

if (isset($_REQUEST['filter'])) {
  $intFilterID = $_REQUEST['filter'];
}
else {
  $intFilterID = 0;
}
if (!isset($_SESSION['height']) || $intFilterID == 0) {
  header("Location: index.php?filter=$intFilterID");
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<html>
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
<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/jquery.timeTo.js"></script>
<title>Allocations</title>
</head>
<body>
<?php



$dutyheight = 35;
$rowheight = 40;
$screenwidth =  $_SESSION['width'];
$screenheight =  $_SESSION['height'];
$date = date("Y-m-d");
echo '<div style="background-color:transparent; z-index:9999; position: absolute; overflow:hidden; width:300px; height:30px; left:'.($screenwidth - 300).'px; top:0px" id="timer">';
echo '</div>';
echo '<div style="position: absolute; overflow:scroll; width:'.$screenwidth.'px; height:'.$screenheight.'px; left:0px; top:0px" id="content" class="content">';
echo '</div>';

?>

<script language="JavaScript" type="text/javascript">
jQuery('body').css('overflow','hidden'); 
<?php
if ($intFilterID != 0) {
  echo "ShowAllocations($intFilterID)\n";
}
?>
function ShowAllocations(filter) {
  // The counter
  $.post("include/timer.php", {
    filter: filter,
  },
  function(data,status){
    $('#timer').html(data);
   }
  )
  $.post("include/allocations.php", {
    filter: filter
  },
  function(data,status){
    $('#content').html(data);
   }
  )
};
</script>
