{{-- Inline style to guarantee overlay centers perfectly, bypassing browser CSS cache --}}
<style>
  .sp-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1040;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  .sp-modal-overlay[style*="display: block"] {
    display: flex !important;
  }
</style>

{{-- Add / Edit Team Modal --}}
<div id="addTeamModal" class="sp-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="addTeamTitle"
  style="display:none;">
  <div class="sp-modal-dialog" role="document">

    {{-- Header --}}
    <div class="sp-modal-header">
      <h2 id="addTeamTitle" class="sp-modal-title">Add Scheduling Team</h2>
      <button type="button" class="sp-modal-close" aria-label="Close dialog" onclick="$('#addTeamModal').hide();">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="sp-modal-body">
      {{-- Error message container --}}
      <div id="addTeamError" class="alert alert-danger visually-hidden" role="alert"></div>      
      <div class="sp-form-grid" aria-labelledby="addTeamTitle">

        {{-- Team Name --}}
        <label id="lbl-addteam" class="sp-form-label" for="addteam">
          Team Name <span class="required" aria-hidden="true">*</span>
        </label>
        <div class="sp-form-control">
          <select id="addteam" aria-labelledby="lbl-addteam" aria-required="true"></select>
        </div>

        {{-- Start Date --}}
        <label id="lbl-addteamstartdate" class="sp-form-label" for="addteamstartdate">
          Start Date <span class="required" aria-hidden="true">*</span>
        </label>
        <div class="sp-form-control">
          <input type="text" id="addteamstartdate" aria-labelledby="lbl-addteamstartdate" aria-required="true"
            placeholder="dd-mm-yyyy" autocomplete="off">
        </div>

        {{-- End Date --}}
        <label id="lbl-addteamenddate" class="sp-form-label" for="addteamenddate">
          End Date
        </label>
        <div class="sp-form-control">
          <input type="text" id="addteamenddate" aria-labelledby="lbl-addteamenddate" placeholder="dd-mm-yyyy"
            autocomplete="off">
        </div>

        {{-- Sort Code --}}
        <label id="lbl-addteamsortcode" class="sp-form-label" for="addteamsortcode">
          Sort Code
        </label>
        <div class="sp-form-control">
          <input type="text" id="addteamsortcode" aria-labelledby="lbl-addteamsortcode" maxlength="20"
            placeholder="Enter Text">
        </div>

        {{-- Default Background Colour (checkbox) --}}
        <label id="lbl-addteamdefaultbgcolour" class="sp-form-label" for="addteamdefaultbgcolour">
          Default Background Colour
        </label>
        <div class="sp-form-control">
          <input type="checkbox" id="addteamdefaultbgcolour" aria-labelledby="lbl-addteamdefaultbgcolour">
        </div>

        {{-- Background Color (colour picker) --}}
        <label id="lbl-addteamBackcolor" class="sp-form-label" for="addteamBackcolor">
          Background Colour
        </label>
        <div class="sp-form-control">
          <input type="text" id="addteamBackcolor" class="btn-class-color btn-staff-color" aria-labelledby="lbl-addteamBackcolor" value="#cccccc">
        </div>

        {{-- Font Color (colour picker) --}}
        <label id="lbl-addteamfontcolor" class="sp-form-label" for="addteamfontcolor">
          Font Colour
        </label>
        <div class="sp-form-control">
          <input type="text" id="addteamfontcolor" class="btn-class-color btn-staff-color" aria-labelledby="lbl-addteamfontcolor" value="#000000">
        </div>

        {{-- Is Available (checkbox) --}}
        <label id="lbl-addteamisavailable" class="sp-form-label" for="addteamisavailable">
          Is Available
        </label>
        <div class="sp-form-control">
          <input type="checkbox" id="addteamisavailable" aria-labelledby="lbl-addteamisavailable">
        </div>

        {{--  Do Not Display in View Screens (checkbox) --}}
        <label id="lbl-do-not-display-in-view-screen" class="sp-form-label" for="do-not-display-in-view-screen">
          Do Not Display in View Screens
        </label>
        <div class="sp-form-control">
          <input type="checkbox" id="do-not-display-in-view-screen" aria-labelledby="lbl-do-not-display-in-view-screen">
        </div>
        <input type="hidden" id="additional_team_type">
      </div>
    </div>

    {{-- Footer --}}
    <div class="sp-modal-footer modal-footer">
      <button class="btn-class btn-staff" type="button"
        onclick="ScheduledPeople && ScheduledPeople.saveAdditionalTeam ? ScheduledPeople.saveAdditionalTeam() : null">Save</button>
      <button class="btn-class btn-secondary" type="button" onclick="$('#addTeamModal').hide();">Cancel</button>
    </div>

  </div>
</div>

{{-- Staff Search and Attach Modal --}}
<div id="myModalUpdate" class="sp-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="staffSearchTitle"
  style="display:none;">
  <div class="sp-modal-dialog sp-modal-lg" role="document">

    {{-- Header --}}
    <div class="sp-modal-header">
      <h2 id="staffSearchTitle" class="sp-modal-title">Staff Details</h2>
      <button type="button" class="sp-modal-close" aria-label="Close dialog" onclick="$('#myModalUpdate').hide();">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="sp-modal-body">

      {{-- Search form (4-column grid: label | input | label | input) --}}
      <div class="sp-search-grid" role="search" aria-label="Staff search filters">

        <label id="lbl-js_staffNumber" class="sp-search-label" for="js_staffNumber">Staff Number</label>
        <div class="sp-search-input">
          <input autocomplete="off" class="ui-autocomplete-input" id="js_staffNumber" name="staff" type="text"
            aria-labelledby="lbl-js_staffNumber" placeholder="Staff Number">
        </div>

        <label id="lbl-js_staffNetworkId" class="sp-search-label" for="js_staffNetworkId">Network ID</label>
        <div class="sp-search-input">
          <input autocomplete="off" class="ui-autocomplete-input" id="js_staffNetworkId" name="networkID" type="text"
            aria-labelledby="lbl-js_staffNetworkId" placeholder="Network ID">
        </div>

        <label id="lbl-js_staffForeName" class="sp-search-label" for="js_staffForeName">Forename</label>
        <div class="sp-search-input">
          <input autocomplete="off" class="ui-autocomplete-input" id="js_staffForeName" name="Forename" type="text"
            aria-labelledby="lbl-js_staffForeName" placeholder="Forename">
        </div>

        <label id="lbl-js_staffSurName" class="sp-search-label" for="js_staffSurName">Surname</label>
        <div class="sp-search-input">
          <input autocomplete="off" class="ui-autocomplete-input" id="js_staffSurName" name="Surname" type="text"
            aria-labelledby="lbl-js_staffSurName" placeholder="Surname">
        </div>

        {{-- Action buttons span the right two columns --}}
        <div class="sp-search-actions">
          <button id="js_find" class="buttonDisabled" disabled>Find</button>
          <button id="js_cancel" style="display:none">Cancel</button>
        </div>

      </div>

      {{-- Search Results Table --}}
      <div class="tables gray-outer">
        <div class="scrollable">
          <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="staffDetailsConfiglist"
            style="width: 100%;" role="grid" aria-describedby="joblisting_info" aria-label="Staff search results">
            <thead>
              <tr class="thstyle">
                <th scope="col">Staff Number</th>
                <th scope="col">Network ID</th>
                <th scope="col">Forename</th>
                <th scope="col">Surname</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody class="context-menu-one" id="staffDetailsConfiglistBody">
              <tr>
                <td></td>
                <td></td>
                <td colspan="3">Please apply the filter.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </div>
</div>

{{-- Staff Edit Modal (for updating staff details) --}}
<div id="staffEditModal" class="sp-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="staffEditTitle"
  style="display:none;">
  <div class="sp-modal-dialog" role="document">

    {{-- Header --}}
    <div class="sp-modal-header">
      <h2 id="staffEditTitle" class="sp-modal-title">Edit Staff Details</h2>
      <button type="button" class="sp-modal-close" aria-label="Close dialog" onclick="$('#staffEditModal').hide();">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    {{-- Body --}}
    <div class="sp-modal-body">
      <div class="sp-form-grid" aria-labelledby="staffEditTitle">

        <label id="lbl-staff_firstname" class="sp-form-label" for="staff_firstname">First Name</label>
        <div class="sp-form-control">
          <input type="text" id="staff_firstname" aria-labelledby="lbl-staff_firstname" placeholder="First Name">
        </div>

        <label id="lbl-staff_surname" class="sp-form-label" for="staff_surname">Surname</label>
        <div class="sp-form-control">
          <input type="text" id="staff_surname" aria-labelledby="lbl-staff_surname" placeholder="Surname">
        </div>

        <label id="lbl-staff_midname" class="sp-form-label" for="staff_midname">Middle Name</label>
        <div class="sp-form-control">
          <input type="text" id="staff_midname" aria-labelledby="lbl-staff_midname" placeholder="Middle Name">
        </div>

        <label id="lbl-staff_prefname" class="sp-form-label" for="staff_prefname">Preferred Name</label>
        <div class="sp-form-control">
          <input type="text" id="staff_prefname" aria-labelledby="lbl-staff_prefname" placeholder="Preferred Name">
        </div>

        <label id="lbl-staff_designation" class="sp-form-label" for="staff_designation">Designation</label>
        <div class="sp-form-control">
          <input type="text" id="staff_designation" aria-labelledby="lbl-staff_designation" placeholder="Designation">
        </div>

        <label id="lbl-staff_netlogin" class="sp-form-label" for="staff_netlogin">Network ID</label>
        <div class="sp-form-control">
          <input type="text" id="staff_netlogin" aria-labelledby="lbl-staff_netlogin" placeholder="Network ID">
        </div>

      </div>
    </div>

    {{-- Footer --}}
    <div class="sp-modal-footer modal-footer">
      <button class="btn-class btn-staff" type="button" id="saveStaffDetails">Save</button>
      <button class="btn-class btn-secondary" type="button" onclick="$('#staffEditModal').hide();">Cancel</button>
    </div>

  </div>
</div>