<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Catches an activity to reshare if js is enabled
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses  check_ajax_referer() for security reasons
 * @uses  buddyreshare_prepare_reshare() to build the reshare arguments
 * @uses  bp_activity_add() to save the reshare
 */
function buddyreshare_handle_ajax_reshare() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;
	
	check_ajax_referer( 'buddyreshare_update', 'nonce' );

	$response = array(
		'result'  => 'error',
		'message' => __( 'OOps, error while trying to reshare..', 'bp-reshare' )
	);

	$activity_id = intval( $_POST['activity'] );
	
	if( empty( $activity_id ) ) {
		$response['message'] = __( 'The activity was not found.', 'bp-reshare' );
		exit( json_encode( $response ) );
	}
	
	$args = buddyreshare_prepare_reshare( $activity_id );

	if( isset( $args['error'] ) ) {
		$response['message'] = $args['error'];
		exit( json_encode( $response ) );
	}

	$reshare_id = bp_activity_add( $args );
	
	if( ! empty( $reshare_id ) ) {
		do_action( 'buddyreshare_reshare_added', $reshare_id );

		$response['result'] = 'success';
		$response['message'] = __( 'Activity successfully reshared.', 'bp-reshare' );
	} else {
		do_action( 'buddyreshare_reshare_added_error', $reshare_id );
	}

	exit( json_encode( $response ) );
}

add_action('wp_ajax_buddyreshare_add', 'buddyreshare_handle_ajax_reshare' );

/**
 * Catches an activity to delete if js is enabled
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses  check_ajax_referer() for security reasons
 * @uses  bp_activity_get_specific() to fetch the activity to delete
 * @uses  buddyreshare_reset_metas() to reset some metas for the parent activity
 * @uses  bp_activity_delete() to delete the reshare
 */
function buddyreshare_ajax_delete_reshare() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_ajax_referer( 'buddyreshare_delete', 'nonce' );

	$response = array(
		'result'  => 'error',
		'message' => __( 'OOps, error while trying to delete your reshare..', 'bp-reshare' )
	);
	
	$reshare_id = intval( $_POST['activity'] );

	if( empty( $reshare_id ) ) {
		$response['message'] = __( 'The reshare was not found.', 'bp-reshare' );
		exit( json_encode( $response ) );
	}
	
	$reshare_to_delete = bp_activity_get_specific( array( 'activity_ids' => $reshare_id ) );
	if( empty( $reshare_to_delete ) ) {
		$response['message'] = __( 'The reshare was not found.', 'bp-reshare' );
		exit( json_encode( $response ) );
	}

	$reshare = $reshare_to_delete['activities'][0];
	
	$reset = buddyreshare_reset_metas( $reshare->secondary_item_id, $reshare->user_id );

	if( empty( $reset ) ) {
		$response['message'] = __( 'Unable to reset the properties of the reshared activity', 'bp-reshare' );
		exit( json_encode( $response ) );
	}
	
	$deleted_reshare = bp_activity_delete( array('type' => 'reshare_update',  'id' => $reshare_id ) );
	
	if( ! empty( $deleted_reshare ) ) {
		do_action( 'buddyreshare_reshare_deleted', $reshare_id );

		$response['result'] = 'success';
		$response['message'] = __( 'Reshare successfully deleted.', 'bp-reshare' );
	} else {
		do_action( 'buddyreshare_reshare_deleted_error', $reshare_id );
	}
	
	exit( json_encode( $response ) );
}

add_action( 'wp_ajax_buddyreshare_delete', 'buddyreshare_ajax_delete_reshare');