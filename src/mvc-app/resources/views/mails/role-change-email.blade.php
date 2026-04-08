@extends('layouts.mail-layout')
@section('content')
    <h2>Hi Admins,</h2>
    <p>The role of user {{ $userFullname }} was updated by {{ $requesterName }} from {{ $oldRoleName }} to {{ $newRoleName }} in {{ $teamsname }} on {{ date('jS M Y') }} at {{ date('H:i') }}</p>
    <br><br><br>
    <p align="left">Thanks</p>
@endsection
