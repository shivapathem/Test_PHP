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

$colName = ['A','B'];

$objPHPExcel = new Spreadsheet();
foreach($colName as $name) {
	$objPHPExcel->getActiveSheet()->getColumnDimension($name)->setAutoSize(true);
}

$rowCounter = 1;
foreach(json_decode($_REQUEST['exportData'], true) ?? [] as $key => $exportData) {
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCounter, $key);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCounter, $exportData);
	$objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	$rowCounter++;
}

  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SicknessSummaryByAreaReport.xlsx"');
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