<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BP_Reshare' ) ) :
/**
 * Main BP Reshare Component Class
 */
class BP_Reshare extends BP_Component {

	/**
	 * Constructor method
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 *
	 * @uses buddyreshare_get_component_id() to get the id of the component
	 * @uses buddyreshare_get_component_name() to get the name of the component
	 * @uses buddyreshare_get_includes_dir() to get plugin's include dir
	 * @uses buddypress() to get BuddyPress main instance
	 */
	function __construct() {

		parent::start(
			buddyreshare_get_component_id(),
			buddyreshare_get_component_name(),
			buddyreshare_get_includes_dir()
		);

		buddypress()->active_components[$this->id] = '1';

	 	$this->includes();
	 	$this->setup_hooks();
	}

	/**
	 * Sets some global for the component
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 *
	 * @param $args array
	 * @uses buddyreshare_get_component_slug() to get the slug of the component
	 * @uses buddypress() to get BuddyPress main instance
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		$args = array(
			'slug'                  => buddyreshare_get_component_slug(),
			'has_directory'         => false,
			'notification_callback' => 'buddyreshare_format_notifications',
		);

		parent::setup_globals( $args );
	}

	/**
	 * Includes the needed files
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 *
	 * @param $args array
	 */
	public function includes( $includes = array() ) {

		// Files to include
		$includes = array(
			'functions.php',
			'filters.php',
			'actions.php',
			'screens.php',
			'templates.php',
			'ajax.php'
		);

		parent::includes( $includes );

	}

	/**
	 * Sets some key hooks for the componet
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 */
	public function setup_hooks() {
		// BuddyPress Actions
		add_action( 'bp_before_activity_type_tab_friends',          array( $this, 'setup_activity_tab'         ) );
		add_action( 'bp_activity_setup_nav',                        array( $this, 'setup_activity_bp_nav'      ) );
		add_action( 'bp_activity_setup_admin_bar',                  array( $this, 'setup_activity_wp_nav'      ) );
		add_action( 'bp_activity_entry_meta',                       'buddyreshare_activity_button'                );
		add_action( 'bp_register_activity_actions',                 array( $this, 'register_activity_actions'  ) );
		add_action( 'bp_activity_filter_options',                   array( $this, 'activity_option' ),        14 );
		add_action( 'bp_member_activity_filter_options',            array( $this, 'activity_option' ),        14 );
		add_action( 'bp_group_activity_filter_options',             array( $this, 'activity_option' ),        14 );
		add_action( 'bp_activity_delete',                           'buddyreshare_handle_activity_delete',  10, 1 );
		add_action( 'bp_actions',                                   'buddyreshare_add_reshare'                    );
		add_action( 'bp_actions',                                   'buddyreshare_remove_reshare'                 );
		add_action( 'bp_activity_screen_single_activity_permalink', 'buddyreshare_reshared_by_list'               );

		// Self Actions
		add_action( 'buddyreshare_reshare_added',   'buddreshare_increment_user_count' );
		add_action( 'buddyreshare_reshare_deleted', 'buddreshare_decrement_user_count' );

		// BuddyPress Filters
		add_filter( 'bp_has_activities',                'buddyreshare_extend_activities_template',  10, 3 );
		add_filter( 'bp_ajax_querystring',              'buddyreshare_activity_querystring_filter', 12, 2 );
		add_filter( 'bp_activity_paged_activities_sql', 'buddyreshare_order_by_most_reshared',      10, 2 );
		add_filter( 'bp_get_activity_delete_link',      'buddyreshare_maybe_replace_delete_link',   10, 1 );

		// Self filters
		add_filter( 'buddyreshare_activity_get_button_class', 'buddyreshare_activity_filter_button_class',   1, 1 );
		add_filter( 'buddyreshare_activity_get_button_title', 'buddyreshare_activity_filter_button_title',   1, 1 );
		add_filter( 'buddyreshare_activity_get_action_url',   'buddyreshare_activity_filter_action_url',     1, 1 );
	}

	/**
	 * Activity directory : "My Reshare tab"
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 *
	 * @uses  buddyreshare_get_total_reshares_count() to get the number of reshared for the loggedin user
	 * @uses  bp_loggedin_user_id() to get current user's id
	 * @uses  bp_loggedin_user_domain() to get current user's profile url
	 * @uses  bp_get_activity_slug() to get Activity slug
	 * @uses  buddyreshare_get_component_slug() to get the slug of the component
	 */
	public function setup_activity_tab() {
		if ( buddyreshare_get_total_reshares_count( bp_loggedin_user_id() ) ) : ?>

			<li id="activity-reshares"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/'. buddyreshare_get_component_slug() .'/'; ?>" title="<?php _e( 'Activities i reshared.', 'bp-reshare' ); ?>"><?php printf( __( '%s <span>%s</span>', 'bp-reshare' ), __( 'My Reshares', 'bp-reshares' ), buddyreshare_get_total_reshares_count( bp_loggedin_user_id() ) ); ?></a></li>

		<?php endif;
	}

	/**
	 * Builds a new BuddyPress subnav for the settings component
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 *
	 * @uses  bp_displayed_user_id() to get displayed user's id
	 * @uses  bp_displayed_user_domain() to get displayed user's profile url
	 * @uses  bp_loggedin_user_domain() to get current user's profile url
	 * @uses  bp_get_activity_slug() to get Activity slug
	 */
	public function setup_activity_bp_nav() {
		$link = bp_displayed_user_id() ? bp_displayed_user_domain() : bp_loggedin_user_domain();

		bp_core_new_subnav_item( array(
			'name' 		      => $this->name,
			'slug' 		      => buddyreshare_get_component_slug(),
			'parent_slug'     => bp_get_activity_slug(),
			'parent_url' 	  => trailingslashit( $link . bp_get_activity_slug() ),
			'css_id'          => 'activity-reshares',
			'user_has_access' => true,
			'screen_function' => 'buddyreshare_screen_user_reshares',
			'position' 	      => 40
		) );
	}

	/**
	 * Builds a new user sub menu for the settings component in WP Admin Bar
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 *
	 * @global $wp_admin_bar object
	 * @uses  buddyreshare_get_component_slug() to get the slug of the component
	 * @uses  bp_loggedin_user_domain() to get the loggedin user profil url
	 * @uses  bp_get_activity_slug() to get Activity slug
	 */
	public function setup_activity_wp_nav() {
		global $wp_admin_bar;

		$activity_menu = array(
			'parent' => 'my-account-activity',
			'id'     => 'my-account-activity-' . buddyreshare_get_component_slug(),
			'title'  => $this->name,
			'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . buddyreshare_get_component_slug() ),
		);

		$wp_admin_bar->add_menu( $activity_menu );
	}

	/**
	 * Registers the plugin's activity actions
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 *
	 * @uses  bp_is_active() to check Activity component is on
	 * @uses  bp_activity_set_action() to set the components activity actions
	 * @uses  is_admin() to check for administration screens
	 */
	public function register_activity_actions() {
		// Bail if activity is not active
		if ( ! bp_is_active( 'activity' ) )
			return false;

		bp_activity_set_action( $this->id, 'reshare_update',        __( 'Reshares', 'bp-reshare' ) );

		if( !is_admin() )
			bp_activity_set_action( $this->id, 'activity_mostreshared', __( 'Most Reshared', 'bp-reshare' ) );

		do_action( 'buddyreshare_register_activity_actions' );
	}

	/**
	 * Displays the filters in Activity filter boxes
	 *
	 * @package BP Reshare
	 * @subpackage Component
	 * @since 1.0
	 *
	 * @uses  buddyreshare_reshare_types() to get the reshare activity actions
	 * @uses  esc_attr() to sanitize output
	 */
	public function activity_option() {
		$types = buddyreshare_reshare_types();

		if( empty( $types ) )
			return;

		foreach( $types as $type ):?>
			<option value="<?php echo esc_attr( $type['key'] );?>"><?php echo esc_attr( $type['value'] ); ?></option>
		<?php endforeach;
	}


}

/**
 * Finally Loads the component into the main BuddyPress instance
 *
 * @uses buddypress()
 * @uses buddyreshare_get_component_id()
 */
function buddyreshare_component() {
	buddypress()->{buddyreshare_get_component_id()} = new BP_Reshare;
}
add_action( 'bp_loaded', 'buddyreshare_component', 11 );

endif;
