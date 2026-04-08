<table class="tablesmalltidy" width="800px">
    <tr>
        <th id="textcenter" style="padding: 10px 5px;">
            <b>History</b>
        </th>
    </tr>
    @if (!empty($history) && count($history) > 0)
        @foreach ($history as $item)
            <tr>
                <td style="font-size: 12px; padding: 3px;">{{ $item['History'] ?? '' }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td style="font-size: 12px; padding: 3px;">There is no History for this user yet!</td>
        </tr>
    @endif
</table>
