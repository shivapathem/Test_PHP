<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/adminfunctions.php';
$intTypeID = $_REQUEST["id"];

$intGroupID = GetGroupIDFromRequestTypeID($intTypeID);
$arrUsersInGroup = GetStaffInLeaveGroup($intGroupID);

$arrUsersCanRequest = GetStaffCanRequestType($intTypeID);

echo '<table class="tablesmall staff">';
echo '<tr>';
echo '<th width="200px">';
echo 'Staff who can Request';
echo '</td>';
echo '<th width="200px">';
echo 'Staff who can\'t Request';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td width="200px" valign="top">';
echo '<div id="snorequest" style="overflow-x: hidden; overflow-y: scroll; width: 100%; height: 350px; position:relative">';
if (isset($arrUsersCanRequest) && !empty($arrUsersCanRequest)) { 
  echo '<table width="100%">';
  foreach ($arrUsersCanRequest as $strStaffLogin => $strName) {
    echo '<tr>';
    echo '<td class="handcursor staff-names-context-menu" onclick="javascript:RemoveRestricted(\''.$strStaffLogin.'\')";>';
    echo $strName;
    echo '</td>';
    echo '</tr>';
  }
  echo '</table>';
}  
else {
  echo 'There are no staff able to request this Type!';

}
echo '</div>';
echo '</td>';

echo '<td width="200px" valign="top">';
echo '<div id="srequest" style="overflow-x: hidden; overflow-y: scroll; width: 100%; height: 350px; position:relative">';  
echo '<table width="100%">';
foreach ($arrUsersInGroup as $strStaffLogin => $arrUser) {
  if (!isset($arrUsersCanRequest[$strStaffLogin])) {
    echo '<tr>';
    echo '<td class="handcursor" onclick="javascript:AddToRestricted(\''.$strStaffLogin.'\')";>';
    echo $arrUser['Name'];
    echo '</td>';
    echo '</tr>';
  }
}
echo '</table>';
echo '</div>';
echo '</td>';
echo '</tr>';



echo '</table>';


?>
<script type="text/javascript">
$(document).ready(function(){
  $("table.staff tr:odd").addClass("odd");
  $("table.staff tr:even").addClass("even");
	if ( $.cookie("snorequest") !== null ) {
	  $("#snorequest").scrollTop($.cookie("snorequest"));
  };
	if ( $.cookie("srequest") !== null ) {
	  $("#srequest").scrollTop($.cookie("srequest"));
  };          
})
$("#snorequest").on("scroll", function() {  
  $.cookie("snorequest", $("#snorequest").scrollTop());
});
$("#srequest").on("scroll", function() {  
  $.cookie("srequest", $("#srequest").scrollTop());
});

function AddToRestricted (login) {
  $.post("page-includes/admin/requestsaddremovepersonrestrictedtype.php", {
     login: login,
     typeid: <?php echo $intTypeID?>,
     action: 1
  },
  function(data,status){
    FillNames(<?php echo $intTypeID?>)
  }
  )
}

function RemoveRestricted (login) {
  $.post("page-includes/admin/requestsaddremovepersonrestrictedtype.php", {
     login: login,
     typeid: <?php echo $intTypeID?>,
     action: 0
  },
  function(data,status){
    FillNames(<?php echo $intTypeID?>)
  }
  )
}


$(function(){
  $.contextMenu({
    selector: '.staff-names-context-menu',
    //trigger: 'left',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
      //alert(id);
    },
    items: {
      "history": {
        name: "EFT",
        icon: "assign",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("id");
          $.post("page-includes/edits/dutyhistory.php", {
            id: dutyid
          },
          function(data,status){
           $.facebox(data);
          })
        }
      },
    },
  })
})

</script>