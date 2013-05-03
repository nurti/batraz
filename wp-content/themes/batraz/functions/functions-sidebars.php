<?php


if (function_exists('register_sidebar')) {
     register_sidebar(array(
        'name' => 'Sidebar header',
        'id' => 'sidebar-h1',
        'description' => __('Appears on posts and pages except the optional Front Page template, which has its own widgets', 'twentytwelve'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
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

?>
