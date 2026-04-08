<form id="action-form" autocomplete="off" style="width: 560px;">
    <h2 class="form-heading"><b>{{isset($action) ? 'Update Actions' : 'Add New Actions'}}</b></h2>
    <div style="background: #eee; border: 1px solid #666;margin:15px;">
        <div style="display: flex;">
            <div style="width: 50%;font-size: 12px;border-bottom: 1px solid #000;padding: 4px;height: 36px;">Action Name <span style="color: red;">*</span></div>
            <div style="width: 48%;border: 1px solid #000;padding: 4px ;height: 36px;">
                <input type="text" style="width: 97%;" id="action_name" name="action_name" size="50" value="{{isset($action) ? $action->action_name : ''}}" required aria-required="true">
            </div>
        </div>

        <div style="display: flex;">
            <div style="width: 50%; font-size: 12px;padding: 4px;">Description <span style="color: red;">*</span></div>
            <div style="width: 48%; padding: 4px; border-left: 1px solid #000;"><textarea name="description" id="description" style=" border: 1px solid #ccc; border-radius: 3px; width: 100%;padding: 0px;" rows="4" required aria-required="true">{{isset($action) ? $action->description : ''}}</textarea></div>
        </div>
    </div>
    @csrf

    <p style="float: right; margin: 0px 20px 15px 0px;">
        @if(!isset($action))
        <button type="button" class="action-form-button" id="submit-create-action-form">Add</button>
        @else
        @method('PUT')
        <button type="button" class="action-form-button" id="submit-update-action-form">Update</button>
        @endif
    </p>
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