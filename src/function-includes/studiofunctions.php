<?php

//Get studio Job details based on scheduling Team
function GetStudioUsage($intWeek, $intDay, $intSchedulingTeamId) {
	try {
	$pdo = OpenDBLinkA7();
	$intEarliestStart = 86400;
	$intLatestEnd = 0;

	$sql = "SELECT  UD_DisplayName as FullName,
			aj.ID AS JobID,
			aj.JobName AS JobName,
			aj.StartTime AS StartTime,
			aj.EndTime AS EndTime,
			CAST(aj.JobBackColour AS nvarchar) AS JobBackColour,
			CAST(aj.JobFontColour AS nvarchar) AS JobFontColour,
			st.studio AS Studio
	FROM Allocations_Jobs_Publish AS aj WITH (NOLOCK)
	INNER JOIN Allocations_Publish AP WITH (NOLOCK) ON AP.ID = AJ.AllocationID
	INNER JOIN UserDetails as sp WITH (NOLOCK) ON ap.SchedulingPersonID = sp.UD_UserID
	INNER JOIN  Studios AS st ON LEFT(aj.JobName, CASE WHEN CharIndex(' ', aj.JobName) = 0
	             THEN Len(LTrim(aj.JobName))
				 ELSE CASE WHEN CharIndex(' ', LTrim(aj.JobName)) - 1 < 0 THEN 0
				           ELSE CharIndex(' ', LTrim(aj.JobName)) - 1 END
				 END) = st.studio
	WHERE (ap.schedulingTeamId = :intTeamId)
	AND (ap.WeekNumber = :intWeek)
	AND (ap.iDay = :intDay)
	AND (ISNULL(aj.UnAllocated, 0) = 0)
	GROUP BY  UD_DisplayName , aj.ID, aj.JobName,
				   aj.StartTime, aj.EndTime,
			CAST(aj.JobBackColour AS nvarchar),
			CAST(aj.JobFontColour AS nvarchar), st.studio
	ORDER BY  studio
  ";
	$stmt = $pdo->prepare($sql);

	$stmt->bindParam(':intWeek', $intWeek, PDO::PARAM_INT);

	$stmt->bindParam(':intDay', $intDay, PDO::PARAM_INT);

	$stmt->bindParam(':intTeamId', $intSchedulingTeamId, PDO::PARAM_INT);

	$stmt->execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($result as $row ) {
      $strJobName = $row['JobName'];
	  $intStartTime = $row["StartTime"];

      if ($intStartTime < $intEarliestStart) {
        $intEarliestStart = $intStartTime;
      }

      $intEndTime = $row["EndTime"];
      if ($intEndTime > $intLatestEnd) {
        $intLatestEnd = $intEndTime;
      }
      $strAllocatedTo = $row["FullName"];

      $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]['JobName'] = $strJobName;
      $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]['StartTime'] = $intStartTime;
      $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]['EndTime'] = $intEndTime;
      $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]['AllocatedTo'] = $strAllocatedTo;
      if (is_null($row["JobBackColour"])) {
        $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]["BackColour"] = '#ddffdd';
      }
      else {
        if (is_numeric($row["JobBackColour"])) {
           $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]["BackColour"] = intotohex($row["JobBackColour"]);
        }
        else {
          $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]["BackColour"] = $row["JobBackColour"];
        }
      }
      if (is_null($row["JobFontColour"])) {
        $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]["FontColour"] = '#ffffff';
      }
      else {
        if (is_numeric($row["JobFontColour"])) {
           $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]["FontColour"] = intotohex($row["JobFontColour"]);
        }
        else {
          $arrJobs['Studios'][$row['Studio']]['Jobs'][$row['JobID']]["FontColour"] = $row["JobFontColour"];
        }
      }
    }
    if (isset($arrJobs)) {
      $arrJobs['EarliestStart'] = $intEarliestStart;
      $arrJobs['LatestEnd'] = $intLatestEnd;
      // ############################################## Work Out the lines for wach job
      foreach ($arrJobs['Studios'] as $strStudio => $arrStudioJobs) {
        unset($arrLines);
        foreach ($arrStudioJobs['Jobs'] as $intJobID => $arrJob) {
          // The job is an array which contains the stuff we need.....
          $intStartTime = $arrJob["StartTime"];
          $endtime = $arrJob["EndTime"];
          $line = 0;
          if (isset($arrLines)) {
            $counter = 0;
            foreach ($arrLines as $line => $arrsub) {
              $confilct = 0;
              foreach ($arrsub as $times) {
                if ($times['start'] < $endtime && $times['end'] > $intStartTime) {
                  $confilct = 1;
                }
              }
              if ($confilct == 0) {
                break;
              }
              $counter++;
            }
            $arrLines[$counter][$intJobID]['start'] = $intStartTime;
            $arrLines[$counter][$intJobID]['end'] = $endtime;
          }
          else {
            $arrLines[0][$intJobID]['start'] = $intStartTime;
            $arrLines[0][$intJobID]['end'] = $endtime;

            $counter = 0;
          }
          $arrJobs['Studios'][$strStudio]['Jobs'][$intJobID]['line'] = $counter;
        }
        $arrJobs['Studios'][$strStudio]['linecount'] = count($arrLines);
      }
      return ($arrJobs);
  }
 } catch (PDOException $e) {
      echo $e->getMessage();
  }
}

// This function returns all the allocations for the day
function readjobs($week, $day, $base) {
$thisdate = datefromweek($week, $day);
  $db = OpenDatabase();
  $query = "SELECT Jobs.ID, Jobs.AllocateJobID, Jobs.JobName, Jobs.StartTime, Jobs.EndTime, Jobs.JobBackColour, Jobs.JobFontColour, Jobs.Comments,
            Staff.Forename + N' ' + Staff.Surname AS Fullname, Staff.RadioBase
            FROM  Jobs
            INNER JOIN Allocations ON Jobs.AllocationID = Allocations.AllocationID
            AND Jobs.DepartmentID = Allocations.DepartmentID
            LEFT OUTER JOIN Staff ON Allocations.StaffNumber = Staff.StaffNumber
            WHERE (Allocations.WeekNumber = N'$week')
            AND (Allocations.iDay = $day)
            ORDER BY Jobs.EndTime - Jobs.StartTime DESC";




    $query = "SELECT       CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.id ELSE jobs_webedit.id, END AS id, CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.allocatejobid ELSE jobs_webedit.allocatejobid END AS AllocateJobID,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.JobName ELSE jobs_webedit.JobName END AS JobName,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.StartTime ELSE jobs_webedit.StartTime END AS StartTime,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.EndTime ELSE jobs_webedit.EndTime END AS EndTime,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.JobBackColour ELSE jobs_webedit.JobBackColour END AS JobBackColour,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.JobFontColour ELSE jobs_webedit.JobFontColour END AS JobFontColour,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.Comments ELSE jobs_webedit.Comments END AS Comments,
                         Staff.Forename + N' ' + Staff.Surname AS Fullname, CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN 0 ELSE 1 END AS Expr2
FROM            Jobs INNER JOIN
                         Staff ON Jobs.StaffNumber = Staff.StaffNumber INNER JOIN
                         Jobs_webedit ON Jobs.WeekNumber = Jobs_webedit.WeekNumber AND Jobs.iDay = Jobs_webedit.iDay AND Jobs.StaffNumber = Jobs_webedit.StaffNumber
WHERE        (Staff.DepartmentID = 4) AND (Jobs.WeekNumber = 201843) AND (Jobs.iDay = 5)
GROUP BY Jobs.ID, Staff.Forename + N' ' + Staff.Surname, CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.allocatejobid ELSE jobs_webedit.allocatejobid END,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.JobName ELSE jobs_webedit.JobName END, CASE WHEN jobs_webedit.AllocateJobID IS NULL
                         THEN jobs.StartTime ELSE jobs_webedit.StartTime END, CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.EndTime ELSE jobs_webedit.EndTime END,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.JobBackColour ELSE jobs_webedit.JobBackColour END,
                         CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN jobs.JobFontColour ELSE jobs_webedit.JobFontColour END, CASE WHEN jobs_webedit.AllocateJobID IS NULL
                          THEN jobs.Comments ELSE jobs_webedit.Comments END, CASE WHEN jobs_webedit.AllocateJobID IS NULL THEN 0 ELSE 1 END
ORDER BY Fullname";












    echo "\n <br> q: ", $query;
    $bookings = sqlsrv_query($db, $query);

    while($row = sqlsrv_fetch_array($bookings)){
      $jobname = $row['JobName'];
      //echo $jobname .' '.strpos($jobname, ' ').'<br>';
      if (strpos($jobname, ' ') === false) {
       $jobnamestart = $jobname;
      }
      else {
        $jobnamestart = substr($jobname, 0, strpos($jobname, ' '));
        $jobname = substr($jobname, strlen($jobnamestart) + 1);
        $jobname = trim(str_replace('-', '', $jobname));
      }
      $allocatejobid = $row['AllocateJobID'];
      $starttime = $row["StartTime"];
      $endtime = $row["EndTime"];
      $allocatedto = $row["Fullname"];

      $arrjobs[$allocatejobid]['id'] = $row["ID"];
      $arrjobs[$allocatejobid]['match'] = $jobnamestart;
      $arrjobs[$allocatejobid]['name'] = $jobname;
      $arrjobs[$allocatejobid]['starttime'] = $starttime;
      $arrjobs[$allocatejobid]['endtime'] = $endtime;
      $arrjobs[$allocatejobid]['allocatedto'] = $allocatedto;
      if (is_null( $row["JobBackColour"])) {
        $arrjobs[$allocatejobid]["backcolour"] = '#ddffdd';
      }
      else {
        $arrjobs[$allocatejobid]["backcolour"] = intotohex($row["JobBackColour"]);
      }
      if (is_null( $row["JobFontColour"])) {
        $arrjobs[$allocatejobid]["fontcolour"] = '#ffffff';
      }
      else {
        $arrjobs[$allocatejobid]["fontcolour"] = intotohex($row["JobFontColour"]);
      }
      if (is_null($row["Comments"]) || $row["Comments"] == '') {
        $arrjobs[$allocatejobid]["comments"] = '';
      }
      else {
        $arrjobs[$allocatejobid]["comments"] = $row["Comments"];
      }
    }
  if (isset($arrjobs)) {
    return ($arrjobs);
  }
}

// ==========================================================================================================
function readeditedjobs ($week, $day) {

  $db = OpenDatabase();
  $query = "SELECT       Jobs_webedit.ID, Jobs_webedit.WeekNumber, Jobs_webedit.iDay, Jobs_webedit.JobName, Jobs_webedit.StartTime,
                         Jobs_webedit.EndTime, Jobs_webedit.JobBackColour, Jobs_webedit.JobFontColour, Jobs_webedit.AllocateJobID, Jobs_webedit.Comments,
                         Staff.Forename + N' ' + Staff.Surname AS Fullname, Jobs_webedit.DepartmentID
            FROM         Jobs_webedit
            LEFT OUTER JOIN
                         Allocations_webedit ON Jobs_webedit.DepartmentID = Allocations_webedit.DepartmentID
            AND          Jobs_webedit.AllocationID = Allocations_webedit.AllocationID
            LEFT OUTER JOIN Staff ON Allocations_webedit.StaffNumber = Staff.StaffNumber
            WHERE        (Jobs_webedit.WeekNumber = '$week') AND (Jobs_webedit.iDay = $day) AND (Jobs_webedit.deleted = 0)
            ORDER BY Jobs_webedit.EndTime - Jobs_webedit.StartTime DESC";

    $bookings = sqlsrv_query($db, $query);
    // echo "\n <br> q: ", $query;
    while($row = sqlsrv_fetch_array($bookings)){
      $jobname = $row['JobName'];
      if (strpos($jobname, ' ') === false) {
       $jobnamestart = $jobname;
      }
      else {
        $jobnamestart = substr($jobname, 0, strpos($jobname, ' '));
        $jobname = substr($jobname, strlen($jobnamestart) + 1);
        $jobname = trim(str_replace('-', '', $jobname));
      }
      $allocatedto = $row["Fullname"];
      $allocatejobid = $row['AllocateJobID'];
      $starttime = $row["StartTime"];
      $endtime = $row["EndTime"];
      $allocatedto = $row["Fullname"];
      $arrjobs[$allocatejobid]['id'] = $row["ID"];
      $arrjobs[$allocatejobid]['match'] = $jobnamestart;
      $arrjobs[$allocatejobid]['name'] = $jobname;
      $arrjobs[$allocatejobid]['starttime'] = $starttime;
      $arrjobs[$allocatejobid]['endtime'] = $endtime;
      $arrjobs[$allocatejobid]['allocatedto'] = $allocatedto;
      if (is_numeric($row["JobBackColour"])) {
        $JobBackColour = intotohex($row["JobBackColour"]);
      }
      else {
        $JobBackColour = $row["JobBackColour"];
      }

      if (is_numeric($row["JobFontColour"])) {
        $JobFontColour = intotohex($row["JobFontColour"]);
      }
      else {
        $JobFontColour = $row["JobFontColour"];
      }
      $arrjobs[$allocatejobid]["backcolour"] = $JobBackColour;
      $arrjobs[$allocatejobid]["fontcolour"] = $JobFontColour;

      if (is_null($row["Comments"]) || $row["Comments"] == '') {
        $arrjobs[$allocatejobid]["comments"] = '';
      }
      else {
        $arrjobs[$allocatejobid]["comments"] = $row["Comments"];
      }
    }
  if (isset($arrjobs)) {
    return ($arrjobs);
  }
}

// ==========================================================================================================
function writelinenumbers ($arrusage) {
  foreach ($arrusage as $studio => $jobs) {

    //echo '<pre>';
  //print_r($jobs);
    unset($arrlines);
    foreach ($jobs as $jobid => $job) {

      // The job is an array which contains the stuff we need.....
      $starttime = $job["starttime"];
      $endtime = $job["endtime"];
      $line = 0;
      if (isset($arrlines)) {
        $counter = 0;
        foreach ($arrlines as $line => $arrsub) {
          $confilct = 0;
          foreach ($arrsub as $times) {
            if ($times['start'] < $endtime && $times['end'] > $starttime) {
              $confilct = 1;
            }
          }
          if ($confilct == 0) {
            break;
          }
          $counter++;
        }

        $arrlines[$counter][$jobid]['start'] = $starttime;
        $arrlines[$counter][$jobid]['end'] = $endtime;

      }
      else {
        $arrlines[0][$jobid]['start'] = $starttime;
        $arrlines[0][$jobid]['end'] = $endtime;

        $counter = 0;
      }
      $arrusage[$studio][$jobid]['line'] = $counter;
    }
    $arrusage[$studio]['linecount'] = count($arrlines);
  }
  return($arrusage);
}

// ==========================================================================================================
function ReadStudios ($base) {
  $db = OpenDatabase();
  $query = "SELECT id, studio, sortorder
            FROM  studios
            ORDER BY sortorder";

  $rsStudios = sqlsrv_query($db, $query);

  while($row = sqlsrv_fetch_array($rsStudios)){
    $arrStudios[$row['studio']] = $row['id'];
  }
  return ($arrStudios);
}
// ==========================================================================================================
?>