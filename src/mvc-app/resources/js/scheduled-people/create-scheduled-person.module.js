/**
 * Scheduled People (module)
 * This module manages the creation, editing, and viewing of scheduled people in the application.
 * Uses jQuery UI tabs + datepickers
 */

/**
 * Constructor for ScheduledPeopleVar
 * @param {Object} p - Configuration object
 * @param {string} p.mode - Operation mode: 'create', 'edit', or 'view'
 * @param {number} p.personId - The scheduled person ID (0 for create mode)
 * @param {Object} p.urls - Object containing URL endpoints for various operations
 */
var ScheduledPeopleVar = function (p) {
  // config
  this.mode = (p && p.mode) || 'create';
  this.personId = (p && p.personId) || 0;
  this.urls = (p && p.urls) || {};
  this.specialTeams = ['ARCHIVE', 'FREELANCER', 'FREELANCERS', 'OTHER BBC']

  // state (legacy-compatible)
  this.additional = []; // [{ teamId, teamName, start, end, sortCode, backColor, fontColor, isAvailable, isDefaultBG }]

  this.init();
};

ScheduledPeopleVar.prototype = {
  /**
  * Initialize the module - sets up UI components, event handlers, and loads initial data
  * Called automatically when the instance is created
  * @returns {void}
  */
  init: function () {
    var that = this; // NOSONAR javascript:S7740


    var $tabs = $("#schedulePersonTab").tabs({});
    if (!$tabs) return;

    // Initialize datepickers for all date fields
    $("#startdate, #enddate, #addteamstartdate, #addteamenddate").datepicker({
      changeMonth: true, changeYear: true, dateFormat: "dd-mm-yy", yearRange: '1995:2050', firstDay: 6
    });

    // Initialize Spectrum color pickers for home team colors (palette-only mode)
    that.initializeSpectrumColorPickers();

    // Listen for homeTeamDeleted event from team-history module
    // When a home team is deleted, refresh the additional teams grid
    $(document).off('homeTeamDeleted.refreshAdditional').on('homeTeamDeleted.refreshAdditional', function (event, data, personid) {
      // Refresh the additional teams from the server
      that.fetchDetails();
    });

    // Wire events
    $('#saveSchedulePerson').off('click.scheduledPeople').on('click.scheduledPeople', function (e) {
      e.preventDefault();
      e.stopPropagation(); // Prevent event bubbling
      //Validate
      //if (!that.getScheduledPersonPayload()) return;

      const result = that.getScheduledPersonPayload();
      if (!result.valid) {
        return;
      }

      //Open future  home team
      if (that.additionalFutureHomeTeam()) {
        that.saveScheduledPerson(result.payload);
      }
    });

    // Staff Save button handler - saves staff details from the modal
    $('#saveStaffDetails').off('click.saveStaff').on('click.saveStaff', function (e) {
      e.preventDefault();
      that.saveStaffDetails();
    });

    // Global legacy-compatible modal API
    globalThis.openModalPopup = function (teamId, teamStart, src) { that.openModalPopup(teamId, teamStart, src); };
    globalThis.goBack = function (teamId, userId) { that.goBack(teamId, userId); };
    globalThis.ScheduledPeople = globalThis.ScheduledPeople || {};
    globalThis.ScheduledPeople.saveAdditionalTeam = function () { that.saveAdditionalTeamFromModal(); };

    // Load teams then hydrate (if edit/view)
    that.loadTeams(that.mode).done(function () {
      if ((that.mode === 'edit' || that.mode === 'view') && that.personId) {
        that.fetchDetails().always(function () {
          // Enable all tabs in edit/view mode
          that.enableStaffAndTeamTabs();
          $('.contract_history').removeClass('buttonDisabled').addClass('buttonEnabled');

          // Call the callback to reload staff details after scheduled person details are loaded
          if (that.onDetailsLoaded && that.personId) {
            that.onDetailsLoaded(that.personId);
          }
        });
      }
      that.bindHomeTeamBehaviour();
    });

    // Update Person behaviour: set start date to today when home team changes
    if ($("#saveSchedulePerson").text().trim() === 'Update Person') {
      $("#hometeam").on("change", function () {
        if ($(this).val()) $("#startdate").datepicker("setDate", new Date());
      });
    }

    //Set default colours
    $('#do-not-display-in-view-screen').on('click', function () {
      if ($('#additional_team_type').val() != 2) {
        return;
      }
      $('#do-not-display-in-view-screen').prop('checked') ? $('#addteamBackcolor').val('#444444') : $('#addteamBackcolor').val('#741b47');
    });
  },

  /**
   * Additional Future Home Team
   */
  additionalFutureHomeTeam: function () {
    let that = this; // NOSONAR javascript:S7740

    //Only while edit the future addtional home team is created
    if (that.mode.toLowerCase() == 'create' || $('#OriginalHomeTeamID').val() == $('#hometeam').val()) {
      return true;
    }

    let endDateAdditional = moment($('#startdate').val(), 'DD-MM-YYYY')
      .subtract(1, 'days').format('DD-MM-YYYY');

    let hasAdditionalTeam = that.additional.some(t => {
      return t.isHomeTeam == 2 && $('#hometeam').val() == t.teamId && endDateAdditional == t.end;
    });

    if (
      !that.specialTeams.includes($('#hometeam option:selected').text().toUpperCase())
      && !hasAdditionalTeam
    ) {

      //Get existing data
      let editExisting = that.additional.find(t =>
        Number(t.isHomeTeam) == 2 && Number(t.teamId) == $('#hometeam').val()
      );

      if (that.mode.toLowerCase() == 'create' && editExisting) {
        that.openModalPopup(editExisting.teamId, editExisting.start, 'auto_add_future_home_team');
      } else {
        that.openModalPopup(null, null, 'auto_add_future_home_team');
      }
      return false;
    }
    return true;
  },
  /**
   * Initialize Spectrum color pickers for all color input fields (palette-only mode)
   * Shows only the color palette without the color picker interface
   * @returns {void}
   */
  initializeSpectrumColorPickers: function () {
    // Home team color pickers - palette only
    $('#hometeambackcolour, #hometeamfontcolour').spectrum({
      type: 'color',
      showPaletteOnly: true,
      hideAfterPaletteSelect: true,
      clickoutFiresChange: false,
      preferredFormat: 'hex',
      palette: [
        ['#ffffff', '#f0f0f0', '#e0e0e0', '#cccccc', '#999999', '#666666', '#333333', '#000000', '#ccccff'],
        ['#ff0000', '#ff6666', '#ff9999', '#ffcccc', '#ffeeee', '#ff8000', '#ffaa44', '#ddaa44', '#999966'],
        ['#ffff00', '#ffff99', '#ffff00', '#ffdd00', '#ffffcc', '#008000', '#66cc00', '#99dd44', '#ccffcc'],
        ['#0000ff', '#6666ff', '#9999ff', '#ccccff', '#eeeeff', '#00ccff', '#44ddff', '#88ffff', '#ccffff'],
        ['#9933ff', '#cc99ff', '#dd99ff', '#ffddff', '#ff99ff', '#ff0066', '#ff3366', '#cc0066', '#990099']
      ],
      change: function (color) {
        // Update the input field value when color changes
        $(this).val(color.toHexString());
      }
    });

    // Modal color pickers - initialize on modal open
    $(document).off('click.initModalColors').on('click.initModalColors', '#myBtn, #myBtnRight, #ddlteamsidedit, #ddlteamsidview', function () {
      // Delay initialization until modal is actually opened
      setTimeout(function () {
        $('#addteamBackcolor, #addteamfontcolor').spectrum({
          type: 'color',
          showPaletteOnly: true,
          hideAfterPaletteSelect: true,
          clickoutFiresChange: false,
          preferredFormat: 'hex',
          palette: [
            ['#ffffff', '#f0f0f0', '#e0e0e0', '#cccccc', '#999999', '#666666', '#333333', '#000000', '#ccccff'],
            ['#ff0000', '#ff6666', '#ff9999', '#ffcccc', '#ffeeee', '#ff8000', '#ffaa44', '#ddaa44', '#999966'],
            ['#ffff00', '#ffff99', '#ffff00', '#ffdd00', '#ffffcc', '#008000', '#66cc00', '#99dd44', '#ccffcc'],
            ['#0000ff', '#6666ff', '#9999ff', '#ccccff', '#eeeeff', '#00ccff', '#44ddff', '#88ffff', '#ccffff'],
            ['#9933ff', '#cc99ff', '#dd99ff', '#ffddff', '#ff99ff', '#ff0066', '#ff3366', '#cc0066', '#990099']
          ],
          change: function (color) {
            // Update the input field value when color changes
            $(this).val(color.toHexString());
          }
        });
      }, 100);
    });
  },

  /**
   * Create HTML for a structured popup with div-based table layout
   * @param {string} msg - The message to display
   * @returns {string} - HTML string for the structured popup
   */
  createStructuredPopupHtml: function (msg) {
    let messageText = String(msg || '');

    // Convert list-style error messages to paragraph format
    let contentHtml = messageText
      .replace(/\*Warning\*\n/g, '*Warning*<br>') // Keep Warning on first line with line break
      .replace(/\n/g, ' ') // Replace remaining newlines with spaces to create paragraph
      .replace(/\d+\.\s*/g, '') // Remove numbered list formatting
      .replace(/\s+/g, ' ') // Normalize multiple spaces
      .trim();

    let html = '<div class="popup-container" style="font-family: Arial, sans-serif; width: 500px; border: 1px solid #C0C0C0; margin-top: 18px; margin-right: 11px;">' +
      // Header row: "Message" label
      '<div class="popup-row popup-header" style="display: flex; background-color: #D0D0D0; padding: 5px; margin-bottom: 0; border-bottom: 1px solid #C0C0C0;">' +
      '<div class="popup-cell" style="flex: 1; font-weight: bold; font-size: 1em;">*Message*</div>' +
      '</div>' +
      // Content row: Warning and message details
      '<div class="popup-row popup-content" style="display: flex; padding: 8px; border-bottom: 1px solid #C0C0C0;">' +
      '<div class="popup-cell" style="flex: 1;">' +
      '<p style="margin: 0; line-height: 1.4;">' + contentHtml + '</p>' +
      '</div>' +
      '</div>' +
      // Footer row: OK button (right-aligned)
      '<div class="popup-row popup-footer" style="display: flex; justify-content: flex-end; padding: 5px; gap: 0;">' +
      '<div class="popup-cell" style="margin-right: 10px;">' +
      '<button class="popup-ok-btn" onclick="$.facebox.close()" style="padding: 5px 15px; cursor: pointer;">OK</button>' +
      '</div>' +
      '</div>' +
      '</div>';

    return html;
  },

  /**
   * Display a structured popup message in a facebox modal
   * @param {string} msg - The message to display
   * @returns {void}
   */
  alertBox: function (msg) {
    let html = this.createStructuredPopupHtml(msg);
    $.facebox(html);
  },

  /**
   * Escape HTML special characters to prevent XSS attacks
   * @param {string} s - The string to escape
   * @returns {string} - The escaped string
   */
  escapeHtml: function (s) {
    return (s || '')
      .replaceAll('&', '&amp;').replaceAll('<', '<').replaceAll('>', '>')
      .replaceAll('"', '"').replaceAll("'", '&#039;');
  },

  /**
   * Parse a UK format date string (dd-mm-yyyy) into a Date object
   * @param {string} str - Date string in dd-mm-yyyy format
   * @returns {Date|null} - Parsed Date object or null if invalid
   */
  parseUkDate: function (str) {
    let m = /^(\d{2})-(\d{2})-(\d{4})$/.exec((str || '').trim());
    if (!m) return null;
    let dd = +m[1], mm = +m[2], yyyy = +m[3];
    let d = new Date(yyyy, mm - 1, dd);
    return Number.isNaN(d.getTime()) ? null : d;
  },

  /**
   * Check if a date is within the allowed lower bound (02-12-1995)
   * @param {Date} d - The date to check
   * @returns {boolean} - True if date is valid and within bounds
   */
  withinLowerBound: function (d) {
    return d >= new Date(1995, 11, 2); // 02-12-1995
  },

  /**
   * Set CSRF token header for AJAX requests
   * @returns {void}
   */
  setCsrfHeader: function () {
    let token = $('meta[name="csrf-token"]').attr('content');
    if (token) $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
  },

  /**
   * Load available scheduling teams from the server and populate dropdowns
   * @param {string} actionType - The action type (create, edit, view, etc.)
   * @returns {jQuery.Deferred} - Promise that resolves when teams are loaded
   */
  loadTeams: function (actionType) {
    const url = (this.urls && this.urls.teamList) || '';
    if (!url) return $.Deferred().resolve().promise();

    var that = this; // NOSONAR javascript:S7740
    const act = actionType || (this.mode || 'create');

    return $.getJSON(url, {
      actionType: act,
      personId: this.personId || 0
    }).done(function (teams) {
      const $home = $('#hometeam');
      const $add = $('#addteam').length ? $('#addteam') : $('#ddlTeams');
      let currentSelected = $home.val();
      const isCreateMode = (that.mode || '').toLowerCase() === 'create';

      $home.empty().append('<option value="-1">Select a Team</option>');
      $add.empty().append('<option value="-1">Select a Team</option>');

      (teams || []).forEach(function (t) {
        const teamId = t.TeamID ?? '';
        const teamName = t.TeamName || t.name || '';
        const safeName = that.escapeHtml(teamName);

        const opt = '<option value="' + teamId + '">' + safeName + '</option>';
        $home.append(opt);
        $add.append(opt);
      });

      if (isCreateMode) {
        const selectedHomeTeamId = $('#selectedteamid').val() || $('#hometeamhidden').val();
        if (selectedHomeTeamId && (!currentSelected || currentSelected === '-1')) {
          $home.val(String(selectedHomeTeamId));
        }
      }
    });
  },

  /**
   * Fetch scheduled person details from the server and populate the form
   * Used in edit and view modes to load existing data
   * @returns {jQuery.Deferred} - Promise that resolves when details are loaded
   */
  fetchDetails: function () {
    var that = this; // NOSONAR javascript:S7740
    let url = that.urls.details;
    if (!url) return $.Deferred().resolve().promise();

    // Cache-bust to avoid stale JSON from the browser cache
    url += (url.includes('?') ? '&' : '?') + '_ts=' + Date.now();

    return $.getJSON(url).done(function (data) {
      let person = null;
      let additional = [];

      if ($.isArray(data) && data.length) {
        let homeRow = data.find(function (r) { return Number(r.IsHomeTeam) === 1; }) || data[0];

        person = {
          display_first_name: homeRow.DisplayFirstName || homeRow.Forename || '',
          display_last_name: homeRow.DisplayLastName || homeRow.Surname || '',
          team_id: (homeRow.TeamID != null) ? Number(homeRow.TeamID) : null,
          SPTeamID: homeRow.SPTeamID ? Number(homeRow.SPTeamID) : 0,
          start_date: homeRow.StartDate || '',
          sort_code: homeRow.SortCode || '',
          admin_notes: homeRow.AdminNotes || '',
          fwa_notes: homeRow.FWANotes || '',
          fontcolour: homeRow.fontcolour || '',
          backcolour: homeRow.BackgroundColour || '',
          default_bg: Number(homeRow.IsDefaultBGColour || 0) === 1,
          additional_leave: Number(homeRow.IsAdditionalLeave || 0) === 1,
          display_name: homeRow.DisplayName || '',
          isHomeTeam: homeRow.IsHomeTeam,
          scheduled_person_id: homeRow.ScheduledPersonID ? Number(homeRow.ScheduledPersonID) : null
        };

        additional = data
          .filter(function (r) { return [0, 2].includes(Number(r.IsHomeTeam)); })
          .map(function (r) {
            return {
              teamId: Number(r.TeamID),
              teamName: r.TeamName || '',
              start: r.StartDate || '',
              end: r.EndDate || '',
              sortCode: r.SortCode || '',
              backColor: r.BackgroundColour || '#cccccc',
              fontColor: r.fontcolour || '#000000',
              isAvailable: Number(r.IsAvailable || 0) === 1,
              isDefaultBG: Number(r.IsDefaultBGColour || 0) === 1,
              addteamSPTeamID: r.SPTeamID ? Number(r.SPTeamID) : 0,
              spTeamId: r.SPTeamID ? Number(r.SPTeamID) : 0,
              isHomeTeam: r.IsHomeTeam,
              doNotDisplayInViewScreen: Number(r.DisplayInViewScreen || 0) == 0 ? 1 : 0
            };
          });
      }

      if (!person) {
        that.additional = [];
        that.syncHidden();
        that.renderAdditionalTeams();
        that.applyArchiveLock();
        return;
      }

      // Populate form fields with fetched data
      $('#dispFirstName').val(person.display_first_name || '');
      $('#dispLastName').val(person.display_last_name || '');

      if (person.team_id != null) {
        $('#hometeam').val(String(person.team_id));
        $('#OriginalHomeTeamID').val(String(person.team_id));
        $('#original-home-date').val(person.start_date);
      }

      if (person.SPTeamID) {
        $('#SPTeamID').val(String(person.SPTeamID));
      }

      $('#startdate').val(person.start_date || '');
      $('#OriginalHomeStartDate').val(person.start_date || '');
      $('#enddate').val(person.end_date || '');
      $('#sortcode').val(person.sort_code || '');

      $('#adminnotes').val(person.admin_notes || '');
      $('#fwanotes').val(person.fwa_notes || '');
      $('#hometeamfontcolour').val(person.fontcolour || '#000000');
      $('#hometeambackcolour').val(person.backcolour || '#eeeeee');

      // Update Spectrum color picker display to reflect the loaded values
      $('#hometeamfontcolour').spectrum('set', person.fontcolour || '#000000');
      $('#hometeambackcolour').spectrum('set', person.backcolour || '#eeeeee');

      $('#defaultbgcolour').prop('checked', !!person.default_bg);
      $('#additionalleave').prop('checked', !!person.additional_leave);

      if (person.scheduled_person_id) $('#newpersonid').val(person.scheduled_person_id);

      // Lock start date after load in edit mode only
      if (this.mode !== 'create') {
        $('#startdate').prop('disabled', true);
      }

      if (person.display_name && ($('#useractiontype').val() !== 'create')) {
        let modeTitle = ($('#useractiontype').val() === 'view') ? 'View' : 'Edit';
        $('#span_scheduled_person').text(modeTitle + ' Scheduled Person Details - ' + person.display_name);
      }

      that.additional = Array.isArray(additional) ? additional : [];
      that.syncHidden();
      that.renderAdditionalTeams();
      that.applyArchiveLock();
    }).fail(function () {
      // Optional: show a friendly message or leave UI as-is
    });
  },

  /**
   * Bind event handlers for home team dropdown changes
   * Handles start date enable/disable and archive team locking
   * @returns {void}
   */
  bindHomeTeamBehaviour: function () {
    let originalHomeId = $('#OriginalHomeTeamID').val();
    let isCreateMode = (this.mode === 'create');

    $('#hometeam').off('change.sp').on('change.sp', function () {
      let selectedName = ($('#hometeam option:selected').text() || '').toUpperCase();
      let isArchive = selectedName.indexOf('ARCHIVE') === 0;

      $('#myBtn').prop('disabled', isArchive);
      $('#additionalteamgrid :input').prop('disabled', isArchive);

      // In create mode, always keep start date enabled
      if (isCreateMode) {
        $('#startdate').prop('disabled', false);
        return;
      }

      let currentHomeId = $(this).val();

      if (originalHomeId && String(currentHomeId) !== String(originalHomeId)) {
        // Team changed → unlock start date & set SPTeamID=0 (insert new home row)
        $('#startdate').prop('disabled', false);
        $('#SPTeamID').val(0);
      } else {
        // Team unchanged → keep start locked; leave SPTeamID as the row id
        $('#startdate').prop('disabled', true);
      }
    });
  },

  /**
   * Apply archive lock to additional team controls
   * Disables add/edit buttons when an Archive team is selected as home team
   * @returns {void}
   */
  applyArchiveLock: function () {
    let selectedName = ($('#hometeam option:selected').text() || '').toUpperCase();
    let isArchive = selectedName.indexOf('ARCHIVE') === 0;
    $('#myBtn').prop('disabled', isArchive);
    $('#additionalteamgrid :input').prop('disabled', isArchive);
  },

  /**
   * Sync the additional teams array to a hidden form field as JSON
   * Used to pass additional teams data to the server on form submission
   * @returns {void}
   */
  syncHidden: function () {
    let that = this; // NOSONAR javascript:S7740
    $('#addteamhiddenarray').val(JSON.stringify(that.additional));
  },

  /**
   * Render the additional teams table with current data
   * Builds the table rows for each team in the additional array
   * @returns {void}
   */
  renderAdditionalTeams: function () {
    let $grid = $('#additionalteamgrid');
    if (!$grid.find('thead tr').length) {
      $grid.prepend(
        '<thead><tr>' +
        '<th>Team Name</th><th>Start</th><th>End</th><th>Sort Code</th><th>Available</th><th>Display in View Screens</th><th>Type</th><th>Action</th>' +
        '</tr></thead>'
      );
    }
    var $tbody = $grid.find('tbody');
    $tbody.empty();

    var that = this; // NOSONAR javascript:S7740
    that.additional.forEach(function (r) {
      let isAvail = r.isAvailable ? 'Yes' : 'No';
      let sortStyle = r.sortCode ? 'style="background:' + r.backColor + ';color:' + r.fontColor + ';"' : '';
      let teamNameShort = (r.teamName || '').slice(0, 30);
      let displayInviewScreen = r.doNotDisplayInViewScreen ? 'No' : 'Yes';
      let type = r.isHomeTeam == 2 ? 'Future Home' : 'Additional';

      $tbody.append(
        '<tr data-team-id="' + r.teamId + '" data-start="' + that.escapeHtml(r.start) + '" style="font-weight: normal;">' +
        '<td class="addTeamHeadStyle" title="' + that.escapeHtml(r.teamName || '') + '">' + that.escapeHtml(teamNameShort) + '</td>' +
        '<td class="addTeamHeadStyle">' + that.escapeHtml(r.start) + '</td>' +
        '<td class="addTeamHeadStyle" id="endDate' + r.teamId + '">' + that.escapeHtml(r.end) + '</td>' +
        '<td class="addTeamHeadStyle" id="sortCode' + r.teamId + '" ' + sortStyle + '>' + that.escapeHtml(r.sortCode || '') + '</td>' +
        '<td class="addTeamHeadStyle" id="isAvailable' + r.teamId + '">' + isAvail + '</td>' +
        '<td class="addTeamHeadStyle">' + displayInviewScreen + '</td>' +
        '<td class="addTeamHeadStyle">' + type + '</td>' +
        '<td class="addTeamHeadStyle">' +
        '<i class="fa-solid fa-eye cursor-pointer" id="ddlteamsidview" onclick="openModalPopup(' + r.teamId + ',\'' + that.escapeHtml(r.start) + '\',\'ddlteamsidview\')" style="font-weight: 100; margin-right: 5px;" ></i>' +
        '<i class="fa-solid fa-pen-to-square btnedit cursor-pointer" id="ddlteamsidedit" onclick="openModalPopup(' + r.teamId + ',\'' + that.escapeHtml(r.start) + '\',\'ddlteamsidedit\')" style="font-weight: 100;" ></i>' +
        '</td>' +
        '</tr>'
      );
    });
  },

  /**
   * Validate that home team is selected before allowing additional team modal
   * Shows warning if home team or start date is not set
   * @returns {boolean} - True if validation passes, false otherwise
   */
  guardHomeBeforeModal: function () {
    let homeTeamId = $('#hometeam').val();
    let homeStart = $('#startdate').val();
    let warn = '';
    if (!homeTeamId || homeTeamId === '-1') warn += "You cannot add additional team before selecting home team.\n";
    if (!homeStart) warn += "You cannot add additional team before selecting home team start date.";
    if (warn) { this.alertBox("Warning:\n" + warn); return false; }
    return true;
  },

  /**
   * Reset the additional team modal form to default values
   * Clears all fields and enables inputs for fresh entry
   * @returns {void}
   */
  resetAdditionalModal: function () {
    $('#teamerrorerror').addClass('visually-hidden').text('');
    let $teamSelect = $('#addteam').length ? $('#addteam') : $('#ddlTeams');
    $teamSelect.val('-1');
    $('#addteamstartdate, #addteamenddate, #addteamsortcode').val('');
    $('#addteamBackcolor').val('#cccccc');
    $('#addteamfontcolor').val('#000000');

    // Update Spectrum color picker display to reflect the reset values
    $('#addteamBackcolor').spectrum('set', '#cccccc');
    $('#addteamfontcolor').spectrum('set', '#000000');
    $('#additional_team_type').val(0);
    $('#addteamisavailable, #addteamdefaultbgcolour, #do-not-display-in-view-screen').prop('checked', false);
    $teamSelect.add('#addteamstartdate,#addteamenddate,#addteamsortcode,#addteamBackcolor,#addteamfontcolor,#addteamisavailable,#addteamdefaultbgcolour,#do-not-display-in-view-screen')
      .prop('disabled', false);
  },

  /**
   * Open the additional team modal popup
   * Pre-fills form if editing existing team, or shows blank form for new team
   * @param {number|null} scheduleTeamId - The team ID to edit (null for new team)
   * @param {string|null} scheduleTeamStart - The start date of the team to edit
   * @param {string} sourceBtnId - The ID of the button that triggered the modal
   * @returns {void}
   */
  openModalPopup: function (scheduleTeamId, scheduleTeamStart, sourceBtnId) {
    if (!this.guardHomeBeforeModal()) return;

    let actionType = (!scheduleTeamId && !scheduleTeamStart && $('#useractiontype').val() === 'edit') ? 'createAddTeam'
      : ((sourceBtnId === 'myBtn') ? 'create' : 'edit');

    var that = this; // NOSONAR javascript:S7740
    let currentHomeTeamId = $('#hometeam').val();
    $('#hometeamhidden').val(currentHomeTeamId);
    $('#addTeamTitle').text('Add Additional Scheduling Team');
    this.loadTeams(actionType).always(function () {
      if (currentHomeTeamId) {
        $('#hometeam').val(currentHomeTeamId);
      }

      that.resetAdditionalModal();

      let futureAdditionalTeamDisable = () => {
        //Disable fields
        $('#addteam, #ddlTeams, #addteamenddate, #addteamdefaultbgcolour, #addteamisavailable, #addteamBackcolor, #addteamfontcolor').prop('disabled', true);
      }

      let futureAdditionalSetDates = () => {
        let startDate = $('#startdate').val();
        $('#addteamstartdate').val(
          moment(startDate, 'DD-MM-YYYY')
            .subtract(6, 'weeks')
            .day(6)
            .startOf('day').format('DD-MM-YYYY')
        );
        $('#addteamenddate').val(moment(startDate, 'DD-MM-YYYY')
          .subtract(1, 'days').format('DD-MM-YYYY'));
      };

      // Pre-fill form for edit/view mode
      if (scheduleTeamId && scheduleTeamStart) {
        let row = that.additional.find(function (r) { return String(r.teamId) === String(scheduleTeamId) && String(r.start) === String(scheduleTeamStart); });
        if (row) {
          let $teamSelect = $('#addteam').length ? $('#addteam') : $('#ddlTeams');
          $teamSelect.val(String(row.teamId));
          $('#addteamstartdate').val(row.start);
          $('#addteamenddate').val(row.end === '01-01-9999' ? '' : row.end);
          $('#addteamsortcode').val(row.sortCode || '');
          $('#addteamBackcolor').val(row.backColor || '#cccccc');
          $('#addteamfontcolor').val(row.fontColor || '#000000');

          // Update Spectrum color picker display to reflect the loaded values
          $('#addteamBackcolor').spectrum('set', row.backColor || '#cccccc');
          $('#addteamfontcolor').spectrum('set', row.fontColor || '#000000');
          $('#addteamisavailable').prop('checked', !!row.isAvailable);
          $('#do-not-display-in-view-screen').prop('checked', !!row.doNotDisplayInViewScreen);
          $('#addteamdefaultbgcolour').prop('checked', !!row.isDefaultBG);
          $('#additional_team_type').val(row.isHomeTeam);
          if (row.isHomeTeam == 2) {
            $('#addTeamTitle').text('Update Future Additional Scheduling Team');
            futureAdditionalTeamDisable();
          }
          if (sourceBtnId === 'ddlteamsidview') {
            // View mode - disable all inputs
            $('#addteam, #ddlTeams, #addteamstartdate, #addteamenddate, #addteamsortcode, #addteamBackcolor, #addteamfontcolor, #addteamisavailable, #addteamdefaultbgcolour, #do-not-display-in-view-screen')
              .prop('disabled', true);
            $('#addTeamModal .modal-footer .btn-class').first().hide();
          } else {
            // Edit mode - disable team selection but allow date changes
            $('#addteam, #ddlTeams, #addteamstartdate').prop('disabled', true);
            if (that.mode.toLowerCase() == 'create') {
              futureAdditionalSetDates();
              $('#addteamstartdate').prop('disabled', false);
            }
            $('#addTeamModal .modal-footer .btn-class').first().show().text('Update');
          }
        }
      } else {
        if (sourceBtnId === 'auto_add_future_home_team') {
          this.autoFutureHomeTeamFlow = true;
          $('#addTeamTitle').text('Add Future Additional Scheduling Team');
          // Auto-add future home team - pre-fill team but keep rest of the fields blank
          var $teamSelect = $('#addteam').length ? $('#addteam') : $('#ddlTeams');
          $teamSelect.val($('#hometeam').val());
          futureAdditionalSetDates();
          $('#addteamBackcolor').val('#741b47');
          $('#addteamfontcolor').val('#f3f3f3');

          // Update Spectrum color picker display to reflect the loaded values
          $('#addteamBackcolor').spectrum('set', '#cccccc');
          $('#addteamfontcolor').spectrum('set', '#f3f3f3');
          $('#addteamisavailable').prop('checked', true);
          $('#do-not-display-in-view-screen').prop('checked', false);
          $('#addteamdefaultbgcolour').prop('checked', false);
          $('#additional_team_type').val(2); // Mark as future home team for internal handling
          //Disable fields
          futureAdditionalTeamDisable();
        }
        $('#addTeamModal .modal-footer .btn-class').first().show().text('Save');
      }

      (document.getElementById('addTeamModal') || document.getElementById('myModal')).style.display = 'block';
    });

    // Close handler
    $(document).off('click.addTeamCancel').on('click.addTeamCancel', '#addteamcancel', function () {
      (document.getElementById('addTeamModal') || document.getElementById('myModal')).style.display = 'none';
    });
  },
  getAdditionalTeamFormData: function () {
    const $teamSelect = $('#addteam').length ? $('#addteam') : $('#ddlTeams');

    return {
      teamId: $teamSelect.val(),
      teamText: $teamSelect.find('option:selected').text(),
      start: $('#addteamstartdate').val(),
      end: $('#addteamenddate').val() || '01-01-9999',
      sort: $('#addteamsortcode').val().trim(),
      backCol: $('#addteamBackcolor').val() || '#cccccc',
      fontCol: $('#addteamfontcolor').val() || '#000000',
      isAvail: $('#addteamisavailable').is(':checked'),
      isDefaultBG: $('#addteamdefaultbgcolour').is(':checked'),
      doNotDisplayInViewScreen: $('#do-not-display-in-view-screen').is(':checked'),
      isHomeTeam: Number($('#additional_team_type').val()) || 0
    };
  },
  validateAdditionalTeamInput: function (data) {
    const errs = [];

    if (!data.teamId || data.teamId === '-1') {
      errs.push(
        'Team is a mandatory field for adding additional team. Please select one from the dropdown.'
      );
    }

    if (!data.start) {
      errs.push(
        'For Additional team Start date is a mandatory field. Please select start date.'
      );
    }

    const sd = this.parseUkDate(data.start);
    const ed = this.parseUkDate(data.end);

    if (sd && ed && sd > ed) {
      errs.push('Start Date cannot be greater than End Date.');
    }

    if (sd && !this.withinLowerBound(sd)) {
      errs.push('Start Date cannot be smaller than 02-12-1995.');
    }

    return { errs, sd, ed };
  },
  findDuplicateAdditionalTeam: function (data, sd, ed, isAddMode, originalStart) {
    for (let i = 0; i < this.additional.length; i++) {
      const addTeam = this.additional[i];

      // Skip self when editing
      if (
        !isAddMode &&
        String(addTeam.teamId) === String(data.teamId) &&
        String(addTeam.start) === String(originalStart)
      ) {
        continue;
      }

      if (String(addTeam.teamId) === String(data.teamId)) {
        // Open-ended existing team
        if (addTeam.end === '01-01-9999') {
          return { start: addTeam.start, end: 'Open-ended' };
        }

        const existStart = this.parseUkDate(addTeam.start);
        const existEnd = this.parseUkDate(addTeam.end);

        if (
          sd && existStart && existEnd &&
          (
            (sd >= existStart && sd <= existEnd) ||
            (ed >= existStart && ed <= existEnd) ||
            (sd <= existStart && ed >= existEnd)
          )
        ) {
          return { start: addTeam.start, end: addTeam.end };
        }
      }
    }

    return null;
  },
  validateAgainstHomeTeam: function (data, sd, ed, errs) {
    const homeTeamId = $('#hometeam').val();
    const homeStart = $('#startdate').val();
    const homeEnd = $('#enddate').val() || '01-01-9999';

    if (String(data.teamId) === String(homeTeamId)) {
      const hsd = this.parseUkDate(homeStart);
      const hed = this.parseUkDate(homeEnd);

      if (hsd && hed && sd && ed && sd <= hed && ed >= hsd) {
        errs.push(
          'Home team is (' + data.teamText + ') and Additional team is (' +
          data.teamText + '). Both cannot be same. Please select some other team.'
        );
      }
    }
  },
  applyAdditionalTeamChange: function (data, isAddMode) {
    const that = this; // NOSONAR javascript:S7740

    if (!isAddMode) {
      const idx = that.additional.findIndex(function (r) {
        return String(r.teamId) === String(data.teamId) &&
          String(r.start) === String(data.start);
      });

      if (idx >= 0) {
        const prev = that.additional[idx];
        const next = {
          teamId: Number(data.teamId),
          teamName: prev.teamName,
          start: data.start,
          end: data.end,
          sortCode: data.sort,
          backColor: data.backCol,
          fontColor: data.fontCol,
          isAvailable: !!data.isAvail,
          isDefaultBG: !!data.isDefaultBG,
          doNotDisplayInViewScreen: data.doNotDisplayInViewScreen,
          isHomeTeam: data.isHomeTeam,
          addteamSPTeamID: prev.addteamSPTeamID || prev.spTeamId || 0,
          spTeamId: prev.spTeamId || prev.addteamSPTeamID || 0
        };

        if (JSON.stringify(prev) === JSON.stringify(next)) {
          that.alertBox('There no changes made in this additional team data.');
          return;
        }

        that.additional[idx] = next;
      }
    } else {
      that.additional.push({
        teamId: Number(data.teamId),
        teamName: data.teamText,
        start: data.start,
        end: data.end,
        sortCode: data.sort,
        backColor: data.backCol,
        fontColor: data.fontCol,
        isAvailable: !!data.isAvail,
        isDefaultBG: !!data.isDefaultBG,
        doNotDisplayInViewScreen: data.doNotDisplayInViewScreen,
        isHomeTeam: data.isHomeTeam,
        addteamSPTeamID: 0,
        spTeamId: 0
      });
    }

    that.syncHidden();
    that.renderAdditionalTeams();

    const btnText = (that.mode === 'edit') ? 'Update Person' : 'Create Person';
    that.alertBox(
      'Please click ' + btnText + ' on home team tab to save it permanently.'
    );

    (document.getElementById('addTeamModal') ||
      document.getElementById('myModal')).style.display = 'none';
  },
  runServerValidation: function (data, sd, isAddMode, onSuccess) {
    const that = this; // NOSONAR javascript:S7740
    const validateUrl = this.urls.validateAdditionalTeam;

    if (!validateUrl) {
      onSuccess();
      return;
    }

    this.setCsrfHeader();

    const payload = {
      schedulepersonid: this.personId || 0,
      ddlteamsid: Number(data.teamId),
      addteamstartdate: data.start,
      addteamenddate: data.end || '01-01-9999'
    };

    $.post(validateUrl, payload)
      .done(function (res) {
        if (res && (res.ok === false || res.status === 0)) {
          that.alertBox(
            'WARNING:\n' + (res.message || res.strStatus || 'Validation failed')
          );
          return;
        }

        const modalTeamTypeVal = Number($('#additional_team_type').val() || 0);
        const isFutureAdditionalTeam = (modalTeamTypeVal === 2);

        if (
          isAddMode &&
          !isFutureAdditionalTeam &&
          res &&
          res.homeTeamFirstStartDate
        ) {
          const homeFirstStart = that.parseUkDate(
            res.homeTeamFirstStartDate.StartDateHomeTeamFirst
          );

          if (homeFirstStart && sd && sd < homeFirstStart) {
            that.alertBox(
              'WARNING:\nStart Date of an Additional Team cannot be before the start date of the first Home Team. Please select a date on or after ' +
              res.homeTeamFirstStartDate.StartDateHomeTeamFirst + '.'
            );
            return;
          }
        }

        onSuccess();
      })
      .fail(function () {
        onSuccess();
      });
  },
  /**
    * Save additional team from modal form
    * Validates input, optionally calls server validation, and adds/updates team in the array
    * @returns {void}
    */
  saveAdditionalTeamFromModal: function () {
    const data = this.getAdditionalTeamFormData();

    const modeText = $('#addTeamModal .modal-footer .btn-class').first().text();
    const isAddMode = (modeText === 'Save');
    const originalStart = isAddMode ? null : data.start;

    const validation = this.validateAdditionalTeamInput(data);
    let errs = validation.errs;

    const duplicate = this.findDuplicateAdditionalTeam(
      data,
      validation.sd,
      validation.ed,
      isAddMode,
      originalStart
    );

    if (duplicate) {
      errs.push(
        'Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn\'t lie on or between (' +
        duplicate.start + ' to ' + duplicate.end + ') for the Additional Teams.'
      );
    }

    this.validateAgainstHomeTeam(data, validation.sd, validation.ed, errs);

    if (errs.length) {
      this.alertBox('WARNING:\n' + errs.join('\n'));
      return;
    }

    this.runServerValidation(
      data,
      validation.sd,
      isAddMode,
      this.applyAdditionalTeamChange.bind(this, data, isAddMode)
    );
  },

  /**
   * Transform additional teams array to the format expected by the stored procedure
   * Converts dates to dd/MM/yyyy format and maps fields to SP parameter names
   * @param {Array} additional - Array of additional team objects
   * @param {number} auditUserId - The user ID for audit trail
   * @returns {Array} - Transformed array for stored procedure
   */
  toSpAdditionalArray: function (additional, auditUserId) {
    /**
     * Format date for SQL Server (dd/MM/yyyy)
     * @param {string} d - Date in dd-mm-yyyy format
     * @returns {string} - Formatted date string
     */
    const fmt103 = (d) => {
      if (!d) return '';
      const [dd, mm, yyyy] = d.split('-');
      return `${dd}/${mm}/${yyyy}`;
    };

    return (additional || []).map(item => ({
      // CRITICAL for updates: carry additional row SPTeamID from details()
      addteamSPTeamID: item.addteamSPTeamID || item.spTeamId || 0,

      addteamsid: Number(item.teamId),
      addteamstartdate: fmt103(item.start),
      addteamenddate: fmt103(item.end || '01-01-9999'),
      addteamsortcode: item.sortCode || null,
      addteamBackcolor: item.backColor || null,
      addteamfontcolor: item.fontColor || null,
      addteamisavailable: item.isAvailable ? 1 : 0,
      addteamCreatedBy: Number(auditUserId || 0),
      addteamCreatedDate: '',
      addteamLastUpdatedBy: Number(auditUserId || 0),
      addteamLastUpdatedDate: '',
      addteamupdate: (item.addteamSPTeamID || item.spTeamId) ? 1 : 0,
      addteamIsDefaultBGColour: item.isDefaultBG ? 1 : 0,
      doNotDisplayInViewScreen: item.doNotDisplayInViewScreen ? 1 : 0,
      isHomeTeam: item.isHomeTeam || 0
    }));
  },

  /**
   * Save the scheduled person form data to the server
   * Validates input, builds payload, and makes AJAX request to create/update
   * @returns {void}
   */
  saveScheduledPerson: function (payload) {
    let that = this; // NOSONAR javascript:S7740

    // Build payload matching controller expectations
    //let payload = that.getScheduledPersonPayload();
    //if (!payload) return; // validation failed in payload builder
    
// If payload is not passed in, build it once
  if (!payload) {
    const result = that.getScheduledPersonPayload();
    if (!result.valid) {
      return;
    }
    payload = result.payload;
  }

    if (that.mode === 'edit' && that._autoFutureHomeTeamFlow) {
      $('#startdate').prop('disabled', true);
    }

    let url = (this.mode === 'edit')
      ? (this.urls.update || '')
      : (this.urls.store || '');

    if (!url) { this.alertBox('Save/Update URL is not configured.'); return; }

    this.setCsrfHeader();

    $.ajax({
      url: url,
      method: 'POST',
      data: payload,
      headers: { 'Accept': 'application/json' },

      success: function (data) {
        if (data.success) {
          that.alertBox(data.message);
          setTimeout(() => {
            globalThis.location.href = data.redirect;
          }, 800);
        } else {
          that.alertBox(data.message || 'Failed to save Scheduled Person.');
        }
      },

      error: function (xhr) {
        console.log('422 body:', xhr.responseText || xhr.responseJSON);
        let message = xhr.responseJSON?.message || 'Failed to save Scheduled Person.';
        if (xhr.responseJSON?.errors) {
          let errors = xhr.responseJSON.errors;
          let errorMessages = [];
          Object.keys(errors).forEach(function (key) {
            errors[key].forEach(function (msg) {
              errorMessages.push(msg);
            });
          });
          message = errorMessages.join('\n');
        } else if (xhr.responseJSON?.success === false) {
          message = xhr.responseJSON.message || 'Failed to save Scheduled Person.';
        }
        that.alertBox(message);
      }
    });
  },
  /**
   * Get form data for the scheduled person from the input fields
   * Trims and formats the data as needed for validation and payload building
   * @returns {Object} - An object containing the form data for the scheduled person
   */
  getScheduledPersonFormData: function () {
    return {
      first: $('#dispFirstName').val().trim(),
      last: $('#dispLastName').val().trim(),
      homeId: $('#hometeam').val() || $('#hometeamhidden').val(),
      start: $('#startdate').val(),
      end: $('#enddate').val() || '01-01-9999',
      sort: $('#sortcode').val(),
      back: $('#hometeambackcolour').val() || '#eeeeee',
      font: $('#hometeamfontcolour').val() || '#000000',
      admin: $('#adminnotes').val() || '',
      fwa: $('#fwanotes').val() || '',
      defaultBg: $('#defaultbgcolour').is(':checked'),
      addLeave: $('#additionalleave').is(':checked'),
      originalHomeId: $('#OriginalHomeTeamID').val(),
      originalHomeStart: $('#OriginalHomeStartDate').val()
    };
  },
  /**
   *  Validate the scheduled person form input before submission
  */
  validateScheduledPersonInput: function (data) {
    const errs = [];

    if (!data.first) {
      errs.push('Display First name is a mandatory field for creating new record.');
    }

    if (!data.last) {
      errs.push('Display Last name is a mandatory field for creating new record.');
    }

    if (!data.homeId || data.homeId === '-1') {
      errs.push('Team is a mandatory field for home team. Please select one from the dropdown.');
    }

    if (!data.start) {
      errs.push('For home team Start date is a mandatory field. Please select start date.');
    }

    const sd = this.parseUkDate(data.start);

    // Home team change validation (edit mode)
    if (
      this.mode === 'edit' &&
      data.originalHomeId &&
      String(data.homeId) !== String(data.originalHomeId) &&
      data.originalHomeStart
    ) {
      const originalSd = this.parseUkDate(data.originalHomeStart);
      if (originalSd && sd && sd.getTime() <= originalSd.getTime()) {
        errs.push(
          'New home team Start Date can not be before or same as current home team Start Date.'
        );
      }
    }

    if (sd && !this.withinLowerBound(sd)) {
      errs.push('Start Date cannot be smaller than 02-12-1995.');
    }

    return { errs, sd };
  },
  /** resolveSpTeamId determines the SPTeamID to send to the server based on whether the home team has changed in edit mode
  */ 
  resolveSpTeamId: function (data) {
    if (
      this.mode === 'edit' &&
      String(data.homeId) === String(data.originalHomeId)
    ) {
      return Number($('#SPTeamID').val() || 0);
    }
    return 0;
  },
  /**
   * Build the payload for creating/updating a scheduled person based on form data and additional teams
  */
  buildScheduledPersonPayload: function (data, spTeamId) {
    const auditUserId = $('#getpersonid').val() || 0;
    const additionalForSp = this.toSpAdditionalArray(this.additional, auditUserId);

    return {
      SPTeamID: spTeamId,
      DisplayFirstName: data.first,
      DisplayLastName: data.last,
      DisplayName: data.first + ' ' + data.last,
      SchedulingTeamID: data.homeId,
      HomeTeamStartDate: this.toIso(data.start),
      HomeTeamEndDate: this.toIso(data.end),
      HomeTeamSortCode: data.sort,
      HomeTeamBackColour: data.back,
      HomeTeamFontColour: data.font,
      HomeTeamAdminNotes: data.admin,
      HomeTeamFWANotes: data.fwa,
      ActionType: this.mode || 'create',
      ScheduledPersonID: this.personId || 0,
      AuditUserID: auditUserId,
      AdditionalTeamArray: JSON.stringify(additionalForSp),
      IsDefaultBGColour: data.defaultBg ? 1 : 0,
      IsAdditionalLeave: data.addLeave ? 1 : 0
    };
  },
  /**
   * Get Payload for creating/updating scheduled person from form fields
   */
  getScheduledPersonPayload: function () {
    const data = this.getScheduledPersonFormData();
    const validation = this.validateScheduledPersonInput(data);

    // Always return an object
    if (validation.errs.length) {
      this.alertBox('*Warning*\n' + validation.errs.join('\n'));
      return {
        valid: false,
        payload: null
      };
    }

    const spTeamId = this.resolveSpTeamId(data);
    return {
      valid: true,
      payload: this.buildScheduledPersonPayload(data, spTeamId)
    };
  },

  /**
   * Open the staff details edit modal and populate fields from the preview table
   * @returns {void}
   */
  openStaffEditModal: function () {
    // Populate modal with current staff details from the preview table
    $('#staff_firstname').val($('#js_forename').text() || '');
    $('#staff_surname').val($('#js_surname').text() || '');
    $('#staff_midname').val($('#js_midname').text() || '');
    $('#staff_prefname').val($('#js_prefname').text() || '');
    $('#staff_designation').val($('#js_designation').text() || '');
    $('#staff_netlogin').val($('#js_netlogin').text() || '');

    // Show the modal
    $('#staffEditModal').show();
  },

  /**
   * Save staff details from the modal to the server
   * Updates the preview table with new values on success
   * @returns {void}
   */
  saveStaffDetails: function () {
    var that = this; // NOSONAR javascript:S7740
    const payload = {
      id: this.personId || 0,
      FirstName: $('#staff_firstname').val(),
      Surname: $('#staff_surname').val(),
      MiddleName: $('#staff_midname').val(),
      PreferredName: $('#staff_prefname').val(),
      Designation: $('#staff_designation').val()
    };

    const url = this.urls.updateStaffDetails;
    if (!url) {
      this.alertBox('Staff details update URL is not configured.');
      return;
    }

    this.setCsrfHeader();

    $.ajax({
      url: url,
      method: 'POST',
      data: payload,
      headers: { 'Accept': 'application/json' },

      success: function (data) {
        if (data.success) {
          that.alertBox(data.message);
          $('#staffEditModal').hide();

          // Update the preview table with new values
          $('#js_forename').text(payload.FirstName || '');
          $('#js_surname').text(payload.Surname || '');
          $('#js_midname').text(payload.MiddleName || '');
          $('#js_prefname').text(payload.PreferredName || '');
          $('#js_designation').text(payload.Designation || '');
        } else {
          that.alertBox(data.message || 'Failed to save staff details.');
        }
      },

      error: function (xhr) {
        let message = xhr.responseJSON?.message || 'Failed to save staff details.';
        that.alertBox(message);
      }
    });
  },

  /**
   * Convert date from UK format (dd-mm-yyyy) to ISO format (yyyy-mm-dd)
   * Used for SQL Server date parameters
   * @param {string} d - Date in dd-mm-yyyy format
   * @returns {string} - Date in yyyy-mm-dd format or empty string
   */
  toIso: function (d) {
    if (!d) return '';
    const [dd, mm, yyyy] = d.split('-');
    return `${yyyy}-${mm}-${dd}`;
  },

  /**
   * Enable the Staff Details and Scheduling Team History tabs
   * Called after successfully creating a scheduled person
   * @returns {void}
   */
  enableStaffAndTeamTabs: function () {
    // Enable Staff Details tab (tab 2)
    $('.staff_detail').removeClass('buttonDisabled').addClass('buttonEnabled');

    // Enable Scheduling Team History tab (tab 3)
    $('.schedule-person-team').removeClass('buttonDisabled').addClass('buttonEnabled');
  },

  /**
     * Navigate back to scheduled people search/index page
     * @param {number|string} teamId - Optional team ID for filtering results
     * @param {number|string} userId - Optional user ID parameter (legacy, unused)
     * @returns {void}
     */
  goBack: function (teamId, userId) {
    const baseUrl = this.urls.index;
    const params = new URLSearchParams();
    if (teamId && teamId !== '0') {
      params.append('selectedteamid', teamId);
    }
    if (userId && userId !== '') {
      params.append('selecteduserid', userId);
    }
    const url = params.toString()
      ? `${baseUrl}?${params.toString()}`
      : baseUrl;

    globalThis.location.href = url;
  }
};

module.exports = ScheduledPeopleVar;
