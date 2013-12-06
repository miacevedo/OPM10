<?PHP

function dot_irt_top_posts ( $atts ) {
 
// get our variable from $atts
extract(shortcode_atts(array(
'container' => 'li',
'number' => '10',
'post_type' => 'post',
'year' => '',
'monthnum' => '',
'show_count' => '1',
), $atts));
 
global $wpdb;
 
$request = "SELECT * FROM $wpdb->posts, $wpdb->postmeta";
$request .= " WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id";
 
if ($year != '') {
$request .= " AND YEAR(post_date) = '$year'";
}
 
if ($monthnum != '') {
$request .= " AND MONTH(post_date) = '$monthnum'";
}
 
$request .= " AND post_status='publish' AND post_type='$post_type' AND meta_key='_recommended'";
$request .= " ORDER BY $wpdb->postmeta.meta_value+0 DESC LIMIT $number";
$posts = $wpdb->get_results($request);
 
$return = '';
 
 
foreach ($posts as $item) {
$post_title = stripslashes($item->post_title);
$permalink = get_permalink($item->ID);
$post_count = do_shortcode("[dot_recommends ID='$item->ID']");
$url = get_post_meta( $item->ID, 'web-url', true );
// $opm_excerpt = get_the_excerpt($item->ID, '11');
$categories = get_the_term_list( $item->ID, 'category', '<p class="tags">', '&nbsp;&#47;&nbsp; ', '</p>' );
 
$return .= '<li class="gallery_post">';

$return .= '<a href="' . $permalink . '" title="' . $post_title.' ">';

$return .= get_the_post_thumbnail($item->ID, 'medium');

$return .= '</a>';

$return .= '<div class="site_info">';
$return .= '<div class="site_title">';

$return .= '<h3><a href="' . $url . '" rel="nofollow" target="_blank" title="Visit ' . $post_title.' ">' . $post_title.'<i class="icon-export"></i></a></h3>';

$return .= '<p><small>';
$return .= human_time_diff( get_the_time('U', $item->ID), current_time('timestamp') ) . ' ago';
$return .= '</small></p>';

$return .= '</div>';

$return .= $post_count . ' <hr />';

$return .= $categories ;

// $return .= ' <hr /> <p class="excerpt">' . $opm_excerpt . '</p>';

$return .= '</div>';

$return .= '</li>';
 
}
return '<ul class="two_up tiles">' . $return . '</ul>';
 
}
add_shortcode('irt_top_posts','dot_irt_top_posts');      

?>