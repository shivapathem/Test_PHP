<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;
$colName = ['A','B','C','D','E','F','G','H','I','J'];

$objPHPExcel = new Spreadsheet();
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Area');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Display Forename');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Display Surname');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Staff Number');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Network Login');
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Home Scheduling Team');
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Sickness Start Date');
$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Sickness End Date');
$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Name of Sickness');
$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Total Duration');
foreach($colName as $name) {
	$objPHPExcel->getActiveSheet()->getColumnDimension($name)->setAutoSize(true);
}
$objPHPExcel->getActiveSheet()->getStyle("A1:J1")->getFont()->setBold(true)->getActiveSheet()->getStyle('A1:J1')->applyFromArray(
	array(
		'fill' => array(
			'type' => Fill::FILL_SOLID,
			'color' => array('rgb' => '66bcdd')
		)
	)
);;

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
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $rowCounter, $exportData[8]);
	$objPHPExcel->getActiveSheet()->setCellValue('J' . $rowCounter, $exportData[9]);
	$rowCounter++;
}

header('Content-Type: application/vnd.ms-excel');
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
$objWriter->save('php://output');
?>