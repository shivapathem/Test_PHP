{{-- @extends('layouts.default')
@section('content') --}}
@push('style-css')
    <link rel="stylesheet" href="/mvc-app/public{{mix('css/admin/admin.css')}}" />
@endpush
<div id="company-admin-booking-tab">
    <input type="hidden" name="js_usertype" value="1" id="js_usertype">
    <input type="hidden" name="js_tablename" value="js_scheduledstaffteamlist" id="js_tablename">
    <input type="hidden" name="js_create" value="" id="js_create">
    <input type="hidden" name="js_sysadmin" value="{{ session('user.SysAdmin') }}" id="js_sysadmin">

    <div class="Searchcontainer Pos-Searchcontainer" style="font-family: Verdana, Arial, sans-serif; padding: 12px 0; margin-top: -20px;">
        <div class="tableheadersmall medtextboldcentre screen-fix-withscroll" style="margin-bottom: 10px;">
            <div style="position: relative; margin-bottom: 10px; display: flex; align-items: center; min-height: 40px;">
                <div style="position: relative; z-index: 10; display: flex; align-items: center;">
                    <form class="stfform" style="margin: 0;">
                        <div class="fields" style="display: flex; align-items: center; margin: 0;">
                            <label for="hometeam" style="font-size: 12px; margin-right: 5px; margin-bottom: 0;">Scheduling Team:</label>
                            <select name="hometeam" class="division-seclect" id="js_schedulled_teamdropdown">
                                <option value="">Select The Team</option>
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
                    <h1 style="font-size: 8pt;margin-bottom: 0px;">Scheduled Staff</h1>
                    <p style="font-weight: bold;">Displaying a list of all Scheduled Staff.</p>
                </div>
            </div>
        </div>
    <div id="scheduled_staffs" class="table-scheduled-people-container" role="tabpanel" tabindex="0"
        aria-labelledby="tab-scheduled_staffs" style="position: relative;">
        <div id="scheduled-staff-loading" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.3); z-index: 100; justify-content: center; align-items: center;">
            <div id="scheduled-staff-spinner-container" style="display: flex; flex-direction: row; gap: 18px; align-items: center;">
                <div class="loader-spinner"></div>
                <span>Loading Scheduled Staff...</span>
            </div>
        </div>
        <table id="js_scheduledstaffteamlist" class="display tablesmall">
            <thead>
                <tr class="headerSticky cellWmmrequested" style="font-size: 11px !important;">
                    <th scope="col">Full Name</th>
                    <th scope="col">Staff Number</th>
                    <th scope="col">
                        <p style="margin-bottom: 2px;">Network ID</p>
                    </th>
                    <th scope="col">Rota</th>
                    <th scope="col">Default</th>
                    <th scope="col">View All Allocations</th>
                    @foreach (App\Models\User\UserRole::getPermission(1) as $permission)
                        <th scope="col">{{ $permission['RoleName'] }}</th>
                    @endforeach
                    <th scope="col">Home&nbsp;</th>
                    <th scope="col">Info/History</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
</div>