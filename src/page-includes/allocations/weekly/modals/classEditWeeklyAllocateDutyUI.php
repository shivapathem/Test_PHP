<?php

/**
 * Class classEditWeeklyAllocateDutyUI
 * This Class used for to handle the UI only not any logic will come here ,
 * this class have multiple view in different function
 *
 */
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

class classEditWeeklyAllocateDutyUI
{
 /*
    * @Description :Add Week Number Popup
	* @access : Public
	* @global : Not Applicable
	* @param  : @request
	* @return : HTML
    */
    function addToUnalloctedDutiesWeek(Request $request,$dutyName='')
    {
        $request = Request::createFromGlobals();
        $service = new AllocationService();

        $request = $service->prepareRequestDates($request);
        $date = new Carbon(date('Y-m-d'));

        $timeDimensionServiceObj = new TimeDimensionService();

        $weekNum = $timeDimensionServiceObj->findByDateWithoutCarbon($date->format('Y-m-d'));

        $weekYear = substr(strval($weekNum->ixYearWeek),0,4);
        $weekNum = substr(strval($weekNum->ixYearWeek),4,2);

        $currWeekValue = $weekNum.'/'.$weekYear;

        $addview = '';
        $addview.='
        <form id="addunallocateddutyform">
        <table id="addunallocatedduty" class="smalltable bluetable" width="100%" role="presentation">
        <thead><tr><th colspan="5">Add to Unallocated Duties ('.$dutyName.')</th></tr></thead>
        <tbody>
          <tr><td class="lightblue">Add To Unallocated Duties From Week</td><td colspan="3"><input id="weeknumber"  name="weeknumber" type="text" size="20" value="'.$currWeekValue.'"></td></tr>
          <tr id ="bugtd">
                <td colspan="2" class ="messageerror error" align="center" ></td>
                </tr>

          <tr>
              <td></td>
              <td colspan="3">
                 <input type="hidden" name="teamId" value="'. $request->get('teamID').'">
                <input type="hidden" name="dutyID" value="'. $request->get('dutyID').'">
                <input type="hidden" name="action" value="saveunallocatedduties">
                <input name="js_saveUnallocted" id="js_saveUnallocted" type="submit" value="Add">

              </td>
          </tr>
                  </tbody>
              </table>
            </form> ';
        $addview.='<script>
        $("#weeknumber").on("click", function () {
            $(this).select();
        });

        $("#weeknumber").focus();
        $("#weeknumber").select();
        </script>';
        return $addview;
    }
/*
    * @Description :Add Week Number Popup
	* @access : Public
	* @global : Not Applicable
	* @param  : @request
	* @return : HTML
    */
    function copyToAllDutiesWeek(Request $request,$dutyName='')
    {
        $request = Request::createFromGlobals();
        $service = new AllocationService();

        $request = $service->prepareRequestDates($request);
        $date = new Carbon(date('Y-m-d'));

        $timeDimensionServiceObj = new TimeDimensionService();

        $weekNum = $timeDimensionServiceObj->findByDateWithoutCarbon($date->format('Y-m-d'));

        $weekYear = substr(strval($weekNum->ixYearWeek),0,4);
        $weekNum = substr(strval($weekNum->ixYearWeek),4,2);

        $currWeekValue = $weekNum.'/'.$weekYear;

        $addview = '';
        $addview.='
        <form id="copytoalldutiesform">
        <table id="copytoallduties" class="smalltable bluetable" width="100%">
        <thead><tr><th colspan="5">Copy To All Duties ('.$dutyName.')</th></tr></thead>
        <tbody>
          <tr><td class="lightblue">Copy To All Duties From Week</td><td colspan="3"><input id="copytoaldutiesweeknumber"  name="copytoaldutiesweeknumber" type="text" size="20" value="'.$currWeekValue.'"></td></tr>
          <tr id ="bugtd">
                <td colspan="2" class ="copytoalldutieserrormsg error" align="center" ></td>
                </tr>

          <tr>
              <td></td>
              <td colspan="3">
                 <input type="hidden" name="teamId" value="'. $request->get('teamID').'">
                <input type="hidden" name="dutyID" value="'. $request->get('dutyID').'">
                <input type="hidden" name="action" value="saveCopyToAllDutiesForm">
                <input name="js_saveCopyForm" id="js_saveCopyForm" type="submit" value="Copy">

              </td>
          </tr>
                  </tbody>
              </table>
            </form> ';
        $addview.='<script>
        $("#copytoaldutiesweeknumber").on("click", function () {
            $(this).select();
        });

        $("#copytoaldutiesweeknumber").focus();
        $("#copytoaldutiesweeknumber").select();
        </script>';
        return $addview;
    }
    /*
    * @Description :Inital Design for Sicknesspopup
	* @access : Public
	* @global : Not Applicable
	* @param  : no param
	* @return : HTML
    */
    function sicknessPopup(Request $request)
    {
		$service = new AllocationService();
        $hours = $request->get('hours');
        $reasonId = $comments ='';
        $action = $request->get('action');
        $sicknessID = 0;
        $checked = '';
        $prevDayReasonId = $request->get('prevDayReasonId');
        $nextPrevDayReasonId = $request->get('nextPrevDayReasonId');
        $nextDayReasonId = $request->get('nextDayReasonId');
        $nextDayComment = $request->get('nextDayComment');
        $nextPrevDayComment = $request->get('nextPrevDayComment');
        $prevDayComment = $request->get('prevDayComment');
        $dataval = $request->get('dataval') ?? [];
		$sicknessscreen= $dataval['screen'] ?? '';
		$sicknessteamId= $dataval['teamId'] ?? '';
        $pschedulingPersonId = $dataval['schedulingPersonId'] ?? '';
        $pdateonly = $dataval['dateonly'] ?? '';
        $pprevDayMarkedSickness = $dataval['prevDayMarkedSickness'] ?? '';
        $pcurrDayMarkedSickness = $dataval['currDayMarkedSickness'] ?? '';
        $pbeforePrevDayReasonId = $dataval['beforePrevDayReasonId'] ?? '';
        $pdutyName = $dataval['dutyName'] ?? '';
        $pdutyDate = $dataval['dutyDate'] ?? '';
        $pfDateStartDate = $dataval['fDateStartDate'] ?? '';
        $pfDateRangeDays = $dataval['fDateRangeDays'] ?? '';
        $pfDataRowStart = $dataval['fDataRowStart'] ?? '';
        $pfDataRowEnd = $dataval['fDataRowEnd'] ?? '';
        $pfDataSchedulingTeamId = $dataval['fDataSchedulingTeamId'] ?? '';
        $pfDateEndDate = $dataval['fDateEndDate'] ?? '';
        $pfDataDutyDuration = $dataval['fDataDutyDuration'] ?? '';
        $pfDataMasterdutyid = $dataval['fDataMasterdutyid'] ?? '';
        $pfDataIsactive = $dataval['fDataIsactive'] ?? '';
        $piday = $dataval['iday'] ?? '';
        $pallocid = $dataval['allocid'] ?? '';
        $pweekStartDate = $dataval['weekStartDate'] ?? '';
        $pweekEndDate = $dataval['weekEndDate'] ?? '';
        $pweeknumber = $dataval['weeknumber'] ?? '';
        $pdate = $dataval['date'] ?? '';
        $allocationsSpId = $dataval['allocationsSpId'] ?? '';
        $allocationsDutyId = $dataval['allocationsDutyId'] ?? '';
        $isChargingPresent = $dataval['isChargingPresent'] ?? '';

        $warrningmsg = $warrningclass='';
        if($request->get('getConsecutiveSickness')['totalcount']> 3){
            $warrningmsg = 'More than 3 Days sick. Self certification required';
            $warrningclass = 'sicknessWarnnignMsg';
        }
        if($request->get('getConsecutiveSickness')['totalcount']> 7){
            $warrningmsg = "More than 7 Days sick. Doctor's Certificate required.";
            $warrningclass = 'sicknessWarnnignMsg';
        }
        $sicknessHrsOld = '';
        if(!empty($request->get('userSicknessRecord'))){
            $reasonId = array_key_exists('SicknessReasonsId', $request->get('userSicknessRecord')[0]) ? $request->get('userSicknessRecord')[0]['SicknessReasonsId'] : '';
            $action =$request->get('action');
            if(isset($request->get('userSicknessRecord')[0]['Hours']) && $request->get('userSicknessRecord')[0]['Hours'] != ''){
                $hours = $service->convertSecondsIntoTime($request->get('userSicknessRecord')[0]['Hours'],'.','No');
				$sicknessHrsOld = $request->get('userSicknessRecord')[0]['Hours'];
                // $m = floor(($request->get('userSicknessRecord')[0]['Hours']%3600)/60);
                // $h = floor(($request->get('userSicknessRecord')[0]['Hours']%86400)/3600);
                // $hours = str_pad($h,2,'0',STR_PAD_LEFT).":".str_pad($m,2,'0',STR_PAD_RIGHT);
            }
            $comments =array_key_exists('Comment', $request->get('userSicknessRecord')[0]) ? $request->get('userSicknessRecord')[0]['Comment'] : '';
            $sicknessID = array_key_exists('SicknessID', $request->get('userSicknessRecord')[0]) ? $request->get('userSicknessRecord')[0]['SicknessID'] : '';
             if(array_key_exists('isChecked', $request->get('userSicknessRecord')[0]) && $request->get('userSicknessRecord')[0]['isChecked']== 1 ){
                 $checked ='checked';
             }
        }

        $addview = '';
        $addview.='
        <div class="sickness-container">
        <div class="sickness-main-heading">
            <h1 class="sickness-heading">Sickness
            </h1>
        </div>
        <form  id="sicknessrecordform" >
        <fieldset>
            <legend class="week-small-text">Sickness Summary</legend>

            <table>
                <tr>
                    <td colspan="11">
                        <label class="sick-lab">Total Consecutive days sick (including this one)</label>
                    </td>
                    <td><input class="sick-inp" type="text" readonly placeholder="1" value ="'.$request->get('getConsecutiveSickness')['totalcount'].'"></td>
                </tr>
                <tr>
                    <td colspan="4"><label class="sick-lab">Number of previous days sick</label></td>
                    <td colspan="2"><input class="sick-inp" type="text" readonly  placeholder="0" value ="'.$request->get('getConsecutiveSickness')['PrevDay'].'"></td>
                    <td colspan="5"><label class="sick-lab">Number of subsequent days sick</label></td>
                    <td colspan="2"><input class="sick-inp" type="text" readonly placeholder="0" value ="'.$request->get('getConsecutiveSickness')['NextDay'].'"></td>
                </tr>
            </table>

        </fieldset>

        <fieldset>
            <legend class="week-small-text">Sickness Details</legend>
            <table>
                <tr>
                    <td colspan="4" style="width:13%;"><label class="sick-lab">Name</label></td>
                    <td colspan="3" style="width:40%;"><input class="width-140" type="text" readonly  value ="'.$request->get('displayName').'"></td>
                    <td colspan="6" style="width:40%;"><label class="sick-lab">Staff Number</label></td>
                    <td colspan="2"><input class="width-55" type="text" name="staffnumber"id="staffnumber" readonly  value ="'.$request->get('staffnumber').'"></td>
                </tr>
                <tr>
                    <td colspan="4"><label class="sick-lab">Reason <span class="required">*</span></label></td>
                    <td colspan="3">

                       <select class="select-sickness width-140" name="reasonlist" id="reasonlist" onChange="sicknessNameNew.value = this.options[this.selectedIndex].text;">';
					   $sicknessNameOld = '';
                       $addview.='  <option  value="0">Please Select</option>';
                            foreach($request->get('sicknessReasonList') as $reasondata){
                                if($reasondata['SicknessReasonID'] == $reasonId)
								{
									$selected = 'selected';
									$sicknessNameOld = $reasondata['SicknessName'];
								}else
								{
									$selected = '';
								}
                              $addview.='  <option '.$selected.' value="'.$reasondata['SicknessReasonID'].'">'.$reasondata['SicknessName'].'</option>';
                            }
                       $addview.=' </select>
                    </td>

                    <td colspan="6"><label class="sick-lab">Duration (Exc breaks)</label></td>
                    <td colspan="2"><input class="width-55" type="text" name="hours" id="hours" value ="'.$hours.'" onclick="hideSickHrError();"></td>
                </tr>
                <tr>
                    <td colspan="4">&nbsp;</td>
                    <td colspan="3"><span class="messageerror error width-140" style="display:none;" id="reasonerror"></span></td>
                    <td colspan="8" style="width:12%;"><span class="messageerror error" style="display:none;" id="durationerror"></span></td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend class="week-small-text">Comments</legend>
            <table>
                <tr>
                    <textarea class="sick-textarea" name="comments" id = "comments" colspan="12" maxlength=500 aria-valuemax="100">'.$comments.'</textarea>
                </tr>
                <tr><input type="checkbox" name="ischeck" id="ischeck" '. $checked.' value="0" style="vertical-align: middle;"><span class="sick-text">Update related Sickness comments as well</span></tr>

                <input type="hidden" name="scheduledpersonId" value ="'.$pschedulingPersonId.'">
                <input type="hidden" name="js_date" value ="'. $pdateonly.'">
                <input type="hidden" id="js_action" name="js_action" value ="'. $action.'">
                <input type="hidden" name="js_actionvalue" value ="saveSicknessAction">
                <input type="hidden" name="js_sicknessid" value="'.$sicknessID.'">
                <input type="hidden" name="js_allocationid" value="'.$pallocid.'">
                <input type="hidden" id="js_nextPrevDayComment" name="js_nextPrevDayComment" value="'.$nextPrevDayComment.'">
                <input type="hidden" id="js_nextPrevDayReasonId"name="js_nextPrevDayReasonId" value="'.$nextPrevDayReasonId.'">
                <input type="hidden" id="js_nextDayReasonId" name="js_nextDayReasonId" value="'.$nextDayReasonId.'">
                <input type="hidden" id="js_prevDayReasonId"  name="js_prevDayReasonId" value="'.$prevDayReasonId.'">
                <input type="hidden" id="js_nextDayComment" name="js_nextDayComment" value="'.$nextDayComment.'">
                <input type="hidden" id="js_prevDayComment" name="js_prevDayComment" value="'.$prevDayComment.'">
                <input type="hidden" id="sicknessscreen" name="sicknessscreen" value="'.$sicknessscreen.'">
                <input type="hidden" id="sicknessteamId" name="sicknessteamId" value="'.$sicknessteamId.'">
                <input type="hidden" id="wstartdate" name="wstartdate" value="'.$pweekStartDate.'">
                <input type="hidden" id="wenddate" name="wenddate" value="'.$pweekEndDate.'">
                <input type="hidden" id="weeknumber" name="weeknumber" value="'.$pweeknumber.'">
                <input type="hidden" id="edate" name="edate" value="'.$pdate.'">
                <input type="hidden" id="prevDayMarkedSickness" name="prevDayMarkedSickness" value="'.$pprevDayMarkedSickness.'">
                <input type="hidden" id="currDayMarkedSickness" name="currDayMarkedSickness" value="'.$pcurrDayMarkedSickness.'">
                <input type="hidden" id="beforePrevDayReasonId" name="beforePrevDayReasonId" value="'.$pbeforePrevDayReasonId.'">
                <input type="hidden" id="oDutyName" name="oDutyName" value="'.$pdutyName.'">
                <input type="hidden" id="dutyDate" name="dutyDate" value="'.$pdutyDate.'">
                <input type="hidden" id="fDateStartDate" name="fDateStartDate" value="'.$pfDateStartDate.'">
                <input type="hidden" id="fDateRangeDays" name="fDateRangeDays" value="'.$pfDateRangeDays.'">
                <input type="hidden" id="fDataRowStart" name="fDataRowStart" value="'.$pfDataRowStart.'">
                <input type="hidden" id="fDataRowEnd" name="fDataRowEnd" value="'.$pfDataRowEnd.'">
                <input type="hidden" id="fDataSchedulingTeamId" name="fDataSchedulingTeamId" value="'.$pfDataSchedulingTeamId.'">
                <input type="hidden" id="fDateEndDate" name="fDateEndDate" value="'.$pfDateEndDate.'">
                <input type="hidden" id="fDataDutyDuration" name="fDataDutyDuration" value="'.$pfDataDutyDuration.'">
                <input type="hidden" id="fDataMasterdutyid" name="fDataMasterdutyid" value="'.$pfDataMasterdutyid.'">
                <input type="hidden" id="fDataIsactive" name="fDataIsactive" value="'.$pfDataIsactive.'">
                <input type="hidden" id="fDataIday" name="fDataIday" value="'.$piday.'">
                <input type="hidden" id="sicknessNameNew" name="sicknessNameNew">
                <input type="hidden" id="sicknessNameOld" name="sicknessNameOld" value="'.$sicknessNameOld.'">
                <input type="hidden" id="sicknessIdOld" name="sicknessIdOld" value="'.$reasonId.'">
                <input type="hidden" id="sicknessHrsOld" name="sicknessHrsOld" value="'.$sicknessHrsOld.'">
                <input type="hidden" id="isChargingPresentSick" name="isChargingPresent" value="'.$isChargingPresent.'">
                <input type="hidden" id="allocationsSpIdSick" name="allocationsSpId" value="'.$allocationsSpId.'">
                <input type="hidden" id="allocationsDutyIdSick" name="allocationsDutyId" value="'.$allocationsDutyId.'">
            </table>
        </fieldset>

        <table>

        <tr ><td class="'.$warrningclass.'">'.$warrningmsg.'</td></tr>

        <tr>
        <td class="btn-c">
        <input name="js_SickSubmit" id="js_SickSubmit" type="submit" value="Save">
        </td>
        </tr>
        </table>
        </form>
        </div> ';
        return $addview;
    }
}
