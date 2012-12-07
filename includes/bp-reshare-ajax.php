<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;
	
add_action('wp_ajax_bp_add_reshare', 'bp_reshare_handle_ajax_reshare');

function bp_reshare_handle_ajax_reshare() {
	
	check_ajax_referer( '_reshare_update', 'nonce' );
	
	$activity_id = $_POST['activity'];
	
	if(! $activity_id ) {
		_e('Unknown activity ?!?', 'bp-reshare');
		die();
	}
	
	$args = bp_reshare_prepare_reshare( $activity_id );
	
	if( bp_activity_add( $args ) ) {
		echo '1';
	} else {
		_e('OOps, error while trying to reshare..', 'bp-reshare');
	}
	die();
}

add_action( 'wp_ajax_bp_delete_reshare', 'bp_reshare_ajax_delete_reshare');

function bp_reshare_ajax_delete_reshare() {
	check_ajax_referer( '_reshare_delete', 'nonce' );
	
	$reshare_id = intval( $_POST['activity'] );
	
	$reshare_to_delete = bp_activity_get_specific( 'activity_ids='. $reshare_id );
	$reshare = $reshare_to_delete['activities'][0];
	
	bp_reshare_delete( $reshare->secondary_item_id, $reshare->user_id );
	
	$deleted_reshare = bp_activity_delete( array('type' => 'reshare_update',  'id' => $reshare_id ) );
	
	if( $deleted_reshare ) {
		echo '1';
	} else {
		_e('OOps, error while trying to delete your reshare..', 'bp-reshare');
	}
	die();
}