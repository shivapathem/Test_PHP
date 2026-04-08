<div>
    <form id="area-neweditduty" style="margin-top: -10px;">
        <input type="hidden" id="area_id" name="area_id" value="{{ isset($areaId) ? $areaId : '' }}" />
        <div class="form-field-container-area" style="margin: 0px;">
            <label for="area-hometeam-user" class="form-label-area">Network ID</label>
            <div class="form-input-area" style="margin-right: 28px;">
                <select name="hometeam" class="division-select" id="area-hometeam-user">
                    <option value="0">--Blank--</option>
                    @if(isset($users) && count($users) > 0)
                        @foreach($users as $user)
                            <option value="{{ $user['UserId'] }}">{{ $user['NetLogin'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </form>
    
    <section>
        <div class="tables table-boxs">
            <div class="scrollable">
                <table class="oddevenclass tablesmall stripe dataTable no-footer w-100 user-details-table-area" id="userDetailsList" role="grid" style="margin-top: 0px;">
                    <thead>
                        <tr>
                            <th>Network ID</th>
                            <th>Full Name</th>
                            <th>First Name</th>
                            <th>Surname</th>
                            <th>Email Address</th>
                            <th>Employee Number</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="context-menu-one" id="area-user-details">
                    </tbody>
                </table>
            </div>
        </div>
    </section> 
</div>

