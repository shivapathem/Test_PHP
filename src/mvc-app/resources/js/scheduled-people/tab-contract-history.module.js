/**
 * Tab Contract History Module for Scheduled People
 * Handles displaying contract history in a DataTable
 * Usage: new TabContractHistoryModule({ urls: {...}, personId: 123 })
 */

let TabContractHistoryModule = function (opts) {
  this.urls = (opts && opts.urls) || {};
  this.personId = (opts && opts.personId) || null;
  this.dataTable = null;
  this.init();
};

TabContractHistoryModule.prototype = {
  /**
   * Initialize the module
   */
  init: function () {
    let that = this; // NOSONAR javascript:S7740
    
    // Initialize DataTable when the tab is clicked
    $(document).on('click', '.contract_history', function () {
      that.loadContractHistory();
    });
  },

  /**
   * Load contract history data via AJAX
   */
  loadContractHistory: function () {
    let that = this; // NOSONAR javascript:S7740
    
    // Destroy existing DataTable if it exists
    let $tbl = $('#contract_history');
    if ($.fn.DataTable.isDataTable($tbl)) {
      $tbl.DataTable().destroy();
    }

    // Initialize DataTable with AJAX
    that.dataTable = $tbl.DataTable({
      "pageLength": 20,
      "lengthChange": false,
      "order": [[0, 'desc']], // Order by Start Date descending
      "processing": false,
      "serverSide": false, // Using client-side processing for now
      "deferRender": true,
      "searching": false, // Remove search text box
      "info": false, // Remove "Showing X to Y of Z entries" text
      "fnDrawCallback": function (oSettings) {
        $(".dataTables_paginate").css('visibility', 'visible');
        $(".dataTables_length").css("display", "block");
        $("#dataTables_paginate ").css('font-size', '8px');
        $(".sorting").css('min-width', '95px');
      },
      "ajax": {
        url: this.urls.contractHistory || '',
        type: 'GET',
        data: {
          personId: this.personId
        },
        beforeSend: function (jqXHR, settings) {
          $('#contract-history-loading').css('display', 'flex');
        },
        dataSrc: function (json) {
          if (json && json.data) {
            return json.data;
          }
          return [];
        },
        complete: function (xhr, status) {
          $('#contract-history-loading').hide();
        }
      },
      "columns": [
        { 
          data: 'Startdate', 
          title: 'Start Date',
          render: function(data, type, row) {
            if (!data) return '';
            // Format date as dd-mm-yyyy
            let dateObj = new Date(data);
            let day = String(dateObj.getDate()).padStart(2, '0');
            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
            let year = dateObj.getFullYear();
            return day + '-' + month + '-' + year;
          }
        },
        { 
          data: 'Enddate', 
          title: 'End Date',
          render: function(data, type, row) {
            if (!data) return '';
            let dateObj = new Date(data);
            let day = String(dateObj.getDate()).padStart(2, '0');
            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
            let year = dateObj.getFullYear();
            return day + '-' + month + '-' + year;
          }
        },
        { data: 'AccountingGroup', title: 'Accounting Group' },
        { data: 'EDPMinimumExcBreaks', title: 'EDP Minimum (Exc Breaks)' },
        { data: 'PaidContract', title: 'Paid Contract (Contracted Hours)' },
        { 
          data: 'ManualEDP', 
          title: 'Manual EDP',
          render: function(data, type, row) {
            return data == 1 ? 'True' : 'False';
          }
        },
        { data: 'EFT', title: 'EFT' },
        { data: 'EmployeeGroup', title: 'Employee Group' },
        { data: 'ContractType', title: 'Contract Type' },
        { data: 'PaymentType', title: 'Payment Type' },
        { data: 'TeamPayDepartment', title: 'Teampay department' },
        { 
          data: 'AutoEDPTOIL', 
          title: 'Prefer EDP TOIL',
          render: function(data, type, row) {
            if (data == 1) return 'Yes';
            if (data == 0) return 'No';
            return '';
          }
        },
        { data: 'TermsConditions', title: 'Terms & Conditions' },
        { data: 'CostCode', title: 'Cost Code' },
        { data: 'ActivityType', title: 'Activity Type' },
        { data: 'duty_duration', title: 'Duty Duration' },
        { data: 'Title', title: 'Title' },
        { data: 'StaffNumber', title: 'Staff Number' },
        { data: 'network_id', title: 'Network ID' },
        { data: 'first_name', title: 'First Name' },
        { data: 'Surname', title: 'Surname' },
        { data: 'middle_name', title: 'Middle Name' },
        { data: 'PreferredForename', title: 'Preferred Name' },
        { data: 'Designation', title: 'Designation' },
        { 
          data: 'ImportedHistory', 
          title: 'History',
          render: function(data, type, row) {
            if (data) {
              let encodedMsg = btoa(data || '');
              return '<a href="javascript:void(0)" class="js-contact-history" data-msg="' + encodedMsg + '">' +
                     '<i class="fa fa-hourglass-3" id="circle-clr"></i></a>';
            }
            return '';
          }
        }
      ],
      "language": {
        "emptyTable": "No Record Found",
        "processing": "&nbsp;",
        "loadingRecords": "&nbsp;"
      }
    });

    // Handle history icon click
    $tbl.on('click', '.js-contact-history', function(e) {
      e.preventDefault();
      that.showContractHistory($(this).data('msg'));
    });
  },

  /**
   * Show contract history popup
   * @param {string} encodedMsg - Base64 encoded message
   */
  showContractHistory: function (encodedMsg) {
    let that = this; // NOSONAR javascript:S7740
    // Use the new Laravel route instead of legacy PHP endpoint
    $.post(that.urls.contractHistoryPopup || '', {
        action: 'contractHistory',
        modulename: 'contractHistory',
        msg: encodedMsg
      },
      function (data, status) {
        $.facebox(data);
      }
    ).fail(function(xhr, status, error) {
      console.error('Error loading contract history:', error);
      alert('Failed to load contract history details.');
    });
  },

  /**
   * Update person ID and reload data
   * @param {number} personId - Scheduled person ID
   */
  setPersonId: function (personId) {
    this.personId = personId;
    if (this.dataTable) {
      this.loadContractHistory();
    }
  }
};

module.exports = TabContractHistoryModule;
