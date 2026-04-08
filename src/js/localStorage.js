var Storage = window.localStorage;
/*
---------Save Selected Team--------
*/
function saveSelectedTeamForSchduledStaff(teamID,teamName) {
    if(typeof(Storage)!=="undefined") {
            localStorage.setItem('teamID',teamID);  
            localStorage.setItem('teamName',teamName);            
    } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}


/*
---------get Selected Team--------
*/

function getSavedTeamForSchduledStaff() {
    
    if(typeof(Storage)!=="undefined") {
         return  localStorage.getItem('teamID');
     } else {
        return 0;
    }
}

/*
---------get Selected Team--------
*/

function getSavedTeamNameForSchduledStaff() {
    
    if(typeof(Storage)!=="undefined") {
         return  localStorage.getItem('teamName');
     } else {
        return 0;
    }
}


/*
---------Save Selected Team For Non Scheduled Team--------
*/
function saveSelectedTeamForNonSchduledStaff(teamID,teamName) {
    if(typeof(Storage)!=="undefined") {
            localStorage.setItem('NSteamID',teamID);  
            localStorage.setItem('NSteamName',teamName);            
    } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}


/*
---------get Selected Team For Non schedulleTeam--------
*/

function getSavedSelectedTeamForNonSchduledStaff() {
    
    if(typeof(Storage)!=="undefined") {
         return  localStorage.getItem('NSteamID');
     } else {
        return 0;
    }
}

/*
---------get Selected Team Name For Non schedulleTeam--------
*/

function getSavedSelectedTeamNameForNonSchduled() {
    
    if(typeof(Storage)!=="undefined") {
         return  localStorage.getItem('NSteamName');
     } else {
        return 0;
    }
}


/*
---------Save Selected Division--------
*/

function saveSelectedDivision(division_id,division_name) {
    
    if(typeof(Storage)!=="undefined") {
        localStorage.setItem('divisionID',division_id);
        localStorage.setItem('divisionName',division_name);
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}

/*
---------get Selected Division--------
*/

function getSavedSelectedDivision() {
    
    if(typeof(Storage)!=="undefined") {
         return  localStorage.getItem('divisionID');
     } else {
        return 0;
    }
}


/*
---------get Selected Division--------
*/

function getSavedSelectedDivisionName() {
    
    if(typeof(Storage)!=="undefined") {
         return  localStorage.getItem('divisionName');
     } else {
        return 0;
    }
}

/** ----Save Temp Weekly Filters */
function applySavedweeklyFilter(postData) {
    if(typeof(Storage)!=="undefined") {
       localStorage.setItem('weeklyscreen_filter', JSON.stringify(postData));
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}

/** ----Get Temp Weekly Filters */
function getSavedweeklyFilter() {
    if(typeof(Storage)!=="undefined") {
        return  localStorage.getItem('weeklyscreen_filter');
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}

/** ----Get Temp Daily Filters--- */
function applySaveddailyFilter(postData) {
    if(typeof(Storage)!=="undefined") {
       localStorage.setItem('dailyscreen_filter', JSON.stringify(postData));
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}

/** ----Get Temp Daily Filters--- */
function getSaveddailyFilter() {
    if(typeof(Storage)!=="undefined") {
        return  localStorage.getItem('dailyscreen_filter');
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}

/** ----delete Daily Filters--- */

function deltedSaveddailyFilter() {
    if(typeof(Storage)!=="undefined") {
        if((localStorage.getItem('dailyscreen_filter') != '') && (localStorage.getItem('dailyscreen_filter') != null)) {
            localStorage.removeItem('dailyscreen_filter');
            return true;
        }
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}

function deltedSavedweeklyFilter() {
    if(typeof(Storage)!=="undefined") {
        if((localStorage.getItem('weeklyscreen_filter') != '') && (localStorage.getItem('weeklyscreen_filter') != null)) {
            localStorage.removeItem('weeklyscreen_filter');
            return true;
        }
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}


/** ----Save Temp Edit Weekly Rota Filters */
function applySavedEditweeklyRotaFilter(postData) {
    if(typeof(Storage)!=="undefined") {
       localStorage.setItem('EditweeklyRotascreen_filter', JSON.stringify(postData));
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}

/** ----Get Temp Edit Weekly Rota Filters */
function getSavedEditweeklyRotaFilter() {
    if(typeof(Storage)!=="undefined") {
        return  localStorage.getItem('EditweeklyRotascreen_filter');
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}

/** ----delete Edit Weekly Rota Filters--- */
function deletedSavedEditweeklyRotaFilter() {
    if(typeof(Storage)!=="undefined") {
        if((localStorage.getItem('EditweeklyRotascreen_filter') != '') && (localStorage.getItem('EditweeklyRotascreen_filter') != null)) {
            localStorage.removeItem('EditweeklyRotascreen_filter');
            return true;
        }
     } else {
        alert("Opps!! Your Browser does not support Web storage.");
    }
}



