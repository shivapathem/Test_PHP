/**
 * SearchStaff Module
 * Modern ES5 class-style wrapper for legacy searchStaffDetails.js functionality.
 * 100% behavioural compatibility maintained.
 */

var SearchStaff = function (p) {
  this.urls = p.urls || {};
  this.personId = p.personId || 0;

  this.actionType = '';
  this.staffNumber = '';
  this.networkID = '';
  this.foreName = '';
  this.surName = '';

  this.sel = {
    searchStaffNo: "#js_staffNumber",
    searchNetId: "#js_staffNetworkId",
    searchForeName: "#js_staffForeName",
    searchSurName: "#js_staffSurName",

    findBtn: "#js_find",
    clearBtn: "#js_cancel",

    resultsTbody: "#staffDetailsConfiglistBody",

    editBtn: "#myBtnEditUpdate",
    modal: "#myModalUpdate",

    saveBtn: "#js_saveStaff",
    undoBtn: ".js_cancelStaff",

    previewTable: "#attachStaffSession",
    errorDiv: "#stafferror",

    getStaffId: "#getstaffid",
    oldScheduledPersonId: "#oldscheduledPersonID",
    newPersonId: "#newpersonid",

    // Staff detail fields in preview table
    staffForename: "#js_forename",
    staffSurname: "#js_surname",
    staffMidname: "#js_midname",
    staffPrefname: "#js_prefname",
    staffDesignation: "#js_designation",
    staffNetLogin: "#js_netlogin",
  };

  this.init();
};

SearchStaff.prototype = {

  /**
   * Build staff details HTML from JSON data
   * This moves HTML generation from PHP controller to JavaScript
   * 
   * @param {Object} staffDetails - Staff details object
   * @returns {string} HTML string
   */
  buildStaffDetailsHtml: function (staffDetails) {
    let title = '';
    let preferredName = staffDetails.PreferredForename || '';
    let staffNumber = staffDetails.StaffNumber || '';
    let netLogin = staffDetails.NetLogin || '';
    let foreName = staffDetails.Forename || '';
    let surName = staffDetails.Surname || '';
    let middleName = staffDetails.MiddleName || '';
    let jobTitle = staffDetails.JobTitle || staffDetails.Designation || '';

    let html = '';
    html += '<tr id="STFdtl">';
    html += '<td>Title</td>';
    html += '<td id="js_title">' + title + '</td>';
    html += '</tr>';
    html += '<tr class="STFdtl">';
    html += '<td>Staff Number</td>';
    html += '<td id="js_staffnumber">' + staffNumber + '</td>';
    html += '</tr>';
    html += '<tr class="STFdtl">';
    html += '<td>Network ID</td>';
    html += '<td id="js_netlogin">' + netLogin + '</td>';
    html += '</tr>';
    html += '<tr class="STFdtl">';
    html += '<td>Forename</td>';
    html += '<td id="js_forename">' + foreName + '</td>';
    html += '</tr>';
    html += '<tr class="STFdtl">';
    html += '<td>Surname</td>';
    html += '<td id="js_surname">' + surName + '</td>';
    html += '</tr>';
    html += '<tr class="STFdtl">';
    html += '<td>Middle Name</td>';
    html += '<td id="js_midname">' + middleName + '</td>';
    html += '</tr>';
    html += '<tr class="STFdtl">';
    html += '<td>Preferred Name</td>';
    html += '<td id="js_prefname">' + preferredName + '</td>';
    html += '</tr>';
    html += '<tr class="STFdtl">';
    html += '<td>Designation</td>';
    html += '<td id="js_designation">' + jobTitle + '</td>';
    html += '</tr>';

    return html;
  },

  /**
   * Check if both staff number and netlogin are populated
   * @returns {boolean} - True if both are populated
   */
  areBothPopulated: function () {
    let staffNumber = $.trim($("#js_staffnumber").text());
    let netLogin = $.trim($("#js_netlogin").text());
    return staffNumber && netLogin;
  },

  /**
   * Initialize module
   */
  init: function () {
    let that = this; // NOSONAR javascript:S7740
    this.setCsrfHeader();
    this.cacheDom();
    $('.js_cancelStaff').hide();
    $('.js_saveStaff').hide();
    this.bindEvents();
    this.toggleSearchButton();

    // Load existing staff details if in edit mode (personId > 0)
    if (this.personId > 0) {
      this.loadStaffDetails();
    }
  },

  /**
   * Load existing staff details for edit mode
   */
  loadStaffDetails: function () {
    let that = this; // NOSONAR javascript:S7740
    let getStaffUrl = this.urls.getStaff;

    // Skip if no URL or personId
    if (!getStaffUrl || !this.personId) {
      return;
    }

    $.ajax({
      url: getStaffUrl,
      type: 'GET',
      dataType: 'json',
      success: function (res) {
        if (res.success && res.staffDetails) {
          // Generate HTML from staffDetails JSON
          let html = that.buildStaffDetailsHtml(res.staffDetails);

          // Populate the staff details preview table
          that.$attachBody.html(html);

          // Check if both Staff Number AND NetLogin are populated
          let staffNumber = res.staffDetails.StaffNumber || '';
          let netLogin = res.staffDetails.NetLogin || '';
          let bothPopulated = staffNumber && netLogin;

          // If both are populated, hide all buttons
          if (bothPopulated) {
            $("#js_saveStaff").hide();
            $(".js_cancelStaff").hide();
            $("#myBtnEditUpdate").hide();
          } else {
            // First time or partially populated - only show Edit button
            // Save/Undo buttons are only shown during attachment process
            $("#js_saveStaff").hide();
            $(".js_cancelStaff").hide();
            $("#myBtnEditUpdate").show();
          }
        } else {
          // No staff details - show Edit button only (initial state)
          $("#js_saveStaff").hide();
          $(".js_cancelStaff").hide();
          $("#myBtnEditUpdate").show();
        }
      },
      error: function (xhr) {
        console.error('Error loading staff details:', xhr);
      }
    });
  },

  /**
   * Cache DOM selectors
   */
  cacheDom: function () {
    this.$number = $("#js_staffNumber");
    this.$netlogin = $("#js_staffNetworkId");
    this.$forename = $("#js_staffForeName");
    this.$surname = $("#js_staffSurName");

    this.$find = $("#js_find");
    this.$cancel = $("#js_cancel");
    this.$resultsBody = $("#staffDetailsConfiglist tbody");

    this.$attachBody = $("#attachStaffSession tbody");

    this.$dialog = $("#dialog-confirm-staff-attach");
  },

  /**
   * Bind UI Events
   */
  bindEvents: function () {
    const that = this; // NOSONAR javascript:S7740
    const ns = this.ns;

    $('#myBtnEditUpdate').on('click', function () {
      $("#myModalUpdate").show();
    });

    // Staff Number autocomplete - following allocate-users.module.js pattern
    $(that.sel.searchStaffNo).on('keyup', function () {
      $(this).autocomplete({
        source: that.urls.staffAutocomplete + '?termKey=StaffNumber',
        minLength: 2,
        select: function (event, ui) {
          $(that.sel.searchStaffNo).val(ui.item.value);
        }
      }).on('mouseup', function () {
        $(this).select();
      });
      that.toggleSearchButton("Enable");
      that.checkResetSearchButton();
    });

    // Network ID autocomplete
    $(that.sel.searchNetId).on('keyup', function () {
      $(this).autocomplete({
        source: that.urls.staffAutocomplete + '?termKey=NetLogin',
        minLength: 2,
        select: function (event, ui) {
          $(that.sel.searchNetId).val(ui.item.value);
        }
      }).on('mouseup', function () {
        $(this).select();
      });
      that.toggleSearchButton("Enable");
      that.checkResetSearchButton();
    });

    // Forename autocomplete
    $(that.sel.searchForeName).on('keyup', function () {
      $(this).autocomplete({
        source: that.urls.staffAutocomplete + '?termKey=Forename',
        minLength: 2,
        select: function (event, ui) {
          $(that.sel.searchForeName).val(ui.item.value);
        }
      }).on('mouseup', function () {
        $(this).select();
      });
      that.toggleSearchButton("Enable");
      that.checkResetSearchButton();
    });

    // Surname autocomplete
    $(that.sel.searchSurName).on('keyup', function () {
      $(this).autocomplete({
        source: that.urls.staffAutocomplete + '?termKey=Surname',
        minLength: 2,
        select: function (event, ui) {
          $(that.sel.searchSurName).val(ui.item.value);
        }
      }).on('mouseup', function () {
        $(this).select();
      });
      that.toggleSearchButton("Enable");
      that.checkResetSearchButton();
    });

    // Search button click
    this.$find.off("click.find").on("click.find", function (e) {
      e.preventDefault();
      that.actionType = 'search';

      that.staffNumber = that.$number.val();
      that.networkID = that.$netlogin.val();
      that.foreName = that.$forename.val();
      that.surName = that.$surname.val();

      that.searchStaff();
    });

    // Clear button click
    this.$cancel.off("click.cancel").on("click.cancel", function () {
      that.clearFilter();
    });

    // Attach handler (delegated)
    $(document).off("click.staffattach")
      .on("click.staffattach", ".staffattach", function (e) {
        e.preventDefault();

        var staffID = $(this).data("staffid");
        var scheduledPersonID = $(this).data("scheduledpersonid");

        that.actionType = 'select';
        that.attachStaffDetails(staffID, 'select', 0, scheduledPersonID);
      });

    // Save Staff
    $("#js_saveStaff").off("click.save").on("click.save", function (e) {
      e.preventDefault();
      var staffID = $("#getstaffid").val();
      var oldScheduledPersonID = $("#oldscheduledPersonID").val();

      that.actionType = 'save';
      that.attachStaffDetails(staffID, 'save', 1, oldScheduledPersonID);
    });

    // Cancel attach (Undo)
    $(".js_cancelStaff").off("click.cancStaff").on("click.cancStaff", function (e) {
      e.preventDefault();
      that.attachStaffDetails('', 'cancel');
    });
  },

  /**
   * Build search results HTML from JSON data
   * This moves HTML generation from PHP controller to JavaScript
   * 
   * @param {Array} staffDetails - Array of staff details objects
   * @returns {string} HTML string
   */
  buildSearchResultsHtml: function (staffDetails) {
    let html = '';

    // Handle empty results
    if (!staffDetails || staffDetails.length === 0) {
      return '<tr><td colspan="5" class="filter_data_unavailabl">No matching records found.</td></tr>';
    }

    // Handle "no filter applied" case (empty array from clearfilter action)
    if (staffDetails.length === 0 && this.actionType === 'clearfilter') {
      return '<tr><td colspan="5" class="filter_data_unavailabl">Please apply the filter.</td></tr>';
    }

    for (var i = 0; i < staffDetails.length; i++) {
      var staff = staffDetails[i];
      var staffID = staff.StaffID || 0;
      var scheduledPersonID = staff.ScheduledPersonID || 0;
      var buttonState = staff.buttonState || 'linked';

      var button = 'Linked';

      if (buttonState === 'attach-tp') {
        button = '<button class="btn-class staffattach" data-staffid="' + staffID + '" data-scheduledpersonid="' + scheduledPersonID + '">Attach-TP</button>';
      } else if (buttonState === 'attach-ad') {
        button = '<button class="btn-class staffattach" data-staffid="' + staffID + '" data-scheduledpersonid="' + scheduledPersonID + '">Attach-AD</button>';
      }

      html += '<tr>';
      html += '<td>' + (staff.StaffNumber || '') + '</td>';
      html += '<td>' + (staff.NetLogin || '') + '</td>';
      html += '<td id="forename_' + staffID + '">' + (staff.Forename || '') + '</td>';
      html += '<td id="surname_' + staffID + '">' + (staff.Surname || '') + '</td>';
      html += '<td class="btntbl">' + button + '</td>';
      html += '</tr>';
    }

    return html;
  },

  /**
   * Perform Staff Search (POST → Laravel)
   */
  searchStaff: function () {
    var that = this; // NOSONAR javascript:S7740

    $.ajax({
      url: this.urls.staffSearch,
      type: "POST",
      dataType: "json",
      data: {
        selectedStaffNumber: that.staffNumber,
        selectedForeName: that.foreName,
        selectedSurName: that.surName,
        selectedNetLogin: that.networkID,
        actionType: that.actionType
      },
      success: function (res) {
        if (res.status === 'success') {
          // Check for new JSON format (staffDetails) or legacy HTML format (view)
          if (res.staffDetails) {
            // New JSON format - generate HTML client-side
            var html = that.buildSearchResultsHtml(res.staffDetails);
            that.$resultsBody.html(html);
          } else if (res.view) {
            // Legacy HTML format - use directly (for backward compatibility)
            that.$resultsBody.html(res.view);
          }

          if (that.actionType === 'search') {
            that.$cancel.show();
          } else {
            that.resetFilters();
          }
        }
      },
      error: function (xhr) {
        alert("Error performing search");
      }
    });
  },

  /**
   * Attach staff details
   */
  attachStaffDetails: function (staffId, actionType, showPopup, scheduledPersonID) {
    var that = this; // NOSONAR javascript:S7740

    var replaceName = false;
    var newSchedPersonID = 0;

    if (actionType === 'save') {
      newSchedPersonID = $("#newpersonid").val();
    }

    // In create mode (personId is 0), handle cancel action locally without AJAX
    if (actionType === 'cancel' && this.personId === 0) {
      // Just reset the UI without making an AJAX call
      $("#js_saveStaff").hide();
      $(".js_cancelStaff").hide();
      $("#myBtnEditUpdate").show();
      $("#stafferror").hide();

      // Clear the staff preview table
      $("#attachStaffSession tbody").html('');
      $("#getstaffid").val('');
      $("#oldscheduledPersonID").val('');

      // Clear the search filters
      this.clearFilter();
      return;
    }

    // Popup confirmation
    if (showPopup) {
      $("#dialog-confirm-staff-attach").dialog({
        resizable: false,
        modal: true,
        title: "Please Confirm!",
        height: 250,
        width: 400,
        buttons: {
          "Yes": function () {
            replaceName = $('input[type=checkbox][id=js_replaceDisplayName]').prop("checked");
            $(this).dialog("close");
            that._sendAttach(staffId, actionType, scheduledPersonID, newSchedPersonID, replaceName, true);
          },
          "No": function () {
            $(this).dialog("close");
          }
        }
      });
    } else {
      that._sendAttach(staffId, actionType, scheduledPersonID, newSchedPersonID, false, false);
    }
  },

  /**
   * Internal attach sender
   * Matches legacy behavior:
   * - 'select' and 'cancel' actions: update the view
   * - 'save' action: do NOT update the view, just hide buttons and keep displayed values
   */
  _sendAttach: function (staffId, actionType, oldID, newID, replaceName, showPopup) {
    var that = this; // NOSONAR javascript:S7740

    // Build URL - use staffAttach for all actions including cancel
    var attachUrl = this.urls.staffAttach;
    // Replace the :personId placeholder with actual personId
    var personIdToUse = this.personId > 0 ? this.personId : 1;
    attachUrl = attachUrl.replace(':personId', personIdToUse);

    $.ajax({
      url: attachUrl,
      type: "POST",
      dataType: "json",
      data: {
        selectedstaffID: parseInt(staffId) || 0,
        actionType: actionType,
        schedPersonID: parseInt(newID) || 0,
        oldschedPersonID: parseInt(oldID) || 0
      },
      success: function (res) {
        // Only update view for 'select' and 'cancel' actions
        // For 'save' action, do NOT update view - keep the displayed values
        if (actionType === 'select' || actionType === 'cancel') {
          if (res.staffDetails) {
            // New JSON format - generate HTML client-side
            var html = that.buildStaffDetailsHtml(res.staffDetails);
            that.$attachBody.html(html);
          } else if (res.view && res.view.trim() !== '') {
            that.$attachBody.html(res.view);
          }
        }
        // For 'save' action, we intentionally do NOT update the view
        if (res.status === 'success') {

          if (showPopup === true && replaceName === true) {
            var lastName = $("#js_surname").text();
            var preferred = $("#js_prefname").text();
            var firstName = preferred && preferred !== "" ? preferred : $("#js_forename").text();

            $("#dispFirstName").val(firstName);
            $("#dispLastName").val(lastName);
          }

          if (actionType === 'select') {
            $("#getstaffid").val(staffId);
            $("#oldscheduledPersonID").val(oldID);

            // Show save and undo buttons during attachment
            $("#js_saveStaff").show();
            $(".js_cancelStaff").show();

            // Hide edit button - only undo and save should be shown after attach
            $("#myBtnEditUpdate").hide();

            // Close the staff search modal
            $("#myModalUpdate").hide();

            // Clear filter after select (legacy behavior)
            that.clearFilter();
          }
          else if (actionType === 'save') {
            // Also save staff details when saving - get values from preview table
            that.saveStaffDetails(function (saveSuccess) {
              if (saveSuccess) {
                // Do NOT reload staff details from server - keep the displayed values
                // as the server may not have all fields (e.g., MiddleName)
              }
            });

            // Check if both are populated to hide edit button
            var bothPopulated = that.areBothPopulated();

            // Hide save/undo buttons after save
            $("#js_saveStaff").hide();
            $(".js_cancelStaff").hide();

            // Hide edit button if both are populated
            if (bothPopulated) {
              $("#myBtnEditUpdate").hide();
            } else {
              $("#myBtnEditUpdate").show();
            }

            $("#myModalUpdate").hide();
            $(".contract_history").removeClass('buttonDisabled').addClass('buttonEnabled');

            // Legacy: do NOT clear filter for 'save' - keep values visible
          }
          else if (actionType === 'cancel') {
            // Always update the view for cancel action - clear the staff details table
            // Check for new JSON format (staffDetails) or legacy HTML format (view)
            if (res.staffDetails) {
              var html = that.buildStaffDetailsHtml(res.staffDetails);
              that.$attachBody.html(html);
            } else {
              that.$attachBody.html(res.view || '');
            }
            $("#js_saveStaff").hide();
            $(".js_cancelStaff").hide();
            $("#myBtnEditUpdate").show();
            $("#stafferror").hide();

            // Clear the stored staff IDs
            $("#getstaffid").val('');
            $("#oldscheduledPersonID").val('');

            // Clear filters after cancel
            that.clearFilter();
          }
        }

        if (res.message) {
          $("#stafferror").text(res.message).show();
        }
      },
      error: function (xhr, status, error) {
        // More detailed error message
        var errorMsg = "Error attaching staff.";

        // Try to get error message from response
        try {
          var response = JSON.parse(xhr.responseText);
          if (response.message) {
            errorMsg = response.message;
          } else if (response.error) {
            errorMsg = response.error;
          } else if (response.exception) {
            errorMsg = response.exception;
          }
        } catch (e) {
          // Use default message if JSON parsing fails
          errorMsg = xhr.responseText || errorMsg;
        }

        // Add HTTP status info if available
        if (xhr.status === 404) {
          errorMsg = "Requested URL not found.";
        } else if (xhr.status === 500) {
          errorMsg = "Internal Server Error: " + errorMsg;
        } else if (status === 'parsererror') {
          errorMsg = "Error parsing JSON response.";
        }

        alert(errorMsg);
      }
    });
  },

  /**
   * Clear filters (back to default)
   */
  clearFilter: function () {
    this.$number.val('');
    this.$netlogin.val('');
    this.$forename.val('');
    this.$surname.val('');

    this.actionType = 'clearfilter';

    this.searchStaff();
    this.toggleSearchButton();
    this.$cancel.hide();
  },

  /**
   * Reset filters after clear
   */
  resetFilters: function () {
    this.$number.val('');
    this.$netlogin.val('');
    this.$forename.val('');
    this.$surname.val('');
    this.toggleSearchButton();
  },

  /**
   * Enable/disable search button
   * Uses CSS classes and disabled attribute following allocate-users.module.js pattern
   */
  toggleSearchButton: function (action) {
    if (action === 'Enable') {
      $("#js_find").removeAttr('disabled').removeClass('notclickable buttonDisabled').addClass('buttonEnabled');
      $("#js_cancel").show();
    } else {
      $("#js_find").attr('disabled', 'disabled').addClass('notclickable buttonDisabled').removeClass('buttonEnabled');
      $("#js_cancel").hide();
    }
  },

  /**
   * Check if all fields empty → disable search button
   */
  checkResetSearchButton: function () {
    if (
      !this.$number.val() &&
      !this.$netlogin.val() &&
      !this.$forename.val() &&
      !this.$surname.val()
    ) {
      this.toggleSearchButton();
    }
  },

  /**
   * Save staff details to the server
   * Gets values from preview table and sends to updateStaff endpoint
   * @param {function} callback - Callback function with boolean success parameter
   */
  saveStaffDetails: function (callback) {
    var that = this; // NOSONAR javascript:S7740

    // Get staff details from the preview table
    var staffForename = $(this.sel.staffForename).text() || '';
    var staffSurname = $(this.sel.staffSurname).text() || '';
    var staffMidname = $(this.sel.staffMidname).text() || '';
    var staffPrefname = $(this.sel.staffPrefname).text() || '';
    var staffDesignation = $(this.sel.staffDesignation).text() || '';

    // Get the person ID (scheduled person ID)
    // In create mode, use newpersonid after the scheduled person is created
    var personId = this.personId > 0 ? this.personId : $('#newpersonid').val();

    // Build the URL for updating staff details
    var updateUrl = this.urls.staffUpdate;

    // Replace :personId placeholder if present
    if (updateUrl && updateUrl.indexOf(':personId') !== -1) {
      if (personId) {
        updateUrl = updateUrl.replace(':personId', personId);
      } else {
        // No personId available, can't save
        if (callback) callback(true);
        return;
      }
    }

    // If no update URL or person ID, just return success (nothing to save)
    if (!updateUrl || !personId) {
      if (callback) callback(true);
      return;
    }

    // Make AJAX call to update staff details
    $.ajax({
      url: updateUrl,
      type: 'POST',
      dataType: 'json',
      data: {
        id: personId,
        FirstName: staffForename,
        Surname: staffSurname,
        MiddleName: staffMidname,
        PreferredName: staffPrefname,
        Designation: staffDesignation
      },
      success: function (data) {
        if (data.success) {
          // Only update fields that were actually returned by the server
          // This preserves the displayed values (like MiddleName) that the server doesn't have
          if (data.FirstName !== undefined && data.FirstName !== null) {
            $(that.sel.staffForename).text(data.FirstName);
          }
          if (data.Surname !== undefined && data.Surname !== null) {
            $(that.sel.staffSurname).text(data.Surname);
          }
          if (data.MiddleName !== undefined && data.MiddleName !== null) {
            $(that.sel.staffMidname).text(data.MiddleName);
          }
          if (data.PreferredName !== undefined && data.PreferredName !== null) {
            $(that.sel.staffPrefname).text(data.PreferredName);
          }
          if (data.Designation !== undefined && data.Designation !== null) {
            $(that.sel.staffDesignation).text(data.Designation);
          }

          if (callback) callback(true);
        } else {
          // Show error message but don't block the flow
          if (data.message) {
            $("#stafferror").text(data.message).show();
          }
          if (callback) callback(false);
        }
      },
      error: function (xhr) {
        // Log error but don't block the UI flow
        var errorMsg = "Error saving staff details.";
        try {
          var response = JSON.parse(xhr.responseText);
          if (response.message) {
            errorMsg = response.message;
          }
        } catch (e) {
          // Use default message
        }
        console.error('Staff details save error:', errorMsg);
        if (callback) callback(false);
      }
    });
  },

  /**
   * Set CSRF for all AJAX
   */
  setCsrfHeader: function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    if (!token) return;
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
  }
};

module.exports = SearchStaff;
