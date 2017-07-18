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

/**
 * Displays an admin notice for users who are not using the Entrepôt plugin.
 *
 * @since 1.0.0
 */
function buddyreshare_needs_upgrade_notice() {
	$version = buddyreshare_get_plugin_db_version();
	
	if ( ! $version || 2.0 <= (float) $version || function_exists( 'entrepot' ) ) {
		return;
	}
	?>
	<div id="message" class="error">
		<p>
			<?php printf( __( 'BP Reshare needs to perform some upgrade tasks on your database. It appears you are not using %s, you will need to run manually the tasks listed into the /includes/upgrade.php file of this plugin.', 'bp-reshare' ),
				'<a href="https://github.com/imath/entrepot/releases">"Entrepot"</a>'
			); ?>
		</p>
	</div>
	<?php
}
add_action( 'all_admin_notices', 'buddyreshare_needs_upgrade_notice' );

/**
 * Creates the Activity reshares table.
 *
 * @since  2.0.0
 *
 * @return integer 1 if the table was created. O otherwise.
 */
function buddyreshare_upgrade_create_table() {
	global $wpdb;

	$prefix          = bp_core_get_table_prefix();
	$charset_collate = $wpdb->get_charset_collate();

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

	$did = 0;

	$suppress = $wpdb->suppress_errors();
	$did = (int) 4 === count( $wpdb->get_results( "SHOW COLUMNS FROM {$prefix}bp_activity_user_reshares" ) );
	$wpdb->suppress_errors( $suppress );

	return $did;
}

/**
 * Migrates the reshares to the new Activity reshares table.
 *
 * @since  2.0.0
 *
 * @param  integer $per_page The number of reshares to migrate.
 * @return integer           The number of migrated reshares.
 */
function buddyreshare_upgrade_migrate_reshares( $per_page = 20 ) {
	global $wpdb;

	$did   = 0;
	$table = bp_core_get_table_prefix() . 'bp_activity_user_reshares';

	$activities = bp_activity_get( array(
		'per_page'    => $per_page,
		'show_hidden' => true,
		'filter'      => array( 'action' => 'reshare_update' ),
	) );

	if ( isset( $activities['activities'] ) && count( $activities['activities'] ) ) {
		foreach ( $activities['activities'] as $activity ) {
			$wpdb->insert( $table, array(
				'activity_id'   => $activity->secondary_item_id,
				'user_id'       => $activity->user_id,
				'date_reshared' => $activity->date_recorded,
			) );

			bp_activity_delete( array( 'id' => $activity->id ) );

			$did += 1;
		}
	}

	return $did;
}

/**
 * Counts the number of reshares to migrate.
 *
 * @since  2.0.0
 *
 * @return integer The number of reshares to migrate.
 */
function buddyreshare_upgrade_migrate_reshares_count() {
	global $wpdb;

	$table = bp_core_get_table_prefix() . 'bp_activity';

	return $wpdb->get_var( "SELECT COUNT( id ) FROM {$table} WHERE type = 'reshare_update'" );
}

/**
 * Deletes the Activity reshares metadata.
 *
 * @since  2.0.0
 *
 * @return integer 1.
 */
function buddyreshare_upgrade_remove_activity_metas() {
	global $wpdb;

	$table = bp_core_get_table_prefix() . 'bp_activity_meta';

	$wpdb->query( "DELETE FROM {$table} WHERE meta_key IN ( 'reshared_count', 'reshared_by' )" );

	return 1;
}

/**
 * Deletes Other reshares metadata.
 *
 * @since  2.0.0
 *
 * @return integer 1.
 */
function buddyreshare_upgrade_remove_other_metas() {
	global $wpdb;

	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'buddyreshare_count'" );
	bp_delete_option( 'bp-reshare-user-amount' );
	bp_delete_option( 'buddyreshare-allowed-types' );

	return 1;
}

/**
 * Creates the BuddyPress email template that will be used for email notifications.
 *
 * @since  2.0.0
 *
 * @return integer 1.
 */
function buddyreshare_upgrade_create_emails() {
	// Install Emails
	if ( ! function_exists( 'buddyreshare_emails_install' ) ) {
		require_once( buddyreshare_get_includes_dir() . 'emails.php' );
	}

	remove_action( 'bp_core_install_emails', 'buddyreshare_emails_install' );
	buddyreshare_emails_install();

	return 1;
}

/**
 * Update the plugin's db version.
 *
 * @since  2.0.0
 *
 * @return integer 1.
 */
function buddyreshare_upgrade_db_version() {
	bp_update_option( 'bp-reshare-version', buddyreshare_get_plugin_version() );

	return 1;
}

/**
 * Registers upgrade routines into the Entrepôt Upgrade API.
 *
 * @since  2.0.0
 */
function buddyreshare_add_upgrade_routines() {
	$db_version = bp_get_option( 'bp-reshare-version', 0 );

	// We are not using the Entrepôt Upgrade API for install.
	if ( 0 === (int) $db_version ) {
		return;
	}

	if ( version_compare( $db_version, buddyreshare_get_plugin_version(), '<' ) ) {
		entrepot_register_upgrade_tasks( 'bp-reshare', $db_version, array(
			'2.0.0' => array(
				array(
					'callback' => 'buddyreshare_upgrade_create_table',
					'count'    => '__return_true',
					'message'  => _x( 'Create the Activity Reshares table', 'Upgrader feedback message', 'bp-reshare' ),
					'number'   => 1,
				),
				array(
					'callback' => 'buddyreshare_upgrade_migrate_reshares',
					'count'    => 'buddyreshare_upgrade_migrate_reshares_count',
					'message'  => _x( 'Migrate the Reshare Updates into the new table', 'Upgrader feedback message', 'bp-reshare' ),
					'number'   => 5,
				),
				array(
					'callback' => 'buddyreshare_upgrade_remove_activity_metas',
					'count'    => '__return_true',
					'message'  => _x( 'Delete Activity reshared metas', 'Upgrader feedback message', 'bp-reshare' ),
					'number'   => 1,
				),
				array(
					'callback' => 'buddyreshare_upgrade_remove_other_metas',
					'count'    => '__return_true',
					'message'  => _x( 'Delete users and site reshared metas', 'Upgrader feedback message', 'bp-reshare' ),
					'number'   => 1,
				),
				array(
					'callback' => 'buddyreshare_upgrade_create_emails',
					'count'    => '__return_true',
					'message'  => _x( 'Create the BuddyPress email template', 'Upgrader feedback message', 'bp-reshare' ),
					'number'   => 1,
				),
				array(
					'callback' => 'buddyreshare_upgrade_db_version',
					'count'    => '__return_true',
					'message'  => _x( 'Upgrade the plugin version', 'Upgrader feedback message', 'bp-reshare' ),
					'number'   => 1,
				),
			),
		) );
	}
}
add_action( 'entrepot_register_upgrade_tasks', 'buddyreshare_add_upgrade_routines' );

/**
 * Install the plugin.
 *
 * @since 2.0.0
 */
function buddyreshare_install() {
	if ( 0 !== (int) bp_get_option( 'bp-reshare-version', 0 ) ) {
		return;
	}

	buddyreshare_upgrade_create_table();
	buddyreshare_upgrade_create_emails();
	buddyreshare_upgrade_db_version();
}
add_action( 'bp_admin_init', 'buddyreshare_install', 1040 );
