/**
 * Entry point for Tab List (Search Scheduled People) page
 * Loads and initializes the TabListModule
 */

var TabListModule = require('./tab-list.module.js');

// Expose main module to window for use in blade templates
window.TabListModule = TabListModule;

// Expose helper functions to window for inline onclick handlers
window.CreateScheduledPerson = function(actionType, schedulepersonid, teamId) {
  var module = window.TabListInstance;
  if (module) {
    module.CreateScheduledPerson(actionType, schedulepersonid, teamId);
  }
};

window.highlightTableRow = function(thisVal) {
  var module = window.TabListInstance;
  if (module) {
    module.highlightTableRow(thisVal);
  }
};

window.unHighlightTableRow = function(thisVal) {
  var module = window.TabListInstance;
  if (module) {
    module.unHighlightTableRow(thisVal);
  }
};

window.customAlert = function(message) {
  var module = window.TabListInstance;
  if (module) {
    module.customAlert(message);
  } else {
    alert(message);
  }
};

// Back button support for search page header

// Initialize module on DOM ready
$(function () {
  // Get config from window (set by Blade template if needed)
  var config = window.__TabListConfig || {};
  
  // Initialize the module and store instance
  window.TabListInstance = new TabListModule({
    urls: config.urls || {}
  });
});
