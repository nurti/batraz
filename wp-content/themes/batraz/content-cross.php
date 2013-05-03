
<?php if(function_exists("cross_content_setting")) : $query = cross_content_setting(); ?>
    <?php if($query ) : ?>
      
        <div class="cross-post-list">      
        <?php while ( $query->have_posts() ) :  $query->the_post(); ?>
            <div class="cross-post">
                <a href="<?php the_permalink(); ?>"><?php echo get_batraz_item_thumbnail(get_the_ID(), 'floatleft'); ?></a>
                <div>
                    <header class="entry-header">

                            <h1 class="entry-title">
                                  <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
                            </h1>
                    </header><!-- .entry-header -->


                    <div class="entry-content">

                            <?php the_excerpt(); ?>

                    </div><!-- .entry-content -->
                </div>	
            </div>
        <?php endwhile; wp_reset_query(); ?>
        </div>
	
    <?php endif; ?>
<?php endif; ?>
        
	
       
         
