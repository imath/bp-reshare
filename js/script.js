/* global bpReshare */

// Make sure the bpReshare object exists.
window.bpReshare = window.bpReshare || {};

( function( bpReshare, $ ) {

	// Bail if not set
	if ( typeof bpReshare.template === 'undefined' ) {
		return;
	}

	bpReshare.get = function() {
		if ( ! $( '.bp-reshare' ).length ) {
			return false;
		}

		$.each( $( '.bp-reshare' ), function( j, link ) {
			var id = parseInt( $( link ).parents( 'li' ).prop( 'id' ).replace( 'activity-', '' ), 10 );

			bpReshare.Ajax.get( id, { user_id: bpReshare.params.u }, function( response ) {
				$( link ).prop( 'href', bpReshare.strings[ response.link ].replace( '%i', id ) )
				         .removeClass( 'add-reshare remove-reshare' )
				         .addClass( 'removeLink' === response.link ? 'remove-reshare' : 'add-reshare' );

				$( link ).find( '.bp-screen-reader-text' ).html( bpReshare.strings[ response.text ] );
				$( link ).find( '.count' ).html( response.count || 0 );
			} );
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

		$.each( $( ul ).children(), function( i, selector ) {
			var id = parseInt( $( selector ).prop( 'id' ).replace( 'activity-', '' ), 10 );

			if ( ! $( selector ).find( 'a.bp-reshare' ).length ) {
				$( selector ).find( '.activity-meta a' ).first().after(
					bpReshare.template.replace( '%l', bpReshare.strings.addLink.replace( '%i', id ) )
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

} )( window.bpReshare, jQuery );
