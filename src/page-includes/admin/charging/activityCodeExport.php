<?php
$pageid = 19;
session_start();

require_once '../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';
require_once '../../../function-includes/DBHelper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\IOFactory;

$pdo = OpenDBLinkA7();
$permissions = getUserRolePermissions($pageid);
if($permissions->canview == 1);
{
	$getActivityCode_q	=	"Usp_ActivityOperations '', '', '', 'SELECT', '', '', ''";
	$getActivityCode_r	=	$pdo->prepare($getActivityCode_q);
	$getActivityCode_r->execute();
	$getActivityCode_f	=	$getActivityCode_r->fetchAll(PDO::FETCH_ASSOC);

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

	$objPHPExcel->getProperties()
    ->setCreator('Allocate7')
    ->setLastModifiedBy('Allocate7')
    ->setTitle('BBC News Allocate');

	$objPHPExcel->setActiveSheetIndex(0);

	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'BBC News Allocate');

	$objPHPExcel->getActiveSheet()->getStyle('A2:D2')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->mergeCells('A2:D2');
	$objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->setCellValue('A2', '- Activity Codes Configuration Extract');

	// Set excel header
	$objPHPExcel->getActiveSheet()->getStyle('A3:D3')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Activity Code');
	$objPHPExcel->getActiveSheet()->setCellValue('B3', 'Description');
	$objPHPExcel->getActiveSheet()->setCellValue('C3', 'Active');
	$objPHPExcel->getActiveSheet()->setCellValue('D3', 'History');

	// Set column width
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);

	$i=4;
	foreach($getActivityCode_f as $getActivityCode_d)
	{
		$statusStr		=	($getActivityCode_d['IsActive'] == 1) ? 'YES' : 'NO';
		$objPHPExcel->getActiveSheet()->getStyle("A$i:D$i")->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $getActivityCode_d['ActivityCodeName']);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $getActivityCode_d['Description']);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $statusStr);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $getActivityCode_d['History']);
		$i++;
	}


	// Redirect output to a client�s web browser (Xlsx)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="ActivityCode_Allocate.xlsx"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
	header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header('Pragma: public'); // HTTP/1.0

	$writer = IOFactory::createWriter($objPHPExcel, 'Xlsx');
	$writer->save('php://output');


}