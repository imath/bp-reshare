// Make sure the bpReshare object exists.
window.bpReshare = window.bpReshare || {};

( function( bpReshare, $ ) {

	// Bail if not set
	if ( 'undefined' === typeof bpReshare.templates ) {
		return;
	}

	/**
	 * Main var to store loaded activities.
	 *
	 * @type {Array}
	 */
	bpReshare.activities = [];

	/**
	 * Loaded activities that cannot be reshared.
	 *
	 * @type {Object}
	 */
	bpReshare.disabledActivities = {};

	/**
	 * Parse an URL to get its query vars.
	 *
	 * @param  {String} url   The URL to parse.
	 * @param  {String} param The key of the query var to get.
	 * @return {Mixed}        A list of keyed query vars or the value of a specific one.
	 */
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

	/**
	 * Checks if an activity can be reshared.
	 *
	 * @param  {Integer} activityID The activity ID.
	 * @param  {String}  type       The activity action/type (eg: activity_update)
	 * @param  {Object}  selector   An HTML element containing the list of loaded activities.
	 * @return {Boolean}            True the activity type can't be reshared.
	 *                              False otherwise.
	 */
	bpReshare.isTypeDisabled = function( activityID, type, selector ) {
		if ( ! activityID ) {
			return true;
		}

		// Defaults to enabled.
		var isDisabled = false;

		if ( type ) {
			isDisabled = bpReshare.params.disabled_types && -1 !== $.inArray( type, bpReshare.params.disabled_types );

		// Check the entry classes.
		} else {
			if ( 'undefined' === typeof bpReshare.disabledActivities[ activityID ] ) {
				var classes = $( '#activity-' + activityID ).prop( 'class' );

				if ( ! classes && selector ) {
					classes = $( selector ).prop( 'class' );
				}

				classes = classes.split( ' ' );

				if ( bpReshare.params.disabled_types ) {
					$.each( bpReshare.params.disabled_types, function( t, action ) {
						if ( -1 !== $.inArray( action, classes ) ) {
							isDisabled = true;
						}
					} );

					bpReshare.disabledActivities[ activityID ] = isDisabled;
				}
			} else {
				isDisabled = bpReshare.disabledActivities[ activityID ];
			}
		}

		return isDisabled;
	};

	/**
	 * Find the position of an item ID into a list of items.
	 *
	 * @param  {Integer} id   The ID of the item to find.
	 * @param  {Array}   list The list of objects to look into.
	 * @return {Integer}      The position of the found ID into the list.
	 */
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

	/**
	 * Build the time since of each reshared activity.
	 *
	 * @param  {Integer} timestamp The timestamp of the reshare.
	 * @return {String}            The human readable time since.
	 */
	bpReshare.getTimeSince = function( timestamp ) {
		var now = new Date( $.now() ), diff, count_1, chunk_1, count_2, chunk_2,
			time_since = [], time_chunks = $.extend( {}, bpReshare.params.time_since.time_chunks ), ms;

		// Returns sometime
		if ( undefined === timestamp ) {
			return '&nbsp;' + bpReshare.params.time_since.sometime;
		}

		// Javascript timestamps are in ms.
		timestamp = new Date( timestamp * 1000 );

		// Calculate the diff
		diff = now - timestamp;

		// Returns right now
		if ( 1000 > diff ) {
			return '&nbsp;' + bpReshare.params.time_since.now;
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

		// Returns sometime by default
		var retval = bpReshare.params.time_since.sometime;

		// Returns x time ago
		if ( time_since.length >= 1 ) {
			retval = bpReshare.params.time_since.ago.replace( '%', time_since.shift() );
		}

		return '&nbsp;' + retval;
	};

	/**
	 * Update the Activity global with the Reshares data.
	 *
	 * @param  {Integer} activityId The activity ID to update.
	 * @return {Boolean}            True on success.
	 *                              False otherwise.
	 */
	bpReshare.refreshActivity = function( activityId ) {
		if ( ! activityId ) {
			return false;
		}

		var link, entry, reshareData = { link: 'addLink', text: 'addReshare' }, activity = {},
		    timeSince = '.activity-header a.activity-time-since';

		if ( activityId.id ) {
			activity = activityId;
		} else {
			activityId = parseInt( activityId, 10 );
			var activityIndex = bpReshare.IndexOf( activityId, bpReshare.activities );

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

			if ( ! entry.find( timeSince ).length ) {
				timeSince = '.activity-header span.time-since';
			}

			entry.find( timeSince ).after(
				$( '<span></span>' ).addClass( 'reshare-time-since' )
				                    .html( bpReshare.getTimeSince( activity.time ) )
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
	};

	/**
	 * Use a REST request to fetch the Reshares data for the loaded
	 * activities.
	 *
	 * @return {Boolean} True on success.
	 *                   False otherwise.
	 */
	bpReshare.get = function() {
		if ( ! $( '.bp-reshare' ).length ) {
			return false;
		}

		var unchecked = [];

		$.each( bpReshare.activities, function( i, activity ) {
			if ( true === bpReshare.activities[i].isChecked ) {
				bpReshare.refreshActivity( activity );
			} else if ( bpReshare.isTypeDisabled( activity.id ) ) {
				return;
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
							bpReshare.activities[a].users = r.users;
							bpReshare.activities[a].time  = r.time;

							bpReshare.refreshActivity( activityId );
						}
					} );
				}
			} );
		}

		return true;
	};

	/**
	 * Look into a stream and init a new entry into the Activities' global global.
	 *
	 * @param  {Object} stream The HTML object of the stream.
	 * @return {Boolean}       True if the stream has been checked.
	 *                         False otherwise.
	 */
	bpReshare.Scan = function( stream ) {
		if ( ! stream ) {
			return false;
		}

		$.each( $( stream ).children(), function( i, selector ) {
			var id = parseInt( $( selector ).prop( 'id' ).replace( 'activity-', '' ), 10 );

			if ( ! id || bpReshare.isTypeDisabled( id, null, selector ) ) {
				return;
			}

			if ( -1 === bpReshare.IndexOf( id, bpReshare.activities ) ) {
				bpReshare.activities.push( { id: id, users: [], markUp: false, isChecked: false } );
			}
		} );

		return true;
	};

	/**
	 * Build the specific Reshare markup for each activity of the stream.
	 *
	 * @return {Boolean} True if the markup was successfully built.
	 *                   False otherwise.
	 */
	bpReshare.setMarkup = function() {
		if ( ! bpReshare.activities.length ) {
			return false;
		}

		$.each( bpReshare.activities, function( i, activity ) {
			var selector   = $( '#activity-' + activity.id ),
			    authorLink = $( selector ).find( '.activity-header a' ).first().prop( 'href' );

			if ( ! selector.length || $( selector ).find( 'a.bp-reshare' ).length || bpReshare.isTypeDisabled( activity.id ) ) {
				return;
			}

			if ( false === activity.markUp ) {
				var authorName = $.trim( authorLink.replace( bpReshare.params.root_members, '' ), '/' ).split( '/' )[0], className = 'add-reshare';

				if ( authorName === bpReshare.params.u_nicename ) {
					className = 'disabled';
				}

				/**
				 * Add it to our global to avoid doing the same thing more than once.
				 *
				 * @type {Array}
				 */
				bpReshare.activities[i].markUp = bpReshare.templates.reshareButton.replace( '%l', bpReshare.strings.addLink.replace( '%i', activity.id ) )
				                                                                  .replace( '%r', className )
				                                                                  .replace( '%a', activity.id )
				                                                                  .replace( '%u', authorName )
				                                                                  .replace( '%t', bpReshare.strings.addReshare )
				                                                                  .replace( '%c', 0 );
			}

			$( selector ).find( '.activity-meta a' ).first().after(
				bpReshare.activities[i].markUp
			);

			if ( bpReshare.displayedUser ) {
				var activityAvatar      = $( selector ).find( '.activity-avatar' ),
				    displayedUserAvatar = $( bpReshare.displayedUser.avatar ).prop( 'src' );

				if ( displayedUserAvatar.split( '?' )[0] !== activityAvatar.find( 'img' ).prop( 'src' ).split( '?' )[0] && ! $( selector ).find( '.activity-reshared-by' ).length ) {
					$( activityAvatar ).before(
						$( '<div></div>' ).addClass( 'activity-reshared-by' )
						                  .html( bpReshare.displayedUser.resharedText.replace( '%s', bpReshare.displayedUser.avatar ) )
					);
				}
			}
		} );

		return true;
	};

	/**
	 * Checks if the current BuddyPress Activity scope is set to "reshares".
	 *
	 * @return {Boolean} True if the BuddyPress Activity Scope is set to Reshares.
	 *                   False otherwise.
	 */
	bpReshare.isResharesScope = function() {
		return 'reshares' === decodeURIComponent( document.cookie ).split( ';' ).map( function( i ) {
			var j = i.split( '=' );

			if ( 'bp-activity-scope' === $.trim( j[0] ) ) {
				return $.trim( j[1] );
			}
		} ).filter( function( k ) {
			if ( k ) {
				return k;
			}
		} ).shift();
	};

	/**
	 * Add a Reshare Tab to the Activity directory.
	 *
	 * This tab is listing the reshares the current user did.
	 *
	 * @param  {Integer} number  The number of Reshares the user did.
	 * @param  {Boolean} tabClass Whether the selected class should be added to the tab.
	 * @return {Void}
	 */
	bpReshare.activityTab = function( number, tabClass ) {
		bpReshare.params.u_count = parseInt( bpReshare.params.u_count, 10 );

		if ( ! bpReshare.params.u_count && ! number ) {
			return;
		}

		if ( ! number ) {
			number = bpReshare.params.u_count;
		} else if ( '-1' === number )  {
			bpReshare.params.u_count -= 1;
		} else {
			bpReshare.params.u_count += parseInt( number, 10 );
		}

		if ( $( 'body.directory #activity-reshares' ).length ) {
			$( 'body.directory #activity-reshares' ).find( 'span' ).first().html( bpReshare.params.u_count );
		} else {
			$( 'body.directory .activity-type-tabs ul li' ).first().after(
				$( bpReshare.templates.directoryTab.replace( '%c', bpReshare.params.u_count ) ).addClass( true === tabClass ? 'selected' : '' )
			);
		}
	};

	/**
	 * Add a Reshare button to activities
	 *
	 * @param  {String} type   Set to "populate" when all activities are fully loaded.
	 * @param  {Object} stream The Activity Stream selector.
	 * @return {Void}
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
	};

	$( document ).ready( function() {
		bpReshare.Button( 'populate' );
		bpReshare.activityTab( null, bpReshare.isResharesScope() );
	} );

	/**
	 * Intercepts Ajax responses to make sure Reshare buttons are added to it.
	 *
	 * @param  {Object} event    The jQuery ajax Success event.
	 * @param  {Object} xhr      The request.
	 * @param  {Object} settings The settings data.
	 * @return {Void}
	 */
	$( document ).ajaxSuccess( function( event, xhr, settings ) {
		var requestData = decodeURIComponent( settings.data ),
		    action      = bpReshare.getURLparams( '?' + requestData, 'action' ),
		    isReshare   = settings.url && -1 !== settings.url.indexOf( bpReshare.params.root_url ),
		    activities;

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
	 * Add the reshare button to the newest activities (Heartbeat).
	 *
	 * @param  {Object} event The Load-Newest click event.
	 * @return {Void}
	 */
	$( 'body.activity #buddypress' ).on( 'click', '.load-newest a', function( event ) {
		var stream = $( event.currentTarget ).closest( 'ul' ), parent = $( event.currentTarget ).parent();

		// Wait a few milliseconds to be able to only get the Heartbeat Activities.
		window.setTimeout( function() {
			var activities = $( '<ul></ul>' ).html( $.map( $( stream.find( '.just-posted' ) ), function( l ) {
				return $( l ).prop( 'outerHTML' );
			} ).join( ' ' ) );

			bpReshare.Button( 'populate', activities );

			if ( parent.length ) {
				parent.remove();
			}
		}, 500 );
	} );

	/**
	 * Listens to the Reshare Link click to add/remove a reshare for the activity.
	 *
	 * @param  {Object} event Reshare Link click
	 * @return {Void}
	 */
	bpReshare.add = function( event ) {
		var link = event.currentTarget, id = $( link ).data( 'activity-id' ), author = $( link ).data( 'author-name' ),
		    countSpan = $(link).find( 'span.count' ).first(), timeSince = '.activity-header a.activity-time-since';

		event.preventDefault();

		// If the user is the author: can't add or remove.
		if ( author === bpReshare.params.u_nicename ) {
			return;
		}

		// If the user is not in the users who reshared: can add reshare.
		if ( $( link ).hasClass( 'add-reshare' ) ) {
			bpReshare.Ajax.post( id, { 'user_id': bpReshare.params.u, 'author_slug': author }, function( status, response ) {
				if ( 200 === status && response.reshared ) {

					// Remove the reshare header if any.
					$( link ).closest( '.activity-content' ).find( '.reshare-time-since' ).remove();

					if ( ! $( '#activity-' + id ).find( timeSince ).length ) {
						timeSince = '.activity-header span.time-since';
					}

					// Update the link for a remove reshare one
					$( link ).removeClass( 'add-reshare' )
					         .addClass( 'remove-reshare' )
					         .prop( 'href', bpReshare.strings.removeLink.replace( '%i', id ) )
					         .closest( '.activity-content' )
					         .find( timeSince )
					         .after(
					          $( '<span></span>' ).addClass( 'reshare-time-since' )
					                              .html( bpReshare.getTimeSince( response.reshared ) )
					         );

					$( countSpan ).html( parseInt( $( countSpan ).html(), 10 ) + 1 );

					bpReshare.activityTab( 1 );
				} else {
					var error = bpReshare.strings.genericError;
					if ( response.message ) {
						error = response.message;
					}

					bpReshare.Ajax.feedback( $( link ).closest( '.activity-meta' ).get( 0 ), error, 'error' );
				}
			} );

		// If the user is in the users who reshared: can remove reshare.
		} else if ( $( link ).hasClass( 'remove-reshare' ) ) {
			bpReshare.Ajax.remove( id, { 'user_id': bpReshare.params.u, 'author_slug': author }, function( status, response ) {
				if ( 200 === status ) {

					// Update the link for a remove reshare one
					$( link ).removeClass( 'remove-reshare' )
					         .addClass( 'add-reshare' )
					         .prop( 'href', bpReshare.strings.addLink.replace( '%i', id ) )
					         .closest( '.activity-content' )
					         .find( '.reshare-time-since' )
					         .remove();

					$( countSpan ).html( parseInt( $( countSpan ).html(), 10 ) - 1 );

					bpReshare.activityTab( '-1' );

					if ( bpReshare.isResharesScope() || ( bpReshare.displayedUser && bpReshare.params.u === bpReshare.displayedUser.userID ) ) {
						$( '#activity-' + id ).remove();
					}
				} else {
					var error = bpReshare.strings.genericError;
					if ( response.message ) {
						error = response.message;
					}

					bpReshare.Ajax.feedback( $( link ).closest( '.activity-meta' ).get( 0 ), error, 'error' );
				}
			} );
		}
	};
	$( '#buddypress' ).on( 'click', '.bp-reshare', bpReshare.add );

	/**
	 * Listens to BuddyPress' Time Since updates to also update the Reshare time since.
	 *
	 * @param  {Object} event livestamp event
	 * @return {Void}
	 */
	$( '#buddypress' ).on( 'change.livestamp', '.time-since', function( event ) {
		var li = $( event.currentTarget ).parents( 'li' ).first(), reshareLink = li.find( '.bp-reshare' ).first(),
		    activityID, activityIndex;

		if ( $( reshareLink ).length ) {
			activityID    = parseInt( $( reshareLink ).data( 'activity-id' ), 10 );
			activityIndex = bpReshare.IndexOf( activityID, bpReshare.activities );

			if ( -1 !== activityIndex ) {
				$( li ).find( '.reshare-time-since' ).html( bpReshare.getTimeSince( bpReshare.activities[ activityIndex ].time ) );
			}
		}
	} );

} )( window.bpReshare, jQuery );
