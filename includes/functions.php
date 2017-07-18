<?php
/**
 * Main functions.
 *
 * @package BP Reshare\includes
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns plugin version
 *
 * @since 1.0
 *
 * @return string The plugin's version
 */
function buddyreshare_get_plugin_version() {
	return buddyreshare()->version;
}

/**
 * Returns plugin DB version
 *
 * @since 1.0
 *
 * @return string The plugin's DB version
 */
function buddyreshare_get_plugin_db_version() {
	return buddyreshare()->db_version;
}

/**
 * Returns plugin's dir
 *
 * @since 1.0
 *
 * @return string The plugin's dir path
 */
function buddyreshare_get_plugin_dir() {
	return buddyreshare()->plugin_dir;
}

/**
 * Returns plugin's includes dir
 *
 * @since 1.0
 *
 * @return string The plugin's includes dir path
 */
function buddyreshare_get_includes_dir() {
	return buddyreshare()->includes_dir;
}

/**
 * Returns plugin's js url
 *
 * @since  1.0
 * @since  2.0.0 Path edited to one level up.
 *
 * @return string The plugin's url to JavaScript's dir.
 */
function buddyreshare_get_js_url() {
	return buddyreshare()->js_url;
}

/**
 * Returns plugin's css url
 *
 * @since  1.0
 * @since  2.0.0 Path edited to one level up.
 *
 * @return string The plugin's url to Style's dir.
 */
function buddyreshare_get_css_url() {
	return buddyreshare()->css_url;
}

/**
 * Get the JS/CSS minified suffix.
 *
 * @since 2.0.0
 *
 * @return string the JS/CSS minified suffix.
 */
function buddyreshare_min_suffix() {
	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG )  {
		$min = '';
	}

	/**
	 * Filter here to edit the minified suffix.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $min The minified suffix.
	 */
	return apply_filters( 'buddyreshare_min_suffix', $min );
}

/**
 * Returns plugin's component id
 *
 * @since 1.0
 *
 * @return string plugin's component id
 */
function buddyreshare_get_component_id() {
	return buddyreshare()->component_id;
}

/**
 * Returns plugin's component slug
 *
 * @since 1.0
 *
 * @return string plugin's component slug
 */
function buddyreshare_get_component_slug() {
	return apply_filters( 'buddyreshare_get_component_slug', buddyreshare()->component_slug );
}

/**
 * Displays the component name
 *
 * @since 1.0
 */
function buddyreshare_component_name() {
	echo buddyreshare_get_component_name();
}

/**
 * Returns plugin's component name
 *
 * @since 1.0
 *
 * @return string plugin's component name
 */
function buddyreshare_get_component_name() {
	return apply_filters( 'buddyreshare_get_component_name', buddyreshare()->component_name );
}

/**
 * Are email notifications active ?
 *
 * @since 2.0.0
 *
 * @return boolean True to send emails, False otherwise.
 */
function buddyreshare_are_emails_active() {
	return (bool) apply_filters( 'buddyreshare_are_emails_active', bp_get_option( 'buddyreshare-emails', false ) );
}

/**
 * Are we on a the current user's profile reshare tab
 *
 * @since 1.0
 * @since 2.0.0 Code clean up.
 *
 * @return  boolean true|false
 */
function buddyreshare_is_user_profile_reshares() {
	$return = false;

	if ( bp_is_activity_component() && bp_is_user() && bp_is_current_action( buddyreshare_get_component_slug() ) ) {
		$return = true;
	}

	return $return;
}

/**
 * Returns the disabled activity actions
 *
 * @since 2.0.0
 *
 * @return array the disabled activity actions.
 */
function buddyreshare_get_disabled_activity_types() {
	$disabled_types = explode( ',', trim( bp_get_option( 'buddyreshare-disabled-activity-types', '' ), ' ' ) );

	return (array) apply_filters( 'buddyreshare_get_disabled_activity_types', array_filter( $disabled_types ) );
}

/**
 * What is the order preference for the activity stream?
 *
 * @since 2.0.0
 *
 * @return string The order preference for the activity stream.
 */
function buddyreshare_get_activity_order_preference() {
	return bp_get_option( 'buddyreshare-activity-order-preferences', 'reshares' );
}

/**
 * Returns strings to be used in JavaScript to build the reshared time since.
 *
 * @since 2.0.0
 *
 * @return array the list of strings to be used in JavaScript to build the reshared time since.
 */
function buddyreshare_get_l10n_time_since() {
	return array(
		'sometime'  => _x( '(Reshared sometime)', 'javascript time since', 'bp-reshare' ),
		'now'       => _x( '(Reshared right now)', 'javascript time since', 'bp-reshare' ),
		'ago'       => _x( '(Reshared % ago)', 'javascript time since', 'bp-reshare' ),
		'separator' => _x( ',', 'Separator in javascript time since', 'bp-reshare' ),
		'year'      => _x( '% year', 'javascript time since singular', 'bp-reshare' ),
		'years'     => _x( '% years', 'javascript time since plural', 'bp-reshare' ),
		'month'     => _x( '% month', 'javascript time since singular', 'bp-reshare' ),
		'months'    => _x( '% months', 'javascript time since plural', 'bp-reshare' ),
		'week'      => _x( '% week', 'javascript time since singular', 'bp-reshare' ),
		'weeks'     => _x( '% weeks', 'javascript time since plural', 'bp-reshare' ),
		'day'       => _x( '% day', 'javascript time since singular', 'bp-reshare' ),
		'days'      => _x( '% days', 'javascript time since plural', 'bp-reshare' ),
		'hour'      => _x( '% hour', 'javascript time since singular', 'bp-reshare' ),
		'hours'     => _x( '% hours', 'javascript time since plural', 'bp-reshare' ),
		'minute'    => _x( '% minute', 'javascript time since singular', 'bp-reshare' ),
		'minutes'   => _x( '% minutes', 'javascript time since plural', 'bp-reshare' ),
		'second'    => _x( '% second', 'javascript time since singular', 'bp-reshare' ),
		'seconds'   => _x( '% seconds', 'javascript time since plural', 'bp-reshare' ),
		'time_chunks' => array(
			'a_year'   => YEAR_IN_SECONDS,
			'b_month'  => 30 * DAY_IN_SECONDS,
			'c_week'   => WEEK_IN_SECONDS,
			'd_day'    => DAY_IN_SECONDS,
			'e_hour'   => HOUR_IN_SECONDS,
			'f_minute' => MINUTE_IN_SECONDS,
			'g_second' => 1,
		),
	);
}

/**
 * Gets the common script data used in all JavaScript UIs.
 *
 * @since 2.0.0
 *
 * @return array The common script data used in all JavaScript UIs.
 */
function buddyreshare_get_common_script_data() {
	$buddyreshare = buddyreshare();

	/**
	 * Filter here to edit the common script data used in all JavaScript UIs.
	 *
	 * @since 2.0.0
	 *
	 * @param array $value The common script data used in all JavaScript UIs.
	 */
	return apply_filters( 'buddyreshare_get_common_script_data', array(
		'params' => array(
			'root_url'       => esc_url_raw( rest_url( trailingslashit( $buddyreshare->rest->namespace . '/' . $buddyreshare->rest->version ) ) ),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'u'              => get_current_user_id(),
			'disabled_types' => buddyreshare_get_disabled_activity_types(),
		),
	) );
}

/**
 * Register the JavaScripts and Style assets.
 *
 * @since 2.0.0
 */
function buddyreshare_register_assets() {
	$min     = buddyreshare_min_suffix();
	$version = buddyreshare_get_plugin_version();
	$assets  = array(
		array( 'type' => 'js', 'handle' => 'bp-reshare-request', 'src' => "request{$min}.js", 'deps' => array() ),
		array( 'type' => 'js', 'handle' => 'bp-reshare', 'src' => "script{$min}.js", 'deps' => array( 'bp-reshare-request', 'jquery' ) ),
		array( 'type' => 'js', 'handle' => 'bp-reshare-activity', 'src' => "single-activity{$min}.js", 'deps' => array( 'bp-reshare' ) ),
		array( 'type' => 'js', 'handle' => 'bp-reshare-posts', 'src' => "posts{$min}.js", 'deps' => array( 'bp-reshare-request' ) ),
		array( 'type' => 'css', 'handle' => 'bp-reshare-style', 'src' => "style{$min}.css", 'deps' => array() ),
	);

	foreach ( $assets as $asset ) {
		if ( 'js' === $asset['type'] ) {
			wp_register_script(
				$asset['handle'],
				buddyreshare_get_js_url() . $asset['src'],
				$asset['deps'],
				$version,
				true
			);
		} elseif ( 'css' === $asset['type'] ) {
			wp_register_style(
				$asset['handle'],
				buddyreshare_get_css_url() . $asset['src'],
				array(),
				$version,
				'all'
			);
		}
	}
}
add_action( 'bp_init', 'buddyreshare_register_assets' );

/**
 * Load translations.
 *
 * @since 2.0.0
 */
function buddyreshare_load_textdomain() {
	$buddyreshare = buddyreshare();

	load_plugin_textdomain( $buddyreshare->domain, false, trailingslashit( basename( $buddyreshare->plugin_dir ) ) . 'languages' );
}
add_action( 'bp_init', 'buddyreshare_load_textdomain', 1 );
