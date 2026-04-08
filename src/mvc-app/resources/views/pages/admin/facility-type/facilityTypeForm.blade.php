<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<form id="create-facility-type-form">
    <div class="facility-type-form-container">
        <div class="facility-type-row" style="background-color: #ccc;">
            <h2 class="facility-type-header">{{isset($facility_type) ? 'Update Facility type' : 'Add New Facility type'}}</h2>
        </div>
        <div class="facility-type-row">
            <div class="facility-type-cell facility-type-cell-left">Facility Type<span class="required-asterisk">*</span></div>
            <div class="facility-type-cell">
                <input type="text" style="width:270px" id="facility_type" name="facility_type" size="35" value="{{isset($facility_type) ? $facility_type->FT_FacilityType : ''}}" required aria-required="true">
            </div>
        </div>
        <div class="facility-type-row">
            <div class="facility-type-cell facility-type-cell-left">Facility Sub Types<span class="required-asterisk">*</span></div>
            <div class="facility-type-cell">
                <select id="facility_sub_types" name="facility_sub_types[]" style="width:60%" data-placeholder="Type the Facility Sub Type" multiple class="facility-sub-type" required aria-required="true">
                    @foreach($facility_type->facilitySubType ?? [] as $facilitySubType)
                    <option value="{{$facilitySubType->FST_FacilitySubType}}" selected>{{$facilitySubType->FST_FacilitySubType}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="facility-type-row">
            <div class="facility-type-cell facility-type-cell-left"></div>
            <div class="facility-type-cell">
                @csrf
                @if(!isset($facility_type))
                <td><button type="button" id="submit-create-facility-type-form">Add</button></td>
                @else
                @method('PUT')
                <td><button type="button" id="submit-update-facility-type-form">Update</button></td>
                @endif
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function(){
		$(".focus-content").focus();
		$('#facebox').on('keydown', function(e) {
			if (e.key === 'Tab') {
				var focusableElements = $('#facebox :focusable');
				var firstElement = focusableElements[0];
				var lastElement = focusableElements[focusableElements.length - 1];
				if (e.shiftKey && document.activeElement === firstElement) {
					lastElement.focus();
					e.preventDefault();
				} else if (!e.shiftKey && document.activeElement === lastElement) {
					firstElement.focus();
					e.preventDefault();
				}
			}
    	});
    	});
</script>