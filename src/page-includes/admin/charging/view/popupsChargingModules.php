<link href="../styles/charging/charging.css" rel="stylesheet">
<?php

class popupsChargingModules
{
    function popupChargeCode($InputParamPopupChargeWBSCode)
    {
        $popupInputTitle = $InputParamPopupChargeWBSCode['popupInputTitle'];
        $popupFirstInputLabel = $InputParamPopupChargeWBSCode['popupFirstInputLabel'];
        $popupSecondInputLabel = $InputParamPopupChargeWBSCode['popupSecondInputLabel'];
        $popupThirdInputLabel = $InputParamPopupChargeWBSCode['popupThirdInputLabel'];
        $popupFormId = $InputParamPopupChargeWBSCode['popupFormId'];
        $popupDivId = $InputParamPopupChargeWBSCode['popupDivId'];
        $popupFirstInput = $InputParamPopupChargeWBSCode['popupFirstInput'];
        $popupSecondInput = $InputParamPopupChargeWBSCode['popupSecondInput'];
        $popupThirdInput = $InputParamPopupChargeWBSCode['popupThirdInput'];
        $popupSubmitButtonName = $InputParamPopupChargeWBSCode['popupSubmitButtonName'];
        $codeType = $InputParamPopupChargeWBSCode['codeType'];
        $actionType = $InputParamPopupChargeWBSCode['actionType'];
        $pageId = $InputParamPopupChargeWBSCode['pageId'];
        $disabledControl = $actionType == 'edit' ? 'disabled' : '';
        $datapopup = '';
        $datapopup .= '<form id="'.$popupFormId.'" name="'.$popupFormId.'" onsubmit="return formHandler('.$popupFormId.')">
            <div id="receiverEntryAdd">
                <!-- Please update the popup label for Add Charge Code !-->
                <div class="popupHeading" id="'.$popupDivId.'" name="'.$popupDivId.'">'.$popupInputTitle.'</div>
                <div class="popupContent">
                    <div class="fields">
                        <label class="popupLabelAlignment" for="addChargeCode">'.$popupFirstInputLabel.'<span class="required">*</span></label>
                        <input type="text" class="firstInputChargeWbsCode" maxlen="50" mandatory="yes" id="'.$popupFirstInput.'" name="'.$popupFirstInput.'" '.$disabledControl.'  fieldname="'.$popupFirstInputLabel.'" maxlength="50"/>
						<input type="hidden" id="addEditWbsCodeName" name="addEditWbsCodeName" />
                    </div>
                    <div class="fields">
                        <label class="popupLabelAlignment" for="addChargeCodeDesc">'.$popupSecondInputLabel.'<span class="required">*</span></label>
                        <input type="text" class="secondInputChargeWbsCode" maxlen="75" fieldname="'.$popupSecondInputLabel.'" mandatory="yes" id="'.$popupSecondInput.'" name="'.$popupSecondInput.'" maxlength="75"/>
                    </div>
                    <div class="fields">
                        <label class="popupLabelAlignment" for="costCenterDesc">'.$popupThirdInputLabel.'<span class="required">*</span></label>
                        <select class="thirdInputChargeWbsCode division-seclect" fieldname="'.$popupThirdInputLabel.'" name="'.$popupThirdInput.'" id="'.$popupThirdInput.'" mandatory="yes"></select>
                    </div>

                    <div class="popupButton">
                        <input type="button" class="charging-btn charging-btn-add" value="'.$popupSubmitButtonName.'" onclick="formHandler(\'addchargeWbscodePopup\')"/>
                        <input type="button" class="charging-btn" value="Cancel" onclick="CancelChargeCode()"/>
                        <input type="hidden" id="conrollerName" name="conrollerName" value="insUpdateChargeWbsCode" />
                        <input type="hidden" id="ChargeWbsCodeId" name="ChargeWbsCodeId" />
                        <input type="hidden" id="codeType" name="codeType" value="'.$codeType.'" />
                        <input type="hidden" id="actionType" name="actionType" value="'.$actionType.'" />
                        <input type="hidden" id="pageId" name="pageId" value="'.$pageId.'" />
                    </div>
                </div>
            </div>
        </form>';
        echo $datapopup;
    }
}
