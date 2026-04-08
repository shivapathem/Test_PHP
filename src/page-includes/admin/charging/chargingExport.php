<?php
ini_set('max_execution_time', 0);
$pageid = 19;
session_start();
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';
require_once '../../../function-includes/DBHelper.php';
$pdo = OpenDBLinkA7();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;

$permissions = getUserRolePermissions($pageid);
if ($permissions->canview == 1);
{
	$chargingContainer	=	($_POST['chargingContainer']);
	$orderByStr			=	base64_decode($_POST['orderBy']);
	$orderByArr			=	explode(',', $orderByStr ?? '');
	$sortingType		=	$orderByArr[1] ?? 'asc';
	switch($orderByArr[0])
	{
		case 0 :	$sortingCol	=	'EC.EstablishCode';
				break;
		case 1 :	$sortingCol	=	'AC.ActivityCodeName';
				break;
		case 2 :	$sortingCol	=	'CWC.ChargeWbsCodeName';
				break;
		case 4 :	$sortingCol	=	'CDML.Quantity';
				break;
		case 5 :	$sortingCol	=	'CDML.UnitPrice';
				break;
		case 7 :	$sortingCol	=	'CDML.ChargingDutyDate';
				break;
		case 8 :	$sortingCol	=	'CDML.Comments';
				break;
		case 9 :	$sortingCol	=	'SP2.DisplayName, EC.EstablishCode';
				break;
		case 10 :	$sortingCol	=	'CDML.StaffId';
				break;
		case 11 :	$sortingCol	=	'SP.DisplayName, CDML.CreatedDate';
				break;
		case 12 :	$sortingCol	=	'CDML.Contact';
				break;
		case 13 :	$sortingCol	=	'CDML.Telephone';
				break;
		default :	$sortingCol	=	'ACML.MappingId';
				break;

	}
	$currentFinancialEndDate = strtotime("01-04-".date('Y'));
	if($currentFinancialEndDate > time())
	{
		$year = date('Y') - 1;
	}else
	{
		$year = date('Y');
	}
	$getActivityCode_q	=	"SELECT
	DISTINCT CDML.ChargingId,
	CDML.Quantity,
	CDML.UnitPrice,
	CDML.ChargingDutyDate,
	CDML.Comments,
	CDML.StaffId,
	CDML.CreatedDate,
	CDML.Contact,
	CDML.Telephone,
	EC.EstablishCode,
	AC.ActivityCodeName,
	CWC.ChargeWbsCodeName,
	ACML.Price,
	ACML.MappingId,
	UD.UD_DisplayName DisplayName,
	UD2.UD_DisplayName as StaffDisplayName,
	UD2.UD_StaffNumber StaffNumber
	FROM ChargingDutyMapping_Link CDML
	JOIN EstablishCode EC ON CDML.EstabCodeId = EC.EstablishCodeId
	JOIN ACTIVITYCODE AC ON CDML.ActivityCodeId = AC.ActivityCodeId
	JOIN CHARGEWBSCODE CWC ON CDML.ChargeCodeId = CWC.ChargeWbsCodeId
	INNER JOIN AllocationsScheduledPersons ON ASP_AllocationsSPID = CDML.AllocationId
	LEFT JOIN schedulingTeams ST ON ST.schedulingTeamId = ASP_ChargingTeamID
	LEFT JOIN UserDetails UD on CDML.CreatedBy = UD.UD_UserID
	JOIN UserDetails UD2 on CDML.PersonId = UD2.UD_UserID
	LEFT JOIN ActivityChargeCodeMapping_Link ACML ON CDML.EstabCodeId = ACML.EstablishCodeId
	AND CDML.ActivityCodeId = ACML.ActiveCodeId
	AND ACML.Year = $year WHERE ChargingId in($chargingContainer) ORDER BY $sortingCol $sortingType";
	$getActivityCode_r	=	$pdo->prepare($getActivityCode_q);
	$getActivityCode_r->execute();
	$getActivityCode_f	=	$getActivityCode_r->fetchAll(PDO::FETCH_ASSOC);
	$dataContainerForSheet	=	array();
	$objPHPExcel = new Spreadsheet();
	$styleArray = new Style();
	  $styleArray = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
        'borders' => [
            'top' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'left' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'right' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Allocate7")->setLastModifiedBy("Allocate7")->setTitle("BBC News Allocate");
	$objPHPExcel->setActiveSheetIndex(0);
//----------------------------------1st worksheet starts here-----------------------------------------------------------------------
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Header Text');

	$objPHPExcel->getActiveSheet()->getStyle('A2:D2')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->mergeCells('A2:D2');
	$objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->setCellValue('A2', 'News Resources Non News Adhoc '.date('d-m-Y'));

	// Set excel header
	$objPHPExcel->getActiveSheet()->getStyle('A3:U3')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->getStyle("A3:U3")->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Sender Cost Centre');
	$objPHPExcel->getActiveSheet()->setCellValue('B3', 'Activity Type');
	$objPHPExcel->getActiveSheet()->setCellValue('C3', 'Receiver CC or WBS Number');
	$objPHPExcel->getActiveSheet()->setCellValue('D3', 'Quantity');
	$objPHPExcel->getActiveSheet()->setCellValue('E3', 'Transaction Date');
	$objPHPExcel->getActiveSheet()->setCellValue('F3', 'Date Of work charged');
	$objPHPExcel->getActiveSheet()->setCellValue('G3', 'Duty Comment');
	$objPHPExcel->getActiveSheet()->setCellValue('H3', 'Duty Name and person who did it');
	$objPHPExcel->getActiveSheet()->setCellValue('I3', 'Created By');
	$objPHPExcel->getActiveSheet()->setCellValue('J3', 'Not Used');
	$objPHPExcel->getActiveSheet()->setCellValue('K3', 'Staff Number');
	$objPHPExcel->getActiveSheet()->setCellValue('L3', 'Analysis 1');
	$objPHPExcel->getActiveSheet()->setCellValue('M3', 'Analysis 2');
	$objPHPExcel->getActiveSheet()->setCellValue('N3', 'Transaction');
	$objPHPExcel->getActiveSheet()->setCellValue('O3', 'Item text 10');
	$objPHPExcel->getActiveSheet()->setCellValue('P3', 'Contact Name');
	$objPHPExcel->getActiveSheet()->setCellValue('Q3', 'Contact Telephone');
	$objPHPExcel->getActiveSheet()->setCellValue('R3', 'X-Reference');
	$objPHPExcel->getActiveSheet()->setCellValue('S3', 'CustReference');
	$objPHPExcel->getActiveSheet()->setCellValue('T3', 'Building');
	$objPHPExcel->getActiveSheet()->setCellValue('U3', 'Personnel Name');
	$i=4;
	foreach($getActivityCode_f as $getActivityCode_d)
	{
		if(empty($getActivityCode_d['MappingId']))
		{
			$dataContainerForSheet[]	=	$getActivityCode_d;
		}else
		{
			$objPHPExcel->getActiveSheet()->getStyle("A$i:U$i")->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $getActivityCode_d['EstablishCode']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $getActivityCode_d['ActivityCodeName']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $getActivityCode_d['ChargeWbsCodeName']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $getActivityCode_d['Quantity']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, date('d/m/Y', strtotime($getActivityCode_d['ChargingDutyDate'])));
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, 'News Charge '.date('d/m/Y', strtotime($getActivityCode_d['ChargingDutyDate'])));
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $getActivityCode_d['Comments']);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $getActivityCode_d['StaffDisplayName'].' ('.$getActivityCode_d['EstablishCode'].') '.date('d/m/Y', strtotime($getActivityCode_d['ChargingDutyDate'])));
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $getActivityCode_d['DisplayName'].' ('.date('d/m/Y', strtotime($getActivityCode_d['CreatedDate'])).')');
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $getActivityCode_d['StaffNumber']);
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $getActivityCode_d['Contact']);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $getActivityCode_d['Telephone']);
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, '');
			$i++;
		}
	}
	$objPHPExcel->getActiveSheet()->setTitle('Charging Report');
//----------------------------------1st worksheet ends here-------------------------------------------------------------------------
	// Create a new worksheet, after the default sheet
	$objPHPExcel->createSheet();

	// Add some data to the second sheet, resembling some different data types
	$objPHPExcel->setActiveSheetIndex(1);
//----------------------------------2nd worksheet starts here-----------------------------------------------------------------------
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Valid : No');

	$objPHPExcel->getActiveSheet()->getStyle('A2:D2')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->mergeCells('A2:D2');
	$objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->setCellValue('A2', 'News Resources Non News Adhoc '.date('d-m-Y'));

	// Set excel header
	$objPHPExcel->getActiveSheet()->getStyle('A3:U3')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->getStyle("A3:U3")->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Sender Cost Centre');
	$objPHPExcel->getActiveSheet()->setCellValue('B3', 'Activity Type');
	$objPHPExcel->getActiveSheet()->setCellValue('C3', 'Receiver CC or WBS Number');
	$objPHPExcel->getActiveSheet()->setCellValue('D3', 'Quantity');
	$objPHPExcel->getActiveSheet()->setCellValue('E3', 'Transaction Date');
	$objPHPExcel->getActiveSheet()->setCellValue('F3', 'Date Of work charged');
	$objPHPExcel->getActiveSheet()->setCellValue('G3', 'Duty Comment');
	$objPHPExcel->getActiveSheet()->setCellValue('H3', 'Duty Name and person who did it');
	$objPHPExcel->getActiveSheet()->setCellValue('I3', 'Created By');
	$objPHPExcel->getActiveSheet()->setCellValue('J3', 'Not Used');
	$objPHPExcel->getActiveSheet()->setCellValue('K3', 'Staff Number');
	$objPHPExcel->getActiveSheet()->setCellValue('L3', 'Analysis 1');
	$objPHPExcel->getActiveSheet()->setCellValue('M3', 'Analysis 2');
	$objPHPExcel->getActiveSheet()->setCellValue('N3', 'Transaction');
	$objPHPExcel->getActiveSheet()->setCellValue('O3', 'Item text 10');
	$objPHPExcel->getActiveSheet()->setCellValue('P3', 'Contact Name');
	$objPHPExcel->getActiveSheet()->setCellValue('Q3', 'Contact Telephone');
	$objPHPExcel->getActiveSheet()->setCellValue('R3', 'X-Reference');
	$objPHPExcel->getActiveSheet()->setCellValue('S3', 'CustReference');
	$objPHPExcel->getActiveSheet()->setCellValue('T3', 'Building');
	$objPHPExcel->getActiveSheet()->setCellValue('U3', 'Personnel Name');
	$i=4;
	foreach($dataContainerForSheet as $getActivityCode_d)
	{
		$objPHPExcel->getActiveSheet()->getStyle("A$i:U$i")->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $getActivityCode_d['EstablishCode']);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $getActivityCode_d['ActivityCodeName']);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $getActivityCode_d['ChargeWbsCodeName']);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $getActivityCode_d['Quantity']);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, date('d-m-Y', strtotime($getActivityCode_d['ChargingDutyDate'])));
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, 'News Charge '.date('d-m-Y', strtotime($getActivityCode_d['ChargingDutyDate'])));
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $getActivityCode_d['Comments']);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $getActivityCode_d['StaffDisplayName'].' ('.$getActivityCode_d['EstablishCode'].') '.date('d-m-Y', strtotime($getActivityCode_d['ChargingDutyDate'])));
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $getActivityCode_d['DisplayName'].' ('.date('d/m/Y h:i:s', strtotime($getActivityCode_d['CreatedDate'])).')');
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, '');
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $getActivityCode_d['StaffNumber']);
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, '');
		$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, '');
		$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, '');
		$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, '');
		$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $getActivityCode_d['Contact']);
		$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $getActivityCode_d['Telephone']);
		$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, '');
		$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, '');
		$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, '');
		$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, '');
		$i++;
	}
	$objPHPExcel->getActiveSheet()->setTitle('Error');
//----------------------------------2nd worksheet ends here-------------------------------------------------------------------------
	$objPHPExcel->setActiveSheetIndex(0);
	// Redirect output to a client’s web browser (Excel5)
	/*header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="Charging to Finance_Allocate.xls"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate');
	header ('Pragma: public'); // HTTP/1.0
	*/
	$objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
    ob_end_clean();
    $objWriter->save(__DIR__.'/excel/Charging to Finance_Allocate.xlsx');
}