// Create a closure
(function(){
    // Your base, I'm in it!
    var originalAddClassMethod = jQuery.fn.removeClass;

    jQuery.fn.removeClass = function(){
        // Execute the original method.
        var result = originalAddClassMethod.apply( this, arguments );

		if( result.attr('id') == 'activity-reshares' ) {
			jQuery("#activity-reshares").trigger('reshareGotFocus');
		}
		
        // return the original result
        return result;
    }
})();

jQuery(document).ready(function($){
	
	/* if we're on reshare focus, then let's hide the filter */
	if( $('div.activity-type-tabs ul li.selected').attr('id') == 'activity-reshares' || $('#activity-reshares-personal-li').hasClass('current') ) {
		$('#activity-filter-select').hide();
	} else {
		$('#activity-filter-select').show();
	}
	
	$('#activity-reshares').on('reshareGotFocus', function(e){
		if( $(this).hasClass('selected') ) {
			$('#activity-filter-select').hide();
		} else {
			$('#activity-filter-select').show();
		}
	});
	
	$('.bp-agu-reshare').live('click', function(){
		
		if(! $(this).find('.bp-agu-reshare-img').hasClass('reshared') && ! $(this).find('.bp-agu-reshare-img').hasClass('loading') )
			resharePost( $(this) );
		
		if( $('div.activity-type-tabs ul li.selected').attr('id') == 'activity-reshares' || $('#activity-reshares-personal-li').hasClass('current') ) {
			deleteReshare( $(this), 0 )
		}
			
		return false;
	});
	
	$('.delete-reshare').live('click', function(){
		
		wpnonce = $(this).attr('href').split('_wpnonce=');
		wpnonce = wpnonce[1];
		
		deleteReshare( $(this).parent('.activity-meta').find('.bp-agu-reshare'), wpnonce );
			
		return false;
	});
	
	if( $('div.activity #message').length && !$('ul#activity-stream').length ){
		
		if( bp_reshare_vars.use_js_trick_one != 1 )
			return false;
		
		if( $('#activity-reshares').hasClass('selected') || $('#activity-reshares-personal-li').hasClass('selected') ) {
			$('div.activity #message p').html( bp_reshare_vars.no_reshare_text );
		} else {
			$('div.activity #message p').html( $('#message p').html() + bp_reshare_vars.filter_text );
		}

	}
	
	$('#launch-filter').live('click', function(){
		$('#activity-filter-select').css('background', 'rgb(255, 234, 166)');
		
		var elewidth = $('#activity-filter-select').width();
		
		$('#activity-filter-select').animate({ width: Number( elewidth + 100)+"px" },1).delay(1000).animate({ width: elewidth+"px" }, 1500, null, function(){$(this).css('background-color', 'transparent')});
		
		return false;
	});
	
	$('#whats-new').on('focus', function(e){
		
		if( bp_reshare_vars.use_js_trick_two != 1 )
			return false;
		
		if( $('div.activity-type-tabs ul li.selected').attr('id') != 'activity-all' ) {
			$('#activity-all a').trigger('click');
		}
	});
	
	function resharePost(button){
		var aid = $(button).attr('rel');
		var loader = $(button).find('.bp-agu-reshare-img');
		loader.addClass('loading');
		var acount = Number( $(button).find('.rs-count').html() );
		var wpnonce = $(button).attr('href').split('_wpnonce=');
			wpnonce = wpnonce[1];
		
		var data = {
	      action: 'bp_add_reshare',
	      activity: aid,
		  nonce: wpnonce
	    };

	    jQuery.post(ajaxurl, data, function(response) {
			loader.removeClass('loading');
			
			if(response == "1"){
				$('.bp-agu-reshare').each(function(){
					if($(this).attr('rel') == aid){
						$(this).find('.bp-agu-reshare-img').addClass('reshared');
						$(this).find('.rs-count').html( Number( acount+1 ) );
					}	
				});
				
				var reshare_count = Number( $('.item-list-tabs ul li#activity-reshares span').html() );
				
				$('.item-list-tabs ul li#activity-reshares span').html( reshare_count + 1 );
				
				if($('body').hasClass('activity') || $('body').hasClass('group-home') ) {
					$("#activity-filter-by").trigger('change');
					$('html, body').animate({ scrollTop: jQuery("#activity-stream").offset().top }, 500);
				}
					
					
			} else {
				if(response == "-1")
					alert('Cheating ?');
				else alert(response);
			}
			
	    });
	
	}
	
	function updateAllCount( activity_id ) {
		$('.bp-agu-reshare').each(function(){
			
			if( $(this).attr('rel') == activity_id ) {
				acount = Number( $(this).find('.rs-count').html() );
				$(this).find('.rs-count').html( Number( acount-1 ) );
				
			}
		});
	}
	
	function deleteReshare( button, wpnonce ){
		var aid = $(button).attr('id').replace('bp-agu-reshare-','');
		var relid = $(button).attr('rel');
		var loader = $(button).find('.bp-agu-reshare-img');
		var li = $(button).parents('div.activity ul li');
		loader.addClass('loading');
		var acount = Number( $(button).find('.rs-count').html() );
		
		if( wpnonce == 0 ) {
			wpnonce = $(button).attr('href').split('_wpnonce=');
			wpnonce = wpnonce[1];
		}
		
		var data = {
	      action: 'bp_delete_reshare',
	      activity: aid,
		  nonce: wpnonce
	    };

	    jQuery.post(ajaxurl, data, function(response) {
			loader.removeClass('loading');
			
			if(response == "1"){
				
				li.slideUp(300);
				
				var reshare_count = Number( $('.item-list-tabs ul li#activity-reshares span').html() );
				
				$('.item-list-tabs ul li#activity-reshares span').html( reshare_count - 1 );
				
				updateAllCount( relid );
				
			} else {
				if(response == "-1")
					alert('Cheating ?');
				else alert(response);
			}
			
	    });
	
	}
});