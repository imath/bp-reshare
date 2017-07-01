<?php
/**
 * BuddyPress Reshare plugin header
 *
 * @package   BP Reshare
 * @author    imath twitter.com/imath
 * @license   GPL-2.0+
 * @link      http://imathi.eu/2012/12/07/bp-reshare
 *
 * @wordpress-plugin
 * Plugin Name:       BP Reshare
 * Plugin URI:        http://imathi.eu/2012/12/07/bp-reshare
 * Description:       Allows members to reshare activities in a BuddyPress powered community
 * Version:           1.0
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
	 * Some init vars
	 *
	 * @since    1.0
	 *
	 * @var      array
	 */
	public static $init_vars = array(
		'reshare_id'          => 'bp_reshare',
		'reshare_slug'        => 'reshare',
		'reshare_name'        => 'Reshares',
		'bp_version_required' => '1.8.1'
	);

	/**
	 * Initialize the plugin
	 *
	 * @since    1.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
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
		$this->component_id   = self::$init_vars['reshare_id'];
		$this->component_slug = self::$init_vars['reshare_slug'];
		$this->component_name = self::$init_vars['reshare_name'];

		// Rest namespace and version.
		$this->rest = (object) array(
			'namespace' => 'bp-reshare',
			'version'   => 'v1',
		);

		// Set Cache Global groups.
		wp_cache_add_global_groups( array(
			'user_reshares',
			'reshared_notifications',
		) );
	}

	/**
	 * Checks BuddyPress version
	 *
	 * @since    1.0
	 */
	public static function buddypress_version_check() {
		// taking no risk
		if( !defined( 'BP_VERSION' ) )
			return false;

		return version_compare( BP_VERSION, self::$init_vars['bp_version_required'], '>=' );
	}

	/**
	 * Checks if current blog is the one where is activated BuddyPress
	 *
	 * @since    1.0
	 */
	public static function buddypress_site_check() {
		global $blog_id;

		if( !function_exists( 'bp_get_root_blog_id' ) )
			return false;

		if( $blog_id != bp_get_root_blog_id() )
			return false;

		return true;
	}

	/**
	 * Includes the needed files
	 *
	 * @since    1.0
	 *
	 * @uses  is_admin() to include the administration screens of the plugin if needed
	 */
	private function includes() {
		require( $this->includes_dir . 'functions.php' );

		if ( bp_is_root_blog() ) {
			require( $this->includes_dir . 'users.php' );
			require( $this->includes_dir . 'filters.php' );

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

		if( is_admin() ) {
			require( $this->includes_dir . 'admin.php' );
		}
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 *
	 * @package BP Reshare
	 * @since   1.0
	 *
	 * @uses   bp_is_active() to only load the component if the Activity component is avalaible
	 */
	private function setup_hooks() {
		// Bail if BuddyPress version is not supported or current blog is not the one where BuddyPress is activated
		if ( ! self::buddypress_version_check() || ! self::buddypress_site_check() ) {
			return;
		}

		// loads the languages..
		add_action( 'bp_init', array( $this, 'load_textdomain' ), 6 );

		// Register/enqueue scripts
		add_action( 'bp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		wp_register_script(
			'bp-reshare-request',
			$this->js_url . 'request.js',
			array(),
			$this->version,
			true
		);

		wp_register_style(
			'bp-reshare-style',
			$this->css_url . 'style.css',
			array(),
			$this->version,
			'all'
		);

		$script_data = array(
			'params' => array(
				'root_url' => esc_url_raw( rest_url( trailingslashit( $this->rest->namespace . '/' . $this->rest->version ) ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'u'        => get_current_user_id(),
			),
		);

		if ( bp_is_activity_component() || bp_is_group_activity() ) {
			wp_enqueue_script(
				'bp-reshare',
				$this->js_url . 'script.js',
				array( 'bp-reshare-request', 'jquery' ),
				$this->version,
				true
			);

			wp_enqueue_style( 'bp-reshare-style' );

			if ( ! empty( $script_data['params']['u'] ) ) {
				$user_domain       = bp_core_get_user_domain( $script_data['params']['u'] );
				$user_domain_path  = parse_url( $user_domain, PHP_URL_PATH );
				$user_domain_array = explode( '/', rtrim( $user_domain_path, '/' ) );
				$user_nicename     = end( $user_domain_array );
				$root_members      = str_replace( $user_nicename, '', rtrim( $user_domain, '/' ) );

				$script_data['params'] = array_merge( $script_data['params'], array(
					'root_members' => $root_members,
					'u_nicename'   => $user_nicename,
					'time_since'   => buddyreshare_get_l10n_time_since(),
				) );
			}

			$reshare_url = trailingslashit( bp_get_root_domain() ) .  bp_get_activity_root_slug() . '/' . buddyreshare_get_component_slug();

			$script_data = array_merge( $script_data, array(
				'template' => '<a href="%l" class="bp-reshare button bp-secondary-action %r" data-activity-id="%a" data-author-name="%u">
					<span class="bp-reshare-icon"></span>
					<span class="bp-screen-reader-text">%t</span>
					<span class="count">%c</span>
				</a>',
				'strings'  => array(
					'addReshare'    => __( 'Reshare this activity', 'bp-reshare' ),
					'removeReshare' => __( 'Remove the Reshare of this activity', 'bp-reshare' ),
					'removeLink'    => esc_url_raw( wp_nonce_url( $reshare_url . '/delete/%i/' , 'buddyreshare_delete' ) ),
					'addLink'       => esc_url_raw( wp_nonce_url( $reshare_url . '/add/%i/' , 'buddyreshare_update' ) ),
				),
			) );
		}

		wp_localize_script( 'bp-reshare-request', 'bpReshare', $script_data );
	}

	/**
	 * Loads the component
	 *
	 * @since   1.0
	 * @deprecated 2.0.0
	 */
	public function load_component() {}

	/**
	 * Enqueues the js and css files only if BP Reshare needs it
	 *
	 * @since   1.0
	 * @deprecated 2.0.0
	 */
	public function cssjs() {}

	/**
	 * The theme can override plugin's css
	 *
	 * @since    1.0
	 * @deprecated 2.0.0
	 */
	public function css_datas() {}

	/**
	 * Loads the translation files
	 *
	 * @since 1.0
	 * @since 2.0.0 Uses load_plugin_textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( $this->domain, false, trailingslashit( basename( $this->plugin_dir ) ) . 'languages' );
	}
}

endif;

// Let's start !
function buddyreshare() {
	return BuddyReshare::get_instance();
}
// Not too early and not too late ! 9 seems ok ;)
add_action( 'bp_include', 'buddyreshare', 9 );
