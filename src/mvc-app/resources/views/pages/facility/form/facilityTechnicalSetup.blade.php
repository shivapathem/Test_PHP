@php($existingServices = isset($editFacility) ? $editFacility->facilityServices->pluck('SR_Service', 'SR_ServiceID') : [])
@php($existingEquipments = isset($editFacility) ? $editFacility->facilityEquipments : null)

<link rel="stylesheet" href="/mvc-app/public/{{mix('css/facilityTechnicalSetup/facilityTechnicalSetup.css')}}" />
<div id="facility-technical-setup-modal">
    <form id="facility-technical-setup-form">
        <div style="color: red;font-size: 12px;text-align:center">
            <p id="service-list-validation-error" style="display: none; margin:unset"><span>Services required</span></p>
            <p id="equipment-list-validation-error" style="display: none;  margin:unset"><span>Equipment / Software required</span></p>
        </div>

        <div id="facility-technical-setup-selections">
            <div class="facility-form facility-form--layout facility-technical-setup-layout">
                <div class="facility-form-row facility-technical-setup-row">
                    <div class="facility-form-cell facility-technical-setup-cell facility-technical-setup-cell--source">
                        <h4 id="services-label">Services</h4>
                        <select name="service_list_select[]" class="facility-form-input" id="service_list_select" size="8" multiple="multiple" aria-labelledby="services-label">
                            @foreach($serviceList as $service)
                            @if(isset($existingServices[$service->SR_ServiceID])) @continue @endif
                            <option value="{{$service->SR_ServiceID}}">{{$service->SR_Service}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="facility-form-cell facility-technical-setup-cell facility-technical-setup-cell--controls" style="text-align: center;width: 55px;">
                        <br><br>
                        <div class="facility-technical-setup-controls" aria-label="Move services between lists">
                            <button style="margin-bottom:4px" type="button" id="service_list_select_rightAll" class="ui-button ui-widget ui-corner-all" aria-label="Add all services">>></button><br>
                            <button style="margin-bottom:4px" type="button" id="service_list_select_rightSelected" class="ui-button ui-widget ui-corner-all" aria-label="Add selected services">></button><br>
                            <button style="margin-bottom:4px" type="button" id="service_list_select_leftSelected" class="ui-button ui-widget ui-corner-all" aria-label="Remove selected services">
                                <
                                    </button><br>
                                    <button type="button" id="service_list_select_leftAll" class="ui-button ui-widget ui-corner-all" aria-label="Remove all services">
                                        <<
                                            </button>
                        </div>
                    </div>

                    <div class="facility-form-cell facility-technical-setup-cell facility-technical-setup-cell--target">
                        <h4 id="selected-services-label">Selected Services</h4>
                        <select name="service_list_select_to[]" class="facility-form-input" id="service_list_select_to" size="8" multiple="multiple" aria-labelledby="selected-services-label">
                            @foreach($existingServices as $extId => $existingService)
                            <option value="{{$extId}}">{{$existingService}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="facility-form-row facility-technical-setup-row">
                    <div class="facility-form-cell facility-technical-setup-cell facility-technical-setup-cell--source">
                        @php($existingEquipmentsArray = isset($editFacility) ? $existingEquipments->pluck('EQ_Equipment', 'EQ_EquipmentID') : [])
                        <h4 id="equipment-label">Equipment / Software</h4>
                        <select name="equipment_list_select[]" class="facility-form-input" id="equipment_list_select" size="8" multiple="multiple" aria-labelledby="equipment-label">
                            @foreach($equipmentList as $equipment)
                            @if(isset($existingEquipmentsArray[$equipment->EQ_EquipmentID])) @continue @endif
                            <option value="{{$equipment->EQ_EquipmentID}}" data-type="{{$equipment->EQ_Equipment_Type}}">{{$equipment->EQ_Equipment}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="facility-form-cell facility-technical-setup-cell facility-technical-setup-cell--controls" style="text-align: center;width: 55px;">
                        <br><br>
                        <div class="facility-technical-setup-controls" aria-label="Move equipment between lists">
                            <button style="margin-bottom:4px" type="button" id="equipment_list_select_rightAll" class="ui-button ui-widget ui-corner-all" aria-label="Add all equipment">>></button><br>
                            <button style="margin-bottom:4px" type="button" id="equipment_list_select_rightSelected" class="ui-button ui-widget ui-corner-all" aria-label="Add selected equipment">></button><br>
                            <button style="margin-bottom:4px" type="button" id="equipment_list_select_leftSelected" class="ui-button ui-widget ui-corner-all" aria-label="Remove selected equipment">
                                <
                                    </button><br>
                                    <button type="button" id="equipment_list_select_leftAll" class="ui-button ui-widget ui-corner-all" aria-label="Remove all equipment">
                                        <<
                                            </button>
                        </div>
                    </div>

                    <div class="facility-form-cell facility-technical-setup-cell facility-technical-setup-cell--target">
                        <h4 id="selected-equipment-label">Selected Equipment / Software</h4>
                        <select name="equipment_list_select_to[]" class="facility-form-input" id="equipment_list_select_to" size="8" multiple="multiple" aria-labelledby="selected-equipment-label">
                            @foreach($existingEquipments ?? [] as $existingEquipment)
                            @php($equipmentName = trim((string)($existingEquipment->EQ_Equipment ?? '')))
                            @if(empty($existingEquipment->EQ_EquipmentID) || $equipmentName === '' || $equipmentName === 'No Equipment Selected')
                            @continue
                            @endif
                            <option value="{{$existingEquipment->EQ_EquipmentID}}" data-type="{{$existingEquipment->EQ_Equipment_Type}}">{{$existingEquipment->EQ_Equipment}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <br>
            <div class="facility-technical-setup-actions" style="text-align: right">
                <button type="button" style="font-size:12px" id="facility-technical-setup-selection-next-page-quantity" class="ui-button ui-widget ui-corner-all">Next</button>
            </div>
        </div>

        <div id="facility-technical-setup-quantity" style="display: none">
            <div class="facility-form facility-form--layout facility-technical-setup-quantity-layout">
                <div class="facility-technical-setup-quantity-title">
                    <h4>Equipment</h4>
                </div>

                <div id="table-technical-setup-quantity" class="technical-quantity-grid">
                    <div class="technical-quantity-row technical-quantity-row--header">
                        <div class="technical-quantity-cell technical-quantity-cell--equipment">Equipment</div>
                        <div class="technical-quantity-cell technical-quantity-cell--quantity">Quantity</div>
                        <div class="technical-quantity-cell technical-quantity-cell--notes">Notes</div>
                    </div>

                    {{-- This html is used in edit, the below html is generated --}}
                    @foreach($existingEquipments ?? [] as $existingEquipment)
                    @php($equipmentName = trim((string)($existingEquipment->EQ_Equipment ?? '')))
                    @if(empty($existingEquipment->EQ_EquipmentID) || $equipmentName === '' || $equipmentName === 'No Equipment Selected')
                    @continue
                    @endif
                    <div class="technical-quantity-row technical-quantity" id="technical-quantity-row-{{$existingEquipment->EQ_EquipmentID}}" data-id="{{$existingEquipment->EQ_EquipmentID}}">
                        <div class="technical-quantity-cell technical-quantity-cell--equipment">{{$existingEquipment->EQ_Equipment}}</div>
                        <div class="technical-quantity-cell technical-quantity-cell--quantity">
                            <input type="number" style="width: 35px;" min="1" step="1" value="{{$existingEquipment->getOriginal('pivot_FCEQ_Quantity')}}" name="equipment_quantity_{{$existingEquipment->EQ_EquipmentID}}" aria-label="Quantity for {{$existingEquipment->EQ_Equipment}}"
                                @include('includes.input-number-regex')>
                        </div>
                        <div class="technical-quantity-cell technical-quantity-cell--notes">
                            <input value="{{$existingEquipment->getOriginal('pivot_FCEQ_Note')}}" type="text" maxlength="220" class="facility-form-input" name="equipment_note_{{$existingEquipment->EQ_EquipmentID}}" aria-label="Notes for {{$existingEquipment->EQ_Equipment}}" oninput="this.value = this.value.slice(0, 220)">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <br>
            <div class="facility-technical-setup-actions" style="text-align: right">
                <button type="button" style="font-size:12px" id="facility-technical-setup-quantity-back-page-selection" class="ui-button ui-widget ui-corner-all">Back</button>
                <button type="button" style="font-size:12px" id="facility-technical-setup-complete" class="ui-button ui-widget ui-corner-all">Done</button>
            </div>
        </div>
    </form>
</div>