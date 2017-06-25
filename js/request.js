/* global bpReshare */
window.bpReshare = window.bpReshare || {};

( function( bpReshare, $ ) {

	// Bail if not set
	if ( typeof bpReshare.params === 'undefined' ) {
		return;
	}

	/**
	 * Ajax Class.
	 * @type {Object}
	 */
	bpReshare.Ajax = {

		request: function( endpoint, data, method ) {
			data = data || {};

			if ( ! endpoint || ! method ) {
				return false;
			}

			this.ajaxRequest = $.ajax( {
				url: bpReshare.params.root_url + endpoint,
				method: method,
				beforeSend: function( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', bpReshare.params.nonce );
					if ( data.alias ) {
						xhr.setRequestHeader('X-HTTP-Method-Override', data.alias );
					}
				},
				data: data
			} );

			return this.ajaxRequest;
		},

		get: function( endpoint, data ) {
			return this.request( endpoint, data, 'GET' );
		},

		post: function( endpoint, data ) {
			return this.request( endpoint, data, 'POST' );
		},

		remove: function( endpoint, data ) {
			return this.request( endpoint, $.extend( data, { alias: 'DELETE' } ), 'POST' );
		}
	};

} )( window.bpReshare, jQuery );
