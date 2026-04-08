<?php
$pageid = 19;
session_start();
require_once '../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';
include_once '../../../function-includes/testaccess.php';
require_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/common/classCommonDBFunctions.php';
$pdo = OpenDBLinkA7();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\IOFactory;

$userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
if (!empty($_GET['schedulingTeam'])) {
    $reportType     = $_GET['reportType'];
    $chargeStatus     = $_GET['chargeStatus'];
    $sapDate         = $_GET['sapDate'];
    $actualStatus     = $_GET['actualStatus'];
    $weeksRange     = $_GET['weeksRange'];
    $weeksStart     = $_GET['weeksStart'];
    $toYear             = $_GET['toYear'];
    $weekFinish     = $_GET['weekFinish'];
    $fromYear         = $_GET['fromYear'];
    $schedulingTeam    = $_GET['schedulingTeam'];
    $estabCodes        = $_GET['estabCodes'];
    $receiverCode    = $_GET['receiverCode'];
    $wbsCodes        = $_GET['wbsCodes'];
    $staffNumber    = $_GET['staffNumber'];
    $groupBy1        = $_GET['groupBy1'];
    $groupBy2        = $_GET['groupBy2'];
    $orderByStr = base64_decode($_GET['orderBy']);
    $orderByArr = explode(',', $orderByStr);
    $sortingType = $orderByArr[1] ? $orderByArr[1] : 'asc';
    switch ($orderByArr[0]) {
        case 1:
            $sortingCol = 'EstablishCode';
            break;
        case 2:
            $sortingCol = 'ActivityCodeName';
            break;
        case 3:
            $sortingCol = 'DisplayName';
            break;
        case 4:
            $sortingCol = 'StaffNumber';
            break;
        case 5:
            $sortingCol = 'ixWeekInYear';
            break;
        case 6:
            $sortingCol = 'sDayName';
            break;
        case 7:
            $sortingCol = 'ChargingDutyDate';
            break;
        case 8:
            $sortingCol = 'DutyName';
            break;
        case 9:
            $sortingCol = 'Comments';
            break;
        case 10:
            $sortingCol = 'ChargeCode';
            break;
        case 11:
            $sortingCol = 'IsActual';
            break;
        case 12:
            $sortingCol = 'ModifiedDate';
            break;
        case 13:
            $sortingCol = 'Quantity';
            break;
        case 14:
            $sortingCol = 'UnitPrice';
            break;
        case 15:
            $sortingCol = 'TotalPrice';
            break;
        case 16:
            $sortingCol = 'CreatedBy';
            break;
        case 17:
            $sortingCol = 'SentToFinanceDate';
            break;
        default:
            $sortingCol = 'schedulingTeamName';
            break;
    }
    $chargeCodeContainer = $chargeCodeContainer ?? '';
    $haystack = $haystack ?? '';
    if (strpos($chargeCodeContainer, ',') === false) {
        $chargeCodeContainerStr = "'" . $chargeCodeContainer . "'";
    } else {
        $chargeCodeContainerArr = explode(',', $chargeCodeContainer);
        $chargeCodeContainerArr = array_filter($chargeCodeContainerArr);
        $chargeCodeContainerStr = "'" . implode("','", $chargeCodeContainerArr) . "'";
    }

    $summaryReport_q = 'exec [dbo].[usp_getWeeklyChargingSummary] ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @userID = ?';
    $summaryReport_r = $pdo->prepare($summaryReport_q);
    $summaryReport_r->execute(array($reportType, $chargeStatus, $sapDate, $actualStatus, $weeksRange, $weeksStart, $toYear, $weekFinish, $fromYear, $schedulingTeam, $estabCodes, $receiverCode, $wbsCodes, $groupBy1, $groupBy2, $staffNumber, $sortingCol, $sortingType, $userID));
    $summaryReport_f = $summaryReport_r->fetchAll(PDO::FETCH_ASSOC);
    usort($summaryReport_f, function ($a, $b) use ($sortingType) {
        $aDate = isset($a['ChargingDutyDate']) ? strtotime($a['ChargingDutyDate']) : 0;
        $bDate = isset($b['ChargingDutyDate']) ? strtotime($b['ChargingDutyDate']) : 0;

        return $sortingType == 'asc' ? $aDate - $bDate : $bDate - $aDate;
    });
    $weekNumberStart = $weeksStart . '/' . $toYear;
    $weekNumberEnd = $weekFinish . '/' . $fromYear;
    $commonObj = new classCommonDBFunctions();
    $weekStartDate = $commonObj->GetWeekStartDateByWeekNoFromTimeDim(($toYear . $weeksStart), 'ByweeknoOnly', NULL);
    $weekEndDate = $commonObj->GetWeekStartDateByWeekNoFromTimeDim(($fromYear . $weekFinish), 'ByweeknoOnly', NULL);
    $weekStartDate = date('d/m/Y', strtotime($weekStartDate['dDateTime']));
    $weekEndDate = date('d/m/Y', strtotime("+6 day", strtotime($weekEndDate['dDateTime'])));
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
    $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A1:R1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:R1');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'BBC News Allocate');

    $objPHPExcel->getActiveSheet()->getStyle('A2:R2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->mergeCells('A2:R2');
    $objPHPExcel->getActiveSheet()->getStyle('A2:R2')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(30);
    if ($weeksRange == 'RoW') {
        $objPHPExcel->getActiveSheet()->setCellValue('A2', " - Charging Summary Report for Week $weekNumberStart ($weekStartDate) To $weekNumberEnd ($weekEndDate)");
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A2', " - Charging Summary Report for Week $weekNumberStart ($weekStartDate)");
    }
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);

    // Set excel header
    $objPHPExcel->getActiveSheet()->getStyle('A3:R3')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A3:R3')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Scheduling Team');
    $objPHPExcel->getActiveSheet()->setCellValue('B3', 'Charge Code');
    $objPHPExcel->getActiveSheet()->setCellValue('C3', 'Activity');
    $objPHPExcel->getActiveSheet()->setCellValue('D3', 'Name');
    $objPHPExcel->getActiveSheet()->setCellValue('E3', 'Staff Number');
    $objPHPExcel->getActiveSheet()->setCellValue('F3', 'Week');
    $objPHPExcel->getActiveSheet()->setCellValue('G3', 'Day');
    $objPHPExcel->getActiveSheet()->setCellValue('H3', 'Date');
    $objPHPExcel->getActiveSheet()->setCellValue('I3', 'Duty Name');
    $objPHPExcel->getActiveSheet()->setCellValue('J3', 'Comments');
    $objPHPExcel->getActiveSheet()->setCellValue('K3', 'Charge From');
    $objPHPExcel->getActiveSheet()->setCellValue('L3', 'Actual');
    $objPHPExcel->getActiveSheet()->setCellValue('M3', 'Actualised Date');
    $objPHPExcel->getActiveSheet()->setCellValue('N3', 'Qty');
    $objPHPExcel->getActiveSheet()->setCellValue('O3', 'Unit Price');
    $objPHPExcel->getActiveSheet()->setCellValue('P3', 'Total Price');
    $objPHPExcel->getActiveSheet()->setCellValue('Q3', 'Created By');
    $objPHPExcel->getActiveSheet()->setCellValue('R3', 'Sent to Finance Date');
    // Set column width
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(10);
    $i = 4;
    foreach ($summaryReport_f as $summaryReport_d) {
        $isActual = match ($summaryReport_d['IsActual'] ?? null) {
            "1" => 'Actual',
            "0" => 'Provisional',
            "2" => 'Hold',
            default => ''
        };

        $objPHPExcel->getActiveSheet()->getStyle("A$i:R$i")->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $summaryReport_d['schedulingTeamName'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $summaryReport_d['EstablishCode'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $summaryReport_d['ActivityCodeName'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $summaryReport_d['DisplayName'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, /*$summaryReport_d['StaffNumber'] ??*/ '');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $summaryReport_d['ixWeekInYear'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $summaryReport_d['sDayName'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, isset($summaryReport_d['ChargingDutyDate']) ? date('d/m/Y', strtotime($summaryReport_d['ChargingDutyDate'])) : 'NA');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $summaryReport_d['DutyName'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $summaryReport_d['Comments'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, $summaryReport_d['ChargeCode'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, $isActual);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, $summaryReport_d['ModifiedDate'] ?? ($summaryReport_d['CreatedDate'] ?? ''));
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, isset($summaryReport_d['Quantity']) ? (int)$summaryReport_d['Quantity'] : 0);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, isset($summaryReport_d['UnitPrice']) ? number_format((float)$summaryReport_d['UnitPrice'], 2, '.', '') : '0.00');
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, isset($summaryReport_d['TotalPrice']) ? number_format((float)$summaryReport_d['TotalPrice'], 2, '.', '') : '0.00');
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, $summaryReport_d['CreatedBy'] ?? '');
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $i, isset($summaryReport_d['SentToFinanceDate']) ? date('d/m/Y h:i:s', strtotime($summaryReport_d['SentToFinanceDate'])) : '');

        $i++;
    }
    $objPHPExcel->getActiveSheet()->getStyle('D1:D' . $i)->getAlignment()->setWrapText(true);
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="weeklyChargingSummaryReport.xlsx"');
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
