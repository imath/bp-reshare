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
			if ( bpReshare.userNotifications.items && 0 < bpReshare.userNotifications.items.length ) {
				this.dN     = document.getElementById( 'wp-admin-bar-no-notifications' );
				this.bubble = document.getElementById( 'ab-pending-notifications' );
				this.items  = bpReshare.userNotifications.items;
				this.amount = bpReshare.userNotifications.items.length;

				this.bubble.setAttribute( 'class', 'pending-count alert' );
				this.bubble.innerHTML = parseInt( this.bubble.innerHTML, 10 ) + this.amount;

				if ( ! this.dN ) {
					var list = document.querySelector( '#wp-admin-bar-bp-notifications-default' );

					for( child in list.childNodes ) {
						if ( 'LI' === list.childNodes[ child ].nodeName && ! this.dN ) {
							this.dN = list.childNodes[ child ].cloneNode( true );
						}
					};

					this.dN.setAttribute( 'id', 'wp-admin-bar-notification-reshares' );
					list.appendChild( this.dN );
				}

				if ( this.dN.nodeName ) {
					var dNlink = this.dN.firstChild;

					dNlink.setAttribute( 'href', '#' );
					dNlink.innerHTML = bpReshare.userNotifications.template.one.replace( '%n', 1 );

					if ( 1 < this.amount ) {
						dNlink.innerHTML = bpReshare.userNotifications.template.more.replace( '%n', this.amount );
						dNlink.setAttribute( 'href', bpReshare.userNotifications.link.more );
					} else {
						var items = this.items;

						dNlink.setAttribute( 'href', bpReshare.userNotifications.link.one.replace( '%n', items.shift() ) );
					}
				}
			}

			var bodyClasses = document.getElementsByTagName( 'body' )[0].getAttribute( 'class' );

			if ( -1 !== bodyClasses.indexOf( 'my-activity' ) && -1 !== bodyClasses.indexOf( 'reshare' ) ) {
				this.highLightReshares();
			}
		},

		highLightReshares: function() {
			if ( ! this.amount || ! this.items ) {
				return;
			}

			this.items.forEach( function( item ) {
				var activity = document.querySelector( '#activity-' + item );

				activity.setAttribute( 'style', 'border-left: 4px solid #21759b; padding-left: 1em' );
				window.setTimeout( function() {
					activity.removeAttribute( 'style' );
				}, 4000 );
			} );

			this.bubble.innerHTML = parseInt( this.bubble.innerHTML, 10 ) - this.amount;

			if ( 0 === parseInt( this.bubble.innerHTML, 10 ) ) {
				this.bubble.setAttribute( 'class', 'count no-alert' );
			}

			this.dN.remove();
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
