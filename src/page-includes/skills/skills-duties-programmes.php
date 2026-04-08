<?php
session_start();
if (isset($_SESSION['bst'])) {
  $bst = $_SESSION['bst'];
}
else {
  $bst = date("I");
  $_SESSION['bst'] = $bst;
}

$intDepartmentID = $_REQUEST['department'];

echo '<div class="tableheadersmall medtextboldcentre" width="550px">';
echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
echo '<tr>';
echo '<td>Assign Skills to Duties</td>';
echo '<td rowspan="2" align="right">';
echo '<img border="0" src="images/clock.png" width="32" height="32" onclick="javascript:ToggleTimeZone('.$intDepartmentID.', 1);" class="handcursor" title="Clck here to change timezone"></td>';
echo '</tr>';
echo '<tr>';
echo '<td>';
echo 'You are Currently Viewing Duties in <font color="#990000">';
if ($bst == 0) {
  echo "GMT";
}
else {
  echo "BST";
}
echo '</font>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '<br>';

echo '<table class="tablesmall" width="100%">';
echo '<tr>';

echo '<td valign="top" width="700px">';
echo '<div id="dutieslist-'.$intDepartmentID.'">';
echo '</div>';
echo '</td>';

echo '<td valign="top">';
echo '<div id="dutiesprogrammes-'.$intDepartmentID.'">';
echo '<div class="tableheadersmall medtextboldcentre" style="width:50%">';
echo '<br>Choose a duty from the list<br><br>';
echo '</div>';


echo '</div>';
echo '</td>';

echo '</tr>';
echo '</table>';

?>

<script type="text/javascript">
$(document).ready(function(){
  FillDutiesList(0, <?php echo $intDepartmentID?>);
})

function FillDutiesList (id, department) {
  $.post("page-includes/skills/skills-fill-duties-list.php", {
    id: id,
    department: department
  },
    function(data,status){
      $('#dutieslist-<?php echo $intDepartmentID?>').html(data);
    }
  )
}

function FillDutiesPage (id, department) {
  $.post("page-includes/skills/skills-duties-progs-fillpage.php", {
    id: id,
    department: department
  },
    function(data,status){
      $('#dutiesprogrammes-<?php echo $intDepartmentID?>').html(data);
    }
  )
}


function EditDuty (id, department) {
  $.post("page-includes/skills/skills-new-edit-duty.php", {
    id: id,
    department: department
  },
  function(data,status){
	  $.facebox(data);
  })
}

function ToggleDutyDay (id, day) {
  $.post("page-includes/skills/skills-toggle-day.php", {
    id: id,
    day: day
  },
  function(data,status){
    FillDutiesList(id, <?php echo $intDepartmentID?>);
    FillDutiesPage(id, <?php echo $intDepartmentID?>);
  })
}

function ToggleGMTBST (id, gmtbst) {
  $.post("page-includes/skills/skills-toggle-gmtbst.php", {
    id: id,
    gmtbst: gmtbst
  },
  function(data,status){
    FillDutiesList(id, <?php echo $intDepartmentID?>);
    FillDutiesPage(id, <?php echo $intDepartmentID?>);
  })
}

function AddProgToDuty (progid, dutyid) {
  $.post("page-includes/skills/skills-add-prog-to-duty.php", {
     dutyid: dutyid,
     progid: progid
  },
    function(data,status){
    FillDutiesPage(dutyid, <?php echo $intDepartmentID?>);
    }
  )
}

function RemoveProgFromDuty (progid, dutyid) {
  $.post("page-includes/skills/skills-remove-prog-from-duty.php", {
     dutyid: dutyid,
     progid: progid
  },
    function(data,status){
    FillDutiesPage(dutyid, <?php echo $intDepartmentID?>);
    }
  )
}



function DeleteDuty (id) {
  customConfirm('Do you want to delete this Duty?<br/>It will remove all programmes associated with it!',function(){
			$.post("page-includes/skills/skills-delete-duty.php", {
			  id: id
			},
		  function(data,status){ 
			$.facebox.close();   
			FillDutiesList(0, <?php echo $intDepartmentID?>);
			FillDutiesPage(0, <?php echo $intDepartmentID?>);
		  }); 
		},
		function() {
			return false;
		}
	);
}

$('#programmes td').click(function(e) {
    $('#programmes td').removeClass('highlighted');
    $(this).addClass('highlighted');
    //$(this).removeClass('odd');
    //$(this).removeClass('even');
});
</script>