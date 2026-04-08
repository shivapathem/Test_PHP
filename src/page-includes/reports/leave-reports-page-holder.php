<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once __DIR__ . '/../../function-includes/user-scheduling-team-list.php';

echo '<link rel="stylesheet" href="mvc-app/node_modules/select2/dist/css/select2.min.css">';
echo '<script src="mvc-app/node_modules/select2/dist/js/select2.min.js"></script>';

$strUser = $strUser ?? '';
$teamOptions = getSchedulingTeamList(auth()->user()->defaultTeamId, 'reports-policy', 'viewBasicReports');

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
echo '<br><h1 style="text-align: center;">Leave Reports</h1><br><br>';
echo '</div>';

if (!isset($teamOptions)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Leave groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}


echo '<div id="LeaveReportTabsDepts">
Scheduling Team <select id="schedulingTeamSelect" class="select2 selectDesign" tabindex="0"  onChange="GetTabContent(this.value);" >';
echo $teamOptions;
echo '</select>
<div id="LeaveReportTabs">
  <div>
    <div class="LeaveReportTabs">
    <ul>
      <li><a href="#LeaveReportTabs-0" tabindex="0" role="button">Leave Remaining</a></li>
      <li><a href="#LeaveReportTabs-1" tabindex="0" role="button">Percentage Leave Utilisation</a></li>
      <li><a href="#LeaveReportTabs-2" tabindex="0" role="button">Leave Summary</a></li>
      <li><a href="#LeaveReportTabs-3" tabindex="0" role="button">Allocate Leave Balance</a></li>
      <li><a href="#LeaveReportTabs-4" tabindex="0" role="button">Leave Requests</a></li>
    </ul>
    <div id="LeaveReportTabs-0">
    </div>
    <div id="LeaveReportTabs-1">
    </div>
    <div id="LeaveReportTabs-2">
    </div>
    <div id="LeaveReportTabs-3">
    </div>
    <div id="LeaveReportTabs-4">
    </div>
    </div>
  </div>
</div>
</div>';

?>
<script type="text/javascript">
window.LeaveReportDatatableAccessibility = window.LeaveReportDatatableAccessibility || (function () {
  function getSortControls($container) {
    return $container.find(
      'thead th .dt-column-order,' +
      'thead th button.dt-column-order,' +
      'thead th span[role="button"].dt-column-order,' +
      'thead th span[role="button"][aria-label*="sort"],' +
      'thead th span[role="button"][aria-label*="Activate to sort"]'
    );
  }

  function applyHeaderAccessibilityFixes(dtInstance) {
    if (!dtInstance || !dtInstance.table) {
      return;
    }

    var $container = $(dtInstance.table().container());
    var $sortControls = getSortControls($container);

    $sortControls.off('focusin.srfix focusout.srfix');
    $sortControls.attr('aria-hidden', 'true');

    $sortControls.each(function () {
      var $control = $(this);
      if (($control.prop('tagName') || '').toLowerCase() !== 'button') {
        var tabindex = $control.attr('tabindex');
        if (!tabindex || tabindex === '-1') {
          $control.attr('tabindex', '0');
        }
      }

      var originalLabel = $control.attr('aria-label');
      if (originalLabel && !$control.attr('data-sr-label')) {
        $control.attr('data-sr-label', originalLabel);
      }
    });

    $sortControls.on('focusin.srfix', function () {
      var $control = $(this);
      $control.removeAttr('aria-hidden');

      var label = $control.attr('data-sr-label');
      if (label) {
        $control.attr('aria-label', label);
      }
    });

    $sortControls.on('focusout.srfix', function () {
      var $control = $(this);
      $control.attr('aria-hidden', 'true');
      $control.removeAttr('aria-label');
    });
  }

  function addAriaLabelsToFilterClearButtons(tableId, filterSetting) {
    var clearButtonLabels = {};

    $.each(filterSetting || [], function (_, filter) {
      if (filter.clear_button_label) {
        clearButtonLabels[filter.column_number] = filter.clear_button_label;
      }
    });

    setTimeout(function () {
      $('#' + tableId).find('.yadcf-filter-reset-button').each(function () {
        var $button = $(this);
        var $th = $button.closest('th');
        var $parentTr = $th.parent('tr');
        var thIndex = $parentTr.find('th').index($th);

        if (!clearButtonLabels[thIndex]) {
          return;
        }

        $button.attr('aria-label', clearButtonLabels[thIndex]);
        $button.attr('title', clearButtonLabels[thIndex]);
        $button.off('focusin.srfix focusout.srfix');
        $button.attr('aria-hidden', 'true');

        if (!$button.attr('tabindex')) {
          $button.attr('tabindex', '0');
        }

        $button.on('focusin.srfix', function () {
          $(this).removeAttr('aria-hidden');
        });

        $button.on('focusout.srfix', function () {
          $(this).attr('aria-hidden', 'true');
        });
      });
    }, 150);
  }

  function addAriaLabelledbyToYadcfTextInputs(tableId) {
    setTimeout(function () {
      var $table = $('#' + tableId);
      var $thead = $table.find('thead');

      if (!$thead.length) {
        return;
      }

      var $labelRow = $thead.find('tr').first();
      if (!$labelRow.length) {
        return;
      }

      $thead.find('tr').each(function () {
        $(this).find('th').each(function (colIdx) {
          var $th = $(this);
          var $input = $th.find('input.yadcf-filter');

          if (!$input.length) {
            return;
          }

          var labelId = tableId + '-col-' + colIdx + '-label';

          if (!document.getElementById(labelId)) {
            var $labelTh = $labelRow.find('th').eq(colIdx);
            if ($labelTh.length) {
              var headerText = $labelTh.clone()
                .find('input, select, button, .yadcf-filter-wrapper, .yadcf-filter, .yadcf-filter-reset-button')
                .remove()
                .end()
                .text()
                .replace(/\s+/g, ' ')
                .trim();

              if (!headerText) {
                headerText = 'Filter';
              }

              $('<span/>', {
                id: labelId,
                text: headerText,
                'aria-hidden': 'true',
                style: 'position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;'
              }).prependTo($labelTh);
            }
          }

          $('#' + labelId).attr('aria-hidden', 'true');
          $input.attr('aria-labelledby', labelId);
          $input.off('focusin.srlabel focusout.srlabel');
          $input.on('focusin.srlabel', function () {
            $('#' + labelId).removeAttr('aria-hidden');
          });
          $input.on('focusout.srlabel', function () {
            $('#' + labelId).attr('aria-hidden', 'true');
          });
        });
      });
    }, 200);
  }

  function hideYadcfSelectValuesUntilFocus(tableId) {
    setTimeout(function () {
      $('#' + tableId).find('select.yadcf-filter').each(function () {
        var $select = $(this);
        $select.off('focusin.srselect focusout.srselect');
        $select.attr('aria-hidden', 'true');

        if (!$select.attr('tabindex')) {
          $select.attr('tabindex', '0');
        }

        $select.on('focusin.srselect', function () {
          $(this).removeAttr('aria-hidden');
        });
        $select.on('focusout.srselect', function () {
          $(this).attr('aria-hidden', 'true');
        });
      });
    }, 200);
  }

  return {
    applyHeaderAccessibilityFixes: applyHeaderAccessibilityFixes,
    addAriaLabelsToFilterClearButtons: addAriaLabelsToFilterClearButtons,
    addAriaLabelledbyToYadcfTextInputs: addAriaLabelledbyToYadcfTextInputs,
    hideYadcfSelectValuesUntilFocus: hideYadcfSelectValuesUntilFocus
  };
})();

$(document).ready(function(){
  $(function() {
    $("#LeaveReportTabsDepts").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#LeaveReportTabsDepts").tabs( "option", "active" );
         GetTabContent($('#schedulingTeamSelect').val(), SelectedTab);
         adjustTablesAfterPageShow(ui.newPanel);
      }
    });
  });

 $(function() {
    $("#LeaveReportTabs").tabs({
      disabled: [3]
    });
  });
  setTimeout(function(){ GetTabContent ($('#schedulingTeamSelect').val(), 0); }, 1000);
});

function adjustTablesAfterPageShow(container) {
      $(container)
          .find('table.dataTable')
          .each(function () {
              if ($.fn.DataTable.isDataTable(this)) {
                  $(this).DataTable().columns.adjust();
              }
          });
}

function GetTabContent (teamID, SelectedTab) {
  SelectedTab = SelectedTab ? SelectedTab : $("#LeaveReportTabs").tabs( "option", "active" );
  switch (SelectedTab) {
  case 0:
   ShowLeaveToDate(teamID);
   break;
  case 1:
   ShowLeaveUtilisation(teamID);
   break;
  case 4:
   ShowLeaveReportRequests(teamID);
   break;
  case 2:
    ShowLeaveReportBalances(teamID);
    break;
  }
}

function ShowLeaveToDate(teamID, year) {
  $.ajax({
        type: 'POST',
        url: "page-includes/reports/leave-report-to-date.php",
        data: {
            'teamID': teamID,
            'year' :year
        },
        success: function (data) {
          $('#LeaveReportTabs-0').html(data);
        },
        error:function (data) {
            alert('some error found in leave-report call.');
        }
    });
}

function ShowLeaveUtilisation(teamID, year) {
  $.ajax({
        type: 'POST',
        url: "page-includes/reports/leave-report-utilisation.php",
        data: {
            'teamID': teamID,
            'year':year
        },
        success: function (data) {
          $('#LeaveReportTabs-1').html(data);
        },
        error:function (data) {
            alert('some error found in leave-report call.');
        }
    });
}

function ShowLeaveReportRequests(teamID) {
  $.ajax({
        type: 'POST',
        url: "page-includes/reports/leave-report-requests.php",
        data: {
            'teamID': teamID
        },
        success: function (data) {
          $('#LeaveReportTabs-4').html(data);
        },
        error:function (data) {
            alert('some error found in leave-report call.');
        }
    });
}

function ShowLeaveBalances(login, year, teamID) {
  $.ajax({
        type: 'POST',
        url: "page-includes/leave/leave-allocate.php",
        data: {
            'user': login,
            'year': year,
            'teamID':teamID
        },
        success: function (data) {
          $('#LeaveReportTabs').tabs( "enable", 3 );
          $('#LeaveReportTabs').tabs( "option", "active", 3 );
          $('#LeaveReportTabs-3').html(data);
        },
        error:function (data) {
            alert('some error found in leave-report call.');
        }
    });
}

function ShowLeaveReportBalances(teamID, year) {
  $.ajax({
        type: 'POST',
        url: "page-includes/reports/leave-report-allocate-balances.php",
        data: {
            'teamID': teamID,
            'year': year
        },
        success: function (data) {
          $('#LeaveReportTabs-2').html(data);;
        },
        error:function (data) {
            alert('some error found in leave-report call.');
        }
    });
}
$("#schedulingTeamSelect").select2({
    width: "200px",
    allowClear: false
});

<?php
if (empty($_REQUEST['teamID'])) {
  echo "GetTabContent(0)";
}
?>
</script>


