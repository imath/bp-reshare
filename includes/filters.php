<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Attach to the activities template the reshare datas
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @global $activities_template
 * @param  boolean $has_activities
 * @param  BP_Activity_Template  $activities_template
 * @param  array   $template_args
 * @uses   buddyreshare_can_reshare() to check if the activity can be reshared
 * @uses   bp_activity_get_meta() to fecth the number of reshares of this activity
 * @uses   buddyreshare_get_class() to eventually add the reshared class
 * @return boolean $has_activities
 */
function buddyreshare_extend_activities_template( $has_activities = false, $activities_template = null, $template_args = array() ) {
	global $activities_template;

	if( empty( $has_activities ) || empty( $activities_template ) )
		return $has_activities;

	foreach( $activities_template->activities as $activity ) {
		$activity->reshares = new StdClass();

		$activity->reshares->can_reshare = buddyreshare_can_reshare( $activity );

		/* Reshare count */
		$activity_first_id = $activity->id;

		if( 'reshare_update' == $activity->type )
			$activity_first_id = $activity->secondary_item_id;

		$activity->reshares->activity_id = $activity_first_id;

		$rs_count = bp_activity_get_meta( $activity_first_id, 'reshared_count' );
		$rs_count = !empty( $rs_count ) ? $rs_count : 0;

		$activity->reshares->count = $rs_count;

		$activity->reshares->css_class = buddyreshare_get_class( $activity, $activity_first_id );

	}

	return $has_activities;
}

/**
 * Catches the activity query string to eventually edit it
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @param  string $query_string
 * @param  string $object
 * @uses   wp_parse_args() to merge user's args with defaults
 * @uses   buddyreshare_is_user_profile_reshares() to check we're on a user's profile reshare tab
 * @uses   bp_displayed_user_id() to get displayed user's id
 * @uses   bp_loggedin_user_id() to get current user's id
 * @uses   bp_is_user() to check we're on user's profile
 * @return string|array $query_string
 */
function buddyreshare_activity_querystring_filter( $query_string, $object ) {
	if( $object != 'activity' )
		return $query_string;

	$r = wp_parse_args( $query_string, array(
		'scope'   => false,
		'action'  => false,
		'type'    => false,
		'user_id' => false,
		'page'    => 1

	) );

	/* global activities */
	if( $r['scope'] == 'reshares' || buddyreshare_is_user_profile_reshares() ) {

		$r['user_id'] = bp_displayed_user_id() ? bp_displayed_user_id() : bp_loggedin_user_id();
		$r['action'] = $r['type'] = 'reshare_update';
		$query_string = empty( $r ) ? $query_string : http_build_query( $r );
	}

	/* most reshared */
	if( $r['action'] == 'activity_mostreshared' ) {
		unset( $r['action'], $r['type'] );

		// on user's profile, shows the most reshared activities for displayed user
		if( bp_is_user() )
			$r['user_id'] = bp_displayed_user_id();

		$r['meta_query'] = array(
			array(
				'key' => 'reshared_count',
				'value' => 1,
				'type' => 'numeric',
				'compare' => '>='
			),
		);

		$query_string = empty( $r ) ? $query_string : $r;
	}

	return apply_filters( 'bp_reshare_activity_querystring_filter', $query_string, $object );
}

/**
 * Trick to order the most reshared DESC
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @param  string $sql
 * @param  string $select_sql
 * @param  string $from_sql
 * @param  string $where_sql
 * @param  string $sort
 * @param  string $pag_sql
 * @return string $sql
 */
function buddyreshare_order_by_most_reshared( $sql = '', $select_sql = '', $from_sql = '', $where_sql = '', $sort = '', $pag_sql = '' ) {

	if( strpos( $sql, 'reshared_count' ) !== false ) {
		preg_match( '/\'reshared_count\' AND CAST\((.*) AS/', $where_sql, $match );

		if( !empty( $match[1] ) )
			$sql = str_replace( 'ORDER BY a.date_recorded', 'ORDER BY '. $match[1] .' + 0' , $sql );
	}

	return $sql;
}

/**
 * Replaces the activity delete link in case of a reshare type
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @param  string $delete_link
 * @uses   bp_get_activity_type() to get activity type
 * @uses   wp_nonce_url() for security reason
 * @uses   bp_get_root_domain() to get the blog's url
 * @uses   bp_get_activity_root_slug() to get the activity slug
 * @uses   buddyreshare_get_component_slug() to get the component's slug
 * @uses   bp_get_activity_id() to get the activity id
 * @return string $delete_link
 */
function buddyreshare_maybe_replace_delete_link( $delete_link = '' ) {
	if( bp_get_activity_type() == 'reshare_update' ) {
		$action_url = wp_nonce_url( bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/' . buddyreshare_get_component_slug() . '/delete/' . bp_get_activity_id() . '/' , 'buddyreshare_delete' );
		$delete_link = '<a href="' . $action_url . '" class="button item-button bp-secondary-action delete-reshare confirm" rel="nofollow">' . __( 'Delete', 'bp-reshare' ) . '</a>';
	}

	return $delete_link;
}

/**
 * Filters the reshare class if the delete action is possible
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @param  array  $classes
 * @uses   buddyreshare_can_unshare() to check if the context is good to allow delete action
 * @return array  $classes
 */
function buddyreshare_activity_filter_button_class( $classes = array() ) {
	if( buddyreshare_can_unshare() )
		$classes[] = 'unshare';

	return $classes;
}

/**
 * Filters the reshare button title if the delete action is possible
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @param  string  $title
 * @uses   buddyreshare_can_unshare() to check if the context is good to allow delete action
 * @return string  $title
 */
function buddyreshare_activity_filter_button_title( $title = '' ) {
	if( buddyreshare_can_unshare() )
		$title = __( 'Unshare', 'bp-reshare' );

	return $title;
}

/**
 * Filters the reshare action url if the delete action is possible
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @param  string $action_url
 * @uses   buddyreshare_can_unshare() to check if the context is good to allow delete action
 * @uses   wp_nonce_url() for security reason
 * @uses   bp_get_root_domain() to get the blog's url
 * @uses   bp_get_activity_root_slug() to get the activity slug
 * @uses   buddyreshare_get_component_slug() to get the component's slug
 * @uses   bp_get_activity_id() to get the activity id [description]
 * @return string $action_url
 */
function buddyreshare_activity_filter_action_url( $action_url = '' ) {
	if( buddyreshare_can_unshare() )
		$action_url = wp_nonce_url( bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/' . buddyreshare_get_component_slug() . '/delete/' . bp_get_activity_id() . '/' , 'buddyreshare_delete' );

	return $action_url;
}

function buddyreshare_sort_activities_by_reshared_date( $sql = '', $args = array() ) {
	if ( false === apply_filters( 'buddyreshare_sort_activities_by_reshared', true ) || ! is_user_logged_in() ) {
		return $sql;
	}

	return str_replace( array(
			'WHERE',
			'ORDER BY'
		),
		array(
			sprintf( 'LEFT JOIN %sbp_activity_user_reshares r ON ( a.id = r.activity_id ) WHERE', bp_core_get_table_prefix() ),
			'ORDER BY IF( r.date_reshared > a.date_recorded, r.date_reshared, a.date_recorded ) DESC, ',
		),
		$sql
	);
}
add_filter( 'bp_activity_paged_activities_sql', 'buddyreshare_sort_activities_by_reshared_date', 20, 2 );
