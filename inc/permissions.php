<?php

function wikify_is_post_owner($post_id) {
	$current_user = wp_get_current_user();
	$UserID		= $current_user->ID;
	$post_id = intval($post_id);
	$post_author_id = get_post_field( 'post_author', $post_id );			
	$result = ($UserID == $post_author_id) ? TRUE: FALSE;
	return $result;
}


function wikify_get_user_type() {
	$currentUser = wp_get_current_user();
	$userID = $currentUser->ID;
	
	if($userID == 0){
		$userType = "guest";
	} else {
		if(is_user_member_of_blog()){
			$userType = "site";				
		} else {
			$userType = "network";	
		}
	}
	
	return $userType;
}

// This function is used to determine if a user has access to certain functionality.
// Wikify Basic doesn't offer permission customization, so all users have full wiki permissions.
function wikify_check_user_type_access($key, $pid = null){
	return true;
}

// Wikify Basic doesn't offer "pending" post functionality. So all posts are immediately published.
function wikify_get_publish_status($type){
	return "publish";
}
