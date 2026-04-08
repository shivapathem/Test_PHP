Dear {{ $adminPreferredForename }},

This is to inform you that the following duties have become Unallocated due to the removal of {{ $scheduledPersonName }} from the team:

@if(!empty($unallocatedDuties) && count($unallocatedDuties) > 0)
    <p>Unallocated Duties ({{ count($unallocatedDuties) }} - Require Reassignment)</p>

<table style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Date</th>
            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Duty Name</th>
        </tr>
    </thead>
    <tbody>
@foreach($unallocatedDuties as $duty)
        <tr>
            <td style="border: 1px solid #ddd; padding: 12px;">{{ \Carbon\Carbon::parse($duty['date'])->format('d-m-Y') }}</td>
            <td style="border: 1px solid #ddd; padding: 12px;">{{ $duty['name'] }}</td>
        </tr>
@endforeach
    </tbody>
</table>

<p style="color: #d9534f; font-weight: bold;"><strong>Action Required</strong>: These {{ count($unallocatedDuties) }} duties now require reassignment within your <strong>{{ $schedulingTeamName }}</strong> team. Please ensure coverage is promptly arranged.</p>

@else
<p><em>No duties have been moved to Unallocated status.</em></p>
@endif

@if(!empty($deletedDuties) && count($deletedDuties) > 0)
   <p>Deleted Duties ({{ count($deletedDuties) }} - No Coverage Action Needed)</p>

<table style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background-color: #dff0d8;">
            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Date</th>
            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Duty Name</th>
        </tr>
    </thead>
    <tbody>
@foreach($deletedDuties as $duty)
        <tr>
            <td style="border: 1px solid #ddd; padding: 12px;">{{ \Carbon\Carbon::parse($duty['date'])->format('d-m-Y') }}</td>
            <td style="border: 1px solid #ddd; padding: 12px;">{{ $duty['name'] }}</td>
        </tr>
@endforeach
    </tbody>
</table>

<p style="color: #5cb85c; font-weight: bold;">These {{ count($deletedDuties) }} duties were marked as "Doesn't Need Covering" and have been <strong>completely deleted</strong> from the system. No coverage action required.</p>

@else
<p><em>No duties were marked as "Doesn't Need Covering."</em></p>
@endif

If you have any questions regarding these removed duties, please contact your administrator.

Thank you,
Allocate7 System
