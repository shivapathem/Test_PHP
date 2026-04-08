<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//Scheduled Person
include_once '../../../../function-includes/testaccess.php';
include_once '../../../../class-includes/userRolePermissions.php';
include_once '../process/classDivisionalAdmin.php';
include_once '../../../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
if (($intSysAdmin == 1) || !empty($userDivisionsList) || ($arrUsersTeamdata["isSchedulingTeamAdmin"] == 1) || ($arrUsersTeamdata["isScheduler"] == 1)) {

    //call the class object
    $divisionobj = new ClassDivisionalAdmin;
    //defien the variable
    $pageid = 15;
    $divisionLists = json_decode($divisionobj->getDivisionsList(), true);

    // Call User Permission function.
    $activeclass = $disabled  = 'noclass';
    $permissions = getUserRolePermissions($pageid);
    if ($permissions->canmodify == 0) {
        $activeclass = 'activeclass';
        $disabled = 'notclickable buttonDisabled  ';
    }
    if ($permissions->cancreate == 0) {
        $activeclass = 'activeclass';
    }
?>

    <div id="searchScheduleperson" class="poswithheight">
        <div class="Searchcontainer p-0">

            <div class="fieldsection">
                <div class="parant_div_1" id="parant_div_1">
                    <div class="no-border additonaltemtd">
                        <?php if (($intSysAdmin == 1) || !empty($userDivisionsList)) { ?>
                            <button class="nonscheduledStaffbtn  btn-class addbtn" onclick="CallPopUp()" ; id="js_addnonscheduledstaff">
                                <img src="../images/button_add.png" alt="">&nbsp;Allocate User&nbsp;&nbsp;
                            </button>
                        <?php } ?>
                        <form class="stfform">
                            <div class="fields">
                                <label for="hometeam" id="w-120">&nbsp;&nbsp;Area:</label>
                                <select name="hometeam" class="division-seclect" id="hometeam">
                                    <option value="0">Select Area</option>
                                    <?php if (!empty($divisionLists)) {
                                        foreach ($divisionLists as $division) {
                                            if ($division['isActive']) { ?>
                                                <option value="<?php echo $division['DivisionID']; ?>"><?php echo  trim(substr($division['DivisionName'], offset: 0, length: 30)) ?></option>
                                    <?php }
                                        }
                                    } ?>
                                </select>
                            </div>
                        </form>
                    </div>
               </div>

		</div>
            <div class="tableheadersmall medtextboldcentre poswithwidth"><br><h2>Area Admin</h2>
                Displaying a list of Area Admins<br><br>
            </div>
            <section class="">
                <div class="tables access-table tablepos">
                    <div class="divadmintableUI hight100percent pos-height-relative divistiontableScrollbar">
                        <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer width100percent" id="divisionlisting"
                            role="grid" aria-describedby="divisionlisting_info">
                            <thead>
                                <tr class="headerSticky">
                                    <th>Full Name</th>
                                    <th>Staff Number</th>
                                    <th>Network ID</th>
                                    <th>Role</th>
                                    <?php $showFacilityAdministrator = getenv('ENABLE_FACILITY_BOOKING_MENU') === 'true'; ?>
                                    <th<?= $showFacilityAdministrator ? '' : ' style="display:none;"' ?>>Facility Administrator</th>
                                    <th>Area Report</th>
                                    <th>History</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="context-menu-one" id="user-mapping-list">
                                <tr>
                                    <td colspan="8" id="textcenter">Please apply the filter</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>
        <input type="hidden" value="<?php echo htmlentities(json_encode($userDivisionsList)) ?>" id="area-admin-user-details">
    </div>
<?php
} else {
    echo "Access Denied";
    die;
}
?>
<script type="text/javascript">
    var saveddivisionId = 0;
    var saveddivisionname = 'Select Division';
    $(document).ready(function() {
        $(".division-seclect").chosen({
            no_results_text: "Oops, nothing found!",
            width: "200px"
        });

        var saveddivisionId = getSavedSelectedDivision();
        var saveddivisionname = getSavedSelectedDivisionName();
        if (saveddivisionname == null) {
            var saveddivisionname = 'Select Division';
        }
        if (saveddivisionId) {
            $('#hometeam').val(saveddivisionId);
            $('.chosen-single span').text(saveddivisionname);
            fetchSavedSearchData(saveddivisionId);
        }
    });

    $(document).on('click', "#historydivisionalAdmin", function() {
        showHistory_other($(this).data("historydivisionid"));
    });

    /* get the history of Divisional Admin*/
    function showHistory_other(divisionid) {
        $.post("function-includes/common/common.php", {
                divisionid: divisionid,
                action: 'history',
                modulename: 'DivisionalAdmin'
            },
            function(data, status) {
                $.facebox(data);
            })
    }

    function CallPopUp() {

        var division_value = $('#hometeam').find(":selected").val();
        var DutyUrl = "page-includes/admin/divisions/view/addDivisionalAdmin.php";
        var disable_class = '<?php echo $disabled; ?>';

        if (division_value != '0') {
            $.post(DutyUrl, {
                    division_id: division_value,
                    disable_class: disable_class
                },
                function(data, status) {
                    $.facebox(data);
                });
        } else {
            alert('Please select any Area from the Area List.');
        }
    }

    function handleAddButton() {
        var division_id = $('#hometeam').val();
        var systemAdmin = <?php echo $intSysAdmin ?>;
        var areaAdminListUser = JSON.parse($('#area-admin-user-details').val());
        var canAccess = systemAdmin == 1 ? true : false;
        areaAdminListUser.forEach(function(item, index) {
            if (item['DivisionID'] == division_id) {
                canAccess = true;
            }
        });
        canAccess == false ? $('#js_addnonscheduledstaff').addClass('notclickable').addClass('buttonDisabled') : $('#js_addnonscheduledstaff').removeClass('notclickable').removeClass('buttonDisabled');
    }
    $(document).delegate('#hometeam', 'change', function(event) {
        event.stopImmediatePropagation();
        var division_id = this.value;
        var division_name = $("#hometeam option:selected").text();
        saveSelectedDivision(division_id, division_name);
        handleAddButton();
        var disable_class = '<?php echo $disabled; ?>';
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
                        },
                        {
                            "orderable": false,
                            "targets": 5
                        }
                    ],
                    drawCallback: function (settings) {
                        if(division_id == 0) {
                            $('.dataTables_empty').text("Please apply the filter.");
                        }
                    }
                });
                // Hide facility administrator role
                hideFacilityAdminColumn(table);
                yadcf.init(table, [{
                    column_number: 0,
                    filter_type: 'text'
                }]);

            });
    });

    $('#adminuserstabs .fields label').click(function() {
        $('#js_teamdropdown_chosen').addClass('chosen-container-active');
    });

    //Added on 17 Septh 2021
    function fetchSavedSearchData(division_id) {
        handleAddButton();
        var disable_class = '<?php echo $disabled; ?>';
        var hit_url = "page-includes/admin/divisions/view/getDivisonalMapList.php";

        $.post(hit_url, {
                division_id: division_id,
                disable_class: disable_class
            },
            function(data, status) {
                $("#user-mapping-list").html(data);
                let table = $('#divisionlisting').DataTable({
                    stateSave: true,
                    "bPaginate": false,
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
                        },
                        {
                            "orderable": false,
                            "targets": 5
                        }
                    ],
                    drawCallback: function (settings) {
                        if(division_id == 0) {
                            $('.dataTables_empty').text("Please apply the filter");
                        }
                    }
                });
                // Hide facility administrator role
                hideFacilityAdminColumn(table);
                yadcf.init(table, [{
                    column_number: 0,
                    filter_type: 'text'
                }]);
            });

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