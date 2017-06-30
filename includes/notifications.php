<?php
/**
 * Notification functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function buddyreshare_notifications_enqueue_script() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	wp_enqueue_script(
		'bp-reshare-notifications',
		buddyreshare_get_js_url() . 'notifications.js',
		array(),
		buddyreshare_get_plugin_version(),
		true
	);

	wp_localize_script( 'bp-reshare-notifications', 'bpReshare', array(
		'userNotifications' => array(
			'amount'   => BP_Notifications_Notification::get_total_count( array(
				'user_id'          => get_current_user_id(),
				'component_name'   => 'bp_reshare',
				'componant_action' => 'new_reshare',
				'is_new'           => 1,
			) ),
			'template' => array(
				'one'  => __( '%n new activity reshare', 'bp-reshare' ),
				'more' => __( '%n new activity reshares', 'bp-reshare' ),
			),
		),
	) );
}
add_action( 'admin_bar_init', 'buddyreshare_notifications_enqueue_script' );

function buddyreshare_notifications_add( $args = array() ) {
	if ( empty( $args['author_slug'] ) || empty( $args['activity_id'] ) || empty( $args['user_id'] ) ) {
		return;
	}

	$author_id = bp_core_get_userid_from_nicename( $args['author_slug'] );

	if ( ! $author_id ) {
		return;
	}

	bp_notifications_add_notification( array(
		'user_id'           => $author_id,
		'item_id'           => $args['activity_id'],
		'secondary_item_id' => $args['user_id'],
		'component_name'    => buddyreshare_get_component_id(),
		'component_action'  => 'new_reshare',
		'date_notified'     => bp_core_current_time(),
		'is_new'            => 1,
	) );
}
add_action( 'buddyreshare_reshare_added', 'buddyreshare_notifications_add', 10, 1 );

function buddyreshare_notifications_remove( $args = array() ) {
	if ( empty( $args['author_slug'] ) || empty( $args['activity_id'] ) || empty( $args['user_id'] ) ) {
		return;
	}

	$author_id = bp_core_get_userid_from_nicename( $args['author_slug'] );

	if ( ! $author_id ) {
		return;
	}

	bp_notifications_delete_notifications_by_item_id(
		$author_id,
		$args['activity_id'],
		buddyreshare_get_component_id(),
		'new_reshare',
		$args['user_id']
	);
}
add_action( 'buddyreshare_reshare_deleted', 'buddyreshare_notifications_remove', 10, 1 );
