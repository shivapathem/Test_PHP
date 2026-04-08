{{--
Facility Administrator add person 
--}}
<div>
    <div>
        <div>
            Facility Administrator
        </div>
        <div>
            <br>
        <select data-facility-id="{{$facility->FC_FacilityID}}" id="facility_admins" name="facility_admins[]" multiple="multiple" data-placeholder="Select User">
            @php($existingUsers = $facility->facilityAdministrators->pluck('UD_UserID')->toArray())
            @foreach($facility->facilityAreaOwner->users as $user)
            <option value={{$user->UD_UserID}}
            @if(in_array($user->UD_UserID, $existingUsers)) selected  @endif 
            >{{$user->UD_DisplayName}} ({{$user->UD_NetLogin}})</option>
            @endforeach
        </select>
        </div>
        <div>
            <br>
            <button id="facility-administrator-update-button" class="ui-button ui-widget ui-corner-all" style="float: right">Update</button>
        </div>
    </div>
</div>