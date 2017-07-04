<?php
/**
 * Main functions.
 *
 * @package BP Reshare\includes
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns plugin version
 *
 * @since 1.0
 *
 * @return string The plugin's version
 */
function buddyreshare_get_plugin_version() {
	return buddyreshare()->version;
}

/**
 * Returns plugin's dir
 *
 * @since 1.0
 *
 * @return string The plugin's dir path
 */
function buddyreshare_get_plugin_dir() {
	return buddyreshare()->plugin_dir;
}

/**
 * Returns plugin's includes dir
 *
 * @since 1.0
 *
 * @return string The plugin's includes dir path
 */
function buddyreshare_get_includes_dir() {
	return buddyreshare()->includes_dir;
}

/**
 * Returns plugin's js url
 *
 * @since  1.0
 * @since  2.0.0 Path edited to one level up.
 *
 * @return string The plugin's url to JavaScript's dir.
 */
function buddyreshare_get_js_url() {
	return buddyreshare()->js_url;
}

/**
 * Returns plugin's css url
 *
 * @since  1.0
 * @since  2.0.0 Path edited to one level up.
 *
 * @return string The plugin's url to Style's dir.
 */
function buddyreshare_get_css_url() {
	return buddyreshare()->css_url;
}

/**
 * Get the JS/CSS minified suffix.
 *
 * @since 2.0.0
 *
 * @return string the JS/CSS minified suffix.
 */
function buddyreshare_min_suffix() {
	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG )  {
		$min = '';
	}

	/**
	 * Filter here to edit the minified suffix.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $min The minified suffix.
	 */
	return apply_filters( 'buddyreshare_min_suffix', $min );
}

/**
 * Returns plugin's component id
 *
 * @since 1.0
 *
 * @return string plugin's component id
 */
function buddyreshare_get_component_id() {
	return buddyreshare()->component_id;
}

/**
 * Returns plugin's component slug
 *
 * @since 1.0
 *
 * @return string plugin's component slug
 */
function buddyreshare_get_component_slug() {
	return apply_filters( 'buddyreshare_get_component_slug', buddyreshare()->component_slug );
}

/**
 * Displays the component name
 *
 * @since 1.0
 */
function buddyreshare_component_name() {
	echo buddyreshare_get_component_name();
}

/**
 * Returns plugin's component name
 *
 * @since 1.0
 *
 * @return string plugin's component name
 */
function buddyreshare_get_component_name() {
	return apply_filters( 'buddyreshare_get_component_name', buddyreshare()->component_name );
}

/**
 * Are email notifications active ?
 *
 * @since 2.0.0
 *
 * @return boolean True to send emails, False otherwise.
 */
function buddyreshare_are_emails_active() {
	return (bool) apply_filters( 'buddyreshare_are_emails_active', bp_get_option( 'buddyreshare-emails', false ) );
}

/**
 * Are we on a the current user's profile reshare tab
 *
 * @since 1.0
 * @since 2.0.0 Code clean up.
 *
 * @return  boolean true|false
 */
function buddyreshare_is_user_profile_reshares() {
	$return = false;

	if ( bp_is_activity_component() && bp_is_user() && bp_is_current_action( buddyreshare_get_component_slug() ) ) {
		$return = true;
	}

	return $return;
}

/**
 * Returns the disabled activity actions
 *
 * @since 2.0.0
 *
 * @return array the disabled activity actions.
 */
function buddyreshare_get_disabled_activity_types() {
	$disabled_types = explode( ',', trim( bp_get_option( 'buddyreshare-disabled-activity-types', array() ), ' ' ) );

	return (array) apply_filters( 'buddyreshare_get_disabled_activity_types', array_filter( $disabled_types ) );
}

function buddyreshare_get_activity_order_preference() {
	return bp_get_option( 'buddyreshare-activity-order-preferences', 'reshares' );
}

function buddyreshare_sort_activities_by_reshared_date( $sql = '', $args = array() ) {
	$order_preference = buddyreshare_get_activity_order_preference();

	if ( false === apply_filters( 'buddyreshare_sort_activities_by_reshared', 'reshares' === $order_preference ) || ! is_user_logged_in() ) {
		return $sql;
	}

	$and = '';

	if ( buddyreshare_is_user_profile_reshares() ) {
		$and = ' AND r.date_reshared IS NOT NULL ';
	} elseif ( isset( $args['scope'] ) && 'reshares' === $args['scope'] ) {
		$and = sprintf( ' AND r.user_id = %d ', get_current_user_id() );
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
add_filter( 'bp_activity_paged_activities_sql', 'buddyreshare_sort_activities_by_reshared_date', 20, 2 );

function buddyreshare_get_l10n_time_since() {
	return array(
		'sometime'  => _x( '(Reshared sometime)', 'javascript time since', 'bp-reshare' ),
		'now'       => _x( '(Reshared right now)', 'javascript time since', 'bp-reshare' ),
		'ago'       => _x( '(Reshared % ago)', 'javascript time since', 'bp-reshare' ),
		'separator' => _x( ',', 'Separator in javascript time since', 'bp-reshare' ),
		'year'      => _x( '% year', 'javascript time since singular', 'bp-reshare' ),
		'years'     => _x( '% years', 'javascript time since plural', 'bp-reshare' ),
		'month'     => _x( '% month', 'javascript time since singular', 'bp-reshare' ),
		'months'    => _x( '% months', 'javascript time since plural', 'bp-reshare' ),
		'week'      => _x( '% week', 'javascript time since singular', 'bp-reshare' ),
		'weeks'     => _x( '% weeks', 'javascript time since plural', 'bp-reshare' ),
		'day'       => _x( '% day', 'javascript time since singular', 'bp-reshare' ),
		'days'      => _x( '% days', 'javascript time since plural', 'bp-reshare' ),
		'hour'      => _x( '% hour', 'javascript time since singular', 'bp-reshare' ),
		'hours'     => _x( '% hours', 'javascript time since plural', 'bp-reshare' ),
		'minute'    => _x( '% minute', 'javascript time since singular', 'bp-reshare' ),
		'minutes'   => _x( '% minutes', 'javascript time since plural', 'bp-reshare' ),
		'second'    => _x( '% second', 'javascript time since singular', 'bp-reshare' ),
		'seconds'   => _x( '% seconds', 'javascript time since plural', 'bp-reshare' ),
		'time_chunks' => array(
			'a_year'   => YEAR_IN_SECONDS,
			'b_month'  => 30 * DAY_IN_SECONDS,
			'c_week'   => WEEK_IN_SECONDS,
			'd_day'    => DAY_IN_SECONDS,
			'e_hour'   => HOUR_IN_SECONDS,
			'f_minute' => MINUTE_IN_SECONDS,
			'g_second' => 1,
		),
	);
}

function buddyreshare_rest_get_all_items( WP_REST_Request $request ) {
	global $wpdb;

	$table = bp_core_get_table_prefix() . 'bp_activity_user_reshares';
	$query = "SELECT activity_id, user_id, date_reshared FROM {$table}";

	$activities = $request->get_param( 'activities' );
	if ( $activities ) {
		$query .= ' WHERE activity_id IN (' . join( ',', wp_parse_id_list( $activities ) ) . ')';
	}

	$query .= ' ORDER BY date_reshared DESC';

	$reshares = $wpdb->get_results( $query );

	$result = array();
	foreach ( $reshares as $reshare ) {
		if ( ! isset( $result[ $reshare->activity_id ] ) ) {
			$result[ $reshare->activity_id ] = array( 'id' => $reshare->activity_id, 'users' => array( $reshare->user_id ), 'time' => strtotime( $reshare->date_reshared ) );
		} else {
			$result[ $reshare->activity_id ]['users'] = array_merge( $result[ $reshare->activity_id ]['users'], array( $reshare->user_id ) );
		}
	}

	return rest_ensure_response( array_values( $result ) );
}

function buddyreshare_rest_get_all_items_permissions_check( WP_REST_Request $request ) {
	return true;
}

function buddyreshare_rest_get_items( WP_REST_Request $request ) {
	$args = $request->get_params();

	if ( isset( $args['id'] ) ) {
		$args['activity_id'] = (int) $args['id'];
	}

	$r = array_intersect_key( $args, array(
		'activity_id' => true,
		'type'        => true,
		'page'        => true,
		'per_page'    => true,
		'include'     => true,
	) );

	if ( empty( $r['activity_id'] ) && empty( $r['include'] ) ) {
		return new WP_Error( 'bp_reshare_missing_argument', __( 'Missing argument' ), array( 'status' => 500 ) );
	}

	$type = sanitize_key( $r['type'] );
	unset( $r['type'] );

	if ( empty( $r['include'] ) ) {

		if ( ! function_exists( 'buddyreshare_users_get_' . $type  ) ) {
			return new WP_Error( 'bp_reshare_unknown_callback', __( 'Missing callback' ), array( 'status' => 500 ) );
		}

		$include = call_user_func( 'buddyreshare_users_get_' . $type, $r['activity_id'] );
	} else {
		$include = wp_parse_id_list( $r['include'] );
	}

	if ( ! is_array( $include ) || ! count( $include ) ) {
		return array();
	}

	$result = array();
	if ( bp_has_members( $r ) ) {
		while ( bp_members() ) : bp_the_member();
			// Get User actions.
			ob_start();
			do_action( 'bp_directory_members_actions' );
			$user_actions = ob_get_clean();

			$user_id = bp_get_member_user_id();

			$result['users'][ $user_id ] = sprintf( '<li %1$s>
					<div class="item-avatar">
						<a href="%2$s">%3$s</a>
					</div>
					<div class="item">
						<div class="item-title">
							<a href="%2$s">%4$s</a>
						</div>
						<div class="item-meta"><span class="activity" data-livestamp="%5$s">%6$s</span></div>
					</div>
					<div class="action">%7$s</div>
					<div class="clear"></div>
				</li>',
				bp_get_member_class(),
				esc_url( bp_get_member_permalink() ),
				bp_get_member_avatar(),
				esc_html( bp_get_member_name() ),
				esc_attr( bp_core_get_iso8601_date( bp_get_member_last_active( array( 'relative' => false ) ) ) ),
				bp_get_member_last_active(),
				$user_actions
			);

		endwhile;

		$result['has_more'] = false;
		if ( ! empty( $GLOBALS['members_template']->total_member_count ) ) {
			$result['has_more'] = ( $r['page'] * $r['per_page'] ) < $GLOBALS['members_template']->total_member_count;
		}
	}

	return rest_ensure_response( $result );
}

function buddyreshare_rest_get_items_permissions_check( WP_REST_Request $request ) {
	return true;
}

function buddyreshare_rest_update_item( WP_REST_Request $request ) {
	global $wpdb;

	$table = bp_core_get_table_prefix() . 'bp_activity_user_reshares';
	$args = $request->get_params();

	if ( isset( $args['id'] ) ) {
		$args['activity_id'] = (int) $args['id'];
	}

	$defaults = array(
		'activity_id'   => 0,
		'user_id'       => get_current_user_id(),
		'date_reshared' => bp_core_current_time(),
	);

	$r = array_intersect_key( wp_parse_args( $args, $defaults ), $defaults );

	if ( empty( $r['user_id'] ) || empty( $r['activity_id'] ) || empty( $r['date_reshared'] ) ) {
		return new WP_Error( 'bp_reshare_missing_argument', __( 'Missing argument' ), array( 'status' => 500 ) );
	}

	$result = array( 'reshared' => false );

	if ( $wpdb->insert( $table, $r ) ) {
		$result['reshared'] = strtotime( $r['date_reshared'] );
	}

	do_action( 'buddyreshare_reshare_added', $args );

	return rest_ensure_response( $result );
}

function buddyreshare_rest_update_item_permissions_check( WP_REST_Request $request ) {
	return true;
}

function buddyreshare_rest_delete_item( WP_REST_Request $request ) {
	global $wpdb;

	$table = bp_core_get_table_prefix() . 'bp_activity_user_reshares';
	$args  = $request->get_params();

	if ( isset( $args['id'] ) ) {
		$args['activity_id'] = (int) $args['id'];
	}

	$defaults = array(
		'user_id'       => get_current_user_id(),
		'activity_id'   => 0,
	);

	$r = array_intersect_key( wp_parse_args( $args, $defaults ), $defaults );

	if ( empty( $r['user_id'] ) || empty( $r['activity_id'] ) ) {
		return new WP_Error( 'bp_reshare_missing_argument', __( 'Missing argument' ), array( 'status' => 500 ) );
	}

	$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE user_id = %d AND activity_id = %d", $r['user_id'], $r['activity_id'] ) );

	if ( is_wp_error( $deleted ) ) {
		return $deleted;
	}

	do_action( 'buddyreshare_reshare_deleted', $args );

	return rest_ensure_response( array( 'deleted' => (bool) $deleted ) );
}

function buddyreshare_rest_delete_item_permissions_check( WP_REST_Request $request ) {
	return true;
}

function buddyreshare_rest_routes() {
	$buddyreshare = buddyreshare();

	$namespace = sprintf( '%1$s/%2$s', $buddyreshare->rest->namespace, $buddyreshare->rest->version );

	register_rest_route( $namespace, '/(?P<id>[\d]+)', array(
		'args' => array(
			'id' => array(
				'description'       => __( 'Unique identifier for the object.', 'bp-reshare' ),
				'type'              => 'integer',
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				}
			),
		),
		array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => 'buddyreshare_rest_get_items',
			'permission_callback' => 'buddyreshare_rest_get_items_permissions_check',
			'args'     => array(
				'page' => array(
					'type'        => 'integer',
					'default'     => 1,
					'description' => __( 'The page number to fetch', 'bp-reshare' ),
				),
				'per_page' => array(
					'type'        => 'integer',
					'default'     => 5,
					'description' => __( 'The number of results to fetch.', 'bp-reshare' ),
				),
				'include' => array(
					'type'        => 'string',
					'default'     => '',
					'description' => __( 'A comma separated user id list to limit the results to fetch.', 'bp-reshare' ),
				),
				'type' => array(
					'type'        => 'string',
					'default'     => 'reshares',
					'description' => __( 'The type of user action to fetch.', 'bp-reshare' ),
				),
			),
		),
		array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => 'buddyreshare_rest_update_item',
			'permission_callback' => 'buddyreshare_rest_update_item_permissions_check',
			'args'     => array(
				'user_id' => array(
					'type'        => 'integer',
					'default'     => 0,
					'description' => __( 'The user ID.', 'bp-reshare' ),
				),
				'author_slug' => array(
					'type'        => 'string',
					'default'     => '',
					'description' => __( 'The activity author slug.', 'bp-reshare' ),
				),
			),
		),
		array(
			'methods'  => WP_REST_Server::DELETABLE,
			'callback' => 'buddyreshare_rest_delete_item',
			'permission_callback' => 'buddyreshare_rest_delete_item_permissions_check',
			'args'     => array(
				'user_id' => array(
					'type'        => 'integer',
					'default'     => 0,
					'description' => __( 'The user ID.', 'bp-reshare' ),
				),
				'author_slug' => array(
					'type'        => 'string',
					'default'     => '',
					'description' => __( 'The activity author slug.', 'bp-reshare' ),
				),
			),
		),
	) );

	register_rest_route( $namespace, '/all', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => 'buddyreshare_rest_get_all_items',
			'permission_callback' => 'buddyreshare_rest_get_all_items_permissions_check',
			'args'     => array(
				'activities' => array(
					'page' => array(
					'type' => 'string',
					'default' => '',
					'description' => __( 'comma separated list of activity ids to fetch', 'bp-reshare' ),
				),
			),
		) ) );
}
add_action( 'rest_api_init', 'buddyreshare_rest_routes' );

function buddyreshare_reset_activity_cache() {
	wp_cache_delete( 'bp_activity_sitewide_front', 'bp' );
	bp_core_reset_incrementor( 'bp_activity' );
	bp_core_reset_incrementor( 'bp_activity_with_last_activity' );
}
add_action( 'buddyreshare_reshare_added', 'buddyreshare_reset_activity_cache' );
add_action( 'buddyreshare_reshare_deleted', 'buddyreshare_reset_activity_cache' );
