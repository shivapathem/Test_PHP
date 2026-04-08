<?php

include_once 'functions.php';

?>
<!doctype html>
<html lang="us">
<head>
	<meta charset="utf-8">
	<title>Production Operations</title>
	<link href="styles/default.css" rel="stylesheet">  
	<link href="styles/jquery-ui.css" rel="stylesheet">
  
  <script src="js/jquery.js"></script>
  <script src="js/jquery-ui.js"></script>
</head>
<body>

<div id="tabs">
	<ul>
		<li><a href="#tabs-1">London</a></li>
		<li><a href="#tabs-3">Salford</a></li>
		<li><a href="#tabs-4">Location</a></li>
		<li><a href="#tabs-5">London Attachments</a></li>
		<li><a href="#tabs-6">Salford Attachments</a></li>
	</ul>

<?php

echo'<div id="tabs-1">';
$dept = 1;
include 'photos-this-department.php';
echo '</div>';

echo'<div id="tabs-3">';
$base = 45;
//include 'photos-this-department.php';
echo '</div>';

echo'<div id="tabs-4">';
$base = 3;
//include 'photos-this-department.php';
echo '</div>';

$departmentID = 4;
echo'<div id="tabs-5">';
//include 'attachments.php';
echo '</div>';

$departmentID = 45;
echo'<div id="tabs-6">';
//include 'attachments.php';
echo '</div>';
?>
  
</div>

<script>
	$( "#tabs" ).tabs();
</script>

</body>
</html>
