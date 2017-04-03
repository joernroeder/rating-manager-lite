<?php

if ( ! class_exists( 'Elm_UR_Settings_GUI' ) ) :

class Elm_UR_Settings_GUI {
    
    function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
    }
	
	/*
     * Add JavaScript for settings style page
     */
	function admin_head() {
		global $pagenow;
		
		$screen = get_current_screen();
		$settings_page = ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-style';
		
		if ( $screen->base == $settings_page && $pagenow == 'admin.php' ) {
			$svg_folder_url = ELM_UR_PLUGIN_URL . '/svg/';
			
			$output = "<script type=\"text/javascript\">";
			$output .= "var svg_folder_url = '". $svg_folder_url . "';
			jQuery( document ).ready(function() {
				var custom_svg = jQuery( '#custom_svg' );
				if ( jQuery( '#custom_svg' ).is(':checked') ) {
					var current_svg_icon = 'custom';
				} else {
					var current_svg_icon = jQuery( '#rating-svg-icon' ).val();
				}
				elm_change_ur_svg_icon( current_svg_icon );
			});";
			$output .= "</script>";
			
			echo $output;
		}
	}
    
	/**
     * Display tabs html
     */
    static function tabs_html() {
        $general_active  = ( basename( $_GET['page'] ) == 'settings-general.php' ) ? 'nav-tab-active' : '';
        $style_active    = ( basename( $_GET['page'] ) == 'settings-style.php' ) ? 'nav-tab-active' : '';
        $texts_active    = ( basename( $_GET['page'] ) == 'settings-messages.php' ) ? 'nav-tab-active' : '';
        
        $html = '<h2 class="nav-tab-wrapper" id="elm-settings-tabs">' . "\r\n";
        $html .= '<a class="nav-tab ' . $general_active . '" id="elm-settings-general-tab" href="' . admin_url( 'admin.php?page=' . ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-general.php' ) . '">' . __( 'General', 'elm' ) . '</a>' . "\r\n";
        $html .= '<a class="nav-tab ' . $style_active . '" id="elm-settings-style-tab" href="' . admin_url( 'admin.php?page=' . ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-style.php' ) . '">' . __( 'Style', 'elm' ) . '</a>' . "\r\n";
        $html .= '<a class="nav-tab ' . $texts_active . '" id="elm-settings-messages-tab" href="' . admin_url( 'admin.php?page=' . ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-messages.php' ) . '">' . __( 'Messages', 'elm' ) . '</a>' . "\r\n";
        $html .= '</h2>' . "\r\n";
        
        echo $html;
    }
    
	/**
     * Display stats tabs HTML
     */
    static function stats_tabs_html() {
		$tools_active     = ( basename( $_GET['page'] ) == 'stats-tools.php' ) ? 'nav-tab-active' : '';
        $overall_active     = ( basename( $_GET['page'] ) == 'stats-overall.php' ) ? 'nav-tab-active' : '';
        $all_ratings_active = ( basename( $_GET['page'] ) == 'stats.php' ) ? 'nav-tab-active' : '';
        
        $html = '<h2 class="nav-tab-wrapper" id="elm-settings-tabs">' . "\r\n";
        $html .= '<a class="nav-tab ' . $all_ratings_active . '" id="elm-all-ratings-tab" href="' . admin_url( 'admin.php?page=' . ELM_UR_PLUGIN_FOLDER . '/admin/panels/stats.php' ) . '">' . __( 'All ratings', 'elm' ) . '</a>' . "\r\n";
        $html .= '<a class="nav-tab ' . $overall_active . '" id="elm-overall-tab" href="' . admin_url( 'admin.php?page=' . ELM_UR_PLUGIN_FOLDER . '/admin/panels/stats-overall.php' ) . '">' . __( 'Overall statistics', 'elm' ) . '</a>' . "\r\n";
		$html .= '<a class="nav-tab ' . $tools_active . '" id="elm-tools-tab" href="' . admin_url( 'admin.php?page=' . ELM_UR_PLUGIN_FOLDER . '/admin/panels/stats-tools.php' ) . '">' . __( 'Tools', 'elm' ) . '</a>' . "\r\n";
        $html .= '</h2>' . "\r\n";
        
        echo $html;
    }
    
    /**
     * Create menu
     */
    function menu() {
		global $submenu;
	
        add_menu_page( __( 'General', 'elm' ), __( 'Rating Manager', 'elm' ), 'manage_options', ELM_UR_PLUGIN_PATH . '/admin/panels/settings-general.php', '', ELM_UR_PLUGIN_URL . '/assets/images/plugin-icon.png' );
        add_submenu_page( null, __( 'Style', 'elm' ), __( 'Style', 'elm' ), 'manage_options', ELM_UR_PLUGIN_PATH . '/admin/panels/settings-style.php' );
        add_submenu_page( null, __( 'Messages', 'elm' ), __( 'Texts', 'elm' ), 'manage_options', ELM_UR_PLUGIN_PATH . '/admin/panels/settings-messages.php' );
        
		// Statistics
        add_submenu_page( ELM_UR_PLUGIN_PATH . '/admin/panels/settings-general.php', __( 'Statistics', 'elm' ), __( 'Statistics', 'elm' ), 'manage_options', ELM_UR_PLUGIN_PATH . '/admin/panels/stats.php' );
        add_submenu_page( ELM_UR_PLUGIN_PATH . '/admin/panels/stats-overall.php', __( 'Statistics Overall', 'elm' ), __( 'Statistics Overall', 'elm' ), 'manage_options', ELM_UR_PLUGIN_PATH . '/admin/panels/stats-overall.php' );
		add_submenu_page( ELM_UR_PLUGIN_PATH . '/admin/panels/stats-tools.php', __( 'Statistics Tools', 'elm' ), __( 'Statistics Tools', 'elm' ), 'manage_options', ELM_UR_PLUGIN_PATH . '/admin/panels/stats-tools.php' );
        add_submenu_page( ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-general.php', __( 'PRO', 'elm' ), __( 'PRO', 'elm' ), 'manage_options', ELM_UR_PLUGIN_PATH . '/admin/panels/pro.php' );
        
        add_submenu_page( null, __( 'View Feedback', 'elm' ), __( 'View Feedback', 'elm' ), 'manage_options', 'elm-ur-view-feedback', array(
             $this,
            'stats_view_feedback_page' 
        ) );
		
		// Change Rating Manager submenu name to Settings
		$settings_general = ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-general.php';
		$submenu[$settings_general][0][0] = __('Settings', 'elm');
    }
    
	/**
     * Stats view feedback page
     */
    function stats_view_feedback_page() {
        require( ELM_UR_PLUGIN_ADMIN_PATH . '/panels/stats-view-feedback.php' );
    }
    
	/**
     * Enqueue admin CSS and scripts
     */
    function enqueue_scripts( $hook ) {
		wp_enqueue_script( 'elm-ur-admin', ELM_UR_PLUGIN_URL . '/assets/js/admin.js' );
	
        if ( $hook != ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-general.php' 
            && $hook != ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-style.php' 
            && $hook != ELM_UR_PLUGIN_FOLDER . '/admin/panels/settings-messages.php'
            && $hook != ELM_UR_PLUGIN_FOLDER . '/admin/panels/stats.php' 
            && $hook != ELM_UR_PLUGIN_FOLDER . '/admin/panels/stats-overall.php' 
            && $hook != ELM_UR_PLUGIN_FOLDER . '/admin/panels/stats-tools.php' )
            return;
        
        wp_enqueue_script( 'elm-ur-colorpicker', ELM_UR_PLUGIN_URL . '/assets/js/colorpicker.min.js' );
        
        wp_register_style( 'elm-ur-admin', ELM_UR_PLUGIN_URL . '/assets/css/admin.min.css' );
        wp_enqueue_style( 'elm-ur-admin' );
        
        wp_register_style( 'elm-ur-colorpicker', ELM_UR_PLUGIN_URL . '/assets/css/colorpicker.min.css' );
        wp_enqueue_style( 'elm-ur-colorpicker' );
    }
    
    /*
     * Get messages for admin pages
     */
	function get_messages() {
        $settings = new Elm_UR_Settings;
        
        if ( !empty( $settings->message ) ) {
            $messages = '';
            
            if ( !empty( $settings->message['update'] ) ) {
                foreach ( $settings->message['update'] as $message ) {
                    $messages .= $message . "<br /> \r\n";
                }
                
                $output = '<div class="updated"><p><strong>' . $messages . '</strong></p></div>';
                
                return $output;
            } else if ( !empty( $settings->message['error'] ) ) {
                foreach ( $settings->message['error'] as $message ) {
                    $messages .= $message . "<br /> \r\n";
                }
                
                $output = '<div class="error"><p><strong>' . $messages . '</strong></p></div>';
                
                return $output;
            }
        }
    }
    
    /*
     * Output messages for general pages
     */
    function messages_html() {
        echo $this->get_messages();
    }
}

endif;