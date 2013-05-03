<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>
	</div><!-- #main .wrapper -->
	<footer id="colophon" role="contentinfo">
            
                <?php if(BTZ_Options_Helper::search_on_footer()) : ?>
                    <?php get_sidebar( 'footer' ); ?>
                 <?php endif; ?>
            
		<div class="site-info">
                    <p class="copyright">
			<?php do_action( 'twentytwelve_credits' ); ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><?php BTZ_Options_Helper::copyright(); ?></a>
                    </p>
                    <!-- .site-info -->
                    <p class="annotation">
                        <?php BTZ_Options_Helper::annotation(); ?>
                    </p>
                </div>     
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>