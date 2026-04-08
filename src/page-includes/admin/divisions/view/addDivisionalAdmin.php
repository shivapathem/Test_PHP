<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../process/classDivisionalAdmin.php';
include_once '../../../users/process/classUserSetup.php';

$divisionobj = new ClassDivisionalAdmin;
$setupObj = new classUserSetup();
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$disabled = 'noclass';
if ($_REQUEST["disable_class"]) {
    $disabled = $_REQUEST["disable_class"];
}
$division_id = $_REQUEST["division_id"];
$divisionLists = json_decode($divisionobj->getLoginList(), true);

$haveAccess = $intSysAdmin == 1 ? true : false;
if (!empty($userDivisionsList) && !$haveAccess) {
    foreach ($userDivisionsList as $userDivision) {
        if ($userDivision['DivisionID'] == $division_id) {
            $haveAccess = true;
        }
    }
}
if (!$haveAccess) {
    echo "Access denied";
    die;
}
?>

<div>
    <form id="neweditduty">
        <div class="fields">
            <label for="hometeam" id="w-150" class="lable-with">Network ID </label>
            <select name="hometeam" class="division-select" id="hometeam-user">
                <option value="0">--Blank--</option>
                <?php if (!empty($divisionLists)) {
                    foreach ($divisionLists as $division) {  ?>
                        <option value="<?php echo $division['UD_UserID']; ?>"><?php echo  trim(substr($division['UD_NetLogin'] ?? '', 0, 30)) ?></option>
                <?php }
                }  ?>
            </select>
        </div>
    </form>
    <section style="margin-top: 2%;">
        <div class="tables table-boxs">
            <div class="scrollable">
                <table class=" oddevenclass tablesmall stripe  dataTable no-footer w-100" id="divisionlisting"
                    role="grid" aria-describedby="divisionlisting_info">
                    <thead>
                        <th>Network ID</th>
                        <th>Full Name</th>
                        <th>First Name</th>
                        <th>Surname</th>
                        <th>Email Address</th>
                        <th>Employee Number</th>
                        <th>Role</th>
                        <th>Action</th>
                    </thead>
                    <tbody class="context-menu-one" id="user-details">
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".division-select").chosen({
            no_results_text: "Oops, nothing found!",
            width: "200px"
        });
    });
    $(document).delegate('#hometeam-user', 'change', function() {
        var user_id = this.value;
        var division_id = "<?php echo $division_id; ?>";
        var hit_url = "page-includes/admin/divisions/view/getUserDetails.php";
        var disable_class = '<?php echo $disabled; ?>';

        $.post(hit_url, {
                user_id: user_id,
                division_id: division_id,
                disable_class: disable_class
            },
            function(data, status) {
                $("#user-details").html(data);
            });
    });
</script>