<?php
/**
 * Settings functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display callback for the Email notifications setting.
 *
 * @since 2.0.0
 */
function buddyreshare_settings_display_emails() {
	?>
	<label for="buddyreshare-emails">
		<input id="buddyreshare-emails" type="checkbox" name="buddyreshare-emails" value="1" <?php checked( true, buddyreshare_are_emails_active() );?>> <?php esc_html_e( 'Yes', 'bp-reshare' ); ?>
	</label>
	<?php
}

/**
 * Display callback for the Activity types setting.
 *
 * @since 2.0.0
 */
function buddyreshare_settings_display_activity_types() {
	$disabled         = buddyreshare_get_disabled_activity_types();
	$activity_actions = bp_activity_get_actions();

	// Merge xprofile & profile.
	foreach( $activity_actions->xprofile as $k => $v ) {
		$activity_actions->profile->{$k} = $v;
	}

	// Remove some noisy types.
	unset( $activity_actions->xprofile );

	if ( bp_is_active( 'friends' ) ) {
		unset( $activity_actions->friends->friends_register_activity_action );
	}

	foreach ( $activity_actions as $component => $activity_types ) {
		$component_label = ucfirst( $component );

		if ( 'Profile' === $component_label ) {
			$component_label = 'Extended ' . $component_label;
		}
		?>
		<fieldset style="border: solid 1px #ccc; margin-bottom: 1em">
			<legend style="padding: 0 1em">
				<label for="bp-reshare-selectall-<?php echo esc_attr( $component ); ?>">
					<?php /* Translators: This string is already translated in BuddyPress */ ; ?>
					<input id="bp-reshare-selectall-<?php echo esc_attr( $component ); ?>" type="checkbox" class="bp-reshare-selectall" data-component-id="<?php echo esc_attr( $component ); ?>"> <?php echo esc_html( __( $component_label, 'buddypress' ) ); ?>
				</label>
			</legend>

			<ul style="margin: 1em 2em 1em;">

			<?php foreach ( $activity_types as $activity_type ) {
				if ( 'activity_comment' === $activity_type['key'] ) {
					continue;
				}
				?>
					<li>
						<label for="bp-reshare-activity-type-<?php echo esc_attr( $activity_type['key'] ); ?>">
							<input id="bp-reshare-activity-type-<?php echo esc_attr( $activity_type['key'] ); ?>" type="checkbox" name="buddyreshare-disabled-activity-types[]" value="<?php echo esc_attr( $activity_type['key'] );?>" data-component-id="<?php echo esc_attr( $component ); ?>" <?php checked( true, in_array( $activity_type['key'], $disabled, true ) );?>> <?php echo esc_html( $activity_type['value'] ) ;?>
						</label>
					</li>
				<?php
			} ?>

		</fieldset>
		<?php
	}
}

/**
 * Sanitization callback for the activity types setting.
 *
 * @since 2.0.0
 *
 * @param  array  $option The activity types to disable.
 * @return array          The sanitized activity types.
 */
function buddyreshare_settings_sanitize_activity_types( $option = array() ) {
	$option = array_map( 'sanitize_key', (array) $option );

	return join( ',', $option );
}

/**
 * Display callback for the Activity stream order setting.
 *
 * @since 2.0.0
 */
function buddyreshare_settings_display_activity_ordering() {
	$order_preference = buddyreshare_get_activity_order_preference();

	$orders = array(
		'reshares' => __( 'Ordered by reshared date', 'bp-reshare' ),
		'default'  => __( 'Ordered by recorded date (BuddyPress default)', 'bp-reshare' ),
	);
	?>
	<select name="buddyreshare-activity-order-preferences" id="buddyreshare-activity-order-preferences">

		<?php foreach ( $orders as $k_order => $order ) : ?>
			<option value="<?php echo esc_attr( $k_order ); ?>" <?php selected( $order_preference, $k_order ); ?>>
				<?php echo esc_html( $orders[ $k_order ] ); ?>
			</option>
		<?php endforeach; ?>

	</select>
	<?php
}

/**
 * Returns the settings fields for the Reshare feature.
 *
 * @since 2.0.0
 *
 * @return array The settings fields for the Reshare feature.
 */
function buddyreshare_settings_get_fields() {
	/**
	 * Filter here to edit the settings fields for the Reshare feature.
	 *
	 * @since 2.0.0
	 *
	 * @param array $value The settings fields for the Reshare feature.
	 */
	return apply_filters( 'buddyreshare_get_settings', array(
		'buddyreshare-emails' => array(
			'option_name'       => 'buddyreshare-emails',
			'type'              => 'boolean',
			'description'       => __( 'Enable Email notifications about activity reshares.', 'bp-reshare' ),
			'display_callback'  => 'buddyreshare_settings_display_emails',
			'sanitize_callback' => 'absint',
			'page'              => 'buddypress',
			'section'           => 'bp_main',
			'show_in_rest'      => false,
			'default'           => false,
		),
		'buddyreshare-disabled-activity-types' => array(
			'option_name'       => 'buddyreshare-disabled-activity-types',
			'type'              => 'string',
			'description'       => __( 'Activity types to disable for reshares.', 'bp-reshare' ),
			'display_callback'  => 'buddyreshare_settings_display_activity_types',
			'sanitize_callback' => 'buddyreshare_settings_sanitize_activity_types',
			'page'              => 'buddypress',
			'section'           => 'bp_activity',
			'show_in_rest'      => false,
			'default'           => '',
		),
		'buddyreshare-activity-order-preferences' => array(
			'option_name'       => 'buddyreshare-activity-order-preferences',
			'type'              => 'string',
			'description'       => __( 'Activity ordering preferences.', 'bp-reshare' ),
			'display_callback'  => 'buddyreshare_settings_display_activity_ordering',
			'sanitize_callback' => 'sanitize_key',
			'page'              => 'buddypress',
			'section'           => 'bp_activity',
			'show_in_rest'      => false,
			'default'           => 'reshares',
		),
	) );
}

/**
 * Add the Reshare feature settings to BuddyPress ones.
 *
 * @since 2.0.0
 */
function buddyreshare_settings_register_fields() {
	$fields = buddyreshare_settings_get_fields();

	foreach ( $fields as $field ) {
		add_settings_field(
				$field['option_name'],
				$field['description'],
				$field['display_callback'],
				$field['page'],
				$field['section']
		);

		register_setting( $field['page'], $field['option_name'], array_intersect_key( $field, array(
			'type'              => true,
			'description'       => true,
			'sanitize_callback' => true,
			'show_in_rest'      => true,
			'default'           => true,
		) ) );
	}
}
add_action( 'bp_register_admin_settings', 'buddyreshare_settings_register_fields', 421 );

/**
 * Adds an inline script to activate/deactivate all activity types of a BP component.
 *
 * @since 2.0.0
 */
function buddyreshare_settings_enqueue_scripts() {
	if ( false === strpos( get_current_screen()->id, 'bp-settings' ) ) {
		return false;
	}

	wp_add_inline_script( 'common', '( function($) {
		$( \'.bp-reshare-selectall\' ).on( \'click\', function( e ) {
			$.each( $( \'[data-component-id="\' + $( e.currentTarget ).data( \'component-id\') + \'"]\' ), function( i, cb ) {
				if ( 0 === i ) {
					return;
				}

				$( cb ).prop( \'checked\', $( e.currentTarget ).prop( \'checked\' ) );
			} );
		} );
	} )( jQuery );' );
}
add_action( 'bp_admin_enqueue_scripts', 'buddyreshare_settings_enqueue_scripts' );
