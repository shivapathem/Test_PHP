<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start(); 
  }
require_once '../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/testaccess.php';
require_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/leave-admin-functions.php';
include_once '../../../function-includes/common/classCommonDBFunctions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;

$commonObj = new classCommonDBFunctions();
$selectedweek = $_REQUEST['expiringPHLWeek'];
$currentYear   = date('Y');
$intWeekNumber = 1;
$teamID = $_REQUEST['TeamId'];
if (isset($selectedweek)) {
   $intWeekNumber =  $selectedweek;
}

$PHLStartDate = getPHLStartDate();
$getExpiringPHL = getAllExpirdPHL($PHLStartDate,$intWeekNumber,$teamID);//Step 3

if (!empty($getExpiringPHL)) {
    foreach ($getExpiringPHL as $index => $ExpiringPHL) {
        $scheduledPerson = $ExpiringPHL['SchedulingPersonID'];
        $creditdateData = explode(" ",$ExpiringPHL['PHLDATE']);
        $creditdate = $creditdateData[0];
        $expireddateData = explode(" ",$ExpiringPHL['ExpDate']);
        $PHLData[$scheduledPerson][$creditdate]['Balance'] = round($ExpiringPHL['diff'],2); 
        $PHLData[$scheduledPerson][$creditdate]['SchedulingPersonID']=$scheduledPerson;        
        $PHLData[$scheduledPerson][$creditdate]['CreditDate'] = date('d/M/Y',strtotime($creditdate));
        $PHLData[$scheduledPerson][$creditdate]['PHLAmount'] = round($ExpiringPHL['rolling_sum'],2); 
        $PHLData[$scheduledPerson][$creditdate]['PHLUsed'] = round($ExpiringPHL['rolling_d_sum'] ?? 0,2);
        $PHLData[$scheduledPerson][$creditdate]['expiryDate'] = $expireddateData[0];
        $PHLData[$scheduledPerson][$creditdate]['StaffNumber'] = $ExpiringPHL['StaffNumber'];
        $PHLData[$scheduledPerson][$creditdate]['Name'] =  $ExpiringPHL['userDisplayName'];
        $PHLData[$scheduledPerson][$creditdate]['iYear'] = $ExpiringPHL['iYear'];
        $PHLData[$scheduledPerson][$creditdate]['comment']= $ExpiringPHL['Comments'];
        $PHLData[$scheduledPerson][$creditdate]['TimeDemensionID']= $ExpiringPHL['TimeDemensionID'];
        $PHLData[$scheduledPerson][$creditdate]['SchedulingTeamid']= $teamID; 
        $PHLData[$scheduledPerson][$creditdate]['ID']= $ExpiringPHL['ID'];   
    }
}

  

$objPHPExcel = new Spreadsheet();


$objPHPExcel->setActiveSheetIndex(0);

$styleArray = new Style();

$styleArray = [
    'font'  => [
				'size'  => 10,
				'name'  => 'Arial'
	],
    'borders' => [
		'top' => ['borderStyle' => Border::BORDER_THIN],
        'bottom' => ['borderStyle' => Border::BORDER_THIN],
        'right' => ['borderStyle' => Border::BORDER_THIN],
		'left' => ['borderStyle' => Border::BORDER_THIN],
        'outline' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];
 
// Set document properties
	$objPHPExcel->getProperties()->setCreator("Allocate7")->setLastModifiedBy("Allocate7")->setTitle("BBC News Allocate");
	$objPHPExcel->setActiveSheetIndex(0);
//----------------------------------1st worksheet starts here-----------------------------------------------------------------------
	$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
	if ($selectedweek > 1) {
		$Heading =' BBC News - PHL Expiring in the Next '.$selectedweek.' Weeks.';
	} else {
		$Heading =' BBC News - PHL Expiring in the Next '.$selectedweek.' Week.';
	}
	$objPHPExcel->getActiveSheet()->setCellValue('A1',$Heading);

	$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
	$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Created on '.date('d/m/Y'));
	// Set excel header
	$objPHPExcel->getActiveSheet()->getStyle('A5:H5')->getFont()->setBold( true );
	$objPHPExcel->getActiveSheet()->getStyle('A5:H5')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Name');
	$objPHPExcel->getActiveSheet()->setCellValue('B5', 'NetLogin');
	$objPHPExcel->getActiveSheet()->setCellValue('C5', 'Leave Year');
	$objPHPExcel->getActiveSheet()->setCellValue('D5', 'PHL Date');
	$objPHPExcel->getActiveSheet()->setCellValue('E5', 'PHL Amount');
	$objPHPExcel->getActiveSheet()->setCellValue('F5', 'Expiry Date');
	$objPHPExcel->getActiveSheet()->setCellValue('G5', 'PHL Expiring');
	$objPHPExcel->getActiveSheet()->setCellValue('H5', 'Comment');
	$i=6;
	if (!empty($PHLData)) {
		foreach ($PHLData as $key => $PHLExpireRow) {
		   foreach ($PHLExpireRow as $innerKey=> $getActivityCode_d) {
			$datediff = strtotime($getActivityCode_d['expiryDate']) - strtotime(date('Y-m-d'));
			$expiryDateFormt = date('d/M/Y',strtotime($getActivityCode_d['expiryDate']));
			if(isset($getActivityCode_d['Balance']) && $getActivityCode_d['Balance'] < 0 && $datediff > 0) { 
					$objPHPExcel->getActiveSheet()->getStyle("A$i:H$i")->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $getActivityCode_d['Name']);
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $getActivityCode_d['StaffNumber']);
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $getActivityCode_d['iYear']);
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $getActivityCode_d['CreditDate']);
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $getActivityCode_d['PHLAmount']);
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $expiryDateFormt);
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $getActivityCode_d['Balance']);
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $getActivityCode_d['comment']);
				$i++;
			}
		  }
  		}
 }else {
	$objPHPExcel->getActiveSheet()->setCellValue('A6',"No Record Found.");

	$objPHPExcel->getActiveSheet()->mergeCells('A6:H6');
}
	$objPHPExcel->getActiveSheet()->setTitle('Expiring PHL Dump');
//----------------------------------1st worksheet ends here-------------------------------------------------------------------------


$activeSheet = $objPHPExcel->getActiveSheet();
// Redirect output to a client�s web browser (Excel5)
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;filename="ExpiringPHLs.xlsx"');
  header('Cache-Control: max-age=0');
  // If you're serving to IE 9, then the following may be needed
  header('Cache-Control: max-age=1');

  // If you're serving to IE over SSL, then the following may be needed
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
  header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
  header('Pragma: public'); // HTTP/1.0

  $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
  ob_end_clean();
  $objWriter->save('php://output'); 
