import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Facility booking websocket JavaScript
 *
 * @param {Object} p parameters
 */
var FacilityBookingWebSocket = function (p) {
    this.facilityBookingFilterInstance = null;
    this.facilityBookingInstance = null;
    this.pusherScheme = null;
    this.pusherPort = null;
};

FacilityBookingWebSocket.prototype = {
    /**
     * Context menus
     */
    init: function () {
        let that = this;
        window.Pusher = Pusher;
        let https = that.pusherScheme == 'http' ? false :  true;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: 'allocate-mvc', // Your local key
            wsHost: window.location.hostname, //Host name
            wsPort: that.pusherPort,
            wssPort: that.pusherPort, // If using WSS
            forceTLS: https, // Set to true if using WSS
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
        });
        window.Echo.connector.pusher.connection.bind('disconnected', function () {
            console.error('Pusher disconnected');
        });

        window.Echo.connector.pusher.connection.bind('error', function (error) {
            console.error('Pusher error:', error);
        });

        window.Echo.private('facility-update')
            .listen('FacilityUpdated', (e) => {
                console.log('Facility updated');
                that.facilityBookingFilterInstance.loadFacilityAndBookings(e.facilityId);
            });
    }
}

export default FacilityBookingWebSocket;