<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
require_once '../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;

$colName = ['A','B','C','D','E','F','G','H'];

$objPHPExcel = new Spreadsheet();
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Name');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Days Unavailable/Sick');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Weeks Sick');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Max Consec Days');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Occurrences');
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Hours');
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Most Recent');
$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Type of Occurrence');
foreach($colName as $name) {
	$objPHPExcel->getActiveSheet()->getColumnDimension($name)->setAutoSize(true);
}
$objPHPExcel->getActiveSheet()->getStyle("A1:H1")->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()
    ->getStyle('A1:H1')
    ->applyFromArray([
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => '66BCDD'
            ]
        ]
    ]);
$colourRow = json_decode($_REQUEST['exportDataRowColour'], true);
$counterData = 0;
$rowCounter = 2;
foreach(json_decode($_REQUEST['exportData'], true) ?? [] as $exportData) {
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCounter, $exportData[0]);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCounter, $exportData[1]);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCounter, $exportData[2]);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCounter, $exportData[3]);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCounter, $exportData[4]);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCounter, $exportData[5]);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $rowCounter, $exportData[6]);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . $rowCounter, $exportData[7]);
	if(in_array($counterData, $colourRow)) {
		$objPHPExcel->getActiveSheet()
		    ->getStyle('A' . $rowCounter . ':H' . $rowCounter)
		    ->applyFromArray([
		        'fill' => [
		            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
		            'startColor' => [
		                'rgb' => 'FFDFDF'
		            ]
		        ]
		    ]);
	}
	$rowCounter++;
	$counterData++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SicknessOccurrencesByAreaReport.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate');
header('Pragma: public'); // HTTP/1.0

$objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
ob_end_clean();
$objWriter->save('php://output');
?>