<?php
session_start();
require_once '../../../vendor/autoload.php';
include_once '../../function-includes/bootstrap.php';
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/reports-functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;

$intTeamID = $_REQUEST['teamId'];
$strStartDate = $_REQUEST['sDate'];
$strEndDate = $_REQUEST['eDate'];
if (isset($_SESSION["reports"]["sortcodesick$intTeamID"])) {
  // Is the session set?
  $strSortcodeFilter = $_SESSION["reports"]["sortcodesick$intTeamID"];    
}
else {
  $strSortcodeFilter = '';  
}
$strDepartmentName = GetDepartmentNameFromID($intTeamID);
$strTeam = '';

$arrCollated = getSicknessReport($intTeamID, $strStartDate, $strEndDate);

if(isset($arrCollated)) {
    $intLine = 0;
    foreach ($arrCollated as $sn => $record) {
      if ($record['DaysUnavailable'] > 0) {
        $arrLines[$intLine][0] = $record['DisplayName'];
        $arrLines[$intLine][1] = $strDepartmentName;
        $arrLines[$intLine][2] = $record['DaysUnavailable'];
        $arrLines[$intLine][3] = $record['WeeksSick'];        
        $arrLines[$intLine][4] = $record['Occurrences'];
        $arrLines[$intLine][5] = round($record['TotalHoursSick']/3600, 2);
        $arrLines[$intLine][6] = $record['TotalDaysWorked'];
        $percent = $record['Percentage'];
        $arrLines[$intLine][7] = $percent.'%';        
        $arrLines[$intLine][8] = date("jS F Y", strtotime($record['MaxDutyDate']));

        $intLine++;
      }
      
    }
  }

$objPHPExcel = new Spreadsheet();
$strHeader = "Sickness Report for $strDepartmentName";
// Set document properties
$objPHPExcel->getProperties()->setCreator("Allocate")
							 ->setLastModifiedBy("Allocate")
							 ->setTitle($strHeader);

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()
->getStyle('A1:J1')
->applyFromArray (
    array(
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => 'AD0102'],
        ],    
    )
);

$objPHPExcel->getActiveSheet()
->getStyle('A2:J2')
->applyFromArray (
    array(
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => 'EEEEEE'],
        ],
    )
);

$styleArray = new Style();
$styleArray = [
    'alignment' => [
        'wrap' => true,
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
    ],
];

$objPHPExcel->getActiveSheet()
->getStyle('A1:I1000')
->applyFromArray($styleArray);

$styleArray1 = new Style();
$styleArray1 = [
    'alignment' => [
        'wrap' => true,
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
    ],
];

$objPHPExcel->getActiveSheet()
->getStyle('J1:I1000')
->applyFromArray($styleArray1);


$styleArray2 = new Style();
$styleArray2 = [
    'alignment' => [
        'wrap' => true,
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
    'font' => [
        'color' => ['rgb' => '000000'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'color' => ['rgb' => 'EEEEEE'],
    ],
    'borders' => [
		'top' => ['borderStyle' => Border::BORDER_THIN],
        'bottom' => ['borderStyle' => Border::BORDER_THIN],
        'right' => ['borderStyle' => Border::BORDER_THIN],
		'left' => ['borderStyle' => Border::BORDER_THIN],
        'outline' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FFFFFF'],
        ],
    ],
];

// The Names
$strEndCell = 'C'.(count($arrLines) + 2);
$objPHPExcel->getActiveSheet()
->getStyle("A3:$strEndCell")
->applyFromArray ($styleArray2);


//Foramt ColJ as text....
$strEndCell = 'J'.(count($arrLines) + 2);
$objPHPExcel->getActiveSheet()
    ->getStyle("J3:$strEndCell")
    ->getNumberFormat()    
    ->setFormatCode(
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT
    );

$styleArray3 = new Style();
$styleArray3 = array(
    'font' => [
        'color' => ['rgb' => 'FFFFFF'],
        'size'  => 10,
        'name'  => 'Verdana',
        'bold' => true,
    ], 
);
if ($strTeam != '') {
 $strHeader.= " and Team '$strTeam'";
}
$strHeader.= "\nBetween Dates ".date("jS F Y", strtotime($strStartDate))."-".date("jS F Y", strtotime($strEndDate));
 
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray3);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
$objPHPExcel->getActiveSheet()->setCellValue('A1', $strHeader);

// Set The widths
$objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(12);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
// Do the Days and dates
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Name');
$objPHPExcel->getActiveSheet()->setCellValue('B2', 'Team');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'Days Unavailable/Sick');
$objPHPExcel->getActiveSheet()->setCellValue('D2', 'Weeks Sick');
$objPHPExcel->getActiveSheet()->setCellValue('E2', 'Occurences');
$objPHPExcel->getActiveSheet()->setCellValue('F2', 'Hours');
$objPHPExcel->getActiveSheet()->setCellValue('G2', 'Worked Days');
$objPHPExcel->getActiveSheet()->setCellValue('H2', 'Percentage');
$objPHPExcel->getActiveSheet()->setCellValue('I2', 'Most Recent');

$activeSheet = $objPHPExcel->getActiveSheet();
$activeSheet->fromArray($arrLines, NULL, 'A3');


// Redirect output to a client�s web browser (Excel5)
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;filename="TeamSicknessReport.xlsx"');
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