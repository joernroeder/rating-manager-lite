<?php
/*
 * Display admin stats view feedback page
 */
 
// don't load directly
if ( !defined('ABSPATH') )
	exit;
	
global $elm_rml_ratings;

$feedback_id = ( isset($_GET['feedback_id'] ) ) ? intval ($_GET['feedback_id'] ) : 0;

$feedback_posts = $elm_rml_ratings->stats->get_feedback( $feedback_id );


if( $feedback_id == 0 || !$feedback_posts )
	return;
?>
<div class="wrap">
    <div id="icon-options-general" class="icon32">
		<br />
	</div>
	
	<h2><?php _e( 'Rating Manager', 'elm' ); ?></h2>
	
<?php $elm_rml_ratings->get_settings_gui->stats_tabs_html(); ?>

<br />

<table class="wp-list-table widefat fixed">
		<thead>
			<tr>
				<th scope="col" class="width-150">
					<span><?php _e( 'Name', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-150">
					<span><?php _e( 'Email', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-150">
					<span><?php _e( 'Feedback', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-150">
					<span><?php _e( 'Rating', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-150">
					<span><?php _e( 'Date', 'elm' ); ?></span>
				</th>
			</tr>
		</thead>
			
		<tfoot>
			<tr>
				<th scope="col" class="width-150">
					<span><?php _e( 'Name', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-150">
					<span><?php _e( 'Email', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-150">
					<span><?php _e( 'Feedback', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-150">
					<span><?php _e( 'Rating', 'elm' ); ?></span>
				</th>
				<th scope="col" class="width-150">
					<span><?php _e( 'Date', 'elm' ); ?></span>
				</th>
			</tr>
		</tfoot>
				
		<tbody>
			<?php 
				foreach( $feedback_posts as $k => $feedback ):
					$class = '';
						
					if ( $k % 2 == 0 ) {
						$class = 'class="alternate"';
					}
				?>
				<tr <?php echo $class; ?>>
					<td>
						<?php echo esc_attr( $feedback->name ); ?>
					</td>
					<td>
						<?php echo esc_attr( $feedback->email ); ?>
					</td>
					<td>
						<?php echo esc_attr( $feedback->feedback ); ?>
					</td>
					<td>
						<?php echo esc_attr( $feedback->rating ); ?>
					</td>
					<td>
						<?php echo esc_attr( $feedback->date ); ?>
					</td>
				</tr>
				<?php
				endforeach;
			?>
		</tbody>
	</table>
</div>