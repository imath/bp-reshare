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

	$reshared_updates = wp_cache_get( $user_id, 'reshared_notifications' );

	if ( empty( $reshared_updates ) ) {
		global $wpdb;
		$table = buddypress()->notifications->table_name;

		$reshared_updates = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT item_id FROM {$table} WHERE user_id = %d AND component_name = %s AND component_action = %s AND is_new = 1",
			$user_id,
			buddyreshare_get_component_id(),
			'new_reshare'
		) );

		wp_cache_add( $user_id,  $reshared_updates, 'reshared_notifications' );
	}

	return $reshared_updates;
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
				'one'  => __( 'New reshared activity', 'bp-reshare' ),
				'more' => __( 'New reshared activities', 'bp-reshare' ),
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

	do_action( 'buddyreshare_notifications_added', $author_id );
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

	do_action( 'buddyreshare_notifications_removed', $author_id );
}
add_action( 'buddyreshare_reshare_deleted', 'buddyreshare_notifications_remove', 10, 1 );

function buddyreshare_notifications_read( $activity = null ) {
	$unread = array();

	if ( ! empty( $activity->id ) && bp_is_single_activity() ) {
		$unread[] = $activity->id;
		$user_id = $activity->user_id;

	} elseif ( bp_is_my_profile() ) {
		$user_id = get_current_user_id();
		$unread  = buddyreshare_notifications_get_unread_item_ids( $user_id );
	}

	/**
	 * BP_Notifications_Notification::update() doesn't allow to pass an array of
	 * item IDs, so I need to create a specific update logic.
	 */
	if ( ! empty( $unread ) ) {
		global $wpdb;
		$table = buddypress()->notifications->table_name;
		$in    = join( ',', wp_parse_id_list( $unread ) );

		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET is_new = %d
			WHERE user_id = %d AND item_id IN ( {$in} ) AND component_name = %s AND component_action = %s",
			false,
			$user_id,
			buddyreshare_get_component_id(),
			'new_reshare'
		) );

		do_action( 'buddyreshare_notifications_marked_read', $user_id );
	}
}
add_action( 'buddyreshare_users_reshared_screen',           'buddyreshare_notifications_read', 10, 1 );
add_action( 'bp_activity_screen_single_activity_permalink', 'buddyreshare_notifications_read', 10, 1 );

function buddyreshare_notifications_clean_cache( $user_id = 0 ) {
	if ( ! $user_id ) {
		return;
	}

	wp_cache_delete( $user_id, 'reshared_notifications' );
}
add_action( 'buddyreshare_notifications_added',       'buddyreshare_notifications_clean_cache', 10, 1 );
add_action( 'buddyreshare_notifications_removed',     'buddyreshare_notifications_clean_cache', 10, 1 );
add_action( 'buddyreshare_notifications_marked_read', 'buddyreshare_notifications_clean_cache', 10, 1 );
