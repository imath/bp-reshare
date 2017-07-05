<?php
/**
 * BuddyPress Reshare plugin header
 *
 * @package   BP Reshare
 * @author    imath https://imathi.eu
 * @license   GPL-2.0+
 * @link      https://imathi.eu/tag/reshare/
 *
 * @wordpress-plugin
 * Plugin Name:       BP Reshare
 * Plugin URI:        https://imathi.eu/tag/reshare/
 * Description:       Allows members to reshare activities in a BuddyPress powered community
 * Version:           2.0.0-beta1
 * Author:            imath
 * Author URI:        http://imathi.eu
 * Text Domain:       bp-reshare
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/imath/bp-reshare
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BuddyReshare' ) ) :
/**
 * Main BP Reshare Class
 *
 * @since BP Reshare (1.0)
 */
class BuddyReshare {
	/**
	 * Instance of this class.
	 *
	 * @since    1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin
	 *
	 * @since    1.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since    1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets some globals for the plugin
	 *
	 * @since    1.0
	 */
	private function setup_globals() {
		/** BP Reshare globals ********************************************/
		$this->version      = '2.0.0-beta1';
		$this->domain       = 'bp-reshare';
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url( $this->file );
		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->js_url       = trailingslashit( $this->plugin_url . 'js'  );
		$this->css_url      = trailingslashit( $this->plugin_url . 'css' );

		/** Component specific globals ********************************************/
		$this->component_id   = 'bp_reshare';
		$this->component_slug = 'reshare';
		$this->component_name = 'Reshares';

		// Rest namespace and version.
		$this->rest = (object) array(
			'namespace' => 'bp-reshare',
			'version'   => 'v1',
		);

		// Set Cache Global groups.
		wp_cache_add_global_groups( array(
			'user_reshares',
			'user_reshared',
			'user_favorites',
			'reshares_count',
			'reshared_notifications',
		) );
	}

	/**
	 * Includes the needed files
	 *
	 * @since    1.0
	 */
	private function includes() {
		require( $this->includes_dir . 'functions.php' );

		if ( bp_is_active( 'activity' ) ) {
			require( $this->includes_dir . 'rest.php'     );
			require( $this->includes_dir . 'users.php'    );
			require( $this->includes_dir . 'activity.php' );

			if ( buddyreshare_are_emails_active() ) {
				require( $this->includes_dir . 'emails.php' );
			}

			if ( bp_is_active( 'notifications' ) ) {
				require( $this->includes_dir . 'notifications.php' );
			}

			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
				require( $this->includes_dir . 'deprecated.php' );
			}
		}

		if ( is_admin() ) {
			require( $this->includes_dir . 'settings.php' );
			require( $this->includes_dir . 'upgrade.php' );
		}
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 *
	 * @since   1.0
	 * @deprecated 2.0.0
	 */
	private function setup_hooks() {
		_deprecated_function( __FUNCTION__, '2.0.0' );
	}

	/**
	 * Loads the component
	 *
	 * @since   1.0
	 * @deprecated 2.0.0
	 */
	public function load_component() {
		_deprecated_function( __FUNCTION__, '2.0.0' );
	}

	/**
	 * Enqueues the js and css files only if BP Reshare needs it
	 *
	 * @since   1.0
	 * @deprecated 2.0.0
	 */
	public function cssjs() {
		_deprecated_function( __FUNCTION__, '2.0.0' );
	}

	/**
	 * The theme can override plugin's css
	 *
	 * @since    1.0
	 * @deprecated 2.0.0
	 */
	public function css_datas() {
		_deprecated_function( __FUNCTION__, '2.0.0' );
	}

	/**
	 * Loads the translation files
	 *
	 * @since 1.0
	 * @deprecated 2.0.0
	 */
	public function load_textdomain() {
		_deprecated_function( __FUNCTION__, '2.0.0' );
	}
}

endif;

// Let's start !
function buddyreshare() {
	return BuddyReshare::get_instance();
}
// Not too early and not too late ! 9 seems ok ;)
add_action( 'bp_include', 'buddyreshare', 9 );
