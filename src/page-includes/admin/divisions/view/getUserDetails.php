<?php
session_start();
include_once '../process/classDivisionalAdmin.php';
include_once '../../../../function-includes/DB_Functions.php';

$divisionobj = new ClassDivisionalAdmin;
$user_id = $_REQUEST["user_id"];
$division_id = $_REQUEST["division_id"];
$getMainRoleLists = json_decode(getRoleLists('all'), true);
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
if (isset($_REQUEST['map'])) {
    $divisionobj->mapAddionalUser($user_id, $division_id, $_REQUEST['role_id'], $_REQUEST['role_name']);
}

$disabled = $_REQUEST["disable_class"] ?? 'noclass';

$user_id = $_REQUEST["user_id"];
$division_id = $_REQUEST["division_id"];
$userDetail = json_decode($divisionobj->getUserDetail($user_id), true);
$checkMapping = $divisionobj->checkUserMapping($user_id, $division_id);
$userDetail['UserId'] = $userDetail['UserId'] ?? '';
if ($userDetail && !empty($userDetail)) {
    $fullname = '';
    if (isset($userDetail['DisplayName']) && $userDetail['DisplayName']) {
        $fullname = $userDetail['DisplayName'];
    } else if (isset($userDetail['PreferredForename']) && $userDetail['PreferredForename']) {
        $fullname =   $userDetail['PreferredForename'] . ' ' . $userDetail['Surname'];
    } else {
        $fullname =   $userDetail['Forename'] . ' ' . $userDetail['Surname'];
    }
?>
    <tr>
        <td><?php echo !empty($userDetail['NetLogin']) ? $userDetail['NetLogin'] : (!empty($userDetail['UD_NetLogin']) ? $userDetail['UD_NetLogin'] : ''); ?></td>
        <td style="word-wrap: break-word; max-width: 120px;"><?php echo $fullname; ?></td>
        <td style="word-wrap: break-word; max-width: 60px;"><?php echo !empty($userDetail['Forename']) ? $userDetail['Forename'] : (!empty($userDetail['UD_DisplayFirstName']) ? $userDetail['UD_DisplayFirstName'] : ''); ?></td>
        <td style="word-wrap: break-word; max-width: 60px;"><?php echo !empty($userDetail['Surname']) ? $userDetail['Surname'] : (!empty($userDetail['UD_DisplayLastName']) ? $userDetail['UD_DisplayLastName'] : ''); ?></td>
        <td style="word-wrap: break-word; max-width: 120px;"><?php echo !empty($userDetail['InternalEmail']) ? $userDetail['InternalEmail'] : (!empty($userDetail['UD_InternalEmail']) ? $userDetail['UD_InternalEmail'] : ''); ?></td>
        <td><?php echo !empty($userDetail['EmpNumber']) ? $userDetail['EmpNumber'] : (!empty($userDetail['UD_EmpNumber']) ? $userDetail['UD_EmpNumber'] : ''); ?></td>
        <td>
            <select id="area-admin-role-add" data-user-id="<?php echo $userDetail['UserId']; ?>" <?php if (count($checkMapping) > 0) {echo 'disabled';} ?>>
                <option value="">Select Role</option>
                <?php
                $selectedRole = count($checkMapping) > 0 ? $checkMapping['UR_RoleID'] : 0;
                foreach ($getMainRoleLists as $getMainRoleList) {
                    if (($intSysAdmin && $getMainRoleList['RoleName'] == 'Area Admin') || $getMainRoleList['RoleName'] == 'Area Viewer') {
                        echo '<option value="' . $getMainRoleList['RoleID'] . '" ' . ($selectedRole == $getMainRoleList['RoleID'] ? 'selected' : '') . '>' . $getMainRoleList['RoleName'] . '</option>';
                    }
                } ?>
            </select>
        </td>
        <td><?php if (count($checkMapping) == 0) { ?><a href="javascript:void(0);" onclick="mapUser()" ;>Add</a><?php } else { ?>Added<?php } ?></td>
    </tr>
<?php } ?>

<script type="text/javascript">
    function mapUser() {
        if ($('#area-admin-role-add').val() == '') {
            alert('Select Role');
            return false;
        }
        var division_id = "<?php echo $division_id; ?>";
        var user_id = "<?php echo $user_id; ?>";
        var disable_class = '<?php echo $disabled; ?>';

        var hit_url = 'page-includes/admin/divisions/view/getUserDetails.php';

        $.post(hit_url, {
                user_id: user_id,
                division_id: division_id,
                map: true,
                role_id: $('#area-admin-role-add').val(),
                role_name: $('#area-admin-role-add option:selected').text(),
                disable_class: disable_class // Add this line
            },
            function(data, status) {
                $("#user-details").html(data);
                var hit_url = "page-includes/admin/divisions/view/getDivisonalMapList.php";

                $.post(hit_url, {
                        division_id: division_id,
                        disable_class: disable_class
                    },
                    function(data, status) {
                        $('#divisionlisting').dataTable().fnClearTable();
                        $('#divisionlisting').dataTable().fnDestroy();
                        $("#user-mapping-list").html(data);
                        let table = $('#divisionlisting').DataTable({
                            stateSave: true,
                            "bPaginate": false,
                            scrollY: 400,
                            "fnDrawCallback": function(oSettings) {
                                $("#divisionlisting_info").css('display', 'none');
                            },
                            dom: 'Bfrtip',
                            buttons: [{
                                extend: 'print',
                                text: '<img id="print" class="handcursor" name="print" border="0" src="images/print.png" style="top:10px; vertical-align:middle;" alt="Print" width="18px" height="17px">',
                                titleAttr: 'Print'
                            }],
                            "columnDefs": [{
                                    "orderable": false,
                                    "targets": 1
                                },
                                {
                                    "orderable": false,
                                    "targets": 3
                                },
                                {
                                    "orderable": false,
                                    "targets": 4
                                }
                            ]
                        });
                        yadcf.init(table, [{
                            column_number: 0,
                            filter_type: 'text'
                        }]);
                    });
            }
        );
    }
</script>