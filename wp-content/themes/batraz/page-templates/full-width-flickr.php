<?php
/**
 * Template Name: Full-width-flickr Page Template Flickr, No Sidebar
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

get_header(); ?>

	<div id="primary" class="site-content-full ui-widget-content">
		<div id="content" role="main">
                      
                    <div id="slide-flickr-sets" class="flickr-sets">
                        <div class="flickr-sets-wrapper"></div>  
                    </div> 
                    <div id="slide-flickr-photos" class="flickr-photos">
                        <div id="slide-flickr-photos-wrapper" class="flickr-photos-wrapper"></div>
                    </div>
                            
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', 'page' ); ?>
			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
                
	</div><!-- #primary -->
        <script>
            jQuery(document).ready(function(){
                jQuery("#content").flickrLoad({theme : '<?php echo $GLOBALS['mSBtheme'] ?>',
                                               dim : 'small', 
                                               dimZoom : 'large',
                                               photos : {
                                                   cols : 3,
                                                   rows : 3,
                                                   speed : 9
                                               }});
            });
        </script>
<?php get_footer(); ?>