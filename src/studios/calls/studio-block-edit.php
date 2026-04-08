<?php

//date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
$id = $_REQUEST['id'];
$studioid = $_REQUEST['studioid'];
$db = OpenDatabase();

if (!isset($_REQUEST['submit'])) {
  $query = "SELECT id, studioid, startdate, enddate, dotw, programmeid, sm, starttime, endtime
            FROM  studios_block_bookings
            WHERE (id = $id) AND AllocateInstanceID = ". getCurrentInstanceId();

  $rsbooking = sqlsrv_query($db, $query, [], ["Scrollable"=>"buffered"]);

  if (sqlsrv_num_rows($rsbooking) == 0) {
    $startdate = date("Y-m-d");
    $enddate = date("Y-m-d");
    $starttime = '09:00';
    $endtime = '09:00';
  }
  else {

    $row = sqlsrv_fetch_array($rsbooking);
    $startdate = date("Y-m-d", strtotime($row['startdate']));
    $enddate = date("Y-m-d", strtotime($row['enddate']));
    $starttime = date("H:i", strtotime($row['starttime']));
    $endtime = date("H:i", strtotime($row['endtime']));
    $programmeid = $row['programmeid'];
    if (!is_null($row['dotw'])) {
      $arrtemp = explode(',', $row['dotw']);
      foreach ($arrtemp as $day)  {
        $arrdays[$day] = 1;
      }
    }
  }

  echo '<div id="page">';
  echo '<form id="editbooking">';
  echo '<table class="tablesmall" width="600px">';
  echo '<tr>';
  echo '<td colspan="3" class="tableheadersmall bigtextboldcentre"><br>Edit Block Booking<br><br></td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall" width="150px">Start Date</td>';
  echo '<td align="right" class="lightcell"><input type="hidden" name="sDate" id="datepicker-start" value="'.$startdate.'" required/></td>';
  echo '<td class="lightcell"><input type="text" id="salternate" size="30" value="'.date("l, j F, Y", strtotime($startdate."-04-01")).'"></td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall" width="150px">Start Date</td>';
  echo '<td align="right" class="lightcell"><input type="hidden" name="eDate" id="datepicker-end" value="'.$enddate.'" required/></td>';
  echo '<td class="lightcell"><input type="text" id="ealternate" size="30" value="'.date("l, j F, Y", strtotime($enddate."-04-01")).'"></td>';
  echo '</tr>';
  echo '</table>';

  echo '<table class="tablesmall" width="600px">';
  echo '<tr>';
  echo '<td class="tableheadersmall" width="100px">Start</td>';
  echo '<td>';
  echo '<input id="StartTime" name="StartTime" type="text" class="time" size="20" value="'.$starttime.'"/>';
  echo '</td>';
  echo '<td class="tableheadersmall" width="100px">End</td>';
  echo '<td>';
  echo '<input id="EndTime" name="EndTime" type="text" class="time" size="20"  value="'.$endtime.'"/>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="tableheadersmall" width="100px">Saturday</td>';
  echo '<td><input type="checkbox" name="0" value="ON"';
  if (isset($arrdays[0])) {
    echo ' checked';
  }
  echo '></td>';
  echo '<td class="tableheadersmall" width="100px">Sunday</td>';
  echo '<td><input type="checkbox" name="1" value="ON"';
  if (isset($arrdays[1])) {
    echo ' checked';
  }
  echo '></td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="tableheadersmall" width="100px">Monday</td>';
  echo '<td><input type="checkbox" name="2" value="ON"';
  if (isset($arrdays[2])) {
    echo ' checked';
  }
  echo '></td>';
  echo '<td class="tableheadersmall" width="100px">Tuesday</td>';
  echo '<td><input type="checkbox" name="3" value="ON"';
  if (isset($arrdays[3])) {
    echo ' checked';
  }
  echo '></td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="tableheadersmall" width="100px">Wednesday</td>';
  echo '<td><input type="checkbox" name="4" value="ON"';
  if (isset($arrdays[4])) {
    echo ' checked';
  }
  echo '></td>';
  echo '<td class="tableheadersmall" width="100px">Thursday</td>';
  echo '<td><input type="checkbox" name="5" value="ON"';
  if (isset($arrdays[5])) {
    echo ' checked';
  }
  echo '></td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="tableheadersmall" width="100px">Friday</td>';
  echo '<td colspan="3"><input type="checkbox" name="6" value="ON"';
  if (isset($arrdays[6])) {
    echo ' checked';
  }
  echo '></td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall" colspan="4">&nbsp;</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall">Programme</td>';
  echo '<td colspan="3">';
  //echo '<div class="ui-widget">';
  echo '<input id="programme" size="30">';
  //echo '</div>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall" colspan="4">&nbsp;</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall">Requires an SM</td>';
  echo '<td colspan="3">';
  echo '<input type="checkbox" name="SM" value="ON"';
  if ($row['sm'] == 1) {
    echo ' checked';
  }
  echo '>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td></td>';
  echo '<td><input name="submit" type="submit" value="Submit"></input></td>';
  echo '</tr>';

  echo '</table>';

  echo '<input type="hidden" name="id" value="'.$id.'">';
  echo '<input type="hidden" name="studioid" value="'.$studioid.'">';
  echo '<input type="hidden" id="programmeid" name="programmeid" value="'.$programmeid.'">';
  echo '</form>';
  echo '</div>';
?>

<script type="text/javascript">
  $('document').ready(function(){
    $('#editbooking').validate({
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
          $.ajax({type:'POST', url: 'studios/calls/studio-block-edit.php', data:$('#editbooking').serialize(), success: function(data) {
             $('#page').html(data);
          }});

   }







  })
  });


  $(function() {
    $( "#datepicker-start" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
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
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#ealternate",
      altFormat: "DD, d MM, yy"
    });
  });

$(function() {
  $('#StartTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

$(function() {
  $('#EndTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

  $( "#programme" ).autocomplete({
    source: "page-includes/ajax-calls/return-programmes.php",
    minLength: 2,
    select: function( event, ui ) {
      $('#programmeid').val(ui.item.id);

    }
  });

</script>

<?php

}
else {

  $startdate = $_REQUEST['sDate'];
  $enddate = $_REQUEST['eDate'];
  $starttime = $_REQUEST['StartTime'];
  $endtime = $_REQUEST['EndTime'];
  $programmeid = $_REQUEST['programmeid'];
  for ($i = 0; $i <=6; $i++) {
    if (isset($_REQUEST[$i])) {
      $arrdays[$i] = $i;
    }
  }

  if (isset($arrdays)) {
    $days = implode(',', $arrdays);
    $days = "N'$days'";
  }
  else {
    $days = "NULL";
  }

  if (isset($_REQUEST['SM'])) {
    $sm = 1;
  }
  else {
      $sm = 0;
  }

  if ($id == 0) {
    $query = "INSERT
              INTO studios_block_bookings(
              studioid,
              startdate,
              enddate,
              dotw,
              programmeid,
              sm,
              starttime,
              endtime,
              AllocateInstanceID)
              VALUES (
              $studioid,
              CONVERT(DATETIME, '$startdate 00:00:00', 102),
              CONVERT(DATETIME, '$enddate 00:00:00', 102),
              $days,
              $programmeid,
              $sm,
              CONVERT(DATETIME, '2015-04-06 $starttime:00', 102),
              CONVERT(DATETIME, '2015-04-06 $endtime:00', 102),
              " . getCurrentInstanceId() .
              ");
              SELECT SCOPE_IDENTITY() as computed";
      $results = sqlsrv_query($db, $query);
      $id = sql_last_insert_id($results);
      
      // echo $query;

  }
  else {
    $query = "UPDATE studios_block_bookings
              SET startdate = CONVERT(DATETIME, '$startdate 00:00:00', 102),
              enddate = CONVERT(DATETIME, '$enddate 00:00:00', 102),
              dotw = $days,
              programmeid = $programmeid,
              sm = $sm,
              starttime = CONVERT(DATETIME, '2015-04-06 $starttime:00', 102),
              endtime = CONVERT(DATETIME, '2015-04-06 $endtime:00', 102)
              WHERE (id = $id)
              AND  AllocateInstanceID = " . getCurrentInstanceId();


  sqlsrv_query($db, $query);
  }

?>

    <script type="text/javascript">
    $(function(){
      $.facebox.close();
      FillBlockDetail (<?php echo $studioid?>, <?php echo $id?>);

    })
    </script>
    <?php
  }

