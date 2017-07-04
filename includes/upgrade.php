<?php
/**
 * Upgrade functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function buddyreshare_get_upgrade_routines() {
	/**
	 * This is were i'll define what to keep and
	 * what to remove for future releases.
	 */

	/** 2.0.0 **/

	// Options to remove:
	// bp-reshare-user-amount
	// buddyreshare-allowed-types
}

function buddyreshare_upgrade() {
	$buddyreshare = buddyreshare();

	$db_version = bp_get_option( 'bp-reshare-version', 0 );
	$version    = buddyreshare_get_plugin_version();

	if ( 2.0 === (float) $version ) {
		$prefix          = bp_core_get_table_prefix();
		$charset_collate = $GLOBALS['wpdb']->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( array(
			"CREATE TABLE {$prefix}bp_activity_user_reshares (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				activity_id bigint(20) NOT NULL,
				user_id bigint(20) NOT NULL,
				date_reshared datetime NOT NULL,
				KEY user_id (user_id),
				KEY activity_id (activity_id),
				KEY date_reshared (date_reshared),
				UNIQUE KEY user_reshared ( activity_id, user_id )
			) {$charset_collate};"
		) );

		// Install Emails
		if ( ! function_exists( 'buddyreshare_emails_install' ) ) {
			require_once( buddyreshare_get_includes_dir() . 'emails.php' );
		}

		remove_action( 'bp_core_install_emails', 'buddyreshare_emails_install' );
		buddyreshare_emails_install();
	}

	bp_update_option( 'bp-reshare-version', $version );
}
add_action( 'bp_admin_init', 'buddyreshare_upgrade', 1040 );
