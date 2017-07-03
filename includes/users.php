<?php
/**
 * User functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns the total reshares for a given user
 *
 * @since   2.0.0
 *
 * @param  integer $user_id The user ID to get the number of reshares of.
 * @return integer the number of reshares of the user
 */
function buddyreshare_users_reshares_count( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$user_id        = (int) $user_id;
	$reshares_count = wp_cache_get( $user_id, 'reshares_count' );

	if ( empty( $reshares_count ) ) {
		global $wpdb;

		$table          = bp_core_get_table_prefix() . 'bp_activity_user_reshares';
		$reshares_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( id ) as count FROM {$table} WHERE user_id = %d", $user_id ) );

		wp_cache_add( $user_id, $reshares_count, 'reshares_count' );
	}

	return $reshares_count;
}

function buddyreshare_users_reshared_screen() {
	if ( ! buddyreshare_is_user_profile_reshares() ) {
		return;
	}

	do_action( 'buddyreshare_users_reshared_screen' );

	bp_core_load_template( apply_filters( 'buddyreshare_users_reshared_template', 'members/single/home' ) );
}

function buddyreshare_users_navigation() {
	$link = bp_displayed_user_id() ? bp_displayed_user_domain() : bp_loggedin_user_domain();

	bp_core_new_subnav_item( array(
		'name' 		        => __( 'Reshared Activities', 'bp-reshare' ),
		'slug' 		        => buddyreshare_get_component_slug(),
		'parent_slug'     => bp_get_activity_slug(),
		'parent_url' 	    => trailingslashit( $link . bp_get_activity_slug() ),
		'user_has_access' => true,
		'screen_function' => 'buddyreshare_users_reshared_screen',
		'position' 	      => 40
	) );
}
add_action( 'bp_activity_setup_nav', 'buddyreshare_users_navigation' );


function buddyreshare_users_admin_menu() {
	$GLOBALS['wp_admin_bar']->add_menu( array(
		'parent' => 'my-account-activity',
		'id'     => 'my-account-activity-' . buddyreshare_get_component_slug(),
		'title'  => __( 'Reshared Activities', 'bp-reshare' ),
		'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . buddyreshare_get_component_slug() ),
	) );
}
add_action( 'bp_activity_setup_admin_bar', 'buddyreshare_users_admin_menu' );

function buddyreshare_users_get_favorites( $activity_id = 0 ) {
	$user_favorites = array();

	if ( ! $activity_id ) {
		return $user_favorites;
	}

	$user_favorites = wp_cache_get( $activity_id, 'user_favorites' );

	if ( ! $user_favorites ) {
		global $wpdb;

		$favorite       = serialize( (string) $activity_id );
		$user_favorites = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta}
			WHERE meta_key = 'bp_favorite_activities' AND meta_value LIKE %s",
			'%' . $favorite . '%'
		) );

		wp_cache_add( $activity_id, $user_favorites, 'user_favorites' );
	}

	return $user_favorites;
}

function buddyreshare_users_get_reshares( $activity_id = 0 ) {
	$user_reshares = array();

	if ( ! $activity_id ) {
		return $user_reshares;
	}

	$user_reshares = wp_cache_get( $activity_id, 'user_reshares' );

	if ( ! $user_reshares ) {
		global $wpdb;

		$table         = bp_core_get_table_prefix() . 'bp_activity_user_reshares';
		$user_reshares = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$table}
			WHERE activity_id = %d",
			$activity_id
		) );

		wp_cache_add( $activity_id, $user_reshares, 'user_reshares' );
	}

	return $user_reshares;
}

function buddyreshare_users_clean_reshares_cache( $args = array() ) {
	if ( empty( $args['user_id'] ) || empty( $args['activity_id'] ) ) {
		return;
	}

	$user_id     = (int) $args['user_id'];
	$activity_id = (int) $args['activity_id'];
	wp_cache_delete( $user_id, 'reshares_count' );
	wp_cache_delete( $activity_id, 'user_reshares' );
}
add_action( 'buddyreshare_reshare_added',   'buddyreshare_users_clean_reshares_cache', 12, 1 );
add_action( 'buddyreshare_reshare_deleted', 'buddyreshare_users_clean_reshares_cache', 12, 1 );

function buddyreshare_users_clean_favorites_cache( $activity_id = 0 ) {
	if ( ! $activity_id ) {
		return;
	}

	wp_cache_delete( $activity_id, 'user_favorites' );
}
add_action( 'bp_activity_add_user_favorite',    'buddyreshare_users_clean_favorites_cache', 12, 1 );
add_action( 'bp_activity_remove_user_favorite', 'buddyreshare_users_clean_favorites_cache', 12, 1 );

/**
 * Sanitize user favorites so that each Activity IDs are interpreted as string before serialization.
 *
 * When updating the favorites BuddyPress is changing the type of each favorited activities.
 * When adding a favorite, it's a string, after updating it's an int. We need it to be a string.
 * That's the reason why we're sanitizing it this way.
 *
 * @since 2.0.0
 *
 * @param  array  $value The list of the activity ids the user favorited.
 * @return array         The sanitized list for BP Reshare's use.
 */
function buddyreshare_sanitize_user_favorites( $value = array() ) {
	$value = (array) $value;

	return array_map( 'strval', $value );
}
add_filter( 'sanitize_user_meta_bp_favorite_activities', 'buddyreshare_sanitize_user_favorites', 10, 1 );
