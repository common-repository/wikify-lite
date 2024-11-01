<?php

add_action( 'init', 'wikify_cpt' );

/**
 * Register "wiki" post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function wikify_cpt() {
	$labels = array(
		'name'               => _x( 'Wiki', 'post type general name', 'wikify' ),
		'singular_name'      => _x( 'Wiki', 'post type singular name', 'wikify' ),
		'menu_name'          => _x( 'Wiki', 'admin menu', 'wikify' ),
		'name_admin_bar'     => _x( 'Wiki', 'add new on admin bar', 'wikify' ),
		'add_new'            => _x( 'Add New', 'wiki', 'wikify' ),
		'add_new_item'       => __( 'Add New Wiki', 'wikify' ),
		'new_item'           => __( 'New Wiki', 'wikify' ),
		'edit_item'          => __( 'Edit Wiki', 'wikify' ),
		'view_item'          => __( 'View Wiki', 'wikify' ),
		'all_items'          => __( 'All Wikis', 'wikify' ),
		'search_items'       => __( 'Search Wikis', 'wikify' ),
		'parent_item_colon'  => __( 'Parent Wikis:', 'wikify' ),
		'not_found'          => __( 'No wikis found.', 'wikify' ),
		'not_found_in_trash' => __( 'No wikis found in Trash.', 'wikify' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Wikify custom post type.', 'wikify' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'wiki' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => true,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions', 'trackbacks', 'custom-fields' )
	);

	register_post_type( 'wiki', $args );
}