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

function buddyreshare_posts_enqueue_assets() {
	$post = get_post();

	if ( ! is_singular( $post->post_type ) ) {
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

	$reshare_url = trailingslashit( bp_get_root_domain() ) .  bp_get_activity_root_slug() . '/' . buddyreshare_get_component_slug();

	$script_data = array_merge( $script_data, array(
		'commentsAreaID' => apply_filters( 'buddyreshare_posts_comments_area_id', '#comments' ),
		'activity'       => array(
			'id'     => $activity->id,
			'author' => bp_core_get_username( $activity->user_id ),
			'isSelf' => (int) $activity->user_id === (int) $script_data['params']['u'],
		),
	), buddyreshare_activity_get_templates( 'reshareButton' ) );

	wp_enqueue_script( 'bp-reshare-posts' );
	wp_localize_script( 'bp-reshare-request', 'bpReshare', $script_data );
	wp_enqueue_style( 'bp-reshare-style' );
}
add_action( 'bp_enqueue_scripts', 'buddyreshare_posts_enqueue_assets' );
