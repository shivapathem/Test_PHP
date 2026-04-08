<?php 
 $addnew .='<h2 style="position: absolute; margin-top: -18px; background: #fff;">Duties Selection for filter: All Londan BST</h2>
                <div class="filtSection" style="margin-top: 10px;">
                    <div class="filtLeft" id="leftbox">
                        <h3 id="available-master-duties" style="position: absolute; margin: -4px 15px; background: #fff;">Available Master Duties</h3>
                            <fieldset aria-labelledby="available-master-duties" style="padding: 15px;margin-bottom: 15px; margin-top: 5px;">
                            <div class="outerDiv">
                                <div id="innerDiv">
                                  <select id="multiSelect1" multiple="multiple" class="js_masterduties">';
                                         foreach($rsAvailableDutiesJson  as $dutyData) {
                                            if($dutyData['DutyTypeID'] == 1){    
                                        
                                            $addnew.=' <option value='.  $dutyData['MasterDutyID'] .'>'.  $dutyData['DutyName'] .'</option>';
                                          }
                                        } 
                                        
                                $addnew.=   ' </select>
                                </div>
                            </div>
                        </fieldset>
                        <h3 id="available-misc-duties" style="position: absolute; margin: -10px 15px; background: #fff;">Available Misc Duties</h3>
                        <fieldset aria-labelledby="available-misc-duties" style="padding: 15px;">
                            <div class="outerDiv">
                                <div id="innerDiv">
                                    <select id="multiSelect2" multiple="multiple" id="lstBox1">';
                                    foreach($rsAvailableDutiesJson  as $dutyData) {
                                            if($dutyData['DutyTypeID'] != 1){    
                                        $addnew.=' <option value='.  $dutyData['MasterDutyID'] .'>'.  $dutyData['DutyName'] /'</option>';
                                        }
                                    } 
                                
                            $addnew.='</select>
                                 </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="filtLeft">
                    <div class="filterLeft" id="middlebox">
                        <div class="Buttons">
                        <div class="fields">
                            <button id="btnAllRight">
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>
                        <div class="fields">
                            <button id="btnRight">
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>
                        </div>
                        <div class="Buttons">
                        <div class="fields">
                            <button id="btnLeft">
                                <i class="fal fa-angle-left"></i>
                            </button>
                        </div>
                        <div class="fields">
                            <button id="btnAllLeft">
                                <i class="fal fa-angle-left"></i>
                                <i class="fal fa-angle-left"></i>
                                <i class="fal fa-angle-left"></i>
                            </button>
                        </div>
                        </div>
                        <div class="Buttons movedownBtns">
                        <div class="fields">
                            <button id="miscdutiesMoveall">
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>
                        <div class="fields">
                            <button id="miscduties">
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>
                        </div>
                    </div>
                    </div>
                    <div class="filtLeft">
                    <div class="filterLeft" id="rightbox">
                        <h3 id="current-masters-&-misc-duties-in-filter" style="position: absolute; margin: -10px 15px; background: #fff;">Current Masters & Misc Duties in Filter</h3>
                        <fieldset aria-labelledby="current-masters-&-misc-duties-in-filter" style="padding: 15px;">
                            <div class="outerDiv" id="outerDivright">
                                <div id="innerDiv">
                                  <select id="multiSelect3" multiple="multiple">';
                                    foreach($rsAssignedDutiesJson  as $dutyAssignedData) {
                                        $addnew.=' <option value='. $dutyData['MasterDutyID'].'>'.$dutyData['DutyName'] .'</option>';
                                    } 
                    $addnew.=' </select>
                                </div>
                              </div>
                        </fieldset>
                    </div>
                    </div>
                </div>';
?>