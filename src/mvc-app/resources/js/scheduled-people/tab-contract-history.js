/**
 * Tab Contract History - Entry point
 * Loads and initializes the TabContractHistoryModule
 */

var TabContractHistoryModule = require('./tab-contract-history.module.js');

// Expose main module to window for use in blade templates
window.TabContractHistoryModule = TabContractHistoryModule;

// Initialize module on DOM ready
$(document).ready(function () {
  // Get config from window (set by Blade template)
  var config = window.__ScheduledPeopleConfig || {};
  
  // Initialize the contract history module and store instance
  window.TabContractHistoryInstance = new TabContractHistoryModule({
    urls: {
      contractHistory: config.urls && config.urls.contractHistory ? config.urls.contractHistory : '/mvc-app/setup/scheduled-people/contract-history',
      contractHistoryPopup: config.urls && config.urls.contractHistoryPopup ? config.urls.contractHistoryPopup : '/mvc-app/setup/scheduled-people/contract-history-popup',
      common: config.urls && config.urls.common ? config.urls.common : '/mvc-app/function-includes/common/common.php'
    },
    personId: config.personId || 0
  });
});
