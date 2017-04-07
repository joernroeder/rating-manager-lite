<?php
/*
  Plugin Name: Rating Manager Lite
  Plugin URI: https://www.elementous.com/product/premium-wordpress-plugins/rating-manager/
  Description: Get more feedback from your website visitors by adding rating forms for content. This is the first ever WordPress rating plugin that uses SVG images as rating form icons. Almost unlimited capabilities for customisation.
  Author: Elementous
  Author URI: https://www.elementous.com
  Version: 1.0
*/

define( 'ELM_UR_VERSION', '1.0' );
define( 'ELM_UR_PLUGIN_BASENAME', plugin_basename(__FILE__) );
define( 'ELM_UR_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'ELM_UR_PLUGIN_ADMIN_PATH', ELM_UR_PLUGIN_PATH . '/admin' );
define( 'ELM_UR_PLUGIN_INCLUDES_PATH', ELM_UR_PLUGIN_PATH . '/includes' );
define( 'ELM_UR_PLUGIN_CLASSES_PATH', ELM_UR_PLUGIN_PATH . '/includes/classes' );
define( 'ELM_UR_PLUGIN_SVG_PATH', ELM_UR_PLUGIN_PATH . '/svg' );
define( 'ELM_UR_PLUGIN_FOLDER', basename( ELM_UR_PLUGIN_PATH ) );
define( 'ELM_UR_PLUGIN_URL', plugins_url() . '/' . ELM_UR_PLUGIN_FOLDER );
define( 'ELM_UR_PLUGIN_PAGED', 20 ); // Pages count for stats pagination

if ( is_admin() ) {
	require ELM_UR_PLUGIN_ADMIN_PATH . '/admin.php';
}

require ELM_UR_PLUGIN_CLASSES_PATH . '/rating-manager.class.php';
require ELM_UR_PLUGIN_CLASSES_PATH . '/shortcodes.class.php';
require ELM_UR_PLUGIN_INCLUDES_PATH . '/actions.php';
require ELM_UR_PLUGIN_INCLUDES_PATH . '/template-functions.php';
require ELM_UR_PLUGIN_INCLUDES_PATH . '/widgets.php';
require ELM_UR_PLUGIN_INCLUDES_PATH . '/extra.php';

// bbPress compatibility
if(class_exists('bbPress')) {
	require ELM_UR_PLUGIN_INCLUDES_PATH . '/bbpress.php';
}

$elm_ur_ratings = new Elm_Rating_Manager();
$elm_ur_shortcodes = new Elm_UR_Shortcodes();

register_activation_hook( __FILE__, array( $elm_ur_ratings, 'install' ) );