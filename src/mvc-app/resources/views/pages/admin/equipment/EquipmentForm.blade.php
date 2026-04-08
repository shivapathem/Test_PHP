<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<form id="equipment-form">
    <div class="equipment-form-container">
        <div class="equipment-row">
            <h2 class="equipment-header">{{isset($equipment) ? 'Update Equipment' : 'Add New Equipment'}}</h2>
        </div>
        <div class="equipment-row">
            <div class="equipment-cell equipment-cell-left">Equipment<span class="required-asterisk">*</span></div>
            <div class="equipment-cell">
                <input type="text" style="width:250px" id="equipment" name="equipment" size="50"  value="{{isset($equipment) ? $equipment->EQ_Equipment : ''}}" required aria-required="true">
            </div>
        </div>
        <div class="equipment-row">
            <div class="equipment-cell equipment-cell-left">Equipment Type<span class="required-asterisk">*</span></div>
            <div class="equipment-cell">
                <select data-placeholder="Select Equipment Type" id="equipment_type" name="equipment_type" style="width:258px" required aria-required="true" aria-label="Equipment Type Selection" role="listbox">
                    <option value="" disabled selected>Select Equipment Type</option>
                    @foreach ($equipmentTypeList as $key => $equipmentType)
                        <option value="@if($equipmentType == "Software") SW @else ET @endif" @if(isset($equipment) && $equipment->EQ_Equipment_Type == $key) selected @endif>{{$equipmentType}}
                        </option>
                    @endforeach
                    </option>
                </select>
            </div>
        </div>
        <div class="equipment-row">
            <div class="equipment-cell equipment-cell-left"></div>
            <div class="equipment-cell">
                @csrf
                @if(!isset($equipment))
                <button type="button" id="submit-create-equipment-form" aria-label="Add new equipment">Add</button>
                @else
                @method('PUT')
                <button type="button" id="submit-update-equipment-form" aria-label="Update existing equipment">Update</button>
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
