<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../function-includes/helpers.php';
include_once '../../class-includes/userRolePermissions.php';
include_once 'setTeamCookieJob.php';

$pageid = 2;
if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
}else{
    $strTeams = 0;
}

// Call User Permission function.
if($strTeams == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $strTeams);
}

if ($permissions->canview == 0) {
    $intStatus = 0;
    $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
    header('Content-type: application/json');
    echo json_encode($response_array);
    exit();
}
if ($permissions->cancreate == 1) {
    $draggable = 'true';
}
else {
    $draggable = 'false';
}

$alljobs = getJobs(0, $strTeams);
$jobs = json_decode($alljobs, true);

?>

<div class="tables">
    <div class="scrollable" id="firsttbl" onScroll="setJobsContainerScrollPos()" style="height:175px;">
	<div id="jobsdetailsPos"  name="jobsdetailsPos"></div>
        <table id="joblisting" style="width:100%" class="tablesmall stripe bluetable" role="presentation">
            <thead>
            <tr id="jobListingPos">
                <th>Start</th>
                <th>End</th>
                <th>Job</th>
                <th>Label</th>
                <th>Location</th>
                <th>Contact</th>
                <th>Info</th>
            </tr>
            </thead>
            <tbody class="context-menu-one">
            <?php
			$jobs[0]['MasterJobID'] = (isset($jobs[0]) && isset($jobs[0]['MasterJobID'])) ? $jobs[0]['MasterJobID'] : 0;
            if ($jobs[0]['MasterJobID'] == 0) { }
            else {
                foreach ($jobs as $job) {
                    // Prepare the title attribute with trimmed concatenation, max 51 chars
                    $title = substr(trim($job['JobName'] . "\n" . $job['Details']), 0, 51);

                    // Escape all output for HTML attributes and content
                    $draggable = htmlspecialchars($draggable, ENT_QUOTES);
                    $teamId = htmlspecialchars($job["TeamID"], ENT_QUOTES);
                    $startTime = htmlspecialchars($job["StartTime"], ENT_QUOTES);
                    $endTime = htmlspecialchars($job["EndTime"], ENT_QUOTES);
                    $jobName = htmlspecialchars($job["JobName"] ?? '', ENT_QUOTES);
                    $isLinkJob = htmlspecialchars($job["IsLinkjob"], ENT_QUOTES);
                    $masterJobId = htmlspecialchars($job["MasterJobID"], ENT_QUOTES);
                    $titleEscaped = htmlspecialchars($title, ENT_QUOTES);

                    $formattedStartTime = htmlspecialchars(FormatTime($job['StartTime']), ENT_QUOTES);
                    $formattedEndTime = htmlspecialchars(FormatTime($job['EndTime']), ENT_QUOTES);
                    $jobNameShort = htmlspecialchars(substr($job['JobName'] ?? '', 0, 40), ENT_QUOTES);
                    $programme = htmlspecialchars($job['Programme'] ?? '', ENT_QUOTES);
                    $location = htmlspecialchars($job['Location'] ?? '', ENT_QUOTES);
                    $contact = htmlspecialchars(trim(substr($job['Contact'] ?? '', 0, 10)), ENT_QUOTES);
                    $details = htmlspecialchars(trim(substr($job['Details'] ?? '', 0, 10)), ENT_QUOTES);

                    echo <<<HTML
                <tr draggable="$draggable" teamid="$teamId" jobsttime="$startTime" jobendtime="$endTime" 
                    class="master-jobs-context-menu dragjob dragmyjob" 
                    jobName="$jobName" mduty="$isLinkJob" jobid="$masterJobId" id="$masterJobId" title="$titleEscaped">
                    <td>$formattedStartTime</td>
                    <td>$formattedEndTime</td>
                    <td>$jobNameShort</td>
                    <td>$programme</td>
                    <td>$location</td>
                    <td>$contact</td>
                    <td>$details</td>
                </tr>
                HTML;
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<div class="tables cls-234" style="max-height:183px;">
    <div class="startend">
        <label for="start">Duty Start Time</label>
        <input type="text" id="dutyStDisp" name="start" id="start" readonly>
        <label for="end">Duty End Time</label>
        <input type="text" name="end" id="dutyEndDisp" readonly>
		<label for="name">Duty Name</label>
        <input type="text" name="dutyName" id="dutyName" readonly>
        <input type="hidden" name="jobattr" id="jobattr" value="">
    </div>
    <div class="scrollable" style="max-height:145px">
        <table id="jobsassigned" style="width:100%;" class="oddevenclass tablesmall stripe bluetable" role="presentation">
            <thead>
            <tr>
                <th>Job</th>
                <th>Start</th>
                <th>End</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td style="text-align: center;" colspan="3">Click below Master duties to see master jobs assigned in
                    that.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript" src="../../js/master-job.js?v=<?php echo time(); ?>"></script>
<script>
    $(document).ready(function () {
        let table = $('#joblisting').DataTable({
            "bInfo" : false,
            "bPaginate": false,
			"stateSave": true
        });

        yadcf.init(table, [

            {
                column_number: 2,
                filter_type: 'text'
            },

        ]);
        $('.yadcf-filter-reset-button').attr('aria-label', 'Clear Filter');
    });
function setJobsContainerScrollPos()
{
	let top = $('#jobsdetailsPos').position().top;
	let left = $('#jobsdetailsPos').position().left;
	$.cookie("jobsdetailsPosTop", top);
}
</script>
<style type="text/css">
    .btn {
        width: 50px;
        height: 30px;

    }

    .dragjob {
        cursor: pointer;
    }

    .dutyjob-context-menu {
        font-size: 10px;
    }
</style>
