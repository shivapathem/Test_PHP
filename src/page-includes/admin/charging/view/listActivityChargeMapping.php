<link href="../styles/charging/charging.css" rel="stylesheet">
<div id="content-activity-mapping">
    <div class="headertextallpages  main-heading"><h1 style="text-align: center;">Charge Code-Activity Code Mapping</h1>
        <button class="btn-class addbtn m-30" id="js_addnewbutton" onClick="addEditMapping();">
            <img src="./images/button_add.png" alt="Add Button">&nbsp;Add New
        </button>
    </div>
    <div id="activity_mapping" class="chargeRow">
        <div class="filterContainerFlex chargeCol7">
            <span class="heading">Filter</span>
            <div class="chargeRow">
                <div class="chargeCol chargeCol5">
                    <form id="activityChargeMappingFilter" name="activityChargeMappingFilter">
                        <div class="fields">
                            <label for="mappingFrom">Year (From - To)</label>
                            <select id="mappingFrom" name="mappingFrom" onChange="mappingTo.value = mappingFrom.value;">
                                <?php
                                $yearOptionContainerFrom = '';
                                $yearOptionContainerTo = '';
                                $currentFinancialYr = (date("m") > 3) ? date("Y") : date("Y") - 1;
                                $selectedYearFrom = $requestData['mappingFrom'] ?? $currentFinancialYr;
                                $selectedYearTo = $requestData['mappingTo'] ?? $currentFinancialYr;
                                for ($yearCounter = 1995; $yearCounter < 2051; $yearCounter++) {
                                    if ($yearCounter == $selectedYearFrom) {
                                        $yearOptionContainerFrom .= '<option value="' . $yearCounter . '" title="' . $yearCounter . '" selected="selected">' . $yearCounter . '</option>';
                                    } else {
                                        $yearOptionContainerFrom .= '<option value="' . $yearCounter . '" title="' . $yearCounter . '">' . $yearCounter . '</option>';
                                    }
                                    if ($yearCounter == $selectedYearTo) {
                                        $yearOptionContainerTo .= '<option value="' . $yearCounter . '" title="' . $yearCounter . '" selected="selected">' . $yearCounter . '</option>';
                                    } else {
                                        $yearOptionContainerTo .= '<option value="' . $yearCounter . '" title="' . $yearCounter . '">' . $yearCounter . '</option>';
                                    }
                                }
                                echo $yearOptionContainerFrom;
                                ?>
                            </select>
                            <span class="barYear">--</span>
                            <select id="mappingTo" name="mappingTo">
                                <?php echo $yearOptionContainerTo; ?>
                            </select>
                        </div>
                        <?php
                            $requestData['searchSelect'] = $requestData['searchSelect'] ?? '';
                        ?>
                        <input type="text" name="searchSelect" placeholder="Search Scheduling Teams" id="searchSelect"
                               style="margin-left: 100px;position: relative;top: 10px;width:65%;"
                               value="<?php echo $requestData['searchSelect']; ?>">
                        <div class="fields" style="display:flex;">
                            <label for="schedulingTeams" style="margin-top:0px;">Scheduling Teams</label>
                            <select id="activityCodeList" name="activityCodeList[]" multiple
                                    onChange="activityCodeListContainer.value = $('#activityCodeList').val(); getChargeCodeUI();">
                                <option value="" title="All Available Teams">All Available Teams</option>
                                <?php
                                $teamOptionContainer = '';
                                $chargeOptionContainer = [];
                                $chargeCodeContainer = array();
                                $requestData['chargeCodeContainer'] = $requestData['chargeCodeContainer'] ?? '';
                                $chargeCodeRequestArr = explode(',', (string) ($requestData['chargeCodeContainer'] ?? ''));
                                $activityCodeListContainerArr = explode(',', (string) ($requestData['activityCodeListContainer'] ?? ''));
                                $defauldChargeCodeArr = array();
                                foreach ($data[0] as $dataVal) {
                                    $selectedActStr = '';
                                    if (in_array($dataVal['schedulingTeamId'], $activityCodeListContainerArr)) {
                                        $selectedActStr = 'selected="selected"';
                                    }
                                    $teamOptionContainer .= '<option value="' . $dataVal['schedulingTeamId'] . '" title="' . $dataVal['schedulingTeamName'] . '" ' . $selectedActStr . ' >' . $dataVal['schedulingTeamName'] . '</option>';
                                    if (!empty($dataVal['EstablishCode'])) {
                                        $selectedEstStr = '';
                                        $defauldChargeCodeArr[] = $dataVal['EstablishCode'];
                                        if (($chargeCodeRequestArr[0] != "0") && in_array($dataVal['EstablishCode'], $chargeCodeRequestArr)) {
                                            $selectedEstStr = 'selected="selected"';
                                        }
                                        $chargeOptionContainer[] = '<option value="' . $dataVal['EstablishCode'] . '" title="' . $dataVal['EstablishCode'] . '" ' . $selectedEstStr . ' >' . $dataVal['EstablishCode'] . '</option>';
                                    }
                                }
                                echo $teamOptionContainer;
								asort($chargeOptionContainer);
                                ?>
                            </select>
                        </div>
                        <?php
                            $requestData['chargeCodeContainer'] = $requestData['chargeCodeContainer'] ?? null;
                            $requestData['activityCodeListContainer'] = $requestData['activityCodeListContainer'] ?? null;
                            $requestData['selectRowId'] = $requestData['selectRowId'] ?? null;
                        ?>
                        <input type="hidden" id="conrollerName" name="conrollerName" value="listActivityChargeMapping">
                        <input type="hidden" id="chargeCodeContainer" name="chargeCodeContainer"
                               value="<?php echo $requestData['chargeCodeContainer']; ?>">
                        <input type="hidden" id="activityCodeListContainer" name="activityCodeListContainer"
                               value="<?php echo $requestData['activityCodeListContainer']; ?>">
                        <input type="hidden" id="selectRowId" name="selectRowId"
                               value="<?php echo $requestData['selectRowId']; ?>">
                </div>
                <div class="chargeCol chargeCol5">
                    <div class="fields" style="display:flex;">
                        <label for="chargeCode">Charge Code</label>
                        <select id="chargeCode" name="chargeCode"
                                onChange="chargeCodeContainer.value=$('#chargeCode').val();" multiple>
                            <option value="0" title="All Charge Codes"
                                    onClick="chargeCodeContainer.value = $('#chargeCode option').map(function() {if($(this).val() != ''){return $(this).val();}}).get().join(',');" <?php if ($chargeCodeRequestArr[0] == "0" || $chargeCodeRequestArr[0] == "") {
                                echo 'selected="selected"';
                            } ?> >All Charge Codes
                            </option>
                            <?php echo implode("\n", $chargeOptionContainer); ?>
                        </select>
                    </div>
                </div>
                <div class="chargeCol chargeCol2 mappingBtnBlock">
                    <?php 
                        $requestData['mappingFrom'] = $requestData['mappingFrom'] ?? '';
                        $requestData['mappingTo'] = $requestData['mappingTo'] ?? '';
                    ?>
                    <button type="text" class="charging-btn" onClick="applyFilter();">Refresh</button>
                    <button type="text" class="charging-btn"
                            onClick="expotData('<?php echo base64_encode($requestData['mappingFrom']); ?>', '<?php echo base64_encode($requestData['mappingTo']); ?>', '<?php if (!empty($requestData['chargeCodeContainer'])) {
                                echo base64_encode($requestData['chargeCodeContainer']);
                            } else {
                                echo base64_encode(implode(',', $defauldChargeCodeArr));
                            } ?>');">
                        <img src="../images/excel.svg" class="exportIcon" alt="Export">Export
                    </button>
                    <button type="text" class="charging-btn" onClick="copyRecords()">Copy Records</button>
                </div>
                </form>
            </div>
        </div>
        <div class="chargeCol4 infoBox">
            <p>Use <strong>Shift & Ctrl</strong> keys to multiselect the teams and charge codes.</p>
            <p>You may click on the column headings to sort by the column. The spreadsheet will be produced with the
                selected order.</p>
            <p>Select the records you want to copy to another year and then click on Copy Records</p>
        </div>
        <div class="chargeRow fullWidth">
            <div class="chargeCol chargeCol12">
                <div class="allSelectAction charging-btn">
                    <label class="checkBoxlabel">
                        <input type="checkbox" id="mapCheckAll" class="checkBoxInput" value=""/> Tick/Untick All</label>
                </div>
            </div>
            <div class="chargeCol chargeCol12">
                <div class="tables mappingEntriesContainer">
                    <table id="mappingEntries" class="oddevenclass tablesmall" style="width:100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Financial Year</th>
                            <th>Effective From</th>
                            <th>Charge Code</th>
                            <th>Charge Code Desc</th>
                            <th>Activity Code</th>
                            <th>Activity Code Desc</th>
                            <th>Price</th>
                            <th> Select To Copy</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $mappingIdContainerArr = array();
                        foreach ($data[1] as $getMapping_d) {
                            $mappingIdContainerArr[] = $getMapping_d['MappingId'];
                            ?>
                            <tr class="activity_mapping-context-menu <?php if ($requestData['selectRowId'] == $getMapping_d['MappingId']) {
                                echo 'highlightOrange';
                            } ?>" MappingId="<?php echo $getMapping_d['MappingId']; ?>"
                                Year="<?php echo $getMapping_d['Year']; ?>"
                                EstablishCodeId="<?php echo $getMapping_d['EstablishCodeId']; ?>"
                                ActiveCodeId="<?php echo $getMapping_d['ActiveCodeId'] ?>"
                                Price="<?php echo number_format($getMapping_d['Price'], 2, '.', ''); ?>"
                                id="<?php echo $getMapping_d['MappingId']; ?>"
                                onMouseDown="highlightMappingRow(this); selectRowId.value = this.id">
                                <td><?php echo $getMapping_d['MappingId']; ?></td>
                                <td><?php echo $getMapping_d['Year']; ?></td>
                                <td><?php echo $getMapping_d['EffectiveFrom']; ?></td>
                                <td><?php echo $getMapping_d['EstablishCode']; ?></td>
                                <td title="<?php echo $getMapping_d['EstablishCodeDescription']; ?>"><?php echo substr($getMapping_d['EstablishCodeDescription'], 0, 20); ?></td>
                                <td><?php echo $getMapping_d['ActivityCodeName']; ?></td>
                                <td title="<?php echo $getMapping_d['Description']; ?>"><?php echo substr($getMapping_d['Description'], 0, 20); ?></td>
                                <td>£<?php echo number_format($getMapping_d['Price'], 2, '.', ''); ?></td>
                                <td>
                                    <input type="checkbox" name="mappingIdContainer[]"
                                           id="mappingIdContainer-<?php echo $getMapping_d['MappingId']; ?>"/>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    <input type="hidden" name='mappingIdContainerJson' id="mappingIdContainerJson"
                           value="[<?php echo implode(',', $mappingIdContainerArr); ?>]">
                </div>
            </div>
        </div>
    </div>
    <form id="getChargeCodeOptions" name="getChargeCodeOptions">
        <input type="hidden" id="teamContainer" name="teamContainer">
        <input type="hidden" id="chargeCodeReqStr" name="chargeCodeReqStr"
               value="<?php echo $requestData['chargeCodeContainer']; ?>">
        <input type="hidden" id="conrollerName" name="conrollerName" value="getChargeCodeOptions">
    </form>

</div>
<script>
    <?php
    if (!empty($requestData['activityCodeListContainer'])) {
        echo "getChargeCodeUI();";
    }
    ?>
    var activityListJson =    <?php echo json_encode($data[0]); ?>;
    <?php if(!empty($requestData['activityCodeListContainer'])){ ?>
    if (document.getElementById('activityCodeListContainer').value != '') {
        searchSelect();
    }
    <?php } ?>
    $(document).ready(function () {

        $("#schedulingTeams").chosen({
            no_results_text: "Oops, nothing found!",
            width: "100%"
        });
        $(".mappingEntriesContainer").height($(window).height() - 412);
        var table = $('#mappingEntries').DataTable({
            lengthChange: false,
            paging: false,
            info: false,
            scrollY: parseInt($(".mappingEntriesContainer").height() - 23),
            filter: false,
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }]

        });
        // Function to select all entries in tha table
        $("#mapCheckAll").click(function () { // Check all id
            var rows, checked;
            rows = $('#mappingEntries').find('tbody tr');
            checked = $(this).prop('checked');
            $.each(rows, function () {
                var checkbox = $($(this).find('td').eq(8)).find('input').prop('checked', checked); // check box column "8"
            });
        });

        //Context menu configuration
        $(function () {
            $.contextMenu({
                selector: '.activity_mapping-context-menu',
                items: {
                    "Add": {
                        name: "New Record",
                        icon: "add",
                        callback: function (key, options) {
                            addEditMapping();
                        }
                    },
                    "Edit": {
                        name: "Edit Record",
                        icon: "edit",
                        callback: function (key, options) {
                            let mappingid = options.$trigger.attr("mappingid");
                            let year = options.$trigger.attr("year");
                            let establishcodeid = options.$trigger.attr("establishcodeid");
                            let activecodeid = options.$trigger.attr("activecodeid");
                            let price = options.$trigger.attr("price");
                            addEditMapping(mappingid, year, establishcodeid, activecodeid, price);
                        }
                    },
                    "Delete": {
                        name: "Delete Record",
                        icon: "delete",
                        callback: function (key, options) {
                            let mappingid = options.$trigger.attr("mappingid");
                            customConfirm('Are you sure you wish to delete this entry?', function () {
                                    deleteMapping(mappingid);
                                },
                                function () {
                                }
                            );
                        }
                    }
                }
            });
        });
        $('#searchSelect').keyup(function () {
            searchSelect();
        });
    });

    function searchSelect() {
        let rxp = new RegExp($('#searchSelect').val(), 'i');
        $('#activityCodeList').empty();
        let activityCodeList = $('#activityCodeList');
        let activityCodeListContainer = $('#activityCodeListContainer').val() ? ',' + $('#activityCodeListContainer').val() + ',' : '';
        let loopCounter = 0;
        activityListJson.forEach(function (data, index) {
            if (loopCounter == 0) {
                activityCodeList.append($('<option/>').attr('value', '').text('All Available Teams').attr('title', 'All Available Teams'));
            }
            if (rxp.test(data['schedulingTeamName'])) {
                if (activityCodeListContainer.indexOf(',' + data['schedulingTeamId'] + ",") > -1) {
                    activityCodeList.append($('<option/>').attr('value', data['schedulingTeamId']).text(data['schedulingTeamName']).attr('title', data['schedulingTeamName']).attr('selected', 'selected'));
                } else {
                    activityCodeList.append($('<option/>').attr('value', data['schedulingTeamId']).text(data['schedulingTeamName']).attr('title', data['schedulingTeamName']));
                }

            } else {
                activityCodeList.append($('<option/>').attr('value', data['schedulingTeamId']).text(data['schedulingTeamName']).attr('title', data['schedulingTeamName']).addClass("hidden"));
            }
            loopCounter++;
        });
    }

    function getChargeCodeUI() {
        document.getElementById('teamContainer').value = $('#activityCodeList').val();
        $.ajax({
            type: 'POST',
            url: 'page-includes/admin/charging/index.php',
            data: $('#getChargeCodeOptions').serialize(),
            success: function (returnHtml) {
                document.getElementById('chargeCode').innerHTML = returnHtml;
                if ($('#chargeCode').val() == "0") {
                    document.getElementById('chargeCodeContainer').value = $('#chargeCode option').map(function () {
                        if ($(this).val() != '') {
                            return $(this).val();
                        }
                    }).get().join(',');
                }
            }
        });
    }

    function chargingCallbackFunc(data) {
        if ($($.parseHTML(data)).filter("#content-activity-mapping").length) {
            $('#content').html(data);
        } else {
            customAlert(data);
        }
    }

    function applyFilter() {
        let mappingFrom = document.getElementById('mappingFrom').value;
        let mappingTo = document.getElementById('mappingTo').value;
        if (mappingFrom > mappingTo) {
            customAlert('Please ensure the Start Year is NOT GREATER than the Finish Year !!');
        } else {
            formHandler('activityChargeMappingFilter');
        }
    }

    function addEditMapping(mappingid = 0, year = 0, establishcodeid = 0, activecodeid = 0, price = 0) {
        $.ajax({
            type: 'POST',
            url: 'page-includes/admin/charging/index.php',
            data: {
                conrollerName: 'addEditMapping',
                mappingid: mappingid,
                year: year,
                establishcodeid: establishcodeid,
                activecodeid: activecodeid,
                price: price
            },
            success: function (returnHtml) {
                $.facebox(returnHtml);
            }
        });
    }

    function deleteMapping(mappingId) {
        $.ajax({
            type: 'POST',
            url: 'page-includes/admin/charging/index.php',
            data: {mappingId: mappingId, "conrollerName": "addEditMappingSave", "actionType": "DELETE"},
            success: function (returnHtml) {
                returnHtml = JSON.parse(returnHtml);
                if (returnHtml.Status == 1) {
                    $.facebox.close();
                    applyFilter();
                    return true;
                } else {
                    customAlertByModel(returnHtml.StatusCode);
                    return false;
                }
            }
        });
    }

    function highlightMappingRow(thisVal) {
        $('.activity_mapping-context-menu').removeClass('highlightOrange');
        $(thisVal).addClass('highlightOrange');
    }

    function copyRecords() {
        let mappingIdContainerArr = JSON.parse(document.getElementById('mappingIdContainerJson').value);
        let checkedMappingIdArr = [];
        let checkedYeardArr = [];
        let checkedYeardUni = [];
        let copyYearHidden = $.cookie('copyYearHidden');
        for (let i = 0; i < mappingIdContainerArr.length; i++) {
            if (document.getElementById('mappingIdContainer-' + mappingIdContainerArr[i]).checked) {
                checkedMappingIdArr.push(mappingIdContainerArr[i]);
                checkedYeardArr.push($("#" + mappingIdContainerArr[i]).attr("year"));
            }
        }
        checkedYeardUni = [...new Set(checkedYeardArr)];
        if (checkedMappingIdArr.length == 0) {
            customAlert("You have not selected any records to copy.<br/>Please use the checkbox to the right of each record to select it.");
            return false;
        }
        if (checkedYeardUni.length > 1) {
            customAlert("You can copy records from one year to another (not from multiple years).<br/>Please set the drop-down lists to the same year and Refresh the form.");
            return false;
        }
        customConfirm('You are about to copy the selected record to a different Financial year.<br/>Are you sure you wish to continue?',
            function () {
                $.ajax({
                    type: 'POST',
                    url: 'page-includes/admin/charging/index.php',
                    data: {
                        conrollerName: 'getYearForCopyMapping',
                        checkedMappingIdArr: checkedMappingIdArr,
                        'selectedYear': checkedYeardUni[0],
                        copyYearHidden: copyYearHidden
                    },
                    success: function (returnHtml) {
                        setTimeout(function () {
                            $.facebox(returnHtml);
                        }, 1000);
                    }
                });
            },
            function () {
            }
        );
    }

    function expotData(mappingFrom, mappingTo, chargeCodeContainer) {
        let mappingIdContainerJson = document.getElementById('mappingIdContainerJson').value;
        let schedulingTeamTxtArr = [];
        let mappingEntriesTbl = $('#mappingEntries').DataTable();
        $("#activityCodeList option:selected").each(function () {
            var $this = $(this);
            if ($this.length) {
                schedulingTeamTxtArr.push($this.text());
            }
        });
        if (schedulingTeamTxtArr.length == 0) {
            schedulingTeamTxtArr.push('All Available Teams');
        }
        if (mappingIdContainerJson.length > 2) {
            location = 'page-includes/admin/charging/activityChargeMappingExport.php?mappingFrom=' + mappingFrom + '&mappingTo=' + mappingTo + '&chargeCodeContainer=' + chargeCodeContainer + '&schedulingTeamTxtArr=' + btoa(schedulingTeamTxtArr) + '&orderBy=' + btoa(mappingEntriesTbl.order());
        } else {
            customAlert('There are no records to Export. Please select different year/scheduling teams.');
        }
        return false;
    }
</script>
