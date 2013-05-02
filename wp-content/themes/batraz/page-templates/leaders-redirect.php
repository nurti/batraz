<?php
/**
 * Template Name: Full-Leaders, No Sidebar
 *
 * Description: Twenty Twelve loves the no-sidebar look as much as
 * you do. Use this page template to remove the sidebar from any page.
 *
 * Tip: to remove the sidebar from all posts and pages simply remove
 * any active widgets from the Main Sidebar area, and the sidebar will
 * disappear everywhere.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

$pageposts = $GLOBALS['leader_page_parms']['data'];
$slug = $GLOBALS['leader_page_parms']['slug'];

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
                    
                        <?php if ($pageposts): ?>
                        <?php global $post; ?>
                        <?php foreach ($pageposts as $post):  ?>
                             <?php setup_postdata($post); ?>
                             <article id="post-<?php the_ID(); ?>" <?php post_class() ?>>
                                <?php if ( is_sticky() && is_home() && ! is_paged() ) : ?>
                                <div class="featured-post">
                                        <?php _e( 'Featured post', 'twentytwelve' ); ?>
                                </div>
                                <?php endif; ?>
                                <header class="entry-header item-right">
                                    <a href="<?php the_permalink(); ?>"><?php echo get_batraz_item_thumbnail(get_the_ID(), 'floatleft'); ?></a>
                                        <h1 class="entry-title">
                                              <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
                                              [ <a href="<?php echo get_term_link( $post->slug, $slug ); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), $post->term_name ) ); ?>"  rel="bookmark"><?php echo $post->term_name; ?></a> ]

                                        </h1>
                                </header><!-- .entry-header -->


                                <div class="entry-content item-right">

                                        <?php the_excerpt(); ?>

                                </div><!-- .entry-content -->


                             </article><!-- #post -->

                        <?php endforeach; ?>
                         <?php twentytwelve_content_nav( 'nav-below' ); ?>
                             
                        <?php endif; ?>

			

		</div><!-- #content -->
	</div><!-- #primary -->
<?php get_sidebar( 'leader' ); ?>
<?php get_footer(); ?>