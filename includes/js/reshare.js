( function( $ ) {

	// Your base, I'm in it!
    var originalAddClassMethod = $.fn.removeClass;

    $.fn.removeClass = function(){
        // Execute the original method.
        var result = originalAddClassMethod.apply( this, arguments );

		if( result.prop( 'id' ) == 'activity-reshares' ) {
			$( '#activity-reshares' ).trigger( 'reshareGotFocus' );
		}
		
        // return the original result
        return result;
    }

    $(document).ready( function() {
    	/* if we're on reshare focus, then let's hide the filter */
		if( $( 'div.activity-type-tabs ul li.selected').prop( 'id' ) == 'activity-reshares' || $( '#' + bp_reshare_vars.personal_li +'-personal-li' ).hasClass( 'current' ) ) {
			$( '#activity-filter-select' ).hide();
		} else {
			$( '#activity-filter-select' ).show();
		}
    } );
	
	$( '#activity-reshares' ).on( 'reshareGotFocus', function( event ){
		if( $(this).hasClass( 'selected' ) ) {
			$( '#activity-filter-select' ).hide();
		} else {
			$( '#activity-filter-select' ).show();
		}
	});
	
	$( 'body.buddypress .activity' ).on( 'click' , '.reshare-button', function( event ){

		event.preventDefault();

		if(! $( this ).find( '.bp-reshare-img' ).hasClass( 'reshared' ) && ! $( this ).find( '.bp-reshare-img' ).hasClass( 'loading' ) )
			postReshare( $( this ) );

		if( $( this ).find( '.bp-reshare-img' ).hasClass( 'unshare' ) && ! $( this ).find( '.bp-reshare-img' ).hasClass( 'loading' ) )
			deleteReshare( $(this), 0 );
			
		return;
	});
	
	$( 'body.buddypress .activity').on( 'click', '.delete-reshare', function( event ){

		event.preventDefault();
		
		wpnonce = $( this ).prop( 'href' ).split( '_wpnonce=' );
		wpnonce = wpnonce[1];
		
		deleteReshare( $( this ).parent( '.activity-meta' ).find( '.reshare-button' ), wpnonce );
			
		return;
	});
	
	function postReshare( button ){
		var aid = $( button ).prop( 'rel' );
		var loader = $( button ).find( '.bp-reshare-img' );
		loader.addClass( 'loading' );

		var acount = Number( $( button ).find( '.rs-count' ).html() );
		var wpnonce = $( button ).prop( 'href' ).split( '_wpnonce=' );
			wpnonce = wpnonce[1];
		
		var data = {
	      action: 'buddyreshare_add',
	      activity: aid,
		  nonce: wpnonce
	    };

	    $.post( ajaxurl, data, function( response ) {
			loader.removeClass( 'loading' );
			
			if( response['result'] == 'success' ){
				$( '.reshare-button' ).each(function(){
					if( $( this ).prop( 'rel' ) == aid ){
						$( this ).find( '.bp-reshare-img' ).addClass( 'reshared' );
						$( this ).find( '.bp-reshare-img' ).html( bp_reshare_vars.reshared_text );
						$( this ).find( '.rs-count' ).html( Number( acount+1 ) );
						$( this ).prop( 'title', bp_reshare_vars.reshared_text );
					}	
				});

				if ( !$( '.item-list-tabs #reshare-personal-li' ).length ) {
					if ( !$( '.item-list-tabs #activity-reshares' ).length )
						$( '.item-list-tabs ul #activity-all' ).after( '<li id="activity-reshares"><a href="#">' + bp_reshare_vars.my_reshares + ' <span>0</span></a></li>' );
				}
				
				var reshare_count = Number( $( '.item-list-tabs ul li#activity-reshares span' ).html() );
				
				$('.item-list-tabs ul li#activity-reshares span').html( reshare_count + 1 );
				
				if( $( 'body' ).hasClass( 'activity' ) || $( 'body' ).hasClass( 'group-home' ) ) {
					$( '#activity-filter-by' ).trigger( 'change' );
					$( 'html, body' ).animate( { scrollTop: $( '#activity-stream' ).offset().top }, 500 );
				}
					
			} else {
				if( response == "-1" )
					alert( bp_reshare_vars.cheating_text );
				else 
					alert( response['message'] );
			}
			
	    }, 'json' );
	
	}
	
	function updateAllCount( activity_id ) {
		$( '.reshare-button' ).each(function(){
			if( $( this ).prop('rel') == activity_id ) {
				acount = Number( $( this ).find( '.rs-count' ).html() );
				$( this ).find( '.rs-count' ).html( Number( acount-1 ) );
			}
		});
	}
	
	function deleteReshare( button, wpnonce ){
		var aid = $( button ).prop( 'id' ).replace( 'bp-reshare-', '' );
		var relid = $( button ).prop( 'rel' );
		var loader = $( button ).find( '.bp-reshare-img' );
		var li = $( button ).parents( 'div.activity ul li' );

		if( loader )
			loader.addClass( 'loading' );

		var acount = Number( $( button ).find( '.rs-count' ).html() );
		
		if( wpnonce == 0 ) {
			wpnonce = $( button ).prop( 'href' ).split( '_wpnonce=' );
			wpnonce = wpnonce[1];
		}
		
		var data = {
	      action: 'buddyreshare_delete',
	      activity: aid,
		  nonce: wpnonce
	    };

	    $.post( ajaxurl, data, function( response ) {
			if( loader )
				loader.addClass( 'loading' );
			
			if( response['result'] == 'success' ){
				
				li.slideUp( 300 );
				
				var reshare_count = Number( $( '.item-list-tabs ul li#activity-reshares span' ).html() );
				
				$( '.item-list-tabs ul li#activity-reshares span' ).html( reshare_count - 1 );
				
				updateAllCount( relid );
				
			} else {
				if(response['result'] == "-1")
					alert( bp_reshare_vars.cheating_text );
				else 
					alert( response['message'] );
			}
			
	    }, 'json' );
	
	}
	

} )( jQuery );