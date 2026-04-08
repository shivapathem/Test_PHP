<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$id = 0;

if (isset($_POST['fav'])) {
  $fav = $_POST['fav'];

  $query = "select * from user_favourites where description='$fav' and Login='$strUser'";
  $groups = sqlsrv_query($db, $query);
  $row = sqlsrv_fetch_array($groups);

  if($row['ID']==''){
  $query = "INSERT
            INTO  user_favourites(description, login)
            VALUES (N'$fav',
            N'$strUser')";
  sqlsrv_query($db, $query);

  $result_id = sqlsrv_query($db, 'SELECT SCOPE_IDENTITY() as computed');
  $row = sqlsrv_fetch_array($result_id);
  $id = $row['computed'];
  }else{

    $id = '000';
  }

?>

<script type="text/javascript">
  $('document').ready(function(){
    $.facebox.close();
    var grpId = <?php echo $id?>;
    if(grpId=='000'){
      alert("Group already exists");
    }
    
    ShowUserGroups(<?php echo $id?>)
  });
</script>


<?php  
  
  
}
else {
  echo '<div id="usergroupaddedit">';
  echo '<form id="newprefform" method="POST" action="page-includes/users/user-groups-add-new.php">';
  echo '<table class="smalltable" width="100%">';
  echo '<tr>';
  echo '<td colspan="2" class="tableheadersmall medtextboldcentre">';
  echo '<br>Add a New Favourites Group<br><br>'; 
  
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall" valign="top">Description</td>';
  echo '<td class="lightcell smalltext"><input type="text" id="fav" name="fav" size="20"></td>';
  echo '</tr>';

  echo '<td class="lightcell smalltext">&nbsp;</td>';
  echo '<td class="lightcell smalltext"><input type="submit" value="update" name="Add"></td>';
  echo '</tr>';
  echo '</table>';
  echo '</form> ';
  echo '</div>';
?>

<script type="text/javascript">
  $('document').ready(function(){
    $('#newprefform').validate({
      rules:{
        "fav":{
          required:true,
          //date: true,
          }
        },
        submitHandler: function(form){
          $(form).ajaxSubmit({
            success: function(data) {
              $('#usergroupaddedit').html(data);
            }
          });
        }
     })
  });
</script>

<?php
}
?>