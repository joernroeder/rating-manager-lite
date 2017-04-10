<?php 
/*
 * Display admin style page
*/

// don't load directly
if ( !defined('ABSPATH') )
	exit;
 
global $elm_rml_ratings;
$settings = $elm_rml_ratings->get_settings->get_settings();
?>

<div class="wrap rating-manager">
	<?php $elm_rml_ratings->get_settings_gui->messages_html(); ?>
		
	<?php $elm_rml_ratings->get_settings_gui->tabs_html(); ?>

    <h3><?php _e('Style Settings', 'elm'); ?></h3>

    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label><?php _e('SVG icons', 'elm'); ?></label>
                </th>
                <td>
					<fieldset><legend class="screen-reader-text"><span><?php _e('SVG', 'elm'); ?></span></legend>
					<select name="rating_image" id="rating-svg-icon">
						<?php
						$image_size_list = elm_rml_icon_list();
						foreach( $image_size_list as $k => $value ) {
							$selected = selected( $settings['style']['rating_image'], $k, false );
					
							echo '<option value="' . $k . '" '. $selected .'>'. $value .'</option>';
						}
						?>
					</select><br />

					<label><?php _e('Preview', 'elm'); ?></label>
					
					<div id="rating-svg-icon-preview"></div>
				</fieldset>
                </td>
            </tr>
			<tr>
                <th scope="row">
                    <label><?php _e('Icon size', 'elm'); ?></label>
                </th>
                <td>
				<select name="rating_image_size" id="rating_image_size">
					<?php
					$image_size_list = elm_rml_image_size();
					foreach($image_size_list as $k => $value ) {
						$selected = selected( $settings['style']['rating_image_size'], $k, false );
					
						echo '<option value="' . $k . '" '. $selected .'>'. $value .'</option>';
					}
					?>
				</select>
				
				<p class="description"><?php _e('Set size of rating form icons.', 'elm'); ?></p>
                </td>
            </tr>
			<tr>
                <th scope="row">
                    <label><?php _e('Maximum value', 'elm'); ?></label>
                </th>
                <td>
				<input type="text" name="max_ratings" id="max-ratings" class="small-text" value="<?php echo esc_html( $settings['style']['max_ratings'] ); ?>" />

				<p class="description"><?php _e('Set the maximum rating value (recommended value 5).', 'elm'); ?></p>
                </td>
            </tr>
			<tr>
                <th scope="row">
                    <label><?php _e('Icon colors', 'elm'); ?></label>
                </th>
                <td>
				
				<ul>
				<li>
				<div class="elm-ur-color">
					<label for="normal_fill_color_picker"><?php _e('Icon color (unrated)', 'elm'); ?></label><br />
					<div id="normal_fill_color_picker" class="colorSelector small-text">
						<div></div>
					</div>
						<input class="elm-color small-text elm-typography elm-typography-color" name="normal_fill" id="normal_fill_color" type="text" value="<?php echo $settings['style']['color']['normal_fill']; ?>" />
				</div>
				</li>
				
				<li>
				<div class="elm-ur-color">
					<label for="rated_fill_color_picker"><?php _e('Icon color (rated)', 'elm'); ?></label><br />
					<div id="rated_fill_color_picker" class="colorSelector">
						<div></div>
					</div>
						<input class="elm-color small-text elm-typography elm-typography-color" name="rated_fill" id="rated_fill_color" type="text" value="<?php echo $settings['style']['color']['rated_fill']; ?>" />
				</div>
				</li>
				
				</ul>

				<p class="description"><?php _e('Select colors for rating icons.', 'elm'); ?></p>
                </td>
            </tr>
			<tr>
                <th scope="row">
                    <label><?php _e('HTML template', 'elm'); ?></label>
                </th>
                <td>
				<textarea rows="10" cols="50" name="template" id="html-template" class="large-text code"><?php echo $settings['style']['template']; ?></textarea><br />
				<input type="button" name="reset_style_html_template" id="reset-style-html-template" class="button button-secondary" value="<?php _e('Default HTML template','elm'); ?>" />
				
				<p class="description"><?php _e('HTML template of rating form.', 'elm'); ?></p>
                </td>
            </tr>
        </table>

		<?php wp_nonce_field( 'elm_rml_settings_style_page_action', 'elm_rml_settings_style_page_nonce' ); ?>
		
        <p class="submit">
            <input type="submit" name="elm_save_ur_settings_style" id="submit" class="button button-primary" value="<?php _e('Save settings', 'elm'); ?>" />
        </p>
    </form>

</div>
