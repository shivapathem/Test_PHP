<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
date_default_timezone_set('Europe/London');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/page-includes/admin/process/classSchedulingTeam.php';
//call class
$schedteamobj = new classSchedulingTeam();
$intuserid = ($_COOKIE['editWeeklyUserId']) ?? $_SESSION['user']['UserID'];
$strMiddleDate = $_REQUEST['date'];
if (isset($_REQUEST['team'])) {
  $intTeamID = $_REQUEST['team'];
}  else {
  $intTeamID = GetDefaultSchedulingTeamIdByLogin($intuserid);
}

$strStartDate = date("Y-m-d", strtotime ("-1 day", strtotime($strMiddleDate)));
$strEndtDate = date("Y-m-d", strtotime ("+1 day", strtotime($strMiddleDate)));

$intStartWeek = bbcweeknumber($strStartDate);
$intEndWeek = bbcweeknumber($strEndtDate);

//replace dept with scheduling team
$arrTeamDefaults = json_decode($schedteamobj->getSchedulingTeamDetails($intuserid,$intTeamID,'edit'),true);

$intConfirmedDays = isset($arrTeamDefaults[0]['ConfirmedDays']) ? $arrTeamDefaults[0]['ConfirmedDays'] : 0 ;
$intMaskDays = $arrTeamDefaults[0]['maskAfter'];
$strSchedulingTeamName = $arrTeamDefaults[0]['schedulingTeamName'];
$intColourWeek = 0;
$filterStr2 = $intTeamID;
$arrAllocations = ReadMultiWeekAllocations($intStartWeek, $intEndWeek, $intTeamID, $intConfirmedDays, $intMaskDays, $intMaskType = 0, $intNoMask = 0, $intIgnoreRota = 0, $intSortOrder = 0, $myschdeullingPersonID = '', $intColourWeek,$filterStr='',$filterStr2,$filterStr3='',$filterOrderStr='', $selSchPersonId='', 1,0, 0,0);
echo '<h1 class="sr-only">Turnaround Checks</h1>';
echo '<table class="tablegreysmallnoborder" width="100%">';
echo '<tr height="50px">';
echo '<td class="medtextbold">Turnaround Checks for  '.$strSchedulingTeamName;
echo '</td>';
echo '<td align="right">Choose a Date</td>';
echo '<td><input type="hidden" id="datepicker"></td>';
echo '</tr>';
echo '</table>';

// End the header
//have we any allocations to show?
if (isset($arrAllocations)) {
  echo '<table id="gridchecks" class="tablesmall" width="100%">';
  echo '<thead>';
  echo '<tr height="40px">';

  // The header
  echo '<th>Name</th>';

  $ddate = $strStartDate;
  $i=0;

  while (strtotime($ddate) <= strtotime($strEndtDate)) {
    $weeksdays[$i]['week'] = bbcweeknumber($ddate);
    $weeksdays[$i]['day'] = $dowMap[date("D", strtotime($ddate))];
    echo '<th>';
    echo date("d/m/Y", strtotime($ddate)).'<br>'.date("l", strtotime($ddate));
    echo '</th>';
    if ($ddate != $strEndtDate) {
      echo '<th>';
      echo '</th>';
   	}
    $ddate = date ("Y-m-d", strtotime("+1 day", strtotime($ddate)));
    $i++;
  }
  echo '</tr>';

  echo '</thead>';
  // End the header
  echo '<tbody>';
	foreach ($arrAllocations as $strCurrStaffNumber => $arrAllocation) {
		$k=1;
		$arrCurrAllocation = $arrAllocation[$weeksdays[$k]['week']][$weeksdays[$k]['day']]['Duty'][0] ?? [];

		if (isset($arrCurrAllocation['StartTimerowvalue']) && ($arrCurrAllocation['StartTimerowvalue']) > 0 && ($arrCurrAllocation['EndTimerowvalue']) > 0) {
			$ddate = $strStartDate;
			echo '<tr>';
			echo '<th width="250px" style="font-weight:normal;" class="handcursor" onclick="javascript:showrotatab(\''.$ddate.'\',\''.$arrAllocation['SchedulingPersonID'].'\',\''.$intTeamID.'\')">';
			$TextColour = $arrAllocation["StaffTextColour"];
			echo '<b><font color="'.$TextColour.'">'.$arrAllocation['FullName'].'</font></b>';
			echo '<br>';
			echo $arrAllocation['SortCode'];
			echo '</td>';

			$i=0;

            while (strtotime($ddate) <= strtotime($strEndtDate)) {
				if (isset($arrAllocation[$weeksdays[$i]['week']][$weeksdays[$i]['day']]['Duty'][0])) {
					$arrCurrentAllocation = $arrAllocation[$weeksdays[$i]['week']][$weeksdays[$i]['day']]['Duty'][0];
				}

				if (isset($arrCurrentAllocation['Duty'])){
					echo '<td class="'.$arrCurrentAllocation['CellClass'].' box220x40">';
					echo $arrCurrentAllocation['Duty'];
					if (($arrCurrentAllocation['StartTimerowvalue'] > 0) && ($arrCurrentAllocation['EndTimerowvalue'] > 0)) {
						echo '<br>'.$arrCurrentAllocation['StartTime'].'-'.$arrCurrentAllocation['EndTime'];
					}
				}

				// Any Requests?
				if (isset($arrrequests)) {
					foreach ($arrrequests['defaults'] as $typeid => $value) {
						if (isset($arrrequests['requests'][$ddate][$typeid]['requests'][$arrAllocation['SchedulingPersonID']])) {
							$requestid = $arrrequests['requests'][$ddate][$typeid]['requests'][$arrAllocation['SchedulingPersonID']]['id'];
							echo '<div class="DutyCellBottomRight handcursor tipremote" requestid="'.$requestid.'">';
							echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
							echo '</div>';
						}
					}
				}

				echo '</td>';
				if ($i != 2) {
					$shiftendtime = $shiftstarttime = $nextshiftstarttime = 0;
					if (isset($arrAllocation[$weeksdays[$i]['week']][$weeksdays[$i]['day']]['Duty'][0]['EndTime']) && isset($arrAllocation[$weeksdays[$i + 1]['week']][$weeksdays[$i + 1]['day']]['Duty'][0]['StartTime']) && (strtoupper($arrCurrentAllocation['Duty'])!='U' && ($arrCurrentAllocation['Duty'] != "") && (strtoupper($arrCurrentAllocation['Duty'])!='ABSENT' && strtoupper($arrCurrentAllocation['Duty'])!='LEAVE' && strtoupper($arrCurrentAllocation['Duty']!='SICK')) && ($arrCurrentAllocation["isEditable"]==1) && ($arrCurrentAllocation['StartTime']!='00:00') && ($arrCurrentAllocation['EndTime']!='00:00')) && (($arrAllocation[$weeksdays[$i]['week']][$weeksdays[$i]['day']]['Duty'][0]['EndTime']!='00:00') && ($arrAllocation[$weeksdays[$i + 1]['week']][$weeksdays[$i + 1]['day']]['Duty'][0]['StartTime']!='00:00'))) {

						if (isset($arrAllocation[$weeksdays[$i]['week']][$weeksdays[$i]['day']]['Duty'][0]['EndTime'])) {
							$shiftendtimeHour = (float) ($arrAllocation[$weeksdays[$i]['week']][$weeksdays[$i]['day']]['Duty'][0]['EndTimerowvalue'] / 3600);
                			$shiftendtime = strlen(trim($shiftendtimeHour))== 1 ? "0".$shiftendtimeHour : $shiftendtimeHour;
						}

						if (isset($arrAllocation[$weeksdays[$i]['week']][$weeksdays[$i]['day']]['Duty'][0]['StartTime'])) {
							$shiftstarttimeHour = (float) ($arrAllocation[$weeksdays[$i]['week']][$weeksdays[$i]['day']]['Duty'][0]['StartTimerowvalue'] / 3600);
                			$shiftstarttime = strlen(trim($shiftstarttimeHour))== 1 ? "0".$shiftstarttimeHour : $shiftstarttimeHour;
						}

						if (isset($arrAllocation[$weeksdays[$i+1]['week']][$weeksdays[$i+1]['day']]['Duty'][0]['StartTime'])) {
							$nextshiftstartHour = (float) ($arrAllocation[$weeksdays[$i+1]['week']][$weeksdays[$i+1]['day']]['Duty'][0]['StartTimerowvalue'] / 3600);
                			$nextshiftstarttime = strlen(trim($nextshiftstartHour))== 1 ? "0".$nextshiftstartHour : $nextshiftstartHour;
						}

						$difference = $shiftstarttime > $shiftendtime /*Shift ended next day */ ? abs($shiftendtime - $nextshiftstarttime) : (24 - $shiftendtime) + $nextshiftstarttime;
						if ($difference > 11) {
							$class = 'LightGreen';
							$text = '> 11 Hours';
						} elseif ($difference == 11) {
							$class = 'LightOrange';
							$text = '= 11 Hours';
						} else {
							$class = 'LightRed';
							$text = '< 11 Hours';
						}
					} else {
						$class = 'DutyCellWorking';
						$text = '';
					}
			        echo '<td class="box90x40 smalltextcentre '.$class.'">'.$text;
					echo '</td>';
			    }  else {
					echo '<td class="DutyCellWorking">';
					echo '</td>';
					echo '<td class="DutyCellWorking">';
					echo '</td>';
				}
				$ddate = date ("Y-m-d", strtotime("+1 day", strtotime($ddate)));
				$i++;
			}
			echo '</tr>';
		}
	}
	echo '</tbody>';
}
echo '</table>';
echo '<br><br><br><br>';
?>
<script language="JavaScript" type="text/javascript">

// Create the tooltips only when document ready
 $(document).ready(function() {
   $('.tipremote').each(function() {
     $(this).qtip({
       content: {
         text: function(event, api) {
           $.ajax({
             url: 'page-includes/ajax-calls/requestinfo.php?id=' + api.elements.target.attr('requestid')
           })
           .then(function(content) {
             // Set the tooltip content upon successful retrieval
             api.set('content.text', content);
           },
           function(xhr, status, error) {
             // Upon failure... set the tooltip content to error
             api.set('content.text', status + ': ' + error);
           });
           return 'Loading...'; // Set some initial text
         }
       },
         position: {
         viewport: $(window)
         },
         style: 'qtip-rounded qtip-shadow qtip-dark'
         });
     });
});

$(function() {
  $( "#datepicker" ).datepicker({
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    dateFormat: "yy-mm-dd",
    defaultDate: "<?php echo $strMiddleDate?>",
    maxDate: "+21D",
    onSelect: function (dateText, inst) {
      ShowGridChecks('<?php echo $intTeamID?>', -1, dateText);
    }
  });
});

$(document).ready(function() {
  $("#gridchecks").tablesorter({
  });
});

</script>