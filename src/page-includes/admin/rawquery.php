<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();

if (isset($_REQUEST['Query'])) {
  $query = $_REQUEST['Query'];
  echo '<form method="POST" action="rawquery.php">';
  echo '<textarea rows="7" name="Query" cols="88">'.$query.'</textarea>';
  echo '<br><input type="submit" value="Submit" name="Submit">';
  echo '</form>';
  echo 'Submitted Query: '.$query.'<br>';
  $rsQuery = sqlsrv_query($db, $query);
  if (sqlsrv_has_rows($rsQuery)) {
    echo '<pre>';
    while ($row = sqlsrv_fetch_array($rsQuery)) {
      print_r($row);
    }  
  }
  else {
    echo 'No Results returned';
  }
  
  

}
else {
echo '<form method="POST" action="rawquery.php">';
echo '<textarea rows="7" name="Query" cols="88"></textarea>';
echo '<br><input type="submit" value="Submit" name="Submit">';
echo '</form>';


}


