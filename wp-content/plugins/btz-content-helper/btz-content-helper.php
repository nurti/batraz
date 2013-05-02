<?php
/**
 * Plugin Name: BTZ Content Helper
 * Description: A plugins for vary shortcodes.
 * Version: 0.1
 * Author: Aisartag
 * Author URI: http://aisartag.worpress.com
 */

/**
 * Pull delle definizioni di classe necessarie
 */
if( !class_exists( 'Btz_Content_Include' ) )
	require_once( 'class-btz-content-include.php' );

if( !class_exists( 'Btz_Popup' ) )
	require_once( 'class-btz-popup.php' );

if( !class_exists( 'Btz_Shortcodes' ) )
	require_once( 'class-btz-shortcodes.php' );

if( !class_exists( 'Btz_Cross_Post' ) )
	require_once( 'class-btz-cross-post.php' );

// --------------------
// --  PLUGIN HOOKS  --
// --------------------
add_action( 'wp_enqueue_scripts', 'btzsc_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'btzsc_enqueue_scripts' );

function btzsc_enqueue_scripts(){
     wp_register_style( 'btzsc-style', plugins_url('/css/btz-content.css', __FILE__) );
     wp_enqueue_style( 'btzsc-style' );
    
}
add_filter('upload_mimes', 'custom_upload_mimes');
function custom_upload_mimes ( $existing_mimes=array() ) {
        
	$existing_mimes['webm'] = 'video/webm';
	return $existing_mimes;
}

/*
 *  mostra ID nella lista dei posts
 */
if(is_admin()){
    add_filter('manage_posts_columns', 'posts_columns_id', 5);
    add_action('manage_posts_custom_column', 'posts_custom_id_columns', 5, 2);
    add_filter('manage_pages_columns', 'posts_columns_id', 5);
    add_action('manage_pages_custom_column', 'posts_custom_id_columns', 5, 2);
}
function posts_columns_id($defaults){
    $defaults['wps_post_id'] = __('ID');
    return $defaults;
}
function posts_custom_id_columns($column_name, $id){
    if($column_name === 'wps_post_id'){
         echo $id;
    }
}




/**
 * 
 *  ATTN! funziona solo se attivato il plugin Types ====== EFFETTUARE VERIFICA =======================
 * 
 */
add_action('wpcf_after_init', 'btz_content_init');
function btz_content_init(){
    new Btz_Content_Include();
   
}

add_filter( 'btz-content-shortcodes-defaults', 'other_args_default', 10, 1);
function other_args_default($args){
    if(!isset($args['tag'])){
        $args['tag'] = '';
    }
    
    $taxonomies = get_taxonomies(array('public' => true, '_builtin' => false), 'names');
    foreach($taxonomies as $tax){
        if(!isset($args[$tax])){
            $args[$tax] = '';
        }
    }
    
    return $args;
}

 new Btz_Popup();
 new Btz_Shortcodes();
 new BTZ_Cross_Post();

?>