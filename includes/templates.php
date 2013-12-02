<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Can this activity be reshared ?
 *
 * @package BP Reshare
 * @since    1.0
 * 
 * @global  BP_Activity_Template $activities_template
 * @return  boolean true|false
 */
function buddyreshare_activity_can_reshare() {
	global $activities_template;

	return apply_filters( 'buddyreshare_activity_can_reshare', (bool) $activities_template->activity->reshares->can_reshare );
}

/**
 * Displays the number of reshared
 *
 * @package BP Reshare
 * @since    1.0
 * 
 * @uses buddyreshare_activity_get_reshares_count() to get it
 */
function buddyreshare_activity_reshares_count() {
	echo buddyreshare_activity_get_reshares_count();
}
	
	/**
	 * How much time activity has been reshared ?
	 *
	 * @package BP Reshare
	 * @since    1.0
	 * 
	 * @global  BP_Activity_Template $activities_template
	 * @return  integer number of reshares
	 */
	function buddyreshare_activity_get_reshares_count() {
		global $activities_template;

		return apply_filters( 'buddyreshare_activity_reshares_count', intval( $activities_template->activity->reshares->count ) );
	}

/**
 * Displays the reshare button class
 *
 * @package BP Reshare
 * @since    1.0
 * 
 * @uses buddyreshare_activity_get_button_class() to get it
 */
function buddyreshare_activity_button_class() {
	echo buddyreshare_activity_get_button_class();
}
	
	/**
	 * Gets the reshare button class
	 *
	 * @package BP Reshare
	 * @since    1.0
	 * 
	 * @global  BP_Activity_Template $activities_template
	 * @return  string the list of classes
	 */
	function buddyreshare_activity_get_button_class() {
		global $activities_template;

		$classes = apply_filters( 'buddyreshare_activity_get_button_class', array( 
			'bp-reshare-img',
			$activities_template->activity->reshares->css_class
		) );
		$classes = array_merge( $classes, array() );

		return join( ' ', $classes );
	}

/**
 * Displays the activity id to reshare
 *
 * @package BP Reshare
 * @since    1.0
 * 
 * @uses buddyreshare_activity_get_id_to_reshare() to get it
 */
function buddyreshare_activity_id_to_reshare() {
	echo buddyreshare_activity_get_id_to_reshare();
}

	/**
	 * Gets the activity id to reshare
	 *
	 * @package BP Reshare
	 * @since    1.0
	 * 
	 * @global  BP_Activity_Template $activities_template
	 * @return  integer the activity id
	 */
	function buddyreshare_activity_get_id_to_reshare() {
		global $activities_template;

		$to_reshare = !empty( $activities_template->activity->reshares->activity_id ) ? $activities_template->activity->reshares->activity_id : 0;
		return apply_filters( 'buddyreshare_activity_get_id_to_reshare', intval( $to_reshare ) );
	}

/**
 * Displays the reshare action url
 *
 * @package BP Reshare
 * @since    1.0
 * 
 * @uses buddyreshare_activity_get_action_url() to get it
 */
function buddyreshare_activity_action_url() {
	echo buddyreshare_activity_get_action_url();
}
	
	/**
	 * Builds the reshare action url
	 *
	 * @package BP Reshare
	 * @since    1.0
	 * 
	 * @global  BP_Activity_Template $activities_template
	 * @uses    buddyreshare_activity_get_id_to_reshare() to get the activity id to reshare
	 * @uses    wp_nonce_url() for security reason
	 * @uses    bp_get_root_domain() to get the blog's url
	 * @uses    bp_get_activity_root_slug() to get the activity slug
	 * @uses    buddyreshare_get_component_slug() to get the component's slug
	 * @return  string the action url
	 */
	function buddyreshare_activity_get_action_url() {
		global $activities_template;

		$to_reshare = buddyreshare_activity_get_id_to_reshare();

		if( empty( $to_reshare ) )
			return false;

		$action_url = wp_nonce_url( bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/' . buddyreshare_get_component_slug() . '/add/' . $to_reshare . '/' , 'buddyreshare_update' );

		return apply_filters( 'buddyreshare_activity_get_action_url', $action_url );
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
	echo buddyreshare_activity_get_button_title();
}
	
	/**
	 * Builds the reshare button title
	 *
	 * @package BP Reshare
	 * @since    1.0
	 * 
	 * @global  BP_Activity_Template $activities_template
	 * @return  string reshare button title
	 */
	function buddyreshare_activity_get_button_title(){
		global $activities_template;
		
		$title = ! empty( $activities_template->activity->reshares->css_class ) ? __( 'Reshared', 'bp-reshare' ) : __( 'Reshare', 'bp-reshare' );

		return apply_filters( 'buddyreshare_activity_get_button_title', $title );
	}

/**
 * Can this reshared activity be deleted ?
 *
 * @package BP Reshare
 * @since    1.0
 * 
 * @global  BP_Activity_Template $activities_template
 * @uses    bp_loggedin_user_id() to get current user's id
 * @uses    bp_is_activity_component() to check for Activity component area
 * @uses    bp_displayed_user_id() to get displayed user's id
 * @uses    buddyreshare_is_my_profile_reshares() to check for current logged in user's porfile reshare area
 * @return  boolean true|false
 */
function buddyreshare_can_unshare() {
	global $activities_template;

	if( empty( $activities_template->activity->reshares->css_class ) || $activities_template->activity->reshares->css_class != 'reshared' )
		return false;

	if( $activities_template->activity->user_id != bp_loggedin_user_id() )
		return false;

	$retval = false;

	if( !empty( $_COOKIE['bp-activity-scope'] ) && $_COOKIE['bp-activity-scope'] == 'reshares' && bp_is_activity_component() && !bp_displayed_user_id() )
		$retval = true;

	if( buddyreshare_is_my_profile_reshares() )
		$retval = true;


	return $retval;
}

/**
 * Displays the reshare button
 *
 * @package BP Reshare
 * @since    1.0
 * 
 * @uses buddyreshare_activity_get_button() to get it
 */
function buddyreshare_activity_button() {
	echo buddyreshare_activity_get_button();
}
	
	/**
	 * Builds the reshare button
	 *
	 * @package BP Reshare
	 * @since    1.0
	 * 
	 * @global  BP_Activity_Template $activities_template
	 * @uses    buddyreshare_activity_can_reshare() to check if the activity can be reshared
	 * @uses    buddyreshare_activity_get_button_class() to get button's classe
	 * @uses    buddyreshare_activity_get_reshares_count() to get the number of reshares
	 * @uses    bp_get_activity_id() to get activity id
	 * @uses    buddyreshare_get_component_id() to get component's id
	 * @uses 	buddyreshare_activity_get_action_url() to get action url
	 * @uses 	buddyreshare_activity_get_id_to_reshare() to get the activity id to reshare
	 * @uses  	buddyreshare_activity_get_button_title() to get the button title
	 * @uses    bp_get_button() to build the button
	 * @return  string reshare button
	 */
	function buddyreshare_activity_get_button(){
		global $activities_template;
		
		if( ! buddyreshare_activity_can_reshare() )
			return false;

		$caption = apply_filters( 'buddyreshare_activity_get_button_caption', buddyreshare_activity_get_button_title() );

		$link_text = '<span class="' . buddyreshare_activity_get_button_class() . '">' . $caption . '</span><span class="rs-count">' . buddyreshare_activity_get_reshares_count() . '</span>';

		$button = array(
			'id'                => 'reshare-container-' . bp_get_activity_id(),
			'component'         => buddyreshare_get_component_id(),
			'must_be_logged_in' => true,
			'wrapper'           => false,
			'block_self'        => false,
			'link_id'           => 'bp-reshare-' . bp_get_activity_id(),
			'link_href'         => buddyreshare_activity_get_action_url(),
			'link_rel'          => buddyreshare_activity_get_id_to_reshare(),
			'link_title'        => buddyreshare_activity_get_button_title(),
			'link_text'         => $link_text,
			'link_class'        => 'button reshare-button bp-secondary-action'
		);

		// Filter and return the HTML button
		return bp_get_button( apply_filters( 'buddyreshare_activity_get_button', $button ) );
	}
