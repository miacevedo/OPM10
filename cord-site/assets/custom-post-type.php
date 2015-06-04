<?php
/* All Custom Post Types */

// Flush rewrite rules for custom post types
add_action( 'after_switch_theme', 'bones_flush_rewrite_rules' );

// Flush your rewrite rules
function bones_flush_rewrite_rules() {
	flush_rewrite_rules();
}

// Locations Post Type
function locations_post() { 

	register_post_type( 'locations', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */

		array( 'labels' => array(
			'name' => __( 'Locations', 'bonestheme' ), /* This is the Title of the Group */
			'singular_name' => __( 'Location Post', 'bonestheme' ), /* This is the individual type */
			'all_items' => __( 'All Location Posts', 'bonestheme' ), /* the all items menu item */
			'add_new' => __( 'Add New', 'bonestheme' ), /* The add new menu item */
			'add_new_item' => __( 'Add New Location', 'bonestheme' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'bonestheme' ), /* Edit Dialog */
			'edit_item' => __( 'Edit Location', 'bonestheme' ), /* Edit Display Title */
			'new_item' => __( 'New Location', 'bonestheme' ), /* New Display Title */
			'view_item' => __( 'View Location', 'bonestheme' ), /* View Display Title */
			'search_items' => __( 'Search Locations', 'bonestheme' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'Nothing found in the Database.', 'bonestheme' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'Nothing found in Trash', 'bonestheme' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'This is the example location post type', 'bonestheme' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-location', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'locations', 'with_front' => false ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'sticky')
		) 
		
	);
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'locations_post');
	
	
// Locations Post Type
/*
function service_area_post() { 

		array( 'labels' => array(
			'name' => __( 'Service Areas', 'bonestheme' ), 
			'singular_name' => __( 'Service Area Post', 'bonestheme' ), 
			'all_items' => __( 'All Service Area Posts', 'bonestheme' ),
			'add_new' => __( 'Add New', 'bonestheme' ), 
			'add_new_item' => __( 'Add New Service Area', 'bonestheme' ),
			'edit' => __( 'Edit', 'bonestheme' ), 
			'edit_item' => __( 'Edit Service Area', 'bonestheme' ), 
			'new_item' => __( 'New Service Area', 'bonestheme' ), 
			'view_item' => __( 'View Service Area', 'bonestheme' ),
			'search_items' => __( 'Search Service Areas', 'bonestheme' ),
			'not_found' =>  __( 'Nothing found in the Database.', 'bonestheme' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'bonestheme' ),
			'parent_item_colon' => ''
			), 
			'description' => __( 'This is the example Service Area post type', 'bonestheme' ), 
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 9, 
			'menu_icon' => 'dashicons-location-alt', 
			'rewrite'	=> array( 'with_front' => false ),
			'has_archive' => false,
			'capability_type' => 'post',
			'hierarchical' => false,

			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'sticky')
		) 
		
	);
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'service_area_post');
	*/
	
?>