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
