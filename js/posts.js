/* global bpReshare */
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
		},

		printButtons: function() {
			var article = document.querySelector( bpReshare.commentsAreaID ).previousElementSibling,
			    className = 'add-reshare';

			if ( bpReshare.activity.isSelf ) {
				className = 'disabled';
			}

			console.log( bpReshare.templates );

			this.container = document.createElement( 'DIV' );
			this.container.setAttribute( 'id', 'buddypress' );
			this.container.setAttribute( 'class', 'buddyreshare activity' );
			this.container.style.margin = '1em 0';
			this.container.innerHTML = bpReshare.templates.reshareButton.replace( '%l', bpReshare.strings.addLink.replace( '%i', bpReshare.activity.id ) )
																		.replace( '%r', className )
																		.replace( '%a', bpReshare.activity.id )
																		.replace( '%u', bpReshare.activity.author )
																		.replace( '%t', bpReshare.strings.addReshare )
																		.replace( '%c', 0 );

			article.appendChild( this.container );
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
