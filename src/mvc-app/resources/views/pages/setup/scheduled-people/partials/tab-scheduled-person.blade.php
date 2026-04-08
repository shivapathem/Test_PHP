<div id="tabs-1" role="tabpanel" aria-labelledby="tab-scheduled-person" style="font-family: Verdana, Arial, sans-serif;">
  {{-- DISPLAY NAME FIELDS --}}
  <div class="fields stfform Leftfld" aria-label="Display Name"
    style="display: flex; align-items: center; justify-content: space-between; width: 100%; max-width: 100%; box-sizing: border-box; margin-top: 10px; margin-bottom: 0;">
    <div style="display: flex; align-items: center; flex-wrap: wrap;">
      <label for="dispFirstName" style="margin-top:0; font-size: 13px; font-weight: normal;"> Display First Name <span class="required"
          aria-hidden="true">*</span></label>
      <input type="text" placeholder="Enter Text" class="dispNamenput" id="dispFirstName" name="dispFirstName"
        minlength="2" maxlength="25" value="{{ old('dispFirstName', $person['display_first_name'] ?? '') }}"
        style="margin-top:0; font-size: 13px; font-family: Verdana, Arial, sans-serif;">
      <label class="ms-3" for="dispLastName" style="margin-top:0; font-size: 13px; font-weight: normal;"> Display Last Name <span class="required"
          aria-hidden="true">*</span></label>
      <input type="text" placeholder="Enter Text" class="displastNamenput" id="dispLastName" name="dispLastName"
        minlength="2" maxlength="25" value="{{ old('dispLastName', $person['display_last_name'] ?? '') }}"
        style="margin-top:0; font-size: 13px; font-family: Verdana, Arial, sans-serif;">
    </div>
    @if(($mode ?? 'create') !== 'view')
      <div style="margin-left: auto;">
        <button id="saveSchedulePerson" class="btn-class btn-staff"
          style="height: 34px; width: auto; padding: 0 16px; margin: 0; font-size: 13px; font-weight: normal; font-family: Verdana, Arial, sans-serif;">
          {{ ($mode ?? 'create') === 'edit' ? 'Update Person' : 'Create Person' }}
        </button>
      </div>
    @endif
  </div>

  <div class="hometeam ht schprsn" style="margin-top: 0 !important;">
    <div class="float-container" style="gap: 2px;">
      {{-- HOME SCHEDULING TEAM --}}
      <div class="float-child schTeamDetails" style="padding: 4px; margin-right: 2px;">
        <h1 style="font-weight: bold; background-color: #D0D0D0; border-radius: 4px; padding: 8px; font-size: 13px;">Home Scheduling Team</h1>

        <div class="fields" style="margin-bottom: 4px;">
          <label for="hometeam" style="font-size: 13px; font-weight: normal;"> Team Name <span class="required" aria-hidden="true">*</span></label>
           <select name="hometeam" id="hometeam" style="font-size: 13px; font-family: Verdana, Arial, sans-serif;">
            </select> 
            <input type="hidden" id="schedulingTeamHistoryHomeTeamFilterFlag" name="schedulingTeamHistoryHomeTeamFilterFlag" value="-1">
          <input type="hidden" id="SPTeamID" value="">
          <input type="hidden" id="OriginalHomeTeamID" value="">
          <input type="hidden" id="canCreateFreelancer" value="{{ auth()->user()->can('createFreelancer', 'scheduled-people') ? '1' : '0' }}">
          <input type="hidden" id="canCreateStaff" value="{{ auth()->user()->can('createStaff', 'scheduled-people') ? '1' : '0' }}">
        </div>

        <div class="fields" style="margin-bottom: 4px;">
          <label for="startdate" style="font-size: 13px; font-weight: normal;"> Start Date <span class="required" aria-hidden="true">*</span></label>
          <input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="startdate" name="startdate"
            value="{{ old('startdate', $person['start_date'] ?? '') }}" style="font-size: 13px; font-family: Verdana, Arial, sans-serif;">
          <input type="hidden" id="original-home-date" value="">
        </div>

        <div class="fields" style="margin-bottom: 4px;">
          <label for="sortcode" style="font-size: 13px; font-weight: normal;">Sort Code</label>
          <input type="text" id="sortcode" maxlength="20" placeholder="Enter Text"
            value="{{ old('sortcode', $person['sort_code'] ?? '') }}" style="font-size: 13px; font-family: Verdana, Arial, sans-serif;">
        </div>

        <div class="fields" style="margin-bottom: 4px;">
          <input type="checkbox" id="defaultbgcolour" value="1" @checked(old('defaultbgcolour', $person['default_bg_colour'] ?? 0))>
          <label for="defaultbgcolour" style="font-size: 13px; font-weight: normal;">Default Background Colour</label>
        </div>

        <div class="fields" style="margin-bottom: 4px;">
          <input type="checkbox" id="additionalleave" value="1" @checked(old('additionalleave', $person['additional_leave'] ?? 0))>
          <label for="additionalleave" style="font-size: 13px; font-weight: normal;">Additional Leave</label>
        </div>

        <div class="rowsbtns colorChoose" style="margin-bottom: 4px;">
          <div class="fields-color">
            <input id="hometeambackcolour" type="text" class="btn-class-color btn-staff-color" aria-labelledby="label-hometeambackcolour"
              value="{{ old('hometeambackcolour', '#eeeeee') }}">
            <label id="label-hometeambackcolour" for="hometeambackcolour" style="font-size: 13px; font-weight: normal;">Background Colour</label>
          </div>
        </div>

        <div class="rowsbtns colorChoose">
          <div class="fields-color">
            <input id="hometeamfontcolour" type="text" class="btn-class-color btn-staff-color" aria-labelledby="label-hometeamfontcolour"
              value="{{ old('hometeamfontcolour', '#000000') }}">
            <label id="label-hometeamfontcolour" for="hometeamfontcolour" style="font-size: 13px; font-weight: normal;">Font Colour</label>
          </div>
        </div>
      </div>

      {{-- ADDITIONAL SCHEDULING TEAMS --}}
      <div class="float-child additionalTeam" style="padding: 4px; margin-left: 2px;">
        <div class="additionalTeamHeader">
          <h1 style="font-weight: bold; background-color: #D0D0D0; border-radius: 4px; padding: 8px; font-size: 13px;">Additional Scheduling Teams</h1>
          <input class="btn-class btn-staff" type="button" id="myBtnRight" onclick="openModalPopup(null,null,'myBtn')"
            value="Add New Team" style="font-size: 13px; font-weight: normal; font-family: Verdana, Arial, sans-serif;">
        </div>
        <div class="additionalteamBox">
          <div id="scrollable-additionalteam">
            <table id="additionalteamgrid" class="tables tablesmall bluetable">
              <thead>
                <tr>
                  <th>Team Name</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Sort Code</th>
                  <th>Available</th>
                  <th>Display in View Screens</th>
                  <th>Type</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {{-- Populated by JS later --}}
              </tbody>
            </table>
          </div>

          <div id="teamerrorerror" class="visually-hidden" role="alert"></div>
        </div>
      </div>
    </div>

    {{-- NOTES --}}
    <div class="textAreaBlock" style="margin-top: 2px; display: flex; gap: 2px;">
      <div class="fields txtar textarea ta1" style="margin-top: 1px;">
        <label for="adminnotes" style="font-size: 13px; font-weight: normal; margin-bottom: 0;">Admin Notes <span class="text-muted" style="font-style: italic; font-size: 12px;">(Not visible to scheduled person)</span></label>
        <textarea id="adminnotes" rows="4" maxlength="500"
          placeholder="Enter large text" style="font-size: 13px; font-weight: normal; padding: 5px;">{{ old('adminnotes', $person['admin_notes'] ?? '') }}</textarea>
      </div>

      <div class="fields txtar textarea ta2" style="margin-top: 1px;">
        <label for="fwanotes" style="font-size: 13px; font-weight: normal; margin-bottom: 0;">Scheduling Notes <span class="text-muted" style="font-style: italic; font-size: 12px;">(Not visible to scheduled person)</span></label>
        <textarea id="fwanotes" rows="4" maxlength="500"
          placeholder="Enter large text" style="font-size: 13px; font-weight: normal; padding: 5px;">{{ old('fwanotes', $person['fwa_notes'] ?? '') }}</textarea>
      </div>
    </div>
  </div>
</div>