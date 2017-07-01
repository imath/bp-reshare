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

function buddyreshare_users_clean_cache( $args = array() ) {
	if ( empty( $args['user_id'] ) ) {
		return;
	}

	$user_id = (int) $args['user_id'];
	wp_cache_delete( $user_id, 'reshares_count' );
}
add_action( 'buddyreshare_reshare_added',   'buddyreshare_users_clean_cache', 12, 1 );
add_action( 'buddyreshare_reshare_deleted', 'buddyreshare_users_clean_cache', 12, 1 );
