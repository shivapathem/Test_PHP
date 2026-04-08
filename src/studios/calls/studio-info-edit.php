<?php

//date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
$id = $_REQUEST['id'];

$db = OpenDatabase();

if (!isset($_REQUEST['submit'])) {
  $query = "SELECT studio, sm, bookable
            FROM  studios
            WHERE (id = $id) AND AllocateInstanceID = ". getCurrentInstanceId();

  $rsstudio = sqlsrv_query($db, $query);
  $row = sqlsrv_fetch_array($rsstudio);
  echo '<div id="page">';
  echo '<form id="editstudio">';
  echo '<table class="smalltable" width="100%">';
  echo '<tr>';
  echo '<td colspan="2" class="tableheadersmall bigtextboldcentre"><br>Edit Studio Info<br><br></td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="tableheadersmall">Name</td>';
  echo '<td>';
  echo '<input id="name" name="name" type="text" size="40" value="'.$row['studio'].'" />';
  echo '</td>';
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
  echo '<td class="tableheadersmall">Bookable (Production)</td>';
  echo '<td colspan="3">';
  echo '<input type="checkbox" name="bookable" value="ON"';
  if ($row['bookable'] == 1) {
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
  echo '</form>';
  echo '</div>';
?>
<script type="text/javascript">
$('document').ready(function(){
    $('#editstudio').validate({
      debug: true,
      rules:{
        "name":{
          required:true,
        }
      },
        messages:{
          "name":{
            required:"<img id='exclamation' src='images/messagebox_warning.png' width='16' height='16' title='Please enter a studio name.' />"
          },
        },
        submitHandler: function(form) {
          $.ajax({type:'POST', url: 'studios/calls/studio-info-edit.php', data:$('#editstudio').serialize(), success: function(data) {
             $('#page').html(data);
          }});
        }
    })
  });
</script>

<?php

}
else {
  $studio = $_REQUEST['name'];
  if (isset($_REQUEST['bookable'])) {
    $bookable = 1;
  }
  else {
    $bookable = 0;
  }
  if (isset($_REQUEST['SM'])) {
    $sm = 1;
  }
  else {
    $sm = 0;
  }
  if ($id == 0) {
    $query = "INSERT INTO studios (studio, sm, bookable, AllocateInstanceID)
              VALUES (N'$studio',
              $sm,
              $bookable, ". getCurrentInstanceId().");
              SELECT SCOPE_IDENTITY() as computed";
    $results = sqlsrv_query($db, $query);
    $id = sql_last_insert_id($results);

  }
  else {
    $query = "UPDATE studios
              SET studio = N'$studio',
              sm = $sm,
              bookable = $bookable
              WHERE (id = $id) AND AllocateInstanceID = ". getCurrentInstanceId();

  sqlsrv_query($db, $query);
  }
      ?>

      <script type="text/javascript">
      $(function(){
        $.facebox.close();
        ShowStudioList ('<?php echo $id?>');
      })
      </script>
      <?php
  }
