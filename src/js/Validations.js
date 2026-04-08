/*
* objective: Centralized validation functions
* Created By: Vipin Kushwaha
* Created Date: 07-04-2021
* Description: Contaons all data validation functions in one file
* */
// Validate length of text and trim it.
var prevValue;

function ValidateText(ControlId, MinLength, MaxLength, FieldName) {
    var Controltext = $(ControlId).val().trim();
    var lengthData = $(ControlId).val().length;
    if (lengthData < MinLength) {
        customAlertByModel("Please enter minimum  " + MinLength + " characters for the " + FieldName + ".");
        return 0;
    } else if (lengthData > MaxLength) {
        customAlertByModel("Maximum length allowed is " + MaxLength + " for the " + FieldName + ".");
        return 0;
    } else if (Controltext == "") {
        customAlertByModel("Please enter valid value for " + FieldName + " . Only spaces are not allowed.")
        $(ControlId).val("");
        return 0;
    } else {
        $(ControlId).val(Controltext);
        return 1;
    }
}

// Validate Text types allowed in a text box
function TextTypeValidation(ControlName, FieldName) {
    var ControlVal = $(ControlName).val();
    var RGEXToCheck = /^([a-zA-Z0-9+&!()|\[\]\- ,.=()*_:{}?]{0,500})$/;
    var key = window.event.keyCode;
    var Flag = RGEXToCheck.test(ControlVal);
    var FlagforDutyName=!/leave|sick/i.test(ControlVal);
    if (FlagforDutyName == false) {
        $(ControlName).val(ControlVal.slice(0, -1));
        customAlertByModel("Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a different name.");
        return 0;
    }
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val(ControlVal.slice(0, -1));
            customAlertByModel("Only specified symbols (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) are permitted in " + FieldName + ".");
            return 0;
        }
    }
}

function TextTypeValidationJob(ControlName, FieldName) {

    var ControlVal = $(ControlName).val();
    var RGEXToCheck = /^([a-zA-Z0-9+&!()|\[\]\- ,.=()*_:{}?]{0,500})$/;
    var key = window.event.keyCode;
    var Flag = RGEXToCheck.test(ControlVal);
    
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val(ControlVal.slice(0, -1));
            customAlertByModel("Only specified symbols (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) are permitted in " + FieldName + ".");
            return 0;
        }
    }
}

/*
* objective: Centralized validation functions
* Created Date: 13-04-2021
* Description: start year and week validation 
* */
function StartYearEndYear(startYear, endYear, startWeek, endWeek) {

    if (parseInt(startYear) > parseInt(endYear)) {
        customAlertByModel("Please ensure that Available From Year should be less than Available To Year");
        return 1;
    } else {
        if (parseInt(startYear) == parseInt(endYear) && (parseInt(startWeek) > parseInt(endWeek))) {
            customAlertByModel("Please ensure that Available From Week should be less than Available To Week");
            return 1;
        }
    }
}

/*
* objective: Centralized validation functions
* Created By: Naveeta Sharma
* Created Date: 28-04-2021
* Description: start time and end time validation 
* */

function TimeValidationCheck(strStartTime, strEndTime) {

    if (strStartTime == '0') {
        strStartTime = '00:00';
    }
    var startTime = new Date().setHours(GetHours(strStartTime), GetMinutes(strStartTime), 0);
    var endTime = new Date(startTime);
    endTime = endTime.setHours(GetHours(strEndTime), GetMinutes(strEndTime), 0);

    if (startTime == endTime) {
        customAlertByModel("Start Time equals End time");
        return 1;
    }

}

//calculate the hours
function GetHours(d) {
    var h = parseInt(d.split(':')[0]);
    return h;
}

//calculate the minutes
function GetMinutes(d) {
    return parseInt(d.split(':')[1].split(' ')[0]);
}

//Function to find a day of the week.
function getDayOfWeek(date) {
    const dayOfWeek = new Date(date.replace(/-/g, '\/')).getDay();
    return isNaN(dayOfWeek) ? null :
        ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][dayOfWeek];
}

// Validate Text types allowed in a text box
function integerTypeValidation(ControlName, FieldName) {
    let ControlVal = $(ControlName).val();
    let RGEXToCheck = /^([0-9]{0,500})$/;
    let key = window.event.keyCode;
    let Flag = RGEXToCheck.test(ControlVal);
    let SplChar = ControlVal.charAt(ControlVal.length - 1);
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val(ControlVal.slice(0, -1));
            customAlertByModel("No Text/symbol/Special character (" + SplChar + ") is permitted in " + FieldName + ".");
            return 0;
        }
    }
}

// Validate integer types with decimal allowed in a text box
function integerTypeValidationWithTwoDecimal(ControlName, FieldName) {
    let ControlVal = $(ControlName).val();
    let RGEXToCheck = /^[0-9]*(\.[0-9]{0,2})?$/;
    let key = window.event.keyCode;
    let Flag = RGEXToCheck.test(ControlVal);
    let SplChar = ControlVal.charAt(ControlVal.length - 1);
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val(ControlVal.slice(0, -1));
            customAlertByModel("No Text/symbol/Special character (" + SplChar + ") is permitted in " + FieldName + ".");
            return 0;
        }
    }
}

// Validate Text types allowed in a text box
function integerTypeValidationNoDecimalNegativeAllowed(ControlName, FieldName) {
    let ControlVal = $(ControlName).val();
    let RGEXToCheck = /^(?:\d*\.\d{1,2}|\d+)$/;
    let key = window.event.keyCode;
    let Flag = RGEXToCheck.test(ControlVal);
    let SplChar = ControlVal.charAt(ControlVal.length - 1);
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val("");
            customAlertByModel("Only positive integer values are allowed upto two decimal place in " + FieldName + " field.");
            return 0;
        }
    }
}
// Validate Text types allowed in a text box
function integerTypeValidationWithFirstCharacter(ControlName, FieldName) {
    let ControlVal = $(ControlName).val();
    // let RGEXToCheck = /^([0-9]{0,500})$/;
    let RGEXToCheck = /^([a-zA-Z][0-9]{0,500})$/;
    let key = window.event.keyCode;
    let Flag = RGEXToCheck.test(ControlVal);
    let SplChar = ControlVal.charAt(ControlVal.length - 1);
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val(ControlVal.slice(0, -1));
            customAlertByModel("No Text/symbol/Special character (" + SplChar + ") is permitted in " + FieldName + ".");
            return 0;
        }
    }
}

function callDatePicker(dateControlName,mindate,maxdate,yearrange) {
    $(dateControlName).datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: false,//no need to display this btn, removed on 21-07-2021
        dateFormat: "dd-mm-yy",
        yearRange: yearrange,
        inline: true,
        minDate: new Date(mindate),
        maxDate: maxdate//new date fn is returning invalid date that's we removed it.
    });
    $(dateControlName).datepicker('option', 'firstDay', 6);
}
