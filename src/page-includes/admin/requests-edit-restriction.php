<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$pdo = OpenDBLinkA7();
$id = $_REQUEST['id'];
$intGroupID = $_REQUEST['groupid'];
if (isset($_REQUEST['Update'])) {
  $sdate = $_REQUEST['sDate'];
  $edate = $_REQUEST['eDate'];
  $intRestrictionType = $_REQUEST['rtype'];
  if ($id == 0) {
    $query = "INSERT INTO RequestDatesClosed(
              startdate,
              enddate,
              type,
              GroupID)
              VALUES (
              '$sdate',
              '$edate',
              $intRestrictionType,
              $intGroupID)";

  }
  else {
    $query = "UPDATE RequestDatesClosed
              SET
              startdate = '$sdate',
              enddate = '$edate',
              type = $intRestrictionType
              WHERE (id = $id)";
  }
  try {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $rsClosed = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }

}
else {
  $strLeaveGroupDesc = GetLeaveRequestDescFromID ($intGroupID);
  if ($id != 0) {
    try {
      $query = "SELECT startdate, enddate, type FROM RequestDatesClosed WHERE (id = :id)";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
     }
    $startdate = date("Y-m-d", strtotime($row['startdate'])); //
    $enddate = date("Y-m-d", strtotime($row['enddate']));  //
    $intRestrictionType = $row['type'];
  } else {
    $startdate = date("Y-m-d");
    $enddate = date("Y-m-d");
    $intRestrictionType = 0;
  }
?>

<form id="restrictionform">
  <table class="tablesmalltidy" width="600px">
    <tr>
      <th colspan="3"><br>Edit Request Restriction  for <?php echo $strLeaveGroupDesc?><br><br></th>
    </tr>
    <tr>
      <td width="250px">Start Date</td>
      <td align="right"><input type="hidden" name="sDate" id="datepicker-start" value="<?php echo $startdate?>" required/></td>
      <td><input type="text" id="salternate" size="30" value="<?php echo date("l, j F, Y", strtotime($startdate))?>"></td>
    </tr>
    <tr>
      <td width="250px">End Date</td>
      <td align="right"><input type="hidden" name="eDate" id="datepicker-end" value="<?php echo $enddate?>" size="20" required/></td>
      <td><input type="text" id="ealternate" size="30" value="<?php echo date("l, j F, Y", strtotime($enddate))?>"></td>
    </tr>
    <tr>
      <td>Type of Restriction</td>
      <td colspan="2">
        <select size="1" name="rtype">
      <?php
      echo '<option value="0"';
      if ($intRestrictionType == 0) {
        echo ' selected';
      }
      echo '>No requests possible</option>'; 
       
      echo '<option value="1"';
      if ($intRestrictionType == 1) {
        echo ' selected';
      }
      echo '>Allowed  all shown as Not Available - Counted</option>';     
  
      echo '<option value="2"';
      if ($intRestrictionType == 2) {
        echo ' selected';
      }
      echo '>Allowed  all shown as Not Available - Not Counted</option>';   
 
  
  ?>
  
      </select>
    </td>
    </tr>
    </table>
    <table>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" value="Update" name="Update"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <input type="hidden" name="id" value="<?php echo $id?>">
  <input type="hidden" name="groupid" value="<?php echo $intGroupID?>">
</form>

<script type="text/javascript">
  $('document').ready(function(){
    $('#restrictionform').validate({
      rules:{
        "datepicker-start":{
          required:true,
          date: true,
        },
        "datepicker-end":{
          required:true,
          date: true,
        },
      }, 
      submitHandler: function(form) {
        $('input[type="submit"]').prop('disabled', true);
        $.ajax({type:'POST', url: 'page-includes/admin/requests-edit-restriction.php', data:$('#restrictionform').serialize(), success: function(data) {
          $.facebox.close();
          ShowRequestsClosed(<?php echo $intGroupID?>);            
        }});
      }       
    })
  });

  $(function() {
    $( "#datepicker-start" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#salternate",
      altFormat: "DD, d MM, yy"
    });
  });
  $(function() {
    $( "#datepicker-end" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#ealternate",
      altFormat: "DD, d MM, yy"
    });
  });
</script>

<?php
 }
?>