<div class="headertextallpages main-heading scheduledpersonRemoveH">
  <div class="main-toolbar" role="toolbar" aria-label="Scheduled Person Actions"
    style="align-items:center; justify-content:space-between; padding:0;">
    
    <div class="parant_div_1 headertextallpages  main-heading">
            {{-- Button is always shown here - JavaScript will handle show/hide based on API permissions --}}
            <h1 id="span_scheduled_person" class="h5" style="margin:0; padding: 5px 10px;">
      @if(($mode ?? 'search') === 'search') Search Scheduled Person
      @elseif(($mode ?? 'create') === 'create') Add New Scheduled Person Details
      @elseif(($mode ?? '') === 'edit') Edit Scheduled Person Details
      @else View Scheduled Person Details
      @endif
    </h1>
          <div>
            @if(($mode ?? 'search') === 'search')
            @can('createAny', 'scheduled-people')
            <button class="btn-class addbtn m-30" id="js_addnewbutton">
                <img src="/mvc-app/public/images/button_add.png" alt="Add Button">&nbsp;Add New
            </button>
            @endcan
          </div>
            @endif
        </div>
  </div>

  {{-- Hidden fields used by legacy JS --}}
  <input id="getpersonid" type="hidden" value="{{ $person['id'] ?? 0 }}" />
  <input id="getstaffid" type="hidden" value="" />
  <input id="oldscheduledPersonID" type="hidden" value="" />
  <input id="newpersonid" type="hidden" value="{{ $person['id'] ?? '' }}" />
  <input id="selectedteamid" type="hidden" value="{{ $preSelectedTeamId ?? $person['home_team_id'] ?? '' }}" />
  <input id="useractiontype" type="hidden" value="{{ $mode ?? 'create' }}" />
  <input id="homestartdateHide" type="hidden" />
  <input id="homestartdateHideNewTeam" type="hidden" />
  <input id="SPTeamID" name="SPTeamID" type="hidden" value="0" />
  <input id="OriginalHomeTeamID" type="hidden" value="0" />
  <input id="addteamhiddenarray" type="hidden" />
  <input id="hometeamhidden" type="hidden" />
</div>
