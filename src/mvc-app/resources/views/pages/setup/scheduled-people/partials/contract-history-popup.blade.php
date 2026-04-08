{{-- Contract History Popup Partial --}}
{{-- This blade template is used to display contract history in a popup --}}

@php
$historyList = $historyDataArr ?? [];
$hasHistory = !empty($historyList);
@endphp

<table class="tablesmalltidy" width="800px">
    <tr>
        <th id="textcenter" style="padding: 10px 5px;"><b>History</b></th>
    </tr>
    
    @if($hasHistory)
        @foreach($historyList as $history)
            <tr>
                <td style="font-size: 12px; padding: 3px;"> {{ $history }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td style="font-size: 12px; padding: 3px;"> There is no History for this user yet! </td>
        </tr>
    @endif
</table>
