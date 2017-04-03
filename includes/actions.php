<?php
		
/**
  * Sort posts by rating DESC and ASC
  *
  * @param array $pieces
  * @param array $query SQL query 
*/
function wpse_exclude_posts_clauses( $pieces, $query ) {
	global $wpdb;
        
	if ( isset( $_GET['ur_sort'] ) && $_GET['ur_sort'] == 'asc' || isset( $_GET['ur_sort'] ) && $_GET['ur_sort'] == 'desc' ) {
		$sort_type = sanitize_text_field( $_GET['ur_sort'] );
	} else {
		$sort_type = 'asc';
	}
	
	$pieces['join'] = "
              LEFT JOIN  $wpdb->postmeta as hidden_meta
                           ON (
                               $wpdb->posts.ID = hidden_meta.post_id
                               AND hidden_meta.meta_key = '_average_page_rating'
                           )
                ";
	$pieces['orderby'] = "hidden_meta.meta_value " . $sort_type;
	
	return $pieces;
}

if ( isset( $_GET['ur_sort'] ) ) {
	add_filter( 'posts_clauses', 'wpse_exclude_posts_clauses', 10, 2 );
}