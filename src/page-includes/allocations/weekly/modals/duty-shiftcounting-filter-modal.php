<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$service = new AllocationService();

$getSchedulingTeamDetails = $service->getSchedulingTeamDetails($request);
$getDutyShiftCountingFiltersList = $service->getDutyShiftCountingFilters($request,0,0);

$dUserId = $request->get('userId');
$dTeamId = $request->get('teamId');
$dselectedFilterId = $request->get('selectedFilterId');
?>
<div id="shiftCounting">
    <h1 class="headertextallpages  main-heading"><?php echo $getSchedulingTeamDetails['schedulingTeamName'];?></h1>
    <div class="shiftCounting-container">
    	<div class="fieldcontainer-message" id="error-mess-div">
    		<div class="fieldBox fieldbox-message" id="error-mess"></div>
    	</div>
    	<div class="fieldcontainer-success-message" id="success-mess-div">
    		<div class="fieldBox fieldbox-success" id="success-mess"></div>
    	</div>
        <div class="filterContainer">
            <span class="heading">Existing Pre-Saved Duty Counts Selections Filters</span>
            <div class="fieldBox">
                <select name="modalDutyShiftCountingFilterId" id="modalDutyShiftCountingFilterId" onchange="showFilterDetails(<?php echo $dUserId?>,<?php echo $dTeamId?>,this.value)">
                    <option value="">No Filter/Edit Filter</option>
                    <?php if(!empty($getDutyShiftCountingFiltersList)) { foreach($getDutyShiftCountingFiltersList as $ky => $vl) {?>
                            <option value="<?php echo $vl['ID']?>" <?php if($vl['ID'] == $dselectedFilterId){?> selected="selected" <?php }?>><?php echo $vl['FilterName']?></option>
                        <?php }?>
                    <?php }?>
                </select>
            </div>
            <div class="fieldBox btnBlock">
                <input name="deleteFilter" id="deleteFilter"  class="btn" type="button" value="Delete Filter" onclick="deleteFilter(<?php echo $dUserId?>,<?php echo $dTeamId?>);">
                <input name="publicFilter" id="publicFilter"  class="btn" type="button" value="Set to Public Filter"  onclick="setPublicFilter(<?php echo $dUserId?>,<?php echo $dTeamId?>,'Public');">
                <input name="privateFilter" id="privateFilter"  class="btn" type="button" value="Set to Private Filter" onclick="setPublicFilter(<?php echo $dUserId?>,<?php echo $dTeamId?>,'Private');">
            </div>
            <div class="fieldBox btnBlock right">
                <input name="clearFilters" id="clearFilters"  class="btn" type="button" value="Clear Filters" onclick="clearDutyShiftCountingFilter(<?php echo $dUserId?>,<?php echo $dTeamId?>);">
            </div>
        </div>
        <div class="shiftCountSection">
            <table id="shiftCountTable">
                <tbody>
                    <tr>
                        <th>Details</th>
                        <th class="grid-item">A</th>
                        <th class="grid-item">B</th>
                        <th class="grid-item">C</th>
                        <th class="grid-item">D</th>
                        <th class="grid-item">E</th>
                        <th class="grid-item">F</th>
                        <th class="grid-item">G</th>
                        <th class="grid-item">H</th>
                        <th class="grid-item">I</th>
                        <th class="grid-item">J</th>
                        <th class="grid-item">K</th>
                        <th class="grid-item">L</th>
                        <th class="grid-item">M</th>
                        <th class="grid-item">N</th>
                        <th class="grid-item">O</th>
                        <th class="grid-item">P</th>
                        <th class="grid-item">Q</th>
                        <th class="grid-item">R</th>
                        <th class="grid-item">S</th>
                        <th class="grid-item">T</th>
                        <th class="grid-item">U</th>
                        <th class="grid-item">V</th>
                        <th class="grid-item">W</th>
                        <th class="grid-item">X</th>
                        <th class="grid-item">Y</th>
                        <th class="grid-item">Z</th>
                        <th>Select</th>
                        <th>Select</th>
                    </tr>
                    <tr>
                        <td>
                            <input name="detailsChk" id="detailsChk" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColA" id="ColA" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColB" id="ColB" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColC" id="ColC" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColD" id="ColD" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColE" id="ColE" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColF" id="ColF" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColG" id="ColG" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColH" id="ColH" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColI" id="ColI" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColJ" id="ColJ" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColK" id="ColK" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColL" id="ColL" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColM" id="ColM" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColN" id="ColN" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColO" id="ColO" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColP" id="ColP" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColQ" id="ColQ" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColR" id="ColR" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColS" id="ColS" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColT" id="ColT" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColU" id="ColU" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColV" id="ColV" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColW" id="ColW" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColX" id="ColX" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColY" id="ColY" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="ColZ" id="ColZ" type="checkbox" class="shiftCountCheckBox" />
                        </td>
                        <td>
                            <input name="checkAllShift" id="checkAllShift"  class="btn" type="button" value="All">
                        </td>
                        <td>
                            <input name="unCheckAllShift" id="unCheckAllShift"  class="btn" type="button" value="None">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="bottomFilterContainer">
            <div class="filterContainer filterContainer1">
                <span class="heading">Options</span>
                <div class="fieldBox">
                    <input id="countsTotal" name="countsTotal"  type="checkbox" style="vertical-align: middle;">
                    <label>Show Counts Total</label>
                </div>
            </div>
            <div class="filterContainer filterContainer2">
                <span class="heading">Save & Apply Filter</span>
                <div class="fieldBox">
                    <input id="saveFilter" name="saveFilter"  type="checkbox" style="vertical-align: middle;">
                    <label>Save Filter As</label>
                    <input type="text" name="newFilterName" id="newFilterName" value="" />
                </div>
            </div>
        </div>
        <div class="FilterContainerActionBtn">
            <input name="applyFilter" id="applyFilter"  class="btn" type="button" value="Apply" onclick="applyDutyShiftCountingFilter(<?php echo $dUserId?>,<?php echo $dTeamId?>);">
            <input name="cancelFilter" id="cancelFilter"  class="btn" type="button" value="Cancel" onclick="closeDutyShiftCountingFilter();">
        </div>
    </div>
</div>
<div id="filterIsPublic" style="display: none;"></div>
<div id="filterIsActive" style="display: none;"></div>
<script>
$(document).ready(function(){
	$('#error-mess').html('');
    $('#error-mess-div').hide();

    $('#success-mess').html('');
    $('#success-mess-div').hide();

  	$('#checkAllShift').click(function(){
    	$(".shiftCountCheckBox").attr("checked", true);
  	});
   	$('#unCheckAllShift').click(function(){
    	$(".shiftCountCheckBox").attr("checked", false);
  	});

  	<?php if(strpos($request->get('selectedFilterId'),'[Pub]') !== true){?>
  		$('#publicFilter').hide();
  		$('#privateFilter').show();
	<?php }?>
	<?php if(strpos($request->get('selectedFilterId'),'[Pub]') !== false){?>
		$('#publicFilter').show();
  		$('#privateFilter').hide();
	<?php }?>
	<?php if(($request->get('selectedFilterId') == '') || ($request->get('selectedFilterId') == 'NA')){?>
		$('#publicFilter').hide();
		$('#privateFilter').hide();
	<?php }?>

	<?php if(!empty($request->get('selectedFilterId')) && $request->get('selectedFilterId') != 'NA'){?>
		showFilterDetails(<?php echo $dUserId?>,<?php echo $dTeamId?>,'<?php echo $dselectedFilterId?>');
	<?php }?>
});
</script>
