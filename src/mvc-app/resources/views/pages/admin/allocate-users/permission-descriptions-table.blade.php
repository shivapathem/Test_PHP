@php
    use App\Models\User\RolePermissionStatus;
    use App\Models\User\RefRole;
@endphp

<div class="Searchcontainer Pos-Searchcontainer">
    <div class="tableheadersmall medtextboldcentre screen-fix-withscroll"
        style="margin-bottom: 10px;text-align: center;padding: 8px;">
        <h2 style="margin: 0px 0px 4px 0px;font-size: 8pt;">Permission Descriptions</h2>
        <span style="font-weight: 600;">Displaying permission descriptions</span>
    </div>
    <div class="table-permission-descriptions-container" style="overflow-x: auto;">
        <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="permissionDescriptionsLists"
            role="table" aria-label="Permission Descriptions">
            <thead>
                <tr class="headerSticky" style="font-size: 11px;">
                    <th scope="col">
                        <div class="header-wrapper">
                            <div class="header-text">Permission</div>
                        </div>
                    </th>
                    <th scope="col">
                        <div class="header-wrapper">
                            <div class="header-text">Default</div>
                            <div class="header-icon">
                                <i class="fa fa-info-circle" style="cursor: pointer; font-size: 10px;"
                                    title="Allows STA to set the default team for the Allocate user which will open when team level shortcut icons or menu items are used, for instance 'Weekly Allocations'"></i>
                            </div>
                        </div>
                    </th>
                    <th scope="col">
                        <div class="header-wrapper">
                            <div class="header-text">View All Allocations</div>
                            <div class="header-icon">
                                <i class="fa fa-info-circle" style="cursor: pointer; font-size: 10px;"
                                    title="Having any permission in a Scheduling Team allows the user to view the allocations for that team. Scheduling Team Viewers and Scheduled People only see masked allocations. Masking settings are in the Scheduling Team setup. Scheduling Team Viewers and Scheduled People who also have ‘Manager’ permissions can see all published allocations beyond the masking point"></i>
                            </div>
                        </div>
                    </th>
                    @foreach($additionalRoles as $additionalRole)
                        @php
                            $hideInfoIcon = in_array($additionalRole->RoleName, [
                                RefRole::EDIT_ALL_ALLOCATIONS,
                                RefRole::EDIT_MASTER_DUTIES,
                                RefRole::EDIT_ROTA_PATTERNS,
                                RefRole::EDIT_LEAVE_CREDITS,
                                RefRole::MOVE_PERSON_BETWEEN_TEAMS,
                                RefRole::CREATE_NEW_FREELANCER,
                                RefRole::CREATE_NEW_STAFF,
                                RefRole::GIVE_TEAM_PERMISSIONS,
                                RefRole::EDIT_TEAM_SETTINGS,
                                RefRole::CREATE_NEW_TEAM,
                                RefRole::CREATE_NEW_GROUP,
                                RefRole::EDIT_MASTER_DUTY_COLOURS,
                                RefRole::CREATE_NEW_AREA,
                                RefRole::EDIT_SYSTEM_SETTINGS
                            ]);
                        @endphp
                        <th scope="col">
                            <div class="header-wrapper">
                                <div class="header-text">{{ $additionalRole->RoleName }}</div>
                                @if(!$hideInfoIcon && $additionalRole->RoleDescription)
                                    <div class="header-icon">
                                        <i class="fa fa-info-circle" style="cursor: pointer; font-size: 10px;"
                                            title="{{ $additionalRole->RoleDescription }}"></i>
                                    </div>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="context-menu-one" id="permission-descriptions-table-body">
                @foreach($mainRoles as $mainRole)
                    <tr class="headerSticky" style="font-size: 11px;">
                        <td>
                            {{ $mainRole->RoleName }}
                            <i class="fa fa-info-circle" style="cursor: pointer; margin-left: 5px;"
                                title="{{ $mainRole->RoleDescription ?? $mainRole->RoleName }}"></i>
                        </td>
                        <td>
                            <div class="tick staffcrossRed"></div>
                        </td>
                        <td>
                            <div class="tick staffcrossGreen grayscale-icon"></div>
                        </td>
                        @foreach($additionalRoles as $additionalRole)
                            @php
                                $entry = $permissionMatrix[$mainRole->RoleID][$additionalRole->RoleID] ?? ['key' => RolePermissionStatus::NA, 'description' => null];
                                $status = is_array($entry) ? $entry['key'] : $entry;
                                $permissionDescription = is_array($entry) ? ($entry['description'] ?? null) : null;
                                $iconClass = '';
                                $icon = '';

                                switch ($status) {
                                    case RolePermissionStatus::MANDATORY:
                                        $iconClass = 'green-tick';
                                        $icon = '<div class="tick staffcrossGreen grayscale-icon"></div>';
                                        break;
                                    case RolePermissionStatus::OPTIONAL:
                                        $iconClass = 'red-cross';
                                        $icon = '<div class="tick staffcrossRed"></div>';
                                        break;
                                    case RolePermissionStatus::NA:
                                        $iconClass = 'grey-cross';
                                        $icon = '<div class="tick staffcrossRed grayscale-icon"></div>';
                                        break;
                                    case RolePermissionStatus::CONDITIONAL:
                                        $iconClass = 'red-cross';
                                        $icon = '<span class="bracket-container"><div class="tick staffcrossRed" style="top: 0;"></div></span>';
                                        break;
                                    case RolePermissionStatus::TICK_CONDITIONAL:
                                        $iconClass = 'grey-tick';
                                        $icon = '<span class="bracket-container"><div class="tick staffcrossGreen grayscale-icon" style="top: 0;"></div></span>';
                                        break;
                                    default:
                                        $iconClass = 'grey-cross';
                                        $icon = '<div class="tick staffcrossRed grayscale-icon"></div>';
                                }
                            @endphp
                            <td>
                                {!! $icon !!}
                                @if(in_array($status, [RolePermissionStatus::CONDITIONAL, RolePermissionStatus::TICK_CONDITIONAL]) && $permissionDescription)
                                    <i class="fa fa-info-circle" style="font-size: 9px; margin-left: 2px; vertical-align: middle; cursor: pointer; color: #5b9bd5;"
                                        title="{{ $permissionDescription }}"></i>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="permission-descriptions-key">
        <table>
            <thead>
                <tr>
                    <th colspan="2"
                        style="background-color: #d0d0d0; padding: 4px 8px; text-align: left; border: none; font-size: 13px;">
                        Key</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: none;">
                        <div class="tick staffcrossRed grayscale-icon"></div>
                    </td>
                    <td style="border: none;">Not available for this permission Role</td>
                </tr>
                <tr>
                    <td style="border: none;">
                        <div class="tick staffcrossRed"></div>
                    </td>
                    <td style="border: none;">Optional for this permission Role and permission <strong>not</strong>
                        given</td>
                </tr>
                <tr>
                    <td style="border: none;"><span class="bracket-container">
                            <div class="tick staffcrossRed"></div>
                        </span> <span class="bracket-container">
                            <div class="tick staffcrossGreen grayscale-icon"></div>
                        </span></td>
                    <td style="border: none;">Optional, but only in conjunction with Team Leader permissions
                    </td>
                </tr>
                <tr>
                    <td style="border: none;">
                        <div class="tick staffcrossGreen grayscale-icon"></div>
                    </td>
                    <td style="border: none;">Mandatory for this permission Role</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>