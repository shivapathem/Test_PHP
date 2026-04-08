<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/genericfunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/common/classCommonDBFunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$commonObj = new classCommonDBFunctions();
$isDivisionalAdmin = $commonObj->UserIsDivAdmin($sessUserId);
$arrUserLeaveSetting=   json_decode($commonObj->userLeaveRequestByNetLogin($strUser, 0),true);

if (isset( $_SESSION['user']['SysAdmin'] ) && $_SESSION['user']['SysAdmin'] == 1){
  $intSysAdmin = 1;
} else {
  $intSysAdmin = 0;
}
// Get the groups (if any) this person is in....)
$arrUserLeaveRequestGroups = GetAdminLeaveRequestGroupsFromLogin($strUser,$isDivisionalAdmin);
$htmlgroupcontecnt = '';
$htmlgroupcontecnt .= '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
$htmlgroupcontecnt .= '<br><h1 style="text-align: center;">Leave and Request Groups</h1><br>Click on a row to Highlight it and then choose an option from the Tabs.<br>Double-Click to edit the  Group\'s default settings.<br><br>';

if (($intSysAdmin == 1) || ($isDivisionalAdmin == 1)) {
  $htmlgroupcontecnt .= '<div class="DutyCellBottomLeft" onclick="javascript:EditLeaveGroup(0)";>';
  $htmlgroupcontecnt .= '<table><tr><td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
  $htmlgroupcontecnt .= '<td><img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
  $htmlgroupcontecnt .= '</td></tr>';
  $htmlgroupcontecnt .= '</table></div>';
}
echo '</div>';
$htmlgroupcontecnt .= '</div>';
$htmlgroupcontecnt .= '<table class="tablesmall compact stripe width100Percent" id="leavegroupslist" min-width="100%">';
$htmlgroupcontecnt .= '<thead>';
$htmlgroupcontecnt .= '<tr>';
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap">';
$htmlgroupcontecnt .= 'Area';
$htmlgroupcontecnt .= '</th>';
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap">';
$htmlgroupcontecnt .= 'Description';
$htmlgroupcontecnt .= '</th>'; 
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap" title="The number by which the leave credit is divided to calculate the number of clicks available">';
$htmlgroupcontecnt .= 'Hours Per Day';
$htmlgroupcontecnt .= '</th>';
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap" title="Allows users to send an Email on completion of requests entered">';
$htmlgroupcontecnt .= 'Collate Leave Requests';
$htmlgroupcontecnt .= '</th>'; 
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap" title="Allows people to put themselves in a waiting list for leave">';
$htmlgroupcontecnt .= 'Allow Leave Requests<br>over Limit';
$htmlgroupcontecnt .= '</th>'; 
$htmlgroupcontecnt .= '<th  class="leaverequestgrupWidthnowrap" title="Allows people to continue to request even above their summer leave amount providing they have enough leave left. These are held in a separate list until the date defined in config when the Summer Leave restriction is lifted">';
$htmlgroupcontecnt .= 'Allow Summer<br>Leave Requests<br>Over Limit';
$htmlgroupcontecnt .= '</th>'; 
/* Allow Part Days of Leave Request - Start */
$htmlgroupcontecnt .= '<th  class="leaverequestgrupWidthnowrap" title="Allow part days of leave request">';
$htmlgroupcontecnt .= 'Allow Part<br>Days of Leave<br>Requests';
$htmlgroupcontecnt .= '</th>';
/* Allow Part Days of Leave Request - End */
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap" title="The number of additional requests a person can make despite having reached their limit. This is useful in 2 situations: Firstly, for teams who are requiring users to click on all days away from work including days off. Secondly, for teams who wish to allow their users flexibility to enter their names into the waiting list for a number of days.">';
$htmlgroupcontecnt .= 'Extra Leave Requests';
$htmlgroupcontecnt .= '</th>';
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap" title="Number of Monthly Requests allowed per month for a Full-Time person. Can be flexible across the year for the Group. Can also be flexible by person by changing their Grouped Requests % which is found in the Staff & Percentages Tab.">';
$htmlgroupcontecnt .= 'Monthly Requests Allowed';
$htmlgroupcontecnt .= '</th>';
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap" title="Number of Requests allowed per Year.">';
$htmlgroupcontecnt .= 'Annual Requests Allowed';
$htmlgroupcontecnt .= '</th>';
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap" title="This is also the address that replies will go to when sent from the user.">';
$htmlgroupcontecnt .= 'Send emails From';
$htmlgroupcontecnt .= '</th>';
$htmlgroupcontecnt .= '<th class="leaverequestgrupWidthnowrap">';
$htmlgroupcontecnt .= 'Send emails Copies To';
$htmlgroupcontecnt .= '</th>';
$htmlgroupcontecnt .= '</tr>'; 
$htmlgroupcontecnt .= '</thead>';
$htmlgroupcontecnt .= '<tbody>';

foreach ($arrUserLeaveRequestGroups as $intGroupID => $arrUserLeaveRequestGroup) {
  $pdlClickAllow = '';	
  if ($arrUserLeaveRequestGroup['Admin'] == 2) {
    $htmlgroupcontecnt .= '<tr class="handcursor" id='.$intGroupID.' ondblclick=\'javascript:EditLeaveGroup('.$intGroupID.')\'>';
  }
  else {
    $htmlgroupcontecnt .= '<tr class="handcursor" id='.$intGroupID.'>';  
  }
  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap">';
  $htmlgroupcontecnt .= $arrUserLeaveRequestGroup['DivisionName'];
  $htmlgroupcontecnt .= '</td>';
  
  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap leaverequestgrupTableDescription">'; 
  $htmlgroupcontecnt .= $arrUserLeaveRequestGroup['Description'];
  if ($arrUserLeaveRequestGroup['Admin'] != 2) {  
    $htmlgroupcontecnt .= '&nbsp;&circledR;';
  } 
  $htmlgroupcontecnt .= '</td>';
 
  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap">';   
  $htmlgroupcontecnt .= $arrUserLeaveRequestGroup['HoursPerLeaveDay'].' Hours';
  $htmlgroupcontecnt .= '</td>';
 
  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap" align="center">';
  $htmlgroupcontecnt .= '<span class="customdateSort">'.$arrUserLeaveRequestGroup['AllowEmails'].'</span>';
  if ($arrUserLeaveRequestGroup['AllowEmails'] == 1) {
     $htmlgroupcontecnt .= '<img border="0" src="images/green_tick.png" width="16" height="16">';
  }
  else {
     $htmlgroupcontecnt .= '<img border="0" src="images/red_cross.png" width="16" height="16">';    
  }   
  $htmlgroupcontecnt .= '</td>';
  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap" align="center">';
  $htmlgroupcontecnt .= '<span class="customdateSort">'.$arrUserLeaveRequestGroup['ShowLeaveOverLimit'].'</span>';
  if ($arrUserLeaveRequestGroup['ShowLeaveOverLimit'] == 1) {
    $htmlgroupcontecnt .= '<img border="0" src="images/green_tick.png" width="16" height="16">';
  }
  else {
    $htmlgroupcontecnt .= '<img border="0" src="images/red_cross.png" width="16" height="16">';    
  }   
  $htmlgroupcontecnt .= '</td>';
  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap" align="center">';
  $htmlgroupcontecnt .= '<span class="customdateSort">'.$arrUserLeaveRequestGroup['SummerLeaveOverLimit'].'</span>';
  
  if ($arrUserLeaveRequestGroup['SummerLeaveOverLimit'] == 1) {
    $htmlgroupcontecnt .= '<img border="0" src="images/green_tick.png" width="16" height="16">';
  }
  else {
    $htmlgroupcontecnt .= '<img border="0" src="images/red_cross.png" width="16" height="16">';    
  }   
  $htmlgroupcontecnt .= '</td>';
  
  /*Allow Part Days of Leave Request - Start */
  $leaveGoupIds = $arrUserLeaveRequestGroup['ID'];
  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap" align="center">';
  $htmlgroupcontecnt .= '<span class="customdateSort">'.$arrUserLeaveRequestGroup['IsPartDayLeaveAllowed'].'</span>';
  
  $pdlTdContent = '<div '.$pdlClickAllow.'><img id="'."red$leaveGoupIds".'" border="0" src="images/red_cross.png" width="16" height="16"></div>'; 
  if ($arrUserLeaveRequestGroup['IsPartDayLeaveAllowed'] == 1) {
	$pdlTdContent = '<div '.$pdlClickAllow.'><img id="'."green$leaveGoupIds".'" border="0" src="images/green_tick.png" width="16" height="16"></div>';
  }
  $htmlgroupcontecnt .= $pdlTdContent;
  $htmlgroupcontecnt .= '</td>';  
  /*Allow Part Days of Leave Request - End */

  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap">';   
  $htmlgroupcontecnt .= $arrUserLeaveRequestGroup['ExtraLeaveClicks'];
  $htmlgroupcontecnt .= '</td>';

  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap" align="center">';
  $strRequestsAllowed = '';
  
  foreach ($arrUserLeaveRequestGroup['RequestsAllowedMonthly'] as $intMonth => $intNumAllowed) {
    $strRequestsAllowed.= "+".$intMonth." Months ".$intNumAllowed.'<br>'; 
  }
  $htmlgroupcontecnt .= '<img class="tip" border="0" src="images/info.png" width="16" height="16" qtip-content="'.$strRequestsAllowed.'">';  
  $htmlgroupcontecnt .= '</td>';  
  
  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap">';
  if ($arrUserLeaveRequestGroup['RequestsAllowedYearly'] == -1) {
    $htmlgroupcontecnt .= 'Not in use';
  }   
  else {
    $htmlgroupcontecnt .= $arrUserLeaveRequestGroup['RequestsAllowedYearly'];
  }
  $htmlgroupcontecnt .= '</td>'; 

  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap">';   
  $htmlgroupcontecnt .= $arrUserLeaveRequestGroup['email'];
  $htmlgroupcontecnt .= '</td>'; 

  $htmlgroupcontecnt .= '<td class="leaverequestgrupWidthnowrap" title='.$arrUserLeaveRequestGroup['emailcopiesto'].'>';   
  $htmlgroupcontecnt .= $emailcopeto  = (strlen($arrUserLeaveRequestGroup['emailcopiesto']) > 30) ? substr($arrUserLeaveRequestGroup['emailcopiesto'], 0, 30).'...' : $arrUserLeaveRequestGroup['emailcopiesto'];
  $htmlgroupcontecnt .= '</td>'; 
  
  $htmlgroupcontecnt .= '</tr>';
  
}
$htmlgroupcontecnt .= '</tbody>';
$htmlgroupcontecnt .= '</table>'; 

// ###################################################### END Put the groups in the div
echo  $htmlgroupcontecnt;
?>

<script>
$(document).ready(function(){

  var table = $("#leavegroupslist").DataTable({
    paging: true,
    iDisplayLength: 50,
    info:     false,
    stateSave: true,
    deferRender: true,
 
    "initComplete": function( settings, json ) {
        ResizeLeaveGroupsList();
    }
  });
yadcf.init(table, [
  {column_number: 1,
    filter_type: 'text'
  },
]);

$(".dataTables_paginate").css('visibility', 'visible');
  
$('#leavegroupslist tbody').on( 'click', 'tr', function () {
  if ( $(this).hasClass('selected') ) {
    $(this).removeClass('selected');
  }
  else {
    table.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
  }
});

$('.tip').qtip({
  content: {
      text: function(event, api) {
          // Retrieve content from custom attribute of the $('.selector') elements.
          return $(this).attr('qtip-content');
      }
  },
    position: {
      my: 'top right',  // Position my top left...
      at: 'bottom middle', // at the bottom right of...
      viewport: $(window),
      adjust: {y: -3}
    },
    show: {
      solo: true
    },
    style: 'qtip-rounded qtip-shadow qtip-dark'    
});  
$('[title]').qtip({
    position: {
      my: 'top center',
      at: 'bottom center',
      viewport: $(window)
  },
  style: 'qtip-rounded qtip-shadow qtip-light'
}); 
if ( $.cookie("leavegroupslist") !== null ) {
  scrollPos = $.cookie("leavegroupslist");
  $('#leavegroupslist').closest('.dataTables_scrollBody').scrollTop(scrollPos);      
};           
breakLongWords();    
});

$(window).resize(function() {
ResizeLeaveGroupsList();
breakLongWords();
})   

function ResizeLeaveGroupsList () {
  if($("#leavegroupslist").length){
    var offset = ($("#leavegroupslist").offset().top);
    var windowheight = $(window).height() - offset - 50;
    $('.dataTables_scrollBody').height((windowheight - 30));
    $('#leavegroupslist').DataTable().columns.adjust().draw();
  }
}  

$('#leavegroupslist').closest('.dataTables_scrollBody').on('scroll', function() { 
var currpos = $('#leavegroupslist').closest('.dataTables_scrollBody').scrollTop();
$.cookie("leavegroupslist", currpos);
}); 

function breakLongWords() {
  const maxLength = 20;
  function breakWord(word) {
      let brokenWord = '';
      while (word.length > maxLength) {
          brokenWord += word.substring(0, maxLength) + '-<br>';
          word = word.substring(maxLength);
      }
      brokenWord += word;
      return brokenWord;
  }
  $('#leavegroupslist td.leaverequestgrupTableDescription').each(function() {
      var words = $(this).html().split(/\s+/);
      var newContent = words.map(word => {
          return word.length > maxLength ? breakWord(word) : word;
      }).join(' ');
      $(this).html(newContent);
  });
}
</script>