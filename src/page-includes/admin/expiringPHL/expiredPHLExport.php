<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
include_once '../../../function-includes/testaccess.php';

require_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/leave-admin-functions.php';
include_once __DIR__ . '/../../../function-includes/common/classCommonDBFunctions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;

$commonObj = new classCommonDBFunctions();
$teamID = $_REQUEST['TeamId'];
$currentYear = date('Y');
$intWeekNumber = 0;
$PHLStartDate = getPHLStartDate();
$getExpiredPHL = getAllExpirdPHL($PHLStartDate, $intWeekNumber, $teamID);
$PHLData = [];

if (!empty($getExpiredPHL)) {
	foreach ($getExpiredPHL as $index => $ExpiredPHL) {
		$scheduledPerson = $ExpiredPHL['SchedulingPersonID'];
		$creditdateData = explode(" ", $ExpiredPHL['PHLDATE']);
		$creditdate = $creditdateData[0];
		$expireddateData = explode(" ", $ExpiredPHL['ExpDate']);
		$PHLData[$scheduledPerson][$creditdate]['Balance'] = isset($ExpiredPHL['diff']) ? round((float)$ExpiredPHL['diff'], 2) : 0;
		$PHLData[$scheduledPerson][$creditdate]['SchedulingPersonID'] = $scheduledPerson;
		$PHLData[$scheduledPerson][$creditdate]['CreditDate'] = date('d/M/Y', strtotime($creditdate));
		$PHLData[$scheduledPerson][$creditdate]['PHLAmount'] = isset($ExpiredPHL['rolling_sum']) ? round((float)$ExpiredPHL['rolling_sum'], 2) : 0;
		$PHLData[$scheduledPerson][$creditdate]['PHLUsed'] = isset($ExpiredPHL['rolling_d_sum']) ? round((float)$ExpiredPHL['rolling_d_sum'], 2) : 0;
		$PHLData[$scheduledPerson][$creditdate]['expiryDate'] = date('d/M/Y', strtotime($expireddateData[0]));
		$PHLData[$scheduledPerson][$creditdate]['StaffNumber'] = $ExpiredPHL['StaffNumber'];
		$PHLData[$scheduledPerson][$creditdate]['Name'] = $ExpiredPHL['userDisplayName'];
		$PHLData[$scheduledPerson][$creditdate]['iYear'] = $ExpiredPHL['iYear'];
		$PHLData[$scheduledPerson][$creditdate]['comment'] = $ExpiredPHL['Comments'];
		$PHLData[$scheduledPerson][$creditdate]['TimeDemensionID'] = $ExpiredPHL['TimeDemensionID'];
		$PHLData[$scheduledPerson][$creditdate]['SchedulingTeamid'] = $teamID;
		$PHLData[$scheduledPerson][$creditdate]['ID'] = $ExpiredPHL['ID'];
	}
}

$objPHPExcel = new Spreadsheet();
$styleArray = [
	'borders' => [
		'top' => [
			'borderStyle' => Border::BORDER_THIN,
		],
		'bottom' => [
			'borderStyle' => Border::BORDER_THIN,
		],
		'left' => [
			'borderStyle' => Border::BORDER_THIN,
		],
		'right' => [
			'borderStyle' => Border::BORDER_THIN,
		],
	],
	'font' => [
		'size' => 10,
		'name' => 'Arial'
	]
];

// Set document properties
$objPHPExcel->getProperties()->setCreator("Allocate7")->setLastModifiedBy("Allocate7")->setTitle("BBC News Allocate");
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
$expiringPHLContainer = count($PHLData);
$Heading = ($expiringPHLContainer > 1) ? 'BBC News - PHL Expired.' : 'BBC News - PHL Expired';
$objPHPExcel->getActiveSheet()->setCellValue('A1', $Heading);
$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Created on ' . date('d/m/Y'));
// Set excel header
$objPHPExcel->getActiveSheet()->getStyle('A5:H5')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A5:H5')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Name');
$objPHPExcel->getActiveSheet()->setCellValue('B5', 'Staff Number');
$objPHPExcel->getActiveSheet()->setCellValue('C5', 'Leave Year');
$objPHPExcel->getActiveSheet()->setCellValue('D5', 'PHL Date');
$objPHPExcel->getActiveSheet()->setCellValue('E5', 'PHL Amount');
$objPHPExcel->getActiveSheet()->setCellValue('F5', 'Expiry Date');
$objPHPExcel->getActiveSheet()->setCellValue('G5', 'PHL Expired');
$objPHPExcel->getActiveSheet()->setCellValue('H5', 'Comment');

$i = 6;
if (!empty($PHLData)) {
	foreach ($PHLData as $key => $PHLExpireRow) {
		foreach ($PHLExpireRow as $innerKey => $getActivityCode_d) {
			if (isset($getActivityCode_d['Balance']) && $getActivityCode_d['Balance'] < 0 && strtotime($getActivityCode_d['expiryDate']) < strtotime(date('Y-m-d'))) {
				$objPHPExcel->getActiveSheet()->getStyle("A$i:H$i")->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $getActivityCode_d['Name']);
				$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $getActivityCode_d['StaffNumber']);
				$objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $getActivityCode_d['iYear']);
				$objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $getActivityCode_d['CreditDate']);
				$objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $getActivityCode_d['PHLAmount']);
				$objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $getActivityCode_d['expiryDate']);
				$objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $getActivityCode_d['Balance']);
				$objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $getActivityCode_d['comment']);
				$i++;
			}
		}
	}
} else {
	$objPHPExcel->getActiveSheet()->setCellValue('A6', "No Record Found.");
	$objPHPExcel->getActiveSheet()->mergeCells('A6:H6');
}

$objPHPExcel->getActiveSheet()->setTitle('Expired PHL Dump');

// Redirect output to a client’s web browser (Xlsx)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ExpiredPHLs.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');
$objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
ob_end_clean();
$objWriter->save('php://output');
exit;
