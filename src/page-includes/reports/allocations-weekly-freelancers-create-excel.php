<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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


$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
if (isset($_REQUEST['id'])) {
  // Passed a week Number?
  $intWeekNumber = $_REQUEST['id'];
}
else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
  }
  else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));
  }
}
$intTeamID = $_REQUEST['teamId'];
if ($intTeamID == 0) {
  $strTeamName = 'All Teams';
}
else {
  $strTeamName = $arrTeams[$intTeamID]['TeamName'] ?? '';
}
$arrAllocations = ReadFreelanceAllocations($intTeamID , $intWeekNumber, array(),$userID);

$arrLines = array();
$intLine = 0;

if (isset($arrAllocations['Duties'])) {
foreach ($arrAllocations['Duties'] as $strStaffNumber => $arrPerson) {

  for ($i = 0; $i <=6; $i++) {
    if (isset($arrPerson['Duties'][$intWeekNumber][$i])) {
      $arrLines[$intLine][0] = date("d/m/Y", strtotime(datefromweek($intWeekNumber, $i)));
      $arrLines[$intLine][1] = $arrPerson['Duties'][$intWeekNumber][$i]["DepartmentName"];
      $arrLines[$intLine][2] = $arrPerson['Name'];
      $arrLines[$intLine][3] = $arrPerson["StaffNumber"];
      $arrLines[$intLine][4] = $arrPerson['Duties'][$intWeekNumber][$i]['DutyName'];
      $arrLines[$intLine][5] = $arrPerson['Duties'][$intWeekNumber][$i]["Duration"];


      if ($arrPerson['Duties'][$intWeekNumber][$i]['PersonComments'] != '') {
        $arrLines[$intLine][6] = trim($arrPerson['Duties'][$intWeekNumber][$i]['PersonComments']);
      }
      else {
        $arrLines[$intLine][6] = "";
      }
    }
    $intLine ++;
  }
}
}
// Create new Spreadsheet object
$objPHPExcel = new Spreadsheet();
$styleArray = new Style();
$styleArray = array([
    	'borders' => [
			'top' => ['borderStyle' => Border::BORDER_THIN],
            'bottom' => ['borderStyle' => Border::BORDER_THIN],
            'right' => ['borderStyle' => Border::BORDER_THIN],
			'left' => ['borderStyle' => Border::BORDER_THIN],
        ],
]);

// Set document properties
$objPHPExcel->getProperties()->setCreator("Allocate")
							 ->setLastModifiedBy("Allocate")
							 ->setTitle("Freelance Usage for ".spinweek($intWeekNumber));

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()
    ->getStyle('A1:G1')
    ->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => '4B72BF'],
        ],
    ]);

$objPHPExcel->getActiveSheet()
    ->getStyle('A2:G2')
    ->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => 'EEEEEE'],
        ],
    ]);

// For range A1:E1000 with left horizontal alignment and top vertical alignment, wrap text enabled
$objPHPExcel->getActiveSheet()
    ->getStyle('A1:E1000')
    ->applyFromArray([
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
            'wrapText' => true,
        ],
    ]);

// For range G1:E1000 with right horizontal alignment and top vertical alignment, wrap text enabled
$objPHPExcel->getActiveSheet()
    ->getStyle('G1:E1000')
    ->applyFromArray([
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
            'wrapText' => true,
        ],
    ]);

// The Names
$strEndCell = 'B' . (count($arrLines) + 2);

$objPHPExcel->getActiveSheet()
    ->getStyle("A3:$strEndCell")
    ->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => 'EEEEEE'],
        ],
        'font' => [
            'color' => ['rgb' => '000000'],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ],
    ]);


//Foramt ColJ as text....
$strEndCell = 'G' . (count($arrLines) + 2);
$objPHPExcel->getActiveSheet()
    ->getStyle("G3:$strEndCell")
    ->getNumberFormat()
    ->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

$styleArray = array(
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'FFFFFF'),
        'size'  => 15,
        'name'  => 'Verdana'
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Allocate - Freelance Usage for Week '.spinweek($intWeekNumber));

// Set The widths
$objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);

// Do the Days and dates
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Date');
$objPHPExcel->getActiveSheet()->setCellValue('B2', 'Team');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'Person');
$objPHPExcel->getActiveSheet()->setCellValue('D2', 'Staff Number');
$objPHPExcel->getActiveSheet()->setCellValue('E2', 'Duty');
$objPHPExcel->getActiveSheet()->setCellValue('F2', 'Duration');
$objPHPExcel->getActiveSheet()->setCellValue('G2', 'Smartbook Ref');

$activeSheet = $objPHPExcel->getActiveSheet();
$activeSheet->fromArray($arrLines, NULL, 'A3');

// Redirect output to a client�s web browser (Excel5)
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;filename="FreelanceUsage.xlsx"');
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
