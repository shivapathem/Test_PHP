
<table style="font-size: 10px;border-collapse: inherit;width: 100%;">
    <tr>
        <td>Services </td>
        <td>{{$facility->facilityServices->pluck('SR_Service')->implode(' | ')}}</td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 2px 0 0 0; border: unset;">
            <table style="border-collapse: collapse;width: 100%;" >
                <tr>
                    <td>Equipment/Software</td>
                    <td>Quantity</td>
                    <td>Note</td>
                </tr>
                @foreach($facility->facilityEquipments as $facilityEquipment)
                <tr>
                    <td>{{$facilityEquipment->EQ_Equipment}}</td>
                    <td>{{$facilityEquipment->getOriginal('pivot_FCEQ_Quantity')}}</td>
                    <td>{{($facilityEquipment->getOriginal('pivot_FCEQ_Note') ?? '-')}}</td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>