<?php
session_start();

if (isset($_REQUEST['width'])) {
  $_SESSION['width'] = $_REQUEST['width'];
  $_SESSION['height'] = $_REQUEST['height'];
  header('Location: allocations-daily.php');  
}
else {
<script language="JavaScript">

getresolution()
function getresolution()  {

  var scwidth=screen.width
  var scheight=screen.height
  document.location = "index.php?width=" + scwidth + "&height=" + scheight;
}

</script>

<?php
}
?>
