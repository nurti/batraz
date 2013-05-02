<?php

/*
 *  lista cross post
 */
function cross_header_ajax_setting(){
    
    // verifica esistenza classe BTZ_Otp_Options
    if( !class_exists( 'BTZ_Otp_Options' ) )
        return false;
    
    global $post;
    
    
    $redirect = get_post_meta( $post->ID, BTZ_Otp_Options::LEADER_META_REDIRECT_KEY, true );
    if($redirect)return false;
 
    
    $taxleader = get_post_meta( $post->ID, BTZ_Otp_Options::LEADER_META_FIELD_KEY, true );
    if(!$taxleader)return false;
    
    $show_desc = get_post_meta( $post->ID, BTZ_Otp_Options::LEADER_META_SHOW_DESC_KEY, true );
    
    $result = array('slug' => $taxleader, 'show_desc' => (bool)$show_desc);
    return $result;
    
}

/*
 *  OBSOLETE ????????????????????????????
 */
function cross_content_setting(){
    global $post;
    
     // verifica esistenza classe BTZ_Cross_Post
    if( !class_exists( 'BTZ_Cross_Post' ) )
        return false;
    
    $cross_post_id = get_post_meta($post->ID, BTZ_Cross_Post::CROSS_POST_META_FIELD_KEY, true);
    if(!$cross_post_id)return;
    
    $args = array('post__in' => explode(",",  $cross_post_id));
    
    $query = new WP_Query($args);
  //  error_log(print_r($query, true));
  
    return $query;
    
}


?>
