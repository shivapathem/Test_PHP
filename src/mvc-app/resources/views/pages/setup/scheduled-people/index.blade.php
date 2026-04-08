@extends('layouts.default')

@section('content')

@push('style-css')
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/scheduled-people/schedulepeople.css') }}">
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/scheduled-people/create-scheduled-person.css') }}">
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/scheduled-people/contact-history.css') }}">
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/scheduled-people/modals.css') }}">
    <link rel="stylesheet" href="/mvc-app/public/css/scheduled-people/team-history.css">
    <link rel="stylesheet" href="/styles/spectrum.css">
  @endpush

  {{-- Top header with title + actions + hidden fields --}}
  @include('pages.setup.scheduled-people.partials.header')

  {{-- Tab List: Search Scheduled Person (shown in search mode) --}}
  @if(($mode ?? 'search') === 'search')
    @include('pages.setup.scheduled-people.partials.tab-list')
  @endif

  @if(($mode ?? 'search') !== 'search')
  {{-- Tabs wrapper open --}}
  @include('pages.setup.scheduled-people.partials.tabs-open')

  {{-- Tabs wrapper close --}}
  @include('pages.setup.scheduled-people.partials.tabs-close')
  @endif

  {{-- Page modals (e.g., Add Team) --}}
  @include('pages.setup.scheduled-people.partials.modals')

  @include('pages.setup.scheduled-people.partials.confirm-attach-dialog')
@endsection
{{-- Make all endpoints available to the JS --}}

@push('scripts')
@if(($mode ?? 'search') === 'search')
  <script>
    // Config for tab-list module
    window.__TabListConfig = {
      urls: {
        'index': '{{ route('setup.scheduled-people.index') }}',
        'search': '{{ route('setup.scheduled-people.search') }}',
        'permissions': '{{ route('setup.scheduled-people.permissions') }}',
        'create': '{{ route('setup.scheduled-people.create') }}'
      }
    };
  </script>
  <script src="/mvc-app/public{{ mix('js/scheduled-people/tab-list.js') }}" type="text/javascript"></script>
  @else
  <script src="/mvc-app/public{{ mix('js/scheduled-people/create-scheduled-person.js') }}" type="text/javascript"></script>
  <script src="/js/spectrum.js" type="text/javascript"></script>
  <script src="/mvc-app/public{{ mix('js/scheduled-people/staff-details.js') }}" type="text/javascript"></script>
  <script src="/mvc-app/public{{ mix('js/scheduled-people/tab-contract-history.js') }}" type="text/javascript"></script>
  <script src="/mvc-app/public{{ mix('js/scheduled-people/team-history.js') }}" type="text/javascript"></script>

  <script>
    $(function () {
      var mode = '{{ $mode ?? "create" }}';        // "create" | "edit"
      var personId = {{ (int) ($personId ?? 0) }};      // 0 in create

      // Main instance (unchanged)
      var ScheduledPeopleInstance = new ScheduledPeopleVar({
        mode: mode,
        personId: personId,
        urls: {
          'index': '{{ route('setup.scheduled-people.index') }}',
          'teamList': '{{ route('setup.scheduled-people.team-list') }}',
          'details': '{{ isset($personId) ? route('setup.scheduled-people.details', $personId) : '' }}',
          'teamHistory': '{{ isset($personId) ? route('setup.scheduled-people.team-history', $personId) : '' }}',
          'validateAdditionalTeam': '{{ route('setup.scheduled-people.validate-additional-team') }}',
          'store': '{{ route('setup.scheduled-people.store') }}',
          'update': '{{ isset($personId) ? route('setup.scheduled-people.update', $personId) : '' }}',
          'edit': '{{ isset($personId) ? route('setup.scheduled-people.edit', $personId) : '' }}',
          'create': '{{ route('setup.scheduled-people.create') }}',
        },
        // Callback to reload staff details after scheduled person is loaded
        onDetailsLoaded: function (newPersonId) {
          if (typeof staffDetails !== 'undefined' && newPersonId > 0) {
            staffDetails.personId = newPersonId;
            staffDetails.loadStaffDetails();
          }
          
          // Also update TeamHistory with the new person ID
          if (typeof teamHistory !== 'undefined' && newPersonId > 0) {
            teamHistory.updatePersonId(newPersonId);
          }
        }
      });

      // Initialize staffDetails with the current personId from the URL
      var staffDetails = new SearchStaff({
        personId: personId,
        urls: {
          // Search + autocomplete
          'staffSearch': '{{ route('setup.scheduled-people.staff-search') }}',
          'staffAutocomplete': '{{ route('setup.scheduled-people.staff-autocomplete') }}',

          // Get staff details (for loading existing staff in edit mode)
          'getStaff': '{{ isset($personId) ? route('setup.scheduled-people.getStaff', $personId) : '' }}',

          // Templates for create mode (use __ID__ placeholder)
          'staffAttachTemplate': '{{ url('mvc-app/setup/scheduled-people/attach/__ID__') }}',
          'staffShowTemplate': '{{ url('mvc-app/setup/scheduled-people/show/__ID__') }}',
          'staffDetachTemplate': '{{ url('mvc-app/setup/scheduled-people/detach/__ID__') }}',

          // Resolved URLs - use placeholder for create mode (personId will be replaced after scheduled person is created)
          'staffAttach': '{{ route('setup.scheduled-people.attach', ['id' => ':personId']) }}',
          'staffShow': '{{ isset($personId) ? route('setup.scheduled-people.show', $personId) : '' }}',
          'staffDetach': '{{ isset($personId) ? route('setup.scheduled-people.detach', $personId) : '' }}',
          'staffUpdate': '{{ isset($personId) ? route('setup.scheduled-people.updateStaff', $personId) : route('setup.scheduled-people.updateStaff', ['id' => ':personId']) }}'
        }
      });

      // Config for contract history module
      window.__ScheduledPeopleConfig = {
        personId: personId,
        urls: {
          contractHistory: '{{ route('setup.scheduled-people.contract-history') }}',
          contractHistoryPopup: '{{ route('setup.scheduled-people.contract-history-popup') }}',
          common: '{{ url('mvc-app/function-includes/common/common.php') }}'
        }
      };

      // Callback to update contract history after scheduled person is saved (for create mode)
      var originalOnDetailsLoaded = ScheduledPeopleInstance.onDetailsLoaded;
      ScheduledPeopleInstance.onDetailsLoaded = function(newPersonId) {
        // Call original callback if exists
        if (originalOnDetailsLoaded) {
          originalOnDetailsLoaded(newPersonId);
        }
        
        // Update contract history module with new person ID
        if (typeof TabContractHistoryInstance !== 'undefined' && newPersonId > 0) {
          TabContractHistoryInstance.setPersonId(newPersonId);
        }
      };
      // Initialize TeamHistory for the Scheduling Team History tab
      var teamHistory = new TeamHistory({
        personId: personId,
        urls: {
          'teamHistory': '{{ isset($personId) ? route('setup.scheduled-people.team-history', $personId) : '' }}',
          'validateRota': '{{ route('setup.scheduled-people.validate-rota') }}',
          'deleteHomeTeam': '{{ route('setup.scheduled-people.delete-home-team') }}',
          'conflictingDuties': '{{ route('setup.scheduled-people.conflicting-duties') }}',
          'removeConflictingDuties': '{{ route('setup.scheduled-people.remove-conflicting-duties') }}',
          'index': '{{ route('setup.scheduled-people.index') }}'
        }
      });
    });
  </script>
  @endif
@endpush
