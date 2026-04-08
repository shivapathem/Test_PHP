<?php

use Illuminate\Support\Facades\Gate;

include_once __DIR__ . '/../function-includes/laravel_init.php';
/**
 * Get scheduling team list for select options html
 */
function getSchedulingTeamList(int $selectedTeam, string $policy, string $policyMethod)
{
    global $viewBlade;
    return $viewBlade->make('includes.select-options.user-scheduling-team-list', ['selectedTeam' => $selectedTeam, 'policy' => $policy, 'policyMethod' => $policyMethod])->render();
}

/**
 * Get scheduling team list as array
 */
function getSchedulingTeamListArray(string $policy, string $policyMethod)
{

    $user = auth()->user();
    $teams = [];
    foreach ($user->userAccessibleTeams(true)->get() as $team) {
        if (Gate::allows($policyMethod, [$policy, $team])) {
            $teams[] = [
                'id'   => $team->schedulingTeamId,
                'name' => $team->schedulingTeamName
            ];
        }
    }
    return $teams;
}
