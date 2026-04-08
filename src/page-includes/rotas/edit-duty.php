<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
//include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/rota_functions.php';

$intRotaID = $_REQUEST["rotaid"];
$intDutyID = $_REQUEST["dutyid"];
$intLineID = $_REQUEST["lineid"];
$intRotaDutyID = $_REQUEST["rotadutyid"];

$intType = 0;
$intShowForm = 1;

if (isset($_POST["submit"])) {            //Update the Duty
  if ($_POST["submit"] == 'Submit') {
    $strStartDate = $_REQUEST["startdate"];
    $strEndDate = $_REQUEST["enddate"];

    $stroldStartDate = $_REQUEST["oldstartdate"];
    $stroldEndDate = $_REQUEST["oldenddate"];

    $strHistory = '';
    $strLastMod = '';

    if ($intRotaID != 0) {
      if ($strStartDate != $stroldStartDate) {
	$strHistory .= PHP_EOL .'-- Start Date changed from ['.$stroldStartDate.'] to ['.$strStartDate.']';
      }
      if ($strEndDate != $stroldEndDate) {
	$strHistory .= PHP_EOL .'-- End Date changed from ['.$stroldEndDate.'] to ['.$strEndDate.']';
      }
    }
    $intShowForm = 0;

  }
  else {
    $intShowForm = 2;
  }
};


//check if we show the form

if ($intShowForm == 1) {
  if ($intRotaDutyID > 0) {
    echo '<div style="width: 600px">';
    echo '<form id="neweditrotaduty">';
    echo '<table id="rotadutynewedittable" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="4">Editing Rota Duty...</th>';

    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    echo '<tr>';
    echo '<td class="lightblue">Duty Name</td>';
    echo '<td colspan="3">';
    echo '<input id="dutyname" name="dutyname" type="text" size="60" readonly value="'.$row['DutyName'].'" />';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightblue" width="15%">Available From</td>';
    echo '<td width="35%">';
    echo '<input id="startdate" name="startdate" type="text" size="15" value="'.$Date1.'" />';
    echo '</td>';
    echo '<td class="lightblue" width="15%">Available Until </td>';
    echo '<td width="35%">';
    echo '<input id="enddate" name="enddate" type="text" size="15" value="'.$Date2.'" />';
    echo '</td>';
    echo '</tr>';


    echo '<tr>';
    echo '<td></td>';
    echo '<td colspan="4"><input name="submit" type="submit" value="Submit"></input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="submit" type="submit" value="Cancel"></input></td>';
    echo '</tr>';

    echo '</tbody>';
    echo '</table>';
    echo '<input type="hidden" name="rotaid" value="'.$intRotaID.'">';
    echo '<input type="hidden" name="dutyid" value="'.$intDutyID.'">';
    echo '<input type="hidden" name="lineid" value="'.$intLineID.'">';
    echo '<input type="hidden" name="rotadutyid" value="'.$intRotaDutyID.'">';
    echo '<input type="hidden" name="oldstartdate" value="'.$Date1.'">';
    echo '<input type="hidden" name="oldenddate" value="'.$Date2.'">';
    //echo '<input type="hidden" name="oldNotes" value="'.$oldNotes.'">';
    //echo '<input type="hidden" name="LastModDate" value="'.$strLastMod.'">';
    //echo '<input type="hidden" name="listtype" value="'.$intType.'">';
    echo '</form>';
    echo '</div>';
  }


  ?>

  <script type="text/javascript">

  $(document).ready(function(){

    $( "#startdate" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      dateFormat: "dd/mm/yy"
    });

    $( "#enddate" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      dateFormat: "dd/mm/yy"
    });

    //var $tabs = $('#masterdutiestabs').tabs();
    //var selected = $tabs.tabs('option', 'active');

    $('#neweditrotaduty').validate({
      debug: true,
      rules:{
	"startdate":{
	  required:true,
	}
      },
	messages:{
	  "startdate":{
	    required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter a Start Date.' />"
	  }
	},
	submitHandler: function(form) {
	  $.ajax({type:'POST', url: 'page-includes/rotas/edit-duty.php', data:$('#neweditrotaduty').serialize(), success: function(data) {
	  ShowRotaDetails(<?=$intRotaID?>, <?=$intLineID?>);
	  $.facebox.close();
	  }});
	}
      });
  });

  $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
   $(function() {
    $( "button" )
      .button()
  });



  </script>

<?php
}
else {
?>

<script type="text/javascript">
    ShowRotaDetails(<?=$intRotaID?>, <?=$intLineID?>);
</script>

<?php
}
?>


