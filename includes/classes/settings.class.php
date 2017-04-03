<?php

if ( ! class_exists( 'Elm_UR_Settings' ) ) :

class Elm_UR_Settings {
    
    public $message;
    
    function __construct() {
        $this->settings = $this->get_settings();
        
        $this->process_forms();
    }
    
    /*
     * Process settings forms
     */
    function process_forms() {
        /*
         * Process general settings form
         */
        if ( isset( $_POST['elm_save_ur_settings_general'] ) && check_admin_referer( 'elm_ur_settings_general_action', 'elm_ur_settings_general_nonce' ) ) {
            if ( isset( $_POST['allow_ratings_on'] ) && is_array( $_POST['allow_ratings_on'] ) ) :
				$this->delete_settings( 'general', 'allow_ratings_on' );
				
                foreach ( $_POST['allow_ratings_on'] as $k => $post_type ) {
                    $this->settings['general']['allow_ratings_on'][$k] = 1;
                }
            else :
				$this->delete_settings( 'general', 'allow_ratings_on' );
			endif;
            
            if ( !empty( $_POST['location'] ) ) :
				$this->delete_settings( 'general', 'location' );
				
                foreach ( $_POST['location'] as $k => $location ) {
                    $this->settings['general']['location'][$k] = 1;
                }
			else :
				$this->delete_settings( 'general', 'location' );
			endif;
            
            $this->settings['general']['location_own_hook_name'] = sanitize_text_field( $_POST['location_own_hook_name'] );
            
            if ( isset( $_POST['visibility'] ) && is_array( $_POST['visibility'] ) ) :
				$this->delete_settings( 'general', 'visibility' );
				
                foreach ( $_POST['visibility'] as $k => $visibility ) {
                    $this->settings['general']['visibility'][$k] = 1;
                }
            else :
				$this->delete_settings( 'general', 'visibility' );
            endif;
            
            $this->settings['general']['exclude_ratings_by']       = @sanitize_text_field( $_POST['exclude_ratings_by'] );
            $this->settings['general']['exclude_ratings']['post_id']  = sanitize_text_field( $_POST['exlcude_ratings_by_post_id'] );
            $this->settings['general']['exclude_ratings']['post_tag'] = sanitize_text_field( $_POST['exlcude_ratings_by_post_tag'] );
            $this->settings['general']['access']                      = sanitize_text_field( $_POST['access'] );
            $this->settings['general']['logging']                     = sanitize_text_field( $_POST['logging'] );
            $this->settings['general']['feedback']['enable_feedback'] = @intval( $_POST['feedback']['enable_feedback'] );
            $this->settings['general']['feedback']['notify_admin']    = @intval( $_POST['feedback']['notify_admin'] );
            $this->settings['general']['feedback']['admin_email']     = sanitize_email( $_POST['feedback']['admin_email'] );
            
            $this->settings['general']['widgets']['enable_ratings_widget']                = @intval( $_POST['widgets']['enable_ratings_widget'] );
            $this->settings['general']['widgets']['enable_ratings_post_edit_widget']      = @intval( $_POST['widgets']['enable_ratings_post_edit_widget'] );
            $this->settings['general']['widgets']['display_current_rating_in_pages_list'] = @intval( $_POST['widgets']['display_current_rating_in_pages_list'] );
			
			if ( @intval( $_POST['rich_snippets'] ) == 1 && $this->settings['style']['max_ratings'] != 5  ) {
				$this->message['error'][] = __( 'Error: You can not use structured data when the maximum rating value is not 5.', 'elm' );
			} else {
				$this->settings['general']['rich_snippets'] = @intval( $_POST['rich_snippets'] );
			}
			
			$this->settings['general']['wpml_support'] = @intval( $_POST['wpml_support'] );
			
			if ( isset( $_POST['feedback']['notify_admin'] ) && intval( $_POST['feedback']['notify_admin'] ) == 1 && empty( $_POST['feedback']['admin_email'] ) ) {
				$this->message['error'][] = __( 'Error: Feedback notify email field is empty.', 'elm' );
			}
			
			if ( isset( $_POST['feedback']['notify_admin'] ) && intval( $_POST['feedback']['notify_admin'] ) == 1 && ! filter_var( $_POST['feedback']['admin_email'], FILTER_VALIDATE_EMAIL ) ) {
				$this->message['error'][] = __( 'Error: Invalid feedback notify email format.', 'elm' );
			}
			
			if ( empty( $this->message['error'] ) ) {
				$this->save_settings();
				
				// Add message
				$this->message['update'][] = __( 'Your settings have been saved.', 'elm' );
			}
        }
        
        /*
         * Process style settings form
         */
        if ( isset( $_POST['elm_save_ur_settings_style'] ) && check_admin_referer( 'elm_ur_settings_style_page_action', 'elm_ur_settings_style_page_nonce' ) ) {
            $this->settings['style']['rating_image'] = sanitize_text_field( $_POST['rating_image'] );
            
            $this->settings['style']['rating_image_size'] = sanitize_text_field( $_POST['rating_image_size'] );
            
            if ( $_POST['max_ratings'] == 0 )
                $_POST['max_ratings'] = 1;
            
            $this->settings['style']['max_ratings'] = intval( $_POST['max_ratings'] );
			
			if ( $_POST['max_ratings'] )
            
            $this->settings['style']['color']['normal_fill'] = sanitize_text_field( $_POST['normal_fill'] );
            $this->settings['style']['color']['rated_fill']  = sanitize_text_field( $_POST['rated_fill'] );
            
            $this->settings['style']['template'] = wp_kses_post( $_POST['template'] );
			
			if ( empty( $this->message['error'] ) ) {
				$this->save_settings();

				// Add message
				$this->message['update'][] = __( 'Your settings have been saved.', 'elm' );
			}
        }
        
        /*
         * Process texts settings form
         */
        if ( isset( $_POST['elm_save_ur_settings_texts'] ) && check_admin_referer( 'elm_ur_settings_texts_page_action', 'elm_ur_settings_texts_page_nonce' ) ) {
            
            $this->settings['general_texts']['thankyou_for_voting']      = sanitize_text_field( $_POST['general_texts']['thankyou_for_voting'] );
            $this->settings['general_texts']['feedback_about_this_page'] = sanitize_text_field( $_POST['general_texts']['feedback_about_this_page'] );
            $this->settings['general_texts']['thankyou_for_feedback']    = sanitize_text_field( $_POST['general_texts']['thankyou_for_feedback'] );
            
            $max_ratings = $this->settings['style']['max_ratings'];
            
            if ( !$max_ratings )
                $max_ratings = 1;
            
            $this->save_settings();
            
            // Add message
            $this->message['update'][] = __( 'Your settings have been saved.', 'elm' );
        }
        
    }
    
    /**
     * Get settings
	 *
     * @param string $saved
     */
    function get_settings( $saved = true ) {
        if ( $saved == true )
            $this->settings = get_option( 'elm_ur_settings' );
        
        return apply_filters( 'elm_ur_get_settings', $this->settings );
    }
    
    /**
     * Get setting
	 *
     * @param string $param1
     * @param string $param2
     * @param string $param3
     */
    function get_setting( $param1 = '', $param2 = '', $param3 = '' ) {
        $settings = $this->get_settings();
        
        if ( $param1 ) {
            $setting = @$settings[$param1];
        }
        
        if ( $param1 && $param2 ) {
            $setting = @$settings[$param1][$param2];
        }
        
        if ( $param1 && $param2 && $param3 ) {
            $setting = @$settings[$param1][$param2][$param3];
        }
        
        return $setting;
    }
    
    /*
     * Save settings
     */
    function save_settings() {
        update_option( 'elm_ur_settings', $this->settings );
    }
    
    /*
     * Delete settings
     */
    function delete_settings( $array, $array2 = null ) {
        if ( !$array2 ) {
            unset( $this->settings[$array] );
        } else {
            unset( $this->settings[$array][$array2] );
        }
        
        $this->save_settings();
    }
    
    /*
     * Delete main settings
     */
    function delete_main_settings() {
        delete_option( 'elm_ur_settings' );
    }
    
    /*
     * Verify settings
     */
    function verify_settings() {
        $update_settings = false;
        
        $default_settings = array(
             'general' => array(
                 'allow_ratings_on' => array(
                     'post' => 1,
                    'page' => 1 
                ),
                'location' => array(
                     'after_post_content' => 1 
                ),
                'visibility' => array(
                     'home' => 1,
                    'single_pages' => 1,
                    'pages' => 1 
                ),
				'exclude_ratings' => array(
					'post_id' => '',
					'post_tag' => ''
				),
                'access' => 'registered_users_and_guests',
                'logging' => 'cookie',
				'location_own_hook_name' => '',
				'feedback' => array(
					'enable_feedback' => 0,
					'notify_admin' => 0
				),
                'rich_snippets' => 1,
				'wpml_support' => 0
            ),
            'style' => array(
                 'color' => array(
                    'normal_fill' => '#e8e8e8',
                    'rated_fill' => '#ffbb00' 
                ),
                'rating_image' => 'star-1',
                'rating_image_size' => '24px',
				'max_ratings' => 5,
                'template' => '%THANK_YOU_MESSAGE% <div class="elm-rating-wrapper">%RATING%</div>',
                'custom_svg' => '' 
            ),
            'general_texts' => array(
				'thankyou_for_voting' => 'Thank you for voting',
                'feedback_about_this_page' => 'Please leave your feedback',
                'thankyou_for_feedback' => 'Thank you for your feedback'
            ) 
        );
        
        foreach ( $default_settings as $element_settings => $settings ) {
            if ( is_array( $settings ) ) {
                foreach ( $settings as $element => $value ) {
                    if ( !isset( $this->settings[$element_settings][$element] ) ) {
                        $this->settings[$element_settings][$element] = $value;
                        $update_settings                             = true;
                    }
                }
            } else {
                if ( !isset( $this->settings[$element_settings] ) ) {
                    $this->settings[$element_settings] = $settings;
                    $update_settings                   = true;
                }
            }
            
            if ( $update_settings == true )
                $this->save_settings();
        }
    }
}

endif;
