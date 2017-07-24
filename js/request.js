/* global JSON, ActiveXObject */

// Make sure the bpReshare object exists.
window.bpReshare = window.bpReshare || {};

( function( bpReshare ) {

	// Bail if not set
	if ( 'undefined' === typeof bpReshare.params ) {
		return;
	}

	/**
	 * Ajax Class.
	 *
	 * @type {Object}
	 */
	bpReshare.Ajax = {

		/**
		 * Performs a WP REST API Request using Ajax.
		 *
		 * @param  {String} endpoint The WP REST API route endpoint.
		 * @param  {Object} data     The request variables.
		 * @param  {String} method   The method of the request (eg: POST, GET, DELETE..)
		 * @param  {String} response The reply callback to handle the result of the request.
		 * @return {Object}          The JSON result of the request
		 */
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
				return encodeURIComponent( k ) + '=' + encodeURIComponent( data[k] );
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
			};

			// DELETE is often blocked by hosts.
			if ( 'DELETE' === method ) {
				headers['X-HTTP-Method-Override'] = method;
				method = 'POST';
			} else if ( 'GET' === method ) {
				endpoint += '?' + queryVars;
				queryVars = null;
				delete headers['Content-Type'];
			}

			ajaxRequest.open( method, endpoint );

			for ( var h in headers ) {
				ajaxRequest.setRequestHeader( h, headers[h] );
			}

			ajaxRequest.send( queryVars );
		},

		/**
		 * Perfoms a new GET request.
		 *
		 * @param  {String} endpoint The WP REST API route endpoint.
		 * @param  {Object} data     The request variables.
		 * @param  {string} response The reply callback to handle the result of the GET request.
		 * @return {Object}          The JSON result for the GET request
		 */
		get: function( endpoint, data, response ) {
			return this.request( endpoint, data, 'GET', response );
		},

		/**
		 * Perfoms a new POST request.
		 *
		 * @param  {String} endpoint The WP REST API route endpoint.
		 * @param  {Object} data     The request variables.
		 * @param  {string} response The reply callback to handle the result of the POST request.
		 * @return {Object}          The JSON result for the POST request
		 */
		post: function( endpoint, data, response ) {
			return this.request( endpoint, data, 'POST', response );
		},

		/**
		 * Perfoms a new DELETE request.
		 *
		 * @param  {String} endpoint The WP REST API route endpoint.
		 * @param  {Object} data     The request variables.
		 * @param  {string} response The reply callback to handle the result of the DELETE request.
		 * @return {Object}          The JSON result for the DELETE request
		 */
		remove: function( endpoint, data, response ) {
			return this.request( endpoint, data, 'DELETE', response );
		},

		/**
		 * Outputs a "BuddyPress" like notice to the user for 4 seconds.
		 *
		 * @param  {Object} container The HTML container to append the notice into.
		 * @param  {String} message   The content of the user notice.
		 * @param  {String} type      The type of the notice (eg: 'error' or 'updated')
		 * @return {Void}
		 */
		feedback: function( container, message, type ) {
			var notice;

			// Remove all previous notices.
			if ( 'DIV' === container.childNodes[0].nodeName && 'template-notices' === container.childNodes[0].getAttribute( 'id' ) ) {
				container.childNodes[0].remove();
			}

			if ( ! message || ! type ) {
				return;
			}

			if ( 'full' === type ) {
				container.innerHTML = bpReshare.templates.notice + container.innerHTML;
			} else {
				notice = document.createElement( 'DIV' );
				notice.setAttribute( 'id', 'template-notices' );
				notice.setAttribute( 'role', 'alert' );
				notice.setAttribute( 'aria-atomic', 'true' );
				notice.innerHTML = '<div id="message" class="' + type + '"><p>' + message + '</p></div>';

				container.insertBefore( notice, container.childNodes[0] );
			}

			window.setTimeout( function() {
				container.childNodes[0].remove();
			}, 4000 );
		}
	};

} )( window.bpReshare );
