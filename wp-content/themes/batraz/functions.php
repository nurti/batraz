<?php

/**
 * BATRAZ child di Twenty Twelve functions and definitions.
 *
 * 
 * vengono eseguite prima delle functions di Twenty Twelve
 */
require_once(STYLESHEETPATH . '/classes/constants.php');
require_once(STYLESHEETPATH . '/classes/class-options-helper.php');
require_once(STYLESHEETPATH . '/classes/class-leaders-helper.php');

include(STYLESHEETPATH . '/functions/functions-sidebars.php');
include(STYLESHEETPATH . '/functions/functions-plugged.php');
include(STYLESHEETPATH . '/functions/functions-otp.php');
include(STYLESHEETPATH . '/functions/functions-cross.php');
include(STYLESHEETPATH . '/scripts/scripts.php');






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
        array('name' => OPTION_SEARCH_ON_HEADER, 'type' => 'checkbox', 'label' => 'Campo di ricerca nell \' header',),
        array('name' => OPTION_SEARCH_ON_FOOTER, 'type' => 'checkbox', 'label' => 'Campo di ricerca nel footer',),
        array('name' => OPTION_LOGO_URL, 'type' => 'text', 'label' => 'URL logo',
            'usemedia' => '/js/media.js', 'class' => 'url-text'),
        array('name' => OPTION_COPYRIGHT, 'type' => 'text', 'label' => 'Copyright', 'class' => 'long-text'),
        array('name' => OPTION_ANNOTATION, 'type' => 'text', 'label' => 'Annotazione', 'class' => 'long-text'),
       
        array('name' => OPTION_COLOR_STYLE, 'type' => 'select', 'values' => $styles,
            'label' => 'Color-Style Tema', 'class' => 'select-color-style'),
        array('name' => OPTION_LEADERS_PPP, 'type' => 'text', 'label' => 'Numero leaders OTP', 'tab' => 'Singolo Post'),
        array('name' => OPTION_LEADERS_SPEED, 'type' => 'text', 'label' => 'Speed leaders OTP', 'tab' => 'Singolo Post'),
        array('name' => OPTION_OTP_NAV_INFINITE, 'type' => 'checkbox', 'label' => 'Navigazione infinita OTP', 'tab' => 'Singolo Post'),
        array('name' => OPTION_TAXONOMIES_HIDE, 'type' => 'checkbox', 'label' => 'Nascondi tassonomie sul post', 'tab' => 'Singolo Post'),
       
    );

    $types = get_post_types(array('_builtin' => false, 'public' => true), 'names');
    $types[] = 'post';

    foreach ($types as $type) {
        $name = OPTION_COLOR_STYLE_SINGLE . '_' . $type;
        $label = 'Color-Style Single' . ' ' . $type;
        $elements[] = array('name' => $name, 'type' => 'select', 'values' => $styles,
            'label' => $label, 'class' => 'select-color-style');
    }

 
    return $elements;
}

// get batraz sub theme per personalizzare i templates header sidebar etc..
function get_current_sub_theme(){
    global $btz_sub_theme;
    if(!isset($btz_sub_theme))$btz_sub_theme = NULL;
    error_log(print_r($btz_sub_theme));
    return $btz_sub_theme;
}

/*
 * batraz thumbnail left index
 */

function get_batraz_item_thumbnail($postId, $class='') {
    return BTZ_Options_Helper::get_thumbnail_indicator($postId, $class);

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