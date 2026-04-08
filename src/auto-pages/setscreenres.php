<?php
session_start();

if (isset($_REQUEST['width'])) {
  $_SESSION['width'] = $_REQUEST['width'];
  $_SESSION['height'] = $_REQUEST['height']; 
}
