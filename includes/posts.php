<?php
/**
 * Post functions.
 *
 * @package BP Reshare\includes
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get the BuddyPress Activity favorite/unfavorite link
 *
 * @since 2.0.0
 *
 * @return array The Fav and Unfav links for the activity.
 */
function buddyreshare_get_activity_favorite_links() {
	$needs_switch = ! bp_is_root_blog();

	if ( $needs_switch ) {
		switch_to_blog( bp_get_root_blog_id() );
	}

	$links = array(
		'fav'   => bp_get_activity_favorite_link(),
		'unfav' => bp_get_activity_unfavorite_link(),
	);

	if ( $needs_switch ) {
		restore_current_blog();
	}

	return $links;
}

/**
 * Enqueue the single Post Type scripts and style needed.
 *
 * @since 2.0.0
 */
function buddyreshare_posts_enqueue_assets() {
	$post = get_post();

	if ( is_404() || ! is_singular( $post->post_type ) ) {
		return;
	}

	$activity_args = bp_activity_get_post_type_tracking_args( $post->post_type );
	$script_data   = buddyreshare_get_common_script_data();

	if ( ! $activity_args || in_array( $activity_args->action_id, $script_data['params']['disabled_types'], true ) || empty( $script_data['params']['u'] ) ) {
		return;
	}

	$activity = bp_activity_get( array(
		'max'    => 1,
		'filter' => array(
			'object'       => buddypress()->blogs->id,
			'action'       => $activity_args->action_id,
			'primary_id'   => get_current_blog_id(),
			'secondary_id' => $post->ID,
		),
	) );

	$activity = reset( $activity['activities'] );

	if ( empty( $activity->id ) ) {
		return;
	}

	$templates = buddyreshare_activity_get_templates( 'reshareButton' );

	if ( bp_activity_can_favorite() ) {
		if ( isset( $GLOBALS['activities_template'] ) ) {
			$reset_activities_template = $GLOBALS['activities_template'];
		}

		$GLOBALS['activities_template'] = (object) array( 'activity' => $activity );
		$user_favorites                 = buddyreshare_users_get_favorites( $activity->id );
		$favorite_links                 = buddyreshare_get_activity_favorite_links();

		if ( in_array( (string) $script_data['params']['u'], $user_favorites, true ) ) {
			$f_link  = $favorite_links['unfav'];
			/* Translators: This string is already translated in BuddyPress */
			$f_text  = __( 'Remove Favorite', 'buddypress' );
			$f_class = 'fav';
		} else {
			$f_link  = $favorite_links['fav'];
			/* Translators: This string is already translated in BuddyPress */
			$f_text  = __( 'Favorite', 'buddypress' );
			$f_class = 'unfav';
		}

		$templates['templates']['favoritesButton'] = sprintf( '<a href="%1$s" class="button %3$s bp-secondary-action">
			%4$s
			<span class="count">%5$s</span>
		</a>', esc_url_raw( $f_link ), 'activity-' . $activity->id, $f_class, esc_html( $f_text ), count( $user_favorites ) );

		if ( isset( $reset_activities_template ) ) {
			$GLOBALS['activities_template'] = $reset_activities_template;
		} else {
			unset( $GLOBALS['activities_template'] );
		}

		// Catch the feedback messages if needed.
		$notice = '';
		ob_start();
		do_action( 'template_notices' );
		$notice = ob_get_clean();

		if ( ! empty( $notice ) ) {
			$templates['templates']['notice'] = sprintf( '<div id="template-notices" role="alert" aria-atomic="true">%s</div>', $notice );
		}
	}

	$reshare_url = trailingslashit( bp_get_root_domain() ) .  bp_get_activity_root_slug() . '/' . buddyreshare_get_component_slug();

	$script_data = array_merge( $script_data, array(
		/**
		 * Filter here to change the comments area ID, if your theme is using another one.
		 *
		 * @since 2.0.0
		 *
		 * @param string $value The comments area selector.
		 */
		'commentsAreaID' => apply_filters( 'buddyreshare_posts_comments_area_id', '#comments' ),
		'activity'       => array(
			'id'       => $activity->id,
			'author'   => bp_core_get_username( $activity->user_id ),
			'isSelf'   => (int) $activity->user_id === (int) $script_data['params']['u'],
			'reshares' => buddyreshare_users_get_reshares( $activity->id ),
		),
	), $templates );

	wp_enqueue_script( 'bp-reshare-posts' );
	wp_localize_script( 'bp-reshare-request', 'bpReshare', $script_data );
	wp_enqueue_style( 'bp-reshare-style' );
	wp_add_inline_style( 'bp-reshare-style', sprintf( '
		[data-activity-id="%1$s"] {
			margin: 1em 0;
		}

		[data-activity-id="%1$s"] .fav span.count,
		[data-activity-id="%1$s"] .unfav span.count {
			background: #767676;
			color: #fff;
			font-size: 90%;
			margin-left: 2px;
			padding: 0 5px;
		}
	', 'activity-' . $activity->id ) );
}
add_action( 'bp_enqueue_scripts', 'buddyreshare_posts_enqueue_assets' );
