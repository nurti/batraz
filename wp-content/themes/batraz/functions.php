<?php

/**
 * BATRAZ child di Twenty Twelve functions and definitions.
 *
 * 
 * vengono eseguite prima delle functions di Twenty Twelve
 */
require_once(STYLESHEETPATH . '/library/constants.php');
require_once(STYLESHEETPATH . '/library/class-options-helper.php');
require_once(STYLESHEETPATH . '/library/class-leaders-helper.php');

include(STYLESHEETPATH . '/functions-plugged.php');
include(STYLESHEETPATH . '/functions-leaders.php');
include(STYLESHEETPATH . '/functions-cross.php');
include(STYLESHEETPATH . '/scripts.php');



/*
 *  registrazione sidebars batraz
 */
if (function_exists('register_sidebar')) {
    register_sidebar(array(
        'name' => 'Sidebar footer',
        'id' => 'sidebar-b1',
        'description' => __('Appears on posts and pages except the optional Front Page template, which has its own widgets', 'twentytwelve'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar( array(
        'name' => __( 'Sidebar leader' ),
        'id' => 'sidebar-l1',
        'description' => __( 'Appears on posts and pages except the optional Front Page template, which has its own widgets', 'twentytwelve' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );
}



/*
 *  setup iniziale del tema
 */
add_action('after_setup_theme', 'batraz_setup');
function batraz_setup() {
    // registra placeholder un menu secondario.
    register_nav_menu('topright', 'Menu Alto a Destra');
    new BTZ_Options_Helper();
    
    BTZ_Leaders_Helper::getInstance();

}

/*
 * aggiunta campi opzioni BATRAZ non assegnabili col costruttore
 * ( post-type non ancora disponibile )
 */
add_filter('adding_elements_options', 'adding_elements_options_func', 10, 1);
function adding_elements_options_func($optObj) {
    $styles = BTZ_Options_Helper::get_styles_path();
    $elements = array(
        array('name' => OPTION_TOPRIGHT_HIDE, 'type' => 'checkbox', 'label' => 'Nascondi menu top',),
        array('name' => OPTION_PRIMARY_HIDE, 'type' => 'checkbox', 'label' => 'Nascondi menu main',),
        array('name' => OPTION_TOPRIGHT_HH, 'type' => 'checkbox', 'label' => 'Item home su menu top',),
        array('name' => OPTION_PRIMARY_HH, 'type' => 'checkbox', 'label' => 'Item home su menu primary',),
        array('name' => OPTION_LOGO_URL, 'type' => 'text', 'label' => 'URL logo',
            'usemedia' => '/js/media.js', 'class' => 'url-text'),
        array('name' => OPTION_COPYRIGHT, 'type' => 'text', 'label' => 'Copyright', 'class' => 'long-text'),
        array('name' => OPTION_ANNOTATION, 'type' => 'text', 'label' => 'Annotazione', 'class' => 'long-text'),
       
        array('name' => OPTION_COLOR_STYLE, 'type' => 'select', 'values' => $styles,
            'label' => 'Color-Style Tema', 'class' => 'select-color-style'),
       
    );

    $types = get_post_types(array('_builtin' => false, 'public' => true), 'names');
    $types[] = 'post';

    foreach ($types as $type) {
        $name = OPTION_COLOR_STYLE_SINGLE . '_' . $type;
        $label = 'Color-Style Single' . ' ' . $type;
        $elements[] = array('name' => $name, 'type' => 'select', 'values' => $styles,
            'label' => $label, 'class' => 'select-color-style');
    }

    // tab leaders
    $elements[] =  array('name' => OPTION_LEADERS_PPP, 'type' => 'text', 'label' => 'Numero leaders', 'tab' => 'Opzioni Leaders');
    $elements[] =  array('name' => OPTION_LEADERS_SPEED, 'type' => 'text', 'label' => 'Speed leaders', 'tab' => 'Opzioni Leaders');
    
    // tab taxonomies
    $elements[] =  array('name' => OPTION_TAXONOMIES_HIDE, 'type' => 'checkbox', 'label' => 'Nascondi tassonomie sul post', 'tab' => 'Opzioni Tassonomie');
    
    return $elements;
}

/*
 * batraz thumbnail left index
 */

function get_batraz_item_thumbnail($class='') {
    return BTZ_Options_Helper::get_thumbnail_indicator($class);

}





/*
 *  AREA FUNZIONI DA SISTEMARE
 */
add_filter('excerpt_length', 'batraz_excerpt_length');
function batraz_excerpt_length($length) {
    if (!is_search()) {
        return 20;
    }
    return $length;
}


?>