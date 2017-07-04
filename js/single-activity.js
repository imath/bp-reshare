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

		// Empty remaining users.
		if ( 'comments' !== navItemName ) {
			$( parent ).html( '' );
		}

		$( parent ).prepend(
			$( '<div></div>' ).prop( 'id', 'message' )
			                  .addClass( 'info' )
			                  .html( '<p>' + bpReshare.activity.nav[ navItemName ].no_item + '</p>' )
		);
	};

	bpReshare.getLoading = function( container ) {
		$( container ).html(
			$( '<div></div>' ).css( {
				background: 'url( ' + bpReshare.activity.loader +' ) no-repeat',
				width: '100%',
				height: '40px',
				'background-position': '50% 50%',
			} )
		);
	};

	bpReshare.getUsers = function( content, navItemName, page ) {
		if ( ! page ) {
			page = 1;
		}

		// Add a loading gif
		if ( 1 === page ) {
			bpReshare.getLoading( content );
		}

		var getData = {
			type:    navItemName,
			include: bpReshare.activity.nav[ navItemName ].users.join( ',' ),
			page:    page
		}

		bpReshare.Ajax.get( bpReshare.activity.id, getData, function( status, response ) {
			if ( 200 === status && response.users ) {
				bpReshare.activity.nav[ navItemName ].usersDetail = response.users;

				if ( response.has_more ) {
					bpReshare.activity.nav[ navItemName ].nextPage = page + 1;
					response.users.loadmore = '<li class="load-more-users"><a href="#" data-next-page="' + bpReshare.activity.nav[ navItemName ].nextPage + '">Load More</a></li>';
				}

				if ( 1 === page || ! $( '#' + navItemName + '-users' ).length ) {
					$( content ).html(
						$( '<ul></ul>' ).prop( { id: navItemName + '-users', class: 'item-list', 'aria-live': 'assertive', 'aria-relevant' : 'all' } )
					);
				} else if ( $( content ).find( '.loading' ).length ) {
					$( content ).find( '.loading' ).remove();
				}

				$.each( response.users, function( i, user ) {
					$( '#' + navItemName + '-users' ).append( user );
				} );
			} else {
				console.log( status );
			}
		} );
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

		if ( 0 === bpReshare.activity.nav.comments.count ) {
			bpReshare.outputNoItem( '#activity-' + bpReshare.activity.id + ' .activity-comments', 'comments' );
		}

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
			var output;

			if ( $( content ).hasClass( contentItem ) ) {
				$( content ).show();

				if ( 0 === bpReshare.activity.nav[ navItemName ].count ) {
					bpReshare.outputNoItem( content, navItemName );

				} else {
					$( content ).find( '#message' ).remove();

					// Here is populating
					if ( ! $( content ).hasClass( 'activity-comments' ) ) {
						bpReshare.getUsers( content, navItemName );
					}
				}

			} else {
				$( content ).hide();
			}
		} );
	} );

	$( '#buddypress' ).on( 'click', '.buddyreshare-nav-content .load-more-users a', function( event ) {
		event.preventDefault();

		var page = $( event.currentTarget ).data( 'next-page' );
		    content = $( event.currentTarget ).closest( '.buddyreshare-nav-content' );
		    navItemName = $( content ).prop( 'class' ).split( ' ' )[0].replace( 'activity-', '' );

		$( event.currentTarget ).parent().addClass( 'loading' ).html(
			bpReshare.getLoading( $(this) )
		);

		bpReshare.getUsers( content, navItemName, page );
	} );

	bpReshare.refreshCounts = function( navItemName, user, number ) {
		var count = 0;

		// Update users for reshares and favorites count.
		if ( 'comments' !== navItemName ) {
			if ( '-1' === number ) {
				bpReshare.activity.nav[ navItemName ].users.splice( bpReshare.activity.nav[ navItemName ].users.indexOf( user.toString() ), 1 );
			} else if ( 1 === number ) {
				bpReshare.activity.nav[ navItemName ].users.push( user.toString() );
			}

			count = bpReshare.activity.nav[ navItemName ].users.length;

			/**
			 * Make sure to default to Comment(s) content so That
			 * the user needs to refresh the users who favorited
			 * or reshared.
			 */
			$( '#display-comments' ).trigger( 'click' );

		// For the comments, we simply need to make sur the Nav label is consistent.
		} else {
			count = number;
			$( '.activity-comments' ).find( '#message.info' ).remove();
		}

		if ( count <= 1 ) {
			$( '#display-' + navItemName ).html( bpReshare.activity.nav[ navItemName ].singular );
		} else {
			$( '#display-' + navItemName ).html( bpReshare.activity.nav[ navItemName ].plural );
		}

		return count;
	}

	$( '#buddypress .activity-meta' ).on( 'click', 'a', function( event ) {
		if ( bpReshare.isTypeDisabled( bpReshare.activity.id ) ) {
			return;
		}
		
		var number = 0;

		// Make sure the comments are displayed.
		if ( $( event.currentTarget ).hasClass( 'acomment-reply' ) ) {
			$( '#display-comments' ).trigger( 'click' );

		// Update Reshares nav and count.
		} else if ( $( event.currentTarget ).hasClass( 'bp-reshare' ) ) {
			if ( $( event.currentTarget ).hasClass( 'add-reshare' ) ) {
				number = 1;
			} else if ( $( event.currentTarget ).hasClass( 'remove-reshare' ) ) {
				number = '-1';
			}

			bpReshare.activity.nav.reshares.count = bpReshare.refreshCounts( 'reshares', bpReshare.params.u, number );
		}
	} );

	$( document ).ajaxSuccess( function( event, xhr, settings ) {
		if ( bpReshare.isTypeDisabled( bpReshare.activity.id ) ) {
			return;
		}

		var requestData = decodeURIComponent( settings.data ), number = 1;
		    action      = bpReshare.getURLparams( '?' + requestData, 'action' );

		// Update the favorites nav label and the favorites count.
		if ( 'activity_mark_fav' === action || 'activity_mark_unfav' === action ) {
			if ( 'activity_mark_unfav' === action ) {
				number = '-1';
			}

			bpReshare.activity.nav.favorites.count = bpReshare.refreshCounts( 'favorites', bpReshare.params.u, number );

			window.setTimeout( function() {
				$( '#activity-' + bpReshare.activity.id + ' .activity-meta' ).find( '.fav, .unfav' ).html(
					$( '<span></span>' ).addClass( 'count' )
				                      .html( bpReshare.activity.nav.favorites.count )
				                      .get( 0 ).outerHTML
					                  + '&nbsp;' + xhr.responseText
				)
			}, 500 );

		// Update the comments nav label.
		} else if ( 'delete_activity_comment' === action || 'new_activity_comment' === action ) {
			if ( 'delete_activity_comment' === action ) {
				bpReshare.activity.nav.comments.count -= 1;
			} else {
				bpReshare.activity.nav.comments.count += 1;
			}

			bpReshare.refreshCounts( 'comments', false, bpReshare.activity.nav.comments.count );
		}
	} );

	$( document ).ready( function() {
		if ( bpReshare.isTypeDisabled( bpReshare.activity.id ) ) {
			return;
		}

		bpReshare.activityNav();
	} );

} )( window.bpReshare, jQuery );
