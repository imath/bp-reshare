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

/**
 * Gets the activity IDs that were reshared to notify the user about it.
 *
 * @since 2.0.0
 *
 * @param  integer $user_id The ID of the user to notify.
 * @return array            The list of activity IDs.
 */
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

		wp_cache_add( $user_id, $reshared_updates, 'reshared_notifications' );
	}

	return $reshared_updates;
}

/**
 * Enqueue the Notifications JavaScript and Style assets.
 *
 * @since 2.0.0
 */
function buddyreshare_notifications_enqueue_assets() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$min = buddyreshare_min_suffix();

	wp_enqueue_script(
		'bp-reshare-notifications',
		buddyreshare_get_js_url() . "notifications{$min}.js",
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
add_action( 'admin_bar_init', 'buddyreshare_notifications_enqueue_assets' );

/**
 * Adds a new entry into the activty author notifications on reshared.
 *
 * @since 2.0.0
 *
 * @param array $args {
 *  An array of arguments.
 *  @type int    $activity_id    The activity ID the reshare refers to.
 *  @type int    $user_id        The ID of the user who's reshared it.
 *  @type string $author_slug    The nicename of the author of the activty.
 * }
 */
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

	/**
	 * Hook here to add custom actions once the notification has been added.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $author_id The activity author ID.
	 */
	do_action( 'buddyreshare_notifications_added', $author_id );
}
add_action( 'buddyreshare_reshare_added', 'buddyreshare_notifications_add', 10, 1 );

/**
 * Removes an entry from the activty author notifications when a reshare is deleted.
 *
 * @since 2.0.0
 *
 * @param array $args {
 *  An array of arguments.
 *  @type int    $activity_id    The activity ID the reshare refers to.
 *  @type int    $user_id        The ID of the user who's reshared it.
 *  @type string $author_slug    The nicename of the author of the activty.
 * }
 */
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

	/**
	 * Hook here to add custom actions once the notification has been removed.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $author_id The activity author ID.
	 */
	do_action( 'buddyreshare_notifications_removed', $author_id );
}
add_action( 'buddyreshare_reshare_deleted', 'buddyreshare_notifications_remove', 10, 1 );

/**
 * Removes entries from the activty author notifications when activity are batch deleted.
 *
 * @since 2.0.0
 *
 * @param array $args {
 *  An array of arguments.
 *  @type array    $activity_ids  The list of deleted activity IDs.
 *  @type array    $user_ids      The list of activity author IDs.
 * }
 */
function buddyreshare_notifications_remove_these( $args = array() ) {
	if ( empty( $args['activity_ids'] ) || empty( $args['user_ids'] ) ) {
		return;
	}

	// Remove notifications.
	foreach ( $args['activity_ids'] as $activity_id ) {
		bp_notifications_delete_all_notifications_by_type(
			$activity_id,
			buddyreshare_get_component_id(),
			'new_reshare'
		);
	}

	// Clean user's notifications cache.
	foreach ( $args['user_ids'] as $user_id ) {
		wp_cache_delete( $user_id, 'reshared_notifications' );
	}

	/**
	 * Hook here to add custom actions when notifications were batch deleted.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args {
	 *  An array of arguments.
	 *  @type array    $activity_ids  The list of deleted activity IDs.
	 *  @type array    $user_ids      The list of activity author IDs.
	 * }
	 */
	do_action( 'buddyreshare_notifications_removed_these', $args );
}
add_action( 'buddyreshare_reshares_deleted', 'buddyreshare_notifications_remove_these', 10, 1 );

/**
 * Mark notification(s) as read.
 *
 * @since 2.0.0
 *
 * @param  BP_Activity_Activity $activity The activity object.
 */
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

		/**
		 * Hook here to add custom actions when notifications has been marked as read.
		 *
		 * @since 2.0.0
		 *
		 * @param integer $user_id The ID of the activity author.
		 */
		do_action( 'buddyreshare_notifications_marked_read', $user_id );
	}
}
add_action( 'buddyreshare_users_reshared_screen',           'buddyreshare_notifications_read', 10, 1 );
add_action( 'bp_activity_screen_single_activity_permalink', 'buddyreshare_notifications_read', 10, 1 );

/**
 * Cleans Reshare notifications cache.
 *
 * @since 2.0.0
 *
 * @param  integer $user_id The ID of the user.
 */
function buddyreshare_notifications_clean_cache( $user_id = 0 ) {
	if ( ! $user_id ) {
		return;
	}

	wp_cache_delete( $user_id, 'reshared_notifications' );
}
add_action( 'buddyreshare_notifications_added',       'buddyreshare_notifications_clean_cache', 10, 1 );
add_action( 'buddyreshare_notifications_removed',     'buddyreshare_notifications_clean_cache', 10, 1 );
add_action( 'buddyreshare_notifications_marked_read', 'buddyreshare_notifications_clean_cache', 10, 1 );
