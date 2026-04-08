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
echo '<td>Skills and Duties People can do</td>';
echo '<td rowspan="2" align="right">';
echo '<img border="0" src="images/clock.png" width="32" height="32" onclick="javascript:ToggleTimeZone('.$intDepartmentID.', 2);" class="handcursor" title="Clck here to change timezone"></td>';
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

echo '<td width="500px" valign="top">';
echo '<div id="dutieslist_scd-'.$intDepartmentID.'">';
echo '</div>';
echo '</td>';

echo '<td valign="top">';
echo '<div id="cando-'.$intDepartmentID.'">';
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
    department: department,
    page: 1,
    showbuttons: 0
  },
    function(data,status){
      $('#dutieslist_scd-<?php echo $intDepartmentID?>').html(data);
    }
  )
}

function FillCanDoPage (id, department) {
  $.post("page-includes/skills/skills-duties-cando-fillpage.php", {
    id: id
  },
    function(data,status){
      $('#cando-<?php echo $intDepartmentID?>').html(data);
    }
  )
}



function EditDuty (id) {
  $('#popup').bPopup({
  easing: 'easeOutBack',
  speed: 450,
  transition: 'slideDown',
  contentContainer:'#popcontent',
  loadUrl:'page-includes/ajax-calls/skills-newedit-duty.php?id='+id+'',
  })
}

function DutyDays (id) {
  $('#popup').bPopup({
  easing: 'easeOutBack',
  speed: 450,
  transition: 'slideDown',
  contentContainer:'#popcontent',
  loadUrl:'page-includes/ajax-calls/skills-duty-days.php?id='+id+'',
  })
}

function DeleteDuty (id) {
  customConfirm('Do you want to delete this Duty?<br\>It will remove all programmes associated with it!',function(){
			$.post("page-includes/ajax-calls/skills-delete-duty.php", {
				  id: id
				},
				function(data,status){
				  FillDutiesList(0);
				  FillDutiesPage(0);
				}
			)
		},
		function() {
			return false;
		}
	);
}

$('#programmes td').click(function(e) {
    $('#programmes td').removeClass('highlighted');
    $(this).addClass('highlighted');

});

</script>