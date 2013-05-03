<?php
/**
 * Plugin Name: BTZ Widget Random Post
 * Description: Widget post  random
 * Version: 0.1
 * Author: Aisartag
 * Author URI: http://aisartag.worpress.com
 */

/**
 * Description of widget-init
 *
 * @author syrdon
 */
class WP_Widget_Random_Posts extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_random_entries', 'description' => __( "Random posts on your site") );
		parent::__construct('random-posts', __('Random Posts'), $widget_ops);
		$this->alt_option_name = 'widget_random_entries';

		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_random_posts', 'widget');

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

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Ransom Posts') : $instance['title'], $instance, $this->id_base);
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
 			$number = 10;
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail'] : false;
                
                $type = isset( $instance['type'] ) ? $instance['type'] : 'post';

		$r = new WP_Query( apply_filters( 'widget_posts_args', array('post_type' => $type, 'posts_per_page' => $number, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true, 'orderby' => 'rand' ) ) );
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			
                             <?php if (( $show_thumbnail ) && ($thumb = get_the_post_thumbnail()))  : ?>
                        <li style="list-style:none">
                                 <a href="<?php the_permalink() ?>" title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>"><span class="post-thumbnail" ><?php echo $thumb; ?></span></a>
                        </li>
                             <?php else : ?> 
                        <li>
                                 <a href="<?php the_permalink() ?>" title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a>
                             <?php endif; ?>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_random_posts', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
                $instance['type'] = strip_tags($new_instance['type']);
		$instance['number'] = (int) $new_instance['number'];
                if(isset( $new_instance['show_thumbnail']) ){
                    $instance['show_thumbnail'] = (bool) $new_instance['show_thumbnail'];
                }else{
                    $instance['show_thumbnail'] = false;
                }
		
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_random_entries']) )
			delete_option('widget_random_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_random_posts', 'widget');
	}

	function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
                $type     = isset( $instance['type'] ) ? esc_attr( $instance['type'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? (bool) $instance['show_thumbnail'] : false;
                
?>
                <p>
                    <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Seleziona post type'); ?></label> 
                    <select name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>" class="widefat"> 
                    <?php 
                         $options = get_post_types(array('_builtin' => false, 'public' => true), 'names');
                         $options[] = 'post';
                         //$options = array('one', 'two', 'three'); 
                         foreach ($options as $option) { 
                            echo '<option value="' . $option . '" id="' . $option . '"', $type == $option ? ' selected="selected"' : '', '>', $option, '</option>'; 
                         } 
                     ?> 
                    </select> 
                </p>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
                
                <p><input class="checkbox" type="checkbox" <?php checked( $show_thumbnail ); ?> id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>"><?php _e( 'Display thumbnail?' ); ?></label></p>

  
<?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Random_Posts");'));
?>
