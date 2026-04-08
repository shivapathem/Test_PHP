<?php
session_start();
include_once '../process/classDivisionalAdmin.php';
include_once '../../../../function-includes/DB_Functions.php';
include_once '../../../users/process/classUserSetup.php';

$divisionobj = new ClassDivisionalAdmin;
$setupObj = new classUserSetup();
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$division_id = $_REQUEST["division_id"];
$disabled = $_REQUEST["disable_class"] ?? 'noclass';

$intSysAdmin =  $_SESSION['user']['SysAdmin'];

$haveAccess = $intSysAdmin == 1 ? true : false;
if (!empty($userDivisionsList) && !$haveAccess) {
    foreach ($userDivisionsList as $userDivision) {
        if ($userDivision['DivisionID'] == $division_id) {
            $haveAccess = true;
        }
    }
}

$disableReportClass = $haveAccess == false ? " notclickable buttonDisabled " : '';

if (isset($_REQUEST['unmap']) && $_REQUEST['unmap'] && isset($_REQUEST['DivisionalAdminId']) && $_REQUEST['DivisionalAdminId']) {
    $divisionobj->unmapAddionalUser($_REQUEST['division_id'], $_REQUEST['userId']);
}
if (isset($_REQUEST['changeRole']) && $_REQUEST['changeRole'] && isset($_REQUEST['DivisionalAdminId']) && $_REQUEST['DivisionalAdminId']) {
    $divisionobj->changeAreaAdminRole($_REQUEST['DivisionalAdminId'], $_REQUEST['roleId'], $_REQUEST['userId'], $division_id, $_REQUEST['oldRoleID']);
}
if((isset($_REQUEST['changeAdditionalRole'])) && ($_REQUEST['changeAdditionalRole']) && (isset($_REQUEST['DivisionalAdminId'])) && ($_REQUEST['DivisionalAdminId'])) {
    $divisionobj->changeAdditionalRole($_REQUEST['DivisionalAdminId'], $_REQUEST['roleId'], $_REQUEST['userId'], $division_id, $_REQUEST['action']);
}
$roleListJson = getRoleLists('all') ?? '';
$getMainRoleLists = json_decode($roleListJson, true);
$userListJson = $divisionobj->getDivisonalMapList($division_id)  ?? '';
$userList = json_decode($userListJson, true);
$divisionListJson = $divisionobj->getDivisionalAdditionalRoleMapList($division_id)  ?? '';
$userAreaAdminAdditonalRoles = json_decode($divisionListJson, true);
$userAdditionalRole = [];
foreach ($userAreaAdminAdditonalRoles ?? [] as $userAreaAdminAdditonalRole) {
    $userAdditionalRole[$userAreaAdminAdditonalRole['UserID']][$userAreaAdminAdditonalRole['RoleName']] = $userAreaAdminAdditonalRole['RoleID'];
}

if ($division_id) {
    if ($userList && !empty($userList)) {
        foreach ($userList as $user) {
            $fullname = $clickdisable='';
            if (isset($user['DisplayName']) && $user['DisplayName']) {
                $fullname = $user['DisplayName'];
            } else if (isset($user['PreferredForename']) && $user['PreferredForename']) {
                $fullname =   $user['PreferredForename'] . ' ' . $user['Surname'];
            } else {
                $fullname =   $user['Forename'] . ' ' . $user['Surname'];
            }
?>
            <tr>
                <td><?php echo $fullname; ?></td>
                <td><?php echo $user['StaffNumber']; ?></td>

                <td><?php echo $user['NetLogin']; ?></td>
                <td>
                    <?php if ($intSysAdmin == 1) { ?>
                        <select class="area-admin-role" data-user-id="<?php echo $user['UserID']; ?>" data-division-admin-id="<?php echo $user['UserRoleID']; ?>" data-division-oldrole-id="<?php echo $user['RoleID']; ?>">
                            <?php
                            //Only system admin can change role
                            $areaReportRoleId = $areaFacilitytRoleId  = 0;
                            foreach ($getMainRoleLists as $getMainRoleList) {
                                if (($getMainRoleList['RoleName'] == 'Area Admin') || $getMainRoleList['RoleName'] == 'Area Viewer') {
                                    echo '<option value="' . $getMainRoleList['RoleID'] . '"
                        ' . ($user['RoleID'] == $getMainRoleList['RoleID'] ? 'selected' : '') . '

                        >' . $getMainRoleList['RoleName'] . '</option>';
                                }
                                if ($getMainRoleList['RoleName'] == 'Area Reports') {
                                    $areaReportRoleId = $getMainRoleList['RoleID'];
                                }

                                if($getMainRoleList['RoleName'] == 'Facility Administrator') {
                                    $areaFacilitytRoleId = $getMainRoleList['RoleID'];
                                }
                                if($getMainRoleList['RoleName'] == 'Area Admin' && $user['RoleID'] == $getMainRoleList['RoleID']) {
                                    $clickdisable = "notclickable";
                                }
                            } ?>
                        </select>
                    <?php } else {
                        $areaReportRoleId  = $areaFacilitytRoleId  = 0;
                        foreach ($getMainRoleLists as $getMainRoleList) {
                            if (($getMainRoleList['RoleName'] == 'Area Admin' || $getMainRoleList['RoleName'] == 'Area Viewer') && $user['RoleID'] == $getMainRoleList['RoleID']) {
                                echo  $getMainRoleList['RoleName'];
                            }
                            if ($getMainRoleList['RoleName'] == 'Area Reports') {
                                $areaReportRoleId = $getMainRoleList['RoleID'];
                            }
                            if($getMainRoleList['RoleName'] == 'Facility Administrator') {
                                $areaFacilitytRoleId = $getMainRoleList['RoleID'];
                            }
                            if($getMainRoleList['RoleName'] == 'Area Admin' && $user['RoleID'] == $getMainRoleList['RoleID']) {
                                $clickdisable = "notclickable";
                            }
                        }
                    } ?>
                </td>
                <td>
                <?php
                    echo !isset($userAdditionalRole[$user['UserID']]['Facility Administrator']) ?
                    '<span class="imgtransparent">0</span><div class="tick areaAdditionalOption crossRedAreaAdmin ' . $clickdisable . '"   data-user-id="' . $user['UserID'] . '" data-division-admin-id="' . $user['UserRoleID'] . '" data-area-additional-role-id="' . $areaFacilitytRoleId . '" data-action="enable" " data-permission="facility"></div>' : '<span class="imgtransparent">1</span><div class="tick areaAdditionalOption crossGreenAreaAdmin ' . $clickdisable . '" data-user-id="' . $user['UserID'] . '" data-division-admin-id="' . $user['UserRoleID'] . '" data-area-additional-role-id="' . $areaFacilitytRoleId . '" data-action="disable"  data-permission="facility"></div>';
                ?>
                </td>
                <td>
                    <?php
                    echo !isset($userAdditionalRole[$user['UserID']]['Area Reports']) ?
                    '<span class="imgtransparent">0</span><div class="tick areaAdditionalOption crossRedAreaAdmin ' . $disableReportClass . '"   data-user-id="' . $user['UserID'] . '" data-division-admin-id="' . $user['UserRoleID'] . '" data-area-additional-role-id="' . $areaReportRoleId . '" data-action="enable" data-permission="report"></div>' : '<span class="imgtransparent">1</span><div class="tick areaAdditionalOption crossGreenAreaAdmin ' . $disableReportClass . '" data-user-id="' . $user['UserID'] . '" data-division-admin-id="' . $user['UserRoleID'] . '" data-area-additional-role-id="' . $areaReportRoleId . '" data-action="disable" data-permission="report"></div>';
                    ?>
                </td>
                <td><span>
                        <a title="History" id="historydivisionalAdmin"
                            data-historydivisionId=<?php echo $user['UserRoleID']; ?> href="javascript:void(0);"><em
                                class="fa fa-hourglass-3"></em></a></span></td>
                                     <td><a title="Remove" href="javascript:void(0);" class="<?php echo $disabled; ?>" onclick="unMapUser('<?php echo $user['DivisionId'] . "','" . $user['UserID']; ?>')">Remove</a></td>
            </tr>
        <?php }
    }
} ?>

<script type="text/javascript">
    function unMapUser(DivisionalAdminId, userId) {
        var division_id = "<?php echo $division_id; ?>";
        var disable_class = "<?php echo $disabled; ?>";

        var hit_url = 'page-includes/admin/divisions/view/getDivisonalMapList.php';

        $.post(hit_url, {
                DivisionalAdminId: DivisionalAdminId,
                userId: userId,
                unmap: true,
                division_id: division_id,
                disable_class: disable_class
            },
            function(data, status) {
                $('#divisionlisting').dataTable().fnClearTable();
                $('#divisionlisting').dataTable().fnDestroy();
                $("#user-mapping-list").html(data);
                let table = $('#divisionlisting').DataTable({
                    stateSave: true,
                    "fnDrawCallback": function(oSettings) {
                        $("#divisionlisting_info").css('display', 'none');
                        if ($('#divisionlisting tr').length >= 11) {
                            $("#divisionlisting_paginate").css('visibility', 'visible');
                            $("#divisionlisting_paginate ").css('font-size', '12px');
                        }
                    },
                    "columnDefs": [{
                            "orderable": true,
                            "targets": 1
                        },
                        {
                            "orderable": true,
                            "targets": 3
                        },
                        {
                            "orderable": true,
                            "targets": 4
                        },
                        {
                            "orderable": true,
                            "targets": 5
                        }
                    ],
                    drawCallback: function (settings) {
                        if(division_id == 0) {
                            $('.dataTables_empty').text("Please apply the filter");
                        }
                    }
                });
                hideFacilityAdminColumn(table);
                yadcf.init(table, [{
                    column_number: 0,
                    filter_type: 'text'
                }]);

            }
        );
    }

    $(document).ready(function() {
        $(".area-admin-role").on("change", function() {
            areaAdminChangeRole($(this));
        });
        $(".areaAdditionalOption").on("click", function() {
            areaAdditionalRoleUpdate($(this));
        })
    });

    function areaAdminChangeRole($select) {
        var division_id = "<?php echo $division_id; ?>";
        var disable_class = "<?php echo $disabled; ?>";

        var hit_url = 'page-includes/admin/divisions/view/getDivisonalMapList.php';
        $.post(hit_url, {
                DivisionalAdminId: $select.attr('data-division-admin-id'),
                changeRole: true,
                division_id: division_id,
                disable_class: disable_class,
                roleId: $select.val(),
                userId: $select.attr('data-user-id'),
                oldRoleID: $select.attr('data-division-oldrole-id')
            },
            function(data, status) {
                $('#divisionlisting').dataTable().fnClearTable();
                $('#divisionlisting').dataTable().fnDestroy();
                $("#user-mapping-list").html(data);
                let table = $('#divisionlisting').DataTable({
                    stateSave: true,
                    "fnDrawCallback": function(oSettings) {
                        $("#divisionlisting_info").css('display', 'none');
                        if ($('#divisionlisting tr').length >= 11) {
                            $("#divisionlisting_paginate").css('visibility', 'visible');
                            $("#divisionlisting_paginate ").css('font-size', '12px');
                        }
                    },
                    "columnDefs": [{
                            "orderable": true,
                            "targets": 1
                        },
                        {
                            "orderable": true,
                            "targets": 3
                        },
                        {
                            "orderable": true,
                            "targets": 4
                        },
                        {
                            "orderable": true,
                            "targets": 5
                        }
                    ],
                    drawCallback: function (settings) {
                        if(division_id == 0) {
                            $('.dataTables_empty').text("Please apply the filter");
                        }
                    }
                });
                hideFacilityAdminColumn(table);
                yadcf.init(table, [{
                    column_number: 0,
                    filter_type: 'text'
                }]);

            }
        );
    }

    function areaAdditionalRoleUpdate($element) {
        var division_id = "<?php echo $division_id; ?>";
        var disable_class = "<?php echo $disabled; ?>";
        var hit_url = 'page-includes/admin/divisions/view/getDivisonalMapList.php';

        $.post(hit_url, {
                DivisionalAdminId: $element.attr('data-division-admin-id'),
                changeAreaReportRole: true,
                additionroletype : $element.attr('data-permission'),
                changeAdditionalRole : true,
                division_id: division_id,
                disable_class: disable_class,
                action: $element.attr('data-action'),
                roleId: $element.attr('data-area-additional-role-id'),
                userId: $element.attr('data-user-id')
            },
            function(data, status) {
                $('#divisionlisting').dataTable().fnClearTable();
                $('#divisionlisting').dataTable().fnDestroy();
                $("#user-mapping-list").html(data);
                let table = $('#divisionlisting').DataTable({
                    stateSave: true,
                    "fnDrawCallback": function(oSettings) {
                        $("#divisionlisting_info").css('display', 'none');
                        if ($('#divisionlisting tr').length >= 11) {
                            $("#divisionlisting_paginate").css('visibility', 'visible');
                            $("#divisionlisting_paginate ").css('font-size', '12px');
                        }
                    },
                    "columnDefs": [{
                            "orderable": true,
                            "targets": 1
                        },
                        {
                            "orderable": true,
                            "targets": 3
                        },
                        {
                            "orderable": true,
                            "targets": 4
                        },
                        {
                            "orderable": true,
                            "targets": 5
                        }
                    ],
                    drawCallback: function (settings) {
                        if(division_id == 0) {
                            $('.dataTables_empty').text("Please apply the filter");
                        }
                    }
                });
                hideFacilityAdminColumn(table);
                yadcf.init(table, [{
                    column_number: 0,
                    filter_type: 'text'
                }]);
            }
        );
    }
    /**
     * Hide facility administrator role
     */
    function hideFacilityAdminColumn(table) {
        var columnIndexToHide = $('#divisionlisting thead th').filter(function () {
            return $(this).text().trim() === 'Facility Administrator';
        }).index();
        //Hide facility administrator role
        !window.facilityBookingEnabled ? table.column(columnIndexToHide).visible(false) :  table.column(columnIndexToHide).visible(true);
    }
</script>