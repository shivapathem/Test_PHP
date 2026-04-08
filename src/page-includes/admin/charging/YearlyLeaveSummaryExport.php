<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
  }
$pageid = 21;
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';
require_once '../../../function-includes/DBHelper.php';
require_once '../../../function-includes/PHPExcel/classes/PHPExcel.php';
include_once '../../../function-includes/helpers.php';
include_once '../../../function-includes/userGroupfunctions.php';
$pdo = OpenDBLinkA7();
$permissions = getUserRolePermissions($pageid);

if (isset($permissions->canview) && $permissions->canview == 1) {
    $yearType 	    = trim($_GET['yearType']);
	$reportType 	= trim($_GET['reportType']);
	$selectedLeaveYEarFrom	= trim($_GET['leaveYear']);
	$selectedleaveYEarTo 	= trim($_GET['leaveFinsh']);
	$selectedgroupBy1 	= trim($_GET['groupBy1']);
	$selectedgroupBy2 	= trim($_GET['groupBy2']);
	$selectedstaffnumbers  = trim($_GET['Staff_Numbers']);
	$selectedschedulingTeamIds = trim($_GET['schedulingTeam']);
    if ($_GET['estabCodes']=="null") {
        $selectedchargeCodes = null;
    } else {
        $selectedchargeCodes = trim($_GET['estabCodes']);
    }
	$DisplayColumns	= explode(",", $_GET['DisplayColumns']);
	$selectedfilterColName	= trim($_GET['columnFilter']);
	$selectedfilterSign	 = trim($_GET['FilterSymbol']);
	$selectedfiltervalue = trim($_GET['FilterCompValue']);
    $orderByStr = base64_decode($_GET['orderBy']);
    $orderByArr = explode(',', $orderByStr);
    $sortingType = $orderByArr[1] ? $orderByArr[1] : 'asc';
    $sortingCol = 'DisplayName';
   
    switch ($orderByArr[0]) {
        case 1 :
            $sortingCol = 'StaffNumber';
            break;
        case 2 :
            $sortingCol = 'schedulingTeamName';
            break;
        case 3 :
            $sortingCol = 'Charge_Codes';
            break;
        case 4 :
            $sortingCol = 'iyear';
            break;
        case 5 :
            $sortingCol = 'Comp';
            break;
        case 6 :
            $sortingCol = 'PHL';
            break;
        case 7 :
            $sortingCol = 'Annual';
            break;
        case 8 :
            $sortingCol = 'Under11TOIL';
            break;
        case 9 :
           $sortingCol = 'Over12TOIL';
            break;
        case 10 :
            $sortingCol = 'Additional';
            break;
        case 11 :
            $sortingCol = 'Casual';
            break;
        case 12 :
            $sortingCol = 'Exceptional';
            break;
        case 13 :
            $sortingCol = 'LongService';
            break;
        case 14 :
            $sortingCol = 'Other';
            break;
        case 15 :
            $sortingCol = 'TotalLeaveAllocated';
            break;
        case 16 :
            $sortingCol = 'CompTaken';
            break;
        case 17 :
            $sortingCol = 'PHLTaken';
            break;
        case 18 :
            $sortingCol = 'AnnualTaken';
            break;
        case 19 :
            $sortingCol = 'Under11TOILTaken';
            break;
        case 20 :
            $sortingCol = 'Over12TOILTaken';
            break;
        case 21:
            $sortingCol = 'AdditionalTaken';
            break;
        case 22 :
            $sortingCol = 'CasualTaken';
            break;
        case 23:
            $sortingCol = 'ExceptionalTaken';
            break;
        case 24 :
            $sortingCol = 'LongServiceTaken';
            break;
        case 25 :
            $sortingCol = 'OtherTaken';
            break;
        case 26 :
            $sortingCol = 'TotalLeaveTaken';
            break;
        case 27 :
            $sortingCol = 'CompRemaining';
            break;
        case 28 :
            $sortingCol = 'PHLRemaining';
             break;
        case 29:
            $sortingCol = 'AnnualRemaining';
            break;
        case 30:
            $sortingCol = 'Under11TOILRemaining';
            break;
        case 31:
            $sortingCol = 'Over12TOILRemaining';
            break;
        case 32:
            $sortingCol = 'AdditionalRemaining';
            break;
        case 33:
            $sortingCol = 'CasualRemaining';
            break;
        case 34:
            $sortingCol = 'ExceptionalRemaining';
            break;
        case 35:
            $sortingCol = 'LongServiceRemaining';
            break;
        case 36:
            $sortingCol = 'OtherRemaining';
            break;
        case 37:
            $sortingCol = 'TotalLeaveRemaining';
            break;
     }

    $selectedTeamID=[];
    $selectedTeamNames=[];
    if (isset($selectedschedulingTeamIds) && !empty($selectedschedulingTeamIds)) {
        if (strpos($selectedschedulingTeamIds, ",") !== false) {
            $selectedTeamID = explode(",", $selectedschedulingTeamIds);
        } else {
            $selectedTeamID[] = $selectedschedulingTeamIds;
        }
    }
    if (!empty($selectedTeamID)) {
        foreach ($selectedTeamID as $teamID) {
            $selectedTeamNames[] = GetTeamNameByID($teamID);
        }
    }
        $teams =implode(",", $selectedTeamNames);
        if (is_array($DisplayColumns) && !empty($DisplayColumns)) {
            foreach ($DisplayColumns as $selectedcol) {
                $keyvalueArray = explode("^", $selectedcol);
                $appliedFilterList[$keyvalueArray[0]] =  $keyvalueArray[1];
            }
            $selectedavailableColumn = $appliedFilterList;
        }

        if (!empty($selectedavailableColumn)) {
            foreach ($selectedavailableColumn as $key => $value) {
                $keyvalueArray = explode("_", $key);
                $Filtercollist[$keyvalueArray[1]] = ucwords($value);
            }
        }
    
    $DBColumns = array_keys($Filtercollist);
    $DBDisplayColumns = array_values($Filtercollist);

    if (isset($selectedfilterColName) && ($selectedfilterColName =='NA')) {
        $selectedfilterColName = null;
        $selectedfilterSign    = null;
        $selectedfiltervalue   = null;
    }

    if (isset($selectedgroupBy1) && ($selectedgroupBy1 =='Nothing')) {
        $selectedgroupBy1 = null;
    }
    if (isset($selectedgroupBy2) && ($selectedgroupBy2 =='Nothing')) {
        $selectedgroupBy2 = null;
    }
    $VDBColumns = null;

    if (($selectedgroupBy1 != null) && ($selectedgroupBy2 !=null)) {
        $sortingCol  = null;
        $sortingType = null;
    } else {
        if ($selectedgroupBy1 != null) {
            $sortingCol = $selectedgroupBy1;
        } elseif ($selectedgroupBy2 != null) {
            $sortingCol = $selectedgroupBy2;
        }
    }
    
    if (($selectedgroupBy1 == null) && ($selectedgroupBy2 == null)) {
        $VDBColumns[]='ScheduledPersonID';
        $VDBColumns[]='StaffNumber';
        $VDBColumns[]='DisplayName';
        $VDBColumns[]='iyear';
        $VDBColumns[]='schedulingTeamName';
        $VDBColumns[]='Charge_Codes';
    }
   
    if (is_array($DBColumns)) {
        $selectedDBColumns = implode(",", $DBColumns);
    } else {
        $selectedDBColumns = $DBColumns;
    }
    if (is_array($VDBColumns)) {
        $selectedVDBColumns = implode(",", $VDBColumns);
    } else {
        $selectedVDBColumns = $VDBColumns;
    }
    if ($selectedstaffnumbers=='') {
        $selectedstaffnumbers = null;
    }

    try {
        $LeaveSummery_F=[];
        $LeaveSummery_Q = "exec [dbo].[usp_get_LeaveReportSummary] :TeamsID, :VDBColumns, :ColumnsNames, :grp1, :grp2, :LeaveYEarFrom, :leaveYEarTo, :chargeCodes,:staffNumbers,:filterColName,:filterSign,:filtervalue,:sortingCol, :sortingType";
        $stmt = $pdo->prepare($LeaveSummery_Q);
            // The parameters
        $stmt->bindParam(':TeamsID', $selectedschedulingTeamIds, PDO::PARAM_STR);
        $stmt->bindParam(':VDBColumns', $selectedVDBColumns, PDO::PARAM_STR);
        $stmt->bindParam(':ColumnsNames', $selectedDBColumns, PDO::PARAM_STR);
        $stmt->bindParam(':grp1', $selectedgroupBy1, PDO::PARAM_STR);
        $stmt->bindParam(':grp2', $selectedgroupBy2, PDO::PARAM_STR);
        $stmt->bindParam(':LeaveYEarFrom', $selectedLeaveYEarFrom, PDO::PARAM_INT);
        $stmt->bindParam(':leaveYEarTo', $selectedleaveYEarTo, PDO::PARAM_INT);
        $stmt->bindParam(':chargeCodes', $selectedchargeCodes, PDO::PARAM_STR);
        $stmt->bindParam(':staffNumbers', $selectedstaffnumbers, PDO::PARAM_STR);
        $stmt->bindParam(':filterColName', $selectedfilterColName, PDO::PARAM_STR);
        $stmt->bindParam(':filterSign', $selectedfilterSign, PDO::PARAM_STR);
        $stmt->bindParam(':filtervalue', $selectedfiltervalue, PDO::PARAM_INT);
        $stmt->bindParam(':sortingCol', $sortingCol, PDO::PARAM_STR);
        $stmt->bindParam(':sortingType', $sortingType, PDO::PARAM_STR);
        $stmt->execute();
        $LeaveSummery_F = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
     logger()->critical('DB Error', (array) $e);
     }

    $objPHPExcel = new PHPExcel();
    $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );

    $colName=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AX','AY','AZ'];

    $coln0 = 5;
    $rowNo = 4;
    $topRow=[];
    if (($selectedgroupBy1 == null) && ($selectedgroupBy2==null)) {
        $colrequired = -2;
    } else {
        $colrequired =3;
    }
    
   
    if (isset($LeaveSummery_F[0]) && (!empty($LeaveSummery_F[0])) && (is_array($LeaveSummery_F[0]))) {
        $topRow = array_keys($LeaveSummery_F[0]);
    }
    if (!empty($topRow)) { $colrequired += count($topRow); }

    // Set document properties
    $objPHPExcel->getProperties()->setCreator("Allocate7")->setLastModifiedBy("Allocate7")->setTitle("BBC News Allocate");
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:$colName[$colrequired]1")->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle("A1:$colName[$colrequired]1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->mergeCells("A1:$colName[$colrequired]1");
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'BBC News Allocate');

    $objPHPExcel->getActiveSheet()->getStyle("A2:$colName[$colrequired]2")->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->mergeCells("A2:$colName[$colrequired]2");
    $objPHPExcel->getActiveSheet()->getStyle("A2:$colName[$colrequired]2")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A2', "Leave Summary Report From $selectedLeaveYEarFrom To $selectedleaveYEarTo");
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);

    $objPHPExcel->getActiveSheet()->getStyle("A3:$colName[$colrequired]3")->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle("A3:$colName[$colrequired]3")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->mergeCells("A3:$colName[$colrequired]3");
    $objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A3', "Scheduling Teams - $teams");
    // Set excel header
    $objPHPExcel->getActiveSheet()->getStyle("A4:$colName[$colrequired]$rowNo")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle("A4:$colName[$colrequired]$rowNo")->applyFromArray($styleArray);
    
    for ($k=0;$k<1;$k++) {
        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'Name');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'Staff Number');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'Scheduling Team');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'Charge Code');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', 'Leave Year');
        if (in_array('Comp', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Comp'].' Credit');
            $coln0++;
        }
        if (in_array('PHL', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['PHL'].' Credit');
            $coln0++;
        }
        if (in_array('Annual', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Annual'].' Credit');
            $coln0++;
        }
        if (in_array('Under11TOIL', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Under11TOIL'].' Credit');
            $coln0++;
        }
        if (in_array('Over12TOIL', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Over12TOIL'].' Credit');
            $coln0++;
        }
        if (in_array('Additional', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Additional'].' Credit');
            $coln0++;
        }
        if (in_array('Casual', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Casual'].' Credit');
            $coln0++;
        }
        if (in_array('Exceptional', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Exceptional'].' Credit');
            $coln0++;
        }
        if (in_array('LongService', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['LongService'].' Credit');
            $coln0++;
        }
        if (in_array('Other', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Other'].' Credit');
            $coln0++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Total Credit');
        $coln0++;
                    /// Debit
        if (in_array('Comp', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Comp'].' Taken');
            $coln0++;
        }
        if (in_array('PHL', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['PHL'].' Taken');
            $coln0++;
        }
        if (in_array('Annual', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Annual'].' Taken');
            $coln0++;
        }
        if (in_array('Under11TOIL', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Under11TOIL'].' Taken');
            $coln0++;
        }
        if (in_array('Over12TOIL', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Over12TOIL'].' Taken');
            $coln0++;
        }
        if (in_array('Additional', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Additional'].' Taken');
            $coln0++;
        }
        if (in_array('Casual', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Casual'].' Taken');
            $coln0++;
        }
        if (in_array('Exceptional', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Exceptional'].' Taken');
            $coln0++;
        }
        if (in_array('LongService', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['LongService'].' Taken');
            $coln0++;
        }
        if (in_array('Other', $DBColumns)) {
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Other'].' Taken');
            $coln0++;
        }
       $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Total Taken');
       $coln0++;
      //Remaining
      if (in_array('Comp', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Comp'].' Remain');
        $coln0++;
      }
      if (in_array('PHL', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['PHL'].' Remain');
        $coln0++;
      }
      if (in_array('Annual', $DBColumns)) {
          $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Annual'].' Remain');
          $coln0++;
      }
      if (in_array('Under11TOIL', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Under11TOIL'].' Remain');
        $coln0++;
      }
      if (in_array('Over12TOIL', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Over12TOIL'].' Remaining');
        $coln0++;
      }
      if (in_array('Additional', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Additional'].'  Remain');
        $coln0++;
      }
      if (in_array('Casual', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Casual'].' Remain');
        $coln0++;
      }
      if (in_array('Exceptional', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Exceptional'].' Remain');
        $coln0++;
      }
      if (in_array('LongService', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['LongService'].' Remain');
        $coln0++;
      }
      if (in_array('Other', $DBColumns)) {
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Filtercollist['Other'].' Remain');
        $coln0++;
      }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Total Remain');
        /** Body Section Start*/
        $rowNo++;
    }
    //Header Loop close
    //Body Row Start
        if (!empty($LeaveSummery_F) && is_array($LeaveSummery_F)) {
            foreach ($LeaveSummery_F as $Row) {
                $coln0 = 5;
                $creditTotal=$debitTotal=$remainingTotal=0;
                if (isset($Row['DisplayName'])) { $displayname = $Row['DisplayName']; } else { $displayname ='-';}
                $objPHPExcel->getActiveSheet()->setCellValue("A$rowNo", $displayname);

                if (isset($Row['StaffNumber'])) { $StaffNumber = $Row['StaffNumber']; } else { $StaffNumber ='-';}
                $objPHPExcel->getActiveSheet()->setCellValue("B$rowNo", $StaffNumber);

                if (isset($Row['schedulingTeamName'])) { $schedulingTeamName = $Row['schedulingTeamName']; } else{ $schedulingTeamName ='-';}
                $objPHPExcel->getActiveSheet()->setCellValue("C$rowNo", $schedulingTeamName);

                if (isset($Row['Charge_Codes'])) { $Charge_Codes = $Row['Charge_Codes']; } else{ $Charge_Codes ='-';}
                $objPHPExcel->getActiveSheet()->setCellValue("D$rowNo", $Charge_Codes);

                if (isset($Row['iyear'])) { $iyear = $Row['iyear']; } else { $iyear= '-';}
                $objPHPExcel->getActiveSheet()->setCellValue("E$rowNo", $iyear);
                
                if (in_array('Comp', $topRow)) {
                    if ($Row['Comp'] < 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
                    } else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '90ee90')
                                )
                            )
                        );
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Comp'],2));
                    $coln0++;
                }
   
                if (in_array('PHL', $topRow)) {
                        if ($Row['PHL'] < 0) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'FF0000')
                                    )
                                )
                            );
                        } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '90ee90')
                                    )
                                )
                            );
                        }
                        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['PHL'], 2));
                        $coln0++;
                }

                if (in_array('Annual', $topRow)) {
                    if ($Row['Annual'] < 0) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'FF0000')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '90ee90')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Annual'], 2));
                    $coln0++;
                }
    
                
                if (in_array('Under11TOIL', $topRow)) {
                    if ($Row['Under11TOIL'] < 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
                    } else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '90ee90')
                                )
                            )
                        );
                    }
                    
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Under11TOIL'],2));
                    $coln0++;
                }

                if (in_array('Over12TOIL', $topRow)) {
                    if ($Row['Over12TOIL'] < 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
                    } else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '90ee90')
                                )
                            )
                        );
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Over12TOIL'],2));
                    $coln0++;
                }

                if (in_array('Additional',$topRow)) {
                    if ($Row['Additional'] < 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
                    } else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '90ee90')
                                )
                            )
                        );
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Additional'], 2));
                    $coln0++;
                }

                if (in_array('Casual', $topRow)) {
                    if ($Row['Casual'] < 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
                    } else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '90ee90')
                                )
                            )
                        );
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Casual'],2));
                    $coln0++;
                }

                if (in_array('Exceptional', $topRow)) {
                    if ($Row['Exceptional'] < 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
                    } else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '90ee90')
                                )
                            )
                        );
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Exceptional'],2));
                    $coln0++;
                }
                if (in_array('LongService',$topRow)) {
                    if ($Row['LongService'] < 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
                    } else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '90ee90')
                                )
                            )
                        );
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['LongService'],2));
                    $coln0++;
                }
                if (in_array('Other',$topRow)) {
                    if ($Row['Other'] < 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
                    }  else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '90ee90')
                                )
                            )
                        );
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Other'],2));
                    $coln0++;
                }

                if ($Row['TotalLeaveAllocated'] < 0) {
                    $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'FF0000')
                            )
                        )
                    );
                }  else {
                    $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '009933')
                            )
                        )
                    );
                }
                $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['TotalLeaveAllocated'],2));
                $coln0++;

        if (in_array('CompTaken', $topRow)) {
            if ($Row['CompTaken'] < 0) {
                $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF0000')
                                )
                            )
                        );
            }  else {
                        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FFB6C1')
                                )
                            )
                        );
            }
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo",round($Row['CompTaken'], 2));
            $coln0++;
        }

        if (in_array('PHLTaken', $topRow)) {
            if ($Row['PHLTaken'] < 0) {
                $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
            } else {
                $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FFB6C1')
                        )
                    )
                );
            }
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['PHLTaken'], 2));
            $coln0++;
        }

    if (in_array('AnnualTaken', $topRow)) {
        if ($Row['AnnualTaken'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        } else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFB6C1')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['AnnualTaken'], 2));
        $coln0++;
    }
    
    if (in_array('Under11TOILTaken', $topRow)) {
        if ($Row['Under11TOILTaken'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        } else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFB6C1')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Under11TOILTaken'],2));
        $coln0++;
    }
    if (in_array('Over12TOILTaken', $topRow)) {
        if ($Row['Over12TOILTaken'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        } else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFB6C1')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Over12TOILTaken'], 2));
        $coln0++;
     }
    if (in_array('AdditionalTaken',$topRow)) {
         if ($Row['AdditionalTaken'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
         } else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFB6C1')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['AdditionalTaken'],2));
        $coln0++;
    }
    if (in_array('CasualTaken', $topRow)) {
         if ($Row['CasualTaken'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
         } else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFB6C1')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['CasualTaken'], 2));
        $coln0++;
    }
    if (in_array('ExceptionalTaken', $topRow)) {
         if ($Row['ExceptionalTaken'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
         } else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFB6C1')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['ExceptionalTaken'],2));
        $coln0++;
    }
    if (in_array('LongServiceTaken', $topRow)) {
        if ($Row['LongServiceTaken'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFB6C1')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['LongServiceTaken'],2));
        $coln0++;
     }
    if (in_array('OtherTaken', $topRow)) {
        if ($Row['OtherTaken'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        } else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFB6C1')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['OtherTaken'], 2));
        $coln0++;
    }

    if ($Row['TotalLeaveTaken'] < 0) {
        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                )
            )
        );
     } else {
        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ff33cc')
                )
            )
        );
    }
    $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['TotalLeaveTaken'], 2));
    $coln0++;
    if (in_array('CompRemaining', $topRow)) {
        if ($Row['CompRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['CompRemaining'], 2));
        $coln0++;
    }
    if (in_array('PHLRemaining', $topRow)) {
        if ($Row['PHLRemaining'] < 0) {
           $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
               array(
                   'fill' => array(
                       'type' => PHPExcel_Style_Fill::FILL_SOLID,
                       'color' => array('rgb' => 'FF0000')
                   )
               )
           );
        }  else {
           $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
               array(
                   'fill' => array(
                       'type' => PHPExcel_Style_Fill::FILL_SOLID,
                       'color' => array('rgb' => '90ee90')
                   )
               )
           );
       }
       $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo",round($Row['PHLRemaining'], 2));
       $coln0++;
   }
    if (in_array('AnnualRemaining', $topRow)) {
        if ($Row['AnnualRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
         }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['AnnualRemaining'],2));
        $coln0++;
    }
    if (in_array('Under11TOILRemaining',$topRow)) {
         if ($Row['Under11TOILRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
         }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Under11TOILRemaining'], 2));
        $coln0++;
    }
    if (in_array('Over12TOILRemaining',$topRow)) {
        if ($Row['Over12TOILRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['Over12TOILRemainings'], 2));
        $coln0++;
    }
    if (in_array('AdditionalRemaining',$topRow)) {
           if ($Row['AdditionalRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
           }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
         $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['AdditionalRemaining'], 2));
         $coln0++;
    }
    
    if (in_array('CasualRemaining', $topRow)) {
         if ($Row['CasualRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
         }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['CasualRemaining'],2));
        $coln0++;
    }
    
    
    if (in_array('ExceptionalRemaining', $topRow)) {
        if ($Row['ExceptionalRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['ExceptionalRemaining'], 2));
        $coln0++;
    }
    

    if (in_array('LongServiceRemaining',$topRow)) {
        if ($Row['LongServiceRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        }  else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['LongServiceRemaining'], 2));
        $coln0++;
    }
   
    if (in_array('OtherRemaining', $topRow)) {
        if ($Row['OtherRemaining'] < 0) {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FF0000')
                    )
                )
            );
        } else {
            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '90ee90')
                    )
                )
            );
        }
        $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['OtherRemaining'],2));
        $coln0++;
    }

    if ($Row['TotalLeaveRemaining'] < 0) {
        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                )
            )
        );
     }  else {
        $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '009933')
                )
            )
        );
    }
    $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", round($Row['TotalLeaveRemaining']
    , 2));
    $rowNo++;
            }
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="LeaveSummaryReport.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
    
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public'); // HTTP/1.0
    
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

}