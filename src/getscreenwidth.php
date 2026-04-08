<?php
if (isset($_REQUEST['width'])) {
  session_start();
  $_SESSION['screenwidth'] = $_REQUEST['width'];    
  echo $_REQUEST['width'];
}



