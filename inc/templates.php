<?php

# CREATE PAGE

add_filter( 'template_include', 'wikify_template_handler', 100 );

function wikify_template_handler( $template ) {
	$createPageID = wikify_option( "create_page_id", "wikify_basic", "0" );
	if(intval($createPageID) == 0) {
		$createPageID = intval(get_post_by_meta_value("wikifyPageType", "create", "page"));
	}
	
	if( is_page() && (get_the_ID() == $createPageID) ) {
		$createPageTemplate = locate_template( 'wikify-create.php' );
		if ($createPageTemplate !== "") {
			return $createPageTemplate ;
		}
	}

	return $template;
}