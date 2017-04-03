<?php
/*
 * Display admin stats overall page
 */
 
 // don't load directly
if ( !defined('ABSPATH') )
	exit;

global $elm_ur_ratings;
?>
<div class="wrap">
    <div id="icon-options-general" class="icon32">
		<br />
	</div>
	
	<h2><?php _e( 'Rating Manager', 'elm' ); ?></h2>
	
	<?php $elm_ur_ratings->get_settings_gui->stats_tabs_html(); ?>
	
	<br />
	<table class="ur_tools_table widefat" cellspacing="0">
		<thead class="tools">
			<tr>
				<th colspan="2"><?php _e('Tools', 'elm'); ?></th>
			</tr>
		</thead>
		<tbody class="tools">
			<tr>
				<td><?php _e('Reset rating data (content)', 'elm'); ?></td>
				<td>
					<p>
						<a href="javascript:void(0)" id="reset-post-type-stats" class="button"><?php _e('Reset content rating stats', 'elm'); ?></a> <span class="reset-post-type-stats-message"></span><input type="hidden" name="reset_post_type_stats_nonce" id="reset-post-type-stats-nonce" value="<?php echo wp_create_nonce( 'elm_ur_reset_post_type_stats_action' ); ?>" />
						<span class="description"><?php _e('Reset all content rating data', 'elm'); ?>.</span><br />
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	
</div>