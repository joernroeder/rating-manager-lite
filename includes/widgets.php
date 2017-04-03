<?php 

// don't load directly
if ( !defined('ABSPATH') )
	exit;

class UR_Top_Rated_Posts_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress
	 */
	function __construct() {
		parent::__construct(
			'elm_ur_top_rated_posts', // Base ID
			__('Top Rated Posts', 'elm'), // Name
			array( 'description' => __( 'Display highest rated posts.', 'elm' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments
	 * @param array $instance Saved values from database
	 */
	public function widget( $args, $instance ) {
		global $wpdb;
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Top Rated Posts', 'elm' );
		}
		
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		
		$sql = "SELECT DISTINCT(post_id) FROM {$wpdb->prefix}elm_ratings WHERE type = '{$instance['post_type']}'";
		
		switch ( sanitize_text_field( $instance['date'] ) ) {
			 case "today":
				$sql .= " AND SUBSTR(date,1,10) = CURDATE()";
				break;
			case "3_days":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 3 DAY AND CURDATE()";
				break;
			case "7_days":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()";
				break;
			case "14_days":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 14 DAY AND CURDATE()";
				break;
			case "1_month":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 1 MONTH AND CURDATE()";
				break;
			case "3_months":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 3 MONTH AND CURDATE()";
				break;
			case "6_months":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 6 MONTH AND CURDATE()";
				break;
			case "1_year":
				$sql .= " AND SUBSTR(date,1,10) BETWEEN CURDATE() - INTERVAL 1 YEAR AND CURDATE()";
				break;
		}
		
		$sql .= "LIMIT {$instance['limit']}";
		
		$posts = $wpdb->get_results( $sql );
		
        if ( empty( $posts ) )
            return;
			
		 foreach ( $posts as $k => $post ) {
            $average = intval( get_post_meta( $post->post_id, '_average_page_rating', TRUE ) );
            
            $_posts[$k]['id']             = $post->post_id;
            $_posts[$k]['average_rating'] = $average;
        }
        
        foreach ( $_posts as $k => $v ) {
            $posts_average[$k] = intval( $v['average_rating'] );
        }
        
        if ( $instance['sort_type'] == 'desc' ) {
            arsort( $posts_average );
        } else {
            asort( $posts_average );
        }
        
        $html = '';
        
        if ( $posts_average ) {
            $html .= '<ul class="elm-top-rated top-rated-widget">';
            
            foreach ( $posts_average as $key => $val ) {
                $html .= '<li><a href="' . get_permalink( $_posts[$key]['id'] ) . '">' . get_the_title( $_posts[$key]['id'] ) . '</a></li>' . "\r\n";
            }
            
            $html .= '</ul>';
        }
		
		echo apply_filters( 'elm_ur_top_rated_posts_widget', $html );
		
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database
	 */
	public function form( $instance ) {
		global $elm_ur_ratings;
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Top Rated Posts', 'elm' );
		}
		
		if ( isset( $instance[ 'limit' ] ) ) {
			$limit = $instance[ 'limit' ];
		} else {
			$limit = 5;
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e( 'Date:' ); ?></label><br />
		<select id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" class="widefat">
		<?php
		$options = $elm_ur_ratings->stats->get_dates();
		
		foreach( $options as $key => $value ) {
			$selected = ( $instance[ 'date' ] == $key ) ? 'selected' : '';
			echo '<option value="' . $key . '" '. $selected .'>' . $value .'</option>';
		}
		?>
		</select> 
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post type:' ); ?></label><br />
		<select id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" class="widefat">
		<?php
		$options = $elm_ur_ratings->get_custom_post_types();
				
		foreach ( $options as $key => $value ) :
			$selected = ( $instance[ 'post_type' ] == $key ) ? 'selected' : '';
		
			echo '<option value="'. $key .'" '. $selected .'>'. $value .'</option>' . "\r\n";
		endforeach;
		?>
		</select> 
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'sort_type' ); ?>"><?php _e( 'Sort:' ); ?></label> 
		<select id="<?php echo $this->get_field_id( 'sort_type' ); ?>" name="<?php echo $this->get_field_name( 'sort_type' ); ?>" class="widefat">
		<?php 
		$options = array('asc' => 'ASC', 'desc' => 'DESC');
			
		foreach ( $options as $key => $value ) :
			$selected = ( $instance[ 'sort_type' ] == $key ) ? 'selected' : '';
		
			echo '<option value="'. $key .'" '. $selected .'>'. $value .'</option>' . "\r\n";
		endforeach;
		?>
		</select> 
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo $limit; ?>" />
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved
	 * @param array $old_instance Previously saved values from database
	 *
	 * @return array Updated safe values to be saved
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['date'] = ( ! empty( $new_instance['date'] ) ) ? strip_tags( $new_instance['date'] ) : '';
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['post_type'] = ( ! empty( $new_instance['post_type'] ) ) ? strip_tags( $new_instance['post_type'] ) : '';
		$instance['sort_type'] = ( ! empty( $new_instance['sort_type'] ) ) ? strip_tags( $new_instance['sort_type'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : '';
		
		
		return $instance;
	}
}

class UR_Ratings_Sortby_Dropdown extends WP_Widget {
	/**
	 * Register widget with WordPress
	 */
	function __construct() {
		parent::__construct(
			'elm_ur_sortby_dropdown', // Base ID
			__('Sort by dropdown', 'elm'), // Name
			array( 'description' => __( 'Sort by dropdown for rated posts.', 'elm' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments
	 * @param array $instance Saved values from database
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
			
		elm_ratings_sortby_dropdown();
	}

	/**
	 * Back-end widget form
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Sort by', 'elm' );
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved
	 * @param array $old_instance Previously saved values from database
	 *
	 * @return array Updated safe values to be saved
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		
		return $instance;
	}
}

class UR_Rating_Form extends WP_Widget {
	/**
	 * Register widget with WordPress
	 */
	function __construct() {
		parent::__construct(
			'elm_ur_rating_form', // Base ID
			__('Rating', 'elm'), // Name
			array( 'description' => __( 'Rating form.', 'elm' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments
	 * @param array $instance Saved values from database
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
			
		if ( ! is_home() && ! is_archive() && ! is_search() )	
			elm_ratings_form();
	}

	/**
	 * Back-end widget form
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Rating', 'elm' );
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved
	 * @param array $old_instance Previously saved values from database
	 *
	 * @return array Updated safe values to be saved
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		
		return $instance;
	}
}

add_action('widgets_init',
     create_function('', 'return register_widget("UR_Top_Rated_Posts_Widget");')
);
add_action('widgets_init',
     create_function('', 'return register_widget("UR_Ratings_Sortby_Dropdown");')
);
add_action('widgets_init',
     create_function('', 'return register_widget("UR_Rating_Form");')
);