<div id="tabs-2" role="tabpanel" aria-labelledby="tab-staff-details">

    <div class="STdetails std">

        {{-- "Edit" button opens staff search modal --}}
        <div class="stfform ebtn">
            <div class="fields">
                <button id="myBtnEditUpdate" type="button" class="buttonEnabled">
                    Edit
                </button>
            </div>
        </div>

        {{-- STAFF DETAILS PREVIEW TABLE --}}
        <table id="attachStaffSession" class="table" style="font-weight: normal;">
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

        {{-- STAFF ACTION BUTTONS --}}
        <div class="undoUpdates ebtn" style="margin-top:15px;">
            <button class="js_cancelStaff" id="js_cancelStaff" type="button"> Undo </button>
            <button class="js_saveStaff" id="js_saveStaff" type="button"> Save </button>
        </div>

        {{-- Hidden error div expected by JS --}}
        <div id="stafferror" class="visually-hidden" role="alert"></div>

    </div>
</div>