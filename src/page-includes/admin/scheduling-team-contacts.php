<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once 'process/editNonBBCEmail.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intTeamID = $_POST['teamid'];
$strTeamname = $_POST['teamname'];
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);


/* new data  team wise*/
if (($arrUsersTeamdata['Teams'][$intTeamID]["ShiftLeader"] == 1) || ($arrUsersTeamdata['Teams'][$intTeamID]["Scheduler"] == 1) || ($arrUsersTeamdata['Teams'][$intTeamID]["Manager"] == 1) || ($arrUsersTeamdata["isAdmin"] == 1) || ($intSysAdmin == 1) || ($arrUsersTeamdata["DivisionalAdmin"] == 1)) {
$commonDBObj = new classCommonDBFunctions();
$arrStaff = json_decode($commonDBObj->getStaffContactDetailsByTeamID($intTeamID), true);

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h2 aria-label="Staff Contacts for '.$strTeamname.'">Staff Contacts for \''.$strTeamname.'\'.</h2><br>';
echo '</div><br>';
echo '<table class="tablesmall compact stripe" id="teamcontacts'.$intTeamID.'" min-width="100%">';
echo '<thead>';
echo '<tr><th>Name</th>'; 
echo '<th>BBC Telephone</th><th>BBC Email </th><th>Non BBC Telephone</th>';   
echo '<th>Non BBC Email</th>'; 
echo '</tr>'; 
echo '</thead>';
echo '<tbody>';
foreach ($arrStaff as $strLogon => $arrPerson) {
  $name = $arrPerson['FullName'];

  if($arrPerson['DisplayName']) {
    $name = $arrPerson['DisplayName'];
  } elseif ($arrPerson['PreferredForename']) {
    $name = $arrPerson['PreferredForename'].' '.$arrPerson['Surname'];
  }

  $arrPerson['OfficeMobile'] = $arrPerson['OfficeMobile'] ?? '';
  $arrPerson['NonBBCPhNo'] = $arrPerson['NonBBCPhNo'] ?? '';
  $arrPerson['NonBBCEmail'] = $arrPerson['NonBBCEmail'] ?? '';
  $arrPerson['BBCEmail'] = $arrPerson['BBCEmail'] ?? '';

  if($arrPerson['scheduledType'] == 1 && ($name || $arrPerson['BBCEmail'])) {
  echo '<tr>';  

  echo '<td>'.$name.'</td>';
  
  echo '<td>'.nl2br($arrPerson['OfficeMobile']).'</td>';
  
  echo '<td>'.nl2br($arrPerson['BBCEmail']).'</td>';
  
  echo '<td>'.nl2br($arrPerson['NonBBCPhNo']).'</td>';
  
  echo '<td>'.nl2br($arrPerson['NonBBCEmail']).'</td>'; 
  echo '</tr>';
  }
}
echo '</tbody>';
echo '</table>'; 

// ###################################################### END Put the groups in the div
} else {
  echo 'Access Denied'; die;
}

?>
<div id="myModal" class="modal" style="display: none;">
            <!-- Modal content -->
            <div class="modal-content w-50"><span class="closebox">×</span>
                <!--Model content-->
                <div>

                    <form id="editNonBBCEmailfrm" method="post">
                        <table id="rotanewedittable" class="smalltable bluetable" width="100%">
                            <thead>
                            <tr>
                                <th colspan="5" id="title"></th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr>
                                <td class="lightblue" width="15%"><label for="sortcode">Non BBC Telephone<span
                                                id="Addlabelstartdate" class="required">*</span> </label></td>
                                <td width="35%">
                                    <input id="nonBBCPhone" name="nonBBCPhone" type="text" size="40" value=""
                                           data-divisionnameid="9" maxlength="10"><br/>
                                </td>
                            </tr>

                            <tr>
                            <td class="lightblue" width="15%"><label for="sortcode">Non BBC Email<span
                                                id="Addlabelstartdate" class="required">*</span> </label></td>
                                <td width="35%">
                                    <input id="nonBBCEmail" name="nonBBCEmail" type="text" size="40" value=""
                                           data-divisionnameid="9"><br/>
                                </td>
                            </tr>
                            <tr>
                                <td width="15%"></td>
                                <td width="35%">
                                    <input name="js_saveMail" id="js_saveMail" type="button" value="save">
                                    <input name="js_staffId" id="js_staffId" type="hidden" value="">
                                           
                                    <input name="js_actionbutton" id="js_actionbutton" type="hidden" value="">
                                    <input name="teamId" id="teamId" type="hidden" value="<?php echo $intTeamID;?>">
                                    <input name="teamname" id="teamname" type="hidden" value="<?php echo $strTeamname;?>">
                                 </td>
                            </tr>

                            </tbody>
                        </table>
                    </form>
                </div>
                <!--Model content closes-->
<script type="text/javascript">
$(document).ready( function () {
  var table = $("#teamcontacts<?php echo $intTeamID?>").DataTable({
    paging: false,
    scrollY: 2000,
    info:     false,
    stateSave: true,
    deferRender: true,
    scrollCollapse: true,

    "initComplete": function( settings, json ) {
        ResizeTeamcontacts();
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]);
  

  if ( $.cookie("teamcontacts<?php echo $intTeamID?>") !== null ) {
    scrollPos = $.cookie("teamcontacts<?php echo $intTeamID?>");
    $('#teamcontacts<?php echo $intTeamID?>').closest('.dataTables_scrollBody').scrollTop(scrollPos);      
  };
  
})


$(window).resize(function() {
  ResizeTeamcontacts();
})   

function ResizeTeamcontacts () {
	if($("#teamcontacts<?php echo $intTeamID?>").length)
	{
	  var offset = ($("#teamcontacts<?php echo $intTeamID?>").offset().top);
	  var windowheight = $(window).height() - offset - 50;
	  $('.dataTables_scrollBody').height((windowheight));
	  $('#teamcontacts<?php echo $intTeamID?>').DataTable().columns.adjust().draw();
	}
}  

$('#teamcontacts<?php echo $intTeamID?>').closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $('#teamcontacts<?php echo $intTeamID?>').closest('.dataTables_scrollBody').scrollTop();
  $.cookie("teamcontacts<?php echo $intTeamID?>", currpos);
});  


//$('#js_saveMail').click(function(){
  $(document).on('click', "#js_saveMail", function () {
  var phone = $('#nonBBCPhone').val();
        var email = $('#nonBBCEmail').val();

        if(phone== ''){
          alert("Please Enter Non BBC Telephone")
          return false;
        }
        if(email== ''){
          alert("Please Enter Non BBC Email")
          return false;
        }
        if(TelephoneNo(phone)==false){
         alert("Please Enter only numbers in Non BBC Telephone")
          return false;
        }

        if(IsEmail(email)==false){
         alert("Please Enter valid Non BBC Email")
          return false;
        }
       
        //saveNonBBCMail
        saveNonBBCMail();
});

function IsEmail(email) {
  var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  if(!regex.test(email)) {
    return false;
  }else{
    return true;
  }
}

function TelephoneNo(phone) {
  var regex = /^([0-9\-\(\)\s]+.)+$/;
  if(!regex.test(phone)) {
    return false;
  }else{
    return true;
  }
}

let modal = document.getElementById("myModal");
$(document).on('click', "#editNonBBCMail", function () {
        selectEmail($(this).data("editnonbbcmailid"));
       $("#js_staffId").val($(this).data("editnonbbcmailid"));
        modal.style.display = "block";
    });

    $(".closebox").on('click', function () {
        resetDivisonForm();
        modal.style.display = "none";
    });
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {

        if (event.target == modal) {
            resetDivisonForm();
            modal.style.display = "none";
        }
    }

	
	/* reset the form validation*/
  function resetDivisonForm() {
     $("#nonBBCEmail").val('');
	 $("#nonBBCPhone").val('');
  }
  
  function selectEmail(staffid) {
      
      $.ajax({
          url: 'page-includes/admin/process/editNonBBCEmail.php',
          dataType: "json",
          type: 'POST',
          data: {staffId: staffid, action: 'openmodal'},
          success: function (data) {
            
              if (data.status == 'success') {
                $("#nonBBCEmail").val(data.ExternalEmail);
                $("#nonBBCPhone").val(data.AltTelephone);
                 
              }
          }
      });
  }

  function saveNonBBCMail() {

      var nonBBCphone = $('#nonBBCPhone').val();
      var nonBBCemail = $('#nonBBCEmail').val();
      var staffid = $("#js_staffId").val();
    $.ajax({
        url: 'page-includes/admin/process/editNonBBCEmail.php',
        dataType: "json",
        type: 'POST',
        data: {nonBBCPhone:nonBBCphone,nonBBCEmail:nonBBCemail,staffId:staffid,action: 'savemail'},
        success: function (data) {
          
            if (data.status == 'success') {
                 alert("Email and Telephone updated succesfully");
              modal.style.display = "none";
              var teamID = $('#teamId').val();
              var teamName = $('#teamname').val();
              ShowTeamContacts(teamID,teamName);
            }
        }
    });
}     
</script>   