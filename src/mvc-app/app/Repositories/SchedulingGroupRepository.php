<?php

namespace App\Repositories;

use App\Models\Scheduling\SchedulingGroup;
use App\Models\Scheduling\Division;
use App\Models\Scheduling\SchedulingTeam;
use App\Models\HistoryLog;
use App\Models\User;
use App\Mail\SchedulingGroupDeletedMail;
use App\Repositories\Contracts\SchedulingGroupRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SchedulingGroupRepository implements SchedulingGroupRepositoryInterface
{
    /**
     * Get all scheduling groups
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSchedulingGroups()
    {
        return SchedulingGroup::with(['area', 'schedulingTeams', 'createdBy', 'updatedBy'])->orderBy('CreatedDate', 'DESC')->get();
    }

    /**
     * Get scheduling groups for a specific area (division)
     *
     * @param int $divisionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSchedulingGroupsByDivision($divisionId)
    {
        return SchedulingGroup::with(['area', 'schedulingTeams'])
            ->where('DivisionID', $divisionId)
            ->get();
    }

    /**
     * Save a new scheduling group
     *
     * @param array $data
     * @param \App\Models\User $user
     * @return \App\Models\Scheduling\SchedulingGroup
     */
    public function saveSchedulingGroup($data, $user)
    {
        DB::beginTransaction();
        try {
            $historyData = [];
            $schedulingGroup = new SchedulingGroup();
            $schedulingGroup->SchedulingGroupsName = $data['group_name'];
            $schedulingGroup->DivisionID = $data['divisionid'];
            $schedulingGroup->CreatedBy = $user->UD_UserID ?? null;
            $schedulingGroup->UpdatedBy = $user->UD_UserID ?? null;
            $schedulingGroup->IsIncludeINMenu = $data['allocations_menu'];
            $schedulingGroup->Notes = $data['notes'];

            $schedulingGroup->save();

            // Store history with detailed information
            $areaName = optional($schedulingGroup->area)->DivisionName ?? 'None';
            $allocationsMenu = $schedulingGroup->IsIncludeINMenu ? 'Yes' : 'No';
            $notes = $schedulingGroup->Notes ?: 'None';

            $historyData = [__('Scheduling Group ":group" created in Area ":area" allocations menu ":allocations_menu", notes ":notes" by :user on :datetime', [
                    'group' => $schedulingGroup->SchedulingGroupsName,
                    'area' => $areaName,
                    'allocations_menu' => $allocationsMenu,
                    'notes' => $notes,
                    'user' => $user->UD_DisplayName,
                    'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                ])
            ];
            $this->storeSchedulingGroupHistory($schedulingGroup, $historyData, $user);

            DB::commit();
            return $schedulingGroup;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing scheduling group
     *
     * @param \App\Models\Scheduling\SchedulingGroup $schedulingGroup
     * @param array $data
     * @param \App\Models\User $user
     * @return \App\Models\Scheduling\SchedulingGroup
     */
    public function updateSchedulingGroup($schedulingGroup, $data, $user)
    {
        DB::beginTransaction();
        try {
            $history = [];

            // Check for name change
            if ($schedulingGroup->getOriginal('SchedulingGroupsName') !== $data['group_name']) {
                $history[] = __('name from ":old" to ":new"', [
                    'old' => $schedulingGroup->getOriginal('SchedulingGroupsName'),
                    'new' => $data['group_name']
                ]);
            }

            // Check for division change
            if ($schedulingGroup->getOriginal('DivisionID') != $data['divisionid']) {
                $oldDivision = Division::find($schedulingGroup->getOriginal('DivisionID'))->DivisionName ?? 'None';
                $newDivision = Division::find($data['divisionid'])->DivisionName ?? 'None';
                $history[] = __('area from ":old" to ":new"', [
                    'old' => $oldDivision,
                    'new' => $newDivision
                ]);
            }

            // Check for allocations_menu change
            if ($schedulingGroup->getOriginal('IsIncludeINMenu') != $data['allocations_menu']) {
                $oldAllocationsMenu = $schedulingGroup->getOriginal('IsIncludeINMenu') ? 'Yes' : 'No';
                $newAllocationsMenu = $data['allocations_menu'] ? 'Yes' : 'No';
                $history[] = __('allocations menu from ":old" to ":new"', [
                    'old' => $oldAllocationsMenu,
                    'new' => $newAllocationsMenu
                ]);
            }

            // Check for notes change
            if ($schedulingGroup->getOriginal('Notes') !== $data['notes']) {
                $history[] = __('notes from ":old" to ":new"', [
                    'old' => $schedulingGroup->getOriginal('Notes') ?: 'None',
                    'new' => $data['notes'] ?: 'None'
                ]);
            }
            $schedulingGroup->fill([
                'SchedulingGroupsName' => $data['group_name'],
                'DivisionID' => $data['divisionid'],
                'IsIncludeINMenu' => $data['allocations_menu'] ?? 0,
                'Notes' => $data['notes'] ?? null,
                'UpdatedBy' => $user->UD_UserID,
            ]);
            $schedulingGroup->save();

            if (!empty($history)) {
                $changeText = implode(', ', $history);
                $areaName = $schedulingGroup->area->DivisionName ?? 'None';
                $historyData = [__('Scheduling Group ":group" updated in Area ":area" - changed :changes by :user on :datetime', [
                        'group' => $schedulingGroup->SchedulingGroupsName,
                        'area' => $areaName,
                        'changes' => $changeText,
                        'user' => $user->UD_DisplayName,
                        'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                    ])
                ];
                $this->storeSchedulingGroupHistory($schedulingGroup, $historyData, $user);
            }

            DB::commit();
            return $schedulingGroup;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a scheduling group
     *
     * @param \App\Models\Scheduling\SchedulingGroup $schedulingGroup
     * @param \App\Models\User $user
     * @return bool
     */
    public function destroySchedulingGroup($schedulingGroup, $user)
    {
        DB::beginTransaction();
        try {
            // Store detailed history for deletion
            $areaName = $schedulingGroup->area->DivisionName ?? 'None';
            $allocationsMenu = $schedulingGroup->IsIncludeINMenu ? 'Yes' : 'No';
            $notes = $schedulingGroup->Notes ?: 'None';
            $historyData = [__('Scheduling Group ":group" deleted from Area ":area" with allocations menu ":allocations_menu", notes ":notes" by :user on :datetime', [
                    'group' => $schedulingGroup->SchedulingGroupsName,
                    'area' => $areaName,
                    'allocations_menu' => $allocationsMenu,
                    'notes' => $notes,
                    'user' => $user->UD_DisplayName,
                    'datetime' => HistoryLog::DATETIME_REPLACE_STRING
                ])
            ];
            $this->storeSchedulingGroupHistory($schedulingGroup, $historyData, $user);

            // Soft delete the scheduling group
            $schedulingGroup->delete();

            DB::commit();

            // Send notification
            $this->sendSchedulingGroupDeletedNotifications($schedulingGroup, $user);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get scheduling teams for a specific area (division)
     *
     * @param \App\Models\Scheduling\Division $area
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAreaTeams($area)
    {
        return $area->schedulingTeam;
    }

    /**
     * Store Scheduling Group history
     *
     * @param SchedulingGroup $schedulingGroup
     * @param array $historyLog
     * @param User $user
     */
    private function storeSchedulingGroupHistory(SchedulingGroup $schedulingGroup, array $historyLog, User $user): ?HistoryLog
    {
        if (empty($historyLog)) {
            return null;
        }
        // Ensure HL_AttributeID is valid
        $schedulingGroupId = $schedulingGroup->SchedulingGroupsID ?? null;
        if ($schedulingGroupId === null) {
            throw new \Exception("HL_AttributeID is required but is null.");
        }
        $history = new HistoryLog();
        $history->HL_HLogs = $historyLog;
        $history->HL_Created_BY = $user->UD_UserID;
        $history->HL_Type = HistoryLog::SCHEDULING_GROUP_HISTORY;
        $history->HL_AttributeID = $schedulingGroup->SchedulingGroupsID;
        $history->save();
        return $history;
    }

    /**
     * Get notification recipients for scheduling group
     * Get Area Admins for the division
     * @param SchedulingGroup $schedulingGroup
     * @return \Illuminate\Support\Collection
     */
    private function getSchedulingGroupNotificationRecipients(SchedulingGroup $schedulingGroup)
    {
        $areaAdmins = User::join('UserRoles', 'UserDetails.UD_UserID', '=', 'UserRoles.UR_UserID')
            ->join('REF_Roles', 'UserRoles.UR_RoleID', '=', 'REF_Roles.RoleID')
            ->join('Divisions', 'UserRoles.UR_DivisionId', '=', 'Divisions.DivisionID')
            ->where('Divisions.DivisionID', $schedulingGroup->DivisionID)
            ->where('REF_Roles.RoleName', 'Area Admin')
            ->select('UserDetails.*')
            ->get();

        // Get STAs (Scheduling Team Admins) for the scheduling teams in the group
        $schedulingTeamIds = $schedulingGroup->schedulingTeams->pluck('schedulingTeamId')->toArray();
        $stas = collect();

        if (!empty($schedulingTeamIds)) {
            // Get users who are Scheduling Team Admins, Senior Schedulers, or Schedulers for the teams in the group
            $staUsers = User::join('UserRoles', 'UserDetails.UD_UserID', '=', 'UserRoles.UR_UserID')
                ->join('REF_Roles', 'UserRoles.UR_RoleID', '=', 'REF_Roles.RoleID')
                ->whereIn('UserRoles.UR_SchedulingTeamID', $schedulingTeamIds)
                ->whereIn('REF_Roles.RoleName', ['Scheduling Team Admin', 'Senior Scheduler', 'Scheduler'])
                ->whereDate('UserRoles.UR_EndDate', '>=', now())
                ->select('UserDetails.*')
                ->distinct()
                ->get();

            $stas = $staUsers;
        }
        return $areaAdmins->merge($stas)->unique('UD_UserID');
    }

    /**
     * Send notification emails for scheduling group deletion
     *
     * @param SchedulingGroup $schedulingGroup
     * @param User $deletedBy
     */
    private function sendSchedulingGroupDeletedNotifications(SchedulingGroup $schedulingGroup, User $deletedBy)
    {
        $recipients = $this->getSchedulingGroupNotificationRecipients($schedulingGroup);

        // Send emails to all recipients
        foreach ($recipients as $recipient) {
            if ($recipient->UD_InternalEmail) {
                Mail::to($recipient->UD_InternalEmail)->send(new SchedulingGroupDeletedMail($schedulingGroup, $deletedBy, $recipient));
            }
        }
    }
}
