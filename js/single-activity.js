/* global bpReshare */

// Make sure the bpReshare object exists.
window.bpReshare = window.bpReshare || {};

( function( bpReshare, $ ) {
	// Bail if not set
	if ( 'undefined' === typeof bpReshare.activity ) {
		return;
	}

	bpReshare.outputNoItem = function( parent, navItemName ) {
		if ( $( parent ).find( '#message' ).length ) {
			return;
		}

		$( parent ).prepend(
			$( '<div></div>' ).prop( 'id', 'message' )
							  .addClass( 'info' )
							  .html( '<p>' + bpReshare.activity.nav[ navItemName ].no_item + '</p>' )
		);
	};

	bpReshare.activityNav = function() {
		var i = 0, nav = [];

		$.each( bpReshare.activity.nav, function( name, navItem ) {
			var label; navItem.count = 0;

			if ( i === navItem.position ) {

				if ( 'comments' === name ) {
					navItem.count = parseInt( $( '#acomment-comment-' + bpReshare.activity.id + ' span' ).html(), 10 );
				} else {
					navItem.count = navItem.users.length;
				}

				if ( 1 >= navItem.count ) {
					label = navItem.singular;
				} else {
					label = navItem.plural;
				}

				nav.push( $( '<a></a>' ).html( label )
				                        .prop( { id: 'display-' + name, href: '#display-' + name } )
				                        .get( 0 ).outerHTML
				);
			}

			i++;
		} );

		$( '#activity-' + bpReshare.activity.id + ' .activity-comments' ).before(
			$( '<ul></ul>' ).html( '<li>' + nav.join( '</li><li>' ) + '</li>' )
			                .prop( 'id', 'buddyreshare-activity-nav' )
		).addClass( 'buddyreshare-nav-content' );

		$( '#display-comments' ).parent().addClass( 'selected' );
		bpReshare.outputNoItem( '#activity-' + bpReshare.activity.id + ' .activity-comments', 'comments' );

		if ( bpReshare.activity.nav.favorites && bpReshare.activity.nav.favorites.count ) {
			$( '#activity-' + bpReshare.activity.id + ' .activity-meta' ).find( '.fav, .unfav' )
			                                                             .prepend( '&nbsp;' )
				                                                         .prepend( $( '<span></span>' ).addClass( 'count' )
																		                               .html( bpReshare.activity.nav.favorites.count ) );
		}
	}

	$( '#buddypress' ).on( 'click', '#buddyreshare-activity-nav li a', function( event ) {
		var navItem = $( event.currentTarget ), contentItem = navItem.prop( 'id' ).replace( 'display', 'activity' ),
		    navItemName = navItem.prop( 'id' ).replace( 'display-', '' );

		event.preventDefault();

		$.each( $( '#buddyreshare-activity-nav li a' ), function( l, link ) {
			$( link ).parent().removeClass( 'selected' );

			if ( $( link ).prop( 'id' ) === navItem.prop( 'id' ) ) {
				$( link ).parent().addClass( 'selected' );
			}
		} );

		if ( ! $( '#activity-' + bpReshare.activity.id + ' .' + contentItem ).length ) {
			$( '#activity-' + bpReshare.activity.id ).append(
				$( '<div></div>' ).addClass( contentItem + ' buddyreshare-nav-content' )
			);
		}

		$.each( $( '.buddyreshare-nav-content' ), function( c, content ) {
			if ( $( content ).hasClass( contentItem ) ) {
				$( content ).show();

				if ( 0 === bpReshare.activity.nav[ navItemName ].count ) {
					bpReshare.outputNoItem( content, navItemName );

				} else {
					$( content ).find( '#message' ).remove();
				}

			} else {
				$( content ).hide();
			}
		} );
	} );

	$( document ).ready( function() {
		bpReshare.activityNav();
	} );
} )( window.bpReshare, jQuery );
