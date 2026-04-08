<?php

function ApplyFilter ($arrAllocations, $arrCurrentFilter, $arrFilters, $intWeekNumber, $intDepartmentID, $intWeeklyFilterOption = 0) {
  if (isset($arrAllocations)) {
    $intKeepit = 0;
    $intKeepit1 = 0;
    switch ($arrCurrentFilter[0]) {
      case 0:
        // base Codes
        if (isset($arrFilters[0][$arrCurrentFilter[1]]['BaseCodes'])) {
          $arrBaseCodes = array_flip($arrFilters[0][$arrCurrentFilter[1]]['BaseCodes']);
        }
        if ($arrFilters[0][$arrCurrentFilter[1]]['SortCodeFilter'] != '') {
          $arrSortCodes = (explode(";", $arrFilters[0][$arrCurrentFilter[1]]['SortCodeFilter']));
        }
        // Now do any Duties......
        if (isset($arrFilters[0][$arrCurrentFilter[1]]['DutyFilter'])) {
          $arrDuties = (explode(";", $arrFilters[0][$arrCurrentFilter[1]]['DutyFilter']));
        }
        // This is a preset filter - so use the $arrFilters
        if ($arrFilters[0][$arrCurrentFilter[1]]['AndMatch'] == 0) {
          // Match any.....
          foreach ($arrAllocations as $strStaffNumber => $arrStaffAllocation) {
            $intKeepit = 0;
            $intKeepit1 = 0;
            if (isset($arrSortCodes)) {
              foreach($arrSortCodes as $intSC => $SortCodeDesc) {
                $pos = strpos(strtoupper($arrStaffAllocation['SortCode']), strtoupper($SortCodeDesc));
                if ($pos !== false) {
                  $intKeepit = 1;
                }
              }
            }
            else {
              //if (!isset($arrBaseCodes)) {
                $intKeepit = 1;
              //}
            }
            if (isset($arrDuties)) {
              //loop through each day....
              for ($i=0; $i <= 6; $i++) {
                if (isset($arrStaffAllocation[$intWeekNumber][$i]['Duty'])) {
                  $intDayMatch = 0;
                  $intDayMatch1 = 0;
                  foreach($arrDuties as $intID => $DutyDesc) {
                    if (substr($DutyDesc, 0, 1) == '*') {
                      // It's a wildcard match
                      if (stripos($arrStaffAllocation[$intWeekNumber][$i]['Duty'], substr($DutyDesc, 1)) !== false) {
                        $intKeepit1 = 1;
                        $intDayMatch = 1;
                      }
                    }
                    else {
                      if (strtoupper(substr($arrStaffAllocation[$intWeekNumber][$i]['Duty'], 0, strlen($DutyDesc))) == strtoupper($DutyDesc) && $DutyDesc !== '') {
                        $intKeepit1 = 1;
                        $intDayMatch1 = 1;
                      }
                    }
                  }

                  if (isset($arrStaffAllocation[$intWeekNumber][$i]['BaseCode'])) {
                    if (isset($arrBaseCodes[$arrStaffAllocation[$intWeekNumber][$i]['BaseCode']])) {
                      $intKeepit1 = 1;
                      $intDayMatch = 1;
                    }
                  }
                  if ($intWeeklyFilterOption == 1 && $intDayMatch == 0 && $intDayMatch1 == 0) {
                    unset ($arrAllocations[$strStaffNumber][$intWeekNumber][$i]);
                  }
                }
              }
            }
            if ($intKeepit == 0 || $intKeepit1 == 0) {
              unset ($arrAllocations[$strStaffNumber]);
            }
          }
        }
        else {
          foreach ($arrAllocations as $strStaffNumber => $arrStaffAllocation) {
            if (isset($arrSortCodes)) {
              foreach($arrSortCodes as $intSC => $SortCodeDesc) {
                $pos = strpos(strtoupper($arrStaffAllocation['SortCode']), strtoupper($SortCodeDesc));
                if ($pos === false) {
                  unset ($arrAllocations[$strStaffNumber]);
                }
              }
            }

            if (isset($arrDuties)) {
              //loop through each day....
              $intCountDays = 0;
              for ($i=0; $i <= 6; $i++) {
                $intKeepit = 1;
                if (isset($arrStaffAllocation[$intWeekNumber][$i]['Duty'])) {
                  foreach($arrDuties as $intID => $DutyDesc) {
                    if (substr($DutyDesc, 0, 1) == '*') {
                      // It's a wildcard match
                      if (stripos($arrStaffAllocation[$intWeekNumber][$i]['Duty'], substr($DutyDesc, 1)) === false) {
                        $intKeepit = 0;
                      }
                    }
                    else {
                      if (strtoupper(substr($arrStaffAllocation[$intWeekNumber][$i]['Duty'], 0, strlen($DutyDesc))) != strtoupper($DutyDesc)) {
                        $intKeepit = 0;
                      }
                    }
                  }
                  if ($intKeepit == 0) {
                    unset ($arrAllocations[$strStaffNumber][$intWeekNumber][$i]);
                    $intCountDays++;
                  }
                }
              }
            }
            if ($intCountDays >= 7) {
              unset ($arrAllocations[$strStaffNumber]);
            }
          }
        }
        break;

      case 11:
        //This is a skills filter
        $arrProgs = StaffNumbersWhoCanDoProg($arrCurrentFilter[1]);
        if (isset($arrProgs)) {
          foreach ($arrAllocations as $strStaffNumber => $arrStaffAllocation) {
            if (!isset($arrProgs[$strStaffNumber])) {
              unset ($arrAllocations[$strStaffNumber]);
            }
          }
        }
      break;

      case 12:
        //This is a Duty Name

        $strDutyMatch = $arrCurrentFilter[1];

        $arrFullMatch = GetMatchOptions ($strDutyMatch);
        $arrNameMatch = $arrFullMatch[0];
        $intMatchType = $arrFullMatch[1];

        foreach ($arrAllocations as $strStaffNumber => $arrStaffAllocation) {
          $intKeepWeek = 0;
          for ($i=0; $i <= 6; $i++) {
            if (isset($arrStaffAllocation[$intWeekNumber][$i]))  {
              $intKeepit = GetMatch($arrStaffAllocation[$intWeekNumber][$i]['Duty'], $arrNameMatch, $intMatchType);
            }
            else {
              $intKeepit = 0;
            }
            if ($intKeepit == 0) {
              if ($intWeeklyFilterOption == 1) {
                unset ($arrAllocations[$strStaffNumber][$intWeekNumber][$i]);
              }
            }
            else {
              $intKeepWeek = 1;
            }
          }
          if ($intKeepWeek == 0) {
            unset ($arrAllocations[$strStaffNumber]);
          }
        }

      break;

      case 13:
        //This is a  Sort Code match
        $strCodeMatch = $arrCurrentFilter[1];
        $arrFullMatch = GetMatchOptions ($strCodeMatch);
        $arrNameMatch = $arrFullMatch[0];
        $intMatchType = $arrFullMatch[1];

        foreach ($arrAllocations as $strStaffNumber => $arrStaffAllocation) {
          $intKeepit = GetMatch($arrStaffAllocation['SortCode'], $arrNameMatch, $intMatchType);
          if ($intKeepit == 0) {
            unset ($arrAllocations[$strStaffNumber]);
          }
        }
      break;

      case 14:
        //This is a  StaffName match
        $strCodeMatch = $arrCurrentFilter[1];
        $arrFullMatch = GetMatchOptions ($strCodeMatch);
        $arrNameMatch = $arrFullMatch[0];
        $intMatchType = $arrFullMatch[1];

        foreach ($arrAllocations as $strStaffNumber => $arrStaffAllocation) {
          $intKeepit = GetMatch($arrStaffAllocation['FullName'], $arrNameMatch, $intMatchType);
          if ($intKeepit == 0) {
            unset ($arrAllocations[$strStaffNumber]);
          }
        }
      break;



    case 10:
      //This is a Favourites filter
      $arrUsers = StaffNumbersInFavGroup($arrCurrentFilter[1]);

      foreach ($arrAllocations as $strStaffNumber => $arrStaffAllocation) {
        $intKeepit = 0;
        $intKeepit1 = 0;
        if (!isset($arrUsers[$strStaffNumber])) {
          unset ($arrAllocations[$strStaffNumber]);
        }
      }
      break;
    }
    return $arrAllocations;
  }
}

function ApplyDutiesViewFilter ($arrAllocations, $arrFilterTexts, $intMatchType) {
  if (isset($arrAllocations['Duties'])) {
    if ($intMatchType == 0) {
      // Match any occurance
      foreach ($arrAllocations['Duties'] as $strDutyName => $arrDuty) {
        $intKeepit = 0;
        foreach ($arrFilterTexts as $intArrID => $strFilter) {
          if (substr($strFilter, 0, 1) == '*') {
            // It's a wildcard match
            if (stripos($strDutyName, substr($strFilter, 1)) !== false) {
              $intKeepit = 1;
            }
          }
          else {
            if (strtoupper(substr($strDutyName, 0, strlen($strFilter))) == strtoupper($strFilter)) {
              $intKeepit = 1;
            }
          }
        }
        if ($intKeepit == 0) {
          unset ($arrAllocations['Duties'][$strDutyName]);
        }
      }
    }
    else {
      // Match any occurance
      foreach ($arrAllocations['Duties'] as $strDutyName => $arrDuty) {
        $intKeepit = 1;
        foreach ($arrFilterTexts as $intArrID => $strFilter) {
          if (substr($strFilter, 0, 1) == '*') {
            // It's a wildcard match
            if (stripos($strDutyName, substr($strFilter, 1)) === false) {
              $intKeepit = 0;
            }
          }
          else {
            if (strtoupper(substr($strDutyName, 0, strlen($strFilter))) != strtoupper($strFilter)) {
              $intKeepit = 0;
            }
          }
        }
        if ($intKeepit == 0) {
          unset ($arrAllocations['Duties'][$strDutyName]);
        }
      }
    }
  }
  return ($arrAllocations);
}



function ApplyFilterDaily ($arrAllocations, $arrCurrentFilter, $arrFilters, $intDepartmentID) {

  $intKeepit = 0;
  $intKeepit1 = 0;
if (isset($arrAllocations['assigned'])) {
  switch ($arrCurrentFilter[0]) {
    case 0;
      // ANd OR Match?
      if ($arrFilters[1][$arrCurrentFilter[1]]['AndMatch'] == 0) {
        // Any  match
        // This is a preset filter - so use the $arrFilters
        // Now do any Duties......
        if ($arrFilters[1][$arrCurrentFilter[1]]['SortCodeFilter'] != '') {
          $arrSortCodes = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['SortCodeFilter']));
        }

        if ($arrFilters[1][$arrCurrentFilter[1]]['DutyFilter'] != '') {
          $arrDutiesFilters = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['DutyFilter']));
        }
        if ($arrFilters[1][$arrCurrentFilter[1]]['JobFilter'] != '') {
          $arrJobsFilters = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['JobFilter']));
        }
        if ($arrFilters[1][$arrCurrentFilter[1]]['JobStarts'] != '') {
          $arrJobsStart = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['JobStarts']));
        }
        if ($arrFilters[1][$arrCurrentFilter[1]]['JobContains'] != '') {
          $arrJobsContain = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['JobContains']));
        }

        foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
          $intKeepit = 0;
          $intKeepit1 = 0;
          $intKeepit2 = 0;
          //print_r($arrStaffAllocation);

          if (isset($arrSortCodes)) {
            foreach($arrSortCodes as $intSC => $SortCodeDesc) {
              $pos = strpos(strtoupper($arrStaffAllocation['sortcode']), strtoupper($SortCodeDesc));
              if ($pos !== false) {
                $intKeepit2 = 1;
              }
            }
          }
          else {
            //$intKeepit2 = 1;
          }
          if (isset($arrDutiesFilters)) {
            foreach($arrDutiesFilters as $intID => $DutyDesc) {
              if (substr($DutyDesc, 0, 1) == '*') {
                $DutyDesc = substr($DutyDesc, 1);
                $intPos = strpos(strtoupper($arrStaffAllocation['duty']), strtoupper($DutyDesc));
                if ($intPos !== false) {
                  $intKeepit = 1;
                }
              }
              else {
                if (strtoupper(substr($arrStaffAllocation['duty'], 0, strlen($DutyDesc))) == strtoupper($DutyDesc)) {
                  $intKeepit = 1;
                }
              }
            }
          }
          // And the Jobs.....
          if (isset($arrJobsFilters) && isset($arrStaffAllocation['jobs'])) {
            foreach ($arrStaffAllocation['jobs'] as $intJobID => $arrJob) {
              foreach($arrJobsFilters as $intID => $JobDesc) {
                if (strtoupper($arrJob['programme']) == strtoupper($JobDesc)) {
                  $intKeepit1 = 1;
                  //$arrAllocations['assigned'][$intDutyID]['jobs'][$intJobID]['HashIt'] = 1;
                }
              }
            }
          }
          // Job starts
          if (isset($arrJobsStart) && isset($arrStaffAllocation['jobs'])) {
            foreach ($arrStaffAllocation['jobs'] as $intJobID => $arrJob) {
              foreach($arrJobsStart as $intID => $JobDesc) {
                if (strtoupper(substr($arrJob['jobname'], 0, strlen($JobDesc))) == strtoupper($JobDesc)) {
                  $intKeepit1 = 1;
                  //$arrAllocations['assigned'][$intDutyID]['jobs'][$intJobID]['HashIt'] = 1;
                }
              }
            }
          }

          // Job contains
          if (isset($arrJobsContain) && isset($arrStaffAllocation['jobs'])) {
            foreach ($arrStaffAllocation['jobs'] as $intJobID => $arrJob) {
              foreach($arrJobsContain as $intID => $JobDesc) {
                $intPos = strpos(strtoupper($arrJob['jobname']), strtoupper($JobDesc));
                if ($intPos !== false) {
                  $intKeepit1 = 1;
                  //$arrAllocations['assigned'][$intDutyID]['jobs'][$intJobID]['HashIt'] = 1;
                }
              }
            }
          }
          if ($intKeepit == 0 && $intKeepit1 == 0 && $intKeepit2 == 0) {
            unset ($arrAllocations['assigned'][$intDutyID]);
          }
        }
      }
      else {
        // Must Match all....
        // This is a preset filter - so use the $arrFilters
        if ($arrFilters[1][$arrCurrentFilter[1]]['SortCodeFilter'] != '') {
          $arrSortCodes = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['SortCodeFilter']));
        }
        // Now do any Duties......
        if ($arrFilters[1][$arrCurrentFilter[1]]['DutyFilter'] != '') {
          $arrDutiesFilters = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['DutyFilter']));
        }
        if ($arrFilters[1][$arrCurrentFilter[1]]['JobFilter'] != '') {
          $arrJobsFilters = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['JobFilter']));
        }
        if ($arrFilters[1][$arrCurrentFilter[1]]['JobStarts'] != '') {
          $arrJobsStart = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['JobStarts']));
        }
        if ($arrFilters[1][$arrCurrentFilter[1]]['JobContains'] != '') {
          $arrJobsContain = (explode(";", $arrFilters[1][$arrCurrentFilter[1]]['JobContains']));
        }

        foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
          $intKeepDuty = 0;
          $intKeepDuty1 = 0;
          $intKeepDutyJobs = 0;
          $intJobMatch = 0;
          $intJobMatch1 = 0;
          $intJobMatch2 = 0;

          if (isset($arrSortCodes)) {
            foreach($arrSortCodes as $intSC => $SortCodeDesc) {
              $pos = strpos(strtoupper($arrStaffAllocation['sortcode']), strtoupper($SortCodeDesc));
              if ($pos !== false) {
                $intKeepDuty = 1;
              }
            }
          }
          else {
            $intKeepDuty = 1;
          }

          if (isset($arrDutiesFilters)) {
            foreach($arrDutiesFilters as $intID => $DutyDesc) {
              if (strtoupper(substr($arrStaffAllocation['duty'], 0, strlen($DutyDesc))) == strtoupper($DutyDesc)) {
                $intKeepDuty1 = 1;
              }
            }
          }
          else {
            $intKeepDuty1 = 1;
          }
          // Now the Jobs.....
          if (isset($arrStaffAllocation['jobs'])) {
            $intKeepDutyJobs = 0;

            foreach ($arrStaffAllocation['jobs'] as $intJobID => $arrJob) {
            $intJobMatch = 0;
            $intJobMatch1 = 0;
            $intJobMatch2 = 0;
             // Job starts
              if (isset($arrJobsStart)) {
                foreach($arrJobsStart as $intID => $JobDesc) {
                  if (strtoupper(substr($arrJob['jobname'], 0, strlen($JobDesc))) == strtoupper($JobDesc)) {
                    $intJobMatch = 1;
                  }
                }
              }
              else {
                $intJobMatch = 1;
              }
              // Now the Job Contains
              if (isset($arrJobsContain)) {
                foreach($arrJobsContain as $intID => $JobDesc) {
                  $intPos = strpos(strtoupper($arrJob['jobname']), strtoupper($JobDesc));
                  if ($intPos !== false) {
                    $intJobMatch1 = 1;
                  }
                }
              }
              else {
                $intJobMatch1 = 1;
              }
              // Do we match a label?
              if (isset($arrJobsFilters)) {
                foreach($arrJobsFilters as $intID => $JobDesc) {
                  if (strtoupper($arrJob['programme']) == strtoupper($JobDesc)) {
                    $intJobMatch2 = 1;
                  }
                }
              }
              else {
                $intJobMatch2 = 1;
              }
              if ($intJobMatch == 1 && $intJobMatch1 == 1 && $intJobMatch2 == 1) {
                //$arrAllocations['assigned'][$intDutyID]['jobs'][$intJobID]['HashIt'] = 1;
                $intKeepDutyJobs = 1;
              }
            }
          }
          if ($intKeepDuty == 0 || $intKeepDuty1 == 0 || $intKeepDutyJobs == 0) {
            unset ($arrAllocations['assigned'][$intDutyID]);
          }
        }
     }
     break;
       // End preset filter
    case 11:
      //This is a skills filter
      $arrProgs = StaffNumbersWhoCanDoProg($arrCurrentFilter[1]);
      if (isset($arrProgs)) {
        foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
          if (!isset($arrProgs[$arrStaffAllocation['StaffNumber']])) {
            unset ($arrAllocations['assigned'][$intDutyID]);
          }
        }
      }
    break;
    //End  a skills filter
    case 12:
      //This is a Duty Name Filter
      $strDutyMatch = $arrCurrentFilter[1];
      $arrFullMatch = GetMatchOptions ($strDutyMatch);
      $arrNameMatch = $arrFullMatch[0];
      $intMatchType = $arrFullMatch[1];
      foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
        $intKeepit = GetMatch($arrStaffAllocation['duty'], $arrNameMatch, $intMatchType);
        if ($intKeepit == 0) {
          unset ($arrAllocations['assigned'][$intDutyID]);
        }
      }
    break;

    case 13:
      //This is a Sortcode filter
        //This is a  Sort Code match
        $strCodeMatch = $arrCurrentFilter[1];
        $arrFullMatch = GetMatchOptions ($strCodeMatch);
        $arrNameMatch = $arrFullMatch[0];
        $intMatchType = $arrFullMatch[1];

        foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
          $intKeepit = GetMatch($arrStaffAllocation['sortcode'], $arrNameMatch, $intMatchType);
          if ($intKeepit == 0) {
            unset ($arrAllocations['assigned'][$intDutyID]);
          }
        }

        /*
        foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
         //print_r($arrStaffAllocation);
         $intKeepit1 = 0;

         if (stripos($arrStaffAllocation['sortcode'], $arrCurrentFilter[1]) !== false) {
           $intKeepit1 = 1;
         }
         if (stripos($arrStaffAllocation['fullname'], $arrCurrentFilter[1]) !== false) {
           $intKeepit1 = 1;
         }

         if ($intKeepit1 == 0) {
           unset ($arrAllocations['assigned'][$intDutyID]);
         }

      }
      */
    break;
    case 14:
      //This is a Staff Name filter
        //This is a  Sort Code match
        $strNameMatch = $arrCurrentFilter[1];
        $arrFullMatch = GetMatchOptions ($strNameMatch);
        $arrNameMatch = $arrFullMatch[0];
        $intMatchType = $arrFullMatch[1];

        foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
          $intKeepit = GetMatch($arrStaffAllocation['fullname'], $arrNameMatch, $intMatchType);
          if ($intKeepit == 0) {
            unset ($arrAllocations['assigned'][$intDutyID]);
          }
        }
    break;
    case 4:
      //Job Label Filter
      foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
        $intKeepit = 0;
        if (isset($arrStaffAllocation['jobs'])) {
          foreach ($arrStaffAllocation['jobs'] as $intJobID => $arrJob) {
            if (strtoupper($arrJob['programme']) == strtoupper($arrCurrentFilter[1])) {
              $intKeepit = 1;
              //$arrAllocations['assigned'][$intDutyID]['jobs'][$intJobID]['HashIt'] = 1;
            }
          }
        }
        if ($intKeepit == 0) {
          unset ($arrAllocations['assigned'][$intDutyID]);
        }
      }
    break;
   case 10:
      //This is a Favourites filter
      $arrUsers = StaffNumbersInFavGroup($arrCurrentFilter[1]);
      foreach ($arrAllocations['assigned'] as $intDutyID => $arrStaffAllocation) {
        $intKeepit = 0;
        $intKeepit1 = 0;
        if (!isset($arrUsers[$arrStaffAllocation['StaffNumber']])) {
          unset ($arrAllocations['assigned'][$intDutyID]);
        }
      }
   break;
  }
  }
  return $arrAllocations;

}

function ApplyTimeFilter ($arrAllocations, $intTimeEndSecs, $intAdjust) {
  foreach ($arrAllocations['assigned'][1] ?? [] as $intDutyID => $arrStaffAllocation) {
    $endtime = $arrStaffAllocation['endtime'];
  if($arrStaffAllocation['starttime'] > $arrStaffAllocation['endtime']){
    $endtime = 86400+$arrStaffAllocation['endtime'];
  }

    if ($endtime < $intTimeEndSecs) {
      unset ($arrAllocations['assigned'][1][$intDutyID]);
    }else {
    $adjustSeconds = ($intAdjust * 86400);
      $arrAllocations['assigned'][1][$intDutyID]['starttime'] = $arrAllocations['assigned'][1][$intDutyID]['starttime'] + $adjustSeconds;
      $arrAllocations['assigned'][1][$intDutyID]['endtime'] = $arrAllocations['assigned'][1][$intDutyID]['endtime'] + $adjustSeconds;
      foreach ($arrAllocations['assigned'][1][$intDutyID]['jobs'] ?? [] as $intJobID => $arrJobs) {
        $arrAllocations['assigned'][1][$intDutyID]['jobs'][$intJobID]['starttime'] = $arrAllocations['assigned'][1][$intDutyID]['jobs'][$intJobID]['starttime'] + $adjustSeconds;
        $arrAllocations['assigned'][1][$intDutyID]['jobs'][$intJobID]['endtime'] = $arrAllocations['assigned'][1][$intDutyID]['jobs'][$intJobID]['endtime'] + $adjustSeconds;
      }
    }
  }
  return($arrAllocations);
}

function  GetMatch($strToMatch, $arrMatch, $intMatchType) {
  $intKeepit = 0;
  $intHasMatch = 0;

  $strToMatch = trim($strToMatch);

  foreach ($arrMatch as $strMatch) {
    $strMatch = trim($strMatch);
    if (stripos($strToMatch, $strMatch) !== false) {
      $intKeepit++;
    }
  }
  if ($intMatchType == 0) {
    // Any Match
    if ($intKeepit > 0) {
      $intHasMatch = 1;
    }
  }
  else {
    if ($intKeepit == count($arrMatch)) {
      $intHasMatch = 1;
    }
  }
  return ($intHasMatch);
}

function GetMatchOptions ($strMatch) {

        // Decide what to explode on......
        if (stripos($strMatch, ' or ') === false) {
          // There is no OR in the string
          if (stripos($strMatch, ' and ') === false) {
            $arrNameMatch[0] = $strMatch;
            $intMatchType = 0;
          }
          else {
            // There are 1 or mor Ands
            $strMatch = str_ireplace(' and ', '|', $strMatch);
            $arrNameMatch = explode('|', $strMatch);
            $intMatchType = 1;
          }
        }

        else {
          // There are 1 or more ORs
          $strMatch = str_ireplace(' or ', '|', $strMatch);
          $arrNameMatch = explode('|', $strMatch);
          $intMatchType = 0;
        }

  return array($arrNameMatch, $intMatchType);
}