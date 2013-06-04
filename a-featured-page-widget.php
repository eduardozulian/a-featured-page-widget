<?php
/*
Plugin Name: A Featured Page Widget
Plugin URI: http://github.com/eduardozulian/a-featured-page-widget
Description: Feature a page and display its excerpt and post thumbnail.
Version: 1.0
Author: Eduardo Zulian
Author URI: http://flutuante.com.br
License: GPL2

Copyright 2013 Eduardo Zulian

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Load translated strings
 */
function afpw_load_textdomain() {

	load_plugin_textdomain( 'a-featured-page-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
}

add_action( 'plugins_loaded', 'afpw_load_textdomain' );

/**
 * Register the widget
 */
function afpw_register_widgets() {

	register_widget( 'A_Featured_Page_Widget' );
	
}

add_action( 'widgets_init', 'afpw_register_widgets' );

/**
 * A Featured Page Widget
 * Feature a page, showing its excerpt and thumbnail
 *
 */
class A_Featured_Page_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'a_featured_page_widget',
			__( 'A Featured Page Widget', 'a-featured-page-widget' ),
			array( 'description' => __( 'Feature a page and display its excerpt and post thumbnail.', 'a-featured-page-widget' ) )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {
	 	$cache = wp_cache_get( 'a_featured_page_widget', 'widget' );

		if ( !is_array($cache) )
		        $cache = array();
		
		if ( ! isset( $args['widget_id'] ) )
		        $args['widget_id'] = $this->id;
		
		if ( isset( $cache[ $args['widget_id'] ] ) ) {
		        echo $cache[ $args['widget_id'] ];
		        return;
		}
		
		ob_start();
		extract($args);		

		if ( isset( $instance['page'] ) && $instance['page'] != -1 ) {
		
			$page_id = (int) $instance['page'];
			$page_link = strip_tags( $instance['page-link'] );
			$image_size = $instance['image-size'];
		
			$p = new WP_Query( array( 'page_id' => $page_id ) );
		
			if ( $p->have_posts() ) {
			
				$p->the_post();
				
				$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? get_the_title() : $instance['title'], $instance, $this->id_base );
				
				echo $before_widget;
				echo $before_title;
				echo $title;
				echo $after_title;
				?>
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>			
					<?php if ( $image_size != 'no-thumbnail' && has_post_thumbnail() ) : ?>
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">						
						<div class="entry-image">
							<?php the_post_thumbnail( $image_size ); ?>
						</div>
					</a>
					<?php endif; ?>
					
					<div class="entry-content">
						<?php the_excerpt(); ?>
						<?php if ( ! empty( $page_link ) ) : ?>
						<a href="<?php the_permalink(); ?>" class="more-link"><?php echo $page_link; ?></a>
						<?php endif; ?>
					</div>
					
				</div>
				
				<?php
				echo $after_widget;
	
				wp_reset_postdata();
			
			}
		}

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set( 'a_featured_page_widget', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['page'] = (int)( $new_instance['page'] );
		$instance['image-size'] = strip_tags( $new_instance['image-size'] );
		$instance['page-link'] = strip_tags( $new_instance['page-link'] );
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['a_featured_page_widget'] ) )
			delete_option( 'a_featured_page_widget' );

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'a_featured_page_widget', 'widget' );
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? strip_tags( $instance['title'] ) : '';
		$page = isset( $instance['page'] ) ? (int) $instance['page'] : 0;
		$image_size = isset( $instance['image-size'] ) ? strip_tags( $instance['image-size'] ) : 'thumbnail';
		$page_link = isset( $instance['page-link'] ) ? strip_tags( $instance['page-link'] ) : __( 'Continue reading', 'a-featured-page-widget' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'a-featured-page-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'page' ); ?>"><?php _e( 'Page:', 'a-featured-page-widget' ); ?></label>
			<?php
			// Mimic wp_dropdown_pages() funcionality to add a 'widefat' class to the <select> tag
			$args = array(
	            'depth' => 0,
	            'child_of' => 0,
	            'selected' => $page,
	            'name' => $this->get_field_name( 'page' ),
	            'id' => $this->get_field_id( 'page' ),
	            'show_option_none' => '',
	            'show_option_no_change' => '',
	            'option_none_value' => ''
	        );
	
	        extract( $args, EXTR_SKIP );
	        $pages = get_pages($args);
	        
	        if ( ! empty( $pages ) ) : ?>
	            <select class="widefat" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>">
	            	<option value="-1"><?php _e( 'Select a page', 'a-featured-page-widget' ); ?></option>
	            	<?php echo walk_page_dropdown_tree( $pages, $depth, $args ) ?>;
	            </select>
	        <?php
	        endif;
	        ?>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id( 'image-size' ); ?>"><?php _e( 'Post thumbnail size:', 'a-featured-page-widget' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'image-size' ); ?>" name="<?php echo $this->get_field_name( 'image-size' ); ?>">
				<option value="no-thumbnail" <?php selected( $image_size, 'no-thumbnail' ); ?>><?php _e( 'No post thumbnail, thanks', 'a-featured-page-widget' ); ?></option>
				<?php
				$all_image_sizes = $this->_get_all_image_sizes();
				foreach ( $all_image_sizes as $key => $value ) :
					$image_dimensions = $value['width'] . 'x' . $value['height']; ?>
					<option value="<?php echo $key; ?>" <?php selected( $image_size, $key ); ?>><?php echo $key; ?> (<?php echo $image_dimensions; ?>)</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'page-link' ) ); ?>"><?php _e( 'Link text:', 'a-featured-page-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'page-link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'page-link' ) ); ?>" type="text" value="<?php echo esc_attr( $page_link ); ?>" />
			<small><?php _e( 'If empty, there will be no link to featured page.', 'a-featured-page-widget' ); ?></small>
		</p>
	<?php
	}
	
	/**
	 * Get all the registered image sizes along with their dimensions
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
	 * @return array $image_sizes The image sizes
	 */
	function _get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = array( 'thumbnail', 'medium', 'large' );
		 
		foreach ( $default_image_sizes as $size ) {
			$image_sizes[$size]['width']	= intval( get_option( "{$size}_size_w") );
			$image_sizes[$size]['height'] = intval( get_option( "{$size}_size_h") );
			$image_sizes[$size]['crop']	= get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}
		
		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) )
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
			
		return $image_sizes;
	}
}
?>