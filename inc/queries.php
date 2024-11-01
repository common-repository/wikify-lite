<?php

function wikify_query_filter_create_select_options($arr, $name, $id = null, $class = null){
	
	if($id == null){
		$id = $name;
	}
	
	if($class == null){
		$class = $name;
	}
	
	// name='option[".$name."]' is used so it's easier to handle form parameters while discarding other input fields that are not needed for building a new URL.
	$html = "<select id='".$id."' name='option[".$name."]' class='".$class."' style='float:right;'>";
		foreach($arr as $key => $value){
			// WP doesn't necessarily return query variables as they appear in the address bar. 
			// For example get_query_var("order") will return example.com/?order=desc as "DESC".
			// So we need to convert $value and get_query_var($name) to lowercase strings so they can be accurately compared.
			
			if( strtolower($key) == strtolower(get_query_var($name)) ){
				$selected = " selected='selected '";
			} else {
				$selected = null;
			}
			$html .= "<option value='".$key."'".$selected.">".$value."</option>";
		}
	$html .= "</select> ";
	return $html;
}

function wikify_query_filter_bar(){
	global $wp_query;
	
	

	$html = 
	'<form action="'.esc_url( admin_url('admin-post.php') ).'" method="post" name="queryFilterBar" id="queryFilterBar" class="queryFilterBar" style="border:1px solid #ccc; padding:8px 10px; width:100%; margin-top: 10px; clear:both; margin-bottom:20px;">
		<input type="hidden" name="action" value="query_filter_form_handler">
		<input type="hidden" name="referer" value="'.$_SERVER['REQUEST_URI'].'">'
				
		.wikify_query_filter_create_select_options(
				array( 
					'asc' => 'asc',
					'desc' => 'desc'
				),
				"order"
			)
		.wikify_query_filter_create_select_options(
			array(
				'date' => 'Recently Created',
				'modified' => 'Recently Modified',
				'lastcomment' => 'Recently Discussed',
				'title' => 'Alphabetical',				
			),
			"orderby"
		).
		'<div class="resultsFound">'.$wp_query->found_posts.' results found</div> 
		</form>';	
	return $html;
}





function wikify_query_filter_get_the_taxonomies($posts, $allFields = false) {
		
	$taxonomies = array();

	if ( ! $posts ) {
		return $taxonomies;
	}

	foreach($posts as $post) {
		foreach ( get_object_taxonomies( $post ) as $taxonomy ) {
			$t = (array) get_taxonomy( $taxonomy );

			if ( empty( $t['args'] ) ) {
				$t['args'] = array();
			}
	
			$terms = get_object_term_cache( $post->ID, $taxonomy );
			if ( false === $terms ) {
				$terms = wp_get_object_terms( $post->ID, $taxonomy );
			}
	
			foreach ( $terms as $term ) {
				if($allFields == true){
					$taxonomies[$taxonomy][$term->term_id] = array('term_id' => $term->term_id, "name" => $term->name, "slug" => $term->slug, );
				} else {
					$taxonomies[$taxonomy][] = $term->term_id;
					$taxonomies[$taxonomy] = array_unique($taxonomies[$taxonomy]);
				}
			}

		}
	}	
	return $taxonomies;
}






# PROCESS QUERY FILTER
function wikify_query_filter_form_handler() {
	$referer = $_POST["referer"];
	$redirect = add_query_arg( $_POST["option"], $referer );
	wp_redirect($redirect);
}
add_action( 'admin_post_nopriv_query_filter_form_handler', 'wikify_query_filter_form_handler' );
add_action( 'admin_post_query_filter_form_handler', 'wikify_query_filter_form_handler' );