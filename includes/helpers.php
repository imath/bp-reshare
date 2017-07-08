<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* helpers */

/**
 * Returns plugin version
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_plugin_version() {
	return buddyreshare()->version;
}

/**
 * Returns plugin's dir
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_plugin_dir() {
	return buddyreshare()->plugin_dir;
}

/**
 * Returns plugin's includes dir
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_includes_dir() {
	return buddyreshare()->includes_dir;
}

/**
 * Returns plugin's includes url
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_includes_url() {
	return buddyreshare()->includes_url;
}

/**
 * Returns plugin's js url
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_js_url() {
	return buddyreshare()->plugin_js;
}

/**
 * Returns plugin's css url
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_css_url() {
	return buddyreshare()->plugin_css;
}

/**
 * Returns plugin's img url
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_img_url() {
	return buddyreshare()->plugin_img;
}

/**
 * Returns plugin's component id
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_component_id() {
	return buddyreshare()->component_id;
}

/**
 * Returns plugin's component slug
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddypress() BuddyPress main instance
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_component_slug() {
	return apply_filters( 'buddyreshare_get_component_slug', buddyreshare()->component_slug );
}

function buddyreshare_component_name() {
	echo buddyreshare_get_component_name();
}

/**
 * Returns plugin's component name
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_component_name() {
	return apply_filters( 'buddyreshare_get_component_name', buddyreshare()->component_name );
}

/**
 * Returns plugin's js vars
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses    buddyreshare_get_component_slug() to get component's slug
 * @return  array
 */
function buddyreshare_js_vars() {
	return apply_filters( 'buddyreshare_js_vars', array(
		'no_reshare_text' => __( 'You have not reshared any activities yet.', 'bp-reshare' ),
		'cheating_text'   => __( 'Cheating ?!', 'bp-reshare' ),
		'reshared_text'   => __( 'Reshared', 'bp-reshare' ),
		'personal_li'     => buddyreshare_get_component_slug(),
		'my_reshares'     => __( 'My Reshares', 'bp-reshares' )
	) );
}

/**
 * Are we on a the current user's profile reshare tab
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses    bp_is_activity_component() are we in activity component
 * @uses    bp_is_my_profile() to check we're on logged in user's profile
 * @uses    bp_is_current_action() to check current action
 * @uses    buddyreshare_get_component_slug() to get component slug
 * @return  boolean true|false
 */
function buddyreshare_is_my_profile_reshares() {
	if( bp_is_activity_component() && bp_is_my_profile() && bp_is_current_action( buddyreshare_get_component_slug() ) )
		return true;

	return false;
}

/**
 * Are we on a the current user's profile reshare tab
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses    bp_is_activity_component() are we in activity component
 * @uses    bp_is_user() to check we're on a user's profile
 * @uses    bp_is_current_action() to check current action
 * @uses    buddyreshare_get_component_slug() to get component slug
 * @return  boolean true|false
 */
function buddyreshare_is_user_profile_reshares() {
	if( bp_is_activity_component() && bp_is_user() && bp_is_current_action( buddyreshare_get_component_slug() ) )
		return true;

	return false;
}

/**
 * Builds an array of the reshare activity actions
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddypress() to get BuddyPress main instance
 * @uses buddyreshare_get_component_id() to get plugin's id
 */
function buddyreshare_reshare_types() {
	$activity_types = buddypress()->activity->actions;

	$reshare_types = array();

	if( !empty( $activity_types->{buddyreshare_get_component_id()} ) )
		$reshare_types = array_values( (array) $activity_types->{buddyreshare_get_component_id()} );

	return $reshare_types;
}

/**
 * Returns the allowed activity actions
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses bp_get_option() to get the settings for allowed types
 */
function buddyreshare_activity_types() {
	$allowed_types = bp_get_option( 'buddyreshare-allowed-types', array( 'activity_update', 'reshare_update' ) );

	return apply_filters( 'buddyreshare_activity_types', $allowed_types );
}
