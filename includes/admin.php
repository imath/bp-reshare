<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BuddyReshare_Admin' ) ) :
/**
 * Loads BP Reshare plugin admin area
 * 
 * @package BP Reshare
 * @subpackage Admin
 * @since version 1.0
 */
class BuddyReshare_Admin {
	
	/**
	 * @var the notice hook depending on config (multisite or not)
	 */
	public $notice_hook = '';

	/**
	 * The constructor
	 *
	 * @package BP Reshare
 	 * @subpackage Admin
     * @since version 1.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Admin globals
	 *
	 * @package BP Reshare
 	 * @subpackage Admin
     * @since version 1.0
	 *
	 * @uses bp_core_do_network_admin() to define the best menu (network or not)
	 */
	private function setup_globals() {
		$this->notice_hook = bp_core_do_network_admin() ? 'network_admin_notices' : 'admin_notices' ;
	}

	/**
	 * Setup the admin hooks, actions and filters
	 *
	 * @package BP Reshare
 	 * @subpackage Admin
     * @since version 1.0
	 * 
	 * @uses bp_core_admin_hook() to hook the right menu (network or not)
	 * @uses buddyreshare() to get plugin's main instance
	 */
	private function setup_actions() {
		$buddyreshare = buddyreshare();

		// Current blog is not the one where BuddyPress is activated so let's warn the administrator
		if( ! $buddyreshare::buddypress_site_check() ) {
			add_action( 'admin_notices',              array( $this, 'warning_notice' )        );
		} else {
			add_action( $this->notice_hook,           array( $this, 'activation_notice' )        );
			add_action( bp_core_admin_hook(),         array( $this, 'admin_menus'       )        );
			add_action( 'bp_register_admin_settings', array( $this, 'register_settings' )        );
		}
		
	}

	/**
	 * Prints a warning notice if the Community administrator activated the plugin on the wrong site
	 *
	 * Since it's possible to activate BuddyPress on any site of the network by defining BP_ROOT_BLOG
	 * with the blog_id, we need to make sure BP Reshare is activated on the same site than BuddyPress
	 * if it's not the case, this notice will be displayed to ask the administrator to activate the 
	 * plugin on the correct blog, or on the network if it's where BuddyPress is activated.
	 *
	 * @package BP Reshare
 	 * @subpackage Admin
     * @since version 1.0
	 * 
	 * @uses is_plugin_active_for_network() to check if the plugin is activated on the network
	 * @uses buddyreshare() to get plugin's main instance
	 * @uses bp_core_do_network_admin() to check if BuddyPress has been activated on the network
	 */
	public function warning_notice() {
		if( is_plugin_active_for_network( buddyreshare()->basename ) )
			return;
		?>
		<div id="message" class="updated fade">
			<?php if( bp_core_do_network_admin() ) :?>
				<p><?php _e( 'BuddyPress is activated on the network, please deactivate BP Reshare from this site and make sure to activate BP Reshare on the network.', 'bp-reshare' );?></p>
			<?php else:?>
				<p><?php _e( 'BP Reshare has been activated on a site where BuddyPress is not, please deactivate BP Reshare from this site and activate it on the same site where BuddyPress is activated.', 'bp-reshare' );?></p>
			<?php endif;?>
		</div>
		<?php
	}

	/**
	 * Displays a warning if BuddyPress version is outdated for the plugin
	 * 
	 * @package BP Reshare
 	 * @subpackage Admin
     * @since version 1.0
	 *
	 * @uses  buddyreshare() to get plugin's main instance
	 */
	public function activation_notice() {
		$buddyreshare = buddyreshare();

		if( ! $buddyreshare::buddypress_version_check() ) {
			?>
			<div id="message" class="updated fade">
				<p><?php printf( __( 'BP Reshare requires at least <strong>BuddyPress %s</strong>, please upgrade', 'bp-reshare' ), $buddyreshare::$init_vars['bp_version_required'] );?></p>
			</div>
			<?php
		}

	}
	
	/**
	 * Stores db version
	 * 
	 * @package BP Reshare
 	 * @subpackage Admin
     * @since version 1.0
	 *
	 * @uses  buddyreshare() to get plugin's main instance
	 * @uses  bp_current_user_can() to check for user's capability
	 * @uses  buddyreshare_get_plugin_version() to get plugin's version
	 * @uses  bp_get_option() to get plugin's db version
	 * @uses  bp_update_option() to update plugin's db version
	 */
	public function admin_menus() {
		$buddyreshare = buddyreshare();

		// Bail if user cannot manage options
		if ( ! bp_current_user_can( 'manage_options' ) )
			return;

		if( $buddyreshare::buddypress_version_check() && buddyreshare_get_plugin_version() != bp_get_option( 'bp-reshare-version' ) )
			bp_update_option( 'bp-reshare-version', buddyreshare_get_plugin_version() );
	}

	/**
	 * Builds the settings fields for the plugin
	 *
	 * @package BP Reshare
 	 * @subpackage Admin
     * @since version 1.0
	 * 
	 * @uses add_settings_field() to add the fields
	 * @uses register_setting() to fianlly register the settings
	 */
	public function register_settings() {
		add_settings_field( 
	        'bp-reshare-user-amount',
	        __( 'Reshared user list', 'bp-reshare' ),
	        array( &$this, 'reshare_list' ),
	        'buddypress', 
	        'bp_activity'
    	);

    	add_settings_field( 
	        'buddyreshare-allowed-types',
	        __( '"Resharable" Activity types', 'bp-reshare' ),
	        array( &$this, 'reshare_types' ),
	        'buddypress', 
	        'bp_activity'
    	);

    	register_setting( 'buddypress', 'bp-reshare-user-amount', 'absint' );
    	register_setting( 'buddypress', 'buddyreshare-allowed-types', array( &$this, 'reshare_types_sanitize' ) );
	}

	/**
	 * Display function for the number of users to display for a single activity
	 *
	 * @package BP Reshare
     * @subpackage Admin
     * @since version 1.0
     *
     * @uses  bp_get_option() to get the stored setting
	 * @return string html output
	 */
	public function reshare_list() {
	    $max = (int)bp_get_option( 'bp-reshare-user-amount', 5 );
	    ?>
	    <input id="bp-reshare-user-amount" name="bp-reshare-user-amount" type="number" min="0" value="<?php echo $max; ?>"/>
	    <p class="description"><?php _e( 'Amount of user&#39;s avatar to show when a single activity is displayed', 'bp-reshare' ); ?></p>
	    <?php
	}

	/**
	 * Display function for the allowed activity types
	 *
	 * @package BP Reshare
     * @subpackage Admin
     * @since version 1.0
     *
     * @uses  bp_get_option() to get the stored setting
     * @uses  bp_activity_get_types() to get the available activity types
     * @uses  esc_html() to sanitize outputs
	 * @return string html output
	 */
	public function reshare_types() {
 		$activity_types = bp_activity_get_types();
 	   	$reshare_types  = bp_get_option( 'buddyreshare-allowed-types', array( 'activity_update', 'reshare_update' ) );

 	   
 	   	foreach( $activity_types as $type => $caption ) {
 	   		if( in_array( $type, array( 'activity_comment', 'friendship_created', 'friendship_accepted', 'new_avatar', 'new_member', 'created_group', 'joined_group' ) ) )
 	   			continue;
       	?> 

        <input id="buddyreshare-allowed-types-<?php echo $type;?>" name="buddyreshare-allowed-types[<?php echo $type;?>]" type="checkbox" value="1" <?php checked( in_array( $type, $reshare_types ) ); ?>>&nbsp;
        <label for="buddyreshare-allowed-types-<?php echo $type;?>"><?php echo esc_html( $caption ) ?></label> <br/>

       	<?php
       	} 
	}

	/**
	 * Sanitize function for the allowed activity types
	 *
	 * @package BP Reshare
     * @subpackage Admin
     * @since version 1.0
     *
	 * @return array the allowed activity types
	 */
	public function reshare_types_sanitize( $option = false ) {
		$option = !is_array( $option ) ? array() : array_keys( $option );
		$option = array_merge( $option, array( 'reshare_update' ) );
 		return $option;
	}
	
}

/**
 * Launches the admin
 * 
 * @package BP Reshare
 * @subpackage Admin
 * @since version 1.0
 * 
 * @uses buddyreshare()
 */
function buddyreshare_admin() {
	buddyreshare()->admin = new BuddyReshare_Admin();
}

add_action( 'bp_loaded', 'buddyreshare_admin' );

endif;