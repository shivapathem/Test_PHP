<?php
session_start();
if (isset($_SESSION['bst'])) {
  $bst = $_SESSION['bst'];
}
else {
  $bst = date("I");
}


if ($bst == 0) {
  $bst= 1;
}
else {
  $bst = 0;
}
$_SESSION['bst'] = $bst;