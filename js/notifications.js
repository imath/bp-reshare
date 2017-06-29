/* global bpReshare */
window.bpReshare = window.bpReshare || {};

( function( bpReshare, document ) {

	// Bail if not set
	if ( 'undefined' === typeof bpReshare.userNotifications ) {
		return;
	}

	/**
	 * Notifications Class.
	 * @type {Object}
	 */
	bpReshare.Notifications = {
		start: function() {
			if ( bpReshare.userNotifications.amount ) {
				var bubble = document.getElementById( 'ab-pending-notifications' );
				bubble.setAttribute( 'class', 'pending-count alert' );
				bubble.innerHTML = bpReshare.userNotifications.amount;
			}
		}
	};

	window.addEventListener( 'load', function() {
		var loaded = false;

		if ( loaded ) {
			return;
		}

		bpReshare.Notifications.start();
		loaded = true;
	} );

} )( window.bpReshare, window.document );
