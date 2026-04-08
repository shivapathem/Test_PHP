<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
echo'<div id="adminleavetabs" style="width:100%">';
echo'<ul>';
echo'<li><a href="#adminleavetabs-1">Leave & Request Groups</a></li>';  
echo'<li><a href="#adminleavetabs-2">Leave Groups Config</a></li>';
echo'<li><a href="#adminleavetabs-3">Request Groups Config</a></li>';
echo'<li><a href="#adminleavetabs-4">Staff & Percentages</a></li>';
echo'</ul>';

echo'<div id="adminleavetabs-1">';

echo '</div>';
echo'<div id="adminleavetabs-2">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre">';
echo '<br>Please highlight a Leave Group and try again!<br><br>';
echo '</div>';
echo '</div>'; 
echo'<div id="adminleavetabs-3">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre">';
echo '<br>Please highlight a Leave Group and try again!<br><br>';
echo '</div>';
echo '</div>'; 
echo'<div id="adminleavetabs-4">';
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre">';
echo '<br>Please highlight a Leave Group and try again!<br><br>';
echo '</div>';
echo '</div>';
echo'</div>';

?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $( "#adminleavetabs" ).tabs({ 
      activate : function( event, ui ) {
        var active = $( "#adminleavetabs" ).tabs( "option", "active" );
        
        switch (active) {
        case 1:
          $("#leavegroupslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowLeaveGroupConfig(id);
          });   
          break;
          
        case 2:
          $("#leavegroupslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowRequestGroupConfig(id);
          });   
          break;          
        case 3:
          $("#leavegroupslist").dataTable().$("tr.selected").each(function(){
            var id = $(this).attr("id");
            ShowLeaveStaff(id);
          });   
          break;
        }

      }
    });
    $("#adminleavetabs .ui-tabs-panel").removeAttr("aria-labelledby");
  });

ShowLeaveGroups();
          
});

function ShowLeaveGroups () {
  $.ajax({
        url: "page-includes/admin/leaverequestgroups.php",
        type: "POST",
        dataType: "HTML",
        data: {},
        success: function (dataresult) {
          $('#adminleavetabs-1').html(dataresult);
        }
    });
}

function ShowLeaveGroupConfig (id) {
  $.ajax({
        url: "page-includes/admin/leavegroupsconfigholder.php",
        type: "POST",
        dataType: "HTML",
        data: {id: id},
        success: function (dataresult) {
          $('#adminleavetabs-2').html(dataresult);
        }
    });
}

function ShowRequestGroupConfig (id) {
  $.ajax({
        url: "page-includes/admin/requestgroupsconfigholder.php",
        type: "POST",
        dataType: "HTML",
        data: {id: id},
        success: function (dataresult) {
          $('#adminleavetabs-3').html(dataresult);
        }
    });
}

function ShowLeaveStaff (id) {
  $.ajax({
        url: "page-includes/admin/leaverequestsstaffingroup.php",
        type: "POST",
        dataType: "HTML",
        data: {id: id},
        success: function (dataresult) {
          $('#adminleavetabs-4').html(dataresult);
        }
    });

}

function showleavegroups () {
 
  $.ajax({
        url: "page-includes/admin/admin-leaverequestgroups.php",
        type: "POST",
        dataType: "HTML",
        data: {},
        success: function (dataresult) {
          $('#adminleavetabs-1').html(dataresult);
        }
    });
}

function EditLeaveType(id, groupid) {
  $.ajax({
        url: "page-includes/admin/leavenewedittype.php",
        type: "POST",
        dataType: "HTML",
        data: {id: id,groupid: groupid},
        success: function (dataresult) {
          $.facebox(dataresult); 
        }
    }); 
}

function EditLeaveGroup(id) {
  $.ajax({
        url: "page-includes/admin/leaveeditgroup.php",
        type: "POST",
        dataType: "HTML",
        data: {id: id},
        success: function (dataresult) {
          $.facebox(dataresult); 
        }
    });  
}

function EditLeaveAvailability (groupid, year) {
  $.ajax({
        url: "page-includes/admin/leaveeditavailability.php",
        type: "POST",
        dataType: "HTML",
        data: { groupid: groupid,
                year: year,},
        success: function (dataresult) {
          $.facebox(dataresult); 
        }
    });             
}
 
function EditLeaveAvailabilityDay (groupid, date) {
  $.ajax({
        url: "page-includes/admin/leaveeditavailabilityday.php",
        type: "POST",
        dataType: "HTML",
        data: { groupid: groupid,
          date: date,},
        success: function (dataresult) {
          $.facebox(dataresult); 
        }
    });
}

function ShowLeaveAvalability(id, year) {
  $.ajax({
        url: "page-includes/admin/leaveavailability.php",
        type: "POST",
        dataType: "HTML",
        data: { id: id,
                year:year},
        success: function (dataresult) {
          $('#admingroupleavetabs-2').html(dataresult);
        }
    });
}

function ShowSummerLeave(id, year) {
  $.ajax({
        url: "page-includes/admin/leavesummerdates.php",
        type: "POST",
        dataType: "HTML",
        data: { id: id,
                year:year},
        success: function (dataresult) {
          $('#admingroupleavetabs-3').html(dataresult);
        }
    });
}

function EditRequestType (id, groupid) {
  $.ajax({
        url: "page-includes/admin/requesttypeedit.php",
        type: "POST",
        dataType: "HTML",
        data: { id: id,
          groupid:groupid},
        success: function (dataresult) {
          $.facebox(dataresult); 
        }
    });           
}

function EditStaffEFT (staffid, groupid) {
  $.ajax({
        url: "page-includes/admin/admin-editstaffeft.php",
        type: "POST",
        dataType: "HTML",
        data: { staffid: staffid,
          groupid:groupid},
        success: function (dataresult) {
          $.facebox(dataresult); 
        }
    }); 
}

function ShowRequestTypes (requestgroup) {
  $.ajax({
        url: "page-includes/admin/requesttypes.php",
        type: "POST",
        dataType: "HTML",
        data: { requestgroup: requestgroup},
        success: function (dataresult) {
          $('#admingrouprequesttabs-1').html(dataresult); 
        }
    }); 
}

function ShowRequestsClosed (requestgroup) {
  $.ajax({
        url: "page-includes/admin/request-dates-closed.php",
        type: "POST",
        dataType: "HTML",
        data: { requestgroup: requestgroup},
        success: function (dataresult) {
          $('#admingrouprequesttabs-2').html(dataresult); 
        }
    }); 
}

function EditRestriction (id, groupid) {
  $.ajax({
        url: "page-includes/admin/requests-edit-restriction.php",
        type: "POST",
        dataType: "HTML",
        data: {  id: id,
      groupid: groupid},
        success: function (dataresult) {
          $.facebox(dataresult); 
        }
    });
}

function DeleteRestriction (id, groupid) {
  customConfirm('Do you want to delete this restriction?',function(){
			$.post("page-includes/admin/requestrestrictiondelete.php", {
			  id: id,
			  groupid: groupid,      
			},
			function(data,status){              
			  ShowRequestsClosed(groupid); 
			});
		},
		function() {
			return false;
		}
	);
}

$( window ).resize(function() {
    DoResizeGrid();
})
function DoResizeGrid() {
  var windowheight = $(window ).height() - 250; 
  $('.dataTables_scrollBody').height((windowheight));
}
  
</script>