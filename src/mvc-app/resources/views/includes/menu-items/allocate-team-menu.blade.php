{{--
Allocation team menu - shows the team name and the relevant allocation options based on permissions
--}}
<li><a>{{$teamModel->schedulingTeamName}}</a>
<ul>
    <li class="menu-list-redirect" data-param1="dailyallocations" data-param2={{$teamModel->schedulingTeamId}}><a>Daily Allocations</a></li>
    <li class="menu-list-redirect" data-param1="weeklyallocations" data-param2={{$teamModel->schedulingTeamId}}><a>Weekly Allocations</a></li>
    @if(Gate::check('team-permissions',[$teamModel, 'editweekly_allocateteam_admin']))
        <li class="menu-list-redirect" data-param1="editweekallocation" data-param2={{$teamModel->schedulingTeamId}}><a>Edit Weekly Allocations</a></li>
        @if(Gate::check('team-permissions',[$teamModel, 'edityear_allocateteam']))
            <li class="menu-list-redirect" data-param1="teamyearallocation" data-param2={{$teamModel->schedulingTeamId}}><a>Edit Yearly Allocations</a></li>
        @endif
    @endif
    @if(Gate::check('team-permissions',[$teamModel, 'edit_prodview']))
        <li class="menu-list-redirect" data-param1="prodview" data-param2={{$teamModel->schedulingTeamId}}><a>Production View</a></li>
    @endif
    <li class="menu-list-redirect" data-param1="multiviewalloc" data-param3={{$ixYearWeek}} data-param2={{$teamModel->schedulingTeamId}}><a>Multi Week Allocations</a></li>
</ul>
</li>