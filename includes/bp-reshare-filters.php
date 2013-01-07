<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;
	
function bp_reshare_activity_querystring_filter( $query_string, $object ) {
	global $bp, $reshare_filter;
	
	/* global activities */
	if( strpos( $query_string, 'reshares' ) || bp_reshare_is_user_profile_reshares() ) {
		
		$defaults = array( 'page' => 0 );
		$r = wp_parse_args( $query_string, $defaults );
		extract( $r, EXTR_SKIP );
		
		$user_id = bp_displayed_user_id() ? $bp->displayed_user->id : $bp->loggedin_user->id;
		$paginate = !empty( $page ) ? '&page='. $page : '' ;
		
		$query_string = 'action=reshare_update&user_id='. $user_id . $paginate ;
	}
	
	/* single member activities */
	if( strpos( $query_string, 'activity_mostreshared' ) ) {
		$reshare_filter = 1;
		$query_string = str_replace( 'type=activity_mostreshared&action=activity_mostreshared', '', $query_string );
	} else {
		$reshare_filter = 0;
	}
		
	
	return apply_filters( 'bp_reshare_activity_querystring_filter', $query_string, $object );
}

add_filter( 'bp_ajax_querystring', 'bp_reshare_activity_querystring_filter', 12, 2  );

function bp_reshare_most_reshared_sql( $sql, $select_sql, $from_sql, $where_sql, $sort, $page = false ) {
	global $wpdb, $reshare_filter, $bp;
	
	if( empty( $reshare_filter ) )
		return $sql;
		
	$select_sql .= ", am.meta_value";
	$from_sql .=" LEFT JOIN {$bp->activity->table_name_meta} am ON a.id = am.activity_id";
	$where_sql .= " AND am.meta_key = 'reshared_count'";
	$order = " ORDER BY am.meta_value + 0 {$sort}";
	
	$sql = $select_sql .' '.$from_sql.' '.$where_sql . $order . ' ' . $page ;
	
	return $sql;
}

add_filter('bp_activity_get_user_join_filter', 'bp_reshare_most_reshared_sql', 10, 6);

function bp_reshare_most_reshared_sql_count( $sql, $where_sql, $sort ) {
	global $wpdb, $reshare_filter, $bp;
	
	if( empty( $reshare_filter ) )
		return $sql;
		
	$where_sql .= " AND am.meta_key = 'reshared_count'";
		
	$sql = "SELECT count(a.id) FROM {$bp->activity->table_name} a LEFT JOIN {$bp->activity->table_name_meta} am ON a.id = am.activity_id {$where_sql} ORDER BY am.meta_value + 0 {$sort}";
	
	return $sql;
}

add_filter( 'bp_activity_total_activities_sql', 'bp_reshare_most_reshared_sql_count', 10,  3 );


function bp_reshare_feed_url( $feed_url, $scope ) {
	if( $scope == 'reshares' )
		$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/reshares/feed/';
		
	return $feed_url;
}

add_filter( 'bp_dtheme_activity_feed_url', 'bp_reshare_feed_url', 10, 2 );

function bp_reshare_notajaxed_feed_url( $feed_url ) {
	if( $_COOKIE['bp-activity-scope'] == 'reshares' )
		$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/reshares/feed/';
		
	return $feed_url;
}

add_filter( 'bp_get_sitewide_activity_feed_link', 'bp_reshare_notajaxed_feed_url', 10, 1 );

function bp_reshare_replace_activity_delete_link( $link ) {

	// if activity type is a reshare, then we replace the delete link behavior
	if( bp_get_activity_type() == 'reshare_update' ) {
		$class = 'delete-reshare';
		$action_url = wp_nonce_url( bp_get_root_domain() . '/' . bp_get_activity_root_slug() . '/?delete_reshare=' . bp_get_activity_id(), '_reshare_delete' );

		$link = '<a href="' . $action_url . '" class="button item-button bp-secondary-action ' . $class . ' confirm" rel="nofollow">' . __( 'Delete', 'bp-reshare' ) . '</a>';
	}
	
	return apply_filters('bp_reshare_replace_activity_delete_link', $link );
}

/*** we need to replace the delete link in case activity type is a reshare ***/
add_filter( 'bp_get_activity_delete_link', 'bp_reshare_replace_activity_delete_link', 10, 1 );


function bp_reshare_check_for_parent_type( $can_comment ){
	global $activities_template;
	
	if( $activities_template->disable_blogforum_replies == 0 ) {
		return $can_comment;
	}
	
	if('reshare_update' != bp_get_activity_type() )
		return $can_comment;
		
	else {
		
		if ( !(int)bp_get_option( 'bp-reshare-disable-blogforum-comments' ) || '' == bp_get_option( 'bp-reshare-disable-blogforum-comments' ) )
			return $can_comment;
		
		/*
		 	the activity is a reshare, 
			Admin wants to disable comments for blogs and forums
			we now need to check for parent type
		*/
		
		$activity_first_id = bp_get_activity_secondary_item_id();
		
		$parent_activity = bp_activity_get_specific('activity_ids='.$activity_first_id );
		
		if( in_array(  $parent_activity['activities'][0]->type, array( 'new_blog_post', 'new_blog_comment', 'new_forum_topic', 'new_forum_post') ) ){
			return false;
			
		} else {
			return $can_comment;
		}
		
	}

}

/*** we need to check the parent type to see if the reshare can be commented.. ***/
add_filter('bp_activity_can_comment', 'bp_reshare_check_for_parent_type', 10, 1);