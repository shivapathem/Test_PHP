<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';

$pdo = OpenDBLinkA7();
$date = $_REQUEST['date'];
$intGroupID = $_REQUEST['groupid'];

if (isset($_REQUEST['Update'])) {
  $weekday = getdayofweek ($date);
  $arrleavetypes = $_REQUEST['leavetype'];

  foreach ($arrleavetypes as $leavetype => $amount) {
  $query = "IF EXISTS (SELECT ID FROM LeaveRequestsAvailability WHERE  (dDate = CONVERT(DATETIME, '$date 00:00:00', 102)) AND (LeaveType = $leavetype)) 
                  UPDATE LeaveRequestsAvailability SET Amount = $amount where dDate = CONVERT(DATETIME, '$date 00:00:00', 102) and LeaveType = $leavetype 
            ELSE 
                  INSERT INTO LeaveRequestsAvailability (dDate, LeaveType, Amount) VALUES ( CONVERT(DATETIME, '$date 00:00:00', 102), $leavetype, $amount)";

          $stmt = $pdo->prepare($query);
          $stmt->execute();
  }

}
else {

// Get the amount of leave for this day....
$arrleave = GetLeaveAllowed($intGroupID, $date, $date);
echo '<form id="availabilityformday">';
  echo '<table width="600px" class="redtable">';
  echo '<tr>';
  echo '<th colspan="2"><br>Edting Leave for '.date("jS F Y", strtotime($date)).'<br>Leave Group: '.$arrleave[$intGroupID]['Description'].'<br><br></th>';
  echo '</tr>';
  
  foreach($arrleave[$intGroupID]['Types'] as $intTypeID => $arrType) {
    echo '<tr>';
    echo '<th>';
    echo $arrType['Description'];
    echo '</th>';
    echo '<td>';
    echo '<input type="text" name="leavetype['.$intTypeID.']" size="20" value="'.$arrType['dates'][$date]['available'].'">';
    echo '</td>';
    echo '</tr>';
  }
?>

    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" value="Update" name="Update"></td>
    </tr>
  </table>
  <input type="hidden" name="date" value="<?php echo $date?>">
  <input type="hidden" name="groupid" value="<?php echo $intGroupID?>">
</form>

<script type="text/javascript">
  $('document').ready(function(){
    $('#availabilityformday').validate({
      rules:{

      <?php
      foreach($arrleave[$intGroupID]['Types'] as $intTypeID => $arrType) {
        echo "'leavetype[$intTypeID]':{\n";
          echo "required:true,\n";
          echo "min: -1,\n";
          echo "max: 10,\n";
        echo "},\n";
       }
       ?>
    },
    submitHandler: function(form) {
      $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/admin/leaveeditavailabilityday.php', data:$('#availabilityformday').serialize(), success: function(data) {
        $.facebox.close();
        ShowLeaveAvalability(<?php echo $intGroupID?>, <?php echo  date ("Y", strtotime("-3 months", strtotime($date)))?>);
      }});
    }     
  })
  });

</script>

<?php
 }
?>