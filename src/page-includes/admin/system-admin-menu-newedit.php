<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$intID = $_REQUEST["id"];

if (isset($_REQUEST['Update'])) {
  $strDescription = $_REQUEST["Description"];
  $strURL = $_REQUEST["URL"];
  if ($intID == 0) {
    $query = "INSERT INTO   HomeMenuShortcuts
                            (Description, URL)
              VALUES        (N'$strDescription', N'$strURL')";
                     
    sqlsrv_query($db, $query);
    echo $query;
  }
  else {
   $query = "UPDATE       HomeMenuShortcuts
             SET          Description = N'$strDescription', URL = N'$strURL'
             WHERE        (ID = $intID)";
  
    sqlsrv_query($db, $query);  
  } 
}
else {

  if ($intID == 0) {
    $strDescription = '';
    $strURL = '';  
  }
  else {
    $query = "SELECT        ID, Description, URL
              FROM          HomeMenuShortcuts
              WHERE         (ID = $intID)";

    $rsoff = sqlsrv_query($db, $query);
    $row = sqlsrv_fetch_array($rsoff);
    $strDescription = $row['Description'];
    $strURL = $row['URL'];
  }
  
  echo '<form id="formmenuedit">';
  echo '<table class="tablesmall" width="400px">';
  echo '<tr height="40px">';
  echo '<th colspan="2">';
  echo 'Edit Text Desctiption'; 
  echo '</th>';
  echo '</tr>';
  echo '<tr>';
  echo '<th>Description</th>';
  echo '<td><input type="text" name="Description" size="20" value="'.$strDescription.'"></td>';
  echo '</tr>';
  
  echo '<th>URL</th>';
  echo '<td><input type="text" name="URL" size="50" value="'.$strURL.'"></td>';
  echo '</tr>';
      
  echo '<tr>';
  echo '<td colspan="2" align="center"><input type="submit" value="update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '</form> ';
  
  echo '</td>'; 
  echo '</tr>';
  echo '</table>';

  
   
?>

<script type="text/javascript">

$('#formmenuedit').validate({
  rules: {  
    Description: {
      required: true,
      maxlength: 50
    },     
    URL: {
      required: true,
      maxlength: 100
    },
  },
  errorPlacement: function(){
   return false;
  },
  submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/admin/system-admin-menu-newedit.php', data:$('#formmenuedit').serialize(), success: function(data) {
        $.facebox.close();
        FillMenu();        
        ShowHomeShortcuts(); 
        
                  
      }});
  } 
});


</script>



<?php
}

function drawkey() {



  echo '<table class= "tablesmalltidy" width="300px">';
  echo '<tr>';
  echo '<th colspan="2">Working days require a code.<br>The explanations are below</th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>-</td>';
  echo '<td>A space</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>@</td>';
  echo '<td>Any text character</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>~</td>';
  echo '<td>Any numeric character</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>ABCDE...1234....</td>';
  echo '<td>Literal match (text or number)</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>!</td>';
  echo '<td>No further characters</td>';
  echo '</tr>';  
  echo '<tr>';
  echo '<td colspan="2">All characters are case insensitive</td>';
  echo '</tr>';
  echo '</table>';


}

?>
