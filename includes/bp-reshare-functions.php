<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;


function bp_reshare_activity_is_hidden(){
	global $activities_template;
	
	if( $activities_template->activity->hide_sitewide == 1 )
		return true;
	else
		return false;
}

function bp_reshare_user_did_reshared( $activity_id ){
	global $bp;
	
	$reshared = true;
	
	$reshared_by = bp_activity_get_meta( $activity_id, 'reshared_by' );
	
	if( !is_array( $reshared_by ) )	
		$reshared = false;
		
	if( is_array( $reshared_by ) && !in_array( $bp->loggedin_user->id,  $reshared_by) )
		$reshared = false;
		
	// is the loggedin_user the original author ?
	$originally_shared = bp_activity_get_specific('activity_ids='.$activity_id);
	
	if( $originally_shared['activities'][0]->user_id == $bp->loggedin_user->id )
		$reshared = true;
	
	return $reshared;
}

function bp_reshare_activity_types() {
	global $bp;
	
	$allowed_types = array( 'activity_update', 'new_blog_post', 'new_blog_comment', 'new_forum_topic', 'new_forum_post', 'reshare_update' );
	
	return apply_filters( 'bp_reshare_activity_types', $allowed_types );
}

function bp_reshare_is_user_profile_reshares() {
	if( bp_is_activity_component() && bp_displayed_user_id() && bp_is_current_action('reshares') )
		return true;
		
	else
		return false;
}

function bp_reshare_prepare_reshare( $activity_id ) {
	global $bp;
	
	$activity_to_reshare = bp_activity_get_specific('activity_ids='.$activity_id);
	$activity = $activity_to_reshare['activities'][0];
	
	/* get and increment reshared count */
	$rs_count = bp_activity_get_meta( $activity_id, 'reshared_count' );
	$rs_count = !empty( $rs_count ) ? (int)$rs_count + 1 : 1;
	bp_activity_update_meta( $activity_id, 'reshared_count', $rs_count );
	
	/* get an array of users that reshared the activity */
	$reshared_by = bp_activity_get_meta( $activity_id, 'reshared_by' );
	if( is_array( $reshared_by ) && !in_array( $bp->loggedin_user->id, $reshared_by ) )
		$reshared_by[] = $bp->loggedin_user->id;
	else
		$reshared_by[] = $bp->loggedin_user->id;
	
	bp_activity_update_meta( $activity_id, 'reshared_by', $reshared_by );
	
	$secondary_avatar = bp_core_fetch_avatar( array( 'item_id' => $activity->user_id, 'object' => 'user', 'type' => 'thumb', 'alt' => $alt, 'class' => 'avatar', 'width' => 20, 'height' => 20 ) );
	
	$component = $activity->component;
	$item_id = $activity->item_id;
	
	if( $component != 'activity' ){
		
		if( $activity->type == 'new_blog_post')
			$action = sprintf( __( '%s reshared a <a href="%s">blog post</a> originally posted by %s', 'bp-reshare' ), bp_core_get_userlink( $bp->loggedin_user->id ), $activity->primary_link, $secondary_avatar . bp_core_get_userlink( $activity->user_id ) );
		
		else if( $activity->type == 'new_blog_comment' )
			$action = sprintf( __( '%s reshared a <a href="%s">comment</a> originally posted by %s', 'bp-reshare' ), bp_core_get_userlink( $bp->loggedin_user->id ), $activity->primary_link, $secondary_avatar . bp_core_get_userlink( $activity->user_id ) );
		
		else if( $component == 'groups' ) {
			$group = groups_get_group( array( 'group_id' => $item_id ) );
			$group_link = '<a href="'. bp_get_group_permalink( $group ) .'">'. $group->name .'</a>';
			
			if( $activity->type == 'new_forum_topic' )
				$action = sprintf( __( '%s reshared a <a href="%s">forum topic</a> originally posted by %s in the group %s', 'bp-reshare' ), bp_core_get_userlink( $bp->loggedin_user->id ), $activity->primary_link, $secondary_avatar . bp_core_get_userlink( $activity->user_id ), $group_link );

			else if( $activity->type == 'new_forum_post' )
				$action = sprintf( __( '%s reshared a <a href="%s">forum reply</a> originally posted by %s in the group %s', 'bp-reshare' ), bp_core_get_userlink( $bp->loggedin_user->id ), $activity->primary_link, $secondary_avatar . bp_core_get_userlink( $activity->user_id ), $group_link );
			
		}
		
	} else {
		
		$action = sprintf( __( "%s reshared an activity originally shared by %s", 'bp-reshare' ), bp_core_get_userlink( $bp->loggedin_user->id ), $secondary_avatar . bp_core_get_userlink( $activity->user_id ) );
		
	}
	
	$reshared_args = array(
		'action'            => apply_filters('bp_reshare_action_parent_activity', $action, $activity->type ),
		'content'           => $activity->content,
		'component'         => $component,
		'type'              => 'reshare_update',
		'user_id'           => $bp->loggedin_user->id,
		'secondary_item_id' => $activity_id,
		'recorded_time'     => bp_core_current_time(),
		'hide_sitewide'     => $activity->hide_sitewide
	);
	
	if( !empty( $item_id ) )
		$reshared_args['item_id'] = $item_id;
	
	return apply_filters( 'bp_reshare_prepare_reshare', $reshared_args, $activity_id);
}

// now let's handle reshares delete
function bp_reshare_delete( $activity_id, $user_id ) {
	
	$count = bp_activity_get_meta( $activity_id, 'reshared_count' );
	$count = $count - 1;
	$reshared_by = bp_activity_get_meta( $activity_id, 'reshared_by' );
	
	if( $count == 0 ) {
		// if count is null, then we can delete all metas !
		bp_activity_delete_meta( $activity_id, 'reshared_count' );
		bp_activity_delete_meta( $activity_id, 'reshared_by' );
	} else {
		foreach( $reshared_by as $key => $val ) {
			if( $user_id  == $val )
				unset( $reshared_by[$key] );
		}
		bp_activity_update_meta( $activity_id, 'reshared_count', $count );
		bp_activity_update_meta( $activity_id, 'reshared_by', $reshared_by );
	}
	
	return true;
	
}

function bp_reshare_get_total_reshare_count_for_user( $user_id ) {
	global $wpdb,$bp;
	
	return $wpdb->get_var( $wpdb->prepare("SELECT count( id ) as count FROM {$bp->activity->table_name} WHERE type = %s AND user_id = %d", 'reshare_update', $user_id ) );
}
