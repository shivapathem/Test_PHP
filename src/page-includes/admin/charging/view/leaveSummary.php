<link href="../styles/charging/charging.css" rel="stylesheet">
<link href="../styles/charging/chargingReports.css" rel="stylesheet">
<!--Same css for Leave Details-->
<title>Leave | Details</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<div id="leaveReport-summary">
        <h1 class="headertextallpages  main-heading">Leave Details</h1>
        <div class="chargeRow" id="weeklyCharging">
              <div class="filterContainerFlex chargeCol12">
                <span class="heading">Filter</span>
                <div class="chargeRow">
                    <div class="chargeCol chargeCol3w">

                        <div class="fields">
                            <label for="yearType" class="labelTxtAlign">Year Type</label>
                            <select id="yearType" class="weeklyChargingFrom" onChange="if(this.value == 'range'){ $('#leaveFinsh').removeAttr('disabled');$('#leaveYearText').text('Leave Start'); } else{ $('#leaveFinsh').attr('disabled','disabled');$('#leaveYearText').text('Leave Year'); }">
                                <option value="single" <?php if (isset($requestData['filterrequest']['selectedLeaveTypes']) && $requestData['filterrequest']['selectedLeaveTypes']=='single'){ echo 'Selected';} ?>>Single Year</option>
                                <option value="range" <?php if (isset($requestData['filterrequest']['selectedLeaveTypes']) && $requestData['filterrequest']['selectedLeaveTypes']=='range'){ echo 'Selected';} ?>>Range of Years</option>
                            </select>
						</div>
						<div class="fields">
                            <label for="reportType" class="labelTxtAlign">Report Type</label>
                            <select id="reportType" class="weeklyChargingFrom" onChange="if(this.value == 'GRP'){ $('#groupBy1').removeAttr('disabled'); $('#groupBy2').removeAttr('disabled'); }else{ $('#groupBy1').attr('disabled','disabled'); $('#groupBy2').attr('disabled','disabled'); }">
							<option value="ALL" <?php if (isset($requestData['filterrequest']['selectedreportType']) && $requestData['filterrequest']['selectedreportType']=='ALL'){ echo 'Selected';} ?>>Show All Records</option>
                                <option value="GRP" <?php if (isset($requestData['filterrequest']['selectedreportType']) && $requestData['filterrequest']['selectedreportType']=='GRP'){ echo 'Selected';} ?>>Group Records</option>
                            </select>
						</div>
						<div class="fields">
							<?php
                            $currentLeaveYear = $requestData['currentLeaveYear'] ?? 0;
                            $YearTo = $requestData['filterrequest']['selectedleaveYEarTo'] ?? $currentLeaveYear;
                            $YearFrom = $requestData['filterrequest']['selectedLeaveYEarFrom'] ?? $currentLeaveYear;
                            ?>
                            <label for="leaveYear" id="leaveYearText" class="labelTxtAlign">Leave Year</label>
                            <select id="leaveYear" class="weeklyChargingFrom">
								<?php for ($i=$currentLeaveYear-7 ; $i < $currentLeaveYear; $i++) {
								?>
  									<option value="<?php echo $i; ?>" <?php if ($YearFrom== $i) { echo "selected"; }?>><?php echo $i; ?></option>
									<?php
								}
								?>
                                <?php for ($i=$currentLeaveYear; $i<=($currentLeaveYear+2); $i++) {
									?>
  									<option value="<?php echo $i; ?>" <?php if ($YearFrom== $i) { echo "selected"; }?>><?php echo $i; ?></option>
									<?php
								}
								?>
                            </select>
						</div>
						<div class="fields">
                            <label for="leaveFinsh" class="labelTxtAlign">Leave Finish</label>
                            <select id="leaveFinsh" class="weeklyChargingFrom" disabled>
							<?php for ($i=$currentLeaveYear-7; $i <$currentLeaveYear;$i++) {
									?>
  									<option value="<?php echo $i; ?>" <?php if ($YearTo== $i) { echo "selected"; }?>><?php echo $i; ?></option>
									<?php
								}
								?>
                                <?php for ($i=$currentLeaveYear;$i<=($currentLeaveYear+2);$i++) {
									?>
  									<option value="<?php echo $i; ?>" <?php if ($YearTo== $i) { echo "selected"; }?>><?php echo $i; ?></option>
									<?php
								}
								?>
                            </select>
						</div>
                        <div class="fields">
                            <label for="groupBy1" class="labelTxtAlign">Group By1</label>
                            <select id="groupBy1" disabled class="weeklyChargingFrom">
                                <option value="Nothing">--Nothing--</option>
                                <option value="schedulingTeamName" <?php if (isset($requestData['filterrequest']['selectedgroupBy1']) && $requestData['filterrequest']['selectedgroupBy1']=='schedulingTeamName') { echo 'Selected';} ?>>Scheduling Team</option>
                                <option value="DisplayName" <?php if (isset($requestData['filterrequest']['selectedgroupBy1']) && $requestData['filterrequest']['selectedgroupBy1']=='DisplayName'){ echo 'Selected';} ?>>Name</option>
                                <option value="iyear" <?php if (isset($requestData['filterrequest']['selectedgroupBy1']) && $requestData['filterrequest']['selectedgroupBy1']=='iyear'){ echo 'Selected';} ?>>Year</option>
                                <option value="Charge_Codes" <?php if (isset($requestData['filterrequest']['selectedgroupBy1']) && $requestData['filterrequest']['selectedgroupBy1']=='Charge_Codes'){ echo 'Selected';} ?>>Charge Code</option>
                            </select>
						</div>
						<div class="fields">
                            <label for="groupBy2" class="labelTxtAlign">Group By2</label>
                            <select id="groupBy2" disabled class="weeklyChargingFrom">
								<option value="Nothing">--Nothing--</option>
                                <option value="schedulingTeamName" <?php if (isset($requestData['filterrequest']['selectedgroupBy2']) && $requestData['filterrequest']['selectedgroupBy2']=='schedulingTeamName') {
                                    echo 'Selected';} ?>>Scheduling Team</option>
                                <option value="DisplayName" <?php if (isset($requestData['filterrequest']['selectedgroupBy2']) && $requestData['filterrequest']['selectedgroupBy2']=='DisplayName') { echo 'Selected';} ?>>Name</option>
                                <option value="iyear" <?php if (isset($requestData['filterrequest']['selectedgroupBy2']) && $requestData['filterrequest']['selectedgroupBy2']=='iyear'){ echo 'Selected';} ?>>Year</option>
                                <option value="Charge_Codes" <?php if (isset($requestData['filterrequest']['selectedgroupBy2']) && $requestData['filterrequest']['selectedgroupBy2']=='Charge_Codes') { echo 'Selected';} ?>>Charge Code</option>
                            </select>
                        </div>
					</div>
					<div class="chargeCol chargeCol3w">
                    <div class="fields displayFlex">
                            <?php
                            $tablColsList = $topLIST = [];
                            if (!empty($requestData['filterrequest']['selectedavailableColumn'])) {
                                foreach ($requestData['filterrequest']['selectedavailableColumn'] as $key => $value) {
                                    if (strpos($key, "_") !== false) {
                                        $ListdataArray = explode("_", $key);
                                        $topLIST[$ListdataArray[1]] = $value;
                                    }
                                }
                            }

                            $TopROWDBCols = array_keys($topLIST);
                            $TopROWDBColsValue = array_values($topLIST);

                            $fiteredTableColumns=[];
                            if (isset($requestData['filterrequest']['selectedavailableColumn']) && (!empty($requestData['filterrequest']['selectedavailableColumn'])) && is_array($requestData['filterrequest']['selectedavailableColumn'])) {
                                $fiteredTableColumns = array_values($requestData['filterrequest']['selectedavailableColumn']);
                            } else {
                                $fiteredTableColumns[0] = $requestData['filterrequest']['selectedavailableColumn'];
                            }
                            ?>
                            <label for="DisplayColumns" class="labelTxtAlign cf-w">Columns</label>
                            <select id="DisplayColumns" data-placeholder="Select Leave Columns" multiple SIZE="10" style="height:110px;">
                            <option value="ALL">Select ALL</option>
							<?php if (isset($requestData['availableColumn']) && !empty($requestData['availableColumn'])) { foreach ($requestData['availableColumn'] as $key => $value) {
                                ?>
                                <option value="<?php echo $key; ?>^<?php echo $value; ?>" <?php if (in_array($value, $fiteredTableColumns)){ echo 'selected';}?>><?php echo $value; ?></option>
								<?php }
							} ?>
                            </select>
                        </div>

                        <div class="fields displayFlex mtop">
                            <?php
                            $selectedfilterColName=null;
                            if (isset($requestData['filterrequest']['selectedFilterColumn']) && !empty($requestData['filterrequest']['selectedFilterColumn'])) {
                                $selectedfilterColName = $requestData['filterrequest']['selectedFilterColumn'];
                            }
                            ?>
                            <label class="FilterW cf-w" for="FilterColName">Filter</label>
                            <select class="wid155" id="FilterColName">
                                <option value="NA">--No Filter--</option>
								<?php if(isset($requestData['filtercolumnlist']) && !empty($requestData['filtercolumnlist'])) { foreach ($requestData['filtercolumnlist'] as $key=>$value) { ?>
                                <option value="<?php echo str_replace(" ","",$key); ?>" <?php if($selectedfilterColName == str_replace(" ","",$key)) { echo 'selected';}?>><?php echo $value; ?></option>
								<?php }
							} ?>
                            </select>
                            <?php
                            $selectedfilterSign = null;
                            if (isset($requestData['filterrequest']['selectedfilterSign']) && !empty($requestData['filterrequest']['selectedfilterSign'])) {
                                $selectedfilterSign = $requestData['filterrequest']['selectedfilterSign'];
                            }
                            ?>
                            <select class="wid50" id="FilterSymbol">
                                <option value=">" <?php if($selectedfilterSign == '>') {  echo 'selected';}?> >></option>
                                <option value="<" <?php if($selectedfilterSign == '<') {  echo 'selected';}?>><</option>
                                <option value="<=" <?php if($selectedfilterSign == '<=') { echo 'selected';}?>><=</option>
                                <option value=">=" <?php if($selectedfilterSign == '>=') { echo 'selected';}?>>>=</option>
                                <option value="="  <?php if($selectedfilterSign == '=') {  echo 'selected';}?>>=</option>
                                <option value="!=" <?php if($selectedfilterSign == '==') { echo 'selected';}?>>!=</option>
                            </select>
                            <?php
                            $selectedfiltervalue = null;
                            if (isset($requestData['filterrequest']['selectedfiltervalue']) && !empty($requestData['filterrequest']['selectedfiltervalue'])) {
                                $selectedfiltervalue = $requestData['filterrequest']['selectedfiltervalue'];
                            }
                            ?>
                           <input type="number" min=0 max="500" class="wid50" id="FilterSymbolValue" value="<?php echo $selectedfiltervalue;?>" style="top:10px; padding:unset;">

                        </div>
                    </div>

                    <div class="chargeCol chargeCol3schedulStaffW m11">
                        <div class="fields displayFlex">
                                <label for="schedulingTeam" class="labelTxtAlign">Scheduling Teams</label>
                                <select id="schedulingTeam" data-placeholder="Select Scheduling Teams" multiple SIZE="10" style="height:110px;width: 170px;">
                                    <option value="ALL">Select ALL</option>
                                <?php
                                $selectedTeams=[];
                            if (isset($requestData['filterrequest']['selectedTeamID']) && !empty($requestData['filterrequest']['selectedTeamID']) && is_array($requestData['filterrequest']['selectedTeamID'])) {
                                    $selectedTeams = array_values($requestData['filterrequest']['selectedTeamID']);
                            } else {
                                $selectedTeams[] = $requestData['filterrequest']['selectedTeamID'];
                            }
                                $selectedTeamsNA = array();
                                if (isset($requestData['teamList']) && !empty($requestData['teamList'])) {
                                        foreach ($requestData['teamList'] as $key => $value) {
                                            $selected ='';
                                            if (in_array($key, $selectedTeams)) {
                                                $selected ='selected';
                                                $selectedTeamsNA[]=$value;
                                            }
                                            ?>
                                            <option value="<?php echo $key; ?>" <?php echo $selected ;?>><?php echo $value; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <input type="hidden" value="<?php if(is_array($selectedTeamsNA)) { echo implode(", ",$selectedTeamsNA);} else{ echo $selectedTeamsNA;}?>"  id="selectedTeamnames"/>
                            </div>
                    </div>

                    <div class="chargeCol chargeCol3wW">
                        <div class="fields displayFlex">
                                <?php
                                $estabCodesArr = [];
                                if (isset($requestData['filterrequest']['selectedchargeCodes']) && !empty($requestData['filterrequest']['selectedchargeCodes']) && is_array($requestData['filterrequest']['selectedchargeCodes'])) {
                                    $estabCodesArr = $requestData['filterrequest']['selectedchargeCodes'];
                                }
                                if (isset($requestData['filterrequest']['selectedchargeCodes']) && !empty($requestData['filterrequest']['selectedchargeCodes']) && !is_array($requestData['filterrequest']['selectedchargeCodes'])) {
                                    $estabCodesArr[] = $requestData['filterrequest']['selectedchargeCodes'];
                                }
                                $estCode='';
                                if (isset($requestData['allchargeCodes']) && !empty($requestData['allchargeCodes'])) {
                                        foreach ($requestData['allchargeCodes'] as $key => $value) {
                                            $selectedStrEst	='';
                                                if (isset($estabCodesArr) && in_array($value, $estabCodesArr)) {
                                                    $selectedStrEst	='selected';
                                                }
                                                $estCode .= '<option value="'.$value.'" '.$selectedStrEst.'>'.$value.'</option>';

                                        }
                                    } else {
                                        $estCode .= '<option value="ALL" disabled>Not Found</option>';
                                    }
                                    ?>
                                <label for="estabCodes" class="labelTxtAlign">Charge Codes</label>
                                <input type='hidden' name='optedestabCodes' id='optedestabCodes' value=''>
                                <select id="estabCodes" data-placeholder="Select Charge Codes" multiple SIZE="10" style="height:110px;">

                                <option value="ALL">Select ALL</option>
                                    <?php
                                        echo $estCode;
                                    ?>
                                </select>
                    <div class="chargeCol chargeCol3v">
                    <button type="text" class="charging-btn" id="refresh">Refresh</button>
                    <button type="text" class="charging-btn" class="charging-btn" id="exportLink2" <?php if ($requestData['disableExport']) {echo 'disabled';} ?>><img src="./images/excel.svg" class="exportIcon">Export</button>
                        <p>You may click on the column headings to sort by that column. The spreadsheet will be produced with the selected order.</p>
                            </div>
                        </div>
                    </div>
              </div>

                    <div class="chargeCol5 infoBox infoBoxPos">
                    <div class="fields displayFlex">
                            <label for="mappingFrom" class="labelTxtAlign m-10 labelWw">Staff Number(s) Split with a comma ','</label>
                            <textarea class="splitCol" cols="30" rows="2" id="Staff_Numbers"><?php if (isset($requestData['filterrequest']['selectedstaffNumbers']) && !empty($requestData['filterrequest']['selectedstaffNumbers']) && !is_array($requestData['filterrequest']['selectedstaffNumbers'])) { echo $requestData['filterrequest']['selectedstaffNumbers']; } else {
                                if (is_array($requestData['filterrequest']['selectedstaffNumbers']) && !empty($requestData['filterrequest']['selectedstaffNumbers']))  {
                                    $itotal = sizeof($requestData['filterrequest']['selectedstaffNumbers']);
                                    $ij=0;
                                    foreach ($requestData['filterrequest']['selectedstaffNumbers'] as $row) {
                                        echo trim($row);
                                        $ij++;
                                        if ($ij < $itotal) {
                                            echo ",";
                                           }
                                    }
                                }

                            } ?></textarea>
                            </div>
                    </div>
                  <?php $topRow=[];
                                    if (isset($requestData['LeaveSummery_F'][0]) && (!empty($requestData['LeaveSummery_F'][0])) && (is_array($requestData['LeaveSummery_F'][0]))) {
                                        $topRow = array_keys($requestData['LeaveSummery_F'][0]);
                                    }
                                    ?>

                    <div class="chargeCol chargeCol12 MT-41">
                        <div class="tables weeklyChangingTable <?php if (isset($topRow) && !empty($topRow)) { ?>  weeklyChangingTableheight <?php } ?>" style="overflow: auto;">
                            <table id="leaveEntries" class="oddevenclass tablesmall reportTable" style="width:100%">
                                <thead>
                                    <tr class="headersticky headerstickyleavetable">
                                    <?php
                                    for ($k=0 ; $k<1; $k++) {
                                        echo '<th class="leavereportSetW">Name</th>';
                                        echo '<th class="leavereportSetW">Staff Number</th>';
                                        echo '<th class="leavereportSetW">Scheduling Team</th>';
                                        echo '<th class="leavereportSetW">Charge Code</th>';
                                        echo '<th class="leavereportSetW">Leave Year</th>';
                                        if (in_array('Comp', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Comp'].' Credit</th>';
                                        }
                                        if (in_array('PHL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['PHL'].' Credit</th>';
                                        }

                                        if (in_array('Annual', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Annual'].' Credit</th>';
                                        }
                                        if (in_array('Under11TOIL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Under11TOIL'].' Credit</th>';
                                        }
                                        if (in_array('Over12TOIL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Over12TOIL'].' Credit</th>';
                                        }
                                        if (in_array('Additional', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Additional'].' Credit</th>';
                                        }
                                        if (in_array('Casual', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Casual'].' Credit</th>';
                                        }
                                        if (in_array('Exceptional', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Exceptional'].' Credit</th>';
                                        }
                                        if (in_array('LongService', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['LongService'].' Credit</th>';
                                        }
                                        if (in_array('Other', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Other'].' Credit</th>';
                                        }

                                            echo '<th class="leavereportSetW">Total Credit</th>';
                                                    /// Debit

                                        if (in_array('Comp', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Comp'].' Taken</th>';
                                        }

                                        if (in_array('PHL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['PHL'].' Taken</th>';
                                        }

                                        if (in_array('Annual', $TopROWDBCols)) {
                                            echo '<th>'.$topLIST['Annual'].' Taken</th>';
                                        }

                                        if (in_array('Under11TOIL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Under11TOIL'].' Taken</th>';
                                        }
                                        if (in_array('Over12TOIL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Over12TOIL'].' Taken</th>';
                                        }
                                        if (in_array('Additional', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Additional'].' Taken</th>';
                                        }
                                        if (in_array('Casual', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Casual'].' Taken</th>';
                                        }
                                        if (in_array('Exceptional', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Exceptional'].' Taken</th>';
                                        }
                                        if (in_array('LongService', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['LongService'].' Taken</th>';
                                        }
                                        if (in_array('Other', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Other'].' Taken</th>';
                                        }

                                            echo '<th class="leavereportSetW">Total Taken</th>';


                                        //Remaining
                                        if (in_array('Comp', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Comp'].' Remain</th>';
                                        }
                                        if (in_array('PHL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['PHL'].' Remain</th>';
                                        }
                                        if (in_array('Annual', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Annual'].' Remain</th>';
                                        }

                                        if (in_array('Under11TOIL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Under11TOIL'].' Remain</th>';
                                        }
                                        if (in_array('Over12TOIL', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Over12TOIL'].' Remain</th>';
                                        }
                                        if (in_array('Additional', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Additional'].' Remain</th>';
                                        }
                                        if (in_array('Casual', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Casual'].' Remain</th>';
                                        }
                                        if (in_array('Exceptional', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Exceptional'].' Remain</th>';
                                        }
                                        if (in_array('LongService', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['LongService'].' Remain</th>';
                                        }
                                        if (in_array('Other', $TopROWDBCols)) {
                                            echo '<th class="leavereportSetW">'.$topLIST['Other'].' Remain</th>';
                                        }

                                            echo '<th class="leavereportSetW">Total Remain</th>';

                                    }
                                    ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($requestData['LeaveSummery_F'] as $Row){
                                        ?>
                                    <tr>
                                        <td><?php if(isset($Row['DisplayName'])) { echo $Row['DisplayName']; } else{ echo '-';} ?></td>
                                        <td><?php if(isset($Row['StaffNumber'])) { if($Row['StaffNumber']!=0) { echo $Row['StaffNumber'];} else{ echo 'Not Set';} } else{ echo '-';} ?></td>
                                        <td><?php if(isset($Row['schedulingTeamName'])) { echo $Row['schedulingTeamName'];} else{ echo '-';} ?></td>
                                        <td><?php if(isset($Row['Charge_Codes'])) { echo $Row['Charge_Codes'];} else{ echo '-';}?></td>
                                        <td><?php if(isset($Row['iyear'])) { echo "Y-".$Row['iyear'];} else{ echo '-';}?></td>
                                        <!--Credit-->
                                        <?php if (in_array('Comp', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Comp'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Comp'],2);?></td>
                                        <?php
                                            }
                                        ?>
                                        <?php
                                        if (in_array('PHL', $topRow)) {
                                                $customeclass='lightGreen';
                                                if ($Row['PHL'] < 0) {
                                                    $customeclass="lightGreen negativeValue";
                                                }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['PHL'], 2);?></td>
                                        <?php }
                                        ?>
                                        <?php
                                        if (in_array('Annual', $topRow)) {
                                                $customeclass='lightGreen';
                                                if ($Row['Annual'] < 0) {
                                                    $customeclass="lightGreen negativeValue";
                                                }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Annual'],2);?></td>
                                        <?php } ?>

                                        <?php if (in_array('Under11TOIL', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Under11TOIL'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Under11TOIL'], 2);?></td>
                                        <?php
                                            }
                                        ?>
                                        <?php if (in_array('Over12TOIL', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Over12TOIL'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Over12TOIL'], 2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('Additional', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Additional'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Additional'], 2);?></td>
                                        <?php } ?>

                                        <?php if (in_array('Casual', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Casual'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                        ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Casual'], 2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('Exceptional', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Exceptional'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                           ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Exceptional'], 2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('LongService', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['LongService'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                             ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['LongService'],2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('Other', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Other'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Other'],2);?></td>
                                        <?php } ?>
                                        <td class="darkGreen totalColumn"><?php echo round($Row['TotalLeaveAllocated'], 2);?></td>
                                        <!--Debit-->
                                        <?php if (in_array('CompTaken', $topRow)) {
                                            $customeclass='lightPink';
											$Row['CompTaken'] = isset($Row['CompTaken']) ? $Row['CompTaken'] : 0;
                                            if ($Row['CompTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['CompTaken'], 2);?></td>
                                        <?php } ?>

                                        <?php if (in_array('PHLTaken',$topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['PHLTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['PHLTaken'], 2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('AnnualTaken',$topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['AnnualTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            } ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['AnnualTaken'],2);?></td>
                                        <?php } ?>

                                        <?php if (in_array('Under11TOILTaken', $topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['Under11TOILTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            }
                                         ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Under11TOILTaken'], 2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('Over12TOILTaken', $topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['Over12TOILTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Over12TOILTaken'], 2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('AdditionalTaken', $topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['AdditionalTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['AdditionalTaken'] ,2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('CasualTaken', $topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['CasualTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['CasualTaken'], 2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('ExceptionalTaken',$topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['ExceptionalTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            } ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['ExceptionalTaken'],2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('LongServiceTaken', $topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['LongServiceTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['LongServiceTaken'],2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('OtherTaken', $topRow)) {
                                            $customeclass='lightPink';
                                            if ($Row['OtherTaken'] < 0) {
                                                $customeclass="lightPink negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['OtherTaken'],2);?></td>
                                        <?php } ?>
                                        <td class="darkPink totalColumn"><?php echo round($Row['TotalLeaveTaken'], 2);?></td>

                                        <!--Remaining-->
                                        <?php if (in_array('CompRemaining', $topRow)) {
                                            $customeclass='lightGreen';
											$Row['CompRemaining'] = isset($Row['CompRemaining']) ? $Row['CompRemaining'] : 0;
                                            if ($Row['CompRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['CompRemaining'], 2);?></td>
                                        <?php } ?>

                                        <?php if (in_array('PHLRemaining', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['PHLRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                             ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['PHLRemaining'], 2);?></td>
                                        <?php } ?>

                                        <?php if (in_array('AnnualRemaining', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['AnnualRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['AnnualRemaining'], 2);?></td>
                                        <?php } ?>


                                        <?php if (in_array('Under11TOILRemaining',$topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Under11TOILRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Under11TOILRemaining'],2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('Over12TOILRemaining',$topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['Over12TOILRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['Over12TOILRemaining'],2);?></td>
                                        <?php } ?>
                                        <?php if (in_array('AdditionalRemaining', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['AdditionalRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['AdditionalRemaining'],2);?></td>
                                        <?php } ?>

                                        <?php if (in_array('CasualRemaining',$topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['CasualRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                           ?>
                                        <td class="<?php echo $customeclass; ?>"><?php echo round($Row['CasualRemaining'],2);?></td>
                                        <?php
                                        }
                                        ?>

                                        <?php if (in_array('ExceptionalRemaining', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['ExceptionalRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass;  ?>"><?php echo round($Row['ExceptionalRemaining'],2);?></td>
                                        <?php
                                        }
                                        ?>

                                        <?php if (in_array('LongServiceRemaining', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['LongServiceRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass;  ?>"><?php echo round($Row['LongServiceRemaining'],2);?></td>
                                        <?php
                                        }
                                        ?>
                                        <?php if (in_array('OtherRemaining', $topRow)) {
                                            $customeclass='lightGreen';
                                            if ($Row['OtherRemaining'] < 0) {
                                                $customeclass="lightGreen negativeValue";
                                            }
                                            ?>
                                        <td class="<?php echo $customeclass;  ?>"><?php echo round($Row['OtherRemaining'], 2);?></td>
                                        <?php } ?>
                                        <td class="darkGreen totalColumn"><?php echo round($Row['TotalLeaveRemaining'], 2);?></td>

                                    </tr>
                                    <?php
                                        }

                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
                                    </div>
</div>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="js/jszip.min.js"></script>
<script type="text/javascript" src="js/pdfmake.min.js"></script>
<script type="text/javascript" src="js/vfs_fonts.js"></script>
<script type="text/javascript" src="js/buttons.html5.min.js"></script>
<script type="text/javascript" src="js/buttons.print.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.13.1/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css"/>

<script type="text/javascript">
var table;
var $fileName = 'leaveSummaryReport';
$(document).ready(function() {
var leaveYearStart = $("#leaveYear").val();
var leaveYearEnd = $("#leaveFinsh").val();
var schedulingTeams = $('#selectedTeamnames').val();
var $sheetTitle ="BBC News Allocate - Leave Summary Report From Leave Year "+leaveYearStart +" TO "+leaveYearEnd;
var $messageTop =" Scheduling Teams : " + schedulingTeams;
    $('#leaveEntries').DataTable( {
        scrollCollapse: true,
        paging: false,
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ],
       initComplete: function() {
       	var $buttons = $('.dt-buttons').hide();
        $('#exportLink2').on('click', function() {
            var btnClass ='.buttons-excel';
            if (btnClass) $buttons.find(btnClass).click();
        });
       },
       buttons: [
            {
                extend: 'excelHtml5',
                filename: $fileName,
                title: $sheetTitle,
                messageTop: $messageTop,
                customize: function( xlsx ) {
                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                $('row :first', sheet).attr('s', '7');
                $('row', sheet).first().attr('ht', '30').attr('customHeight', "1");
                $('row* ', sheet).each(function(index) {
                if (index > 0 && index < 2) {
                    $(this).attr('ht', 30);
                    $(this).attr('customHeight', 1);
                }
                });
                var cols =$('col',sheet);
                var rows =$('row',sheet);
                var i; var y; var ltr; var prefLtr; var colLtrs = [];
                //fill the Excel column letters array
                for ( i=0; i < cols.length; i++ ) {
                    if ( i == 0 ) {
                        prefLtr = '';
                        ltr = 'A';
                    } else if ( i == 26 ) {
                        prefLtr = 'A';
                        ltr = 'A';
                    }
                    colLtrs.push(prefLtr + ltr);
                    ltr = String.fromCharCode(ltr.charCodeAt() + 1);
                }
                var searchtext ="Taken";
                var searchtot ="Total";
                var newRow = '';
                var oldRow = '';
                var takencols =[];
                var totalcols =[];
                for (i=2; i < 3; i++ ) {
                    for ( y=5; y < cols.length; y++ ) {
                        var mainstring = $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text();
                        var isFound = mainstring.includes(searchtext);
                        var isFoundTot = mainstring.includes(searchtot);
                        if (isFound==true) {
                            var total= takencols.push(y);
                        }
                        if (isFoundTot==true) {
                            var totalTot= totalcols.push(y);
                        }
                        $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '47' );
                    }
                    for(y=0; y < 5; y++ )
                    {
                        $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '47' );
                    }
                }

                for (i=1; i < 2; i++ ) {
                    for ( y=0; y < cols.length; y++ ) {
                        $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '8' );
                    }
                }

                for (i=2; i < rows.length; i++ ) {
                    for ( y=5; y < cols.length; y++ ) {

                            if (jQuery.inArray(y, takencols) !== -1) {
                                if ($('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text() < 0) {
                                $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '12' );  //white text red
                                }
                                if ($('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text() >= 0 && $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text() < 100) {
                                $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '10');  //white text red
                                }
                                if ($('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text() >=100) {
                                $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '10');  //white text red
                                }
                            } else {
                                if ($('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text() < 0) {
                                $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '12');  //white text red
                                } else {
                                if ($('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text() >= 0 && $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text() < 100) {
                                $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '15');  //white text red
                                }
                                if ($('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).text() >=100) {
                                $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '17');  //white text red
                                }
                            }
                        }
                    }
                }
            }
            },
        ]
    });
} );

</script>
<script type="text/javascript">
$(document).ready(function () {
    if ($("#leaveYear").data('options') === undefined) {
        /*Taking an array of all options-2 and kind of embedding it on the select1*/
        $("#leaveYear").data('options', $('#leaveFinsh option').clone());
    }
    var lyear = $("#leaveYear").val();
    $('#leaveFinsh option').filter(function() {
        return $(this).val() < lyear;
    }).prop('disabled', true);

    $('#leaveFinsh option').filter(function() {
        return $(this).val() >= lyear;
    }).prop('disabled', false);

    if ($('#groupBy1').data('options') === undefined) {
        /*Taking an array of all options-2 and kind of embedding it on the select1*/
        $(this).data('options', $('#groupBy2 option').clone());
    }
    var id = $('#groupBy1').val();
    if (id!='Nothing') {
     var options = $(this).data('options').filter('[value!=' + id + ']');
    }
    $('#groupBy2').html(options);

    var yearTypeck = $('#yearType').val();
    var reportTypechk = $('#reportType').val();
    if(yearTypeck=='range' && yearTypeck !='undefined')
    {
        $('#leaveFinsh').removeAttr("disabled");
    } else {
        $('#leaveFinsh').attr("disabled","disabled");
    }
    if(reportTypechk=='GRP' && reportTypechk!='undefined')
    {
        $('#groupBy1').removeAttr("disabled");
        $('#groupBy2').removeAttr("disabled");
    } else {
        $('#groupBy1').attr("disabled","disabled");
        $('#groupBy2').attr("disabled","disabled");
    }

    $('#leaveFinsh').change(function() {
      var leavestartval= $('#leaveYear').val();
      var endyear =$(this).val();
      if( endyear< leavestartval)
      {
        customAlertByModel("You must have a leave end year that is greater or equal to your leave start year.");
        $('#leaveFinsh').focus();
      }

    });

    $("#leaveYear").change(function() {
    if ($(this).data('options') === undefined) {
        /*Taking an array of all options-2 and kind of embedding it on the select1*/
        $(this).data('options', $('#leaveFinsh option').clone());
    }
        var lyear = $(this).val();
        $('#leaveFinsh option').filter(function() {
        return $(this).val() < lyear;
        }).prop('disabled', true);

        $('#leaveFinsh option').filter(function() {
        return $(this).val() >= lyear;
        }).prop('disabled', false);
    });

    /** Group BY ITEM Setting Start*/
    $("#groupBy1").change(function() {
    if ($(this).data('options') === undefined) {
        /*Taking an array of all options-2 and kind of embedding it on the select1*/
        $(this).data('options', $('#groupBy2 option').clone());
    }
    var id = $(this).val();
    var options = $(this).data('options').filter('[value!=' + id + ']');
    $('#groupBy2').html(options);
    });
    /** Group By Item Setting END */
	$(".weeklyChargingSelect").chosen({
                no_results_text: "Oops, nothing found!",
                width: "100%"
            });

    $(".chosen-choices").css('max-height','60px');
    $(".chosen-choices").css('overflow','auto');

    $('#estabCodes').change(function() {
        if ($(this).val() == 'ALL') {
            $('#estabCodes option').prop('selected', true);
        }
        let estabCodes = $('#estabCodes').val();

        if (estabCodes != null && typeof estabCodes !== "undefined") {
            estabCodes = $('#estabCodes').val().toString();
        }
        $('#optedestabCodes').val(estabCodes);
    });

    $("#refresh").trigger('click');
    $('#schedulingTeam').change(function() {
        if ($(this).val() == 'ALL') {
            $('#schedulingTeam option').prop('selected', true);
        }
    });
    $('#DisplayColumns').change(function() {
        if ($(this).val() == 'ALL') {
            $('#DisplayColumns option').prop('selected', true);
        }
    });



    //When Column Report Filter Changed
    $("#refresh").click(function() {
        let yearType   = $('#yearType').val();
        let reportType = $('#reportType').val();
        let leaveYear  = $('#leaveYear').val();
        let leaveFinsh = $('#leaveFinsh').val();
        let groupBy1 = $('#groupBy1').val();
        let groupBy2 = $('#groupBy2').val();
        let Staff_Numbers = $('#Staff_Numbers').val();
        let schedulingTeam = $('#schedulingTeam').val();
        let estabCodes = $('#optedestabCodes').val();
        let DisplayColumns = $('#DisplayColumns').val()
        if (Staff_Numbers != null && typeof Staff_Numbers !== "undefined")        {
            Staff_Numbers = $('#Staff_Numbers').val().toString();
        }
        if (schedulingTeam != null && typeof schedulingTeam !== "undefined") {
            schedulingTeam = $('#schedulingTeam').val().toString();
        }
        if (DisplayColumns!=null && typeof DisplayColumns !== "undefined") {
            DisplayColumns = $('#DisplayColumns').val().toString();
        }
        let columnFilter = $('#FilterColName').val();
        let FilterSymbol = $('#FilterSymbol').val();
        let FilterCompValue = $('#FilterSymbolValue').val();
        let DataArr = {
            'selectedyearType':yearType,
            'selectedreportType':reportType,
            'selectedleaveFromYear':leaveYear,
            'selectedleaveTo':leaveFinsh,
            'selectedgroupBy1':groupBy1,
            'selectedgroupBy2':groupBy2,
            'selectedstaffNumbers':Staff_Numbers,
            'selectedschedulingTeam':schedulingTeam,
            'selectedchargeCodes':estabCodes,
            'selectedavailableColumn':DisplayColumns,
            'selectedFilterColumn':columnFilter,
            'selectedfilterSign':FilterSymbol,
            'selectedfiltervalue':FilterCompValue,
            'conrollerName'	:'showLeaveSummary'
            };
            $.ajax({
                url: 'page-includes/admin/charging/index.php',
                type:"post",
                data:DataArr,
                success:function(response,status,http) {
                   $('#leaveReport-summary').html(response);
                },
                error:function(http,status,error) {
                    customAlertByModel("Some Error Found in Response :" + error);
                }
            });
        });

        /** Check the Export  Button activation*/
        $('#leaveFinsh,#yearType,#reportType,#leaveYear,#groupBy1,#groupBy2,#Staff_Numbers,#schedulingTeam,#estabCodes,#DisplayColumns').change(function() {
           $('#DownReport').attr('disabled','disabled');
        });
});

function createDocs()
{
	let yearType   = $('#yearType').val();
    let reportType = $('#reportType').val();
    let leaveYear  = $('#leaveYear').val();
        let leaveFinsh = $('#leaveFinsh').val();
        let groupBy1 = $('#groupBy1').val();
        let groupBy2 = $('#groupBy2').val();
        let Staff_Numbers = $('#Staff_Numbers').val();
        let schedulingTeam = $('#schedulingTeam').val();
        let estabCodes = $('#estabCodes').val();
        let DisplayColumns = $('#DisplayColumns').val()
        if (Staff_Numbers != null && typeof Staff_Numbers !== "undefined")        {
            Staff_Numbers = $('#Staff_Numbers').val().toString();
        }
        if (schedulingTeam != null && typeof schedulingTeam !== "undefined") {
            schedulingTeam = $('#schedulingTeam').val().toString();
        }
        if (estabCodes != null && typeof estabCodes !== "undefined") {
            estabCodes = $('#estabCodes').val().toString();
        }
        if (DisplayColumns!=null && typeof DisplayColumns !== "undefined") {
            DisplayColumns = $('#DisplayColumns').val().toString();
        }
        let columnFilter = $('#FilterColName').val();
        let FilterSymbol = $('#FilterSymbol').val();
        let FilterCompValue = $('#FilterSymbolValue').val();
        if (leaveYear!=false && schedulingTeam!=false && schedulingTeam != null && typeof schedulingTeam !== "undefined") {
            location='page-includes/admin/charging/YearlyLeaveSummaryExport.php?yearType='+yearType+'&reportType='+reportType+'&leaveYear='+leaveYear+'&leaveFinsh='+leaveFinsh+'&groupBy1='+groupBy1+'&groupBy2='+groupBy2+'&Staff_Numbers='+Staff_Numbers+'&schedulingTeam='+schedulingTeam+'&estabCodes='+estabCodes+'&DisplayColumns='+DisplayColumns+'&columnFilter='+columnFilter+'&FilterSymbol='+FilterSymbol+'&FilterCompValue='+FilterCompValue+'&orderBy='+btoa(table.order());
        } else {
            customAlertByModel("Check selected Schedulling Team & leave Year, They are not set.Try again.");
        }
}
</script>
