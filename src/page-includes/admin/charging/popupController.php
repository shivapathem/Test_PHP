<?php
require_once '../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/page-includes/admin/charging/view/popupsChargingModules.php';
$popupId = $_POST['popupName'];
$codeType = $_POST['codeType'] != null ? $_POST['codeType'] : 0;
$actionType = $_POST['actionType'] != '' ? $_POST['actionType'] : 'add';
if ($codeType == 0) {
    // Popup Related variables
    if ($actionType == 'add') {
        $popupInputTitle = 'Add Charge Code';
        $popupFirstInputLabel = 'Charge Code';
        $popupSecondInputLabel = 'Description';
        $popupThirdInputLabel = 'Area';
        $popupFirstInput = 'addChargeCode';
        $popupSecondInput = 'addChargeCodeDesc';
        $popupSubmitButtonName = 'Add';
    } else {
        $popupInputTitle = 'Edit Charge Code';
        $popupFirstInputLabel = 'Charge Code';
        $popupSecondInputLabel = 'Description';
        $popupThirdInputLabel = 'Area';
        $popupFirstInput = 'editChargeCode';
        $popupSecondInput = 'editChargeCodeDesc';
        $popupSubmitButtonName = 'Update';
    }
    $popupDivId = 'receiverEntryAdd';
    $popupFormId = 'addchargeWbscodePopup';
    $popupThirdInput = 'DivisionId';
    $codeType = 0;
    $pageId = 17;
} else {
    // Popup Related variables
    if ($actionType == 'add') {
        $popupInputTitle = 'Add WBS Code';
        $popupFirstInputLabel = 'WBS Code';
        $popupSecondInputLabel = 'Description';
        $popupThirdInputLabel = 'Area';
        $popupFirstInput = 'addWbsCode';
        $popupSecondInput = 'addWbsCodeDesc';
        $popupSubmitButtonName = 'Add';
    } else {
        $popupInputTitle = 'Edit WBS Code';
        $popupFirstInputLabel = 'WBS Code';
        $popupSecondInputLabel = 'Description';
        $popupThirdInputLabel = 'Area';
        $popupFirstInput = 'editWbsCode';
        $popupSecondInput = 'editWbsCodeDesc';
        $popupSubmitButtonName = 'Update';
    }
    $popupDivId = 'receiverEntryEdit';
    $popupFormId = 'addchargeWbscodePopup';
    $popupThirdInput = 'DivisionId';
    $codeType = 1;
    $pageId = 18;
}
$popupCall = new popupsChargingModules;
if ($popupId == 'popupChargeWbsCode') {
    $InputParamPopupChargeWBSCode = array();
    $InputParamPopupChargeWBSCode['popupInputTitle'] = $popupInputTitle;
    $InputParamPopupChargeWBSCode['popupFirstInputLabel'] = $popupFirstInputLabel;
    $InputParamPopupChargeWBSCode['popupSecondInputLabel'] = $popupSecondInputLabel;
    $InputParamPopupChargeWBSCode['popupThirdInputLabel'] = $popupThirdInputLabel;
    $InputParamPopupChargeWBSCode['popupFormId'] = $popupFormId;
    $InputParamPopupChargeWBSCode['popupDivId'] = $popupDivId;
    $InputParamPopupChargeWBSCode['popupFirstInput'] = $popupFirstInput;
    $InputParamPopupChargeWBSCode['popupSecondInput'] = $popupSecondInput;
    $InputParamPopupChargeWBSCode['popupThirdInput'] = $popupThirdInput;
    $InputParamPopupChargeWBSCode['popupSubmitButtonName'] = $popupSubmitButtonName;
    $InputParamPopupChargeWBSCode['codeType'] = $codeType;
    $InputParamPopupChargeWBSCode['actionType'] = $actionType;
    $InputParamPopupChargeWBSCode['pageId'] = $pageId;
    $dataPopup = $popupCall->popupChargeCode($InputParamPopupChargeWBSCode);
    echo $dataPopup;
}
