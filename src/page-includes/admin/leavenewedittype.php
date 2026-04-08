<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';

$pdo = OpenDBLinkA7();

$intGroupID = $_REQUEST['groupid'];
$intTypeID = $_REQUEST['id'];

if (isset($_REQUEST['Update'])) {
  $leavedesc = $_REQUEST['description'];
  if (isset($_REQUEST['countclicks'])) {
    $countclicks = 1;
  }
  else {
      $countclicks = 0;
  }
  $intLeaveStarts = $_REQUEST['lStarts'];
  $intShortNoticeLeaveStarts = $_REQUEST['snlStarts'];
  $intLeaveEnds = $_REQUEST['LeaveEnds'];
        
  $days = $_REQUEST['0'];
  $days.= ','.$_REQUEST['1'];
  $days.= ','.$_REQUEST['2'];
  $days.= ','.$_REQUEST['3'];
  $days.= ','.$_REQUEST['4'];
  $days.= ','.$_REQUEST['5'];
  $days.= ','.$_REQUEST['6'];

  if ($intTypeID == 0) {
    $query = "INSERT INTO leave_types
              (GroupID, description, LeaveStarts, SNLeaveStarts, LeaveEnds, countclicks, defaultamounts)
              VALUES (
              $intGroupID,
              '$leavedesc',
              $intLeaveStarts,
              $intShortNoticeLeaveStarts,
              $intLeaveEnds,
              $countclicks,
              '$days')";
  }
  else {
    $query = "UPDATE leave_types 
              SET
              description = '$leavedesc',
              LeaveStarts = $intLeaveStarts,
              SNLeaveStarts = $intShortNoticeLeaveStarts,
              LeaveEnds = $intLeaveEnds,
              countclicks = $countclicks,
              defaultamounts = '$days'
              where id = $intTypeID";
  
  }

$stmt = $pdo->prepare($query);
$stmt->execute();

}
else {
if ($intTypeID == 0) {
  $strDescription = '';
  $intCountClicks = '';
  $intLeaveStarts = '';
  $intShortNoticeLeaveStarts = '';
  $intLeaveEnds = '';
  $arrDays = explode(',', '0,0,0,0,0,0,0,0,0,0,0');
}
else {
  $query = "SELECT id, GroupID, description,  LeaveStarts, SNLeaveStarts, LeaveEnds, countclicks, defaultamounts
            FROM  leave_types
            WHERE (id = $intTypeID)";

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $strDescription = $row['description'];
  $intCountClicks = $row['countclicks'];
  $intLeaveStarts = $row['LeaveStarts'];
  $intShortNoticeLeaveStarts = $row['SNLeaveStarts'];
  $intLeaveEnds = $row['LeaveEnds'];
  $arrDays = explode(',', $row['defaultamounts']);

}

  echo '<form id="neweditleavetype">';
  echo '<table width="600px" class="redtable">';
  echo '<th><br>Editing Leave Type<br><br></td>';
  echo '</tr>';  
  echo '</table>';
  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>';
 
  echo '<table width="600px" class="redtable">';
  echo '<tr>';
  echo '<th width="100px">Description</th>';
  echo '<td colspan="3"><input type="text" name="description" size="50" maxlength=100 value="'.$strDescription.'"></td>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td colspan="4">';    
  echo '<table class="redtable" width="100%">';     
  echo '<th>';
  echo 'Days Until Leave Starts';
  echo ' </th>';  
  echo ' <td>';  
  echo ' <input class="smalltext" name="lStarts" id="lStarts" size="5" value="'.$intLeaveStarts.'" type="text">';
  echo ' </td>';

  echo ' <th>';
  echo 'Days until Short Notice';
  echo ' </th>';  
  echo ' <td>'; 
  echo ' <input name="snlStarts" id="snlStarts" size="5" value="'.$intShortNoticeLeaveStarts.'" type="text">';
  echo '</td>';
  echo '<th>';
  echo 'Leave Ends';
  echo '</th>';  
  echo '<td>';   
  echo ' <input name="LeaveEnds" id="LeaveEnds" size="5" value="'.$intLeaveEnds.'" type="text">';
  echo '</td>';

  echo '</tr>';    
  echo '</table>';
  echo '</tr>';  
   
  echo '<tr>';
  echo '<th width="100px">Count Clicks</th>';
  echo '<td colspan="3">';
  echo '<input type="checkbox" name="countclicks" value="ON"';
  if ($intCountClicks == 1) {
    echo ' checked';
  }
  echo '>';

        echo '</td>';
      echo '</tr>';

      echo '<tr>';
        echo '<th width="100px">Saturday</th>';
        echo '<td><input type="text" name="0" size="10" value="'.$arrDays[0].'"></td>';
        echo '<th width="100px">Sunday</th>';
        echo '<td><input type="text" name="1" size="10" value="'.$arrDays[1].'"></td>';
      echo '</tr>';
      echo '<tr>';
        echo '<th width="100px">Monday</th>';
        echo '<td><input type="text" name="2" size="10" value="'.$arrDays[2].'"></td> ';
        echo '<th width="100px">Tuesday</th>';
        echo '<td><input type="text" name="3" size="10" value="'.$arrDays[3].'"></td>';
      echo '</tr>';
      echo '<tr>';
        echo '<th width="100px">Wednesday</th>';
        echo '<td><input type="text" name="4" size="10" value="'.$arrDays[4].'"></td>';
        echo '<th width="100px">Thursday</th>';
        echo '<td><input type="text" name="5" size="10" value="'.$arrDays[5].'"></td>';
      echo '</tr>';
      echo '<tr>';
        echo '<th width="100px">Friday</th>';
        echo '<td><input type="text" name="6" size="10" value="'.$arrDays[6].'"></td>';
        echo '<td>&nbsp;</td>';
        echo '<td>&nbsp;</td>';
      echo '</tr>';
      echo '<tr>';
        echo '<td>&nbsp;</td>';
        echo '<td><input type="submit" value="Update" name="Update"></td>';
        echo '<td><input type="button" value="Cancel" onclick="cancel()"></td>';
        echo '<td>&nbsp;</td>';
      echo '</tr>';
    echo '</table>';
    echo '<input type="hidden" name="groupid" value="'.$intGroupID.'">';
    echo '<input type="hidden" name="id" value="'.$intTypeID.'">';
  echo '</form> ';
?>

<script type="text/javascript">
  $('document').ready(function(){
    jQuery.validator.addMethod("noSpace", function(value, element) { 
      return   value.trim() != ""; 
    }, "Please Enter a Description<br>");
    
    $('#neweditleavetype').validate({
      errorLabelContainer: "#errorBox",    
      rules:{
        "description":{
          noSpace : true,
          required: true
        },
        "snlStarts": {
          required: true,
          number: true
        },
        "lStarts": {
          required: true,
          number: true
        },
        "LeaveEnds": {
          required: true,
          number: true
        },         
        "0":{
          required:true,
          min: -1,
          max: 10,
        },
        "1":{
          required:true,
          min: -1,
          max: 10,
        },
        "2":{
          required:true,
          min: -1,
          max: 10,
        },
        "3":{
          required:true,
          min: -1,
          max: 10,
        },
        "4":{
          required:true,
          min: -1,
          max: 10,
        },
        "5":{
          required:true,
          min: -1,
          max: 10,
        },
         "6":{
          required:true,
          min: -1,
          max: 10,
        }},
        messages: {
          description: {
                    required: "Please Enter a Description<br>"
                },
          snlStarts: "Please Enter the number of Days before Short Notice Leave Starts<br>",
          lStarts: "Please Enter the number of Days before Leave Starts<br>",
          LeaveEnds: "Please Enter the number of Days that Leave Ends<br>",
          0: "Please Enter the number of Requests allowed for Saturday<br>",
          1: "Please Enter the number of Requests allowed for Sunday<br>",
          2: "Please Enter the number of Requests allowed for Monday<br>",
          3: "Please Enter the number of Requests allowed for Tuesday<br>",
          4: "Please Enter the number of Requests allowed for Wednesday<br>",
          5: "Please Enter the number of Requests allowed for Thursday<br>",
          6: "Please Enter the number of Requests allowed for Friday<br>",
        },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/leavenewedittype.php', data:$('#neweditleavetype').serialize(), success: function(data) {
            $.facebox.close();
            ShowLeaveGroupConfig(<?php echo $intGroupID?>);
            
          }});
        }   
  })
});

</script>
<?php
 }
?>