<?php

function wikify_display_post_history($pid){
	$pid = intval($pid);
	$permalink = get_the_permalink($pid);

	$history = wp_get_post_revisions($pid);

	$html = "<p><small>*The first item is the current version of the post.</small></p> <div id='historyWrapper'> <div id='revisionTableHeader'> <div class='wikifyHistoryNote'>Note</div> <div class='wikifyHistoryDate'>Date</div> <div class='wikifyHistoryAuthor'>Author</div> <div class='wikifyHistoryOld'>Old</div> <div class='wikifyHistoryNew'>New</div> </div><form action='".get_the_permalink($pid)."' method='GET'><input type='hidden' name='action' value='wikify'> <input type='hidden' name='view' value='diff'>";
	
	$count = 0;
	
	foreach($history as $h){
	
		$count++;
		
		if($h->post_author == 0){
			$author = get_post_meta($h->post_parent, '_wikify_revision_ip_'.$h->ID, true);
		} else {
			$userData = get_userdata( $h->post_author );
			$author = "@".$userData->user_login;		
		}
		
		$revisionNote = get_post_meta($pid, '_wikify_revision_note_'.$h->ID, true);
		
		if( !isset($revisionNote) || (isset($revisionNote) && empty($revisionNote)) ){
			$revisionNote = "<span class='noNote'>No note provided.</span>";
		}
		
		// Don't show the restore link on the first item since that is the current version.
		if($count > 1){
			$restoreLink = "<a href='".esc_url( admin_url('admin-post.php') )."?action=wikify_restore_revision&rid=".$h->ID."' class='wikify-restore-link'>[restore]</a>";
		} else {
			$restoreLink = null;
		}
		
		$html .= "<div class='historyItem'> <div class='wikifyHistoryItemNote'><a href='".$permalink."?action=wikify&view=revision&pid=".$h->ID."'>".$revisionNote."</a> ".$restoreLink."</div> <div class='wikifyHistoryItemDate'><a href='".$permalink."?action=wikify&view=revision&pid=".$h->ID."'>".$h->post_date."</a></div> <div class='wikifyHistoryItemAuthor'>".$author."</div> <div class='wikifyHistoryItemOld'><input type='radio' name='oid' value='".$h->ID."'></div> <div class='wikifyHistoryItemNew'><input name='nid' type='radio' value='".$h->ID."'></div> </div>";
	}
	
	$html .= "</div><p class='text-right wikifyHistoryRowSubmit'><input type='submit' value='View Diff'></p></form>";
	
	return $html;
}

function wikify_display_revision_post($revisionID) {
	$revision = get_post($revisionID);
	$permission['approve'] = wikify_user_permission("approve_pending_post");
	$permission['restore'] = wikify_user_permission("restore_post_revision");		
	
	$userData = get_userdata( $revision->post_author );
	if($revision->post_author == 0){
		$author = get_post_meta($revision->post_parent, '_wikify_revision_ip_'.$revisionID, true);	
	} else {
		$author = '@'.$userData->user_login.' ('.$userData->display_name.')';	
	}

	$content = '<div class="alert alert-warning" role="alert">This is an earlier version of "'.get_the_title($revision->post_parent).'" -- modified by '.$author.' on '.$revision->post_date.'. (The current version may differ significantly.)</div>';
	
	$revisionNote = get_post_meta($revision->post_parent, '_wikify_revision_note_'.$revisionID, true);
	
	if(isset($revisionNote) && !empty($revisionNote)){
		$content .= "<p>Revision Note: ".$revisionNote."</p>"; //post_parent is the "real/original" post id for the current revision post.
	}
	

	// pid and rid/nid values are NOT a mistake.			
	
	if(isset($_GET["type"]) && ($_GET["type"] == "pending")){
		if($permission['approve']){
			$content .= "<p class='text-right'><a href='".esc_url( admin_url('admin-post.php') )."?action=wikify_approve_changes&rid=".$revisionID."'>Approve This Draft</a></p>";
		}
	} else {
		if($permission['restore']) {
			$content .= "<p class='text-right'><a href='".esc_url( admin_url('admin-post.php') )."?action=wikify_restore_revision&rid=".$revisionID."'>Restore This Revision</a></p>";
		}
	}
	
	$content .= get_post_meta($revision->post_parent, '_wikify_revision_note_'.$revisionID, true)."<hr>"; //post_parent is the "real/original" post id for the current revision post.
	
	$content .= $revision->post_content;
	
	return $content;
}


# RESTORE REVISION
function wikify_restore_revision() {
	if(wikify_user_permission("restore_post_revision")){
		$pid = intval($_GET['pid']);
		$rid = intval($_GET['rid']);
		
		$revision = get_post($rid);
		
		wp_update_post( 
			array (
				"ID" => $pid,
				"post_title" => $revision->post_title,
				"post_content" => $revision->post_content,
			)	
		);
		
		$revisions = wp_get_post_revisions($pid);
		$revisionID = array_values($revisions)[0]->ID;
		update_post_meta($pid, '_wikify_revision_note_'.$revisionID, "Restored revision.");	
		update_post_meta($pid, 'wikify_revision_note', "Restored revision.");
				
		wp_redirect(get_post_permalink($pid));
	}
	
}
add_action( 'admin_post_nopriv_wikify_restore_revision', 'wikify_restore_revision' );
add_action( 'admin_post_wikify_restore_revision', 'wikify_restore_revision' );

