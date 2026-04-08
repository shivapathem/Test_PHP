@extends('layouts.mail-layout')
@section('content')
    Dear {{$recipient->UD_DisplayName}},<br>
    This is to inform you that Scheduling Group {{$schedulingGroup->SchedulingGroupsName}} has been deleted from Allocate.
@endsection
