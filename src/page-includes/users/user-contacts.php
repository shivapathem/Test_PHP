<?php
date_default_timezone_set('UTC');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once 'process/classUserSetup.php';

$setupObj = new classUserSetup();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
if (isset($arrUsersTeamdata["Teams"]) && empty($arrUsersTeamdata["Teams"])) {
    echo 'Access Denied';die;
}
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$pdo = OpenDBLinkA7();

if (isset($_POST['submit'])) {
    $strTelephone = $_POST['telephone'];
    if ($strTelephone == '') {
        $strTelephone = "NULL";
    } else {
        $strTelephone = "'" . $strTelephone . "'";
    }

    $strEmail = $_POST['email'];
    if ($strEmail == '') {
        $strEmail = "NULL";
    } else {
        $strEmail = "'" . $strEmail . "'";
    }
    // Set the statement to use
    $sql = "UPDATE UserDetails SET UD_PersonalPhone = $strTelephone, UD_ExternalEmail = $strEmail WHERE (UD_NetLogin = '$strUser')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

// Get the current option
$sql = "SELECT UD_DisplayName as FullName, UD_NetLogin as Login, UD_PersonalPhone as UserPhone, UD_ExternalEmail as PersonalEmail, UD_InternalEmail as BBCEmail,UD_OfficePhone as BBCphone FROM UserDetails WHERE (UD_NetLogin = ?)";
$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1, $strUser, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$strFullName = $row['FullName'];
$strTelephone = $row['UserPhone'];
$strEmail = $row['PersonalEmail'];

$BBCEmail = $row['BBCEmail'];
$BBCphone = $row['BBCphone'];
echo '<h1 class="sr-only">My Contact Details</h1>';
if (is_null($row)) {
    echo '<table class="tablesmall" width="100%">';
    echo '<tr>';
    echo '<th colspan="2"><br>Because you are not assigned to a scheduling team
          <br>in Allocate it is not possible for you to provide contact details.
          <br><br></th>';
    echo '</tr>';
    echo '</table>';
} else {
    echo '<form id="updatecontacts">';
    echo '<table class="tablesmall" width="100%">';
    echo '<tr>';
    echo '<th colspan="2"><br>You may provide Telephone numbers and eMail addresses here so we may contact you.<br>
          Only Shift Leaders, Schedulers and Managers can see this information.
          <br><br></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<th>';
    echo 'BBC Telephone<br>';
    echo '</th>';
    echo '<td>';
    echo '<textarea id="bbctelephone" name="bbctelephone" rows="5" cols="50" readonly>';
    echo $BBCphone;
    echo '</textarea>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th>';
    echo 'BBC Email<br>';
    echo '</th>';
    echo '<td>';
    echo '<textarea id="bbcemail" name="bbcemail" rows="5" cols="50" readonly>';
    echo $BBCEmail;
    echo '</textarea>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th>';
    echo 'NON BBC Telephone<br>';
    echo '</th>';
    echo '<td>';
    echo '<textarea id="telephone" name="telephone" rows="5" cols="50">';
    echo $strTelephone;
    echo '</textarea>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th>';
    echo 'NON BBC EMail<br>';
    echo '</th>';
    echo '<td>';
    echo '<textarea id="email" name="email" rows="5" cols="50">';
    echo $strEmail;
    echo '</textarea>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td></td>';
    echo '<td><input name="submit" type="submit" value="Submit"></input></td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';
    ?>
    <div id="dialog-update-contact" title="Information!" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Thank You<br>Your
            information has been updated.</p>
    </div>

    <script type="text/javascript">
        $('document').ready(function () {
            $('#updatecontacts').validate({
                submitHandler: function (form) {
                    $.ajax({
                        type: 'POST',
                        url: 'page-includes/users/user-contacts.php',
                        data: $('#updatecontacts').serialize(),
                        success: function (data) {
                            $("#dialog-update-contact").dialog(
                                {
                                    buttons: {
                                        "OK": function () {
                                            $(this).dialog("close");
                                        },
                                    }
                                }
                            );
                            ShowUserContacts();
                        }
                    });
                }
            })
        });
    </script>

<?php
}
?>