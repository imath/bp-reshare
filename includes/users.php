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
