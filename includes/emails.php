<?php
/**
 * Email functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Appends a new row to Activity's User Email preferences.
 *
 * @since 2.0.0
 */
function buddyreshare_emails_user_preferences() {
	$send_emails = 'yes';
	if ( 'no' === bp_get_user_meta( bp_displayed_user_id(), 'buddyreshare_emails_send', true ) ) {
		$send_emails = 'no';
	}

	$checked   = ' ' . checked( $send_emails, 'yes', false );
	$unchecked = ' ' . checked( $send_emails, 'no', false );

	printf( '<tr id="activity-notification-settings-reshares">
		<td>&nbsp;</td>
		<td>%1$s</td>
		<td class="yes">
			<input type="radio" name="notifications[buddyreshare_emails_send]" id="notification-activity-reshares-yes" value="yes"%2$s/>
			<label for="notification-activity-reshares-yes" class="bp-screen-reader-text">%3$s</label>
		</td>
		<td class="no">
			<input type="radio" name="notifications[buddyreshare_emails_send]" id="notification-activity-reshares-no" value="no"%4$s/>
			<label for="notification-activity-reshares-no" class="bp-screen-reader-text">%5$s</label>
		</td>
		</tr>',
		esc_html__( 'A member reshares one of your updates.', 'bp-reshare' ),
		$checked,
		esc_html__( 'Yes, send email', 'bp-reshare' ),
		$unchecked,
		esc_html__( 'No, do not send email', 'bp-reshare' )
	);
}
add_action( 'bp_activity_screen_notification_settings', 'buddyreshare_emails_user_preferences' );

/**
 * Appends the BP Reshare unsubscribe emails schema to BuddyPress one.
 *
 * NB: This makes sure the unsubscribe link, when clicked will disable
 * the 'buddyreshare_emails_send' notification for the user.
 *
 * @since 2.0.0
 *
 * @param  array  $emails The BuddyPress emails schema.
 * @return array          The BuddyPress emails schema with the Reshare's one.
 */
function buddyreshare_emails_schema( $emails = array() ) {
	return array_merge( $emails, array(
		'buddyreshare-new-reshare' => array(
			'description'	=> __( 'Reshared activities.', 'bp-reshare' ),
			'unsubscribe'	=> array(
				'meta_key'	=> 'buddyreshare_emails_send',
				'message'	=> __( 'You will no longer receive emails when someone reshared on of your activity.', 'bp-reshare' ),
			),
		),
	) );
}
add_filter( 'bp_email_get_unsubscribe_type_schema', 'buddyreshare_emails_schema' );

/**
 * Sends a notification email to the activity author when reshared.
 *
 * @since 2.0.0
 *
 * @param  array $args {
 *     @type int $activity_id  The reshared activity ID.
 *     @type int $user_id      The ID of the user who reshared.
 * }
 */
function buddyreshare_emails_send( $args = array() ) {
	if ( empty( $args['user_id'] ) || empty( $args['activity_id'] ) ) {
		return;
	}

	$activity  = new BP_Activity_Activity( $args['activity_id'] );
	$resharer  = bp_core_get_user_displayname( $args['user_id'] );

	if ( empty( $activity->id ) || ! $resharer ) {
		return;
	}

	// Send the email only if user did not disallow it.
	if ( 'no' != bp_get_user_meta( $activity->user_id, 'buddyreshare_emails_send', true ) ) {
		$email_type = 'buddyreshare-new-reshare';
		$link       = bp_activity_get_permalink( $activity->id, $activity );

		remove_filter( 'bp_get_activity_content_body', 'convert_smilies' );
		remove_filter( 'bp_get_activity_content_body', 'wpautop' );
		remove_filter( 'bp_get_activity_content_body', 'bp_activity_truncate_entry', 5 );

		$content = apply_filters_ref_array( 'bp_get_activity_content_body', array( $activity->content, &$activity ) );

		add_filter( 'bp_get_activity_content_body', 'convert_smilies' );
		add_filter( 'bp_get_activity_content_body', 'wpautop' );
		add_filter( 'bp_get_activity_content_body', 'bp_activity_truncate_entry', 5 );

		$unsubscribe_args = array(
			'user_id'           => $activity->user_id,
			'notification_type' => $email_type,
		);

		bp_send_email( $email_type, $activity->user_id, array(
			'tokens' => array(
				'activity'         => $activity,
				'usermessage'      => wp_strip_all_tags( $content ),
				'thread.url'       => $link,
				'poster.name'      => $resharer,
				'receiver-user.id' => $activity->user_id,
				'unsubscribe' 	   => esc_url( bp_email_get_unsubscribe_link( $unsubscribe_args ) ),
			),
		) );
	}

	/**
	 * Fires after handling email notifications.
	 *
	 * @since 2.0.0
	 *
	 * @param BP_Activity_Activity $activity The activity object.
	 * @param array                array $args {
	 *     @type int $activity_id  The reshared activity ID.
	 *     @type int $user_id      The ID of the user who reshared.
	 * }
	 */
	do_action( 'buddyreshare_notify_reshare', $activity, $args );
}
add_action( 'buddyreshare_reshare_added', 'buddyreshare_emails_send', 11, 1 );

/**
 * Gets email templates
 *
 * @since 2.0.0
 *
 * @return array An associative array containing the email type and the email template data.
 */
function buddyreshare_emails_get() {
	/**
	 * Filter here to add your custom emails
	 *
	 * @since 2.0.0
	 *
	 * @param array $value An associative array containing the email type and the email template data.
	 */
	return apply_filters( 'buddyreshare_emails_get', array(
		'buddyreshare-new-reshare' => array(
			'description'  => _x( 'A member reshared an activity', 'BP Email template description', 'bp-reshare' ),
			'term_id'      => 0,
			'post_title'   => _x( '[{{{site.name}}}] {{poster.name}} reshared your update', 'BP Email template subject', 'bp-reshare' ),
			'post_content' => _x( "{{poster.name}} reshared this update:\n\n<blockquote>&quot;{{usermessage}}&quot;</blockquote>\n\n<a href=\"{{{thread.url}}}\">Go to your update</a>.", 'BP Email template HTML text', 'bp-reshare' ),
			'post_excerpt' => _x( "{{poster.name}} reshared this update:\n\n\"{{usermessage}}\"\n\nGo to your update: {{{thread.url}}}", 'BP Email template plain text', 'bp-reshare' ),
		),
	) );
}

/**
 * Installs/Reinstalls email templates for the plugin's email notifications
 *
 * @since 2.0.0
 */
function buddyreshare_emails_install() {
	$switched = false;

	// Switch to the root blog, where the email posts live.
	if ( ! bp_is_root_blog() ) {
		switch_to_blog( bp_get_root_blog_id() );
		$switched = true;
	}

	// Get Emails
	$email_types = buddyreshare_emails_get();

	// Set email types
	foreach( $email_types as $email_term => $term_args ) {
		if ( term_exists( $email_term, bp_get_email_tax_type() ) ) {
			$email_type = get_term_by( 'slug', $email_term, bp_get_email_tax_type() );

			$email_types[ $email_term ]['term_id'] = $email_type->term_id;
		} else {
			$term = wp_insert_term( $email_term, bp_get_email_tax_type(), array(
				'description' => $term_args['description'],
			) );

			$email_types[ $email_term ]['term_id'] = $term['term_id'];
		}

		// Insert Email templates if needed
		if ( ! empty( $email_types[ $email_term ]['term_id'] ) && ! is_a( bp_get_email( $email_term ), 'BP_Email' ) ) {
			wp_insert_post( array(
				'post_status'  => 'publish',
				'post_type'    => bp_get_email_post_type(),
				'post_title'   => $email_types[ $email_term ]['post_title'],
				'post_content' => $email_types[ $email_term ]['post_content'],
				'post_excerpt' => $email_types[ $email_term ]['post_excerpt'],
				'tax_input'    => array(
					bp_get_email_tax_type() => array( $email_types[ $email_term ]['term_id'] )
				),
			) );
		}
	}

	if ( $switched ) {
		restore_current_blog();
	}
}
add_action( 'bp_core_install_emails', 'buddyreshare_emails_install' );
