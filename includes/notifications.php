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

function buddyreshare_notifications_get_unread_item_ids( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	$table = buddypress()->notifications->table_name;

	return $wpdb->get_col( $wpdb->prepare(
		"SELECT DISTINCT item_id FROM {$table} WHERE user_id = %d AND component_name = %s AND component_action = %s AND is_new = 1",
		$user_id,
		buddyreshare_get_component_id(),
		'new_reshare'
	) );
}

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
			'items'   => buddyreshare_notifications_get_unread_item_ids(),
			'template' => array(
				'one'  => __( '%n reshared activity', 'bp-reshare' ),
				'more' => __( '%n reshared activities', 'bp-reshare' ),
			),
			'link' => array(
				'one'  => bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/p/%n/',
				'more' => trailingslashit( bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . buddyreshare_get_component_slug() ),
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

function buddyreshare_notifications_read() {
	/**
	 * @todo
	 */
	return;
}
add_action( 'buddyreshare_users_reshared_screen', 'buddyreshare_notifications_read' );
