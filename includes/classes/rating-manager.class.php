<?php

class Elm_Rating_Manager {
    
    function __construct() {
        $this->includes();
        
        add_action( 'init', array( $this, 'init' ) );
    }
    
    function init() {
        // Load class instances
        $this->get_settings     = new ELM_RML_Settings;
        $this->get_settings_gui = new ELM_RML_Settings_GUI;
        $this->stats            = new ELM_RML_Stats;
        
        $this->create_post_type(); // Creates post type
        
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this,  'enqueue_scripts'  ) );
		add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ) );
        
        add_action( 'wp_ajax_elm_process_rating', array( $this, 'process_rating_callback' ) );
        add_action( 'wp_ajax_nopriv_elm_process_rating', array( $this, 'process_rating_callback' ) );
        add_action( 'wp_ajax_elm_process_feedback', array( $this,  'process_feedback_callback' ) );
        add_action( 'wp_ajax_nopriv_elm_process_feedback', array( $this, 'process_feedback_callback' ) );
		
        add_action( 'wp_head', array( $this, 'front_end_js' ) );
        add_action( 'wp_footer', array( $this, 'rating_js' ) );
		
        add_filter( 'the_content', array( $this, 'content' ) );
        
        $own_hook_name = $this->get_settings->get_setting( 'general', 'location_own_hook_name' );
        
        if ( !empty( $own_hook_name ) ) {
            add_action( $own_hook_name, array( $this, 'echo_rating_form_html' ) );
        }
		
		do_action( 'elm_rml_init' );
    }
	/*
     * Load plugin textdomain
     */
	function load_textdomain() {
		load_plugin_textdomain( 'elm', false, ELM_RML_PLUGIN_PATH . '/languages' ); 
	}
    
    /*
     * AJAX feedback callback
     */
    function process_feedback_callback() {
        $message = sanitize_text_field( @$_POST['message'] );
        $name    = sanitize_text_field( @$_POST['name'] );
        $email   = sanitize_text_field( @$_POST['email'] );
        $post_id = intval( @$_POST['post_id'] );
		$rating = intval( @$_POST['rating'] );
		$nonce = sanitize_text_field( @$_POST['nonce'] );
		$captcha = sanitize_text_field( @$_POST['captcha'] );
		
		if ( !empty( $captcha ) )
			die;
		
		if( !wp_verify_nonce( $nonce, 'feedback_form-' . $post_id ) )
			die( 'Invalid nonce' );
			
		do_action( 'elm_rml_feedback_ajax_callback', $message, $name, $email, $post_id, $rating );
        
        $this->add_feedback( $post_id, $rating, $message, $name, $email );
        
        $thank_you_msg = $this->get_settings->get_setting( 'general_texts', 'thankyou_for_feedback' );
        
        if ( !$thank_you_msg )
            $thank_you_msg = '';
        
        $notify_admin = $this->get_settings->get_setting( 'general', 'feedback', 'notify_admin' );
        
        if ( $notify_admin ) {
            $this->new_feedback_notification( $post_id, $name, $email, $message );
        }
        
        $response = array(
             'message' => $thank_you_msg 
        );
        
        echo json_encode( $response );
        
        die();
    }
    
    /*
     * AJAX rate callback
     */
    function process_rating_callback() {
		check_ajax_referer( 'elm_rml_process_rating_action', 'nonce' );
		
		do_action( 'elm_rml_add_rating_ajax_callback', $_POST );
		
		// Rating value
        $value = intval( $_POST['value'] );
		
		if ( $value == 0 )
			die;
        
        $post_id = explode( '-', sanitize_text_field( $_POST['post_id'] ) );
        $post_id = $post_id[1];
    
       if ( !$this->get_log_rating( $this->get_current_log_method(), $post_id ) ) {
            $this->add_rating( $post_id, $value );
            
            $set = $this->set_log_rating( $this->get_current_log_method(), $post_id );
        }

        $avg = $this->calculate_average( $post_id );
		
		$thankyou_msg = $this->get_settings->get_setting( 'general_texts', 'thankyou_for_voting' );
        
        $response = array(
			'avg' => $avg,
            'thankyou_msg' => $thankyou_msg 
        );
        
        echo json_encode( $response );
        
        die;
    }
    
    /**
     * Add feedback to the database
	 *
     * @param int $post_id post ID
     * @param string $message message
     * @param string $name name
     * @param string $email email
     * 
     */
    function add_feedback( $post_id, $rating, $message, $name, $email ) {
        global $wpdb;
        
        // Check if the same feedback exist
        $same_feedback = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(post_id) FROM {$wpdb->prefix}elm_ratings_feedback WHERE post_id = %d AND feedback = %s", $post_id, $message ) );
        
        if ( !$same_feedback ) {
            $wpdb->insert( $wpdb->prefix . 'elm_ratings_feedback', array(
				'post_id' => $post_id,
				'rating' => $rating,
                'feedback' => $message,
                'name' => $name,
                'email' => $email,
            ), array(
				'%d',
				'%d',
                '%s',
                '%s',
                '%s',
            ) );
        }
		
		do_action( 'elm_rml_add_feedback', $post_id, $rating, $message, $name, $email );
    }
    
    /**
     * Add rating
	 *
     * @param int $post_id
     * @param int $rating
     */
    function add_rating( $post_id = 0, $rating = 0 ) {
        global $wpdb;
        
        $post_type = get_post_type( $post_id );
        
        $wpdb->insert( $wpdb->prefix . 'elm_ratings', array(
             'post_id' => $post_id,
            'type' => $post_type,
            'rating_value' => $rating 
        ), array(
             '%d',
            '%s',
            '%d' 
        ) );
        
        // Update average
        $this->update_average_page_rating( $post_id );
		
		do_action( 'elm_rml_add_rating', $post_type, $rating, $post_id );
        
        return $rating;
    }
    
    /**
     * Set log rating by method and post ID
	 *
     * @param string $method method name
     * @param int $post post ID
     */
    function set_log_rating( $method, $post_id ) {
        global $wpdb, $current_user;
		
		$wpml_support_setting = $this->get_settings->get_setting( 'general', 'wpml_support' );
		
		// WPML support
		if ( class_exists( 'SitePress' ) && function_exists( 'icl_object_id' ) && $wpml_support_setting == 1 ) {
			global $sitepress;
			$post_id = icl_object_id( $post_id, get_post_type( $post_id ), true, $sitepress->get_default_language() );
		}
	
        if ( $method == 'cookie' ) {
            setcookie( 'elm_rating_post_id_' . $post_id, $this->stats->get_cookie_value_prefix() . $post_id, time() + ( 10 * 365 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
        } else if ( $method == 'ip' ) {
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $wpdb->insert( $wpdb->prefix . 'elm_ratings_log', array(
				'type' => 'ip',
                'type_value' => $ip,
                'value' => $post_id 
            ), array(
                 '%s',
                '%s',
                '%d' 
            ) );
        } else if ( $method == 'cookie_and_ip' ) {
            setcookie( 'elm_rating_post_id_' . $post_id, $this->stats->get_cookie_value_prefix() . $post_id, time() + ( 10 * 365 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
            
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $wpdb->insert( $wpdb->prefix . 'elm_ratings_log', array(
                 'type' => 'ip',
                'type_value' => $ip,
                'value' => $post_id 
            ), array(
                 '%s',
                '%d',
                '%d' 
            ) );
        } else if ( $method == 'username' ) {
            if ( is_user_logged_in() ) {
                $wpdb->insert( $wpdb->prefix . 'elm_ratings_log', array(
					'type' => 'username',
                    'type_value' => $current_user->user_login,
                    'value' => $post_id 
                ), array(
					'%s',
                    '%s',
                    '%d' 
                ) );
            }
        }
    }
    
    /**
     * Check if rating has been logged
	 *
     * @param string $method method name
     * @param int $post_id post ID
     */
    function get_log_rating( $method, $post_id ) {
        global $wpdb, $current_user;
		
		$wpml_support_setting = $this->get_settings->get_setting( 'general', 'wpml_support' );
		
		// WPML support
		if ( class_exists( 'SitePress' ) && function_exists( 'icl_object_id' ) && $wpml_support_setting == 1 ) {
			global $sitepress;
			$post_id = icl_object_id( $post_id, get_post_type( $post_id ), true, $sitepress->get_default_language() );
		}
        
        if ( $method == 'cookie' ) {
            if ( isset( $_COOKIE['elm_rating_post_id_' . $post_id] ) && $_COOKIE['elm_rating_post_id_' . $post_id] == $this->stats->get_cookie_value_prefix() . $post_id ) {
                return true;
            } else {
                return false;
            }
        } else if ( $method == 'ip' ) {
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $query = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}elm_ratings_log WHERE type = %s AND type_value = %s AND value = %d", 'ip', $ip, $post_id ) );
            
            if ( $query ) {
                return true;
            } else {
                return false;
            }
        } else if ( $method == 'cookie_and_ip' ) {
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $query = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}elm_ratings_log WHERE type = %s AND type_value = %s AND value = %d", 'ip', $ip, $post_id ) );
            
            if ( $_COOKIE['elm_rating_post_id_' . $post_id] == $this->stats->get_cookie_value_prefix() . $post_id && $query ) {
                return true;
            } else {
                return false;
            }
            
        } else if ( $method == 'username' ) {
            $query = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}elm_ratings_log WHERE type = %s AND type_value = %s AND value = %d", 'username', $current_user->user_login, $post_id ) );
            
            if ( $query ) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    /*
     * Get logging method
     */
    function get_current_log_method() {
        $method = $this->get_settings->get_setting( 'general', 'logging' );
        
        return $method;
    }
    
    /*
     * Check who's allowed to rate
     */
    function is_allowed_to_rate() {
        $allowed_to_rate = $this->get_settings->get_setting( 'general', 'access' );
        
        if ( $allowed_to_rate == 'guests' ) {
            if ( !is_user_logged_in() ) {
                return true;
            }
        } else if ( $allowed_to_rate == 'registered_users' ) {
            if ( is_user_logged_in() ) {
                return true;
            }
        } else if ( $allowed_to_rate == 'registered_users_and_guests' ) {
            return true;
        }
    }
    
    /**
     * Visibility and location where to add rating form
	 *
     * @param string $content content
     */
    function content( $content ) {
        global $post;
		
        $settings = $this->get_settings->get_setting( 'general' );
        
        $before_post_content       = 0;
        $after_post_content        = 0;
        $visibility_home           = 0;
        $visibility_archives       = 0;
        $visibility_single_pages   = 0;
        $visibility_search_results = 0;
        $exclude_ratings_by        = '';
        $exclude_ratings_post_id   = '';
        $exclude_ratings_post_tag  = '';
        
        $exclude = false;
		
		if ( ! isset( $settings['allow_ratings_on'] ) ) :
			$settings['allow_ratings_on'] = array();
        else :
			$allow_ratings_on = $settings['allow_ratings_on'];
        endif;
		
        if ( isset( $settings['location']['before_post_content'] ) == 1 )
            $before_post_content = 1;
        
        if ( isset( $settings['location']['after_post_content'] ) == 1 )
            $after_post_content = $settings['location']['after_post_content'];
        
        if ( isset( $settings['visibility']['home'] ) == 1 )
            $visibility_home = $settings['visibility']['home'];
        
        if ( isset( $settings['visibility']['archives'] ) == 1 )
            $visibility_archives = $settings['visibility']['archives'];
        
        if ( isset( $settings['visibility']['single_pages'] ) == 1 )
            $visibility_single_pages = $settings['visibility']['single_pages'];
        
        if ( isset( $settings['visibility']['search_results'] ) == 1 )
            $visibility_search_results = $settings['visibility']['search_results'];
        
        if ( isset( $settings['visibility']['pages'] ) == 1 )
            $visibility_page = 1;
        
        if ( isset( $settings['exclude_ratings_by'] ) )
            $exclude_ratings_by = $settings['exclude_ratings_by'];
        
        if ( isset( $settings['exclude_ratings']['post_id'] ) )
            $exclude_ratings_post_id = $settings['exclude_ratings']['post_id'];
        
        if ( isset( $settings['exclude_ratings']['post_tag'] ) )
            $exclude_ratings_post_tag = $settings['exclude_ratings']['post_tag'];
        
        if ( $before_post_content == 1 && $after_post_content == 0 ) {
            $_content = $this->get_rating_form_html() . $content;
        } else  if ( $before_post_content ==  0 && $after_post_content == 1 ) {
			$_content = $content . $this->get_rating_form_html();
        } else if ( $before_post_content == 1 && $after_post_content == 1 ) {
			$_content = $this->get_rating_form_html()  . $content . $this->get_rating_form_html();
		} else {
			$_content = $content;
		}
        
        if ( !empty( $allow_ratings_on ) ) {
            foreach ( $allow_ratings_on as $post_type => $value ) {
                if ( $post_type == $post->post_type ) {
                    // case home
                    if ( is_front_page() && is_home() && $visibility_home == 1 ) {
                        $show = true;
                        // case archives
                    } else if ( is_archive() && $visibility_archives == 1 ) {
                        $show = true;
                        // case search results
                    } else if ( is_single() && $visibility_single_pages == 1 ) {
                        $show = true;
                        // case single pages
                    } else if ( is_search() && $visibility_search_results == 1 ) {
                        $show = true;
                    } else if ( is_page() && @$visibility_page == 1 ) {
                        // case single pages
                        $show = true;
                    }
                    
                    if ( $exclude_ratings_by == 1 ) {
                        if ( !empty( $exclude_ratings_post_id ) ) {
                            if ( strpos( $exclude_ratings_post_id, ',' ) ) {
                                $post_ids = explode( ',', $exclude_ratings_post_id );
                            } else {
                                $post_ids = $exclude_ratings_post_id;
                            }
                            
                            if ( is_array( $post_ids ) ) {
                                if ( in_array( $post->ID, $post_ids ) ) {
                                    $exclude = true;
                                }
                            } else {
                                if ( $post->ID == $post_ids ) {
                                    $exclude = true;
                                }
                            }
                        }
						
                        if ( !empty( $exclude_ratings_post_tag ) ) {
                            if ( strpos( $exclude_ratings_post_tag, ',' ) ) {
                                $post_tags = explode( ',', $exclude_ratings_post_tag );
                            } else {
                                $post_tags = $exclude_ratings_post_tag;
                            }
                            
                            $wp_post_tags = wp_get_post_tags( $post->ID );
                            
                            if ( is_array( $wp_post_tags ) ) {
                                foreach ( $wp_post_tags as $k => $wp_tag ) {
                                    if ( is_array( $post_tags ) ) {
                                        if ( in_array( $wp_tag->name, $post_tags ) ) {
                                            $exclude = true;
                                        }
                                    } else {
                                        if ( $wp_tag->name == $post_tags ) {
                                            $exclude = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if ( isset( $show ) && isset( $exclude ) && $show == true && $exclude != true ) {
            $content = @$_content;
        }
        
        return $content;
    }
    
    /**
     * Add rating information meta box to posts
     */
    function meta_boxes() {
        global $post;
		
		$settings = $this->get_settings->get_setting( 'general' );
		
		if ( ! isset( $settings['allow_ratings_on'] ) ) :
			$settings['allow_ratings_on'] = array();
        else :
			$allow_ratings_on = $settings['allow_ratings_on'];
        endif;
		
		 if ( !empty( $allow_ratings_on ) ) {
            foreach ( $allow_ratings_on as $post_type => $value ) {
				add_meta_box( 'rating_meta_box', __( 'Ratings', 'elm-' ), array( $this, 'ratings_meta_box' ), $post_type, 'side', 'low' );
			}
		}
    }
    
    /**
     * Ratings statistics meta box.
     */
    function ratings_meta_box() {
        global $post;
		
		$settings = $this->get_settings->get_setting( 'general' );
		
		$output = '';
		$output .= '<strong>' . __('Average rating:', 'elm'). '</strong> <span class="ur-average-rating">' . $this->calculate_average( $post->ID ) .'</span><br />' ;
		$output .= '<strong>' . __('Rated by users:', 'elm'). '</strong> <span class="ur-rated-by-users">' . $this->get_rated_by_users_num( $post->ID ) .'</span><br />' ;
		
		if ( (int) $this->get_settings->get_setting( 'general', 'feedback', 'enable_feedback' ) == 1 )
			$output .= '<strong>' . __('Feedback:', 'elm'). '</strong> <span class="ur-feedback">' . $this->stats->get_feedback_numb( $post->ID ) .'</span>';
			
		if ( $this->stats->get_feedback_numb( $post->ID ) != 0 ) :
			$output .= ' ' . $this->stats->get_view_feedback_url( $post->ID );
			$output .= '<br />' ;
		else :
			$output .= '<br />' ;
		endif;
		
		$output .= '<br />';
		
		$output .= '<input type="hidden" name="reset_ratings_post_id" id="reset-ratings-post-id" value="'. $post->ID .'" />';
		$output .= '<input type="hidden" name="reset_ratings_nonce" id="reset-ratings-nonce" value="'. wp_create_nonce( 'elm_rml_reset_ratings_action' ) .'" />';

		$output .= '<a href="javascript:void(0)" id="reset-post-stats" class="button">'. __('Reset stats', 'elm') .'</a> <span class="reset-post-stats-message"></span>';
		
		echo $output;
    }
    
    /**
     * Update post, page, media and custom post average rating
     * 
     * @param int $post_id
     */
    function update_average_page_rating( $post_id = 0 ) {
        $calculate_average = $this->calculate_average( $post_id );
        
        $update = update_post_meta( $post_id, '_average_page_rating', $calculate_average );
		
		do_action( 'elm_rml_update_average_rating', $calculate_average, $post_id );
        
        return $update;
    }
    
    /**
     * Calculate average
     * 
     * @param int $post_id post ID
     */
    function calculate_average( $post_id ) {
        global $wpdb;
		
		$post_type = get_post_type( $post_id );
		
		$wpml_support_setting = $this->get_settings->get_setting( 'general', 'wpml_support' );
        
		// WPML support
		// Sum average between different languages
		if ( class_exists( 'SitePress' )  && $wpml_support_setting == 1 ) {
			global $sitepress;
			
			$trid = $sitepress->get_element_trid( $post_id, 'post_' . $post_type);
			$translations = $sitepress->get_element_translations($trid, 'post_' . $post_type);
		
			if ( $translations ) {
				foreach( $translations as $k => $translation ) {
					$t_post_id = $translation->element_id;
					
					$t_posts[] = $t_post_id;
				}
				
				$translations_separated = implode( ',', $t_posts );
			} else {
				$translations_separated = $post_id;
			}
			
			$results = $wpdb->get_results( "SELECT (rating_value) FROM {$wpdb->prefix}elm_ratings WHERE post_id IN ({$translations_separated}) AND type = '{$post_type}'" );
		} else {
		
			$results = $wpdb->get_results( "SELECT (rating_value) FROM {$wpdb->prefix}elm_ratings WHERE post_id = {$post_id} AND type = '{$post_type}'" );
			
		}
        
        if ( !$results )
            return 0;
        
        foreach ( $results as $k => $postmeta_object ) {
            $ratings[] = $postmeta_object->rating_value;
        }
        
        $average = $this->average_calculate_func( $post_id, $ratings );
        
        return $average;
    }
    
    /**
     * Average calculation function
     * 
     * @param array $array
     */
    function average_calculate_func( $post_id, $array ) {
	
		$max_ratings = get_option( 'elm_rml_settings' );
		$max_ratings = $max_ratings['style']['max_ratings'];

        $total = 0;
        
        $count = count( $array );
        
        foreach ( $array as $value ) {
            $total = $total + $value;
        }

		$average = apply_filters( 'elm_rml_average_calculate_func', ( $total / $count ), $max_ratings, $array, $count, $total );
        
        return round( $average );
    }
	
	/**
     * Count how many users rated the post
     * 
     * @param int $post_id post ID
     */
	function get_rated_by_users_num( $post_id, $post_type = '' ) {
		global $wpdb;
			
		if ( !$post_type )
			$post_type = get_post_type( $post_id );
		
		$result = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}elm_ratings WHERE post_id = {$post_id} AND type = '{$post_type}'" );
	
		return apply_filters( 'elm_rml_rated_users_number', $result, $post_id, $post_type );
	}
    
    /*
     * Add rating JavaScript code to the site footer
     */
    function rating_js() {
		global $post;
	
		$svg_icon_name = $this->get_settings->get_setting( 'style', 'rating_image' );
		$svg_icon = ELM_RML_PLUGIN_URL . '/svg/' . $svg_icon_name . '.svg';
		
		if ( ! file_exists( ELM_RML_PLUGIN_SVG_PATH . '/' . $svg_icon_name . '.svg' ) )
			return;
	
        $max_ratings = $this->get_settings->get_setting( 'style', 'max_ratings' );
        $image_size  = $this->get_settings->get_setting( 'style', 'rating_image_size' );
        $normal_fill = $this->get_settings->get_setting( 'style', 'color', 'normal_fill' );
        $rated_fill  = $this->get_settings->get_setting( 'style', 'color', 'rated_fill' );
        
		$ratings_nonce = wp_create_nonce( 'elm_rml_process_rating_action' );
        
        $output = "<script type=\"text/javascript\">
			var options = { numIcons: " . $max_ratings . ", maxValue: " . $max_ratings . ", normalFill: '" . $normal_fill . "', ratedFill: '" . $rated_fill . "', svgWidth: '" . $image_size . "' }; var ur_nonce = '". $ratings_nonce ."';
			elm_ultimate_ratings( '" . $svg_icon . "', options, ur_nonce );
        </script>";
		
		do_action( 'elm_rml_rating_js' );
        
        echo apply_filters( 'elm_rml_rating_js', $output );
    }
    
    /**
     * Template for rating form
	 *
     * @param int $post post ID
     */
    function template( $post_id ) {
        $avg       = (int) $this->calculate_average( $post_id );
        
        $feedback_form = (int) $this->get_settings->get_setting( 'general', 'feedback', 'enable_feedback' );
		$use_schema = (int) $this->get_settings->get_setting( 'general', 'rich_snippets' );
		$max_ratings = (int) $this->get_settings->get_setting( 'style', 'max_ratings' );
		$thankyou_msg = $this->get_settings->get_setting( 'general_texts', 'thankyou_for_voting' );
		
		$_template = $this->get_settings->get_setting( 'style', 'template' );
		
		do_action( 'elm_rml_below_html_template', $avg, $feedback_form, $use_schema, $max_ratings, $thankyou_msg );
		
		$_template = str_replace( '%THANK_YOU_MESSAGE%', '<div class="elm-thankyou-msg"></div>', $_template );
		
		if ( strpos( $_template, '%RATED_BY_USERS_NUM%' ) !== false ) {
			$rated_by_users_num = $this->get_rated_by_users_num( $post_id );
			$_template = str_replace( '%RATED_BY_USERS_NUM%', $rated_by_users_num, $_template );
		}
        
        if ( $avg && $this->get_log_rating( $this->get_current_log_method(), $post_id ) ) {
            $replace   = '<div class="elm-rating-readonly" data-elm-value="' . $avg . '" data-elm-readonly="true"></div>';
            $_template = str_replace( '%RATING%', $replace, $_template );
            
            $replace   = '<div class="elm-rating-stats elm-readonly">' . $avg . '</div>';
            $_template = str_replace( '%AVERAGE%', $replace, $_template );
        } else if ( !$this->is_allowed_to_rate() ) {
            $replace   = '<div class="elm-rating-readonly" data-elm-value="' . $avg . '" data-elm-readonly="true"></div>';
            $_template = str_replace( '%RATING%', $replace, $_template );
            
            $replace   = '<div class="elm-rating-stats elm-readonly">'. $avg . '</div>';
            $_template = str_replace( '%AVERAGE%', $replace, $_template );
        } else {
            $replace   = '<div class="elm-rating post-' . $post_id . ' float-left" data-elm-value="' . $avg . '"></div>';
            $_template = str_replace( '%RATING%', $replace, $_template );
            
            $replace   = '<div class="elm-rating-stats">' . $avg . '</div>';
            $_template = str_replace( '%AVERAGE%', $replace, $_template );
        }

		// Rich snippets
		if ( $use_schema == 1 && $avg != 0 && $max_ratings == 5 )
			$_template = '<div itemscope itemtype="http://schema.org/Review">
			<div itemprop="author" itemscope itemtype="http://schema.org/Person">
			<meta itemprop="name" content="'. __('visitors', 'elm') .'"></div>
			<div itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">
			<meta itemprop="name" content="'. get_the_title( $post_id ) .'"></div>
			<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
			<meta itemprop="ratingValue" content="'. $avg .'"></div>
			</div>' . $_template;
		
		// If feedback is enabled, then add feedback form
        if ( $feedback_form )
            $_template .= $this->feedback_form();
			
		do_action( 'elm_rml_after_html_template', $_template, $avg, $feedback_form, $use_schema, $max_ratings, $thankyou_msg );
        
        return apply_filters( 'elm_rml_html_template', $_template );
    }
    
    /*
     * Feedback HTML form
     */
    function feedback_form() {
        global $post;
        
        $feedback_about_text = $this->get_settings->get_setting( 'general_texts', 'feedback_about_this_page' );
		$nonce = wp_create_nonce( 'feedback_form-' . $post->ID );
        
		$output = '<div class="elm-feedback">';
        $output .= '<a href="javascript:void(0)" class="elm-leave-your-feedback">' . apply_filters( 'elm_rml_leave_your_feedback', __( 'Leave your feedback', 'elm' ) ) . '</a>';
        
        $output .= '<div class="feedback-wrapper">';
        
        if ( !empty( $feedback_about_text ) ) {
            $output .= '<div class="feedback-messages">' . $feedback_about_text . '</div>';
        }
        
        $output .= '
		<div class="elm-feedback-form">
            <div class="feedback-errors"></div>
            <form class="feedback-form">
                <label>' . __( 'Name', 'elm' ) . '</label><br />
                <input type="text" name="feedback_name" class="feedback-name" placeholder="' . __( 'Name', 'elm' ) . '" value="" /><br />
                <label>' . __( 'Email', 'elm' ) . '</label><br />
                <input type="text" name="feedback_email" class="feedback-email" placeholder="' . __( 'Email', 'elm' ) . '" value="" /><br />
                <label>' . __( 'Message', 'elm' ) . '</label><br />
                <textarea name="feedback_message" class="feedback-message" placeholder="'. __('Your feedback', 'elm') .'" rows="4" cols="50"></textarea><br />
                <input type="hidden" name="feedback_post_id" class="feedback-post-id" value="' . $post->ID . '" /><br />
				<input type="hidden" name="feedback_captcha" class="feedback-captcha" value="" /><br />
				<input type="hidden" name="feedback_nonce" class="feedback-nonce" value="' . $nonce . '" /><br />
                <input type="button" name="send_feedback" class="send-feedback" value="' . __( 'Send', 'elm' ) . '" />
            </form>
		</div>';
        
        $output .= '</div>';
		$output .= '</div>';
        
        return apply_filters( 'elm_rml_feedback_form_html', $output );
    }
    
    /**
     * Get rating form HTML
     */
    function get_rating_form_html( $post_id = 0 ) {
        global $post;
		
        if ( $post_id == 0 )
			$post_id = $post->ID;
			
        $output = $this->template( $post_id );
        
        return $output;
    }
    
    /**
     * Echo rating form HTML
     */
    function echo_rating_form_html( $post_id = 0 ) {
        global $post;
		
		if ( $post_id == 0 )
			$post_id = $post->ID;
        
        $output = $this->template( $post_id );
        
        echo $output;
    }
    
    /**
     * Enqueue scripts
     */
    function enqueue_scripts() {
		global $post;
		
		$ratings_js_url = apply_filters( 'elm_rml_ratings_js_url', ELM_RML_PLUGIN_URL . '/assets/js/ratings.js' );
		$ultimate_ratings_js_url = apply_filters( 'elm_rml_js_url', ELM_RML_PLUGIN_URL . '/assets/js/rating-manager.js' );
		$ultimate_ratings_css_url = apply_filters( 'elm_rml_css_url', ELM_RML_PLUGIN_URL . '/assets/css/rating-manager.css' );
		
        wp_enqueue_script( 'elm-rml-ratings', $ratings_js_url, array(
             'jquery' 
        ) );
		
        wp_enqueue_script( 'elm-rml', $ultimate_ratings_js_url, array(
             'jquery' 
        ) );
		
		// Localize the script
		$translation_array = array(
			'feedback_required' => __( 'Feedback is a required field', 'elm' ),
			'name_required' => __( 'Name is a required field', 'elm' ),
			'email_required' => __( 'Email is a required field', 'elm' ),
			'wrong_email' => __( 'Wrong email', 'elm' )
		);
		wp_localize_script( 'elm-rml', 'feedback_texts', $translation_array );
        
        wp_enqueue_style( 'elm-rml', $ultimate_ratings_css_url, array() );
		
		do_action( 'elm_rml_enqueue_scripts' );
    }
    
    /**
     * Create post type
     */
    function create_post_type() {
        
        register_post_type( 'page_rating', array(
             'labels' => array(
                 'name' => __( 'Page rating' ),
                'singular_name' => __( 'Page rating' ) 
            ),
            
            'public' => false,
            'has_archive' => false,
            'supports' => array(
                 'custom-fields' 
            ) 
        ) );
        
    }
    
	/**
     * Include classes
     */
    function includes() {
        require( ELM_RML_PLUGIN_CLASSES_PATH . '/settings.class.php' );
        require( ELM_RML_PLUGIN_CLASSES_PATH . '/settings-gui.class.php' );
        require( ELM_RML_PLUGIN_CLASSES_PATH . '/stats.class.php' );
    }
    
    /**
     * Send new feedback notification email
	 *
     * @param int $post_id
     * @param string $name 
     * @param string $email
     * @param string $feedback_text
     */
    function new_feedback_notification( $post_id, $name, $email, $feedback_text ) {
        $email = $this->get_settings->get_setting( 'general', 'feedback', 'admin_email' );
        
        if ( !empty( $email ) ) {
            $post_title = get_the_title( $post_id );
            
            $subject = apply_filters( 'elm_rml_feedback_notification_subject', get_bloginfo( 'name' ) . ': ' . __( 'New feedback notification', 'elm' ), $name, $email, $post_id );
            $message = apply_filters( 'elm_rml_feedback_notification_message', "
            " . __('Hello', 'elm') . ", <br /><br />
            " . __('one of', 'elm') . " " . sprintf( '<a href="%s">%s</a>', site_url(), get_bloginfo( 'name' ) ) . " " . __('visitors has rated post', 'elm') . " " . sprintf( '<a href="%s">%s</a>', get_permalink( $post_id ), $post_title ) . " " . __('and left feedback about it.') . "<br />
			<br />
			" . __('Feedback:', 'elm') . "<br />" . 
			$feedback_text  . "<br /><br />" 
			. __('Name:', 'elm') . " " . $name . "<br />
            " . __('Email:', 'elm') . " " . $email . "<br />
			
			<div style=\"width: 100%; font-size: 11px; margin-top: 30px; text-align: center;\">
			<p>" . __('Email sent by', 'elm') . " <b>Elementous Rating Manager</b> WordPress ". __('plugin', 'elm') ."</p>
			<p>". __('If you don\'t want to receive these email notifications about every new feedback left on your website, then please change notification settings (WordPress Dashboard > Rating Manager > Settings > General Settings > Notifications)', 'elm') ."</p>
			</div>", $name, $subject, $email, $post_id );

			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
			
            wp_mail( $email, $subject, $message );
			
			// Reset content-type to avoid conflicts - http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
        }
    }
	
	/**
     * Sets an email content type to HTML
     */
	function set_html_content_type() {
		return 'text/html';
	}
	
    /**
     * Add front-end JavaScript
     */
    function front_end_js() {
        $ajax_url = admin_url( 'admin-ajax.php' );
        
        echo '<script type="text/javascript">var ajaxurl = "' . $ajax_url . '"; </script>';
    }
    
    /**
     * Log method
	 *
     * @param string $string
     */
    function debug_log( $string ) {
        $filename    = ELM_RML_PLUGIN_PATH . '/debug_log.txt';
        $somecontent = $string . "\r\n";
        
        if ( is_writable( $filename ) ) {
            $handle = fopen( $filename, 'a' );
            
            fwrite( $handle, $somecontent );
            
            fclose( $handle );
        }
    }
    
    /*
     * Get custom post types
     */
    function get_custom_post_types() {
        $_types = get_post_types();
        
        $_exclude = array(
             'revision',
            'nav_menu_item',
            'page_rating',
            'custom_css',
            'customize_changeset'
        );
        
        foreach ( $_types as $key => $type ) {
            $obj = get_post_type_object( $key );
            
            if ( !in_array( $type, $_exclude ) ) {
                $types[$key] = $obj->labels->singular_name;
            }
        }
        
        return apply_filters( 'elm_rml_get_custom_post_types', $types );
    }
	
	/*
     * Check if rating is enabled for the current post type
	 *
	 * @return bool
     */
	function allow_ratings_on() {
		global $post;

        if ( $post ) {
            $allow_ratings_on = $this->get_settings->get_setting('general', 'allow_ratings_on');

            if (!$allow_ratings_on)
                $allow_ratings_on = array();

            if ($post_type = get_post_type($post)) {
                if (!array_key_exists($post_type, $allow_ratings_on)) {
                    return false;
                } else {
                    return true;
                }
            }
        }
	}
	
	/*
     * Install plugin
     */
    function install() {
        global $wpdb;
		
		if ( get_option( 'elm_rating_manager_ite' ) != 'installed' ) {
			update_option( 'elm_rating_manager_ite', 'installed' );
			
			// Add default settings
			$settings = new ELM_RML_Settings;
			$settings->verify_settings();
			
			$stats = new ELM_RML_Stats;
			$stats->add_cookie_value_prefix();

			$wpdb->hide_errors();
			
			$collate = '';
			
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( !empty( $wpdb->charset ) )
					$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				if ( !empty( $wpdb->collate ) )
					$collate .= " COLLATE $wpdb->collate";
			}
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			// Rating tables
			$tables = "
				CREATE TABLE {$wpdb->prefix}elm_ratings_log (
				  id bigint(20) NOT NULL auto_increment,
				  type varchar(200) NOT NULL,
				  type_value varchar(200) NOT NULL,
				  rating_type varchar(200) NOT NULL,
				  value bigint(20) NOT NULL,
				  ip bigint(20) NOT NULL,
				  PRIMARY KEY  (id),
				  KEY type (type)
				) $collate;    
				CREATE TABLE {$wpdb->prefix}elm_ratings_feedback (
				  id bigint(20) NOT NULL auto_increment,
				  post_id bigint(20) NOT NULL,
				  rating bigint(20) NOT NULL,
				  feedback text NOT NULL,
				  name varchar(200) NOT NULL,
				  email varchar(200) NOT NULL,
				  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY  (id),
				  KEY post_id (post_id)
				) $collate;
				CREATE TABLE {$wpdb->prefix}elm_ratings (
				  id bigint(20) NOT NULL auto_increment,
				  post_id bigint(20) NOT NULL,
				  type varchar(200) NOT NULL,
				  rating_value bigint(20) NOT NULL,
				  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY  (id),
				  KEY post_id (post_id)
				) $collate;";
			
			dbDelta( $tables );
		}
    }
}