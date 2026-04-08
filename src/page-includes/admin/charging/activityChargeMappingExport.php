<?php
$pageid = 19;
session_start();
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';
require_once '../../../function-includes/DBHelper.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;
$pdo = OpenDBLinkA7();
$permissions = getUserRolePermissions($pageid);
if ($permissions->canview == 1)
{
    $mappingFrom = base64_decode($_GET['mappingFrom']);
    $mappingFrom = $mappingFrom ? $mappingFrom : date('Y');
    $mappingTo = base64_decode($_GET['mappingTo']);
    $mappingTo = $mappingTo ? $mappingTo : date('Y');
    $chargeCodeContainer = base64_decode($_GET['chargeCodeContainer']);
    $yearStr = ($mappingFrom == $mappingTo) ? $mappingFrom : "$mappingFrom to $mappingTo";
    $schedulingTeamTxt = base64_decode($_GET['schedulingTeamTxtArr']);
    $schedulingTeamArr = explode(',', $schedulingTeamTxt);
    $schedulingTeamTxt = implode(', ', $schedulingTeamArr);
    $orderByStr = base64_decode($_GET['orderBy']);
    $orderByArr = explode(',', $orderByStr);
    $sortingType = $orderByArr[1] ? $orderByArr[1] : 'asc';
    switch ($orderByArr[0]) {
        case 1 :
            $sortingCol = 'Year';
            break;
        case 2 :
            $sortingCol = 'EffectiveFrom';
            break;
        case 3 :
            $sortingCol = 'EstablishCode';
            break;
        case 4 :
            $sortingCol = 'EstablishCodeDescription';
            break;
        case 5 :
            $sortingCol = 'ActivityCodeName';
            break;
        case 6 :
            $sortingCol = 'Description';
            break;
        case 7 :
            $sortingCol = 'Price';
            break;
        default :
            $sortingCol = 'MappingId';
            break;
    }
    if (strpos($chargeCodeContainer, ',') === false) {
        $chargeCodeContainerStr = "'" . $chargeCodeContainer . "'";
    } else {
        $chargeCodeContainerArr = explode(',', $chargeCodeContainer);
        $chargeCodeContainerArr = array_filter($chargeCodeContainerArr);
        $chargeCodeContainerStr = "'" . implode("','", $chargeCodeContainerArr) . "'";
    }

    $getChargeCode_q = "SELECT MappingId, Year, EffectiveFrom, EstablishCode, EstablishCodeDescription, ActivityCodeName, Description, Price, ACCML.EstablishCodeId, ACCML.ActiveCodeId FROM ActivityChargeCodeMapping_Link ACCML JOIN ACTIVITYCODE AC ON ACCML.ActiveCodeId = AC.ActivityCodeId JOIN EstablishCode EC ON ACCML.EstablishCodeId = EC.EstablishCodeId WHERE EC.EstablishCode IN($chargeCodeContainerStr) AND ACCML.Year BETWEEN $mappingFrom AND $mappingTo ORDER BY $sortingCol $sortingType";
    $getChargeCode_r = $pdo->prepare($getChargeCode_q);
    $getChargeCode_r->execute();
    $getChargeCode_f = $getChargeCode_r->fetchAll(PDO::FETCH_ASSOC);
    $objPHPExcel = new Spreadsheet();
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'style' => Border::BORDER_THIN
            ]
        ]
    ];
    // Set document properties
    $objPHPExcel->getProperties()->setCreator("Allocate7")->setLastModifiedBy("Allocate7")->setTitle("BBC News Allocate");
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'BBC News Allocate');

    $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->mergeCells('A2:I2');
    $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A2', " - Charge Code Activity Code Mappings for $yearStr \n - $schedulingTeamTxt -");
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);

    // Set excel header
    $objPHPExcel->getActiveSheet()->getStyle('A3:J3')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'ID');
    $objPHPExcel->getActiveSheet()->setCellValue('B3', 'Year');
    $objPHPExcel->getActiveSheet()->setCellValue('C3', 'Effective From');
    $objPHPExcel->getActiveSheet()->setCellValue('D3', 'Charge Code');
    $objPHPExcel->getActiveSheet()->setCellValue('E3', 'Charge Code Desc');
    $objPHPExcel->getActiveSheet()->setCellValue('F3', 'Activity Code');
    $objPHPExcel->getActiveSheet()->setCellValue('G3', 'Activity Code Desc');
    $objPHPExcel->getActiveSheet()->setCellValue('H3', 'Price');
    // Set column width
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
    $i = 4;
    foreach ($getChargeCode_f as $getChargeCode_d) {
        $objPHPExcel->getActiveSheet()->getStyle("A$i:H$i")->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $getChargeCode_d['MappingId']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $getChargeCode_d['Year']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $getChargeCode_d['EffectiveFrom']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $getChargeCode_d['EstablishCode']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $getChargeCode_d['EstablishCodeDescription']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $getChargeCode_d['ActivityCodeName']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $getChargeCode_d['Description']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, '£' . number_format($getChargeCode_d['Price'], 2, '.', ''));
        $i++;
    }
    $objPHPExcel->getActiveSheet()->getStyle('D1:D' . $i)->getAlignment()->setWrapText(true);
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="ActivityCodeChargeCodeMapping_Allocate.xlsx"');
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
}