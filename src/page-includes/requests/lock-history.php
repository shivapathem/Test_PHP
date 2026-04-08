<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
  }
    include_once '../../function-includes/init.php';
    include_once '../../function-includes/genericfunctions.php';
    $id = $_REQUEST['id'];
    $pdo = OpenDBLinkA7();

    try {  
        $strHistory = "SELECT LockRequests.WeekNumber, LockRequests.iDay, LockRequests.RequestedOn, LockRequests.DutyName,
                        LockRequests.StartTime, LockRequests.EndTime, LockRequests.Duration, LockRequests.History, UserDetails.UD_DisplayLastName + ' ' + UserDetails.UD_DisplayFirstName as FullName
                        FROM LockRequests WITH (NOLOCK)
                        INNER JOIN UserDetails WITH (NOLOCK) ON LockRequests.ScheduledPersonID = UserDetails.UD_UserID
                        WHERE (LockRequests.ID = :id)";
        $stmt = $pdo->prepare($strHistory);
        $stmt->bindParam(':id',$id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
      } catch(Exception $e) {
        logger()->critical('DB Error', (array) $e);
       }
    if (!empty($row)) { 
        $intWeek = $row['WeekNumber'];
        $intDay = $row['iDay'];

        $strDate = date("l, jS M Y", strtotime(datefromweek($intWeek, $intDay)));
        echo '<table class="redtable" width="600px">';
        echo '<tr>';
        echo '<td class="tableheadersmall">';
        echo "<br>Lock History for ".$row["FullName"]."<br>On ".$strDate;
        if(!empty($row["dDate"])) {
            echo "Date ".date("jS F Y",strtotime($row["dDate"]));
        }

        echo '<br><br>';
        echo '</td';
        echo '</tr>';

        echo '<tr>';
        echo '<td>';
        echo $row["History"];
        echo '<hr>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';


        echo '</div>';
    }  else {
        
        echo '<table class="redtable" width="600px">';
        echo '<tr>';
        echo '<td class="tableheadersmall">';
        echo '<br><br>';
        echo '</td';
        echo '</tr>';

        echo '<tr>';
        echo '<td>';
        echo "No History Found";
        echo '<hr>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';


        echo '</div>';
    }

?>