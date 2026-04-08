<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/skillsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
require_once '../../../vendor/autoload.php';
include_once '../../function-includes/allocationsfunctionsstaffing.php';
include_once '../../function-includes/bootstrap.php';


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;



$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
if (isset($_REQUEST['id'])) {
  // Passed a week Number?
  $intWeekNumber = $_REQUEST['id'];
} else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
  } else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));
  }
}
$intAreaID = $_REQUEST['areaId'];
$startDate = $_REQUEST['startDate'];
$endDate = $_REQUEST['endDate'];
$scheduledPersonID = $_REQUEST['scheduledPersonID'] ? $_REQUEST['scheduledPersonID'] : 0;
$weekNumber = '';
$intDepartmentID = '';
if ($intAreaID == 0) {
  $strTeamName = 'All Areas';
} else {
  $strTeamName = $arrTeams[$intAreaID]['TeamName'];
}
$arrAllocations = ReadFreelanceAllocationsArea($intDepartmentID, $intAreaID, $weekNumber, $startDate, $endDate, $scheduledPersonID, array(), $userID);
$arrLines = array();
$intLine = 0;
$datesBetween = getDatesBetween($startDate, $endDate);

$startDateTime = new DateTime($startDate);
$endDateTime = new DateTime($endDate);
$interval = $startDateTime->diff($endDateTime);
$totalDays = $interval->days + 1;

if (isset($arrAllocations['Duties'])) {
  foreach ($arrAllocations['Duties'] as $strStaffNumber => $arrPerson) {
    foreach ($datesBetween as $strCurrDate) {
      if (isset($arrPerson['Duties'][$strCurrDate][0]) && $arrPerson['Duties'][$strCurrDate][0]['IsAreaUnderUser'] == 1) {
        $arrLines[$intLine][0] = date("d/m/Y", strtotime($strCurrDate));
        $arrLines[$intLine][1] = $arrPerson['Duties'][$strCurrDate][0]["DepartmentName"];
        $arrLines[$intLine][2] = $arrPerson['Name'];
        $arrLines[$intLine][3] = $arrPerson["StaffNumber"];
        $arrLines[$intLine][4] = $arrPerson['Duties'][$strCurrDate][0]['DutyName'];
        $arrLines[$intLine][5] = $arrPerson['Duties'][$strCurrDate][0]["Duration"];
        if ($arrPerson['Duties'][$strCurrDate][0]['PersonComments'] != '') {
          $arrLines[$intLine][6] = trim($arrPerson['Duties'][$strCurrDate][0]['PersonComments']);
        } else {
          $arrLines[$intLine][6] = "";
        }
        $arrLines[$intLine][7] = $arrPerson['Duties'][$strCurrDate][0]["AreaName"];
        $arrLines[$intLine][8] = $arrPerson['Duties'][$strCurrDate][0]["CostCode"];
      }
      $intLine++;
    }
  }
}

// Sort the array by date
usort($arrLines, function($a, $b) {
  $dateA = DateTime::createFromFormat('d/m/Y', $a[0]);
  $dateB = DateTime::createFromFormat('d/m/Y', $b[0]);
  return $dateA <=> $dateB;
});

// Create new PHPExcel object
$objPHPExcel = new Spreadsheet();


// Set document properties
$objPHPExcel->getProperties()->setCreator("Allocate")->setLastModifiedBy("Allocate")->setTitle("Freelance Usage for " . spindate($startDate) . " to " . spindate($endDate));

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()
  ->getStyle('A1:I1')
  ->applyFromArray(
      [
        'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'startColor' => ['rgb' => '4B72BF']
        ],
        'font'  => [
            'color' => ['rgb' => 'FFFFFF']
        ],
      ]
  );

  $styleArray = new Style();

	$styleArray = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'EEEEEE'],
    ],
	];


  $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->applyFromArray($styleArray);

  $objPHPExcel->getActiveSheet()->getStyle('A1:E1000')
    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

  $objPHPExcel->getActiveSheet()->getStyle('A1:E1000')
    ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

  $objPHPExcel->getActiveSheet()->getStyle('A1:E1000')->getAlignment()->setWrapText(true);

  $objPHPExcel->getActiveSheet()->getStyle('I1:E1000')
    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

  $objPHPExcel->getActiveSheet()->getStyle('I1:E1000')
    ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

  $objPHPExcel->getActiveSheet()->getStyle('I1:E1000')->getAlignment()->setWrapText(true);

  // The Names
  $strEndCell = 'B' . (count($arrLines) + 2);

    $styleArray_fill = new Style();

    $styleArray_fill = [
    'font' => [
        'color' => array('rgb' => '000000'),
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'top' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => array('rgb' => 'FFFFFF')
        ],
        'bottom' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => array('rgb' => 'FFFFFF')
        ],
        'left' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => array('rgb' => 'FFFFFF')
        ],
        'right' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => array('rgb' => 'FFFFFF')
        ],
    ],
  ];


  $objPHPExcel->getActiveSheet()->getStyle("A3:$strEndCell")->applyFromArray($styleArray_fill);

  //Foramt ColJ as text....
  $strEndCell = 'G' . (count($arrLines) + 2);
  $objPHPExcel->getActiveSheet()->getStyle("G3:$strEndCell")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

  $styleArray_font = new Style();

  $styleArray_font = array([
    	'font' => [
        'fillType' => Fill::FILL_SOLID,
        'color' => ['argb' => 'EEEEEE'],
        'size' => 15,
        'name' => 'Verdana',
        'bold' => true
    ],
	]);

  $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray_font);
  $objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
  $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
  $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Allocate - Freelance Usage for ' . spindate($startDate) . ' to ' . spindate($endDate));

  // Set The widths
  $objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);

  $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
  $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
  $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
  $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
  $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
  $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
  $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
  $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);

  // Do the Days and dates
  $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Date');
  $objPHPExcel->getActiveSheet()->setCellValue('B2', 'Team');
  $objPHPExcel->getActiveSheet()->setCellValue('C2', 'Person');
  $objPHPExcel->getActiveSheet()->setCellValue('D2', 'Staff Number');
  $objPHPExcel->getActiveSheet()->setCellValue('E2', 'Duty');
  $objPHPExcel->getActiveSheet()->setCellValue('F2', 'Duration');
  $objPHPExcel->getActiveSheet()->setCellValue('G2', 'Smartbook Ref');
  $objPHPExcel->getActiveSheet()->setCellValue('H2', 'Area');
  $objPHPExcel->getActiveSheet()->setCellValue('I2', 'Cost Code');

  $activeSheet = $objPHPExcel->getActiveSheet();
  $activeSheet->fromArray($arrLines, NULL, 'A3');

  // Redirect output to a client�s web browser (Excel5)
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="FreelanceUsageArea.xlsx"');
  header('Cache-Control: max-age=0');
  // If you're serving to IE 9, then the following may be needed
  header('Cache-Control: max-age=1');

  // If you're serving to IE over SSL, then the following may be needed
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
  header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
  header('Pragma: public'); // HTTP/1.0

  $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
  ob_end_clean();
  $objWriter->save('php://output');
