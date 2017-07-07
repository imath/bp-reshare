<?php
/**
 * Rest functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


function buddyreshare_rest_read_permissions_check( WP_REST_Request $request ) {
	if ( is_user_logged_in() ) {
		return true;
	}

	return false;
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
		return new WP_Error( 'bp_reshare_missing_argument', __( 'Missing argument', 'bp-reshare' ), array( 'status' => 500 ) );
	}

	$type = sanitize_key( $r['type'] );
	unset( $r['type'] );

	if ( empty( $r['include'] ) ) {

		if ( ! function_exists( 'buddyreshare_users_get_' . $type  ) ) {
			return new WP_Error( 'bp_reshare_unknown_callback', __( 'Missing callback', 'bp-reshare' ), array( 'status' => 500 ) );
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

function buddyreshare_rest_edit_permissions_check( WP_REST_Request $request ) {
	$user_id = $request->get_param( 'user_id' );

	if ( (int) $user_id === (int) get_current_user_id() || current_user_can( 'edit_users' ) ) {
		return true;
	}

	return false;
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
		return new WP_Error( 'bp_reshare_missing_argument', __( 'Missing argument', 'bp-reshare' ), array( 'status' => 500 ) );
	}

	$result = array( 'reshared' => false );

	if ( $wpdb->insert( $table, $r ) ) {
		$result['reshared'] = strtotime( $r['date_reshared'] );
	}

	do_action( 'buddyreshare_reshare_added', $args );

	return rest_ensure_response( $result );
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
		return new WP_Error( 'bp_reshare_missing_argument', __( 'Missing argument', 'bp-reshare' ), array( 'status' => 500 ) );
	}

	$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE user_id = %d AND activity_id = %d", $r['user_id'], $r['activity_id'] ) );

	if ( is_wp_error( $deleted ) ) {
		return $deleted;
	}

	do_action( 'buddyreshare_reshare_deleted', $args );

	return rest_ensure_response( array( 'deleted' => (bool) $deleted ) );
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
			'permission_callback' => 'buddyreshare_rest_read_permissions_check',
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
			'permission_callback' => 'buddyreshare_rest_edit_permissions_check',
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
			'permission_callback' => 'buddyreshare_rest_edit_permissions_check',
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
			'permission_callback' => 'buddyreshare_rest_read_permissions_check',
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
