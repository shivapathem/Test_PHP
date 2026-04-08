<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;
$intTeamId = $_REQUEST['teamId'];
$arrAllocLeaveTypes = GetLeaveAllocateTypes();

if (isset( $_REQUEST['year'])) {
  $intLeaveYear = $_REQUEST['year'];
} else {
  if (isset($_SESSION['allocations']["leave"]['curentleaveyear'])) {
    $intLeaveYear = $_SESSION['allocations']["leave"]['curentleaveyear'];
  }  else {
    $intLeaveYear = date("Y", strtotime("-3 months"));
  }
}
$_SESSION['allocations']["leave"]['curentleaveyear'] = $intLeaveYear;
$intPercentofYear = (GetPercentageOfYear($intLeaveYear));
$intCurrentYear  = GetCurrentLeaveYearGeneric();
$strTeamName = GetDepartmentNameFromID($intTeamId);
$activeScheduledPeople = (isset($_COOKIE["activescheduledpeopled_r"])) ? $_COOKIE["activescheduledpeopled_r"] : 0;
$additionalflag =  0;
$arrLeaveCredits = GetLeaveCreditsSummary($intTeamId, $intLeaveYear, $arrAllocLeaveTypes,$activeScheduledPeople,$additionalflag);
$arrLeaveTaken = GetLeaveApprovedTakenSummary($intTeamId, $intLeaveYear, $arrAllocLeaveTypes);
$intLeaveTypesCount = 0;

$strHeader = "Leave Remaining for '$strTeamName' - Leave Year $intLeaveYear/".($intLeaveYear + 1)." - $intPercentofYear% through the Leave Year";
  foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    if ($arrAllocLeaveType['IncludeInReports'] == 1) {
        $intLeaveTypesCount++;
    }
  }

  $intLine = 0;
  foreach ($arrLeaveCredits as $schdefdulledPerson => $arrCreditsPerson) {
    $strStaffNumber = $arrCreditsPerson['StaffNumber'];
    $intTotalCredit = 0;
    $intTotalTaken = 0;
    $arrLines[$intLine][0] = $arrCreditsPerson['Name'];
    $arrLines[$intLine][1] = $arrCreditsPerson['EFT'];
    $intRow = 3;
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      if ($arrAllocLeaveType['IncludeInReports'] == 1) {
        if (isset($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']])) {
          $intCredit = number_format(($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']]),2, '.', '');
        } else {
          $intCredit = 0;
        }
        if ($arrAllocLeaveType['CalcInReports'] == 1) {
          $intTotalCredit = $intTotalCredit + $intCredit;
        }
        if (isset($arrLeaveTaken[$schdefdulledPerson][$LTID])) {
          $intTaken = number_format(($arrLeaveTaken[$schdefdulledPerson][$arrAllocLeaveType['ID']]),2, '.', '');
        } else {
          $intTaken = 0;
        }

        if ($arrAllocLeaveType['CalcInReports'] == 1) {
          $intTotalTaken = $intTotalTaken + $intTaken;
        }

        $arrLines[$intLine][$intRow] = $intCredit - $intTaken;
        $intRow++;
      }
    }
    $intLine++;
}

$strLastCol = chr($intLeaveTypesCount + 1 + 65);
$objPHPExcel = new Spreadsheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Allocate")
							 ->setLastModifiedBy("Allocate")
							 ->setTitle("Leave Report");

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()
->getStyle('A1:'.$strLastCol.'1')
->applyFromArray (
    [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'AD0102']
        ],
       'alignment' => [
           'horizontal' => Alignment::HORIZONTAL_CENTER,
           'vertical' => Alignment::VERTICAL_CENTER
       ]

    ]
);

$strEndCell = $strLastCol.(count($arrLines) + 4);
$objPHPExcel->getActiveSheet()
->getStyle("A2:$strEndCell")
->applyFromArray (
    [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'EEEEEE']
        ],
        'font'  => [
            'color' => ['rgb' => '000000']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'FFFFFF']
            ]
        ]
    ]
);

$objPHPExcel->getActiveSheet()
->getStyle("D2:$strEndCell")
->applyFromArray (
    [
       'alignment' => [
           'horizontal' => Alignment::HORIZONTAL_CENTER,
           'vertical' => Alignment::VERTICAL_CENTER
       ]
    ]
);

$styleArray = [
    'font'  => [
        'bold'  => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size'  => 14,
        'name'  => 'Verdana'
    ]
];

$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->mergeCells('A1:'.$strLastCol.'1');
$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
$objPHPExcel->getActiveSheet()->setCellValue('A1', $strHeader);

// Set The widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$strColumn = "D";
for ($i=0; $i<= $intLeaveTypesCount; $i++) {
 $objPHPExcel->getActiveSheet()->getColumnDimension($strColumn)->setWidth(20);
 $strColumn++;
}
// Do the Days and dates
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Name');
$objPHPExcel->getActiveSheet()->setCellValue('B2', 'EFT');
// The types of leave
$strColumn = "C";
  foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    if ($arrAllocLeaveType['IncludeInReports'] == 1) {
      $strLeaveType = $arrAllocLeaveType['Description'];
      $objPHPExcel->getActiveSheet()->setCellValue($strColumn.'2', $strLeaveType);
      $strColumn++;
    }
  }
$activeSheet = $objPHPExcel->getActiveSheet();
$activeSheet->fromArray($arrLines, NULL, 'A3');
// Redirect output to a client�s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="LeaveRemaining.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
$objWriter->save('php://output');
