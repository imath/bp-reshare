<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Loads the templates for the user's profile reshare tab
 * 
 * @package BP Reshare
 * @since    1.0
 * 
 * @uses  buddyreshare_is_user_profile_reshares() to check we're on a user's profile reshare tab
 * @uses  bp_core_load_template() to load the template
 */
function buddyreshare_screen_user_reshares() {
	if( ! buddyreshare_is_user_profile_reshares() )
		return;

	do_action( 'bpuddyreshare_screen_my_reshares' );
	bp_core_load_template( apply_filters( 'buddyreshare_screen_user_reshares', 'members/single/home' ) );
}