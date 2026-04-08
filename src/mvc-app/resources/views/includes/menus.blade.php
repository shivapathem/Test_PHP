<div>
    @php
    $allocadocs =  App\Models\Scheduling\AllocateDocuments::all()->sortby('Description')->pluck('Description', 'ID');           
    @endphp
    @if(auth()->user() != null)
    @php 
        $ixYearWeek =   auth()->user()->getixYearWeek();
        $userTeam =  auth()->user()->userAccessibleTeams(true)->select('schedulingTeamId','schedulingTeamName', 'divisionid', 'IsShowEditYearly', 'showProductionView')->get();
        $yesterdaydate = date("Y-m-d",strtotime("-1 day"));
        $todaydate = date("Y-m-d");
        $tomorrowdate = date("Y-m-d",strtotime("+1 day"));
        $less_3_month = date("Y", strtotime("-3 months"));
        $defaultTeam = $userTeam->where('schedulingTeamId', auth()->user()->defaultTeamId)->first();

        // scheduling group
        $userAreas = $userTeam->pluck('divisionid')->unique()->toArray();
        $schedulingGroups = App\Models\Scheduling\SchedulingGroup::where('IsIncludeINMenu', 1)->whereIn('DivisionID', $userAreas)->with(['schedulingTeams' => function($query) use ($userTeam) {
            $query->whereIn('schedulingTeams.schedulingTeamId', $userTeam->pluck('schedulingTeamId')->toArray());
            $query->where('schedulingTeams.isActive', 1);
        }])->get();
   @endphp
    <ul id="main-menu" class="sm sm-clean nav bbcMenu">
        <li>
            <a href='#'><b>Home</b></a>
            <ul>
                <li><a href="/">Allocations Home</a></li>                    
            </ul>
        </li>
        
        @if(Gate::any(['system-or-divisional-admin', 'has-scheduler', 'is-edit-master-duties', 'is-edit-rota-pattern']))
            <li><a href='#'><b>Forward Planning</b></a>
                <ul>
                    @if(Gate::any(['system-or-divisional-admin', 'has-scheduler', 'is-edit-master-duties']))
                    <li class="menu-list-redirect" data-param1="masterjobs"><a>Duties & Jobs</a></li>
                    @endif
                    @if(Gate::any(['system-or-divisional-admin', 'has-scheduler', 'is-edit-rota-pattern']))
                    <li class="menu-list-redirect" data-param1="rotas"><a>Rota Patterns</a></li>
                    @endif
                    @if(Gate::any(['system-or-divisional-admin', 'has-scheduler', 'is-edit-master-duties']))
                    <li class="menu-list-redirect" data-param1="masterdutiesfilter"><a>Duties Filter</a></li>
                    @endif
                </ul>
            </li>
	    @endif
        @if(config('bookingmenu.enable'))
        <li><a href="#">Booking</a>
            <ul>
                <li><a href="{{route('facility.index')}}">Facility Catalogue</a></li>
                <li><a href='#'><b>Facilities</b></a>
                    <ul>
                        <li><a href="{{route('facility-booking.index')}}">Facility Bookings</a></li>
                    </ul>
                </li>
                <li><a href='#'><b>Setup</b></a>
                    <ul>
                        <li><a>External Customer</a>
                            <ul>
                                <li><a href="{{route('customer.index')}}">Add External Customer</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
               <li><a href="{{route('facility-booking-admin.index')}}">Bookings Administration</a></li>
            </ul>
        </li>
        @endif
        @if(Gate::allows('has-team'))
            <li><a href='#'><b>Allocations</b></a>
                <ul>
                    <!-- My Screens -->
                    @if(Gate::allows('is-scheduleperson'))
                        <li class="menu-list-redirect" data-param1="dailyrota"><a><b>My Daily Allocations</b></a></li>
                        <li class="menu-list-redirect" data-param1="monthrota"><a><b>My Monthly Allocations</b></a></li>
                        <li class="menu-list-redirect" data-param1="yearrota"><a><b>My Yearly Allocations</b></a></li>
                    @endif
                    <li class="divider"></li>
                    <!-- Scheduling Allocations Screens -->
                    @if($defaultTeam != null)
                        @if(Gate::check('team-permissions',[$defaultTeam, 'edit_prodview']))
                            <li class="menu-list-redirect" data-param1="prodview" data-param2={{$defaultTeam->schedulingTeamId}}><a><b>Production View</b></a></li>
                        @endif
                        @if(Gate::check('team-permissions',[$defaultTeam, 'edit_prodview']))
                            <li class="menu-list-redirect" data-param1="editweekallocation" data-param2={{$defaultTeam->schedulingTeamId}}><a><b>Edit Weekly Allocations</b></a></li>
                        @endif
                        @if(Gate::check('team-permissions',[$defaultTeam, 'edityear_allocateteam']))
                            <li class="menu-list-redirect" data-param1="teamyearallocation" data-param2={{$defaultTeam->schedulingTeamId}}><a><b>Edit Yearly Allocations</b></a></li>
                        @endif                    
                        <li class="menu-list-redirect" data-param1="weeklyallocations" data-param2={{$defaultTeam->schedulingTeamId}}><a><b>Weekly Allocations</b></a></li>
                        <li class="menu-list-redirect" data-param1="multiviewalloc" data-param3={{$ixYearWeek}} data-param2={{$defaultTeam->schedulingTeamId}}><a><b>Multi Week Allocations</b></a></li>
                        <li class="menu-list-redirect" data-param1="studiousage" data-param2={{$defaultTeam->schedulingTeamId}}><a><b>Daily Allocations by Studio</b></a></li>
                    @endif
                    @if(Gate::allows('has-scheduler'))
                        <li class="menu-list-redirect" data-param1="adhocduty"><a><b>Ad Hoc Duty</b></a></li>
                    @endif
                    <li class="divider"></li>
                    @foreach($schedulingGroups as $group) 
                        @php $groupTeams = $group->schedulingTeams->sortBy('schedulingTeamName'); @endphp
                        @if($groupTeams->isNotEmpty())
                            <li class="scheduling-group-menu" style="background-color: #747474 !important; border-bottom: 2px solid;"><a><i>{{$group->SchedulingGroupsName}}</i></a>
                                <ul>
                                    @foreach($groupTeams as $team)
                                        @include('includes.menu-items.allocate-team-menu', ['teamModel' => $team])
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach                    
                    @php
                        $orphanedTeams = $userTeam->whereNotIn('schedulingTeamId', $schedulingGroups->pluck('schedulingTeams')->flatten()->pluck('schedulingTeamId')->toArray())->sortBy('schedulingTeamName');
                    @endphp
                    @foreach($orphanedTeams as $team)
                        @include('includes.menu-items.allocate-team-menu', ['teamModel' => $team])
                    @endforeach
                </ul>
            </li>
	    @endif
        @if(Gate::any(['has-scheduler', 'has-xmaspoint', 'has-leave', 'is-leave', 'is-leaveAdmin']))
            <li><a href='#'><b>Leave</b></a>
                <ul>
                    @if(Gate::allows('has-hometeam'))
                        <li class="menu-list-redirect" data-param1="showallocleave" data-param2="{{$less_3_month}}"><a>My Leave Record</a></li>
                    @endif
                    @if(Gate::allows('is-leave'))
                        <li class="menu-list-redirect" data-param1="showleave" data-param2="{{$less_3_month}}"><a>My Leave Requests</a></li>
                        <li class="menu-list-redirect" data-param1="showleaveweekly"><a>My Leave By Week</a></li>
                    @endif
                    @if(Gate::allows('is-leaveAdmin'))
                        <li class="menu-list-redirect" data-param1="adminshowleaveweekly"><a>Leave By Week (Admin)</a></li>
                        <li class="menu-list-redirect" data-param1="showleaveadmin"><a>Leave Admin</a></li>
                    @endif
                    @if(Gate::any(['has-scheduledadmin', 'system-or-divisional-admin', 'has-edit-leave-credits']))
                        <li class="menu-list-redirect" data-param1="leavecredit"><a>Leave Credits</a></li>
                    @endif
                     @if(Gate::any(['has-xmaspoint', 'has-scheduledadmin', 'system-or-divisional-admin']))
                           <li href="#"><a>Christmas Points</a>
                           <ul>
                        @foreach($userTeam as $key => $teamValue)
                                @if(Gate::check('team-permissions',[$teamValue,'team-xmas-point']))

                                    <li class="menu-list-redirect" data-param1="showxmaspoint" data-param2="{{$teamValue->schedulingTeamId}}"><a>{{$teamValue->schedulingTeamName}}</a></li>

                                @endif
                        @endforeach
                            </ul>
                        </li>
                    @endif

                </ul>
            </li>
	    @endif
        @if(Gate::allows('is-requestadmin-or-request'))
            <li><a href='#'><b>Requests & Locks</b></a>
                <ul>
                    @if(Gate::allows('is-request'))
                        <li class="menu-list-redirect" data-param1="showrequests"><a>My Requests & Locks</a></li>
                        <li class="menu-list-redirect" data-param1="showweeklyrequests"><a>My Requests By Week</a></li>
                    @endif
                    @if(Gate::allows('is-requestadmin'))
                        <li class="menu-list-redirect" data-param1="showrequestsadmin"><a>Requests Admin</a></li>
                        <li class="menu-list-redirect" data-param1="adminshowweeklyrequests"><a>Requests By Week <br> (Admin)</b></a></li>
                        <li class="menu-list-redirect" data-param1="showlocksadmin"><a>Locks Admin</a></li>
                    @endif
                </ul>
            </li>
	    @endif


        @if(Gate::allows('is-skilladmin', 'is-skillsshow'))
            <li><a href='#'><b>Skills</b></a>
                <ul>
                    @if(Gate::allows('has-skills'))
                        <li class="menu-list-redirect" data-param1="myskill"><a>My Skills</a></li>
                    @endif
                    @if(Gate::allows('is-skilladmin'))
                        @if(Gate::allows('has-scheduler') && $defaultTeam != null)
                            <li class="menu-list-redirect" data-param1="allocatehelper" data-param2="{{$defaultTeam->schedulingTeamId}}"><a>Allocate Helper</a></li>
                        @endif
                        <li class="menu-list-redirect" data-param1="skilladmin"><a>Skill Admin</a></li>
                    @endif
                </ul>
            </li>
	    @endif
        @if(Gate::any(['has-report', 'is-areareport', 'is-advancereport']))
            <li><a href='#'><b>Management Info</b></a>
                <ul>
                    @if(Gate::allows('has-report'))
                        <li class="menu-list-redirect" data-param1="freelancereport"><a>Freelance Usage</a></li>
                        <li class="menu-list-redirect" data-param1="leavereport"><a>Leave</a></li>
                        <li><a href='#'><b>Skicness</b></a>
                            <ul>
                                <li class="menu-list-redirect" data-param1="sickreport"><a>Reports</a></li>
                                <li class="menu-list-redirect" data-param1="sickreportglobal"><a>Team Summary</a></li>
                            </ul>
                        </li>
                    @endif
                    @if(Gate::allows('is-advancereport'))
                        <li class="menu-list-redirect" data-param1="actionHandler" data-param2="dutyExtractReport"><a>Duty Extract</a></li>
                        <li class="menu-list-redirect" data-param1="actionHandler" data-param2="showLeaveSummary"><a>Leave Details</a></li>
                        <li><a href='#'><b>Master Duties</b></a>
                            <ul>
                                <li class="menu-list-redirect" data-param1="weeklyduties"><a>Weekly</a></li>
                                <li class="menu-list-redirect" data-param1="summaryduties"><a>Summary</a></li>
                            </ul>
                        </li>
                        <li class="menu-list-redirect" data-param1="rotapattern"><a>Rota Patterns</a></li>
                        <li class="menu-list-redirect" data-param1="skillreports"><a>Skills</a></li>
                        <li class="menu-list-redirect" data-param1="allocationcapcity"><a>Spare Capacity</a></li>
                        <li class="menu-list-redirect" data-param1="actionHandler" data-param2="staffExtractReport"><a>Staff Extract</a></li>
                        <li class="menu-list-redirect" data-param1="actionHandler" data-param2="weeklyChargingSummary"><a>Weekly Charging Summary</a></li>
                        <li class="menu-list-redirect" data-param1="actionHandler" data-param2="WTDSummary"><a>WTD Report</a></li>
                    @endif
                    @if(Gate::any('system-admin','is-areareport'))
                        <li><a href='#'><b>Area Reports</b></a>
                            <ul>
                                <li class="menu-list-redirect" data-param1="byallocarea"><a>Freelance Usage by Area</a></li>
                                <li class="menu-list-redirect" data-param1="byleavearea"><a>Leave by Area</a></li>
                                <li class="menu-list-redirect" data-param1="bysickreport"><a>Sickness by Area</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        <li><a href="#">Admin</a>
            <ul>
                @if(Gate::allows('has-team'))
                    <li class="menu-list-redirect" data-param1="mysetup"><a>My Setup</a></li>
                    <li class="menu-list-redirect" data-param1="mycontact"><a>My Contact Details</a></li>
                @endif
                @if(Gate::any('has-scheduler','system-or-divisional-admin','has-shiftleader-manager'))
                <li class="menu-list-redirect" data-param1="teamcontact"><a>Team Contact Details</a></li>
                @endif
                <li class="menu-list-redirect" data-param1="deptadmin"><a>Team Administrators</a></li>
                <li><a href="#">Allocate Help</a>
                    <ul>
                        @foreach($allocadocs as $key => $itemValue)
                            <li><a class="menu-list-redirect" data-param1="allocatehelp" data-param2={{$key}}>{{$itemValue}}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="menu-list-redirect" data-param1="webversion"><a>Website Version</a></li>
                @if(Gate::allows('has-shiftleader') && $defaultTeam != null)
                    <li><a href="#">Shift Leader </a>
                        <ul>
                            @if(Gate::allows('has-handover'))
                                <li class="menu-list-redirect" data-param1="handovers"><a>Handovers</a></li>
                            @endif
                            <li><a>Turnaround Check</a>
                                <ul>
                                    <li class="menu-list-redirect" data-param1="gridcheck" data-param2={{$defaultTeam->schedulingTeamId}} data-param3="1"><a>Tomorrow</a></li>
                                    <li class="menu-list-redirect" data-param1="gridcheck" data-param2={{$defaultTeam->schedulingTeamId}} data-param3="8"><a>7 Days</a></li>
                                    <li class="menu-list-redirect" data-param1="gridcheck" data-param2={{$defaultTeam->schedulingTeamId}} data-param3="15"><a>14 Days</a></li>
                                    <li class="menu-list-redirect" data-param1="gridcheck" data-param2={{$defaultTeam->schedulingTeamId}} data-param3="22"><a>21 Days</a></li>
                                </ul>
                            </li>
                            <li class="menu-list-redirect" data-param1="skillpage"><a>Skills</a></li>
                            <li class="menu-list-redirect" data-param1="skillnocando" data-param2={{$defaultTeam->schedulingTeamId}}><a>Shifts To Check</a></li>
                        </ul>
                    </li>
                @endif

                @if(Gate::any(['has-scheduler','system-or-divisional-admin']))
                    <li><a href="#">Setup </a>
                        <ul>
                            @if(Gate::any(['has-scheduler','system-or-divisional-admin','is-requestadmin']))
                                {{-- <li class="menu-list-redirect" data-param1="allocateuser"><a>Allocate Users</a></li>  --}}
                                <li><a href="{{route('admin.allocate-users.index')}}">Allocate Users</a></li>
                                <li class="menu-list-redirect"><a href="{{ route('setup.scheduled-people.index') }}"  onclick="sessionStorage.removeItem('scheduled_people_team'); sessionStorage.removeItem('scheduled_people_user');">Scheduled People</a></li>
                            @endif
                            @if(Gate::any(['system-or-divisional-admin','is-requestadmin']))<!--need to add rights-->
                            <li class="menu-list-redirect" data-param1="configleave"><a>Leave & Request Groups</a></li>
                            @endif
                            @if(Gate::any(['has-scheduledadmin','system-or-divisional-admin']))
                                <li class="menu-list-redirect" data-param1="showscheduledteam"><a>Scheduling Teams</a></li>
                            @endif
                            @if(Gate::any(['has-scheduler','system-or-divisional-admin']))
                                <li><a>Charging</a>
                                    <ul>
                                        <li class="menu-list-redirect" data-param1="actionHandler" data-param2="listChargeCode"><a>Charge Codes</a></li>
                                        <li class="menu-list-redirect" data-param1="actionHandler" data-param2="listWbsCode"><a>WBS Codes</a></li>
                                        @if(Gate::allows('system-or-divisional-admin'))
                                            <li class="menu-list-redirect" data-param1="actionHandler" data-param2="activityCodesConfig"><a>Activity Codes</a></li>
                                            <li class="menu-list-redirect" data-param1="actionHandler" data-param2="listActivityChargeMapping"><a>Activity-Charge Mapping</a></li>
                                            <li class="menu-list-redirect" data-param1="actionHandler" data-param2="sendChargingToFinance"><a>Send Charging to Finance</a></li>
                                        @endif
                                    </ul>
                                </li>
                           @endif
                        </ul>
                    </li>
                @endif                              
                @if(Gate::any(['system-or-divisional-admin','has-scheduledadmin']))
                    <li><a>Admins</a>
                        <ul>
                            <li><a class="menu-list-redirect" data-param1="actionHandler" data-param2="teamPayIntegration">TeamPay Integration</a></li>
                            @if(Gate::allows('system-or-divisional-admin'))
                            <li><a  class="menu-list-redirect" data-param1="dutycolour">Master Duty Colours</a></li>
                            @endif
                            @if(config('bookingmenu.enable'))
                            @can('create', App\Models\Facility\Facility::class)
                            <li><a>Facilities</a>
                                <ul>
                                    <li><a href="{{route('facility.index')}}">Facility Catalogue</a></li>                                    
                                    <li><a href="{{route('action.index')}}">Actions</a></li>                                    
                                    <li><a href="{{route('equipment.index')}}">Equipment</a></li>
                                    <li><a href="{{route('customer.index')}}">External Customers</a></li>
                                    <li><a href="{{route('facility-type.index')}}">Facility Types</a></li>
                                    <li><a href="{{route('location.index')}}">Locations</a></li>
                                    <li><a href="{{route('service.index')}}">Services</a></li>
                                </ul>
                            </li>
                            @endcan 
                            @endif 
                            @if(Gate::allows('system-or-divisional-admin'))
                            <li><a  class="menu-list-redirect" data-param1="system">System</a></li>
                            <li><a href="{{route('scheduling-group.index')}}">Scheduling Groups</a></li>
                            @endif                          
                        </ul>
                    </li>
                @endif
            </ul>
        </li>
    </ul>
    @else
    <ul id="main-menu" class="sm sm-clean nav bbcMenu">
        <li>
            <a href='#'><b>Home</b></a>
            <ul>
                <li><a href="/">Allocations Home</a></li>                    
            </ul>
        </li>
        <li><a href="#">Admin</a>
            <ul>
                <li class="menu-list-redirect" data-param1="deptadmin"><a>Team Administrators</a></li>
                <li><a href="#">Allocate Help</a>
                    <ul>
                        @foreach($allocadocs as $key => $itemValue)
                            <li><a class="menu-list-redirect" data-param1="allocatehelp" data-param2={{$key}}>{{$itemValue}}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="menu-list-redirect" data-param1="webversion"><a>Website Version</a></li>
            </ul>
        </li>
    </ul>
    @endif
</div>
<script src="/mvc-app/public{{mix('js/smartmenus/menu.js')}}" type="text/javascript"></script>
