<?php if(is_page()) : ?>
     <?php if(function_exists("leader_header_ajax_setting")) : $leadarr = leader_header_ajax_setting(); ?>
        <?php if(is_array($leadarr) && array_key_exists('slug', $leadarr)) : ?>
             <div class="leaders-list ui-widget" title="<?php echo get_taxonomy($leadarr['slug'])->labels->name;  ?>">
             <?php if(isset($leadarr['show_desc']) && $leadarr['show_desc']) : ?>
                        <div class="header-leaders">
                              <span><marquee scrollamount="5"><?php echo get_taxonomy($leadarr['slug'])->description; ?></marquee></span>
                        </div> 
                   <?php endif; ?>
                   <div id="slide-leaders" class="content-leaders">
                        <div class="content-leaders-wrapper ui-widget-content"></div>
                   </div>
             </div>
              <script>
                 
                jQuery(document).ready(function(){
                     jQuery("#slide-leaders").loadLeaders({ slug : '<?php echo $leadarr['slug']; ?>',
                                                            ppp : <?php the_leaders_ppp_option(); ?>,
                                                            speed : <?php the_leaders_speed_option(); ?>,
                                                            debug : <?php the_debug_state(); ?>
                                            });
                });
             </script>
                
        <?php endif; ?>
    <?php endif; ?>
 <?php endif; ?>

