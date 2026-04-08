<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$arrLeaveEmailsToSend = '';
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/userfunctions.php';
$strUser = GetUserLogon();

$openAccordionLogin = isset($_POST['openAccordionLogin']) ? $_POST['openAccordionLogin'] : null;

echo '<div class="tableheadersmall medtextboldcentre" style="width: 800px">
       <br>Below are the people who have had leave approved but have not yet been notified.<br>
       Click on a name to send email to that person<br><br>
       </div><br>';

$arrLeaveEmailsToSend = GetEmailsToSend($strUser);

$LeaveAdminGroupsData = GetLeaveAdminGroups($strUser);
$arrLeaveAdmingrps = [];
if (!empty($LeaveAdminGroupsData)) {
  foreach ($LeaveAdminGroupsData as $value) {
    array_push($arrLeaveAdmingrps, $value['LeaveGroupID']);
  }
}

if (isset($arrLeaveEmailsToSend) && !empty($arrLeaveEmailsToSend)) {
  echo '<div id="accordionemails">';
  foreach ($arrLeaveEmailsToSend as $strLogin => $arrPerson) {

    if (!empty(array_intersect(array_unique($arrPerson['grGroupID']), $arrLeaveAdmingrps))) {
      $intGroupID = $arrPerson['GroupID'];
      echo '<h3>';
      echo $arrPerson['Name'];
      echo '</h3>';
      echo '<div data-login="'.$strLogin.'">';
      echo '<table class="tablesmalltidy">';
      echo '<tr>';
      echo '<th style="display:none">';
      echo '';
      echo '</th>';
      echo '<th width="150px">';
      echo 'Date';
      echo '</th>';
      echo '<th width="300px">';
      echo 'Leave Type';
      echo '</th>';
      echo '<th width="200px">';
      echo 'Leave Category';
      echo '</th>';
      echo '<th width="100px">';
      echo 'Duration';
      echo '</th>'; 
      echo '<th width="100px">';
      echo 'Send Emails';
      echo '</th>';
      echo '<th class="handcursor" width="30px" onclick="javascript:sendleaveemail(\'' . $strLogin . '\')">';
      echo '<img border="0" src="../images/Email.png" width="25px" height="25px">';
      echo '</th>';
      echo '</tr>';
      if (isset($arrPerson['Dates']) && !empty($arrPerson['Dates'])) {
        foreach ($arrPerson['Dates'] as $strCurrDate => $strLeaveType) {
          if (in_array($strLeaveType['sGroupID'], $arrLeaveAdmingrps)) {
            echo '<tr>';
            echo '<td style="display:none">';
            echo '<input type="checkbox" name="emailNotification"  id="' . $strCurrDate . '" data-netlogin="' . $strLogin . '" checked="checked" disabled>';
            echo '</td>';
            echo '<td>';
            echo date("jS M Y", strtotime($strCurrDate));
            echo '</td>';
            echo '<td>';
            echo $strLeaveType['desc'];
            echo '</td>';
            echo '<td>';
            $leaveCategoryDisplay = $strLeaveType['LeaveCategoryAmounts'] == 0
              ? implode(', ', $strLeaveType['LeaveCategories']) . ' (OFF Leave)'
              : implode(', ', $strLeaveType['LeaveCategories']);
            echo $leaveCategoryDisplay;
            echo '</td>';
            echo '<td>';
            echo $strLeaveType['LeaveCategoryAmounts'];
            echo '</td>';
            echo '<td colspan="2">';
            $sentOptionValue = $strLeaveType['sentOptionValue'];
            echo '<select name="emailDropdown" class="emailDropdown">';
            echo '<option value="0"' . ($sentOptionValue == 0 ? ' selected' : '') . '>Automatic Send</option>';
            echo '<option value="1"' . ($sentOptionValue == 1 ? ' selected' : '') . '>Delayed Send</option>';
            echo '<option value="2"' . ($sentOptionValue == 2 ? ' selected' : '') . '>Never Send</option>';
            echo '</select>';
            echo '</td>';
            echo '</tr>';
          }
        }
      }
      echo '</table>';
      echo '</div>';
    }
  }
  echo '</div>';
}
?>
<script type="text/javascript">
  $(document).ready(function () {
    $(function () {
      $("#accordionemails").accordion({
        heightStyle: "content",
        collapsible: true,
      });
      var openAccordionLogin = '<?php echo $openAccordionLogin; ?>';
      if (openAccordionLogin) {
        $("#accordionemails h3").each(function (index) {
          var login = $(this).next("div").data("login");
          if (login === openAccordionLogin) {
            $("#accordionemails").accordion("option", "active", index);
          }
        });
      }
    });

    $('select[name="emailDropdown"]').change(function () {
      var leaveDate = $(this).closest('tr').find('input[type="checkbox"]').attr('id');
      var Netlogin = $(this).closest('tr').find('input[type="checkbox"]').attr('data-netlogin');
      var checkedstatus = $(this).closest('tr').find('input[type="checkbox"]').prop("checked") ? 0 : 1;
      var sentOption = $(this).closest('tr').find('select[name="emailDropdown"]').val();
      updateSent(leaveDate, checkedstatus, sentOption, Netlogin);
    });

  })

  function updateSent(leaveDate, checkedstatus, sentOption, Netlogin) {
    $.ajax({
      type: 'POST',
      url: 'page-includes/leave/leave-update-sentemail.php',
      data: {
        'leaveDate': leaveDate,
        'checkedstatus': checkedstatus,
        'sentOption': sentOption,
        'Netlogin': Netlogin
      },
      success: function (data) {
      },
      error: function (data) {
        console.log('some error occurred.');
      }
    });
  }

  function sendleaveemail(Login) {
    $('*').qtip('hide');
    $.post("page-includes/leave/leave-sendemails.php", {
      login: Login,
      openAccordionLogin:Login
    },
      function () {
        GetTabContent(6, Login);
      }
    )
  };

</script>

<?php

die;




$db = OpenDatabase();
$query = " SELECT
        LeaveApplications.StaffNumber                    ,
        Staff.Login                                      ,
        Staff.Forename + N' ' + Staff.Surname AS FullName,
        leave_types.GroupID                              ,
        LeaveRequestGroups.Description
FROM
        LeaveRequestGroups
INNER JOIN
        leave_types
ON
        LeaveRequestGroups.id                 = leave_types.GroupID
AND     LeaveRequestGroups.AllocateInstanceID = leave_types.AllocateInstanceID
INNER JOIN
        LeaveApplications
INNER JOIN
        Staff
ON
        LeaveApplications.StaffNumber = Staff.StaffNumber
ON
        leave_types.id                       = LeaveApplications.LeaveTypesID
AND     LeaveApplications.AllocateInstanceID = Staff.AllocateInstanceID
  AND leave_types.AllocateInstanceID=LeaveApplications.AllocateInstanceID
WHERE
        (
                LeaveApplications.Sent = 0)
AND     (
                LeaveApplications.Deleted = 0)
AND     (
                LeaveApplications.Approved = 1)
GROUP BY
        LeaveApplications.StaffNumber        ,
        Staff.Login                          ,
        Staff.Forename + N' ' + Staff.Surname,
        leave_types.GroupID                  ,
        LeaveRequestGroups.Description
HAVING
        (
                leave_types.GroupID = $intGroupID)";

echo $query;
$request = sqlsrv_query($db, $query);
echo '<table class="tablesmalltidy" width="400px">';
echo '<tr>';
echo '<th>Person</th>';
echo '</tr>';
while ($row = sqlsrv_fetch_array($request)) {
  echo '<tr>';
  echo '<td class="handcursor" onclick="javascript:sendleaveemail(\'' . $row['StaffNumber'] . '\')">' . $row['FullName'] . '</td>';
  echo '</tr>';
}
echo '</table>';

?>
<script type="text/javascript">

  function sendleaveemail(StaffNumber) {
    $('*').qtip('hide');
    $.post("page-includes/ajax-calls/leavesendemails.php", {
      staffnumber: StaffNumber,
    },
      function () {
        loadsendemails();
      }
    )
  };
</script>