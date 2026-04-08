<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<form id="location-form">
    @csrf
    <div class="location-form-container">
        <h2 class="location-header">{{isset($location) ? 'Update Location' : 'Add New Location'}}</h2>
        <div class="location-row">
            <div class="location-cell location-cell-left">Location<span class="required-asterisk">*</span></div>
            <div class="location-cell">
                <input type="text" style="width:250px" id="location" name="location" value="{{isset($location) ? $location->LN_Location : ''}}" required aria-required="true">
            </div>
        </div>
        <div class="location-row">
            <div class="location-cell location-cell-left"></div>
            <div class="location-cell">
                @if(!isset($location))
                <button type="button" id="submit-create-location-form" aria-label="Add location" aria-pressed="false">Add</button>
                @else
                @method('PUT')
                <button type="button" id="submit-update-location-form" aria-label="Update location" aria-pressed="false">Update</button>
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