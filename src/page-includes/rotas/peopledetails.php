<?php

use Illuminate\Support\Collection;

session_start();

include_once '../../function-includes/init.php';

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';
include_once 'setTeamCookieRotas.php';

$intAreaID = $_SESSION['user']['AreaID'];
$intStaffID = $_SESSION['user']['StaffID'];
$intuserid = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

if (isset($_POST["id"])) {
  $intPersonID = $_POST["id"];
  if (date('N', strtotime($_POST["RotaDate"])) == 6) {
      $RotaDate = date('d-m-Y', strtotime($_POST["RotaDate"]));
  }
  else {
      $RotaDate = date('d-m-Y', strtotime('last Saturday', strtotime($_POST["RotaDate"])));
  }

}
else {
  $intPersonID = 0;
  $RotaDate = date("d-m-Y");
}
if ($_POST["RotaDate"] == null || $_POST["RotaDate"] == "") {
    $SelectedDateRota = date("d-m-Y");
}
else{
    $SelectedDateRota = $_POST["RotaDate"];
}
$TeamId = $_POST["teamId"];
$pageid = 24;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $TeamId);

$rsPeopleJson = GetUserAreaPeopleList($intuserid,$TeamId);
$rsPeople = json_decode($rsPeopleJson,true);
$rsPeople = new Collection($rsPeople);
$rsPeople = $rsPeople->map(function($row){
  $row['sortName'] = strtolower($row['Fullname']);
  return $row;
})
->sortBy("sortName")
->values()
;

echo '<div id="rotapersondetailstopdiv" style="width:100%;">';
echo '<table id="rotapersondetails" class="stripe bluetable" width="100%" style="margin-bottom:0px; margin-top:10px">';
echo '<thead>';
echo '<tr>';
echo '<th >Showing Person : ';
echo '<select class="chosen-select" name="ddlPersonName" id="ddlPersonName" style="background-color:white; width:250px;">';
  if ($intPersonID == 0) {
    echo '<option value="0" selected>Select Person</option>';
  }
  else {
    echo '<option value="0">Select Person</option>';
  }
if (!empty($rsPeople)) {
    $rowcount = count($rsPeople);
  for ($row = 0; $row < $rowcount; $row++) {
    if ($rsPeople[$row]['ScheduledPersonID'] == $intPersonID) {
      echo '<option value="'.$rsPeople[$row]['ScheduledPersonID'].'" selected>'.$rsPeople[$row]['Fullname'].'</option>';
    }
    else {
      echo '<option value="'.$rsPeople[$row]['ScheduledPersonID'].'">'.$rsPeople[$row]['Fullname'].'</option>';
    }
  }  
}
echo '</select>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;Date: <input id="RotaDate" style="margin-left: 10px;font-size:12px;height:20px" name="RotaDate" type="text" size="15" value="'.$SelectedDateRota.'" /> Choose a date to see how the Rota relates to the following Weeks.';
echo '</th>';
echo '</tr>';
echo '</thead>';
echo '</table>';
echo '</div>';

echo '<div id="rotapersonlist0" style="clear: left; clear:right; height: 140px" class="ScrollableTableRotaPeople tableFixHead RotaRemoveBottomMargin">';
echo '<table id="persondetails2" class="stripe bluetable" style="margin-top:-1px; width:100%;">';
echo '<thead>';
echo '<tr>';
echo '<th style="border-top:2px solid #000000;" width="25%">Rota Pattern Name</th>';
echo '<th style="border-top:2px solid #000000;" width="15%">Start Date</th>';
echo '<th style="border-top:2px solid #000000;" width="15%">Weeks In Rota Pattern</th>';
echo '<th style="border-top:2px solid #000000;" width="15%">Rota Pattern Week</th>';
echo '<th style="border-top:2px solid #000000;" width="15%">Week Start Date</th>';
echo '<th style="border-top:2px solid #000000;" width="15%">Week End Date</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$rsPersonRotasJson = GetRotasByPerson($intPersonID, date("d/m/Y", strtotime($_POST['RotaDate'])));
$rsPersonRotas = json_decode($rsPersonRotasJson,true);
$pdo = OpenDBLinkA7();
$RotaWeek = 0;
foreach($rsPersonRotas as $rsPersonRotasVal)
{
	$startDate  = date("Y-m-d",strtotime(str_replace('/','-',$rsPersonRotasVal['RotaPersonStartDate'])));
	$endDate  = (in_array($rsPersonRotasVal['RotaPersonEndDate'], array('01/01/9999', '31/12/9999'))) ? date('Y-m-d', strtotime('+2 years', time())) : date("Y-m-d",strtotime(str_replace('/','-',$rsPersonRotasVal['RotaPersonEndDate'])));

	$sql = "select  min(dDateTime) sDate, max(dDateTime) eDate, dbo.GetWeekofRota(".$rsPersonRotasVal['WeeksInRota'].", ".$rsPersonRotasVal['RotaStartWeek'].", ixYearWeek) as rotaWeek from TimeDimension where dDateTime between '$startDate' and '$endDate' group by ixYearWeek order by sDate";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rotaDates = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$RotaWeek = 0;
	foreach($rotaDates as $rotaDatesVal)
	{
		$count2 = 0;
		$rotaDatesVal['sDate'] = date("Y-m-d",strtotime($rotaDatesVal['sDate']));
		$rotaDatesVal['eDate'] = date("Y-m-d",strtotime($rotaDatesVal['eDate']));
		$rotaDisplayDate  = str_replace('/','-',$rotaDatesVal['sDate']);
		if($RotaWeek == 0)
		{
			$RotaWeek = $rotaDatesVal['rotaWeek'];
		}
		$highlightRow = (($rotaDatesVal['sDate'] <= date('Y-m-d')) && ($rotaDatesVal['eDate'] >= date('Y-m-d'))) ? 1 : 0;
		if($highlightRow)
		{
			echo '<tr class="highlightOrange">';
		}else
		{
			echo '<tr onclick="javascript:highlightBg('.$count2.');" id="TableRow'.$count2.'">';
		}
		if (date("Y-m-d", strtotime($RotaDate)) <= date("Y-m-d", strtotime($rotaDisplayDate)))
		{
	?>
		<td >
              <a href="#" onclick='javascript:ShowThisRota('<?php echo $rsPersonRotasVal['RotaID']; ?>','<?php echo $startDate; ?>')\><?php echo $rsPersonRotasVal['RotaName']; ?></a>
              </td>
              <td >
              30-12-1995
              </td>
              <td >
              <?php echo $rsPersonRotasVal['WeeksInRota']; ?>
              </td>
              <td >
              <?php echo $RotaWeek; ?>
              </td>
              <td >
              <?php echo date("d-m-Y", strtotime($rotaDatesVal['sDate'])); ?>
              </td>
              <td >
              <?php echo date("d-m-Y", strtotime($rotaDatesVal['eDate'])); ?>
              </td>
              <tr>
	<?php
		}
			if($RotaWeek < $rsPersonRotasVal['WeeksInRota'])
			{
				$RotaWeek++;
			}else
			{
				$RotaWeek = 1;
			}
		$count2++;
	}

}
echo '</tbody>';
echo '</table>';
echo '</div>'; 
echo '<div id="currDiv" style="display:none;"></div>';
?>

<script type="text/javascript">
$(document).ready(function(){
  if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
    $( '#content' ).load( 'page-includes/no_access.php', function() { });
  }
  else {
    $( "#RotaDate" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: false,
        dateFormat: "dd-mm-yy"
    });
    $('#RotaDate').datepicker('option', 'firstDay', 6);
    $("#ddlPersonName").change(function(event){
		event.stopImmediatePropagation();
        var RotaDate = $("#RotaDate").val();

        ListRotaPeople(1, this.value, RotaDate);
    });
    $("#RotaDate").change(function(event){
		event.stopImmediatePropagation();
        RotaPersonName = $("#ddlPersonName").val();
        ListRotaPeople(1, RotaPersonName, this.value);
    });
    $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
      $(function() {
	    $("button").button()
      });

    $(function() {
      $( ".selector" ).selectmenu({
	    change: function( event, ui ) {
			event.stopImmediatePropagation();
            var value = $(this).val();
            ShowRotaDetails(value, 1);
	    }
      });
    });
  }
  $("#ddlRotaTeams").on('change', function(event){
	  event.stopImmediatePropagation();
      ListRotaPeople(1, 0, RotaDate = null);
  });
});

function ListRotaPeople(listtype, id, RotaDate = null) {
    var tabCookieName = "rotapeopletabs";
    $.cookie(tabCookieName, listtype, { expires: 0 });

    if (listtype == 1) {
        $.post("page-includes/rotas/peopledetails.php", {
                id: id,
                RotaDate: RotaDate,
                teamId: $("#ddlRotaTeams").val()
            },
            function(data,status){
                $('#rotapeoplelistdiv1').html(data);
            }
        );
    }
    else {
        $('#rotapeoplelistdiv1').empty();
    }

    if($.cookie('selectRotaId') > 0)
	{
		ShowRotaDetails($.cookie('selectRotaId'), 1);
	}else
	{
		ShowRotaDetails(0, 1);
	}
}

function ShowThisRota(rotaid, selecteddate) {  
  ShowRotaDetails(rotaid, 1);
} 

function highlightBg(rotaId){
  var currDiv = $('#currDiv').text();
  if(currDiv == ''){
    $('#currDiv').html(rotaId);
    $("#TableRow"+rotaId).css("background-color","#dddddd");
  } else {
    $('#TableRow'+currDiv).css("background-color","#ffffff");
    $("#TableRow"+rotaId).css("background-color","#dddddd");
    $('#currDiv').html(rotaId);
  }
} 
  
</script>

