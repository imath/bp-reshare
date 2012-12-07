<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;


/**
* let's add reshare if user's browser has javascript turned off
*/
function bp_reshare_post_reshare() {
	if( !empty( $_GET['to_reshare'] ) && is_numeric( $_GET['to_reshare'] ) ) {
		
		check_admin_referer( '_reshare_update' );
		
		$redirect = remove_query_arg( array( 'to_reshare','_wpnonce' ), wp_get_referer() );
		
		$reshared_args = bp_reshare_prepare_reshare( $_GET['to_reshare'] );
		
		$reshared_activity_id = bp_activity_add( $reshared_args );
		
		if ( !empty( $reshared_activity_id ) ) {
			
			do_action( 'bp_reshare_handle_nojs_posted', $reshared_activity_id );
			
			bp_core_add_message( __( 'Activity reshared !', 'bp-reshare' ) );
			bp_core_redirect( $redirect );
		}
			
		else{
			
			do_action( 'bp_reshare_handle_nojs_missed', $reshared_activity_id );
			
			bp_core_add_message( __( 'OOps, error while trying to reshare..', 'bp-reshare' ), 'error' );
			bp_core_redirect( $redirect );
		}
		
	}
}	

add_action('bp_actions', 'bp_reshare_post_reshare', 11 );
	
/**
* let's delete reshare updates if main activity is deleted
*/
function bp_reshare_handle_deleting_reshare( $args ) {
	if( $args['type'] == 'reshare_update' )
		return false;
	
	bp_activity_delete( array('type' => 'reshare_update',  'secondary_item_id' => $args['id'] ) );
}

add_action('bp_activity_delete', 'bp_reshare_handle_deleting_reshare', 10, 1 );


/**
* let's delete reshare update if js is disabled
*/	
function bp_reshare_delete_reshare() {
	if( !empty( $_GET['delete_reshare'] ) && is_numeric( $_GET['delete_reshare'] ) ) {
		
		check_admin_referer( '_reshare_delete' );
		
		$redirect = remove_query_arg( array( 'delete_reshare','_wpnonce' ), wp_get_referer() );
		
		$reshare_id = intval( $_GET['delete_reshare'] );
		
		$reshare_to_delete = bp_activity_get_specific( 'activity_ids='. $reshare_id );
		$reshare = $reshare_to_delete['activities'][0];
		
		bp_reshare_delete( $reshare->secondary_item_id, $reshare->user_id );
		
		$deleted_reshare = bp_activity_delete( array('type' => 'reshare_update',  'id' => $reshare_id ) );
		
		if ( !empty( $deleted_reshare ) ) {
			
			do_action( 'bp_reshare_handle_nojs_deleted', $reshare_id );
			
			bp_core_add_message( __( 'Reshare deleted !', 'bp-reshare' ) );
			bp_core_redirect( $redirect );
		}
			
		else{
			
			do_action( 'bp_reshare_handle_nojs_missed', $reshare_id );
			
			bp_core_add_message( __( 'OOps, error while trying to reshare..', 'bp-reshare' ), 'error' );
			bp_core_redirect( $redirect );
		}
		
	}
}

add_action( 'bp_actions', 'bp_reshare_delete_reshare', 12 );