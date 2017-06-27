/* global bpReshare */

// Make sure the bpReshare object exists.
window.bpReshare = window.bpReshare || {};

( function( bpReshare, $ ) {

	// Bail if not set
	if ( 'undefined' === typeof bpReshare.template ) {
		return;
	}

	bpReshare.activities = [];

	bpReshare.getURLparams = function( url, param ) {
		var qs;

		if ( url ) {
			qs = ( -1 !== url.indexOf( '?' ) ) ? '?' + url.split( '?' )[1] : '';
		} else {
			qs = document.location.search;
		}

		if ( ! qs ) {
			return null;
		}

		var params = qs.replace( /(^\?)/, '' ).split( '&' ).map( function( n ) {
			return n = n.split( '=' ), this[n[0]] = n[1], this;
		}.bind( {} ) )[0];

		if ( param ) {
			return params[param];
		}

		return params;
	};

	bpReshare.IndexOf = function( id, list ) {
		var r = -1;
		list = list || [];

		if ( ! id || ! $.isArray( list ) ) {
			return r;
		}

		$.each( list, function( i, o ) {
			if ( id === o.id ) {
				r = i;
			} else {
				return;
			}
		} );

		return r;
	};

	bpReshare.getTimeSince = function( timestamp ) {
		var now = new Date( $.now() ), diff, count_1, chunk_1, count_2, chunk_2,
			time_since = [], time_chunks = $.extend( {}, bpReshare.params.time_since.time_chunks ), ms;

		// Returns sometime
		if ( undefined === timestamp ) {
			return bpReshare.params.time_since.sometime;
		}

		// Javascript timestamps are in ms.
		timestamp = new Date( timestamp * 1000 );

		// Calculate the diff
		diff = now - timestamp;

		// Returns right now
		if ( 0 === diff ) {
			return bpReshare.params.time_since.now;
		}

		$.each( time_chunks, function( c, chunk ) {
			var milliseconds = chunk * 1000;
			var rounded_time = Math.floor( diff / milliseconds );

			if ( 0 !== rounded_time && ! chunk_1 ) {
				chunk_1 = c;
				count_1 = rounded_time;
				ms      = milliseconds;
			}
		} );

		// First chunk
		chunk_1 = chunk_1.substr( 2 );
		time_since.push( ( 1 === count_1 ) ? bpReshare.params.time_since[ chunk_1 ].replace( '%', count_1 ) : bpReshare.params.time_since[ chunk_1 + 's' ].replace( '%', count_1 ) );

		// Remove Year from chunks
		delete time_chunks.a_year;

		$.each( time_chunks, function( c, chunk ) {
			var milliseconds = chunk * 1000;
			var rounded_time = Math.floor( ( diff - ( ms * count_1 ) ) / milliseconds );

			if ( 0 !== rounded_time && ! chunk_2 ) {
				chunk_2 = c;
				count_2 = rounded_time;
			}
		} );

		// Second chunk
		if ( undefined !== chunk_2 ) {
			chunk_2 = chunk_2.substr( 2 );
			time_since.push( ( 1 === count_2 ) ? bpReshare.params.time_since[ chunk_2 ].replace( '%', count_2 ) : bpReshare.params.time_since[ chunk_2 + 's' ].replace( '%', count_2 ) );
		}

		// Returns x time, y time ago
		if ( time_since.length >= 1 ) {
			return bpReshare.params.time_since.ago.replace( '%', time_since.join( bpReshare.params.time_since.separator + ' ' ) );

		// Returns sometime
		} else {
			return bpReshare.params.time_since.sometime;
		}
	}

	bpReshare.get = function() {
		if ( ! $( '.bp-reshare' ).length ) {
			return false;
		}

		var activities = bpReshare.activities.map( function( k ) {
			return k.id;
		} ).join( ',' );

		bpReshare.Ajax.get( 'all', { activities: activities }, function( status, response ) {
			if ( 200 === status ) {
				if ( ! $.isArray( response ) || ! response.length ) {
					return;
				}

				$.each( response, function( i, r ) {
					var a_id = parseInt( r.id, 10 ), entry, link, reshareData = { link: 'addLink', text: 'addReshare' },
					    a = bpReshare.IndexOf( a_id, bpReshare.activities );

					if ( -1 !== a ) {
						bpReshare.activities[a].users = r.users;

						entry = $( '#activity-' + a_id );
						entry.prop( 'class', entry.prop( 'class' ).replace( /date-recorded-([0-9]+)/, 'date-recorded-' + r.time ) );
						entry.find( '.activity-header a.activity-time-since' ).after(
							$( '<span></span>' ).addClass( 'time-since' )
							                    .html( '&nbsp;' + bpReshare.getTimeSince( r.time ) )
						);

						link  = entry.find( '.bp-reshare' ).first();
						link.find( 'span.count' ).first().html( r.users.length );

						if ( -1 !== $.inArray( bpReshare.params.u.toString(), r.users ) ) {
							reshareData = { link: 'removeLink', text: 'removeReshare' }
						}

						link.prop( 'href', bpReshare.strings[ reshareData.link ].replace( '%i', a_id ) )
						    .removeClass( 'add-reshare remove-reshare' )
						    .addClass( 'removeLink' === reshareData.link ? 'remove-reshare' : 'add-reshare' );

						link.find( '.bp-screen-reader-text' ).html( bpReshare.strings[ reshareData.text ] );
					}
				} );
			} else {
				console.log( status );
			}
		} );

		return true;
	}

	bpReshare.clearInterval = function() {
		clearInterval( bpReshare.interval );
	}

	/**
	 * Add a Reshare button to activities
	 *
	 * @param  {object} ul The Activity Stream ul selector.
	 * @return {string}    The HTML for the elements.
	 */
	bpReshare.Button = function( type, ul ) {
		if ( ! ul ) {
			ul = '#activity-stream';
		}

		/**
		 * @todo
		 * This is not working when an activity is posted.
		 *
		 * In all cases we should only populate the bpReshare.activities array
		 * and perform the templating just before fetching users/count.
		 */
		$.each( $( ul ).children(), function( i, selector ) {
			var id         = parseInt( $( selector ).prop( 'id' ).replace( 'activity-', '' ), 10 ),
			    authorLink = $( selector ).find( '.activity-header a' ).first().prop( 'href' );

			if ( ! id ) {
				return;
			}

			bpReshare.activities.push( { id: id, users: [] } );

			if ( ! $( selector ).find( 'a.bp-reshare' ).length ) {
				$( selector ).find( '.activity-meta a' ).first().after(
					bpReshare.template.replace( '%l', bpReshare.strings.addLink.replace( '%i', id ) )
					                  .replace( '%a', id )
					                  .replace( '%u', authorLink.replace( bpReshare.params.root_members, '' ).replace( '/', '' ) )
					                  .replace( '%t', bpReshare.strings.addReshare )
					                  .replace( '%c', 0 )
				);
			}
		} );

		// When displaying the stream populate counts.
		if ( 'populate' === type ) {
			bpReshare.get();

		// When Ajax refreshing the stream, use an interval before populating counts.
		} else {
			bpReshare.interval = setInterval( function() {
				var isRefreshed = bpReshare.get();

				if ( true === isRefreshed ) {
					bpReshare.clearInterval();
				}
			}, 500 );
		}

		return $( ul ).prop( 'outerHTML' );
	}
	$( document ).ready( bpReshare.Button( 'populate' ) );

	/**
	 * Intercepts Ajax responses to make sure Reshare buttons are added to it.
	 *
	 * @param  {[type]} event    [description]
	 * @param  {[type]} xhr      [description]
	 * @param  {[type]} settings [description]
	 * @return {void}
	 */
	$( document ).ajaxSuccess( function( event, xhr, settings ) {
		var isHeartbeat = 0 === decodeURIComponent( settings.data ).indexOf( 'data[bp_activity_last_recorded]' ),
		    isReshare   = settings.url && -1 !== settings.url.indexOf( bpReshare.params.root_url ),
		    activities, content, newContent;

		if ( ! isHeartbeat && ! isReshare ) {
			activities = $( xhr.responseJSON.contents )[0];
			newContent = bpReshare.Button( 'refresh', activities );

			content = $.map( $( xhr.responseJSON.contents ), function( e, k ) {
				if ( 0 === k ) {
					return newContent;
				}

				return $( e ).prop( 'outerHTML' );
			} ).join( ' ' );

			xhr.responseJSON.contents = content;
			xhr.responseText = JSON.stringify( xhr.responseJSON );
		}
	} );

	bpReshare.add = function( event ) {
		var link = event.currentTarget, id = $( link ).data( 'activity-id' ), author = $( link ).data( 'author-name' );

		event.preventDefault();

		if ( author === bpReshare.params.u_nicename ) {
			return;
		}

		/**
		 * @todo
		 * If the user is the author: can't add or remove.
		 * If the user is in the users who reshared: can remove reshare.
		 * If the user is not in the users who reshared: can add reshare.
		 */
		bpReshare.Ajax.post( id, { user_id: bpReshare.params.u }, function( status, response ) {
			if ( 200 === status ) {
				console.log( response );
			} else {
				console.log( status );
			}
		} );
	}
	$( '#buddypress' ).on( 'click', '.bp-reshare', bpReshare.add );

} )( window.bpReshare, jQuery );
