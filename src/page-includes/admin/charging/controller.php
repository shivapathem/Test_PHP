<?php
include_once '../../../function-includes/genericfunctions.php';
include_once __DIR__ . '/../../../function-includes/user-scheduling-team-list.php';
class ControllerClass
{
    private $dbResource;
    private $intUserID;
    private $usernetlogin;
    private $service;
    function __construct()
    {
        $this->dbResource = OpenDBLinkA7();
        $this->intUserID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
        $this->usernetlogin = isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ? $_SESSION['user']['user'] :$_COOKIE['editWeeklyUserNetLogin'];
        $this->service = new AllocationService();
    }

    private function fetchData($query, $queryParams = array(), $outputType = "ARRAY")
    {
        $data = array();
        $errorContainer = array();
        try {
            $query = $this->dbResource->prepare($query);
            $query->execute($queryParams);
            $errorContainer = $query->errorInfo();
            if ($errorContainer[0] == 0) {
                $data = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($outputType == "JSON") {
                    $data = json_encode($data);
                }
            }
        } catch (Exception $e) {
            $errorContainer[1] = $e;
        }
        return array('data' => $data, 'error' => $errorContainer[1]);
    }

    private function fetchDataSingleResult($query, $queryParams = array(), $outputType = "ARRAY")
    {
        $data = array();
        $errorContainer = array();
        try {
            $query = $this->dbResource->prepare($query);
            $query->execute($queryParams);
            $errorContainer = $query->errorInfo();
            if ($errorContainer[0] == 0) {
                $data = $query->fetch(PDO::FETCH_ASSOC);
                if ($outputType == "JSON") {
                    $data = json_encode($data);
                }
            }
        } catch (Exception $e) {
            $errorContainer[1] = $e;
        }
        return array('data' => $data, 'error' => $errorContainer[1]);
    }

    private function loadView($page, $data = array(), $requestData = array())
    {
        require_once("view/" . $page . ".php");
    }

    public function teamPayIntegration($requestData)
    {
        $teamPayMissingRecord_f['data'] = $teamPayMissingRecord_f['data'] ?? '';
        $getSchedulingTeams_f['data'] = $getSchedulingTeams_f['data'] ?? '';
        $this->loadView('teamPayIntegrationScreen', array($teamPayMissingRecord_f['data'], $getSchedulingTeams_f['data']), $requestData);
    }

    public function teamPayIntegrationSave($requestData)
    {
        $chkIdArr = $requestData['chkId'];
        $chkIdStr = implode(',', $chkIdArr);
        $onHoldComment = $requestData['onHoldComment'];
        $requestType = $requestData['requestType'];
        $requestFrom = $requestData['requestFrom'];
        if ($requestType == 'ONHOLD') {
            $teamPayOnHold_q = "UPDATE StaffConfig_Processed SET COMMENTS='$onHoldComment' WHERE SCP_ID IN($chkIdStr)";
            $this->fetchDataResource($teamPayOnHold_q, array());
        } else if ($requestType == 'APPROVED') {
            if ($requestFrom == 'ICR') {
                $teamPayOnHold_q = "exec [dbo].[usp_Sync_Incorrect_Records] ?, ?";
                $this->fetchDataResource($teamPayOnHold_q, array($chkIdStr, $this->intUserID));
            } else {
                $teamPayOnHold_q = "exec [dbo].[usp_Sync_Missing_Records] ?, ?";
                $this->fetchDataResource($teamPayOnHold_q, array($chkIdStr,$this->intUserID));
            }
        }
		if($requestData['formName'] == 'leaversRecords')
		{
			$this->getLeaversRecord($requestData);
		}else
		{
        $this->getIncorrectRecord($requestData);
    }
    }

    public function getIncorrectRecord($requestData)
    {
        $schedulingTeamId = $requestData['schedulingTeamId'] ?? 0;
        $empNumber = $requestData['empNumber'] ?? 0;
        $estCode = $requestData['estCode'] ?? '';
        $name = $requestData['name'] ?? '';
        $records = $requestData['records'] ?? '';
        $filterType = $requestData['filterType'] ?? '';
		$recordOffset		=	$requestData['recordOffset'] ?? 0;
        $teamPayMissingRecord_q = "exec [dbo].[usp_Get_Incorrect_Records] ?, ?, ?, ?, ?, ?, ?";
        $teamPayMissingRecord_f = $this->fetchData($teamPayMissingRecord_q, array($schedulingTeamId, $empNumber, $estCode, $filterType, $name, $records, $recordOffset));
        $getSchedulingTeams_q = "select schedulingTeamId, schedulingTeamName from schedulingTeams where isActive = 1 and schedulingTeamName not like '%Up to 31st March%' order by schedulingTeamName";
        $getSchedulingTeams_f = $this->fetchData($getSchedulingTeams_q, array());
        $this->loadView('listIncorrectRecord', array($teamPayMissingRecord_f['data'], $getSchedulingTeams_f['data']), $requestData);
    }

    public function getMissingRecord($requestData)
    {
        $schedulingTeamId = $requestData['schedulingTeamId'] ?? 0;
        $empNumber = $requestData['empNumber'] ?? 0;
        $estCode = $requestData['estCode'] ?? 0;
        $name = $requestData['name'] ?? '';
        $records = $requestData['records'] ?? '';
        $filterType = $requestData['filterType'] ?? '';
		$recordOffset		=	$requestData['recordOffset'] ?? 0;
        $teamPayMissingRecord_q = "exec [dbo].[usp_Get_Missing_Records] ?, ?, ?, ?, ?, ?, ?";
        $teamPayMissingRecord_f = $this->fetchData($teamPayMissingRecord_q, array($schedulingTeamId, $empNumber, $estCode, $filterType, $name, $records, $recordOffset));
        //$getSchedulingTeams_q = "select schedulingTeamId, schedulingTeamName from schedulingTeams where isActive = 1";
        //$getSchedulingTeams_f = $this->fetchData($getSchedulingTeams_q, array());
        $this->loadView('listMissingRecord', array($teamPayMissingRecord_f['data'], array()), $requestData);
    }

    public function syncRefTables($requestData)
    {
        $syncRefTables_q = "exec [dbo].[usp_Import_AL_A7_REF_MASTER]";
        $this->fetchData($syncRefTables_q, array());
        echo "success";
    }

    public function listChargeCode($requestData)
    {
        $requestData['codetype'] = 0;
        $chargeCode_q = 'usp_GET_ChargeWbsCodeList ?,?';
        $chargeCode_f = $this->fetchData($chargeCode_q, array($requestData['codetype'], $this->intUserID));
        $this->loadView('listChargeWbsCode', $chargeCode_f['data'], $requestData);
    }

    public function listWbsCode($requestData)
    {
        $requestData['codetype'] = 1;
        $chargeCode_q = 'usp_GET_ChargeWbsCodeList ?,?';
        $chargeCode_f = $this->fetchData($chargeCode_q, array($requestData['codetype'], $this->intUserID));
        $this->loadView('listChargeWbsCode', $chargeCode_f['data'], $requestData);
    }

    public function sendChargingToFinance($requestData)
    {
        include_once '../../../function-includes/common/classCommonDBFunctions.php';
        include_once '../../admin/divisions/process/classDivisionalAdmin.php';

        $commonObj = new classCommonDBFunctions();
        $divisionobj = new ClassDivisionalAdmin;
        $systemAdmin = $commonObj->UserIsSysAdmin($this->intUserID);
        $isDivisionalAdmin = $commonObj->UserIsDivAdmin($this->intUserID);
        if($systemAdmin == false && $isDivisionalAdmin == 0) {
            echo 'Access denied'; die;
        }
        $divisionLists = $divisionobj->getDivisionsListIdByNetUserRole($this->usernetlogin,$isDivisionalAdmin,$systemAdmin,true);

        $queryCond1 = " AND CDML.IsSentToFinance = 0";
        if (!empty($requestData['fromDate']) && !empty($requestData['toDate'])) {
            $queryCond1 = " AND CDML.IsSentToFinance = 0 AND CDML.SentToFinanceDate IS NULL AND (ChargingDutyDate BETWEEN CAST('" . date('Y/m/d', strtotime(str_replace('/', '-', $requestData['fromDate']))) . "' AS DATE) AND CAST('" . date('Y/m/d', strtotime(str_replace('/', '-', $requestData['toDate']))) . "' AS DATE))";
        }
        if (!empty($requestData['existingDate'])) {
            $queryCond1 = " AND CDML.IsSentToFinance = 1 AND CDML.SentToFinanceDate IS NOT NULL AND (convert(date, CDML.SentToFinanceDate, 103) = convert(date, '" . $requestData['existingDate'] . "', 103))";
            $requestData['fromDate'] = $requestData['fromDate1'];
            $requestData['toDate'] = $requestData['toDate1'];
        }

        $queryParams = [];
        foreach((!empty(array_filter($requestData['area_id'] ?? [])) ? $requestData['area_id'] : $divisionLists) as $areaId) {
            $queryParams['param'][] = '?';
            $queryParams['areaId'][] = is_array($areaId) ? $areaId['DivisionID'] : $areaId;
        }
        $areaIdQuery = " AND ST.divisionId IN (" . implode(',', $queryParams['param']) . ")";
        $dutyDateFilter_q = "SELECT DISTINCT CDML.ChargingId,
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
								UD2.UD_DisplayName AS StaffDisplayName,
								UD2.UD_StaffNumber StaffNumber
				FROM            ChargingDutyMapping_Link CDML
				JOIN            EstablishCode EC ON              CDML.EstabCodeId = EC.EstablishCodeId
				JOIN            ACTIVITYCODE AC ON              CDML.ActivityCodeId = AC.ActivityCodeId
				JOIN            CHARGEWBSCODE CWC ON              CDML.ChargeCodeId = CWC.ChargeWbsCodeId
				INNER JOIN      AllocationsScheduledPersons ASP ON              ASP.ASP_AllocationsSPID = CDML.AllocationId
				INNER JOIN		Allocations AL on AL_AllocationsID = ASP_AllocationsID
				LEFT JOIN 		schedulingTeams ST ON ST.schedulingTeamId = ASP.ASP_ChargingTeamID
				LEFT JOIN 		UserDetails UD ON CDML.CreatedBy = UD.UD_UserID
				JOIN 			UserDetails UD2 ON CDML.PersonId = UD2.UD_UserID
				LEFT JOIN       ActivityChargeCodeMapping_Link ACML ON              CDML.EstabCodeId = ACML.EstablishCodeId
								AND             CDML.ActivityCodeId = ACML.ActiveCodeId
								AND             ACML.Year = Year(CDML.ChargingDutyDate)
				WHERE           IsActual = 1 $queryCond1 $areaIdQuery
				ORDER BY        CDML.ChargingDutyDate ASC";
        $dutyDateFilter_f = $this->fetchData($dutyDateFilter_q, $queryParams['areaId']);
        $exixtingFilter_q = "SELECT convert(date, SentToFinanceDate, 110) as SentToFinanceDate FROM ChargingDutyMapping_Link WHERE SentToFinanceDate IS NOT NULL AND IsSentToFinance = 1 GROUP BY convert(date, SentToFinanceDate, 110) order by SentToFinanceDate desc";
        $exixtingFilter_f = $this->fetchData($exixtingFilter_q, array());
        $this->loadView('sendChargingToFinance', array($dutyDateFilter_f['data'], $exixtingFilter_f['data'], $divisionLists), $requestData);
    }

    public function activityCodesConfig()
    {
        $activityCode_q = "Usp_ActivityOperations '', '', '', 'SELECT', ?, '', ''";
        $activityCode_f = $this->fetchData($activityCode_q, array($this->intUserID));
        $this->loadView('activityCodesConfig', $activityCode_f['data']);
    }

    public function listActivityChargeMapping($requestData)
    {
        global $errorContainer;

        include_once '../../../function-includes/common/classCommonDBFunctions.php';
        include_once '../../admin/divisions/process/classDivisionalAdmin.php';

        $commonObj = new classCommonDBFunctions();
        $divisionobj = new ClassDivisionalAdmin;
        $systemAdmin = $commonObj->UserIsSysAdmin($this->intUserID);
        $isDivisionalAdmin = $commonObj->UserIsDivAdmin($this->intUserID);
        $divisionListsIds = $divisionobj->getDivisionsListIdByNetUserRole($this->usernetlogin,$isDivisionalAdmin,$systemAdmin);

        $pageid = 19;
        $permissions = getUserRolePermissions($pageid);
        if ($permissions->canview == 1) {
            $scheTeam_q = "exec [dbo].[usp_SearchSchedulingTeamDetails] ?, 0";
            $scheTeam_f = $this->fetchData($scheTeam_q, array($this->intUserID));
            $divisionIds = array_column($divisionListsIds, 'DivisionID');
            $filteredData = array_filter($scheTeam_f['data'], function($item) use ($divisionIds) {
                return in_array($item['divisionId'], $divisionIds);
            });
            $scheTeam_f['data'] = array_values($filteredData);
            $chargeCodeContainerArr = [];
            if (!empty($requestData['chargeCodeContainer'])) {
                $chargeCodeContainerArr = explode(',', $requestData['chargeCodeContainer']);
                $chargeCodeContainerArr = array_filter($chargeCodeContainerArr);
            } elseif ((count($requestData) == 1) || (isset($requestData['chargeCodeContainer']) && $requestData['activityCodeListContainer'] == "")) {
                foreach ($scheTeam_f['data'] as $dataVal) {
                    if (!empty($dataVal['EstablishCode'])) {
                        $chargeCodeContainerArr[] = $dataVal['EstablishCode'];
                    }
                }
            }
            $currentFinancialYr = (date("m") > 3) ? date("Y") : date("Y") - 1;
            $selectedYearFrom = empty($requestData['mappingFrom']) ? $currentFinancialYr : $requestData['mappingFrom'];
            $selectedYearTo = empty($requestData['mappingTo']) ? $currentFinancialYr : $requestData['mappingTo'];
            $chargeCodeContainerStr = "'" . implode("','", $chargeCodeContainerArr) . "'";
            $getChargeCode_q = "SELECT MappingId, Year, EffectiveFrom, EstablishCode, EstablishCodeDescription, ActivityCodeName, Description, Price, ACCML.EstablishCodeId, ACCML.ActiveCodeId FROM ActivityChargeCodeMapping_Link ACCML (NOLOCK) JOIN ACTIVITYCODE AC (NOLOCK) ON ACCML.ActiveCodeId = AC.ActivityCodeId JOIN EstablishCode EC (NOLOCK) ON ACCML.EstablishCodeId = EC.EstablishCodeId WHERE EC.EstablishCode IN($chargeCodeContainerStr) AND isNull(EC.EstablishCode, '') != '' AND ACCML.Year BETWEEN ? AND ?";
            $getChargeCode_f = $this->fetchData($getChargeCode_q, array($selectedYearFrom, $selectedYearTo));
            $this->loadView('listActivityChargeMapping', array($scheTeam_f['data'], $getChargeCode_f['data']), $requestData);
        } else {
            echo $errorContainer['EPIC5']['016'];
        }
    }

    public function searchChargingCode($requestData)
    {
        $chargeCode_q = "select * from chargewbscode (NOLOCK) where ChargeWbsCodeName like '" . $requestData['searchReceiverCode'] . "%'";
        $chargeCode_f = $this->fetchData($chargeCode_q, array());
        $this->loadView('searchChargingCode', $chargeCode_f['data'], $requestData);
    }

    public function activityCodesConfigSave($requestData)
    {
        $getUserDetails_q = "SELECT UD_TeampayStaffID StaffID,UD_StaffNumber StaffNumber, UD_DisplayName userDisplayName ,UD_UserID UserID 	FROM UserDetails  (nolock) where UD_NetLogin = ltrim(?)";
        $getUserDetails_f = $this->fetchData($getUserDetails_q, array($this->usernetlogin));
        $requestData['userDetails'] = $getUserDetails_f['data'][0];
        $pageid = 19;
        $permissions = getUserRolePermissions($pageid);
        global $errorContainer;
        switch ($requestData['submitType']) {
            case 0 :
                $submitType = 'INSERT';
                $actStatus = $actId = '';
                $actCode = trim($requestData['actCode']);
                $actDesc = trim($requestData['actDesc']);
                $historyStr = 'Created by ' . ucwords($requestData['userDetails']['userDisplayName']) . ' on ' . date('d/m/Y \\a\\t H:i:s.', time()).".\n";
                $messageCode = ($permissions->cancreate == 1) ? '' : '010';
                break;
            case 1 :
                $submitType = 'UPDATE';
                $actStatus = $actCode = '';
                $actId = $requestData['actId'];
                $actDesc = trim($requestData['actDesc']);
                $historyStr = 'Modified by ' . ucwords($requestData['userDetails']['userDisplayName']) . ' on ' . date('d/m/Y \\a\\t H:i:s', time()) . ' - Description changed from ' . "'" . $requestData['actDescOld'] . "'" . ' to ' . "'" . $actDesc . "'" . ".\n";
                $messageCode = ($permissions->canmodify == 1) ? '' : '011';
                break;
            default :
                $submitType = 'UPDATESTATUS';
                $actStatus = ($requestData['actStatus'] == 1) ? 0 : 1;
                $actId = $requestData['actId'];
                $actCode = $actDesc = '';
                $historyStr = (($requestData['actStatus'] == 1) ? 'Deactivated' : 'Activated') . ' by ' . ucwords($_SESSION['user']['FullName']) . ' on ' . date('d/m/Y \\a\\t H:i:s.', time())."\n";
                $messageCode = ($permissions->canmodify == 1) ? '' : '011';
                break;
        }
        if ($messageCode == '') {
            $searchSelect = $requestData['searchSelect'];
            $activityCode_q = "Usp_ActivityOperations ?, ?, ?, ?, ?, ?, ?";
            $activityCode_f = $this->fetchData($activityCode_q, array($actId, $actCode, $actDesc, $submitType, $this->intUserID, $actStatus, $historyStr));
            if ($activityCode_f['data'][0]['Status'] > 0) {
                $lastId = $activityCode_f['data'][0]['LastId'];
                $activityCode_q = "Usp_ActivityOperations '', '', '', 'SELECT', ?, '', ?";
                $activityCode_f = $this->fetchData($activityCode_q, array($this->intUserID, $historyStr));
                $this->loadView('activityCodesConfig', $activityCode_f['data'], array('lastId' => $lastId, 'searchSelect' => $searchSelect));
            } else {
                echo $errorContainer['EPIC5'][$activityCode_f['data'][0]['StatusCode']];
            }
        } else {
            echo $errorContainer['EPIC5'][$messageCode];
        }
    }

    private function fetchDataResource($query, $queryParams = array())
    {
        $errorContainer = array();
        try {
            $query = $this->dbResource->prepare($query);
            $query->execute($queryParams);
        } catch (Exception $e) {
            $errorContainer[1] = $e;
        }
        return array('resource' => $query, 'error' => $errorContainer[1]);
    }

    public function isActiveCode($requestData)
    {
        global $errorContainer;
        //hit the update record
        $chargeCodeMod_q = 'usp_mod_SetActiveChargeWbsCode ?,?';
        $chargeCodeMod_f = $this->fetchData($chargeCodeMod_q, array($requestData['chargeWbsCodeid'], $this->intUserID));
        //get the data by id
        $chargeCodeDetail_q = "SELECT * from ChargeWbsCode where ChargeWbsCodeId = ?";
        $chargeCodeDetail_f = $this->fetchData($chargeCodeDetail_q, array($requestData['chargeWbsCodeid']));
        $chargeCodeMod_f['data'][0]['responseMessage'] = $errorContainer['EPIC5'][$chargeCodeMod_f['data'][0]['StrStatus']];
        $chargeCodeMod_f['data'][0]['actionValue'] = $chargeCodeDetail_f['data'][0]['IsActive'];
        $resppnse_array = json_encode($chargeCodeMod_f['data'][0]);
        echo $resppnse_array;
        exit();
    }

    public function insUpdateChargeWbsCode($requestData)
    {
        global $errorContainer;
        $query = '[dbo].[usp_mod_ChargeWbsCode] ?,?,?,?,?,?';
        if ($requestData['codeType'] == 0) {
            if ($requestData['actionType'] == 'add') {
                $chargeWbsCodeId = rtrim(ltrim($requestData['ChargeWbsCodeId']));
                $chargeWbsCode = rtrim(ltrim($requestData['addChargeCode']));
                $chargeWbsCodeDesc = $requestData['addChargeCodeDesc'];
            } else {
                $chargeWbsCodeId = rtrim(ltrim($requestData['ChargeWbsCodeId']));
                $chargeWbsCode = $requestData['editChargeCode'] ? rtrim(ltrim($requestData['editChargeCode'])) : rtrim(ltrim($requestData['addEditWbsCodeName']));
                $chargeWbsCodeDesc = $requestData['editChargeCodeDesc'];
            }
        } else {
            if ($requestData['actionType'] == 'add') {
                $chargeWbsCodeId = rtrim(ltrim($requestData['ChargeWbsCodeId']));
                $chargeWbsCode = rtrim(ltrim($requestData['addWbsCode']));
                $chargeWbsCodeDesc = $requestData['addWbsCodeDesc'];
            } else {
                $chargeWbsCodeId = rtrim(ltrim($requestData['ChargeWbsCodeId']));
                $chargeWbsCode = $requestData['editWbsCode'] ? rtrim(ltrim($requestData['editWbsCode'])) : rtrim(ltrim($requestData['addEditWbsCodeName']));
                $chargeWbsCodeDesc = $requestData['editWbsCodeDesc'];
            }
        }
        $DivisionId = $requestData['DivisionId'];
        $chargeCodeMod_f = $this->fetchDataSingleResult($query, array($chargeWbsCodeId, $chargeWbsCode, $chargeWbsCodeDesc, $DivisionId, $this->intUserID, $requestData['codeType']));
        if ((int)($chargeCodeMod_f['data']['intStatus']) > 0) {
            if ($requestData['codeType'] == 0 || $requestData['codeType'] == '0') {
                $requestData = array();
                $this->listChargeCode($requestData);
            } else {
                $requestData = array();
                $this->listWbsCode($requestData);
            }
        } else {
            echo $errorContainer['EPIC5'][$chargeCodeMod_f['data']['strStatus']];
        }
    }

    public function getChargeCodeOptions($requestData)
    {
        include_once '../../../function-includes/common/classCommonDBFunctions.php';
        $commonObj = new classCommonDBFunctions();
        $systemAdmin = $commonObj->UserIsSysAdmin($this->intUserID);

        if ($requestData['teamContainer'] != '') {
            $teamContainerArr = explode(',', $requestData['teamContainer']);
            $teamContainerStr = "'" . implode("','", $teamContainerArr) . "'";
            $scheTeam_q = "
	            SELECT EC.EstablishCode, EC.EstablishCodeDescription, EC.EstablishCodeId FROM schedulingTeams ST JOIN EstablishCode EC ON ST.EstablishCodeID = EC.EstablishCodeId WHERE ST.schedulingTeamId IN($teamContainerStr) AND isNull(EC.EstablishCode, '') != '' ORDER BY EC.EstablishCode ASC";
        } else {
            if(($systemAdmin != 0) || ($systemAdmin != '')){
                $scheTeam_q = "
                    SELECT EC.EstablishCode, EC.EstablishCodeDescription, EC.EstablishCodeId FROM schedulingTeams ST JOIN EstablishCode EC ON ST.EstablishCodeID = EC.EstablishCodeId WHERE isNull(EC.EstablishCode, '') != '' ORDER BY EC.EstablishCode ASC";
            }else{
                $scheTeam_q = "
                    SELECT EC.EstablishCode, EC.EstablishCodeDescription, EC.EstablishCodeId
                    FROM schedulingTeams ST
                    INNER JOIN EstablishCode EC ON ST.EstablishCodeID = EC.EstablishCodeId
                    INNER JOIN schedulingTeamDivision_Link sl on sl.schedulingTeamId = st.schedulingTeamId
                    inner join DivisonalAdmin DA on DA.DivisionId = sl.divisionId
                    JOIN REF_Roles rr on rr.RoleID = DA.RoleId
                    WHERE isNull(EC.EstablishCode, '') != '' AND rr.RoleName = 'Area Admin'
                    and DA.UserId = '$this->intUserID'
                    ORDER BY EC.EstablishCode ASC
                ";
            }
        }
        $scheTeam_f = $this->fetchData($scheTeam_q, array());
        $this->loadView('getChargeCodeOptions', $scheTeam_f['data'], $requestData);
    }

    public function addEditMapping($requestData)
    {
        $getChargeCode_q = "SELECT EstablishCodeId, EstablishCode, EstablishCodeDescription FROM EstablishCode ORDER BY EstablishCode";
        $getChargeCode_f = $this->fetchData($getChargeCode_q, array());
        $getActivityCode_q = "SELECT ActivityCodeId, ActivityCodeName, Description FROM ActivityCode WHERE IsActive = 1 ORDER BY ActivityCodeName";
        $getActivityCode_f = $this->fetchData($getActivityCode_q, array());
        $this->loadView('addEditMapping', array($getChargeCode_f['data'], $getActivityCode_f['data']), $requestData);
    }

    public function addEditMappingSave($requestData)
    {
        global $errorContainer;
        $addEditChargeCode = $requestData['addEditChargeCode'];
        $addEditActivityCode = $requestData['addEditActivityCode'];
        $addEditPrice = $requestData['addEditPrice'] ? $requestData['addEditPrice'] : 0;
        $mappingId = $requestData['mappingId'] ? $requestData['mappingId'] : 0;
        $actionType = $requestData['actionType'];
        $addEditYear = $requestData['addEditYear'];
        $getActivityCode_q = "Usp_AddEditActiveChargeCodeMapping ?, ?, ?, ?, ?, ?, ?";
        $getActivityCode_f = $this->fetchData($getActivityCode_q, array($mappingId, $addEditChargeCode, $addEditActivityCode, $addEditYear, $addEditPrice, $actionType, $this->intUserID));
        $getActivityCode_f['data'][0]['StatusCode'] = $errorContainer['EPIC5'][$getActivityCode_f['data'][0]['StatusCode']];
        echo json_encode($getActivityCode_f['data'][0]);
    }

    public function getYearForCopyMapping($requestData)
    {
        $this->loadView('getYearForCopyMapping', array(), $requestData);
    }

    public function copyMapping($requestData)
    {
        $copyYear = $requestData['copyYear'];
        $checkedMappingIdStr = $requestData['checkedMappingIdArr'];
        $checkedMappingIdArr = explode(',', $checkedMappingIdStr);
        $skippedMappingId_q = "
select accm1.MappingId as Mapping1,accm1.Year as Year1,accm2.MappingId,accm2.Price,accm2.Year, EstablishCode, EstablishCodeDescription, ActivityCodeName, Description from ActivityChargeCodeMapping_Link accm1 join ActivityChargeCodeMapping_Link accm2 on accm1.EstablishCodeId = accm2.EstablishCodeId and accm1.ActiveCodeId = accm2.ActiveCodeId and accm2.Year = ? JOIN ACTIVITYCODE AC ON accm1.ActiveCodeId = AC.ActivityCodeId JOIN EstablishCode EC ON accm1.EstablishCodeId = EC.EstablishCodeId where accm1.MappingId in($checkedMappingIdStr)";
        $skippedMappingId_f = $this->fetchData($skippedMappingId_q, array($copyYear));
        $skippedRowHtml = '';
        foreach ($skippedMappingId_f['data'] as $skippedMappingId_v) {
            if (in_array($skippedMappingId_v['Mapping1'], $checkedMappingIdArr)) {
                $pos = array_search($skippedMappingId_v['Mapping1'], $checkedMappingIdArr);
                unset($checkedMappingIdArr[$pos]);
            }
            $skippedRowHtml .= "<tr >
										<td>" . $skippedMappingId_v['EstablishCode'] . '</td>
										<td title="' . $skippedMappingId_v['EstablishCodeDescription'] . '">' . substr($skippedMappingId_v['EstablishCodeDescription'], 0, 20) . "</td>
										<td>" . $skippedMappingId_v['ActivityCodeName'] . '</td>
										<td title="' . $skippedMappingId_v['Description'] . '">' . substr($skippedMappingId_v['Description'], 0, 20) . "</td>
									</tr>";
        }
        $requestData['skippedRowHtml'] = $skippedRowHtml;
        $copyMappingIdStr = implode(',', $checkedMappingIdArr);
        $copyMapping_q = "SELECT MappingId, Year, EffectiveFrom, EstablishCode, EstablishCodeDescription, ActivityCodeName, Description, Price, ACCML.EstablishCodeId, ACCML.ActiveCodeId FROM ActivityChargeCodeMapping_Link ACCML JOIN ACTIVITYCODE AC ON ACCML.ActiveCodeId = AC.ActivityCodeId JOIN EstablishCode EC ON ACCML.EstablishCodeId = EC.EstablishCodeId WHERE ACCML.MappingId IN($copyMappingIdStr)";
        $copyMapping_f = $this->fetchData($copyMapping_q, array());
        $this->loadView('copyMappingView', $copyMapping_f['data'], $requestData);
    }

    public function copyMappingSave($requestData)
    {
        $copyYear = $requestData['copyYear'];
        $errorArr = array();
        $this->dbResource->beginTransaction();
        foreach ($requestData['price'] as $mappingId => $priceArr) {
            $insertMapping_q = "INSERT INTO ActivityChargeCodeMapping_Link(Year, EffectiveFrom, EstablishCodeId, ActiveCodeId, Price, CreatedBy, CreatedDate, UpdateBy, UpdatedDate) (SELECT ?, CAST(? as varchar(6))+'-04-01', EstablishCodeId, ActiveCodeId, ?, ?, GETDATE(), ?, GETDATE() FROM ActivityChargeCodeMapping_Link WHERE MappingId = ?)";
            $insertMapping_f = $this->fetchDataResource($insertMapping_q, array($copyYear, $copyYear, trim($priceArr[0], '£'), $this->intUserID, $this->intUserID, $mappingId));
            if (!empty($insertMapping_f['error'])) {
                $errorArr[] = $insertMapping_f['error'];
            }
        }
        if (count($errorArr) > 0) {
            $this->dbResource->rollback();
        } else {
            $this->dbResource->commit();
        }
    }

    public function popupChargingOpen($requestData)
    {
        global $errorContainer;
        $requestData['allocationsSpId'] = isset($requestData['allocationsSpId']) ? $requestData['allocationsSpId'] : $getAllocationdDetails_f['AllocationsSPID'];
        $getAllocationdDetails_f = GetAllocationsDetailsByAllocationDutyId($requestData['allocationsDutyId'], $requestData['allocationsSpId']);
        $requestData['allocationDetails'] = $getAllocationdDetails_f;
        if ($requestData['allocationDetails']['Duration'] <= 0) {
            echo $errorContainer['EPIC5']['025'];
            die;
        }
        // Fetch the active code mapped with charge code
        $getActiveCodeMapped_q = "usp_GET_ActiveChargeCodeMappingList_Charging ?,?,?";
        $getActiveCodeMapped_f = $this->fetchData($getActiveCodeMapped_q,
            array($getAllocationdDetails_f['EstablishCode'],
                $requestData['allocationDetails']['StartDate'],
                $requestData['teamId']));
        $requestData['activecodemapped'] = $getActiveCodeMapped_f['data'];
        $schTeam = $requestData['allocationDetails']['SchedulingTeamId'];
        // Fetch Charge code
        $getActiveCodeMapped_q = "Select DISTINCT ChargeWbsCodeId,ChargeWbsCodeName,Description from ChargeWbsCode cwc (NOLOCK) inner join schedulingTeams st (nolock) on cwc.DivisionId = st.divisionid where cwc.CodeType = 0 and cwc.IsActive = 1 and st.IsActive = 1 and schedulingTeamId = " . $schTeam . " order by cwc.ChargeWbsCodeName asc";
        $getActiveCodeMapped_f = $this->fetchData($getActiveCodeMapped_q, array($getAllocationdDetails_f['EstablishCode'], 0));
        $requestData['chargeCodeList'] = $getActiveCodeMapped_f['data'];
        // Fetch WBS code
        $getActiveCodeMapped_q = "Select DISTINCT ChargeWbsCodeId,ChargeWbsCodeName,Description from ChargeWbsCode cwc (NOLOCK) inner join schedulingTeams st (nolock) on cwc.DivisionId = st.divisionId where cwc.CodeType = 1 and cwc.IsActive = 1 and st.IsActive = 1 and st.schedulingTeamId = " . $schTeam . " order by cwc.ChargeWbsCodeName asc";
        $getActiveCodeMapped_f = $this->fetchData($getActiveCodeMapped_q, array($getAllocationdDetails_f['EstablishCode'], 0));
        $requestData['WbsCodeList'] = $getActiveCodeMapped_f['data'];
        // Fetch table data Charge Code duty
        $scheTeam_q = "[dbo].[usp_get_ChargeDutyWeeklyAllocation] ?,?,?,?,?,?";
        $scheTeam_f = $this->fetchData($scheTeam_q, array($requestData['allocationDetails']['ScheduledPersonID'],
            $requestData['allocationDetails']['MasterDutyId'],
            $requestData['allocationsSpId'],
            date("d/m/Y", strtotime($requestData['allocationDetails']['StartDate'])),
            $this->intUserID, $requestData['teamId']));
        $requestData['chargingDutyWeeklyAllocation'] = $scheTeam_f['data'];
        $this->loadView('chargingScreen', $requestData);
    }

    public function insUpdateDelChargingDutyMapping($requestData)
    {
        global $errorContainer;
        $getAllocationdDetails_q = "usp_mod_ChargingDutyMapping ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
		$requestData['maintainCharging'] = isset($requestData['maintainCharging']) ? $requestData['maintainCharging'] : '';
		if($requestData['dutyCharging'] == 'Provisional')
		{
			$requestData['dutyCharging'] = 0;
		}elseif($requestData['dutyCharging'] == 'Actual')
		{
			$requestData['dutyCharging'] = 1;
		}elseif($requestData['dutyCharging'] == 'Hold')
		{
			$requestData['dutyCharging'] = 2;
		}
        $requestArray = array(
            $requestData['chargingId'],
            $requestData['chargeCodeschTeamId'],
            $requestData['activityCode'],
            $requestData['masterDutyId'],
            $requestData['allocationsSpId'],
            $requestData['chargeWbsCodeRadio'] == 'wbsCodeRadio' ? $requestData['WbsCode'] : $requestData['chargeCodeMain'],
            $requestData['chargeWbsQuantity'],
            $requestData['activityUnitPrice'],
            $requestData['Comment'],
            $requestData['chargeWbsContact'],
            $requestData['chargeWbsTelephone'],
            $requestData['dutyCharging'],
            $requestData['maintainCharging'] == 'on' ? 1 : 0,
            $requestData['ChargingDate'],
            $requestData['scheduledPersonId'],
            $requestData['staffDetailsId'],
            $this->intUserID,
            $requestData['ActionType'],
            $requestData['teamId']
        );
        $getAllocationdDetails_f = $this->fetchDataSingleResult($getAllocationdDetails_q,
            $requestArray);
        $requestData['allocid'] = $requestData['allocationsSpId'];
        if ($getAllocationdDetails_f['data']['IntStatus'] == 1) {
            $this->popupChargingOpen($requestData);
        } else {
            echo $errorContainer['EPIC5'][$getAllocationdDetails_f['data']['StrStatus']];
        }
    }

    public function sendChargingToFinanceSave($requestData)
    {
        global $errorContainer;
        $chargingContainer = trim($requestData['chargingContainer']);
        if (!empty($chargingContainer)) {
            $update_q = "UPDATE ChargingDutyMapping_Link SET IsSentToFinance = 1, SentToFinanceDate =  GETDATE(), ModifiedBy = '" . $this->intUserID . "', ModifiedDate = GETDATE() WHERE ChargingId in($chargingContainer)";
            $update_r = $this->fetchDataResource($update_q, array());
            if (!empty($update_r['error'])) {
                echo $errorContainer['EPIC5']['028'];
                die;
            } else {
                echo $errorContainer['EPIC5']['001'];
                die;
            }
        } else {
            echo $errorContainer['EPIC5']['029'];
            die;
        }
    }

    public function maintainCharging($requestData)
    {
        global $errorContainer;
        $getAllocationdDetails_q = "[dbo].[usp_Mod_MaintainPrefilledCharge] ?,?,?,?";
        $requestArray = array(
            $this->intUserID,
            $requestData['chargingId'],
            $requestData['maintainCharging'],
            $requestData['SchedulingTeamId']
        );
        $this->fetchDataSingleResult($getAllocationdDetails_q, $requestArray);
    }
	public function weeklyChargingSummary($requestData)
    {
        global $errorContainer;
        $getTeamDetails_f 			= 	getSchedulingTeamListArray('reports-policy', 'viewAdvancedReports');
		$requestData['disableExport'] = 1;
		$userId = $this->intUserID;
		$getWbcChargeCode_q = 'select Distinct ChargeWbsCodeId,ChargeWbsCodeName, CodeType from ChargeWbsCode where IsActive = 1';
        $getWbcChargeCode_f = $this->fetchData($getWbcChargeCode_q, array());
		$exixtingFilter_q = "SELECT convert(date, SentToFinanceDate, 110) as SentToFinanceDate FROM ChargingDutyMapping_Link WHERE SentToFinanceDate IS NOT NULL AND IsSentToFinance = 1 GROUP BY convert(date, SentToFinanceDate, 110) order by SentToFinanceDate desc";
        $exixtingFilter_f = $this->fetchData($exixtingFilter_q, array());
		$requestData['schedulingTeam']	= isset($requestData['schedulingTeam']) ? $requestData['schedulingTeam'] : "-1";
        $requestData['reportType'] = $requestData['reportType'] ?? '';
		if(in_array($requestData['reportType'],array('ALL', 'GRP')))
		{
			$reportType 	= $requestData['reportType'];
			$chargeStatus 	= $requestData['chargeStatus'] ?? '';
			$sapDate	 	= $requestData['sapDate'] ?? '';
			$actualStatus 	= $requestData['actualStatus'] ?? '';
			$weeksRange 	= $requestData['weeksRange'] ?? '';
			$weeksStart 	= $requestData['weeksStart'] ?? '';
			$toYear		 	= $requestData['toYear'] ?? '';
			$weekFinish 	= $requestData['weekFinish'] ?? '';
			$fromYear	 	= $requestData['fromYear'] ?? '';
			$schedulingTeam	= $requestData['schedulingTeam'] ?? '';
			$estabCodes		= $requestData['estabCodes'] ?? '';
			$receiverCode	= $requestData['receiverCode'] ?? '';
			$wbsCodes		= $requestData['wbsCodes'] ?? '';
			$staffNumber	= $requestData['staffNumber'] ?? '';
			$groupBy1		= $requestData['groupBy1'] ?? '';
			$groupBy2		= $requestData['groupBy2'] ?? '';
			$requestData['disableExport'] = 0;
			$staffNumber	= str_replace(' ', '', $staffNumber);
			$staffNumberArr	= explode(',', $staffNumber);
			$staffNumberStr	= implode(",", $staffNumberArr);
			$summaryReport_q= 'exec [dbo].[usp_getWeeklyChargingSummary] ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @userID = ?';
			$summaryReport_f = $this->fetchData($summaryReport_q, array($reportType, $chargeStatus, $sapDate, $actualStatus, $weeksRange, $weeksStart, $toYear, $weekFinish, $fromYear, $schedulingTeam, $estabCodes, $receiverCode, $wbsCodes, $groupBy1, $groupBy2, $staffNumberStr, $userId));
            }
        $summaryReport_f = $summaryReport_f ?? null;
		$this->loadView('weeklyChargingSummary', array($getTeamDetails_f, $getWbcChargeCode_f, $exixtingFilter_f, $summaryReport_f), $requestData);
    }
	public function dutyExtractReport($requestData)
    {
		global $errorContainer;
		$getDataList_f				=	array();
		$defaultTeam				=	auth()->user()->defaultTeamId;
		$requestData['schedulingTeam'] = isset($requestData['schedulingTeam']) ? $requestData['schedulingTeam'] : $defaultTeam;
        $schedulingTeam = isset($requestData['schedulingTeam']) ? $requestData['schedulingTeam'] : $defaultTeam;
		$userId = $this->intUserID;
        $weekFrom = $requestData['weekFrom'] ?? null;
        $weekTo   = $requestData['weekTo']   ?? null;
		if(!empty($requestData['weekTo']) && !empty($requestData['weekFrom']))
		{
			$weekFrom 				= $requestData['weekFrom'] ?? '';
			$weekTo 				= $requestData['weekTo'] ?? '';
			$ExtractDays 			= $requestData['ExtractDays'] ?? '';
			$extractFilter 			= $requestData['extractFilter'] ?? '';
			$includeLeave 			= $requestData['includeLeave'] ?? '';
			$includeJobData 		= $requestData['includeJobData'] ?? '';
			$reportType 			= $requestData['reportType'];
			$extractGroup1 			= $requestData['extractGroup1'];
			$extractGroup2 			= $requestData['extractGroup2'];
			$staffNumber			= $requestData['staffNumber'];
			$staffNumberVal			= $requestData['staffNumberVal'];
			$extractJob				= $requestData['extractJob'];
			$extractJobVal			= $requestData['extractJobVal'];
			$extractProgramme		= $requestData['extractProgramme'];
			$extractProgrammeVal	= $requestData['extractProgrammeVal'];
			$extractContact			= $requestData['extractContact'];
			$extractContactVal		= $requestData['extractContactVal'];
			$extractLocation		= $requestData['extractLocation'];
			$extractLocationVal		= $requestData['extractLocationVal'];
			$extractDuty			= $requestData['extractDuty'];
			$extractDutyVal			= $requestData['extractDutyVal'];
			$extractFilterBy		= $requestData['extractFilterBy'];
			$extractFilterByCond	= $requestData['extractFilterByCond'];
			$extractFilterByCondVal	= $requestData['extractFilterByCondVal'];
			$extractHistoryData		= $requestData['extractHistoryData'];
            $extractJobProgramme	= $requestData['extractJobProgramme'];
			$extractJobProgrammeVal	= $requestData['extractJobProgrammeVal'];
			$sortingCol = '1';
			$sortingType = 'asc';
			$getDataList_q 			= "exec usp_getDutyExtractReportDetails ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?";
	        $getDataList_f 			= $this->fetchData($getDataList_q, array($weekFrom, $weekTo, $ExtractDays, $extractFilter, $includeLeave, $includeJobData, $reportType, $extractGroup1, $extractGroup2, $staffNumber, $staffNumberVal, $extractJob, $extractJobVal, $extractProgramme, $extractProgrammeVal, $extractContact, $extractContactVal, $extractLocation, $extractLocationVal, $extractDuty, $extractDutyVal, $extractFilterBy, $extractFilterByCond, $extractFilterByCondVal, $extractHistoryData, $schedulingTeam, $extractJobProgramme, $extractJobProgrammeVal,$sortingCol,$sortingType,$userId));
            if (!empty($getDataList_f['data'])) {
                $newResultSet['data'] = [];
                foreach ($getDataList_f['data'] as $dataValues) {
                    $key = $dataValues['DutyDate'] . '|' . $dataValues['WeekNumber'] . '|' . $dataValues['iDay'] . '|' . $dataValues['DutyName'] . '|' . $dataValues['StaffNumber'];
                    $jobEntry = [];
                    if (!empty($dataValues['JobName'])) {
                        $jobStartTime = $dataValues['JobStartTime'];
                        $jobEndTime = $dataValues['JobEndTime'];
                        $jobDuration = ($jobEndTime - $jobStartTime);
                        if ($jobStartTime > $jobEndTime) {
                            $jobDuration = ((86400 + $jobEndTime) - $jobStartTime);
                        }
                        $jobEntry = [
                            'JobStartTime' => $this->service->convertSecondsIntoTime($jobStartTime, ':', 'No'),
                            'JobEndTime' => $this->service->convertSecondsIntoTime($jobEndTime, ':', 'No'),
                            'JobDuration' => $this->service->convertSecondsIntoTime($jobDuration, ':', 'No'),
                            'JobName' => $dataValues['JobName'],
                            'JobLabel' => $dataValues['JobLabel'],
                            'JobLocation' => $dataValues['Location'],
                            'JobContact' => $dataValues['Contact'],
                        ];
                    }
                    if (!isset($newResultSet['data'][$key])) {
                        $newResultSet['data'][$key] = $dataValues;
                        $newResultSet['data'][$key]['jobdata'] = [];
                        $newResultSet['data'][$key]['DisplayAllocName'] = !empty($dataValues['DisplayAllocName']) ? [$dataValues['DisplayAllocName']] : [];
                        if (!empty($jobEntry)) {
                            $newResultSet['data'][$key]['jobdata'][] = $jobEntry;
                        }
                        unset($newResultSet['data'][$key]['JobName']);
                        unset($newResultSet['data'][$key]['JobStartTime']);
                        unset($newResultSet['data'][$key]['JobEndTime']);
                        unset($newResultSet['data'][$key]['JobLabel']);
                        unset($newResultSet['data'][$key]['Location']);
                        unset($newResultSet['data'][$key]['Contact']);
                    } else {
                        if (!empty($jobEntry)) {
                            $isDuplicate = false;
                            foreach ($newResultSet['data'][$key]['jobdata'] as $existingJob) {
                                if (
                                    ($existingJob['JobName'] === $jobEntry['JobName']) &&
                                    ($existingJob['JobStartTime'] === $jobEntry['JobStartTime']) &&
                                    ($existingJob['JobEndTime'] === $jobEntry['JobEndTime'])
                                ) {
                                    $isDuplicate = true;
                                    break;
                                }
                            }
                            if (!$isDuplicate) {
                                $newResultSet['data'][$key]['jobdata'][] = $jobEntry;
                            }
                        }
                        if (!empty($dataValues['DisplayAllocName']) && !in_array($dataValues['DisplayAllocName'], $newResultSet['data'][$key]['DisplayAllocName'])) {
                            $newResultSet['data'][$key]['DisplayAllocName'][] = $dataValues['DisplayAllocName'];
                        }
                    }
                }
                foreach ($newResultSet['data'] as &$entry) {
                    $entry['DisplayAllocName'] = implode(', ', $entry['DisplayAllocName']);
                }
                $newResultSet['data'] = array_values($newResultSet['data']);
            }
		}
		$getFilterList_q = "select * from AutoPagesFilters where isPublic = 1 AND SchedulingTeamId = ?";
        $getFilterList_f = $this->fetchData($getFilterList_q, array($schedulingTeam));
		$getWeekExistCount_q = "select al_WeekNumber as WeekNumber from allocations where al_SchedulingTeamID = ? AND al_WeekNumber between ? AND ?";
        $getWeekExistCount_f = $this->fetchData($getWeekExistCount_q, array($schedulingTeam, $weekFrom, $weekTo));
		$getWeekExistArr 	=	array();
		$getWeekExistArrTemp=	array();
		foreach($getWeekExistCount_f['data'] as $getWeekExistCount_d)
		{
			$getWeekExistArrTemp[] = $getWeekExistCount_d['WeekNumber'];
		}
		for($i = $weekFrom; $i <= $weekTo; $i++)
		{
			if(!in_array($i, $getWeekExistArrTemp))
			{
				$getWeekExistArr[] = $i;
			}
		}
		$weekMsgStr = '+';
		for($j = 0; $j < count($getWeekExistArr); $j++)
		{
			if($weekMsgStr[strlen($weekMsgStr) - 1]  == '+')
			{
				$weekMsgStr = $weekMsgStr . ' ' . $getWeekExistArr[$j];
			} elseif (!isset($getWeekExistArr[$j + 1]) || $getWeekExistArr[$j] + 1 != $getWeekExistArr[$j + 1])
			{
				$weekMsgStr = $weekMsgStr . ' - ' . $getWeekExistArr[$j] . ' <br/> +';
			}
		}
		$weekMsgStr = rtrim($weekMsgStr, '<br/> ');
		$weekMsgStr = rtrim($weekMsgStr, '+');
		if((trim($weekMsgStr) != '') && ($schedulingTeam != -1) )
		{
			echo "<script> customAlert('Note that some Weeks in your selection have not been created yet:<br/>".$weekMsgStr."'); </script>";
		}
        $getFilterList_f['data'] = $getFilterList_f['data'] ?? null;
        $newResultSet['data'] = $newResultSet['data'] ?? null;
        $getTeamDetails_f['data'] = getSchedulingTeamList(0, 'reports-policy', 'viewAdvancedReports');
		$this->loadView('dutyExtractReport', array($getFilterList_f['data'], $newResultSet['data'], $getTeamDetails_f['data']), $requestData);
	}
	public function staffExtractReport($requestData)
    {
		global $errorContainer;
		$getPayTypSortCOde_q 		= 	"select distinct PaymentTypeShortCode from REF_PaymentType where IsActive = 1";
		$getPayTypSortCOde_f		= 	$this->fetchData($getPayTypSortCOde_q, array());
		$userId = $this->intUserID;
		$teamDetails            = 	getSchedulingTeamListArray('reports-policy', 'viewAdvancedReports');  
		$requestData['schedulingTeam'] = isset($requestData['schedulingTeam']) ? $requestData['schedulingTeam'] : '';
		$requestData['estabCodes'] 	=	isset($requestData['estabCodes']) ? $requestData['estabCodes'] : '';
		$requestData['schedulingTeam'] = is_array($requestData['schedulingTeam']) ? implode('|', $requestData['schedulingTeam']) : "-1";
		$requestData['estabCodes'] = is_array($requestData['estabCodes']) ? implode('|', $requestData['estabCodes']) : $requestData['estabCodes'];
		$schedulingTeam				= $requestData['schedulingTeam'] ? $requestData['schedulingTeam'] : $defaultTeamArr['schedulingTeamId'];
		$effectINForm				= (!empty($requestData['effectINForm'])) ? $requestData['effectINForm'] : date('d/m/Y');
		$requestData['eft']			= isset($requestData['eft']) ? $requestData['eft'] : '';
		$requestData['edp'] 		= isset($requestData['edp']) ? $requestData['edp'] : '';
		$requestData['fsv'] 		= isset($requestData['fsv']) ? $requestData['fsv'] : '';
		$requestData['reportType'] 	= isset($requestData['reportType']) ? $requestData['reportType'] : '';
		$requestData['estabCodes'] 	= isset($requestData['estabCodes']) ? $requestData['estabCodes'] : '';
		$requestData['staffNumber'] = isset($requestData['staffNumber']) ? $requestData['staffNumber'] : '';
		$requestData['groupBy1'] 	= isset($requestData['groupBy1']) ? $requestData['groupBy1'] : '';
		$requestData['groupBy2'] 	= isset($requestData['groupBy2']) ? $requestData['groupBy2'] : '';
		$eft		 				= $requestData['eft'];
		$edp			 			= $requestData['edp'];
		$fsv			 			= $requestData['fsv'];
		$reportType		 			= $requestData['reportType'];
		$estabCodes			 		= $requestData['estabCodes'];
		$staffNumber	 			= $requestData['staffNumber'];
		$groupBy1		 			= $requestData['groupBy1'];
		$groupBy2		 			= $requestData['groupBy2'];
        $sortingCol                 = 1;
        $sortingType                = 'asc';
		$staffNumber				= str_replace(' ', '', $staffNumber);
		$staffNumberArr				= explode(',', $staffNumber);
		$staffNumberStr				= implode("|", $staffNumberArr);
		if(isset($requestData['flormFlag']))
		{
			$getStaffList_q = "EXEC usp_getStaffExtractReportDetails ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
			$getStaffList_f = $this->fetchData($getStaffList_q, array($schedulingTeam, $effectINForm, $eft, $edp, $fsv, $reportType, $estabCodes, $staffNumberStr, $groupBy1, $groupBy2, $sortingCol, $sortingType, $userId));
        }else
		{
			$getStaffList_f = array();
		}

		if($schedulingTeam == "-1")
		{
			$getCodeCode_q = "SELECT DISTINCT SC.UC_CostCode CostCode
								FROM UserDetails ud
								INNER JOIN ScheduledPersonTeam_LINK SPTL ON SPTL.ScheduledPersonID = ud.UD_UserID
								INNER JOIN UserConfigs SC ON SC.UC_UserID = ud.UD_UserID
								WHERE SPTL.StartDate < SC.UC_EndDate AND isnull(SPTL.EndDate,SC.UC_StartDate) >= SC.UC_StartDate order by CostCode";
		}else
		{
			$getCodeCode_q = "SELECT DISTINCT SC.UC_CostCode CostCode
								FROM UserDetails ud
								INNER JOIN ScheduledPersonTeam_LINK SPTL ON SPTL.ScheduledPersonID = ud.UD_UserID
								INNER JOIN UserConfigs SC ON SC.UC_UserID = ud.UD_UserID
								WHERE SPTL.StartDate < SC.UC_EndDate AND isnull(SPTL.EndDate,SC.UC_StartDate) >= SC.UC_StartDate
									AND SPTL.TeamID in(".str_replace('|', ',', $schedulingTeam).") order by CostCode";
		}
        $getCodeCode_f = $this->fetchData($getCodeCode_q, array());
        $this->loadView('staffExtractReport', array(
            $getStaffList_f['data'] ?? [],
            $teamDetails,
            $getCodeCode_f['data'] ?? [],
            $getPayTypSortCOde_f['data'] ?? []
        ), $requestData);
	}

    /*
        Description : LEave Summary Report

    */

    public function showLeaveSummary($pageData)
    {
        include_once '../../../function-includes/leavefunctions.php';
        include_once '../../../function-includes/adminfunctions.php';
        include_once '../../../function-includes/genericfunctions.php';
        include_once '../../../function-includes/leave-admin-functions.php';
        global $errorContainer;
        /** Default Values Start*/
        $availableColumn=[];
        $appliedFilter = [];
        $LeaveTypes = 'single';
        $reportType = 'ALL';
		$pageData['disableExport'] = 1; //Deault Setting
        $selectedLeaveYEarFrom = $selectedleaveYEarTo = GetCurrentLeaveYearGeneric(); //Default Leave Year
        $pageData['currentLeaveYear'] = $selectedLeaveYEarFrom;
        $strUser = $this->usernetlogin;
        $strUserId = $this->intUserID;
        $intSysAdmin = GetIsSysAdmin($strUser);

        $appliedFilter['selectedLeaveTypes']=$LeaveTypes;
        $appliedFilter['selectedreportType']=$reportType;
        $appliedFilter['selectedLeaveYEarFrom'] = $selectedLeaveYEarFrom;
        $appliedFilter['selectedleaveYEarTo'] = $selectedleaveYEarTo;
        $appliedFilter['selectedgroupBy1']=null;
        $appliedFilter['selectedgroupBy2']=null;
        $appliedFilter['selectedstaffNumbers']=null;
        $appliedFilter['selectedfilterColName']=null;
        $appliedFilter['selectedfilterSign']=null;
        $appliedFilter['selectedfiltervalue']=null;
        /* SP Parameters */
        $teamforChargeCode = null;
        $selectedDBColumns =null;
        $selectedgroupBy1 =null;
        $selectedgroupBy2 =null;
        $selectedchargeCodes = null;
        $selectedstaffnumbers =null;
        $selectedfilterSign =null;
        $selectedfiltervalue =null;
        $selectedfilterColName =null;
        /** Default Values End  */
        $defautTeam = auth()->user()->defaultTeamId;
        $teamData = getSchedulingTeamListArray('reports-policy', 'viewAdvancedReports');
        $teamID = [];
        if (!empty($teamData)) {
            foreach ($teamData as $team) {	
                $teamID[$team['id']] = trim($team['name']);
            }
		}
        $teamids = array_keys($teamID);
        $teamforChargeCode = implode(",", $teamids);
        $appliedFilter['selectedTeamID'] = $defautTeam ?? 0; //Selected Teams By default load 1
		$userId = $this->intUserID;
        $scheTeam_q = "exec [dbo].[usp_SearchSchedulingTeamDetails] ?, 0";
        $scheTeam_f = $this->fetchData($scheTeam_q, array($userId));
		$scheTeamArray = [];

        if (!empty($teamData)) {
            foreach ($teamData as $key =>$values) {
				$scheTeamArray[$values['id']] = trim($values['name']);
            }
        }
        $pageData['teamList'] = $scheTeamArray; //All Team List on Page

        $availableColumn = GetLeaveAllocateTypes(); //All Default Category Coloumns
        $Leavecats = [];
        $LeavecatsDBNAME = [];

        if (!empty($availableColumn) && isset($availableColumn)) {
            foreach ($availableColumn as $key => $values) {
                $Leavecats[$values['ID'].'_'.$values['AllocName']]  = trim($values['Description']);
            }
        }
        $appliedFilter['selectedavailableColumn'] = $Leavecats; //By DEault All
        $pageData['availableColumn'] = $Leavecats; //All columns List on Page
        $Filtercollist = [];

        /* Filter applied Start*/
        if (isset($_POST) && !empty($_POST)) {
            if (isset($_POST['selectedschedulingTeam']) && !empty($_POST['selectedschedulingTeam'])) {
               $appliedFilter['selectedTeamID'] = explode(",", $_POST['selectedschedulingTeam']);
            }

            if (isset($_POST['selectedyearType']) && ($_POST['selectedyearType']!='')) {
                $LeaveTypes = trim($_POST['selectedyearType']);
                $appliedFilter['selectedLeaveTypes'] = trim($_POST['selectedyearType']);
            }
            if (isset($_POST['selectedreportType']) && ($_POST['selectedreportType']!='')) {
               $appliedFilter['selectedreportType'] = trim($_POST['selectedreportType']);
            }

            if ($LeaveTypes=='single') {
                if (isset($_POST['selectedleaveFromYear']) && !empty($_POST['selectedleaveFromYear'])) {
                    $appliedFilter['selectedLeaveYEarFrom'] = $appliedFilter['selectedleaveYEarTo'] = trim($_POST['selectedleaveFromYear']);
                }
            } elseif ($LeaveTypes=='range') {
                if ($_POST['selectedleaveTo'] < $_POST['selectedleaveFromYear']) {
                    $_POST['selectedleaveTo']=$_POST['selectedleaveFromYear'];
                }
                $appliedFilter['selectedleaveYEarTo'] = trim($_POST['selectedleaveTo']);
                $appliedFilter['selectedLeaveYEarFrom'] = trim($_POST['selectedleaveFromYear']);
            }

            if (isset($_POST['selectedreportType']) && ($_POST['selectedreportType'] != 'ALL')) {
                if (isset($_POST['selectedgroupBy1']) && ($_POST['selectedgroupBy1'] !='Nothing')) {
                    $appliedFilter['selectedgroupBy1'] = trim($_POST['selectedgroupBy1']);
                }
                if (isset($_POST['selectedgroupBy2']) && ($_POST['selectedgroupBy2'] !='Nothing')) {
                    $appliedFilter['selectedgroupBy2'] = trim($_POST['selectedgroupBy2']);
                }
            }

            if (isset($_POST['selectedstaffNumbers']) && ($_POST['selectedstaffNumbers'] !='')) {
                if (strpos($_POST['selectedstaffNumbers'], ",") !== false) {
                    $appliedFilter['selectedstaffNumbers'] = explode(",", $_POST['selectedstaffNumbers']);
                } else {
                    $appliedFilter['selectedstaffNumbers'] = trim($_POST['selectedstaffNumbers']);
                }
            }
            if (isset($_POST['selectedavailableColumn']) && ($_POST['selectedavailableColumn'] !='')) {
                if (strpos($_POST['selectedavailableColumn'], "ALL,") !== false) {
                    $_POST['selectedavailableColumn'] = str_replace('ALL,', '', $_POST['selectedavailableColumn']);
                }
                if (strpos($_POST['selectedavailableColumn'], ",") !== false) {
                    $appliedFilter['selectedavailableColumn'] = explode(",", $_POST['selectedavailableColumn']);
                } else {
                    $appliedFilter['selectedavailableColumn'] = trim($_POST['selectedavailableColumn']);
                }
                if (is_array($appliedFilter['selectedavailableColumn'])) {
                    foreach ($appliedFilter['selectedavailableColumn'] as $selectedcol) {
                        $keyvalueArray = explode("^", $selectedcol);
                        $appliedFilterList[$keyvalueArray[0]] =  $keyvalueArray[1];
                    }
                } else {
                    $keyvalueArray = explode("^", $appliedFilter['selectedavailableColumn']);
                    $appliedFilterList[$keyvalueArray[0]] =  $keyvalueArray[1];
                }
                $appliedFilter['selectedavailableColumn'] = $appliedFilterList;
            }

            $Leavecats = $appliedFilter['selectedavailableColumn']; //Table Column update when filter applied

            if (isset($_POST['selectedFilterColumn']) && ($_POST['selectedFilterColumn'] !='NA')) {
                $appliedFilter['selectedFilterColumn'] =  trim($_POST['selectedFilterColumn']);
                $appliedFilter['selectedfilterSign']    = trim($_POST['selectedfilterSign']);
                $appliedFilter['selectedfiltervalue']   = trim($_POST['selectedfiltervalue']);
            }

        }   //POST END

        if (is_array($appliedFilter['selectedTeamID']) && !empty($appliedFilter['selectedTeamID'])) {
            $reskey = array_search('ALL', $appliedFilter['selectedTeamID']);
            if ($reskey!==false) {
                unset($appliedFilter['selectedTeamID'][$reskey]);
            }
            $teamforChargeCode = implode(',', $appliedFilter['selectedTeamID']);
        } else {
            if ($appliedFilter['selectedTeamID']=='ALL') {
                $teamforChargeCode = implode(',', $teams);
            } else {
             $teamforChargeCode = trim($appliedFilter['selectedTeamID']);
            }
        }
       //charge code
        $chargeCodes = $this->getChargeCodeByStaffID($teamforChargeCode);
        $chargeCodeslist = [];
        if (!empty($chargeCodes)) {
            foreach ($chargeCodes as $row) {
                    if (!empty($row['UC_CostCode'])) {
                            $chargeCodeslist[] = $row['UC_CostCode'];
                        }
                }
        }
        $pageData['allchargeCodes'] = $chargeCodeslist;
        $appliedFilter['selectedchargeCodes'] = null; //No Filter in First load

        if (isset($_POST['selectedchargeCodes']) && !empty($_POST['selectedchargeCodes'])) {
            $appliedFilter['selectedchargeCodes'] = explode(",", $_POST['selectedchargeCodes']);
        }

        if (is_array($appliedFilter['selectedchargeCodes']) && !empty($appliedFilter['selectedchargeCodes'])) {
            $reskey =array_search('ALL', $appliedFilter['selectedchargeCodes']);
            if ($reskey !== false) {
                unset($appliedFilter['selectedchargeCodes'][$reskey]);
            }
            $selectedchargeCodes = implode(',', $appliedFilter['selectedchargeCodes']);
        } else {
            if ($appliedFilter['selectedchargeCodes']=='ALL' || empty($appliedFilter['selectedchargeCodes'])) {
                $selectedchargeCodes = null;
            } else {
             $selectedchargeCodes = trim($appliedFilter['selectedchargeCodes']);
            }
        }

        if (!empty($Leavecats) && is_array($Leavecats)) {
             foreach ($Leavecats as $key => $value) {
                $keyvalueArray = explode("_", $key);
                 $Filtercollist[] = ucwords($keyvalueArray[1]);
                 $DFiltercollist[ucwords($keyvalueArray[1])] = ucwords($value).' Credit';
                 $DFiltercollist[ucwords($keyvalueArray[1]).'Taken'] = ucwords($value).' Taken';
                 $DFiltercollist[ucwords($keyvalueArray[1]).'Remaining']= ucwords($value).' Remain';
            }
            $DFiltercollist['TotalLeaveRemaining'] = 'Total Remain';
        }
        $pageData['filtercolumnlist'] = $DFiltercollist; //ALL Filter Columns for PAge
        $DBColumns = $Filtercollist;
        $sortingCol  = null;
        $sortingType = null;
        $VDBColumns =null;
        if (($appliedFilter['selectedgroupBy1'] == null) && ($appliedFilter['selectedgroupBy2'] ==null)) {
            $VDBColumns[]='ScheduledPersonID';
            $VDBColumns[]='StaffNumber';
            $VDBColumns[]='DisplayName';
            $VDBColumns[]='iyear';
            $VDBColumns[]='schedulingTeamName';
            $VDBColumns[]='Charge_Codes';
            $sortingCol  = 'DisplayName';
            $sortingType = 'ASC';
        }
        $appliedFilter['selectedDBCols'] = $DBColumns;
        $appliedFilter['selectedVDBCols'] = $VDBColumns;

        //SETTING FINAL PARAMETERT FOR SP
        if (isset($appliedFilter['selectedgroupBy1'])) {
            $selectedgroupBy1=$appliedFilter['selectedgroupBy1'];
        }
        if (isset($appliedFilter['selectedgroupBy2'])) {
            $selectedgroupBy2=$appliedFilter['selectedgroupBy2'];
        }

        if (is_array($appliedFilter['selectedstaffNumbers'])) {
         $selectedstaffnumbers = implode(",", $appliedFilter['selectedstaffNumbers']);
        } else {
            $selectedstaffnumbers = $appliedFilter['selectedstaffNumbers'];
        }

        if (is_array($appliedFilter['selectedDBCols'])) {
            $selectedDBColumns = implode(",", $appliedFilter['selectedDBCols']);
        } else {
            $selectedDBColumns = $appliedFilter['selectedDBCols'];
        }

        if (is_array($appliedFilter['selectedVDBCols'])) {
            $selectedVDBColumns = implode(",", $appliedFilter['selectedVDBCols']);
        } else {
            $selectedVDBColumns = $appliedFilter['selectedVDBCols'];
        }

        if (isset($appliedFilter['selectedFilterColumn'])) {
            $selectedfilterColName = $appliedFilter['selectedFilterColumn'];
        }
        if (isset($appliedFilter['selectedfilterSign'])) {
            $selectedfilterSign = $appliedFilter['selectedfilterSign'];
        }
        if (isset($appliedFilter['selectedfiltervalue'])) {
            $selectedfiltervalue = $appliedFilter['selectedfiltervalue'];
        }
        if (isset($appliedFilter['selectedLeaveYEarFrom']) && !empty($appliedFilter['selectedLeaveYEarFrom'])) {
            $selectedLeaveYEarFrom = $appliedFilter['selectedLeaveYEarFrom'];
        }
        if (isset($appliedFilter['selectedleaveYEarTo']) && !empty($appliedFilter['selectedleaveYEarTo'])) {
            $selectedleaveYEarTo = $appliedFilter['selectedleaveYEarTo'];
        }

        $LeaveSummery_Q = "exec [dbo].[usp_get_LeaveReportSummary] :TeamsID,:VDBColumns,:ColumnsNames, :grp1, :grp2, :LeaveYEarFrom, :leaveYEarTo, :chargeCodes,:staffNumbers,:filterColName,:filterSign,:filtervalue,:sortingCol,:sortingType";
        try {
            $stmt = $this->dbResource->prepare($LeaveSummery_Q);
            // The parameters
            $stmt->bindParam(':TeamsID', $teamforChargeCode, PDO::PARAM_STR);
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
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
        if (!empty($LeaveSummery_F)) {
            $pageData['disableExport'] = 0;
        }
        $pageData['LeaveSummery_F'] = $LeaveSummery_F;
        $pageData['filterrequest'] = $appliedFilter;
		$this->loadView('leaveSummary', array($LeaveSummery_F), $pageData);
    }


    /**
     * Description : This function search for Chnarcode By Team ID
     * @param :$TeamID strings 4,5
     * return array;
     */

    public function getChargeCodeByTeam($TeamID)
    {
        $LeaveSummery_Q = "SELECT Distinct (EstablishCode.EstablishCode) FROM schedulingTeams (NOLOCK) INNER JOIN EstablishCode (NOLOCK) ON schedulingTeams.establishCodeID=EstablishCode.EstablishCodeId where schedulingTeams.schedulingTeamId IN ($TeamID)";
        $stmt = $this->dbResource->prepare($LeaveSummery_Q);
        // The parameters
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**Description : WTD REPORT START*/
    public function WTDSummary($pageData)
    {

        include_once '../../../function-includes/leavefunctions.php';
        include_once '../../../function-includes/adminfunctions.php';
        include_once '../../../function-includes/genericfunctions.php';
        include_once '../../../function-includes/leave-admin-functions.php';
        global $errorContainer;
        /** Default Values Start*/
        $appliedFilter = $teamID = $chargeCodeslist=[];
        $WTDbreechType =-1;
        $approvalStatus=-1;
        $reportType  = 'ALL';
        $sortingCol  = 'schedulingTeamName';
        $sortingType = 'asc';
		$pageData['disableExport'] = 1; //Default Setting
        $selectedWTDWeekFrom = $selectedWTDWeekTo = date('W');
        if ($selectedWTDWeekFrom < 10) {
            $selectedWTDWeekFrom ='0'.$selectedWTDWeekFrom;
        }
        if ($selectedWTDWeekTo < 10) {
            $selectedWTDWeekTo ='0'.$selectedWTDWeekTo;
        }
        $appliedFilter['selectedWTDWeekFrom'] = $selectedWTDWeekFrom;
        $appliedFilter['selectedWTDWeekTo'] = $selectedWTDWeekTo;
        $pageData['currentWTDYear'] = $selectedWTDYearFrom = $selectedWTDYearTo = GetCurrentLeaveYearGeneric(); //Default Leave Year
        $strUser = $this->usernetlogin;
        $strUserId = $this->intUserID;
        $intSysAdmin = GetIsSysAdmin($strUser);
        $appliedFilter['selectedreportType'] = $reportType;
        $appliedFilter['selectedWTDYearFrom'] = $selectedWTDYearFrom;
        $appliedFilter['selectedWTDYearTo'] = $selectedWTDYearTo;
        $appliedFilter['selectedgroupBy1'] = $appliedFilter['selectedgroupBy2']= $appliedFilter['selectedstaffNumbers'] = null;
        $appliedFilter['WTDbreechType']=-1;
        $appliedFilter['approvalStatus']=-1;

        /* SP Parameters */
        $selectedschedulingTeamIds = $selectedgroupBy1 = $selectedgroupBy2 = $selectedchargeCodes = $selectedstaffnumbers = null;
        /** Default Values End  */

		$userId = $this->intUserID;
        $teamData = getSchedulingTeamListArray('reports-policy', 'viewAdvancedReports');
		$scheTeamArray = [];
        if (!empty($teamData)) {
            foreach ($teamData as  $values) {
				$scheTeamArray[$values['id']] = trim($values['name']);
            }
        }
        $defautTeam = auth()->user()->defaultTeamId;
        $teamData2 = getSchedulingTeamListArray('allocation-policy', 'viewEditWeekly');
        if (!empty($teamData2)) {
            foreach ($teamData2 as $rowData) {                
                $teamID[$rowData['id']] = trim($rowData['name']);
            }
        }
        $teamids = array_keys(array_intersect_assoc($teamID, $scheTeamArray));
        $teamforChargeCode = implode(",", $teamids);
        $appliedFilter['selectedTeamID'] = $defautTeam ?? 0; //Selected Teams By default load 1
		$pageData['teamList'] = $scheTeamArray; //All Team List on Page
        $appliedFilter['selectedTeamID'] = !empty($scheTeamArray) ? array_key_first($scheTeamArray) : 0; //Selected Teams By default load 1
        $appliedFilter['getWTDType'] = 'ALL';
        $pageData['getWTDTypes'] = $this->getWTDTypes();

        /* Filter applied Start*/
        if (isset($_POST) && !empty($_POST)) {
            if (isset($_POST['selectedschedulingTeam']) && !empty($_POST['selectedschedulingTeam'])) {
                $appliedFilter['selectedTeamID'] = explode(",", $_POST['selectedschedulingTeam']);
            }

            if (isset($_POST['selectedreportType']) && ($_POST['selectedreportType']!='')) {
               $appliedFilter['selectedreportType'] = trim($_POST['selectedreportType']);
            }

            if (isset($_POST['WTDbreechType']) && ($_POST['WTDbreechType']!='')) {
                if ($_POST['WTDbreechType']=='ALL') {
                    $appliedFilter['WTDbreechType']=-1;
                } else {
                    $appliedFilter['WTDbreechType'] = trim($_POST['WTDbreechType']);
                }
             }

             if (isset($_POST['approvalStatus']) && ($_POST['approvalStatus']!='')) {
                if ($_POST['approvalStatus']=='ALL') {
                    $appliedFilter['approvalStatus']=-1;
                } else {
                    $appliedFilter['approvalStatus'] = trim($_POST['approvalStatus']);
                }
             }

            if (isset($_POST['selectedWTDYearTo']) && isset($_POST['selectedWTDYearFrom'])) {
                if ($_POST['selectedWTDYearTo'] < $_POST['selectedWTDYearFrom']) {
                    $_POST['selectedWTDYearTo'] = $_POST['selectedWTDYearFrom'];
                }
                $appliedFilter['selectedWTDYearTo'] = trim($_POST['selectedWTDYearTo']);
                $appliedFilter['selectedWTDYearFrom'] = trim($_POST['selectedWTDYearFrom']);
            }

            if (isset($_POST['selectedWTDWeekTo']) && isset($_POST['selectedWTDWeekFrom'])) {
                $appliedFilter['selectedWTDWeekTo'] = trim($_POST['selectedWTDWeekTo']);
                $appliedFilter['selectedWTDWeekFrom'] = trim($_POST['selectedWTDWeekFrom']);
            }

            if (isset($_POST['selectedreportType']) && ($_POST['selectedreportType'] != 'ALL')) {
                if (isset($_POST['selectedgroupBy1']) && ($_POST['selectedgroupBy1'] !='Nothing')) {
                    $appliedFilter['selectedgroupBy1'] = trim($_POST['selectedgroupBy1']);
                }
                if (isset($_POST['selectedgroupBy2']) && ($_POST['selectedgroupBy2'] !='Nothing')) {
                    $appliedFilter['selectedgroupBy2'] = trim($_POST['selectedgroupBy2']);
                }
            }

            if (isset($_POST['selectedstaffNumbers']) && ($_POST['selectedstaffNumbers'] !='')) {
                if (strpos($_POST['selectedstaffNumbers'] ,  ",") !== false) {
                    $appliedFilter['selectedstaffNumbers'] = explode(",", $_POST['selectedstaffNumbers']);
                } else {
                    $appliedFilter['selectedstaffNumbers'] = $_POST['selectedstaffNumbers'];
                }
            }

        }   //POST END

        if (is_array($appliedFilter['selectedTeamID']) && !empty($appliedFilter['selectedTeamID'])) {
            $reskey=array_search('ALL', $appliedFilter['selectedTeamID']);
            if ($reskey!==false) {
                unset($appliedFilter['selectedTeamID'][$reskey]);
            }
            $teamforChargeCode = implode(',', $appliedFilter['selectedTeamID']);
        } else {
            if ($appliedFilter['selectedTeamID']=='ALL') {
                $teamforChargeCode = implode(',', $teams);
            }else {
             $teamforChargeCode = trim($appliedFilter['selectedTeamID']);
            }
        }
       //charge code
        $chargeCodes = $this->getChargeCodeByStaffID($teamforChargeCode);
        $chargeCodeslist = [];
        if (!empty($chargeCodes)) {
            foreach ($chargeCodes as $row) {
                if (!empty($row['CostCode'])) {
                    $chargeCodeslist[] = $row['CostCode'];
                }
            }
        }
        $pageData['allchargeCodes'] = $chargeCodeslist;
        $appliedFilter['selectedchargeCodes'] = null;
        if (isset($_POST['selectedchargeCodes']) && !empty($_POST['selectedchargeCodes'])) {
            $appliedFilter['selectedchargeCodes'] = explode(",", $_POST['selectedchargeCodes']);
        }

        if (is_array($appliedFilter['selectedchargeCodes']) && !empty($appliedFilter['selectedchargeCodes'])) {
            $reskey=array_search('ALL', $appliedFilter['selectedchargeCodes']);
            if ($reskey!==false) {
                unset($appliedFilter['selectedchargeCodes'][$reskey]);
            }
            $selectedchargeCodes = implode(',', $appliedFilter['selectedchargeCodes']);
        } else {
            if (($appliedFilter['selectedchargeCodes']=='ALL') || (empty($selectedchargeCodes))) {
                $selectedchargeCodes = null;
            }else {
             $selectedchargeCodes = trim($appliedFilter['selectedchargeCodes']);
            }
        }

        if (isset($appliedFilter['selectedgroupBy1'])) {
            $selectedgroupBy1=$appliedFilter['selectedgroupBy1'];
        }
        if (isset($appliedFilter['selectedgroupBy2'])) {
            $selectedgroupBy2=$appliedFilter['selectedgroupBy2'];
        }
        if (is_array($appliedFilter['selectedstaffNumbers'])) {
            $selectedstaffnumbers = implode(",", $appliedFilter['selectedstaffNumbers']);
        } else {
            $selectedstaffnumbers = $appliedFilter['selectedstaffNumbers'];
        }

        if (isset($appliedFilter['selectedWTDYearFrom']) && !empty($appliedFilter['selectedWTDYearFrom'])) {
            $selectedWTDYearFrom = $appliedFilter['selectedWTDYearFrom'];
        }

        if (isset($appliedFilter['selectedWTDYearTo']) && !empty($appliedFilter['selectedWTDYearTo'])) {
            $selectedWTDYearTo = $appliedFilter['selectedWTDYearTo'];
        }

        if (isset($appliedFilter['selectedWTDWeekFrom']) && !empty($appliedFilter['selectedWTDWeekFrom'])) {
            $selectedWTDWeekFrom = $appliedFilter['selectedWTDWeekFrom'];
        }
        if (isset($appliedFilter['selectedWTDWeekTo']) && !empty($appliedFilter['selectedWTDWeekTo'])) {
            $selectedWTDWeekTo = $appliedFilter['selectedWTDWeekTo'];
        }

        if (isset($appliedFilter['WTDbreechType'])) {
            $WTDbreechType = $appliedFilter['WTDbreechType'];
        }

        if (isset($appliedFilter['approvalStatus'])) {
            $approvalStatus = $appliedFilter['approvalStatus'];
        }

        $intFromWeekNumber = $selectedWTDYearFrom.$selectedWTDWeekFrom;
        $intToWeekNumber = $selectedWTDYearTo.$selectedWTDWeekTo;

        $WTDFromWeekDate = $this->getStartDateByWeek($intFromWeekNumber);
        $WTDToWeekDate  = $this->getEndDateByWeek($intToWeekNumber);

        $pageData['wtdstartDate'] = $WTDFromWeekDate;
        $pageData['wtdendDate'] = $WTDToWeekDate;
        $WTDSummery_F=[];

        $selectedgroupBy1 = $selectedgroupBy1 ?? null;
        $selectedgroupBy = $selectedgroupBy ?? null;

        if (($selectedgroupBy1 !=null) || ($selectedgroupBy !=null)) {
            if ($selectedgroupBy1 != null) {
                $sortingCol = $selectedgroupBy1;
            } elseif ($selectedgroupBy2 != null) {
                $sortingCol = $selectedgroupBy2;
            }
        }
        $wtdSummery_Q = "exec [dbo].[usp_get_WTDSummary] :TeamsID,:grp1, :grp2, :WTDFromWeekDate,:WTDToWeekDate, :chargeCodes,:staffNumbers,:WTDbreechType,:approvalStatus,:orderby,:orderbyvlue";

        $stmt = $this->dbResource->prepare($wtdSummery_Q);

        // The parameters
        $stmt->bindParam(':TeamsID', $teamforChargeCode, PDO::PARAM_STR);

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
        if (!empty($WTDSummery_F)) {
            $pageData['disableExport'] = 0;
        }
        $pageData['WTDSummery_F'] = $WTDSummery_F;
        $pageData['filterrequest'] = $appliedFilter;
        $this->loadView('WTDSummary', array($WTDSummery_F), $pageData);
    }

    /**
     * Description : This function search for ChargeCode By Staff Details
     * @param :$Teams strings 4,5
     * return array;
     */

    public function getChargeCodeByStaffID($Teams)
    {
        $CostCode_Q = "SELECT DISTINCT UC_CostCode
							FROM Scheduledpersonteam_link as stl (NOLOCK)
							INNER JOIN UserConfigs UC on UC_UserID = stl.ScheduledPersonID
							WHERE  stl.TeamID IN ($Teams)
							AND stl.IsHomeTeam = 1
							AND GETDATE() BETWEEN  stl.StartDate  AND stl.EndDate
							ORDER BY UC_CostCode ASC";
        $stmt = $this->dbResource->prepare($CostCode_Q);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Description : getWTDTypes
     * return array;
     */

    public function getWTDTypes()
    {
        $WTD_Types_Q = "SELECT * FROM WTD_Types (NOLOCK)";
        $stmt = $this->dbResource->prepare($WTD_Types_Q);
        // The parameters
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Description : getStartDate BY Week
     * @param :intweekno int
     * return array;
     */

    public function getStartDateByWeek($intweekno)
    {
        $dateStartStr[0]='';
        include_once '../../../function-includes/common/classCommonDBFunctions.php';
        $commonObj = new classCommonDBFunctions();
        $datefromweek = $commonObj->GetWeekStartDateByWeekNoFromTimeDim($intweekno,'ByweeknoOnly',NULL);
        $dateStartStr = explode(' ',$datefromweek['dDateTime']);
        return $dateStartStr[0];
    }
    /**
     * Description : getEndDate BY Week
     * @param :intweekno int
     * return array;
     */

    public function getEndDateByWeek($intweekno)
    {
        include_once '../../../function-includes/common/classCommonDBFunctions.php';
        $commonObj = new classCommonDBFunctions();
        $datefromweek = $commonObj->GetWeekStartDateByWeekNoFromTimeDim($intweekno,'ByweeknoOnly',NULL);
        $dateStartStr = explode(' ',$datefromweek['dDateTime']);
        $dteStartDate = $dateStartStr[0];
        return date('Y-m-d', strtotime($dteStartDate. ' + 6 days'));
    }
	public function dutyExtractFilterOption($requestData)
	{
		global $errorContainer;
		$schedulingTeam  = $requestData['schedulingTeam'];
		$getFilterList_q = "select * from AutoPagesFilters where isPublic = 1 AND SchedulingTeamId = $schedulingTeam";
        $getFilterList_f = $this->fetchData($getFilterList_q, array($schedulingTeam));
		$optionStr = '<option value="0">No Filter</option>';
		foreach($getFilterList_f['data'] as $getFilterList_d)
		{
			$optionStr .= '<option value="'.$getFilterList_d['ID'].'" >'.$getFilterList_d['Description'].'</option>';
		}
		echo $optionStr;
	}
	public function getLeaversRecord($requestData)
    {
        $schedulingTeamId = $requestData['schedulingTeamId'] ?? 0;
        $empNumber = $requestData['empNumber'] ?? 0;
        $estCode = $requestData['estCode'] ?? '';
        $name = $requestData['name'] ?? '';
        $records = $requestData['records'] ?? '';
        $filterType = $requestData['filterType'] ?? '';
		$recordOffset		=	$requestData['recordOffset'] ?? 0;
        $teamPayMissingRecord_q = "exec [dbo].[usp_Get_Leavers_Records] ?, ?, ?, ?, ?, ?, ?";
        $teamPayMissingRecord_f = $this->fetchData($teamPayMissingRecord_q, array($schedulingTeamId, $empNumber, $estCode, $filterType, $name, $records, $recordOffset));
        $getSchedulingTeams_q = "select schedulingTeamId, schedulingTeamName from schedulingTeams where isActive = 1 order by schedulingTeamName";
        $getSchedulingTeams_f = $this->fetchData($getSchedulingTeams_q, array());
        $this->loadView('listLeaversRecord', array($teamPayMissingRecord_f['data'], $getSchedulingTeams_f['data']), $requestData);
    }
	public function getEFTDiscNews($requestData)
    {
        $getEFTDiscTG_q = "exec [dbo].[usp_get_EFTReport] 2";
        $getEFTDiscTG_f = $this->fetchData($getEFTDiscTG_q, array());
        $this->loadView('getEFTDiscTG', array($getEFTDiscTG_f['data']), $requestData);
    }
	public function getPayTypeNews($requestData)
    {
        $getPayTypeNews_q = "exec [dbo].[usp_get_PaymentTypeReport] 2";
        $getPayTypeNews_f = $this->fetchData($getPayTypeNews_q, array());
        $this->loadView('getPayTypeNews', array($getPayTypeNews_f['data']), $requestData);
    }
	public function getEFTDiscTG($requestData)
    {
        $getEFTDiscTG_q = "exec [dbo].[usp_get_EFTReport] 1";
        $getEFTDiscTG_f = $this->fetchData($getEFTDiscTG_q, array());
        $this->loadView('getEFTDiscTG', array($getEFTDiscTG_f['data']), $requestData);
    }
	public function getPayTypeTG($requestData)
    {
        $getPayTypeTG_q = "exec [dbo].[usp_get_PaymentTypeReport] 1";
        $getPayTypeTG_f = $this->fetchData($getPayTypeTG_q, array());
        $this->loadView('getPayTypeNews', array($getPayTypeTG_f['data']), $requestData);
    }
    public function getChargeCodesOfTeams($requestData){
        $chargeCodes = $this->getChargeCodeByStaffID($requestData['selTeams']);
        $x['status'] = true;
        $x['chargeCodes'] = $chargeCodes;
        echo json_encode($x);
        exit;
    }
}
