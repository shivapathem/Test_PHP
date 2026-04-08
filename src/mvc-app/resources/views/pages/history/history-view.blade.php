<div id="historyWapper">
    <table class="redtable" style="width: 700px; margin-top: 5px;">
        <thead>
            <tr>
                <th style="text-align: center; padding: 15px;">{{$title}}</th>
            </tr>
        </thead>
        <tbody>
            @if($historyLogs->count() > 0)

                @foreach($historyLogs as $historyLog)
                <tr>
                    <td style="padding: 5px;">
                        @foreach($historyLog->HL_HLogs as $log)
                            <p style="margin: unset">{{str_replace(App\Models\HistoryLog::DATETIME_REPLACE_STRING, $historyLog->HL_Created_ON->format('d/m/Y H:i'), $log)}}.</p>
                        @endforeach
                    </td>
                <tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align: center">
                        No history found.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>