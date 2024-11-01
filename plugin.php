<?php
/**
 * Plugin Name: Wikify (Basic)
 * Description: Allow users to create and edit content on your site using a frontend editor.
 * Author: bookbinder
 * Version: 1.1.1
 */


require_once dirname( __FILE__ ) . '/src/class.settings-api.php';
require_once dirname( __FILE__ ) . '/settings.php';
require_once dirname( __FILE__ ) . '/inc/views.php';
require_once dirname( __FILE__ ) . '/inc/queries.php';

require_once dirname( __FILE__ ) . '/inc/cpt.php';

require_once dirname( __FILE__ ) . '/inc/tabs.php';
require_once dirname( __FILE__ ) . '/inc/comments.php';
require_once dirname( __FILE__ ) . '/inc/editor.php';
require_once dirname( __FILE__ ) . '/inc/posts.php';

require_once dirname( __FILE__ ) . '/inc/revisions.php';
require_once dirname( __FILE__ ) . '/inc/permissions.php';
require_once dirname( __FILE__ ) . '/inc/sanitize.php';
require_once dirname( __FILE__ ) . '/inc/templates.php';

require_once dirname( __FILE__ ) . '/lib/php-diff/lib/Diff.php';
require_once dirname( __FILE__ ) . '/lib/php-diff/lib/Diff/Renderer/Html/Inline.php';

function wikify_get_plugin_directory(){
	$directory = array();
	
	$directory['path'] = trailingslashit( plugin_dir_path( __FILE__ ) );
	$directory['url'] = plugin_dir_url( __FILE__ );
	return $directory;
}

// https://codex.wordpress.org/Function_Reference/register_activation_hook
// https://codex.wordpress.org/Function_Reference/register_deactivation_hook
// https://wordpress.stackexchange.com/questions/25910/uninstall-activate-deactivate-a-plugin-typical-features-how-to/25979#25979
// http://solislab.com/blog/plugin-activation-checklist/
register_deactivation_hook(__FILE__, 'wikify_deactivation');
register_activation_hook(__FILE__, 'wikify_activation');

function wikify_activation(){
	flush_rewrite_rules();
	wikify_auto_generate_pages();
}

function wikify_deactivation(){
	flush_rewrite_rules();
}


new Wikify_Settings_API();

function wikify_option( $option, $section, $default = '' ) {
 
    $options = get_option( $section );
 
    if ( isset( $options[$option] ) ) {
    return $options[$option];
    }
 
    return $default;
}


# Include wiki css file on single wiki pages. 
function wikify_styles() {
	global $wp_styles;
	
	$pluginDirectory = wikify_get_plugin_directory();

	# REGISTER STYLES
	wp_register_style( 'font-awesome',  $pluginDirectory['url'].'assets/css/font-awesome.min.css', array(), 1 );
    wp_register_style( 'wikify-style',  $pluginDirectory['url'].'assets/css/wiki.css', array(), 1 );


	# REGISTER SCRIPTS

	wp_register_script( "jquery-animate-colors", $pluginDirectory["url"].'assets/js/jquery.animate-colors-min.js', array(), null, true );
	wp_register_script( "moment", $pluginDirectory["url"].'assets/js/moment.js', array(), null, true );
	wp_register_script( "slimscroll", $pluginDirectory["url"].'assets/js/jquery.slimscroll.min.js', array(), null, true );
	wp_register_script( "livestamp", $pluginDirectory["url"].'assets/js/livestamp.min.js', array(), null, true );

	wp_register_script( "wikify", $pluginDirectory["url"].'assets/js/wiki.js', array(), null, true );

	
	$action = isset($_GET['action']) ? $_GET['action'] : null;
	$view = isset($_GET['view']) ? $_GET['view'] : null;

	# ENQUEUE
	wp_enqueue_script('jquery');
	wp_enqueue_style('font-awesome');
	
	# WIKIFY
	if($action == "wikify"){
		
		wp_enqueue_script('jquery-animate-colors');
		wp_enqueue_script('moment');
		wp_enqueue_script('slimscroll');
		wp_enqueue_script('livestamp');
		wp_enqueue_script('bootstrap');

		wp_enqueue_script('wikify');
		
	}
	
	
	wp_enqueue_style('wikify-style');	
	
}
add_action ('wp_enqueue_scripts', 'wikify_styles', 100);



function get_post_by_meta_value($key, $value, $cpt){
	$args = array(
		'post_type' => $cpt,
		'meta_query' => array(
			array(
				'key'     => $key,
				'value'   => $value,
			)
		)
	);
	
	$posts = get_posts($args);
	
	if(!empty($posts)){
		return intval($posts[0]->ID);
	} else {
		return null;
	}
}


function wikify_auto_generate_pages() {
	//get_post_by_meta_value returns "null" when no matches found.
	$createPageID = get_post_by_meta_value("wikifyPageType", "create", "page");
	$mainPageID = get_post_by_meta_value("wikifyPageType", "main", "wiki");

	if($createPageID == null){
		$createPageID = wp_insert_post( 
			array(
				'post_author' => 1,
				'post_name' => 'create',
				'post_status' => 'publish' ,
				'post_title' => 'Create Wiki Page',
				'post_type' => 'page',		
			)
		);
		
		update_post_meta(intval($createPageID), "wikifyPageType", "create");		
	}
	
		
	if($mainPageID == null){
		$mainPageID = wp_insert_post( 
			array(
				'post_author' => 1,
				'post_name' => 'main',
				'post_status' => 'publish' ,
				'post_title' => 'Main Page',
				'post_type' => 'wiki',		
			)
		);

		update_post_meta(intval($mainPageID), "wikifyPageType", "main");
	} 
}


// WIKIFY CREATE PAGES
function wikify_get_pages() {
	$pages = get_posts(
		array(
			'post_type' => "page",
			"posts_per_page" => -1,
			'numberposts' => -1,							
		)
	);
	$pages_options = array();
	if ( $pages ) {
		foreach ($pages as $page) {
			$pagesOptions[$page->ID] = $page->post_title;
		}
	}
	return $pagesOptions;
}

function wikify_user_permission($action) {
	// the action key is a placeholder until additional condition checks are added.
	
	if( current_user_can( 'manage_options' ) ) {
		return true;
	} else {
		return false;
	}
}