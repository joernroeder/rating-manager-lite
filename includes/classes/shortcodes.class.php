<?php

// don't load directly
if ( !defined('ABSPATH') )
	exit;
	
if ( ! class_exists( 'ELM_RML_Shortcodes' ) ) :

class ELM_RML_Shortcodes {
    
    function __construct() {
		add_shortcode( 'elm_rml_rating', array( $this, 'elm_rml_rating' ) );
		add_shortcode( 'elm_rml_rating_readonly', array( $this, 'elm_rml_readonly_rating_form' ) );
		add_shortcode( 'elm_rml_top_rated', array( $this, 'elm_rml_top_rated' ) );
    }
	
    /**
     * Top posts shortcode
	 *
     * @param array $atts
     */
    function elm_rml_top_rated( $atts ) {
        $atts = shortcode_atts( array(
             'post_type' => '',
            'sort' => 'asc',
            'limit' => '' 
        ), $atts );
        
        global $wpdb;
        
        if ( !$atts['post_type'] ) {
            $sql = "SELECT DISTINCT(post_id) FROM {$wpdb->prefix}elm_ratings";
            
            if ( $atts['limit'] )
                $sql = "SELECT DISTINCT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE LIMIT {$atts['limit']}";
            
            $get_posts = $wpdb->get_results( $sql );
        } else {
            $sql = "SELECT DISTINCT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE type = '{$atts['post_type']}'";
            
            if ( $atts['limit'] )
                $sql = "SELECT DISTINCT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE type = '{$atts['post_type']}' LIMIT {$atts['limit']}";
            
            $get_posts = $wpdb->get_results( $sql );
        }
        
        if ( empty( $get_posts ) )
            return;
        
        foreach ( $get_posts as $k => $post ) {
            $average = intval( get_post_meta( $post->post_id, '_average_page_rating', TRUE ) );
            
            $posts[$k]['id']             = $post->post_id;
            $posts[$k]['average_rating'] = $average;
        }
        
        foreach ( $posts as $k => $v ) {
            $b[$k] = intval( $v['average_rating'] );
        }
        
		// Sort
        if ( $atts['sort'] == 'asc' ) {
            arsort( $b );
        } else {
            asort( $b );
        }
        
        $html = '';
        
        if ( $b ) {
            $html .= '<ul class="elm-top-rated">';
            
            foreach ( $b as $key => $val ) {
                $html .= '<li><a href="' . get_permalink( $posts[$key]['id'] ) . '">' . get_the_title( $posts[$key]['id'] ) . '</a></li>' . "\r\n";
            }
            
            $html .= '</ul>';
        }
        
        return apply_filters( 'elm_rml_top_rated_html_shortcode', $html );
    }
	
	/**
     * Display rating shortcode
	 *
     * @param array $atts
     */
	function elm_rml_rating( $atts ) {
		global $elm_rml_ratings;
		
		return $elm_rml_ratings->get_rating_form_html();
	}
	
	/*
	* Display read-only rating form
	*
	* @param array $atts
	*/
	function elm_rml_readonly_rating_form( $atts ) {
		$atts = shortcode_atts( array(
			'average' => '0'
        ), $atts );
		
		return elm_rml_readonly_rating_form( intval( $atts['average'] ), false );
	}
}

endif;