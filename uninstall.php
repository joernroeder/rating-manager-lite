<?php
/**
 * Rating Manager Uninstall
 *
 * Uninstalling Rating Manager deletes options and tables.
 *
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

global $wpdb;

// Delete tables
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "elm_ratings_log" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "elm_ratings_feedback" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "elm_ratings" );
		
$wpdb->hide_errors();
		
// Delete options
delete_option( 'elm_ultimate_ratings' );
delete_option( 'elm_ur_settings' );
