<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/components/filters/filter-process.php';

/**
* This function is used to get the Data For Daily Allocation from the DB as per the scheduling teams ID.
* @param $intTeamID This param contains the TeamID information
* @param $intDay This param contains the WeekDay information
* @param $intWeek This param contains the WeekNo information
* @param $rolePermission This param contains the rolePermission information
*
* @return array Returns Array with sub Array of Assign ,Unassign, Unassign Jobs from the DB as per the scheduling teams ID
*/
function ReadAllocationsDay($intWeek = 0, $intDay = 0, $intTeamID = 0, $intSortOrder = 0,$rolePermission = 0, $filterStr1='', $filterStr2='', $filterStr3='', $filterOrderStr='', $strCurrentDate = '', $day7date = '', $skillFilterDaily = '', $dutyFilterDaily = '', $jobFilterDaily = '', $jobNameAll = '', $jobLabelAll = '', $canViewAdditional = 1) {
  $pdo = OpenDBLinkA7();
  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  $arrAllocations = [];
  $alwaysresult=[];
  $result=[];
  $thisdate = datefromweek($intWeek, $intDay);
    try {
      $alwayssql = "exec [dbo].[usp_get_ReadAllocationsDay] '".str_replace("'","''",$filterStr3)."',".$rolePermission.",'".$strCurrentDate."','".$day7date."', '" . $strUser . "'," . $intSortOrder;
      $stmt1 = $pdo->prepare($alwayssql);
      $stmt1->execute();
      $alwaysresult = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
    }

    $i = 0;
    $dutyearlieststart = [];
    $dutyend = [];
    $arrEditable=$arrLocked=$arrMLocked=$arrDayEdited=[];

   if (!empty($alwaysresult) && count($alwaysresult)>0) {
      /** Check Day Editable Code Start */
	   foreach ($alwaysresult as $row) {
        if (!is_null($row['StartDate'])) {
          $dutyDateinfor =explode(" ",$row['StartDate']);
          $dutyDate = $dutyDateinfor[0];
        } else {
			$dutyDate = date('Y-m-d',strtotime((string) $row['DutyDate']));
		}
        if (!isset($arrEditable[$dutyDate]['IsDayEditable']) && $row['IsDayEditable'] > 0) {
            $arrEditable[$dutyDate]['IsDayEditable'] = $row['IsDayEditable'];
        }
        if (!isset($arrMLocked[$dutyDate]['ManualLock'])) {
            $arrMLocked[$dutyDate]['ManualLock'] = $row['ManualLock'];
        }
        if (!isset($arrLocked[$dutyDate]['LockStatus'])) {
            $arrLocked[$dutyDate]['LockStatus'] = $row['LockStatus'];
        }
        if (!isset($arrDayEdited[$dutyDate]['isEdited']) && $row['isEdited'] > 0) {
            $arrDayEdited[$dutyDate]['isDayEdited'] = $row['isEdited'];
        }
      }
	  foreach ($alwaysresult as $row) {
        if ($row['Grid']=='AD') {
          $dutyid = $row["DutyID"];
          $scheduleId = $row["ScheduledPersonID"];
          $DayNumber = $row["DayNumber"];
          if (!is_null($row["ScheduledPersonID"]) || $row["ScheduledPersonID"]!=0) {
            //Hide additional team record if Do not display view screen checked
            if ($canViewAdditional == 0 && in_array((int)$row["IsHomeTeam"], [0, 2]) && $row['DisplayInViewScreen'] == 0) {
                continue;
            }
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffNumber"] = $row["StaffNumber"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["ScheduledPersonID"] = $row["ScheduledPersonID"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["TriangleColour"] = $row["TriangleColour"] ?? '';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["DutyParentID"] = $row['DutyParentID'] ?? 0;

            if (!empty($row["FullName"])) {
              $name = $row["FullName"];
            } else {
              $name = $row["DisplayName"];
            }

            if ($intTeamID == $row["schedulingTeamId"]) {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["fullname"] = $name;
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["sortcode"] = $row["SortCode"] ?? '';
            } else {
              if (!isset($arrAllocations['assigned'][$DayNumber][$scheduleId]["fullname"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["fullname"] =  $name;
              }
              if (!isset($arrAllocations['assigned'][$DayNumber][$scheduleId]["sortcode"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["sortcode"] =  $row["SortCode"] ?? '';
              }
            }           

            $arrAllocations['assigned'][$DayNumber][$scheduleId]["CostCode"] = $row["CostCode"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffBBCEmail"] = $row["StaffInternalEmail"] ?? '';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffNONBBCEmail"] = $row["StaffExernalEmail"] ?? '';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffTextColour"] = $row["PersonFontColour"] ?? '';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffBackColour"] = $row["PersonBackgroundColour"] ?? '';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["allocationSPID"] = $row["AllocationsSPID"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["allocationDutyID"] = $row["AllocationsDutyID"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["iscopy"] = $row["iscopy"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["isEdited"] = $row["isEdited"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["edited"] = $row["isDutyEditable"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["editable"] = $row["isEditable"] ?? 0;
            $starttime = intval($row["StartTime"] ?? 0);
            $endtime = intval($row["EndTime"] ?? 0);
            $misc = 0;
            if ((empty($starttime)) && (empty($endtime))) {
              $endtime = intval($row["Duration"] ?? 0);
              $misc=1;
            }
            if($starttime==0 && $endtime==0 && $row["Duration"]==0) {
              $endtime = 3600;
            }
            if ($row["DutyName"]=='Leave' || $row["DutyName"]=='OFF Leave') {
              $endtime = 3600;
            }
      			if(($row['DutyMidnightFlag']== 1)){
      				$endtime+=86400;
      				$jobdutyDate = date("Y-m-d", strtotime((string) $row["DutyDate"]));
      			}
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["miscduty"] = $misc;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["starttime"] = $starttime;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["endtime"] = $endtime;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StartDate"] = $row['StartDate'] ?? '';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["EndDate"] = $row['EndDate'] ?? '';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["editable"] = $row['isEditable'] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["inbuilding"] = $row["inBuilding"] ?? 0;
            if (is_null($row["active"]) || $row["active"] == 0) {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["signin"] = 0;
            } else {
              if ($row["StartTime"] == $row["SignInStartTime"] && $row["EndTime"] == $row["SignInEndTime"]) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["signin"] = $row["active"];
              } else {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["signin"] = 2;
              }
            }
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["duty"] = !empty($row["DutyName"]) ? $row["DutyName"] : 'U';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["duration"] = $row["Duration"] ?? 0;
            if ($DayNumber==1) {
              if (($arrAllocations['assigned'][$DayNumber][$scheduleId]["duty"]<>'U') && ($arrAllocations['assigned'][$DayNumber][$scheduleId]["duty"]<>'Leave')){
                $dutyearlieststart[]=$arrAllocations['assigned'][$DayNumber][$scheduleId]["starttime"];
                if ($starttime>=$endtime){
                  $dutyend[]=$arrAllocations['assigned'][$DayNumber][$scheduleId]["endtime"];
                } else {
        					$dutyend[]=$arrAllocations['assigned'][$DayNumber][$scheduleId]["endtime"]-86400;
        				}
              }
            }

            if (is_null($row["dutyColorId"]) || $row["dutyColorId"]=='0') {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["backcolour"] = '#708090';
              if (($row["DutyName"]=='Leave') || ($row["DutyName"]=='OFF Leave')) {
                // Set default green colour for Leave and OFF Leave
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#009900';
              }else if (($row["DutyName"]=='Sick') || ($row["DutyName"]=='U-Sick') || ($row["DutyName"]=='-Sick')){
                 // Set default mustard yellow colour for Sick and U-Sick
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#e89e3c';
              }else{
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#fff0f5';
              }
            } else {
              if (!empty($row["ColourBackground"]) && !empty($row["ColourFont"])) {
				          $arrAllocations['assigned'][$DayNumber][$scheduleId]["backcolour"] = '#'.$row["ColourBackground"];
				          $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#'.$row["ColourFont"];
              } else {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["backcolour"] = '#708090';
                if (($row["DutyName"]=='Leave') || ($row["DutyName"]=='OFF Leave')) {
                  // Set default green colour for Leave and OFF Leave
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#009900';
                }else if (($row["DutyName"]=='Sick') || ($row["DutyName"]=='U-Sick') || ($row["DutyName"]=='-Sick')){
                  // Set default mustard yellow colour for Sick and U-Sick
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#e89e3c';
                }else{
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#fff0f5';
                }
              }
            }

            $arrAllocations['assigned'][$DayNumber][$scheduleId]["dutycomments"] = $row["DutyComments"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["personcomments"] = $row["PersonComments"];
			      $arrAllocations['assigned'][$DayNumber][$scheduleId]["dutyMidnightFlag"] = $row["DutyMidnightFlag"];
	          // Set the flag to Internally edited if the duty starts 1 day from now
            if ($row["InternalEdited"] == 1 && ($starttime + strtotime($thisdate) < strtotime("+ 1 Day"))) {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["internaledited"] = 1;
            } else {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["internaledited"] = 0;
            }

            $arrAllocations['assigned'][$DayNumber][$scheduleId]["IsAttention"] = $row["IsAttention"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["IsRequest"] = $row["IsRequest"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["isPublished"] = (empty($row["isPublished"])|| is_null($row["isPublished"])) ? 0 : $row["isPublished"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["DutyParentID"] = $row["DutyParentID"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["DutyID"] = !empty($row["DutyID"]) ? $row["DutyID"] : 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["ManualEDP"] = !empty($row["ManualEDP"]) ? $row["ManualEDP"] : 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["DutyDate"] = $row["DutyDate"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["MannualOThours"] = $row["MannualOThours"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["MarkedOvertime"] = $row["MarkedOvertime"];		$arrAllocations['assigned'][$DayNumber][$scheduleId]["IsHomeTeam"] = $row["IsHomeTeam"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["pdlStartTime"] = $row["LeaveStartTime"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["pdlEndTime"] = $row["LeaveEndTime"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["DutyLabels"] = [
              $row["dutyProgramId"] ?? '',
              $row["DutyProgramId2"] ?? '',
              $row["DutyProgramId3"] ?? '',
              $row["DutyProgramId4"] ?? '',
              $row["DutyProgramId5"] ?? '',
              $row["DutyProgramId6"] ?? ''
            ];

            // JobUnder Duty Start
            $dutyid = $row["DutyID"];
            if (!is_null($row["JobID"]) && $row['DutyName'] != 'U') {
              $jobstarttime = floor($row["JobStartTime"]);
              $jobendtime = floor($row["JobEndTime"]);
              if(($row['MidnightFlag']== 1) && ($jobstarttime > $jobendtime)){
                  $jobendtime+=86400;
              }elseif(($row['MidnightFlag']== 1) && ($jobstarttime < $jobendtime)){
                $jobstarttime+=86400;
                $jobendtime+=86400;
              }
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["jobid"] = $row["JobID"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["dutyId"] = $dutyid;
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["jobname"] = $row["JobName"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["starttime"] = $jobstarttime;
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["endtime"] = $jobendtime;
              if(is_null($row["isJobEdited"])){ $row["isJobEdited"]=0;}
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["edited"] = $row["isJobEdited"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["JobParentID"] = $row["JobParentID"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["programme"] = $row["Programme"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["programmeid"] = $row["ProgrammeId"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["MidnightFlag"] = $row["MidnightFlag"];
              if (empty($row["JobBackColour"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["backcolour"] = '#ddffdd';
              } else {
                if (is_numeric($row["JobBackColour"])) {
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["backcolour"] = intotohex($row["JobBackColour"]);
                } else {
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["backcolour"] = $row["JobBackColour"];
                }
              }

              if (empty($row["JobFontColour"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["fontcolour"] = '#000000';
              } else {
                if (is_numeric($row["JobFontColour"])) {
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["fontcolour"] = intotohex($row["JobFontColour"]);
                }
                else {
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["fontcolour"] = $row["JobFontColour"];
                }
              }

              if (is_null($row["JobComments"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["comments"] = '';
              } else {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["comments"] = htmlspecialchars($row["JobComments"]);
              }

              if (is_null($row["Job_Info"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["Job_Info"] = '';
              }
              else {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["Job_Info"] = htmlspecialchars($row["Job_Info"]);
              }

            }
          }
        $i++;
        }
          // ======= The Unallocated Duties
          if (($row['Grid']=='UD') && (mb_strtolower($row["DutyName"]) != 'unassigned job')) {
            $dutyid = $row["DutyID"] ?? 0;
            $DayNumber = $row["DayNumber"] ?? 0;

            if (!is_null($row["JobID"]) && $row["DutyName"] != 'U') {
                $jobstarttime = floor($row["JobStartTime"]);
                $jobendtime = floor($row["JobEndTime"]);

                if(($row['MidnightFlag']== 1) && ($jobstarttime > $jobendtime)){
                  $jobendtime+=86400;
                }elseif(($row['MidnightFlag']== 1) && ($jobstarttime < $jobendtime)){
                  $jobstarttime+=86400;
                  $jobendtime+=86400;
                }

                $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["jobid"] = $row["JobID"];
                $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["jobname"] = $row["JobName"];
                $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["starttime"] = $jobstarttime;
                $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["endtime"] = $jobendtime;
                if (is_null($row["isJobEdited"])) { $row["isJobEdited"]=0;}
                $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["edited"] = $row["isJobEdited"] ?? 0;
                $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["programme"] = $row["Programme"] ?? 0;
                $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["JobParentID"] = $row["JobParentID"] ?? 0;
                $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["MidnightFlag"] = $row["MidnightFlag"] ?? 0;
                if (is_null($row["JobBackColour"])|| $row["JobBackColour"] == '') {
                  $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["backcolour"] = '#ddffdd';
                }
                else {
                  if (is_numeric($row["JobBackColour"])) {
                    $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["backcolour"] = intotohex($row["JobBackColour"]);
                  }
                  else {
                    $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["backcolour"] = $row["JobBackColour"];
                  }
                }

                if (is_null($row["JobFontColour"]) || $row["JobFontColour"] == '') {
                  $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["fontcolour"] = '#000000';
                } else {
                  if (is_numeric($row["JobFontColour"])) {
                    $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["fontcolour"] = intotohex($row["JobFontColour"]);
                  }
                  else {
                    $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["fontcolour"] = $row["JobFontColour"];
                  }
                }

                if (isset($row["JobComments"]) && is_null($row["JobComments"])) {
                  $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["comments"] = '';
                } else {
                  $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["comments"] = isset($row["JobComments"]) ? htmlspecialchars($row["JobComments"]):'';
                }

                if (isset($row["Job_Info"]) && is_null($row["Job_Info"])) {
                  $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["Job_Info"] = '';
                } else {
                  $arrAllocations['unassigned'][$DayNumber][$dutyid]['jobs'][$row["JobID"]]["Job_Info"] = isset($row["Job_Info"]) ? htmlspecialchars($row["Job_Info"]):'';
                }
              }

            $starttime = intval($row["StartTime"]);
            $endtime = intval($row["EndTime"]);
            if(($row['DutyMidnightFlag']== 1)){
      				$endtime+=86400;
      			}

            $arrAllocations['unassigned'][$DayNumber][$dutyid]["StaffNumber"] = null;
            if (is_null($row["isEdited"])){ $row["isEdited"]=0;}
            $arrAllocations['unassigned'][$DayNumber][$dutyid]["edited"] = $row["isEdited"];
            $arrAllocations['unassigned'][$DayNumber][$dutyid]["duty"] = $row["DutyName"];
            $arrAllocations['unassigned'][$DayNumber][$dutyid]["dutyId"] = $dutyid;
            $arrAllocations['unassigned'][$DayNumber][$dutyid]["AllocationParentID"] = $row["DutyParentID"];
            $arrAllocations['unassigned'][$DayNumber][$dutyid]["duration"] = $row["Duration"];
            $arrAllocations['unassigned'][$DayNumber][$dutyid]["dutyDate"] = $row["DutyDate"];

            if (is_null($row["dutyColorId"]) || $row["dutyColorId"]=='0') {
              $arrAllocations['unassigned'][$DayNumber][$dutyid]["backcolour"] = '#708090';
              $arrAllocations['unassigned'][$DayNumber][$dutyid]["fontcolour"] = '#fff0f5';
            } else {
              if(!empty($row["ColourBackground"]) && !empty($row["ColourFont"])) {
                $arrAllocations['unassigned'][$DayNumber][$dutyid]["backcolour"] = '#'.$row["ColourBackground"];
                $arrAllocations['unassigned'][$DayNumber][$dutyid]["fontcolour"] = '#'.$row["ColourFont"];
              } else{
                $arrAllocations['unassigned'][$DayNumber][$dutyid]["backcolour"] = '#708090';
                $arrAllocations['unassigned'][$DayNumber][$dutyid]["fontcolour"] = '#fff0f5';
              }
            }

            $arrAllocations['unassigned'][$DayNumber][$dutyid]["dutycomments"] = $row["DutyComments"];
            $arrAllocations['unassigned'][$DayNumber][$dutyid]["starttime"] = $starttime;
            $arrAllocations['unassigned'][$DayNumber][$dutyid]["endtime"] = $endtime;
          }
          // ======= The Unallocated Jobs

          $jobid = $row["JobID"];
          if (!is_null($jobid) && ($row['Grid']=='UJ')) {
              $jobstarttime = floor($row["JobStartTime"]);
              $jobendtime = floor($row["JobEndTime"]);
              if(($row['MidnightFlag']== 1) && ($jobstarttime > $jobendtime)){
                $jobendtime+=86400;
                $jobdutyDate = date("Y-m-d", strtotime((string) $row["DutyDate"]));
              }
              elseif(($row['MidnightFlag']== 0)){
                $jobdutyDate = date("Y-m-d", strtotime((string) $row["DutyDate"]));
              }
              elseif(($row['MidnightFlag']== 1) && ($jobstarttime < $jobendtime)){
                $jobstarttime+=86400;
                $jobendtime+=86400;
                $jobdutyDate = date("Y-m-d", strtotime((string) $row["DutyDate"]));
              }

              $arrAllocations['jobs'][$jobdutyDate][$jobid]["jobid"] = $row["JobID"];
              if (is_null($row["isEdited"])) { $row["isEdited"]=0;}
              $arrAllocations['jobs'][$jobdutyDate][$jobid]["edited"] = $row["isEdited"];
              $arrAllocations['jobs'][$jobdutyDate][$jobid]["allocatejobid"] = $row["JobParentID"];
              $arrAllocations['jobs'][$jobdutyDate][$jobid]["jobname"] = $row["JobName"];
              $arrAllocations['jobs'][$jobdutyDate][$jobid]["programme"] = $row["Programme"];
              $arrAllocations['jobs'][$jobdutyDate][$jobid]["starttime"] = $jobstarttime;
              $arrAllocations['jobs'][$jobdutyDate][$jobid]["endtime"] = $jobendtime;
              $arrAllocations['jobs'][$jobdutyDate][$jobid]["JobMidnightFlag"] = $row['MidnightFlag'];

              if (is_numeric($row["JobBackColour"])) {
                  $arrAllocations['jobs'][$jobdutyDate][$jobid]["backcolour"] = intotohex($row["JobBackColour"]);
              } else {
                if (is_null($row["JobBackColour"])) {
                    $arrAllocations['jobs'][$jobdutyDate][$jobid]["backcolour"]='#ddffdd';
                } else {
                  $arrAllocations['jobs'][$jobdutyDate][$jobid]["backcolour"] = $row["JobBackColour"];
                }
              }
              if (is_numeric($row["JobFontColour"])) {
                  $arrAllocations['jobs'][$jobdutyDate][$jobid]["fontcolour"] = intotohex($row["JobFontColour"]);
                } else {
                  if (is_null($row["JobFontColour"])) {
                  $arrAllocations['jobs'][$jobdutyDate][$jobid]["fontcolour"] = '#000000';
                } else {
                    $arrAllocations['jobs'][$jobdutyDate][$jobid]["fontcolour"] = $row["JobFontColour"];
                }
              }
              if (is_null($row["JobComments"])) {
                  $arrAllocations['jobs'][$jobdutyDate][$jobid]["JobComments"] = '';
              } else {
                  $arrAllocations['jobs'][$jobdutyDate][$jobid]["JobComments"] = htmlspecialchars((string) isset($row["Comments"]) ? $row["Comments"] : '');
              }
              if (is_null($row["Job_Info"])) {
                  $arrAllocations['jobs'][$jobdutyDate][$jobid]["Job_Info"] = '';
              } else {
                  $arrAllocations['jobs'][$jobdutyDate][$jobid]["Job_Info"] = htmlspecialchars($row["Job_Info"] ?? '');
              }
          }
      }
  }

  /** Check Day Editable Code End */
  if (!empty($result)) {
    foreach ($result as $row) {
      if ($row['Grid']=='AD') {
          $dutyid = $row["DutyID"];
          $scheduleId = $row["ScheduledPersonID"];
          $DayNumber = $row["DayNumber"];
          if (!is_null($row["ScheduledPersonID"]) || $row["ScheduledPersonID"]!=0) {
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffNumber"] = $row["StaffNumber"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["ScheduledPersonID"] = $row["ScheduledPersonID"] ?? 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["TriangleColour"] = $row["TriangleColour"] ?? 'None';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["allocationSPID"] = $row["allocationSPID"] ?? 0;

            if (!empty($row["FullName"])) {
              $name = $row["FullName"];
            } else {
              $name = $row["DisplayName"];
            }

            if ($intTeamID == $row["schedulingTeamId"]) {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["fullname"] = $name;
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["sortcode"] = $row["SortCode"];
            } else {
              if (!isset($arrAllocations['assigned'][$DayNumber][$scheduleId]["fullname"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["fullname"] =  $name;
              }
              if (!isset($arrAllocations['assigned'][$DayNumber][$scheduleId]["sortcode"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["sortcode"] =  $row["SortCode"];
              }
            }

            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffBBCEmail"] = $row["StaffInternalEmail"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffNONBBCEmail"] = $row["StaffExernalEmail"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffTextColour"] = $row["PersonFontColour"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StaffBackColour"] = $row["PersonBackgroundColour"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["iscopy"] = $row["iscopy"];
            if(is_null($row["isEdited"])) {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["isEdited"] = 0;
            } else {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["isEdited"] = $row["isEdited"];
            }
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["edited"] = $row["isDutyEditable"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["editable"] = $row["isEditable"];
            $starttime = intval($row["StartTime"]);
            $endtime = intval($row["EndTime"]);
            $misc = 0;
            if ((empty($starttime)) && (empty($endtime))) {
              $endtime = intval($row["Duration"]);
              $misc=1;
            }
            if($starttime==0 && $endtime==0 && $row["Duration"]==0) {
              $endtime = 3600;
            }

            if ($row["DutyName"]=='Leave' || $row["DutyName"]=='OFF Leave') {
              $endtime = 3600;
            }
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["miscduty"] = $misc;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["starttime"] = $starttime;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["endtime"] = $endtime;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["StartDate"] = $row['StartDate'];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["EndDate"] = $row['EndDate'];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["editable"] = $row['isEditable'];

            if (is_null($row["inBuilding"])) {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["inbuilding"] = 0;
            } else {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["inbuilding"] = $row["inBuilding"];
            }

            if (is_null($row["active"])) {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["signin"] = 0;
            } else {
              if ($row["StartTime"] == $row["SignInStartTime"] && $row["EndTime"] == $row["SignInEndTime"]) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["signin"] = $row["active"];
              } else {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["signin"] = 2;
              }
            }
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["duty"] = !empty($row["DutyName"]) ? $row["DutyName"] : 'U';
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["duration"] = $row["Duration"];
            if ($DayNumber==1) {
              if (($arrAllocations['assigned'][$DayNumber][$scheduleId]["duty"]<>'U') && ($arrAllocations['assigned'][$DayNumber][$scheduleId]["duty"]<>'Leave')){
                $dutyearlieststart[]=$arrAllocations['assigned'][$DayNumber][$scheduleId]["starttime"];
                if ($starttime>=$endtime){
                  $dutyend[]=$arrAllocations['assigned'][$DayNumber][$scheduleId]["endtime"];
                } else {
					$dutyend[]=$arrAllocations['assigned'][$DayNumber][$scheduleId]["endtime"]-86400;
				}
              }
            }
            if (is_null($row["dutyColorId"]) || $row["dutyColorId"]=='0') {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["backcolour"] = '#708090';
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#fff0f5';
            } else {
              if (!empty($row["ColourBackground"]) && !empty($row["ColourFont"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["backcolour"] = '#'.$row["ColourBackground"];
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#'.$row["ColourFont"];
              }else {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["backcolour"] = '#708090';
                $arrAllocations['assigned'][$DayNumber][$scheduleId]["fontcolour"] = '#fff0f5';
              }

            }

            $arrAllocations['assigned'][$DayNumber][$scheduleId]["dutycomments"] = $row["DutyComments"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["personcomments"] = $row["PersonComments"];
            // Set the flag to Internally edited if the duty starts 1 day from now
            if ($row["InternalEdited"] == 1 && ($starttime + strtotime($thisdate) < strtotime("+ 1 Day"))) {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["internaledited"] = 1;
            } else {
              $arrAllocations['assigned'][$DayNumber][$scheduleId]["internaledited"] = 0;
            }

            $arrAllocations['assigned'][$DayNumber][$scheduleId]["IsAttention"] = $row["IsAttention"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["IsRequest"] = $row["IsRequest"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["isPublished"] = (empty($row["isPublished"])|| is_null($row["isPublished"])) ? 0 : $row["isPublished"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["DutyParentID"] = $row["DutyParentID"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["DutyID"] = !empty($row["DutyID"]) ? $row["DutyID"] : 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["ManualEDP"] = !empty($row["ManualEDP"]) ? $row["ManualEDP"] : 0;
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["DutyDate"] = $row["DutyDate"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["MannualOThours"] = $row["MannualOThours"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["MarkedOvertime"] = $row["MarkedOvertime"];
      			$arrAllocations['assigned'][$DayNumber][$scheduleId]["IsHomeTeam"] = $row["IsHomeTeam"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["pdlStartTime"] = $row["LeaveStartTime"];
            $arrAllocations['assigned'][$DayNumber][$scheduleId]["pdlEndTime"] = $row["LeaveEndTime"];
            // JobUnder Duty Start
            $dutyid = $row["DutyID"];
            if (!is_null($row["JobID"])) {
              $jobstarttime = floor($row["JobStartTime"]);
              $jobendtime = floor($row["JobEndTime"]);
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["jobid"] = $row["JobID"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["dutyId"] = $dutyid;
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["jobname"] = $row["JobName"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["starttime"] = $jobstarttime;
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["endtime"] = $jobendtime;
              if(is_null($row["isJobEdited"])){ $row["isJobEdited"]=0;}
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["edited"] = $row["isJobEdited"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["JobParentID"] = $row["JobParentID"];
              $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["programme"] = $row["Programme"];

              if (empty($row["JobBackColour"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["backcolour"] = '#ddffdd';
              } else {
                if (is_numeric($row["JobBackColour"])) {
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["backcolour"] = intotohex($row["JobBackColour"]);
                } else {
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["backcolour"] = $row["JobBackColour"];
                }
              }

              if (empty($row["JobFontColour"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["fontcolour"] = '#000000';
              } else {
                if (is_numeric($row["JobFontColour"])) {
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["fontcolour"] = intotohex($row["JobFontColour"]);
                }
                else {
                  $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["fontcolour"] = $row["JobFontColour"];
                }
              }

              if (is_null($row["JobComments"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["comments"] = '';
              } else {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["comments"] = htmlspecialchars($row["JobComments"]);
              }

              if (is_null($row["Job_Info"])) {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["Job_Info"] = '';
              }
              else {
                $arrAllocations['assigned'][$DayNumber][$scheduleId]['jobs'][$row["JobID"]]["Job_Info"] = htmlspecialchars($row["Job_Info"]);
              }

            }
          }
        $i++;
      }
    }
  }

  $arrAllocations['IsDayEditable']=$arrEditable;
  $arrAllocations['lockStatus']=$arrLocked;
  $arrAllocations['ManualLock']=$arrMLocked;
  $arrAllocations['isDayEdited']=$arrDayEdited;

  $earlieststart =0;
  $lateststart =24;

  // ================================================================================ END The Allocated Duties ================================================================================

  $arrAllocations['earlieststart'] = $earlieststart;
  $arrAllocations['lateststart'] = $lateststart;

  if (!empty($dutyearlieststart) && count($dutyearlieststart)>0){
    $arrAllocations['showdutystart'] = min($dutyearlieststart);
  }  else {
    $arrAllocations['showdutystart'] = 0;
  }

  if (!empty($dutyend) && count($dutyend)> 0) {
    $arrAllocations['showdutyend'] = max($dutyend);
  }  else {
    $arrAllocations['showdutyend'] = 0;
  }

  return json_encode($arrAllocations);
}

/**
* This function is used to get the Data for Compare AllocationsDay
* @param $intDepartmentID This param contains the intDepartmentID information
* @param $intDay This param contains the WeekDay information
* @param $intWeek This param contains the WeekNo information
* @return Returns Array Allocation Compare Day
*/
// ##########Get the Allocations where the Duty has been edited ########################

function ReadAllocationsDayCompare($intTeamID, $intWeek, $intDay) {
  $earlieststart = 86400;
  $lateststart = 0;
  $lateststartarr = [];
  try {
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "SELECT AD_AllocationsDutyID as ID ,
          AD_DutyName AS UnEditedDutyName,
          AD_Duration AS UnEditedDuration,
          AD_StartTimeSec AS UnEditedStartTime,
		  AD_EndTimeSec AS UnEditedEndTime,
		  AD_DutyColourID AS UnEditedDutyColour,
		  ASP_SchedulingPersonID AS UnEditedSchedulledPersonID,
		  AJ_AllocateJobID  AS UnEditedJobID,
		  AJ_JobName AS UnEditedJobName,
		  AJ_JobStartTimeSec AS UnEditedJobStartTime,
		  AJ_JobEndTimeSec AS UnEditedJobEndTime,
		  AJ_JobBGColour AS UnEditedJobBackColour,
		  AJ_JobFontColour AS UnEditedJobFontColour,
		  AJ_IsEditedJobAttention AS UnEditedJobEdited,
		  AJ_Comments AS UneditedJobComments,
		  DATEDIFF(DAY,AJ_JobStartTimeLocal,AJ_JobEndTimeLocal) AS MidnightFlag,
          ISNULL(spl.BackgroundColour, '#cccccc') AS StaffBackColour,
          ISNULL(spl.fontcolour, '#000000') AS StaffTextColour,spl.SortCode,
		  UD_DisplayName AS Fullname,
		   UD_NetLogin as Login
      FROM Allocations as a (NoLock)
	  INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
	  LEFT JOIN AllocationsScheduledPersons asp on ASP_AllocationsDutyID = AD_AllocationsDutyID
	  LEFT OUTER JOIN Allocationsjobs as aj (NoLock) ON aj.AJ_AllocationsDutyID = AD_AllocationsDutyID
	  Left JOIN ScheduledPersonTeam_LINK as spl (NoLock) ON spl.ScheduledPersonID =ASP_SchedulingPersonID
				AND spl.TeamID = AL_SchedulingTeamID
				and spl.scheduledType = 1
				and AD_DutyDate between spl.StartDate and spl.EndDate
	  left JOIN UserDetails ud on UD_UserID = ASP_SchedulingPersonID
	  WHERE AL_SchedulingTeamID = :teamID
		AND AL_WeekNumber = :intWeek
		AND AD_DutyStatus <> 9
    AND AD_iDay = :intDay
		AND AD_IsEditedDutyAttention = 1";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(':intWeek', $intWeek, PDO::PARAM_INT);
    $stmt->bindParam(':intDay', $intDay, PDO::PARAM_INT);
    $stmt->bindParam(':teamID', $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $rsAllocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
    catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
    }
	foreach($rsAllocations as $row) {
      $arrAllocations['duties'][$row['ID']]['fullname'] = $row['Fullname'];
      $arrAllocations['duties'][$row['ID']]['sortcode'] = $row['SortCode'];
      $arrAllocations['duties'][$row['ID']]['StaffTextColour'] = $row['StaffTextColour'];
      $arrAllocations['duties'][$row['ID']]['StaffBackColour'] = $row['StaffBackColour'];
      // The Unedited Duty

      $arrAllocations['duties'][$row['ID']]['unedited']['dutyname'] = $row['UnEditedDutyName'];

      if (is_null($row['UnEditedStartTime'])) {
        $arrAllocations['duties'][$row['ID']]['unedited']['starttime'] = 0;
        $arrAllocations['duties'][$row['ID']]['unedited']['endtime'] = 0;
        $arrAllocations['duties'][$row['ID']]['unedited']['duration'] = $row["UnEditedDuration"];
      } else {
        $starttime = $row['UnEditedStartTime'];
        $endtime = $row['UnEditedEndTime'];
        $arrAllocations['duties'][$row['ID']]['unedited']['starttime'] = $starttime;
        $arrAllocations['duties'][$row['ID']]['unedited']['endtime'] = $endtime;
        $arrAllocations['duties'][$row['ID']]['unedited']['duration'] = $row["UnEditedDuration"];
        if ($earlieststart > $starttime) {
          $earlieststart = $starttime;
        }
        if ($starttime < $endtime) {
          $lateststartarr[] = $endtime;
        } else {
		  $lateststartarr[] = 86400+ $endtime;
		}
      }

      /*Color Code Start */
     if (is_null($row["UnEditedDutyColour"]) || $row["UnEditedDutyColour"]=='0') {
        $arrAllocations['duties'][$row['ID']]['unedited']["backcolour"] = '#cccccc';
        $arrAllocations['duties'][$row['ID']]['unedited']["fontcolour"] = '#000000';
      } else {
          $colorid = $row["UnEditedDutyColour"];
          $ColorSQl = "SELECT ColourBackground,ColourFont FROM REF_MasterDutyColours WHERE MasterDutyColourID =?";
          $stmt = $pdo->prepare($ColorSQl);
          $stmt->bindParam(1, $colorid, PDO::PARAM_INT);
          $stmt->execute();
          $result2 = $stmt->fetch(PDO::FETCH_ASSOC);

          if(!empty($result2) && count($result2) && $result2['ColourBackground']!='' && $result2['ColourFont']!='') {
            $arrAllocations['duties'][$row['ID']]['unedited']["backcolour"] = '#'.$result2['ColourBackground'];
            $arrAllocations['duties'][$row['ID']]['unedited']["fontcolour"] = '#'.$result2['ColourFont'];
            } else {
            $arrAllocations['duties'][$row['ID']]['unedited']["backcolour"] = '#cccccc';
            $arrAllocations['duties'][$row['ID']]['unedited']["fontcolour"]= '#000000';
            }

      }
      /* Color Code End */
      // The Unedited Jobs.....
      if (!is_null($row['UnEditedJobID'])) {
        $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]['jobname'] = $row["UnEditedJobName"];

        $starttime = $row["UnEditedJobStartTime"];
        $endtime = $row["UnEditedJobEndTime"];
        $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]['starttime'] = $starttime;
        $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]['endtime'] = $endtime;
        if ($earlieststart > $starttime) {
          $earlieststart = $starttime;
        }
        if ($lateststart < $endtime) {
          $lateststart = $endtime;
        }
        $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]['comments'] = $row["UneditedJobComments"];
		 $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]['midnightFlag'] = $row["MidnightFlag"];

        // Back colour
        if (is_null($row["UnEditedJobBackColour"])) {
          $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]["backcolour"] = '#cccccc';
        }
        else {
          $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]["backcolour"] = $row["UnEditedJobBackColour"];

        }
        // Font Colour
        if (is_null($row["UnEditedJobFontColour"])) {
          $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]["fontcolour"] = '#000000';
        }
        else {
          $arrAllocations['duties'][$row['ID']]['unedited']["jobs"][$row['UnEditedJobID']]["fontcolour"] = $row["UnEditedJobFontColour"];

        }
      }
      //  End the Unedited Jobs

     if ($earlieststart > $starttime) {
       $earlieststart = $starttime;
     }
     if (!empty($lateststartarr)) {
		 $lateststart = max($lateststartarr);
     }
     // End the Edited Duty

      //  End the Unedited Jobs
    }


    if (isset($arrAllocations)) {
      if ($earlieststart > 0) {
        $arrAllocations['earlieststart'] = intval(round($earlieststart/3600));
      } else {
        $arrAllocations['earlieststart'] = $earlieststart;
      }
      //dd($earlieststart,$lateststart,$arrAllocations['earlieststart']);
      $arrAllocations['lateststart'] = intval(ceil($lateststart/3600));
      return ($arrAllocations);
    }
}
// #################### End  ReadAllocationsDayCompare #####################################################
function ReadAllocationsDayDeleted ($intTeamID, $intWeek, $intDay) {
  $earlieststart = 86400;
  $lateststart = 0;
  $pdo = OpenDBLinkA7();

  $sql = "	SELECT AD_AllocationsDutyID        DutyID,
			   AD_DutyName DutyName,
			   AD_Duration Duration,
			   AD_StartTimeSec AS DutyStartTime,
			   AD_EndTimeSec   AS DutyEndTime,
			   AJ_AllocateJobID        JobID,
			   AJ_AllocationsDutyID AllocationID,
			   AJ_ProgrammeID ProgrammeId,
			   PS.Programme,
			   AJ_JobName as JobName,
			   AJ_JobStartTimeSec AS JobStartTime,
			   AJ_JobEndTimeSec   AS JobEndTime
		FROM   Allocations AL
		INNER JOIN AllocationsDuties (NoLock) AS AD on AL_AllocationsID = AD_AllocationsID
		LEFT JOIN AllocationsJobs (NoLock) AS AJ ON AJ.AJ_AllocationsDutyID = AD_AllocationsDutyID
		LEFT JOIN Programmes AS PS ON AJ_ProgrammeID = PS.ID
		WHERE AL_WeekNumber = :intWeek
		  AND AD_iDay = :intDay
		  AND AL_SchedulingTeamID = :teamId
		  AND AD_DutyStatus = 9
		UNION
		SELECT AD_AllocationsDutyID        DutyID,
			   AD_DutyName DutyName,
			   AD_Duration Duration,
			   AD_StartTimeSec AS DutyStartTime,
			   AD_EndTimeSec   AS DutyEndTime,
			   AJ_AllocateJobID        JobID,
			   AJ_AllocationsDutyID AllocationID,
			   AJ_ProgrammeID ProgrammeId,
			   PS.Programme,
			   AJ_JobName as JobName,
			   AJ_JobStartTimeSec AS JobStartTime,
			   AJ_JobEndTimeSec   AS JobEndTime
		FROM   Allocations AL
		INNER JOIN AllocationsDuties (NoLock) AS AD on AL_AllocationsID = AD_AllocationsID
		LEFT JOIN AllocationsJobs (NoLock) AS AJ ON AJ.AJ_AllocationsDutyID = AD_AllocationsDutyID
		LEFT JOIN Programmes AS PS ON AJ_ProgrammeID = PS.ID
		WHERE AL_WeekNumber = :intWeek2
		  AND AD_iDay = :intDay2
		  AND AL_SchedulingTeamID = :teamId2
		  AND AD_DutyType = 10
		  AND AJ_JobStatus = 9
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':intWeek', $intWeek, PDO::PARAM_STR);
  $stmt->bindParam(':intDay', $intDay, PDO::PARAM_STR);
  $stmt->bindParam(':teamId', $intTeamID, PDO::PARAM_STR);
  $stmt->bindParam(':intWeek2', $intWeek, PDO::PARAM_STR);
  $stmt->bindParam(':intDay2', $intDay, PDO::PARAM_STR);
  $stmt->bindParam(':teamId2', $intTeamID, PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetchall(PDO::FETCH_ASSOC);

  foreach($result as $row) {
    $dutyid = $row["DutyID"];

    if ($row['DutyID'] > 0 && $row['DutyName'] <> '' && mb_strtolower($row['DutyName']) <> 'unassigned job') {
      $arrAllocations['Duties'][$dutyid]["id"] = $row["DutyID"];
      $arrAllocations['Duties'][$dutyid]["DutyName"] = $row["DutyName"];
      $arrAllocations['Duties'][$dutyid]["DutyStartTime"] = $row['DutyStartTime'];
      $arrAllocations['Duties'][$dutyid]["DutyEndTime"] = $row['DutyEndTime'];
      if($row["JobID"] > 0) {
        $arrAllocations['Duties'][$dutyid]["Jobs"][$row['JobID']]['JobName'] = $row["JobName"];
        $arrAllocations['Duties'][$dutyid]["Jobs"][$row['JobID']]["JobStartTime"] = $row["JobStartTime"];
        $arrAllocations['Duties'][$dutyid]["Jobs"][$row['JobID']]["JobEndTime"] = $row["JobEndTime"];
        $arrAllocations['Duties'][$dutyid]["Jobs"][$row['JobID']]["Programme"] = $row["Programme"];
      }
    }
    else {
      $arrAllocations['Jobs'][$row['JobID']]['JobName'] = $row["JobName"];
      $arrAllocations['Jobs'][$row['JobID']]["JobStartTime"] = $row["JobStartTime"];
      $arrAllocations['Jobs'][$row['JobID']]["JobEndTime"] = $row["JobEndTime"];
      $arrAllocations['Jobs'][$row['JobID']]["Programme"] = $row["Programme"];
    }
  }

  if (isset($arrAllocations)) {
    return ($arrAllocations);
  }
}

/**
* This function is used to get the schedulingpersonId by using StaffNumber .
*
* @param $StaffNumber This param contains the StaffNumber information
*
* @return $SchedulingPersonId Returns the Schedulingpersonid.
*/
function getSchedulingPersonId($StaffNumber){

  $pdo = OpenDBLinkA7();
  $Query = "exec [dbo].[usp_get_SchedulePersonByStaffNumber] ?";
  $stmt = $pdo->prepare($Query);
  $stmt->bindParam(1, $StaffNumber, PDO::PARAM_STR);
  $stmt->execute();
  $resultData = $stmt->fetch(PDO::FETCH_ASSOC);

  if(!empty($resultData['ScheduledPersonID'])){

    $SchedulingPersonId =  $resultData['ScheduledPersonID'];
  }else{

    $SchedulingPersonId=0;
  }

  return $SchedulingPersonId;
}

/**
* This function is used to get the Allocation data of a particular scheduled Person.
*
* @param $intWeek This param contains the weekNumber.
*
* @param $intDay This param contains the Day.
*
* @param $schedulingPersonId This param contains the SchedulingPerson ID.
*
* @param $intTeamID This param contains the Team ID.
*
* @return $arrAllocations array Returns the data of allocations for a particular ScheduledPerson.
*/
function ReadIndividualAllocationsDay($intWeek, $intDay, $schedulingPersonId, $isShiftleader) {

  $pNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  $earlieststart = 86400;
  $lateststart = 0;

  $intDay = intval($intDay);

  $pdo = OpenDBLinkA7();

 $strQuery = "exec [dbo].[usp_get_ReadIndividualAllocationsDay] $intWeek, $intDay, $schedulingPersonId, $isShiftleader, '".$pNetLogin."'";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if(count($result)>0) {
     foreach($result as $row) {
      $intCurrentTeamID = $row['schedulingTeamId'];
      $arrAllocations['FullName'] = $row['FullName'];
      $arrAllocations['Allocations'][$intCurrentTeamID]['dutyname'] = $row['DutyName'];
      $arrAllocations['Allocations'][$intCurrentTeamID]['DutyStartDate'] = $row['DutyStartDate'];

      if(empty($row['StartTime']) && empty($row['EndTime']) && $row["Duration"]==0) {
        $intDutyEndTime = 10800;
        $intDutyStartTime = 0;
      }

      if ($row['StartTime'] > 0 || $row['EndTime'] > 0) {
        $intDutyStartTime = intval($row['StartTime']);
        $intDutyEndTime = intval($row['EndTime']);
        $misc = 0;
      } else {
        if($row['StartTime'] == 0 && $row['EndTime'] == 0){
          $intDutyEndTime = intval($row["Duration"]);
          $intDutyStartTime = intval($row['StartTime']);
          $misc = 1;
        } else if($row['DutyName'] == 'U') {
            $intDutyStartTime = 0;
            $intDutyEndTime = 10800;
            $misc = 1;
        }
      }

      if(($intDutyEndTime == 0) && ($intDutyStartTime == 0)) {
        $earlieststart = 86400;
        $lateststart = 0;
      } else {
        if ($earlieststart > $intDutyStartTime) {
          $earlieststart = $intDutyStartTime;
        }

        if ($lateststart < $intDutyEndTime) {
          $lateststart = $intDutyEndTime;
        }
      }


      $arrAllocations['Allocations'][$intCurrentTeamID]['starttime'] = $intDutyStartTime;
      $arrAllocations['Allocations'][$intCurrentTeamID]['endtime'] = $intDutyEndTime;
      $arrAllocations['Allocations'][$intCurrentTeamID]['duration'] = $row['Duration'];
      $arrAllocations['Allocations'][$intCurrentTeamID]['misc'] = $misc;
      $arrAllocations['Allocations'][$intCurrentTeamID]['dutycomments'] = $row['DutyComments'];
      $arrAllocations['Allocations'][$intCurrentTeamID]['personcomments'] = $row['PersonComments'];
	  $arrAllocations['Allocations'][$intCurrentTeamID]['IsHomeTeam'] = $row['IsHomeTeam'];

      if (is_null($row["dutyColorId"]) || $row["dutyColorId"]=='0') {
        $arrAllocations['Allocations'][$intCurrentTeamID]['AllocBackColour'] = '#708090';
        $arrAllocations['Allocations'][$intCurrentTeamID]["AllocFontColour"] = '#fff0f5';
      } else {
        $arrAllocations['Allocations'][$intCurrentTeamID]['AllocFontColour'] = '#'.$row['AllocFontColour'];
        $arrAllocations['Allocations'][$intCurrentTeamID]['AllocBackColour'] = '#'.$row['AllocBackColour'];
      }

      if (!is_null($row['JobID'])) {
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['jobname'] = $row['JobName'];
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['jobstart'] = intval($row['JobStartTime']);
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['jobend'] = intval($row['JobEndTime']);
        // Back colour
        if (is_null($row["JobBackColour"])) {
          $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['backcolour'] = '#aaaaaa';
        }
        else {
          if (is_numeric($row["JobBackColour"])) {
            $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['backcolour'] = intotohex($row["JobBackColour"]);
          }
          else {
            $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['backcolour'] = $row["JobBackColour"];
          }
        }
        // Font Colour
        if (is_null($row["JobFontColour"])) {
          $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['fontcolour'] = '#ffffff';
        }
        else {
          if (is_numeric($row["JobFontColour"])) {
            $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['fontcolour'] = intotohex($row["JobFontColour"]);
          }
          else {
            $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['fontcolour'] = $row["JobFontColour"];
          }
        }
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['comments'] = $row["JobComments"];
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['aftermidnight'] = $row["aftermidnight"];
      }
    }
  }
  $arrAllocations['earlieststart'] = $earlieststart;
  $arrAllocations['lateststart'] = $lateststart;

  if (isset($arrAllocations)) {
    return ($arrAllocations);
  }
}
/**
 * Description :This function Create Unallocated Job grid
 * @param $arrjobs [] of  job items
 * @param $earlieststart start time for jobs
 * @param $hourwidth setting
 * @param $iseditable status
 * return Job HTML DIV
 */

function drawunallocjobs ($arrjobs, $earlieststart, $hourwidth, $iseditable,$intTeamID,$selectedDay,$currday) {
  if ($iseditable == 1) {
    $draggable = ' draggable jobresizable ';
    $jobcontextmenu = ' job-context-menu-unassigned';
  } else {
    $draggable = '';
    $jobcontextmenu = '';
  }
  $txtjob = '';
  if (isset($arrjobs) && (count($arrjobs)>0)){
	  foreach ($arrjobs as $jobid => $job) {
		$jobbackcolour = $job["backcolour"];
		$jobfontcolour = $job["fontcolour"];
		$allocatejobid = $job["allocatejobid"];
		$tip = '<font color=#99CCFF>'.$job["jobname"].'</font><br>';
		$tip.= gmdate("H:i", $job["starttime"])."-".gmdate("H:i", $job["endtime"]);
		if ($job["JobComments"] != '') {
		  $tip.= "<br><font color=#00CC00>".$job["JobComments"].'</font>';
		}
		if ($job["Job_Info"] != '') {
		  $tip.= '<br><font color=#00CC00>';
		  $tip.= $job["Job_Info"].'</font>';
		}
		if ($job["programme"] != '') {
		  $tip.= '<br>('.$job["programme"].')';
		}
		$starttime = $job["starttime"];
		$endtime = $job["endtime"];
		$line = 0;
		if (isset($arrlines)) {
		  $counter = 0;
		  foreach ($arrlines as $line => $arrsub) {
			$confilct = 0;
			foreach ($arrsub as $times) {
			  if ($times['start']>$times['end']){
					$times['end']=$times['end']+86400;
				}
				$timematchend = $endtime;
				$timematchstart = $starttime;
				if ($starttime>$endtime){
					$timematchend=$timematchend+86400;
				}

			  if ($times['start'] <= $timematchend && $times['end'] >= $timematchstart) {
				$confilct = 1;
			  }
			}
			if ($confilct == 0) {
			  break;
			}
			$counter++;
		  }
		  $arrlines[$counter][$job["jobid"]]['start'] = $starttime;
		  $arrlines[$counter][$job["jobid"]]['end'] = $endtime;
		} else {
		  $arrlines[0][$job["jobid"]]['start'] = $starttime;
		  $arrlines[0][$job["jobid"]]['end'] = $endtime;
		  $counter = 0;
		}
		if ($selectedDay>1){
			$midval = ($currday-1)*(1500/$selectedDay);
			$zindex="z-index:1;";
			$left = $midval + (((($starttime) / 3600) - $earlieststart + 1) * $hourwidth) - $hourwidth;
			$right = $midval+ (((($endtime) / 3600) - $earlieststart + 1) * $hourwidth) - $hourwidth;

		} else {
			if ($endtime < $starttime){
				$endtime=86400 + $endtime;
			}
			$midval= 0;
			$zindex="";
			$left = (((($midval+$starttime) / 3600) - $earlieststart + 1) * $hourwidth) - $hourwidth;
			$right = (((($midval+$endtime) / 3600) - $earlieststart + 1) * $hourwidth) - $hourwidth;
		}

		$width = $right - $left;

		if (is_null($job["edited"])) { $job["edited"]=0;}
		$txtjob.= '<div align="center"  jobtype="1" id="'.$jobid.'" isEdited="'.$job["edited"].'" parentId="'.$job['allocatejobid'].'" allocatejobid="'.$allocatejobid.'" data-teamid ="'.$intTeamID.'" class="boxed '.$draggable.' tipjob handcursor'.$jobcontextmenu.'" style="overflow:hidden; width: '.$width.'px; height: 15px; position:absolute; '.$zindex.' left:'.$left.'px; top:'.($counter * 20).'px; background-color:'.$jobbackcolour.'" data-qtip-content="'.$tip.'"  onmouseover="customtipjob(\'showtooltip\',this,'.$jobid.',1,100)"  onmouseleave="customtipjob(\'hidetooltip\')">';
		$txtjob.= '<font color="'.$jobfontcolour.'">'.$job["jobname"].'</font>';
		$txtjob.= '<input type="hidden" id="unaljobstart_'.$jobid.'" value='.gmdate("H:i", $starttime).'>';
		$txtjob.= '<input type="hidden" id="unaljobend_'.$jobid.'" value='.gmdate("H:i", $endtime).'>';
		$txtjob.= '<input type="hidden" id="jobmidnight_'.$jobid.'" value='.$job["JobMidnightFlag"].'>';
		$txtjob.= '</div>';
	  }
      $arret['rows'] = count($arrlines);
	  $arret['jobs'] = $txtjob;
	  return ($arret);
   } else {
	  $arret['rows'] = 1;
	  $arret['jobs'] = '';
	  return ($arret);
   }
}

// ================================================================================================
function checkwillfit($dutyid = 0, $jobstarttime = '', $jobendtime = '', $jobid = 0, $WeekNumber = '', $iday = '') {

   $pdo = OpenDBLinkA7();

    if (!is_numeric($dutyid)) {
        $dutyid = 0;
    }
    $sql = "exec [dbo].[usp_get_CountOfJobsByTime] ?,?,?,?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $jobid, PDO::PARAM_INT);
    $stmt->bindParam(2, $dutyid, PDO::PARAM_INT);
    $stmt->bindParam(3, $jobstarttime, PDO::PARAM_STR);
    $stmt->bindParam(4, $jobendtime, PDO::PARAM_STR);
    $stmt->execute();

   $jobrow = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($jobrow['CountJobs'] == 0) {
     $willfit = 0;
   }
   else {
     $willfit = 1;
   }

   return ($willfit);
   }

// ================================================================================================
function checkJobOverlap($dutyid = 0, $jobstarttime = '', $jobendtime = '', $midnight = 0, $jobId = '') {

   $pdo = OpenDBLinkA7();

    if (!is_numeric($dutyid)) {
        $dutyid = 0;
    }
    $sql = "SELECT dbo.ufn_check_JobOverLapNewJob(".$dutyid.",'".$jobstarttime."','".$jobendtime."'," . $midnight . ", '" . $jobId . "') AS JobCount";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $jobrow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($jobrow['JobCount'] == 0) {
     $willfit = 0;
    } else {
     $willfit = 1;
    }

  return ($willfit);
}

/**
* This function is used to get the Grid Checks.
*
* @param $date This param contains the date.
*
* @param $intTeamID This param contains the Team ID.
*
* @return array Returns the status and period from the DB as per the scheduling teams ID
*/
function getgridchecks($date, $intTeamID) {

  $db = OpenDatabase();
  $query = "SELECT status, period
            FROM  GridChecks
            WHERE (dDate = CONVERT(DATETIME, '$date 00:00:00', 102))
            AND (SchedulingTeamId = $intTeamID)";

  $checks = sqlsrv_query($db, $query);

  while($row = sqlsrv_fetch_array($checks)){
    $arrchecks[$row['period']] = $row['status'];
  }
  if (isset($arrchecks)) {
    return ($arrchecks);
  }
}

function getgridchecksinfo ($arrgridchecks, $period) {

  if (isset($arrgridchecks[$period])) {
    $status = $arrgridchecks[$period];
      $tipgridcheck = ' tipgridcheckcomments';
      $customtipgridcheck = ' onmouseover="customtipgridcheck(\'showtooltip\',this)" onmouseleave="customtipgridcheck(\'hidetooltip\')"';
      $img = match ($status) {
          0 => 'green_tick.png',
          1 => 'messagebox_warning.png',
          // Not checked
          default => 'red_cross.png',
      };
  }
  else {
    $img = 'red_cross.png';
    $tipgridcheck = '';
    $customtipgridcheck = '';
  }

  $arrret['image'] = $img;
  $arrret['tip'] = $tipgridcheck;
  $arrret['customtipgridcheck'] = $customtipgridcheck;
  return($arrret);
}


function GetLastDayUpdate ($strCurrentDate, $day7date,$intTeamID, $isViewPage, $dutyHistoryID,$jobHistoryID,$signinID,$signinLastUpdte) {

  $pdo = OpenDBLinkA7();

  $strQuery = "exec [dbo].[usp_get_ReadAllocationsDayLastUpdRefreshPage] '".$strCurrentDate."','".$day7date."',".$intTeamID.",".$isViewPage.",".$dutyHistoryID.",".$jobHistoryID.",".$signinID.",".$signinLastUpdte;

  $stmt = $pdo->prepare($strQuery);
  // The parameters
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  return ($result);
}
function MakeDayEdited ($intWeek, $intDay, $intteamID) {
  $time = time();

  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  try {
  $pdo = OpenDBLinkA7();
  // Make the Allocations Edited
  $strQuery = "INSERT INTO Allocations_edit
                                (SchedulingPersonID, DutyName, Duration, WeekNumber, iDay, StartTime, EndTime, SchedulingTeamId, DutyComments, PersonComments,
                                AllocationID, BackColour, FontColour, OrigAllocationID, LastUpdate, BaseCode,dutyColorId)
               SELECT
                                ISNULL(Allocations.SchedulingPersonID, 0) AS SchedulingPersonID, Allocations.DutyName, Allocations.Duration, Allocations.WeekNumber, Allocations.iDay, Allocations.StartTime,
                                Allocations.EndTime, Allocations.SchedulingTeamId, CONVERT(nvarchar(255), Allocations.DutyComments) AS Expr2, CONVERT(nvarchar(max),
                                Allocations.PersonComments) AS Expr3, Allocations.AllocationID, Allocations.BackColour, Allocations.FontColour, Allocations.AllocationID AS Expr1,
                                '$time', BaseCode,dutyColorId
               FROM             Allocations
               WHERE            (Allocations.WeekNumber = N'$intWeek')
               AND              (Allocations.iDay = $intDay)
               AND              (Allocations.SchedulingTeamId = $intteamID)
               AND              (NOT EXISTS
                                  (SELECT        1 AS Expr1
                                   FROM          Allocations_edit AS d
                                   WHERE        (SchedulingTeamId = Allocations.SchedulingTeamId)
                                   AND          (AllocationID = Allocations.ID)))";

  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
// Make the Jobs Edited
  $strQuery = "INSERT INTO Allocation_jobs_edit
                                (AllocateJobID, schedulingTeamId, SchedulingPersonID, AllocationID, WeekNumber, iDay, Programme, JobName, StartTime, EndTime, JobBackColour, JobFontColour, Comments,
                                MasterJobID, OrigAllocationID, LastUpdate)
               SELECT Jobs.ID, Jobs.schedulingTeamId, ISNULL(Jobs.SchedulingPersonID, 0) AS StaffNumber, Jobs.AllocationID, Jobs.WeekNumber, Jobs.iDay, Jobs.Programme,
                                Jobs.JobName, Jobs.StartTime, Jobs.EndTime, Jobs.JobBackColour, Jobs.JobFontColour, Jobs.Comments, Jobs.MasterJobID, Jobs.AllocationID AS Expr1,  '$time' AS Expr2
               FROM  Allocations_jobs as Jobs
               WHERE (Jobs.WeekNumber = $intWeek)
               AND              (Jobs.iDay = $intDay)
               AND              (Jobs.schedulingTeamId = $intteamID)
               AND              (NOT EXISTS
                                  (SELECT        1 AS Expr1
                                   FROM          Allocation_jobs_edit AS x
                                   WHERE         (schedulingTeamId = Jobs.schedulingTeamId)
                                   AND (AllocateJobID = Jobs.ID)))";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();

  $strHistory = "This day became editable on ".date("jS M Y")." at ".date("H:i").".<br>";

  $LastPublish = GetLastPublish($intWeek, $intteamID);

  if ($LastPublish !=0 ) {
    $strHistory.= "It was based on a publish from Allocate on ".date("jS M Y", $LastPublish).' at '.date("H:i", $LastPublish).'<hr>';
    $dateInsert  = date('Y-m-d H:i', $LastPublish);
  }
  else {
       $dateInsert  = date("Y-m-d H:i:s");

  }

    $strHistory = escapeSingleQuotes($strHistory);
$strQuery = "IF EXISTS      (SELECT   ID
                               FROM     DayIsEdited
                               WHERE    (SchedulingTeamId = $intteamID)
                               AND (WeekNumber = $intWeek)
                               AND (iDay = $intDay))
               UPDATE          DayIsEdited
               SET             isEdited = 1                      ,
                               History = CONCAT(ISNULL(History,''), '$strHistory'),
                               AllocatePublish = '$dateInsert',
                               MadeEditableBy  =  '$strUser'
               WHERE           (SchedulingTeamId = $intteamID)
               AND             (WeekNumber = $intWeek)
               AND             (iDay = $intDay)
             ELSE
               INSERT INTO     DayIsEdited (
                  DepartmentID   ,
                  WeekNumber     ,
                  iDay           ,
                  isEdited       ,
                  History        ,
                  AllocatePublish,
                  MadeEditableBy
                )
                VALUES (
                  $intteamID,
                  $intWeek        ,
                  $intDay         ,
                  1               ,
                  '$strHistory'   ,
                  '$dateInsert',
                  '$strUser'
                )";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
      } catch (PDOException $e) {
        logger()->critical('DB Error', (array) $e);
      }
}

/**
 *  This function is used to get current set filter for the daily screen
 *
 * @param $screenName This param contains the Screen Name Information
 * @param $teamId This param contains the TeamID Information
 *
 * @return Integer
 */
function GetCurrentSetDailyFilter($screenName, $teamId){
    $pdo = OpenDBLinkA7();
    $dailyFilterId = getCurrentSetFilter($screenName, $teamId, $pdo);
    if($dailyFilterId != ''){
        return $dailyFilterId;
    } else {
        return 0;
    }
}

/**
* This function is used to get the Allocation data of a particular scheduled Person.
*
* @param $intWeek This param contains the weekNumber.
*
* @param $intDay This param contains the Day.
*
* @param $schedulingPersonId This param contains the SchedulingPerson ID.
*
* @param $intTeamID This param contains the Team ID.
*
* @return $arrAllocations array Returns the data of allocations for a particular ScheduledPerson.
*/
function ReadIndvidualAllocationsSignIN($intWeek, $intDay, $schedulingPersonId, $intTeamID = 0) {

  $earlieststart = 86400;
  $lateststart = 0;

  $intDay=intval($intDay);

  $pdo = OpenDBLinkA7();

  $strQuery = "exec [dbo].[usp_get_ReadIndvidualAllocationsSignIN] ?,?,?,?";
  $stmt = $pdo->prepare($strQuery);
  // The parameters
  $stmt->bindParam(1, $intWeek, PDO::PARAM_INT);
  $stmt->bindParam(2, $intDay, PDO::PARAM_INT);
  $stmt->bindParam(3, $schedulingPersonId, PDO::PARAM_INT);
  $stmt->bindParam(4, $intTeamID, PDO::PARAM_INT);

  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if(count($result)>0){
     foreach($result as $row){

      $intCurrentTeamID = $row['schedulingTeamId'];
      $arrAllocations['FullName'] = $row['FullName'];
      $arrAllocations['Allocations'][$intCurrentTeamID]['dutyname'] = $row['DutyName'];
      $arrAllocations['Allocations'][$intCurrentTeamID]['duration'] = $row['Duration'];
      $arrAllocations['Allocations'][$intCurrentTeamID]['DutyStartDate'] = $row['DutyStartDate'];

      if (!is_null($row['StartTime'])) {
        $intDutyStartTime = intval($row['StartTime']);
        $arrAllocations['Allocations'][$intCurrentTeamID]['starttime'] = $intDutyStartTime;
        if ($earlieststart > $intDutyStartTime) {
          $earlieststart = $intDutyStartTime;
        }
        $intDutyEndTime = intval($row['EndTime']);
        $arrAllocations['Allocations'][$intCurrentTeamID]['endtime'] = $intDutyEndTime;
        if ($lateststart < $intDutyEndTime) {
          $lateststart = $intDutyEndTime;
        }
      }
      $arrAllocations['Allocations'][$intCurrentTeamID]['dutycomments'] = $row['DutyComments'];
      $arrAllocations['Allocations'][$intCurrentTeamID]['personcomments'] = $row['PersonComments'];
      if (is_null($row["AllocBackColour"])) {
        $arrAllocations['Allocations'][$intCurrentTeamID]['backcolour'] = '#aaaaaa';
      }
      else {
        if (is_numeric($row["AllocBackColour"])) {
          $arrAllocations['Allocations'][$intCurrentTeamID]['backcolour'] = intotohex($row["AllocBackColour"]);
        }
        else {
          $arrAllocations['Allocations'][$intCurrentTeamID]['backcolour'] = $row["AllocBackColour"];
        }
      }

      if (!is_null($row['JobID']) && $row['JobStaffNumber'] == $row['StaffNumber']) {
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['jobname'] = $row['JobName'];
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['jobstart'] = intval($row['JobStartTime']);
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['jobend'] = intval($row['JobEndTime']);
        // Back colour
        if (is_null($row["JobBackColour"])) {
          $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['backcolour'] = '#aaaaaa';
        }
        else {
          if (is_numeric($row["JobBackColour"])) {
            $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['backcolour'] = intotohex($row["JobBackColour"]);
          }
          else {
            $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['backcolour'] = $row["JobBackColour"];
          }
        }
        // Font Colour
        if (is_null($row["JobFontColour"])) {
          $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['fontcolour'] = '#ffffff';
        }
        else {
          if (is_numeric($row["JobFontColour"])) {
            $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['fontcolour'] = intotohex($row["JobFontColour"]);
          }
          else {
            $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['fontcolour'] = $row["JobFontColour"];
          }
        }
        $arrAllocations['Allocations'][$intCurrentTeamID]['jobs'][$row['JobID']]['comments'] = $row["JobComments"];
      }
  }
}
  $arrAllocations['earlieststart'] = $earlieststart;
  $arrAllocations['lateststart'] = $lateststart;

  if (isset($arrAllocations)) {
    return ($arrAllocations);
  }
}

/**
 *  This function is used to remove key value pair from array
 *
 * @param $allocArray This param contains the processing array Information
 * @param $keyName This param contains the Key Name Information
 * @param $valueName This param contains Value Information which needs to be removed
 *
 * @return array
 */
function removeElementWithValue($allocArray, $keyName, $valueName){
    foreach($allocArray as $subKey => $subArray){
        if($subArray[$keyName] == $valueName){
             unset($allocArray[$subKey]);
        }
    }
    return $allocArray;
}

/*
   Description : Default Loack setting BY TEAM on a Date
 * @param :$intTeamID int
 * @param :$strCurrentDate date
 * return []
 */

function getDefaultLocksettingByTeamONiDay($intTeamID,$strCurrentDate) {
  $arrAllocations=[];
  try {
      $pdo = OpenDBLinkA7();
      // Set the statement to use
      $sql = "exec [dbo].[usp_get_DailyAllocationLockStatus] ?,?,?";
      $stmt = $pdo->prepare($sql);
      // The parameters
      $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
      $stmt->bindParam(2, $strCurrentDate, PDO::PARAM_STR);
      $stmt->bindParam(3, $strCurrentDate, PDO::PARAM_STR);
      $stmt->execute();
      $result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      }
      /** Check Day Editable Code Start */
      if (!empty($result2)) {
        foreach ($result2 as $row) {
          if (!isset($arrMLocked[$strCurrentDate]['ManualLock'])) {
              $arrMLocked[$strCurrentDate]['ManualLock'] = $row['ManualLock'];
            }
          if (!isset($arrLocked[$strCurrentDate]['LockStatus'])) {
              $arrLocked[$strCurrentDate]['LockStatus'] = $row['LockStatus'];
            }
          }
      $arrAllocations['lockStatus']=$arrLocked;
      $arrAllocations['ManualLock']=$arrMLocked;
      }
      return $arrAllocations;
      /** Check Day Editable Code End */
  }
?>
