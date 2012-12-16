<?php
/* 
Plugin Name: BP reshare
Plugin URI: http://imath.owni.fr/2012/12/07/bp-reshare
Description: BuddyPress component to reshare activities
Version: 1.0-beta4
Author: imath
Author URI: http://imath.owni.fr
License: GPLv2
Network: true
Text Domain: bp-reshare
Domain Path: /languages/
*/

/* définition des constantes */
define ( 'BP_RESHARE_SLUG', 'reshare' );
define ( 'BP_RESHARE_PLUGIN_NAME', 'bp-reshare' );
define ( 'BP_RESHARE_PLUGIN_URL',  plugins_url('' , __FILE__) );
define ( 'BP_RESHARE_PLUGIN_URL_JS',  plugins_url('js' , __FILE__) );
define ( 'BP_RESHARE_PLUGIN_URL_CSS',  plugins_url('css' , __FILE__) );
define ( 'BP_RESHARE_PLUGIN_URL_IMG',  plugins_url('images' , __FILE__) );
define ( 'BP_RESHARE_PLUGIN_DIR',  WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );
define ( 'BP_RESHARE_PLUGIN_VERSION', '1.0-beta4');

add_action('bp_include', 'bp_reshare_init');

function bp_reshare_init() {
	global $bp;
	
	require( BP_RESHARE_PLUGIN_DIR . '/includes/bp-reshare-component.php' );
	
	$bp_reshare = new BP_Reshare;
	
}

function bp_reshare_install(){
	if( !get_option( 'bp-reshare-version' ) || "" == get_option( 'bp-reshare-version' ) || BP_RESHARE_PLUGIN_VERSION != get_option( 'bp-reshare-version' ) ){
		
		update_option( 'bp-reshare-js-trick-one', 1);
		update_option( 'bp-reshare-js-trick-two', 1);
		update_option( 'bp-reshare-version', BP_RESHARE_PLUGIN_VERSION );
	}
}

register_activation_hook( __FILE__, 'bp_reshare_install' );

/**
* bp_reshare_load_textdomain
* translation!
* 
*/
function bp_reshare_load_textdomain() {

	// try to get locale
	$locale = apply_filters( 'bp_checkins_load_textdomain_get_locale', get_locale() );

	// if we found a locale, try to load .mo file
	if ( !empty( $locale ) ) {
		// default .mo file path
		$mofile_default = sprintf( '%s/languages/%s-%s.mo', BP_RESHARE_PLUGIN_DIR, BP_RESHARE_PLUGIN_NAME, $locale );
		// final filtered file path
		$mofile = apply_filters( 'bp_checkins_load_textdomain_mofile', $mofile_default );
		// make sure file exists, and load it
		if ( file_exists( $mofile ) ) {
			load_textdomain( BP_RESHARE_PLUGIN_NAME, $mofile );
		}
	}
}
add_action ( 'init', 'bp_reshare_load_textdomain', 8 );