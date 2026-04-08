<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';

$pdo = OpenDBLinkA7();
$year = $_REQUEST['year'];
$intGroupID = $_REQUEST['groupid'];

if (isset($_REQUEST['Update'])) {

  $sdate = $_REQUEST['sDate'];
  $edate = $_REQUEST['eDate'];
  $leavetype = $_REQUEST['type'];

  $day[0] = $_REQUEST['0'];
  $day[1] = $_REQUEST['1'];
  $day[2] = $_REQUEST['2'];
  $day[3] = $_REQUEST['3'];
  $day[4] = $_REQUEST['4'];
  $day[5] = $_REQUEST['5'];
  $day[6] = $_REQUEST['6'];

  while (strtotime($sdate) <= strtotime($edate)) {
    $weekday = getdayofweek ($sdate);
    
      $query = "IF EXISTS (SELECT ID FROM LeaveRequestsAvailability WHERE  (dDate = CONVERT(DATETIME, '$sdate 00:00:00', 102)) AND (LeaveType = $leavetype)) 
                  UPDATE LeaveRequestsAvailability SET Amount = $day[$weekday] where dDate = CONVERT(DATETIME, '$sdate 00:00:00', 102) and LeaveType = $leavetype 
                ELSE 
                  INSERT INTO LeaveRequestsAvailability (dDate, LeaveType, Amount) VALUES ( CONVERT(DATETIME, '$sdate 00:00:00', 102), $leavetype, $day[$weekday])";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
 	  $sdate = date ("Y-m-d", strtotime("+1 day", strtotime($sdate)));
  }
}
else {

$startdate = "$year-04-01";
// Only interested in the defaultts.....
$arrleave = GetLeaveAllowed($intGroupID, $startdate, $startdate);

?>

<form id="availabilityform">
  <table width="600px" class="redtable">
  <th colspan="4"><br>Editing Leave Availability<br><br></th>
  </tr> 
    <tr>
      <th width="150px">Start Date</th>
      <td align="right"><input type="hidden" name="sDate" id="datepicker-start" value="<?php echo $year?>-04-01" required/></td>
      <td><input type="text" id="salternate" size="30" value="<?php echo date("l, j F, Y", strtotime($year."-04-01"))?>" readonly="true"></td>
    </tr>
    <tr>
      <th width="100px">End Date</th>
      <td align="right"><input type="hidden" name="eDate" id="datepicker-end" value="<?php echo $year?>-04-01" size="20" required/></td>
      <td><input type="text" id="ealternate" size="30" value="<?php echo date("l, j F, Y", strtotime($year."-04-01"))?>" readonly="true"></td>
    </tr>
    </table>

  <table class="tablesmalltidy" width="100%">
    <tr>
      <th width="100px">Leave Type</th>
      <td><select size="1" name="type">
      <?php
      foreach($arrleave[$intGroupID]['Types'] as $intTypeID => $arrLeaveType) {
        echo '<option value="'.$intTypeID.'">'.$arrLeaveType['Description'].'</option>';
      }
      ?>
      </select></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th width="100px">Saturday</th>
      <td><input type="text" name="0" size="10" value="1"></td>
      <th width="100px">Sunday</td>
      <td><input type="text" name="1" size="10" value="1"></td>
    </tr>
    <tr>
      <th width="100px">Monday</th>
      <td><input type="text" name="2" size="10" value="1"></td>
      <th width="100px">Tuesday</td>
      <td><input type="text" name="3" size="10" value="1"></td>
    </tr>
    <tr>
      <th width="100px">Wednesday</th>
      <td><input type="text" name="4" size="10" value="1"></td>
      <th width="100px">Thursday</td>
      <td><input type="text" name="5" size="10" value="1"></td>
    </tr>
    <tr>
      <th width="100px">Friday</th>
      <td><input type="text" name="6" size="10" value="1"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" value="Update" name="Update"></td>
      <td><input type="button" value="Cancel" onclick="cancel()"></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <input type="hidden" name="year" value="<?php echo $year?>">
  <input type="hidden" name="groupid" value="<?php echo $intGroupID?>">
</form>


<script type="text/javascript">
  $('document').ready(function(){

    $.validator.addMethod("noSpace", function (value, element) {
        let item = value.trim();
        return item != "";
    }, "This field is required");

    $('#availabilityform').validate({
      rules:{
        "datepicker-start":{
          required:true,
          date: true,
        },
        "datepicker-end":{
          required:true,
          date: true,
        },
        "0":{
          required:true,
          min: -1,
          max: 10,
          noSpace: true,
        },
        "1":{
          required:true,
          min: -1,
          max: 10,
          noSpace: true,
        },
        "2":{
          required:true,
          min: -1,
          max: 10,
          noSpace: true,
        },
        "3":{
          required:true,
          min: -1,
          max: 10,
          noSpace: true,
        },
        "4":{
          required:true,
          min: -1,
          max: 10,
          noSpace: true,
        },
        "5":{
          required:true,
          min: -1,
          max: 10,
          noSpace: true,
        },
         "6":{
          required:true,
          min: -1,
          max: 10,
          noSpace: true,
        }},

        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/leaveeditavailability.php', data:$('#availabilityform').serialize(), success: function(data) {
            $.facebox.close();
            ShowLeaveAvalability(<?php echo $intGroupID?>, <?php echo $year?>);            
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