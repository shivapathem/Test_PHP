<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$intID = $_REQUEST['id'];
$arrHelper = GetAllocateHelpDetail ($intID);
if(!empty($arrHelper)){
    $helperDescription = $arrHelper['Description'];
    $helperContent = $arrHelper['Content'];
    if (strpos($helperContent, 'â€“') !== false) {
        $helperContent = str_replace('â€“', '–', $helperContent);
    }
    if (strpos($helperContent, 'Ã¢â‚¬â€œ') !== false) {
        $helperContent = str_replace('Ã¢â‚¬â€œ', '-', $helperContent);
    }
    if (strpos($helperContent, 'Ã‚') !== false) {
        $helperContent = str_replace('Ã‚', '', $helperContent);
    }
    
    if (strpos($helperDescription, 'â€“') !== false) {
        $helperDescription = str_replace('â€“', '–', $helperDescription);
    }
    if (strpos($helperDescription, 'Ã¢â‚¬â€œ') !== false) {
        $helperDescription = str_replace('Ã¢â‚¬â€œ', '-', $helperDescription);
    }
    if (strpos($helperDescription, 'Ã‚') !== false) {
        $helperDescription = str_replace('Ã‚', '', $helperDescription);
    }
  }
  else{
    $helperDescription = '';
    $helperContent = '';
  }
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intSysAdmin = GetIsSysAdmin($strUser);

echo '<div style="width:80%; margin:0 auto; padding: 20px;  position:relative;" class="LightGrey">';
echo '<div class="allocate-help">';
echo '<div style="width: auto;">';
echo '<span>Allocate Help for</span><h1 style="display: inline;" > \''.$helperDescription.'\'</h1>';
echo '</div>';
echo '</div>';
if ($intSysAdmin == 1) {
  echo '<div class="allocate-help-icon">';
  echo '<img border="0" src="images/edit.gif" width="16" height="16" Style="cursor: pointer" onclick="javascript:EditHelper('.$intID.')";>&nbsp;&nbsp;&nbsp;';
  echo '<img border="0" src="images/add.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:EditHelper(0)";>&nbsp;&nbsp;&nbsp;';
  echo '<img border="0" src="images/delete.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:DeleteHelper('.$intID.')";>';
  echo '</div>';
};
echo '<br>'.$helperContent;   
echo '</div>';
?>

<div id="dialog-delete-helper" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this Helper?</p>
</div>


<script type="text/javascript">
function EditHelper(id) {
  $.post("page-includes/admin/system-allocate-help.php", {
    id: id  
  },
  function(data,status){
    {
      $('#content').html(data);
    }
  });
}

function DeleteHelper(id) {
  $( "#dialog-delete-helper" ).dialog(
  {
    width: 600, 
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/admin/delete-allocate-help.php", {
        id: id
      },
      function(data,status){
        FillMenu();
        location = 'index.php';
      });
      },
      "No": function() {
       $( this ).dialog( "close" );
      },
      }
    }
  );
}
</script>