<div class="Searchcontainer Pos-Searchcontainer" style="font-family: Verdana, Arial, sans-serif; padding: 6px 0; margin-top: -20px;">
    <div class="tableheadersmall medtextboldcentre screen-fix-withscroll" style="margin-bottom: 10px;">
        <div style="position: relative; margin-bottom: 10px; display: flex; align-items: center; min-height: 40px;">
            <div style="position: relative; z-index: 10; display: flex; align-items: center; gap: 10px;">
                @if($canAllocate)
                <button class="btn-class addbtn allocate-addbtn allocate-btn notclickable buttonDisabled grayscale-icon" id="js_addallocateuser_area_permission" aria-controls="area_permission" aria-label="Allocate User to Area" style="font-size: 1em; white-space: nowrap; margin-bottom: 0;cursor: pointer; display: flex; align-items: center;">
                    <img src="/images/button_add.png" alt="Allocate User">&nbsp;Allocate User
                </button>
                @endif
                <div class="area-dropdown-wrap" style="display: flex; align-items: center; margin: 0;">
                    <span class="area-dropdown-label" style="font-size: 12px; margin-right: 5px; white-space: nowrap;">Area:</span>
                    <select class="facility-form-input" data-placeholder="Select Accessible" id="area_chosen" name="area_chosen" required aria-required="true" style="width: 200px;">
                        <option value="">Select Area</option>
                        @foreach($allArea as $area)
                            @if($area->isActive == 1)
                                <option value="{{$area->DivisionID}}">{{\Illuminate\Support\Str::limit($area->DivisionName, 30, '')}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="position: absolute; left: 0; right: 0; text-align: center; padding: 1px 0; z-index: 1; pointer-events: none;">
                <h1 style="font-size: 8pt;margin-bottom: 0px;">Area Admin</h1>
                <p style="font-weight: bold; margin: 0;">Displaying a list of Area Admins.</p>
            </div>
        </div>
    </div>
    <div class="table-allocate-user-container" style="position: relative;">
        <div id="area-permissions-loading" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.3); z-index: 100; justify-content: center; align-items: center;">
            <div id="area-permissions-spinner-container" style="display: flex; flex-direction: row; gap: 18px; align-items: center;">
                <div class="loader-spinner"></div>
                <span>Loading Area Permissions...</span>
            </div>
        </div>
        <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer screen-fix-withscroll width100Percent" id="areaPermissionsLists">
            <thead>
                <tr class="headerSticky" style="font-size: 11px;">
                    <th>Full Name</th>
                    <th>Staff Number</th>
                    <th>Network ID</th>
                    <th>Role</th>
                    <th>Facility Administrator</th>
                    <th>Area Report</th>
                    <th>History</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="context-menu-one" id="area-users-table-body">
            </tbody>
        </table>
    </div>
</div>
<script>
    window.areaPermissions = {
        isSysAdmin: {{ $isSysAdmin }},
        canAllocate: {{ $canAllocate }}
    };
</script>
