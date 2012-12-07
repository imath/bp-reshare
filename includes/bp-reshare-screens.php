<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;


function bp_reshare_add_reshare_button(){
	global $bp;
	
	if( !is_user_logged_in() )
		return false;
		
	$activity_types_resharable = bp_reshare_activity_types();
	
	if( !in_array( bp_get_activity_type(), $activity_types_resharable ) )
		return false;
		
	if( bp_reshare_activity_is_hidden() )
		return false;
		
	if( bp_reshare_is_user_profile_reshares() && $bp->displayed_user->id != $bp->loggedin_user->id )
		return false;
		
	$activity_first_id = bp_get_activity_id();
	
	if('reshare_update' == bp_get_activity_type() )
		$activity_first_id = bp_get_activity_secondary_item_id();
		
	$rs_count = bp_activity_get_meta( $activity_first_id, 'reshared_count' );
	$rs_count = !empty( $rs_count ) ? $rs_count : 0;
		
	if( $bp->loggedin_user->id == bp_get_activity_user_id() || bp_reshare_user_did_reshared( $activity_first_id ) )
		$reshared_class = 'reshared';
		
	
	$action_url = wp_nonce_url( bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/?to_reshare=' . $activity_first_id, '_reshare_update' );
	
	if ( $_POST['scope'] == 'reshares' || bp_reshare_is_user_profile_reshares() || ( bp_is_activity_component() && !bp_displayed_user_id() && $_COOKIE['bp-activity-scope'] == 'reshares' ) ) {
		$extra_class = 'unshare';
		$action_url = wp_nonce_url( bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/?delete_reshare=' . bp_get_activity_id(), '_reshare_delete' );
	}
	
	?>
	
	<a href="<?php echo $action_url ?>" class="button bp-primary-action bp-agu-reshare" id="bp-agu-reshare-<?php bp_activity_id(); ?>" rel="<?php echo $activity_first_id?>"><span class="bp-agu-reshare-img <?php echo $reshared_class .' '.$extra_class ;?>"></span><span class="rs-count"><?php echo $rs_count;?></span></a>
	<?php
	
}	

add_action('bp_activity_entry_meta', 'bp_reshare_add_reshare_button', 10 );

function bp_reshare_screen_my_reshares() {
	
	if( bp_reshare_is_user_profile_reshares() ) {
		
		do_action( 'bp_reshare_screen_my_reshares' );
		bp_core_load_template( apply_filters( 'bp_reshare_template_my_reshares', 'members/single/home' ) );
		
	}

}
	
add_action('bp_actions', 'bp_reshare_screen_my_reshares');


function bp_reshare_add_filter_options(){
	?>
	<option value="activity_mostreshared"><?php _e( 'Most Reshared', 'bp-reshare' ); ?></option>
	<?php
}

add_action('bp_activity_filter_options', 'bp_reshare_add_filter_options', 14);
add_action('bp_member_activity_filter_options', 'bp_reshare_add_filter_options', 14 );
add_action('bp_group_activity_filter_options', 'bp_reshare_add_filter_options', 14 );


function bp_reshare_type_tabs() {
	?>
	<li id="activity-reshares"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/reshares/'; ?>" title="<?php _e( 'Activity that I have reshared.', 'bp-reshare' ); ?>"><?php _e( 'Reshares', 'bp-reshare' ); ?> <strong><?php printf( __( '<span>%s</span>', 'bp-reshare' ), bp_reshare_get_total_reshare_count_for_user( bp_loggedin_user_id() ) ); ?></strong></a></li>
	<?php
}

add_action('bp_activity_type_tabs', 'bp_reshare_type_tabs');

function bp_reshare_prepare_list_user_avatars( $activity, $has_access ) {
	
	add_action('bp_before_activity_entry_comments', 'bp_reshare_list_user_avatars');
}

add_action('bp_activity_screen_single_activity_permalink', 'bp_reshare_prepare_list_user_avatars', 10, 2 );

function bp_reshare_list_user_avatars() {
	$user_list = bp_activity_get_meta( bp_get_activity_id(), 'reshared_by' );
	
	if( is_array( $user_list ) && count( $user_list ) >= 1 ){
		rsort( $user_list );
		
		$amount_user = (int)bp_get_option( 'bp-reshare-user-amount' ) ? bp_get_option( 'bp-reshare-user-amount' ) : 5 ;
		$output = '<div class="reshared-list activity-content">'.__('Reshared by :', 'bp-reshare').'<ul>';
		$step = 0;
		$max = apply_filters('bp_reshare_max_reshared_users', $amount_user );
		
		foreach( $user_list as $user ) { 
			
			if( $step == $max )
				break;
			
			$output .= '<li><a href="'. bp_core_get_userlink( $user, false, true ) .'">'. bp_core_fetch_avatar( array( 'item_id' => $user, 'object' => 'user', 'type' => 'thumb', 'class' => 'avatar reshared', 'width' => '40', 'height' => '40' ) ) .'</a></li>';
			
			$step += 1;
		}
		
		$output .= '</ul><br style="clear:both"></div>';
		
		echo $output;
	}
}