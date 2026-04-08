/**
 * Tab List Module for Scheduled People Search
 * Handles search functionality, team filtering, and displaying scheduled people list
 * Usage: new TabListModule({ urls: {...} })
 */

let TabListModule = function (opts = {}) {
  this.urls = {
    index: '',
    search: '',
    create: '',
    permissions: '',
    ...opts.urls
  };
  this.actionType = '';
  this.teamID = '';
  this.userID = '';
  this.schedulepersonid = null;
  this._a11y = new DatatableAccessibility();
  this._select2Ui = new Select2Ui();
  this.init();
};

TabListModule.prototype = {
  /**
   * Initialize the module
   */
  init: function () {
    let that = this; // NOSONAR javascript:S7740

    // Expose goBack globally for button click handler in header.blade.php
    window.goBack = function (teamId, userId) { that.goBack(teamId, userId); };

    // Initialize select2 for team dropdown
    $(".division-seclect").each(function () {
      var $select = $(this);
      $select.select2({
        width: "200px",
        dropdownParent: $select.closest(".fields"),
        language: {
          noResults: function () {
            return "Oops, nothing found!";
          }
        }
      });
    });
    that._select2Ui.applyUiFixes(".division-seclect", { width: "200px" });

    // Call by default function
    that.getPagePermission(6);
    that.autocompleteDisplayName();

    // Hide clear button initially
    $("#clearfilterbox").css("visibility", "hidden");

    // Handle form submission (when user presses Enter)
    $("#searchForm").on('submit', function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      that.actionType = 'search';
      that.teamID = $("#teamdropdown").val();
      that.userID = $("#dispalyName").val();
      that.searchClearFilter(that.actionType, that.teamID, that.userID);
      $('.ui-autocomplete').hide();
      return false;
    });

    // Click on add new button create, edit, view
    $("#js_addnewbutton").on('click', function (e) {
      e.stopImmediatePropagation();
      e.preventDefault();
      that.actionType = 'create';
      that.schedulepersonid = null;
      that.CreateScheduledPerson(that.actionType, that.schedulepersonid);
    });

    // Enable disabled button of search start
    if ($("#teamdropdown option:selected").val() == '' || $("#dispalyName").val() == '') {
      that.toogleSearchButton();
    }

    $("#dispalyName").on('keyup', function (e) {
      e.stopImmediatePropagation();
      e.preventDefault();
      // Declare action variable explicitly using let or const
      let action = 'Enable'; 
      that.toogleSearchButton(action);
      if ($("#dispalyName").val() == '' && $("#teamdropdown option:selected").val() == '') {
        that.toogleSearchButton();
      }
      setTimeout(function () {
        $('.ui-menu.ui-autocomplete').css('overflow-y', 'auto').css('overflow-x', 'hidden').css('max-height', '50%').css('min-height', 'auto').css('width', '13.5%');
      }, 700);
    });

    // Search the scheduled people
    $("#search").on('click', function (e) {
      e.stopImmediatePropagation();
      e.preventDefault();
      // Define the variable
      that.actionType = 'search';
      that.teamID = $("#teamdropdown").val();
      that.userID = $("#dispalyName").val();

      that.searchClearFilter(that.actionType, that.teamID, that.userID);
    });

    $("#teamdropdown").on('change', function () {
      let teamText = $("#teamdropdown option:selected").text();
      if (teamText.toLowerCase() == 'archive') {
        $("#excludeNoTeam").attr("disabled", true);
        $("#excludeNoTeamVal").val(0);
      }
      else {
        $("#excludeNoTeam").removeAttr("disabled");
        if ($('#excludeNoTeam')[0].checked) {
          $("#excludeNoTeamVal").val(1);
        } else {
          $("#excludeNoTeamVal").val(0);
        }
      }
      that.teamID = $("#teamdropdown").val();
      that.userID = $("#dispalyName").val();
      that.autocompleteDisplayName(that.teamID);
      let actionString = 'Enable';

      if ($("#dispalyName").val() == '' && $("#teamdropdown").val() == '') {
        that.toogleSearchButton();
        that.actionType = 'claerfilter';
        that.teamID = '';
        that.userID = '';
      } else {
        that.toogleSearchButton(actionString);
        that.actionType = 'search';
      }
      that.searchClearFilter(that.actionType, that.teamID, that.userID);
    });

    // Clear the filter
    $(document).on('click', '#clearfilter', function (e) {
      e.stopImmediatePropagation();
      e.preventDefault();
      // Define the variable
      that.actionType = 'claerfilter';
      that.teamID = '';
      that.userID = '';
      that.searchClearFilter(that.actionType, that.teamID, that.userID);
    });

    // Handle URL parameters for team and user search
    let urlParams = new URLSearchParams(window.location.search);
    let searchTeamId = urlParams.get('searchteamid');
    let searchUserId = urlParams.get('searchuserid');

    if (searchTeamId || searchUserId || $("#teamdropdown").val()) {
      if (searchTeamId == 0 || searchTeamId == '0') {
        searchTeamId = '';
      }
      setTimeout(function () {
        that.searchClearFilter('search', searchTeamId || $("#teamdropdown").val() || '', searchUserId || $("#dispalyName").val() || '');
      }, 500);
    }

    try {
      // Only run if URL didn't already drive a search (keeps behavior predictable)
      const noUrlTeam = !searchTeamId;
      const noUrlUser = !searchUserId;

      if (noUrlTeam && noUrlUser) {
        const savedTeam = sessionStorage.getItem("scheduled_people_team");
        const savedUser = sessionStorage.getItem("scheduled_people_user");

        if (savedTeam || savedUser) {
          if (savedTeam) {
            $("#teamdropdown").val(savedTeam).trigger("change.select2");
          }
          if (savedUser) {
            $("#dispalyName").val(savedUser);
          }

          // Trigger automatic search with restored values
          that.searchClearFilter('search', savedTeam || '', savedUser || '');

          // Clear stored state after use
          sessionStorage.removeItem("scheduled_people_team");
          sessionStorage.removeItem("scheduled_people_user");
        }
      }
    } catch (e) {
      // sessionStorage might be blocked; ignore
    }

    // Handle click events for view and edit icons (delegated event)
    $(document).on('click', '.view-scheduled-person-icon', function (e) {
      e.preventDefault();
      let scheduledPersonId = $(this).data('scheduled-person-id');
      let teamId = $(this).data('team-id') || 0;
      that.CreateScheduledPerson('view', scheduledPersonId, teamId);
    });

    $(document).on('click', '.edit-scheduled-person-icon', function (e) {
      e.preventDefault();
      let scheduledPersonId = $(this).data('scheduled-person-id');
      let teamId = $(this).data('team-id') || 0;
      that.CreateScheduledPerson('edit', scheduledPersonId, teamId);
    });
  },

  /**
   * Get page permission
   */
  getPagePermission: function (pageId) {
    let url = this.urls.permissions || '';
    $.ajax({
      url: url,
      type: "GET",
      dataType: "json",
      data: {
        'pageId': pageId,
      },
      success: function (data) {
        if (data.status == 'success') {
          if (data.permissions.canview == 0) {
            window.location.href = data.redirect;
          }
          // Button visibility is now handled by blade file (always shown by default)
        }
      }
    });
  },

  /**
   * Autocomplete for display name
   * Uses route from urls config (passed from Blade template)
   */
  autocompleteDisplayName: function (teamID) {
    let searchUrl = this.urls.search || '';
    $("#dispalyName").autocomplete({
      source: function (request, response) {
        $.getJSON(searchUrl, { excludeNoTeam: $('#excludeNoTeamVal').val(), teamid: teamID, term: $('#dispalyName').val() },
          response);
      },
      minLength: 2,
      select: function (event, ui) {
        $('#dispalyName').val(ui.item.value);
        let searchTeamID = $("#teamdropdown").val();
        let searchUserName = ui.item.value;
        let actionType = 'search';
        if (searchTeamID != '' && searchUserName != '') {
          that.searchClearFilter(actionType, searchTeamID, searchUserName);
        } else {
          $("#search").trigger('click');
        }
      }
    })
      .on('mouseup', function () {
        $(this).select();
      });
  },

  /**
   * Search and clear filter functionality with DataTables pagination
   * Uses route from urls config (passed from Blade template)
   */
  searchClearFilter: function (actionType, teamID, userID) {
    let that = this; // NOSONAR javascript:S7740
    let searchUrl = this.urls.search || '';
    console.log('searchClearFilter called:', { actionType, teamID, userID, excludeNoTeam: $('#excludeNoTeamVal').val() });

    // If userID is empty, send the request without it (i.e., do not filter by name)
    if (!userID) {
      userID = '';  // Ensure it's explicitly empty
    }

    // Destroy existing DataTable if it exists
    let $tbl = $('#scheduledpeoplelist');
    if ($.fn.DataTable.isDataTable($tbl)) {
      $tbl.DataTable().destroy();
    }

    // Initialize DataTable with AJAX - simple pagination like scheduling group list
    that.dataTable = $tbl.DataTable({
      bPaginate: true,
      iDisplayLength: 20,
      lengthChange: false,
      destroy: true,
      order: [[0, 'asc']],
      searching: false, // Disable search box in table header
      info: false, // Disable "Showing X of Y entries" info text
      initComplete: function () {
        if (that._a11y) {
          that._a11y.applyHeaderAccessibilityFixes(that.dataTable);
        }
      },
      drawCallback: function () {
        if (that._a11y) {
          that._a11y.applyHeaderAccessibilityFixes(that.dataTable);
        }
      },
      createdRow: function (row, data, dataIndex) {
        // Add hover effects like legacy code
        $(row).on('mouseover', function () {
          $(this).addClass('highlightOrange');
        });
        $(row).on('mouseout', function () {
          $(this).removeClass('highlightOrange');
        });
      },
      ajax: {
        url: searchUrl,
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: {
          'selectedTeamID': teamID,
          'selectedUserName': userID,
          'actionType': actionType,
          'excludeNoTeam': $('#excludeNoTeamVal').val()
        },
        dataSrc: function (json) {
          // Handle legacy response format {status: 'success', data: [...]}
          let peopleData = json;
          if (json.status === 'success' && json.data) {
            peopleData = json.data;
          }

          // Update clear filter visibility
          if (actionType == 'search') {
            $("#clearfilterbox").css("visibility", "visible");
          } else {
            that.toogleSearchButton();
            $("#clearfilterbox").css("visibility", "hidden");
            that.autocompleteDisplayName();
            $('#teamdropdown').val('').trigger('change.select2');
            $("#dispalyName").val('');
          }

          // Handle archive team checkbox state
          if ($("#teamdropdown option:selected").text().toLowerCase() == 'archive') {
            $("#excludeNoTeam").attr("disabled", true);
            $("#excludeNoTeamVal").val(0);
          } else {
            $("#excludeNoTeam").removeAttr("disabled");
            if ($('#excludeNoTeam')[0].checked) {
              $("#excludeNoTeamVal").val(1);
            } else {
              $("#excludeNoTeamVal").val(0);
            }
          }

          return peopleData || [];
        }
      },
      columns: [
        { data: 'DisplayName', title: 'Display Name' },
        { data: 'Forename', title: 'Forename' },
        { data: 'Surname', title: 'Surname' },
        { data: 'NetLogin', title: 'Network ID' },
        { data: 'InternalEmail', title: 'BBC Email Address' },
        { data: 'TeamName', title: 'Home Scheduling Team' },
        {
          data: null,
          title: 'Actions',
          render: function(data, type, row) {
            // View icon is always shown
            let viewIcon = '<i class="fa fa-eye view-scheduled-person-icon" data-scheduled-person-id="' + row.ScheduledPersonID + '" data-team-id="' + (row.TeamID || 0) + '" role="button" aria-label="View scheduled person" tabindex="0" title="View"></i>';
            
            // Edit icon is only shown if the person's home team matches the selected team filter
            // This ensures users can only edit scheduled people in their selected team
            let selectedTeam = String($("#teamdropdown").val() || '');
            let personTeam = String(row.TeamID || '');
            let editIcon = '';
            
            if (selectedTeam && selectedTeam === personTeam) {
              editIcon = '&nbsp;<i class="fas fa-edit edit-scheduled-person-icon" data-scheduled-person-id="' + row.ScheduledPersonID + '" data-team-id="' + (row.TeamID || 0) + '" role="button" aria-label="Edit scheduled person" tabindex="0" title="Edit"></i>';
            }
            
            return '<div style="width: 90px; text-align: center; color: #0000ee;font-size: 10px;">' +
                   viewIcon + editIcon +
                   '</div>';
          }
        }
      ]
    });
  },

  /**
   * Enable disabled button of search
   */
  toogleSearchButton: function (action) {
    if (action == 'Enable') {
      $("#search").removeClass('buttonDisabled').addClass('buttonEnabled');
    } else {
      $("#search").removeClass('buttonEnabled').addClass('buttonDisabled');
    }
  },

  /**
   * Call CreateScheduledPerson page - navigate directly to create page
   */
  CreateScheduledPerson: function (actionType, schedulepersonid, teamId) {
    let selectedteamid = teamId || $("#teamdropdown").val();
    let searchUserId = $('#dispalyName').val();

    let basePath = '/mvc-app/setup/scheduled-people';
    let targetUrl;

    try {
      sessionStorage.setItem("scheduled_people_team", $("#teamdropdown").val() || "");
      sessionStorage.setItem("scheduled_people_user", $("#dispalyName").val() || "");
    } catch (e) {
      // sessionStorage might be blocked; fail silently
    }

    if (actionType === 'edit' && schedulepersonid) {
      targetUrl = `${basePath}/${schedulepersonid}/edit`;
    }
    else if (actionType === 'create') {
      targetUrl = `${basePath}/create`;
    } else {
      let createUrl = this.urls.create || '';
      let params = [];
      if (actionType) params.push('useraction=' + encodeURIComponent(actionType));
      if (schedulepersonid) params.push('schedulepersonid=' + schedulepersonid);
      if (selectedteamid) params.push('selectedteamid=' + selectedteamid);
      if (searchUserId) params.push('selecteduserid=' + encodeURIComponent(searchUserId));

      if (params.length > 0) {
        createUrl += '?' + params.join('&');
      }
      targetUrl = createUrl;
    }

    // Navigate directly to the create page (this ensures all JavaScript is properly loaded)
    window.location.href = targetUrl;
  },

  /**
   * Highlight table row
   */
  highlightTableRow: function (thisVal) {
    $(thisVal).addClass('highlightOrange');
  },

  /**
   * Unhighlight table row
   */
  unHighlightTableRow: function (thisVal) {
    $(thisVal).removeClass('highlightOrange');
  },

  /**
   * Custom alert function for displaying error messages
   */
  customAlert: function (message) {
    alert(message);
  },

  /**
   * Navigate back to scheduled people search/index page - preserves current search state
   */
  goBack: function (teamId, userId) {
    const teamIdCurrent = $("#teamdropdown").val() || '';
    const userIdCurrent = $("#dispalyName").val() || '';
    const baseUrl = this.urls.index || '/mvc-app/setup/scheduled-people';
    const params = new URLSearchParams();

    if (teamIdCurrent && teamIdCurrent !== '') {
      params.append('searchteamid', teamIdCurrent);
    }
    if (userIdCurrent) {
      params.append('searchuserid', userIdCurrent);
    }

    const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
    window.location.href = finalUrl;
  }

};

module.exports = TabListModule;
