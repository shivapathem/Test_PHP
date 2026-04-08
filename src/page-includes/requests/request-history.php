<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$id = $_REQUEST['id'] ?? 0;
$pdo = OpenDBLinkA7();
$strHistory = "exec [dbo].[usp_fetch_RequestHistory] :ID";
try {
    $stmt = $pdo->prepare($strHistory);
    $stmt->bindParam(':ID', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
}
$rowtext ='';
if (!empty($row) && isset($row)) //Bypass code for empty result set
{
    $rowtext.= '<table class="redtable" width="600px">';
    $rowtext.= '<tr>';
    $rowtext.= '<td class="tableheadersmall">';
    $rowtext.= '<br>Request History for ' . $row["FullName"] . '<br>';
    if (!empty($row["dDate"])) {
        $rowtext.= "Date " . date("jS F Y",strtotime($row["dDate"]));
    }
    $rowtext.= '<br><br>';
    $rowtext.= '</td';
    $rowtext.= '</tr>';
    $rowtext.= '<tr>';
    $rowtext.= '<td>';
    $rowtext.= $row["History"];
    $rowtext.= '<hr>';
    $rowtext.= '</td>';
    $rowtext.= '</tr>';
    $rowtext.= '<tr>';
    $rowtext.= '<td align="center">';
    $rowtext.= '<br><input type="button" value="OK" onclick="cancel()">';
    $rowtext.= '</td>';
    $rowtext.= '</tr>';
    $rowtext.= '</table>';
    $rowtext.='</div>';
} else {
    $rowtext.= 'No Result Found. Contact admin.';
}
echo $rowtext;
?>