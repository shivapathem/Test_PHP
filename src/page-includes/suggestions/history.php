<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/suggestionsfunctions.php';

$id = $_REQUEST['id'];
  $db = OpenDatabase();
  $strQuery = "SELECT         History
               FROM          dbo.WebSiteSuggestions
               WHERE        (id = $id)";

  $rsSuggestion = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsSuggestion);
  $strHistory = $row['History'];
echo '<div style="width: 800px; height: 400px; position: relative; overflow:auto">'; 
echo '<table class="tablesmalltidy" width="100%">';
echo '<tr>';
echo '<th>';
echo '<br>History<br><br>';
echo '</th>';
echo '</tr>';

echo '<tr>';
echo '<td>';
echo $strHistory;

echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '<br><input type="button" value="Close" onclick="cancel()">';
