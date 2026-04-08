<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
require_once '../../../vendor/autoload.php';
include_once '../../function-includes/bootstrap.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;

$colName = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R', 'S'];

$objPHPExcel = new Spreadsheet();
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Area');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Cost Code');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Scheduling Team Name');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'NetLogin');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Staff Number');
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Display Name');
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'EFT');
$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Approx. Days to Take');
$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Annual Balance');
$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Annual Leave (above Carry Over)');
$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Annual Leave Carry Over Limit');
$objPHPExcel->getActiveSheet()->setCellValue('L1', 'PHL Balance');
$objPHPExcel->getActiveSheet()->setCellValue('M1', 'PHL (above Carry Over)');
$objPHPExcel->getActiveSheet()->setCellValue('N1', 'PHL Carry Over Limit');
$objPHPExcel->getActiveSheet()->setCellValue('O1', 'Additional');
$objPHPExcel->getActiveSheet()->setCellValue('P1', 'Additional (above Carry Over)');
$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'EDP TOIL Balance');
$objPHPExcel->getActiveSheet()->setCellValue('R1', 'Under 11 TOIL Balance');
$objPHPExcel->getActiveSheet()->setCellValue('S1', 'Other Balance');
$objPHPExcel->getActiveSheet()->getStyle("A1:S1")->getFont()->setBold(true);
foreach($colName as $name) {
	$objPHPExcel->getActiveSheet()->getColumnDimension($name)->setAutoSize(true);
}
$rowCounter = 2;
$neutralStyle = new Style();

$neutralStyle = [
	'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'color' => ['argb' => 'FFEB9C'],
    ],
    'font' => [
        'color' => array('rgb' => '9C6500'),
    ],
];

$badStyle = new Style();

$badStyle = [
	'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'color' => ['argb' => 'FFC7CE'],
    ],
    'font' => [
        'color' => array('rgb' => '9C0006'),
    ],
];


foreach(json_decode($_REQUEST['exportData'], true) ?? [] as $exportData) {
	$alaco = !empty($exportData[9]) ? $exportData[9] : 0;
	$eft = !empty($exportData[6]) ? $exportData[6] : 0;
	$phlaco = !empty($exportData[12]) ? $exportData[12] : 0;
	$aaco = !empty($exportData[15]) ? $exportData[15] : 0;
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCounter, html_entity_decode($exportData[0]));
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCounter, $exportData[1]);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCounter, $exportData[2]);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCounter, $exportData[3]);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCounter, $exportData[4]);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCounter, $exportData[5]);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $rowCounter, $eft)->getStyle('G' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('H' . $rowCounter, $exportData[7]);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $rowCounter, $exportData[8])->getStyle('I' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');

	//Annual Leave (above Carry Over)
	$objPHPExcel->getActiveSheet()->setCellValue('J' . $rowCounter, $alaco <= 0 ? '' : $alaco)->getStyle('J' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	if($alaco  > 0 && ($alaco > (35 * $eft))) {
		$objPHPExcel->getActiveSheet()->getStyle('J' . $rowCounter)->applyFromArray(
			$badStyle
		);
	}
	if($alaco  > 0 && ($alaco <= (35 * $eft))) {
		$objPHPExcel->getActiveSheet()->getStyle('J' . $rowCounter)->applyFromArray(
			$neutralStyle
		);
	}

	$objPHPExcel->getActiveSheet()->setCellValue('K' . $rowCounter, $exportData[10])->getStyle('K' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('L' . $rowCounter, $exportData[11])->getStyle('L' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');

	//PHL (above Carry Over)
	$objPHPExcel->getActiveSheet()->setCellValue('M' . $rowCounter, $phlaco <= 0 ? '' : $phlaco)->getStyle('M' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	if($phlaco  > 0 && ($phlaco > (15.75 * $eft))) {
		$objPHPExcel->getActiveSheet()->getStyle('M' . $rowCounter)->applyFromArray(
			$badStyle
		);
	}
	if($phlaco  > 0 && ($phlaco <= (15.75 * $eft))) {
		$objPHPExcel->getActiveSheet()->getStyle('M' . $rowCounter)->applyFromArray(
			$neutralStyle
		);
	}
	$objPHPExcel->getActiveSheet()->setCellValue('N' . $rowCounter, $exportData[13])->getStyle('N' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('O' . $rowCounter, $exportData[14])->getStyle('O' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');

	//Additional (above Carry Over)
	$objPHPExcel->getActiveSheet()->setCellValue('P' . $rowCounter, $aaco <= 0 ? '' : $aaco)->getStyle('P' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	if($aaco  > 0) {
		$objPHPExcel->getActiveSheet()->getStyle('P' . $rowCounter)->applyFromArray(
			$badStyle
		);
	}
	$objPHPExcel->getActiveSheet()->setCellValue('Q' . $rowCounter, $exportData[16])->getStyle('Q' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('R' . $rowCounter, $exportData[17])->getStyle('R' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('S' . $rowCounter, $exportData[18])->getStyle('S' . $rowCounter)->getNumberFormat()
    ->setFormatCode('0.00');
	$rowCounter++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="LeaveByAreaReport.xlsx"');
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