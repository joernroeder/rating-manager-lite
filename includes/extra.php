<?php

/**
 * Add a link to the settings page to the plugins list
 *
 * @param array $links array of links for the plugins, adapted when the current plugin is found.
 *
 * @return array $links
 */
function elm_ur_plugin_links( $links ) {
    $get_pro = '<a title="Get Rating Manager Pro" href="https://www.elementous.com/product/premium-wordpress-plugins/rating-manager/">' . esc_html__( 'Get Rating Manager Pro', 'elm' ) . '</a>';
    array_unshift( $links, $get_pro );

    return $links;
}
add_filter( 'plugin_action_links_' . ELM_UR_PLUGIN_BASENAME, 'elm_ur_plugin_links' );