<?php
/**
 *
 * bbPress compatibility
 */

/*
 * Add rating form to forums loop.
 */
function elm_rml_bbpress_forums() {
    global $elm_rml_ratings;
    $settings = $elm_rml_ratings->get_settings->get_settings();

    if($settings['general']['allow_ratings_on']['forum'] == 1 ) {
        elm_rml_ratings_form();
    }
}
add_action('bbp_theme_after_forum_description', 'elm_rml_bbpress_forums');

/*
 * Add rating form to topics loop.
 */
function elm_rml_bbpress_topics() {
    global $elm_rml_ratings;
    $settings = $elm_rml_ratings->get_settings->get_settings();

    if($settings['general']['allow_ratings_on']['topic'] == 1 ) {
        elm_rml_ratings_form();
    }
}
add_action('bbp_theme_after_topic_meta', 'elm_rml_bbpress_topics');

/*
 * Add rating form to a single topic.
 */
function elm_rml_bbpress_single_topic() {
    global $post, $elm_rml_ratings;
    $settings = $elm_rml_ratings->get_settings->get_settings();

    if(get_post_type($post) == 'topic' && $settings['general']['allow_ratings_on']['topic'] == 1 ){
        elm_rml_ratings_form();
    }
}
add_action('bbp_theme_after_reply_content', 'elm_rml_bbpress_single_topic');

/*
 * Add rating form to a single reply.
 */
function elm_rml_bbpress_single_reply() {
    global $elm_rml_ratings;
    $settings = $elm_rml_ratings->get_settings->get_settings();

    if($settings['general']['allow_ratings_on']['reply'] == 1 ) {
        elm_rml_ratings_form();
    }
}
add_action('bbp_theme_after_reply_content', 'elm_rml_bbpress_single_reply');