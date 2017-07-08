<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Catches an activity to reshare if js is disabled
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses  bp_is_activity_component() are we in activity component
 * @uses  bp_is_current_action() to check current action
 * @uses  buddyreshare_get_component_slug() to get component slug
 * @uses  bp_action_variable() to check the variables
 * @uses  check_admin_referer() for security reasons
 * @uses  bp_core_get_user_domain() to build user's url
 * @uses  bp_loggedin_user_id() to get current user's id
 * @uses  bp_get_activity_slug() to get activity slug
 * @uses  buddyreshare_prepare_reshare() to build the reshare arguments
 * @uses  bp_core_add_message() to print a warning message
 * @uses  bp_core_redirect() to safely redirect user
 * @uses  bp_activity_add() to save the reshare
 */
function buddyreshare_add_reshare() {
	// Not adding a reshare
	if ( ! bp_is_activity_component() || ! bp_is_current_action( buddyreshare_get_component_slug() ) )
		return false;

	// No reshare to add
	if ( ! bp_action_variable( 0 ) || bp_action_variable( 0 ) != 'add' || ! bp_action_variable( 1 ) || ! is_numeric( bp_action_variable( 1 ) ) )
		return false;

	$reshare_id = bp_action_variable( 1 );

	check_admin_referer( 'buddyreshare_update' );

	// redirecting to user's profile
	$redirect = bp_core_get_user_domain( bp_loggedin_user_id() ) . bp_get_activity_slug() . '/';

	$reshared_args = buddyreshare_prepare_reshare( $reshare_id );

	if( isset( $reshared_args['error'] ) ) {
		bp_core_add_message( $reshared_args['error'], 'error' );
		bp_core_redirect( $redirect );
	}

	$reshared = bp_activity_add( $reshared_args );

	if ( !empty( $reshared ) ) {

		do_action( 'buddyreshare_reshare_added', $reshare_id );

		bp_core_add_message( __( 'Activity reshared !', 'bp-reshare' ) );
		bp_core_redirect( $redirect );
	} else{

		do_action( 'buddyreshare_reshare_added_error', $reshare_id );

		bp_core_add_message( __( 'OOps, error while trying to reshare..', 'bp-reshare' ), 'error' );
		bp_core_redirect( $redirect );
	}

}
	
/**
 * Deletes the reshared of a deleted activity
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses  bp_activity_delete() to delete the 'reshare_activity'
 */
function buddyreshare_handle_activity_delete( $args ) {
	if( $args['type'] == 'reshare_update' )
		return;
	
	bp_activity_delete( array('type' => 'reshare_update',  'secondary_item_id' => $args['id'] ) );
}


/**
 * Catches a reshare to delete if js is disabled
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses  bp_is_activity_component() are we in activity component
 * @uses  bp_is_current_action() to check current action
 * @uses  buddyreshare_get_component_slug() to get component slug
 * @uses  bp_action_variable() to check the variables
 * @uses  check_admin_referer() for security reasons
 * @uses  bp_activity_get_specific() to fetch the activity to delete
 * @uses  bp_do_404() to eventually send the user on a 404
 * @uses  bp_core_get_user_domain() to build user's url
 * @uses  bp_get_activity_slug() to get activity slug
 * @uses  buddyreshare_reset_metas() to reset some metas for the parent activity
 * @uses  bp_core_add_message() to print a warning message
 * @uses  bp_core_redirect() to safely redirect user
 * @uses  bp_activity_delete() to delete the reshare
 */
function buddyreshare_remove_reshare() {

	// Not deleting a reshare
	if ( ! bp_is_activity_component() || ! bp_is_current_action( buddyreshare_get_component_slug() ) )
		return false;

	// No reshare to delete
	if ( ! bp_action_variable( 0 ) || bp_action_variable( 0 ) != 'delete' || ! bp_action_variable( 1 ) || ! is_numeric( bp_action_variable( 1 ) ) )
		return false;

	$reshare_id = bp_action_variable( 1 );

	check_admin_referer( 'buddyreshare_delete' );

	// Get the activity details
	$activity = bp_activity_get_specific( array( 'activity_ids' => bp_action_variable( 1 ), 'show_hidden' => true ) );

	// 404 if activity does not exist
	if ( empty( $activity['activities'][0] ) ) {
		bp_do_404();
		return;
	} else {
		$reshare = $activity['activities'][0];
	}

	// redirecting to user's profile
	$redirect = bp_core_get_user_domain( $reshare->user_id, $reshare->user_nicename, $reshare->user_login ) . bp_get_activity_slug() . '/';

	$reset = buddyreshare_reset_metas( $reshare->secondary_item_id, $reshare->user_id );

	if( empty( $reset ) ) {
		bp_core_add_message( __( 'Unable to reset the properties of the reshared activity', 'bp-reshare' ), 'error' );
		bp_core_redirect( $redirect );
	}
		
	$deleted_reshare = bp_activity_delete( array('type' => 'reshare_update',  'id' => $reshare_id ) );
	
	if ( !empty( $deleted_reshare ) ) {
		
		do_action( 'buddyreshare_reshare_deleted', $reshare_id );
		
		bp_core_add_message( __( 'Reshare deleted !', 'bp-reshare' ) );
		bp_core_redirect( $redirect );
	}
		
	else{
		
		do_action( 'buddyreshare_reshare_deleted_error', $reshare_id );
		
		bp_core_add_message( __( 'OOps, error while trying to reshare..', 'bp-reshare' ), 'error' );
		bp_core_redirect( $redirect );
	}

}

/**
 * Increment user's reshare count
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses  bp_loggedin_user_id() to get current user's id
 * @uses  bp_get_user_meta() to get the count of the user
 * @uses  bp_update_user_meta to update the count of the user
 */
function buddreshare_increment_user_count( $reshare_id = 0, $user_id = 0 ) {
	if( empty( $user_id ) )
		$user_id = bp_loggedin_user_id();

	// Update the user's personal reshares count
	$count = bp_get_user_meta( $user_id, 'buddyreshare_count', true );
	$count = !empty( $count ) ? (int) $count + 1 : 1;

	// Update user meta
	bp_update_user_meta( $user_id, 'buddyreshare_count', $count );
}

/**
 * Decrement user's reshare count
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses  bp_loggedin_user_id() to get current user's id
 * @uses  bp_get_user_meta() to get the count of the user
 * @uses  bp_update_user_meta to update the count of the user
 */
function buddreshare_decrement_user_count( $reshare_id = 0, $user_id = 0 ) {
	if( empty( $user_id ) )
		$user_id = bp_loggedin_user_id();

	// Update the user's personal reshares count
	$count = bp_get_user_meta( $user_id, 'buddyreshare_count', true );
	$count = !empty( $count ) ? (int) $count - 1 : 0;

	// Update user meta
	bp_update_user_meta( $user_id, 'buddyreshare_count', $count );
}

/**
 * List the users that have reshared an activity
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses   bp_get_option() to get the setting for the number of users to display
 * @uses   bp_activity_get_meta() to get some meta about the activity
 * @uses   bp_get_activity_id() to get activity id
 * @uses   bp_core_get_userlink() to build user's profile link
 * @uses   bp_core_fetch_avatar() to get user's avatar
 * @return string html output
 */
function buddyreshare_list_user_avatars() {
	$max = (int)bp_get_option( 'bp-reshare-user-amount', 5 );

	if( empty( $max ) )
		return;

	$user_list = bp_activity_get_meta( bp_get_activity_id(), 'reshared_by' );
	
	if( is_array( $user_list ) && count( $user_list ) >= 1 ){
		rsort( $user_list );
		
		$output = '<div class="reshared-list activity-content">' . __( 'Reshared by :', 'bp-reshare') . '<ul>';
		$step = 0;
		
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

/**
 * Hook to only load the user's list if on a single activity
 *
 * @package BP Reshare
 * @since    1.0
 */
function buddyreshare_reshared_by_list() {
	add_action('bp_before_activity_entry_comments', 'buddyreshare_list_user_avatars' );
}

