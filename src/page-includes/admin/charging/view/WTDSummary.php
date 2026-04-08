<link href="../styles/charging/charging.css" rel="stylesheet">
<link href="../styles/charging/chargingReports.css" rel="stylesheet">
<title>WTD Summary</title>
<div id="wtdReport-summary">
        <h1 class="headertextallpages  main-heading">WTD Report</h1>
        <div id="wtdReport" class="chargeRow">
            <div class="filterContainerFlex chargeCol10">
                <h2 class="heading">Filter</h2>
                <div class="chargeRow">
                    <div class="chargeCol chargeCol4">
                        <div class="fields">
                        <label for="reportType" class="labelTxtAlign">Report Type</label>
                            <select id="reportType" class="weeklyChargingFrom" onChange="if(this.value == 'GRP'){ $('#groupBy1').removeAttr('disabled'); $('#groupBy2').removeAttr('disabled'); }else{ $('#groupBy1').attr('disabled','disabled'); $('#groupBy2').attr('disabled','disabled'); }">
							<option value="ALL" <?php if(isset($requestData['filterrequest']['selectedreportType']) && $requestData['filterrequest']['selectedreportType']=='ALL'){ echo 'Selected';} ?>>Show All Records</option>
                                <option value="GRP" <?php if(isset($requestData['filterrequest']['selectedreportType']) && $requestData['filterrequest']['selectedreportType']=='GRP'){ echo 'Selected';} ?>>Group Records</option>   
                            </select>
						</div>

                        <div class="fields">
                            <label for="groupBy1" class="labelTxtAlign">Group By1</label>
                            <select id="groupBy1" disabled class="weeklyChargingFrom">
                                <option value="schedulingTeamName" <?php if(isset($requestData['filterrequest']['selectedgroupBy1']) && $requestData['filterrequest']['selectedgroupBy1']=='schedulingTeamName'){ echo 'Selected';} ?>>Scheduling Team</option>
                            </select>
						</div>
						
						<div class="fields">
                            <label for="groupBy2" class="labelTxtAlign">Group By2</label>
                            <select id="groupBy2" disabled class="weeklyChargingFrom">
								<option value="Nothing">--Nothing--</option>
                                <option value="DisplayName" <?php if(isset($requestData['filterrequest']['selectedgroupBy2']) && $requestData['filterrequest']['selectedgroupBy2']=='DisplayName'){ echo 'Selected';} ?>>Name</option>
                                <option value="BreachType" <?php if(isset($requestData['filterrequest']['selectedgroupBy2']) && $requestData['filterrequest']['selectedgroupBy2']=='BreachType'){ echo 'Selected';} ?>>Breach Type</option>
                                <option value="CostCode" <?php if(isset($requestData['filterrequest']['selectedgroupBy2']) && $requestData['filterrequest']['selectedgroupBy2']=='CostCode'){ echo 'Selected';} ?>>Charge Code</option>
                            </select>
						</div>

						<div class="fields chargeRow">
						<div class="weeksBlock chargeCol8">
						<div>
						<label for="weekStartYear" class="labelTxtAlign">Week From</label>
                        <?php
                            $currentLeaveYear = $requestData['currentWTDYear'] ?? 0;
                            $YearTo = $requestData['filterrequest']['selectedWTDYearTo'] ?? $currentLeaveYear;
                            $YearFrom = $requestData['filterrequest']['selectedWTDYearFrom'] ?? $currentLeaveYear;
                            ?>
                                <select id="weekStartYear" class="weekStatus weekW-50">
								<?php for ($i=$currentLeaveYear-7;$i < $currentLeaveYear;$i++)
								{
									?>
  									<option value="<?php echo $i; ?>" <?php if($YearFrom== $i) { echo "selected"; }?>><?php echo $i; ?></option>
									<?php
								}
								?>
                                <?php for ($i=$currentLeaveYear;$i<=($currentLeaveYear+2);$i++) {
									?>
  									<option value="<?php echo $i; ?>" <?php if($YearFrom== $i) { echo "selected"; }?>><?php echo $i; ?></option>
									<?php
								}
								?>
                            </select>
                            <span class="slas">/</span>
                            <select id="weekStart" name="weekStart" class="weekStatus">
                                <?php
                                    $requestedWeek = $requestData['filterrequest']['selectedWTDWeekFrom'] ?? 0;
									for ($weekItr = 1; $weekItr < 53; $weekItr++) {
										$selectedStr	=	'';
										if ($weekItr == $requestedWeek) {
											$selectedStr	=	'selected';
										}
										$weekItrVal = ($weekItr <= 9) ? "0".$weekItr : $weekItr;
										echo '<option value="'.$weekItrVal.'" '.$selectedStr.'>'.$weekItrVal.'</option>';
									}
								?>
                            </select>
                        </div>
						<div>
                            <label for="weekToYear" class="labelTxtAlign">Week To</label>
                            <select id="weekToYear" class="weekStatus weekW-50">
							<?php for ($i=$currentLeaveYear-7;$i <$currentLeaveYear;$i++) {
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

                            <span class="slas">/</span>
                            <select id="weekTo" name="weekTo" class="weekStatus">
                                <?php
                                    $requestedendWeek = $requestData['filterrequest']['selectedWTDWeekTo'] ?? 0;
									for ($weekItr = 1; $weekItr < 53; $weekItr++) {
										$selectedStr	=	'';
										if ($weekItr == $requestedendWeek) {
											$selectedStr	=	'selected';
										}
										$weekItrVal = ($weekItr <= 9) ? "0".$weekItr : $weekItr;
										echo '<option value="'.$weekItrVal.'" '.$selectedStr.'>'.$weekItrVal.'</option>';
									}
								?>
                            </select>
						</div>
						</div>
							
						<div class="timeBlock chargeCol2">
						    <div class="timeContent">
                                <span><input type="hidden" value="<?php echo date('Y-m-d',strtotime($requestData['wtdstartDate']));?>" id="wtdstartDate"> 
                                <?php echo date('d/m/Y', strtotime($requestData['wtdstartDate']));?></span>
                                <span>To</span>
                                <span><input type="hidden" value="<?php echo date('Y-m-d',strtotime($requestData['wtdendDate']));?>" id="wtdendDate"> 
                                <?php echo date('d/m/Y', strtotime($requestData['wtdendDate']));?></span>
							</div>
                        </div>
						</div>
					</div>

                    <div class="chargeCol chargeCol3">
                        <div class="fields displayFlex" >
                            <label for="schedulingTeam" class="labelTxtAlign schedulingTeamW">Scheduling Teams</label>
                            <select id="schedulingTeam" data-placeholder="Select Scheduling Teams" multiple SIZE="10" style="height:150px;">
                                <?php
                                  $selectedTeams=[];
                                  $selected='';
                                  if (isset($requestData['filterrequest']['selectedTeamID']) && !empty($requestData['filterrequest']['selectedTeamID']) && is_array($requestData['filterrequest']['selectedTeamID'])) { 
                                       $selectedTeams = array_values($requestData['filterrequest']['selectedTeamID']);
                                  } else {
                                   $selectedTeams[] = $requestData['filterrequest']['selectedTeamID'] ?? "ALL";
                                  }
                                  if (in_array('ALL', $selectedTeams)) {
                                        $selected ='selected';
                                    }
                                ?>
							<option value="ALL">Select ALL</option>
							<?php
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
                            <?php $selectedTeamsNA = $selectedTeamsNA ?? null; ?>
                            <input type="hidden" value="<?php if(is_array($selectedTeamsNA)) { echo implode(", ",$selectedTeamsNA);} else{ echo $selectedTeamsNA;}?>"  id="selectedTeamnames"/>
                        </div>

                        <div class="fields displayFlex">
                            <label for="mappingFrom" class="labelTxtAlign staffnumWidth">Staff Number(s) Split with a comma','</label>
                            <textarea class="splitCol" cols="35" id="Staff_Numbers"><?php if(isset($requestData['filterrequest']['selectedstaffNumbers']) && !empty($requestData['filterrequest']['selectedstaffNumbers']) && !is_array($requestData['filterrequest']['selectedstaffNumbers'])) { echo $requestData['filterrequest']['selectedstaffNumbers']; } else {
                                if (isset($requestData['filterrequest']) && is_array($requestData['filterrequest']['selectedstaffNumbers']) && !empty($requestData['filterrequest']['selectedstaffNumbers']))  {
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
					<div class="chargeCol chargeCol4 p14">
                        <div class="fields displayFlex">
                            <label for="estabCodes" class="labelTxtAlign" style="width:38%;">
                              <div class="charge-text-clr">Charge Codes</div>
                              <div>
                                <button type="button" class="charging-btn-clr" id="clearChargeCodes">Clear Charge Codes</button>
                              </div>
                            </label>
                    <?php
                    $estabCodesArr = [];
                    $selected='';
                    if (isset($requestData['filterrequest']['selectedchargeCodes']) && !empty($requestData['filterrequest']['selectedchargeCodes']) && is_array($requestData['filterrequest']['selectedchargeCodes'])) {
                        $estabCodesArr = $requestData['filterrequest']['selectedchargeCodes'];
                    }
                    if (isset($requestData['filterrequest']['selectedchargeCodes']) && !empty($requestData['filterrequest']['selectedchargeCodes']) && !is_array($requestData['filterrequest']['selectedchargeCodes'])) {
                        $estabCodesArr[] = $requestData['filterrequest']['selectedchargeCodes'] ?? "ALL";
                    }
                    if (in_array('ALL', $estabCodesArr)) {
                        $selected ='selected';
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
                     <select id="estabCodes" data-placeholder="Select Charge Codes" multiple SIZE="10" style="height:150px;">
                     <option value="ALL">Select ALL</option>
                                <?php
									echo $estCode;
								?>
                    </select>
                    <input type='hidden' name='optedestabCodes' id='optedestabCodes' value=''>
                    </div>
					<div class="fields">
                            <label for="BreachType" class="labelTxtAlign  BreachW">Breach Type</label>
                            <?php
                            $selectedBreachType='';
                            if (isset($requestData['filterrequest']['WTDbreechType']) && !empty($requestData['filterrequest']['WTDbreechType'])) {
                                $selectedBreachType = $requestData['filterrequest']['WTDbreechType'] ?? 0;
                                if ($selectedBreachType == '-1') {
                                    $selectedBreachType ='ALL';
                                }
                           }
                           ?>
                            <select id="BreachType" class="weeklyChargingFrom weeklyChargingFromW">
                            <option value="ALL" <?php if ($selectedBreachType == 'ALL') { echo 'selected'; } ?>>--Select All--</option>
                            <?php
                           
							 if (isset($requestData['getWTDTypes']) && !empty($requestData['getWTDTypes'])) {
									foreach ($requestData['getWTDTypes'] as $key => $value) {
                                        $selected ='';
                                        if ($value["ID"]==$selectedBreachType) {
                                            $selected ='selected';
                                        }
                                        if ($value["ID"] > 0 && $value["ID"]!=2) {
                                            if ($value["ID"]==1) {
                                                $rule='Less than 11 Hours break';
                                            }
                                            if ($value["ID"]==3) {
                                                $rule='More than ave 48 hours/Week';
                                            }
                                            if ($value["ID"]==4) {
                                                $rule='More than 6 consecutive shifts';
                                            }
										?>
										<option value="<?php echo $value["ID"]; ?>" <?php echo $selected ;?>><?php echo $rule; ?></option>
										<?php
                                        }
									}
								}
								?>
                            </select>
						</div>
						<div class="fields">
                            <?php
                            $selecteapprovalStatus='ALL';
                            if (isset($requestData['filterrequest']['approvalStatus'])) {
                                $selecteapprovalStatus = $requestData['filterrequest']['approvalStatus'];
                            }
                          ?>
                            <label for="BreachStatus" class="labelTxtAlign BreachW">Breach Status</label>
                            <select id="BreachStatus" class="weeklyChargingFrom weeklyChargingFromW">
                                <option value="ALL" <?php if ($selecteapprovalStatus=='ALL') { echo 'selected'; } ?>>--Select All--</option>
                                <option value="1" <?php if ($selecteapprovalStatus=='1') { echo 'selected'; } ?>>Approved</option>
                                <option value="0" <?php if ($selecteapprovalStatus=='0') { echo 'selected'; } ?>>Not Aprpoved</option>
                               
                            </select>
						</div>
						
                    </div>
                    
                    <div class="chargeCol chargeCol1">
                        <button type="text" class="charging-btn" id="refresh">Refresh</button>
                        <button type="text" class="charging-btn" id="exportLink2" <?php if($requestData['disableExport']){echo 'disabled';} ?>><img src="./images/excel.svg"
                                class="exportIcon">Export</button>
                       
                    </div>
					
                </div>
            </div>

            <div class="chargeCol2 infoBox">

                <div class="filterContainerFlex">
                    <h3 class="heading">Key</h3>
                    <div class="keyBlock">
                        <div class="keytext keytextunset">WTD Approved</div>
                        <div class="keyBox1"></div>
                    </div>

                    <div class="keyBlock">
                        <div class="keytext keytextunset">WTD not Approved</div>
                        <div class="keyBox2 keyBoxPink"></div>
                    </div>
                </div>


         
                <p>You may click on the column headings to sort by that column. The spreadsheet will be produced with
                    the selected order.</p>
            </div>
			</div>

            <div class="chargeCol chargeCol12">
                <div class="tables weeklyChangingTable">
                    <table id="wtdEntries" class="oddevenclass tablesmall reportTable" style="width:100%">
                        <thead>
                            <tr>
                                <?php $topRow=[];
                                if (isset($requestData['WTDSummery_F']) && !empty($requestData['WTDSummery_F'])) { 
                                        $topRow = array_keys($requestData['WTDSummery_F'][0]);
                                    } ?>
                                   
                                   <th>Scheduling Team</th>
                                   <th>Name</th>
                                   <th>Charge Code</th>
                                    <?php
                                    if (in_array('StaffNumber', $topRow)) {
                                    ?>
                                        <th>Staff Number</th>
                                    <?php } ?>
                               
                                                                  
                                    <th>Breach Type</th>
                                    <?php
                                      if (in_array('StartDate', $topRow)) {
                                        ?>
                                        <th>Start Date</th>
                                    <?php } ?>
                                    <?php
                                        if (in_array('EndDate', $topRow)) {
                                        ?>
                                         <th>End Date</th>
                                    <?php } ?>
                                    <?php
                                        if (in_array('BreachedBy', $topRow)) {
                                        ?>
                                           <th>Breached By</th>
                                    <?php } ?>

                                    <?php
                                        if (in_array('BreachedDate', $topRow)) {
                                        ?>
                                            <th>Breached On</th>
                                    <?php } ?>

                                    <?php
                                        if (in_array('ApprovedBy', $topRow)) {
                                        ?>
                                           <th>Approved By</th>
                                    <?php } ?>

                                    <?php
                                        if (in_array('ApprovedDate', $topRow)) {
                                        ?>
                                            <th>Approved On</th>
                                    <?php } ?>

                                    <?php
                                        if (in_array('Comments', $topRow)) {
                                        ?>
                                            <th>Comments</th>
                                    <?php } ?>
                                    
                                    <?php
                                         if (in_array('Count', $topRow)) {
                                        ?>
                                            <th>Count</th>
                                    <?php } ?>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($requestData['WTDSummery_F']) && !empty($requestData['WTDSummery_F'])) {
                                foreach ($requestData['WTDSummery_F'] as $rowData) {
                                    if (isset($rowData['IsApproved']) && $rowData['IsApproved']==1) {
                                        $class="keyBoxBlue";
                                    } else {
                                        $class="keyBoxPink";
                                    }
                                    ?>
                                <tr class="<?php echo $class; ?>">
                                    <?php
                                    if (in_array('schedulingTeamName', $topRow)) {
                                    ?>
                                    <td><?php if (isset($rowData['schedulingTeamName'])) {echo $rowData['schedulingTeamName'];} ?></td>
                                    <?php
                                        } else { echo '<td>--</td>';}
                                    ?>
                                    <?php
                                    if (in_array('DisplayName', $topRow)) {
                                    ?>
                                    <td><?php if (isset($rowData['DisplayName'])) {echo $rowData['DisplayName'];} ?></td>
                                    <?php
                                        } else { echo '<td>--</td>';}
                                    ?>
                                    <?php
                                    if (in_array('CostCode', $topRow)) {
                                    ?>
                                    <td><?php if (isset($rowData['CostCode'])) {echo $rowData['CostCode'];} ?></td>
                                    <?php } else { echo '<td>--</td>';} ?>
                                    <?php
                                    if (in_array('StaffNumber', $topRow)) {
                                    ?>
                                    <td><?php if (isset($rowData['StaffNumber'])) {echo $rowData['StaffNumber'];} ?></td>
                                    <?php
                                      }
                                    ?>
                                    
                                    <?php
                                    if (in_array('BreachType', $topRow)) {
                                    ?>
                                    <td><?php if (isset($rowData['BreachType'])) {echo $rowData['BreachType'];} ?></td>
                                    <?php
                                        } else { echo '<td>--</td>';}
                                    ?>
                                    <?php
                                    if (in_array('StartDate', $topRow)) {
                                    ?>
                                    <td><?php if(isset($rowData['StartDate'])) {echo date('d/m/Y',strtotime($rowData['StartDate']));} ?></td>
                                    <?php
                                        }
                                     ?>

                                    <?php
                                    if (in_array('EndDate',$topRow)) {
                                    ?>
                                        <td><?php if(isset($rowData['EndDate'])) {echo date('d/m/Y',strtotime($rowData['EndDate']));} ?></td>
                                    <?php  } ?>
                                    <?php
                                    if (in_array('BreachedBy',$topRow)) {
                                        ?>
                                    <td><?php if (isset($rowData['BreachedBy']) && !empty($rowData['BreachedBy'])) {echo $rowData['BreachedBy'];} else{ echo '...';} ?></td>
                                    <?php
                                        }
                                    ?>
                                    <?php
                                   if (in_array('BreachedDate',$topRow))
                                         {
                                        ?>
                                    <td><?php if(isset($rowData['BreachedDate'])) {echo date('d/m/Y',strtotime($rowData['BreachedDate']));} ?></td>
                                    <?php
                                         }
                                    ?>
                                    <?php
                                   if (in_array('ApprovedBy',$topRow))
                                         {
                                        ?>
                                        <td><?php if(isset($rowData['ApprovedBy']) && !empty($rowData['ApprovedBy'])) {echo $rowData['ApprovedBy'];}  else {
                                            echo "...";}?></td>
                                    <?php
                                        }
                                    ?>
                                    <?php
                                   if (in_array('ApprovedDate',$topRow))
                                         {
                                        ?>
                                    <td><?php if(isset($rowData['ApprovedDate']) && !empty($rowData['ApprovedDate'])) {echo date('d/m/Y', strtotime($rowData['ApprovedDate']));} else{ echo '...'; } ?></td>
                                    <?php
                                         }
                                    ?>
                                   <?php
                                   if (in_array('Comments', $topRow)) {
                                        ?>
                                        <td><?php if (isset($rowData['Comments']) && !empty($rowData['Comments'])) {echo $rowData['Comments'];} else{ echo '...';} ?></td>
                                    <?php } ?>

                                    <?php
                                        if (in_array('Count', $topRow)) {
                                        ?>
                                        <td><?php if (isset($rowData['Count'])) {echo $rowData['Count'];} ?></td>
                                    <?php } ?>
                                                               
                                </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
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
var $fileName = 'WTDSummaryReport';
$(document).ready(function() {
    var wtdstartDate = $('#wtdstartDate').val();
    var wtdendDate = $('#wtdendDate').val();
    var schedulingTeams = $('#selectedTeamnames').val();
    var $sheetTitle ="BBC News Allocate - WTD Summary Report From "+wtdstartDate +" TO "+wtdendDate;
    var $messageTop ="Scheduling Teams : " + schedulingTeams;
    $('#wtdEntries').DataTable( {
        scrollY: '300px',
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
                var approvedcols =[];
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
                for (i=2; i < 3; i++ ) {
                    for ( y=0; y < cols.length; y++ ) {
                        $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '32' );
                    }
                }
                for (i=3; i < rows.length; i++ ) {
                    if ($('row:eq('+i+') c[r^='+colLtrs[9]+']', sheet).text().length == 3)
                    {
                        for ( y=0; y < cols.length; y++ ) {
                            $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '10' );
                        }
                    } else {
                        for ( y=0; y < cols.length; y++ ) {
                            $('row:eq('+i+') c[r^='+colLtrs[y]+']', sheet).attr( 's', '20' );
                        }
                    }
                }
                      
              }
                
            }
        ]
    });
} );
</script>
<script type="text/javascript">
$(document).ready(function () {
    var reportTypechk = $('#reportType').val();
    if(reportTypechk=='GRP' && reportTypechk!='undefined')
    {
        $('#groupBy1').removeAttr("disabled");
        $('#groupBy2').removeAttr("disabled");
    } else {
        $('#groupBy1').attr("disabled","disabled");
        $('#groupBy2').attr("disabled","disabled");
    }

    $('#weekToYear').change(function() {
      var leavestartval= $('#weekStartYear').val();
      var endyear =$(this).val();
      if( endyear< leavestartval)
      {
        customAlertByModel("LeaveEnd year must be more than LeaveStart year.");
        $('#weekToYear').focus();
      }
    
    });

    $("#weekStartYear").change(function() {
    if ($(this).data('options') === undefined) {
        /*Taking an array of all options-2 and kind of embedding it on the select1*/
        $(this).data('options', $('#leaveFinsh option').clone());
    }
        var lyear = $(this).val();
        $('#weekToYear option').filter(function() {
        return $(this).val() < lyear;
        }).prop('disabled', true);

        $('#weekToYear option').filter(function() {
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
    //When Column Report Filter Changed
    $("#refresh").click(function() {
        let reportType = $('#reportType').val();
        let weekStartYear = $('#weekStartYear').val();
        let weekToYear = $('#weekToYear').val();
        let weekStart = $('#weekStart').val();
        let weekTo = $('#weekTo').val();
        let groupBy1 = $('#groupBy1').val();
        let groupBy2 = $('#groupBy2').val();
        let Staff_Numbers = $('#Staff_Numbers').val();
        let schedulingTeam = $('#schedulingTeam').val();
        let estabCodes = $('#optedestabCodes').val();
        if (Staff_Numbers != null && typeof Staff_Numbers !== "undefined")        {
            Staff_Numbers = $('#Staff_Numbers').val().toString();
        }
        if (schedulingTeam != null && typeof schedulingTeam !== "undefined") {
            schedulingTeam = $('#schedulingTeam').val().toString();
        }
      
        let WTDbreechType = $('#BreachType').val();
        let BreachStatus = $('#BreachStatus').val();
        let DataArr = {
            'selectedreportType':reportType,
            'selectedWTDYearFrom':weekStartYear,
            'selectedWTDYearTo':weekToYear,
            'selectedWTDWeekTo':weekTo,
            'selectedWTDWeekFrom':weekStart,
            'selectedgroupBy1':groupBy1,
            'selectedgroupBy2':groupBy2,
            'selectedstaffNumbers':Staff_Numbers,
            'selectedschedulingTeam':schedulingTeam,
            'selectedchargeCodes':estabCodes,
            'conrollerName'	:'WTDSummary',
            'WTDbreechType':WTDbreechType,
            'approvalStatus':BreachStatus,
            };
            $.ajax({
                url: 'page-includes/admin/charging/index.php',
                type:"post",
                data:DataArr,
                success:function(response,status,http) {
                   $('#wtdReport-summary').html(response);
                },
                error:function(http,status,error) {
                    customAlertByModel("Some Error Found in Response :" + error);
                }
            });
        });

        /** Check the Export  Button activation*/
        $('#reportType,#leaveYear,#groupBy1,#groupBy2,#Staff_Numbers,#schedulingTeam,#estabCodes').change(function() {
           $('#DownReport').attr('disabled','disabled');
        });

        let isHandlingChange = false;
        $('#estabCodes').on('change', function () {
            if (isHandlingChange) return;
            isHandlingChange = true;
            let selvalue = $(this).val();
            if ((selvalue) && (selvalue.includes('ALL'))) {
                $('#estabCodes option').prop('selected', true);
            }
            let estabCodes = $('#estabCodes').val();
            $('#optedestabCodes').val(estabCodes ? estabCodes.toString() : '');
        });

        $(document).ready(function () {
            let estabCodes = $('#estabCodes').val();
            $('#optedestabCodes').val(estabCodes ? estabCodes.toString() : '');
            $('#schedulingTeam option').trigger('change');
        });
        
        $('#schedulingTeam').change(function() {
            if ($(this).val() == 'ALL') {
                $('#schedulingTeam option').prop('selected', true);
            }
        });

        $('#schedulingTeam').on('change', function() {
            let selected = $(this).val() || [];
            if (selected.includes("ALL")) {
                let allValues = $('#schedulingTeam option').map(function() {
                    return $(this).val();
                }).get();
                let filtered = allValues.filter(val => val !== "ALL");
                $('#schedulingTeam').val(filtered);
                selected = filtered;
            }
            let selectedTexts = $('#schedulingTeam option:selected').map(function() {
                return $(this).val();
            }).get();
            let selectedTeams = selectedTexts.join(', ');

            let DataArr = {
                'selTeams':selectedTeams,
                'conrollerName' :'getChargeCodesOfTeams'
            };
            $.ajax({
                url: 'page-includes/admin/charging/index.php',
                type:"post",
                data:DataArr,
                success:function(response,status,http) {
                    response = $.parseJSON(response);
                    let $select = $('#estabCodes');
                    $select.empty();
                    if (Array.isArray(response.chargeCodes) && response.chargeCodes.length > 0) {
                        $select.append($('<option>', {
                            value: 'ALL',
                            text: 'Select ALL'
                        }));
                        response.chargeCodes.forEach(function(code) {
                            if(code.UC_CostCode != null && code.UC_CostCode != ''){    
                                $select.append($('<option>', {
                                    value: code.UC_CostCode,
                                    text: code.UC_CostCode
                                }));
                            }
                        });
                    } else {
                        $select.append($('<option>', {
                            value: '',
                            text: 'No Charge Codes Found'
                        }));
                    }
                },
                error:function(http,status,error) {
                    customAlertByModel("Some Error Found in Response :" + error);
                }
            });
            
        });

        $('#clearChargeCodes').click(function() {
          $('#estabCodes option').prop('selected', false);
          $('#estabCodes').trigger('change');
        });
});

function createDocs()
{
	    let reportType = $('#reportType').val();
        let weekStartYear = $('#weekStartYear').val();
        let weekToYear = $('#weekToYear').val();
        let weekStart = $('#weekStart').val();
        let weekTo = $('#weekTo').val();
        let groupBy1 = $('#groupBy1').val();
        let groupBy2 = $('#groupBy2').val();
        let Staff_Numbers = $('#Staff_Numbers').val();
        let schedulingTeam = $('#schedulingTeam').val();
        let estabCodes = $('#estabCodes').val();
        if (schedulingTeam != null && typeof schedulingTeam !== "undefined") {
            schedulingTeam = $('#schedulingTeam').val().toString();
        }
        if (Staff_Numbers != null && typeof Staff_Numbers !== "undefined")        {
            Staff_Numbers = $('#Staff_Numbers').val().toString();
        }
        if (estabCodes != null && typeof estabCodes !== "undefined")        {
            estabCodes = $('#estabCodes').val().toString();
        }
        let WTDbreechType = $('#BreachType').val();
        let BreachStatus = $('#BreachStatus').val();
        let wtdstartDate = $('#wtdstartDate').val();
        let wtdendDate = $('#wtdendDate').val();

        if (weekStartYear!=false && schedulingTeam!=false && schedulingTeam != null && typeof schedulingTeam !== "undefined") {
            location='page-includes/admin/charging/wtdSummaryExport.php?reportType='+reportType+'&weekStartYear='+weekStartYear+'&weekToYear='+weekToYear+'&groupBy1='+groupBy1+'&groupBy2='+groupBy2+'&Staff_Numbers='+Staff_Numbers+'&schedulingTeam='+schedulingTeam+'&estabCodes='+estabCodes+'&weekStart='+weekStart+'&weekTo='+weekTo+'&WTDbreechType='+WTDbreechType+'&BreachStatus='+BreachStatus+'&wtdstartDate='+wtdstartDate+'&wtdendDate='+wtdendDate+'&orderBy='+btoa(table.order());
        } else {
            customAlertByModel("Check selected Schedulling Team & leave Year, They are not set.Try again.");
        }
}
</script>