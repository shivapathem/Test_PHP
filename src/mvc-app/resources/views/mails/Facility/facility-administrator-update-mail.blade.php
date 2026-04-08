@extends('layouts.mail-layout')
@section('content')
        Hi,
        <br>
        <br>
        @if ($existingFacilityAdmins->diff($newFacilityAdmins)->count() > 0 || $newFacilityAdmins->diff($existingFacilityAdmins)->count() > 0)
            @if ($newFacilityAdmins->count() == 0)
                {{__('Facility Administrator removed :data1 by :user on :facility .', ['data1' => $existingFacilityAdmins->pluck('UD_DisplayName')->implode(', '), 'user' => $user->UD_DisplayName, 'facility' => $facility->FC_FacilityName])}}
            @elseif ($existingFacilityAdmins->count() == 0)
                {{__('Facility Administrator updated as :data2 by :user on :facility .', ['data2' => $newFacilityAdmins->pluck('UD_DisplayName')->implode(', '), 'user' => $user->UD_DisplayName, 'facility' => $facility->FC_FacilityName])}}
            @else
                {{__('Facility Administrator changed from :data1 to :data2 by :user on :facility .', ['data1' => $existingFacilityAdmins->pluck('UD_DisplayName')->implode(', '), 'data2' => $newFacilityAdmins->pluck('UD_DisplayName')->implode(', '), 'user' => $user->UD_DisplayName, 'facility' => $facility->FC_FacilityName])}}
            @endif
        @endif
        <br>
        <br>
@endsection
