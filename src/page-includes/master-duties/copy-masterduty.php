<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$intID          =   trim($_REQUEST["id"]);
$intDutyType    =   trim($_REQUEST["dutytype"]);
$action         =   isset($_REQUEST["action"]) ? trim($_REQUEST["action"]): '';
$intStaffID     =   $_SESSION['user']['StaffID'];
$intUserID      =   isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID      =   $_SESSION['user']['AreaID'];
$pageid         =   3; 
$permissions    =   getUserRolePermissions($pageid);        // getting user roles and permissions
//check if we show the form
if ($permissions->cancreate == 1)
{
    if($action == 'createCopy')
    {
        $newDutyName        =   trim($_POST['newDutyName']);
        $copyDutyResultJson =   copyDutyAndJobDetails($newDutyName, $intID, $intDutyType);
        echo $copyDutyResultJson;
        die;
    }
    echo '<div style="width: 600px">';
    echo '<form id="copyDutyAndJobsForm" name="copyDutyAndJobsForm" >';
    echo '<table id="copyDuty" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="5">Copy Duty</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>New Duty Name</td>';
    echo '<td colspan="3"><input id="newDutyName" name="newDutyName" type="text" maxlength="50" minlength="1" class="valid" aria-invalid="false" ></td>';
    echo '</tr>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td></td>';
    echo '<td colspan="3"><input name="submit" type="submit" value="Create Duty"></input></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '<input type="hidden" name="id" id="id" value="'.$intID.'">';
    echo '<input type="hidden" name="intDutyType" id="intDutyType" value="'.$intDutyType.'">';
    echo '</form>';
    echo '</div>';
}else
{
    echo '<table id="copyDuty" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="5">Copy Duty</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<table>';
    echo '<tr>';
    echo '<th colspan="5">Current user is not allowed to copy duty. Please contact administrator. </th>';
    echo '</tr>';
    echo '</table>';
    echo '</table>';
}

?>

<script type="text/javascript">
$('#newDutyName').on('keyup', function(){
    let ControlName =   "#newDutyName";
    let Fieldname   =   'New Duty Name';
    let TextValid   =   TextTypeValidation(ControlName,Fieldname);
    if(TextValid == 0)
    {
        return false;
    }
});
$(document).ready(function(){

    $( "#copyDutyAndJobsForm" ).submit(function( event ) {
        var newDutyName     =   $( "#newDutyName" ).val();
        var dutyId          =   $( "#id" ).val();
        var intDutyType     =   $( "#intDutyType" ).val();
        var messageStr      =   '';
        if(newDutyName == '')
        {
            messageStr      +=  'Please enter the duty name\n';
        }
        $.ajax({
            url     :   "page-includes/master-duties/copy-masterduty.php",
            type    :   "POST",
            dataType:   "json",
            data    :   
            {
                    'action'        :   'createCopy',
                    'newDutyName'   :   newDutyName,
                    'id'            :   dutyId,
                    'dutytype'      :   intDutyType
            },
            success :   function(data)
            {
                if (data.returnCode == "1")
                {
                    if(intDutyType == "1")
                    {
                        ListMasterDuties(0, 0);//copy duty is not work for misc duties
                    }
                    $.facebox.close();
                }
                else
                {
                    customAlertByModel(data.returnMsg);
                }
            },
            error   :   function(x,e)
            {
                if (x.status==0)
                {
                    console.log('You are offline!!<br/> Please Check Your Network.');
                }else if(x.status==404)
                {
                    aleconsole.logrt('Requested URL not found.');
                }else if(x.status==500)
                {
                    console.log('Internal Server Error.');
                }else if(e=='parsererror')
                {
                    console.log('Error.<br/>Parsing JSON Request failed.');
                }else if(e=='timeout')
                {
                    console.log('Request Time out.');
                }else
                {
                    console.log('Unknow Error.<br/>'+x.responseText);
                }
            }
        });
        if(messageStr != '')
        {
            customAlertByModel('Warning:<br/>' + messageStr);
        }
        return false;
    });
});
</script>