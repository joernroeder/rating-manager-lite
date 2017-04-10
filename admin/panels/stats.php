<?php 
/*
 * Display admin stats page
*/

// don't load directly
if ( !defined('ABSPATH') )
	exit;
 
global $elm_rml_ratings;
	
$stats = $elm_rml_ratings->stats->get_stats();

$post_types = $elm_rml_ratings->stats->get_post_types_db();

$sort_types = $elm_rml_ratings->stats->get_sort_types();

$dates = $elm_rml_ratings->stats->get_dates();

$items = $elm_rml_ratings->stats->stats_content($stats['items'], $stats['limit']); 
?>

<div class="wrap">
    <div id="icon-options-general" class="icon32">
		<br />
	</div>
	
	<h2><?php _e('Rating Manager', 'elm'); ?></h2>

<?php $elm_rml_ratings->get_settings_gui->stats_tabs_html(); ?>

<?php
	if ( !empty( $items) ) :
?>
	<div class="tablenav top">
		<div class="alignleft actions">
			<form action="" method="post">
				<select name="stats_post_type">
					<option value=""><?php _e('Post type', 'elm'); ?></option>
					<?php
					foreach( $post_types as $post_type ) {
						if ( isset( $_POST['stats_post_type'] ) ) {
							$selected = ( $post_type == $_POST['stats_post_type'] ) ? 'selected' : '';
						} else {
							$selected = ( $post_type == $_GET['stats_post_type'] ) ? 'selected' : '';
						}
						
						echo '<option value="' . strtolower( $post_type ) . '" '. $selected .'>' . ucfirst( $post_type ) .'</option>';
					}
					?>
				</select>
				<select name="stats_sort_type">
					<option value="asc"><?php _e('Sort', 'elm'); ?></option>
					
					<?php
					foreach( $sort_types as $k => $sort_type ) {
						if ( isset($_POST['stats_sort_type'] ) ) {
							$selected = ($k == $_POST['stats_sort_type']) ? 'selected' : '';
						} else {
							$selected = ($k == $_GET['stats_sort_type']) ? 'selected' : '';
						}
						
						echo '<option value="' . $k . '" '. $selected .'>' . $sort_type .'</option>';
					}
					?>
				</select>
				<select name="stats_date">
					<option value=""><?php _e('Date', 'elm'); ?></option>
					<?php
					foreach( $dates as $k => $date ) {
						if ( isset($_POST['stats_sort_type'] ) ) {
							$selected = ($k == $_POST['stats_date']) ? 'selected' : '';
						} else {
							$selected = ($k == $_GET['stats_date']) ? 'selected' : '';
						}
						
						echo '<option value="' . $k . '" '. $selected .'>' . $date .'</option>';
					}
					?>
				</select>
				<input type="submit" name="ur_stats_filter" class="button action" value="<?php _e('Filter', 'elm'); ?>" />
			</form>
		</div>
		
		<div class="tablenav-pages">
			<?php $elm_rml_ratings->stats->stats_pagination($stats['qty_pages'], $stats['qty_items']); ?>
		</div>
	</div>
	<?php
	endif;
	
	if ( !empty( $items) ) :
	?>
	<table class="wp-list-table widefat fixed">
		<thead>
			<tr>
				<th scope="col" class="width-155">
					<span><?php _e('Title', 'elm'); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e('Type', 'elm'); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e('Average Rating', 'elm'); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e('Votes', 'elm'); ?></span>
				</th>
				<th scope="col">
					<span><?php _e('Feedback', 'elm'); ?></span>
				</th>
			</tr>
		</thead>
			
		<tfoot>
			<tr>
				<th scope="col" class="width-155">
					<span><?php _e('Title', 'elm'); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e('Type', 'elm'); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e('Average Rating', 'elm'); ?></span>
				</th>
				<th scope="col" class="width-105">
					<span><?php _e('Votes', 'elm'); ?></span>
				</th>
				<th scope="col">
					<span><?php _e('Feedback', 'elm'); ?></span>
				</th>
			</tr>
		</tfoot>
				
		<tbody>
			<?php 
				foreach( $items as $k => $item ):
					$class = '';
						
					if ( $k % 2 == 0 ) {
						$class = 'class="alternate"';
					}
				?>
				<tr <?php echo $class; ?>>
					<td>
						<strong><a href="<?php echo get_permalink( $item['id'] ); ?>" target="_blank"><?php echo get_the_title( $item['id'] ); ?></a></strong>
					</td>
					<td>
					<?php echo ucfirst( get_post_type( $item['id']) ); ?>
					</td>
					<td>
							<?php echo $item['average_rating']; ?>
					</td>
					<td>
						<?php echo $elm_rml_ratings->get_rated_by_users_num( $item['id'] ); ?>
					</td>
					<td>
						<?php echo $elm_rml_ratings->stats->get_view_feedback_url( $item['id'] ); ?>
					</td>	
				</tr>
				<?php
				endforeach;
			?>
		</tbody>
	</table>
	
	<?php
	else :
	?>
	
	<p><?php _e('No rated pages have been found.', 'elm'); ?></p>
	
	<?php
	endif;
	?>
</div>