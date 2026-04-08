<?php
session_start();
$intDepartmentID = $_REQUEST['department'];
if (isset($_REQUEST['readonly'])) {
  $intReadOnly = $_REQUEST['readonly'];
}
else {
  $intReadOnly = 0;
}
echo '<div class="tableheadersmall" style="width: 100%">';
echo '<br>Skills and Staff.<br>You can define Skills and assign Staff to them here.<br>Skills can be Programmes, The ability to work in a Studio or Resource or another type of Skill such as Director, Camera Operator or Sound.<br><br>';
echo '</div>';
echo '<br>';

echo '<table class="tablesmall" width="100%">';
echo '<tr>';

echo '<td valign="top" width="300px">';
echo '<div id="programmelist-'.$intDepartmentID.'">';
echo '</div>';
echo '</td>';

echo '<td valign="top">';
echo '<div id="staff-'.$intDepartmentID.'">';
echo '<div class="tableheadersmall medtextboldcentre" style="width:50%">';
echo '<br>Choose a Skill from the list<br><br>';
echo '</div>';


echo '</div>';
echo '</td>';

echo '</tr>';
echo '</table>';

?>
<script type="text/javascript">
$(document).ready(function(){
  FillProgrammesList(0, <?php echo $intDepartmentID?>);
})

function FillProgrammesList (id, department) {
  $.post("page-includes/skills/skills-fill-programmes-list.php", {
    id: id,
    department: department,
    readonly: <?php echo $intReadOnly?>
  },
    function(data,status){
      $('#programmelist-<?php echo $intDepartmentID?>').html(data);
    }
  )
}

function FillProgsStaffPage (id, department) {
  $.post("page-includes/skills/skills-progs-staff-fillpage.php", {
    id: id,
    department: department,
    readonly: <?php echo $intReadOnly?>
  },
    function(data,status){
      $('#staff-<?php echo $intDepartmentID?>').html(data);
    }
  )
}


function EditProgramme (id, department) {
  $.post("page-includes/skills/skills-new-edit-programme.php", {
    id: id,
    department: department
  },
  function(data,status){
	  $.facebox(data);
  })
}


function DeleteProgramme (id) {
  $( "#dialog-programme-delete" ).dialog({
    width:500,
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/skills/skills-delete-programme.php", {
        id: id
      },
      function(data,status){
        $.facebox.close();       
        FillProgrammesList(0, <?php echo $intDepartmentID?>);
        FillProgsStaffPage(0, <?php echo $intDepartmentID?>);
      });
      },
      "No": function() {
        $( this ).dialog( "close" );
      },
    }
  }); 

}

$('#programmes td').click(function(e) {
    $('#programmes td').removeClass('highlighted');
    $(this).addClass('highlighted');

});

</script>