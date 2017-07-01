<?php
/**
 * Deprecated functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns plugin's includes url
 *
 * @since   1.0
 * @deprecated 2.0.0
 *
 * @uses buddyreshare() plugin's main instance
 */
function buddyreshare_get_includes_url() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns plugin's img url
 *
 * @since   1.0
 * @deprecated 2.0.0
 */
function buddyreshare_get_img_url() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns plugin's js vars
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_js_vars() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Are we on a the current user's profile reshare tab
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_is_my_profile_reshares() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Builds the argument of the reshared activity
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_prepare_reshare( $activity_id = 0 ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * In case of a reshare delete, reset some activity metas
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_reset_metas( $activity_id = 0, $user_id = 0 ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns the reshared css class in case an activity has been reshared by the user
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_get_class( $activity = null, $activity_first_id = 0 ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Returns the total reshares for a given user
 *
 * @package BP Reshare
 * @since    1.0
 * @deprecated 2.0.0
 *
 * @param  integer $user_id The user ID to get the number of reshares of.
 * @return integer the number of reshares of the user
 */
function buddyreshare_get_total_reshares_count( $user_id = 0 ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'buddyreshare_users_reshares_count()' );
	return buddyreshare_users_reshares_count( $user_id );
}
