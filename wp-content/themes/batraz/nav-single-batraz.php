<?php
/**
 * The Template for nav single batraz.
 *
 * @package Batraz
 * @subpackage Batraz
 * @since Batraz 0.0
 */
?>

<!-- se non esiste otp order -->
<?php if(count($array_nav =btz_otp_navigation()) == 0 ) : ?>
    <nav class="nav-single">
            <h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
            <span titl="Post Precedente" class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentytwelve' ) . '</span> %title' , true); ?></span>
            <span title="Post Successivo" class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentytwelve' ) . '</span>', true ); ?></span>

    </nav><!-- .nav-single -->
<?php else : ?> 
    <?php foreach ($array_nav as $nav) : ?>
        <nav class="nav-single-otp ui-widget-content ui-corner-all" title="<?php echo $nav->name; ?>">
            <span><marquee  behavior="alternate" hspace="100" ><?php echo $nav->name; ?></marquee></span>
            <?php if($nav->otp_prev) : ?>
                <div class="nav-single-otp-prev">

                    <a href="<?php echo $nav->guid_prev;  ?>"  >
                        <span title="<?php echo $nav->title_prev;  ?>"></span> 
                        <?php echo get_batraz_item_thumbnail($nav->otp_prev); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if($nav->otp_next) : ?>
                <div class="nav-single-otp-next">

                    <a href="<?php echo $nav->guid_next;  ?>"  >
                        <?php echo get_batraz_item_thumbnail($nav->otp_next); ?>
                         <span title="<?php echo $nav->title_next;  ?>"></span> 
                    </a>
                </div>
            <?php endif; ?>
        </nav>
    <?php endforeach; ?>
    
<?php endif; ?>
                                
                                
                                
