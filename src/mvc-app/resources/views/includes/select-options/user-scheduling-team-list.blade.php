{{--Generates options of shceduling team based on user access--}}
@foreach (auth()->user()->userAccessibleTeams(true)->get() as $team)
    @can($policyMethod, [$policy, $team])
        <option value="{{$team->schedulingTeamId}}" @if($selectedTeam == $team->schedulingTeamId) selected @endif>
            {{$team->schedulingTeamName}}
        </option>
    @endcan
@endforeach