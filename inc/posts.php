<?php

# CREATE / UPDATE POST
function wikify_create_update_post_processor() {
	$nonce = sanitize_text_field($_POST['nonce']);
	$pid = intval($_POST['pid']);
	$referer = esc_url(home_url().sanitize_text_field($_POST['_wp_http_referer']));
	$referer = add_query_arg('error','1', $referer);
	
	if ( wp_verify_nonce( $nonce, 'wikify-post-nonce' ) ) {
		if(($pid == null) && wikify_check_user_type_access("create_permission")){
			wikify_editor_add_post($_POST);
		} elseif(($pid !== null) && wikify_check_user_type_access("edit_permission")) {
			wikify_editor_add_post($_POST);		
		} else {
		 wp_redirect($referer);		
		}
	} else {
		 wp_redirect($referer);
	}
}
add_action( 'admin_post_nopriv_create_update_post', 'wikify_create_update_post_processor' );
add_action( 'admin_post_create_update_post', 'wikify_create_update_post_processor' );

/**
 * Save post metadata when a post is saved.
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not. 

add_action( 'save_post', 'save_post_data', 10, 3 );
 */


function wikify_editor_add_post($input){
	$current_user = wp_get_current_user();
	$UserID		= intval($current_user->ID);
		
	if( isset($input['pid']) && !empty($input['pid']) ) {
		$pid = intval($input['pid']);	
	} else {
		$pid = null;
	}

	$args = array();

	if(isset($input['post-content']) && !empty($input['post-content'])){
		$args["post_content"] = wikify_sanitize_html($input['post-content']);
	}

	if(isset($input['post-excerpt']) && !empty($input['post-excerpt'])){
		$args["post_excerpt"] = sanitize_text_field($input['post-excerpt']);
	}
	
	$args['post_title'] = sanitize_text_field($input['post-title']); 

	$args['post_author'] = intval($UserID);

	$section = "wiki";
	
	$args['post_type'] = $section; 
	$args['post_status'] = "publish";
	

	if($pid == NULL){
		$post_id = wp_insert_post( $args );
	} else {
		$args['ID'] = intval($pid);
		$post_id = wp_update_post( $args );	
	}

	# Revision IP
	// store the author's IP (especially important for guest/unregistered users)
	update_post_meta($post_id, '_wikify_revision_ip_'.$revisionID, $_SERVER['REMOTE_ADDR']);
	
	wp_redirect(get_permalink($post_id));		
}