$(function () {
    $('#main-menu').smartmenus({
        subMenusSubOffsetX: 1,
        subMenusSubOffsetY: -8
    });
    $('.menu-list-redirect').on('click', function (event) {
        let param1 = $(this).attr('data-param1');
        let param2 = $(this).attr('data-param2');
        let param3 = $(this).attr('data-param3');

        let url = "/?menuRedirect=1";
        localStorage.setItem('param1', param1);
        if (param2 !== undefined) {
            localStorage.setItem('param2', param2);
        }
        if (param3 !== undefined) {
            localStorage.setItem('param3', param3);
        }

        if (typeof ShowMasterJobs === 'function') { // Redirect in legacy code
            loadPageFunction();
        } else { // Redirect from laravel 
            document.location.href = url;
        }
    });
    loadPageFunction();
});

/**
 * Load pages based on menu actions
 */
function loadPageFunction() {
    var urlParams = new URLSearchParams(window.location.search);
    var param1 = localStorage.getItem("param1");
    var param2 = localStorage.getItem("param2");
    var param3 = localStorage.getItem("param3");
    if (param1 && param1 !== '' && param1 !== 'null' && param1 !== 'undefined') {
        switch (param1) {
            case 'masterjobs':
                ShowMasterJobs();
                break;
            case 'rotas':
                ShowRotas();
                break;
            case 'masterdutiesfilter':
                ShowMasterDutiesFilter();
                break;
            case 'adhocduty':
                showadhocduty();
                break;
            case 'dailyrota':
                ShowDailyRota();
                break;
            case 'monthrota':
                ShowRota();
                break;
            case 'yearrota':
                ShowYearAllocations();
                break;
            case 'editweekallocation':
                EditAllocations(param2);
                break;
            case 'prodview':
                ShowAllocationsDuties(param2);
                break;
            case 'studiousage':
                ShowStudioUsage(param2);
                break;
            case 'weeklyallocations':
                ShowAllocations(param2);
                break;
            case 'teamallocgrid':
                ShowDailyAllocations(param2, param3);
                break;
            case 'dailyallocations':
                ShowDailyAllocations(param2, "", "", 1);
                break;
            case 'editteamallocation':
                EditAllocations(param2);
                break;
            case 'teamyearallocation':
                ShowSchedulingTeamYearAllocations(param2);
                break;
            case 'multiviewalloc':
                ShowAllocationsMulti(param2, param3);
                break;
            case 'mysetup':
                ShowMySetUpOptions();
                break;
            case 'mycontact':
                ShowUserContacts();
                break;
            case 'deptadmin':
                ShowDepartmentAdmins();
                break;
            case 'teamcontact':
                ShowTeamContacts();
                break;
            case 'webversion':
                ShowVersion();
                break;
            case 'gridcheck':
                ShowGridChecks(param2, param3);
                break;
            case 'skillpage':
                ShowSkillsShiftleaders();
                break;
            case 'skillnocando':
                ShowAllocationsNoCanDo(param2);
                break;
            case 'handovers':
                ShowHandovers();
                break;
            case 'freelancereport':
                ShowAllocationsUsageSched();
                break;
            case 'leavereport':
                ShowLeaveReports(0);
                break;
            case 'sickreport':
                ShowSicknessReports();
                break;
            case 'sickreportglobal':
                ShowSicknessReportsGlobal();
                break;
            case 'actionHandler':
                actionHandler(param2);
                break;
            case 'weeklyduties':
                ShowMasterDutiesWeekly();
                break;
            case 'summaryduties':
                ShowMasterDutiesSummary();
                break;
            case 'rotapattern':
                ShowRotasReports();
                break;
            case 'skillreports':
                ShowSkillsReports();
                break;
            case 'allocationcapcity':
                ShowAllocationsCapacity();
                break;
            case 'byallocarea':
                ShowAllocationsUsageArea();
                break;
            case 'byleavearea':
                ShowLeaveByAreaReport();
                break;
            case 'bysickreport':
                ShowSicknessByAreaReports();
                break;
            case 'allocateuser':
                ShowAllocateUsers();
                break;
            case 'scheduledpeople':
                ShowScheduledPerson();
                break;
            case 'configleave':
                ShowConfigLeave();
                break;
            case 'showscheduledteam':
                showSchedulingTeamUI();
                break;
            case 'myskill':
                ShowAllTeamSkills();
                break;
            case 'allocatehelper':
                ShowAllocateHelper(param2);
                break;
            case 'skilladmin':
                ShowSkillsAdmin();
                break;
            case 'showallocleave':
                ShowAllocateLeave(param2);
                break;
            case 'showleave':
                ShowLeave(param2);
                break;
            case 'showleaveweekly':
                Showleaveweekly();
                break;
            case 'adminshowleaveweekly':
                AdminShowLeaveWeekly();
                break;
            case 'showleaveadmin':
                ShowLeaveAdmin();
                break;
            case 'showxmaspoint':
                ShowXmasPoints(param2, 0);
                break;
            case 'leavecredit':
                ShowConfigLeaveCredits();
                break;
            case 'dutycolour':
                ConfigureMasterDutyColors();
                break;
            case 'system':
                ShowSystemAdmin();
                break;
            case 'allocatehelp':
                ShowAllocateHelp(param2)
                break;
            case 'showrequests':
                ShowRequests();
                break;
            case 'showweeklyrequests':
                ShowWeeklyRequests();
                break;
            case 'showrequestsddmin':
                ShowRequestsAdmin();
                break;
            case 'adminshowweeklyrequests':
                AdminShowWeeklyRequests();
                break;
            case 'showlocksadmin':
                ShowLocksAdmin();
                break;
        }
    } else {
        if (urlParams.get('menuRedirect') == 1) {
            window.location.replace("/");
        }
    }
    localStorage.removeItem("param1");
    localStorage.removeItem("param2");
    localStorage.removeItem("param3");
}