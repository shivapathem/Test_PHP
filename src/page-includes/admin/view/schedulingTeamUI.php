<?php

include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/pageperms.php';
include_once '../../../class-includes/userRolePermissions.php';
include_once '../../../function-includes/genericfunctions.php';


class schedulingTeamUI
{
    /*
    * @Description : Controller function for schedulling team UI.
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : String data
    */
    public $hasProductionGroupAccess = false;

    public function __construct($userRole)
    {
        if(isset($userRole)){
          $this->hasProductionGroupAccess = $userRole;
        }
    }

    private function listingHtml() {
      $html = '<tr><th class="w150">Scheduling Team Name</th>
      <th class="w250">Scheduling Team Description</th>
      <th class="w150">Area Name</th>
      <th class="w150">Scheduling Groups</th>
      <th class="w150">Default Charge Code</th>
      <th class="w150">Active</th>
      <th class="w100">Default Sickness hours for Allocation</th>
      <th class="w100">Default Duty Duration (Exc Break)</th>
      <th class="w100">Work Time Directive Opt Out</th>
      <th>Check Over Six Days Worked</th>
      <th>Check Over Five Days Worked</th>
      <th>Sign-In</th>
      <th class="w100">Sign-In Days</th>
      <th class="w100">Allow In Building Sign-in</th>
      <th class="w100">Allow Overtime Requests</th>
      <th>Colour Week</th>
      <th>Locks</th>
      <th>Locks Start</th>
      <th>Locks End</th>
      <th class="w100">Locks Week At a Time</th>
      <th>Mask Type</th>
      <th>Mask After</th>
      <th class="w100">Daily View Masking</th>
      <th class="w100">Daily View Masking Days</th>
      <th>Freelancer Masking</th>
      <th>Freelancer Masking Days</th>
      <th>Restricted Editing</th>
      <th class="w100">Number of Days Allowed for Editing</th>
      <th class="w120">Editing Start</th>
      <th class="w120">Editing End</th>
      <th>Weekend Only</th>
      <th class="w100">Lock Current Day on Timer</th>
      <th class="w100">Show Grid Checks</th>
      <th>Show Production View</th>
      <th>Allow Master Duties in Create Duty from Rota</th>
      <th>Show Jobs In Weekly View</th>
      <th class="w100">Auto Import Weeks</th>
      <th class="w100">No. of Auto-import weeks</th>
      <th>Show Christmas Points</th>
      <th class="w100">Default Number of weeks in Rota Pattern</th>
      <th class="w80">Default Rota Start Date</th>
      <th>Current Leave Year</th>
      <th>Selectively Hide Leave</th>
      <th>Show Handovers</th>
      <th class="w100">Staff Availability Report Start Date</th>
      <th>Actions</th></tr>';

      return $html;
    } 

    private function addTeamTabs(){
      $html = '<ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
          <li role="tab" tabindex="1"
              class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active ui-state-focus schedulepersontab notclickable" id="identityTab"
              aria-controls="admindeptstabs-1" aria-labelledby="ui-id-1" aria-selected="true" aria-expanded="true">
              <a onclick="openTab(`identitytab`)" role="presentation" tabindex="1"
                  class="ui-tabs-anchor tablinks " id="ui-id-1">Identity</a>
          </li>
          <li role="tab" tabindex="2"
              class="ui-tabs-tab ui-corner-top ui-state-default ui-tab schedule-person-team schedulepersontab notclickable" id="allocationTab"
              aria-controls="admindeptstabs-2" aria-labelledby="ui-id-2" aria-selected="false"
              aria-expanded="false">
              <a onclick="openTab(`allocationtab`)" role="presentation" tabindex="2"
                  class="ui-tabs-anchor tablinks " id="ui-id-2">Allocations</a>
          </li>
          
          <li role="tab" tabindex="3"
              class="ui-tabs-tab ui-corner-top ui-state-default ui-tab schedule-person-team schedulepersontab notclickable" id="miscellaneousTab"
              aria-controls="admindeptstabs-3" aria-labelledby="ui-id-3" aria-selected="false"
              aria-expanded="false">
              <a onclick="openTab(`miscellaneoustab`)" role="presentation" tabindex="3"
                  class="ui-tabs-anchor tablinks" id="ui-id-3">Miscellaneous</a>
          </li>
      </ul>';
      
      return $html;
    }

    private function identityTabHtml($controlhideinput, $controlhideinput2) {
      $html = '<div id="identitytab" class="tabcontent" style="display: block;">
          <!-- Tab start Content -->
          <div id="identitytable" class="smalltable bluetable resetmargintopbottom identitytable-layout">
                  <div class="identitytable-row">
                      <div class="identitytable-cell identitytable-label lightblue"><label for="schedulingTeamName">Scheduling Team Name<span id="Addlabelstartdate" class="required">*</span></label></div>
                      <div class="identitytable-cell identitytable-value">
                          <input id="schedulingTeamName" name="schedulingTeamName" class="widthpopupcontrols" type="text" maxlength="100">
                          <input id="identityTabDone" name="identityTabDone" type="hidden" value=0>
                          <input id="allocationTabDone" name="identityTabDone" type="hidden" value=0>
                          <input id="miscelTabDone" name="identityTabDone" type="hidden" value=0>
                      </div>
                  </div>
                  
                  <div class="identitytable-row">
                      <div class="identitytable-cell identitytable-label lightblue"><label for="schedulingTeamDescription">Scheduling Team Description </label></div>
                      <div class="identitytable-cell identitytable-value">
                          <textarea id="schedulingTeamDescription" class="text-area" name="schedulingTeamDescription" type="text" class="widthpopupcontrols" maxlength="500"></textarea>
                      </div>
                  </div>
                  <div class="identitytable-row">
                      <div class="identitytable-cell identitytable-label lightblue"><label for="divisionId">Area<span id="Addlabelstartdate" class="required">*</span></label></div>
                      <div class="identitytable-cell identitytable-value">
                          <select class="ddlTeams select_itemss" name="divisionId" id="divisionId" class="widthpopupcontrols"></select>
                          <input type="text" name="divisionName" class="divName activeclass" id="divisionName" value="" disabled="disabled" aria-label="Area Name">
                      </div>
                  </div>
                  <div class="identitytable-row">
                    <div class="identitytable-cell identitytable-label lightblue">
                      <label for="schedulingGroups" id="schedulingGroups-label">Scheduling Groups</label>
                    </div>
                    <div class="identitytable-cell identitytable-value">
                          <select id="schedulingGroups" name="schedulingGroups[]" class="widthpopupcontrols chosen-select" multiple="multiple" data-placeholder="Select one or more groups" aria-labelledby="schedulingGroups-label" aria-describedby="schedulingGroups-hint"></select>
                    </div>
                  </div>
                  <div class="identitytable-row">
                      <div class="identitytable-cell identitytable-label lightblue"><label for="defaultChargeCode">Default Charge Code </label></div>
                      <div class="identitytable-cell identitytable-value">
                          <input id="defaultChargeCode" name="defaultChargeCode" type="text" pattern="[0-9]{1,10}" maxlength="10">
                      </div>
                  </div>
                  <div class="identitytable-row">
                  <div class="identitytable-cell identitytable-label lightblue"><label for="defaultChargeCodeDesc">Default Charge Code Description </label></div>
                  <div class="identitytable-cell identitytable-value">
                      <input id="defaultChargeCodeDesc" name="defaultChargeCodeDesc" type="text"  maxlength="75">
                  </div>
                  </div>
                  <div class="identitytable-row">
                      <div class="identitytable-cell identitytable-label lightblue"><label for="defaultActiveCode">Default Activity Code</label></div>
                      <div class="identitytable-cell identitytable-value">
                          <select class="ddlTeams select_itemss" name="defaultActiveCode" id="defaultActiveCode" class="widthpopupcontrols"></select>
                          <input type="text" name="defaultActiveCodeId" class="divName activeclass" id="defaultActiveCodeName" value="" disabled="disabled" aria-label="Default Activity Code Name">
                      </div>
                  </div>

                  <div class="identitytable-row">
                      <div class="identitytable-cell identitytable-label"><label for="isActive">Active<span id="Addlabelstartdate" class="required">*</span></label></div>
                      <div class="identitytable-cell identitytable-value">
                          <input id="isActive" name="isActive" type="checkbox">
                      </div>
                  </div>
                  <div class="identitytable-row identitytable-row-full">
                      <div class="identitytable-cell identitytable-fullwidth">
                          <input name="identitysubmit" class="handcursorimageall" id="identitysubmit" type="button" value="Next" ' . $controlhideinput . ' >
                          <!-- <input name="identitynext" class="handcursorimageall" id="identitynext" type="button" value="Next"> -->
                          <input name="identityclose" class="handcursorimageall cancelschteampopup" id="identityclose" type="button" value="Cancel">
                          <input id="intnewschteamid" type="hidden" value="0">
                      </div>
                  </div>
          </div>
      <!--Tab content closes-->
      </div>
      <!-- End Identity Tab -->';

      return $html;
    }
    private function allocationTabHtmlPre() {
      $html = '<div id="allocationtab" class="tabcontent" style="display: none;">
      <div id="rotanewedittable" class="smalltable bluetable allocationtable-layout" role="presentation">
        <div class="allocationtable-row allocationtable-row-4">
          <div class="allocationtable-cell allocationtable-label">
            <label for="defaultSicknessHoursAllocation">Default Sickness hours for Allocation
              <span id="Addlabelstartdate" class="required">*</span>
            </label>
          </div>
          <div class="allocationtable-cell allocationtable-value">
            <select class="ddlTeams widthallocationcontrols" name="defaultSicknessHoursAllocation" id="defaultSicknessHoursAllocation">
              <option value="1">7 Hours</option>
              <option value="2">Standard Day length from teampay config</option>
              <option value="3">Shift Length (without mealbreaks)</option> 
            </select>
          </div>
          <div class="allocationtable-cell allocationtable-label">
            <label for="defaultDutyDuration" id="w-25">Default Duty Duration (Exc Break)
              <span id="Addlabelstartdate" class="required">*</span></label>
          </div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="defaultDutyDuration" name="defaultDutyDuration" type="text" class="widthallocationcontrols" maxlength="5">
          </div>
        </div>
      </div>
      <hr>
      <div id="rotanewedittable" class="smalltable bluetable allocationtable-layout" role="presentation">
        <div class="allocationtable-row allocationtable-row-4">
          <div class="allocationtable-cell allocationtable-label">
            <label for="maskType">Mask Type</label>
          </div>
          <div class="allocationtable-cell allocationtable-value">
            <select class="ddlTeams widthallocationcontrols" name="maskType" id="maskType">
            </select>
          </div>
          <div class="allocationtable-cell allocationtable-label">
            <label for="maskAfter">Mask After</label>
            <span id="Addlabelstartdate" class="required">*</span>
          </div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="maskAfter" name="maskAfter" maxlength="3" type="text" class="widthallocationcontrols">
          </div>
        </div>
        <div class="allocationtable-row allocationtable-row-4">
          <div class="allocationtable-cell allocationtable-label">
            <label for="DailyViewMasking">Daily View Masking</label>
          </div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="DailyViewMasking" name="DailyViewMasking" type="checkbox">
          </div>
          <div class="allocationtable-cell allocationtable-label"><label for="dailyViewMaskingDays">Daily View Masking Days</label>
          </div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="dailyViewMaskingDays" name="dailyViewMaskingDays" maxlength="3" type="text" class="widthallocationcontrols">
          </div>
        </div>
        <div class="allocationtable-row allocationtable-row-4">
          <div class="allocationtable-cell allocationtable-label"><label for="freelancerMasking">Freelancer Masking
            </label></div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="freelancerMasking" name="freelancerMasking" type="checkbox">
          </div>

          <div class="allocationtable-cell allocationtable-label"><label for="freelancerMaskingDays"> Freelancer Masking Days
            </label></div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="freelancerMaskingDays" name="freelancerMaskingDays" maxlength="3" type="text" class="widthallocationcontrols">
          </div>
        </div>
      </div>
      <hr>
      <div id="rotanewedittable" class="smalltable bluetable allocationtable-layout" role="presentation">
        <div class="allocationtable-row allocationtable-row-4">
          <div class="allocationtable-cell allocationtable-label">
            <label for="restrictedEditing"> Time Restricted </label>
          </div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="restrictedEditing" name="restrictedEditing" type="checkbox">
          </div>
          <div class="allocationtable-cell allocationtable-label">
            <label for="numberofDaysAllowedEditing"> Number of Days Allowed for Editing
            </label>
            <span id="Addlabelstartdate" class="required">*</span>
          </div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="numberofDaysAllowedEditing" name="numberofDaysAllowedEditing" type="text" maxlength="3" class="widthallocationcontrols">
          </div>
        </div>
        <div class="allocationtable-row allocationtable-row-4">
          <div class="allocationtable-cell allocationtable-label"><label for="WeekendOnly">Weekend Only
            </label></div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="WeekendOnly" name="WeekendOnly" type="checkbox">
          </div>

          <div class="allocationtable-cell allocationtable-label"><label id="editingStart-label" for="editingStartHour">Editing Start
            </label><span id="Addlabelstartdate" class="required">*</span></div>
          <div class="allocationtable-cell allocationtable-value">
            <select id="editingStartHour" name="editingStartHour" class="widthallocationcontrolstime"></select>
            : <select id="editingStartMinute" name="editingStartMinute" class="widthallocationcontrolstime" aria-labelledby="editingStart-label"></select>
          </div>
        </div>
        <div class="allocationtable-row allocationtable-row-4">
          <div class="allocationtable-cell allocationtable-label"><label for="autoLockTodayTimer"> Lock Current Day on Timer
            </label></div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="autoLockTodayTimer" name="autoLockTodayTimer" type="checkbox">
          </div>
          <div class="allocationtable-cell allocationtable-label"><label id="editingEnd-label" for="editingEndHour"> Editing End
            </label><span id="Addlabelstartdate" class="required">*</span></div>
          <div class="allocationtable-cell allocationtable-value">
            <select id="editingEndHour" name="editingEndHour" class="widthallocationcontrolstime"></select>
            : <select id="editingEndMinute" name="editingEndMinute" class="widthallocationcontrolstime" aria-labelledby="editingEnd-label"></select>
          </div>
        </div>
      </div>
      <hr>
      <div id="rotanewedittable" class="smalltable bluetable allocationtable-layout" role="presentation">
        <div class="allocationtable-row allocationtable-row-2">
          <div class="allocationtable-cell allocationtable-label"><label for="workTimeDirectiveOptOut">Work Time Directive Opt Out
            </label></div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="workTimeDirectiveOptOut" name="workTimeDirectiveOptOut" type="checkbox">
          </div>
        </div>
        <div class="allocationtable-row allocationtable-row-2">
          <div class="allocationtable-cell allocationtable-label"><label for="checkOverSixDaysWorked"> Check Over Six Days Worked
            </label></div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="checkOverSixDaysWorked" name="checkOverSixDaysWorked" type="checkbox">
          </div>
        </div>
        <div class="allocationtable-row allocationtable-row-2" style="display:none;">
          <div class="allocationtable-cell allocationtable-label"><label for="checkOverFiveDaysWorked"> Check Over Five Days Worked
            </label></div>
          <div class="allocationtable-cell allocationtable-value">
            <input id="checkOverFiveDaysWorked" name="checkOverFiveDaysWorked" type="checkbox" value="0">
          </div>
        </div>
      </div><hr>';

      return $html;
    }

    private function allocationTabHtml($controlhideinput) {
      $html = $this->allocationTabHtmlPre();
      $html .= '<div id="rotanewedittable" class="smalltable bluetable allocationtable-layout" role="presentation">
          <div class="allocationtable-row allocationtable-row-4">
            <div class="allocationtable-cell allocationtable-label">
              <label for="locks"> Locks</label>
            </div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="locks" name="locks" type="checkbox">
            </div>
            <div class="allocationtable-cell allocationtable-label">
              <label for="locksStart"> Locks Start</label><span id="Addlabelstartdate" class="required">*</span>
            </div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="locksStart" name="locksStart" type="text" maxlength="3" class="widthallocationcontrols">
            </div>
          </div>
          <div class="allocationtable-row allocationtable-row-4">
            <div class="allocationtable-cell allocationtable-label"><label for="locksWeekataTime"> Locks Week At a Time
              </label></div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="locksWeekataTime" name="locksWeekataTime" type="checkbox">
            </div>
            <div class="allocationtable-cell allocationtable-label"><label for="locksEnd"> Locks End
              </label><span id="Addlabelstartdate" class="required">*</span></div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="locksEnd" name="locksEnd" type="text" maxlength="3" class="widthallocationcontrols">
            </div>
          </div>
        </div>
        <hr>
        <div id="rotanewedittable" class="smalltable bluetable allocationtable-layout" role="presentation">
          <div class="allocationtable-row allocationtable-row-4">
            <div class="allocationtable-cell allocationtable-label">
              <label for="signIn"> Sign-In</label>
            </div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="signIn" name="signIn" type="checkbox">
            </div>
            <div class="allocationtable-cell allocationtable-label">
              <label for="signInDays"> Sign-In Days</label>
              <span id="Addlabelstartdate" class="required">*</span>
            </div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="signInDays" name="signInDays" type="text" maxlength="3" class="widthallocationcontrols">
            </div>
          </div>
          <div class="allocationtable-row allocationtable-row-2">
            <div class="allocationtable-cell allocationtable-label"><label for="allowInBuilding"> Allow In Building Sign-in
              </label></div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="allowInBuilding" name="allowInBuilding" maxlength="3" type="checkbox">
            </div>
          </div>
        </div>
        <hr>
        <div id="editYearlySettings" class="smalltable bluetable allocationtable-layout" role="presentation">
          <div class="allocationtable-row allocationtable-row-4">
            <div class="allocationtable-cell allocationtable-label">
              <label for="showEditYearly"> Show Edit Yearly</label>
            </div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="showEditYearly" name="showEditYearly" type="checkbox">
            </div>
            <div class="allocationtable-cell allocationtable-label">
              <label for="restrictDeleteDuty"> Restrict Delete Duty</label>
            </div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="restrictDeleteDuty" name="restrictDeleteDuty" type="checkbox">
            </div>
          </div>
          <div class="allocationtable-row allocationtable-row-2">
            <div class="allocationtable-cell allocationtable-label"><label for="restrictApplyRota"> Restrict Apply Rota Pattern</label></div>
            <div class="allocationtable-cell allocationtable-value">
              <input id="restrictApplyRota" name="restrictApplyRota" type="checkbox">
            </div>
          </div>
        </div>
        <hr>
        <div id="rotanewedittable" class="smalltable bluetable allocationtable-layout" role="presentation">
            <div class="allocationtable-row allocationtable-row-2">
              <div class="allocationtable-cell allocationtable-label">
                <label for="hasGridChecks"> Show Grid Checks</label>
              </div>
              <div class="allocationtable-cell allocationtable-value">
                <input id="hasGridChecks" name="hasGridChecks" type="checkbox">
              </div>
            </div>
            <div class="allocationtable-row allocationtable-row-2">
              <div class="allocationtable-cell allocationtable-label">
                <label for="restrictCopyDuty"> Restrict Copy Duty</label>
              </div>
              <div class="allocationtable-cell allocationtable-value">
                <input id="restrictCopyDuty" name="restrictCopyDuty" type="checkbox" checked="checked">
              </div>
            </div>
            <div class="allocationtable-row allocationtable-row-2">
              <div class="allocationtable-cell allocationtable-label">
                <label for="showProductionView"> Show Production View </label>
              </div>
              <div class="allocationtable-cell allocationtable-value">
                <input id="showProductionView" name="showProductionView" type="checkbox">
              </div>
            </div>
            <div class="allocationtable-row allocationtable-row-2">
              <div class="allocationtable-cell allocationtable-label">
                <label for="createDutyFromRota"> Allow Master Duties in Create Duty from Rota </label>
              </div>
              <div class="allocationtable-cell allocationtable-value">
                <input id="createDutyFromRota" name="createDutyFromRota" type="checkbox">
              </div>
            </div>
            <div class="allocationtable-row allocationtable-row-2">
              <div class="allocationtable-cell allocationtable-label">
                <label for="showJobsInWeeklyView"> Show Jobs In Weekly View </label>
              </div>
              <div class="allocationtable-cell allocationtable-value">
                <input id="showJobsInWeeklyView" name="showJobsInWeeklyView" type="checkbox">
              </div>
            </div>
            <div class="allocationtable-row allocationtable-row-2">
              <div class="allocationtable-cell allocationtable-label">
                <label for="allowOvertimeRequests"> Allow Overtime Requests </label>
              </div>
              <div class="allocationtable-cell allocationtable-value">
                <input id="allowOvertimeRequests" name="allowOvertimeRequests" type="checkbox">
              </div>
            </div>
            <div class="allocationtable-row allocationtable-row-2">
              <div class="allocationtable-cell allocationtable-label">
                <label for="colourWeek"> Colour cells on Weekly/Monthly Views</label>
              </div>
              <div class="allocationtable-cell allocationtable-value">
                <input id="colourWeek" name="colourWeek" type="checkbox">
              </div>
            </div>
            <div class="allocationtable-row allocationtable-row-full">
              <div class="allocationtable-cell allocationtable-fullwidth">
                <input name="AllocationsBack" id="AllocationsBack" type="button" value="Back">
                <input name="AllocationsSubmit" id="AllocationsSubmit" type="button" value="Next" ' . $controlhideinput . '>
                <!-- <input name="AllocationsNext" id="AllocationsNext" type="button" value="Next"> -->
                <input name="Allocationscancel" class="handcursorimageall cancelschteampopup" id="Allocationscancel" type="button" value="Cancel">
              </div>
            </div>
        </div>
      </div>';
      return $html;
    }

    private function miscellaneousTabHtml($controlhideinput) {
      $html = '<div id="miscellaneoustab" class="tabcontent" style="display: none;">
      <!-- Tab start -->
      <div id="miscellaneoustable" class="smalltable bluetable resetmargintopbottom misctable-layout" role="presentation">
          <div class="misctable-row misctable-row-2">
              <div class="misctable-cell misctable-label"><label for="defaultNumberweeksRotaPattern">Default Number of weeks in Rota Pattern <span id="Addlabelstartdate" class="required">*</span></label></div>
              <div class="misctable-cell misctable-value">
                  <input id="defaultNumberweeksRotaPattern" name="defaultNumberweeksRotaPattern" maxlength="2" type="text" class="widthpopupcontrols">
              </div>
          </div>
          
          <div class="misctable-row misctable-row-2">
              <div class="misctable-cell misctable-label"><label for="leaveSelectiveHide">Selectively Hide Leave </label></div>
              <div class="misctable-cell misctable-value">
                  <input id="leaveSelectiveHide" name="leaveSelectiveHide"  type="checkbox">
              </div>
          </div>
          <div class="misctable-row misctable-row-2">
              <div class="misctable-cell misctable-label"><label for="hasHandovers">Show Handovers </label></div>
              <div class="misctable-cell misctable-value">
                  <input id="hasHandovers" name="hasHandovers"  type="checkbox">
              </div>
          </div>
          <div class="misctable-row misctable-row-2">
              <div class="misctable-cell misctable-label"><label for="hasXmasPoints">Show Christmas Points</label></div>
              <div class="misctable-cell misctable-value">
                  <input id="hasXmasPoints" name="hasXmasPoints" type="checkbox">
              </div>
          </div>
          
          <div class="misctable-row misctable-row-2">
              <div class="misctable-cell misctable-label"><label for="staffAvailabilityReportStartDate">Staff Availability Report Start Date <span id="" class="required">*</span></label></div>
              <div class="misctable-cell misctable-value">
              <input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="staffAvailabilityReportStartDate"
              name="staffAvailabilityReportStartDate" required="required" class="widthpopupcontrols">
              </div>
          </div>
          <div class="misctable-row misctable-row-2">
              <div class="misctable-cell misctable-label"><label for="schEmail">eMail <br>(Use semi-colon to separate multiple entries)</label></div>
              <div class="misctable-cell misctable-value"><input type="text" autocomplete="off" id="schEmail" name="schEmail" class="widthpopupcontrols"></div>
          </div>
          
          <div class="misctable-row misctable-row-full">
              <div class="misctable-cell misctable-fullwidth">
                  <input name="miscellaneousBack" id="miscellaneousBack" type="button" value="Back" class="handcursorimageall" ' . $controlhideinput . '>
                  <input name="miscellaneoussubmit" id="miscellaneoussubmit" type="button" value="Save & close" class="handcursorimageall" ' . $controlhideinput . '>
                  <input name="miscellaneouscancel" class="handcursorimageall cancelschteampopup" id="miscellaneouscancel" type="button" value="Cancel">
              </div>
          </div>
      </div>
      </div>';

      return $html;
    }

    function schedulingTeamHtmlCall()
    {
        // Page Id / Form ID
        $pageid = 8;
        // Call User Permission function.
        $permissions = getUserRolePermissions($pageid);
        if ($permissions->cancreate == 0 && $permissions->canmodify == 0 ) {
            $controlhideinput = 'hidden';
            $controlhideinput2 = '';
        } else  if ($permissions->cancreate == 0 && $permissions->canmodify == 1 ) {
            $controlhideinput = '';
            $controlhideinput2 = 'hidden';
        } else {
            $controlhideinput = '';
            $controlhideinput2 = '';
        }
        echo '<script src="../../../js/schedulingTeam.js?v=' . time() . '"></script>';
        echo '<div id="adminuserstabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
				<ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
				<li role="tab" tabindex="0" class="ui-tabs-tab ui-corner-top ui-state-default  ui-state-hover  ui-state-focus ui-state-active" aria-controls="scheduling" aria-labelledby="ui-id-1" aria-selected="true" aria-expanded="true" onclick="javascript:showSchedulingTeamUI()";><a href="#scheduling" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-1">Scheduling Team</a></li>';
				if($this->hasProductionGroupAccess){
          echo	'<li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="prodviewgroups" aria-labelledby="ui-id-4" aria-selected="false" aria-expanded="false" onclick="javascript:ShowProductionViewGroupFilters(0)";><a href="#prodviewgroups" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-2">Production View Groups</a></li>
                  <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="christmas" aria-labelledby="ui-id-1" aria-selected="false" aria-expanded="false" onclick="javascript:showTeamXmasPoint()";><a href="#christmas" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-3">Christmas Points</a></li>
				          <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="extra" aria-labelledby="ui-id-3" aria-selected="false" aria-expanded="false" onclick="javascript:showSchedulingTeamExtraXmasPointUI()";><a href="#extra" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-4">Extra Christmas Points</a></li>
              ';
        }
        echo '</ul></div>';
        
        echo '<div id="Searchfield">
                <div class="fieldsection">
                    <div class="parant_div_1 headertextallpages  main-heading" id="parant_div_1">
                    <div class="HeaderTitle"><h1 style="text-align: center;">Scheduling Teams</h1></div>';
                    if($permissions->cancreate) { 
                      echo '<div>Click on "Add New" to add a new Scheduling Team</div>';
                      }
                    if($permissions->canmodify) { 
                      echo '<div>Click on "Edit" icon to edit a Scheduling Team setup in action column</div>';
                      }
                    echo '<div>Click on "History" icon to view all history of a scheduling team in action column</div>';
                    if($permissions->cancreate) { 
                    echo '<button type="button" id="createschteambtn" class="btn-class addbtn m-30"><img src="../../../images/button_add.png" class="add-btn">&emsp;Add New</button>';
                    }
                  
                    echo '</div>
                </div>
                <section>
                        <div class="schedulingTeamContainer ui-widget ui-widget-content" >
                            <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="schedulingTeamDetails"
                                style="width: 100%;">
                                <thead>';
                          echo  $this->listingHtml();
                          echo  '</thead>
                                <tbody class="context-menu-one">
 
                                </tbody>
                            </table>
                        </div>
                </section>
            </div>
            
            <!-- Trigger/Open The Modal -->
            <div id="myModalSchedTeam" class="modal schTeamOverlay">
            <!-- Modal content -->
                <div class="modal-content popupwidth-60"><span id="aligncloseschteam" class="closebox">&times;</span>
                    <div id="headerschteam" class="greyborderpopup"></div>
                    <!--Model content-->
                    
                    <!--new tabbs-->
                    <div id="admindeptstabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content first-tab">';
                        echo $this->addTeamTabs();
                        echo $this->identityTabHtml($controlhideinput, $controlhideinput2);                        
                        echo $this->allocationTabHtml($controlhideinput);  
                        echo $this->miscellaneousTabHtml($controlhideinput);  
                    echo '</div>
                </div>
            </div>
            <!--new tabs ends-->
<!--*************************************************-->
            </div>
        </div>'
		;
							
    }

 /*
    * @Description : Call the Extra Xmas Point by Scheduling Team .
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : String data
    */
    function extraXmasPointPopupHTML($scheduledPeopleLists,$param){
    
      $extraXmasPoint ='';
      $year = $param['extraxmaspoint'] == 0 ? " ":$param['extraXmasPointDetails']['year'];
      $points = $param['extraxmaspoint'] == 0 ? " ":$param['extraXmasPointDetails']['points'];
      $notes = $param['extraxmaspoint'] == 0 ? " ":$param['extraXmasPointDetails']['notes'];
            $extraXmasPoint.='<link rel="stylesheet" href="../../styles/schedulingTeamUI.css">';      $extraXmasPoint.='<form id="newaddxmaspoint">
      <div class="xmaspointtable-layout">
      
      <!-- Header row -->
      <div class="xmaspointtable-row xmaspointtable-row-full">
      <div class="xmaspointtable-cell xmaspointtable-header">'.$param['title'].' Additional Christmas Points</div>
      </div>
      
      <!-- Person row -->
      <div class="xmaspointtable-row">
      <div class="xmaspointtable-cell xmaspointtable-label"><label>Person</label></div>
      <div class="xmaspointtable-cell xmaspointtable-value">';
      if($param['extraxmaspoint'] == 0){
        $extraXmasPoint.='<select class="chosen-select" name="userid">';
            foreach($scheduledPeopleLists as $userdata){
              $extraXmasPoint.='<option value="'.$userdata['ScheduledPersonID'].'">'.$userdata['Fullname'].'</option>';
            }
            $extraXmasPoint.='</select>';
        }
      else {
        $extraXmasPoint.= $param['extraXmasPointDetails']['Fullname'];
        $extraXmasPoint.='<input type="hidden" name="userid"  id = "userid" value="'.$param['extraXmasPointDetails']['ScheduledPersonID'].'">';
       
      }
      $extraXmasPoint.='</div>
      </div>
      
      <!-- Year row -->
      <div class="xmaspointtable-row">
      <div class="xmaspointtable-cell xmaspointtable-label"><label for="year">Year</label></div>
      <div class="xmaspointtable-cell xmaspointtable-value"><input type="text" id="year" name="year" class="xmaspointtable-input" value="'.$year.'"></div>
      </div>
      
      <!-- Points row -->
      <div class="xmaspointtable-row">
      <div class="xmaspointtable-cell xmaspointtable-label"><label for="points">Points</label></div>
      <div class="xmaspointtable-cell xmaspointtable-value"><input type="text" id="points" name="points" class="xmaspointtable-input" value="'.$points.'"></div>
      </div>
      
      <!-- Notes row -->
      <div class="xmaspointtable-row">
      <div class="xmaspointtable-cell xmaspointtable-label"><label for="notes">Notes</label></div>
      <div class="xmaspointtable-cell xmaspointtable-value"><textarea id="notes" rows="2" name="notes" class="xmaspointtable-textarea" cols="40">'.$notes.'</textarea></div>
      </div>
      
      <!-- Submit row -->
      <div class="xmaspointtable-row xmaspointtable-row-buttons">
      <div class="xmaspointtable-cell xmaspointtable-label"></div>
      <div class="xmaspointtable-cell xmaspointtable-value"><input type="submit" value="'.$param['button'].'" name="addxtrapointubmit" id="js_addxtrapointubmit" class="xmaspointtable-button"></div>
      </div>
      
      </div>
      <input type="hidden" name="extraxmaspoint"  id = "extraxmaspoint" value="'.$param['extraxmaspoint'].'">
      <input type="hidden" name="teamid"  id = "teamid" value="'.$param['teamid'].'">
      <input type="hidden" value="'.$param['button'].'" name="js_xtrapointubmit" id="js_xtrapointubmit">
      </form>';

      return $extraXmasPoint;
    }


    /*
    * @Description : Call the Extra Xmas Point by Scheduling Team .
    * @access : Public
    * @global : Not Applicable
    * @param  : N/A
    * @return : String data
    */
public function extraXmasPointListingHTML($scheduledTeamList,$isSysAdmin){
  
  $pageid = 13;
  // Call User Permission function.
  $activeclass = '';
  $permissions = getUserRolePermissions($pageid);
  //set the permissions
  if($permissions->highestrole > 2){
    if ($permissions->cancreate == 0 ) {
        $activeclass = 'activeclass';
    }
  }
  $extraXmasLists ='';
    if($this->hasProductionGroupAccess) {
        $extraXmasLists .= '<script src="../../../js/teamXmasPoint.js"></script>
    <div id="adminuserstabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
		<ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
		<li role="tab" tabindex="0" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="scheduling" aria-labelledby="ui-id-1" aria-selected="true" aria-expanded="true" onclick="javascript:showSchedulingTeamUI()";><a href="#scheduling" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-1">Scheduling Team</a></li>
		<li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="prodviewgroups" aria-labelledby="ui-id-4" aria-selected="false" aria-expanded="false" onclick="javascript:ShowProductionViewGroupFilters(0)";><a href="#prodviewgroups" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-2">Production View Groups</a></li>
    <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="christmas" aria-labelledby="ui-id-1" aria-selected="false" aria-expanded="false" onclick="javascript:showTeamXmasPoint()";><a href="#christmas" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-3">Christmas Points</a></li>
		<li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus ui-state-active" aria-controls="extra" aria-labelledby="ui-id-3" aria-selected="false" aria-expanded="false" onclick="javascript:showSchedulingTeamExtraXmasPointUI()";><a href="#extra" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-4">Extra Christmas Points</a></li>
		
		</ul>';
    } else {
            $extraXmasLists .='<script src="../../../js/teamXmasPoint.js"></script>
    <div id="adminuserstabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
		<ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
		<li role="tab" tabindex="0" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="scheduling" aria-labelledby="ui-id-1" aria-selected="true" aria-expanded="true" onclick="javascript:showSchedulingTeamUI()";><a href="#scheduling" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-1">Scheduling Team</a></li>
		<li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="christmas" aria-labelledby="ui-id-1" aria-selected="false" aria-expanded="false" onclick="javascript:showTeamXmasPoint()";><a href="#christmas" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-2">Christmas Points</a></li>
		<li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus ui-state-active" aria-controls="extra" aria-labelledby="ui-id-3" aria-selected="false" aria-expanded="false" onclick="javascript:showSchedulingTeamExtraXmasPointUI()";><a href="#extra" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-3">Extra Christmas Points</a></li>
		</ul>';

        }
    $extraXmasLists .= '</div>
   
  <input type="hidden"  name = "js_sysadmin" value="'.$isSysAdmin  .'" id="js_sysadmin">    
  <input type="hidden"  name = "js_create" value="" id="js_create">    
                          <div id="searchScheduleperson">
                          <div class="tableheadersmall medtextboldcentr " style="position: relative; width:100%">
                            <div class="main-heading"><br><br><h1><b>Extra Christmas Points</b> </h1>
                            <div class="'.$activeclass.'"><b>You can add discretionary points for people here.</b></div><br><br></div>
                          </div>
                              <div class="fieldsection m-1">
                                  <div class="parant_div_1" id="parant_div_1">
                                      <div class="no-border additonaltemtd ">
                                          <button class=" notclickable nonscheduledbtn  buttonDisabled btn-class addbtn '.$activeclass.'" id="js_addxmaspointuser" >
                                          <img src="images/button_add.png" alt="" >&nbsp;Add Extra Christmas Points
                                            </button>
                                              <div class="fields m-tb" id="">
                                                  <label for="hometeam"> Scheduling Team</label>
                                                  <select name="hometeam" id="js_teamdropdown" class="teams-seclect">
                                                      <option data-divisionid ="0"  value="">Select The Team</option>';
                                                    foreach ($scheduledTeamList as $team) {
														if($team['hasXmasPoints'])
														  {
															$extraXmasLists.='<option data-divisionid ="'.$team['DivisionId'].'" value="'. $team['TeamID'].'">'.$team['TeamName'].'</option>';
														  }
                                                      } 
                                                      $extraXmasLists.='</select>
                                              </div>
                                      </div>
                                </div>
                          </div>

                          <div class="tables">
                              <div class="">
                                  <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="extraxmaspoinlists"
                                        style="width: 100%;" role="grid" >
                                      <thead>
                                      
                                      <th>Name</th>
                                      <th>Year </th>
                                      <th>Points</th>
                                      <th>Notes </th>
                                      <th>Actions</th>
                                      </thead>
                                      <tbody class="context-menu-one">
                                      <tr>

                                          <td></td>
                                          <td></td>
                                          <td></td>
                                          <td class="no-record">Please Apply filter to display records.</td>
                                          <td></td>
                                          

                                      </tr>
                                        </tbody>
                                  </table>
                              </div>
                          </div>
                        </div>';
                    
  return $extraXmasLists;
} 
   
}
