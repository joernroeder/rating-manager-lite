<?php
/*
 * Display admin general page
*/

// don't load directly
if ( !defined('ABSPATH') )
    exit;

global $elm_ur_ratings;
$settings = $elm_ur_ratings->get_settings->get_settings();
?>

<div class="wrap rating-manager">
    <h3><?php _e('PRO', 'elm'); ?></h3>

    <p>
        We recommend you to use the Pro version of the plugin and unlock more customization options. <a href="https://www.elementous.com/product/premium-wordpress-plugins/rating-manager/" class="button-primary" target="_blank">Read more</a>
    </p>

</div>