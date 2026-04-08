<style>
    .addpeople {
        background-color:#c7c7c7;
    }
</style>
<div class="tooltip-div" id="customTip" aria-atomic="true"></div>
<?php
$intTeamDetails = getSchedulingTeamDetails($intTeamID);

if (isset($arrAllocations) && count($arrAllocations) > 0 && $arrAllocations!==false) {
    // The holder
    echo '<div style="position: relative">';
    // The top Left Corner
    echo '<div style="position: absolute; width:'.$intNamesWidth.'px; height:'.($intDateHeight - 1).'px; left:0px; top:0px" id="fixed"  class="dotw">';
    echo 'Week: '.spinweek($intWeekNumber);
    echo '</div>';
    // END the holder

    // The days of the week....
    echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($intDateHeight - 1).'px; left:'.$intNamesWidth.'px; top:0px" id="weeklytop">';
    for ($i = 0; $i <=6; $i++) {
        $strCurrDate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
        if ($strCurrDate == date("Y-m-d")) {
            echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - $intStatusHeight - 1).'px" class="dotwlight handcursor date-comment headerEle" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $intTeamID . '" onclick=\'javascript:ShowDailyAllocations('.$intTeamID.',"'.$strCurrDate.'",0,'.$intShowAll.')\';>';
        } else {
            echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - $intStatusHeight - 1).'px" class="dotw handcursor date-comment headerEle" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $intTeamID . '" onclick=\'javascript:ShowDailyAllocations('.$intTeamID.',"'.$strCurrDate.'",0,'.$intShowAll.')\';>';
        }
        echo $invdowMap[$i] .'<br>'.spindate($strCurrDate);
        if (isset($arrHolidays[$strCurrDate])) {
            echo "<br>(".$arrHolidays[$strCurrDate].")";
        }
        echo '<span class="date-commment-info-icon" style="cursor: pointer; position:absolute; padding:20px; top:-6px; right:-10px;"></span>';
        echo '</div>';
        $strStatusClass = GetDayStatus($strCurrDate, $intCanViewComments, $intMaskDays, @$arrHiddenDays[$intTeamID]);
        echo '<div pageid="1" date="'.$strCurrDate.'" team="'.$intTeamID.'" style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($intDateHeight - $intStatusHeight).'px; height:'.($intStatusHeight - 1).'px" class="'.$strStatusClass.' handcursor auto-width-set">';
        echo '</div>';
    }
    echo '</div>';
    // END the days of the week....
    // The names down the left side
    echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:'.$intNamesWidth.'px; height:400px; left:0px; top:'.$intDateHeight.'px" class="whitebackground" id="weeklynames">';
    $counter = 0;

    foreach ($arrAllocations as $sn => $value) {
        $TextColour = "#000000";
        $BackColour = "#cccccc";
        if (($intIsFreelance == 0 || $myschdeullingPersonID == $sn) && ($value["TeamID"] == $intTeamID) && ($value["IsHomeTeam"] == 1)) {
            echo '<div TeamID="'.$intTeamID.'" LogIn="'.$value["Login"].'" class="'.$strCanEditPerson.'names handcursor person-data-cell personname_'.$sn.'" data-cost-code="' . ($value['CostCode'] ?? '') . '" data-sort-code="' . mb_convert_encoding($value["SortCode"], 'UTF-8', 'ISO-8859-1') . '" data-id="' . $value['SchedulingPersonID'] . '" data-order="' . mb_convert_encoding($value["FullName"], 'UTF-8', 'ISO-8859-1') . '" style="position: absolute; width:100%; height:'.($intRowHeight - 1).'px; left:0px; top:'.($counter * $intRowHeight).'px;background-color:'.$BackColour.'"  onclick="highlightRowPerson('.$sn.')">';

            echo '<font color="'.$TextColour.'" onclick=\'event.stopPropagation(); javascript:ShowRota("'.$dteStartDate.'","'.$sn.'",'.$intTeamID.')\';>'.mb_convert_encoding($value["FullName"], 'UTF-8', 'ISO-8859-1');
        } else {
            echo '<div TeamID="'.$intTeamID.'" LogIn="'.$value["Login"].'" class="'.$strCanEditPerson.'names person-data-cell personname_'.$sn.'" data-cost-code="' . ($value['CostCode'] ?? '') . '" data-sort-code="' . mb_convert_encoding($value["SortCode"], 'UTF-8', 'ISO-8859-1') . '" data-id="' . $value['SchedulingPersonID'] . '" data-order="' . mb_convert_encoding($value["FullName"], 'UTF-8', 'ISO-8859-1') . '" style="position: absolute; width:100%; height:'.($intRowHeight - 1).'px; left:0px; top:'.($counter * $intRowHeight).'px;background-color:'.$BackColour.'"  onclick="highlightRowPerson('.$sn.')">';

            echo '<font color="'.$TextColour.'">'.mb_convert_encoding($value["FullName"], 'UTF-8', 'ISO-8859-1');
        }

        echo '<br>';
        echo mb_convert_encoding($value["SortCode"], 'UTF-8', 'ISO-8859-1');
        echo '</font></div>';
        $counter++;
    }
    echo '</div>';
  // ############################################################################### End the names

  // The Duties
  // The duties in the div
        echo '<div style="overflow: scroll; position: absolute; width:900px; height:400px; left:'.$intNamesWidth.'px; top:'.$intDateHeight.'px" id="weeklyduties" class="whitebackground">';
        $counter = 0;
        foreach ($arrAllocations as $sn => $value) {
            $hascomments=0;
            $hasmanualOT=0;
            $intFirstLock = 0;
            echo '<div class="scheduledPerson_duties_' . $value['SchedulingPersonID'] . '">';
            for ($i = 0; $i <=6; $i++) {
                $currdate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
			    $strMaskClass = GetDayStatus($currdate, $intCanViewComments, $intMaskDays,isset($arrHiddenDays[$intTeamID])?$arrHiddenDays[$intTeamID]:'');
                  // The day is hidden?
                $backColor  ='';
                $fontColor ='';
                $fontStyle = '';
                $tDutyName = '';
                $hascomments = 0;
                $jobs = [];
                $dutyLabels = [];
                if (isset($arrHiddenDays[$intTeamID][$currdate]) && $intCanViewComments == 0) {
                    echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="DutyCellNotWorking handcursor  dutyAllocation_'.$sn.'">';
                      echo '</div>';
                  } else {
                        if (isset($value[$intWeekNumber][$i])) {
                        // Do we colour the background?
                            if ($intColourWeek == 1) {
                                if (is_array($value[$intWeekNumber][$i]["Duty"])) {
                                    if (sizeof($value[$intWeekNumber][$i]["Duty"])>1) {
                                        $dutyKeys =array_keys($value[$intWeekNumber][$i]["Duty"]);
                                        sort($dutyKeys);
                                        $backColor  =$value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["BackColour"];
                                        $fontColor = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["FontColour"];
                                        $fontStyle = getFontStyle($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]['display_priority'] ?? 0,
                                            $intTeamDetails['colourWeek'],
                                            $intTeamID,
                                            $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]['TeamID'],
                                            $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["LeaveType"] ?? $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duty"],
                                            $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]
                                        );
                                        $CellClass = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["CellClass"];
                                        $Dutyid = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Dutyid"];
										$tDutyName = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duty"] ?? '';
										$dutyLabels = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["DutyLabels"];
                                        if (($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["StartTime"]=='00:00') && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["EndTime"]=='00:00')) {
											$tDutyTime = ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]==0)?'':number_format((float)($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"] / 3600), 2, '.', '');
										} else {
											$tDutyTime = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["StartTime"].'-'.$value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["EndTime"];
										}
										$tDutyTeam = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["schedulingTeamName"];
										$tDutyLeaveType = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["LeaveType"];
										$AllocationID = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["AllocationID"];
										$jobs = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Jobs"] ?? [];
                                        if (isset($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["DutyComments"]) && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["DutyComments"] !=0)) {
                                            $hascomments = 1;
                                        } else if (((isset($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["PersonComments"])) && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["PersonComments"] != 0)) && ($intCanViewComments==1 )){
                                            $hascomments = 1;
                                        } else {
                                            $hascomments = 0;
                                        }
                                      if ((isset($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["MannualOThours"]) && $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["MannualOThours"]>0) &&  (($redpound==1) || ($myschdeullingPersonID == $sn))) {
                                            $hasmanualOT = 1;
                                        } else {
                                            $hasmanualOT = 0;
                                        }
                                    } else {
                                        foreach ($value[$intWeekNumber][$i]["Duty"] as $dutyData) {
                                            $backColor  = $dutyData["BackColour"];
                                            $fontColor  = $dutyData["FontColour"];
                                            $fontStyle = getFontStyle($dutyData['display_priority'] ?? 0,
                                                $intTeamDetails['colourWeek'],
                                                $intTeamID,
                                                $dutyData['TeamID'],
                                                $dutyData["LeaveType"] ?? $dutyData["Duty"],
                                                $dutyData
                                            );
                                            $dutyLabels = $dutyData['DutyLabels'];
                                            $CellClass = $dutyData["CellClass"];
                                            $Dutyid = $dutyData["Dutyid"];
											$AllocationID = $dutyData["AllocationID"];
											$tDutyName = $dutyData["Duty"] ?? '';
											if (($dutyData["StartTime"]=='00:00') && ($dutyData["EndTime"]=='00:00')) {
												$tDutyTime = ($dutyData["Duration"]==0)?'':number_format((float)($dutyData['Duration'] / 3600), 2, '.', '');
											} else {
												$tDutyTime = $dutyData["StartTime"].'-'.$dutyData["EndTime"];
											}
											$tDutyTeam = $dutyData["schedulingTeamName"];
											$tDutyLeaveType = $dutyData["LeaveType"];

										     if ((isset($dutyData["PersonComments"]) && ($dutyData["PersonComments"] !=0)) && ($intCanViewComments==1 )) {
                                                $hascomments = 1;
                                            } else if (isset($dutyData["DutyComments"]) && ($dutyData["DutyComments"]!=0)) {
                                                $hascomments = 1;
                                            } else {
                                                $hascomments = 0;
                                            }
                                           if ((isset($dutyData["MannualOThours"]) && ($dutyData["MannualOThours"]>0)) &&  (($redpound==1) || ($myschdeullingPersonID == $sn))) {
                                                $hasmanualOT = 1;
                                            } else {
                                                $hasmanualOT = 0;
                                            }
                                            $jobs = $dutyData["Jobs"] ?? [];
                                        }
                                    }
                            } else {
							    $CellClass = isset($value[$intWeekNumber][$i]["Duty"][0]["CellClass"])?$value[$intWeekNumber][$i]["Duty"][0]["CellClass"]:'';
                                $backColor = $value[$intWeekNumber][$i]["BackColour"];
                                $fontColor = $value[$intWeekNumber][$i]["FontColour"];
                            }
							$tDutyNameLower = $tDutyName ? strtolower($tDutyName) : '';
							if (($tDutyName !== null && ($tDutyNameLower == 'leave' || $tDutyNameLower == 'off leave' || $tDutyNameLower == 'u-sick' || $tDutyNameLower == 'sick' || $tDutyNameLower == '-sick' )) || ($tDutyName === "-") || ($tDutyName === "--") || ($tDutyName === "") || ($tDutyName === "U")) {
									 echo '<div style="background-color:'.$backColor.'; width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px; overflow:hidden;" class="'.$CellClass.'  handcursor scheduledPersonDuty dutyAllocation_'.$sn.'" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '" >';
							} else {

							$dutyqtipcontentw1=$tDutyName;
							$dutyqtipcontentw1 .=($tDutyTime=='')?'':"<br>". $tDutyTime;
							$dutyqtipcontentw1 .="<br>".$tDutyTeam;
                            echo '<div style="background-color:'.$backColor.'; width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px; overflow:hidden;" class="'.$CellClass.'  handcursor scheduledPersonDuty dutyAllocation_'.$sn.'" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '" id ='.$Dutyid.' data-qtip-content="' . $dutyqtipcontentw1 . '" onmouseover="customtipweekly(\'showtooltip\',this,'.$Dutyid.')" onmouseleave="customtipweekly(\'hidetooltip\',\'\','.$Dutyid.')">';
							}
					          echo '<font color="'. $fontColor.'" style="' . $fontStyle . '">';
                        } else {
                            if (is_array($value[$intWeekNumber][$i]["Duty"])) {
                                if (sizeof($value[$intWeekNumber][$i]["Duty"])>1) {
                                    $dutyKeys = array_keys($value[$intWeekNumber][$i]["Duty"]);
                                    sort($dutyKeys);
                                    if (isset($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]) && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]>0)) {
                                        $CellClass = 'DutyCellWorking';
                                    } else {
                                        $CellClass = 'DutyCellNotWorking';
                                    }
                                    $backColor = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["BackColour"] ?? '#cccccc';
                                    $fontColor = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["FontColour"] ?? '#000000';
                                    $fontStyle = getFontStyle($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]['display_priority'] ?? 0,
                                        $intTeamDetails['colourWeek'],
                                        $intTeamID,
                                        $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]['TeamID'],
                                        $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["LeaveType"] ?? $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duty"],
                                        $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]
                                    );
                                    $dutyLabels = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]['DutyLabels'];
                                    $Dutyid = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Dutyid"] ?? 0;
									$AllocationID = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["AllocationID"] ?? 0;
                                    $jobs = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Jobs"] ?? 0;
									$tDutyName = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duty"] ?? '';
									if (($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["StartTime"]=='00:00') && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["EndTime"]=='00:00')) {
										$tDutyTime = ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]==0)?'':number_format((float)($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"] / 3600), 2, '.', '');
									} else {
										$tDutyTime = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["StartTime"].'-'.$value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["EndTime"];
									}
									$tDutyTeam = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["schedulingTeamName"];
									$tDutyLeaveType = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["LeaveType"];
									if ((isset($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["DutyComments"])) && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["DutyComments"] !=0)) {
                                        $hascomments = 1;
                                     } else if ((isset($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["PersonComments"]) && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["PersonComments"] != 0)) && ($intCanViewComments==1 )) {
                                        $hascomments = 1;
                                    } else {
                                        $hascomments = 0;
                                    }

                                    if ((isset($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["MannualOThours"]) && $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["MannualOThours"]>0) &&  (($redpound==1) || ($myschdeullingPersonID == $sn))) {
                                        $hasmanualOT = 1;
                                    } else {
                                        $hasmanualOT = 0;
                                    }
                                } else {
                                    foreach ($value[$intWeekNumber][$i]["Duty"] as $dutyData) {
                                        $hascomments = 0;
                                        if ($dutyData["Duration"]>0) {
                                            $CellClass = 'DutyCellWorking';
                                            $backColor = $dutyData["BackColour"];
                                        } else {
                                            $CellClass = 'DutyCellNotWorking';
                                            $backColor = $dutyData["BackColour"];
                                        }
                                        $fontColor = $dutyData["FontColour"];
                                        $fontStyle = getFontStyle($dutyData['display_priority'] ?? 0,
                                            $intTeamDetails['colourWeek'],
                                            $intTeamID,
                                            $dutyData['TeamID'],
                                            $dutyData["LeaveType"] ?? $dutyData["Duty"],
                                            $dutyData
                                        );
                                        $dutyLabels = $dutyData['DutyLabels'];
                                        $Dutyid = $dutyData["Dutyid"];
										$AllocationID = $dutyData["AllocationID"];
										$tDutyName = $dutyData["Duty"] ?? '';
                                        $jobs = $dutyData["Jobs"];
										if (($dutyData["StartTime"]=='00:00') && ($dutyData["EndTime"]=='00:00'))
										{
												$tDutyTime = ($dutyData["Duration"]==0)?'':number_format((float)($dutyData['Duration'] / 3600), 2, '.', '');
											} else {
												$tDutyTime = $dutyData["StartTime"].'-'.$dutyData["EndTime"];
											}
										$tDutyTeam = $dutyData["schedulingTeamName"];
										$tDutyLeaveType = $dutyData["LeaveType"];
									    if (isset($dutyData["DutyComments"]) && ($dutyData["DutyComments"]!=0)) {
                                            $hascomments = 1;
                                         } else if ((isset($dutyData["PersonComments"]) && ($dutyData["PersonComments"]!=0)) && ($intCanViewComments==1 )) {
                                            $hascomments = 1;
                                        } else {
                                            $hascomments = 0;
                                        }

                                        if ((isset($dutyData["MannualOThours"]) && ($dutyData["MannualOThours"]>0)) &&  (($redpound==1) || ($myschdeullingPersonID == $sn))) {
                                            $hasmanualOT = 1;
                                        } else {
                                            $hasmanualOT = 0;
                                        }
                                    }

                                }
                            } else {
                                $backColor = $value[$intWeekNumber][$i]["BackColour"];
                                $fontColor = $value[$intWeekNumber][$i]["FontColour"];
                                $CellClass = array_key_exists('CellClass', $value[$intWeekNumber][$i]) ? $value[$intWeekNumber][$i]["CellClass"] : '';
                            }
							$tDutyNameLower = $tDutyName ? strtolower($tDutyName) : '';
                            if(($tDutyNameLower == 'leave' || $tDutyNameLower == 'off leave'|| $tDutyNameLower == 'u-sick'|| $tDutyNameLower == 'sick'|| $tDutyNameLower == '-sick') || ($tDutyName=="-") || ($tDutyName=="--") || ($tDutyName=="") || ($tDutyName=="U")) {
									 echo '<div style="background-color:'.$backColor.'; width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px; overflow:hidden;" class="'.$CellClass.'  handcursor scheduledPersonDuty dutyAllocation_'.$sn.'" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '" >';
							} else {
							$dutyqtipcontentw=$tDutyName;
							$dutyqtipcontentw .=($tDutyTime=='')?'':"<br>". $tDutyTime;
							$dutyqtipcontentw .="<br>".$tDutyTeam;
                            echo '<div style="background-color:'.$backColor.'; width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px; overflow:hidden;" class="'.$CellClass.'  handcursor scheduledPersonDuty dutyAllocation_'.$sn.'" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '" id ='.$Dutyid.' data-qtip-content="' . $dutyqtipcontentw . '" onmouseover="customtipweekly(\'showtooltip\',this,'.$Dutyid.')" onmouseleave="customtipweekly(\'hidetooltip\',\'\','.$Dutyid.')">';
							}
						    echo '<font color="'. $fontColor.'" style="' . $fontStyle . '">';
                        }
                //Color Week Close here
                if (is_array($value[$intWeekNumber][$i]["Duty"])) {
                    if (sizeof($value[$intWeekNumber][$i]["Duty"])>1) {
						//$dutyqtipcontent='';
                        foreach ($value[$intWeekNumber][$i]["Duty"] as $key => $DutyData) {
						    if ($key==0) {
                                if (isset($DutyData['display_priority']) && ($DutyData['display_priority'] == 2) && ($strMaskClass == 'daynotfixed' || $strMaskClass == 'daynotfixed allocations-hideday-menu'))  {
									$LeaveTypeLower = $DutyData['LeaveType'] ? strtolower($DutyData['LeaveType']) : '';
                                    if(($LeaveTypeLower == 'leave' || $LeaveTypeLower == 'off leave')) {
                                        echo '<font ><b>'.$DutyData['LeaveType'].'</b></font><br> ' . (!empty($DutyData['Duty']) ? '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . '">Was: '. $DutyData['Duty'] . '</font>' : '');
                                    } else {
                                        if($DutyData['IsPublished'] == 0){
                                            echo '<i>'.$DutyData['Duty'].'</i>';
                                        } else {
                                            echo $DutyData['Duty'];
                                        }
                                    }
                                }else if (strtolower($DutyData['LeaveType'] ?? '') == 'leave' || strtolower($DutyData['LeaveType'] ?? '') == 'off leave') {
                                    echo '<font ><b>'.$DutyData['LeaveType'].'</b></font><br> ' . (!empty($DutyData['Duty']) ? '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . '">Was: '. $DutyData['Duty'] . '</font>' : '');
                                } else {
                                    if($DutyData['IsPublished'] == 0){
                                        echo '<i>'.$DutyData['Duty'].'</i>';
                                    } else {
                                        echo $DutyData['Duty'];
                                    }
                                }

                                if (isset($DutyData['IsTemplate']) && ($DutyData['IsTemplate'] != 0) && ($DutyData['Duration']>0) && ($DutyData['isEditable']==1) && is_null($DutyData['LeaveType'])) {
                                    $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                                    echo "<br>".$intendHour ." Hours";
                                } else {
                                        if(isset($DutyData["Duty"]) && (strtolower($DutyData["Duty"])!='sick') && (strtolower($DutyData["Duty"])!='u-sick') && (strtolower($DutyData["Duty"])!='-sick')){
                                            if((($DutyData["StartTime"] < $DutyData["EndTime"])|| ($DutyData["StartTime"] > $DutyData["EndTime"])) && !empty($DutyData["StartTime"]) && is_null($DutyData['LeaveType'])) {
                                                echo "<br>".$DutyData["StartTime"].'-'.$DutyData["EndTime"];
                                            } else {
                                                if ($DutyData['Duration']>0 && is_null($DutyData['LeaveType'])) {
                                                    $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                                                    echo "<br>".$intendHour ." Hours";
                                                }
                                            }
                                        }
                                        // Only Do SignIn if there is a start
                                        if (isset($DutyData["StartTime"]) && ($DutyData["StartTime"] !='00:00') && $currdate >= date("Y-m-d") && (strtotime($currdate) <= strtotime("+".$intSignInDays." days", strtotime(date("Y-m-d")))) && strtoupper($DutyData["Duty"])!='U' && ($DutyData["Duty"] != "") && (strtoupper($DutyData["Duty"])!='ABSENT' && strtoupper($DutyData["Duty"])!='LEAVE' && strtoupper($DutyData["Duty"]!='SICK')) && ($DutyData["isEditable"]==1) && ($DutyData["display_priority"] == 1)) {
                                        $js = '';

                                        if (!isset($DutyData["signin"])) {
                                            $img = 'red_cross.png';
                                            $action = 1;
                                        } else {
                                            switch ($DutyData["signin"]) {
                                                case 1:
                                                    if ($DutyData["inbuilding"] == 1) {
                                                        // Signed in OK
                                                        $img = 'blue_tick.png';
                                                        $action = 0;
                                                    }
                                                    else {
                                                        // Signed in OK
                                                        $img = 'green_tick.png';
                                                        $action = 0;
                                                    }
                                                    break;
                                                // Signed in NOT OK
                                                case 2:
                                                    $img = 'messagebox_warning.png';
                                                    $action = 1;
                                                    break;
                                                default:
                                                    // Not signed in
                                                    //$flashit = '';
                                                    $img = 'red_cross.png';
                                                    $action = 1;
                                                    break;
                                            } //Switch End
                                            if ($currdate == date("Y-m-d") && $intAllowInBuilding == 1) {
                                                if ($intSignInAll == 1) {
                                                    $js = 'onclick=\'javascript:WeeklySignInToDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , "'.$DutyData["StartTimeSec"].'", "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'", '.$DutyData["signin"].')\';';
                                                } else {
                                                    if($myschdeullingPersonID == $value["SchedulingPersonID"])  {
                                                        $js = 'onclick=\'javascript:WeeklySignInToDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , "'.$DutyData["StartTimeSec"].'", "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'", '.$DutyData["signin"].')\';';
                                                    } else {
                                                        $js = '';
                                                    }
                                                }
                                            } else {
                                                if ($intSignInAll == 1) {
                                                    $js = ' onclick=\'javascript:WeeklySignInDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'", '.$DutyData["signin"].')\';';
                                                }
                                                else {
                                                    if($myschdeullingPersonID == $DutyData["SchedulingPersonID"])  {
                                                        $js = ' onclick=\'javascript:WeeklySignInDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'", '.$DutyData["signin"].')\';';
                                                    }
                                                    else {
                                                        $js = '';
                                                    }
                                                }
                                            } //For Other Dates
                                        } //Else Close when Sign is set

                                        echo '<div allocationsSPID = "'.$DutyData["AllocationsSPID"].'" DutyName = "'.$DutyData["Duty"].'" StartTime = "'.$DutyData["StartTimeSec"].'" EndTime = "'.$DutyData["EndTimeSec"].'" date="'.$currdate.'" ScheduledPerson="'.$sn.'" teamid="'.$intTeamID.'"  class="handcursor signedtip WeekDutyCellTopRight " '.$js.'>';
                                        echo '<img src="images/'.$img.'" border="0" height="12px" width="12px">';
                                        echo '</div>';
                                    } //Conditinal check for Is Editable Entry only
                                } //check cose for master duty only
                            } //Check to Sghow only One Master Duty from Rota
				        } //Foreach close

                    } else
                    {
						//$dutyqtipcontent='';
                        foreach ($value[$intWeekNumber][$i]["Duty"] as $DutyData) {
					        if(($strMaskClass == 'daynotfixed' || $strMaskClass == 'daynotfixed allocations-hideday-menu'))  {
                                if (isset($DutyData['LeaveType']) && (strtolower($DutyData['LeaveType']) == 'leave' || strtolower($DutyData['LeaveType']) == 'off leave')) {
                                    echo '<font ><b>'.$DutyData['LeaveType'].'</b></font><br>' .  (!empty($DutyData['Duty']) ? '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . '">Was: '. $DutyData['Duty'] . '</font>' : '');
                                }else {
                                    if($DutyData['IsPublished'] == 0){
                                        echo '<i>'.$DutyData['Duty'].'</i>';
                                    } else {
                                        echo $DutyData['Duty'];
                                    }
                                }
                            }
                            else if($DutyData['LeaveType'] !== null && (strtolower($DutyData['LeaveType']) == 'leave' || strtolower($DutyData['LeaveType']) == 'off leave')) {
                                echo '<font ><b>'.$DutyData['LeaveType'].'</b></font><br>' . (!empty($DutyData['Duty']) ? '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . '">Was: '. $DutyData['Duty'] . '</font>' : '');
                            } else {
                                if($DutyData['IsPublished'] == 0){
                                    echo '<i>'.$DutyData['Duty'].'</i>';
                                } else {
                                    echo $DutyData['Duty'];
                                }
                            }

                            if(($DutyData['IsTemplate'] != 0) && ($DutyData['Duration']>0) && ($DutyData['isEditable']==1)) {
                                if(is_null($DutyData['LeaveType'])) {
                                $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                                    echo "<br>".$intendHour ." Hours";
                                }
                            } else {
                                if ($DutyData["Duty"] !== null && strtolower($DutyData["Duty"]) != 'sick' && strtolower($DutyData["Duty"]) != 'u-sick' && strtolower($DutyData["Duty"]) != '-sick') {
                                    if(($DutyData["StartTime"] < $DutyData["EndTime"]) || ($DutyData["StartTime"] > $DutyData["EndTime"]) && is_null($DutyData['LeaveType'])) {
                                        if(($DutyData['pdlStartTime'] != 0) || ($DutyData['pdlEndTime'] != 0)){
                                            $dispPDLEndTime = $DutyData['pdlEndTime'];
                                            if($DutyData['pdlStartTime'] > $DutyData['pdlEndTime']){
                                                $dispPDLEndTime = (86400 + $DutyData['pdlEndTime']);
                                            }
                                            $pdlTitle = 'PDL: '.$service->convertSecondsIntoTime($DutyData['pdlStartTime'],':','No').'-'.$service->convertSecondsIntoTime($DutyData['pdlEndTime'],':','No').'&nbsp;|&nbsp;'.$service->convertSecondsIntoTime($dispPDLEndTime-$DutyData['pdlStartTime'],'.','Yes');

                                            echo '<div style="width: 100px; white-space:nowrap; display: -webkit-box;"><div>'.$DutyData["StartTime"].'-'.$DutyData["EndTime"].'</div><div style="color:#109146; padding-left:4px;" title="'.$pdlTitle.'">[L:'.$service->convertSecondsIntoTime($DutyData['pdlStartTime'],':','No').'-'.$service->convertSecondsIntoTime($DutyData['pdlEndTime'],':','No').']</div></div>';
                                        } else {
                                            echo "<br>" . '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . ';">' . $DutyData["StartTime"].'-'.$DutyData["EndTime"] . '</font>';
                                        }
                                    } else {
                                        if ($DutyData['Duration'] > 0 && is_null($DutyData['LeaveType'])) {
                                            $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                                            echo "<br>".$intendHour ." Hours";
                                        }
                                    }
                                }
                                //# Only Do SignIn if there is a start
                                if(($intSignInDays > 0) && ($DutyData["StartTime"] !='00:00') && $currdate >= date("Y-m-d") && (strtotime($currdate) <= strtotime("+".$intSignInDays." days", strtotime(date("Y-m-d")))) && strtoupper($DutyData["Duty"])!='U' && !empty($DutyData["Duty"]) &&(strtoupper($DutyData["Duty"])!='ABSENT' && strtoupper($DutyData["Duty"])!='LEAVE' && strtoupper($DutyData["Duty"]!='SICK')) && ($DutyData["isEditable"]==1) && ($DutyData["display_priority"] == 1)) {
                                    $js = '';

                                    if (!isset($DutyData["signin"])) {
                                        $img = 'red_cross.png';
                                        $action = 1;
                                    }
                                    else {
                                        switch ($DutyData["signin"]) {
                                            case 1:
                                                if ($DutyData["inbuilding"] == 1) {
                                                    // Signed in OK
                                                    $img = 'blue_tick.png';
                                                    $action = 0;
                                                } else {
                                                    // Signed in OK
                                                    $img = 'green_tick.png';
                                                    $action = 0;
                                                }
                                            break;
                                            // Signed in NOT OK
                                            case 2:
                                                $img = 'messagebox_warning.png';
                                                $action = 1;
                                            break;
                                            default:
                                                // Not signed in
                                                $img = 'red_cross.png';
                                                $action = 1;
                                            break;
                                        }//Switch Close
                                        if ($currdate == date("Y-m-d") && $intAllowInBuilding == 1) {
                                            if ($intSignInAll == 1) {
                                                $js = 'onclick=\'javascript:WeeklySignInToDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , "'.$DutyData["StartTimeSec"].'", "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'", '.$DutyData["signin"].')\';';
                                            } else {
                                                if ($myschdeullingPersonID == $value["SchedulingPersonID"]) {
                                                    $js = 'onclick=\'javascript:WeeklySignInToDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , "'.$DutyData["StartTimeSec"].'", "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'", '.$DutyData["signin"].')\';';
                                                    } else {
                                                        $js = '';
                                                    }
                                            }
                                        } else {
                                            if ($intSignInAll == 1) {
                                                $js = ' onclick=\'javascript:WeeklySignInDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'", '.$DutyData["signin"].')\';';
                                            } else {
                                                if($myschdeullingPersonID == $value["SchedulingPersonID"]) {
                                                    $js = ' onclick=\'javascript:WeeklySignInDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'", '.$DutyData["signin"].')\';';
                                                } else {
                                                    $js = '';
                                                }
                                            }
                                        }
                                    } //When isset is set
                                    echo '<div allocationsSPID = "'.$DutyData["AllocationsSPID"].'" DutyName = "'.$DutyData["Duty"].'" StartTime = "'.$DutyData["StartTimeSec"].'" EndTime = "'.$DutyData["EndTimeSec"].'" date="'.$currdate.'" ScheduledPerson="'.$sn.'" teamid="'.$intTeamID.'"  class="handcursor signedtip WeekDutyCellTopRight " '.$js.'>';
									if ($DutyData["IsPublished"]=="1"){
										echo '<img src="images/'.$img.'" border="0" height="12px" width="12px" style="z-index:1 !important">';
									}
                                    echo '</div>';
                                } //Check close of Sign days condition
                            } //OnlyApply on Master Duty

                        } //Foreach Close

                    } //Else Close for Single Entry Check
                } else {
                    echo str_replace('--', '-', $value[$intWeekNumber][$i]["Duty"]);
                }
                echo '</font>';

                if(is_array($jobs) && count($jobs) > 0 && $arrTeamDefaults[$intTeamID]['HasJobsInWeeklyView'] == 1) {
                    $jobData = [];
                    foreach($jobs as $job) {
                        $backColor = $job['JobBackColour'] ?? null;
                        $fontColor = $job['JobFontColour'] ?? null;

                        if(($backColor == '#ffffff' && $fontColor == '#000000') ||
                        ($backColor == '#fff' && $fontColor == '#000')
                        ) {
                            continue;
                        }

                        $jobData[] = [
                            'jobName' => $job['JobName'],
                            'jobStartTime' => gmdate("H:i", (intval($job['JobStartTime']))),
                            'jobEndTime' => gmdate("H:i", (intval($job['JobEndTime'])))
                        ];
                    }

                    if(!empty($jobData)) {
                        echo '<div class="view-job-details-icon DutyCellBottomRight handcursor dutyAllocation_'.$sn.'" data-job-detail=\'' . json_encode($jobData) . '\' style="text-align:right;margin-right: 2px;"><i class="fa fa-circle" style="color: blueviolet;padding-top: 5px;" aria-hidden="true"></i></div>';
                    }
                }
                          // Show the comments?
                if (($hascomments == 1)) {
                    $scTeamId = $value[$intWeekNumber][$i]["Duty"][0]["TeamID"] ?? null;
                    echo '<div class="DutyCellBottomRight handcursor tipremotecomments " teamid= "'.$scTeamId.'"  dutyId= "'.$Dutyid.'" DutyDate="'.$currdate.'" SchedulingPersonID="'.$sn.'" style="width:10%!important;z-index:1 !important;margin-bottom: 13px;margin-right: 1px;"  >';
                    echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
                    echo '</div>';
                }
                if ($hasmanualOT == 1) {
                    $scTeamId = $value[$intWeekNumber][$i]["Duty"][0]["TeamID"] ?? null;
                    echo '<div class="DutyCellBottomRight   handcursor" teamid= "'.$scTeamId.'"  dutyId= "'.$Dutyid.'" DutyDate="'.$currdate.'" SchedulingPersonID="'.$sn.'" style="z-index:1 !important;width:10%; margin-bottom: 28px;">';
                    echo '<img class="tipremote" schPersonId="'.$sn.'" dutydate="'.$currdate.'" teamId ="'.$scTeamId.'" border="0" src="images/money.png" width="12px" height="12px">';
                    echo '</div>';
                }
            // Show the Locks....?
            if (isset($arrRequests[$value["SchedulingPersonID"]][$i])) {
                if ($arrRequests[$value["SchedulingPersonID"]][$i]['IsLock'] == 1) {
                    $intFirstLock = 1;
                    $strTitle = 'Day Is Locked<br>'.$arrRequests[$value["SchedulingPersonID"]][$i]['TipText'];
                    $strImage = 'locked';
                } else {
                    $strTitle = $arrRequests[$value["SchedulingPersonID"]][$i]['Description'];
                        if ($arrRequests[$value["SchedulingPersonID"]][$i]['Approved'] == 1) {
                            if ($intFirstLock == 0 && $arrRequests[$value["SchedulingPersonID"]][$i]['AffectLocks'] == 1) {
                                 $strTitle.= '<br>This is the Lock for this Week<br>Approved';
                                 $strImage = 'locked';
                                } else {
                                $strTitle.= '<br>Approved';
                                $strImage = 'requested';
                                }
                            $intFirstLock = 1;
                            } else {
                                      if ($arrRequests[$value["SchedulingPersonID"]][$i]['Approved'] == 1) {
                                          $strTitle.= '<br>OK - Not yet Approved';
                                          $strImage = 'requested';
                                      } else {
                                          $strTitle.= '<br>Waiting List - Not yet Approved';
                                          $strImage = 'requestedInQ';
                                      }
                                  }

                    }
                             echo '<div class="DutyCellBottomCentre" style="width:90%">';
                            echo '<img title="'.$strTitle.'" width="15" height="15" border="0" src="images/locks/'.$strImage.'.png"></img>';
                            echo '</div>';
                          }
                          // Edit EDP here
                        if (isset($arrEDP[$value["SchedulingPersonID"]][$currdate])) {
                            if ($intSignInAll == 1) {
                              echo '<div class="handcursor tipremoteedp" style="width:12px; position:absolute;
                              left: 106px;background: #ccc; border-radius: 50%; height:12px ;bottom:3px;z-index:1 !important" DutyDate="'.$currdate.'" SchedulingPersonID="'.$value["SchedulingPersonID"].'" teamid="'.$intTeamID.'" onclick="javascript:EDPEdit(\''.$currdate.'\',\''.$currdate.'\',\''.$value["SchedulingPersonID"].'\', '.$intTeamID.')">';
                              if (($intovertime==1) || ($myschdeullingPersonID == $sn)) {
								echo '<img width="12" height="12" border="0" src="images/overtime.png"></img>';
							  }
                              echo '</div>';
                            } else {
								if (($intovertime==1) && ($myschdeullingPersonID == $sn)) {
									echo '<div class="handcursor tipremoteedp" style="background: #ccc;border-radius: 50%;  width:12px; position:absolute; height:12px;bottom:3px; z-index:1 !important" DutyDate="'.$currdate.'" SchedulingPersonID="'.$value["SchedulingPersonID"].'" teamid="'.$intTeamID.'" >';
									echo '<img width="12" height="12" border="0" src="images/overtime.png"></img>';

                              echo '</div>';
							   }
                            }
                        }
                          echo '</div>';
                      }
                      else {
                          echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="DutyCellNotWorking handcursor dutyAllocation_'.$sn.'">';

                          echo '</div>';
                      }
                  }
              }
              echo '</div>';
              $counter++;
             }
                echo '</div>';
                echo '</div>';

            } else {
                echo '<br>';
                echo '<div class="tableheadersmall bigtextboldcentre" style="width:'.$intTableWidth.'px">';
                echo '<br><br>';
                echo 'Unable to display The Allocations for this week<br>';
                if ($intShowCanDo == 1)  {
                    echo 'You are viewing Shifts To Check and it may be that all shifts are covered without any conflicts.<br>';
                }
                echo '<br></br><br>';
                echo '</div>';
            }

    echo '<br><br><br><br>';
    echo '<div id="dialog-edp-offer" title="Information!" style="display:none;">';
    echo '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can delete this offer<br>or add comments to it.</p>';
    echo '</div>';
?>