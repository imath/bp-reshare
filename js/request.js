/* global bpReshare */
window.bpReshare = window.bpReshare || {};

( function( bpReshare ) {

	// Bail if not set
	if ( 'undefined' === typeof bpReshare.params ) {
		return;
	}

	/**
	 * Ajax Class.
	 * @type {Object}
	 */
	bpReshare.Ajax = {

		request: function( endpoint, data, method, response ) {
			var ajaxRequest, queryVars,
			    headers = {
			    	'X-Requested-With' : 'XMLHttpRequest',
			    	'X-WP-Nonce'       : bpReshare.params.nonce,
			    	'Cache-Control'    : 'no-cache, must-revalidate, max-age=0',
			    	'Content-Type'     : 'application/x-www-form-urlencoded'
			    };

			if ( ! endpoint || ! method ) {
				return false;
			}

			endpoint = bpReshare.params.root_url + endpoint;
			data     = data || {};

			if ( 'undefined' !== typeof XMLHttpRequest ) {
				ajaxRequest = new XMLHttpRequest();
			} else {
				ajaxRequest = new ActiveXObject( 'Microsoft.XMLHTTP' );
			}

			queryVars = Object.keys( data ).map( function( k ) {
				return encodeURIComponent( k ) + '=' + encodeURIComponent( data[k] )
			} ).join( '&' );

			ajaxRequest.onreadystatechange = function( event ) {
				if ( event.currentTarget && 4 === event.currentTarget.readyState ) {
					var r = JSON.parse( event.currentTarget.responseText ), status;

					if ( r.status ) {
						status = r.status;
					} else {
						status = event.currentTarget.status;
					}

					response && response( status, r );
				}
			}

			if ( 'DELETE' === method ) {
				headers['X-HTTP-Method-Override'] = method;
				method = 'POST';
			} else if ( 'GET' === method ) {
				endpoint += '?' + queryVars;
				queryVars = null;
				delete headers['Content-Type'];
			}

			ajaxRequest.open( method, endpoint );

			for ( h in headers ) {
				ajaxRequest.setRequestHeader( h, headers[h] );
			}

			ajaxRequest.send( queryVars );
		},

		get: function( endpoint, data, response ) {
			return this.request( endpoint, data, 'GET', response );
		},

		post: function( endpoint, data, response ) {
			return this.request( endpoint, data, 'POST', response );
		},

		delete: function( endpoint, data, response ) {
			return this.request( endpoint, data, 'DELETE', response );
		}
	};

} )( window.bpReshare );
