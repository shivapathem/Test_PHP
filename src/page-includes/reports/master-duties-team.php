<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';

$intTeamID = $_REQUEST['departmentid'];
$strDepartmentName = GetDepartmentNameFromID($intTeamID);

// ########################################## A few settings that affect page layout ##########################################
if (isset($_SESSION['screenwidth'])) {
  $intScreenWidth = $_SESSION['screenwidth'];
} else {
  $intScreenWidth = 1600;
}
// ########################################## END  ###########################################################################

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

// ######################################################## Work Out the week we are to view ################################################
if (isset($_REQUEST['WeekNumber'])) {
  // Passed a week Number?
  $intWeekNumber = $_REQUEST['WeekNumber'];
} else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
  } else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));
  }
}

$dteStartDate = datefromweek($intWeekNumber);
$dteEndtDate = date('Y-m-d', strtotime($dteStartDate . ' + 6 days'));
$arrHolidays = calculateBankHolidays(date("Y", strtotime($dteStartDate)));
$intPreviousWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate . ' - 7 days')));
$intNextWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate . ' + 7 days')));
$_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;

$arrMasterDuties = ReadMasterDuties($intTeamID, $intWeekNumber, $intWeekNumber);

echo '<span style="display:none" id="dutiestabletotal-' . $intTeamID . '"></span>';
echo '<table class="tablegreysmallnoborder" border="1" width="100%">';

echo '<tr height="50px">';
echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowMasterDutiesDepartment1(' . $intTeamID . ',"' . $intPreviousWeek . '")\';>&nbsp;&lt;&lt; Week ' . spinweek($intPreviousWeek) . '</span></td>';
echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowMasterDutiesDepartment1(' . $intTeamID . ',"' . $intNextWeek . '")\';>Week ' . spinweek($intNextWeek) . '&nbsp;&gt;&gt;</span></td>';
echo '<td width="120px" class="medtextbold" align="right">Choose a Date</td>';
echo '<td width="75px" class="medtextbold handcursor" align="left"><input type="hidden" id="datepicker' . $intTeamID . '"></td>';

echo '<td class="medtextbold" nowrap width="300px">';
echo $strDepartmentName . ' for Week ' . spinweek($intWeekNumber) . '</td>';
echo '</td>';

echo '<td class="medtextbold" nowrap>';
echo '<span id="staffeft-' . $intTeamID . '"></span></td>';
echo '</td>';

echo '<td class="medtextbold" nowrap>';
echo 'Night Hours Cost &pound;<span id="dutiestablenighttotal-' . $intTeamID . '"></span>';
echo '<br>Estimated Yearly Cost &pound;<span id="dutiestablenightyear-' . $intTeamID . '"></span>';
echo '<br><span id="dutiestablenightpercent-' . $intTeamID . '"></span>';

echo '</td>';

echo '<td class="medtextbold" nowrap>';
echo 'LNEMT &pound;<span id="dutiestablelnemttotal-' . $intTeamID . '"></span>';
echo '<br>Estimated Yearly Cost &pound;<span id="dutiestablelnemtyear-' . $intTeamID . '"></span>';
echo '<br>Assuming Journey Cost of &pound;60';
echo '</td>';

echo '<td class="medtextbold" nowrap>';
echo '</td>';
echo '</tr> ';
echo '</table>';

echo '<table class="tablesmall stripe" id="MasterDutiesdepartments">';
echo '<thead>';
echo '<tr>';
echo '<th>Duty Name</th>';
echo '<th>EFT</th>';
echo '<th>Start Time</th>';
echo '<th>End Time</th>';
echo '<th>Duration</th>';
echo '<th>Duration<br>(Less Meals)</th>';
echo '<th>Night Shift</th>';
echo '<th>Night Hours<br>Cost (&pound;)</th>';
echo '<th>Sat</th>';
echo '<th>Sun</th>';
echo '<th>Mon</th>';
echo '<th>Tue</th>';
echo '<th>Wed</th>';
echo '<th>Thur</th>';
echo '<th>Fri</th>';
echo '<th>Total</th>';
echo '<th>LNEMT<br>Cost (&pound;)</th>';
echo '<th>NC</th>';
echo '</tr>';
echo '</thead>';

echo '<tbody>';
if (isset($arrMasterDuties['Weeks'][$intWeekNumber])) {
  foreach ($arrMasterDuties['Weeks'][$intWeekNumber] as $intDutyID => $arrMasterDuty) {
    echo '<tr>';
    echo '<td style="text-align:left;">';
    echo $arrMasterDuty['DutyName'];
    echo '</td>';
    echo '<td style="text-align:center;">';
    echo number_format((($arrMasterDuty['WeekCount'] * $arrMasterDuty['DutyDurationLessMeal'] * 52) / 1456), 3, '.', '');
    echo '</td>';

    echo '<td style="text-align:center;">';
    echo $arrMasterDuty['StartMinutes'];
    echo '</td>';
    echo '<td style="text-align:center;">';
    echo $arrMasterDuty['EndMinutes'];
    echo '</td>';
    echo '<td style="text-align:center;">';
    echo $arrMasterDuty['DutyDuration'];
    echo '</td>';
    echo '<td style="text-align:center;">';
    echo $arrMasterDuty['DutyDurationLessMeal'];
    echo '</td>';
    echo '<td style="text-align:center;">';

    if ($arrMasterDuty['isNight'] == 0) {
      echo 'N';
    } else {
      echo 'Y';
    }

    echo '</td>';
    echo '<td style="text-align:center;">';
    // What's the cost
    $intNightCost = 0;
    if (isset($arrMasterDuty['NightCostLow'])) {
      $intNightCost = $arrMasterDuty['NightCostLow'];
    }
    if (isset($arrMasterDuty['NightCostHigh'])) {
      $intNightCost = $intNightCost + $arrMasterDuty['NightCostHigh'];
    }
    echo round($intNightCost * $arrMasterDuty['WeekCount']);
    echo '</td>';

    for ($i = 0; $i <= 6; $i++) {
      echo '<td style="text-align:center;">';
      echo $arrMasterDuty["$i"];
      echo '</td>';
    }

    echo '<td style="text-align:center;">';
    echo $arrMasterDuty['WeekCount'];
    echo '</td>';

    echo '<td style="text-align:center;">';
    echo $arrMasterDuty['LNEMT'] * $arrMasterDuty['WeekCount'] * 60;
    echo '</td>';

    echo '<td style="text-align:center;">';
    echo $arrMasterDuty['isNight'] * $arrMasterDuty['WeekCount'];
    echo '</td>';

    echo '</tr>';
  }
}
echo '</tbody>';
echo '<tfoot>';
echo '<tr>';
echo '<th>Totals</th>';
echo '<th></th>';
echo '<th></th>';
echo '<th></th>';
echo '<th></th>';
echo '<th></th>';
echo '<th></th>';
echo '<th></th>';
echo '<th class="footer"></th>';
echo '<th class="footer"></th>';
echo '<th class="footer"></th>';
echo '<th class="footer"></th>';
echo '<th class="footer"></th>';
echo '<th class="footer"></th>';
echo '<th class="footer"></th>';
echo '<th class="footer"></th>';
echo '<th class="footer"></th>';
echo '<th></th>';
echo '</tr>';
echo '</tfoot>';
echo '</table>';
?>
<style>
  .footer {
    text-align: center !important;
  }
</style>
<script type="text/javascript">
  $(document).ready(function() {
    var table = $("#MasterDutiesdepartments").DataTable({
      destroy: true,
      paging: false,
      scrollY: 1000,
      scrollCollapse: true,
      info: false,
      stateSave: true,
      deferRender: true,
      order: [
        [2, "desc"]
      ],

      "initComplete": function(settings, json) {
        ResizeMasterDutiesTable();
        $('#loading').hide();
      },
      "footerCallback": function(row, data, start, end, display) {
        var api = this.api(),
          data;

        // Remove the formatting to get integer data for summation
        var intVal = function(i) {
          return typeof i === 'string' ?
            i.replace(/[\$,]/g, '') * 1 :
            typeof i === 'number' ?
            i : 0;
        };
        pageTotal = api
          .column(1, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        nightTotal = api
          .column(7, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        lnemtTotal = api
          .column(16, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);

        SatTotal = api
          .column(8, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(8).footer()).html(
          SatTotal
        );

        SunTotal = api
          .column(9, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(9).footer()).html(
          SunTotal
        );

        MonTotal = api
          .column(10, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(10).footer()).html(
          MonTotal
        );

        TueTotal = api
          .column(11, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(11).footer()).html(
          TueTotal
        );

        WedTotal = api
          .column(12, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(12).footer()).html(
          WedTotal
        );

        ThuTotal = api
          .column(13, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(13).footer()).html(
          ThuTotal
        );

        FriTotal = api
          .column(14, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(14).footer()).html(
          FriTotal
        );

        CountShiftsTotal = api
          .column(15, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(15).footer()).html(
          CountShiftsTotal
        );

        NightTotal = api
          .column(17, {
            page: 'current'
          })
          .data()
          .reduce(function(a, b) {
            return intVal(a) + intVal(b);
          }, 0);
        $(api.column(17).footer()).html(
          NightTotal
        );
        $('#dutiestabletotal-<?php echo $intTeamID ?>').html($.number(pageTotal, 1));
        $('#dutiestablenighttotal-<?php echo $intTeamID ?>').html($.number(nightTotal, 0));
        $('#dutiestablenightyear-<?php echo $intTeamID ?>').html($.number(nightTotal * 52, 0) + '&nbsp;&nbsp;');
        $('#dutiestablelnemttotal-<?php echo $intTeamID ?>').html($.number(lnemtTotal, 0));
        $('#dutiestablelnemtyear-<?php echo $intTeamID ?>').html($.number(lnemtTotal * 52, 0));
        $('#dutiestablenightpercent-<?php echo $intTeamID ?>').html($.number(NightTotal / CountShiftsTotal * 100, 1) + '% of ' + CountShiftsTotal + ' Shifts (' + NightTotal + ' Nights)&nbsp;&nbsp;');
      }
    });
    table.column(17).visible(false, false);
    yadcf.init(table, [{
        column_number: 0,
        filter_type: 'text'
      },
      {
        column_number: 6,
        select_type: 'select',
        select_type_options: {
          no_results_text: 'Can\'t find ->',
          search_contains: true
        }
      },
    ]);

    if ($.cookie("#MasterDutiesdepartments") !== null) {
      scrollPos = $.cookie("#MasterDutiesdepartments");
      $("#MasterDutiesdepartments").closest('.dataTables_scrollBody').scrollTop(scrollPos);
    };
    GetStaffEFT($('#yadcf-filter--MasterDutiesdepartments-0').val(), $('#yadcf-filter--MasterDutiesdepartments-1').val());
    $('.ui-tabs-nav').width("95%");
  })

  $('#yadcf-filter--MasterDutiesdepartments-0').change(function() {
    GetStaffEFT($(this).val(), $('#yadcf-filter--MasterDutiesdepartments-1').val());
  });
  $("#yadcf-filter--MasterDutiesdepartments-0-reset").button().click(function() {
    GetStaffEFT(-1, $('#yadcf-filter--MasterDutiesdepartments-1').val());
  });

  $('#yadcf-filter--MasterDutiesdepartments-1').keyup(function() {
    GetStaffEFT($('#yadcf-filter--MasterDutiesdepartments-0').val(), $(this).val());
  });
  $("#yadcf-filter--MasterDutiesdepartments-1-reset").button().click(function() {
    GetStaffEFT($('#yadcf-filter--MasterDutiesdepartments-0').val(), '');
  });

  $("#MasterDutiesdepartments").closest('.dataTables_scrollBody').on('scroll', function() {
    var currpos = $("#MasterDutiesdepartments").closest('.dataTables_scrollBody').scrollTop();
    $.cookie("#MasterDutiesdepartments", currpos);
  });

  $(function() {
    $("#datepicker<?php echo $intTeamID ?>").datepicker({
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      showOtherMonths: 'true',
      selectOtherMonths: 'true',
      firstDay: '6',
      gotoCurrent: 'true',
      changeMonth: true,
      changeYear: true,
      dateFormat: "yy-mm-dd",
      defaultDate: "<?php echo $dteStartDate ?>",
      onSelect: function(dateText, inst) {
        var currentdate = dateText;
        $.post("page-includes/allocations/allocations-set-week-from-date.php", {
            date: currentdate
          },
          function(data, status) {
            {
              ShowMasterDutiesDepartment1(<?php echo $intTeamID ?>, data)
            }
          });
      }
    });
  });

  $(window).resize(function() {
    ResizeMasterDutiesTable();
  })

  function ResizeMasterDutiesTable() {
    if ($("#MasterDutiesdepartments").length > 0) {
      var offset = ($("#MasterDutiesdepartments").offset().top);
      var windowheight = $(window).height() - offset - 100;
      $('.dataTables_scrollBody:has(#MasterDutiesdepartments)').height(windowheight + 'px');
      $('#MasterDutiesdepartments').dataTable().fnAdjustColumnSizing();
    }
  }

  function GetStaffEFT(filter, filter1) {
    $.post("page-includes/reports/master-duty-department-staffeft.php", {
        weeknumber: <?php echo $intWeekNumber ?>,
        filter: filter,
        filter1: filter1,
        department: <?php echo $intTeamID ?>,
        requiredeft: $('#dutiestabletotal-<?php echo $intTeamID ?>').text()
      },
      function(data, status) {
        $('#staffeft-<?php echo $intTeamID ?>').html(data);
      })
  }

  function ShowMasterDutiesDepartment1(DepartmentID, weeknumber) {
    $.ajax({
      type: 'POST',
      url: 'page-includes/reports/master-duties-team.php',
      data: {
        'departmentid': DepartmentID,
        'WeekNumber': weeknumber
      },
      success: function(data) {
        $('#MDReportTabs-0').html(data);
      },
      error: function(data) {
        alert('some error found in master duties team call.');
      }
    });
  }
</script>