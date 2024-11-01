<?php

function wikify_content_filter( $content ) {
	global $post;

	// remember: wikify_option will return the specified default value as a fallback, when the field hasn't been saved/set. However, if the field have been deliberately updated as null/empty, the default value will not be returned.
	
	// It's important to check for the pid URL variable first.
	if(isset($_GET['pid'])){
		$pid = intval($_GET['pid']);
	} else {
		$pid = $post->ID;	
	}
	$view = isset($_GET['view']) ? $_GET['view'] : "page";
	$success = isset($_GET['success']) ? $_GET['success'] : null;
	
	$createPageID = wikify_option('create_page_id', 'wikify_basic', null);
	
	if($createPageID == null){
		$createPageID = get_post_by_meta_value("wikifyPageType", "create", "page");
	}

	
	if($view == "edit") {
		$content = wikify_editor($pid);
	} elseif ($pid == $createPageID) { // "Create New Wiki" Page
		$content = wikify_editor();		
	} elseif ($view == "history") {
		$content = 	wikify_display_post_history($pid);
	} elseif ($view == "pending") {
		$content = wikify_pending_posts($pid, $post);
	} elseif ($view == "comments") {
		$content = 	wikify_display_comments($pid);
	} elseif ($view == "diff") {
		$content = wikify_view_diff();
	} elseif ($view == "revision") {
		$content = wikify_display_revision_post($pid);
	}	
	
	if($success == "restored"){
		$content = "<p class='wikify-alert'>Revision successfuly restored.</p>".$content;
	} else if($success == "pending"){
		$content = "<p class='wikify-alert'>Your revision has been submitted for review.</p>".$content;
	}
	
	$status = wikify_option('wiki_navbar', 'wikify_basic', 'enable');
	
	if(is_singular("wiki")){
		ob_start();
		do_action( 'wikify_prepend_content', $pid );
		$content = ob_get_clean().$content;
    }
    
    return $content;
}

add_filter( 'the_content', 'wikify_content_filter', 20 );


# VIEW DIFF
function wikify_view_diff(){
	$oid = intval($_GET["oid"]);
	$nid = intval($_GET["nid"]);
	

	
	$old = explode("\n", get_post($oid)->post_content);
	$new = explode("\n", get_post($nid)->post_content);

	// Options for generating the diff
	$options = array(
		//'ignoreWhitespace' => true,
		//'ignoreCase' => true,
	);
	
	$diff = new Diff($old, $new, $options);

	// Generate an inline diff
	$renderer = new Diff_Renderer_Html_Inline;
	$content = $diff->render($renderer);
	
	return $content;
}