<div id="schedulePersonTab">
  <div class="toolbar-actions" style="gap:.5rem; align-items:center;">
      @if(($mode ?? 'search') !== 'search')
      <button class="back-button" type="button" onclick="goBack($('#selectedteamid').val() || 0, '')" style="font-size: 14px;">
        <i class="fa fa-arrow-left"></i>
        &nbsp;Go Back To Search Scheduled Person
      </button>
      @endif

      @if(($mode ?? 'create') !== 'view')
        {{-- Save button moved to tab-scheduled-person.blade.php --}}
      @endif
    </div>
  <ul class="first-tab" role="tablist" aria-label="Scheduled Person Tabs" style="font-family: Verdana, Arial, sans-serif;">
    <li><a href="#tabs-1" id="tab-scheduled-person" role="tab" aria-controls="tabs-1">Scheduled Person</a></li>
    <li class="staff_detail buttonDisabled"><a href="#tabs-2" id="tab-staff-details" role="tab" aria-controls="tabs-2">Staff Details</a></li>
    <li class="schedule-person-team buttonDisabled"><a href="#tabs-3" id="tab-team-history" role="tab" aria-controls="tabs-3">Scheduling Team History</a></li>
    <li class="contract_history buttonDisabled"><a href="#tabs-4" id="tab-contract-history" role="tab" aria-controls="tabs-4">Contract History</a></li>
  </ul>

  {{-- Tab 1: Scheduled Person --}}
@include('pages.setup.scheduled-people.partials.tab-scheduled-person')

{{-- Tab 2: Staff Details --}}
@include('pages.setup.scheduled-people.partials.tab-staff-details')

{{-- Tab 3: Scheduling Team History --}}
@include('pages.setup.scheduled-people.partials.tab-team-history')

{{-- Tab 4: Contract History --}}
@include('pages.setup.scheduled-people.partials.tab-contract-history')

</div>