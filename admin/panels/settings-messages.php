<?php 
/*
 * Display admin texts page
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

    <h3><?php _e('Messages', 'elm'); ?></h3>

    <form action="" method="post">
        <table class="form-table">
			<tr>
                <th scope="row">
                    <label><?php _e('Thank you for voting', 'elm'); ?></label>
                </th>
                <td>
				<input type="text" name="general_texts[thankyou_for_voting]" id="general_texts[thankyou_for_voting]" class="regular-text" value="<?php echo $settings['general_texts']['thankyou_for_voting']; ?>" />
                </td>
            </tr>
			<tr>
                <th scope="row">
                    <label><?php _e('Feedback about this page', 'elm'); ?></label>
                </th>
                <td>
				<input type="text" name="general_texts[feedback_about_this_page]" id="general_texts[feedback_about_this_page]" class="regular-text" value="<?php echo $settings['general_texts']['feedback_about_this_page']; ?>" />
                </td>
            </tr>
			<tr>
                <th scope="row">
                    <label><?php _e('Thank you for feedback', 'elm'); ?></label>
                </th>
                <td>
				<input type="text" name="general_texts[thankyou_for_feedback]" id="general_texts[thankyou_for_feedback]" class="regular-text" value="<?php echo $settings['general_texts']['thankyou_for_feedback']; ?>" />
                </td>
            </tr>
        </table>

		<?php wp_nonce_field( 'elm_rml_settings_texts_page_action', 'elm_rml_settings_texts_page_nonce' ); ?>
		
        <p class="submit">
            <input type="submit" name="elm_save_ur_settings_texts" id="submit" class="button button-primary" value="<?php _e('Save settings', 'elm'); ?>" />
        </p>
    </form>

</div>
