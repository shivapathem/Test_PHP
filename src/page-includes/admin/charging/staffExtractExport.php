<?php
$pageid = 19;
if (session_status() === PHP_SESSION_NONE)
{
	session_start(); 
}
require_once '../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';
include_once '../../../function-includes/testaccess.php';
require_once '../../../function-includes/DBHelper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;

$pdo = OpenDBLinkA7();
$userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
if (!empty($_GET['schedulingTeam']))
{
    $schedulingTeam	= 	$_GET['schedulingTeam'];
	$effectINForm	= 	$_GET['effectINForm'];
	$eft		 	= 	$_GET['eft'];
	$edp			= 	$_GET['edp'];
	$fsv			= 	$_GET['fsv'];
	$reportType		= 	$_GET['reportType'];
	$estabCodes		= 	$_GET['estabCodes'] ? $_GET['estabCodes'] : '';
	$staffNumber	= 	$_GET['staffNumber'];
	$groupBy1		= 	$_GET['groupBy1'];
	$groupBy2		= 	$_GET['groupBy2'];
    $orderByStr 	= 	base64_decode($_GET['orderBy']);
    $orderByArr 	= 	explode(',', $orderByStr);
    $sortingType 	= 	$orderByArr[1] ? $orderByArr[1] : 'asc';
	$sortingColName = 	'';
    $schedulingTeamNameArr	= 	$_GET['schedulingTeamNameArr'];
	switch($groupBy1)
	{
		case 1 :	$sortingColName = 'Scheduling Team';
				break;
		case 2 :	$sortingColName = 'Sort Code';
				break;
		case 3 :	$sortingColName = 'Charge Code';
				break;
	}
	switch($groupBy2)
	{
		case 1 :	$sortingColName = $sortingColName . ', Scheduling Team';
				break;
		case 2 :	$sortingColName = $sortingColName . ', Sort Code';
				break;
		case 3 :	$sortingColName = $sortingColName . ', Charge Code';
				break;
	}
    switch ($orderByArr[0]) {
        case 0 :
            $sortingCol = 'schedulingTeamName';
            break;
        case 1 :
            $sortingCol = 'DisplayName';
            break;
        case 2 :
            $sortingCol = 'StaffNumber';
            break;
        case 3 :
            $sortingCol = 'EstablishCode';
            break;
        case 4 :
            $sortingCol = 'SortCode';
            break;
        case 5 :
            $sortingCol = 'PaymentTypeShortCode';
            break;
        case 6 :
            $sortingCol = 'ManualEDP';
            break;
        case 7 :
            $sortingCol = 'EFT';
            break;
        default :
            $sortingCol = '1';
            break;
    }
	$getStaffList_q = "EXEC usp_getStaffExtractReportDetails ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
    $getStaffList_r = $pdo->prepare($getStaffList_q);
    $getStaffList_r->execute(array($schedulingTeam, $effectINForm, $eft, $edp, $fsv, $reportType, $estabCodes, $staffNumber, $groupBy1, $groupBy2, $sortingCol, $sortingType, $userID));
    $getStaffList_f = $getStaffList_r->fetchAll(PDO::FETCH_ASSOC);
    $objPHPExcel = new Spreadsheet();
    
    $styleArray = new Style();

    $styleArray = [
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
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'BBC News Allocate');

    $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
    $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(45);
    $objPHPExcel->getActiveSheet()->setCellValue('A2', "-- Grouped Staff Extract with Effective From Date $effectINForm \n--- Group by : $sortingColName \n-- $schedulingTeamNameArr");
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);

    // Set excel header
    $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Team');
    $objPHPExcel->getActiveSheet()->setCellValue('B3', 'Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C3', 'Staff Number');
    $objPHPExcel->getActiveSheet()->setCellValue('D3', 'Charge Code');
    $objPHPExcel->getActiveSheet()->setCellValue('E3', 'Sort Code');
    $objPHPExcel->getActiveSheet()->setCellValue('F3', 'FSV');
    $objPHPExcel->getActiveSheet()->setCellValue('G3', 'EDP');
    $objPHPExcel->getActiveSheet()->setCellValue('H3', 'EFT');
    // Set column width
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(5);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(5);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(5);
    $i = 4;
    foreach ($getStaffList_f as $getStaffList_d){
		$manualEDP 	= 	'';
		if($getStaffList_d['ManualEDP'] === "0")
		{
			$manualEDP 	= 	'C';
		}elseif($getStaffList_d['ManualEDP'] === "1")
		{
			$manualEDP 	= 	'M';
		}
        $objPHPExcel->getActiveSheet()->getStyle("A$i:H$i")->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $getStaffList_d['schedulingTeamName']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $getStaffList_d['DisplayName']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $getStaffList_d['StaffNumber']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $getStaffList_d['CostCode']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $getStaffList_d['SortCode']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $getStaffList_d['PaymentTypeShortCode']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $manualEDP);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $getStaffList_d['EFT']);
        $i++;
    }
    $objPHPExcel->getActiveSheet()->getStyle('D1:D' . $i)->getAlignment()->setWrapText(true);
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="StaffExtractReport.xlsx"');
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
}