<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;
	
/**
* Main reshare class
*/
class BP_Reshare
{
	
	function __construct()
	{
		$this->includes();
		
		add_action( 'bp_ready', array ( $this, 'setup_nav') , 10 );
		add_action( 'bp_activity_setup_admin_bar', array ( $this, 'setup_admin_bar') , 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ), 10 );
	}
	
	function includes() {
		
		require( BP_RESHARE_PLUGIN_DIR . '/includes/bp-reshare-functions.php' );
		require( BP_RESHARE_PLUGIN_DIR . '/includes/bp-reshare-actions.php' );
		require( BP_RESHARE_PLUGIN_DIR . '/includes/bp-reshare-screens.php' );
		require( BP_RESHARE_PLUGIN_DIR . '/includes/bp-reshare-ajax.php' );
		require( BP_RESHARE_PLUGIN_DIR . '/includes/bp-reshare-filters.php' );
		
		if( is_admin() )
			require( BP_RESHARE_PLUGIN_DIR . '/includes/bp-reshare-admin.php' );
		
	}
	
	function setup_nav() {
		global $bp;
		
		$link = bp_displayed_user_id() ? bp_displayed_user_domain() : bp_loggedin_user_domain();

		$reshare_nav = array( 'name'            => __( 'Reshares', 'bp-reshare'), 
							  'link'            => $link . bp_get_activity_slug() . '/reshares/', 
							  'slug'            => 'reshares', 
							  'css_id'          => 'activity-reshares', 
							  'user_has_access' => true, 
							  'position'        => 50,
							  'screen_function' => false );
		
		$bp->bp_options_nav['activity']['reshares'] = $reshare_nav;

	}
	
	function setup_admin_bar() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu( array( 'parent' => 'my-account-'. bp_get_activity_slug(), 
										'title'  => __('Reshares','bp-reshare'), 
										'href'   => bp_loggedin_user_domain() . bp_get_activity_slug() . '/reshares/' ) );
		
	}
	
	function register_plugin_styles() {
		
		if( bp_is_activity_component() || bp_is_group_home() )
			wp_enqueue_style('bp-reshare-css', BP_RESHARE_PLUGIN_URL_CSS .'/reshare.css');
			
	}
	
	function register_plugin_scripts() {
		
		if( bp_is_activity_component() || bp_is_group_home() ) {
			wp_enqueue_script('bp-reshare-js', BP_RESHARE_PLUGIN_URL_JS .'/reshare.js', array('jquery'), 0, 1);
			BP_Reshare::localize_script();
		}
			
	}
	
	function localize_script() {
		
		wp_localize_script('bp-reshare-js', 'bp_reshare_vars', array(
			'use_js_trick_one' => bp_get_option( 'bp-reshare-js-trick-one' ),
			'use_js_trick_two' => bp_get_option( 'bp-reshare-js-trick-two' ),
			'filter_text'      => __(' Stuck ? <a href="#" id="launch-filter">Click here to add a yellow background to the filter</a>', 'bp-reshare'),
			'no_reshare_text'  => __('You did not reshared any activities so far.', 'bp-reshare')
			)
		);
		
	}
}
