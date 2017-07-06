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
 * Builds an array of the reshare activity actions
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_reshare_types() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Can this activity be reshared
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_can_reshare( $activity = null ) {
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
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_get_total_reshares_count( $user_id = 0 ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'buddyreshare_users_reshares_count()' );
	return buddyreshare_users_reshares_count( $user_id );
}

/**
 * Loads the templates for the user's profile reshare tab
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_screen_user_reshares() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'buddyreshare_users_reshared_screen()' );
	return buddyreshare_users_reshared_screen();
}

/**
 * Catches an activity to reshare if js is enabled
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_handle_ajax_reshare() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Catches an activity to delete if js is enabled
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_ajax_delete_reshare() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Can this activity be reshared ?
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_can_reshare() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the number of reshared
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_reshares_count() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * How much time activity has been reshared ?
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_get_reshares_count() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the reshare button class
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_button_class() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Gets the reshare button class
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_get_button_class() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the activity id to reshare
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_id_to_reshare() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Gets the activity id to reshare
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_get_id_to_reshare() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the reshare action url
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_action_url() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Builds the reshare action url
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_get_action_url() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the reshare button title
 *
 * @package BP Reshare
 * @since    1.0
 *
 * @uses buddyreshare_activity_get_button_title() to get it
 */
function buddyreshare_activity_button_title() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Builds the reshare button title
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_get_button_title(){
	_deprecated_function( __FUNCTION__, '2.0.0' );;
}

/**
 * Can this reshared activity be deleted ?
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_can_unshare() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Displays the reshare button
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_button() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Builds the reshare button
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_get_button(){
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Attach to the activities template the reshare datas
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_extend_activities_template( $has_activities = false, $activities_template = null, $template_args = array() ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Catches the activity query string to eventually edit it
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_querystring_filter( $query_string, $object ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Trick to order the most reshared DESC
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_order_by_most_reshared( $sql = '', $select_sql = '', $from_sql = '', $where_sql = '', $sort = '', $pag_sql = '' ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Replaces the activity delete link in case of a reshare type
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_maybe_replace_delete_link( $delete_link = '' ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Filters the reshare class if the delete action is possible
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_filter_button_class( $classes = array() ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Filters the reshare button title if the delete action is possible
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_filter_button_title( $title = '' ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Filters the reshare action url if the delete action is possible
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_activity_filter_action_url( $action_url = '' ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

if ( ! class_exists( 'BP_Reshare' ) ) :
/**
 * Main BP Reshare Component Class
 *
 * @since 1.0
 * @deprecated 2.0.0
 */
class BP_Reshare extends BP_Component {

	/**
	 * Constructor method
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	function __construct() {
		_deprecated_constructor( __METHOD__, '2.0.0', 'BP_Component' );
	}

	/**
	 * Sets some global for the component
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function setup_globals( $args = array() ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Includes the needed files
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function includes( $includes = array() ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Sets some key hooks for the componet
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function setup_hooks() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Activity directory : "My Reshare tab"
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function setup_activity_tab() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Builds a new BuddyPress subnav for the settings component
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function setup_activity_bp_nav() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Builds a new user sub menu for the settings component in WP Admin Bar
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function setup_activity_wp_nav() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Registers the plugin's activity actions
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function register_activity_actions() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Displays the filters in Activity filter boxes
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function activity_option() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
}

endif;

/**
 * Finally Loads the component into the main BuddyPress instance
 *
 * @since 1.0
 * @deprecated 2.0.0
 */
function buddyreshare_component() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

if ( ! class_exists( 'BuddyReshare_Admin' ) ) :
/**
 * Loads BP Reshare plugin admin area
 *
 * @since 1.0
 * @deprecated 2.0.0
 */
class BuddyReshare_Admin {

	/**
	 * The notice hook depending on config (multisite or not)
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 * @var string
	 */
	public $notice_hook = '';

	/**
	 * The constructor
	 *
   * @since 1.0
   * @deprecated 2.0.0
	 */
	public function __construct() {
		_deprecated_constructor( __METHOD__, '2.0.0' );
	}

	/**
	 * Admin globals
	 *
   * @since 1.0
	 * @deprecated 2.0.0
	 */
	private function setup_globals() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Setup the admin hooks, actions and filters
	 *
   * @since version 1.0
   * @deprecated 2.0.0
	 */
	private function setup_actions() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Prints a warning notice if the Community administrator activated the plugin on the wrong site
	 *
   * @since 1.0
   * @deprecated 2.0.0
	 */
	public function warning_notice() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Displays a warning if BuddyPress version is outdated for the plugin
	 *
   * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function activation_notice() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Builds the settings fields for the plugin
	 *
   * @since 1.0
   * @deprecated 2.0.0
	 */
	public function register_settings() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Display function for the number of users to display for a single activity
	 *
   * @since 1.0
   * @deprecated 2.0.0 There is no more restrictions of the amount of users.
	 */
	public function reshare_list() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Display function for the allowed activity types
	 *
   * @since 1.0
   * @deprecated 2.0.0 It's now an option to disable instead of enable.
	 */
	public function reshare_types() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Sanitize function for the allowed activity types
	 *
   * @since 1.0
   * @deprecated 2.0.0 This option is no more used.
	 */
	public function reshare_types_sanitize( $option = false ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}
}

endif;

/**
 * Launches the admin
 *
 * @since 1.0
 * @deprecated 2.0.0
 */
function buddyreshare_admin() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Catches an activity to reshare if js is disabled
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_add_reshare() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'buddyreshare_activity_add_reshare()' );
	return buddyreshare_activity_add_reshare();
}

/**
 * Catches a reshare to delete if js is disabled
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_remove_reshare() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'buddyreshare_activity_remove_reshare()' );
	return buddyreshare_activity_remove_reshare();
}

/**
 * Increment user's reshare count
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddreshare_increment_user_count( $reshare_id = 0, $user_id = 0 ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Decrement user's reshare count
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddreshare_decrement_user_count( $reshare_id = 0, $user_id = 0 ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * List the users that have reshared an activity
 *
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_list_user_avatars() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Hook to only load the user's list if on a single activity
 * 
 * @since    1.0
 * @deprecated 2.0.0
 */
function buddyreshare_reshared_by_list() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}
