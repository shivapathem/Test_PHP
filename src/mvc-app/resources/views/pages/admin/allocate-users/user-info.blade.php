{{--
User info tables
--}}
<div class="p-0" style="width:100%;">
    <div class="fieldsection">
        <div class="parant_div_1 fieldmargin" id="parant_div_1">
            <h2 style="margin: 12px 0 0 0; font-size: 14px; background-color: #cccccc; padding: 6px 8px;">
                Information for {{ $user->UD_DisplayName ?? '' }}
                ({{ $user->UD_NetLogin ?? '' }})
            </h2>
        </div>
    </div>
    <section class="section-margin" style="margin-top: 14px;">
        <div class="tables access-table" style="border: none; width: 100%;">
            <div class="scrollable" style="overflow-x: auto; overflow-y: auto; width: 100%;">
                <table
                    class="oddevenclass tablesmall stripe dataTable no-footer"
                    id="user-info"
                    role="grid"
                    aria-describedby="user-info_info">
                    <thead>
                        <tr class="headerSticky">
                            <th class="min200">Scheduling Team</th>
                            <th>Default</th>
                            @foreach($roleLists as $role)
                            <th>{{ $role->RoleName ?? '' }}</th>
                            @endforeach
                            <th>Home Team</th>
                        </tr>
                    </thead>

                    <tbody class="context-menu-one">
                        @if(!empty($user->userSetup))
                        @foreach($user->userSetup as $userdata)
                        <tr>
                            <td style="font-size: 8pt;">{{ $userdata->schedulingteamname }}</td>
                            <td style="text-align: center;">{!! $userdata->isdefault != 1 ? '<img src="/images/red_cross.png" alt="No" class="tick">' : '<img src="/images/green_tick.png" alt="Yes" class="tick">' !!}</td>
                            @foreach($roleLists as $role)
                            @php
                            $roleFlatten = strtolower(preg_replace('/\s+/', '_', $role['RoleName']));
                            $roleFlatten = $role['RoleName'] == 'Area Admin' ? 'divisionid' : $roleFlatten;
                            $roleUser = $userdata->{$roleFlatten} ?? '';
                            if($roleFlatten == 'scheduled_person' && $userdata->scheduledtype == 0) {
                            $roleUser = null;
                            }
                            @endphp
                            <td style="text-align: center;">{!! empty($roleUser) ? '<img src="/images/red_cross.png" alt="No" class="tick">' : '<img src="/images/green_tick.png" alt="Yes" class="tick">' !!}</td>
                            @endforeach
                            <td style="text-align: center;">{!! $userdata->home_team != 1 ? '<img src="/images/red_cross.png" alt="No" class="tick">' : '<img src="/images/green_tick.png" alt="Yes" class="tick">' !!}</td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="{{ count($roleLists) + 4 }}" class="filter_data_unavailabl">
                                No Record Found
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <br>
    <section>
        <div class="tables access-table sec-acces-table sec-width" style="border: none;">
            <div>
                <table
                    class="oddevenclass tablesmall stripe dataTable no-footer"
                    id="user-info2"
                    style="width: 100%;"
                    role="grid"
                    aria-describedby="user-info2_info"
                    style="margin-top: unset">
                    <thead class="headerSticky">
                        <tr>
                            <th>Leave &amp; Request Group(s)</th>
                            <th>Administrator</th>
                        </tr>
                    </thead>

                    <tbody class="context-menu-one">
                        @foreach($user->userLeaveGroup as $arrLeaveRequest)
                        <tr>
                            <td>{{ $arrLeaveRequest->Description }}</td>
                            <td class="center-text">
                                @php
                                $admin = $arrLeaveRequest->LeaveAdmin ?? 0;
                                $map = [
                                3 => [
                                'src' => '/images/purple_tick.png',
                                'title' => 'You may Leave manage the configuration of this Group',
                                'alt' => 'Leave Manager',
                                ],
                                2 => [
                                'src' => '/images/green_tick.png',
                                'title' => 'You may administer the configuration of this Group',
                                'alt' => 'Administrator',
                                ],
                                1 => [
                                'src' => '/images/yellow_tick.png',
                                'title' => 'You may approve Leave in this Group',
                                'alt' => 'Authoriser',
                                ],
                                0 => [
                                'src' => '/images/red_cross.png',
                                'title' => 'You may request Leave in this Group',
                                'alt' => 'Requester',
                                ],
                                ];

                                $icon = $map[$admin] ?? $map[0];
                                @endphp
                                <img
                                    title="{{ $icon['title'] }}"
                                    src="{{ $icon['src'] }}"
                                    alt="{{ $icon['alt'] }}"
                                    width="12"
                                    height="12">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>