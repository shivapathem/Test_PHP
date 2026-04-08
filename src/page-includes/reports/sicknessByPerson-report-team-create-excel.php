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
$schedulePeopleList = $_REQUEST['schedulePeopleList'];
$excelJson1 = json_decode($_REQUEST['excelJson1']);
$ScheduledPersonID = $_REQUEST['ScheduledPersonID'];
$teamId = $_REQUEST['teamId'];
$arrAllocations = getSicknessByPerson($strStartDate, $strEndDate, $ScheduledPersonID, $teamId);
$arrLines = array();
$intLine = 0;
foreach ($arrAllocations as $sn => $record) {
		$arrLines[$intLine][0] = date("jS F Y", strtotime($record['DutyDate']));
		$arrLines[$intLine][1] = spinweek($record['WeekNumber']);
		$arrLines[$intLine][2] = date("l", strtotime($record['DutyDate']));
		$arrLines[$intLine][3] = $record['DutyName'];        
		$arrLines[$intLine][4] = round($record['Duration']/3600, 2);
		$intLine++;
}

$objPHPExcel = new Spreadsheet();
$strHeader1 = "Allocate Sickness record";
// Set document properties
$objPHPExcel->getProperties()->setCreator("Allocate")
							 ->setLastModifiedBy("Allocate")
							 ->setTitle($strHeader1);

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()
->getStyle('A1:E1')
->applyFromArray (
    array(
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => '1a99e3'],
            'size'  => 3,
        ],      
    )
);

$objPHPExcel->getActiveSheet()
->getStyle('A2:E2')
->applyFromArray (
    array(
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => 'EEEEEE'],
        ],        
    )
);
$strHeader = "";

$styleArray = new Style();
$styleArray = array(
    'font' => [
        'color' => ['rgb' => '000000'],
        'size'  => 5,
        'name'  => 'Calibri',
        'bold' => true,
    ], 
);
$styleArray1 = new Style();
$styleArray1 = array(
    'font' => [
        'color' => ['rgb' => '000000'],
        'size'  => 10,
        'name'  => 'Verdana',
        'bold' => true,
    ], 
);

$strHeader.= $schedulePeopleList." sickness - ".date("l jS F Y", strtotime($strStartDate))." to ".date("l jS F Y", strtotime($strEndDate));
 
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
$objPHPExcel->getActiveSheet()->setCellValue('A1', $strHeader1);
$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray1);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
$objPHPExcel->getActiveSheet()->setCellValue('A2', $strHeader);
$objPHPExcel->getActiveSheet()->setCellValue('A4', 'Total number of days sick');
$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Duty duration sick');
$objPHPExcel->getActiveSheet()->setCellValue('A6', 'Occurrences');

$objPHPExcel->getActiveSheet()->setCellValue('B4', $excelJson1[0]);
$objPHPExcel->getActiveSheet()->setCellValue('B5', $excelJson1[2]);
$objPHPExcel->getActiveSheet()->setCellValue('B6', $excelJson1[1]);

// Set The widths
$objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(15);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
// Do the Days and dates
$objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray(array('font'  => array('bold'  => true)));
$objPHPExcel->getActiveSheet()->getStyle('B8')->applyFromArray(array('font'  => array('bold'  => true)));
$objPHPExcel->getActiveSheet()->getStyle('C8')->applyFromArray(array('font'  => array('bold'  => true)));
$objPHPExcel->getActiveSheet()->getStyle('D8')->applyFromArray(array('font'  => array('bold'  => true)));
$objPHPExcel->getActiveSheet()->getStyle('E8')->applyFromArray(array('font'  => array('bold'  => true)));
$objPHPExcel->getActiveSheet()->setCellValue('A8', 'Date');
$objPHPExcel->getActiveSheet()->setCellValue('B8', 'Week');
$objPHPExcel->getActiveSheet()->setCellValue('C8', 'Day');
$objPHPExcel->getActiveSheet()->setCellValue('D8', 'Duty');
$objPHPExcel->getActiveSheet()->setCellValue('E8', 'Duration');
$objPHPExcel->getActiveSheet()->getStyle('E9:E256')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()->getStyle('A8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
$activeSheet = $objPHPExcel->getActiveSheet();
$activeSheet->fromArray($arrLines, null, 'A9', true);


// Redirect output to a client�s web browser (Excel5)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SicknessByPersonReport.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
ob_end_clean();
$objWriter->save('php://output');

?>