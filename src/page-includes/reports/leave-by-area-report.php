<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../class-includes/reports/leave/LeaveAreaReport.php';
include_once '../../page-includes/admin/divisions/process/classDivisionalAdmin.php';
$leaveYear = $_REQUEST['leaveYear'];
$leaveReportDatas = (new LeaveAreaReoprt)->getLeaveAreaReport($leaveYear);
$areaObject = new ClassDivisionalAdmin();
$areaList = $areaObject->getDivisionsListBasedOnAreaReportRole();
function MRound($num,$parts) {
  $res = $num * $parts;
  $res = round($res);
  return $res /$parts;
}
?>
<link href="../styles/reports/leave-area-report/leaveAreaReport.css" rel="stylesheet">
<div id="leave-by-area-report" style="padding: 2px; background:#eeeeee">
  <div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%"><h1 style="text-align: center;"><br>Leave by Area<br><br><br></h1></div>

  <div class="filterContainerFlex">
    <span class="heading">Leave by Area Report</span>
    <div class="leave-by-report-filter-container">
      <div class="fields">
          <label class="labelTxtAlign" for="leaveYearInput">Leave Year</label>
          <select id="leaveYearInput" name="leaveYearInput" style="width: 75px;">
            <?php 
            $finacialYear = ( date('m') < 4) ? date('Y') - 1 : date('Y');
            for($i = $finacialYear - 7; $i <= $finacialYear + 7;  $i++) { 
              echo  '<option value=' . $i . ' ' . ($leaveYear == $i ? 'selected' : '') .'>' . $i .'<option>';
             } ?>
          </select>
      </div>
      <button type="text" class="leave-by-report-btn" onclick="getLeaveByReportData();">Extract</button>

      <button type="text" class="leave-by-report-btn" onclick="createLeaveByAreaDocs();"><img src="./images/excel.svg" class="exportIcon">Create</button>
    </div>
  </div>

  <form method="post" id="leave-by-area-export-form" action="page-includes/reports/leave-by-area-report-excel-export.php">
    <input type="hidden" id="exportData" name="exportData">
  </form>

  <div>
    <table style="width: 100%;" class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="leave-area-report">
      <thead>
        <tr>
          <th>Area</th>
          <th>Cost Code</th>
          <th>Scheduling Team Name</th>
          <th>NetLogin</th>
          <th>Staff Number</th>
          <th>Display Name</th>
          <th>EFT</th>
          <th>Approx. Days to Take</th>
          <th>Annual Balance</th>
          <th>Annual Leave (above Carry Over)</th>
          <th>Annual Leave Carry Over Limit</th>
          <th>PHL Balance</th>
          <th>PHL (above Carry Over)</th>
          <th>PHL Carry Over Limit</th>
          <th>Additional</th>
          <th>Additional (above Carry Over)</th>
          <th>EDP TOIL Balance</th>
          <th>Under 11 TOIL Balance</th>
          <th>Other Balance</th>
        </tr>
      </thead>
        <tbody>
          <?php
          $neturalColour = ['background:#FFEB9C', 'font:#9C6500'];
          $badColour = ['background:#FFC7CE', 'font:#9C0006'];
          foreach($leaveReportDatas as $leaveReportData) {
            $leaveReportData['AnnualLeaveCarryOverLimit'] = !empty($leaveReportData['AnnualLeaveCarryOverLimit']) ? MRound($leaveReportData['AnnualLeaveCarryOverLimit'], 4) : 0;
            $leaveReportData['Annual Balance'] = MRound($leaveReportData['Annual Balance'], 4); 
            $leaveReportData['Annual Leave (above Carry Over)'] = (!empty($leaveReportData['Annual Leave (above Carry Over)']) ? MRound($leaveReportData['Annual Leave (above Carry Over)'], 4) : 0);   
            $leaveReportData['PHL Balance'] = MRound($leaveReportData['PHL Balance'], 4);
            $leaveReportData['PHL (above Carry Over)'] = (!empty($leaveReportData['PHL (above Carry Over)']) ? MRound($leaveReportData['PHL (above Carry Over)'], 4) : 0);
            $leaveReportData['PHLCarryOverLimit'] = MRound($leaveReportData['PHLCarryOverLimit'], 4);
            $leaveReportData['Additional'] = MRound($leaveReportData['Additional'], 4);
            $leaveReportData['Additional (above Carry Over)'] = MRound($leaveReportData['Additional (above Carry Over)'], 4);
            $leaveReportData['EDP TOIL Balance'] = MRound($leaveReportData['EDP TOIL Balance'], 4);
            $leaveReportData['Under 11 TOIL Balance'] = MRound($leaveReportData['Under 11 TOIL Balance'], 4);
            $leaveReportData['Other Balance'] = MRound($leaveReportData['Other Balance'], 4);
            
            $alaco = !empty($leaveReportData['Annual Leave (above Carry Over)']) ? $leaveReportData['Annual Leave (above Carry Over)'] : 0;
            $eft = !empty($leaveReportData['EFT']) ? $leaveReportData['EFT'] : 0;
            $phlaco = !empty($leaveReportData['PHL (above Carry Over)']) ? $leaveReportData['PHL (above Carry Over)'] : 0;
            $aaco = !empty($leaveReportData['Additional (above Carry Over)']) ? $leaveReportData['Additional (above Carry Over)'] : 0;
            
            //Annual Leave (above Carry Over)
            $alacoColor = [];
            if($alaco  > 0 && ($alaco > (35 * $eft))) {
              $alacoColor = $badColour;
            }
            if($alaco  > 0 && ($alaco <= (35 * $eft))) {
              $alacoColor = $neturalColour;
            }

            //PHL (above Carry Over)
            $phlacoColor = [];
            if($phlaco  > 0 && ($phlaco > (15.75 * $eft))) {
              $phlacoColor = $badColour;
            }
            if($phlaco  > 0 && ($phlaco <= (15.75 * $eft))) {
              $phlacoColor = $neturalColour;
            }

            //Additional (above Carry Over)
            $aacoColor = [];
            if($aaco  > 0) {
              $aacoColor = $badColour;
            }
            echo '<tr>
                    <td>' . $leaveReportData['Area'] . '</td>
                    <td>' . $leaveReportData['CostCode'] . '</td>
                    <td>' . $leaveReportData['Scheduling Team Name'] . '</td>
                    <td>' . (!empty($leaveReportData['Netlogin']) ? ($leaveReportData['Netlogin']) : '') . '</td>
                    <td>' . $leaveReportData['Staff Number'] . '</td>
                    <td>' . $leaveReportData['Display Name'] . '</td>
                    <td>' . (!empty($leaveReportData['EFT']) ? number_format($leaveReportData['EFT'], 2) : '') . '</td>
                    <td>' . round($leaveReportData['Approx. Days to Take']) . '</td>
                    <td>' . number_format($leaveReportData['Annual Balance'], 2) . '</td>
                    <td style="' . ($alaco <= 0 ? 'color: transparent !important;' : '') . implode(';', $alacoColor) . '">' . (!empty($leaveReportData['Annual Leave (above Carry Over)']) ? number_format($leaveReportData['Annual Leave (above Carry Over)'], 2) : '') . '</td>
                    <td>' . number_format($leaveReportData['AnnualLeaveCarryOverLimit'], 2) . '</td>
                    <td>' . number_format($leaveReportData['PHL Balance'], 2) . '</td>
                    <td style="' . ($phlaco <= 0 ? 'color: transparent !important;' : '')  . implode(';', $phlacoColor) . '">' . (!empty($leaveReportData['PHL (above Carry Over)']) ? number_format($leaveReportData['PHL (above Carry Over)'], 2) : '')  . '</td>
                    <td>' . number_format($leaveReportData['PHLCarryOverLimit'], 2) . '</td>
                    <td>' . number_format($leaveReportData['Additional'], 2) . '</td>
                    <td style="' . ($aaco <= 0 ? 'color: transparent !important;' : '') . implode(';', $aacoColor) . '">' . number_format($leaveReportData['Additional (above Carry Over)'], 2) . '</td>
                    <td>' . number_format($leaveReportData['EDP TOIL Balance'], 2) . '</td>
                    <td>' . number_format($leaveReportData['Under 11 TOIL Balance'], 2) . '</td>
                    <td>' . number_format($leaveReportData['Other Balance'], 2) . '</td>
              </tr>';
          }
          ?>
      </tbody>
    </table>
  </div>
</div>
<input type="hidden" id="user-area-list" value="<?php echo htmlentities(json_encode($areaList)) ?>">
<script>
$(document).ready(function () {
  $('#leaveYearInput').chosen();
  initializeLeaveAreaTable();
});
var leaveReportDataTable;
 function initializeLeaveAreaTable() {
  leaveReportDataTable = $("#leave-area-report").DataTable({
        lengthChange: false,
        paging: false,
        iDisplayLength: 5,
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        scrollX: true,
        "scrollY": "55vh",
        dom: 'Bfrtip'
    });
    let areaListSelect = []; 
    let areaList = JSON.parse($('#user-area-list').val());
    areaList.forEach(function(item) {
      areaListSelect.push(item.DivisionName);
    });
    yadcf.init(leaveReportDataTable, [
        {
            column_number: 0,
            filter_type: 'select',
            data: areaListSelect,
            filter_match_mode: 'exact'
        },
        {
            column_number: 1,
            filter_type: 'text'
        },
        {
            column_number: 2,
            filter_type: 'text'
        },
        {
            column_number: 3,
            filter_type: 'text'
        },
        {
            column_number: 4,
            filter_type: 'text'
        },
        {
            column_number: 5,
            filter_type: 'text'
        },
        {
            column_number: 6,
            filter_type: 'text'
        },
        {
            column_number: 7,
            filter_type: 'text'
        },
        {
            column_number: 8,
            filter_type: 'text'
        },
        {
            column_number: 9,
            filter_type: 'text'
        },
        {
            column_number: 10,
            filter_type: 'text'
        },
        {
            column_number: 11,
            filter_type: 'text'
        },
        {
            column_number: 12,
            filter_type: 'text'
        },
        {
            column_number: 13,
            filter_type: 'text'
        },
        {
            column_number: 14,
            filter_type: 'text'
        },
        {
            column_number: 15,
            filter_type: 'text'
        },
        {
            column_number: 16,
            filter_type: 'text'
        },
        {
            column_number: 17,
            filter_type: 'text'
        },
        {
            column_number: 18,
            filter_type: 'text'
        }
    ]);
  }

  function getLeaveByReportData() {
    if(/^\d{4}$/.test($('#leaveYearInput').val()) ==  false) {
      alert('Invalid year');
      return false;
    }
    ShowLeaveByAreaReport($('#leaveYearInput').val());
  }

  function createLeaveByAreaDocs() {
    $('#exportData').val(JSON.stringify(leaveReportDataTable.rows({search:'applied'}).data().toArray()));
    $('#leave-by-area-export-form').submit()
  }
</script>