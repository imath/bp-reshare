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
	};

	bpReshare.refreshActivity = function( activityId ) {
		if ( ! activityId ) {
			return false;
		}

		var link, entry, reshareData = { link: 'addLink', text: 'addReshare' }, activity = {};

		if ( activityId.id ) {
			activity = activityId;
		} else {
			activityId = parseInt( activityId, 10 );
			var activityIndex = bpReshare.IndexOf( activityId, bpReshare.activities )

			if ( -1 === activityIndex ) {
				return false;
			} else {
				activity = bpReshare.activities[ activityIndex ];
			}
		}

		if ( ! $( '#activity-' + activity.id ).length || true !== activity.isChecked ) {
			return false;
		}

		entry = $( '#activity-' + activity.id );

		if ( ! entry.find( '.reshare-time-since' ).length && activity.time ) {
			entry.prop( 'class',
				entry.prop( 'class' )
				     .replace( /date-recorded-([0-9]+)/, 'date-recorded-' + activity.time )
			);

			entry.find( '.activity-header a.activity-time-since' ).after(
				$( '<span></span>' ).addClass( 'time-since reshare-time-since' )
				                    .html( '&nbsp;' + bpReshare.getTimeSince( activity.time ) )
			);
		}

		link  = entry.find( '.bp-reshare' ).first();
		link.find( 'span.count' ).first().html( activity.users.length );

		if ( -1 !== $.inArray( bpReshare.params.u.toString(), activity.users ) ) {
			reshareData = { link: 'removeLink', text: 'removeReshare' };
		}

		link.prop( 'href', bpReshare.strings[ reshareData.link ].replace( '%i', activity.id ) )
		    .removeClass( 'add-reshare remove-reshare' )
		    .addClass( 'removeLink' === reshareData.link ? 'remove-reshare' : 'add-reshare' );

		link.find( '.bp-screen-reader-text' ).html( bpReshare.strings[ reshareData.text ] );

		return true;
	}

	bpReshare.get = function() {
		if ( ! $( '.bp-reshare' ).length ) {
			return false;
		}

		var unchecked = [];

		$.each( bpReshare.activities, function( i, activity ) {
			if ( true === bpReshare.activities[i].isChecked ) {
				bpReshare.refreshActivity( activity );
			} else {
				unchecked.push( activity.id );
				bpReshare.activities[i].isChecked = true;
			}
		} );

		if ( unchecked.length ) {
			bpReshare.Ajax.get( 'all', { activities: unchecked.join( ',' ) }, function( status, response ) {
				if ( 200 === status ) {
					if ( ! $.isArray( response ) || ! response.length ) {
						return;
					}

					$.each( response, function( i, r ) {
						var activityId = parseInt( r.id, 10 ), a = bpReshare.IndexOf( activityId, bpReshare.activities );

						if ( -1 !== a ) {
							bpReshare.activities[a].users     = r.users;
							bpReshare.activities[a].time      = r.time;

							bpReshare.refreshActivity( activityId );
						}
					} );
				} else {
					console.log( status );
				}
			} );
		}

		return true;
	};

	bpReshare.clearInterval = function() {
		clearInterval( bpReshare.interval );
	};

	bpReshare.Scan = function( stream ) {
		if ( ! stream ) {
			return false;
		}

		$.each( $( stream ).children(), function( i, selector ) {
			var id = parseInt( $( selector ).prop( 'id' ).replace( 'activity-', '' ), 10 );

			if ( ! id ) {
				return;
			}

			if ( -1 === bpReshare.IndexOf( id, bpReshare.activities ) ) {
				bpReshare.activities.push( { id: id, users: [], markUp: false, isChecked: false } );
			}
		} );

		return true;
	};

	bpReshare.setMarkup = function() {
		if ( ! bpReshare.activities.length ) {
			return false;
		}

		$.each( bpReshare.activities, function( i, activity ) {
			var selector   = $( '#activity-' + activity.id ),
			    authorLink = $( selector ).find( '.activity-header a' ).first().prop( 'href' );

			if ( ! selector.length || $( selector ).find( 'a.bp-reshare' ).length ) {
				return;
			}

			if ( false === activity.markUp ) {
				bpReshare.activities[i].markUp = bpReshare.template.replace( '%l', bpReshare.strings.addLink.replace( '%i', activity.id ) )
				                                                   .replace( '%r', 'add-reshare' )
				                                                   .replace( '%a', activity.id )
				                                                   .replace( '%u', authorLink.replace( bpReshare.params.root_members, '' ).replace( '/', '' ) )
				                                                   .replace( '%t', bpReshare.strings.addReshare )
				                                                   .replace( '%c', 0 );
			}

			$( selector ).find( '.activity-meta a' ).first().after(
				bpReshare.activities[i].markUp
			);
		} );

		return true;
	};

	/**
	 * Add a Reshare button to activities
	 *
	 * @param  {object} ul The Activity Stream ul selector.
	 * @return {string}    The HTML for the elements.
	 */
	bpReshare.Button = function( type, stream ) {
		if ( ! stream ) {
			stream = '#activity-stream';
		}

		if ( true !== bpReshare.Scan( stream ) ) {
			return;
		}

		// When displaying the stream populate counts.
		if ( 'populate' === type ) {
			bpReshare.setMarkup();
			bpReshare.get();

		// When Ajax refreshing the stream, wait a few milliseconds before populating counts.
		} else {
			window.setTimeout( function() {
				bpReshare.setMarkup();
				bpReshare.get();
			}, 500 );
		}
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
		var requestData = decodeURIComponent( settings.data ),
		    action      = bpReshare.getURLparams( '?' + requestData, 'action' ),
		    isReshare   = settings.url && -1 !== settings.url.indexOf( bpReshare.params.root_url ),
		    activities, content, newContent;

		if ( ! isReshare && -1 !== $.inArray( action, ['activity_get_older_updates', 'activity_widget_filter', 'post_update' ] ) ) {
			if ( 'post_update' === action ) {
				activities = $( '<ul></ul>' ).html( xhr.responseText );
			} else {
				activities = $( xhr.responseJSON.contents )[0];

				if ( 'LI' === activities.nodeName ) {
					activities = $( '<ul></ul>' ).html( $.map( $( xhr.responseJSON.contents ), function( l ) {
						return $( l ).prop( 'outerHTML' );
					} ).join( ' ' ) );
				}

				// Reset the activities to get fresher reshares
				if ( 'activity_widget_filter' === action ) {
					bpReshare.activities = [];
				}
			}

			bpReshare.Button( 'refresh', activities );
		}
	} );

	/**
	 * Heartbeat Activities.
	 * @param  {object} event The click event.
	 * @return {void}
	 */
	$( '#buddypress ul.activity-list' ).click( '#newest', function( event ) {
		var stream = $( event.currentTarget ).closest( 'ul' );

		// Wait a few milliseconds to be able to only get the Heartbeat Activities.
		window.setTimeout( function() {
			activities = $( '<ul></ul>' ).html( $.map( $( stream.find( '.just-posted' ) ), function( l ) {
				return $( l ).prop( 'outerHTML' );
			} ).join( ' ' ) );

			bpReshare.Button( 'populate', activities );
		}, 500 );
	} );

	bpReshare.add = function( event ) {
		var link = event.currentTarget, id = $( link ).data( 'activity-id' ), author = $( link ).data( 'author-name' );

		event.preventDefault();

		// If the user is the author: can't add or remove.
		if ( author === bpReshare.params.u_nicename ) {
			return;
		}

		// If the user is not in the users who reshared: can add reshare.
		if ( $( link ).hasClass( 'add-reshare' ) ) {
			bpReshare.Ajax.post( id, { user_id: bpReshare.params.u }, function( status, response ) {
				if ( 200 === status ) {
					/**
					 * @todo Once done, the class and link need to be updated
					 */
					console.log( response );
				} else {
					console.log( status );
				}
			} );

		// If the user is in the users who reshared: can remove reshare.
		} else if ( $( link ).hasClass( 'remove-reshare' ) ) {
			bpReshare.Ajax.delete( id, { user_id: bpReshare.params.u }, function( status, response ) {
				if ( 200 === status ) {
					/**
					 * @todo Once done, the class and link need to be updated
					 */
					console.log( response );
				} else {
					console.log( status );
				}
			} );
		}
	}
	$( '#buddypress' ).on( 'click', '.bp-reshare', bpReshare.add );

} )( window.bpReshare, jQuery );
