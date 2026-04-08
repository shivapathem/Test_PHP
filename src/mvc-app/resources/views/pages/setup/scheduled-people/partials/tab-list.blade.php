{{-- Page-specific CSS compiled via Mix (push to head) --}}
@push('style-css')
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/scheduled-people/schedulepeople.css') }}">
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/scheduled-people/create-scheduled-person.css') }}">
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/scheduled-people/contact-history.css') }}">
@endpush

@php
    // Simulate legacy userTeamList population
    $userId = Auth::id() ?? 0;
    $pageId = 6;
    $userActionType = 'create';
    $schedulePersonId = 0;
    $userTeamList = app('App\Repositories\Admin\ScheduledPeopleRepository')->getSchedulingTeams($userId, $userActionType, $pageId, $schedulePersonId);
@endphp
<div id="page-container">
    <div id="searchScheduleperson" class="search-container">
        <div class="Searchcontainer">
            <div class="fieldsection" style="margin-top: -10px; padding: 0px; margin-bottom: -10px;">
                <div class="parant_div_1" id="parant_div_1">
                    <div class='child_div_1 no-border'>
                        <form class="stfform">
                            <div class="fields" id="homescheduledteam">
                                <label for="teamdropdown"> Home Scheduling Team</label>
                                <select name="hometeam" id="teamdropdown" class="division-seclect">
                                    <option value="">Select a Team</option>
                                    @foreach ($userTeamList as $key => $team)
                                        <option value="{{ $team['TeamID'] }}" {{ (request('searchteamid') == $team['TeamID']) ? 'selected' : '' }}>{{ $team['TeamName'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class='child_div_2' style="display:flex; justify-content:flex-end; align-items:center; padding:4px 0;">
                        {{-- Display Name search --}}
                        <form class="stfform Srcfld" id="searchForm" style="margin:0; padding:0;">
                            <div style="display:inline-flex; align-items:center; gap:5px;">
                                <label id="pt-37" for="dispalyName" style="font-size:12px; margin:0; white-space:nowrap;">Display Name</label>
                                <input autocomplete="off" class="ui-autocomplete-input" type="text"
                                    placeholder="Enter Text" id="dispalyName" name="dispName"
                                    value="{{ request('searchuserid') }}"
                                    style="height:20px; margin:0;">
                                <em class="fa fa-search" id="search" style="cursor:pointer;"></em>
                            </div>
                        </form>
                        {{-- Exclude Archive + Clear Filter --}}
                        <div style="display:inline-flex; align-items:center; gap:8px; margin-left:12px;">
                            <label for="excludeNoTeam" id="pt-38" style="font-size:12px; margin:0; white-space:nowrap;">Exclude Archive</label>
                            <input type="checkbox" id="excludeNoTeam" name="excludeNoTeam" value="asd"
                                style="height:10px; margin:0; width:auto;"
                                onclick="if(excludeNoTeamVal.value == 0){excludeNoTeamVal.value = 1;}else{excludeNoTeamVal.value = 0;}"
                                checked>
                            <input type="hidden" id="excludeNoTeamVal" name="excludeNoTeamVal" value="1">
                            <div id="clearfilterbox" style="display:inline-flex; align-items:center;">
                                <button type="button" id="clearfilter" aria-label="Clear filter"
                                    style="background:transparent; border:0; padding:0; cursor:pointer; display:inline-flex; align-items:center; gap:4px; font-family:inherit; color:inherit;">
                                    <img src="/mvc-app/public/images/red_cross.png" alt="" width="14px" style="display:block; flex-shrink:0;">
                                    <span style="font-size:12px; line-height:1; white-space:nowrap;">Clear filter</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="container tables scrollable" id="tableheightsearch">
                <table class="display tablesmall" id="scheduledpeoplelist" style="width: 100%;">
                    <thead>
                        <tr style="height: 25px;">
                            <th>Display Name</th>
                            <th>Forename</th>
                            <th>Surname</th>
                            <th>Network ID</th>
                            <th>BBC Email Address</th>
                            <th>Home Scheduling Team</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="context-menu-one">
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="no-record">Please Apply filter to display records.</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="/mvc-app/public{{ mix('js/scheduled-people/tab-list.js') }}" type="text/javascript"></script>
@endpush