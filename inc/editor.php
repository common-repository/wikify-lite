<?php

function wikify_editor($pid = NULL) {
	global $post;
	
	$current_user = wp_get_current_user();
	$username = $current_user->user_login;		 
	$UserID = $current_user->ID;

	if($pid !== null){
		$pid = intval($pid);
	}


	// We're outputing a lot of html and the easiest way 
	// to do it is with output buffering from php.
	ob_start();
 
?>

<div id="wikify-editor-postbox" class="<?php if(is_user_logged_in()) echo 'closed'; else echo 'loggedout'?>">
		<?php do_action( 'wikify-editor-notice' ); ?>
		<?php 			
				# Title
				if(isset($pid) && !empty($pid)) {
					$post = get_post($pid);
					
					$title = esc_html(trim($post->post_title));
					$excerpt = esc_html(trim($post->post_excerpt));
					$content = $post->post_content;
				} else {
					$title = null;
					$excerpt = null;					
					$content = null;
				}		
		?>
		
		<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="fep-new-post" class="wordpress-ajax-form" name="new_post" role="form">
					<?php
					
					if($UserID == 0){
						echo '<p class="wikify-alert wikify-warning" role="alert">You are not logged in. If you publish/edit a post, your IP address ('.$_SERVER['REMOTE_ADDR'].') will be publicly listed. (If you login/create an account you username will be shown instead.)</p>';
					} ?>
					<p><input type="text" id="fep-post-title" name="post-title" value="<?php echo $title; ?>" placeholder="Title" />
					<input type="text" id="fep-post-excerpt" name="post-excerpt" value="<?php echo $excerpt; ?>" placeholder="Excerpt" /></p>
												
					<?php 
					$wp_editor_args = array();
					$wp_editor_args["media_buttons"] = false;
					$wp_editor_args["drag_drop_upload"] = false;						
					wp_editor($content, "post-content", $wp_editor_args); ?>
												
				<div class="wikifyPostFormRow">

					<p class="submitButtonRow"><input type="submit" id="submit-form-button" class="submit-form-button" value="Save" /></p>
		
					<input type="hidden" name="action" value="create_update_post">
					<input type="hidden" id="wikify-post-id" name="pid" value="<?php echo $pid; ?>">			
					<input type="hidden" id="wikify-ajax-url" name="ajax-url" value="<?php echo admin_url('admin-ajax.php'); ?>">			
					<?php wp_nonce_field( 'wikify-post-nonce', 'nonce' ); ?>
					
					<input type="hidden" id="fep-post-external-title" name="external-title" value="" />
		</form>
</div>
<?php
	// Output the content.
	$output = ob_get_contents();
	ob_end_clean();
 
	return  $output;
}
 
// Add the shortcode to WordPress. 
add_shortcode('wikify-editor', 'wikify_editor');
 
 
function wikify_editor_errors(){
?>
<style>
.wikify-editor-error{border:1px solid #CC0000;border-radius:5px;background-color: #FFEBE8;margin: 0 0 16px 0px;padding: 12px;}
</style>
<?php
	global $error_array;
	foreach($error_array as $error){
		echo '<p class="wikify-editor-error">' . $error . '</p>';
	}
}
 
function wikify_editor_notices(){
?>
<style>
.wikify-editor-notice{ border:1px solid #E6DB55;border-radius:5px;background-color: #FFFBCC;margin: 0 0 16px 0px;padding: 12px;}
</style>
<?php
}