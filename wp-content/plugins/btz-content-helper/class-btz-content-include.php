<?php
/**
 * The class setup for post-content-shortcodes plugin
 * @version 0.3.2
 */
if( !class_exists( 'Btz_Content_Include' ) ) {
	/**
	 * Classe e metodi per implementazione del cloning content
	 */
	class Btz_Content_Include {
            /**
            *  I valori di default dello shortcode
            */
           var $defaults	= array();
            
            function __construct() {
                add_shortcode( 'btz-content', array( &$this, 'btz_content' ) );
                add_shortcode( 'btz-list', array( &$this, 'btz_list' ) );
                
                global $blog_id;
                /**
                * Set up dei valori degli attributi
                * @uses apply_filtersper consentire la variazione dei valori di defaults nei temi
                */
                $this->defaults = apply_filters( 'btz-content-shortcodes-defaults', array(
                        'id'			=> 0,
                        'post_type'		=> 'post',
                        'order'			=> 'asc',
                        'orderby'		=> 'post_title',
                        'numberposts'	=> -1,
                        'post_status'	=> 'publish',
                        'offset'		=> null,
                        'category'		=> null,
                        'include'		=> null,
                        'exclude'		=> null,
                        'meta_key'		=> null,
                        'meta_value'	=> null,
                        'post_mime_type'=> null,
                        'post_parent'	=> null,
                        /* Non-standard arguments */
                        'exclude_current'=> true,
                        'blog_id'		=> $blog_id,
                        'show_image'	=> false,
                        'show_excerpt'	=> false,
                        'excerpt_length'=> 0,
                        'image_width'	=> 0,
                        'image_height'	=> 0,
                        'tag' => ''
                ) );
                
                
            }
            
            /**
            * Handle the shortcode to display another post's content
            */
            function btz_content( $atts=array() ) {
                global $wpdb;
                extract( shortcode_atts( $this->defaults, $atts ) );
                /**
                 *   Per evitare l'include di se stesso
                 */
                if( $id == $GLOBALS['post']->ID || empty( $id ) )
                        return;

                $p = $this->get_post_from_blog( $id, $blog_id );
                if( empty( $p ) || is_wp_error( $p ) )
                    return apply_filters( 'btz-content-shortcodes-no-posts-error', '<p>Nessun post relativo ai criteri specificati.</p>', $this->get_args( $atts ) );

                $this->is_true( $show_excerpt );
                $this->is_true( $show_image );

                $content = $p->post_content;
                
                if ( $show_excerpt ) {
                    $content = empty( $p->post_excerpt ) ? $p->post_content : $p->post_excerpt;
                }
                
                if ( intval( $excerpt_length ) && intval( $excerpt_length ) < str_word_count( $content ) ) {
                    $content = explode( ' ', $content );
                    $content = implode( ' ', array_slice( $content, 0, ( intval( $excerpt_length ) - 1 ) ) );
                    $content = force_balance_tags( $content );
                    $content .= apply_filters( 'btz-content-shortcodes-read-more', ' <span class="read-more"><a href="' . get_permalink( $p->ID ) . '" title="' . apply_filters( 'the_title_attribute', $p->post_title ) . '">' . __( 'Read more' ) . '</a></span>' );
		}
                
                if ( $show_image ) {
                    if ( empty( $image_height ) && empty( $image_width ) )
                            $image_size = apply_filters( 'post-content-shortcodes-default-image-size', 'thumbnail' );
                    else
                            $image_size = array( intval( $image_width ), intval( $image_height ) );

                    $content = $this->get_the_post_thumbnail( $p->ID, $image_size, array( 'class' => apply_filters( 'btz-content-shortcodes-image-class', 'pcs-featured-image' ) ), $blog_id ) . '<br>' .  $content;
                }
			
		return apply_filters( 'btz-content-shortcodes-content', apply_filters( 'the_content', $content ), $p );
                
            }
            
            /**
            * Retrieve the featured image HTML for the current post
            */
           function get_the_post_thumbnail( $post_ID, $image_size = 'thumbnail', $attr = array(), $blog_id = 0 ) {
                   if ( empty( $blog_id ) || (int) $blog_id === (int) $GLOBALS['blog_id'] )
                           return get_the_post_thumbnail( $post_ID, $image_size, $attr );
                   if ( ! is_numeric( $post_ID ) || ! is_numeric( $blog_id ) )
                           return '';

                   global $wpdb;
                   $old = $wpdb->set_blog_id( $blog_id );
                   $post_thumbnail_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s AND post_id=%d LIMIT 1", '_thumbnail_id', $post_ID ) );
                   $html = wp_get_attachment_image( $post_thumbnail_id, $image_size, false, $attr );
                   $wpdb->set_blog_id( $old );
                   return $html;
           }
            
            function get_post_from_blog( $post_id=0, $blog_id=0 ) {
                if( empty( $post_id ) )
                        return;
                if( $blog_id == $GLOBALS['blog_id'] || empty( $blog_id ) )
                        return get_post( $post_id );

                if( false !== ( $p = get_transient( 'btzpc-blog' . $blog_id . '-post' . $post_id ) ) )
                        return $p;

                global $wpdb;
                $org_blog = $wpdb->set_blog_id( $blog_id );
                $p = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID=%d", $post_id ) );
                $wpdb->set_blog_id( $org_blog );

                set_transient( 'btzpc-blog' . $blog_id . '-post' . $post_id, $p, apply_filters( 'btzpc--transient-timeout', 60 * 60 ) );

                return $p;
	    }
            
            
            /**
            * Handle the shortcode to display a list of posts
            */
           function btz_list( $atts=array() ) {
                $atts = shortcode_atts( $this->defaults, $atts );
                $this->is_true( $atts['exclude_current'] );
                $this->is_true( $atts['show_excerpt'] );
                $this->is_true( $atts['show_image'] );

                /**
                 * Output a little debug info if necessary
                 */
                if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || isset( $_REQUEST['pcs-debug'] ) )
                        error_log( '[PCS Debug]: Preparazione retrieve post list con i seguenti parametri : ' . print_r( $atts, true ) );

                $posts = $this->get_posts_from_blog( $atts, $atts['blog_id'] );
               
                if( empty( $posts ) )
                        return apply_filters( 'btz-content-shortcodes-no-posts-error', '<p>Non esiste nessun post relativo ai criteri specificati.</p>', $this->get_args( $atts ) );

                $output = apply_filters( 'btz-content-shortcodes-open-list', '<ul class="post-list' . ( $atts['show_excerpt'] ? ' with-excerpt' : '' ) . ( $atts['show_image'] ? ' with-image' : '' ) . '">' );
                foreach( $posts as $p ) {
                        $output .= apply_filters( 'btz-content-shortcodes-open-item', '<li class="listed-post">' );
                        $output .= apply_filters( 'btz-content-shortcodes-item-link-open', '<a class="pcs-post-title" href="' . $this->get_shortlink_from_blog( $p->ID, $atts['blog_id'] ) . '" title="' . apply_filters( 'the_title_attribute', $p->post_title ) . '">', apply_filters( 'the_permalink', get_permalink( $p->ID ) ), apply_filters( 'the_title_attribute', $p->post_title ) );
                        $output .= apply_filters( 'the_title', $p->post_title );
                        $output .= apply_filters( 'btz-content-shortcodes-item-link-close', '</a>' );
                        if( $atts['show_excerpt'] ) {
                                $output .= '<div class="pcs-excerpt-wrapper">';
                        }
                        if( $atts['show_image'] && has_post_thumbnail( $p->ID ) ) {
                                if( empty( $atts['image_height'] ) && empty( $atts['image_width'] ) )
                                        $image_size = 'thumbnail';
                                else
                                        $image_size = array( $atts['image_width'], $atts['image_height'] );
                                $output .= get_the_post_thumbnail( $p->ID, $image_size, array( 'class' => 'pcs-featured-image' ) );
                        }
                        if( $atts['show_excerpt'] ) {
                                $excerpt = empty( $p->post_excerpt ) ? $p->post_content : $p->post_excerpt;
                                if( !empty( $atts['excerpt_length'] ) && is_numeric( $atts['excerpt_length'] ) ) {
                                        $excerpt = apply_filters( 'the_excerpt', $excerpt );
                                        if( str_word_count( $excerpt ) > $atts['excerpt_length'] ) {
                                                $excerpt = explode( ' ', $excerpt );
                                                $excerpt = implode( ' ', array_slice( $excerpt, 0, ( $atts['excerpt_length'] - 1 ) ) );
                                                $excerpt = force_balance_tags( $excerpt );
                                        }
                                }
                                $output .= '<div class="pcs-excerpt">' . apply_filters( 'the_content', $excerpt ) . '</div></div>';
                        }
                        $output .= apply_filters( 'btz-content-shortcodes-close-item', '</li>' );
                }
                $output .= apply_filters( 'btz-content-shortcodes-close-list', '</ul>' );

                return $output;
            }
            
            /**
            * Retrieve a batch of posts from a specific blog
            */
            function get_posts_from_blog( $atts=array(), $blog_id=0 ) {
                   $args = $this->get_args( $atts );

                   if( $blog_id == $GLOBALS['blog_id'] || empty( $blog_id ) || !is_numeric( $blog_id ) )
                           return get_posts( $args );

                   if( false !== ( $p = get_transient( 'pcsc-list-blog' . $blog_id . '-args' . md5( maybe_serialize( $args ) ) ) ) )
                           return $p;

                   global $wpdb;
                   $org_blog = $wpdb->set_blog_id( $blog_id );
                   $p = get_posts( $args );
                   $wpdb->set_blog_id( $org_blog );

                   set_transient( 'pcsc-list-blog'. $blog_id . '-args' . md5( maybe_serialize( $args ) ), $p, apply_filters( 'pcsc-transient-timeout', 60 * 60 ) );
                   return $p;
            }
            
            /**
            * Determine the shortlink to a post on a specific blog
            */
           function get_shortlink_from_blog( $post_id=0, $blog_id=0 ) {
                   if( empty( $post_id ) )
                           return;
                   if( empty( $blog_id ) || $blog_id == $GLOBALS['blog_id'] || !is_numeric( $blog_id ) )
                           return apply_filters( 'the_permalink', get_permalink( $post_id ) );

                   global $wpdb;
                   $blog_info = $wpdb->get_row( $wpdb->prepare( "SELECT domain, path FROM {$wpdb->blogs} WHERE blog_id=%d", $blog_id ), ARRAY_A );
                   return 'http://' . $blog_info['domain'] . $blog_info['path'] . '?p=' . $post_id;
           }

            /**
            * Determine whether a variable evaluates to boolean true
            */
            function is_true( &$var ) {
                if( in_array( $var, array( 'false', false, 0 ), true ) )
                        return $var = false;
                if( in_array( $var, array( 'true', true, 1 ), true ) )
                        return $var = true;
            }
            
            function get_args( $atts ) {
                    unset( $atts['id'] );

                    if( $atts['exclude_current'] && $GLOBALS['blog_id'] != $atts['blog_id'] ) {
                            if( !empty( $atts['exclude'] ) ) {
                                    if( !is_array( $atts['exclude'] ) )
                                            $atts['exclude'] = array_map( 'trim', explode( ',', $atts['exclude'] ) );

                                    $atts['exclude'][] = $GLOBALS['post']->ID;
                            } else {
                                    $atts['exclude'] = array( $GLOBALS['post']->ID );
                            }
                    }

                    $atts['orderby'] = str_replace( 'post_', '', $atts['orderby'] );

                    unset( $atts['blog_id'], $atts['exclude_current'] );

                    /**
                     * Output a little debug info if necessary
                     */
                    if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || isset( $_REQUEST['pcs-debug'] ) )
                            error_log( '[PCS Debug]: Preparing to return filtered args: ' . print_r( $atts, true ) );

                    return array_filter( $atts );
            }
        }
}
?>