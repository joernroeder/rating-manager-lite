<?php

if ( ! class_exists( 'ELM_RML_Stats' ) ) :

class ELM_RML_Stats {
    
    function __construct() {
		add_action( 'wp_ajax_elm_reset_post_type_stats', array( $this, 'reset_content_stats' ) );
		add_action( 'wp_ajax_elm_reset_post_stats', array( $this, 'reset_post_stats' ) );
		
		add_action( 'delete_post', array( $this, 'delete_post' ) );
	
        $this->stats_page_action();
    }
	
	/**
     * Delete post stats data after deleting a post
	 *
     * @param string $post_id
     */
	function delete_post( $post_id ) {
		global $wpdb;
		
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings WHERE post_id = {$post_id}" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings_feedback WHERE post_id = {$post_id}" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings_log WHERE value = {$post_id}" );
	}
	
	/*
     * AJAX reset post type stats data callback
     */
	function reset_content_stats() {
		check_ajax_referer( 'elm_rml_reset_post_type_stats_action', 'nonce' );
		
		$this->reset_content_stats_query();
		
		// Change cookie prefix
		$this->add_cookie_value_prefix();
		
		$response = array(
            'message' => __('Done', 'elm'),
        );
		
		echo json_encode( $response );
        
        die;
	}
    
    /*
     * Reset ratings stats for post AJAX callback
     */
    function reset_post_stats() {
		check_ajax_referer( 'elm_rml_reset_ratings_action', 'nonce' );
		
		$post_id = intval( $_POST['post_id'] );
		
		$this->reset_post_stats_query( $post_id );
		
		$response = array(
            'message' => __('Done', 'elm'),
        );
		
		echo json_encode( $response );
        
        die;
    }
	
	/**
     * Check cookie prefix
	 *
     * @param string $_key type
	 * @return bool
     */
	function add_cookie_value_prefix() {
		// create unique key
		$_key = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz123456789' ), 0, 6 );
				
		if ( $this->cookie_prefix_exists( $_key ) != false ) {
			$cookie_key_exists = $this->cookie_prefix_exists( $_key );
					
			while ( $cookie_key_exists != false ) {
				$_key = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz123456789' ), 0, 6 );
				
				$cookie_key_exists = $this->cookie_prefix_exists( $_key );
			}
		}
		
		$_key .= '_';
		
		update_option( 'elm_rml_cookie_prefix', $_key );
	}
	
	/**
     * Check cookie prefix
	 *
     * @param string $_key type
	 * @return bool
     */
	function cookie_prefix_exists( $_key ) {
		if ( $this->get_cookie_value_prefix() == $_key )
			return true;
	}
	
	 /**
     * Get cookie value prefix
     */
	function get_cookie_value_prefix() {
		$prefix = get_option( 'elm_rml_cookie_prefix' );
		
		return $prefix;
	}
	
	 /**
     * Reset ratings statistics for a single post
     */
	function reset_post_stats_query( $post_id ) {
		global $wpdb;
		
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings WHERE post_id = {$post_id}" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = '_average_page_rating' AND post_id = {$post_id}" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings_feedback WHERE post_id = {$post_id}" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings_log WHERE value = {$post_id}" );
	}
	
	 /**
     * Reset ratings statistics for content
     */
	function reset_content_stats_query() {
		global $wpdb;
		
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings_feedback" );
		
		// Delete post rating meta
		$meta_query = $wpdb->get_results( "SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_average_page_rating'" );
		
		foreach( $meta_query as $meta ) {
			$id = $meta->meta_id;
			
			$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_id = {$id}" );
		}
		
		$wpdb->query( "DELETE FROM {$wpdb->prefix}elm_ratings_log WHERE rating_type != 'comment'" );
	}
    
    /**
     * Get total number of ratings per day, week and month
	 *
     * @param string $type type
     */
    function get_rated_posts_numb_date( $type ) {
        global $wpdb;
        
        $data['today'] = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE SUBSTR(date,1,10) = CURDATE()
         AND type = '{$type}'" );
        $data['week']  = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
         AND type = '{$type}'" );
        $data['month'] = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 1 MONTH AND CURDATE()
         AND type = '{$type}'" );
		 $data['year'] = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 1 YEAR AND CURDATE()
         AND type = '{$type}'" );
		 $data['overall'] = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE type = '{$type}'" );
        
        return $data;
    }
    
    /**
     * Get view feedback URL by post ID
	 *
     * @param int $post_id
     */
    function get_view_feedback_url( $post_id ) {
        $total = $this->get_feedback_numb( $post_id );
        
        $url = esc_url( add_query_arg( array(
             'feedback_id' => $post_id 
        ), admin_url( 'admin.php?page=elm-ur-view-feedback' ) ) );
        
        if ( $total != 0 ) {
            return '<a href="' . $url . '">' . __( 'View', 'elm' ) . ' (' . $total . ')' . '</a>';
        } else {
            return __( 'No feedback yet', 'elm' );
        }
    }
    
    /**
     * Get feedback by post ID
	 *
     * @param int $post_id
     */
    function get_feedback( $post_id ) {
        global $wpdb;
        
        $posts = $wpdb->get_results( "SELECT post_id, rating, feedback, name, email, date FROM {$wpdb->prefix}elm_ratings_feedback WHERE post_id = '{$post_id}'" );
        
        return $posts;
    }
    
    /**
     * Count feedback by post ID
	 *
     * @param int $post_id
     */
    function get_feedback_numb( $post_id ) {
        global $wpdb;
        
        $numb = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}elm_ratings_feedback WHERE post_id = {$post_id}" );
        
        return $numb;
    }
    
    /*
     * Stats page actions
     */
    function stats_page_action() {
        if ( isset( $_POST['stats_paged'] ) ) {
            $page = intval( $_POST['stats_paged'] );
            
            $url = $this->get_stats_url();
            if ( isset( $_POST['hidden_stats_post_type'] ) && !empty( $_POST['hidden_stats_post_type'] ) )
                $url = esc_url( add_query_arg( array(
                     'stats_post_type' => $_POST['hidden_stats_post_type'] 
                ), $url ) );
            
            if ( isset( $_GET['stats_post_type'] ) )
                $url = esc_url( add_query_arg( array(
                     'stats_post_type' => $_GET['stats_post_type'] 
                ), $url ) );
            
            if ( isset( $_POST['hidden_stats_sort_type'] ) && !empty( $_POST['hidden_stats_sort_type'] ) )
                $url = esc_url( add_query_arg( array(
                     'stats_sort_type' => $_POST['hidden_stats_sort_type']
                ), $url ) );
            
            if ( isset( $_GET['stats_sort_type'] ) )
                $url = esc_url( add_query_arg( array(
                     'stats_sort_type' => $_GET['stats_sort_type']
                ), $url ) );
				
			if ( isset( $_POST['hidden_date'] ) && !empty( $_POST['hidden_date'] ) )
                $url = esc_url( add_query_arg( array(
                     'stats_date' => $_POST['hidden_date']
                ), $url ) );
            
            if ( isset( $_GET['stats_date'] ) )
                $url = esc_url( add_query_arg( array(
                     'stats_date' => $_GET['stats_date']
                ), $url ) );
            
            wp_safe_redirect( array(
                 'stats_page' => $page 
            ), $url );
        }
    }
    
    /*
     * Get rating post types
     */
    function get_post_types_db() {
        global $wpdb;
        
        $_post_types = $wpdb->get_results( "SELECT DISTINCT(type) FROM {$wpdb->prefix}elm_ratings" );
		
		$post_types = array();
		
        $exclude     = array(
             'comment' 
        );
        
        foreach ( $_post_types as $post_type ) {
            if ( !in_array( $post_type->type, $exclude ) ) {
                $post_types[] = $post_type->type;
            }
        }
        
        return $post_types;
    }
    
    /*
     * Get sort types
     */
    function get_sort_types() {
        $sort_types = array(
             'asc' => __('Ascending', 'elm'),
            'desc' => __('Descending', 'elm') 
        );
        
        return $sort_types;
    }
	
	/*
     * Get dates
     */
    function get_dates() {
        $dates = array(
			'today' => __('Today', 'elm'),
			'3_days' => __('3 Days', 'elm'),
			'7_days' => __('7 Days', 'elm'),
			'14_days' => __('14 Days', 'elm'),
			'1_month' => __('1 Month', 'elm'),
			'3_months' => __('3 Months', 'elm'),	
			'6_months' => __('6 Months', 'elm'),		
			'1_year' => __('1 Year', 'elm'),		
        );
        
        return $dates;
    }
    
    /*
     * Get stats URL
     */
    function get_stats_url() {
        return admin_url( 'admin.php?page=' . ELM_RML_PLUGIN_PATH . '/admin/panels/stats.php' );
    }
    
    /*
     * Get stats
     */
    function get_stats() {
        if ( isset( $_POST['ur_stats_filter'] ) ) {
            $post_type = ( isset( $_POST['stats_post_type'] ) ) ? sanitize_text_field( $_POST['stats_post_type'] ) : 'all';
			$date = ( isset( $_POST['stats_date'] ) ) ? sanitize_text_field( $_POST['stats_date'] ) : '';
            
            $items = $this->get_sorted_rated_items( $post_type, sanitize_text_field( $_POST['stats_sort_type'] ), $date );
        } else {
            $post_type = ( isset( $_GET['stats_post_type'] ) ) ? sanitize_text_field( $_GET['stats_post_type'] ) : 'all';
            $sort_type = ( isset( $_GET['stats_sort_type'] ) ) ? sanitize_text_field( $_GET['stats_sort_type'] ) : 'asc';
			$date = ( isset( $_GET['stats_date'] ) ) ? sanitize_text_field( $_GET['stats_date'] ) : '';
            
            $items = $this->get_sorted_rated_items( $post_type, $sort_type, $date );
        }
        
        $data['limit']     = 20;
        $data['items']     = $items;
        $data['qty_items'] = count( $items );
        $data['qty_pages'] = ceil( $data['qty_items'] / $data['limit'] );
        
        return $data;
    }
    
    /**
     * Get stats items
	 *
     * @param array $items items
     * @param int $limit
     */
    function stats_content( $items, $limit ) {
        
        if ( !$items )
            return;
        
        $curr_page = isset( $_GET['stats_page'] ) ? intval( $_GET['stats_page'] ) : 1;
        
        $offset = ( $curr_page - 1 ) * $limit;
        $items  = array_slice( $items, $offset, $limit );
        
        return $items;
    }
    
    /**
     * Stats pagination
	 *
     * @param int $qty_pages
     * @param int $qty_items
     */
    function stats_pagination( $qty_pages, $qty_items ) {
        
        $first_page = 1;
        $last_page  = $qty_pages;
        
        $curr_page = isset( $_GET['stats_page'] ) ? intval( $_GET['stats_page'] ) : 1;
        $next_page = $curr_page < $qty_pages ? $curr_page + 1 : $last_page;
        $prev_page = $curr_page > 1 ? $curr_page - 1 : null;
        
        $next_page_url  = esc_url( add_query_arg( array(
             'stats_page' => $next_page 
        ), $this->get_stats_url() ) );
        $prev_page_url  = esc_url( add_query_arg( array(
             'stats_page' => $prev_page 
        ), $this->get_stats_url() ) );
        $first_page_url = $this->get_stats_url();
        $last_page_url  = esc_url( add_query_arg( array(
             'stats_page' => $last_page 
        ), $this->get_stats_url() ) );
        
        // Next page
        if ( isset( $_POST['stats_post_type'] ) ) {
            $next_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $next_page,
                'stats_post_type' => $_POST['stats_post_type'] 
            ), $next_page_url ) );
		} else if ( isset( $_GET['stats_post_type'] ) ) {
            $next_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $next_page,
                'stats_post_type' => $_GET['stats_post_type']
            ), $next_page_url ) );
		}
		
		if ( isset( $_POST['stats_sort_type'] ) ) {
            $next_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $next_page,
                'stats_sort_type' => $_POST['stats_sort_type']
            ), $next_page_url ) );
		} else if ( isset( $_GET['stats_sort_type'] ) ) {
            $next_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $next_page,
                'stats_sort_type' => $_GET['stats_sort_type']
            ), $next_page_url ) );
		}
		
		if ( isset( $_POST['stats_date'] ) ) {
            $next_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $next_page,
                'stats_date' => $_POST['stats_date']
            ), $next_page_url ) );
		} else if ( isset( $_GET['stats_date'] ) ) {
            $next_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $next_page,
                'stats_date' => $_GET['stats_date']
            ), $next_page_url ) );
		}
        
        // Prev page
        if ( isset( $_POST['stats_post_type'] ) ) {
            $prev_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $prev_page,
                'stats_post_type' => $_POST['stats_post_type']
            ), $prev_page_url ) );
		} else if ( isset( $_GET['stats_post_type'] ) ) {
            $prev_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $prev_page,
                'stats_post_type' => $_GET['stats_post_type']
            ), $prev_page_url ) );
		}
			
		if ( isset( $_POST['stats_sort_type'] ) ) {
            $prev_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $prev_page,
                'stats_sort_type' => $_POST['stats_sort_type']
            ), $prev_page_url ) );
		} else if ( isset( $_GET['stats_sort_type'] ) ) {
            $prev_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $prev_page,
                'stats_sort_type' => $_GET['stats_sort_type']
            ), $prev_page_url ) );
		}
			
		if ( isset( $_POST['stats_date'] ) ) {
            $prev_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $prev_page,
                'stats_date' => $_POST['stats_date']
            ), $prev_page_url ) );
		} else if ( isset( $_GET['stats_date'] ) ) {
            $prev_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $prev_page,
                'stats_date' => $_GET['stats_date']
            ), $prev_page_url ) );
		}
        
        // First page
        if ( isset( $_POST['stats_post_type'] ) ) {
            $first_page_url = esc_url( add_query_arg( array(
                 'stats_post_type' => $_POST['stats_post_type']
            ), $first_page_url ) );
        } else if ( isset( $_GET['stats_post_type'] ) ) {
            $first_page_url = esc_url( add_query_arg( array(
                 'stats_post_type' => $_GET['stats_post_type']
            ), $first_page_url ) );
		}
			
		if ( isset( $_POST['stats_sort_type'] ) ) {
            $first_page_url = esc_url( add_query_arg( array(
                'stats_sort_type' => $_POST['stats_sort_type']
            ), $first_page_url ) );
		} else if ( isset( $_GET['stats_sort_type'] ) ) {
            $first_page_url = esc_url( add_query_arg( array(
                'stats_sort_type' => $_GET['stats_sort_type'] 
            ), $first_page_url ) );
		}
			
		if ( isset( $_POST['stats_date'] ) ) {
            $first_page_url = esc_url( add_query_arg( array(
                'stats_date' => $_POST['stats_date'] 
            ), $first_page_url ) );
		} else if ( isset( $_GET['stats_date'] ) ) {
            $first_page_url = esc_url( add_query_arg( array(
                'stats_date' => $_GET['stats_date'] 
            ), $first_page_url ) );
		}
        
        // Last page
        if ( isset( $_POST['stats_post_type'] ) ) {
            $last_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $last_page,
                'stats_post_type' => $_POST['stats_post_type'] 
            ), $last_page_url ) );
		} else if ( isset( $_GET['stats_post_type'] ) ) {
            $last_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $last_page,
                'stats_post_type' => $_GET['stats_post_type'] 
            ), $last_page_url ) );
		}

        if ( isset( $_POST['stats_sort_type'] ) ) {
            $last_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $last_page,
                'stats_sort_type' => $_POST['stats_sort_type'] 
            ), $last_page_url ) );
       } else if ( isset( $_GET['stats_sort_type'] ) ) {
            $last_page_url = esc_url( add_query_arg( array(
                 'stats_page' => $last_page,
                'stats_sort_type' => $_GET['stats_sort_type'] 
            ), $last_page_url ) );
		}
		
		if ( isset( $_POST['stats_date'] ) ) {
            $last_page_url = esc_url( add_query_arg( array(
				'stats_page' => $last_page,
                'stats_date' => $_POST['stats_date'] 
            ), $last_page_url ) );
		} else if ( isset( $_GET['stats_date'] ) ) {
            $last_page_url = esc_url( add_query_arg( array(
				'stats_page' => $last_page,
                'stats_date' => $_GET['stats_date'] 
            ), $last_page_url ) );
		}
        
        echo '<span class="displaying-num">' . $qty_items . ' ' . __( 'items', 'elm' ) . '</span>';
        echo '    <span class="pagination-links">';
        echo '<a class="first-page disabled" title="' . __( 'Go to the first page', 'elm' ) . '" href="' . $first_page_url . '">&laquo;</a>';
        echo '<a class="prev-page disabled" title="' . __( 'Go to the previous page', 'elm' ) . '" href="' . $prev_page_url . '">&lsaquo;</a>';
        echo '<span class="paging-input">';
        echo '<form action="" method="post" class="display-inline">
        <input class="current-page" title="' . __( 'Current page', 'elm' ) . '" type="text" name="stats_paged" value="' . $curr_page . '" size="1" /> of <span class="total-pages">
        <input type="hidden" name="hidden_stats_post_type" value="' . @$_POST['stats_post_type'] . '" />
        <input type="hidden" name="hidden_stats_sort_type" value="' . @$_POST['stats_sort_type'] . '" />
		<input type="hidden" name="hidden_stats_date" value="' . @$_POST['stats_date'] . '" />
        </form>
        ' . $qty_pages . '</span>';
        echo '</span>';
        echo '<a class="next-page" title="' . __( 'Go to the next page', 'elm' ) . '" href="' . $next_page_url . '">&rsaquo;</a>';
        echo '<a class="last-page" title="' . __( 'Go to the last page', 'elm' ) . '" href="' . $last_page_url . '">&raquo;</a>';
        echo '</span>';
        
    }
    
    /**
     * Get sorted rated items
     * 
     * @param string $post_type
     * @param string $sort_type
     */
    function get_sorted_rated_items( $post_type, $sort_type, $date ) {
        $get_posts = $this->get_all_rated_posts( $post_type, $date );
        
        if ( !$get_posts )
            return;
        
        foreach ( $get_posts as $k => $post ) {
            $posts[$k]['id']             = $post->post_id;
            $posts[$k]['average_rating'] = $this->get_average_page_rating( $post->post_id );
        }
        
        foreach ( $posts as $k => $v ) {
            $b[$k] = intval( $v['average_rating'] );
        }
        
        if ( $sort_type == 'asc' ) {
            arsort( $b );
        } else {
            asort( $b );
        }
        
        foreach ( $b as $key => $val ) {
            $posts_sorted[] = $posts[$key];
        }
        
        return $posts_sorted;
    }
    
    /**
     * Get all rated posts
	 *
     * @param string $post_type
	 * @param string $date
     */
    function get_all_rated_posts( $post_type = '', $date = '' ) {
        global $wpdb;
        
        if ( empty( $post_type ) || $post_type == 'all' ) {
			$sql = "SELECT DISTINCT(post_id) FROM {$wpdb->prefix}elm_ratings";
		} else {
			$sql = "SELECT DISTINCT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE type = '{$post_type}'";
		}
		
		if ( !empty( $date ) ) {
			switch ( $date ) {
			 case "today":
				$sql .= " AND SUBSTR(date,1,10) = CURDATE()";
				break;
			case "3_days":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 3 DAY AND CURDATE()";
				break;
			case "7_days":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()";
				break;
			case "14_days":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 14 DAY AND CURDATE()";
				break;
			case "1_month":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 1 MONTH AND CURDATE()";
				break;
			case "3_months":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 3 MONTH AND CURDATE()";
				break;
			case "6_months":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 6 MONTH AND CURDATE()";
				break;
			case "1_year":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 1 YEAR AND CURDATE()";
				break;
			}
		}
		
		$results = $wpdb->get_results( $sql );
        
        return $results;
    }
    
    /**
     * Count total ratings
     * 
     * @param int $post_id
     */
    function total_ratings( $post_id = 0 ) {
        global $wpdb;
        
        $calculate_ratings = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE post_id = {$post_id}" );
		
        return $calculate_ratings;
        
    }
    
    /**
     * Get average page rating by post ID
     * 
     * @param int $post_id
     */
    function get_average_page_rating( $post_id = 0 ) {
        $get_average = get_post_meta( $post_id, '_average_page_rating', TRUE );
        
        $get_average = ( !empty( $get_average ) ) ? strval( $get_average ) : 0;
        
        return $get_average;
    }
}

endif;