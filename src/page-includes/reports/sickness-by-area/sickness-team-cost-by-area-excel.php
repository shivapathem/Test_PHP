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

$colName = ['A','B','C','D'];

$objPHPExcel = new Spreadsheet();
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Area');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Home Scheduling Team');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Sickness Percentage');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Sickness Cost');
foreach($colName as $name) {
	$objPHPExcel->getActiveSheet()->getColumnDimension($name)->setAutoSize(true);
}
$objPHPExcel->getActiveSheet()->getStyle("A1:D1")->getFont()->setBold(true);

$rowCounter = 2;
foreach(json_decode($_REQUEST['exportData'], true) ?? [] as $exportData) {
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCounter, html_entity_decode($exportData[0]));
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCounter, $exportData[1]);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCounter, strip_tags($exportData[2]));
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCounter, $exportData[3]);
	$objPHPExcel->getActiveSheet()
    ->getStyle('I' . $rowCounter . ':' . 'E' . $rowCounter)
    ->getAlignment()
    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);  
	$rowCounter++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SicknessTeamCostByAreaReport.xlsx"');
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