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
	<?php $elm_ur_ratings->get_settings_gui->messages_html(); ?>
		
	<?php $elm_ur_ratings->get_settings_gui->tabs_html(); ?>

    <h3><?php _e('General Settings', 'elm'); ?></h3>

    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label><?php _e('Enable ratings on', 'elm'); ?></label>
                </th>
                <td>
				<fieldset><legend class="screen-reader-text"><span><?php _e('Enable ratings on', 'elm'); ?></span></legend>
				<?php
				$options = $elm_ur_ratings->get_custom_post_types();
				
				foreach ( $options as $key => $value ) :
					echo '<label for="allow_ratings_on['. $key .']"><input type="checkbox" name="allow_ratings_on['. $key .']" id="allow_ratings_on['. $key .']" value="'. $key .'" '. checked( @$settings['general']['allow_ratings_on'][$key], 1, false ) .' />
						'. $value .'</label><br />';
				endforeach;
				?>
				</fieldset>
				
				<p class="description"><?php _e('Enable rating system on preferred post types.', 'elm'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Rating form location', 'elm'); ?></label>
                </th>
                <td>
				<fieldset><legend class="screen-reader-text"><span><?php _e('Rating form location', 'elm'); ?></span></legend>
				<?php 
				$options = array( 'before_post_content' => __('Before post content', 'elm'), 'after_post_content' => __('After post content', 'elm') );
				
				foreach ( $options as $key => $value ) :
					echo '<label for="location['. $key .']"><input type="checkbox" name="location['. $key .']" id="location['. $key .']" value="'. $key .'" '. checked( @$settings['general']['location'][$key], 1, false ) .' />
					'. $value .'</label><br />';
				endforeach;
				?>
		
				<label><?php _e('Hook name:', 'elm'); ?>
				<input type="text" name="location_own_hook_name" id="location-own-hook-name" class="regular-text" value="<?php echo $settings['general']['location_own_hook_name']; ?>" /></label>
				</fieldset>
				
				<p class="description"><?php _e('Select preferred rating form location.', 'elm'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="siteurl"><?php _e('Visibility', 'elm'); ?></label>
                </th>
                <td>
				<fieldset><legend class="screen-reader-text"><span><?php _e('Visibility', 'elm'); ?></span></legend>
                <?php
				$options = array( 'home' => __('Home'), 'single_pages' => __('Single post pages', 'elm'), 'pages' => __('Single pages', 'elm'), 'archives' => __('Archives', 'elm')
				//'search_results' => __('Search results', 'elm') 
				);
				
				foreach ( $options as $key => $value ) :
					echo '<label for="visibility['. $key .']"><input type="checkbox" name="visibility['. $key .']" id="visibility['. $key .']" value="'. $key .'" '. checked( @$settings['general']['visibility'][$key], 1, false ) .' />
				'. $value .'</label><br />';
				endforeach;
				?>
				</fieldset>
				
				<p class="description"><?php _e('Rating form visibility on other non rateable pages (archives and other).', 'elm'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="exclude-ratings-by"><?php _e('Exclusion', 'elm'); ?></label>
                </th>
                <td>
				<fieldset><legend class="screen-reader-text"><span><?php _e('Exclusion', 'elm'); ?></span></legend>
				<label for="exclude-ratings-by">
                <input type="checkbox" name="exclude_ratings_by" id="exclude-ratings-by" value="1" <?php checked( @$settings['general']['exclude_ratings_by'], 1, true ) ?> />
				<?php _e('Exclude ratings on posts by', 'elm'); ?></label><br />
			
				<label for="exclude-ratings-by-post-id"><?php _e('Post ID:', 'elm'); ?>
				<input type="text" name="exlcude_ratings_by_post_id" id="exclude-ratings-by-post-id" class="regular-text exclude-by-post-id" value="<?php echo $settings['general']['exclude_ratings']['post_id']; ?>" />
				</label><br />
				
				<label for="exclude-ratings-by-post-tag"><?php _e('Post tag:', 'elm'); ?>
				<input type="text" name="exlcude_ratings_by_post_tag" id="exclude-ratings-by-post-tag" class="regular-text" value="<?php echo $settings['general']['exclude_ratings']['post_tag']; ?>"/>
				</label>
				</fieldset>

				<p class="description"><?php _e('Exclude content from rating system.', 'elm'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="access"><?php _e('Access', 'elm'); ?></label>
                </th>
                <td>
				<select name="access" id="access">
				<?php
				$options = array( 'guests' => __('Guests', 'elm'), 'registered_users' => __('Registered users', 'elm'), 'registered_users_and_guests' => __('Registered users and guests', 'elm') );
				
				foreach ( $options as $key => $value ) :
					$selected = ( $settings['general']['access'] == $key ) ? 'selected' : '';
					echo '<option value="'. $key .'" '. $selected .'>'. $value .'</option>' . "\r\n";
				endforeach;
				?>
				</select>
				<p class="description"><?php _e('Select access strategy.', 'elm'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Logging', 'elm'); ?></th>
                <td>
				<select name="logging" id="logging">
				<?php
				$options = array( 'cookie' => __('Cookie', 'elm'), 'ip' => __('IP', 'elm'), 'cookie_and_ip' => __('Cookie and IP', 'elm'), 'username' => __('Username', 'elm') );
				
				foreach( $options as $key => $value ) :
					$selected = ( $settings['general']['logging'] == $key ) ? 'selected' : '';
					echo '<option value="'. $key .'" '. $selected .'>'. $value .'</option>' . "\r\n";
				endforeach;
				
				?>
				</select>
				<p class="description"><?php _e('Select logging strategy.', 'elm'); ?></p>
                </td>
            </tr>
			<tr>
                <th scope="row">
                    <label for="feedback[enable_feedback]"><?php _e('Feedback', 'elm'); ?></label>
                </th>
                <td>
				<input type="checkbox" name="feedback[enable_feedback]" id="feedback[enable_feedback]" value="1" <?php checked( $settings['general']['feedback']['enable_feedback'] ); ?> />
				<label for="feedback[enable_feedback]"><?php _e('Enable feedback message', 'elm'); ?></label>
				
				<br class="clear" />
				<p class="description"><?php _e('Enable or disable Feedback form.', 'elm'); ?></p>
			   </td>
            </tr>
			<tr>
                <th scope="row">
                    <label for="feedback[notify_admin]"><?php _e('Notifications', 'elm'); ?></label>
                </th>
				<td>
				
				<fieldset><legend class="screen-reader-text"><span><?php _e('Notifications', 'elm'); ?></span></legend>
				
				<label for="feedback[notify_admin]">
				<input type="checkbox" name="feedback[notify_admin]" id="feedback[notify_admin]" value="1" <?php checked( $settings['general']['feedback']['notify_admin'] ); ?> />
				<?php _e('Notify administrator about every new feedback', 'elm'); ?></label>
				<br />

				<label for="feedback[admin_email]"><?php _e('Email:', 'elm'); ?>
				<input type="text" name="feedback[admin_email]" id="feedback[admin_email]" class="regular-text" value="<?php echo @$settings['general']['feedback']['admin_email']; ?>" />
				</label>
				
				</fieldset>
				
				<p class="description"><?php _e('Feedback form notifications.', 'elm'); ?></p>
				</td>
			</tr>
			<tr>
                <th scope="row">
                    <label for="rich_snippets"><?php _e('Structured data', 'elm'); ?></label>
                </th>
                <td>
				<input type="checkbox" name="rich_snippets" id="rich_snippets" value="1" <?php checked( $settings['general']['rich_snippets'] ); ?> />
				<label for="rich_snippets"><?php printf( __('Enable Rich Snippets (<a href="%s" target="_blank">schema.org</a>)', 'elm'), 'http://schema.org/' );?></label>
				
				<br class="clear" />
				<p class="description"><?php _e('Enable Rich Snippets (Google, Bing, Yahoo!, Yandex compatible).', 'elm'); ?></p>
			   </td>
            </tr>
			
			<?php
			// WPML support
			if ( class_exists( 'SitePress' ) ) :
			?>
			<tr>
                <th scope="row">
                    <label for="wpml_support"><?php _e('WPML sync', 'elm'); ?></label>
                </th>
                <td>
				<input type="checkbox" name="wpml_support" id="wpml_support" value="1" <?php checked( $settings['general']['wpml_support'] ); ?> />
				<label for="wpml_support"><?php _e( 'WPML synchronization', 'elm' ); ?></label>
				
				<br class="clear" />
				<p class="description"><?php _e('Enable rating score synchronization between translations of posts and pages.', 'elm'); ?></p>
			   </td>
            </tr>
			<?php
			endif;
			?>
			
        </table>

		<?php wp_nonce_field( 'elm_ur_settings_general_action', 'elm_ur_settings_general_nonce' ); ?>
		
        <p class="submit">
            <input type="submit" name="elm_save_ur_settings_general" id="submit" class="button button-primary" value="<?php _e('Save settings', 'elm'); ?>" />
        </p>
    </form>

</div>
