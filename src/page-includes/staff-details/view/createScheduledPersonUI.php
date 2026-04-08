<?php

class createSchedulePersonUI
{
    public function schedulepersonhtmlcall($useraction, $schedulepersonid, $selectedteamid = '', $selecteduserid = '')
    {

        if ($useraction == "create") {
            $titlesheduleperson = "Add New Scheduled Person Details";
            $hidestars = "";
            $disabledcontrol = "";
            $hidecontrol = "";
            $hidecontrolbutton = "";
            $submitbtntext = "Create Person";
            $hideenddate = "hidden";
        } else if ($useraction == "edit") {
            $titlesheduleperson = "Edit Scheduled Person Details";
            $hidestars = "";
            $disabledcontrol = "";
            $hidecontrol = "";
            $hidecontrolbutton = "";
            $submitbtntext = "Update Person";
            $hideenddate = "";
        } else {
            $titlesheduleperson = "View Scheduled Person Details";
            $hidestars = "hidden";
            $disabledcontrol = "disabled";
            $hidecontrol = "hidden";
            $hidecontrolbutton = "display:none";
            $submitbtntext = "View Person";
            $hideenddate = "";
        }

        if (!$selectedteamid) {
            $selectedteamid = 0;
        }

        echo ' <script src="../../../js/searchStaffDetails.js"></script>';
        echo '<script src="../../../js/scheduleTeamHistory.js"></script>';
        echo '<script src="../../../js/contractHistory.js"></script>';
        echo '<script src="../../../js/createScheduledPerson.js"></script>
            <div class="headertextallpages main-heading scheduledpersonRemoveH">

                <h1 style="text-align: center;" id="span_scheduled_person">' . $titlesheduleperson . '</h1>
                <input id="getpersonid" value="' . $schedulepersonid . '" type="hidden"/>
                <input id="getstaffid" value="" type="hidden"/>
                <input id="oldscheduledPersonID" value="" type="hidden"/>

            </div>
            <div id="searchScheduleperson" class="h-100Perc">
                <div class="Searchcontainer addNew h-100Perc">
                    <div id="schedulePersonTab" class="h-100Perc">
                        <button class="back-button" onclick="goBack(' . $selectedteamid . ',\'' . trim($selecteduserid) . '\')"><i class="fa fa-arrow-left"></i>&nbsp;Go Back To Search Scheduled Person</button>
                        <ul>
                            <li><a href="#tabs-1">Scheduled Person</a></li>
                            <li><a href="#tabs-2">Staff Details</a></li>
                            <li class="schedule-person-team"><a href="#tabs-3">Scheduling Team History</a></li>
                            <li class="contract_history"><a href="#tabs-4">Contract History</a></li>
                        </ul>

                        <!-- Tab -1 -->
                        <div id="tabs-1">
                            <div class="fields stfform Leftfld">
                                <label style="width: 130px;" for="dispFirstName">Display First Name<span class="required" ' . $hidestars . '>*</span></label>
                                <input style="width: 214px;" type="text" placeholder="Enter Text" class="dispNamenput"
                                       id="dispFirstName" name="dispFirstName" minlength="2" maxlength="25" required="required" ' . $disabledcontrol . '>
                                       <label style="width: 130px;" for="displastName">Display Last Name<span class="required" ' . $hidestars . '>*</span></label>
                                       <input style="width: 214px;" type="text" placeholder="Enter Text" class="displastNamenput"
                                              id="dispLastName" name="displastName" minlength="2" maxlength="25" required="required" ' . $disabledcontrol . '>
                                <input name="useractiontype" id="useractiontype" value="' . $useraction . '" type="hidden" />
                            </div>
                            <div class="fields savePersonBTN ">
                                <button id="saveSchedulePerson" class="btn-class btn-style btn-staff" style="' . $hidecontrolbutton . '">' . $submitbtntext . '
                                </button>
                                <input id="newpersonid" type="hidden"/>
                                <input id="selectedteamid" type="hidden" value="' . $selectedteamid . '" />
                            </div>
                            <div class="hometeam ht schprsn">
                                <div class="float-container">
                                    <div class="float-child schTeamDetails">
                                        <h2>Home Scheduling Team</h2>
                                        <div class="fields">
                                            <label style="width: 110px;" for="hometeam">Team Name<span class="required" ' . $hidestars . '>*</span></label>
                                            <select name="hometeam" id="hometeam" required="required" ' . $disabledcontrol . '></select>
                                            <input name="hometeamhidden" id="hometeamhidden" type="hidden" />
                                            <input value="-1" name="schedulingTeamHistoryHomeTeamFilterFlag" id="schedulingTeamHistoryHomeTeamFilterFlag" type="hidden" />
                                        </div>
                                        <div class="fields">
                                            <label style="width: 110px;" for="startdate">Start Date<span class="required" ' . $hidestars . '>*</span></label>
                                            <input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="startdate"
                                                    name="startdate" required="required" ' . $disabledcontrol . '>
                                            <input type="hidden" autocomplete="off" placeholder="dd-mm-yyyy" id="homestartdateHide"
                                                    name="homestartdateHide" required="required" >
                                            <input type="hidden" autocomplete="off" placeholder="dd-mm-yyyy" id="homestartdateHideNewTeam"
                                                    name="homestartdateHideNewTeam" required="required" >
											<input type="hidden" id="SPTeamID" name="SPTeamID" value="0" >
                                        </div>
                                        <div class="fields" id="homeenddatediv" ' . $hideenddate . '>
                                            <label style="width: 110px;" for="edndate">End Date</label>
                                            <input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="enddate"
                                                   name="enddate" class="hasDatepicker" ' . $disabledcontrol . '>
                                        </div>
                                        <div class="fields">
                                            <label style="width: 110px;" for="sortcode">Sort Code</label>
                                            <input type="text" placeholder="Enter Text" id="sortcode" name="sortcode"
                                                   maxlength="20" ' . $disabledcontrol . '>
                                        </div>
                                        <div class="fields">
                                            <input type="checkbox" name="defaultbgcolour" id="defaultbgcolour" value="1" style="width: 15px; vertical-align: middle;" ' . $disabledcontrol . '>
                                            <label style="width: 170px; vertical-align: middle;" for="sortcode">Default Background Colour</label>

                                        </div>
										<div class="fields">
                                            <input type="checkbox" name="additionalleave" id="additionalleave" value="1" style="width: 15px; vertical-align: middle;" ' . $disabledcontrol . '>
                                            <label style="width: 170px; vertical-align: middle;" for="sortcode">Additional Leave</label>

                                        </div>
                                        <div class="rowsbtns colorChoose">
                                            <div class="fields-color">
                                                <input id="hometeambackcolour" style="width:50px" type="color"
                                                       class="btn-class-color btn-staff-color" ' . $disabledcontrol . '>
                                                <label style="width: 110px;" for="sortcode">Background Colour</label>
                                            </div>
                                        </div>
                                        <div class="rowsbtns colorChoose">
                                            <div class="fields-color">
                                                <input id="hometeamfontcolour" type="color"
                                                       class="btn-class-color btn-staff-color" ' . $disabledcontrol . '>
                                                <label style="width: 110px;" for="sortcode">Font Colour</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="float-child right additionalTeam">
                                        <h2 style="margin-bottom: 9px;">Additional Scheduling Teams</h2>
                                        <input class="btn-class btn-staff globalbutton ' . $hidecontrol . ' addTeamButtonBorder" type="button" id="myBtn"
                                               onclick="openModalPopup(null, null,\'myBtn\')" value="Add New Team">
                                        <div class="dispNameAddTeam">
                                            <div id="scrollable-additionalteam">
                                                <table id="additionalteamgrid" class="tables tableduplicate"></table>
                                                <input id="addteamhiddenarray" type="hidden"/>
                                            </div>
                                            <div>
                                            </div>

                                            <!-- Trigger/Open The Modal -- Modal for add new team -->
                                            <div id="myModal" class="modal">
                                                <!-- Modal content -->
                                                <div class="modal-content"><span class="closebox">×</span>
                                                    <!--Model content-->
                                                    <div>
                                                        <div class="greyborderpopup">Additional Teams</div>
                                                        <span id= "teamerrorerror">You do not have permissions to Edit this team.</span>
                                                        <table id="rotanewedittable" class="smalltable bluetable" width="100%">
                                                            <tbody>
                                                            <tr>
                                                                <td class="lightblue"><label for="PopupTeam">Team Name<span
                                                                                id="Addlabelteam" class="required"
                                                                                 ' . $hidestars . '>*</span>
                                                                    </label></td>
                                                                <td colspan="3">
                                                                    <select class="ddlTeams chosen-selectMaxWidth" name="ddlTeams" id="ddlTeams"
                                                                            required="required" ' . $disabledcontrol . '>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="lightblue"><label>Start Date
                                                                        <span id="Addlabelstartdate" class="required"  ' . $hidestars . '>*</span></label>
                                                                </td>
                                                                <td colspan="3">
                                                                    <input type="text" autocomplete="off"
                                                                           placeholder="dd-mm-yyyy" id="addteamstartdate"
                                                                           name="addteamstartdate" required="required"
                                                                           ' . $disabledcontrol . '>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="lightblue"><label>End Date </label></td>
                                                                <td colspan="3">
                                                                    <input type="text" autocomplete="off"
                                                                           placeholder="dd-mm-yyyy" id="addteamenddate"
                                                                           name="addteamenddate" required="required"
                                                                           maxlength="20" ' . $disabledcontrol . '>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="lightblue" width="15%"><label for="sortcode">Sort
                                                                        Code</label></td>
                                                                <td width="35%">
                                                                    <input id="addteamsortcode" name="addteamsortcode"
                                                                           type="text" maxlength="20" size="15" ' . $disabledcontrol . '>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Default Background Colour</td>
                                                                <td>
                                                                    <div class="rowsbtns">
                                                                        <div class="fields-color">
                                                                            <input id="addteamdefaultbgcolour"
                                                                                   name="addteamdefaultbgcolour" style="width:50px"
                                                                                   type="checkbox" ' . $disabledcontrol . '>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
						                                    <tr>
                                                                <td>Background Colour</td>
                                                                <td>
                                                                    <div class="rowsbtns">
                                                                        <div class="fields-color">
                                                                            <input id="addteamBackcolor" name="addteamBackcolor" style="width:50px"
                                                                                   class="btn-class-color btn-staff-color" ' . $disabledcontrol . '></input>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Font Colour</td>
                                                                <td>
                                                                    <div>
                                                                        <div class="fields-color">
                                                                            <input id="addteamfontcolor" name="addteamfontcolor"
                                                                                   class="btn-class-color btn-staff-color" ' . $disabledcontrol . '></input>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Is Available</td>
                                                                <td>
                                                                    <div class="rowsbtns">
                                                                        <div class="fields-color">
                                                                            <input id="addteamisavailable"
                                                                                   name="addteamisavailable" style="width:50px"
                                                                                   type="checkbox" ' . $disabledcontrol . '>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
																	<input type="hidden" id="addteamSPTeamID" name="addteamSPTeamID" value="0" >
                                                                    <input name="addteamsubmit" id="addteamsubmit" type="button"
                                                                           value="Create Team" ' . $hidecontrol . '>
                                                                    <input name="addteamcancel" id="addteamcancel" type="button"
                                                                           value="Cancel" ' . $hidecontrol . '>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!--Model content closes-->
                                                </div>
                                            </div>
                                            <!--model closes-->
                                        </div>
                                    </div>

                                    <div class="textAreaBlock">
                                        <div class="fields txtar textarea">
                                            <label for="largetext">Admin Notes</label>
                                            <span class="italicText">(Not visible to scheduled person)</span>
                                            <textarea name="adminnotes" id="adminnotes" cols="52" rows="45"
                                                      placeholder="Enter large text" maxlength="500" ' . $disabledcontrol . '></textarea>
                                        </div>
                                        <div class="fields txtar textarea ta2">
                                            <label for="largetext">Scheduling Notes</label>
                                            <span class="italicText">(Not visible to scheduled person)</span>
                                            <textarea name="fwanotes" id="fwanotes" cols="52" rows="45"
                                                      placeholder="Enter large text" maxlength="500" ' . $disabledcontrol . '></textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Tab -2 -->
                        <div id="tabs-2">
                            <!-- Trigger/Open The Modal -->
                            <div>
                                <div class="STdetails hometeam std">
                                    <div class="stfform ebtn">
                                        <div class="fields">
                                            <button class="btn-class btn-staff" id="myBtnEditUpdate">Edit</button>
                                        </div>
                                        <!-- The Modal -->
                                        <div id="myModalUpdate" class="modal">
                                            <!-- Modal content -->
                                            <div class="modal-content"><span class="closeupd" id="closeupd">×</span>
                                                <!--Model content-->
                                                <div>
                                                    <table id="rotanewedittable" class="smalltable bluetable" width="100%">
                                                        <thead>
                                                        <tr>
                                                            <th colspan="5">Staff Details</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr>
                                                            <td class="lightblue" width="15%">Staff Number</td>
                                                            <td width="35%">
                                                                <input autocomplete="off" class="ui-autocomplete-input"
                                                                       id="js_staffNumber" name="staff" type="text" size="15"
                                                                       placeholder="Staff Number">
                                                            </td>
                                                            <td class="lightblue" width="15%">Network ID</td>
                                                            <td width="35%">
                                                                <input autocomplete="off" class="ui-autocomplete-input"
                                                                       id="js_staffNetworkId" name="networkID" type="text"
                                                                       size="15" placeholder="Network ID">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="lightblue" width="15%">Forename</td>
                                                            <td width="35%">
                                                                <input autocomplete="off" class="ui-autocomplete-input"
                                                                       id="js_staffForeName" name="Forename" type="text"
                                                                       size="15" value="" placeholder="ForeName">
                                                            </td>
                                                            <td class="lightblue" width="15%">Surname</td>
                                                            <td width="35%">
                                                                <input autocomplete="off" class="ui-autocomplete-input"
                                                                       id="js_staffSurName" name="Surname" type="text" size="15"
                                                                       placeholder="Surname">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td colspan="3">
                                                                <input name="submit" type="submit" value="Find" id="js_find">
                                                                <input name="submit" id="js_cancel" type="button"
                                                                       value="Cancel">
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    <div class="tables gray-outer">
                                                        <div class="scrollable">
                                                            <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer"
                                                                   id="staffDetailsConfiglist" style="width: 100%;" role="grid"
                                                                   aria-describedby="joblisting_info">
                                                                <thead>
                                                                <tr class="thstyle">
                                                                    <th>Staff Number</th>
                                                                    <th>Network ID</th>
                                                                    <th>Forename</th>
                                                                    <th>Surname</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody class="context-menu-one">
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td colspan="3">Please apply the filter.</td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                </div>
                                                <!--Model content closes-->
                                            </div>
                                        </div>
                                    </div>
                                    <!--model close-->

                                    <table id="attachStaffSession">
                                        <tbody>
                                        <tr id="STFdtl">
                                            <td>Title</td>
                                            <td id="js_title"></td>
                                        </tr>
                                        <tr class="STFdtl">
                                            <td>Staff Number</td>
                                            <td id="js_staffnumber"></td>
                                        </tr>
                                        <tr class="STFdtl">
                                            <td>Network ID</td>
                                            <td id="js_netlogin"></td>
                                        </tr>
                                        <tr class="STFdtl">
                                            <td>First name</td>
                                            <td id="js_forename"></td>
                                        </tr>
                                        <tr class="STFdtl">
                                            <td>Surname</td>
                                            <td id="js_surname"></td>
                                        </tr>
                                        <tr class="STFdtl">
                                            <td>Middle Name</td>
                                            <td id="js_midname"></td>
                                        </tr>
                                        <tr class="STFdtl">
                                            <td>Preferred Name</td>
                                            <td id="js_prefname"></td>
                                        </tr>
                                        <tr class="STFdtl">
                                            <td>Designation</td>
                                            <td id="js_designation"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div class="rowbtns edtbtns undoUpdates" margin-top: 20px;">
                                        <button class="btn-class btn-staff btnf js_cancelStaff" id="js_cancelStaff">Undo</button>
                                        <button class="btn-class btn-staff btnf js_saveStaff" id="js_saveStaff">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab -3 -->
                        <div id="tabs-3">
                            <div id="tableContainer" class="tableContainer">
                                <div class="tables">
                                    <div class="container scrollable">
                                        <table class="oddevenclass tablesmall stripe bluetable dataTable no-footer"
                                           id="scheduleTeamHistory" style="width: 100%;" role="grid"
                                           aria-describedby="joblisting_info">
                                            <thead>
                                            <th>Scheduling Team</th>
                                            <th>Home Team Y/N</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Sort Code</th>
                                            <th>Created Date</th>
                                            <th>Created By</th>
                                            <th>Last Updated Date</th>
                                            <th>Last Updated By</th>
											<th>Is Available</th>
											<th>Action</th>
                                            </thead>
                                            <tbody class="context-menu-one">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab -4 -->
                        <div id="tabs-4">
                            <div class="tables">
                                    <div class="container scrollable">
                                        <table id="contract_history" class="oddevenclass tablesmall stripe bluetable dataTable no-footer" style="width: 99.96%;">
                                        <thead>
                                          <tr>
                                            <th class="w80">Start Date</th>
                                            <th class="w80">End Date</th>
                                            <th>Accounting Group</th>
                                            <th>EDP Minimum (Exc Breaks)</th>
                                            <th>Paid Contract (Contracted Hours)</th>
                                            <th>Manual EDP</th>
                                            <th>EFT</th>
                                            <th>Employee Group</th>
                                            <th>Contract Type</th>
                                            <th>Payment Type</th>
                                            <th>Teampay department</th>
                                            <th>Prefer EDP TOIL</th>
                                            <th>Terms & Conditions</th>
                                            <th>Cost Code</th>
                                            <th>Activity Type</th>
                                            <th>Duty Duration</th>
                                            <th>Title</th>
                                            <th>Staff Number</th>
                                            <th>Network ID</th>
                                            <th>First Name</th>
                                            <th>Surname</th>
                                            <th>Middle Name</th>
                                            <th>Preferred Name</th>
                                            <th>Designation</th>
                                            <th>History</th>
                                          </tr>
                                          </thead>
                                          <tbody>
                                          </tbody>
                                        </table>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }
}
