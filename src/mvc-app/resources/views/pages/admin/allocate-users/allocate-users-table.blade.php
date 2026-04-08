{{--
Allocate user tab
--}}
<div class="Searchcontainer Pos-Searchcontainer" style="font-family: Verdana, Arial, sans-serif; padding: 20px 0; margin-top: -20px;">
    <div class="tableheadersmall medtextboldcentre screen-fix-withscroll" style="margin-bottom: 10px;">
        <div style="position: relative; margin-bottom: 10px; display: flex; align-items: center; min-height: 40px;">
            <div style="position: relative; z-index: 10; display: flex; align-items: center;">
                @can('createAllocateUser', 'allocate-user')
                <button class="btn-class addbtn allocate-addbtn allocate-btn" id="js_addallocateuser" style="font-size: 1em; margin-bottom: 0;cursor: pointer; display: flex; align-items: center;">
                    <img src="/images/button_add.png" alt="Allocate User">&nbsp;Allocate User
                </button>
                @endcan
            </div>
            <div style="position: absolute; left: 0; right: 0; text-align: center; padding: 1px 0; z-index: 1; pointer-events: none;">
                <h1 style="font-size: 8pt;margin-bottom: 0px;">Allocate Users</h1>
                <p style="font-weight: bold; margin: 0;">Displaying a list of all Allocate Users.</p>
            </div>
        </div>
    </div>
    <div class="table-allocate-user-container" style="position: relative;">
        <div id="allocate-users-loading" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.3); z-index: 100; justify-content: center; align-items: center;">
            <div id="allocate-users-spinner-container" style="display: flex; flex-direction: row; gap: 18px; align-items: center;">
                <div class="loader-spinner"></div>
                <span>Loading Allocate Users...</span>
            </div>
        </div>
        <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer display screen-fix-withscroll width100Percent" id="alocateUsersLists">
            <thead>
                <tr class="headerSticky" style="font-size: 11px;">
                    <th>Full Name</th>
                    <th>Network ID</th>
                    <th>Email address</th>
                    <th>Employee Number</th>
                    <th>Staff Number</th>
                    <th>Current Home Team</th>
                    <th>Info</th>
                </tr>
            </thead>
            <tbody class="context-menu-one">
            </tbody>
        </table>
    </div>
</div>