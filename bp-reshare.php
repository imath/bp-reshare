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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BuddyReshare' ) ) :
/**
 * Main BP Reshare Class
 *
 * @since BP Reshare (1.0)
 */
class BuddyReshare {
	/**
	 * Instance of this class.
	 *
	 * @package BP Reshare
	 * @since    1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Some init vars
	 *
	 * @package BP Reshare
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
	 * @package BP Reshare
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
	 * @package BP Reshare
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
	 * @package BP Reshare
	 * @since    1.0
	 */
	private function setup_globals() {
		/** BP Reshare globals ********************************************/
		$this->version                = '1.0';
		$this->domain                 = 'bp-reshare';
		$this->file                   = __FILE__;
		$this->basename               = plugin_basename( $this->file );
		$this->plugin_dir             = plugin_dir_path( $this->file );
		$this->plugin_url             = plugin_dir_url( $this->file );
		$this->lang_dir               = trailingslashit( $this->plugin_dir . 'languages' );
		$this->includes_dir           = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url           = trailingslashit( $this->plugin_url . 'includes' );
		$this->plugin_js              = trailingslashit( $this->includes_url . 'js' );
		$this->plugin_css             = trailingslashit( $this->includes_url . 'css' );
		$this->plugin_img             = trailingslashit( $this->includes_url . 'images' );

		/** Component specific globals ********************************************/
		$this->component_id                     = self::$init_vars['reshare_id'];
		$this->component_slug                   = self::$init_vars['reshare_slug'];
		$this->component_name                   = self::$init_vars['reshare_name'];

	}

	/**
	 * Checks BuddyPress version
	 * 
	 * @package BP Reshare
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
	 * @package BP Reshare
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
	 * @package BP Reshare
	 * @since    1.0
	 *
	 * @uses  is_admin() to include the administration screens of the plugin if needed
	 */
	private function includes() {
		require( $this->includes_dir . 'helpers.php' );

		if( is_admin() )
			require( $this->includes_dir . 'admin.php' );
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
		if( ! self::buddypress_version_check() || ! self::buddypress_site_check() )
			return;

		//Actions
		// loads the languages..
		add_action( 'bp_init',            array( $this, 'load_textdomain' ), 6 );
		add_action( 'bp_enqueue_scripts', array( $this, 'cssjs'           )    );

		// Loading the main component after version and site check
		if( bp_is_active( 'activity' ) )
			add_action( 'bp_include',     array( $this, 'load_component'  )    );
	}

	/**
	 * Loads the component
	 * 
	 * @package BP Reshare
	 * @since   1.0
	 */
	public function load_component() {
		require( $this->includes_dir . 'component.php' );
	}

	/**
	 * Enqueues the js and css files only if BP Reshare needs it
	 * 
	 * @package BP Reshare
	 * @since   1.0
	 * 
	 * @uses bp_is_active() to check if the plugin's component is active
	 * @uses bp_is_activity_component() to check if we are in the Activity component area
	 * @uses bp_is_group_home() to check if we are in a group home
	 * @uses wp_enqueue_style() to safely add our style to WordPress queue
	 * @uses wp_enqueue_script() to safely add our script to WordPress queue
	 * @uses wp_localize_script() to attach some vars to it
	 * @uses buddyreshare_js_vars() to get the js vars for the plugin
	 */
	public function cssjs() {

		if( ! bp_is_active( $this->component_id ) )
			return;

		if( bp_is_activity_component() || ( bp_is_active( 'groups' ) && bp_is_group_home() ) ) {

			// CSS is Theme's territory, so let's help him to easily override our css.
			$css_datas = (array) $this->css_datas();

			wp_enqueue_style( $css_datas['handle'], $css_datas['location'], false, $this->version );
			wp_enqueue_script( 'bp-reshare-js', $this->plugin_js . 'reshare.js', array( 'jquery' ), $this->version, true );
			wp_localize_script( 'bp-reshare-js', 'bp_reshare_vars', buddyreshare_js_vars() );
		}
		
	}

	/**
	 * The theme can override plugin's css
	 * 
	 * @package BP Reshare
	 * @since    1.0
	 *
	 * @uses get_stylesheet_directory()
	 * @uses get_stylesheet_directory_uri()
	 * @uses get_template_directory()
	 * @uses get_template_directory_uri()
	 */
	public function css_datas() {
		$file = 'css/reshare.css';
		
		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() ) . $file ; 
			$handle   = 'bp-reshare-child-css';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() ) . $file ;
			$handle   = 'bp-reshare-parent-css';

		// use our style
		} else {
			$location = $this->includes_url . $file;
			$handle   = 'bp-reshare-css';
		}

		return array( 'handle' => $handle, 'location' => $location );
	}

	/**
	 * Loads the translation files
	 *
	 * @package BP Reshare
	 * @since    1.0
	 * 
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_texdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/bp-reshare/' . $mofile;

		// Look in global /wp-content/languages/bp-reshare folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/bp-reshare/languages/ folder
		load_textdomain( $this->domain, $mofile_local );
	}

	
}

// Let's start !
function buddyreshare() {
	return BuddyReshare::get_instance();
}
// Not too early and not too late ! 9 seems ok ;)
add_action( 'bp_include', 'buddyreshare', 9 );

endif;

