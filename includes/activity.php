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

function buddyreshare_activity_scripts() {
	$script_data = buddyreshare_get_common_script_data();

	if ( bp_is_activity_component() || bp_is_group_activity() ) {

		if ( bp_is_single_activity() ) {
			wp_enqueue_script( 'bp-reshare-activity' );

			if ( ! empty( $script_data['params']['u'] ) ) {
				$activity_nav = array(
					'comments' => array(
						'singular' => __( 'Comment', 'bp-reshare' ),
						'plural'   => __( 'Comments', 'bp-reshare' ),
						'position' => 0,
						'users'    => array(),
						'no_item'  => __( 'This activity has no comments yet, add yours!', 'bp-reshare' ),
					),
					'reshares' => array(
						'singular' => __( 'User who Reshared', 'bp-reshare' ),
						'plural'   => __( 'Users who Reshared', 'bp-reshare' ),
						'position' => 1,
						'users'    => buddyreshare_users_get_reshares( bp_current_action() ),
						'no_item'  => __( 'This activity has no reshares yet, reshare it!', 'bp-reshare' ),
					),
				);

				if ( bp_activity_can_favorite() ) {
					$activity_nav['favorites'] = array(
						'singular' => __( 'User who Favorited', 'bp-reshare' ),
						'plural'   => __( 'Users who Favorited', 'bp-reshare' ),
						'position' => 2,
						'users'    => buddyreshare_users_get_favorites( bp_current_action() ),
						'no_item'  => __( 'This activity is not favorited yet, add it to your favorites!', 'bp-reshare' ),
					);
				}

				$script_data = array_merge( $script_data, array(
					'activity'  => array(
						'nav'    => $activity_nav,
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

		$reshare_url = trailingslashit( bp_get_root_domain() ) .  bp_get_activity_root_slug() . '/' . buddyreshare_get_component_slug();

		$script_data = array_merge( $script_data, array(
			'templates' => array(
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
					esc_html__( 'My Reshares', 'bp-reshares' ),
					'<span>%c</span>'
				),
			),
			'strings'  => array(
				'addReshare'    => __( 'Reshare this activity', 'bp-reshare' ),
				'removeReshare' => __( 'Remove the Reshare of this activity', 'bp-reshare' ),
				'removeLink'    => esc_url_raw( wp_nonce_url( $reshare_url . '/delete/%i/' , 'buddyreshare_delete' ) ),
				'addLink'       => esc_url_raw( wp_nonce_url( $reshare_url . '/add/%i/' , 'buddyreshare_update' ) ),
			),
		) );
	}

	wp_localize_script( 'bp-reshare-request', 'bpReshare', $script_data );
}
add_action( 'bp_enqueue_scripts', 'buddyreshare_activity_scripts' );

function buddyreshare_activity_filter_scope( $retval = array(), $filter = array() ) {
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

function buddyreshare_activity_sort_by_reshared_date( $sql = '', $args = array() ) {
	$and = '';

	if ( buddyreshare_is_user_profile_reshares() ) {
		$and = ' AND r.date_reshared IS NOT NULL ';
	} elseif ( isset( $args['scope'] ) && 'reshares' === $args['scope'] ) {
		$and = sprintf( ' AND r.user_id = %d ', get_current_user_id() );
	}

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

function buddyreshare_activity_reset_cache() {
	wp_cache_delete( 'bp_activity_sitewide_front', 'bp' );
	bp_core_reset_incrementor( 'bp_activity' );
	bp_core_reset_incrementor( 'bp_activity_with_last_activity' );
}
add_action( 'buddyreshare_reshare_added',   'buddyreshare_activity_reset_cache' );
add_action( 'buddyreshare_reshare_deleted', 'buddyreshare_activity_reset_cache' );
