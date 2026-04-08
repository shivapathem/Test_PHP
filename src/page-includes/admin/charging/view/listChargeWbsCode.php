<?php
global $errorContainer;
include_once $_SERVER['DOCUMENT_ROOT'].'/class-includes/userRolePermissions.php';
if ($requestData['codetype'] == 1) {
    $firstColName = 'WBS';
    $dataTableId = 'wbsCodeEntries';
    $mainHeading = 'WBS Codes';
    $containerClass = 'wbsContainer';
    $contextMenu = 'receiverCode-context-menu';
    $pageId = 18;
} else {
    $firstColName = 'Charge Code';
    $dataTableId = 'receiverCodeEntries';
    $mainHeading = 'Charge Codes';
    $containerClass = 'chargingContainer';
    $contextMenu = 'receiverCode-context-menu';
    $pageId = 17;
}

// Call User Permission function.
$permissions = getUserRolePermissions($pageId);
?>
<script src="js/insUpdateChargeWbsCode.js?v=<?php echo time(); ?>"></script>
<script src="js/ListingChargeWbsCode.js?v=<?php echo time(); ?>"></script>
<link href="../styles/charging/charging.css?v=<?php echo time(); ?>" rel="stylesheet">
<div id="content-chargecode">
    <div class="headertextallpages  main-heading"><h1 style="text-align: center;"><?php echo $mainHeading; ?></h1>
        <?php if ($permissions->cancreate == 1) { ?>
        <button class="btn-class addbtn m-30" id="js_addnewbutton">
            <img src="./images/button_add.png" alt="Add Button">&nbsp;Add New
        </button>
        <?php } ?>
        <input type="hidden" id="js_codetype" name="js_codetype" value="<?php echo $requestData['codetype']; ?>"/>

    </div>
    <div class="middleBlock">
        <div id="receiverCode">
            <div class="chargeCol chargeCol12">
                <div class="<?php echo $containerClass; ?>">
                    <table id="<?php echo $dataTableId; ?>" class="oddevenclass chargingWBSTable tablesmall"
                           style="width:100%">
                        <thead>
                        <tr>
                            <th class="width30Percent"><?php echo $firstColName; ?></th>
                            <th class="width40Percent">Description</th>
                            <th class="width20Percent">Area</th>
                            <th class="width10Percent">Active</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (count($data) > 0) {
                        foreach ($data as $dataVal) {

                            ?>
                            <tr id="<?php echo $dataVal['ChargeWbsCodeId']; ?>"
                                ChargeWbsCodeId="<?php echo $dataVal['ChargeWbsCodeId']; ?>"
                                ChargeWbsCodeName="<?php echo $dataVal['ChargeWbsCodeName']; ?>"
                                Description="<?php echo $dataVal['Description']; ?>"
                                DivisionId="<?php echo $dataVal['DivisionId']; ?>"
                                DivisionName="<?php echo $dataVal['DivisionName']; ?>"
                                class="<?php echo $contextMenu; ?> chargeWbsDetails unHighlightOrange" onmousedown="highlightTableRow(this)">
                                <td><?php echo $dataVal['ChargeWbsCodeName']; ?></td>
                                <td><?php echo $dataVal['Description']; ?></td>
                                <td><?php echo '&emsp;'.$dataVal['DivisionName']; ?></td>
                                <td><span class='activehidestatus' id='activehidestatus_<?php echo $dataVal['ChargeWbsCodeId']; ?>'><?php echo $dataVal['IsActive']; ?></span>
                                    <img src="./images/<?php echo $dataVal['IsActive'] ? 'green_tick.png' : 'red_cross.png'; ?>"
                                         data-activecodeid="<?php echo $dataVal['ChargeWbsCodeId']; ?>"
                                         class="activeTick js_isActiveCode_<?php echo $dataVal['ChargeWbsCodeId']; ?>"
                                         id='js_activeCode' alt="Tick" onclick="startsupdate('<?php echo $dataVal['ChargeWbsCodeId']; ?>');"></td>
                            </tr>
                            <?php
                            }
                        } else { ?>
                            <tr><td></td><td id='ChargeWBSTableNoDataFound'>No Record Found.</td><td></td><td></td></tr>
                        <?php
                        } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(".division-seclect").chosen({
        no_results_text: "Oops, nothing found!",
        width: "200px"
    });

    function chargingCallbackFunc(data) {
        if ($($.parseHTML(data)).filter("#content-chargecode").length) {
            $('#content').html(data);
        } else {
            customAlertByModel(data);
        }
    }
</script>
</body>
