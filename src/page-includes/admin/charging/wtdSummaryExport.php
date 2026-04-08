<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
  }
$pageid = 23;
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';
require_once '../../../function-includes/DBHelper.php';
require_once '../../../function-includes/PHPExcel/classes/PHPExcel.php';
include_once '../../../function-includes/helpers.php';
include_once '../../../function-includes/userGroupfunctions.php';
$pdo = OpenDBLinkA7();
$permissions = getUserRolePermissions($pageid);
if ((isset($permissions->canview)) && ($permissions->canview == 1)) {
    $reportType 	 = trim($_GET['reportType']);
    $weekStartYear	 = trim($_GET['weekStartYear']);
    $weekToYear 	 = trim($_GET['weekToYear']);

    $selectedstaffnumbers  = trim($_GET['Staff_Numbers']);
    $selectedschedulingTeamIds = trim($_GET['schedulingTeam']);
    $selectedchargeCodes = trim($_GET['estabCodes']);
    $weekStart	= trim($_GET['weekStart']);
    $selectedchargeCodes1 = str_replace("'", " ", $selectedchargeCodes);
    $weekTo	= trim($_GET['weekTo']);

    $WTDbreechType = trim($_GET['WTDbreechType']);
    $BreachStatus = trim($_GET['BreachStatus']);
    $WTDFromWeekDate = trim($_GET['wtdstartDate']);
    $WTDToWeekDate	 = trim($_GET['wtdendDate']);
    $orderByStr = base64_decode($_GET['orderBy']);
    $orderByArr = explode(',', $orderByStr);
    $sortingType = $orderByArr[1] ? $orderByArr[1] : 'asc';
    $selectedTeamID = [];
    $selectedTeamNames = [];
    $sortingCol = 'schedulingTeamName';
    switch ($orderByArr[0]) {
        case 0 :
            $sortingCol = 'schedulingTeamName';
            break;
        case 1 :
            $sortingCol = 'DisplayName';
            break;
        case 2 :
            $sortingCol = 'Charge_Codes';
            break;
        case 3 :
            $sortingCol = 'StaffNumber';
            break;
        case 4 :
            $sortingCol = 'BreachType';
            break;
        case 5 :
            $sortingCol = 'StartDate';
            break;
        case 6 :
           $sortingCol = 'EndDate';
            break;
        case 7 :
            $sortingCol = 'BreachedBy';
            break;
        case 8 :
            $sortingCol = 'BreachedDate';
            break;
        case 9 :
            $sortingCol = 'ApprovedBy';
            break;
        case 10 :
            $sortingCol = 'ApprovedDate';
            break;
        case 11 :
            $sortingCol = 'Comments';
            break;
        }

        /** To Display on Top */
        $selectedgroupBy1 = null;
        $selectedgroupBy2 = null;
        if ($selectedstaffnumbers=='') {
            $selectedstaffnumbers = null;
        }
        if (isset($reportType) && ($reportType != 'ALL')) {
            $selectedgroupBy1 = trim($_GET['groupBy1']);
            $selectedgroupBy2 = trim($_GET['groupBy2']);
        }
        if (isset($selectedgroupBy1) && ($selectedgroupBy1 =='Nothing')) {
            $selectedgroupBy1 = null;
        }
        if (isset($selectedgroupBy2) && ($selectedgroupBy2 =='Nothing')) {
            $selectedgroupBy2 = null;
        }
        if (($selectedgroupBy1 !=null) || ($selectedgroupBy !=null)) {
            if ($selectedgroupBy1 != null) {
                $sortingCol = $selectedgroupBy1;
            } elseif ($selectedgroupBy2 != null) {
                $sortingCol = $selectedgroupBy2;
            }
        }

        if (isset($WTDbreechType) && ($WTDbreechType!='') && ($WTDbreechType=='ALL')) {
            $WTDbreechType = -1;
        }

        if (isset($BreachStatus) && ($BreachStatus!='')) {
            if ($BreachStatus=='ALL') {
                $approvalStatus=-1;
            } else {
                $approvalStatus = $BreachStatus;
            }
        }
        if (($selectedchargeCodes=='ALL') || (empty($selectedchargeCodes))) {
            $selectedchargeCodes = null;
        }

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
        try {
            $WTDSummery_F=[];
            $wtdSummery_Q = "exec [dbo].[usp_get_WTDSummary] :TeamsID,:grp1, :grp2, :WTDFromWeekDate,:WTDToWeekDate, :chargeCodes,:staffNumbers,:WTDbreechType,:approvalStatus,:orderby,:orderbyvlue";
            $stmt = $pdo->prepare($wtdSummery_Q);
            // The parameters
            $stmt->bindParam(':TeamsID', $selectedschedulingTeamIds, PDO::PARAM_STR);
            $stmt->bindParam(':grp1', $selectedgroupBy1, PDO::PARAM_STR);
            $stmt->bindParam(':grp2', $selectedgroupBy2, PDO::PARAM_STR);
            $stmt->bindParam(':WTDFromWeekDate', $WTDFromWeekDate, PDO::PARAM_INT);
            $stmt->bindParam(':WTDToWeekDate', $WTDToWeekDate, PDO::PARAM_INT);
            $stmt->bindParam(':chargeCodes', $selectedchargeCodes, PDO::PARAM_STR);
            $stmt->bindParam(':staffNumbers', $selectedstaffnumbers, PDO::PARAM_STR);
            $stmt->bindParam(':WTDbreechType', $WTDbreechType, PDO::PARAM_INT);
            $stmt->bindParam(':approvalStatus', $approvalStatus, PDO::PARAM_INT);
            $stmt->bindParam(':orderby', $sortingCol, PDO::PARAM_INT);
            $stmt->bindParam(':orderbyvlue', $sortingType, PDO::PARAM_INT);
            $stmt->execute();
            $WTDSummery_F = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $colName=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];

        $coln0 = 3;
        $rowNo = 4;
        $topRow=[];

        if (isset($WTDSummery_F) && !empty($WTDSummery_F)) {
            $topRow = array_keys($WTDSummery_F[0]);
        }
        
        if (($selectedgroupBy1 == null) && ($selectedgroupBy2==null)) {
            $colrequired =11;
        } else {
            $colrequired =4;
        }

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
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "WTD Summary Report From $WTDFromWeekDate To $WTDToWeekDate");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getStyle("A3:$colName[$colrequired]3")->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle("A3:$colName[$colrequired]3")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->mergeCells("A3:$colName[$colrequired]3");
        $objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->setCellValue('A3', "Scheduling Teams - $teams
        ");
    
        // Set excel header
        $objPHPExcel->getActiveSheet()->getStyle("A4:$colName[$colrequired]$rowNo")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("A4:$colName[$colrequired]$rowNo")->applyFromArray($styleArray);

        for ($k=0;$k<1;$k++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'Scheduling Team');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'Name');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'Charge Code');
            if (in_array('StaffNumber', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Staff Number');
                $coln0++;
            }
           
            $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Breach Type');
            $coln0++;
            if (in_array('StartDate', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Start Date');
                $coln0++;
            }
            if (in_array('EndDate', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'End Date');
                $coln0++;
            }
            if (in_array('BreachedBy', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Breached By');
                $coln0++;
            }
            if (in_array('BreachedDate', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Breached Date');
                $coln0++;
            }
            if (in_array('ApprovedBy', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Approved By');
                $coln0++;
            }
            if (in_array('ApprovedDate', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Approved Date');
                $coln0++;
            }
            if (in_array('Comments', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Comments');
                $coln0++;
            }
            if (in_array('Count', $topRow)) {
                $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", 'Count');
                $coln0++;
            }
        
            /** Body Section Start*/
            $rowNo++;
        }

        if (!empty($WTDSummery_F) && is_array($WTDSummery_F)) {
            foreach ($WTDSummery_F as $Row) {
                $coln0 = 3;
                if (isset($Row['schedulingTeamName'])) { $schedulingTeamName = $Row['schedulingTeamName']; }else{ $schedulingTeamName ='-';}
                if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                    $objPHPExcel->getActiveSheet()->getStyle("A$rowNo")->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '00c7f8')
                            )
                        )
                    );
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle("A$rowNo")->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'ffe6f9')
                            )
                        )
                    );
                }
                $objPHPExcel->getActiveSheet()->setCellValue("A$rowNo", $schedulingTeamName); //First

                if (isset($Row['DisplayName'])) { $DisplayName = $Row['DisplayName']; } else { $DisplayName ='-';}
                if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                    $objPHPExcel->getActiveSheet()->getStyle("B$rowNo")->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '00c7f8')
                            )
                        )
                    );
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle("B$rowNo")->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'ffe6f9')
                            )
                        )
                    );
                }
                $objPHPExcel->getActiveSheet()->setCellValue("B$rowNo", $DisplayName); //II

                if (isset($Row['CostCode'])) { $CostCode = $Row['CostCode']; } else { $CostCode ='-';}

                
                if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("C$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                } else {
                            $objPHPExcel->getActiveSheet()->getStyle("C$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                }
                $objPHPExcel->getActiveSheet()->setCellValue("C$rowNo", $CostCode);
               

                if (in_array('StaffNumber', $topRow)) {
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Row['StaffNumber']);
                    $coln0++;
                }
                if (isset($Row['BreachType'])) { $BreachType = $Row['BreachType']; } else { $BreachType ='-';}
                if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $BreachType);  //4
                    $coln0++;

                if (in_array('StartDate',$topRow)) {
                if (!empty($Row['StartDate'])) { $StartDate = date('d/m/Y',strtotime($Row['StartDate']));} else {
                    $StartDate='--';
                }
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $StartDate);
                    $coln0++;
                }

                if (in_array('EndDate',$topRow)) {
                    if (!empty($Row['EndDate'])) { $EndDate = date('d/m/Y',strtotime($Row['EndDate']));} else {
                        $EndDate='--';
                    }
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $EndDate);
                    $coln0++;
                }

                if (in_array('BreachedBy', $topRow)) {
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Row['BreachedBy']);
                    $coln0++;
                }

                if (in_array('BreachedDate', $topRow)) {
                    if (!empty($Row['BreachedDate'])) { $BreachedDate = date('d/m/Y',strtotime($Row['BreachedDate']));} else {
                        $BreachedDate ='--';
                    }
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo",$BreachedDate);
                    $coln0++;
                }

                if (in_array('ApprovedBy',$topRow)) {
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Row['ApprovedBy']);
                    $coln0++;
                }

                if (in_array('ApprovedDate', $topRow)) {
                if (!empty($Row['ApprovedDate'])) { $approveDate =date('d/m/Y', strtotime($Row['ApprovedDate']));} else {
                    $approveDate='--';
                }
                    
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $approveDate);
                    $coln0++;
                }

                if (in_array('Comments', $topRow)) {
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Row['Comments']);
                    $coln0++;
                }

                if (in_array('Count',$topRow)) {
                    if (isset($Row['IsApproved']) && $Row['IsApproved']==1) {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => '00c7f8')
                                    )
                                )
                            );
                    } else {
                            $objPHPExcel->getActiveSheet()->getStyle("$colName[$coln0]$rowNo")->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'ffe6f9')
                                    )
                                )
                            );
                        }
                    $objPHPExcel->getActiveSheet()->setCellValue("$colName[$coln0]$rowNo", $Row['Count']);
                }
                $rowNo++;
            }
        }
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="WTDSummaryReport.xls"');
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
?>