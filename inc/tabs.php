<?php

function wikify_get_current_view($current, $name) {
	if(!is_array($name)){
		$name = array($name);
	}
	
	if(in_array($current, $name)){
		return 'class="current-wikify-tab"';
	}
	
	return null;
}

function wikify_tabs($pid) {
// https://www.sitepoint.com/add-advanced-search-wordpress-site/

	$baseURL = esc_url( get_the_permalink($pid) )."?action=wikify&view=";
	$cptArchiveURL = get_post_type_archive_link("wiki");
	$pendingCount = intval(get_post_meta($pid, "pending_count", true));
	
	$createPageID = wikify_option('create_page_id', 'wikify_basic', null);
	
	if($createPageID == null){
		$createPageID = intval(get_post_by_meta_value("wikifyPageType", "create", "page"));
	}
	
	$current = isset($_GET['view']) ? $_GET['view'] : "page";
	
	$html = '<ul class="wikify-tabs">';
	$html.= '<li '.wikify_get_current_view($current, "page").'><a href="'.get_the_permalink($pid).'"><i class="fa fa-file" aria-hidden="true"></i> Page</a></li>';

	
	
	$html .= '	
		<li '.wikify_get_current_view($current, "edit").'><a href="'.$baseURL.'edit&pid='.$pid.'"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a></li>
				
		<li '.wikify_get_current_view($current, array("revision","history", "diff")).'><a href="'.$baseURL.'history&pid='.$pid.'"><i class="fa fa-calendar" aria-hidden="true"></i> History</a></li>';
							
	$html .=	'<li '.wikify_get_current_view($current, "comments").'><a href="'.$baseURL.'comments&pid='.$pid.'"><i class="fa fa-comments" aria-hidden="true"></i> Discuss</a></li>';
	
	if($pendingCount > 0){
		$html.= '<li '.wikify_get_current_view($current, "pending").'><a href="'.$baseURL.'pending&pid='.$pid.'"><i class="fa fa-bell" aria-hidden="true"></i> Pending ('.$pendingCount.')</a></li>';
	}
	
	/*
	$html .= '<li style="float:right;" class="menu-item-has-children"><a href="#"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></a>
			<ul class="sub-menu">
				<li><a href="'.$cptArchiveURL.'">Recently Created</a></li>
				<li><a href="'.$cptArchiveURL.'?orderby=modified">Recently Updated</a></li>
				<li><a href="'.$cptArchiveURL.'?orderby=lastcomment">Recently Discussed</a></li>
				<li><a href="'.$cptArchiveURL.'?orderby=title&order=asc">Alphabetical</a></li>		
			</ul>
		</li>';
	*/
		
		
	$html .= '<li style="float:right;" '.wikify_get_current_view($current, "create").'><a href="'.get_the_permalink($createPageID).'"><i class="fa fa-plus" aria-hidden="true"></i> Create</a></li>';
		
	$html .= '</ul><hr class="wikify-tabs-rule" />';
	
	

	return $html;
}


function wikify_auto_tabs($pid){
	echo wikify_tabs($pid);
}

add_action('wikify_prepend_content','wikify_auto_tabs');