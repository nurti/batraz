<?php

class BTZ_Leaders_Helper {
    const METHOD_SERVICE_LEADERS = 'ajax_leaders';
    
    function __construct() {

        add_action( 'wp_enqueue_scripts', array(&$this, 'load_scripts'));

        add_action('wp_ajax_' . self::METHOD_SERVICE_LEADERS , array(&$this, self::METHOD_SERVICE_LEADERS));
        add_action('wp_ajax_nopriv_' . self::METHOD_SERVICE_LEADERS , array(&$this, self::METHOD_SERVICE_LEADERS));

    }

    function load_scripts(){
        wp_register_script('btz-leaders', get_stylesheet_directory_uri() . '/js/btz-leaders.js',    array('jquery'));

        wp_localize_script( 'btz-leaders', 'leaders_ajax_object',
                array( 'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'action_leaders' => self::METHOD_SERVICE_LEADERS ) ); 
        wp_enqueue_script( 'btz-leaders' );

     }
     
     function ajax_leaders(){
        
         if( isset($_POST['slug'])){
             $slug = $_POST['slug'];
             $result_query = self::otp_get_leaders_svc($slug, 0, 50);
             
             if($result_query && isset($result_query['data']) && isset($result_query['slug']) ){
                  global $post;
                  $tmp_post = $post;
                  $slug = $result_query['slug'];
                  
                  $result = NULL;
                  foreach ($result_query['data'] as $post){
                      setup_postdata($post);
                      $result[] = array('permalink' => get_permalink(),
                                        'title' => $post->post_title, 
                                        'thumb' => BTZ_Options_Helper::get_thumbnail_indicator(get_the_ID()),
                                        'term_link' => get_term_link( $post->slug, $slug ),
                                        'term_name' => $post->term_name,
                                  );
                      
                  }
                 
                  $post = $tmp_post;
                  if(is_array($result)){
                    echo json_encode($result);
                  }
             }
             
         }
         die();
     }
     
     
     public static function getInstance(){
         return new BTZ_Leaders_Helper();
     }
     
     /*--------------------------------------------------------------------------------------------------
      *  CUSTOM QUERY LEADERS
      --------------------------------------------------------------------------------------------------*/
     public static function otp_get_leaders_redirect(){
         global $wpdb, $wp_query,  $post;
         if (!class_exists('Btz_Otp_Repository')) {
             return;
         }
         $redirect = get_post_meta( $post->ID, BTZ_Otp_Options::LEADER_META_REDIRECT_KEY, true );
         if(!$redirect)return;

         $taxleader = get_post_meta( $post->ID, BTZ_Otp_Options::LEADER_META_FIELD_KEY, true );
         if(!$taxleader)return;

         $totalposts = Btz_Otp_Repository::get_otp_leaders_count($taxleader);

         $ppp = intval(get_query_var('posts_per_page'));
         $wp_query->found_posts = $totalposts;
         $wp_query->max_num_pages = ceil($wp_query->found_posts / $ppp);
         $on_page = intval(get_query_var('paged'));
         if($on_page == 0){ $on_page = 1; }
         $offset = ($on_page-1) * $ppp;
         
         
         return self::otp_get_leaders_svc($taxleader, $startrow, $numrows);
         
    }

    public static function otp_get_leaders_svc($taxleader, $startrow, $numrows){
        global $wp_query;

        $sql_result = Btz_Otp_Repository::get_otp_leaders($wp_query, $taxleader, $startrow, $numrows);
        $result = array('slug' => $taxleader, 'data' => $sql_result);
        wp_reset_query();
        return $result;

         
    }

}
?>
