<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
//include_once '../../function-includes/masterduty_functions.php';
include_once '../../function-includes/rota_functions.php';

$intRotaID = $_REQUEST["rotaid"];
$intRotaPersonID = $_REQUEST["rotapersonid"];

$intType = 0;
$intShowForm = 1;

if (isset($_POST["submit"])) {            //Update the Rota Person
  if ($_POST["submit"] == 'Submit') {
    $strStartDate = $_REQUEST["startdate"];
    $strEndDate = $_REQUEST["enddate"];

    $stroldStartDate = $_REQUEST["oldstartdate"];
    $stroldEndDate = $_REQUEST["oldenddate"];

    $strHistory = '';
    $strLastMod = '';
    $intWeek = 0;
    $intStaffID = 0;

    if ($intRotaPersonID != 0) {
      if ($strStartDate != $stroldStartDate) {
	$strHistory .= PHP_EOL .'-- Start Date changed from ['.$stroldStartDate.'] to ['.$strStartDate.']';
      }
      if ($strEndDate != $stroldEndDate) {
	$strHistory .= PHP_EOL .'-- End Date changed from ['.$stroldEndDate.'] to ['.$strEndDate.']';
      }
    }

    list($intStatus, $strStatus) = DropPersonOnRota($intRotaPersonID, $intRotaID, $intWeek, $intStaffID, $strStartDate, $strEndDate, $strLastMod, $strHistory);

    $intShowForm = 0;

  }
  else {
    $intShowForm = 2;
  }
};


//check if we show the form

if ($intShowForm == 1) {
  if ($intRotaPersonID > 0) {
    echo '<div style="width: 600px">';
    echo '<form id="neweditrotaperson">';
    echo '<table id="rotapersonnewedittable" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="4">Editing Rota Person...</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td class="lightblue" width="15%">Rota From</td>';
    echo '<td width="35%">';
    echo '<input id="startdate" name="startdate" type="text" size="15" value="'.$Date1.'" />';
    echo '</td>';
    echo '<td class="lightblue" width="15%">Rota Until </td>';
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
    echo '<input type="hidden" id="rotaid" name="rotaid" value="'.$intRotaID.'">';
    echo '<input type="hidden" id="rotapersonid" name="rotapersonid" value="'.$intRotaPersonID.'">';
    echo '<input type="hidden" id="oldstartdate" name="oldstartdate" value="'.$Date1.'">';
    echo '<input type="hidden" id="oldenddate" name="oldenddate" value="'.$Date2.'">';

    //echo '<input type="hidden" name="oldNotes" value="'.$oldNotes.'">';
    //echo '<input type="hidden" name="LastModDate" value="'.$strLastMod.'">';
    //echo '<input type="hidden" name="listtype" value="'.$intType.'">';

  }
  else {
    $Date1 = '';
    $Date2 = '';

    if ($Date1 == '') {
      $Date1 = '01/01/2010';
    }
    if ($Date2 == '') {
      $Date2 = '31/12/2030';
    }


    echo '<div style="width: 600px">';
    echo '<form id="neweditrotaperson">';
    echo '<table id="rotapersonnewedittable" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="4">Editing Rota Person...</th>';

    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';


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
    echo '<input type="hidden" id="rotaid" name="rotaid" value="'.$intRotaID.'">';
    echo '<input type="hidden" id="rotapersonid" name="rotapersonid" value="'.$intRotaPersonID.'">';
    echo '<input type="hidden" id="oldstartdate" name="oldstartdate" value="">';
    echo '<input type="hidden" id="oldenddate" name="oldenddate" value="">';
    //echo '<input type="hidden" name="oldNotes" value="'.$oldNotes.'">';
    //echo '<input type="hidden" name="LastModDate" value="'.$strLastMod.'">';
    //echo '<input type="hidden" name="listtype" value="'.$intType.'">';


  }

  echo '</form>';
  echo '</div>';


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

    $('#neweditrotaperson').validate({
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
	  var rotaid = form.elements["rotaid"].value;

	  $.ajax({type:'POST',
		   url: 'page-includes/rotas/edit-person.php',
		  data: $('#neweditrotaperson').serialize(),
	       success: function(data) {
			ShowPeopleByRota(rotaid);
			$.facebox.close();
		      }
	  });
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
    ShowPeopleByRota(<?=$intRotaID?>);
</script>

<?php
}
?>


