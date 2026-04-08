/**
 * Team History Module
 * Handles loading and displaying scheduling team history for a scheduled person
 * Uses DataTables to display the history data
 */

// Global confirmation modal function for delete operations
function openConfirmDeleteModal(message, yesCallback) {
  // Remove existing modal dialog if any
  $('#confirmDeleteModal').remove();

  let modalHtml = '<div id="confirmDeleteModal" title="Please Confirm">' +
    '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' + message + '</p>' +
    '</div>';

  // Add modal to body
  $('body').append(modalHtml);

  // Create jQuery UI Dialog
  $('#confirmDeleteModal').dialog({
    resizable: false,
    height: "auto",
    width: 400,
    modal: true,
    buttons: {
      "Yes": function () {
        $(this).dialog("close");
        if (typeof yesCallback === 'function') {
          yesCallback();
        }
      },
      "No": function () {
        $(this).dialog("close");
      }
    },
    close: function () {
      $(this).remove();
    }
  });
}

// Global function to close confirm delete modal
function closeConfirmDeleteModal(shouldClose) {
  if (shouldClose && $('#confirmDeleteModal').length > 0) {
    $('#confirmDeleteModal').dialog("close");
  }
}

// Make functions globally available
globalThis.openConfirmDeleteModal = openConfirmDeleteModal;
globalThis.closeConfirmDeleteModal = closeConfirmDeleteModal;

var TeamHistory = function (p) {
  this.personId = p?.personId || 0;
  this.urls = p?.urls || {};
  this.dataTable = null;
  this.initialized = false;
  this._a11y = new DatatableAccessibility();

  // Set CSRF token for AJAX requests
  this.setCsrfHeader();

  this.init();
};

TeamHistory.prototype = {
  /**
   * Set CSRF token header for AJAX requests
   */
  setCsrfHeader: function () {
    let token = $('meta[name="csrf-token"]').attr('content');
    if (token) $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
  },

  /**
   * Initialize the team history functionality
   */
  init: function () {
    var that = this; // NOSONAR javascript:S7740

    // Disable/enable tab button based on person ID (legacy behavior)
    this.checkPersonIdAndToggleTab();

    // Set up delete button handler
    this.setupDeleteHandler();

    // Set up tab UI state handlers (legacy behavior)
    this.setupTabStateHandlers();

    // Set up filter change handler (legacy behavior - store filter state)
    this.setupFilterHandlers();

    // Set up click handler for the team history tab
    $(document).off('click.teamHistoryTab').on('click.teamHistoryTab', '.schedule-person-team', function (e) {
      // Only initialize if tab is enabled (not disabled)
      if ($(this).hasClass('buttonDisabled')) {
        return;
      }

      // Try to get person ID from hidden field first (set after saving scheduled person)
      let personId = $('#newpersonid').val() || that.personId;
      if (personId && personId > 0) {
        that.personId = personId;
        that.loadTeamHistory();
      }
    });

    // Also handle tab activation via jQuery UI tabs
    $(document).on('tabsactivate', '#schedulePersonTab', function (event, ui) {
      let tabId = ui.newPanel.attr('id');
      if (tabId === 'tabs-3') { // Team History tab
        let personId = $('#newpersonid').val() || that.personId;
        if (personId && personId > 0) {
          that.personId = personId;
          that.loadTeamHistory();
        }
      }
    });

    // Also try to load immediately if personId is already set (edit mode)
    if (this.personId && this.personId > 0) {
      // Small delay to ensure DOM is ready
      setTimeout(function () {
        that.loadTeamHistory();
      }, 100);
    }
  },

  /**
   * Check person ID and enable/disable tab button (legacy behavior)
   */
  checkPersonIdAndToggleTab: function () {
    let newpersonid = $('#newpersonid').val();
    if (!newpersonid || newpersonid.length === 0) {
      $(".schedule-person-team").addClass('buttonDisabled');
    } else {
      $(".schedule-person-team").removeClass('buttonDisabled');
    }
  },

  /**
   * Set up tab UI state handlers (legacy behavior)
   */
  setupTabStateHandlers: function () {
    // First tab click handler
    $(".first-tab ul li:first").on('click.tabState', function (e) {
      $(this).addClass("ui-tabs-active ui-state-active ui-state-focus");
      $(this).attr("aria-expanded", true);
      $(this).attr("aria-selected", true);
      $(".schedule-person-team").removeClass("ui-tabs-active ui-state-active ui-state-focus");
      $(".schedule-person-team").attr("aria-expanded", false);
      $(".schedule-person-team").attr("aria-selected", false);
    });

    // Schedule person team tab click handler
    $(".schedule-person-team").on('click.tabState', function (e) {
      $(this).addClass("ui-tabs-active ui-state-active ui-state-focus");
      $(this).attr("aria-expanded", true);
      $(this).attr("aria-selected", true);
      $(".first-tab ul li:first").removeClass("ui-tabs-active ui-state-active ui-state-focus");
      $(".first-tab ul li:first").attr("aria-expanded", false);
      $(".first-tab ul li:first").attr("aria-selected", false);
    });

    // Staff detail tab click handler
    $(".staff_detail").on('click.tabState', function (e) {
      $(this).addClass("ui-tabs-active ui-state-active ui-state-focus");
      $(this).attr("aria-expanded", true);
      $(this).attr("aria-selected", true);
      $(".contract_history").removeClass("ui-tabs-active ui-state-active ui-state-focus");
      $(".contract_history").attr("aria-expanded", false);
      $(".contract_history").attr("aria-selected", false);
    });

    // Contract history tab click handler
    $(".contract_history").on('click.tabState', function (e) {
      $(this).addClass("ui-tabs-active ui-state-active ui-state-focus");
      $(this).attr("aria-expanded", true);
      $(this).attr("aria-selected", true);
      $(".staff_detail").removeClass("ui-tabs-active ui-state-active ui-state-focus");
      $(".staff_detail").attr("aria-expanded", false);
      $(".staff_detail").attr("aria-selected", false);
    });
  },

  /**
   * Set up filter change handlers (legacy behavior)
   */
  setupFilterHandlers: function () {
    var that = this; // NOSONAR javascript:S7740
    // Store filter state when changed (legacy behavior)
    $(document).on('change', '.yadcf-filter', function () {
      let val = $('option:selected', this).val();
      $('#schedulingTeamHistoryHomeTeamFilterFlag').val(val);
    });
  },

  /**
   * Load team history data and initialize DataTable
   */
  loadTeamHistory: function () {
    var that = this; // NOSONAR javascript:S7740
    let $tbl = $('#scheduleTeamHistory');

    // If already initialized, just redraw
    if (that.dataTable) {
      that.dataTable.draw();
      return;
    }

    // Get the URL - use the one from config or build from personId
    let url = this.urls.teamHistory;
    if (!url && this.personId > 0) {
      url = '/mvc-app/setup/scheduled-people/' + this.personId + '/team-history';
    }

    if (!url) {
      console.error('Team history URL not configured');
      return;
    }

    // Destroy existing DataTable if any
    if ($.fn.DataTable.isDataTable($tbl)) {
      $tbl.DataTable().destroy();
    }

    // Build header dynamically
    let thead = '<thead>' +
      '<tr>' +
      '<th>Scheduling Team</th>' +
      '<th>Type</th>' +
      '<th>Start Date</th>' +
      '<th>End Date</th>' +
      '<th>Sort Code</th>' +
      '<th>Created Date</th>' +
      '<th>Created By</th>' +
      '<th>Last Updated Date</th>' +
      '<th>Last Updated By</th>' +
      '<th>Is Available</th>' +
      '<th>Action</th>' +
      '</tr>' +
      '</thead>';

    // Remove existing thead if any and add new one
    $tbl.find('thead').remove();
    $tbl.prepend(thead);

    // Initialize DataTable with explicit column definitions
    this.dataTable = $tbl.DataTable({
      bPaginate: true,
      iDisplayLength: 20,
      destroy: true,
      lengthChange: false,
      order: [[0, 'desc']],
      dom: 'rtip',
      searching: true,
      columns: [
        { data: 'ScheduleTeam', title: 'Scheduling Team' },
        {
          data: 'HomeTeam', title: 'Type',
          render: function (data, type, row) {
            let isHomeTeamRaw = row.IsHomeTeam;
            let isHomeTeamNum = Number.parseInt(isHomeTeamRaw);
            if (isHomeTeamNum == 2) return 'Future Home';
            if (isHomeTeamNum == 1) return 'Home';
            return 'Additional';

          }
        },
        { data: 'Startdate', title: 'Start Date' },
        { data: 'Enddate', title: 'End Date' },
        { data: 'SortCode', title: 'Sort Code' },
        { data: 'CreatedDate', title: 'Created Date' },
        { data: 'CreatedBy', title: 'Created By', orderable: true },
        { data: 'LastUpdatedDate', title: 'Last Updated Date' },
        { data: 'LastUpdatedBy', title: 'Last Updated By' },
        { data: 'IsAvailable', title: 'Is Available' },
        {
          data: null, title: 'Action', orderable: false,
          render: function (data, type, row) {
            let endDate = row.Enddate || '';
            let isFarFuture = false;
            if (endDate) {
              let dateParts = endDate.match(/(\d{2})-(\d{2})-(\d{4})/);
              if (dateParts) {
                let endYear = Number.parseInt(dateParts[3]);
                if (endYear > 9998) {
                  isFarFuture = true;
                }
              }
            }

            // Check for Home type (IsHomeTeam = 1) and far future end date
            let homeTeamNum = Number.parseInt(row.IsHomeTeam);
            if (isFarFuture && homeTeamNum === 1 && row.canDelete === 1) {
              return '<a id="schPersonDelete" class="viewaction js_historyschteam" href="javascript:void(0)" title="Delete" value="' + row.TeamID + '" PersonIdHomeTeam="' + row.ScheduledPersonID + '"><i class="fa fa-trash anchor-colour iconstyleallocate7"></i></a>';
            }

            return '';
          }
        }
      ],
      ajax: {
        url: url,
        type: 'GET',
        dataType: 'json',
        headers: {
          'Accept': 'application/json'
        },
        dataSrc: function (json) {
          let data = [];
          if (json.data) {
            data = json.data;
          }
          return data;
        },
        complete: function (xhr, textStatus) {
          if (xhr.status === 200) {
            let filterFlag = $('#schedulingTeamHistoryHomeTeamFilterFlag').val();
            if (filterFlag !== '' && that.dataTable) {
              let filterElement = $('#yadcf-filter--scheduleTeamHistory-1');
              if (filterElement.length) {
                filterElement.val(filterFlag).trigger('change');
              }
            }
          }
        },
        error: function (xhr, status, error) {
          console.error('Error loading team history:', error);
        }
      },
      fnDrawCallback: function (oSettings) {
        if ($('#scheduleTeamHistory tr').length >= 11) {
          $(".dataTables_paginate").css('visibility', 'visible');
          $(".dataTables_length").css("display", "block");
          $("#dataTables_paginate ").css('font-size', '8px');
          $(".dataTables_info").css('display', 'block');
        } else {
          $(".dataTables_info").css('display', 'none');
        }

        if (that._a11y) {
          that._a11y.applyHeaderAccessibilityFixes(that.dataTable);
        }
      },
      initComplete: function (settings, json) {
        let filterSetting = [
          {
            column_number: 1,
            filter_type: 'select',
            data: [
              { value: 'Home', label: 'Home' },
              { value: 'Additional', label: 'Additional' },
              { value: 'Future Home', label: 'Future Home' }
            ],
            clear_button_label: 'Clear Type filters'
          }
        ];

        yadcf.init(that.dataTable, filterSetting);

        if (that._a11y) {
          that._a11y.applyHeaderAccessibilityFixes(that.dataTable);
          that._a11y.addAriaLabelledbyToYadcfTextInputs('scheduleTeamHistory');
          that._a11y.addAriaLabelsToFilterClearButtons('scheduleTeamHistory', filterSetting);
          that._a11y.hideYadcfSelectValuesUntilFocus('scheduleTeamHistory');
        }
      }
    });

    $(".dataTables_length").css("display", "block");

    if (that._a11y) {
      that._a11y.applyHeaderAccessibilityFixes(that.dataTable);
    }

    $('#scheduleTeamHistory').off('draw.dt.a11y').on('draw.dt.a11y', function () {
      if (!that._a11y) return;
      that._a11y.applyHeaderAccessibilityFixes(that.dataTable);
      that._a11y.addAriaLabelledbyToYadcfTextInputs('scheduleTeamHistory');
      that._a11y.hideYadcfSelectValuesUntilFocus('scheduleTeamHistory');
    });
  },

  /**
   * Set up delete button click handler
   */
  setupDeleteHandler: function () {
    var that = this; // NOSONAR javascript:S7740

    $(document).off('click.deleteSchPersonHomeTeam').on('click.deleteSchPersonHomeTeam', '#schPersonDelete', function (event) {
      event.stopImmediatePropagation();
      event.preventDefault();

      let HomeTeamIdDel = $(this).attr('value');
      let personid = $(this).attr('PersonIdHomeTeam');
      
      if (typeof openConfirmDeleteModal !== 'undefined') {
        openConfirmDeleteModal('Do you wish to delete this Home Team?', function () {
          that.checkForConflictingDuties(HomeTeamIdDel, personid);
        });
      }  else {
        if (confirm('Do you wish to delete this Home Team?')) {
          that.checkForConflictingDuties(HomeTeamIdDel, personid);
        }
      }
    });
  },

  /**
   * Check for conflicting duties in Additional Teams before deletion
   */
  checkForConflictingDuties: function (HomeTeamIdDel, personid) {
    var that = this; // NOSONAR javascript:S7740
    let conflictingDutiesUrl = this.urls.conflictingDuties || '/mvc-app/setup/scheduled-people/conflicting-duties';

    $.ajax({
      url: conflictingDutiesUrl,
      type: 'POST',
      dataType: 'json',
      data: {
        'HomeTeamId': HomeTeamIdDel,
        'personid': personid
      },
      success: function (data) {
        console.log('Conflicting duties response:', data);
        
        if (data.success && data.duties && data.duties.length > 0) {
          // Show CHOICE modal BEFORE duties modal
          that.showDeleteChoiceModal(HomeTeamIdDel, personid, data.duties);
        } else {
          // No conflicting duties, proceed with validation
          that.validatePersonOnRota(HomeTeamIdDel, personid);
        }
      },
      error: function (xhr, status, error) {
        console.error('Error checking conflicting duties:', error);
        // If error checking duties, proceed anyway
        that.validatePersonOnRota(HomeTeamIdDel, personid);
      }
    });
  },

  /**
   * Show choice modal when duties exist
   * Choice 1: Keep Additional Team (delete Home only)
   * Choice 2: Remove duties first (current flow)
   */
  showDeleteChoiceModal: function (HomeTeamIdDel, personid, duties) {
    var that = this; // NOSONAR javascript:S7740
    
    // Remove existing choice modal
    if ($('#deleteChoiceModal').length > 0) {
      $('#deleteChoiceModal').dialog('destroy').remove();
    }

    let scheduledPersonName = that.getScheduledPersonName() || 'the scheduled person';
    let homeTeamName = that.getHomeTeamName() || 'the Home Team';
    
    let choiceHtml = '<div id="deleteChoiceModal" title="Duties Found in Additional Team">' +
      '<div style="padding: 20px;">' +
      '<p><strong>There are duties in the Additional (Future Home) Team during this Home Team period.</strong></p>' +
      '<p><strong>[' + scheduledPersonName + ' in ' + homeTeamName + ']</strong></p>' +
      '<p>Please choose:</p>' +
      '<div style="margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">' +
      '  <strong style="color: #337ab7;">Option 1: Keep Additional Team</strong><br>' +
      '  Keep Additional Team record and duties in place. <strong>Delete Home Team only.</strong>' +
      '</div>' +
      '<div style="margin: 10px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f0f8f0;">' +
      '  <strong style="color: #d9534f;">Option 2: Remove Duties First</strong><br>' +
      '  Review and remove duties from Additional Team. Both teams can be removed if no duties remain.' +
      '</div>' +
      '</div>' +
      '</div>';

    $('body').append(choiceHtml);

    $('#deleteChoiceModal').dialog({
      resizable: false,
      height: 'auto',
      width: 500,
      modal: true,
      buttons: {
        'Keep Additional Team (Delete Home Only)': function () {
          $(this).dialog('close');
          // Choice 1: Direct delete Home Team (keep Additional)
          that.deleteHomeTeamKeepAdditional(HomeTeamIdDel, personid);
        },
        'Remove Duties First': function () {
          $(this).dialog('close');
          // Choice 2: Current flow - show duties modal
          that.showConflictingDutiesModal(duties, HomeTeamIdDel, personid);
        },
        Cancel: function () {
          $(this).dialog('close');
        }
      },
      close: function () {
        $(this).remove();
      }
    });
  },

  /**
   * Delete Home Team keeping Additional Team (Choice 1 path)
   */
  deleteHomeTeamKeepAdditional: function (HomeTeamIdDel, personid) {
    var that = this; // NOSONAR javascript:S7740
    let keepAdditionalUrl = this.urls.deleteHomeTeam || '/mvc-app/setup/scheduled-people/delete-home-team';

    $.ajax({
      url: keepAdditionalUrl,
      type: 'POST',
      dataType: 'json',
      data: {
        'HomeTeamId': HomeTeamIdDel,
        'personid': personid,
        'DeleteFromRota': 0
      },
      success: function (data) {
        console.log('Keep Additional delete response:', data);
        
        if (data && data[0]) {
          if (typeof customAlert !== 'undefined') {
            customAlert(data[0].strStatus || 'Home Team deleted. Additional Team kept intact.');
          } else {
            alert(data[0].strStatus || 'Home Team deleted. Additional Team kept intact.');
          }
        } else {
          if (typeof customAlert !== 'undefined') {
            customAlert('Delete response invalid');
          } else {
            alert('Delete response invalid');
          }
        }

        if (that.dataTable) {
          that.dataTable.ajax.reload(null, false);
        }
      },
      error: function (xhr, status, error) {
        console.error('Keep Additional delete error:', error);
        alert('Error deleting Home Team. Please try again.');
      }
    });
  },

  /**
   * Show modal for conflicting duties and allow user to select which to remove
   */
  showConflictingDutiesModal: function (duties, HomeTeamIdDel, personid) {
    var that = this; // NOSONAR javascript:S7740
    
    // Remove existing modal if any
    if ($('#conflictingDutiesModal').length > 0) {
        $('#conflictingDutiesModal').dialog('destroy').remove();
    }

    // Get scheduled person name and home team details from form/table
    let scheduledPersonName = that.getScheduledPersonName() || 'the scheduled person';
    let homeTeamName = that.getHomeTeamName() || 'the Home Team';
    let homeTeamStartDate = that.getHomeTeamStartDate() || '';
    
    // Create the modal HTML with enhanced message
    let modalHtml = that.buildConflictingDutiesModalHtml(duties, scheduledPersonName, homeTeamName, homeTeamStartDate);
    $('body').append(modalHtml);

    let selectedDuties = {};
    let selectedTeams = {};

    // Set up the dialog
    $('#conflictingDutiesModal').dialog({
      resizable: false,
      height: 'auto',
      width: 800,
      modal: true,
      buttons: {
        'Remove Selected Duties': function () {
          // Direct scan checked checkboxes (no selectedDuties dependency)
          let actualDutyIds = [];
          $('#conflictingDutiesList input[type="checkbox"]:checked').each(function() {
            let adDutyId = Number.parseInt($(this).data('duty-id')) || Number.parseInt($(this).val());
            if (adDutyId > 0) {
              actualDutyIds.push(adDutyId);
            }
          });

          console.log('Button click - checked AD IDs:', actualDutyIds);

          if (actualDutyIds.length === 0) {
            alert('No duties selected. Please select duties to remove.');
            return;
          }

          // Show confirmation before removal
          let scheduledPersonName = that.getScheduledPersonName() || 'the scheduled person';
          let confirmMessage = 'The selected duties (' + actualDutyIds.length + ') will be removed from ' + scheduledPersonName + 
            '. They will be moved to \'Unallocated\' unless marked as \'Doesn\'t Need Covering\', which will be cancelled.';
          
          if (typeof openConfirmDeleteModal !== 'undefined') {
            openConfirmDeleteModal(confirmMessage, function () {
              that.removeConflictingDuties(actualDutyIds, personid, HomeTeamIdDel);
            });
          } else if (typeof customConfirmModal !== 'undefined') {
            customConfirmModal(confirmMessage,
              function () {
                that.removeConflictingDuties(actualDutyIds, personid, HomeTeamIdDel);
              },
              function () {
                return;
              }
            );
          } else {
            if (confirm(confirmMessage)) {
              that.removeConflictingDuties(actualDutyIds, personid, HomeTeamIdDel);
            }
          }
          $(this).dialog('close');
        },
        'Cancel': function () {
          $(this).dialog('close');
        }
      },
      close: function () {
        $(this).remove();
      }
    });

    // Set up row click handlers for duty selection
    $('#conflictingDutiesList tr').on('click', function (e) {
      if ($(e.target).is('input[type="checkbox"]')) return;
      
      let checkbox = $(this).find('input[type="checkbox"]');
      checkbox.prop('checked', !checkbox.prop('checked'));
      $(this).toggleClass('selected', checkbox.prop('checked'));
      
      let dutyId = checkbox.val();
      let teamId = checkbox.data('team-id');
      
      if (checkbox.prop('checked')) {
        selectedDuties[dutyId] = true;
        
        // Auto-select all duties from this team
        $('#conflictingDutiesList input[data-team-id="' + teamId + '"]').each(function () {
          $(this).prop('checked', true);
          $(this).closest('tr').addClass('selected');
          selectedDuties[$(this).val()] = true;
        });
        selectedTeams[teamId] = true;
      } else {
        selectedDuties[dutyId] = false;
        
        // Check if this team still has selected duties
        let teamHasSelected = Object.keys(selectedDuties).some(function(id) {
          let elem = $('#conflictingDutiesList input[value="' + id + '"]');
          return selectedDuties[id] === true && elem.data('team-id') == teamId;
        });

        if (!teamHasSelected) {
          // Deselect all duties from this team
          $('#conflictingDutiesList input[data-team-id="' + teamId + '"]').each(function () {
            $(this).prop('checked', false);
            $(this).closest('tr').removeClass('selected');
            selectedDuties[$(this).val()] = false;
          });
          delete selectedTeams[teamId];
        }
      }
    });

    // Set up checkbox click handlers
    $('#conflictingDutiesList input[type="checkbox"]').on('change', function () {
      let isChecked = $(this).prop('checked');
      let dutyId = $(this).val();
      let teamId = $(this).data('team-id');

      if (isChecked) {
        selectedDuties[dutyId] = true;
        $(this).closest('tr').addClass('selected');
        
        // Auto-select all duties from this team
        $('#conflictingDutiesList input[data-team-id="' + teamId + '"]').each(function () {
          $(this).prop('checked', true);
          $(this).closest('tr').addClass('selected');
          selectedDuties[$(this).val()] = true;
        });
        selectedTeams[teamId] = true;
      } else {
        selectedDuties[dutyId] = false;
        $(this).closest('tr').removeClass('selected');
        
        // Check if this team still has selected duties
        let teamHasSelected = $('#conflictingDutiesList input[data-team-id="' + teamId + '"]:checked').length > 0;

        if (!teamHasSelected) {
          // Deselect all duties from this team
          $('#conflictingDutiesList input[data-team-id="' + teamId + '"]').each(function () {
            $(this).prop('checked', false);
            $(this).closest('tr').removeClass('selected');
            selectedDuties[$(this).val()] = false;
          });
          delete selectedTeams[teamId];
        }
      }
    });

    // Set up "Select All" checkbox
    $('#selectAllDuties').on('change', function () {
      let isChecked = $(this).prop('checked');
      $('#conflictingDutiesList input[type="checkbox"]').prop('checked', isChecked);
      $('#conflictingDutiesList tr').toggleClass('selected', isChecked);
      
      $('#conflictingDutiesList input[type="checkbox"]').each(function () {
        let dutyId = $(this).val();
        selectedDuties[dutyId] = isChecked;
        let teamId = $(this).data('team-id');
        if (isChecked) {
          selectedTeams[teamId] = true;
        }
      });
    });
  },

  /**
   * Build HTML for conflicting duties modal
   */
  buildConflictingDutiesModalHtml: function (duties, scheduledPersonName, homeTeamName, homeTeamStartDate) {
    // Ensure we have values for the message
    scheduledPersonName = scheduledPersonName || 'the scheduled person';
    homeTeamName = homeTeamName || 'the Home Team';
    homeTeamStartDate = homeTeamStartDate ? ' from ' + homeTeamStartDate : '';
    
    let html = '<div id="conflictingDutiesModal" title="Additional Team" style="display: none;">' +
      '<div id="conflictingDutiesContent" style="max-height: 500px; overflow-y: auto;">' +
      '<div id="conflictingDutiesMessage" style="margin-bottom: 20px; padding: 15px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">' +
      '<p style="color: #d9534f; font-weight: bold; margin: 0;">You have opted to delete the Home Team record for ' + scheduledPersonName + ' in ' + homeTeamName + homeTeamStartDate + '.</p>' +
      '<p style="margin: 10px 0 0 0; color: #666;">It is not possible to delete this Home Team record at the moment because there are Duties in that Home Team during the associated \'Future Home\' period.</p>' +
      '<p style="margin: 10px 0 0 0; color: #666;">Further action is needed from you. Below is a list of these duties. Please select any duties which you wish to remove. These will be moved to Unallocated.</p>' +
      '<p style="margin: 10px 0 0 0; color: #666;"><strong>The Additional (Future Home) record will stay in place if there are any duties left unselected.</strong></p>' +
      '</div>' +
      '<table id="conflictingDutiesTable" class="oddevenclass tablesmall stripe bluetable" style="width: 100%; border-collapse: collapse;">' +
      '<thead style="background-color: #f5f5f5;">' +
      '<tr>' +
      '<th style="width: 30px; padding: 8px; text-align: center;"><input type="checkbox" id="selectAllDuties" title="Select all duties" /></th>' +
      '<th style="padding: 8px; text-align: left;">Date of Duty</th>' +
      '<th style="padding: 8px; text-align: left;">Scheduling Team of Duty</th>' +
      '<th style="padding: 8px; text-align: left;">Duty Name</th>' +
      '<th style="padding: 8px; text-align: left;">Start Time</th>' +
      '<th style="padding: 8px; text-align: left;">End Time</th>' +
      '</tr>' +
      '</thead>' +
      '<tbody id="conflictingDutiesList">';

    duties.forEach(function (duty, index) {
      let dutyId = duty.ASP_AllocationsDutyID || duty.ASP_AllocationsSPID || index;
      let teamId = duty.TeamID || duty.ASP_DutyTeamId || 0;
      
      let adDutyId = duty.AD_AllocationsDutyID || duty.ASP_AllocationsDutyID || dutyId;
      html += '<tr class="duty-row" style="border-bottom: 1px solid #ddd;">' +
        '<td style="padding: 8px; text-align: center;"><input type="checkbox" class="duty-checkbox" value="' + adDutyId + '" data-duty-id="' + adDutyId + '" data-team-id="' + teamId + '" /></td>' +
        '<td style="padding: 8px;">' + (duty.DutyDate || duty.AD_DutyDate || '') + '</td>' +
        '<td style="padding: 8px;">' + (duty.TeamName || 'N/A') + '</td>' +
        '<td style="padding: 8px;">' + (duty.DutyName || duty.AD_DutyName || 'N/A') + '</td>' +
        '<td style="padding: 8px;">' + (duty.StartTime || 'N/A') + '</td>' +
        '<td style="padding: 8px;">' + (duty.EndTime || 'N/A') + '</td>' +
        '</tr>';
    });

    html += '</tbody>' +
      '</table>' +
      '<div style="margin-top: 20px; padding: 15px; background-color: #f5f5f5; border-left: 4px solid #5cb85c; border-radius: 4px;">' +
      '<p style="margin: 0; color: #333; font-size: 13px;">' +
      '<strong>Note:</strong> If you select one duty from a team, all duties from that team will be automatically selected. ' +
      'These selected duties will be moved to Unallocated. The Additional (Future Home) Team record will remain if any duties are left unselected.' +
      '</p>' +
      '</div>' +
      '</div>' +
      '</div>';

    return html;
  },

  /**
   * Remove conflicting duties via AJAX
   */
    removeConflictingDuties: function (dutyIds, personid, HomeTeamIdDel) {
    var that = this; // NOSONAR javascript:S7740
    let removeUrl = this.urls.removeConflictingDuties || '/mvc-app/setup/scheduled-people/remove-conflicting-duties';

    // FIXED: Direct document scope - Button click logs show checkboxes exist, modal scope fails (jQuery UI issue)
    // Use global document selector + fallback to parameter + body scan
    let $globalList = $('#conflictingDutiesList');
    let $globalCheckboxes = $('body input[data-duty-id][type="checkbox"]:checked');
    
    let actualDutyIds = [];
    
    // Primary: Modal-specific if found
    if ($globalList.length > 0) {
      $globalList.find('input[data-duty-id][type="checkbox"]:checked').each(function() {
        let adDutyId = Number.parseInt($(this).data('duty-id'));
        if (!Number.isNaN(adDutyId) && adDutyId > 0) actualDutyIds.push(adDutyId);
      });
    }
    
    // Fallback 1: All body data-duty-id checkboxes
    if (actualDutyIds.length === 0 && $globalCheckboxes.length > 0) {
      $globalCheckboxes.each(function() {
        let adDutyId = Number.parseInt($(this).data('duty-id'));
        if (!Number.isNaN(adDutyId) && adDutyId > 0) actualDutyIds.push(adDutyId);
      });
    }
    
    // Fallback 2: Use dutyIds parameter (from button context)
    if (actualDutyIds.length === 0 && dutyIds && dutyIds.length > 0) {
      actualDutyIds = dutyIds.map(id => Number.Number.parseInt(id)).filter(id => !Number.Number.isNaN(id) && id > 0);
    }
    
    if (actualDutyIds.length === 0) {
      alert('No duties selected. Please select duties to remove.');
      return;
    }

    $.ajax({
      url: removeUrl,
      type: 'POST',
      dataType: 'json',
      data: {
        dutyIds: actualDutyIds,
        personid: personid,
        homeTeamId: HomeTeamIdDel
      },
      success: function (data) {
        if (data.success) {
          // Show detailed results
          let msg = 'processed: ';
          if (data.removed > 0) {
            msg += (data.removed || 0) + ' duties removed.';
          }
          // Show specific errors if any
          if (data.errors && data.errors.length > 0) {
            data.errors.forEach(function(error) {
              msg += '• ' + error + '\\n';
            });
            // Use customAlert if available, else alert
            if (typeof customAlert !== 'undefined') {
              customAlert(msg);
            } else {
              alert(msg);
            }
          } else {
            alert(msg + '. Deleting Home Team...');
          }
          
          that.validatePersonOnRota(HomeTeamIdDel, personid);
        } else {
          alert(data.message || 'Failed to remove duties.');
        }
      },
      error: function (xhr) {
        console.error('AJAX fail:', xhr.responseText);
        alert('AJAX error. Console logged.');
      }
    });
  },

  /**
   * Validate if person is assigned to a rota before deletion
   */
  validatePersonOnRota: function (HomeTeamIdDel, personid) {
    var that = this; // NOSONAR javascript:S7740
    const validateUrl = this.urls.validateRota;

    console.log('Validating rota for HomeTeamId:', HomeTeamIdDel, 'personid:', personid, 'url:', validateUrl);

    $.ajax({
      url: validateUrl,
      type: 'POST',
      dataType: 'json',
      data: {
        'HomeTeamId': HomeTeamIdDel,
        'personid': personid
      },
      success: function (data) {
        console.log('Validate rota response:', data);

          // FUTURE HOME message (after YES)
          if (data.FutureHomeHasDuties === 1) {
            let msg = 'This person has duties within the Future Home period. The Future Home entry will NOT be removed.';
            alert(msg);
            return;
          }

          that.deleteHomeTeamSchPerson(HomeTeamIdDel, personid, data.IntStatus == 0 ? 1 : 0);
      },   
      error: function (xhr, status, error) {
        console.error('Validate rota error:', error);
      }
    });
  },

  /**
   * Delete home team for scheduled person
   */
  deleteHomeTeamSchPerson: function (HomeTeamIdDel, personid, DeleteFromRota = 0) {
    var that = this; // NOSONAR javascript:S7740
    const deleteUrl = this.urls.deleteHomeTeam;

    console.log('Delete home team - URL:', deleteUrl, 'HomeTeamId:', HomeTeamIdDel, 'personid:', personid, 'DeleteFromRota:', DeleteFromRota);

    $.ajax({
      url: deleteUrl,
      type: 'POST',
      dataType: 'json',
      data: {
        'HomeTeamId': HomeTeamIdDel,
        'personid': personid,
        'DeleteFromRota': DeleteFromRota
      },
      success: function (data) {
        console.log('Delete response:', data);

        if (typeof closeConfirmDeleteModal === 'function') {
          closeConfirmDeleteModal(false);
        }

        if (data[0].intStatus == 0) {
          // Show success message
          if (typeof customAlert !== 'undefined') {
            customAlert(data[0].strStatus);
          } else {
            alert(data[0].strStatus);
          }
        } else {
          // Update home team form fields (legacy behavior)
          $('#hometeam').val(data[0].TeamID);
          $('#hometeamhidden').val(data[0].TeamID);
          $('#startdate').val(data[0].StartDate);
          $('#homestartdateHide').val(data[0].StartDate);
          $('#startdate').prop('disabled', true);

          if (data[0].EndDate == '01-01-9999') {
            $('#enddate').val('');
          } else {
            $('#enddate').val(data[0].EndDate);
          }
          $('#homeenddatediv').css('display', 'none');
          $('#sortcode').val(data[0].SortCode);
          $('#hometeamfontcolour').val(data[0].fontcolour);
          if (typeof colorPickerDropdown === 'function') {
            colorPickerDropdown('#hometeamfontcolour', data[0].fontcolour);
          }
          $('#hometeambackcolour').val(data[0].BackgroundColour);
          if (typeof colorPickerDropdown === 'function') {
            colorPickerDropdown('#hometeambackcolour', data[0].BackgroundColour);
          }

          // Reload the team history table (trigger click like legacy)
          $('.schedule-person-team').trigger('click');

          // Rebuild additional team grid if needed (legacy behavior)
          let addteamarray = [];
          let $griddata = '';
          if (data.length > 1) {
            $griddata += '<thead><tr><th class="addTeamHeadStyle1">Team Name</th><th class="addTeamHeadStyle2">Start Date</th><th class="addTeamHeadStyle3">End Date</th><th class="addTeamHeadStyle6">Sort Code</th><th class="addTeamHeadStyle4">Is Available</th><th class="addTeamHeadStyle5">Display in View Screens</th><th class="addTeamHeadStyle6">Type</th><th class="addTeamHeadStyle7">&emsp;Action</th></tr></thead>';

            data.forEach(function (number, index, array) {
              let IsAvail = array[index]['IsAvailable'] == 0 ? 'No' : 'Yes';
              if (index > 0 && array[index]['IsHomeTeam'] == 0) {
                // Format LastUpdatedDate (legacy behavior)
                let ludate = new Date(array[index]['LastUpdatedDate']);
                let newluDate = ludate.toLocaleString('en-GB', { timeZone: 'Europe/London' });
                newluDate = newluDate.replace(',', '');
                newluDate = newluDate.replaceAll('/', '-');

                addteamarray[index - 1] = {
                  addteamSPTeamID: array[index]['SPTeamID'],
                  addteamsid: array[index]['TeamID'],
                  addteamstartdate: array[index]['StartDate'],
                  addteamenddate: array[index]['EndDate'],
                  addteamsortcode: array[index]['SortCode'],
                  addteamBackcolor: array[index]['BackgroundColour'],
                  addteamfontcolor: array[index]['fontcolour'],
                  addteamisavailable: array[index]['IsAvailable'],
                  addteamCreatedBy: array[index]['CreatedBy'],
                  addteamCreatedDate: array[index]['CreatedDate'],
                  addteamLastUpdatedBy: array[index]['LastUpdatedBy'],
                  addteamLastUpdatedDate: newluDate,
                  addteamupdate: 0
                };

                let sortCodeStyle = array[index]['SortCode'] !== '' ? 'background:' + array[index]['BackgroundColour'] + ';color:' + array[index]['fontcolour'] + ';' : '';
                $griddata += '<tr><td class="addTeamHeadStyle1" title="' + array[index]['TeamName'] + '">' + array[index]['TeamName'].substring(0, 30) + '</td>' +
                  '<td class="addTeamHeadStyle2">' + array[index]['StartDate'] + '</td>' +
                  '<td class="addTeamHeadStyle3">' + array[index]['EndDate'] + '</td>' +
                  '<td class="addTeamHeadStyle6" style="' + sortCodeStyle + '">' + $('<span></span>').text(array[index]['SortCode']).html() + '</td>' +
                  '<td class="addTeamHeadStyle4">' + IsAvail + '</td>' +
                  '<td class="addTeamHeadStyle5"><input id="ddlteamsidview" type="button" value="View" onclick="openModalPopup(' + array[index]["TeamID"] + ',\'' + array[index]["StartDate"] + '\',\'ddlteamsidview\')" teamid="' + array[index]['TeamID'] + '"/>' +
                  '<input id="ddlteamsidedit" type="button" value="Edit" onclick="openModalPopup(' + array[index]["TeamID"] + ',\'' + array[index]["StartDate"] + '\',\'ddlteamsidedit\')" teamid="' + array[index]['TeamID'] + '" /></td>' +
                  '</tr>';
              }
            });
            $griddata += '<input type="hidden" value="Add" name="adduseractiontype" id="adduseractiontype" />';
          }
          $('#additionalteamgrid').html($griddata);
          $('#addteamhiddenarray').val(JSON.stringify(addteamarray));

          // Trigger event for other components
          $(document).trigger('homeTeamDeleted', [data, personid]);
        }

        if (that.dataTable) {
          that.dataTable.ajax.reload(null, false);
        } else {
          that.loadTeamHistory();
        }
      },
      error: function (xhr, status, error) {
        console.error('Delete error:', error);
      }
    });
  },

  /**
   * Update person ID and reinitialize
   */
  updatePersonId: function (personId) {
    this.personId = personId;
    // Also update the tab button state (legacy behavior)
    this.checkPersonIdAndToggleTab();
    if (this.dataTable) {
      this.dataTable.destroy();
      this.dataTable = null;
      this.initialized = false;
    }
  },

  /**
   * Get scheduled person name from the form
   */
  getScheduledPersonName: function () {
    // Try to get from the display name field
    let displayName = $('#displayName').val();
    if (displayName) return displayName;
    
    // Try to construct from first and last name
    let firstName = $('#displayFirstName').val();
    let lastName = $('#displayLastName').val();
    if (firstName || lastName) {
      return (firstName + ' ' + lastName).trim();
    }
    
    return '';
  },

  /**
   * Get home team name from the form
   */
  getHomeTeamName: function () {
    // Try to get from home team dropdown
    let homeTeamSelect = $('#hometeam');
    if (homeTeamSelect.length) {
      let selectedOption = homeTeamSelect.find('option:selected');
      if (selectedOption.length) {
        return selectedOption.text();
      }
    }
    return '';
  },

  /**
   * Get home team start date from the form
   */
  getHomeTeamStartDate: function () {
    // Try to get from start date field
    let startDate = $('#startdate').val() || $('#homestartdateHide').val();
    if (startDate) return startDate;
    return '';
  }
};

module.exports = TeamHistory;

