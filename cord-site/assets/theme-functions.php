<?php
/* Welcome to Bones :)
This is the core Bones file where most of the
main functions & features reside. If you have
any custom functions, it's best to put them
in the functions.php file.

Developed by: Eddie Machado
URL: http://themble.com/bones/

  - head cleanup (remove rsd, uri links, junk css, ect)
  - enqueueing scripts & styles
  - theme support functions
  - custom menu output & fallbacks
  - related post function
  - page-navi function
  - removing <p> from around images
  - customizing the post excerpt

*/

/*********************
WP_HEAD GOODNESS
The default wordpress head is
a mess. Let's clean it up by
removing all the junk we don't
need.
*********************/

function bones_head_cleanup() {
	// category feeds
	// remove_action( 'wp_head', 'feed_links_extra', 3 );
	// post and comment feeds
	// remove_action( 'wp_head', 'feed_links', 2 );
	// EditURI link
	remove_action( 'wp_head', 'rsd_link' );
	// windows live writer
	remove_action( 'wp_head', 'wlwmanifest_link' );
	// previous link
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	// start link
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	// links for adjacent posts
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	// WP version
	remove_action( 'wp_head', 'wp_generator' );
	// remove WP version from css
	add_filter( 'style_loader_src', 'bones_remove_wp_ver_css_js', 9999 );
	// remove Wp version from scripts
	add_filter( 'script_loader_src', 'bones_remove_wp_ver_css_js', 9999 );

} /* end bones head cleanup */

// A better title
// http://www.deluxeblogtips.com/2012/03/better-title-meta-tag.html
function rw_title( $title, $sep, $seplocation ) {
  global $page, $paged;

  // Don't affect in feeds.
  if ( is_feed() ) return $title;

  // Add the blog's name
  if ( 'right' == $seplocation ) {
    $title .= get_bloginfo( 'name' );
  } else {
    $title = get_bloginfo( 'name' ) . $title;
  }

  // Add the blog description for the home/front page.
  $site_description = get_bloginfo( 'description', 'display' );

  if ( $site_description && ( is_home() || is_front_page() ) ) {
    $title .= " {$sep} {$site_description}";
  }

  // Add a page number if necessary:
  if ( $paged >= 2 || $page >= 2 ) {
    $title .= " {$sep} " . sprintf( __( 'Page %s', 'dbt' ), max( $paged, $page ) );
  }

  return $title;

} // end better title

// remove WP version from RSS
function bones_rss_version() { return ''; }

// remove WP version from scripts
function bones_remove_wp_ver_css_js( $src ) {
	if ( strpos( $src, 'ver=' ) )
		$src = remove_query_arg( 'ver', $src );
	return $src;
}

// remove injected CSS for recent comments widget
function bones_remove_wp_widget_recent_comments_style() {
	if ( has_filter( 'wp_head', 'wp_widget_recent_comments_style' ) ) {
		remove_filter( 'wp_head', 'wp_widget_recent_comments_style' );
	}
}

// remove injected CSS from recent comments widget
function bones_remove_recent_comments_style() {
	global $wp_widget_factory;
	if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
		remove_action( 'wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style') );
	}
}

// remove injected CSS from gallery
function bones_gallery_style($css) {
	return preg_replace( "!<style type='text/css'>(.*?)</style>!s", '', $css );
}

/*********************
BLOG BREADCRUMBS
*********************/
function the_breadcrumb()	{
$separator = '<span class="separator">»</span>';
$name = __("Home");

echo '<div id="breadcrumb"><span class="breadcrumbs pathway">';

if (!is_home() && !is_front_page() /*&& get_post_type() == $type*/ || is_paged()) {

	global $post;
	$home = get_bloginfo('url');
	$blog = '<a class="pathway" href="' . $home . '/blog/">Blog</a> ' . $separator . '';
	$locations = '<a class="pathway" href="' . $home . '/locations/">Locations</a> ' . $separator . '';
			
	echo '<a class="pathway" href="' . $home . '">' . $name . '</a> ' . $separator . '';
	
	if (is_category()) {
		global $wp_query;
		$cat_obj = $wp_query->get_queried_object();
		$thisCat = $cat_obj->term_id;
		$thisCat = get_category($thisCat);
		$parentCat = get_category($thisCat->parent);
		
		echo $blog;
		
		if ($thisCat->parent != 0) {
			echo(get_category_parents($parentCat, true, '' . $separator . ''));
		}
		
		echo single_cat_title();
	}
	
	else if (is_day()) {
		
		echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $separator . '';
		echo '<a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $separator . ' ';
		echo get_the_time('d');
	
	} else if (is_month()) {
		
		echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $separator . '';
		echo get_the_time('F');
	
	} else if (is_year()) {

		echo get_the_time('Y');
	
	} else if (is_attachment()) {
		
		the_title();
	
	} if (is_single() && get_post_type() == 'post' ){

		echo $blog;
		the_title();
	
	} else if (is_page() && !$post->post_parent) {
		
		the_title();
	
	} else if (is_page() && $post->post_parent) {
		$parent_id = $post->post_parent;
		$breadcrumbs = array();
		while ($parent_id) {
			$page = get_page($parent_id);
			$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
			$parent_id = $page->post_parent;
		}
		$breadcrumbs = array_reverse($breadcrumbs);
		foreach($breadcrumbs as $crumb)
		echo $crumb . ' ' . $separator . ' ';

		the_title();
	
	} else if (is_search()) {
		
		echo 'Search Results for <strong>'; 
	    echo get_search_query();
	    echo'</strong>';
	}
	
	else if ( is_singular( 'locations' ) ) {

		echo $locations;
		the_title();
		
	}
	
	 else if (is_tag()) {
		
		echo $blog;
		echo single_tag_title();
	
	} else if (is_author()) {
		global $author;
		$userdata = get_userdata($author);
		echo $userdata->display_name;
	
	} else if (is_404()) {
		echo '404 Not Found';
	}
	
	if (get_query_var('paged')) {
		if (is_home() || is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
			echo ' ';
		}
		
		echo __('Page','limelight') . ' ' . get_query_var('paged');
		if (is_home() || is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {

		}
		
	}
}

	else if ( is_home() ) {

		echo '<a class="pathway" href="' . $home . '">' . $name . '</a> ' . $separator . '';
		echo 'Blog';
		
	}

 echo '</span></div>';// Final Closing Tags
 
}


/*********************
SCRIPTS & ENQUEUEING
*********************/

// loading modernizr and jquery, and reply script
function bones_scripts_and_styles() {

  global $wp_styles; // call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way

  if (!is_admin()) {

		// modernizr (without media query polyfill)
		wp_register_script( 'bones-modernizr', get_stylesheet_directory_uri() . '/assets/js/libs/modernizr.custom.min.js', array(), '2.5.3', false );
		wp_register_script( 'script-noconflict', get_stylesheet_directory_uri() . '/assets/js/tools/noconflict.js', array(), '1.1', false );
		wp_register_script( 'script-cookie', get_stylesheet_directory_uri() . '/assets/js/menus/jquery.cookie.js', array(), '1.1', false );
		wp_register_script( 'script-superfish', get_stylesheet_directory_uri() . '/assets/js/menus/superfish.js', array(), '1.1', false );
		wp_register_script( 'script-hoverIntent', get_stylesheet_directory_uri() . '/assets/js/menus/jquery.hoverIntent.minified.js', array(), '1.1', false );
		wp_register_script( 'script-slide', get_stylesheet_directory_uri() . '/assets/js/effects/slide.js', array(), '1.1', false );
		wp_register_script( 'script-hiddenpanel', get_stylesheet_directory_uri() . '/assets/js/modal/hiddenpanel.js', array(), '1.1', false );
		wp_register_script( 'script-template', get_stylesheet_directory_uri() . '/assets/js/template/template.js', array(), '1.1', false );
		wp_register_script( 'script-core', get_stylesheet_directory_uri() . '/assets/js/core.js', array(), '1.1', false );
		wp_register_script( 'script-mootools-core', get_stylesheet_directory_uri() . '/assets/js/mootools-core.js', array(), '1.1', false );
		wp_register_script( 'script-mootools-more', get_stylesheet_directory_uri() . '/assets/js/mootools-more.js', array(), '1.1', false );
		wp_register_script( 'script-modal', get_stylesheet_directory_uri() . '/assets/js/modal.js', array(), '1.1', false );
		wp_register_script( 'script-k2', get_stylesheet_directory_uri() . '/assets/js/compontents/k2.js', array(), '1.1', false );
		wp_register_script( 'script-caption', get_stylesheet_directory_uri() . '/assets/js/caption.js', array(), '1.1', false );
		wp_register_script( 'script-masonry', get_stylesheet_directory_uri() . '/assets/js/jquery.masonry.js', array(), '1.1', false );
		wp_register_script( 'script-sidebar', get_stylesheet_directory_uri() . '/assets/js/sidebar.js', array(), '1.1', false );
		wp_register_script( 'script-cycle', get_stylesheet_directory_uri() . '/assets/js/jquery.cycle.lite.js', array(), '1.1', false );

		// register main stylesheet
		wp_register_style( 'stylesheet-global', get_stylesheet_directory_uri() . '/assets/css/global.css', array(), '', 'all' );
		/*
		wp_register_style( 'stylesheet-modal', get_stylesheet_directory_uri() . '/assets/css/modal/modal.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-k2', get_stylesheet_directory_uri() . '/assets/css/k2.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-base', get_stylesheet_directory_uri() . '/assets/css/base.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-grid', get_stylesheet_directory_uri() . '/assets/css/grid.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-type', get_stylesheet_directory_uri() . '/assets/css/type.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-forms', get_stylesheet_directory_uri() . '/assets/css/forms.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-superfish', get_stylesheet_directory_uri() . '/assets/css/superfish.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-modal-default', get_stylesheet_directory_uri() . '/assets/css/modal/default.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-fonts', get_stylesheet_directory_uri() . '/assets/css/fonts.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-theme', get_stylesheet_directory_uri() . '/assets/css/theme.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-hiliteSummer', get_stylesheet_directory_uri() . '/assets/css/hiliteSummer.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-paletteSummer', get_stylesheet_directory_uri() . '/assets/css/paletteSummer.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-mediaqueries', get_stylesheet_directory_uri() . '/assets/css/mediaqueries.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-custom', get_stylesheet_directory_uri() . '/assets/css/custom.css', array(), '', 'all' );
		wp_register_style( 'stylesheet-zentools', get_stylesheet_directory_uri() . '/assets/css/zentools.css', array(), '', 'all' );
		*/	
		
		// ie-only style sheet
		wp_register_style( 'bones-ie-only', get_stylesheet_directory_uri() . '/assets/css/ie.css', array(), '' );

    // comment reply script for threaded comments
    if ( is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
		  wp_enqueue_script( 'comment-reply' );
    }

		//adding scripts file in the footer
		wp_register_script( 'bones-js', get_stylesheet_directory_uri() . '/assets/js/scripts.js', array( 'jquery' ), '', true );

		// enqueue styles and scripts
		
		wp_enqueue_style( 'stylesheet-global' );
		
		/*
		wp_enqueue_style( 'stylesheet-modal' );
		wp_enqueue_style( 'stylesheet-k2' );
		wp_enqueue_style( 'stylesheet-base' );
		wp_enqueue_style( 'stylesheet-grid' );		
		wp_enqueue_style( 'stylesheet-type' );
		wp_enqueue_style( 'stylesheet-forms' );
		wp_enqueue_style( 'stylesheet-superfish' );
		wp_enqueue_style( 'stylesheet-modal-default' );
		wp_enqueue_style( 'stylesheet-fonts' );
		wp_enqueue_style( 'stylesheet-theme' );
		wp_enqueue_style( 'stylesheet-hiliteSummer' );		
		wp_enqueue_style( 'stylesheet-paletteSummer' );
		wp_enqueue_style( 'stylesheet-mediaqueries' );
		wp_enqueue_style( 'stylesheet-custom' );
		wp_enqueue_style( 'stylesheet-zentools' );
		*/
		
		wp_enqueue_style( 'bones-ie-only' );

		$wp_styles->add_data( 'bones-ie-only', 'conditional', 'lt IE 9' ); // add conditional wrapper around ie stylesheet

		/*
		I recommend using a plugin to call jQuery
		using the google cdn. That way it stays cached
		and your site will load faster.
		*/
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'bones-js' );
		
		
		wp_enqueue_script( 'bones-modernizr' );
		wp_enqueue_script( 'script-noconflict' );
		wp_enqueue_script( 'script-cookie' );
		wp_enqueue_script( 'script-superfish' );
		wp_enqueue_script( 'script-hoverIntent' );
		wp_enqueue_script( 'script-slide' );
		wp_enqueue_script( 'script-hiddenpanel' );
		wp_enqueue_script( 'script-template' );
		wp_enqueue_script( 'script-core' );
		wp_enqueue_script( 'script-mootools-core' );
		wp_enqueue_script( 'script-mootools-more' );
		wp_enqueue_script( 'script-modal' );
		wp_enqueue_script( 'script-k2' );
		wp_enqueue_script( 'script-caption' );
		wp_enqueue_script( 'script-masonry' );
		wp_enqueue_script( 'script-sidebar' );
		wp_enqueue_script( 'script-cycle' );

	}
}

//Making jQuery Google API
function modify_jquery() {
	if (!is_admin()) {
		// comment out the next two lines to load the local copy of jQuery
		wp_deregister_script('jquery');
		wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js', false, '1.8.1');
		wp_enqueue_script('jquery');
	}
}
add_action('init', 'modify_jquery');

/*********************
THEME SUPPORT
*********************/

// Adding WP 3+ Functions & Theme Support
function bones_theme_support() {

	// wp thumbnails (sizes handled in functions.php)
	add_theme_support( 'post-thumbnails' );

	// default thumb size
	set_post_thumbnail_size(125, 125, true);

	// wp custom background (thx to @bransonwerner for update)
	add_theme_support( 'custom-background',
	    array(
	    'default-image' => '',    // background image default
	    'default-color' => '',    // background color default (dont add the #)
	    'wp-head-callback' => '_custom_background_cb',
	    'admin-head-callback' => '',
	    'admin-preview-callback' => ''
	    )
	);

	// rss thingy
	add_theme_support('automatic-feed-links');

	// to add header image support go here: http://themble.com/support/adding-header-background-image-support/

	// wp menus
	add_theme_support( 'menus' );

	// registering wp3+ menus
	register_nav_menus(
		array(
			'main-nav' => __( 'The Main Menu', 'bonestheme' ),
			'sidebar-nav-1' => __( 'Sidebar "Menu"', 'bonestheme' ),
			'sidebar-nav-2' => __( 'Sidebar "Services"', 'bonestheme' ),
			'footer-nav-1' => __( 'Footer Resources', 'bonestheme' ),
			'footer-nav-2' => __( 'Footer Locations', 'bonestheme' ),
			'footer-nav-3' => __( 'Footer Services', 'bonestheme' ),
			'footer-nav-4' => __( 'Footer STL', 'bonestheme' ),
			'footer-nav-5' => __( 'Footer Links', 'bonestheme' )
		)
	);

	// Enable support for HTML5 markup.
	add_theme_support( 'html5', array(
		'comment-list',
		'search-form',
		'comment-form'
	) );

} /* end bones theme support */


/*********************
RELATED POSTS FUNCTION
*********************/

// Related Posts Function (call using bones_related_posts(); )
function bones_related_posts() {
	echo '<ul id="bones-related-posts">';
	global $post;
	$tags = wp_get_post_tags( $post->ID );
	if($tags) {
		foreach( $tags as $tag ) {
			$tag_arr .= $tag->slug . ',';
		}
		$args = array(
			'tag' => $tag_arr,
			'numberposts' => 5, /* you can change this to show more */
			'post__not_in' => array($post->ID)
		);
		$related_posts = get_posts( $args );
		if($related_posts) {
			foreach ( $related_posts as $post ) : setup_postdata( $post ); ?>
				<li class="related_post"><a class="entry-unrelated" href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></li>
			<?php endforeach; }
		else { ?>
			<?php echo '<li class="no_related_post">' . __( 'No Related Posts Yet!', 'bonestheme' ) . '</li>'; ?>
		<?php }
	}
	wp_reset_postdata();
	echo '</ul>';
} /* end bones related posts function */

/*********************
PAGE NAVI
*********************/

// Numeric Page Navi (built into the theme by default)
function bones_page_navi() {
  global $wp_query;
  $bignum = 999999999;
  if ( $wp_query->max_num_pages <= 1 )
    return;
  echo '<nav class="pagination">';
  echo paginate_links( array(
    'base'         => str_replace( $bignum, '%#%', esc_url( get_pagenum_link($bignum) ) ),
    'format'       => '',
    'current'      => max( 1, get_query_var('paged') ),
    'total'        => $wp_query->max_num_pages,
    'prev_text'    => '&larr;',
    'next_text'    => '&rarr;',
    'type'         => 'list',
    'end_size'     => 3,
    'mid_size'     => 3
  ) );
  echo '</nav>';
} /* end page navi */

/*********************
RANDOM CLEANUP ITEMS
*********************/

// remove the p from around imgs (http://css-tricks.com/snippets/wordpress/remove-paragraph-tags-from-around-images/)
function bones_filter_ptags_on_images($content){
	return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}

// This removes the annoying […] to a Read More link
function bones_excerpt_more($more) {
	global $post;
	// edit here if you like
	return '...  <a class="excerpt-read-more" href="'. get_permalink( $post->ID ) . '" title="'. __( 'Read ', 'bonestheme' ) . esc_attr( get_the_title( $post->ID ) ).'">'. __( 'Read more &raquo;', 'bonestheme' ) .'</a>';
}

// Set Excerpt Length
function excerpt($limit) {
$excerpt = explode(' ', get_the_excerpt(), $limit);
if (count($excerpt)>=$limit) {
array_pop($excerpt);
$excerpt = implode(" ",$excerpt).'...';
} else {
$excerpt = implode(" ",$excerpt);
}	
$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
return $excerpt;
}
 
function content($limit) {
$content = explode(' ', get_the_content(), $limit);
if (count($content)>=$limit) {
array_pop($content);
$content = implode(" ",$content).'...';
} else {
$content = implode(" ",$content);
}	
$content = preg_replace('/\[.+\]/','', $content);
$content = apply_filters('the_content', $content);
$content = str_replace(']]>', ']]&gt;', $content);
return $content;
} 


?>
