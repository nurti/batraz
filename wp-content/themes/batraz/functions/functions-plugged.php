<?php
/**
 * BATRAZ child di Twenty Twelve functions-overrides.
 *
 * 
 * contiene le funzioni overrides del teme root
 */
if ( ! function_exists( 'twentytwelve_content_nav' ) ) :
/**
 * override
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_content_nav( $html_id ) {
	global $wp_query;

	$html_id = esc_attr( $html_id );

	if ( $wp_query->max_num_pages > 1 ) : ?>

            <?php if(function_exists('wp_pagenavi') ) : ?>
		<?php wp_pagenavi(); ?>
			<br />
		<?php else: ?>
                    <nav id="<?php echo $html_id; ?>" class="navigation" role="navigation">
                            <h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
                            <div class="nav-previous alignleft"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentytwelve' ) ); ?></div>
                            <div class="nav-next alignright"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?></div>
                    </nav><!-- #<?php echo $html_id; ?> .navigation -->
           <?php endif; ?>             
	<?php endif;
}
endif;

function btz_link_pages(){
    if(function_exists('wp_pagenavi')){
        wp_pagenavi( array( 'type' => 'multipart' ) );
    }else{
        wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'twentytwelve' ), 'after' => '</div>' ) );
    }
    
}

?>
