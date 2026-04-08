<?php
session_start();
include_once '../../function-includes/init.php';

$intDutyID = $_REQUEST['id'];
$intDay = $_REQUEST['day'];

$db = OpenDatabase();


  $query = "IF EXISTS (SELECT        ID
                        FROM          skills_duties_days
                        WHERE         (duty_id = $intDutyID) AND (dotw = $intDay))
                        
                        DELETE FROM skills_duties_days
WHERE        (duty_id = $intDutyID) AND (dotw = $intDay)

ELSE 
INSERT INTO skills_duties_days
                         (duty_id, dotw)
VALUES        ($intDutyID, $intDay)






";

echo $query;

sqlsrv_query($db, $query);
echo $intDutyID;
?>