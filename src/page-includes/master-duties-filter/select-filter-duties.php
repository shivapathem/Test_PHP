<?php
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../function-includes/masterduty_filter_functions.php';
include_once '../../function-includes/masterduty_functions.php';

    //get all active master duties
    $intAreaID = $_SESSION['user']['AreaID'];
    $intFilterId = $_REQUEST['FilterID'];
    $FilterValue = 'new';//$_REQUEST['FilterValue'];
    $intDutyTypeID = 1;
    $strSearchText = '';
    $intArchived = 1;
    if($FilterValue == 'new'){
        $rsDutiesJson = ListAllMasterDutiesForRota($intAreaID, $intDutyTypeID, $intArchived, $strSearchText, $strTeams);
        $rsDuties = json_decode($rsDutiesJson,true);
    }else{
        $rsSelectFilterDutiesFJson = GetAllMasterDutiesForFilter($intFilterId);
        $rsDuties = json_decode($rsSelectFilterDutiesFJson,true);
    }
?>


<script src="js/masterDutiesFilter.js?v=<?php echo time(); ?>"></script>


<section class="duties-selection">
        <fieldset>
            <h2 style="position: absolute; margin-top: -18px; background: #fff;">Duties Selection for filter: All Londan BST</h2>
            <div class="filtSection" style="margin-top: 10px;">
                <div class="filtSection">
                    <div class="filtLeft" id="leftbox">
                        <h3 id="available-master-duties" style="position: absolute; margin: -4px 15px; background: #fff;">Available Master Duties</h3>
                            <fieldset aria-labelledby="available-master-duties" style="padding: 15px;margin-bottom: 15px; margin-top: 5px;">
                            <div class="outerDiv">
                                <div id="innerDiv">
                                  <select id="multiSelect1" multiple="multiple" class="js_masterduties">
                                        <?php foreach($rsDuties  as $dutyData) {?>
                                            <option value=<?php echo $dutyData['id'] ?>><?php echo $dutyData['DutyName'] ?></option>
                                        <?php } ?>
                                  </select>
                                </div>
                              </div>
                        </fieldset>
                        <h3 id="available-misc-duties" style="position: absolute; margin: -10px 15px; background: #fff;">Available Misc Duties</h3>
                        <fieldset aria-labelledby="available-misc-duties" style="padding: 15px;">
                            <div class="outerDiv">
                                <div id="innerDiv">
                                  <select id="multiSelect2" multiple="multiple" id='lstBox1'>
                                    <option value="miscdutiescp">1 Clipboard</option>
                                    <option value="miscdutiescp1">2 Clipboard</option>
                                    <option value="miscdutiescp2">D01 Early Shieldleader</option>
                                    <option value="miscdutiescp3">D01 Early Shieldleader</option>
                                    <option value="miscdutiescp4">1 Clipboard</option>
                                    <option value="miscdutiescp5">2 Clipboard</option>
                                  </select>
                                </div>
                              </div>
                        </fieldset>
                    </div>
                    <div class="filtLeft">
                    <div class="filterLeft" id="middlebox">
                        <div class="Buttons">
                        <div class="fields">
                            <button id='btnAllRight'>
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>
                        <div class="fields">
                            <button id='btnRight'>
                                <i class="fal fa-angle-right"></i>
                            </button>
                        </div>
                        </div>
                        <div class="Buttons">
                        <div class="fields">
                            <button id='btnLeft'>
                                <i class="fal fa-angle-left"></i>
                            </button>
                        </div>
                        <div class="fields">
                            <button id='btnAllLeft'>
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
                                  <select id="multiSelect3" multiple="multiple">
                                    <option value="1cpboard">1 Clipboard</option>
                                    <option value="2cpboard">2 Clipboard</option>
                                    <option value="d01early">D01 Early Shieldleader</option>
                                  </select>
                                </div>
                              </div>
                        </fieldset>
                    </div>
                    </div>
                </div>
            </fieldset>
        </section>