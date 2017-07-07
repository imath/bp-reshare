// Make sure the bpReshare object exists.
window.bpReshare = window.bpReshare || {};

( function( bpReshare, document ) {

	// Bail if not set
	if ( 'undefined' === typeof bpReshare.templates ) {
		return;
	}

	/**
	 * Posts Class.
	 * @type {Object}
	 */
	bpReshare.Posts = {
		start: function() {
			this.printButtons();

			this.scrollToBuddyPress();

			document.querySelector( '#buddypress .bp-reshare' ).addEventListener(
				'click',
				this.reshareAction
			);
		},

		displayFeedback: function( container ) {
			// Remove all previous notices.
			if ( 'template-notices' === container.childNodes[0].getAttribute( 'id' ) ) {
				container.childNodes[0].remove();
			}

			if ( ! bpReshare.templates.notice ) {
				return;
			}

			container.innerHTML = bpReshare.templates.notice + container.innerHTML;

			window.setTimeout( function() {
				document.getElementById( 'template-notices' ).remove();
			}, 4000 );
		},

		printButtons: function() {
			var article = document.querySelector( bpReshare.commentsAreaID ).previousElementSibling,
			    className = 'add-reshare', link = bpReshare.strings.addLink.replace( '%i', bpReshare.activity.id ),
			    screenReaderText = bpReshare.strings.addReshare;

			if ( bpReshare.activity.isSelf ) {
				className = 'disabled';
			} else if ( -1 !== bpReshare.activity.reshares.indexOf( bpReshare.params.u.toString() ) ) {
				className = 'remove-reshare';
				link = bpReshare.strings.removeLink.replace( '%i', bpReshare.activity.id );
				screenReaderText = bpReshare.strings.removeReshare;
			}

			this.container = document.createElement( 'DIV' );
			this.container.setAttribute( 'id', 'buddypress' );
			this.container.setAttribute( 'class', 'buddyreshare activity' );
			this.container.setAttribute( 'data-activity-id', 'activity-' + bpReshare.activity.id );
			this.container.innerHTML = bpReshare.templates.reshareButton.replace( '%l', link )
			                                                            .replace( '%r', className )
			                                                            .replace( '%a', bpReshare.activity.id )
			                                                            .replace( '%u', bpReshare.activity.author )
			                                                            .replace( '%t', screenReaderText )
			                                                            .replace( '%c', bpReshare.activity.reshares.length );

			this.container.innerHTML += bpReshare.templates.favoritesButton;
			article.appendChild( this.container );

			if ( bpReshare.templates.notice ) {
				bpReshare.Ajax.feedback( this.container, bpReshare.templates.notice, 'full' );
			}
		},

		scrollToBuddyPress: function() {
			if ( '#activity-' + bpReshare.activity.id !== window.location.hash ) {
				return;
			}

			location.href = '#buddypress';
		},

		reshareAction: function( event ) {
			event.preventDefault();

			var button = event.currentTarget, classes = button.getAttribute( 'class' ).split( ' ' ),
			    container = document.getElementById( 'buddypress' );

			if ( -1 !== classes.indexOf( 'add-reshare' ) ) {
				bpReshare.Ajax.post( bpReshare.activity.id, { 'user_id': bpReshare.params.u, 'author_slug': bpReshare.activity.author }, function( status, response ) {
					if ( 200 === status && response.reshared ) {

						// Update the link for a remove reshare one
						button.setAttribute( 'class', button.getAttribute( 'class' ).replace( 'add-reshare', 'remove-reshare' ) );
						button.setAttribute( 'href', bpReshare.strings.removeLink.replace( '%i', bpReshare.activity.id ) );

						bpReshare.activity.reshares.push( bpReshare.params.u );

						for ( var span in button.childNodes ) {
							if ( 'SPAN' === button.childNodes[ span ].nodeName && 'count' === button.childNodes[ span ].getAttribute( 'class' ) ) {
								button.childNodes[ span ].innerHTML = parseInt( button.childNodes[ span ].innerHTML, 10 ) + 1;
							}
						}
					} else {
						var error = bpReshare.strings.genericError;
						if ( response.message ) {
							error = response.message;
						}

						bpReshare.Ajax.feedback( container, error, 'error' );
					}
				} );

			// If the user is in the users who reshared: can remove reshare.
			} else if ( -1 !== classes.indexOf( 'remove-reshare' ) ) {
				bpReshare.Ajax.remove( bpReshare.activity.id, { 'user_id': bpReshare.params.u, 'author_slug': bpReshare.activity.author }, function( status, response ) {
					if ( 200 === status ) {

						// Update the link for a remove reshare one
						button.setAttribute( 'class', button.getAttribute( 'class' ).replace( 'remove-reshare','add-reshare' ) );
						button.setAttribute( 'href', bpReshare.strings.addLink.replace( '%i', bpReshare.activity.id ) );

						bpReshare.activity.reshares.splice( bpReshare.activity.reshares.indexOf( bpReshare.params.u.toString() ), 1 );

						for ( var span in button.childNodes ) {
							if ( 'SPAN' === button.childNodes[ span ].nodeName && 'count' === button.childNodes[ span ].getAttribute( 'class' ) ) {
								button.childNodes[ span ].innerHTML = parseInt( button.childNodes[ span ].innerHTML, 10 ) - 1;
							}
						}
					} else {
						var error = bpReshare.strings.genericError;
						if ( response.message ) {
							error = response.message;
						}

						bpReshare.Ajax.feedback( container, error, 'error' );
					}
				} );
			}
		}
	};

	window.addEventListener( 'load', function() {
		var loaded = false;

		if ( loaded ) {
			return;
		}

		bpReshare.Posts.start();
		loaded = true;
	} );

} )( window.bpReshare, window.document );
