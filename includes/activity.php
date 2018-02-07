<?php
/**
 * Activity functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns the templates and strings for JavaScript UIs.
 *
 * @since 2.0.0
 *
 * @param  string $template Which templates to get: 'all', 'reshareButton' or 'directoryTab' ?
 * @return array           The templates & strings list.
 */
function buddyreshare_activity_get_templates( $template = 'all' ) {
	$reshare_url = trailingslashit( bp_get_root_domain() ) .  bp_get_activity_root_slug() . '/' . buddyreshare_get_component_slug();

	$templates = array(
		'reshareButton' => '<a href="%l" class="bp-reshare button bp-secondary-action %r" data-activity-id="%a" data-author-name="%u">
			<span class="bp-reshare-icon"></span>
			<span class="bp-screen-reader-text">%t</span>
			<span class="count">%c</span>
		</a>',
		'directoryTab' => sprintf( '<li id="activity-reshares">
				<a href="%1$s" aria-label="%2$s">%3$s %4$s</a>
			</li>',
			esc_url_raw( bp_loggedin_user_domain() . bp_get_activity_slug() . '/'. buddyreshare_get_component_slug() .'/' ),
			esc_attr__( 'Activities I reshared.', 'bp-reshare' ),
			esc_html__( 'My Reshares', 'bp-reshare' ),
			'<span>%c</span>'
		),
	);

	if ( 'all' !== $template && isset( $templates[ $template ] ) ) {
		$templates = array_intersect_key( $templates, array( $template => true ) );
	}

	return array(
		'templates' => $templates,
		'strings'   => array(
			'addReshare'    => __( 'Reshare this activity', 'bp-reshare' ),
			'removeReshare' => __( 'Remove the Reshare of this activity', 'bp-reshare' ),
			'removeLink'    => esc_url_raw( wp_nonce_url( $reshare_url . '/delete/%i/' , 'buddyreshare_delete' ) ),
			'addLink'       => esc_url_raw( wp_nonce_url( $reshare_url . '/add/%i/' , 'buddyreshare_update' ) ),
			'genericError'  => __( 'Unknown error, please try again later.', 'bp-reshare' ),
		),
	);
}

/**
 * Returns the data to JavaScript build the single activity navigation.
 *
 * @since 2.0.0
 *
 * @param  integer $activity_id The ID of the activity.
 * @return array                Nav items for the single activity navigation.
 */
function buddyreshare_activity_get_single_nav( $activity_id = 0 ) {
	if ( ! $activity_id ) {
		return array();
	}

	$single_nav = array(
		'comments' => array(
			'singular'    => __( 'Comment', 'bp-reshare' ),
			'plural'      => __( 'Comments', 'bp-reshare' ),
			'position'    => 0,
			'users'       => array(),
			'no_item'     => __( 'This activity has no comments yet, add yours!', 'bp-reshare' ),
			'no_comments' => __( 'Activity comments are closed.', 'bp-reshare' ),
		),
		'reshares' => array(
			'singular' => __( 'User who Reshared', 'bp-reshare' ),
			'plural'   => __( 'Users who Reshared', 'bp-reshare' ),
			'position' => 1,
			'users'    => buddyreshare_users_get_reshares( $activity_id ),
			'no_item'  => __( 'This activity has no reshares yet, reshare it!', 'bp-reshare' ),
		),
	);

	if ( bp_activity_can_favorite() ) {
		$single_nav['favorites'] = array(
			'singular' => __( 'User who Favorited', 'bp-reshare' ),
			'plural'   => __( 'Users who Favorited', 'bp-reshare' ),
			'position' => 2,
			'users'    => buddyreshare_users_get_favorites( $activity_id ),
			'no_item'  => __( 'This activity is not favorited yet, add it to your favorites!', 'bp-reshare' ),
		);
	}

	/**
	 * Filter here to edit the single activity navigation.
	 *
	 * @since 2.0.0
	 *
	 * @param array $single_nav Nav items for the single activity navigation.
	 */
	return apply_filters( 'buddyreshare_activity_get_single_nav', $single_nav );
}

/**
 * Enqueue the activity scripts and style needed.
 *
 * @since 2.0.0
 */
function buddyreshare_activity_enqueue_assets() {
	$script_data = buddyreshare_get_common_script_data();

	if ( bp_is_activity_component() || bp_is_group_activity() ) {

		if ( bp_is_single_activity() ) {
			wp_enqueue_script( 'bp-reshare-activity' );

			if ( ! empty( $script_data['params']['u'] ) ) {
				$script_data = array_merge( $script_data, array(
					'activity'  => array(
						'nav'    => buddyreshare_activity_get_single_nav( bp_current_action() ),
						'id'     => (int) bp_current_action(),
						'loader' => esc_url_raw( admin_url( 'images/spinner-2x.gif' ) ),
					),
				) );
			}
		} else {
			wp_enqueue_script( 'bp-reshare' );
		}

		wp_enqueue_style( 'bp-reshare-style' );

		if ( ! empty( $script_data['params']['u'] ) ) {
			$user_domain       = bp_core_get_user_domain( $script_data['params']['u'] );
			$user_domain_path  = parse_url( $user_domain, PHP_URL_PATH );
			$user_domain_array = explode( '/', rtrim( $user_domain_path, '/' ) );
			$user_nicename     = end( $user_domain_array );
			$root_members      = str_replace( $user_nicename, '', rtrim( $user_domain, '/' ) );

			$script_data['params'] = array_merge( $script_data['params'], array(
				'root_members' => $root_members,
				'u_nicename'   => $user_nicename,
				'u_count'      => buddyreshare_users_reshares_count(),
				'time_since'   => buddyreshare_get_l10n_time_since(),
			) );
		}

		if ( bp_is_user_activity() && 'just-me' === bp_current_action() ) {
			$script_data['displayedUser'] = array(
				'avatar'       => bp_get_displayed_user_avatar( array( 'width' => '20px', 'height' => '20px' ) ),
				/* Translators: Do not omit the placeholder it is used to be replaced by the user avatar */
				'resharedText' => __( '%s reshared this activity', 'bp-reshare' ),
				'userID'       => bp_displayed_user_id(),
			);
		}

		$script_data = array_merge( $script_data, buddyreshare_activity_get_templates() );
	}

	wp_localize_script( 'bp-reshare-request', 'bpReshare', $script_data );
}
add_action( 'bp_enqueue_scripts', 'buddyreshare_activity_enqueue_assets' );

/**
 * Set the Activity directory Reshares scope.
 *
 * NB: This is only used with default BuddyPress order.
 *
 * @since 2.0.0
 *
 * @param  array $retval Empty array by default.
 * @param  array $filter Current activity arguments.
 * @return array        Override arguments for the Reshares scope
 */
function buddyreshare_activity_filter_scope( $retval = array(), $filter = array() ) {
	/**
	 * Filter here to override the use of the scope filter.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $value Whether the order preference is set to date of reshared or not.
	 */
	if ( true === apply_filters( 'buddyreshare_activity_sort_by_reshared_date', 'reshares' === buddyreshare_get_activity_order_preference() ) ) {
		return $retval;
	}

	// Get the reshares.
	$reshared = buddyreshare_users_get_reshared( get_current_user_id() );
	if ( empty( $reshared ) ) {
		$reshared = array( 0 );
	}

	$retval = array(
		'relation' => 'AND',
		array(
			'column'  => 'id',
			'compare' => 'IN',
			'value'   => (array) $reshared,
		),
		array(),

		// Overrides.
		'override' => array(
			'display_comments' => true,
			'filter'           => array( 'user_id' => 0 ),
			'show_hidden'      => true,
		),
	);

	return $retval;
}
add_filter( 'bp_activity_set_reshares_scope_args', 'buddyreshare_activity_filter_scope', 10, 2 );

/**
 * Add activities the user reshared into his personal stream.
 *
 * @since 2.0.0
 *
 * @param  array  $retval Activity arguments for use with the 'just-me' scope.
 * @param  array  $filter Current activity arguments.
 * @return array          Activity arguments for use with the 'just-me' scope.
 */
function buddyreshare_activity_just_me_scope( $retval = array(), $filter = array() ) {
	if ( empty( $retval[0]['column'] ) || 'user_id' !== $retval[0]['column'] ) {
		return $retval;
	}

	$retval['relation'] = 'OR';
	array_push( $retval, array(
		'column'  => 'id',
		'compare' => 'IN',
		'value'   => (array) buddyreshare_users_get_reshared( $retval[0]['value'] ),
	) );

	if ( ! bp_is_my_profile() ) {
		$columns = wp_list_pluck( array_diff_key( $retval, array(
			'relation' => false,
			'override' => false )
		), 'column' );

		$hide_sitewide_index = array_search( 'hide_sitewide', $columns );

		if ( false !== $hide_sitewide_index ) {
			unset( $retval[ $hide_sitewide_index] );

			if ( isset( $retval['override'] ) ) {
				$retval['override']['show_hidden'] = 0;
			}
		}
	}

	return $retval;
}
add_filter( 'bp_activity_set_just-me_scope_args', 'buddyreshare_activity_just_me_scope', 20, 2 );

/**
 * Order the Activity stream according to the last reshared date.
 *
 * @since 2.0.0
 *
 * @param  string $sql  The main activity SQL Query.
 * @param  array  $args The requested arguments for this query.
 * @return string       The SQL Query ordered by reshared date (or unchanged).
 */
function buddyreshare_activity_sort_by_reshared_date( $sql = '', $args = array() ) {
	$and = '';
	$show_sitewide = str_replace( 'AND a.hide_sitewide = 0', '', $sql );

	if ( buddyreshare_is_user_profile_reshares() ) {
		$and = ' AND r.date_reshared IS NOT NULL ';

		// Display all reshared activities to the current user.
		if ( bp_is_my_profile() || current_user_can( 'bp_moderate' ) ) {
			$sql = $show_sitewide;
		}

	} elseif ( isset( $args['scope'] ) && 'reshares' === $args['scope'] ) {
		$and = sprintf( ' AND r.user_id = %d ', get_current_user_id() );

		// Display all reshares to the current user.
		$sql = $show_sitewide;
	}

	/**
	 * Filter here to override the sort preference.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $value Whether the order preference is set to date of reshared or not.
	 */
	if ( false === apply_filters( 'buddyreshare_activity_sort_by_reshared_date', 'reshares' === buddyreshare_get_activity_order_preference() ) || ! is_user_logged_in() ) {
		if ( buddyreshare_is_user_profile_reshares() ) {
			$sql = str_replace( array(
					'WHERE',
					'ORDER BY'
				),
				array(
					sprintf( 'LEFT JOIN %sbp_activity_user_reshares r ON ( a.id = r.activity_id ) WHERE', bp_core_get_table_prefix() ),
					sprintf( '%sORDER BY', $and ),
				),
				$sql
			);
		}

		return $sql;
	}

	return str_replace( array(
			'WHERE',
			'ORDER BY a.date_recorded DESC'
		),
		array(
			sprintf( 'LEFT JOIN ( SELECT activity_id, user_id, date_reshared FROM %sbp_activity_user_reshares ORDER BY id DESC ) r ON ( a.id = r.activity_id ) WHERE', bp_core_get_table_prefix() ),
			sprintf( '%sORDER BY IF( r.date_reshared > a.date_recorded, r.date_reshared, a.date_recorded ) DESC', $and ),
		),
		$sql
	);
}
add_filter( 'bp_activity_paged_activities_sql', 'buddyreshare_activity_sort_by_reshared_date', 20, 2 );

/**
 * Clean the Activity stream cache on reshare's events.
 *
 * @since 2.0.0
 */
function buddyreshare_activity_reset_cache() {
	wp_cache_delete( 'bp_activity_sitewide_front', 'bp' );
	bp_core_reset_incrementor( 'bp_activity' );
	bp_core_reset_incrementor( 'bp_activity_with_last_activity' );
}
add_action( 'buddyreshare_reshare_added',    'buddyreshare_activity_reset_cache' );
add_action( 'buddyreshare_reshare_deleted',  'buddyreshare_activity_reset_cache' );
add_action( 'buddyreshare_reshares_deleted', 'buddyreshare_activity_reset_cache' );

/**
 * Fallback in case JavaScript fails to add a reshare to an activity
 *
 * @since 2.0.0
 *
 * @param array $args {
 *  An array of arguments.
 *  @type int    $activity_id    Pass an activity ID to reshare it.
 *  @type int    $user_id        Pass a the ID of the user resharing it.
 *  @type string $date_reshared  Pass a MySql formatted date.
 *  @type string $author_slug    Optional. The nicename of the author of the activty.
 * }
 */
function buddyreshare_activity_add_reshare( $args = array() ) {
	if ( 'bp_actions' === current_action() && ! ( bp_is_activity_component() && bp_is_current_action( buddyreshare_get_component_slug() ) && 'add' === bp_action_variable() ) ) {
		return;
	}

	$defaults = array(
		'activity_id'   => 0,
		'user_id'       => get_current_user_id(),
		'date_reshared' => bp_core_current_time(),
	);

	$error_message = __( 'There was an error resharing the activity. Please try again.', 'bp-reshare' );

	// Set the reshare args according to the URL.
	if ( bp_is_activity_component() && bp_is_current_action( buddyreshare_get_component_slug() ) && 'add' === bp_action_variable() ) {
		check_admin_referer( 'buddyreshare_update' );

		$activity = new BP_Activity_Activity( bp_action_variable( 1 ) );

		if ( empty( $activity->id ) ) {
			bp_core_add_message( $error_message, 'error' );
		}

		$r = wp_parse_args( array(
			'activity_id' => $activity->id,
			'author_slug' => bp_core_get_username( $activity->user_id )
		), $defaults );
	} else {
		$r = wp_parse_args( $args, $defaults );
	}

	if ( empty( $r['activity_id'] ) || empty( $r['user_id'] ) || empty( $r['date_reshared'] ) ) {
		bp_core_add_message( $error_message, 'error' );

	// Add The reshare
	} else {
		global $wpdb;
		$table    = bp_core_get_table_prefix() . 'bp_activity_user_reshares';
		$inserted = $wpdb->insert( $table, array_intersect_key( $r, $defaults ) );

		if ( ! $inserted ) {
			bp_core_add_message( $error_message, 'error' );
		} else {
			bp_core_add_message( __( 'Activity reshared.', 'bp-reshare' ) );

			/**
			 * Hook here to perform custom actions once the reshare is added.
			 *
			 * @since 2.0.0
			 *
			 * @param array $r {
			 *  An array of arguments.
			 *  @type int    $activity_id    The activity ID the reshare refers to.
			 *  @type int    $user_id        The ID of the user who's resharing it.
			 *  @type string $date_reshared  The MySql formatted date for this reshare.
			 *  @type string $author_slug    Optional. The nicename of the author of the activty.
			 * }
			 */
			do_action( 'buddyreshare_reshare_added', $r );
		}
	}

	// Redirect the user to where he comes from
	if ( ! empty( $activity->id ) ) {
		bp_core_redirect( wp_get_referer() . '#activity-' . $activity->id );
	} else {
		return $inserted;
	}
}
add_action( 'bp_actions', 'buddyreshare_activity_add_reshare' );

/**
 * Fallback in case JavaScript fails to remove an activity reshare.
 *
 * It's also used when deleting the reshared activity.
 *
 * @since 2.0.0
 *
 * @param array $args {
 *  An array of arguments.
 *  @type int    $activity_id    Pass an activity ID to reshare it.
 *  @type int    $user_id        Pass a the ID of the user resharing it.
 *  @type string $author_slug    Optional. The nicename of the author of the activty.
 * }
 */
function buddyreshare_activity_remove_reshare( $args = array() ) {
	global $wpdb;

	$action = current_action();
	$table  = bp_core_get_table_prefix() . 'bp_activity_user_reshares';

	// Delete the reshare(s) when one or more activities are deleted.
	if ( 'bp_activity_deleted_activities' === $action ) {
		$deleted_activities = array_filter( wp_parse_id_list( $args ) );

		if ( empty( $deleted_activities ) ) {
			return;
		}

		// We need to get user ids to properly clean the users reshare cache.
		$user_ids = $wpdb->get_col( sprintf( "SELECT DISTINCT user_id FROM {$table} WHERE activity_id IN ( %s )", join( ',', $deleted_activities ) ) );
		$deleted  = $wpdb->query( sprintf( "DELETE FROM {$table} WHERE activity_id IN ( %s )", join( ',', $deleted_activities ) ) );

		if ( ! is_wp_error( $deleted ) ) {
			/**
			 * Hook here to perform custom actions once many activity where deleted.
			 *
			 * @since 2.0.0
			 *
			 * @param array $value {
			 *  array $user_ids           The list of deleted activity authors.
			 *  array $deleted_activities The list of IDs of the deleted activities.
			 * }
			 */
			do_action( 'buddyreshare_reshares_deleted', array(
				'user_ids'     => $user_ids,
				'activity_ids' => $deleted_activities,
			) );
		}

	// Stop, it's not
	} elseif ( 'bp_actions' === $action && ! ( bp_is_activity_component() && bp_is_current_action( buddyreshare_get_component_slug() ) && 'delete' === bp_action_variable() ) ) {
		return;
	}

	$defaults = array(
		'activity_id' => 0,
		'user_id'     => get_current_user_id(),
	);

	$error_message = __( 'There was an error removing the reshare for the activity. Please try again.', 'bp-reshare' );

	if ( bp_is_activity_component() && bp_is_current_action( buddyreshare_get_component_slug() ) && 'delete' === bp_action_variable() ) {
		check_admin_referer( 'buddyreshare_delete' );

		$activity = new BP_Activity_Activity( bp_action_variable( 1 ) );

		if ( empty( $activity->id ) ) {
			bp_core_add_message( $error_message, 'error' );
		}

		$r = wp_parse_args( array(
			'activity_id' => $activity->id,
			'author_slug' => bp_core_get_username( $activity->user_id )
		), $defaults );
	} else {
		$r = wp_parse_args( $args, $defaults );
	}

	if ( empty( $r['user_id'] ) || empty( $r['activity_id'] ) ) {
		bp_core_add_message( $error_message, 'error' );
	} else {
		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE user_id = %d AND activity_id = %d", $r['user_id'], $r['activity_id'] ) );

		if ( is_wp_error( $deleted ) ) {
			bp_core_add_message( $error_message, 'error' );
		} else {
			bp_core_add_message( __( 'Activity reshare removed.', 'bp-reshare' ) );

			/**
			 * Hook here to perform custom actions once the reshare is removed.
			 *
			 * @since 2.0.0
			 *
			 * @param array $r {
			 *  An array of arguments.
			 *  @type int    $activity_id    The activity ID the reshare refers to.
			 *  @type int    $user_id        The ID of the user who's reshared it.
			 *  @type string $author_slug    Optional. The nicename of the author of the activty.
			 * }
			 */
			do_action( 'buddyreshare_reshare_deleted', $r );
		}
	}

	// Redirect the user to where he comes from
	if ( ! empty( $activity->id ) ) {
		bp_core_redirect( wp_get_referer() . '#activity-' . $activity->id );
	} else {
		return $deleted;
	}
}
add_action( 'bp_actions', 'buddyreshare_activity_remove_reshare' );
add_action( 'bp_activity_deleted_activities', 'buddyreshare_activity_remove_reshare' );
