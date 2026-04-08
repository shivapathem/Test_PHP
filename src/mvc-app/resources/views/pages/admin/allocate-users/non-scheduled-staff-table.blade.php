@push('style-css')
<link rel="stylesheet" href="/mvc-app/public{{mix('css/admin/admin.css')}}" />
@endpush
<div id="company-admin-booking-tab">
    <input type="hidden" name="js_usertype" value="0" id="js_usertype">
    <input type="hidden" name="js_tablename" value="js_nonscheduledstaffteamlist" id="js_tablename">
    <input type="hidden" name="js_create" value="" id="js_create">
    <input type="hidden" name="js_sysadmin" value="{{ session('user.SysAdmin') }}" id="js_sysadmin">
    <input type="hidden" name="js_show_add" value="{{ $showAddNonScheduled ?? 0 }}" id="js_show_add">

    <div class="Searchcontainer Pos-Searchcontainer" style="font-family: Verdana, Arial, sans-serif; padding: 22px 0; margin-top: -20px;">
        <div class="tableheadersmall medtextboldcentre screen-fix-withscroll" style="margin-bottom: 10px;">
            <div style="position: relative; margin-bottom: 10px; display: flex; align-items: center; min-height: 40px;">
                <div style="position: relative; z-index: 10; display: flex; align-items: center; gap: 10px;">
                    @if(($showAddNonScheduled ?? 0) == 1)
                    <button class="notclickable nonscheduledStaffbtn buttonDisabled btn-class addbtn allocate-btn" id="js_addnonscheduledstaff" style="font-size: 1em; white-space: nowrap; margin-bottom: 0;cursor: pointer; display: flex; align-items: center;">
                        <img src="/images/button_add.png" alt="">&nbsp;Non Scheduled Staff
                    </button>
                    @endif
                    <form class="stfform" style="margin: 0;">
                        <div class="fields" style="display: flex; align-items: center; margin: 0;">
                            <label for="hometeam" style="font-size: 12px; margin-right: 5px; margin-bottom: 0; white-space: nowrap;">Scheduling Team:</label>
                            <select name="hometeam" class="division-seclect" id="js_teamdropdown">
                                <option value="">Select the Team</option>
                                @foreach ($userTeamLists as $teamdata)
                                    <option data-divisionid="{{ $teamdata->DivisionId }}" value="{{ $teamdata->TeamID }}">
                                        {{ $teamdata->TeamName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div style="position: absolute; left: 0; right: 0; text-align: center; padding: 1px 0; z-index: 1; pointer-events: none;">
                    <h1 style="font-size: 8pt;margin-bottom: 0px;">Non Scheduled Staff</h1>
                    <p style="font-weight: bold;">Displaying a list of all Non Scheduled Staff.</p>
                </div>
            </div>
        </div>

    <div class="table-non-scheduled-people-container border-0" style="position: relative;">
        <div id="non-scheduled-staff-loading" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.3); z-index: 100; justify-content: center; align-items: center;">
            <div id="non-scheduled-staff-spinner-container" style="display: flex; flex-direction: row; gap: 18px; align-items: center;">
                <div class="loader-spinner"></div>
                <span>Loading Non-Scheduled Staff...</span>
            </div>
        </div>
        <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="js_nonscheduledstaffteamlist"
            style="width: 100%;position: relative;" role="grid" aria-describedby="joblisting_info">
            <thead>
                <tr class="cellWmmrequested" style="font-size: 11px !important;">
                    <th class="cellWmmrequested">Full Name</th>
                    <th class="cellWmmrequested">Staff Number</th>
                    <th class="cellWmmrequested">Network ID</th>
                    <th class="schedulingTeam185W">Role</th>
                    <th class="mWH60">Default</th>
                    <th scope="col">View All Allocations</th>
                    @foreach (App\Models\User\UserRole::getPermission(0) as $permission)
                        <th scope="col">{{ $permission['RoleName'] }}</th>
                    @endforeach
                    <th scope="col">Info/History</th>
                    <th class="mWH60">Action</th>
                </tr>
            </thead>
            <tbody class="context-menu-one">
            </tbody>
        </table>
    </div>
</div>
</div>
