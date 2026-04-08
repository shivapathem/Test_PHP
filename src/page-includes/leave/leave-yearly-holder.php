<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/userfunctions.php';
$strUser = GetUserLogon();

$arrStaff = GetStaffInMyAdminGroups($strUser);

echo '<div class="tableheadersmall" style="width:100%; height:50px">';
echo '<br><b>Please choose someone to view their Leave Requests</b>&nbsp;&nbsp;';
echo '<select class="chosen-select" size="1" name="ChooseUser" onchange="javascript:ShowUserLeave(value)";>';
echo '<option selected value="">-</option>';  
foreach ($arrStaff as $strLogin => $arrStafDetails) {
  if ($arrStafDetails['Admin'] == 0) {
    echo '<option value="'.$strLogin.'">'.$arrStafDetails['Name'].'</option>';
  }
}
echo '</select>';
echo '</div>';
echo '<div id="leaverecord" style="top:100px: left:0px;width:100%; min-height: 300px; position:relative; overflow:auto; overflow-y: hidden;"></div>';
?>
<script type="text/javascript">

$(document).ready(function() {
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "200px"
  });
})

function ShowUserLeave(user, year) {
  if (user != '') {
    $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-yearly.php',
        data: {
            'year': year,
            'user': user,
            'admin': 1,
        },
        success: function (data) {
            document.getElementById('content').style.pointerEvents = 'auto';
            $('#loader').hide();
            $('#leaverecord').html(data);
        },
        error:function (data) {
            alert('some error found in leave yearly call.');
        }
    });
  }       
}
</script>