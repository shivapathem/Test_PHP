<?php
$pageid = 19;
session_start();
require_once '../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';
include_once '../../../function-includes/testaccess.php';
require_once '../../../function-includes/DBHelper.php';
require_once '../../allocations/weekly/service/AllocationService.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$pdo = OpenDBLinkA7();

$userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
$service = new AllocationService();
if (!empty($_GET['schedulingTeam'])) {
    $schedulingTeam         = $_GET['schedulingTeam'];
    $weekFrom               = $_GET['weekFrom'];
    $weekTo                 = $_GET['weekTo'];
    $ExtractDays            = $_GET['ExtractDays'];
    $extractFilter          = $_GET['extractFilter'];
    $includeLeave           = $_GET['includeLeave'];
    $includeJobData         = $_GET['includeJobData'];
    $reportType             = $_GET['reportType'];
    $extractGroup1          = $_GET['extractGroup1'];
    $extractGroup2          = $_GET['extractGroup2'];
    $staffNumber            = $_GET['staffNumber'];
    $staffNumberVal         = $_GET['staffNumberVal'];
    $extractJob             = $_GET['extractJob'];
    $extractJobVal          = trim($_GET['extractJobVal']);
    $extractProgramme       = $_GET['extractProgramme'];
    $extractProgrammeVal    = $_GET['extractProgrammeVal'];
    $extractContact         = $_GET['extractContact'];
    $extractContactVal      = $_GET['extractContactVal'];
    $extractLocation        = $_GET['extractLocation'];
    $extractLocationVal     = $_GET['extractLocationVal'];
    $extractDuty            = $_GET['extractDuty'];
    $extractDutyVal         = $_GET['extractDutyVal'];
    $extractFilterBy        = $_GET['extractFilterBy'];
    $extractFilterByCond    = $_GET['extractFilterByCond'];
    $extractFilterByCondVal = $_GET['extractFilterByCondVal'];
    $extractHistoryData     = $_GET['extractHistoryData'];
    $extractJobProgramme    = $_GET['extractJobProgramme'];
    $extractJobProgrammeVal = $_GET['extractJobProgrammeVal'];
    $orderByStr = base64_decode($_GET['orderBy']);
    $orderByArr = explode(',', $orderByStr);
    $sortingType = isset($orderByArr[1]) ? $orderByArr[1] : 'asc';
    switch ($orderByArr[0]) {
        case 0:
            $sortingCol = 'DisplayName';
            break;
        case 1:
            $sortingCol = 'StaffNumber';
            break;
        case 2:
            $sortingCol = 'sortcode';
            break;
        case 3:
            $sortingCol = 'DutyDate';
            break;
        case 4:
            $sortingCol = 'WeekNumber';
            break;
        case 5:
            $sortingCol = 'iDay';
            break;
        case 6:
            $sortingCol = 'Programme';
            break;
        case 7:
            $sortingCol = 'DutyName';
            break;
        case 8:
            $sortingCol = 'StartTime';
            break;
        case 9:
            $sortingCol = 'EndTime';
            break;
        case 10:
            $sortingCol = 'Duration';
            break;
        case 11:
            $sortingCol = 'JobName';
            break;
        case 12:
            $sortingCol = 'Location';
            break;
        case 13:
            $sortingCol = 'JobProgramme';
            break;
        case 14:
            $sortingCol = 'Contact';
            break;
        case 15:
            $sortingCol = 'DutyComments';
            break;
        case 16:
            $sortingCol = 'PersonComments';
            break;
        default:
            $sortingCol = 'DisplayName';
            break;
    }
    $chargeCodeContainer = $chargeCodeContainer ?? '';
    if (strpos($chargeCodeContainer, ',') === false) {
        $chargeCodeContainerStr = "'" . $chargeCodeContainer . "'";
    } else {
        $chargeCodeContainerArr = explode(',', $chargeCodeContainer);
        $chargeCodeContainerArr = array_filter($chargeCodeContainerArr);
        $chargeCodeContainerStr = "'" . implode("','", $chargeCodeContainerArr) . "'";
    }
    $summaryReport_q = "exec usp_getDutyExtractReportDetails ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @userID = ?";
    $summaryReport_r = $pdo->prepare($summaryReport_q);
    $summaryReport_r->execute(array($weekFrom, $weekTo, $ExtractDays, $extractFilter, $includeLeave, $includeJobData, $reportType, $extractGroup1, $extractGroup2, $staffNumber, $staffNumberVal, $extractJob, $extractJobVal, $extractProgramme, $extractProgrammeVal, $extractContact, $extractContactVal, $extractLocation, $extractLocationVal, $extractDuty, $extractDutyVal, $extractFilterBy, $extractFilterByCond, $extractFilterByCondVal, $extractHistoryData, $schedulingTeam, $extractJobProgramme, $extractJobProgrammeVal, $sortingCol, $sortingType, $userID));
    $summaryReport_f = $summaryReport_r->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($summaryReport_f)) {
        $newResultSet = [];
        foreach ($summaryReport_f as $dataValues) {
            $key = $dataValues['DutyDate'] . '|' . $dataValues['WeekNumber'] . '|' . $dataValues['iDay'] . '|' . $dataValues['DutyName'] . '|' . $dataValues['StaffNumber'];
            $jobEntry = [];
            $jobKey = null;
            if (!empty($dataValues['JobName'])) {
                $jobStartTime = $dataValues['JobStartTime'];
                $jobEndTime   = $dataValues['JobEndTime'];
                $jobDuration  = ($jobEndTime >= $jobStartTime)
                    ? ($jobEndTime - $jobStartTime)
                    : ((86400 + $jobEndTime) - $jobStartTime);

                $jobEntry = [
                    'JobStartTime' => $service->convertSecondsIntoTime($jobStartTime, ':', 'No'),
                    'JobEndTime'   => $service->convertSecondsIntoTime($jobEndTime, ':', 'No'),
                    'JobDuration'  => $service->convertSecondsIntoTime($jobDuration, ':', 'No'),
                    'JobName'      => $dataValues['JobName'],
                    'JobLabel'     => $dataValues['JobLabel'],
                    'JobLocation'  => $dataValues['Location'],
                    'JobContact'   => $dataValues['Contact'],
                ];
                // Hash key for duplicate detection
                $jobKey = $jobEntry['JobName'] . '|' . $jobEntry['JobStartTime'] . '|' . $jobEntry['JobEndTime'];
            }
            if (!isset($newResultSet[$key])) {
                $newResultSet[$key] = $dataValues;
                $newResultSet[$key]['jobdata'] = [];
                $newResultSet[$key]['jobdata_index'] = [];
                $newResultSet[$key]['DisplayAllocName'] = [];

                if (!empty($dataValues['DisplayAllocName'])) {
                    $newResultSet[$key]['DisplayAllocName'][$dataValues['DisplayAllocName']] = true;
                }

                if (!empty($jobEntry)) {
                    $newResultSet[$key]['jobdata'][] = $jobEntry;
                    $newResultSet[$key]['jobdata_index'][$jobKey] = true;
                }

                unset(
                    $newResultSet[$key]['JobName'],
                    $newResultSet[$key]['JobStartTime'],
                    $newResultSet[$key]['JobEndTime'],
                    $newResultSet[$key]['JobLabel'],
                    $newResultSet[$key]['Location'],
                    $newResultSet[$key]['Contact']
                );
            } else {
                if (!empty($jobEntry) && !isset($newResultSet[$key]['jobdata_index'][$jobKey])) {
                    $newResultSet[$key]['jobdata'][] = $jobEntry;
                    $newResultSet[$key]['jobdata_index'][$jobKey] = true;
                }

                if (!empty($dataValues['DisplayAllocName'])) {
                    $newResultSet[$key]['DisplayAllocName'][$dataValues['DisplayAllocName']] = true;
                }
            }
        }
        foreach ($newResultSet as &$entry) {
            $entry['DisplayAllocName'] = implode(', ', array_keys($entry['DisplayAllocName']));
            unset($entry['jobdata_index']);
            // Cache timestamp once
            $entry['_DutyDateTS'] = strtotime($entry['DutyDate']);
        }
        $newResultSet = array_values($newResultSet);
    }

    usort($newResultSet, function ($a, $b) use ($sortingType) {
        if ($sortingType === 'asc') {
            return ($a['_DutyDateTS'] <=> $b['_DutyDateTS'])
                ?: ($a['StartTime'] <=> $b['StartTime']);
        }
        return ($b['_DutyDateTS'] <=> $a['_DutyDateTS'])
            ?: ($b['StartTime'] <=> $a['StartTime']);
    });

    $iDayTxtArr = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    function formatDate($date)
    {
        return $date ? date('d/m/Y', strtotime($date)) : 'NA';
    }

    function isLeaveRow($dutyName, $jobName)
    {
        return (
            (!empty($jobName) && stripos($jobName, 'leave') !== false) ||
            (!empty($dutyName) && stripos($dutyName, 'leave') !== false)
        );
    }


    if (!empty($newResultSet)) {
        $templatePath = __DIR__.'/excel/DutyExtractReport_layout.xlsx';

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'BBC Allocate');
        $sheet->setCellValue('A2', "- Weeks Extract from Week $weekFrom to $weekTo");

        $headers = [
            'Scheduling Team',
            'Name',
            'Staff Number',
            'Sort Code',
            'Date',
            'Week',
            'Day',
            'Duty Label',
            'Duty Name',
            'Start',
            'End',
            'Duration',
            'Job Name',
            'Job Start',
            'Job End',
            'Job Duration',
            'Job Label',
            'Job Location',
            'Job Contact',
            'Duty Comments',
            'Person Comments',
            'Leave Type',
        ];

        if ($extractHistoryData) {
            $headers[] = 'History';
        }

        $col = 'A';
        $rowNum = 3; // Header row
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $rowNum, $header);
            $col++;
        }

        $rowNum = 4;

        foreach ($newResultSet as $row) {
            $durationSeconds = $row['Duration'] - $row['dutyBreakTime'];
            $totalDuration = $service->convertSecondsIntoTime($durationSeconds, '.', 'No');

            $baseData = [
                $row['HomeTeam'],
                $row['DisplayName'],
                $row['StaffNumber'],
                $row['sortcode'],
                formatDate($row['DutyDate']),
                $row['WeekNumber'],
                $iDayTxtArr[$row['iDay']] ?? '',
                implode(', ', array_filter([
                    $row['Programme'],
                    $row['DutyLabel2'],
                    $row['DutyLabel3'],
                    $row['DutyLabel4'],
                    $row['DutyLabel5'],
                    $row['DutyLabel6'],
                ])),
                $row['DutyName'],
                gmdate("H:i", $row['StartTime']),
                gmdate("H:i", $row['EndTime']),
                $totalDuration,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $row['DutyComments'],
                $row['PersonComments'],
                '',
            ];

            if (empty($row['jobdata'])) {
                if (isLeaveRow($row['DutyName'], '')) {
                    $baseData[21] = $row['DisplayAllocName'];
                }
                if ($extractHistoryData) {
                    $baseData[] = $row['History'];
                }

                $col = 'A';
                $colIndex = 0;
                foreach ($baseData as $value) {
                    if ($colIndex === 19 || $colIndex === 20) {
                        $sheet->setCellValueExplicit($col . $rowNum, $value, DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValue($col . $rowNum, $value);
                    }
                    $col++;
                    $colIndex++;
                }
                $rowNum++;
                continue;
            }

            foreach ($row['jobdata'] as $job) {
                $data = $baseData;
                $data[12] = $job['JobName'];
                $data[13] = $job['JobStartTime'];
                $data[14] = $job['JobEndTime'];
                $data[15] = $job['JobDuration'];
                $data[16] = $job['JobLabel'];
                $data[17] = $job['JobLocation'];
                $data[18] = $job['JobContact'];

                if (isLeaveRow($row['DutyName'], $job['JobName'])) {
                    $data[21] = $row['DisplayAllocName'];
                }
                if ($extractHistoryData) {
                    $data[] = $row['History'];
                }

                $col = 'A';
                $colIndex = 0;
                foreach ($data as $value) {
                    if ($colIndex === 19 || $colIndex === 20) {
                        $sheet->setCellValueExplicit($col . $rowNum, $value, DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValue($col . $rowNum, $value);
                    }
                    $col++;
                    $colIndex++;
                }
                $rowNum++;
            }
        }

        $filename = 'DutyExtractReport.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
