<?php

# COMMENTS LIST
function wikify_display_comments($pid, $postForm = TRUE) {
	if($postForm == TRUE){
		$form = wikify_display_comments_form($pid);
	} else {
		$form = NULL;
	}
	
	$comments = get_comments(array(
		'post_id' => intval($pid),
		'status' => 'approve'
		)
	);
	
	$html = "";
	
	foreach($comments as $c) {
		if($c->user_id == 0){
			$commentAuthor = $c->comment_author_IP;
		} else {
			$commentAuthor = esc_html($c->comment_author);
		}
		
		$html .= '<li><div class="commentItem">';
		$html .= '<div class="commentSideBar">'.get_avatar( $c->comment_ID, 50 ).'</div>';
		$html .= '<div class="commentMainArea"><span class="commentAuthor">'.$commentAuthor.'</span> <span class="commentTime"  data-livestamp="'.strtotime($c->comment_date_gmt).'"></span> <div class="commentContent">'.$c->comment_content.'</div></div>';
		$html .= '</div></li>';
	}
	
	$html = '<div id="commentsWrapper"><ol id="wikifyCommentsList">
		'.$html.'
	</ol>'.$form.'</div>';
	
	return $html;
}

# COMMENTS FORM

function wikify_display_comments_form($pid) {
	$current_user = wp_get_current_user();
	$UserID		= intval($current_user->ID);
	
	$html = "";
	$html .= '<div id="commentFormRow">';

	if($UserID == 0){		
		$html .= '<div class="alert alert-warning" role="alert">You are not logged in. If you publish/edit a comment, your IP address ('.$_SERVER['REMOTE_ADDR'].') will be publicly listed. (If you login/create an account you username will be shown instead.)</div>';
	}
	
	$html .= '<form action="'.admin_url("admin-ajax.php").'" id="wikifyCommentsForm" method="POST">';
	$html .= '<div class="commentSideBar"></div>';
	$html .= '<div class="commentMainArea"><textarea name="content" id="wikifyCommentFormContent"></textarea></div>';
	$html .= '<p class="text-right"><input type="submit" value="Save" id="submitWikifyComment" class="btn btn-primary"></p>';
	$html .= '<input type="hidden" name="action" value="wikify_post_comment" />';
	$html .= '<input type="hidden" name="pid" value="'.intval($pid).'" />';
	$html .= '<input type="hidden" name="nonce" value="'.wp_create_nonce('wikify-comment-nonce').'" />';
	$html .= '</form>';
	$html .= '</div>';
	return $html;
}


// http://www.wprecipes.com/wordpress-hack-insert-comments-programatically/
add_action('wp_ajax_wikify_post_comment', 'wikify_post_comment');
add_action('wp_ajax_nopriv_wikify_post_comment', 'wikify_post_comment');

function wikify_post_comment() {
	$nonce = sanitize_text_field($_POST['nonce']);
	$referer = esc_url(home_url().sanitize_text_field($_POST['_wp_http_referer']));
	$referer = add_query_arg('error','1', $referer);
	
	$access = wikify_check_user_type_access("comment_permission");
	
	if( wp_verify_nonce( $nonce, 'wikify-comment-nonce' ) && $access ) {
		$current_user = wp_get_current_user();
		$UserID		= intval($current_user->ID);
		$pid = intval($_POST['pid']);
		
		if($UserID == 0){
			$commentAuthor = $_SERVER['REMOTE_ADDR'];
		} else {
			$email = sanitize_email($current_user->user_email);
			$commentAuthor = $current_user->user_login;		
		}
		
		$content = wikify_sanitize_html($_POST['content']);
		
		$data = array(
			'comment_post_ID' => $pid,
			'comment_author' => 'admin',
			'comment_author_email' => $email,
			'comment_content' => $content,
			'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
			'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
			'comment_date' => date('Y-m-d H:i:s'),
			'comment_date_gmt' => date('Y-m-d H:i:s'),
			'user_id' => $UserID,			
			'comment_approved' => 1,
		);
		
		$comment_id = wp_insert_comment($data);
		
		$html  = "";	
		$html .= '<li><div class="commentItem">';
		$html .= '<div class="commentSideBar">'.get_avatar( $comment_id, 50 ).'</div>';
		$html .= '<div class="commentMainArea"><span class="commentAuthor">'.$commentAuthor.'</span> <span class="commentTime"  data-livestamp="'.time().'"></span> <div class="commentContent">'.$content.'</div></div>';
		$html .= '</div></li>';	
		
		$result['commentID'] = $comment_id;
		$result['status'] = "success";	
		$result['html'] = $html;
	} else {
		$result['status'] = "error";
		 wp_redirect($referer);
	}

	echo json_encode($result);
	exit();	
}	
	
