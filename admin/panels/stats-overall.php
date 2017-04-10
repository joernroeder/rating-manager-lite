<?php
/*
 * Display admin stats overall page
 */
 
 // don't load directly
if ( !defined('ABSPATH') )
	exit;

global $elm_rml_ratings;
?>
<div class="wrap">
    <div id="icon-options-general" class="icon32">
		<br />
	</div>
	
	<h2><?php _e( 'Rating Manager', 'elm' ); ?></h2>
	
	<?php $elm_rml_ratings->get_settings_gui->stats_tabs_html(); ?>
	
	<p><?php _e( 'Total number of ratings per day, week and month.', 'elm') ; ?></p>
	
	<table class="wp-list-table widefat fixed">
		<thead>
			<tr>
				<th scope="col" class="width-155">
					<span><?php _e( 'Post Type', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Today', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Week', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Month', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Year', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Overall', 'elm' ); ?></span>
				</th>
			</tr>
		</thead>
			
		<tfoot>
			<tr>
				<th scope="col" class="width-155">
					<span><?php _e( 'Post Type', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Today', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Week', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Month', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Year', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e( 'Overall', 'elm' ); ?></span>
				</th>
			</tr>
		</tfoot>
				
		<tbody>
			<?php 
				$post_types = $elm_rml_ratings->get_custom_post_types();
			
				$k = -1;
				foreach( $post_types as $post_type => $label ) :
					$k += 1;
				
					$stats = $elm_rml_ratings->stats->get_rated_posts_numb_date( $post_type );
					$class = '';
				
					if ( $k % 2 == 0 ) {
						$class = 'class="alternate"';
					}
				?>
				<tr <?php echo $class; ?>>
					<td>
						<?php echo $label; ?>
					</td>
					<td>
						<?php echo $stats['today']; ?>
					</td>
					<td>
						<?php echo $stats['week']; ?>
					</td>
					<td>
						<?php echo $stats['month']; ?>
					</td>	
					<td>
						<?php echo $stats['year']; ?>
					</td>	
					<td>
						<?php echo $stats['overall']; ?>
					</td>	
				</tr>
				<?php
				endforeach;
			?>
		</tbody>
	</table>
	
</div>