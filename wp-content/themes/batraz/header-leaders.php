<?php if(is_page()) : ?>
    <?php if(function_exists("leader_header_setting")) : $leadarr = leader_header_setting(); ?>
        <?php if(is_array($leadarr) && array_key_exists('data', $leadarr) && array_key_exists('slug', $leadarr)) : ?>
            <?php $pageposts = $leadarr['data'];  $slug = $leadarr['slug']; ?>  
            <div class="leaders-list ui-widget" title="<?php echo get_taxonomy($slug)->labels->name;  ?>">
                <?php if(isset($leadarr['show_desc']) && $leadarr['show_desc']) : ?>
                    <div class="header-leaders ui-widget-header ui-corner-all">
                          <?php echo get_taxonomy($slug)->description; ?>
                    </div> 
                <?php endif; ?>
                <div id="slide-leaders" class="content-leaders">
                    
                    <div class="content-leaders-wrapper ui-widget-content">
                        <?php foreach ($pageposts as $post):  ?>
                            <?php setup_postdata($post); ?>
                            <p>
                                <a href="<?php the_permalink($post->ID); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), the_title_attribute( 'echo=0' ) ) ); ?>"><?php echo get_batraz_item_thumbnail(get_the_ID()); ?></a>
                                <a href="<?php echo get_term_link( $post->slug, $slug ); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), $post->term_name ) ); ?>"  rel="bookmark"><?php echo $post->term_name; ?></a>
                            </p>
                         <?php endforeach; ?>
                    </div>
                 </div>
            </div>
            <br>
               <script>
                    jQuery(document).ready(function(){
                        jQuery("#slide-leaders").mCustomScrollbar({
                                scrollButtons:{
                                        enable:true
                                },
                                theme : '<?php echo $GLOBALS['mSBtheme'] ?>',
                                horizontalScroll:true,
                                advanced:{autoExpandHorizontalScroll:true,updateOnContentResize:false}
                        });

                    });
                </script>      
        <?php endif; ?>
    <?php endif; ?>
 <?php endif; ?>
