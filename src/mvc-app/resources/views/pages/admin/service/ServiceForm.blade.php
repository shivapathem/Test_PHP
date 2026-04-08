<form id="service-form">
    <div class="service-form-main-div" id="">
        <div style="border:1px solid #666">
            <div>
                <h2 class="service-form-heading">{{isset($service) ? 'Update Service' : 'Add New Service'}}</h2>
            </div>
            <div style="display: flex;border-bottom:1px solid #666">
                <div class="service-form-label">Service<span class="required-asterisk">*</span></div>
                <div style="padding: 4px;">
                    <input type="text" style="width:250px;margin-left:1px;" id="service" name="service" size="35" value="{{isset($service) ? $service->SR_Service : ''}}" required aria-required="true">
                </div>
            </div>
            <div style="display: flex;">
                <div class="service-form-label"></div>
                <div style="padding: 4px;">
                    @if(!isset($service))
                    <button type="button" style="margin-left: 1px;" id="submit-create-service-form">Add</button>
                    @else
                    @method('PUT')
                    <button type="button" style="margin-left: 1px;" id="submit-update-service-form">Update</button>
                    @endif
                </div>
            </div>
            @csrf
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