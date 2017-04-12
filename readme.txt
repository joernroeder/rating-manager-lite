=== Rating Manager Lite ===
Contributors: Elementous, dominykasgel, darius_fx
Donate link: https://www.elementous.com
Tags: rating, star rating, ratings, star, vote, voting, comment rating, bbpress rating, images rating
Requires at least: 3.0.1
Tested up to: 4.7.3
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin allows you to rate content by adding custom rating forms. Almost unlimited capabilities for customization.

== Description ==

Rating Manager Lite plugin is a first ever content plugin for WordPress that utilizes SVG image technology. Utilization of SVG technology gave a possibility for us to create very light, fast and highly customizable rating plugin. Rating Manager Lite is SEO friendly, and it can generate Rich Snippets, so, your website can provide more data to the search engines and advance in search results ranking. To make this plugin more efficient tool, we have integrated statistics engine, so, you can get a clear image of what’s happening on with your website content and comment rating. Moreover, to make this plugin more useful and attractive to WordPress developers, we have enriched it with hooks, filters, and functions that could help developers to use this plugin more efficient.

Rating Manager Lite is compatible with WooCommerce, bbPress, BuddyPress. The plugin is multi-site ready.

[Rating Manager Lite Documentation](https://www.elementous.com/documentation/#rating-manager)

We also have a public [GIT repository](https://github.com/elementous/rating-manager-lite) for this plugin and you're welcome to contribute your patch.

= PRO version =
The PRO version of the plugin includes more than 100 rating icons, custom rating icon (SVG) upload, comments rating and premium support. [Get Rating Manager PRO](https://www.elementous.com/product/premium-wordpress-plugins/rating-manager/)

**Shortcodes**
`
[elm_rml_rating] - No parameters
[elm_rml_rating_readonly] - Required parameter: average (up to 5) e.g. [elm_rml_rating_readonly average="4"] 
[elm_rml_top_rated] - Optional parameters: post_type, sort, limit
`

**Widgets**
`
Rating
Top Rated Posts
Sort by dropdown (highest/lowest rating)
`

**Filters**
`
elm_rml_html_template - front-end template
elm_rml_feedback_form_html - front-end feedback form template
elm_rml_readonly_rating_form - front-end read-only rating form template
elm_rml_rating_js - front-end JavaScript code
elm_rml_average_calculate_func - average calculation
elm_rml_rated_users_number - rated users number
elm_rml_leave_your_feedback - leave your feedback text
elm_rml_ratings_js_url - ratings JavaScript file url
elm_rml_js_url - rating manager JavaScript file url
elm_rml_css_url - rating manager CSS file url
elm_rml_feedback_notification_subject - Feedback notification email subject text
elm_rml_feedback_notification_message - Feedback notification email message text
elm_rml_get_custom_post_types - Available custom post types 
elm_rml_get_settings - Rating Manager settings
elm_rml_top_rated_html_shortcode - front-end top rated posts shortcode template
elm_rml_ratings_sortby_dropdow - front-end sortby dropdown
elm_rml_top_rated_posts_widget - front-end top rated posts widget template
`

**Actions**
`
elm_rml_init - Plugin init
elm_rml_feedback_ajax_callback - Feedback AJAX callback
elm_rml_add_rating_ajax_callback - Rating AJAX callback
elm_rml_add_rating - After adding rating
elm_rml_update_average_rating - After updating average rating
elm_rml_add_feedback - After adding feedback
elm_rml_rating_js - After Rating Manager JavaScript code
elm_rml_below_html_template - Below rating template
elm_rml_after_html_template - After rating template
elm_rml_enqueue_scripts - After Enqueing scripts
`

== Installation ==
1. Upload `rating-manager-lite` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

For FAQ and more information, please go to [Rating Manager Lite Documentation](https://www.elementous.com/documentation/#rating-manager)

== Screenshots ==

1. Rating form
2. Media rating
3. bbPress topics/replies rating
4. General settings
5. Styling settings
6. Messages settings
7. Statistics settings
8. Overall statistics

== Changelog ==

= 1.0 =
* Initial release.
