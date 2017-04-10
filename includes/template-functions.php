<?php

/*
* Posts sorting by rating DESC and ASC drop down
*/
function elm_rml_ratings_sortby_dropdown() {
	$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
	$sort_desc_url = add_query_arg( array(
		'ur_sort' => 'desc' 
	), $current_url );
	$sort_asc_url  = add_query_arg( array(
		'ur_sort' => 'asc' 
	), $current_url );
	
	$selected_desc = isset( $_GET['ur_sort'] ) && $_GET['ur_sort'] == 'desc' ? 'selected' : '';
	$selected_asc  = isset( $_GET['ur_sort'] ) && $_GET['ur_sort'] == 'asc' ? 'selected' : '';
	
	$html = apply_filters( 'elm_rml_ratings_sortby_dropdow', '
        <div class="elm-sort-by-widget">
            <select name="elm_sort_by_select" onchange="javascript:location.href = this.value;">
                <option value="' . esc_url( $sort_desc_url ) . '" ' . $selected_desc . '>' . __( 'Highest rating', 'elm' ) . '</option>
                <option value="' . esc_url( $sort_asc_url ) . '" ' . $selected_asc . '>' . __( 'Lowest rating', 'elm' ) . '</option>
            </select>
        </div>', $sort_desc_url, $sort_asc_url );
		
	echo $html;
}

/*
* Display rating form
*/
function elm_rml_ratings_form( $post_id = 0, $echo = true ) {
	global $elm_rml_ratings, $post;
	
	if ( $post_id == 0 )
		$post_id = $post->ID;
	
	if ( $echo == true ) :
		$elm_rml_ratings->echo_rating_form_html( $post_id );
	else :
		$elm_rml_ratings->get_rating_form_html( $post_id );
	endif;
}

/*
* Display read-only rating form
*
* @param integer $avg average
* @param float $echo
*/
function elm_rml_readonly_rating_form( $avg, $echo = true ) {
	$output = '<div class="elm-rating-readonly" data-elm-value="' . $avg . '" data-elm-readonly="true"></div>';
	
	if ( $echo == true ) :
		echo apply_filters( 'elm_rml_readonly_rating_form', $output );
	else :
		return apply_filters( 'elm_rml_readonly_rating_form', $output );
	endif;
}

/*
* Get post total rating
*
* @param integer $post_id
* @return integer 
*/
function elm_rml_ratings_total( $post_id ) {
	global $elm_rml_ratings;
	
	return $elm_rml_ratings->stats->total_ratings( $post_id );
}

/*
* Get rated posts
*
* @param string $post_type
* @param string $date today|3_days|7_days|14_days|1_month|3_months|6_months|1_year
* @return integer 
*/
function elm_rml_ratings_get_rated_posts( $post_type = 'all', $date = '' ) {
	global $elm_rml_ratings;
	
	return $elm_rml_ratings->stats->get_all_rated_posts( $post_type, $date );
}

/*
* Get post average rating
*
* @param integer $post_id
* @return integer 
*/
function elm_rml_ratings_get_average( $post_id ) {
	global $elm_rml_ratings;
	
	return $elm_rml_ratings->calculate_average( $post_id );
}